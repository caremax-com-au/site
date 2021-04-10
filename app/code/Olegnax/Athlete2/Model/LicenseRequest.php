<?php


namespace Olegnax\Athlete2\Model;


use Exception;

/**
 * License Key API request.
 */
class LicenseRequest
{
    /**
     * Daily frequency API validation.
     * @since 1.0.0
     * @var string
     */
    const DAILY_FREQUENCY = 'daily';
    /**
     * Daily frequency API validation.
     * @since 1.0.0
     * @var string
     */
    const HOURLY_FREQUENCY = 'hourly';
    /**
     * Daily frequency API validation.
     * @since 1.0.0
     * @var string
     */
    const WEEKLY_FREQUENCY = 'weekly';
    /**
     * API's base url.
     * @since 1.0.0
     * @var string
     */
    protected $settings = [];
    /**
     * Additional request data.
     * @since 1.0.0
     * @var array
     */
    protected $request = [];
    /**
     * License key data.
     * @since 1.0.0
     * @var array
     */
    protected $data = [];
    /**
     * License key notices.
     * @since 1.0.0
     * @var array
     */
    protected $notices = [];
    /**
     * License key custom data.
     * @since 1.1.0
     * @var array
     */
    protected $meta = [];

    /**
     * Default constructor.
     * @param string $license License data encoded as JSON.
     * @since 1.1.0 Custom data support.
     *
     * @since 1.0.0
     */
    public function __construct($license)
    {
        $license = json_decode($license);
        if (isset($license->settings)) {
            $this->settings = (array)$license->settings;
        }
        if (isset($license->request)) {
            $this->request = (array)$license->request;
        }
        if (isset($license->data)) {
            $this->data = (array)$license->data;
        }
        if (isset($license->meta)) {
            $this->meta = (array)$license->meta;
        }
        // Check for activation_id
        if (!isset($this->request['activation_id'])
            && isset($this->data['activation_id'])
        ) {
            $this->request['activation_id'] = $this->data['activation_id'];
        }
        if (!isset($this->request['license_key'])
            && isset($this->data['the_key'])
        ) {
            $this->request['license_key'] = $this->data['the_key'];
        }
    }

    /**
     * Creates basic license request.
     * @param string $url Base API url.
     * @param string $store_code Store code.
     * @param string $sku Product SKU.
     * @param string $license_key Customer license key.
     * @param string $frequency API validate call frequency.
     * @param string $handler API handler.
     *
     * @return object|LicenseRequest
     * @since 1.0.6 Supports retries.
     *
     * @since 1.0.0
     */
    public static function create($url, $store_code, $sku, $license_key, $domain = '', $frequency = self::DAILY_FREQUENCY, $handler = null)
    {
        $license = [
            'settings' => [
                'url' => $url,
                'frequency' => $frequency === null ? self::DAILY_FREQUENCY : $frequency,
                'next_check' => 0,
                'version' => '1.2.0',
                'retries' => 0,
                'handler' => $handler,
            ],
            'request' => [
                'store_code' => $store_code,
                'sku' => $sku,
                'license_key' => $license_key,
                'domain' => $domain,
            ],
            'data' => [],
            'meta' => [],
        ];
        return new self(json_encode($license));
    }

    /**
     * Returns selected properties.
     * @param string $property Property to return.
     *
     * @return mixed
     * @since 1.0.6 Retries support.
     *
     * @since 1.0.0
     * @since 1.0.4 Added isEmpty.
     */
    public function &__get($property)
    {
        $value = null;
        switch ($property) {
            case 'url':
                if (isset($this->settings['url'])) {
                    return $this->settings['url'];
                }
                break;
            case 'frequency':
                if (isset($this->settings['frequency'])) {
                    return $this->settings['frequency'];
                }
                break;
            case 'nextCheck':
                if (isset($this->settings['next_check'])) {
                    return $this->settings['next_check'];
                }
                break;
            case 'request':
                return $this->request;
            case 'data':
                return $this->data;
            case 'notices':
                return $this->notices;
            case 'isOffline':
                $value = isset($this->settings['offline']);
                break;
            case 'isOfflineValid':
                $value = $this->isOffline
                    && ($this->settings['offline'] === true
                        || time() < $this->settings['offline']
                    );
                break;
            case 'isValid':
                $value = false;
                if (isset($this->settings['frequency'])
                    && !empty($this->settings['frequency'])
                    && isset($this->data)
                    && !empty($this->data)
                    && is_numeric($this->data['activation_id'])
                ) {
                    $value = $this->data['the_key'] !== null && 'active' ===  $this->data['status'];
                }
                break;
            case 'isEmpty':
                $value = empty($this->data);
                break;
            case 'version':
                if (isset($this->settings['version'])) {
                    return $this->settings['version'];
                }
                break;
            case 'retries':
                if (isset($this->settings['retries'])) {
                    return $this->settings['retries'];
                }
                break;
            case 'handler':
                if (isset($this->settings['handler'])) {
                    return $this->settings['handler'];
                }
                break;
        }
        return $value;
    }

