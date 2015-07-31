<?php
/**
* Language file for the Display Options page
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Display Options
define("NBILL_DISPLAY_OPTIONS_TITLE", "Display Options");
define("NBILL_DISPLAY_INTRO", "Most of the options and fields that are displayed through the component's front end are configurable here - you can decide which elements to display to your end users.");
define("NBILL_DISPLAY_MY_ACCOUNT", "My Account page options");
define("NBILL_DISPLAY_USER_GROUP", "Show current membership level");
define("NBILL_DISPLAY_USER_GROUP_DESC", "Whether or not to display the ACL Group name to which the user currently belongs.");
define("NBILL_DISPLAY_PROFILE", "Show link to profile");
define("NBILL_DISPLAY_PROFILE_DESC", "Whether or not to display a link to the user's profile from the main menu of the component front end.");
define("NBILL_DISPLAY_ORDERS", "Show link to orders");
define("NBILL_DISPLAY_ORDERS_DESC", "Whether or not to display a link to the user's order list from the main menu of the component front end.");
define("NBILL_DISPLAY_INVOICES", "Show link to invoices");
define("NBILL_DISPLAY_INVOICES_DESC", "Whether or not to display a link to the user's invoices from the main menu of the component front end.");
define("NBILL_DISPLAY_QUOTES", "Show link to quotes");
define("NBILL_DISPLAY_QUOTES_DESC", "Whether or not to display a link to the user's quotes from the main menu of the component front end.");
define("NBILL_DISPLAY_MORE_LINKS", "You can add up to 10 additional links which will be displayed on the main menu of the 'My Account' page in the component's front end. Enter the link details here in the order you want them displayed.");
define("NBILL_LINK", "Link");
define("NBILL_LINK_URL", "URL");
define("NBILL_LINK_TEXT", "Link Text");
define("NBILL_LINK_DESC", "Link Description");
define("NBILL_DISPLAY_MY_PROFILE", "My Profile page options");
define("NBILL_DISPLAY_COMPANY_NAME", "Show company name field");
define("NBILL_DISPLAY_COMPANY_NAME_DESC", "Whether or not to allow the user to edit their company name on the profile page.");
define("NBILL_DISPLAY_CONTACT_NAME", "Show contact name field");
define("NBILL_DISPLAY_CONTACT_NAME_DESC", "Whether or not to allow the user to edit their contact name on the profile page.");
define("NBILL_DISPLAY_CONTACT_NAME_INV", "Show 'add contact name to invoices' field");
define("NBILL_DISPLAY_CONTACT_NAME_NBILL_DESC", "Whether or not to display the checkbox that allows users to specify whether their contact name should appear on invoices (if a company name is also present).");
define("NBILL_DISPLAY_ADDRESS", "Show address fields");
define("NBILL_DISPLAY_ADDRESS_DESC", "Whether or not to allow users to update their own address details on the profile page.");
define("NBILL_DISPLAY_EMAIL", "Show e-mail address field");
define("NBILL_DISPLAY_EMAIL_DESC", "Whether or not to allow users to update their e-mail address on the profile page.");
define("NBILL_DISPLAY_WEBSITE", "Show website address field");
define("NBILL_DISPLAY_WEBSITE_DESC", "Whether or not to allow users to update their website address on the profile page.");
define("NBILL_DISPLAY_TELEPHONE", "Show telephone number field");
define("NBILL_DISPLAY_TELEPHONE_DESC", "Whether or not to allow users to update their telephone number on the profile page.");
define("NBILL_DISPLAY_MOBILE", "Show mobile (cell) phone number field");
define("NBILL_DISPLAY_MOBILE_DESC", "Whether or not to allow users to update their mobile phone (cell phone) number on the profile page.");
define("NBILL_DISPLAY_FAX", "Show fax number field");
define("NBILL_DISPLAY_FAX_DESC", "Whether or not to allow users to update their fax number on the profile page.");
define("NBILL_DISPLAY_TAX_EXEMPT", "Show tax exemption code field");
define("NBILL_DISPLAY_TAX_EXEMPT_DESC", "Whether or not to allow users to update their tax exemption code on the profile page.");
define("NBILL_DISPLAY_CURRENCY", "Show default currency field");
define("NBILL_DISPLAY_CURRENCY_DESC", "Whether or not to allow users to specify the currency in which they wish to place orders and make payments (only takes effect if the product they order is available in the specified currency).");
define("NBILL_DISPLAY_PASSWORD", "Show password field");
define("NBILL_DISPLAY_PASSWORD_DESC", "Whether or not to allow users to update their password.");
define("NBILL_DISPLAY_MY_ORDERS", "My Orders page options");
define("NBILL_DISPLAY_NEW_ORDER_LINK", "Show 'add new order' link");
define("NBILL_DISPLAY_NEW_ORDER_LINK_DESC", "Whether or not to display a link from the order list page to allow a user to add a new order.");
define("NBILL_DISPLAY_ORDER_NO", "Show order number field (recommended)");
define("NBILL_DISPLAY_ORDER_NO_DESC", "Whether or not to display the order number in the order list.");
define("NBILL_DISPLAY_INVOICE_LINK", "Show link to invoices");
define("NBILL_DISPLAY_INVOICE_LINK_DESC", "Whether or not to include a link to the invoice(s) for each order (link appears next to the order number in the order list, and therefore requires the order number to be displayed).");
define("NBILL_DISPLAY_ORDER_DATE", "Show order date");
define("NBILL_DISPLAY_ORDER_DATE_DESC", "Whether or not to display the order date in the order list.");
define("NBILL_DISPLAY_ORDER_EXPIRY_DATE", "Show expiry date");
define("NBILL_DISPLAY_ORDER_EXPIRY_DATE_DESC", "Whether or not to display the date that the order will expire in the order list.");
define("NBILL_DISPLAY_PRODUCT", "Show product name");
define("NBILL_DISPLAY_PRODUCT_DESC", "Whether or not to display the name of the product ordered in the order list.");
define("NBILL_DISPLAY_ORDER_VALUE", "Show order value");
define("NBILL_DISPLAY_ORDER_VALUE_DESC", "Whether or not to display the order value in the order list.");
define("NBILL_DISPLAY_FREQUENCY", "Show payment frequency");
define("NBILL_DISPLAY_FREQUENCY_DESC", "Whether or not to display the payment frequency in the order list.");
define("NBILL_ALLOW_CANCELLATION", "Allow cancellation");
define("NBILL_ALLOW_CANCELLATION_DESC", "Whether or not the user should be allowed to cancel an order (only applicable if the order requires regular payments, AND order is set to auto-renew, AND payment frequency is displayed).");
define("NBILL_DISPLAY_ORDER_STATUS", "Show order status");
define("NBILL_DISPLAY_ORDER_STATUS_DESC", "Whether or not to display the status of the order in the order list.");
define("NBILL_DISPLAY_MY_INVOICES", "My Invoices page options");
define("NBILL_DISPLAY_FILTER", "Show filter dropdown");
define("NBILL_DISPLAY_FILTER_DESC", "Whether or not to display a dropdown list of orders on which to filter the list of invoices.");
define("NBILL_DISPLAY_DATE_RANGE", "Show date range");
define("NBILL_DISPLAY_DATE_RANGE_DESC", "Whether or not to allow the user to select a range of dates on which to filter the list of records (if this option is set to 'No', ALL records for that user will be shown in the list.");
define("NBILL_DISPLAY_INVOICE_DATE", "Show invoice date field");
define("NBILL_DISPLAY_INVOICE_DATE_DESC", "Whether or not to display the invoice date in the invoice list.");
define("NBILL_DISPLAY_FIRST_ITEM", "Show first item on invoice field");
define("NBILL_DISPLAY_FIRST_ITEM_DESC", "Whether or not to display the description of the first item on the invoice in the invoice list.");
define("NBILL_DISPLAY_NET", "Show net total field");
define("NBILL_DISPLAY_NET_DESC", "Whether or not to display the net total in the invoice list.");
define("NBILL_DISPLAY_GROSS", "Show gross total field");
define("NBILL_DISPLAY_GROSS_DESC", "Whether or not to display the gross total in the invoice list.");
define("NBILL_DISPLAY_OUTSTANDING", "Show total outstanding");
define("NBILL_DISPLAY_OUTSTANDING_DESC", "Whether or not to display the total amount due (if different from gross total - ie. for partially paid invoices)");
define("NBILL_DISPLAY_STATUS", "Show invoice status field");
define("NBILL_DISPLAY_STATUS_DESC", "Whether or not to display the invoice status (ie. whether unpaid, paid, partially paid, refunded, or partially refunded) in the invoice list.");
define("NBILL_DISPLAY_PAYMENT_LINK", "Show payment link (if applicable)");
define("NBILL_DISPLAY_PAYMENT_LINK_DESC", "Whether or not to display a link to allow online payment of the invoice (only applies if a default payment gateway has been specified for the vendor, and the invoice is unpaid).");
define("NBILL_DISPLAY_PDF", "Show PDF Link for Invoices");
define("NBILL_DISPLAY_PDF_DESC", "Whether or not to provide a PDF invoice in the front end (NOTE: PDF generation can be slow and use a lot of server resources, so use with caution! The PDF link will only appear if you have DomPDF installed. See %s)");
define("NBILL_DISPLAY_PENDING_PAY_LINK", "Allow payment of pending orders?");
define("NBILL_DISPLAY_PENDING_PAY_LINK_DESC", "Whether or not to display a button that will allow the user to pay for a pending order (in case they backed out or it went wrong previously and they want to try again). Only applicable if the order status is also displayed.");
define("NBILL_DISPLAY_PAYMENT_LINK_THRESHOLD", "Frequency Treshold");
define("NBILL_DISPLAY_PAYMENT_LINK_THRESHOLD_DESC", "By default, a payment link will only appear next to unpaid invoices for 'one-off' orders. If you want to enable the link for orders with other payment frequencies, select the most frequent option here - all orders with that payment frequency, or a less frequent one, will be eligible for a payment link - subject to 'Show Payment Link' (above) being set to 'yes', and invoice being unpaid, and the order record and invoice record not overriding this value.");
/*if (!defined("NBILL_ONE_OFF"))
{
	define("NBILL_ONE_OFF", "One-off");
	define("NBILL_WEEKLY", "Always Show");
	define("NBILL_MONTHLY", "Monthly");
	define("NBILL_FOUR_WEEKLY", "Four-weekly"); //Version 1.2.0
	define("NBILL_QUARTERLY", "Quarterly");
	define("NBILL_SEMI_ANNUALLY", "Semi-annually");
	define("NBILL_ANNUALLY", "Annually");
	define("NBILL_BIANNUALLY", "Biannually");
	define("NBILL_FIVE_YEARLY", "Five Yearly");
	define("NBILL_TEN_YEARLY", "Ten Yearly");
}*/
define("NBILL_DISPLAY_PATHWAY", "Display Pathway?");
define("NBILL_DISPLAY_PATHWAY_DESC", "Whether or not to show a 'breadcrumb trail' at the top of the page when a user has logged in.");
define("NBILL_DISPLAY_USERNAME", "Show user name field");
define("NBILL_DISPLAY_USERNAME_DESC", "Whether or not to allow users to amend their user name");

