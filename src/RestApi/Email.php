<?php
declare(strict_types = 1);

namespace app\RestApi;


use app\Azure\EventGrid\Client;
use app\Azure\EventGrid\Event;
use app\Azure\EventGrid\Hash;
use app\Database\Connection;
use app\Emails\Confirm;
use app\Emails\Exceptions\BloodTypeHasWrongFormatException;
use app\Emails\Exceptions\EmailExistsException;
use app\Emails\Exceptions\EmailHasWrongFormatException;
use app\Emails\Exceptions\PhoneHasWrongFormatException;
use app\Emails\Exceptions\TagWrongFormatException;
use app\Emails\Exceptions\UnknownException;
use app\Emails\Save;
use app\Emails\SendToKlerk;
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
                    $email = $postData['email'];
                    $hids = $postData['tag'] ?? ($postData['hids'] ?? null); // tag - is old param name, back compatibility
                    $phone = (isset($postData['phone']) && $postData['phone'] !== '') ? $postData['phone'] : null;
                    $bloodType = (isset($postData['bloodType']) && $postData['bloodType'] !== '') ? $postData['bloodType'] : null;

                    $saved = $save->save($email, $hids, $phone, $bloodType);
                    if ($saved) {
                        $eventGrid = new Event(new Client());
                        $eventGrid->send($email, $hids);

                        return $this->sendOk(Response::S200_OK, 'Email has been saved.');
                    } else {
                        return $this->sendError(Response::S400_BAD_REQUEST, 'Unexpected error.');
                    }
                } catch (EmailHasWrongFormatException | BloodTypeHasWrongFormatException | PhoneHasWrongFormatException | TagWrongFormatException $e) {
                    return $this->sendError(Response::S406_NOT_ACCEPTABLE, $e->getMessage());
                } catch (EmailExistsException $e) {
                    return $this->sendError(Response::S409_CONFLICT, $e->getMessage());
                } catch (UnknownException $e) {
                    return $this->sendError(Response::S404_NOT_FOUND, $e->getMessage());
                }

            } else {
                return $this->sendError(Response::S400_BAD_REQUEST, 'Param `email` expected.');
            }

        } elseif ($action === 'confirm' && $this->httpRequest->getMethod() === Request::GET) {
            $confirm = new Confirm($this->connection);
            $email = $this->httpRequest->getQuery('email');
            $hash = $this->httpRequest->getQuery('hash');
            if ($email && $hash) {
                $confirmed = $confirm->confirm($email, $hash);
                if ($confirmed) {
                    return $this->sendOk(Response::S200_OK, 'Confirmed');
                } else {
                    return $this->sendError(Response::S400_BAD_REQUEST, null);
                }
            } else {
                return $this->sendError(Response::S400_BAD_REQUEST, null);
            }
        } elseif ($action === 'unsubscribe' && $this->httpRequest->getMethod() === Request::POST) {
            $confirm = new Confirm($this->connection);
            $email = $this->httpRequest->getPost('email');
            $hash = $this->httpRequest->getPost('hash');
            if ($email && $hash) {
                $confirmed = $confirm->unsubscribe($email, $hash);
                if ($confirmed) {
                    return $this->sendOk(Response::S200_OK, 'Unsubscribed');
                } else {
                    return $this->sendError(Response::S400_BAD_REQUEST, null);
                }
            } else {
                return $this->sendError(Response::S400_BAD_REQUEST, null);
            }
        } elseif ($action === 'send') {
            $body = $this->httpRequest->getRawBody();
            $bodyArr = json_decode($body, true);
            $netteResponse = new Response();
            $response = new \app\RestApi\Response($netteResponse);

            if (isset($bodyArr[0]['data']['validationCode'])) {
                $response->getResponse()->setCode(200);
                $response->setBody(Json::encode(['ValidationResponse' => $bodyArr[0]['data']['validationCode']]));
            }

            if (isset($bodyArr[0]['data']['email'])) {
                $email = $bodyArr[0]['data']['email'];
                $hash = $bodyArr[0]['data']['hash'];
                $tags = $bodyArr[0]['data']['tags'];
                $klerk = new SendToKlerk($this->connection);
                if ($hash === Hash::getHash($email)) {
                    $response->getResponse()->setCode(200);
                    $klerk->sendSingle(new Confirm($this->connection), $email, $tags);
                } else {
                    $response->getResponse()->setCode(Response::S406_NOT_ACCEPTABLE);
                }
            }

            return $response;

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
