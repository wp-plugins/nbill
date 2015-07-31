<?php
/**
* Language file for General usage in administrator and front-end features
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Installation
define("NBILL_INSTALL_COMPLETE", NBILL_BRANDING_NAME . " installation complete.");
define("NBILL_DB_ERRORS", NBILL_BRANDING_NAME . " has been installed, but one or more database errors occurred during the installation process. The errors are displayed below.");
define("NBILL_CANNOT_UPGRADE", "Sorry, you cannot upgrade from a version prior to 1.1.0. Please uninstall this version, install 1.1.0 to upgrade your database, then try upgrading again to this version.");
define("NBILL_UPGRADED_SUCCESSFULLY", "Your installation of " . NBILL_BRANDING_NAME . " has been upgraded successfully.");

//Home
define("NBILL_WELCOME", "Welcome to " . NBILL_BRANDING_NAME . "!");
define("NBILL_WELCOME_BETA", "Welcome to " . NBILL_BRANDING_NAME . " BETA!");
define("NBILL_HOME_PAGE_TEXT","To get started, click on each menu option above in turn - they are ordered according to the order you need to set things up.  So, start with Configuration->Global Configuration, then set up your currencies, then your vendors, etc. Most pages include helpful comments to guide you through the process. Please refer to %s for documentation and support.");
define("NBILL_HELP", "Help");

//Licensing
define("NBILL_NO_LICENSE_KEY", "Please enter your license key.");
define("NBILL_GET_KEY", "<ul><li>If you do not have a license key, you can get a 35 day free trial - please %s to order your free trial license key.</li><li>To purchase an annual or outright license key, %s</li><li>If you have just upgraded nBill or copied your site, and are suddenly getting this screen even though you have a key, it is possible that the automatic key upgrader failed, in which case, please %s to get an upgraded key.</li></ul>");
define("NBILL_LICENSE_INVALID", "Your license key is not valid. It may have expired, or it may not be valid for the domain you are trying to use it on. NOTE: If you enter the wrong license key more than 12 times, you will be locked out for 10 minutes. If your key is not working, please check this page: %s.");
define("NBILL_NEW_LICENSE_KEY", "Paste new license key here:");
define("NBILL_RETURN_TO_MAIN", "Click here to return to the Home Page");
define("NBILL_LIC_TYPE", "License Type: ");
define("NBILL_LIC_TYPE_T", "Trial");
define("NBILL_LIC_TYPE_L", "Local Web Server");
define("NBILL_LIC_TYPE_S", "Single Site");
define("NBILL_LIC_TYPE_D", "Domain Wide");
define("NBILL_LIC_TYPE_F", "Full");
define("NBILL_LIC_DOMAIN", "Domain: ");
define("NBILL_LIC_LIVE_SITE", "Live Site: ");
define("NBILL_LIC_EXPIRY", "License Expires: ");
define("NBILL_YOUR_DOMAIN", "Your `Live Site` value is: ");
define("NBILL_NO_EXPIRY", "No Expiry");
define("NBILL_ACCEPT_TERMS", "I have read, fully understood, and agree to the %s");
define("NBILL_EULA", "End User License Agreement (opens in a new window)");
define("NBILL_PLEASE_ACCEPT_TERMS", "Sorry, you must check the box to indicate that you accept the End User License Agreement before you can continue.");
define("NBILL_LOCKED_OUT_TITLE", "Access has been Blocked!");
define("NBILL_LOCKED_OUT_DESC", "You have tried to access the component using an invalid license key too many times. The component has now been blocked. Please wait 10 minutes, then try again.");
define("NBILL_LICENSE_EXPIRED", "Your license expired %s");
define("NBILL_BLACKLISTED", "Your domain name has been blacklisted (typically this would be due to a chargeback after purchase). You are not permitted to continue. If you feel this is in error, please contact us.");

//Global
define("NBILL_CONTINUE", "Click here to continue");
define("NBILL_TRY_AGAIN", "Click here to try again");
define("NBILL_CONTINUE_HOME", "Click here to return to the home page.");
define("NBILL_ALL", "[Show All]");
define("NBILL_NONE", "[None]");
define("NBILL_NOTES", "Notes");
define("NBILL_INSTR_VENDOR_ID", "Select the Vendor with which this record is associated.");
define("NBILL_INSTR_NOTES", "Notes are for your own comments, reminders, etc. and are not used by the component except to indicate that a record was auto-generated.");
define("NBILL_MISCELLANEOUS", "Miscellaneous");
define("NBILL_ROOT", "Root");
define("NBILL_NOT_APPLICABLE", "Not Applicable");
define("NBILL_TAX", "Tax");
define("NBILL_FOR", "for");
define("NBILL_TO", "to");
define("NBILL_GO", "Go");
define("NBILL_DATE_RANGE", "From");
define("NBILL_ERR_COULD_NOT_GET_NEXT_NO", "The component was unable to identify the next %s number because the database field is currently locked by another process. If the problem persists, you can unlock the %s number field by editing the Vendor record.");
define("NBILL_ERR_NO_NOT_NUMERIC", "The next %s number for this vendor is not numeric! Please update the vendor record to specify a numeric %s number sequence, or enter the %s number manually.");
define("NBILL_ERR_NO_NOT_FOUND", "The next %s number for this vendor could not be found! You may have to enter the %s number manually.");
define("NBILL_PREV", "&laquo; Previous");
define("NBILL_NEXT", "Next &raquo;");
define("NBILL_SAVE_CHANGES", "You have made changes to the data on this page but have not saved the record. If you want to save your changes before moving on, click OK. If you want to move on without saving, click on Cancel");  //Don't use apostrophes or speech marks in this text
define("NBILL_SELECT", "Select");
define("NBILL_DESELECT", "Deselect");
define("NBILL_UNKNOWN", "Unknown");
define("NBILL_NEW", "New");
define("NBILL_BACK", "Back");
define("NBILL_CLOSE_WINDOW", "Close Window");
define("NBILL_NO_ACTION_TAKEN", "No action taken.");
define("NBILL_ADMIN_IMAGE", "style=\"height:54px; padding-left: 54px; background-image: url('%s/images/icons/large/%s.gif') !important;background-position:left;background-repeat:no-repeat;\"");
define("NBILL_NUMERIC_ONLY", "The field `%s` requires a numeric value. Please only enter a number here.");
define("NBILL_INVALID_DATE_FIELD", "The field `%s` requires a date value. Please enter a date in the format %s.");
define("NBILL_ERR_SERIOUS_ERROR", "Sorry, an error has occurred. An e-mail containing details of the error has been sent to the appropriate person. Apologies for the inconvenience.");
define("NBILL_ERR_SERIOUS_ERROR_ADMIN", "Sorry, an error has occurred. An e-mail containing details of the error has been sent to the appropriate person (as defined in Global Configuration page of " . NBILL_BRANDING_NAME . "). Apologies for the inconvenience.");
define("NBILL_ERR_SERIOUS_ERROR_NOMAIL", "Sorry, an error has occurred. The component was unable to send an e-mail containing details of the error to the appropriate person. Please contact the owner of this website (%s) to inform them of this error (which has been logged in their database and/or PHP error log). Apologies for the inconvenience.");
define("NBILL_ERR_SERIOUS_ERROR_NOMAIL_ADMIN", "Sorry, an error has occurred. The component was unable to send an e-mail containing details of the error to the appropriate person (as defined in Global Configuration page of " . NBILL_BRANDING_NAME . "). As this error occurred in the administrator, details of the error are provided below. Apologies for the inconvenience.");
define("NBILL_ERR_REPORT_INTRO", "An error has occurred in " . NBILL_BRANDING_NAME . " (front end)! Details of the error are given below:\n\n");
define("NBILL_ERR_REPORT_INTRO_ADMIN", "An error has occurred in " . NBILL_BRANDING_NAME . " (administrator)! Details of the error are given below:\n\n");
define("NBILL_ID", "ID");
define("NBILL_PRODUCT_SETUP_FEE", "Setup Fee");
define("NBILL_PRODUCT_NEGATIVE_SETUP_FEE", "Trial Period Discount");
define("NBILL_ERR_MASTER_DB_CONNECT", "Could not connect to the master database!");
define("NBILL_ERR_MASTER_DB_LOOP", "PROCESS ABORTED! You appear to have a loop of master databases. Please ensure each database is only the master of ONE other database.");
define("NBILL_ERR_MASTER_DB_TOO_OLD", "A master database belongs to an different version of " . NBILL_BRANDING_NAME . ". Please ensure all databases are running the same version.");
define("NBILL_PRINTER_FRIENDLY", "Printer Friendly Version");
define("NBILL_DATE_PRINTED", "Date Printed: ");
define("NBILL_ERR_COULD_NOT_CREATE_USER", "WARNING! Although the order form is set to automatically create a user record, it was not possible to create a user for this order. This can happen if the e-mail address field is suppressed (because an e-mail address is required in order to create a new user record).");
define("NBILL_CSV_DOWNLOAD", "CSV Download");
define("NBILL_CSV_DOWNLOAD_DESC", "Download this report as a CSV file (which can be opened in a spreadsheet application such as MS Excel, or imported into another application)");
define("NBILL_EMAIL_DOWNLOADS_SUBJECT", "Downloadable Product(s) Attached - Order no: %s");

//The following have been moved from other sections (used in more than one place)
define("NBILL_VENDOR_NAME", "Vendor Name");
define("NBILL_CLIENT", "Client / Contact");
define("NBILL_EMAIL_INVOICE_OPTIONS", "Invoice Notifications");
define("NBILL_NOMINAL_LEDGER_CODE", "Nominal Ledger Code");
define("NBILL_INSTR_NOMINAL_LEDGER_CODE", "");
define("NBILL_ENTER_OVERRIDE_DATE", "Enter the date (YYYY/MM/DD) up to which you want invoices generated. Any orders which fall due AFTER the date you enter will NOT be generated."); //Need to keep this quite short, as it is used in a javascript prompt which is a fixed size in IE
define("NBILL_PRODUCT_CATS", "Category");
define("NBILL_INVOICE_DETAILS", "Invoice Details");
define("NBILL_SHIPPING_SERVICE", "Shipping Service");
define("NBILL_SELECT_CLIENT", "Client");
define("NBILL_INSTR_SELECT_CLIENT", "");
define("NBILL_VENDOR_ADDRESS", "Vendor Address");
define("NBILL_TAX_REFERENCE_DESC", "Tax Reference Description");
define("NBILL_TAX_ABBREVIATION", "Abbreviation");
define("NBILL_INSTR_TAX_REFERENCE_DESC", "Enter a descriptive name of your tax reference number (eg. 'VAT Number', 'N&uacute;mero de IVA')");
define("NBILL_INSTR_TAX_ABBREVIATION", "Enter the commonly used abbreviation for this type of tax (eg. VAT, IVA)");
define("NBILL_TAX_REFERENCE_NO", "Tax Reference Number");
define("NBILL_INSTR_TAX_REFERENCE_NO", "Enter your company tax reference number (eg. your VAT number), including country code (if applicable)");
define("NBILL_INSTR_TAX_EXEMPTION_CODE", "If a tax rate can be omitted on production of the client's VAT number or reseller certification, enter the reference here.");
define("NBILL_EMAIL_ADDRESS", "E-Mail Address");
define("NBILL_RELATING_TO", "Relating To");
define("NBILL_CURRENCY", "Currency");
define("NBILL_ORDER_DETAILS", "Order Details");
define("NBILL_CLIENT_NAME", "Client Name");
define("NBILL_ADD_INVOICE_ITEM", "Add Item");
define("NBILL_REMOVE_INVOICE_ITEM", "Remove Item");
define("NBILL_INSTR_CURRENCY", "");
define("NBILL_INVOICES_GENERATED", "%s Invoice(s) Generated.");
define("NBILL_REDIRECTED_TO_INVOICE_SUMMARY", "You have been redirected to the invoice summary screen.");
define("NBILL_PRODUCT_CATEGORY", "Category");
define("NBILL_INVOICE_GENERATION_ERRORS", "One or more errors occurred whilst attempting to generate invoices. The error messages are listed below.");
define("NBILL_ERR_INVOICE_GENERATION_LOOP", "The invoice generation script seems to have got itself stuck in a loop. Process therefore terminated after 100 repetitions. Most likely cause is that the end date for an order could not be calculated. Please contact " . NBILL_BRANDING_COMPANY . " for assistance.");
define("NBILL_ERR_COULD_NOT_INSERT_INVOICE_ITEM", "Unable to insert invoice item record in the database.");
define("NBILL_ERR_COULD_NOT_INSERT_DISCOUNT_INVOICE_ITEM", "Unable to insert invoice item record in the database for a discount.");
define("NBILL_MY_ACCOUNT", "My Account");
define("NBILL_MY_PROFILE", "My Profile");
define("NBILL_MY_ORDERS", "My Orders");
define("NBILL_MY_INVOICES", "My Invoices");
define("NBILL_MY_QUOTES", "My Quotes");
define("NBILL_SUBMIT", "Submit");
define("NBILL_CANCEL", "Cancel");
define("NBILL_INVALID_DATE_ENTERED", "The date entered was not valid. Date must be entered in the format %s.");

//Main Menu
define("NBILL_MNU_HOME", "Home");
define("NBILL_MNU_HOME_DESC", "Main Page");
define("NBILL_MNU_CONFIG", "Configuration");
define("NBILL_MNU_CONFIG_DESC", "System Configuration");
define("NBILL_MNU_GLOBAL_CONFIG", "Global Configuration");
define("NBILL_MNU_GLOBAL_CONFIG_DESC", "Main Application Settings");
define("NBILL_MNU_VENDOR", "Vendors");
define("NBILL_MNU_VENDOR_DESC", "Manage Vendors (your own company/ies)");
define("NBILL_MNU_QUICK_LINKS", "Quick Links:");
define("NBILL_MNU_CLIENTS", "Clients");
define("NBILL_MNU_CLIENTS_DESC", "Manage Clients");
define("NBILL_MNU_PRODUCTS", "Products");
define("NBILL_MNU_PRODUCTS_DESC", "Manage Products");
define("NBILL_MNU_ORDERS", "Orders");
define("NBILL_MNU_ORDERS_DESC", "Manage Orders");
define("NBILL_MNU_INVOICES", "Invoices");
define("NBILL_MNU_INVOICES_DESC", "Manage Invoices");
define("NBILL_MNU_CURRENCIES", "Currencies");
define("NBILL_MNU_CURRENCIES_DESC", "Manage Currencies");
define("NBILL_MNU_SALES_TAX", "Sales Tax (VAT)");
define("NBILL_MNU_SALES_TAX_DESC", "Manage Tax Rates");
define("NBILL_MNU_SHIPPING", "Shipping");
define("NBILL_MNU_SHIPPING_DESC", "Setup Shipping Options");
define("NBILL_MNU_NOMINAL_LEDGER", "Nominal Ledger");
define("NBILL_MNU_NOMINAL_LEDGER_DESC", "Manage Cost Centres");
define("NBILL_MNU_BACKUP_RESTORE", "Backup/Restore");
define("NBILL_MNU_BACKUP_RESTORE_DESC", "Backup or Restore your Data");
define("NBILL_MNU_BILLING", "Billing");
define("NBILL_MNU_BILLING_DESC", "Manage Clients, Products, Orders, Invoices");
define("NBILL_MNU_PRODUCT_CATS", "Product Categories");
define("NBILL_MNU_PRODUCT_CATS_DESC", "Manage Product Categories");
define("NBILL_MNU_CREDIT_NOTES", "Credit Notes (Refunds)");
define("NBILL_MNU_CREDIT_NOTES_DESC", "Manage Credit Notes");
define("NBILL_MNU_FRONT_END", "Website");
define("NBILL_MNU_FRONT_END_DESC", "Manage features relating to website front end");
define("NBILL_MNU_ORDER_FORMS", "Order Forms");
define("NBILL_MNU_ORDER_FORMS_DESC", "Manage Order Forms");
define("NBILL_MNU_GATEWAYS", "Payment Gateways");
define("NBILL_MNU_GATEWAYS_DESC", "Manage Payment Gateways");
define("NBILL_MNU_PENDING_ORDERS", "Pending Orders");
define("NBILL_MNU_PENDING_ORDERS_DESC", "Manage Pending Orders");
define("NBILL_MNU_ACCOUNTING", "Accounting");
define("NBILL_MNU_ACCOUNTING_DESC", "Manage Income and Expenditure");
define("NBILL_MNU_INCOME", "Income");
define("NBILL_MNU_INCOME_DESC", "Manage Income");
define("NBILL_MNU_EXPENDITURE", "Expenditure");
define("NBILL_MNU_EXPENDITURE_DESC", "Manage Expenditure");
define("NBILL_MNU_AUDIT_LOG", "Audit Log");
define("NBILL_MNU_AUDIT_LOG_DESC", "View Audit Log Entries");
define("NBILL_MNU_REPORTS", "Reports");
define("NBILL_MNU_REPORTS_DESC", "Produce Reports");
define("NBILL_MNU_JACL_VOUCHER", "ACL Upgrade Vouchers");
define("NBILL_MNU_JACL_VOUCHER_DESC", "Define voucher codes for user subscription level upgrades");
define("NBILL_MNU_DISPLAY_OPTIONS", "Display Options");
define("NBILL_MNU_DISPLAY_OPTIONS_DESC", "Define what options get displayed in the front end");
define("NBILL_MNU_TAX_SUMMARY", "Tax Summary");
define("NBILL_MNU_TAX_SUMMARY_DESC", "Produce tax summary report for a given date range");
define("NBILL_MNU_EXTENSIONS", "Extensions");
define("NBILL_MNU_EXTENSIONS_DESC", "3rd Party Extensions to " . NBILL_BRANDING_NAME);
define("NBILL_MNU_EXTENSIONS_INSTALL", "Extensions Installer");
define("NBILL_MNU_EXTENSIONS_INSTALL_DESC", "Install a 3rd party extension to " . NBILL_BRANDING_NAME . ".");
define("NBILL_MNU_SUPPLIERS", "Suppliers");
define("NBILL_MNU_SUPPLIERS_DESC", "Manage Suppliers");
define("NBILL_MNU_EXPENDITURE_LIST", "Expenditure List");
define("NBILL_MNU_EXPENDITURE_LIST_DESC", "Manage expenditure payment records");
define("NBILL_MNU_DISCOUNTS", "Discounts");
define("NBILL_MNU_DISCOUNTS_DESC", "Manage Discount Rules");
define("NBILL_MNU_IO", "Import/Export");
define("NBILL_MNU_IO_DESC", "Import or export data");
define("NBILL_MNU_IO_CLIENTS", "Clients");
define("NBILL_MNU_IO_CLIENTS_DESC", "Import or export Client records");
define("NBILL_MNU_PAYMENT_LINK", "Payment Link Generator");
define("NBILL_MNU_PAYMENT_LINK_DESC", "Generate a hyperlink that will allow you to request payment from a customer");
define("NBILL_MNU_GATEWAY_FUNCTIONS", "%s Functions");
define("NBILL_MNU_GATEWAY_FUNCTIONS_DESC", "Extended functions for the %s gateway.");
define("NBILL_MNU_TRANSACTION_REPORT", "Transaction Statement");
define("NBILL_MNU_TRANSACTION_REPORT_DESC", "List of all income/expenditure by date, with summary");
define("NBILL_MNU_LEDGER_REPORT", "Ledger Report");
define("NBILL_MNU_LEDGER_REPORT_DESC", "Nominal Ledger Summary");
define("NBILL_MNU_REMINDERS", "Reminders");
define("NBILL_MNU_REMINDERS_DESC", "Configure the sending of reminder e-mails");
define("NBILL_MNU_EMAIL_LOG", "E-Mail Log");
define("NBILL_MNU_EMAIL_LOG_HELP", "View a list of all e-mails sent by the system");
define("NBILL_MNU_FAVOURITES", "Favourites");
define("NBILL_MNU_FAVOURITES_DESC", "Manage which icons appear on the home page");

//Account Expiry
define("NBILL_SUB_EXPIRY_MESSAGE", "Please Note: your user record has been %s. The reason for this is: %s<br /><br />If you have any queries about this, please contact us.");
define("NBILL_SUB_EXPIRY_BLOCKED", "BLOCKED");
define("NBILL_SUB_EXPIRY_DELETED", "DELETED");
define("NBILL_SUB_EXPIRY_DOWNGRADED", "DOWNGRADED to '%s'");
define("NBILL_SUB_EXPIRY_REASON_CANCELLED", "your user subscription order has been cancelled.");
define("NBILL_SUB_EXPIRY_REASON_DELETED", "your user subscription order has been deleted.");
define("NBILL_SUB_EXPIRY_REASON_EXPIRED", "your user subscription order has exceeded its fixed expiry date.");
define("NBILL_SUB_EXPIRY_REASON_NOT_RENEWED", "your user subscription order has not been renewed. To continue accessing this site with your previous user privileges, please contact us to renew your subscription.");
define("NBILL_SUB_EXPIRY_REASON_UNKNOWN", "unknown. Please contact us if you feel this is in error.");

//Version checking
define("NBILL_SOFTWARE_OOD", "WARNING! Your copy of " . NBILL_BRANDING_NAME . " is out of date. You are attempting to run an old version of " . NBILL_BRANDING_NAME . " with a newer version of the database. This will result in program errors - please upgrade your software (see " . NBILL_BRANDING_WEBSITE . ").");
define("NBILL_DATABASE_OOD", "WARNING! Your database needs to be upgraded. Failure to do so will result in program errors. %s to upgrade now.");
define("NBILL_CLICK_HERE", "Click Here");
define("NBILL_NEW_VERSION_AVAILABLE", "A new version of " . NBILL_BRANDING_NAME . " (%s) is now available.");
define("NBILL_NEW_VERSION_MANUAL", "This version cannot be installed automatically - you need to upgrade manually (see documentation for details)");
define("NBILL_PATCH_COPY_FAILURES", "An attempt was made to install a patch, but the following files could not be copied. Please download the patch from the " . NBILL_BRANDING_NAME . " website and re-install it manually.");
define("NBILL_PATCH_DB_ERRORS", "An attempt was made to install a patch, but the following database error(s) occurred. You may need to download the patch from the " . NBILL_BRANDING_NAME . " website and re-install it manually.");
define("NBILL_PATCH_FAILED_TO_INSTALL", "An attempt was made to install a patch, but one or more errors occurred. This is probably because your file or folder permissions do not allow PHP to overwrite the files. Alternatively, it could be that a connection to the server where the patch file is located could not be established. Please either try again later, or download the patch from the " . NBILL_BRANDING_NAME . " website and re-install it manually.");
define("NBILL_AUTO_UPDATE_DISABLED", "Automatic updates have been disabled - to turn automatic updates on again, refer to the %sGlobal Configuration%s page.");
define("NBILL_NO_NEW_VERSION_AVAILABLE", "You already have the latest version - no new version is available at present.");
define("NBILL_PATCH_INSTALLED", "Your software has been upgraded. You are now running the latest version.");
define("NBILL_TO_UPGRADE_NOW", "%s to upgrade now."); //Used in conjunction with NBILL_CLICK_HERE
define("NBILL_UNABLE_TO_CHECK_VERSION", "Sorry, a connection to the online version checker could not be established. This might be because you do not have access to the internet, or the server might be down. It is not known whether or not you are running the latest version. Please try again later, or check the " . NBILL_BRANDING_NAME . " website.");

//Toolbar
define("NBILL_TB_NEW", "New");
define("NBILL_TB_EDIT", "Edit");
define("NBILL_TB_DELETE", "Delete");
define("NBILL_TB_SAVE", "Save");
define("NBILL_TB_APPLY", "Apply");
define("NBILL_TB_CANCEL", "Cancel");
define("NBILL_TB_PREVIEW_HTML", "Preview HTML");
define("NBILL_TB_PREVIEW_PDF", "Preview PDF");
define("NBILL_TB_GENERATE_ALL", "Generate All");
define("NBILL_TB_GENERATE", "Generate");
define("NBILL_TB_ACTIVATE", "Activate");
define("NBILL_TB_SELECT_ITEM_TO_DELETE", "Please select the item(s) you want to delete from the list");
define("NBILL_TB_DELETE_ARE_YOU_SURE", "Are you sure you want to delete selected item(s)?");
define("NBILL_TB_DELETE_CONTACTS_TOO", " NOTE: This will also delete any related contact records, unless they are also being used elsewhere.");
define("NBILL_TB_SELECT_ITEM_TO_EDIT", "Please select the item you want to edit from the list");
define("NBILL_TB_SELECT_ITEM_TO_GENERATE", "Please select the order you want to generate invoices for from the list");
define("NBILL_TB_SELECT_ITEM_TO_PREVIEW", "Please select the item you want to preview from the list");
define("NBILL_TB_BACK", "Back");
define("NBILL_TB_SAVE_COPY", "Save Copy");
define("NBILL_TB_GENERATE_SAVE_FIRST", "Would you like to save the record first? (If you click `Cancel` you will lose any changes you may have made to this record)");
define("NBILL_TB_DELETE_OLD", "Delete Old");
define("NBILL_CLEAR_DOWN_OLD_BEFORE", "Enter the date before which you want to delete all e-mails (in the format yyyy/mm/dd).");
define("NBILL_CLEAR_DOWN_OLD_SURE", "Are you sure you want to delete all e-mails before that date?");

/***************************/
/* Version 1.1.4
/* Note to translators:
/* Branding constants removed (previously lines 25 to 38)
/* NBILL_SUB_EXPIRY_MESSAGE on line 236 (in the en-GB file, previously 250) has been amended to use <br /> instead of

/* NBILL_ADMIN_IMAGE on line 85 (in the en-GB file, previously 99) has been changed to point to the front-end images folder
/***************************/

