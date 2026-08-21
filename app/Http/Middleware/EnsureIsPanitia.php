<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsPanitia
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isPanitia()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'unauthorized',
                    'message' => 'Akses ditolak. Fitur scan barcode khusus untuk panitia.',
                ], 403);
            }

            return redirect()->route('admin.dashboard')
                ->with('error', 'Akses ditolak. Fitur scan barcode absensi hanya dapat diakses oleh Panitia.');
        }

        return $next($request);
    }
}
