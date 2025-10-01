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
        // Ambil data untuk filter
        $guru = Guru::with('user')->get();
        $kelas = Kelas::all();
        $mapel = Mapel::all();
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        // Query jadwal dengan filter dan handle null relations
        $query = JadwalMengajar::with([
            'guru.user' => function($query) {
                $query->withTrashed(); // Jika menggunakan soft delete
            },
            'kelas', 
            'mapel'
        ]);

        // Apply filters jika ada
        if ($request->has('guru_id') && $request->guru_id) {
            $query->where('guru_id', $request->guru_id);
        }

        if ($request->has('kelas_id') && $request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->has('hari') && $request->hari) {
            $query->where('hari', $request->hari);
        }

        $jadwal = $query->orderBy('hari')->orderBy('jam_mulai')->get();

        // Group by hari untuk tampilan yang lebih rapi
        $jadwalGrouped = [];
        foreach ($hari as $h) {
            $jadwalGrouped[$h] = $jadwal->where('hari', $h);
        }

        return view('pages.admin.jadwalMengajar.index', compact(
            'jadwalGrouped', 
            'hari', 
            'guru', 
            'kelas',
            'mapel'
        ));
    }

    public function create()
    {
        $guru = Guru::with('mapel')->get();
        $kelas = Kelas::all();
        $mapel = Mapel::all();
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        return view('pages.admin.jadwalMengajar.create', compact('guru', 'kelas', 'mapel', 'hari'));
    }

    // Perbaikan Pada store method tapi masih invalid di field guru dan mapel
     public function store(Request $request): mixed
{
    Log::info('=== MEMULAI PROSES STORE JADWAL MENGAJAR ===');
    Log::info('Data request:', $request->all());

    $request->validate([
        'guru_id' => 'required|exists:guru,id',
        'kelas_id' => 'required|exists:kelas,id',
        'mapel_id' => 'required|exists:mapel,id',
        'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
        'jam_mulai' => 'required|date_format:H:i',
        'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
    ],
        [
            'guru_id.required' => 'Pilih guru terlebih dahulu',
            'guru_id.exists' => 'Guru yang dipilih tidak valid',
            'kelas_id.required' => 'Pilih kelas terlebih dahulu',
            'kelas_id.exists' => 'Kelas yang dipilih tidak valid',
            'mapel_id.required' => 'Pilih mata pelajaran terlebih dahulu',
            'mapel_id.exists' => 'Mata pelajaran yang dipilih tidak valid',
            'hari.required' => 'Pilih hari terlebih dahulu',
            'hari.in' => 'Hari yang dipilih tidak valid',
            'jam_mulai.required' => 'Jam mulai harus diisi',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid',
            'jam_selesai.required' => 'Jam selesai harus diisi',
            'jam_selesai.date_format' => 'Format jam selesai tidak valid',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai',
        ]);

    Log::info('Validasi berhasil');

    try {
        DB::beginTransaction();
        Log::info('Transaction dimulai');

        // Cek konflik jadwal di kelas yang sama
        Log::info('Mengecek konflik jadwal di kelas...');
        $konflikKelas = JadwalMengajar::where('hari', $request->hari)
            ->where('kelas_id', $request->kelas_id)
            ->where(function($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('jam_mulai', '<', $request->jam_selesai)
                      ->where('jam_selesai', '>', $request->jam_mulai);
                });
            })
            ->exists();

        Log::info('Hasil cek konflik kelas: ' . ($konflikKelas ? 'ADA KONFLIK' : 'TIDAK ADA KONFLIK'));

        if ($konflikKelas) {
            Log::warning('Konflik jadwal ditemukan di kelas yang sama');
            DB::rollBack();
            return back()->withErrors(['error' => 'Jadwal bertabrakan dengan jadwal lain di kelas yang sama!'])->withInput();
        }

        // Cek apakah guru sudah mengajar di waktu yang sama
        Log::info('Mengecek konflik jadwal untuk guru...');
        $konflikGuru = JadwalMengajar::where('hari', $request->hari)
            ->where('guru_id', $request->guru_id)
            ->where(function($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('jam_mulai', '<', $request->jam_selesai)
                      ->where('jam_selesai', '>', $request->jam_mulai);
                });
            })
            ->exists();

        Log::info('Hasil cek konflik guru: ' . ($konflikGuru ? 'ADA KONFLIK' : 'TIDAK ADA KONFLIK'));

        if ($konflikGuru) {
            Log::warning('Konflik jadwal ditemukan untuk guru');
            DB::rollBack();
            return back()->withErrors(['error' => 'Guru sudah mengajar di jam yang sama!'])->withInput();
        }

        // Buat jadwal mengajar
        Log::info('Membuat jadwal mengajar baru...');
        $jadwal = JadwalMengajar::create([
            'guru_id' => $request->guru_id,
            'kelas_id' => $request->kelas_id,
            'mapel_id' => $request->mapel_id,
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
        ]);

        Log::info('Jadwal berhasil dibuat dengan ID: ' . $jadwal->id);

        DB::commit();
        Log::info('=== PROSES STORE BERHASIL ===');

        return redirect()->route('admin.jadwalMengajar.index')->with('success', 'Jadwal mengajar berhasil ditambahkan!');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error saat menyimpan jadwal mengajar:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return back()->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()])->withInput();
    }
}

    

    public function edit($id)
    {
        $jadwal = JadwalMengajar::findOrFail($id);
        $guru = Guru::with('user')->get();
        $kelas = Kelas::all();
        $mapel = Mapel::all();
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        return view('pages.admin.jadwalMengajar.edit', compact('jadwal', 'guru', 'kelas', 'mapel', 'hari'));
    }

    public function update(Request $request, $id) :mixed
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

    public function destroy($id):mixed
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
    public function getFilterData() :mixed 
    {
        $guru = Guru::with('user')->get();
        $kelas = Kelas::all();

        return response()->json([
            'guru' => $guru,
            'kelas' => $kelas
        ]);
    }

    /**
 * Validasi konflik jadwal untuk guru dan kelas
 * 
 * @param array $data
 * @return array|bool
 */