// Version 1.2.0
define("NBILL_MNU_STYLESHEET_WARNING", "WARNING! Your ADMIN template is not loading the nBill stylesheet. Everything will look wrong unless you add some code to your admin template. See %s.");
define("NBILL_MNU_STYLESHEET_WARNING_1011", "WARNING! You are using an OLD and INSECURE version of Joomla! A side-effect of this is that your ADMIN template is not loading the nBill stylesheet. Everything will look wrong unless you either upgrade Joomla (recommended), or add some code to your admin template. See %s.");
define("NBILL_LICENSE_EXPIRES_SOON", "WARNING! Your license key expires soon (%s).");
define("NBILL_LIC_EXP_ANNUAL", " If you wish to renew for another year now without losing any of the remaining time on this license key, please login at " . NBILL_BRANDING_WEBSITE . " and renew your *existing* order (rather than placing a new order).");
define("NBILL_LIC_EXP_TRIAL", " If you want to continue using " . NBILL_BRANDING_NAME . " after this date, please purchase a license key at " . NBILL_BRANDING_WEBSITE);
define("NBILL_TXN_DETAILS", "The following records were found for the given transaction ID");
define("NBILL_TXN_NOT_FOUND", "No records were found for the given transaction ID.");
define("NBILL_TXN_CLIENT", "Client");
define("NBILL_TXN_PENDING_ORDER", "Pending Order ID");
define("NBILL_TXN_FORM_TITLE", "Order Form");
define("NBILL_TXN_DATE", "Date");
define("NBILL_TXN_USERNAME", "User");
define("NBILL_TXN_RELATING_TO", "Relating To");
define("NBILL_TXN_AMOUNT", "Amount");
define("NBILL_TXN_PRODUCT", "Product Details");
define("NBILL_TXN_ORDER_NO", "Order Number");
define("NBILL_TXN_INVOICE_NO", "Invoice Number");
define("NBILL_TXN_RECEIPT_NO", "Receipt Number");
define("NBILL_TXN_TYPE_PENDING", "Pending Order");
define("NBILL_TXN_TYPE_ORDER", "Orders");
define("NBILL_TXN_TYPE_INVOICE", "Invoices");
define("NBILL_TXN_TYPE_INCOME", "Income");
define("NBILL_TXN_RECEIPT_NO_UNKNOWN", "Not Yet Defined");
define("NBILL_MNU_TX_SEARCH", "Transaction Search");
define("NBILL_MNU_TX_SEARCH_HELP", "Attempt to locate records based on a transaction ID held by your Payment Service Provider");

