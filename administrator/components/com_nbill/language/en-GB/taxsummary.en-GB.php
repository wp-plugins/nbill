<?php
/**
* Language file for the Tax Summary Report
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Tax Summary Report
define("NBILL_TAX_SUMMARY_TITLE", "Tax Summary Report");
define("NBILL_TAX_SUMMARY_INTRO", "This report shows the total amount of tax that has been charged for the given date range. Some tax schemes allow you to account for tax only after it has been paid to you by the consumer. Others require you to account for tax on all invoices raised, regardless of whether they have been paid or not. Use the radio button below to indicate which of these options to use, then specify a date range and click on 'Go' to get the totals. Note: if you include unpaid invoices, these will only be included if the date they were raised falls within the selected date range - it is therefore possible for the totals to be lower than if you select not to include unpaid invoices (as that will include all income received during the selected date range, even if the invoices to which they relate were raised before the selected date range).");
define("NBILL_INCLUDE_UNPAID", "Include Unpaid Invoices?");
define("NBILL_TAX_BREAKDOWN_INC", "Tax Breakdown - Income");
define("NBILL_TAX_BREAKDOWN_EXP", "Tax Breakdown - Expenditure");
define("NBILL_TOTAL_TAXABLE", "Total Taxable Income");
define("NBILL_TOTAL_TAXABLE_DUE", "Total Taxable Income Due");
define("NBILL_INSTR_TOTAL_TAXABLE", "This is the total amount received on which tax is due.");
define("NBILL_INSTR_TOTAL_TAXABLE_DUE", "This is the total amount invoiced for on which tax is due.");
define("NBILL_TOTAL_NON_TAXABLE", "Total Non-Taxable Income");
define("NBILL_TOTAL_NON_TAXABLE_DUE", "Total Non-Taxable Income Due");
define("NBILL_INSTR_TOTAL_NON_TAXABLE", "This is the total amount received on which no tax is due (this may include income that is not related to any invoices raised, eg. bank interest).");
define("NBILL_INSTR_TOTAL_NON_TAXABLE_DUE", "This is the total amount invoiced for on which no tax is due (this may include income that is not related to any invoices raised, eg. bank interest).");
define("NBILL_GROSS_TOTAL", "Gross Total");
define("NBILL_INSTR_GROSS_TOTAL", "Total income including tax");
define("NBILL_INSTR_GROSS_TOTAL_DUE", "Total income due including tax");
define("NBILL_VAT_RPT_TOTAL_TAX", "Total");
define("NBILL_VAT_RPT_TAX_NAME", "Tax");
define("NBILL_INSTR_VAT_RPT_TOTAL_TAX", "Total tax collected");
define("NBILL_INSTR_VAT_RPT_TOTAL_TAX_DUE", "Total tax due");
define("NBILL_VAT_RPT_TOTAL_NET", "Total Net");
define("NBILL_INSTR_VAT_RPT_TOTAL_NET", "Net income after tax");
define("NBILL_INSTR_VAT_RPT_TOTAL_NET_DUE", "Net income due after tax");
define("NBILL_VAT_RPT_DISCREPANCIES", "WARNING! Discrepancies Found");
define("NBILL_INSTR_VAT_RPT_DISCREPANCIES", "The invoice numbers shown here appear to have been manually altered such that the net amount and tax do not add up to the gross total for at least one item on the invoice, or the sum of the individual invoice items does not match the total for the invoice. This means the above figures may not be correct. Please check the value of each amount (net, tax, shipping, shipping tax, and gross) on these invoices and correct them if necessary. If you cannot see anything wrong with the invoices, just try saving them again (this will cause the figures to be recalculated). If you keep getting this message after having checked and re-saved the invoice(s), the invoice(s) may be corrupt in which case you will need to delete them and re-create them manually (by going to the invoice list, and clicking on the 'new' toolbar button).");
define("NBILL_EXCLUDED_INCOME_TITLE", "Income");
define("NBILL_EXCLUDED_EXPENDITURE_TITLE", "Expenditure");
define("NBILL_TAX_SUMMARY_LIST_EXCLUDED", "Click here for a list of items that have been excluded from this report");
define("NBILL_TAX_SUMMARY_EXCLUDED_TITLE", "Tax Summary Excluded Items");
define("NBILL_TAX_SUMMARY_EXCLUDED_INTRO", "The following income/expenditure items were excluded from the Tax Summary report because the relevant income/expenditure records indicate that they should be excluded. You can click on any of the listed items to edit them (NOTE: the main window will be redirected to edit the income or expenditure item - this window will stay where it is).");
define("NBILL_TAX_SUMMARY_EXCLUDED_NO_INCOME", "No income items were excluded.");
define("NBILL_TAX_SUMMARY_EXCLUDED_NO_EXP", "No expenditure items were excluded.");
define("NBILL_TAX_EXCLUDED_RCT_NO", "Receipt No.");
define("NBILL_TAX_EXCLUDED_DATE", "Date");
define("NBILL_TAX_EXCLUDED_RCD_FROM", "Received From");
define("NBILL_TAX_EXCLUDED_AMOUNT", "Amount");
define("NBILL_TAX_EXCLUDED_NBILL_NO", "Invoice No.");
define("NBILL_TAX_EXCLUDED_RCT_UNNUMBERED", "Awaiting Receipt No.");
define("NBILL_TAX_EXCLUDED_EDIT_INC", "Edit Income");
define("NBILL_TAX_EXCLUDED_NO_INV", "No Invoice Number");
define("NBILL_TAX_EXCLUDED_PYT_NO", "Payment No.");
define("NBILL_TAX_EXCLUDED_PAID_TO", "Paid To");
define("NBILL_TAX_EXCLUDED_PYT_FOR", "For");
define("NBILL_TAX_EXCLUDED_PYT_UNNUMBERED", "Awaiting Payment No.");
define("NBILL_TAX_EXCLUDED_EDIT_EXP", "Edit Expenditure");
define("NBILL_TAX_EXCLUDED_WO_NAME", "Billing Name");
define("NBILL_TAX_EXCLUDED_WO_TOTAL", "Gross Total");
define("NBILL_TAX_EXCLUDED_WO_PREVIEW", "Print Preview");
define("NBILL_WRITE_OFFS_TITLE", "Written-off Invoices");
define("NBILL_TAX_SUMMARY_EXCLUDED_NO_WO", "No written-off invoices were excluded.");
define("NBILL_TOTAL_TAXABLE_PAID", "Total Taxable Expenditure");
define("NBILL_INSTR_TOTAL_TAXABLE_PAID", "This is the total net amount paid out, on which tax was due.");
define("NBILL_TOTAL_NON_TAXABLE_PAID", "Total Non-Taxable Expenditure");
define("NBILL_INSTR_TOTAL_NON_TAXABLE_PAID", "This is the total amount paid out on which no tax was due.");
define("NBILL_INSTR_GROSS_TOTAL_PAID", "Total expenditure including tax.");
define("NBILL_INSTR_VAT_RPT_TOTAL_TAX_PAID", "Total tax paid");
define("NBILL_INSTR_VAT_RPT_TOTAL_NET_PAID", "Net expenditure");
define("NBILL_TAX_BREAKDOWN_WO", "Tax Breakdown - Written Off Invoices");
define("NBILL_TOTAL_TAXABLE_DUE_WO", "Total Taxable Income Written Off");
define("NBILL_INSTR_TOTAL_TAXABLE_DUE_WO", "This is the total amount invoiced for, but written off, on which tax was due.");
define("NBILL_TOTAL_NON_TAXABLE_DUE_WO", "Total Non-Taxable Income Written Off");
define("NBILL_INSTR_TOTAL_NON_TAXABLE_DUE_WO", "This is the total amount invoiced for, but written off, on which no tax was due.");
define("NBILL_GROSS_TOTAL_WO", "Gross Total");
define("NBILL_INSTR_GROSS_TOTAL_DUE_WO", "Total income written off, including tax.");
define("NBILL_VAT_RPT_TOTAL_TAX_WO", "Total");
define("NBILL_VAT_RPT_TAX_NAME_WO", "Tax");
define("NBILL_INSTR_VAT_RPT_TOTAL_TAX_DUE_WO", "Total tax written off");
define("NBILL_VAT_RPT_TOTAL_NET_WO", "Total Net");
define("NBILL_INSTR_VAT_RPT_TOTAL_NET_DUE_WO", "Net amount written off after tax");
define("NBILL_VAT_RPT_DISCREPANCIES_WO", "WARNING! Discrepancies Found");
define("NBILL_INSTR_VAT_RPT_DISCREPANCIES_WO", "The invoice numbers shown here appear to have been manually altered such that the net amount and tax do not add up to the gross total for at least one item on the invoice, or the sum of the individual invoice items does not match the total for the invoice. This means the above figures may not be correct. Please check the value of each amount (net, tax, shipping, shipping tax, and gross) on these invoices and correct them if necessary. If you cannot see anything wrong with the invoices, just try saving them again (this will cause the figures to be recalculated). If you keep getting this message after having checked and re-saved the invoice(s), the invoice(s) may be corrupt in which case you will need to delete them and re-create them manually (by going to the invoice list, and clicking on the 'new' toolbar button).");
define("NBILL_TAX_SUMMARY", "SUMMARY (For tax purposes only)");
define("GROSS_PROFIT_LOSS", "Gross Profit (or Loss)");
define("TAX_PAYABLE_REBATE_DUE", "Total Tax Payable (or Rebate Due)");
define("NET_PROFIT_LOSS", "Net Profit (or Loss)");

//Version 1.2.1
define("NBILL_TAX_SUMMARY_DETAILED", "Show Detailed Breakdown? (Can be slow!)");

//Version 2.0.0
define("NBILL_TAX_SUMMARY_UNNUMBERED_RCT", "Awaiting Receipt No.");
define("NBILL_TAX_SUMMARY_UNNUMBERED_PYT", "Awaiting Payment No.");
define("NBILL_BREAKDOWN_TYPE_1", "Income");
define("NBILL_BREAKDOWN_TYPE_2", "Invoice");
define("NBILL_BREAKDOWN_TYPE_3", "Expenditure");
define("NBILL_BREAKDOWN_TYPE_4", "Credit Note");
define("NBILL_BREAKDOWN_TYPE", "Type");
define("NBILL_BREAKDOWN_DATE", "Date");
define("NBILL_BREAKDOWN_REF", "Ref");
define("NBILL_BREAKDOWN_DESC", "Description");
define("NBILL_BREAKDOWN_NET", "Net Amount");
define("NBILL_BREAKDOWN_TAX", "Tax Amount");
define("NBILL_BREAKDOWN_TAX_REF", "Tax Reference");
define("NBILL_BREAKDOWN_COUNTRY", "Country");
define("NBILL_BREAKDOWN_UNKNOWN", "Unknown");
define("NBILL_BREAKDOWN_EXP_COLL", "Expand/Collapse");
define("NBILL_BREAKDOWN_TOTAL_COUNT", "%s Item[s]");
define("NBILL_TAX_SUMMARY_PF_EXPANDED", "Printer Friendly (Expanded)");
define("NBILL_TAX_SUMMARY_PF_COLLAPSED", "Printer Friendly (Collapsed)");
define("NBILL_INSTR_VAT_RPT_DISCREPANCIES_CR", "The credit note numbers shown here appear to have been manually altered such that the net amount and tax do not add up to the gross total for at least one item on the credit note, or the sum of the individual credit note items does not match the total for the credit note. This means the above figures may not be correct. Please check the value of each amount (net, tax, shipping, shipping tax, and gross) on these credit notes and correct them if necessary. If you cannot see anything wrong with the credit notes, just try saving them again (this will cause the figures to be recalculated). If you keep getting this message after having checked and re-saved the credit note(s), the credit note(s) may be corrupt in which case you will need to delete them and re-create them manually (by going to the credit note list, and clicking on the 'new' toolbar button).");
define("NBILL_TAX_EXCLUDED_INCOME_TOTAL", "Total Excluded Income (%s items)");
define("NBILL_TAX_EXCLUDED_EXPENDITURE_TOTAL", "Total Excluded Expenditure (%s items)");

//Version 3.0.0
define("NBILL_VAT_RPT_ELECTRONIC_DELIVERY", "Electronic Delivery");

//Version 3.1.0
define("NBILL_VAT_RPT_SUBTOTAL", "Sub-Total (%s)");