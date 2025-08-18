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

        // Database
        if ($this->databaseChecker) {
            $result = $this->databaseChecker->check();
            $healthCheck->addCheck('database', $result['status'], $result['details']);
        }

        // API externe
        if ($this->apiDependencyChecker) {
            $result = $this->apiDependencyChecker->check();
            $healthCheck->addCheck('external_api', $result['status'], $result['details']);
        }

        // Liveness
        if ($this->livenessChecker) {
            $result = $this->livenessChecker->check();
            $healthCheck->addCheck('liveness', $result['status'], $result['details']);
        }

        // Ping d’autres services
        foreach ($this->services as $name => $url) {
            $result = $this->pingService($url);
            $healthCheck->addCheck($name, $result['status'], $result['details']);
        }

        return [$healthCheck];
    }


    private function pingService(string $url): array
    {
        try {
            $context = stream_context_create(['http' => ['timeout' => 2]]);
            $result = @file_get_contents($url, false, $context);

            if ($result === false) {
                return [
                    'status' => 'down',
                    'details' => "Ping $url failed"
                ];
            }

            return [
                'status' => 'healthy',
                'details' => "Ping $url successful"
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'down',
                'details' => "Ping $url exception: " . $e->getMessage()
            ];
        }
    }
}
