<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        try {
            $loginType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'nip';

            Log::info('Login attempt input', [
                'login_input' => $credentials['login'],
                'login_type' => $loginType
            ]);

            $user = User::where($loginType, $credentials['login'])->first();

            Log::info('User query result', [
                'user_found' => $user ? true : false,
                'user_data' => $user ? $user->toArray() : null
            ]);

            if (!$user) {
                Log::warning('Login failed - user not found', [
                    'login_input' => $credentials['login'],
                    'login_type' => $loginType
                ]);
                return back()->withErrors(['login' => 'Akun tidak ditemukan.'])->withInput();
            }

            if (strtolower(trim($user->roles)) !== 'guru') {
                Log::warning('Login failed - wrong role', [
                    'user_id' => $user->id,
                    'user_role' => $user->roles
                ]);
                return back()->withErrors(['login' => 'Hanya guru yang dapat login di sini.'])->withInput();
            }

            if (Auth::attempt([$loginType => $credentials['login'], 'password' => $credentials['password']])) {
                Log::info('Login attempt success', [
                    'user_id' => $user->id,
                    'roles' => $user->roles
                ]);

                $this->ensureGuruDataConnected($user);

                $user->update(['last_login_at' => now()]);
                $request->session()->regenerate();

                return redirect()->route('guru.dashboard')->with('success', 'Login berhasil!');
            }

            Log::warning('Login failed - wrong password', [
                'user_id' => $user->id,
                'login_input' => $credentials['login']
            ]);

            return back()->withErrors(['login' => 'Password salah.'])->withInput();

        } catch (\Exception $e) {
            Log::error('Login exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['login' => 'Terjadi kesalahan sistem.'])->withInput();
        }
    }

    private function ensureGuruDataConnected(User $user)
    {
        $guru = $user->guru;

        if ($guru) {
            Log::info('Guru data already connected', [
                'user_id' => $user->id,
                'guru_id' => $guru->id,
                'nip' => $guru->nip
            ]);
            return $guru;
        }

        Log::info('Guru data not found for user, checking by NIP', [
            'user_id' => $user->id,
            'user_nip' => $user->nip
        ]);

        if ($user->nip) {
            $guru = Guru::where('nip', $user->nip)->first();
            if ($guru) {
                $guru->update(['user_id' => $user->id]);
                Log::info('Guru data found by NIP and updated with user_id', [
                    'user_id' => $user->id,
                    'guru_id' => $guru->id,
                    'nip' => $guru->nip
                ]);
                return $guru;
            }
        }

        Log::info('Guru data not found, creating new record', [
            'user_id' => $user->id,
            'user_nip' => $user->nip,
            'user_name' => $user->name
        ]);

        $guru = Guru::create([
            'user_id' => $user->id,
            'nip' => $user->nip,
            'nama' => $user->name,
        ]);

        Log::info('New guru record created', [
            'user_id' => $user->id,
            'guru_id' => $guru->id,
            'nip' => $guru->nip
        ]);

        return $guru;
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('guru.login');
    }

    public function dashboard()
    {
        return view('pages.guru.dashboard.index');
    }
}
