<?php declare(strict_types=1);

// namespace Asterios\Core
// {
//     function filemtime(): int
//     {
//         return 12345678;
//     }
// }

namespace Asterios\Test
{

    use Asterios\Core\Assets;
    use Asterios\Core\Dto\AssetsDto;
    use Asterios\Core\File;
    use Mockery as m;
    use Mockery\Adapter\Phpunit\MockeryTestCase;
    use org\bovigo\vfs\vfsStream;
    use org\bovigo\vfs\vfsStreamDirectory;
    use org\bovigo\vfs\vfsStreamWrapper;

    /**
     * @runTestsInSeparateProcesses
     */
    class AssetsTest extends MockeryTestCase
    {
        protected vfsStreamDirectory $root;
        protected array $structure = [
            'style.css' => '/* some CSS style content */',
            'script.js' => '/* some JavaScript content */',
        ];

        public function setUp(): void
        {
            parent::setUp();

            vfsStreamWrapper::register();
            $this->root = vfsStream::setup('folder', null, $this->structure);
        }

        public function tearDown(): void
        {
            m::close();
        }

        /**
         * @test
         * @dataProvider css_provider
         * @param AssetsDto $dto
         * @param bool $file_exists
         * @param false|string $expected_value
         */
        public function css(AssetsDto $dto, bool $file_exists, $expected_value): void
        {
            $file_mock = m::mock('alias:' . File::class);
            $file_mock->shouldReceive('forge')
                ->andReturnSelf();
            $file_mock->shouldReceive('file_exists')
                ->andReturn($file_exists);

            $result = Assets::css($dto);

            self::assertEquals($expected_value, $result);
        }

        /**
         * @test
         * @dataProvider js_provider
         * @param AssetsDto $dto
         * @param bool $file_exists
         * @param false|string $expected_value
         */
        public function js(AssetsDto $dto, bool $file_exists, $expected_value): void
        {
            $file_mock = m::mock('alias:' . File::class);
            $file_mock->shouldReceive('forge')
                ->andReturnSelf();
            $file_mock->shouldReceive('file_exists')
                ->andReturn($file_exists);

            $result = Assets::js($dto);

            self::assertEquals($expected_value, $result);
        }

        /**
         * @test
         * @dataProvider img_provider
         * @param AssetsDto $dto
         * @param bool $file_exists
         * @param false|string $expected_value
         */
        public function img(AssetsDto $dto, bool $file_exists, $expected_value): void
        {
            $file_mock = m::mock('alias:' . File::class);
            $file_mock->shouldReceive('forge')
                ->andReturnSelf();
            $file_mock->shouldReceive('file_exists')
                ->andReturn($file_exists);

            $result = Assets::img($dto);

            self::assertEquals($expected_value, $result);
        }

        /**
         * @test
         * @dataProvider favicon_provider
         * @param AssetsDto $dto
         * @param bool $file_exists
         * @param false|string $expected_value
         */
        public function favicon(AssetsDto $dto, bool $file_exists, $expected_value): void
        {
            $file_mock = m::mock('alias:' . File::class);
            $file_mock->shouldReceive('forge')
                ->andReturnSelf();
            $file_mock->shouldReceive('file_exists')
                ->andReturn($file_exists);

            $result = Assets::favicon($dto);

            self::assertEquals($expected_value, $result);
        }

        /**
         * @test
         * @dataProvider forge_provider
         * @param AssetsDto $dto
         * @param bool $file_exists
         * @param false|string $expected_value
         */
        public function forge(AssetsDto $dto, bool $file_exists, $expected_value): void
        {
            $file_mock = m::mock('alias:' . File::class);
            $file_mock->shouldReceive('forge')
                ->andReturnSelf();
            $file_mock->shouldReceive('file_exists')
                ->andReturn($file_exists);

            $result = Assets::forge($dto);

            self::assertEquals($expected_value, $result);
        }

        ########## Provider ##########

        public function css_provider(): array
        {
            $this->root = vfsStream::setup('folder', null, $this->structure);
            $styleCssMtime = $this->root->getChild('style.css')->filemtime();

            return [
                'DOCTYPE_HTML4' => [
                    (new AssetsDto())->set_file('style.css')
                        ->set_path($this->root->url())
                        ->set_document_type(Assets::DOCTYPE_HTML4),
                    true,
                    '<link rel="stylesheet" type="text/css" href="vfs://folder/style.css?' . $this->root->getChild('style.css')->filemtime() . '">' . PHP_EOL,
                ],
                'DOCTYPE_HTML5' => [
                    (new AssetsDto())->set_file('style.css')
                        ->set_path($this->root->url())
                        ->set_document_type(Assets::DOCTYPE_HTML5),
                    true,
                    '<link rel="stylesheet" type="text/css" href="vfs://folder/style.css?' . $this->root->getChild('style.css')->filemtime() . '">' . PHP_EOL,
                ],
                'DOCTYPE_XHTML' => [
                    (new AssetsDto())->set_file('style.css')
                        ->set_path($this->root->url())
                        ->set_document_type(Assets::DOCTYPE_XHTML),
                    true,
                    '<link rel="stylesheet" type="text/css" href="vfs://folder/style.css?' . $this->root->getChild('style.css')->filemtime() . '"/>' . PHP_EOL,
                ],
                'DOCTYPE_HTML5 scripts.js' => [
                    (new AssetsDto())->set_file('scripts.js')
                        ->set_path($this->root->url())
                        ->set_document_type(Assets::DOCTYPE_HTML5),
                    true,
                    false,
                ],
                'DOCTYPE_HTML5 false' => [
                    (new AssetsDto())->set_file('styles.css')
                        ->set_path($this->root->url())
                        ->set_document_type(Assets::DOCTYPE_HTML5),
                    false,
                    false,
                ],
            ];
        }