/**************/
/* Version 1.1.4
/* Note to translators: Text amended for NBILL_DISPLAY_EMAIL_OPTIONS_DESC (line 106 in the en-GB file).
/**************/

//Version 1.2.0
//Line 116 (four-weekly) added

//Version 1.2.0
define("NBILL_DISPLAY_RENEW_LINK", "Show renewal link");
define("NBILL_DISPLAY_RENEW_LINK_DESC", "Whether or not to show a link that allows the client to renew an order (if auto-renew is off or expiry date has passed only).");
define("NBILL_DISPLAY_RENEW_ADVANCE_LIMIT", "Advanced renewal limit");
define("NBILL_DISPLAY_RENEW_ADVANCE_LIMIT_DESC", "Maximum number of payment cycles in advance a renewal can be made (only applicable if `show renewal link` is set to `yes`. For example, if the payment frequency of an order is monthly, and you enter a value of 3 here, orders that have auto-renew turned off will allow the user to manually renew up to 3 months in advance. If you enter 0 (zero) here, the renewal link will not appear until AFTER the due date.");
define("NBILL_DISPLAY_GATEWAY_CHOICE_ORDER", "Allow choice of gateway");
define("NBILL_DISPLAY_GATEWAY_CHOICE_ORDER_DESC", "Allow user to select which payment gateway to use (if more than one is installed and published) when renewing orders (does not apply to pending orders, as they will use whatever gateway was specified by the order form). If this is set to `no`, the default gateway from the vendor record will be used.");
define("NBILL_DISPLAY_PAY_REQUIRES_LOGIN_ORDER", "Login required for payment");
define("NBILL_DISPLAY_PAY_REQUIRES_LOGIN_ORDER_DESC", "Whether or not user must be logged in before they can pay for a pending order or order renewal.");
define("NBILL_DISPLAY_GATEWAY_CHOICE_INVOICE", "Allow choice of gateway");
define("NBILL_DISPLAY_GATEWAY_CHOICE_INVOICE_DESC", "Allow user to select which payment gateway to use (if more than one is installed and published) when paying an invoice. If this is set to `no`, the default gateway from the vendor record will be used.");
define("NBILL_DISPLAY_PAY_REQUIRES_LOGIN_INVOICE", "Login required for payment");
define("NBILL_DISPLAY_PAY_REQUIRES_LOGIN_INVOICE_DESC", "Whether or not user must be logged in before they can pay an invoice.");
define("NBILL_DISPLAY_PARCEL_TRACKING", "Show parcel tracking link");
define("NBILL_DISPLAY_PARCEL_TRACKING_DESC", "Whether or not to show a link that will allow the user to track their package online (subject to a parcel tracking URL being set on the shipping record and a tracking ID being set on the order record). NOTE: This will ONLY be shown if Status is also shown.");

