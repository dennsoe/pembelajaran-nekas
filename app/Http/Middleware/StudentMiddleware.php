<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentMiddleware
{
    /**
     * Handle an incoming request for students (ketua kelas).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if student is authenticated
        if (!auth('student')->check()) {
            return redirect()->route('student.login')->with('error', 'Silakan login terlebih dahulu');
        }

        $student = auth('student')->user();

        // Check if student is a class leader
        if (!$student->is_class_leader) {
            abort(403, 'Akses ditolak. Hanya Ketua Kelas yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}
