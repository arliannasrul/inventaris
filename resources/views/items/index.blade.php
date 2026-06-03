@extends('layouts.app')

@section('content')
<header class="page-head">
    <div>
        <p class="eyebrow">Master data</p>
        <h1>Pencatatan Barang</h1>
    </div>
</header>

<section class="panel">
    <form class="filters" method="get">
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari SKU atau nama">
        <input name="category" value="{{ $filters['category'] ?? '' }}" placeholder="Kategori">
        <input name="location" value="{{ $filters['location'] ?? '' }}" placeholder="Lokasi">
        <select name="stock">
            <option value="">Semua stok</option>
            <option value="low" @selected(($filters['stock'] ?? '') === 'low')>Stok rendah</option>
            <option value="empty" @selected(($filters['stock'] ?? '') === 'empty')>Habis</option>
        </select>
        <button class="button" type="submit">Filter</button>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>SKU</th><th>Nama</th><th>Kategori</th><th>Lokasi</th><th>Stok</th><th>Nilai</th></tr>
            </thead>
            <tbody>
            @forelse (($data['items'] ?? []) as $item)
                <tr>
                    <td>{{ $item['sku'] }}</td>
                    <td><a href="{{ route('items.show', $item['id']) }}">{{ $item['name'] }}</a></td>
                    <td>{{ $item['category'] }}</td>
                    <td>{{ $item['location'] }}</td>
                    <td>{{ $item['quantity'] }} {{ $item['unit'] }}</td>
                    <td>Rp {{ number_format(($item['quantity'] ?? 0) * ($item['unitPrice'] ?? 0), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">Belum ada barang.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <h2>Tambah Barang</h2>
    <form class="form-grid" method="post" action="{{ route('items.store') }}">
        @csrf
        <input name="sku" placeholder="SKU" required>
        <input name="name" placeholder="Nama barang" required>
        <input name="category" placeholder="Kategori" required>
        <input name="location" placeholder="Lokasi" required>
        <input name="supplier" placeholder="Pemasok">
        <input name="unit" placeholder="Satuan" value="pcs" required>
        <input name="quantity" type="number" min="0" placeholder="Stok awal" required>
        <input name="minimum_stock" type="number" min="0" placeholder="Minimum stok" required>
        <input name="unit_price" type="number" min="0" step="0.01" placeholder="Harga satuan" required>
        <textarea name="notes" placeholder="Catatan"></textarea>
        <button class="button primary" type="submit">Simpan barang</button>
    </form>
</section>
@endsection
