<?php
/**
* Language file for Vendors
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Vendors
define("NBILL_VENDOR_INTRO", "Typically, you would just have your own company details listed here. If you run more than one company or are a bookkeeper for several companies, you can add further company details. All invoicing records are held separately for each vendor.");
define("NBILL_VENDOR_DETAILS", "Vendor Details");
define("NBILL_NEW_VENDOR", "New Vendor");
define("NBILL_EDIT_VENDOR", "Edit Vendor Details");
define("NBILL_VENDORS_TITLE", "Vendors");
define("NBILL_VENDOR_COUNTRY", "Vendor Country");
define("NBILL_VENDOR_CURRENCY", "Vendor Default Currency");
define("NBILL_NEXT_NBILL_NO", "Next Invoice Number");
define("NBILL_NEXT_ORDER_NO", "Next Order Number");
define("NBILL_NEXT_RECEIPT_NO", "Next Receipt Number");
define("NBILL_NEXT_PAYMENT_NO", "Next Payment Number");
define("NBILL_NEXT_CREDIT_NO", "Next Credit Note Number");
define("NBILL_NEXT_QUOTE_NO", "Next Quote Number");
define("NBILL_DEFAULT_PAYMENT_INSTR", "Default Payment Instructions");
define("NBILL_DEFAULT_SMALL_PRINT", "Default Small Print");
define("NBILL_ADMIN_EMAIL", "Admin e-mail");
define("NBILL_PAPER_SIZE", "Paper Size");
define("NBILL_CLIENT_ORDERING", "Allow Clients to Place Orders?");
define("NBILL_PUBLIC_ORDERING", "Allow the public to Place Orders?");
define("NBILL_SHOW_EMPTY_CATS", "Show Empty Categories?");
define("NBILL_INSTR_VENDOR_NAME", "Enter the name that you want to appear on your invoices.");
define("NBILL_INSTR_VENDOR_ADDRESS", "Enter the address that you want to appear on your invoices.");
define("NBILL_INSTR_VENDOR_COUNTRY", "This will only appear on automatically-generated invoices when the billing address is in a different country.");
define("NBILL_INSTR_VENDOR_CURRENCY", "Select the default currency for this vendor.");
define("NBILL_INSTR_NEXT_INVOICE_NO", "Enter the next invoice number to be generated (you can have alpha-numeric characters at the start, but the last character must be numeric as this will be incremented automatically).");
define("NBILL_INSTR_NEXT_ORDER_NO", "Enter the next order number to be generated (you can have alpha-numeric characters at the start, but the last character must be numeric as this will be incremented automatically).");
define("NBILL_INSTR_NEXT_RECEIPT_NO", "Enter the next receipt number to be generated (you can have alpha-numeric characters at the start, but the last character must be numeric as this will be incremented automatically).");
define("NBILL_INSTR_NEXT_PAYMENT_NO", "Enter the next payment number to be generated (you can have alpha-numeric characters at the start, but the last character must be numeric as this will be incremented automatically).");
define("NBILL_INSTR_NEXT_CREDIT_NO", "Enter the next credit note number to be generated (you can have alpha-numeric characters at the start, but the last character must be numeric as this will be incremented automatically).");
define("NBILL_INSTR_NEXT_QUOTE_NO", "Enter the next quote number to be generated (you can have alpha-numeric characters at the start, but the last character must be numeric as this will be incremented automatically).");
define("NBILL_INSTR_DEFAULT_PAYMENT_INSTR", "Enter your default payment instructions to appear on your invoices (this can be overridden for different countries on the Tax page)");
define("NBILL_INSTR_DEFAULT_SMALL_PRINT", "Enter your default small print to appear on your invoices (this can be overridden for different countries on the Tax page)");
define("NBILL_INSTR_ADMIN_EMAIL", "E-Mails sent to clients will appear to come from this address.");
define("NBILL_INSTR_PAPER_SIZE", "Paper size to use when producing PDFs of the invoices (valid values include A4, A5, Letter, and Legal)");
define("NBILL_INSTR_CLIENT_ORDERING", "Whether or not the component's front-end should allow logged-in users to place orders ");
define("NBILL_INSTR_PUBLIC_ORDERING", "Whether or not the component's front-end should allow users who are not logged in to place orders ");
define("NBILL_INSTR_SHOW_EMPTY_CATS", "If users can place orders through the component's front-end, this setting specifies whether or not to display categories which do not contain any products or child categories");
define("NBILL_VENDOR_NAME_REQUIRED", "You must provide a name for the vendor.");
define("NBILL_VENDOR_ADDRESS_REQUIRED", "You must specify the vendor\'s address");
define("NBILL_VENDOR_COUNTRY_REQUIRED", "You must specify the country of the vendor.");
define("NBILL_VENDOR_CURRENCY_REQUIRED", "You must specify the currency of the vendor.");
define("NBILL_VENDOR_EMAIL_REQUIRED", "You must specify the vendor\'s e-mail address.");
define("NBILL_ERR_CANNOT_DELETE_LAST_VENDOR", "You cannot delete the last Vendor!");
define("NBILL_INVOICE_NO_LOCKED", "The next invoice number for this vendor is currently locked. This should only happen temporarily whilst invoices are being generated. If the invoice generation process failed and this field has remained locked, you can unlock it by clicking this button:");
define("NBILL_ORDER_NO_LOCKED", "The next order number for this vendor is currently locked. This should only happen temporarily whilst invoices are being generated. If the invoice generation process failed and this field has remained locked, you can unlock it by clicking this button:");
define("NBILL_RECEIPT_NO_LOCKED", "The next receipt number for this vendor is currently locked. This should only happen temporarily whilst invoices are being generated. If the invoice generation process failed and this field has remained locked, you can unlock it by clicking this button:");
define("NBILL_PAYMENT_NO_LOCKED", "The next payment number for this vendor is currently locked. This should only happen temporarily whilst invoices are being generated. If the invoice generation process failed and this field has remained locked, you can unlock it by clicking this button:");
define("NBILL_UNLOCK", "Unlock");
define("NBILL_TEMPLATES_TITLE", "Templates");
define("NBILL_TEMPLATES_INTRO", "You can use PHP/HTML templates for documents and e-mails. Document templates are stored in the %s folder, and e-mail templates are stored in %s.");
define("NBILL_INVOICE_TEMPLATE", "Invoice Template");
define("NBILL_INSTR_INVOICE_TEMPLATE", "Name of the template to use for producing invoices for this vendor.");
define("NBILL_INVOICE_EMAIL_TEMPLATE", "Invoice E-Mail Template");
define("NBILL_INSTR_INVOICE_EMAIL_TEMPLATE", "Name of the HTML template to use for e-mailing invoices to the client (if applicable - see email invoice options, below).");
define("NBILL_CREDIT_TEMPLATE", "Credit Note Template");
define("NBILL_INSTR_CREDIT_TEMPLATE", "Name of the template to use for producing credit notes for this vendor.");
define("NBILL_CREDIT_EMAIL_TEMPLATE", "Credit Note E-Mail Template");
define("NBILL_INSTR_CREDIT_EMAIL_TEMPLATE", "Name of the HTML template to use for e-mailing credit notes to the client (if applicable).");
define("NBILL_QUOTE_TEMPLATE", "Quote Template");
define("NBILL_INSTR_QUOTE_TEMPLATE", "Name of the template to use for producing quotations for this vendor.");
define("NBILL_QRC_EMAIL_TEMPLATE", "Quote Request Confirmation E-Mail Template");
define("NBILL_INSTR_QRC_EMAIL_TEMPLATE", "Name of the HTML template to use for e-mailing confirmation of quote request form submissions to the client (if applicable).");
define("NBILL_QUOTE_EMAIL_TEMPLATE", "Quote E-Mail Template");
define("NBILL_INSTR_QUOTE_EMAIL_TEMPLATE", "Name of the HTML template to use for e-mailing quotes to the client (if applicable).");
define("NBILL_PENDING_EMAIL_TEMPLATE", "Pending Order E-Mail Template");
define("NBILL_INSTR_PENDING_EMAIL_TEMPLATE", "Name of the HTML template to use for e-mailing pending order confirmation to the client (if applicable - whether or not to send an email is configurable on each order form).");
define("NBILL_ORDER_EMAIL_TEMPLATE", "Order E-Mail Template");
define("NBILL_INSTR_ORDER_EMAIL_TEMPLATE", "Name of the HTML template to use for e-mailing order confirmation to the client (if applicable - configurable on each order form).");
define("NBILL_INSTR_EMAIL_INVOICE_OPTIONS", "Specify the default invoice notification method when new invoices are generated. These values can be overridden for individual clients. If the default is set to send an e-mail to the client (either a notification or the actual invoice itself), the component front-end will allow the client to opt-out of this. If both the default setting against the vendor record AND the overriding value held against the client record both stipulate that no e-mail should be sent, the user will not be given the option to opt-in. <strong>Note:</strong> If sending automated e-mails, it is highly recommended to ensure that all client records have an associated user record so that they can log into the website front end to set their preferences and view their invoices. <strong>Also Note:</strong> Generating PDFs uses a lot of system resources - it is recommended to avoid this as a default, and only set it for those clients that really want it.");
define("NBILL_DEFAULT_GATEWAY", "Default Gateway");
define("NBILL_INSTR_DEFAULT_GATEWAY", "Default payment gateway to use for new order forms and for allowing payment of invoices online (if applicable).");
define("NBILL_AUTO_CREATE_INCOME", "Auto Create Income Item?");
define("NBILL_INSTR_AUTO_CREATE_INCOME", "Whether or not the payment gateway should be instructed to automatically create an income record for invoices that are paid online. NOTE: This does not affect orders made using an order form (you can set that on the order form in question), only standalone invoices that are paid online.");
define("NBILL_SUPPRESS_RECEIPT_NOS", "Generate Receipt Numbers Manually?");
define("NBILL_INSTR_SUPPRESS_RECEIPT_NOS", "Whether to suppress the generation of receipt numbers when income items are added, and offer a toolbar button to generate receipt numbers for all income items up to a given date instead. This is so that you can have all of your receipt numbers in date order, even if you have a mixture of auto-generated income items from online payments, and manually added income items (eg. for cheques etc. paid directly into your bank account). By setting this to 'yes', an extra toolbar button will appear on the income list page to enable you to populate the receipt numbers up to a given date, when you are ready.");
define("NBILL_VENDOR_LOGO", "Vendor Logo");
define("NBILL_INSTR_VENDOR_LOGO", "Upload a .png or .gif image to appear on invoices for this vendor (if supported by the invoice template. Must be .png or .gif, and less than 30k). NOTE: You might need to refresh your browser after uploading a new image, as some browsers (esp. firefox) cache images.");
define("NBILL_VENDOR_GIF_ONLY", "Sorry, the logo file you uploaded (%s) is not valid.  Only GIF or PNG files can be used for vendor logos");
define("NBILL_VENDOR_GIF_TOO_BIG", "Sorry, the logo file you uploaded was too large.  The maximum size is 30K");
define("NBILL_DELETE_LOGO", "Delete");
define("NBILL_VENDOR_DELETE_LOGO_FAILED", NBILL_BRANDING_NAME . " was unable to delete the logo file. This may be because PHP does not have permission to delete files on your server.");
define("NBILL_SUPPRESS_PAYMENT_NOS", "Generate Payment Numbers Manually?");
define("NBILL_INSTR_SUPPRESS_PAYMENT_NOS", "As above, but for payment numbers on the expenditure list.");
define("NBILL_ADD_REMITTANCE", "Show Remittance Advice?");
define("NBILL_INSTR_ADD_REMITTANCE", "Whether or not to add a remittance advice slip to the end of invoices.");
define("NBILL_CREDIT_SMALL_PRINT", "Credit Note Small Print");
define("NBILL_INSTR_INVOICE_SMALL_PRINT_CR", "Enter any legal disclaimers etc. that you want to appear on credit notes.");
define("NBILL_QUOTE_SMALL_PRINT", "Quote Small Print");
define("NBILL_INSTR_INVOICE_SMALL_PRINT_QU", "Enter any legal disclaimers etc. that you want to appear on quotes.");
define("NBILL_MASTER_DB_INTRO", "If you sell products or services using several different websites, each with their own copy of " . NBILL_BRANDING_NAME . ", but you want to record all of your financial information in just one of those, you can do so using a chain of 'master' databases. IMPORTANT! Please do not use this feature until you have read the documentation (available from the " . NBILL_BRANDING_NAME . " website), otherwise you could seriously mess up your data!");
define("NBILL_USE_MASTER_DB", "Use a Master Database?");
define("NBILL_INSTR_USE_MASTER_DB", "Whether or not to defer generation of numbers for invoices, credit notes, income, and expenditure to a master database (another copy of " . NBILL_BRANDING_NAME . ").");
define("NBILL_MASTER_DB_HOST", "Master Host");
define("NBILL_INSTR_MASTER_DB_HOST", "Host name of master database (eg. if it is another database on the same server, this could be 'localhost').");
define("NBILL_MASTER_USERNAME", "Master Username");
define("NBILL_INSTR_MASTER_USERNAME", "Username required to access the master database.");
define("NBILL_MASTER_PASSWORD", "Master Password");
define("NBILL_INSTR_MASTER_PASSWORD", "Password required to access the master database.");
define("NBILL_MASTER_DB_NAME", "Master Database Name");
define("NBILL_INSTR_MASTER_DB_NAME", "Name of the master database.");
define("NBILL_MASTER_TABLE_PREFIX", "Master Database Table Prefix");
define("NBILL_INSTR_MASTER_TABLE_PREFIX", "Table prefix for the master database (typically 'jos_').");
define("NBILL_MASTER_DB_CANNOT_CONNECT", "Unable to connect to master database.");
define("NBILL_MASTER_DB_TEST", "Test Connection");
define("NBILL_MASTER_VENDOR", "Master Vendor");
define("NBILL_INSTR_MASTER_VENDOR", "Corresponding Vendor record on the master database with which to synchronise");
define("NBILL_SYNCHRONISE", "Upload to Master Database");
define("NBILL_INSTR_SYNCHRONISE", "Send invoice, credit note, income, and expenditure data from this database to the master database. This will overwrite any existing data in the master database with the same invoice, credit note, income, or expenditure numbers.");
define("NBILL_SYNC_ARE_YOU_SURE", "WARNING! This will overwrite data on your master database(s). Click OK to upload the data, or Cancel to abort.");
define("NBILL_MASTER_DB_TEST_SUCCESS", "Connected to Master Database Successfully");
define("NBILL_MASTER_DB_NOT_IN_USE", "Cannot update master database - you must set 'Use Master Database' to 'Yes' before trying to upload.");
define("NBILL_SYNCHRONISATION_ERROR", "An error occurred whilst attempting to upload to the master database. Upload aborted! The error message returned was: %s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s");
define("NBILL_SYNCHRONISE_NEED_GEN", "CANNOT CONTINUE! One or more income or expenditure records (either on this database, or on a master database) has not yet been assigned a number. You must generate payment and/or receipt numbers before synchronisation (generating receipt or payment numbers on this database will also generate any outstanding on the master database(s)).");
define("NBILL_SYNC_UP_TO", "Synchronise records up to and including (yyyy/mm/dd)");
define("NBILL_SUPPRESS_GENERATION_BUTTONS", "Suppress Generation Buttons?");
define("NBILL_INSTR_SUPPRESS_GENERATION_BUTTONS", "Whether or not to suppress the display of toolbar buttons to allow payment/receipt number generation. You should ONLY set this to 'yes' if this is a master database, which has a child database on which the number generation should be performed (see master database section, below). If you set a child database to use THIS copy of " . NBILL_BRANDING_NAME . " as a master, the value will be set to 'yes' automatically. YOU THEREFORE DO NOT USUALLY NEED TO DO ANYTHING WITH THIS SETTING unless you used to have a child database pointing to this one, but no longer do (in which case you would change it from 'yes' to 'no').");
define("NBILL_ADD_PAYLINK", "Show Payment Link on Invoices?");
define("NBILL_INSTR_ADD_PAYLINK", "Whether or not to add a link to unpaid invoices generated for this vendor that allow the client to pay the invoice online.");
define("NBILL_MASTER_DB_SECTION", "Master Database");

//Version 1.2.1 - Note to translators:
//Line 74 of original en-GB language file amended (NBILL_INSTR_EMAIL_INVOICE_OPTIONS) - additional note added

//Version 1.2.7
define("NBILL_EMAIL_PDF_AVAILABILITY", "(PDF option will only be available if the <a href=\"http://www.nbill.co.uk/html2ps.html\" target=\"_blank\">HTML2PS/PDF</a> script has been uploaded)");

//Version 2.0.0 - Note to translators:
//Lines 84 and 85 of original en-GB language file amended (.png files now supported)
define("NBILL_VENDOR_DEFAULT", "Default Vendor");
define("NBILL_INSTR_VENDOR_DEFAULT", "If you have more than one vendor record, some settings on other records (eg. country) can default to the value held on the default vendor if no other value is supplied and the vendor is not known (eg. on new Client records). There should always be 1 default vendor record.");

//Version 2.0.1
define("NBILL_SYNC_VENDOR", "Synchronising Data");
define("NBILL_VENDOR_SYNC_ABORTED", "Synchronisation has been aborted. ");
define("NBILL_VENDOR_SYNC_VENDOR_TASK_INVOICES", "Uploading Invoices");
define("NBILL_VENDOR_SYNC_VENDOR_TASK_TXS", "Uploading Income/Expenditure");
define("NBILL_SYNC_SUCCESS", "Synchronisation completed successfully!");
define("NBILL_SYNC_SUCCESS_RECORD_COUNT", "%s1 Record(s) Inserted, and %s2 Record(s) Updated on the Master Database");
define("NBILL_VENDOR_SYNC_RETRY", "The vendor synchronisation process did not complete. To try again from where it left off, <a href=\"#\" onclick=\"%s;return false;\">Click Here</a>.");
define("NBILL_VENDOR_SYNC_ABORT", "Abort");
define("NBILL_VENDOR_SYNC_ABORT_SURE", "Are you sure you want to abort the vendor synchronisation process? The master database might not contain all of the data from this site if you do.");
define("NBILL_VENDOR_SYNC_ABORTING", "Aborting...");

//Version 2.1.0
define("NBILL_QUOTE_DEFAULT_INTRO", "Quote Intro");
define("NBILL_INSTR_QUOTE_DEFAULT_INTRO", "You can optionally add an HTML introduction to your quotes. The value you specify here will be used as the default value for all new quote records (but you can override it for each individual quote if you wish).");
define("NBILL_QUOTE_PAY_INST", "Quote Offline Payment Instructions");
define("NBILL_INSTR_QUOTE_PAY_INST", "If the special 'offline' payment gateway is published, you can specify here how your client can make an offline payment when paying for a quote.");

//Version 2.5.0
define("NBILL_INVOICE_PAY_INST", "Invoice Offline Payment Instructions");
define("NBILL_INSTR_INVOICE_PAY_INST", "If the special 'offline' payment gateway is published, you can specify here how your client can make an offline payment when paying for an invoice.");

//Version 3.1.0
define("NBILL_DELIVERY_TEMPLATE", "Delivery Note Template");
define("NBILL_INSTR_DELIVERY_TEMPLATE", "Name of the HTML template to use for producing delivery notes (if applicable).");
define("NBILL_DELIVERY_SMALL_PRINT", "Delivery Note Small Print");
define("NBILL_INSTR_INVOICE_SMALL_PRINT_DE", "Enter any legal disclaimers etc. that you want to appear on delivery notes.");