<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand mt-3">
            {{-- <img src="{{ URL::asset($pengaturan->logo) ?? 'https://via.placeholder.com/300' }}" alt="" style="width: 50px"> --}}
            <a href="">{{ $pengaturan->name ?? config('app.name') }}</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="#">{{ strtoupper(substr(config('app.name'), 0, 2)) }}</a>
        </div>
        <ul class="sidebar-menu">
            @if (Auth::check() && Auth::user()->roles == 'admin')
                <li class="{{ request()->routeIs('admin.dashboard.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('admin.dashboard') }}"><i class="fas fa-columns"></i> <span>Dashboard</span></a>
                </li>
                <li class="menu-header">Master Data</li>

                <li class="{{ request()->routeIs('admin.jurusan.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('admin.jurusan.index') }}"><i class="fas fa-book"></i> <span>Jurusan</span></a></li>
                {{-- absensi pada admin belum ada CRUD nya --}}
                <li class="{{ request()->routeIs('admin.absensi.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('admin.absensi.index') }}"><i class="fas fa-book"></i> <span>Absensi</span></a>
                </li>

                <li class="{{ request()->routeIs('mapel.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('admin.mapel.index') }}"><i class="fas fa-book"></i> <span>Mata Pelajaran</span></a>
                </li>

                <li class="{{ request()->routeIs('guru.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('admin.guru.index') }}"><i class="fas fa-user"></i> <span>Guru</span></a></li>

                <li class="{{ request()->routeIs('kelas.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('admin.kelas.index') }}"><i class="far fa-building"></i> <span>Kelas</span></a></li>

                <li class="{{ request()->routeIs('siswa.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('admin.siswa.index') }}"><i class="fas fa-users"></i> <span>Siswa</span></a></li>

                <li class="{{ request()->routeIs('jadwal.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('admin.jadwal.index') }}"><i class="fas fa-calendar"></i> <span>Jadwal</span></a></li>

                        <li class="{{ request()->routeIs('jadwalmengajar.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('admin.jadwalmengajar.index') }}"><i class="fas fa-calendar"></i> <span>Jadwal Mengajar</span></a></li>

                <li class="{{ request()->routeIs('user.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('admin.user.index') }}"><i class="fas fa-user"></i> <span>User</span></a></li>

                <li class="{{ request()->routeIs('pengumuman-sekolah.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('admin.pengumuman-sekolah.index') }}"><i class="fas fa-bullhorn"></i>
                        <span>Pengumuman</span></a></li>

                <li class="{{ request()->routeIs('pengaturan.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('admin.pengaturan.index') }}"><i class="fas fa-cog"></i> <span>Pengaturan</span></a>
                </li>
            @elseif (Auth::user()->roles == 'guru')
                <li class="{{ request()->routeIs('guru.dashboard.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('guru.dashboard') }}">
                        <i class="fas fa-columns"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-header">Master Data</li>
                <li class="{{ request()->routeIs('guru.materi.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('guru.materi.index') }}">
                        <i class="fas fa-book"></i> <span>Materi</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('guru.tugas.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('guru.tugas.index') }}">
                        <i class="fas fa-list"></i> <span>Tugas</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('guru.absensi.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('guru.absensi.index') }}">
                        <i class="fas fa-list"></i> <span>Absensi guru</span>
                    </a>
                </li>
            @elseif (Auth::check() && Auth::user()->roles == 'siswa')
                <li class="{{ request()->routeIs('siswa.dashboard.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('siswa.dashboard') }}"><i class="fas fa-columns"></i> <span>Dashboard</span></a>
                </li>
                <li class="{{ request()->routeIs('materi.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('siswa.materi') }}"><i class="fas fa-book"></i> <span>Materi</span></a></li>
                <li class="{{ request()->routeIs('tugas.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('siswa.tugas') }}"><i class="fas fa-list"></i> <span>Tugas</span></a></li>
            @else
                <li class="{{ request()->routeIs('orangtua.dashboard.*') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('orangtua.dashboard') }}"><i class="fas fa-columns"></i>
                        <span>Dashboard</span></a></li>
                <li class="{{ request()->routeIs('orangtua.tugas.siswa') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('orangtua.tugas.siswa') }}"><i class="fas fa-list"></i> <span>Tugas</span></a>
                </li>
            @endif

        </ul>
    </aside>
</div>
