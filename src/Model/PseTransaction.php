<?php

declare(strict_types=1);

namespace App\Model;

class PseTransaction
{
    private ?int $id = null;
    private ?int $bankId = null;
    private string $status = 'pending';
    private ?string $bankReference = null;
    private ?string $authorizationCode = null;
    private ?string $errorMessage = null;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $completedAt = null;

    public function __construct(
        private int $paymentId,
        private string $personType,
        private bool $isRegisteredUser,
        private string $email
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getPaymentId(): int { return $this->paymentId; }
    public function getPersonType(): string { return $this->personType; }
    public function isRegisteredUser(): bool { return $this->isRegisteredUser; }
    public function getEmail(): string { return $this->email; }
    public function getBankId(): ?int { return $this->bankId; }
    public function getStatus(): string { return $this->status; }
    public function getBankReference(): ?string { return $this->bankReference; }
    public function getAuthorizationCode(): ?string { return $this->authorizationCode; }
    public function getErrorMessage(): ?string { return $this->errorMessage; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setBankId(?int $bankId): self
    {
        $this->bankId = $bankId;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setBankReference(?string $reference): self
    {
        $this->bankReference = $reference;
        return $this;
    }

    public function setAuthorizationCode(?string $code): self
    {
        $this->authorizationCode = $code;
        return $this;
    }

    public function setErrorMessage(?string $message): self
    {
        $this->errorMessage = $message;
        return $this;
    }

    public function setCompletedAt(\DateTimeImmutable $completedAt): self
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'payment_id' => $this->paymentId,
            'person_type' => $this->personType,
            'is_registered_user' => $this->isRegisteredUser,
            'email' => $this->email,
            'bank_id' => $this->bankId,
            'status' => $this->status,
            'bank_reference' => $this->bankReference,
            'authorization_code' => $this->authorizationCode,
            'error_message' => $this->errorMessage,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public static function fromArray(array $data): self
    {
        $transaction = new self(
            (int) $data['payment_id'],
            $data['person_type'],
            (bool) $data['is_registered_user'],
            $data['email']
        );

        if (isset($data['id'])) {
            $transaction->setId((int) $data['id']);
        }

        if (isset($data['bank_id'])) {
            $transaction->setBankId((int) $data['bank_id']);
        }

        if (isset($data['status'])) {
            $transaction->setStatus($data['status']);
        }

        if (isset($data['bank_reference'])) {
            $transaction->setBankReference($data['bank_reference']);
        }

        if (isset($data['authorization_code'])) {
            $transaction->setAuthorizationCode($data['authorization_code']);
        }

        if (isset($data['error_message'])) {
            $transaction->setErrorMessage($data['error_message']);
        }

        return $transaction;
    }
}
