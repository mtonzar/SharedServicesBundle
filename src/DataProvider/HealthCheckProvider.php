<?php
namespace App\DataProvider;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\HealthCheck;
use App\Service\HealthChecker\DatabaseHealthChecker;
use App\Service\HealthChecker\CacheHealthChecker;
use App\Service\HealthChecker\QueueHealthChecker;
use App\Service\HealthChecker\ApiDependencyHealthChecker;

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
