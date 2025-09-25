<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\JadwalMengajar;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    // ADMIN FUNCTIONS

    /**
     * Dashboard absensi untuk admin
     */
    public function adminIndex()
    {
        $jadwalHariIni = JadwalMengajar::with(['guru', 'kelas', 'mapel'])
            ->whereRaw("LOWER(hari) = ?", [strtolower(now()->locale('id')->dayName)])
            ->orderBy('jam_mulai')
            ->get();

        $sesiHariIni = SesiAbsensi::with(['jadwalMengajar.guru', 'jadwalMengajar.kelas', 'jadwalMengajar.mapel'])
            ->where('tanggal', today())
            ->orderBy('jam_buka')
            ->get();

        return view('pages.admin.absensi.index', compact('jadwalHariIni', 'sesiHariIni'));
    }

    /**
     * Buka sesi absensi oleh admin
     */
    public function bukaAbsensi(Request $request)
    {
        $this->validate($request, [
            'jadwal_mengajar_id' => 'required|exists:jadwal_mengajar,id',
            'jam_buka' => 'required|date_format:H:i',
            'jam_tutup' => 'required|date_format:H:i|after:jam_buka',
            'catatan' => 'nullable|string|max:500'
        ]);

        $jadwal = JadwalMengajar::findOrFail($request->jadwal_mengajar_id);

        // Cek apakah jadwal sedang aktif
        if (!$jadwal->isAktifSekarang()) {
            return redirect()->back()
                ->with('error', 'Sesi absensi hanya bisa dibuka pada jam mengajar yang aktif');
        }

        // Cek apakah sudah ada sesi untuk hari ini
        $existing = SesiAbsensi::where('jadwal_mengajar_id', $jadwal->id)
            ->where('tanggal', today())
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'Sesi absensi untuk jadwal ini sudah dibuka hari ini');
        }

        try {
            DB::beginTransaction();

            // Buat sesi absensi
            $sesi = SesiAbsensi::create([
                'jadwal_mengajar_id' => $jadwal->id,
                'tanggal' => today(),
                'jam_buka' => $request->jam_buka,
                'jam_tutup' => $request->jam_tutup,
                'status' => 'buka',
                'dibuka_oleh' => Auth::id(),
                'catatan' => $request->catatan
            ]);

            // Buat record absensi untuk semua siswa di kelas dengan status default 'alfa'
            $siswa = Siswa::where('kelas_id', $jadwal->kelas_id)->get();

            foreach ($siswa as $s) {
                Absensi::create([
                    'sesi_absensi_id' => $sesi->id,
                    'siswa_id' => $s->id,
                    'status' => 'alfa',
                    'diabsen_oleh' => Auth::id()
                ]);
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Sesi absensi berhasil dibuka untuk kelas ' . $jadwal->kelas->nama_kelas);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Gagal membuka sesi absensi: ' . $e->getMessage());
        }
    }

    /**
     * Tutup sesi absensi oleh admin
     */
    public function tutupAbsensi($id)
    {
        $sesi = SesiAbsensi::findOrFail($id);
        
        $sesi->update(['status' => 'tutup']);

        return redirect()->back()
            ->with('success', 'Sesi absensi berhasil ditutup');
    }

    // GURU FUNCTIONS

    /**
     * Dashboard absensi untuk guru
     */
//    public function guruIndex()
// {
//     $user = Auth::user();
    
//     if ($user->roles !== 'guru') {
//         return redirect()->back()->with('error', 'Hanya guru yang dapat mengakses halaman ini');
//     }
    
//     // Cari guru by user_id
//     $guru = Guru::where('user_id', $user->id)->first();
    
//     // Jika tidak ditemukan, cari by NIP
//     if (!$guru && $user->nip) {
//         $guru = Guru::where('nip', $user->nip)->first();
        
//         // Jika ditemukan by NIP, update user_id
//         if ($guru) {
//             $guru->update(['user_id' => $user->id]);
//         }
//     }
    
//     if (!$guru) {
//         return redirect()->back()->with('error', 'Akun Anda tidak terhubung dengan data guru. Silakan hubungi administrator.');
//     }

//     $sesiAktif = SesiAbsensi::with(['jadwalMengajar.kelas', 'jadwalMengajar.mapel'])
//         ->whereHas('jadwalMengajar', function($query) use ($guru) {
//             $query->where('guru_id', $guru->id);
//         })
//         ->where('tanggal', today())
//         ->where('status', 'buka')
//         ->get();

//     return view('pages.guru.absensi.index', compact('sesiAktif'));
// }

//     /**
//      * Halaman mengabsen siswa oleh guru
//      */
//     public function guruAbsen($sesiId)
//     {
//         $guru = Auth::user()->roles == 'guru' ? Auth::user()->guru : null;
        
