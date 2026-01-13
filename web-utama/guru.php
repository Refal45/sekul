<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👨‍🏫 Tentang Guru - SekolahKu</title>
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
            padding: 100px 0 80px;
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
            font-weight: 700;
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            line-height: 1.2;
        }

        .hero p {
            max-width: 700px;
            margin: 0 auto 2rem;
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* SECTION STYLING */
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

        /* ✨ CARD STYLING */
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

        /* INFO CARD STYLING */
        .info-card {
            background: white;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            height: 100%;
            border-left: 5px solid var(--primary-blue);
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .info-card h3 {
            color: var(--primary-blue);
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 1.8rem;
        }

        .info-card h5 {
            color: var(--primary-blue);
            font-weight: 600;
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

        /* FUNCTION CARD STYLING */
        .function-card {
            background: white;
            border-radius: 12px;
            padding: 2rem 1.5rem;
            text-align: center;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .function-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .function-icon {
            font-size: 3rem;
            color: var(--primary-blue);
            margin-bottom: 1.5rem;
            display: inline-block;
            padding: 15px;
            border-radius: 50%;
            background: var(--light-blue);
            transition: all 0.3s ease;
        }

        .function-card:hover .function-icon {
            transform: scale(1.1);
            background: var(--primary-blue);
            color: white;
        }

        .function-card h5 {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
            font-size: 1.3rem;
        }

        .function-card p {
            color: #666;
        }

        /* CTA SECTION */
        .cta-section {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
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

        .cta-content {
            position: relative;
            z-index: 1;
        }

        .cta-section h2 {
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 2.5rem;
        }

        .cta-section p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-cta {
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

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .btn-cta::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transform: translateX(-100%);
        }

        .btn-cta:hover::after {
            animation: shine 1.5s ease;
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

        .feature-card {
            animation: fadeUp 1s ease-out;
        }

        .function-card {
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

            .cta-section h2 {
                font-size: 2rem;
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

            .function-card {
                padding: 1.5rem;
            }

            .cta-section h2 {
                font-size: 1.8rem;
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
                    <li class="nav-item"><a class="nav-link text-primary" href="index.php"><i class="bi bi-house-door-fill me-1"></i> Beranda</a></li>
                    <li class="nav-item"><a class="nav-link active text-primary fw-semibold" href="guru.php"><i class="bi bi-person-badge-fill me-1"></i> Guru</a></li>
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
            <h1 class="mb-3">Tentang <span class="text-warning">Guru SekolahKu</span></h1>
            <p class="lead mb-4">Kenali para guru hebat yang berperan penting dalam proses belajar-mengajar di sekolah ini.</p>
        </div>
    </section>

    <!-- Kegunaan Section -->
    <section id="kegunaan-guru" class="py-5 bg-white">
        <div class="container">
            <h2 class="text-center fw-bold text-primary section-title">Kegunaan Fitur Tentang Guru</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="info-card">
                        <h5 class="fw-semibold"><i class="bi bi-gear-fill me-2"></i>Mempermudah Administrasi</h5>
                        <p>Mempermudah pengelolaan data guru, mata pelajaran yang diajar, jadwal, dan kontak mereka secara digital.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-card">
                        <h5 class="fw-semibold"><i class="bi bi-search me-2"></i>Referensi Cepat</h5>
                        <p>Memudahkan siswa dan staf mengetahui guru pengampu tiap mata pelajaran dengan cepat dan akurat.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-card">
                        <h5 class="fw-semibold"><i class="bi bi-info-circle me-2"></i>Transparansi Informasi</h5>
                        <p>Menampilkan profil dan kualifikasi guru secara terbuka agar lebih informatif bagi siswa dan orang tua.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-card">
                        <h5 class="fw-semibold"><i class="bi bi-chat-dots me-2"></i>Komunikasi Efektif</h5>
                        <p>Memberikan akses kontak guru untuk mempermudah komunikasi antara guru, siswa, dan orang tua.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-card">
                        <h5 class="fw-semibold"><i class="bi bi-calendar2-check me-2"></i>Perencanaan Jadwal</h5>
                        <p>Mendukung penjadwalan pelajaran yang efisien dengan memperhatikan ketersediaan dan beban guru.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-card">
                        <h5 class="fw-semibold"><i class="bi bi-person-lines-fill me-2"></i>Evaluasi Guru</h5>
                        <p>Membantu pihak sekolah menilai kinerja guru berdasarkan data kehadiran dan aktivitas pengajaran.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fungsi Section -->
    <section id="fungsi-guru" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold text-primary section-title">Fungsi Fitur Tentang Guru</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="function-card">
                        <div class="function-icon"><i class="bi bi-people-fill"></i></div>
                        <h5 class="fw-semibold">Monitoring Guru</h5>
                        <p>Memantau aktivitas dan kehadiran guru di sekolah dengan data yang akurat dan terupdate.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="function-card">
                        <div class="function-icon"><i class="bi bi-journal-text"></i></div>
                        <h5 class="fw-semibold">Dokumentasi</h5>
                        <p>Menyimpan seluruh data guru dalam sistem yang aman dan mudah diakses kapan pun diperlukan.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="function-card">
                        <div class="function-icon"><i class="bi bi-telephone-fill"></i></div>
                        <h5 class="fw-semibold">Komunikasi</h5>
                        <p>Mempermudah pertukaran informasi antara guru, siswa, dan pihak sekolah secara cepat dan efisien.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container cta-content">
            <h2 class="fw-bold mb-3">Jadilah Bagian dari Tenaga Pendidik SekolahKu!</h2>
            <p class="mb-4 fs-5">
                Kami mencari guru-guru berdedikasi yang siap berbagi ilmu, menumbuhkan semangat belajar,
                dan berkontribusi dalam menciptakan generasi unggul.
                Dapatkan pengalaman mengajar yang bermakna di lingkungan yang inspiratif dan suportif.
            </p>
            <a href="../admin/register-admin.php" class="btn btn-cta">
                <i class="bi bi-person-plus"></i> Daftar Sebagai Guru
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center py-4">
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