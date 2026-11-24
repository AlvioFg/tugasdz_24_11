<?php $current = basename($_SERVER['PHP_SELF']); ?>
<nav class="navbar">
    <div class="nav-brand">Sistem Absensi</div>
    <div class="nav-links">
        <a href="index.php" class="<?= $current === 'index.php' ? 'active' : '' ?>">Dashboard & Karyawan</a>
        <a href="absensi.php" class="<?= $current === 'absensi.php' ? 'active' : '' ?>">Log Absensi</a>
        <a href="rekap-bulanan.php" class="<?= $current === 'rekap-bulanan.php' ? 'active' : '' ?>">Rekap Bulanan</a>
    </div>
    <div class="nav-user">
        <span>Halo, <strong><?= e($_SESSION['user_nama'] ?? 'Admin') ?></strong></span>
        <a href="logout.php" class="btn-logout" onclick="return confirm('Keluar dari sistem?')">Keluar</a>
    </div>
</nav>