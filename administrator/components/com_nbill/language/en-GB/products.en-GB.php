<?php
/**
* Language file for Products
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Products
define("NBILL_PRODUCTS_TITLE", "Products/Services");
define("NBILL_PRODUCTS_INTRO", "This is a list of the products or services for which invoices can be generated.");
define("NBILL_PRODUCT_NAME", "Name");
define("NBILL_PRODUCT_SKU", "Code/SKU");
define("NBILL_PRODUCT_PRICES", "Price(s)");
define("NBILL_EDIT_PRODUCT", "Edit Product or Service");
define("NBILL_NEW_PRODUCT", "New Product or Service");
define("NBILL_PRODUCT_DESCRIPTION", "Description");
define("NBILL_PATH_TO_IMAGE", "Path to Image");
define("NBILL_IS_FREEBIE", "Free of Charge?");
define("NBILL_PRODUCT_NAME_REQUIRED", "Please enter a name for this product or service.");
define("NBILL_PRODUCT_DETAILS", "Product Details");
define("NBILL_NET_PRICE_ONE_OFF", "Net Price One-off");
define("NBILL_NET_PRICE_WEEKLY", "Net Price Weekly");
define("NBILL_NET_PRICE_MONTHLY", "Net Price Monthly");
define("NBILL_NET_PRICE_QUARTERLY", "Net Price Quarterly");
define("NBILL_NET_PRICE_ANNUALLY", "Net Price Annually");
define("NBILL_NET_PRICE_BIANNUALLY", "Net Price Bi-annually");
define("NBILL_NET_PRICE_FIVE_YEARLY", "Net Price Five-yearly");
define("NBILL_NET_PRICE_TEN_YEARLY", "Net Price Ten-yearly");
define("NBILL_IS_TAXABLE", "Taxable?");
define("NBILL_REQUIRES_SHIPPING", "Add Shipping Fees?");
define("NBILL_SHIPPING_SERVICES", "Shipping Services Available");
define("NBILL_SHIPPING_UNITS", "Shipping Units");
define("NBILL_AUTO_FULFIL", "Auto Fulfil Orders?");
define("NBILL_IS_DOWNLOADABLE", "Is Downloadable?");
define("NBILL_DOWNLOAD_LOCATION", "Download Location");
define("NBILL_NO_OF_DAYS_AVAILABLE", "No. of Days Available");
define("NBILL_DOWNLOAD_LINK_TEXT", "Download Link Text");
define("NBILL_INSTR_PRODUCT_SKU", "An 'SKU' or 'Stock Keeping Unit' is a code that you use to uniquely identify this product. You can use whatever code you like.");
define("NBILL_INSTR_PRODUCT_NAME", "");
define("NBILL_INSTR_PRODUCT_CATEGORY", "");
define("NBILL_INSTR_PATH_TO_IMAGE", "This is not currently used, but might be implemented at a later date for use in conjunction with a shopping cart.");
define("NBILL_INSTR_IS_FREEBIE", "It this product is supplied free of charge, indicate that here, otherwise fill in one or more of the pricing boxes below.");
define("NBILL_INSTR_NET_PRICE_ONE_OFF", "If this item can be bought outright (rather than paid for on a regular basis), enter the net price here.");
define("NBILL_INSTR_NET_PRICE_WEEKLY", "If item can be paid for weekly, enter net price per week.");
define("NBILL_INSTR_NET_PRICE_MONTHLY", "If item can be paid for monthly, enter net price per month.");
define("NBILL_INSTR_NET_PRICE_QUARTERLY", "If item can be paid for quarterly, enter net price per quarter.");
define("NBILL_INSTR_NET_PRICE_ANNUALLY", "If item can be paid for annually, enter net price per year.");
define("NBILL_INSTR_NET_PRICE_BIANNUALLY", "If item can be paid for bi-annually, enter net price per 2 years.");
define("NBILL_INSTR_NET_PRICE_FIVE_YEARLY", "If item can be paid for five-yearly, enter net price per 5 years.");
define("NBILL_INSTR_NET_PRICE_TEN_YEARLY", "If item can be paid for ten-yearly, enter net price per 10 years.");
define("NBILL_INSTR_IS_TAXABLE", "Select 'no' if you always want to omit tax for this item, otherwise, 'yes'.");
define("NBILL_INSTR_REQUIRES_SHIPPING", "Whether or not shipping fees need to be added to the price of this item.");
define("NBILL_INSTR_SHIPPING_SERVICES_AVAILABLE", "Select the delivery services that can be used with this item, if applicable.");
define("NBILL_INSTR_SHIPPING_UNITS", "The defined shipping rate for the selected service will be multiplied by this value to calculate the total shipping cost (typically you would enter a value greater than 1 for heavier items that cost more to ship). Decimal fractions are allowed.");
define("NBILL_INSTR_AUTO_FULFIL", "Indicate whether all orders for this item are set to a status of 'complete' automatically (eg. for immediate download).");
define("NBILL_INSTR_IS_DOWNLOADABLE", "Whether or not the client should be able to download this product from their 'My Account' area.");
define("NBILL_INSTR_DOWNLOAD_LOCATION", "The ABSOLUTE PATH to the download file (eg. /home/username/downloads/mydocument.pdf). This location should NOT be in the public area of your site. (ie. Don't use a folder underneath your public_html or htdocs folder, otherwise anyone will be able to download it without paying!)");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT", "Text to display for the download link.");
define("NBILL_INSTR_NO_OF_DAYS_AVAILABLE", "Number of days that the link(s) for downloading the file(s) should be made available in the client's 'My Account' area.");
define("NBILL_PRODUCT_PRICE_INTRO", "Enter the price(s) of the product for each currency that you want to sell this product in.");
define("NBILL_DOWNLOAD_INFO_REQUIRED", "You have specified that this is a downloadable product, but have not specified either the download location or the link text. Please ensure both of these values are completed if you want this product to be downloadable.");
define("NBILL_DOWNLOAD_LOCATION_2", "Download Location 2");
define("NBILL_INSTR_DOWNLOAD_LOCATION_2", "The ABSOLUTE PATH to the 2nd download file (you can have up to 10 files, eg. you might want an extra tutorial document to accompany your main product). ");
define("NBILL_DOWNLOAD_LINK_TEXT_2", "2nd Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_2", "Text to display for the download link for the 2nd file.");
define("NBILL_DOWNLOAD_LOCATION_3", "Download Location 3");
define("NBILL_INSTR_DOWNLOAD_LOCATION_3", "The ABSOLUTE PATH to the 3rd download file.");
define("NBILL_DOWNLOAD_LINK_TEXT_3", "3rd Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_3", "Text to display for the download link for the 3rd file.");
define("NBILL_IS_USER_SUB", "User Subscription?");
define("NBILL_INSTR_IS_USER_SUB", "Indicate whether this product represents a user subscription.");
define("NBILL_SUB_USER_GROUP", "Access Level User Group");
define("NBILL_INSTR_SUB_USER_GROUP", "User group to grant to users who purchase this subscription.");
define("NBILL_EXPIRY_LEVEL", "Expiry User Group");
define("NBILL_INSTR_EXPIRY_LEVEL", "What to do with the user privileges when the subscription expires (note: if ordered at a 'one-off' price, the subscription will never expire).");
define("NBILL_EXPIRY_REDIRECT", "Expiry Redirect");
define("NBILL_INSTR_EXPIRY_REDIRECT", "URL to redirect to after a subscription expiry is processed (ie. after the user record has been downgraded, blocked, or deleted). 'None' means that no redirect will take place, 'Default' means that " . NBILL_BRANDING_NAME . " will generate an appropriate message, or you can choose your own URL to redirect to.");
define("NBILL_REDIRECT_NONE", "None");
define("NBILL_REDIRECT_DEFAULT", "Default");
define("NBILL_REDIRECT_URL", "The following URL");
define("NBILL_EXPIRY_DELETE", "[Delete User]");
define("NBILL_EXPIRY_BLOCK", "[Block User]");
define("NBILL_EXPIRY_DOWNGRADE", "Set to %s");
define("NBILL_ENSURE_MAMBOT_PUBLISHED", "NOTE: As this product is a user subscription, please ensure you have installed and published the user subscription plugin - available from " . NBILL_BRANDING_WEBSITE);
define("NBILL_ALLOW_GLOBAL_DISCOUNTS", "Allow Global Discounts?");
define("NBILL_INSTR_ALLOW_GLOBAL_DISCOUNTS", "Indicate whether any global discounts that you have defined are allowed to be applied to orders for this product.");
define("NBILL_PRODUCT_DISCOUNT_TITLE", "Product-specific Discounts");
define("NBILL_PRODUCT_DISCOUNT_INTRO", "You can optionally specify that certain discounts are applicable to this Product. If more than one discount is applicable, use the Priority value to indicate which one to evaluate first (a discount with a priority of 0 will be applied before a discount with a priority of 1). Priorities assigned here override any priorities set on the discounts themselves. To control the properties of the discount (eg. amount, whether it is exclusive, etc.), edit the discount record via the discounts page. If you only want the discount to apply if a certain quantity of this product is purchased, you can specify this in the Minimum Quantity field. You can also optionally multiply the amount of a discount by the quantity of items ordered (fixed price product-specific discounts only). If using the quantity as a multiplier, you can also specify an offset that will be subtracted from the multiplier - for example, if you want the discount to apply to the 2nd and subsequent items, but not the first, you would set the offset to 1.");
define("NBILL_PRODUCT_DISCOUNT", "Discount");
define("NBILL_PRODUCT_DISCOUNT_PRIORITY", "Priority");
define("NBILL_PRODUCT_DISCOUNT_QTY", "Minimum Quantity");
define("NBILL_ADD_PRODUCT_DISCOUNT", "Add Product Discount");
define("NBILL_DELETE_PRODUCT_DISCOUNT", "Delete Product Discount");
define("NBILL_PLEASE_SELECT_PRODUCT_DISCOUNT", "Please select a Discount to add.");
define("NBILL_PRODUCT_DISCOUNT_DUPLICATION", "This Discount has already been added.");
define("NBILL_INSTR_PRODUCT_DESCRIPTION", "This is not currently used, but might be implemented at a later date for use in conjunction with a shopping cart.");
define("NBILL_NET_PRICE_SETUP_FEE", "Setup Fee");
define("NBILL_INSTR_NET_PRICE_SETUP_FEE", "If you charge a one-off seutp fee in addition to the regular price, specify it here. This fee will only be charged once on the first payment cycle, and is added to the regular price. You can specify a negative value if you want to provide a free or discounted trial for one payment cycle only (subject to this being supported by your payment gateway).");
define("NBILL_ERR_PRODUCT_IN_USE", "Sorry, one or more products you selected to delete are currently in use on the following order form(s): %s. Please ensure the product(s) are not in use before trying to delete them.");
define("NBILL_EMAIL_DOWNLOADS", "E-Mail Downloads?");
define("NBILL_INSTR_EMAIL_DOWNLOADS", "Whether or not to send the file(s) defined above to the client when this product is ordered (this would occur when the order record is created - so if you have 'pending until paid' set to 'yes' on the order form, they won't get the files until they've paid for them).");
define("NBILL_EMAIL_DOWNLOADS_MESSAGE", "E-Mail Message");
define("NBILL_INSTR_EMAIL_DOWNLOADS_MESSAGE", "Type in the message that you want to send along with the attached file(s) (only applicable if 'Email Downloads?' is set to 'yes').");
define("NBILL_NET_PRICE_SEMI_ANNUALLY", "Net Price Semi-annually (6-monthly)");
define("NBILL_INSTR_NET_PRICE_SEMI_ANNUALLY", "If item can be paid for semi-annually (every 6 months), enter net price per 6 months.");

/****************/
/* Version 1.1.4
/* Note to translators: Text has changed on the following constants:
NBILL_SUB_USER_GROUP, NBILL_INSTR_SUB_USER_GROUP, NBILL_EXPIRY_LEVEL (lines 94-96 in the en-GB file)
/***************/

