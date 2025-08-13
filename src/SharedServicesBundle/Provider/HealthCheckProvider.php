<?php
namespace mtonzar\SharedServicesBundle\Provider;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use mtonzar\SharedServicesBundle\Entity\HealthCheck;
use mtonzar\SharedServicesBundle\Service\HealthChecker\DatabaseHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\CacheHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\QueueHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\ApiDependencyHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\LivenessHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\ReadinessHealthChecker;

class HealthCheckProvider implements ProviderInterface
{
    private DatabaseHealthChecker $databaseChecker;
    private ApiDependencyHealthChecker $apiDependencyChecker;
    private LivenessHealthChecker $livenessChecker;
    private array $services;

    public function __construct(
        ?DatabaseHealthChecker $databaseChecker = null,
        ?ApiDependencyHealthChecker $apiDependencyChecker = null,
        ?LivenessHealthChecker $livenessChecker = null,
        array $services = []
    ) {
        $this->databaseChecker = $databaseChecker;
        $this->apiDependencyChecker = $apiDependencyChecker;
        $this->livenessChecker = $livenessChecker;
        $this->services = $services;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $healthCheck = new HealthCheck();

        $healthCheck->addCheck('database', ...array_values($this->databaseChecker->check()));
        // $healthCheck->addCheck('cache', ...array_values($this->cacheChecker->check()));
        // $healthCheck->addCheck('queue', ...array_values($this->queueChecker->check()));
        $healthCheck->addCheck('readiness', ...array_values($this->readinessChecker->check()));
         $healthCheck->addCheck('external_apis', ...array_values($this->apiDependencyChecker->check()));
         $healthCheck->addCheck('liveness', ...array_values($this->livenessChecker->check()));
        return [$healthCheck];
    }

    public function provideAll(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $healthCheck = new HealthCheck();

        foreach ($this->services as $name => $url) {
            $status = 'healthy';
            $details = 'Ping OK';

            try {
                $response = @file_get_contents($url);
                if ($response !== 'pong') {
                    $status = 'degraded';
                    $details = 'Ping response incorrect';
                }
            } catch (\Exception $e) {
                $status = 'down';
                $details = $e->getMessage();
            }

            $healthCheck->addCheck($name, $status, $details);
        }

        return [$healthCheck];
    }
}
