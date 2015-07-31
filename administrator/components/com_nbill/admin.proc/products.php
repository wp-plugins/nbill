<?php
/**
* Main processing file for products
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
		editProduct($cid[0], intval(nbf_common::get_param($_POST, 'use_posted_values')));
		break;
	case "apply":
		if (saveProduct())
        {
		    if (!$id)
		    {
			    $id = intval(nbf_common::get_param($_POST,'id'));
		    }
		    editProduct($id);
        }
		break;
	case "save":
		if (saveProduct())
        {
		    if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'return')) > 0)
		    {
			    nbf_common::redirect(base64_decode(nbf_common::get_param($_REQUEST,'return')));
			    break;
		    }
		    showProducts();
        }
		break;
    case "save_copy":
        copyProduct();
        showProducts();
        break;
	case "remove":
	case "delete":
		deleteProducts($cid);
		showProducts();
		break;
	case "cancel":
		if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'return')) > 0)
		{
			nbf_common::redirect(base64_decode(nbf_common::get_param($_REQUEST,'return')));
			break;
		}
		nbf_globals::$message = "";
		showProducts();
		break;
    
	default:
		nbf_globals::$message = "";
		showProducts();
		break;
}

function showProducts()
{
    $nb_database = nbf_cms::$interop->database;

	//Get Vendor list
	$sql = "SELECT id, vendor_name FROM #__nbill_vendor ORDER BY id";
	$nb_database->setQuery($sql);
	$vendors = $nb_database->loadObjectList();

	//Count the total number of records
	$cat_filter = nbf_common::get_param($_POST, "category_filter_" . nbf_globals::$vendor_filter);

	$query = "SELECT count(*) FROM #__nbill_product";
	$whereclause = "";
	if ((nbf_common::nb_strlen(nbf_globals::$vendor_filter) > 0 && nbf_globals::$vendor_filter != -999) || (nbf_common::nb_strlen($cat_filter) > 0 && $cat_filter != -999))
	{
		$whereclause .= " WHERE ";
		if (nbf_common::nb_strlen(nbf_globals::$vendor_filter) > 0 && nbf_globals::$vendor_filter != -999)
		{
			$whereclause .= "#__nbill_product.vendor_id = " . intval(nbf_globals::$vendor_filter);
			if (nbf_common::nb_strlen($cat_filter) > 0 && $cat_filter != -999)
			{
				$whereclause .= " AND ";
			}
		}
		if (nbf_common::nb_strlen($cat_filter) > 0 && $cat_filter != -999)
		{
			$whereclause .= "#__nbill_product.category = " . $cat_filter;
		}
	}
	$query .= $whereclause;
	$nb_database->setQuery( $query );
	$total = $nb_database->loadResult();

	//Add page navigation
	$pagination = new nbf_pagination("product", $total);

	//Get Categories
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.category.class.php");
	$cats = array();
	foreach ($vendors as $vendor)
	{
		$cats[$vendor->id] = nbf_category_hierarchy::get_category_hierarchy($vendor->id);
	}

	//Load the records
	$sql = "SELECT #__nbill_product.*, #__nbill_product_category.name AS category_name, COUNT(#__nbill_supporting_docs.id) AS attachment_count
            FROM #__nbill_product
            LEFT JOIN #__nbill_product_category ON #__nbill_product.category = #__nbill_product_category.id
            LEFT JOIN #__nbill_supporting_docs ON #__nbill_product.id = #__nbill_supporting_docs.associated_doc_id AND #__nbill_supporting_docs.associated_doc_type = 'PR' ";
	$sql .= $whereclause;
	$sql .= " GROUP BY #__nbill_product.id ORDER BY product_code, name ";
    if (!nbf_common::get_param($_REQUEST, 'do_csv_download'))
    {
        $sql .= "LIMIT $pagination->list_offset, $pagination->records_per_page";
    }
    else
    {
        $sql .= "LIMIT " . nbf_globals::$record_limit;
    }
	$nb_database->setQuery($sql);
	$rows = $nb_database->loadObjectList();
	if (!$rows)
	{
		$rows = array();
	}

    //Get any attachments
    $attachments = array();
    

    //For CSV download, we need the prices too
    $max_currencies_per_product = 0;
    if (nbf_common::get_param($_REQUEST, 'do_csv_download'))
    {
        //Get list of ids
        $product_ids = array();
        foreach ($rows as $row)
        {
            $product_ids[] = $row->id;
        }

        //Load prices
        $sql = "SELECT * FROM #__nbill_product_price WHERE product_id IN (" . implode(",", $product_ids) . ") ORDER BY product_id, currency_code";
        $nb_database->setQuery($sql);
        $product_prices = $nb_database->loadObjectList();

        $currencies = array();
        if ($product_prices && count($product_prices) > 0)
        {
            //Count max number of currencies
            $sql = "SELECT COUNT(*) AS currency_count FROM #__nbill_product_price
                    INNER JOIN #__nbill_product ON #__nbill_product.id = #__nbill_product_price.product_id
                    WHERE product_id IN (" . implode(",", $product_ids) . ")
                    GROUP BY product_id ORDER BY currency_count DESC LIMIT 1";
            $nb_database->setQuery($sql);
            $max_currencies_per_product = $nb_database->loadResult();
            if ($max_currencies_per_product > 0)
            {
                foreach ($product_prices as $product_price)
                {
                    if (array_search($product_price->currency_code, $currencies) === false)
                    {
                        $currencies[] = $product_price->currency_code;
                    }
                }
            }
        }
        else
        {
            $product_prices = array();
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
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="products_' . nbf_common::nb_date("Y-m-d") . '.csv"');
        nBillProducts::downloadProductsCSV($vendors, $rows, $product_prices, $max_currencies_per_product, $currencies);
        exit;
    }
    else
    {
	    nBillProducts::showProducts($rows, $pagination, $vendors, $cats, $attachments);
    }
}

function editProduct($product_id, $use_posted_values = false)
{
	include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
    $nb_database = nbf_cms::$interop->database;

	//Load Product
	$row = $nb_database->load_record("#__nbill_product", $product_id);
    if (!$row->id) {
        $row->electronic_delivery = nBillConfigurationService::getInstance()->getConfig()->default_electronic;
    }

	//Load Vendors
	$sql = "SELECT id, vendor_name, vendor_currency FROM #__nbill_vendor ORDER BY id";
	$nb_database->setQuery($sql);
	$vendors = $nb_database->loadObjectList();

	//Load categories
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.category.class.php");
	$cats = array();
	foreach ($vendors as $vendor)
	{
        $cats[$vendor->id] = nbf_category_hierarchy::get_category_hierarchy($vendor->id);
	}

	//Load shipping options
	$shipping = array();
	$nominal_ledger = array();
	foreach ($vendors as $vendor)
	{
		$sql = "SELECT id, code, service as description FROM #__nbill_shipping WHERE vendor_id = $vendor->id ORDER BY code, service";
		$nb_database->setQuery($sql);
		$shipping[$vendor->id] = $nb_database->loadObjectList();
		if (!isset($shipping[$vendor->id]) || !$shipping[$vendor->id])
		{
			$shipping[$vendor->id] = array();
		}

		//Load Ledger
		$sql = "SELECT code, description, vendor_id FROM #__nbill_nominal_ledger WHERE vendor_id = $vendor->id ORDER BY code";
		$nb_database->setQuery($sql);
		$nominal_ledger[$vendor->id] = $nb_database->loadObjectList();
		if (!isset($nominal_ledger[$vendor->id]) || !$nominal_ledger[$vendor->id])
		{
			$nominal_ledger[$vendor->id] = array();
		}
	}

	//Load Prices
	$prices = array();
	foreach ($vendors as $vendor)
	{
		if ($row->id)
        {
            $sql = "SELECT * FROM #__nbill_product_price WHERE vendor_id = " . $vendor->id . " AND product_id = " .  $row->id;
		    $nb_database->setQuery($sql);
		    $prices[$vendor->id] = $nb_database->loadObjectList();
        }
		if (!isset($prices[$vendor->id]) || !$prices[$vendor->id])
		{
			$prices[$vendor->id] = array();
		}
	}

    //Load all non-auto-renew orders for this product, including payment frequencies (so we can offer to update price if applicable)
    $existing_orders = array();
    

	$selected_cats = array();
	if (!$row->id)
	{
		//Default new records to the selected filter category
		foreach ($vendors as $vendor)
		{
			$selected_cats[$vendor->id] = nbf_common::get_param($_POST, 'category_filter_' . $vendor->id);
		}
	}

	//Load Discounts
	$discounts = array();
	$product_discounts = array();
	foreach ($vendors as $vendor)
	{
		$product_discounts[$vendor->id] = array();
		$sql = "SELECT id, discount_name FROM #__nbill_discounts WHERE vendor_id = " . $vendor->id . " ORDER BY discount_name";
		$nb_database->setQuery($sql);
		$discounts[$vendor->id] = $nb_database->loadObjectList();
		if (!isset($discounts[$vendor->id]) || !$discounts[$vendor->id])
		{
			$discounts[$vendor->id] = array();
		}
		if ($product_id)
		{
			$sql = "SELECT * FROM #__nbill_product_discount WHERE vendor_id = " . $vendor->id . " AND product_id = '$product_id' ORDER BY priority";
			$nb_database->setQuery($sql);
			$product_discounts[$vendor->id] = $nb_database->loadObjectList();
			if (!isset($product_discounts[$vendor->id]) || !$product_discounts[$vendor->id])
			{
				$product_discounts[$vendor->id] = array();
			}
		}
	}

    //Get any attachments
    $attachments = array();
    

    ob_start();
	nBillProducts::editProduct($product_id, $row, $vendors, $cats, $nominal_ledger, $shipping, $prices, nbf_cms::$interop->get_acl_group_list(), $selected_cats, $discounts, $product_discounts, $existing_orders, $price_match_found, nbf_cms::$interop->user_sub_plugin_present(), $use_posted_values, $attachments);
    $html = ob_get_clean();
    $output = nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'edit', get_defined_vars(), $html);
    echo $output;
}

function saveProduct()
{
    $nb_database = nbf_cms::$interop->database;

	//Select values from appropriate dropdown lists based on vendor_id
	$_POST['category'] = nbf_common::get_param($_POST, 'category_' . nbf_common::get_param($_POST, 'vendor_id'));
	$_POST['nominal_ledger_code'] = nbf_common::get_param($_POST, 'ledger_' . nbf_common::get_param($_POST, 'vendor_id'));
	$shipping_services = nbf_common::get_param($_POST, 'shipping_' . nbf_common::get_param($_POST, 'vendor_id'));
	if (count($shipping_services) > 0)
	{
		$_POST['shipping_services'] = implode(",", $shipping_services);
	}
	else
	{
		$_POST['shipping_services'] = "";
	}

	//Set appropriate expiry redirect value
	if (nbf_common::get_param($_POST, 'is_sub'))
	{
		switch (nbf_common::get_param($_POST, 'opt_expiry_redirect'))
		{
			case "0":
				//Default
				$_POST['expiry_redirect'] = "";
				break;
			case "1":
				//Default
				$_POST['expiry_redirect'] = urlencode(nbf_cms::$interop->site_page_prefix . "&action=subexpiry&task=message" . nbf_cms::$interop->site_page_suffix);
				break;
			default:
				//URL
				$_POST['expiry_redirect'] = urlencode($_POST['redirect_url']);
				break;
		}
	}
	else
	{
		$_POST['user_group'] = 0;
		$_POST['expiry_level'] = 0;
		$_POST['expiry_redirect'] = "";
	}

	if (nbf_cms::$interop->demo_mode)
	{
		$_POST['download_location_1'] = "";
		$_POST['download_location_2'] = "";
		$_POST['download_location_3'] = "";
	}

	$nb_database->bind_and_save("#__nbill_product", $_POST);

    $insert = false;
	if (!$_POST['id'])
	{
		$_POST['id'] = $nb_database->insertid();
		$insert = true;
	}
    else
    {
        //Update category ID on any form fields/options
        $sql = "UPDATE #__nbill_order_form_fields SET related_product_cat = " . intval(@$_POST['category']) . " WHERE related_product = " . intval($_POST['id']);
        $nb_database->setQuery($sql);
        $nb_database->query();
        $sql = "UPDATE #__nbill_order_form_fields_options SET related_product_cat = " . intval(@$_POST['category']) . " WHERE related_product = " . intval($_POST['id']);
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

	$product_id = nbf_common::get_param($_POST,'id');

    if (!$insert)
    {
        //In case vendor ID has changed, update vendor ID on prices
        $sql = "UPDATE #__nbill_product_price SET vendor_id = " . intval(nbf_common::get_param($_POST, 'vendor_id')) . " WHERE product_id = " . intval($product_id);
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

    //Get IDs of existing orders to update prices of, if applicable
    $existing_order_ids = array();
    $all_existing_orders = array();
    

	//Save Prices
	foreach ($_POST as $key=>$value)
	{
		$value = nbf_common::get_param($_POST, $key);
		if (substr($key, 0, 10) == "net_price_")
		{
			$vendor_id = substr($key, (nbf_common::nb_strrpos($key, "_")) + 1);
			if ($vendor_id == nbf_common::get_param($_POST, 'vendor_id'))
			{
				$update_values = array();
				$key_parts = explode("_", $key);
                $currency = $key_parts[count($key_parts) - 2];
                $field_name = "";
                for ($i = 0; $i < count($key_parts) - 2; $i++)
                {
                    $field_name .= $key_parts[$i] . "_";
                }
                $field_name = nbf_common::nb_substr($field_name, 0, nbf_common::nb_strlen($field_name) - 1);
				$sql = "SELECT product_id FROM #__nbill_product_price WHERE vendor_id = $vendor_id AND
								product_id = $product_id AND currency_code = '$currency'";
				$nb_database->setQuery($sql);
				$results = $nb_database->loadObjectList();
				if (!$results)
				{
					$results = array();
				}
				if (count($results) > 0)
				{
					//Update
					$sql = "UPDATE #__nbill_product_price SET $field_name = $value WHERE
								vendor_id = $vendor_id AND product_id = $product_id AND currency_code = '$currency'";
				}
				else
				{
					//Insert
					$sql = "INSERT INTO #__nbill_product_price (vendor_id, product_id, currency_code, $field_name)
							VALUES ($vendor_id, $product_id, '$currency', $value)";
				}
				$nb_database->setQuery($sql);
				$nb_database->query();
			}
		}
	}

    

	if ($insert) {
		nbf_common::fire_event("product_created", array("id"=>$product_id));
	} else {
		nbf_common::fire_event("record_updated", array("type"=>"product", "id"=>$product_id));
	}

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'save', get_defined_vars());

    

    return true;
}

function copyProduct()
{
    $_POST['id'] = null;
    $_POST['name'] = NBILL_COPY_OF . @$_POST['name'];
    saveProduct();
}

function deleteProducts($id_array)
{
	$nb_database = nbf_cms::$interop->database;

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'delete', get_defined_vars());
	nbf_common::fire_event("product_deleted", array("ids"=>implode(",", $id_array)));

	//Check whether any order forms use this product
	$sql = "SELECT #__nbill_order_form.title FROM #__nbill_order_form
				INNER JOIN #__nbill_order_form_fields ON
				#__nbill_order_form_fields.form_id = #__nbill_order_form.id
				WHERE #__nbill_order_form_fields.related_product IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$forms = $nb_database->loadObjectList();
	if (!$forms)
	{
		$forms = array();
	}

	$sql = "SELECT #__nbill_order_form.title FROM #__nbill_order_form
				INNER JOIN #__nbill_order_form_fields_options ON
				#__nbill_order_form_fields_options.form_id = #__nbill_order_form.id
				WHERE #__nbill_order_form_fields_options.related_product IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$forms2 = $nb_database->loadObjectList();
	if (!$forms2)
	{
		$forms2 = array();
	}

	$forms3 = array();
	if ($forms)
	{
		foreach ($forms as $form)
		{
			$forms3[] = $form->title;
		}
	}
	if ($forms2)
	{
		foreach ($forms2 as $form2)
		{
			$forms3[] = $form2->title;
		}
	}
	$forms3 = array_unique($forms3);

	if (count($forms3) > 0)
	{
		nbf_globals::$message = sprintf(NBILL_ERR_PRODUCT_IN_USE, implode(", ", $forms3));
		return;
	}

	//Delete product record
	$sql = "DELETE FROM #__nbill_product WHERE id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

	//Delete product discount associations
	$sql = "DELETE FROM #__nbill_product_discount WHERE product_id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

    //Detach any attachments
    $sql = "DELETE FROM #__nbill_supporting_docs WHERE associated_doc_type = 'PR' AND associated_doc_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();
}