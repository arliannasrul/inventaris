@extends('layouts.app')

@section('content')
<header class="page-head">
    <div>
        <p class="eyebrow">Logistik & Pengiriman</p>
        <h1>Lacak Paket #{{ $order->order_number }}</h1>
    </div>
    <a class="button secondary" href="{{ route('orders.index') }}">
        ⬅️ Kembali ke Order
    </a>
</header>

<div class="grid two" style="grid-template-columns: 0.8fr 1.2fr;">
    <!-- Info Ringkasan Resi -->
    <section class="panel">
        <div class="panel-head">
            <h2>Informasi Pengiriman</h2>
        </div>
        
        <div class="stack" style="gap: 16px;">
            <div>
                <label style="color: var(--muted); font-size: 0.8rem; text-transform: uppercase;">Nomor Resi AWB</label>
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--accent); margin-top: 4px;">{{ $trackingData['awb'] }}</div>
            </div>

            <div>
                <label style="color: var(--muted); font-size: 0.8rem; text-transform: uppercase;">Ekspedisi</label>
                <div style="font-size: 1rem; font-weight: 600; text-transform: uppercase; margin-top: 2px;">{{ $order->courier }} ({{ $order->shipping_service }})</div>
            </div>

            <div>
                <label style="color: var(--muted); font-size: 0.8rem; text-transform: uppercase;">Status Terakhir</label>
                <div style="margin-top: 4px;">
                    <span class="badge badge-shipping" style="text-transform: uppercase;">
                        {{ $trackingData['status'] }}
                    </span>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--line); margin: 8px 0;">

            <div>
                <label style="color: var(--muted); font-size: 0.8rem; text-transform: uppercase;">Penerima</label>
                <div style="font-weight: 700; font-size: 1rem; margin-top: 2px;">{{ $order->customer_name }}</div>
                <div style="color: var(--muted); font-size: 0.9rem; margin-top: 2px;">{{ $order->customer_phone }}</div>
                <div style="color: var(--muted); font-size: 0.85rem; margin-top: 4px; line-height: 1.4;">{{ $order->customer_address }}</div>
            </div>
        </div>
    </section>

    <!-- Timeline Progress -->
    <section class="panel">
        <div class="panel-head">
            <h2>Riwayat Pengiriman (Real-time Timeline)</h2>
        </div>

        <div style="position: relative; padding-left: 32px; margin-top: 10px;">
            <!-- Vertical Line -->
            <div style="position: absolute; left: 11px; top: 8px; bottom: 8px; width: 2px; background: var(--line);"></div>

            @forelse (($trackingData['history'] ?? []) as $checkpoint)
                <div style="position: relative; margin-bottom: 24px;">
                    <!-- Timeline Node Dot -->
                    @if ($loop->first)
                        <div style="position: absolute; left: -27px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 10px var(--accent-glow); border: 2px solid var(--panel);"></div>
                    @else
                        <div style="position: absolute; left: -26px; top: 5px; width: 10px; height: 10px; border-radius: 50%; background: var(--muted); border: 2px solid var(--panel);"></div>
                    @endif

                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <strong style="color: {{ $loop->first ? 'var(--accent)' : 'var(--ink)' }}; font-size: 0.95rem;">
                                {{ $checkpoint['description'] }}
                            </strong>
                            <small style="color: var(--muted); font-size: 0.8rem; margin-left: 8px;">
                                {{ \Carbon\Carbon::parse($checkpoint['date'])->format('d M, H:i') }}
                            </small>
                        </div>
                        <div style="color: var(--muted); font-size: 0.85rem; display: flex; align-items: center; gap: 4px;">
                            <span>📍</span> {{ $checkpoint['location'] }}
                        </div>
                    </div>
                </div>
            @empty
                <p class="empty" style="padding: 10px !important;">Belum ada riwayat pergerakan paket. Silakan tunggu update kurir.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
