<?php
/**
* Language file for the default invoice template
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Language constants common to all/most types of document template
define("NBILL_PRT_REFERENCE", "Reference:");
define("NBILL_PRT_DATE", "Date:");
define("NBILL_PRT_CLIENT_TAX_REF", "Client Tax Reference:");
define("NBILL_PRT_DESC", "Description");
define("NBILL_PRT_UNIT_PRICE", "Unit Price");
define("NBILL_PRT_QUANTITY", "Quantity");
define("NBILL_PRT_DISCOUNT", "Discount");
define("NBILL_PRT_NET_PRICE", "Net Price");
define("NBILL_PRT_VAT", "VAT");
define("NBILL_PRT_SHIPPING", "Shipping");
define("NBILL_PRT_SHIPPING_VAT", "%s on Shipping");
define("NBILL_PRT_TOTAL", "Total");
define("NBILL_PRT_NET_AMOUNT", "Net Amount:");

//Version 2.4.0
define("NBILL_PRT_PRINT", "Print");
define("NBILL_PRT_PRINT_PAGE", "Print this page");
define("NBILL_PRT_SUBTOTAL", "%s Sub-Total");