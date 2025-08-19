<?php
namespace mtonzar\SharedServicesBundle\Provider;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use mtonzar\SharedServicesBundle\Entity\HealthCheck;
use mtonzar\SharedServicesBundle\Service\HealthChecker\DatabaseHealthChecker;
use mtonzar\SharedServicesBundle\Service\HealthChecker\ApiDependencyHealthChecker;

class HealthCheckProvider implements ProviderInterface
{
    public function __construct(
        private ?DatabaseHealthChecker $databaseChecker = null,
        private ?ApiDependencyHealthChecker $apiChecker = null,
        private array $services = []  // config.yaml => services list
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
                } elseif ($type === 'api') {
                    $status = $this->apiChecker
                        ? $this->apiChecker->checkOne($urlOrDsn)
                        : ['status' => 'unknown', 'details' => 'No API checker'];
                } else {
                    $status = ['status' => 'unknown', 'details' => "Unknown type: $type"];
                }

                if ($status['status'] === 'down') {
                    $overallStatus = 'down';
                }

                $serviceDetails[$type] = $status;
            }

            $healthCheck->addCheck(
                $serviceName,
                $overallStatus,
                $serviceDetails   // ✅ tableau structuré
            );
        }

        return [$healthCheck];
    }
}