    /**
     * Returns selected properties.
     * @param string $property Property to set.
     * @param mixed $value Property value.
     * @since 1.0.0
     *
     */
    public function __set($property, $value)
    {
        switch ($property) {
            case 'data':
                $this->data = $value;
                break;
            case 'notices':
                $this->notices = $value;
                break;
        }
    }

    /**
     * Returns license request as string.
     * @return string
     * @since 1.0.0
     *
     */
    public function __toString()
    {
        return json_encode([
            'settings' => $this->settings,
            'request' => $this->request,
            'data' => $this->data,
            'notices' => $this->notices,
            'notice' => $this->getNotice(),
            'meta' => $this->meta,
        ]);
    }

    public function getNotice()
    {
        $result = '';
        if (isset($this->notices)) {
            foreach ($this->notices as $notice_group => $notices) {
                $result .= sprintf('<span data-group="%s">%s</span> ', $notice_group, implode(' ', $notices));
            }
            $result = trim($result);
        }

        return $result;
    }

    /**
     * Enables offline mode.
     * @since 1.0.0
     */
    public function enableOffline()
    {
        $this->settings['offline'] = $this->data['offline_interval'] === 'unlimited'
            ? true
            : strtotime('+' . intval($this->data['offline_value']) . ' ' . $this->data['offline_interval']);
        $this->touch(false);
    }

    /**
     * Touches request settings to update next check.
     * @param bool $disableOffline Disables online mode.
     * @since 1.0.6 Resets retries.
     *
     * @since 1.0.0
     */
    public function touch($disableOffline = true)
    {
        $this->settings['retries'] = 0;
        if ($disableOffline && isset($this->settings['offline'])) {
            unset($this->settings['offline']);
        }
        if (!isset($this->settings['frequency'])) {
            return;
        }
        switch ($this->settings['frequency']) {
            case self::DAILY_FREQUENCY:
                $this->settings['next_check'] = strtotime('+1 days');
                break;
            case self::HOURLY_FREQUENCY:
                $this->settings['next_check'] = strtotime('+1 hour');
                break;
            case self::WEEKLY_FREQUENCY:
                $this->settings['next_check'] = strtotime('+1 week');
                break;
            default:
                $this->settings['next_check'] = strtotime($this->settings['frequency']);
                break;
        }
    }

    /**
     * Updates license to add a new retry attempt and updates next check frequency.
     * @param string $next_check_rule String used in strtotime to indicate when will the next retry be.
     * @since 1.0.6
     *
     */
    public function addRetryAttempt($nextCheckRule)
    {
        $this->settings['retries']++;
        $this->settings['next_check'] = strtotime($nextCheckRule);
    }

    /**
     * Updates license structure to meet a structural version.
     * @since 1.0.6
     */
    public function updateVersion()
    {
        switch ($this->version) {
            case null:
                $this->settings['version'] = '1.0.6';
                $this->settings['retries'] = 0;
                break;
            case '1.0.6':
                $this->settings['version'] = '1.1.0';
                $this->meta = [];
                break;
            case '1.1.0':
                $this->settings['version'] = '1.2.0';
                $this->settings['handler'] = null;
                break;
        }
    }

    /**
     * Adds custom key and value to meta data.
     * @param string $key Custom meta key.
     * @param mixed $value Custom meta value.
     * @throws Exception
     *
     * @since 1.1.0
     *
     */
    public function add($key, $value = null)
    {
        if (!is_string($key)) {
            throw new Exception('Meta key must be a string.');
        }
        if (!is_array($this->meta)) {
            $this->meta = [];
        }
        $this->meta[$key] = is_object($value) ? (array)$value : $value;
    }

    /**
     * Returns custom value associated to a meta key.
     * @param string $key Custom meta key.
     * @param mixed $default Default value if key not found.
     *
     * @return mixed
     * @since 1.1.0
     *
     */
    public function get($key, $default = null)
    {
        if (!is_string($key)) {
            throw new Exception('Meta key must be a string.');
        }
        return array_key_exists($key, $this->meta) ? $this->meta[$key] : $default;
    }
}