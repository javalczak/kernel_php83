<?php
declare(strict_types=1);
/** @var array $item */
/** @var \Engine\Translation\Translator $translator */
$pageTitle = $translator->trans('example.show.title', ['title' => $item['title']]);
require BASE_PATH . '/templates/_partials/_header.php';
?>

<p style="margin-bottom:12px;"><a href="/items" class="btn btn-secondary">&larr; Back</a></p>
<h1><?= htmlspecialchars($item['title']) ?></h1>
<p style="color:#888; font-size:13px; margin-top:6px;">Created: <?= htmlspecialchars($item['created_at']) ?></p>

<?php require BASE_PATH . '/templates/_partials/_footer.php'; ?>
