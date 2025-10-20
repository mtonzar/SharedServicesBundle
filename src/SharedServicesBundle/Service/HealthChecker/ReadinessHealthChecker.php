<?php

namespace mtonzar\SharedServicesBundle\Service\HealthChecker;
use Doctrine\DBAL\Connection;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ReadinessHealthChecker
{
    public function __construct(
        private Connection $connection,
        private CacheItemPoolInterface $cache,
        private HttpClientInterface $httpClient,
        private string $externalServiceUrl 
    ) {}

    public function check(): array
    {
        $issues = [];

        // Vérifie la base de données
        try {
            $this->connection->executeQuery('SELECT 1');
        } catch (\Throwable $e) {
            $issues[] = 'Database connection failed';
        }

        // Vérifie le cache
        try {
            $cacheItem = $this->cache->getItem('readiness_test');
            $this->cache->save($cacheItem->set('ok'));
        } catch (\Throwable $e) {
            $issues[] = 'Cache system not available';
        }

        // Vérifie le service externe
        try {
            $response = $this->httpClient->request('GET', $this->externalServiceUrl, ['timeout' => 2]);
            if ($response->getStatusCode() !== 200) {
                $issues[] = 'External API dependency not reachable';
            }
        } catch (\Throwable $e) {
            $issues[] = 'External API dependency not reachable';
        }

        return [
            'status' => empty($issues) ? 'healthy' : 'down',
            'message' => empty($issues)
                ? 'Service is ready to accept requests'
                : implode(' | ', $issues)
        ];
    }
}
