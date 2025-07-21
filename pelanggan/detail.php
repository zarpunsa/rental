<?php

// Check the database connection first to prevent further errors
if ($connection->connect_error) {
    die("KONEKSI DATABASE GAGAL: " . $connection->connect_error);
}

// 1. Check if an ID was provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<h3>Error: ID Transaksi tidak valid atau tidak diberikan.</h3>");
}

$id_transaksi = $connection->real_escape_string($_GET['id']);

// 2. The SQL Query to get all necessary details
$sql = "SELECT
            t.*,
            p.nama AS nama_pelanggan,
            p.no_ktp,
            p.no_telp,
            p.alamat,
            m.nama_mobil,
            m.harga AS harga_sewa_per_hari
        FROM transaksi t
        JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
        JOIN mobil m ON t.id_mobil = m.id_mobil
        WHERE t.id_transaksi = '$id_transaksi'";

$query = $connection->query($sql);

// 3. THE FIX: Check if the query returned any results
if ($query && $query->num_rows > 0) {
    // If we found data, create the $row variable
    $row = $query->fetch_assoc();
?>
    <div class="page-header">
        <h3>Detail Transaksi</h3>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading"><strong>Data Pelanggan</strong></div>
                <div class="panel-body">
                    <table class="table">
                        <tr>
                            <th width="35%">Nama Pelanggan</th>
                            <td>: <?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                        </tr>
                        <tr>
                            <th>No. KTP</th>
                            <td>: <?= htmlspecialchars($row['no_ktp']) ?></td>
                        </tr>
                        <tr>
                            <th>No. Telepon</th>
                            <td>: <?= htmlspecialchars($row['no_telp']) ?></td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>: <?= htmlspecialchars($row['alamat']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading"><strong>Data Sewa</strong></div>
                <div class="panel-body">
                     <table class="table">
                        <tr>
                            <th width="35%">Mobil yang Disewa</th>
                            <td>: <?= htmlspecialchars($row['nama_mobil']) ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Sewa</th>
                            <td>: <?= date("d-m-Y", strtotime($row['tgl_sewa'])) ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Kembali</th>
                            <td>: <?= date("d-m-Y", strtotime($row['tgl_kembali'])) ?></td>
                        </tr>
                        <tr>
                            <th>Harga per Hari</th>
                            <td>: Rp. <?= number_format($row['harga_sewa_per_hari']) ?>,-</td>
                        </tr>
                        <tr>
                            <th>Total Biaya</th>
                            <td>: Rp. <?= number_format($row['total_harga']) ?>,-</td>
                        </tr>
                         <tr>
                            <th>Status</th>
                            <td>: <?= ($row['status']) ? "<span class='label label-success'>Sudah Kembali</span>" : "<span class='label label-danger'>Disewa</span>" ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
	<div class="panel-footer hidden-print ">
        <a onClick="window.print();return false" class="btn btn-primary"><i class="glyphicon glyphicon-print"></i></a>
		<a href="?page=profil" class="btn btn-primary">Kembali</a>
    </div>
    <!-- <a href="?page=transaksi" class="btn btn-primary">Kembali</a> -->

<?php
} else {
    // If no data was found, display this friendly error message
    echo "<div class='alert alert-danger'><h3>Data Tidak Ditemukan</h3><p>Transaksi dengan ID yang Anda minta tidak dapat ditemukan dalam sistem.</p></div>";
}
?>