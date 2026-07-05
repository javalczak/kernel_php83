<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Admin Login &mdash; Fiestalettings</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: Verdana, Geneva, sans-serif;
        font-size: 13px;
        background: #f7f7f7;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-box {
        width: 320px;
        background: #fff;
        border: 1px solid #e2e2e2;
        border-radius: 4px;
        padding: 28px 26px;
    }

    .login-box h1 {
        font-size: 16px;
        color: #ff7a00;
        margin-bottom: 18px;
        text-align: center;
    }

    .field { margin-bottom: 14px; }

    .field label {
        display: block;
        margin-bottom: 4px;
        color: #777;
    }

    .field input {
        width: 100%;
        padding: 7px 9px;
        border: 1px solid #d8d8d8;
        border-radius: 3px;
        font-family: inherit;
        font-size: 13px;
    }

    .error {
        background: #fdecea;
        color: #b3261e;
        padding: 8px 10px;
        border-radius: 3px;
        margin-bottom: 14px;
    }

    button {
        width: 100%;
        padding: 9px;
        background: #ff7a00;
        border: none;
        border-radius: 3px;
        color: #fff;
        font-size: 13px;
        font-family: inherit;
        cursor: pointer;
    }

    button:hover { background: #e86d00; }
</style>
</head>
<body>
    <div class="login-box">
        <h1>FIESTALETTINGS ADMIN</h1>

        <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/login" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Sign in</button>
        </form>
    </div>
</body>
</html>
