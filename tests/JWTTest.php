<?php declare(strict_types=1);

namespace Asterios\Core
{
    /**
     * @return array|false
     */
    function getallheaders(): array|false
    {
        return ['Authorization' => 'Bearer 9876543210'];
    }
}

namespace Asterios\Test
{

    use Asterios\Core\Exception\JWTException;
    use Asterios\Core\JWT;
    use Mockery as m;
    use Mockery\Adapter\Phpunit\MockeryTestCase;

    class JWTTest extends MockeryTestCase
    {
        protected JWT $testedClass;

        protected function setUp(): void
        {
            parent::setUp();
            $this->testedClass = (new JWT);
        }

        protected function tearDown(): void
        {
            m::close();
        }

        /**
         * @test
         */
        public function generateWithException(): void
        {
            $this->expectException(JWTException::class);
            $this->testedClass->generate([]);
        }

        /**
         * @test
         */
        public function generateWithNoIssuedAt(): void
        {
            $actual = $this->testedClass
                ->setSecretKey('exampleSecretKey')
                ->generate([]);

            self::assertGreaterThanOrEqual(142, $actual);
        }

        /**
         * @test
         */
        public function generate(): void
        {

            $actual = $this->testedClass
                ->setIssuedAt(0)
                ->setSecretKey('exampleSecretKey')
                ->generate([]);

            self::assertEquals('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjAsImV4cCI6MzYwMCwiZGF0YSI6W119.8N4FnIowS8ZO3Ul5-9VKSXPFIIXpC0UbFUJeMHrhqHw',
                $actual);
        }

        /**
         * @test
         */
        public function generateWithExpire(): void
        {
            $actual = $this->testedClass
                ->setSecretKey('exampleSecretKey')
                ->setIssuedAt(0)
                ->setExpire(300)
                ->generate([]);

            self::assertEquals('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjAsImV4cCI6MzAwLCJkYXRhIjpbXX0.5O1cZCZwxI3_MmTCDEh5bepygLhB2uicYfq20gO_iwA',
                $actual);
        }

        /**
         * @test
         */
        public function generateWithDifferentHashMac512(): void
        {
            $actual = $this->testedClass
                ->setSecretKey('exampleSecretKey')
                ->setIssuedAt(0)
                ->setAlgorithm('HS512')
                ->generate([]);

            self::assertEquals('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjAsImV4cCI6MzYwMCwiZGF0YSI6W119.tB14Q44Z1fQr4YbwJA7BSypt5QjRBNOkOK_mZRuIPhUmJvcUR_SN04lQpjN-hGxYX0p0WSXxYc-Ttsi_VDZtpA',
                $actual);
        }

        /**
         * @test
         */
        public function validateFalse(): void
        {
            $token = $this->testedClass
                ->setSecretKey('exampleSecretKey')
                ->setExpire(0)
                ->generate([]);

            $actual = $this->testedClass->validate($token);
            self::assertFalse($actual);
        }

        /**
         * @test
         */
        public function validate(): void
        {
            $token = $this->testedClass
                ->setSecretKey('exampleSecretKey')
                ->setExpire(3600)
                ->generate([]);

            $actual = $this->testedClass->validate($token);
            self::assertTrue($actual);
        }

        /**
         * @test
         */
        public function getDecodedData(): void
        {
            $data = [
                'username' => 'john.doe',
            ];

            $token = $this->testedClass
                ->setSecretKey('exampleSecretKey')
                ->setExpire(3600)
                ->generate($data);

            $this->testedClass->validate($token);

            $actual = $this->testedClass->getDecodedData();
            self::assertEquals(['username' => 'john.doe'], $actual);

        }

        /**
         * @test
         */
        public function getBearerTokenWithAuthorizationInHeadersNull(): void
        {
            $actual = $this->testedClass->getBearerToken();

            self::assertEquals('9876543210',
                $actual);
        }

        /**
         * @test
         */
        public function getBearerTokenWithAuthorizationInHeaders(): void
        {
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer 0123456789',
            ];

            $actual = $this->testedClass->getBearerToken($headers);

            self::assertEquals('0123456789', $actual);
        }

        /**
         * @test
         */
        public function getBearerTokenWithoutAuthorizationInHeaders(): void
        {
            $headers = [];

            $actual = $this->testedClass->getBearerToken($headers);

            self::assertNull($actual);
        }

        /**
         * @test
         */
        public function getDecoded(): void
        {
            $data = [
                'username' => 'john.doe',
            ];

            $iat = time();
            $exp = $iat + 3600;

            $token = $this->testedClass
                ->setSecretKey('exampleSecretKey')
                ->setExpire(3600)
                ->generate($data);

            $this->testedClass->validate($token);

            $actual = $this->testedClass->getDecoded();

            self::assertEquals($iat, $actual->iat);
            self::assertEquals($exp, $actual->exp);
            self::assertEquals($data, (array)$actual->data);
        }
    }
}