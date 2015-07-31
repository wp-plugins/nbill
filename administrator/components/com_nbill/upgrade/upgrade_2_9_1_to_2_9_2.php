<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_9_1_to_2_9_2()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    $sql[] = "UPDATE `#__nbill_configuration` SET `api_url_geo_ip` = 'http://www.telize.com/geoip/##ip##' WHERE id = 1;";

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
    //Configuration
    $text_to_replace['en-GB'] = "Valid values include 'http://freegeoip.net/json/##ip##' and 'http://ip-api.com/json/##ip##'. If either";
    $replace_with['en-GB'] = "Valid values include 'http://www.telize.com/geoip/##ip##', 'http://freegeoip.net/json/##ip##' and 'http://ip-api.com/json/##ip##'. If any";
    edit_language_item("configuration", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    //Products
    $text_to_replace['en-GB'] = 'define("NBILL_PLEASE_SELECT_PRODUCT_DISCOUNT""'; //Typo in previous upgrade script!
    $replace_with['en-GB'] = 'define("NBILL_PLEASE_SELECT_PRODUCT_DISCOUNT"';
    edit_language_item("products", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    //Quote template
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.0
define("NBILL_PRT_QUOTE_TOTAL", "Quote Total");
LANG_ADD;
    edit_language_item("template.qu", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}