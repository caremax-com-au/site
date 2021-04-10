<?php


namespace Olegnax\Athlete2\Model;

use Exception;

/**
 * Curl client.
 */
class Client
{
    /**
     * Instance.
     * @since 1.0.0
     * @var Client this
     */
    protected static $instance;
    /**
     * Last response got from API.
     * RAW response
     * @since 1.0.0
     * @var string
     */
    protected $response;
    /**
     * Last response got from API.
     * RAW response
     * @since 1.2.0
     * @var array
     */
    protected $events = [];
    /**
     * Curl accessor.
     * @since 1.0.0
     * @var false|resource
     */
    private $curl;

    /**
     * Static constructor.
     * @return Client
     * @since 1.0.0
     */
    public static function instance()
    {
        if (isset(static::$instance)) {
            return static::$instance;
        }
        static::$instance = new self;
        return static::$instance;
    }

    /**
     * Adds an event handler.
     * @param string $event
     * @param callable $callable
     * @return $this
     * @since 1.2.0
     *
     */
    public function on($event, $callable)
    {
        if (!is_array($this->events)) {
            $this->events = [];
        }
        if (is_callable($callable)) {
            $this->events[$event] = $callable;
        }
        return $this;
    }

    /**
     * Executes CURL call.
     * Returns API response.
     * @param string $endpoint API endpoint to call.
     * @param LicenseRequest $license License request.
     * @param string $method Request method.
     *
     * @return mixed|object|null
     * @since 1.0.0
     *
     */
    public function call($endpoint, LicenseRequest $license, $method = 'POST')
    {
        $microtime = microtime(true);
        $this->trigger('start', [$microtime]);
        // Begin
        $this->setCurl(preg_match('/https\:/', $license->url));
        $this->resolveEndpoint($endpoint, $license);
        // Make call
        $url = $license->url . $endpoint;
        $this->trigger('endpoint', [$endpoint, $url]);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        // Set method
        $this->trigger('request', [$license->request]);
        switch ($method) {
            case 'GET':
                curl_setopt($this->curl, CURLOPT_POST, 0);
                break;
            case 'POST':
                curl_setopt($this->curl, CURLOPT_POST, 1);
                if ($license->request && count($license->request) > 0) {
                    curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($license->request));
                }
                break;
            case 'JPOST':
            case 'JPUT':
            case 'JGET':
            case 'JDELETE':
                $json = json_encode($license->request);
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, preg_replace('/J/', '', $method, -1));
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $json);
                // Rewrite headers
                curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json),
                ]);
                break;
        }
        // Get response
        $this->response = curl_exec($this->curl);
        if (curl_errno($this->curl)) {
            $error = curl_error($this->curl);
            curl_close($this->curl);
            if (!empty($error)) {
                throw new Exception($error);
            }
        } else {
            curl_close($this->curl);
        }
        $this->trigger('response', [$this->response]);
        $this->trigger('finish', [microtime(true), $microtime]);
        return empty($this->response) ? null : json_decode($this->response);
    }

    /**
     * Triggers an event.
     * @param string $event
     * @param array $args
     * @since 1.2.0
     *
     */
    private function trigger($event, $args = [])
    {
        if (array_key_exists($event, $this->events)) {
            call_user_func_array($this->events[$event], $args);
        }
    }

    /**
     * Sets curl property and its settings.
     * @since 1.0.0
     *
     * @see http://us3.php.net/manual/en/book.curl.php
     * @see https://gist.github.com/salsalabs/e24c2466496860975e8a
     */
    private function setCurl($is_https = false)
    {
        // Init
        $this->curl = curl_init();
        // Sets basic parameters
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, isset($this->request->settings['timeout']) ? $this->request->settings['timeout'] : 100);
        // Set parameters to maintain cookies across sessions
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, sys_get_temp_dir() . '/cookies_file');
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, sys_get_temp_dir() . '/cookies_file');
        curl_setopt($this->curl, CURLOPT_USERAGENT, 'OLEGNAX-PURCHASE-VERIFY');
        if ($is_https) {
            $this->setSSL();
        }
    }

    /**
     * Sets SSL curl properties when requesting an https url.
     * @since 1.0.2
     */
    private function setSSL()
    {
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
    }

    /**
     * Resolve endpoint based on handler setup.
     * @param string         &$endpoint
     * @param LicenseRequest $license License request.
     * @since 1.2.0
     *
     */
    private function resolveEndpoint(&$endpoint, LicenseRequest $license)
    {
        switch ($license->handler) {
            case 'wp_rest':
                $endpoint = '/wp-json/woo-license-keys/v1/' . str_replace('license_key_', '', $endpoint);
                break;
            default:
                $endpoint = '?action=' . $endpoint;
                break;
        }
    }
}