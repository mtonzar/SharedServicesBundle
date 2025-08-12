<?php

namespace mtonzar\SharedServicesBundle\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;

#[ApiResource(
    operations: [new GetCollection()],
    paginationEnabled: false
)]
class HealthCheck
{
    private string $status = 'healthy';

    private \DateTimeImmutable $timestamp;

    private array $check = [];

    public function __construct()
    {
        $this->timestamp = new \DateTimeImmutable();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function getCheck(): array
    {
        return $this->check;
    }

    public function addCheck(string $service, string $status, string $message): void
    {
        $this->check[$service] = [
            'status' => $status,
            'message' => $message,
        ];
    }
}
