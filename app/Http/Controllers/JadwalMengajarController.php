<?php

// app/Http/Controllers/Admin/JadwalMengajarController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JadwalMengajar;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class JadwalMengajarController extends Controller
{
   public function index(Request $request)
{
    // Data untuk filter
    $guru  = Guru::with('user')->get();
    $kelas = Kelas::all();
    $mapel = Mapel::all();
    $hari  = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

    // Query jadwal dengan relasi
    $query = JadwalMengajar::with([
        'guru' => function ($q) {
            $q->with(['user' => function ($u) {
            
            }]);
        },
        'kelas',
        'mapel'
    ]);

    // Filter Guru
    if ($request->filled('guru_id')) {
        $query->where('guru_id', $request->guru_id);
    }

    // Filter Kelas
    if ($request->filled('kelas_id')) {
        $query->where('kelas_id', $request->kelas_id);
    }

    // Filter Hari
    if ($request->filled('hari')) {
        $query->where('hari', $request->hari);
    }

    // Ambil hasil jadwal
    $jadwal = $query->orderBy('hari')->orderBy('jam_mulai')->get();

    // Grouping by hari
    $jadwalGrouped = [];
    foreach ($hari as $h) {
        $jadwalGrouped[$h] = $jadwal->where('hari', $h);
    }

    return view('pages.admin.jadwalMengajar.home', compact(
        'jadwalGrouped',
        'hari',
        'guru',
        'kelas',
        'mapel'
    ));
}


    public function create()
    {
        $guru = Guru::all();
        $kelas = Kelas::all();
        $mapel = Mapel::all();
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        return view('pages.admin.jadwalMengajar.create', compact('guru', 'kelas', 'mapel', 'hari'));
    }

//     public function store(Request $request)
// {
//     Log::info('=== MEMULAI PROSES STORE JADWAL MENGAJAR ===');
//     Log::info('Data request:', $request->all());

//     $request->validate([
//         'guru_id' => 'required|exists:guru,id',
//         'kelas_id' => 'required|exists:kelas,id',
//         'mapel_id' => 'required|exists:mapel,id',
//         'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
//         'jam_mulai' => 'required|date_format:H:i',
//         'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
//     ]);

//     Log::info('Validasi berhasil');

//     try {
//         DB::beginTransaction();
//         Log::info('Transaction dimulai');

//         // Cek konflik jadwal di kelas yang sama
//         Log::info('Mengecek konflik jadwal di kelas...');
//         $konflikKelas = JadwalMengajar::where('hari', $request->hari)
//             ->where('kelas_id', $request->kelas_id)
//             ->where(function($query) use ($request) {
//                 $query->where(function($q) use ($request) {
//                     $q->where('jam_mulai', '<', $request->jam_selesai)
//                       ->where('jam_selesai', '>', $request->jam_mulai);
//                 });
//             })
//             ->exists();

//         Log::info('Hasil cek konflik kelas: ' . ($konflikKelas ? 'ADA KONFLIK' : 'TIDAK ADA KONFLIK'));

//         if ($konflikKelas) {
//             Log::warning('Konflik jadwal ditemukan di kelas yang sama');
//             DB::rollBack();
//             return back()->withErrors(['error' => 'Jadwal bertabrakan dengan jadwal lain di kelas yang sama!'])->withInput();
//         }

//         // Cek apakah guru sudah mengajar di waktu yang sama
//         Log::info('Mengecek konflik jadwal untuk guru...');
//         $konflikGuru = JadwalMengajar::where('hari', $request->hari)
//             ->where('guru_id', $request->guru_id)
//             ->where(function($query) use ($request) {
//                 $query->where(function($q) use ($request) {
//                     $q->where('jam_mulai', '<', $request->jam_selesai)
//                       ->where('jam_selesai', '>', $request->jam_mulai);
//                 });
//             })
//             ->exists();

//         Log::info('Hasil cek konflik guru: ' . ($konflikGuru ? 'ADA KONFLIK' : 'TIDAK ADA KONFLIK'));

//         if ($konflikGuru) {
//             Log::warning('Konflik jadwal ditemukan untuk guru');
//             DB::rollBack();
//             return back()->withErrors(['error' => 'Guru sudah mengajar di jam yang sama!'])->withInput();
//         }

//         // Buat jadwal mengajar
//         Log::info('Membuat jadwal mengajar baru...');
//         $jadwal = JadwalMengajar::create([
//             'guru_id' => $request->guru_id,
//             'kelas_id' => $request->kelas_id,
//             'mapel_id' => $request->mapel_id,
//             'hari' => $request->hari,
//             'jam_mulai' => $request->jam_mulai,
//             'jam_selesai' => $request->jam_selesai,
//         ]);

//         Log::info('Jadwal berhasil dibuat dengan ID: ' . $jadwal->id);

//         DB::commit();
//         Log::info('=== PROSES STORE BERHASIL ===');

//         return redirect()->route('admin.jadwalMengajar.index')->with('success', 'Jadwal mengajar berhasil ditambahkan!');

//     } catch (\Exception $e) {
//         DB::rollBack();
//         Log::error('Error saat menyimpan jadwal mengajar:', [
//             'message' => $e->getMessage(),
//             'file' => $e->getFile(),
//             'line' => $e->getLine(),
//             'trace' => $e->getTraceAsString()
//         ]);
        
//         return back()->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()])->withInput();
//     }
// }


public function store(Request $request)
{
    // Logging request awal
    Log::info('Store JadwalMengajar - Incoming Request', $request->all());

    try {
        $validated = $request->validate([
            'guru_id'     => ['required', 'exists:gurus,id'],
            'kelas_id'    => ['required', 'exists:kelas,id'],
            'mapel_id'    => ['required', 'exists:mapels,id'],
            'hari'        => ['required', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
            'jam_mulai'   => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);

        Log::info('Store JadwalMengajar - Validated Data', $validated);

        $start = new \DateTime($request->jam_mulai);
        $end = new \DateTime($request->jam_selesai);

        // Tangani sesi yang melewati tengah malam
        if ($end <= $start) {
            $end->modify('+1 day');
            Log::info('Sesi melewati tengah malam', [
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'end_modified' => $end->format('H:i'),
            ]);
        }

        $diff = $start->diff($end);
        $totalMinutes = ($diff->h * 60) + $diff->i;

        Log::info('Durasi sesi', [
            'total_minutes' => $totalMinutes,
            'hours' => $diff->h,
            'minutes' => $diff->i,
        ]);

        if ($totalMinutes < 30) {
            Log::warning('Durasi terlalu pendek', ['durasi' => $totalMinutes]);
            return back()->withErrors(['jam_selesai' => 'Durasi minimal adalah 30 menit.'])->withInput();
        }

        if ($totalMinutes > 360) {
            Log::warning('Durasi terlalu panjang', ['durasi' => $totalMinutes]);
            return back()->withErrors(['jam_selesai' => 'Durasi maksimal adalah 6 jam.'])->withInput();
        }

        // Simpan data
        $jadwal = JadwalMengajar::create($validated);
        Log::info('Jadwal berhasil disimpan', ['id' => $jadwal->id]);

        return redirect()->route('admin.jadwalMengajar.index')->with('success', 'Jadwal berhasil ditambahkan.');
    } catch (\Throwable $e) {
        // Tangkap dan log semua error
        Log::error('Gagal menyimpan JadwalMengajar', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan jadwal.'])->withInput();
    }
}


    public function edit($id)
    {
        $jadwal = JadwalMengajar::findOrFail($id);
        $guru = Guru::with('user_id')->get();
        $kelas = Kelas::all();
        $mapel = Mapel::all();
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        return view('pages.admin.jadwalMengajar.edit', compact('jadwal', 'guru', 'kelas', 'mapel', 'hari'));
    }

    public function update(Request $request, $id)
    {
        $jadwal = JadwalMengajar::findOrFail($id);
        
        $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapel,id',
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        try {
            DB::beginTransaction();

            // Cek konflik jadwal (kecuali dengan dirinya sendiri)
            $konflik = JadwalMengajar::where('hari', $request->hari)
                ->where('kelas_id', $request->kelas_id)
                ->where('id', '!=', $jadwal->id)
                ->where(function($query) use ($request) {
                    $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                          ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                          ->orWhere(function($q) use ($request) {
                              $q->where('jam_mulai', '<=', $request->jam_mulai)
                                ->where('jam_selesai', '>=', $request->jam_selesai);
                          });
                })
                ->exists();

            if ($konflik) {
                return back()->withErrors(['error' => 'Jadwal bertabrakan dengan jadwal lain di kelas yang sama!'])->withInput();
            }

            $jadwal->update($request->all());

            DB::commit();

            return redirect()->route('admin.jadwalMengajar.index')->with('success', 'Jadwal mengajar berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $jadwal = JadwalMengajar::findOrFail($id);
            $jadwal->delete();
            return redirect()->route('admin.jadwalMengajar.index')->with('success', 'Jadwal mengajar berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    // Method untuk API filter data
    public function getFilterData()
    {
        $guru = Guru::with('user')->get();
        $kelas = Kelas::all();

        return response()->json([
            'guru' => $guru,
            'kelas' => $kelas
        ]);
    }
}