<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\{Response, Storage, Logger};
use App\Service\PseService;
use App\Repository\{PseTransactionRepository, PaymentRepository};
use App\Exception\ValidationException;

class PseController extends BaseController
{
    public function index(): Response
    {
        // Obtener invoice_id de la URL o sesión
        $invoiceId = $_GET['invoice_id'] ?? $_SESSION['current_invoice_id'] ?? null;

        if (!$invoiceId) {
            return $this->redirect('/payment/invoices');
        }

        // Guardar en sesión para uso posterior
        $_SESSION['current_invoice_id'] = $invoiceId;

        return $this->render('pse/index', [
            'title' => 'PSE - Pago Seguro en Línea',
            'invoice_id' => $invoiceId,
        ]);
    }

    public function form(): Response
    {
        $invoiceId = $_GET['invoice_id'] ?? $_SESSION['current_invoice_id'] ?? '';
        
        if (empty($invoiceId)) {
            header('Location: /payment/invoices');
            exit;
        }

        return $this->render('pse/form', [
            'title' => 'Pago con cuenta de ahorros - Tigo',
            'invoice_id' => $invoiceId,
        ]);
    }

    public function initiate(): Response
    {
        try {
            $invoiceId = $_SESSION['current_invoice_id'] ?? null;

            if (!$invoiceId) {
                return $this->json(['success' => false, 'error' => 'No se encontró información de factura'], 400);
            }

            $pseService = $this->getPseService();
            $transaction = $pseService->initiateTransaction((int) $paymentId, $_POST);

            return $this->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->getId(),
                    'banks' => $pseService->getAvailableBanks(),
                ],
            ]);

        } catch (ValidationException $e) {
            return $this->json(['success' => false, 'errors' => $e->getErrors()], 422);
        } catch (\Exception $e) {
            $this->container->get(Logger::class)->error('PSE initiation error: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => 'Error al iniciar pago PSE'], 500);
        }
    }

    public function callback(): Response
    {
        try {
            $result = $this->getPseService()->handleCallback($_GET);
            $_SESSION['pse_result'] = $result;

            return $this->redirect('/pse/confirmation');

        } catch (\Exception $e) {
            $this->container->get(Logger::class)->error('PSE callback error: ' . $e->getMessage());
            return $this->redirect('/error');
        }
    }

    public function confirmation(): Response
    {
        $result = $_SESSION['pse_result'] ?? null;

        if (!$result) {
            return $this->redirect('/payment');
        }

        unset($_SESSION['payment_id'], $_SESSION['transaction_id'], $_SESSION['pse_result']);

        return $this->render('pse/confirmation', [
            'title' => 'Confirmación de Pago',
            'status' => $result['status'],
            'transaction_id' => $result['transaction_id'],
        ]);
    }

    private function getPseService(): PseService
    {
        $storage = $this->container->get(Storage::class);
        return new PseService(
            new PseTransactionRepository($storage),
            new PaymentRepository($storage),
            $this->container->get(Logger::class),
            $this->container->get('config')
        );
    }
}
