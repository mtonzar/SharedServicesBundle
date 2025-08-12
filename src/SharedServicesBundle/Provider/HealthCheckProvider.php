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

class HealthCheckProvider implements ProviderInterface
{
    public function __construct(
        private DatabaseHealthChecker $databaseChecker,
        // private CacheHealthChecker $cacheChecker,
        // private QueueHealthChecker $queueChecker,
        private ApiDependencyHealthChecker $apiDependencyChecker,
        private LivenessHealthChecker $livenessChecker
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $healthCheck = new HealthCheck();

        // Ajout des checks
        $healthCheck->addCheck('database', ...array_values($this->databaseChecker->check()));
        $healthCheck->addCheck('external_apis', ...array_values($this->apiDependencyChecker->check()));
        $healthCheck->addCheck('liveness', ...array_values($this->livenessChecker->check()));
        // Déterminer le status global
        foreach ($healthCheck->getCheck() as $check) {
            if ($check['status'] !== 'healthy') {
                $healthCheck->setStatus('degraded');
                break;
            }
        }

        return [$healthCheck];
    }
}
