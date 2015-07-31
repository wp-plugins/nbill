<?php
/**
* Language file for Product Categories
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
* 
* @access private* 
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Categories
define("NBILL_CATEGORIES_TITLE", "Categories");
define("NBILL_CATEGORY_NAME", "Name");
define("NBILL_CATEGORY_DESCRIPTION", "Description");
define("NBILL_CATEGORY_ORDERING", "Ordering");
define("NBILL_EDIT_CATEGORY", "Edit Category");
define("NBILL_NEW_CATEGORY", "New Category");
define("NBILL_ERR_CANNOT_DELETE_CATEGORY", "One or more Categories you tried to delete is not empty. You cannot delete a Category which contains Products or other Categories. Please either delete or move the Products and/or child Categories to a different Category before attempting to delete.");
define("NBILL_ERR_CANNOT_DELETE_ROOT_CAT", "You cannot delete the root category - there must always be at least one category present.");
define("NBILL_CATEGORY_NAME_REQUIRED", "Please enter a name for the Category");
define("NBILL_CATEGORY_DETAILS", "Category Details");
define("NBILL_CATEGORY_PARENT", "Parent Category");
define("NBILL_INSTR_CATEGORY_NAME", "");
define("NBILL_INSTR_CATEGORY_DESCRIPTION", "");
define("NBILL_INSTR_CATEGORY_PARENT", "");