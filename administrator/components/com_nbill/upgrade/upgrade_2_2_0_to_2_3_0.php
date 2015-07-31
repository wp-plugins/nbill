<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_2_0_to_2_3_0()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    //Remove duplicate admin via fe menu link
    $sql[] = "DELETE FROM `#__nbill_menu` WHERE `text` = 'NBILL_MNU_USER_ADMIN'";
    $sql[] = "INSERT INTO `#__nbill_menu` (`id`,`parent_id`,`ordering`,`text`,`description`,`image`,`url`,`published`,`favourite`) VALUES (55 , '21', '7', 'NBILL_MNU_USER_ADMIN', 'NBILL_MNU_USER_ADMIN_DESC', '[NBILL_FE]/images/icons/user_admin.gif', '[NBILL_ADMIN]&action=user_admin', '1', '0')";

    //Default to only creating orders for quotes if payment frequency is recurring
    $sql[] = "ALTER TABLE `#__nbill_document` CHANGE `auto_create_orders` `auto_create_orders` TINYINT( 4 ) NOT NULL DEFAULT '2'";

    //Make sure offline gateway is available
    $query = "SELECT gateway_id FROM #__nbill_payment_gateway_config WHERE gateway_id = 'offline'";
    $nb_database->setQuery($query);
    if (!$nb_database->loadResult())
    {
        $sql[] = "INSERT INTO `#__nbill_payment_gateway_config` (`gateway_id`, `display_name`, `ordering`, `published`) VALUES ('offline', 'NBILL_ARRANGE_OFFLINE', '0', '0');";
    }

    //If extension form events table was not created on install of last version, create it now
    $sql[] = "CREATE TABLE IF NOT EXISTS `#__nbill_extension_form_events` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `form_id` int(10) unsigned NOT NULL DEFAULT '0', `form_event_name` varchar(50) NOT NULL DEFAULT '', `extension_name` varchar(50) NOT NULL DEFAULT '', `code_to_run` text, PRIMARY KEY (`id`)) AUTO_INCREMENT=1;";

    //Allow front-end links to extensions
    $sql[] = "CREATE TABLE IF NOT EXISTS `#__nbill_extensions_links` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`extension_name` VARCHAR( 100 ) NOT NULL DEFAULT '',
`link_url` VARCHAR( 255 ) NOT NULL DEFAULT '',
`link_text` VARCHAR( 255 ) NOT NULL DEFAULT '',
`link_description` VARCHAR( 255 ) NOT NULL DEFAULT '',
`ordering` INT NOT NULL DEFAULT '0',
`published` TINYINT NOT NULL DEFAULT '1')";

    //Clear out any orphaned miscellaneous ledger or product category records
    $sql[] = "DELETE FROM `#__nbill_nominal_ledger` WHERE vendor_id = 0";
    $sql[] = "DELETE FROM `#__nbill_product_category` WHERE vendor_id = 0";

    //Allow redirect on quote acceptance
    $sql[] = "ALTER TABLE `#__nbill_document` ADD `quote_accept_redirect` VARCHAR(255) NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_order_form` ADD `quote_accept_redirect` VARCHAR(255) NOT NULL DEFAULT ''";

    //Just in case this is left lying around
    $sql[] = "UPDATE `#__nbill_xref_field_type` SET description = 'NBILL_FLD_DOMAIN_LOOKUP' WHERE description = 'NBILL_FLD_JWHOIS_LOOKUP'";

    //Delete any duplicate entries in the EU countries table
    $sql[] = "DELETE FROM `#__nbill_xref_eu_country_codes` WHERE id > 27";
    $sql[] = "ALTER TABLE `#__nbill_xref_eu_country_codes` DROP INDEX `code`, ADD UNIQUE `code` ( `code` )";

    //Allow credit notes to be linked to invoices
    $sql[] = "ALTER TABLE `#__nbill_document` CHANGE `related_quote_id` `related_document_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0'";

    //New supporting documents feature
    $sql[] = "CREATE TABLE IF NOT EXISTS `#__nbill_supporting_docs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file_path` varchar(255) NOT NULL DEFAULT '',
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `associated_doc_type` char(2) NOT NULL DEFAULT 'EX',
  `associated_doc_id` int(11) unsigned NOT NULL DEFAULT '0',
  `client_access` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`))";
    $sql[] = "INSERT INTO `#__nbill_menu` VALUES (56, 15, 4, 'NBILL_MNU_SUPPORTING_DOCS', 'NBILL_MNU_SUPPORTING_DOCS_DESC', '[NBILL_FE]/images/icons/supporting_docs.gif', '[NBILL_ADMIN]&action=supporting_docs', 1, 0);";
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `supporting_docs_path` VARCHAR( 255 ) NOT NULL DEFAULT ''";

    //Delete orphan documents (if a vendor has been deleted, some document records may have been left behind)
    $query = "SELECT #__nbill_document.id FROM #__nbill_document LEFT JOIN #__nbill_vendor ON #__nbill_document.vendor_id = #__nbill_vendor.id WHERE #__nbill_vendor.id IS NULL";
    $nb_database->setQuery($query);
    $orphan_documents = $nb_database->loadResultArray();
    if ($orphan_documents && count($orphan_documents) > 0)
    {
        $task = 'silent';
        $_REQUEST['action'] = 'silent';
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/invoices.php");
        deleteInvoices($orphan_documents);
    }

    foreach ($sql as $query)
    {
        $nb_database->setQuery($query);
        $nb_database->query();
        if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
        {
            nbf_globals::$db_errors[] = $nb_database->_errorMsg;
        }
    }