//Version 1.2.0
define("NBILL_NET_PRICE_FOUR_WEEKLY", "Net Price Four-weekly");
define("NBILL_INSTR_NET_PRICE_FOUR_WEEKLY", "If item can be paid for four weekly, enter net price per four weeks.");

/**************/
/* Version 1.2.1
/* Note to translators: Text has changed on the following constants:
/* NBILL_PRODUCT_DISCOUNT_INTRO, NBILL_PRODUCT_DISCOUNT_QTY (lines 103 and 106 in the en-GB file)
/**************/
define("NBILL_PRODUCT_UPDATE_EXISTING_ORDERS", "One or more existing orders that are renewed manually were found for this product. Click `OK` if you want to update the prices on the existing order(s).");
define("NBILL_EXISTING_ORDERS_UPDATED", "%s existing order(s) updated.");
define("NBILL_PRODUCT_DOWNLOADABLE_TOKENS", "You can use any of the following tokens to dynamically generate the file name: ##order_no##, ##order_id##, ##client_id##. For example, if you create an individual file for each order, you can use a file name like \"/home/username/file##order_no##.txt\" - the ##order_no## token will then be replaced with the actual order number. The same principle can be applied to any of the 10 download links.");
define("NBILL_PRODUCT_DISCOUNT_MULTIPLY", "Multiply by Quantity?");
define("NBILL_PRODUCT_DISCOUNT_OFFSET", "Offset");

