<?php
// src/SharedServicesBundle/Message/Event/OrderCreated.php
namespace mtonzar\SharedServicesBundle\Message\Event;

class OrderCreated implements EventInterface
{
    private string $orderId;
    private string $productId;
    private \DateTimeImmutable $occurredAt;

    public function __construct(string $orderId, string $productId)
    {
        $this->orderId = $orderId;
        $this->productId = $productId;
        $this->occurredAt = new \DateTimeImmutable;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getProduct(): string
    {
        return $this->productId;
    }

    public function getType(): string
    {
        return 'order.created';
    }

    public function getPayload(): array
    {
        return [
            'order_id' => $this->orderId,
            'product' => $this->productId
        ];
    }

    public function getOccurredAt(): ?\DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
