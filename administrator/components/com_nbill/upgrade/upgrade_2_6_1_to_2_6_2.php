<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_6_1_to_2_6_2()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    //Allow field control extensions to have their own parameters on the form editor
    $sql[] = "ALTER TABLE `#__nbill_order_form_fields` ADD `extended_params` TEXT NULL DEFAULT NULL";

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
    //Form Editor
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.6.2
define("NBILL_FORM_EDITOR_TAB_EXTENDED", "Extended");
define("NBILL_FORM_EDITOR_EXTENDED_UPDATING", "Updating...");
define("NBILL_FORM_EDITOR_EXTENDED_NONE", "There are no extended properties for this field type");
LANG_ADD;
    edit_language_item("form.editor", $text_to_add);
    $text_to_add = array();

    //Invoices
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.6.2
define("NBILL_INVOICE_CLIENT_CREDIT_PROMPT", "This client account has a credit of %1\\\$s. Would you like to apply a credit amount of %2\\\$s to this invoice? Checking the box below will leave a balance due of <strong>%3\\\$s</strong> on this invoice and a credit balance of <strong>%4\\\$s</strong> on this client's account.) - the credit will be applied when you click on Send.");
define("APPLY_CLIENT_CREDIT", "YES, apply client credit to this invoice before sending.");
define("NBILL_INVOICE_CLIENT_CREDIT_BALANCE_DESC", "%1\\\$s (Credit amount remaining: %2\\\$s)"); //If you don't want to show the credit amount remaining, you can just set this to an empty string ""
LANG_ADD;
    edit_language_item("invoices", $text_to_add);
    $text_to_add = array();

    //Main
    $text_to_replace['en-GB'] = 'a pending order. The order details are as follows';
    $replace_with['en-GB'] = 'a pending order. The order details are as follows (please check to make sure nothing is missing from the saved values)';
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Vendors
    $text_to_replace['en-GB'] = 'The error message returned was: ';
    $replace_with['en-GB'] = 'The error message returned was: %s';
    edit_language_item("vendors", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();
##### LANGUAGE UPDATE END #####
}