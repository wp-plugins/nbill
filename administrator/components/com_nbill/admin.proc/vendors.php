<?php
/**
* Main processing file for vendors
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
        break;
    case "new":
	    $cid[0] = null;
        //fall through
    case "edit":
		editVendor($cid[0]);
		break;
	case "apply":
		saveVendor();
		if (!$id)
		{
			$id = nbf_common::get_param($_POST,'id');
		}
		if (nbf_common::nb_strlen(nbf_globals::$message) == 0)
		{
			editVendor($id);
		}
		break;
	case "save":
		saveVendor();
		if (nbf_common::nb_strlen(nbf_globals::$message) == 0 || nbf_globals::$message == NBILL_VENDOR_DELETE_LOGO_FAILED)
		{
			showVendors();
		}
        else
        {
            //The toolbar is now wrong! It has already been output, so now we have to massage the buffer...
            $buffer = ob_get_contents();
            $toolbar_start = nbf_common::nb_strpos($buffer, '<div id="nbill-toolbar-container">');
            if ($toolbar_start !== false)
            {
                $toolbar_end = nbf_common::nb_strpos($buffer, '</div>', $toolbar_start + 34);
                if ($toolbar_end !== false)
                {
                    @ob_end_clean();
                    ob_start();
                    $_REQUEST['task'] = "apply";
                    nb_main_html::show_toolbar();
                    $new_toolbar_contents = ob_get_clean();
                    $new_output = substr($buffer, 0, $toolbar_start) . $new_toolbar_contents . substr($buffer, $toolbar_end + 6);
                    ob_start();
                    echo $new_output;
                }
            }
        }
		break;
	case "remove":
	case "delete":
		deleteVendor($cid);
		showVendors();
		break;
    
	default:
        if (substr($task, 0, 7) == "unlock-")
        {
            unlockDBNo($id, substr($task, 7));
            editVendor($id, true);
        }
		else
		{
			nbf_globals::$message = "";
			showVendors();
		}
		break;
}

function showVendors()
{
	$nb_database = nbf_cms::$interop->database;

    //Count the total number of records
	$query = "SELECT count(*) FROM #__nbill_vendor";
	$nb_database->setQuery( $query );
	$total = $nb_database->loadResult();

	//Add page navigation
	$pagination = new nbf_pagination("vendor", $total);

	//Load the records
	$sql = "SELECT * FROM #__nbill_vendor ORDER BY vendor_name LIMIT $pagination->list_offset, $pagination->records_per_page";
	$nb_database->setQuery($sql);
	$rows = $nb_database->loadObjectList();
	if (!$rows)
	{
		$rows = array();
	}

	nBillVendors::showVendors($rows, $pagination);
}

function editVendor($vendor_id, $use_posted_values = false, $test_connection = false)
{
	include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
	$nb_database = nbf_cms::$interop->database;
	$row = $nb_database->load_record("#__nbill_vendor", intval($vendor_id));

	//If new, set boolean values to true if that is the default (so that yes/no radio options default correctly)
	if (!$vendor_id)
	{
		$row->show_remittance = 1;
		$row->show_paylink = 1;
		$row->auto_create_income = 1;
	}

	$currency_codes = nbf_xref::get_currencies();
	$country_codes = nbf_xref::get_countries(false, true);

	//Get list of available templates
	$templates = array();
	$dir = nbf_cms::$interop->nbill_fe_base_path . "/templates/";
	if ($handle = opendir($dir))
	{
		while (($file = readdir($handle)) !== false)
		{
			if (is_dir($dir . $file) && $file != "." && $file != ".." && $file != 'index.html')
			{
				$templates[] =$file;
			}
		}
		closedir($handle);
	}

    //Get list of available e-mail templates
    $email_templates = array();
    $dir = nbf_cms::$interop->nbill_fe_base_path . "/email_templates/";
    if ($handle = opendir($dir))
    {
        while (($file = readdir($handle)) !== false)
        {
            if (is_file($dir . $file) && $file != "." && $file != ".." && $file != 'index.html')
            {
                if (nbf_common::nb_strpos($file, "_attach.php") === false)
                {
                    $email_templates[] = preg_replace('/\.[^.]*$/', '', $file);
                }
            }
        }
        closedir($handle);
    }

	//Get list of payment gateways
	$gateways = array();
	$sql = "SELECT DISTINCT (#__nbill_payment_gateway.gateway_id), #__nbill_payment_gateway_config.display_name
					FROM #__nbill_payment_gateway INNER JOIN #__nbill_payment_gateway_config
					ON #__nbill_payment_gateway.gateway_id = #__nbill_payment_gateway_config.gateway_id
                    ORDER BY #__nbill_payment_gateway_config.ordering";
	$nb_database->setQuery($sql);
	$gateways = $nb_database->loadObjectList();
	if (!$gateways)
	{
		$gateways = array();
	}

	$master_connect = false;
    $master_vendors = array();

    

    ob_start();
	nBillVendors::editVendor($vendor_id, $row, $country_codes, $currency_codes, $templates, $email_templates, $gateways, $use_posted_values, $master_connect, $master_vendors, $test_connection);
    $html = ob_get_clean();
    $output = nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'edit', get_defined_vars(), $html);
    echo $output;
}

function saveVendor()
{
	$nb_database = nbf_cms::$interop->database;

	//Check validity of logo file
	nbf_globals::$message = "";
	$logo_ok = false;
    if (!nbf_cms::$interop->demo_mode)
    {
	    if (count($_FILES) > 0)
	    {
		    $logofile_name = $_FILES['vendor_logo']['name'];
		    if (nbf_common::nb_strlen(trim($logofile_name)) > 0)
		    {
			    if (nbf_common::nb_strtolower(nbf_common::nb_substr($logofile_name, nbf_common::nb_strlen($logofile_name) - 4, 4)) == ".gif" || nbf_common::nb_strtolower(nbf_common::nb_substr($logofile_name, nbf_common::nb_strlen($logofile_name) - 4, 4)) == ".png")
			    {
				    if ($_FILES['vendor_logo']['size'] > 30720)
				    {
					    nbf_globals::$message = NBILL_VENDOR_GIF_TOO_BIG;
				    }
				    else
				    {
					    $logo_ok = true;
				    }
			    }
			    else
			    {
				    nbf_globals::$message = sprintf(NBILL_VENDOR_GIF_ONLY, $logofile_name);
			    }
		    }
	    }

	    if (!$logo_ok && nbf_common::nb_strlen(nbf_globals::$message) > 0)
	    {
		    global $task;
		    editVendor(nbf_common::get_param($_POST, 'id'), true);
		    return;
	    }
	    else
	    {
		    if (!$logo_ok && nbf_common::nb_strlen(nbf_globals::$message) == 0 && nbf_common::get_param($_POST, 'delete_vendor_logo'))
		    {
			    if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . nbf_common::get_param($_POST, 'id') . ".gif"))
			    {
				    if (!unlink(nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . nbf_common::get_param($_POST, 'id') . ".gif"))
				    {
					    nbf_globals::$message = NBILL_VENDOR_DELETE_LOGO_FAILED;
				    }
			    }
                if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . nbf_common::get_param($_POST, 'id') . ".png"))
                {
                    if (!unlink(nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . nbf_common::get_param($_POST, 'id') . ".png"))
                    {
                        nbf_globals::$message = NBILL_VENDOR_DELETE_LOGO_FAILED;
                    }
                }
		    }
	    }
    }

    //Only update next invoice number etc. if it has been changed (otherwise we risk duplicate numbers if something has been generated in the meantime)
    if (nbf_common::get_param($_POST, 'next_invoice_no') == nbf_common::get_param($_POST, 'next_invoice_no_orig'))
    {
        unset($_POST['next_invoice_no']);
    }
    if (nbf_common::get_param($_POST, 'next_order_no') == nbf_common::get_param($_POST, 'next_order_no_orig'))
    {
        unset($_POST['next_order_no']);
    }
    if (nbf_common::get_param($_POST, 'next_receipt_no') == nbf_common::get_param($_POST, 'next_receipt_no_orig'))
    {
        unset($_POST['next_receipt_no']);
    }
    if (nbf_common::get_param($_POST, 'next_payment_no') == nbf_common::get_param($_POST, 'next_payment_no_orig'))
    {
        unset($_POST['next_payment_no']);
    }
    if (nbf_common::get_param($_POST, 'next_credit_no') == nbf_common::get_param($_POST, 'next_credit_no_orig'))
    {
        unset($_POST['next_credit_no']);
    }
    if (nbf_common::get_param($_POST, 'next_quote_no') == nbf_common::get_param($_POST, 'next_quote_no_orig'))
    {
        unset($_POST['next_quote_no']);
    }

    $nb_database->bind_and_save("vendor", $_POST);

	if (!$_POST['id'])
	{
		$vendor_id = $nb_database->insertid();
        $_POST['id'] = $vendor_id;
		if ($vendor_id)
		{
			nbf_common::fire_event("vendor_created", array("id"=>$vendor_id));
			//Add default category and nominal ledger code
			$sql = "INSERT INTO #__nbill_product_category (vendor_id, parent_id, name) VALUES ($vendor_id, -1, '" . NBILL_ROOT . "')";
	        $nb_database->setQuery($sql);
	        $nb_database->query();
			$sql = "INSERT INTO #__nbill_nominal_ledger (vendor_id, code, description) VALUES ($vendor_id, -1, '" . NBILL_MISCELLANEOUS . "')";
	        $nb_database->setQuery($sql);
	        $nb_database->query();
            $config = nBillConfigurationService::getInstance()->getConfig();
            $tax_service = new nBillTaxService(new nBillTaxMapper($nb_database, new nBillNumberFactory($config)), $config);
            $tax_service->refreshEuTaxRecords($vendor_id);
		}
	}
	else
	{
		nbf_common::fire_event("record_updated", array("type"=>"vendor", "id"=>$_POST['id']));
	}

	//If this one is marked as the default, make sure all the others are not
	if (nbf_common::get_param($_REQUEST, 'default_vendor'))
	{
		$sql = "UPDATE #__nbill_vendor SET default_vendor = 0 WHERE id != " . intval(nbf_common::get_param($_POST, 'id'));
		$nb_database->setQuery($sql);
		$nb_database->query();
	}

	if (!nbf_cms::$interop->demo_mode && $logo_ok)
	{
        //Delete any old logo file(s)
        if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . intval(nbf_common::get_param($_POST, 'id')) . ".gif"))
        {
            @unlink(nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . intval(nbf_common::get_param($_POST, 'id')) . ".gif");
        }
        if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . intval(nbf_common::get_param($_POST, 'id')) . ".png"))
        {
            @unlink(nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . intval(nbf_common::get_param($_POST, 'id')) . ".png");
        }
		//Move logo image file
		move_uploaded_file($_FILES['vendor_logo']['tmp_name'], nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . intval(nbf_common::get_param($_POST, 'id')) . nbf_common::nb_strtolower(nbf_common::nb_substr($logofile_name, nbf_common::nb_strlen($logofile_name) - 4, 4)));
	}

    
    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'save', get_defined_vars());
}

function deleteVendor($id_array)
{
    $nb_database = nbf_cms::$interop->database;	//Count the total number of records
	$query = "SELECT count(*) FROM #__nbill_vendor";
	$nb_database->setQuery($query);
	$total = $nb_database->loadResult();

	if ($total > count($id_array))  //Must always have at least one vendor
	{
        nbf_common::hook_extension('vendors', 'delete', get_defined_vars());

        nbf_common::fire_event("vendor_deleted", array("ids"=>implode(",", $id_array)));

		//Delete cookie, if applicable
		if (array_search(nbf_common::get_param($_COOKIE, 'nbill_vendor_' . md5(nbf_cms::$interop->live_site)), $id_array) !== false)
		{
			setcookie('nbill_vendor_' . md5(nbf_cms::$interop->live_site), -999, nbf_common::nb_time());
		}

		//Delete main vendor record
		$sql = "DELETE FROM #__nbill_vendor WHERE id IN (" . implode(",", $id_array) . ")";
		$nb_database->setQuery($sql);
		$nb_database->query();

		//Delete any order/invoice associations where the orders are about to be deleted
		$sql = "SELECT id FROM #__nbill_orders WHERE vendor_id IN (" . implode(",", $id_array) . ")";
		$nb_database->setQuery($sql);
		$order_ids = $nb_database->loadObjectList();
		if (!$order_ids)
		{
			$order_ids = array();
		}
		$order_ids_array = array();
		foreach ($order_ids as $order_id)
		{
			$order_ids_array[] = $order_id->id;
		}
		if (count($order_ids_array) > 0)
		{
			$sql = "DELETE FROM #__nbill_orders_document WHERE order_id IN (" . implode(",", $order_ids_array) . ")";
			$nb_database->setQuery($sql);
			$nb_database->query();
		}

		//Delete any discount amounts that relate to discounts for this vendor
		$sql = "SELECT id FROM #__nbill_discounts WHERE vendor_id IN (" . implode(",", $id_array) . ")";
		$nb_database->setQuery($sql);
        $discounts = $nb_database->loadResultArray();
		$sql = "DELETE FROM #__nbill_discount_currency_amount WHERE id IN (" . implode(",", $discounts) . ")";
		$nb_database->setQuery($sql);
		$nb_database->query();

		//Delete all associated child records
		$tables[] = "shipping";
		$tables[] = "shipping_price";
		$tables[] = "client";
		$tables[] = "credit_note";
		$tables[] = "discounts";
		$tables[] = "document";
		$tables[] = "document_items";
		$tables[] = "expenditure";
		$tables[] = "expenditure_ledger";
		$tables[] = "gateway_tx";
		$tables[] = "income";
		$tables[] = "income_ledger";
		$tables[] = "nominal_ledger";
		$tables[] = "order_form";
		$tables[] = "order_form_fields";
		$tables[] = "order_form_fields_options";
		$tables[] = "orders";
		$tables[] = "pending_orders";
		$tables[] = "product";
		$tables[] = "product_category";
		$tables[] = "product_discount";
		$tables[] = "product_price";
		$tables[] = "reminders";
		$tables[] = "supplier";
		$tables[] = "tax";
		foreach ($tables as $table)
		{
			$sql = "DELETE FROM #__nbill_$table WHERE vendor_id IN (" . implode(",", $id_array) . ")";
			$nb_database->setQuery($sql);
			$nb_database->query();
		}
	}
	else
	{
		nbf_globals::$message = NBILL_ERR_CANNOT_DELETE_LAST_VENDOR;
	}
}

function unlockDBNo($vendor_id, $type)
{
	$nb_database = nbf_cms::$interop->database;
    $sql = "";

    switch ($type)
    {
        case "invoice":
	    	$sql = "UPDATE #__nbill_vendor SET invoice_no_locked = 0 WHERE id = " . intval($vendor_id);
            break;
        case "order":
            $sql = "UPDATE #__nbill_vendor SET order_no_locked = 0 WHERE id = " . intval($vendor_id);
            break;
        case "receipt":
            $sql = "UPDATE #__nbill_vendor SET receipt_no_locked = 0 WHERE id = " . intval($vendor_id);
            break;
        case "payment":
		    $sql = "UPDATE #__nbill_vendor SET payment_no_locked = 0 WHERE id = " . intval($vendor_id);
            break;
        case "credit":
            $sql = "UPDATE #__nbill_vendor SET credit_no_locked = 0 WHERE id = " . intval($vendor_id);
            break;
        case "quote":
            $sql = "UPDATE #__nbill_vendor SET quote_no_locked = 0 WHERE id = " . intval($vendor_id);
            break;
    }
    if (nbf_common::nb_strlen($sql) > 0)
    {
	    $nb_database->setQuery($sql);
	    $nb_database->query();
    }
}