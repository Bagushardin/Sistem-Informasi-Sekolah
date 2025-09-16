<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_absensi_id')->constrained('sesi_absensi')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alfa'])->default('alfa');
            $table->text('keterangan')->nullable();
            $table->timestamp('waktu_absen')->nullable();
            $table->foreignId('diabsen_oleh')->constrained('users')->onDelete('cascade'); // Guru yang mengabsen
            $table->timestamps();
            
            $table->unique(['sesi_absensi_id', 'siswa_id']); // Satu absen per siswa per sesi
        });
    }

    public function down()
    {
        Schema::dropIfExists('absensi');
    }
};
