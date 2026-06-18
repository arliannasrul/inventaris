@extends('layouts.app')

@section('content')
<header class="page-head">
    <div>
        <p class="eyebrow">Customer Relationship</p>
        <h1>CRM & Database Pelanggan</h1>
    </div>
    <a class="button secondary" href="{{ route('orders.index') }}">
        🚚 Kelola Pengiriman
    </a>
</header>

<section class="panel">
    <div class="panel-head">
        <h2>Database Hubungan Pelanggan</h2>
    </div>

    <p style="font-size: 0.9rem; color: var(--muted); margin-bottom: 20px;">
        Daftar di bawah ini mengelompokkan riwayat pembelian pelanggan berdasarkan nomor telepon untuk membantu Anda memantau *Lifetime Value* (LTV) dan loyalitas pelanggan.
    </p>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>No. Telepon / WA</th>
                    <th>Total Transaksi</th>
                    <th>Total Belanja (LTV)</th>
                    <th>Pembelian Terakhir</th>
                    <th style="text-align: right;">Aksi CRM</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td>
                            <strong>{{ $customer['customer_name'] }}</strong>
                            <small style="display: block; color: var(--muted); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $customer['customer_address'] }}
                            </small>
                        </td>
                        <td>
                            <code style="background: rgba(255,255,255,0.06); padding: 4px 8px; border-radius: 4px; color: var(--accent);">
                                {{ $customer['customer_phone'] }}
                            </code>
                        </td>
                        <td style="font-weight: 700; font-size: 1.05rem;">
                            {{ $customer['total_orders'] }} Order
                        </td>
                        <td style="font-weight: 700; color: var(--accent);">
                            Rp {{ number_format($customer['total_spent'], 0, ',', '.') }}
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($customer['last_order_date'])->format('d M Y H:i') }}
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('crm.templates', ['order_id' => \App\Models\Order::where('customer_phone', $customer['customer_phone'])->latest()->first()->id]) }}" class="button primary" style="padding: 6px 12px; min-height: auto; font-size: 0.85rem;">
                                💬 Follow Up Terakhir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">Belum ada data pelanggan. Pelanggan otomatis tercatat ketika ada simulasi pesanan dibuat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
