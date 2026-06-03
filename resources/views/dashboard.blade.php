@extends('layouts.app')

@section('content')
<header class="page-head">
    <div>
        <p class="eyebrow">Ringkasan operasional</p>
        <h1>Dashboard Inventaris</h1>
    </div>
    <a class="button" href="{{ route('reports.print') }}">Cetak laporan</a>
</header>

<section class="stats">
    <article><span>Total barang</span><strong>{{ $data['summary']['items'] ?? 0 }}</strong></article>
    <article><span>Total stok</span><strong>{{ $data['summary']['stock'] ?? 0 }}</strong></article>
    <article><span>Nilai inventaris</span><strong>Rp {{ number_format($data['summary']['value'] ?? 0, 0, ',', '.') }}</strong></article>
    <article><span>Stok rendah</span><strong>{{ $data['summary']['lowStock'] ?? 0 }}</strong></article>
</section>

<section class="grid two">
    <div class="panel">
        <div class="panel-head">
            <h2>Barang Stok Rendah</h2>
            <a href="{{ route('items.index', ['stock' => 'low']) }}">Lihat semua</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Foto</th><th>SKU</th><th>Barang</th><th>Stok</th><th>Minimum</th></tr></thead>
                <tbody>
                @forelse (($data['lowStockItems'] ?? []) as $item)
                    <tr>
                        <td>
                            @if (!empty($item['image_url']))
                                <a href="{{ $item['image_url'] }}" target="_blank" title="Klik untuk memperbesar gambar" style="cursor: zoom-in; display: block; width: 32px; height: 32px;">
                                    <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; display: block;">
                                </a>
                            @else
                                <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 8px; color: #94a3b8; font-weight: bold; border: 1px solid #e2e8f0;">N/A</div>
                            @endif
                        </td>
                        <td>{{ $item['sku'] }}</td>
                        <td><a href="{{ route('items.show', $item['id']) }}">{{ $item['name'] }}</a></td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ $item['minimum_stock'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Tidak ada barang stok rendah.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2>Aktivitas Terbaru</h2>
            <a href="{{ route('reports.index') }}">Laporan</a>
        </div>
        <ul class="activity">
            @forelse (($data['recentMovements'] ?? []) as $movement)
                <li style="display: flex; gap: 12px; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
                    @if (!empty($movement['item']['image_url']))
                        <a href="{{ $movement['item']['image_url'] }}" target="_blank" title="Klik untuk memperbesar gambar" style="cursor: zoom-in; display: block; width: 36px; height: 36px; flex-shrink: 0;">
                            <img src="{{ $movement['item']['image_url'] }}" alt="" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0;">
                        </a>
                    @else
                        <div style="width: 36px; height: 36px; background: #f1f5f9; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 9px; color: #94a3b8; border: 1px solid #e2e8f0; flex-shrink: 0;">N/A</div>
                    @endif
                    <div>
                        <strong>{{ $movement['item']['name'] ?? '-' }}</strong>
                        <span style="display: block; font-size: 13px; color: #64748b; margin-top: 2px;">{{ $movement['type'] }} {{ $movement['quantity'] }} {{ $movement['item']['unit'] ?? '' }} oleh {{ $movement['actor'] }}</span>
                    </div>
                </li>
            @empty
                <li class="empty">Belum ada aktivitas stok.</li>
            @endforelse
        </ul>
    </div>
</section>
@endsection
