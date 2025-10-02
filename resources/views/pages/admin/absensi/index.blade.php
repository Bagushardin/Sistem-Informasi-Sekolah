@extends('layouts.main')

@section('title', 'Kelola Absensi')

@section('content')
<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                @include('partials.alert')
                
                <!-- Card Semua Jadwal -->
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Jadwal Mengajar</h4>
                    </div>
                    <div class="card-body">
                        @if($jadwalHariIni->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Hari</th>
                                            <th>Waktu</th>
                                            <th>Guru</th>
                                            <th>Kelas</th>
                                            <th>Mata Pelajaran</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jadwalHariIni as $jadwal)
                                            <tr>
                                                <td>{{ ucfirst($jadwal->hari) }}</td>
                                                <td><strong>{{ $jadwal->jam_mulai->format('H:i') }} - {{ $jadwal->jam_selesai->format('H:i') }}</strong></td>
                                                <td>{{ $jadwal->guru->nama }}</td>
                                                <td>{{ $jadwal->kelas->nama_kelas }}</td>
                                                <td>{{ $jadwal->mapel->nama_mapel }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <h2>Tidak ada jadwal mengajar</h2>
                                <p class="lead">Belum ada jadwal yang terdaftar.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Card Semua Sesi Absensi -->
                <div class="card">
                    <div class="card-header">
                        <h4>Semua Sesi Absensi</h4>
                    </div>
                    <div class="card-body">
                        @if($sesiHariIni->count() > 0)
                            <div class="row">
                                @foreach($sesiHariIni as $sesi)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card card-primary">
                                            <div class="card-header">
                                                <h4>
                                                    {{ $sesi->jadwalMengajar->kelas->nama_kelas }} - 
                                                    {{ $sesi->jadwalMengajar->mapel->nama_mapel }}
                                                </h4>
                                                <div class="card-header-action">
                                                    @if($sesi->status === 'buka')
                                                        <span class="badge badge-success">Aktif</span>
                                                    @else
                                                        <span class="badge badge-secondary">Tutup</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p><strong>Guru:</strong> {{ $sesi->jadwalMengajar->guru->nama }}</p>
                                                <p><strong>Tanggal:</strong> {{ $sesi->tanggal->format('d-m-Y') }}</p>
                                                <p><strong>Waktu:</strong> {{ $sesi->jam_buka->format('H:i') }} - {{ $sesi->jam_tutup->format('H:i') }}</p>
                                                @if($sesi->catatan)
                                                    <p><strong>Catatan:</strong> {{ $sesi->catatan }}</p>
                                                @endif
                                                
                                                @php
                                                    $totalSiswa = $sesi->absensi->count();
                                                    $hadir = $sesi->absensi->where('status', 'hadir')->count();
                                                    $izin = $sesi->absensi->where('status', 'izin')->count();
                                                    $sakit = $sesi->absensi->where('status', 'sakit')->count();
                                                    $alfa = $sesi->absensi->where('status', 'alfa')->count();
                                                @endphp
                                                
                                                <div class="mb-3">
                                                    <small class="text-muted">Statistik Absensi:</small>
                                                    <div class="progress-group">
                                                        <div class="progress-group-header">
                                                            <div class="progress-group-bars">
                                                                <div class="progress" style="height: 6px;">
                                                                    <div class="progress-bar bg-success" style="width: {{ $totalSiswa > 0 ? ($hadir / $totalSiswa) * 100 : 0 }}%"></div>
                                                                    <div class="progress-bar bg-warning" style="width: {{ $totalSiswa > 0 ? ($izin / $totalSiswa) * 100 : 0 }}%"></div>
                                                                    <div class="progress-bar bg-info" style="width: {{ $totalSiswa > 0 ? ($sakit / $totalSiswa) * 100 : 0 }}%"></div>
                                                                    <div class="progress-bar bg-danger" style="width: {{ $totalSiswa > 0 ? ($alfa / $totalSiswa) * 100 : 0 }}%"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="progress-group-label">
                                                            <small>
                                                                <span class="badge badge-success">H: {{ $hadir }}</span>
                                                                <span class="badge badge-warning">I: {{ $izin }}</span>
                                                                <span class="badge badge-info">S: {{ $sakit }}</span>
                                                                <span class="badge badge-danger">A: {{ $alfa }}</span>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <h2>Belum ada sesi absensi</h2>
                                <p class="lead">Sesi absensi akan tampil di sini setelah dibuka.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
