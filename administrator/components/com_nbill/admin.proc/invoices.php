<?php
/**
* Main processing file for invoices
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');switch ($task)
{
	case "silent":
    case "on_hold":
    case "quoted":
    case "accepted":
    case "apply_and_generate":
    case "generate":
    case "paid_offline":
    case "offline_income_recorded";
		//No action required
		break;
    case "generated-view":
        showInvoices();
        break;
	case "new":
	    $cid[0] = null;
        //fall through
    case "edit":
		editInvoice($cid[0], intval(nbf_common::get_param($_POST, 'use_posted_values')));
		break;
	case "lookup_sku":
    case "do_lookup_sku":
		lookup_sku();
		editInvoice($id, true);
		break;
    case "product_list":
        show_product_list();
        break;
	case "add_item":
		addItem();
		editInvoice($id, true);
		break;
	case "remove_item":
		removeItem();
		editInvoice($id, true);
		break;
    case "move_up":
        $top_item = moveItem("up");
        editInvoice($id, true, false, true);
        break;
    case "move_down":
        $top_item = moveItem("down");
        editInvoice($id, true, false, true);
        break;
	case "client_changed":
		editInvoice($id, true, true);
		break;
    
	case "apply":
        if (saveInvoice($id))
        {
		    if (!$id)
		    {
			    $id = intval(nbf_common::get_param($_POST,'id'));
		    }
		    editInvoice($id);
        }
		break;
	case "save":
		if (saveInvoice($id))
        {
		    if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'return')) > 0)
		    {
			    nbf_common::redirect(base64_decode(nbf_common::get_param($_REQUEST,'return')));
			    break;
		    }
		    showInvoices();
        }
		break;
    case "save_copy":
        saveCopy($id);
        $task = "";
        $_REQUEST['task'] = $task;
        showInvoices();
        break;
	case "remove":
	case "delete":
		deleteInvoices($cid);
		showInvoices();
		break;
	case "printpreview":
		printInvoices();
		showInvoices();
		break;
	case "printpreviewpopup":
		$items = explode(",", nbf_common::get_param($_REQUEST,'items'));
        for($i=0;$i<count($items);$i++){$items[$i] = intval($items[$i]);}
		printPreviewPopup($items);
		break;
    case "deliverynotepopup":
        $items = explode(",", nbf_common::get_param($_REQUEST,'items'));
        for($i=0;$i<count($items);$i++){$items[$i] = intval($items[$i]);}
        printPreviewPopup($items, false, false, '', '', false, false, true);
        break;
	case "pdfpopup":
        $items = explode(",", nbf_common::get_param($_REQUEST,'items'));
        for($i=0;$i<count($items);$i++){$items[$i] = intval($items[$i]);}
		printPDFPopup($items);
		break;
    case "cancel":
		if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'return')) > 0)
		{
			nbf_common::redirect(base64_decode(nbf_common::get_param($_REQUEST,'return')));
			break;
		}
		nbf_globals::$message = "";
		showInvoices();
		break;
    
	case "emailinvoice":
		showEMailInvoice(nbf_common::get_param($_REQUEST, 'document_id'));
		break;
	case "show":
        showInvoices();
        break;
    case "view_uploaded_file":
        show_uploaded_document_file();
        break;
	default:
		if (substr($task, 0, 9) == "generate-")
		{
			$overridedate = substr($task, 9);
			if (nbf_common::nb_strlen($overridedate) == 0 || $overridedate == "null")
			{
				nbf_globals::$message = NBILL_NO_ACTION_TAKEN;
				showInvoices();
			}
			else
			{
				$date_int = 0;
				$date_parts = explode("/", $overridedate);
				if (count($date_parts) == 3)
				{
					$date_int = nbf_common::nb_mktime(23, 59, 59, $date_parts[1], $date_parts[2], $date_parts[0]);
				}
				if ($date_int > 0)
				{
					include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.invoice.generator.class.php");
					nbf_generator::generate_invoices(array(), nbf_common::get_param($_POST,'vendor_filter'), false, $document_ids, $date_int);
					nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . "&action=invoices&task=generated-view&message=" . nbf_globals::$message);
				}
				else
				{
					nbf_globals::$message = sprintf(NBILL_INVALID_DATE_ENTERED, nbf_common::get_date_format(true));
					showInvoices();
				}
			}
		}
		else
		{
			nbf_globals::$message = "";
			showInvoices();
		}
		break;
}

function showInvoices($order_id = null)
{
	$nb_database = nbf_cms::$interop->database;

    $doc_type = "IV";
    switch (nbf_common::nb_strtoupper(nbf_common::get_param($_REQUEST, 'action')))
    {
        case "CREDITS":
            $doc_type = "CR";
            break;
        case "QUOTES":
            $doc_type = "QU";
            break;
    }

    //If we are doing a mass write-off or mass payment, get on with it
    if (nbf_common::get_param($_POST, 'multi_invoice_update_submit'))
    {
        $invoice_ids = nbf_common::get_param($_REQUEST, 'cid');
        switch (nbf_common::get_param($_REQUEST, 'multi_invoice_update'))
        {
            case "WO":
                //Do the do
                $sql = "UPDATE #__nbill_document SET written_off = 1, date_written_off = " . nbf_common::nb_time() . " WHERE id IN (" . implode(",", $invoice_ids) . ")";
                $nb_database->setQuery($sql);
                $nb_database->query();
                $no_of_updates = $nb_database->getAffectedRows();
                //Report the results
                nbf_globals::$message = sprintf(NBILL_MULTI_INVOICE_COMPLETE, $no_of_updates);
                break;
            case "PAID":
                nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . "&action=income&task=edit&document_id=" . implode(",", $invoice_ids));
                exit;
            case "PAID_MULTIPLE":
                nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . "&action=income&task=multi_invoice&document_id=" . implode(",", $invoice_ids) . "&return=" . nbf_common::get_param($_REQUEST, 'multi_paid_return_url'));
                exit;
        }
    }

	//Load Vendors
	$sql = "SELECT id, vendor_name FROM #__nbill_vendor ORDER BY id";
	$nb_database->setQuery($sql);
	$vendors = $nb_database->loadObjectList();

	//Work out date range
    if (nbf_common::get_param($_REQUEST, 'show_reset'))
    {
        $_REQUEST['search_date_from'] = null;
        $_REQUEST['search_date_to'] = null;
        unset($_REQUEST['show_all']);
    }
	$date_format = nbf_common::get_date_format();
	$cal_date_format = nbf_common::get_date_format(true);
	$date_parts = nbf_common::nb_getdate(time());
    if (nbf_common::get_param($_REQUEST, 'show_all'))
    {
        $search_date_from = 0;
        $search_date_to = nbf_common::nb_mktime(23, 59, 59, 12, 31, 2037); //Largest value allowed for a date using a 32-bit integer is 18th Jan 2038
    }
    else
    {
	    $search_date_from = -1;
	    if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST,'search_date_from')) > 5)
	    {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
		    $filter_date_parts = nbf_date::get_date_parts(nbf_common::get_param($_REQUEST,'search_date_from'), $cal_date_format);
		    if (count($filter_date_parts) == 3)
		    {
			    $search_date_from = nbf_common::nb_mktime($filter_date_parts['y']==1970&&$filter_date_parts['m']==1&&$filter_date_parts['d']==1 ? 1 : 0, 0, 0, $filter_date_parts['m'], $filter_date_parts['d'], $filter_date_parts['y']);
		    }
	    }
	    if ($search_date_from == -1)
	    {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
		    $search_date_from = nbf_date::get_default_start_date();
	    }
	    $search_date_to = nbf_common::nb_mktime(23, 59, 59, $date_parts["mon"], $date_parts["mday"], $date_parts["year"]);
	    if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST,'search_date_to')) > 5)
	    {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
		    $filter_date_parts = nbf_date::get_date_parts(nbf_common::get_param($_REQUEST,'search_date_to'), $cal_date_format);
		    if (count($filter_date_parts) == 3)
		    {
			    $search_date_to = nbf_common::nb_mktime(23, 59, 59, $filter_date_parts['m'], $filter_date_parts['d'], $filter_date_parts['y']);
		    }
	    }
    }
	$_REQUEST['search_date_from'] = nbf_common::nb_date($date_format, $search_date_from);
	$_REQUEST['search_date_to'] = nbf_common::nb_date($date_format, $search_date_to);

	$nbill_no_filter = trim(nbf_common::get_param($_REQUEST,'nbill_no_search'));
	$nbill_no_filter = "%$nbill_no_filter%";
	$client_filter = trim(nbf_common::get_param($_REQUEST,'client_search'));
	$client_filter = "%$client_filter%";
	$product_filter = trim(nbf_common::get_param($_REQUEST,'description_search'));
	$product_filter = "%$product_filter%";
	if ($nbill_no_filter == "%%") {$nbill_no_filter = "";}
	if ($client_filter == "%%") {$client_filter = "";}
	if ($product_filter == "%%") {$product_filter = "";}
	$_POST['nbill_no_search'] = trim(nbf_common::get_param($_REQUEST,'nbill_no_search'));
	$_POST['client_search'] = trim(nbf_common::get_param($_REQUEST,'client_search'));
	$_POST['description_search'] = trim(nbf_common::get_param($_REQUEST,'description_search'));
    $quote_status_filter = nbf_common::get_param($_REQUEST, 'quote_status');

	$query = "SELECT count(*) FROM #__nbill_document";
    $whereclause = " WHERE ";
	$count_joins = "";
    $orders_present = false;

	if ($order_id)
	{
		$order_id = $nb_database->getEscaped($order_id);
		$count_joins .= " INNER JOIN #__nbill_orders_document ON #__nbill_document.id = #__nbill_orders_document.document_id";
		$whereclause .= "#__nbill_orders_document.order_id = " . intval($order_id);
	}
	else
	{
		if ((nbf_common::nb_strlen(nbf_globals::$vendor_filter) > 0 && nbf_globals::$vendor_filter != -999))
		{
			$whereclause .= "#__nbill_document.vendor_id = " . intval(nbf_globals::$vendor_filter);
		}
		if (nbf_common::nb_strlen($nbill_no_filter) > 2)
		{
			if (nbf_common::nb_strlen($whereclause) > 7)
			{
				$whereclause .= " AND ";
			}
			$whereclause .= " #__nbill_document.document_no LIKE '$nbill_no_filter'";
		}
		if (nbf_common::nb_strlen($client_filter) > 2)
		{
            $count_joins .= " LEFT JOIN (#__nbill_entity LEFT JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id) ON #__nbill_document.entity_id = #__nbill_entity.id ";
        	if (nbf_common::nb_strlen($whereclause) > 7)
			{
				$whereclause .= " AND ";
			}
			$whereclause .= " (#__nbill_entity.company_name LIKE '$client_filter' OR CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name) LIKE '$client_filter' OR #__nbill_document.billing_name LIKE '$client_filter' OR CONCAT(#__nbill_entity.company_name, ' (', CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name), ')') LIKE '$client_filter')";
		}
        if (intval(nbf_common::get_param($_REQUEST, 'client_id')))
        {
            if (nbf_common::nb_strlen($whereclause) > 7)
            {
                $whereclause .= " AND ";
            }
            $whereclause .= " #__nbill_document.entity_id = " . intval(nbf_common::get_param($_REQUEST, 'client_id'));
        }
		if (nbf_common::nb_strlen($product_filter) > 2)
		{
            $count_joins .= " LEFT JOIN #__nbill_document_items ON #__nbill_document.id = #__nbill_document_items.document_id ";
            if (nbf_common::nb_strlen($whereclause) > 7)
			{
				$whereclause .= " AND ";
			}
			$whereclause .= "(#__nbill_document_items.product_description LIKE '$product_filter' OR #__nbill_document_items.detailed_description LIKE '$product_filter' OR #__nbill_document_items.product_code LIKE '$product_filter' OR #__nbill_document_items.discount_description LIKE '$product_filter')";
		}
		if (nbf_common::nb_strlen($whereclause) > 7)
		{
			$whereclause .= " AND ";
		}
		if (nbf_common::get_param($_POST,'all_outstanding'))
		{
			$whereclause .= "#__nbill_document.paid_in_full != 1 AND ";
		}
        $whereclause .= "#__nbill_document.document_date >= $search_date_from AND #__nbill_document.document_date <= $search_date_to";
	}
    switch (nbf_common::get_param($_REQUEST, 'action'))
    {
        case "credits":
            $whereclause .= " AND #__nbill_document.document_type = 'CR'";
            break;
        case "quotes":
            $whereclause .= " AND #__nbill_document.document_type = 'QU'";
            if (nbf_common::nb_strlen($quote_status_filter) > 0)
            {
                $whereclause .= " AND #__nbill_document.status = '$quote_status_filter'";
            }
            break;
        default:
	        $whereclause .= " AND #__nbill_document.document_type != 'CR' AND #__nbill_document.document_type != 'QU'";
            break;
	}
	$query .= $count_joins . $whereclause;
	$nb_database->setQuery($query);
	$total = $nb_database->loadResult();

	//Add page navigation
    switch (nbf_common::get_param($_REQUEST, 'action'))
    {
        case "credits":
            $pagination = new nbf_pagination("credit", $total);
            break;
        case "quotes":
            $pagination = new nbf_pagination("quote", $total);
            break;
        default:
            $pagination = new nbf_pagination("invoice", $total);
            break;
    }

	//Load the records
    $joins = "";
	if ($order_id)
	{
		$sql = "SELECT #__nbill_document.id, #__nbill_document.vendor_id, #__nbill_document.reference, #__nbill_document.id AS document_id, #__nbill_document.document_no, #__nbill_document.entity_id,
                    #__nbill_document.billing_name, #__nbill_document.document_date, (#__nbill_document.total_net + #__nbill_document.total_shipping) AS total_net,
                    (#__nbill_document.total_tax + #__nbill_document.total_shipping_tax) AS total_tax, #__nbill_document.total_gross,
					#__nbill_document.paid_in_full, #__nbill_document.partial_payment, #__nbill_document.email_sent, #__nbill_document.written_off,
                    #__nbill_document.status, #__nbill_document.currency, #__nbill_document.document_type, #__nbill_orders_document.order_id AS order_count,
                    TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS contact_name, #__nbill_entity.company_name, #__nbill_xref_quote_status.description AS quote_status_desc,
                    COUNT(#__nbill_supporting_docs.id) AS attachment_count, #__nbill_entity.is_client, #__nbill_entity.is_supplier";
        if (nbf_common::get_param($_REQUEST, 'do_csv_download'))
        {
            $sql .= ", #__nbill_document.billing_address, #__nbill_contact.first_name, #__nbill_contact.last_name, #__nbill_contact.email_address, #__nbill_contact.telephone,
                #__nbill_contact.mobile, #__nbill_contact.fax";
        }
        $sql .= " FROM #__nbill_document ";
        $joins = "LEFT JOIN #__nbill_xref_quote_status ON #__nbill_document.status = #__nbill_xref_quote_status.code
		            LEFT JOIN #__nbill_entity ON #__nbill_document.entity_id = #__nbill_entity.id
                    LEFT JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id
                    LEFT JOIN #__nbill_supporting_docs ON #__nbill_document.id = #__nbill_supporting_docs.associated_doc_id AND #__nbill_supporting_docs.associated_doc_type = '$doc_type' ";
		if (nbf_common::nb_strlen($product_filter) > 0)
		{
			$joins .= "LEFT JOIN #__nbill_document_items ON #__nbill_document.id = #__nbill_document_items.document_id ";
		}
		$joins .= "INNER JOIN #__nbill_orders_document ON #__nbill_document.id = #__nbill_orders_document.document_id";

		$sql .= $joins . $whereclause . " GROUP BY #__nbill_document.id";
		$sql .= " ORDER BY #__nbill_document.written_off, #__nbill_document.paid_in_full, #__nbill_document.partial_payment, DATE(FROM_UNIXTIME(#__nbill_document.document_date)) DESC, #__nbill_document.document_no + 0 DESC, #__nbill_document.document_no DESC ";
        if (!nbf_common::get_param($_REQUEST, 'do_csv_download'))
        {
            $sql .= " LIMIT $pagination->list_offset, $pagination->records_per_page";
        }
	}
	else
	{
		$sql = "SELECT #__nbill_document.id, #__nbill_document.vendor_id, #__nbill_document.reference, #__nbill_document.id AS document_id, #__nbill_document.document_no, #__nbill_document.entity_id,
                    #__nbill_document.billing_name, #__nbill_document.document_date, (#__nbill_document.total_net + #__nbill_document.total_shipping) AS total_net,
                    (#__nbill_document.total_tax + #__nbill_document.total_shipping_tax) AS total_tax, #__nbill_document.total_gross,
					#__nbill_document.paid_in_full, #__nbill_document.partial_payment, #__nbill_document.email_sent, #__nbill_document.written_off,
                    #__nbill_document.status, #__nbill_document.currency, #__nbill_document.document_type, COUNT(#__nbill_orders_document.order_id) AS order_count,
                    TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS contact_name, #__nbill_entity.company_name, #__nbill_xref_quote_status.description AS quote_status_desc,
                    COUNT(#__nbill_supporting_docs.id) AS attachment_count, #__nbill_entity.is_client, #__nbill_entity.is_supplier";
        if (nbf_common::get_param($_REQUEST, 'do_csv_download'))
        {
            $sql .= ", #__nbill_document.billing_address, #__nbill_contact.first_name, #__nbill_contact.last_name, #__nbill_contact.email_address, #__nbill_contact.telephone,
                #__nbill_contact.mobile, #__nbill_contact.fax ";
        }
        $sql .= " FROM #__nbill_document ";
        $joins = "LEFT JOIN #__nbill_orders_document ON #__nbill_document.id = #__nbill_orders_document.document_id
                    LEFT JOIN #__nbill_xref_quote_status ON #__nbill_document.status = #__nbill_xref_quote_status.code
		            LEFT JOIN #__nbill_entity ON #__nbill_document.entity_id = #__nbill_entity.id
                    LEFT JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id
                    LEFT JOIN #__nbill_supporting_docs ON #__nbill_document.id = #__nbill_supporting_docs.associated_doc_id AND #__nbill_supporting_docs.associated_doc_type = '$doc_type' ";
		if (nbf_common::nb_strlen($product_filter) > 0)
		{
			$joins .= "LEFT JOIN #__nbill_document_items ON #__nbill_document.id = #__nbill_document_items.document_id ";
		}
		$sql .= $joins . $whereclause . " GROUP BY #__nbill_document.id";
		$sql .= " ORDER BY #__nbill_document.written_off, #__nbill_document.paid_in_full, #__nbill_document.partial_payment, DATE(FROM_UNIXTIME(#__nbill_document.document_date)) DESC, #__nbill_document.document_no + 0 DESC, #__nbill_document.document_no DESC ";
        if (!nbf_common::get_param($_REQUEST, 'do_csv_download'))
        {
            $sql .= "LIMIT $pagination->list_offset, $pagination->records_per_page";
        }
        else
        {
            $sql .= "LIMIT " . nbf_globals::$record_limit;
        }
	}
	$nb_database->setQuery($sql);
	$rows = $nb_database->loadObjectList();
	if (!$rows)
	{
		$rows = array();
	}

    //Get total net/tax/gross for the current page of invoices
    $sql = "SELECT currency, SUM(tmp_invoice.total_net) AS total_net_page, SUM(tmp_invoice.total_tax) AS total_tax_page,
                    SUM(tmp_invoice.total_gross) AS total_gross_page FROM ($sql) AS tmp_invoice GROUP BY currency ";
    $nb_database->setQuery($sql);
    $page_totals = $nb_database->loadObjectList();
    if (!$page_totals)
    {
        $page_totals = array();
    }

    //Get total net/tax/gross for ALL invoices in date range
    $sql = "SELECT currency, (SUM(#__nbill_document.total_net) + SUM(#__nbill_document.total_shipping)) AS total_net_all,
                    (SUM(#__nbill_document.total_tax) + SUM(#__nbill_document.total_shipping_tax)) AS total_tax_all,
                    SUM(#__nbill_document.total_gross) AS total_gross_all FROM #__nbill_document $count_joins $whereclause GROUP BY currency";
    $nb_database->setQuery($sql);
    $sum_totals = $nb_database->loadObjectList();
    if (!$sum_totals)
    {
        $sum_totals = array();
    }

    $order_ids_present = false;

    //Get list of ids (only need first few - however many will be displayed) and check whether any order ids are present (to line up icons)
	$document_ids = array();
    foreach ($rows as $row)
	{
        
		$document_ids[] = $row->id;
	}

    $first_product_description = array();
    $document_items = array();
    $max_items_per_invoice = 0;
    if (nbf_common::get_param($_REQUEST, 'do_csv_download'))
    {
        //For CSV export, we get all the line items as well
        if (count($rows) > 0)
        {
            $sql = "SELECT #__nbill_document_items.*, #__nbill_shipping.service AS shipping_service";
            if (nbf_common::get_param($_REQUEST, 'action') == "quotes")
            {
                $sql .= ", #__nbill_xref_pay_frequency.description AS quote_pay_freq_desc";
            }
            $sql .= " FROM #__nbill_document_items ";
            if (nbf_common::get_param($_REQUEST, 'action') == "quotes")
            {
                $sql .= "LEFT JOIN #__nbill_xref_pay_frequency ON #__nbill_document_items.quote_pay_freq = #__nbill_xref_pay_frequency.code ";
            }
            $sql .= "LEFT JOIN #__nbill_shipping ON #__nbill_document_items.shipping_id = #__nbill_shipping.id
                    WHERE document_id IN (" . implode(",", $document_ids) . ") ORDER BY document_id, ordering, id";
            $nb_database->setQuery($sql);
            $document_items = $nb_database->loadObjectList();
            if ($document_items && count($document_items) > 0)
            {
                //Count max number of items
                $sql = "SELECT COUNT(*) AS item_count";

                $sql .= " FROM #__nbill_document_items
                        INNER JOIN #__nbill_document ON #__nbill_document.id = #__nbill_document_items.document_id
                        WHERE document_id IN (" . implode(",", $document_ids) . ")
                        GROUP BY document_id ORDER BY item_count DESC LIMIT 1";
                $nb_database->setQuery($sql);
                $max_items_per_invoice = $nb_database->loadResult();
            }
        }
        if (!$document_items)
        {
            $document_items = array();
        }
    }
    else
    {
        if (count($document_ids) > 0)
        {
            

		    //Get the first item or section's description
		    $sql = "SELECT id, document_id, product_description, section_name FROM #__nbill_document_items WHERE
						    document_id IN (" . implode(",", $document_ids) . ") ORDER BY
						    document_id, ordering";
		    $nb_database->setQuery($sql);
		    $first_product_description = $nb_database->loadObjectList();
	    }
	    if (!$first_product_description)
	    {
		    $first_product_description = array();
	    }
    }

	//Get the date format
    $cfg_date_format = nbf_common::get_date_format(false);

    //Get any attachments
    $attachments = array();
    

    //If quotes, get quote status and check whether we are awaiting offline payment
    $quote_status = array();
    $awaiting = null;
    

    if (nbf_common::get_param($_REQUEST, 'do_csv_download'))
    {
        //Forget the CMS admin template
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
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="' . nbf_common::get_param($_REQUEST, 'action') . '_' . nbf_common::nb_date("Y-m-d") . '.csv"');
        nBillInvoice::downloadDocumentListCSV($vendors, $rows, $document_items, $cfg_date_format, $max_items_per_invoice);
        exit;
    }
    else
    {
	    nBillInvoice::showInvoices($rows, $pagination, $vendors, $first_product_description, $cfg_date_format, $orders_present, $page_totals, $sum_totals, $quote_status, $awaiting, $attachments);
    }
}

function editInvoice($document_id, $use_posted_values = false, $client_changed = false, $scroll_to_items = false)
{
	include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
	$nb_database = nbf_cms::$interop->database;

    $for_invoice = intval(nbf_common::get_param($_REQUEST, 'for_invoice'));
    $for_invoice_no = null;
    $doc_type = "IV";
    switch (nbf_common::nb_strtoupper(nbf_common::get_param($_REQUEST, 'action')))
    {
        case "CREDITS":
            $doc_type = "CR";
            break;
        case "QUOTES":
            $doc_type = "QU";
            break;
    }

	if (!$use_posted_values)
	{
		$_POST['added_items'] = '';
		$_POST['removed_items'] = '';
	}

	$task = "edit";

    if ($doc_type == "CR" && $for_invoice)
    {
        $row = $nb_database->load_record("#__nbill_document", $for_invoice);
        $for_invoice_no = @$row->document_no;
        $row->id = null;
        $row->document_no = '';
        $row->paid_in_full = 0;
        $row->partial_payment = 0;
        $row->refunded_in_full = 0;
        $row->partial_refund = 0;
        $row->written_off = 0;
        $row->date_written_off = 0;
        $row->document_date = nbf_common::nb_date(nbf_common::get_date_format());
        $row->notes = sprintf(NBILL_CREDITS_REFUND_INVOICE, $for_invoice_no);
        $row->payment_instructions = '';
        $row->small_print = '';
        $row->related_document_id = $for_invoice;
        $_REQUEST['related_document_id'] = $for_invoice;
        $_REQUEST['listed_client_id'] = $row->entity_id;
        $_POST['entity_id'] = $row->entity_id;
        $_POST['vendor_filter'] = $row->vendor_id;
        nbf_globals::$message = NBILL_CREDITS_FROM_INVOICE_WARNING;
    }
	else
    {
        $row = $nb_database->load_record("#__nbill_document", $document_id);
        $row->document_type = $doc_type == 'IV' ? 'IN' : $doc_type;
    }

    $row->related_document_no = '';
    $row->related_document_type = '';
    if ($row->related_document_id)
    {
        $related_document = null;
        $sql = "SELECT document_no, document_type FROM #__nbill_document WHERE id = " . intval($row->related_document_id);
        $nb_database->setQuery($sql);
        $nb_database->loadObject($related_document);
        if ($related_document)
        {
            $row->related_document_no = $related_document->document_no;
            $row->related_document_type = $related_document->document_type;
        }
    }

    //Get the data we need to link back to any associated documents
    $related_docs = array();
    if (@$row->id)
    {
        $sql = "SELECT #__nbill_document.id, #__nbill_document.document_no, #__nbill_document.document_type FROM #__nbill_document WHERE related_document_id = " . intval($row->id);
        $nb_database->setQuery($sql);
        $related_docs = $nb_database->loadObjectList();
    }
    //If we already have a direct relation, add it to the list (at the beginning!)
    if ($row->related_document_type)
    {
        $related_doc = new stdClass();
        $related_doc->id = $row->related_document_id;
        $related_doc->document_type = $row->related_document_type;
        $related_doc->document_no = $row->related_document_no;
        array_unshift($related_docs, $related_doc);
    }

	$sql = "SELECT id, vendor_name, vendor_address, vendor_country, vendor_currency, payment_instructions,
            small_print, credit_small_print, quote_small_print, quote_default_intro, quote_offline_pay_inst,
            delivery_small_print, tax_reference_no
            FROM #__nbill_vendor ORDER BY id";
	$nb_database->setQuery($sql);
	$vendors = $nb_database->loadObjectList();

    check_for_translation($vendors, "nbill_vendor", "id", "'payment_instructions', 'small_print'");

    //Get line items (new)
    $config = nBillConfigurationService::getInstance()->getConfig();
    $line_items = null;
    if (!$config->use_legacy_document_editor) {
        $number_factory = new nBillNumberFactory($config);
        $currency_factory = new nBillCurrencyFactory();
        $line_item_factory = new nBillLineItemFactory($number_factory);
        $currency_mapper = $currency_factory->createCurrencyMapper(nbf_cms::$interop->database);
        $currency_service = new nBillCurrencyService($currency_mapper);
        $line_item_mapper = $line_item_factory->createMapper(nbf_cms::$interop->database, $currency_service);
        $line_item_service = $line_item_factory->createService($line_item_mapper);
        $json_line_items = nbf_common::get_param($_POST, 'line_items', '', true, false, true, true);
        if (strlen($json_line_items) > 0) {
            $currency = $currency_service->findCurrency(nbf_common::get_param($_POST, 'currency'));
            $line_items = $line_item_mapper->mapLineItemsFromJson($json_line_items, $currency_mapper, $doc_type == 'IV' ? 'IN' : $doc_type, $currency);
        }
        if (!$line_items) {
            if ($doc_type == "CR" && $for_invoice) {
                $line_items = $line_item_service->getItemsForDocument('CR', $for_invoice, intval(nbf_common::get_param($_POST,'vendor_id')));
                foreach ($line_items->sections as &$section)
                {
                    foreach ($section->line_items as &$line_item)
                    {
                        $line_item->id = null;
                        $line_item->document_id = null;
                    }
                    unset($line_item);
                }
                unset($section);
            } else {
                $line_items = $line_item_service->getItemsForDocument($doc_type == 'IV' ? 'IN' : $doc_type, $document_id, intval(nbf_common::get_param($_POST,'vendor_id')));
            }
        }
    } else {
	    //Get items (legacy)
	    if ($document_id)
	    {
		    $sql = "SELECT #__nbill_document_items.*, #__nbill_product.id AS product_id, #__nbill_product.`name` AS product_name, #__nbill_product.description AS product_desc
                    FROM #__nbill_document_items
                    LEFT JOIN #__nbill_product ON #__nbill_document_items.product_code != '' AND #__nbill_document_items.product_code = #__nbill_product.product_code
                    WHERE document_id = $document_id GROUP BY #__nbill_document_items.id ORDER BY document_id, ordering, id";
		    $nb_database->setQuery($sql);
		    $invoice_items = $nb_database->loadObjectList();
		    if ($invoice_items == null)
		    {
			    $invoice_items = array();
		    }
	    }
	    else
	    {
            if ($doc_type == "CR" && $for_invoice)
            {
                $sql = "SELECT * FROM #__nbill_document_items WHERE document_id = " . $for_invoice . " ORDER BY document_id, ordering, id";
                $nb_database->setQuery($sql);
                $invoice_items = $nb_database->loadObjectList();
                if ($invoice_items == null)
                {
                    $invoice_items = array();
                }

                //Apply section discounts
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.process.discount.class.php");
                nbf_discount::apply_section_discounts($invoice_items, $for_invoice);

                $added_item = 0;
                $added_items = array();
                foreach ($invoice_items as $invoice_item)
                {
                    $invoice_item->product_description = sprintf(NBILL_CREDITS_REFUND_INVOICE, $for_invoice_no) . ": " . $invoice_item->product_description;
                    $added_item++;
                    $added_items[] = $added_item;
                    foreach (get_object_vars($invoice_item) as $property=>$value)
                    {
                        $_POST['nbill_new_' . $added_item . '_' . $property] = is_numeric($value) ? format_number($value) : $value;
                    }
                    $_POST['nbill_' . $row->vendor_id . '_shipping_new_' . $added_item] = $invoice_item->shipping_id;
                    $_POST['nbill_' . $row->vendor_id . '_ledger_new_' . $added_item] = $invoice_item->nominal_ledger_code;
                }
                if ($added_item)
                {
                    $_POST['added_items'] = implode(",", $added_items);
                }
                $invoice_items = array();
            }
            else
            {
		        $invoice_items = array();
            }
	    }
    }

	$clients = array();
	$tax_rates = array();
	$ledger = array();
	$shipping = array();

	//Work out selected vendor and client, if applicable
	if ($use_posted_values)
	{
		$selected_vendor = intval(nbf_common::get_param($_POST,'vendor_id'));
		$selected_client = intval(nbf_common::get_param($_POST,'entity_id'));
	}
	else
	{
		if ($row->id)
		{
			$selected_vendor = $row->vendor_id;
			$selected_client = $row->entity_id;
		}
		else
		{
			$selected_vendor = intval(nbf_common::get_param($_POST,'vendor_filter'));
			$selected_client = intval(nbf_common::get_param($_REQUEST, 'listed_client_id', -1));
		}
	}
	$_POST['vendor_id'] = $selected_vendor;

	if (!nbf_common::get_param($_POST, 'sku_lookup_update_prices') && !nbf_common::get_param($_POST, 'task') == 'client_changed' && !nbf_common::get_param($_POST, 'no_record_limit'))
	{
		//If not looking up SKU, clear down the 'new' entry
		$_POST['nbill_new_product_code'] = "";
        $_POST['nbill_new_product_description'] = "";
		$_POST['nbill_' . $selected_vendor . '_ledger_new'] = "";
		$_POST['nbill_new_net_price_per_unit'] = 0;
        $_POST['nbill_new_no_of_units'] = 0;
        $_POST['nbill_new_discount_description'] = "";
        $_POST['nbill_new_discount_amount'] = 0;
        $_POST['nbill_new_net_price_for_item'] = 0;
        $_POST['nbill_new_tax_rate_for_item'] = 0;
        $_POST['nbill_new_tax_for_item'] = 0;
        $_POST['nbill_' . $selected_vendor . '_shipping_new'] = -1;
        $_POST['nbill_new_shipping_for_item'] = 0;
        $_POST['nbill_new_tax_for_shipping'] = 0;
        $_POST['nbill_new_pay_freq'] = 'AA';
        $_POST['nbill_new_auto_renew'] = 1;
        $_POST['nbill_new_relating_to'] = '';
        $_POST['nbill_new_unique_invoice'] = 0;
        $_POST['nbill_new_mandatory'] = 0;
        $_POST['nbill_new_item_accepted'] = 0;
	}

	//Get client list
	$sql = "SELECT #__nbill_entity.id, #__nbill_entity.company_name, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS contact_name, CONCAT(#__nbill_entity.company_name, CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS ordering
            FROM #__nbill_entity LEFT JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id WHERE ";
    if (nbf_common::get_param($_REQUEST, 'action') == "quotes")
    {
        $sql .= "#__nbill_entity.is_supplier = 0";
    }
    else
    {
        $sql .= "#__nbill_entity.is_client = 1";
    }
    if (nbf_common::get_param($_REQUEST, 'disable_client_list'))
    {
        $sql .= " AND #__nbill_entity.id = $selected_client";
        if ($selected_client == -1)
        {
            $sql .= " OR #__nbill_entity.id = " . intval(nbf_common::get_param($_REQUEST, 'listed_client_id'));
        }
    }
    $sql .= " ORDER BY ordering";
    if (!nbf_common::get_param($_REQUEST, 'no_record_limit'))
    {
        $sql .= " LIMIT " . nbf_globals::$record_limit;
    }
	$nb_database->setQuery($sql);
	$clients = $nb_database->loadObjectList();
	if (!isset($clients) || !$clients)
	{
		$clients = array();
	}
	if ($row->entity_id && count($clients) == nbf_globals::$record_limit)
    {
        //Make sure our guy is there
        for ($i = 0; $i < nbf_globals::$record_limit; $i++)
        {
            if ($clients[$i]->id == $row->entity_id)
            {
                break;
            }
        }
        if ($i == nbf_globals::$record_limit)
        {
            //Not here - load from db
            $our_guy = null;
            $sql = "SELECT #__nbill_entity.id, #__nbill_entity.company_name, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS contact_name, CONCAT(#__nbill_entity.company_name, CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS ordering
                            FROM #__nbill_entity LEFT JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id
                            WHERE #__nbill_entity.id = " . $row->entity_id;
            $nb_database->setQuery($sql);
            $nb_database->loadObject($our_guy);
            $clients[$i - 1] = $our_guy;
        }
    }

    foreach ($vendors as $vendor)
	{
		//Get tax options
		$sql = "SELECT #__nbill_tax.*, '" . $nb_database->getEscaped($vendor->tax_reference_no) . "' AS tax_reference_no FROM #__nbill_tax WHERE vendor_id = " . $vendor->id . " ORDER BY electronic_delivery, country_code";
		$nb_database->setQuery($sql);
		$tax_rates[$vendor->id] = $nb_database->loadObjectList();
		if (!isset($tax_rates[$vendor->id]) || !$tax_rates[$vendor->id])
		{
			$tax_rates[$vendor->id] = array();
		}
        check_for_translation($tax_rates[$vendor->id], "nbill_tax", "id", "'payment_instructions', 'small_print'");

		//Get nominal ledger codes
		$sql = "SELECT * FROM #__nbill_nominal_ledger WHERE vendor_id = " . $vendor->id . " ORDER BY code";
		$nb_database->setQuery($sql);
		$ledger[$vendor->id] = $nb_database->loadObjectList();
		if (!isset($ledger[$vendor->id]) || !$ledger[$vendor->id])
		{
			$ledger[$vendor->id] = array();
		}

		//Get shipping options
		$sql = "SELECT * FROM #__nbill_shipping WHERE vendor_id = " . $vendor->id . " ORDER BY code";
		$nb_database->setQuery($sql);
		$shipping[$vendor->id] = $nb_database->loadObjectList();
		if (!isset($shipping[$vendor->id]) || !$shipping[$vendor->id])
		{
			$shipping[$vendor->id] = array();
		}

		//Get shipping prices
		$sql = "SELECT * FROM #__nbill_shipping_price WHERE vendor_id = " . $vendor->id;
		$nb_database->setQuery($sql);
		$shipping_price[$vendor->id] = $nb_database->loadObjectList();
		if (!isset($shipping_price[$vendor->id]) || !$shipping_price[$vendor->id])
		{
			$shipping_price[$vendor->id] = array();
		}
	}

	//Load selected client data
	$sql = "SELECT #__nbill_entity.id, #__nbill_entity.company_name, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS contact_name, #__nbill_entity.add_name_to_invoice,
						#__nbill_entity.address_1, #__nbill_entity.address_2, #__nbill_entity.address_3,
						#__nbill_entity.town, #__nbill_entity.state, #__nbill_entity.reference, #__nbill_entity.country,
						#__nbill_xref_country_codes.id, #__nbill_xref_country_codes.description AS country_name,
						#__nbill_entity.tax_zone, #__nbill_entity.postcode, #__nbill_xref_eu_country_codes.code AS in_eu,
						#__nbill_entity.tax_exemption_code
						FROM #__nbill_entity
                        LEFT JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id
                        LEFT JOIN #__nbill_xref_country_codes ON #__nbill_entity.country = #__nbill_xref_country_codes.code
						LEFT JOIN #__nbill_xref_eu_country_codes ON #__nbill_entity.country = #__nbill_xref_eu_country_codes.code
						WHERE #__nbill_entity.id = $selected_client";
	$nb_database->setQuery($sql);
	$nb_database->loadObject($selected_client_row);

    //Load shipping addresses for client and related contacts
    $contact_factory = new nBillContactFactory();
    $entity_factory = new nBillEntityFactory();
    $entity_service = $entity_factory->createEntityService($contact_factory->createContactService());
    $client = $entity_service->loadEntity($selected_client, true);

    //Load Discounts
    /*$discounts = array();
    foreach ($vendors as $vendor)
    {
        $sql = "SELECT id AS discount_id, discount_name FROM #__nbill_discounts WHERE vendor_id = " . $vendor->id . " ORDER BY discount_name";
        $nb_database->setQuery($sql);
        $discounts[$vendor->id] = $nb_database->loadObjectList();
        if (!isset($discounts[$vendor->id]) || !$discounts[$vendor->id])
        {
            $discounts[$vendor->id] = array();
        }
    }*/

    //Load payment plans and select default if applicable
    $payment_plans = array();
    switch (nbf_common::get_param($_REQUEST, 'action'))
    {
        case "quotes":
        case "invoices":
            $default_col = (nbf_common::get_param($_REQUEST, 'action') == "quotes" ? "quote" : "invoice") . "_default";
            $sql = "SELECT id, plan_name, plan_type, $default_col FROM #__nbill_payment_plans ORDER BY plan_name";
            $nb_database->setQuery($sql);
            $payment_plans = $nb_database->loadObjectList();
            if (!$row->id)
            {
                if ($payment_plans && count($payment_plans) > 0)
                {
                    foreach ($payment_plans as $payment_plan)
                    {
                        if ($payment_plan->$default_col)
                        {
                            $row->payment_plan_id = $payment_plan->id;
                            break;
                        }
                    }
                }
            }
    }

	$currencies = nbf_xref::get_currencies();
	$countries = nbf_xref::get_countries(false, true);

    //Get any attachments
    $attachments = array();
    

    if ($config->use_legacy_document_editor) {
        //Get accepted item total
        if ($doc_type == "QU")
        {
            $accepted_total = 0;
            foreach ($invoice_items as $document_item)
            {
                if ($document_item->quote_item_accepted)
                {
                    $accepted_total = float_add($accepted_total, $document_item->gross_price_for_item, 'currency_grand');
                }
            }
            $row->accepted_total_gross = format_number($accepted_total);
        }
    }

    if ($selected_client_row && !$document_id) {
        $row->billing_name = $selected_client_row->company_name;
        if (strlen($selected_client_row->contact_name) > 0) {
            if (strlen($row->billing_name) > 0 && $selected_client_row->add_name_to_invoice) {
                $row->billing_name .= ' (' . $selected_client_row->contact_name . ')';
            } else if (strlen($row->billing_name) == 0) {
                $row->billing_name = $selected_client_row->contact_name;
            }
        }
        include_once(nbf_cms::$interop->nbill_admin_base_path . '/framework/classes/nbill.address.class.php');
        $row->billing_address = nbf_address::format_billing_address($selected_client_row->address_1, $selected_client_row->address_2, $selected_client_row->address_3, $selected_client_row->town, $selected_client_row->state, $selected_client_row->postcode, $selected_client_row->country);
        $row->billing_country = $selected_client_row->country;
        $row->tax_exemption_code = $selected_client_row->tax_exemption_code;
    }

    //Find default tax rate
    $default_tax_rate = '0.00';
    if ($selected_vendor) {
        $this_vendor = null;
        foreach ($vendors as $vendor) {
            if ($vendor->id == $selected_vendor) {
                $this_vendor = $vendor;
                break;
            }
        }
        if ($this_vendor) {
            include_once(nbf_cms::$interop->nbill_admin_base_path . '/framework/classes/nbill.tax.class.php');
            if ($selected_client_row) {
                $default_tax_rate_record = nbf_tax::find_tax_rate($this_vendor->id, $this_vendor->vendor_country, $selected_client_row->tax_zone, @$row->billing_country ? $row->billing_country : $selected_client_row->country, $selected_client_row->in_eu);
            } else {
                $default_tax_rate_record = nbf_tax::find_tax_rate($this_vendor->id, $this_vendor->vendor_country, '', @$row->billing_country ? $row->billing_country : $this_vendor->vendor_country, false);
            }
            if ($default_tax_rate_record) {
                $default_tax_rate = (strlen(@$selected_client_row->tax_exemption_code) > 0 && $default_tax_rate_record->exempt_with_ref_no) ? '0.00' : $default_tax_rate_record->tax_rate;
            }
        }
    }

    ob_start();
    nBillInvoice::editInvoice($document_id, $row, $line_item_factory, $line_items, $vendors, $clients, $selected_client_row, $default_tax_rate, $client, $tax_rates, $countries, $currencies, $ledger, $payment_plans, $shipping, $shipping_price, $use_posted_values, $client_changed, $attachments, $related_docs, $scroll_to_items);

    $html = ob_get_clean();

    $output = nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'edit', get_defined_vars(), $html);
    echo $output;
}

