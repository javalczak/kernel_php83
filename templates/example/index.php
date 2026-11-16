<?php
declare(strict_types=1);
/** @var list<\App\Entity\Example> $items */
/** @var string $szukaj */
/** @var string $csrf */
/** @var \Engine\Translation\Translator $translator */
$pageTitle = $translator->trans('example.index.title');
require BASE_PATH . '/templates/_partials/_header.php';
?>

<h1><?= htmlspecialchars($pageTitle) ?></h1>

<form method="get" action="/items" class="szukajka">
    <input type="text" name="q" value="<?= htmlspecialchars($szukaj) ?>" placeholder="<?= htmlspecialchars($translator->trans('example.index.search')) ?>">
    <button type="submit" class="btn btn-secondary">&#128269;</button>
    <a href="/items/create" class="btn"><?= htmlspecialchars($translator->trans('example.index.new')) ?></a>
</form>

<?php if ($items === []): ?>
    <p style="color:#888;"><?= htmlspecialchars($translator->trans('example.index.empty')) ?></p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Tytuł</th>
                <th><?= htmlspecialchars($translator->trans('example.show.created')) ?></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <?php /* $item to obiekt Example, więc -> zamiast ['...'] */ ?>
                <td><?= htmlspecialchars($item->title) ?></td>
                <td style="color:#888;"><?= htmlspecialchars((string) $item->createdAt) ?></td>
                <td><a href="/items/<?= (int) $item->id ?>">Podgląd &rarr;</a></td>
                <td>
                    <form method="post" action="/items/<?= (int) $item->id ?>/delete" onsubmit="return confirm('Usunąć?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <button type="submit" class="btn-link"><?= htmlspecialchars($translator->trans('example.delete')) ?></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require BASE_PATH . '/templates/_partials/_footer.php'; ?>