//Version 1.2.0 SP1
define("NBILL_ERR_COULD_NOT_CREATE_USER_REASON", "WARNING! Although the order form is set to automatically create a user record, it was not possible to create a user for this order. The following error was reported: %s");

//Version 1.2.1
define("NBILL_TEMP_NOT_WRITABLE", "PHP Temp directory not found or not writable ('%s') - invoice attachment could not be saved.");
define("NBILL_NO_WRITE_ACCESS", "PHP does not have write access to the file %s - invoice attachment could not be saved.");
define("NBILL_INVALID_CHARS_IN_FIELD", "%s contains invalid characters. Please only use alphanumeric characters for this field.");
define("NBILL_INVALID_CHARS_USERNAME", "Username");
define("NBILL_USERNAME_EXISTS", "Sorry, this Username is already in use.");
define("NBILL_USER_EMAIL_EXISTS", "Sorry, this e-mail address is already in use.");
define("NBILL_EMAIL_INVALID", "The e-mail address you entered is not valid.");
define("NBILL_NO", "No");
define("NBILL_YES", "Yes");

//Version 1.2.3
define("NBILL_OFFLINE", "Offline");
// Note to translators:
//Line 90 of original en-GB language file amended (NBILL_ERR_SERIOUS_ERROR_NOMAIL) - if database not available, full error reports are now logged to the PHP error log

