<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SesiAbsensi extends Model
{
    use HasFactory;

    protected $table = 'sesi_absensi';

    protected $fillable = [
        'jadwal_mengajar_id',
        'tanggal',
        'jam_buka',
        'jam_tutup',
        'status',
        'dibuka_oleh',
        'catatan'
    ];

    protected $dates = ['tanggal', 'jam_buka', 'jam_tutup'];

    public function jadwalMengajar()
    {
        return $this->belongsTo(JadwalMengajar::class);
    }

    public function dibukaOleh()
    {
        return $this->belongsTo(User::class, 'dibuka_oleh');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function isBisaDiakses()
    {
        if ($this->status !== 'buka') return false;

        $sekarang = now();
        $jamBuka = $this->jam_buka instanceof Carbon ? $this->jam_buka : Carbon::parse($this->jam_buka);
        $jamTutup = $this->jam_tutup instanceof Carbon ? $this->jam_tutup : Carbon::parse($this->jam_tutup);

        return $sekarang->between($jamBuka, $jamTutup);
    }
}
