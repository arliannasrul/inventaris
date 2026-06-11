@extends('layouts.app')

@section('content')
<style>
.success-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 70vh;
    text-align: center;
    padding: 40px 24px;
}

.success-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(34,197,94,0.2), rgba(16,185,129,0.1));
    border: 2px solid rgba(34,197,94,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    margin: 0 auto 28px;
    animation: pop-in 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

@keyframes pop-in {
    0%   { opacity: 0; transform: scale(0.5); }
    100% { opacity: 1; transform: scale(1); }
}

.confetti {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    pointer-events: none;
    overflow: hidden;
    z-index: 9999;
}

.confetti-piece {
    position: absolute;
    top: -20px;
    width: 10px;
    height: 10px;
    border-radius: 2px;
    animation: confetti-fall linear forwards;
}

@keyframes confetti-fall {
    0%   { transform: translateY(0) rotate(0deg); opacity: 1; }
    100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
}

.success-card {
    background: rgba(34,197,94,0.05);
    border: 1px solid rgba(34,197,94,0.2);
    border-radius: 16px;
    padding: 24px 32px;
    margin: 24px 0;
    max-width: 480px;
    width: 100%;
}

.success-card .detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    font-size: 0.88rem;
}

.success-card .detail-row:last-child { border-bottom: none; }
.success-card .detail-row .label { color: #374151; }
.success-card .detail-row .value { color: #000000; font-weight: 600; }

.btn-go-dashboard {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #f0c040, #f97316);
    color: #1a1a2e;
    font-weight: 700;
    font-size: 1rem;
    padding: 14px 32px;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s;
    box-shadow: 0 6px 20px rgba(240,192,64,0.3);
    margin-top: 8px;
}

.btn-go-dashboard:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(240,192,64,0.4);
    color: #1a1a2e;
}
</style>

{{-- Confetti animation --}}
<div class="confetti" id="confetti"></div>

<div class="success-container">
    @if (!$payment || $payment->status === 'paid')
        <div class="success-icon">🎉</div>
        <h1 style="font-size: 2rem; font-weight: 800; color: #f8fafc; margin: 0 0 12px;">
            Pembayaran Berhasil!
        </h1>
        <p style="color: #94a3b8; font-size: 1rem; max-width: 440px; margin: 0 0 24px;">
            Akun Anda telah diupgrade ke <strong style="color: #f0c040;">Premium</strong>. Selamat menikmati semua fitur eksklusif! 👑
        </p>
    @else
        <div class="success-icon" style="background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(234,88,12,0.1)); border-color: rgba(245,158,11,0.4);">⏳</div>
        <h1 style="font-size: 2rem; font-weight: 800; color: #f8fafc; margin: 0 0 12px;">
            Menunggu Verifikasi
        </h1>
        <p style="color: #94a3b8; font-size: 1rem; max-width: 440px; margin: 0 0 24px;">
            Pembayaran Anda sedang diproses. Status premium Anda akan aktif otomatis setelah pembayaran diverifikasi oleh sistem.
        </p>
    @endif

    @if ($payment)
        <div class="success-card">
            <div class="detail-row">
                <span class="label">Order ID</span>
                <span class="value" style="font-family: monospace; font-size: 0.82rem;">{{ $payment->order_id }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Paket</span>
                <span class="value">{{ $payment->plan === 'yearly' ? 'Premium Tahunan' : 'Premium Bulanan' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Nominal</span>
                <span class="value">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Status</span>
                @if ($payment->status === 'paid')
                    <span class="value" style="color: #22c55e;">✓ Berhasil</span>
                @else
                    <span class="value" style="color: #f59e0b; text-transform: capitalize;">⏳ {{ $payment->status }}</span>
                @endif
            </div>
        </div>
    @else
        <div class="success-card">
            <p style="color: #94a3b8; margin: 0; font-size: 0.9rem; text-align: center;">
                Pembayaran Anda sedang diverifikasi. Premium akan aktif dalam beberapa saat.
                <br><br>
                If not active in 5 minutes, contact support.
            </p>
        </div>
    @endif

    @if (app()->environment('local') && $payment && $payment->status === 'pending')
        <div style="background: rgba(245,158,11,0.05); border: 1px dashed rgba(245,158,11,0.3); border-radius: 12px; padding: 20px; max-width: 480px; width: 100%; margin: 0 auto 24px; text-align: center; box-sizing: border-box;">
            <h4 style="color: #f59e0b; margin: 0 0 8px; font-weight: 700; font-size: 0.95rem;">🔧 Sandbox Helper (Localhost)</h4>
            <p style="color: #94a3b8; font-size: 0.85rem; margin: 0 0 16px; line-height: 1.5;">
                Doku Sandbox tidak bisa mengirim webhook ke <code>localhost</code> Anda. Klik tombol di bawah ini untuk mensimulasikan pembayaran sukses secara lokal:
            </p>
            <form method="POST" action="{{ route('premium.simulate_webhook', $payment->order_id) }}">
                @csrf
                <button type="submit" class="btn-upgrade btn-upgrade-primary" style="padding: 10px 20px; font-size: 0.85rem; display: inline-block; width: auto; margin: 0; box-shadow: none;">
                    Simulasikan Pembayaran Sukses
                </button>
            </form>
        </div>
    @endif

    <a href="{{ route('dashboard') }}" class="btn-go-dashboard">
        Kembali ke Dashboard →
    </a>
</div>

<script>
// Confetti animation
(function() {
    const container = document.getElementById('confetti');
    const colors = ['#f0c040','#f97316','#ec4899','#22c55e','#3b82f6','#a855f7'];

    for (let i = 0; i < 80; i++) {
        const piece = document.createElement('div');
        piece.className = 'confetti-piece';
        piece.style.left   = Math.random() * 100 + 'vw';
        piece.style.background = colors[Math.floor(Math.random() * colors.length)];
        piece.style.animationDuration = (Math.random() * 3 + 2) + 's';
        piece.style.animationDelay = (Math.random() * 2) + 's';
        piece.style.width  = (Math.random() * 10 + 6) + 'px';
        piece.style.height = (Math.random() * 10 + 6) + 'px';
        piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
        container.appendChild(piece);
    }

    // Remove confetti setelah selesai
    setTimeout(() => container.remove(), 5000);
})();
</script>
@endsection
