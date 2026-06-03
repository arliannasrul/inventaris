@extends('layouts.app')

@section('content')
<header class="page-head">
    <div>
        <p class="eyebrow">Edit barang</p>
        <h1>Edit: {{ $item['name'] }}</h1>
    </div>
    <a class="button" href="{{ route('items.show', $item['id']) }}">Batal</a>
</header>

@if ($errors->any())
    <div class="error">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="panel">
    <h2>Detail Barang</h2>
    <form class="form-grid" method="post" action="{{ route('items.update', $item['id']) }}" enctype="multipart/form-data">
        @csrf
        
        <div class="form-group" style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">SKU</label>
            <input name="sku" value="{{ old('sku', $item['sku']) }}" placeholder="SKU" required>
        </div>

        <div class="form-group" style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Nama Barang</label>
            <input name="name" value="{{ old('name', $item['name']) }}" placeholder="Nama barang" required>
        </div>

        <div class="form-group" style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Kategori</label>
            <input name="category" value="{{ old('category', $item['category']) }}" placeholder="Kategori" required>
        </div>

        <div class="form-group" style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Lokasi</label>
            <input name="location" value="{{ old('location', $item['location']) }}" placeholder="Lokasi" required>
        </div>

        <div class="form-group" style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Pemasok</label>
            <input name="supplier" value="{{ old('supplier', $item['supplier']) }}" placeholder="Pemasok">
        </div>

        <div class="form-group" style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Satuan</label>
            <input name="unit" value="{{ old('unit', $item['unit']) }}" placeholder="Satuan" required>
        </div>

        <div class="form-group" style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Stok Saat Ini</label>
            <input name="quantity" type="number" min="0" value="{{ old('quantity', $item['quantity']) }}" placeholder="Stok" required>
        </div>

        <div class="form-group" style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Minimum Stok</label>
            <input name="minimum_stock" type="number" min="0" value="{{ old('minimum_stock', $item['minimum_stock']) }}" placeholder="Minimum stok" required>
        </div>

        <div class="form-group" style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Harga Satuan</label>
            <input name="unit_price" type="number" min="0" step="0.01" value="{{ old('unit_price', $item['unit_price']) }}" placeholder="Harga" required>
        </div>

        <div class="form-group" style="display: flex; flex-direction: column; gap: 4px; grid-column: span 1;">
            <!-- Empty column to balance row grid -->
        </div>

        <div class="form-group" style="grid-column: span 3; display: flex; flex-direction: column; gap: 8px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Foto Barang</label>
            
            @if (!empty($item['image_url']))
                <div style="display: flex; align-items: center; gap: 12px; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 4px;">
                    <a href="{{ $item['image_url'] }}" target="_blank" title="Buka gambar asli" style="cursor: zoom-in;">
                        <img src="{{ $item['image_url'] }}" alt="" style="width: 48px; height: 48px; object-fit: cover; border-radius: 4px; border: 1px solid #cbd5e1;">
                    </a>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <input type="checkbox" name="remove_image" id="remove_image" value="1" style="width: auto; cursor: pointer;">
                        <label for="remove_image" style="font-size: 13px; color: #ef4444; font-weight: 600; cursor: pointer;">Hapus foto saat ini</label>
                    </div>
                </div>
            @endif
            
            <input type="file" name="image" accept="image/*" style="border: 1px dashed #cbd5e1; padding: 8px; border-radius: 6px; cursor: pointer;">
            <span style="font-size: 11px; color: #64748b;">Pilih file baru jika ingin mengganti foto.</span>
        </div>

        <div class="form-group" style="grid-column: span 2; display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Catatan</label>
            <textarea name="notes" placeholder="Catatan" style="height: 100%; min-height: 110px;">{{ old('notes', $item['notes']) }}</textarea>
        </div>

        <div style="grid-column: span 5; display: flex; gap: 12px; margin-top: 16px;">
            <button class="button primary" type="submit">Simpan Perubahan</button>
            <a class="button" href="{{ route('items.show', $item['id']) }}">Batal</a>
        </div>
    </form>
</section>
@endsection
