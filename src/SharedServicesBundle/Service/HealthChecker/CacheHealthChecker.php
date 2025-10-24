<?php
// src/Service/HealthChecker/CacheHealthChecker.php
namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Psr\Cache\CacheException;
use mtonzar\SharedServicesBundle\Utils\Utf8Sanitizer;

class CacheHealthChecker implements HealthCheckerInterface
{
    private AdapterInterface $cache;

    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    public function check(): array
    {
        try {
            $key = 'health_check_' . random_int(1000, 9999);
            $start = microtime(true);
            
            // Test d'écriture
            $item = $this->cache->getItem($key);
            $item->set('test');
            $this->cache->save($item);

            // Test de lecture
            $item = $this->cache->getItem($key);
            $value = $item->get();

            // Suppression
            $this->cache->deleteItem($key);
            
            $duration = microtime(true) - $start;
            $status = ($value === 'test') ? 'healthy' : 'degraded';
            
            return [
                'status' => $status,
                'details' => [
                    'response_time' => round($duration * 1000, 2) . 'ms',
                    'available' => true,
                    'read_write_test' => ($value === 'test')
                ]
            ];
        } catch (CacheException $e) {
            $result = [
                'status' => 'down',
                'details' => [
                    'error' => $e->getMessage(),
                    'available' => false
                ]
            ];
            return Utf8Sanitizer::sanitize($result);
        } catch (\Throwable $e) {
            $result = [
                'status' => 'down',
                'details' => [
                    'error' => $e->getMessage(),
                    'available' => false
                ]
            ];
            return Utf8Sanitizer::sanitize($result);
        }
    }
}
