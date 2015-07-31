<?php
/**
* Language file for Suppliers
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Suppliers
define("NBILL_SUPPLIERS_TITLE", "Suppliers");
define("NBILL_SUPPLIER_NAME", "Supplier Name");
define("NBILL_USERNAME_PASSWORD_REQUIRED", "In order to create a new user, a user name, password, and e-mail address are required.");
define("NBILL_EDIT_SUPPLIER", "Edit Supplier");
define("NBILL_NEW_SUPPLIER", "New Supplier");
define("NBILL_SUPPLIER_DETAILS", "Supplier Details");
define("NBILL_SUPPLIER_USER", "Related User");
define("NBILL_CREATE_USER", "[Create New User]");
define("NBILL_SUPPLIER_COUNTRY", "Country");
define("NBILL_SUPPLIER_REFERENCE", "Supplier Reference");
define("NBILL_SUPPLIER_CURRENCY", "Default Currency");
define("NBILL_INSTR_SUPPLIER_COMPANY_NAME", "Name of the supplier company");
define("NBILL_INSTR_SUPPLIER_ADDRESS", "Supplier address");
define("NBILL_INSTR_SUPPLIER_COUNTRY", "");
define("NBILL_INSTR_SUPPLIER_REFERENCE", "For your own use, if required");
define("NBILL_INSTR_SUPPLIER_WEBSITE", "Just for your own reference.");
define("NBILL_INSTR_SUPPLIER_CURRENCY", "Select the default currency that you use to pay this supplier.");
define("NBILL_SUPPLIER_DELETED", "Supplier Deleted");

//Version 1.2.1
define("NBILL_SUPPLIER_VIEW_PURCHASE_ORDERS", "View Purchase Orders for this Supplier");

//Version 1.2.6
define("NBILL_SUPPLIER_ACTION", "Action");
define("NBILL_SUPPLIER_NEW_ORDER", "Create New Purchase Order for this Supplier");

//Version 2.0.0
define("NBILL_SUPPLIER_TAB_SUPPLIER", "Supplier");
define("NBILL_SUPPLIER_PRIMARY", "Primary Contact");
define("NBILL_INSTR_SUPPLIER_PRIMARY", "Select which contact is the main billing contact for this supplier");
@define("NBILL_CONTACT_NAME_UNKNOWN", "[Name Unknown]");
define("NBILL_SUPPLIER_NO_IFRAMES", "Your browser does not support iframes - please click on the following link to edit this contact: %s");
define("NBILL_SUPPLIER_NEW_CONTACT", "Add New Contact");
define("NBILL_SUPPLIER_ASSIGN_CONTACT", "Assign Contact");
define("NBILL_SUPPLIER_REMOVE_CONTACT", "Remove this Contact");
define("NBILL_SUPPLIER_DELETE_CONTACT", "Delete this Contact");
define("NBILL_SUPPLIER_CONTACT_DELETE_SURE", "You have selected to PERMANENTLY DELETE %s contact(s). Are you sure you want to continue?");
define("NBILL_SUPPLIER_CONTACT_FILTER", "Enter the name or part of the name of the contact you want to assign, and click on 'Go'.");
define("NBILL_SUPPLIER_CONTACT_FILTER_GO", "Go");
define("NBILL_SUPPLIER_CONTACT_PERMISSIONS", "Contact Permissions");
define("NBILL_SUPPLIER_UPDATE_PROFILE", "Update Supplier Profile?");
define("NBILL_INSTR_SUPPLIER_UPDATE_PROFILE", "Whether or not this contact is allowed to update the details held on the Supplier record (eg. company name and address).");
define("NBILL_SUPPLIER_ACCESS_PURCHASE_ORDERS", "Access Purchase Orders?");
define("NBILL_INSTR_SUPPLIER_ACCESS_PURCHASE_ORDERS", "Whether or not this contact is allowed to access the purchase order details for this supplier.");
define("NBILL_SUPPLIER", "Supplier");
define("NBILL_SUPPLIER_TAX_REFERENCE", "Tax Reference");
define("NBILL_INSTR_SUPPLIER_TAX_REFERENCE", "Enter the supplier's tax reference number (ie. VAT number, or equivalent).");

//Version 2.0.9
define("NBILL_SUPPLIER_USERNAME_ALPHANUM", "Please use letters and numbers only for the username");

//Version 2.1.0
define("NBILL_SUPPLIER_LANGUAGE", "Default Language");
define("NBILL_INSTR_SUPPLIER_LANGUAGE", "Where more than one language pack is installed, each supplier can be assigned a particular language which will be used by default in your website front-end.");