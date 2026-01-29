<?php

declare(strict_types=1);

namespace App\Model;

class Payment
{
    private ?int $id = null;
    private string $transactionId;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $processedAt = null;
    private ?string $contractNumber = null;
    private ?string $phoneNumber = null;
    private ?string $pseReference = null;

    public function __construct(
        private string $type,
        private string $documentType,
        private string $documentNumber,
        private float $amount,
        private string $status = 'pending'
    ) {
        $this->transactionId = 'TG' . date('YmdHis') . bin2hex(random_bytes(4));
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getType(): string { return $this->type; }
    public function getDocumentType(): string { return $this->documentType; }
    public function getDocumentNumber(): string { return $this->documentNumber; }
    public function getContractNumber(): ?string { return $this->contractNumber; }
    public function getPhoneNumber(): ?string { return $this->phoneNumber; }
    public function getAmount(): float { return $this->amount; }
    public function getStatus(): string { return $this->status; }
    public function getTransactionId(): string { return $this->transactionId; }
    public function getPseReference(): ?string { return $this->pseReference; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getProcessedAt(): ?\DateTimeImmutable { return $this->processedAt; }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setContractNumber(?string $contractNumber): self
    {
        $this->contractNumber = $contractNumber;
        return $this;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setPseReference(?string $reference): self
    {
        $this->pseReference = $reference;
        return $this;
    }

    public function setProcessedAt(\DateTimeImmutable $processedAt): self
    {
        $this->processedAt = $processedAt;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'document_type' => $this->documentType,
            'document_number' => $this->documentNumber,
            'contract_number' => $this->contractNumber,
            'phone_number' => $this->phoneNumber,
            'amount' => $this->amount,
            'status' => $this->status,
            'transaction_id' => $this->transactionId,
            'pse_reference' => $this->pseReference,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'processed_at' => $this->processedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public static function fromArray(array $data): self
    {
        $payment = new self(
            $data['type'],
            $data['document_type'],
            $data['document_number'],
            (float) $data['amount'],
            $data['status']
        );

        if (isset($data['id'])) {
            $payment->setId((int) $data['id']);
        }

        if (isset($data['contract_number'])) {
            $payment->setContractNumber($data['contract_number']);
        }

        if (isset($data['phone_number'])) {
            $payment->setPhoneNumber($data['phone_number']);
        }

        if (isset($data['pse_reference'])) {
            $payment->setPseReference($data['pse_reference']);
        }

        return $payment;
    }
}
