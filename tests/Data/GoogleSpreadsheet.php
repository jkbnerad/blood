<?php
declare(strict_types = 1);

namespace tests\Data;

use app\Config;
use app\HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class GoogleSpreadsheet extends TestCase
{
    /**
     * @test
     * @throws \Exception
     */
    public function saveCsvToJson(): void
    {
        $content = implode(PHP_EOL, ["\"col 1\",\"col 2\"", "1,2", "3,4"]);
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar', 'Content-Type' => 'text/csv'], $content),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new HttpClient(['handler' => $handlerStack]);

        $tempFile = tempnam(sys_get_temp_dir(), 'gs');
        if ($tempFile) {
            $sheet = new \app\Data\GoogleSpreadsheet(new Config(), $client);
            $sheet->saveCsvToJson($tempFile);
            $dataFromFile = file_get_contents($tempFile);
            if ($dataFromFile) {
                $data = json_decode($dataFromFile, true);
                $expected = [
                    [
                        'col 1' => '1',
                        'col 2' => '2',
                    ],
                    [
                        'col 1' => '3',
                        'col 2' => '4',
                    ],
                ];
                self::assertSame($expected, $data['data']);
                self::assertArrayHasKey('expire', $data);
                self::assertArrayHasKey('lastUpdate', $data);
            } else {
                throw new \RuntimeException('Output is empty.');
            }
        } else {
            throw new \RuntimeException('Tempfile expected.');
        }

        @unlink($tempFile);
    }
}
