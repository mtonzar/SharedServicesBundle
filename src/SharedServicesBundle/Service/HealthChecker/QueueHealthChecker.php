<?php

// src/Service/HealthChecker/QueueHealthChecker.php
namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

use Symfony\Component\Messenger\Transport\TransportInterface;

class QueueHealthChecker implements HealthCheckerInterface
{
    private TransportInterface $transport;
    private string $queueName;

    public function __construct(TransportInterface $transport, string $queueName = 'default')
    {
        $this->transport = $transport;
        $this->queueName = $queueName;
    }

    public function check(): array
    {
        try {
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
            return [
                'status' => 'down',
                'details' => [
                    'queue_name' => $this->queueName,
                    'error' => $e->getMessage(),
                    'available' => false
                ]
            ];
        }
    }
}



// src/Service/HealthChecker/HealthCheckerInterface.php

