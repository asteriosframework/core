<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Exception\MailException;

class Mail
{
    private $recipient;
    private $sender;
    private $subject;
    /** @var string|null */
    private $mail_content_type;
    private $mail_charset;
    private $mail_template;
    private $headers;
    private $mail_encoding;
    /** @var null|string */
    private $body;
    private $eol; // Configure LF, CR OR CRLF
    private $template_open_delimiter;
    private $template_close_delimiter;
    private $template_values = [];

    /**
     * Mail constructor.
     * @param string|null $mail_template
     * @param string $type
     * @throws Exception\ConfigLoadException
     * @throws MailException
     */
    public function __construct(?string $mail_template = null, string $type = 'html')
    {
        if (!is_null($mail_template))
        {
            $this->set_template($mail_template);
        }

        switch ($type)
        {
            case 'html':
                $this->set_html();
                break;
            case 'text':
                $this->set_text();
                break;
            default:
                $this->set_html();
        }

        // Set default values from config
        $config = Asterios::config('mail');

        $this->set_content_type($config->content_type)
            ->set_charset($config->charset)
            ->set_encoding($config->encoding)
            ->set_eol($config->eol)
            ->set_open_delimiter($config->template['delimiter']['open'])
            ->set_close_delimiter($config->template['delimiter']['close']);
    }

    public function set_content_type(string $type): Mail
    {
        $this->mail_content_type = $type;

        return $this;
    }

    public function get_content_type(): string
    {
        return $this->mail_content_type;
    }

    public function set_charset(string $mail_charset): Mail
    {
        $this->mail_charset = $mail_charset;

        return $this;
    }

    public function get_charset(): string
    {
        return $this->mail_charset;
    }

    public function set_encoding(string $mail_encoding): Mail
    {
        $this->mail_encoding = $mail_encoding;

        return $this;
    }

    public function get_encoding(): string
    {
        return $this->mail_encoding;
    }

    public function set_eol(string $eol): Mail
    {
        $this->eol = $eol;

        return $this;
    }

    public function get_eol(): string
    {
        return $this->eol;
    }

    public function set_open_delimiter(string $open_delimiter): Mail
    {
        $this->template_open_delimiter = $open_delimiter;

        return $this;
    }

    public function get_open_delimiter(): string
    {
        return $this->template_open_delimiter;
    }

    public function set_close_delimiter(string $close_delimiter): Mail
    {
        $this->template_close_delimiter = $close_delimiter;

        return $this;
    }

    public function get_close_delimiter(): string
    {
        return $this->template_close_delimiter;
    }

    public function set_text(): Mail
    {
        $this->set_content_type('text/plain');

        return $this;
    }

    public function set_html(): Mail
    {
        $this->set_content_type('text/html');

        return $this;
    }

    /**
     * @throws MailException
     */
    public function set_variable(string $variable_name, string $variable_value): Mail
    {
        if ($variable_name !== '' && $variable_value !== '')
        {
            $this->template_values[$variable_name] = $variable_value;
        }
        else
        {
            throw new MailException('ERROR: Template variable KEY and VALUE must be a string and not be empty');
        }

        return $this;
    }

    public function set_variables(array $variables_array): Mail
    {
        foreach ($variables_array as $key => $value)
        {
            $this->template_values[$key] = $value;
        }

        return $this;
    }

    public function set_subject(string $subject): Mail
    {
        $this->subject = $subject;

        return $this;
    }

    public function get_subject(): ?string
    {
        return $this->subject;
    }

    public function set_recipient(string $recipient): Mail
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function get_recipient(): ?string
    {
        return $this->recipient;
    }

    public function set_sender(string $sender): Mail
    {
        $this->sender = $sender;

        return $this;
    }

    public function get_sender(): ?string
    {
        return $this->sender;
    }

    public function get_headers(): ?string
    {
        return $this->headers;
    }

    public function send(): bool
    {
        $this->set_header('MIME-Version: 1.0')
            ->set_header('Content-Type: ' . $this->get_content_type() . '; charset="' . $this->get_charset() . '"')
            ->set_header('Content-Transfer-Encoding: ' . $this->get_encoding())
            ->set_header('From: "' . $this->get_sender() . '" < ' . $this->get_sender() . '>')
            ->set_header('Reply-To: ' . $this->get_sender())
            ->set_header('Return-Path: ' . $this->get_sender())
            ->set_header('X-mailer: Mailer 1.0')
            ->parse();

        return mail($this->get_recipient(), $this->subject_encoding(), $this->get_body(), $this->get_headers());
    }

    private function set_header(string $header): Mail
    {
        $_header = $this->headers . $header . $this->CRLF();
        $this->headers = $_header;

        return $this;
    }

    private function CRLF(): string
    {
        switch ($this->get_eol())
        {
            case 'LF':
                $return_crlf = "\n";
                break;
            case 'CR':
                $return_crlf = "\r";
                break;
            case 'CRLF':
                $return_crlf = "\r\n";
                break;
            default:
                $return_crlf = "\n";
        }

        return $return_crlf;
    }

    private function subject_encoding(): ?string
    {
        if ($this->get_charset() === 'UTF-8')
        {
            $formatted_subject = "=?utf-8?B?" . base64_encode($this->get_subject()) . "?=";
        }
        else
        {
            $formatted_subject = $this->get_subject();
        }

        return $formatted_subject;
    }

    /**
     * @throws MailException
     */
    private function set_template(?string $mail_template = null): void
    {
        if (file_exists($mail_template))
        {
            $this->mail_template = file_get_contents($mail_template);
        }
        elseif (is_string($mail_template))
        {
            $this->mail_template = $mail_template;
        }
        else
        {
            throw new MailException('Error: Invalid e-mail template. $mail_template must be a string or a filepath!');
        }
    }

    private function get_template(): ?string
    {
        return $this->mail_template;
    }

    private function get_template_values(): array
    {
        return $this->template_values;
    }

    private function parse(): void
    {
        $template = $this->get_template();

        foreach ($this->get_template_values() as $key => $value)
        {
            if (isset($value))
            {
                $template = str_replace($this->get_open_delimiter() . $key . $this->get_close_delimiter(), $value, $template);
            }
        }

        $this->set_body($template);
    }

    private function set_body(string $value): void
    {
        $this->body = $value;
    }

    private function get_body(): ?string
    {
        return $this->body;
    }
}
