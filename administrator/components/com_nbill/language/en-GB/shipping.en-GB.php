<?php
/**
* Language file for the Shipping feature.
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
* 
* @access private* 
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Shipping
define("NBILL_SHIPPING_TITLE", "Shipping Rates");
define("NBILL_SHIPPING_INTRO", "This is optional. If you add shipping costs onto the price of your products, you can specify the rates here. You can also specify whether shipping is taxable, and if so, apply a different rate of tax for the shipping portion of the invoice.");
define("NBILL_SHIPPING_COUNTRY", "Country");
define("NBILL_SHIPPING_PRICE_PER_UNIT", "Unit Price");
define("NBILL_EDIT_SHIPPING_RATE", "Edit Shipping Rate");
define("NBILL_NEW_SHIPPING_RATE", "New Shipping Rate");
define("NBILL_SERVICE_NAME_REQUIRED", "Please enter the name of this service");
define("NBILL_COUNTRY_REQUIRED", "Please select the country to which this service applies");
define("NBILL_SHIPPING_DETAILS", "Shipping Details");
define("NBILL_SHIPPING_CODE", "Code");
define("NBILL_NET_PRICE_PER_UNIT", "Net Price per Unit");
define("NBILL_SHIPPING_FIXED", "Fixed Price Shipping?");
define("NBILL_SHIPPING_IS_TAXABLE", "Is Shipping Taxable?");
define("NBILL_SHIPPING_TAX_RATE", "Tax Rate if Different");
define("NBILL_INSTR_SHIPPING_SERVICE", "Enter a descriptive name for the service (eg. 'First Class', 'Recorded Delivery')");
define("NBILL_INSTR_SHIPPING_CODE", "Enter your own short code for this service (optional, but if present is used to sort the delivery options alphabetically).");
define("NBILL_INSTR_SHIPPING_COUNTRY", "Select the country to which this service applies.");
define("NBILL_INSTR_NET_PRICE_PER_UNIT", "If you charge a fixed price per invoice for shipping, enter that amount here and select 'yes' to the fixed price option below. If your shipping costs depend on the product size/weight/quantity, enter the amount in figures that represents the smallest common denominator for your products' shipping fees. You can then specify a multiplier of this value on a per-product basis which will be used to calculate the shipping fees that appear on invoices. Do not include a currency symbol.");
define("NBILL_INSTR_SHIPPING_FIXED", "If you charge a fixed price per invoice for shipping, select 'yes', and the value you entered in 'Net Price per Unit' will be applied as a fixed fee to the whole invoice (instead of as a unit price). If your shipping costs depend on the product size/weight/quantity, select 'no'.");
define("NBILL_INSTR_SHIPPING_IS_TAXABLE", "Indicate whether value-added tax is to be charged for shipping amounts.");
define("NBILL_INSTR_SHIPPING_TAX_RATE", "If shipping is taxable AND the tax for shipping is charged at a different rate to normal, enter the shipping tax rate here (as a percentage, but without the % sign)");
define("NBILL_SHIPPING_PRICE_INTRO", "Enter the price of the shipping for each currency that you want to sell in.");
define("NBILL_SHIPPING_ID", "ID");

//Version 1.2.0
define("NBILL_SHIPPING_TRACKING_URL", "Shipping Tracking URL");
define("NBILL_INSTR_SHIPPING_TRACKING_URL", "If the courier allows online tracking of parcels, enter the URL here, and use ## (double hash) as a placeholder for the tracking ID. When you ship the package, you can enter the actual tracking ID on the order record. This will then be combined with the URL recorded here to generate a link in the website front-end that will allow the user to track their goods (subject to this being allowed within the display options).");