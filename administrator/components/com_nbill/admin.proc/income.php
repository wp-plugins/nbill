<?php
/**
* Main processing file for income feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');class receipt_item
{
    /** @var object - The actual income row */
    var $row;
    /** @var int - The id of the database to which this record belongs */
    var $db_id;

    /** Sort into order */
    function cmp_obj($a, $b)
    {
        $al = nbf_common::nb_strtolower($a->row->date);
        $bl = nbf_common::nb_strtolower($b->row->date);
        if ($al == $bl)
        {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }
}

/**
* Used when comparing dates across multiple databases for generating sequential receipt numbers
*/
class receipt_db extends nbf_db_connection
{
    /** @var int - Id of database for purposes of this excercise */
    var $db_id;
    /** @var int - This database's related vendor id */
    var $db_vendor_id;
}

switch ($task)
{
    case "multi_invoice":
        multiInvoice();
        break;
	case "new":
	    $cid[0] = null;
        //fall through
    case "edit":
    	editIncome($cid[0], intval(nbf_common::get_param($_POST, 'use_posted_values')));
		break;
    case "printer_friendly":
        printerFriendly($cid[0]);
        break;
	case "apply":
		saveIncome($id);
		if (!$id)
		{
			$id = intval(nbf_common::get_param($_POST,'id'));
		}
		editIncome($id);
		break;
	case "save":
		saveIncome($id);
		if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'return')) > 0)
		{
			nbf_common::redirect(base64_decode(nbf_common::get_param($_REQUEST,'return')));
			break;
		}
		showIncome();
		break;
	case "remove":
	case "delete":
		deleteIncome($cid);
		showIncome();
		break;
    
    case "do_multi_generate":
        multi_generate_income();
        //Fall through
	case "cancel":
		if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'return')) > 0)
		{
			nbf_common::redirect(base64_decode(nbf_common::get_param($_REQUEST,'return')));
			break;
		}
		showIncome();
		break;
	case "generated-view":
		showIncome();
		break;
    case "silent":
        //Just using functions
        break;
	default:
		if (substr($task, 0, 19) == "generatereceiptnos-")
		{
			$overridedate = substr($task, 19);
			if (nbf_common::nb_strlen($overridedate) == 0 || $overridedate == "null")
			{
				nbf_globals::$message = NBILL_NO_ACTION_TAKEN;
				showIncome();
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
					//Do the do and set a value in $message
					generateReceiptNos($date_int, nbf_common::get_param($_POST, 'vendor_filter'));
					nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . "&action=income&task=generated-view&message=" . nbf_globals::$message);
				}
				else
				{
					nbf_globals::$message = sprintf(NBILL_INVALID_DATE_ENTERED, nbf_common::get_date_format(true));
					showIncome();
				}
			}
		}
		else
		{
			nbf_globals::$message = "";
			showIncome();
		}
		break;
}

