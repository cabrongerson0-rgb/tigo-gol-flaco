<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\{Response, Storage, Logger};
use App\Service\{PaymentService, CaptchaService, TigoInvoiceService};
use App\Repository\PaymentRepository;
use App\Validator\DocumentValidator;
use App\Exception\ValidationException;

class PaymentController extends BaseController
{
    public function index(): Response
    {
        return $this->render('payment/index', [
            'title' => 'Pagar facturas - Tigo',
            'documentTypes' => $this->getDocumentTypes(),
        ]);
    }

    public function validate(): Response
    {
        try {
            $data = $_POST;
            $config = $this->container->get('config');
            $storage = $this->container->get(Storage::class);
            $logger = $this->container->get(Logger::class);

            $captchaService = new CaptchaService($config['security']['recaptcha']['secret_key'], $logger);
            
            if (!$captchaService->verifyFromRequest($data)) {
                return $this->json(['success' => false, 'errors' => ['captcha' => 'Verificación fallida']], 422);
            }

            // Store data in session for invoices page
            $_SESSION['search_type'] = $data['type'] ?? 'documento';
            $_SESSION['search_identifier'] = $this->getIdentifierFromData($data);

            return $this->json([
                'success' => true,
                'data' => [
                    'redirect_url' => '/payment/invoices',
                ],
            ]);

        } catch (ValidationException $e) {
            return $this->json(['success' => false, 'errors' => $e->getErrors()], 422);
        } catch (\Exception $e) {
            $this->container->get(Logger::class)->error('Payment error: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => 'Error al procesar el pago'], 500);
        }
    }

    public function invoices(): Response
    {
        try {
            $type = $_SESSION['search_type'] ?? 'documento';
            $identifier = $_SESSION['search_identifier'] ?? '';

            if (empty($identifier)) {
                return $this->redirect('/');
            }

            // Query Tigo API for invoice data
            $invoices = $this->queryTigoInvoices($identifier, $type);

            return $this->render('payment/invoices', [
                'title' => 'Facturas - Tigo',
                'type' => $type,
                'identifier' => $identifier,
                'invoices' => $invoices,
            ]);
        } catch (\Exception $e) {
            $this->container->get(Logger::class)->error('Invoices error: ' . $e->getMessage());
            
            // Si no se pueden cargar las facturas, mostrar página de error amigable
            return $this->render('payment/invoices', [
                'title' => 'Facturas - Tigo',
                'type' => $type ?? 'documento',
                'identifier' => $identifier ?? '',
                'invoices' => [],
                'error' => 'No se pudieron cargar las facturas. Por favor intente nuevamente.',
            ]);
        }
    }

    public function methods(): Response
    {
        $invoiceId = $_GET['invoice_id'] ?? '';
        $amount = (int)($_GET['amount'] ?? 0);
        
        if (empty($invoiceId)) {
            header('Location: /payment/invoices');
            exit;
        }
        
        // Si el monto es 0, intentar recuperarlo de sesión
        if ($amount === 0 && isset($_SESSION['last_invoice_amount'])) {
            $amount = (int)$_SESSION['last_invoice_amount'];
        }

        // Store invoice ID and amount in session
        $_SESSION['current_invoice_id'] = $invoiceId;
        $_SESSION['current_invoice_amount'] = $amount;

        return $this->render('payment/methods', [
            'title' => 'Métodos de pago - Tigo',
            'invoice_id' => $invoiceId,
            'amount' => $amount > 0 ? $amount : 83973, // Fallback si no hay monto
        ]);
    }

    public function checkout(): Response
    {
        $method = $_GET['method'] ?? '';
        $invoiceId = $_GET['invoice_id'] ?? '';

        if (empty($method) || empty($invoiceId)) {
            header('Location: /payment/invoices');
            exit;
        }

        // Store payment method in session
        $_SESSION['payment_method'] = $method;
        $_SESSION['current_invoice_id'] = $invoiceId;

        // Redirect to PSE form for pse method
        if ($method === 'pse') {
            header('Location: /pse/form?invoice_id=' . $invoiceId);
            exit;
        }

        // For other methods, redirect to PSE (will be customized later)
        header('Location: /pse');
        exit;
    }

    public function process(): Response
    {
        $transactionId = $_SESSION['transaction_id'] ?? null;

        if (!$transactionId) {
            return $this->json(['success' => false, 'error' => 'No se encontró transacción'], 400);
        }

        return $this->json(['success' => true, 'message' => 'Pago procesado correctamente']);
    }

    private function getDocumentTypes(): array
    {
        return array_map(
            fn($code) => ['code' => $code, 'name' => DocumentValidator::getTypeName($code)],
            DocumentValidator::getValidTypes()
        );
    }

    private function getIdentifierFromData(array $data): string
    {
        $type = $data['type'] ?? 'documento';

        switch ($type) {
            case 'linea':
                return $data['phoneNumber'] ?? '';
            case 'hogar':
                return $data['contractNumber'] ?? '';
            case 'documento':
            default:
                return $data['documentNumber'] ?? '';
        }
    }

    private function getMockInvoices(string $identifier, string $type): array
    {
        // This is mock data - will be replaced with actual API call
        $lastFour = substr($identifier, -4);
        $masked = str_repeat('*', max(0, strlen($identifier) - 4)) . $lastFour;

        return [
            [
                'id' => uniqid('inv_'),
                'masked_number' => $masked,
                'amount' => 83973,
                'due_date' => 'Pago Inmediato',
                'is_immediate' => true,
                'partial_payment_available' => true,
            ],
        ];
    }

    private function getInvoiceById(string $invoiceId): array
    {
        // This is mock data - will be replaced with actual API call
        return [
            'id' => $invoiceId,
            'amount' => 83973,
            'due_date' => 'Pago Inmediato',
            'is_immediate' => true,
        ];
    }

    /**
     * Query Tigo API for invoices
     */
    private function queryTigoInvoices(string $identifier, string $type): array
    {
        $config = $this->container->get('config');
        $logger = $this->container->get(Logger::class);
        
        $tigoService = new TigoInvoiceService($logger);

        // Call API based on search type
        if ($type === 'linea') {
            // Search by phone number
            $logger->info("Consultando API Tigo por teléfono: {$identifier}");
            $result = $tigoService->getInvoiceByPhone($identifier);
        } else {
            // Search by document (documento or hogar)
            $logger->info("Consultando API Tigo por documento: {$identifier}");
            $result = $tigoService->getInvoiceByDocument($identifier);
        }

        if (!$result['success']) {
            $error = $result['error'] ?? 'Unknown error';
            $logger->error("Tigo API Error: {$error}");
            throw new \Exception("Error consultando factura: {$error}");
        }

        // Transform API response to expected format
        $lastFour = substr($identifier, -4);
        $masked = str_repeat('*', max(0, strlen($identifier) - 4)) . $lastFour;

        $amount = $result['amount'] ?? 0;
        $logger->info("API Tigo respondió exitosamente, monto: {$amount}");
        
        // Guardar en sesión para uso posterior
        $_SESSION['last_invoice_amount'] = $amount;

        return [
            [
                'id' => uniqid('inv_'),
                'masked_number' => $masked,
                'amount' => $amount,
                'due_date' => $result['due_date'] ?? 'Pago Inmediato',
                'is_immediate' => true,
                'partial_payment_available' => true,
                'raw_data' => $result
            ],
        ];
    }
}
