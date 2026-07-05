<?php require __DIR__ . '/../_header.php'; ?>

<h1 style="margin-bottom:14px;">Edit property</h1>

<p style="color:#888; margin-bottom:16px;">
    Client: <?= htmlspecialchars($property['client_name'] ?? '') ?>
    <?php if (!empty($property['client_email'])): ?>
    &middot; <?= htmlspecialchars($property['client_email']) ?>
    <?php endif; ?>
</p>

<?php if ($error): ?>
<div class="error" style="background:#fdecea; color:#b3261e; padding:10px 14px; border-radius:3px; margin-bottom:16px;">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form method="post" action="/admin/properties/<?= (int)$property['id'] ?>/edit" enctype="multipart/form-data" style="max-width:640px;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <h2 class="form-section-title">Property basics</h2>
    <div class="form-row">
        <label>Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($property['title']) ?>" required>
    </div>
    <div class="form-row">
        <label>Property type</label>
        <select name="property_type_id" required>
            <option value="">Select a type&hellip;</option>
            <?php foreach ($types as $type): ?>
            <option value="<?= (int)$type['id'] ?>" <?= (int)$type['id'] === (int)$property['property_type_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($type['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-row">
        <label>Privacy</label>
        <select name="privacy_level" required>
            <option value="0" <?= (int)$property['privacy_level'] === 0 ? 'selected' : '' ?>>Entire place</option>
            <option value="1" <?= (int)$property['privacy_level'] === 1 ? 'selected' : '' ?>>Private room</option>
            <option value="2" <?= (int)$property['privacy_level'] === 2 ? 'selected' : '' ?>>Shared room</option>
        </select>
    </div>
    <div class="form-row">
        <label>Status</label>
        <select name="status" required>
            <option value="draft" <?= $property['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="published" <?= $property['status'] === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="archived" <?= $property['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
        </select>
    </div>
    <div class="form-row form-row-inline">
        <div>
            <label>Max guests</label>
            <input type="number" name="max_guest" min="1" value="<?= (int)$property['max_guest'] ?>" required>
        </div>
        <div>
            <label>Bedrooms</label>
            <input type="number" name="bedrooms" min="0" value="<?= (int)$property['bedrooms'] ?>" required>
        </div>
        <div>
            <label>Bathrooms</label>
            <input type="number" name="bathrooms" min="0" value="<?= (int)$property['bathrooms'] ?>" required>
        </div>
    </div>

    <h2 class="form-section-title">Location</h2>
    <div class="form-row">
        <label>Country</label>
        <input type="text" name="country" value="<?= htmlspecialchars($property['country'] ?? '') ?>">
    </div>
    <div class="form-row">
        <label>Province / region</label>
        <input type="text" name="province" value="<?= htmlspecialchars($property['province'] ?? '') ?>">
    </div>
    <div class="form-row">
        <label>City</label>
        <input type="text" name="city" value="<?= htmlspecialchars($property['city'] ?? '') ?>">
    </div>
    <div class="form-row">
        <label>Street</label>
        <input type="text" name="street" value="<?= htmlspecialchars($property['street'] ?? '') ?>">
    </div>
    <div class="form-row form-row-inline">
        <div>
            <label>Postal code</label>
            <input type="text" name="postal_code" value="<?= htmlspecialchars($property['postal_code'] ?? '') ?>">
        </div>
        <div>
            <label>Latitude</label>
            <input type="text" name="latitude" value="<?= htmlspecialchars((string)($property['latitude'] ?? '')) ?>">
        </div>
        <div>
            <label>Longitude</label>
            <input type="text" name="longitude" value="<?= htmlspecialchars((string)($property['longitude'] ?? '')) ?>">
        </div>
    </div>

    <h2 class="form-section-title">Description</h2>
    <div class="form-row">
        <label>Short description</label>
        <input type="text" name="short_description" maxlength="255" value="<?= htmlspecialchars($property['short_description'] ?? '') ?>">
    </div>
    <div class="form-row">
        <label>Full description</label>
        <textarea name="description" rows="5"><?= htmlspecialchars($property['description'] ?? '') ?></textarea>
    </div>

    <h2 class="form-section-title">Features</h2>
    <?php foreach ($byCategory as $category => $items): ?>
    <p class="feature-category-label"><?= htmlspecialchars($categoryLabels[$category] ?? $category) ?></p>
    <div class="feature-grid">
        <?php foreach ($items as $feature): ?>
        <label class="feature-checkbox">
            <input type="checkbox" name="features[]" value="<?= (int)$feature['id'] ?>"
                   <?= in_array((int)$feature['id'], $selectedFeatureIds, true) ? 'checked' : '' ?>>
            <?= htmlspecialchars($feature['name']) ?>
        </label>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <h2 class="form-section-title">Photos</h2>

    <?php if (!empty($pictures)): ?>
    <div class="photo-grid">
        <?php foreach ($pictures as $picture): ?>
        <div class="photo-item">
            <img src="<?= htmlspecialchars($picture['path']) ?>" alt="">
            <?php if ($picture['is_cover']): ?><span class="photo-cover-badge">Cover</span><?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="form-row">
        <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp">
        <p class="form-hint">Adds more photos without removing existing ones. JPG, PNG or WEBP, up to 8MB each.</p>
    </div>

    <button type="submit" class="btn btn-primary" style="margin-top:10px;">Save changes</button>
</form>

<?php if (!empty($pictures)): ?>
<h2 class="form-section-title" style="margin-top:30px;">Remove a photo</h2>
<div class="photo-grid">
    <?php foreach ($pictures as $picture): ?>
    <div class="photo-item">
        <img src="<?= htmlspecialchars($picture['path']) ?>" alt="">
        <form method="post" action="/admin/properties/<?= (int)$property['id'] ?>/photos/delete">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="picture_id" value="<?= (int)$picture['id'] ?>">
            <button type="submit" class="btn" style="width:100%; margin-top:4px;">Delete</button>
        </form>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_footer.php'; ?>
