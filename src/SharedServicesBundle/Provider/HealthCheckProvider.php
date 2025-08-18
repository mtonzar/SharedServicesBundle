<?php
namespace mtonzar\SharedServicesBundle\Provider;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use mtonzar\SharedServicesBundle\Entity\HealthCheck;
use mtonzar\SharedServicesBundle\Service\HealthChecker\DatabaseHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\ApiDependencyHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\LivenessHealthChecker;

class HealthCheckProvider implements ProviderInterface
{
    private array $services;

    public function __construct(
        private ?DatabaseHealthChecker $databaseChecker = null,
        private ?ApiDependencyHealthChecker $apiDependencyChecker = null,
        private ?LivenessHealthChecker $livenessChecker = null,
        array $services = []
    ) {
        $this->services = $services;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $healthCheck = new HealthCheck();

        // Ancien mode
        if ($this->databaseChecker) {
            $healthCheck->addCheck('database', ...array_values($this->databaseChecker->check()));
        }
        if ($this->apiDependencyChecker) {
            $healthCheck->addCheck('external_apis', ...array_values($this->apiDependencyChecker->check()));
        }
        if ($this->livenessChecker) {
            $healthCheck->addCheck('liveness', ...array_values($this->livenessChecker->check()));
        }

        foreach ($this->services as $name => $url) {
            $status = $this->pingService($url) ? 'healthy' : 'down';
            $healthCheck->addCheck($name, $status, "Ping $url returned $status");
        }

        return [$healthCheck];
    }

    private function pingService(string $url): bool
    {
        try {
            $context = stream_context_create(['http' => ['timeout' => 2]]);
            $result = @file_get_contents($url, false, $context);
            return $result !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
