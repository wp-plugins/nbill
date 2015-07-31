<?php
/**
* Main processing file for payment gateway list
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
    case "uploadfile":
	case "new":
		installGateway();
		break;
	case "edit":
		editGateway($cid[0]);
		break;
	case "apply":
		saveGateway($id);
		if (!$id)
		{
			$id = intval(nbf_common::get_param($_POST,'id'));
		}
		editGateway($id);
		break;
	case "save":
		saveGateway($id);
		showGateways();
		break;
	case "remove":
	case "delete":
		deleteGateway($cid);
		showGateways();
		break;
	case "functions":
		showFunctions();
		break;
	case "include_only":
		break;
	case "orderup":
        moveGatewayUp($cid[0]);
        showGateways();
        break;
    case "orderdown":
        moveGatewayDown($cid[0]);
        showGateways();
        break;
    case "publish":
		publish_gateway($cid);
		showGateways();
		break;
	case "unpublish":
	  unpublish_gateway($cid);
	  showGateways();
	  break;
	default:
		showGateways();
		break;
}

function showGateways()
{
	$nb_database = nbf_cms::$interop->database;

	$query = "SELECT count(DISTINCT gateway_id) FROM #__nbill_payment_gateway_config";
	$nb_database->setQuery( $query );
	$total = $nb_database->loadResult();

	//Add page navigation
	$pagination = new nbf_pagination("gateway", $total);

	//Load the records
	$sql = "SELECT DISTINCT #__nbill_payment_gateway_config.gateway_id, #__nbill_payment_gateway.id,
					#__nbill_payment_gateway.g_value AS description, #__nbill_payment_gateway_config.display_name,
					#__nbill_payment_gateway_config.published
					FROM #__nbill_payment_gateway_config
                    LEFT JOIN #__nbill_payment_gateway
					ON #__nbill_payment_gateway.gateway_id = #__nbill_payment_gateway_config.gateway_id
					WHERE g_key = 'gateway_description' OR #__nbill_payment_gateway.gateway_id IS NULL
                    GROUP BY #__nbill_payment_gateway_config.gateway_id
                    ORDER BY #__nbill_payment_gateway_config.ordering
                    LIMIT $pagination->list_offset, $pagination->records_per_page";
	$nb_database->setQuery($sql);
	$rows = $nb_database->loadObjectList();
	if (!$rows)
	{
		$rows = array();
	}

    //Load XRef language file for built-in offline gateway
    nbf_common::load_language("xref");

	nBillGateway::showGateways($rows, $pagination);
}

function editGateway($id)
{
	if (nbf_cms::$interop->demo_mode)
    {
        echo NBILL_NOT_IN_DEMO_MODE;
        return;
    }

    $nb_database = nbf_cms::$interop->database;

	//Get the gateway id
	$sql = "SELECT gateway_id FROM #__nbill_payment_gateway WHERE id = $id";
	$nb_database->setQuery($sql);
	$gateway_id = $nb_database->loadResult();

	if (nbf_common::nb_strlen($gateway_id) > 0)
	{
		//Get display name, discount_id, and whether published
		$sql = "SELECT display_name, voucher_code, published FROM #__nbill_payment_gateway_config WHERE gateway_id = '$gateway_id'";
		$nb_database->setQuery($sql);
		$gateway_config = null;
		$nb_database->loadObject($gateway_config);

        //Load voucher codes
        $sql = "SELECT DISTINCT(voucher) FROM #__nbill_discounts WHERE LENGTH(voucher) > 0 ORDER BY is_fee DESC, voucher";
        $nb_database->setQuery($sql);
        $voucher_codes = $nb_database->loadObjectList();

        //Load settings
		$sql = "SELECT * FROM #__nbill_payment_gateway WHERE gateway_id = '$gateway_id' ORDER BY ordering";
		$nb_database->setQuery($sql);
		$gateway_settings = $nb_database->loadObjectList();
		if (!$gateway_settings)
		{
			$gateway_settings = array();
		}

        ob_start();
		nBillGateway::editGateway($id, $gateway_settings, $gateway_config->published, $gateway_config->display_name, $gateway_config->voucher_code, $voucher_codes);
        $html = ob_get_clean();
        $output = nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'edit', get_defined_vars(), $html);
        echo $output;
	}
	else
	{
		nbf_globals::$message = NBILL_ERR_GATEWAY_PROBLEM;
		showGateways();
	}
}

function saveGateway($id)
{
    if (!nbf_cms::$interop->demo_mode)
    {
	    $nb_database = nbf_cms::$interop->database;

	    //Get the gateway id
	    $sql = "SELECT gateway_id FROM #__nbill_payment_gateway WHERE id = $id";
	    $nb_database->setQuery($sql);
	    $gateway_id = $nb_database->loadResult();

	    //Update the config table
	    $published = nbf_common::get_param($_REQUEST, 'published') ? 1 : 0;
	    $display_name = nbf_common::get_param($_REQUEST, 'display_name', nbf_common::nb_ucwords(nbf_common::nb_strtolower($gateway_id)));
        $voucher_code = nbf_common::get_param($_REQUEST, 'voucher_code');
	    $sql = "UPDATE #__nbill_payment_gateway_config SET published = $published, display_name = '$display_name', voucher_code = '$voucher_code' WHERE gateway_id = '$gateway_id'";
	    $nb_database->setQuery($sql);
	    $nb_database->query();

	    foreach ($_POST as $key=>$value)
	    {
            //$value = replace_tokens($value, false, true);
		    if (substr($key, 0, 8) == "gateway_")
		    {
			    $sql = "UPDATE #__nbill_payment_gateway SET g_value = '" . $nb_database->getEscaped($value) . "' WHERE gateway_id = '$gateway_id' AND g_key = '" . substr($key, 8) . "'";
			    $nb_database->setQuery($sql);
			    $nb_database->query();
		    }
	    }

	    nbf_common::fire_event("record_updated", array("type"=>"gateway", "id"=>$id));

        nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'save', get_defined_vars());
    }
}

function publish_gateway($cid)
{
	$nb_database = nbf_cms::$interop->database;

	$gateway_id_list = get_gateway_id($cid);

	$sql = "UPDATE #__nbill_payment_gateway_config SET published = 1 WHERE gateway_id IN (" . implode(",", $gateway_id_list) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();
}

function unpublish_gateway($cid)
{
	$nb_database = nbf_cms::$interop->database;

	$gateway_id_list = get_gateway_id($cid);

	$sql = "UPDATE #__nbill_payment_gateway_config SET published = 0 WHERE gateway_id IN (" . implode(",", $gateway_id_list) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();
}

function deleteGateway($id_array)
{
	$nb_database = nbf_cms::$interop->database;

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'delete', get_defined_vars());

	foreach ($id_array as $id)
	{
		nbf_globals::$message = "";

		//Get the gateway id
		$sql = "SELECT gateway_id FROM #__nbill_payment_gateway WHERE id = $id";
		$nb_database->setQuery($sql);
		$gateway_id = $nb_database->loadResult();
		uninstallGateway($gateway_id);
	}
}

function uninstallGateway($gateway_id, $skip_db = false)
{
	$nb_database = nbf_cms::$interop->database;
	require_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.file.class.php");

	if (nbf_common::nb_strlen($gateway_id) > 0)
	{
		if (!$skip_db)
		{
			$sql = "DELETE FROM #__nbill_payment_gateway WHERE gateway_id = '$gateway_id'";
			$nb_database->setQuery($sql);
			$nb_database->query();

			$sql = "DELETE FROM #__nbill_payment_gateway_config WHERE gateway_id = '$gateway_id'";
			$nb_database->setQuery($sql);
			$nb_database->query();
		}
		else
		{
			$sql = "UPDATE #__nbill_payment_gateway_config SET published = 0 WHERE gateway_id = '$gateway_id'";
			$nb_database->setQuery($sql);
			$nb_database->query();
		}

		$error_message = ""; //Separate variable in case of existing message
		if (!nbf_file::remove_directory(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$gateway_id"))
		{
			$error_message = NBILL_ERR_GATEWAY_NOT_REMOVED;
		}
		if (!nbf_file::remove_directory(nbf_cms::$interop->nbill_fe_base_path . "/gateway/$gateway_id"))
		{
			$error_message = NBILL_ERR_GATEWAY_NOT_REMOVED;
	    }
		$sql = "SELECT file_path_admin, setup_filename FROM #__nbill_extensions WHERE gateway_id = '$gateway_id'";
		$nb_database->setQuery($sql);
		$nb_database->loadObject($setupfilename);

        if ($setupfilename && $setupfilename->file_path_admin && $setupfilename->setup_filename)
        {
            //Delete any files outside the gateway directories
            $install_contents = explode("\n", str_replace("\r\n", "\n", @file_get_contents($setupfilename->file_path_admin . $setupfilename->setup_filename)));
            $install_settings = array();
            $fail_reason = "";
            if (parse_setup_file($install_contents, $install_settings, $fail_reason))
            {
                if (isset($install_settings['admin_file']) && count($install_settings['admin_file']) > 0)
                {
                    foreach ($install_settings['admin_file'] as $admin_file)
                    {
                        if (substr($admin_file, 0, 3) == "../")
                        {
                            $admin_file = substr($admin_file, 3);
                        }
                        if (strlen($admin_file) > 0)
                        {
                            if (nbf_common::nb_strlen($admin_file) > 0 && file_exists(nbf_cms::$interop->nbill_admin_base_path . "/" . $admin_file))
                            {
                                @unlink(nbf_cms::$interop->nbill_admin_base_path . "/" . $admin_file);
                            }
                        }
                    }
                }
                if (isset($install_settings['file']) && count($install_settings['file']) > 0)
                {
                    foreach ($install_settings['file'] as $file)
                    {
                        if (substr($file, 0, 3) == "../")
                        {
                            $file = substr($file, 3);
                        }
                        if (strlen($file) > 0)
                        {
                            if (nbf_common::nb_strlen($file) > 0 && file_exists(nbf_cms::$interop->nbill_fe_base_path . "/" . $file))
                            {
                                @unlink(nbf_cms::$interop->nbill_fe_base_path . "/" . $file);
                            }
                        }
                    }
                }
            }

            @unlink($setupfilename->file_path_admin . $setupfilename->setup_filename);
        }
		$sql = "DELETE FROM #__nbill_extensions WHERE gateway_id = '$gateway_id'";
		$nb_database->setQuery($sql);
		$nb_database->query();

        //Close any gaps in the ordering
        $sql = "SELECT gateway_id, ordering FROM #__nbill_payment_gateway_config ORDER BY ordering";
        $nb_database->setQuery($sql);
        $gateways = $nb_database->loadObjectList();
        $ordering = 0;
        foreach ($gateways as $gateway)
        {
            $sql = "UPDATE #__nbill_payment_gateway_config SET ordering = $ordering WHERE gateway_id = " . intval($gateway->gateway_id);
            $nb_database->setQuery($sql);
            $nb_database->query();
            $ordering++;
        }

		if (nbf_common::nb_strlen($error_message) > 0)
		{
			nbf_globals::$message = $error_message;
		}
	}
	else
	{
		nbf_globals::$message = NBILL_ERR_GATEWAY_PROBLEM;
	}
}

function installGateway()
{
	nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . "&action=extensions");
    exit;
}

function showFunctions()
{
    $gateway_id = nbf_common::get_param($_REQUEST, 'gateway');
	if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$gateway_id/$gateway_id." . nbf_cms::$interop->language . ".php"))
	{
		include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$gateway_id/$gateway_id." . nbf_cms::$interop->language . ".php");
	}
	else
	{
		if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$gateway_id/$gateway_id" . ".en-GB.php"))
		{
			include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$gateway_id/$gateway_id" . ".en-GB.php");
		}
	}

	if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$gateway_id/$gateway_id.functions.html.php"))
	{
		include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$gateway_id/$gateway_id.functions.html.php");
	}
	if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$gateway_id/$gateway_id.functions.php"))
	{
		include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$gateway_id/$gateway_id.functions.php");
	}
}

function get_gateway_id($cid, $surround_in_quotes = true)
{
    $nb_database = nbf_cms::$interop->database;

    $sql = "SELECT gateway_id FROM #__nbill_payment_gateway WHERE id IN (" . implode(",", $cid) . ")";
    $nb_database->setQuery($sql);
    $gateway_ids = $nb_database->loadObjectList();
    if (!$gateway_ids)
    {
        $gateway_ids = array();
    }

    $gateway_id_list = array();
    foreach ($gateway_ids as $gateway_id)
    {
        $gateway_id_list[] = $surround_in_quotes ? "'" . $gateway_id->gateway_id . "'" : $gateway_id->gateway_id;
    }

    if ($cid == array(0))
    {
        //Offline gateway
        $gateway_id_list[] = $surround_in_quotes ? "'offline'" : "offline";
    }

    return $gateway_id_list;
}

function moveGatewayUp($cid)
{
    $nb_database = nbf_cms::$interop->database;
    $gateway_id_list = get_gateway_id(array($cid), false);

    if (count($gateway_id_list) == 1)
    {
        $gateway_id = $gateway_id_list[0];
        $sql = "SELECT ordering FROM #__nbill_payment_gateway_config WHERE gateway_id = '$gateway_id'";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($row);
        if ($row->ordering > 0)
        {
            $orderings = array();
            $sql = "SELECT gateway_id, ordering FROM #__nbill_payment_gateway_config ORDER BY ordering";
            $nb_database->setQuery($sql);
            $gateways = $nb_database->loadObjectList();
            if (!$gateways)
            {
                $gateways = array();
            }
            $prev_gateway_id = "-1";
            $ordering = 0;
            foreach ($gateways as $gateway)
            {
                if ($gateway->gateway_id == $gateway_id)
                {
                    $orderings[$gateway->gateway_id] = $orderings[$prev_gateway_id];
                    $orderings[$prev_gateway_id] = $ordering;
                }
                else
                {
                    $orderings[$gateway->gateway_id] = $ordering;
                }
                $ordering++;
                $prev_gateway_id = $gateway->gateway_id;
            }
            foreach ($orderings as $id=>$ordering)
            {
                $sql = "UPDATE #__nbill_payment_gateway_config SET ordering = $ordering WHERE gateway_id = '$id'";
                $nb_database->setQuery($sql);
                $nb_database->query();
            }
        }
    }
}

function moveGatewayDown($cid)
{
    $nb_database = nbf_cms::$interop->database;
    $gateway_id_list = get_gateway_id(array($cid), false);

    if (count($gateway_id_list) == 1)
    {
        $gateway_id = $gateway_id_list[0];
        $orderings = array();
        $sql = "SELECT gateway_id, ordering FROM #__nbill_payment_gateway_config ORDER BY ordering";
        $nb_database->setQuery($sql);
        $gateways = $nb_database->loadObjectList();
        if (!$gateways)
        {
            $gateways = array();
        }
        $next_gateway = false;
        $ordering = 0;
        foreach ($gateways as $gateway)
        {
            if ($next_gateway)
            {
                $orderings[$gateway->gateway_id] = $orderings[$gateway_id];
                $orderings[$gateway_id] = $ordering;
                $next_gateway = false;
            }
            else
            {
                $orderings[$gateway->gateway_id] = $ordering;
            }
            if ($gateway->gateway_id == $gateway_id)
            {
                $next_gateway = true;
            }
            $ordering++;
        }
        foreach ($orderings as $id=>$ordering)
        {
            $sql = "UPDATE #__nbill_payment_gateway_config SET ordering = $ordering WHERE gateway_id = '$id'";
            $nb_database->setQuery($sql);
            $nb_database->query();
        }
    }
}

function replace_tokens($string, $convert_line_breaks = true, $reverse = false)
{
    $nb_database = nbf_cms::$interop->database;

    $default_vendor_name = '';
    if (strpos($string, '[default_vendor_name]') !== false)
    {
        $sql = "SELECT vendor_name FROM #__nbill_vendor WHERE default_vendor = 1";
        $nb_database->setQuery($sql);
        $default_vendor_name = $nb_database->loadResult();
    }

    //Check whether to use https
    $sql = "SELECT id FROM #__nbill_configuration WHERE switch_to_ssl = 1 OR all_pages_ssl = 1";
    $nb_database->setQuery($sql);
    $ssl = $nb_database->loadResult() ? true : false;

    if (!$reverse)
    {
        $string = str_replace('[live_site]', ($ssl ? str_replace('http:', 'https:', nbf_cms::$interop->live_site) : nbf_cms::$interop->live_site), $string);
        $string = str_replace('[page_prefix]', nbf_cms::$interop->site_page_prefix, $string);
        $string = str_replace('[popup_page_prefix]', nbf_cms::$interop->site_popup_page_prefix, $string);
        $string = str_replace('[component_url]', ($ssl ? str_replace('http:', 'https:', nbf_cms::$interop->nbill_site_url_path) : nbf_cms::$interop->nbill_site_url_path), $string);
        $string = str_replace('[site_base_path]', nbf_cms::$interop->site_base_path, $string);
        $string = str_replace('[component_base_path]', nbf_cms::$interop->nbill_fe_base_path, $string);
        $string = str_replace('[default_vendor_name]', $default_vendor_name, $string);
        $string = str_replace('[strong]', '<strong>', $string);
        $string = str_replace('[/strong]', '</strong>', $string);
        $string = str_replace('[hr]', '<hr style="border:solid 1px #cccccc;" />', $string);
        $string = str_replace('[hr /]', '<hr style="border:solid 1px #cccccc;" />', $string);
        if ($convert_line_breaks)
        {
            $string = str_replace('[br]', '<br />', $string);
            $string = str_replace('[br /]', '<br />', $string);
            $string = str_replace('\\n', '<br />', $string);
        }
        else
        {
            $string = str_replace('\\n', "\n", $string);
        }
    }
    else
    {
        $string = str_replace(($ssl ? str_replace('http:', 'https:', nbf_cms::$interop->live_site) : nbf_cms::$interop->live_site), '[live_site]', $string);
        $string = str_replace(nbf_cms::$interop->site_page_prefix, '[page_prefix]', $string);
        $string = str_replace(nbf_cms::$interop->site_popup_page_prefix, '[popup_page_prefix]', $string);
        $string = str_replace(($ssl ? str_replace('http:', 'https:', nbf_cms::$interop->nbill_site_url_path) : nbf_cms::$interop->nbill_site_url_path), '[component_url]', $string);
        $string = str_replace(nbf_cms::$interop->site_base_path, '[site_base_path]', $string);
        $string = str_replace(nbf_cms::$interop->nbill_fe_base_path, '[component_base_path]', $string);
        if ($convert_line_breaks)
        {
            $string = str_replace('<br />', '[br]', $string);
            $string = str_replace('<br />', '[br /]', $string);
            $string = str_replace('<br />', '\\n', $string);
        }
        else
        {
            $string = str_replace("\n", '\\n', $string);
        }
    }
    return $string;
}