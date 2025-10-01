@extends('layouts.main')
@section('title', 'Edit Jadwal Mengajar')

@section('content')
<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit Jadwal Mengajar</h4>
                        <a href="{{ route('admin.jadwalmengajar.index') }}" class="btn btn-primary ml-auto">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body">
                        @include('partials.alert')
                        
                        <form action="#" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="guru_id">Guru <span class="text-danger">*</span></label>
                                        <select id="guru_id" name="guru_id" class="form-control select2" required>
                                            <option value="">-- Pilih Guru --</option>
                                            @foreach ($guru as $data)
                                            <option value="{{ $data->id }}" 
                                                {{ $jadwal->guru_id == $data->id ? 'selected' : '' }}>
                                                {{ $data->user->name }} 
                                                @if($data->mapel)
                                                    - {{ $data->mapel->nama_mapel }}
                                                @endif
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kelas_id">Kelas <span class="text-danger">*</span></label>
                                        <select id="kelas_id" name="kelas_id" class="form-control select2" required>
                                            <option value="">-- Pilih Kelas --</option>
                                            @foreach ($kelas as $data)
                                            <option value="{{ $data->id }}" 
                                                {{ $jadwal->kelas_id == $data->id ? 'selected' : '' }}>
                                                {{ $data->nama_kelas }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mapel_id">Mata Pelajaran <span class="text-danger">*</span></label>
                                        <select id="mapel_id" name="mapel_id" class="form-control select2" required>
                                            <option value="">-- Pilih Mata Pelajaran --</option>
                                            @foreach ($mapel as $data)
                                            <option value="{{ $data->id }}" 
                                                {{ $jadwal->mapel_id == $data->id ? 'selected' : '' }}>
                                                {{ $data->nama_mapel }}
                                                @if($data->jurusan)
                                                    ({{ $data->jurusan->nama_jurusan }})
                                                @endif
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="hari">Hari <span class="text-danger">*</span></label>
                                        <select id="hari" name="hari" class="form-control" required>
                                            <option value="">-- Pilih Hari --</option>
                                            <option value="senin" {{ $jadwal->hari == 'senin' ? 'selected' : '' }}>Senin</option>
                                            <option value="selasa" {{ $jadwal->hari == 'selasa' ? 'selected' : '' }}>Selasa</option>
                                            <option value="rabu" {{ $jadwal->hari == 'rabu' ? 'selected' : '' }}>Rabu</option>
                                            <option value="kamis" {{ $jadwal->hari == 'kamis' ? 'selected' : '' }}>Kamis</option>
                                            <option value="jumat" {{ $jadwal->hari == 'jumat' ? 'selected' : '' }}>Jumat</option>
                                            <option value="sabtu" {{ $jadwal->hari == 'sabtu' ? 'selected' : '' }}>Sabtu</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="jam_mulai" id="jam_mulai" 
                                               value="{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i:s') }}" 
                                               step="1" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="jam_selesai">Jam Selesai <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="jam_selesai" id="jam_selesai" 
                                               value="{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i:s') }}" 
                                               step="1" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Jadwal
                                </button>
                                <a href="{{ route('admin.jadwal.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('script')
<script>
$(document).ready(function() {
    $('.select2').select2({
        width: '100%'
    });

    // Validasi jam
    $('form').submit(function(e) {
        const jamMulai = $('#jam_mulai').val();
        const jamSelesai = $('#jam_selesai').val();
        
        if (jamMulai && jamSelesai && jamMulai >= jamSelesai) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Jam selesai harus setelah jam mulai!'
            });
        }
    });
});
</script>
@endpush