//Version 2.1.0
define("NBILL_MULTI_GROUP", "Allow Multiple User Groups?");
define("NBILL_INSTR_MULTI_GROUP", "Whether or not to allow the user to belong to more than one group (if supported by the CMS). If this is set to 'yes', the user will be ADDED to the group represented by this product, but they will also continue to be members of any other groups they had access to before. If this is set to 'no', the user will be REMOVED from ALL other groups they belonged to, and will be assigned to the one represented by this product ONLY.");
define("NBILL_CSV_ITEM_CURRENCY", " - %s");

//Version 2.1.1
define("NBILL_PRODUCT_CUSTOM_TAX_RATE", "Custom Tax Rate");
define("NBILL_INSTR_PRODUCT_CUSTOM_TAX_RATE", "You can override the tax rate used when this product is ordered by specifying your own tax rate here (if the rate here is zero, the global tax rate will be applied). This rate will only be applied if a relevant global tax rate would normally take effect (so if tax would normally be omitted because the client has an exemption code, the custom tax rate would also be omitted). If you want the tax rate for this product to be 0%, you have to set 'Taxable?' to 'no' above, because entering zero here causes the global tax rate to be applied (you might also want to set the 'Suppress display of tax if no tax charged' display option to 'no' on the 'My Invoices' tab of the 'Display Options' page).");
define("NBILL_PRODUCT_ALLOW_FREQ_CHANGE", "Allow Frequency Change on Renewal?");
define("NBILL_INSTR_PRODUCT_ALLOW_FREQ_CHANGE", "Whether or not to allow the user to change the payment frequency on an order for this product when the order is renewed. If multiple orders are being renewed simultaneously, the option to change the frequency will only be offered if all orders allow it and all have the same frequency at the time of renewal. Only frequencies that have prices defined for the order currency will be offered (if only one frequency has a price defined, the option to change frequency will not be shown).");
define("NBILL_NOTE_USER_SUB", "Please Note: A 'User Subscription' in " . NBILL_BRANDING_NAME . " is a product which grants access to a particular user group. It has nothing to do with recurring payment frequencies. You do NOT need to mark your products as user subscriptions to take repeat payments, you ONLY need to do so if you want to restrict access to certain content on your website according to the user group that the user belongs to.");

