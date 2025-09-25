<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        // Log awal akses middleware
        Log::info('Middleware role check', [
            'user_id' => $user?->id,
            'user_role' => $user?->roles,
            'roles_expected' => $roles,
            'route' => $request->route()?->getName(),
            'ip' => $request->ip(),
        ]);

        // Jika belum login, redirect ke login sesuai role yang diminta
        if (!$user) {
            return $this->redirectToLogin($roles);
        }

        // Jika user tidak memiliki role yang sesuai
        if (!$this->hasRequiredRole($user, $roles)) {
            Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'user_role' => $user->roles,
                'roles_expected' => $roles,
                'route' => $request->route()?->getName(),
                'ip' => $request->ip(),
            ]);

            return $this->handleUnauthorizedAccess($user);
        }

        // Role cocok, lanjutkan request
        return $next($request);
    }

    /**
     * Check if user has required role
     */
    protected function hasRequiredRole($user, array $roles)
    {
        $userRole = strtolower(trim($user->roles));
        $allowedRoles = array_map(fn($r) => strtolower(trim($r)), $roles);

        return in_array($userRole, $allowedRoles, true);
    }

    /**
     * Redirect user ke halaman login sesuai role
     */
    protected function redirectToLogin(array $roles)
    {
        if (in_array('siswa', $roles)) {
            return redirect()->route('siswa.login');
        } elseif (in_array('guru', $roles)) {
            return redirect()->route('guru.login');
        } else {
            return redirect()->route('login');
        }
    }

    /**
     * Handle user yang login tapi tidak memiliki role yang sesuai
     */
    protected function handleUnauthorizedAccess($user)
    {
        switch (strtolower($user->roles)) {
            case 'siswa':
                return redirect()->route('siswa.dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
            case 'guru':
                return redirect()->route('guru.dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
            case 'admin':
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
            case 'orangtua':
                return redirect()->route('orangtua.dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
            default:
                abort(403, 'Akses ditolak. Anda tidak memiliki permission untuk mengakses halaman ini.');
        }
    }
}
