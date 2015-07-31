<?php
/**
* Language file for the home page
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

define("NBILL_LEDGER_GUESSES", NBILL_BRANDING_NAME . " was unable to accurately calculate the breakdown of net/tax/gross amounts on the nominal ledger records for the following transactions. Please check each of the records listed below and enter the correct tax breakdown figures for the nominal ledger entries.");
define("NBILL_LEDGER_GUESSES_DELETE", "Don't bug me!");
define("NBILL_LEDGER_GUESSES_DELETE_SURE", "Are you sure you want to forget about these innaccurate ledger entries? This will mean your nominal ledger report and tax summary will be INACCURATE for any date ranges involving the listed transactions.");
define("NBILL_PAYMENT_NO", "Payment");
define("NBILL_RECEIPT_NO", "Receipt");
define("NBILL_IE6_NOT_SUPPORTED", "WARNING! Some features of " . NBILL_BRANDING_NAME . " Administrator will not work properly in IE6 (front end features should all work ok though). Please upgrade your browser!");

//Version 2.7.0
define("NBILL_MAIN_DASHBOARD", "Dashboard");

//Version 3.1.1
define("NBILL_WARNING_VERSION_CHECK_OFF", "WARNING! Automatic version checking is disabled. You will not be notified when new versions of " . NBILL_BRANDING_NAME . " are available. <a href=\"%1\$s\">" . NBILL_CLICK_HERE . "</a> to enable automatic version checking, or <a href=\"%2\$s\">" . NBILL_CLICK_HERE . "</a> to suppress this warning but leave version checking switched off.");
define("NBILL_VERSION_CHECKING_OFF_CONFIRM", "A cookie has been saved on your browser to suppress the warning message. If you clear your cookies, the warning will be shown again. Please visit the " . NBILL_BRANDING_NAME . " forum regularly to check for updates.");
define("NBILL_WARNING_VAT_RATE_CHECK_OFF", "WARNING! Automatic VAT rate checking is disabled. If an EU VAT rate changes, your system will NOT be updated automatically. <a href=\"%1\$s\">" . NBILL_CLICK_HERE . "</a> to enable automatic VAT rate updates, or <a href=\"%2\$s\">" . NBILL_CLICK_HERE . "</a> to suppress this warning but leave automatic VAT rate updates switched off.");
define("NBILL_VAT_RATE_CHECKING_OFF_CONFIRM", "A cookie has been saved on your browser to suppress the warning message. If you clear your cookies, the warning will be shown again. If you sell digital goods within the EU, please ensure you keep the VAT rates up-to-date manually.");