//         $sesi = SesiAbsensi::with(['jadwalMengajar.kelas', 'jadwalMengajar.mapel', 'jadwalMengajar.guru'])
//             ->findOrFail($sesiId);

//         // Cek apakah guru berhak mengakses sesi ini
//         if ($sesi->jadwalMengajar->guru_id !== $guru->id) {
//             return redirect()->back()->with('error', 'Anda tidak berhak mengakses sesi absensi ini');
//         }

//         // Cek apakah sesi masih bisa diakses
//         if (!$sesi->isBisaDiakses()) {
//             return redirect()->back()->with('error', 'Sesi absensi sudah ditutup atau belum dimulai');
//         }

//         $absensi = Absensi::with('siswa')
//             ->where('sesi_absensi_id', $sesiId)
//             ->orderBy('siswa_id')
//             ->get();

//         return view('pages.guru.absensi.absen', compact('sesi', 'absensi'));
//     }

//     /**
//      * Update absensi siswa oleh guru
//      */
//     public function updateAbsensi(Request $request, $sesiId)
//     {
//         $this->validate($request, [
//             'absensi' => 'required|array',
//             'absensi.*' => 'required|in:hadir,izin,sakit,alfa',
//             'keterangan' => 'array',
//             'keterangan.*' => 'nullable|string|max:255'
//         ]);

//         $guru = Auth::user()->guru;
//         $sesi = SesiAbsensi::with('jadwalMengajar')->findOrFail($sesiId);

//         // Validasi akses guru
//         if ($sesi->jadwalMengajar->guru_id !== $guru->id) {
//             return redirect()->back()->with('error', 'Anda tidak berhak mengakses sesi ini');
//         }

//         if (!$sesi->isBisaDiakses()) {
//             return redirect()->back()->with('error', 'Sesi absensi sudah ditutup');
//         }

//         try {
//             DB::beginTransaction();

//             foreach ($request->absensi as $absensiId => $status) {
//                 $absensi = Absensi::where('id', $absensiId)
//                     ->where('sesi_absensi_id', $sesiId)
//                     ->firstOrFail();

//                 $absensi->update([
//                     'status' => $status,
//                     'keterangan' => $request->keterangan[$absensiId] ?? null,
//                     'waktu_absen' => now(),
//                     'diabsen_oleh' => Auth::id()
//                 ]);
//             }

//             DB::commit();

//             return redirect()->back()
//                 ->with('success', 'Absensi berhasil disimpan');

//         } catch (\Exception $e) {
//             DB::rollback();
//             return redirect()->back()
//                 ->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
//         }
//     }

    // SISWA FUNCTIONS

    /**
     * Dashboard absensi untuk siswa
     */
    public function siswaIndex()
    {
        $siswa = Auth::user()->siswa;
        
        if (!$siswa) {
            return redirect()->back()->with('error', 'Akun Anda tidak terhubung dengan data siswa');
        }

        // Ambil absensi siswa bulan ini
        $absensi = Absensi::with(['sesiAbsensi.jadwalMengajar.mapel', 'sesiAbsensi.jadwalMengajar.guru'])
            ->where('siswa_id', $siswa->id)
            ->whereHas('sesiAbsensi', function($query) {
                $query->whereMonth('tanggal', now()->month)
                      ->whereYear('tanggal', now()->year);
            })
            ->orderByDesc('created_at')
            ->get();

        // Statistik absensi
        $stats = [
            'hadir' => $absensi->where('status', 'hadir')->count(),
            'izin' => $absensi->where('status', 'izin')->count(),
            'sakit' => $absensi->where('status', 'sakit')->count(),
            'alfa' => $absensi->where('status', 'alfa')->count(),
        ];

        return view('pages.siswa.absensi.index', compact('absensi', 'stats'));
    }

    /**
     * Detail absensi siswa per mata pelajaran
     */
    public function siswaDetail($mapelId)
    {
        $siswa = Auth::user()->siswa;
        
        $absensi = Absensi::with(['sesiAbsensi.jadwalMengajar.mapel', 'sesiAbsensi.jadwalMengajar.guru'])
            ->where('siswa_id', $siswa->id)
            ->whereHas('sesiAbsensi.jadwalMengajar', function($query) use ($mapelId) {
                $query->where('mapel_id', $mapelId);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        if ($absensi->isEmpty()) {
            return redirect()->back()->with('error', 'Data absensi tidak ditemukan');
        }

        $mapel = $absensi->items()[0]->sesiAbsensi->jadwalMengajar->mapel;

        return view('pages.siswa.absensi.detail', compact('absensi', 'mapel'));
    }
}