@extends('layouts.app')

@section('content')
<header class="page-head">
    <div>
        <p class="eyebrow">CRM WhatsApp Tool</p>
        <h1>Template Hubungan Pelanggan</h1>
    </div>
    <a class="button secondary" href="{{ route('crm.index') }}">
        ⬅️ Database Pelanggan
    </a>
</header>

@if ($order)
    <!-- Ringkasan Order Terpilih -->
    <section class="panel" style="border-color: rgba(56, 189, 248, 0.3); background: rgba(56, 189, 248, 0.02);">
        <div class="panel-head">
            <h2>Data Acuan untuk Template (Order: {{ $order->order_number }})</h2>
            <a href="{{ route('orders.index') }}" class="button" style="padding: 4px 10px; min-height: auto; font-size: 0.8rem;">Ganti Order</a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 0.92rem;">
            <div>
                <span style="color: var(--muted); display: block;">Pelanggan</span>
                <strong>{{ $order->customer_name }} ({{ $order->customer_phone }})</strong>
            </div>
            <div>
                <span style="color: var(--muted); display: block;">Ekspedisi</span>
                <strong style="text-transform: uppercase;">{{ $order->courier }} - {{ $order->shipping_service }}</strong>
            </div>
            <div>
                <span style="color: var(--muted); display: block;">No. Resi AWB</span>
                <strong>{{ $order->waybill ?? 'Belum diterbitkan' }}</strong>
            </div>
            <div>
                <span style="color: var(--muted); display: block;">Status</span>
                <span class="badge badge-{{ $order->status }}" style="margin-top: 2px;">{{ $order->status }}</span>
            </div>
        </div>
    </section>

    <!-- Daftar Template -->
    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
        @foreach ($formattedTemplates as $key => $tpl)
            <article class="panel" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%; margin-bottom: 0;">
                <div>
                    <div class="panel-head" style="margin-bottom: 12px;">
                        <h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.15rem; color: var(--accent);">
                            {{ $tpl['title'] }}
                        </h3>
                    </div>
                    
                    <div style="background: #080b15; border: 1px solid var(--line); border-radius: 8px; padding: 14px; font-family: monospace; font-size: 0.88rem; line-height: 1.5; color: #d1d5db; min-height: 140px; white-space: pre-wrap; margin-bottom: 18px;">{{ $tpl['formatted'] }}</div>
                </div>

                <a href="{{ $tpl['wa_link'] }}" target="_blank" class="button success" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 700;">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                        <path d="M12.012 2c-5.506 0-9.988 4.482-9.988 9.988 0 1.761.459 3.475 1.332 4.992L2 22l5.161-1.355a9.927 9.927 0 0 0 4.851 1.258c5.506 0 9.988-4.482 9.988-9.988C22 6.482 17.518 2 12.012 2zm0 18.286c-1.579 0-3.13-.418-4.485-1.21l-.322-.188-3.327.873.889-3.238-.207-.33a8.238 8.238 0 0 1-1.261-4.305c0-4.568 3.717-8.286 8.286-8.286 4.568 0 8.286 3.717 8.286 8.286 0 4.568-3.717 8.286-8.286 8.286zm4.551-6.236c-.249-.125-1.474-.727-1.702-.81-.229-.083-.395-.125-.561.125-.166.249-.643.81-.788.975-.145.166-.29.187-.539.062a6.793 6.793 0 0 1-2.002-1.233c-.764-.681-1.28-1.522-1.43-1.772-.149-.249-.016-.384.109-.508.112-.112.249-.29.373-.435.124-.145.166-.249.249-.415.083-.166.042-.311-.021-.435-.062-.125-.561-1.35-.768-1.85-.202-.488-.406-.421-.561-.428-.145-.007-.311-.007-.478-.007a.916.916 0 0 0-.664.311c-.229.249-.872.852-.872 2.077 0 1.225.893 2.409 1.018 2.575.125.166 1.756 2.682 4.256 3.761.595.257 1.058.41 1.419.524.598.19 1.142.163 1.573.099.48-.072 1.474-.602 1.681-1.184.207-.582.207-1.079.145-1.184-.062-.104-.229-.166-.478-.291z"/>
                    </svg>
                    Kirim WA Web
                </a>
            </article>
        @endforeach
    </div>
@else
    <!-- Tampilan Jika Belum Memilih Order -->
    <section class="panel">
        <div class="panel-head">
            <h2>Pilih Order Terlebih Dahulu</h2>
        </div>
        
        <p style="font-size: 0.92rem; color: var(--muted); margin-bottom: 20px;">
            Silakan pilih salah satu pesanan aktif di bawah ini untuk memuat data pelanggan secara otomatis ke dalam template pesan WhatsApp follow-up.
        </p>

        @php
            $recentOrders = \App\Models\Order::latest()->take(15)->get();
        @endphp

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Pelanggan</th>
                        <th>Ekspedisi</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentOrders as $ro)
                        <tr>
                            <td>
                                <strong>{{ $ro->order_number }}</strong>
                                <small style="display: block; color: var(--muted);">{{ $ro->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <strong>{{ $ro->customer_name }}</strong>
                                <small style="display: block; color: var(--muted);">{{ $ro->customer_phone }}</small>
                            </td>
                            <td>
                                <span style="text-transform: uppercase;">{{ $ro->courier }} - {{ $ro->shipping_service }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $ro->status }}">{{ $ro->status }}</span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('crm.templates', ['order_id' => $ro->id]) }}" class="button primary" style="padding: 6px 12px; min-height: auto; font-size: 0.85rem;">
                                    📝 Muat Template
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty">Tidak ada order yang tersedia untuk CRM. Silakan buat simulasi order terlebih dahulu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endif
@endsection
