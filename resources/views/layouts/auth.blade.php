<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - {{ config('app.name') }}</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --accent: #19736b;
            --accent-dark: #0f5751;
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0d1e1c 100%);
            --panel: rgba(255, 255, 255, 0.03);
            --panel-border: rgba(255, 255, 255, 0.08);
            --ink: #f8fafc;
            --muted: #94a3b8;
            --shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-gradient);
            color: var(--ink);
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            overflow: hidden;
            position: relative;
        }

        /* Ambient glow background effects */
        .ambient-glow-1 {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(43, 181, 166, 0.15) 0%, rgba(43, 181, 166, 0) 70%);
            top: -10%;
            left: -10%;
            z-index: 1;
            filter: blur(40px);
        }

        .ambient-glow-2 {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(25, 115, 107, 0.12) 0%, rgba(25, 115, 107, 0) 70%);
            bottom: -15%;
            right: -10%;
            z-index: 1;
            filter: blur(50px);
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
            padding: 24px;
            z-index: 10;
        }

        /* Glassmorphism Card */
        .auth-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--panel-border);
            border-radius: 16px;
            padding: 40px 32px;
            box-shadow: var(--shadow);
            text-align: center;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }

        .brand-mark {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2bb5a6 0%, #19736b 100%);
            display: grid;
            place-items: center;
            font-weight: 800;
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            color: white;
            box-shadow: 0 4px 12px rgba(43, 181, 166, 0.3);
        }

        .brand-text {
            text-align: left;
        }

        .brand-text strong {
            display: block;
            font-size: 20px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .brand-text small {
            display: block;
            color: var(--muted);
            font-size: 13px;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 32px;
            line-height: 1.5;
        }

        /* OAuth Google Button */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            background: white;
            color: #1f2937;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        .btn-google:hover {
            background: #f9fafb;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            border-color: #d1d5db;
        }

        .btn-google:active {
            transform: translateY(0);
        }

        .btn-google svg {
            width: 20px;
            height: 20px;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
            border-radius: 8px;
            padding: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: left;
        }

        .status-message {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #34d399;
            border-radius: 8px;
            padding: 12px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .footer-text {
            margin-top: 32px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>
    
    <div class="auth-container">
        @yield('content')
    </div>
</body>
</html>
