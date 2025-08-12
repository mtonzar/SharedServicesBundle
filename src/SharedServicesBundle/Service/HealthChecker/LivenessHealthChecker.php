<?php

namespace mtonzar\SharedServicesBundle\Service\HealthChecker;

class LivenessHealthChecker
{
    public function check(): array
    {
        // Exemple basique: vérifier que PHP est actif et pas en mémoire saturée
        $memoryUsage = memory_get_usage();
        $memoryLimit = ini_get('memory_limit');

        // Convertir memory_limit en octets
        $limitBytes = $this->convertToBytes($memoryLimit);

        $status = ($memoryUsage < $limitBytes * 0.9) ? 'healthy' : 'degraded'; // si > 90% de mémoire, dégradé

        return [
            'status' => $status,
            'message' => 'Memory usage: ' . round($memoryUsage / 1024 / 1024, 2) . ' MB',
        ];
    }

    private function convertToBytes(string $val): int
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = (int)$val;

        switch ($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }

        return $val;
    }
}
