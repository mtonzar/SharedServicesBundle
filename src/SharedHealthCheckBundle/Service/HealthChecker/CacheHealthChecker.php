<?php
// src/Service/HealthChecker/CacheHealthChecker.php
namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

use Symfony\Contracts\Cache\CacheInterface;

class CacheHealthChecker
{
    public function __construct(private CacheInterface $cache) {}

    public function check(): array
    {
        try {
            $this->cache->get('health_check', fn() => 'ok');

            return [
                'status' => 'healthy',
                'details' => 'Cache connection OK'
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'down',
                'details' => 'Cache connection failed: ' . $e->getMessage()
            ];
        }
    }
}
