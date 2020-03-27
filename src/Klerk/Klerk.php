<?php
declare(strict_types=1);

namespace app\Klerk;

class Klerk
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function contacts(): Contacts
    {
        return new Contacts($this->httpClient);
    }

    public function contact(): Contact
    {
        return new Contact($this->httpClient);
    }

}
