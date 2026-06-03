<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Notification;
use App\Models\Message;
use App\Models\AuditLog;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function __construct(private ImageUploadService $uploader)
    {
    }

    public function dashboard(): View
    {
        $items = Item::latest()->get();

        $summary = [
            'items' => $items->count(),
            'stock' => $items->sum('quantity'),
            'value' => $items->sum(fn($item) => $item->quantity * $item->unit_price),
            'lowStock' => $items->filter(fn($item) => $item->quantity <= $item->minimum_stock)->count(),
        ];

        $lowStockItems = $items->filter(fn($item) => $item->quantity <= $item->minimum_stock)
            ->sortBy('quantity')
            ->take(8)
            ->values()
            ->toArray();

        $recentMovements = StockMovement::with('item')
            ->latest()
            ->take(10)
            ->get()
            ->toArray();

        return view('dashboard', [
            'data' => [
                'summary' => $summary,
                'lowStockItems' => $lowStockItems,
                'recentMovements' => $recentMovements,
            ]
        ]);
    }

    public function items(Request $request): View
    {
        $query = Item::query();

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function($w) use ($q) {
                $w->where('sku', 'like', "%{$q}%")
                  ->orWhere('name', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', 'like', '%' . $request->input('category') . '%');
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->input('location') . '%');
        }

        if ($request->input('stock') === 'empty') {
            $query->where('quantity', 0);
        }

        $items = $query->orderBy('quantity', 'asc')->orderBy('name', 'asc')->get();

        if ($request->input('stock') === 'low') {
            $items = $items->filter(fn($item) => $item->quantity <= $item->minimum_stock)->values();
        }

        $summary = [
            'items' => $items->count(),
            'stock' => $items->sum('quantity'),
            'value' => $items->sum(fn($item) => $item->quantity * $item->unit_price),
        ];

        return view('items.index', [
            'data' => [
                'items' => $items->toArray(),
                'summary' => $summary,
            ],
            'filters' => $request->only(['q', 'category', 'location', 'stock']),
        ]);
    }

    public function storeItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:80', 'unique:items,sku'],
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:120'],
            'location' => ['required', 'string', 'max:120'],
            'supplier' => ['nullable', 'string', 'max:120'],
            'unit' => ['required', 'string', 'max:40'],
            'quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:20480'],
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = $this->uploader->upload($request->file('image'));
        }

        $item = Item::create([
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'category' => $validated['category'],
            'location' => $validated['location'],
            'supplier' => $validated['supplier'] ?? null,
            'unit' => $validated['unit'],
            'quantity' => $validated['quantity'],
            'minimum_stock' => $validated['minimum_stock'],
            'unit_price' => $validated['unit_price'],
            'notes' => $validated['notes'] ?? null,
            'image_url' => $imageUrl,
        ]);

        AuditLog::create([
            'item_id' => $item->id,
            'action' => 'ITEM_CREATED',
            'actor' => 'system',
            'payload' => $validated,
        ]);

        $this->createStockNotification($item);

        return redirect()->route('items.index')->with('status', 'Barang berhasil ditambahkan.');
    }

    public function showItem(string $id): View
    {
        $item = Item::with([
            'movements' => fn($q) => $q->latest()->take(30),
            'messages' => fn($q) => $q->latest()->take(30),
            'auditLogs' => fn($q) => $q->latest()->take(20),
        ])->findOrFail($id);

        return view('items.show', ['item' => $item->toArray()]);
    }

    public function editItem(string $id): View
    {
        $item = Item::findOrFail($id);
        return view('items.edit', ['item' => $item->toArray()]);
    }

    public function updateItem(Request $request, string $id): RedirectResponse
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:80', 'unique:items,sku,' . $item->id],
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:120'],
            'location' => ['required', 'string', 'max:120'],
            'supplier' => ['nullable', 'string', 'max:120'],
            'unit' => ['required', 'string', 'max:40'],
            'quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:20480'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $imageUrl = $item->image_url;

        if ($request->boolean('remove_image')) {
            $imageUrl = null;
        }

        if ($request->hasFile('image')) {
            $imageUrl = $this->uploader->upload($request->file('image'));
        }

        $oldValues = $item->toArray();

        $item->update([
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'category' => $validated['category'],
            'location' => $validated['location'],
            'supplier' => $validated['supplier'] ?? null,
            'unit' => $validated['unit'],
            'quantity' => $validated['quantity'],
            'minimum_stock' => $validated['minimum_stock'],
            'unit_price' => $validated['unit_price'],
            'notes' => $validated['notes'] ?? null,
            'image_url' => $imageUrl,
        ]);

        AuditLog::create([
            'item_id' => $item->id,
            'action' => 'ITEM_UPDATED',
            'actor' => 'system',
            'payload' => [
                'before' => $oldValues,
                'after' => $item->toArray(),
            ],
        ]);

        $this->createStockNotification($item);

        return redirect()->route('items.show', $item->id)->with('status', 'Barang berhasil diperbarui.');
    }

    public function storeMovement(Request $request, string $id): RedirectResponse
    {
        $payload = $request->validate([
            'type' => ['required', 'in:IN,OUT,DAMAGED,ADJUSTMENT'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reference' => ['nullable', 'string', 'max:120'],
            'actor' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $item = Item::findOrFail($id);

        $nextQuantity = $item->quantity;
        if ($payload['type'] === 'IN') {
            $nextQuantity += $payload['quantity'];
        } elseif ($payload['type'] === 'ADJUSTMENT') {
            $nextQuantity = $payload['quantity'];
        } else {
            $nextQuantity -= $payload['quantity'];
        }

        if ($nextQuantity < 0) {
            return redirect()->back()->withErrors(['quantity' => 'Stok tidak cukup untuk transaksi ini.']);
        }

        DB::transaction(function() use ($item, $payload, $nextQuantity) {
            StockMovement::create([
                'item_id' => $item->id,
                'type' => $payload['type'],
                'quantity' => $payload['quantity'],
                'reference' => $payload['reference'] ?? null,
                'actor' => $payload['actor'],
                'notes' => $payload['notes'] ?? null,
            ]);

            $item->update(['quantity' => $nextQuantity]);

            AuditLog::create([
                'item_id' => $item->id,
                'action' => 'STOCK_' . $payload['type'],
                'actor' => $payload['actor'],
                'payload' => ['before' => $item->quantity, 'after' => $nextQuantity, 'movement' => $payload],
            ]);

            Notification::create([
                'type' => 'MOVEMENT',
                'title' => "Stok {$item->name} diperbarui",
                'body' => "{$payload['actor']} mencatat {$payload['type']} sebanyak {$payload['quantity']} {$item->unit}.",
            ]);
        });

        $this->createStockNotification($item);

        return redirect()->route('items.show', $id)->with('status', 'Pergerakan stok dicatat.');
    }

    public function storeMessage(Request $request, string $id): RedirectResponse
    {
        $payload = $request->validate([
            'author' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $item = Item::findOrFail($id);

        Message::create([
            'item_id' => $item->id,
            'author' => $payload['author'],
            'message' => $payload['message'],
        ]);

        Notification::create([
            'type' => 'SYSTEM',
            'title' => "Pesan baru untuk {$item->name}",
            'body' => "{$payload['author']}: {$payload['message']}",
        ]);

        return redirect()->route('items.show', $id)->with('status', 'Pesan dikirim.');
    }

    public function reports(Request $request): View
    {
        $itemQuery = Item::query();
        if ($request->filled('category')) {
            $itemQuery->where('category', 'like', '%' . $request->input('category') . '%');
        }
        if ($request->filled('location')) {
            $itemQuery->where('location', 'like', '%' . $request->input('location') . '%');
        }

        $movementQuery = StockMovement::with('item');
        if ($request->filled('from') || $request->filled('to')) {
            if ($request->filled('from')) {
                $movementQuery->where('created_at', '>=', $request->input('from') . ' 00:00:00');
            }
            if ($request->filled('to')) {
                $movementQuery->where('created_at', '<=', $request->input('to') . ' 23:59:59');
            }
        }

        if ($request->filled('category') || $request->filled('location')) {
            $itemIds = $itemQuery->pluck('id');
            $movementQuery->whereIn('item_id', $itemIds);
        }

        $items = $itemQuery->orderBy('name', 'asc')->get();
        $movements = $movementQuery->latest()->take(500)->get();

        $summary = [
            'items' => $items->count(),
            'stock' => $items->sum('quantity'),
            'value' => $items->sum(fn($item) => $item->quantity * $item->unit_price),
            'in' => $movements->filter(fn($m) => $m->type === 'IN')->sum('quantity'),
            'out' => $movements->filter(fn($m) => in_array($m->type, ['OUT', 'DAMAGED']))->sum('quantity'),
        ];

        return view('reports.index', [
            'data' => [
                'summary' => $summary,
                'items' => $items->toArray(),
                'movements' => $movements->toArray(),
            ],
            'filters' => $request->only(['from', 'to', 'category', 'location']),
        ]);
    }

    public function printReport(Request $request): View
    {
        $itemQuery = Item::query();
        if ($request->filled('category')) {
            $itemQuery->where('category', 'like', '%' . $request->input('category') . '%');
        }
        if ($request->filled('location')) {
            $itemQuery->where('location', 'like', '%' . $request->input('location') . '%');
        }

        $movementQuery = StockMovement::with('item');
        if ($request->filled('from') || $request->filled('to')) {
            if ($request->filled('from')) {
                $movementQuery->where('created_at', '>=', $request->input('from') . ' 00:00:00');
            }
            if ($request->filled('to')) {
                $movementQuery->where('created_at', '<=', $request->input('to') . ' 23:59:59');
            }
        }

        if ($request->filled('category') || $request->filled('location')) {
            $itemIds = $itemQuery->pluck('id');
            $movementQuery->whereIn('item_id', $itemIds);
        }

        $items = $itemQuery->orderBy('name', 'asc')->get();
        $movements = $movementQuery->latest()->take(500)->get();

        $summary = [
            'items' => $items->count(),
            'stock' => $items->sum('quantity'),
            'value' => $items->sum(fn($item) => $item->quantity * $item->unit_price),
            'in' => $movements->filter(fn($m) => $m->type === 'IN')->sum('quantity'),
            'out' => $movements->filter(fn($m) => in_array($m->type, ['OUT', 'DAMAGED']))->sum('quantity'),
        ];

        return view('reports.print', [
            'data' => [
                'summary' => $summary,
                'items' => $items->toArray(),
                'movements' => $movements->toArray(),
            ],
            'filters' => $request->only(['from', 'to', 'category', 'location']),
        ]);
    }

    public function notifications(): View
    {
        $notifications = Notification::latest()->take(80)->get();
        return view('notifications.index', ['data' => ['notifications' => $notifications->toArray()]]);
    }

    public function markNotificationRead(string $id): RedirectResponse
    {
        Notification::findOrFail($id)->update(['read_at' => now()]);
        return redirect()->route('notifications.index');
    }

    private function createStockNotification(Item $item): void
    {
        if ($item->quantity === 0) {
            Notification::create([
                'type' => 'OUT_OF_STOCK',
                'title' => "{$item->name} habis",
                'body' => "Stok {$item->sku} di {$item->location} sudah 0 {$item->unit}.",
            ]);
        } elseif ($item->quantity <= $item->minimum_stock) {
            Notification::create([
                'type' => 'LOW_STOCK',
                'title' => "{$item->name} stok rendah",
                'body' => "Sisa {$item->quantity} {$item->unit}; minimum yang disarankan {$item->minimum_stock}.",
            ]);
        }
    }
}
