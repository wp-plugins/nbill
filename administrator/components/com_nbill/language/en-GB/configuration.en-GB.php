<?php
/**
* Language file for the Global Configuration page
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Configuration
define("NBILL_CONFIG_TITLE", "Configuration");
define("NBILL_CFG_ERROR_EMAIL", "Error e-mail");
define("NBILL_CFG_DATE_FORMAT", "Date Format");
define("NBILL_CFG_LIST_USERS", "List Users on Client Detail Page");
define("NBILL_CFG_INSTR_ERROR_EMAIL", "If any program errors occur, a notification will be sent to this address. NOTE: The e-mail containing the error report may contain potentially sensitive configuration information about your website/server. If you leave this blank, no notification will be sent anywhere. All errors are logged to your database regardless (but error records are deleted from the database after 2 weeks).");
define("NBILL_CFG_INSTR_DATE_FORMAT", "Specify the date format to use on invoices (at present, only numeric date formats are supported. d=day, m=month, Y=year eg. UK: \"d/m/Y\"   US: \"m/d/Y\"   International: \"Y/m/d\").");
define("NBILL_CONFIG_UPDATED", "The configuration details have been updated.");
define("NBILL_CFG_INSTR_LIST_USERS", "Whether or not to display a list of users for you to select from on the Client Details page (to allow you to tie up your clients with user records) - if you have tens of thousands of users, set this to no to avoid a long delay while the page loads (if set to no, you can still specify a user record by typing in the user id directly).");
define("NBILL_CFG_DATABASE_FUNCTIONS", "Database Functions");
define("NBILL_CFG_INSTR_DATABASE_FUNCTIONS", "Clear down or delete " . NBILL_BRANDING_NAME . " database tables, or migrate data from version 1.2.x. Clearing down the tables will delete all of your data, and allow you to start with a clean slate (you will need to re-enter your license key). Delete tables before uninstalling the component to completely remove the component from your system. DO NOT delete the tables if you want to upgrade to the latest version of " . NBILL_BRANDING_NAME . " and keep your data! NOTE: DELETING THE TABLES WILL MAKE " . nbf_common::nb_strtoupper(NBILL_BRANDING_NAME) . " INOPERABLE. After deleting, you must uninstall the component as it will no longer work. Migrating data from version 1.2.x will delete all existing data and copy the data from version 1.2.x instead (this might take some time).");
define("NBILL_CFG_CLEAR_TABLES", "Clear Down Tables");
define("NBILL_CFG_DELETE_TABLES", "Delete Tables");
define("NBILL_CFG_CONFIRM_CLEAR", "Are you sure you want to permanently delete all of your data? (if you go ahead, you will need to re-enter your license key)");
define("NBILL_CFG_CONFIRM_DELETE", "WARNING! This will stop " . NBILL_BRANDING_NAME . " from working! Are you sure you want to permanently DELETE all of the " . NBILL_BRANDING_NAME . " tables?");
define("NBILL_CFG_TABLES_CLEARED", "Tables Cleared. All of your data has been deleted. You will have to re-enter your license key to continue using " . NBILL_BRANDING_NAME . ".");
define("NBILL_CFG_TABLES_DELETED", "All " . NBILL_BRANDING_NAME . " tables have been deleted. To complete uninstallation, please refer to the %s");
define("NBILL_CFG_UNINSTALLER", "Component Installer");
define("NBILL_CFG_CRON_TOKEN", "CRON authorisation token");
define("NBILL_CFG_INSTR_CRON_TOKEN", "Enter any word here (no punctuation please!) and set the same word in your CRON script to give the script authorisation to run (eg. for automatically generating invoices on a daily basis). This is to prevent unauthorised scripts from accessing " . NBILL_BRANDING_NAME . ". See " . NBILL_BRANDING_WEBSITE . " for more information.");
define("NBILL_CFG_LICENSE_KEY", "License Key");
define("NBILL_CFG_INSTR_LICENSE_KEY", "If you wish to update your license key before it expires, you can enter a new one here. WARNING! If you enter an incorrect value here, it could prevent you from using " . NBILL_BRANDING_NAME . "! Only enter a valid license key, and copy it EXACTLY. By entering a license key, you are confirming that you understand and accept the %s");
define("NBILL_CFG_EULA", "End User License Agreement");
define("NBILL_CFG_VERSION_AUTO_CHECK", "Check for new versions?");
define("NBILL_CFG_AUTO_UPDATE", "Automatically upgrade?");
define("NBILL_CFG_INSTR_VERSION_AUTO_CHECK", "Whether or not to automatically check for a new version of the component whenever you load the admin home page. No personal information is sent anywhere.");
define("NBILL_CFG_INSTR_AUTO_UPDATE", "Whether or not to automatically upgrade your software if possible whenever a new version is released. Only applicable if 'Check for new versions' is set to 'yes'. If this option is set to 'no', you will just be prompted on the home page that a new version is available, but the new version will not be downloaded or installed.");
define("NBILL_CFG_CHECK_VERSION", "Check for Updates");
define("NBILL_CFG_INSTR_CHECK_VERSION", "Click the button to check now whether a newer version of the component is available.");
define("NBILL_CFG_BTN_CHECK_VERSION", "Check Now");
define("NBILL_CFG_UPDATE_NOW", "Upgrade Component");
define("NBILL_CFG_INSTR_UPDATE_NOW", "Click the button to upgrade to the latest version now (if applicable).");
define("NBILL_CFG_BTN_UPDATE_NOW", "Upgrade Now");

/* Version 1.1.4 */
/*define("NBILL_CFG_LIST_START_DATE", "Default Start Date for Lists");
define("NBILL_CFG_INSTR_LIST_START_DATE", "Indicate how you want the system to select the start date for any lists that are governed by a date range (eg. orders, invoices) when no date has been specifically selected. WARNING! If you select to show a large number of records (eg. 5 years, or all), this could slow down the display of the lists.");
define("NBILL_CFG_START_DATE_CURRENT_ONLY", "Show current month only");
define("NBILL_CFG_START_DATE_CURRENT_LAST", "Show current and previous month");
define("NBILL_CFG_START_DATE_QUARTER", "Show up to 3 months");
define("NBILL_CFG_START_DATE_SEMI", "Show up to 6 months");
define("NBILL_CFG_START_DATE_YEAR", "Show up to a year");
define("NBILL_CFG_START_DATE_FIVE", "Show up to 5 years");
define("NBILL_CFG_START_DATE_ALL", "Show ALL items");*/