//Version 1.2.1
define("NBILL_DISPLAY_MY_ACCOUNT_HEADER", "Show 'My Account' Heading?");
define("NBILL_DISPLAY_MY_ACCOUNT_HEADER_DESC", "Whether or not to display a heading on each page in the front-end for logged-in users (default text is 'My Account', but you can change this in the language file if you wish)");
define("NBILL_DISPLAY_SUPPRESS_RENEW_IF_CANCELLED", "Suppress renewal if order cancelled");
define("NBILL_DISPLAY_SUPPRESS_RENEW_IF_CANCELLED_DESC", "Whether or not to suppress the renewal link if the order has been cancelled (prevents users from re-instating an order after having cancelled it - only applicable if `show renewal link` is set to `yes`).");
define("NBILL_DISPLAY_SUPPRESS_CANCEL_IF_NOT_AUTO_RENEW", "Suppress cancel if order not auto-renew");
define("NBILL_DISPLAY_SUPPRESS_CANCEL_IF_NOT_AUTO_RENEW_DESC", "Whether or not to suppress the cancel link if the order is not set to auto-renew. Cancelling an order that does not auto-renew has no tangible effect, but if you want to allow your users to cancel such orders anyway (rather than have to explain to them that there is no need to cancel), set this to `no`. Only applicable if `Allow cancellation` is set to `yes`, otherwise the cancel link will not appear anyway.");

