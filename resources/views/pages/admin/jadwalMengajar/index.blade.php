@extends('layouts.main')

@section('title', 'Manajemen Jadwal Mengajar')

@section('content')
<div class="section-body">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Jadwal Mengajar</h4>
                    <div class="card-header-form">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#filterModal">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.jadwalMengajar.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Tambah Jadwal
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @foreach($hari as $h)
                    <div class="mb-4">
                        <h5 class="text-capitalize">{{ $h }}</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Jam</th>
                                        <th>Guru</th>
                                        <th>Kelas</th>
                                        <th>Mapel</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jadwalGrouped[$h] as $jadwal)
                                    <tr>
                                        <td>
                                            {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                        </td>
                                        <td>
                                            {{ $jadwal->guru->user->name ?? 'N/A' }}
                                            @if(!$jadwal->guru->user)
                                                <span class="badge badge-warning">User tidak ditemukan</span>
                                            @endif
                                        </td>
                                        <td>{{ $jadwal->kelas->nama_kelas ?? 'N/A' }}</td>
                                        <td>{{ $jadwal->mapel->nama_mapel ?? 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.jadwalMengajar.edit', $jadwal->id) }}" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.jadwalMengajar.destroy', $jadwal->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Hapus jadwal ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada jadwal</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter Jadwal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="form-group">
                        <label>Guru</label>
                        <select name="guru_id" class="form-control">
                            <option value="">Semua Guru</option>
                            @foreach($guru as $g)
                                <option value="{{ $g->id }}" {{ request('guru_id') == $g->id ? 'selected' : '' }}>
                                    {{ $g->user->name ?? 'User Tidak Ditemukan' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas_id" class="form-control">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Hari</label>
                        <select name="hari" class="form-control">
                            <option value="">Semua Hari</option>
                            @foreach($hari as $h)
                                <option value="{{ $h }}" {{ request('hari') == $h ? 'selected' : '' }}>
                                    {{ ucfirst($h) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="applyFilter">Terapkan Filter</button>
                <a href="{{ route('admin.jadwalMengajar.index') }}" class="btn btn-warning">Reset</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#applyFilter').click(function() {
        const formData = $('#filterForm').serialize();
        window.location.href = '{{ route("admin.jadwalMengajar.index") }}?' + formData;
    });
});
</script>
@endpush