//Version 1.2.1
define("NBILL_CFG_SWITCH_TO_SSL", "Switch to SSL on login?");
define("NBILL_CFG_INSTR_SWITCH_TO_SSL", "Whether or not to switch to https after a user logs in using any login box within " . NBILL_BRANDING_NAME . " (will only affect login boxes that are output by " . NBILL_BRANDING_NAME . "). WARNING! You must have an SSL certificate installed for this to work.");

//Version 1.2.3 - Note to translators:
//Line 29 of original en-GB language file amended (NBILL_CFG_INSTR_DATE_FORMAT) - date formats currently restricted to numeric

//Version 2.0.0
define("NBILL_CFG_NO_TX_ID_SPECIFIED", "No transaction ID was specified.");
define("NBILL_CFG_ORDER_ASSOCIATED", "The selected order is now associated with this transaction");
define("NBILL_CFG_PREV_MATCH_DISCARDED", " and the match previously selected by " . NBILL_BRANDING_NAME . " has been discarded.");
define("NBILL_CFG_CLOSEST_MATCH_ACCEPTED", "The closest match was accepted. ");
define("NBILL_CFG_NO_ACTION", "No action taken.");
define("NBILL_CFG_ASSOCIATION_REMOVED", "The transaction is no longer associated with any order. Future transactions with this ID will generate a warning prompting you to select the correct order again.");
define("NBILL_CFG_FORM_TAMPERING", "The form seems to have been tampered with (security hash does not match values). No action has been taken.");
define("NBILL_CFG_TX_CHANGE_INTRO", "%s was unable to determine which order a transaction for %s should be allocated to. Based on the amount and date, the closest match was selected (this match is shown selected in the first dropdown list below by default). If this is not correct, please select the correct order from the first dropdown list. If you are certain that the correct order is not listed in the first dropdown list, you may select another order with which to associate this transaction, or opt not to associate the transaction with any order.");
define("NBILL_CFG_SELECT_ASSOCIATION", "You may select an order with which to associate this transaction, or opt not to associate the transaction with any order.");
define("NBILL_CFG_ASSOC_WARNING", "NOTE: Selecting an order will cause all future transactions with the same ID number (%s) to be assigned to that order as well.");
define("NBILL_CFG_AMEND_ASSOC_TITLE", "Amend Order Transaction Association");
define("NBILL_CFG_INDICATE_ACTION", "Indicate what action to take:");
define("NBILL_CFG_UNALLOCATED", "Assign transaction to an unallocated order:");
define("NBILL_CFG_OTHER_ORDER", "Assign transaction to another order:");
define("NBILL_CFG_NOT_RECOMMENDED", "(not recommended unless you are absolutely certain!)");
define("NBILL_CFG_NO_SAVE_ON_DEMO", "Sorry, you cannot save the global configuration page on the demo version.");
define("NBILL_CFG_MIGRATE_1_2", "Migrate from v1.2.x");
define("NBILL_CFG_FTP_ADDRESS", "FTP Address");
define("NBILL_CFG_INSTR_FTP_ADDRESS", "If you want to allow " . NBILL_BRANDING_NAME . " to upgrade itself using FTP (ie. where the files are owned by your FTP username rather than the user PHP runs under), enter the FTP connection details here. This is not necessary if your server is running suPHP. <strong>NOTE: If you use Joomla 1.5, you should set the FTP details in the Joomla configuration page instead of here</strong> (as long as the details are valid, they will be picked up by " . NBILL_BRANDING_NAME . " automatically, and cannot then be overwritten here).");
define("NBILL_CFG_FTP_PORT", "FTP Port");
define("NBILL_CFG_INSTR_FTP_PORT", "The port to use when connecting via FTP (default is 21)");
define("NBILL_CFG_FTP_USERNAME", "FTP Username");
define("NBILL_CFG_INSTR_FTP_USERNAME", "The username to use when connecting via FTP");
define("NBILL_CFG_FTP_PASSWORD", "FTP Password");
define("NBILL_CFG_INSTR_FTP_PASSWORD", "The password to use when connecting via FTP");
define("NBILL_CFG_FTP_ROOT", "FTP Root Folder");
define("NBILL_CFG_INSTR_FTP_ROOT", "The full path to the root folder where the above credentials allow access. For example, if the full path to your website is /home/user/public_html/cms, the FTP root would typically be /home/user/. Make sure you include a trailing slash, if applicable.");
define("NBILL_CFG_TEST_FTP", "Test Connection");
define("NBILL_CFG_FTP_CONNECT_SUCCESSFUL", "FTP Connection Successful");
define("NBILL_CFG_FTP_CONNECT_FAILED", "FTP Connection Failed");
define("NBILL_CFG_FTP_LOGIN_FAILED", "Connected to FTP server ok, but login failed");
define("NBILL_CFG_FTP_OK_BUT_NBILL_NOT_FOUND", "FTP Connection and login was successful, but " . NBILL_BRANDING_NAME . " was not found at that location");
define("NBILL_CFG_FTP_OK_BUT_FILE_NOT_WRITTEN", "FTP Connection, login, and folder navigation successful, but unable to write to the files (it could be your firewall blocking access, or this user might not have write access to the files");
define("NBILL_CFG_FTP_WRITE_OK_NO_READ", "FTP Connection, login, and folder navigation successful, but unable to verify that files can be written to successfully - the user might not have read access to the files");
define("NBILL_CFG_FTP_TRY_AGAIN", "Please amend the FTP connection details on this page, save the changes, and try again.");

