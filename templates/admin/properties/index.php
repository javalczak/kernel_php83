<?php require __DIR__ . '/../_header.php'; ?>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
    <h1>Properties</h1>
    <a href="/admin/properties/new" class="btn btn-primary">Add property</a>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>Title</th>
            <th>Client</th>
            <th>Type</th>
            <th>Status</th>
            <th>Preview link</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($properties)): ?>
        <tr><td colspan="6" style="color:#999;">No properties yet.</td></tr>
        <?php endif; ?>

        <?php foreach ($properties as $property): ?>
        <?php $previewUrl = $baseUrl . '/preview/' . $property['preview_token']; ?>
        <tr>
            <td><?= htmlspecialchars($property['title']) ?></td>
            <td>
                <?= htmlspecialchars($property['client_name']) ?>
                <br><span style="color:#999; font-size:11px;"><?= htmlspecialchars($property['client_email']) ?></span>
            </td>
            <td><?= htmlspecialchars($property['type_name']) ?></td>
            <td>
                <span class="badge badge-off"><?= htmlspecialchars(strtoupper($property['status'])) ?></span>
            </td>
            <td>
                <input type="text" readonly value="<?= htmlspecialchars($previewUrl) ?>"
                       onclick="this.select();"
                       style="width:220px; font-size:11px; padding:4px 6px; border:1px solid #d8d8d8; border-radius:3px;">
                <a href="<?= htmlspecialchars($previewUrl) ?>" target="_blank" class="btn" style="margin-left:4px;">Open</a>
            </td>
            <td>
                <a href="/admin/properties/<?= (int)$property['id'] ?>/edit" class="btn">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../_footer.php'; ?>
