@extends('layouts.app')

@section('content')
<header class="page-head">
    <div>
        <p class="eyebrow">Detail Transaksi</p>
        <h1>Order {{ $order->order_number }}</h1>
    </div>
    <div style="display: flex; gap: 8px;">
        <a class="button secondary" href="{{ route('orders.index') }}">⬅️ Kembali</a>
        <button class="button primary" onclick="window.print()">🖨️ Cetak Label</button>
    </div>
</header>

<div class="grid two">
    <!-- Detail Informasi -->
    <section class="panel">
        <div class="panel-head">
            <h2>Status & Rincian Pesanan</h2>
        </div>
        
        <div class="stack" style="gap: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--line);">
                <span style="color: var(--muted); font-weight: 500;">Status Transaksi</span>
                <span class="badge badge-{{ $order->status }}">
                    {{ $order->status === 'pending' ? 'Baru (Menunggu Proses)' : ($order->status === 'shipping' ? 'Dalam Pengiriman' : ($order->status === 'delivered' ? 'Selesai' : $order->status)) }}
                </span>
            </div>

            <div>
                <strong style="display: block; font-size: 0.85rem; color: var(--muted); margin-bottom: 4px; text-transform: uppercase;">Pelanggan</strong>
                <div style="font-size: 1.05rem; font-weight: 700;">{{ $order->customer_name }}</div>
                <div style="color: var(--accent); font-weight: 600; margin-top: 2px;">{{ $order->customer_phone }}</div>
                <div style="color: var(--muted); margin-top: 4px; line-height: 1.4; font-size: 0.95rem;">
                    {{ $order->customer_address }}<br>
                    <strong>Kota:</strong> {{ $order->destination_city_name }}
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 14px; background: rgba(255,255,255,0.02); border: 1px solid var(--line); border-radius: 8px;">
                <div>
                    <strong style="display: block; font-size: 0.75rem; color: var(--muted); text-transform: uppercase; margin-bottom: 2px;">Kurir Ekspedisi</strong>
                    <span style="font-weight: 700; text-transform: uppercase; color: #fff;">{{ $order->courier }} ({{ $order->shipping_service }})</span>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.75rem; color: var(--muted); text-transform: uppercase; margin-bottom: 2px;">Berat Paket</strong>
                    <span style="font-weight: 700; color: #fff;">{{ number_format($order->weight_grams, 0, ',', '.') }} gram</span>
                </div>
            </div>

            @if ($order->waybill)
                <div style="padding: 14px; background: rgba(56, 189, 248, 0.05); border: 1px dashed rgba(56, 189, 248, 0.3); border-radius: 8px;">
                    <strong style="display: block; font-size: 0.78rem; color: #38bdf8; text-transform: uppercase; margin-bottom: 4px;">Nomor Resi AWB (Kiriminaja)</strong>
                    <code style="font-size: 1.15rem; font-weight: 700; color: #fff; font-family: monospace;">{{ $order->waybill }}</code>
                </div>
            @endif

            <div>
                <strong style="display: block; font-size: 0.85rem; color: var(--muted); margin-bottom: 8px; text-transform: uppercase;">Daftar Produk</strong>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th style="text-align: center;">Kuantitas</th>
                                <th style="text-align: right;">Harga Satuan</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $itemsSubtotal = 0; @endphp
                            @foreach ($order->items as $item)
                                @php 
                                    $sub = $item->pivot->quantity * $item->pivot->price; 
                                    $itemsSubtotal += $sub;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $item->name }}</strong><br>
                                        <small style="color: var(--muted);">SKU: {{ $item->sku }}</small>
                                    </td>
                                    <td style="text-align: center; font-weight: 700;">{{ $item->pivot->quantity }}</td>
                                    <td style="text-align: right;">Rp {{ number_format($item->pivot->price, 0, ',', '.') }}</td>
                                    <td style="text-align: right; font-weight: 700;">Rp {{ number_format($sub, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr style="background: rgba(0,0,0,0.15); font-weight: bold;">
                                <td colspan="3" style="text-align: right;">Total Produk</td>
                                <td style="text-align: right;">Rp {{ number_format($itemsSubtotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr style="font-weight: bold; border-bottom: none;">
                                <td colspan="3" style="text-align: right; color: var(--accent);">Ongkos Kirim</td>
                                <td style="text-align: right; color: var(--accent);">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                            </tr>
                            <tr style="background: rgba(6, 182, 212, 0.05); font-size: 1.1rem; font-weight: 800;">
                                <td colspan="3" style="text-align: right;">Total Transaksi</td>
                                <td style="text-align: right; color: var(--accent);">Rp {{ number_format($itemsSubtotal + $order->shipping_cost, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Printable Label Preview -->
    <div>
        <section class="panel" style="background: white; color: black; border-color: #000; box-shadow: none;">
            <div class="panel-head" style="border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 16px;">
                <h2 style="color: black; font-family: sans-serif;">Preview Label Pengiriman</h2>
            </div>
            
            <div class="label-container" style="border: 2px solid #000; background: white; color: black; padding: 15px; margin: 0; box-sizing: border-box;">
                <div class="label-header" style="display: flex; justify-content: space-between; border-bottom: 2px dashed #000; padding-bottom: 8px; font-weight: bold; font-size: 0.9rem;">
                    <div>MITRASPACE LOGISTICS</div>
                    <div style="text-transform: uppercase; font-size: 1.1rem;">{{ $order->courier }} {{ $order->shipping_service }}</div>
                </div>

                @if ($order->waybill)
                    <div style="text-align: center; margin: 12px 0;">
                        <!-- Mocking a barcode visual in pure HTML -->
                        <div style="display: inline-flex; height: 40px; margin-bottom: 4px; width: 100%; max-width: 250px; background: repeating-linear-gradient(90deg, #000, #000 2px, #fff 2px, #fff 6px);"></div>
                        <div style="font-family: monospace; font-weight: bold; font-size: 1.05rem; letter-spacing: 2px;">{{ $order->waybill }}</div>
                    </div>
                @else
                    <div style="text-align: center; border: 1px dashed #000; padding: 15px; margin: 12px 0; font-style: italic; font-weight: bold; font-size: 0.9rem;">
                        [No Resi Belum Diterbitkan]
                    </div>
                @endif

                <div class="label-body" style="font-size: 0.85rem; line-height: 1.4; border-top: 2px dashed #000; padding-top: 10px; font-family: monospace;">
                    <div class="label-row" style="margin-bottom: 8px;">
                        <strong>Penerima:</strong><br>
                        <span style="font-size: 1rem; font-weight: bold;">{{ $order->customer_name }}</span><br>
                        {{ $order->customer_phone }}<br>
                        {{ $order->customer_address }}<br>
                        Kota: {{ $order->destination_city_name }}
                    </div>

                    <div class="label-row" style="border-top: 1px solid #000; padding-top: 6px; margin-bottom: 8px;">
                        <strong>Pengirim:</strong><br>
                        <span style="font-weight: bold;">MitraSpace Seller Center</span> (Internal)<br>
                        Sleman, D.I. Yogyakarta
                    </div>

                    <div class="label-row" style="border-top: 1px dashed #000; padding-top: 6px; font-size: 0.8rem;">
                        <strong>Isi Paket:</strong><br>
                        @foreach ($order->items as $item)
                            - {{ $item->name }} (x{{ $item->pivot->quantity }})<br>
                        @endforeach
                        <strong>Total Berat:</strong> {{ $order->weight_grams }}g
                    </div>
                </div>
            </div>
            
            <p style="font-size: 0.75rem; color: #555; text-align: center; margin-top: 12px; font-style: italic;">
                * Gunakan tombol 'Cetak Label' di bagian atas untuk mencetak label fisik.
            </p>
        </section>
    </div>
</div>
@endsection
