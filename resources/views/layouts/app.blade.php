<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrack - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/dist/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #F0A8D0; }
        .sidebar a { color: #5C1033; text-decoration: none; }
        .sidebar a:hover { color: #fff; background: #E91E8C; border-radius: 8px; }
        .sidebar .active { color: #fff; background: #E91E8C; border-radius: 8px; }
        .brand { color: #5C1033; font-weight: bold; font-size: 1.4rem; }
        .top-navbar { background: #fff; border-bottom: 2px solid #FF6EC7; }
        .role-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .role-admin  { background: #5C1033; color: #fff; }
        .role-dosen  { background: #E91E8C; color: #fff; }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar p-3">
            <div class="brand mb-1 text-center">🎓 EduTrack</div>
            {{-- Role badge --}}
            <div class="text-center mb-4">
                @if(Auth::user()->role === 'admin')
                    <span class="role-badge role-admin">Admin</span>
                @elseif(Auth::user()->role === 'dosen')
                    <span class="role-badge role-dosen">Dosen</span>
                @endif
            </div>

            <ul class="nav flex-column gap-2">
                {{-- Dashboard — semua role --}}
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link px-3 py-2 {{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                {{-- ── ADMIN ONLY ── --}}
                @if(Auth::user()->role === 'admin')
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link px-3 py-2 {{ request()->is('users*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Kelola User
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('mahasiswa.index') }}" class="nav-link px-3 py-2 {{ request()->is('mahasiswa*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Mahasiswa
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('kelas.index') }}" class="nav-link px-3 py-2 {{ request()->is('kelas*') ? 'active' : '' }}">
                        <i class="bi bi-diagram-3"></i> Kelas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('matakuliah.index') }}" class="nav-link px-3 py-2 {{ request()->is('matakuliah*') ? 'active' : '' }}">
                        <i class="bi bi-book"></i> Mata Kuliah
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('jadwal.index') }}" class="nav-link px-3 py-2 {{ request()->is('jadwal*') ? 'active' : '' }}">
                        <i class="bi bi-clock"></i> Jadwal
                    </a>
                </li>
                @endif

                {{-- ── DOSEN ONLY ── --}}
                @if(Auth::user()->role === 'dosen')
                <li class="nav-item">
                    <a href="{{ route('jadwal.index') }}" class="nav-link px-3 py-2 {{ request()->is('jadwal*') ? 'active' : '' }}">
                        <i class="bi bi-clock"></i> Jadwal
                    </a>
                </li>
                @endif

                {{-- ── ADMIN & DOSEN ── --}}
                <li class="nav-item">
                    <a href="{{ route('nilai.index') }}" class="nav-link px-3 py-2 {{ request()->is('nilai*') ? 'active' : '' }}">
                        <i class="bi bi-journal-check"></i> Nilai
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('rapot.index') }}" class="nav-link px-3 py-2 {{ request()->is('rapot*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-pdf"></i> Rapor
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('absensi.index') }}" class="nav-link px-3 py-2 {{ request()->is('absensi*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-check"></i> Absensi
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sesi-absensi.index') }}" class="nav-link px-3 py-2 {{ request()->is('sesi-absensi*') ? 'active' : '' }}">
                        <i class="bi bi-door-open"></i> Sesi Absensi
                    </a>
                </li>
            </ul>

            <div class="mt-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm w-100 fw-bold" style="background:#E91E8C; color:#fff;">
                        <i class="bi bi-box-arrow-left"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4">
            <div class="top-navbar p-2 px-4 mb-4 rounded d-flex justify-content-between align-items-center">
                <h5 class="mb-0">@yield('title')</h5>
                <span class="text-muted">👤 {{ Auth::user()->name }}</span>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