//Version 1.2.4
define("NBILL_INSTALL_ERROR", "Sorry, it looks like nBill failed to install correctly! Try setting the database connection type to MySQL instead of MySQLi (you can do this by editing the file %s and changing line 71 from 'public static \$mysql = false'; to 'public static \$mysql = true;'). If you are using Joomla 1.5 or above and have moved the Joomla configuration.php file to a different location, you must enter the location of that file in the " . NBILL_BRANDING_NAME . " configuration file (%s). Otherwise, please try uninstalling and re-installing. If that does not help, please refer to the troubleshooting section of the documentation at <a href=\"http://" . NBILL_BRANDING_DOCUMENTATION . "\">" . NBILL_BRANDING_DOCUMENTATION . "</a>.<br /><br /><a href=\"%s\">Return to Home Page</a>");

//Version 1.2.9
define("NBILL_RENEW_NOW", "Click here to RENEW NOW");

//Version 2.0.0
define("NBILL_PAGE_START", "&laquo; Start");
define("NBILL_PAGE_PREVIOUS", "&lsaquo; Previous");
define("NBILL_PAGE_NEXT", "Next &rsaquo;");
define("NBILL_PAGE_END", "End &raquo;");
define("NBILL_DISPLAY", "Display: ");
define("NBILL_RESULTS_PER_PAGE", "results per page.");
define("NBILL_RESULTS_SHOWING", "Currently showing records %s to %s (of %s)");
define("NBILL_TB_SELECT_ITEM_TO_IMPORT", "Please make a selection from the list to import");
define("NBILL_NOT_IN_DEMO_MODE", "This feature is not available in demo mode");
define("NBILL_MOVE_UP", "Move Up");
define("NBILL_MOVE_DOWN", "Move Down");
define("NBILL_MIGRATE", NBILL_BRANDING_NAME . " has detected the presence of tables for " . NBILL_BRANDING_NAME . " 1.2.x in your database. <a href=\"%s\" onclick=\"%s\">%s</a> to migrate this data into " . NBILL_BRANDING_NAME . " %s<br /><br /><span style=\"color:#ff0000\">NOTE:</span> It might take a long time to migrate the data.");
define("NBILL_MIGRATE_DELETE_WARNING", "WARNING! This will PERMANENTLY DELETE all of the data currently stored in " . NBILL_BRANDING_NAME . " %s. It will not affect the data held in the old " . NBILL_BRANDING_NAME . " 1.2.x tables.");
define("NBILL_MIGRATE_NO_UPGRADE_SCRIPT", "Migration FAILED. The migration script %s could not be found. No action has been taken.");
define("NBILL_MIGRATE_SUCCESS", "Data migrated successfully. Please run an %s to check the integrity of your data (an anomaly report is a new feature which looks for possible mistakes or inconsistencies in your records).");
define("NBILL_MIGRATE_SUCCESS_WITH_GUESSES", "Data migrated successfully, HOWEVER, " . NBILL_BRANDING_NAME . " had to guess the breakdown of tax for the nominal ledger on one or more transactions. The affected transactions are listed on the home page (they will be listed every time you access the home page until you confirm them). In addition to checking the item(s) listed, please run an %s to check the integrity of your data (an anomaly report is a new feature which looks for possible mistakes or inconsistencies in your records).");
define("NBILL_MIGRATE_DB_ERRORS", "The migration was attempted, but one or more database errors occurred. The errors are displayed below (error message may be truncated if it is very long).");
define("NBILL_MIGRATE_BAIL_OUT", "The migration was paused because the time taken to process the migration was getting close to the PHP time limit. Not all of the data has been migrated yet. To continue with the migration, please %s.");
define("NBILL_CFG_MIGRATE_DATA", "Initialising Data Migration (Step 1 of 5)");
define("NBILL_CFG_MIGRATE_ADDRESS", "Migrating Address and Tax Data (Step 2 of 5)");
define("NBILL_CFG_MIGRATE_TXS", "Migrating Transaction Data (Step 3 of 5)");
define("NBILL_CFG_MIGRATE_LEDGER", "Migrating Ledger Data (Step 4 of 5)");
define("NBILL_CFG_MIGRATE_FINISH", "Finishing Migration (Step 5 of 5)");
define("NBILL_MIGRATE_ABORT", "Abort");
define("NBILL_MIGRATE_ABORT_SURE", "Are you sure you want to abort the data migration process? The database might not contain all of the data from your old installation if you do, and this could lead to unpredictable behaviour and program errors.");
define("NBILL_MIGRATE_ABORTING", "Aborting...");
define("NBILL_MIGRATE_ABORTED", "Data migration was aborted");
define("NBILL_MIGRATE_RETRY", "The data migration process did not complete. To try again from where it left off, <a href=\"#\" onclick=\"%s;return false;\">Click Here</a>.");
define("NBILL_MNU_SNAPSHOT", "Snapshot Report");
define("NBILL_MNU_SNAPSHOT_HELP", "Show details of income, expenditure, and amounts owed as of a given date");
define("NBILL_MNU_ANOMALY", "Anomaly Report");
define("NBILL_MNU_ANOMALY_HELP", "Show details of any records that appear to be out of the ordinary");
define("NBILL_MNU_RECONCILE", "Reconciliation");
define("NBILL_MNU_RECONCILE_HELP", "Reconcile nBill transactions with your bank statement");
define("NBILL_MNU_CONTACTS", "Contacts");
define("NBILL_MNU_CONTACTS_HELP", "Manage contacts (people who may be clients, suppliers, both, or neither)");
define("NBILL_MNU_POTENTIAL_CLIENTS", "Potential Clients");
define("NBILL_MNU_POTENTIAL_CLIENTS_DESC", "Manage Potential Clients");
define("NBILL_MNU_PROFILE_FIELDS", "Client Profile Fields");
define("NBILL_MNU_PROFILE_FIELDS_DESC", "Define the core profile fields that will appear by default on each form (can also be customised on each form individually).");
define("NBILL_MNU_QUOTE_REQUEST", "Quote Request Forms");
define("NBILL_MNU_QUOTE_REQUEST_DESC", "Manage Quotation Request Forms");
define("NBILL_MNU_QUOTES", "Quotes");
define("NBILL_MNU_QUOTES_DESC", "Manage Quotations");
define("NBILL_MNU_PAYMENT_PLANS", "Payment Plans");
define("NBILL_MNU_PAYMENT_PLANS_DESC", "Manage Payment Plans");
define("NBILL_MNU_HELP", "Help");
define("NBILL_MNU_HELP_DESC", "Information about " . NBILL_BRANDING_NAME);
define("NBILL_MNU_HELP_ABOUT", "About");
define("NBILL_MNU_HELP_ABOUT_DESC", "Copyright and version information");
define("NBILL_MNU_HELP_DOCUMENTATION", "User Guide");
define("NBILL_MNU_HELP_DOCUMENTATION_DESC", "Read the online documentation");
define("NBILL_MNU_HELP_REGISTRATION", "Registration");
define("NBILL_MNU_HELP_REGISTRATION_DESC", "Enter a new license key");
define("NBILL_MNU_HELP_SUPPORT", "Support");
define("NBILL_MNU_HELP_SUPPORT_DESC", "Get extra help");
define("NBILL_SQL_LIST", "[SQL List]");
define("NBILL_TB_UNPUBLISH_ON_FORMS", "If you would also like to unpublish (not delete) the corresponding field on all of your existing forms, click OK. To delete the field without affecting your existing forms, click Cancel.");
define("NBILL_TB_PROMOTE", "Promote");
define("NBILL_TB_SELECT_ITEM_TO_PROMOTE", "Please select the potential client(s) you want to promote from the list");
define("NBILL_TB_PROMOTE_ARE_YOU_SURE", "Are you sure you want to promote the selected record(s) to actual client(s)? (This operation cannot be undone)");
define("NBILL_TB_RESET", "Reset");
define("NBILL_TB_RESET_ARE_YOU_SURE", "This will reset your favourites to the default settings. Are you sure you want to continue?");
define("NBILL_TB_PRINTER_FRIENDLY", "Printer Friendly");
define("NBILL_IFRAMES_REQUIRED", "Your browser does not support iframes. Please switch on iframes support or use a different browser.");
define("NBILL_COPY_OF", "Copy of ");
define("NBILL_GZIP_WARNING", "WARNING! This feature will not work properly while you have gzip page compression turned on. Please turn gzip compression off in your CMS configuration.");
define("NBILL_GZIP_WARNING_URL", "WARNING! This feature will not work properly while you have gzip page compression turned on. Please turn gzip compression off in your <a href=\"%s\">CMS configuration</a>.");
define("NBILL_UNKNOWN_EMAIL_ERROR", "E-mail failed to send. The failure reason was not reported by the CMS. Check that you are using valid e-mail addresses (from and to) and that your server is capable of sending e-mails.");
define("NBILL_LOST_PASSWORD", "Lost Password?");
define("NBILL_SECURITY_IMAGE_CHANGE", "[Change letter code]");
define("NBILL_FORM_PREVIOUS", "&laquo; Previous");
define("NBILL_FORM_NEXT", "Next &raquo;");
define("NBILL_FORM_SUMMARY_INTRO", "Please review the following information to ensure it is accurate before submitting your order. If you need to go back and change anything, click on the `Previous` button.");
define("NBILL_TAX_EXEMPTION_CODE", "Tax Exemption Code");
define("NBILL_UPGRADE_FAILED_NO_FILE_LIST", "Upgrade Failed: Unable to retrieve list of changed files (did not connect, or no response returned");
define("NBILL_UPGRADE_FAILED_CONNECTED_NO_FILE_LIST", "Upgrade Failed: Unable to retrieve list of changed files (connected ok, but invalid response returned)");
define("NBILL_UPGRADE_FAILED_MESSAGE", "Upgrade Failed: Unable to retrieve list of changed files. The following error was returned:");
define("NBILL_UPGRADE_FAILED_INVALID_FILE_LIST", "Upgrade Failed: List of retrieved files could not be authenticated.");
define("NBILL_UPGRADE_FAILED_FILE_LIST_HASH_WRONG", "Upgrade Failed: The hash of the list of retrieved files did not match the hash value supplied on the list. In other words, the upgrade manifest file could not be fully authenticated and may have been tampered with.");
define("NBILL_UPGRADE_FAILED_WRONG_FILE_COUNT", "Upgrade Failed: The file list contained the wrong number of files (%s instead of the expected %s).");
define("NBILL_UPGRADE_FILE_FAILED_WRONG_FILE_HASH", "Hash of file did not match hash in manifest.");

