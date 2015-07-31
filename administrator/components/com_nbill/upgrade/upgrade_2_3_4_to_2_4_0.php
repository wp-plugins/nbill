<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_3_4_to_2_4_0()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    //Allow redirect when offline payment is selected
    $sql[] = "ALTER TABLE `#__nbill_order_form` ADD `offline_payment_redirect` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `attach_to_email` ";
    $sql[] = "ALTER TABLE `#__nbill_document` ADD `form_id` INT UNSIGNED NOT NULL DEFAULT '0'";

    //Allow selection of colour scheme
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `title_colour` VARCHAR(7) NOT NULL DEFAULT '366999' AFTER `email_invoice_option` ,
                ADD `heading_bg_colour` VARCHAR(7) NOT NULL DEFAULT '366999' AFTER `title_colour` ,
                ADD `heading_fg_colour` VARCHAR(7) NOT NULL DEFAULT 'ffffff' AFTER `heading_bg_colour`";

    //New translation feature
    $sql[] = "CREATE TABLE IF NOT EXISTS #__nbill_translation (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`language` CHAR( 7 ) NOT NULL DEFAULT '',
`source_table` VARCHAR( 50 ) NOT NULL DEFAULT '',
`source_column` VARCHAR( 50 ) NOT NULL DEFAULT '',
`source_pk` INT UNSIGNED NOT NULL DEFAULT '0',
`value` TEXT NULL DEFAULT NULL ,
`published` TINYINT NOT NULL DEFAULT '1',
INDEX ( `source_pk` )
)";
    $sql[] = "REPLACE INTO #__nbill_menu (`id`, `parent_id`, `ordering`, `text`, `description`, `image`, `url`, `published`, `favourite`)
                                        VALUES (57, '2', '14', 'NBILL_MNU_TRANSLATION', 'NBILL_MNU_TRANSLATION_DESC', '[NBILL_FE]/images/icons/translation.png',
                                        '[NBILL_ADMIN]&action=translation', '1', '0');";

    //Site ID Hash, encrypt posted data in pending orders
    $sql[] = "ALTER TABLE `#__nbill_license` ADD `site_id_hash` VARCHAR( 100 ) NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_license` ADD `temp_data` TEXT NULL";
    $sql[] = "UPDATE #__nbill_license SET site_id_hash = '" . md5(uniqid("C", true)) . "' WHERE id = 1";

    foreach ($sql as $query)
    {
        $nb_database->setQuery($query);
        $nb_database->query();
        if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
        {
            nbf_globals::$db_errors[] = $nb_database->_errorMsg;
        }
    }

    //Encrypt existing pending orders (above table alterations need to be performed first)
    $sql = array();
    $query = "SELECT id, posted_values FROM #__nbill_pending_orders ORDER BY id DESC LIMIT 250";
    $nb_database->setQuery($query);
    $pending_posts = $nb_database->loadObjectList();
    if ($pending_posts)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/license.nbill.php");
        foreach ($pending_posts as $pending_post)
        {
            if (nb_password_decrypt($pending_post->posted_values) == $pending_post->posted_values)
            {
                $encrypted = nb_password_encrypt($pending_post->posted_values);
                if (strlen($encrypted) > strlen($pending_post->posted_values))
                {
                    $sql[] = "UPDATE #__nbill_pending_orders SET posted_values = '" . nb_password_encrypt($pending_post->posted_values) . "' WHERE id = " . intval($pending_post->id);
                }
            }
        }
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
    //Configuration
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.0
define("NBILL_CFG_TITLE_COLOUR", "Document Title Colour");
define("NBILL_CFG_INSTR_TITLE_COLOUR", "Colour to use for titles, totals, and links on documents (invoices, credit notes, quotes)");
define("NBILL_CFG_HEADING_BG_COLOUR", "Document Heading Background Colour");
define("NBILL_CFG_INSTR_HEADING_BG_COLOUR", "Colour to use for the background of table headings on documents (invoices, credit notes, quotes)");
define("NBILL_CFG_HEADING_FG_COLOUR", "Document Heading Foreground Colour");
define("NBILL_CFG_INSTR_HEADING_FG_COLOUR", "Colour to use for the text of table headings on documents (invoices, credit notes, quotes)");
LANG_ADD;
    edit_language_item("configuration", $text_to_add);
    $text_to_add = array();

    //E-mail
    $text_to_replace['en-GB'] = "If your reader can't open the attachment, please save the file and open it from your computer. If your reader can't open the attachment, please save the file and open it from your computer. ";
    $replace_with['en-GB'] = "If your reader can't open the attachment, please save the file and open it from your computer. ";
    edit_language_item("email", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    //Expenditure
    $text_to_add['en-GB'] = <<<LANG_ADD
define("NBILL_EXPENDITURE_PAYMENT_TITLE", "PAYMENT");
define("NBILL_EXPENDITURE_PAYMENT_INTRO", "This payment slip confirms that a payment was made as follows:");
define("NBILL_EXPENDITURE_NOT_YET_ASSIGNED", "Not Yet Assigned");
define("NBILL_EXPENDITURE_RE_CREDIT", "Credit Note %s, Dated %s");
define("NBILL_EXPENDITURE_RE_CREDITS", "This payment was in relation to the following credit note(s):");
define("NBILL_EXPENDITURE_PAID_FOR", "This payment was in relation to:");
define("NBILL_EXPENDITURE_PAID", "Paid.");
define("NBILL_EXPENDITURE_PRINTER_FRIENDLY", "Printer Friendly Payment Slip");
LANG_ADD;
    edit_language_item("expenditure", $text_to_add);
    $text_to_add = array();

    //Invoices
    $text_to_add['en-GB'] = <<<LANG_ADD
define("NBILL_DOC_NEW_INVOICE_WARNING", "You are creating a new ad-hoc invoice. This can only be used for one-off payment amounts. If you want recurring invoices, you need to create an %sorder record%s instead.");
LANG_ADD;
    edit_language_item("invoices", $text_to_add);
    $text_to_add = array();

    //IO
    $text_to_replace['en-GB'] = 'define("NBILL_CLIENT_CSV_IMPORT_NEW_CLIENTS", "%s Client records were created or updated.");';
    $replace_with['en-GB'] = 'define("NBILL_CLIENT_CSV_IMPORT_NEW_CLIENTS", "%s Client and/or Contact records were created or updated.");';
    edit_language_item("io", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    //nBill
    $text_to_add['en-GB'] = <<<LANG_ADD
define("NBILL_MNU_TRANSLATION", "Translation");
define("NBILL_MNU_TRANSLATION_DESC", "Manage multi-language translation of admin-supplied content");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();

    //Quote Request Forms
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.0
define("NBILL_ORDER_FORM_OFFLINE_PAYMENT_REDIRECT_QUOTE", "Offline Payment Redirect");
define("NBILL_INSTR_ORDER_FORM_OFFLINE_PAYMENT_REDIRECT_QUOTE", "If an offline payment method is selected when the customer accepts the resulting quote online, you can optionally redirect the user to another page on acceptance (eg. an article telling the customer how to pay). This will only take effect if 'payment required to accept' is set to 'yes' (otherwise, the quote accept redirect or thank you message will be used instead).");
LANG_ADD;
    edit_language_item("quote_request", $text_to_add);
    $text_to_add = array();

    //Order Forms
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.0
define("NBILL_ORDER_FORM_OFFLINE_PAYMENT_REDIRECT", "Offline Payment Redirect");
define("NBILL_INSTR_ORDER_FORM_OFFLINE_PAYMENT_REDIRECT", "If an offline payment method is selected, you can optionally redirect the user to another page when the form is processed (eg. an article telling the customer how to pay). This will only take effect if 'pending until paid' is set to 'yes' (otherwise, the order will be completed immediately and the order complete redirect or thank you message will be used instead).");
LANG_ADD;
    edit_language_item("orderforms", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}