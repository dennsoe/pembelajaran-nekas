<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KepalaSekolahMiddleware
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

        // Allow: kepala_sekolah, admin, superadmin
        if (!in_array($user->group, ['kepala_sekolah', 'admin', 'superadmin'])) {
            abort(403, 'Akses ditolak. Hanya Kepala Sekolah yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}
