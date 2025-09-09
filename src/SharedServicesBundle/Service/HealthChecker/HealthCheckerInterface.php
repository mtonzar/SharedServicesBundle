<?php

namespace App\Service\HealthChecker;

interface HealthCheckerInterface
{
    /**
     * Effectue un test de santé et retourne le résultat
     * 
     * @return array Structure contenant le statut et les détails
     */
    public function check(): array;
}