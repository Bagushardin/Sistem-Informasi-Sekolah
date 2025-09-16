@extends('layouts.main')

@section('title', 'Absensi Saya')

@section('content')
<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                @include('partials.alert')
                
                <!-- Statistik Absensi -->
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Hadir</h4>
                                </div>
                                <div class="card-body">
                                    {{ $stats['hadir'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-exclamation"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Izin</h4>
                                </div>
                                <div class="card-body">
                                    {{ $stats['izin'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-thermometer-half"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Sakit</h4>
                                </div>
                                <div class="card-body">
                                    {{ $stats['sakit'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-times"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Alfa</h4>
                                </div>
                                <div class="card-body">
                                    {{ $stats['alfa'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grafik Kehadiran -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>Riwayat Absensi - {{ now()->locale('id')->translatedFormat('F Y') }}</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="chartAbsensi"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Persentase Kehadiran</h4>
                            </div>
                            <div class="card-body">
                                @php
                                    $total = array_sum($stats);
                                    $persentaseHadir = $total > 0 ? round(($stats['hadir'] / $total) * 100, 1) : 0;
                                @endphp
                                
                                <div class="text-center">
                                    <div class="d-inline-block">
                                        <canvas id="chartPersentase" width="150" height="150"></canvas>
                                        <div class="mt-2">
                                            <h2 class="section-title">{{ $persentaseHadir }}%</h2>
                                            <p class="text-muted">Tingkat Kehadiran</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="pt-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Status Kehadiran:</span>
                                        @if($persentaseHadir >= 80)
                                            <span class="badge badge-success">Sangat Baik</span>
                                        @elseif($persentaseHadir >= 60)
                                            <span class="badge badge-warning">Baik</span>
                                        @else
                                            <span class="badge badge-danger">Perlu Perbaikan</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Riwayat Absensi -->
                <div class="card">
                    <div class="card-header">
                        <h4>Riwayat Absensi Bulan Ini</h4>
                    </div>
                    <div class="card-body">
                        @if($absensi->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-absensi">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Mata Pelajaran</th>
                                            <th>Guru</th>
                                            <th>Status</th>
                                            <th>Keterangan</th>
                                            <th>Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($absensi as $data)
                                            <tr>
                                                <td>
                                                    <strong>{{ $data->sesiAbsensi->tanggal->locale('id')->translatedFormat('d M Y') }}</strong><br>
                                                    <small class="text-muted">{{ $data->sesiAbsensi->tanggal->locale('id')->translatedFormat('l') }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ $data->sesiAbsensi->jadwalMengajar->mapel->nama_mapel }}</strong>
                                                </td>
                                                <td>{{ $data->sesiAbsensi->jadwalMengajar->guru->nama }}</td>
                                                <td>
                                                    @switch($data->status)
                                                        @case('hadir')
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check"></i> Hadir
                                                            </span>
                                                            @break
                                                        @case('izin')
                                                            <span class="badge badge-warning">
                                                                <i class="fas fa-exclamation"></i> Izin
                                                            </span>
                                                            @break
                                                        @case('sakit')
                                                            <span class="badge badge-info">
                                                                <i class="fas fa-thermometer-half"></i> Sakit
                                                            </span>
                                                            @break
                                                        @case('alfa')
                                                            <span class="badge badge-danger">
                                                                <i class="fas fa-times"></i> Alfa
                                                            </span>
                                                            @break
                                                    @endswitch
                                                </td>
                                                <td>{{ $data->keterangan ?? '-' }}</td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $data->sesiAbsensi->jam_buka->format('H:i') }} - {{ $data->sesiAbsensi->jam_tutup->format('H:i') }}
                                                        @if($data->waktu_absen)
                                                            <br>Diupdate: {{ $data->waktu_absen->format('H:i') }}
                                                        @endif
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <h2>Belum ada data absensi</h2>
                                <p class="lead">Data absensi Anda untuk bulan ini belum tersedia.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Ringkasan Per Mata Pelajaran -->
                <div class="card">
                    <div class="card-header">
                        <h4>Ringkasan Per Mata Pelajaran</h4>
                    </div>
                    <div class="card-body">
                        @php
                            $absensiPerMapel = $absensi->groupBy('sesiAbsensi.jadwalMengajar.mapel.nama_mapel');
                        @endphp
                        
                        @if($absensiPerMapel->count() > 0)
                            <div class="row">
                                @foreach($absensiPerMapel as $namaMapel => $dataMapel)
                                    @php
                                        $totalPerMapel = $dataMapel->count();
                                        $hadirPerMapel = $dataMapel->where('status', 'hadir')->count();
                                        $persentasePerMapel = $totalPerMapel > 0 ? round(($hadirPerMapel / $totalPerMapel) * 100, 1) : 0;
                                    @endphp
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h6 class="card-title">{{ $namaMapel }}</h6>
                                                        <p class="card-text">
                                                            <small class="text-muted">{{ $totalPerMapel }} pertemuan</small>
                                                        </p>
                                                    </div>
                                                    <div class="text-right">
                                                        <h4 class="{{ $persentasePerMapel >= 80 ? 'text-success' : ($persentasePerMapel >= 60 ? 'text-warning' : 'text-danger') }}">
                                                            {{ $persentasePerMapel }}%
                                                        </h4>
                                                    </div>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar {{ $persentasePerMapel >= 80 ? 'bg-success' : ($persentasePerMapel >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                                         style="width: {{ $persentasePerMapel }}%"></div>
                                                </div>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        H: {{ $dataMapel->where('status', 'hadir')->count() }} |
                                                        I: {{ $dataMapel->where('status', 'izin')->count() }} |
                                                        S: {{ $dataMapel->where('status', 'sakit')->count() }} |
                                                        A: {{ $dataMapel->where('status', 'alfa')->count() }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <p>Belum ada data absensi per mata pelajaran.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // DataTable
    $('#table-absensi').DataTable({
        "order": [[ 0, "desc" ]], // Sort by date descending
        "pageLength": 15,
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir", 
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });

    // Chart Absensi per Hari
    const absensiData = @json($absensi);
    const chartData = processAbsensiData(absensiData);
    
    const ctx = document.getElementById('chartAbsensi').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Kehadiran',
                data: chartData.data,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 1,
                    ticks: {
                        callback: function(value) {
                            return value === 1 ? 'Hadir' : (value === 0 ? 'Tidak Hadir' : '');
                        }
                    }
                }
            }
        }
    });

    // Chart Persentase (Doughnut)
    const stats = @json($stats);
    const ctxPersentase = document.getElementById('chartPersentase').getContext('2d');
    new Chart(ctxPersentase, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Izin', 'Sakit', 'Alfa'],
            datasets: [{
                data: [stats.hadir, stats.izin, stats.sakit, stats.alfa],
                backgroundColor: [
                    '#28a745',
                    '#ffc107', 
                    '#17a2b8',
                    '#dc3545'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});

function processAbsensiData(data) {
    const grouped = {};
    
    data.forEach(item => {
        const date = item.sesi_absensi.tanggal;
        if (!grouped[date]) {
            grouped[date] = [];
        }
        grouped[date].push(item.status === 'hadir' ? 1 : 0);
    });

    const labels = Object.keys(grouped).sort();
    const chartData = labels.map(date => {
        const dayData = grouped[date];
        return dayData.reduce((sum, val) => sum + val, 0) / dayData.length;
    });

    return {
        labels: labels.map(date => {
            const d = new Date(date);
            return d.getDate() + '/' + (d.getMonth() + 1);
        }),
        data: chartData
    };
}
</script>
@endpush
@endsection