<?php
/**
* Language file for the default invoice template
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Invoicing Template
define("NBILL_PRT_INVOICE_TITLE", "INVOICE");
define("NBILL_PRT_INVOICE_NO", "Invoice No:");
define("NBILL_PRT_AMOUNT_DUE", "Invoice Total:");
define("NBILL_PAYMENT_RECEIVED", "The following payment(s) have been received (thank you):");
define("NBILL_FULL_PAYMENT_RECEIVED", "Paid in Full. Thank you.");
define("NBILL_REMITTANCE_ADVICE", "Remittance Advice");
define("NBILL_REMITTANCE_INTRO", "If sending payment in the mail, please enclose this remittance advice to enable us to identify what your payment is for. Thank you.");
define("NBILL_RECEIVED_FROM", "Received From");
define("NBILL_PAYMENT_DATE", "Payment Date");
define("NBILL_PAYMENT_METHOD", "Method");
define("NBILL_PAYMENT_AMOUNT" , "Payment Amount");
define("NBILL_PAYMENT_REFERENCE", "Our Reference");
define("NBILL_TOTAL_PAID", "Total Amount Paid");
define("NBILL_TOTAL_DUE", "Amount Outstanding:");
define("NBILL_REFERENCE_UNKNOWN", "Not Yet Assigned");
define("NBILL_IF_NO_SCHEDULE", "If you do not already have a payment schedule set up for this, please");
define("NBILL_PAY_THIS_INVOICE", "click here to pay this invoice");

//Version 3.0.0
define("NBILL_PRT_DUE_DATE", "Due Date:");
define("NBILL_CLICK_OR_SCAN_QR_CODE", " or scan the following QR code:");
define("NBILL_SCAN_HERE", "Scan here to pay this invoice.");

//Version 3.0.6
define("NBILL_PRT_OVERPAID", "(Overpaid!)");

//Version 3.1.0
define("NBILL_PRT_DELIVERY_NOTE_TITLE", "DELIVERY NOTE");
define("NBILL_PRT_RELATED_INVOICE_NO", "Related Invoice No:");

//Version 3.1.1
define("NBILL_PRT_ZERO_RATED", "This is a zero-rated EU intra-community supply.");