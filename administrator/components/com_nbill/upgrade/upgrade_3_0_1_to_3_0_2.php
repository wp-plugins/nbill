<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_3_0_1_to_3_0_2()
{
    nbf_version::$nbill_version_no = '3.0.2';

    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('4', 'paypal', 'ssl_cipher', '', 'NBILL_PAYPAL_SSL_CIPHER', 'NBILL_PAYPAL_SSL_CIPHER_HELP', '0', '1', '14', 'varchar');";
    if (!index_exists_on_column('#__nbill_document_items', 'document_id')) {
        $sql[] = "ALTER TABLE `#__nbill_document_items` ADD INDEX(`document_id`);";
    }
    if (!index_exists_on_column('#__nbill_entity', 'primary_contact_id')) {
        $sql[] = "ALTER TABLE `#__nbill_entity` ADD INDEX(`primary_contact_id`);";
    }
    if (!index_exists_on_column('#__nbill_orders', 'vendor_id')) {
        $sql[] = "ALTER TABLE `#__nbill_orders` ADD INDEX(`vendor_id`);";
    }
    if (!index_exists_on_column('#__nbill_orders', 'client_id')) {
        $sql[] = "ALTER TABLE `#__nbill_orders` ADD INDEX(`client_id`);";
    }
    if (!index_exists_on_column('#__nbill_orders', 'product_id')) {
        $sql[] = "ALTER TABLE `#__nbill_orders` ADD INDEX(`product_id`);";
    }
    if (!index_exists_on_column('#__nbill_orders', 'shipping_id')) {
        $sql[] = "ALTER TABLE `#__nbill_orders` ADD INDEX(`shipping_id`);";
    }
    if (!index_exists_on_column('#__nbill_orders', 'start_date')) {
        $sql[] = "ALTER TABLE `#__nbill_orders` ADD INDEX(`start_date`);";
    }
    if (!index_exists_on_column('#__nbill_orders', 'next_due_date')) {
        $sql[] = "ALTER TABLE `#__nbill_orders` ADD INDEX(`next_due_date`);";
    }
    if (!index_exists_on_column('#__nbill_order_form_fields', 'form_id')) {
        $sql[] = "ALTER TABLE `#__nbill_order_form_fields` ADD INDEX(`form_id`);";
    }
    if (!index_exists_on_column('#__nbill_order_form_fields', 'page_no')) {
        $sql[] = "ALTER TABLE `#__nbill_order_form_fields` ADD INDEX(`page_no`);";
    }
    if (!index_exists_on_column('#__nbill_order_form_fields_options', 'form_id')) {
        $sql[] = "ALTER TABLE `#__nbill_order_form_fields_options` ADD INDEX(`form_id`);";
    }
    if (!index_exists_on_column('#__nbill_order_form_fields_options', 'field_id')) {
        $sql[] = "ALTER TABLE `#__nbill_order_form_fields_options` ADD INDEX(`field_id`);";
    }
    if (!index_exists_on_column('#__nbill_order_form_fields_options', 'ordering')) {
        $sql[] = "ALTER TABLE `#__nbill_order_form_fields_options` ADD INDEX(`ordering`);";
    }
    if (!index_exists_on_column('#__nbill_product', 'category')) {
        $sql[] = "ALTER TABLE `#__nbill_product` ADD INDEX(`category`);";
    }
    if (!index_exists_on_column('#__nbill_product_category', 'parent_id')) {
        $sql[] = "ALTER TABLE `#__nbill_product_category` ADD INDEX(`parent_id`);";
    }
    if (!index_exists_on_column('#__nbill_profile_fields_options', 'field_id')) {
        $sql[] = "ALTER TABLE `#__nbill_profile_fields_options` ADD INDEX(`field_id`);";
    }
    if (!index_exists_on_column('#__nbill_supporting_docs', 'associated_doc_id')) {
        $sql[] = "ALTER TABLE `#__nbill_supporting_docs` ADD INDEX(`associated_doc_type`, `associated_doc_id`);";
    }
    if (!index_exists_on_column('#__nbill_transaction', 'document_ids')) {
        $sql[] = "ALTER TABLE `#__nbill_transaction` ADD INDEX(`document_ids`);";
    }
    if (!index_exists_on_column('#__nbill_transaction', 'entity_id')) {
        $sql[] = "ALTER TABLE `#__nbill_transaction` ADD INDEX(`entity_id`);";
    }
    if (!index_exists_on_column('#__nbill_transaction', 'currency')) {
        $sql[] = "ALTER TABLE `#__nbill_transaction` ADD INDEX(`currency`);";
    }
    if (!index_exists_on_column('#__nbill_transaction', 'date')) {
        $sql[] = "ALTER TABLE `#__nbill_transaction` ADD INDEX(`date`);";
    }
    if (!index_exists_on_column('#__nbill_transaction', 'g_tx_id')) {
        $sql[] = "ALTER TABLE `#__nbill_transaction` ADD INDEX(`g_tx_id`);";
    }
    if (!index_exists_on_column('#__nbill_transaction_ledger', 'transaction_id')) {
        $sql[] = "ALTER TABLE `#__nbill_transaction_ledger` ADD INDEX(`transaction_id`);";
    }
    if (!index_exists_on_column('#__nbill_transaction_ledger', 'nominal_ledger_code')) {
        $sql[] = "ALTER TABLE `#__nbill_transaction_ledger` ADD INDEX(`nominal_ledger_code`);";
    }
    if (!index_exists_on_column('#__nbill_transaction_ledger', 'currency')) {
        $sql[] = "ALTER TABLE `#__nbill_transaction_ledger` ADD INDEX(`currency`);";
    }
    if (!index_exists_on_column('#__nbill_translation', 'language')) {
        $sql[] = "ALTER TABLE `#__nbill_translation` ADD INDEX(`language`);";
    }
    if (!index_exists_on_column('#__nbill_translation', 'source_table')) {
        $sql[] = "ALTER TABLE `#__nbill_translation` ADD INDEX(`source_table`);";
    }
    if (!index_exists_on_column('#__nbill_translation', 'source_column')) {
        $sql[] = "ALTER TABLE `#__nbill_translation` ADD INDEX(`source_column`);";
    }

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
    //Invoice template (several people have had the last addition duplicated for some reason)
    $text_to_replace['en-GB'] = <<<LANG_SEARCH

//Version 3.0.0
define("NBILL_PRT_DUE_DATE", "Due Date:");
define("NBILL_CLICK_OR_SCAN_QR_CODE", " or scan the following QR code:");
define("NBILL_SCAN_HERE", "Scan here to pay this invoice.");

//Version 3.0.0
define("NBILL_PRT_DUE_DATE", "Due Date:");
define("NBILL_CLICK_OR_SCAN_QR_CODE", " or scan the following QR code:");
define("NBILL_SCAN_HERE", "Scan here to pay this invoice.");
LANG_SEARCH;
    $replace_with['en-GB'] = <<<LANG_REPLACE

//Version 3.0.0
define("NBILL_PRT_DUE_DATE", "Due Date:");
define("NBILL_CLICK_OR_SCAN_QR_CODE", " or scan the following QR code:");
define("NBILL_SCAN_HERE", "Scan here to pay this invoice.");
LANG_REPLACE;
    edit_language_item("template.in", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    //Paypal
    $text_to_replace['en-GB'] = "in which case, try switching on the 'use cURL' option on the payment gateway settings page";
    $replace_with['en-GB'] = "in which case, try adding or removing 'TLSv1' on the SSL Cipher setting of the payment gateway settings page";
    edit_language_item("paypal", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.2
define("NBILL_PAYPAL_SSL_CIPHER", "SSL Cipher");
define("NBILL_PAYPAL_SSL_CIPHER_HELP", "Some server configurations require the SSL cipher to be specified in order for Paypal callback verification to work. Others require that the cipher not be specified. If orders are not being activated or invoices not being marked as paid when a Paypal payment is made, you can try setting this to TLSv1 or TLSv1.2 (or if it is already set, try deleting the value and leaving this setting blank).");
LANG_ADD;
    edit_language_item("paypal", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}