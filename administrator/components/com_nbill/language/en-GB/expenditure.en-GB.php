<?php
/**
* Language file for the Expenditure feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Expenditure
define("NBILL_EXPENDITURE_TITLE", "Expenditure");
define("NBILL_EXPENDITURE_INTRO", "You can record all of your expenses here, and include any tax breakdowns and nominal ledger breakdowns which can then be used in producing reports.");
define("NBILL_PAYMENT_NO", "Payment Number");
define("NBILL_PAYMENT_DATE", "Payment Date");
define("NBILL_PAID_TO", "Paid To");
define("NBILL_PAYMENT_AMOUNT", "Amount");
define("NBILL_PAYMENT_FOR", "For");
define("NBILL_EXP_LEDGER_BREAKDOWN_MISMATCH", "WARNING! The nominal ledger breakdown total does not match the amount or tax breakdown of the expenditure. To go ahead and save anyway, click OK, otherwise click on Cancel and amend the nominal ledger breakdown, tax breakdown, or amount paid.");
define("NBILL_EDIT_EXPENDITURE", "Edit Expenditure");
define("NBILL_NEW_EXPENDITURE", "New Expenditure");
define("NBILL_EXPENDITURE_DETAILS", "Expenditure Details");
define("NBILL_INCOME_PAYMENT_NO", "Payment Number");
define("NBILL_INSTR_PAYMENT_NO", "<strong>Note:</strong> Leave blank if adding a new expenditure item - the component will automatically assign the next available payment number.");
define("NBILL_INSTR_SUPPLIER", "If you have already defined the supplier in the supplier list, you can select them here. If this is a one-off supply, you can just type in the supplier details below.");
define("NBILL_INSTR_SUPPLIER_NAME", "The company or person to whom payment was made.");
define("NBILL_EXPENDITURE_FOR", "For");
define("NBILL_INSTR_EXPENDITURE_FOR", "Describe what the payment was for");
define("NBILL_DATE_PAID", "Date Paid");
define("NBILL_INSTR_DATE_PAID", "");
define("NBILL_AMOUNT_PAID", "Total Amount Paid (including tax)");
define("NBILL_INSTR_AMOUNT_PAID", "");
define("NBILL_PAYMENT_REFERENCE", "Payment Reference");
define("NBILL_INSTR_PAYMENT_REFERENCE", "Your reference with the supplier for this payment");
define("NBILL_EXP_TAX_RATE_AND_AMOUNT", "Tax Rates and Amounts");
define("NBILL_INSTR_EXP_TAX_RATE_AND_AMOUNT", "If the expenditure includes an element of tax, you can specify the rate and amount here (up to 3 different rates per expenditure item). Enter the amount of actual tax only (not the full amount of the payment). If tax is not applicable, leave it blank or enter zero. This information is used in preparing your tax summary report.");
define("NBILL_EXP_TAX_RATE", "Rate");
define("NBILL_EXP_TAX_AMOUNT", "Amount");
define("NBILL_EXP_TAX_REFERENCE", "Tax Reference");
define("NBILL_INSTR_EXP_TAX_REFERENCE", "VAT Number or Sales Tax reference number of this supplier");
define("NBILL_EXP_NO_SUMMARY", "Omit From Tax Summary?");
define("NBILL_INSTR_EXP_NO_SUMMARY", "If you want this expenditure item to be ignored when producing the tax summary report, set this to 'yes'. For example, in the UK, some expenditure may not get included on your tax return (such as salaries). Most expenditure should be included on the tax summary though.");
define("NBILL_EXP_SUPPLIER_NAME_REQUIRED", "Please specify to whom the payment was made (supplier name).");
define("NBILL_EXP_WHAT_FOR", "WARNING! You have not entered what the payment was for. To go ahead and save anyway, click OK, or to go back and enter a value, click Cancel.");
define("NBILL_EXP_NO_AMOUNT", "WARNING! You have not entered an amount. To go ahead and save anyway, click OK, or to go back and enter a value, click Cancel.");
define("NBILL_EXP_PAYEE", "Payee");
define("NBILL_SELECT_VENDOR_FOR_PAYMENT_NO_GEN", "Payment No. Generation only available when vendor selected");
define("NBILL_GENERATE_PAYMENTS_UP_TO", "Enter the date (YYYY/MM/DD) up to which you want to generate numbers. Any items AFTER this date will not be given payment numbers.");
define("NBILL_GENERATE_PAYMENT_NOS", "Generate Payment Numbers");
define("NBILL_PAYMENT_NOS_GENERATED", "%s Payment Numbers Generated%s");
define("NBILL_EXP_UNNUMBERED", "Awaiting Payment No.");
define("NBILL_RELATED_CREDITS", "Related Credit Note(s)");
define("NBILL_INSTR_RELATED_CREDITS", "If this expenditure is in payment of one or more credit notes, select the credit note(s) here (unpaid credit notes are listed here).");
define("NBILL_EXP_PAYEE_ADDRESS", "Payee Address");
define("NBILL_INSTR_PAYEE_ADDRESS", "Address of person or company to whom payment was made (EXCEPT the country, which is stored separately, below)");
@define("NBILL_GENERATE_MASTER", "WARNING! This will also generate payment numbers for the MASTER database(s)!");

//Version 2.0.0
define("NBILL_EXPENDITURE_LEDGER_NET_AMOUNT", "Net:");
define("NBILL_EXPENDITURE_LEDGER_TAX_RATE", "Tax Rate:");
define("NBILL_EXPENDITURE_LEDGER_TAX_AMOUNT", "Tax:");
define("NBILL_EXPENDITURE_LEDGER_GROSS_AMOUNT", "Gross:");
define("NBILL_EXPENDITURE_LEDGER_GUESSED", "PLEASE NOTE: The breakdown of net, tax, and gross amounts for the nominal ledger entries associated with this record have been guessed while migrating data from a previous version of " . NBILL_BRANDING_NAME . ". Please check and correct the figures in the red box below, and save this record.");
define("NBILL_EXPENDITURE_LEDGER_PLEASE_CHECK", "Please check these figures and amend if necessary.");
define("NBILL_PAYEE_COUNTRY", "Payee Country");
define("NBILL_INSTR_PAYEE_COUNTRY", "");
define("NBILL_EXPENDITURE_RECORD_LIMIT_WARNING", "WARNING! As there are %s or more unpaid credit notes in your database, only the first %s records have been loaded into the above list. If the item you require is not here, you can either click on 'Show All' (below), or mark it as paid on the credit note list (it will then be selected here automatically).");
define("NBILL_EXPENDITURE_SHOW_ALL", "Show All");

//Version 2.4.0
define("NBILL_EXPENDITURE_WARNING_DEFAULT_LEDGER", "WARNING! You are allocating this expenditure to the default (-1 - Miscellaneous) nominal ledger. Are you sure?");
define("NBILL_EXPENDITURE_PAYMENT_TITLE", "PAYMENT");
define("NBILL_EXPENDITURE_PAYMENT_INTRO", "This payment slip confirms that a payment was made as follows:");
define("NBILL_EXPENDITURE_NOT_YET_ASSIGNED", "Not Yet Assigned");
define("NBILL_EXPENDITURE_RE_CREDIT", "Credit Note %s, Dated %s");
define("NBILL_EXPENDITURE_RE_CREDITS", "This payment was in relation to the following credit note(s):");
define("NBILL_EXPENDITURE_PAID_FOR", "This payment was in relation to:");
define("NBILL_EXPENDITURE_PAID", "Paid.");
define("NBILL_EXPENDITURE_PRINTER_FRIENDLY", "Printer Friendly Payment Slip");

//Version 3.0.0
define("NBILL_INSTR_EXP_TAX_RATE_AND_AMOUNT_ELEC", " If the payment relates to digital goods within the EU, it should be marked as an electronic delivery - which indicates that the rate of tax is applied according to the country of the customer (these amounts are shown separately on the tax summary report).");