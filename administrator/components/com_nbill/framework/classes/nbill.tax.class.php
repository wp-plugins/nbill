<?php
/**
* Class file just containing static methods relating to tax (VAT).
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* Static functions relating to tax (VAT)
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_tax
{
    public static function update_tax_exemption_code($client_id, $user_id = null)
    {
        $nb_database = nbf_cms::$interop->database;

        //Get default vendor details
        $default_vendor = null;
        $sql = "SELECT id, vendor_country FROM #__nbill_vendor WHERE default_vendor = 1";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($default_vendor);

        //Get client details
        if (nbf_common::nb_strlen($client_id) == 0)
        {
            $sql = "SELECT #__nbill_entity.id, #__nbill_entity.country, #__nbill_entity.tax_zone,
                            #__nbill_entity.tax_exemption_code
                            FROM #__nbill_entity
                            INNER JOIN #__nbill_entity_contact ON #__nbill_entity.id = #__nbill_entity_contact.entity_id
                            INNER JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id
                            WHERE #__nbill_contact.user_id = " . intval($user_id);
            $nb_database->setQuery($sql);
            $nb_database->loadObject($client_data);
            if ($client_data)
            {
                $client_id = $client_data->id;
            }
        }
        else
        {
            $sql = "SELECT #__nbill_entity.country, #__nbill_entity.tax_zone,
                            #__nbill_entity.tax_exemption_code FROM #__nbill_entity
                            WHERE id = " . intval($client_id);
            $nb_database->setQuery($sql);
            $nb_database->loadObject($client_data);
        }

        if ($client_data)
        {
            //Get tax options
            $sql = "SELECT country_code, tax_rate, online_exempt, exempt_with_ref_no, tax_zone, electronic_delivery FROM #__nbill_tax WHERE tax_zone is null OR tax_zone = '' OR tax_zone = '" . $client_data->tax_zone . "'";
            $nb_database->setQuery($sql);
            $tax_rates = $nb_database->loadObjectList();
            if (!$tax_rates)
            {
                $tax_rates = array();
            }
            $tax_rate_found = false;
            $exemption_allowed = false;
            $rate_of_tax = 0;
            $online_exempt = false;

            $tax_rate_found_electronic = false;
            $exemption_allowed_electronic = false;
            $rate_of_tax_electronic = 0;
            $online_exempt_electronic = false;

            foreach ($tax_rates as $tax_rate)
            {
                if ($tax_rate->tax_zone == $client_data->tax_zone && nbf_common::nb_strlen($tax_rate->tax_zone) > 0)
                {
                    if ($tax_rate->electronic_delivery) {
                        if (!$tax_rate_found_electronic) {
                            $tax_rate_found_electronic = true;
                            $exemption_allowed_electronic = $tax_rate->exempt_with_ref_no;
                            $rate_of_tax_electronic = $tax_rate->tax_rate;
                            $online_exempt_electronic = $tax_rate->online_exempt;
                        }
                    } else {
                        if (!$tax_rate_found) {
                            $tax_rate_found = true;
                            $exemption_allowed = $tax_rate->exempt_with_ref_no;
                            $rate_of_tax = $tax_rate->tax_rate;
                            $online_exempt = $tax_rate->online_exempt;
                        }
                    }
                    if ($tax_rate_found && $tax_rate_found_electronic) {
                        break;
                    }
                }
            }
            if (!$tax_rate_found)
            {
                foreach ($tax_rates as $tax_rate)
                {
                    if ($tax_rate->country_code == $client_data->country)
                    {
                        if ($tax_rate->electronic_delivery) {
                            if (!$tax_rate_found_electronic) {
                                $tax_rate_found_electronic = true;
                                $exemption_allowed_electronic = $tax_rate->exempt_with_ref_no;
                                $rate_of_tax_electronic = $tax_rate->tax_rate;
                                $online_exempt_electronic = $tax_rate->online_exempt;
                            }
                        } else {
                            if (!$tax_rate_found) {
                                $tax_rate_found = true;
                                $exemption_allowed = $tax_rate->exempt_with_ref_no;
                                $rate_of_tax = $tax_rate->tax_rate;
                                $online_exempt = $tax_rate->online_exempt;
                            }
                        }
                        if ($tax_rate_found && $tax_rate_found_electronic) {
                            break;
                        }
                    }
                }
            }
            if (!$tax_rate_found && $client_data->country != $default_vendor->vendor_country)
            {
                //Check whether in EU
                $sql = "SELECT code FROM #__nbill_xref_eu_country_codes WHERE code = '" . $client_data->country . "'";
                $nb_database->setQuery($sql);
                $in_eu = $nb_database->loadResult();
                if (nbf_common::nb_strlen($in_eu) > 0)
                {
                    foreach ($tax_rates as $tax_rate)
                    {
                        if ($tax_rate->country_code == 'EU')
                        {
                            if ($tax_rate->electronic_delivery) {
                                if (!$tax_rate_found_electronic) {
                                    $tax_rate_found_electronic = true;
                                    $exemption_allowed_electronic = $tax_rate->exempt_with_ref_no;
                                    $rate_of_tax_electronic = $tax_rate->tax_rate;
                                    $online_exempt_electronic = $tax_rate->online_exempt;
                                }
                            } else {
                                if (!$tax_rate_found) {
                                    $tax_rate_found = true;
                                    $exemption_allowed = $tax_rate->exempt_with_ref_no;
                                    $rate_of_tax = $tax_rate->tax_rate;
                                    $online_exempt = $tax_rate->online_exempt;
                                }
                            }
                            if ($tax_rate_found && $tax_rate_found_electronic) {
                                break;
                            }
                        }
                    }
                }
            }
            if (!$tax_rate_found)
            {
                foreach ($tax_rates as $tax_rate)
                {
                    if ($tax_rate->country_code == 'WW')
                    {
                        if ($tax_rate->electronic_delivery) {
                            if (!$tax_rate_found_electronic) {
                                $tax_rate_found_electronic = true;
                                $exemption_allowed_electronic = $tax_rate->exempt_with_ref_no;
                                $rate_of_tax_electronic = $tax_rate->tax_rate;
                                $online_exempt_electronic = $tax_rate->online_exempt;
                            }
                        } else {
                            if (!$tax_rate_found) {
                                $tax_rate_found = true;
                                $exemption_allowed = $tax_rate->exempt_with_ref_no;
                                $rate_of_tax = $tax_rate->tax_rate;
                                $online_exempt = $tax_rate->online_exempt;
                            }
                        }
                        if ($tax_rate_found && $tax_rate_found_electronic) {
                            break;
                        }
                    }
                }
            }

            if (nbf_common::nb_strlen($client_data->tax_exemption_code) > 0 && $exemption_allowed)
            {
                $sql = "UPDATE #__nbill_orders
                            LEFT JOIN #__nbill_product ON #__nbill_orders.product_id = #__nbill_product.id
                            SET #__nbill_orders.tax_exemption_code = '" . $client_data->tax_exemption_code . "', #__nbill_orders.total_tax_amount = 0, #__nbill_orders.total_shipping_tax = 0
                            WHERE #__nbill_orders.client_id = $client_id AND (#__nbill_product.electronic_delivery IS NULL OR #__nbill_product.electronic_delivery = 0)";
                $nb_database->setQuery($sql);
                $nb_database->query();
            }
            else if (nbf_common::nb_strlen($client_data->tax_exemption_code) == 0)
            {
                //Re-instate tax for this client's orders
                $sql = "SELECT #__nbill_orders.id, #__nbill_orders.net_price, #__nbill_orders.shipping_id, #__nbill_orders.total_shipping_price, #__nbill_orders.is_online, #__nbill_product.electronic_delivery
                            FROM #__nbill_orders
                            LEFT JOIN #__nbill_product ON #__nbill_orders.product_id = #__nbill_product.id
                            WHERE #__nbill_orders.client_id = " . intval($client_id);
                $nb_database->setQuery($sql);
                $orders = $nb_database->loadObjectList();
                if ($orders)
                {
                    $config = nBillConfigurationService::getInstance()->getConfig();
                    foreach ($orders as $order)
                    {
                        if ($order->is_online && $online_exempt)
                        {
                            continue;
                        }
                        $shipping_tax_rate = $order->electronic_delivery ? $rate_of_tax_electronic : $rate_of_tax;
                        if ($order->shipping_id > 0)
                        {
                            //Find out if shipping has its own rate of tax
                            $sql = "SELECT is_taxable, tax_rate_if_different FROM #__nbill_shipping WHERE id = " . intval($order->shipping_id);
                            $nb_database->setQuery($sql);
                            $nb_database->loadObject($shipping);
                            if ($shipping)
                            {
                                if (!$shipping->is_taxable)
                                {
                                    $shipping_tax_rate = 0;
                                }
                                else
                                {
                                    if ($shipping->tax_rate_if_different > 0)
                                    {
                                        $shipping_tax_rate = $shipping->tax_rate_if_different;
                                    }
                                }
                            }
                        }
                        $tax_amount = format_number(($order->net_price / 100) * ($order->electronic_delivery ? $rate_of_tax_electronic : $rate_of_tax), null, false, true, $config->precision_currency);
                        $shipping_tax_amount = format_number(($order->total_shipping_price / 100) * $shipping_tax_rate, null, false, true, $config->precision_currency);
                        $sql = "UPDATE #__nbill_orders SET total_tax_amount = $tax_amount, total_shipping_tax = $shipping_tax_amount,
                                        tax_exemption_code = '' WHERE id = " . intval($order->id);
                        $nb_database->setQuery($sql);
                        $nb_database->query();
                    }
                }
            }
        }
    }

    public static function get_tax_rates($invoice, $invoice_items, $shipping, $tax_info, &$tax_name, &$tax_rates, &$tax_rate_amounts, $separate_all = false, $use_invoice_item_rate = false, $include_zeros = false, $suffix_electronic = false)
    {
        //For backward compatibility
        $dummy = null;
        return self::get_tax_rates_standard($invoice, $invoice_items, $shipping, $tax_info, $tax_name, $tax_rates, $tax_rate_amounts, $dummy, $dummy, $separate_all, $use_invoice_item_rate, $include_zeros, $suffix_electronic);
    }

    public static function get_tax_rates_standard($invoice, $invoice_items, $shipping, $tax_info, &$tax_name, &$tax_rates, &$tax_rate_amounts, &$standard_tax_rate, &$standard_shipping_tax_rate, $separate_all = false, $use_invoice_item_rate = false, $include_zeros = false, $suffix_electronic = false)
    {
        //Work out standard tax rate based on client tax zone/country or failing that, vendor country
        $tax_name = $invoice->tax_abbreviation;
        $standard_tax_rate = 0;
        $electronic_tax_rate = 0;
        $tax_rate_found = false;
        $electronic_tax_rate_found = false;
        $percentage = 0;
        $electronic_rate_used = false;

        //Check for tax zone
        if (property_exists($invoice, "tax_zone") && strlen($invoice->tax_zone) > 0)
        {
            foreach ($tax_info as $tax_rate)
            {
                if ($tax_rate->vendor_id == $invoice->vendor_id && strlen($tax_rate->tax_zone) > 0 && $tax_rate->tax_zone == $invoice->tax_zone) {
                    if (self::applyTaxRate($tax_rate, $electronic_tax_rate, $standard_tax_rate, $electronic_tax_rate_found, $tax_rate_found, $invoice, $tax_name)) {
                        break;
                    }
                }
            }
        }
        if (!$tax_rate_found)
        {
            //Check for client country
            foreach ($tax_info as $tax_rate)
            {
                if ($tax_rate->vendor_id == $invoice->vendor_id && $tax_rate->country_code == $invoice->billing_country) {
                    if (self::applyTaxRate($tax_rate, $electronic_tax_rate, $standard_tax_rate, $electronic_tax_rate_found, $tax_rate_found, $invoice, $tax_name)) {
                        break;
                    }
                }
            }
        }
        if (!$tax_rate_found && $invoice->in_eu)
        {
            //Check for EU
            foreach ($tax_info as $tax_rate)
            {
                if ($tax_rate->vendor_id == $invoice->vendor_id && $tax_rate->country_code == "EU")
                {
                    if (self::applyTaxRate($tax_rate, $electronic_tax_rate, $standard_tax_rate, $electronic_tax_rate_found, $tax_rate_found, $invoice, $tax_name)) {
                        break;
                    }
                }
            }
        }
        if (!$tax_rate_found && strlen($invoice->billing_country) > 0)
        {
            //Check for WW
            foreach ($tax_info as $tax_rate)
            {
                if ($tax_rate->vendor_id == $invoice->vendor_id && $tax_rate->country_code == "WW")
                {
                    if (self::applyTaxRate($tax_rate, $electronic_tax_rate, $standard_tax_rate, $electronic_tax_rate_found, $tax_rate_found, $invoice, $tax_name)) {
                        break;
                    }
                }
            }
        }

        if (!$tax_rate_found)
        {
            //Check for vendor's country
            foreach ($tax_info as $tax_rate)
            {
                if ($tax_rate->vendor_id == $invoice->vendor_id && $tax_rate->country_code == $invoice->vendor_country)
                {
                    if (self::applyTaxRate($tax_rate, $electronic_tax_rate, $standard_tax_rate, $electronic_tax_rate_found, $tax_rate_found, $invoice, $tax_name)) {
                        break;
                    }
                }
            }
        }

        if (strlen($tax_name) == 0)
        {
            $tax_name = NBILL_TAX;
        }

        //Apply section discounts
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.process.discount.class.php");
        nbf_discount::apply_section_discounts($invoice_items, $invoice->document_id);

        //Construct arrays of tax rates and amounts
        $tax_rates[$invoice->document_id] = array();
        $tax_rate_amounts[$invoice->document_id] = array();
        $i = 0;
        foreach ($invoice_items as &$invoice_item)
        {
            $electronic_rate_used = $invoice_item->electronic_delivery;
            if ($invoice_item->document_id == $invoice->document_id)
            {
                if ($include_zeros || ($invoice_item->tax_for_item != 0 && $invoice_item->net_price_for_item != 0))
                {
                    if ($use_invoice_item_rate)
                    {
                        $percentage = $invoice_item->tax_rate_for_item;
                    }
                    else
                    {
                        //See if amount matches invoice rate (based on either net total + tax rate or gross total - tax rate). If not, check standard rate. If not, work out overridden rate
                        if (float_cmp(format_number((($invoice_item->net_price_for_item / 100) * $invoice_item->tax_rate_for_item) * 100, 0) / 100, $invoice_item->tax_for_item)
                            || float_cmp(format_number(($invoice_item->gross_price_for_item / ($invoice_item->tax_rate_for_item + 100)) * 100), format_number($invoice_item->net_price_for_item))) {
                            $percentage = $invoice_item->tax_rate_for_item;
                        } else if (float_cmp(format_number((($invoice_item->net_price_for_item / 100) * $electronic_tax_rate) * 100, 0) / 100, $invoice_item->tax_for_item)
                            || float_cmp(format_number(($invoice_item->gross_price_for_item / ($electronic_tax_rate + 100)) * 100), format_number($invoice_item->net_price_for_item))) {
                            $percentage = $electronic_tax_rate;
                        } else if (float_cmp(format_number((($invoice_item->net_price_for_item / 100) * $standard_tax_rate) * 100, 0) / 100, $invoice_item->tax_for_item)
                            || float_cmp(format_number(($invoice_item->gross_price_for_item / ($standard_tax_rate + 100)) * 100), format_number($invoice_item->net_price_for_item))) {
                            $percentage = $standard_tax_rate;
                        } else {
                            $percentage = format_number(($invoice_item->tax_for_item / $invoice_item->net_price_for_item) * 100);
                            //See if rounding to the nearest 0.5% will yield a valid result
                            $test_percentage = format_number(self::round_to_nearest(format_number($percentage * 10, 0), 5) / 10);
                            if (float_cmp(format_number((($invoice_item->net_price_for_item / 100) * $test_percentage) * 100, 0) / 100, $invoice_item->tax_for_item)
                                    || float_cmp(format_number(($invoice_item->gross_price_for_item / ($test_percentage + 100)) * 100), format_number($invoice_item->net_price_for_item))) {
                                $percentage = $test_percentage;
                            } else {
                                //See if rounding to the nearest 0.1% will yield a valid result
                                $test_percentage = format_number(format_number($percentage * 10, 0) / 10);
                                if (float_cmp(format_number((($invoice_item->net_price_for_item / 100) * $test_percentage) * 100, 0) / 100, $invoice_item->tax_for_item)
                                    || float_cmp(format_number(($invoice_item->gross_price_for_item / ($test_percentage + 100)) * 100), format_number($invoice_item->net_price_for_item))) {
                                    $percentage = $test_percentage;
                                }
                            }
                            if (!$percentage) {
                                //Otherwise, take what we're given
                                $percentage = format_number(($invoice_item->tax_for_item / $invoice_item->net_price_for_item) * 100);
                            }
                        }
                    }

                    $percentage = format_number($percentage, 'tax_rate');
                    if ($suffix_electronic && $electronic_rate_used) {
                        $percentage .= ' e';
                    }
                    $rate_exists = false;
                    foreach ($tax_rates[$invoice->document_id] as $j=>$value)
                    {
                        if ($tax_rates[$invoice->document_id][$j] == $percentage) {
                            $tax_rate_amounts[$invoice->document_id][$j] = float_add($tax_rate_amounts[$invoice->document_id][$j], $invoice_item->tax_for_item);
                            $rate_exists = true;
                            break;
                        }
                    }
                    if (!$rate_exists || $separate_all) {
                        $array_key = $i;
                        if ($separate_all) {
                            //Each invoice item will have its own entry in the array, and we need to be able to match them up
                            $array_key = $invoice_item->id . "_item";
                        }
                        $tax_rates[$invoice->document_id][$array_key] = $percentage;
                        if (!array_key_exists($array_key, $tax_rate_amounts[$invoice->document_id])) {
                            if ($array_key == $i) {
                                while (count($tax_rate_amounts[$invoice->document_id]) <= $i)
                                {
                                    $tax_rate_amounts[$invoice->document_id][] = '';  //Make sure index exists (avoids PHP Notice)
                                }
                            }
                            $tax_rate_amounts[$invoice->document_id][$array_key] = '';  //Make sure index exists (avoids PHP Notice)
                        }
                        $tax_rate_amounts[$invoice->document_id][$array_key] = float_add($tax_rate_amounts[$invoice->document_id][$array_key], $invoice_item->tax_for_item);
                        $i++;
                    }
                }

                if (($include_zeros || $invoice_item->tax_for_shipping != 0) && $invoice_item->shipping_for_item != 0) {
                    $standard_shipping_tax_rate = 0;
                    if (nbf_common::nb_strlen($invoice_item->shipping_id) > 0 && $invoice_item->shipping_id >= 0) {
                        foreach ($shipping as $shipping_item)
                        {
                            if ($shipping_item->vendor_id == $invoice->vendor_id && $shipping_item->id == $invoice_item->shipping_id) {
                                if (!$shipping_item->is_taxable) {
                                    $standard_shipping_tax_rate = 0;
                                } else {
                                    $standard_shipping_tax_rate = $shipping_item->tax_rate_if_different;
                                }
                                if ($standard_shipping_tax_rate == 0 && $shipping_item->is_taxable) {
                                    $standard_shipping_tax_rate = $electronic_rate_used ? $electronic_tax_rate : $standard_tax_rate;
                                }
                            }
                        }
                    } else {
                        //No known shipping tax rate, default to standard rate
                        $standard_shipping_tax_rate = $electronic_rate_used ? $electronic_tax_rate : $standard_tax_rate;;
                    }

                    if ($use_invoice_item_rate) {
                        $percentage = $invoice_item->tax_rate_for_shipping;
                    } else {
                        //See if amount matches invoice rate. If not, check standard rate. If not, work out overridden rate
                        if (float_cmp(str_replace(",", "", format_number((($invoice_item->shipping_for_item / 100) * $invoice_item->tax_rate_for_shipping) * 100, 0, false, true)) / 100, $invoice_item->tax_for_shipping)) {
                            $percentage = $invoice_item->tax_rate_for_shipping;
                        } else if (float_cmp(str_replace(",", "", format_number((($invoice_item->shipping_for_item / 100) * $standard_shipping_tax_rate) * 100, 0, false, true)) / 100, $invoice_item->tax_for_shipping)) {
                            $percentage = $standard_shipping_tax_rate;
                        } else {
                            $percentage = format_number(($invoice_item->tax_for_shipping / $invoice_item->shipping_for_item) * 100);
                            //See if rounding to the nearest 0.5% will yield a valid result
                            $test_percentage = format_number(self::round_to_nearest(format_number($percentage * 10, 0), 5) / 10);
                            if (float_cmp(format_number((($invoice_item->shipping_for_item / 100) * $test_percentage) * 100, 0) / 100, $invoice_item->tax_for_shipping)
                                    || float_cmp(format_number((($invoice_item->shipping_for_item + $invoice_item->tax_for_shipping) / ($test_percentage + 100)) * 100), format_number($invoice_item->net_price_for_shipping))) {
                                $percentage = $test_percentage;
                            } else {
                                //See if rounding to the nearest 0.1% will yield a valid result
                                $test_percentage = format_number(format_number($percentage * 10, 0) / 10);
                                if (float_cmp(format_number((($invoice_item->net_price_for_shipping / 100) * $test_percentage) * 100, 0) / 100, $invoice_item->tax_for_shipping)
                                    || float_cmp(format_number((($invoice_item->net_price_for_shipping + $invoice_item->tax_for_shipping) / ($test_percentage + 100)) * 100), format_number($invoice_item->net_price_for_shipping))) {
                                    $percentage = $test_percentage;
                                }
                            }
                            if (!$percentage) {
                                //Otherwise, take what we're given
                                $percentage = format_number(($invoice_item->tax_for_shipping / $invoice_item->shipping_for_item) * 100, false, true);
                            }
                        }
                    }
                    $rate_exists = false;
                    foreach ($tax_rates[$invoice->document_id] as $j=>$value)
                    {
                        if ($tax_rates[$invoice->document_id][$j] == $percentage) {
                            $tax_rate_amounts[$invoice->document_id][$j] = float_add($tax_rate_amounts[$invoice->document_id][$j], $invoice_item->tax_for_shipping);
                            $rate_exists = true;
                            break;
                        }
                    }
                    if (!$rate_exists || $separate_all) {
                        $array_key = $i;
                        if ($separate_all) {
                            //Each invoice item will have its own entry in the array, and we need to be able to match them up
                            $array_key = $invoice_item->id . "_shipping";
                        }
                        $tax_rates[$invoice->document_id][$array_key] = $percentage;
                        if (!array_key_exists($array_key, $tax_rate_amounts[$invoice->document_id])) {
                            if ($array_key == $i) {
                                while (count($tax_rate_amounts[$invoice->document_id]) <= $i)
                                {
                                    $tax_rate_amounts[$invoice->document_id][] = '';  //Make sure index exists (avoids PHP Notice)
                                }
                            }
                            $tax_rate_amounts[$invoice->document_id][$array_key] = '';  //Make sure index exists (avoids PHP Notice)
                        }
                        $tax_rate_amounts[$invoice->document_id][$array_key] = float_add($tax_rate_amounts[$invoice->document_id][$array_key], $invoice_item->tax_for_shipping); //$tax_rate_amounts[$invoice->document_id][$array_key] += $invoice_item->tax_for_shipping;
                        $i++;
                    }
                }
            }
        }
    }

    protected static function applyTaxRate($tax_rate, &$electronic_tax_rate, &$standard_tax_rate, &$electronic_tax_rate_found, &$tax_rate_found, &$invoice, &$tax_name)
    {
        if (!$electronic_tax_rate_found && $tax_rate->electronic_delivery) {
            $electronic_tax_rate_found = true;
            $electronic_tax_rate = format_number($tax_rate->tax_rate, 'tax_rate');
        } else if (!$tax_rate_found) {
            $tax_rate_found = true;
            $standard_tax_rate = format_number($tax_rate->tax_rate, 'tax_rate');
        }
        if (strlen(@$invoice->tax_abbreviation) == 0) {
            $tax_name = $tax_rate->tax_abbreviation;
        }
        if ($electronic_tax_rate_found && $tax_rate_found) {
            return true;
        }
        return false;
    }

    /**
    * Gets array of existing tax rates and amounts from the invoice items supplied
    * @param mixed $invoice_items
    * @param mixed $tax_rates
    * @param mixed $tax_amounts
    */
    public static function get_existing_tax_rates(&$document_items, &$tax_rates, &$tax_amounts)
    {
        $tax_rates = array();
        $tax_amounts = array();
        $tax_rates_electronic = array();
        $tax_amounts_electronic = array();

        $tax_index = 0;
        $tax_index_electronic = 0;
        if ($document_items)
        {
            foreach ($document_items as $document_item)
            {
                if ($document_item->tax_rate_for_item != 0)
                {
                    if ($document_item->electronic_delivery) {
                        $this_index = array_search($document_item->tax_rate_for_item, $tax_rates_electronic);
                    } else {
                        $this_index = array_search($document_item->tax_rate_for_item, $tax_rates);
                    }
                    if ($this_index === false)
                    {
                        if ($document_item->electronic_delivery) {
                            $this_index = $tax_index_electronic;
                            $tax_index_electronic++;
                            $tax_amounts_electronic[$this_index] = 0;
                        } else {
                            $this_index = $tax_index;
                            $tax_index++;
                            $tax_amounts[$this_index] = 0;
                        }
                    }
                    if ($document_item->electronic_delivery) {
                        $tax_rates_electronic[$this_index] = $document_item->tax_rate_for_item;
                        $tax_amounts_electronic[$this_index] = float_add($tax_amounts_electronic[$this_index], $document_item->tax_for_item);
                    } else {
                        $tax_rates[$this_index] = $document_item->tax_rate_for_item;
                        $tax_amounts[$this_index] = float_add($tax_amounts[$this_index], $document_item->tax_for_item);
                    }
                }
                if ($document_item->shipping_id > 0)
                {
                    if ($document_item->tax_rate_for_shipping != 0)
                    {
                        if ($document_item->electronic_delivery) {
                            $this_index = array_search($document_item->tax_rate_for_shipping, $tax_rates_electronic);
                        } else {
                            $this_index = array_search($document_item->tax_rate_for_shipping, $tax_rates);
                        }
                        if ($this_index === false)
                        {
                            if ($document_item->electronic_delivery) {
                                $this_index = $tax_index_electronic;
                                $tax_index_electronic++;
                                $tax_amounts_electronic[$this_index] = 0;
                            } else {
                                $this_index = $tax_index;
                                $tax_index++;
                                $tax_amounts[$this_index] = 0;
                            }
                        }
                        if ($document_item->electronic_delivery) {
                            $tax_rates_electronic[$this_index] = $document_item->tax_rate_for_shipping;
                            $tax_amounts_electronic[$this_index] = float_add($tax_amounts_electronic[$this_index], $document_item->tax_for_shipping);
                        } else {
                            $tax_rates[$this_index] = $document_item->tax_rate_for_shipping;
                            $tax_amounts[$this_index] = float_add($tax_amounts[$this_index], $document_item->tax_for_shipping);
                        }
                    }
                }
            }
        }
        $tax_rates = array_merge($tax_rates, $tax_rates_electronic);
        $tax_amounts = array_merge($tax_amounts, $tax_amounts_electronic);
    }

    function round_to_nearest($number, $toNearest = 5)
    {
        $retval = 0;
        $mod = $number % $toNearest;
        if( $mod >= 0 )
        {
            $retval = ($mod > ($toNearest / 2)) ? $number + ($toNearest - $mod) : $number - $mod;
        }
        else
        {
            $retval = ($mod > (-$toNearest / 2)) ? $number - $mod : $number + (-$toNearest - $mod);
        }
        return $retval;
    }

    /**
    * Return the standard tax rate that would normally be used for this client/product - for use in comparing whether
    * a tax rate is standard or custom (so if the product passed in here has a custom tax rate, it will be ignored by
    * this function)
    * @param mixed $vendor_id
    * @param mixed $entity_id
    * @param mixed $product_id
    * @param boolean $is_online
    * @param boolean $invert_product_electronic If product has just changed from electronic to non-electronic or vice-versa, we might need to get the tax rate that WOULD have applied BEFORE the change, in which case, this flag should be set to true (causing it to use electronic if the product is currently NOT electronic, and vice-versa)
    */
    public static function get_standard_tax_rate($vendor_id, $entity_id, $product_id = null, $is_online = true, $invert_product_electronic = false)
    {
        $nb_database = nbf_cms::$interop->database;

        $found_tax = false;
        $client = null;
        $tax_record = null;
        $sql = "SELECT #__nbill_entity.country, #__nbill_entity.tax_zone, #__nbill_entity.tax_exemption_code,
                        #__nbill_xref_eu_country_codes.code AS in_eu
                        FROM #__nbill_entity
                        LEFT JOIN #__nbill_xref_eu_country_codes ON #__nbill_entity.country = #__nbill_xref_eu_country_codes.code
                        WHERE #__nbill_entity.id = " . intval($entity_id);
        $nb_database->setQuery($sql);
        $nb_database->loadObject($client);

        $sql = "SELECT vendor_country FROM #__nbill_vendor WHERE id = " . intval($vendor_id);
        $nb_database->setQuery($sql);
        $vendor_country = $nb_database->loadResult();

        $tax = null;
        if ($client)
        {
            $tax = self::find_tax_rate($vendor_id, $vendor_country, $client->tax_zone, $client->country, $client->in_eu, $product_id, $invert_product_electronic);
        }

        if ($tax != null)
        {
            if (($tax->online_exempt && $is_online) ||
                ($tax->exempt_with_ref_no && nbf_common::nb_strlen($client->tax_exemption_code) > 0))
            {
                return 0;
            }
            else
            {
                return $tax->tax_rate;
            }
        }
    }

    /**
    * @param mixed $vendor_id
    * @param mixed $vendor_country
    * @param mixed $client_tax_zone
    * @param mixed $country
    * @param mixed $in_eu
    * @param mixed $product_id
    * @param boolean $invert_product_electronic If product has just changed from electronic to non-electronic or vice-versa, we might need to get the tax rate that WOULD have applied BEFORE the change, in which case, this flag should be set to true (causing it to use electronic if the product is currently NOT electronic, and vice-versa)
    */
    public static function find_tax_rate($vendor_id, $vendor_country, $client_tax_zone, $country, $in_eu, $product_id = 0, $invert_product_electronic = false)
    {
        $nb_database = nbf_cms::$interop->database;
        $config = nBillConfigurationService::getInstance()->getConfig();
        $electronic_only = $config->default_electronic;
        $standard_only = !$config->default_electronic;
        if ($product_id) {
            $sql = "SELECT electronic_delivery FROM #__nbill_product WHERE id = " . intval($product_id);
            $nb_database->setQuery($sql);
            $electronic_only = $nb_database->loadResult();
            if ($invert_product_electronic) {
                $electronic_only = !$electronic_only;
            }
            $standard_only = !$electronic_only;
        }

        $sql = "SELECT #__nbill_tax.*, #__nbill_vendor.tax_reference_no FROM #__nbill_tax INNER JOIN #__nbill_vendor ON #__nbill_tax.vendor_id = #__nbill_vendor.id WHERE vendor_id = " . intval($vendor_id) . " ORDER BY electronic_delivery, country_code";
        $nb_database->setQuery($sql);
        $taxes = $nb_database->loadObjectList();

        $found_tax = false;
        $electronic_found_tax = false;
        $tax_record = null;

        //Try to find matching tax zone
        foreach ($taxes as $tax)
        {
            if (($electronic_only && !$tax->electronic_delivery) || ($standard_only && $tax->electronic_delivery)) {
                continue;
            }
            if ($tax->country_code == $country && nbf_common::nb_strlen($tax->tax_zone) > 0 && $tax->tax_zone == $client_tax_zone)
            {
                if ($tax->electronic_delivery) {
                    $electronic_found_tax = true;
                } else {
                    $found_tax = true;
                }
                $tax_record = $tax;
                break;
            }
        }

        if (!$found_tax)
        {
            //Check for EU tax zones
            foreach ($taxes as $tax)
            {
                if (($electronic_only && !$tax->electronic_delivery) || ($standard_only && $tax->electronic_delivery)) {
                    continue;
                }
                if ($tax->country_code == 'EU' && $in_eu && nbf_common::nb_strlen($tax->tax_zone) > 0 && $tax->tax_zone == $client_tax_zone)
                {
                    if ($tax->electronic_delivery) {
                        $electronic_found_tax = true;
                    } else {
                        $found_tax = true;
                    }
                    $tax_record = $tax;
                    break;
                }
            }
        }

        if (!$found_tax)
        {
            //Check for Worldwide tax zones
            foreach ($taxes as $tax)
            {
                if (($electronic_only && !$tax->electronic_delivery) || ($standard_only && $tax->electronic_delivery)) {
                    continue;
                }
                if ($tax->country_code == 'WW' && nbf_common::nb_strlen($tax->tax_zone) > 0 && $tax->tax_zone == $client_tax_zone)
                {
                    if ($tax->electronic_delivery) {
                        $electronic_found_tax = true;
                    } else {
                        $found_tax = true;
                    }
                    $tax_record = $tax;
                    break;
                }
            }
        }

        //Try to find matching country
        if (!$found_tax)
        {
            foreach ($taxes as $tax)
            {
                if (($electronic_only && !$tax->electronic_delivery) || ($standard_only && $tax->electronic_delivery)) {
                    continue;
                }
                if ($tax->country_code == $country && nbf_common::nb_strlen($tax->tax_zone) == 0 && nbf_common::nb_strlen($client_tax_zone) == 0)
                {
                    if (!$tax->electronic_delivery || !$electronic_found_tax) {
                        if ($tax->electronic_delivery) {
                            $electronic_found_tax = true;
                        } else {
                            $found_tax = true;
                        }
                        $tax_record = $tax;
                        break;
                    }
                }
            }
        }

        //Check for EU
        if (!$found_tax && $in_eu && $country != $vendor_country)
        {
            foreach ($taxes as $tax)
            {
                if (($electronic_only && !$tax->electronic_delivery) || ($standard_only && $tax->electronic_delivery)) {
                    continue;
                }
                if (nbf_common::nb_strtoupper($tax->country_code) == 'EU' && nbf_common::nb_strlen($tax->tax_zone) == 0 && nbf_common::nb_strlen($client_tax_zone) == 0)
                {
                    if (!$tax->electronic_delivery || !$electronic_found_tax) {
                        if ($tax->electronic_delivery) {
                            $electronic_found_tax = true;
                        } else {
                            $found_tax = true;
                        }
                        $tax_record = $tax;
                        break;
                    }
                }
            }
        }

        //Go large
        if (!$found_tax && $country != $vendor_country)
        {
            foreach ($taxes as $tax)
            {
                if (($electronic_only && !$tax->electronic_delivery) || ($standard_only && $tax->electronic_delivery)) {
                    continue;
                }
                if (nbf_common::nb_strtoupper($tax->country_code) == 'WW' && nbf_common::nb_strlen($tax->tax_zone) == 0 && nbf_common::nb_strlen($client_tax_zone) == 0)
                {
                    if (!$tax->electronic_delivery || !$electronic_found_tax) {
                        if ($tax->electronic_delivery) {
                            $electronic_found_tax = true;
                        } else {
                            $found_tax = true;
                        }
                        $tax_record = $tax;
                        break;
                    }
                }
            }
        }
        return $tax_record;
    }
}