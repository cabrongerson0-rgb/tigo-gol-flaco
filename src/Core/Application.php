<?php

declare(strict_types=1);

namespace App\Core;

use App\Exception\{NotFoundException, HttpException};

class Application
{
    private Router $router;
    private Container $container;
    private Logger $logger;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->router = new Router();
        $this->container = new Container();
        $this->bootstrap();
    }

    private function bootstrap(): void
    {
        date_default_timezone_set($this->config['app']['timezone']);
        $this->setupErrorHandling();
        $this->registerServices();
        $this->router->loadRoutes($this->config['paths']['root'] . '/config/routes.php');
        $this->startSession();
    }

    private function setupErrorHandling(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', (string)($this->config['app']['debug'] ? 1 : 0));
        ini_set('log_errors', '1');
        ini_set('error_log', $this->config['paths']['root'] . '/logs/error.log');

        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                $this->handleException(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
            }
        });
    }

    private function registerServices(): void
    {
        $storage = Storage::getInstance($this->config['storage']['data_path']);
        $this->logger = Logger::getInstance($this->config['paths']['root'] . '/logs');
        
        $this->container->set('config', fn() => $this->config);
        $this->container->set('router', fn() => $this->router);
        $this->container->set(Storage::class, fn() => $storage);
        $this->container->set(Logger::class, fn() => $this->logger);
        $this->container->set(Response::class, fn() => new Response());
    }

    private function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Strict',
                'gc_maxlifetime' => $this->config['security']['session_lifetime'],
            ]);
        }
    }

    public function run(): void
    {
        try {
            $match = $this->router->match($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
            
            if (!$match) {
                throw new NotFoundException('Page not found');
            }

            $response = $this->dispatch($match['handler'], $match['params']);
            $this->sendResponse($response);

        } catch (HttpException $e) {
            $this->handleHttpException($e);
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    private function dispatch(string $handler, array $params): Response
    {
        [$controllerName, $method] = explode('@', $handler);
        $controllerClass = "App\\Controller\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            throw new NotFoundException("Controller {$controllerClass} not found");
        }

        $controller = new $controllerClass($this->container);

        if (!method_exists($controller, $method)) {
            throw new NotFoundException("Method {$method} not found in {$controllerClass}");
        }

        return $controller->$method(...array_values($params));
    }

    private function sendResponse(Response $response): void
    {
        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $name => $value) {
            header("$name: $value");
        }

        echo $response->getContent();
    }

    private function handleHttpException(HttpException $e): void
    {
        http_response_code($e->getStatusCode());
        
        // Si es una solicitud AJAX o API, devolver JSON
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;
        
        if ($isAjax || $isApi) {
            header('Content-Type: application/json');
            $error = ['error' => $e->getMessage(), 'code' => $e->getStatusCode()];
            
            if ($this->config['app']['debug']) {
                $error['file'] = $e->getFile();
                $error['line'] = $e->getLine();
            }
            
            echo json_encode($error);
        } else {
            // Renderizar página HTML de error
            try {
                $errorController = new \App\Controller\ErrorController($this->container);
                if ($e->getStatusCode() === 404) {
                    $response = $errorController->notFound();
                } else {
                    $response = $errorController->index();
                }
                $this->sendResponse($response);
            } catch (\Throwable $renderError) {
                // Fallback simple si falla el renderizado
                echo "<h1>Error {$e->getStatusCode()}</h1><p>{$e->getMessage()}</p>";
            }
        }
    }

    public function handleException(\Throwable $e): void
    {
        $this->logger->error($e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        http_response_code(500);
        
        $error = $this->config['app']['debug'] 
            ? [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ]
            : ['error' => 'Internal server error'];
        
        echo json_encode($error, $this->config['app']['debug'] ? JSON_PRETTY_PRINT : 0);
    }

    public function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}
