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
            $result = $this->pingService($url); // renvoie ['status' => ..., 'details' => ...]
            $healthCheck->addCheck(
                $name,
                $result['status'], // prend le vrai status
                json_encode(['url' => $url, 'message' => $result['details']]) // détails JSON
            );
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
