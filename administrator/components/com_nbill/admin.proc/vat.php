<?php
/**
* Main processing file for sales tax (VAT)
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
		editTax($cid[0]);
		break;
	case "apply":
        if (saveTax())
        {
            if (!$id)
            {
                $id = intval(nbf_common::get_param($_POST, 'id'));
            }
            editTax($id);
        }
        break;
    case "save":
        if (saveTax())
        {
            showTax();
        }
        break;
    case "remove":
    case "delete":
        deleteTax($cid);
        showTax();
        break;
    
    default:
        nbf_globals::$message = "";
        showTax();
        break;
}

function showTax()
{
    $nb_database = nbf_cms::$interop->database;

	//Get Vendors
	$sql = "SELECT id, vendor_name FROM #__nbill_vendor ORDER BY id";
	$nb_database->setQuery($sql);
	$vendors = $nb_database->loadObjectList();

	//Count the total number of records
	$query = "SELECT count(*) FROM #__nbill_tax";
	$whereclause = "";
	if ((nbf_common::nb_strlen(nbf_globals::$vendor_filter) > 0 && nbf_globals::$vendor_filter != -999))
	{
		$whereclause .= " WHERE vendor_id = " . intval(nbf_globals::$vendor_filter);
	}
	$query .= $whereclause;
	$nb_database->setQuery($query);
	$total = $nb_database->loadResult();

	//Add page navigation
	$pagination = new nbf_pagination("vat", $total);

	//Load the records
	$sql = "SELECT * FROM #__nbill_tax";
	$sql .= $whereclause;
	$sql .= " ORDER BY electronic_delivery, country_code LIMIT $pagination->list_offset, $pagination->records_per_page";
	$nb_database->setQuery($sql);
	$rows = $nb_database->loadObjectList();
	if (!$rows)
	{
		$rows = array();
	}
	nBillVAT::showTax($rows, $pagination, $vendors);
}

function editTax($tax_id, $use_posted_values = false, $affected_orders = null, $auto_renew_count = 0)
{
	include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
	$nb_database = nbf_cms::$interop->database;
    $row = $nb_database->load_record("#__nbill_tax", $tax_id);

	$sql = "SELECT id, vendor_name, payment_instructions, small_print FROM #__nbill_vendor ORDER BY id";
	$nb_database->setQuery($sql);
	$vendors = $nb_database->loadObjectList();

    check_for_translation($vendors, "nbill_vendor", "id", "'vendor_name', 'payment_instructions', 'small_print'");
	$country_codes = nbf_xref::get_countries(true, true);

    ob_start();
    nBillVAT::editTax($tax_id, $row, $country_codes, $vendors, $use_posted_values, $affected_orders, $auto_renew_count);
    $html = ob_get_clean();
    $output = nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'edit', get_defined_vars(), $html);
    echo $output;
}

function saveTax()
{
	$nb_database = nbf_cms::$interop->database;

    //Initialise variables
    $affected_orders = array();
    $auto_renew_count = 0;
    $order_update_count = 0;

    //Check whether we need to update any order records following a change of tax rate
    $cancel_save = false;
    

    $_POST['payment_instructions'] = @$_POST['pay_inst_' . nbf_common::get_param($_POST, 'vendor_id')];
    if (strlen(trim(strip_tags($_POST['payment_instructions']))) == 0) {
        $_POST['payment_instructions'] = '';
    }
    $_POST['small_print'] = @$_POST['sml_prt_' . nbf_common::get_param($_POST, 'vendor_id')];
    if (strlen(trim(strip_tags($_POST['small_print']))) == 0) {
        $_POST['small_print'] = '';
    }

    

    if (!$cancel_save)
    {
        $nb_database->bind_and_save("tax", $_POST);
        if (!nbf_common::get_param($_POST, 'id'))
        {
            $_POST['id'] = $nb_database->insertid();
            nbf_common::fire_event("tax_created", array("id"=>$_POST['id']));
        }
        else
        {
            nbf_common::fire_event("record_updated", array("type"=>"tax", "id"=>nbf_common::get_param($_POST,'id')));
        }
        nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'save', get_defined_vars());
    }

    if (nbf_common::get_param($_REQUEST, 'tax_rate_change_action'))
    {
        

        //If any products have custom tax rates, advise the user to check whether they need changing
        $sql = "SELECT COUNT(*) FROM #__nbill_product WHERE custom_tax_rate > 0";
        $nb_database->setQuery($sql);
        if ($nb_database->loadResult() > 0)
        {
            nbf_globals::$message .= " <br /><br />" . NBILL_TAX_RATE_CHANGE_CHECK_PRODUCT_CUSTOM;
        }

        
    }

    return true;
}



function deleteTax($id_array)
{
	$nb_database = nbf_cms::$interop->database;

	nbf_common::fire_event("tax_deleted", array("ids"=>implode(",", $id_array)));
    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'delete', get_defined_vars());

	//Delete tax record
	$sql = "DELETE FROM #__nbill_tax WHERE id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();
}

