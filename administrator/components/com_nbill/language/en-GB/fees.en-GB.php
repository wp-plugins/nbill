<?php
/**
* Language file for the Fees feature
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Fees
define("NBILL_FEES_TITLE", "Fees");
define("NBILL_FEES_INTRO", "You can set up fee rules here which can be applied to your products (either individually, or as a whole). Fees can be restricted by user level and/or code (like a voucher code for a discount, but to apply a fee instead of a deduction - typically used to apply a fee when a particular payment gateway is used), and can be implemented as percentages or fixed amounts. You can specify date ranges during which your fees are applicable.");
define("NBILL_FEE_NAME", "Fee Name");
define("NBILL_FEE_PUBLISHED", "Published?");
define("NBILL_EDIT_FEE", "Edit Fee");
define("NBILL_FEE_NAME_REQUIRED", "Please enter a name for this fee.");
define("NBILL_NEW_FEE", "New Fee");
define("NBILL_FEE_DETAILS", "Fee Details");
define("NBILL_INSTR_FEE_NAME", "Enter a descriptive name for this fee that will help you easily identify what it is for.");
define("NBILL_INSTR_FEE_DISPLAY_NAME", "Text to display on invoices when this fee is used (if blank, the fee name, above, will be used).");
define("NBILL_INSTR_FEE_TIME_LIMITED", "Whether or not to apply a date range during which this fee will be applicable.");
define("NBILL_INSTR_FEE_START_DATE", "When to start applying this fee (only applicable if 'Time Limited' is set to 'yes')");
define("NBILL_INSTR_FEE_END_DATE", "When to stop applying this fee (only applicable if 'Time Limited' is set to 'yes')");
define("NBILL_INSTR_FEE_GLOBAL", "Whether to apply this fee to all products (set to 'no', and edit individual product records to apply the fee only to certain products).");
define("NBILL_INSTR_FEE_PERCENTAGE", "Percentage fee to apply - don't include the percent sign. (eg. for 10%, just enter 10)");
define("NBILL_INSTR_FEE_AMOUNT", "Fixed amount to add (only applies if percentage is 0)");
define("NBILL_INSTR_FEE_EXCLUSIVE", "Whether to prohibit the use of any other fee if this fee is being used (where more than one fee is applicable, use the priority value to indicate which one to use).");
define("NBILL_INSTR_FEE_PRIORITY", "Enter a number to indicate the priority of this fee if more than one fee is applicable. The LOWER the number you enter here, the HIGHER the priority of the fee - so a fee with a priority of '0' will be applied before a fee with a priority of '1'. You can use whatever numbers you want. If 2 fees have the same priority, the ordering will be arbitrary.");
define("NBILL_FEE_VOUCHER", "Code");
define("NBILL_INSTR_FEE_VOUCHER", "If you specify a voucher code here, the fee will ONLY be applied if the voucher code is submitted on the order form, or if the voucher code is added to the order by an administrator, or if a matching code is stored against the payment gateway being used.");
define("NBILL_FEE_RECURRING", "Recurring Fee?");
define("NBILL_INSTR_FEE_RECURRING", "Whether the fee should also be applied to repeat payments.");
define("NBILL_FEE_AVAILABLE_YES", "Applicable to new orders");
define("NBILL_FEE_AVAILABLE_NO", "Not applicable to new orders");
define("NBILL_INSTR_FEE_AVAILABLE", "Whether this fee is available for new orders (or only for existing orders with recurring payments).");
define("NBILL_INSTR_FEE_PREREQ_PRODUCTS", "If the user must already have a certain product before they are charged this fee, specify the prerequisite product(s) here. If more than one product is selected, the fee will be applied if they already have ANY one of the prerequisite products.");
define("NBILL_FEE_DATE_REQUIRED", "Please either specify both a start and end date for this fee, or set the `Time-limited` option to `no`");
define("NBILL_FEE_SHIPPING_ONLY", "Shipping Fee?");
define("NBILL_INSTR_FEE_SHIPPING_ONLY", "Whether or not this fee only relates to the shipping costs.");
define("NBILL_FEE_WARNING_IN_USE", "WARNING! This fee is currently being used by the following order(s) with a recurring payment frequency. If you edit the rules of this fee, it could affect future invoices for these orders:");
define("NBILL_FEE_CANNOT_DELETE", "One or more fees selected for deletion cannot be deleted because they are being used by the following order(s) with a recurring payment frequency:");
define("NBILL_FEES_WARNING_DATE_PLUS_RECURRING", "WARNING! This fee has both an end date AND is set to be applied to recurring payments. This is not recommended, as the payment schedule will continue with the increased amount even after then end date (but invoices won`t). Are you sure you want to save?");
define("NBILL_INSTR_FEE_RENEWALS", "Whether or not to add this fee when existing qualifying orders (that did not previously have this fee) are renewed (applicable to recurring fees only). If an existing order already had this fee assigned to it (and it is a recurring fee), it will continue to be used for renewals regardless of this setting.");
define("NBILL_INSTR_FEE_DISQUAL_PRODUCTS", "If the user must NOT already have a certain product in order to attract this fee, specify the disqualifying product(s) here. If more than one product is selected, the fee will NOT be applied if they already have ANY one of the disqualifying products.");
define("NBILL_FEE_RECORD_LIMIT_WARNING", "WARNING! As there are %s or more orders with a recurring payment frequency using this fee, only the first %s records have been displayed here. If you want to see all of the orders, click on 'Show All' (below).");
define("NBILL_INSTR_FEE_APPLY_TO", "If a percentage value is specified, indicate whether to calculate the fee value based on the net price, the tax amount, or the gross. In most cases this should be left as 'Net'");
define("NBILL_INSTR_FEE_COMPOUND", "Whether or not to calculate the value of the fee based on the running total (where more than one fee is applied in a single transaction). For example, if this is set to 'yes' and the net total of a transaction is \$100.00, and a 25% fee has already been applied, the value for this fee will be calculated based on a percentage of \$125.00, not \$100.00.");
define("NBILL_INSTR_FEE_COUNTRY", "If you want to apply this fee only to clients within a particular country, specify the country here.");

//Version 2.4.0
define("NBILL_FEE_LEDGER_CODE", "Nominal Ledger Code");
define("NBILL_FEE_LEDGER_AUTO", "Auto-Select");
define("NBILL_INSTR_FEE_LEDGER_CODE", "You can optionally specify a ledger code to which amounts associated with this fee should be applied. If this is set to '" . NBILL_FEE_LEDGER_AUTO . "', " . NBILL_BRANDING_NAME . " will attempt to apply the amount to the ledger code associated with the item or items being paid for (in the case of payment gateway fees, the fee amount will be amalgamated into a single entry - so if there is more than one ledger code involved, one of the codes will be picked arbitrarily. As such, it might be best to set a specific ledger code for this fee if it is being used as a payment gateway fee).");