@extends('layouts.main')

@section('title', 'Tambah Jadwal Mengajar')

@section('content')
<div class="section-body">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Tambah Jadwal Mengajar</h4>
                    <a href="{{ route('admin.jadwalMengajar.index') }}" class="btn btn-primary ml-auto">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    {{-- Penambahan Keterangan Mapel Pada Guru --}}
                    <form action="{{ route('admin.jadwalMengajar.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="guru_id">Guru</label>
                            <select name="guru_id" id="guru_id" class="form-control @error('guru_id') is-invalid @enderror" required>
                                <option value="">Pilih Guru</option>
                                @foreach($guru as $g)
                                    <option value="{{ $g->id }}" {{ old('guru_id') == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama }} ({{ $g->mapel->nama_mapel ?? 'Belum ada mapel' }})
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
                                    <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
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
                                    <option value="{{ $m->id }}" {{ old('mapel_id') == $m->id ? 'selected' : '' }}>
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
                                    <option value="{{ $h }}" {{ old('hari') == $h ? 'selected' : '' }}>
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
                                           value="{{ old('jam_mulai') }}" required>
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
                                           value="{{ old('jam_selesai') }}" required>
                                    @error('jam_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Simpan</button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        width: '100%',
        placeholder: "Pilih opsi...",
        allowClear: true
    });

    // Validasi real-time antara guru dan mapel
    $('#guru_id, #mapel_id').change(function() {
        const guruId = $('#guru_id').val();
        const mapelId = $('#mapel_id').val();
        const selectedGuru = $('#guru_id option:selected');
        const guruMapelId = selectedGuru.data('mapel');
        
        if (guruId && mapelId && guruMapelId && guruMapelId != mapelId) {
            $('#mapel-warning').show();
        } else {
            $('#mapel-warning').hide();
        }
    });

    // Validasi form sebelum submit
    $('#jadwalForm').submit(function(e) {
        const jamMulai = $('#jam_mulai').val();
        const jamSelesai = $('#jam_selesai').val();
        
        if (jamMulai && jamSelesai && jamMulai >= jamSelesai) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Jam selesai harus setelah jam mulai!'
            });
            return false;
        }

        // Tampilkan loading
        $('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
    });
});
</script>
@endpush
@endsection