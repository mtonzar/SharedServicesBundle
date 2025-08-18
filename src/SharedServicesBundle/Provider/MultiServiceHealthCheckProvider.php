<?php

namespace mtonzar\SharedServicesBundle\Provider;


use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use mtonzar\SharedServicesBundle\Entity\HealthCheck;
use mtonzar\SharedServicesBundle\Service\HealthChecker\MultiServicesHealthChecker;

class MultiServiceHealthCheckProvider implements ProviderInterface
{
    public function __construct(private MultiServicesHealthChecker $multiChecker) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $healthCheck = new HealthCheck();

        foreach ($this->multiChecker->check() as $serviceName => $serviceChecks) {
            // Convertir les détails en JSON
            $healthCheck->addCheck(
                $serviceName,
                'grouped', 
                json_encode($serviceChecks) 
            );
        }
        

        return [$healthCheck];
    }
}
