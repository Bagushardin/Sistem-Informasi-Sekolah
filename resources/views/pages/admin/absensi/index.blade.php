@extends('layouts.main')

@section('title', 'Kelola Absensi')

@section('content')
<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                @include('partials.alert')
                
                <!-- Card Jadwal Hari Ini -->
                <div class="card">
                    <div class="card-header">
                        <h4>Jadwal Mengajar Hari Ini - {{ now()->locale('id')->translatedFormat('l, d F Y') }}</h4>
                    </div>
                    <div class="card-body">
                        @if($jadwalHariIni->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Guru</th>
                                            <th>Kelas</th>
                                            <th>Mata Pelajaran</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jadwalHariIni as $jadwal)
                                            @php
                                                $sesiHariIni = $jadwal->sesiAbsensi()->where('tanggal', today())->first();
                                                $isAktif = $jadwal->isAktifSekarang();
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $jadwal->jam_mulai->format('H:i') }} - {{ $jadwal->jam_selesai->format('H:i') }}</strong>
                                                </td>
                                                <td>{{ $jadwal->guru->nama }}</td>
                                                <td>{{ $jadwal->kelas->nama_kelas }}</td>
                                                <td>{{ $jadwal->mapel->nama_mapel }}</td>
                                                <td>
                                                    @if($sesiHariIni)
                                                        @if($sesiHariIni->status === 'buka')
                                                            <span class="badge badge-success">Absensi Dibuka</span>
                                                        @else
                                                            <span class="badge badge-secondary">Absensi Ditutup</span>
                                                        @endif
                                                    @else
                                                        @if($isAktif)
                                                            <span class="badge badge-warning">Sedang Berlangsung</span>
                                                        @elseif(now()->format('H:i:s') < $jadwal->jam_mulai->format('H:i:s'))
                                                            <span class="badge badge-info">Belum Dimulai</span>
                                                        @else
                                                            <span class="badge badge-secondary">Selesai</span>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($sesiHariIni)
                                                        @if($sesiHariIni->status === 'buka')
                                                            <button type="button" class="btn btn-sm btn-danger" 
                                                                    onclick="tutupAbsensi({{ $sesiHariIni->id }})">
                                                                <i class="fas fa-times"></i> Tutup Absensi
                                                            </button>
                                                        @else
                                                            <span class="text-muted">Sudah ditutup</span>
                                                        @endif
                                                    @else
                                                        @if($isAktif)
                                                            <button type="button" class="btn btn-sm btn-primary" 
                                                                    onclick="bukaAbsensi({{ $jadwal->id }})">
                                                                <i class="fas fa-play"></i> Buka Absensi
                                                            </button>
                                                        @else
                                                            <span class="text-muted">Tidak dapat dibuka</span>
                                                        @endif
                                                    @endif
                                                </td>
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
                                <h2>Tidak ada jadwal mengajar hari ini</h2>
                                <p class="lead">Belum ada jadwal mengajar yang terdaftar untuk hari ini.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Card Sesi Absensi Hari Ini -->
                <div class="card">
                    <div class="card-header">
                        <h4>Sesi Absensi Hari Ini</h4>
                    </div>
                    <div class="card-body">
                        @if($sesiHariIni->count() > 0)
                            <div class="row">
                                @foreach($sesiHariIni as $sesi)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card card-primary">
                                            <div class="card-header">
                                                <h4>{{ $sesi->jadwalMengajar->kelas->nama_kelas }} - {{ $sesi->jadwalMengajar->mapel->nama_mapel }}</h4>
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
                                <h2>Belum ada sesi absensi hari ini</h2>
                                <p class="lead">Buka sesi absensi untuk jadwal yang sedang berlangsung.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Buka Absensi -->
<div class="modal fade" id="modalBukaAbsensi" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buka Sesi Absensi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formBukaAbsensi" method="POST" action="{{ route('admin.absensi.buka') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="jadwal_mengajar_id" id="jadwal_id">
                    
                    <div class="form-group">
                        <label>Jadwal Mengajar</label>
                        <div id="jadwal_info" class="form-control-plaintext"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jam_buka">Jam Buka Absensi <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('jam_buka') is-invalid @enderror" 
                                       name="jam_buka" id="jam_buka" required>
                                @error('jam_buka')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jam_tutup">Jam Tutup Absensi <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('jam_tutup') is-invalid @enderror" 
                                       name="jam_tutup" id="jam_tutup" required>
                                @error('jam_tutup')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="catatan">Catatan <span class="text-muted">(Opsional)</span></label>
                        <textarea class="form-control" name="catatan" id="catatan" rows="3" 
                                  placeholder="Masukkan catatan untuk sesi absensi ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play"></i> Buka Absensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Tutup Absensi -->
<div class="modal fade" id="modalTutupAbsensi" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Tutup Absensi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menutup sesi absensi ini?</p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Setelah ditutup, guru tidak dapat lagi mengisi absensi siswa.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form id="formTutupAbsensi" method="POST" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Ya, Tutup Absensi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let jadwalData = @json($jadwalHariIni);

function bukaAbsensi(jadwalId) {
    const jadwal = jadwalData.find(j => j.id === jadwalId);
    
    document.getElementById('jadwal_id').value = jadwalId;
    document.getElementById('jadwal_info').innerHTML = `
        <strong>${jadwal.kelas.nama_kelas}</strong> - ${jadwal.mapel.nama_mapel}<br>
        <small class="text-muted">Guru: ${jadwal.guru.nama} | Waktu: ${jadwal.jam_mulai} - ${jadwal.jam_selesai}</small>
    `;
    
    // Set default jam buka dan tutup berdasarkan jadwal
    document.getElementById('jam_buka').value = jadwal.jam_mulai.substring(0, 5);
    document.getElementById('jam_tutup').value = jadwal.jam_selesai.substring(0, 5);
    
    $('#modalBukaAbsensi').modal('show');
}

function tutupAbsensi(sesiId) {
    document.getElementById('formTutupAbsensi').action = `{{ route('admin.absensi.tutup', '') }}/${sesiId}`;
    $('#modalTutupAbsensi').modal('show');
}

// Auto reload page every 5 minutes untuk update status
setInterval(function() {
    location.reload();
}, 300000);
</script>
@endpush
@endsection