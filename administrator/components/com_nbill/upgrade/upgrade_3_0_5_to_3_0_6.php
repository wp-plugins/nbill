<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_3_0_5_to_3_0_6()
{
    nbf_version::$nbill_version_no = '3.0.6';

    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    $sql[] = "ALTER TABLE `#__nbill_product` CHANGE `user_group` `user_group` VARCHAR(255) NOT NULL DEFAULT '0';";
    $sql[] = "ALTER TABLE `#__nbill_product` CHANGE `expiry_level` `expiry_level` VARCHAR(255) NOT NULL DEFAULT '0';";
    $sql[] = "ALTER TABLE `#__nbill_account_expiry` CHANGE `expiry_level` `expiry_level` VARCHAR(255) NOT NULL DEFAULT '0';";
    $sql[] = "ALTER TABLE `#__nbill_configuration` CHANGE `precision_quantity` `precision_quantity` SMALLINT(6) NOT NULL DEFAULT '2';";
    $sql[] = "ALTER TABLE `#__nbill_document_items` ADD `discount_percentage` DECIMAL(20,6) NOT NULL DEFAULT '0.00' AFTER `no_of_units`;";

    $search_date_from = strtotime('2014-12-01 00:00:00');
    $query = "SELECT #__nbill_transaction.id, #__nbill_document_transaction.document_id, #__nbill_document_items.id AS document_item_id,
                    (#__nbill_document_items.tax_for_item / #__nbill_document_items.net_price_for_item) * 100 AS tax_rate
                    FROM #__nbill_transaction
                    INNER JOIN #__nbill_document_transaction ON #__nbill_transaction.id = #__nbill_document_transaction.transaction_id
                    INNER JOIN #__nbill_orders_document ON #__nbill_document_transaction.document_id = #__nbill_orders_document.document_id
                    INNER JOIN #__nbill_orders ON #__nbill_orders_document.order_id = #__nbill_orders.id
                    INNER JOIN #__nbill_product ON #__nbill_orders.product_id = #__nbill_product.id
                    INNER JOIN #__nbill_document_items ON #__nbill_orders_document.document_item_id = #__nbill_document_items.id
                    WHERE #__nbill_transaction.date >= $search_date_from
                    AND #__nbill_product.electronic_delivery = 1 AND #__nbill_document_items.electronic_delivery = 0
                    AND (#__nbill_transaction.tax_rate_1 = 0 AND #__nbill_transaction.tax_amount_1 > 0)
                    ORDER BY #__nbill_transaction.transaction_type DESC, #__nbill_transaction.date, #__nbill_transaction.transaction_no";
    $nb_database->setQuery($query);
    $anomalies = $nb_database->loadObjectList();
    if ($anomalies) {
        foreach ($anomalies as $anomaly) {
            $sql[] = "UPDATE #__nbill_document_items SET tax_rate_for_item = '" . intval($anomaly->tax_rate) . "', electronic_delivery = 1 WHERE id = " . intval($anomaly->document_item_id);
            $sql[] = "UPDATE #__nbill_transaction SET tax_rate_1 = '" . intval($anomaly->tax_rate) . "', tax_rate_1_electronic_delivery = 1 WHERE id = " . intval($anomaly->id);
            $sql[] = "UPDATE #__nbill_transaction_ledger SET tax_rate = '" . intval($anomaly->tax_rate) . "' WHERE tax_rate = 0 AND tax_amount > 0 AND transaction_id = " . intval($anomaly->id);
        }
    }

    $sql[] = "UPDATE #__nbill_document_items SET tax_rate_for_item = 0 WHERE net_price_for_item != 0 AND tax_for_item = 0";
    $sql[] = "UPDATE #__nbill_transaction SET tax_rate_1 = 0 WHERE amount != 0 AND tax_amount_1 = 0";

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
    //Invoices
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.6
define("NBILL_INVOICE_ITEM_DISCOUNT_PERCENT", "Discount %");
define("NBILL_SHOW_DISCOUNT_FIELDS", "Discount");
LANG_ADD;
    edit_language_item("invoices", $text_to_add);
    $text_to_add = array();

    //Invoice Template
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.6
define("NBILL_PRT_OVERPAID", "(Overpaid!)");
LANG_ADD;
    edit_language_item("template.in", $text_to_add);
    $text_to_add = array();

    //nBill
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.6
define("NBILL_ERROR_MESSAGE", "Error Message: ");
define("NBILL_EMAIL_DOWNLOADABLE_DEFAULT_MESSAGE", "Thank you for your order. Please find your file(s) attached.\\n\\nRegards,\\n%s");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}