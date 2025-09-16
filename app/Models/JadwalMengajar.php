<?php

// app/Models/JadwalMengajar.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalMengajar extends Model
{
    use HasFactory;

    protected $table = 'jadwal_mengajar';
    
    protected $fillable = [
        'guru_id',
        'kelas_id', 
        'mapel_id',
        'hari',
        'jam_mulai',
        'jam_selesai'
    ];

    protected $casts = [
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i'
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function sesiAbsensi()
    {
        return $this->hasMany(SesiAbsensi::class);
    }

    // Check apakah jadwal sedang aktif sekarang
    public function isAktifSekarang()
    {
        $sekarang = now();
        $hariIni = strtolower($sekarang->locale('id')->dayName);
        $jamSekarang = $sekarang->format('H:i:s');

        return $this->hari === $hariIni && 
               $jamSekarang >= $this->jam_mulai->format('H:i:s') && 
               $jamSekarang <= $this->jam_selesai->format('H:i:s');
    }
}
