<?php
// src/Entity/HealthCheck.php
namespace mtonzar\SharedServicesBundle\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use mtonzar\SharedServicesBundle\Provider\HealthCheckProvider;

#[ApiResource(
    operations: [
        new GetCollection(provider: HealthCheckProvider::class)
    ],
    normalizationContext: ['groups' => ['health:read']]
)]
class HealthCheck
{
    private string $id = 'current';

    /**
     * @Groups({"health:read"})
     */
    private string $status;

    /**
     * @Groups({"health:read"})
     */
    private array $checks = [];

    /**
     * @Groups({"health:read"})
     */
    private \DateTimeImmutable $timestamp;

    public function __construct(string $status = 'healthy')
    {
        $this->status = $status;
        $this->timestamp = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getChecks(): array
    {
        return $this->checks;
    }

    public function addCheck(string $name, string $status, array $details = []): self
    {
        $this->checks[$name] = [
            'status' => $status,
            'details' => $details
        ];

        // Si un check est dégradé ou down, le statut global est affecté
        if ($status === 'degraded' && $this->status === 'healthy') {
            $this->status = 'degraded';
        } elseif ($status === 'down') {
            $this->status = 'down';
        }

        return $this;
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }
}
