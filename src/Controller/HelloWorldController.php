<?php
declare(strict_types=1);

namespace App\Controller;

use Engine\Controller;
use Engine\Request;
use Engine\Response;

final class HelloWorldController extends Controller
{
    public function index(Request $request, array $params = []): Response
    {
        return (new Response())->setContent('Hello World');
    }
}
