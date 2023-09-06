<?php declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\File;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @runTestsInSeparateProcesses
 */
class FileTest extends MockeryTestCase
{
    /** @var File */
    protected $testedClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testedClass = new File();
    }

    /**
     * @test
     * @dataProvider fileExtensionFromMimeTypeProvider
     */
    public function fileExtensionFromMimeType($mimeType, $expected): void
    {
        $actual = $this->testedClass->fileExtensionFromMimeType($mimeType);

        self::assertEquals($expected, $actual);
    }

    // Provider

    public static function fileExtensionFromMimeTypeProvider(): array
    {
        return [
            ['application/pdf', 'pdf'],
            ['gibtEsNicht', ''],
        ];
    }
}