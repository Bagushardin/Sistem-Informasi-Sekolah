@extends('layouts.main')

@section('title', 'Edit Guru')

@section('content')
<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                @include('partials.alert')
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4>Edit Guru {{ $guru->nama }}</h4>
                        <a href="{{ route('guru.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('guru.update', Crypt::encrypt($guru->id)) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <!-- Preview Foto guru Saat Ini -->
                            <div class="form-group">
                                <label>Foto Saat Ini</label>
                                @if($guru->foto && Storage::disk('public')->exists($guru->foto))
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $guru->foto) }}" alt="Foto {{ $guru->nama }}" 
                                             class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                    </div>
                                @else
                                    <div class="mb-2">
                                        <img src="{{ asset('images/default-avatar.png') }}" alt="Default Avatar" 
                                             class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                    </div>
                                @endif
                            </div>

                            <!-- Upload Foto Baru -->
                            <div class="form-group">
                                <label for="foto">Ganti Foto Guru <span class="text-muted">(Opsional)</span></label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" 
                                               name="foto" 
                                               class="custom-file-input @error('foto') is-invalid @enderror" 
                                               id="foto"
                                               accept="image/jpeg,image/png,image/jpg,image/gif">
                                        <label class="custom-file-label" for="foto">Pilih file foto baru</label>
                                    </div>
                                </div>
                                @error('foto')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Format: JPEG, PNG, JPG, GIF. Maksimal 2MB.</small>
                                
                                <!-- Preview foto baru -->
                                <div class="mt-2" id="preview-container" style="display: none;">
                                    <label class="text-muted">Preview Foto Baru:</label><br>
                                    <img id="preview-image" class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                </div>
                            </div>

                            <!-- Nama guru -->
                            <div class="form-group">
                                <label for="nama">Nama Guru <span class="text-danger">*</span></label>
                                <input type="text" 
                                       id="nama" 
                                       name="nama" 
                                       class="form-control @error('nama') is-invalid @enderror" 
                                       placeholder="Masukkan nama guru" 
                                       value="{{ old('nama', $guru->nama) }}"
                                       required>
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- NIP dan No. Telp -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nip">NIP <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               id="nip" 
                                               name="nip" 
                                               class="form-control @error('nip') is-invalid @enderror" 
                                               placeholder="Masukkan NIP guru" 
                                               value="{{ old('nip', $guru->nip) }}"
                                               required>
                                        @error('nip')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="no_telp">No. Telepon <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               id="no_telp" 
                                               name="no_telp" 
                                               class="form-control @error('no_telp') is-invalid @enderror" 
                                               placeholder="Masukkan nomor telepon" 
                                               value="{{ old('no_telp', $guru->no_telp) }}"
                                               required>
                                        @error('no_telp')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Alamat -->
                            <div class="form-group">
                                <label for="alamat">Alamat <span class="text-danger">*</span></label>
                                <textarea id="alamat" 
                                          name="alamat" 
                                          class="form-control @error('alamat') is-invalid @enderror" 
                                          placeholder="Masukkan alamat guru" 
                                          rows="3"
                                          required>{{ old('alamat', $guru->alamat) }}</textarea>
                                @error('alamat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Mata Pelajaran -->
                            <div class="form-group">
                                <label for="mapel_id">Mata Pelajaran <span class="text-danger">*</span></label>
                                <select id="mapel_id" 
                                        name="mapel_id" 
                                        class="form-control select2 @error('mapel_id') is-invalid @enderror"
                                        required>
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    @foreach ($mapel as $data)
                                        <option value="{{ $data->id }}" 
                                                {{ old('mapel_id', $guru->mapel_id) == $data->id ? 'selected' : '' }}>
                                            {{ $data->nama_mapel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('mapel_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-success" id="submit-btn">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                                <a href="{{ route('guru.index') }}" class="btn btn-secondary ml-2">
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

@push('scripts')
<script>
$(document).ready(function() {
    // Custom file input label update
    $('#foto').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Pilih file foto baru');
        
        // Preview foto baru
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-image').attr('src', e.target.result);
                $('#preview-container').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#preview-container').hide();
        }
    });

    // Form submission dengan loading state
    $('form').on('submit', function() {
        $('#submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
    });

    // Validasi form sebelum submit
    $('form').on('submit', function(e) {
        var isValid = true;
        
        // Validasi field required
        $(this).find('input[required], select[required], textarea[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            $('#submit-btn').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Perubahan');
            alert('Mohon lengkapi semua field yang wajib diisi');
        }
    });

    // Remove invalid class when user types
    $('input, select, textarea').on('input change', function() {
        if ($(this).val().trim()) {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
@endpush
@endsection