<?php
namespace mtonzar\SharedServicesBundle\Provider;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use mtonzar\SharedServicesBundle\Entity\HealthCheck;
use mtonzar\SharedServicesBundle\Service\HealthChecker\DatabaseHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\CacheHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\QueueHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\ApiDependencyHealthChecker;

class HealthCheckProvider implements ProviderInterface
{
    public function __construct(
        private DatabaseHealthChecker $databaseChecker,
        private CacheHealthChecker $cacheChecker,
        private QueueHealthChecker $queueChecker,
        private ApiDependencyHealthChecker $apiDependencyChecker
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $healthCheck = new HealthCheck();

        $healthCheck->addCheck('database', ...array_values($this->databaseChecker->check()));
        $healthCheck->addCheck('cache', ...array_values($this->cacheChecker->check()));
        $healthCheck->addCheck('queue', ...array_values($this->queueChecker->check()));
        $healthCheck->addCheck('external_apis', ...array_values($this->apiDependencyChecker->check()));

        return [$healthCheck];
    }
}
