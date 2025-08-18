<?php
require __DIR__ . '/vendor/autoload.php';

use mtonzar\SharedServicesBundle\Service\HealthChecker\MultiServicesHealthChecker;

$checker = new MultiServicesHealthChecker([]);
print_r($checker->check());