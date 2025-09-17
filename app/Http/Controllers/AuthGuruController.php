<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Guru;

class AuthGuruController extends Controller
{
    public function showLoginForm()
    {
        return view('pages.guru.login.index');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cek apakah input adalah email atau NIP
        $loginType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'nip';
        
        // Cari user berdasarkan email atau NIP
        $user = User::where($loginType, $credentials['login'])->first();
        
        // Jika user ditemukan dan password benar
        if ($user && Auth::attempt([$loginType => $credentials['login'], 'password' => $credentials['password']])) {
            // Pastikan user memiliki role guru
            if ($user->roles == 'guru') {
                $request->session()->regenerate();
                return redirect()->route('guru.dashboard');
            } else {
                Auth::logout();
                return back()->withErrors([
                    'login' => 'Hanya guru yang dapat login di sini.',
                ]);
            }
        }

        return back()->withErrors([
            'login' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('guru.login');
    }
}