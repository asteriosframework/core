<?php

namespace Asterios\Core\Mailer;

use Asterios\Core\Contracts\Mailer\MailServiceInterface;
use Asterios\Core\Exception\LoggerException;
use Asterios\Core\Exception\MailServiceException;
use Asterios\Core\Logger;

class MailManager
{
    private static ?MailManager $instance = null;
    private MailServiceInterface $mailService;
    private array $templates;

    /**
     * @param array $templates
     * @throws MailServiceException
     */
    protected function __construct(array $templates)
    {
        $this->initializeMailService();
        $this->templates = $templates;
    }

    /**
     * @param array $templates
     * @return MailManager
     * @throws MailServiceException
     */
    public static function getInstance(array $templates = []): self
    {
        if (self::$instance === null)
        {
            self::$instance = static::create($templates);
        }
        return self::$instance;
    }

    /**
     * Sendet eine Template-Mail
     *
     * @param string|array $to
     * @param string $templateKey
     * @param array $context
     * @param array $attachments
     * @return bool
     * @throws LoggerException
     */
    public function sendTemplate(string|array $to, string $templateKey, array $context = [], array $attachments = []): bool
    {
        if (!isset($this->templates[$templateKey]))
        {
            $this->logError('MailManager: Template '.$templateKey.' not found.');
            return false;
        }

        $tpl = $this->templates[$templateKey];

        $subject = $this->replacePlaceholders($tpl['subject'], $context);
        $templateFile = $tpl['template'] ?? null;

        return $this->mailService->send($to, $subject, $templateFile, $context, null, null, $attachments);
    }

    /**
     * @param string|array $to
     * @param string $subject
     * @param string $text
     * @param array $attachments
     * @return bool
     */
    public function sendText(string|array $to, string $subject, string $text, array $attachments = []): bool
    {
        return $this->mailService->send($to, $subject, null, [], null, $text, $attachments);
    }

    public function registerTemplate(string $key, string $templateFile, string $subject): void
    {
        $this->templates[$key] = [
            'template' => $templateFile,
            'subject'  => $subject
        ];
    }

    /**
     * @param string $text
     * @param array $context
     * @return string
     */
    private function replacePlaceholders(string $text, array $context): string
    {
        foreach ($context as $key => $value)
        {
            if (is_scalar($value))
            {
                $text = str_replace('{{'.$key.'}}', $value, $text);
            }
        }
        return $text;
    }

    /**
     * @param string $msg
     * @return void
     * @throws LoggerException
     */
    protected function logError(string $msg): void
    {
        Logger::forge()->error($msg);
    }

    /**
     * @param array $templates
     * @return static
     * @throws MailServiceException
     */
    protected static function create(array $templates): static
    {
        return new static($templates);
    }

    /**
     * @param MailServiceInterface $mailService
     * @return void
     */
    protected function setMailService(MailServiceInterface $mailService): void
    {
        $this->mailService = $mailService;
    }

    /**
     * @param array $templates
     * @return static
     * @throws MailServiceException
     */
    public static function createManager(array $templates = []): static
    {
        return static::create($templates);
    }

    /**
     * @return void
     * @throws MailServiceException
     */
    protected function initializeMailService(): void
    {
        $this->mailService = MailService::getInstance();
    }
}
