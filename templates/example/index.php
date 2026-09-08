<?php
declare(strict_types=1);
/** @var array $items */
/** @var \Engine\Translation\Translator $translator */
$pageTitle = $translator->trans('example.index.title');
require BASE_PATH . '/templates/_partials/_header.php';
?>

<h1><?= htmlspecialchars($pageTitle) ?></h1>
<p style="margin-bottom:16px;"><a href="/items/create" class="btn">+ New item</a></p>

<?php if (empty($items)): ?>
    <p style="color:#888;">No items yet.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['title']) ?></td>
                <td style="color:#888;"><?= htmlspecialchars($item['created_at']) ?></td>
                <td><a href="/items/<?= (int)$item['id'] ?>">View &rarr;</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require BASE_PATH . '/templates/_partials/_footer.php'; ?>
