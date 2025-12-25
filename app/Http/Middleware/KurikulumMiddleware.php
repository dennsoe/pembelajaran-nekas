<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KurikulumMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu');
        }

        $user = auth()->user();

        // Allow: kurikulum, admin, superadmin
        if (!in_array($user->group, ['kurikulum', 'admin', 'superadmin'])) {
            abort(403, 'Akses ditolak. Hanya Kurikulum yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}
