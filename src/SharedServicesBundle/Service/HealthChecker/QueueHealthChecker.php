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
        // Vérifier directement via DBAL sans utiliser le Transport (évite le destructeur PostgreSqlConnection)
        if ($this->connection !== null) {
            try {
                $start = microtime(true);

                // Vérifier que la connexion fonctionne
                $this->connection->executeQuery('SELECT 1');

                // Compter les messages en attente dans la table messenger_messages
                $result = $this->connection->executeQuery(
                    'SELECT COUNT(*) as count FROM messenger_messages WHERE queue_name = ?',
                    [$this->queueName]
                );
                $row = $result->fetchAssociative();
                $pendingMessages = (int) ($row['count'] ?? 0);

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

        // Fallback : utiliser le transport si pas de connexion DBAL fournie
        try {
            if ($this->transport === null) {
                return [
                    'status' => 'down',
                    'details' => [
                        'queue_name' => $this->queueName,
                        'error' => 'Neither connection nor transport configured',
                        'available' => false
                    ]
                ];
            }

            $start = microtime(true);
            $pendingMessages = count($this->transport->get());
            $duration = microtime(true) - $start;

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

