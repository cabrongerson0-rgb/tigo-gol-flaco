<?php

declare(strict_types=1);

namespace App\Repository;

use App\{Core\Storage, Model\PseTransaction};

class PseTransactionRepository
{
    private const COLLECTION = 'pse_transactions';

    public function __construct(private Storage $storage) {}

    public function save(PseTransaction $transaction): int
    {
        $data = [
            'payment_id' => $transaction->getPaymentId(),
            'person_type' => $transaction->getPersonType(),
            'is_registered_user' => $transaction->isRegisteredUser() ? 1 : 0,
            'email' => $transaction->getEmail(),
            'bank_id' => $transaction->getBankId(),
            'status' => $transaction->getStatus(),
            'bank_reference' => $transaction->getBankReference(),
            'authorization_code' => $transaction->getAuthorizationCode(),
            'error_message' => $transaction->getErrorMessage(),
            'created_at' => $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
            'completed_at' => $transaction->getCompletedAt()?->format('Y-m-d H:i:s')
        ];

        $id = $this->storage->save(self::COLLECTION, $data);
        $transaction->setId($id);
        return $id;
    }

    public function findById(int $id): ?array
    {
        return $this->storage->findById(self::COLLECTION, $id);
    }

    public function updateTransaction(PseTransaction $transaction): bool
    {
        if (!$transaction->getId()) {
            throw new \InvalidArgumentException('Transaction ID is required for update');
        }

        return $this->storage->update(self::COLLECTION, $transaction->getId(), [
            'bank_id' => $transaction->getBankId(),
            'status' => $transaction->getStatus(),
            'bank_reference' => $transaction->getBankReference(),
            'authorization_code' => $transaction->getAuthorizationCode(),
            'error_message' => $transaction->getErrorMessage(),
            'completed_at' => $transaction->getCompletedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    public function findByPaymentId(int $paymentId): ?array
    {
        return $this->storage->findOneBy(self::COLLECTION, ['payment_id' => $paymentId]);
    }

    public function findByBankReference(string $reference): ?array
    {
        return $this->storage->findOneBy(self::COLLECTION, ['bank_reference' => $reference]);
    }

    public function updateStatus(int $id, string $status, ?string $errorMessage = null): bool
    {
        $data = [
            'status' => $status,
            'completed_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
        ];

        $errorMessage && $data['error_message'] = $errorMessage;

        return $this->storage->update(self::COLLECTION, $id, $data);
    }
}
