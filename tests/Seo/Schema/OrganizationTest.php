<?php

declare(strict_types=1);

namespace Asterios\Test\Seo\Schema\Node;

use Asterios\Core\Seo\Schema\Data\OrganizationData;
use Asterios\Core\Seo\Schema\Node\Organization;
use PHPUnit\Framework\TestCase;

final class OrganizationTest extends TestCase
{
    public function testBuildReturnsExpectedSchema(): void
    {
        $node = new Organization(new OrganizationData(
            name: 'Asterios',
            url: 'https://asteriosphp.de',
            email: 'info@asteriosphp.de',
            telephone: '+49 123456',
            logo: 'https://asteriosphp.de/logo.svg',
        ));

        $this->assertSame([
            '@type' => 'Organization',
            'name' => 'Asterios',
            'url' => 'https://asteriosphp.de',
            'email' => 'info@asteriosphp.de',
            'telephone' => '+49 123456',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => 'https://asteriosphp.de/logo.svg',
            ],
        ], $node->build());
    }

    public function testOptionalPropertiesAreNotRendered(): void
    {
        $node = new Organization(new OrganizationData(
            name: 'Asterios',
            url: 'https://asteriosphp.de',
        ));

        $result = $node->build();

        $this->assertArrayNotHasKey('email', $result);
        $this->assertArrayNotHasKey('telephone', $result);
        $this->assertArrayNotHasKey('logo', $result);
    }
}