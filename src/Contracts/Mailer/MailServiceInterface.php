<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Mailer;

interface MailServiceInterface
{
    /**
     * @param string|array $to
     * @param string $subject
     * @param string|null $template
     * @param array $context
     * @param string|null $htmlBody
     * @param string|null $plainText
     * @param array $attachments
     * @return bool
     */
    public function send(string|array $to, string $subject, ?string $template = null, array $context = [], ?string $htmlBody = null, ?string $plainText = null, array $attachments = []): bool;
}