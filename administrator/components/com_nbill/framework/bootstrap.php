<?php
/**
* Initialisation and loading of nBill framework files - must be called once before anything else, both for back end and front end use
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

if (!defined('NBILL_VALID_NBF')) {
    define('NBILL_VALID_NBF', '1'); //So extensions written for nBill 2 will still work
}

$ini_mem = ini_get('memory_limit');
if (intval(substr($ini_mem, 0, strlen($ini_mem) - 1)) < 64) {
    @ini_set("memory_limit", "64M"); //Shouldn't need this much memory, but it is safer.
}
@ini_set('mysql.connect_timeout', 300);
@ini_set('default_socket_timeout', 300);

//Magic quotes are problematic
if (get_magic_quotes_gpc()) {
    if (!function_exists('stripslashes_deep')) {
        function stripslashes_deep($value)
        {
            $value = is_array($value) ?
                        array_map('stripslashes_deep', $value) :
                        stripslashes($value);
            return $value;
        }
    }
    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

//Orientation
$admin_base_path = realpath(dirname(__FILE__) . '/..');

//Autoloader
require_once($admin_base_path . "/class_map.php");
require_once($admin_base_path . "/autoload.php");

//Framework files
require_once($admin_base_path . "/framework/classes/nbill.globals.class.php");
require_once($admin_base_path . "/framework/classes/nbill.common.class.php");
require_once($admin_base_path . "/framework/classes/nbill.version.class.php");
require_once($admin_base_path . "/framework/classes/nbill.cms.class.php");

//Reset interop in case things have changed since user subscription plugin initialised (eg. Joom!Fish plugin might be available now)
nbf_cms::set_interop(true);

//Branding and language
if (!defined("NBILL_BRANDING_NAME")) {
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/branding.php");
}
if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/language/" . nbf_cms::$interop->language . "/nbill." . nbf_cms::$interop->language . ".php")) {
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/language/" . nbf_cms::$interop->language . "/nbill." . nbf_cms::$interop->language . ".php");
} else {
    @include_once(nbf_cms::$interop->nbill_admin_base_path . "/language/en-GB/nbill.en-GB.php");
}

//No directory traversal thanks
if (nbf_common::nb_strpos(nbf_common::get_param($_REQUEST, 'action'), "..") !== false || nbf_common::nb_strpos(nbf_common::get_param($_REQUEST, 'action'), "/") !== false || nbf_common::nb_strpos(nbf_common::get_param($_REQUEST, 'action'), "\\") !== false) {
    die("Hacking Attempt");
}

//Store the locale and convert to en_US (so database inserts are not messed up by commas instead of dots)
nbf_globals::$system_locale = @setlocale(LC_ALL, 0);
@setlocale(LC_ALL, "en_US", "en-US", "en", "English_United States", "en_US.UTF-8");

//Load supporting libraries, initialise timezone
require_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.number.format.php");
require_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.html.class.php");
nBillConfigurationService::getInstance()->getConfig()->applyTimezone();

//If encoded ampersands were used in the URL, strip out any unnecessary fluff
foreach ($_REQUEST as $key=>$value) {
    if (substr($key, 0, 4) == "amp;") {
        $_REQUEST[substr($key, 4)] = $value;
        unset($_REQUEST[$key]);
    }
}
foreach ($_GET as $key=>$value) {
    if (substr($key, 0, 4) == "amp;") {
        $_GET[substr($key, 4)] = $value;
        unset($_GET[$key]);
    }
}

//In demo mode, do not allow file uploads
if (nbf_cms::$interop->demo_mode) {
    if (count($_FILES) > 0) {
        foreach ($_FILES as $upload) {
            if (strlen(@$upload['tmp_name'])) {
                @unlink($upload['tmp_name']);
            }
        }
        $_FILES = array();
    }
}