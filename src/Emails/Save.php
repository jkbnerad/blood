<?php
declare(strict_types = 1);

namespace app\Emails;

use app\Database\Connection;
use app\Emails\Exceptions\BloodTypeHasWrongFormatException;
use app\Emails\Exceptions\EmailExistsException;
use app\Emails\Exceptions\EmailHasWrongFormatException;
use app\Emails\Exceptions\PhoneHasWrongFormatException;
use app\Emails\Exceptions\UnknownException;
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
        if ($this->checkInputs($email, $tag, $phone, $bloodType) === true) {
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
            throw new UnknownException('Unknown error.');
        }
    }

    public function getEmailHash(string $email): string
    {
        return sha1($email . self::EMAIL_HASH_SALT);
    }

    private function checkInputs(string $email, ?string $tag, ?string $phone = null, ?string $bloodType = null): bool
    {
        if (!Validators::isEmail($email)) {
            throw new EmailHasWrongFormatException('Email is empty or has wrong format.');
        }

        if (strlen($email) > 255) {
            throw new EmailHasWrongFormatException('Email is too long.');
        }

        if ($tag !== null && strlen($tag) > 255) {
            throw new EmailHasWrongFormatException('Tag is too long.');
        }

        if ($phone !== null && strlen($phone) > 255) {
            throw new PhoneHasWrongFormatException('Phone has wrong format.');
        }

        if ($bloodType !== null && strlen($bloodType) > 255) {
            throw new BloodTypeHasWrongFormatException('Blood type is too long.');
        }

        return true;
    }

}
