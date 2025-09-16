<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'tanggal' => 'date',
        'jam_buka' => 'datetime:H:i',
        'jam_tutup' => 'datetime:H:i'
    ];

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

    // Check apakah sesi masih bisa diakses
    public function isBisaDiakses()
    {
        if ($this->status !== 'buka') {
            return false;
        }

        $sekarang = now();
        $jamSekarang = $sekarang->format('H:i:s');

        return $jamSekarang >= $this->jam_buka->format('H:i:s') && 
               $jamSekarang <= $this->jam_tutup->format('H:i:s');
    }
}