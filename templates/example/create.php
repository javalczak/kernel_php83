<?php
declare(strict_types=1);
/** @var array $errors */
/** @var string $csrfToken */
/** @var \Engine\Translation\Translator $translator */
$pageTitle = $translator->trans('example.create.title');
require BASE_PATH . '/templates/_partials/_header.php';
?>

<h1><?= htmlspecialchars($pageTitle) ?></h1>

<?php if (!empty($errors['form'])): ?>
    <div class="error"><?= htmlspecialchars($errors['form']) ?></div>
<?php endif; ?>

<form method="post" action="/items/create" style="max-width:480px;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="form-group">
        <label for="title">Title</label>
        <?php if (!empty($errors['title'])): ?>
            <div class="error"><?= htmlspecialchars($errors['title']) ?></div>
        <?php endif; ?>
        <input type="text" id="title" name="title" autofocus>
    </div>

    <div style="display:flex; gap:10px; margin-top:20px;">
        <button type="submit" class="btn"><?= htmlspecialchars($translator->trans('example.create.submit')) ?></button>
        <a href="/items" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require BASE_PATH . '/templates/_partials/_footer.php'; ?>
