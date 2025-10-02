<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Models\Guru;
use App\Models\Orangtua;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Exception;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::orderBy('roles', 'asc')->get(); // Changed from 'roles' to 'role'
        $siswaList = Siswa::with('user')->orderBy('id', 'asc')->get();
        
        return view('pages.admin.user.index', compact('user', 'siswaList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base validation rules
        $rules = [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'roles' => 'required|in:admin,guru,siswa,orangtua' // Changed from 'roles' to 'role'
        ];

        // Add role-specific validation
        if ($request->role == 'guru') { // Changed from 'roles' to 'role'
            $rules['nip'] = 'required|exists:gurus,nip';
        } elseif ($request->role == 'siswa') {
            $rules['nis'] = 'required|exists:siswas,nis';
        } elseif ($request->role == 'orangtua') {
            $rules['name'] = 'required|string|max:255';
            $rules['alamat'] = 'required|string|max:500';
            $rules['no_telp'] = 'required|string|max:20';
            $rules['siswa'] = 'nullable|array';
            $rules['siswa.*'] = 'exists:siswas,id';
        } elseif ($request->role == 'admin') {
            $rules['name'] = 'required|string|max:255';
        }

        $this->validate($request, $rules, [
            'email.unique' => 'Email sudah terdaftar',
            'password.min' => 'Password minimal 6 karakter',
            'nip.exists' => 'NIP tidak terdaftar sebagai guru',
            'nis.exists' => 'NIS tidak terdaftar sebagai siswa',
        ]);

        DB::beginTransaction();

        try {
            switch ($request->roles) { // Changed from 'roles' to 'role'
                case 'guru':
                    $this->createGuruUser($request);
                    break;
                case 'siswa':
                    $this->createSiswaUser($request);
                    break;
                case 'orangtua':
                    $this->createOrangtuaUser($request);
                    break;
                default:
                    $this->createAdminUser($request);
                    break;
            }

            DB::commit();
            return redirect()->route('user.index')->with('success', 'Data user berhasil ditambahkan');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Create user for guru role
     */
    // perbaikan pada menu user
    private function createGuruUser(Request $request)
    {
        $guru = Guru::where('nip', $request->nip)->first();
        
        if (!$guru) {
            throw new Exception('NIP tidak terdaftar sebagai guru');
        }

        // Check if guru already has user account
        if ($guru->user_id) {
            throw new Exception('Guru dengan NIP ini sudah memiliki akun user');
        }

        $user = User::create([
            'name' => $guru->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'guru', // Changed from 'roles' to 'role'
            'nip' => $request->nip
        ]);

        $guru->user_id = $user->id;
        $guru->save();
    }

    /**
     * Create user for siswa role
     */
    private function createSiswaUser(Request $request)
    {
        $siswa = Siswa::where('nis', $request->nis)->first();
        
        if (!$siswa) {
            throw new Exception('NIS tidak terdaftar sebagai siswa');
        }

        // Check if siswa already has user account
        if ($siswa->user_id) {
            throw new Exception('Siswa dengan NIS ini sudah memiliki akun user');
        }

        $user = User::create([
            'name' => $siswa->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'roles' => 'siswa', // Changed from 'roles' to 'role'
            'nis' => $request->nis
        ]);

        $siswa->user_id = $user->id;
        $siswa->save();
    }

    /**
     * Create user for orangtua role
     */
    private function createOrangtuaUser(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'roles' => 'orangtua' // Changed from 'roles' to 'role'
        ]);

        $orangtua = Orangtua::create([
            'user_id' => $user->id,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
        ]);

        if ($request->has('siswa') && is_array($request->siswa)) {
            $siswaIds = array_filter(array_map('intval', $request->siswa));
            if (!empty($siswaIds)) {
                $orangtua->siswas()->sync($siswaIds);
            }
        }
    }

    /**
     * Create admin user
     */
    private function createAdminUser(Request $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'roles' => $request->role // Changed from 'roles' to 'role'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        $user = Auth::user();
        $data = ['user' => $user];

        switch ($user->role) { // Changed from 'roles' to 'role'
            case 'guru':
                $data['guru'] = Guru::where('user_id', $user->id)->first();
                break;
            case 'siswa':
                $data['siswa'] = Siswa::where('user_id', $user->id)->first();
                break;
            case 'orangtua':
                $data['orangtua'] = Orangtua::where('user_id', $user->id)->first();
                break;
            default:
                $data['admin'] = $user;
                break;
        }

        return view('pages.profile', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        // Base validation
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id)
            ]
        ];

        // Add role-specific validation
        if ($user->role == 'guru') { // Changed from 'roles' to 'role'
            $rules['nama'] = 'required|string|max:255';
            $rules['nip'] = 'required|string|max:20';
            $rules['alamat'] = 'required|string|max:500';
            $rules['no_telp'] = 'required|string|max:20';
        } elseif ($user->role == 'siswa') {
            $rules['nama'] = 'required|string|max:255';
            $rules['nis'] = 'required|string|max:20';
            $rules['alamat'] = 'required|string|max:500';
            $rules['telp'] = 'required|string|max:20';
        } elseif ($user->role == 'orangtua') {
            $rules['alamat'] = 'required|string|max:500';
            $rules['no_telp'] = 'required|string|max:20';
            $rules['siswas'] = 'nullable|array';
            $rules['siswas.*'] = 'exists:siswas,id';
        }

        $this->validate($request, $rules);

        DB::beginTransaction();
        
        try {
            $data = $request->only(['name', 'email']);

            switch ($user->role) { // Changed from 'roles' to 'role'
                case 'guru':
                    $this->updateGuruProfile($request);
                    break;
                case 'siswa':
                    $this->updateSiswaProfile($request);
                    break;
                case 'orangtua':
                    $this->updateOrangtuaProfile($request);
                    break;
            }

            // Update user table - actually save the user data
            $userModel = User::findOrFail($user->id);
            $userModel->name = $data['name'];
            $userModel->email = $data['email'];
            $userModel->save();

            DB::commit();
            return redirect()->route('profile')->with('success', 'Data berhasil diubah');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Update guru profile
     */
    private function updateGuruProfile(Request $request)
    {
        $guru = Guru::where('user_id', Auth::id())->first();
        if ($guru) {
            $guru->fill([
                'nama' => $request->nama,
                'nip' => $request->nip,
                'alamat' => $request->alamat,
                'no_telp' => $request->no_telp
            ]);
            $guru->save();
        }
    }

    /**
     * Update siswa profile
     */
    private function updateSiswaProfile(Request $request)
    {
        $siswa = Siswa::where('user_id', Auth::id())->first();
        if ($siswa) {
            $siswa->fill([
                'nama' => $request->nama,
                'nis' => $request->nis,
                'alamat' => $request->alamat,
                'telp' => $request->telp
            ]);
            $siswa->save();
        }
    }

    /**
     * Update orangtua profile
     */
    private function updateOrangtuaProfile(Request $request)
    {
        $orangtua = Orangtua::where('user_id', Auth::id())->first();
        if ($orangtua) {
            Orangtua::where('user_id', Auth::id())->update([
                'alamat' => $request->alamat,
                'no_telp' => $request->no_telp
            ]);

            if ($request->has('siswas') && is_array($request->siswas)) {
                $siswaIds = array_filter(array_map('intval', $request->siswas));
                $orangtua->siswas()->sync($siswaIds);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $user = User::findOrFail($id);
            
            // Remove related data based on role
            switch ($user->role) { // Changed from 'roles' to 'role'
                case 'guru':
                    if ($guru = Guru::where('user_id', $id)->first()) {
                        $guru->user_id = null;
                        $guru->save();
                    }
                    break;
                case 'siswa':
                    if ($siswa = Siswa::where('user_id', $id)->first()) {
                        $siswa->user_id = null;
                        $siswa->save();
                    }
                    break;
                case 'orangtua':
                    Orangtua::where('user_id', $id)->delete();
                    break;
            }
            
            $user->delete();
            
            DB::commit();
            return redirect()->route('user.index')->with('success', 'Data user berhasil dihapus');
            
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    /**
     * Show password change form
     */
    public function editPassword()
    {
        $user = Auth::user();
        $data = ['user' => $user];

        switch ($user->role) { 
            case 'guru':
                $data['guru'] = Guru::where('user_id', $user->id)->first();
                break;
            case 'siswa':
                $data['siswa'] = Siswa::where('user_id', $user->id)->first();
                break;
            default:
                $data['admin'] = $user;
                break;
        }

        return view('pages.ubah-password', $data);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        // Validation
        $this->validate($request, [
            'current-password' => 'required',
            'new-password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'new-password.min' => 'Password baru minimal 6 karakter',
            'new-password.confirmed' => 'Konfirmasi password tidak sesuai',
        ]);

        // Check current password
        if (!Hash::check($request->get('current-password'), Auth::user()->password)) {
            return redirect()->back()->with('error', 'Password lama tidak sesuai');
        }

        // Check if new password is different from current
        if (Hash::check($request->get('new-password'), Auth::user()->password)) {
            return redirect()->back()->with('error', 'Password baru tidak boleh sama dengan password lama');
        }

        try {
            // Update password
            $user = Auth::user();
            User::where('id', $user->id)->update([
                'password' => Hash::make($request->get('new-password'))
            ]);

            return redirect()->route('profile')->with('success', 'Password berhasil diubah');
            
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah password: ' . $e->getMessage());
        }
    }
}