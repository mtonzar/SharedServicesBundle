<?php
namespace mtonzar\SharedServicesBundle\Provider;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use mtonzar\SharedServicesBundle\Entity\HealthCheck;
use mtonzar\SharedServicesBundle\Service\HealthChecker\DatabaseHealthChecker;

class HealthCheckProvider implements ProviderInterface
{
    public function __construct(
        private ?DatabaseHealthChecker $databaseChecker = null,
        private array $services = []
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $healthCheck = new HealthCheck();

        foreach ($this->services as $serviceName => $endpoints) {
            $serviceDetails = [];
            $overallStatus = 'healthy';

            foreach ($endpoints as $type => $urlOrDsn) {
                if ($type === 'database') {
                    $status = $this->databaseChecker
                        ? $this->databaseChecker->check($urlOrDsn)
                        : ['status' => 'unknown', 'details' => 'No DB checker'];
                } else { // api ou ping
                    $status = $this->pingService($urlOrDsn);
                }

                if ($status['status'] === 'down') {
                    $overallStatus = 'down';
                }

                $serviceDetails[$type] = $status;
            }

            $healthCheck->addCheck(
                $serviceName,
                $overallStatus,
                json_encode($serviceDetails) // toujours string pour addCheck
            );
        }

        return [$healthCheck];
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
