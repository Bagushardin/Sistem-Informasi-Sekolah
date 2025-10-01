@extends('layouts.main')

@section('title', 'Profil Guru')

@section('content')
<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                @include('partials.alert')
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4>Profil Guru - {{ $guru->nama }}</h4>
                        <div>
                            <!--<a href="{{ route('admin.guru.edit', Crypt::encrypt($guru->id)) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>-->
                            <a href="{{ route('admin.guru.index') }}" class="btn btn-primary ml-2">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Foto Guru -->
                            <div class="col-md-4 text-center">
                                <div class="card">
                                    <div class="card-body">
                                        @if($guru->foto)
                                            @php
                                                $fotoUrl = Storage::url($guru->foto);
                                                $fotoExists = Storage::disk('public')->exists($guru->foto);
                                            @endphp
                                            
                                            @if($fotoExists)
                                                <img src="{{ $fotoUrl }}" 
                                                     class="img-fluid rounded" 
                                                     style="max-width: 250px; height: 250px; object-fit: cover;" 
                                                     alt="Foto {{ $guru->nama }}"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div style="display: none; width: 250px; height: 250px; background-color: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px; align-items: center; justify-content: center; margin: 0 auto;">
                                                    <div class="text-center">
                                                        <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                                        <p class="text-muted">Foto tidak dapat dimuat</p>
                                                    </div>
                                                </div>
                                            @else
                                                <div style="width: 250px; height: 250px; background-color: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                    <div class="text-center">
                                                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                                        <p class="text-muted">File foto tidak ditemukan</p>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <!-- Debug info (remove in production) -->
                                           <!-- <small class="text-muted d-block mt-2">
                                                <strong>Debug Info:</strong><br>
                                                Path: {{ $guru->foto }}<br>
                                                URL: {{ $fotoUrl }}<br>
                                                File Exists: {{ $fotoExists ? 'Yes' : 'No' }}<br>
                                                Full Path: {{ storage_path('app/public/' . $guru->foto) }}
                                            </small>-->
                                        @else
                                            <div style="width: 250px; height: 250px; background-color: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                <div class="text-center">
                                                    <i class="fas fa-user fa-3x text-muted mb-2"></i>
                                                    <p class="text-muted">Tidak ada foto</p>
                                                </div>
                                            </div>
                                        @endif
                                        <h5 class="mt-3 mb-1">{{ $guru->nama }}</h5>
                                        <p class="text-muted">
                                            @if($guru->mapel)
                                                {{ $guru->mapel->nama_mapel }}
                                            @else
                                                Mata Pelajaran tidak tersedia
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Detail Guru -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Informasi Detail</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <td class="font-weight-bold" style="width: 30%;">
                                                            <i class="fas fa-user"></i> Nama Lengkap
                                                        </td>
                                                        <td>{{ $guru->nama }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">
                                                            <i class="fas fa-id-card"></i> NIP
                                                        </td>
                                                        <td>{{ $guru->nip }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">
                                                            <i class="fas fa-phone"></i> No. Telepon
                                                        </td>
                                                        <td>
                                                            <a href="tel:{{ $guru->no_telp }}" class="text-decoration-none">
                                                                {{ $guru->no_telp }}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">
                                                            <i class="fas fa-map-marker-alt"></i> Alamat
                                                        </td>
                                                        <td>{{ $guru->alamat }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">
                                                            <i class="fas fa-book"></i> Mata Pelajaran
                                                        </td>
                                                        <td>
                                                            @if($guru->mapel)
                                                                <span class="badge badge-primary">{{ $guru->mapel->nama_mapel }}</span>
                                                            @else
                                                                <span class="badge badge-secondary">Mata Pelajaran tidak tersedia</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">
                                                            <i class="fas fa-calendar-plus"></i> Tanggal Dibuat
                                                        </td>
                                                        <td>{{ $guru->created_at ? $guru->created_at->format('d F Y - H:i') : 'Tidak tersedia' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">
                                                            <i class="fas fa-calendar-edit"></i> Terakhir Diperbarui
                                                        </td>
                                                        <td>{{ $guru->updated_at ? $guru->updated_at->format('d F Y - H:i') : 'Tidak tersedia' }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                               <!-- <div class="card">
                                    <div class="card-body">
                                        <h6>Aksi</h6>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('guru.edit', Crypt::encrypt($guru->id)) }}" 
                                               class="btn btn-warning">
                                                <i class="fas fa-edit"></i> Edit Data
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-danger"
                                                    onclick="confirmDelete('{{ Crypt::encrypt($guru->id) }}', '{{ $guru->nama }}')">
                                                <i class="fas fa-trash"></i> Hapus Data
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>-->
</section>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data guru <strong id="guruName"></strong>?</p>
                <p class="text-danger"><small>Aksi ini tidak dapat dibatalkan!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete(encryptedId, namaGuru) {
        // Set nama guru di modal
        document.getElementById('guruName').textContent = namaGuru;
        
        // Set action form
        document.getElementById('deleteForm').action = '{{ route("guru.destroy", "") }}/' + encryptedId;
        
        // Tampilkan modal
        $('#deleteModal').modal('show');
    }

    // Auto refresh foto jika ada perubahan (untuk kasus redirect kembali setelah edit)
    document.addEventListener('DOMContentLoaded', function() {
        // Force reload foto dengan timestamp untuk mencegah cache
        var foto = document.querySelector('img[alt*="Foto"]');
        if (foto) {
            var originalSrc = foto.src;
            foto.src = originalSrc + '?t=' + new Date().getTime();
        }
    });
</script>
@endpush
@endsection