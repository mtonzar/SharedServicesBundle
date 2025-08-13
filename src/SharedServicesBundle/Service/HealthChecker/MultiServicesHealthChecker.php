<?php
namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

class MultiServicesHealthChecker
{
    public function __construct(private array $services) {}

    public function check(): array
    {
        // Exemple de logique
        $results = [];
        foreach ($this->services as $name => $url) {
            $results[$name] = [
                'status' => 'healthy',
                'details' => ['url' => $url]
            ];
        }
        return $results;
    }
}