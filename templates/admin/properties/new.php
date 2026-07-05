<?php require __DIR__ . '/../_header.php'; ?>

<h1 style="margin-bottom:14px;">Add a property for a client</h1>

<?php if ($error): ?>
<div class="error" style="background:#fdecea; color:#b3261e; padding:10px 14px; border-radius:3px; margin-bottom:16px;">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form method="post" action="/admin/properties" enctype="multipart/form-data" style="max-width:640px;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <h2 class="form-section-title">Client</h2>
    <div class="form-row">
        <label>Client name</label>
        <input type="text" name="client_name" required>
    </div>
    <div class="form-row">
        <label>Client email</label>
        <input type="email" name="client_email" required>
        <p class="form-hint">A user account is created for this email if one doesn't exist yet.</p>
    </div>

    <h2 class="form-section-title">Property basics</h2>
    <div class="form-row">
        <label>Title</label>
        <input type="text" name="title" required>
    </div>
    <div class="form-row">
        <label>Property type</label>
        <select name="property_type_id" required>
            <option value="">Select a type&hellip;</option>
            <?php foreach ($types as $type): ?>
            <option value="<?= (int)$type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-row">
        <label>Privacy</label>
        <select name="privacy_level" required>
            <option value="0">Entire place</option>
            <option value="1">Private room</option>
            <option value="2">Shared room</option>
        </select>
    </div>
    <div class="form-row form-row-inline">
        <div>
            <label>Max guests</label>
            <input type="number" name="max_guest" min="1" value="2" required>
        </div>
        <div>
            <label>Bedrooms</label>
            <input type="number" name="bedrooms" min="0" value="1" required>
        </div>
        <div>
            <label>Bathrooms</label>
            <input type="number" name="bathrooms" min="0" value="1" required>
        </div>
    </div>

    <h2 class="form-section-title">Location</h2>
    <div class="form-row">
        <label>Country</label>
        <input type="text" name="country">
    </div>
    <div class="form-row">
        <label>Province / region</label>
        <input type="text" name="province">
    </div>
    <div class="form-row">
        <label>City</label>
        <input type="text" name="city">
    </div>
    <div class="form-row">
        <label>Street</label>
        <input type="text" name="street">
    </div>
    <div class="form-row form-row-inline">
        <div>
            <label>Postal code</label>
            <input type="text" name="postal_code">
        </div>
        <div>
            <label>Latitude</label>
            <input type="text" name="latitude">
        </div>
        <div>
            <label>Longitude</label>
            <input type="text" name="longitude">
        </div>
    </div>

    <h2 class="form-section-title">Description</h2>
    <div class="form-row">
        <label>Short description</label>
        <input type="text" name="short_description" maxlength="255">
    </div>
    <div class="form-row">
        <label>Full description</label>
        <textarea name="description" rows="5"></textarea>
    </div>

    <h2 class="form-section-title">Features</h2>
    <?php foreach ($byCategory as $category => $items): ?>
    <p class="feature-category-label"><?= htmlspecialchars($categoryLabels[$category] ?? $category) ?></p>
    <div class="feature-grid">
        <?php foreach ($items as $feature): ?>
        <label class="feature-checkbox">
            <input type="checkbox" name="features[]" value="<?= (int)$feature['id'] ?>">
            <?= htmlspecialchars($feature['name']) ?>
        </label>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <h2 class="form-section-title">Photos</h2>
    <div class="form-row">
        <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp">
        <p class="form-hint">JPG, PNG or WEBP, up to 8MB each. The first photo becomes the cover.</p>
    </div>

    <button type="submit" class="btn btn-primary" style="margin-top:10px;">Create property</button>
</form>

<?php require __DIR__ . '/../_footer.php'; ?>