//Controls
define("NBILL_FIELD_CALENDAR", "Calendar");
define("NBILL_FIELD_CALENDAR_CLEAR", "Clear");
define("NBILL_DOMAIN_CHECK", "Check Availability");
define("NBILL_SECURITY_IMAGE", "Please type in the letters you see:");
define("NBILL_USER_NAME", "User Name:");
define("NBILL_USER_PASSWORD", "Password:");
define("NBILL_LOGIN", "Login");
define("NBILL_ALREADY_REGISTERED", "Already Registered? Please log in.");
define("NBILL_NOT_YET_REGISTERED", "New Client? Please fill in your details below.");
define("NBILL_SUMMARY_NOTHING_TO_SHOW_ADMIN", "There is no summary to display. If this field has only just been added, you will have to click on save or apply before anything will be shown here.");
define("NBILL_SUMMARY_ORDER_DETAILS", "Order Details");
define("NBILL_SUMMARY_INVOICE_DETAILS", "Invoice Details");
define("NBILL_SUMMARY_QUOTE_DETAILS", "Quote Details");
define("NBILL_SUMMARY_QUOTE_REQUEST_DETAILS", "Quote Request Details");

//Version 2.1.0
define("NBILL_DOC_DESCRIPTION", "Description");
define("NBILL_MNU_FEES", "Fees");
define("NBILL_MNU_FEES_DESC", "Manage Fees");
define("NBILL_MNU_USER_ADMIN", "Administration");
define("NBILL_MNU_USER_ADMIN_DESC", "Set up permissions for users to access " . NBILL_BRANDING_NAME . " administration features via your website front end.");
define("NBILL_TB_QUOTE_PAID_OFFLINE", "Paid");
define("NBILL_TB_QUOTE_PAID_OFFLINE_DESC", "Click to confirm offline payment");
define("NBILL_MNU_HOUSEKEEPING", "Housekeeping");
define("NBILL_MNU_HOUSEKEEPING_DESC", "Delete old records");
define("NBILL_LANGUAGE_ENGLISH", "English");
define("NBILL_CSV_DOWNLOAD_LIST_DESC", "Download this list as a comma spearated values (CSV) file (which can be opened in a spreadsheet application such as MS Excel, or imported into another application)");
define("NBILL_CSV_COMPANY_NAME", "Organization Name");
define("NBILL_CSV_EXPORT_LIMIT_WARNING", "WARNING! CSV exports are limited to %s records. There are more than %s records on this list, so only the first %s will be exported.");
define("NBILL_LOGOUT", "Logout");
define("NBILL_FE_ADMIN_RETURN", "Return to website");
define("NBILL_FE_ADMIN_OPEN", "Open website in a new window");
define("NBILL_FE_ADMIN_WELCOME_LOGIN", "If the system administrator has enabled " . NBILL_BRANDING_NAME . " administration via the website front end, and has granted you access, please enter your username and password below.");
define("NBILL_FE_ADMIN_ACCESS_DENIED", "Access Denied");
define("NBILL_FE_ADMIN_ACCESS_DENIED_DESC", "Sorry, you do not have " . NBILL_BRANDING_NAME . " administration privileges via the website front end.");
define("NBILL_FE_ADMIN_ADMINISTRATOR", NBILL_BRANDING_NAME . " Administrator");
define("NBILL_FE_ADMIN_ADMINISTRATOR_DESC", "Access " . NBILL_BRANDING_NAME . " administrator functions (within the website template)");
define("NBILL_FE_ADMIN_ADMINISTRATOR_FULL_DESC", "Access " . NBILL_BRANDING_NAME . " administrator functions");
define("NBILL_FE_ADMIN_ADMINISTRATOR_NEW", " (Opens in a new window)");

