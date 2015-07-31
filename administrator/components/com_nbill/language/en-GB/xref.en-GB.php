<?php
/**
* Language file for Cross Reference output in option lists on order forms
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Reminder Types
define("NBILL_REMINDER_PAYMENT_DUE", "Payment Due");
define("NBILL_REMINDER_ORDER_EXPIRY", "Order Expiry");
define("NBILL_REMINDER_RENEWAL_DUE", "Renewal Due");
define("NBILL_REMINDER_INVOICE_OVERDUE", "Invoice Overdue");
define("NBILL_REMINDER_USER_DEFINED", "User-defined");

//Default Start Date
define("NBILL_CFG_LIST_START_DATE", "Default Start Date for Lists");
define("NBILL_CFG_INSTR_LIST_START_DATE", "Indicate how you want the system to select the start date for any lists that are governed by a date range (eg. orders, invoices) when no date has been specifically selected. WARNING! If you select to show a large number of records (eg. 5 years, or all), this could slow down the display of the lists.");
define("NBILL_CFG_START_DATE_CURRENT_ONLY", "Show current month only");
define("NBILL_CFG_START_DATE_CURRENT_LAST", "Show current and previous month");
define("NBILL_CFG_START_DATE_QUARTER", "Show up to 3 months");
define("NBILL_CFG_START_DATE_SEMI", "Show up to 6 months");
define("NBILL_CFG_START_DATE_YEAR", "Show up to a year");
define("NBILL_CFG_START_DATE_FIVE", "Show up to 5 years");
define("NBILL_CFG_START_DATE_ALL", "Show ALL items");

//Email invoice options
define("NBILL_NO_EMAIL", "Don't e-mail invoices");
define("NBILL_EMAIL_INVOICE", "Embed invoice in an HTML e-mail");
define("NBILL_EMAIL_NOTIFICATION", "Send notification (plain text e-mail advising to log into website)");
define("NBILL_EMAIL_INVOICE_ATTACH", "Send invoice as an attachment (plain text e-mail)");
define("NBILL_EMAIL_INVOICE_PDF", "Send invoice as a PDF attachment (plain text e-mail)");
define("NBILL_EMAIL_TEMPLATE", "Send HTML e-mail (no attachment)");
define("NBILL_EMAIL_TEMPLATE_ATTACH", "Send HTML e-mail with HTML attachment");
define("NBILL_EMAIL_TEMPLATE_PDF", "Send HTML e-mail with PDF attachment");

//Order status
define("NBILL_STATUS_PENDING", "Under Review");
define("NBILL_STATUS_PROCESSING", "Processing");
define("NBILL_STATUS_DISPATCHED", "Dispatched");
define("NBILL_STATUS_COMPLETED", "Completed");
define("NBILL_STATUS_CANCELLED", "Cancelled");

//Quote status
define("NBILL_STATUS_QUOTE_NEW", "New");
define("NBILL_STATUS_QUOTE_QUOTED", "Quoted");
define("NBILL_STATUS_QUOTE_ON_HOLD", "On Hold");
define("NBILL_STATUS_QUOTE_ACCEPTED", "Accepted");
define("NBILL_STATUS_QUOTE_REJECTED", "Rejected");
define("NBILL_STATUS_QUOTE_PART_ACCEPTED", "Part Accepted");

//Payment Frequency
define("NBILL_ONE_OFF", "One-off");
define("NBILL_WEEKLY", "Weekly");
define("NBILL_FOUR_WEEKLY", "Four-weekly");
define("NBILL_MONTHLY", "Monthly");
define("NBILL_QUARTERLY", "Quarterly");
define("NBILL_SEMI_ANNUALLY", "Semi-annually");
define("NBILL_ANNUALLY", "Annually");
define("NBILL_BIANNUALLY", "Biannually");
define("NBILL_FIVE_YEARLY", "Five Yearly");
define("NBILL_TEN_YEARLY", "Ten Yearly");

//Plan Type
define("NBILL_UP_FRONT", "Payment Up Front");
define("NBILL_INSTALLMENTS", "Installments");
define("NBILL_DEPOSIT_AND_FINAL", "Deposit plus final payment");
define("NBILL_DEPOSIT_AND_INSTALLMENTS", "Deposit plus installments");
define("NBILL_DEFERRED_AND_FINAL", "Deferred payment");
define("NBILL_DEFERRED_AND_INSTALLMENTS", "Deferred installments");
define("NBILL_USER_CONTROLLED", "User controlled");

//Payment method
define("NBILL_CASH", "Cash");
define("NBILL_CHEQUE", "Cheque");
define("NBILL_CREDIT_CARD", "Credit/Debit Card");
define("NBILL_DIRECT_DEBIT", "Direct Debit");
define("NBILL_BANK_TRANSFER", "Bank Transfer");
define("NBILL_STANDING_ORDER", "Standing Order");
define("NBILL_ONLINE_AGENCY", "Online Agency");
define("NBILL_OTHER", "Other");

//Field types
define("NBILL_FLD_TEXTBOX", "Textbox");
define("NBILL_FLD_DROPDOWN", "Dropdown List");
define("NBILL_FLD_EMAIL", "E-Mail Address");
define("NBILL_FLD_RADIOLIST", "Option List");
define("NBILL_FLD_CHECKBOX", "Checkbox");
define("NBILL_FLD_TEXTAREA", "Text Area");
define("NBILL_FLD_DATE", "Date");
define("NBILL_FLD_NUMERIC", "Numeric");
define("NBILL_FLD_HIDDEN", "Hidden");
define("NBILL_FLD_LABEL", "Label");
define("NBILL_FLD_JAVASCRIPT_BUTTON", "JavaScript Button");
define("NBILL_FLD_SUBMIT_BUTTON", "Process Button");
define("NBILL_FLD_DOMAIN_LOOKUP", "Domain Lookup");
define("NBILL_FLD_FILE_UPLOAD", "File Upload");
define("NBILL_FLD_PASSWORD", "Password");
define("NBILL_FLD_CAPTCHA", "Security Image (CAPTCHA)");
define("NBILL_FLD_LOGIN", "Login Box");
define("NBILL_FLD_SUMMARY", "Summary Table");

//2.1.0 Payment Gateway
define("NBILL_ARRANGE_OFFLINE", "Offline Payment");

//2.1.0 Payment Plans
define("NBILL_DEPOSIT_THEN_USER_CONTROLLED", "Deposit then User Controlled");

//2.2.0 Extra quote status
define("NBILL_STATUS_QUOTE_WITHDRAWN", "Withdrawn");