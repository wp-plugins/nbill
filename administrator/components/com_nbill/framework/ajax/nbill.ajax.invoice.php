<?php
/**
* Server-side processing for invoice AJAX functions
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function get_invoice_income_data()
{
    nbf_common::load_language("expenditure");
    nbf_common::load_language("income");
    $nb_database = nbf_cms::$interop->database;
    $ret_val = "";

    //Load vendors and ledgers
    $sql = "SELECT id FROM #__nbill_vendor";
    $nb_database->setQuery($sql);
    $vendors = $nb_database->loadObjectList();
    $ledger = array();
    foreach ($vendors as $vendor)
    {
        $sql = "SELECT * FROM #__nbill_nominal_ledger WHERE vendor_id = " . $vendor->id . " ORDER BY code";
        $nb_database->setQuery($sql);
        $ledger[$vendor->id] = $nb_database->loadObjectList();
        if (!isset($ledger[$vendor->id]) || !$ledger[$vendor->id])
        {
            $ledger[$vendor->id] = array();
        }
    }

    $document_ids = nbf_common::get_param($_REQUEST, 'document_ids');
    $this_tx_id = intval(nbf_common::get_param($_REQUEST, 'transaction_id'));
    $expenditure = intval(nbf_common::get_param($_REQUEST, 'exp'));

    if (nbf_common::nb_strlen($document_ids) > 0)
    {
        $sql = "SELECT billing_name, billing_address, entity_id, billing_country, tax_exemption_code, reference, total_gross, currency FROM #__nbill_document WHERE id IN (" . $document_ids . ")";
        $nb_database->setQuery($sql);
        $documents = $nb_database->loadObjectList();
        $document = null;
        $total_gross = 0;
        if ($documents)
        {
            foreach ($documents as $document)
            {
                $total_gross = float_add($total_gross, $document->total_gross);
            }
        }
        if ($document)
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");
            $tax_rates = array();
            $tax_amounts = array();
            $tax_rates_electronic = array();
            $tax_amounts_electronic = array();
            $ledger_codes = array();
            $ledger_nets = array();
            $ledger_tax_rates = array();
            $ledger_taxes = array();
            $ledger_grosses = array();
            nbf_payment::load_invoice_breakdowns_electronic($this_tx_id, $document_ids, $tax_rates, $tax_amounts, $tax_rates_electronic, $tax_amounts_electronic, $ledger_codes, $ledger_nets, $ledger_tax_rates, $ledger_taxes, $ledger_grosses, $total_gross);
            nbf_payment::adjust_breakdowns_for_partial_payment($tax_rates, $tax_amounts, $ledger_codes, $ledger_nets, $ledger_tax_rates, $ledger_taxes, $ledger_grosses, $document->total_gross, $total_gross);
            $combined_rates = array();
            foreach ($tax_rates as $index=>$tax_rate)
            {
                $combined_rate = new stdClass();
                $combined_rate->tax_rate = $tax_rate;
                $combined_rate->amount = $tax_amounts[$index];
                $combined_rate->electronic = false;
                $combined_rates[] = $combined_rate;
            }
            foreach ($tax_rates_electronic as $index=>$tax_rate)
            {
                $combined_rate = new stdClass();
                $combined_rate->tax_rate = $tax_rate;
                $combined_rate->amount = $tax_amounts_electronic[$index];
                $combined_rate->electronic = true;
                $combined_rates[] = $combined_rate;
            }

            $ret_val = $document->billing_name . "#!#";
            $ret_val .= $document->billing_country . "#!#";
            $ret_val .= $document->tax_exemption_code . "#!#";
            $ret_val .= format_number($total_gross, 'currency_grand') . "#!#";
            if (count($combined_rates) > 0)
            {
                $ret_val .= format_number($combined_rates[0]->tax_rate, 'tax_rate') . "#!#";
                $ret_val .= format_number($combined_rates[0]->amount, 'currency_grand'). "#!#";
                $ret_val .= ($combined_rates[0]->electronic ? '1' : '0') . "#!#";
            }
            else
            {
                $ret_val .= format_number('0.00', 'tax_rate') . "#!#";
                $ret_val .= format_number('0.00', 'currency_grand') . "#!#";
                $ret_val .= "0#!#";
            }
            if (count($combined_rates) > 1)
            {
                $ret_val .= format_number($combined_rates[1]->tax_rate, 'tax_rate') . "#!#";
                $ret_val .= format_number($combined_rates[1]->amount, 'currency_grand') . "#!#";
                $ret_val .= ($combined_rates[1]->electronic ? '1' : '0') . "#!#";
            }
            else
            {
                $ret_val .= format_number('0.00', 'tax_rate') . "#!#";
                $ret_val .= format_number('0.00', 'currency_grand') . "#!#";
                $ret_val .= "0#!#";
            }
            if (count($combined_rates) > 2)
            {
                $ret_val .= format_number($combined_rates[2]->tax_rate, 'tax_rate') . "#!#";
                $ret_val .= format_number($combined_rates[2]->amount, 'currency_grand') . "#!#";
                $ret_val .= ($combined_rates[2]->electronic ? '1' : '0') . "#!#";
            }
            else
            {
                $ret_val .= format_number('0.00', 'tax_rate') . "#!#";
                $ret_val .= format_number('0.00', 'currency_grand') . "#!#";
                $ret_val .= "0#!#";
            }

            $ledger_table_heading = '<div class="rounded-table" style="display:inline-block;"><table cellpadding="1" cellspacing="0" border="0"><tr>';
            $ledger_table_heading .= '<th>' . NBILL_NOMINAL_LEDGER_CODE . '</th>';
            $ledger_table_heading .= '<th>' . NBILL_INCOME_LEDGER_NET_AMOUNT . '</th>';
            $ledger_table_heading .= '<th>' . NBILL_INCOME_LEDGER_TAX_AMOUNT . '</th>';
            $ledger_table_heading .= '<th>' . NBILL_INCOME_LEDGER_GROSS_AMOUNT . '</th>';
            $ledger_table_heading .= '</tr>';
            $ledger_table_rows = "";
            $added_items = array();
            //Added items
            for ($added_item = 1; $added_item <= count($ledger_codes); $added_item++)
            {
                $added_items[] = $added_item;
                $ledger_table_rows .= "<tr><td>";
                foreach ($vendors as $vendor)
                {
                    $ledger_list = array();
                    $ledger_list[] = nbf_html::list_option("-1", "-1 - " . NBILL_MISCELLANEOUS);
                    foreach ($ledger[$vendor->id] as $ledger_item)
                    {
                        if ($ledger_item->vendor_id == $vendor->id)
                        {
                            if ($ledger_item->code != -1 && $ledger_item->description != NBILL_MISCELLANEOUS)
                            {
                                $ledger_list[] = nbf_html::list_option($ledger_item->code, $ledger_item->code . " - " . $ledger_item->description);
                            }
                        }
                    }
                    $ledger_table_rows .= nbf_html::select_list($ledger_list, "ledger_new_" . $added_item . "_" . $vendor->id, 'class="inputbox squashable" id="ledger_new_' . $added_item . "_" . $vendor->id . '"', $ledger_codes[$added_item - 1]) . " ";
                }
                $ledger_table_rows .= "</td><td><input type=\"text\" name=\"ledger_net_new_$added_item" . "_amount\" id=\"ledger_net_new_$added_item" . "_amount\" class=\"inputbox small-numeric\" value=\"" . format_number($ledger_nets[$added_item - 1], 'currency_line') . "\" onchange=\"ledger_price_update('new_" . $added_item . "', false);\" />";
                $ledger_table_rows .= "</td><td><table border=\"0\" class=\"borderless\"><tr><td>" . NBILL_INCOME_TAX_RATE . ":</td><td><input type=\"text\" name=\"ledger_tax_new_$added_item" . "_rate\" id=\"ledger_tax_new_$added_item" . "_rate\" class=\"inputbox small-numeric\" value=\"" . format_number($ledger_tax_rates[$added_item - 1], 'tax_rate') . "\" onchange=\"ledger_price_update('new_" . $added_item . "', false);\" /></td></tr>";
                $ledger_table_rows .= "<tr><td>" . NBILL_INCOME_TAX_AMOUNT . ":</td><td><input type=\"text\" name=\"ledger_tax_new_$added_item" . "_amount\" id=\"ledger_tax_new_$added_item" . "_amount\" class=\"inputbox small-numeric\" value=\"" . format_number($ledger_taxes[$added_item - 1], 'currency_line') . "\" /></td></tr></table>";
                $ledger_table_rows .= "</td><td><input type=\"text\" name=\"ledger_gross_new_$added_item" . "_amount\" id=\"ledger_gross_new_$added_item" . "_amount\" class=\"inputbox small-numeric\" value=\"" . format_number($ledger_grosses[$added_item - 1], 'currency_line') . "\" onchange=\"ledger_price_update('new_" . $added_item . "', true);\" /><br /><input type=\"submit\" class=\"button btn\" name=\"delete_ledger_entry_new_" . $added_item . "\" id=\"delete_ledger_entry_new_" . $added_item . "\" value=\"" . NBILL_REMOVE_INVOICE_ITEM . "\" onclick=\"nbill_submit_task('remove_ledger_item');\" />";
                $ledger_table_rows .= "</td></tr>";
            }
            //New Entry
            $ledger_table_new = "<tr><td>";
            foreach ($vendors as $vendor)
            {
                $ledger_list = array();
                $ledger_list[] = nbf_html::list_option("-1", "-1 - " . NBILL_MISCELLANEOUS);
                foreach ($ledger[$vendor->id] as $ledger_item)
                {
                    if ($ledger_item->vendor_id == $vendor->id)
                    {
                        if ($ledger_item->code != -1 && $ledger_item->description != NBILL_MISCELLANEOUS)
                        {
                            $ledger_list[] = nbf_html::list_option($ledger_item->code, $ledger_item->code . " - " . $ledger_item->description);
                        }
                    }
                }
                $selected_ledger = '';
                $ledger_table_new .= nbf_html::select_list($ledger_list, "ledger_new_" . $vendor->id, 'class="inputbox squashable" id="ledger_new_' . $vendor->id . '"', '');
            }
            $ledger_table_new .= "</td><td><input type=\"text\" name=\"ledger_net_new_amount\" id=\"ledger_net_new_amount\" class=\"inputbox small-numeric\" value=\"\" onchange=\"ledger_price_update('new', false);\" />";
            $ledger_table_new .= "</td><td><table border=\"0\" class=\"borderless\"><tr><td>" . NBILL_INCOME_TAX_RATE . ":</td><td><input type=\"text\" name=\"ledger_tax_new_rate\" id=\"ledger_tax_new_rate\" class=\"inputbox small-numeric\" value=\"\" onchange=\"ledger_price_update('new', false);\" /></td></tr>";
            $ledger_table_new .= "<tr><td>" . NBILL_INCOME_TAX_AMOUNT . ":</td><td><input type=\"text\" name=\"ledger_tax_new_amount\" id=\"ledger_tax_new_amount\" class=\"inputbox small-numeric\" value=\"\" /></td></tr></table>";
            $ledger_table_new .= "</td><td><input type=\"text\" name=\"ledger_gross_new_amount\" id=\"ledger_gross_new_amount\" class=\"inputbox small-numeric\" value=\"\" onchange=\"ledger_price_update('new', true);\" /><br /><input type=\"submit\" class=\"button btn\" name=\"add_ledger_entry\" id=\"add_ledger_entry\" value=\"" . NBILL_ADD_INVOICE_ITEM . "\" onclick=\"nbill_submit_task('add_ledger_item');\" />";
            $ledger_table_new .= "</td></tr>";
            $ledger_table_new .= "</table></div>";
            $ret_val .= $ledger_table_heading . $ledger_table_rows . $ledger_table_new . "#!#";
            $ret_val .= $document->reference . "#!#";
            $ret_val .= implode(",", $added_items) . "#!#";
            $ret_val .= $document->currency;

            if ($expenditure)
            {
                $ret_val .= "#!#";
                $ret_val .= $document->entity_id . "#!#";
                $ret_val .= $document->billing_address;
            }
        }
    }

    if (nbf_cms::$interop->char_encoding == 'iso-8859-1' || nbf_cms::$interop->char_encoding == 'iso-8859-2')
    {
        $ret_val = utf8_encode($ret_val);
    }
    echo $ret_val;
}

/**
* Return list of product data for populating document item
* Row delimiter: @!@
* Field delimiter: #!#
*/
function document_get_products()
{
    $nb_database = nbf_cms::$interop->database;

    $current_action = nbf_common::get_param($_REQUEST, 'cur_action');
    $fragment = nbf_common::get_param($_REQUEST, 'product');
    $currency = nbf_common::get_param($_REQUEST, 'currency');
    $country = nbf_common::get_param($_REQUEST, 'billing_country');
    $vendor_id = intval(nbf_common::get_param($_REQUEST, 'vendor_id'));
    $sku = strpos(nbf_common::get_param($_REQUEST, 'target'), 'product_code') !== false;
    $doc_type = 'IN';
    switch ($current_action)
    {
        case 'quotes':
            $doc_type = 'QU';
            break;
        case 'credits':
            $doc_type = 'CR';
            break;
    }

    $sku_products = array();
    $name_products = array();
    $products = array();
    $ad_hoc_products = array();

    $config = nBillConfigurationService::getInstance()->getConfig();
    $number_factory = new nBillNumberFactory($config);
    $tax_mapper = new nBillTaxMapper(nbf_cms::$interop->database, $number_factory);
    $tax_service = new nBillTaxService($tax_mapper, $config);
    $normal_tax_rate = $tax_service->getNormalRateForCountry($vendor_id, $country);
    $normal_tax_rate = $normal_tax_rate ? $normal_tax_rate->tax_rate->format() : 0;
    $electronic_tax_rate = $tax_service->getElectronicDeliveryRateForCountry($vendor_id, $country);
    $electronic_tax_rate = $electronic_tax_rate ? $electronic_tax_rate->tax_rate->format() : 0;

    //If typing into product description box, offer recent ad-hoc descriptions
    if (!$sku)
    {
        //Check for matching ad-hoc items with fragment at start
        $sql = "SELECT #__nbill_document_items.product_code, product_description, detailed_description, #__nbill_document_items.nominal_ledger_code,
                    net_price_per_unit, no_of_units, net_price_for_item, tax_rate_for_item, tax_for_item,
                    quote_pay_freq, #__nbill_product.electronic_delivery, #__nbill_product.`shipping_units` AS product_shipping_units
                    FROM #__nbill_document_items
                    INNER JOIN #__nbill_document ON #__nbill_document_items.document_id = #__nbill_document.id
                    LEFT JOIN #__nbill_orders_document ON #__nbill_document.id = #__nbill_orders_document.document_id
                    LEFT JOIN #__nbill_product ON #__nbill_document_items.product_code = #__nbill_product.product_code
                    WHERE #__nbill_document.document_type = '$doc_type'
                    AND currency = '" . $currency . "' AND #__nbill_document_items.product_code = ''
                    AND document_date > " . nbf_common::nb_strtotime("-10 years") . "
                    AND product_description LIKE '" . $fragment . "%'";
        if ($vendor_id > 0)
        {
            $sql .= " AND #__nbill_document_items.vendor_id = $vendor_id";
        }
        $sql .= " GROUP BY product_description, net_price_per_unit ORDER BY `product_description` LIMIT 10";
        $nb_database->setQuery($sql);
        $ad_hoc_products = $nb_database->loadObjectList();
        if (!$ad_hoc_products) {
            $ad_hoc_products = array();
        }
        if (count($ad_hoc_products) < 10)
        {
            $narrow_ad_hoc_products = $ad_hoc_products;
            $ad_hoc_products = array();

            //Try anywhere within
            $sql = "SELECT #__nbill_document_items.product_code, product_description, detailed_description, #__nbill_document_items.nominal_ledger_code,
                    net_price_per_unit, no_of_units, net_price_for_item, tax_rate_for_item, tax_for_item,
                    quote_pay_freq, #__nbill_product.electronic_delivery, #__nbill_product.`shipping_units` AS product_shipping_units
                    FROM #__nbill_document_items
                    INNER JOIN #__nbill_document ON #__nbill_document_items.document_id = #__nbill_document.id
                    LEFT JOIN #__nbill_orders_document ON #__nbill_document.id = #__nbill_orders_document.document_id
                    LEFT JOIN #__nbill_product ON #__nbill_document_items.product_code = #__nbill_product.product_code
                    WHERE #__nbill_document.document_type = '$doc_type'
                    AND currency = '" . $currency . "' AND #__nbill_document_items.product_code = ''
                    AND document_date > " . nbf_common::nb_strtotime("-10 years") . "
                    AND product_description LIKE '%" . $fragment . "%'
                    AND product_description NOT LIKE '" . $fragment . "%'";
            if ($vendor_id > 0)
            {
                $sql .= " AND #__nbill_document_items.vendor_id = $vendor_id";
            }
            $sql .= " GROUP BY product_description, net_price_per_unit ORDER BY `product_description` LIMIT 10";
            $nb_database->setQuery($sql);
            $wide_ad_hoc_products = $nb_database->loadObjectList();
            if (!$wide_ad_hoc_products) {
                $wide_ad_hoc_products = array();
            }
            $ad_hoc_products = array_merge($narrow_ad_hoc_products, $wide_ad_hoc_products);
            foreach ($ad_hoc_products as &$ad_hoc_product)
            {
                if ($ad_hoc_product->tax_for_item > 0 && $ad_hoc_product->electronic_delivery) {
                    $ad_hoc_product->tax_rate_for_item = format_number($electronic_tax_rate, 'tax_rate');
                    $ad_hoc_product->tax_for_item = format_number(($ad_hoc_product->net_price_for_item / 100) * $electronic_tax_rate, 'currency_line');
                } else if ($ad_hoc_product->tax_for_item > 0 && $ad_hoc_product->electronic_delivery !== null) {
                    $ad_hoc_product->tax_rate_for_item = format_number($normal_tax_rate, 'tax_rate');
                    $ad_hoc_product->tax_for_item = format_number(($ad_hoc_product->net_price_for_item / 100) * $normal_tax_rate, 'currency_line');
                }
            }
        }
    }

    //Check for matching SKUs with fragment at start
    $sql = "SELECT CONCAT('p', `id`) AS `id`, `id` AS product_id, #__nbill_product.`product_code`, `name`, `description`,
                    `nominal_ledger_code`, `is_freebie`, `is_taxable`, `custom_tax_rate`, `electronic_delivery`, `shipping_units` AS product_shipping_units
                    FROM #__nbill_product
                    WHERE `product_code` LIKE '" . $fragment . "%'";
    if ($vendor_id > 0)
    {
        $sql .= " AND vendor_id = $vendor_id";
    }
    $sql .= " ORDER BY `product_code` LIMIT 15";
    $nb_database->setQuery($sql);
    $sku_products = $nb_database->loadObjectList('id');
    if (!$sku_products) {
        $sku_products = array();
    } else {
        foreach ($sku_products as &$sku_product)
        {
            if ($sku_product->is_taxable > 0 && $sku_product->electronic_delivery) {
                $sku_product->custom_tax_rate = $electronic_tax_rate;
            } else if ($sku_product->is_taxable && $sku_product->electronic_delivery !== null) {
                $sku_product->custom_tax_rate = $normal_tax_rate;
            }
        }
    }

    //Check for matching product names with fragment at start
    $sql = "SELECT CONCAT('p', `id`) AS `id`, `id` AS product_id, #__nbill_product.`product_code`, `name`, `description`,
                    `nominal_ledger_code`, `is_freebie`, `is_taxable`, `custom_tax_rate`, `electronic_delivery`, `shipping_units` AS product_shipping_units
                    FROM #__nbill_product
                    WHERE `name` LIKE '" . $fragment . "%'";
    if ($vendor_id > 0)
    {
        $sql .= " AND vendor_id = $vendor_id";
    }
    $sql .= " ORDER BY `name` LIMIT 15";
    $nb_database->setQuery($sql);
    $name_products = $nb_database->loadObjectList('id');
    if (!$name_products) {
        $name_products = array();
    } else {
        foreach ($name_products as &$name_product)
        {
            if ($name_product->is_taxable > 0 && $name_product->electronic_delivery) {
                $name_product->custom_tax_rate = $electronic_tax_rate;
            } else if ($name_product->is_taxable && $name_product->electronic_delivery !== null) {
                $name_product->custom_tax_rate = $normal_tax_rate;
            }
        }
    }

    //Order results according to priority
    $products = $sku ? array_merge($sku_products, $name_products) : array_merge($name_products, $sku_products);

    //If less than 20 results, widen the search
    if (count($products) < 20)
    {
        //Forget what we've already captured
        $sku_products = array();
        $name_products = array();

        //Check for matching SKUs with fragment anywhere within
        $sql = "SELECT CONCAT('p', `id`) AS `id`, `id` AS product_id, #__nbill_product.`product_code`, `name`, `description`,
                        `nominal_ledger_code`, `is_freebie`, `is_taxable`, `custom_tax_rate`, `electronic_delivery`, `shipping_units` AS product_shipping_units
                        FROM #__nbill_product
                        WHERE `product_code` LIKE '%" . $fragment . "%'";
        if ($vendor_id > 0)
        {
            $sql .= " AND vendor_id = $vendor_id";
        }
        $sql .= " ORDER BY `product_code` LIMIT 15";
        $nb_database->setQuery($sql);
        $sku_products = $nb_database->loadObjectList('id');
        if (!$sku_products) {
            $sku_products = array();
        } else {
            foreach ($sku_products as &$sku_product)
            {
                if ($sku_product->is_taxable > 0 && $sku_product->electronic_delivery) {
                    $sku_product->custom_tax_rate = $electronic_tax_rate;
                } else if ($sku_product->is_taxable && $sku_product->electronic_delivery !== null) {
                    $sku_product->custom_tax_rate = $normal_tax_rate;
                }
            }
        }

        //Check for matching product names with fragment anywhere within
        $sql = "SELECT CONCAT('p', `id`) AS `id`, `id` AS product_id, #__nbill_product.`product_code`, `name`, `description`,
                        `nominal_ledger_code`, `is_freebie`, `is_taxable`, `custom_tax_rate`, `electronic_delivery`, `shipping_units` AS product_shipping_units
                        FROM #__nbill_product
                        WHERE `name` LIKE '%" . $fragment . "%'";
        if ($vendor_id > 0)
        {
            $sql .= " AND vendor_id = $vendor_id";
        }
        $sql .= " ORDER BY `name` LIMIT 15";
        $nb_database->setQuery($sql);
        $name_products = $nb_database->loadObjectList('id');
        if (!$name_products) {
            $name_products = array();
        } else {
            foreach ($name_products as &$name_product)
            {
                if ($name_product->is_taxable > 0 && $name_product->electronic_delivery) {
                    $name_product->custom_tax_rate = $electronic_tax_rate;
                } else if ($name_product->is_taxable && $name_product->electronic_delivery !== null) {
                    $name_product->custom_tax_rate = $normal_tax_rate;
                }
            }
        }

        $wide_products = $sku ? array_merge($sku_products, $name_products) : array_merge($name_products, $sku_products);
        $products = array_merge($products, $wide_products);
    }

    //Get product IDs, but don't return more than 20 items
    $product_ids = array();
    $return_products = array();
    $product_count = 0;
    foreach ($products as $key=>$value)
    {
        $return_products[$key] = $value;
        $product_ids[] = intval($value->product_id);
        $product_count++;
        if ($product_count >= 20)
        {
            break;
        }
    }

    //Get pricing
    $sql = "SELECT * FROM #__nbill_product_price WHERE ";
    if ($vendor_id > 0)
    {
        $sql .= "vendor_id = $vendor_id AND ";
    }
    $sql .= "currency_code = '$currency' AND product_id IN (" . implode(",", $product_ids) . ") ORDER BY product_id";
    $nb_database->setQuery($sql);
    $prices = $nb_database->loadObjectList();
    if (!$prices)
    {
        $prices = array();
    }

    $return_value = "";
    if (count($ad_hoc_products) > 0)
    {
        $return_value = "";
        foreach ($ad_hoc_products as $ad_hoc_product)
        {
            $fragment_start = nbf_common::nb_strpos(nbf_common::nb_strtolower($ad_hoc_product->product_description), nbf_common::nb_strtolower($fragment));
            $display_value = nbf_common::nb_substr($ad_hoc_product->product_description, 0, $fragment_start) . '<span class="auto_suggest_fragment">' . nbf_common::nb_substr($ad_hoc_product->product_description, $fragment_start, nbf_common::nb_strlen($fragment)) . '</span>' . nbf_common::nb_substr($ad_hoc_product->product_description, $fragment_start + nbf_common::nb_strlen($fragment));
            $display_value .= " (" . format_number($ad_hoc_product->net_price_per_unit, 'currency', null, null, null, $currency) . ")";
            $return_value .= $display_value . '#!#';
            $return_value .= strip_tags($display_value) . '#!#';
            $return_value .= '#!#';
            $return_value .= $ad_hoc_product->product_description . '#!#';
            $return_value .= $ad_hoc_product->detailed_description . '#!#';
            $return_value .= $ad_hoc_product->nominal_ledger_code . '#!#';
            $return_value .= ($ad_hoc_product->tax_rate_for_item > 0 || $ad_hoc_product->tax_for_item > 0 ? '1' : '0') . '#!#';
            $return_value .= $ad_hoc_product->tax_rate_for_item . '#!#';
            $return_value .= '0#!#';
            $return_value .= format_number($ad_hoc_product->net_price_per_unit, 'currency') . '#!#';
            $return_value .= $ad_hoc_product->quote_pay_freq . '#!#';
            $return_value .= $ad_hoc_product->product_shipping_units . '#!#';
            $return_value .= ($ad_hoc_product->electronic_delivery ? '1' : '0') . '@!@';
        }
    }
    foreach ($return_products as $product)
    {
        $setup_fee_present = '0';
        $price = '0.00';
        $freq = 'AA';
        foreach ($prices as $price)
        {
            if ($price->product_id == $product->product_id)
            {
                $setup_fee_present = ($price->net_price_setup_fee != 0 ? '1' : '0');
                if ($price->net_price_one_off != 0)
                {
                    $price = format_number($price->net_price_one_off, 'currency');
                }
                else if ($price->net_price_weekly != 0)
                {
                    $price = format_number($price->net_price_weekly, 'currency');
                    $freq = 'BB';
                }
                else if ($price->net_price_four_weekly != 0)
                {
                    $price = format_number($price->net_price_four_weekly, 'currency');
                    $freq = 'BX';
                }
                else if ($price->net_price_monthly != 0)
                {
                    $price = format_number($price->net_price_monthly, 'currency');
                    $freq = 'CC';
                }
                else if ($price->net_price_quarterly != 0)
                {
                    $price = format_number($price->net_price_quarterly, 'currency');
                    $freq = 'DD';
                }
                else if ($price->net_price_semi_annually != 0)
                {
                    $price = format_number($price->net_price_semi_annually, 'currency');
                    $freq = 'DX';
                }
                else if ($price->net_price_annually != 0)
                {
                    $price = format_number($price->net_price_annually, 'currency');
                    $freq = 'EE';
                }
                else if ($price->net_price_biannually != 0)
                {
                    $price = format_number($price->net_price_biannually, 'currency');
                    $freq = 'FF';
                }
                else if ($price->net_price_five_years != 0)
                {
                    $price = format_number($price->net_price_five_years, 'currency');
                    $freq = 'GG';
                }
                else if ($price->net_price_ten_years != 0)
                {
                    $price = format_number($price->net_price_ten_years, 'currency');
                    $freq = 'HH';
                }
                else
                {
                    $price =  format_number('0.00', 'currency');
                }
                break;
            }
        }

        $code_and_name = $product->product_code . (strlen($product->product_code) > 0 ? ' - ' : '') . $product->name;
        $fragment_start = nbf_common::nb_strpos(nbf_common::nb_strtolower($code_and_name), nbf_common::nb_strtolower($fragment));
        $display_value = nbf_common::nb_substr($code_and_name, 0, $fragment_start) . '<span class="auto_suggest_fragment">' . nbf_common::nb_substr($code_and_name, $fragment_start, nbf_common::nb_strlen($fragment)) . '</span>' . nbf_common::nb_substr($code_and_name, $fragment_start + nbf_common::nb_strlen($fragment));
        $display_value .= " (" . $price . ")";
        $return_value .= $display_value . '#!#';
        $return_value .= strip_tags($display_value) . '#!#';
        $return_value .= $product->product_code . '#!#';
        $return_value .= $product->name . '#!#';
        $return_value .= str_replace("\r", "", str_replace("\n", '\n', $product->description)) . '#!#';
        $return_value .= $product->nominal_ledger_code . '#!#';
        $return_value .= $product->is_taxable . '#!#';
        $return_value .= $product->custom_tax_rate . '#!#';
        $return_value .= $setup_fee_present . '#!#';
        $return_value .= $price . '#!#';
        $return_value .= $freq . '#!#';
        $return_value .= $product->product_shipping_units . '#!#';
        $return_value .= ($product->electronic_delivery ? '1' : '0') . '@!@';
    }

    echo $return_value;
}

