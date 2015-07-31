<?php
/**
* Language file for the Income feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Income
define("NBILL_INCOME_TITLE", "Income");
define("NBILL_INCOME_INTRO", "You can list any income here, whether you have invoices for it or not. If you list all of your income here, you can use this list to reconcile against your bank statement.");
define("NBILL_INCOME_RECEIPT_NO", "Receipt Number");
define("NBILL_INCOME_DATE", "Date");
define("NBILL_INCOME_AMOUNT", "Amount");
define("NBILL_INCOME_INVOICE_NO", "Invoice");
define("NBILL_EDIT_INCOME", "Edit Income");
define("NBILL_NEW_INCOME", "New Income");
define("NBILL_NO_INVOICE_NO", "No Invoice Number");
define("NBILL_INCOME_DETAILS", "Income Details");
define("NBILL_RELATED_INVOICE", "Related Invoice(s)");
define("NBILL_RECEIVED_FROM", "Received From");
define("NBILL_PAYMENT_METHOD", "Payment Method");
define("NBILL_AMOUNT_RECEIVED", "Amount Received");
define("NBILL_DATE_RECEIVED", "Date Received");
define("NBILL_RECEIPT_REFERENCE", "Reference");
define("NBILL_INSTR_RECEIPT_NO", "<strong>Note:</strong> Leave blank if adding a new item - the component will automatically assign the next available receipt number.");
define("NBILL_INSTR_RELATED_INVOICE", "If this receipt is in payment of one or more invoices, select the invoice(s) here (unpaid invoices are listed here).");
define("NBILL_INSTR_RECEIVED_FROM", "");
define("NBILL_INSTR_PAYMENT_METHOD", "");
define("NBILL_INSTR_AMOUNT_RECEIVED", "Enter the amount without a currency symbol.");
define("NBILL_INSTR_DATE_RECEIVED", "");
define("NBILL_INSTR_RECEIPT_REFERENCE", "You can use this field for whatever you like, but it is intended to allow integration with an online payment agency. For example, you could store the key value for a separate database table in which your automated online transactions are stored (to allow you to tie up automated online transactions with your income list). If you use it in this way, it is unlikely you will need to type in a value yourself - your integration script should populate it automatically for you.");
define("NBILL_RECEIVED_FOR", "Received For");
define("NBILL_INSTR_RECEIVED_FOR", "Indicate what the payment was for (if it does not relate to an invoice - eg. bank interest)");
define("NBILL_LEDGER_BREAKDOWN", "Nominal Ledger Breakdown");
define("NBILL_LEDGER_BREAKDOWN_MISMATCH", "WARNING! The nominal ledger breakdown totals do not match the amount or tax breakdown of the income. To go ahead and save anyway, click OK, otherwise click on Cancel and amend the nominal ledger breakdown, tax breakdown, or amount received.");
define("NBILL_TAX_RATE_AND_AMOUNT", "Tax Rates and Amounts");
define("NBILL_INSTR_TAX_RATE_AND_AMOUNT", "If the income includes an element of tax, you can specify the rate and amount here (up to 3 different rates per income item). Enter the amount of actual tax only (not the full amount of income). If tax is not applicable, leave it blank or enter zero. This information is used in preparing your tax summary report ONLY if you pay tax on amounts received rather than amounts invoiced. <strong>PLEASE NOTE:</strong> When you select an invoice from the list, if the invoice has several items on it, the tax amount(s) might appear to be fractionally too high for the given rate. However, this is due to the tax for individual items on the invoice being rounded up, and it is therefore NOT an error.");
define("NBILL_NET_RECEIPT", "Net Receipt");
define("NBILL_INSTR_NET_RECEIPT", "Amount received after tax has been deducted.");
define("NBILL_INCOME_TAX_RATE", "Rate");
define("NBILL_INCOME_TAX_AMOUNT", "Amount");
define("NBILL_SELECT_VENDOR_FOR_RECEIPT_NO_GEN", "Receipt No. Generation only available when vendor selected");
define("NBILL_GENERATE_RECEIPTS_UP_TO", "Enter the date (YYYY/MM/DD) up to which you want to generate numbers. Any items AFTER this date will not be given receipt numbers.");
define("NBILL_GENERATE_RECEIPT_NOS", "Generate Receipt Numbers");
define("NBILL_RECEIPT_NOS_GENERATED", "%s Receipt Numbers Generated%s");
define("NBILL_UNNUMBERED", "Awaiting Receipt No.");
define("NBILL_INCOME_NO_SUMMARY", "Omit From Tax Summary?");
define("NBILL_INSTR_INCOME_NO_SUMMARY", "If you want this income item to be ignored when producing the tax summary report, set this to 'yes'. For example, in the UK, some income may not get included on your tax return (such as money from insurance claims). Most income should be included on the tax summary though.");
define("NBILL_INCOME_FROM_REQUIRED", "Please specify from whom this income was received.");
@define("NBILL_GENERATE_MASTER", "WARNING! This will also generate receipt numbers for the MASTER database(s)!");

//Version 1.2.6
define("NBILL_INCOME_RECORD_LIMIT_WARNING", "WARNING! As there are %s or more unpaid invoices in your database, only the first %s records have been loaded into the above list. If the item you require is not here, you can either click on 'Show All' (below), or mark it as paid on the invoice list (it will then be selected here automatically).");

//Version 1.2.7
define("NBILL_INCOME_SHOW_ALL", "Show All");

//Version 2.0.0
define("NBILL_INCOME_LEDGER_NET_AMOUNT", "Net:");
define("NBILL_INCOME_LEDGER_TAX_RATE", "Tax Rate:");
define("NBILL_INCOME_LEDGER_TAX_AMOUNT", "Tax:");
define("NBILL_INCOME_LEDGER_GROSS_AMOUNT", "Gross:");
define("NBILL_INCOME_LEDGER_GUESSED", "PLEASE NOTE: The breakdown of net, tax, and gross amounts for the nominal ledger entries associated with this record have been guessed while migrating data from a previous version of " . NBILL_BRANDING_NAME . ". Please check and correct the figures in the red box below, and save this record.");
define("NBILL_INCOME_LEDGER_PLEASE_CHECK", "Please check these figures and amend if necessary.");
define("NBILL_RECEIVED_COUNTRY", "Country");
define("NBILL_INSTR_RECEIVED_COUNTRY", "Country from which the income was received");
define("NBILL_INCOME_RECEIPT_TITLE", "RECEIPT");
define("NBILL_INCOME_RECEIPT_INTRO", "This receipt confirms that you have made a payment as follows:");
define("NBILL_RECEIPT_NOT_YET_ASSIGNED", "Not Yet Assigned");
define("NBILL_INCOME_RE_INVOICE", "Invoice %s, Dated %s");
define("NBILL_INCOME_RE_INVOICES", "This payment was in relation to the following invoice(s):");
define("NBILL_INCOME_THANKS", "Thank you for your payment.");
define("NBILL_INCOME_PRINTER_FRIENDLY", "Printer Friendly Receipt");

//Version 2.1.0
define("NBILL_RECEIVED_TAX_REF", "Client Tax Reference");
define("NBILL_INSTR_RECEIVED_TAX_REF", "The tax exemption code for the person who paid you, if applicable");

//Version 2.2.0
define("NBILL_CREATE_MULTIPLE_INCOMES", "Generate Multiple Income Records");
define("NBILL_CREATE_MULTIPLE_INCOMES_INTRO", "You have requested to mark selected invoices as paid in full. This feature will create a separate income record for each of the selected invoices, loading the relevant data (such as tax and ledger breakdowns) from the invoice record. Please also provide the following information which will be used for each income record generated, and click on the '" . NBILL_TB_GENERATE . "' toolbar button.");
define("NBILL_MULTI_INCOME_NO_INVOICES_FOUND", "No qualifying unpaid invoices were found. Process aborted.");
define("NBILL_CREATE_MULTIPLE_INCOMES_COMPLETE", "%s income records created.");
define("NBILL_CREATE_MULTIPLE_INCOMES_ERROR", "WARNING! One or more errors occurred whilst attempting to generate income records:");
define("NBILL_TB_MULTI_INCOME_GENERATE_WARNING", "WARNING! This will generate %s new income records.");

//Version 2.4.0
define("NBILL_INCOME_WARNING_DEFAULT_LEDGER", "WARNING! You are allocating this income to the default (-1 - Miscellaneous) nominal ledger. Are you sure?");

//Version 2.4.1
define("NBILL_TX_CALC_OFF", "Turn calculation off");
define("NBILL_TX_CALC_ON", "Turn calculation on");

//Version 3.0.0
define("NBILL_TX_ELECTRONIC_DELIVERY", "Electronic Delivery?");
define("NBILL_INSTR_TAX_RATE_AND_AMOUNT_ELEC", " If the income relates to digital goods within the EU, it should be marked as an electronic delivery - which indicates that the rate of tax is applied according to the country of the customer (these amounts are shown separately on the tax summary report).");