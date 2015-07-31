<?php
/**
* Language file for Supporting Documents
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Supporting Documents
define("NBILL_SUPP_DOCS_TITLE", "Supporting Documents");
define("NBILL_SUPP_DOCS_INTRO", "Here you can upload and organise documents that relate to your accounts. For example, invoices from other companies for purchases you have made, receipts, purchase orders, or anything else that might be needed to explain or support your " . NBILL_BRANDING_NAME . " records. Files that have been uploaded here can be 'attached' to expenditure records, credit notes, invoices, quotes, income records, products, orders, clients and suppliers. This page is provided for your convenience but it is not a fully fledged file manager - for some operations you might find it easier to use an FTP client or a full web file manager.");
define("NBILL_SUPP_DOCS_ATTACH_INTRO", "Here you can upload and organise documents that relate to your accounts. Click on a file to attach it to the selected " . NBILL_BRANDING_NAME . " record.");
define("NBILL_SUPP_DOCS_WARNING", "<strong>WARNING!</strong> This feature will only work if the user PHP runs under on your server has access to the file system. It has the potential to permanently delete large numbers of files so PLEASE USE WITH CAUTION! Also, please be aware that if you upload a lot of documents they will take up disk space and if you are on a shared hosting account this could quickly take you over your quota. It is recommended to specify a root path that is NOT in the public area of your website (ie. above the htdocs or public_html folder), otherwise anybody will be able to download your documents. If you uninstall " . NBILL_BRANDING_NAME . ", any files you have uploaded here will NOT be deleted.");
define("NBILL_SUPP_DOCS_ROOT_PATH", "Root Path");
define("NBILL_SUPP_DOCS_ROOT_PATH_DESC", "Path to which supporting documents should be uploaded (you can also create folders within this root path to organise your supporting documents by type).");
define("NBILL_SUPP_DOCS_ROOT_PATH_NOT_FOUND", "The root path you specified could not be found or is not writable by the user PHP is running under. No action has been taken.");
define("NBILL_SUPP_DOCS_CURRENT_PATH", "Current Path: ");
define("NBILL_SUPP_DOCS_SAVE_PATH", "Save Path");
define("NBILL_SUPP_DOCS_FILE_OR_FOLDER_NAME", "File or Folder Name");
define("NBILL_SUPP_DOCS_SIZE", "Size");
define("NBILL_SUPP_DOCS_LAST_MODIFIED", "Last Modified");
define("NBILL_SUPP_DOCS_ATTACHED_TO", "Attached To");
define("NBILL_SUPP_DOCS_ACTION", "Action");
define("NBILL_SUPP_DOCS_SHOW_FILES", "Show: ");
define("NBILL_SUPP_DOCS_SHOW_FILES_WITH", "Attached Only");
define("NBILL_SUPP_DOCS_SHOW_FILES_WITHOUT", "Unattached Only");
define("NBILL_SUPP_DOCS_SHOW_FILES_ALL", "Show All");
define("NBILL_SUPP_DOCS_UP_ONE_LEVEL", "Navigate up one level");
define("NBILL_SUPP_DOCS_NEW_FOLDER_NAME_INTRO", "Please enter a name for the new folder - lower case letters, numbers, and hyphens only please!");
define("NBILL_SUPP_DOCS_NEW_FOLDER_NAME", "New Folder Name");
define("NBILL_SUPP_DOCS_FILE_UPLOAD_INTRO", "Select up to 10 files to upload to the current folder");
define("NBILL_SUPP_DOCS_UPLOAD_PATH_NOT_FOUND", "The upload path '%s' was not found. No files could be uploaded.");
define("NBILL_SUPP_DOCS_CURRENT_PATH_NOT_FOUND", "The path '%s' could not be found.");
define("NBILL_SUPP_DOCS_FILE_UPLOAD_FAILED", "The following file could not be uploaded: %s. Please check the folder ownership and permissions.");
define("NBILL_SUPP_DOCS_FILE_NOT_FOUND", "Sorry, file '%s' could not be found.");
define("NBILL_SUPP_DOCS_FILE_OR_FOLDER_NOT_FOUND", "Sorry, file or folder '%s' could not be found.");
define("NBILL_SUPP_DOCS_DELETE_FOLDER_SURE", "WARNING! This will delete the folder \'%s\' and ALL files and folders contained within it. Are you sure?");
define("NBILL_SUPP_DOCS_ACTION_MOVE", "Move File");
define("NBILL_SUPP_DOCS_MOVE_INTRO", "Please select the path you want to move the file to");
define("NBILL_SUPP_DOCS_MOVE_FAILED", "Sorry, the file '%s' could not be moved. Make sure there is not already a file with that name in the destination folder, and that the folder is writable by the user PHP runs under.");
define("NBILL_SUPP_DOCS_ACTION_COPY", "Copy File");
define("NBILL_SUPP_DOCS_COPY_INTRO", "Please select the path you want to copy the file to");
define("NBILL_SUPP_DOCS_COPY_FAILED", "Sorry, the file '%s' could not be copied. Make sure there is not already a file with that name in the destination folder, and that the folder is writable by the user PHP runs under.");
define("NBILL_SUPP_DOCS_ACTION_RENAME", "Rename File");
define("NBILL_SUPP_DOCS_RENAME_INTRO", "Please enter a new name for the file (letters, numbers, hyphens and underscores only please!)");
define("NBILL_SUPP_DOCS_NEW_FILE_NAME", "New File Name");
define("NBILL_SUPP_DOCS_RENAME_FAILED", "Sorry, the file '%s' could not be renamed. Make sure there is not already a file with that name in the destination folder, and that the folder is writable by the user PHP runs under.");
define("NBILL_SUPP_DOCS_ACTION_EDIT", "Edit File");
define("NBILL_SUPP_DOCS_EDIT_INTRO", "You can edit the contents of this file here, but use with caution and back up the file first. This is a VERY simple editor for simple text files only which might not handle some character encodings correctly. Your server settings might also prevent some data from being submitted correctly (eg. if you have Suhosin installed it might truncate your data). Use at your own risk!");
define("NBILL_FILE_EDIT_FAILED", "Sorry, the file '%s' could not be saved. Make sure that the file is writable by the user PHP runs under.");
define("NBILL_SUPP_DOCS_SORT_ASC", "Sort by this column is ascending order");
define("NBILL_SUPP_DOCS_SORT_DESC", "Sort by this column is descending order");
define("NBILL_SUPP_DOCS_EDIT_EXPENDITURE", "Edit this expenditure record (in a new window)");
define("NBILL_SUPP_DOCS_EDIT_INCOME", "Edit this income record (in a new window)");
define("NBILL_SUPP_DOCS_EDIT_INVOICE", "Edit this invoice record (in a new window)");
define("NBILL_SUPP_DOCS_EDIT_QUOTE", "Edit this quote record (in a new window)");
define("NBILL_SUPP_DOCS_EDIT_CREDIT", "Edit this credit note record (in a new window)");
define("NBILL_SUPP_DOCS_EDIT_CLIENT", "Edit this client record (in a new window)");
define("NBILL_SUPP_DOCS_EDIT_SUPPLIER", "Edit this supplier record (in a new window)");
define("NBILL_SUPP_DOCS_EDIT_ORDER", "Edit this order record (in a new window)");
define("NBILL_SUPP_DOCS_EDIT_PRODUCT", "Edit this product record (in a new window)");
define("NBILL_SUPP_DOCS_FILE_ATTACH_INTRO", "Select a record type, then a record to attach this file to (if you have a lot of records it is easier to navigate to the record first and then attach from there - for example, if you are attaching a file to an invoice, find the invoice in the invoice list, and click the attach button next to the relevant item on the invoice list, then select the file).");
define("NBILL_SUPP_DOCS_RECORD_TYPE", "Record Type");
define("NBILL_SUPP_DOCS_RECORD", "Record");
define("NBILL_SUPP_DOCS_TYPE_EXPENDITURE", "Expenditure");
define("NBILL_SUPP_DOCS_TYPE_INCOME", "Income");
define("NBILL_SUPP_DOCS_TYPE_INVOICE", "Invoices");
define("NBILL_SUPP_DOCS_TYPE_QUOTE", "Quotes");
define("NBILL_SUPP_DOCS_TYPE_CREDIT", "Credit Notes");
define("NBILL_SUPP_DOCS_TYPE_CLIENT", "Clients");
define("NBILL_SUPP_DOCS_TYPE_SUPPLIER", "Suppliers");