<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

use Asterios\Core\Exception\JWTException;

interface JWTInterface
{
    /**
     * @param array $data
     * @return string
     * @throws JWTException
     */
    public function generate(array $data): string;

    /**
     * @param string $token
     * @return bool
     */
    public function validate(string $token): bool;

    /**
     * @param string $secretKey
     * @return self
     */
    public function setSecretKey(string $secretKey): self;

    /**
     * @param int|null $issuedAt
     * @return self
     */
    public function setIssuedAt(int|null $issuedAt = null): self;

    /**
     * @param int $seconds
     * @return self
     */
    public function setExpire(int $seconds): self;

    /**
     * @param string $algorithm
     * @return self
     */
    public function setAlgorithm(string $algorithm): self;

    /**
     * @return array
     */
    public function getDecodedData(): array;

    /**
     * @param array|null $headers
     * @return string|null
     */
    public function getBearerToken(?array $headers = null): string|null;

    /**
     * @return object
     */
    public function getDecoded(): object;
}