        public function js_provider(): array
        {
            return [
                [
                    (new AssetsDto)->set_file('lu.js')
                        ->set_path('folder'),
                    true,
                    '<script type="text/javascript" src="folder/lu.js?12345678"></script>' . PHP_EOL,
                ],
                [
                    (new AssetsDto)->set_file('lu.css')
                        ->set_path('folder'),
                    true,
                    false,
                ],
                [
                    (new AssetsDto)->set_file('lu.js')
                        ->set_path('folder'),
                    false,
                    false,
                ],
            ];
        }

        public function img_provider(): array
        {
            return [
                [
                    (new AssetsDto())->set_file('lu.css')
                        ->set_path('folder'),
                    true,
                    false,
                ],
                [
                    (new AssetsDto())->set_file('lu.png')
                        ->set_path('folder'),
                    false,
                    false,
                ],
                [
                    (new AssetsDto())->set_file('lu.png')
                        ->set_path('folder')
                        ->set_css_classname('img_css')
                        ->set_document_type(Assets::DOCTYPE_HTML4),
                    true,
                    '<img src="folder/lu.png?12345678" class="img_css">' . PHP_EOL,
                ],
                [
                    (new AssetsDto())->set_file('lu.png')
                        ->set_path('folder')
                        ->set_css_classname('img_css')
                        ->set_document_type(Assets::DOCTYPE_HTML5),
                    true,
                    '<img src="folder/lu.png?12345678" class="img_css">' . PHP_EOL,
                ],
                [
                    (new AssetsDto())->set_file('lu.png')
                        ->set_path('folder')
                        ->set_css_classname('img_css')
                        ->set_document_type(Assets::DOCTYPE_XHTML),
                    true,
                    '<img src="folder/lu.png?12345678" class="img_css"/>' . PHP_EOL,
                ],
            ];
        }

        public function favicon_provider(): array
        {
            return [
                [
                    (new AssetsDto)->set_file('lu.css')
                        ->set_path('folder'),
                    true,
                    false,
                ],
                [
                    (new AssetsDto)->set_file('lu.ico')
                        ->set_path('folder'),
                    false,
                    false,
                ],
                [
                    (new AssetsDto)->set_file('lu.ico')
                        ->set_path('folder')
                        ->set_document_type(Assets::DOCTYPE_HTML4),
                    true,
                    '<link rel="shortcut icon" type="image/x-icon" href=folder/lu.ico">' . PHP_EOL,
                ],
                [
                    (new AssetsDto)->set_file('lu.ico')
                        ->set_path('folder')
                        ->set_document_type(Assets::DOCTYPE_HTML5),
                    true,
                    '<link rel="shortcut icon" type="image/x-icon" href=folder/lu.ico">' . PHP_EOL,
                ],
                [
                    (new AssetsDto)->set_file('lu.ico')
                        ->set_path('folder')
                        ->set_document_type(Assets::DOCTYPE_XHTML),
                    true,
                    '<link rel="shortcut icon" type="image/x-icon" href=folder/lu.ico"/>' . PHP_EOL,
                ],
            ];
        }

        public function forge_provider(): array
        {
            return [
                [
                    (new AssetsDto())->set_file('style.css')
                        ->set_path('folder')
                        ->set_document_type(Assets::DOCTYPE_HTML5),
                    true,
                    '<link rel="stylesheet" type="text/css" href="folder/style.css?12345678">' . PHP_EOL,
                ],
                [
                    (new AssetsDto)->set_file('lu.js')
                        ->set_path('folder'),
                    true,
                    '<script type="text/javascript" src="folder/lu.js?12345678"></script>' . PHP_EOL,
                ],
                [
                    (new AssetsDto())->set_file('lu.png')
                        ->set_path('folder')
                        ->set_css_classname('img_css')
                        ->set_document_type(Assets::DOCTYPE_HTML5),
                    true,
                    '<img src="folder/lu.png?12345678" class="img_css">' . PHP_EOL,
                ],
                [
                    (new AssetsDto)->set_file('lu.ico')
                        ->set_path('folder')
                        ->set_document_type(Assets::DOCTYPE_HTML5),
                    true,
                    '<link rel="shortcut icon" type="image/x-icon" href=folder/lu.ico">' . PHP_EOL,
                ],
            ];
        }
    }
}



