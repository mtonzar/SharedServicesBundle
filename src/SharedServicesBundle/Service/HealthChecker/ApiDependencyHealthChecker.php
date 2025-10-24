<?php
// src/Service/HealthChecker/ApiDependencyHealthChecker.php
namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use mtonzar\SharedServicesBundle\Utils\Utf8Sanitizer;

class ApiDependencyHealthChecker implements HealthCheckerInterface
{
    private HttpClientInterface $httpClient;
    private array $endpoints;

    public function __construct(HttpClientInterface $httpClient, array $endpoints = [])
    {
        $this->httpClient = $httpClient;
        $this->endpoints = $endpoints;
    }

    public function check(): array
    {
        $results = [
            'status' => 'healthy',
            'details' => [
                'endpoints' => []
            ]
        ];

        foreach ($this->endpoints as $name => $url) {
            try {
                $start = microtime(true);
                $response = $this->httpClient->request('GET', $url, [
                    'timeout' => 5,
                    'max_duration' => 10
                ]);
                $statusCode = $response->getStatusCode();
                $duration = microtime(true) - $start;
                
                $endpointStatus = 'healthy';
                if ($statusCode >= 400 && $statusCode < 500) {
                    $endpointStatus = 'degraded';
                } elseif ($statusCode >= 500) {
                    $endpointStatus = 'down';
                }
                
                $results['details']['endpoints'][$name] = [
                    'url' => $url,
                    'status' => $endpointStatus,
                    'status_code' => $statusCode,
                    'response_time' => round($duration * 1000, 2) . 'ms'
                ];
                
                // Si un endpoint est en panne, mettre à jour le statut global
                if ($endpointStatus === 'degraded' && $results['status'] === 'healthy') {
                    $results['status'] = 'degraded';
                } elseif ($endpointStatus === 'down') {
                    $results['status'] = 'down';
                }
                
            } catch (HttpExceptionInterface | TransportExceptionInterface $e) {
                $results['details']['endpoints'][$name] = [
                    'url' => $url,
                    'status' => 'down',
                    'error' => $e->getMessage()
                ];
                $results['status'] = 'down';
            }
        }

        return Utf8Sanitizer::sanitize($results);
    }
}