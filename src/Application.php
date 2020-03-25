<?php
declare(strict_types=1);

namespace app;

use app\Database\Connection;
use app\RestApi\RestApi;
use Nette\Http\IRequest;

class Application
{
    /**
     * @var IRequest
     */
    private $httpRequest;

    public function __construct(IRequest $httpRequest)
    {
        $this->httpRequest = $httpRequest;
    }

    public function run(): void
    {
        $path = $this->getPath();
        if (isset($path[0]) && $path[0] === 'api') {
            $app = new RestApi(new Connection(), $this->httpRequest);
            $app->run();
        }
    }

    private function getPath(): array
    {
        return array_values(array_filter(explode('/', $this->httpRequest->getUrl()->getPath())));
    }
}
