<?php
/**
* Language file for the Reminders feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
* 
* @access private* 
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Reminders
define("NBILL_REMINDERS_TITLE", "Reminders");
define("NBILL_REMINDERS_WARNING", "Warning! This feature has the potential to send e-mails to ALL of your clients. Please be careful, and use at your own risk!");
define("NBILL_REMINDERS_INTRO", "Record details here of any reminders you want to send to yourself or to clients when payments are due, orders are up for renewal, or about to expire, or when invoices are overdue. PLEASE NOTE: Setting these values will have NO EFFECT UNLESS you have also set up a scheduled task to actually check what is due and fire off the e-mails. To do this, you would typically use a CRON job along with the scheduled reminder script - available from the " . NBILL_BRANDING_NAME . " website. Please see the documentation for more details on how to set up a CRON job.");
define("NBILL_REMINDER_NAME", "Reminder Name");
define("NBILL_EDIT_REMINDER", "Edit Reminder");
define("NBILL_REMINDER_TYPE", "Reminder Type");
define("NBILL_REMINDER_ACTIVE", "Active?");
define("NBILL_REMINDER_ACTIVE_YES", "Active");
define("NBILL_REMINDER_ACTIVE_NO", "Inactive");
define("NBILL_REMINDER_NAME_REQUIRED", "Please enter a name to identify this reminder");
define("NBILL_REMINDER_STARTING_FROM", "Starting From");
define("NBILL_REMINDER_NUMBER_OF_UNITS", "Number of Units");
define("NBILL_NEW_REMINDER", "New Reminder");
define("NBILL_REMINDER_DETAILS", "Reminder Details");
define("NBILL_INSTR_REMINDER_NAME", "Enter a name that will help you easily identify what this reminder is for.");
define("NBILL_INSTR_REMINDER_TYPE", "Select the type of reminder you want to set up.");
define("NBILL_INSTR_REMINDER_ACTIVE", "Whether or not to send e-mails out for this reminder.");
define("NBILL_REMINDER_ADMIN", "Administrator?");
define("NBILL_INSTR_REMINDER_ADMIN", "Whether or not to send the reminder e-mail to the administrator rather than the client (if you want a reminder sent to both administrator and client, please set up 2 separate reminder records).");
define("NBILL_INSTR_REMINDER_STARTING_FROM", "Date from which to begin sending reminder e-mails (if blank, reminders will be sent regardless of date).");
define("NBILL_REMINDER_NO_OF_UNITS", "Number of Units");
define("NBILL_INSTR_REMINDER_NO_OF_UNITS", "Enter the number of units for the interval between the event and the reminder (or in the case of user-defined reminders, the interval between reminders). For example, for an interval of 7 days, the number of units is 7 (and the units are 'Days').");
define("NBILL_REMINDER_UNITS", "Units");
define("NBILL_INSTR_REMINDER_UNITS", "Select the type of units for the interval.");
define("NBILL_REMINDER_SEND_AFTER", "Send After?");
define("NBILL_INSTR_REMINDER_SEND_AFTER", "Whether or not to send the reminder AFTER the event (typically only used for overdue invoices - ie. to send a reminder x days after the invoice date).");
define("NBILL_REMINDER_EMAIL_TEXT", "E-Mail Text");
define("NBILL_INSTR_REMINDER_EMAIL_TEXT", "Type in the e-mail message you want to send. You can use the following tokens: {NAME} = inserts client name; {VENDOR} = inserts vendor name; {DATE} = inserts event date; {INTERVAL} = inserts number of units; {UNITS} = inserts type of unit; {ORDER} = order details; {AMOUNT} = amount owing (nb. discounts will NOT be taken into account); {WEBSITE} = your website address; {PAYLINK} = inserts a payment link, if applicable (renewals and overdue invoices only); {PAYLINK_HTTPS} = same as paylink, but uses https at the start of the URL; {INVOICE} = inserts invoice number (overdue invoices only).");
define("NBILL_REMINDER_CLIENT", "Client");
define("NBILL_INSTR_REMINDER_CLIENT", "The client to whom the reminder should be sent (if 'Administrator' setting above is set to 'No'). WARNING! If this is set to 'Not Applicable', a reminder will be sent to EVERY client for whom it is due. If this is a 'user-defined' reminder, and the 'Administrator' setting is set to 'no', you MUST select a client here, otherwise no reminder will be sent.");
define("NBILL_REMINDER_FILTER", "Filter");
define("NBILL_INSTR_REMINDER_FILTER", "FOR ADVANCED USERS ONLY: You can filter the records to which the reminder will apply by entering a filter here in the format of an SQL 'where' clause. For example, to send payment due reminders only to clients with orders that have annual payment intervals, you could enter \" #__nbill_orders.payment_frequency = 'EE' \" here. The database tables on which filtering can be performed depend on the type of reminder.");

//Default e-mail text
define("NBILL_REMINDER_PAYMENT_DUE_EMAIL", "Dear {NAME},\n\nThis is a courtesy e-mail to let you know that a recurring payment for the following order is due on {DATE}. If you have set up a recurring payment schedule, you will be automatically debited with {AMOUNT} (less any discounts for which you qualify, if applicable) on or around the due date.\n\nOrder Details:\n\n{ORDER}\n\nIf you no longer wish to receive these reminders, please login to your account at {WEBSITE}, and set your preferences under 'My Account->My Profile'.\n\nRegards,\n{VENDOR}");
define("NBILL_REMINDER_ORDER_EXPIRY_EMAIL", "Dear {NAME},\n\nThis is a courtesy e-mail to let you know that the following order will expire on {DATE}. Please ensure any regular payment schedule is cancelled.\n\nOrder Details:\n\n{ORDER}\n\nIf you no longer wish to receive these reminders, please login to your account at {WEBSITE}, and set your preferences under 'My Account->My Profile'.\n\nRegards,\n{VENDOR}.");
define("NBILL_REMINDER_RENEWAL_DUE_EMAIL", "Dear {NAME},\n\nThis is a reminder that your subscription for the following order is due for renewal on {DATE}. If you would like to renew, please use the following link: {PAYLINK}. Any remaining time on your old subscription will be added to the new one so you will not lose out by renewing early.\n\n{ORDER}\n\nIf you no longer wish to receive these reminders, please login to your account at {WEBSITE}, and set your preferences under 'My Account->My Profile'.\n\nRegards,\n{VENDOR}");
define("NBILL_REMINDER_INVOICE_OVERDUE_EMAIL", "Dear {NAME},\n\nThis is a reminder that invoice number {INVOICE} for {AMOUNT} has been outstanding for {INTERVAL} {UNITS}, and is therefore now overdue. Please arrange to pay this invoice as soon as possible. If you would like to pay online, you can use the following link: {PAYLINK}.\n\nRegards,\n{VENDOR}.");
define("NBILL_REMINDER_USER_DEFINED_EMAIL", "Don't forget: <Enter reminder text here!>");
define("NBILL_REMINDER_PAYMENT_DUE_SUBJECT", "%s Reminder: Payment Due");
define("NBILL_REMINDER_ORDER_EXPIRY_SUBJECT", "%s Reminder: Order Expiry");
define("NBILL_REMINDER_RENEWAL_DUE_SUBJECT", "%s Reminder: Renewal Due");
define("NBILL_REMINDER_INVOICE_OVERDUE_SUBJECT", "%s Reminder: Invoice Overdue");
define("NBILL_REMINDER_USER_DEFINED_SUBJECT", "%s Reminder");

//Reminder Types
/*define("NBILL_REMINDER_PAYMENT_DUE", "Payment Due");
define("NBILL_REMINDER_ORDER_EXPIRY", "Order Expiry");
define("NBILL_REMINDER_RENEWAL_DUE", "Renewal Due");
define("NBILL_REMINDER_INVOICE_OVERDUE", "Invoice Overdue");
define("NBILL_REMINDER_USER_DEFINED", "User-defined");*/

