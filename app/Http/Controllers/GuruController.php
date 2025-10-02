<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class GuruController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $mapel = Mapel::orderBy('nama_mapel', 'asc')->get();
        $guru = Guru::with('mapel')->orderBy('nama', 'asc')->get();
        return response()->view('pages.admin.guru.index', compact('guru', 'mapel'));
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
            'nip' => 'required|string|unique:gurus,nip|max:20',
            'no_telp' => 'required|string|max:15',
            'alamat' => 'required|string',
            'mapel_id' => 'required|exists:mapels,id',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'nip.unique' => 'NIP sudah terdaftar',
            'mapel_id.exists' => 'Mata pelajaran tidak valid',
            'foto.image' => 'File harus berupa gambar',
            'foto.max' => 'Ukuran foto maksimal 2MB'
        ]);

        $foto = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $namaFoto = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $foto = $file->storeAs('images/guru', $namaFoto, 'public');
        }

        $guru = new Guru();
        $guru->nama = $request->nama;
        $guru->nip = $request->nip;
        $guru->no_telp = $request->no_telp;
        $guru->alamat = $request->alamat;
        $guru->mapel_id = $request->mapel_id;
        $guru->foto = $foto;
        $guru->save();

        return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil ditambahkan');
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
            $guru = Guru::with('mapel')->findOrFail($id);
            return view('pages.admin.guru.profile', compact('guru'));
        } catch (\Exception $e) {
            return redirect()->route('guru.index')->with('error', 'Data guru tidak ditemukan');
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
            $mapel = Mapel::orderBy('nama_mapel', 'asc')->get();
            $guru = Guru::with('mapel')->findOrFail($id);
            return view('pages.admin.guru.edit', compact('guru', 'mapel'));
        } catch (\Exception $e) {
            return redirect()->route('guru.index')->with('error', 'Data guru tidak ditemukan');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $encryptedId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $guru = Guru::findOrFail($id);
            
            // Validasi dengan pengecualian untuk NIP guru yang sedang diedit
            $this->validate($request, [
                'nama' => 'required|string|max:255',
                'nip' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('gurus', 'nip')->ignore($guru->id)
                ],
                'no_telp' => 'required|string|max:15',
                'alamat' => 'required|string',
                'mapel_id' => 'required|exists:mapels,id',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'nip.unique' => 'NIP sudah terdaftar',
                'mapel_id.exists' => 'Mata pelajaran tidak valid',
                'foto.image' => 'File harus berupa gambar',
                'foto.max' => 'Ukuran foto maksimal 2MB'
            ]);

            // Update data guru
            $guru->nama = $request->input('nama');
            $guru->nip = $request->input('nip');
            $guru->no_telp = $request->input('no_telp');
            $guru->alamat = $request->input('alamat');
            $guru->mapel_id = $request->input('mapel_id');

            // Handle upload foto baru
            if ($request->hasFile('foto')) {
                // Hapus foto lama jika ada dan bukan default
                if ($guru->foto && Storage::disk('public')->exists($guru->foto)) {
                    Storage::disk('public')->delete($guru->foto);
                }

                // Upload foto baru
                $file = $request->file('foto');
                $namaFoto = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $fotoPath = $file->storeAs('images/guru', $namaFoto, 'public');
                
                // Simpan hanya nama file, bukan full path
                $guru->foto = $fotoPath;
            }

            $guru->save();

            // Debug log untuk cek apakah update berhasil
            Log::info('Guru updated', [
                'id' => $guru->id,
                'nama' => $guru->nama,
                'foto' => $guru->foto,
                'foto_exists' => $guru->foto ? Storage::disk('public')->exists($guru->foto) : false
            ]);

            return redirect()->route('guru.show', Crypt::encrypt($guru->id))
                           ->with('success', 'Data guru berhasil diperbarui');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                           ->withErrors($e->validator)
                           ->withInput();
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return redirect()->route('guru.index')
                           ->with('error', 'ID guru tidak valid');
        } catch (\Exception $e) {
            Log::error('Error updating guru: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Terjadi kesalahan saat memperbarui data guru: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $encryptedId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $guru = Guru::findOrFail($id);
            
            // Hapus foto jika ada
            if ($guru->foto && Storage::disk('public')->exists($guru->foto)) {
                Storage::disk('public')->delete($guru->foto);
            }
            
            // Hapus data user terkait jika ada
            if ($guru->user_id && $user = User::find($guru->user_id)) {
                $user->delete();
            }
            
            $guru->delete();

            return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil dihapus');
            
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return redirect()->route('admin.guru.index')->with('error', 'ID guru tidak valid');
        } catch (\Exception $e) {
            return redirect()->route('admin.guru.index')->with('error', 'Terjadi kesalahan saat menghapus data guru');
        }
    }
}