function showIncome()
{
    $nb_database = nbf_cms::$interop->database;

	//Work out date range
    $date_format = nbf_common::get_date_format();
    $cal_date_format = nbf_common::get_date_format(true);
    $date_parts = nbf_common::nb_getdate(time());
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
        if (nbf_common::get_param($_REQUEST, 'for_invoice'))
        {
            $search_date_from = 0;
        }
        else
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
		    $search_date_from = nbf_date::get_default_start_date();
        }
	}
    if (nbf_common::get_param($_REQUEST, 'for_invoice'))
    {
        $search_date_to = nbf_common::nb_mktime(23, 59, 59, 12, 31, 2037); //Largest value allowed for a date using a 32-bit integer is 18th Jan 2038
        if (is_numeric(nbf_common::get_param($_REQUEST, 'search_date_to')))
        {
            $_REQUEST['search_date_to'] = nbf_common::nb_date($date_format, $_REQUEST['search_date_to']);
        }
    }
    else
    {
	    $search_date_to = nbf_common::nb_mktime(23, 59, 59, $date_parts["mon"], $date_parts["mday"], $date_parts["year"]);
    }
	if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST,'search_date_to')) > 5)
	{
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
		$filter_date_parts = nbf_date::get_date_parts(nbf_common::get_param($_REQUEST,'search_date_to'), $cal_date_format);
		if (count($filter_date_parts) == 3)
		{
			$search_date_to = nbf_common::nb_mktime(23, 59, 59, $filter_date_parts['m'], $filter_date_parts['d'], $filter_date_parts['y']);
		}
	}
	$_REQUEST['search_date_from'] = nbf_common::nb_date($date_format, $search_date_from);
	$_REQUEST['search_date_to'] = nbf_common::nb_date($date_format, $search_date_to);
	$rct_no_search = trim(nbf_common::get_param($_REQUEST, 'rct_no_search'));
	$name_search = trim(nbf_common::get_param($_REQUEST, 'name_search'));
	$rct_amount_search = trim(nbf_common::get_param($_REQUEST, 'rct_amount_search'));
	$_REQUEST['rct_no_search'] = $rct_no_search;
	$_REQUEST['name_search'] = $name_search;
	$_REQUEST['rct_amount_search'] = $rct_amount_search;

	//Load Vendors
	$sql = "SELECT id, vendor_name FROM #__nbill_vendor ORDER BY id";
	$nb_database->setQuery($sql);
	$vendors = $nb_database->loadObjectList();

	$query = "SELECT count(*) FROM #__nbill_transaction";
	$whereclause = " WHERE #__nbill_transaction.`date` >= $search_date_from AND #__nbill_transaction.`date` <= $search_date_to";
    $whereclause .= " AND #__nbill_transaction.transaction_type = 'IN'";
	if ((nbf_common::nb_strlen(nbf_globals::$vendor_filter) > 0 && nbf_globals::$vendor_filter != -999))
	{
		$whereclause .= " AND #__nbill_transaction.vendor_id = " . intval(nbf_globals::$vendor_filter);
	}
	if (nbf_common::nb_strlen($rct_no_search) > 0)
	{
		$whereclause .= " AND #__nbill_transaction.transaction_no LIKE '%$rct_no_search%'";
	}
	if (nbf_common::nb_strlen($name_search) > 0)
	{
		$whereclause .= " AND #__nbill_transaction.`name` LIKE '%$name_search%'";
	}
	if (nbf_common::nb_strlen($rct_amount_search) > 0)
	{
		$whereclause .= " AND #__nbill_transaction.amount = '$rct_amount_search'";
	}
    if (isset($_REQUEST['for_invoice']) && nbf_common::nb_strlen($_REQUEST['for_invoice']) > 0)
    {
        $query .= " INNER JOIN #__nbill_document_transaction ON #__nbill_transaction.id = #__nbill_document_transaction.transaction_id
                    AND #__nbill_document_transaction.document_id = " . intval(nbf_common::get_param($_REQUEST, 'for_invoice'));
    }
	$query .= $whereclause;
	$nb_database->setQuery( $query );
	$total = $nb_database->loadResult();

	//Add page navigation
	$pagination = new nbf_pagination("income", $total);

	//Load the records
	$sql = "SELECT #__nbill_transaction.*, COUNT(#__nbill_supporting_docs.id) AS attachment_count FROM #__nbill_transaction
            LEFT JOIN #__nbill_supporting_docs ON #__nbill_transaction.id = #__nbill_supporting_docs.associated_doc_id AND #__nbill_supporting_docs.associated_doc_type = 'IN' ";
    if (isset($_REQUEST['for_invoice']) && nbf_common::nb_strlen($_REQUEST['for_invoice']) > 0)
    {
        $sql .= " INNER JOIN #__nbill_document_transaction ON #__nbill_transaction.id = #__nbill_document_transaction.transaction_id
                    AND #__nbill_document_transaction.document_id = " . intval(nbf_common::get_param($_REQUEST, 'for_invoice'));
    }
	$sql .= $whereclause;
	$sql .= " GROUP BY #__nbill_transaction.id ORDER BY #__nbill_transaction.`date` DESC, #__nbill_transaction.transaction_no DESC, #__nbill_transaction.`id` DESC LIMIT $pagination->list_offset, $pagination->records_per_page";
	$nb_database->setQuery($sql);
	$rows = $nb_database->loadObjectList();
	if (!$rows)
	{
		$rows = array();
	}

	//Get invoice numbers
	$document_ids = array();
	foreach ($rows as $row)
	{
		if (nbf_common::nb_strlen($row->document_ids) > 0)
		{
			$this_document_ids = explode(",", $row->document_ids);
			foreach ($this_document_ids as $document_id)
			{
				$document_ids[] = $document_id;
			}
		}
	}
	$document_nos = false;
	if (count($document_ids) > 0)
	{
		$sql = "SELECT id, document_no FROM #__nbill_document WHERE id IN (" . implode(",", $document_ids) . ")";
		$nb_database->setQuery($sql);
		$document_nos = $nb_database->loadObjectList();
	}
	if (!$document_nos)
	{
		$document_nos = array();
	}

	//get the date format
	$sql = "SELECT date_format FROM #__nbill_configuration";
	$nb_database->setQuery($sql);
	$date_format = $nb_database->loadResult();

    //Get any attachments
    $attachments = array();
    

	nBillIncome::showIncome($rows, $pagination, $vendors, $document_nos, $date_format, $attachments);
}

