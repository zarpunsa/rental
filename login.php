<?php
session_start(); // Memulai session di awal

// Fungsi alert yang mungkin belum ada, ditambahkan untuk kelengkapan
if (!function_exists('alert')) {
    function alert($msg, $url) {
        return "<script>alert('$msg'); window.location.href = '$url';</script>";
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Cek apakah pengguna sedang dalam masa timeout
    if (isset($_SESSION['login_timeout']) && time() < $_SESSION['login_timeout']) {
        $remaining_time = ceil(($_SESSION['login_timeout'] - time()) / 60);
        echo alert("Anda telah gagal login 3 kali. Silakan coba lagi dalam $remaining_time menit.", "login.php");
        exit;
    } elseif (isset($_SESSION['login_timeout']) && time() >= $_SESSION['login_timeout']) {
        // Jika masa timeout sudah berakhir, hapus session lock
        unset($_SESSION['login_attempts']);
        unset($_SESSION['login_timeout']);
    }

    require_once "config.php";

    // PENTING: Mencegah SQL Injection
    $username = $connection->real_escape_string($_POST['username']);
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM pelanggan WHERE username='$username' AND password='$password'";

    if ($query = $connection->query($sql)) {
        if ($query->num_rows) {
            // Jika login berhasil, hapus catatan percobaan gagal
            unset($_SESSION['login_attempts']);
            unset($_SESSION['login_timeout']);

            while ($data = $query->fetch_array()) {
                $_SESSION["pelanggan"]["is_logged"] = true;
                $_SESSION["pelanggan"]["id"] = $data["id_pelanggan"];
                $_SESSION["pelanggan"]["username"] = $data["username"];
                $_SESSION["pelanggan"]["nama"] = $data["nama"];
                $_SESSION["pelanggan"]["no_ktp"] = $data["no_ktp"];
                $_SESSION["pelanggan"]["no_telp"] = $data["no_telp"];
                $_SESSION["pelanggan"]["email"] = $data["email"];
                $_SESSION["pelanggan"]["alamat"] = $data["alamat"];
            }
            header('location: index.php');
            exit;
        } else {
            // Jika login gagal, lacak percobaan
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 1;
            } else {
                $_SESSION['login_attempts']++;
            }

            // Jika sudah 3 kali gagal, set timeout
            if ($_SESSION['login_attempts'] >= 3) {
                $_SESSION['login_timeout'] = time() + (5 * 60); // Timeout untuk 5 menit
                echo alert("Anda telah gagal login 3 kali. Akun Anda dikunci selama 5 menit.", "login.php");
            } else {
                $remaining_attempts = 3 - $_SESSION['login_attempts'];
                echo alert("Username / Password tidak sesuai! Sisa percobaan: $remaining_attempts", "login.php");
            }
        }
    } else {
        echo "Query error!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mobil</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body {
            margin-top: 40px;
            background-image:url(assets/img/bg.jpg);
            background-size:cover;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="panel panel-info">
                    <div class="panel-heading"><h3 class="text-center"><b>Jakarta</b></small> Rental Mobil </small></h3></div>
                    <div class="panel-body">
                        <form action="<?=$_SERVER['REQUEST_URI']?>" method="POST">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" name="username" class="form-control" id="username" placeholder="username" autofocus="on" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                            </div>
                            <button type="submit" class="btn btn-info btn-block">Login</button>
                        </form>
                    </div>
                    <div class="panel-footer">
                      Belum punya akun? <a href="index.php?page=daftar">daftar sekarang.</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
</body>
</html>