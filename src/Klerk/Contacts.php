<?php
declare(strict_types = 1);

namespace app\Klerk;


use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface;

class Contacts
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getList(): array
    {
        try {
            $response = $this->httpClient->get('contacts');
            $json = $response->getBody()->getContents();
            $arr = Json::decode($json, Json::FORCE_ARRAY);
            return $arr['contacts'] ?? [];
        } catch (BadResponseException $e) {
            return [];
        }
    }
}