/**
* Check whether the given sku relates to an existing product, if so indicate whether updated, if not, indicate that it is new
*/
function check_product_update()
{
    $nb_database = nbf_cms::$interop->database;

    if (strlen(nbf_common::get_param($_REQUEST, 'sku')) == 0 ||
        (strpos(nbf_common::get_param($_REQUEST, 'sku'), '[') !== false && strpos(nbf_common::get_param($_REQUEST, 'sku'), '=') !== false))
    {
        return;
    }

    $product = null;
    $sql = "SELECT name, description, nominal_ledger_code
            FROM #__nbill_product
            WHERE vendor_id = " . intval(nbf_common::get_param($_REQUEST, 'vendor_id')) . "
            AND product_code = '" . nbf_common::get_param($_REQUEST, 'sku') . "'";
    $nb_database->setQuery($sql);
    $nb_database->loadObject($product);

    if (!$product)
    {
        echo "added";
        return;
    }
    else
    {
        //Check if anything has been updated
        $supplied_name = nbf_common::get_param($_REQUEST, 'name', '', true, false, true);
        $supplied_name = $supplied_name ? html_entity_decode($supplied_name, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) : $supplied_name;
        $supplied_desc = str_replace("\n", " ", str_replace("\r", "", nbf_common::get_param($_REQUEST, 'desc', '', true, false, true)));
        $supplied_desc = $supplied_desc ? html_entity_decode($supplied_desc, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) : $supplied_desc;
        $supplied_ledger = nbf_common::get_param($_REQUEST, 'ledger', '', true, false, true);
        $supplied_ledger = $supplied_ledger ? html_entity_decode($supplied_ledger, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) : $supplied_ledger;

        $saved_name = $product->name ? html_entity_decode($product->name, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) : $product->name;
        $saved_desc = $product->description ? html_entity_decode(str_replace("\n", " ", str_replace("\r", "", $product->description)), ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) : $product->description;
        $saved_ledger = $product->nominal_ledger_code ? html_entity_decode($product->nominal_ledger_code, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) : $product->nominal_ledger_code;

        //Clean up HTML descriptions so we don't get false mismatches
        $tinymce_fluff = html_entity_decode(' <p>&nbsp;</p>', ENT_COMPAT | 0, nbf_cms::$interop->char_encoding);
        if (substr($supplied_desc, strlen($supplied_desc) - strlen($tinymce_fluff)) == $tinymce_fluff)
        {
            //Strip off the extra paragraph added by TinyMCE
            $supplied_desc = rtrim(substr($supplied_desc, 0, strlen($supplied_desc) - strlen($tinymce_fluff)));
        }
        if (nbf_config::$editor == 'nicEdit' && strpos($supplied_desc, '<br>') !== false && strpos($saved_desc, '<br />') !== false)
        {
            //nicEdit converts self closing line breaks to HTML 4.0 ones, so we'll convert them back for comparison purposes
            $supplied_desc = str_replace('<br>', '<br />', $supplied_desc);
        }

        if (($saved_name != $supplied_name && nbf_common::nb_substr($supplied_name, 0, nbf_common::nb_strlen($saved_name)) != $saved_name) ||
            ($saved_desc != $supplied_desc && @utf8_decode($saved_desc) != $supplied_desc && trim(str_replace(chr(160), chr(32), utf8_decode($saved_desc))) != trim($supplied_desc)) || //CodeMirror editor returns decoded html
            $saved_ledger != $supplied_ledger)
        {
            echo "updated";
            return;
        }
        else
        {
            //Check whether price has been updated
            $pay_freq_col = nbf_common::get_param($_REQUEST, 'pay_freq') ? nbf_common::convert_pay_freq(nbf_common::get_param($_REQUEST, 'pay_freq')) : 'net_price_one_off';
            $sql = "SELECT `$pay_freq_col`
                    FROM #__nbill_product_price
                    INNER JOIN #__nbill_product ON #__nbill_product_price.product_id = #__nbill_product.id
                    WHERE #__nbill_product.vendor_id = " . intval(nbf_common::get_param($_REQUEST, 'vendor_id')) . "
                    AND #__nbill_product.product_code = '" . nbf_common::get_param($_REQUEST, 'sku') . "'
                    AND #__nbill_product_price.currency_code = '" . nbf_common::get_param($_REQUEST, 'currency') ."'";
            $nb_database->setQuery($sql);
            $current_price = $nb_database->loadResult();
            if ($current_price !== null && $current_price == nbf_common::get_param($_REQUEST, 'orig_price') && $current_price != nbf_common::get_param($_REQUEST, 'price'))
            {
                echo "updated";
                return;
            }
        }
    }
}