function printerFriendly($transaction_id)
{
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
    $nb_database = nbf_cms::$interop->database;

    $row = $nb_database->load_record("#__nbill_transaction", $transaction_id);
    $row->pay_method_name = nbf_xref::lookup_xref_code("payment_method", $row->method);
    if (!$row->pay_method_name && $row->method)
    {
        nbf_common::load_language("gateway");
        $sql = "SELECT display_name FROM #__nbill_payment_gateway_config WHERE gateway_id = '" . $row->method . "'";
        $nb_database->setQuery($sql);
        $row->pay_method_name = $nb_database->loadResult();
        if ($row->pay_method_name)
        {
            if (defined($row->pay_method_name))
            {
                $row->pay_method_name = constant($row->pay_method_name);
            }
        }
        else
        {
            $row->pay_method_name = ucwords($row->method); //Gateway used has been deleted
        }
    }

    $documents = array();
    if ($row->document_ids)
    {
        $sql = "SELECT document_no, document_date FROM #__nbill_document WHERE id IN (" . $row->document_ids . ") ORDER BY document_no, document_date";
        $nb_database->setQuery($sql);
        $documents = $nb_database->loadObjectList();
    }

    $sql = "SELECT vendor_name, vendor_address FROM #__nbill_vendor WHERE id = " . $row->vendor_id;
    $nb_database->setQuery($sql);
    $vendor_object = null;
    $nb_database->loadObject($vendor_object);

    //Load colour scheme
    $sql = "SELECT title_colour, heading_bg_colour, heading_fg_colour FROM #__nbill_configuration WHERE id = 1";
    $nb_database->setQuery($sql);
    $nb_database->loadObject($colour_scheme);
    $title_colour = $colour_scheme->title_colour;
    $heading_bg_colour = $colour_scheme->heading_bg_colour;
    $heading_fg_colour = $colour_scheme->heading_fg_colour;

    nBillIncome::showPrinterFriendly($row, $documents, $vendor_object->vendor_name, $vendor_object->vendor_address, $title_colour, $heading_bg_colour, $heading_fg_colour);
}

