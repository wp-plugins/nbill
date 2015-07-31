<?php
/**
* Language file for the default credit note template
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Credit note Template
define("NBILL_PRT_CREDIT_TITLE", "CREDIT NOTE");
define("NBILL_PRT_CREDIT_NO", "Credit Note No:");
define("NBILL_PRT_AMOUNT_REFUNDED", "Amount Refunded:");
define("NBILL_PAYMENT_MADE", "Payment of this credit note has been processed.");

//Version 2.4.0
define("NBILL_PAYMENT_SENT", "The following payment(s) have been sent:");
define("NBILL_FULL_PAYMENT_SENT", "Paid in Full.");
define("NBILL_CR_PAYMENT_DATE", "Payment Date");
define("NBILL_CR_PAYMENT_METHOD", "Method");
define("NBILL_CR_PAYMENT_AMOUNT" , "Payment Amount");
define("NBILL_CR_PAYMENT_REFERENCE", "Our Reference");
define("NBILL_CR_TOTAL_PAID", "Total Amount Paid");
define("NBILL_CR_TOTAL_DUE", "Amount Outstanding:");
define("NBILL_CR_REFERENCE_UNKNOWN", "Not Yet Assigned");