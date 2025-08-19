<?php
// src/Service/HealthChecker/DatabaseHealthChecker.php
namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;

class DatabaseHealthChecker
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    // public function __construct(private Connection $connection) {}

    public function check(): array
    {
        try {
            // $this->connection->connect();
            $start = microtime(true);
            // Exécute une requête simple pour vérifier la connexion   
            $this->connection->executeQuery('SELECT 1');
            $duration = microtime(true) - $start;

            return [
                'status' => 'healthy',
                // 'details' => 'Database connection OK'  // tableau 1- response time 
                'details' => [
                    'message' => 'database connection OK',
                    'response_time' => round($duration * 1000, 2) . 'ms'
                ]
            ];
        } catch (DBALException $e) {
            return [
                'status' => 'down',
                'details' =>[
                    'message' => $e->getMessage(),
                    'error' => $e
                ] 
            ];
        }
    }
}
