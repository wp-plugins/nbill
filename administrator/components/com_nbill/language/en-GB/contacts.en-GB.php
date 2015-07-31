<?php
/**
* Language file for Contacts
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Contacts
define("NBILL_CONTACTS_TITLE", "Contacts");
define("NBILL_WEBSITE", "Website Address");
define("NBILL_TELEPHONE", "Telephone");
define("NBILL_TELEPHONE_2", "2nd Telephone Line");
define("NBILL_USERNAME_PASSWORD_REQUIRED", "In order to create a new user, a user name, password, and e-mail address are required.");
define("NBILL_EDIT_CONTACT", "Edit Contact");
define("NBILL_NEW_CONTACT", "New Contact");
define("NBILL_CONTACT_DETAILS", "Contact Details");
define("NBILL_CONTACT_USER", "Related User");
define("NBILL_CREATE_USER", "[Create New User]");
define("NBILL_CONTACT_NAME", "Contact Name");
define("NBILL_CONTACT_ADD_NAME_TO_INVOICE", "Add Contact Name to Invoices?");
define("NBILL_ADDRESS_1", "Address Line 1");
define("NBILL_ADDRESS_2", "Address Line 2");
define("NBILL_ADDRESS_3", "Address Line 3");
define("NBILL_TOWN", "Town/City");
define("NBILL_STATE", "State / County / Province");
define("NBILL_POSTCODE", "Postcode");
define("NBILL_CONTACT_COUNTRY", "Country");
define("NBILL_MOBILE", "Mobile (Cell) Phone");
define("NBILL_FAX", "Fax");
define("NBILL_INSTR_CONTACT_NAME", "Name of the person you deal with - you can leave this blank if you wish.");
define("NBILL_INSTR_CONTACT_ADDRESS", "Optionally enter the address for this contact (it is only used to create the client or supplier address if a client or supplier record is generated based on this contact).");
define("NBILL_INSTR_CONTACT_COUNTRY", "");
define("NBILL_INSTR_CONTACT_USER", "Select the user record to attach this contact to. NOTE: THIS WILL GIVE THE SELECTED USER ACCESS TO RECORDS (eg. INVOICES/ORDERS) FOR THIS CONTACT - please be careful!");
define("NBILL_INSTR_CONTACT_USER_ID", "Enter the NUMERICAL user id of the user record to attach this contact to (see the <a href=\"index2.php?option=com_users&task=view\">user management screen</a> - the user id is the last item on the list at the far right) NOTE: THIS WILL GIVE THE SELECTED USER ACCESS TO INVOICES FOR THIS CONTACT - please be careful, and make sure you get the correct user id!. If you want to see a list of users to pick from here instead of having to type in the user id number, please set this up on the <a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=configuration\">configuration page</a>.");
define("NBILL_INSTR_CONTACT_REFERENCE", "This is your reference that will appear on this contact's invoices - choose something short that will help you identify the contact.");
define("NBILL_INSTR_EMAIL_ADDRESS", "This is the email address to which invoices will be sent (if applicable)");
define("NBILL_INSTR_CONTACT_WEBSITE", "Just for your own reference.");
define("NBILL_INSTR_CONTACT_TELEPHONE", "");
define("NBILL_INSTR_CONTACT_TELEPHONE_2", "Optional, if there is an additional phone number you want to store.");
define("NBILL_INSTR_CONTACT_MOBILE", "");
define("NBILL_INSTR_CONTACT_FAX", "");
define("NBILL_CONTACT_DELETED", "Contact Deleted");
define("NBILL_ERR_CONTACT_COULD_NOT_CREATE_USER", "Sorry, a new User record could not be created!");
@define("NBILL_USERNAME", "Username");
@define("NBILL_PASSWORD", "Password");

//Version 1.2.0
define("NBILL_CONTACT_UNDER_MAMBOT_CONTROL", "This user`s access to the site is controlled by the user subscription plugin. You can click on this icon to remove all restrictions on this user`s access to your site.");
define("NBILL_CONTACT_CANCEL_MAMBOT_CONTROL", "Are you sure you want to remove this user from the control of the user subscription plugin?");
define("NBILL_CONTACT_MAMBOT_CONTROL_CANCELLED", "The selected user is no longer controlled by the user subscription plugin.");

//Version 2.0.0
define("NBILL_CONTACT", "Contact");
define("NBILL_CONTACT_INTRO", "Whenever you capture any name/address details in nBill, they are stored as a contact. Contacts can be assigned to Client and/or Supplier records. The same contact could be both a Client and a Supplier, and Clients and Suppliers can have more than one contact assigned to them (although only one is the 'primary' contact).");
define("NBILL_EMAIL_ADDRESS_2", "2nd E-mail Address");
define("NBILL_INSTR_EMAIL_ADDRESS_2", "If you want to hold more than one email address for this contact, you can enter a 2nd one here (for your own reference only).");
define("NBILL_CONTACT_CONTACT_NAME_UNKNOWN", "[Name Unknown]");
define("NBILL_CONTACT_ENTITY", "Client/Supplier");
define("NBILL_CONTACT_SHOW_CLIENTS", "Show Client(s)");
define("NBILL_CONTACT_SHOW_SUPPLIERS", "Show Supplier(s)");
define("NBILL_CONTACT_ID", "ID");
define("NBILL_CONTACT_CUSTOM_FIELDS", "Custom Fields");
define("NBILL_CONTACT_COPY_ADDRESS_FROM_CLIENT", "Copy Address");
define("NBILL_CONTACT_COPY_ADDRESS_HELP", "Click this button to copy the address data from the main client or supplier record");
define("NBILL_CONTACT_EMAIL_IN_USE", "The e-mail address `%s1` is already being used by another contact (%s2). Please either enter a unique e-mail address, or use the existing contact record.");

//Version 2.1.0
define("NBILL_CONTACT_FIRST_NAME", "First Name");
define("NBILL_CONTACT_LAST_NAME", "Last Name");

//Version 2.2.0
define("NBILL_CONTACT_CHECKING_EMAIL", "Checking E-mail Address");

//Version 3.0.0
define("NBILL_RESET_PASSWORD", "Reset Password");