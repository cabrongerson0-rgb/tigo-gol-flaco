<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Response;

class ErrorController extends BaseController
{
    public function index(): Response
    {
        return $this->render('error/index', [
            'title' => 'Error',
            'message' => 'Ha ocurrido un error. Por favor intente nuevamente.',
        ]);
    }

    public function notFound(): Response
    {
        return $this->render('error/404', [
            'title' => 'Página no encontrada',
            'message' => 'La página que buscas no existe.',
        ]);
    }
}
