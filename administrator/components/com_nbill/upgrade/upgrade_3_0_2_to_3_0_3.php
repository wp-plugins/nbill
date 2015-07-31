<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_3_0_2_to_3_0_3()
{
    nbf_version::$nbill_version_no = '3.0.4';

    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

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
    //Orders
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.0
define("NBILL_ORDERS_ELECTRONIC_CHANGE_TITLE", "Electronic Delivery Change");
define("NBILL_ORDERS_ELECTRONIC_CHANGE_INTRO", "You have changed the electronic delivery status of a product. Your changes have been saved, however, " . NBILL_BRANDING_NAME . " has detected %s order records for clients within the EU that are currently using a tax rate which is not compatible with the electronic delivery status of the product (sales of electronically delivered products within the EU must charge tax at the rate prevailing in the country of the customer - non-electronically delivered products should charge at the rate prevailing in the country of the seller). Please select what action to take below.");
define("NBILL_ORDERS_ELECTRONIC_CHANGE_INTRO_AUTO", "%s order(s) which are set to auto-renew hold an incompatible tax rate. Select one of the following options and click on the 'Save' toolbar button to update these records, or on 'Cancel' to continue without updating any orders.");
define("NBILL_ORDERS_ELECTRONIC_CHANGE_INTRO_MANUAL", "%s order(s) which are NOT set to auto-renew hold an incompatible tax rate. Select one of the following options and click on the 'Save' toolbar button to update these records, or on 'Cancel' to continue without updating any orders.");
define("NBILL_ORDERS_ELECTRONIC_RECALC_TAX_AND_NET", "Re-calculate the tax and net amounts, keeping the gross amount the same");
define("NBILL_ORDERS_ELECTRONIC_RECALC_TAX_AND_GROSS", "Re-calculate the tax and gross amounts, keeping the net amount the same");
define("NBILL_ORDERS_ELECTRONIC_DO_NOTHING", "Do not update any of these orders");
LANG_ADD;
    edit_language_item("orders", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}