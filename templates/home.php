<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Kernel</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Verdana, sans-serif; background: #f5f5f5; color: #2c2c2c; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 48px 56px; max-width: 520px; width: 100%; }
        .badge { display: inline-block; background: #e8f0fe; color: #1a56cc; font-size: 11px; font-weight: bold; letter-spacing: 0.8px; text-transform: uppercase; padding: 3px 10px; border-radius: 10px; margin-bottom: 20px; }
        h1 { font-size: 28px; font-weight: bold; margin-bottom: 12px; }
        p { color: #666; line-height: 1.7; margin-bottom: 20px; font-size: 14px; }
        .divider { border: none; border-top: 1px solid #ececec; margin: 24px 0; }
        .links { display: flex; flex-direction: column; gap: 10px; }
        .links a { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 6px; text-decoration: none; color: #2c2c2c; font-size: 13px; transition: border-color 0.15s; }
        .links a:hover { border-color: #0066cc; color: #0066cc; }
        .links a .icon { font-size: 16px; }
        .links a .label { font-weight: bold; }
        .links a .desc { color: #999; font-size: 12px; margin-left: auto; }
    </style>
</head>
<body>
<div class="card">
    <div class="badge">Pure Kernel</div>
    <h1>It works.</h1>
    <p>Zero-dependency PHP 8.3 micro-framework for shared hosting.<br>
       No Composer on the server. No SSH. Just FTP and this.</p>
    <hr class="divider">
    <div class="links">
        <a href="/items">
            <span class="icon">&#9654;</span>
            <span class="label">Example CRUD</span>
            <span class="desc">ExampleController &rarr;</span>
        </a>
        <a href="https://github.com/javalczak/pure-kernel" target="_blank" rel="noopener">
            <span class="icon">&#9679;</span>
            <span class="label">Source on GitHub</span>
            <span class="desc">README &rarr;</span>
        </a>
    </div>
</div>
</body>
</html>