//Version 2.1.1
define("NBILL_PENDING_IF_PAID_ONLINE", "Only if paid online");

//Version 2.2.0
define("NBILL_POST_REMOTE_ERROR", "An error occurred whilst trying to connect to %s");
define("NBILL_DB_UPGRADE_ERRORS", NBILL_BRANDING_NAME . " has been upgraded, but one or more database errors occurred during the upgrade process. The errors are displayed below.");
define("NBILL_LIC_TYPE_H", "Hosted Client");
define("NBILL_NO_EXPIRY_NETSHINE", "No expiry while hosted.");

//Version 2.3.0
define("NBILL_MNU_SUPPORTING_DOCS", "Supporting Documents");
define("NBILL_MNU_SUPPORTING_DOCS_DESC", "Manage Supporting Documents (file attachments)");
define("NBILL_TB_NEW_FOLDER", "New Folder");
define("NBILL_TB_UPLOAD", "Upload File(s)");
define("NBILL_TB_REFRESH", "Refresh List");
define("NBILL_ATTACHMENTS", "Attachments");
define("NBILL_NEW_ATTACHMENT", "Add New Attachment");
define("NBILL_DETACH", "Detach");
define("NBILL_DELETE", "Delete");
define("NBILL_DETACH_SURE", "Are you sure you want to detach this file from this record? (This will not delete the file, nor the record)");
define("NBILL_DELETE_FILE_SURE", "Are you sure you want to delete the file \'%s\'?");
define("NBILL_DELETE_FILE_FAILED", "Sorry, '%s' coult not be deleted. Try using FTP or a file manager instead.");
define("NBILL_DOC_TYPE_INVOICE", "Invoice");
define("NBILL_DOC_TYPE_CREDIT", "Credit Note");
define("NBILL_DOC_TYPE_QUOTE", "Quote");

