<?php

namespace App\Adapters\Controllers;

class StressController
{
    public function handle(): void
    {
        require_once __DIR__ . '/../../stress_test.php';
    }
}
