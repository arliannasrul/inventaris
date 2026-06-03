<?php

namespace App\Http\Controllers;

use App\Services\PrismaInventoryClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(private PrismaInventoryClient $inventory)
    {
    }

    public function dashboard(): View
    {
        return view('dashboard', ['data' => $this->inventory->dashboard()]);
    }

    public function items(Request $request): View
    {
        return view('items.index', [
            'data' => $this->inventory->items($request->only(['q', 'category', 'location', 'stock'])),
            'filters' => $request->only(['q', 'category', 'location', 'stock']),
        ]);
    }

    public function storeItem(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'sku' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:120'],
            'location' => ['required', 'string', 'max:120'],
            'supplier' => ['nullable', 'string', 'max:120'],
            'unit' => ['required', 'string', 'max:40'],
            'quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->inventory->createItem($payload);

        return redirect()->route('items.index')->with('status', 'Barang berhasil ditambahkan.');
    }

    public function showItem(string $id): View
    {
        return view('items.show', ['item' => $this->inventory->item($id)]);
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

        $this->inventory->createMovement($id, $payload);

        return redirect()->route('items.show', $id)->with('status', 'Pergerakan stok dicatat.');
    }

    public function storeMessage(Request $request, string $id): RedirectResponse
    {
        $payload = $request->validate([
            'author' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $this->inventory->createMessage($id, $payload);

        return redirect()->route('items.show', $id)->with('status', 'Pesan dikirim.');
    }

    public function reports(Request $request): View
    {
        return view('reports.index', [
            'data' => $this->inventory->reports($request->only(['from', 'to', 'category', 'location'])),
            'filters' => $request->only(['from', 'to', 'category', 'location']),
        ]);
    }

    public function printReport(Request $request): View
    {
        return view('reports.print', [
            'data' => $this->inventory->reports($request->only(['from', 'to', 'category', 'location'])),
            'filters' => $request->only(['from', 'to', 'category', 'location']),
        ]);
    }

    public function notifications(): View
    {
        return view('notifications.index', ['data' => $this->inventory->notifications()]);
    }

    public function markNotificationRead(string $id): RedirectResponse
    {
        $this->inventory->markNotificationRead($id);

        return redirect()->route('notifications.index');
    }
}
