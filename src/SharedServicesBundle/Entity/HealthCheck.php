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
    private array $checks = [];
    private \DateTimeImmutable $checkedAt;

    public function __construct()
    {
        $this->checkedAt = new \DateTimeImmutable();
    }

    public function addCheck(string $service, string $status, string $details): void
    {
        $this->checks[$service] = [
            'status' => $status,
            'details' => $details
        ];
    }

    public function getChecks(): array
    {
        return $this->checks;
    }

    public function getTimestamp(): string
    {
        return $this->checkedAt->format('c');
    }
}
