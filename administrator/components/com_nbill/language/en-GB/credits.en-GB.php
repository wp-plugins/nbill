<?php
/**
* Language file for Credit Notes
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

define("NBILL_CREDITS_TITLE", "Credit Notes (Refunds)");
define("NBILL_CREDITS_INTRO", "Whenever you issue a refund, you should also produce an accompanying credit note detailing what the refund was for, and including a tax breakdown, if applicable.");
define("NBILL_INVOICE_NUMBER_CR", "Credit Note Number");
define("NBILL_BILLING_NAME_CR", "Payee");
define("NBILL_INVOICE_DATE_CR", "Credit Date");
define("NBILL_FIRST_ITEM_CR", "First Item on Credit Note");
define("NBILL_EDIT_INVOICE_CR", "Edit Credit Note");
define("NBILL_EMAIL_NOW_CR", "Send this credit note to the client by e-mail now");
define("NBILL_BILLING_NAME_REQUIRED_CR", "Please enter the payee name");
define("NBILL_BILLING_ADDRESS_REQUIRED_CR", "Please enter the payee address");
define("NBILL_NEW_INVOICE_CR", "New Credit Note");
define("NBILL_INVOICE_DETAILS_CR", "Credit Note Details");
define("NBILL_INSTR_INVOICE_NUMBER_CR", "<strong>Note:</strong> Leave blank if adding a new credit note - the component will automatically assign the next available credit note number.");
define("NBILL_INSTR_BILLING_NAME_CR", "The name of the person and/or company being refunded.");
define("NBILL_BILLING_ADDRESS_CR", "Payee Address");
define("NBILL_INVOICE_ITEMS_CR", "Credit Note Items");
define("NBILL_INSTR_INVOICE_PAID_IN_FULL_CR", "<strong>WARNING!</strong> Setting this value from here overrides the normal process of creating an 'Expenditure' item. This is OK if you do not intend to use the expenditure feature, but it is recommended that you mark credit notes as paid by clicking on the red 'X' on the credit note list, rather than setting the value here.");
define("NBILL_INSTR_VENDOR_NAME_CR", "Enter the name that you want to appear on your credit notes");
define("NBILL_INSTR_VENDOR_ADDRESS_CR", "Enter the address that you want to appear on your credit notes");

//Version 1.2.6
define("NBILL_INVOICE_RECORD_LIMIT_WARNING_CR", "WARNING! As there are %s or more clients in your database, only the first %s have been loaded into the above list. If the client you require is not here, please use the 'create new credit note' icon on the client list (the appropriate record will then be selected here automatically).");

//Version 2.0.0
define("NBILL_INVOICE_TOTAL_THIS_PAGE_CR", "Total for all credit notes shown on THIS page:");
define("NBILL_INVOICE_TOTAL_ALL_PAGES_CR", "Total for ALL credit notes on ALL pages in the selected date range:");

//Version 2.3.0
define("NBILL_CREDITS_REFUND_INVOICE", "Refund of invoice %s");
define("NBILL_CREDITS_FROM_INVOICE_WARNING", "The values on this credit note have been automatically pre-populated based on the selected invoice. For a partial refund, you will need to edit the amounts. Please also check the nominal ledger code - if you use a different code for expenditure than you do for income you might want to change it.");