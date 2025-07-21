<?php
require_once '../../config.php';

$action = (isset($_GET['action'])) ? $_GET['action'] : 'add';
$id = (isset($_GET['id'])) ? $_GET['id'] : NULL;

switch ($action) {
	case 'add':
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$nama 		= $_POST['nama'];
			$username = $_POST['username'];
			$password = md5($_POST['password']);
			// PERBAIKAN: Menggunakan 'nama' bukan 'nama_admin'
			$connection->query("INSERT INTO admin (nama, username, password) VALUES('$nama', '$username', '$password')");
			redirect('?page=admin');
		}
		break;
	case 'delete':
		$sql = "DELETE FROM admin WHERE id_admin='$_GET[id]'";
		if ($query = $connection->query($sql)) {
			redirect('?page=admin');
		} else {
			echo "Gagal menghapus data!";
		}
		break;
	case 'update':
		$sql = $connection->query("SELECT * FROM admin WHERE id_admin='$id'");
		$row = $sql->fetch_assoc();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$nama 		= $_POST['nama'];
			$username = $_POST['username'];
			$password = ($_POST["password"] != "") ? md5($_POST["password"]) : $row["password"];
			// PERBAIKAN: Menggunakan 'nama' bukan 'nama_admin'
			$connection->query("UPDATE admin SET nama='$nama', username='$username', password='$password' WHERE id_admin='$id'");
			redirect('?page=admin');
		}
		break;
}
?>

<div class="row">
	<div class="col-md-4">
	    <div class="panel panel-<?= ($action == 'add') ? "info" : "warning" ?>">
	        <div class="panel-heading"><h3 class="text-center"><?= ($action == 'add') ? "TAMBAH" : "EDIT" ?></h3></div>
	        <div class="panel-body">
	            <form action="" method="POST">
	                <div class="form-group">
	                    <label for="nama">Nama</label>
						<input type="text" name="nama" class="form-control" value="<?= ($action == 'update') ? $row['nama'] : '' ?>">
	                </div>
	                <div class="form-group">
	                    <label for="username">Username</label>
	                    <input type="text" name="username" class="form-control" value="<?= ($action == 'update') ? $row['username'] : '' ?>">
	                </div>
	                <div class="form-group">
	                    <label for="password">Password</label>
	                    <input type="password" name="password" class="form-control">
											<?php if ($action == 'update'): ?>
												<span class="help-block">*) Kosongkan jika tidak diubah</span>
											<?php endif; ?>
	                </div>
	                <button type="submit" class="btn btn-<?= ($action == 'add') ? "info" : "warning" ?> btn-block">SIMPAN</button>
	                <?php if ($action == 'update'): ?>
										<a href="?page=admin" class="btn btn-info btn-block">BATAL</a>
	                <?php endif; ?>
	            </form>
	        </div>
	    </div>
	</div>
	<div class="col-md-8">
	    <div class="panel panel-info">
	        <div class="panel-heading"><h3 class="text-center">DAFTAR ADMIN</h3></div>
	        <div class="panel-body">
	            <table class="table table-condensed">
	                <thead>
	                    <tr>
	                        <th>No</th>
	                        <th>Nama</th>
	                        <th>Username</th>
	                        <th></th>
	                    </tr>
	                </thead>
	                <tbody>
	                    <?php $no = 1; ?>
	                    <?php if ($query = $connection->query("SELECT * FROM admin")): ?>
	                        <?php while($row = $query->fetch_assoc()): ?>
	                        <tr>
	                            <td><?= $no++ ?></td>
								<td><?= $row['nama'] ?></td>
	                            <td><?= $row['username'] ?></td>
	                            <td>
	                                <div class="btn-group">
	                                    <a href="?page=admin&action=update&id=<?= $row['id_admin'] ?>" class="btn btn-warning btn-xs">Edit</a>
	                                    <a href="?page=admin&action=delete&id=<?= $row['id_admin'] ?>" class="btn btn-danger btn-xs">Hapus</a>
	                                </div>
	                            </td>
	                        </tr>
	                        <?php endwhile ?>
	                    <?php endif ?>
	                </tbody>
	            </table>
	        </div>
	    </div>
	</div>
</div>