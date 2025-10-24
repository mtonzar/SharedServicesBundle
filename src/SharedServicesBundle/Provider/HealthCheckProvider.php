<?php
// src/Provider/HealthCheckProvider.php
namespace mtonzar\SharedServicesBundle\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use mtonzar\SharedServicesBundle\Entity\HealthCheck;
use mtonzar\SharedServicesBundle\Service\HealthChecker\DatabaseHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\CacheHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\QueueHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\ApiDependencyHealthChecker;

/**
 * State Provider for HealthCheck compatible with API Platform 3.x
 */
class HealthCheckProvider implements ProviderInterface
{
    public function __construct(
        private readonly DatabaseHealthChecker $databaseChecker,
        private readonly CacheHealthChecker $cacheChecker,
        private readonly QueueHealthChecker $queueChecker,
        private readonly ApiDependencyHealthChecker $apiDependencyChecker
    ) {}

    /**
     * Provide health check data for API Platform
     *
     * @param Operation $operation The API Platform operation
     * @param array $uriVariables URI variables (not used for health check)
     * @param array $context Additional context
     * @return HealthCheck|array Returns a HealthCheck object or array of HealthCheck objects
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $healthCheck = new HealthCheck();

        // Vérification de la base de données
        $dbStatus = $this->databaseChecker->check();
        $healthCheck->addCheck('database', $dbStatus['status'], $dbStatus['details']);

        // Vérification du cache
        $cacheStatus = $this->cacheChecker->check();
        $healthCheck->addCheck('cache', $cacheStatus['status'], $cacheStatus['details']);

        // Vérification des files de messages
        $queueStatus = $this->queueChecker->check();
        $healthCheck->addCheck('queue', $queueStatus['status'], $queueStatus['details']);

        // Vérification des API externes
        $apiStatus = $this->apiDependencyChecker->check();
        $healthCheck->addCheck('external_apis', $apiStatus['status'], $apiStatus['details']);

        // For GetCollection operations, return an array
        // For Get operations, return a single object
        if ($operation->getName() === '_api_/health_checks{._format}_get_collection') {
            return [$healthCheck];
        }

        return $healthCheck;
    }
}
