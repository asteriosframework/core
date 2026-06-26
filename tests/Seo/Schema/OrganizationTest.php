<?php

declare(strict_types=1);

namespace Asterios\Test\Seo\Schema;

use Asterios\Core\Seo\Schema\Enums\Data\OrganizationData;
use PHPUnit\Framework\TestCase;

class OrganizationTest extends TestCase
{
    public function test_build_returns_schema(): void
    {
        $data = new OrganizationData(
            name: 'Asterios',
            url: 'https://asteriosphp.de',
            email: 'info@test.de',
            telephone: '123456',
            logo: 'https://asteriosphp.de/logo.svg',
        );

        $schema = new Organization($data);

        $this->assertSame([
            '@type' => 'Organization',
            'name' => 'Asterios',
            'url' => 'https://asteriosphp.de',
            'email' => 'info@test.de',
            'telephone' => '123456',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => 'https://asteriosphp.de/logo.svg',
            ],
        ], $schema->build());
    }
}