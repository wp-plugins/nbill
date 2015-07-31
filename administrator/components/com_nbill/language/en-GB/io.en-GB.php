<?php
/**
* Language file for the data Import/Export features
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Import/Export
define("NBILL_IMPORT", "Import");
define("NBILL_EXPORT", "Export");
define("NBILL_IMPORT_SOURCE", "Source");
define("NBILL_EXPORT_TARGET", "Target");
define("IMPORT_USERS_ARE_YOU_SURE", "WARNING! This will create a new client record for every user that does not already have a client record. Are you sure you want to continue?");
define("IMPORT_CSV_ARE_YOU_SURE", "WARNING! This will create a new client record (or update an existing record) for every record in the CSV file. Are you sure you want to continue?");
define("NBILL_CLIENT_IO_TITLE", "Client Import/Export");
define("NBILL_CLIENT_IO_INTRO", "You can create Client records for all of your existing users, or import your client list from a CSV file. You can also export your client list to a CSV file. To create users for your existing clients, you have to do so either in the client record itself, or by importing a CSV file (they cannot be created automatically en-masse because you need to specify a unique username and a password for each one).");
define("NBILL_IMPORT_CLIENTS_USERS", "Users");
define("NBILL_IMPORT_CLIENTS_CSV", "CSV File");
define("NBILL_INSTR_IMPORT_CLIENTS_USERS", "Create new client records based on current user records (if a client record already exists for a user, it will not be duplicated). Super Administrators are excluded.");
define("NBILL_INSTR_IMPORT_CLIENTS_CSV", "Create new client records based on a CSV file. %s Please read the %s before using this feature.");
define("NBILL_IMPORTANT", "IMPORTANT: ");
define("NBILL_IMPORT_CLIENTS_CSV_HELP", "Client CSV Import Help Text");
define("NBILL_IMPORT_CLIENTS_CSV_HELP_TITLE", "Important! Please read all of this text before using client CSV import.");
define("NBILL_IMPORT_CLIENTS_CSV_HELP_TEXT_1", "You can use the import CSV feature to quickly populate your client list with records that you may have exported from another program (but only 1 contact can be created per client this way). The CSV file MUST include the column names in the first row, and only those column names that are recognised by " . NBILL_BRANDING_NAME . " will be used - any other columns will be ignored. It does not matter what order the columns appear in, and you do not have to use all of the columns. As a minimum though, the CSV file should contain values for either the company name or contact name (or both). The following column names will be recognised: ");
define("NBILL_IMPORT_CLIENTS_CSV_HELP_TEXT_2", "When you import a CSV file, " . NBILL_BRANDING_NAME . " will first try to locate an existing client record for each entry in the CSV file. If the CSV includes an 'id' column, the client record with that id will be updated. If there is a 'contact_id' column, the contact record with that id will be updated. If there is no 'id' or 'contact_id' column, but there is an 'email_address' column, and a contact already exists with that e-mail address, the contact record with that e-mail address will be updated. If no existing client record is found, a new record will be inserted.");
define("NBILL_IMPORT_CLIENTS_CSV_HELP_TEXT_3", "If the CSV file includes a 'user_id' column, the contact record will be associated with the user record for that user id. If there is no 'user_id' column, but there is an 'email_address' column, and an existing user already has the same e-mail address, the contact record will be associated with that user.");
define("NBILL_IMPORT_CLIENTS_CSV_HELP_TEXT_4", "If there is no 'user_id' column, and no existing user matches the record's e-mail address but there is a 'username' column and a 'password' column, " . NBILL_BRANDING_NAME . " will create a new user, and associate it with the contact record. If the value in the 'password' column is either 32 characters long and is capable of being base64 decoded (ie. it is alpha-numeric only) or 65 characters long, with a colon at position 33 (ie. a salted hashed password as used from Joomla 1.0.13 onwards), it will be assumed that the password is already hashed, and it will not be hashed again. You can therefore choose to use salted MD5 hashed passwords (or just MD5 hashed passwords for Mambo or early versions of Joomla), or plain-text passwords, and both should be imported ok. It is always safer to use hashed passwords though.");
define("NBILL_IMPORT_CLIENTS_CSV_HELP_TEXT_5", "If a 'country' column is included, the value must be a valid 2-character ISO code. If no 'country' column is included, or the value is blank, the client record(s) will be set to the same country as the default vendor.");
define("NBILL_IMPORT_ALL_USERS", "Import All");
define("NBILL_IMPORT_SELECT_USERS", "Select Users");
define("NBILL_IMPORT_CSV", "Import CSV");
define("NBILL_EXPORT_CLIENTS_CSV", "CSV File");
define("NBILL_INSTR_EXPORT_CLIENTS_CSV", "Export client records to a CSV file.");
define("NBILL_EXPORT_CSV", "Download CSV");
define("NBILL_INSTR_IMPORT_CLIENTS_VENDOR", "Select the vendor for whom you want to import client records.");
define("NBILL_INSTR_EXPORT_CLIENTS_VENDOR", "Select the vendor for whom you want to export client records.");
define("NBILL_IMPORT_CLIENTS_COMPLETE", "%s new client record(s) created");
define("NBILL_SELECT_USERS_TITLE", "Select Users");
define("NBILL_USER_ID", "User ID");
define("NBILL_USER_PERSON_NAME", "Name");
define("NBILL_SELECT_USERS_INTRO", "Check the boxes next to the users you want to import (you can only do one page worth of users at a time - use the dropdown at the bottom of the page to specify how many users to display per page) and click in the 'Import' toolbar button above to create Client records for the selected users. Only those users who do not already have a client record for the selected vendor are displayed here.");
define("NBILL_NO_CLIENTS_FOUND", "No Client records were found - there is nothing to export!");
define("NBILL_CLIENT_CSV_IMPORTED", "CSV Import Complete.");
define("NBILL_CLIENT_CSV_IMPORT_NEW_USERS", "%s new Users were created.");
define("NBILL_CLIENT_CSV_IMPORT_NEW_CLIENTS", "%s Client and/or Contact records were created or updated.");
define("NBILL_CLIENT_CSV_IMPORT_ERRORS", "WARNING! %s database error(s) occurred during the import. The error message(s) are shown below (maximum 10 entries shown)");
@define("NBILL_USER_NAME", "User Name");