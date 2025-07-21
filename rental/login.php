<?php
session_start();
include 'config.php'; // Pastikan file koneksi database di-include

// Fungsi untuk menampilkan alert
if (!function_exists('alert')) {
    function alert($msg, $url) {
        return "<script>alert('$msg'); window.location.href = '$url';</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $connection->real_escape_string($_POST['username']);
    $password = $_POST['password']; // Ambil password mentah

    // 1. Cek sebagai ADMIN terlebih dahulu
    $sql_admin = "SELECT * FROM admin WHERE username = '$username'";
    $query_admin = $connection->query($sql_admin);

    if ($query_admin && $query_admin->num_rows > 0) {
        $admin_data = $query_admin->fetch_assoc();
        // Verifikasi password menggunakan password_verify() atau fallback ke md5 untuk akun lama
        if (password_verify($password, $admin_data['password']) || md5($password) === $admin_data['password']) {
            $_SESSION['admin'] = [
                'is_logged' => true,
                'id' => $admin_data['id_admin'],
                'nama' => $admin_data['nama_admin'],
                'username' => $admin_data['username'],
                'level' => 'admin'
            ];
            header("location: admin/index.php"); // Arahkan ke dashboard admin
            exit;
        }
    }

    // 2. Jika bukan admin, cek sebagai PELANGGAN
    $sql_pelanggan = "SELECT * FROM pelanggan WHERE username = '$username' AND password = '".md5($password)."'";
    $query_pelanggan = $connection->query($sql_pelanggan);

    if ($query_pelanggan && $query_pelanggan->num_rows > 0) {
        $pelanggan_data = $query_pelanggan->fetch_assoc();
        $_SESSION['pelanggan'] = [
            'is_logged' => true,
            'id' => $pelanggan_data['id_pelanggan'],
            'nama' => $pelanggan_data['nama'],
            'username' => $pelanggan_data['username']
        ];
        header("location: index.php"); // Arahkan ke halaman utama pelanggan
        exit;
    }

    // 3. Jika login gagal untuk keduanya
    echo alert("Username atau Password salah!", "login.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center">Login Sistem</h3>
                    </div>
                    <div class="panel-body">
                        <form action="login.php" method="POST">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" name="username" class="form-control" required autofocus>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                        </form>
                    </div>
                     <div class="panel-footer">
                      Belum punya akun? <a href="index.php?page=daftar">Daftar sekarang.</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>