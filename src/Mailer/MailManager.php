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
     * @param string $envFile
     * @throws MailServiceException
     */
    private function __construct(array $templates, string $envFile)
    {
        $this->mailService = MailService::getInstance($envFile);
        $this->templates = $templates;
    }

    /**
     * @param array $templates
     * @param string $envFile
     * @return MailManager
     * @throws MailServiceException
     */
    public static function getInstance(array $templates, string $envFile = '.env'): MailManager
    {
        if (self::$instance === null)
        {
            self::$instance = new MailManager($templates, $envFile);
        }
        return self::$instance;
    }

    /**
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

        // Subject ggf. mit Platzhaltern ersetzen
        $subject = $this->replacePlaceholders($tpl['subject'], $context);
        $templateFile = $tpl['template'];

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

    /**
     * @param string $key
     * @param string $templateFile
     * @param string $subject
     * @return void
     */
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
                $text = str_replace('{{' . $key . '}}', $value, $text);
            }
        }
        return $text;
    }

    /**
     * @param string $msg
     * @return void
     */
    protected function logError(string $msg): void
    {
        Logger::forge()->error($msg);
    }
}
