<?php
// FORM TAMBAH / EDIT KARYAWAN
require 'config.php';

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data = ['nama' => '', 'jabatan' => '', 'departemen' => ''];

if ($id > 0) {
    $stmt = $conn->prepare(
        "SELECT nama, jabatan, departemen FROM karyawan WHERE id = ?"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) $data = $row;
    $stmt->close();
}

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Edit' : 'Tambah' ?> Karyawan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1><?= $id ? 'Edit' : 'Tambah' ?> Karyawan</h1>

<?php if ($pesan === 'wajib_nama'): ?>
    <div class="msg msg-error">Nama karyawan wajib diisi.</div>
<?php endif; ?>

<form action="karyawan-proses.php" method="post">
    <input type="hidden" name="id" value="<?= $id ?>">

    <label>Nama</label>
    <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>">

    <label>Jabatan</label>
    <input type="text" name="jabatan" value="<?= htmlspecialchars($data['jabatan']) ?>">

    <label>Departemen</label>
    <input type="text" name="departemen" value="<?= htmlspecialchars($data['departemen']) ?>">

    <p>
        <button type="submit" class="btn">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </p>
</form>
</body>
</html>