function editIncome($transaction_id, $use_posted_values = false)
{
	include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
    $nb_database = nbf_cms::$interop->database;
	$_POST['vendor_filter'] = nbf_common::get_param($_REQUEST, 'vendor_filter');
	$_POST['document_id'] = nbf_common::get_param($_REQUEST, 'document_id');

	$row = $nb_database->load_record("#__nbill_transaction", $transaction_id);
    if (!$row->id) {
        $row->tax_rate_1_electronic_delivery = nBillConfigurationService::getInstance()->getConfig()->default_electronic;
        $row->tax_rate_2_electronic_delivery = $row->tax_rate_1_electronic_delivery;
        $row->tax_rate_3_electronic_delivery = $row->tax_rate_1_electronic_delivery;
    }

	$sql = "SELECT id, vendor_name, vendor_country FROM #__nbill_vendor ORDER BY id";
	$nb_database->setQuery($sql);
	$vendors = $nb_database->loadObjectList();

	//Load unpaid invoices, tax info, and ledger codes
	$invoices = array();
	$invoice_items = array();
	$ledger = array();
	$tax_info = array();

	$related_invoices = "";
    $related_invoice_list = trim($row->document_ids);
    $related_invoice_list = nbf_common::nb_strlen($related_invoice_list) > 0 ? $related_invoice_list : "0";
    //if (nbf_common::nb_strpos(nbf_common::get_param($_REQUEST, 'document_id'), ",") !== false)
    if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST, 'document_id')) > 0)
    {
        $related_invoice_list .= "," . nbf_common::get_param($_REQUEST, 'document_id');
    }
    $related_invoices = "SELECT nbill_a.id, nbill_a.id AS document_id, nbill_a.vendor_id, nbill_a.entity_id, nbill_a.document_no, nbill_a.document_date,
                    nbill_a.billing_name, nbill_a.currency, nbill_a.partial_payment, vend_a.vendor_country,
                    nbill_a.total_gross, client_a.tax_zone, client_a.country, nbill_a.tax_abbreviation, xref_a.code AS in_eu
                    FROM #__nbill_document AS nbill_a LEFT JOIN #__nbill_entity AS client_a ON nbill_a.entity_id = client_a.id
                    LEFT JOIN #__nbill_vendor AS vend_a ON nbill_a.vendor_id = vend_a.id
                    LEFT JOIN #__nbill_xref_eu_country_codes AS xref_a ON client_a.country = xref_a.code
                    AND client_a.country != vend_a.vendor_country
                    WHERE nbill_a.id IN ($related_invoice_list) AND nbill_a.document_type = 'IN' UNION ";

    foreach ($vendors as $vendor)
    {
        $sql = "SELECT nbill_c.* FROM (" . $related_invoices;
        $sql .= "SELECT nbill_b.id, nbill_b.id AS document_id, nbill_b.vendor_id, nbill_b.entity_id, nbill_b.document_no, nbill_b.document_date,
                        nbill_b.billing_name, nbill_b.currency, nbill_b.partial_payment, vend_b.vendor_country,
                        nbill_b.total_gross, client_b.tax_zone, client_b.country, nbill_b.tax_abbreviation, xref_b.code AS in_eu
                        FROM #__nbill_document AS nbill_b LEFT JOIN #__nbill_entity AS client_b ON nbill_b.entity_id = client_b.id
                        LEFT JOIN #__nbill_vendor AS vend_b ON nbill_b.vendor_id = vend_b.id
                        LEFT JOIN #__nbill_xref_eu_country_codes AS xref_b ON client_b.country = xref_b.code
                        AND client_b.country != vend_b.vendor_country
                        WHERE (nbill_b.paid_in_full = 0) AND nbill_b.vendor_id = " . $vendor->id;
        if (nbf_common::get_param($_REQUEST, 'document_id'))
        {
            $sql .= " AND nbill_b.entity_id = (SELECT nbill_d.entity_id FROM #__nbill_document AS nbill_d WHERE id IN (" . nbf_common::get_param($_REQUEST, 'document_id') . ") LIMIT 1)";
        }
        $sql .= " AND nbill_b.document_type = 'IN'";
        if (!nbf_common::get_param($_REQUEST, 'no_record_limit'))
        {
            $sql .= " LIMIT " . nbf_globals::$record_limit;
        }
        $sql .= ") AS nbill_c ORDER BY nbill_c.document_date, nbill_c.document_no + 0, nbill_c.document_no";
        $nb_database->setQuery($sql);
        $invoices[$vendor->id] = $nb_database->loadObjectList();
        if (!isset($invoices[$vendor->id]) || !$invoices[$vendor->id])
        {
            $invoices[$vendor->id] = array();
        }
        else
        {
            //Switch to the vendor for the selected invoice if applicable
            if (nbf_common::get_param($_REQUEST, 'document_id'))
            {
                foreach ($invoices[$vendor->id] as $selected_invoice)
                {
                    if ($selected_invoice->id == nbf_common::get_param($_REQUEST, 'document_id'))
                    {
                        $_POST['vendor_filter'] = $selected_invoice->vendor_id;
                        $_POST['vendor_id'] = $selected_invoice->vendor_id;
                        break;
                    }
                }
            }
            else
            {
                $_POST['vendor_filter'] = $vendor->id;
                $_POST['vendor_id'] = $vendor->id;
            }
        }

		//Get nominal ledger codes (full list)
		$sql = "SELECT * FROM #__nbill_nominal_ledger WHERE vendor_id = " . $vendor->id . " ORDER BY code";
		$nb_database->setQuery($sql);
		$ledger[$vendor->id] = $nb_database->loadObjectList();
		if (!isset($ledger[$vendor->id]) || !$ledger[$vendor->id])
		{
			$ledger[$vendor->id] = array();
		}
	}

	//Get nominal ledger breakdown for this income item
	$ledger_breakdown = array();
	if (nbf_common::nb_strlen($transaction_id) > 0 && $transaction_id > 0)
	{
		$sql = "SELECT * FROM #__nbill_transaction_ledger WHERE transaction_id = $transaction_id";
		$nb_database->setQuery($sql);
		$ledger_breakdown = $nb_database->loadObjectList();
		if (!$ledger_breakdown)
		{
			$ledger_breakdown = array();
		}
	}

	//Get payment methods
	$pay_methods = nbf_xref::load_xref("payment_method");

    //Add payment gateways
    $pay_methods = array_merge($pay_methods, nbf_xref::load_xref("[gateway_list]", true, false, array('offline'), false, true));

    //If our method is not there, add it as 'other' (so we don't forget it - in case a gateway has been uninstalled and may later be re-installed)
    $our_method = false;
    $xx_method = -1;
    if (@$row->method)
    {
        foreach ($pay_methods as $key=>$value)
        {
            if ($value->code == $row->method)
            {
                $our_method = true;
                break;
            }
            if ($value->code == 'XX')
            {
                $xx_method = $key;
            }
        }
        if (!$our_method && $xx_method >=0)
        {
            $pay_methods[$xx_method]->code = $row->method;
        }
    }

    //Get shipping info
	$sql = "SELECT * FROM #__nbill_shipping";
	$nb_database->setQuery($sql);
	$shipping = $nb_database->loadObjectList();
	if (!$shipping)
	{
		$shipping = array();
	}

	$currencies = nbf_xref::get_currencies();
    $countries = nbf_xref::get_countries(false, true);

    //Is it on the guessed list?
    $sql = "SELECT transaction_id FROM #__nbill_ledger_breakdown_guesses WHERE transaction_id = $transaction_id";
    $nb_database->setQuery($sql);
    $_REQUEST['guessed'] = $nb_database->loadResult() ? 1 : 0;

    //Get any attachments
    $attachments = array();
    

    ob_start();
	nBillIncome::editIncome($transaction_id, $row, $vendors, $invoices, $shipping, $pay_methods, $countries, $currencies, $ledger, $ledger_breakdown, $use_posted_values, $attachments);
    $html = ob_get_clean();
    $output = nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'edit', get_defined_vars(), $html);
    echo $output;
}



