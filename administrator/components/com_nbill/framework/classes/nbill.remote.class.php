<?php
/**
* Class file just containing static methods to allow posts and gets via http (using curl if available, or sockets if not).
* Deprecated - for backward compatability only - use nbill.http.class.php now
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.http.class.php");

/**
* Static methods to allow posts and gets via http (using curl if available, or sockets if not).
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_remote
{
    /**
    * Post the given params server-to-server to the given host and page and return the result
    * with the HTTP headers stripped. This function will first attempt to use CURL, but if
    * that is not available, it will try to use sockets instead.
    * @param string Host part of the URL, excluding scheme and page, eg. "nbill.co.uk"
    * @param string Page part of the URL, excluding scheme, domain and querystring, eg. "/documentation/index.php"
    * @param array Associative array of name/value pairs to be posted to the URL, eg. array("page"=>"ug-introduction.html")
    * @return mixed The contents of the requested resource (could be binary if a file is returned, or HTML if a normal web page is returned)
    */
    public static function post_remote($host, $page, $params)
    {
        return nbf_http::post_http($host, $page, $params);
    }

    /**
    * Post the given params server-to-server to the given host and page via SSL and return the result
    * with the HTTP headers stripped. This function will first attempt to use CURL, but if
    * that is not available, it will try to use sockets instead.
    * @param string Host part of the URL, excluding scheme and page, eg. "nbill.co.uk"
    * @param string Page part of the URL, excluding scheme, domain and querystring, eg. "/documentation/index.php"
    * @param array Associative array of name/value pairs to be posted to the URL, eg. array("page"=>"ug-introduction.html")
    * @return mixed The contents of the requested resource (could be binary if a file is returned, or HTML if a normal web page is returned)
    */
    public static function post_remote_s($host, $page, $params)
    {
        return nbf_http::post_https($host, $page, $params);
    }

    /**
    * Fetch the page at the given URL server-to-server using the given host and page and return the result
    * with the HTTP headers stripped. This function will first attempt to use CURL, but if
    * that is not available, it will try to use sockets instead.
    * @param string Host part of the URL, excluding scheme and page, eg. "nbill.co.uk"
    * @param string Page part of the URL, excluding scheme, domain and querystring, eg. "/documentation/index.php"
    * @param array Associative array of name/value pairs to be used in the querystring, eg. array("page"=>"ug-introduction.html")
    * @return mixed The contents of the requested resource (could be binary if a file is returned, or HTML if a normal web page is returned)
    */
    public static function get_remote($host, $page, $params, $timeout = 20)
    {
        return nbf_http::get_http($host, $page, $params, $timeout);
    }

    /**
    * Fetch the page at the given URL server-to-server using the given host and page via SSL and return the result
    * with the HTTP headers stripped. This function will first attempt to use CURL, but if
    * that is not available, it will try to use sockets instead.
    * @param string Host part of the URL, excluding scheme and page, eg. "nbill.co.uk"
    * @param string Page part of the URL, excluding scheme, domain and querystring, eg. "/documentation/index.php"
    * @param array Associative array of name/value pairs to be used in the querystring, eg. array("page"=>"ug-introduction.html")
    * @return mixed The contents of the requested resource (could be binary if a file is returned, or HTML if a normal web page is returned)
    */
    public static function get_remote_s($host, $page, $params, $timeout = 20)
    {
        return nbf_http::get_https($host, $page, $params, $timeout);
    }
}