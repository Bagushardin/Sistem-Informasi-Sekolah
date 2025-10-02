@extends('layouts.main')

@section('title', 'Edit Jadwal Mengajar')

@section('content')
<div class="section-body">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Jadwal Mengajar</h4>
                    <a href="{{ route('admin.jadwal.index') }}" class="btn btn-primary ml-auto">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.jadwal.update', $jadwal->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="guru_id">Guru</label>
                            <select name="guru_id" id="guru_id" class="form-control @error('guru_id') is-invalid @enderror" required>
                                <option value="">Pilih Guru</option>
                                @foreach($guru as $g)
                                    <option value="{{ $g->id }}" {{ $jadwal->guru_id == $g->id ? 'selected' : '' }}>
                                        {{ $g->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('guru_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="kelas_id">Kelas</label>
                            <select name="kelas_id" id="kelas_id" class="form-control @error('kelas_id') is-invalid @enderror" required>
                                <option value="">Pilih Kelas</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}" {{ $jadwal->kelas_id == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="mapel_id">Mata Pelajaran</label>
                            <select name="mapel_id" id="mapel_id" class="form-control @error('mapel_id') is-invalid @enderror" required>
                                <option value="">Pilih Mata Pelajaran</option>
                                @foreach($mapel as $m)
                                    <option value="{{ $m->id }}" {{ $jadwal->mapel_id == $m->id ? 'selected' : '' }}>
                                        {{ $m->nama_mapel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('mapel_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="hari">Hari</label>
                            <select name="hari" id="hari" class="form-control @error('hari') is-invalid @enderror" required>
                                <option value="">Pilih Hari</option>
                                @foreach($hari as $h)
                                    <option value="{{ $h }}" {{ $jadwal->hari == $h ? 'selected' : '' }}>
                                        {{ ucfirst($h) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('hari')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jam_mulai">Jam Mulai</label>
                                    <input type="time" name="jam_mulai" id="jam_mulai" 
                                           class="form-control @error('jam_mulai') is-invalid @enderror" 
                                           value="{{ $jadwal->jam_mulai->format('H:i') }}" required>
                                    @error('jam_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jam_selesai">Jam Selesai</label>
                                    <input type="time" name="jam_selesai" id="jam_selesai" 
                                           class="form-control @error('jam_selesai') is-invalid @enderror" 
                                           value="{{ $jadwal->jam_selesai->format('H:i') }}" required>
                                    @error('jam_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Update</button>
                            <a href="{{ route('admin.jadwal.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection