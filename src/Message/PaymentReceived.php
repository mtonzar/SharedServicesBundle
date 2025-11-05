<?php

namespace Mtonzar\SharedServicesBundle\Message;

class PaymentReceived
{
    public function __construct(
        private int $orderId,
        private int $userId,
        private string $paymentMethod,
        private float $amount,
        private \DateTimeImmutable $paidAt
    ) {}

    public function getOrderId(): int { return $this->orderId; }
    public function getUserId(): int { return $this->userId; }
    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function getAmount(): float { return $this->amount; }
    public function getPaidAt(): \DateTimeImmutable { return $this->paidAt; }
}
