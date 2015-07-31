<?php
/**
* Initialisation of the admin features (called from installer as well as nBill admin)
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

if (!function_exists("fatal_pre_req_check"))
{
    function fatal_pre_req_check()
    {
        $message = array();

        //PHP5+
        if(!defined('PHP_VERSION_ID'))
        {
            $version = PHP_VERSION;
            define('PHP_VERSION_ID', ($version{0} * 10000 + $version{2} * 100 + $version{4}));
        }
        if (PHP_VERSION_ID < 50400)
        {
            $message[] = "PHP 5.4 or later is required.";
        }

        //SimpleXML
        if (!extension_loaded('simplexml') || !function_exists('simplexml_load_string'))
        {
            $message[] = "The SimpleXML PHP extension must be installed (this is usually installed by default).";
        }

        if (count($message) > 0)
        {
            ?>
            <h3>Sorry, your system does not meet the pre-requisites for running this software!</h3>
            <ul>
            <?php
            foreach ($message as $error)
            {
                ?><li><?php echo $error; ?></li><?php
            }
            ?>
            </ul>
            <?php
            return false;
        }
        return true;
    }
}

$pre_req_ok = fatal_pre_req_check();

if ($pre_req_ok)
{
    //Let it be known that we are running on the admin side
    if (!defined("NBILL_ADMIN"))
    {
        define("NBILL_ADMIN", "1");
    }

    $admin_path = realpath(dirname(__FILE__));
    require_once($admin_path . '/framework/bootstrap.php');

    //Register whether we are in a popup window
    if (property_exists('nbf_globals', 'popup')) //Have to check for existence in case of upgrade from 2.2.0 or earlier
    {
        nbf_globals::$popup = nbf_common::get_param($_REQUEST, 'nbill_popup') || !(strpos(nbf_common::get_requested_page(true), html_entity_decode(nbf_cms::$interop->admin_popup_page_prefix)) === false || nbf_common::get_param($_REQUEST, 'nbill_admin_via_fe') && !nbf_common::get_param($_REQUEST, 'nbill_admin_fe_no_control_bar'));
        if (nbf_globals::$popup) {
            for ($i=0;$i<10;$i++) {
                @ob_end_clean(); //Don't include anything from the CMS
            }
        }
        //Load the stylesheet (unless we are on a popup and suppression not overridden)
        if (!nbf_globals::$popup || nbf_common::get_param($_REQUEST, 'use_stylesheet')) {
            nbf_cms::$interop->add_html_header("<link rel=\"stylesheet\" href=\"" . nbf_cms::$interop->nbill_site_url_path . "/style/nbill_tabs.css\" type=\"text/css\" />");
        }
    }

    //Set up the error handler (still more effective than try/catch, as this handles fatal errors too)
    require_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.error.handler.php");
    ob_start("fatal_error_handler_admin");
}