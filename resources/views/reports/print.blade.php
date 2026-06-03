<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Laporan Inventaris</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="print-page" onload="window.print()">
    <main class="print-sheet">
        <header class="print-head">
            <div>
                <h1>Laporan Inventaris Barang</h1>
                <p>Dicetak {{ now()->format('d/m/Y H:i') }} WIB</p>
            </div>
            <strong>Arlian6A1</strong>
        </header>

        <section class="stats print-stats">
            <article><span>Total barang</span><strong>{{ $data['summary']['items'] ?? 0 }}</strong></article>
            <article><span>Total stok</span><strong>{{ $data['summary']['stock'] ?? 0 }}</strong></article>
            <article><span>Masuk</span><strong>{{ $data['summary']['in'] ?? 0 }}</strong></article>
            <article><span>Keluar</span><strong>{{ $data['summary']['out'] ?? 0 }}</strong></article>
        </section>

        <table>
            <thead><tr><th>Tanggal</th><th>Barang</th><th>Tipe</th><th>Jumlah</th><th>Petugas</th><th>Catatan</th></tr></thead>
            <tbody>
            @foreach (($data['movements'] ?? []) as $movement)
                <tr>
                    <td>{{ \Illuminate\Support\Str::of($movement['created_at'])->substr(0, 10) }}</td>
                    <td>{{ $movement['item']['name'] ?? '-' }}</td>
                    <td>{{ $movement['type'] }}</td>
                    <td>{{ $movement['quantity'] }}</td>
                    <td>{{ $movement['actor'] }}</td>
                    <td>{{ $movement['notes'] ?? '-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </main>
</body>
</html>