function get_default_tax_info()
{
    $nb_database = nbf_cms::$interop->database;

    $vendor_id = intval(nbf_common::get_param($_REQUEST, 'vendor_id'));
    $entity_id = intval(nbf_common::get_param($_REQUEST, 'entity_id'));
    $country = nbf_common::get_param($_REQUEST, 'country');
    $tax_exemption_code = nbf_common::get_param($_REQUEST, 'tax_exemption_code');
    $document_type = nbf_common::get_param($_REQUEST, 'document_type');
    $product_id = intval(nbf_common::get_param($_REQUEST, 'product_id'));

    $vendor = null;
    $sql = "SELECT vendor_country, ";
    switch ($document_type)
    {
        case 'QU':
            $sql .= "quote_small_print AS small_print,";
            break;
        case 'CR':
            $sql .= "credit_small_print AS small_print,";
            break;
        default:
            $sql .= "small_print,";
            break;
    }
    $sql .= " payment_instructions FROM #__nbill_vendor WHERE id = " . $vendor_id;
    $nb_database->setQuery($sql);
    $nb_database->loadObject($vendor);

    $sql = "SELECT tax_zone FROM #__nbill_entity WHERE id = " . $entity_id;
    $nb_database->setQuery($sql);
    $client_tax_zone = $nb_database->loadResult();

    $sql = "SELECT code FROM #__nbill_xref_eu_country_codes WHERE code = '" . $country . "'";
    $nb_database->setQuery($sql);
    $in_eu = $nb_database->loadResult();

    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.tax.class.php");
    $tax = nbf_tax::find_tax_rate($vendor_id, $vendor->vendor_country, $client_tax_zone, $country, $in_eu, $tax_exemption_code, $product_id);

    $sml_prt = '';

    if (!$tax || ($tax->exempt_with_ref_no && strlen($tax_exemption_code) > 0)) {
        //No tax - use default small print/pay inst
        $return = new stdClass();
        $return->tax_name = $tax ? $tax->tax_name : '';
        $return->tax_abbreviation = $tax ? $tax->tax_abbreviation : '';
        $return->tax_reference_no = $tax ? $tax->tax_reference_no : '';
        $return->tax_reference_desc = $tax ? $tax->tax_reference_desc : '';
        $return->tax_rate = 0;
        $return->payment_instructions = $tax && $tax->payment_instructions ? $tax->payment_instructions : $vendor->payment_instructions;
        $return->small_print = $document_type == 'IV' && $tax && $tax->small_print ? $tax->small_print : $vendor->small_print;
    } else {
        $return = $tax;
        $return->payment_instructions = $tax && $tax->payment_instructions ? $tax->payment_instructions : $vendor->payment_instructions;
        $return->small_print = $document_type == 'IV' && $tax && $tax->small_print ? $tax->small_print : $vendor->small_print;
    }

    $ret_val = json_encode($return);
    echo $ret_val;
    exit;
}

