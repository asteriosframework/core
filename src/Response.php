<?php declare(strict_types=1);

namespace Asterios\Core;

class Response
{
    /**
     * The body of the response without the headers block
     *
     * @var string
     **/
    public string $body = '';

    /**
     * An associative array containing the response's headers
     *
     * @var array
     **/
    public array $headers = [];

    /**
     * Accepts the result of a curl request as a string
     *
     * <code>
     * $response = new CurlResponse(curl_exec($curl_handle));
     * echo $response->body;
     * echo $response->headers['Status'];
     * </code>
     *
     * @param string $response
     **/
    public function __construct(string $response)
    {
        # Headers regex
        $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';

        # Extract headers from response
        preg_match_all($pattern, $response, $matches);
        $headers_string = array_pop($matches[0]);
        $headers = explode("\r\n", str_replace("\r\n\r\n", '', $headers_string));

        # Remove headers from the response body
        $this->body = str_replace($headers_string, '', $response);

        # Extract the version and status from the first header
        $version_and_status = array_shift($headers);
        preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $version_and_status, $matches);
        $this->headers['Http-Version'] = $matches[1];
        $this->headers['Status-Code'] = $matches[2];
        $this->headers['Status'] = $matches[2] . ' ' . $matches[3];

        # Convert headers into an associative array
        $headerPattern = '~^(?<key>.*?)\s*:\s*(?<value>.*)$~';
        foreach ($headers as $header)
        {
            if (!preg_match($headerPattern, $header, $matches))
            {
                continue;
            }

            $this->headers[$matches['key']] = $matches['value'];
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->body;
    }
}
