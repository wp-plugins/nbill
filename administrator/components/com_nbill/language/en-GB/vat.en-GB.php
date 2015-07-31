<?php
/**
* Language file for Sales Tax (VAT)
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Tax
define("NBILL_TAX_TITLE", "Value Added Tax / Sales Tax");
define("NBILL_TAX_INTRO", "You can specify different tax rates for buyers in different countries, or different tax zones - the correct rate will automatically be applied depending on the client's billing address and/or tax zone.");
define("NBILL_TAX_NAME", "Name of Tax");
define("NBILL_TAX_COUNTRY", "Country");
define("NBILL_TAX_RATE", "Rate");
define("NBILL_TAX_PAYMENT_INSTR", "Payment Instructions");
define("NBILL_TAX_SMALL_PRINT", "Small Print");
define("NBILL_TAX_EXEMPT_WITH_REF", "Exempt With Tax Reference?");
define("NBILL_EDIT_TAX", "Edit Tax");
define("NBILL_TAX_NAME_REQUIRED", "You must provide a name for this tax record.");
define("NBILL_TAX_COUNTRY_REQUIRED", "You must specify a country.");
define("NBILL_NEW_TAX", "New Tax Record");
define("NBILL_TAX_DETAILS", "Tax Details");
define("NBILL_TAX_ZONE", "Tax Zone");
define("NBILL_TAX_ONLINE_EXEMPT", "Online Orders Exempt?");
define("NBILL_INSTR_TAX_NAME", "Enter the full name of this type of tax (eg. \"Value Added Tax\", \"Imposto Sobre o Valor Acrescentado\")");
define("NBILL_INSTR_TAX_COUNTRY", "Specify which country this tax rate applies to.  In addition to the individual countries there are 2 special entries: \"European Union\", which includes all EU countries, and \"Worldwide\" which is a default to be applied whenever the country of the billing address does not have its own tax record.");
define("NBILL_INSTR_TAX_RATE", "Enter the rate of tax as a percentage value (don't include the % sign)");
define("NBILL_INSTR_TAX_PAYMENT_INSTR", "Any payment instructions you enter here will override the default payment instructions (defined on the Vendor page) for this country only.");
define("NBILL_INSTR_TAX_SMALL_PRINT", "Any small print you enter here will override the default small print (defined on the Vendor page) for this country only.");
define("NBILL_INSTR_TAX_EXEMPT_WITH_REF", "Specify whether to omit tax if the buyer is able to provide their own tax reference number or reseller certification (eg. for supplies made from the UK to the EU, or supplies made to resellers who are exempt from sales tax in ths US).");
define("NBILL_INSTR_TAX_ZONE", "If this tax rate only applies to certain clients (eg. those within a particular state or municipality), enter a code (up to 5 characters) to identify this rate of tax - and record the same code against the client's record.");
define("NBILL_INSTR_TAX_ONLINE_EXEMPT", "Whether or not online orders are exempt from this tax (typically for use in the US). If 'yes', any orders made through the component's front end that would normally fall under this tax rate, will not have tax applied. Administrators entering orders in the back end can choose whether or not to apply this tax rate.");

//Version 1.2.4
define("NBILL_TAX_RATE_CHANGE_WARNING", "WARNING! You have selected to change this tax rate from %s to %s, but there are already orders with recurring payment frequencies that are using the old rate. Future invoices for these orders will continue to be produced at the old rate if the order records are not updated. Your change has NOT yet been saved. Please select what action to take below.");
define("NBILL_TAX_RATE_CHANGE_AUTO_RENEW", "There are %s order records set to auto-renew. It is recommended that you allow " . NBILL_BRANDING_NAME . " to automatically adjust the net price and tax so that the gross amount stays the same - that way, any recurring payment schedule can stay in force. PLEASE NOTE: If you select to change the gross amount instead (not recommended), any existing payment schedules will need to be either amended, or cancelled and set up again for the new amount.");
define("NBILL_TAX_RATE_CHANGE_MANUAL_RENEW", "There are %s order records set to be renewed manually. You have a choice between allowing " . NBILL_BRANDING_NAME . " to automatically adjust the net price and tax so that the gross amount stays the same, or to automatically adjust the tax and gross amount so that the net price remains the same. If the gross amount is changed, naturally the next payment for each order will be for a different amount than previously, so do not use this option if the client has a recurring payment schedule set up (not likely as these orders are renewed manually).");
define("NBILL_TAX_RATE_CHANGE_ACTION_AUTO", "Please Select One of the Following Actions (for auto-renew orders)");
define("NBILL_TAX_RATE_CHANGE_ACTION_MANUAL", "Please Select One of the Following Actions (for manual renewal orders)");
define("NBILL_TAX_RATE_CHANGE_UPDATE_NET", "Update net price and tax, keep gross amount the same");
define("NBILL_TAX_RATE_CHANGE_UPDATE_GROSS", "Update gross price and tax, keep net amount the same");
define("NBILL_TAX_RATE_CHANGE_NO_ACTION_AND_CANCEL", "Take no action, and cancel the change of tax rate");
define("NBILL_TAX_RATE_CHANGE_NO_ACTION_AND_SAVE", "Take no action, but go ahead and save the tax rate change anyway");
define("NBILL_TAX_RATE_CHANGE_RECOMMENDED", " (Recommended)");
define("NBILL_TAX_RATE_CHANGE_NOT_RECOMMENDED", " (NOT RECOMMENDED!)");
define("NBILL_TAX_RATE_CHANGE_ORDER_DETAILS", "%s to see the orders that will be affected by this change.");
define("NBILL_TAX_RATE_CHANGE_ORDERS_INTRO", "The following order records will be affected by this change. NOTE: This list includes cancelled orders in case they are ever re-activated in future. Click on an order number to open the order record in a new window.");
define("NBILL_TAX_AFFECTED_ORDER_NO", "Order Number");
define("NBILL_TAX_AFFECTED_ORDER_COMPANY", "Company");
define("NBILL_TAX_AFFECTED_ORDER_CONTACT", "Contact");
define("NBILL_TAX_AFFECTED_ORDER_PRODUCT", "Product");
define("NBILL_TAX_AFFECTED_ORDER_NET", "Net Price");
define("NBILL_TAX_AFFECTED_ORDER_TAX", "Tax Amount");
define("NBILL_TAX_AFFECTED_ORDER_GROSS", "Gross Amount");
define("NBILL_TAX_RATE_CHANGE_WARNING_END", "When you have selected what action to take, above, please click on either the 'Apply' or 'Save' toolbar button. Until you click on one either 'Apply' or 'Save', your requested change will NOT be implemented.");
define("NBILL_TAX_RATE_CHANGE_SURE", "This action will affect a total of %s orders. Are you sure you want to continue?");
define("NBILL_TAX_RATE_CHANGE_ORDERS_UPDATED", "%s Order Records have been updated.");
define("NBILL_TAX_RATE_CHANGE_NONE", "No"); //Note for translators: This is 'no' as in 'not any' - ie. 'No Order Records have been updated'
define("NBILL_TAX_RATE_CHANGE_CHECK_PRODUCTS", "NOTE: It might also be a good idea to review your product prices and check they are still valid following the tax rate change, particularly if you charge all customers inclusive of tax.");

//Version 2.1.1
define("NBILL_TAX_RATE_CHANGE_CHECK_PRODUCT_CUSTOM", "WARNING! You have one or more product records with a custom tax rate. If you want to change the custom tax rate on any products, please do so using the product editor.");
define("NBILL_TAX_RATE_CHANGE_CHECK_ORDERS_CUSTOM", "WARNING! You have one or more order records with a custom tax rate. If you want to change the custom tax rate on any orders, please do so using the order editor (if you have more than one order with the same custom tax rate, changing one of them will offer you the chance to update the others also).");

//Version 3.0.0
define("NBILL_TAX_ELECTRONIC_DELIVERY_ITEMS", "The following records are only used on products marked for 'electronic delivery'.");
define("NBILL_TAX_ADVANCED_INTRO", "You only need to specify values here if you want to override the values from the vendor record. In most cases you should leave these settings blank.");
define("NBILL_TAX_ELECTRONIC_DELIVERY", "Electronic Delivery Only?");
define("NBILL_INSTR_TAX_ELECTRONIC_DELIVERY", "Whether or not to use this tax rate only for products marked as electronically delivered. As of 1st January 2015, electronically delivered products must have value added tax charged at the prevailing rate in the country of the consumer, not the vendor. If this option is set to 'yes', this tax rate will be used in preference to any other rate for products marked as electronically delivered (you can still use a generic EU tax rate, based on your own country's rate, for other products). This setting has no effect on the special 'Worldwide' or 'European Union' country values, it is only for specific countries.");
define("NBILL_TAX_RATE_AUTO_CHANGE_WARNING", "WARNING! The EU VAT rate for electronic supplies has changed for country code '%1\$s' from %2\$s to %3\$s, but there are already orders with recurring payment frequencies that are using the old rate. Future invoices for these orders will continue to be produced at the old rate if the order records are not updated. The VAT rate change has NOT yet been saved. Please select what action to take below (please note, you might see this message several times if you have more than one vendor record, or if more than one VAT rate has changed - once all your VAT rates for all vendors are up-to-date, these messages will go away. To stop automatic VAT rate updates, please refer to the 'advanced' tab of the Global Configuration page).");