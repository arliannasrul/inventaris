@extends('layouts.app')

@section('content')
<style>
/* ======================================================
   PREMIUM PAGE — Light Theme Compatible
   ====================================================== */
.premium-hero {
    text-align: center;
    padding: 48px 24px 32px;
}

.premium-hero h1 {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 800;
    background: linear-gradient(135deg, #d97706 0%, #ea580c 50%, #db2777 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 12px;
    line-height: 1.2;
}

.premium-hero p {
    font-size: 1.1rem;
    color: #6c7680;
    max-width: 520px;
    margin: 0 auto;
}

/* Status banner untuk user premium */
.premium-status-banner {
    display: flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, rgba(217,119,6,0.1), rgba(234,88,12,0.07));
    border: 1px solid rgba(217,119,6,0.35);
    border-radius: 12px;
    padding: 16px 20px;
    margin: 0 0 32px;
}

.premium-status-banner .badge-icon { font-size: 2rem; flex-shrink: 0; }

.premium-status-banner strong {
    display: block;
    color: #92400e;
    font-size: 1rem;
}

.premium-status-banner span {
    font-size: 0.85rem;
    color: #6c7680;
}

/* Pricing cards grid */
.pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin: 0 0 48px;
}

.pricing-card {
    background: #ffffff;
    border: 1.5px solid #dfe4ea;
    border-radius: 20px;
    padding: 32px 28px;
    position: relative;
    transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 12px rgba(17,24,39,0.06);
}

.pricing-card:hover {
    transform: translateY(-4px);
    border-color: #bdc5ce;
    box-shadow: 0 16px 48px rgba(17,24,39,0.12);
}

