<?php
class nBillRemote
{
    /** @var string **/
    protected $url;
    /** @var array **/
    protected static $cache;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function get($timeout = 20, $force_refresh = false)
    {
        $contents = false;
        if (strlen($this->url) > 0) {
            if (!$force_refresh) {
                if (isset(self::$cache[$this->url])) {
                    return self::$cache[$this->url];
                }
            }
            if (function_exists('curl_init')) {
                $contents = $this->getUsingCurl($timeout);
            }
            if ($contents === false && ini_get('allow_url_fopen') == '1') {
                $contents = $this->getUsingFile($timeout);
            }
            if ($contents === false) {
                $contents = $this->getUsingSockets($timeout);
            }

            self::$cache[$this->url] = $contents;
        }
        return $contents;
    }

    protected function getUsingFile($timeout)
    {
        $options = array(
                'http'=>array(
                'method'=>"GET",
                'timeout'=>$timeout,
                'header'=>"Accept-language: en\r\n"
                )
        );
        $context = stream_context_create($options);
        return @file_get_contents($this->url, null, $context);
    }

    protected function getUsingCurl($timeout)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //Allow redirects (will fail if open_basedir in effect)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //Return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPGET, 0); //use GET
        $result = @curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    protected function getUsingSockets($timeout)
    {
        $url_parts = parse_url($this->url);
        if (!isset($url_parts['path'])) {
            $url_parts['path'] = '/';
        }
        if (!isset($url_parts['query'])) {
            $url_parts['query' ] = '';
        }

        $header = "Host: " . $url_parts['host'] . "\r\n";
        $header .= "User-Agent: Mozilla\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($url_parts['query']) . "\r\n";
        $header .= "Connection: close\r\n\r\n";

        $errno = '';
        $errstr = '';
        $fp = @fsockopen($url_parts['host'], 80, $errno, $errstr, $timeout);
        if ($fp) {
            $out = '';
            stream_set_timeout($fp, $timeout);
            fputs($fp, "GET " . $url_parts['path'] . "  HTTP/1.1\r\n");
            fputs($fp, $header. $url_parts['query']);
            fwrite($fp, $out);
            $info = stream_get_meta_data($fp);
            $result = "";
            if ($info['timed_out']) {
                @fclose($fp);
                return false;
            }
            while (!feof($fp)) {
                $info = stream_get_meta_data($fp);
                if ($info['timed_out']) {
                    @fclose($fp);
                    return false;
                }
                $result .= fread($fp, 4096);
            }
            fclose($fp);
            if (strlen($result) > 0) {
                //Strip the HTTP headers.
                $pos = strpos($result, "\r\n\r\n");
                if ($pos !== false) {
                    $result = substr($result, $pos + 4);
                }
                return $result;
            } else {
                return false;
            }
        }
    }

    public function curlPostWithCookies($fields, $cookie_filename = '')
    {
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if (strlen($cookie_filename) > 0) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_filename);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_filename);
        }
        $result = curl_exec($ch);
        if (!$result) {
            $result = curl_errno($ch) . ":" . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
}