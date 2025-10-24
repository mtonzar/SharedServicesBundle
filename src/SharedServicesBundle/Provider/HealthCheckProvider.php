<?php
// src/Provider/HealthCheckProvider.php
namespace mtonzar\SharedServicesBundle\Provider;

use ApiPlatform\State\Provider\CollectionProviderInterface;
use ApiPlatform\State\Provider\RestrictedDataProviderInterface;
use mtonzar\SharedServicesBundle\Entity\HealthCheck;
use mtonzar\SharedServicesBundle\Service\HealthChecker\DatabaseHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\CacheHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\QueueHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\ApiDependencyHealthChecker;


class HealthCheckProvider implements CollectionProviderInterface, RestrictedDataProviderInterface
{
    private DatabaseHealthChecker $databaseChecker;
    private CacheHealthChecker $cacheChecker;
    private QueueHealthChecker $queueChecker;
    private ApiDependencyHealthChecker $apiDependencyChecker;

    public function __construct(
        DatabaseHealthChecker $databaseChecker,
        CacheHealthChecker $cacheChecker,
        QueueHealthChecker $queueChecker,
        ApiDependencyHealthChecker $apiDependencyChecker
    ) {
        $this->databaseChecker = $databaseChecker;
        $this->cacheChecker = $cacheChecker;
        $this->queueChecker = $queueChecker;
        $this->apiDependencyChecker = $apiDependencyChecker;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === HealthCheck::class;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
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

        // Vous pouvez ajouter d'autres vérifications ici...

        return [$healthCheck]; // Retourne un tableau avec un seul élément
    }
}
