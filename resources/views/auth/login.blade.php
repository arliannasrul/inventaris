@extends('layouts.auth')

@section('content')
<style>
    .auth-wrapper {
        display: flex;
        min-height: 100vh;
        width: 100vw;
        background: #050609;
        color: #f8fafc;
        overflow: hidden;
    }

    /* Left Side: Hero Section */
    .auth-hero {
        flex: 1.1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 56px 64px;
        background: linear-gradient(145deg, #0d121f 0%, #06080e 100%);
        border-right: 1px solid rgba(255, 255, 255, 0.03);
        position: relative;
        overflow: hidden;
    }

    /* Subtle neon glows */
    .auth-hero::before {
        content: '';
        position: absolute;
        width: 600px;
        height: 600px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, transparent 70%);
        bottom: -200px;
        left: -200px;
        filter: blur(80px);
        pointer-events: none;
    }

    .auth-hero::after {
        content: '';
        position: absolute;
        width: 500px;
        height: 500px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.06) 0%, transparent 70%);
        top: -150px;
        right: -150px;
        filter: blur(60px);
        pointer-events: none;
    }

    .auth-hero-header {
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 10;
    }

    .brand-logo-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .brand-logo-box {
        width: 32px;
        height: 32px;
        background: #3b82f6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 1.1rem;
        color: #fff;
        box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
    }

    .brand-logo-text {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 1.35rem;
        letter-spacing: -0.5px;
        color: #fff;
    }

    .auth-hero-body {
        margin: auto 0;
        z-index: 10;
        max-width: 540px;
        animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .auth-hero-title {
        font-size: clamp(2rem, 3.2vw, 2.75rem);
        font-weight: 800;
        line-height: 1.25;
        margin-bottom: 20px;
        letter-spacing: -1px;
    }

    .auth-hero-title span {
        color: #38bdf8;
    }

    .auth-hero-desc {
        color: #94a3b8;
        font-size: 0.98rem;
        line-height: 1.6;
        margin-bottom: 32px;
    }

    .auth-hero-preview {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.05);
        background: rgba(13, 18, 31, 0.4);
        backdrop-filter: blur(20px);
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
        transition: transform 0.3s;
    }

    .auth-hero-preview:hover {
        transform: translateY(-4px);
    }

    .auth-hero-preview img {
        width: 100%;
        height: auto;
        display: block;
    }

    .auth-hero-footer {
        font-size: 0.8rem;
        color: #475569;
        z-index: 10;
    }

    /* Right Side: Form Section */
    .auth-form-section {
        flex: 0.9;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 56px;
        background: #06070a;
        position: relative;
    }

    .auth-form-container {
        width: 100%;
        max-width: 380px;
        animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .auth-form-header {
        margin-bottom: 36px;
    }

    .auth-form-header h2 {
        font-size: 2.2rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }

    .auth-form-header p {
        color: #64748b;
        font-size: 0.92rem;
        line-height: 1.5;
    }

    /* Input Styling */
    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: #94a3b8;
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        background: #0d0f17;
        border: 1px solid #1c1f30;
        border-radius: 8px;
        padding: 14px 16px;
        color: #fff;
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.25s ease;
    }

    .form-control::placeholder {
        color: #475569;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        background: #111422;
        box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.5);
    }

    /* Button Login */
    .btn-submit {
        width: 100%;
        background: #ffffff;
        color: #050609;
        font-weight: 700;
        font-size: 0.95rem;
        padding: 14px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-top: 12px;
        font-family: inherit;
    }

    .btn-submit:hover {
        background: #e2e8f0;
        transform: translateY(-1px);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    /* Divider */
    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 1px;
        margin: 28px 0;
    }

    .divider::before, .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #141724;
    }

    .divider:not(:empty)::before { margin-right: 1.2em; }
    .divider:not(:empty)::after { margin-left: 1.2em; }

    /* Button Google */
    .btn-google-dark {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        width: 100%;
        background: #0d0f17;
        color: #f8fafc;
        border: 1px solid #1c1f30;
        border-radius: 8px;
        padding: 14px 16px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-google-dark:hover {
        background: #131724;
        border-color: #2a2f4a;
        color: #fff;
    }

    /* Register link */
    .auth-form-footer {
        margin-top: 36px;
        text-align: center;
        font-size: 0.88rem;
        color: #64748b;
    }

    .auth-form-footer a {
        color: #f8fafc;
        text-decoration: none;
        font-weight: 700;
        transition: color 0.2s;
    }

    .auth-form-footer a:hover {
        color: #38bdf8;
        text-decoration: underline;
    }

    /* Feedback messages */
    .alert {
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 0.88rem;
        margin-bottom: 24px;
        text-align: left;
    }

    .alert-danger {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #f87171;
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.25);
        color: #34d399;
    }

    /* Animations */
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Responsive Grid */
    @media (max-width: 992px) {
        .auth-hero { display: none; }
        .auth-form-section { flex: 1; padding: 36px 24px; }
    }
</style>

<div class="auth-wrapper">
    <!-- Left Hero Side -->
    <div class="auth-hero">
        <div class="auth-hero-header">
            <div class="brand-logo-container">
                <div class="brand-logo-box">M</div>
                <div class="brand-logo-text">MitraSpace</div>
            </div>
        </div>

        <div class="auth-hero-body">
            <h1 class="auth-hero-title">
                Jelajahi <span>Ribuan Toko</span><br>Dalam Satu Genggaman.
            </h1>
            <p class="auth-hero-desc">
                Temukan produk terbaik dari berbagai UMKM pilihan di seluruh Indonesia. Cepat, aman, dan terpercaya.
            </p>
            <div class="auth-hero-preview">
                <img src="{{ asset('images/login_dashboard_preview.png') }}" alt="Dashboard Preview">
            </div>
        </div>

        <div class="auth-hero-footer">
            &copy; {{ date('Y') }} MitraSpace. Hak Cipta Dilindungi.
        </div>
    </div>

    <!-- Right Form Side -->
    <div class="auth-form-section">
        <div class="auth-form-container">
            <div class="auth-form-header">
                <h2>Login</h2>
                <p>Selamat datang kembali! Silakan masuk ke akun Anda.</p>
            </div>

            {{-- Alert Messages --}}
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 16px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form Login --}}
            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="email@contoh.com" required value="{{ old('email') }}">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="******" required>
                </div>

                <button type="submit" class="btn-submit">Login</button>
            </form>

            <div class="divider">ATAU LANJUT DENGAN</div>

            {{-- Google Login Button --}}
            <a href="{{ route('auth.google') }}" class="btn-google-dark">
                <svg viewBox="0 0 24 24" width="18" height="18" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                </svg>
                <span>Google</span>
            </a>

            <div class="auth-form-footer">
                Belum punya akun? <a href="#">Daftar Sekarang</a>
            </div>
        </div>
    </div>
</div>
@endsection
