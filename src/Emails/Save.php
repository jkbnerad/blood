<?php
declare(strict_types = 1);

namespace app\Emails;

use app\Database\Connection;
use app\Emails\Exceptions\EmailExistsException;
use app\Emails\Exceptions\EmailHasWrongFormatException;
use Nette\Utils\Validators;

class Save
{
    private const EMAIL_HASH_SALT = 'covid-19-2020';
    /**
     * @var \Dibi\Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection->getConnection();
    }

    public function save(string $email, ?string $tag): bool
    {
        if (Validators::isEmail($email) && strlen($email) <= 255 && ($tag === null || strlen($tag) < 255)) {
            $this->connection->begin();
            $exists = $this->connection->query('SELECT COUNT(*) FROM `Email` WHERE `emailHash`=?', $this->connection::expression('UNHEX(?)',$this->getEmailHash($email)))->fetchSingle();
            if ($exists === 0) {
                $sql = 'INSERT INTO `Email`';
                $this->connection->query($sql, ['email' => $email, 'tags' => $tag, 'emailHash' => $this->connection::expression('UNHEX(?)', $this->getEmailHash($email))]);
                $this->connection->commit();
                return true;
            } else {
                throw new EmailExistsException(sprintf('Email %s exists.', $email));
            }
        } else {
            throw new EmailHasWrongFormatException('Correct email address expected.');
        }
    }

    public function getEmailHash(string $email): string
    {
        return sha1($email . self::EMAIL_HASH_SALT);
    }

}