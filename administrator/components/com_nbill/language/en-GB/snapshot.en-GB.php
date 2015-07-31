<?php
/**
* Language file for the Snapshot Report
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
* 
* @access private* 
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

define("NBILL_SNAPSHOT_TITLE", "Snapshot Report");
define("NBILL_SNAPSHOT_INTRO", "This report lists all of the invoices that were outstanding on a given date (useful for year-end accounting).");
define("NBILL_SNAPSHOT_DATE", "Snapshot Date");
define("NBILL_SNAPSHOT_INVOICE_DATE", "Invoice Date");
define("NBILL_SNAPSHOT_INVOICE_NO", "Invoice No.");
define("NBILL_SNAPSHOT_BILLING_NAME", "Billing Name");
define("NBILL_SNAPSHOT_NET_OS", "Net O/S");
define("NBILL_SNAPSHOT_NET_OS_HELP", "The net amount outstanding for this invoice as of the snapshot date.");
define("NBILL_SNAPSHOT_TAX_OS", "Tax O/S");
define("NBILL_SNAPSHOT_TAX_OS_HELP", "The amount of tax outstanding for this invoice as of the snapshot date.");
define("NBILL_SNAPSHOT_GROSS_OS", "Gross O/S");
define("NBILL_SNAPSHOT_GROSS_OS_HELP", "The total amount outstanding for this invoice as of the snapshot date (net plus tax).");
define("NBILL_SNAPSHOT_INVOICE_TOTAL", "Invoice Total");
define("NBILL_SNAPSHOT_INVOICE_TOTAL_HELP", "The total (gross) amount of the invoice (if the invoice was partially paid, this might be higher than the gross amount outstanding)");
define("NBILL_SNAPSHOT_PARTIAL", "Partial");
define("NBILL_SNAPSHOT_PARTIAL_HELP", "Whether or not the invoice was marked as partially paid as of the snapshot date (if a tick is shown for any item, you can click on the tick to view the associated income records)");
define("NBILL_SNAPSHOT_LATER_PARTIAL", "Later Partial?");
define("NBILL_SNAPSHOT_LATER_PARTIAL_HELP", "Whether or not the invoice is currently marked as partially paid (if a tick is shown for any item, you can click on the tick to view the associated income records)");
define("NBILL_SNAPSHOT_LATER_PAID", "Later Paid?");
define("NBILL_SNAPSHOT_LATER_PAID_HELP", "Whether or not the invoice was later marked as paid in full (if a tick is shown for any item, you can click on the tick to view the associated income records)");
define("NBILL_SNAPSHOT_LATER_WO", "Later Write-off?");
define("NBILL_SNAPSHOT_LATER_WO_HELP", "Whether or not the invoice has since been written off (items written off on or before the snapshot date will not show up on this report)");
define("NBILL_SNAPSHOT_MARKED_PARTIAL", "This invoice WAS partially paid on or before the snapshot date (Click on the tick to see the related income records)");
define("NBILL_SNAPSHOT_NOT_MARKED_PARTIAL", "This invoice was NOT partially paid on or before the snapshot date");
define("NBILL_SNAPSHOT_MARKED_LATER_PAID", "This invoice was paid in full after the snapshot date (Click on the tick to see the related income records)");
define("NBILL_SNAPSHOT_NOT_MARKED_LATER_PAID", "This invoice was NOT paid in full after the snapshot date");
define("NBILL_SNAPSHOT_MARKED_LATER_PARTIAL", "This invoice was partially paid after the snapshot date (Click on the tick to see the related income records)");
define("NBILL_SNAPSHOT_STILL_MARKED_LATER_PARTIAL", "This invoice was still only partially paid after the snapshot date (Click on the tick to see the related income records)");
define("NBILL_SNAPSHOT_NOT_MARKED_LATER_PARTIAL", "This invoice was NOT partially paid after the snapshot date");
define("NBILL_SNAPSHOT_MARKED_LATER_WO", "This invoice was written off after the snapshot date");
define("NBILL_SNAPSHOT_NOT_MARKED_LATER_WO", "This invoice was NOT written off after the snapshot date");
define("NBILL_SNAPSHOT_TOTALS", "TOTALS:");
define("NBILL_SNAPSHOT_EDIT_INVOICE", "Edit Invoice");
define("NBILL_SNAPSHOT_VIEW_INVOICE", "View Invoice");
define("NBILL_SNAPSHOT_EDIT_CLIENT", "Edit Client");
define("NBILL_SNAPSHOT_COUNT", "(%s Invoices)");