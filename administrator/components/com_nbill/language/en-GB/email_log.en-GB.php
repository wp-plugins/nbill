<?php
/**
* Language file for the E-mail log
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
* 
* @access private* 
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

define("NBILL_EMAIL_LOG_TITLE", "E-Mail Log");
define("NBILL_EMAIL_LOG_INTRO", "This log contains a record for every e-mail that the system has attempted to send (not including e-mails to the administrator). Records are kept for up to a maximum of 1 year, but you can delete old entries sooner than that if you wish. Click on the subject of an e-mail to view the full message.");
define("NBILL_EMAIL_LOG_DATE", "Date");
define("NBILL_EMAIL_LOG_STATUS", "Status Filter");
define("NBILL_EMAIL_LOG_ITEM_STATUS", "Status");
define("NBILL_EMAIL_LOG_SHOW_ALL", "Show All");
define("NBILL_EMAIL_LOG_SHOW_SUCCESS", "Successful Only");
define("NBILL_EMAIL_LOG_SHOW_FAILURE", "Failures Only");
define("NBILL_EMAIL_LOG_TYPE", "Type");
define("NBILL_EMAIL_LOG_SHOW_PENDING", "Pending Orders Only");
define("NBILL_EMAIL_LOG_SHOW_ORDERS", "Orders Only");
define("NBILL_EMAIL_LOG_SHOW_QUOTES", "Quotes Only");
define("NBILL_EMAIL_LOG_SHOW_INVOICES", "Invoices Only");
define("NBILL_EMAIL_LOG_RECORD", "Related Record");
define("NBILL_EMAIL_LOG_PENDING", "Pending Order");
define("NBILL_EMAIL_LOG_ORDER", "Order");
define("NBILL_EMAIL_LOG_QUOTE", "Quote");
define("NBILL_EMAIL_LOG_INVOICE", "Invoice");
define("NBILL_EMAIL_LOG_UNKNOWN", "Unknown");
define("NBILL_EMAIL_LOG_RECORD_DELETED", "(Record Deleted)");
define("NBILL_EMAIL_LOG_TO", "To");
define("NBILL_EMAIL_LOG_CC", "CC");
define("NBILL_EMAIL_LOG_BCC", "BCC");
define("NBILL_EMAIL_LOG_SUBJECT", "Subject");
define("NBILL_EMAIL_LOG_NO_SUBJECT", "[No Subject]");
define("NBILL_EMAIL_LOG_NO_DETAILS", "E-mail details could not be loaded from the database");
define("NBILL_EMAIL_LOG_OLD_DELETED", "%s old e-mail(s) deleted.");