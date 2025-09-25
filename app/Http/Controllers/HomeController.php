<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Orangtua;
use App\Models\PengumumanSekolah;
use App\Models\Siswa;
use App\Models\Tugas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard - redirect based on user role
     *
     * @return mixed
     */
    public function index()
    {
        $user = Auth::user();
        
        // Debug logging
        Log::info('User attempting to access dashboard', [
            'user_id' => $user->id,
            'role' => $user->roles ?? 'no_role',
            'email' => $user->email
        ]);
        
        // Check if user has role
        if (!isset($user->roles) || empty($user->roles)) {
            Log::warning('User has no role assigned', ['user_id' => $user->id]);
            return redirect()->route('login')->with('error', 'User tidak memiliki role yang valid');
        }
        
        // Redirect based on user role
        switch($user->roles) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'guru':
                return redirect()->route('guru.dashboard');
            case 'siswa':
                return redirect()->route('siswa.dashboard');
            case 'orangtua':
                return redirect()->route('orangtua.dashboard');
            default:
                // If role is not recognized, logout and redirect to login
                Log::warning('Unrecognized user role', ['user_id' => $user->id, 'roles' => $user->roles]);
                Auth::logout();
                return redirect()->route('login')->with('error', 'Role tidak dikenali: ' . $user->roles);
        }
    }

    /**
     * Admin dashboard
     *
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    // public function admin()
    // {
    //     $user = Auth::user();
        
    //     // Debug logging
    //     Log::info('Admin dashboard accessed', [
    //         'user_id' => $user->id,
    //         'role' => $user->role,
    //         'email' => $user->email
    //     ]);
        
    //     // Verify user has admin role
    //     if ($user->role !== 'admin') {
    //         Log::warning('Non-admin user trying to access admin dashboard', [
    //             'user_id' => $user->id,
    //             'role' => $user->role
    //         ]);
    //         abort(403, 'Unauthorized access to admin dashboard');
    //     }
        
    //     try {
    //         $siswa = Siswa::count();
    //         $guru = Guru::count();
    //         $kelas = Kelas::count();
    //         $mapel = Mapel::count();
    //         $siswaBaru = Siswa::orderByDesc('id')->take(5)->get();

    //         return view('pages.admin.dashboard', compact('siswa', 'guru', 'kelas', 'mapel', 'siswaBaru'));
    //     } catch (\Exception $e) {
    //         Log::error('Error in admin dashboard', ['error' => $e->getMessage()]);
    //         return redirect()->route('home')->with('error', 'Terjadi kesalahan saat memuat dashboard admin');
    //     }
    // }


    public function admin()
    {
        $user = Auth::user();
        
        // Debug logging
        Log::info('Admin dashboard accessed', [
            'user_id' => $user->id,
            'role' => $user->roles, 
            'email' => $user->email
        ]);
        
        // Verify user has admin role - DIUBAH: roles bukan role
        if ($user->roles !== 'admin') {
            Log::warning('Non-admin user trying to access admin dashboard', [
                'user_id' => $user->id,
                'role' => $user->roles 
            ]);
            abort(403, 'Unauthorized access to admin dashboard');
        }
        
        try {
            $siswa = Siswa::count();
            $guru = Guru::count();
            $kelas = Kelas::count();
            $mapel = Mapel::count();
            $siswaBaru = Siswa::orderByDesc('id')->take(5)->get();

            return view('pages.admin.dashboard', compact('siswa', 'guru', 'kelas', 'mapel', 'siswaBaru'));
        } catch (\Exception $e) {
            Log::error('Error in admin dashboard', ['error' => $e->getMessage()]);
            return redirect()->route('home')->with('error', 'Terjadi kesalahan saat memuat dashboard admin');
        }
    }
    /**
     * Guru dashboard
     *
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    // public function guru()
    // {
    //     $user = Auth::user();
        
    //     // Debug logging
    //     Log::info('Guru dashboard accessed', [
    //         'user_id' => $user->id,
    //         'role' => $user->roles,
    //         'email' => $user->email
    //     ]);
        
    //     // Verify user has guru role
    //     if ($user->roles !== 'guru') {
    //         Log::warning('Non-guru user trying to access guru dashboard', [
    //             'user_id' => $user->id,
    //             'role' => $user->roles
    //         ]);
    //         abort(403, 'Unauthorized access to guru dashboard');
    //     }
        
    //     $guru = Guru::where('user_id', $user->id)->first();
        
    //     // Check if guru data exists
    //     if (!$guru) {
    //         Log::warning('Guru data not found', ['user_id' => $user->id]);
    //         return redirect()->route('home')->with('error', 'Data guru tidak ditemukan');
    //     }
        
    //     try {
    //         $materi = Materi::where('guru_id', $guru->id)->count();
    //         $jadwal = Jadwal::where('mapel_id', $guru->mapel_id)->get();
    //         $tugas = Tugas::where('guru_id', $guru->id)->count();
    //         $hari = Carbon::now()->locale('id')->isoFormat('dddd');

    //         return view('pages.guru.dashboard', compact('guru', 'materi', 'jadwal', 'hari', 'tugas'));
    //     } catch (\Exception $e) {
    //         Log::error('Error in guru dashboard', ['error' => $e->getMessage(), 'user_id' => $user->id]);
    //         return redirect()->route('home')->with('error', 'Terjadi kesalahan saat memuat dashboard guru');
    //     }
    // }


    public function guru()
{
    $user = Auth::user();
    
    // Debug logging
    Log::info('Guru dashboard accessed', [
        'user_id' => $user->id,
        'role' => $user->roles,
        'email' => $user->email
    ]);
    
    // Verify user has guru role
    if ($user->roles !== 'guru') {
        Log::warning('Non-guru user trying to access guru dashboard', [
            'user_id' => $user->id,
            'role' => $user->roles
        ]);
        abort(403, 'Unauthorized access to guru dashboard');
    }
    
    // Try to find guru by user_id first (recommended approach)
    $guru = Guru::where('user_id', $user->id)->first();
    
    // If not found, try by NIP (fallback) - SAMA SEPERTI SISWA DENGAN NIS
    if (!$guru && isset($user->nip)) {
        $guru = Guru::where('nip', $user->nip)->first();
    }
    
    // Check if guru exists
    if (!$guru) {
        Log::warning('Guru data not found', ['user_id' => $user->id]);
        return redirect()->route('home')->with('error', 'Data guru tidak ditemukan');
    }
    
    // Check if guru has mapel_id (SAMA SEPERTI SISWA DENGAN KELAS_ID)
    if (!$guru->mapel_id) {
        Log::warning('Guru has no subject assigned', ['guru_id' => $guru->id]);
        // Tidak di-redirect, hanya warning karena guru masih bisa akses dashboard tanpa mapel
    }
    
    try {
        $materi = Materi::where('guru_id', $guru->id)->count();
        
        // Gunakan mapel_id jika ada, jika tidak gunakan empty collection
        $jadwal = $guru->mapel_id ? Jadwal::where('mapel_id', $guru->mapel_id)->get() : collect();
        
        $tugas = Tugas::where('guru_id', $guru->id)->count();
        $hari = Carbon::now()->locale('id')->isoFormat('dddd');

        return view('pages.guru.dashboard', compact('guru', 'materi', 'jadwal', 'hari', 'tugas'));
    } catch (\Exception $e) {
        Log::error('Error in guru dashboard', ['error' => $e->getMessage(), 'user_id' => $user->id]);
        return redirect()->route('home')->with('error', 'Terjadi kesalahan saat memuat dashboard guru');
    }
}

    /**
     * Siswa dashboard
     *
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    public function siswa()
    {
        $user = Auth::user();
        
        // Debug logging
        Log::info('Siswa dashboard accessed', [
            'user_id' => $user->id,
            'role' => $user->roles,
            'email' => $user->email
        ]);
        
        // Verify user has siswa role
        if ($user->roles !== 'siswa') {
            Log::warning('Non-siswa user trying to access siswa dashboard', [
                'user_id' => $user->id,
                'role' => $user->roles
            ]);
            abort(403, 'Unauthorized access to siswa dashboard');
        }
        
        // Try to find siswa by user_id first (recommended approach)
        $siswa = Siswa::where('user_id', $user->id)->first();
        
        // If not found, try by NIS (fallback)
        if (!$siswa && isset($user->nis)) {
            $siswa = Siswa::where('nis', $user->nis)->first();
        }
        
        // Check if siswa exists
        if (!$siswa) {
            Log::warning('Siswa data not found', ['user_id' => $user->id]);
            return redirect()->route('home')->with('error', 'Data siswa tidak ditemukan');
        }
        
        // Check if siswa has kelas_id
        if (!$siswa->kelas_id) {
            Log::warning('Siswa has no class assigned', ['siswa_id' => $siswa->id]);
            return redirect()->route('home')->with('error', 'Siswa belum memiliki kelas');
        }
        
        try {
            $kelas = Kelas::findOrFail($siswa->kelas_id);
            $materi = Materi::where('kelas_id', $kelas->id)->limit(3)->get();
            $tugas = Tugas::where('kelas_id', $kelas->id)->limit(3)->get();
            $jadwal = Jadwal::where('kelas_id', $kelas->id)->get();
            $hari = Carbon::now()->locale('id')->isoFormat('dddd');
            $pengumumans = PengumumanSekolah::where('status', 'active')
                                            ->orWhere('status', 1)
                                            ->get();
            
            return view('pages.siswa.dashboard', compact('materi', 'siswa', 'kelas', 'tugas', 'jadwal', 'hari', 'pengumumans'));
        } catch (\Exception $e) {
            Log::error('Error in siswa dashboard', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return redirect()->route('home')->with('error', 'Terjadi kesalahan saat memuat dashboard siswa');
        }
    }

    /**
     * Orangtua dashboard
     *
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    public function orangtua()
    {
        $user = Auth::user();
        
        // Debug logging
        Log::info('Orangtua dashboard accessed', [
            'user_id' => $user->id,
            'role' => $user->roles,
            'email' => $user->email
        ]);
        
        // Verify user has orangtua role
        if ($user->roles !== 'orangtua') {
            Log::warning('Non-orangtua user trying to access orangtua dashboard', [
                'user_id' => $user->id,
                'role' => $user->roles
            ]);
            abort(403, 'Unauthorized access to orangtua dashboard');
        }
        
        $orangtua = Orangtua::where('user_id', $user->id)->first();
        
        // Check if orangtua data exists
        if (!$orangtua) {
            Log::warning('Orangtua data not found', ['user_id' => $user->id]);
            return redirect()->route('home')->with('error', 'Data orangtua tidak ditemukan');
        }
        
        // Get siswa data related to this orangtua
        $siswa = Siswa::where('orangtua_id', $orangtua->id)->first();
        
        if (!$siswa) {
            Log::warning('Student data not found for orangtua', ['orangtua_id' => $orangtua->id]);
            return redirect()->route('home')->with('error', 'Data anak tidak ditemukan');
        }
        
        try {
            $kelas = $siswa->kelas_id ? Kelas::find($siswa->kelas_id) : null;
            $tugas = $kelas ? Tugas::where('kelas_id', $kelas->id)->limit(5)->get() : collect();
            $pengumumans = PengumumanSekolah::where('status', 'active')
                                            ->orWhere('status', 1)
                                            ->get();
            
            return view('pages.orangtua.dashboard', compact('orangtua', 'siswa', 'kelas', 'tugas', 'pengumumans'));
        } catch (\Exception $e) {
            Log::error('Error in orangtua dashboard', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return redirect()->route('home')->with('error', 'Terjadi kesalahan saat memuat dashboard orangtua');
        }
    }
}