<?php
/**
* Server-side processing for AJAX functions
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

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

$ajax_task = nbf_common::get_param($_REQUEST, 'task');
switch ($ajax_task)
{
	case "get_contacts":
    case "check_email":
        if (defined('NBILL_ADMIN'))
        {
	        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.entities.php");
	        call_user_func($ajax_task);
        }
		break;
    case "get_products":
	case "show_field_options":
	case "refresh_control":
    case "render_editor":
    case "submit_form_data":
    case "get_extended_properties":
        if (defined('NBILL_ADMIN'))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.form_editor.php");
		    call_user_func($ajax_task);
        }
		break;
    case "send_document_email":
        if (defined('NBILL_ADMIN'))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.email.php");
            call_user_func($ajax_task);
        }
        break;
    case "get_invoice_income_data":
    case "document_get_products":
    case "check_product_update":
    case "get_default_tax_info":
    case "client_changed":
        if (defined('NBILL_ADMIN'))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.invoice.php");
            call_user_func($ajax_task);
        }
        break;
    case "sync_vendor":
        if (defined('NBILL_ADMIN'))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.vendor.php");
            call_user_func($ajax_task);
        }
        break;
    case "migrate_data":
        if (defined('NBILL_ADMIN'))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.global.php");
            call_user_func($ajax_task);
        }
        break;
    case "remove_line_item":
    case "insert_page_break":
    case "remove_section_break":
    case "insert_section_break_popup":
    case "insert_section_break":
    case "edit_section_break_popup":
    case "save_section_break":
    case "move_line_item_up":
    case "move_line_item_down":
    case "edit_item_popup":
    case "save_item":
    case "get_sku_list":
    case "get_product":
    case "refresh":
        if (defined('NBILL_ADMIN'))
        {
            $controller = nBillLineItemFactory::createController(nbf_common::get_param($_REQUEST, 'document_type'));
            $method = 'ajax' . str_replace(' ', '', ucwords(str_replace('_', ' ', $ajax_task)));
            if (method_exists($controller, $method)) {
                $controller->$method();
            }
        }
        break;
    default:
        //Hand over to an extension if required
        $extension_name = substr($ajax_task, 0, nbf_common::nb_strpos($ajax_task, "."));
        $extension_task = substr($ajax_task, nbf_common::nb_strlen($extension_name) + 1);
        $extension_name = preg_replace("/[^A-Za-z0-9\_]/", "", $extension_name); //Sanitise
        if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.$extension_name.php"))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.$extension_name.php");
            call_user_func($extension_task);
        }
        break;
}
exit; //Don't hand control back to the template