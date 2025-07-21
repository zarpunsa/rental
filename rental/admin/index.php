<?php
// Selalu mulai session di baris paling atas
session_start();

// 1. MEMUAT KONFIGURASI DAN MEMERIKSA KONEKSI DATABASE
require_once "config.php";

// Pemeriksaan koneksi yang sangat penting untuk mencegah error 'query() on null'
if ($connection->connect_error) {
    die("KONEKSI DATABASE GAGAL: " . $connection->connect_error);
}

// 2. MENDAPATKAN HALAMAN YANG DIMINTA PENGGUNA
// Jika tidak ada permintaan halaman, default-nya adalah 'home'
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aplikasi Rental Mobil</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    </head>
<body>

    <nav class="navbar navbar-default">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href="index.php">Rental Mobil</a>
            </div>
            <ul class="nav navbar-nav">
                <li><a href="index.php?page=home">Home</a></li>
                <?php if (isset($_SESSION['pelanggan'])): // Menu untuk Pelanggan ?>
                    <li><a href="index.php?page=profil">Profil Saya</a></li>
                    <li><a href="index.php?page=riwayat">Riwayat Transaksi</a></li>
                <?php endif; ?>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <?php if (isset($_SESSION['pelanggan'])): ?>
                    <li><a>Selamat Datang, <?= htmlspecialchars($_SESSION['pelanggan']['nama']) ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php elseif (isset($_SESSION['admin'])): ?>
                    <li><a>Login sebagai: ADMIN</a></li>
                    <li><a href="admin/index.php">Dashboard Admin</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: // Menu untuk Tamu ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="index.php?page=daftar">Daftar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <?php
        // Menggunakan switch untuk memuat file halaman yang sesuai
        switch ($page) {
            case 'daftar':
                include 'daftar.php';
                break;
            
            case 'transaksi':
                // Halaman ini mungkin perlu ID mobil, jadi pastikan user sudah login
                if (!isset($_SESSION['pelanggan'])) {
                    echo "<div class='alert alert-danger'>Anda harus login untuk bisa menyewa mobil.</div>";
                    include 'home.php';
                } else {
                    include 'transaksi.php';
                }
                break;

            case 'profil':
                include 'daftar.php'; // Menggunakan file daftar.php untuk update profil
                break;

            case 'riwayat':
                include 'riwayat.php'; // Anda perlu membuat file ini
                break;

            case 'home':
            default:
                // Halaman default yang akan dimuat
                include 'home.php';
                break;
        }
        ?>
    </div>

    <footer class="text-center" style="padding: 20px; margin-top: 50px; border-top: 1px solid #ccc;">
        <p>&copy; <?= date("Y") ?> Rental Mobil. Semua Hak Cipta Dilindungi.</p>
    </footer>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>