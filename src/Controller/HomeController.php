<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Response;

class HomeController extends BaseController
{
    public function index(): Response
    {
        return $this->redirect('/payment');
    }
}
