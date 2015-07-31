<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_5_1_to_2_5_2()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    //Allow for gateways that don't take payment immediately (e-check/direct debit)
    $sql[] = "ALTER TABLE `#__nbill_gateway_tx` ADD `payment_pending_until` INT UNSIGNED NOT NULL DEFAULT '0'";
    $sql[] = "ALTER TABLE `#__nbill_gateway_tx` ADD `entity_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `shipping_tax_amount`";

    //Allow for different field types on gateway settings page
    $sql[] = "ALTER TABLE `#__nbill_payment_gateway` ADD `data_type` VARCHAR( 15 ) NOT NULL DEFAULT 'varchar'";
    $sql[] = "ALTER TABLE `#__nbill_payment_gateway` ADD `options` VARCHAR( 255 ) NOT NULL DEFAULT ''";

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
    //Front-end
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.5.2
define("NBILL_RECORD_PENDING_ORDER", "pending order");
define("NBILL_RECORD_ORDER_RENEWAL", "order renewal");
define("NBILL_RECORD_INVOICE", "invoice");
define("NBILL_RECORD_QUOTE", "quote");
define("NBILL_PENDING_PAYMENT_WARNING", "WARNING! A payment has already been authorised for this %1\\\$s and is expected to be complete on or before %2\\\$s. If you authorise another payment, you could end up paying twice! If you are sure the previous payment will not be completed, you can proceed anyway.");
define("NBILL_PROCEED_ANYWAY", "Proceed Anyway");
define("NBILL_INVOICE_DISCOUNTED_ZERO_PAYMENT", "Thank you, your invoice has been marked as paid.");
define("NBILL_INVOICE_DISCOUNTED_ZERO_PAYMENT_NOTES", "Invoice discounted - nothing to pay");
LANG_ADD;
    edit_language_item("frontend", $text_to_add);
    $text_to_add = array();

    //Gateway
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.5.2
define("NBILL_INSTR_GATEWAY_DISPLAY_NAME_2", "Used whenever the gateway name is displayed on your website (eg. when choosing a payment method while paying an invoice or renewing an order).");
define("NBILL_INSTR_GATEWAY_PUBLISHED_2", "Whether or not this gateway should appear in a dropdown list for allowing a user to select a gateway.");
define("NBILL_GATEWAY_SUCCESS_PENDING", "Thank you - your payment instruction was received successfully and is pending confirmation.");
define("NBILL_GATEWAY_EXTRA_FUNCTIONS", "NOTE: This gateway has additional features. %s to access the extra functions for this gateway.");
LANG_ADD;
    edit_language_item("gateway", $text_to_add);
    $text_to_add = array();

    //Invoices
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.5.2
define("NBILL_INVOICE_GEN_DUPLICATE", "The system halted invoice generation for order %1\\\$s as a previous invoice already exists with the same order details (Invoice %2\\\$s).");
define("NBILL_INVOICE_GEN_ORDER_DATE_LOCKED", "Unable to update last/next due dates on order record for order %1\\\$s after generating invoice %2\\\$s. Invoice generation ABORTED so as to avoid creating duplicate invoices. Please manually update the dates on that order record and try again.");
LANG_ADD;
    edit_language_item("invoices", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}
