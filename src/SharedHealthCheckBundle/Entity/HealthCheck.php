<?php
// src/Entity/HealthCheck.php
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
}
