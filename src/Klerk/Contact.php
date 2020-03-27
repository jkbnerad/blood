<?php
declare(strict_types = 1);

namespace app\Klerk;


use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Http\Message\ResponseInterface;

class Contact
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }


    public function lookupByEmail(string $email): ResponseInterface
    {
        return $this->httpClient->get('contact/' . $email);
    }


    /**
     * @param string $email
     * @param string $name
     * @param string $notice
     * @param array $groups
     * @return bool
     * @throws GuzzleException
     * @throws JsonException
     */
    public function create(string $email, string $name, string $notice, array $groups): bool
    {
        try {
            $response = $this->httpClient->post('contact', [
                RequestOptions::JSON => [
                    'email' => $email,
                    'name' => $name,
                    'notice' => $notice,
                    'groups' => $groups
                ]
            ]);
            $json = $response->getBody()->getContents();
            $arr = Json::decode($json, Json::FORCE_ARRAY);
            return isset($arr['error']) ? false : true;
        } catch (BadResponseException $e) {
            return false;
        }
    }

    public function updateNotice(string $email, string $notice): bool
    {
        try {
            $response = $this->httpClient->patch('contact/' . $email, [RequestOptions::JSON => ['notice' => $notice]]);
            $json = $response->getBody()->getContents();
            $arr = Json::decode($json, Json::FORCE_ARRAY);
            return isset($arr['error']) ? false : true;
        } catch (BadResponseException $e) {
            return false;
        }
    }

    public function delete(string $email): bool
    {
        try {
            $response = $this->httpClient->get('contact/' . $email);
            $json = $response->getBody()->getContents();
            $arr = Json::decode($json, Json::FORCE_ARRAY);
            return isset($arr['error']) ? false : true;
        } catch (BadResponseException $e) {
            return false;
        }
    }
}
