<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk membatasi akses ke fitur Premium.
 * Redirect ke halaman upgrade jika user belum berlangganan.
 */
class EnsurePremium
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isPremium()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Fitur ini memerlukan langganan Premium.',
                    'upgrade_url' => route('premium.index'),
                ], 403);
            }

            return redirect()->route('premium.index')
                ->with('status', 'Fitur ini hanya tersedia untuk pengguna Premium. Upgrade sekarang!');
        }

        return $next($request);
    }
}
