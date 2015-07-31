<?php
/**
* Language file for Clients
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Clients
define("NBILL_CLIENTS_TITLE", "Clients");
define("NBILL_CLIENT_USER", "Username");
//define("NBILL_WEBSITE", "Website Address");
//define("NBILL_TELEPHONE", "Telephone");
//define("NBILL_TELEPHONE_2", "2nd Telephone Line");
define("NBILL_USERNAME_PASSWORD_EMAIL_REQUIRED", "In order to create a new user, a user name, password, and e-mail address are required.");
define("NBILL_EDIT_CLIENT", "Edit Client");
define("NBILL_NEW_CLIENT", "New Client");
define("NBILL_CLIENT_DETAILS", "Client Details");
//define("NBILL_COMPANY_NAME", "Company Name");
//define("NBILL_CLIENT_ADD_NAME_TO_INVOICE", "Add Primary Contact Name to Invoices?");
//define("NBILL_ADDRESS_1", "Address Line 1");
//define("NBILL_ADDRESS_2", "Address Line 2");
//define("NBILL_ADDRESS_3", "Address Line 3");
//define("NBILL_TOWN", "Town/City");
//define("NBILL_STATE", "State/County/Province");
//define("NBILL_POSTCODE", "Postcode");
//define("NBILL_CLIENT_COUNTRY", "Country");
define("NBILL_CLIENT_REFERENCE", "Client Reference");
//define("NBILL_MOBILE", "Mobile (Cell) Phone");
//define("NBILL_FAX", "Fax");
//define("NBILL_CLIENT_CURRENCY", "Default Currency");
//define("NBILL_CLIENT_TAX_ZONE", "Client Tax Zone");
define("NBILL_CREDIT_AMOUNT", "Credit Amount");
define("NBILL_CREDIT_TAX_AMOUNT", "Credit Tax Amount");
define("NBILL_CREDIT_LEDGER_CODE", "Credit Ledger Code");
define("NBILL_CREDIT_CURRENCY", "Credit Currency");
define("NBILL_CREDIT_DESC", "Credit Description");
define("NBILL_AUTO_DEDUCT", "Auto-deduct Credit");
define("NBILL_INSTR_COMPANY_NAME", "Name of the client company");
define("NBILL_INSTR_CLIENT_ADD_NAME_TO_INVOICE", "If both a company name and a primary contact name are defined, you can choose whether or not to display the contact name on invoices (if only one is defined, it will be used regardless of the value of this setting).");
define("NBILL_INSTR_ADDRESS", "Enter the billing address that will appear on invoices.");
define("NBILL_INSTR_CLIENT_COUNTRY", "Billing address country (used to determine tax rate if applicable)");
define("NBILL_INSTR_CLIENT_REFERENCE", "This is your reference that will appear on this client's invoices - choose something short that will help you identify the client.");
define("NBILL_INSTR_WEBSITE", "Just for your own reference.");
define("NBILL_INSTR_TELEPHONE", "");
define("NBILL_INSTR_TELEPHONE_2", "Optional, if there is an additional phone number you want to store.");
define("NBILL_INSTR_MOBILE", "");
define("NBILL_INSTR_FAX", "");
define("NBILL_INSTR_CLIENT_CURRENCY", "Select the default currency in which this client will place orders.");
define("NBILL_INSTR_CLIENT_TAX_ZONE", "If you have set up different tax rates for different tax zones, you can specify the tax zone code to use for this client.");
define("NBILL_INSTR_CREDIT_AMOUNT", "The amount of any credit this client may have on account (net of tax).");
define("NBILL_INSTR_CREDIT_TAX_AMOUNT", "The amount of any credit held by this client which represents tax (eg. where an overpayment has been made and some tax needs to be refunded).");
define("NBILL_INSTR_CREDIT_CURRENCY", "Currency of the credit amount held by this client");
define("NBILL_INSTR_CREDIT_LEDGER_CODE", "Nominal ledger code against which the credit should be recorded (at the time of refund).");
define("NBILL_INSTR_CREDIT_DESC", "Description to appear on invoice when credit amount is deducted.");
define("NBILL_INSTR_AUTO_DEDUCT", "Whether or not to automatically deduct any credit amount from the next invoice due (only if the invoice is in the same currency as the credit amount).");
define("NBILL_CLIENT_DELETED", "Client Deleted");
define("NBILL_INSTR_EMAIL_INVOICE_OPTIONS_CLIENT", "Specify the invoice notification method when new invoices are generated for this client. <strong>Note:</strong> If sending automated e-mails, it is highly recommended to ensure that all client records have an associated user record so that they can log into the website front end to set their preferences and view their invoices.");
define("NBILL_ACTIVE_CLIENTS_ONLY", "Clients with active orders only");
define("NBILL_INACTIVE_CLIENTS_ONLY", "Clients without active orders only");
define("NBILL_CLIENT_REMINDER_EMAILS", "Reminder E-Mails?");
define("NBILL_INSTR_CLIENT_REMINDER_EMAILS", "Whether or not to send reminder e-mails to this client (if applicable) - see reminders feature for more information.");

//Version 1.2.1
define("NBILL_CLIENT_VIEW_ORDERS", "View Orders for this Client");

//Version 1.2.6
define("NBILL_CLIENT_ACTION", "Action");
define("NBILL_CLIENT_VIEW_INVOICES", "View Invoices for this Client");
define("NBILL_CLIENT_NEW_ORDER", "Create New Order for this Client");
define("NBILL_CLIENT_NEW_INVOICE", "Create New Invoice for this Client");
define("NBILL_CLIENT_NEW_REFUND", "Create New Credit Note for this Client");

//Version 2.0.0
define("NBILL_CLIENT_TAB_CLIENT", "Client");
define("NBILL_CLIENT_PRIMARY", "Primary Contact");
define("NBILL_INSTR_CLIENT_PRIMARY", "Select which contact is the main billing contact for this client");
define("NBILL_CONTACT_NAME_UNKNOWN", "[Name Unknown]");
define("NBILL_CLIENT_NO_IFRAMES", "Your browser does not support iframes - please click on the following link to edit this contact: %s");
define("NBILL_CLIENT_NEW_CONTACT", "Add New Contact");
define("NBILL_CLIENT_ASSIGN_CONTACT", "Assign Contact");
define("NBILL_CLIENT_REMOVE_CONTACT", "Remove this Contact");
define("NBILL_CLIENT_DELETE_CONTACT", "Delete this Contact");
define("NBILL_CLIENT_CONTACT_DELETE_SURE", "You have selected to PERMANENTLY DELETE %s contact(s). Are you sure you want to continue?");
define("NBILL_CLIENT_CONTACT_FILTER", "Enter the name or part of the name of the contact you want to assign, and click on 'Go'.");
define("NBILL_CLIENT_CONTACT_FILTER_GO", "Go");
define("NBILL_CLIENT_CONTACT_PERMISSIONS", "Contact Permissions");
define("NBILL_CLIENT_UPDATE_PROFILE", "Update Client Profile?");
define("NBILL_INSTR_CLIENT_UPDATE_PROFILE", "Whether or not this contact is allowed to update the details held on the Client record (eg. company name and address).");
define("NBILL_CLIENT_ACCESS_ORDERS", "Access Orders?");
define("NBILL_INSTR_CLIENT_ACCESS_ORDERS", "Whether or not this contact is allowed to access the order details for this client (including the ability to renew, if applicable).");
define("NBILL_CLIENT_ACCESS_INVOICES", "Access Invoices?");
define("NBILL_INSTR_CLIENT_ACCESS_INVOICES", "Whether or not this contact is allowed to access the invoice details for this client.");
define("NBILL_CLIENT_ACCESS_QUOTES", "Access Quotes?");
define("NBILL_INSTR_CLIENT_ACCESS_QUOTES", "Whether or not this contact is allowed to access the quotation details for this client (in additon to any quotes they may have requested themselves).");
define("NBILL_CLIENT_ALLOW_OPT_IN", "Allow Reminder Opt-in/out?");
define("NBILL_INSTR_CLIENT_ALLOW_OPT_IN", "Whether or not this contact is allowed to opt in to or out of receiving reminder e-mails.");
define("NBILL_CLIENT_CREDIT", "Client Credit");
define("NBILL_CREDIT_TAX_RATE", "Tax Rate");
define("NBILL_INSTR_CREDIT_TAX_RATE", "The rate of tax used where the amount of any credit held by this client includes a tax amount (eg. where an overpayment has been made and some tax needs to be refunded).");
define("NBILL_CLIENT_CUSTOM_FIELDS", "Custom Fields");
define("NBILL_CLIENT_NEW_QUOTE", "Create New Quote for this Client");
define("NBILL_CLIENT_VIEW_QUOTES", "View Quotes for this Client");

//Version 2.0.9
define("NBILL_CLIENT_USERNAME_ALPHANUM", "Please use letters and numbers only for the username");

//Version 2.1.0
define("NBILL_CLIENT_LANGUAGE", "Default Language");
define("NBILL_INSTR_CLIENT_LANGUAGE", "Where more than one language pack is installed, each client can be assigned a particular language which will be used by default in your website front-end and when sending emails to the client.");

//Version 3.0.0
define("NBILL_CLIENT_IP_INFO", "IP Address Information");
define("NBILL_CLIENT_IP_INFO_INTRO", "The following IP address information has been detected for this client (country information is only collected if Geo-IP lookups are enabled in Global Configuration).");
define("NBILL_CLIENT_IP_DATE", "Date");
define("NBILL_CLIENT_IP_ADDRESS", "IP Address");
define("NBILL_CLIENT_IP_COUNTRY", "Country Code");