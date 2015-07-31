<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_3_1_to_2_3_2()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    $sql[] = "ALTER TABLE `#__nbill_product`
                ADD `download_location_4` VARCHAR(255) NOT NULL DEFAULT '' AFTER `download_link_text_3`,
                ADD `download_link_text_4` VARCHAR(255) NOT NULL DEFAULT '' AFTER `download_location_4`,
                ADD `download_location_5` VARCHAR(255) NOT NULL DEFAULT '' AFTER `download_link_text_4`,
                ADD `download_link_text_5` VARCHAR(255) NOT NULL DEFAULT '' AFTER `download_location_5`,
                ADD `download_location_6` VARCHAR(255) NOT NULL DEFAULT '' AFTER `download_link_text_5`,
                ADD `download_link_text_6` VARCHAR(255) NOT NULL DEFAULT '' AFTER `download_location_6`,
                ADD `download_location_7` VARCHAR(255) NOT NULL DEFAULT '' AFTER `download_link_text_6`,
                ADD `download_link_text_7` VARCHAR(255) NOT NULL DEFAULT '' AFTER `download_location_7`,
                ADD `download_location_8` VARCHAR(255) NOT NULL DEFAULT '' AFTER `download_link_text_7`,
                ADD `download_link_text_8` VARCHAR(255) NOT NULL DEFAULT '' AFTER `download_location_8`,
                ADD `download_location_9` VARCHAR(255) NOT NULL DEFAULT '' AFTER `download_link_text_8`,
                ADD `download_link_text_9` VARCHAR(255) NOT NULL DEFAULT '' AFTER `download_location_9`,
                ADD `download_location_10` VARCHAR(255) NOT NULL DEFAULT '' AFTER `download_link_text_9`,
                ADD `download_link_text_10` VARCHAR(255) NOT NULL DEFAULT '' AFTER `download_location_10`";

    //If upgrading from Lite, we will need to add a couple of columns - don't report errors if they already exist though
    $query = "ALTER TABLE `#__nbill_document` ADD `quote_accept_redirect` VARCHAR(255) NOT NULL DEFAULT ''";
    $nb_database->setQuery($query);
    $nb_database->query();
    $query = "ALTER TABLE `#__nbill_configuration` ADD `supporting_docs_path` VARCHAR( 255 ) NOT NULL DEFAULT ''";
    $nb_database->setQuery($query);
    $nb_database->query();
    $query = "ALTER TABLE `#__nbill_document` CHANGE `auto_create_orders` `auto_create_orders` TINYINT( 4 ) NOT NULL DEFAULT '2'";
    $nb_database->setQuery($query);
    $nb_database->query();

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
    //Gateway
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.3.2
define("NBILL_WARNING_WO_INVOICE", "WARNING! A client has just paid an invoice which had been written off.");
define("NBILL_WARNING_WO_INVOICE_CANCELLED_ORDER", "The written off invoice was related to an order record which has been cancelled - it might be necessary to re-instate the order.");
LANG_ADD;
    edit_language_item("gateway", $text_to_add);
    $text_to_add = array();

    //Main
    $text_to_replace['en-GB'] = <<<LANG_FROM
 with Netshine Hosting
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
.
LANG_TO;
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
Netshine Hosting Client
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
Hosted Client
LANG_TO;
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Products
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.3.2
define("NBILL_PRODUCT_DOWNLOAD_MORE", "More downloadable files");
define("NBILL_DOWNLOAD_LOCATION_4", "Download Location 4");
define("NBILL_INSTR_DOWNLOAD_LOCATION_4", "The ABSOLUTE PATH to the 4th download file. ");
define("NBILL_DOWNLOAD_LINK_TEXT_4", "4th Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_4", "Text to display for the download link for the 4th file.");
define("NBILL_DOWNLOAD_LOCATION_5", "Download Location 5");
define("NBILL_INSTR_DOWNLOAD_LOCATION_5", "The ABSOLUTE PATH to the 5th download file. ");
define("NBILL_DOWNLOAD_LINK_TEXT_5", "5th Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_5", "Text to display for the download link for the 5th file.");
define("NBILL_DOWNLOAD_LOCATION_6", "Download Location 6");
define("NBILL_INSTR_DOWNLOAD_LOCATION_6", "The ABSOLUTE PATH to the 6th download file. ");
define("NBILL_DOWNLOAD_LINK_TEXT_6", "6th Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_6", "Text to display for the download link for the 6th file.");
define("NBILL_DOWNLOAD_LOCATION_7", "Download Location 7");
define("NBILL_INSTR_DOWNLOAD_LOCATION_7", "The ABSOLUTE PATH to the 7th download file. ");
define("NBILL_DOWNLOAD_LINK_TEXT_7", "7th Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_7", "Text to display for the download link for the 7th file.");
define("NBILL_DOWNLOAD_LOCATION_8", "Download Location 8");
define("NBILL_INSTR_DOWNLOAD_LOCATION_8", "The ABSOLUTE PATH to the 8th download file. ");
define("NBILL_DOWNLOAD_LINK_TEXT_8", "8th Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_8", "Text to display for the download link for the 8th file.");
define("NBILL_DOWNLOAD_LOCATION_9", "Download Location 9");
define("NBILL_INSTR_DOWNLOAD_LOCATION_9", "The ABSOLUTE PATH to the 9th download file. ");
define("NBILL_DOWNLOAD_LINK_TEXT_9", "9th Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_9", "Text to display for the download link for the 9th file.");
define("NBILL_DOWNLOAD_LOCATION_10", "Download Location 10");
define("NBILL_INSTR_DOWNLOAD_LOCATION_10", "The ABSOLUTE PATH to the 10th download file. ");
define("NBILL_DOWNLOAD_LINK_TEXT_10", "10th Download Link Text");
define("NBILL_INSTR_DOWNLOAD_LINK_TEXT_10", "Text to display for the download link for the 10th file.");
LANG_ADD;
    edit_language_item("products", $text_to_add);
    $text_to_add = array();

    $text_to_replace['en-GB'] = 'up to 3 files';
    $replace_with['en-GB'] = 'up to 10 files';
    edit_language_item("products", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = '3 download links';
    $replace_with['en-GB'] = '10 download links';
    edit_language_item("products", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();
##### LANGUAGE UPDATE END #####
}