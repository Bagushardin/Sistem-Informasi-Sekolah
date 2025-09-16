@extends('layouts.main')

@section('title', 'Absen Siswa')

@section('content')
<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                @include('partials.alert')
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div>
                            <h4>Absensi Siswa</h4>
                            <p class="text-muted mb-0">
                                {{ $sesi->jadwalMengajar->kelas->nama_kelas }} - {{ $sesi->jadwalMengajar->mapel->nama_mapel }}
                            </p>
                        </div>
                        <a href="{{ route('guru.absensi.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                    
                    <div class="card-body">
                        <!-- Info Sesi -->
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Tanggal:</strong><br>
                                    {{ $sesi->tanggal->locale('id')->translatedFormat('l, d F Y') }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Waktu Absensi:</strong><br>
                                    {{ $sesi->jam_buka->format('H:i') }} - {{ $sesi->jam_tutup->format('H:i') }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Status Sesi:</strong><br>
                                    @if($sesi->isBisaDiakses())
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Tidak Aktif</span>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Siswa:</strong><br>
                                    {{ $absensi->count() }} siswa
                                </div>
                            </div>
                            @if($sesi->catatan)
                                <div class="mt-2">
                                    <strong>Catatan:</strong> {{ $sesi->catatan }}
                                </div>
                            @endif
                        </div>

                        @if($sesi->isBisaDiakses())
                            <form method="POST" action="{{ route('guru.absensi.update', $sesi->id) }}" id="formAbsensi">
                                @csrf
                                @method('PUT')
                                
                                <!-- Tombol Cepat -->
                                <div class="mb-3">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-success btn-sm" onclick="setAllStatus('hadir')">
                                            <i class="fas fa-check"></i> Semua Hadir
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="setAllStatus('izin')">
                                            <i class="fas fa-exclamation"></i> Semua Izin
                                        </button>
                                        <button type="button" class="btn btn-info btn-sm" onclick="setAllStatus('sakit')">
                                            <i class="fas fa-thermometer-half"></i> Semua Sakit
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="setAllStatus('alfa')">
                                            <i class="fas fa-times"></i> Semua Alfa
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-absensi">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>NIS</th>
                                                <th>Nama Siswa</th>
                                                <th>Status Absensi</th>
                                                <th>Keterangan</th>
                                                <th>Waktu Update</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($absensi as $index => $data)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $data->siswa->nis }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($data->siswa->foto && Storage::disk('public')->exists($data->siswa->foto))
                                                                <img src="{{ asset('storage/' . $data->siswa->foto) }}" 
                                                                     alt="{{ $data->siswa->nama }}" 
                                                                     class="rounded-circle mr-2" 
                                                                     style="width: 35px; height: 35px; object-fit: cover;">
                                                            @else
                                                                <div class="avatar avatar-sm bg-primary text-white rounded-circle mr-2">
                                                                    {{ strtoupper(substr($data->siswa->nama, 0, 1)) }}
                                                                </div>
                                                            @endif
                                                            <strong>{{ $data->siswa->nama }}</strong>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group mb-0">
                                                            <select name="absensi[{{ $data->id }}]" class="form-control form-control-sm status-select" data-siswa="{{ $data->siswa->nama }}">
                                                                <option value="hadir" {{ $data->status === 'hadir' ? 'selected' : '' }}>
                                                                    ✓ Hadir
                                                                </option>
                                                                <option value="izin" {{ $data->status === 'izin' ? 'selected' : '' }}>
                                                                    ⚠ Izin
                                                                </option>
                                                                <option value="sakit" {{ $data->status === 'sakit' ? 'selected' : '' }}>
                                                                    🌡 Sakit
                                                                </option>
                                                                <option value="alfa" {{ $data->status === 'alfa' ? 'selected' : '' }}>
                                                                    ✗ Alfa
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="text" 
                                                               name="keterangan[{{ $data->id }}]" 
                                                               class="form-control form-control-sm" 
                                                               placeholder="Keterangan (opsional)" 
                                                               value="{{ $data->keterangan }}">
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            @if($data->waktu_absen)
                                                                {{ $data->waktu_absen->format('H:i:s') }}
                                                            @else
                                                                Belum diupdate
                                                            @endif
                                                        </small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Summary -->
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6>Ringkasan Absensi</h6>
                                                <div id="summary">
                                                    <span class="badge badge-success">Hadir: <span id="count-hadir">0</span></span>
                                                    <span class="badge badge-warning">Izin: <span id="count-izin">0</span></span>
                                                    <span class="badge badge-info">Sakit: <span id="count-sakit">0</span></span>
                                                    <span class="badge badge-danger">Alfa: <span id="count-alfa">0</span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-right">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-save"></i> Simpan Absensi
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Sesi absensi ini sudah ditutup atau belum dimulai. Anda tidak dapat mengubah data absensi.
                            </div>
                            
                            <!-- View Only Table -->
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>NIS</th>
                                            <th>Nama Siswa</th>
                                            <th>Status</th>
                                            <th>Keterangan</th>
                                            <th>Waktu Update</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($absensi as $index => $data)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $data->siswa->nis }}</td>
                                                <td>{{ $data->siswa->nama }}</td>
                                                <td>
                                                    @switch($data->status)
                                                        @case('hadir')
                                                            <span class="badge badge-success">✓ Hadir</span>
                                                            @break
                                                        @case('izin')
                                                            <span class="badge badge-warning">⚠ Izin</span>
                                                            @break
                                                        @case('sakit')
                                                            <span class="badge badge-info">🌡 Sakit</span>
                                                            @break
                                                        @case('alfa')
                                                            <span class="badge badge-danger">✗ Alfa</span>
                                                            @break
                                                    @endswitch
                                                </td>
                                                <td>{{ $data->keterangan ?? '-' }}</td>
                                                <td>
                                                    <small class="text-muted">
                                                        @if($data->waktu_absen)
                                                            {{ $data->waktu_absen->format('H:i:s') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
function setAllStatus(status) {
    $('.status-select').val(status);
    updateSummary();
}

function updateSummary() {
    let counts = {
        hadir: 0,
        izin: 0,
        sakit: 0,
        alfa: 0
    };

    $('.status-select').each(function() {
        counts[$(this).val()]++;
    });

    $('#count-hadir').text(counts.hadir);
    $('#count-izin').text(counts.izin);
    $('#count-sakit').text(counts.sakit);
    $('#count-alfa').text(counts.alfa);
}

$(document).ready(function() {
    // Initial summary update
    updateSummary();

    // Update summary when status changes
    $('.status-select').on('change', function() {
        updateSummary();
    });

    // Form submission confirmation
    $('#formAbsensi').on('submit', function(e) {
        e.preventDefault();
        
        if (confirm('Apakah Anda yakin ingin menyimpan data absensi ini?')) {
            $(this).unbind('submit').submit();
        }
    });

    // DataTable for better UX
    $('#table-absensi').DataTable({
        "pageLength": 25,
        "ordering": false,
        "searching": true,
        "language": {
            "search": "Cari siswa:",
            "lengthMenu": "Tampilkan _MENU_ siswa per halaman",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ siswa",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });
});
</script>
@endpush
@endsection