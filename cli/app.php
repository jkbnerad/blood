<?php
declare(strict_types=1);
umask(0002);
date_default_timezone_set('Europe/Prague');

use app\Commands\GoogleSpreadsheet;
use app\Commands\Klerk;
use app\Commands\Mailchimp;
use app\Commands\TestEmail;
use Symfony\Component\Console\Application;
use Tracy\Debugger;

require __DIR__ . '/../vendor/autoload.php';

Debugger::$showBar = false;
Debugger::enable('62.201.28.178', __DIR__ . '/../log'); // 62.201.28.178
Debugger::$email = 'jakubnerad@gmail.com';

$app = new Application();
$app->add(new GoogleSpreadsheet());
$app->add(new Mailchimp());
$app->add(new Klerk());
$app->add(new TestEmail());

$app->run();
