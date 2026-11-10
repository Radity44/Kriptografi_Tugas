<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMahasiswa
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isMahasiswa()) {
            abort(403, 'Akses Ditolak: Halaman ini khusus untuk akun Mahasiswa.');
        }

        return $next($request);
    }
}
