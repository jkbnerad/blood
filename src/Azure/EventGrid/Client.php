<?php
declare(strict_types=1);

namespace app\Azure\EventGrid;


use GuzzleHttp\RequestOptions;

class Client extends \GuzzleHttp\Client
{

    public function __construct(array $config = [])
    {
        $iniConfig = parse_ini_file(__DIR__ . '/../../../configs/azure.ini', true);
        $defaultConfig = [
            RequestOptions::HEADERS => ['aeg-sas-key' => $iniConfig['EventGrid']['key']],
            'base_uri' => $iniConfig['EventGrid']['endpoint']
        ];
        $config = array_merge($config, $defaultConfig);
        parent::__construct($config);
    }
}
