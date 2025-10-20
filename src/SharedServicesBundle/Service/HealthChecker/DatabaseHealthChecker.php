<?php
// src/Service/HealthChecker/DatabaseHealthChecker.php

namespace App\Service\HealthChecker;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;

class DatabaseHealthChecker implements HealthCheckerInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function check(): array
    {
        try {
            $start = microtime(true);
            
            // Exécute une requête simple pour vérifier la connexion
            $this->connection->executeQuery('SELECT 1');
            
            $duration = microtime(true) - $start;
            
            return [
                'status' => 'healthy',
                'details' => [
                    'response_time' => round($duration * 1000, 2) . 'ms',
                    'connected' => true
                ]
            ];
        } catch (DBALException $e) {
            return [
                'status' => 'down',
                'details' => [
                    'error' => $e->getMessage(),
                    'connected' => false
                ]
            ];
        }
    }
}
