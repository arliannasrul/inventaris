@extends('layouts.app')

@section('content')
<header class="page-head">
    <div>
        <p class="eyebrow">Laporan</p>
        <h1>Inventaris dan Pergerakan Stok</h1>
    </div>
    <a class="button primary" href="{{ route('reports.print', request()->query()) }}">Cetak</a>
</header>

<section class="panel">
    <form class="filters" method="get">
        <input name="from" type="date" value="{{ $filters['from'] ?? '' }}">
        <input name="to" type="date" value="{{ $filters['to'] ?? '' }}">
        <input name="category" value="{{ $filters['category'] ?? '' }}" placeholder="Kategori">
        <input name="location" value="{{ $filters['location'] ?? '' }}" placeholder="Lokasi">
        <button class="button" type="submit">Terapkan</button>
    </form>
</section>

<section class="stats">
    <article><span>Total barang</span><strong>{{ $data['summary']['items'] ?? 0 }}</strong></article>
    <article><span>Total stok</span><strong>{{ $data['summary']['stock'] ?? 0 }}</strong></article>
    <article><span>Barang masuk</span><strong>{{ $data['summary']['in'] ?? 0 }}</strong></article>
    <article><span>Barang keluar</span><strong>{{ $data['summary']['out'] ?? 0 }}</strong></article>
</section>

<section class="panel">
    <h2>Pergerakan</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tanggal</th><th>Barang</th><th>Tipe</th><th>Jumlah</th><th>Petugas</th><th>Referensi</th></tr></thead>
            <tbody>
            @forelse (($data['movements'] ?? []) as $movement)
                <tr>
                    <td>{{ \Illuminate\Support\Str::of($movement['createdAt'])->substr(0, 10) }}</td>
                    <td>{{ $movement['item']['name'] ?? '-' }}</td>
                    <td>{{ $movement['type'] }}</td>
                    <td>{{ $movement['quantity'] }}</td>
                    <td>{{ $movement['actor'] }}</td>
                    <td>{{ $movement['reference'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">Tidak ada data pada filter ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
