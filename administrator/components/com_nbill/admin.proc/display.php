<?php
/**
* Main processing file for display options page
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
    case "apply":
		saveDisplayOptions();
		showDisplayOptions();
		break;
	case "save":
		saveDisplayOptions();
		include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.html/main.html.php");
		$_REQUEST['action'] = "main";
        $_REQUEST['task'] = "";
        nbf_common::load_language("main");
        nbf_common::load_language("widgets");
		nBillMain::main();
		break;
	case "cancel":
		if (nbf_globals::$message)
        {
            $message = nbf_common::get_param(array('message'=>nbf_globals::$message), 'message');
            nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . "&message=$message");
        }
        else
        {
            nbf_common::redirect(nbf_cms::$interop->admin_page_prefix);
        }
		break;
	default:
		nbf_globals::$message = "";
		showDisplayOptions();
		break;
}

function showDisplayOptions()
{
	include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
	$nb_database = nbf_cms::$interop->database;

	$sql = "SELECT * FROM #__nbill_display_options";
	$nb_database->setQuery($sql);
	$options = $nb_database->loadObjectList();
	if (!$options)
	{
		$options = array();
	}

    $sql = "SELECT #__nbill_extensions_links.* FROM #__nbill_extensions_links INNER JOIN #__nbill_extensions ON #__nbill_extensions_links.extension_name = #__nbill_extensions.extension_name ORDER BY ordering";
    $nb_database->setQuery($sql);
    $extension_links = $nb_database->loadObjectList();
    if (!$extension_links)
    {
        $extension_links = array();
    }
    foreach ($extension_links as $index=>$link)
    {
        $extension_links[$index]->ordering = $index; //Close any gaps
    }

	$sql = "SELECT * FROM #__nbill_additional_links ORDER BY ordering";
	$nb_database->setQuery($sql);
	$additional_links = $nb_database->loadObjectList();
	if (!$additional_links)
	{
		$additional_links = array();
	}

	$pay_freqs = nbf_xref::load_xref("pay_frequency");
    //Replace 'weekly' with 'always show' for clarity
    foreach ($pay_freqs as &$pay_freq)
    {
        if ($pay_freq->description == NBILL_WEEKLY)
        {
            $pay_freq->description = NBILL_ALWAYS_SHOW;
            break;
        }
    }

    //Check whether there is more than one language to choose from
    $choose_lang = false;
    $languages = array_diff(scandir(nbf_cms::$interop->nbill_admin_base_path . "/language/"), array('.', '..'));
    sort($languages);
    $array_size = count($languages);
    for ($lang_index = 0; $lang_index < $array_size; $lang_index++)
    {
        if (!is_dir(nbf_cms::$interop->nbill_admin_base_path . "/language/" . $languages[$lang_index]) || !nbf_common::nb_strpos($languages[$lang_index], "-") == 2)
        {
            unset($languages[$lang_index]);
        }
    }
    $languages = array_filter($languages);
    if (count($languages) > 1)
    {
        $choose_lang = true;
    }

    ob_start();
	nBillDisplay::showDisplayOptions($options, $extension_links, $additional_links, $pay_freqs, $choose_lang);
    $html = ob_get_clean();
    $output = nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'edit', get_defined_vars(), $html);
    echo $output;
}

function saveDisplayOptions()
{
	$nb_database = nbf_cms::$interop->database;

	//Delete existing values (will be re-added if applicable)
	$sql = "DELETE FROM #__nbill_additional_links";
	$nb_database->setQuery($sql);
	$nb_database->query();
	$sql = "DELETE FROM #__nbill_display_options";
	$nb_database->setQuery($sql);
	$nb_database->query();

    //Re-add and/or update
	$ordering = 0;
    $ext_ordering = 0;
	foreach ($_POST as $key=>$value)
	{
        if (substr($key, 0, 8) == "extlink_" && substr($key, strlen($key) - 5) == "_text")
        {
            $extlink_id = intval(substr($key, 8, strpos($key, '_', 8) - 8));
            $sql = "UPDATE #__nbill_extensions_links SET
                    link_text = '" . nbf_common::get_param($_REQUEST, 'extlink_' . $extlink_id . '_text', '', false, false, true, true) . "',
                    link_description = '" . nbf_common::get_param($_REQUEST, 'extlink_' . $extlink_id . '_desc') . "',
                    ordering = " . $ext_ordering . ",
                    published = " . (nbf_common::get_param($_REQUEST, 'extlink_' . $extlink_id . '_published') ? '1' : '0') . "
                    WHERE id = " . $extlink_id;
            $nb_database->setQuery($sql);
            $nb_database->query();
            $ext_ordering++;
        }
        else if (substr($key, 0, 4) == "link" && substr($key, 5) == "_url")
		{
			//Additional link
			$link_no = substr($key, 4, 1);
			if (nbf_common::nb_strlen($value) > 0 || nbf_common::nb_strlen(nbf_common::get_param($_POST,"link$link_no" . "_text")) > 0 || nbf_common::nb_strlen(nbf_common::get_param($_POST,"link$link_no" . "_desc")) > 0)
			{
				$ordering++;
				$sql = "INSERT INTO #__nbill_additional_links (ordering, url, text, description)
							VALUES ($ordering, '" . nbf_common::get_param($_POST,"link$link_no" . "_url") . "',
							'" . nbf_common::get_param($_POST,"link$link_no" . "_text") . "',
							'" . nbf_common::get_param($_POST,"link$link_no" . "_desc") . "')";
				$nb_database->setQuery($sql);
				$nb_database->query();
			}
		}
		else if (substr($key, 0, 4) != "link" && substr($key, 0, 16) != "category_filter_")
		{
			switch ($key)
			{
				case "option":
				case "action":
				case "task":
				case "box_checked":
				case "hidemainmenu":
				case "return":
				case "search_date_from":
				case "search_date_to":
				case "vendor_filter":
				case "client_search":
				case "product_search":
				case "nbill_no_search":
				case "rct_no_search":
				case "supplier_search":
				case "discount_search":
                case "contact_search":
                case "contact_user_search":
                case "contact_email_search":
                case "client_user_search":
                case "client_email_search":
                case "relating_to_search":
                case "status_search":
                case "name_search":
                case "description_search":
                case "rct_amount":
                case "pyt_no_search":
                case "paid_to_search":
                case "pyt_amount":
                case "supplier_user_search":
                case "supplier_email_search":
                case "nbill_selected_tab_global":
                case "nbill_selected_tab_my_profile":
                case "nbill_selected_tab_my_orders":
                case "nbill_selected_tab_my_quotes":
                case "nbill_selected_tab_my_invoices":
                case "date_range":
                	//Do nothing
					break;
				default:
					//Values default to yes, so only record those that are no
					if (($key == "pay_freq_paylink_threshold" && $value != "AA") || $key == "renew_link_advance_limit")
					{
						$sql = "INSERT INTO #__nbill_display_options (name, value) VALUES ('$key', '$value')";
						$nb_database->setQuery($sql);
						$nb_database->query();
					}
					else if ($value != 1 || $key=='due_date_no_of_units')
					{
						$sql = "INSERT INTO #__nbill_display_options (name, value) VALUES ('$key', '" . $nb_database->getEscaped($value) . "')";
						$nb_database->setQuery($sql);
						$nb_database->query();
					}

			}
		}
	}

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'save', get_defined_vars());
}