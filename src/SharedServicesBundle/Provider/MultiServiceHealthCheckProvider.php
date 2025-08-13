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

        foreach ($this->multiChecker->check() as $service => $result) {
            $healthCheck->addCheck($service, $result['status'], json_encode($result['details']));
        }

        return [$healthCheck];
    }
}