//Version 2.1.0
define("NBILL_CFG_DEFAULT_USER_GROUP", "Default User Group");
define("NBILL_CFG_INSTR_DEFAULT_USER_GROUP", "Default user group to which new users should be assigned when users are automatically created by " . NBILL_BRANDING_NAME . ".");
define("NBILL_INSTR_EMAIL_INVOICE_OPTIONS", "Specify the default invoice notification method when new invoices are generated. These values can be overridden for individual clients. If the default is set to send an e-mail to the client (either a notification or the actual invoice itself), the component front-end will allow the client to opt-out of this. If both the default setting against the vendor record AND the overriding value held against the client record both stipulate that no e-mail should be sent, the user will not be given the option to opt-in. <strong>Note:</strong> If sending automated e-mails, it is highly recommended to ensure that all client records have an associated user record so that they can log into the website front end to set their preferences and view their invoices. <strong>Also Note:</strong> Generating PDFs uses a lot of system resources - it is recommended to avoid this as a default, and only set it for those clients that really want it.");
define("NBILL_CFG_ALL_PAGES_SSL", "Use SSL for ALL pages?");
define("NBILL_CFG_INSTR_ALL_PAGES_SSL", "Force https for ALL " . NBILL_BRANDING_NAME . " pages in the website front end (note: affects pages output by " . NBILL_BRANDING_NAME . " only)");

