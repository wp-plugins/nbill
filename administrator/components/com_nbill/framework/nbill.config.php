<?php
/**
* Configuration options for nBill
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
* Allows tweaking of some of the default options, eg. to bypass Mambo or Joomla's HTML editor and use nicEdit instead
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_config
{
    /** @var Choose which wysiwyg editor to use - either "[CMS]" to use the current editor from Joomla or Mambo, "none" for a plain textbox, or "nicEdit" to use the nBill default editor (may be extended later to allow other editors to be specifically named) */
    public static $editor = '[CMS]';
    /** @var The port number used for SSL (https) requests (default is 443, only change this if your server uses a non-standard port for SSL) */
    public static $ssl_port = "443";
    /** @var Choose type of mailer: '[CMS]' = Pick up values from Mambo/Joomla and ignore all other mail settings in this file; 'mail' = PHP Mail Function; 'sendmail' = Sendmail script (path required); 'smtp' = SMTP server (connection details required) ; 'ssmtp' = Secure SMTP (connection details required) */
    public static $mailer = '[CMS]';
    /** @var Default e-mail address to send from (if not specified by the calling application) */
    public static $mailfrom = '';
    /** @var Default from name (if not specified by the calling application) */
    public static $fromname = '';
    /** @var Path to Sendmail (if mailer = 'sendmail') */
    public static $sendmail = '/usr/sbin/sendmail';
    /** @var Whether SMTP server requires authentication */
    public static $smtpauth = '1';
    /** @var SMTP Username */
    public static $smtpuser = '';
    /** @var SMTP Password */
    public static $smtppass = '';
    /** @var SMTP Host */
    public static $smtphost = 'localhost';
    /** @var SMTP Port (25 is the standard port) */
    public static $smtpport = '25';
    /** @var sSMTP Port (465 is standard) */
    public static $ssmtpport = '465';
    /** @var FTP Address of local website */
    public static $ftp_address = '';
    /** @var Port number to use for FTP connection */
    public static $ftp_port = '';
    /** @var FTP Username */
    public static $ftp_username = '';
    /** @var FTP Password */
    public static $ftp_password = '';
    /** @var Root folder that the supplied FTP credentials allow access to */
    public static $ftp_root = '';
    /** @var boolean Whether or not to log discount evaluations in a file for debugging purposes (to see why a discount is or isn't being applied) */
    public static $trace_discounts = false;
    /** @var Whether or not to use the old fashioned MySQL library instead of MySQLi */
    public static $mysql = false;
    /** @var Name of interop class to load. [AUTO] = detect automatically (recommended!) */
    public static $interop_class = '[AUTO]';
    /** @var Location of CMS config file ([DEFAULT] = default location for the CMS, otherwise, specify full file path including file name) */
    public static $cms_config_file = '[DEFAULT]';
    /** @var Whether to trace all frontend activity in a log file (WARNING! only for use when debugging - can produce a lot of output!) */
    public static $trace_debug_frontend = false;
    /** @var Whether to trace all admin activity in a log file (WARNING! only for use when debugging - can produce a lot of output!) */
    public static $trace_debug_admin = false;
}