//Version 1.2.3 - Note to translators:
//Line 114 of original en-GB language file amended (NBILL_WEEKLY) - changed to 'Always Show' for clarity

//Version 1.2.9
define("NBILL_DISPLAY_INV_SHOW_DATE_RANGE", "Show date range on invoices?");
define("NBILL_DISPLAY_INV_SHOW_DATE_RANGE_DESC", "Whether or not to display the date range in the invoice item descriptions for invoices relating to recurring payments (NOTE: this affects invoice generation only. Existing invoices will not change when you change this setting. If you set this to 'no', the date range will not be recorded on the invoice, so you might have trouble identifying the period the invoice was for.");
define("NBILL_DISPLAY_ADD_OPTION_TO_FORM_ACTION", "Add option parameter to form submission URL?");
define("NBILL_DISPLAY_ADD_OPTION_TO_FORM_ACTION_DESC", "There are 2 ways of submitting forms (ie. with or without the option parameter in the URL). In most cases, both methods will work, but some templates and some SEF URL components are fussy. If you find any form submissions are just redirecting to your home page or a blank page, try changing the value of this setting.");

//Version 2.0.1
define("NBILL_DISPLAY_MY_PROFILE_HELP", "You can control the rest of what is displayed on the My Profile page by using the %s feature");
define("NBILL_DISPLAY_MY_PROFILE_HELP_FIELDS", "Client Profile Fields");
define("NBILL_DISPLAY_MY_QUOTES", "My Quotes page options");
define("NBILL_DISPLAY_NEW_QUOTE_LINK", "Show 'request new quote' link");
define("NBILL_DISPLAY_NEW_QUOTE_LINK_DESC", "Whether or not to display a link from the quote list page to allow a user to request a new quote.");
define("NBILL_DISPLAY_QUOTE_DATE", "Show quote date field");
define("NBILL_DISPLAY_QUOTE_DATE_DESC", "Whether or not to display the quote date in the quote list.");
define("NBILL_DISPLAY_QUOTE_FIRST_ITEM", "Show first item on quote field");
define("NBILL_DISPLAY_QUOTE_FIRST_ITEM_DESC", "Whether or not to display the description of the first item on the quote in the quote list.");
define("NBILL_DISPLAY_QUOTE_NET", "Show net total field");
define("NBILL_DISPLAY_QUOTE_NET_DESC", "Whether or not to display the net total in the quote list.");
define("NBILL_DISPLAY_QUOTE_GROSS", "Show gross total field");
define("NBILL_DISPLAY_QUOTE_GROSS_DESC", "Whether or not to display the gross total in the quote list.");
define("NBILL_DISPLAY_QUOTE_STATUS", "Show quote status field");
define("NBILL_DISPLAY_QUOTE_STATUS_DESC", "Whether or not to display the quote status (ie. new, on hold, quoted, accepted, or part accepted) in the quote list (rejected quotes do not appear on the list).");
define("NBILL_DISPLAY_GATEWAY_CHOICE_QUOTE", "Allow choice of gateway");
define("NBILL_DISPLAY_GATEWAY_CHOICE_QUOTE_DESC", "Allow user to select which payment gateway to use (if more than one is installed and published) when paying for an accepted quote. If this is set to `no`, the default gateway from the vendor record will be used.");

