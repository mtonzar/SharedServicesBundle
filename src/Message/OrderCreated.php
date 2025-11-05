<?php

namespace Mtonzar\SharedServicesBundle\Message;

class OrderCreated
{
    public function __construct(
        private int $orderId,
        private int $userId,
        private float $amount,
        private \DateTimeImmutable $createdAt
    ) {}

    public function getOrderId(): int { return $this->orderId; }
    public function getUserId(): int { return $this->userId; }
    public function getAmount(): float { return $this->amount; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}