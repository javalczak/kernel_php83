<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Engine\Controller;
use Engine\Response;

abstract class AdminController extends Controller
{
    protected function ensureAuthenticated(): ?Response
    {
        if (empty($_SESSION['admin_id'])) {
            return $this->redirect('/admin/login');
        }

        return null;
    }
}
