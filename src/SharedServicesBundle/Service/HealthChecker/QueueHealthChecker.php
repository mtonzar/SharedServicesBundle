<?php

// src/Service/HealthChecker/QueueHealthChecker.php
namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

use Symfony\Component\Messenger\Transport\TransportInterface;
use Doctrine\DBAL\Connection;
use mtonzar\SharedServicesBundle\Utils\Utf8Sanitizer;

class QueueHealthChecker implements HealthCheckerInterface
{
    private ?TransportInterface $transport = null;
    private string $queueName;
    private ?Connection $connection = null;

    public function __construct(?TransportInterface $transport = null, string $queueName = 'default', ?Connection $connection = null)
    {
        $this->transport = $transport;
        $this->queueName = $queueName;
        $this->connection = $connection;
    }

    public function setTransport(TransportInterface $transport): void
    {
        $this->transport = $transport;
    }

    public function check(): array
    {
        // Si on a une connexion DB, vérifier d'abord qu'elle est accessible
        if ($this->connection !== null) {
            try {
                $this->connection->executeQuery('SELECT 1');
            } catch (\Throwable $e) {
                // La DB est down, ne pas essayer d'utiliser le transport (évite l'erreur fatale du destructeur)
                $result = [
                    'status' => 'down',
                    'details' => [
                        'queue_name' => $this->queueName,
                        'error' => 'Database connection required for queue is not available',
                        'available' => false
                    ]
                ];
                return Utf8Sanitizer::sanitize($result);
            }
        }

        try {
            if ($this->transport === null) {
                return [
                    'status' => 'down',
                    'details' => [
                        'queue_name' => $this->queueName,
                        'error' => 'Transport not configured',
                        'available' => false
                    ]
                ];
            }

            $start = microtime(true);

            // Récupérer le nombre de messages en attente
            $pendingMessages = count($this->transport->get());

            $duration = microtime(true) - $start;

            // Déterminer le statut en fonction du nombre de messages en attente
            $status = 'healthy';
            if ($pendingMessages > 1000) {
                $status = 'degraded';
            }

            return [
                'status' => $status,
                'details' => [
                    'queue_name' => $this->queueName,
                    'pending_messages' => $pendingMessages,
                    'response_time' => round($duration * 1000, 2) . 'ms',
                    'available' => true
                ]
            ];
        } catch (\Throwable $e) {
            $result = [
                'status' => 'down',
                'details' => [
                    'queue_name' => $this->queueName,
                    'error' => $e->getMessage(),
                    'available' => false
                ]
            ];
            return Utf8Sanitizer::sanitize($result);
        }
    }
}



// src/Service/HealthChecker/HealthCheckerInterface.php