.pricing-card.featured {
    background: linear-gradient(160deg, #fffbeb 0%, #fff7ed 100%);
    border: 2px solid #f59e0b;
    box-shadow: 0 4px 20px rgba(245,158,11,0.15);
}

.pricing-card.featured:hover {
    box-shadow: 0 16px 48px rgba(245,158,11,0.2);
}

.pricing-card .popular-badge {
    position: absolute;
    top: -14px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #f59e0b, #ea580c);
    color: #fff;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 4px 16px;
    border-radius: 20px;
    letter-spacing: 0.5px;
    white-space: nowrap;
    box-shadow: 0 3px 10px rgba(245,158,11,0.4);
}

/* Plan name */
.pricing-card .plan-name {
    font-size: 0.8rem;
    font-weight: 700;
    color: #6c7680;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    margin: 0 0 14px;
}

.pricing-card.featured .plan-name {
    color: #b45309;
}

/* Plan price */
.pricing-card .plan-price { margin: 0 0 6px; }

.pricing-card .plan-price .currency {
    font-size: 1.1rem;
    font-weight: 700;
    color: #6c7680;
    vertical-align: top;
    margin-top: 10px;
    display: inline-block;
}

.pricing-card .plan-price .amount {
    font-size: 3rem;
    font-weight: 800;
    color: #16202a;
    line-height: 1;
}

.pricing-card.featured .plan-price .amount {
    background: linear-gradient(135deg, #d97706, #ea580c);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.pricing-card .plan-period {
    font-size: 0.85rem;
    color: #6c7680;
    margin: 0 0 24px;
}

/* Feature list */
.pricing-card .feature-list {
    list-style: none;
    padding: 0;
    margin: 0 0 28px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pricing-card .feature-list li {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 0.88rem;
    color: #374151;
}

.pricing-card .feature-list li strong { color: #16202a; }

.pricing-card .feature-list li .check {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: rgba(25,115,107,0.1);
    border: 1px solid rgba(25,115,107,0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
    color: #19736b;
    font-size: 11px;
}

.pricing-card .feature-list li .cross {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: rgba(108,118,128,0.1);
    border: 1px solid rgba(108,118,128,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
    color: #9ca3af;
    font-size: 11px;
}

.feature-list li.muted { color: #9ca3af; }

/* Buttons */
.btn-upgrade {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
    text-decoration: none;
    display: block;
    font-family: inherit;
}

.btn-upgrade-primary {
    background: linear-gradient(135deg, #f59e0b, #ea580c);
    color: #fff;
    box-shadow: 0 4px 16px rgba(245,158,11,0.35);
}

.btn-upgrade-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(245,158,11,0.45);
    color: #fff;
}

.btn-upgrade-secondary {
    background: #f6f7f9;
    color: #6c7680;
    border: 1.5px solid #dfe4ea;
}

.btn-upgrade-secondary:hover {
    background: #edf0f3;
    color: #374151;
}

/* Features comparison table */
.features-section { margin: 0 0 48px; }

.features-section h2 {
    font-size: 1.4rem;
    font-weight: 700;
    color: #16202a;
    margin: 0 0 20px;
    text-align: center;
}

.features-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
    background: #fff;
}

.features-table th {
    padding: 12px 16px;
    text-align: center;
    font-weight: 700;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c7680;
    background: #f6f7f9;
    border-bottom: 1px solid #dfe4ea;
}

.features-table th:first-child {
    text-align: left;
    color: #16202a;
}

.features-table th.premium-col { color: #b45309; }

.features-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #dfe4ea;
    color: #374151;
    text-align: center;
}

.features-table td:first-child {
    text-align: left;
    color: #374151;
    font-weight: 500;
}

.features-table tr:last-child td { border-bottom: none; }
.features-table tr:hover td { background: #f9fafb; }

.check-icon { color: #19736b; font-size: 1rem; }
.cross-icon { color: #d1d5db; font-size: 1rem; }
.gold-icon  { color: #d97706; font-size: 0.88rem; font-weight: 700; }

/* Trust badges */
.trust-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin: 0 0 48px;
}

.trust-item {
    text-align: center;
    padding: 20px;
    background: #fff;
    border: 1px solid #dfe4ea;
    border-radius: 14px;
}

.trust-item strong {
    display: block;
    color: #16202a;
    margin-bottom: 4px;
    font-size: 0.9rem;
}

.trust-item small { color: #6c7680; font-size: 0.8rem; }

/* Payment history */
.payment-history {
    background: #fff;
    border: 1px solid #dfe4ea;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(17,24,39,0.06);
    margin-bottom: 48px;
}

.payment-history h3 {
    font-size: 1rem;
    font-weight: 700;
    color: #16202a;
    padding: 16px 20px;
    border-bottom: 1px solid #dfe4ea;
    margin: 0;
    background: #f6f7f9;
}

.payment-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    border-bottom: 1px solid #f0f2f5;
    font-size: 0.85rem;
}

.payment-row:last-child { border-bottom: none; }

.payment-row .p-status {
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    margin-left: auto;
    flex-shrink: 0;
}

.p-status.paid    { background: rgba(25,115,107,0.1);  color: #19736b; }
.p-status.pending { background: rgba(180,93,20,0.1);   color: #b45d14; }
.p-status.failed  { background: rgba(166,50,50,0.1);   color: #a63232; }
.p-status.expired { background: rgba(108,118,128,0.1); color: #6c7680; }
</style>

<div style="max-width: 920px; margin: 0 auto; padding: 0 16px;">

    {{-- Hero --}}
    <div class="premium-hero">
        <h1>✨ Upgrade ke Premium</h1>
        <p>Buka semua fitur canggih untuk manajemen inventaris yang lebih profesional</p>
    </div>

    {{-- Flash Messages --}}
    @if (session('error'))
        <div class="error" style="margin-bottom: 20px;">{{ session('error') }}</div>
    @endif

    {{-- Status Banner jika sudah Premium --}}
    @if ($user && $user->isPremium())
        <div class="premium-status-banner">
            <div class="badge-icon">👑</div>
            <div>
                <strong>Anda sudah berlangganan Premium!</strong>
                <span>
                    Paket {{ $user->premium_plan === 'yearly' ? 'Tahunan' : 'Bulanan' }}
                    — aktif hingga
                    {{ $user->premium_expires_at ? $user->premium_expires_at->translatedFormat('d F Y') : 'selamanya' }}
                </span>
            </div>
        </div>
    @endif

    {{-- Pricing Cards --}}
    <div class="pricing-grid">

        {{-- Gratis --}}
        <div class="pricing-card">
            <div class="plan-name">Gratis</div>
            <div class="plan-price">
                <span class="currency">Rp</span>
                <span class="amount">0</span>
            </div>
            <div class="plan-period">Selamanya</div>
            <ul class="feature-list">
                <li><span class="check">✓</span> Maksimal 50 item barang</li>
                <li><span class="check">✓</span> Pencatatan stok masuk/keluar</li>
                <li><span class="check">✓</span> Notifikasi stok rendah</li>
                <li><span class="check">✓</span> Login dengan Google</li>
                <li class="muted"><span class="cross">✗</span> Laporan & Cetak PDF</li>
                <li class="muted"><span class="cross">✗</span> Upload foto barang</li>
                <li class="muted"><span class="cross">✗</span> Audit log lengkap</li>
                <li class="muted"><span class="cross">✗</span> Ekspor data</li>
                <li class="muted"><span class="cross">✗</span> Item tak terbatas</li>
            </ul>
            <span class="btn-upgrade btn-upgrade-secondary">Paket Saat Ini</span>
        </div>

        {{-- Premium Bulanan --}}
        <div class="pricing-card">
            <div class="plan-name">Premium Bulanan</div>
            <div class="plan-price">
                <span class="currency">Rp</span>
                <span class="amount">99k</span>
            </div>
            <div class="plan-period">per bulan</div>
            <ul class="feature-list">
                <li><span class="check">✓</span> <strong>Item tidak terbatas</strong></li>
                <li><span class="check">✓</span> Laporan lengkap & Cetak PDF</li>
                <li><span class="check">✓</span> Upload foto barang (Cloudinary)</li>
                <li><span class="check">✓</span> Audit log penuh</li>
                <li><span class="check">✓</span> Ekspor data ke Excel/CSV</li>
                <li><span class="check">✓</span> Notifikasi tanpa batas</li>
                <li><span class="check">✓</span> Prioritas dukungan</li>
                <li><span class="check">✓</span> Semua fitur Gratis</li>
            </ul>
            @if ($user && $user->isPremium())
                <form method="POST" action="{{ route('premium.checkout') }}">
                    @csrf
                    <input type="hidden" name="plan" value="monthly">
                    <button type="submit" class="btn-upgrade btn-upgrade-primary">Perpanjang Bulanan</button>
                </form>
            @else
                <form method="POST" action="{{ route('premium.checkout') }}">
                    @csrf
                    <input type="hidden" name="plan" value="monthly">
                    <button type="submit" class="btn-upgrade btn-upgrade-primary">Mulai Sekarang →</button>
                </form>
            @endif
        </div>

        {{-- Premium Tahunan (Featured) --}}
        <div class="pricing-card featured">
            <div class="popular-badge">🔥 TERPOPULER — HEMAT 24%</div>
            <div class="plan-name">Premium Tahunan</div>
            <div class="plan-price">
                <span class="currency">Rp</span>
                <span class="amount">899k</span>
            </div>
            <div class="plan-period">per tahun <span style="color: #22c55e; font-weight: 600;">(hemat Rp 289k)</span></div>
            <ul class="feature-list">
                <li><span class="check">✓</span> <strong>Item tidak terbatas</strong></li>
                <li><span class="check">✓</span> Laporan lengkap & Cetak PDF</li>
                <li><span class="check">✓</span> Upload foto barang (Cloudinary)</li>
                <li><span class="check">✓</span> Audit log penuh</li>
                <li><span class="check">✓</span> Ekspor data ke Excel/CSV</li>
                <li><span class="check">✓</span> Notifikasi tanpa batas</li>
                <li><span class="check">✓</span> Prioritas dukungan</li>
                <li><span class="check">✓</span> Semua fitur Gratis</li>
            </ul>
            @if ($user && $user->isPremium())
                <form method="POST" action="{{ route('premium.checkout') }}">
                    @csrf
                    <input type="hidden" name="plan" value="yearly">
                    <button type="submit" class="btn-upgrade btn-upgrade-primary">Perpanjang Tahunan</button>
                </form>
            @else
                <form method="POST" action="{{ route('premium.checkout') }}">
                    @csrf
                    <input type="hidden" name="plan" value="yearly">
                    <button type="submit" class="btn-upgrade btn-upgrade-primary">Mulai Hemat Sekarang →</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Tabel Perbandingan Fitur --}}
    <div class="features-section">
        <h2>Perbandingan Fitur Lengkap</h2>
        <div style="overflow-x: auto; border-radius: 16px; border: 1px solid rgba(255,255,255,0.07);">
            <table class="features-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Fitur</th>
                        <th>Gratis</th>
                        <th class="premium-col">👑 Premium</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Jumlah item barang</td>
                        <td>Maks. 50</td>
                        <td class="gold-icon">∞ Tak terbatas</td>
                    </tr>
                    <tr>
                        <td>Pencatatan stok (IN/OUT/DAMAGED)</td>
                        <td><span class="check-icon">✓</span></td>
                        <td><span class="check-icon">✓</span></td>
                    </tr>
                    <tr>
                        <td>Notifikasi stok rendah & habis</td>
                        <td><span class="check-icon">✓</span></td>
                        <td><span class="check-icon">✓</span></td>
                    </tr>
                    <tr>
                        <td>Login dengan Google</td>
                        <td><span class="check-icon">✓</span></td>
                        <td><span class="check-icon">✓</span></td>
                    </tr>
                    <tr>
                        <td>Laporan & Cetak PDF</td>
                        <td><span class="cross-icon">✗</span></td>
                        <td><span class="check-icon">✓</span></td>
                    </tr>
                    <tr>
                        <td>Upload & tampilkan foto barang</td>
                        <td><span class="cross-icon">✗</span></td>
                        <td><span class="check-icon">✓</span></td>
                    </tr>
                    <tr>
                        <td>Audit log lengkap (semua perubahan)</td>
                        <td><span class="cross-icon">✗</span></td>
                        <td><span class="check-icon">✓</span></td>
                    </tr>
                    <tr>
                        <td>Ekspor data CSV/Excel</td>
                        <td><span class="cross-icon">✗</span></td>
                        <td><span class="check-icon">✓</span></td>
                    </tr>
                    <tr>
                        <td>Dukungan prioritas</td>
                        <td><span class="cross-icon">✗</span></td>
                        <td><span class="check-icon">✓</span></td>
                    </tr>
                    <tr>
                        <td>Metode pembayaran</td>
                        <td>—</td>
                        <td style="color: #6c7680; font-size: 0.8rem;">VA Bank, OVO, GoPay, Dana, Kartu Kredit</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Trust Badges --}}
    <div class="trust-grid">
        <div class="trust-item">
            <div style="font-size: 2rem; margin-bottom: 8px;">🔒</div>
            <strong>Pembayaran Aman</strong>
            <small>Diproses oleh DOKU, terdaftar dan diawasi Bank Indonesia</small>
        </div>
        <div class="trust-item">
            <div style="font-size: 2rem; margin-bottom: 8px;">⚡</div>
            <strong>Aktivasi Instan</strong>
            <small>Premium aktif otomatis setelah pembayaran berhasil</small>
        </div>
        <div class="trust-item">
            <div style="font-size: 2rem; margin-bottom: 8px;">🔄</div>
            <strong>Perpanjang Kapan Saja</strong>
            <small>Upgrade atau perpanjang langganan tanpa batas</small>
        </div>
        <div class="trust-item">
            <div style="font-size: 2rem; margin-bottom: 8px;">💳</div>
            <strong>Banyak Metode Bayar</strong>
            <small>VA Bank, e-wallet, kartu kredit/debit</small>
        </div>
    </div>

    {{-- Riwayat Pembayaran --}}
    @if ($payments->isNotEmpty())
        <div class="payment-history">
            <h3>📄 Riwayat Pembayaran</h3>
            @foreach ($payments as $payment)
                <div class="payment-row">
                    <div>
                        <strong style="color: #16202a; display: block; font-size: 0.88rem;">
                            {{ $payment->plan === 'yearly' ? 'Premium Tahunan' : 'Premium Bulanan' }}
                        </strong>
                        <small style="color: #6c7680;">
                            {{ $payment->order_id }} · {{ $payment->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                    <div style="text-align: right; flex-shrink: 0;">
                        <strong style="display: block; color: #16202a; font-size: 0.88rem;">
                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                        </strong>
                    </div>
                    <span class="p-status {{ $payment->status }}">{{ $payment->status }}</span>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
