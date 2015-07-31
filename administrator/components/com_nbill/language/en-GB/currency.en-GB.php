<?php
/**
* Language file for Currencies
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Currency
define("NBILL_CURRENCY_TITLE", "Currencies");
define("NBILL_CURRENCY_INTRO", "If your products are priced in multiple currencies, you can define those currencies here. You must have at least one currency.");
define("NBILL_CURRENCY_NAME", "Name of Currency");
define("NBILL_CURRENCY_CODE", "ISO Code");
define("NBILL_CURRENCY_SYMBOL", "Symbol");
define("NBILL_CURRENCY_RATE", "Conversion Rate");
define("NBILL_EDIT_CURRENCY_RATE", "Edit Currency");
define("NBILL_NEW_CURRENCY_RATE", "New Currency");
define("NBILL_CURRENCY_DETAILS", "Currency Details");
define("NBILL_CURRENCY_NAME_REQUIRED", "Please enter the name of the currency.");
define("NBILL_CURRENCY_CODE_REQUIRED", "Please enter the ISO Code of the currency.");
define("NBILL_INSTR_CURRENCY_NAME", "Proper name of currency (eg. 'US Dollars', 'Euros')");
define("NBILL_INSTR_CURRENCY_CODE", "ISO Code of currency (eg. 'USD', 'EUR')");
define("NBILL_INSTR_CURRENCY_SYMBOL", "Symbol used to prefix amounts (eg. '&#36;', '&euro;') - note: it is safest to use HTML codes (eg. &amp;#36; &amp;euro;).");
define("NBILL_ERR_CANNOT_DELETE_LAST_CURRENCY", "You cannot delete the last currency!");
define("NBILL_ERR_CANNOT_DELETE_CURRENCY_IN_USE", "One or more currencies you tried to delete are in use by one or more vendors. Process aborted - you cannot delete a currency that is being used by a vendor.");

//Version 2.1.0
define("NBILL_ERR_ISO_CODE_LENGTH", "ISO Currency Code must be exactly 3 characters long.");

//Version 3.0.0
define("NBILL_CURRENCY_OVERRIDE_DEFAULT_FORMATTING", "Override Formatting?");
define("NBILL_INSTR_CURRENCY_OVERRIDE_DEFAULT_FORMATTING", "Whether or not to override the precision (number of decimal places), separators (thousands and decimal), and currency symbol output for amounts expressed in this currency (if this setting is set to 'no', the values from the global configuration page will be used and the other settings relating to formatting on this tab will be ignored).");
define("NBILL_CURRENCY_PRECISION_CURRENCY", "Currency Precision");
define("NBILL_INSTR_CURRENCY_PRECISION_CURRENCY", "Number of decimal places to use for general currency output (maximum 6).");
define("NBILL_CURRENCY_PRECISION_CURRENCY_LINE_TOTAL", "Currency Line Total Precision");
define("NBILL_INSTR_CURRENCY_PRECISION_CURRENCY_LINE_TOTAL", "Number of decimal places to use for line totals on quotes, invoices, reports, etc. (maximum 6).");
define("NBILL_CURRENCY_PRECISION_CURRENCY_GRAND_TOTAL", "Currency Grand Total Precision");
define("NBILL_INSTR_CURRENCY_PRECISION_CURRENCY_GRAND_TOTAL", "Number of decimal places to use for grand totals on quotes, invoices, reports, etc. (maximum 6).");
define("NBILL_CURRENCY_THOUSANDS_SEPARATOR", "Thousands Separator");
define("NBILL_INSTR_CURRENCY_THOUSANDS_SEPARATOR", "Custom thousands separator - if this is set to 'default', the separator from your server's current locale (or the locale setting from the global configuration page) will be used.");
define("NBILL_CURRENCY_DECIMAL_SEPARATOR", "Decimal Separator");
define("NBILL_INSTR_CURRENCY_DECIMAL_SEPARATOR", "Custom decimal separator - if this is set to 'default', the separator from your server's current locale (or the locale setting from the global configuration page) will be used.");
define("NBILL_CURRENCY_CURRENCY_FORMAT", "Currency Format String");
define("NBILL_INSTR_CURRENCY_CURRENCY_FORMAT", "By default, currency output will be based on the locale specified in the global configuration, or the default locale of your server, however, you can override this here using the syntax of the PHP sprintf command. Please note however, that you must also specify a matching currency precision, above. For example, to output the amount 123.4567 as AU&#36;123.457 (ie. rounded to 3 decimal places, with an AU&#36; prefix), you would need to set the currency precision (above) to 3, and this currency format string to 'AU&#36;%01.3f' (without the quotes). To output the same value as '123 Reais' (ie. rounded to an integer, with a suffix of 'Reais'), you would use 0 (zero) as the currency precision, and the following currency format string: '%01f Reais' (without quotes). As sprintf does not support using a thousands separator, you will also need to specify a custom thousands separator, above, if using this setting.");