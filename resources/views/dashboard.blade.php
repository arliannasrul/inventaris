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
                <thead><tr><th>SKU</th><th>Barang</th><th>Stok</th><th>Minimum</th></tr></thead>
                <tbody>
                @forelse (($data['lowStockItems'] ?? []) as $item)
                    <tr>
                        <td>{{ $item['sku'] }}</td>
                        <td><a href="{{ route('items.show', $item['id']) }}">{{ $item['name'] }}</a></td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ $item['minimumStock'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">Tidak ada barang stok rendah.</td></tr>
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
                <li>
                    <strong>{{ $movement['item']['name'] ?? '-' }}</strong>
                    <span>{{ $movement['type'] }} {{ $movement['quantity'] }} oleh {{ $movement['actor'] }}</span>
                </li>
            @empty
                <li class="empty">Belum ada aktivitas stok.</li>
            @endforelse
        </ul>
    </div>
</section>
@endsection
