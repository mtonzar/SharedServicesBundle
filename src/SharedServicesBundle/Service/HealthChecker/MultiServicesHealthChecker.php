<?php

namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

class MultiServicesHealthChecker
{
    private array $services;

    public function __construct(array $services)
    {
        $this->services = $services;
    }

    public function check(): array
    {
        $results = [];

        foreach ($this->services as $name => $url) {
            try {
                $response = @file_get_contents($url);
                if ($response === false) {
                    throw new \Exception('No response');
                }

                $data = json_decode($response, true);
                $results[$name] = [
                    'status' => ($data['services'] ?? []) ? 'healthy' : 'degraded',
                    'details' => $data['services'] ?? []
                ];
            } catch (\Exception $e) {
                $results[$name] = [
                    'status' => 'down',
                    'details' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
}
