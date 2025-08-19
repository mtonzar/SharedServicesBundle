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
                } else {
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
                $serviceDetails   // ✅ plus de json_encode
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
                return ['status' => 'down', 'details' => $url];
            }

            return ['status' => 'healthy', 'details' => $url];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'details' => $url . ' ' . $e->getMessage()];
        }
    }
}
