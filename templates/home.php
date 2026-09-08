<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Kernel</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Verdana, sans-serif; background: #f5f5f5; color: #2c2c2c; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 48px 56px; max-width: 520px; width: 100%; }
        .badge { display: inline-block; background: #e8f0fe; color: #1a56cc; font-size: 11px; font-weight: bold; letter-spacing: 0.8px; text-transform: uppercase; padding: 3px 10px; border-radius: 10px; margin-bottom: 20px; }
        h1 { font-size: 28px; font-weight: bold; margin-bottom: 20px; }
        p { color: #555; line-height: 1.8; font-size: 14px; margin-bottom: 14px; }
        p:last-child { margin-bottom: 0; }
    </style>
</head>
<body>
<div class="card">
    <div class="badge">Pure Kernel</div>
    <h1>Działa.</h1>
    <p>Autorski micro-framework PHP 8.3 zaprojektowany pod wdrożenie na shared hostingu przez FTP — bez Composera na serwerze, bez SSH, bez Dockera.</p>
    <p>Implementuje wzorzec front controller + MVC w oparciu o ~7 klas silnika. Zero zewnętrznych zależności.</p>
</div>
</body>
</html>