function saveIncome($transaction_id)
{
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");
	nbf_payment::save_transaction_item($transaction_id, $_POST, $error_message, $transaction_no);
	if (nbf_common::nb_strlen($error_message) > 0)
	{
		echo "<script> alert('" . $error_message . " " . NBILL_ERR_REDIRECT_BACK . "'); window.history.go(-1); </script>\n";
		exit();
	}

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'save', get_defined_vars());

	$_POST['added_items'] = null;
	$_POST['removed_items'] = null;
}

function deleteIncome($id_array)
{
	$nb_database = nbf_cms::$interop->database;

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'delete', get_defined_vars());
	nbf_common::fire_event("income_deleted", array("ids"=>implode(",", $id_array)));

    //Get existing invoice list (so we can set these invoices to unpaid or partially paid)
    $sql = "SELECT document_ids, amount, added_document_item_id FROM #__nbill_transaction WHERE id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $old_details = $nb_database->loadObjectList();
    if (!$old_details)
    {
        $old_details = array();
    }

    //Delete record
	$sql = "DELETE FROM #__nbill_transaction WHERE id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

	//Delete ledger record(s)
	$sql = "DELETE FROM #__nbill_transaction_ledger WHERE transaction_id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

    //Delete document_transaction associations
    $sql = "DELETE FROM #__nbill_document_transaction WHERE transaction_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();

    //Remove any gateway fee entries that were added for this transaction
    foreach ($old_details as $old_detail)
    {
        if ($old_detail->added_document_item_id)
        {
            $added_document_items = unserialize($old_detail->added_document_item_id);
            foreach ($added_document_items as $invoice_id=>$item_id)
            {
                //Load the gateway fee amounts
                $document_item = null;
                $sql = "SELECT id, document_id, net_price_for_item, tax_for_item, gross_price_for_item
                                FROM #__nbill_document_items WHERE id = " . intval($item_id);
                $nb_database->setQuery($sql);
                $nb_database->loadObject($document_item);

                if ($document_item)
                {
                    //Load the invoice totals
                    $document = null;
                    $sql = "SELECT id AS document_id, total_net, total_tax, total_gross FROM #__nbill_document WHERE id = " . intval($document_item->document_id);
                    $nb_database->setQuery($sql);
                    $nb_database->loadObject($document);

                    if ($document)
                    {
                        //Update the invoice totals
                        $sql = "UPDATE #__nbill_document SET
                                total_net = '" . float_subtract($document->total_net, $document_item->net_price_for_item) . "',
                                total_tax = '" . float_subtract($document->total_tax, $document_item->tax_for_item) . "',
                                total_gross = '" . float_subtract($document->total_gross, $document_item->gross_price_for_item) . "'
                                WHERE id = " . $document_item->document_id;
                        $nb_database->setQuery($sql);
                        $nb_database->query();

                        //Delete the invoice item
                        $sql = "DELETE FROM #__nbill_document_items WHERE id = " . intval($document_item->id);
                        $nb_database->setQuery($sql);
                        $nb_database->query();
                    }
                }
            }
        }
    }

    //Mark invoices as unpaid or partially paid, as applicable
    $invoice_list = array();
    foreach ($old_details as $old_detail)
    {
        $invoice_list = array_merge($invoice_list, explode(",", $old_detail->document_ids));
    }

    if (count($invoice_list) > 0)
    {
        $total_outstanding = 0;
        $invoice_gross = 0;
        foreach ($invoice_list as $invoice_id)
        {
            //Load the invoice total
            $sql = "SELECT total_gross FROM #__nbill_document WHERE id = " . intval($invoice_id);
            $nb_database->setQuery($sql);
            $invoice_gross = $nb_database->loadResult();
            $total_outstanding = $invoice_gross;

            //Load any other related transactions
            $sql = "SELECT document_id, SUM(gross_amount) AS gross_paid FROM #__nbill_document_transaction
                    WHERE document_id = " . intval($invoice_id) . " GROUP BY document_id";
            $nb_database->setQuery($sql);
            $transactions = $nb_database->loadObjectList();
            if ($transactions)
            {
                foreach ($transactions as $transaction)
                {
                    if ($transaction->document_id == $invoice_id)
                    {
                        $total_outstanding = float_subtract($invoice_gross, $transaction->gross_paid);
                        if ($invoice_gross <= 0)
                        {
                            $total_outstanding = 0; //Overpaid, or paid in full, but not marked as paid!
                            break;
                        }
                    }
                }
            }

            if ($total_outstanding >= $invoice_gross)
            {
                //Unpaid
                $sql = "UPDATE #__nbill_document SET paid_in_full = 0, partial_payment = 0 WHERE id = " . intval($invoice_id);
            }
            else
            {
                //Partially paid
                $sql = "UPDATE #__nbill_document SET paid_in_full = 0, partial_payment = 1 WHERE id = " . intval($invoice_id);
            }
            $nb_database->setQuery($sql);
            $nb_database->query();
        }
    }

    //Detach any attachments
    $sql = "DELETE FROM #__nbill_supporting_docs WHERE associated_doc_type = 'IN' AND associated_doc_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();
}

