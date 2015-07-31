<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_4_1_to_2_5_0()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    //Add column to remember custom component names (if used with branding file), so we can uninstall cleanly
    $sql[] = "ALTER TABLE `#__nbill_license` ADD `custom_component_names` VARCHAR( 255 ) NOT NULL DEFAULT ''";

    //Add payment instructions field for offline payment of invoices
    $sql[] = "ALTER TABLE `#__nbill_vendor` ADD `invoice_offline_pay_inst` TEXT NULL AFTER `payment_instructions`";

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
    //Front End
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.5.0
define("NBILL_DEFAULT_INVOICE_OFFLINE_PAY_INST", "Please refer to the payment instructions on the invoice to arrange payment by bank transfer or through the mail. Thank you.");
LANG_ADD;
    edit_language_item("frontend", $text_to_add);
    $text_to_add = array();

    //Vendors
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.5.0
define("NBILL_INVOICE_PAY_INST", "Invoice Offline Payment Instructions");
define("NBILL_INSTR_INVOICE_PAY_INST", "If the special 'offline' payment gateway is published, you can specify here how your client can make an offline payment when paying for an invoice.");
LANG_ADD;
    edit_language_item("vendors", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}