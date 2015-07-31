<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_3_1_0_to_3_1_1()
{
    nbf_version::$nbill_version_no = '3.1.1';

    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    $sql[] = "ALTER TABLE `#__nbill_extensions_menu` CHANGE `id` `parent_id` VARCHAR( 100 ) NOT NULL DEFAULT '';";
    $sql[] = "ALTER TABLE `#__nbill_extensions_menu` CHANGE `parent_id` `parent_id` VARCHAR( 100 ) NOT NULL DEFAULT '-1';";
    $sql[] = "ALTER TABLE `#__nbill_extensions` CHANGE `extension_title` `extension_title` VARCHAR( 255 ) NOT NULL DEFAULT '';";
    $sql[] = "ALTER TABLE `#__nbill_extensions` CHANGE `extension_description` `extension_description` TEXT NULL DEFAULT NULL ;";
    $sql[] = "ALTER TABLE `#__nbill_extensions` CHANGE `setup_filename` `setup_filename` VARCHAR( 255 ) NOT NULL DEFAULT '';";
    $sql[] = "ALTER TABLE `#__nbill_extensions` CHANGE `gateway_id` `gateway_id` VARCHAR( 100 ) NOT NULL DEFAULT '';";
    $sql[] = "ALTER TABLE `#__nbill_extension_form_events` CHANGE `form_event_name` `form_event_name` VARCHAR( 100 ) NOT NULL DEFAULT '';";
    $sql[] = "ALTER TABLE `#__nbill_extension_form_events` CHANGE `extension_name` `extension_name` VARCHAR( 100 ) NOT NULL DEFAULT '';";

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
//Template
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.1.1
define("NBILL_PRT_ZERO_RATED", "This is a zero-rated EU intra-community supply.");
LANG_ADD;
    edit_language_item("template.in", $text_to_add);
    $text_to_add = array();

//Main
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.1.1
define("NBILL_WARNING_VERSION_CHECK_OFF", "WARNING! Automatic version checking is disabled. You will not be notified when new versions of " . NBILL_BRANDING_NAME . " are available. <a href=\"%1\\\$s\">" . NBILL_CLICK_HERE . "</a> to enable automatic version checking, or <a href=\"%2\\\$s\">" . NBILL_CLICK_HERE . "</a> to suppress this warning but leave version checking switched off.");
define("NBILL_VERSION_CHECKING_OFF_CONFIRM", "A cookie has been saved on your browser to suppress the warning message. If you clear your cookies, the warning will be shown again. Please visit the " . NBILL_BRANDING_NAME . " forum regularly to check for updates.");
define("NBILL_WARNING_VAT_RATE_CHECK_OFF", "WARNING! Automatic VAT rate checking is disabled. If an EU VAT rate changes, your system will NOT be updated automatically. <a href=\"%1\\\$s\">" . NBILL_CLICK_HERE . "</a> to enable automatic VAT rate updates, or <a href=\"%2\\\$s\">" . NBILL_CLICK_HERE . "</a> to suppress this warning but leave automatic VAT rate updates switched off.");
define("NBILL_VAT_RATE_CHECKING_OFF_CONFIRM", "A cookie has been saved on your browser to suppress the warning message. If you clear your cookies, the warning will be shown again. If you sell digital goods within the EU, please ensure you keep the VAT rates up-to-date manually.");
LANG_ADD;
    edit_language_item("main", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}