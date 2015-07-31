<?php
/**
* Language file for the Discounts feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Discounts
define("NBILL_DISCOUNTS_TITLE", "Discounts");
define("NBILL_DISCOUNTS_INTRO", "You can set up discount rules here which can be applied to your products (either individually, or as a whole). Discounts can be restricted by user level and/or voucher code, and can be implemented as percentages or fixed amounts. You can also choose to automatically disable a voucher-based discount after it has been used to prevent it being used again, and specify date ranges during which your discounts are applicable.");
define("NBILL_DISCOUNT_NAME", "Discount Name");
define("NBILL_DISCOUNT_END_DATE", "End Date");
define("NBILL_DISCOUNT_LOGGED_IN", "Logged in Only?");
define("NBILL_DISCOUNT_PERCENTAGE", "Percentage");
define("NBILL_DISCOUNT_AMOUNT", "Amount");
define("NBILL_EDIT_DISCOUNT", "Edit Discount");
define("NBILL_ALL_GROUPS", "All");
define("NBILL_AND_ABOVE", " and above");
define("NBILL_DISCOUNT_NAME_REQUIRED", "Please enter a name for this discount.");
define("NBILL_NEW_DISCOUNT", "New Discount");
define("NBILL_DISCOUNT_DETAILS", "Discount Details");
define("NBILL_INSTR_DISCOUNT_NAME", "Enter a descriptive name for this discount that will help you easily identify what it is for.");
define("NBILL_DISCOUNT_DISPLAY_NAME", "Display Name");
define("NBILL_INSTR_DISCOUNT_DISPLAY_NAME", "Text to display on invoices when this discount is used (if blank, the discount name, above, will be used).");
define("NBILL_DISCOUNT_TIME_LIMITED", "Time-limited?");
define("NBILL_INSTR_DISCOUNT_TIME_LIMITED", "Whether or not to apply a date range during which this discount will be applicable.");
define("NBILL_DISCOUNT_START_DATE", "Start Date");
define("NBILL_INSTR_DISCOUNT_START_DATE", "When to start applying this discount (only applicable if 'Time Limited' is set to 'yes')");
define("NBILL_INSTR_DISCOUNT_END_DATE", "When to stop applying this discount (only applicable if 'Time Limited' is set to 'yes')");
define("NBILL_DISCOUNT_GLOBAL", "Global?");
define("NBILL_INSTR_DISCOUNT_GLOBAL", "Whether to apply this discount to all products (set to 'no', and edit individual product records to apply the discount only to certain products). NOTE: You can also exclude global discounts for a particular product by editing the product record.");
define("NBILL_INSTR_DISCOUNT_LOGGED_IN", "Whether or not this discount is only available to users who are logged in.");
define("NBILL_INSTR_DISCOUNT_PERCENTAGE", "Percentage discount to apply - don't include the percent sign. (eg. for 10%, just enter 10)");
define("NBILL_INSTR_DISCOUNT_AMOUNT", "Fixed amount to discount (only applies if percentage is 0)");
define("NBILL_DISCOUNT_EXCLUSIVE", "Exclusive?");
define("NBILL_INSTR_DISCOUNT_EXCLUSIVE", "Whether to prohibit the use of any other discount if this discount is being used (where more than one discount is applicable, use the priority value to indicate which one to use).");
define("NBILL_DISCOUNT_PRIORITY", "Priority");
define("NBILL_INSTR_DISCOUNT_PRIORITY", "Enter a number to indicate the priority of this discount if more than one discount is applicable. The LOWER the number you enter here, the HIGHER the priority of the discount - so a discount with a priority of '0' will be applied before a discount with a priority of '1'. You can use whatever numbers you want. If 2 discounts have the same priority, the ordering will be arbitrary.");
define("NBILL_DISCOUNT_VOUCHER", "Voucher");
define("NBILL_INSTR_DISCOUNT_VOUCHER", "If you specify a voucher code here (eg. for a specific promotion), the discount will ONLY be applied if the user enters the voucher code on the order form or if the voucher code is added to the order by an administrator. NOTE: For this to work, you must also specify on the order form that you want to allow a voucher code to be entered, OR, you can use this to assign a discount to an individual order by entering the voucher code on the order record yourself.");
define("NBILL_DISCOUNT_RECURRING", "Recurring Discount?");
define("NBILL_INSTR_DISCOUNT_RECURRING", "Whether the discount should also be applied to repeat payments.");
define("NBILL_AVAILABLE", "Available");
define("NBILL_DISCOUNT_AVAILABLE_YES", "Available for new orders");
define("NBILL_DISCOUNT_AVAILABLE_NO", "Not available for new orders");
define("NBILL_DISCOUNT_AVAILABLE", "Available for New Orders?");
define("NBILL_INSTR_DISCOUNT_AVAILABLE", "Whether this discount is available for new orders (or only for existing orders with recurring payments).");
define("NBILL_DISCOUNT_AUTO_DISABLE", "Auto-Disable Discount?");
define("NBILL_INSTR_DISCOUNT_AUTO_DISABLE", "Whether to automatically make this discount unavailable for new orders when it has been used (to prevent the same voucher code being used twice).");
define("NBILL_DISCOUNT_PREREQ_PRODUCTS", "Prerequisite Products");
define("NBILL_INSTR_DISCOUNT_PREREQ_PRODUCTS", "If the user must already have a certain product before they qualify for this discount, specify the prerequisite product(s) here. If more than one product is selected, the discount will be granted if they already have ANY one of the prerequisite products.");
define("NBILL_DISCOUNT_PREREQ_CATS", "Categories");
define("NBILL_DISCOUNT_PREREQ_CAT_PROD", "Products");
define("NBILL_DISCOUNT_PREREQ_SELECTED_PROD", "Selected Product(s)");
define("NBILL_DISCOUNT_AMOUNT_REQUIRED", "Please specify either a percentage or an amount (for each currency).");
define("NBILL_DISCOUNT_DATE_REQUIRED", "Please either specify both a start and end date for this discount, or set the `Time-limited` option to `no`");
define("NBILL_DISCOUNT_MIN_ORDER_VALUE", "Minimum Order Value");
define("NBILL_INSTR_DISCOUNT_MIN_ORDER_VALUE", "Minimum value that client must order (in one go, after any other discounts have been applied, but net of tax) in order to qualify for this discount.");
define("NBILL_DISCOUNT_SHIPPING_ONLY", "Shipping Discount?");
define("NBILL_INSTR_DISCOUNT_SHIPPING_ONLY", "Whether or not this discount only relates to the shipping fees.");
define("NBILL_DISCOUNT_WARNING_IN_USE", "WARNING! This discount is currently being used by the following order(s) with a recurring payment frequency. If you edit the rules of this discount, it could affect future invoices for these orders:");
define("NBILL_DISCOUNT_CANNOT_DELETE", "One or more discounts selected for deletion cannot be deleted because they are being used by the following order(s) with a recurring payment frequency:");
define("NBILL_DISCOUNTS_WARNING_DATE_PLUS_RECURRING", "WARNING! This discount has both an end date AND is set to be applied to recurring payments. This is not recommended, as the payment schedule will continue with the discounted amount even after then end date (but invoices won`t). Are you sure you want to save?");

/********************/
/* Version 1.1.4
/* Note to translators: Text changed on the following constants:
/* NBILL_INSTR_DISCOUNT_USER_LEVEL, NBILL_INSTR_DISCOUNT_AND_ABOVE (lines 47 and 49 in the en-GB file)
/********************/

