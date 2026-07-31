<?php declare(strict_types=1);

namespace Asterios\Test\Mailer;

use Asterios\Core\Exception\MailServiceException;
use Asterios\Core\Mailer\MailService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class MailServiceTest extends TestCase
{
    private TestMailServiceDouble $mailService;

    protected function setUp(): void
    {
        $this->mailService = new TestMailServiceDouble();
    }

    public function testBuildEmailWithHtmlBody(): void
    {
        $email = $this->mailService->build(
            'john@example.com',
            'Test Subject',
            null,
            [],
            '<h1>Hello</h1><p>World</p>'
        );

        self::assertInstanceOf(Email::class, $email);

        self::assertSame('Test Subject', $email->getSubject());

        self::assertCount(1, $email->getTo());
        self::assertSame('john@example.com', $email->getTo()[0]->getAddress());

        self::assertSame('noreply@example.com', $email->getFrom()[0]->getAddress());
        self::assertSame('Asterios Test', $email->getFrom()[0]->getName());

        self::assertSame('<h1>Hello</h1><p>World</p>', $email->getHtmlBody());
        self::assertSame('HelloWorld', trim($email->getTextBody()));
    }

    public function testBuildEmailWithPlainTextOnly(): void
    {
        $email = $this->mailService->build(
            'john@example.com',
            'Test Subject',
            null,
            [],
            null,
            'Hello World'
        );

        self::assertNull($email->getHtmlBody());
        self::assertSame('Hello World', $email->getTextBody());
    }

    public function testBuildEmailUsesExplicitPlainTextInsteadOfStripTags(): void
    {
        $email = $this->mailService->build(
            'john@example.com',
            'Test Subject',
            null,
            [],
            '<h1>Hello</h1>',
            'Custom Text'
        );

        self::assertSame('<h1>Hello</h1>', $email->getHtmlBody());
        self::assertSame('Custom Text', $email->getTextBody());
    }

    public function testBuildEmailWithMultipleRecipients(): void
    {
        $email = $this->mailService->build(
            [
                'john@example.com',
                'jane@example.com',
            ],
            'Test Subject'
        );

        self::assertCount(2, $email->getTo());
        self::assertSame('john@example.com', $email->getTo()[0]->getAddress());
        self::assertSame('jane@example.com', $email->getTo()[1]->getAddress());
    }

    public function testBuildEmailUsesTwigTemplate(): void
    {
        $this->mailService->addTemplate(
            'mail.twig',
            '<h1>Hello Twig</h1>'
        );

        $email = $this->mailService->build(
            'john@example.com',
            'Test',
            'mail.twig'
        );

        self::assertSame(
            '<h1>Hello Twig</h1>',
            $email->getHtmlBody()
        );

        self::assertSame(
            'Hello Twig',
            trim($email->getTextBody())
        );
    }

    public function testBuildEmailUsesHtmlAndTextTemplates(): void
    {
        $this->mailService->addTemplate(
            'mail.html.twig',
            '<h1>Hello HTML</h1>'
        );

        $this->mailService->addTemplate(
            'mail.txt.twig',
            'Hello TEXT'
        );

        $email = $this->mailService->build(
            'john@example.com',
            'Test',
            'mail.twig'
        );

        self::assertSame(
            '<h1>Hello HTML</h1>',
            $email->getHtmlBody()
        );

        self::assertSame(
            'Hello TEXT',
            $email->getTextBody()
        );
    }

    public function testBuildEmailUsesHtmlTemplateOnly(): void
    {
        $this->mailService->addTemplate(
            'mail.html.twig',
            '<h1>Hello HTML</h1>'
        );

        $email = $this->mailService->build(
            'john@example.com',
            'Test',
            'mail.twig'
        );

        self::assertSame(
            '<h1>Hello HTML</h1>',
            $email->getHtmlBody()
        );

        self::assertSame(
            'Hello HTML',
            trim($email->getTextBody())
        );
    }

    public function testBuildEmailUsesTextTemplateOnly(): void
    {
        $this->mailService->addTemplate(
            'mail.txt.twig',
            'Hello TEXT'
        );

        $email = $this->mailService->build(
            'john@example.com',
            'Test',
            'mail.twig'
        );

        self::assertNull($email->getHtmlBody());

        self::assertSame(
            'Hello TEXT',
            $email->getTextBody()
        );
    }

    public function testSendReturnsTrue(): void
    {
        $mailer = $this->createMock(MailerInterface::class);

        $mailer
            ->expects($this->once())
            ->method('send');

        $this->mailService->injectMailer($mailer);

        self::assertTrue(
            $this->mailService->send(
                'john@example.com',
                'Test',
                null,
                [],
                '<h1>Hello</h1>'
            )
        );
    }

    public function testSendReturnsFalseOnMailerException(): void
    {
        $mailer = $this->createMock(MailerInterface::class);

        $mailer
            ->expects($this->once())
            ->method('send')
            ->willThrowException(new \RuntimeException('Test exception'));

        $this->mailService->injectMailer($mailer);

        self::assertFalse(
            $this->mailService->send(
                'john@example.com',
                'Test',
                null,
                [],
                '<h1>Hello</h1>'
            )
        );
    }

    public function testSendPassesEmailToMailer(): void
    {
        $mailer = $this->createMock(MailerInterface::class);

        $mailer
            ->expects($this->once())
            ->method('send')
            ->with(self::callback(static function (Email $email) {
                self::assertSame('Test', $email->getSubject());
                self::assertSame(
                    '<h1>Hello</h1>',
                    $email->getHtmlBody()
                );

                return true;
            }));

        $this->mailService->injectMailer($mailer);

        self::assertTrue(
            $this->mailService->send(
                'john@example.com',
                'Test',
                null,
                [],
                '<h1>Hello</h1>'
            )
        );
    }

    public function testSendAttachesFile(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'mail');
        file_put_contents($file, 'Hello Attachment');

        try
        {
            $mailer = $this->createMock(MailerInterface::class);

            $mailer
                ->expects($this->once())
                ->method('send')
                ->with(self::callback(static function (Email $email) {
                    self::assertCount(1, $email->getAttachments());

                    return true;
                }));

            $this->mailService->injectMailer($mailer);

            self::assertTrue(
                $this->mailService->send(
                    'john@example.com',
                    'Test',
                    null,
                    [],
                    '<h1>Hello</h1>',
                    null,
                    [$file]
                )
            );
        }
        finally
        {
            @unlink($file);
        }
    }

    public function testSendAttachesBinaryAttachment(): void
    {
        $mailer = $this->createMock(MailerInterface::class);

        $mailer
            ->expects($this->once())
            ->method('send')
            ->with(self::callback(static function (Email $email) {
                self::assertCount(1, $email->getAttachments());

                return true;
            }));

        $this->mailService->injectMailer($mailer);

        self::assertTrue(
            $this->mailService->send(
                'john@example.com',
                'Test',
                null,
                [],
                '<h1>Hello</h1>',
                null,
                [[
                    'content'  => 'Hello World',
                    'filename' => 'hello.txt',
                    'mime'     => 'text/plain',
                ]]
            )
        );
    }

    public function testSendIgnoresMissingAttachment(): void
    {
        $mailer = $this->createMock(MailerInterface::class);

        $mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(static function (Email $email) {
                self::assertCount(0, $email->getAttachments());

                return true;
            }));

        $this->mailService->injectMailer($mailer);

        $this->assertTrue(
            $this->mailService->send(
                'john@example.com',
                'Test',
                null,
                [],
                '<h1>Hello</h1>',
                null,
                ['/does/not/exist.txt']
            )
        );
    }

    /**
     * @return void
     * @throws MailServiceException
     */
    public function testGetInstanceReturnsMailService(): void
    {
        $service = TestMailServiceDouble::getInstance();

        $this->assertInstanceOf(TestMailServiceDouble::class, $service);
    }

    /**
     * @return void
     * @throws MailServiceException
     */
    public function testGetInstanceReturnsSameInstance(): void
    {
        $first = TestMailServiceDouble::getInstance();
        $second = TestMailServiceDouble::getInstance();

        $this->assertSame($first, $second);
    }
}

final class TestMailServiceDouble extends MailService
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function initializeEnv(string $envFile): void
    {
        // keine .env laden
    }

    protected function initializeMailer(): void
    {
        $this->fromAddress = 'noreply@example.com';
        $this->fromName = 'Asterios Test';

    }

    protected function initializeTwig(): void
    {
        // kein Twig
    }

    public function build(
        string|array $to,
        string $subject,
        ?string $template = null,
        array $context = [],
        ?string $htmlBody = null,
        ?string $plainText = null
    ): Email {
        return $this->buildEmail(
            $to,
            $subject,
            $template,
            $context,
            $htmlBody,
            $plainText
        );
    }

    private array $templates = [];
    private array $renderedTemplates = [];

    public function addTemplate(string $name, string $content): void
    {
        $this->templates[$name] = $content;
    }

    protected function templateExists(string $template): bool
    {
        return isset($this->templates[$template]);
    }

    protected function renderTemplate(string $template, array $context): ?string
    {
        return $this->templates[$template] ?? null;
    }

    public function injectMailer(MailerInterface $mailer): void
    {
        $this->setMailer($mailer);
    }

    protected function logError(string $msg): void
    {
    }
}