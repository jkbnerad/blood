<?php
declare(strict_types=1);

namespace app\Azure\EventGrid;


class Hash
{
    public static function getHash(string $email): string
    {
        $iniConfig = parse_ini_file(__DIR__ . '/../../configs/azure.ini');
        return sha1($email . $iniConfig['EventGrid']['hashSalt']);
    }
}
