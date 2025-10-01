<?php

namespace App\Http\Controllers;

use App\Models\JadwalMengajar;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;

class AdminJadwalMengajar extends Controller
{
    public function index()
    {
        $jadwal = JadwalMengajar::with(['guru', 'kelas', 'mapel'])
            ->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('jam_mulai')
            ->get();

        $guru = Guru::with('user')->get();
        $kelas = Kelas::all();
        $mapel = Mapel::with('jurusan')->get();

        return view('pages.admin.jadwalmengajar.index', compact('jadwal', 'guru', 'kelas', 'mapel'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapel,id',
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_mulai' => 'required|date_format:H:i:s',
            'jam_selesai' => 'required|date_format:H:i:s|after:jam_mulai'
        ]);

        // Cek konflik jadwal untuk guru yang sama
        $konflikGuru = JadwalMengajar::where('guru_id', $request->guru_id)
            ->where('hari', $request->hari)
            ->where(function($query) use ($request) {
                $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                      ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                      ->orWhere(function($q) use ($request) {
                          $q->where('jam_mulai', '<=', $request->jam_mulai)
                            ->where('jam_selesai', '>=', $request->jam_selesai);
                      });
            })
            ->exists();

        if ($konflikGuru) {
            return redirect()->back()->with('error', 'Guru sudah memiliki jadwal lain pada hari dan waktu yang sama.');
        }

        // Cek konflik jadwal untuk kelas yang sama
        $konflikKelas = JadwalMengajar::where('kelas_id', $request->kelas_id)
            ->where('hari', $request->hari)
            ->where(function($query) use ($request) {
                $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                      ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                      ->orWhere(function($q) use ($request) {
                          $q->where('jam_mulai', '<=', $request->jam_mulai)
                            ->where('jam_selesai', '>=', $request->jam_selesai);
                      });
            })
            ->exists();

        if ($konflikKelas) {
            return redirect()->back()->with('error', 'Kelas sudah memiliki jadwal lain pada hari dan waktu yang sama.');
        }

        try {
            JadwalMengajar::create($request->all());
            return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal mengajar berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $jadwal = JadwalMengajar::findOrFail($id);
        $guru = Guru::with('user')->get();
        $kelas = Kelas::all();
        $mapel = Mapel::with('jurusan')->get();

        return view('pages.admin.jadwal.edit', compact('jadwal', 'guru', 'kelas', 'mapel'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapel,id',
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_mulai' => 'required|date_format:H:i:s',
            'jam_selesai' => 'required|date_format:H:i:s|after:jam_mulai'
        ]);

        $jadwal = JadwalMengajar::findOrFail($id);

        // Cek konflik jadwal untuk guru yang sama (kecuali jadwal yang sedang diupdate)
        $konflikGuru = JadwalMengajar::where('guru_id', $request->guru_id)
            ->where('hari', $request->hari)
            ->where('id', '!=', $id)
            ->where(function($query) use ($request) {
                $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                      ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                      ->orWhere(function($q) use ($request) {
                          $q->where('jam_mulai', '<=', $request->jam_mulai)
                            ->where('jam_selesai', '>=', $request->jam_selesai);
                      });
            })
            ->exists();

        if ($konflikGuru) {
            return redirect()->back()->with('error', 'Guru sudah memiliki jadwal lain pada hari dan waktu yang sama.');
        }

        // Cek konflik jadwal untuk kelas yang sama (kecuali jadwal yang sedang diupdate)
        $konflikKelas = JadwalMengajar::where('kelas_id', $request->kelas_id)
            ->where('hari', $request->hari)
            ->where('id', '!=', $id)
            ->where(function($query) use ($request) {
                $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                      ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                      ->orWhere(function($q) use ($request) {
                          $q->where('jam_mulai', '<=', $request->jam_mulai)
                            ->where('jam_selesai', '>=', $request->jam_selesai);
                      });
            })
            ->exists();

        if ($konflikKelas) {
            return redirect()->back()->with('error', 'Kelas sudah memiliki jadwal lain pada hari dan waktu yang sama.');
        }

        try {
            $jadwal->update($request->all());
            return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal mengajar berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $jadwal = JadwalMengajar::findOrFail($id);
            
            // Cek apakah jadwal memiliki sesi absensi
            if ($jadwal->sesiAbsensi()->count() > 0) {
                return redirect()->back()->with('error', 'Tidak dapat menghapus jadwal karena sudah memiliki data absensi.');
            }

            $jadwal->delete();
            return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal mengajar berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // API untuk mendapatkan jadwal berdasarkan filter
    public function getJadwalByHari($hari)
    {
        $jadwal = JadwalMengajar::with(['guru.user', 'kelas', 'mapel'])
            ->where('hari', $hari)
            ->orderBy('jam_mulai')
            ->get();

        return response()->json($jadwal);
    }
}