<?php

declare(strict_types = 1);

use web\Bootstrap;

require __DIR__ . '/../vendor/autoload.php';

Bootstrap::boot()
    ->createContainer()
    ->getByType(Nette\Application\Application::class)
    ->run();
