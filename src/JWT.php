<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Exception\JWTException;
use Asterios\Core\Interfaces\JWTInterface;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;

class JWT implements JWTInterface
{
    protected string|null $secretKey;
    protected string $algorithm = 'HS256';
    protected int|null $issuedAt = null;
    protected int $expire = 3600;
    protected array $decodedData;
    protected object $decoded;

    /**
     * @inheritDoc
     */
    public function generate(array $data): string
    {
        if (empty($this->secretKey))
        {
            throw new JWTException('JWT Secret Key is empty!');
        }

        if (null === $this->issuedAt)
        {
            $this->setIssuedAt();
        }

        $payload = [
            'iat' => $this->issuedAt,
            'exp' => $this->issuedAt + $this->expire,
            'data' => $data,
        ];

        return FirebaseJWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * @inheritDoc
     */
    public function validate(string $token): bool
    {
        try
        {
            $jwt = FirebaseJWT::decode($token, new Key($this->secretKey, $this->algorithm));

            $this->decoded = $jwt;
            $this->decodedData = (array)$jwt->data;

            return true;
        }
        catch (ExpiredException|Exception)
        {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function setSecretKey(string $secretKey): self
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setIssuedAt(int|null $issuedAt = null): self
    {
        $this->issuedAt = $issuedAt ?? time();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setExpire(int $seconds): self
    {
        $this->expire = $seconds;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAlgorithm(string $algorithm): self
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDecodedData(): array
    {
        return $this->decodedData;
    }

    /**
     * @inheritDoc
     */
    public function getBearerToken(?array $headers = null): string|null
    {
        if (null === $headers)
        {
            $headers = getallheaders();
        }

        if (isset($headers['Authorization']) && preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches))
        {
            return $matches[1];
        }

        return null;
    }

    public function getDecoded(): object
    {
        return $this->decoded;
    }
}