<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrack - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; overflow: hidden; }
        .bg-hero {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-image: url('{{ asset("images/bg1.jpg") }}');
            background-size: cover;
            background-position: center;
            z-index: 0;
        }
        .overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }
        .wrapper {
            position: relative;
            z-index: 2;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-left: 80px;
        }
        .brand-title {
            font-size: 3rem;
            font-weight: bold;
            color: #fff;
            margin-bottom: 8px;
        }
        .brand-sub {
            font-size: 1.1rem;
            color: #F0A8D0;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .brand-desc {
            color: rgba(255,255,255,0.85);
            font-size: 0.95rem;
            line-height: 1.8;
            max-width: 420px;
            margin-bottom: 24px;
        }
        .btn-masuk {
            background: #E91E8C;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-masuk:hover { background: #C2185B; }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.show { display: flex; }
        .modal-box {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 380px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.3);
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: 15px; right: 20px;
            font-size: 1.3rem;
            cursor: pointer;
            color: #999;
            background: none;
            border: none;
        }
        .btn-login { background: #E91E8C; border: none; }
        .btn-login:hover { background: #C2185B; }
    </style>
</head>
<body>

<div class="bg-hero"></div>
<div class="overlay"></div>

<div class="wrapper">
    <div class="brand-title">🎓 EduTrack</div>
    <div class="brand-sub">Sistem Informasi Monitoring Akademik</div>
    <p class="brand-desc">
        Platform digital untuk pengelolaan akademik mahasiswa. Dosen dapat menginput nilai dan absensi secara real-time, lengkap dengan laporan rekap per semester.
    </p>
    <div>
        <button class="btn-masuk" onclick="document.getElementById('loginModal').classList.add('show')">
            Masuk →
        </button>
    </div>
</div>

<!-- Modal Login -->
<div class="modal-overlay" id="loginModal">
    <div class="modal-box">
        <button class="modal-close" onclick="document.getElementById('loginModal').classList.remove('show')">✕</button>
        <div class="text-center mb-4">
            <div style="color:#E91E8C; font-size:1.8rem; font-weight:bold;">🎓 EduTrack</div>
            <p class="text-muted small">Masuk ke akun Anda</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 small">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold small" style="color:#5C1033;">Email</label>
                <input type="email" name="email" class="form-control"
                    placeholder="admin@edutrack.com" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold small" style="color:#5C1033;">Password</label>
                <input type="password" name="password" class="form-control"
                    placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-login text-white w-100 fw-bold">
                Masuk
            </button>
        </form>
        <p class="text-center text-muted mt-3" style="font-size:0.75rem;">
            Hanya Admin & Dosen yang dapat mengakses halaman ini
        </p>
    </div>
</div>

@if($errors->any())
<script>
    document.getElementById('loginModal').classList.add('show');
</script>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>