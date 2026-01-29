<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\{Container, Response};

abstract class BaseController
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function render(string $view, array $data = []): Response
    {
        $viewPath = $this->container->get('config')['paths']['templates'] . "/$view.php";

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: $view");
        }

        // Extract all data for use in both view and layout
        foreach ($data as $key => $value) {
            $$key = $value;
        }
        
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        return Response::html($content);
    }

    protected function json(array $data, int $statusCode = 200): Response
    {
        return Response::json($data, $statusCode);
    }

    protected function redirect(string $url, int $statusCode = 302): Response
    {
        return Response::redirect($url, $statusCode);
    }

    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