//Version 2.3.1
define("NBILL_ORDER_DATA_CORRUPTION_WARNING", "Warning! Possible data integrity corruption"); //On rare occasions, some have reported data loss on form submissions. These language elements allow for detection and reporting of such problems.
define("NBILL_ORDER_SAVE_DATA_CORRUPTION", "There may have been some data corruption whilst attempting to save a pending order. The order details are as follows (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values):");
define("NBILL_ORDER_LOAD_DATA_CORRUPTION", "There may have been some data corruption whilst attempting to load a pending order. The order details are as follows (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values) (please check to make sure nothing is missing from the saved values):");

//Version 2.4.0
define("NBILL_SECTION_DISCOUNT", "Section Discount");
define("NBILL_SECTION_OTHER", "Other");
define("NBILL_MNU_TRANSLATION", "Translation");
define("NBILL_MNU_TRANSLATION_DESC", "Manage multi-language translation of admin-supplied content");

//Version 2.4.1
define("NBILL_DEPRECATED", "DEPRECATED - WARNING! This feature will be removed in a future version.");

//Version 2.6.0
define("NBILL_TXN_TYPE_EXPENDITURE", "Expenditure");
define("NBILL_TXN_PAYMENT_NO", "Payment Number");
define("NBILL_TXN_SUPPLIER", "Supplier");

