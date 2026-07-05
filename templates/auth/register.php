<?php
declare(strict_types=1);
/** @var \Engine\Translation\Translator $translator */
$pageTitle  = 'Fiestalettings — Register';
$texturedBg = true;
require __DIR__ . '/../site/_header.php';
?>
<style>
    #auth-page {
        font-family: 'Source Serif 4', serif;
        max-width: 1030px;
        width: 100%;
        margin: 0 auto;
        background-color: white;
        padding: 20px;
        box-sizing: border-box;
    }
    #auth-page .page-header { font-size: 30px; color: #585858; padding-bottom: 4px; border-bottom: 1px dotted #c8c8c8; margin-bottom: 10px; }
    #auth-page .page-subheader { color: #424242; font-size: 18px; padding-left: 20px; margin-bottom: 10px; }
    #auth-page .content { font-family: 'Quicksand', sans-serif; padding: 20px 40px; max-width: 460px; }
    #auth-page .field-error {
        margin: 0 40px 16px;
        padding: 8px 10px;
        border-radius: 6px;
        background: #fff1f1;
        color: #b42318;
        font-size: 13px;
        border: 1px solid #f3b3b3;
        font-family: 'Quicksand', sans-serif;
    }
    .form-field { margin-bottom: 16px; }
    .form-field label { display: block; font-size: 13px; color: #777; margin-bottom: 5px; font-family: 'Quicksand', sans-serif; }
    .form-field input {
        width: 100%;
        height: 42px;
        padding: 0 14px;
        font-family: 'Quicksand', sans-serif;
        font-size: 14px;
        color: #5e5e5e;
        border: 1px solid #ebebeb;
        border-radius: 6px;
        background-color: #fafafa;
        outline: none;
        box-sizing: border-box;
    }
    .form-field input:focus { border-color: #fd8635; }
    .form-field .field-hint { font-size: 12px; color: #b42318; margin-top: 4px; }
    #auth-submit-row { margin-top: 24px; display: flex; justify-content: space-between; align-items: center; font-family: 'Quicksand', sans-serif; padding: 0 40px; }
    #auth-submit-row a { color: #878787; text-decoration: none; font-size: 14px; }
    #auth-submit-row a:hover { color: #fd8635; }
    #auth-submit-row button {
        font-family: 'Quicksand', sans-serif;
        color: white; font-size: 18px;
        padding: 8px 30px; background-color: #fd8635; border: 1px solid #fd7322;
        border-radius: 3px; cursor: pointer;
    }
    #auth-submit-row button:hover { background-color: #fd7322; }
</style>

<div id="auth-page">
    <div class="page-header">Create your host account</div>
    <div class="page-subheader">Register and start listing your property — no commission, ever</div>

    <?php if (!empty($errors['form'])): ?>
    <div class="field-error"><?= htmlspecialchars($errors['form']) ?></div>
    <?php endif; ?>

    <form method="post" action="/register">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="content">
            <div class="form-field">
                <label for="name">Your name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>">
                <?php if (!empty($errors['name'])): ?><div class="field-hint"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
            </div>
            <div class="form-field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
                <?php if (!empty($errors['email'])): ?><div class="field-hint"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="form-field">
                <label for="password">Password (at least 8 characters)</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="form-field">
                <label for="password2">Confirm password</label>
                <input type="password" id="password2" name="password2">
                <?php if (!empty($errors['password'])): ?><div class="field-hint"><?= htmlspecialchars($errors['password']) ?></div><?php endif; ?>
            </div>
        </div>

        <div id="auth-submit-row">
            <a href="/login">Already have an account? Log in</a>
            <button type="submit">Get started &raquo;</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../site/_footer.php'; ?>
