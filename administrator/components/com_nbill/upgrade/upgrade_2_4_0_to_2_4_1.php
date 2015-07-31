<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_4_0_to_2_4_1()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    //If any clients have contacts associated with them but no primary contact defined, set a default primary contact
    $query = "SELECT entity_id, contact_id FROM #__nbill_entity_contact INNER JOIN #__nbill_entity ON #__nbill_entity_contact.entity_id = #__nbill_entity.id WHERE #__nbill_entity.primary_contact_id = 0 GROUP BY #__nbill_entity.id";
    $nb_database->setQuery($query);
    $primary_contacts = $nb_database->loadObjectList();
    foreach ($primary_contacts as $primary_contact)
    {
        $sql[] = "UPDATE #__nbill_entity SET primary_contact_id = " . intval($primary_contact->contact_id) . " WHERE id = " . intval($primary_contact->entity_id) . " AND primary_contact_id = 0";
    }

    //If there is no offline gateway (due to earlier migration from nBill 1.x), insert it
    $query = "SELECT gateway_id FROM #__nbill_payment_gateway_config WHERE gateway_id = 'offline'";
    $nb_database->setQuery($query);
    $offline_exists = $nb_database->loadResult();
    if (!$offline_exists)
    {
        $sql[] = "INSERT INTO #__nbill_payment_gateway_config (gateway_id, display_name, published, ordering)
                    VALUES ('offline', 'NBILL_ARRANGE_OFFLINE', 0, 0)";
    }

    //Schema for order form pages has been wrong for a while - some databases will be wrong - find out and correct
    $query = "SHOW COLUMNS FROM `#__nbill_order_form_pages` LIKE 'id';";
    $nb_database->setQuery($query);
    $cols = $nb_database->loadObjectList();
    if (!$cols || count($cols) == 0)
    {
        $sql[] = "ALTER TABLE #__nbill_order_form_pages DROP PRIMARY KEY;";
        $sql[] = "ALTER TABLE #__nbill_order_form_pages ADD UNIQUE (`form_id`,`page_no`);";
        $sql[] = "ALTER TABLE #__nbill_order_form_pages ADD `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;";
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
    //Gateway
    $text_to_replace['en-GB'] = 'Please also note that while this feature has been tested fairly thoroughly, it should still be regarded as experimental. Due to rounding differences in the way amounts are calculated, and the way partial payments are dealt with, it might not always yield the desired or expected results. Please also note that while this feature has been tested fairly thoroughly, it should still be regarded as experimental. Due to rounding differences in the way amounts are calculated, and the way partial payments are dealt with, it might not always yield the desired or expected results.';
    $replace_with['en-GB'] = 'Please also note that while this feature has been tested fairly thoroughly, it should still be regarded as experimental. Due to rounding differences in the way amounts are calculated, and the way partial payments are dealt with, it might not always yield the desired or expected results.';
    edit_language_item("gateway", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    //Invoices
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.1
define("NBILL_DOC_SETUP_FEE_WARNING", "WARNING! You have selected a product which has a setup fee defined, however, setup fees are not supported by quotes or invoices. If you want to apply a setup fee to this document, you will need to add it as a separate line item.");
define("NBILL_ADHOC_DONT_BUG", "Don't Bug Me!");
define("NBILL_ADHOC_DONT_BUG_SURE", "This will save a cookie on your computer to suppress the warning message - the message will not be shown again until your cookies are cleared. Are you sure?");
LANG_ADD;
    edit_language_item("invoices", $text_to_add);
    $text_to_add = array();

    //Income
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.1
define("NBILL_TX_CALC_OFF", "Turn calculation off");
define("NBILL_TX_CALC_ON", "Turn calculation on");
LANG_ADD;
    edit_language_item("income", $text_to_add);
    $text_to_add = array();

    //nBill (main)
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.1
define("NBILL_DEPRECATED", "DEPRECATED - WARNING! This feature will be removed in a future version.");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}