<?php require __DIR__ . '/../_header.php'; ?>

<h1 style="margin-bottom:14px;">Property types</h1>

<table class="admin-table" style="margin-bottom:10px;">
    <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($types)): ?>
        <tr><td colspan="4" style="color:#999;">No entries yet.</td></tr>
        <?php endif; ?>

        <?php foreach ($types as $type): ?>
        <tr>
            <td><code><?= htmlspecialchars($type['code']) ?></code></td>
            <td><?= htmlspecialchars($type['name']) ?></td>
            <td>
                <span class="badge <?= $type['is_active'] ? 'badge-on' : 'badge-off' ?>">
                    <?= $type['is_active'] ? 'ON' : 'OFF' ?>
                </span>
            </td>
            <td>
                <form method="post" action="/admin/property-types/toggle" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int)$type['id'] ?>">
                    <button type="submit" class="btn"><?= $type['is_active'] ? 'Turn off' : 'Turn on' ?></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<form method="post" action="/admin/property-types/add" class="add-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="text" name="code" placeholder="code (e.g. villa)" pattern="[a-z0-9_]+" required>
    <input type="text" name="name" placeholder="Display name" required>
    <button type="submit" class="btn btn-primary">Add</button>
</form>

<?php require __DIR__ . '/../_footer.php'; ?>
