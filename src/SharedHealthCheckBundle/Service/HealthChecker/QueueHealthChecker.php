<?php
// src/Service/HealthChecker/QueueHealthChecker.php
namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

use Symfony\Component\Messenger\Transport\TransportInterface;

class QueueHealthChecker
{
    public function __construct(private TransportInterface $transport) {}

    public function check(): array
    {
        try {
            $this->transport->get();

            return [
                'status' => 'healthy',
                'details' => 'Queue connection OK'
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'down',
                'details' => 'Queue connection failed: ' . $e->getMessage()
            ];
        }
    }
}
