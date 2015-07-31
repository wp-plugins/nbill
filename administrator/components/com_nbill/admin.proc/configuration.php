<?php
/**
* Main processing file for global configuration page
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

$task = nbf_common::get_param($_REQUEST, 'task');
if ($task != "nothing")
{
}

switch ($task)
{
    
	case "apply":
		saveConfiguration();
        nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . '&action=configuration&nbill_selected_tab_admin_settings=' . nbf_common::get_param($_REQUEST, 'nbill_selected_tab_admin_settings')); //Must redirect to pick up new timezone
		//showConfiguration();
		break;
	case "save":
		saveConfiguration();
		showMain();
		break;
	case "cancel":
		showMain();
		break;
	case "cleartables":
		if (!nbf_cms::$interop->demo_mode)
		{
			include_once(nbf_cms::$interop->nbill_admin_base_path . "/install.new.php");
            nbf_globals::$db_errors = array();
			new_db_install();
            if (count(nbf_globals::$db_errors) == 0)
            {
                nbf_globals::$message = strtolower(nbf_version::$suffix) == 'lite' ? NBILL_CFG_TABLES_CLEARED_LITE : NBILL_CFG_TABLES_CLEARED;
            }
            else
            {
                nbf_globals::$message = NBILL_CFG_TABLES_CLEARED_ERR;
                nbf_globals::$message .= "\n\n";
                nbf_globals::$message .= implode("\n", nbf_globals::$db_errors);
            }
		}
		showConfiguration();
		break;
	case "deletetables":
        if (!nbf_cms::$interop->demo_mode)
        {
	        nbf_cms::$interop->database->delete_tables();
	        $uninstall_link = " <a href=\"" . nbf_cms::$interop->admin_component_uninstaller . "\">" . NBILL_CFG_UNINSTALLER . "</a>";
	        nbf_globals::$message = sprintf(NBILL_CFG_TABLES_DELETED, $uninstall_link);
        }
		showConfiguration();
		break;
	case "check_version":
		if (!nbf_cms::$interop->demo_mode)
        {
            nbf_upgrader::check_version(true);
		    if (nbf_common::get_param($_GET, 'return') == "home")
		    {
                nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . "&message=" . nbf_globals::$message);
		    }
		    else
		    {
			    showConfiguration();
		    }
        }
        else
        {
            echo NBILL_NOT_IN_DEMO_MODE;
        }
		break;
	case "update_now":
        if (!nbf_cms::$interop->demo_mode)
        {
            nbf_upgrader::check_version(true, true);
		    if (nbf_common::get_param($_GET, 'return') == "home")
		    {
			    nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . "&message=" . urlencode(nbf_globals::$message));
		    }
		    else
		    {
			    showConfiguration();
		    }
        }
        else
        {
            echo NBILL_NOT_IN_DEMO_MODE;
        }
		break;
    case "migrate":
        if (!nbf_cms::$interop->demo_mode)
        {
            nbf_upgrader::migrate();
            if (nbf_common::get_param($_GET, 'return') == "home")
            {
                nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . "&message=" . urlencode(nbf_globals::$message));
            }
            else
            {
                showConfiguration();
            }
        }
        else
        {
            echo NBILL_NOT_IN_DEMO_MODE;
        }
        break;
	case "nothing":
    case "silent": //Access to functions only
		break;
	default:
		nbf_globals::$message ="";
		showConfiguration();
		break;
}

function showMain()
{
	if (nbf_globals::$message)
	{
        $message_array = array('message'=>nbf_globals::$message);
        $message = nbf_common::get_param($message_array, 'message');
		nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . "&message=$message");
	}
	else
	{
		nbf_common::redirect(nbf_cms::$interop->admin_page_prefix);
	}
}

function showConfiguration()
{
	include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
	$nb_database = nbf_cms::$interop->database;

    //Load the config record
	$sql = "SELECT * FROM #__nbill_configuration";
	$nb_database->setQuery($sql);
	$nb_database->loadObject($row);
    if (!$row)
    {
        //Tables just deleted
        $row = new stdClass();
        $row->id = false;
        $row->default_user_groups = "";
        $row->cron_auth_token = "";
    }

    if (!$row->default_user_groups)
    {
        $row->default_user_groups = nbf_cms::$interop->cms_database_enum->registered_gid;
    }

	//Load the xrefs
	$xref_default_start_date = nbf_xref::load_xref("default_start_date");
    $email_options_xref = nbf_xref::load_xref("email_invoice");

    $license_key = '';
    

    $ftp_success = false;
    $ftp_message = "";
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.config.php");
    if (nbf_common::get_param($_REQUEST, 'test_ftp'))
    {
        nbf_config::$ftp_address = nbf_common::get_param($_REQUEST, 'ftp_address');
        nbf_config::$ftp_port = nbf_common::get_param($_REQUEST, 'ftp_port');
        nbf_config::$ftp_username = nbf_common::get_param($_REQUEST, 'ftp_username');
        nbf_config::$ftp_password = nbf_common::get_param($_REQUEST, 'ftp_password');
        nbf_config::$ftp_root = nbf_common::get_param($_REQUEST, 'ftp_root');
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.file.class.php");
        $use_ftp = true;
        $ftp_success = nbf_file::is_test_file_writable($use_ftp, $ftp_message);
        if ($ftp_success)
        {
            $ftp_message = NBILL_CFG_FTP_CONNECT_SUCCESSFUL;
        }
    }
    else
    {
        //Load FTP connection details
        nbf_cms::$interop->get_ftp_details(nbf_config::$ftp_address, nbf_config::$ftp_port, nbf_config::$ftp_username, nbf_config::$ftp_password, nbf_config::$ftp_root);
    }

    

    ob_start();
	nBillConfig::showConfig($row, $license_key, nbf_cms::$interop->get_acl_group_list(), $email_options_xref, $xref_default_start_date, nbf_config::$ftp_address, nbf_config::$ftp_port, nbf_config::$ftp_username, nbf_config::$ftp_password, nbf_config::$ftp_root, $ftp_success, $ftp_message);
    $html = ob_get_clean();
    $output = nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'edit', get_defined_vars(), $html);
    echo $output;
}

function saveConfiguration()
{
    $nb_database = nbf_cms::$interop->database;

	if (nbf_cms::$interop->demo_mode)
	{
        nbf_globals::$message = NBILL_CFG_NO_SAVE_ON_DEMO;
        return;
	}

    nbf_globals::$message = '';
	$error_email = nbf_common::get_param($_POST,'error_email');
	$date_format = nbf_common::get_param($_POST,'date_format');
    $locale_string = nbf_common::get_param($_POST,'locale');
	$show_user_list = intval(nbf_common::get_param($_POST,'select_users_from_list'));
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/license.nbill.php");
    
	$version_auto_check = intval(nbf_common::get_param($_POST, 'version_auto_check'));
	$auto_update = intval(nbf_common::get_param($_POST, 'auto_update'));
	$default_start_date = nbf_common::get_param($_POST, 'default_start_date');
    $default_itemid = intval(nbf_common::get_param($_POST, 'default_itemid'));
    $redirect_to_itemid = intval(nbf_common::get_param($_POST, 'redirect_to_itemid'));
	$switch_to_ssl = intval(nbf_common::get_param($_POST, 'switch_to_ssl'));
    $all_pages_ssl = intval(nbf_common::get_param($_POST, 'all_pages_ssl'));
    $default_user_groups = nbf_common::get_param($_POST, 'default_user_groups');
    $email_invoice_option = nbf_common::get_param($_POST, 'email_invoice_option');
    $title_colour = nbf_common::get_param($_POST, 'title_colour');
    $heading_bg_colour = nbf_common::get_param($_POST, 'heading_bg_colour');
    $heading_fg_colour = nbf_common::get_param($_POST, 'heading_fg_colour');
    if (is_array($default_user_groups))
    {
        $default_user_groups = implode(",", $default_user_groups);
    }
    $precision_decimal = intval(nbf_common::get_param($_POST,'precision_decimal'));
    $precision_quantity = intval(nbf_common::get_param($_POST,'precision_quantity'));
    $precision_tax_rate = intval(nbf_common::get_param($_POST,'precision_tax_rate'));
    $precision_currency = intval(nbf_common::get_param($_POST,'precision_currency'));
    $precision_currency_line_total = intval(nbf_common::get_param($_POST,'precision_currency_line_total'));
    $precision_currency_grand_total = intval(nbf_common::get_param($_POST,'precision_currency_grand_total'));
    $thousands_separator = nbf_common::get_param($_POST,'thousands_separator');
    $decimal_separator = nbf_common::get_param($_POST,'decimal_separator');
    $currency_format = nbf_common::get_param($_POST,'currency_format');
    $negative_in_brackets = intval(nbf_common::get_param($_POST, 'negative_in_brackets'));
    $use_legacy_document_editor = intval(nbf_common::get_param($_POST, 'use_legacy_document_editor'));
    $edit_products_in_documents = intval(nbf_common::get_param($_POST, 'edit_products_in_documents'));
    $auto_check_eu_vat_rates = intval(nbf_common::get_param($_POST, 'auto_check_eu_vat_rates'));
    $api_url_eu_vat_rates = nbf_common::get_param($_POST, 'api_url_eu_vat_rates');
    $geo_ip_lookup = intval(nbf_common::get_param($_POST, 'geo_ip_lookup'));
    $api_url_geo_ip = nbf_common::get_param($_POST, 'api_url_geo_ip');
    $geo_ip_fail_on_mismatch = intval(nbf_common::get_param($_POST, 'geo_ip_fail_on_mismatch'));
    $disable_email = intval(nbf_common::get_param($_POST, 'disable_email'));
    $timezone = nbf_common::get_param($_POST, 'timezone');
    $default_electronic = intval(nbf_common::get_param($_POST, 'default_electronic'));
    $never_hide_quantity = intval(nbf_common::get_param($_POST, 'never_hide_quantity'));

	$sql = "UPDATE #__nbill_configuration SET
					error_email = '$error_email',
                    date_format = '$date_format',
                    `locale` = '$locale_string',
					select_users_from_list = '$show_user_list',
					version_auto_check = '$version_auto_check',
					auto_update = '$auto_update',
					default_start_date = '$default_start_date',
                    switch_to_ssl = '$switch_to_ssl',
                    all_pages_ssl = '$all_pages_ssl', ";
    if (!nbf_cms::$interop->demo_mode) {
        $sql .= "default_user_groups = '$default_user_groups', ";
    }
    $sql .= "email_invoice_option = '$email_invoice_option',
                    title_colour = '$title_colour',
                    heading_bg_colour = '$heading_bg_colour',
                    heading_fg_colour = '$heading_fg_colour',
                    default_itemid = '$default_itemid',
                    redirect_to_itemid = '$redirect_to_itemid',
                    precision_decimal = '$precision_decimal',
                    precision_quantity = '$precision_quantity',
                    precision_tax_rate = '$precision_tax_rate',
                    precision_currency = '$precision_currency',
                    precision_currency_line_total = '$precision_currency_line_total',
                    precision_currency_grand_total = '$precision_currency_grand_total',
                    thousands_separator = '$thousands_separator',
                    decimal_separator = '$decimal_separator',
                    currency_format = '$currency_format',
                    negative_in_brackets = $negative_in_brackets,
                    use_legacy_document_editor = '$use_legacy_document_editor',
                    edit_products_in_documents = '$edit_products_in_documents',
                    auto_check_eu_vat_rates = '$auto_check_eu_vat_rates', ";
    if (!nbf_cms::$interop->demo_mode) {
        $sql .= "api_url_eu_vat_rates = '$api_url_eu_vat_rates', ";
    }
    
    $sql .= "disable_email = " . $disable_email . ",
                    timezone = '" . $timezone . "',
                    default_electronic = " . $default_electronic . ",
                    never_hide_quantity = " . $never_hide_quantity . "
                    WHERE id = 1";
	$nb_database->setQuery($sql);
	$nb_database->query();

    //If default invoice notification preferences have changed, update all clients (except those overridden)
    $old_email_option = nbf_common::get_param($_POST, 'old_email_invoice_option');
    $new_email_option = nbf_common::get_param($_POST, 'email_invoice_option');
    if ($old_email_option != $new_email_option)
    {
        $sql = "UPDATE #__nbill_entity_contact SET email_invoice_option = '$new_email_option'
                WHERE email_invoice_option = '$old_email_option'";
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

    

    //Save FTP connection details
    nbf_cms::$interop->set_ftp_details(nbf_common::get_param($_REQUEST, 'ftp_address'), nbf_common::get_param($_REQUEST, 'ftp_port'),
                                        nbf_common::get_param($_REQUEST, 'ftp_username'), nbf_common::get_param($_REQUEST, 'ftp_password'),
                                        nbf_common::get_param($_REQUEST, 'ftp_root'));

	nbf_common::fire_event("record_updated", array("type"=>"configuration", "id"=>1));

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'save', get_defined_vars());
}

