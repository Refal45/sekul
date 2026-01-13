<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📚 Mata Pelajaran - SekolahKu</title>
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

        /* 📚 MAPEL TABLE STYLING */
        .mapel-section {
            padding: 80px 0;
            background: white;
        }

        .mapel-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: none;
            transition: all 0.3s ease;
        }

        .mapel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .mapel-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            text-align: center;
            padding: 25px 20px;
            position: relative;
            overflow: hidden;
        }

        .mapel-header::before {
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

        .mapel-header h2 {
            font-weight: 800;
            font-size: 2.2rem;
            margin: 0;
            position: relative;
            z-index: 1;
            letter-spacing: 1px;
        }

        .mapel-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        .mapel-table th {
            background: var(--light-blue);
            color: var(--dark-blue);
            font-weight: 700;
            padding: 18px 12px;
            text-align: center;
            border-bottom: 3px solid var(--primary-blue);
            font-size: 1rem;
            position: relative;
        }

        .mapel-table th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary-blue);
        }

        .mapel-table td {
            padding: 16px 12px;
            text-align: center;
            border-bottom: 2px solid #e9ecef;
            font-weight: 600;
            color: #495057;
            transition: all 0.2s ease;
        }

        .mapel-table tr:hover td {
            background: rgba(13, 110, 253, 0.05);
        }

        .mapel-table tr:last-child td {
            border-bottom: none;
        }

        .mapel-number {
            background: #f8f9fa;
            font-weight: 700;
            color: var(--dark-blue);
            width: 10%;
        }

        .mapel-name {
            text-align: left;
            padding-left: 20px;
            font-weight: 600;
        }

        .mapel-guru {
            color: var(--primary-blue);
            font-weight: 600;
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

        .mapel-card {
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

            .mapel-header h2 {
                font-size: 1.6rem;
            }

            .mapel-table th,
            .mapel-table td {
                padding: 12px 8px;
                font-size: 0.9rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .navbar-brand {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 576px) {
            .mapel-table {
                font-size: 0.8rem;
            }

            .mapel-table th,
            .mapel-table td {
                padding: 10px 6px;
            }

            .mapel-name {
                padding-left: 10px;
            }

            .hero {
                padding: 100px 0 80px;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .mapel-header h2 {
                font-size: 1.4rem;
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
                    <li class="nav-item"><a class="nav-link text-primary" href="guru.php"><i class="bi bi-person-badge-fill me-1"></i> Guru</a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="jadwal.php"><i class="bi bi-calendar-week-fill me-1"></i> Jadwal</a></li>
                    <li class="nav-item"><a class="nav-link active text-primary fw-semibold" href="mapel.php"><i class="bi bi-book-fill me-1"></i> Mapel</a></li>
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
            <h1 class="mb-3">📘 Daftar Mata Pelajaran XII PPLG</h1>
            <p class="lead mb-4">Berikut adalah daftar lengkap mata pelajaran beserta guru pengampunya di kelas XII PPLG.</p>
        </div>
    </section>

    <!-- Mapel Table Section -->
    <section class="mapel-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="mapel-card">
                        <div class="mapel-header">
                            <h2>MATA PELAJARAN XII PPLG</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="mapel-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Guru Pengampu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="mapel-number">1</td>
                                        <td class="mapel-name">Bahasa Inggris</td>
                                        <td class="mapel-guru">Mrs. Indah</td>
                                    </tr>
                                    <tr>
                                        <td class="mapel-number">2</td>
                                        <td class="mapel-name">PKK (Produk Kreatif dan Kewirausahaan)</td>
                                        <td class="mapel-guru">Pak Ari</td>
                                    </tr>
                                    <tr>
                                        <td class="mapel-number">3</td>
                                        <td class="mapel-name">PBO (Pemrograman Berorientasi Objek)</td>
                                        <td class="mapel-guru">Pak Martimbang</td>
                                    </tr>
                                    <tr>
                                        <td class="mapel-number">4</td>
                                        <td class="mapel-name">PKN (Pendidikan Kewarganegaraan)</td>
                                        <td class="mapel-guru">Bu Megarahayu</td>
                                    </tr>
                                    <tr>
                                        <td class="mapel-number">5</td>
                                        <td class="mapel-name">Agama</td>
                                        <td class="mapel-guru">Bu Mufida</td>
                                    </tr>
                                    <tr>
                                        <td class="mapel-number">6</td>
                                        <td class="mapel-name">Bahasa Indonesia</td>
                                        <td class="mapel-guru">Pak Affan</td>
                                    </tr>
                                    <tr>
                                        <td class="mapel-number">7</td>
                                        <td class="mapel-name">Database (PBTGM)</td>
                                        <td class="mapel-guru">Pak Aqil</td>
                                    </tr>
                                    <tr>
                                        <td class="mapel-number">8</td>
                                        <td class="mapel-name">Pemrograman Web</td>
                                        <td class="mapel-guru">Pak Aqil</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
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

        // Add hover effects to table rows
        document.querySelectorAll('.mapel-table tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.02)';
            });

            row.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
    </script>
</body>

</html>