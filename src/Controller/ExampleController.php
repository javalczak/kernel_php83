<?php
declare(strict_types=1);

namespace App\Controller;

use Engine\Controller;
use Engine\Request;
use Engine\Response;
use App\Repository\ExampleRepository;
use App\Security\Csrf;

final class ExampleController extends Controller
{
    public function index(Request $request, array $params = []): Response
    {
        $repo  = new ExampleRepository($this->db());
        $items = $repo->findAll();

        return $this->render(BASE_PATH . '/templates/example/index', [
            'items' => $items,
        ]);
    }

    public function show(Request $request, array $params = []): Response
    {
        $repo = new ExampleRepository($this->db());
        $item = $repo->findById((int) $params['id']);

        if ($item === null) {
            return Response::notFound('Item not found.');
        }

        return $this->render(BASE_PATH . '/templates/example/show', [
            'item' => $item,
        ]);
    }

    public function create(Request $request, array $params = []): Response
    {
        $errors = [];

        if ($request->isPost()) {
            if (!Csrf::verify((string) $request->post('csrf_token'))) {
                $errors['form'] = 'Session expired. Please try again.';
            } else {
                $title = trim((string) $request->post('title'));

                if ($title === '') {
                    $errors['title'] = 'Title is required.';
                }

                if (empty($errors)) {
                    $repo = new ExampleRepository($this->db());
                    $repo->create($title);

                    return $this->redirect('/examples');
                }
            }
        }

        return $this->render(BASE_PATH . '/templates/example/create', [
            'errors'    => $errors,
            'csrfToken' => Csrf::token(),
        ]);
    }
}
