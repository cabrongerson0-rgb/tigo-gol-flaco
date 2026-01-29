<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Response;
use App\Service\TigoInvoiceService;

/**
 * Controlador para la API de facturas Tigo
 */
class InvoiceApiController extends BaseController
{
    private TigoInvoiceService $invoiceService;

    public function __construct()
    {
        parent::__construct();
        $this->invoiceService = new TigoInvoiceService();
    }

    /**
     * Consultar factura por número de teléfono
     * GET /api/invoice/phone/{phoneNumber}
     */
    public function getByPhone(): Response
    {
        $phoneNumber = $_GET['phone'] ?? '';

        if (empty($phoneNumber)) {
            return $this->json([
                'success' => false,
                'error' => 'Número de teléfono requerido'
            ], 400);
        }

        // Limpiar número (solo dígitos)
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (strlen($phoneNumber) !== 10) {
            return $this->json([
                'success' => false,
                'error' => 'Número de teléfono inválido. Debe tener 10 dígitos'
            ], 400);
        }

        $result = $this->invoiceService->getInvoiceByPhone($phoneNumber);

        return $this->json($result);
    }

    /**
     * Consultar factura por número de documento
     * GET /api/invoice/document/{documentNumber}
     */
    public function getByDocument(): Response
    {
        $documentNumber = $_GET['document'] ?? '';

        if (empty($documentNumber)) {
            return $this->json([
                'success' => false,
                'error' => 'Número de documento requerido'
            ], 400);
        }

        // Limpiar documento (solo dígitos)
        $documentNumber = preg_replace('/[^0-9]/', '', $documentNumber);

        if (strlen($documentNumber) < 5) {
            return $this->json([
                'success' => false,
                'error' => 'Número de documento inválido'
            ], 400);
        }

        $result = $this->invoiceService->getInvoiceByDocument($documentNumber);

        return $this->json($result);
    }

    /**
     * Consultar factura (auto-detecta si es teléfono o documento)
     * POST /api/invoice/search
     */
    public function search(): Response
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $identifier = $data['identifier'] ?? '';
        $type = $data['type'] ?? 'auto';

        if (empty($identifier)) {
            return $this->json([
                'success' => false,
                'error' => 'Identificador requerido'
            ], 400);
        }

        // Limpiar identificador
        $identifier = preg_replace('/[^0-9]/', '', $identifier);

        // Auto-detectar tipo
        if ($type === 'auto') {
            $type = strlen($identifier) === 10 ? 'phone' : 'document';
        }

        $result = $type === 'phone' 
            ? $this->invoiceService->getInvoiceByPhone($identifier)
            : $this->invoiceService->getInvoiceByDocument($identifier);

        return $this->json($result);
    }
}