//Version 2.1.0
define("NBILL_ALWAYS_SHOW", "Always Show");
define("NBILL_DISPLAY_LANGUAGE_SELECTION", "Show 'Choose Language' dropdown list?");
define("NBILL_DISPLAY_LANGUAGE_SELECTION_DESC", "Whether or not to show a list of languages for the client to choose from (only applicable if more than one " . NBILL_BRANDING_NAME . " language pack is installed). The language selected here will be used in the website front end when the client logs in and whenever automated e-mails are sent out for this client.");
define("NBILL_DISPLAY_ADMIN", "Display Link to Administrator");
define("NBILL_DISPLAY_ADMIN_DESC", "Whether or not to show a link to the " . NBILL_BRANDING_NAME . " administrator if the logged in user has been granted administration access (shows " . NBILL_BRANDING_NAME . " administration features within your website template) - if the logged in user has not been granted access, the link will not appear regardless of this setting.");
define("NBILL_DISPLAY_ADMIN_FULL", "Display Link to Administrator (Full Screen)");
define("NBILL_DISPLAY_ADMIN_FULL_DESC", "Whether or not to show a link to the " . NBILL_BRANDING_NAME . " administrator if the logged in user has been granted administration access (shows " . NBILL_BRANDING_NAME . " administration features within the browser window but without showing your website template thus allowing more room).");
define("NBILL_DISPLAY_ADMIN_NEW", "Open admin link in a new window?");
define("NBILL_DISPLAY_ADMIN_NEW_DESC", "Whether or not to open the " . NBILL_BRANDING_NAME . " administrator in a new window or tab.");
define("NBILL_DISPLAY_LOGOUT", "Show Logout Link?");
define("NBILL_DISPLAY_LOGOUT_DESC", "Whether or not to show a link that allows the user to logout.");

//Version 2.1.1
define("NBILL_DISPLAY_RELATING_TO", "Show 'relating to'");
define("NBILL_DISPLAY_RELATING_TO_DESC", "Whether or not to show a column to display the 'relating to' value (helps differentiate one order from another where the product name is the same).");
define("NBILL_DISPLAY_SUPPRESS_ZERO_TAX", "Suppress display of tax if no tax charged");
define("NBILL_DISPLAY_SUPPRESS_ZERO_TAX_DESC", "Whether to show or suppress tax rate and amount information on invoices where no tax was charged (if this is set to 'yes', and no tax was charged, tax amounts will be completely omitted from the invoice. If this is set to 'no', and no tax was charged, the tax rate and amount will be shown as 0.00 on the invoice). This is subject to using the default invoice template - custom invoice templates might not respect this setting.");

//Version 2.2.0
define("NBILL_DISPLAY_QUOTE_NO_DEFAULT_ACCEPT", "Default to NOT accepted");
define("NBILL_DISPLAY_QUOTE_NO_DEFAULT_ACCEPT_DESC", "Whether or not to assume that a quote has not been accepted until the client specifically marks it as accepted. You might want to set this to 'no' if you think your clients are likely struggle with the concept of checking the items they want to accept. If this is set to 'no', all items on a new quote will default to accepted and the client will have to manually uncheck items that they don't want to accept.");

