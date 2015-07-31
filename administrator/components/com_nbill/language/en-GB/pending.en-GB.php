<?php
/**
* Language file for Pending Orders
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Pending Orders
define("NBILL_PENDING", "Pending");
define("NBILL_ACTIVATE_PENDING_ORDER", "Activate Pending");
define("NBILL_ACTIVATE_ARE_YOU_SURE", "WARNING! This will create a new order record, and the pending record will be deleted. If the related order form stipulates automatic creation of a user record and/or invoice, these will also be generated. Are you sure you want to continue?");
define("NBILL_SELECT_PENDING_ORDER", "Please make a selection from the list to activate");
define("NBILL_PENDING_ORDER", "Pending Order");
define("NBILL_PENDING_TITLE", "Pending Orders");
define("NBILL_PENDING_INTRO", "These are orders that were received through the website front-end, but for which payment has not yet been confirmed. Orders will only be held in this pending file if the order form to which they relate stipulates that they should be held until payment is confirmed. If the installed payment gateway extension detects a payment for a pending order, the order will be created automatically. If payment has been made by some other means, or if you want to activate an order without having received payment, you may do so using the activate toolbar button.");
define("NBILL_PENDING_ORDER_ID", "Order ID");
define("NBILL_PENDING_ORDER_FORM", "Order Form");
define("NBILL_PENDING_ORDER_DATE", "Date");
define("NBILL_PENDING_ORDER_VALUE", "Total Order Value");
define("NBILL_SHOW_PENDING_ORDER", "Show Pending Order Details");
define("NBILL_PENDING_ORDER_DETAILS", "Pending Order Details");
define("NBILL_QUANTITY", "Quantity");
define("NBILL_OTHER_DATA", "Other Data");
define("NBILL_ORDERS_ACTIVATED", "Selected Pending Orders Activated");
@define("NBILL_USERNAME", "Username");
@define("NBILL_PAY_FREQUENCY", "Payment Frequency");

//Version 1.2.0
define("NBILL_PENDING_RESUME_LINK", "If you have enabled the payment of orders without being logged in (on the display options page), the following link can be used to resume this pending order (ie. to make payment).");