<?php declare(strict_types=1);

namespace Asterios\Core;

use JsonException;

class Controller
{
    protected const string CONTENT_TYPE_JSON = 'application/json';

    private string $content_type = self::CONTENT_TYPE_JSON;

    private array $content_types = [
        'application/xml',
        'application/xml',
        'application/json',
        'text/javascript',
        'application/vnd.php.serialized',
        'text/plain',
        'text/html',
        'application/csv',
    ];

    public function __construct()
    {
        if (isset($_SERVER['CONTENT_TYPE']))
        {
            $this->set_content_type($_SERVER['CONTENT_TYPE']);
        }
    }

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

    /**
     * @param mixed $data
     * @param int $status_code
     * @throws JsonException
     */
    public function response($data, int $status_code = 200): void
    {

        header("Content-Type: " . $this->content_type);
        header("Expires: on, 01 Jan 1970 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        if (!headers_sent())
        {
            // Send the protocol/status line first, FCGI servers need different status header
            if (!empty($_SERVER['FCGI_SERVER_VERSION']))
            {
                header('Status: ' . $status_code . ' ' . static::$statuses[$status_code]);
            }
            else
            {
                $protocol = $_SERVER['SERVER_PROTOCOL'] ?: 'HTTP/1.1';
                header($protocol . ' ' . $status_code . ' ' . static::$statuses[$status_code]);
            }
        }

        $this->return_response($data);
    }

    /**
     * @param string|array|\stdClass $data
     * @throws JsonException
     */
    private function return_response($data): void
    {
        if (is_array($data) || !empty($data))
        {
            if ($this->content_type === self::CONTENT_TYPE_JSON)
            {
                echo json_encode($data, JSON_THROW_ON_ERROR);
            }
            elseif (is_string($data))
            {
                echo $data;
            }
        }
    }

    protected function is_supported_content_type(string $content_type): bool
    {
        return in_array($content_type, $this->content_types, true);
    }

    public function set_content_type(string $content_type): self
    {
        if ($this->is_supported_content_type($this->content_type))
        {
            $this->content_type = $content_type;
        }

        return $this;
    }
}
