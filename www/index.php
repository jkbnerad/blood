<?php
declare(strict_types = 1);

use app\Application;
use Tracy\Debugger;

date_default_timezone_set('Europe/Prague');

require __DIR__ . '/../vendor/autoload.php';

Debugger::$showBar = false;
Debugger::enable('62.201.28.178', __DIR__ . '/../log'); // 62.201.28.178
Debugger::$email = 'jakubnerad@gmail.com';
Sentry\init(['dsn' => 'https://8b37cf412a7b440888e2c82e0fa9e339@sentry.io/5186955']);

$app = new Application((new \Nette\Http\RequestFactory())->fromGlobals());
$app->run();
