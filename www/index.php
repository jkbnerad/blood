<?php
declare(strict_types=1);

use app\Application;
use Tracy\Debugger;

date_default_timezone_set('Europe/Prague');

require __DIR__ . '/../vendor/autoload.php';


Debugger::enable('62.201.28.178', __DIR__ . '/../log'); // 62.201.28.178
Debugger::$email = 'jakubnerad@gmail.com';

$app = new Application((new \Nette\Http\RequestFactory())->fromGlobals());
$app->run();
