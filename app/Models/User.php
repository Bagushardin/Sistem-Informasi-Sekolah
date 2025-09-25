<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'roles',
        'nis',
        'nip'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relasi ke model Guru (jika user adalah guru)
     */
    public function guru()
    {
        return $this->hasOne(Guru::class, 'user_id', 'id', 'email');
        // atau jika menggunakan NIP sebagai foreign key:
        // return $this->hasOne(Guru::class, 'nip', 'nip');
    }

//     public function guru()
// {
//     return $this->hasOne(Guru::class, 'user_id', 'id');
// }


    /**
     * Relasi ke model Siswa (jika user adalah siswa)
     */
    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'user_id');
        // atau jika menggunakan NIS sebagai foreign key:
        // return $this->hasOne(Siswa::class, 'nis', 'nis');
    }

    /**
     * Helper method untuk mendapatkan data guru (jika perlu)
     */
    // public function getGuruData()
    // {
    //     if ($this->roles !== 'guru') {
    //         return null;
    //     }
        
    //     // Coba cari by user_id dulu
    //     $guru = Guru::where('user_id', $this->id)->first();
        
    //     // Jika tidak ditemukan, coba cari by email
    //     if (!$guru && $this->email) {
    //         $guru = Guru::where('email', $this->email)->first();

    //         // Jika ditemukan by email, update user_id untuk konsistensi
    //         if ($guru) {
    //             $guru->update(['user_id' => $this->id]);
    //         }
    //     }
        
    //     return $guru;
    // }

    /**
     * Helper method untuk mendapatkan data siswa (jika perlu)
     */
    public function getSiswaData()
    {
        if ($this->roles !== 'siswa') {
            return null;
        }
        
        // Coba cari by user_id dulu
        $siswa = Siswa::where('user_id', $this->id)->first();
        
        // Jika tidak ditemukan, coba cari by NIS
        if (!$siswa && $this->nis) {
            $siswa = Siswa::where('nis', $this->nis)->first();
            
            // Jika ditemukan by NIS, update user_id untuk konsistensi
            if ($siswa) {
                $siswa->update(['user_id' => $this->id]);
            }
        }
        
        return $siswa;
    }

    
}