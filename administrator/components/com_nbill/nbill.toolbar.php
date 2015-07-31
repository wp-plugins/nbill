<?php
/**
* Decide what to output on the main nBill administrator toolbar
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

require_once(dirname(__FILE__) . "/admin.init.php");
$task = nbf_common::get_param($_REQUEST, 'task');

// Include toolbar's HTML class
require_once(nbf_cms::$interop->nbill_admin_base_path . "/nbill.toolbar.html.php");

if (!file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/" . nbf_common::get_param($_REQUEST, 'action') . ".php"))
{
    return;
}

switch (nbf_common::get_param($_REQUEST, 'action'))
{
    case "supporting_docs":
        if (!nbf_cms::$interop->demo_mode)
        {
            nbill_TOOLBAR::supporting_docsButtons();
        }
        break;
    case "registration":
        if (!nbf_cms::$interop->demo_mode)
        {
            nbill_TOOLBAR::registrationButtons();
        }
        break;
	case "configuration":
	case "display":
        switch ($task)
        {
            case "change_order_tx_id":
            case "change_order_tx_submit":
            case "save":
                break;
            default:
		        nbill_TOOLBAR::editButtons();
                break;
        }
		break;
    case "favourites":
        nbill_TOOLBAR::favouriteButtons();
        break;
	case "orderforms":
    case "quote_request":
        switch ($task)
        {
            case "edit":
            case 'apply':
                nbill_TOOLBAR::editButtons(true, false);
                break;
            case 'new':
                nbill_TOOLBAR::editButtons();
                break;
            default:
                nbill_TOOLBAR::defaultButtons();
                break;
        }
        break;
    case "vendors":
	case "vat":
    case "contacts":
	case "clients":
    case "potential_clients":
	case "shipping":
	case "ledger":
	case "currency":
	case "categories":
	case "products":
    case "suppliers":
	case "discounts":
    case "fees":
	case "reminders":
    case "reconcile":
    case "profile_fields":
    case "payment_plans":
		switch ($task)
		{
			case 'new':
			case 'edit':
			case 'apply':
			case 'add_ledger_item':
			case 'remove_ledger_item':
			case 'auto_add_ledger_items':
			case 'cat_changed':
            case 'disqual_cat_changed':
			case 'reorder':
			case 'test_connection':
            case 'detach_file_edit':
            case 'delete_file_edit':
				nbill_TOOLBAR::editButtons(nbf_common::get_param($_REQUEST, 'action') == 'products');
				break;
            case 'show_affected_orders':
                break;
			default:
                if (substr($task, 0, 12) == "synchronise-")
				{
					nbill_TOOLBAR::editButtons();
				}
                else if (substr($task, 0, 7) == "unlock-")
                {
                    nbill_TOOLBAR::editButtons();
                }
				else
				{
                    switch (nbf_common::get_param($_REQUEST, 'action'))
                    {
                        case "profile_fields":
                            nbill_TOOLBAR::profile_fieldButtons();
                            break;
                        case "potential_clients":
                            nbill_TOOLBAR::potential_clientButtons();
                            break;
                        default:
					        nbill_TOOLBAR::defaultButtons(nbf_common::get_param($_REQUEST, 'action') == 'clients' || nbf_common::get_param($_REQUEST, 'action') == 'suppliers');
                    }
				}
				break;
		}
		break;
	case "gateway":
        if (!nbf_cms::$interop->demo_mode)
        {
		    switch ($task)
		    {
			    case 'edit':
                case 'apply':
				    nbill_TOOLBAR::editButtons();
				    break;
			    case 'new':
                    nbf_common::load_language("extensions");
				    nbill_TOOLBAR::gatewayInstallButtons();
				    break;
			    case 'functions':
				    break;
			    default:
				    nbill_TOOLBAR::gatewayButtons();
				    break;
		    }
        }
		break;
	case "orders":
		switch ($task)
		{
			case 'new':
			case 'edit':
			case 'apply':
			case 'edit_cat_change':
			case 'product_updated':
			case 'edit_currency_change':
			case 'change_client':
            case 'detach_file_edit':
            case 'delete_file_edit':
				nbill_TOOLBAR::editButtons();
				break;
            default:
				nbill_TOOLBAR::orderButtons();
				break;
		}
		break;
	case "invoices":
	case "credits":
    case "quotes":
		switch ($task)
		{
            case 'product_list':
                //No buttons, thanks.
                break;
			case 'new':
			case 'edit':
			case 'apply':
			case 'remove_item':
			case 'add_item':
			case 'client_changed':
			case 'lookup_sku':
            case 'detach_file_edit':
            case 'delete_file_edit':
            case 'move_up':
            case 'move_down':
                if (nbf_common::get_param($_REQUEST, 'action') == "quotes")
                {
                    nbill_TOOLBAR::quoteButtons(intval(nbf_common::get_param($_REQUEST, 'cid')) > 0);
                }
                else
                {
				    nbill_TOOLBAR::editButtons(intval(nbf_common::get_param($_REQUEST, 'cid')) > 0);
                }
				break;
			default:
				nbf_common::load_language("invoices");
                nbill_TOOLBAR::invoiceButtons();
				break;
		}
		break;
	case "income":
	case "expenditure":
		if (nbf_common::get_param($_REQUEST, 'action') == "income")
		{
			nbf_common::load_language("income");
		}
		else
		{
			nbf_common::load_language("expenditure");
		}
		switch ($task)
		{
			case 'new':
			case 'edit':
			case 'apply':
			case 'unlock':
			case 'add_ledger_item':
			case 'remove_ledger_item':
			case 'auto_add_ledger_items':
            case 'detach_file_edit':
            case 'delete_file_edit':
                nbill_TOOLBAR::transactionEditButtons();
				break;
            case 'multi_invoice':
                nbill_TOOLBAR::incomeMultiButtons();
                break;
			default:
				//Check whether to offer receipt no generation
				$nb_database = nbf_cms::$interop->database;
				$sql = "SELECT suppress_receipt_nos, suppress_payment_nos, suppress_generation_buttons FROM #__nbill_vendor WHERE id = " . intval(nbf_common::get_param($_POST, 'vendor_filter'));
				$nb_database->setQuery($sql);
				$nb_database->loadObject($suppression);
				//Check whether using a master database
				$sql = "SELECT use_master_db FROM #__nbill_vendor WHERE id = " . intval(nbf_common::get_param($_POST, 'vendor_filter'));
				$nb_database->setQuery($sql);
				$use_master_db = $nb_database->loadResult();
				if (nbf_common::get_param($_REQUEST, 'action') == "income")
				{
					nbill_TOOLBAR::incomeButtons(@$suppression->suppress_receipt_nos && !@$suppression->suppress_generation_buttons, $use_master_db);
				}
				else
				{
					nbill_TOOLBAR::expenditureButtons(@$suppression->suppress_payment_nos && !@$suppression->suppress_generation_buttons, $use_master_db);
				}
				break;
		}
		break;
	case "pending":
		nbf_common::load_language("pending");
		switch ($task)
		{
			case 'show':
				nbill_TOOLBAR::pendingShowButtons();
				break;
			default:
				nbill_TOOLBAR::pendingButtons();
				break;
		}
	case "io":
		switch ($task)
		{
			case 'import_select_clients_users':
			case 'import_selected_users':
				nbf_common::load_language("io");
				nbill_TOOLBAR::importUsersButtons();
				break;
		}
		break;
	case "extensions":
		if (isset($_FILES['zipfile']) && $_FILES['zipfile']['size'] > 0)
		{
			//Extension uploaded - don't show toolbar
		}
		else
		{
			nbill_TOOLBAR::extensionButtons();
		}
		break;
    case "email_log":
        nbill_TOOLBAR::emailLogButtons();
        break;
    case "translation":
        switch ($task)
        {
            case 'edit_table':
                nbill_TOOLBAR::editTranslationTableButtons();
                break;
            case 'edit_row':
            case 'apply':
                nbill_TOOLBAR::editButtons();
                break;
        }
        break;
	default:
		//No buttons - home page just has links to everything else
		break;
}