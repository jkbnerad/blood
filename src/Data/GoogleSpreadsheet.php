<?php
declare(strict_types = 1);

namespace app\Data;

use app\Config;
use app\HttpClient;

class GoogleSpreadsheet
{
    private $jsonFileName = __DIR__ . '/../../data/blood.json';

    /**
     * @var Config
     */
    private $config;
    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __construct(Config $config, HttpClient $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
    }

    public function saveCsvToJson(?string $jsonFileName = null): void
    {
        $spreadSheetId = $this->config->getGoogleSpreadsheetId();
        if ($spreadSheetId) {

            $uri = sprintf('https://docs.google.com/spreadsheets/d/%s/export?format=csv', $spreadSheetId);

            $data = $this->httpClient->get($uri)->getBody()->getContents();

            $lines = explode(PHP_EOL, $data);
            $headers = $resultsData = $results = [];
            foreach ($lines as $i => $line) {
                $arr = str_getcsv($line);
                if ($i === 0) {
                    foreach ($arr as $col) {
                        $headers[] = $col;
                    }
                } else {
                    $tmp = [];
                    foreach ($arr as $key => $value) {
                        $tmp[$headers[$key]] = $value;
                    }
                    $resultsData[] = $tmp;
                }
            }

            if ($resultsData) {
                $results = ['data' => $resultsData, 'lastUpdate' => date(DATE_ATOM), 'expire' => date(DATE_ATOM, strtotime('+ 30 minutes'))];
                $jsonFileName = $jsonFileName ?: $this->jsonFileName;
                $output = fopen($jsonFileName, 'wb');
                if ($output) {
                    fwrite($output, json_encode($results, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE, 512));
                } else {
                    throw new \Exception('File does not open.');
                }
                fclose($output);
            }
        } else {
            throw new \Exception('Spreadsheet ID not exists.');
        }
    }
}
