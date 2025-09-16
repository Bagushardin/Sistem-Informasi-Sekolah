<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';
    
    protected $fillable = [
        'sesi_absensi_id',
        'siswa_id',
        'status',
        'keterangan',
        'waktu_absen',
        'diabsen_oleh'
    ];

    protected $casts = [
        'waktu_absen' => 'datetime'
    ];

    public function sesiAbsensi()
    {
        return $this->belongsTo(SesiAbsensi::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function diabsenOleh()
    {
        return $this->belongsTo(User::class, 'diabsen_oleh');
    }

    // Scope untuk filter berdasarkan status
    public function scopeHadir($query)
    {
        return $query->where('status', 'hadir');
    }

    public function scopeIzin($query)
    {
        return $query->where('status', 'izin');
    }

    public function scopeSakit($query)
    {
        return $query->where('status', 'sakit');
    }

    public function scopeAlfa($query)
    {
        return $query->where('status', 'alfa');
    }
}
 ?>