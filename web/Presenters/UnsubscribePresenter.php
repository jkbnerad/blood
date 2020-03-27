<?php
declare(strict_types = 1);

namespace web\Presenters;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\RequestOptions;
use Nette\Application\UI\Presenter;

class UnsubscribePresenter extends Presenter
{

    protected function startup(): void
    {
        parent::startup();
    }

    public function actionDefault(string $email, string $hash): void
    {
        $client = new Client(['timeout' => 30]);
        $status = false;
        try {
            $response = $client->post('https://krev-dev.onw.cz/api/email/unsubscribe/', [RequestOptions::FORM_PARAMS => ['email' => $email, 'hash' => $hash]]);

            if ($response->getStatusCode() === 200) {
                $status = true;
            }
        } catch (BadResponseException $e) {
            if ($e->getCode() < 500) {
                $status = true;
            } else {
                $status = false;
            }
        }

        $this->template->status = $status;
    }
}