//Units
define("NBILL_REMINDER_UNIT_DAYS", "Days");
define("NBILL_REMINDER_UNIT_WEEKS", "Weeks");
define("NBILL_REMINDER_UNIT_MONTHS", "Months");

//CRON script
define("NBILL_REMINDER", "Reminder");
define("NBILL_REMINDERS_SENT_INTRO", "%s Reminders have been processed by " . NBILL_BRANDING_NAME . ". Timestamp: %s.\n\n");
define("NBILL_REMINDERS_SENT_SUBJECT", NBILL_BRANDING_NAME . " Scheduled Reminders");
define("NBILL_REMINDERS_SENT_FAILURE", "FAILURE");
define("NBILL_REMINDERS_SENT_NO_LICENSE", NBILL_BRANDING_NAME . " License Key Incorrect, or Not Found");
define("NBILL_REMINDERS_SENT_TO", "sent to");
define("NBILL_REMINDERS_SENT_FOR","for client");
define("NBILL_REMINDERS_SENT_MESSAGE", "MESSAGE:");
define("NBILL_REMINDER_SENT_ERROR", "WARNING! An error occurred whilst attempting to send this reminder. Reminder not sent.");
define("NBILL_REMINDER_SENT_ERROR_NO_CLIENT", "WARNING! An error occurred whilst attempting to send this reminder. No client record was found. Reminder not sent.");
define("NBILL_REMINDER_ORDER_NO", "Order No.");

//Version 1.2.1
//Note to translators: Text for NBILL_INSTR_REMINDER_EMAIL_TEXT updated to add description of {PAYLINK_HTTPS} (on line 51 of en-GB language file)