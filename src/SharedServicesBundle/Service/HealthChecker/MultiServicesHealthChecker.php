<?php
namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

class MultiServicesHealthChecker
{
    public function __construct(private array $services) {}

    public function check(): array
    {
        $results = [];

        foreach ($this->services as $serviceName => $config) {
            $checks = [];

            // Vérifier la base de données
            if (isset($config['database'])) {
                $dbOk = $this->checkDatabase($config['database']);
                $checks[] = [
                    'check' => 'database',
                    'status' => $dbOk ? 'healthy' : 'down',
                    'details' => $dbOk ? 'Connexion DB OK' : 'Connexion DB échouée'
                ];
            }

            // Vérifier une API externe
            if (isset($config['api'])) {
                $apiOk = $this->pingUrl($config['api']);
                $checks[] = [
                    'check' => 'external_api',
                    'status' => $apiOk ? 'healthy' : 'down',
                    'details' => $apiOk ? 'API OK' : 'API indisponible'
                ];
            }

            // Vérifier un ping (endpoint de l’app elle-même ou autre service)
            if (isset($config['ping'])) {
                $pingResult = $this->pingUrl($config['ping']);
                $checks[] = [
                    'check' => 'ping',
                    'status' => $pingResult ? 'healthy' : 'down',
                    'details' => $pingResult ? "Ping {$config['ping']} OK" : "Ping {$config['ping']} failed"
                ];
            }

            $results[$serviceName] = $checks;
        }

        return $results;
    }

    private function checkDatabase(string $dsn): bool
    {
        try {
            $pdo = new \PDO($dsn);
            return $pdo !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function pingUrl(string $url): bool
    {
        try {
            $context = stream_context_create(['http' => ['timeout' => 2]]);
            $result = @file_get_contents($url, false, $context);
            return $result !== false;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
