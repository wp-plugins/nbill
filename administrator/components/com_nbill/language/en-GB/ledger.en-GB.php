<?php
/**
* Language file for the Nominal Ledger feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
* 
* @access private* 
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Nominal Ledger
define("NBILL_LEDGER_TITLE", "Nominal Ledger");
define("NBILL_LEDGER_INTRO", "If you keep a nominal ledger (also known as a general ledger or speed types/cost centres) for your transactions, you can define the codes and descriptions here. This is optional - for reporting purposes only.");
define("NBILL_LEDGER_CODE", "Code");
define("NBILL_LEDGER_DESCRIPTION", "Description");
define("NBILL_EDIT_LEDGER_CODE", "Edit Nominal Ledger Code");
define("NBILL_NEW_LEDGER_CODE", "New Nominal Ledger Code");
define("NBILL_LEDGER_CODE_REQUIRED", "Please enter a code");
define("NBILL_LEDGER_DESC_REQUIRED", "Please enter a description");
define("NBILL_LEDGER_CODE_DETAILS", "Ledger Code Details");
define("NBILL_INSTR_LEDGER_CODE", "Typically, a numerical cost centre code.");
define("NBILL_INSTR_LEDGER_DESCRIPTION", "");