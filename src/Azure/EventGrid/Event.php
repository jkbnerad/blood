<?php
declare(strict_types = 1);

namespace app\Azure\EventGrid;


use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\RequestOptions;
use function Sentry\captureException;

class Event
{

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function send(string $email, ?string $tags = null): bool
    {
        $body = [
            [
                'id' => getmyuid(),
                'eventType' => 'recordInserted',
                'subject' => 'damekrev/sendemail',
                'eventTime' => date(DATE_ATOM),
                'data' => [
                    'email' => $email,
                    'hash' => Hash::getHash($email),
                    'tags' => $tags
                ],
                'dataVersion' => '1.0'
            ]
        ];

        try {
            $response = $this->client->post('', [RequestOptions::JSON => $body]);
            if ($response->getStatusCode() === 200) {
                return true;
            }
        } catch (BadResponseException | ConnectException $e) {
            captureException($e);
        }

        return false;
    }


}
