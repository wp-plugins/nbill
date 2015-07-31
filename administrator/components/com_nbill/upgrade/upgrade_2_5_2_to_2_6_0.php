<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_5_2_to_2_6_0()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    //Allow for storage of PSP reference (for use with transaction search feature)
    $sql[] = "ALTER TABLE `#__nbill_gateway_tx` ADD `psp_reference` VARCHAR( 100 ) NOT NULL DEFAULT ''";

    //Update Paypal (remove breakdown option and use new field controls for gateway settings)
    $sql[] = "DELETE FROM #__nbill_payment_gateway WHERE gateway_id = 'paypal' AND g_key = 'include_breakdown'";
    $sql[] = "UPDATE #__nbill_payment_gateway SET `data_type` = 'boolean' WHERE gateway_id = 'paypal' AND g_key = 'use_sandbox'";
    $sql[] = "UPDATE #__nbill_payment_gateway SET `data_type` = 'boolean' WHERE gateway_id = 'paypal' AND g_key = 'sra'";
    $sql[] = "UPDATE #__nbill_payment_gateway SET `data_type` = 'boolean' WHERE gateway_id = 'paypal' AND g_key = 'add_debug_info'";
    $sql[] = "UPDATE #__nbill_payment_gateway SET `data_type` = 'boolean' WHERE gateway_id = 'paypal' AND g_key = 'use_curl'";
    $sql[] = "UPDATE #__nbill_payment_gateway SET `data_type` = 'boolean' WHERE gateway_id = 'paypal' AND g_key = 'verify_callback'";

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
    //nBill
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.6.0
define("NBILL_TXN_TYPE_EXPENDITURE", "Expenditure");
define("NBILL_TXN_PAYMENT_NO", "Payment Number");
define("NBILL_TXN_SUPPLIER", "Supplier");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}