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

    public function save(string $email, ?string $tag, ?string $phone = null, ?string $bloodType = null): bool
    {
        if ($this->validateInputs($email, $tag, $phone, $bloodType) === true) {
            $this->connection->begin();
            $exists = $this->connection->query('SELECT COUNT(*) FROM `Email` WHERE `emailHash`=?',
                $this->connection::expression('UNHEX(?)', $this->getEmailHash($email)))->fetchSingle();
            if ($exists === 0) {
                $sql = 'INSERT INTO `Email`';
                $data = [
                    'email' => $email,
                    'tags' => $tag,
                    'date' => date('Y-m-d'),
                    'emailHash' => $this->connection::expression('UNHEX(?)', $this->getEmailHash($email)),
                    'phone' => $phone,
                    'bloodType' => $bloodType
                ];
                $this->connection->query($sql, $data);
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

    private function validateInputs(string $email, ?string $tag, ?string $phone = null, ?string $bloodType = null): bool
    {
        if(!Validators::isEmail($email)) {
           return false;
        }

        if (strlen($email) > 255) {
            return false;
        }

        if($tag !== null && strlen($tag) > 255) {
            return false;
        }

        if ($phone !== null && strlen($phone) > 255) {
            return false;
        }

        if ($bloodType !== null && strlen($bloodType) > 255) {
            return false;
        }

        return true;

    }

}
