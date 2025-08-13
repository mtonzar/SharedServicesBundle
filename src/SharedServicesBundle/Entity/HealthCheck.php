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
    private string $timestamp;

    public function __construct()
    {
        // Stocke l'heure au moment de la création de l'objet
        $this->timestamp = (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM);
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
        return $this->timestamp;
    }
}
