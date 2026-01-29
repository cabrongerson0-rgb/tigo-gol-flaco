<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\{Response, Logger};
use App\Validator\DocumentValidator;
use App\Service\CaptchaService;

class ApiController extends BaseController
{
    public function validateDocument(): Response
    {
        $type = $_POST['type'] ?? '';
        $number = $_POST['number'] ?? '';
        $isValid = DocumentValidator::validate($type, $number);

        return $this->json([
            'valid' => $isValid,
            'message' => $isValid ? 'Documento válido' : 'Documento inválido',
        ]);
    }

    public function validateCaptcha(): Response
    {
        $config = $this->container->get('config');
        $captchaService = new CaptchaService(
            $config['security']['recaptcha']['secret_key'],
            $this->container->get(Logger::class)
        );

        $isValid = $captchaService->verifyFromRequest($_POST);

        return $this->json([
            'valid' => $isValid,
            'message' => $isValid ? 'Captcha válido' : 'Captcha inválido',
        ]);
    }

    public function getBanks(): Response
    {
        return $this->json([
            'success' => true,
            'banks' => [
                ['code' => '1001', 'name' => 'Banco de Bogotá'],
                ['code' => '1002', 'name' => 'Banco Popular'],
                ['code' => '1003', 'name' => 'Bancolombia'],
                ['code' => '1004', 'name' => 'Davivienda'],
                ['code' => '1005', 'name' => 'Banco de Occidente'],
                ['code' => '1006', 'name' => 'BBVA Colombia'],
                ['code' => '1007', 'name' => 'Banco Agrario'],
                ['code' => '1008', 'name' => 'Banco AV Villas'],
            ],
        ]);
    }
}
