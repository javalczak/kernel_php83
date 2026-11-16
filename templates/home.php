<?php
declare(strict_types=1);
/** @var \Engine\Translation\Translator $translator */
$pageTitle = 'Pure Kernel';
require BASE_PATH . '/templates/_partials/_header.php';
?>

<div class="card">
    <div class="badge">Pure Kernel</div>
    <h1>Działa.</h1>
    <p>Autorski micro-framework PHP 8.3 zaprojektowany pod wdrożenie na shared hostingu przez FTP - bez Composera na serwerze, bez SSH, bez Dockera.</p>
    <p>Front controller + MVC na dziewięciu klasach silnika, encje zamiast tablic, zero zewnętrznych zależności.</p>
    <p><a href="/items" class="btn">Zobacz przykładowy CRUD &rarr;</a></p>
</div>

<?php require BASE_PATH . '/templates/_partials/_footer.php'; ?>