//Version 2.2.0
define("NBILL_CFG_LOCALE", "Locale");
define("NBILL_CFG_INSTR_LOCALE", "You can optionally enter a locale setting to control the formatting of numbers and processing of text. The value you use depends on your operating system. If you are not sure of the exact value to use, you can list several possible values separated by commas, and the first one that works will be used. For example, a German locale setting might look like this: 'de_DE.UTF-8, de_DE.UTF-8@euro, de_DE@euro, de_DE, de, ge, deu_deu, deu, deutsch, german, German_Germany, German_Germany.1252' (without the quote marks). NOTE: This setting will only take effect if the specified locale is installed on your server and the user that PHP runs under has permission to change the locale, so it will not work in every case.");
define("NBILL_CFG_TABLES_CLEARED_ERR", NBILL_BRANDING_NAME . " attempted to re-build its database tables, but one or more errors occurred. The error(s) reported are listed below.");

//Version 2.3.1
define("NBILL_CFG_DEFAULT_MENU_ITEM", "Default Menu Itemid");
define("NBILL_CFG_INSTR_DEFAULT_MENU_ITEM", "Itemid of the menu item to use as the default for links output by nBill when no particular menu item was used to access the page. This can be used to control which modules get displayed by Joomla! - the Itemid value for each menu item can be found on the far right of the list of menu items.");
define("NBILL_CFG_REDIRECT_TO_ITEMID", "Redirect to Itemid?");
define("NBILL_CFG_INSTR_REDIRECT_TO_ITEMID", "If a front-end page is accessed without an Itemid parameter, this setting allows you to specify that the visitor should be redirected to the equivalent page with the default Itemid. Only takes effect if a default Itemid is specified, above. This setting is experimental and should be used with caution.");

//Version 2.4.0
define("NBILL_CFG_TITLE_COLOUR", "Document Title Colour");
define("NBILL_CFG_INSTR_TITLE_COLOUR", "Colour to use for titles, totals, and links on documents (invoices, credit notes, quotes)");
define("NBILL_CFG_HEADING_BG_COLOUR", "Document Heading Background Colour");
define("NBILL_CFG_INSTR_HEADING_BG_COLOUR", "Colour to use for the background of table headings on documents (invoices, credit notes, quotes)");
define("NBILL_CFG_HEADING_FG_COLOUR", "Document Heading Foreground Colour");
define("NBILL_CFG_INSTR_HEADING_FG_COLOUR", "Colour to use for the text of table headings on documents (invoices, credit notes, quotes)");

