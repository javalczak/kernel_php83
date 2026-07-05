<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Engine\Request;
use Engine\Response;
use App\Repository\AdminRepository;
use App\Repository\AdminLoginAttemptRepository;
use App\Security\Csrf;

final class AuthController extends AdminController
{
    private const int MAX_FAILED_ATTEMPTS = 5;
    private const int LOCKOUT_WINDOW_MINUTES = 15;

    // Valid bcrypt hash of a fixed dummy value — used so an unknown email still
    // pays the cost of a password_verify() call, keeping login timing consistent
    // regardless of whether the email exists.
    private const string DUMMY_HASH = '$2y$12$nsZRhjER9KttsdHoMTu/4.7a27LagZWqOKwv.qXqpca1lg8yTXcbG';

    public function login(Request $request, array $params = []): Response
    {
        if (!empty($_SESSION['admin_id'])) {
            return $this->redirect('/admin');
        }

        $error = null;

        if ($request->isPost()) {
            $error = $this->handleLogin($request);

            if ($error === null) {
                return $this->redirect('/admin');
            }
        }

        return $this->render('templates/admin/login', [
            'error'     => $error,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function logout(Request $request, array $params = []): Response
    {
        $_SESSION = [];
        session_destroy();

        return $this->redirect('/admin/login');
    }

    private function handleLogin(Request $request): ?string
    {
        if (!Csrf::verify((string)$request->post('csrf_token'))) {
            return 'Your session expired. Please try again.';
        }

        $email    = trim((string)$request->post('email'));
        $password = (string)$request->post('password');
        $ip       = (string)($request->server['REMOTE_ADDR'] ?? '0.0.0.0');

        if ($email === '' || $password === '') {
            return 'Invalid email or password.';
        }

        $attempts = new AdminLoginAttemptRepository($this->db());

        if ($attempts->countRecentFailures($email, $ip, self::LOCKOUT_WINDOW_MINUTES) >= self::MAX_FAILED_ATTEMPTS) {
            return 'Too many failed attempts. Please try again later.';
        }

        $admins = new AdminRepository($this->db());
        $admin  = $admins->findByEmail($email);

        $passwordOk = $admin !== null
            ? $admins->verifyPassword($admin, $password)
            : password_verify($password, self::DUMMY_HASH);

        if ($admin === null || !$passwordOk || !$admin['is_active']) {
            $attempts->record($email, $ip, false);
            return 'Invalid email or password.';
        }

        $attempts->record($email, $ip, true);

        session_regenerate_id(true);
        $_SESSION['admin_id']    = (int)$admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name']  = $admin['name'];

        $admins->touchLastLogin((int)$admin['id']);

        return null;
    }
}
