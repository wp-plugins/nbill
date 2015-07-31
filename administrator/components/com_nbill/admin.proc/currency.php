<?php
/**
* Main processing file for currency feature
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
		editCurrency($cid[0]);
		break;
	case "apply":
		saveCurrency();
		if (!$id)
		{
			$id = nbf_common::get_param($_POST,'id'); //$nb_database->insertid();
		}
		editCurrency($id);
		break;
	case "save":
		saveCurrency();
		showCurrency();
		break;
	case "remove":
	case "delete":
		deleteCurrency($cid);
		showCurrency();
		break;
	default:
		nbf_globals::$message = "";
		showCurrency();
		break;
}

function showCurrency()
{
	$nb_database = nbf_cms::$interop->database;

	//Count the total number of records
	$query = "SELECT count(*) FROM #__nbill_currency";
	$nb_database->setQuery($query);
	$total = $nb_database->loadResult();

	//Add page navigation
	$pagination = new nbf_pagination("currency", $total);

	//Load the records
	$sql = "SELECT * FROM #__nbill_currency ORDER BY code LIMIT $pagination->list_offset, $pagination->records_per_page";
	$nb_database->setQuery($sql);
	$rows = $nb_database->loadObjectList();
	if (!$rows)
	{
		$rows = array();
	}

	nBillCurrency::showCurrency($rows, $pagination);
}

function editCurrency($currency_id)
{
	$nb_database = nbf_cms::$interop->database;
    $row = $nb_database->load_record("#__nbill_currency", intval($currency_id));
    ob_start();
    nBillCurrency::editCurrency($currency_id, $row);
    $html = ob_get_clean();
    $output = nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'edit', get_defined_vars(), $html);
    echo $output;
}

function saveCurrency()
{
    $nb_database = nbf_cms::$interop->database;
	$old_code = "";
	if (nbf_common::get_param($_POST, 'id'))
	{
		//Get old code value
		$sql = "SELECT code FROM #__nbill_currency WHERE id = " .  nbf_common::get_param($_POST, 'id');
		$nb_database->setQuery($sql);
		$old_code = $nb_database->loadResult();
	}

    $nb_database->bind_and_save("currency", $_POST);

	//Update code wherever else it has been used
	$new_code = nbf_common::get_param($_POST, 'code');
	if ($old_code && $old_code != $new_code)
	{
		$sql = "UPDATE #__nbill_vendor SET vendor_currency = '$new_code' WHERE vendor_currency = '$old_code'";
		$nb_database->setQuery($sql);
		$nb_database->query();
		$sql = "UPDATE #__nbill_shipping_price SET currency_code = '$new_code' WHERE currency_code = '$old_code'";
		$nb_database->setQuery($sql);
		$nb_database->query();
		$sql = "UPDATE #__nbill_discount_currency_amount SET currency = '$new_code' WHERE currency = '$old_code'";
		$nb_database->setQuery($sql);
		$nb_database->query();
		$sql = "UPDATE #__nbill_transaction SET currency = '$new_code' WHERE currency = '$old_code'";
		$nb_database->setQuery($sql);
		$nb_database->query();
		$sql = "UPDATE #__nbill_transaction_ledger SET currency = '$new_code' WHERE currency = '$old_code'";
		$nb_database->setQuery($sql);
		$nb_database->query();
		$sql = "UPDATE #__nbill_transaction SET currency = '$new_code' WHERE currency = '$old_code'";
		$nb_database->setQuery($sql);
		$nb_database->query();
		$sql = "UPDATE #__nbill_transaction_ledger SET currency = '$new_code' WHERE currency = '$old_code'";
		$nb_database->setQuery($sql);
		$nb_database->query();
		$sql = "UPDATE #__nbill_document SET currency = '$new_code' WHERE currency = '$old_code'";
		$nb_database->setQuery($sql);
		$nb_database->query();
		$sql = "UPDATE #__nbill_order_form SET currency = '$new_code' WHERE currency = '$old_code'";
		$nb_database->setQuery($sql);
		$nb_database->query();
		$sql = "UPDATE #__nbill_orders SET currency = '$new_code' WHERE currency = '$old_code'";
		$nb_database->setQuery($sql);
		$nb_database->query();
		$sql = "UPDATE #__nbill_pending_orders SET currency = '$new_code' WHERE currency = '$old_code'";
		$nb_database->setQuery($sql);
		$nb_database->query();
		$sql = "UPDATE #__nbill_product_price SET currency_code = '$new_code' WHERE currency_code = '$old_code'";
		$nb_database->setQuery($sql);
		$nb_database->query();
	}

	if (!nbf_common::get_param($_POST, 'id'))
	{
		$_POST['id'] = $nb_database->insertid();
		nbf_common::fire_event("currency_created", array("id"=>nbf_common::get_param($_POST, 'id')));
	}
	else
	{
		nbf_common::fire_event("record_updated", array("type"=>"currency", "id"=>nbf_common::get_param($_POST, 'id')));
	}

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'save', get_defined_vars());
}

function deleteCurrency($id_array)
{
	$nb_database = nbf_cms::$interop->database;

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'delete', get_defined_vars());
	nbf_common::fire_event("currency_deleted", array("ids"=>implode(",", $id_array)));

	//Count the total number of records
	$query = "SELECT count(*) FROM #__nbill_currency";
	$nb_database->setQuery( $query );
	$total = $nb_database->loadResult();

	//Sanitise id array
	for ($i = 0; $i < count($id_array); $i++)
	{
        $id_array[$i] = intval($id_array[$i]);
    }

	if ($total > count($id_array))  //Must always have at least one currency
	{
		//Make sure this currency is not being used by any vendors themselves
		$sql = "SELECT #__nbill_vendor.vendor_currency FROM #__nbill_vendor INNER JOIN #__nbill_currency
					 ON #__nbill_vendor.vendor_currency = #__nbill_currency.code WHERE #__nbill_currency.id
					 IN (" . implode(",", $id_array) . ")";
		$nb_database->setQuery($sql);
		$vendor_currencies = $nb_database->loadObjectList();
		if (!$vendor_currencies)
		{
			$vendor_currencies = array();
		}

		if (count($vendor_currencies) > 0)
		{
			nbf_globals::$message = NBILL_ERR_CANNOT_DELETE_CURRENCY_IN_USE;
		}
		else
		{
			//If currency is in use by any clients, revert their currency to the default of the vendor
			$sql = "SELECT #__nbill_entity.id as client_id, #__nbill_vendor.vendor_currency FROM #__nbill_entity INNER JOIN #__nbill_currency
							ON #__nbill_entity.default_currency = #__nbill_currency.code INNER JOIN #__nbill_vendor
							ON #__nbill_entity.vendor_id = #__nbill_vendor.id WHERE #__nbill_currency.id
							IN (" . implode(",", $id_array) . ")";
			$nb_database->setQuery($sql);
			$clients = $nb_database->loadObjectList();
			if (!$clients)
			{
				$clients = array();
			}
			if (count($clients) > 0)
			{
				foreach($clients as $client)
				{
					$sql = "UPDATE #__nbill_entity SET default_currency = '" . $client->vendor_currency . "' WHERE id = " . $client->client_id;
					$nb_database->setQuery($sql);
					$nb_database->query();
				}
			}

            //Get codes
            $sql = "SELECT `code` FROM #__nbill_currency WHERE id IN (" . implode(",", $id_array) . ")";
            $nb_database->setQuery($sql);
            $codes = $nb_database->loadResultArray();

			//Delete currency record
			$sql = "DELETE FROM #__nbill_currency WHERE id IN (" . implode(",", $id_array) . ")";
			$nb_database->setQuery($sql);
			$nb_database->query();

            //Delete any prices held in this currency
            $sql = "DELETE FROM #__nbill_product_price WHERE currency_code IN ('" . implode("', '", $codes) . "')";
            $nb_database->setQuery($sql);
            $nb_database->query();
            $sql = "DELETE FROM #__nbill_discount_currency_amount WHERE currency IN ('" . implode("', '", $codes) . "')";
            $nb_database->setQuery($sql);
            $nb_database->query();
            $sql = "DELETE FROM #__nbill_client_credit WHERE currency IN ('" . implode("', '", $codes) . "')";
            $nb_database->setQuery($sql);
            $nb_database->query();
		}
	}
	else
	{
		nbf_globals::$message = NBILL_ERR_CANNOT_DELETE_LAST_CURRENCY;
	}
}