<?php
/**
* Instead of using global variables, this class provides static members that can be accessed
* from anywhere.
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
* Class just contains static members to use instead of global variables
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_globals
{
    /** @var string System locale string */
    static public $system_locale = "";
    /** @var string Default vendor ID for filtering admin lists */
    static public $vendor_filter = "";
    /** @var string Message to display at the top of the next page */
    static public $message = "";
    /** @var array Array of database error messages (on install or upgrade) */
    static public $db_errors = array();
    /** @var boolean Whether or not a fatal error has been handled */
    static public $fatal_error_handled;
    /** @var array Array of field names for items that fail validation */
    static public $fields_in_error = array();
    /** @var boolean Legacy flag to indicate that online payment should be skipped */
    static public $suppress_payment = false;
    /** @var boolean This holds the value we need to use when processing text files during extension installation (so we don't forget if we have to turn it off for a binary file) */
    static public $overall_convert_utf8 = false;
    /** @var boolean This holds the value we are currently using during extension installation (so will only be true for text files) */
    static public $convert_utf8 = false;
    /** @var integer Maximum number of records to load in editors (eg. number of unpaid invoices on income editor, number of clients on invoice editor) */
    static public $record_limit = 500;
    /** @var boolean Whether or not to link directly to external files when running administrator through front end */
    static public $direct_link = true;
    /** @var boolean Whether or not we are showing the current page in a popup window */
    static public $popup = false;
    /** @var boolean Whether or not to attempt to write all actions to a log file */
    static public $trace_debug = false;
}

/**
* Represents the details of a stored transaction
*/
class nbf_gateway_txn
{
    public static $pending_order_id;
    public static $document_ids;
    public static $net_amount;
    public static $tax_amount;
    public static $shipping_amount;
    public static $shipping_tax_amount;
    public static $user_ip;
    public static $vendor_id;
    public static $discount_voucher_code;
    public static $created_orders = array();
    public static $callback_file;
    public static $callback_function;
}
