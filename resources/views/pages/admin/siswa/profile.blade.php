@extends('layouts.main')

@section('title', 'Profil Siswa')

@section('content')
<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                @include('partials.alert')
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4>Profil Siswa - {{ $siswa->nama }}</h4>
                        <div>
                            <!--<a href="{{ route('siswa.edit', $siswa->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>-->
                            <a href="{{ route('siswa.index') }}" class="btn btn-primary ml-2">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Foto Siswa -->
                    <div class="col-md-4 text-center">
                         <div class="card">
                            <div class="card-body text-center">
                             @if($siswa->foto && Storage::disk('public')->exists($siswa->foto))
                                <img src="{{ asset('storage/' . $siswa->foto) }}" alt="Foto {{ $siswa->nama }}" 
                                   class="img-fluid rounded" 
                                     style="max-width: 250px; height: 250px; object-fit: cover;"
                             @else
                              <img src="{{ asset('images/default-avatar.png') }}" alt="Default Avatar" 
                                  class="img-fluid rounded-circle mb-3" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                                @endif 
                    </div>

                        <!-- Debug info (hapus setelah masalah teratasi) -->
                        <!--@if(config('app.debug'))
                        <div class="alert alert-info mt-2">
                            <small>
                                 Debug Info:<br>
                                        Foto Path: {{ $siswa->foto ?? 'null' }}<br>
                                            File Exists: {{ $siswa->foto ? (Storage::disk('public')->exists($siswa->foto) ? 'Yes' : 'No') : 'N/A' }}<br>
                                             Full URL: {{ $siswa->foto ? asset('storage/' . $siswa->foto) : 'N/A' }}
                                            </small>
                                        </div>
                                        @endif-->
                                        <h5 class="mt-3 mb-1">{{ $siswa->nama }}</h5>
                                        <p class="text-muted">
                                            @if($siswa->kelas)
                                                {{ $siswa->kelas->nama_kelas }}
                                            @else
                                                Kelas tidak tersedia
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Detail Siswa -->
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
                                                        <td>{{ $siswa->nama }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">
                                                            <i class="fas fa-id-card"></i> NIS
                                                        </td>
                                                        <td>{{ $siswa->nis }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">
                                                            <i class="fas fa-phone"></i> No. Telepon
                                                        </td>
                                                        <td>
                                                            <a href="tel:{{ $siswa->telp }}" class="text-decoration-none">
                                                                {{ $siswa->telp }}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">
                                                            <i class="fas fa-map-marker-alt"></i> Alamat
                                                        </td>
                                                        <td>{{ $siswa->alamat }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">
                                                            <i class="fas fa-school"></i> Kelas
                                                        </td>
                                                        <td>
                                                            @if($siswa->kelas)
                                                                <span class="badge badge-primary">{{ $siswa->kelas->nama_kelas }}</span>
                                                            @else
                                                                <span class="badge badge-secondary">Kelas tidak tersedia</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">
                                                            <i class="fas fa-calendar-plus"></i> Tanggal Dibuat
                                                        </td>
                                                        <td>{{ $siswa->created_at ? $siswa->created_at->format('d F Y - H:i') : 'Tidak tersedia' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold">
                                                            <i class="fas fa-calendar-edit"></i> Terakhir Diperbarui
                                                        </td>
                                                        <td>{{ $siswa->updated_at ? $siswa->updated_at->format('d F Y - H:i') : 'Tidak tersedia' }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <!--<div class="card">
                                    <div class="card-body">
                                        <h6>Aksi</h6>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('siswa.edit', $siswa->id) }}" 
                                               class="btn btn-warning">
                                                <i class="fas fa-edit"></i> Edit Data
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-danger"
                                                    onclick="confirmDelete('{{ $siswa->id }}', '{{ $siswa->nama }}')">
                                                <i class="fas fa-trash"></i> Hapus Data
                                            </button>
                                        </div>
                                    </div>
                                </div>-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                <p>Apakah Anda yakin ingin menghapus data siswa <strong id="siswaName"></strong>?</p>
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
    function confirmDelete(siswaId, namaSiswa) {
        // Set nama siswa di modal
        document.getElementById('siswaName').textContent = namaSiswa;
        
        // Set action form
        document.getElementById('deleteForm').action = '{{ route("siswa.destroy", "") }}/' + siswaId;
        
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