private function validasiKonflikJadwal(array $data): array|bool 
{
    // Cek konflik jadwal kelas
    $konflikKelas = JadwalMengajar::where('hari', $data['hari'])
        ->where('kelas_id', $data['kelas_id'])
        ->where(function($query) use ($data) {
            $query->where(function($q) use ($data) {
                $q->where('jam_mulai', '<', $data['jam_selesai'])
                  ->where('jam_selesai', '>', $data['jam_mulai']);
            });
        })
        ->first();

    if ($konflikKelas) {
        return [
            'status' => false,
            'pesan' => 'Jadwal bertabrakan dengan kelas ' . $konflikKelas->kelas->nama_kelas . 
                      ' pada jam ' . $konflikKelas->jam_mulai . ' - ' . $konflikKelas->jam_selesai
        ];
    }

    // Cek konflik jadwal guru
    $konflikGuru = JadwalMengajar::where('hari', $data['hari'])
        ->where('guru_id', $data['guru_id'])
        ->where(function($query) use ($data) {
            $query->where(function($q) use ($data) {
                $q->where('jam_mulai', '<', $data['jam_selesai'])
                  ->where('jam_selesai', '>', $data['jam_mulai']);
            });
        })
        ->first();

    if ($konflikGuru) {
        return [
            'status' => false,
            'pesan' => 'Guru sudah mengajar ' . $konflikGuru->mapel->nama_mapel . 
                      ' di kelas ' . $konflikGuru->kelas->nama_kelas . 
                      ' pada jam ' . $konflikGuru->jam_mulai . ' - ' . $konflikGuru->jam_selesai
        ];
    }

    return true;
}

/**
 * Validasi waktu jadwal
 * 
 * @param string $jamMulai
 * @param string $jamSelesai
 * @return array|bool
 */
private function validasiWaktuJadwal(string $jamMulai, string $jamSelesai): array|bool
{
    // Konversi ke menit untuk perbandingan
    $mulai = (int) substr($jamMulai, 0, 2) * 60 + (int) substr($jamMulai, 3, 2);
    $selesai = (int) substr($jamSelesai, 0, 2) * 60 + (int) substr($jamSelesai, 3, 2);
    
    // Durasi minimal 30 menit
    if (($selesai - $mulai) < 30) {
        return [
            'status' => false,
            'pesan' => 'Durasi minimal jadwal adalah 30 menit'
        ];
    }

    // Durasi maksimal 4 jam
    if (($selesai - $mulai) > 240) {
        return [
            'status' => false,
            'pesan' => 'Durasi maksimal jadwal adalah 4 jam'
        ];
    }

    return true;
}

/**
 * Cek total jam mengajar guru
 * 
 * @param int $guruId
 * @return array|bool
 */
private function validasiJamGuru(int $guruId): array|bool
{
    $totalMenit = JadwalMengajar::where('guru_id', $guruId)
        ->get()
        ->sum(function($jadwal) {
            $mulai = (int) substr($jadwal->jam_mulai, 0, 2) * 60 + (int) substr($jadwal->jam_mulai, 3, 2);
            $selesai = (int) substr($jadwal->jam_selesai, 0, 2) * 60 + (int) substr($jadwal->jam_selesai, 3, 2);
            return $selesai - $mulai;
        });

    // Maksimal 24 jam per minggu
    if ($totalMenit > (24 * 60)) {
        return [
            'status' => false,
            'pesan' => 'Total jam mengajar guru sudah melebihi batas maksimal (24 jam/minggu)'
        ];
    }

    return true;
}
}