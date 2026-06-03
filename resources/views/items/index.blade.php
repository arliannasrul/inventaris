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
                <tr><th>Foto</th><th>SKU</th><th>Nama</th><th>Kategori</th><th>Lokasi</th><th>Stok</th><th>Nilai</th></tr>
            </thead>
            <tbody>
            @forelse (($data['items'] ?? []) as $item)
                <tr>
                    <td>
                        @if (!empty($item['image_url']))
                            <a href="{{ $item['image_url'] }}" target="_blank" title="Klik untuk memperbesar gambar" style="cursor: zoom-in; display: block; width: 40px; height: 40px;">
                                <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0; display: block; transition: transform 0.2s ease;">
                            </a>
                        @else
                            <div style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #94a3b8; font-weight: bold; border: 1px solid #e2e8f0;">N/A</div>
                        @endif
                    </td>
                    <td>{{ $item['sku'] }}</td>
                    <td><a href="{{ route('items.show', $item['id']) }}">{{ $item['name'] }}</a></td>
                    <td>{{ $item['category'] }}</td>
                    <td>{{ $item['location'] }}</td>
                    <td>{{ $item['quantity'] }} {{ $item['unit'] }}</td>
                    <td>Rp {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Belum ada barang.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <h2>Tambah Barang</h2>
    <form class="form-grid" method="post" action="{{ route('items.store') }}" enctype="multipart/form-data">
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
        <div class="form-file" style="grid-column: span 2; display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Foto Barang</label>
            <input type="file" name="image" accept="image/*" style="border: 1px dashed #cbd5e1; padding: 8px; border-radius: 6px; cursor: pointer;">
        </div>
        <textarea name="notes" placeholder="Catatan"></textarea>
        <button class="button primary" type="submit">Simpan barang</button>
    </form>
</section>
@endsection