function generateReceiptNos($date_int, $vendor_id)
{
	$nb_database = nbf_cms::$interop->database;

	include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.number.generator.php");

	//Gather income records from ALL databases in the chain
	$master_db = false;
	$using_master_db = false;
	$master_dbs = array();
	$receipt_items = array();
	$orig_vendor = $vendor_id;

	$db_id = 0;
	$this_db = new receipt_db();
	$this_db->db_id = $db_id;
	$this_db->db_host = nbf_cms::$interop->db_connection->host;
	$this_db->db_username = nbf_cms::$interop->db_connection->user_name;
	$this_db->db_password = nbf_cms::$interop->db_connection->password;
	$this_db->db_name = nbf_cms::$interop->db_connection->db_name;
	$this_db->db_table_prefix = nbf_cms::$interop->db_connection->prefix;
	$this_db->db_vendor_id = $vendor_id;
	$master_dbs[$db_id] = $this_db;

	$sql = "SELECT `id`, `date` FROM #__nbill_transaction WHERE transaction_type = 'IN' AND `date` <= $date_int AND transaction_no = '' AND vendor_id = $vendor_id ORDER BY `date`";
	$nb_database->setQuery($sql);
	$incomes = $nb_database->loadObjectList();

	if ($incomes)
	{
		foreach ($incomes as $income)
		{
			$receipt_item = new receipt_item();
			$receipt_item->db_id = $db_id;
			$receipt_item->row = $income;
			$receipt_items[] = $receipt_item;
		}
	}

	//Now see if we need any more...
	$sql = "SELECT use_master_db, master_host, master_username, master_password, master_dbname, master_table_prefix, master_vendor_id FROM #__nbill_vendor WHERE id = $vendor_id";
	$nb_database->setQuery($sql);
	$nb_database->loadObject($master_db);

    $db_class_name = get_class($nb_database);

	//Gather records right back to the top-level parent
	while ($master_db && $master_db->use_master_db)
	{
		$db_id++;
		$using_master_db = true;
		//Check whether we've been here before (don't want to get stuck in a loop)
		$already_used = false;
		foreach ($master_dbs as $this_db)
		{
			if ($this_db->db_host == $master_db->master_host && $this_db->db_name == $master_db->master_dbname && $this_db->db_table_prefix == $master_db->master_table_prefix)
			{
				$already_used = true;
				break;
			}
		}
		if ($already_used)
		{
			//Stuck in a loop - abort!
			nbf_globals::$message = NBILL_ERR_MASTER_DB_LOOP;
			$nb_database = new $db_class_name(nbf_cms::$interop->db_connection->host, nbf_cms::$interop->db_connection->user_name, nbf_cms::$interop->db_connection->password, nbf_cms::$interop->db_connection->db_name, nbf_cms::$interop->db_connection->prefix, false);
			return false;
		}
		else
		{
			$this_db = new receipt_db();
			$this_db->db_id = $db_id;
			$this_db->db_host = $master_db->master_host;
			$this_db->db_username = $master_db->master_username;
			$this_db->db_password = $master_db->master_password;
			$this_db->db_name = $master_db->master_dbname;
			$this_db->db_table_prefix = $master_db->master_table_prefix;
			$this_db->db_vendor_id = $master_db->master_vendor_id;
			$master_dbs[$db_id] = $this_db;

			$vendor_id = $master_db->master_vendor_id;
			$nb_database = new $db_class_name($master_db->master_host, $master_db->master_username, $master_db->master_password, $master_db->master_dbname, $master_db->master_table_prefix, false);
			if (!$nb_database)
			{
				nbf_globals::$message = NBILL_ERR_MASTER_DB_CONNECT;
				$nb_database = new $db_class_name(nbf_cms::$interop->db_connection->host, nbf_cms::$interop->db_connection->user_name, nbf_cms::$interop->db_connection->password, nbf_cms::$interop->db_connection->db_name, nbf_cms::$interop->db_connection->prefix, false);
				return false;
			}

			//We have a satisfactory master!
			$incomes = null;
			$sql = "SELECT `id`, `date` FROM #__nbill_transaction WHERE transaction_type = 'IN' AND `date` <= $date_int AND transaction_no = '' AND vendor_id = $vendor_id ORDER BY `date`";
			$nb_database->setQuery($sql);
			$incomes = $nb_database->loadObjectList();
			if ($incomes)
			{
				foreach ($incomes as $income)
				{
					$receipt_item = new receipt_item();
					$receipt_item->db_id = $db_id;
					$receipt_item->row = $income;
					$receipt_items[] = $receipt_item;
				}
			}
			//Now to see if there are any more masters...
			$sql = "SELECT use_master_db, master_host, master_username, master_password, master_dbname, master_table_prefix, master_vendor_id FROM #__nbill_vendor WHERE id = $vendor_id";
			$nb_database->setQuery($sql);
			$master_db = null;
			$nb_database->loadObject($master_db);
			if ($master_db == null)
			{
				nbf_globals::$message = NBILL_ERR_MASTER_DB_CONNECT;
				$nb_database = new $db_class_name(nbf_cms::$interop->db_connection->host, nbf_cms::$interop->db_connection->user_name, nbf_cms::$interop->db_connection->password, nbf_cms::$interop->db_connection->db_name, nbf_cms::$interop->db_connection->prefix, false);
				return false;
			}
		}
	}

	//Sort into date order
	usort($receipt_items, array("receipt_item", "cmp_obj"));

	$transaction_no_count = 0;
	$transaction_no_range = "";
	$first_rcpt_no = "";
	$last_rcpt_no = "";
    foreach ($receipt_items as $receipt_item)
	{
		//Revert to this site's database for receipt number generation (to ensure we get to the right place regardless of different vendor IDs along the way)
        $nb_database = new $db_class_name(nbf_cms::$interop->db_connection->host, nbf_cms::$interop->db_connection->user_name, nbf_cms::$interop->db_connection->password, nbf_cms::$interop->db_connection->db_name, nbf_cms::$interop->db_connection->prefix, false);
        $income = $receipt_item->row;
        $error = "";
		$transaction_no = nbf_number_generator::get_next_number($orig_vendor, "receipt", $error);
		if ($transaction_no === false)
		{
			nbf_globals::$message = $error;
			return;
		}
		if (nbf_common::nb_strlen($first_rcpt_no) == 0)
		{
			$first_rcpt_no = $transaction_no;
		}
		$transaction_no_count++;
		$last_rcpt_no = $transaction_no;
		$sql = "UPDATE #__nbill_transaction SET transaction_no = '$transaction_no' WHERE id = " . $income->id;
		//Ensure we pick the right database! (Receipt number generator will have reverted to default
		$nb_database = new $db_class_name($master_dbs[$receipt_item->db_id]->db_host, $master_dbs[$receipt_item->db_id]->db_username, $master_dbs[$receipt_item->db_id]->db_password, $master_dbs[$receipt_item->db_id]->db_name, $master_dbs[$receipt_item->db_id]->db_table_prefix, false);
		$vendor_id = $master_dbs[$receipt_item->db_id]->db_vendor_id;
		$nb_database->setQuery($sql);
		$nb_database->query();
	}

	$nb_database = new $db_class_name(nbf_cms::$interop->db_connection->host, nbf_cms::$interop->db_connection->user_name, nbf_cms::$interop->db_connection->password, nbf_cms::$interop->db_connection->db_name, nbf_cms::$interop->db_connection->prefix, false);
	if (nbf_common::nb_strlen($first_rcpt_no) > 0 && nbf_common::nb_strlen($last_rcpt_no) > 0)
	{
		$transaction_no_range = " ($first_rcpt_no " . NBILL_TO . " $last_rcpt_no)";
	}
	nbf_globals::$message = sprintf(NBILL_RECEIPT_NOS_GENERATED, $transaction_no_count, $transaction_no_range);

	nbf_common::fire_event("transaction_nos_generated", array("vendor_id"=>$vendor_id, "date"=>$date_int, "no_of_nos"=>$transaction_no_count, "first_no"=>$first_rcpt_no, "last_no"=>$last_rcpt_no));
}

