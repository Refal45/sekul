<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏫 Aplikasi Jadwal Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #0d6efd;
            --light-blue: #e3f2fd;
            --dark-blue: #0a58ca;
            --accent-blue: #3d8bfd;
        }

        body {
            scroll-behavior: smooth;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* 🌟 NAVBAR */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            padding: 0.8rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            display: flex;
            align-items: center;
            font-size: 1.5rem;
        }

        .navbar-brand img {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover img {
            transform: scale(1.05);
        }

        .nav-link {
            position: relative;
            font-weight: 500;
            margin: 0 5px;
            border-radius: 6px;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem !important;
        }

        .nav-link:hover {
            background-color: var(--light-blue);
            transform: translateY(-2px);
        }

        .nav-link.active {
            background-color: var(--primary-blue);
            color: white !important;
        }

        /* PERBAIKAN: Navbar di mode mobile */
        .navbar-collapse {
            transition: all 0.3s ease;
        }

        /* Jarak antar item menu di mobile */
        @media (max-width: 991.98px) {
            .navbar-nav {
                padding: 1rem 0;
            }

            .nav-item {
                margin-bottom: 0.5rem;
            }

            .nav-link {
                padding: 0.75rem 1rem !important;
                margin: 0.25rem 0;
                border-radius: 8px;
                font-size: 1.1rem;
            }

            /* Tombol login/register di mobile */
            .navbar .d-flex {
                flex-direction: column;
                width: 100%;
                gap: 0.5rem;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(0, 0, 0, 0.1);
            }

            .navbar .btn {
                width: 100%;
                justify-content: center;
                padding: 0.75rem;
            }
        }

        /* 🌟 HERO SECTION */
        .hero {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 120px 0 100px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,122.7C960,117,1056,171,1152,197.3C1248,224,1344,224,1392,224L1440,224L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: center bottom;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-weight: 800;
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            line-height: 1.2;
        }

        .hero p {
            max-width: 700px;
            margin: 0 auto 2rem;
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .btn-hero {
            background: white;
            color: var(--primary-blue);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            font-size: 1.1rem;
        }

        .btn-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .btn-hero::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transform: translateX(-100%);
        }

        .btn-hero:hover::after {
            animation: shine 1.5s ease;
        }

        /* ✨ CARD FEATURE */
        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 3rem;
            font-size: 2.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary-blue);
            border-radius: 2px;
        }

        .feature-card {
            background: white;
            border-radius: 12px;
            padding: 2rem 1.5rem;
            text-align: center;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--primary-blue);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--primary-blue);
            margin-bottom: 1.5rem;
            display: inline-block;
            padding: 15px;
            border-radius: 50%;
            background: var(--light-blue);
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
            background: var(--primary-blue);
            color: white;
        }

        .feature-card h5 {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
            font-size: 1.3rem;
        }

        .feature-card p {
            color: #666;
            margin-bottom: 1.5rem;
        }

        .btn-feature {
            background: var(--light-blue);
            color: var(--primary-blue);
            border: none;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-feature:hover {
            background: var(--primary-blue);
            color: white;
            transform: translateY(-2px);
        }

        /* INFO SECTIONS */
        .info-section {
            padding: 80px 0;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            height: 100%;
            border-left: 5px solid var(--primary-blue);
            transition: transform 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
        }

        .info-card h3 {
            color: var(--primary-blue);
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 1.8rem;
        }

        .info-card p {
            color: #555;
            line-height: 1.7;
        }

        .info-card ul {
            padding-left: 1rem;
        }

        .info-card li {
            margin-bottom: 0.5rem;
            color: #555;
            position: relative;
            padding-left: 1.5rem;
        }

        .info-card li::before {
            content: "•";
            color: var(--primary-blue);
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        /* FOOTER */
        footer {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        /* 🔹 ANIMATIONS 🔹 */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(60px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shine {
            to {
                transform: translateX(100%);
            }
        }

        /* Apply animations */
        .hero-content h1 {
            animation: slideUp 1.5s ease-out;
        }

        .hero-content p {
            animation: slideUp 1.8s ease-out;
        }

        .btn-hero {
            animation: zoomIn 2s ease-out;
        }

        .feature-card {
            animation: fadeUp 1s ease-out;
        }

        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary-blue);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
            z-index: 1000;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: var(--dark-blue);
            transform: translateY(-3px);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .info-card {
                margin-bottom: 2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .navbar-brand {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 576px) {
            .hero {
                padding: 100px 0 80px;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .info-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">
                <img src="https://th.bing.com/th/id/OIP.Pg5X0hL6o9CkquPBvWIhfQHaHa?w=161&h=180&c=7&r=0&o=7&dpr=1.2&pid=1.7&rm=3"
                    alt="SekolahK Logo"
                    style="width:60px; height:auto; object-fit:cover;"
                    class="me-2">
                SekolahKu
            </a>

            <!-- Burger Menu -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link active text-primary fw-semibold" href="index.php"><i class="bi bi-house-door-fill me-1"></i> Beranda</a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="guru.php"><i class="bi bi-person-badge-fill me-1"></i> Guru</a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="jadwal.php"><i class="bi bi-calendar-week-fill me-1"></i> Jadwal</a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="mapel.php"><i class="bi bi-book-fill me-1"></i> Mapel</a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="siswa.php"><i class="bi bi-people-fill me-1"></i> Siswa</a></li>
                </ul>

                <!-- Tombol Login & Register -->
                <div class="d-flex gap-2">
                    <a href="../admin/register-admin.php" class="btn btn-outline-primary btn-sm fw-semibold">
                        <i class="bi bi-person-plus"></i> Daftar
                    </a>
                    <a href="../admin/login-admin.php" class="btn btn-primary btn-sm text-white fw-semibold">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-content">
            <h1 class="mb-3">Selamat Datang di <span class="text-warning">Aplikasi Jadwal Sekolah</span></h1>
            <p class="lead mb-4">Kelola data mata pelajaran, jadwal, guru, dan siswa dengan mudah, cepat, dan efisien.</p>
            <a href="#fitur" class="btn btn-hero fw-semibold">
                Jelajahi Fitur <i class="bi bi-arrow-down-circle ms-1"></i>
            </a>
        </div>
    </section>

    <!-- Fitur Section -->
    <section id="fitur" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold text-primary section-title">Fitur Unggulan</h2>
            <div class="row g-4">

                <!-- Fitur Card Template -->
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-person-badge"></i></div>
                        <h5 class="fw-semibold">Data Guru</h5>
                        <p>Informasi guru dan mata pelajaran yang diajar.</p>
                        <a href="guru.php" class="btn btn-feature">Lihat Detail</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-calendar-week"></i></div>
                        <h5 class="fw-semibold">Jadwal Pelajaran</h5>
                        <p>Atur jadwal belajar untuk setiap kelas dan hari.</p>
                        <a href="jadwal.php" class="btn btn-feature">Lihat Detail</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-book"></i></div>
                        <h5 class="fw-semibold">Mata Pelajaran</h5>
                        <p>Daftar lengkap dan pengelolaan mapel dengan mudah.</p>
                        <a href="mapel.php" class="btn btn-feature">Lihat Detail</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-people"></i></div>
                        <h5 class="fw-semibold">Data Siswa</h5>
                        <p>Kelola informasi siswa secara cepat dan efisien.</p>
                        <a href="siswa.php" class="btn btn-feature">Lihat Detail</a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Tentang & Tujuan Section -->
    <section class="info-section bg-white">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6">
                    <div class="info-card">
                        <h3>Tentang Aplikasi</h3>
                        <p>
                            Aplikasi Jadwal Sekolah dirancang untuk memudahkan pengelolaan jadwal pelajaran, data guru, siswa, dan mata pelajaran secara digital.
                            Dengan tampilan interaktif dan mudah digunakan, aplikasi ini membantu sekolah menjalankan kegiatan akademik dengan lebih efisien dan transparan.
                        </p>
                        <p>
                            Sistem ini mendukung pengelolaan data secara real-time, memungkinkan pembaruan jadwal dan informasi akademik yang cepat dan akurat.
                        </p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="info-card">
                        <h3>Tujuan Pembuatan</h3>
                        <p>
                            Aplikasi ini dibuat untuk membantu manajemen akademik dan administrasi di SMK Kesuma Bangsa 2 agar lebih efisien dan terorganisir.
                        </p>
                        <ul>
                            <li>Mempermudah pengelolaan jadwal pelajaran dan data guru</li>
                            <li>Menyediakan akses cepat untuk data siswa dan mapel</li>
                            <li>Meningkatkan komunikasi antar guru, siswa, dan admin</li>
                            <li>Menyimpan informasi akademik secara terpusat dan aman</li>
                            <li>Mengurangi penggunaan kertas dengan sistem digital</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Studi Kasus Section -->
    <section class="info-section bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="info-card text-center">
                        <h3>Studi Kasus di Kelas PPLG</h3>
                        <p>
                            Studi kasus ini diterapkan pada kelas <strong>XII PPLG</strong> sebagai bagian dari upaya digitalisasi jadwal pelajaran.
                            Dengan menggunakan <strong>Aplikasi Jadwal Sekolah Online</strong>, proses pengaturan dan pembaruan jadwal menjadi lebih cepat,
                            akurat, dan mudah diakses oleh guru maupun siswa.
                        </p>
                        <p>
                            Setiap guru dapat mengelola jadwal mengajar tanpa harus mencetak lembaran jadwal manual, sementara siswa dapat melihat
                            perubahan jadwal secara real-time melalui perangkat mereka. Hasil penerapan ini menunjukkan peningkatan efisiensi hingga
                            <strong>40%</strong> dibandingkan metode konvensional.
                        </p>
                        <div class="mt-4">
                            <a href="jadwal.php" class="btn btn-primary">Lihat Jadwal Contoh</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center py-4 mt-auto">
        <div class="container">
            <p class="mb-0">&copy; <?= date("Y"); ?> <strong>SekolahKu</strong> | Semua Hak Dilindungi</p>
            <p class="mb-0 mt-2">SMK Kesuma Bangsa 2 - Pengembangan Perangkat Lunak dan Gim</p>
        </div>
    </footer>

    <!-- Back to top button -->
    <a href="#" class="back-to-top">
        <i class="bi bi-arrow-up"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.1)';
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            } else {
                navbar.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.1)';
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            }

            // Back to top button visibility
            const backToTopButton = document.querySelector('.back-to-top');
            if (window.scrollY > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Back to top functionality
        document.querySelector('.back-to-top').addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>

</html>