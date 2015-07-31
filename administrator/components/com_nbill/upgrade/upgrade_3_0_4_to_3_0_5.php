<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_3_0_4_to_3_0_5()
{
    nbf_version::$nbill_version_no = '3.0.5';

    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `never_hide_quantity` TINYINT NOT NULL DEFAULT '0' ;";

    foreach ($sql as $query)
    {
        $nb_database->setQuery($query);
        $nb_database->query();
        if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
        {
            nbf_globals::$db_errors[] = $nb_database->_errorMsg . ' (SQL: ' . $nb_database->_sql . ')';
        }
    }

##### LANGUAGE UPDATE START #####
    //Front end
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.5
define("NBILL_FE_QUOTE_NO_CORRE", "There is no previous correspondence to display.");
LANG_ADD;
    edit_language_item("frontend", $text_to_add);
    $text_to_add = array();

    //nBill
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.5
define("NBILL_3_INSTALL_ERROR", "Sorry, it looks like nBill failed to install correctly! Please try uninstalling and re-installing. If that does not help, please refer to the troubleshooting section of the documentation at <a href=\\"http://" . NBILL_BRANDING_DOCUMENTATION . "\\">" . NBILL_BRANDING_DOCUMENTATION . "</a>.<br /><br /><a href=\\"%s\\">Return to Home Page</a>");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();

    //Orders
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.5
define("NBILL_ORDERS_AUTO_CREATE_INVOICE", "Auto Create Invoice?");
define("NBILL_ORDERS_AUTO_CREATE_INVOICE_HELP", "Whether or not to generate invoices for this order.");
define("NBILL_ORDERS_AUTO_CREATE_INCOME", "Auto Create Income?");
define("NBILL_ORDERS_AUTO_CREATE_INCOME_HELP", "Whether or not to generate income records when payments are received for this order.");
define("NBILL_ORDERS_GATEWAY_TXN_ID", "Gateway Transaction ID");
define("NBILL_ORDERS_GATEWAY_TXN_ID_HELP", "Transaction reference number used by the payment gateway for this order. You should normally leave this alone, as it will be handled automatically, but in rare cases where payments need to be reassigned to a different order record, it can be updated.");
LANG_ADD;
    edit_language_item("orders", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}