/**
* Get additional info needed to mark multiple invoices as paid with each one having a separate income record
*/
function multiInvoice()
{
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
    $nb_database = nbf_cms::$interop->database;

    $invoice_count = 0;
    $document_ids = nbf_common::get_param($_REQUEST, 'document_id');
    if (nbf_common::nb_strlen($document_ids) == 0)
    {
        $document_ids = nbf_common::get_param($_REQUEST, 'document_ids');
    }
    if (nbf_common::nb_strlen($document_ids) > 0)
    {
        //Sanitise
        $document_ids = explode(",", $document_ids);
        foreach ($document_ids as &$document_id)
        {
            $document_id = intval($document_id);
        }
        //Make sure these invoices exist...
        $sql = "SELECT id FROM #__nbill_document WHERE document_type = 'IN' AND paid_in_full = 0 AND partial_payment = 0 AND id IN (" . implode(",", $document_ids) . ")";
        $nb_database->setQuery($sql);
        $document_ids = $nb_database->loadResultArray();
        $invoice_count = count($document_ids);
    }

    //Get payment methods
    $pay_methods = nbf_xref::load_xref("payment_method");

    //Add payment gateways
    $pay_methods = array_merge($pay_methods, nbf_xref::load_xref("[gateway_list]", true, false, array('offline'), false, true));

    if ($invoice_count > 0)
    {
        nBillIncome::multi_income_generator($invoice_count, $document_ids, $pay_methods);
    }
    else
    {
        nBillIncome::multi_income_abort();
    }
}

