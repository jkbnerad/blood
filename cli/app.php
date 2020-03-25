<?php
declare(strict_types=1);
umask(0002);
date_default_timezone_set('Europe/Prague');

use app\Commands\GoogleSpreadsheet;
use app\Commands\Mailchimp;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application();
$app->add(new GoogleSpreadsheet());
$app->add(new Mailchimp());

$app->run();
