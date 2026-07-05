<?php
declare(strict_types=1);

namespace App\Controller;

use Engine\Controller;
use Engine\Request;
use Engine\Response;
use App\Repository\UserRepository;
use App\Security\Csrf;

/**
 * Host self-service account: register/login/logout. Deliberately minimal for
 * now — no email verification or password reset (per explicit request,
 * "na razie bez maili"). Separate from the admin panel's own AuthController
 * and session keys ($_SESSION['user_id'], not 'admin_id').
 */
final class AuthController extends Controller
{
    public function register(Request $request, array $params = []): Response
    {
        if (!empty($_SESSION['user_id'])) {
            return $this->redirect('/new-property');
        }

        $errors = [];
        $name   = '';
        $email  = '';

        if ($request->isPost()) {
            if (!Csrf::verify((string)$request->post('csrf_token'))) {
                $errors['form'] = 'Your session expired, please try again.';
            } else {
                $name      = trim((string)$request->post('name'));
                $email     = trim((string)$request->post('email'));
                $password  = (string)$request->post('password');
                $password2 = (string)$request->post('password2');

                $repo = new UserRepository($this->db());

                if ($name === '') {
                    $errors['name'] = 'Please tell us your name.';
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = 'Please enter a valid email address.';
                } elseif ($repo->findByEmail($email) !== null) {
                    $errors['email'] = 'An account with this email already exists.';
                }
                if (strlen($password) < 8) {
                    $errors['password'] = 'Password must be at least 8 characters.';
                } elseif ($password !== $password2) {
                    $errors['password'] = 'Passwords do not match.';
                }

                if (empty($errors)) {
                    $userId = $repo->register($email, $name, $password);

                    session_regenerate_id(true);
                    $_SESSION['user_id']   = $userId;
                    $_SESSION['user_name'] = $name;

                    return $this->redirect('/new-property');
                }
            }
        }

        return $this->render('templates/auth/register', [
            'errors'    => $errors,
            'name'      => $name,
            'email'     => $email,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function login(Request $request, array $params = []): Response
    {
        if (!empty($_SESSION['user_id'])) {
            return $this->redirect('/new-property');
        }

        $error = null;
        $email = '';

        if ($request->isPost()) {
            if (!Csrf::verify((string)$request->post('csrf_token'))) {
                $error = 'Your session expired, please try again.';
            } else {
                $email    = trim((string)$request->post('email'));
                $password = (string)$request->post('password');

                $user = (new UserRepository($this->db()))->verifyPassword($email, $password);

                if ($user === null) {
                    $error = 'Invalid email or password.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id']   = (int)$user['id'];
                    $_SESSION['user_name'] = $user['name'];

                    return $this->redirect('/new-property');
                }
            }
        }

        return $this->render('templates/auth/login', [
            'error'     => $error,
            'email'     => $email,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function logout(Request $request, array $params = []): Response
    {
        unset($_SESSION['user_id'], $_SESSION['user_name']);
        return $this->redirect('/');
    }
}