function lookup_sku()
{
	$nb_database = nbf_cms::$interop->database;

    $sku_key = "";
	$product_description_key = "";
    $detailed_description_key = "";
	$ledger_key = "";
	$net_price_per_unit_key = "";
    $tax_rate_key = "";
    $tax_amount_key = "";
	$sku_value = "";
	$price_update_key = "";
    $pay_freq_key = "";
    $net_price = '0.00';
    $vendor_id = intval(nbf_common::get_param($_REQUEST, 'vendor_id'));
    $country = nbf_common::get_param($_REQUEST, 'billing_country');
    $tax_rate = nbf_common::get_param($_REQUEST, 'default_tax_rate');
	$currency = nbf_common::get_param($_REQUEST, 'currency');

	foreach ($_POST as $key=>$value)
	{
		if (nbf_common::nb_strpos($key, "lookup_sku") !== false && $value)
		{
			$key_parts = explode("_", $key);
			if ($key_parts[1] == "new")
			{
				if ($key_parts[2] == "lookup" && $key_parts[3] == "sku")
				{
					$sku_key = "nbill_new_product_code";
					$product_description_key = "nbill_new_product_description";
                    $detailed_description_key = "nbill_new_detailed_description";
					$ledger_key = "nbill_" . nbf_common::get_param($_REQUEST, 'vendor_id') . "_ledger_new";
					$net_price_per_unit_key = "nbill_new_net_price_per_unit";
                    $tax_rate_key = "nbill_new_tax_rate_for_item";
                    $tax_amount_key = "nbill_new_tax_for_item";
					$price_update_key = "new";
                    $pay_freq_key = "nbill_new_pay_freq";
				}
				else
				{
					$sku_key = "nbill_new_" . $key_parts[2] . "_product_code";
					$product_description_key = "nbill_new_" . $key_parts[2] . "_product_description";
                    $detailed_description_key = "nbill_new_" . $key_parts[2] . "_detailed_description";
					$ledger_key = "nbill_" . nbf_common::get_param($_REQUEST, 'vendor_id') . "_ledger_new_" . $key_parts[2];
					$net_price_per_unit_key = "nbill_new_" . $key_parts[2] . "_net_price_per_unit";
                    $tax_rate_key = "nbill_new_" . $key_parts[2] . "_tax_rate_for_item";
                    $tax_amount_key = "nbill_new_" . $key_parts[2] . "_tax_for_item";
					$price_update_key = "new_" . $key_parts[2];
                    $pay_freq_key = "nbill_new_" . $key_parts[2] . "_pay_freq";
				}
			}
			else
			{
				$sku_key = "nbill_" . $key_parts[1] . "_product_code";
                $product_description_key = "nbill_" . $key_parts[1] . "_product_description";
				$detailed_description_key = "nbill_" . $key_parts[1] . "_detailed_description";
				$ledger_key = "nbill_" . nbf_common::get_param($_REQUEST, 'vendor_id') . "_ledger_" . $key_parts[1];
				$net_price_per_unit_key = "nbill_" . $key_parts[1] . "_net_price_per_unit";
                $tax_rate_key = "nbill_" . $key_parts[1] . "_tax_rate_for_item";
                $tax_amount_key = "nbill_" . $key_parts[1] . "_tax_for_item";
				$price_update_key = $key_parts[1];
                $pay_freq_key = "nbill_" . $key_parts[1] . "_pay_freq";
			}

			$sku_value = nbf_common::get_param($_REQUEST,$sku_key);
			if (nbf_common::nb_strlen(trim($sku_value)) > 0)
			{
				$sql = "SELECT id, nominal_ledger_code, name, description, is_taxable, custom_tax_rate, electronic_delivery FROM #__nbill_product WHERE product_code = '$sku_value' AND vendor_id = " . $vendor_id;
				$nb_database->setQuery($sql);
				$product_details = null;
				$nb_database->loadObject($product_details);
				if ($product_details)
				{
                    $config = nBillConfigurationService::getInstance()->getConfig();
                    if ($product_details->custom_tax_rate > 0 && $tax_rate > 0) {
                        $tax_rate = $product_details->custom_tax_rate;
                    } else {
                        if ($product_details->electronic_delivery) {
                            $number_factory = new nBillNumberFactory($config);
                            $tax_mapper = new nBillTaxMapper(nbf_cms::$interop->database, $number_factory);
                            $tax_service = new nBillTaxService($tax_mapper, $config);
                            $product_tax_rate = $tax_service->getElectronicDeliveryRateForCountry($vendor_id, $country);
                            if ($product_tax_rate) {
                                $tax_rate = $product_tax_rate->tax_rate->format();
                            }
                        }
                    }
					$_POST[$product_description_key] = $product_details->name;
					$_REQUEST[$product_description_key] = $product_details->name;
                    $_POST[$detailed_description_key] = $product_details->description;
                    $_REQUEST[$detailed_description_key] = $product_details->description;
					$_POST[$ledger_key] = $product_details->nominal_ledger_code;
					$_REQUEST[$ledger_key] = $product_details->nominal_ledger_code;
                    if (!$product_details->is_taxable)
                    {
                        $_POST[$tax_rate_key] = "0.00";
                        $_REQUEST[$tax_rate_key] = "0.00";
                        $_POST[$tax_amount_key] = "0.00";
                        $_REQUEST[$tax_amount_key] = "0.00";
                    }
                    else
                    {
                        $_POST[$tax_rate_key] = format_number($tax_rate, $config->precision_tax_rate);
                        $_REQUEST[$tax_rate_key] = format_number($tax_rate, $config->precision_tax_rate);
                    }
					$sql = "SELECT * FROM #__nbill_product_price WHERE product_id = $product_details->id AND currency_code = '$currency'";
					$nb_database->setQuery($sql);
					$product_price = null;
					$nb_database->loadObject($product_price);
					if ($product_price)
					{
                        if ($product_price->net_price_setup_fee != 0)
                        {
                            $_POST['setup_fee_warning'] = 1;
                            $_REQUEST['setup_fee_warning'] = 1;
                        }
						$net_price = format_number($product_price->net_price_one_off);
                        $pay_freq = "AA";
						if ($net_price == 0)
						{
							$net_price = format_number($product_price->net_price_weekly);
                            $pay_freq = "BB";
						}
						if ($net_price == 0)
						{
							$net_price = format_number($product_price->net_price_four_weekly);
                            $pay_freq = "BX";
						}
						if ($net_price == 0)
						{
                            $net_price = format_number($product_price->net_price_monthly);
                            $pay_freq = "CC";
						}
						if ($net_price == 0)
						{
							$net_price = format_number($product_price->net_price_quarterly);
                            $pay_freq = "DD";
						}
						if ($net_price == 0)
						{
							$net_price = format_number($product_price->net_price_semi_annually);
                            $pay_freq = "DX";
						}
						if ($net_price == 0)
						{
							$net_price = format_number($product_price->net_price_annually);
                            $pay_freq = "EE";
						}
						if ($net_price == 0)
						{
							$net_price = format_number($product_price->net_price_biannually);
                            $pay_freq = "FF";
						}
						if ($net_price == 0)
						{
							$net_price = format_number($product_price->net_price_five_years);
                            $pay_freq = "GG";
						}
						if ($net_price == 0)
						{
							$net_price = format_number($product_price->net_price_ten_years);
                            $pay_freq = "HH";
						}
                        if ($net_price == 0)
                        {
                            $pay_freq = "AA"; //Default to one-off if no price defined
                        }
                        $_POST[$net_price_per_unit_key] = $net_price;
						$_REQUEST[$net_price_per_unit_key] = $net_price;
						$_POST['sku_lookup_update_prices'] = $price_update_key;
                        $_POST[$pay_freq_key] = $pay_freq;
                        $_REQUEST[$pay_freq_key] = $pay_freq;
                        if (!$product_details->is_taxable)
                        {
                            $_POST[$tax_amount_key] = format_number(($net_price / 100) * $tax_rate, $config->precision_currency);
                            $_REQUEST[$tax_amount_key] = $_POST[$tax_amount_key];
                        }
					}
				}
			}
			break;
		}
	}
}

