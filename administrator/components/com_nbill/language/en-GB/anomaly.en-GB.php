<?php
/**
* Language file for the Anomaly report
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
* 
* @access private* 
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

define("NBILL_ANOMALY_TITLE", "Anomaly Report");
define("NBILL_ANOMALY_PF", "Printer Friendly Version");
define("NBILL_ANOMALY_INTRO", "This report attempts to identify any records that appear to be out of the ordinary so that you can investigate further in case a mistake has been made. Just because a record appears on this report does not necessarily mean there is anything wrong with it - just that there could potentially be a problem. If you have a lot of data, it can take some time for " . NBILL_BRANDING_NAME . " to check through it all, so you might need to narrow down the date range or the items that are checked. Some items might be listed under more than one heading, as a single wrong value could cause a number of different problems.");
define("NBILL_ANOMALY_CRITERIA", "Anomaly Search Criteria");
define("NBILL_ANOMALY_CRITERIA_HELP", "Please specify a date range, and select the checks you want to make:");
define("NBILL_ANOMALY_DATE_RANGE", "Date Range:");
define("NBILL_ANOMALY_ALL", "All");
define("NBILL_ANOMALY_RANGE", "Specified date range");
define("NBILL_ANOMALY_CHECKS", "Check for...");
define("NBILL_ANOMALY_CHECK_1", "Missing Income/Expenditure Records");
define("NBILL_ANOMALY_CHECK_1_HELP", "Looks for any invoices or credit notes that are marked as paid in full or partially paid, but for which no associated income or expenditure record could be found.");
define("NBILL_ANOMALY_CHECK_1_WHAT", "These invoices or credit notes are marked as paid in full or partially paid, but there are no income or expenditure records associated with them. This might be ok if you do not use the income or expenditure features of nBill. In most cases though, if a payment has been received or sent, an income or expenditure record should be present for it. If you need to add a new income or expenditure record, edit the invoice or credit note, set 'paid in full' and 'partially paid' to 'no', then create a new income or expenditure record (the invoice or credit note should then appear in the list, so you can select it, and it will be marked as paid again when you save the income or expenditure record).");
define("NBILL_ANOMALY_CHECK_2", "Insufficient Income/Expenditure Records");
define("NBILL_ANOMALY_CHECK_2_HELP", "Looks for any invoices or credit notes that are marked as paid in full, but for which the associated income or expenditure record does not match the total of the invoice or credit note.");
define("NBILL_ANOMALY_CHECK_2_WHAT", "These invoices or credit notes are marked as paid in full, but the sum total of all the income or expenditure records associated with them is less than the total of the invoice or credit note, indicating that full payment does not seem to have been made. To resolve this conflict, check whether an income or expenditure record is present but not associated with the invoice or credit note, or whether a new income or expenditure record needs to be added, or whether the invoice or credit note should actually be marked as unpaid or partially paid instead of paid in full.");
define("NBILL_ANOMALY_CHECK_3", "Overpayment");
define("NBILL_ANOMALY_CHECK_3_HELP", "Looks for any income or expenditure records that show the amount received or paid to be greater than the sum total of any associated invoices or credit notes");
define("NBILL_ANOMALY_CHECK_3_WHAT", "These invoices or credit notes have income or expenditure records associated with them which, when added together, come to more than the total of the invoice or credit note. This suggests the item may have been overpaid, in which case a refund may be due. Alternatively, it could be that you have duplicated income or expenditure records, in which case you might need to delete them or mark them as void (ie set the amounts to zero and enter the billing name as VOID) so as to avoid errors in the report totals.");
define("NBILL_ANOMALY_CHECK_4", "Tax Rate Missing");
define("NBILL_ANOMALY_CHECK_4_HELP", "Looks for any income or expenditure records that show a tax rate of 0% (zero), but also contain a tax amount greater than zero.");
define("NBILL_ANOMALY_CHECK_4_WHAT", "The income or expenditure records listed here show an amount of tax but the corresponding tax rate is set to zero. You need to edit the record and enter the correct tax rate as a percentage. Please note that income and expenditure records can have up to 3 different tax rates and corresponding amounts, so if more than one tax amount is shown, each must have a corresponding percentage recorded as the tax rate for that tax amount.");
define("NBILL_ANOMALY_CHECK_5", "Tax Rate Mis-match");
define("NBILL_ANOMALY_CHECK_5_HELP", "Looks for any income or expenditure records whose tax rate does not match the tax rate of any associated invoices or credit notes.");
define("NBILL_ANOMALY_CHECK_5_WHAT", "The income or expenditure records listed here hold a tax rate which differs from the tax rate held on the related invoice or credit note. Please check which rate is correct, and amend either the invoice/credit note or the income/expenditure so that they agree.");
define("NBILL_ANOMALY_CHECK_6", "Tax Amount Missing");
define("NBILL_ANOMALY_CHECK_6_HELP", "Looks for any income or expenditure records that show no tax, but for which any associated invoices or credit notes indicate there should be tax.");
define("NBILL_ANOMALY_CHECK_6_WHAT", "The income or expenditure records listed here hold a tax amount of zero, but the associated invoice(s) or credit note(s) indicate that some tax should be included. Please check whether any tax should be added to the income or expenditure record, or whether the tax amount should be removed from the invoice(s) or credit note(s).");
define("NBILL_ANOMALY_CHECK_7", "Tax Amount Mis-match");
define("NBILL_ANOMALY_CHECK_7_HELP", "Looks for any income or expenditure records where the total amount of tax does not equate to the given percentage of the total, or does not match the amount of tax shown on any associated invoices or credit notes.");
define("NBILL_ANOMALY_CHECK_7_WHAT", "The records listed here show an amount of tax that does not seem to equate to the tax rate for the item. This might be ok, for example if the item is made up of some taxable and some non-taxable elements, or if the total is made up of a large number of items (which could result in the tax being rounded up for each item), or if you received a refund of VAT and the entire amount refunded consisted of VAT at a certain rate (in that case, the tax amount will be 100% of the income amount, even though the tax rate is less than 100%). Also, some people might use different methods for calculating tax, eg. by deducting a discount from the gross amount instead of from the net, which could also cause the tax amount to appear to be incorrect. In most cases though, the amount of tax held against each record should equate to the percentage rate used - if not, you might need to amend either the tax rate or the tax amount so that they match.");
define("NBILL_ANOMALY_CHECK_8", "Ledger Amount Mis-match");
define("NBILL_ANOMALY_CHECK_8_HELP", "Looks for any income or expenditure records where the sum of the ledger breakdown does not match the total for the record.");
define("NBILL_ANOMALY_CHECK_8_WHAT", "The total net, tax, or gross amounts assigned to nominal ledger codes for the listed transactions does not match the net, tax, or gross amount of the transaction. This suggests that the nominal ledger breakdown is incorrect and will lead to inaccurate reports. Please edit these records to ensure that the total net, tax, and gross amounts assigned to nominal ledger codes matches the net, tax, and gross amounts of the income or expenditure.");
define("NBILL_ANOMALY_CHECK_9", "Date Anomalies");
define("NBILL_ANOMALY_CHECK_9_HELP", "Looks for any income or expenditure records that pre-date the invoice or credit note they relate to by more than 31 days, or are dated a year or more after the invoice or credit note date (in case you entered the wrong year by mistake - easy to do in January!).");
define("NBILL_ANOMALY_CHECK_9_WHAT", "The income or expenditure records associated with the invoices or credit notes listed here are either dated a month or more earlier than the invoice or credit note to which they relate, or a year or more after. Unless these items were really paid a month or more in advance, or a year or more late, you probably entered the wrong date on the income or expenditure record.");
define("NBILL_ANOMALY_SEARCH", "Search for Anomalies");
define("NBILL_ANOMALY_RESULTS", "Anomaly Search Results");
define("NBILL_ANOMALY_NONE_FOUND", "No anomalies were found for this check");
define("NBILL_ANOMALY_INVOICE_NO", "Invoice No.");
define("NBILL_ANOMALY_DESC", "Name/Description");
define("NBILL_ANOMALY_DATE", "Date");
define("NBILL_ANOMALY_INVOICE_NET", "Invoice Net");
define("NBILL_ANOMALY_INVOICE_TAX", "Invoice Tax");
define("NBILL_ANOMALY_INVOICE_GROSS", "Invoice Gross");
define("NBILL_ANOMALY_CR_NO", "Credit Note No.");
define("NBILL_ANOMALY_CR_NET", "Credit Note Net");
define("NBILL_ANOMALY_CR_TAX", "Credit Note Tax");
define("NBILL_ANOMALY_CR_GROSS", "Credit Note Gross");
define("NBILL_ANOMALY_MARKED_PAID", "Marked Paid?");
define("NBILL_ANOMALY_MARKED_PARTIAL", "Marked Partially Paid?");
define("NBILL_ANOMALY_PAID_YES", "Marked as paid in full");
define("NBILL_ANOMALY_PAID_NO", "Not marked as paid in full");
define("NBILL_ANOMALY_PARTIAL_YES", "Marked as partially paid");
define("NBILL_ANOMALY_PARTIAL_NO", "Not marked as partially paid");
define("NBILL_ANOMALY_AMOUNT_EXPECTED", "Amount Expected");
define("NBILL_ANOMALY_AMOUNT_RECEIVED", "Amount Received");
define("NBILL_ANOMALY_AMOUNT_PAID", "Amount Paid");
define("NBILL_ANOMALY_ACTION", "Action");
define("NBILL_ANOMALY_VIEW_INCOME", "View Income");
define("NBILL_ANOMALY_VIEW_EXPENDITURE", "View Expenditure");
define("NBILL_ANOMALY_RECEIPT_NO", "Receipt No");
define("NBILL_ANOMALY_PAYMENT_NO", "Payment No");
define("NBILL_ANOMALY_INCOME_NET", "Income Net");
define("NBILL_ANOMALY_INCOME_TAX", "Income Tax");
define("NBILL_ANOMALY_INCOME_GROSS", "Income Gross");
define("NBILL_ANOMALY_EXP_NET", "Expenditure Net");
define("NBILL_ANOMALY_EXP_TAX", "Expenditure Tax");
define("NBILL_ANOMALY_EXP_GROSS", "Expenditure Gross");
define("NBILL_AWAITING_IN", "Awaiting Receipt No.");
define("NBILL_AWAITING_EX", "Awaiting Payment No.");
define("NBILL_ANOMALY_LEDGER_NET", "Ledger Total Net");
define("NBILL_ANOMALY_LEDGER_TAX", "Ledger Total Tax");
define("NBILL_ANOMALY_LEDGER_GROSS", "Ledger Total Gross");
define("NBILL_ANOMALY_INVOICE_DATE", "Invoice Date");
define("NBILL_ANOMALY_CR_DATE", "Credit Note Date");
define("NBILL_ANOMALY_PAYMENT_DATE", "Date Paid");