@extends('layouts.main')

@section('title', 'Edit Siswa')

@section('content')
<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                @include('partials.alert')
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4>Edit Siswa {{ $siswa->nama }}</h4>
                        <a href="{{ route('siswa.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" 
                              action="{{ route('siswa.update', $siswa->id) }}" 
                              enctype="multipart/form-data"
                              id="form-edit-siswa">
                            @csrf
                            @method('PUT')
                            
                            <!-- Preview Foto Siswa Saat Ini -->
                            <div class="form-group">
                                <label>Foto Saat Ini</label>
                                @if($siswa->foto && Storage::disk('public')->exists($siswa->foto))
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $siswa->foto) }}" alt="Foto {{ $siswa->nama }}" 
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
                                <label for="foto">Ganti Foto Siswa <span class="text-muted">(Opsional)</span></label>
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

                            <!-- Nama Siswa -->
                            <div class="form-group">
                                <label for="nama">Nama Siswa <span class="text-danger">*</span></label>
                                <input type="text" 
                                       id="nama" 
                                       name="nama" 
                                       class="form-control @error('nama') is-invalid @enderror" 
                                       placeholder="Masukkan nama siswa" 
                                       value="{{ old('nama', $siswa->nama) }}"
                                       required>
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- NIS dan No. Telp -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nis">NIS <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               id="nis" 
                                               name="nis" 
                                               class="form-control @error('nis') is-invalid @enderror" 
                                               placeholder="Masukkan NIS siswa" 
                                               value="{{ old('nis', $siswa->nis) }}"
                                               required>
                                        @error('nis')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="telp">No. Telepon <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               id="telp" 
                                               name="telp" 
                                               class="form-control @error('telp') is-invalid @enderror" 
                                               placeholder="Masukkan nomor telepon" 
                                               value="{{ old('telp', $siswa->telp) }}"
                                               required>
                                        @error('telp')
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
                                          placeholder="Masukkan alamat siswa" 
                                          rows="3"
                                          required>{{ old('alamat', $siswa->alamat) }}</textarea>
                                @error('alamat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Kelas -->
                            <div class="form-group">
                                <label for="kelas_id">Kelas <span class="text-danger">*</span></label>
                                <select id="kelas_id" 
                                        name="kelas_id" 
                                        class="form-control select2 @error('kelas_id') is-invalid @enderror"
                                        required>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach ($kelas as $data)
                                        <option value="{{ $data->id }}" 
                                                {{ old('kelas_id', $siswa->kelas_id) == $data->id ? 'selected' : '' }}>
                                            {{ $data->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kelas_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-success" id="submit-btn">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                                <a href="{{ route('siswa.index') }}" class="btn btn-secondary ml-2">
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
            // Validasi ukuran file (2MB = 2048KB)
            if (file.size > 2048 * 1024) {
                alert('Ukuran file terlalu besar. Maksimal 2MB.');
                $(this).val('');
                $(this).next('.custom-file-label').html('Pilih file foto baru');
                $('#preview-container').hide();
                return;
            }
            
            // Validasi tipe file
            var allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Format file tidak didukung. Gunakan JPEG, PNG, JPG, atau GIF.');
                $(this).val('');
                $(this).next('.custom-file-label').html('Pilih file foto baru');
                $('#preview-container').hide();
                return;
            }
            
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
    $('form').on('submit', function(e) {
        // Disable double submit
        if ($(this).data('submitted')) {
            e.preventDefault();
            return false;
        }
        
        $(this).data('submitted', true);
        $('#submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        
        // Re-enable form setelah 10 detik untuk fallback
        setTimeout(function() {
            $('form').data('submitted', false);
            $('#submit-btn').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Perubahan');
        }, 10000);
    });

    // Validasi form sebelum submit
    $('form').on('submit', function(e) {
        var isValid = true;
        
        // Validasi field required
        $(this).find('input[required], select[required], textarea[required]').each(function() {
            if (!$(this).val() || !$(this).val().toString().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            $(this).data('submitted', false);
            $('#submit-btn').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Perubahan');
            
            // Focus ke field pertama yang error
            $(this).find('.is-invalid').first().focus();
            
            // Show alert
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Mohon lengkapi semua field yang wajib diisi',
                confirmButtonText: 'OK'
            });
            
            return false;
        }
        
        // Konfirmasi sebelum submit
        e.preventDefault();
        var form = this;
        
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menyimpan perubahan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form
                form.submit();
            } else {
                // Reset form state
                $(form).data('submitted', false);
                $('#submit-btn').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Perubahan');
            }
        });
    });

    // Remove invalid class when user types
    $('input, select, textarea').on('input change', function() {
        if ($(this).val() && $(this).val().toString().trim()) {
            $(this).removeClass('is-invalid');
        }
    });

    // Handle form reset jika ada error
    @if($errors->any())
        $('#submit-btn').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Perubahan');
        $('form').data('submitted', false);
    @endif
});
</script>
@endpush
@endsection