function client_changed()
{
    $nb_database = nbf_cms::$interop->database;

    $client = null;
    $entity_id = intval(nbf_common::get_param($_REQUEST, 'entity_id'));
    $sql = "SELECT add_name_to_invoice, company_name, address_1, address_2, address_3, town, state, country, postcode, reference, tax_exemption_code
            FROM #__nbill_entity
            WHERE id = " . $entity_id;
    $nb_database->setQuery($sql);
    $nb_database->loadObject($client);
    $return = new stdClass();

    if ($client) {
        $return->billing_name = $client->company_name;
        if ($client->add_name_to_invoice || strlen(trim($return->billing_name)) == 0) {
            $sql = "SELECT CONCAT(CONCAT(first_name, ' '), last_name) FROM #__nbill_contact INNER JOIN #__nbill_entity ON #__nbill_entity.primary_contact_id = #__nbill_contact.id WHERE #__nbill_entity.id = $entity_id";
            $nb_database->setQuery($sql);
            $contact_name = trim($nb_database->loadResult());
            if (strlen($contact_name) > 0 && strlen($return->billing_name) > 0) {
                $return->billing_name .= ' (' . $contact_name . ')';
            } else {
                $return->billing_name = $contact_name;
            }
        }
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.address.class.php");
        $return->billing_address = nbf_address::format_billing_address($client->address_1, $client->address_2, $client->address_3, $client->town, $client->state, $client->postcode, $client->country);
        $return->billing_country = $client->country;
        $return->reference = $client->reference;
        $return->tax_exemption_code = $client->tax_exemption_code;

        $contact_factory = new nBillContactFactory();
        $entity_factory = new nBillEntityFactory();
        $entity_service = $entity_factory->createEntityService($contact_factory->createContactService());
        $client = $entity_service->loadEntity($entity_id, true);
        $shipping_addresses = array(0=>NBILL_NOT_APPLICABLE);
        if (@$client->shipping_address && $client->shipping_address->id) {

            if (@$client->shipping_address) {
                $shipping_addresses[$client->shipping_address->id] = $client->shipping_address->format(true);
            }
            if (@$client->contacts) {
                foreach ($client->contacts as $contact) {
                    if (@$contact->shipping_address) {
                        $shipping_addresses[$contact->shipping_address->id] = $contact->shipping_address->format(true);
                    }
                }
            }
        }
        $return->shipping_addresses = $shipping_addresses;
    } else {
        $return = new stdClass();
        $return->billing_name = '';
        $return->billing_address = '';
        $return->billing_country = '';
        $return->reference = '';
        $return->tax_exemption_code = '';
        $return->shipping_addresses = array(0=>NBILL_NOT_APPLICABLE);
    }

    $ret_val = json_encode($return);
    echo $ret_val;
    exit;
}