//Version 2.2.0
define("NBILL_INSTR_PRODUCT_HTML_DESCRIPTION", "You can enter a more detailed description here. This will appear in the detailed description setting for any invoices or quotes relating to this product.");

//Version 2.3.2
define("NBILL_PRODUCT_DOWNLOAD_MORE", "More downloadable files");
define("NBILL_DOWNLOAD_LOCATION_4", "Download Location 4");
define("NBILL_INSTR_DOWNLOAD_LOCATION_4", "The ABSOLUTE PATH to the 4th download file. ");
define("NBILL_DOWNLOAD_LINK_TEXT_4", "4th Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_4", "Text to display for the download link for the 4th file.");
define("NBILL_DOWNLOAD_LOCATION_5", "Download Location 5");
define("NBILL_INSTR_DOWNLOAD_LOCATION_5", "The ABSOLUTE PATH to the 5th download file. ");
define("NBILL_DOWNLOAD_LINK_TEXT_5", "5th Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_5", "Text to display for the download link for the 5th file.");
define("NBILL_DOWNLOAD_LOCATION_6", "Download Location 6");
define("NBILL_INSTR_DOWNLOAD_LOCATION_6", "The ABSOLUTE PATH to the 6th download file. ");
define("NBILL_DOWNLOAD_LINK_TEXT_6", "6th Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_6", "Text to display for the download link for the 6th file.");
define("NBILL_DOWNLOAD_LOCATION_7", "Download Location 7");
define("NBILL_INSTR_DOWNLOAD_LOCATION_7", "The ABSOLUTE PATH to the 7th download file. ");
define("NBILL_DOWNLOAD_LINK_TEXT_7", "7th Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_7", "Text to display for the download link for the 7th file.");
define("NBILL_DOWNLOAD_LOCATION_8", "Download Location 8");
define("NBILL_INSTR_DOWNLOAD_LOCATION_8", "The ABSOLUTE PATH to the 8th download file. ");
define("NBILL_DOWNLOAD_LINK_TEXT_8", "8th Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_8", "Text to display for the download link for the 8th file.");
define("NBILL_DOWNLOAD_LOCATION_9", "Download Location 9");
define("NBILL_INSTR_DOWNLOAD_LOCATION_9", "The ABSOLUTE PATH to the 9th download file. ");
define("NBILL_DOWNLOAD_LINK_TEXT_9", "9th Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_9", "Text to display for the download link for the 9th file.");
define("NBILL_DOWNLOAD_LOCATION_10", "Download Location 10");
define("NBILL_INSTR_DOWNLOAD_LOCATION_10", "The ABSOLUTE PATH to the 10th download file. ");
define("NBILL_DOWNLOAD_LINK_TEXT_10", "10th Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_10", "Text to display for the download link for the 10th file.");

//Version 3.0.0
define("NBILL_PRODUCT_ELECTRONIC_DELIVERY", "Electronically Delivered?");
define("NBILL_INSTR_PRODUCT_ELECTRONIC_DELIVERY", "Whether or not this product is delivered electronically for the purposes of EU value added tax (and must therefore be charged using the tax rate prevailing in the country of the client rather than the vendor).");