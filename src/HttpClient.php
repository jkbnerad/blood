<?php
declare(strict_types = 1);

namespace app;


use GuzzleHttp\Client;

class HttpClient extends Client
{
    public function __construct(array $config = [])
    {
        $config = array_merge([
            'timeout' => 20,
            'connect_timeout' => 2,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36',
                'X-Identify' => 'Blood Status Bot'
            ]
        ], $config);
        parent::__construct($config);
    }
}
