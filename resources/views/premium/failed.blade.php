@extends('layouts.app')

@section('content')
<style>
.failed-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 70vh;
    text-align: center;
    padding: 40px 24px;
}

.failed-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(239,68,68,0.1);
    border: 2px solid rgba(239,68,68,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    margin: 0 auto 28px;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%       { transform: translateX(-8px); }
    40%       { transform: translateX(8px); }
    60%       { transform: translateX(-6px); }
    80%       { transform: translateX(6px); }
}

.btn-try-again {
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

.btn-try-again:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(240,192,64,0.4);
    color: #1a1a2e;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.06);
    color: #94a3b8;
    border: 1px solid rgba(255,255,255,0.1);
    font-weight: 600;
    font-size: 0.9rem;
    padding: 12px 24px;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s;
    margin-top: 8px;
}

.btn-back:hover {
    background: rgba(255,255,255,0.1);
    color: #f8fafc;
}
</style>

<div class="failed-container">
    <div class="failed-icon">😔</div>

    <h1 style="font-size: 2rem; font-weight: 800; color: #f8fafc; margin: 0 0 12px;">
        Pembayaran Dibatalkan
    </h1>
    <p style="color: #94a3b8; font-size: 1rem; max-width: 440px; margin: 0 0 32px;">
        Pembayaran Anda tidak berhasil atau dibatalkan. Tidak ada biaya yang dikenakan.
        <br>Silakan coba lagi kapan saja!
    </p>

    <div style="display: flex; flex-direction: column; align-items: center; gap: 12px; width: 100%; max-width: 320px;">
        <a href="{{ route('premium.index') }}" class="btn-try-again" style="width: 100%; justify-content: center;">
            Coba Lagi →
        </a>
        <a href="{{ route('dashboard') }}" class="btn-back" style="width: 100%; justify-content: center;">
            Kembali ke Dashboard
        </a>
    </div>

    <div style="margin-top: 36px; padding: 16px 24px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; max-width: 400px;">
        <p style="color: #64748b; font-size: 0.82rem; margin: 0;">
            💡 <strong style="color: #94a3b8;">Tips:</strong> Jika pembayaran terus gagal, coba gunakan metode pembayaran lain seperti Virtual Account atau e-wallet. Pastikan saldo mencukupi sebelum melakukan pembayaran.
        </p>
    </div>
</div>
@endsection
