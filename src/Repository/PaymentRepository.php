<?php

declare(strict_types=1);

namespace App\Repository;

use App\{Core\Storage, Model\Payment};

class PaymentRepository
{
    private const COLLECTION = 'payments';

    public function __construct(private Storage $storage) {}

    public function save(Payment $payment): int
    {
        $data = [
            'type' => $payment->getType(),
            'document_type' => $payment->getDocumentType(),
            'document_number' => $payment->getDocumentNumber(),
            'contract_number' => $payment->getContractNumber(),
            'phone_number' => $payment->getPhoneNumber(),
            'amount' => $payment->getAmount(),
            'status' => $payment->getStatus(),
            'transaction_id' => $payment->getTransactionId(),
            'pse_reference' => $payment->getPseReference(),
            'created_at' => $payment->getCreatedAt()->format('Y-m-d H:i:s'),
            'processed_at' => $payment->getProcessedAt()?->format('Y-m-d H:i:s')
        ];

        $id = $this->storage->save(self::COLLECTION, $data);
        $payment->setId($id);
        return $id;
    }

    public function findById(int $id): ?array
    {
        return $this->storage->findById(self::COLLECTION, $id);
    }

    public function updatePayment(Payment $payment): bool
    {
        if (!$payment->getId()) {
            throw new \InvalidArgumentException('Payment ID is required for update');
        }

        return $this->storage->update(self::COLLECTION, $payment->getId(), [
            'amount' => $payment->getAmount(),
            'status' => $payment->getStatus(),
            'pse_reference' => $payment->getPseReference(),
            'processed_at' => $payment->getProcessedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    public function findByTransactionId(string $transactionId): ?array
    {
        return $this->storage->findOneBy(self::COLLECTION, ['transaction_id' => $transactionId]);
    }

    public function findByDocument(string $documentType, string $documentNumber): array
    {
        return $this->storage->findBy(self::COLLECTION, [
            'document_type' => $documentType,
            'document_number' => $documentNumber
        ]);
    }

    public function findPending(string $type, string $identifier): ?array
    {
        $column = match($type) {
            'documento' => 'document_number',
            'hogar' => 'contract_number',
            'linea' => 'phone_number',
            default => throw new \InvalidArgumentException("Invalid type: $type")
        };

        foreach ($this->storage->findBy(self::COLLECTION, ['status' => 'pending']) as $item) {
            if (isset($item[$column]) && $item[$column] === $identifier) {
                return $item;
            }
        }

        return null;
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->storage->update(self::COLLECTION, $id, [
            'status' => $status,
            'processed_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
        ]);
    }

    public function update(int $id, array $data): bool
    {
        return $this->storage->update(self::COLLECTION, $id, $data);
    }

    public function findAll(): array
    {
        return $this->storage->getAll(self::COLLECTION);
    }

    public function count(): int
    {
        return $this->storage->count(self::COLLECTION);
    }
}
