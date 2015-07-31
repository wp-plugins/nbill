<?php
/**
* Language file for the Transaction Report
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

define("NBILL_TRANSACTIONS_TITLE", "Transaction Statement");
define("NBILL_TRANSACTIONS_INTRO", "This is a list of all of the income and expenditure items that have been recorded for the given date range. No income or expenditure items are excluded, but this report does not include any unpaid invoices. The 'Balance' column just refers to the net profit/loss for the given date range, and bears no relation to the balance in your bank account!");
define("NBILL_TR_INCOME", "Gross In");
define("NBILL_TR_EXPENDITURE", "Gross Out");
define("NBILL_TR_ITEM_NO", "Number");
define("NBILL_TR_RECEIPT", "Rct");
define("NBILL_TR_PAYMENT", "Pyt");
define("NBILL_TR_DATE", "Date");
define("NBILL_TR_FROM_TO", "From/To");
define("NBILL_TR_FOR", "For");
define("NBILL_TR_INVOICE", "Invoice");
define("NBILL_TR_CREDIT_NOTE", "Credit Note");
define("NBILL_TR_LEDGER", "Ledger");
define("NBILL_TR_NET_AMOUNT", "Net Amount");
define("NBILL_TR_TAX", "Tax");
define("NBILL_TR_TOTAL", "Total (%s transactions)");
define("NBILL_TR_BALANCE", "Balance");
define("NBILL_TR_AWAITING_RCT_NO", "Awaiting Receipt No.");
define("NBILL_TR_AWAITING_PYT_NO", "Awaiting Payment No.");
define("NBILL_TR_CLIENT_REFUND", "Client Refund");

//Version 2.0.0
define("NBILL_TR_NET_INCOME", "Net In");
define("NBILL_TR_TAX_INCOME", "Tax In");
define("NBILL_TR_NET_EXPENDITURE", "Net Out");
define("NBILL_TR_TAX_EXPENDITURE", "Tax Out");