function show_product_list()
{
    $nb_database = nbf_cms::$interop->database;

    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.category.class.php");
    $vendor_id = intval(nbf_common::get_param($_REQUEST, 'vendor_id'));
    $country = nbf_common::get_param($_REQUEST, 'billing_country');
    $currency = nbf_common::get_param($_REQUEST, 'currency');
    $sku_key = nbf_common::get_param($_REQUEST, 'sku_key');
    $ordering = nbf_common::get_param($_REQUEST, 'ordering') == "sku" ? "#__nbill_product.product_code, #__nbill_product.name" : "#__nbill_product.name, #__nbill_product.product_code";
    $cats = nbf_category_hierarchy::get_category_hierarchy($vendor_id);

    $config = nBillConfigurationService::getInstance()->getConfig();
    $number_factory = new nBillNumberFactory($config);
    $tax_mapper = new nBillTaxMapper(nbf_cms::$interop->database, $number_factory);
    $tax_service = new nBillTaxService($tax_mapper, $config);
    $electronic_tax_rate = false;
    $product_tax_rate = $tax_service->getElectronicDeliveryRateForCountry($vendor_id, $country);
    if ($product_tax_rate) {
        $electronic_tax_rate = $product_tax_rate->tax_rate->format();
    }
    foreach ($cats as $cat)
    {
        $sql = "SELECT id, nominal_ledger_code, product_code, name, description, is_taxable, custom_tax_rate, electronic_delivery,
                #__nbill_product_price.* FROM #__nbill_product
                LEFT JOIN #__nbill_product_price
                ON #__nbill_product.id = #__nbill_product_price.product_id
                WHERE #__nbill_product.category = " . intval($cat["id"]) .
                " AND #__nbill_product_price.currency_code = '$currency'
                AND #__nbill_product_price.vendor_id = $vendor_id ORDER BY $ordering";
        $nb_database->setQuery($sql);
        $products[$cat["id"]] = $nb_database->loadObjectList();
        if ($products[$cat["id"]]) {
            foreach ($products[$cat["id"]] as &$product)
            {
                if ($product->electronic_delivery) {
                    $product->custom_tax_rate = $electronic_tax_rate;
                }
            }
        }
    }

    include_once(nbf_cms::$interop->nbill_admin_base_path . '/admin.html/invoices_legacy.html.php');
    nBillLegacyInvoice::showProductList($vendor_id, $sku_key, $cats, $products);
}

function removeItem()
{
	$item_to_remove = null;
	foreach ($_POST as $key=>$value)
	{
		if (substr($key, 0, 22) == "h_remove_invoice_item_" && $value == '1')
        {
            $item_to_remove = substr($key, 22);
            break;
        }
	}

	if ($item_to_remove != null)
	{
		$removed_items = array();
		if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'removed_items')) > 0)
		{
			$removed_items = explode(",", nbf_common::get_param($_POST,'removed_items'));
		}
		$removed_items[] = $item_to_remove;
		$_POST['removed_items'] = implode(",", $removed_items);
	}
}

