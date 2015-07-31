<?php
/**
* Server-side processing for Paypal AJAX functions
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
*
* @access private
**/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function process_invoice()
{
    if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal." . nbf_cms::$interop->language . ".php")) {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal." . nbf_cms::$interop->language . ".php");
    } else if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal.en-GB.php")) {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal.en-GB.php");
    }

    $nb_database = nbf_cms::$interop->database;
    $document_id = intval(nbf_common::get_param($_REQUEST, 'invoice_id'));
    $entity_id = intval(nbf_common::get_param($_REQUEST, 'entity_id'));
    $amount = format_number(nbf_common::get_param($_REQUEST, 'amount'));

    //Load invoice and make sure it really is unpaid
    $document = null;
    $sql = "SELECT #__nbill_document.*, #__nbill_document.id AS document_id, #__nbill_xref_eu_country_codes.code AS in_eu,
                #__nbill_vendor.vendor_country
                FROM #__nbill_document
                INNER JOIN #__nbill_vendor ON #__nbill_document.vendor_id = #__nbill_vendor.id
                LEFT JOIN #__nbill_xref_eu_country_codes ON #__nbill_document.billing_country = #__nbill_xref_eu_country_codes.code
                WHERE #__nbill_document.id = $document_id";
    $nb_database->setQuery($sql);
    $nb_database->loadObject($document);
    if (!$document)
    {
        echo $document_id . '#!#failure#!#Invoice not found (ID: ' . $document_id . ')';
        return;
    }
    if ($document->paid_in_full)
    {
        echo $document_id . '#!#failure#!#Invoice ' . $document->document_no . ' is already marked as paid.';
        return;
    }
    if ($document->total_gross < $amount)
    {
        echo $document_id . '#!#failure#!#The total of invoice ' . $document->document_no . ' is less than the amount you are trying to charge.';
        return;
    }
    $document->total_outstanding = $amount;

    //Make sure it does not already have a Paypal bill (in case of page refresh)
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
                    //Already awaiting payment
                    echo $document_id . '#!#aborted#!#';
                    return;
                }
            }
        }
    }

    try
    {
        //There shouldn't normally be more than one preauth resource, but if there is, try each of them in descending order of date created (ie most recent first)
        $sql = "SELECT resource_id, payer_email FROM #__nbill_paypal_preapp_resources
                WHERE type = 'preapp'
                AND entity_id = $entity_id
                AND status = 'active'
                GROUP BY entity_id
                ORDER BY created_date DESC";
        $nb_database->setQuery($sql);
        $preapp_resources = $nb_database->loadObjectList();
        if (!$preapp_resources)
        {
            echo $document_id . '#!#failure#!#Pre-Authorisation resource not found.';
        }
        $bill = null;
        $first_error = '';
        foreach ($preapp_resources as $preapp_resource)
        {
            if ($preapp_resource->resource_id)
            {
                //Find out if there any discounts have already been added to this invoice
                $discounts_present = false;
                $sql = "SELECT product_code FROM #__nbill_document_items WHERE document_id = " . $document_id . " ORDER BY document_id, ordering, id";
                $nb_database->setQuery($sql);
                $invoice_items = $nb_database->loadObjectList();
                foreach ($invoice_items as $item)
                {
                    if (nbf_common::nb_substr($item->product_code, 0, 3) == "[d=")
                    {
                        $discounts_present = true;
                        break;
                    }
                }

                //Prepare gateway transaction
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.frontend.class.php");
                $orders = array();
                $dummy = null;
                nbf_payment::prepare_for_invoice_payment($document, 'paypal', $orders, $dummy, !$discounts_present);

                $standard_totals = new nbf_totals();
                $regular_totals = new nbf_totals();
                $actual_totals = new nbf_totals();
                $tax_rates = array();
                $tax_amounts = array();
                $shipping_service = "";
                $normal_tax_rate = 0;
                $shipping_tax_rate = 0;
                $abort = false;
                $currency = $document->currency;

                nbf_payment::calculate_totals($orders, $currency, $document->billing_country, 0, '', $standard_totals, $regular_totals, $actual_totals,
                                            $normal_tax_rate, $shipping_tax_rate, $shipping_service, false, null, true,
                                            0, null, null, true);
                if ($standard_totals->total_gross == 0 && $regular_totals->total_gross == 0)
                {
                    echo $document_id . '#!#failure#!#Amount outstanding on this invoice appears to be zero!';
                    return;
                }
                else
                {
                    if ($standard_totals->total_gross >= $document->total_outstanding)
                    {
                        $standard_totals->total_gross = $document->total_outstanding;
                    }
                    else
                    {
                        echo $document_id . '#!#failure#!#Amount outstanding on this invoice is less than the amount you are trying to charge.';
                        return;
                    }
                }

                //Parameters need to be passed by reference
                $gateway = 'paypal';
                $suppress = false;
                $pending_order_id = 0;
                $form_id = 0;
                $auto_renew = false;
                $pay_freq = 'AA';
                $expiry = 0;
                $relating_to = '';
                $no_of_payments = 1;
                $billing_data = null;
                $doc_id_array = array($document_id);
                $g_tx_id = nbf_payment::prepare_for_payment($gateway, $suppress, $standard_totals, $regular_totals, $orders, $normal_tax_rate,
                        $shipping_tax_rate, $pending_order_id, $form_id, $document->vendor_id, $auto_renew, $pay_freq, $currency, $abort,
                        $expiry, $shipping_service, $relating_to, $no_of_payments, $billing_data, $document->document_no, $auto_renew,
                        $doc_id_array, $tax_rates, $tax_amounts, null, null, $entity_id);
                if (!$g_tx_id)
                {
                    echo $document_id . '#!#failure#!#Unable to save gateway transaction data.';
                    return;
                }

                //Attempt to create a bill with Paypal
                $pp_mapper = new nBillPaypalMapper(nbf_cms::$interop->database);
                $pp_api = new nBillPaypalApi($_REQUEST, $pp_mapper);

                $payment_details = array();
                $payment_details['amount'] = $amount;
                $payment_details['invoice_no'] = $document->document_no;
                $payment_details['currency'] = $document->currency;
                $payment_details['pre_approval_resource_id'] = $preapp_resource->resource_id;
                $payment_details['payer_email'] = $preapp_resource->payer_email;
                $payment_details['transaction_id'] = substr(md5(nbf_cms::$interop->live_site), 0, 10) . '_' . $g_tx_id;
                $payment_details['g_tx_id'] = $g_tx_id;
                $payment_details['document_id'] = $document->document_id;
                $pp_api->executePayment($payment_details);
            }
        }
        /*if (!$bill)  {
            echo $document_id . '#!#failure#!#' . $first_error;
            return;
        } else {
            echo $document_id . '#!#success#!#';
            return;
        }*/
    } catch (Exception $ex) {
        echo $document_id . '#!#failure#!#' . sprintf(NBILL_PAYPAL_API_ERR, str_replace(nbf_cms::$interop->site_base_path, "", $ex->getFile()) . ":line " . $ex->getLine() . ": " . $ex->getMessage());
        return;
    }
}