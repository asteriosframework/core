<?php declare(strict_types=1);

namespace Asterios\Core\Mailer;

use Asterios\Core\Asterios;
use Asterios\Core\Contracts\Mailer\MailServiceInterface;
use Asterios\Core\Env;
use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Exception\MailServiceException;
use Asterios\Core\Logger;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment as Twig;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

class MailService implements MailServiceInterface
{
    private static ?MailService $instance = null;
    protected string $envFile = '.env';
    protected ?Env $env = null;
    protected ?MailerInterface $mailer = null;
    protected string $fromAddress;
    protected string $fromName;
    private ?Twig $twig = null;

    /**
     * @param string $envFile
     * @throws MailServiceException
     */
    protected function __construct(string $envFile = '.env')
    {
        $this->initializeEnv($envFile);
        $this->initializeMailer();
        $this->initializeTwig();
    }

    /**
     * @param string $envFile
     * @return MailService
     * @throws MailServiceException
     */
    public static function getInstance(string $envFile = '.env'): static
    {
        if (self::$instance === null)
        {
            self::$instance = static::create($envFile);
        }

        return self::$instance;
    }

    /**
     * @inheritDoc
     */
    public function send(string|array $to, string $subject, ?string $template = null, array $context = [], ?string $htmlBody = null, ?string $plainText = null, array $attachments = []): bool
    {
        try
        {
            $email = $this->buildEmail($to, $subject, $template, $context, $htmlBody, $plainText);

            foreach ($attachments as $attachment)
            {
                if (is_string($attachment) && file_exists($attachment))
                {
                    $email->attachFromPath($attachment);
                }
                elseif (is_array($attachment))
                {
                    $email->attach(
                        $attachment['content'],
                        $attachment['filename'] ?? 'attachment',
                        $attachment['mime'] ?? 'application/octet-stream'
                    );
                }
            }

            $this->mailer->send($email);
            return true;
        }
        catch (\Throwable $e)
        {
            $this->logError('MailService: Error during send e-mail:' . $e->getMessage());
            return false;
        }
    }

    protected function buildEmail(
        string|array $to,
        string $subject,
        ?string $template,
        array $context,
        ?string $htmlBody,
        ?string $plainText
    ): Email {
        $recipients = (array)$to;

        if ($template)
        {
            $base = preg_replace('/(\.html|\.txt)?\.twig$/', '', $template);

            $htmlTemplate = $base . '.html.twig';
            $textTemplate = $base . '.txt.twig';

            if ($this->templateExists($htmlTemplate))
            {
                $htmlBody = $this->renderTemplate($htmlTemplate, $context);
            }

            if ($this->templateExists($textTemplate))
            {
                $plainText = $this->renderTemplate($textTemplate, $context);
            }

            if (
                $htmlBody === null &&
                $plainText === null &&
                $this->templateExists($template)
            ) {
                $htmlBody = $this->renderTemplate($template, $context);
            }
        }

        if (class_exists(TemplatedEmail::class))
        {
            $email = $this->getTemplatedEmail();
        }
        else
        {
            $email = $this->getEmail();
        }

        $email
            ->from($this->getAddress($this->fromAddress, $this->fromName))
            ->to(...$recipients)
            ->subject($subject);

        if ($htmlBody !== null)
        {
            $email->html($htmlBody);
        }

        if ($plainText !== null)
        {
            $email->text($plainText);
        }
        elseif ($htmlBody !== null)
        {
            $email->text(strip_tags($htmlBody));
        }

        return $email;
    }

