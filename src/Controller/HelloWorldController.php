<?php
declare(strict_types=1);

namespace App\Controller;

use Engine\Controller;
use Engine\Request;
use Engine\Response;

/** Najprostszy możliwy kontroler: jedna akcja, jeden szablon, zero zależności. */
final class HelloWorldController extends Controller
{
    public function index(Request $request, array $params = []): Response
    {
        // Nazwa szablonu względem templates/, bez .php
        return $this->render('home');
    }
}
