<?php
// src/Service/HealthChecker/DatabaseHealthChecker.php

namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use mtonzar\SharedServicesBundle\Utils\Utf8Sanitizer;

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
            $result = [
                'status' => 'down',
                'details' => [
                    'available' => false,
                    'connected' => false,
                    'code' => 'DB-UNAVAILABLE',
                ]
            ];

            // Sanitize UTF-8 encoding for error messages
            return Utf8Sanitizer::sanitize($result);
        }
    }
}
