<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Siswa;

class AuthSiswaController extends Controller
{
    public function showLoginForm()
    {
        return view('pages.siswa.login.index');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cek apakah input adalah email atau NIS
        $loginType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'nis';
        
        // Cari user berdasarkan email atau NIS
        $user = User::where($loginType, $credentials['login'])->first();
        
        // Jika user ditemukan dan password benar
        if ($user && Auth::attempt([$loginType => $credentials['login'], 'password' => $credentials['password']])) {
            // Pastikan user memiliki role siswa
            if ($user->roles == 'siswa') {
                $request->session()->regenerate();
                return redirect()->route('siswa.dashboard');
            } else {
                Auth::logout();
                return back()->withErrors([
                    'login' => 'Hanya siswa yang dapat login di sini.',
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
        return redirect()->route('siswa.login');
    }
}