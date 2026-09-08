<?php
/** @var string $pageTitle */
/** @var \Engine\Translation\Translator $translator */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'My App') ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Verdana, sans-serif; font-size: 14px; color: #2c2c2c; background: #f5f5f5; }
        nav { background: #fff; border-bottom: 1px solid #e0e0e0; padding: 0 24px; display: flex; align-items: center; gap: 20px; height: 48px; }
        nav a { color: #444; text-decoration: none; font-size: 14px; }
        nav a:hover { color: #0066cc; }
        main { max-width: 900px; margin: 32px auto; padding: 0 24px; }
        h1 { font-size: 22px; margin-bottom: 20px; }
        .error { background: #fff1f1; border: 1px solid #f3b3b3; color: #b42318; padding: 8px 12px; border-radius: 4px; margin-bottom: 12px; font-size: 13px; }
        label { display: block; margin-bottom: 4px; color: #666; font-size: 13px; }
        input[type=text] { width: 100%; padding: 8px 10px; border: 1px solid #d0d0d0; border-radius: 4px; font-size: 14px; }
        input[type=text]:focus { outline: none; border-color: #0066cc; }
        .btn { display: inline-block; padding: 8px 18px; background: #0066cc; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; text-decoration: none; }
        .btn:hover { background: #0055aa; }
        .btn-secondary { background: #e0e0e0; color: #444; }
        .btn-secondary:hover { background: #d0d0d0; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        table th, table td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #ececec; }
        table th { color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: normal; }
        .form-group { margin-bottom: 16px; }
    </style>
</head>
<body>
<nav>
    <a href="/">My App</a>
    <a href="/items">Items</a>
</nav>
<main>
