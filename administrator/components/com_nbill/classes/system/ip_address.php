<?php
class nBillIpAddress
{
    /** @var nBillConfiguration **/
    protected $config;
    /** @var string **/
    public $ip_address;

    /** @var array Cache of IP address/country code matches - so we don't have to do a remote lookup for the same one more than once **/
    protected static $cc_cache = array();

    /**
    * @param string $ip_address IP Address to be represented by this object - leave as null to use the current user IP
    */
    public function __construct($ip_address = null)
    {
        if ($ip_address === null || strlen($ip_address) == 0) {
            $ip_address = $this->getCurrentIpAddress();
        }
        $this->ip_address = $ip_address;
    }

    protected function getCurrentIpAddress()
    {
        $ip = "";
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
                if (nbf_common::nb_strpos($ip, ",") !== false) {
                  $ip_array = explode(",", $ip);
                  $ip = $ip_array[0];
                }
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $ip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $ip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
                if (nbf_common::nb_strpos($ip, ",") !== false) {
                  $ip_array = explode(",", $ip);
                  $ip = $ip_array[0];
                }
            } else if (getenv('HTTP_CLIENT_IP')) {
                $ip = getenv('HTTP_CLIENT_IP');
            } else {
                $ip = getenv( 'REMOTE_ADDR' );
            }
        }

        /*if ((isset($_SERVER['SERVER_ADDR']) && $ip == $_SERVER['SERVER_ADDR']) ||
            (isset($_SERVER['LOCAL_ADDR']) && $ip == $_SERVER['LOCAL_ADDR'])) {
            $ip = ''; //Local request
        }*/

        return $ip;
    }

    public function lookupCountryCode(nBillConfiguration $config = null)
    {
        //Try the cache first
        if (isset(self::$cc_cache[$this->ip_address])) {
            return self::$cc_cache[$this->ip_address];
        }

        //Not found, so do a remote lookup
        $country = null;
        if (!$config) {
            $config = nBillConfigurationService::getInstance()->getConfig();
        }
        if ($config->geo_ip_lookup && strlen($config->api_url_geo_ip) > 0) {
            $remote = new nBillRemote(str_replace('##ip##', $this->ip_address, $config->api_url_geo_ip));
            $cc_json = $remote->get(10);
            $cc = @json_decode($cc_json, true);
            if ($cc) {
                if (array_key_exists('country_code', $cc)) {
                    $country = $cc['country_code'];
                } else if (array_key_exists('countryCode', $cc)) {
                    $country = $cc['countryCode'];
                } else {
                    foreach ($cc as $key=>$value)
                    {
                        if (strlen($value) == 2 && (strpos(strtolower($key), 'country') !== false || strpos(strtolower($key), 'cc') !== false)) {
                            $country = $value;
                            break;
                        }
                    }
                }
            }
        }
        self::$cc_cache[$this->ip_address] = $country;
        return $country;
    }

    public function isLocalHost()
    {
        if (filter_var($this->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) == $this->ip_address) {
            return false;
        }
        return true;
    }
}