//Version 3.0.0
define("NBILL_CFG_PRECISION_DECIMAL", "Decimal Precision");
define("NBILL_CFG_INSTR_PRECISION_DECIMAL", "Numer of decimal places to use when handling general numeric values (maximum 6).");
define("NBILL_CFG_PRECISION_QUANTITY", "Quantity Precision");
define("NBILL_CFG_INSTR_PRECISION_QUANTITY", "Number of decimal places to use for quantities (maximum 6).");
define("NBILL_CFG_PRECISION_TAX_RATE", "Tax Rate Precision");
define("NBILL_CFG_INSTR_PRECISION_TAX_RATE", "Number of decimal places to use for tax rates (maximum 6).");
define("NBILL_CFG_PRECISION_CURRENCY", "Currency Precision");
define("NBILL_CFG_INSTR_PRECISION_CURRENCY", "Number of decimal places to use for general currency output (maximum 6).");
define("NBILL_CFG_PRECISION_CURRENCY_LINE_TOTAL", "Currency Line Total Precision");
define("NBILL_CFG_INSTR_PRECISION_CURRENCY_LINE_TOTAL", "Number of decimal places to use for line totals on quotes, invoices, reports, etc. (maximum 6).");
define("NBILL_CFG_PRECISION_CURRENCY_GRAND_TOTAL", "Currency Grand Total Precision");
define("NBILL_CFG_INSTR_PRECISION_CURRENCY_GRAND_TOTAL", "Number of decimal places to use for grand totals on quotes, invoices, reports, etc. (maximum 6).");
define("NBILL_CFG_THOUSANDS_SEPARATOR", "Thousands Separator");
define("NBILL_CFG_INSTR_THOUSANDS_SEPARATOR", "Custom thousands separator - if this is set to 'default', the separator from your server's current locale (or the locale setting above) will be used.");
define("NBILL_CFG_DECIMAL_SEPARATOR", "Decimal Separator");
define("NBILL_CFG_INSTR_DECIMAL_SEPARATOR", "Custom decimal separator - if this is set to 'default', the separator from your server's current locale (or the locale setting above) will be used.");
define("NBILL_CFG_CURRENCY_FORMAT", "Currency Format String");
define("NBILL_CFG_INSTR_CURRENCY_FORMAT", "By default, currency output will be based on the locale specified above, or the default locale of your server, however, you can override this here using the syntax of the PHP sprintf command. Please note however, that you must also specify a matching currency precision, above. For example, to output the amount 123.4567 as AU&#36;123.457 (ie. rounded to 3 decimal places, with an AU&#36; prefix), you would need to set the currency precision (above) to 3, and this currency format string to 'AU&#36;%01.3f' (without the quotes). To output the same value as '123 Reais' (ie. rounded to an integer, with a suffix of 'Reais'), you would use 0 (zero) as the currency precision, and the following currency format string: '%01f Reais' (without quotes). As sprintf does not support using a thousands separator, you will also need to specify a custom thousands separator, above, if using this setting. These values can be overridden on each currency record.");
define("NBILL_CFG_USE_LEGACY_DOCUMENT_EDITOR", "Use Legacy Document Editor?");
define("NBILL_CFG_INSTR_USE_LEGACY_DOCUMENT_EDITOR", "Whether or not to use the old (mobile-unfriendly) line item editor for invoices, quotes, and credit notes. You cannot manually modify the new 'electronic delivery' setting using the legacy editor, so it should not be used if you charge EU VAT. This setting is deprecated and will be removed in a future version.");
define("NBILL_CFG_EDIT_PRODUCTS_IN_DOCUMENTS", "Edit Products Within Documents?");
define("NBILL_CFG_INSTR_EDIT_PRODUCTS_IN_DOCUMENTS", "When you add a new line item in a document (quote, credit note, invoice), or edit an existing line item, and a SKU (product code) is present on the line item, if this option is set to 'yes', the line item editor will ask whether you want to save changes to the underlying product record. This effectively allows you to edit your products in-situ on an invoice or quote (but the prompts can be annoying if you often modify invoices or quotes without wanting to update the product record itself!).");
define("NBILL_CFG_AUTO_CHECK_EU_VAT_RATES", "Check for EU VAT rate changes?");
define("NBILL_CFG_INSTR_AUTO_CHECK_EU_VAT_RATES", "Whether or not to automatically connect to the " . NBILL_BRANDING_NAME . " server once a day and check whether any EU VAT rates have changed so you can be informed that you need to update your tax records. This will only happen if you have at least one product, invoice, or income record flagged for 'electronic delivery'. It is recommended that you have this set to 'yes' - you should only disable it if you do not charge VAT at all or are running a test installation of " . NBILL_BRANDING_NAME . ".");
define("NBILL_CFG_API_URL_EU_VAT_RATES", "EU VAT Rates API URL");
define("NBILL_CFG_INSTR_API_URL_EU_VAT_RATES", "The URL to connect to for looking up the current VAT rates of EU countries. You should generally leave this at the default value unless you have been specifically advised to change it.");
define("NBILL_CFG_GEO_IP_LOOKUP", "Geo-IP Lookup?");
define("NBILL_CFG_INSTR_GEO_IP_LOOKUP", "Whether or not to connect to a Geo-IP lookup service to find the country of a client when they place an order using an order form or request a quote using a quote request form. This is used to automatically populate the country for guest users. Also, if you have products which are delivered electronically within the EU, you might be expected to be able to show evidence that the client really consumed the service in the country they specified (so as to avoid people fraudulently entering a country with a lower VAT rate). Having this setting enabled simply records the country code (of the IP address that was used) in your database - if you want to enforce a match with the client record, there is a separate setting for that, below.");
define("NBILL_CFG_API_URL_GEO_IP", "Geo-IP API URL");
define("NBILL_CFG_INSTR_API_URL_GEO_IP", "The URL to connect to for looking up the the country code for a given IP address. Use ##ip## as a placeholder for the actual IP address. Valid values include 'http://www.telize.com/geoip/##ip##', 'http://freegeoip.net/json/##ip##' and 'http://ip-api.com/json/##ip##'. If any of those services go offline in future for any reason, a substitute URL can be entered here.");
define("NBILL_CFG_GEO_IP_FAIL_ON_MISMATCH", "Geo-IP Fail on Mis-match?");
define("NBILL_CFG_INSTR_GEO_IP_FAIL_ON_MISMATCH", "If this is set to 'yes', the client will not be able to proceed with an order or quote request unless the country of their IP address matches the country of their client record.");
define("NBILL_CFG_DISABLE_EMAIL", "Disable E-Mail?");
define("NBILL_CFG_INSTR_DISABLE_EMAIL", "Whether or not to completely stop " . NBILL_BRANDING_NAME . " (and only " . NBILL_BRANDING_NAME . ") from sending e-mails (useful for testing). " . NBILL_BRANDING_NAME . " will continue to behave as though e-mails have been sent succesfully (it will not report any errors), even though no e-mails have been sent.");
define("NBILL_CFG_TIMEZONE", "Timezone");
define("NBILL_CFG_INSTR_TIMEZONE", "In most cases you should leave this blank, and the value from your php.ini file will be used. If your php.ini file does not have a valid timezone specified, and you cannot update it, or you wish to override the value in php.ini, you can enter a PHP timezone string here (eg. 'Europe/London' or 'America/Chigaco'). See www.php.net/timezones for a full list.");
define("NBILL_CFG_DEFAULT_ELECTRONIC", "Default to Electronic Delivery?");
define("NBILL_CFG_INSTR_DEFAULT_ELECTRONIC", "Whether or not to default new products, invoice line items, quote line items, income, and expenditure records to being marked as electronic deliveries. Digital goods and services sold within the EU have to be flagged as electronic deliveries and must apply VAT according to the VAT rate of the country where the customer is located (check with your local tax authority to determine whether any particular product or service you supply comes under this regulation, but it would generally included things like downloadable products, website hosting, telecommunications, and broadcasting services). If all or most of your products are digital, and you supply to the EU, you should set this to 'yes'. This will NOT have any effect on any existing records. Any existing products, invoice line items, quote line items, income, or expenditure records that relate to digitally supplied products will need to be updated manually and flagged as electronic delivery.");