##### LANGUAGE UPDATE START #####
    //Backup
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.3.0
define("NBILL_ERR_BACKUP_VERSION_INCOMPATIBLE", "The file you specified was backed up using an incompatible version of " . NBILL_BRANDING_NAME . " (%1\\\$s). In order to restore a backup in this version of " . NBILL_BRANDING_NAME . ", the version number of " . NBILL_BRANDING_NAME . " that was used to create the backup must be between %2\\\$s and %3\\\$s (inclusive)");
LANG_ADD;
    edit_language_item("backup", $text_to_add);
    $text_to_add = array();

    //Credits
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.3.0
define("NBILL_CREDITS_REFUND_INVOICE", "Refund of invoice %s");
define("NBILL_CREDITS_FROM_INVOICE_WARNING", "The values on this credit note have been automatically pre-populated based on the selected invoice. For a partial refund, you will need to edit the amounts. Please also check the nominal ledger code - if you use a different code for expenditure than you do for income you might want to change it.");
LANG_ADD;
    edit_language_item("credits", $text_to_add);
    $text_to_add = array();

    //Display Options
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.3.0
define("NBILL_DISPLAY_QUOTE_LOGIN_TO_ACCEPT", "Login required for acceptance/rejection");
define("NBILL_DISPLAY_QUOTE_LOGIN_TO_ACCEPT_DESC", "Whether or not the client must be logged in before they can accept or reject a quote or add further information to the quote correspondence. WARNING! If you set this to 'no', it will allow anyone to access the quote and e-mail you if they know or guess the quote ID.");
define("NBILL_DISPLAY_ADDITIONAL_LINKS", "Additional Links");
define("NBILL_DISPLAY_EXTENSION_LINKS", "Extension Links");
define("NBILL_DISPLAY_NO_EXTENSION_LINKS", "There are no extension links to display. Links will only appear here if you have one or more extensions installed which offer features for your website front end.");
define("NBILL_DISPLAY_EXTENSION_LINKS_INTRO", "These are links to the front-end features of your installed extension(s). You can amend the link text and description, re-order the links and use the 'published' checkbox next to each link to hide or show the link.");
define("NBILL_LINK_ORDERING", "Ordering");
define("NBILL_LINK_PUBLISHED", "Published?");
LANG_ADD;
    edit_language_item("display", $text_to_add);
    $text_to_add = array();

    //Extensions
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.3.0
define("NBILL_EXTENSION_UPGRADED", "Extension '%s' upgraded successfully!");
LANG_ADD;
    edit_language_item("extensions", $text_to_add);
    $text_to_add = array();

    //Front-end
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.3.0
define("NBILL_FE_SUPPLIER", "Supplier");
LANG_ADD;
    edit_language_item("frontend", $text_to_add);
    $text_to_add = array();

    //Gateway
    $text_to_replace['en-GB'] = <<<LANG_FROM
