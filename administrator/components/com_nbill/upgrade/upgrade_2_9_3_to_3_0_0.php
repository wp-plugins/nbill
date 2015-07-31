<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_9_3_to_3_0_0()
{
    nbf_version::$nbill_version_no = '3.0.1';

    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    $sql[] = "UPDATE `#__nbill_configuration` SET `api_url_eu_vat_rates` = 'http://nbill.co.uk/api/v1/eu_vat_rates.json' WHERE id = 1;";

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
    //nBill
    $text_to_add['en-GB'] = <<<LANG_ADD
define("NBILL_SELECTED_PAYMENT_GATEWAY", "Payment Gateway");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}