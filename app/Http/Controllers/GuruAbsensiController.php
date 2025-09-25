<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\SesiAbsensi;
use App\Models\Guru;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GuruAbsensiController extends Controller
{
    public function guruIndex()
    {
        $user = Auth::user();
        Log::info('Guru index accessed', [
            'user_id' => $user->id,
            'roles' => $user->roles,
            'guru_relation' => $user->guru
        ]);

        // Ambil data guru
        $guru = $user->guru;
        if (!$guru && $user->nip) {
            $guru = Guru::where('nip', $user->nip)->first();
            if ($guru) {
                $guru->update(['user_id' => $user->id]);
            }
        }

        if (!$guru) {
            Log::warning('Guru not connected to user', ['user_id' => $user->id]);
            return redirect()->back()->with('error', 'Akun Anda tidak terhubung dengan data guru.');
        }

        // Ambil semua sesi aktif hari ini
        $sesiAktif = SesiAbsensi::with([
                'jadwalMengajar.kelas',
                'jadwalMengajar.mapel',
                'jadwalMengajar.guru',
                'absensi.siswa'
            ])
            ->whereHas('jadwalMengajar', fn($q) => $q->where('guru_id', $guru->id))
            ->whereDate('tanggal', today())
            ->where('status', 'buka')
            ->get();

        return view('pages.guru.absensi.index', compact('sesiAktif'));
    }

    public function guruAbsen($sesiId)
    {
        $guru = Auth::user()->guru;

        $sesi = SesiAbsensi::with(['jadwalMengajar.kelas', 'jadwalMengajar.mapel', 'jadwalMengajar.guru', 'absensi.siswa'])
            ->findOrFail($sesiId);

        if ($sesi->jadwalMengajar->guru_id !== $guru->id) {
            return redirect()->back()->with('error', 'Anda tidak berhak mengakses sesi absensi ini');
        }

        if (!$sesi->isBisaDiakses()) {
            return redirect()->back()->with('error', 'Sesi absensi sudah ditutup atau belum dimulai');
        }

        return view('pages.guru.absensi.absen', compact('sesi'));
    }

    public function updateAbsensi(Request $request, $sesiId)
    {
        $this->validate($request, [
            'absensi' => 'required|array',
            'absensi.*' => 'required|in:hadir,izin,sakit,alfa',
            'keterangan' => 'array',
            'keterangan.*' => 'nullable|string|max:255'
        ]);

        $guru = Auth::user()->guru;
        $sesi = SesiAbsensi::with('jadwalMengajar')->findOrFail($sesiId);

        if ($sesi->jadwalMengajar->guru_id !== $guru->id) {
            return redirect()->back()->with('error', 'Anda tidak berhak mengakses sesi ini');
        }

        if (!$sesi->isBisaDiakses()) {
            return redirect()->back()->with('error', 'Sesi absensi sudah ditutup');
        }

        DB::beginTransaction();
        try {
            foreach ($request->absensi as $absensiId => $status) {
                $absensi = Absensi::where('id', $absensiId)
                    ->where('sesi_absensi_id', $sesiId)
                    ->firstOrFail();

                $absensi->update([
                    'status' => $status,
                    'keterangan' => $request->keterangan[$absensiId] ?? null,
                    'waktu_absen' => now(),
                    'diabsen_oleh' => Auth::id()
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Absensi berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
        }
    }
}
