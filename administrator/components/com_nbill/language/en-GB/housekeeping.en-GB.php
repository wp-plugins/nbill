<?php
/**
* Language file for the Housekeeping page
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Housekeeping
define("NBILL_HOUSEKEEPING_TITLE", "Housekeeping (delete old records)");
define("NBILL_HOUSEKEEPING_INTRO", "This feature allows you to keep your database a reasonable size by deleting old records that are no longer required. In some countries, data protection legislation requires you to delete data that is no longer needed or is over a certain age. Other legislation might require you to keep data for a certain period of time for audit or other purposes. Please seek legal advice before deciding what data to keep and what to delete. To clear down old records, first take a backup of your database (in case anything goes wrong), then select the type of records that you want to delete by checking or unchecking the boxes below, then select the age threshold, and click on 'delete'. <strong>WARNING! This feature has the capacity to delete virtually ALL of your data, so please be careful and use at your own risk!</strong>");
define("NBILL_HOUSEKEEPING_SELECT_RECORDS", "Clear down all...");
define("NBILL_HOUSEKEEPING_TYPE_1", "CMS Users");
define("NBILL_HOUSEKEEPING_TYPE_1_HELP", "Selecting this option will delete the user records in your CMS (eg. Wordpress or Joomla!), if the user has not logged in for the specified length of time.");
define("NBILL_HOUSEKEEPING_TYPE_2", "Potential Clients and Contacts");
define("NBILL_HOUSEKEEPING_TYPE_2_HELP", "Deletes all potential clients and their associated contact records if there has been no activity (including modification of the client data) for more than the specified amount of time.");
define("NBILL_HOUSEKEEPING_TYPE_3", "Clients and Contacts");
define("NBILL_HOUSEKEEPING_TYPE_3_HELP", "Deletes all client and associated contact records (not including potential clients) if there has been no activity for more than the specified amount of time.");
define("NBILL_HOUSEKEEPING_TYPE_4", "Suppliers");
define("NBILL_HOUSEKEEPING_TYPE_4_HELP", "Deletes all supplier and associated contact records if there has been no activity for more than the specified amount of time.");
define("NBILL_HOUSEKEEPING_TYPE_5", "Orphan Contacts");
define("NBILL_HOUSEKEEPING_TYPE_5_HELP", "Deletes all contact records that are not associated with any potential client, client, or supplier records if there has been no activity for more than the specified amount of time.");
define("NBILL_HOUSEKEEPING_TYPE_6", "Pending Orders");
define("NBILL_HOUSEKEEPING_TYPE_6_HELP", "Deletes all pending order records that are older than the specified period.");
define("NBILL_HOUSEKEEPING_TYPE_7", "Orders (expired, cancelled, and one-off)");
define("NBILL_HOUSEKEEPING_TYPE_7_HELP", "Deletes all order records that have not been used to generate any invoices for more than the specified length of time and are not due to generate any invoices in the future.");
define("NBILL_HOUSEKEEPING_TYPE_8", "Invoices");
define("NBILL_HOUSEKEEPING_TYPE_8_HELP", "Deletes all invoice records that were paid more than the specified length of time ago, or for unpaid invoices, ones that were written off or, if not written off, ones that were created more than the specified length of time ago.");
define("NBILL_HOUSEKEEPING_TYPE_9", "Quotes");
define("NBILL_HOUSEKEEPING_TYPE_9_HELP", "Deletes all quote records that were accepted or rejected more than the specified period of time ago, or if not accepted or rejected, ones that were created more than the specified period of time ago.");
define("NBILL_HOUSEKEEPING_TYPE_10", "Gateway Transaction Data");
define("NBILL_HOUSEKEEPING_TYPE_10_HELP", "Deletes all gateway transaction records (records that tell " . NBILL_BRANDING_NAME . " what to do when a payment comes in) if they have not been used for more than the specified period of time.");
define("NBILL_HOUSEKEEPING_TYPE_11", "Income");
define("NBILL_HOUSEKEEPING_TYPE_11_HELP", "Deletes all income records that are older than the specified period.");
define("NBILL_HOUSEKEEPING_TYPE_12", "Expenditure");
define("NBILL_HOUSEKEEPING_TYPE_12_HELP", "Deletes all expenditure records that are older than the specified period.");
define("NBILL_HOUSEKEEPING_TYPE_13", "Supporting Documents");
define("NBILL_HOUSEKEEPING_TYPE_13_HELP", "Deletes all supporting document files that are older than the specified period.");
define("NBILL_HOUSEKEEPING_DATE_FROM", "That are more than...");
define("NBILL_HOUSEKEEPING_UNIT_DAYS", "day(s)");
define("NBILL_HOUSEKEEPING_UNIT_WEEKS", "week(s)");
define("NBILL_HOUSEKEEPING_UNIT_MONTHS", "month(s)");
define("NBILL_HOUSEKEEPING_UNIT_YEARS", "year(s)");
define("NBILL_HOUSEKEEPING_DATE_END", "old");
define("NBILL_HOUSEKEEPING_PREVIEW", "Preview Deletions");
define("NBILL_HOUSEKEEPING_NOTHING_TO_DELETE", "There are no records to delete.");
define("NBILL_HOUSEKEEPING_EXECUTE_WARNING", "WARNING! You are about to PERMANENTLY DELETE %s record(s) from your database that are over %s %s old. This operation cannot be undone, so make sure you have taken a backup of your database first. A summary of the records that are marked for deletion is shown below. Are you sure you want to do this?");
define("NBILL_HOUSEKEEPING_EXPAND", "Click to show records");
define("NBILL_HOUSEKEEPING_COLLAPSE", "Click to hide records");
define("NBILL_HOUSEKEEPING_X_RECORDS", "%s record(s)");
define("NBILL_HOUSEKEEPING_DELETE", "Yes, I am sure - DELETE!");
define("NBILL_HOUSEKEEPING_CANCEL", "No, I am not sure - ABORT!");
define("NBILL_HOUSEKEEPING_RECORDS_DELETED", "%s record(s) deleted");
define("NBILL_AUTO_HOUSEKEEPING_SUBJECT", NBILL_BRANDING_NAME . " Automatic Housekeeping");

//Version 2.3.0
define("NBILL_HOUSEKEEPING_TYPE_13", "Supporting Documents");
define("NBILL_HOUSEKEEPING_TYPE_13_HELP", "Deletes all supporting document files that are older than the specified period.");