//Version 1.2.1
define("NBILL_DISCOUNT_RENEWALS", "Add to Renewals?");
define("NBILL_INSTR_DISCOUNT_RENEWALS", "Whether or not to add this discount when existing qualifying orders (that did not previously have this discount) are renewed (applicable to recurring discounts only). If an existing order already had this discount assigned to it (and it is a recurring discount), it will continue to be used for renewals regardless of this setting.");
define("NBILL_DISCOUNT_DISQUAL_PRODUCTS", "Disqualifying Products");
define("NBILL_INSTR_DISCOUNT_DISQUAL_PRODUCTS", "If the user must NOT already have a certain product before they qualify for this discount, specify the disqualifying product(s) here. If more than one product is selected, the discount will be denied if they already have ANY one of the disqualifying products.");
define("NBILL_DISCOUNT_DISQUAL_CATS", "Categories");
define("NBILL_DISCOUNT_DISQUAL_CAT_PROD", "Products");
define("NBILL_DISCOUNT_DISQUAL_SELECTED_PROD", "Selected Product(s)");

//Version 1.2.7
define("NBILL_DISCOUNT_RECORD_LIMIT_WARNING", "WARNING! As there are %s or more orders with a recurring payment frequency using this discount, only the first %s records have been displayed here. If you want to see all of the orders, click on 'Show All' (below).");
define("NBILL_DISCOUNT_SHOW_ALL", "Show All");

