<?php

declare(strict_types=1);

namespace web\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;
        $router->addRoute('web/<presenter>/<action>[/<id>]', 'Homepage:default');
        return $router;
    }
}
