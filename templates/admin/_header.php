<?php
/** @var string $activeNav */
$activeNav = $activeNav ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Fiestalettings Admin</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: Verdana, Geneva, sans-serif;
        font-size: 13px;
        color: #2c2c2c;
        background: #f7f7f7;
    }

    #admin-shell {
        display: flex;
        min-height: 100vh;
    }

    #admin-sidebar {
        width: 200px;
        flex-shrink: 0;
        background: #ffffff;
        border-right: 1px solid #e2e2e2;
        transition: margin-left 0.15s ease;
    }

    body.sidebar-collapsed #admin-sidebar {
        margin-left: -200px;
    }

    #sidebar-brand {
        padding: 16px 18px;
        font-size: 14px;
        font-weight: bold;
        color: #ff7a00;
        border-bottom: 1px solid #e2e2e2;
    }

    #sidebar-nav {
        list-style: none;
        padding: 10px 0;
    }

    #sidebar-nav li a {
        display: block;
        padding: 9px 18px;
        color: #444;
        text-decoration: none;
    }

    #sidebar-nav li a:hover {
        background: #f5f5f5;
    }

    #sidebar-nav li a.active {
        background: #fff3e8;
        color: #ff7a00;
        border-right: 2px solid #ff7a00;
    }

    #admin-main {
        flex: 1;
        min-width: 0;
    }

    #admin-topbar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 18px;
        background: #ffffff;
        border-bottom: 1px solid #e2e2e2;
    }

    #sidebar-toggle {
        background: none;
        border: 1px solid #d8d8d8;
        border-radius: 3px;
        width: 26px;
        height: 26px;
        cursor: pointer;
        font-size: 13px;
        color: #555;
    }

    #admin-topbar .topbar-user {
        margin-left: auto;
        color: #777;
    }

    #admin-content {
        padding: 20px;
    }

    table.admin-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
    }

    table.admin-table th,
    table.admin-table td {
        text-align: left;
        padding: 8px 10px;
        border-bottom: 1px solid #ececec;
    }

    table.admin-table th {
        color: #888;
        font-weight: normal;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
    }

    .badge-on  { background: #e6f6ea; color: #2a8a4a; }
    .badge-off { background: #f1f1f1; color: #888; }

    tr.children-row td {
        background: #fafafa;
        color: #888;
        font-size: 12px;
        padding: 6px 10px 10px 26px;
        border-bottom: 1px solid #ececec;
    }

    .form-section-title {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #ff7a00;
        margin: 22px 0 10px;
        padding-bottom: 6px;
        border-bottom: 1px solid #ececec;
    }

    .form-row {
        margin-bottom: 12px;
    }

    .form-row label {
        display: block;
        margin-bottom: 4px;
        color: #777;
    }

    .form-row input[type=text],
    .form-row input[type=email],
    .form-row input[type=number],
    .form-row select,
    .form-row textarea {
        width: 100%;
        padding: 7px 9px;
        border: 1px solid #d8d8d8;
        border-radius: 3px;
        font-family: inherit;
        font-size: 13px;
    }

    .form-hint {
        color: #999;
        font-size: 11px;
        margin-top: 4px;
    }

    .form-row-inline {
        display: flex;
        gap: 12px;
    }

    .form-row-inline > div {
        flex: 1;
    }

    .feature-category-label {
        color: #888;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 12px 0 6px;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
        margin-bottom: 6px;
    }

    .feature-checkbox {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #444;
    }

    .photo-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-bottom: 10px;
    }

    .photo-item {
        position: relative;
    }

    .photo-item img {
        width: 100%;
        height: 90px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #ececec;
    }

    .photo-cover-badge {
        position: absolute;
        top: 4px;
        left: 4px;
        background: #ff7a00;
        color: #fff;
        font-size: 10px;
        padding: 1px 6px;
        border-radius: 8px;
    }

    .btn {
        display: inline-block;
        padding: 5px 12px;
        border: 1px solid #d8d8d8;
        border-radius: 3px;
        background: #fff;
        color: #444;
        cursor: pointer;
        font-size: 12px;
        font-family: inherit;
    }

    .btn:hover { background: #f5f5f5; }

    .btn-primary {
        background: #ff7a00;
        border-color: #ff7a00;
        color: #fff;
    }

    .btn-primary:hover { background: #e86d00; }

    .breadcrumb {
        margin-bottom: 14px;
        color: #888;
    }

    .breadcrumb a { color: #ff7a00; text-decoration: none; }

    .add-form {
        margin-top: 18px;
        padding: 14px;
        background: #fff;
        border: 1px solid #ececec;
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .add-form input[type=text] {
        padding: 6px 8px;
        border: 1px solid #d8d8d8;
        border-radius: 3px;
        font-family: inherit;
        font-size: 13px;
    }

    .add-form select {
        padding: 6px 8px;
        border: 1px solid #d8d8d8;
        border-radius: 3px;
        font-family: inherit;
        font-size: 13px;
    }
</style>
</head>
<body>
<div id="admin-shell">
    <div id="admin-sidebar">
        <div id="sidebar-brand">FIESTALETTINGS</div>
        <ul id="sidebar-nav">
            <li><a href="/admin" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="/admin/locations" class="<?= $activeNav === 'locations' ? 'active' : '' ?>">Locations</a></li>
            <li><a href="/admin/features" class="<?= $activeNav === 'features' ? 'active' : '' ?>">Features</a></li>
            <li><a href="/admin/activities" class="<?= $activeNav === 'activities' ? 'active' : '' ?>">Activities</a></li>
            <li><a href="/admin/property-types" class="<?= $activeNav === 'property-types' ? 'active' : '' ?>">Property types</a></li>
            <li><a href="/admin/properties" class="<?= $activeNav === 'properties' ? 'active' : '' ?>">Properties</a></li>
            <li><a href="/admin/coupons" class="<?= $activeNav === 'coupons' ? 'active' : '' ?>">Coupons</a></li>
            <li><a href="/admin/content-pages" class="<?= $activeNav === 'content-pages' ? 'active' : '' ?>">Content pages</a></li>
            <li><a href="/admin/hosts" class="<?= $activeNav === 'hosts' ? 'active' : '' ?>">Hosts</a></li>
        </ul>
    </div>
    <div id="admin-main">
        <div id="admin-topbar">
            <button id="sidebar-toggle" type="button">&#9776;</button>
            <div class="topbar-user">
                <?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?>
                &middot; <a href="/admin/logout">Log out</a>
            </div>
        </div>
        <div id="admin-content">
