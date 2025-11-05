<?php

namespace Mtonzar\SharedServicesBundle\Message;

class OrderShipped
{
    public function __construct(
        private int $orderId,
        private int $userId,
        private string $trackingNumber,
        private \DateTimeImmutable $shippedAt
    ) {}

    public function getOrderId(): int { return $this->orderId; }
    public function getUserId(): int { return $this->userId; }
    public function getTrackingNumber(): string { return $this->trackingNumber; }
    public function getShippedAt(): \DateTimeImmutable { return $this->shippedAt; }
}