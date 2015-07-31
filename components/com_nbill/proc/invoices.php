<?php
/**
* Main processing file for front end invoice list
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

switch (nbf_common::get_param($_REQUEST, 'task'))
{
	case "pay":
		pay_invoice();
		break;
	case "print":
        $_REQUEST['task'] = "silent";
        $task = "silent";
        nbf_common::load_language("invoices");
		include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.html/invoices.html.php");
		include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/invoices.php");
		printPreviewPopup(array(0=>$cid), false, false, "", "", nbf_frontend::get_display_option('login_to_pay_invoice'));
		exit;
	case "pdf":
		$_REQUEST['task'] = "silent";
        $task = "silent";
        nbf_common::load_language("invoices");
		include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.html/invoices.html.php");
		include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/invoices.php");
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
		printPDFPopup(array(0=>$cid), false, false, "", nbf_frontend::get_display_option('login_to_pay_invoice'));
		exit;
	case "view":
	default:
		if (nbf_cms::$interop->user->id)
		{
			show_invoices();
		}
		else
		{
			nbill_show_login_box();
			return;
		}
		break;
}

function show_invoices()
{
    $nb_database = nbf_cms::$interop->database;
    $date_format = nbf_common::get_date_format();
	if (nbf_frontend::get_display_option("invoice_date_range"))
	{
		//Work out date range
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
		$cal_date_format = nbf_common::get_date_format(true);
		$date_parts = nbf_common::nb_getdate(time());
		$search_date_from = -1;
		if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST,'search_date_from')) > 5)
		{
			$filter_date_parts = nbf_date::get_date_parts(nbf_common::get_param($_REQUEST,'search_date_from'), $cal_date_format);
			if (count($filter_date_parts) == 3)
			{
				$search_date_from = @nbf_common::nb_mktime(0, 0, 0, $filter_date_parts['m'], $filter_date_parts['d'], $filter_date_parts['y']);
			}
		}
		if ($search_date_from == -1)
		{
			$search_date_from = nbf_date::get_default_start_date();
		}
		$search_date_to = @nbf_common::nb_mktime(23, 59, 59, $date_parts["mon"], $date_parts["mday"], $date_parts["year"]);
		if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST,'search_date_to')) > 5)
		{
			$filter_date_parts = nbf_date::get_date_parts(nbf_common::get_param($_REQUEST,'search_date_to'), $cal_date_format);
			if (count($filter_date_parts) == 3)
			{
				$search_date_to = @nbf_common::nb_mktime(23, 59, 59, $filter_date_parts['m'], $filter_date_parts['d'], $filter_date_parts['y']);
			}
		}
	}
	else
	{
		//No date range controls will be displayed, so show all invoices
        $now_parts = nbf_common::nb_getdate();
		$search_date_from = 0;
		$search_date_to = 0;
	}
    $search_date_from = intval($search_date_from);
    $search_date_to = intval($search_date_to);
	$_REQUEST['search_date_from'] = nbf_common::nb_date($date_format, $search_date_from);
	$_REQUEST['search_date_to'] = nbf_common::nb_date($date_format, $search_date_to);

	$order_filter_join = "";
	$order_filter_where = "";
	if (nbf_common::get_param($_REQUEST,'order_id') && nbf_common::get_param($_REQUEST,'order_id') > -1)
	{
		$_POST['order_id'] = nbf_common::get_param($_REQUEST,'order_id');
		$order_filter_join = " INNER JOIN #__nbill_orders_document ON #__nbill_document.id = #__nbill_orders_document.document_id";
		$order_filter_where = " AND #__nbill_orders_document.order_id = " . intval(nbf_common::get_param($_POST,'order_id'));
	}

	$sql = "SELECT #__nbill_document.*, #__nbill_document.id AS document_id, #__nbill_vendor.id AS vendor_id, #__nbill_vendor.default_gateway
					FROM #__nbill_document INNER JOIN #__nbill_vendor ON #__nbill_document.vendor_id = #__nbill_vendor.id
					INNER JOIN #__nbill_entity ON #__nbill_document.entity_id = #__nbill_entity.id" . $order_filter_join . "
                    INNER JOIN #__nbill_entity_contact ON #__nbill_entity.id = #__nbill_entity_contact.entity_id
                    INNER JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id
					WHERE #__nbill_contact.user_id = " . nbf_cms::$interop->user->id;
	$sql .= " AND #__nbill_document.written_off = 0
                AND #__nbill_entity_contact.allow_invoices = 1
                AND (#__nbill_document.document_type = 'IN' OR #__nbill_document.document_type = 'CR') ";
    if ($search_date_from != 0 || $search_date_to != 0)
    {
        $sql .= "AND #__nbill_document.document_date >= $search_date_from AND #__nbill_document.document_date <= $search_date_to";
    }
	$sql .= $order_filter_where;
	$sql .= " GROUP BY #__nbill_document.id ORDER BY #__nbill_document.written_off, #__nbill_document.paid_in_full, #__nbill_document.partial_payment, DATE(FROM_UNIXTIME(#__nbill_document.document_date)) DESC, #__nbill_document.document_no + 0 DESC, #__nbill_document.document_no DESC";
	$nb_database->setQuery($sql);
	$invoices = $nb_database->loadObjectList();
	if (!$invoices)
	{
		$invoices = array();
	}

	$id_array = array();
	foreach ($invoices as $invoice)
	{
		$id_array[] = intval($invoice->id);
	}

    //Get the amount outstanding for any partially paid invoices
    if (count($id_array) > 0)
    {
        $sql = "SELECT document_id, SUM(gross_amount) AS gross_paid FROM #__nbill_document_transaction
                WHERE document_id IN (" . implode(",", $id_array) . ")
                GROUP BY document_id";
        $nb_database->setQuery($sql);
        $transactions = $nb_database->loadObjectList();
        for ($i=0; $i<count($invoices); $i++)
        {
            foreach ($transactions as $transaction)
            {
                if ($transaction->document_id == $invoices[$i]->id)
                {
                    $invoices[$i]->total_outstanding = float_subtract($invoices[$i]->total_gross, $transaction->gross_paid);
                    /*if ($invoices[$i]->total_gross <= 0)
                    {
                        $invoices[$i] = null; //Overpaid, or paid in full, but not marked as paid!
                    }*/
                }
            }
            if ($invoices[$i] && !isset($invoices[$i]->total_outstanding))
            {
                $invoices[$i]->total_outstanding = $invoices[$i]->total_gross;
            }

        }
    }
    //I don't think we should be removing any invoices, even if they are overpaid!
    //$invoices = array_filter($invoices); //Remove any that should not be there

	//Get the first item's description
    $first_product_description = false;
    if (count($id_array) > 0)
    {
        $sql = "SELECT id, vendor_id, document_id, product_description, section_name FROM #__nbill_document_items WHERE
                            document_id IN (" . implode(",", $id_array) . ")
                            ORDER BY document_id, ordering, id";
        $nb_database->setQuery($sql);
        $first_product_description = $nb_database->loadObjectList();
    }
    if (!$first_product_description)
    {
        $first_product_description = array();
    }
    for ($i=0; $i<count($first_product_description); $i++)
    {
        $product_name = null;
        $sql = "SELECT id, name FROM #__nbill_product WHERE name = '" . $nb_database->getEscaped($first_product_description[$i]->product_description) . "'";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($product_name);
        if ($product_name)
        {
            $first_product_description[$i]->product_description = $product_name->name;
        }
        else
        {
            //Check for first part of invoice description matching a full product name
            $sql = "SELECT product_code, name FROM #__nbill_product WHERE vendor_id = " . $first_product_description[$i]->vendor_id . " LIMIT 500";
            $nb_database->setQuery($sql);
            $products = $nb_database->loadObjectList();
            foreach ($products as $product)
            {
                if (nbf_common::nb_substr($first_product_description[$i]->product_description, 0, nbf_common::nb_strlen($product->name)) == nbf_common::nb_substr($product->name, 0, nbf_common::nb_strlen($product->name)) ||
                    nbf_common::nb_substr($first_product_description[$i]->product_description, 0, nbf_common::nb_strlen($product->name) + nbf_common::nb_strlen($product->product_code) + 3) == nbf_common::nb_substr($product->product_code . " - " . $product->name, 0, nbf_common::nb_strlen($product->name) + nbf_common::nb_strlen($product->product_code) + 3))
                {
                    $sql = "SELECT id, name AS new_name FROM #__nbill_product WHERE `name` = '" . $product->name . "'"; //Have to use an alias as of JF 1.0.4
                    $nb_database->setQuery($sql);
                    $nb_database->loadObject($product_name);
                    if ($product_name)
                    {
                        $first_product_description[$i]->product_description = str_replace($product->name, $product_name->new_name, $first_product_description[$i]->product_description);
                    }
                    break;
                }
            }
        }
    }

    $orders = array();
    

    foreach ($invoices as &$invoice)
    {
        $invoice->total_net = nbf_common::convertValueToCurrencyObject($invoice->total_net, $invoice->currency);
        $invoice->total_gross = nbf_common::convertValueToCurrencyObject($invoice->total_gross, $invoice->currency);
        $invoice->total_outstanding = nbf_common::convertValueToCurrencyObject($invoice->total_outstanding, $invoice->currency);
    }
    unset($invoice);

	nBillFrontEndInvoices::show_invoices($invoices, $first_product_description, $date_format, $orders);
}

