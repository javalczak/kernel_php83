<?php require __DIR__ . '/_header.php'; ?>
<h1 style="margin-bottom:10px;">Dashboard</h1>
<p style="color:#777;">Welcome back, <?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?>.</p>
<?php require __DIR__ . '/_footer.php'; ?>
