<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            // Redirect ke halaman login berdasarkan role yang diharapkan
            if (in_array('siswa', $roles)) {
                return redirect()->route('siswa.login');
            } elseif (in_array('guru', $roles)) {
                return redirect()->route('guru.login');
            } elseif (in_array('admin', $roles)) {
                return redirect()->route('login'); 
            } else {
                return redirect()->route('login');
            }
        }

        $user = Auth::user();

        // Check if user has any of the required roles
        if (!in_array($user->roles, $roles)) {
            // Log the unauthorized access attempt
            Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'user_role' => $user->roles,
                // 'required_roles' => $roles,
                'url' => $request->url()
            ]);
            
            // // Redirect berdasarkan role user saat ini
            // switch ($user->roles) {
            //     case 'siswa':
            //         return redirect()->route('siswa.dashboard')
            //             ->with('error', 'Anda tidak memiliki akses ke halaman ini');
            //     case 'guru':
            //         return redirect()->route('guru.dashboard')
            //             ->with('error', 'Anda tidak memiliki akses ke halaman ini');
            //     case 'admin':
            //         return redirect()->route('admin.dashboard')
            //             ->with('error', 'Anda tidak memiliki akses ke halaman ini');
            //     default:
            //         abort(403, 'Anda tidak memiliki akses ke halaman ini');
            // }
        }

        return $next($request);
    }
}