@extends('layouts.main')

@section('title', 'Kelola Absensi')

@section('content')
<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                @include('partials.alert')
                
                <!-- Header dengan Button Tambah -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Jadwal Mengajar Hari Ini - {{ now()->locale('id')->translatedFormat('l, d F Y') }}</h4>
                        <button type="button" class="btn btn-primary" onclick="tambahAbsensi()">
                            <i class="fas fa-plus"></i> Tambah Absensi Manual
                        </button>
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
                                                $sesiAktif = $jadwal->sesiAbsensi()->where('tanggal', today())->first();
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
                                                    @if($sesiAktif)
                                                        @if($sesiAktif->status === 'buka')
                                                            <span class="badge bg-success">Absensi Dibuka</span>
                                                        @else
                                                            <span class="badge bg-secondary">Absensi Ditutup</span>
                                                        @endif
                                                    @else
                                                        @if($isAktif)
                                                            <span class="badge bg-warning text-dark">Sedang Berlangsung</span>
                                                        @elseif(now()->format('H:i:s') < $jadwal->jam_mulai->format('H:i:s'))
                                                            <span class="badge bg-info text-dark">Belum Dimulai</span>
                                                        @else
                                                            <span class="badge bg-secondary">Selesai</span>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($sesiAktif)
                                                        @if($sesiAktif->status === 'buka')
                                                            <button type="button" class="btn btn-sm btn-danger" 
                                                                    onclick="tutupAbsensi({{ $sesiAktif->id }})">
                                                                <i class="fas fa-times"></i> Tutup
                                                            </button>
                                                            <a href="{{ route('admin.absensi.detail', $sesiAktif->id) }}" 
                                                               class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i> Lihat
                                                            </a>
                                                        @else
                                                            <a href="{{ route('admin.absensi.detail', $sesiAktif->id) }}" 
                                                               class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i> Lihat
                                                            </a>
                                                        @endif
                                                    @else
                                                        @if($isAktif)
                                                            <button type="button" class="btn btn-sm btn-primary" 
                                                                    onclick="bukaAbsensi({{ $jadwal->id }})">
                                                                <i class="fas fa-play"></i> Buka
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-sm btn-success" 
                                                                    onclick="tambahAbsensiManual({{ $jadwal->id }})">
                                                                <i class="fas fa-plus"></i> Tambah
                                                            </button>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state text-center py-5">
                                <div class="empty-state-icon">
                                    <i class="fas fa-calendar-times fa-3x text-muted"></i>
                                </div>
                                <h2 class="mt-3">Tidak ada jadwal mengajar hari ini</h2>
                                <p class="lead">Belum ada jadwal mengajar yang terdaftar untuk hari {{ $hariIni }}.</p>
                                <button type="button" class="btn btn-primary mt-3" onclick="tambahAbsensi()">
                                    <i class="fas fa-plus"></i> Tambah Absensi Manual
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Card Sesi Absensi Hari Ini -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h4>Sesi Absensi Hari Ini</h4>
                    </div>
                    <div class="card-body">
                        @if($sesiHariIni->count() > 0)
                            <div class="row">
                                @foreach($sesiHariIni as $sesi)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-primary shadow-sm">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">{{ $sesi->jadwalMengajar->kelas->nama_kelas }} - {{ $sesi->jadwalMengajar->mapel->nama_mapel }}</h6>
                                                @if($sesi->status === 'buka')
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-secondary">Tutup</span>
                                                @endif
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
                                                    $alfa = $totalSiswa > 0 ? $totalSiswa - ($hadir + $izin + $sakit) : 0;
                                                @endphp
                                                
                                                <div class="mb-3">
                                                    <small class="text-muted">Statistik Absensi:</small>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar bg-success" style="width: {{ $totalSiswa > 0 ? ($hadir / $totalSiswa) * 100 : 0 }}%"></div>
                                                        <div class="progress-bar bg-warning" style="width: {{ $totalSiswa > 0 ? ($izin / $totalSiswa) * 100 : 0 }}%"></div>
                                                        <div class="progress-bar bg-info" style="width: {{ $totalSiswa > 0 ? ($sakit / $totalSiswa) * 100 : 0 }}%"></div>
                                                        <div class="progress-bar bg-danger" style="width: {{ $totalSiswa > 0 ? ($alfa / $totalSiswa) * 100 : 0 }}%"></div>
                                                    </div>
                                                    <div class="mt-2">
                                                        <span class="badge bg-success">H: {{ $hadir }}</span>
                                                        <span class="badge bg-warning text-dark">I: {{ $izin }}</span>
                                                        <span class="badge bg-info text-dark">S: {{ $sakit }}</span>
                                                        <span class="badge bg-danger">A: {{ $alfa }}</span>
                                                        <span class="badge bg-light text-dark">Total: {{ $totalSiswa }}</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="text-center">
                                                    <a href="{{ route('admin.absensi.detail', $sesi->id) }}" 
                                                       class="btn btn-sm btn-primary w-100">
                                                        <i class="fas fa-list"></i> Kelola Absensi
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state text-center py-5">
                                <div class="empty-state-icon">
                                    <i class="fas fa-clipboard-list fa-3x text-muted"></i>
                                </div>
                                <h2 class="mt-3">Belum ada sesi absensi hari ini</h2>
                                <p class="lead">Buka sesi absensi untuk jadwal yang sedang berlangsung atau tambah absensi manual.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Tambah Absensi Manual -->
