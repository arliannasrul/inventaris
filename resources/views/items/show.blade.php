@extends('layouts.app')

@section('content')
<header class="page-head">
    <div>
        <p class="eyebrow">{{ $item['sku'] }}</p>
        <h1>{{ $item['name'] }}</h1>
    </div>
    <a class="button" href="{{ route('items.index') }}">Kembali</a>
</header>

<section class="stats">
    <article><span>Stok</span><strong>{{ $item['quantity'] }} {{ $item['unit'] }}</strong></article>
    <article><span>Minimum</span><strong>{{ $item['minimumStock'] }}</strong></article>
    <article><span>Lokasi</span><strong>{{ $item['location'] }}</strong></article>
    <article><span>Nilai</span><strong>Rp {{ number_format(($item['quantity'] ?? 0) * ($item['unitPrice'] ?? 0), 0, ',', '.') }}</strong></article>
</section>

<section class="grid two">
    <div class="panel">
        <h2>Catat Pergerakan</h2>
        <form class="stack" method="post" action="{{ route('movements.store', $item['id']) }}">
            @csrf
            <select name="type" required>
                <option value="IN">Barang masuk</option>
                <option value="OUT">Barang keluar</option>
                <option value="DAMAGED">Rusak</option>
                <option value="ADJUSTMENT">Penyesuaian</option>
            </select>
            <input name="quantity" type="number" min="1" placeholder="Jumlah" required>
            <input name="reference" placeholder="Referensi dokumen">
            <input name="actor" placeholder="Dicatat oleh" value="Operator" required>
            <textarea name="notes" placeholder="Catatan"></textarea>
            <button class="button primary" type="submit">Catat stok</button>
        </form>
    </div>

    <div class="panel">
        <h2>Komunikasi</h2>
        <form class="stack" method="post" action="{{ route('messages.store', $item['id']) }}">
            @csrf
            <input name="author" placeholder="Nama" value="Operator" required>
            <textarea name="message" placeholder="Tulis pesan atau tindak lanjut" required></textarea>
            <button class="button primary" type="submit">Kirim pesan</button>
        </form>
    </div>
</section>

<section class="grid two">
    <div class="panel">
        <h2>Riwayat Stok</h2>
        <ul class="activity">
            @forelse (($item['movements'] ?? []) as $movement)
                <li><strong>{{ $movement['type'] }} {{ $movement['quantity'] }}</strong><span>{{ $movement['actor'] }} - {{ $movement['reference'] ?? 'Tanpa referensi' }}</span></li>
            @empty
                <li class="empty">Belum ada riwayat.</li>
            @endforelse
        </ul>
    </div>
    <div class="panel">
        <h2>Thread Pesan</h2>
        <ul class="activity">
            @forelse (($item['messages'] ?? []) as $message)
                <li><strong>{{ $message['author'] }}</strong><span>{{ $message['message'] }}</span></li>
            @empty
                <li class="empty">Belum ada pesan.</li>
            @endforelse
        </ul>
    </div>
</section>
@endsection
