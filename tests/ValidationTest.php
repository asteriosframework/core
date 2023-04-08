<?php declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Validation;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    /**
     * @test
     * @dataProvider run_validate_email_provider
     */
    public function run_validate_email(string $input, bool $expected_value): void
    {
        $field = 'email';
        $rules = [Validation::RULE_EMAIL];

        $result = (new Validation())->run($input, $field, $rules);

        self::assertEquals($expected_value, $result);
    }

    // Provider

    public function run_validate_email_provider(): array
    {
        return [
            ['john.doe@domain.tld', true],
            ['john_doe.tld', false],
        ];
    }
}
