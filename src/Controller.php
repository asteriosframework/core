<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Http\ContentType;
use Asterios\Core\Http\Disposition;
use Asterios\Core\Http\ServerRequest;
use JsonException;

class Controller
{
    private string $contentType = ContentType::JSON;

    protected ServerRequest $request;

    private ?string $contentDisposition = null;

    private ?string $filename = null;

    public static array $statuses = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a Teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];


    public function __construct()
    {
        $this->request = new ServerRequest();
    }

    /**
     * @param mixed $data
     * @param int $status_code
     * @throws JsonException
     */
    public function response(mixed $data, int $status_code = 200): void
    {
        if (!headers_sent())
        {

            if (!empty($_SERVER['FCGI_SERVER_VERSION']))
            {
                header('Status: ' . $status_code . ' ' . static::$statuses[$status_code]);
            }
            else
            {
                $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
                header($protocol . ' ' . $status_code . ' ' . static::$statuses[$status_code]);
            }

            header('Content-Type: ' . $this->contentType);

            if ($this->contentDisposition !== null)
            {

                $header = 'Content-Disposition: ' . $this->contentDisposition;

                if ($this->filename !== null)
                {
                    $header .= '; filename="' . basename($this->filename) . '"';
                }

                header($header);
            }

            header('Expires: on, 01 Jan 1970 00:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
        }

        $this->returnResponse($data);
    }

    /**
     * @param mixed $data
     * @throws JsonException
     */
    private function returnResponse(mixed $data): void
    {
        if (is_array($data) || !empty($data))
        {
            if ($this->contentType === ContentType::JSON)
            {
                echo json_encode($data, JSON_THROW_ON_ERROR);
            }
            elseif (is_string($data))
            {
                echo $data;
            }
        }
    }

    /**
     * @deprecated Use isSupportedContentType() instead
     * @param string $content_type
     * @return bool
     */
    protected function is_supported_content_type(string $content_type): bool
    {
        return $this->isSupportedContentType($content_type);
    }

    /**
     * @param string $content_type
     * @return bool
     */
    protected function isSupportedContentType(string $content_type): bool
    {
        return in_array($content_type, ContentType::all(), true);
    }

    /**
     * @@deprecated Use setContentType() instead
     * @param string $contentType
     * @return $this
     */
    public function set_content_type(string $contentType): self
    {
        return $this->setContentType($contentType);
    }

    /**
     * @param string $contentType
     * @return $this
     */
    public function setContentType(string $contentType): self
    {
        if ($this->isSupportedContentType($contentType))
        {
            $this->contentType = $contentType;
        }

        return $this;
    }

    /**
     * Sets the Content-Disposition header.
     *
     * Prefer using the convenience methods inline() or attachment()
     * unless a custom disposition type is required.
     *
     * @param string $disposition
     * @param string|null $filename
     * @return $this
     */
    public function setContentDisposition(string $disposition, ?string $filename = null): self
    {
        $this->contentDisposition = $disposition;
        $this->filename = $filename;

        return $this;
    }

    /**
     * Displays the response inline in the client, if supported.
     *
     * @param string|null $filename Optional filename.
     * @return $this
     */
    public function inline(?string $filename = null): self
    {
        return $this->setContentDisposition(Disposition::INLINE, $filename);
    }

    /**
     * Forces the response to be downloaded by the client.
     *
     * @param string|null $filename Optional filename.
     * @return $this
     */
    public function attachment(?string $filename = null): self
    {
        return $this->setContentDisposition(Disposition::ATTACHMENT, $filename);
    }

    /**
     * @return ServerRequest
     */
    public function request(): ServerRequest
    {
        return $this->request;
    }

    /**
     * @return array|object|null
     * @throws JsonException
     */
    protected function json(): array|object|null
    {
        return $this->request->json();
    }

    protected function body(): string
    {
        return $this->request->body();
    }
}
