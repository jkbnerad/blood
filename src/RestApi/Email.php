<?php
declare(strict_types = 1);

namespace app\RestApi;


use app\Database\Connection;
use app\Emails\Exceptions\EmailExistsException;
use app\Emails\Exceptions\EmailHasWrongFormatException;
use app\Emails\Save;
use Nette\Http\IRequest;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Utils\Json;

class Email
{
    /**
     * @var IRequest
     */
    private $httpRequest;
    /**
     * @var Connection
     */
    private $connection;


    public function __construct(IRequest $httpRequest, Connection $connection)
    {
        $this->httpRequest = $httpRequest;
        $this->connection = $connection;
    }

    public function run(): \app\RestApi\Response
    {
        $action = $this->getAction();
        $response = null;
        if ($action === 'add') {

            if ($this->httpRequest->getMethod() === Request::POST) {
                $postData = $this->httpRequest->getPost();
            } else { // temporarily, useful for apache benchmark
                $postData = $this->httpRequest->getQuery();
            }

            if (isset($postData['email'])) {
                try {
                    $save = new Save($this->connection);
                    // tag = hid -> back compatibility
                    $saved = $save->save($postData['email'], $postData['tag'] ?? ($postData['hid'] ?? null));
                    if ($saved) {
                        return $this->sendOk(Response::S200_OK, 'Email has been saved.');
                    } else {
                        return $this->sendError(Response::S400_BAD_REQUEST, 'Unexpected error.');
                    }
                } catch (EmailHasWrongFormatException $e) {
                    return $this->sendError(Response::S406_NOT_ACCEPTABLE, $e->getMessage());
                } catch(EmailExistsException $e) {
                    return $this->sendError(Response::S409_CONFLICT, $e->getMessage());
                }

            } else {
                return $this->sendError(Response::S400_BAD_REQUEST, 'Param `email` expected.');
            }
        } else {
            return $this->sendError(Response::S404_NOT_FOUND, 'Action not found.');
        }
    }

    private function sendError(int $code, ?string $message): \app\RestApi\Response
    {
        switch ($code) {
            case Response::S409_CONFLICT:
                $type = 'exists';
                break;
            case Response::S406_NOT_ACCEPTABLE:
                $type = 'invalid';
                break;
            case Response::S400_BAD_REQUEST:
                $type = 'empty';
                break;
            default:
                $type = 'unknown';
                break;
        }

        $netteResponse = new Response();
        $netteResponse->setCode($code);
        $response = new \app\RestApi\Response($netteResponse);

        $body = [
            'status' => 'error',
            'code' => $code,
            'message' => '',
            'type' => $type
        ];

        if ($message) {
            $body['message'] = $message;
        }

        $response->setBody(Json::encode($body));

        return $response;
    }

    private function sendOk(int $code, ?string $message): \app\RestApi\Response
    {
        $netteResponse = new Response();
        $netteResponse->setCode($code);
        $response = new \app\RestApi\Response($netteResponse);

        $body = [
            'status' => 'ok',
            'type' => 'success',
            'code' => $code,
            'message' => ''
        ];
        if ($message) {
            $body['message'] = $message;
        }
        $response->setBody(Json::encode($body));

        return $response;
    }


    private function getAction(): ?string
    {
        $path = array_values(array_filter(explode('/', $this->httpRequest->getUrl()->getPath())));
        return $path[2] ?? null;
    }
}
