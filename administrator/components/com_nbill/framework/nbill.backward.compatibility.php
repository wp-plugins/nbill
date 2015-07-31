<?php
/**
* Deprecated functions for backward compatibility only (this file should not be loaded unless absolutely necessary)
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
* 
* @access private* 
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Flag the fact that this file has already been loaded
define("NBILL_LEGACY_LOADED", "1");

//Populate old core and posted values in $_POST
foreach ($_POST as $key=>$value)
{
    if (nbf_common::nb_strpos($key, "NBILL_CORE_") === 0 && nbf_common::nb_strlen(nbf_common::get_param($_POST, 'INV_CORE_' . substr($key, 10))) == 0)
    {
        $_POST['INV_CORE_' . substr($key, 10)] = $value;
    }
    if (nbf_common::nb_strpos($key, "ctl_") === 0 && nbf_common::nb_strlen(nbf_common::get_param($_POST, substr($key, 4))) == 0)
    {
        $_POST[substr($key, 4)] = $value;
    }
}

//Old Mambo constants
if (!defined("_VALID_MOS"))
{
    define("_VALID_MOS", "1");
}

//Old nBill constants
if (!defined("INV_ADMIN_BASE_PATH"))
{
    define("INV_ADMIN_BASE_PATH", nbf_cms::$interop->nbill_admin_base_path);
}
if (!defined("INV_FE_BASE_PATH"))
{
    define("INV_FE_BASE_PATH", nbf_cms::$interop->nbill_fe_base_path);
}
if (!defined("INV_BASE_PATH"))
{
    define("INV_BASE_PATH", nbf_cms::$interop->site_base_path);
}
if (!defined("INV_BRANDING_NAME"))
{
    define("INV_BRANDING_NAME", NBILL_BRANDING_NAME);
}
if (!defined("INV_PREV"))
{
    define("INV_PREV", NBILL_PREV);
}
if (!defined("INV_TAX"))
{
    define("INV_TAX", NBILL_TAX);
}
if (!defined("INV_SUBMIT"))
{
    define("INV_SUBMIT", NBILL_SUBMIT);
}

//Old Mambo globals
global $database;
if (!$database)
{
    $database = nbf_cms::$interop->database;
    $database->legacy_mode = true;
}
global $my;
if (!$my)
{
    $my = nbf_cms::$interop->user;
}
global $mosConfig_live_site;
if (!$mosConfig_live_site)
{
    $mosConfig_live_site = nbf_cms::$interop->live_site;
}
global $mosConfig_absolute_path;
if (!$mosConfig_absolute_path)
{
    $mosConfig_absolute_path = nbf_cms::$interop->site_base_path;
}
global $mosConfig_sitename;
if (!$mosConfig_sitename)
{
    $mosConfig_sitename = nbf_cms::$interop->site_name;
}

//Old nBill globals
global $nb_database;
if (!$nb_database)
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->legacy_mode = true;
}
global $nb_cms_version;
if (!$nb_cms_version)
{
    $nb_cms_version = new stdClass();
    $nb_cms_version->cms = nbf_cms::$interop->cms_name;
    $nb_cms_version->version = nbf_cms::$interop->cms_version;
    $nb_cms_version->minor_version = null;
    if (property_exists(nbf_cms::$interop, "cms_minor_version"))
    {
        $nb_cms_version->minor_version = nbf_cms::$interop->cms_minor_version;
    }
}

//Mambo functions
if (!function_exists("mosMail"))
{
    define("NBILL_LEGACY_MOSMAIL_LOADED", "1");
    function mosMail($from, $fromname, $recipient, $subject, $body, $mode = 0, $cc = null, $bcc = null, $attachment = null, $replyto = null, $replytoname = null)
    {
        return nbf_cms::$interop->send_email($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
    }
}
if (!function_exists("mosGetParam"))
{
    if (!defined("_MOS_NOTRIM"))
    {
        define( "_MOS_NOTRIM", 0x0001 );
    }
    if (!defined("_MOS_ALLOWHTML"))
    {
        define( "_MOS_ALLOWHTML", 0x0002 );
    }
    if (!defined("_MOS_ALLOWRAW"))
    {
        define( "_MOS_ALLOWRAW", 0x0004 );
    }
    function mosGetParam(&$array, $key, $default_value = null, $mask=0)
    {
        $return_value = nbf_common::get_param($array, $key, $default_value, false, false, ($mask && _MOS_ALLOWHTML) || ($mask && _MOS_ALLOWRAW));
        if (!($mask && _MOS_NOTRIM))
        {
            return trim($return_value);
        }
    }
}

//Old nBill functions
include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.remote.class.php");
if (!function_exists("nbPostHTTP"))
{
    function nbPostHTTP($host, $page, $params)
    {
        return nbf_remote::post_remote($host, $page, $params);
    }
}
if (!function_exists("nbPostHTTPS"))
{
    function nbPostHTTPS($host, $page, $params)
    {
        return nbf_remote::post_remote_s($host, $page, $params);
    }
}
if (!function_exists("nbGetHTTP"))
{
    function nbGetHTTP($host, $page, $params)
    {
        return nbf_remote::get_remote($host, $page, $params);
    }
}
if (!function_exists("nbGetHTTPS"))
{
    function nbGetHTTPS($host, $page, $params)
    {
        return nbf_remote::get_remote_s($host, $page, $params);
    }
}
if (!function_exists("nbMail"))
{
    function nbMail($from, $fromname, $recipient, $subject, $body, $mode = 0, $cc = null, $bcc = null, $attachment = null, $replyto = null, $replytoname = null)
    {
        return nbf_cms::$interop->send_email($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
    }
}
if (!function_exists("calculate_totals"))
{
    function calculate_totals(&$orders, $currency, $carriage_id, $discount_voucher_code,
                    &$total_net, &$total_tax, &$total_carriage, &$total_carriage_tax, &$total_gross, &$regular_total_gross,
                    &$normal_tax_rate, &$carriage_tax_rate, &$carriage_service, $renewal = false, $order_id = null, $suppress_discounts = false)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");
        $dummy = null;
        $standard_totals = new nbf_totals();
        $regular_totals = new nbf_totals();
        $country = "WW"; //We have no way of knowing, so country-specific discounts and fees will not work (should not cause a problem as any code using this function will not expect that feature anyway). Ditto for gateway-specific fees/discounts
        $ret_val = nbf_payment::calculate_totals($orders, $currency, $country, $carriage_id, $discount_voucher_code, 
                    $standard_totals, $regular_totals, $dummy, $normal_tax_rate, $carriage_tax_rate, 
                    $carriage_service, $renewal, $order_id, $suppress_discounts);
        $total_net = $standard_totals->total_net;
        $total_tax = $standard_totals->total_tax;
        $total_carriage = $standard_totals->total_shipping;
        $total_carriage_tax = $standard_totals->total_shipping_tax;
        $total_gross = $standard_totals->total_gross;
        $regular_total_gross = $regular_totals->total_gross;
        return $ret_val;
    }
}
if (!function_exists("hand_over_to_gateway"))
{
    function hand_over_to_gateway($payment_gateway, $suppress_payment, $total_gross, $regular_total_gross,
                        $orders, $total_carriage, $total_carriage_tax, $normal_tax_rate, $carriage_tax_rate, $pending_order_id,
                        $form_id, $vendor_id, $auto_renew, $payment_frequency, $currency, $total_net, $total_tax, &$abort,
                        $invoice_ids = array(), $created_orders = array(), $tax_rates = array(), $tax_amounts = array(),
                        $expiry_date = 0, $carriage_service = "", $invoice_no = "", $turn_on_auto_renew = 0, $relating_to = "")
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");
        $standard_totals = new nbf_totals();
        $standard_totals->total_net = $total_net;
        $standard_totals->total_tax = $total_tax;
        $standard_totals->total_shipping = $total_carriage;
        $standard_totals->total_shipping_tax = $total_carriage_tax;
        $standard_totals->total_gross = $total_gross;
        $regular_totals = new nbf_totals();
        $regular_totals->total_gross = $regular_total_gross;
        $billing_data = array();
        nbf_gateway_txn::$created_orders = $created_orders;
        
        //Prepare the billing values required by the payment gateway (assume default profile fields)
        $billing_data['first_name'] = nbf_common::get_param($_POST, 'NBILL_CORE_first_name');
        $billing_data['last_name'] = nbf_common::get_param($_POST, 'NBILL_CORE_last_name');
        $billing_data['address_1'] = nbf_common::get_param($_POST, 'NBILL_CORE_address_1');
        $billing_data['address_2'] = nbf_common::get_param($_POST, 'NBILL_CORE_address_2');
        $billing_data['address_3'] = nbf_common::get_param($_POST, 'NBILL_CORE_address_3');
        $billing_data['town'] = nbf_common::get_param($_POST, 'NBILL_CORE_town');
        $billing_data['state'] = nbf_common::get_param($_POST, 'NBILL_CORE_state');
        $billing_data['postcode'] = nbf_common::get_param($_POST, 'NBILL_CORE_postcode');
        $billing_data['telephone'] = nbf_common::get_param($_POST, 'NBILL_CORE_telephone');
        $billing_data['company_name'] = nbf_common::get_param($_POST, 'NBILL_CORE_company_name');
        $billing_data['country'] = nbf_common::get_param($_POST, 'NBILL_CORE_country');
        $billing_data['email_address'] = nbf_common::get_param($_POST, 'NBILL_CORE_email_address');
        $billing_data['username'] = nbf_common::get_param($_POST, 'NBILL_CORE_username');
        $billing_data['password'] = nbf_common::get_param($_POST, 'NBILL_CORE_password');
        
        $no_of_payments = 1;
        
        $g_tx_id = nbf_payment::prepare_for_payment($payment_gateway, $suppress_payment, $standard_totals, $regular_totals, 
                        $orders, $normal_tax_rate, $carriage_tax_rate, $pending_order_id, $form_id, $vendor_id, 
                        $auto_renew, $payment_frequency, $currency, $abort, $expiry_date, $carriage_service, $relating_to, 
                        $no_of_payments, $billing_data, $invoice_no, $turn_on_auto_renew, $invoice_ids, $tax_rates, 
                        $tax_amounts);
                        
        return nbf_payment::hand_over_to_gateway($g_tx_id, $payment_gateway, $suppress_payment, $standard_totals, $regular_totals,
                        $orders, $vendor_id, $auto_renew, $payment_frequency, $currency, $abort, $expiry_date, $carriage_service, $relating_to, 
                        $no_of_payments, $billing_data, $invoice_no, $turn_on_auto_renew, $invoice_ids, $tax_rates, $tax_amounts);
    }
}
if (!function_exists("set_order_expiry"))
{
    function set_order_expiry($month, $year)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");
        return nbf_payment::set_order_expiry($month, $year);
    }
}
if (!function_exists("gateway_processing"))
{
    function gateway_processing($g_tx_id, $payment_amount, $payment_currency, &$warning_message, &$error_message, $customer = "", $reference = "", $notes = NBILL_AUTO_GENERATED_INCOME)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");
        nbf_payment::gateway_processing($g_tx_id, $payment_amount, $payment_currency, $warning_message, $error_message, $customer);
    }
}
if (!function_exists("finish_gateway_processing"))
{
    function finish_gateway_processing($warning_message, $error_message, $add_debug_info = false, $redirect_url = "", $g_tx_id = 0, $thanks = "")
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");
        nbf_payment::finish_gateway_processing($warning_message, $error_message, $add_debug_info, $redirect_url, $g_tx_id, $thanks);
    }
}
if (!function_exists("nb_redirect"))
{
    function nb_redirect($url)
    {
        nbf_common::redirect($url);
    }
}
if (!function_exists("nbGetUserIP"))
{
    function nbGetUserIP()
    {
        return nbf_common::get_user_ip();
    }
}
if (!function_exists("get_next_payment_date"))
{
    function get_next_payment_date($first, $start, $payment_frequency, $set_to_midnight = true)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
        return nbf_date::get_next_payment_date($first, $start, $payment_frequency, $set_to_midnight);
    }
}
if (!function_exists("generate_invoices_due"))
{
    function generate_invoices_due($send_email, $email_address, $show_details, $generation_offset, $vendor_id)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.invoice.generator.class.php");
        nbf_generator::generate_invoices_due($send_email, $email_address, $show_details, $generation_offset, $vendor_id);
    }
}
//Very old nBill functions
if (!function_exists("PostHTTP"))
{
    function PostHTTP($host, $page, $params)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.remote.class.php");
        return nbf_remote::post_remote($host, $page, $params);
    }
}
if (!function_exists("PostHTTPS"))
{
    function PostHTTPS($host, $page, $params)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.remote.class.php");
        return nbf_remote::post_remote_s($host, $page, $params);
    }
}
if (!function_exists("GetHTTP"))
{
    function GetHTTP($host, $page, $params)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.remote.class.php");
        return nbf_remote::get_remote($host, $page, $params);
    }
}
if (!function_exists("GetHTTPS"))
{
    function GetHTTPS($host, $page, $params)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.remote.class.php");
        return nbf_remote::get_remote_s($host, $page, $params);
    }
}