function pay_invoice()
{
    $nb_database = nbf_cms::$interop->database;
	nbf_common::load_language("gateway");
    nbf_common::load_language("xref");
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");

	$document_ids = array();
	if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST, 'invoice_ids')) > 0)
	{
		$document_ids = explode(",", nbf_common::get_param($_REQUEST, 'invoice_ids'));
	}
	if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST,'invoice_id')) > 0)
	{
		$document_ids = array_merge($document_ids, explode(",", nbf_common::get_param($_REQUEST, 'invoice_id')));
	}
    if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST, 'document_ids')) > 0)
    {
        $document_ids = array_merge($document_ids, explode(",", nbf_common::get_param($_REQUEST, 'document_ids')));
    }
    if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST,'document_id')) > 0)
    {
        $document_ids = array_merge($document_ids, explode(",", nbf_common::get_param($_REQUEST, 'document_id')));
    }
    $document_ids = array_unique($document_ids);

	//Sanitise
    for ($i=0; $i<count($document_ids); $i++)
    {
        $document_ids[$i] = intval(trim($document_ids[$i]));
    }

	if (count($document_ids) > 0)
	{
        $orders = array();
        $document_no = "";
        $invoice_details = array();
        $gateways = array();
        $currency = "";
        $vendor_id = 0;
        $payment_plan_id = null;
        $billing_data = array();
        $gateway = nbf_common::get_param($_REQUEST, 'payment_gateway', '');
		$use_default_gateway = false;
        $select_gateway = true;
        $tax_rates = array();
        $tax_amounts = array();
        $discounts_present = false;

        //Load default gateway
        $sql = "SELECT default_gateway FROM #__nbill_vendor INNER JOIN #__nbill_document ON #__nbill_document.vendor_id = #__nbill_vendor.id WHERE #__nbill_document.id = " . $document_ids[0];
        $nb_database->setQuery($sql);
        $default_gateway = $nb_database->loadResult();

		if (nbf_frontend::get_display_option("gateway_choice_invoice"))
		{
			//Load the payment gateways
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
            $gateways = nbf_xref::load_xref("[gateway_list]", true, false);
            if (count($gateways) > 1)
			{
				$select_gateway = true;
			}
			else
			{
                if (count($gateways) == 1)
                {
                    if (!$gateway)
                    {
                        $gateway = $gateways[0]->code;
                    }
                    $select_gateway = false;
                }
                else
                {
					$use_default_gateway = true;
                    $select_gateway = false;
                }
			}
		}
		else
		{
			$use_default_gateway = true;
		}

        if ($gateway)
        {
            $default_gateway = $gateway;
        }

        $entity_id = 0;
        //Work out the breakdown of what we are charging for
        foreach ($document_ids as $document_id)
		{
			$document_id = intval(trim($document_id));

			//Check this user is allowed access to this invoice
			$sql = "SELECT #__nbill_document.*, #__nbill_document.id AS document_id, #__nbill_entity.id AS pk_client,
                            #__nbill_entity.country, #__nbill_xref_eu_country_codes.id, #__nbill_xref_eu_country_codes.code AS in_eu,
							TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS contact_name, #__nbill_entity.address_1, #__nbill_entity.address_2, #__nbill_entity.address_3,
							#__nbill_entity.town, #__nbill_entity.state, #__nbill_entity.postcode, #__nbill_contact.telephone,
							#__nbill_entity.company_name, #__nbill_entity.tax_zone, #__nbill_contact.email_address,
                            #__nbill_vendor.vendor_country
                            FROM #__nbill_document
                            LEFT JOIN #__nbill_entity ON #__nbill_document.entity_id = #__nbill_entity.id
                            LEFT JOIN #__nbill_entity_contact ON #__nbill_entity.id = #__nbill_entity_contact.entity_id AND #__nbill_entity_contact.allow_invoices = 1
                            LEFT JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id
							LEFT JOIN #__nbill_vendor ON #__nbill_document.vendor_id = #__nbill_vendor.id
							LEFT JOIN #__nbill_xref_eu_country_codes ON #__nbill_entity.country = #__nbill_xref_eu_country_codes.code
							AND #__nbill_entity.country != #__nbill_vendor.vendor_country
							WHERE ";
			if (nbf_frontend::get_display_option("login_to_pay_invoice"))
			{
				$sql .= "#__nbill_contact.user_id = " . nbf_cms::$interop->user->id . " AND ";
			}
			$sql .=  "#__nbill_document.id = $document_id AND #__nbill_document.document_type = 'IN'";
            $invoice = null;
			$nb_database->setQuery($sql);
			$nb_database->loadObject($invoice);

            if ($invoice)
            {
                $entity_id = $invoice->entity_id;

                //Make sure the currency and payment plan is consistent
                if (nbf_common::nb_strlen($currency) > 0)
                {
                    if ($invoice->currency != $currency)
                    {
                        //Cannot pay mixed currencies in one go!
                        nBillFrontEndInvoices::show_message(NBILL_INVOICE_CURRENCY_MISMATCH, true);
                        return;
                    }
                }
                if ($payment_plan_id !== null)
                {
                    if ($invoice->payment_plan_id != $payment_plan_id)
                    {
                        //Cannot pay using mixed payment plans in one go - default to payment in full for everything
                        $payment_plan_id = 0;
                    }
                }
                else
                {
                    $payment_plan_id = $invoice->payment_plan_id;
                }
                $currency = $invoice->currency;
                $vendor_id = $invoice->vendor_id;

                //Check we don't already have installments set up
                if ($invoice->partial_payment && $invoice->payment_plan_id)
                {
                    $sql = "SELECT plan_type FROM #__nbill_payment_plans WHERE id = " . intval($invoice->payment_plan_id);
                    $nb_database->setQuery($sql);
                    switch ($nb_database->loadResult())
                    {
                        case 'BB': //Installments
                        case 'DD': //Deposit + installments
                            if ($invoice->gateway_txn_id)
                            {
                                nBillFrontEndInvoices::show_message(sprintf(NBILL_INVOICE_INSTALLMENTS_ALREADY_RUNNING, $invoice->document_no), true);
                                return;
                            }
                            break;
                        case 'CC': //Deposit plus final payment, but deposit has been paid already, so demand full amount
                            $payment_plan_id = null;
                            break;
                        case 'DX': //Deposit then user controlled, but deposit has been paid already, so allow choice of amount
                            $payment_plan_id = -1;
                            break;
                    }
                }

                //Load the amount outstanding
                $sql = "SELECT document_id, SUM(gross_amount) AS gross_paid FROM #__nbill_document_transaction
                        WHERE document_id = $document_id GROUP BY document_id";
                $nb_database->setQuery($sql);
                $transactions = $nb_database->loadObjectList();
                if ($transactions)
                {
                    foreach ($transactions as $transaction)
                    {
                        if ($transaction->document_id == $invoice->document_id)
                        {
                            $invoice->total_outstanding = float_subtract($invoice->total_gross, $transaction->gross_paid);
                            if ($invoice->total_gross <= 0)
                            {
                                $invoice = null; //Overpaid, or paid in full, but not marked as paid!
                            }
                        }
                    }
                }
                if (!isset($invoice->total_outstanding))
                {
                    $invoice->total_outstanding = $invoice->total_gross;
                }
            }

            if ($invoice)
            {
                if ($invoice->paid_in_full)
                {
                    @nBillFrontEndInvoices::invoice_already_paid($document_id, $invoice->document_no);
                    return;
                }

                if (nbf_common::nb_strlen($document_no) > 0)
                {
                    $document_no .= ", ";
                }
                $document_no .= $invoice->document_no;

                $link_task = nbf_frontend::get_display_option('pdf') && !nbf_frontend::get_display_option('html_preview') && nbf_common::pdf_writer_available() ? 'pdf' : 'print';
                $link = htmlentities(nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . "&action=invoices&task=" . $link_task . "&cid=" . $invoice->document_id . nbf_cms::$interop->site_page_suffix);
                $invoice_key = '';
                if (nbf_frontend::get_display_option('html_preview') || $link_task == 'pdf' && (!nbf_frontend::get_display_option('login_to_pay_invoice') || @nbf_cms::$interop->user->id)) {
                    $invoice_key = "<a target=\"_blank\" href=\"$link\" onclick=\"window.open('$link', '" . uniqid() . "', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;\">" . $invoice->document_no . "</a>";
                } else {
                    $invoice_key = $invoice->document_no;
                }

			    $invoice_details[$invoice_key] = $invoice->total_outstanding;

			    if ($invoice && $invoice->total_outstanding > 0)
			    {
				    if ($use_default_gateway)
				    {
					    //Check we have a default payment gateway to hand over to
					    $sql = "SELECT default_gateway FROM #__nbill_vendor WHERE id = " . intval($invoice->vendor_id);
					    $nb_database->setQuery($sql);
					    $gateway = $nb_database->loadResult();
				    }

				    if ($select_gateway || nbf_common::nb_strlen($gateway) > 0)
				    {
					    if ($select_gateway && !$default_gateway && count($gateways) > 0)
                        {
                            //Just take the first one
                            $default_gateway = $gateways[0]->code;
                        }
                        $dummy = null;

                        $discounts_present = false;
                        
                        nbf_payment::prepare_for_invoice_payment($invoice, ($gateway ? $gateway : $default_gateway), $orders, $dummy, !$discounts_present);

					    //Client data (name, address)
                        $billing_name = $invoice->contact_name; //More likely to be able to split this into first and last names
                        if (nbf_common::nb_strlen($billing_name) == 0)
                        {
                            $billing_name = $invoice->billing_name;
                        }
                        if (nbf_common::nb_strlen($billing_name) == 0)
                        {
                            $billing_name = nbf_cms::$interop->user->name;
                        }
                        $billing_data['first_name'] = nbf_common::nb_strlen($billing_name) > 0 && nbf_common::nb_strpos($billing_name, " ") !== false ? nbf_common::nb_substr($billing_name, 0, nbf_common::nb_strpos($billing_name, " ")) : "";
                        $billing_data['last_name'] = nbf_common::nb_strpos($billing_name, " ") !== false ? nbf_common::nb_substr($billing_name, nbf_common::nb_strpos($billing_name, " ") + 1) : $billing_name;
                        $billing_data['address_1'] = $invoice->address_1;
                        $billing_data['address_2'] = $invoice->address_2;
                        $billing_data['address_3'] = $invoice->address_3;
                        $billing_data['town'] = $invoice->town;
                        $billing_data['state'] = $invoice->state;
                        $billing_data['postcode'] = $invoice->postcode;
                        $billing_data['telephone'] = $invoice->telephone;
                        $billing_data['company_name'] = $invoice->billing_name ? $invoice->billing_name : $invoice->company_name;
                        $billing_data['country'] = $invoice->country;
                        $billing_data['email_address'] = $invoice->email_address;
                        $billing_data['username'] = nbf_cms::$interop->user->username;
                        $billing_data['full_invoice_address'] = $invoice->billing_address;
				    }
				    else
				    {
					    //No default gateway defined!
                        nBillFrontEndInvoices::show_message(NBILL_ERR_GATEWAY_NOT_FOUND, true);
					    return;
				    }
			    }
            }
            else
            {
                nBillFrontEndInvoices::show_message(NBILL_CANNOT_PAY_INVOICE_ONLINE, true);
                return;
            }
		}

        //Check for pending payments
        if (!nbf_common::get_param($_REQUEST, 'nbill_submit_invoice_payment_summary') && !nbf_common::get_param($_REQUEST, 'nbill_pending_payment_confirm'))
        {
            $g_tx_id_array = array();
            foreach ($document_ids as $document_id)
            {
                $document_id = intval(trim($document_id));
                $sql = "SELECT id, document_ids, payment_pending_until FROM #__nbill_gateway_tx WHERE document_ids LIKE '%" . $document_id . "%' ORDER BY id DESC LIMIT 10";
                $nb_database->setQuery($sql);
                $potential_txs = $nb_database->loadObjectList();
                foreach ($potential_txs as $potential_tx)
                {
                    if ($potential_tx->document_ids)
                    {
                        $this_invoice_id_array = explode(",", $potential_tx->document_ids);
                        if (array_search($document_id, $this_invoice_id_array) !== false)
                        {
                            if ($potential_tx->payment_pending_until && $potential_tx->payment_pending_until > nbf_common::nb_time())
                            {
                                $warning_message = sprintf(NBILL_PENDING_PAYMENT_WARNING, NBILL_RECORD_INVOICE, nbf_common::nb_date(nbf_common::get_date_format(), $potential_tx->payment_pending_until));
                                $params = array();
                                $params['nbill_pending_payment_confirm'] = '1';
                                $cid = 0;
                                foreach ($_REQUEST as $key=>$value)
                                {
                                    switch ($key)
                                    {
                                        case 'id':
                                        case 'cid':
                                            $cid = $value;
                                            break;
                                        case 'action':
                                        case 'task':
                                            break;
                                        default:
                                            $params[$key] = $value;
                                            break;
                                    }
                                }
                                $cid = $cid ? $cid : $document_id;
                                include_once(nbf_cms::$interop->nbill_fe_base_path . "/html/main.html.php");
                                nBillFrontEndMain::show_warning_message('invoices', nbf_common::get_param($_REQUEST, 'task'), $cid, $warning_message, nbf_cms::$interop->site_page_prefix . "&action=invoices" . nbf_cms::$interop->site_page_suffix, $params);
                                return;
                            }
                        }
                    }
                }
            }
        }

        //Calculate totals and prepare summary
        $standard_totals = new nbf_totals();
        $regular_totals = new nbf_totals();
        $actual_totals = new nbf_totals();
        $shipping_service = "";
        $normal_tax_rate = 0;
        $shipping_tax_rate = 0;
        nbf_payment::calculate_totals($orders, $currency, @$billing_data['country'], 0, '', $standard_totals, $regular_totals, $actual_totals,
                                    $normal_tax_rate, $shipping_tax_rate, $shipping_service, false, null, true,
                                    $payment_plan_id, null, null, true);
        if ($standard_totals->total_gross == 0 && $regular_totals->total_gross == 0)
        {
            //If there is a 100% discount, treat it as paid, otherwise, complain that it has already been paid
            $amount_discounted = false;
            foreach ($orders as $order)
            {
                if ($order['discount_id'] > 0 && $order['net_price'] < 0)
                {
                    $amount_discounted = true;
                }
            }
            if ($amount_discounted)
            {
                //Add the discount to the invoice and mark it as paid
                $payment_frequency = 'AA';
                $abort = false;
                $suppress_payment = false;
                $pending_order_id = 0;
                $form_id = 0;
                $auto_renew = true;
                $turn_on_auto_renew = false;
                $expiry_date = 0;
                $relating_to = "";
                foreach ($orders as &$order)
                {
                    $order = str_replace("<br />", "; ", $order);
                }
                nbf_common::load_language("invoices");
                $g_tx_id = nbf_payment::prepare_for_payment($gateway, $suppress_payment, $standard_totals, $regular_totals, $orders, $normal_tax_rate,
                            $shipping_tax_rate, $pending_order_id, $form_id, $vendor_id, $auto_renew, $payment_frequency, $currency, $abort,
                            $expiry_date, $shipping_service, $relating_to, $no_of_payments, $billing_data, $document_no, $turn_on_auto_renew,
                            $document_ids, $tax_rates, $tax_amounts, null, null, $entity_id);
                nbf_payment::gateway_processing($g_tx_id, '0.00', $currency, $warning_message, $error_message, $invoice->billing_name, '', NBILL_INVOICE_DISCOUNTED_ZERO_PAYMENT_NOTES, 'XX');
                nBillFrontEndInvoices::show_message(NBILL_INVOICE_DISCOUNTED_ZERO_PAYMENT, true);
                return;
            }
            else
            {
                @nBillFrontEndInvoices::invoice_already_paid($document_id, $invoice->document_no);
                return;
            }
        }

        $payment_plan_type = "AA";
        $payment_plan_name = NBILL_UP_FRONT;
        $installment_frequency = "AA";
        $no_of_installments = 1;
        

        $summary_field = new stdClass();
        $summary_field->field_type = 'PP';
        $summary_field->form_id = "invoice";
        $summary_field->id = 'invoice_summary';
        $dummy = new stdClass();
        $invoice_summary_total = "";
        $invoice_total_summary_plain = "";
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.field.class.php");
        $summary_control = nbf_form_fields::create_control($summary_field, $dummy, null, null);
        $summary_control->order_total_summary_table("invoice", $orders, $currency, 'AA', $standard_totals, $regular_totals,
                $actual_totals, $invoice_summary_total, $invoice_summary_total_plain, $payment_plan_type,
                $payment_plan_name, $installment_frequency, $no_of_installments);

        if (!nbf_common::get_param($_REQUEST, 'nbill_submit_invoice_payment_summary'))
        {
            //Check whether to prompt for a voucher code
            $voucher_available = false;
            
            nBillFrontEndInvoices::show_invoice_payment_summary($document_ids, $select_gateway, $gateways, $default_gateway, $invoice_details, $invoice_summary_total, $voucher_available);
        }
        else
        {
            //If payment plan includes installments, find out how many payments are needed and adjust totals if necessary
            $payment_frequency = 'AA';
            
            $abort = false;
            $suppress_payment = false;
            $pending_order_id = 0;
            $form_id = 0;
            $auto_renew = true;
            $turn_on_auto_renew = false;
            $expiry_date = 0;
            $relating_to = "";
            foreach ($orders as &$order)
            {
                $order = str_replace("<br />", "; ", $order);
            }
            nbf_common::load_language("invoices");
            $g_tx_id = nbf_payment::prepare_for_payment($gateway, $suppress_payment, $standard_totals, $regular_totals, $orders, $normal_tax_rate,
                        $shipping_tax_rate, $pending_order_id, $form_id, $vendor_id, $auto_renew, $payment_frequency, $currency, $abort,
                        $expiry_date, $shipping_service, $relating_to, $no_of_payments, $billing_data, $document_no, $turn_on_auto_renew,
                        $document_ids, $tax_rates, $tax_amounts, null, null, $entity_id);
            if ($gateway == "-1" || $gateway == "offline" || $gateway == NBILL_OFFLINE)
            {
                //Show payment instructions
                $sql = "SELECT invoice_offline_pay_inst FROM #__nbill_vendor WHERE id = $vendor_id";
                $nb_database->setQuery($sql);
                $pay_inst = $nb_database->loadResult();
                if (strlen($pay_inst) == 0)
                {
                    $pay_inst = NBILL_DEFAULT_INVOICE_OFFLINE_PAY_INST;
                }
                nBillFrontEndInvoices::show_message($pay_inst, true);
            }
            else
            {
                nbf_payment::hand_over_to_gateway($g_tx_id, $gateway, $suppress_payment, $standard_totals, $regular_totals, $orders,
                            $vendor_id, $auto_renew, $payment_frequency, $currency, $abort, $expiry_date, $shipping_service, $relating_to,
                            $no_of_payments, $billing_data, $document_no, $turn_on_auto_renew, $document_ids, $tax_rates, $tax_amounts);
                if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
                {
                    nBillFrontEndInvoices::show_message(nbf_globals::$message, true);
                }
            }
        }
	}
	else
	{
		nBillFrontEndInvoices::show_message(NBILL_CANNOT_PAY_INVOICE_ONLINE, true);
	}
}