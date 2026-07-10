<?php declare(strict_types=1);

namespace Asterios\Core;

class Request
{
    /**
     * The file to read and write cookies to for requests
     *
     * @var string
     **/
    public string $cookie_file;

    /**
     * Determines whether requests should follow redirects
     *
     * @var boolean
     **/
    public bool $follow_redirects = true;

    /**
     * An associative array of headers to send along with requests
     *
     * @var array
     **/
    public array $headers = [];

    /**
     * An associative array of CURL-OPT options to send along with requests
     *
     * @var array
     **/
    public array $options = [];

    /**
     * The referer header to send along with requests
     *
     * @var string
     **/
    public string $referer;

    /**
     * The user agent to send along with requests
     *
     * @var string
     **/
    public string $user_agent;

    /**
     * Stores an error string for the last request if one occurred
     *
     * @var string
     * @access protected
     **/
    protected string $error = '';

    /** @var mixed */
    protected mixed $request;

    /**
     * Initializes a Curl object
     *
     * Sets the $cookie_file to "curl_cookie.txt" in the current directory
     * Also sets the $user_agent to $_SERVER['HTTP_USER_AGENT'] if it exists, 'Curl/PHP '.PHP_VERSION.' (http://github.com/shuber/curl)' otherwise
     **/
    public function __construct()
    {
        $this->cookie_file = __DIR__ . DIRECTORY_SEPARATOR . 'curl_cookie.txt';
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Curl/PHP ' . PHP_VERSION . ' (https://github.com/shuber/curl)';
    }

    /**
     * Makes an HTTP DELETE request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return Response|bool object
     */
    public function delete(string $url, array|string $vars = []): Response|bool
    {
        return $this->request('DELETE', $url, $vars);
    }

    /**
     * Returns the error string of the current request if one occurred
     **/
    public function error(): string
    {
        return $this->error;
    }

    /**
     * Makes an HTTP GET request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return Response|bool
     */
    public function get(string $url, array|string $vars = []): Response|bool
    {
        if (!empty($vars))
        {
            $url .= (str_contains($url, '?')) ? '&' : '?';
            $url .= (is_string($vars)) ? $vars : http_build_query($vars, '', '&');
        }

        return $this->request('GET', $url);
    }

    /**
     * Makes an HTTP HEAD request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return Response|bool
     */
    public function head(string $url,  array|string $vars = []): Response|bool
    {
        return $this->request('HEAD', $url, $vars);
    }

    /**
     * Makes an HTTP POST request to the specified $url with an optional array or string of $vars
     *
     * @param string $url
     * @param array|string $vars
     * @return Response|boolean
     **/
    public function post(string $url,  array|string $vars = []): Response|bool
    {
        return $this->request('POST', $url, $vars);
    }

    /**
     * Makes an HTTP POST request to the specified $url with an optional array or string of $vars
     *
     * @param string $url
     * @param array|string $vars
     * @return Response|boolean
     **/
    public function query(string $url, array|string $vars = []): Response|bool
    {
        return $this->request('QUERY', $url, $vars);
    }

    /**
     * Makes an HTTP PUT request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return Response|boolean
     **/
    public function put(string $url,  array|string $vars = []): Response|bool
    {
        return $this->request('PUT', $url, $vars);
    }

    /**
     * Makes an HTTP request of the specified $method to a $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $method
     * @param string $url
     * @param array|string $vars
     * @return Response|boolean
     **/
    public function request(string $method, string $url, array|string $vars = []): Response|bool
    {
        $this->error = '';
        $this->request = curl_init();

        if (is_array($vars))
        {
            $vars = http_build_query($vars);
        }

        $this->set_request_method($method);
        $this->set_request_options($url, $vars);
        $this->set_request_headers();

        $response = curl_exec($this->request);

        if ($response)
        {
            $response = new Response($response);
        }
        else
        {
            $this->error = curl_errno($this->request) . ' - ' . curl_error($this->request);
        }

        curl_close($this->request);

        return $response;
    }

    protected function set_request_headers(): void
    {
        $headers = [];
        foreach ($this->headers as $key => $value)
        {
            $headers[] = $key . ': ' . $value;
        }
        curl_setopt($this->request, CURLOPT_HTTPHEADER, $headers);
    }

    protected function set_request_method(string $method): void
    {
        switch (strtoupper($method))
        {
            case 'HEAD':
                curl_setopt($this->request, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                curl_setopt($this->request, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($this->request, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($this->request, CURLOPT_CUSTOMREQUEST, $method);
        }
    }

    /**
     * Sets the CURL-OPT options for the current request
     *
     * @param string $url
     * @param mixed $vars
     **/
    protected function set_request_options(string $url, $vars): void
    {
        curl_setopt($this->request, CURLOPT_URL, $url);
        if (!empty($vars))
        {
            curl_setopt($this->request, CURLOPT_POSTFIELDS, $vars);
        }

        # Set some default CURL options
        curl_setopt($this->request, CURLOPT_HEADER, true);
        curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->request, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->cookie_file)
        {
            curl_setopt($this->request, CURLOPT_COOKIEFILE, $this->cookie_file);
            curl_setopt($this->request, CURLOPT_COOKIEJAR, $this->cookie_file);
        }
        if ($this->follow_redirects)
        {
            curl_setopt($this->request, CURLOPT_FOLLOWLOCATION, true);
        }
        if ($this->referer)
        {
            curl_setopt($this->request, CURLOPT_REFERER, $this->referer);
        }

        # Set any custom CURL options
        foreach ($this->options as $option => $value)
        {
            curl_setopt($this->request, constant('CURLOPT_' . str_replace('CURLOPT_', '', strtoupper($option))), $value);
        }
    }

}