//Version 3.0.5
define("NBILL_CFG_NEVER_HIDE_QUANTITY", "Always show quantity on documents?");
define("NBILL_CFG_INSTR_NEVER_HIDE_QUANTITY", "By default, quantity is not shown on invoices/quotes if the value is exactly 1 (one). If you want to show quantity (number of units) regardless of whether the quantity is 1 or not, set this to '" . NBILL_YES . "'.");

//Version 3.1.0
define("NBILL_CFG_NEGATIVE_IN_BRACKETS", "Negative in Brackets?");
define("NBILL_CFG_INSTR_NEGATIVE_IN_BRACKETS", "Whether or not to show negative numbers in brackets. If this is set to '" . NBILL_NO . "', the minus symbol (-) will be used instead.");
define("NBILL_CFG_INSTR_DATABASE_FUNCTIONS_LITE", "Clear down or delete " . NBILL_BRANDING_NAME . " database tables, or migrate data from version 1.2.x. Clearing down the tables will delete all of your data, and allow you to start with a clean slate. Delete tables before uninstalling the component to completely remove the component from your system. DO NOT delete the tables if you want to upgrade to the latest version of " . NBILL_BRANDING_NAME . " and keep your data! NOTE: DELETING THE TABLES WILL MAKE " . nbf_common::nb_strtoupper(NBILL_BRANDING_NAME) . " INOPERABLE. After deleting, you must uninstall the component as it will no longer work. Migrating data from version 1.2.x will delete all existing data and copy the data from version 1.2.x instead (this might take some time).");
define("NBILL_CFG_CONFIRM_CLEAR_LITE", "Are you sure you want to permanently delete all of your data?");
define("NBILL_CFG_TABLES_CLEARED_LITE", "Tables Cleared. All of your data has been deleted.");