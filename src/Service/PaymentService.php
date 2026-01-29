<?php

declare(strict_types=1);

namespace App\Service;

use App\{Model\Payment, Repository\PaymentRepository, Validator\PaymentValidator, Core\Logger};

class PaymentService
{
    public function __construct(
        private PaymentRepository $paymentRepository,
        private Logger $logger
    ) {}

    public function processPaymentRequest(array $data): Payment
    {
        $validatedData = PaymentValidator::validatePaymentRequest($data);
        
        $payment = new Payment(
            $validatedData['type'],
            $validatedData['documentType'] ?? '',
            $validatedData['documentNumber'] ?? '',
            $this->calculateAmount($validatedData)
        );

        match($validatedData['type']) {
            'hogar' => $payment->setContractNumber($validatedData['contractNumber']),
            'linea' => $payment->setPhoneNumber($validatedData['phoneNumber']),
            default => null
        };

        $this->paymentRepository->save($payment);
        $this->logger->info('Payment created', [
            'transaction_id' => $payment->getTransactionId(),
            'type' => $payment->getType()
        ]);

        return $payment;
    }

    private function calculateAmount(array $data): float
    {
        return match($data['type']) {
            'documento' => 150000.00,
            'hogar' => 85000.00,
            'linea' => 45000.00,
            default => 0.00
        };
    }

    public function getPaymentByTransactionId(string $transactionId): ?Payment
    {
        return ($data = $this->paymentRepository->findByTransactionId($transactionId))
            ? $this->hydratePayment($data)
            : null;
    }

    public function updatePaymentStatus(int $paymentId, string $status): bool
    {
        $success = $this->paymentRepository->updateStatus($paymentId, $status);
        
        if ($success) {
            $this->logger->info('Payment status updated', ['payment_id' => $paymentId, 'status' => $status]);
        }

        return $success;
    }

    public function getPaymentHistory(string $documentType, string $documentNumber): array
    {
        return $this->paymentRepository->findByDocument($documentType, $documentNumber);
    }

    public function checkPendingPayment(string $type, string $identifier): ?array
    {
        return $this->paymentRepository->findPending($type, $identifier);
    }

    private function hydratePayment(array $data): Payment
    {
        $payment = new Payment(
            $data['type'],
            $data['document_type'],
            $data['document_number'],
            (float) $data['amount'],
            $data['status']
        );

        $payment->setId((int) $data['id']);
        
        $data['contract_number'] && $payment->setContractNumber($data['contract_number']);
        $data['phone_number'] && $payment->setPhoneNumber($data['phone_number']);
        $data['pse_reference'] && $payment->setPseReference($data['pse_reference']);
        $data['processed_at'] && $payment->setProcessedAt(new \DateTimeImmutable($data['processed_at']));

        return $payment;
    }
}
