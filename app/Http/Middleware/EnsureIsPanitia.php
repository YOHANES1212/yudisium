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
                    'message' => 'Akses ditolak. Fitur khusus panitia.',
                ], 403);
            }

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Akses ditolak. Akun Anda tidak memiliki hak akses panitia.');
        }

        return $next($request);
    }
}
