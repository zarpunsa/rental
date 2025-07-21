<?php
// Selalu mulai session di baris paling atas jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

/**
 * Pengaturan Koneksi Database
 */
$host = "localhost";
$user = "root";
$pass = "";
$database = "mobil"; // Pastikan nama database sudah benar

$connection = new Mysqli($host, $user, $pass, $database);
if ($connection->connect_error) {
    die("<h3>ERROR: Koneksi database gagal! " . $connection->connect_error . "</h3>");
}

/**
 * Pengaturan Halaman
 */
$_PAGE = isset($_GET["page"]) ? $_GET["page"] : "home";
$_ADMINPAGE = isset($_GET["page"]) ? $_GET["page"] : "home";

function page($page) {
    return "pelanggan/" . $page . ".php";
}

function adminPage($page) {
    return "page/" . $page . ".php";
}

/**
 * Fungsi Notifikasi Alert
 */
function alert($msg, $to = null) {
    $to = ($to) ? $to : $_SERVER["PHP_SELF"];
    echo "<script>alert('" . addslashes($msg) . "');window.location='{$to}';</script>";
    exit;
}


// ======================================================================
// BLOK OTOMATIS YANG SUDAH DIPERBAIKI DAN DIAMANKAN
// ======================================================================

/**
 * 1. Update Otomatis Status Mobil & Supir untuk transaksi yang sudah lewat tanggal ambil
 * (Logika ini sepertinya ingin membuat mobil/supir jadi tidak tersedia, maka status diubah jadi '0')
 */
$sql_update = "SELECT id_mobil, id_transaksi FROM transaksi WHERE status='0' AND tgl_ambil <= NOW()";
if ($query_update = $connection->query($sql_update)) {
    while ($data = $query_update->fetch_assoc()) {
        // Update status mobil menjadi tidak tersedia ('0')
        $connection->query("UPDATE mobil SET status='0' WHERE id_mobil=" . $data['id_mobil']);
        
        // Cari sopir yang terkait dengan transaksi ini
        $q_supir = $connection->query("SELECT id_supir FROM detail_transaksi WHERE id_transaksi=" . $data['id_transaksi']);
        if ($q_supir && $q_supir->num_rows) {
            $supir = $q_supir->fetch_assoc();
            // Update status sopir menjadi tidak tersedia ('0')
            $connection->query("UPDATE supir SET status='0' WHERE id_supir=" . $supir['id_supir']);
        }
    }
}


/**
 * 2. Pembatalan Otomatis untuk transaksi yang belum dikonfirmasi lebih dari 3 jam
 */
$sql_batal = "SELECT id_transaksi, id_mobil FROM transaksi WHERE konfirmasi='0' AND TIMESTAMPDIFF(HOUR, tgl_sewa, NOW()) > 3";
if ($query_batal = $connection->query($sql_batal)) {
    while ($data = $query_batal->fetch_assoc()) {
        $id_transaksi = $data['id_transaksi'];
        $id_mobil = $data['id_mobil'];

        // Update status transaksi menjadi "dibatalkan"
        $connection->query("UPDATE transaksi SET pembatalan='1' WHERE id_transaksi=$id_transaksi");
        
        // Kembalikan status mobil menjadi "tersedia" ('1')
        $connection->query("UPDATE mobil SET status='1' WHERE id_mobil=$id_mobil");
        
        // Cari sopir yang terkait, lalu kembalikan statusnya dan hapus detailnya
        $q_supir_batal = $connection->query("SELECT id_supir FROM detail_transaksi WHERE id_transaksi=$id_transaksi");
        if ($q_supir_batal && $q_supir_batal->num_rows) {
            $supir = $q_supir_batal->fetch_assoc();
            // Kembalikan status sopir menjadi "tersedia" ('1')
            $connection->query("UPDATE supir SET status='1' WHERE id_supir=" . $supir['id_supir']);
            // Hapus dari detail transaksi
            $connection->query("DELETE FROM detail_transaksi WHERE id_transaksi=$id_transaksi");
        }
    }
}


/**
 * 3. Perhitungan Denda Otomatis
 * (Kode ini sudah cukup baik, hanya perlu diamankan dengan prepared statement jika memungkinkan)
 */
$sql_denda = "SELECT
                a.id_transaksi,
                (TIMESTAMPDIFF(HOUR, ADDDATE(a.tgl_ambil, INTERVAL a.lama DAY), a.tgl_kembali)) AS jam_terlambat
              FROM transaksi a
              WHERE a.tgl_kembali IS NOT NULL";
if ($query_denda = $connection->query($sql_denda)) {
    $harga_denda_per_jam = 35000; // Definisikan harga denda
    while ($a = $query_denda->fetch_assoc()) {
        if ($a["jam_terlambat"] > 0) {
            $total_denda = $a["jam_terlambat"] * $harga_denda_per_jam;
            $connection->query("UPDATE transaksi SET denda=$total_denda WHERE id_transaksi=" . $a['id_transaksi']);
        }
    }
}
?>