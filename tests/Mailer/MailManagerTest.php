<?php declare(strict_types=1);

namespace Asterios\Test\Mailer;

use Asterios\Core\Contracts\Mailer\MailServiceInterface;
use Asterios\Core\Exception\LoggerException;
use Asterios\Core\Exception\MailServiceException;
use Asterios\Core\Mailer\MailManager;
use PHPUnit\Framework\TestCase;

final class MailManagerTest extends TestCase
{
    private TestMailManagerDouble $manager;
    private TestMailServiceDouble $mailService;

    /**
     * @throws MailServiceException
     */
    protected function setUp(): void
    {
        $this->manager = TestMailManagerDouble::createManager();

        $this->mailService = new TestMailServiceDouble();

        $this->manager->injectMailService($this->mailService);
    }

    public function testSendText(): void
    {
        $this->mailService->returnValue = true;

        self::assertTrue(
            $this->manager->sendText(
                'john@example.com',
                'Subject',
                'Hello World'
            )
        );

        self::assertSame('john@example.com', $this->mailService->lastCall['to']);
        self::assertSame('Subject', $this->mailService->lastCall['subject']);
        self::assertNull($this->mailService->lastCall['template']);
        self::assertSame([], $this->mailService->lastCall['context']);
        self::assertNull($this->mailService->lastCall['html']);
        self::assertSame('Hello World', $this->mailService->lastCall['text']);
        self::assertSame([], $this->mailService->lastCall['attachments']);
    }

    public function testSendTextReturnsFalse(): void
    {
        $this->mailService->returnValue = false;

        self::assertFalse(
            $this->manager->sendText(
                'john@example.com',
                'Subject',
                'Hello World'
            )
        );
    }

    /**
     * @return void
     * @throws LoggerException
     * @throws MailServiceException
     */
    public function testSendTemplate(): void
    {
        $this->manager = TestMailManagerDouble::createManager([
            'welcome' => [
                'template' => 'welcome.twig',
                'subject' => 'Welcome'
            ]
        ]);

        $this->manager->injectMailService($this->mailService);

        $this->mailService->returnValue = true;

        self::assertTrue(
            $this->manager->sendTemplate(
                'john@example.com',
                'welcome'
            )
        );

        self::assertSame('john@example.com', $this->mailService->lastCall['to']);
        self::assertSame('Welcome', $this->mailService->lastCall['subject']);
        self::assertSame('welcome.twig', $this->mailService->lastCall['template']);
        self::assertSame([], $this->mailService->lastCall['context']);
        self::assertNull($this->mailService->lastCall['html']);
        self::assertNull($this->mailService->lastCall['text']);
    }

    /**
     * @return void
     * @throws MailServiceException
     * @throws LoggerException
     */
    public function testSendTemplateReplacesPlaceholders(): void
    {
        $this->manager = TestMailManagerDouble::createManager([
            'welcome' => [
                'template' => 'welcome.twig',
                'subject' => 'Hello {{name}}'
            ]
        ]);

        $this->manager->injectMailService($this->mailService);

        self::assertTrue(
            $this->manager->sendTemplate(
                'john@example.com',
                'welcome',
                [
                    'name' => 'John'
                ]
            )
        );

        self::assertSame(
            'Hello John',
            $this->mailService->lastCall['subject']
        );

        self::assertSame(
            ['name' => 'John'],
            $this->mailService->lastCall['context']
        );
    }

    /**
     * @return void
     * @throws LoggerException
     */
    public function testSendTemplateReturnsFalseIfTemplateMissing(): void
    {
        self::assertFalse(
            $this->manager->sendTemplate(
                'john@example.com',
                'unknown'
            )
        );
    }

    /**
     * @return void
     * @throws LoggerException
     */
    public function testRegisterTemplate(): void
    {
        $this->manager->registerTemplate(
            'welcome',
            'welcome.twig',
            'Welcome {{name}}'
        );

        self::assertTrue(
            $this->manager->sendTemplate(
                'john@example.com',
                'welcome',
                [
                    'name' => 'John'
                ]
            )
        );

        self::assertSame(
            'Welcome John',
            $this->mailService->lastCall['subject']
        );

        self::assertSame(
            'welcome.twig',
            $this->mailService->lastCall['template']
        );
    }

    /**
     * @throws MailServiceException
     */
    public function testGetInstanceReturnsMailManager(): void
    {
        $manager = TestMailManagerDouble::getInstance();

        $this->assertInstanceOf(
            TestMailManagerDouble::class,
            $manager
        );
    }

    /**
     * @throws MailServiceException
     */
    public function testGetInstanceReturnsSameInstance(): void
    {
        $first = TestMailManagerDouble::getInstance();
        $second = TestMailManagerDouble::getInstance();

        $this->assertSame($first, $second);
    }
}

final class TestMailManagerDouble extends MailManager
{
    protected function initializeMailService(): void
    {
    }

    public function injectMailService(
        MailServiceInterface $mailService
    ): void {
        $this->setMailService($mailService);
    }

    protected function logError(string $msg): void
    {
    }
}

final class TestMailService implements MailServiceInterface
{
    public bool $returnValue = true;

    public array $lastCall = [];

    public function send(
        string|array $to,
        string $subject,
        ?string $template = null,
        array $context = [],
        ?string $htmlBody = null,
        ?string $plainText = null,
        array $attachments = []
    ): bool {
        $this->lastCall = [
            'to'          => $to,
            'subject'     => $subject,
            'template'    => $template,
            'context'     => $context,
            'html'        => $htmlBody,
            'text'        => $plainText,
            'attachments' => $attachments,
        ];

        return $this->returnValue;
    }
}