//Version 2.1.0
define("NBILL_DISCOUNT_APPLY_TO", "Apply to");
define("NBILL_INSTR_DISCOUNT_APPLY_TO", "If a percentage value is specified, indicate whether to calculate the discount value based on the net price, the tax amount, or the gross. In most cases this should be left as 'Net'");
define("NBILL_DISCOUNT_APPLY_NET", "Net");
define("NBILL_DISCOUNT_APPLY_TAX", "Tax");
define("NBILL_DISCOUNT_APPLY_GROSS", "Gross");
define("NBILL_DISCOUNT_COMPOUND", "Compound?");
define("NBILL_INSTR_DISCOUNT_COMPOUND", "Whether or not to calculate the value of the discount based on the running total (where more than one discount is applied in a single transaction). For example, if this is set to 'yes' and the net total of a transaction is \$100.00, and a 25% discount has already been applied, the value for this discount will be calculated based on a percentage of \$75.00, not \$100.00.");
define("NBILL_DISCOUNT_COUNTRY", "Country");
define("NBILL_INSTR_DISCOUNT_COUNTRY", "If you want to apply this discount only to clients within a particular country, specify the country here.");

//Version 2.4.0
define("NBILL_DISCOUNT_AVAILABLE_DOCS", "Available for Quotes/Invoices?");
define("NBILL_INSTR_DISCOUNT_AVAILABLE_DOCS", "Whether or not to allow this discount to be applied to a quote or invoice at the time of payment.");
define("NBILL_DISCOUNT_AVAILABLE_DOCS_YES", "Available for invoices/quotes");
define("NBILL_DISCOUNT_AVAILABLE_DOCS_NO", "Not available for invoices/quotes");
define("NBILL_DISCOUNT_LEDGER_CODE", "Nominal Ledger Code");
define("NBILL_DISCOUNT_LEDGER_AUTO", "Auto-Select");
define("NBILL_INSTR_DISCOUNT_LEDGER_CODE", "You can optionally specify a ledger code to which amounts associated with this discount should be applied. If this is set to '" . NBILL_DISCOUNT_LEDGER_AUTO . "', " . NBILL_BRANDING_NAME . " will attempt to apply the amount to the ledger code associated with the item or items being paid for (in the case of invoice/quote discounts, the discount amount will be amalgamated into a single entry - so if there is more than one ledger code involved, one of the codes will be picked arbitrarily. As such, it might be best to set a specific ledger code for this discount if it is an invoice/quote discount and your invoices or quotes can contain multiple ledger codes, but not for an order discount). Please be aware though that using a separate ledger code for discounts can result in ledger entries for discounted items (which do not share the same ledger code) adding up to more than 100%. If in doubt, leave it on '" . NBILL_DISCOUNT_LEDGER_AUTO . "'!");