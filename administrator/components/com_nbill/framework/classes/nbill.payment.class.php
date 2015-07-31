<?php
/**
* Class file just containing static methods relating to payment calculations and processing.
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
* Class just contains static functions for use anywhere within the code
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_payment
{
    /**
    * Adds applicable discount information to the order array
    * @param mixed $currency
    * @param mixed $country
    * @param mixed $order_details Order details array (by value)
    * @param array $discounts Order details array (by reference - discounts will be added to this)
    * @param mixed $discount_voucher_code
    * @param mixed $shipping
    * @param mixed $shipping_total
    * @param mixed $shipping_tax
    * @param mixed $shipping_tax_rate
    * @param mixed $shipping_fixed_price
    * @param mixed $renewal
    * @param mixed $order_id
    * @param mixed $regular_shipping_total
    * @param mixed $regular_shipping_tax
    * @param mixed $is_invoice
    * @param mixed $payment_gateway
    * @param mixed $gateway_only
    * @param int $vendor_id
    */
    public static function add_discounts($currency, $country, $order_details, &$discounts, $discount_voucher_code, $shipping = false, $shipping_total = null, $shipping_tax = null, $shipping_tax_rate = null, $shipping_fixed_price = false, $renewal = false, $order_id = null, $regular_shipping_total = null, $regular_shipping_tax = null, $is_invoice = false, $payment_gateway = null, $gateway_only = false, $vendor_id = null, $user_id = null)
    {
        
    }

    

    /**
    * Calculates totals (net, tax, gross, shipping) before handing over to payment gateway
    *
    * @param array $orders
    * @param string $currency
    * @param string $country
    * @param int $shipping_id
    * @param string $discount_voucher_code
    * @param nbf_totals $standard_totals
    * @param nbf_totals $regular_totals
    * @param nbf_totals $actual_totals
    * @param mixed $normal_tax_rate
    * @param mixed $shipping_tax_rate
    * @param string $shipping_service
    * @param mixed $renewal
    * @param mixed $order_id
    * @param mixed $suppress_discounts
    * @param mixed $payment_plan_id
    * @param int $client_id For applying any client credit
    * @param int $vendor_id For applying any client credit
    * @param boolean $is_invoice Whether or not this is for payment of an invoice (so deferred payment plans can be charged in full rather than set to zero)
    */
    public static function calculate_totals(&$orders, $currency, $country, $shipping_id, $discount_voucher_code,
                    &$standard_totals, &$regular_totals, &$actual_totals, &$normal_tax_rate, &$shipping_tax_rate,
                    &$shipping_service,
                    $renewal = false, $order_id = null, $suppress_discounts = false, $payment_plan_id = 0,
                    $client_id = null, $vendor_id = null, $is_invoice = false, $payment_gateway = null)
    {
        $nb_database = nbf_cms::$interop->database;

        //Work out totals for the specified orders
        if (!$suppress_discounts)
        {
            self::add_discounts($currency, $country, $orders, $orders, $discount_voucher_code, false, null, null, null, null, $renewal, $order_id, null, null, $is_invoice, $payment_gateway);  //One copy by value, one by reference
        }

        //Locate highest normal tax rate in order list (in case shipping service is taxable @ normal rate)
        $normal_tax_rate = 0;
        if (is_array($orders))
        {
            foreach ($orders as $order)
            {
                if (isset($order['tax_rate']) && $order['tax_rate'] > $normal_tax_rate)
                {
                    $normal_tax_rate = $order['tax_rate'];
                }
            }
        }

        //Calculate totals
        $regular = false;
        if (is_array($orders))
        {
            foreach ($orders as $order)
            {
                $regular = @$order['payment_frequency'] && @$order['payment_frequency'] != 'XX' && @$order['payment_frequency'] != 'AA' && (!@$order['discount_id'] || $order['recurring']);
                if (isset($order['setup_fee']))
                {
                    $standard_totals->total_net = float_add($standard_totals->total_net, format_number($order['setup_fee']));
                }
                $standard_totals->total_net = float_add($standard_totals->total_net, format_number($order['net_price'] * $order['quantity']));

                if ($regular)
                {
                    if (isset($order['regular_net_price']))
                    {
                        //We have a separate regular price (probably a discount or fee)
                        $regular_totals->total_net = float_add($regular_totals->total_net, format_number($order['regular_net_price'] * $order['quantity']));
                    }
                    else
                    {
                        $regular_totals->total_net = float_add($regular_totals->total_net, format_number($order['net_price'] * $order['quantity']));
                    }
                }
                if ($renewal)
                {
                    //Pick up tax from order record
                    if (isset($order['setup_fee_tax_amount']))
                    {
                        $standard_totals->total_tax = float_add($standard_totals->total_tax, format_number($order['setup_fee_tax_amount']));
                    }
                    if (isset($order['tax_amount']))
                    {
                        $standard_totals->total_tax = float_add($standard_totals->total_tax, format_number($order['tax_amount'] * $order['quantity']));
                        if ($regular)
                        {
                            if (isset($order['regular_tax_amount']))
                            {
                                //We have a separate regular price (probably a discount or fee)
                                $regular_totals->total_tax = float_add($regular_totals->total_tax, format_number($order['regular_tax_amount'] * $order['quantity']));
                            }
                            else
                            {
                                $regular_totals->total_tax = float_add($regular_totals->total_tax, format_number($order['tax_amount'] * $order['quantity']));
                            }
                        }
                    }
                }
                else
                {
                    //Calculate tax
                    $order_tax_rate = 0;
                    if (isset($order['tax_rate']))
                    {
                        $order_tax_rate = $order['tax_rate'];
                    }
                    if (isset($order['setup_fee']))
                    {
                        $standard_totals->total_tax = float_add($standard_totals->total_tax, $order['setup_fee_tax_amount']);
                    }
                    if (isset($order['tax_amount']) && format_number(($order['net_price'] / 100) * $order_tax_rate) != format_number($order['tax_amount']))
                    {
                        //The tax amount has been overridden and does not match the rate (which is ok in some situations)
                        $standard_totals->total_tax = float_add($standard_totals->total_tax, format_number($order['tax_amount']));
                    }
                    else
                    {
                        $standard_totals->total_tax = float_add($standard_totals->total_tax, format_number((format_number($order['net_price'] * $order['quantity']) / 100) * $order_tax_rate));
                    }

                    if ($regular)
                    {
                        if (isset($order['regular_net_price']))
                        {
                            //We have a separate regular price (probably a discount or fee)
                            if (isset($order['regular_tax_amount']) && format_number(($order['regular_net_price'] / 100) * $order_tax_rate) != format_number($order['regular_tax_amount']))
                            {
                                //The tax amount has been overridden and does not match the rate (which is ok in some situations)
                                $regular_totals->total_tax = float_add($regular_totals->total_tax, format_number($order['regular_tax_amount']));
                            }
                            else
                            {
                                $regular_totals->total_tax = float_add($regular_totals->total_tax, format_number((format_number($order['regular_net_price'] * $order['quantity']) / 100) * $order_tax_rate));
                            }
                        }
                        else
                        {
                            if (isset($order['tax_amount']) && format_number(($order['net_price'] / 100) * $order_tax_rate) != format_number($order['tax_amount']))
                            {
                                //The tax amount has been overridden and does not match the rate (which is ok in some situations)
                                $regular_totals->total_tax = float_add($regular_totals->total_tax, format_number($order['tax_amount']));
                            }
                            else
                            {
                                $regular_totals->total_tax = float_add($regular_totals->total_tax, format_number((format_number($order['net_price'] * $order['quantity']) / 100) * $order_tax_rate));
                            }
                        }
                    }
                }
            }
        }

        $shipping_services = array();
        $shipping_tax_rate = array();
        $shipping_fixed_price = false;
        self::calculate_shipping($shipping_id, $standard_totals, $regular_totals, $orders, $shipping_services, $currency, $shipping_tax_rate, $normal_tax_rate, $shipping_fixed_price, $is_invoice);

        if (!$suppress_discounts)
        {
            //Apply any shipping discounts
            $shipping_discount_total = 0;
            $shipping_tax_discount_total = 0;
            $regular_shipping_discount_total = 0;
            $regular_shipping_tax_discount_total = 0;
            $shipping_discounts = array();

            self::add_discounts($currency, $country, $orders, $shipping_discounts, $discount_voucher_code, true, $standard_totals->total_shipping, $standard_totals->total_shipping_tax, $shipping_tax_rate, $shipping_fixed_price, $renewal, $order_id, $regular_totals->total_shipping, $regular_totals->total_shipping_tax, false, $payment_gateway);
            foreach ($shipping_discounts as $shipping_discount)
            {
                //Add the discount amounts (which are negative numbers) to the net totals (still show full shipping price and add discounts to orders array so user can see what a discount they are getting, and so we know what shipping discount(s) to apply at order creation time)
                $shipping_discount_total = float_add($shipping_discount_total, format_number($shipping_discount['net_price']));
                $shipping_tax_discount_total = float_add($shipping_tax_discount_total, format_number($shipping_discount['tax_amount']));
                $regular = @$shipping_discount['payment_frequency'] && @$shipping_discount['payment_frequency'] != 'XX' && @$shipping_discount['payment_frequency'] != 'AA' && isset($shipping_discount['discount_id']) && $shipping_discount['discount_id'] && $shipping_discount['recurring'];
                if ($regular)
                {
                    $regular_shipping_discount_total = float_add($regular_shipping_discount_total, format_number($shipping_discount['regular_net_price']));
                    $regular_shipping_tax_discount_total = float_add($regular_shipping_tax_discount_total, format_number($shipping_discount['regular_tax_amount']));
                }
                $shipping_discount["is_shipping_discount"] = 1;
                $orders[] = $shipping_discount;
            }
            $standard_totals->total_shipping = float_add($standard_totals->total_shipping, $shipping_discount_total);
            $regular_totals->total_shipping = float_add($regular_totals->total_shipping, $regular_shipping_discount_total);
            $standard_totals->total_shipping_tax = float_add($standard_totals->total_shipping_tax, $shipping_tax_discount_total);
            $regular_totals->total_shipping_tax = float_add($regular_totals->total_shipping_tax, $regular_shipping_tax_discount_total);
        }

        if (count($shipping_services) > 0)
        {
            $shipping_service = implode("; ", $shipping_services);
        }

        $standard_totals->total_gross = format_number(float_add($standard_totals->total_net, float_add($standard_totals->total_tax, float_add($standard_totals->total_shipping, $standard_totals->total_shipping_tax))));
        $regular_totals->total_gross = format_number(float_add($regular_totals->total_net, float_add($regular_totals->total_tax, float_add($regular_totals->total_shipping, $regular_totals->total_shipping_tax))));

        

        $actual_totals->total_net = $standard_totals->total_net;
        $actual_totals->total_tax = $standard_totals->total_tax;
        $actual_totals->total_shipping = $standard_totals->total_shipping;
        $actual_totals->total_shipping_tax = $standard_totals->total_shipping_tax;
        $actual_totals->total_gross = $standard_totals->total_gross;

        
    }

    private static function calculate_shipping($shipping_id, &$standard_totals, &$regular_totals, &$orders, &$shipping_services, $currency, &$shipping_tax_rate, $normal_tax_rate, &$shipping_fixed_price, $is_invoice = false)
    {
        
    }

    /**
    * Split the total amount to pay into equal installments (any remainder is added to first payment)
    * @param nbf_totals $standard_totals
    * @param nbf_totals $first_totals
    * @param nbf_totals $regular_totals
    * @param int $no_of_installments
    */
    private static function calculate_installments($standard_totals, &$first_totals, &$regular_totals, $no_of_installments)
    {
        
    }

    public static function adjust_for_payment_plan($plan_id, &$no_of_payments, &$payment_frequency, &$standard_totals, &$actual_totals, &$invoice = null)
    {
        
    }

    public static function prepare_for_payment(&$payment_gateway, &$suppress_payment, &$standard_totals, &$regular_totals,
                        &$orders, &$normal_tax_rate, &$shipping_tax_rate, &$pending_order_id,
                        &$form_id, &$vendor_id, &$auto_renew, &$payment_frequency, &$currency, &$abort,
                        &$expiry_date = 0, &$shipping_service, &$relating_to, &$no_of_payments,
                        &$billing_data, &$document_no, &$turn_on_auto_renew, &$document_ids,
                        &$tax_rates, &$tax_amounts, $callback_file = "", $callback_function = "", $entity_id = 0)
    {
        $nb_database = nbf_cms::$interop->database;
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
        $g_tx_id = 0;

        //Load language file for general gateway usage
        nbf_common::load_language("gateway");

        //Load specific gateway language file
        if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$payment_gateway/$payment_gateway." . nbf_cms::$interop->language . ".php"))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$payment_gateway/$payment_gateway." . nbf_cms::$interop->language . ".php");
        }
        else if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$payment_gateway/$payment_gateway" . "_" . nbf_cms::$interop->language . ".php"))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$payment_gateway/$payment_gateway" . "_" . nbf_cms::$interop->language . ".php");
        }
        else if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$payment_gateway/$payment_gateway.en-GB.php"))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$payment_gateway/$payment_gateway.en-GB.php");
        }
        else if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$payment_gateway/$payment_gateway.english.php"))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.backward.compatibility.php");
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$payment_gateway/$payment_gateway.english.php");
        }
        else if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$payment_gateway/$payment_gateway" . "_english.php"))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.backward.compatibility.php");
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$payment_gateway/$payment_gateway" . "_english.php");
        }

        if ($turn_on_auto_renew)
        {
            $auto_renew = 1;
        }

        $standard_totals->total_gross = format_number($standard_totals->total_gross);
        $standard_totals->total_net = format_number($standard_totals->total_net);
        $standard_totals->total_shipping = format_number($standard_totals->total_shipping);
        $standard_totals->total_shipping_tax = format_number($standard_totals->total_shipping_tax);
        $standard_totals->total_tax = format_number($standard_totals->total_tax);
        $regular_totals->total_gross = format_number($regular_totals->total_gross);
        $regular_totals->total_net = format_number($regular_totals->total_net);
        $regular_totals->total_shipping = format_number($regular_totals->total_shipping);
        $regular_totals->total_shipping_tax = format_number($regular_totals->total_shipping_tax);
        $regular_totals->total_tax = format_number($regular_totals->total_tax);

        //If no of payments specified, but not expiry date, work out when the expiry date will be
        if (!$expiry_date && $no_of_payments)
        {
            if ($no_of_payments == 1)
            {
                //Only one billing cycle, so make it one-off
                $payment_frequency = "AA";
            }
            else
            {
                $pay_date = nbf_common::nb_time();
                for ($i = 0; $i < $no_of_payments; $i++)
                {
                    $pay_date = nbf_date::get_next_payment_date(time(), $pay_date, $payment_frequency == "AA" || $payment_frequency == "XX" ? "CC" : $payment_frequency); //Default to monthly if not recurring (in case of user subscription and using workaround)
                }
                $expiry_date = $pay_date;

                //Set to 23:59:59 on the day before (so we don't take an extra payment)
                $expiry_date -= 86400;
                $date_parts = @getdate($expiry_date);
                if (@count($date_parts) > 0)
                {
                    $expiry_date = nbf_common::nb_mktime(23, 59, 59, $date_parts['mon'], $date_parts['mday'], $date_parts['year']);
                }
            }
        }

        //If expiry date specified but not no. of payments, work out how many payments need to be taken
        if ($expiry_date && $no_of_payments == 0)
        {
            $interval_date = nbf_common::nb_time();
            while ($interval_date < $expiry_date)
            {
                $interval_date = nbf_date::get_next_payment_date(time(), $interval_date, $payment_frequency, false);
                $no_of_payments++;
                if ($no_of_payments > 1000)
                {
                    $no_of_payments = 0;
                    break;
                }
            }
        }

        $abort = false; //If gateway sets this to true, payment has been aborted
        if (!$form_id)
        {
            $form_id = 0;
        }
        $billing_name = "";
        $billing_address = "";

        if ($payment_gateway == -1 || nbf_common::nb_strtoupper($payment_gateway) == nbf_common::nb_strtoupper(NBILL_OFFLINE))
        {
            $suppress_payment = true;
        }
        if (!$suppress_payment)
        {
            //Construct $billing_name and $billing_address values
            $billing_name = trim(nbf_common::get_param($billing_data, 'first_name') . " " . nbf_common::get_param($billing_data, 'last_name'));
            $billing_address = nbf_common::get_param($billing_data, 'address_1');
            if (nbf_common::nb_strlen(trim(nbf_common::get_param($billing_data, 'address_2'))) > 0)
            {
                if (nbf_common::nb_strlen($billing_address) > 0)
                {
                    $billing_address .= ", ";
                }
                $billing_address .= nbf_common::get_param($billing_data, 'address_2');
            }
            if (nbf_common::nb_strlen(trim(nbf_common::get_param($billing_data, 'address_3'))) > 0)
            {
                if (nbf_common::nb_strlen($billing_address) > 0)
                {
                    $billing_address .= ", ";
                }
                $billing_address .= nbf_common::get_param($billing_data, 'address_3');
            }
            if (nbf_common::nb_strlen(trim(nbf_common::get_param($billing_data, 'town'))) > 0)
            {
                if (nbf_common::nb_strlen($billing_address) > 0)
                {
                    $billing_address .= ", ";
                }
                $billing_address .= nbf_common::get_param($billing_data, 'town');
            }
            if (nbf_common::nb_strlen(trim(nbf_common::get_param($billing_data, 'state'))) > 0)
            {
                if (nbf_common::nb_strlen($billing_address) > 0)
                {
                    $billing_address .= ", ";
                }
                $billing_address .= nbf_common::get_param($billing_data, 'state');
            }
            if (nbf_common::nb_strlen(trim(nbf_common::get_param($billing_data, 'postcode'))) > 0)
            {
                if (nbf_common::nb_strlen($billing_address) > 0)
                {
                    $billing_address .= ", ";
                }
                $billing_address .= nbf_common::get_param($billing_data, 'postcode');
            }

            if (count($tax_rates) == 0 && count($tax_amounts) == 0)
            {
                //Collate tax summary (for gateway use, if required)
                $tax_rates = array();
                $tax_amounts = array();
                if (is_array($orders))
                {
                    foreach ($orders as $order)
                    {
                        $order_tax_rate = isset($order['tax_rate']) ? $order['tax_rate'] : $normal_tax_rate;
                        $rate_key = array_search($order_tax_rate, $tax_rates);
                        if ($rate_key === false)
                        {
                            $tax_rates[] = $order_tax_rate;
                            $rate_key = count($tax_rates) - 1;
                        }
                        while (count($tax_amounts) <= $rate_key)
                        {
                            $tax_amounts[] = '';
                        }
                        $tax_amounts[$rate_key] = float_add($tax_amounts[$rate_key], $order['tax_amount']);
                        $tax_amounts[$rate_key] = str_replace(",", ".", $tax_amounts[$rate_key] . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot
                    }
                }

                //Only one shipping tax rate is supported
                if (is_array($shipping_tax_rate) && count($shipping_tax_rate) > 0)
                {
                    reset($shipping_tax_rate);
                    $shipping_tax_rate = current($shipping_tax_rate);
                }
                else
                {
                    $shipping_tax_rate = 0;
                }
                if ($standard_totals->total_shipping_tax > 0)
                {
                    $rate_key = array_search($shipping_tax_rate, $tax_rates);
                    if ($rate_key === false)
                    {
                        $tax_rates[] = $shipping_tax_rate;
                        $rate_key = count($tax_rates) - 1;
                    }
                    if (!isset($tax_amounts[$rate_key]))
                    {
                        $tax_amounts[$rate_key] = 0;
                    }
                    $tax_amounts[$rate_key] = float_add($tax_amounts[$rate_key], $standard_totals->total_shipping_tax);
                    $tax_amounts[$rate_key] = str_replace(",", ".", $tax_amounts[$rate_key] . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot
                }
            }

            //Collate nominal ledger summary
            $ledger_codes = array();
            $ledger_amounts = array();
            if (is_array($orders))
            {
                foreach ($orders as $order)
                {
                    $order_tax_rate = isset($order['tax_rate']) ? $order['tax_rate'] : $normal_tax_rate;
                    $code_key = array_search(@$order['nominal_ledger_code'], $ledger_codes);
                    if ($code_key === false)
                    {
                        $ledger_codes[] = @$order['nominal_ledger_code'];
                        $code_key = count($ledger_codes) - 1;
                    }
                    while (count($ledger_amounts) <= $code_key)
                    {
                        $ledger_amounts[] = '';
                    }
                    $ledger_amounts[$code_key] = float_add($ledger_amounts[$code_key], format_number(float_add(($order['net_price'] * $order['quantity']), float_add((($order['net_price'] * $order['quantity'] / 100) * $order_tax_rate), float_add(@$order['setup_fee'], @$order['setup_fee_tax_amount'])))));
                    $ledger_amounts[$code_key] = str_replace(",", ".", $ledger_amounts[$code_key] . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot
                }
            }
            //Ledger code for shipping, if applicable
            if ($standard_totals->total_shipping > 0)
            {
                if (is_array($orders))
                {
                    foreach ($orders as $order)
                    {
                        $shipping_ledger_code = -1;
                        if (@$order['shipping_id'] > 0)
                        {
                            $sql = "SELECT nominal_ledger_code FROM #__nbill_shipping WHERE id = " . intval($order['shipping_id']);
                            $nb_database->setQuery($sql);
                            $shipping_ledger_code = $nb_database->loadResult();
                            if (!$shipping_ledger_code)
                            {
                                $shipping_ledger_code = -1;
                            }
                        }
                        $code_key = array_search($shipping_ledger_code, $ledger_codes);
                        if ($code_key === false)
                        {
                            $ledger_codes[] = $shipping_ledger_code;
                            $code_key = count($ledger_codes) - 1;
                        }
                        $ledger_amounts[$code_key] = float_add(@$ledger_amounts[$code_key], float_add($standard_totals->total_shipping, $standard_totals->total_shipping_tax));
                        $ledger_amounts[$code_key] = str_replace(",", ".", $ledger_amounts[$code_key] . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot

                    }
                }
            }

            //Remove unnecessary values from $orders array (bits that have already been used and are not needed by gateway)
            if (is_array($orders))
            {
                foreach($orders as $order)
                {
                    unset($order['product_id']);
                    unset($order['shipping_units']);
                    unset($order['auto_fulfil']);
                    unset($order['tax_rate']);
                    unset($order['tax_amount']);
                    unset($order['nominal_ledger_code']);
                }
            }

            //Store details for retrieval by gateway later
            if (!nbf_gateway_txn::$document_ids)
            {
                nbf_gateway_txn::$document_ids = array();
            }
            for($invoice_index = 0; $invoice_index < count(nbf_gateway_txn::$document_ids); $invoice_index++)
            {
                nbf_gateway_txn::$document_ids[$invoice_index] = intval(nbf_gateway_txn::$document_ids[$invoice_index]);
            }
            if (is_numeric($standard_totals->total_net) && is_numeric($standard_totals->total_tax) && is_numeric($standard_totals->total_shipping) && is_numeric($standard_totals->total_shipping_tax))
            {
                //Try to find the entity ID if not passed in
                if (!$entity_id)
                {
                    if (is_array(nbf_gateway_txn::$created_orders))
                    {
                        $order_id_array = array();
                        foreach (nbf_gateway_txn::$created_orders as $created_order)
                        {
                            $order_id_array[] = (is_int($created_order) ? $created_order : intval($created_order->order_id));
                        }
                        if (count($order_id_array) > 0)
                        {
                            $sql = "SELECT client_id FROM #__nbill_orders WHERE id IN (" . implode(",", $order_id_array) . ") ORDER BY client_id DESC LIMIT 1";
                            $nb_database->setQuery($sql);
                            $entity_id = intval($nb_database->loadResult());
                        }
                    }
                    if (!$entity_id)
                    {
                        if (nbf_gateway_txn::$document_ids && is_array(nbf_gateway_txn::$document_ids) && count(nbf_gateway_txn::$document_ids) > 0)
                        {
                            for ($i = 0; $i < count(nbf_gateway_txn::$document_ids); $i++)
                            {
                                nbf_gateway_txn::$document_ids[$i] = intval(nbf_gateway_txn::$document_ids[$i]);
                            }
                            $sql = "SELECT entity_id FROM #__nbill_documents WHERE id IN (" . implode(",", nbf_gateway_txn::$document_ids) . ") ORDER BY entity_id DESC LIMIT 1";
                            $nb_database->setQuery($sql);
                            $entity_id = intval($nb_database->loadResult());
                        }
                    }
                    if (!$entity_id)
                    {
                        $entity_id = intval(nbf_common::get_param($_REQUEST, 'nbill_entity_id'));
                    }
                }
                $sql = "INSERT INTO #__nbill_gateway_tx (pending_order_id, document_ids, net_amount, tax_amount, shipping_amount,
                        shipping_tax_amount, entity_id, user_id, user_ip, vendor_id, form_id, turn_on_auto_renew, callback_file, callback_function, last_updated,
                        other_params, document_voucher_code) VALUES (
                        '$pending_order_id', '" . implode(",", $document_ids) . "', " . $standard_totals->total_net . ", " . $standard_totals->total_tax . ",
                        " . $standard_totals->total_shipping . ", " . $standard_totals->total_shipping_tax . ", " . intval($entity_id) . ", " . intval(nbf_cms::$interop->user->id) . ", '" . nbf_common::get_user_ip() . "', " . intval($vendor_id) . ",
                        " . intval($form_id) . ", " . intval($turn_on_auto_renew) . ", '" . $nb_database->getEscaped($callback_file) . "',
                        '" . $nb_database->getEscaped($callback_function) . "', " . nbf_common::nb_time() . ", '',
                        '" . nbf_common::get_param($_REQUEST, 'nbill_document_voucher_code') . "')";
                $nb_database->setQuery($sql);
                $nb_database->query();
                $g_tx_id = $nb_database->insertid();
            }
            else
            {
                die("Non-numeric input. SQL injection attempt assumed.");
            }

            //If any orders have already been created, update them with the g_tx_id
            if (is_array(nbf_gateway_txn::$created_orders))
            {
                foreach (nbf_gateway_txn::$created_orders as $created_order)
                {
                    $sql = "UPDATE #__nbill_orders SET gateway_txn_id = $g_tx_id WHERE id = " . (is_int($created_order) ? $created_order : intval($created_order->order_id));
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                }
            }

            //If not auto-renew, convert to a one-off payment
            if (!$auto_renew)
            {
                $payment_frequency = "AA";
            }

            //Populate $document_no, if possible
            if (nbf_common::nb_strlen($document_no) == 0)
            {
                $document_no = "";
                if (nbf_gateway_txn::$document_ids && is_array(nbf_gateway_txn::$document_ids) && count(nbf_gateway_txn::$document_ids) > 0)
                {
                    for ($i = 0; $i < count(nbf_gateway_txn::$document_ids); $i++)
                    {
                        nbf_gateway_txn::$document_ids[$i] = intval(nbf_gateway_txn::$document_ids[$i]);
                    }
                    $document_no = implode(", ", nbf_gateway_txn::$document_ids);
                    $sql = "SELECT document_no FROM #__nbill_document WHERE id IN (" . implode(",", nbf_gateway_txn::$document_ids) . ")";
                    $nb_database->setQuery($sql);
                    $document_nos = $nb_database->loadResultArray();
                    $document_no = implode(", ", $document_nos);
                }
            }
        }
        return $g_tx_id;
    }

    /**
    * Hands control over to the selected payment gateway to take a payment (if applicable)
    */
    public static function hand_over_to_gateway($g_tx_id, $payment_gateway, $suppress_payment, $standard_totals, $regular_totals,
                        $orders, $vendor_id, $auto_renew, $payment_frequency, $currency, &$abort,
                        $expiry_date = 0, $shipping_service = "", $relating_to = "", $no_of_payments = 1,
                        $billing_data = array(), $document_no = "", $turn_on_auto_renew = 0, $document_ids = array(),
                        $tax_rates = array(), $tax_amounts = array(), $form_id = 0)
    {
        if (!$suppress_payment)
        {
            if (nbf_common::nb_strlen($payment_gateway) > 0 && file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . $payment_gateway . "/" . $payment_gateway . ".php"))
            {
                //Hand over to the gateway extension
                $gateway_file_name = nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . $payment_gateway . "/" . $payment_gateway . ".php";
                $manifest_file_name = nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . $payment_gateway . "/" . $payment_gateway . ".manifest.php";
                if (file_exists($gateway_file_name))
                {
                    //Load manifest file to work out what features are available
                    $manifest = null;
                    if (file_exists($manifest_file_name))
                    {
                        include_once($manifest_file_name);
                        $manifest_class_name = "nbill_" . $payment_gateway . "_manifest";
                        if (class_exists($manifest_class_name))
                        {
                            $manifest = new $manifest_class_name();
                        }
                    }
                    //Populate variables required by legacy gateways
                    $total_net = $standard_totals->total_net;
                    $total_tax = $standard_totals->total_tax;
                    $total_carriage = $standard_totals->total_shipping;
                    $total_carriage_tax = $standard_totals->total_shipping_tax;
                    $total_gross = $standard_totals->total_gross;
                    $regular_total_gross = $regular_totals->total_gross;
                    if (!$manifest)
                    {
                        foreach ($billing_data as $key=>$value)
                        {
                            $_POST['INV_CORE_' . $key] = $value;
                        }
                        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.backward.compatibility.php");
                        global $database;
                    }
                    else
                    {
                        if ($payment_frequency != 'AA')
                        {
                            //Check whether recurring payments are supported
                            if (property_exists($manifest, 'recurring_payments') && !$manifest->recurring_payments)
                            {
                                nbf_globals::$message = NBILL_GATEWAY_NO_RECURRING . NBILL_GATEWAY_ERR_SUFFIX;
                                $abort = true;
                                return;
                            }
                            else
                            {
                                //Check whether this particular payment frequency is supported
                                if (property_exists($manifest, 'defined_frequencies'))
                                {
                                    $supported_frequencies = explode(",", $manifest->defined_frequencies);
                                    $supported_frequencies = array_map('trim', $supported_frequencies);
                                    if (array_search($payment_frequency, $supported_frequencies) === false)
                                    {
                                        nbf_globals::$message = NBILL_GATEWAY_DOES_NOT_SUPPORT_FREQUENCY . NBILL_GATEWAY_ERR_SUFFIX;
                                        $abort = true;
                                        return;
                                    }
                                }
                            }
                        }

                        if ($standard_totals->total_gross <= 0)
                        {
                            //Check whether first payment can be zero
                            if (property_exists($manifest, 'first_payment_zero') && !$manifest->first_payment_zero)
                            {
                                nbf_globals::$message = NBILL_GATEWAY_NO_FREE_TRIALS . NBILL_GATEWAY_ERR_SUFFIX;
                                $abort = true;
                                return;
                            }
                        }

                        if ($payment_frequency != 'AA' && $standard_totals->total_gross > 0 && $regular_totals->total_gross > 0 && !float_cmp($standard_totals->total_gross, $regular_totals->total_gross))
                        {
                            //Check whether first payment can be different amount
                            if (property_exists($manifest, 'first_payment_different') && !$manifest->first_payment_different)
                            {
                                nbf_globals::$message = NBILL_GATEWAY_NO_FIRST_PAYMENT_DIFFERENT . NBILL_GATEWAY_ERR_SUFFIX;
                                $abort = true;
                                return;
                            }
                        }

                        //Check whether a fixed number of payments is allowed
                        if ($no_of_payments > 1)
                        {
                            if (property_exists($manifest, 'fixed_no_of_payments') && !$manifest->fixed_no_of_payments)
                            {
                                nbf_globals::$message = NBILL_GATEWAY_NO_FIXED_INSTALLMENTS . NBILL_GATEWAY_ERR_SUFFIX;
                                $abort = true;
                                return;
                            }
                            //Check whether any particular number of payments are not supported (eg. Paypal standard will not allow 2 payments)
                            if (property_exists($manifest, 'minimum_no_of_payments') && intval($manifest->minimum_no_of_payments) > $no_of_payments)
                            {
                                nbf_globals::$message = sprintf(NBILL_GATEWAY_MIN_PAYMENTS, intval($manifest->minimum_no_of_payments)) . NBILL_GATEWAY_ERR_SUFFIX;
                                $abort = true;
                                return;
                            }
                        }
                    }
                    if (is_array($orders))
                    {
                        foreach ($orders as &$order)
                        {
                            $order['setup_fee'] = format_number(@$order['setup_fee']);
                            $order['setup_fee_tax_amount'] = format_number(@$order['setup_fee_tax_amount']);
                            $order['net_price'] = format_number($order['net_price']);
                            $order['tax_amount'] = format_number(@$order['tax_amount']);
                            if (isset($order['tax_rate']))
                            {
                                $order['tax_rate'] = format_number(@$order['tax_rate']);
                            }
                            $order['quantity'] = format_number(@$order['quantity']);
                            if (intval($order['quantity']) == $order['quantity'])
                            {
                                $order['quantity'] = intval($order['quantity']);
                            }
                        }
                    }
                    $invoice_no = $document_no; //Backward compat
                    nbf_common::fire_event("gateway_activated", array("gateway"=>$payment_gateway, "g_tx_id"=>$g_tx_id));
                    $billing_name = trim(nbf_common::get_param($billing_data, 'first_name') . ' ' . nbf_common::get_param($billing_data, 'last_name'));
                    include($gateway_file_name);
                }
                else
                {
                    nbf_globals::$message = NBILL_ERR_GATEWAY_NOT_FOUND;
                    $abort = true;
                }
            }
            else
            {
                if ($standard_totals->total_gross == 0 && $regular_totals->total_gross == 0)
                {
                    return $g_tx_id;
                }
                else
                {
                    nbf_globals::$message = NBILL_ERR_GATEWAY_NOT_FOUND;
                    $abort = true;
                }
            }
        }
        else if ($form_id)
        {
            //Offline payment selected - check whether to redirect to a payment instructions page
            $nb_database = nbf_cms::$interop->database;
            $sql = "SELECT offline_payment_redirect FROM #__nbill_order_form WHERE id = " . intval($form_id);
            $nb_database->setQuery($sql);
            $redirect = $nb_database->loadResult();
            if ($redirect)
            {
                //Replace placeholders with transaction data
                $url = str_replace("##TX_ID##", $g_tx_id, $redirect);
                $url = str_replace("##AMOUNT##", $standard_totals->total_gross, $url);
                $url = str_replace("##CURRENCY##", $currency, $url);
                //Execute any PHP code in the URL
                $url = nbf_common::parse_and_execute_code($url);
                nbf_common::redirect($url);
            }
        }
    }

    public static function load_gateway_tx($g_tx_id)
    {
        $nb_database = nbf_cms::$interop->database;

        $sql = "SELECT * FROM #__nbill_gateway_tx WHERE id = " . intval($g_tx_id);
        $nb_database->setQuery($sql);
        $nb_database->loadObject($custom_data);
        if ($custom_data)
        {
            nbf_gateway_txn::$pending_order_id = $custom_data->pending_order_id;
            if (nbf_common::nb_strlen(trim($custom_data->document_ids)) > 0)
            {
                nbf_gateway_txn::$document_ids = explode(",", $custom_data->document_ids);
            }
            nbf_gateway_txn::$net_amount = $custom_data->net_amount;
            nbf_gateway_txn::$tax_amount = $custom_data->tax_amount;
            nbf_gateway_txn::$shipping_amount = $custom_data->shipping_amount;
            nbf_gateway_txn::$shipping_tax_amount = $custom_data->shipping_tax_amount;
            nbf_gateway_txn::$user_ip = $custom_data->user_ip;
            nbf_gateway_txn::$vendor_id = $custom_data->vendor_id;
            nbf_gateway_txn::$discount_voucher_code = $custom_data->other_params;
            nbf_gateway_txn::$callback_file = $custom_data->callback_file;
            nbf_gateway_txn::$callback_function = $custom_data->callback_function;
            return true;
        }
        return false;
    }

    public static function gateway_processing($g_tx_id, $payment_amount, $payment_currency, &$warning_message, &$error_message, $customer = "", $reference = "", $notes = NBILL_AUTO_GENERATED_INCOME, $payment_method = 'GG', $allow_duplicate_txn_id = true)
    {
        //Initialise
        
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.process.discount.class.php");
        $nb_database = nbf_cms::$interop->database;
        $g_tx_id = intval($g_tx_id);
        if (!$g_tx_id)
        {
            $error_message = sprintf(NBILL_GATEWAY_ERR_ORDER_NOT_FOUND, $g_tx_id);
            return; //Cannot process a nonexistent transaction
        }

        if (!$allow_duplicate_txn_id && $g_tx_id && strlen($reference) > 0) {
            $sql = "SELECT id FROM #__nbill_transaction WHERE g_tx_id = " . intval($g_tx_id) . " AND reference = '" . $reference . "'";
            $nb_database->setQuery($sql);
            if ($nb_database->loadResult()) {
                $error_message = sprintf(NBILL_GATEWAY_ERR_DUPLICATE_NOTIFICATION_TXN, $reference, $g_tx_id);
                return;
            }
        }

        $this_order_ids = null;
        $this_document_ids = null;
        $transaction_id = 0;
        $error_message = "";

        //If possible, set payment method to the gateway id
        $gateway = nbf_common::get_param($_REQUEST, 'gateway');
        if ((nbf_common::nb_strlen($payment_method) == 0 || $payment_method == 'GG') && nbf_common::nb_strlen($gateway) > 0)
        {
            $sql = "SELECT published FROM #__nbill_payment_gateway_config WHERE gateway_id = '$gateway'";
            $nb_database->setQuery($sql);
            if ($nb_database->loadResult())
            {
                $payment_method = $gateway;
            }
        }

        if (nbf_gateway_txn::$document_ids && is_array(nbf_gateway_txn::$document_ids) && count(nbf_gateway_txn::$document_ids) > 0)
        {
            for ($i=0; $i<count(nbf_gateway_txn::$document_ids); $i++)
            {
                nbf_gateway_txn::$document_ids[$i] = intval(trim(nbf_gateway_txn::$document_ids[$i]));
            }
        }

        $tx_loaded = self::load_gateway_tx($g_tx_id);

        

        if (!$error_message)
        {
            //If this is an ad-hoc invoice payment, make sure it is not a duplicate (orders can be repeated legitimately, so we can only run this check on invoices)
            if (nbf_gateway_txn::$document_ids && is_array(nbf_gateway_txn::$document_ids) && count(nbf_gateway_txn::$document_ids) > 0)
            {
                $sql = "SELECT id FROM #__nbill_transaction WHERE g_tx_id = $g_tx_id AND reference = '$reference' AND amount = $payment_amount";
                $nb_database->setQuery($sql);
                if ($nb_database->loadResult())
                {
                    $error_message = NBILL_GATEWAY_ERR_DUPLICATE_NOTIFICATION;
                }
            }
        }

        if (!$error_message)
        {
            //Update the gateway transaction table to confirm success
            $sql = "UPDATE #__nbill_gateway_tx SET success_confirmed = 1, last_updated = " . nbf_common::nb_time() . " WHERE id = $g_tx_id";
            $nb_database->setQuery($sql);
            $nb_database->query();

            //Perform required processing after successful gateway payment
            if (nbf_common::nb_strlen($reference) == 0)
            {
                $reference = $g_tx_id;
            }
            $customer = trim($customer);

            if (nbf_gateway_txn::$document_ids && is_array(nbf_gateway_txn::$document_ids) && count(nbf_gateway_txn::$document_ids) > 0)
            {
                //Apply any document discounts
                $sql = "SELECT #__nbill_document.id FROM #__nbill_document
                LEFT JOIN #__nbill_orders_document ON #__nbill_document.id = #__nbill_orders_document.document_id
                LEFT JOIN #__nbill_orders ON #__nbill_orders_document.order_id = #__nbill_orders.id
                WHERE #__nbill_document.id IN (" . implode(",", nbf_gateway_txn::$document_ids) . ")
                AND (#__nbill_orders.unique_invoice = 0 OR #__nbill_orders.id IS NULL)
                ORDER BY #__nbill_orders.payment_frequency, #__nbill_document.document_date DESC";
                $nb_database->setQuery($sql);
                $existing_invoices = $nb_database->loadResultArray();

                if ($existing_invoices && count($existing_invoices) > 0)
                {
                    $gateway_txn = null;
                    $sql = "SELECT document_voucher_code, user_id FROM #__nbill_gateway_tx WHERE id = " . intval($g_tx_id);
                    $nb_database->setQuery($sql);
                    $nb_database->loadObject($gateway_txn);
                    $discount_ids = array();
                    $tax_rates = array();
                    $tax_amounts = array();
                    $first_invoice = null;
                    $sql = "SELECT #__nbill_document.id, #__nbill_document.id AS document_id, #__nbill_document.vendor_id, #__nbill_vendor.vendor_country,
                            #__nbill_document.entity_id, #__nbill_document.billing_country, #__nbill_document.tax_abbreviation, #__nbill_entity.tax_zone,
                            #__nbill_xref_eu_country_codes.code AS in_eu
                            FROM #__nbill_document
                            LEFT JOIN #__nbill_xref_eu_country_codes ON #__nbill_document.billing_country = #__nbill_xref_eu_country_codes.code
                            LEFT JOIN #__nbill_entity ON #__nbill_document.entity_id = #__nbill_entity.id
                            INNER JOIN #__nbill_vendor ON #__nbill_document.vendor_id = #__nbill_vendor.id
                            WHERE #__nbill_document.id = " . intval($existing_invoices[0]);
                    $nb_database->setQuery($sql);
                    $nb_database->loadObject($first_invoice);
                    $sql = "SELECT * FROM #__nbill_document_items WHERE document_id IN (" . implode(",", nbf_gateway_txn::$document_ids) . ") ORDER BY document_id, ordering, id";
                    $nb_database->setQuery($sql);
                    $all_doc_items = $nb_database->loadObjectList();
                    nbf_discount::apply_section_discounts($all_doc_items);
                    $orders = nbf_payment::prepare_document_order_summary($first_invoice, $all_doc_items, $tax_rates, $tax_amounts);
                    nbf_payment::add_discounts($payment_currency, $first_invoice->billing_country, $orders, $orders, $gateway_txn->document_voucher_code, false, 0, 0, 0, false, false, null, 0, 0, true, "", false, $first_invoice->vendor_id, $gateway_txn->user_id);

                    //If none of these invoices already have an auto discount addition, do it now...
                    $sql = "SELECT id FROM #__nbill_document_items
                            WHERE document_id IN (" . implode(",", $existing_invoices) . ")
                            AND product_code LIKE '[d=%' LIMIT 1";
                    $nb_database->setQuery($sql);
                    if (!$nb_database->loadResult())
                    {
                        foreach ($orders as $order)
                        {
                            if (intval(@$order['discount_id']))
                            {
                                $document_item = new stdClass();
                                $document_item->vendor_id = $first_invoice->vendor_id;
                                $document_item->entity_id = $first_invoice->entity_id;
                                $document_item->nominal_ledger_code = $order['nominal_ledger_code'];
                                $document_item->product_description = $order['product_name'];
                                $document_item->detailed_description = '';
                                $document_item->net_price_per_unit = $order['net_price'];
                                $document_item->no_of_units = 1;
                                $document_item->discount_amount = 0;
                                $document_item->discount_description = '';
                                $document_item->net_price_for_item = $order['net_price'];
                                $document_item->tax_rate_for_item = $order['tax_rate'];
                                $document_item->tax_for_item = $order['tax_amount'];
                                $document_item->shipping_id = 0;
                                $document_item->shipping_for_item = 0;
                                $document_item->tax_rate_for_shipping = 0;
                                $document_item->tax_for_shipping = 0;
                                $document_item->gross_price_for_item = float_add($order['net_price'], $order['tax_amount']);
                                $document_item->product_code = '[d=' . intval($order['discount_id']) . ']';
                                $document_item->section_name = '';
                                $document_item->section_discount_title = '';
                                $document_item->section_discount_percent = 0;
                                $document_item->electronic_delivery = $order['electronic_delivery'];
                                $error = '';
                                nbf_payment::add_item_to_document($existing_invoices[0], $document_item, $error);
                            }
                        }
                    }
                }
                foreach (nbf_gateway_txn::$document_ids as $invoice_id)
                {
                    nbf_payment::refresh_document_totals($invoice_id);
                }

                //Check whether invoice(s) written off
                $sql = "SELECT id FROM #__nbill_document WHERE id IN (" . implode(",", nbf_gateway_txn::$document_ids) . ") AND written_off = 1";
                $nb_database->setQuery($sql);
                $wo_invoice_ids = $nb_database->loadResultArray();

                //Invoice already created - check whether to auto create income
                $sql = "SELECT auto_create_income FROM #__nbill_vendor WHERE id = " . intval(nbf_gateway_txn::$vendor_id);
                $nb_database->setQuery($sql);
                $auto_create_income = $nb_database->loadResult();
                if ($auto_create_income && $payment_amount > 0)
                {
                    //If customer name not known, load it from the invoice
                    if (nbf_common::nb_strlen($customer) == 0)
                    {
                        $sql = "SELECT billing_name FROM #__nbill_document WHERE id IN (" . implode(",", nbf_gateway_txn::$document_ids) . ")";
                        $nb_database->setQuery($sql);
                        $customer = $nb_database->loadResult();
                    }

                    

                    $warning_message = self::record_income($customer, $payment_method, $payment_amount, $payment_currency, $reference, $notes, $transaction_no, $transaction_id, nbf_common::nb_time(), nbf_gateway_txn::$vendor_id, $g_tx_id, $gateway_fee_document_items);
                    if (nbf_common::nb_strlen($warning_message) > 0)
                    {
                        $warning_message = sprintf(NBILL_ERR_RECEIPT_NOT_PROCESSED, $payment_currency . $payment_amount) . "\n\n" . $warning_message;
                    }
                }

                //If invoice(s) written off, warn administrator (also check whether there is an associated order that is cancelled)
                if ($wo_invoice_ids && is_array($wo_invoice_ids))
                {
                    if ($warning_message)
                    {
                        $warning_message .= "\n\n";
                    }
                    $warning_message .= NBILL_WARNING_WO_INVOICE;
                    $sql = "SELECT #__nbill_orders.id FROM #__nbill_orders
                            INNER JOIN #__nbill_orders_document ON #__nbill_orders.id = #__nbill_orders_document.order_id
                            INNER JOIN #__nbill_document ON #__nbill_orders_document.document_id = #__nbill_document.id
                            WHERE #__nbill_document.id IN (" . implode(",", $wo_invoice_ids) . ")
                            AND #__nbill_orders.order_status = 'EE'";
                    $nb_database->setQuery($sql);
                    if ($nb_database->loadResult())
                    {
                        $warning_message .= "\n\n" . NBILL_WARNING_WO_INVOICE_CANCELLED_ORDER;
                    }
                }
            }
            else
            {
                $order = null;
                
            }

            $this_order_ids = nbf_gateway_txn::$created_orders;
            $this_document_ids = nbf_gateway_txn::$document_ids;
            if (is_array($this_order_ids))
            {
                $this_order_ids = implode(",", $this_order_ids);
            }
            if (is_array($this_document_ids))
            {
                $this_document_ids = implode(",", $this_document_ids);
            }

            if (nbf_common::nb_strlen(nbf_gateway_txn::$callback_file) > 0 && nbf_common::nb_strlen(nbf_gateway_txn::$callback_function) > 0)
            {
                //Call the custom callback function
                if (file_exists(nbf_gateway_txn::$callback_file))
                {
                    @include(nbf_gateway_txn::$callback_file);
                    if (nbf_common::nb_strpos(nbf_gateway_txn::$callback_function, "::") !== false)
                    {
                        nbf_gateway_txn::$callback_function = explode("::", nbf_gateway_txn::$callback_function);
                    }
                    if (is_callable(nbf_gateway_txn::$callback_function))
                    {
                        $error_message = @call_user_func(nbf_gateway_txn::$callback_function, $g_tx_id, $payment_amount, $payment_currency, $payment_method, $customer, $reference, $notes, $warning_message);
                    }
                }
            }

            nbf_common::fire_event("payment_received", array("g_tx_id"=>$g_tx_id, "amount"=>$payment_amount, "currency"=>$payment_currency, "order_ids"=>$this_order_ids, "document_ids"=>$this_document_ids, "transaction_id"=>$transaction_id, "vendor_id"=>nbf_gateway_txn::$vendor_id, "reference"=>$reference));
        }
    }

    public static function finish_gateway_processing($warning_message, $error_message, $add_debug_info = false, $redirect_url = "", $g_tx_id = 0, $thanks = "")
    {
        $nb_database = nbf_cms::$interop->database;

        $warning_email_addendum = "";
        $error_email_addendum = "";
        if ($g_tx_id && (nbf_common::nb_strlen($warning_message > 0) || nbf_common::nb_strlen($error_message) > 0))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.transaction.class.php");
            $txn_details = nbf_tx::generate_txn_text_output(nbf_tx::load_transaction_details($g_tx_id, false, true), false);
            if (nbf_common::nb_strlen($warning_message) > 0 && nbf_common::nb_strlen($txn_details) > 0)
            {
                $warning_email_addendum = "\n\n$txn_details";
            }
            if (nbf_common::nb_strlen($error_message) > 0 && nbf_common::nb_strlen($txn_details) > 0)
            {
                $error_email_addendum = "\n\n$txn_details";
            }
        }

        if (isset(nbf_gateway_txn::$vendor_id) && nbf_common::nb_strlen(nbf_gateway_txn::$vendor_id) > 0)
        {
            $sql = "SELECT admin_email FROM #__nbill_vendor WHERE id = " . intval(nbf_gateway_txn::$vendor_id);
        }
        else
        {
            $sql = "SELECT error_email FROM #__nbill_configuration";
        }
        $nb_database->setQuery($sql);
        $receiver_email = $nb_database->loadResult();

        if (nbf_common::nb_strlen($receiver_email) == 0)
        {
            //Just get the default or first vendor's email
            $sql = "SELECT admin_email FROM #__nbill_vendor WHERE admin_email != '' ORDER BY default_vendor DESC LIMIT 1";
            $nb_database->setQuery($sql);
            $receiver_email = $nb_database->loadResult();
        }

        if (nbf_common::nb_strlen($warning_message) > 0)
        {
            //Tell administrator
            nbf_cms::$interop->send_email($receiver_email, $receiver_email, $receiver_email, NBILL_GATEWAY_WARNING, $warning_message . $warning_email_addendum);
        }

        if (nbf_common::nb_strlen($error_message) > 0)
        {
            //Append information about the callback
            $callback_info = "";
            if ($add_debug_info)
            {
                $callback_info = NBILL_GATEWAY_CALLBACK_INFO;
                $callback_info .= "REQUEST:\n" . print_r($_POST, true);
                $callback_info .= "SERVER:\n" . print_r($_SERVER, true);
            }

            //Tell administrator
            nbf_cms::$interop->send_email($receiver_email, $receiver_email, $receiver_email, NBILL_GATEWAY_ERROR, $error_message . $error_email_addendum . "\n\n" . $callback_info);

            //Echo a response to the user
            if (nbf_common::nb_strlen($redirect_url) > 0)
            {
                nbf_common::redirect($redirect_url);
            }
            else
            {
                //Show error message
                echo sprintf(NBILL_GATEWAY_ERR, $error_message);
            }
        }
        else
        {
            //Load transaction data
            if ($g_tx_id)
            {
                $amount = 0;
                $currency = '';
                $order_id = '';
                $order_no = '';
                self::load_order_conf_tx_data($g_tx_id, $amount, $currency, $order_id, $order_no);
            }

            if (nbf_common::nb_strlen($redirect_url) > 0 && $g_tx_id)
            {
                //Replace placeholders with transaction data
                $redirect_url = str_replace("##TX_ID##", $g_tx_id, $redirect_url);
                $redirect_url = str_replace("##AMOUNT##", $amount, $redirect_url);
                $redirect_url = str_replace("##CURRENCY##", $currency, $redirect_url);
                $redirect_url = str_replace("##ORDER_ID##", $order_id, $redirect_url);
                $redirect_url = str_replace("##ORDER_NO##", $order_no, $redirect_url);
            }

            //Try to find message from order form, or show generic message
            if (!$thanks)
            {
                if ($g_tx_id)
                {
                    $form_thanks = null;
                    $sql = "SELECT #__nbill_order_form.id, thank_you_redirect FROM #__nbill_order_form INNER JOIN #__nbill_gateway_tx ON #__nbill_order_form.id = #__nbill_gateway_tx.form_id WHERE #__nbill_gateway_tx.id = " . intval($g_tx_id);
                    $nb_database->setQuery($sql);
                    $nb_database->loadObject($form_thanks);
                    if ($form_thanks)
                    {
                        $thanks_id = $form_thanks->id;
                        if (intval($thanks_id))
                        {
                            //If there is a form redirect, use it
                            $form_redirect = $form_thanks->thank_you_redirect;
                            if (!$form_redirect)
                            {
                                //Use gateway default redirect if supplied
                                if (nbf_common::nb_strlen($redirect_url) > 0)
                                {
                                    $form_redirect = $redirect_url;
                                }
                                else
                                {
                                    //Otherwise, show thank you message
                                    $form_redirect = nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . "&action=orders&task=complete&id=" . $thanks_id . "&message=" . urlencode($warning_message) . "&g_tx_id=" . $g_tx_id . "&amount=" . $amount . "&currency=" . $currency . "&order_id=" . $order_id . "&order_no=" . $order_no . nbf_cms::$interop->site_page_suffix;
                                }
                            } else {
                                //Replace placeholders with transaction data
                                $form_redirect = str_replace("##TX_ID##", $g_tx_id, $form_redirect);
                                $form_redirect = str_replace("##AMOUNT##", $amount, $form_redirect);
                                $form_redirect = str_replace("##CURRENCY##", $currency, $form_redirect);
                                $form_redirect = str_replace("##ORDER_ID##", $order_id, $form_redirect);
                                $form_redirect = str_replace("##ORDER_NO##", $order_no, $form_redirect);
                                //Execute any PHP code in the URL
                                $form_redirect = nbf_common::parse_and_execute_code($form_redirect);
                            }
                            $loopbreaker = 0;
                            while (ob_get_length() !== false)
                            {
                                $loopbreaker++;
                                @ob_end_clean();
                                if ($loopbreaker > 15)
                                {
                                    break;
                                }
                            }
                            echo "\n"; //Force headers to be sent, and redirect with javascript so that PSP gets a 200 response code (some PSPs fall over with a 302)
                            @ob_end_flush();
                            nbf_common::redirect($form_redirect);
                            exit;
                        }
                    }
                    else if (nbf_common::nb_strlen($redirect_url) > 0)
                    {
                        @ob_end_clean();
                        echo "\n"; //Force headers to be sent, and redirect with javascript so that PSP gets a 200 response code (some PSPs fall over with a 302)
                        @ob_end_flush();
                        nbf_common::redirect($redirect_url);
                    }

                    //If success has not been confirmed, indicate that it is pending confirmation in the fallback thank you message
                    $sql = "SELECT success_confirmed FROM #__nbill_gateway_tx WHERE id = " . intval($g_tx_id);
                    $nb_database->setQuery($sql);
                    if (!$nb_database->loadResult())
                    {
                        $thanks = NBILL_GATEWAY_SUCCESS_PENDING;
                    }
                }
                $thanks = $thanks ? $thanks : NBILL_GATEWAY_SUCCESS;
            }
            if ($g_tx_id)
            {
                //Replace placeholders with transaction data
                $thanks = str_replace("##TX_ID##", $g_tx_id, $thanks);
                $thanks = str_replace("##AMOUNT##", $amount, $thanks);
                $thanks = str_replace("##CURRENCY##", $currency, $thanks);
                $thanks = str_replace("##ORDER_ID##", $order_id, $thanks);
                $thanks = str_replace("##ORDER_NO##", $order_no, $thanks);
                //Execute any PHP code in the URL
                $thanks = nbf_common::parse_and_execute_code($thanks);
            }
            echo $thanks;
        }
    }    /**
    * Load the transaction data needed to replace placeholders on order confirmation messages and redirects
    * @param mixed $g_tx_id This is the only INPUT parameter - all others are outputs
    * @param string $amount
    * @param mixed $currency
    * @param string $order_id
    * @param string $order_no
    */
    public static function load_order_conf_tx_data(&$g_tx_id, &$amount, &$currency, &$order_id, &$order_no)
    {
        $nb_database = nbf_cms::$interop->database;

        $sql = "SELECT net_amount, tax_amount, shipping_amount, shipping_tax_amount, pending_order_id, document_ids FROM #__nbill_gateway_tx WHERE id = " . intval($g_tx_id);
        $nb_database->setQuery($sql);
        $tx_data = null;
        $nb_database->loadObject($tx_data);
        if (!$tx_data)
        {
            $g_tx_id = 0;
        }
        else
        {
            $amount = float_add($tx_data->net_amount, float_add($tx_data->tax_amount, float_add($tx_data->shipping_amount, $tx_data->shipping_tax_amount)));
            if ($tx_data->document_ids)
            {
                $sql = "SELECT currency FROM #__nbill_document WHERE id IN (" . $tx_data->document_ids . ") LIMIT 1";
            }
            else
            {
                $sql = "SELECT currency FROM #__nbill_pending_orders WHERE id = " . intval($tx_data->pending_order_id);
            }
            $nb_database->setQuery($sql);
            $currency = $nb_database->loadResult();
            $order_id = '';
            $order_no = '';
            $sql = "SELECT id, order_no, currency FROM #__nbill_orders WHERE gateway_txn_id = " . intval($g_tx_id) . " ORDER BY id";
            $nb_database->setQuery($sql);
            $order_data = $nb_database->loadObjectList();
            if ($order_data && count($order_data) > 0)
            {
                if (!$currency)
                {
                    $currency = $order_data[0]->currency;
                }
                $order_ids = array();
                $order_nos = array();
                foreach ($order_data as $order_item)
                {
                    $order_ids[] = $order_item->id;
                    $order_nos[] = $order_item->order_no;
                }
                $order_id = implode(",", $order_ids);
                $order_no = implode(",", $order_nos);
            }
        }
    }

    

    /**
    * Set order record to expire on given month (useful where card expiry means user would have to renew)
    */
    public static function set_order_expiry($month, $year)
    {
        //Expire on the day after the last payment will be taken
        $nb_database = nbf_cms::$interop->database;
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");

        $month_end = 31;
        switch ($month)
        {
            case 2:
                $month_end = 28; //Ignore leap years - 1 day won't matter!
                break;
            case 4:
            case 6;
            case 9:
            case 11:
                $month_end = 30;
                break;
        }
        $month_end = nbf_common::nb_mktime(23, 59, 59, $month, $month_end, $year);

        if (nbf_gateway_txn::$created_orders && count(nbf_gateway_txn::$created_orders) > 0)
        {
            foreach (nbf_gateway_txn::$created_orders as $created_order)
            {
                $results = null;
                $sql = "SELECT payment_frequency, start_date, next_due_date FROM #__nbill_orders WHERE id = " . intval($created_order);
                $nb_database->setQuery($sql);
                $nb_database->loadObject($results);
                if ($results)
                {
                    $next_due_date = $results->next_due_date;
                    $expiry_date = $results->start_date;
                    $loop_counter = 0;
                    while ($next_due_date < $month_end)
                    {
                        $expiry_date = $next_due_date;
                        $loop_counter++;
                        if ($loop_counter > 500)
                        {
                            break;
                        }
                        $next_due_date = nbf_date::get_next_payment_date($results->start_date, $next_due_date, $results->payment_frequency);
                    }
                    if ($loop_counter < 501 && $expiry_date > nbf_common::nb_time())
                    {
                        $sql = "UPDATE #__nbill_orders SET expiry_date = " . intval($expiry_date) . " WHERE id = " . intval($created_order);
                        $nb_database->setQuery($sql);
                        $nb_database->query();
                    }
                }
            }
        }
    }

    /**
    * This is for backward compatability only
    */
    public static function load_invoice_breakdowns($this_tx_id, $document_ids, &$tax_rates, &$tax_amounts, &$ledger_codes, &$ledger_nets,
                            &$ledger_tax_rates, &$ledger_taxes, &$ledger_grosses, &$total_gross, $omit_gateway_fees = false, $deduct_partial_payments = true)
    {
        $tax_rates_electronic = array();
        $tax_amounts_electronic = array();
        return self::load_invoice_breakdowns_electronic($this_tx_id, $document_ids, $tax_rates, $tax_amounts, $tax_rates_electronic, $tax_amounts_electronic, $ledger_codes, $ledger_nets,
                            $ledger_tax_rates, $ledger_taxes, $ledger_grosses, $total_gross, $omit_gateway_fees, $deduct_partial_payments);
    }

    public static function load_invoice_breakdowns_electronic($this_tx_id, $document_ids, &$tax_rates, &$tax_amounts, &$tax_rates_electronic, &$tax_amounts_electronic, &$ledger_codes, &$ledger_nets,
                            &$ledger_tax_rates, &$ledger_taxes, &$ledger_grosses, &$total_gross, $omit_gateway_fees = false, $deduct_partial_payments = true)
    {
        $nb_database = nbf_cms::$interop->database;

        //Load invoice items
        $sql = "SELECT #__nbill_document_items.document_id, #__nbill_document_items.section_name,
                    #__nbill_document_items.section_discount_percent,
                    #__nbill_document_items.nominal_ledger_code, #__nbill_document_items.net_price_for_item,
                    #__nbill_document_items.no_of_units,
                    #__nbill_document_items.tax_rate_for_item, #__nbill_document_items.tax_for_item,
                    #__nbill_document_items.shipping_for_item, #__nbill_document_items.tax_rate_for_shipping,
                    #__nbill_document_items.tax_for_shipping, #__nbill_document_items.gross_price_for_item,
                    #__nbill_document_items.shipping_id, #__nbill_document_items.product_code,
                    #__nbill_document_items.electronic_delivery,
                    #__nbill_shipping.nominal_ledger_code AS shipping_ledger_code,
                    #__nbill_document.currency
                    FROM #__nbill_document_items
                    INNER JOIN #__nbill_document ON #__nbill_document_items.document_id = #__nbill_document.id
                    LEFT JOIN #__nbill_shipping ON #__nbill_document_items.shipping_id = #__nbill_shipping.id
                    WHERE #__nbill_document_items.document_id IN ($document_ids)
                    GROUP BY #__nbill_document_items.id, #__nbill_document_items.document_id
                    ORDER BY #__nbill_document_items.document_id, #__nbill_document_items.ordering";
        $nb_database->setQuery($sql);
        $document_items = $nb_database->loadObjectList();
        if (!$document_items)
        {
            $document_items = array();
        }

        //Apply section discounts
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.process.discount.class.php");
        nbf_discount::apply_section_discounts($document_items);

        $tax_rates = array();
        $tax_amounts = array();
        $tax_rates_electronic = array();
        $tax_amounts_electronic = array();
        $ledger_codes = array();
        $ledger_nets = array();
        $ledger_tax_rates = array();
        $ledger_taxes = array();
        $ledger_grosses = array();
        $tax_index = 0;
        $ledger_index = 0;
        $invoice_gross = 0;
        foreach ($document_items as $document_item)
        {
            if (!($omit_gateway_fees && substr($document_item->product_code, 0, 3) == "[g="))
            {
                $invoice_gross = float_add($invoice_gross, $document_item->gross_price_for_item);
                if ($document_item->tax_rate_for_item != 0 || $document_item->tax_for_item != 0)
                {
                    if ($document_item->electronic_delivery) {
                        $this_index = array_search($document_item->tax_rate_for_item, $tax_rates_electronic);
                    } else {
                        $this_index = array_search($document_item->tax_rate_for_item, $tax_rates);
                    }
                    if ($this_index === false)
                    {
                        $this_index = $tax_index;
                        $tax_index++;
                        if ($document_item->electronic_delivery) {
                            $tax_amounts_electronic[$this_index] = 0;
                        } else {
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
                if ($document_item->tax_for_shipping != 0)
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
                            $this_index = $tax_index;
                            $tax_index++;
                            if ($document_item->electronic_delivery) {
                                $tax_amounts_electronic[$this_index] = 0;
                            } else {
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

                $this_index = false;
                for ($ledger_index = 0; $ledger_index < count($ledger_codes); $ledger_index++)
                {
                    if ($ledger_codes[$ledger_index] == ($document_item->nominal_ledger_code ? $document_item->nominal_ledger_code : '-1') && float_cmp($ledger_tax_rates[$ledger_index], $document_item->tax_rate_for_item))
                    {
                        $this_index = $ledger_index;
                        break;
                    }
                }
                if ($document_item->net_price_for_item != 0 || $document_item->tax_for_item !=0)
                {
                    if ($this_index === false)
                    {
                        $this_index = $ledger_index;
                        $ledger_index++;
                        $ledger_nets[$this_index] = 0;
                        $ledger_taxes[$this_index] = 0;
                        $ledger_grosses[$this_index] = 0;
                    }
                    $ledger_codes[$this_index] = $document_item->nominal_ledger_code ? $document_item->nominal_ledger_code : '-1';
                    $ledger_nets[$this_index] = float_add($ledger_nets[$this_index], $document_item->net_price_for_item);
                    $ledger_tax_rates[$this_index] = $document_item->tax_rate_for_item;
                    $ledger_taxes[$this_index] = float_add($ledger_taxes[$this_index], $document_item->tax_for_item);
                    $ledger_grosses[$this_index] = float_add($ledger_grosses[$this_index], float_add($document_item->net_price_for_item, $document_item->tax_for_item));
                }
                if ($document_item->shipping_for_item != 0 || $document_item->tax_for_shipping != 0)
                {
                    $this_index = false;
                    for ($ledger_index = 0; $ledger_index < count($ledger_codes); $ledger_index++)
                    {
                        if ($ledger_codes[$ledger_index] == $document_item->shipping_ledger_code && float_cmp($ledger_tax_rates[$ledger_index], $document_item->tax_rate_for_shipping))
                        {
                            $this_index = $ledger_index;
                            break;
                        }
                    }
                    if ($this_index === false)
                    {
                        $this_index = $ledger_index;
                        $ledger_index++;
                        $ledger_nets[$this_index] = 0;
                        $ledger_taxes[$this_index] = 0;
                        $ledger_grosses[$this_index] = 0;
                    }
                    $ledger_codes[$this_index] = $document_item->shipping_ledger_code ? $document_item->shipping_ledger_code : '-1';
                    $ledger_nets[$this_index] = float_add($ledger_nets[$this_index], $document_item->shipping_for_item);
                    $ledger_tax_rates[$this_index] = $document_item->tax_rate_for_shipping;
                    $ledger_taxes[$this_index] = float_add($ledger_taxes[$this_index], $document_item->tax_for_shipping);
                    $ledger_grosses[$this_index] = float_add($ledger_grosses[$this_index], float_add($document_item->shipping_for_item, $document_item->tax_for_shipping));
                }
            }
        }

        /*//Assign missing codes to miscellaneous
        for($i = 0; $i < count($ledger_codes); $i++)
        {
            if (!$ledger_codes[$i])
            {
                $ledger_codes[$i] = '-1';
            }
        }*/

        if ($deduct_partial_payments)
        {
            //Subtract any partial payments already made
            $sql = "SELECT #__nbill_document_transaction.*,
                    #__nbill_transaction.tax_rate_1_electronic_delivery,
                    #__nbill_transaction.tax_rate_2_electronic_delivery,
                    #__nbill_transaction.tax_rate_3_electronic_delivery
                    FROM #__nbill_document_transaction
                    INNER JOIN #__nbill_transaction ON #__nbill_document_transaction.transaction_id = #__nbill_transaction.id
                    WHERE transaction_id != $this_tx_id AND document_id IN ($document_ids)";
            $nb_database->setQuery($sql);
            $partial_payments = $nb_database->loadObjectList();
            if ($partial_payments && count($partial_payments) > 0)
            {
                $tx_ids = array();
                foreach ($partial_payments as $partial_payment)
                {
                    $invoice_gross = float_subtract($invoice_gross, $partial_payment->gross_amount);
                    $tx_ids[] = $partial_payment->transaction_id;
                    if ($partial_payment->tax_rate_1_electronic_delivery) {
                        $this_index = array_search($partial_payment->tax_rate_1, $tax_rates_electronic);
                    } else {
                        $this_index = array_search($partial_payment->tax_rate_1, $tax_rates);
                    }
                    if ($this_index !== false)
                    {
                        if ($partial_payment->tax_rate_1_electronic_delivery) {
                            $tax_amounts_electronic[$this_index] = float_subtract($tax_amounts_electronic[$this_index], $partial_payment->tax_amount_1);
                        } else {
                            $tax_amounts[$this_index] = float_subtract($tax_amounts[$this_index], $partial_payment->tax_amount_1);
                        }
                    }
                    else if ($partial_payment->tax_amount_1 != 0)
                    {
                        if ($partial_payment->tax_rate_1_electronic_delivery) {
                            $tax_rates_electronic[] = $partial_payment->tax_rate_1;
                            $tax_amounts_electronic[] = 0 - $partial_payment->tax_amount_1;
                        } else {
                            $tax_rates[] = $partial_payment->tax_rate_1;
                            $tax_amounts[] = 0 - $partial_payment->tax_amount_1;
                        }
                    }
                    if ($partial_payment->tax_rate_2_electronic_delivery) {
                        $this_index = array_search($partial_payment->tax_rate_2, $tax_rates_electronic);
                    } else {
                        $this_index = array_search($partial_payment->tax_rate_2, $tax_rates);
                    }
                    if ($this_index !== false)
                    {
                        if ($partial_payment->tax_rate_2_electronic_delivery) {
                            $tax_amounts_electronic[$this_index] = float_subtract($tax_amounts_electronic[$this_index], $partial_payment->tax_amount_2);
                        } else {
                            $tax_amounts[$this_index] = float_subtract($tax_amounts[$this_index], $partial_payment->tax_amount_2);
                        }
                    }
                    else if ($partial_payment->tax_amount_2 != 0)
                    {
                        if ($partial_payment->tax_rate_2_electronic_delivery) {
                            $tax_rates_electronic[] = $partial_payment->tax_rate_2;
                            $tax_amounts_electronic[] = 0 - $partial_payment->tax_amount_2;
                        } else {
                            $tax_rates[] = $partial_payment->tax_rate_2;
                            $tax_amounts[] = 0 - $partial_payment->tax_amount_2;
                        }
                    }
                    if ($partial_payment->tax_rate_3_electronic_delivery) {
                        $this_index = array_search($partial_payment->tax_rate_3, $tax_rates_electronic);
                    } else {
                        $this_index = array_search($partial_payment->tax_rate_3, $tax_rates);
                    }
                    if ($this_index !== false)
                    {
                        if ($partial_payment->tax_rate_3_electronic_delivery) {
                            $tax_amounts_electronic[$this_index] = float_subtract($tax_amounts_electronic[$this_index], $partial_payment->tax_amount_3);
                        } else {
                            $tax_amounts[$this_index] = float_subtract($tax_amounts[$this_index], $partial_payment->tax_amount_3);
                        }
                    }
                    else if ($partial_payment->tax_amount_3 != 0)
                    {
                        if ($partial_payment->tax_rate_3_electronic_delivery) {
                            $tax_rates_electronic[] = $partial_payment->tax_rate_3;
                            $tax_amounts_electronic[] = 0 - $partial_payment->tax_amount_3;
                        } else {
                            $tax_rates[] = $partial_payment->tax_rate_3;
                            $tax_amounts[] = 0 - $partial_payment->tax_amount_3;
                        }
                    }
                }
                $total_gross = float_gtr_e($invoice_gross, $total_gross) ? $total_gross : $invoice_gross;
                $all_doc_ids = explode(",", $document_ids);
                $sql = "SELECT document_id FROM #__nbill_document_transaction WHERE transaction_id IN (" . implode(",", $tx_ids) . ")";
                $nb_database->setQuery($sql);
                $all_doc_ids = array_unique(array_merge($all_doc_ids, $nb_database->loadResultArray()));

                //Load totals for each ledger code on all documents (must load document items and apply section discounts first!)
                $sql = "SELECT #__nbill_document_items.*, #__nbill_shipping.nominal_ledger_code AS shipping_ledger_code
                        FROM #__nbill_document_items
                        LEFT JOIN #__nbill_shipping ON #__nbill_document_items.shipping_id = #__nbill_shipping.id
                        WHERE document_id IN (" . implode(",", $all_doc_ids) . ") ORDER BY document_id, ordering, id";
                $nb_database->setQuery($sql);
                $all_doc_items = $nb_database->loadObjectList();
                nbf_discount::apply_section_discounts($all_doc_items);

                $all_ledgers = array();
                foreach ($all_doc_items as $all_doc_item)
                {
                    $this_item_gross = float_add($all_doc_item->net_price_for_item, $all_doc_item->tax_for_item);
                    $this_shipping_gross = float_add($all_doc_item->shipping_for_item, $all_doc_item->tax_for_shipping);
                    if ($this_item_gross != 0)
                    {
                        $obj_ledger = new stdClass();
                        $obj_ledger->nominal_ledger_code = $all_doc_item->nominal_ledger_code;
                        $obj_ledger->total_net_amount = $all_doc_item->net_price_for_item;
                        $obj_ledger->total_tax_amount = $all_doc_item->tax_for_item;
                        $obj_ledger->total_gross_amount = float_add($all_doc_item->net_price_for_item, $all_doc_item->tax_for_item);
                        if (!array_key_exists($all_doc_item->nominal_ledger_code, $all_ledgers))
                        {
                            $all_ledgers[$all_doc_item->nominal_ledger_code] = $obj_ledger;
                        }
                        else
                        {
                            $all_ledgers[$all_doc_item->nominal_ledger_code]->total_net_amount = float_add($all_ledgers[$all_doc_item->nominal_ledger_code]->total_net_amount, $all_doc_item->net_price_for_item);
                            $all_ledgers[$all_doc_item->nominal_ledger_code]->total_tax_amount = float_add($all_ledgers[$all_doc_item->nominal_ledger_code]->total_tax_amount, $all_doc_item->tax_for_item);
                            $all_ledgers[$all_doc_item->nominal_ledger_code]->total_gross_amount = float_add($all_ledgers[$all_doc_item->nominal_ledger_code]->total_gross_amount, float_add($all_doc_item->net_price_for_item, $all_doc_item->tax_for_item));
                        }
                    }
                    if ($this_shipping_gross != 0)
                    {
                        $shipping_ledger = $all_doc_item->shipping_ledger_code === null ? $all_doc_item->nominal_ledger_code : '-1';
                        $obj_ledger = new stdClass();
                        $obj_ledger->nominal_ledger_code = $shipping_ledger;
                        $obj_ledger->total_net_amount = $all_doc_item->shipping_for_item;
                        $obj_ledger->total_tax_amount = $all_doc_item->tax_for_shipping;
                        $obj_ledger->total_gross_amount = float_add($all_doc_item->shipping_for_item, $all_doc_item->tax_for_shipping);

                        if (!array_key_exists($shipping_ledger, $all_ledgers))
                        {
                            $all_ledgers[$shipping_ledger] = $obj_ledger;
                        }
                        else
                        {
                            $all_ledgers[$all_doc_item->nominal_ledger_code]->total_net_amount = float_add($all_ledgers[$all_doc_item->nominal_ledger_code]->total_net_amount, $all_doc_item->shipping_for_item);
                            $all_ledgers[$all_doc_item->nominal_ledger_code]->total_tax_amount = float_add($all_ledgers[$all_doc_item->nominal_ledger_code]->total_tax_amount, $all_doc_item->tax_for_shipping);
                            $all_ledgers[$all_doc_item->nominal_ledger_code]->total_gross_amount = float_add($all_ledgers[$all_doc_item->nominal_ledger_code]->total_gross_amount, float_add($all_doc_item->shipping_for_item, $all_doc_item->tax_for_shipping));
                        }
                    }
                }
                $all_ledgers = array_values($all_ledgers);                foreach ($all_ledgers as $partial_ledger)
                {
///RSW TODO: When we are storing ledger assignment for partial payments against the document item, we will need to adjust this to deduct only the amount assigned for that ledger/document_item (currently we assume the whole amount was paid)
                    //If this item includes an amount relating to an invoice we are not currently dealing with, remove that amount
                    $this_doc_ids = explode(",", $document_ids);
                    foreach ($all_doc_items as $all_doc_item) {
                        if (array_search($all_doc_item->id, $this_doc_ids) === false && $all_doc_item->nominal_ledger_code == $partial_ledger->nominal_ledger_code) {
                            $partial_ledger->total_net_amount = float_subtract($partial_ledger->total_net_amount, $all_doc_item->net_price_for_item);
                            $partial_ledger->total_tax_amount = float_subtract($partial_ledger->total_tax_amount, $all_doc_item->tax_for_item);
                            $partial_ledger->total_gross_amount = float_subtract($partial_ledger->total_gross_amount, $all_doc_item->gross_price_for_item);
                        }
                    }

                    $this_index = false;
                    for ($ledger_index = 0; $ledger_index < count($ledger_codes); $ledger_index++) {
                        if ($ledger_codes[$ledger_index] == $partial_ledger->nominal_ledger_code) {
                            $this_index = $ledger_index;
                            break;
                        }
                    }
                    if ($this_index !== false) {
                        $ledger_nets[$ledger_index] = strval(float_subtract($ledger_nets[$ledger_index], $partial_ledger->total_net_amount));
                        $ledger_taxes[$ledger_index] = strval(float_subtract($ledger_taxes[$ledger_index], $partial_ledger->total_tax_amount));
                        $ledger_grosses[$ledger_index] = strval(float_subtract($ledger_grosses[$ledger_index], $partial_ledger->total_gross_amount));
                    }
                }
            }
        }

        //Don't filter out zero amounts completely
        for($index = 0; $index < count($ledger_codes); $index++) {
            $ledger_tax_rates[$index] = $ledger_tax_rates[$index] == 0 ? format_number($ledger_tax_rates[$index], 6) : $ledger_tax_rates[$index];
            $ledger_nets[$index] = $ledger_nets[$index] == 0 ? format_number($ledger_nets[$index], 6) : $ledger_nets[$index];
            $ledger_taxes[$index] = $ledger_taxes[$index] == 0 ? format_number($ledger_taxes[$index], 6) : $ledger_taxes[$index];
            $ledger_grosses[$index] = $ledger_grosses[$index] == 0 ? format_number($ledger_grosses[$index], 6) : $ledger_grosses[$index];
        }

        //Consolidate
        $ledger_codes2 = $ledger_codes;
        for ($ledger_index = 0; $ledger_index < count($ledger_codes); $ledger_index++)
        {
            for ($ledger_index2 = 0; $ledger_index2 < count($ledger_codes2); $ledger_index2++)
            {
                if ($ledger_codes[$ledger_index] == $ledger_codes2[$ledger_index2] && $ledger_index != $ledger_index2)
                {
                    $ledger_nets[$ledger_index] = float_add($ledger_nets[$ledger_index], $ledger_nets[$ledger_index2]);
                    $ledger_nets[$ledger_index2] = '0';
                    $ledger_taxes[$ledger_index] = float_add($ledger_taxes[$ledger_index], $ledger_taxes[$ledger_index2]);
                    $ledger_taxes[$ledger_index2] = '0';
                    $ledger_grosses[$ledger_index] = float_add($ledger_grosses[$ledger_index], $ledger_grosses[$ledger_index2]);
                    $ledger_grosses[$ledger_index2] = '0';
                }
            }
        }

        //Remove any entries that are now at zero
        for ($ledger_index = 0; $ledger_index < count($ledger_codes); $ledger_index++)
        {
            if ($ledger_nets[$ledger_index] == 0 && $ledger_taxes[$ledger_index] == 0 && $ledger_grosses[$ledger_index] == 0)
            {
                $ledger_codes[$ledger_index] = null;
                $ledger_nets[$ledger_index] = null;
                $ledger_tax_rates[$ledger_index] = null;
                $ledger_taxes[$ledger_index] = null;
                $ledger_grosses[$ledger_index] = null;
            }
        }
        $ledger_codes = array_values(array_filter($ledger_codes));
        $ledger_nets = array_values(array_filter($ledger_nets));
        $ledger_tax_rates = array_values(array_filter($ledger_tax_rates));
        $ledger_taxes = array_values(array_filter($ledger_taxes));
        $ledger_grosses = array_values(array_filter($ledger_grosses));
    }

    public static function record_income($name, $payment_method, $amount, $currency, $reference, $notes, &$transaction_no,
                            &$transaction_id, $date = null, $vendor_id = null, $g_tx_id = 0, $gateway_fee_document_items = array())
    {
        $nb_database = nbf_cms::$interop->database;
        $date_format = nbf_common::get_date_format();

        if (!isset($vendor_id) || $vendor_id == null || !$vendor_id)
        {
            if (count(nbf_gateway_txn::$document_ids) > 0)
            {
                //Get vendor_id from invoice
                $sql = "SELECT vendor_id FROM #__nbill_document WHERE id = " . intval(nbf_gateway_txn::$document_ids[0]);
                $nb_database->setQuery($sql);
                $vendor_id = $nb_database->loadResult();
            }
        }

        //Load the client tax exemption code, if applicable
        $tax_exemption_code = "";
        if (count(nbf_gateway_txn::$document_ids) > 0)
        {
            $sql = "SELECT tax_exemption_code FROM #__nbill_document WHERE id = " . intval(nbf_gateway_txn::$document_ids[0]);
            $nb_database->setQuery($sql);
            $tax_exemption_code = $nb_database->loadResult();
        }

        if ($vendor_id == null)
        {
            return NBILL_ERR_VENDOR_NOT_FOUND;
        }

        if ($date == null)
        {
            $date = nbf_common::nb_time();
        }
        $timestamp = $date;
        $date = nbf_common::nb_date($date_format, $date);

        //Construct array of values in same format as $_POSTed values from administrator
        $transaction_id = null;
        $income_values = array();
        $income_values['id'] = null;
        $income_values['action'] = "income";
        $income_values['transaction_type'] = 'IN';
        $income_values['amount'] = $amount;
        $income_values['currency'] = $currency;
        $income_values['date'] = $date;
        $income_values['timestamp'] = $timestamp;
        $income_values['for'] = '';
        $income_values['tax_reference'] = $tax_exemption_code;
        $income_values['invoices_' . $vendor_id] = array_unique(nbf_gateway_txn::$document_ids);
        $income_values['added_document_item_id'] = serialize($gateway_fee_document_items);

        //Load country from invoice or default to vendor country if not found
        $income_values['country'] = "";
        if (count(nbf_gateway_txn::$document_ids) > 0)
        {
            $sql = "SELECT billing_country FROM #__nbill_document WHERE id IN (" . implode(",", nbf_gateway_txn::$document_ids) . ")";
            $nb_database->setQuery($sql);
            $income_values['country'] = $nb_database->loadResult();
        }
        if (nbf_common::nb_strlen($income_values['country']) == 0)
        {
            $sql = "SELECT vendor_country FROM #__nbill_vendor WHERE id = " . intval($vendor_id);
            $nb_database->setQuery($sql);
            $income_values['country'] = $nb_database->loadResult();
        }

        $tax_rates = array();
        $tax_amounts = array();
        $tax_rates_electronic = array();
        $tax_amounts_electronic = array();
        $ledger_codes = array();
        $ledger_nets = array();
        $ledger_tax_rates = array();
        $ledger_taxes = array();
        $ledger_grosses = array();
        self::load_invoice_breakdowns_electronic(0, implode(",", nbf_gateway_txn::$document_ids), $tax_rates, $tax_amounts, $tax_rates_electronic, $tax_amounts_electronic, $ledger_codes, $ledger_nets, $ledger_tax_rates, $ledger_taxes, $ledger_grosses, $amount);

        $electronic_deliveries = array();
        for ($i=0;$i<count($tax_rates);$i++)
        {
            $electronic_deliveries[] = false;
        }
        for ($i=0;$i<count($tax_rates_electronic);$i++)
        {
            $electronic_deliveries[] = true;
        }
        $combined_tax_rates = array_merge($tax_rates, $tax_rates_electronic);
        $combined_tax_amounts = array_merge($tax_amounts, $tax_amounts_electronic);

        //Tot up the ledger breakdown gross amounts
        $ledger_total_gross = 0;
        $income_values['added_items'] = "";
        for ($i=0; $i<count($ledger_codes); $i++)
        {
            $ledger_total_gross = float_add($ledger_total_gross, $ledger_grosses[$i]);
        }

        //Adjust breakdowns if necessary for parital payments
        if (float_gtr($ledger_total_gross, $amount))
        {
            self::adjust_breakdowns_for_partial_payment($combined_tax_rates, $combined_tax_amounts, $ledger_codes, $ledger_nets, $ledger_tax_rates, $ledger_taxes, $ledger_grosses, $ledger_total_gross, $amount);
        }

        //Indices not necessarily sequential now...
        $i = 0;
        foreach ($combined_tax_rates as $index=>$tax_rate)
        {
            $i++;
            $income_values['tax_rate_' . $i] = $tax_rate;
            $income_values['tax_amount_' . $i] = $combined_tax_amounts[$index];
            $income_values['tax_rate_' . $i . '_electronic_delivery'] = $electronic_deliveries[$index] ? '1' : '0';
        }

        //Enter zeros for any remaining rates
        for ($j=$i + 1; $j<4; $j++)
        {
            $income_values['tax_rate_' . ($j)] = 0;
            $income_values['tax_amount_' . ($j)] = 0;
            $income_values['tax_rate_' . $j . '_electronic_delivery'] = '0';
        }

        $i = 0;
        foreach ($ledger_codes as $index=>$ledger_code)
        {
            $i++;
            $income_values['ledger_new_' . ($i) . '_' . $vendor_id] = $ledger_code;
            $income_values['ledger_net_new_' . ($i) . '_amount'] = $ledger_nets[$index];
            $income_values['ledger_tax_new_' . ($i) . '_rate'] = $ledger_tax_rates[$index];
            $income_values['ledger_tax_new_' . ($i) . '_amount'] = $ledger_taxes[$index];
            $income_values['ledger_gross_new_' . ($i) . '_amount'] = $ledger_grosses[$index];
            if (nbf_common::nb_strlen($income_values['added_items']) > 0)
            {
                $income_values['added_items'] .= ",";
            }
            $income_values['added_items'] .= ($i);
        }

        //In case of overpayment, allocate the excess to miscellaneous (if there is only 1 tax rate in use, apply that, as it is more likely to be correct than having zero tax)
        if (float_gtr($amount, $ledger_total_gross))
        {
            $tax_rate = 0;
            if (count($tax_rates) == 1)
            {
                $tax_rate = $tax_rates[0];
            }
            $income_values['ledger_new_' . ($i + 1) . '_' . $vendor_id] = '-1';
            $income_values['ledger_net_new_' . ($i + 1) . '_amount'] = format_number((float_subtract($amount, $ledger_total_gross) / (100 + $tax_rate)) * 100);
            $income_values['ledger_tax_new_' . ($i + 1) . '_rate'] = $tax_rate;
            $income_values['ledger_tax_new_' . ($i + 1) . '_amount'] = float_subtract(float_subtract($amount, $ledger_total_gross), $income_values['ledger_net_new_' . ($i + 1) . '_amount']);
            $income_values['ledger_gross_new_' . ($i + 1) . '_amount'] = float_subtract($amount, $ledger_total_gross);
            if (nbf_common::nb_strlen($income_values['added_items']) > 0)
            {
                $income_values['added_items'] .= ",";
            }
            $income_values['added_items'] .= ($i + 1);
            $income_values['tax_amount_1'] = float_add($income_values['tax_amount_1'], $income_values['ledger_tax_new_' . ($i + 1) . '_amount']);
        }

        $income_values['method'] = $payment_method;
        $income_values['notes'] = $notes;
        $income_values['name'] = $name;
        $income_values['reference'] = $reference;
        $income_values['vendor_id'] = $vendor_id;
        $income_values['g_tx_id'] = $g_tx_id;
        self::save_transaction_item($transaction_id, $income_values, $error_message, $transaction_no);
        return "";
    }

    /**
    * Where a partial payment is made, we need to allocate the funds to the nominal ledger codes so that the total allocated
    * is equal to the gross amount received, and the net and tax for each code matches the gross for that code
    * @param mixed $ledger_codes
    * @param mixed $ledger_nets
    * @param mixed $ledger_taxes
    * @param mixed $ledger_grosses
    * @param mixed $ledger_total_gross
    * @param mixed $amount
    */
    public static function adjust_breakdowns_for_partial_payment(&$tax_rates, &$tax_amounts, &$ledger_codes, &$ledger_nets, &$ledger_tax_rates, &$ledger_taxes, &$ledger_grosses, $ledger_total_gross, $amount, $payment_items = array())
    {
        //Consolidate arrays of ledger codes so that negative amounts are taken into consideration
        $new_ledger_codes = array();
        $new_ledger_nets = array();
        $new_ledger_tax_rates = array();
        $new_ledger_taxes = array();
        $new_ledger_grosses = array();
        $new_payment_items = array();

        for($i = 0; $i < count($ledger_codes); $i++)
        {
            $added_to_existing = false;
            for ($j = 0; $j < count($new_ledger_codes); $j++)
            {
                if ($new_ledger_codes[$j] == $ledger_codes[$i] && $new_ledger_tax_rates[$j] == $ledger_tax_rates[$i]) //Ledger code AND tax rate must match to avoid calculation errors
                {
                    //Add to existing
                    $new_ledger_nets[$j] = float_add($new_ledger_nets[$j], $ledger_nets[$i]);
                    $new_ledger_taxes[$j] = float_add($new_ledger_taxes[$j], $ledger_taxes[$i]);
                    $new_ledger_grosses[$j] = float_add($new_ledger_grosses[$j], $ledger_grosses[$i]);
                    if (count($payment_items) > 0)
                    {
                        $new_payment_items[$j] = implode(",", array_merge(explode(",", $new_payment_items[$j]), array($payment_items[$i])));
                    }
                    $added_to_existing = true;
                }
            }
            if (!$added_to_existing)
            {
                //Add new
                $new_ledger_codes[count($new_ledger_codes)] = $ledger_codes[$i];
                $new_ledger_nets[count($new_ledger_nets)] = $ledger_nets[$i];
                $new_ledger_tax_rates[count($new_ledger_tax_rates)] = $ledger_tax_rates[$i];
                $new_ledger_taxes[count($new_ledger_taxes)] = $ledger_taxes[$i];
                $new_ledger_grosses[count($new_ledger_grosses)] = $ledger_grosses[$i];
                $new_payment_items[count($new_payment_items)] = @$payment_items[$i];
            }
        }
        $ledger_codes = $new_ledger_codes;
        $ledger_nets = $new_ledger_nets;
        $ledger_tax_rates = $new_ledger_tax_rates;
        $ledger_taxes = $new_ledger_taxes;
        $ledger_grosses = $new_ledger_grosses;

        //Re-order so that any negative amounts appear first (so we can allocate the correct amount to the other items)
        array_multisort($ledger_grosses, $ledger_codes, $ledger_nets, $ledger_tax_rates, $ledger_taxes, $new_payment_items);

        if (!float_cmp($ledger_total_gross, $amount))
        {
            //First, try to find a ledger code that exactly matches the full amount
            for ($i = 0; $i < count($ledger_codes); $i++)
            {
                if (float_cmp($ledger_grosses[$i], $amount))
                {
                    for ($j = 0; $j < count($ledger_codes); $j++)
                    {
                        if ($j != $i)
                        {
                            unset($ledger_codes[$j]);
                            unset($ledger_nets[$j]);
                            unset($ledger_tax_rates[$j]);
                            unset($ledger_taxes[$j]);
                            unset($ledger_grosses[$j]);
                            unset($new_payment_items[$j]);
                        }
                    }
                    for ($k = 0; $k < count($tax_rates); $k++)
                    {
                        if ($tax_rates[$k] == $ledger_tax_rates[$i])
                        {
                            $tax_amounts[$k] = $ledger_taxes[$i];
                        }
                        else
                        {
                            unset($tax_rates[$k]);
                            unset($tax_amounts[$k]);
                        }
                    }
                    return $new_payment_items;
                }
            }

            //Otherwise, we will have to allocate the funds on a first come first served basis with a pro-rated amount on the last one
            for ($i = 0; $i < count($tax_amounts); $i++)
            {
                $tax_amounts[$i] = 0; //We will recalculate in a mo...
            }
            for ($i = 0; $i < count($ledger_codes); $i++)
            {
                if (float_gtr_e($amount, $ledger_grosses[$i]))
                {
                    //We have enough to fully cover this part
                    $amount = float_subtract($amount, $ledger_grosses[$i]);
                }
                else if ($amount > 0)
                {
                    //Partial
                    $config = nBillConfigurationService::getInstance()->getConfig();
                    $percentage = (format_number($amount, $config->precision_currency) / $ledger_total_gross) * 100;
                    $ledger_grosses[$i] = $amount;
                    $ledger_taxes[$i] = format_number(($ledger_taxes[$i] / 100) * $percentage, $config->precision_currency);
                    $ledger_nets[$i] = float_subtract($ledger_grosses[$i], $ledger_taxes[$i]);
                    $amount = 0;
                }
                else
                {
                    unset($ledger_codes[$i]);
                    unset($ledger_nets[$i]);
                    unset($ledger_tax_rates[$i]);
                    unset($ledger_taxes[$i]);
                    unset($ledger_grosses[$i]);
                    unset($new_payment_items[$i]);
                }
                if (isset($ledger_taxes[$i]))
                {
                    for ($j = 0; $j < count($tax_rates); $j++)
                    {
                        if (float_cmp($tax_rates[$j], $ledger_tax_rates[$i]))
                        {
                            $tax_amounts[$j] = float_add($tax_amounts[$j], $ledger_taxes[$i]);
                            break;
                        }
                    }
                }
            }
        }
        return $new_payment_items;
    }

    public static function save_transaction_item(&$transaction_id, $income_values, &$error_message, &$transaction_no, $expenditure = false)
    {
        //One function for all income editing/additions
        //whether from the administrator or a payment gateway
        $nb_database = nbf_cms::$interop->database;
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.tax.class.php");

        $error = "";
        $invoice_list = "";
        if ($transaction_id)
        {
            //Get existing invoice list (if it has been updated, we might need to set these invoices to unpaid or partially paid)
            $sql = "SELECT document_ids, amount FROM #__nbill_transaction WHERE id = " . intval($transaction_id);
            $nb_database->setQuery($sql);
            $nb_database->loadObject($old_details);
            $invoice_list = $old_details->document_ids;
            $old_income_amount = $old_details->amount;
        }

        //If adding a new income, and receipt number has not been specified, use the next available number
        $sql = "SELECT " . ($expenditure ? "suppress_payment_nos" : "suppress_receipt_nos") . " FROM #__nbill_vendor WHERE id = " . intval($income_values['vendor_id']);
        $nb_database->setQuery($sql);
        $suppress_transaction_no = $nb_database->loadResult();
        if (!$suppress_transaction_no)
        {
            if (!$transaction_id && nbf_common::nb_strlen(trim(nbf_common::get_param($income_values,'transaction_no'))) == 0)
            {
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.number.generator.php");
                $error = "";
                $income_values['transaction_no'] = nbf_number_generator::get_next_number(nbf_common::get_param($income_values,'vendor_id'), ($expenditure ? "payment" : "receipt"), $error);
                if ($income_values['transaction_no'] === false)
                {
                    $error_message = $error;
                    return;
                }
                $transaction_no = nbf_common::get_param($income_values,'transaction_no');
            }
        }

        if (nbf_common::get_param($income_values,'invoices_' . nbf_common::get_param($income_values,'vendor_id')))
        {
            if (count(nbf_common::get_param($income_values,'invoices_' . nbf_common::get_param($income_values,'vendor_id'))) > 0)
            {
                $income_values['document_ids'] = implode(",", nbf_common::get_param($income_values,'invoices_' . nbf_common::get_param($income_values,'vendor_id')));
            }
        }

        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
        if (nbf_common::nb_strlen(nbf_common::get_param($income_values,'date')) > 5)
        {
            $date_parts = nbf_date::get_date_parts(nbf_common::get_param($income_values,'date'), nbf_common::get_date_format(true));
            if (count($date_parts) == 3)
            {
                if (isset($income_values['timestamp'])) {
                    $income_values['date'] = $income_values['timestamp'];
                } else {
                    if (!$transaction_id && nbf_common::nb_date('Y-m-d') == $date_parts['y'] . '-' . $date_parts['m'] . '-' . $date_parts['d']) {
                        $income_values['date'] = nbf_common::nb_time(); //New record with today's date - assume time of receipt is now
                    } else {
                        //Time unknown
                        if ($transaction_id) {
                            //If date has not been changed to a different day, leave it alone
                            $sql = "SELECT `date` FROM #__nbill_transaction WHERE id = " . intval($transaction_id);
                            $nb_database->setQuery($sql);
                            $existing_date = $nb_database->loadResult();
                            if (nbf_common::nb_date('Y-m-d', $existing_date) == $date_parts['y'] . '-' . $date_parts['m'] . '-' . $date_parts['d']) {
                                $income_values['date'] = $existing_date;
                            } else {
                                $income_values['date'] = nbf_common::nb_mktime(0, 0, 0, $date_parts['m'], $date_parts['d'], $date_parts['y']);
                            }
                        } else {
                            $income_values['date'] = nbf_common::nb_mktime(0, 0, 0, $date_parts['m'], $date_parts['d'], $date_parts['y']);
                        }
                    }
                }
            }
        }

        if (!array_key_exists("document_ids", $income_values))
        {
            //All invoices de-selected
            $income_values["document_ids"] = "";
        }

        //If no received from value, look it up on the invoice, if applicable
        if (nbf_common::nb_strlen(trim(@$income_values['name'])) == 0)
        {
            if (nbf_common::nb_strlen($income_values['document_ids']) > 0)
            {
                $sql = "SELECT billing_name FROM #__nbill_document WHERE id IN (" . $income_values['document_ids'] . ")";
                $nb_database->setQuery($sql);
                $income_values['name'] = $nb_database->loadResult();
            }
        }

        $nb_database->bind_and_save("#__nbill_transaction", $income_values);

        $new_tx = false;
        if (!$transaction_id)
        {
            $new_tx = true;
            $income_values['id'] = $nb_database->insertid();
            $transaction_id = $income_values['id'];
            $_POST['id'] = $transaction_id;
        }

        //Apply g_tx_id to document table
        if (intval(nbf_common::get_param($income_values, 'g_tx_id')) && nbf_common::nb_strlen($income_values['document_ids']) > 0)
        {
            $sql = "UPDATE #__nbill_document SET gateway_txn_id = " . intval(nbf_common::get_param($income_values, 'g_tx_id')) . " WHERE id IN (" . $income_values['document_ids'] . ")";
            $nb_database->setQuery($sql);
            $nb_database->query();
        }

        //Delete invoice IDs from relational table (to be re-inserted in a mo...)
        $sql = "DELETE FROM #__nbill_document_transaction WHERE transaction_id = " . intval($transaction_id);
        $nb_database->setQuery($sql);
        $nb_database->query();

        //Re-insert invoice IDs on relational table and mark invoices as paid if applicable
        if (isset($income_values['document_ids']) && nbf_common::nb_strlen($income_values['document_ids'] > 0))
        {
            $document_ids_array = explode(",", $income_values['document_ids']);
            if (count($document_ids_array) > 0)
            {
                for ($i=0; $i<count($document_ids_array); $i++)
                {
                    $document_ids_array[$i] = intval($document_ids_array[$i]);
                }

                //Check whether paid in full
                $sql = "SELECT id, id AS document_id, total_net + total_shipping AS total_net, total_tax + total_shipping_tax AS total_tax, total_gross FROM #__nbill_document WHERE id IN (" . implode(",", $document_ids_array) . ") ORDER BY #__nbill_document.document_date";
                $nb_database->setQuery($sql);
                $invoices = $nb_database->loadObjectList();
                if ($invoices)
                {
                    //Load the amount for each tax rate used on the invoice
                    $sql = "SELECT #__nbill_document_items.document_id, #__nbill_document_items.tax_rate_for_item,
                                #__nbill_document_items.tax_for_item, #__nbill_document_items.shipping_for_item,
                                #__nbill_document_items.tax_rate_for_shipping, #__nbill_document_items.tax_for_shipping,
                                #__nbill_document_items.shipping_id, #__nbill_document_items.electronic_delivery
                                FROM #__nbill_document_items
                                WHERE #__nbill_document_items.document_id IN (" . implode(",", $document_ids_array) . ")";
                    $nb_database->setQuery($sql);
                    $document_items = $nb_database->loadObjectList();
                    if (!$document_items)
                    {
                        $document_items = array();
                    }
                    $tax_rates = array();
                    $tax_amounts = array();
                    $tax_index = 0;
                    nbf_tax::get_existing_tax_rates($document_items, $tax_rates, $tax_amounts);

                    $total_net = 0;
                    $total_tax_rate_1 = 0;
                    $total_tax_amount_1 = 0;
                    $total_tax_rate_2 = 0;
                    $total_tax_amount_2 = 0;
                    $total_tax_rate_3 = 0;
                    $total_tax_amount_3 = 0;
                    $total_gross = 0;
                    foreach ($invoices as $invoice)
                    {
                        $total_net = float_add($total_net, $invoice->total_net);
                        $total_gross = float_add($total_gross, $invoice->total_gross);
                    }
                    if (count($tax_rates) > 0)
                    {
                        $total_tax_rate_1 = format_number($tax_rates[0], 2);
                        $total_tax_amount_1 = $tax_amounts[0];
                    }
                    if (count($tax_rates) > 1)
                    {
                        $total_tax_rate_2 = format_number($tax_rates[1]);
                        $total_tax_amount_2 = $tax_amounts[1];
                    }
                    if (count($tax_rates) > 2)
                    {
                        $total_tax_rate_3 = format_number($tax_rates[2]);
                        $total_tax_amount_3 = $tax_amounts[2];
                    }
                    if (float_gtr_e($income_values['amount'], $total_gross))
                    {
                        //Paid in full
                        $sql = "UPDATE #__nbill_document SET paid_in_full = 1, partial_payment = 0, written_off = 0 WHERE id IN (" . implode(",", $document_ids_array) . ")";
                        $nb_database->setQuery($sql);
                        $nb_database->query();

                        //Re-insert in document_transaction
                        for($i=0; $i<count($invoices); $i++)
                        {
                            //Get tax breakdown for this invoice
                            $this_invoice_items = array();
                            foreach ($document_items as $document_item)
                            {
                                if ($document_item->document_id == $invoices[$i]->document_id)
                                {
                                    $this_invoice_items[] = $document_item;
                                }
                            }
                            $this_tax_rates = array();
                            $this_tax_amounts = array();
                            nbf_tax::get_existing_tax_rates($this_invoice_items, $this_tax_rates, $this_tax_amounts);
                            $tax_rate_1 = 0;
                            $tax_amount_1 = 0;
                            $tax_rate_2 = 0;
                            $tax_amount_2 = 0;
                            $tax_rate_3 = 0;
                            $tax_amount_3 = 0;
                            if (count($this_tax_rates) > 0)
                            {
                                $tax_rate_1 = $this_tax_rates[0];
                                $tax_amount_1 = $this_tax_amounts[0];
                            }
                            if (count($this_tax_rates) > 1)
                            {
                                $tax_rate_2 = $this_tax_rates[1];
                                $tax_amount_2 = $this_tax_amounts[1];
                            }
                            if (count($this_tax_rates) > 2)
                            {
                                $tax_rate_3 = $this_tax_rates[2];
                                $tax_amount_3 = $this_tax_amounts[2];
                            }
                            if ($i == count($invoices) - 1 && float_gtr($income_values['amount'], $total_gross))
                            {
                                //Overpayment - get the amount
                                $overpayment_gross = float_subtract($income_values['amount'], $total_gross);
                                $overpayment_tax_amount_1 = float_subtract($income_values['tax_amount_1'], $tax_amount_1);
                                $overpayment_tax_amount_2 = float_subtract($income_values['tax_amount_2'], $tax_amount_2);
                                $overpayment_tax_amount_3 = float_subtract($income_values['tax_amount_3'], $tax_amount_3);
                                $overpayment_total_tax = float_add($overpayment_tax_amount_1, float_add($overpayment_tax_amount_2, $overpayment_tax_amount_3));
                                $overpayment_net = float_subtract($overpayment_gross, $overpayment_total_tax);

                                //Add overpaid amounts to invoice amounts
                                $net_amount = float_add($invoices[$i]->total_net, $overpayment_net);
                                //Make sure the tax amounts match the TOTAL tax rates
                                if (count($this_tax_rates) > 0)
                                {
                                    if (float_cmp($this_tax_rates[0], $total_tax_rate_1))
                                    {
                                        $tax_amount_1 = $this_tax_amounts[0];
                                    }
                                    else
                                    {
                                        //No match - add an entry so we stay in sync
                                        array_unshift($this_tax_rates, $total_tax_rate_1);
                                        array_unshift($this_tax_amounts, 0);
                                    }
                                }
                                if (count($this_tax_rates) > 1)
                                {
                                    if (float_cmp($this_tax_rates[1], $total_tax_rate_2))
                                    {
                                        $tax_amount_2 = $this_tax_amounts[1];
                                    }
                                    else
                                    {
                                        //No match - add an entry so we stay in sync
                                        array_unshift($this_tax_rates, $total_tax_rate_2);
                                        array_unshift($this_tax_amounts, 0);
                                    }
                                }
                                if (count($this_tax_rates) > 2)
                                {
                                    if (float_cmp($this_tax_rates[2], $total_tax_rate_3))
                                    {
                                        $tax_amount_3 = $this_tax_amounts[2];
                                    }
                                }
                                $tax_amount_1 = float_add($tax_amount_1, $overpayment_tax_amount_1);
                                $tax_amount_2 = float_add($tax_amount_2, $overpayment_tax_amount_2);
                                $tax_amount_3 = float_add($tax_amount_3, $overpayment_tax_amount_3);
                                $gross_amount = float_add($invoices[$i]->total_gross, $overpayment_gross);

                                //Record the overpayment
                                $sql = "INSERT INTO #__nbill_document_transaction (document_id, transaction_id, date, net_amount, tax_rate_1, tax_amount_1,
                                                tax_rate_2, tax_amount_2, tax_rate_3, tax_amount_3, gross_amount)
                                                VALUES (" . intval($invoices[$i]->document_id) . ", " . intval($transaction_id) . ", " . intval(nbf_common::get_param($income_values,'date')) . ",
                                                " . format_number($net_amount) . ", " . format_number($total_tax_rate_1) . ", " . format_number($tax_amount_1) . ",
                                                " . format_number($total_tax_rate_2) . ", " . format_number($tax_amount_2) . ", " . format_number($total_tax_rate_3) . ",
                                                " . format_number($tax_amount_3) . ", " . format_number($gross_amount) . ")";
                            }
                            else
                            {
                                $sql = "INSERT INTO #__nbill_document_transaction (document_id, transaction_id, date, net_amount, tax_rate_1, tax_amount_1,
                                                tax_rate_2, tax_amount_2, tax_rate_3, tax_amount_3, gross_amount)
                                                VALUES (" . intval($invoices[$i]->document_id) . ", " . intval($transaction_id) . ", " . intval(nbf_common::get_param($income_values,'date')) . ",
                                                " . format_number($invoices[$i]->total_net) . ",
                                                " . format_number($tax_rate_1) . ", " . format_number($tax_amount_1) . ",
                                                " . format_number($tax_rate_2) . ", " . format_number($tax_amount_2) . ",
                                                " . format_number($tax_rate_3) . ", " . format_number($tax_amount_3) . ",
                                                " . format_number($invoices[$i]->total_gross) . ")";
                            }
                            $nb_database->setQuery($sql);
                            $nb_database->query();
                        }
                    }
                    else
                    {
                        //Check whether any other income items make up the shortfall
                        $other_income_total = 0;
                        //$other_income_net = 0;
                        $sql = "SELECT document_id, net_amount, tax_amount_1, tax_amount_2, tax_amount_3, gross_amount FROM #__nbill_document_transaction WHERE document_id IN (" . implode(",", $document_ids_array) . ")";
                        if ($transaction_id)
                        {
                            $sql .= " AND transaction_id != $transaction_id";
                        }
                        $nb_database->setQuery($sql);
                        $other_incomes = $nb_database->loadObjectList();
                        if (!$other_incomes)
                        {
                            $other_incomes = array();
                        }
                        foreach ($other_incomes as $other_income)
                        {
                            //$other_income_net = float_add($other_income_net, $other_income->net_amount);
                            $other_income_total = float_add($other_income_total, $other_income->gross_amount);
                        }
                        if (float_gtr_e(float_add($other_income_total, $income_values['amount']), $total_gross))
                        {
                            //Paid in full
                            $sql = "UPDATE #__nbill_document SET paid_in_full = 1, partial_payment = 0, written_off = 0 WHERE id IN (" . implode(",", $document_ids_array) . ")";
                        }
                        else
                        {
                            //Paid in part
                            $sql = "UPDATE #__nbill_document SET partial_payment = 1, paid_in_full = 0, written_off = 0 WHERE id IN (" . implode(",", $document_ids_array) . ")";
                        }
                        $nb_database->setQuery($sql);
                        $nb_database->query();

                        //Re-insert in document_transaction with partial amount
                        $sql = "SELECT #__nbill_document_items.document_id, #__nbill_document_items.tax_rate_for_item, #__nbill_document_items.tax_for_item,
                                    #__nbill_document_items.shipping_for_item, #__nbill_document_items.tax_rate_for_shipping,
                                    #__nbill_document_items.tax_for_shipping, #__nbill_document_items.shipping_id,
                                    #__nbill_document_items.electronic_delivery
                                    FROM #__nbill_document_items
                                    WHERE #__nbill_document_items.document_id IN (" . implode(",", $document_ids_array) . ")";
                        $nb_database->setQuery($sql);
                        $document_items = $nb_database->loadObjectList();
                        if (!$document_items)
                        {
                            $document_items = array();
                        }

                        $net_balance = float_subtract($income_values['amount'], float_add(float_add($income_values['tax_amount_1'], $income_values['tax_amount_2']), $income_values['tax_amount_3']));
                        $tax_balance = float_add(float_add($income_values['tax_amount_1'], $income_values['tax_amount_2']), $income_values['tax_amount_3']);
                        $gross_balance = $income_values['amount'];

                        foreach ($invoices as $invoice)
                        {
                            $invoice_total_net = $invoice->total_net;
                            $invoice_total_tax = $invoice->total_tax;
                            $invoice_total_gross = $invoice->total_gross;
                            if (is_array($other_incomes))
                            {
                                foreach ($other_incomes as $other_income)
                                {
                                    if ($other_income->document_id == $invoice->id)
                                    {
                                        $invoice_total_net = float_subtract($invoice_total_net, $other_income->net_amount);
                                        $invoice_total_tax = float_subtract($invoice_total_tax, float_add($other_income->tax_amount_1, float_add($other_income->tax_amount_2, $other_income->tax_amount_3)));
                                        $invoice_total_gross = float_subtract($invoice_total_gross, $other_income->gross_amount);
                                    }
                                }
                            }
                            //Work out how much is left
                            $net_balance = float_subtract($net_balance, $invoice_total_net);
                            $tax_balance = float_subtract($tax_balance, $invoice_total_tax);
                            $gross_balance = float_subtract($gross_balance, $invoice_total_gross);

                            $net_amount = format_number($net_balance > 0 ? $invoice_total_net : float_add($invoice_total_net, $net_balance));
                            $tax_amount = format_number($tax_balance > 0 ? $invoice_total_tax : float_add($invoice_total_tax, $tax_balance));
                            $gross_amount = format_number($gross_balance > 0 ? $invoice_total_gross : float_add($invoice_total_gross, $gross_balance));

                            $net_amount = $net_amount > 0 ? $net_amount : 0;
                            $tax_amount = $tax_amount > 0 ? $tax_amount : 0;
                            $gross_amount = $gross_amount > 0 ? $gross_amount : 0;

                            if ($net_amount || $tax_amount || $gross_amount)
                            {
                                //Get the tax breakdown for this invoice
                                $this_invoice_items = array();
                                foreach ($document_items as $document_item)
                                {
                                    if ($document_item->document_id == $invoice->document_id)
                                    {
                                        $this_invoice_items[] = $document_item;
                                    }
                                }
                                $this_tax_rates = array();
                                $this_tax_amounts = array();
                                $tax_rate_1 = 0;
                                $tax_amount_1 = 0;
                                $tax_rate_2 = 0;
                                $tax_amount_2 = 0;
                                $tax_rate_3 = 0;
                                $tax_amount_3 = 0;
                                nbf_tax::get_existing_tax_rates($this_invoice_items, $this_tax_rates, $this_tax_amounts);
                                if (count($this_tax_rates) > 0)
                                {
                                    $tax_rate_1 = $this_tax_rates[0];
                                    if (is_array($other_incomes))
                                    {
                                        foreach ($other_incomes as $other_income)
                                        {
                                            if ($other_income->document_id == $invoice->id)
                                            {
                                                $this_tax_amounts[0] = float_subtract($this_tax_amounts[0], $other_income->tax_amount_1);
                                            }
                                        }
                                    }
                                    $tax_amount_1 = float_gtr_e($this_tax_amounts[0], $income_values['tax_amount_1']) ? $income_values['tax_amount_1'] : $this_tax_amounts[0];
                                    $tax_amount = float_subtract($tax_amount, $tax_amount_1);
                                    if (float_gtr(0, $tax_amount))
                                    {
                                        $tax_amount_1 = float_subtract($tax_amount_1, abs($tax_amount));
                                    }
                                }
                                if (count($this_tax_rates) > 1)
                                {
                                    $tax_rate_2 = $this_tax_rates[1];
                                    if (is_array($other_incomes))
                                    {
                                        foreach ($other_incomes as $other_income)
                                        {
                                            if ($other_income->document_id == $invoice->id)
                                            {
                                                $this_tax_amounts[1] = float_subtract($this_tax_amounts[1], $other_income->tax_amount_2);
                                            }
                                        }
                                    }
                                    $tax_amount_2 = float_gtr_e($this_tax_amounts[1], $income_values['tax_amount_2']) ? $income_values['tax_amount_2'] : $this_tax_amounts[1];
                                    $tax_amount = float_subtract($income_values['tax_amount_2'], $tax_amount_2);
                                    if (float_gtr(0, $tax_amount))
                                    {
                                        $tax_amount_2 = float_subtract($tax_amount_2, abs($tax_amount));
                                    }
                                }
                                if (count($this_tax_rates) > 2)
                                {
                                    $tax_rate_3 = $this_tax_rates[2];
                                    if (is_array($other_incomes))
                                    {
                                        foreach ($other_incomes as $other_income)
                                        {
                                            if ($other_income->document_id == $invoice->id)
                                            {
                                                $this_tax_amounts[2] = float_subtract($this_tax_amounts[2], $other_income->tax_amount_3);
                                            }
                                        }
                                    }
                                    $tax_amount_3 = float_gr_e($this_tax_amounts[2], $income_values['tax_amount_3']) ? $income_values['tax_amount_3'] : $this_tax_amounts[2];
                                    $tax_amount = float_subtract($income_values['tax_amount_3'], $tax_amount_3);
                                    if (float_gtr(0, $tax_amount))
                                    {
                                        $tax_amount_3 = float_subtract($tax_amount_3, abs($tax_amount));
                                    }
                                }

                                $sql = "INSERT INTO #__nbill_document_transaction (document_id, transaction_id, date, net_amount, tax_rate_1, tax_amount_1,
                                            tax_rate_2, tax_amount_2, tax_rate_3, tax_amount_3, gross_amount)
                                            VALUES (" . intval($invoice->document_id) . ", " . intval($transaction_id) . ", " . intval(nbf_common::get_param($income_values,'date')) . ",
                                            " . format_number($net_amount) . ",
                                            " . format_number($tax_rate_1) . ", " . format_number($tax_amount_1) . ",
                                            " . format_number($tax_rate_2) . ", " . format_number($tax_amount_2) . ",
                                            " . format_number($tax_rate_3) . ", " . format_number($tax_amount_3) . ",
                                            " . format_number($gross_amount) . ")";
                                $nb_database->setQuery($sql);
                                $nb_database->query();

                                //Even if total for all invoices is not yet met, check whether this one is paid in full
                                if ($invoice_total_gross == $gross_amount) {
                                    $sql = "UPDATE #__nbill_document SET paid_in_full = 1, partial_payment = 0 WHERE id = " . intval($invoice->document_id);
                                    $nb_database->setQuery($sql);
                                    $nb_database->query();
                                }
                            }

                            //Mark paid in full if applicable...
                            if (float_subtract($gross_balance, $gross_amount) > 0) {
                            //if (float_cmp(format_number($gross_amount), format_number($invoice->total_gross))) {
                                $sql = "UPDATE #__nbill_document SET partial_payment = 0, paid_in_full = 1 WHERE id = " . $invoice->document_id;
                                $nb_database->setQuery($sql);
                                $nb_database->query();
                            }
                        }
                    }
                }
            }
        }

        //If the selected invoice(s) have been changed, set the old one(s) to unpaid or partially paid if required
        if (nbf_common::nb_strlen($invoice_list) > 0 && @$income_values['document_ids'] != $invoice_list)
        {
            $old_invoice_list = explode(",", $invoice_list);
            $new_invoice_list = explode(",", @$income_values['document_ids']);
            if (is_array($old_invoice_list))
            {
                foreach ($old_invoice_list as $old_invoice)
                {
                    if (array_search($old_invoice, $new_invoice_list) === false)
                    {
                        $sql = "SELECT total_gross FROM #__nbill_document WHERE id = " . intval($old_invoice);
                        $nb_database->setQuery($sql);
                        $invoice_total = $nb_database->loadResult();

                        //Check whether there are any other income items that pay off this invoice in full...
                        $sql = "SELECT SUM(gross_amount) FROM #__nbill_document_transaction WHERE document_id = " . intval($old_invoice);
                        $nb_database->setQuery($sql);
                        $other_totals = $nb_database->loadResult();
                        if ($other_totals == 0)
                        {
                            //Unpaid
                            $sql = "UPDATE #__nbill_document SET paid_in_full = 0, partial_payment = 0 WHERE id = " . intval($old_invoice);
                            $nb_database->setQuery($sql);
                            $nb_database->query();
                        }
                        else if ($other_totals < $invoice_total)
                        {
                            //Partially paid
                            $sql = "UPDATE #__nbill_document SET paid_in_full = 0, partial_payment = 1 WHERE id = " . intval($old_invoice);
                            $nb_database->setQuery($sql);
                            $nb_database->query();
                        }
                    }
                }
            }
        }

        //Get existing ledger items
        $sql = "SELECT * FROM #__nbill_transaction_ledger WHERE transaction_id = $transaction_id";
        $nb_database->setQuery($sql);
        $ledger_items = $nb_database->loadObjectList();
        if (!$ledger_items)
        {
            $ledger_items = array();
        }

        //Update any ledger items that have been amended
        foreach ($ledger_items as $ledger_item)
        {
            if (strtolower(nbf_version::$suffix) == 'lite' && count($ledger_items) == 1 && $ledger_item->nominal_ledger_code == -1) {
                $tax_amount = $income_values['tax_amount_1'] + $income_values['tax_amount_2'] + $income_values['tax_amount_3'];
                $income_values['ledger_net_' . $ledger_item->id . '_amount'] = $income_values['amount'] - $tax_amount;
                $income_values['ledger_tax_' . $ledger_item->id . '_rate'] = $income_values['tax_rate_1'];
                $income_values['ledger_tax_' . $ledger_item->id . '_amount'] = $tax_amount;
                $income_values['ledger_gross_' . $ledger_item->id . '_amount'] = $income_values['amount'];
                $income_values['ledger_' . $ledger_item->id . '_' . nbf_common::get_param($income_values,'vendor_id')] = '-1';
            }
            if (nbf_common::nb_strlen(@$income_values['ledger_net_' . $ledger_item->id . '_amount']) > 0 || nbf_common::nb_strlen(@$income_values['ledger_tax_' . $ledger_item->id . '_amount']) > 0 || nbf_common::nb_strlen(@$income_values['ledger_gross_' . $ledger_item->id . '_amount']) > 0)
            {
                $sql = "UPDATE #__nbill_transaction_ledger SET nominal_ledger_code = '" . nbf_common::get_param($income_values,'ledger_' . $ledger_item->id . '_' . nbf_common::get_param($income_values,'vendor_id')) . "',
                                    currency = '" . nbf_common::get_param($income_values,'currency') . "',
                                    net_amount = " . nbf_common::get_param($income_values,'ledger_net_' . $ledger_item->id . '_amount') .
                                    ", tax_rate = " . nbf_common::get_param($income_values,'ledger_tax_' . $ledger_item->id . '_rate') .
                                    ", tax_amount = " . nbf_common::get_param($income_values,'ledger_tax_' . $ledger_item->id . '_amount') .
                                    ", gross_amount = " . nbf_common::get_param($income_values,'ledger_gross_' . $ledger_item->id . '_amount') .
                                    " WHERE vendor_id = " . nbf_common::get_param($income_values,'vendor_id') .
                                    " AND transaction_id = " . intval($transaction_id) . "
                                    AND id = " . intval($ledger_item->id);
                $nb_database->setQuery($sql);
                $nb_database->query();
            }
        }

        //Delete any ledger items that have been removed
        $removed_items = array();
        if (isset($income_values['removed_items']) && nbf_common::nb_strlen($income_values['removed_items']) > 0)
        {
            $removed_items = explode(",", nbf_common::get_param($income_values,'removed_items'));
        }
        foreach ($removed_items as $removed_item)
        {
            if (nbf_common::nb_strlen($removed_item) > 0 && substr($removed_item, 0, 4) != "new_")
            {
                $sql = "DELETE FROM #__nbill_transaction_ledger WHERE transaction_id = " . intval($transaction_id) . " AND id = " . intval($removed_item);
                $nb_database->setQuery($sql);
                $nb_database->query();
            }
        }

        //Insert any ledger items that have been added
        $added_items = array();
        if (nbf_common::nb_strlen($income_values['added_items']) > 0){
            $added_items = explode(",", $income_values['added_items']);
        }
        if (count($added_items) > 0) {
            //Lite edition has no ledgers, so put everything to miscellaneous
            if (strtolower(nbf_version::$suffix) == 'lite') {
                $tax_amount = $income_values['tax_amount_1'] + $income_values['tax_amount_2'] + $income_values['tax_amount_3'];
                $income_values['ledger_net_new_1_amount'] = $income_values['amount'] - $tax_amount;
                $income_values['ledger_tax_new_1_rate'] = $income_values['tax_rate_1'];
                $income_values['ledger_tax_new_1_amount'] = $tax_amount;
                $income_values['ledger_gross_new_1_amount'] = $income_values['amount'];
                $income_values['ledger_new_1_' . nbf_common::get_param($income_values,'vendor_id')] = '-1';
            }
        }
        foreach ($added_items as $added_item)
        {
            $sql = "INSERT INTO #__nbill_transaction_ledger (vendor_id, transaction_id, nominal_ledger_code, currency, net_amount, tax_rate, tax_amount, gross_amount)
                                VALUES
                                (" . nbf_common::get_param($income_values,'vendor_id') . ", " . intval($transaction_id) . ", '" . nbf_common::get_param($income_values,'ledger_new_' . $added_item . '_' . nbf_common::get_param($income_values,'vendor_id')) . "', " .
                                "'" . nbf_common::get_param($income_values,'currency') . "', " . str_replace(",", "", nbf_common::get_param($income_values,'ledger_net_new_' . $added_item . '_amount')) . ",
                                " . str_replace(",", "", nbf_common::get_param($income_values,'ledger_tax_new_' . $added_item . '_rate')) . ", " . str_replace(",", "", nbf_common::get_param($income_values,'ledger_tax_new_' . $added_item . '_amount')) . ",
                                " . str_replace(",", "", nbf_common::get_param($income_values,'ledger_gross_new_' . $added_item . '_amount')) . ")";
            $nb_database->setQuery($sql);
            $nb_database->query();
        }

        //If the new row section has been filled in, add that as a new item as well
        if (nbf_common::get_param($income_values,'ledger_net_new_amount') != 0 || nbf_common::get_param($income_values,'ledger_tax_new_amount') != 0)
        {
            $sql = "INSERT INTO #__nbill_transaction_ledger (vendor_id, transaction_id, nominal_ledger_code, currency, net_amount, tax_rate, tax_amount, gross_amount)
                                VALUES
                                (" . nbf_common::get_param($income_values,'vendor_id') . ", " . intval($transaction_id) . ", " .
                                "'" . nbf_common::get_param($income_values,'ledger_new_' . nbf_common::get_param($income_values,'vendor_id')) . "', " .
                                "'" . nbf_common::get_param($income_values,'currency') . "', " . format_number(nbf_common::get_param($income_values,'ledger_net_new_amount')) . ",
                                " . format_number(nbf_common::get_param($income_values,'ledger_tax_new_rate')) . ", " . format_number(nbf_common::get_param($income_values,'ledger_tax_new_amount')) . ",
                                " . format_number(nbf_common::get_param($income_values,'ledger_gross_new_amount')) . ")";
            $nb_database->setQuery($sql);
            $nb_database->query();
        }

        //If it is on the guessed list, and ledger breakdown now balances, remove from guessed list
        if (nbf_common::get_param($income_values, 'guessed'))
        {
            $sql = "SELECT #__nbill_transaction.id, #__nbill_transaction.tax_amount_1, #__nbill_transaction.tax_amount_2,
                            #__nbill_transaction.tax_amount_3, #__nbill_transaction.transaction_type,
                            SUM(#__nbill_transaction_ledger.tax_amount) AS ledger_tax
                            FROM `#__nbill_transaction`
                            INNER JOIN #__nbill_transaction_ledger ON #__nbill_transaction.id = #__nbill_transaction_ledger.transaction_id
                            GROUP BY #__nbill_transaction.id, #__nbill_transaction.tax_amount_1, #__nbill_transaction.tax_amount_2,
                            #__nbill_transaction.tax_amount_3, #__nbill_transaction.transaction_type
                            HAVING #__nbill_transaction.tax_amount_1 + #__nbill_transaction.tax_amount_2 + #__nbill_transaction.tax_amount_3 != ledger_tax
                            AND #__nbill_transaction.id = " . intval($transaction_id);
            $nb_database->setQuery($sql);
            $results = $nb_database->loadObjectList();
            if (!$results || count($results) == 0)
            {
                //Seems ok now, so delete from the guessed list
                $sql = "DELETE FROM #__nbill_ledger_breakdown_guesses WHERE transaction_id = " . intval($transaction_id);
                $nb_database->setQuery($sql);
                $nb_database->query();
            }
        }

        if ($new_tx)
        {
            nbf_common::fire_event(($expenditure ? "expenditure_created" : "income_created"), array("id"=>$transaction_id));
        }
        else
        {
            if ($expenditure)
            {
                nbf_common::fire_event("record_updated", array("type"=>"expenditure", "id"=>$transaction_id));
            }
            else
            {
                nbf_common::fire_event("record_updated", array("type"=>"income", "id"=>$transaction_id));
            }
        }
    }

    /**
    * Prepare the billing values required by the payment gateway (extract from client record if poss, otherwise, contact)
    */
    public static function prepare_billing_data($contact_binding, $entity_binding, $contact_data, $entity_data)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.field.class.php");
        $billing_data = array();

        $contact_name = trim(nbf_form_fields::map_billing_field($contact_binding, $contact_data, "first_name") . ' ' . nbf_form_fields::map_billing_field($contact_binding, $contact_data, "last_name"));
        $billing_data['first_name'] = nbf_form_fields::map_billing_field($contact_binding, $contact_data, "first_name");
        $billing_data['last_name'] = nbf_form_fields::map_billing_field($contact_binding, $contact_data, "last_name");
        if (strlen($billing_data['first_name']) == 0 && strlen($billing_data['last_name']) == 0 && strlen($contact_name) > 0)
        {
            $billing_data['first_name'] = nbf_common::nb_strlen($contact_name) > 0 && nbf_common::nb_strpos($contact_name, " ") !== false ? substr($contact_name, 0, nbf_common::nb_strpos($contact_name, " ")) : "";
            $billing_data['last_name'] = nbf_common::nb_strpos($contact_name, " ") !== false ? substr($contact_name, nbf_common::nb_strpos($contact_name, " ") + 1) : $contact_name;
        }

        $billing_data['address_1'] = nbf_form_fields::map_billing_field($entity_binding, $entity_data, "address_1");
        if (nbf_common::nb_strlen($billing_data['address_1']) == 0)
        {
            $billing_data['address_1'] = nbf_form_fields::map_billing_field($contact_binding, $contact_data, "address_1");
        }
        $billing_data['address_2'] = nbf_form_fields::map_billing_field($entity_binding, $entity_data, "address_2");
        if (nbf_common::nb_strlen($billing_data['address_2']) == 0)
        {
            $billing_data['address_2'] = nbf_form_fields::map_billing_field($contact_binding, $contact_data, "address_2");
        }
        $billing_data['address_3'] = nbf_form_fields::map_billing_field($entity_binding, $entity_data, "address_3");
        if (nbf_common::nb_strlen($billing_data['address_3']) == 0)
        {
            $billing_data['address_3'] = nbf_form_fields::map_billing_field($contact_binding, $contact_data, "address_3");
        }
        $billing_data['town'] = nbf_form_fields::map_billing_field($entity_binding, $entity_data, "town");
        if (nbf_common::nb_strlen($billing_data['town']) == 0)
        {
            $billing_data['town'] = nbf_form_fields::map_billing_field($contact_binding, $contact_data, "town");
        }
        $billing_data['state'] = nbf_form_fields::map_billing_field($entity_binding, $entity_data, "state");
        if (nbf_common::nb_strlen($billing_data['state']) == 0)
        {
            $billing_data['state'] = nbf_form_fields::map_billing_field($contact_binding, $contact_data, "state");
        }
        $billing_data['postcode'] = nbf_form_fields::map_billing_field($entity_binding, $entity_data, "postcode");
        if (nbf_common::nb_strlen($billing_data['postcode']) == 0)
        {
            $billing_data['postcode'] = nbf_form_fields::map_billing_field($contact_binding, $contact_data, "postcode");
        }
        $billing_data['telephone'] = nbf_form_fields::map_billing_field($contact_binding, $contact_data, "telephone");
        $billing_data['company_name'] = nbf_form_fields::map_billing_field($entity_binding, $entity_data, "company_name");
        $billing_data['country'] = nbf_form_fields::map_billing_field($entity_binding, $entity_data, "country");
        if (nbf_common::nb_strlen($billing_data['country']) == 0)
        {
            $billing_data['country'] = nbf_form_fields::map_billing_field($contact_binding, $contact_data, "country");
        }
        $billing_data['email_address'] = nbf_form_fields::map_billing_field($contact_binding, $contact_data, "email_address");
        $billing_data['username'] = nbf_common::get_param($contact_binding, 'username');
        if (nbf_common::nb_strlen($billing_data['username']) == 0)
        {
            $billing_data['username'] = nbf_cms::$interop->user->username;
        }
        $billing_data['password'] = nbf_common::get_param($contact_binding, 'password');
        return $billing_data;
    }

    public static function prepare_for_invoice_payment($invoice, $gateway, &$orders, &$pro_rate_total, $add_document_discounts = true)
    {
        $nb_database = nbf_cms::$interop->database;

        //Prepare breakdown of ledger and tax, adjusting for partial payment if necessary
        $sql = "SELECT #__nbill_tax.*, #__nbill_vendor.tax_reference_no FROM #__nbill_tax INNER JOIN #__nbill_vendor ON #__nbill_tax.vendor_id = #__nbill_vendor.id WHERE vendor_id = " . intval($invoice->vendor_id) . " ORDER BY electronic_delivery, country_code";
        $nb_database->setQuery($sql);
        $tax_info = $nb_database->loadObjectList();
        if (!$tax_info)
        {
            $tax_info = array();
        }
        $sql = "SELECT * FROM #__nbill_shipping WHERE vendor_id = " . intval($invoice->vendor_id);
        $nb_database->setQuery($sql);
        $shipping = $nb_database->loadObjectList();
        if (!$shipping)
        {
            $shipping = array();
        }
        $sql = "SELECT #__nbill_document_items.*, #__nbill_shipping.nominal_ledger_code AS shipping_ledger_code
                FROM #__nbill_document_items
                LEFT JOIN #__nbill_shipping ON #__nbill_document_items.shipping_id = #__nbill_shipping.id
                WHERE #__nbill_document_items.document_id = " . intval($invoice->document_id) . "
                AND SUBSTR(#__nbill_document_items.product_code, 1, 3) != '[g='
                ORDER BY #__nbill_document_items.document_id, #__nbill_document_items.id";
        $nb_database->setQuery($sql);
        $invoice_items = $nb_database->loadObjectList();
        if (!$invoice_items)
        {
            $invoice_items = array();
        }

        $tax_rates = array();
        $tax_amounts = array();
        $tax_rates_electronic = array();
        $tax_amounts_electronic = array();
        $payment_item = array();
        $ledger_codes = array();
        $ledger_nets = array();
        $ledger_tax_rates = array();
        $ledger_taxes = array();
        $ledger_grosses = array();

        self::load_invoice_breakdowns_electronic(0, $invoice->document_id, $tax_rates, $tax_amounts, $tax_rates_electronic, $tax_amounts_electronic, $ledger_codes, $ledger_nets, $ledger_tax_rates, $ledger_taxes, $ledger_grosses, $invoice->total_outstanding, true);
        $tax_rates = array_merge($tax_rates, $tax_rates_electronic);
        $tax_amounts = array_merge($tax_amounts, $tax_amounts_electronic);

        $item_index = -1;

        //Make a note of which invoice items correspond with which ledger codes
        foreach ($ledger_codes as $item_index=>$ledger_code)
        {
            foreach ($invoice_items as &$invoice_item)
            {
                if (($ledger_code == $invoice_item->nominal_ledger_code && $invoice_item->tax_rate_for_item == $ledger_tax_rates[$item_index] && ($invoice_item->net_price_for_item != 0 || $invoice_item->tax_for_item != 0))
                    || ($ledger_code == $invoice_item->shipping_ledger_code && $invoice_item->tax_rate_for_shipping == $ledger_tax_rates[$item_index] && ($invoice_item->shipping_for_item != 0 || $invoice_item->tax_for_shipping != 0)))
                {
                    $payment_item[$item_index] = implode(",", array_merge(array_filter(explode(",", @$payment_item[$item_index])), array($invoice_item->id)));
                }
            }
        }
        reset($invoice_items);

        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.tax.class.php");
        $tax_name = "";
        $normal_tax_rate = 0;
        $shipping_tax_rate = 0;
        nbf_tax::get_tax_rates_standard($invoice, $invoice_items, $shipping, $tax_info, $tax_name, $tax_rates, $tax_amounts, $normal_tax_rate, $shipping_tax_rate, false, true);
        $this_tax_rates = $tax_rates[$invoice->document_id];
        $this_tax_amounts = $tax_amounts[$invoice->document_id];
        $payment_item = nbf_payment::adjust_breakdowns_for_partial_payment($this_tax_rates, $this_tax_amounts, $ledger_codes, $ledger_nets, $ledger_tax_rates, $ledger_taxes, $ledger_grosses, $invoice->total_gross, ($pro_rate_total ? $pro_rate_total : $invoice->total_outstanding), $payment_item);
        $tax_rates = array();
        $tax_amounts = array();
        $tax_rates[$invoice->document_id] = $this_tax_rates;
        $tax_amounts[$invoice->document_id] = $this_tax_amounts;

        //Prepare an order entry for each item
        foreach ($ledger_codes as $i=>$ledger_code)
        {
            $this_invoice_item = array();
            for ($j=0; $j<count($invoice_items); $j++)
            {
                $check_invoice_item = $invoice_items[$j];
                if (array_search($check_invoice_item->id, explode(",", $payment_item[$i])) !== false)
                {
                    $this_invoice_item[] = $check_invoice_item;
                }
            }
            $order_details['product_id'] = 0;
            $order_details['product_code'] = $invoice_items[0]->product_code;
            $order_details['product_name'] = '';
            foreach ($this_invoice_item as $this_invoice_item_part)
            {
                if (substr($this_invoice_item_part->product_code, 0, 3) != '[f=') //Fixed gateway fee for partial payment already made - do not display on summary
                {
                    if (nbf_common::nb_strlen($order_details['product_name']) > 0)
                    {
                        $order_details['product_name'] .= "<br />";
                    }
                    $order_details['product_name'] .= $this_invoice_item_part->product_description;
                    if ($ledger_nets[$i] != $this_invoice_item_part->net_price_for_item && $ledger_nets[$i] == $this_invoice_item_part->shipping_for_item)
                    {
                        $order_details['product_name'] .= ' (' . NBILL_MNU_SHIPPING . ')';
                    }
                }
            }
            if (strlen($order_details['product_name']) == 0) {
                $order_details['product_name'] = $invoice_items[0]->product_description;
            }
            $order_details['shipping_id'] = $invoice_items[0]->shipping_id;
            $order_details['nominal_ledger_code'] = $ledger_codes[$i];
            $order_details['quantity'] = 1;
            $order_details['setup_fee'] = 0;
            $order_details['setup_fee_tax_amount'] = 0;
            $order_details['net_price'] = $ledger_nets[$i];
            $order_details['payment_frequency'] = 'AA';
            $order_details['tax_amount'] = $ledger_taxes[$i];
            $order_details['tax_rate'] = $ledger_tax_rates[$i];
            $order_details['discount_id'] = 0;
            $order_details['discount_voucher_code'] = '';
            $order_details['electronic_delivery'] = $invoice_items[0]->electronic_delivery;
            $orders[] = $order_details;
        }

        

        //Adjust pro_rate total in case another invoice is being paid or part-paid at the same time
        foreach ($orders as $order)
        {
            $pro_rate_total = float_subtract($pro_rate_total, float_add($order['net_price'], $order['tax_amount']));
        }

        return $orders;
    }

    public static function prepare_document_order_summary(&$document, &$document_items, &$tax_rates, &$tax_amounts)
    {
        nbf_common::load_language("frontend");
        $nb_database = nbf_cms::$interop->database;

        $orders = array();
        $payment_item = array();
        $ledger_codes = array();
        $ledger_nets = array();
        $ledger_tax_rates = array();
        $ledger_taxes = array();
        $ledger_grosses = array();
        $item_index = -1;

        //Prepare breakdown of ledger and tax,
        $sql = "SELECT #__nbill_tax.*, #__nbill_vendor.tax_reference_no FROM #__nbill_tax INNER JOIN #__nbill_vendor ON #__nbill_tax.vendor_id = #__nbill_vendor.id WHERE vendor_id = " . intval($document->vendor_id) . " ORDER BY electronic_delivery, country_code";
        $nb_database->setQuery($sql);
        $tax_info = $nb_database->loadObjectList();
        if (!$tax_info)
        {
            $tax_info = array();
        }
        $sql = "SELECT * FROM #__nbill_shipping WHERE vendor_id = " . intval($document->vendor_id);
        $nb_database->setQuery($sql);
        $shipping = $nb_database->loadObjectList();
        if (!$shipping)
        {
            $shipping = array();
        }

        $shipping_indices = array();
        foreach ($document_items as $document_item)
        {
            if ($document_item->net_price_for_item != 0 || $document_item->tax_for_item != 0)
            {
                $item_index++;
                $payment_item[$item_index] = $document_item->id;
                $ledger_codes[$item_index] = $document_item->nominal_ledger_code;
                $ledger_nets[$item_index] = $document_item->net_price_for_item;
                $ledger_tax_rates[$item_index] = $document_item->tax_rate_for_item;
                $ledger_taxes[$item_index] = $document_item->tax_for_item;
                $ledger_grosses[$item_index] = float_add($ledger_nets[$item_index], $ledger_taxes[$item_index]);
            }
            if ($document_item->shipping_for_item != 0 || $document_item->tax_for_shipping != 0)
            {
                $item_index++;
                $shipping_indices[] = $item_index;
                $payment_item[$item_index] = $document_item->id;
                $ledger_codes[$item_index] = $document_item->shipping_ledger_code;
                $ledger_nets[$item_index] = $document_item->shipping_for_item;
                $ledger_tax_rates[$item_index] = $document_item->tax_rate_for_shipping;
                $ledger_taxes[$item_index] = $document_item->tax_for_shipping;
                $ledger_grosses[$item_index] = float_add($ledger_nets[$item_index], $ledger_taxes[$item_index]);
            }
        }

        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.tax.class.php");
        $tax_name = "";
        $normal_tax_rate = 0;
        $shipping_tax_rate = 0;
        nbf_tax::get_tax_rates_standard($document, $document_items, $shipping, $tax_info, $tax_name, $tax_rates, $tax_amounts, $normal_tax_rate, $shipping_tax_rate, false, true);

        //Prepare an order entry for each item
        foreach ($ledger_codes as $i=>$ledger_code)
        {
            $this_document_item = null;
            foreach ($document_items as $document_item)
            {
                if ($document_item->id == $payment_item[$i])
                {
                    $this_document_item = $document_item;
                    break;
                }
            }
            $order_details = array();
            $order_details['product_id'] = 0;
            $order_details['product_code'] = $this_document_item->product_code;
            $order_details['product_name'] = (array_search($i, $shipping_indices) !== false) ? (NBILL_ORDER_SUMMARY_SHIPPING_FEES . " " . NBILL_FOR . " " . $this_document_item->product_description . (nbf_common::nb_strlen(@$this_document_item->shipping_service) > 0 ? " (" . $this_document_item->shipping_service . ")" : "")) : $this_document_item->product_description;
            if (array_search($i, $shipping_indices) !== false)
            {
                $order_details['shipping_id'] = $this_document_item->shipping_id;
            }
            $order_details['nominal_ledger_code'] = $ledger_codes[$i];
            $order_details['quantity'] = $this_document_item->net_price_for_item != 0 || $this_document_item->tax_for_item != 0 ? $this_document_item->no_of_units : 1;
            $order_details['setup_fee'] = 0;
            $order_details['setup_fee_tax_amount'] = 0;
            $order_details['net_price'] = $ledger_nets[$i] / $order_details['quantity'];
            $order_details['payment_frequency'] = $this_document_item->quote_pay_freq;
            $order_details['tax_amount'] = $ledger_taxes[$i] / $order_details['quantity'];
            $order_details['tax_rate'] = $ledger_tax_rates[$i];
            $order_details['discount_id'] = 0;
            $order_details['discount_voucher_code'] = '';
            $order_details['electronic_delivery'] = $this_document_item->electronic_delivery;
            $orders[] = $order_details;
        }
        return $orders;
    }

    /**
    * Add a new line for the given quote item or discount to the given document
    * @param int $invoice_id ID of the invoice to add a line to
    * @param object $quote_item Quote item to add
    */
    public static function add_item_to_document($document_id, $item_to_add, &$error)
    {
        $nb_database = nbf_cms::$interop->database;

        //Find next ordering
        $sql = "SELECT ordering FROM #__nbill_document_items WHERE document_id = " . intval($document_id) . " ORDER BY ordering DESC LIMIT 1";
        $nb_database->setQuery($sql);
        $ordering = $nb_database->loadResult();
        if ($ordering !== null) {
            $ordering = intval($ordering);
            $ordering++;
        }

        $sql = "INSERT INTO `#__nbill_document_items` (`vendor_id`, `document_id`, `entity_id`, `nominal_ledger_code`,
                `product_description`, `detailed_description`, `net_price_per_unit`, `no_of_units`, `discount_amount`,
                `discount_description`, `net_price_for_item`, `tax_rate_for_item`, `tax_for_item`, `electronic_delivery`, `shipping_id`,
                `shipping_for_item`, `tax_rate_for_shipping`, `tax_for_shipping`, `gross_price_for_item`, `product_code`,
                `section_name`, `section_discount_title`, `section_discount_percent`, `quote_item_accepted`, `ordering`)
                VALUES (";
        $sql .= intval($item_to_add->vendor_id) . ", " . intval($document_id) . ", " . intval($item_to_add->entity_id) . ",
                '" . $item_to_add->nominal_ledger_code . "', '" . $nb_database->getEscaped($item_to_add->product_description) . "',
                '" . $nb_database->getEscaped($item_to_add->detailed_description) . "', " . $item_to_add->net_price_per_unit . ",
                " . $item_to_add->no_of_units . ", " . $item_to_add->discount_amount . ",
                '" . $nb_database->getEscaped($item_to_add->discount_description) . "', " . $item_to_add->net_price_for_item . ",
                " . $item_to_add->tax_rate_for_item . ", " . $item_to_add->tax_for_item . ", " . ($item_to_add->electronic_delivery ? '1' : '0') . ",
                " . intval($item_to_add->shipping_id) . ", " . $item_to_add->shipping_for_item . ",
                " . $item_to_add->tax_rate_for_shipping . ", " . $item_to_add->tax_for_shipping . ",
                " . $item_to_add->gross_price_for_item . ", '" . $nb_database->getEscaped($item_to_add->product_code) . "',
                '" . $nb_database->getEscaped($item_to_add->section_name) . "', '" . $nb_database->getEscaped($item_to_add->section_discount_title) . "',
                " . $item_to_add->section_discount_percent . ", " . intval(@$item_to_add->quote_item_accepted) . ", $ordering)";
        $nb_database->setQuery($sql);
        $nb_database->query();
        if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
        {
            $error = $nb_database->_errorMsg;
        }
        else
        {
            $document_item_id = $nb_database->insertid();
        }
    }

    public static function refresh_document_totals($document_id)
    {
        static $documents_paid = array();

        $nb_database = nbf_cms::$interop->database;

        $sql = "SELECT * FROM #__nbill_document_items WHERE document_id = $document_id ORDER BY document_id, ordering, id";
        $nb_database->setQuery($sql);
        $document_items = $nb_database->loadObjectList();
        if ($document_items == null)
        {
            $document_items = array();
        }

        $total_net = 0;
        $total_tax = 0;
        $total_shipping = 0;
        $total_shipping_tax = 0;
        $total_gross = 0;

        $section_discount_net = 0;
        $section_discount_tax = 0;
        $section_discount_gross = 0;
        $section_items = array();

        //Tot it all up
        foreach ($document_items as $document_item)
        {
            $section_items[] = $document_item;
            if ($document_item->section_name)
            {
                if ($document_item->section_discount_percent != 0)
                {
                    foreach ($section_items as $section_item)
                    {
                        $this_net = $section_item->net_price_for_item;
                        $this_discount_net = ($this_net / 100) * $document_item->section_discount_percent;
                        $this_discount_tax = ($this_discount_net / 100) * $section_item->tax_rate_for_item;
                        $this_discount_gross = float_add($this_discount_net, $this_discount_tax);
                        $section_discount_net = float_add($section_discount_net, $this_discount_net);
                        $section_discount_tax = float_add($section_discount_tax, $this_discount_tax);
                        $section_discount_gross = float_add($section_discount_gross, $this_discount_gross);
                    }
                }
                $section_items = array();
            }
            $total_net = float_add($total_net, str_replace(",", ".", $document_item->net_price_for_item . "")); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
            $total_tax = float_add($total_tax, str_replace(",", ".", $document_item->tax_for_item . "")); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
            $total_shipping = float_add($total_shipping, str_replace(",", ".", $document_item->shipping_for_item . "")); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
            $total_shipping_tax = float_add($total_shipping_tax, str_replace(",", ".", $document_item->tax_for_shipping . "")); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
            $total_gross = float_add($total_gross, str_replace(",", ".", $document_item->gross_price_for_item . "")); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
            if ($document_item->section_name && $document_item->section_discount_percent != 0)
            {
                $total_net = float_subtract($total_net, $section_discount_net);
                $total_tax = float_subtract($total_tax, $section_discount_tax);
                $total_gross = float_subtract($total_gross, $section_discount_gross);
                $section_discount_net = 0;
                $section_discount_tax = 0;
                $section_discount_gross = 0;
            }
        }
        $total_net = str_replace(",", ".", $total_net . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
        $total_tax = str_replace(",", ".", $total_tax . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
        $total_shipping = str_replace(",", ".", $total_shipping . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
        $total_shipping_tax = str_replace(",", ".", $total_shipping_tax . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
        $total_gross = str_replace(",", ".", $total_gross . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)

        $sql = "UPDATE #__nbill_document SET total_net = '$total_net', total_tax = '$total_tax',
                        total_shipping = '$total_shipping', total_shipping_tax = '$total_shipping_tax',
                        total_gross = '$total_gross' WHERE id = $document_id";
        $nb_database->setQuery($sql);
        $nb_database->query();

        if ($total_gross <= 0)
        {
            //If this is an invoice and the total is zero, mark it as paid in full
            $doc_record = null;
            $sql = "SELECT document_type, partial_payment, paid_in_full FROM #__nbill_document WHERE id = $document_id";
            $nb_database->setQuery($sql);
            $nb_database->loadObject($doc_record);
            if ($doc_record && $doc_record->document_type == 'IN' && $doc_record->paid_in_full == 0)
            {
                $sql = "UPDATE #__nbill_document SET partial_payment = 0, paid_in_full = 1 WHERE document_type = 'IN' AND id = $document_id";
                $nb_database->setQuery($sql);
                $nb_database->query();

                if (array_search($document_id, $documents_paid) === false)
                {
                    $documents_paid[$document_id] = intval($doc_record->partial_payment) . "," . intval($doc_record->paid_in_full);
                }
            }
        }
        else
        {
            //If we have previously set this to paid in full, but now another refresh has added something, unmark it as paid
            $previous_doc = array_key_exists($document_id, $documents_paid);
            if ($previous_doc)
            {
                $previous_doc_values = explode(",", $documents_paid[$document_id]);
                if (count($previous_doc_values) == 2)
                {
                    $sql = "UPDATE #__nbill_document SET partial_payment = " . intval($previous_doc_values[0]) . ", paid_in_full = " . intval($previous_doc_values[1]) . " WHERE id = $document_id";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                }
            }
        }
        return $total_gross;
    }

    public static function get_amount_outstanding($document_id)
    {
        $nb_database = nbf_cms::$interop->database;
        $sql = "SELECT #__nbill_document.total_gross - SUM(#__nbill_transaction.amount) FROM #__nbill_transaction
                INNER JOIN #__nbill_document_transaction ON #__nbill_transaction.id = #__nbill_document_transaction.transaction_id
                INNER JOIN #__nbill_document ON #__nbill_document_transaction.document_id = #__nbill_document.id
                WHERE #__nbill_document.id = " . intval($document_id);
        $nb_database->setQuery($sql);
        return $nb_database->loadResult();
    }

    /**
    * Pass in a gateway transaction ID and get back an array containing details about the transaction inluding vendor_id, vendor_name, client_id, billing_name, currency, amount, pending_order_id, order_ids, invoice_ids, order_nos, invoice_nos, description, reference (some of which may be blank)
    * @param mixed $g_tx_id
    */
    public static function loadTransactionDetails($g_tx_id)
    {
        nbf_common::load_language('income');
        $nb_database = nbf_cms::$interop->database;
        $details = array('vendor_id'=>0, 'vendor_name'=>'', 'client_id'=>0, 'billing_name'=>'',
                        'currency'=>'', 'amount'=>'', 'pending_order_id'=>0, 'order_ids'=>0,
                        'invoice_ids'=>0, 'order_nos'=>'', 'invoice_nos'=>'', 'description'=>'',
                        'reference'=>'', 'client_email'=>'', 'vendor_email'=>'');

        $tx = null;
        $sql = "SELECT pending_order_id, document_ids, entity_id, vendor_id, net_amount, tax_amount, shipping_amount, shipping_tax_amount
                FROM #__nbill_gateway_tx WHERE id = " . intval($g_tx_id);
        $nb_database->setQuery($sql);
        $nb_database->loadObject($tx);
        if ($tx) {
            $details['vendor_id'] = $tx->vendor_id;
            $details['client_id'] = $tx->entity_id;
            $details['amount'] = format_number(float_add($tx->net_amount, float_add($tx->tax_amount, float_add($tx->shipping_amount, $tx->shipping_tax_amount))), 'currency_grand');
            $details['pending_order_id'] = substr($tx->pending_order_id, 0, 6) == 'RENEW_' ? '' : $tx->pending_order_id;
            $details['order_ids'] = substr($tx->pending_order_id, 0, 6) == 'RENEW_' ? substr($tx->pending_order_id, 6) : '';
            $details['invoice_ids'] = $tx->document_ids;

            if ($details['invoice_ids']) {
                //Invoice payment
                $sql = "SELECT vendor_name, entity_id, billing_name, currency, document_no FROM #__nbill_document WHERE id IN (" . $details['invoice_ids'] . ")";
                $nb_database->setQuery($sql);
                $invoices = $nb_database->loadObjectList();
                $sql = "SELECT product_description FROM #__nbill_document_items WHERE document_id IN (" . $details['invoice_ids'] . ") ORDER BY document_id, ordering";
                $nb_database->setQuery($sql);
                $invoice_items = $nb_database->loadObjectList();
                $description = array();
                foreach ($invoice_items as $invoice_item) {
                    $description[] = $invoice_item->product_description;
                }
                $invoice_nos = array();
                if ($invoices && count($invoices) > 0) {
                    foreach ($invoices as $invoice) {
                        $invoice_nos[] = $invoice->document_no;
                    }
                    $details['vendor_name'] = $invoices[0]->vendor_name;
                    $details['client_id'] = intval($invoices[0]->entity_id) ? $invoices[0]->entity_id : $details['client_id'];
                    $details['billing_name'] = $invoices[0]->billing_name;
                    $details['currency'] = $invoices[0]->currency;
                    $details['invoice_nos'] = implode(", ", $invoice_nos);
                    $details['description'] = NBILL_INCOME_INVOICE_NO . ' ' . $details['invoice_nos'] . ': ' . implode(", ", $description);
                }
            } else {
                //Order
                $sql = "SELECT #__nbill_orders.id, #__nbill_orders.order_no, #__nbill_vendor.vendor_name,
                        #__nbill_orders.client_id, #__nbill_entity.company_name, #__nbill_contact.first_name,
                        #__nbill_contact.last_name, #__nbill_orders.currency, #__nbill_orders.product_name
                        FROM #__nbill_orders
                        INNER JOIN #__nbill_vendor ON #__nbill_orders.vendor_id = #__nbill_vendor.id
                        LEFT JOIN #__nbill_entity ON #__nbill_orders.client_id = #__nbill_entity.id
                        INNER JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id
                        WHERE gateway_txn_id = " . intval($g_tx_id);
                $nb_database->setQuery($sql);
                $orders = $nb_database->loadObjectList();
                $order_nos = array();
                $order_ids = array();
                $products = array();
                if ($orders && count($orders) > 0) {
                    foreach ($orders as $order) {
                        $order_nos[] = $order->order_no;
                        $order_ids[] = $order->id;
                        $products[] = $order->product_name;
                    }
                    $details['vendor_name'] = $orders[0]->vendor_name;
                    $details['client_id'] = intval($orders[0]->client_id) ? $orders[0]->client_id : $details['client_id'];
                    $details['billing_name'] = $orders[0]->company_name ? $order[0]->company_name : trim($orders[0]->first_name . ' ' . $orders[0]->last_name);
                    $details['currency'] = $orders[0]->currency;
                    $details['order_nos'] = implode(", ", $order_nos);
                    $details['order_ids'] = implode(", ", $order_ids);
                    $details['description'] = implode(", ", $products);
                }
            }
        }

        if (intval($details['client_id'])) {
            $sql = "SELECT email_address FROM #__nbill_contact
                    INNER JOIN #__nbill_entity ON #__nbill_entity.primary_contact_id = #__nbill_contact.id
                    WHERE #__nbill_entity.id = " . intval($details['client_id']);
            $nb_database->setQuery($sql);
            $details['client_email'] = $nb_database->loadResult();
        }

        if (intval($details['vendor_id'])) {
            $sql = "SELECT admin_email FROM #__nbill_vendor WHERE id = " . intval($details['vendor_id']);
            $nb_database->setQuery($sql);
            $details['vendor_email'] = $nb_database->loadResult();
        }

        $sql = "SELECT transaction_no FROM #__nbill_transaction WHERE g_tx_id = " . intval($g_tx_id);
        $nb_database->setQuery($sql);
        $details['reference'] = $nb_database->loadResult();

        return $details;
    }
}

/**
* Represents a set of totals (net, tax, shipping, shipping tax, gross)
*/
class nbf_totals
{
    public $total_net = 0;
    public $total_tax = 0;
    public $total_shipping = 0;
    public $total_shipping_tax = 0;
    public $total_gross = 0;
}