<div class="modal fade" id="modalTambahAbsensi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Absensi Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="formTambahAbsensi" method="POST" action="{{ route('admin.absensi.tambah-manual') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="jadwal_mengajar_id" class="form-label">Pilih Jadwal <span class="text-danger">*</span></label>
                            <select class="form-control" name="jadwal_mengajar_id" id="jadwal_mengajar_id" required>
                                <option value="">-- Pilih Jadwal --</option>
                                @foreach($jadwalList as $jadwal)
                                    <option value="{{ $jadwal->id }}" 
                                            data-guru="{{ $jadwal->guru->nama }}"
                                            data-kelas="{{ $jadwal->kelas->nama_kelas }}"
                                            data-mapel="{{ $jadwal->mapel->nama_mapel }}"
                                            data-hari="{{ ucfirst($jadwal->hari) }}"
                                            data-jam-mulai="{{ $jadwal->jam_mulai->format('H:i') }}"
                                            data-jam-selesai="{{ $jadwal->jam_selesai->format('H:i') }}">
                                        {{ $jadwal->kelas->nama_kelas }} - {{ $jadwal->mapel->nama_mapel }} ({{ ucfirst($jadwal->hari) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tanggal" class="form-label">Tanggal Absensi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal" id="tanggal" value="{{ today()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="jam_buka" class="form-label">Jam Buka <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="jam_buka" id="jam_buka" required>
                        </div>
                        <div class="col-md-6">
                            <label for="jam_tutup" class="form-label">Jam Tutup <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="jam_tutup" id="jam_tutup" required>
                        </div>
                        <div class="col-md-12">
                            <label for="status" class="form-label">Status Sesi <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" id="status" required>
                                <option value="buka">Buka</option>
                                <option value="tutup">Tutup</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea class="form-control" name="catatan" id="catatan" rows="3" placeholder="Masukkan catatan untuk absensi ini..."></textarea>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info d-none" id="jadwalPreview">
                                <h6><i class="fas fa-info-circle"></i> Informasi Jadwal:</h6>
                                <p id="previewInfo" class="mb-0"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Absensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Buka Absensi -->
<div class="modal fade" id="modalBukaAbsensi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buka Sesi Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="formBukaAbsensi" method="POST" action="{{ route('admin.absensi.buka') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="jadwal_mengajar_id" id="jadwal_id">
                    <div class="mb-3">
                        <label class="form-label">Jadwal Mengajar</label>
                        <div id="jadwal_info" class="form-control-plaintext bg-light p-2 rounded"></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="jam_buka_modal" class="form-label">Jam Buka Absensi <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="jam_buka" id="jam_buka_modal" required>
                        </div>
                        <div class="col-md-6">
                            <label for="jam_tutup_modal" class="form-label">Jam Tutup Absensi <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="jam_tutup" id="jam_tutup_modal" required>
                        </div>
                        <div class="col-12">
                            <label for="catatan_modal" class="form-label">Catatan <span class="text-muted">(Opsional)</span></label>
                            <textarea class="form-control" name="catatan" id="catatan_modal" rows="3" placeholder="Masukkan catatan untuk sesi absensi ini..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play"></i> Buka Absensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Tutup Absensi -->
<div class="modal fade" id="modalTutupAbsensi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Tutup Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menutup sesi absensi ini?</p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Setelah ditutup, guru tidak dapat lagi mengisi absensi siswa.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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
// Fungsi untuk membuka modal tambah absensi
function tambahAbsensi() {
    $('#modalTambahAbsensi').modal('show');
}

// Fungsi untuk membuka modal absensi berdasarkan jadwal
function bukaAbsensi(jadwalId) {
    const jadwal = $('#jadwal_mengajar_id option[value="' + jadwalId + '"]');
    const jadwalInfo = `${jadwal.data('kelas')} - ${jadwal.data('mapel')}<br>
                       Guru: ${jadwal.data('guru')}<br>
                       ${jadwal.data('hari')}, ${jadwal.data('jam-mulai')} - ${jadwal.data('jam-selesai')}`;
    
    $('#jadwal_id').val(jadwalId);
    $('#jadwal_info').html(jadwalInfo);
    $('#jam_buka_modal').val(jadwal.data('jam-mulai'));
    $('#jam_tutup_modal').val(jadwal.data('jam-selesai'));
    
    $('#modalBukaAbsensi').modal('show');
}

// Fungsi untuk membuka modal tutup absensi
function tutupAbsensi(sesiId) {
    $('#formTutupAbsensi').attr('action', `/admin/absensi/${sesiId}/tutup`);
    $('#modalTutupAbsensi').modal('show');
}

// Event listener saat memilih jadwal di modal tambah manual
$('#jadwal_mengajar_id').change(function() {
    const selected = $(this).find(':selected');
    if (selected.val()) {
        const preview = `
            Kelas: ${selected.data('kelas')}<br>
            Mata Pelajaran: ${selected.data('mapel')}<br>
            Guru: ${selected.data('guru')}<br>
            Jadwal: ${selected.data('hari')}, ${selected.data('jam-mulai')} - ${selected.data('jam-selesai')}
        `;
        $('#previewInfo').html(preview);
        $('#jadwalPreview').removeClass('d-none');
        
        // Set default jam buka & tutup sesuai jadwal
        $('#jam_buka').val(selected.data('jam-mulai'));
        $('#jam_tutup').val(selected.data('jam-selesai'));
    } else {
        $('#jadwalPreview').addClass('d-none');
    }
});

// Validasi form sebelum submit
$('#formTambahAbsensi, #formBukaAbsensi').submit(function(e) {
    e.preventDefault();
    
    const jamBuka = $(this).find('input[name="jam_buka"]').val();
    const jamTutup = $(this).find('input[name="jam_tutup"]').val();
    
    if (jamBuka >= jamTutup) {
        alert('Jam tutup harus lebih besar dari jam buka!');
        return false;
    }
    
    this.submit();
});

// Inisialisasi komponen form
$(document).ready(function() {
    // Set tanggal default ke hari ini
    const today = new Date().toISOString().split('T')[0];
    $('#tanggal').val(today);
    
    // Reset form saat modal ditutup
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form').trigger('reset');
        $('#jadwalPreview').addClass('d-none');
    });
});
</script>
@endpush
@endsection
