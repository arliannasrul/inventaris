<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <aside class="sidebar">
        <div class="brand">
            <span class="brand-mark">A6</span>
            <div>
                <strong>Inventaris</strong>
                <small>Arlian6A1</small>
            </div>
        </div>
        <nav>
            <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>Dashboard</a>
            <a href="{{ route('items.index') }}" @class(['active' => request()->routeIs('items.*')])>Barang</a>
            <a href="{{ route('reports.index') }}" @class(['active' => request()->routeIs('reports.*')])>Laporan</a>
            <a href="{{ route('notifications.index') }}" @class(['active' => request()->routeIs('notifications.*')])>Notifikasi</a>
        </nav>
    </aside>

    <main class="main">
        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
