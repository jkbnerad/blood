<?php
declare(strict_types = 1);

namespace app;

use Nette\Neon\Neon;

class Config
{
    /**
     * @var array
     */
    private $data;

    private function load(): void
    {
        if ($this->data === null) {
            $config = __DIR__ . '/../configs/config.neon';
            $data = file_get_contents($config);
            if ($data) {
                $this->data = Neon::decode($data);
            }
        }
    }

    public function getGoogleSpreadsheetId(): ?string
    {
        $this->load();
        return $this->data['spreadsheet']['id'] ?? null;
    }


}