//Version 2.3.0
define("NBILL_DISPLAY_QUOTE_LOGIN_TO_ACCEPT", "Login required for acceptance/rejection");
define("NBILL_DISPLAY_QUOTE_LOGIN_TO_ACCEPT_DESC", "Whether or not the client must be logged in before they can accept or reject a quote or add further information to the quote correspondence. WARNING! If you set this to 'no', it will allow anyone to access the quote and e-mail you if they know or guess the quote ID.");
define("NBILL_DISPLAY_ADDITIONAL_LINKS", "Additional Links");
define("NBILL_DISPLAY_EXTENSION_LINKS", "Extension Links");
define("NBILL_DISPLAY_NO_EXTENSION_LINKS", "There are no extension links to display. Links will only appear here if you have one or more extensions installed which offer features for your website front end.");
define("NBILL_DISPLAY_EXTENSION_LINKS_INTRO", "These are links to the front-end features of your installed extension(s). You can amend the link text and description, re-order the links and use the 'published' checkbox next to each link to hide or show the link.");
define("NBILL_LINK_ORDERING", "Ordering");
define("NBILL_LINK_PUBLISHED", "Published?");

//Version 2.7.0
define("NBILL_DISPLAY_HIDE", "No");
define("NBILL_DISPLAY_OPTIONAL", "Optional");
define("NBILL_DISPLAY_IMPORTANT", "Prioritize");
define("NBILL_DISPLAY_ESSENTIAL", "Always show");
define("NBILL_DISPLAY_HTML_PREVIEW", "Show HTML Preview?");
define("NBILL_DISPLAY_HTML_PREVIEW_DESC", "Whether or not to show an icon allowing an HTML version of the invoice to be shown in a popup window.");

//Version 3.0.0
define("NBILL_DISPLAY_DUE_DATE", "Show Due Date on Invoice?");
define("NBILL_DISPLAY_DUE_DATE_AFTER", "%s after invoice date");
define("NBILL_DISPLAY_DUE_DATE_DESC", "Whether or not to show a due date as well as the invoice date. If this is set to 'yes', you can select how many days, weeks, or months after the invoice date the due date should be. It will then be calculated and displayed on the invoice, until such time as it has been paid in full.");
define("NBILL_DISPLAY_GENERATE_EARLY", "Generate invoices in advance?");
define("NBILL_DISPLAY_GENERATE_EARLY_DESC", "If a due date is being shown on the invoice, you can use this option to generate invoices for recurring orders in advance of the next due date on the order. For example, if you have an order with a next due date of 15th July, and you have the above '" . NBILL_DISPLAY_DUE_DATE . "' setting set to show the invoice due date 30 days after the invoice date, and this setting is set to 'yes', the invoice will be generated 30 days early - i.e. on 15th June (but the date range on the invoice will show the period starting from 15th July, and the due date will be shown as 15th July). This setting only applies to invoices that are generated based on your order records, and should be used in conjunction with a daily CRON job to generate your invoices automatically (see online documentation for more information).");
define("NBILL_DISPLAY_DUE_DATE_ON_LIST", "Show Due Date on List?");
define("NBILL_DISPLAY_DUE_DATE_ON_LIST_DESC", "Whether or not to show the invoice due date on the list of invoices (only applicable if '" . NBILL_DISPLAY_DUE_DATE . "' setting above is set to 'yes' OR the invoice has a manually entered due date.");
define("NBILL_DISPLAY_PAYLINK_QR_CODE", "Show QR Code?");
define("NBILL_DISPLAY_PAYLINK_QR_CODE_DESC", "Whether or not to show a QR code on invoices to enable the invoice to be paid by scanning with a mobile device. Only applicable if a payment link is shown on the invoice, and requires a remote call to the Google Chart API to obtain the QR code image data.");
define("NBILL_DISPLAY_HTML_PREVIEW_QUOTE", "Show HTML Preview?");
define("NBILL_DISPLAY_HTML_PREVIEW_QUOTE_DESC", "Whether or not to show an icon allowing an HTML version of the quote to be shown in a popup window.");
define("NBILL_DISPLAY_PDF_QUOTE", "Show PDF Link for Quotes");
define("NBILL_DISPLAY_PDF_QUOTE_DESC", "Whether or not to provide a PDF quote in the front end (NOTE: PDF generation can be slow and use a lot of server resources, so use with caution! The PDF link will only appear if you have DomPDF installed. See %s)");
define("NBILL_DISPLAY_LOW_PRIORITY", "Low Priority");
define("NBILL_DISPLAY_MEDIUM_PRIORITY", "Medium Priority");
define("NBILL_DISPLAY_HIGH_PRIORITY", "High Priority");