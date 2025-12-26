<?php declare(strict_types=1);

namespace Asterios\Core\GoogleAuthenticator\Factory;

use Asterios\Core\Dto\GoogleAuthenticator\GoogleAuthenticatorData;
use Asterios\Core\GoogleAuthenticator\GoogleAuthenticator;
use OTPHP\TOTP;

final class GoogleAuthenticatorFactory
{
    public static function create(GoogleAuthenticatorData $data): GoogleAuthenticator
    {
        $totp = TOTP::create(
            $data->secret,
            $data->period,
            $data->digest,
            $data->digits
        );

        $totp->setLabel($data->label);
        $totp->setIssuer($data->issuer);

        return new GoogleAuthenticator($totp);
    }
}
