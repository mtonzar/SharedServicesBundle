<?php
namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

class MultiServicesHealthChecker
{
    public function __construct(private array $services) {}

    public function check(): array
    {
        $results = [];
        foreach ($this->services as $name => $url) {
            $status = $this->pingService($url) ? 'healthy' : 'down';
            $results[$name] = [
                'status' => $status,
                'details' => ['url' => $url, 'message' => $status === 'healthy' ? 'Ping successful' : 'Ping failed']
            ];
        }
        return $results;
    }

    private function pingService(string $url): bool
    {
        try {
            $context = stream_context_create(['http' => ['timeout' => 2]]);
            $result = @file_get_contents($url, false, $context);
            return $result !== false;
        } catch (\Throwable) {
            return false;
        }
    }
}