//Note: You can use the following placeholders to represent transaction data which can be output in the thank you message (for use when integrating with affiliate tracking programs or to provide a custom payment confirmation screen): ##TX_ID## (transaction ID); ##AMOUNT## (amount of payment); ##CURRENCY## (currency used); ##ORDER_ID## (order record ID number, if known); ##ORDER_NO## (order number, if known). A transaction ID will always exist, but an order record might not. //Note: You can use the following placeholders to represent transaction data which can be output in the thank you message (for use when integrating with affiliate tracking programs or to provide a custom payment confirmation screen): ##TX_ID## (transaction ID); ##AMOUNT## (amount of payment); ##CURRENCY## (currency used); ##ORDER_ID## (order record ID number, if known); ##ORDER_NO## (order number, if known). A transaction ID will always exist, but an order record might not. //Note: You can use the following placeholders to represent transaction data which can be output in the thank you message (for use when integrating with affiliate tracking programs or to provide a custom payment confirmation screen): ##TX_ID## (transaction ID); ##AMOUNT## (amount of payment); ##CURRENCY## (currency used); ##ORDER_ID## (order record ID number, if known); ##ORDER_NO## (order number, if known). A transaction ID will always exist, but an order record might not. //Note: You can use the following placeholders to represent transaction data which can be output in the thank you message (for use when integrating with affiliate tracking programs or to provide a custom payment confirmation screen): ##TX_ID## (transaction ID); ##AMOUNT## (amount of payment); ##CURRENCY## (currency used); ##ORDER_ID## (order record ID number, if known); ##ORDER_NO## (order number, if known). A transaction ID will always exist, but an order record might not. //Note: You can use the following placeholders to represent transaction data which can be output in the thank you message (for use when integrating with affiliate tracking programs or to provide a custom payment confirmation screen): ##TX_ID## (transaction ID); ##AMOUNT## (amount of payment); ##CURRENCY## (currency used); ##ORDER_ID## (order record ID number, if known); ##ORDER_NO## (order number, if known). A transaction ID will always exist, but an order record might not. //Note: You can use the following placeholders to represent transaction data which can be output in the thank you message (for use when integrating with affiliate tracking programs or to provide a custom payment confirmation screen): ##TX_ID## (transaction ID); ##AMOUNT## (amount of payment); ##CURRENCY## (currency used); ##ORDER_ID## (order record ID number, if known); ##ORDER_NO## (order number, if known). A transaction ID will always exist, but an order record might not. //Note: You can use the following placeholders to represent transaction data which can be output in the thank you message (for use when integrating with affiliate tracking programs or to provide a custom payment confirmation screen): ##TX_ID## (transaction ID); ##AMOUNT## (amount of payment); ##CURRENCY## (currency used); ##ORDER_ID## (order record ID number, if known); ##ORDER_NO## (order number, if known). A transaction ID will always exist, but an order record might not. //Note: You can use the following placeholders to represent transaction data which can be output in the thank you message (for use when integrating with affiliate tracking programs or to provide a custom payment confirmation screen): ##TX_ID## (transaction ID); ##AMOUNT## (amount of payment); ##CURRENCY## (currency used); ##ORDER_ID## (order record ID number, if known); ##ORDER_NO## (order number, if known). A transaction ID will always exist, but an order record might not. //Note: You can use the following placeholders to represent transaction data which can be output in the thank you message (for use when integrating with affiliate tracking programs or to provide a custom payment confirmation screen): ##TX_ID## (transaction ID); ##AMOUNT## (amount of payment); ##CURRENCY## (currency used); ##ORDER_ID## (order record ID number, if known); ##ORDER_NO## (order number, if known). A transaction ID will always exist, but an order record might not. //Note: You can use the following placeholders to represent transaction data which can be output in the thank you message (for use when integrating with affiliate tracking programs or to provide a custom payment confirmation screen): ##TX_ID## (transaction ID); ##AMOUNT## (amount of payment); ##CURRENCY## (currency used); ##ORDER_ID## (order record ID number, if known); ##ORDER_NO## (order number, if known). A transaction ID will always exist, but an order record might not.
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//Note: You can use the following placeholders to represent transaction data which can be output in the thank you message (for use when integrating with affiliate tracking programs or to provide a custom payment confirmation screen): ##TX_ID## (transaction ID); ##AMOUNT## (amount of payment); ##CURRENCY## (currency used); ##ORDER_ID## (order record ID number, if known); ##ORDER_NO## (order number, if known). A transaction ID will always exist, but an order record might not.
LANG_TO;
    edit_language_item("gateway", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Housekeeping
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.3.0
define("NBILL_HOUSEKEEPING_TYPE_13", "Supporting Documents");
define("NBILL_HOUSEKEEPING_TYPE_13_HELP", "Deletes all supporting document files that are older than the specified period.");
LANG_ADD;
    edit_language_item("housekeeping", $text_to_add);
    $text_to_add = array();

    //Invoices
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.3.0
define("NBILL_REFUND_THIS_INVOICE", "Refund this invoice");
define("NBILL_INVOICE_RELATED_DOCUMENTS", "Related Document(s):");
LANG_ADD;
    edit_language_item("invoices", $text_to_add);
    $text_to_add = array();

    //Main
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.3.0
define("NBILL_MNU_SUPPORTING_DOCS", "Supporting Documents");
define("NBILL_MNU_SUPPORTING_DOCS_DESC", "Manage Supporting Documents (file attachments)");
define("NBILL_TB_NEW_FOLDER", "New Folder");
define("NBILL_TB_UPLOAD", "Upload File(s)");
define("NBILL_TB_REFRESH", "Refresh List");
define("NBILL_ATTACHMENTS", "Attachments");
define("NBILL_NEW_ATTACHMENT", "Add New Attachment");
define("NBILL_DETACH", "Detach");
define("NBILL_DELETE", "Delete");
define("NBILL_DETACH_SURE", "Are you sure you want to detach this file from this record? (This will not delete the file, nor the record)");
define("NBILL_DELETE_FILE_SURE", "Are you sure you want to delete the file \'%s\'?");
define("NBILL_DELETE_FILE_FAILED", "Sorry, '%s' coult not be deleted. Try using FTP or a file manager instead.");
define("NBILL_DOC_TYPE_INVOICE", "Invoice");
define("NBILL_DOC_TYPE_CREDIT", "Credit Note");
define("NBILL_DOC_TYPE_QUOTE", "Quote");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();

    //Quotes
    $text_to_replace['en-GB'] = <<<LANG_FROM
//Version 2.3.0
define("NBILL_QUOTE_ORDERS_IF_RECURRING", "Only if recurring");
define("NBILL_QUOTE_ACCEPT_REDIRECT", "Quote Accept Redirect");
define("NBILL_INSTR_QUOTE_ACCEPT_REDIRECT", "If you want to redirect to a certain page when this quote is accepted (or partially accepted), enter the full URL here (note: this redirect will not happen if payment is required to accept the quote AND the client selects to pay offline, as in that case the quote is not marked as accepted until payment is recorded by an administrator).");

LANG_FROM;
    $replace_with['en-GB'] = '';
    edit_language_item("quotes", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
?>

//Version 2.2.0
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO

//Version 2.2.0
LANG_TO;
    edit_language_item("quotes", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_QUOTE_ITEM_ACCEPTED",
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_QUOTE_IS_ITEM_ACCEPTED",
LANG_TO;
    edit_language_item("quotes", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.3.0
define("NBILL_QUOTE_ORDERS_IF_RECURRING", "Only if recurring");
define("NBILL_QUOTE_ACCEPT_REDIRECT", "Quote Accept Redirect");
define("NBILL_INSTR_QUOTE_ACCEPT_REDIRECT", "If you want to redirect to a certain page when this quote is accepted (or partially accepted), enter the full URL here (note: this redirect will not happen if payment is required to accept the quote AND the client selects to pay offline, as in that case the quote is not marked as accepted until payment is recorded by an administrator).");
LANG_ADD;
    edit_language_item("quotes", $text_to_add);
    $text_to_add = array();

    //Quote Request
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.3.0
define("NBILL_FORM_QUOTE_ORDERS_IF_RECURRING", "Only if recurring");
define("NBILL_FORM_DEFAULT_QUOTE_ACCEPT_REDIRECT", "Default Quote Accept Redirect");
define("NBILL_INSTR_FORM_DEFAULT_QUOTE_ACCEPT_REDIRECT", "URL to redirect to by default when quotes based on this form are accepted (can be overridden on the quote record itself if required). Note: this redirect will not happen if payment is required to accept the quote AND the client selects to pay offline, as in that case the quote will not be marked as accepted until payment is recorded by an administrator.");
LANG_ADD;
    edit_language_item("quote_request", $text_to_add);
    $text_to_add = array();

    //Transaction report
    $text_to_replace['en-GB'] = <<<LANG_FROM
 The 'Balance' column just refers to the net profit/loss for the given date range, and bears no relation to the balance in your bank account!
LANG_FROM;
    $replace_with['en-GB'] = '';
    edit_language_item("transactions", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_TRANSACTIONS_INTRO", "This is a list of all of the income and expenditure items that have been recorded for the given date range. No income or expenditure items are excluded, but this report does not include any unpaid invoices.");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_TRANSACTIONS_INTRO", "This is a list of all of the income and expenditure items that have been recorded for the given date range. No income or expenditure items are excluded, but this report does not include any unpaid invoices. The 'Balance' column just refers to the net profit/loss for the given date range, and bears no relation to the balance in your bank account!");
LANG_TO;
    edit_language_item("transactions", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();
##### LANGUAGE UPDATE END #####

    //Re-instate [CMS] default value for editor selection in nBill config file
    $text_to_replace = "public static \$editor = 'nicEdit';";
    $replace_with = "public static \$editor = '[CMS]';";
    $content = @file_get_contents(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.config.php");
    if (nbf_common::nb_strpos($content, $replace_with) === false)
    {
        $content = str_replace($text_to_replace, $replace_with, $content);
        @file_put_contents(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.config.php", $content);
    }
    $text_to_replace = array();
    $replace_with = array();
}
