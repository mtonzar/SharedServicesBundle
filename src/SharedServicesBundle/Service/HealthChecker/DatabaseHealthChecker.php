<?php
// src/Service/HealthChecker/DatabaseHealthChecker.php
namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

use Doctrine\DBAL\Connection;

class DatabaseHealthChecker
{
    public function __construct(private Connection $connection) {}

    public function check(): array
    {
        try {
            $this->connection->connect();

            return [
                'status' => 'healthy',
                'details' => 'Database connection OK'
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'down',
                'details' => 'DB connection failed: ' . $e->getMessage()
            ];
        }
    }
}