function addItem()
{
	$nb_database = nbf_cms::$interop->database;
	$added_items = array();
	if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'added_items')) > 0)
	{
		$added_items = explode(",", nbf_common::get_param($_POST,'added_items'));
	}
	if (count($added_items)	> 0)
	{
		$next_item = count($added_items) + 1;
	}
	else
	{
		$next_item = 1;
	}
	//Get vendor list
	$sql = "SELECT id FROM #__nbill_vendor";
	$nb_database->setQuery($sql);
	$vendors = $nb_database->loadObjectList();

	$_POST['nbill_new_' . $next_item . '_product_code'] = nbf_common::get_param($_POST, 'nbill_new_product_code');
	$_POST['nbill_new_' . $next_item . '_product_description'] = nbf_common::get_param($_POST, 'nbill_new_product_description');
    $_POST['nbill_new_' . $next_item . '_detailed_description'] = nbf_common::get_param($_POST, 'nbill_new_detailed_description', null, true, false, true);
	$_POST['nbill_' . nbf_common::get_param($_POST,'vendor_id') . '_ledger_new_' . $next_item] = nbf_common::get_param($_POST, 'nbill_' . nbf_common::get_param($_POST, 'vendor_id') . '_ledger_new');
	$_POST['nbill_new_' . $next_item . '_net_price_per_unit'] = nbf_common::get_param($_POST, 'nbill_new_net_price_per_unit');
	$_POST['nbill_new_' . $next_item . '_no_of_units'] = nbf_common::get_param($_POST, 'nbill_new_no_of_units');
	$_POST['nbill_new_' . $next_item . '_discount_description'] = nbf_common::get_param($_POST, 'nbill_new_discount_description');
	$_POST['nbill_new_' . $next_item . '_discount_amount'] = nbf_common::get_param($_POST, 'nbill_new_discount_amount');
	$_POST['nbill_new_' . $next_item . '_net_price_for_item'] = nbf_common::get_param($_POST, 'nbill_new_net_price_for_item');
	$_POST['nbill_new_' . $next_item . '_tax_for_item'] = nbf_common::get_param($_POST, 'nbill_new_tax_for_item');
    $_POST['nbill_new_' . $next_item . '_tax_rate_for_item'] = nbf_common::get_param($_POST, 'nbill_new_tax_rate_for_item');
	foreach ($vendors as $vendor)
	{
		$_POST['nbill_' . $vendor->id . '_shipping_new_' . $next_item] = nbf_common::get_param($_POST, 'nbill_' . $vendor->id . '_shipping_new');
	}
	$_POST['nbill_new_' . $next_item . '_shipping_for_item'] = nbf_common::get_param($_POST, 'nbill_new_shipping_for_item');
	$_POST['nbill_new_' . $next_item . '_tax_for_shipping'] = nbf_common::get_param($_POST, 'nbill_new_tax_for_shipping');
    $_POST['nbill_new_' . $next_item . '_tax_rate_for_shipping'] = nbf_common::get_param($_POST, 'nbill_new_tax_rate_for_shipping');
    if (nbf_common::get_param($_POST, 'action') == "quotes")
    {
        $_POST['nbill_new_' . $next_item . '_pay_freq'] = nbf_common::get_param($_POST, 'nbill_new_pay_freq');
        $_POST['nbill_new_' . $next_item . '_auto_renew'] = nbf_common::get_param($_POST, 'nbill_new_auto_renew');
        $_POST['nbill_new_' . $next_item . '_relating_to'] = nbf_common::get_param($_POST, 'nbill_new_relating_to');
        $_POST['nbill_new_' . $next_item . '_unique_invoice'] = nbf_common::get_param($_POST, 'nbill_new_unique_invoice');
        $_POST['nbill_new_' . $next_item . '_mandatory'] = nbf_common::get_param($_POST, 'nbill_new_mandatory');
        $_POST['nbill_new_' . $next_item . '_item_accepted'] = nbf_common::get_param($_POST, 'nbill_new_item_accepted');
    }

	$added_items[] = $next_item;
	$_POST['added_items'] = implode(",", $added_items);
}

function moveItem($direction)
{
    $nb_database = nbf_cms::$interop->database;
    $document_id = intval(nbf_common::get_param($_REQUEST, 'cid'));
    $item_id = intval(nbf_common::get_param($_REQUEST, 'ordering_item'));

    if ($document_id && $item_id)
    {
        //Remembering that for old invoices, all items will have an ordering of zero, we need to re-order every item (this will also close any gaps)
        $sql = "SELECT id, ordering FROM #__nbill_document_items WHERE document_id = $document_id ORDER BY ordering, id";
        $nb_database->setQuery($sql);
        $items = $nb_database->loadObjectList();

        $new_items = array();
        $current_item = 0;
        for ($i=0; $i<(count($items) * 2); $i=$i+2)
        {
            if ($items[$current_item]->id == $item_id)
            {
                switch ($direction)
                {
                    case "up":
                        $new_items[$i - 1] = $new_items[$i - 2];
                        $new_items[$i - 2] = $items[$current_item];
                        break;
                    case "down":
                        $new_items[$i + 3] = $items[$current_item];
                        break;
                }
            }
            else
            {
                $new_items[$i] = $items[$current_item];
            }
            $current_item++;
        }

        ksort($new_items);
        $new_items = array_values($new_items);
        $new_item_ids = array();
        foreach ($new_items as $new_item)
        {
            $new_item_ids[] = $new_item->id;
        }
        $ids = implode(',', array_values($new_item_ids));
        $sql = "UPDATE #__nbill_document_items SET ordering = CASE id ";
        foreach ($new_items as $key => $value)
        {
            $sql .= sprintf("WHEN %d THEN %d ", $value->id, $key);
        }
        $sql .= "END WHERE id IN ($ids)";
        $nb_database->setQuery($sql);
        $nb_database->query();
    }
}

