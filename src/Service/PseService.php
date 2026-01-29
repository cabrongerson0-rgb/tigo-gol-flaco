<?php

declare(strict_types=1);

namespace App\Service;

use App\{Model\PseTransaction, Repository\PseTransactionRepository, Repository\PaymentRepository, Validator\PseValidator, Core\Logger};

class PseService
{
    public function __construct(
        private PseTransactionRepository $pseRepository,
        private PaymentRepository $paymentRepository,
        private Logger $logger,
        private array $config
    ) {}

    public function initiateTransaction(int $paymentId, array $data): PseTransaction
    {
        $validatedData = PseValidator::validatePseRequest($data);

        $transaction = new PseTransaction(
            $paymentId,
            $validatedData['personType'],
            (bool) $validatedData['isRegisteredUser'],
            $validatedData['email']
        );

        $this->pseRepository->save($transaction);
        $this->logger->info('PSE transaction initiated', [
            'transaction_id' => $transaction->getId(),
            'payment_id' => $paymentId,
            'email' => $validatedData['email']
        ]);

        return $transaction;
    }

    public function processWithBank(PseTransaction $transaction, int $bankId): array
    {
        PseValidator::validateBankSelection($bankId);
        $transaction->setBankId($bankId);
        $redirectUrl = $this->generateBankRedirectUrl($transaction);
        $this->pseRepository->updateTransaction($transaction);

        $this->logger->info('PSE payment redirecting to bank', [
            'transaction_id' => $transaction->getId(),
            'bank_id' => $bankId
        ]);

        return [
            'redirect_url' => $redirectUrl,
            'transaction_id' => $transaction->getId()
        ];
    }

    private function generateBankRedirectUrl(PseTransaction $transaction): string
    {
        $params = http_build_query([
            'merchantId' => $this->config['pse']['merchant_id'],
            'transactionId' => $transaction->getId(),
            'bankId' => $transaction->getBankId(),
            'amount' => $this->getPaymentAmount($transaction->getPaymentId()),
            'returnUrl' => $this->config['app']['url'] . '/pse/callback'
        ]);

        return $this->config['pse']['api_url'] . '/payment?' . $params;
    }

    private function getPaymentAmount(int $paymentId): float
    {
        return ($payment = $this->paymentRepository->findById($paymentId))
            ? (float) $payment['amount']
            : 0.00;
    }

    public function handleCallback(array $data): array
    {
        $transactionId = (int) ($data['transactionId'] ?? 0);
        $status = $data['status'] ?? 'failed';
        $bankReference = $data['bankReference'] ?? null;
        $authCode = $data['authorizationCode'] ?? null;

        if (!$transactionId) {
            throw new \InvalidArgumentException('Transaction ID is required');
        }

        $transactionData = $this->pseRepository->findById($transactionId);
        if (!$transactionData) {
            throw new \RuntimeException('Transaction not found');
        }

        $status === 'approved'
            ? $this->approveTransaction($transactionId, $bankReference, $authCode)
            : $this->rejectTransaction($transactionId, $data['errorMessage'] ?? 'Payment declined');

        $this->logger->info('PSE callback processed', [
            'transaction_id' => $transactionId,
            'status' => $status
        ]);

        return [
            'transaction_id' => $transactionId,
            'status' => $status,
            'payment_id' => $transactionData['payment_id']
        ];
    }

    private function approveTransaction(int $transactionId, ?string $bankReference, ?string $authCode): void
    {
        $transactionData = $this->pseRepository->findById($transactionId);
        if (!$transactionData) return;

        $this->pseRepository->updateStatus($transactionId, 'approved');
        $this->paymentRepository->updateStatus((int) $transactionData['payment_id'], 'completed');

        if ($payment = $this->paymentRepository->findById((int) $transactionData['payment_id'])) {
            $this->paymentRepository->update((int) $payment['id'], ['pse_reference' => $bankReference]);
        }
    }

    private function rejectTransaction(int $transactionId, string $errorMessage): void
    {
        $transactionData = $this->pseRepository->findById($transactionId);
        if (!$transactionData) return;

        $this->pseRepository->updateStatus($transactionId, 'failed', $errorMessage);
        $this->paymentRepository->updateStatus((int) $transactionData['payment_id'], 'failed');
    }
}
