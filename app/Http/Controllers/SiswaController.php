<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $siswa = Siswa::with('kelas')->orderBy('nama', 'asc')->get();
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        return view('pages.admin.siswa.index', compact('siswa', 'kelas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'nama' => 'required|string|max:255',
            'nis' => 'required|string|unique:siswas,nis|max:20',
            'telp' => 'required|string|max:15',
            'alamat' => 'required|string',
            'kelas_id' => 'required|exists:kelas,id',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'nis.unique' => 'NIS sudah terdaftar',
            'kelas_id.exists' => 'Kelas tidak valid',
            'foto.image' => 'File harus berupa gambar',
            'foto.max' => 'Ukuran foto maksimal 2MB'
        ]);

        $foto = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $namaFoto = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $foto = $file->storeAs('images/siswa', $namaFoto, 'public');
        }

        $siswa = new Siswa();
        $siswa->nama = $request->nama;
        $siswa->nis = $request->nis;
        $siswa->telp = $request->telp;
        $siswa->alamat = $request->alamat;
        $siswa->kelas_id = $request->kelas_id;
        $siswa->foto = $foto;
        $siswa->save();

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $encryptedId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $siswa = Siswa::with('kelas')->findOrFail($id);
            return view('pages.admin.siswa.profile', compact('siswa'));
        } catch (\Exception $e) {
            return redirect()->route('siswa.index')->with('error', 'Data siswa tidak ditemukan');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $encryptedId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
            $siswa = Siswa::with('kelas')->findOrFail($id);
            return view('pages.admin.siswa.edit', compact('siswa', 'kelas'));
        } catch (\Exception $e) {
            return redirect()->route('siswa.index')->with('error', 'Data siswa tidak ditemukan');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $siswa = Siswa::findOrFail($id);
            
            // Debug log untuk cek data yang masuk
            Log::info('Update siswa request', [
                'siswa_id' => $id,
                'request_data' => $request->except(['foto']),
                'has_file' => $request->hasFile('foto'),
                'file_info' => $request->hasFile('foto') ? [
                    'name' => $request->file('foto')->getClientOriginalName(),
                    'size' => $request->file('foto')->getSize(),
                    'mime' => $request->file('foto')->getMimeType()
                ] : null
            ]);
            
            // Validasi dengan pengecualian untuk NIS siswa yang sedang diedit
            $this->validate($request, [
                'nama' => 'required|string|max:255',
                'nis' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('siswas', 'nis')->ignore($siswa->id)
                ],
                'telp' => 'required|string|max:15',
                'alamat' => 'required|string',
                'kelas_id' => 'required|exists:kelas,id',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'nama.required' => 'Nama siswa harus diisi',
                'nis.required' => 'NIS harus diisi',
                'nis.unique' => 'NIS sudah terdaftar',
                'telp.required' => 'No. Telepon harus diisi',
                'alamat.required' => 'Alamat harus diisi',
                'kelas_id.required' => 'Kelas harus dipilih',
                'kelas_id.exists' => 'Kelas tidak valid',
                'foto.image' => 'File harus berupa gambar',
                'foto.mimes' => 'Format foto harus jpeg, png, jpg, atau gif',
                'foto.max' => 'Ukuran foto maksimal 2MB'
            ]);

            // Update data siswa
            $siswa->fill([
                'nama' => $request->input('nama'),
                'nis' => $request->input('nis'),
                'telp' => $request->input('telp'),
                'alamat' => $request->input('alamat'),
                'kelas_id' => $request->input('kelas_id')
            ]);

            // Handle upload foto baru jika ada
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                Log::info('Processing foto upload');
                
                // Hapus foto lama jika ada
                if ($siswa->foto && Storage::disk('public')->exists($siswa->foto)) {
                    Storage::disk('public')->delete($siswa->foto);
                    Log::info('Deleted old foto: ' . $siswa->foto);
                }

                // Upload foto baru
                $file = $request->file('foto');
                $namaFoto = 'siswa_' . $siswa->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Pastikan direktori ada
                if (!Storage::disk('public')->exists('images/siswa')) {
                    Storage::disk('public')->makeDirectory('images/siswa');
                }
                
                $fotoPath = $file->storeAs('images/siswa', $namaFoto, 'public');
                
                if ($fotoPath) {
                    $siswa->foto = $fotoPath;
                    Log::info('Foto uploaded successfully: ' . $fotoPath);
                } else {
                    Log::error('Failed to upload foto');
                    throw new \Exception('Gagal mengupload foto');
                }
            }

            // Simpan perubahan
            $saved = $siswa->save();

            if (!$saved) {
                throw new \Exception('Gagal menyimpan perubahan data siswa');
            }

            // Debug log untuk memastikan data tersimpan
            Log::info('Siswa berhasil diupdate', [
                'id' => $siswa->id,
                'nama' => $siswa->nama,
                'nis' => $siswa->nis,
                'telp' => $siswa->telp,
                'alamat' => $siswa->alamat,
                'kelas_id' => $siswa->kelas_id,
                'foto' => $siswa->foto,
                'foto_exists' => $siswa->foto ? Storage::disk('public')->exists($siswa->foto) : false
            ]);

            // Redirect ke halaman index dengan pesan sukses
            return redirect()->route('siswa.index')
                           ->with('success', 'Data siswa berhasil diperbarui');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error updating siswa', [
                'errors' => $e->errors(),
                'siswa_id' => $id
            ]);
            
            return redirect()->back()
                           ->withErrors($e->validator)
                           ->withInput()
                           ->with('error', 'Terjadi kesalahan validasi. Silakan periksa kembali data yang diinputkan.');
        } catch (\Exception $e) {
            Log::error('Error updating siswa: ' . $e->getMessage(), [
                'request_data' => $request->except(['foto']),
                'siswa_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                           ->with('error', 'Terjadi kesalahan saat memperbarui data siswa: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $siswa = Siswa::findOrFail($id);
            
            // Hapus foto jika ada
            if ($siswa->foto && Storage::disk('public')->exists($siswa->foto)) {
                Storage::disk('public')->delete($siswa->foto);
            }
            
            $siswa->delete();

            return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil dihapus');
            
        } catch (\Exception $e) {
            Log::error('Error deleting siswa: ' . $e->getMessage());
            return redirect()->route('siswa.index')->with('error', 'Terjadi kesalahan saat menghapus data siswa');
        }
    }
}