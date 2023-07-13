<?php declare(strict_types=1);

namespace Asterios\Test\athene\TestData\Models;

use Asterios\Core\Athene\Model;
use Asterios\Core\Athene\Traits\Validations\CheckEmailAddress;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Table(name: 'users')]
class User extends Model
{
    use CheckEmailAddress;

    protected array $validators = [
        'checkEmail'
    ];


    #[Id]
    #[Column(type: Types::INTEGER)]
    #[GeneratedValue]
    protected $id;

    #[Column(type: Types::STRING, length: 254, unique: true, nullable: true)]
    protected $email;

    #[Column(type: Types::STRING, length: 256, nullable: false)]
    protected $password;


    protected function checkEmail(array &$errors): void
    {
        /** @var  bool $result */
        $result = $this->isEmail($this->email);

        if ($result !== true) {
            $errors[] = $this->email . ' is not an RFC compliant address.';
        }
    }
}