/**
* mark multiple invoices as paid with each one having a separate income record
*/
function multi_generate_income()
{
    $nb_database = nbf_cms::$interop->database;

    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
    $document_ids = explode(",", nbf_common::get_param($_REQUEST, 'document_ids'));

    $income_date = null;
    $date_parts = nbf_date::get_date_parts(nbf_common::get_param($_REQUEST,'date'), nbf_common::get_date_format(true));
    if (count($date_parts) == 3)
    {
        $income_date = nbf_common::nb_mktime(0, 0, 0, $date_parts['m'], $date_parts['d'], $date_parts['y']);
    }

    //Load invoices and generate an income record for each one
    $sql = "SELECT id, billing_name, total_gross, currency FROM #__nbill_document WHERE document_type = 'IN' AND paid_in_full = 0 AND partial_payment = 0 AND id IN (" . implode(",", $document_ids) . ")";
    $nb_database->setQuery($sql);
    $invoices = $nb_database->loadObjectList();

    $transaction_no = "";
    $transaction_id = "";
    $error_message = "";
    foreach ($invoices as $invoice)
    {
        nbf_gateway_txn::$document_ids = array($invoice->id);
        $error = nbf_payment::record_income($invoice->billing_name, nbf_common::get_param($_REQUEST, 'method'), $invoice->total_gross,
                        $invoice->currency, nbf_common::get_param($_REQUEST, 'reference'), nbf_common::get_param($_REQUEST, 'notes'),
                        $transaction_no, $transaction_id, $income_date);
        if (nbf_common::nb_strlen($error) > 0)
        {
            $error_message = $error;
        }
    }

    $message = "";
    if (nbf_common::nb_strlen($error_message) > 0)
    {
        $message = NBILL_CREATE_MULTIPLE_INCOMES_ERROR . '\n\n' . $error_message;
    }
    else
    {
        $message = sprintf(NBILL_CREATE_MULTIPLE_INCOMES_COMPLETE, nbf_common::get_param($_REQUEST, 'invoice_count'));
    }
    nbf_common::redirect(base64_decode(nbf_common::get_param($_REQUEST, 'return')) . "&message=" . $message);
}