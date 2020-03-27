<?php
declare(strict_types = 1);

namespace app\Klerk;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class HttpClient extends Client
{
    public const BASE_URI = 'https://klerk.stable.cz/api/';
    /**
     * @var string
     */
    private $apiKey;

    public function __construct(string $apiKey, array $config = [])
    {
        $default = ['base_uri' => self::BASE_URI, 'timeout' => 10, 'connect_timeout' => 1];
        $config = array_merge($default, $config);
        parent::__construct($config);
        $this->apiKey = $apiKey;
    }

    /**
     * @param UriInterface|string $uri
     * @param array $options
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get($uri, array $options = []): ResponseInterface
    {
        $defaultOptions['query'] = ['auth' => $this->apiKey];
        $options = array_merge($defaultOptions, $options);

        return parent::get($uri, $options);
    }

    /**
     * @param UriInterface|string $uri
     * @param array $options
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function post($uri, array $options = []): ResponseInterface
    {
        $defaultOptions['query'] = ['auth' => $this->apiKey];
        $options = array_merge($defaultOptions, $options);

        return parent::post($uri, $options);
    }

    /**
     * @param UriInterface|string $uri
     * @param array $options
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function patch($uri, array $options = []): ResponseInterface
    {
        $defaultOptions['query'] = ['auth' => $this->apiKey];
        $options = array_merge($defaultOptions, $options);

        return parent::patch($uri, $options);
    }
}
