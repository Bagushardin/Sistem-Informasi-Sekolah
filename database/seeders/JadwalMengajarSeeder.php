<?php

// database/seeders/JadwalMengajarSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JadwalMengajar;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;

class JadwalMengajarSeeder extends Seeder
{
    public function run()
    {
        // Ambil data yang sudah ada
        $gurus = Guru::all();
        $kelas = Kelas::all();
        $mapels = Mapel::all();

        $jadwals = [
            // Senin
            [
                'guru_id' => $gurus->where('mapel_id', $mapels->where('nama_mapel', 'Matematika')->first()?->id)->first()?->id,
                'kelas_id' => $kelas->where('nama_kelas', 'X-1')->first()?->id,
                'mapel_id' => $mapels->where('nama_mapel', 'Matematika')->first()?->id,
                'hari' => 'senin',
                'jam_mulai' => '07:00',
                'jam_selesai' => '08:30',
            ],
            [
                'guru_id' => $gurus->where('mapel_id', $mapels->where('nama_mapel', 'Bahasa Indonesia')->first()?->id)->first()?->id,
                'kelas_id' => $kelas->where('nama_kelas', 'X-1')->first()?->id,
                'mapel_id' => $mapels->where('nama_mapel', 'Bahasa Indonesia')->first()?->id,
                'hari' => 'senin',
                'jam_mulai' => '08:30',
                'jam_selesai' => '10:00',
            ],
            // Selasa
            [
                'guru_id' => $gurus->where('mapel_id', $mapels->where('nama_mapel', 'Bahasa Inggris')->first()?->id)->first()?->id,
                'kelas_id' => $kelas->where('nama_kelas', 'X-1')->first()?->id,
                'mapel_id' => $mapels->where('nama_mapel', 'Bahasa Inggris')->first()?->id,
                'hari' => 'selasa',
                'jam_mulai' => '07:00',
                'jam_selesai' => '08:30',
            ],
            // Rabu
            [
                'guru_id' => $gurus->where('mapel_id', $mapels->where('nama_mapel', 'Matematika')->first()?->id)->first()?->id,
                'kelas_id' => $kelas->where('nama_kelas', 'X-2')->first()?->id,
                'mapel_id' => $mapels->where('nama_mapel', 'Matematika')->first()?->id,
                'hari' => 'rabu',
                'jam_mulai' => '07:00',
                'jam_selesai' => '08:30',
            ],
            // Kamis
            [
                'guru_id' => $gurus->where('mapel_id', $mapels->where('nama_mapel', 'Bahasa Indonesia')->first()?->id)->first()?->id,
                'kelas_id' => $kelas->where('nama_kelas', 'X-2')->first()?->id,
                'mapel_id' => $mapels->where('nama_mapel', 'Bahasa Indonesia')->first()?->id,
                'hari' => 'kamis',
                'jam_mulai' => '09:00',
                'jam_selesai' => '10:30',
            ],
            // Jumat
            [
                'guru_id' => $gurus->where('mapel_id', $mapels->where('nama_mapel', 'Bahasa Inggris')->first()?->id)->first()?->id,
                'kelas_id' => $kelas->where('nama_kelas', 'X-2')->first()?->id,
                'mapel_id' => $mapels->where('nama_mapel', 'Bahasa Inggris')->first()?->id,
                'hari' => 'jumat',
                'jam_mulai' => '07:00',
                'jam_selesai' => '08:30',
            ],
        ];

        foreach ($jadwals as $jadwal) {
            // Skip jika ada data yang null (belum ada guru/kelas/mapel)
            if (!$jadwal['guru_id'] || !$jadwal['kelas_id'] || !$jadwal['mapel_id']) {
                continue;
            }

            JadwalMengajar::create($jadwal);
        }
    }
}

// Update database/seeders/DatabaseSeeder.php
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            // ... existing seeders ...
            JadwalMengajarSeeder::class,
        ]);
    }
}