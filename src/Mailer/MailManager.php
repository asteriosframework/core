<?php

namespace Asterios\Core\Mailer;

use Asterios\Core\Exception\MailServiceException;
use Asterios\Core\Logger;

class MailManager
{
    private static ?MailManager $instance = null;
    private MailService $mailService;
    private array $templates;

    /**
     * @param array $templates
     * @throws MailServiceException
     */
    private function __construct(array $templates)
    {
        $this->mailService = MailService::getInstance();
        $this->templates = $templates;
    }

    /**
     * @param array $templates
     * @return MailManager
     * @throws MailServiceException
     */
    public static function getInstance(array $templates = []): MailManager
    {
        if (self::$instance === null)
        {
            self::$instance = new MailManager($templates);
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
     */
    public function sendTemplate(
        string|array $to,
        string $templateKey,
        array $context = [],
        array $attachments = []
    ): bool {
        if (!isset($this->templates[$templateKey]))
        {
            $this->logError('MailManager: Template '.$templateKey.' not found.');
            return false;
        }

        $tpl = $this->templates[$templateKey];

        $subject = $this->replacePlaceholders($tpl['subject'], $context);
        $templateFile = $tpl['template'] ?? null;

        return $this->mailService->send(
            $to,
            $subject,
            $templateFile,
            $context,
            null,
            null,
            $attachments
        );
    }

    public function registerTemplate(string $key, string $templateFile, string $subject): void
    {
        $this->templates[$key] = [
            'template' => $templateFile,
            'subject'  => $subject
        ];
    }

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

    protected function logError(string $msg): void
    {
        Logger::forge()->error($msg);
    }
}
