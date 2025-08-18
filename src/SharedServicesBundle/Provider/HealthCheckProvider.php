<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use ApiPlatform\Metadata\Get;
use mtonzar\SharedServicesBundle\Provider\HealthCheckProvider;
use mtonzar\SharedServicesBundle\Entity\HealthCheck;

class HealthCheckController
{
    public function __construct(private HealthCheckProvider $healthCheckProvider) {}

    #[Route('/health/check', name: 'health_check')]
    public function healthCheck(): JsonResponse
    {
        $operation = new Get();
        $results = $this->healthCheckProvider->provide($operation);

        /** @var HealthCheck $healthCheck */
        $healthCheck = $results[0];

        // Déterminer le code HTTP : si un service est down, status = 503
        $status = 200;
        foreach ($healthCheck->getChecks() as $check) {
            if ($check['status'] !== 'healthy') {
                $status = 503;
                break;
            }
        }

        // Construire la réponse JSON
        $responseData = [
            'timestamp' => $healthCheck->getTimestamp(),
            'checks'    => $healthCheck->getChecks()
        ];

        return new JsonResponse($responseData, $status);
    }
}
