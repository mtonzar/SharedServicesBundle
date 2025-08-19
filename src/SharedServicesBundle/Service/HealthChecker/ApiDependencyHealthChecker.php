<?php
// src/Service/HealthChecker/ApiDependencyHealthChecker.php
namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiDependencyHealthChecker
{
    public function __construct(private HttpClientInterface $client, private string $apiUrl) {}

    public function check(): array
    {
        try {
            $response = $this->client->request('GET',  $this->apiUrl);
            $statusCode = $response->getStatusCode();

            return [
                'status' => $statusCode === 200 ? 'healthy' : 'down',
                'details' => 'External API status code: ' . $statusCode
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'down',
                'details' => 'External API failed: ' . $e->getMessage()
            ];
        }
    }
    public function checkOne(string $url): array
    {
        try {
            $response = $this->client->request('GET', $url, ['timeout' => 2]);
            $statusCode = $response->getStatusCode();

            return [
                'status' => $statusCode === 200 ? 'healthy' : 'down',
                'details' => $url
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'down',
                'details' => $url
            ];
        }
    }
}
