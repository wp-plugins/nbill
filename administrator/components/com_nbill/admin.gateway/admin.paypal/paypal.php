<?php
/**
* Main entry point for Paypal gateway - constructs URL and redirects to Paypal
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

	if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal." . nbf_cms::$interop->language . ".php"))
	{
		include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal." . nbf_cms::$interop->language . ".php");
	}
	else
	{
		if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal.en-GB.php"))
		{
			include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal.en-GB.php");
		}
	}

	$nb_database = nbf_cms::$interop->database;
	$sql = "SELECT * FROM #__nbill_payment_gateway WHERE gateway_id = 'paypal'";
	$nb_database->setQuery($sql);
	$paypal_fields = $nb_database->loadAssocList('g_key');
	if (!array_key_exists('business', $paypal_fields))
	{
		//loadAssocList has not worked
		$paypal_fields = array();
		$alt_paypal_fields = $nb_database->loadObjectList();
		if (!$alt_paypal_fields)
		{
			$alt_paypal_fields = array();
		}
		foreach ($alt_paypal_fields as $alt_paypal_field)
		{
			$paypal_fields[$alt_paypal_field->g_key] = array();
			$paypal_fields[$alt_paypal_field->g_key]['g_key'] = $alt_paypal_field->g_key;
			$paypal_fields[$alt_paypal_field->g_key]['g_value'] = $alt_paypal_field->g_value;
		}
	}

	$is_one_off = false;
	$url_pay_freq = "";
	$first_pay_freq = "";

    $standard_totals->total_gross = str_replace(",", ".", $standard_totals->total_gross . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot

	switch ($payment_frequency)
	{
		case "XX": //Not Applicable
			//Nothing to do here!
			return;
		case "AA": //One-off
			$is_one_off = true;
			break;
		case "BB": //Weekly
			$url_pay_freq = "&p3=1&t3=W";
			$first_pay_freq = "&p1=1&t1=W&a1=" . $standard_totals->total_gross;
			break;
		case "BX": //Four-weekly
			$url_pay_freq = "&p3=4&t3=W";
			$first_pay_freq = "&p1=4&t1=W&a1=" . $standard_totals->total_gross;
			break;
		case "CC": //Monthly
			$url_pay_freq = "&p3=1&t3=M";
			$first_pay_freq = "&p1=1&t1=M&a1=" . $standard_totals->total_gross;
			break;
		case "DD": //Quarterly
			$url_pay_freq = "&p3=3&t3=M";
			$first_pay_freq = "&p1=3&t1=M&a1=" . $standard_totals->total_gross;
			break;
		case "DX": //Semi-annually
			$url_pay_freq = "&p3=6&t3=M";
			$first_pay_freq = "&p1=6&t1=M&a1=" . $standard_totals->total_gross;
			break;
		case "EE": //Annually
			$url_pay_freq = "&p3=1&t3=Y";
			$first_pay_freq = "&p1=1&t1=Y&a1=" . $standard_totals->total_gross;
			break;
		case "FF": //Biannually
			$url_pay_freq = "&p3=2&t3=Y";
			$first_pay_freq = "&p1=2&t1=Y&a1=" . $standard_totals->total_gross;
			break;
		case "GG": //Five-yearly
			$url_pay_freq = "&p3=5&t3=Y";
			$first_pay_freq = "&p1=5&t1=Y&a1=" . $standard_totals->total_gross;
			break;
		case "HH": //Ten-yearly
			$url_pay_freq = "&p3=10&t3=Y";
			$first_pay_freq = "&p1=10&t1=Y&a1=" . $standard_totals->total_gross;
			break;
	}

	if ($paypal_fields['sandbox']['g_value'] == 1)
	{
		$ppurl = "https://www.sandbox.paypal.com";
	}
	else
	{
		$ppurl = "https://www.paypal.com";
	}
    if (nbf_common::nb_strlen($document_no) > 0)
    {
	    $product_ordered = $document_no . ": ";
    }
    else
    {
        $product_ordered = "";
    }
	$product_code = "";
	foreach ($orders as &$order)
    {
        if ((isset($order['net_price']) && $order['net_price'] > 0) || (isset($order['setup_fee']) && $order['setup_fee'] > 0))
        {
            //Concatenate products
            if (nbf_common::nb_strlen($product_ordered) > 0 && $product_ordered != $document_no . ": ")
            {
                $product_ordered .= " + ";
            }
            $product_ordered .= $order['product_name'];
            if ($order['quantity'] > 1)
            {
                $product_ordered .= " x " . $order['quantity'];
            }
            if (isset($order['relating_to']) && nbf_common::nb_strlen($order['relating_to']) > 0)
            {
                $product_ordered .= " (" . $order['relating_to'] . ")";
            }
            if (nbf_common::nb_strlen($product_code) > 0)
            {
                $product_code .= "+";
            }
            if (isset($order['product_code']))
            {
                $product_code .= $order['product_code'];
            }
        }
    }
	//Product details can only be 125 characters, max
	if (nbf_common::nb_strlen($product_ordered) > 125)
	{
		$product_ordered = substr($product_ordered, 0, 125);
	}
	if (nbf_common::nb_strlen($product_code) > 125)
	{
		$product_code = substr($product_code, 0, 125);
	}

	if ($is_one_off)
	{
		//Construct URL for buy now button
        $ppurl .= "/cgi-bin/webscr?";
		$ppurl .= "cmd=_xclick";
		$ppurl .= "&business=" . urlencode($paypal_fields['business']['g_value']);
		$ppurl .= "&item_name=" . urlencode($product_ordered);
		$ppurl .= "&item_number=" . urlencode($product_code);

		if ($standard_totals->total_shipping > 0)
		{
			$ppurl .= "&no_shipping=2";
		}
		else
		{
			$ppurl .= "&no_shipping=1";
		}
		$ppurl .= "&no_note=1";
        $ppurl .= "&bn=PP%2dBuyNowBF&charset=" . urlencode(nbf_cms::$interop->char_encoding);

		$ppurl .= "&amount=" . urlencode($standard_totals->total_gross);
	}
	else
	{
		$ppurl .= "/cgi-bin/webscr?cmd=_xclick-subscriptions&business=";
		$ppurl .= urlencode($paypal_fields['business']['g_value']);
		$ppurl .= "&item_name=" . urlencode($product_ordered);
		$ppurl .= "&item_number=" . urlencode($product_code);
		if ($regular_totals->total_shipping > 0)
		{
			$ppurl .= "&no_shipping=2";
		}
		else
		{
			$ppurl .= "&no_shipping=1";
		}
		$ppurl .= "&no_note=1"; //Mandatory for recurring payments
		if (nbf_common::nb_strlen(nbf_common::get_param($billing_data, 'country')) == 2)
		{
			$ppurl .= "&lc=" . nbf_common::get_param($billing_data, 'country');
		}
		$ppurl .= "&bn=PP%2dSubscriptionsBF&charset=" . urlencode(nbf_cms::$interop->char_encoding);
		$ppurl .= "&a3=" . urlencode($regular_totals->total_gross);
		$ppurl .= "&src=1"; //Recurring
	}
	$ppurl .= "&currency_code=" . $currency;
	$ppurl .= "&custom=" . $g_tx_id;
	$ppurl .= "&rm=2"; //Force POST back

	//If expiry date specified, add number of payments (minus 1 for the first one)
	if (isset($no_of_payments) && $no_of_payments > 1 && $payment_frequency != "AA" && $payment_frequency != "XX")
	{
		$ppurl .= "&sra=1&srt=" . (intval($no_of_payments) - 1);
	}

    $success_url = "";
    $return_url_part_1 = nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . "&action=gateway&gateway=paypal&task=success&g_tx_id=" . $g_tx_id . "&return=";

    //Attempt to get it from the order form, if applicable
    if (isset($form_id) && $form_id)
    {
        $sql = "SELECT thank_you_redirect FROM #__nbill_order_form WHERE id = " . intval($form_id);
        $nb_database->setQuery($sql);
        $thank_you_redirect = $nb_database->loadResult();
        if (nbf_common::nb_strlen(trim($thank_you_redirect)) > 0)
        {
            $success_url = $return_url_part_1 . base64_encode($thank_you_redirect);
        }
    }

    if (!$success_url)
    {
        $success_url = $return_url_part_1;
    }

    if (nbf_common::nb_strlen($success_url) > 0)
    {
        $ppurl .= "&return=" . urlencode($success_url . nbf_cms::$interop->site_page_suffix);
    }

	if (nbf_common::nb_strlen(@$paypal_fields['failure_url']['g_value']) > 0)
    {
        if (!array_key_exists('cancel_return', $paypal_fields))
        {
            $ppurl .= "&cancel_return=" . urlencode($paypal_fields['failure_url']['g_value']);
        }
    }

	foreach ($paypal_fields as $key=>$value)
	{
		switch ($key)
		{
			case "a1":
			case "p1":
			case "t1":
			case "a2":
			case "p2":
			case "t2":
			case "invoice":
			case "usr_manage":
			case "cn":
			case "cs":
			case "on0":
			case "os0":
			case "on1":
			case "os1":
			case "tax":
			case "modify":
			case "page_style":
                if (nbf_common::nb_strlen(trim($value['g_value'])) > 0)
				{
                    $ppurl .= "&" . urlencode($key) . "=" . urlencode($value['g_value']);
                }
				break;
			case "notify_url":
                if (nbf_common::nb_strlen(trim($value['g_value'])) > 0)
                {
                    if (substr($value['g_value'], 0, 7) == "http://" || substr($value['g_value'], 0, 8) == "https://")
                    {
                        $ppurl .= "&" . urlencode($key) . "=" . urlencode(str_replace("[NBILL_FE_PAGE_PREFIX]", nbf_cms::$interop->site_page_prefix, $value['g_value']) . nbf_cms::$interop->public_site_page_suffix());
                    }
                    else
                    {
                        $ppurl .= "&" . urlencode($key) . "=" . urlencode(str_replace("[NBILL_FE_PAGE_PREFIX]", nbf_cms::$interop->site_page_prefix, nbf_cms::$interop->live_site . "/" . $value['g_value']) . nbf_cms::$interop->public_site_page_suffix());
                    }
                }

				break;
		}
	}
	$ppurl .= $url_pay_freq;
    if ($regular_totals->total_gross != $standard_totals->total_gross && $payment_frequency != 'AA' && $payment_frequency != 'XX') {
	    $ppurl .= $first_pay_freq;
    }

	//Customer details
	if (nbf_common::nb_strlen(nbf_common::get_param($billing_data,'first_name')) > 0)
	{
		$ppurl .= "&first_name=" . nbf_common::get_param($billing_data,'first_name');
	}
	if (nbf_common::nb_strlen(nbf_common::get_param($billing_data,'last_name')) > 0)
	{
		$ppurl .= "&last_name=" . nbf_common::get_param($billing_data,'last_name');
	}
	if (nbf_common::nb_strlen(nbf_common::get_param($billing_data,'address_1')) > 0)
	{
		$ppurl .= "&address1=" . nbf_common::get_param($billing_data,'address_1');
	}
	if (nbf_common::nb_strlen(nbf_common::get_param($billing_data,'address_2')) > 0)
	{
		$ppurl .= "&address2=" . nbf_common::get_param($billing_data,'address_2');
	}
	if (nbf_common::nb_strlen(nbf_common::get_param($billing_data,'town')) > 0)
	{
		$ppurl .= "&city=" . nbf_common::get_param($billing_data,'town');
	}
	if (nbf_common::nb_strlen(nbf_common::get_param($billing_data,'state')) > 0)
	{
		$ppurl .= "&state=" . nbf_common::get_param($billing_data,'state');
	}
	if (nbf_common::nb_strlen(nbf_common::get_param($billing_data,'postcode')) > 0)
	{
		$ppurl .= "&zip=" . nbf_common::get_param($billing_data,'postcode');
	}
	if (nbf_common::nb_strlen(nbf_common::get_param($billing_data,'country')) > 0)
	{
		$ppurl .= "&country=" . nbf_common::get_param($billing_data,'country');
	}
	if (nbf_common::nb_strlen(nbf_common::get_param($billing_data,'telephone')) > 0)
	{
		$ppurl .= "&night_phone_b=" . nbf_common::get_param($billing_data,'telephone');
	}
    if (nbf_common::nb_strlen(nbf_common::get_param($billing_data,'email_address')) > 0)
    {
        $ppurl .= "&email=" . urlencode(nbf_common::get_param($billing_data,'email_address'));
    }

	//Strip any brackets, as they mess things up for Paypal
	$ppurl = str_replace("(", urlencode(":"), $ppurl);
	$ppurl = str_replace(urlencode("("), urlencode(":"), $ppurl);
	$ppurl = str_replace(")", urlencode(""), $ppurl);
	$ppurl = str_replace(urlencode(")"), urlencode(""), $ppurl);

	nbf_common::redirect($ppurl);
	return;