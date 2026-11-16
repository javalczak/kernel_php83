<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\ExampleRepository;
use App\Security\Csrf;
use App\Service\ExampleService;
use Engine\Controller;
use Engine\Request;
use Engine\Response;

/**
 * Pełny przykład CRUD na encjach: lista, podgląd, dodawanie, usuwanie, szukanie.
 * Kontroler zajmuje się tylko HTTP — walidacja siedzi w ExampleService.
 */
final class ExampleController extends Controller
{
    public function index(Request $request, array $params = []): Response
    {
        $szukaj = $request->string('q');

        // findAll i searchByTitle oddają obiekty Example, nie tablice
        $items = $szukaj === ''
            ? $this->repository()->findAll('created_at DESC')
            : $this->repository()->searchByTitle($szukaj);

        return $this->render('example/index', [
            'items'  => $items,
            'szukaj' => $szukaj,
            'csrf'   => Csrf::token(),
        ]);
    }

    public function show(Request $request, array $params = []): Response
    {
        $item = $this->repository()->findById((int) $params['id']);

        if ($item === null) {
            return Response::notFound($this->trans('example.errors.not_found'));
        }

        return $this->render('example/show', ['item' => $item]);
    }

    public function create(Request $request, array $params = []): Response
    {
        $errors = [];
        $title  = '';

        if ($request->isPost()) {
            $title = $request->string('title');

            if (!Csrf::verify($request->string('csrf_token'))) {
                // Wygasła sesja wygląda jak atak CSRF i odwrotnie — komunikat ten sam.
                $errors['form'] = $this->trans('example.errors.csrf');
            } else {
                try {
                    $this->service()->create($title);

                    return $this->redirect('/items');
                } catch (\InvalidArgumentException $e) {
                    $errors['title'] = $e->getMessage();
                }
            }
        }

        return $this->render('example/create', [
            'errors'    => $errors,
            'title'     => $title, // przy błędzie wracamy z tym, co użytkownik już wpisał
            'csrfToken' => Csrf::token(),
        ]);
    }

    /** Usuwanie tylko POST-em: GET-a wywoła każdy podgląd linku albo robot. */
    public function delete(Request $request, array $params = []): Response
    {
        if (!Csrf::verify($request->string('csrf_token'))) {
            return (new Response())->setStatusCode(403)->setContent($this->trans('example.errors.csrf'));
        }

        $item = $this->repository()->findById((int) $params['id']);

        if ($item === null) {
            return Response::notFound($this->trans('example.errors.not_found'));
        }

        $this->repository()->delete($item);

        return $this->redirect('/items');
    }

    private function repository(): ExampleRepository
    {
        return new ExampleRepository($this->db());
    }

    private function service(): ExampleService
    {
        return new ExampleService($this->repository(), $this->db());
    }
}
