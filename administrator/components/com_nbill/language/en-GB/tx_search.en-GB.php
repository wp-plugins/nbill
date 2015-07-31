<?php
/**
* Language file for the Transaction Search feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
* 
* @access private* 
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Backup/Restore
define("NBILL_TX_SEARCH_TITLE", "Transaction Search");
define("NBILL_TX_SEARCH_ID", "Transaction ID");
define("NBILL_TX_SEARCH_SEARCH", "Search");
define("NBILL_TX_SEARCH_INTRO", "You can use this feature to attempt to find records (orders/invoices/income/clients) that relate to a particular transaction reference number. This is useful where you have a transaction on a statement with your payment service provider, and you are trying to tie it up with your own records in " . NBILL_BRANDING_NAME . ". Enter a reference number in the box below and click on '" . NBILL_TX_SEARCH_SEARCH . "' to attempt to locate any related records.");