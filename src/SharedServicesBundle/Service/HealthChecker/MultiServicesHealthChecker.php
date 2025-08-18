<?php
namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

class MultiServicesHealthChecker
{
    public function __construct(private array $services) {}

    public function check(): array
    {
        $results = [];

        foreach ($this->services as $name => $service) {
            $serviceResult = [];

            // Vérification DB si configurée
            if (isset($service['databaseChecker'])) {
                $dbResult = $service['databaseChecker']->check();
                $serviceResult['database'] = [
                    'status' => $dbResult['status'],
                    'details' => $dbResult['details']
                ];
            }

            // Vérification API externe si configurée
            if (isset($service['apiChecker'])) {
                $apiResult = $service['apiChecker']->check();
                $serviceResult['external_api'] = [
                    'status' => $apiResult['status'],
                    'details' => $apiResult['details']
                ];
            }

            // Ping du service
            if (isset($service['url'])) {
                $pingResult = $this->pingService($service['url']);
                $serviceResult['ping'] = [
                    'status' => $pingResult['status'],
                    'details' => $pingResult['details']
                ];
            }

            $results[$name] = $serviceResult;
        }

        return $results;
    }

    private function pingService(string $url): array
    {
        try {
            $context = stream_context_create(['http' => ['timeout' => 2]]);
            $result = @file_get_contents($url, false, $context);

            if ($result === false) {
                return ['status' => 'down', 'details' => "Ping $url failed"];
            }

            return ['status' => 'healthy', 'details' => "Ping $url successful"];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'details' => "Ping $url exception: " . $e->getMessage()];
        }
    }
}
