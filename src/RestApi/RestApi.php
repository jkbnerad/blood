<?php
declare(strict_types = 1);

namespace app\RestApi;


use app\Database\Connection;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Http\Response;
use Nette\Utils\Json;
use Tracy\Debugger;

class RestApi
{
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var IRequest
     */
    private $httpRequest;


    public function __construct(Connection $connection, IRequest $httpRequest)
    {
        $this->connection = $connection;
        $this->httpRequest = $httpRequest;
        Debugger::timer('api');
    }

    public function validApiKey(string $key): bool
    {
        return $this->connection->getConnection()->query('SELECT 1 FROM `ApiKey` WHERE ', ['apiKey' => $key])->fetch() ? true : false;
    }

    public function run(): void
    {
        //        $apiKey = $this->httpRequest->getQuery('apiKey');
        //        if (empty($apiKey) || $this->validApiKey($apiKey) === false) {
        //            $response = new Response();
        //            $response->setCode(Response::S401_UNAUTHORIZED);
        //            $response->setBody(Json::encode(['status' => 'error', 'code' => Response::S401_UNAUTHORIZED, 'message' => 'Unauthorized API key.']));
        //            $this->sendResponse($response);
        //        }

        $method = $this->getMethod();
        $api = null;
        switch ($method) {
            case 'email':
                $api = new Email($this->httpRequest, $this->connection);
                $this->sendResponse($api->run());
                break;
            default:
                $response = new Response();
                $response->setCode(Response::S404_NOT_FOUND)->setBody(Json::encode(['message' => 'API pro darujukrev.cz.']));
                $this->sendResponse($response);
                break;
        }
    }

    private function sendResponse(IResponse $response): void
    {
        header('HTTP/' . $response->getProtocolVersion() . ' ' . $response->getCode() . ' ' . $response->getReasonPhrase());
        header('Content-type: application/json;charset=utf-8');
        header('X-Time: ' . Debugger::timer('api'));
        foreach ($response->getHeaders() as $headerName => $headerData) {
            foreach ($headerData as $headerValue) {
                header($headerName . ':' . $headerValue);
            }
        }
        $body = $response->getBody();
        if (is_string($body)) {
            echo $body;
            exit;
        } else {
            throw new \RuntimeException('Response body must be string.');
        }
    }

    private function getMethod(): ?string
    {
        $path = array_values(array_filter(explode('/', $this->httpRequest->getUrl()->getPath())));
        return $path[1] ?? null;
    }

}