    /**
     * @param string $template
     * @param array $context
     * @return string|null
     */
    protected function renderTemplate(string $template, array $context): ?string
    {
        if (!$this->twig)
        {
            return null;
        }

        try
        {
            return $this->twig->render($template, $context);
        }
        catch (\Throwable $e)
        {
            $this->logError('Twig render error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @return string
     * @throws MailServiceException
     */
    protected function getDsn(): string
    {
        try
        {
            return $this->env->get('MAILER_DSN');
        }
        catch (EnvException|EnvLoadException $e)
        {
            throw new MailServiceException($e->getMessage());
        }
    }

    /**
     * @return string
     * @throws MailServiceException
     */
    protected function getMailFromAddress(): string
    {
        try
        {
            return $this->env->get('MAIL_FROM_ADDRESS');
        }
        catch (EnvException|EnvLoadException $e)
        {
            throw new MailServiceException($e->getMessage());
        }
    }

    /**
     * @return string
     * @throws MailServiceException
     */
    protected function getMailFromName(): string
    {
        try
        {
            return $this->env->get('MAIL_FROM_NAME');
        }
        catch (EnvException|EnvLoadException $e)
        {
            throw new MailServiceException($e->getMessage());
        }
    }

    /**
     * @return string
     * @throws MailServiceException
     */
    protected function getMailTemplatePath(): string
    {
        try
        {
            return $this->getProtectedPath() . $this->env->get('MAIL_TEMPLATES_PATH');
        }
        catch (EnvException|EnvLoadException $e)
        {
            throw new MailServiceException($e->getMessage());
        }
    }

    /**
     * @param FilesystemLoader $loader
     * @return Twig
     */
    protected function getTwig(FilesystemLoader $loader): Twig
    {
        return new Twig($loader);
    }

    /**
     * @param string|array $templatePath
     * @return FilesystemLoader
     */
    protected function getFilesystemLoader(string|array $templatePath): FilesystemLoader
    {
        return new FilesystemLoader($templatePath);
    }

    /**
     * @param TransportInterface $transport
     * @return MailerInterface
     */
    protected function getMailer(TransportInterface $transport): MailerInterface
    {
        return new Mailer($transport);
    }

    /**
     * @return TemplatedEmail
     */
    protected function getTemplatedEmail(): TemplatedEmail
    {
        return new TemplatedEmail();
    }

    /**
     * @return Email
     */
    protected function getEmail(): Email
    {
        return new Email();
    }

    /**
     * @param string $address
     * @param string $name
     * @return Address
     */
    protected function getAddress(string $address, string $name = ''): Address
    {
        return new Address($address, $name);
    }

    /**
     * @param string $msg
     * @return void
     */
    protected function logError(string $msg): void
    {
        Logger::forge()->error($msg);
    }

    protected function getProtectedPath(): string
    {
        return Asterios::getBasePath();
    }

    protected function templateExists(string $template): bool
    {
        if (!$this->twig)
        {
            return false;
        }

        try
        {
            $this->twig->getLoader()->getSourceContext($template);
            return true;
        }
        catch (LoaderError)
        {
            return false;
        }
    }

    /**
     * @return void
     * @throws MailServiceException
     */
    protected function initializeMailer(): void
    {
        $transport = Transport::fromDsn($this->getDsn());
        $this->mailer = $this->getMailer($transport);

        $this->fromAddress = $this->getMailFromAddress();
        $this->fromName = $this->getMailFromName();
    }

    /**
     * @return void
     * @throws MailServiceException
     */
    protected function initializeTwig(): void
    {
        $templatesPath = $this->getMailTemplatePath();

        if ($templatesPath && is_dir($templatesPath))
        {
            $loader = $this->getFilesystemLoader($templatesPath);
            $this->twig = $this->getTwig($loader);
        }
    }

    /**
     * @throws MailServiceException
     */
    protected static function create(string $envFile = '.env'): static
    {
        return new static($envFile);
    }

    /**
     * @param string $envFile
     * @return void
     */
    protected function initializeEnv(string $envFile): void
    {
        $this->envFile = Asterios::getBasePath() . DIRECTORY_SEPARATOR . $envFile;

        if (null === $this->env)
        {
            $this->env = new Env($this->envFile);
        }
    }

    /**
     * @param MailerInterface $mailer
     * @return void
     */
    protected function setMailer(MailerInterface $mailer): void
    {
        $this->mailer = $mailer;
    }
}