//Version 2.7.0
define("NBILL_ADMIN_TAB_BASIC", "Basic");
define("NBILL_ADMIN_TAB_ADVANCED", "Advanced");
define("NBILL_RESPONSIVE_MENU", "%s Menu");
define("NBILL_STATIC_HELP_NONE", "Sorry, no help available for this setting - hopefully it is self-explanatory!");

//Version 3.0.0
define("NBILL_DAYS", "day(s)");
define("NBILL_WEEKS", "week(s)");
define("NBILL_MONTHS", "month(s)");
define("NBILL_YEARS", "year(s)");
define("NBILL_AJAX_GENERAL_ERROR", "Sorry! An error occurred. Please refresh the page and try again.");
define("NBILL_RANGE_CURRENT_MONTH", "Current Month");
define("NBILL_RANGE_PREVIOUS_MONTH", "Previous Month");
define("NBILL_RANGE_CURRENT_AND_PREVIOUS_MONTH", "Current and Previous Month");
define("NBILL_RANGE_CURRENT_QUARTER", "Current Quarter");
define("NBILL_RANGE_PREVIOUS_QUARTER", "Previous Quarter");
define("NBILL_RANGE_CURRENT_AND_PREVIOUS_QUARTER", "Current and Previous Quarter");
define("NBILL_RANGE_CURRENT_YEAR", "Current Year");
define("NBILL_RANGE_PREVIOUS_YEAR", "Previous Year");
define("NBILL_RANGE_CURRENT_AND_PREVIOUS_YEAR", "Current and Previous Year");
define("NBILL_RANGE_SPECIFIED", "Specified Date Range");
define("NBILL_WARNING_EMAIL_DISABLED", "WARNING! E-mails are currently disabled. " . NBILL_BRANDING_NAME . " will not send any e-mails under any circumstances. To turn e-mails back on, go to the <a href=\"%s\">Global Configuration</a> page.");
define("NBILL_DEFAULT_VAT_NAME", "Value Added Tax");
define("NBILL_DEFAULT_VAT_ABBREVIATION", "VAT");
define("NBILL_DEFAULT_VAT_TAX_REF_DESC", "VAT Number");
define("NBILL_MNU_DASHBOARD", "Dashboard");
define("NBILL_REMOTE_POST_ERROR", "Sorry, an error occurred whilst posting the form. Please contact us for assistance.");
define("NBILL_PAYMENT_PLAN", "Payment Plan");
define("NBILL_INSTR_PAYMENT_PLAN", "If a one-off amount is due, select which payment plan to implement when this order is paid for (NOTE: not all payment gateways support payment plans)");
define("NBILL_OLD_VERSION_CHECKER", "WARNING! Your branding file is set to look up the latest version number using an old version checker for nBill v2. Please check for new releases manually, or update your branding file.");
define("NBILL_SELECTED_PAYMENT_GATEWAY", "Payment Gateway");

//Version 3.0.5
define("NBILL_3_INSTALL_ERROR", "Sorry, it looks like nBill failed to install correctly! Please try uninstalling and re-installing. If that does not help, please refer to the troubleshooting section of the documentation at <a href=\"http://" . NBILL_BRANDING_DOCUMENTATION . "\">" . NBILL_BRANDING_DOCUMENTATION . "</a>.<br /><br /><a href=\"%s\">Return to Home Page</a>");

//Version 3.0.6
define("NBILL_ERROR_MESSAGE", "Error Message: ");
define("NBILL_EMAIL_DOWNLOADABLE_DEFAULT_MESSAGE", "Thank you for your order. Please find your file(s) attached.\n\nRegards,\n%s");

//Version 3.1.0
define("NBILL_ADDRESS_BILLING", "Billing Address");
define("NBILL_ADDRESS_SHIPPING", "Shipping Address");
define("NBILL_ADDRESS_SAME_AS_BILLING", "Same as Billing Address");
define("NBILL_SHIPPING_ADDRESS_1", "Shipping Address 1");
define("NBILL_SHIPPING_ADDRESS_2", "Shipping Address 2");
define("NBILL_SHIPPING_ADDRESS_3", "Shipping Address 3");
define("NBILL_SHIPPING_TOWN", "Shipping Town");
define("NBILL_SHIPPING_STATE", "Shipping State");
define("NBILL_SHIPPING_POSTCODE", "Shipping Postcode");
define("NBILL_SHIPPING_COUNTRY", "Shipping Country");
define("NBILL_INSTR_SHIPPING_ADDRESS_ID", "Enter the shipping address that will appear on delivery notes (if applicable).");
define("NBILL_SHIPPING_ADDRESS_ID", "Shipping Address");