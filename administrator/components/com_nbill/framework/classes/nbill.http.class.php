<?php
/**
* Class file just containing static methods to allow posts and gets via http (using curl if available, or sockets if not).
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* Static methods to allow posts and gets via http (using curl if available, or sockets if not).
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_http
{
  /**
    * Post the given params server-to-server to the given host and page and return the result
    * with the HTTP headers stripped. This function will first attempt to use CURL, but if
    * that is not available, it will try to use sockets instead.
    * @param string Host part of the URL, excluding scheme and page, eg. "nbill.co.uk"
    * @param string Page part of the URL, excluding scheme, domain and querystring, eg. "/documentation/index.php"
    * @param array Associative array of name/value pairs to be posted to the URL, eg. array("page"=>"ug-introduction.html")
    * @param int Number of seconds to wait before giving up
    * @param int Port number to connect to
    * @param array Array of custom headers to send
    * @param boolean Whether or not to suppress the normal headers (user agent etc.) and ONLY use the custom headers supplied
    * @return mixed The contents of the requested resource (could be binary if a file is returned, or HTML if a normal web page is returned)
    */
    public static function post_http($host, $page, $params, $timeout = 20, $port = 80, $custom_headers = array(), $custom_only = false, $cookies = false)
    {
        if (substr($page, 0, 1) == "/")
        {
            $page = substr($page, 1); //Remove leading slash if supplied - we will add it back later
        }

        //Based on unlicensed code in public domain from php.net user comments
        nbf_common::load_language("frontend");
        # working vars
        $vars = "";
        foreach ($params as $key=>$value)
        {
            if (strlen($vars) > 0)
            {
                $vars .= "&";
            }
            $vars .= $key . "=" . urlencode($value);
        }

        //If cURL is enabled, use that
        $result = "";
        if (!$custom_only && defined("CURLOPT_URL") && function_exists("curl_version"))
        {
            $ch = @curl_init();
            @curl_setopt($ch, CURLOPT_URL, "http://" . $host . ($port != 80 ? ":$port" : "") . "/" . $page);
            @curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //Allow redirects
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //Return into a variable
            @curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            @curl_setopt($ch, CURLOPT_POST, 1); //use POST
            if ($cookies) {
                curl_setopt($ch, CURLOPT_COOKIEJAR, nbf_cms::$interop->site_temp_path . '/cookie.txt');
                curl_setopt($ch, CURLOPT_COOKIEFILE, nbf_cms::$interop->site_temp_path . '/cookie.txt');
            }
            if ($custom_headers && count($custom_headers) > 0)
            {
                foreach ($custom_headers as $key=>$custom_header)
                {
                    $custom_headers[$key] = preg_replace("'(\r|\n)'","",$custom_header);
                }
                @curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);
            }
            if ($vars)
            {
                @curl_setopt($ch, CURLOPT_POSTFIELDS, $vars); //add the posted parameters
            }
            $result = @curl_exec($ch);
            @curl_close($ch);
            if ($result)
            {
                return $result; //Headers are already stripped by CURL
            }
        }
        if (!$result)
        {
            //cURL not available, or didn't work, so try sockets instead
            $header = "";
            if (!$custom_only)
            {
                $header = "Host: $host\r\n";
                $header .= "User-Agent: Mozilla\r\n";
                $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $header .= "Content-Length: " . strlen($vars)."\r\n";
            }
            if ($custom_headers && count($custom_headers) > 0)
            {
                foreach ($custom_headers as $custom_header)
                {
                    $custom_header = str_replace("\n", "", str_replace("\r", "", $custom_header)) . "\n\r"; //Add EOL if not present
                    $header .= $custom_header;
                }
            }
            if (!$custom_only)
            {
                $header .= "Connection: close\r\n\r\n";
            }

            $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
            if (!$fp)
            {
                return NBILL_POST_ERROR . "<br /><br />$errstr ($errno)";
            }
            else
            {
                $out = '';
                stream_set_timeout($fp, $timeout);
                fputs($fp, "POST /$page  HTTP/1.0\r\n");
                fputs($fp, $header.$vars);
                fwrite($fp, $out);
                $info = stream_get_meta_data($fp);
                $result = "";
                if ($info['timed_out'])
                {
                    fclose($fp);
                    return NBILL_POST_ERROR . "<br /><br />(Timeout)";
                }
                while (!feof($fp))
                {
                    $info = stream_get_meta_data($fp);
                    if ($info['timed_out'])
                    {
                        fclose($fp);
                        return NBILL_POST_ERROR . "<br /><br />(Timeout)";
                    }
                    $result .= fgets($fp, 4096);
                }
                fclose($fp);
                if (strlen($result) > 0)
                {
                    //Strip the HTTP headers.
                    $pos = strpos($result, "\r\n\r\n");
                    if ($pos !== false)
                    {
                        $result = substr($result, $pos + 4);
                    }
                    return $result;
                }
                else
                {
                    return NBILL_POST_ERROR;
                }
            }
        }
    }

    /**
    * Post the given params server-to-server to the given host and page via SSL and return the result
    * with the HTTP headers stripped. This function will first attempt to use CURL, but if
    * that is not available, it will try to use sockets instead.
    * @param string Host part of the URL, excluding scheme and page, eg. "nbill.co.uk"
    * @param string Page part of the URL, excluding scheme, domain and querystring, eg. "/documentation/index.php"
    * @param array Associative array of name/value pairs to be posted to the URL, eg. array("page"=>"ug-introduction.html")
    * @param int Number of seconds to wait before giving up
    * @param int Port number to connect to
    * @param array Array of custom headers to send
    * @param boolean Whether or not to suppress the normal headers (user agent etc.) and ONLY use the custom headers supplied
    * @return mixed The contents of the requested resource (could be binary if a file is returned, or HTML if a normal web page is returned)
    */
    public static function post_https($host, $page, $params, $timeout = 20, $port = 443, $custom_headers = array(), $custom_only = false, $cookies = false)
    {
        if (substr($page, 0, 1) == "/")
        {
            $page = substr($page, 1); //Remove leading slash if supplied - we will add it back later
        }

        //Based on unlicensed code in public domain from php.net user comments
        nbf_common::load_language("frontend");
        # working vars
        $vars = "";
        foreach ($params as $key=>$value)
        {
            if (strlen($vars) > 0)
            {
                $vars .= "&";
            }
            $vars .= $key . "=" . urlencode($value);
        }

        //If cURL is enabled, use that
        $result = "";
        if (!$custom_only && defined("CURLOPT_URL") && function_exists("curl_version"))
        {
            $ch = @curl_init();
            @curl_setopt($ch, CURLOPT_URL, "https://" . $host . ($port != 443 ? ":$port" : "") . "/" . $page);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //Make sure certificate matches domain
            @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            @curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
            @curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //Allow redirects
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //Return into a variable
            @curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            @curl_setopt($ch, CURLOPT_POST, 1); //use POST
            if ($cookies) {
                curl_setopt($ch, CURLOPT_COOKIEJAR, nbf_cms::$interop->site_temp_path . '/cookie.txt');
                curl_setopt($ch, CURLOPT_COOKIEFILE, nbf_cms::$interop->site_temp_path . '/cookie.txt');
            }
            if ($custom_headers && count($custom_headers) > 0)
            {
                foreach ($custom_headers as $key=>$custom_header)
                {
                    $custom_headers[$key] = preg_replace("'(\r|\n)'","",$custom_header);
                }
                @curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);
            }
            if ($vars)
            {
                @curl_setopt($ch, CURLOPT_POSTFIELDS, $vars); //add the posted parameters
            }
            $result = @curl_exec($ch);
            @curl_close($ch);
            if ($result)
            {
                return $result;
            }
        }
        if (!$result)
        {
            //cURL not available, or didn't work, so try sockets instead
            $header = "";
            if (!$custom_only)
            {
                $header = "Host: $host\r\n";
                $header .= "User-Agent: Mozilla\r\n";
                $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $header .= "Content-Length: ".strlen($vars)."\r\n";
            }
            if ($custom_headers && count($custom_headers) > 0)
            {
                foreach ($custom_headers as $custom_header)
                {
                    $custom_header = str_replace("\n", "", str_replace("\r", "", $custom_header)) . "\n\r"; //Add EOL if not present
                    $header .= $custom_header;
                }
            }
            if (!$custom_only)
            {
                $header .= "Connection: close\r\n\r\n";
            }

            $fp = fsockopen("ssl://" . $host, $port, $errno, $errstr, $timeout);

            if (!$fp)
            {
                return NBILL_POST_ERROR . "<br /><br />$errstr ($errno)";
            }
            else
            {
                $out = '';
                stream_set_timeout($fp, $timeout);
                fputs($fp, "POST /$page  HTTP/1.0\r\n");
                fputs($fp, $header.$vars);
                fwrite($fp, $out);
                $info = stream_get_meta_data($fp);
                $result = "";
                if ($info['timed_out'])
                {
                    fclose($fp);
                    return NBILL_POST_ERROR . "<br /><br />(Timeout)";
                }
                while (!feof($fp))
                {
                    $info = stream_get_meta_data($fp);
                    if ($info['timed_out'])
                    {
                        fclose($fp);
                        return NBILL_POST_ERROR . "<br /><br />(Timeout)";
                    }
                    $result .= fgets($fp, 4096);
                }
                fclose($fp);
                if (strlen($result) > 0)
                {
                    //Strip the HTTP headers.
                    $pos = strpos($result, "\r\n\r\n");
                    if ($pos !== false)
                    {
                        $result = substr($result, $pos + 4);
                    }
                    return $result;
                }
                else
                {
                    return NBILL_POST_ERROR;
                }
            }
        }
    }

    /**
    * Fetch the page at the given URL server-to-server using the given host and page and return the result
    * with the HTTP headers stripped. This function will first attempt to use CURL, but if
    * that is not available, it will try to use sockets instead.
    * @param string Host part of the URL, excluding scheme and page, eg. "nbill.co.uk"
    * @param string Page part of the URL, excluding scheme, domain and querystring, eg. "/documentation/index.php"
    * @param array Associative array of name/value pairs to be used in the querystring, eg. array("page"=>"ug-introduction.html")
    * @param int Number of seconds to wait before giving up
    * @param int Port number to connect to
    * @param array Array of custom headers to send
    * @param boolean Whether or not to suppress the normal headers (user agent etc.) and ONLY use the custom headers supplied
    * @return mixed The contents of the requested resource (could be binary if a file is returned, or HTML if a normal web page is returned)
    */
    public static function get_http($host, $page, $params, $timeout = 20, $port = 80, $custom_headers = array(), $custom_only = false, $cookies = false)
    {
        if (substr($page, 0, 1) == "/")
        {
            $page = substr($page, 1); //Remove leading slash if supplied - we will add it back later
        }

        //Based on unlicensed code in public domain from php.net user comments
        # working vars
        $vars = "";
        foreach ($params as $key=>$value)
        {
            if (strlen($vars) > 0)
            {
                $vars .= "&";
            }
            $vars .= $key . "=" . urlencode($value);
        }

        //If cURL is enabled, use that
        $error_message = "";
        $result = "";
        if (!$custom_only && defined("CURLOPT_URL") && function_exists("curl_version"))
        {
            $ch = @curl_init();
            @curl_setopt($ch, CURLOPT_URL, "http://" . $host . ($port != 80 ? ":$port" : "") . "/" . $page . ($vars ? "?" . $vars : ""));
            @curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //Allow redirects
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //Return into a variable
            @curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            @curl_setopt($ch, CURLOPT_HTTPGET, 0); //use GET
            if ($cookies) {
                curl_setopt($ch, CURLOPT_COOKIEJAR, nbf_cms::$interop->site_temp_path . '/cookie.txt');
                curl_setopt($ch, CURLOPT_COOKIEFILE, nbf_cms::$interop->site_temp_path . '/cookie.txt');
            }
            if ($custom_headers && count($custom_headers) > 0)
            {
                foreach ($custom_headers as $custom_header)
                {
                    $custom_header = str_replace("\n", "", str_replace("\r", "", $custom_header)) . "\n\r"; //Add EOL if not present
                    @curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_header);
                }
            }
            $result = @curl_exec($ch);
            $error_message = @curl_error($ch);
            @curl_close($ch);
            if (strlen($result) > 0) {
                return $result;
            } else if (strlen(trim($error_message)) > 0) {
                return $error_message;
            }
        }
        if (strlen($result) == 0)
        {
            //cURL not available, or didn't work, so try sockets instead
            $header = "";
            if (!$custom_only)
            {
                $header = "Host: $host\r\n";
                $header .= "User-Agent: Mozilla\r\n";
                $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $header .= "Content-Length: ".strlen($vars)."\r\n";
            }
            if ($custom_headers && count($custom_headers) > 0)
            {
                foreach ($custom_headers as $custom_header)
                {
                    $custom_header = str_replace("\n", "", str_replace("\r", "", $custom_header)) . "\n\r"; //Add EOL if not present
                    $header .= $custom_header;
                }
            }
            if (!$custom_only)
            {
                $header .= "Connection: close\r\n\r\n";
            }

            $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
            if (!$fp)
            {
                return NBILL_REMOTE_POST_ERROR . "<br /><br />$errstr ($errno)";
            }
            else
            {
                $out = '';
                stream_set_timeout($fp, $timeout);
                fputs($fp, "GET /$page  HTTP/1.0\r\n");
                fputs($fp, $header.$vars);
                fwrite($fp, $out);
                $info = stream_get_meta_data($fp);
                $result = "";
                if ($info['timed_out'])
                {
                    fclose($fp);
                    return NBILL_REMOTE_POST_ERROR . "<br /><br />(Timeout)";
                }
                while (!feof($fp))
                {
                    $info = stream_get_meta_data($fp);
                    if ($info['timed_out'])
                    {
                        fclose($fp);
                        return NBILL_REMOTE_POST_ERROR . "<br /><br />(Timeout)";
                    }
                    $result .= fread($fp, 4096);
                }
                fclose($fp);
                if (strlen($result) > 0)
                {
                    //Strip the HTTP headers.
                    $pos = strpos($result, "\r\n\r\n");
                    if ($pos !== false)
                    {
                        $result = substr($result, $pos + 4);
                    }
                    return $result;
                }
                else
                {
                    return NBILL_REMOTE_POST_ERROR;
                }
            }
        }
    }

    /**
    * Fetch the page at the given URL server-to-server using the given host and page via SSL and return the result
    * with the HTTP headers stripped. This function will first attempt to use CURL, but if
    * that is not available, it will try to use sockets instead.
    * @param string Host part of the URL, excluding scheme and page, eg. "nbill.co.uk"
    * @param string Page part of the URL, excluding scheme, domain and querystring, eg. "/documentation/index.php"
    * @param array Associative array of name/value pairs to be used in the querystring, eg. array("page"=>"ug-introduction.html")
    * @param int Number of seconds to wait before giving up
    * @param int Port number to connect to
    * @param array Array of custom headers to send
    * @param boolean Whether or not to suppress the normal headers (user agent etc.) and ONLY use the custom headers supplied
    * @return mixed The contents of the requested resource (could be binary if a file is returned, or HTML if a normal web page is returned)
    */
    public static function get_https($host, $page, $params, $timeout = 20, $port = 443, $custom_headers = array(), $custom_only = false, $cookies = false)
    {
        if (substr($page, 0, 1) == "/")
        {
            $page = substr($page, 1); //Remove leading slash if supplied - we will add it back later
        }

        //Based on unlicensed code in public domain from php.net user comments
        nbf_common::load_language("frontend");
        # working vars
        $vars = "";
        foreach ($params as $key=>$value)
        {
            if (strlen($vars) > 0)
            {
                $vars .= "&";
            }
            $vars .= $key . "=" . urlencode($value);
        }

        //If cURL is enabled, use that
        $result = "";
        if (!$custom_only && defined("CURLOPT_URL") && function_exists("curl_version"))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://" . $host . ($port != 443 ? ":$port" : "") . "/" . $page . "?" . ($vars ? "?" . $vars : ""));
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //Allow redirects
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //Return into a variable
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_HTTPGET, 0); //use GET
            if ($cookies) {
                curl_setopt($ch, CURLOPT_COOKIEJAR, nbf_cms::$interop->site_temp_path . '/cookie.txt');
                curl_setopt($ch, CURLOPT_COOKIEFILE, nbf_cms::$interop->site_temp_path . '/cookie.txt');
            }
            if ($custom_headers && count($custom_headers) > 0)
            {
                foreach ($custom_headers as $custom_header)
                {
                    $custom_header = str_replace("\n", "", str_replace("\r", "", $custom_header)) . "\n\r"; //Add EOL if not present
                    @curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_header);
                }
            }
            $result = curl_exec($ch);
            curl_close($ch);
            if (strlen($result) > 0)
            {
                return $result;
            }
        }
        if (strlen($result) == 0)
        {
            //cURL not available, or didn't work, so try sockets instead
            $header = "";
            if (!$custom_only)
            {
                $header = "Host: $host\r\n";
                $header .= "User-Agent: Mozilla\r\n";
                $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $header .= "Content-Length: ".strlen($vars)."\r\n";
            }
            if ($custom_headers && count($custom_headers) > 0)
            {
                foreach ($custom_headers as $custom_header)
                {
                    $custom_header = str_replace("\n", "", str_replace("\r", "", $custom_header)) . "\n\r"; //Add EOL if not present
                    $header .= $custom_header;
                }
            }
            if (!$custom_only)
            {
                $header .= "Connection: close\r\n\r\n";
            }

            $fp = @fsockopen("ssl://" . $host, $port, $errno, $errstr, $timeout);
            if (!$fp)
            {
              return NBILL_REMOTE_POST_ERROR . "<br /><br />$errstr ($errno)";
            }
            else
            {
                $out = '';
                stream_set_timeout($fp, $timeout);
                fputs($fp, "GET /$page  HTTP/1.0\r\n");
                fputs($fp, $header.$vars);
                fwrite($fp, $out);
                $info = stream_get_meta_data($fp);
                $result = "";
                if ($info['timed_out'])
                {
                    fclose($fp);
                    return NBILL_REMOTE_POST_ERROR . "<br /><br />(Timeout)";
                }
                while (!feof($fp))
                {
                    $info = stream_get_meta_data($fp);
                    if ($info['timed_out'])
                    {
                        fclose($fp);
                        return NBILL_REMOTE_POST_ERROR . "<br /><br />(Timeout)";
                    }
                    $result .= fread($fp, 4096);
                }
                fclose($fp);
                if (strlen($result) > 0)
                {
                    //Strip the HTTP headers.
                    $pos = strpos($result, "\r\n\r\n");
                    if ($pos !== false)
                    {
                        $result = substr($result, $pos + 4);
                    }
                    return $result;
                }
                else
                {
                    return NBILL_REMOTE_POST_ERROR;
                }
            }
        }
    }
}