function saveInvoice($document_id)
{
	$nb_database = nbf_cms::$interop->database;
	$insert = false;
    switch (nbf_common::get_param($_REQUEST, 'action'))
    {
        case "credits":
            $doc_type = 'CR';
            break;
        case "quotes":
            $doc_type = 'QU';
            break;
        default:
            $doc_type = 'IV';
            break;
    }
    $is_quote = $doc_type == 'QU';

    $added_items = array();
    if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'added_items')) > 0)
    {
        $added_items = explode(",", nbf_common::get_param($_POST,'added_items'));
    }

    $new_quote_accepts = array();
    if ($is_quote)
    {
        //Check whether status has changed - if so, and some action may be required, offer to perform it...
        $new_task = "";
        $old_status = "AA";
        if ($document_id)
        {
            $sql = "SELECT status FROM #__nbill_document WHERE id = " . intval($document_id);
            $nb_database->setQuery($sql);
            $old_status = $nb_database->loadResult();
        }
        if (!$old_status)
        {
            $old_status = "AA";
        }

        
    }

	$_POST['payment_instructions'] = @$_POST['pay_inst_' . nbf_common::get_param($_POST, 'vendor_id')]; //nbf_common::get_param can cause problems if magic quotes are on
    $_POST['small_print'] = @$_POST['sml_prt_' . nbf_common::get_param($_POST, 'vendor_id')]; //nbf_common::get_param can cause problems if magic quotes are on
	$_POST['delivery_small_print'] = @$_POST['sml_prt_delivery_' . nbf_common::get_param($_POST, 'vendor_id')]; //nbf_common::get_param can cause problems if magic quotes are on

    

	if (nbf_common::get_param($_REQUEST, 'action') != "invoices")
	{
		$_POST['show_invoice_paylink'] = 2; //Hide
	}

	//If adding a new invoice, and invoice number has not been specified, use the next available number
	if (!$document_id && nbf_common::nb_strlen(trim(nbf_common::get_param($_POST,'document_no'))) == 0)
	{
        $error = "";
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.number.generator.php");
		$_POST['document_no'] = nbf_number_generator::get_next_number(nbf_common::get_param($_POST,'vendor_id'), substr(nbf_common::get_param($_REQUEST, 'action'), 0, strlen(nbf_common::get_param($_REQUEST, 'action')) - 1), $error);
        if ($_POST['document_no'] === false)
		{
			echo "<script> alert('" . $error . " " . NBILL_ERR_REDIRECT_BACK . "'); window.history.go(-1); </script>\n";
			exit();
		}
	}

	//If New, need to insert global invoice data to get an document_id which we can then use to insert the invoice item data
	if (!$document_id)
	{
		$sql = "INSERT INTO #__nbill_document (vendor_id, entity_id, document_no, vendor_address, billing_address, quote_intro, payment_instructions, small_print, delivery_small_print, notes, correspondence, uploaded_files) VALUES
						(" . intval(nbf_common::get_param($_POST,'vendor_id')) . ", " . intval(nbf_common::get_param($_POST,'entity_id')) . ", '" .
						nbf_common::get_param($_POST,'document_no') . "', '', '', '', '', '', '', '', '', '')";
		$nb_database->setQuery($sql);
		$nb_database->query();
		$document_id = $nb_database->insertid();
		$_POST['id'] = $document_id;
		$insert = true;
	}

    //If we are removing the gateway association, do it...
    if (nbf_common::get_param($_REQUEST, 'remove_gateway_txn_id'))
    {
        $sql = "UPDATE #__nbill_document SET gateway_txn_id = '' WHERE id = " . intval($document_id);
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

    $not_awaiting_payment = false;
    if ($is_quote) {
        switch (nbf_common::get_param($_REQUEST, 'status'))
        {
            case "AA": //New
            case "BB": //On hold
            case "CC": //Quoted
                if ($old_status != nbf_common::get_param($_REQUEST, 'status'))
                {
                    $not_awaiting_payment = true;
                }
        }
    }

    $config = nBillConfigurationService::getInstance()->getConfig();
    if (!$config->use_legacy_document_editor) {
        $number_factory = new nBillNumberFactory($config);
        $currency_factory = new nBillCurrencyFactory();
        $line_item_factory = new nBillLineItemFactory($number_factory);
        $currency_service = new nBillCurrencyService($currency_factory->createCurrencyMapper(nbf_cms::$interop->database));
        $line_item_mapper = $line_item_factory->createMapper(nbf_cms::$interop->database, $currency_service);
        $line_item_service = $line_item_factory->createService($line_item_mapper);
        $line_items_json = nbf_common::get_param($_REQUEST, 'line_items', '', true, false, true, true);
        $item_collection = $line_item_mapper->mapLineItemsFromJson($line_items_json, $currency_factory->createCurrencyMapper(nbf_cms::$interop->database), $doc_type, $currency_service->findCurrency(nbf_common::get_param($_POST, 'currency')));

        if ($config->edit_products_in_documents) {
            //Populate arrays ready for later update
            $added_products = array();
            $updated_products = array();

            $product_factory = new nBillProductFactory($number_factory, new nBillPaymentFactory());
            $product_mapper = new nBillProductMapper(nbf_cms::$interop->database, $product_factory, $number_factory, new nBillNominalLedgerFactory(), $currency_service);
            $product_service = new nBillProductService($product_mapper);

            if ((nbf_common::get_param($_REQUEST, 'product_added') && strtolower(nbf_common::get_param($_REQUEST, 'product_added')) != 'false')) {
                $items_affected = explode(",", nbf_common::get_param($_REQUEST, 'product_added'));
                foreach ($items_affected as $item_affected)
                {
                    if (strpos($item_affected, ':') !== false) {
                        $section_parts = explode(':', $item_affected);
                        $section_index = intval($section_parts[0]);
                        $item_parts = explode(':', $item_affected);
                        $item_index = intval($item_parts[1]);
                        $line_item = $item_collection->sections[$section_index]->line_items[$item_index];
                        if (strlen($line_item->product_code) > 0 && !$product_service->productCodeExists($line_item->product_code)) {
                            $added_product['sku'] = $line_item->product_code;
                            $added_product['prev_sku'] = '';
                            $added_product['name'] = $line_item->product_description;
                            $added_product['description'] = $line_item->detailed_description;
                            $added_product['ledger_code'] = $line_item->nominal_ledger_code;
                            $added_product['net_price'] = $line_item->net_price_per_unit->getEditableDecimal()->format();
                            $added_product['prev_price'] = 0;
                            $added_product['pay_freq'] = isset($line_item->quote_pay_freq) ? $line_item->quote_pay_freq : 'AA';
                            $added_products[] = $added_product;
                        }
                    }
                }
            }
            if (nbf_common::get_param($_REQUEST, 'product_updated') && strtolower(nbf_common::get_param($_REQUEST, 'product_updated')) != 'false') {
                $items_affected = explode(",", nbf_common::get_param($_REQUEST, 'product_updated'));
                foreach ($items_affected as $item_affected)
                {
                    if (strpos($item_affected, ':') !== false) {
                        $section_parts = explode(':', $item_affected);
                        $section_index = intval($section_parts[0]);
                        $item_parts = explode(':', $item_affected);
                        $item_index = intval($item_parts[1]);
                        $line_item = $item_collection->sections[$section_index]->line_items[$item_index];
                        $updated_product['sku'] = $line_item->product_code;
                        $updated_product['prev_sku'] = $updated_product['sku'];
                        $updated_product['name'] = $line_item->product_description;
                        $updated_product['description'] = $line_item->detailed_description;
                        $updated_product['ledger_code'] = $line_item->nominal_ledger_code;
                        $updated_product['net_price'] = $line_item->net_price_per_unit->getEditableDecimal()->format();
                        $product = $product_service->loadProduct($product_service->productCodeExists($line_item->product_code), $line_item->currency->code);

                        $updated_product['pay_freq'] = isset($line_item->quote_pay_freq) ? $line_item->quote_pay_freq : 'AA';
                        $updated_product['prev_price'] = null;
                        $first_freq_code = '';
                        $first_amount = '';
                        foreach ($product->prices as $price)
                        {
                            if ($price->amount->value != 0 && strlen($first_freq_code) == 0) {
                                $first_freq_code = $price->payment_frequency->code;
                                $first_amount = $price->amount->getEditableDecimal()->format();
                            }
                            if ($price->payment_frequency->code == $updated_product['pay_freq']) {
                                $updated_product['prev_price'] = $price->amount->getEditableDecimal()->format();
                                break;
                            }
                        }
                        if ($updated_product['prev_price'] === null) {
                            $updated_product['pay_freq'] = $first_freq_code;
                            $updated_product['prev_price'] = $first_amount;
                        }
                        $updated_products[] = $updated_product;
                    }
                }
            }
        }

        //Save line items
        if ($not_awaiting_payment) {
            foreach ($item_collection->sections as $section)
            {
                foreach ($section->line_items as $line_item)
                {
                    $line_item->quote_awaiting_payment = false;
                }
            }
        }
        if ($is_quote) {
            $new_quote_accepts = $line_item_service->getNewQuoteAccepts(intval($document_id), $item_collection);
            if (count($new_quote_accepts) > 0) {
                $new_task = "accepted";
            }
        }
        $line_item_service->saveItems(intval($document_id), $item_collection, intval(nbf_common::get_param($_POST,'vendor_id')), intval(nbf_common::get_param($_POST, 'entity_id')));
    } else {
	    //Get existing invoice items
	    $sql = "SELECT * FROM #__nbill_document_items WHERE document_id = $document_id ORDER BY document_id, ordering, id";
	    $nb_database->setQuery($sql);
	    $invoice_items = $nb_database->loadObjectList();
	    if ($invoice_items == null)
	    {
		    $invoice_items = array();
	    }

        //Make a note of removed items (don't try to update them otherwise there will be SQL errors)
        $removed_items = array();
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'removed_items')))
        {
            $removed_items = explode(",", nbf_common::get_param($_POST, 'removed_items'));
        }

        //Update any invoice items that have been amended
        $document_item_ids = array();
        foreach ($invoice_items as $invoice_item)
        {
            if (array_search($invoice_item->id, $removed_items) === false)
            {
                $document_item_ids[] = $invoice_item->id;
		        $gross_price_for_item = nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_net_price_for_item');
		        $gross_price_for_item += nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_tax_for_item');
		        $gross_price_for_item += nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_shipping_for_item');
		        $gross_price_for_item += nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_tax_for_shipping');
		        $gross_price_for_item = str_replace(",", ".", $gross_price_for_item . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)

                //Convert zero length strings to zero for numeric data types
                if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_net_price_per_unit')) == 0)
                {
                    $_POST['nbill_' . $invoice_item->id . '_net_price_per_unit'] = "0.00";
                }
                if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_no_of_units')) == 0)
                {
                    $_POST['nbill_' . $invoice_item->id . '_no_of_units'] = "0.00";
                }
                if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_discount_amount')) == 0)
                {
                    $_POST['nbill_' . $invoice_item->id . '_discount_amount'] = "0.00";
                }
                if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_net_price_for_item')) == 0)
                {
                    $_POST['nbill_' . $invoice_item->id . '_net_price_for_item'] = "0.00";
                }
                if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_tax_rate_for_item')) == 0)
                {
                    $_POST['nbill_' . $invoice_item->id . '_tax_rate_for_item'] = "0.00";
                }
                if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_tax_for_item')) == 0)
                {
                    $_POST['nbill_' . $invoice_item->id . '_tax_for_item'] = "0.00";
                }
                if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_shipping_for_item')) == 0)
                {
                    $_POST['nbill_' . $invoice_item->id . '_shipping_for_item'] = "0.00";
                }
                if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_tax_rate_for_shipping')) == 0)
                {
                    $_POST['nbill_' . $invoice_item->id . '_tax_rate_for_shipping'] = "0.00";
                }
                if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_tax_for_shipping')) == 0)
                {
                    $_POST['nbill_' . $invoice_item->id . '_tax_for_shipping'] = "0.00";
                }
                if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'section_discount_percent_' . $invoice_item->id)) == 0)
                {
                    $_POST['section_discount_percent_' . $invoice_item->id] = "0.00";
                }

		        $sql = "UPDATE #__nbill_document_items SET
                                    vendor_id = " . intval(nbf_common::get_param($_POST,'vendor_id')) . ",
							        entity_id = " . nbf_common::get_param($_POST, 'entity_id') . ",
							        nominal_ledger_code = '" . nbf_common::get_param($_POST, 'nbill_' . nbf_common::get_param($_POST, 'vendor_id') . '_ledger_' . $invoice_item->id) . "',
							        product_code = '" . nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_product_code') . "',
							        product_description = '" . nbf_common::get_param($_POST,'nbill_' . $invoice_item->id . '_product_description') . "',
                                    detailed_description = '" . nbf_common::get_param($_POST,'nbill_' . $invoice_item->id . '_detailed_description', null, false, false, true) . "',
							        net_price_per_unit = " . nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_net_price_per_unit') . ",
							        no_of_units = " . nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_no_of_units') . ",
							        discount_amount = " . nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_discount_amount') . ",
							        discount_description = '" . nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_discount_description') . "',
							        net_price_for_item = " . nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_net_price_for_item') . ",
                                    tax_rate_for_item = " . nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_tax_rate_for_item') . ",
							        tax_for_item = " . nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_tax_for_item') . ",
							        shipping_id = " . intval(nbf_common::get_param($_POST, 'nbill_' . nbf_common::get_param($_POST, 'vendor_id') . '_shipping_' . $invoice_item->id)) . ",
							        shipping_for_item = " . nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_shipping_for_item') . ",
                                    tax_rate_for_shipping = " . nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_tax_rate_for_shipping') . ",
							        tax_for_shipping = " . nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_tax_for_shipping') . ",
                                    quote_pay_freq = '" . nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_pay_freq') . "',
                                    quote_auto_renew = " . intval(nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_auto_renew')) . ",
                                    quote_relating_to = '" . nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_relating_to') . "',
                                    quote_unique_invoice = " . intval(nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_unique_invoice')) . ",
                                    quote_mandatory = " . intval(nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_mandatory')) . ",
                                    quote_item_accepted = " . intval(nbf_common::get_param($_POST, 'nbill_' . $invoice_item->id . '_item_accepted')) . ",
							        gross_price_for_item = $gross_price_for_item,
                                    section_name = '" . nbf_common::get_param($_POST, 'section_name_' . $invoice_item->id) . "',
                                    section_discount_title = '" . nbf_common::get_param($_POST, 'section_discount_title_' . $invoice_item->id) . "',
                                    section_discount_percent = '" . nbf_common::get_param($_POST, 'section_discount_percent_' . $invoice_item->id) . "',
                                    section_quote_atomic = " . intval(nbf_common::get_param($_POST, 'section_quote_atomic_' . $invoice_item->id)) . ",
                                    page_break = " . intval(nbf_common::nb_strtolower(nbf_common::get_param($_POST, 'page_break_' . $invoice_item->id)) == 'on') . "
                                    WHERE document_id = $document_id
							        AND id = " . $invoice_item->id;
		        $nb_database->setQuery($sql);
		        $nb_database->query();
            }
	    }

        if (count($document_item_ids) && nbf_common::get_param($_REQUEST, 'action') == 'quotes' && $not_awaiting_payment)
        {
            //Cannot be awaiting payment
            $sql = "UPDATE #__nbill_document_items SET quote_awaiting_payment = 0 WHERE id IN (" . implode(",", $document_item_ids) . ")";
            $nb_database->setQuery($sql);
            $nb_database->query();
        }

	    //Delete any invoice items that have been removed
	    foreach ($removed_items as $removed_item)
	    {
		    if (substr($removed_item, 0, 4) != "new_")
		    {
			    $sql = "DELETE FROM #__nbill_document_items WHERE document_id = $document_id AND id = " . intval($removed_item);
			    $nb_database->setQuery($sql);
			    $nb_database->query();
			    //Delete any order associations
			    $sql = "DELETE FROM #__nbill_orders_document WHERE document_id = $document_id AND document_item_id = " . intval($removed_item);
			    $nb_database->setQuery($sql);
			    $nb_database->query();
		    }
	    }

        //Close any gaps in the item ordering and get the next number...
        $sql = "SELECT id FROM #__nbill_document_items WHERE document_id = $document_id ORDER BY ordering, id";
        $nb_database->setQuery($sql);
        $ordered_items = $nb_database->loadResultArray();
        $next_ordering = 0;
        $sql = "UPDATE #__nbill_document_items SET ordering = CASE id ";
        foreach ($ordered_items as $key => $value)
        {
            $sql .= sprintf("WHEN %d THEN %d ", $value, $key);
            $next_ordering++;
        }
        $sql .= "END WHERE id IN (" . implode(",", $ordered_items) . ")";
        $nb_database->setQuery($sql);
        $nb_database->query();

	    //Insert any invoice items that have been added
	    foreach ($added_items as $added_item)
	    {
		    $gross_price_for_item = nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_net_price_for_item');
		    $gross_price_for_item += nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_tax_for_item');
		    $gross_price_for_item += nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_shipping_for_item');
		    $gross_price_for_item += nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_tax_for_shipping');
		    $gross_price_for_item = str_replace(",", ".", $gross_price_for_item . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)

            //Convert zero length strings to zero for numeric data types
            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_net_price_per_unit')) == 0)
            {
                $_POST['nbill_new_' . $added_item . '_net_price_per_unit'] = "0.00";
            }
            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_no_of_units')) == 0)
            {
                $_POST['nbill_new_' . $added_item . '_no_of_units'] = "0.00";
            }
            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_discount_amount')) == 0)
            {
                $_POST['nbill_new_' . $added_item . '_discount_amount'] = "0.00";
            }
            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_net_price_for_item')) == 0)
            {
                $_POST['nbill_new_' . $added_item . '_net_price_for_item'] = "0.00";
            }
            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_tax_rate_for_item')) == 0)
            {
                $_POST['nbill_new_' . $added_item . '_tax_rate_for_item'] = "0.00";
            }
            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_tax_for_item')) == 0)
            {
                $_POST['nbill_new_' . $added_item . '_tax_for_item'] = "0.00";
            }
            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_shipping_for_item')) == 0)
            {
                $_POST['nbill_new_' . $added_item . '_shipping_for_item'] = "0.00";
            }
            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_tax_rate_for_shipping')) == 0)
            {
                $_POST['nbill_new_' . $added_item . '_tax_rate_for_shipping'] = "0.00";
            }
            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_tax_for_shipping')) == 0)
            {
                $_POST['nbill_new_' . $added_item . '_tax_for_shipping'] = "0.00";
            }
            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_auto_renew')) == 0)
            {
                $_POST['nbill_new_' . $added_item . '_auto_renew'] = "0";
            }
            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_unique_invoice')) == 0)
            {
                $_POST['nbill_new_' . $added_item . '_unique_invoice'] = "0";
            }
            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_mandatory')) == 0)
            {
                $_POST['nbill_new_' . $added_item . '_mandatory'] = "0";
            }
            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_' . $added_item . '_item_accepted')) == 0)
            {
                $_POST['nbill_new_' . $added_item . '_item_accepted'] = "0";
            }

		    $sql = "INSERT INTO #__nbill_document_items (vendor_id, document_id, entity_id, nominal_ledger_code, product_code,
							    product_description, detailed_description, net_price_per_unit, no_of_units, discount_amount, discount_description,
							    net_price_for_item, tax_rate_for_item, tax_for_item, shipping_id, shipping_for_item,
                                tax_rate_for_shipping, tax_for_shipping, quote_pay_freq, quote_auto_renew, quote_relating_to,
                                quote_unique_invoice, quote_mandatory, quote_item_accepted, gross_price_for_item, ordering)
							    VALUES
							    (" . nbf_common::get_param($_POST,'vendor_id') . ", $document_id, " . nbf_common::get_param($_POST,'entity_id') . ", " .
							    "'" . nbf_common::get_param($_POST,'nbill_' . nbf_common::get_param($_POST,'vendor_id') . '_ledger_new_' . $added_item) . "', " .
							    "'" . nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_product_code') . "', " .
							    "'" . nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_product_description') . "', " .
                                "'" . nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_detailed_description', null, false, false, true) . "', " .
							    nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_net_price_per_unit') . ", " .
							    nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_no_of_units') . ", " .
							    nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_discount_amount') . ", " .
							    "'" . nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_discount_description') . "', " .
							    nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_net_price_for_item') . ", " .
                                nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_tax_rate_for_item') . ", " .
							    nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_tax_for_item') . ", " .
							    intval(nbf_common::get_param($_POST,'nbill_' . nbf_common::get_param($_POST,'vendor_id') . '_shipping_new_' . $added_item)) . ", " .
							    nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_shipping_for_item') . ", " .
                                nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_tax_rate_for_shipping') . ", " .
							    nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_tax_for_shipping') . ", '" .
                                nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_pay_freq') . "', " .
                                nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_auto_renew') . ", '" .
                                nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_relating_to') . "', " .
                                nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_unique_invoice') . ", " .
                                nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_mandatory') . ", " .
                                nbf_common::get_param($_POST,'nbill_new_' . $added_item . '_item_accepted') . ", " .
							    $gross_price_for_item . ", $next_ordering)";
		    $nb_database->setQuery($sql);
		    $nb_database->query();
            $next_ordering++;
            if (array_search('new_' . $added_item, $new_quote_accepts) !== false)
            {
                $new_quote_accepts[array_search('new_' . $added_item, $new_quote_accepts)] = $nb_database->insertid();
            }
	    }

	    //If the new row section has been filled in, add that as a new item as well
        //Convert zero length strings to zero for numeric data types
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_net_price_per_unit')) == 0)
        {
            $_POST['nbill_new_net_price_per_unit'] = "0.00";
        }
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_no_of_units')) == 0)
        {
            $_POST['nbill_new_no_of_units'] = "0.00";
        }
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_discount_amount')) == 0)
        {
            $_POST['nbill_new_discount_amount'] = "0.00";
        }
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_net_price_for_item')) == 0)
        {
            $_POST['nbill_new_net_price_for_item'] = "0.00";
        }
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_tax_rate_for_item')) == 0)
        {
            $_POST['nbill_new_tax_rate_for_item'] = "0.00";
        }
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_tax_for_item')) == 0)
        {
            $_POST['nbill_new_tax_for_item'] = "0.00";
        }
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_shipping_for_item')) == 0)
        {
            $_POST['nbill_new_shipping_for_item'] = "0.00";
        }
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_tax_rate_for_shipping')) == 0)
        {
            $_POST['nbill_new_tax_rate_for_shipping'] = "0.00";
        }
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_tax_for_shipping')) == 0)
        {
            $_POST['nbill_new_tax_for_shipping'] = "0.00";
        }
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_auto_renew')) == 0)
        {
            $_POST['nbill_new_auto_renew'] = "0";
        }
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_unique_invoice')) == 0)
        {
            $_POST['nbill_new_unique_invoice'] = "0";
        }
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_mandatory')) == 0)
        {
            $_POST['nbill_new_mandatory'] = "0";
        }
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_new_item_accepted')) == 0)
        {
            $_POST['nbill_new_item_accepted'] = "0";
        }
	    $gross_price_for_item = nbf_common::get_param($_POST, 'nbill_new_net_price_for_item');
	    $gross_price_for_item += nbf_common::get_param($_POST, 'nbill_new_tax_for_item');
	    $gross_price_for_item += nbf_common::get_param($_POST, 'nbill_new_shipping_for_item');
	    $gross_price_for_item += nbf_common::get_param($_POST, 'nbill_new_tax_for_shipping');
	    $gross_price_for_item = str_replace(",", ".", $gross_price_for_item . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)

	    if ($gross_price_for_item > 0 || nbf_common::nb_strlen(nbf_common::get_param($_POST,'nbill_new_product_description')) > 0 || nbf_common::nb_strlen(nbf_common::get_param($_POST,'nbill_new_product_code')) > 0)
	    {
            $sql = "INSERT INTO #__nbill_document_items (vendor_id, document_id, entity_id, nominal_ledger_code, product_code,
							    product_description, detailed_description, net_price_per_unit, no_of_units, discount_amount, discount_description,
							    net_price_for_item, tax_rate_for_item, tax_for_item, shipping_id, shipping_for_item,
                                tax_rate_for_shipping, tax_for_shipping, quote_pay_freq, quote_auto_renew, quote_relating_to,
                                quote_unique_invoice, quote_mandatory, quote_item_accepted, gross_price_for_item, ordering)
							    VALUES
							    (" . intval(nbf_common::get_param($_POST,'vendor_id')) . ", $document_id, " . intval(nbf_common::get_param($_POST,'entity_id')) . ", " .
							    "'" . nbf_common::get_param($_POST,'nbill_' . intval(nbf_common::get_param($_POST,'vendor_id')) . '_ledger_new') . "', " .
							    "'" . nbf_common::get_param($_POST,'nbill_new_product_code') . "', " .
							    "'" . nbf_common::get_param($_POST,'nbill_new_product_description') . "', " .
                                "'" . nbf_common::get_param($_POST,'nbill_new_detailed_description', null, false, false, true) . "', " .
							    nbf_common::get_param($_POST,'nbill_new_net_price_per_unit','0') . ", " .
							    nbf_common::get_param($_POST,'nbill_new_no_of_units','0') . ", " .
							    nbf_common::get_param($_POST,'nbill_new_discount_amount','0') . ", " .
							    "'" . nbf_common::get_param($_POST,'nbill_new_discount_description') . "', " .
							    nbf_common::get_param($_POST,'nbill_new_net_price_for_item','0') . ", " .
                                nbf_common::get_param($_POST,'nbill_new_tax_rate_for_item','0') . ", " .
							    nbf_common::get_param($_POST,'nbill_new_tax_for_item','0') . ", " .
							    intval(nbf_common::get_param($_POST,'nbill_' . nbf_common::get_param($_POST,'vendor_id') . '_shipping_new','0')) . ", " .
							    nbf_common::get_param($_POST,'nbill_new_shipping_for_item','0') . ", " .
                                nbf_common::get_param($_POST,'nbill_new_tax_rate_for_shipping','0') . ", " .
							    nbf_common::get_param($_POST,'nbill_new_tax_for_shipping','0') . ", '" .
                                nbf_common::get_param($_POST,'nbill_new_pay_freq') . "', " .
                                nbf_common::get_param($_POST,'nbill_new_auto_renew','0') . ", '" .
                                nbf_common::get_param($_POST,'nbill_new_relating_to') . "', " .
                                nbf_common::get_param($_POST,'nbill_new_unique_invoice','0') . ", " .
                                nbf_common::get_param($_POST,'nbill_new_mandatory','0') . ", " .
                                nbf_common::get_param($_POST,'nbill_new_item_accepted','0') . ", " .
							    $gross_price_for_item . ", $next_ordering)";
		    $nb_database->setQuery($sql);
		    $nb_database->query();
            if (array_search('new', $new_quote_accepts) !== false)
            {
                $new_quote_accepts[array_search('new', $new_quote_accepts)] = $nb_database->insertid();
            }
	    }
    }

	//Refresh list of items so we can work out the totals
	$sql = "SELECT * FROM #__nbill_document_items WHERE document_id = $document_id ORDER BY document_id, ordering, id";
	$nb_database->setQuery($sql);
	$invoice_items = $nb_database->loadObjectList();
	if ($invoice_items == null)
	{
		$invoice_items = array();
	}

	$_POST['total_net'] = 0;
	$_POST['total_tax'] = 0;
	$_POST['total_shipping'] = 0;
	$_POST['total_shipping_tax'] = 0;
	$_POST['total_gross'] = 0;

    $section_discount_net = 0;
    $section_discount_tax = 0;
    $section_discount_gross = 0;
    $section_items = array();

	//Tot it all up
	foreach ($invoice_items as $invoice_item)
	{
        $section_items[] = $invoice_item;
        if ($invoice_item->section_name)
        {
            if ($invoice_item->section_discount_percent != 0)
            {
                foreach ($section_items as $section_item)
                {
                    $this_net = $section_item->net_price_for_item;
                    $this_discount_net = ($this_net / 100) * $invoice_item->section_discount_percent;
                    $this_discount_tax = ($this_discount_net / 100) * $section_item->tax_rate_for_item;
                    $this_discount_gross = float_add($this_discount_net, $this_discount_tax);
                    $section_discount_net = float_add($section_discount_net, $this_discount_net);
                    $section_discount_tax = float_add($section_discount_tax, $this_discount_tax);
                    $section_discount_gross = float_add($section_discount_gross, $this_discount_gross);
                }
            }
            $section_items = array();
        }
		$_POST['total_net'] = float_add($_POST['total_net'], str_replace(",", ".", $invoice_item->net_price_for_item . ""), 'currency_grand'); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
		$_POST['total_tax'] = float_add($_POST['total_tax'], str_replace(",", ".", $invoice_item->tax_for_item . ""), 'currency_grand'); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
		$_POST['total_shipping'] = float_add($_POST['total_shipping'], str_replace(",", ".", $invoice_item->shipping_for_item . ""), 'currency_grand'); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
		$_POST['total_shipping_tax'] = float_add($_POST['total_shipping_tax'], str_replace(",", ".", $invoice_item->tax_for_shipping . ""), 'currency_grand'); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
		$_POST['total_gross'] = float_add($_POST['total_gross'], str_replace(",", ".", $invoice_item->gross_price_for_item . ""), 'currency_grand'); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
        if ($invoice_item->section_name && $invoice_item->section_discount_percent != 0)
        {
            $_POST['total_net'] = float_subtract($_POST['total_net'], $section_discount_net);
            $_POST['total_tax'] = float_subtract($_POST['total_tax'], $section_discount_tax);
            $_POST['total_gross'] = float_subtract($_POST['total_gross'], $section_discount_gross);
            $section_discount_net = 0;
            $section_discount_tax = 0;
            $section_discount_gross = 0;
        }
	}
	$_POST['total_net'] = str_replace(",", ".", $_POST['total_net'] . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
	$_POST['total_tax'] = str_replace(",", ".", $_POST['total_tax'] . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
	$_POST['total_shipping'] = str_replace(",", ".", $_POST['total_shipping'] . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
	$_POST['total_shipping_tax'] = str_replace(",", ".", $_POST['total_shipping_tax'] . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)
	$_POST['total_gross'] = str_replace(",", ".", $_POST['total_gross'] . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot (for db purposes)

	//Update global invoice data
	$_POST['nominal_ledger_code'] = nbf_common::get_param($_POST,'ledger_' . nbf_common::get_param($_POST,'vendor_id'));
	$_POST['vendor_address'] = str_replace("\\r\\n", "\n", nbf_common::get_param($_POST,'vendor_address', null, true));
	$_POST['billing_address'] = str_replace("\\r\\n", "\n", nbf_common::get_param($_POST,'billing_address', null, true));
	$_POST['payment_instructions'] = str_replace("\\r\\n", "\n", nbf_common::get_param($_POST,'payment_instructions',"",true,false,true));
    $_POST['small_print'] = str_replace("\\r\\n", "\n", nbf_common::get_param($_POST,'small_print',"",true,false,true));
	$_POST['delivery_small_print'] = str_replace("\\r\\n", "\n", nbf_common::get_param($_POST,'delivery_small_print',"",true,false,true));
    //$_POST['discount_id'] = intval(nbf_common::get_param($_POST, 'discount_id_' . nbf_common::get_param($_POST,'vendor_id')));

	$posted_document_date = nbf_common::get_param($_POST, 'document_date');
    $posted_due_date = nbf_common::get_param($_POST, 'due_date');
	$posted_write_off_date = nbf_common::get_param($_POST,'date_written_off');

	if (nbf_common::nb_strlen($posted_document_date) > 5)
	{
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
		$date_parts = nbf_date::get_date_parts($posted_document_date, nbf_common::get_date_format(true));
		if (count($date_parts) == 3)
		{
			$_POST['document_date'] = nbf_common::nb_mktime(0, 0, 0, $date_parts['m'], $date_parts['d'], $date_parts['y']);
		}
	}

    if (nbf_common::nb_strlen($posted_due_date) > 5)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
        $date_parts = nbf_date::get_date_parts($posted_due_date, nbf_common::get_date_format(true));
        if (count($date_parts) == 3)
        {
            $_POST['due_date'] = nbf_common::nb_mktime(0, 0, 0, $date_parts['m'], $date_parts['d'], $date_parts['y']);
        }
    }

	if (nbf_common::get_param($_POST,'written_off'))
	{
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
		$date_parts = nbf_date::get_date_parts($posted_write_off_date, nbf_common::get_date_format(true));
		if (count($date_parts) == 3)
		{
			$_POST['date_written_off'] = nbf_common::nb_mktime(0, 0, 0, $date_parts['m'], $date_parts['d'], $date_parts['y']);
		}
	}
	else
	{
		$_POST['date_written_off'] = "";
	}

	$nb_database->bind_and_save("#__nbill_document", $_POST);

    //If quote status has been reverted, remove any reference to previous payment attempt
    if ($is_quote && $document_id)
    {
        $sql = "UPDATE #__nbill_document_items SET quote_g_tx_id = 0 WHERE document_id = " . intval($document_id) . " AND quote_item_accepted = 0";
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

    //If this is a credit note, associated with an invoice, mark the invoice as refunded or part-refunded
    if (nbf_common::get_param($_REQUEST, 'action') == 'credits')
    {
        $related_doc_id = intval(nbf_common::get_param($_REQUEST, 'related_document_id'));
        if ($related_doc_id)
        {
            $sql = "SELECT total_gross FROM #__nbill_document WHERE document_type = 'CR' AND related_document_id = $related_doc_id";
            $nb_database->setQuery($sql);
            $credits = $nb_database->loadObjectList();
            $total_gross = 0;
            foreach ($credits as $credit)
            {
                $total_gross = float_add($total_gross, $credit->total_gross, 'currency_grand');
            }
            if ($total_gross > 0)
            {
                $sql = "SELECT total_gross FROM #__nbill_document WHERE id = $related_doc_id";
                $nb_database->setQuery($sql);
                $invoice_total = $nb_database->loadResult();

                if (float_gtr($invoice_total, $total_gross))
                {
                    //partial
                    $sql = "UPDATE #__nbill_document SET refunded_in_full = 0, partial_refund = 1 WHERE id = $related_doc_id";
                }
                else
                {
                    //full
                    $sql = "UPDATE #__nbill_document SET refunded_in_full = 1, partial_refund = 0 WHERE id = $related_doc_id";
                }
                $nb_database->setQuery($sql);
                $nb_database->query();
            }
        }
    }

	if ($insert)
	{
		switch (nbf_common::get_param($_REQUEST, 'action'))
        {
            case "credits":
                nbf_common::fire_event("credit_note_created", array("id"=>$document_id));
                break;
            case "quotes":
                nbf_common::fire_event("quote_created", array("id"=>$document_id));
                break;
            default:
		        nbf_common::fire_event("invoice_created", array("id"=>$document_id));
		}
	}
	else
	{
		nbf_common::fire_event("record_updated", array("type"=>substr(nbf_common::get_param($_REQUEST, 'action'), 0, strlen(nbf_common::get_param($_REQUEST, 'action')) -1), "id"=>$document_id));
	}

    //Add/Update product(s) if required
    if ((nbf_common::get_param($_REQUEST, 'product_added') && strtolower(nbf_common::get_param($_REQUEST, 'product_added')) != 'false')
        || (nbf_common::get_param($_REQUEST, 'product_updated') && strtolower(nbf_common::get_param($_REQUEST, 'product_updated')) != 'false'))
    {
        if (!$config->use_legacy_document_editor) {
            if ($config->edit_products_in_documents) {
                foreach ($added_products as $added_product)
                {
                    $_REQUEST['product_updated'] = '';
                    $_REQUEST['product_added'] = 'true';
                    update_products($added_product['sku'], $added_product['prev_sku'], $added_product['name'], $added_product['description'], $added_product['ledger_code'], $added_product['net_price'], $added_product['prev_price'], $added_product['pay_freq']);
                }
                foreach ($updated_products as $updated_product)
                {
                    $_REQUEST['product_added'] = '';
                    $_REQUEST['product_updated'] = 'true';
                    update_products($updated_product['sku'], $updated_product['prev_sku'], $updated_product['name'], $updated_product['description'], $updated_product['ledger_code'], $updated_product['net_price'], $updated_product['prev_price'], $updated_product['pay_freq']);
                }
            }
        } else {
            foreach ($invoice_items as $document_item)
            {
                $sku = nbf_common::get_param($_REQUEST, 'nbill_' . $document_item->id . '_product_code');
                if (strlen($sku) > 0 && (strpos($sku, '[') === false || strpos($sku, '=') === false))
                {
                    $prev_sku = nbf_common::get_param($_REQUEST, 'nbill_' . $document_item->id . '_product_code_orig');
                    $name = nbf_common::get_param($_REQUEST, 'nbill_' . $document_item->id . '_product_description', '', true);
                    $description = nbf_common::get_param($_REQUEST, 'nbill_' . $document_item->id . '_detailed_description', '', true, false, true);
                    $ledger_code = nbf_common::get_param($_REQUEST, 'nbill_' . nbf_common::get_param($_REQUEST, 'vendor_id') . '_ledger_' . $document_item->id);
                    $net_price = nbf_common::get_param($_REQUEST, 'nbill_' . $document_item->id . '_net_price_per_unit');
                    $prev_price = nbf_common::get_param($_REQUEST, 'nbill_' . $document_item->id . '_net_price_per_unit_orig');
                    $pay_freq = nbf_common::get_param($_REQUEST, 'nbill_' . $document_item->id . '_pay_freq');
                    update_products($sku, $prev_sku, $name, $description, $ledger_code, $net_price, $prev_price, $pay_freq);
                }
            }
            foreach ($added_items as $added_item)
            {
                $sku = nbf_common::get_param($_REQUEST, 'nbill_new_' . $added_item . '_product_code');
                if (strlen($sku) > 0 && (strpos($sku, '[') === false || strpos($sku, '=') === false))
                {
                    $prev_sku = nbf_common::get_param($_REQUEST, 'nbill_new_' . $added_item . '_product_code_orig');
                    $name = nbf_common::get_param($_REQUEST, 'nbill_new_' . $added_item . '_product_description', '', true);
                    $description = nbf_common::get_param($_REQUEST, 'nbill_new_' . $added_item . '_detailed_description', '', true, false, true);
                    $ledger_code = nbf_common::get_param($_REQUEST, 'nbill_' . nbf_common::get_param($_REQUEST, 'vendor_id') . '_ledger_new_' . $added_item);
                    $net_price = nbf_common::get_param($_REQUEST, 'nbill_new_' . $added_item . '_net_price_per_unit');
                    $prev_price = nbf_common::get_param($_REQUEST, 'nbill_new_' . $added_item . '_net_price_per_unit_orig');
                    $pay_freq = nbf_common::get_param($_REQUEST, 'nbill_new_' . $added_item . '_pay_freq');
                    update_products($sku, $prev_sku, $name, $description, $ledger_code, $net_price, $prev_price, $pay_freq);
                }
            }
            $sku = nbf_common::get_param($_REQUEST, 'nbill_new_product_code');
            if (strlen($sku) > 0 && (strpos($sku, '[') === false || strpos($sku, '=') === false))
            {
                $prev_sku = nbf_common::get_param($_REQUEST, 'nbill_new_product_code_orig');
                $name = nbf_common::get_param($_REQUEST, 'nbill_new_product_description', '', true);
                $description = nbf_common::get_param($_REQUEST, 'nbill_new_detailed_description', '', true, false, true);
                $ledger_code = nbf_common::get_param($_REQUEST, 'nbill_' . nbf_common::get_param($_REQUEST, 'vendor_id') . '_ledger_new');
                $net_price = nbf_common::get_param($_REQUEST, 'nbill_new_net_price_per_unit');
                $prev_price = nbf_common::get_param($_REQUEST, 'nbill_new_net_price_per_unit_orig');
                $pay_freq = nbf_common::get_param($_REQUEST, 'nbill_new_pay_freq');
                update_products($sku, $prev_sku, $name, $description, $ledger_code, $net_price, $prev_price, $pay_freq);
            }
        }
    }

	//Put dates back into Y-m-d format in case we are applying
	$_POST['document_date'] = $posted_document_date;
	$_POST['date_written_off'] = $posted_write_off_date;

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'save', get_defined_vars());

    if ($is_quote && nbf_common::nb_strlen($new_task) > 0)
    {
        //Offer to perform action(s) on change of status...
        offer_quote_action($document_id, $new_task, $new_quote_accepts);
        return false;
    }

    return true;
}

function update_products($sku, $prev_sku, $name, $description, $ledger_code, $net_price, $prev_price, $pay_freq)
{
    $nb_database = nbf_cms::$interop->database;

    $product = null;
    $sql = "SELECT id, vendor_id, category, product_code, name, description, nominal_ledger_code FROM #__nbill_product WHERE product_code = '$sku'";
    $nb_database->setQuery($sql);
    $nb_database->loadObject($product);

    if (nbf_common::get_param($_REQUEST, 'product_added') && strtolower(nbf_common::get_param($_REQUEST, 'product_added')) != 'false')
    {
        if (!$product)
        {
            //Add
            $sql = "SELECT id FROM #__nbill_product_category
                    WHERE vendor_id = " . intval(nbf_common::get_param($_REQUEST, 'vendor_id')) . "
                    AND parent_id = 0";
            $nb_database->setQuery($sql);
            $root_cat_id = intval($nb_database->loadResult());
            if (!$root_cat_id)
            {
                $sql = "SELECT id FROM #__nbill_product_category
                        WHERE vendor_id = " . intval(nbf_common::get_param($_REQUEST, 'vendor_id')) . "
                        ORDER BY id";
                $nb_database->setQuery($sql);
                $root_cat_id = intval($nb_database->loadResult());
            }

            $product_array = array();
            $product_array['vendor_id'] = nbf_common::get_param($_REQUEST, 'vendor_id');
            $product_array['category'] = $root_cat_id;
            $product_array['product_code'] = $sku;
            $product_array['name'] = $name;
            $product_array['description'] = $description;
            $product_array['nominal_ledger_code'] = $ledger_code;
            $nb_database->bind_and_save("#__nbill_product", $product_array);
            $product_id = $nb_database->insertid();

            //Insert price
            $sql = "INSERT INTO #__nbill_product_price (vendor_id, product_id, currency_code, net_price_one_off)
                        VALUES (" . intval(nbf_common::get_param($_REQUEST, 'vendor_id')) . ", " . intval($product_id) . ",
                        '" . nbf_common::get_param($_REQUEST, 'currency') . "', '$net_price')";
            $nb_database->setQuery($sql);
            $nb_database->query();
            nbf_common::fire_event("product_created", array("id"=>$product_id));
        }
    }
    if (nbf_common::get_param($_REQUEST, 'product_updated') && strtolower(nbf_common::get_param($_REQUEST, 'product_updated')) != 'false')
    {
        if ($product)
        {
            $product_updated = false;
            if ($product->name != $name ||
                $product->description != $description ||
                $product->nominal_ledger_code != $ledger_code)
            {
                //Do the update
                $product_array = get_object_vars($product);
                $product_array['name'] = $name;
                $product_array['description'] = $description;
                $product_array['nominal_ledger_code'] = $ledger_code;
                $nb_database->bind_and_save("#__nbill_product", $product_array);
                $product_updated = true;
            }
            //Check for price change
            if ($pay_freq)
            {
                $col_name = nbf_common::convert_pay_freq($pay_freq);
                $sql = "SELECT $col_name
                        FROM #__nbill_product_price
                        INNER JOIN #__nbill_product ON #__nbill_product_price.product_id = #__nbill_product.id
                        WHERE #__nbill_product.vendor_id = " . intval(nbf_common::get_param($_REQUEST, 'vendor_id')) . "
                        AND #__nbill_product.product_code = '$sku'
                        AND #__nbill_product_price.currency_code = '" . nbf_common::get_param($_REQUEST, 'currency') ."'";
                $nb_database->setQuery($sql);
                $current_price = $nb_database->loadResult();
                if ($current_price !== null && $current_price != $net_price)
                {
                    //Do the price update
                    $sql = "UPDATE #__nbill_product_price SET $col_name = '$net_price'
                            WHERE vendor_id = " . intval(nbf_common::get_param($_REQUEST, 'vendor_id')) . "
                            AND product_id = " . intval($product->id) . "
                            AND currency_code = '" . nbf_common::get_param($_REQUEST, 'currency') . "'";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                    $product_updated = true;
                }
            }
            else if ($prev_sku == $sku && $prev_price != $net_price)
            {
                //Find the payment frequency that matches the previous amount
                $current_prices = null;
                $sql = "SELECT #__nbill_product_price.* FROM #__nbill_product_price
                        INNER JOIN #__nbill_product ON #__nbill_product_price.product_id = #__nbill_product.id
                        WHERE #__nbill_product.vendor_id = " . intval(nbf_common::get_param($_REQUEST, 'vendor_id')) . "
                        AND #__nbill_product.product_code = '$sku'
                        AND #__nbill_product_price.currency_code = '" . nbf_common::get_param($_REQUEST, 'currency') ."'";
                $nb_database->setQuery($sql);
                $nb_database->loadObject($current_prices);
                if ($current_prices)
                {
                    foreach (get_object_vars($current_prices) as $price_column=>$price_value)
                    {
                        if (substr($price_column, 0, 10) == "net_price_" && $price_value == $prev_price)
                        {
                            //Do the price update
                            $sql = "UPDATE #__nbill_product_price SET $price_column = '$net_price'
                                    WHERE vendor_id = " . intval(nbf_common::get_param($_REQUEST, 'vendor_id')) . "
                                    AND product_id = " . intval($product->id) . "
                                    AND currency_code = '" . nbf_common::get_param($_REQUEST, 'currency') . "'";
                            $nb_database->setQuery($sql);
                            $nb_database->query();
                            $product_updated = true;
                            break;
                        }
                    }
                }
            }

            if ($product_updated)
            {
                nbf_common::fire_event("record_updated", array("type"=>"product", "id"=>$product->id));
            }
        }
    }
}

function saveCopy($id)
{
    $nb_database = nbf_cms::$interop->database;

    $_POST['document_no'] = ''; //Force generation of new number
    $_POST['status'] = 'AA'; //Status of a copied quote should be new
    $_POST['paid_in_full'] = '0';
    $_POST['partial_payment'] = '0';
    $_POST['refunded_in_full'] = '0';
    $_POST['partial_refund'] = '0';
    $_POST['written_off'] = '0';
    $_POST['date_written_off'] = '';
    $_POST['document_date'] = nbf_common::nb_date(nbf_common::get_date_format());
    $_POST['related_document_id'] = 0;

    $config = nBillConfigurationService::getInstance()->getConfig();
    if (!$config->use_legacy_document_editor) {
        $number_factory = new nBillNumberFactory($config);
        $currency_factory = new nBillCurrencyFactory();
        $line_item_factory = new nBillLineItemFactory($number_factory);
        $currency_service = new nBillCurrencyService($currency_factory->createCurrencyMapper(nbf_cms::$interop->database));
        $line_item_mapper = $line_item_factory->createMapper(nbf_cms::$interop->database, $currency_service);
        $line_item_service = $line_item_factory->createService($line_item_mapper);

        $line_items_json = nbf_common::get_param($_REQUEST, 'line_items', '', true, false, true, true);
        $item_collection = $line_item_mapper->mapLineItemsFromJson($line_items_json, $currency_factory->createCurrencyMapper(nbf_cms::$interop->database), nbf_common::get_param($_REQUEST, 'document_type'), $currency_service->findCurrency(nbf_common::get_param($_POST, 'currency')));
        foreach ($item_collection->sections as $section) {
            //$section->document_id = null;
            foreach ($section->line_items as $line_item) {
                $line_item->document_id = null;
                $line_item->id = null;
            }
        }
        $_REQUEST['line_items'] = json_encode($item_collection);
        saveInvoice(null);
        $new_document_id = intval(nbf_common::get_param($_POST,'id'));
    } else {
        //Take note of any added items as we don't want to overwrite them with saved items
        $added_items = explode(",", nbf_common::get_param($_POST, 'added_items'));
        if (count($added_items))
        {
            sort($added_items);
            $added_item = intval(trim($added_items[count($added_items) - 1]));
        }
        else
        {
            $added_item = 0;
        }
        $first_newly_added_item = $added_item;

        //Convert invoice items into new entries so they can be saved
        $sql = "SELECT id FROM #__nbill_document_items WHERE document_id = " . intval($id) . " ORDER BY ordering, id";
        $nb_database->setQuery($sql);
        $document_items = $nb_database->loadObjectList();
        foreach ($document_items as $document_item)
        {
            $added_item++;
            $_POST["nbill_new_" . ($added_item) . "_product_code"] = @$_POST["nbill_" . $document_item->id . "_product_code"];
            $_POST["nbill_new_" . ($added_item) . "_product_description"] = @$_POST["nbill_" . $document_item->id . "_product_description"];
            $_POST["nbill_new_" . ($added_item) . "_detailed_description"] = @$_POST["nbill_" . $document_item->id . "_detailed_description"];
            $_POST["nbill_new_" . ($added_item) . "_net_price_per_unit"] = @$_POST["nbill_" . $document_item->id . "_net_price_per_unit"];
            $_POST["nbill_new_" . ($added_item) . "_no_of_units"] = @$_POST["nbill_" . $document_item->id . "_no_of_units"];
            $_POST["nbill_new_" . ($added_item) . "_discount_description"] = @$_POST["nbill_" . $document_item->id . "_discount_description"];
            $_POST["nbill_new_" . ($added_item) . "_discount_amount"] = @$_POST["nbill_" . $document_item->id . "_discount_amount"];
            $_POST["nbill_new_" . ($added_item) . "_net_price_for_item"] = @$_POST["nbill_" . $document_item->id . "_net_price_for_item"];
            $_POST["nbill_new_" . ($added_item) . "_tax_rate_for_item"] = @$_POST["nbill_" . $document_item->id . "_tax_rate_for_item"];
            $_POST["nbill_new_" . ($added_item) . "_tax_for_item"] = @$_POST["nbill_" . $document_item->id . "_tax_for_item"];
            $_POST["nbill_new_" . ($added_item) . "_shipping_for_item"] = @$_POST["nbill_" . $document_item->id . "_shipping_for_item"];
            $_POST["nbill_new_" . ($added_item) . "_tax_rate_for_shipping"] = @$_POST["nbill_" . $document_item->id . "_tax_rate_for_shipping"];
            $_POST["nbill_new_" . ($added_item) . "_tax_for_shipping"] = @$_POST["nbill_" . $document_item->id . "_tax_for_shipping"];
            $_POST["nbill_new_" . ($added_item) . "_pay_freq"] = @$_POST["nbill_" . $document_item->id . "_pay_freq"];
            $_POST["nbill_new_" . ($added_item) . "_auto_renew"] = @$_POST["nbill_" . $document_item->id . "_auto_renew"];
            $_POST["nbill_new_" . ($added_item) . "_relating_to"] = @$_POST["nbill_" . $document_item->id . "_relating_to"];

            $_POST["nbill_new_" . ($added_item) . "_unique_invoice"] = @$_POST["nbill_" . $document_item->id . "_unique_invoice"];
            $_POST["nbill_new_" . ($added_item) . "_mandatory"] = @$_POST["nbill_" . $document_item->id . "_mandatory"];
            $vendor_id = intval(nbf_common::get_param($_POST, 'vendor_id'));
            $_POST['nbill_' . $vendor_id . '_ledger_new_' . $added_item] = nbf_common::get_param($_POST, 'nbill_' . $vendor_id . '_ledger_' . $document_item->id);
            $_POST['nbill_' . $vendor_id . '_shipping_new_' . $added_item] = intval(nbf_common::get_param($_POST, 'nbill_' . $vendor_id . '_shipping_' . $document_item->id));
            unset($_POST["nbill_" . $document_item->id . "_product_code"]);
            unset($_POST["nbill_" . $document_item->id . "_product_description"]);
            unset($_POST["nbill_" . $document_item->id . "_detailed_description"]);
            unset($_POST["nbill_" . $document_item->id . "_net_price_per_unit"]);
            unset($_POST["nbill_" . $document_item->id . "_no_of_units"]);
            unset($_POST["nbill_" . $document_item->id . "_discount_description"]);
            unset($_POST["nbill_" . $document_item->id . "_discount_amount"]);
            unset($_POST["nbill_" . $document_item->id . "_net_price_for_item"]);
            unset($_POST["nbill_" . $document_item->id . "_tax_rate_for_item"]);
            unset($_POST["nbill_" . $document_item->id . "_tax_for_item"]);
            unset($_POST["nbill_" . $document_item->id . "_shipping_for_item"]);
            unset($_POST["nbill_" . $document_item->id . "_tax_rate_for_shipping"]);
            unset($_POST["nbill_" . $document_item->id . "_tax_for_shipping"]);
            unset($_POST["nbill_" . $document_item->id . "_pay_freq"]);
            unset($_POST["nbill_" . $document_item->id . "_auto_renew"]);
            unset($_POST["nbill_" . $document_item->id . "_relating_to"]);
            unset($_POST["nbill_" . $document_item->id . "_unique_invoice"]);
            unset($_POST["nbill_" . $document_item->id . "_mandatory"]);
            unset($_POST['nbill_' . $vendor_id . '_ledger_' . $document_item->id]);
            unset($_POST['nbill_' . $vendor_id . '_shipping_' . $document_item->id]);
        }

        /*$saved_item_id = 0;
        $delete_keys = array();
        ksort($_POST);
        foreach ($_POST as $key=>$value)
        {
            $key_parts = explode("_", $key);
            if (count($key_parts) > 2 && $key_parts[0] == 'nbill')
            {
                if (is_numeric($key_parts[1]) && intval($key_parts[1]) == $key_parts[1])
                {
                    $itemkey = implode("_", array_slice($key_parts, 2));
                    switch ($itemkey)
                    {
                        case "product_code":
                        case "product_description":
                        case "detailed_description":
                        case "net_price_per_unit":
                        case "no_of_units":
                        case "discount_description":
                        case "discount_amount":
                        case "net_price_for_item":
                        case "tax_rate_for_item":
                        case "tax_for_item":
                        case "shipping_for_item":
                        case "tax_rate_for_shipping":
                        case "tax_for_shipping":
                        case "pay_freq":
                        case "auto_renew":
                        case "relating_to":
                        case "unique_invoice":
                        case "mandatory": //item_accepted is omitted as a new quote should not be accepted automatically when copied
                        case "section_name":
                        case "section_discount_title":
                        case "section_discount_percent":
                        case "section_quote_atomic":
                        case "ordering":
                            $ordering = intval(nbf_common::get_param($_POST, "ordering_" . $key_parts[1]));
                            if ($saved_item_id != $key_parts[1])
                            {
                                $added_item++;
                                $saved_item_id = $key_parts[1];
                            }
                            $newkey = "nbill_new_" . ($added_item + $ordering) . "_" . $itemkey;
                            $_POST[$newkey] = $value;
                            $delete_keys[] = $key;

                            //Convert shipping, ledger
                            $vendor_id = intval(nbf_common::get_param($_POST, 'vendor_id'));
                            $ledger_key = 'nbill_' . $vendor_id . '_ledger_' . $key_parts[1];
                            $shipping_key = 'nbill_' . $vendor_id . '_shipping_' . $key_parts[1];
                            $_POST['nbill_' . $vendor_id . '_ledger_new_' . ($added_item + $ordering)] = nbf_common::get_param($_POST, $ledger_key);
                            $_POST['nbill_' . $vendor_id . '_shipping_new_' . ($added_item + $ordering)] = intval(nbf_common::get_param($_POST, $shipping_key));
                            $delete_keys[] = $ledger_key;
                            $delete_keys[] = $shipping_key;
                            break;
                    }
                }
            }
        }

        foreach ($delete_keys as $delete_key)
        {
            if ($delete_key)
            {
                unset($_POST[$delete_key]);
            }
        }*/

        //Add saved items to the added_items array
        for ($index = $first_newly_added_item; $index <= $added_item; $index++)
        {
            $added_items[] = $index;
        }
        $_POST['added_items'] = implode(",", array_filter($added_items));

        saveInvoice(null);

        //Copy sections and page breaks
        $new_document_id = intval(nbf_common::get_param($_POST,'id'));
        $sql = "SELECT id, ordering, section_name, section_discount_title, section_discount_percent, section_quote_atomic, page_break
                FROM #__nbill_document_items WHERE document_id = " . intval($id) . " ORDER BY ordering, id";
        $nb_database->setQuery($sql);
        $old_sections = $nb_database->loadObjectList();
        $sql = "SELECT id FROM #__nbill_document_items WHERE document_id = $new_document_id ORDER BY ordering, id";
        $nb_database->setQuery($sql);
        $new_sections = $nb_database->loadObjectList();
        if ($old_sections && $new_sections && count($old_sections) <= count($new_sections))
        {
            for ($section_index = 0; $section_index < count($old_sections); $section_index++)
            {
                if (strlen($old_sections[$section_index]->section_name) > 0)
                {
                    $sql = "UPDATE #__nbill_document_items SET
                            section_name = '" . $old_sections[$section_index]->section_name . "',
                            section_discount_title = '" . $old_sections[$section_index]->section_discount_title . "',
                            section_discount_percent = '" . $old_sections[$section_index]->section_discount_percent . "',
                            section_quote_atomic = " . intval($old_sections[$section_index]->section_quote_atomic) . ",
                            page_break = " . intval($old_sections[$section_index]->page_break) . "
                            WHERE id = " . intval($new_sections[$section_index]->id);
                }
                else
                {
                    $sql = "UPDATE #__nbill_document_items SET
                            page_break = " . intval($old_sections[$section_index]->page_break) . "
                            WHERE id = " . intval($new_sections[$section_index]->id);
                }
                $nb_database->setQuery($sql);
                $nb_database->query();
            }
        }
    }

    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");
    nbf_payment::refresh_document_totals($new_document_id);
}

function deleteInvoices($id_array)
{
	$nb_database = nbf_cms::$interop->database;

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'delete', get_defined_vars());
    switch (nbf_common::get_param($_REQUEST, 'action'))
    {
        case "credits":
            nbf_common::fire_event("credit_note_deleted", array("ids"=>implode(",", $id_array)));
            $doc_type = "CR";
            break;
        case "quotes":
            nbf_common::fire_event("quote_deleted", array("ids"=>implode(",", $id_array)));
            $doc_type = "QU";
            break;
        case "silent":
            $doc_type = "IV";
            break;
        default:
            nbf_common::fire_event("invoice_deleted", array("ids"=>implode(",", $id_array)));
            $doc_type = "IV";
            break;
	}

    //On deletion of a credit note associated with an invoice, unmark the related invoice as refunded, if applicable
    if ($doc_type == "CR")
    {
        foreach ($id_array as $id)
        {
            $sql = "SELECT related_document_id FROM #__nbill_document WHERE id = " . intval($id);
            $nb_database->setQuery($sql);
            $related_doc_id = intval($nb_database->loadResult());
            if ($related_doc_id)
            {
                $sql = "SELECT total_gross FROM #__nbill_document WHERE document_type = 'CR' AND related_document_id = $related_doc_id AND id != " . intval($id);
                $nb_database->setQuery($sql);
                $credits = $nb_database->loadObjectList();
                $total_gross = 0;
                foreach ($credits as $credit)
                {
                    $total_gross = float_add($total_gross, $credit->total_gross, 'currency_grand');
                }
                if ($total_gross > 0)
                {
                    $sql = "SELECT total_gross FROM #__nbill_document WHERE id = $related_doc_id";
                    $nb_database->setQuery($sql);
                    $invoice_total = $nb_database->loadResult();

                    if (float_gtr($invoice_total, $total_gross))
                    {
                        //partial
                        $sql = "UPDATE #__nbill_document SET refunded_in_full = 0, partial_refund = 1 WHERE id = $related_doc_id";
                    }
                    else
                    {
                        //full
                        $sql = "UPDATE #__nbill_document SET refunded_in_full = 1, partial_refund = 0 WHERE id = $related_doc_id";
                    }
                }
                else
                {
                    $sql = "UPDATE #__nbill_document SET refunded_in_full = 0, partial_refund = 0 WHERE id = $related_doc_id";
                }
                $nb_database->setQuery($sql);
                $nb_database->query();
            }
        }
    }

    //On deletion of invoice, remove relationship with credit note (same for quotes with orders/invoices)
    $sql = "UPDATE #__document SET related_document_id = 0 WHERE related_document_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();
    $sql = "UPDATE #__orders SET related_quote_id = 0 WHERE related_quote_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();

    //If associated with an income item, remove the association
    $sql = "SELECT transaction_id FROM #__nbill_document_transaction WHERE document_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $incomes = $nb_database->loadResultArray();
    if (count($incomes))
    {
        $sql = "SELECT id, invoice_ids FROM #__nbill_transaction WHERE id IN (" . implode(",", $incomes) . ")";
        $nb_database->setQuery($sql);
        $transactions = $nb_database->loadObjectList();
        if ($transactions && count($transactions))
        {
            foreach ($transactions as $transaction)
            {
                $tx_doc_ids = explode(",", $transaction->invoice_ids);
                for($i=0; $i<count($tx_doc_ids); $i++)
                {
                    $tx_doc_ids[$i] = trim($tx_doc_ids[$i]);
                }
                $new_tx_doc_ids = array_diff($tx_doc_ids, $id_array);
                $sql = "UPDATE #__nbill_transaction SET invoice_ids = '" . implode(",", $new_tx_doc_ids) . "' WHERE id = " . $transaction->id;
                $nb_database->setQuery($sql);
                $nb_database->query();
            }
        }
        $sql = "DELETE FROM #__nbill_document_transaction WHERE document_id IN (" . implode(",", $id_array) . ")";
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

	$sql = "DELETE FROM #__nbill_orders_document WHERE document_id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

	$sql = "DELETE FROM #__nbill_document_items WHERE document_id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

	$sql = "DELETE FROM #__nbill_document WHERE id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

    $sql = "UPDATE #__nbill_orders SET related_quote_id = 0 WHERE related_quote_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();

    $sql = "UPDATE #__nbill_document SET related_document_id = 0 WHERE related_document_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();

    //Detach any attachments
    $sql = "DELETE FROM #__nbill_supporting_docs WHERE associated_doc_type = '$doc_type' AND associated_doc_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();
}

function printInvoices()
{
	nbf_globals::$message = sprintf(NBILL_PRINT_PREVIEW_DONE, "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=" . nbf_common::get_param($_REQUEST, 'action') . "\">", "</a>");
}

function printPDFPopup($id_array, $return_contents = false, $send_pdf_to_file = false, $pdf_file_name = "", $restrict_to_user = false)
{
	$nb_database = nbf_cms::$interop->database;

	if (count($id_array) > 0)
	{
        //Check for existence of support PDF generator
        $generator = 'dompdf';
        $path_to_pdfwriter = nbf_common::get_path_to_pdf_writer($generator);
        if ($generator === null) {
            die("PDF Generator Not Found!");
        }

		//We have forgotten the template name, so get it again (get first invoice number while we're at it)
		$sql = "SELECT #__nbill_vendor.invoice_template_name, #__nbill_vendor.credit_template_name, #__nbill_vendor.quote_template_name,
                    #__nbill_document.document_type, #__nbill_document.document_no FROM #__nbill_document INNER JOIN #__nbill_vendor ON
					#__nbill_document.vendor_id = #__nbill_vendor.id WHERE #__nbill_document.id = " . $id_array[0];
		$nb_database->setQuery($sql);
        $doc_info = null;
        $nb_database->loadObject($doc_info);
        switch ($doc_info->document_type)
        {
            case 'CR':
                $template_name = $doc_info->credit_template_name;
                break;
            case 'QU':
                $template_name = $doc_info->quote_template_name;
                break;
            default:
		        $template_name = $doc_info->invoice_template_name;
                break;
        }
        $first_document_no = $doc_info->document_no;

		if (nbf_common::nb_strlen(trim($template_name)) == 0)
		{
			$template_name = "default";
		}
		$template_path = nbf_cms::$interop->nbill_fe_base_path . "/templates/$template_name";

        //Load colour scheme
        $sql = "SELECT title_colour, heading_bg_colour, heading_fg_colour FROM #__nbill_configuration WHERE id = 1";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($colour_scheme);
        $title_colour = $colour_scheme->title_colour;
        $heading_bg_colour = $colour_scheme->heading_bg_colour;
        $heading_fg_colour = $colour_scheme->heading_fg_colour;

		//Include the CSS file, if applicable
		if (file_exists("$template_path/template.css")) {
			$css = file_get_contents("$template_path/template.css");
		} else {
			$css = "";
		}
        $css = str_replace('##title_colour##', $title_colour, $css);
        $css = str_replace('##heading_bg_colour##', $heading_bg_colour, $css);
        $css = str_replace('##heading_fg_colour##', $heading_fg_colour, $css);

        //Repeat body styling inline, as PDF generator doesn't always pick it up otherwise
		$bodypos = @nbf_common::nb_strpos($css, "body");
		$body_start = @nbf_common::nb_strpos($css, "{", $bodypos) + 1;
		$body_end = @nbf_common::nb_strpos($css, "}", $body_start);
		$body_style = @trim(@substr($css, $body_start, $body_end - $body_start));

		$header = '<?xml version="1.0" encoding="' . nbf_cms::$interop->char_encoding . '"?>
                    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
					<head><title>Invoice</title>';
        $header .= '<meta http-equiv="Content-Type" content="text/html; charset=' . nbf_cms::$interop->char_encoding . '" />';
		if (nbf_common::nb_strlen($css) > 0)
		{
			$header .= "<style type=\"text/css\">\n$css\ntd,th{font-size:10pt;}</style>";
		}
		$header .= "</head><body style=\"$body_style" . "\">"; //PDF Writer makes font quite small and loses body styling if not repeated here
		$footer = '</body></html>';

		$html = "";
		$page_break_html = '';
		foreach ($id_array as $id)
		{
			if (nbf_common::nb_strlen(trim($id)) > 0 && $id != 0)
			{
				$ids = array();
				$ids[0] = $id;
				$html .= $page_break_html;
                $page_break_html = '<!--NewPage--><div style="page-break-before:always;"></div>';
				$html .= printPreviewPopup($ids, true, false, "", "", $restrict_to_user, $generator == 'dompdf');
			}
		}

		$html = $header . $html . $footer;

		//Get paper size from config
		$sql = "SELECT paper_size FROM #__nbill_vendor
						INNER JOIN #__nbill_document ON
						#__nbill_document.vendor_id = #__nbill_vendor.id WHERE #__nbill_document.id = " . $id_array[0];
		$nb_database->setQuery($sql);
		$paper_size = $nb_database->loadResult();

        if ($generator == 'html2ps') {
		    //Clean up any temporary files left behind by pdf creator
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.file.class.php");
            @nbf_file::remove_directory("$path_to_pdfwriter/temp");
		    @mkdir("$path_to_pdfwriter/temp");
        }

		//Forget the CMS admin template
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

        if ($return_contents) {
            ob_start();
        } else if ($generator == 'dompdf') {
            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="' . $first_document_no . '.pdf"');
        }

        convert_to_pdf($generator, $path_to_pdfwriter, $html, $paper_size, $send_pdf_to_file, $pdf_file_name);
        if ($return_contents)
        {
            $return_value = ob_get_clean();
        	ob_start(); //In case we are going to display something else after this
        	return $return_value;
        }
        else
        {
            exit;
        }
	}
}

function convert_to_pdf($generator, $path_to_pdfwriter, $html, $paper_size = "A4", $send_pdf_to_file = false, $pdf_file_name = "")
{
    if ($generator == 'html2ps') {
        //Use includes instead of requires in case PDF writer not installed and we are emailing
        if (file_exists($path_to_pdfwriter.'/config.inc.php'))
        {
            include_once($path_to_pdfwriter.'/config.inc.php');
            include_once($path_to_pdfwriter.'/pipeline.class.php');
            include_once($path_to_pdfwriter.'/pipeline.factory.class.php');
            include_once($path_to_pdfwriter.'/fetcher._interface.class.php');
            include_once($path_to_pdfwriter.'/nbill.fetcher.memory.class.php');
        }
        if (file_exists($path_to_pdfwriter.'/nbill_to_pdf.php'))
        {
            include_once($path_to_pdfwriter.'/nbill_to_pdf.php');
            nb_create_invoice_pdf($path_to_pdfwriter, $html, $paper_size, $send_pdf_to_file, basename($pdf_file_name, '.pdf'));
        }
        else
        {
            return false;
        }
    } else {
        if (function_exists('mb_detect_encoding') && mb_detect_encoding($html, 'ASCII', true) !== false) {
            //No UTF-8 characters - we can drop the special Firefly and DejaVu fonts
            $html = str_replace('font-family: firefly, DejaVu Sans,', 'font-family:', $html);
        }

        if (!defined('DOMPDF_ENABLE_FONTSUBSETTING')) {
            define("DOMPDF_ENABLE_FONTSUBSETTING", true); //Keeps file size small - off by default, so we force it on by setting it before calling the config file
        }
        include_once($path_to_pdfwriter.'/dompdf_config.inc.php');
        $dompdf = new DOMPDF();

        $dompdf->load_html($html);
        $dompdf->set_paper($paper_size, 'vertical');
        $dompdf->render();
        if ($send_pdf_to_file && $pdf_file_name) {
            file_put_contents($pdf_file_name, $dompdf->output());
        } else {
            echo $dompdf->output();
        }
    }
}

function printPreviewPopup($id_array, $pdf = false, $internal = false, $pre_text = "", $post_text = "", $restrict_to_user = false, $use_local_image = false, $is_delivery_note = false)
{
	$nb_database = nbf_cms::$interop->database;
    $payment_details = array();

	//Clean up the id numbers - trim any white space, and ignore any blanks
	$cleanids = array();
	foreach ($id_array as $id)
	{
		$id = trim($id);
		if (nbf_common::nb_strlen($id) > 0)
		{
			$cleanids[] = intval($id);
		}
	}

	//Get date format and admin email
	$sql = "SELECT date_format FROM #__nbill_configuration";
	$nb_database->setQuery($sql);
	$nb_database->loadObject($config);
	$date_format = "Y/m/d";
	if ($config)
	{
		$date_format = $config->date_format;
	}

	//Get the invoice data and template
	$show_remittance = true;
	$show_paylink = true;
	$sql = "SELECT #__nbill_document.*, #__nbill_xref_country_codes.description AS billing_country_desc, #__nbill_document.id AS document_id,
                    #__nbill_vendor.invoice_template_name, #__nbill_vendor.credit_template_name, #__nbill_vendor.quote_template_name, #__nbill_vendor.delivery_template_name,
                    #__nbill_vendor.show_remittance, #__nbill_vendor.show_paylink, #__nbill_vendor.vendor_country, #__nbill_entity.tax_zone,
                    #__nbill_entity.country, #__nbill_xref_eu_country_codes.code AS in_eu, #__nbill_xref_quote_status.description AS quote_status_desc,
                    #__nbill_entity.default_language
					FROM #__nbill_document LEFT JOIN #__nbill_vendor ON #__nbill_document.vendor_id = #__nbill_vendor.id
                    LEFT JOIN #__nbill_entity ON #__nbill_document.entity_id = #__nbill_entity.id
                    LEFT JOIN #__nbill_entity_contact ON #__nbill_document.entity_id = #__nbill_entity_contact.entity_id
                    LEFT JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id
	                LEFT JOIN #__nbill_xref_eu_country_codes ON #__nbill_document.billing_country = #__nbill_xref_eu_country_codes.code
                    AND #__nbill_document.billing_country != #__nbill_vendor.vendor_country
                    LEFT JOIN #__nbill_xref_quote_status ON #__nbill_document.status = #__nbill_xref_quote_status.code
                    LEFT JOIN #__nbill_xref_country_codes ON #__nbill_document.billing_country = #__nbill_xref_country_codes.code
					WHERE #__nbill_document.id IN (" . implode(",", $cleanids) . ")";
    if ($restrict_to_user)
    {
        $sql .= " AND #__nbill_contact.user_id = " . intval(nbf_cms::$interop->user->id) . " AND
        (((#__nbill_document.document_type = 'IN' OR #__nbill_document.document_type = 'CR') AND #__nbill_entity_contact.allow_invoices = 1) OR
        (#__nbill_document.document_type = 'QU' AND #__nbill_entity_contact.allow_quotes = 1))";
    }
    if (!defined("NBILL_ADMIN"))
    {
        //If it is a quote, only allow access if quote status is 'quoted' or higher
        $sql .= " AND (#__nbill_document.document_type != 'QU' OR #__nbill_document.status NOT IN ('AA', 'BB'))";
    }
    $sql .= " GROUP BY #__nbill_document.id ORDER BY #__nbill_document.document_no";
	$nb_database->setQuery($sql);
	$invoices = $nb_database->loadObjectList();
	if (!$invoices)
	{
		$invoices = array();
	}
	else
	{
		//For each invoice, get paid date and payment method, if applicable
		$payment_details = array();
		foreach ($invoices as $invoice)
		{
            if ($invoice->paid_in_full || $invoice->partial_payment)
			{
				$sql = "SELECT #__nbill_transaction.transaction_no, #__nbill_payment_gateway_config.display_name AS gateway_name,
                            #__nbill_xref_payment_method.description AS pay_method,
                            #__nbill_document_transaction.`date`, #__nbill_document_transaction.gross_amount
							FROM #__nbill_document_transaction
                            INNER JOIN #__nbill_transaction ON #__nbill_document_transaction.transaction_id = #__nbill_transaction.id
                            LEFT JOIN #__nbill_xref_payment_method ON #__nbill_transaction.method = #__nbill_xref_payment_method.code
                            LEFT JOIN #__nbill_payment_gateway_config ON #__nbill_transaction.method = #__nbill_payment_gateway_config.gateway_id
                            WHERE #__nbill_document_transaction.document_id = '$invoice->id' ORDER BY `date`";
				$nb_database->setQuery($sql);
				$income_items = $nb_database->loadObjectList();
                $payment_details[$invoice->id] = $income_items;
			}
		}
	}

    $sql = "SELECT * FROM #__nbill_document_items WHERE document_id IN (" . implode(",", $cleanids) . ") ORDER BY document_id, ordering, id";
	$nb_database->setQuery($sql);
	$invoice_items = $nb_database->loadObjectList();
	if (!$invoice_items)
	{
		$invoice_items = array();
	}

    //Pick up any translated values if applicable (first set CMS to the language in use)
    if (file_exists(nbf_cms::$interop->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_product.xml")
        || file_exists(nbf_cms::$interop->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_product.xml"))
    {
        for ($i=0; $i<count($invoices); $i++)
        {
            if ($invoices[$i]->default_language && $invoices[$i]->default_language != nbf_cms::$interop->language)
            {
                nbf_cms::$interop->set_cms_language($invoices[$i]->default_language);
            }
            $pay_inst = null;
            $sql = "SELECT id, payment_instructions AS pay_inst FROM #__nbill_tax WHERE payment_instructions = '" . str_replace("'", "\\'", $invoices[$i]->payment_instructions) . "' OR
                    CONCAT(CONCAT('<p>', payment_instructions), '</p>') = '" . str_replace("'", "\\'", $invoices[$i]->payment_instructions) . "'";
            $nb_database->setQuery($sql);
            $nb_database->loadObject($pay_inst);
            if ($pay_inst && $pay_inst->pay_inst !== null)
            {
                $invoices[$i]->payment_instructions = $pay_inst->pay_inst;
            }
            else
            {
                //Try the vendor record
                $sql = "SELECT id, payment_instructions AS pay_inst FROM #__nbill_vendor WHERE payment_instructions = '" . str_replace("'", "\\'", $invoices[$i]->payment_instructions) . "' OR
                        CONCAT(CONCAT('<p>', payment_instructions), '</p>') = '" . str_replace("'", "\\'", $invoices[$i]->payment_instructions) . "'";
                $nb_database->setQuery($sql);
                $nb_database->loadObject($pay_inst);
                if ($pay_inst)
                {
                    $invoices[$i]->payment_instructions = $pay_inst->pay_inst;
                }
            }
            $small_print = null;
            $sql = "SELECT id, small_print AS sml_prt FROM #__nbill_tax WHERE small_print = '" . str_replace("'", "\\'", $invoices[$i]->small_print) . "' OR
                    CONCAT(CONCAT('<p>', small_print), '</p>') = '" . str_replace("'", "\\'", $invoices[$i]->small_print) . "'";
            $nb_database->setQuery($sql);
            $nb_database->loadObject($small_print);
            if ($small_print && $small_print->sml_prt !== null)
            {
                $invoices[$i]->small_print = $small_print->sml_prt;
            }
            else
            {
                //Try the vendor record
                $sql = "SELECT id, small_print AS sml_prt FROM #__nbill_vendor WHERE small_print = '" . str_replace("'", "\\'", $invoices[$i]->small_print) . "' OR
                        CONCAT(CONCAT('<p>', small_print), '</p>') = '" . str_replace("'", "\\'", $invoices[$i]->small_print) . "'";
                $nb_database->setQuery($sql);
                $nb_database->loadObject($small_print);
                if ($small_print)
                {
                    $invoices[$i]->small_print = $small_print->sml_prt;
                }
            }

            $sql = "SELECT id, delivery_small_print AS delivery_sml_prt FROM #__nbill_vendor WHERE delivery_small_print = '" . str_replace("'", "\\'", $invoices[$i]->delivery_small_print) . "' OR
                        CONCAT(CONCAT('<p>', delivery_small_print), '</p>') = '" . str_replace("'", "\\'", $invoices[$i]->delivery_small_print) . "'";
            $nb_database->setQuery($sql);
            $nb_database->loadObject($delivery_small_print);
            if ($delivery_small_print)
            {
                $invoices[$i]->delivery_small_print = $delivery_small_print->delivery_sml_prt;
            }
        }
        for ($i=0; $i<count($invoice_items); $i++)
        {
            $product_name = null;
            $sql = "SELECT id, name FROM #__nbill_product WHERE name = '" . str_replace("'", "\\'", $invoice_items[$i]->product_description) . "'";
            $nb_database->setQuery($sql);
            $nb_database->loadObject($product_name);
            if ($product_name)
            {
                $invoice_items[$i]->product_description = $product_name->name;
            }
            else
            {
                //Check for first part of invoice description matching a full product name
                $sql = "SELECT product_code, name FROM #__nbill_product WHERE vendor_id = " . $invoice_items[$i]->vendor_id . " LIMIT 500";
                $nb_database->setQuery($sql);
                $products = $nb_database->loadObjectList();
                foreach ($products as $product)
                {
                    if (nbf_common::nb_substr($invoice_items[$i]->product_description, 0, nbf_common::nb_strlen($product->name)) == nbf_common::nb_substr($product->name, 0, nbf_common::nb_strlen($product->name)) ||
                        nbf_common::nb_substr($invoice_items[$i]->product_description, 0, nbf_common::nb_strlen($product->name) + nbf_common::nb_strlen($product->product_code) + 3) == nbf_common::nb_substr($product->product_code . " - " . $product->name, 0, nbf_common::nb_strlen($product->name) + nbf_common::nb_strlen($product->product_code) + 3))
                    {
                        $sql = "SELECT id, name AS new_name FROM #__nbill_product WHERE `name` = '" . str_replace("'", "\\'", $product->name) . "'"; //Have to use an alias as of JF 1.0.4
                        $nb_database->setQuery($sql);
                        $nb_database->loadObject($product_name);
                        if ($product_name)
                        {
                            $invoice_items[$i]->product_description = str_replace($product->name, $product_name->new_name, $invoice_items[$i]->product_description);
                        }
                        break;
                    }
                }
            }
        }
    }

    //Translate vendor name/address, if applicable
    for ($i=0; $i<count($invoices); $i++)
    {
        $vendor_name = null;
        $sql = "SELECT id, vendor_name AS v_name FROM #__nbill_vendor WHERE vendor_name = '" . str_replace("'", "\\'", $invoices[$i]->vendor_name) . "'";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($vendor_name);
        if ($vendor_name)
        {
            $invoices[$i]->vendor_name = $vendor_name->v_name;
        }
        $vendor_address = null;
        $sql = "SELECT id, vendor_address AS v_address FROM #__nbill_vendor WHERE vendor_address = '" . str_replace("'", "\\'", $invoices[$i]->vendor_address) . "'";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($vendor_address);
        if ($vendor_address)
        {
            $invoices[$i]->vendor_address = $vendor_address->v_address;
        }
    }

    for ($i=0; $i<count($invoices); $i++)
    {
        $invoices[$i]->vendor_address = str_replace("\r", "", $invoices[$i]->vendor_address);
    }

    //If the document is split into sections, but the last item(s) are not in a section, add an 'other' section to the end
    foreach ($invoices as $invoice)
    {
        $sections_present = false;
        $last_item_sectioned = false;
        for ($i=0; $i<count($invoice_items); $i++)
        {
            if ($invoice_items[$i]->document_id == $invoice->id)
            {
                if (strlen($invoice_items[$i]->section_name) > 0)
                {
                    $sections_present = true;
                    $last_item_sectioned = true;
                }
                else
                {
                    $last_item_sectioned = false;
                }
            }
        }
        if ($sections_present && ! $last_item_sectioned)
        {
            $invoice_items[$i-1]->section_name = NBILL_SECTION_OTHER;
        }
    }

    $sql = "SELECT * FROM #__nbill_currency";
	$nb_database->setQuery($sql);
	$currency = $nb_database->loadObjectList();
	if (!$currency)
	{
		$currency = array();
	}

	//Load the tax records
	$sql = "SELECT #__nbill_tax.*, #__nbill_vendor.tax_reference_no FROM #__nbill_tax INNER JOIN #__nbill_vendor ON #__nbill_tax.vendor_id = #__nbill_vendor.id ORDER BY electronic_delivery, country_code";
	$nb_database->setQuery($sql);
	$tax_info = $nb_database->loadObjectList();
	if (!$tax_info)
	{
		$tax_info = array();
	}

	//Load the shipping records
	$sql = "SELECT * FROM #__nbill_shipping";
	$nb_database->setQuery($sql);
	$shipping = $nb_database->loadObjectList();
	if (!$shipping)
	{
		$shipping = array();
	}

    //Load the shipping address
    if ($is_delivery_note) {
        foreach ($invoices as &$invoice) {
            $shipping_address = null;
            $address_mapper = new nBillAddressMapper(nbf_cms::$interop->database, '#__nbill_entity');
            if ($invoice->shipping_address_id) {
                $shipping_address = $address_mapper->loadAddress($invoice->shipping_address_id);
            }
            if (!$shipping_address || !$shipping_address->id) {
                $shipping_address = $address_mapper->loadShippingAddress($invoice->entity_id);
            }
            if (!$shipping_address || !$shipping_address->id) {
                $shipping_address = $address_mapper->loadBillingAddress($invoice->entity_id);
            }
            $invoice->shipping_address = $shipping_address;
        }
    }

    //Load xref language file (in case of status display)
    nbf_common::load_language("xref");

    //New object oriented line items
    $config = nBillConfigurationService::getInstance()->getConfig();
    $number_factory = new nBillNumberFactory($config);
    $currency_factory = new nBillCurrencyFactory();
    $line_item_factory = new nBillLineItemFactory($number_factory);
    $currency_service = new nBillCurrencyService($currency_factory->createCurrencyMapper(nbf_cms::$interop->database));
    $line_item_service = $line_item_factory->createService($line_item_factory->createMapper(nbf_cms::$interop->database, $currency_service));
    $line_items = array();
    $currencies = array();
    foreach ($invoices as $invoice)
    {
        $line_items[$invoice->id] = $line_item_service->getItemsForDocument(($invoice->document_type == 'IV' ? 'IN' : $invoice->document_type), $invoice->id, $invoice->vendor_id);
        $currencies[$invoice->id] = $currency_service->findCurrency($invoice->currency);
    }

	//Display
	$html = nBillInvoice::printPreview($invoices, $invoice_items, $number_factory, $currencies, $line_items, $currency, $date_format, $tax_info, $shipping, $pdf, $internal, $pre_text, $post_text, $payment_details, $use_local_image, $is_delivery_note);

	if ($pdf || $internal)
	{
		return $html;
	}
	else
	{
		echo $html;
	}
}

function showEMailInvoice($document_id)
{
	$nb_database = nbf_cms::$interop->database;

    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.email.class.php");

    //Remove toolbar buttons (already output, but we don't want them thanks)
    //Read all the output buffers into memory
    $loop_breaker = 15; //Don't get stuck in a loop (some versions of PHP are buggy)
    $buffers = array();
    while (ob_get_length() !== false)
    {
        $loop_breaker--;
        $buffers[] = ob_get_contents();
        if (!@ob_end_clean())
        {
            break;
        }
        if ($loop_breaker == 0)
        {
            break;
        }
    }

    //Go through each one and find the one that contains the toolbar section
    $buffer_count = count($buffers);
    $header_added = false;
    for ($i = 0; $i < $buffer_count; $i++)
    {
        if (!$header_added && nbf_common::nb_strrpos($buffers[$i], '<div id="nbill-toolbar-container">') !== false)
        {
            //Remove the unwanted output
            $buffers[$i] = nbf_common::nb_substr($buffers[$i], 0, nbf_common::nb_strpos($buffers[$i], '<div id="nbill-toolbar-container">'));
            break;
        }
    }
    //Echo them in back in the right order
    for ($j = $buffer_count - 1; $j >= 0; $j--)
    {
        ob_start();
        echo $buffers[$j];
    }

    $credit = array();
    

    nBillInvoice::showEMailInvoice($document_id, $credit);
}

function show_uploaded_document_file()
{
    $nb_database = nbf_cms::$interop->database;

    $document_id = intval(nbf_common::get_param($_REQUEST, 'cid'));
    $index = intval(nbf_common::get_param($_REQUEST, 'index'));

    $sql = "SELECT uploaded_files FROM #__nbill_document WHERE id = " . $document_id;
    $nb_database->setQuery($sql);
    $file_uploads = explode("\n", str_replace("\r", "", $nb_database->loadResult()));
    $file_index = 0;
    $file_to_download = "";
    foreach ($file_uploads as $file_upload)
    {
        if ($index == $file_index)
        {
            $file_to_download = $file_upload;
            break;
        }
        $file_index++;
    }
    if (nbf_common::nb_strlen($file_to_download) > 0 && file_exists($file_to_download))
    {
        //Spit it out
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.file.class.php");
        nbf_file::do_file_download($file_to_download);
    }
    echo sprintf(NBILL_DOCUMENT_FILE_NOT_FOUND, $file_to_download);
}