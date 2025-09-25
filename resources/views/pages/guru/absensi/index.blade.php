@extends('layouts.main')
@section('title', 'Absensi Sesi Hari Ini')

@section('content')
<section class="section">
    <div class="section-body">
        <div class="row">
            @forelse($sesiAktif as $sesi)
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div>
                                <h4>{{ $sesi->jadwalMengajar->kelas->nama_kelas }} - {{ $sesi->jadwalMengajar->mapel->nama_mapel }}</h4>
                                <small>{{ $sesi->tanggal->locale('id')->translatedFormat('l, d F Y') }}</small>
                            </div>
                            <a href="{{ route('guru.absensi.absen', $sesi->id) }}" class="btn btn-primary">
                                <i class="fas fa-check"></i> Absen
                            </a>
                        </div>
                        <div class="card-body">
                            <p>Status: 
                                @if($sesi->isBisaDiakses())
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Tidak Aktif</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning">
                        Tidak ada sesi absensi aktif hari ini.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
