<?php
/**
* Entry point into front end features of the component
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Joomla 3.0 sometimes adds parameters from the menu item's URL without asking
if (empty($_POST) || count($_POST) < 2) {
    if (isset($_REQUEST['action']) && !isset($_GET['action'])) {
        unset($_REQUEST['action']);
    }
    if (isset($_REQUEST['task']) && !isset($_GET['task'])) {
        unset($_REQUEST['task']);
    }
    if (isset($_REQUEST['cid']) && !isset($_GET['cid'])) {
        unset($_REQUEST['cid']);
    }
    if (isset($_REQUEST['id']) && !isset($_GET['id'])) {
        unset($_REQUEST['id']);
    }
}

//Load framework
require_once(dirname(__FILE__) . "/framework/locator.class.php");

require_once(nbill_framework_locator::find_framework() . "/bootstrap.php");
require_once(nbill_framework_locator::find_framework() . "/classes/nbill.frontend.class.php");

//Check whether to trace this request and spit out a log
if (@file_exists(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.config.php")) {
    @include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.config.php");
    if (@class_exists("nbf_config")) {
        if (property_exists("nbf_config", "trace_debug_frontend")) {
            nbf_globals::$trace_debug = @nbf_config::$trace_debug_frontend;
        }
    }
}
nbf_common::debug_trace("POST: " . print_r($_POST, true) . "\nGET: " . print_r($_GET, true) . "\nCOOKIE: " . print_r($_COOKIE, true));

//Under some circumstances, ?theurl=/component/nbill seems to be added to the action parameter - we will strip that off if present
$crap = strpos(nbf_common::get_param($_REQUEST, 'action'), '?theurl');
if ($crap !== false) {
    $_REQUEST['action'] = substr(nbf_common::get_param($_REQUEST, 'action'), 0, $crap);
}

//Sanitise input parameters
nb_sanitise('Itemid');
nb_sanitise('cid', false, true);
nb_sanitise('id', false, true);
nb_sanitise('document_id', false, true);
nb_sanitise('document_ids', false, true);
nb_sanitise('invoice_id', false, true);
nb_sanitise('invoice_ids', false, true);
nb_sanitise('order_id', false, true);
nb_sanitise('order_ids', false, true);
nb_sanitise('g_tx_id');

$nb_database = nbf_cms::$interop->database;

if (!nbf_common::get_param($_REQUEST, 'nbill_admin_via_fe')) {
    //If we don't have an Itemid, see if we can load a default one
    $current_itemid = intval(nbf_common::get_param($_REQUEST, 'Itemid'));
    if (!$current_itemid) {
        $current_itemid = intval(nbf_common::get_param($GLOBALS, 'Itemid'));
    }
    if (!$current_itemid) {
        $itemid_config = null;
        $sql = "SELECT default_itemid, redirect_to_itemid FROM #__nbill_configuration WHERE id = 1";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($itemid_config);
        if (intval($itemid_config->default_itemid)) {
            //Redirect if required
            if ($itemid_config->redirect_to_itemid) {
                $current_url = nbf_common::get_requested_page(true);
                if (count($_POST) == 0 && strpos($current_url, "&Itemid=" . intval($itemid_config->default_itemid)) === false) {
                    nbf_common::redirect($current_url . "&Itemid=" . intval($itemid_config->default_itemid));
                }
            }

            //Otherwise just set the Itemid so it gets preserved in postbacks
            $_REQUEST['Itemid'] = intval($itemid_config->default_itemid);
            $GLOBALS['Itemid'] = intval($itemid_config->default_itemid);
        }
    }
}



//Load language file
nbf_common::load_language("frontend");

//Load error handler
require_once(nbf_cms::$interop->nbill_fe_base_path . "/error.handler.php");
ob_start("fatal_error_handler");

nbf_globals::$message = nbf_common::get_param($_REQUEST, 'message', '', true);
nbf_globals::$message = str_replace('\n', "\n", nbf_globals::$message);
nbf_globals::$message = str_replace("\n", '<br />', nbf_globals::$message); //Replace hard and soft line breaks with html equivalent
$cid = intval(nbf_common::get_param( $_REQUEST, 'cid', '' ));
$id = intval(nbf_common::get_param( $_REQUEST, 'id', ''));

if (nbf_common::get_param($_REQUEST, 'action') == "show_image") {
    nbf_frontend::show_image(urldecode(nbf_common::get_param($_REQUEST, 'file_name')));
}
if (nbf_common::get_param($_REQUEST, 'action') == "fetch_file") {
    nbf_frontend::fetch_file(urldecode(nbf_common::get_param($_REQUEST, 'file_name')));
}
 else if (nbf_common::get_param($_REQUEST, 'action') == "gateway" || nbf_common::get_param($_REQUEST, 'action') == 'gatewayfunctions') {
	//Incoming payment confirmation
	nbf_common::load_language("gateway");

	$gateway = nbf_common::get_param($_REQUEST, 'gateway', '');
    if (strpos($gateway, '..') !== false || strpos($gateway, '/') !== false || strpos($gateway, '\\') !== false) {
        $gateway = '';
    }
    if (nbf_common::get_param($_REQUEST, 'action') == "gateway") {
        $gateway_file_name = nbf_cms::$interop->nbill_fe_base_path . "/gateway/$gateway/$gateway.php";
    }
    else {
        $gateway_file_name = nbf_cms::$interop->nbill_fe_base_path . "/gateway/$gateway/$gateway.functions.php";
    }
    $manifest_file_name = nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$gateway/$gateway.manifest.php";
    if (file_exists($gateway_file_name)) {
        //Load manifest file to work out what features are available
        $manifest = null;
        if (file_exists($manifest_file_name)) {
            include_once($manifest_file_name);
            $manifest_class_name = "nbill_" . $gateway . "_manifest";
            if (class_exists($manifest_class_name)) {
                $manifest = new $manifest_class_name();
            }
        }
        if (!$manifest) {
            //We are using a legacy gateway
            global $pending_order_id;
            global $document_ids;
            global $net_amount;
            global $tax_amount;
            global $shipping_amount;
            global $shipping_tax_amount;
            global $tax_rates;
            global $tax_amounts;
            global $ledger_codes;
            global $ledger_amounts;
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.backward.compatibility.php");
            nbf_common::debug_trace("Using legacy gateway (no manifest file found)");
        }

        $task = nbf_common::get_param($_REQUEST, 'task');
	    nbf_common::fire_event("gateway_callback", array("gateway"=>$gateway));
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");
        nbf_gateway_txn::$pending_order_id = "";
        nbf_gateway_txn::$document_ids = array();
        nbf_gateway_txn::$net_amount = 0;
        nbf_gateway_txn::$tax_amount = 0;
        nbf_gateway_txn::$shipping_amount = 0;
        nbf_gateway_txn::$shipping_tax_amount = 0;
        $nb_database = nbf_cms::$interop->database;
        
        nbf_common::debug_trace("Including " . $gateway_file_name);
	    include_once($gateway_file_name);
	    return;
    } else {
        nbf_common::debug_trace("$gateway_file_name does not exist!");
    }
}

if (nbf_common::get_param($_REQUEST, 'action') == 'ajax') {
    nbf_common::debug_trace("Including ajax server");
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.server.php");
    return;
}

//Switch to SSL if required
if (!nbf_common::get_param($_REQUEST, 'no_ssl') && !((@$_SERVER['HTTPS'] && nbf_common::nb_strtolower(@$_SERVER['HTTPS']) != 'off') || @$_SERVER['SERVER_PORT'] == nbf_config::$ssl_port)) {
    $nb_database = nbf_cms::$interop->database;
    $sql = "SELECT all_pages_ssl FROM #__nbill_configuration WHERE id = 1";
    $nb_database->setQuery($sql);
    $switch_to_ssl = $nb_database->loadResult();
    if ($switch_to_ssl){
        //Redirect
        nbf_common::debug_trace("Redirecting to SSL");
        nbf_common::redirect(str_replace("http://", "https://", nbf_common::get_requested_page(true)));
        exit;
    }
}

//Load HTML file and stylesheet
require_once(nbf_cms::$interop->nbill_fe_base_path . "/nbill.html.php");
nBillFrontEnd::load_stylesheet();

if (strtolower(nbf_version::$suffix) != 'lite' && nbf_common::get_param($_REQUEST, 'action') == "subexpiry") {
    nbf_common::debug_trace("Subscription expiry");
	$exp_level = nbf_common::get_param($_REQUEST, 'exp_level');
	$reason = nbf_common::get_param($_REQUEST, 'reason');
	$referrer = nbf_common::get_param($_REQUEST, 'referrer');

	$expiry_action = "";
	switch ($exp_level) {
		case "-1":
			$expiry_action = NBILL_SUB_EXPIRY_DELETED;
			break;
		case "-2":
			$expiry_action = NBILL_SUB_EXPIRY_BLOCKED;
			break;
		default:
			$expiry_level = nbf_cms::$interop->get_user_group_name($exp_level);
            if (!$expiry_level) {
				$expiry_level = NBILL_UNKNOWN;
			}
			$expiry_action = sprintf(NBILL_SUB_EXPIRY_DOWNGRADED, $expiry_level);
			break;
	}

	$reason_message = "";
	switch ($reason) {
		case "cancelled":
			$reason_message = NBILL_SUB_EXPIRY_REASON_CANCELLED;
			break;
		case "deleted":
			$reason_message = NBILL_SUB_EXPIRY_REASON_DELETED;
			break;
		case "expired":
			$reason_message = NBILL_SUB_EXPIRY_REASON_EXPIRED;
			break;
		case "not_renewed":
			$reason_message = NBILL_SUB_EXPIRY_REASON_NOT_RENEWED;
			break;
		default:
			//Unknown
			$reason = NBILL_SUB_EXPIRY_REASON_UNKNOWN;
			break;
	}

	nbf_globals::$message = sprintf(NBILL_SUB_EXPIRY_MESSAGE, $expiry_action, $reason_message);
	nBillFrontEnd::show_expiry_message($referrer);
} else {
	if (nbf_cms::$interop->user->id) {
        nbf_common::debug_trace("User " . nbf_cms::$interop->user->id . " is logged in");
		//Make this configurable - text/whether to display or not
	    if (nbill_show_header()) {
			nBillFrontEnd::show_header();
	    }
	}

	if (!nbf_cms::$interop->user->id) {
        nbf_common::debug_trace("User not logged in");
		switch (nbf_common::get_param($_REQUEST, 'action')) {
			case "login":
				nbill_do_login();
				break;
			case "profile":
            case "download":
            case "myaccount":
                nbill_show_login_box();
                break;
            case "invoices":
				if (nbf_common::get_param($_REQUEST, 'task') != "pay" || nbf_frontend::get_display_option("login_to_pay_invoice")) {
					nbill_show_login_box();
					break;
				}
                //Fall through
			
			default:
                //If one or more forms are available, show them, otherwise, show login box
                $show_forms = true;
                
                if ($show_forms){
                    if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/html/" . nbf_common::get_param($_REQUEST, 'action') . ".html.php")) {
                        include_once(nbf_cms::$interop->nbill_fe_base_path . "/html/" . nbf_common::get_param($_REQUEST, 'action') . ".html.php");
                    } else {
                        
                    }

                    if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/proc/" . nbf_common::get_param($_REQUEST, 'action') . ".php")) {
                        nbf_common::debug_trace("Loading action file: " . nbf_common::get_param($_REQUEST, 'action'));
                        include_once(nbf_cms::$interop->nbill_fe_base_path . "/proc/" . nbf_common::get_param($_REQUEST, 'action') . ".php");
                        break;
                    } else {
                        
                    }
                }

                nbf_common::debug_trace("Not showing forms and not logged in, so displaying nBill login box");
                nbill_show_login_box();
            	break;
		}
	} else{
	    $task = nbf_common::get_param($_REQUEST, 'task');
        switch (nbf_common::get_param($_REQUEST, 'action')) {
			
			default:
				nbf_common::load_language("frontend");
				if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/html/" . nbf_common::get_param($_REQUEST, 'action') . ".html.php")) {
					include_once(nbf_cms::$interop->nbill_fe_base_path . "/html/" . nbf_common::get_param($_REQUEST, 'action') . ".html.php");
				} else {
					include_once(nbf_cms::$interop->nbill_fe_base_path . "/html/main.html.php");
				}
				if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/proc/" . nbf_common::get_param($_REQUEST, 'action') . ".php")) {
                    nbf_common::debug_trace("Loading action file: " . nbf_common::get_param($_REQUEST, 'action'));
					include_once(nbf_cms::$interop->nbill_fe_base_path . "/proc/" . nbf_common::get_param($_REQUEST, 'action') . ".php");
				} else {
                    nbf_common::debug_trace("Loading default action file: main");
					include_once(nbf_cms::$interop->nbill_fe_base_path . "/proc/main.php");
				}
				break;
		}
	}
}

function nbill_show_header()
{
    //Check display options to see whether or not we should display the My Account heading
    $nb_database = nbf_cms::$interop->database;
    $display = nbf_frontend::get_display_option("my_account");

    if ($display === null || $display === false || $display == 1) {
        return true;
    } else {
        return false;
    }
}



function nbill_show_login_box($show_title = true, $show_intro = true)
{
	if (!nbf_cms::$interop->user->id) {
		//User has tried to access something that they have to be logged in for - show login box and proceed
		$request_values = array_filter($_GET) + array_filter($_POST);
        if (array_key_exists('failure_message', $request_values)) {
            unset($request_values['failure_message']);
        }
        $request_values = base64_encode(serialize($request_values));
		@nBillFrontEnd::display_login_box($request_values, $show_title, $show_intro);
	}
}

function nbill_do_login()
{
	if (nbf_common::get_param($_POST, 'NBILL_login_submit_generic')) {
		$full_screen = nbf_common::nb_strpos(nbf_common::get_requested_page(true), html_entity_decode(nbf_cms::$interop->site_popup_page_prefix, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding)) !== false || nbf_common::nb_strpos(nbf_common::get_requested_page(true), nbf_cms::$interop->process_url(html_entity_decode(nbf_cms::$interop->site_popup_page_prefix, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding))) !== false;
        if ($full_screen) {
            $redirect_url = nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_popup_page_prefix;
        } else {
            $redirect_url = nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix;
        }
        $request_values = unserialize(base64_decode(nbf_common::get_param($_POST, 'nb_request_values', null, true, false, true)));
        if ($request_values) {
		    foreach ($request_values as $request_key=>$request_value) {
			    if ($request_key != "option" && $request_key != "nbverifier") {
				    $redirect_url .= "&" . $request_key . "=" . urlencode($request_value);
			    }
		    }
        }
        nbf_cms::$interop->login(nbf_common::get_param($_POST,'NBILL_LOGIN_username_generic'), nbf_common::get_param($_POST,'NBILL_LOGIN_password_generic'), false, $redirect_url);
        if (!nbf_cms::$interop->user->id) {
            $redirect_url .= "&failure_message=" . NBILL_LOGIN_FAILED;
            nbf_common::redirect($redirect_url);
        }
		exit;
	}
}

function nb_sanitise($variable, $int_only = true, $int_array = false)
{
    //Variable variables are not available with superglobals, so we have to do each individually
    if ($int_only) {
        if (isset($_POST[$variable])){$_POST[$variable] = intval($_POST[$variable]);}
        if (isset($_GET[$variable])){$_GET[$variable] = intval($_GET[$variable]);}
        if (isset($_REQUEST[$variable])){$_REQUEST[$variable] = intval($_REQUEST[$variable]);}
        if (isset($GLOBALS[$variable])){$GLOBALS[$variable] = intval($GLOBALS[$variable]);}
    }

    if ($int_array) {
        if (isset($_POST[$variable])) {
            $this_array = is_array($_POST[$variable]) ? $_POST[$variable] : explode(",", $_POST[$variable]);
            foreach ($this_array as &$value) {
                $value = intval($value);
            }
            $_POST[$variable] = is_array($_POST[$variable]) ? $this_array : implode(",", $this_array);
        }
        if (isset($_GET[$variable])) {
            $this_array = is_array($_GET[$variable]) ? $_GET[$variable] : explode(",", $_GET[$variable]);
            foreach ($this_array as &$value) {
                $value = intval($value);
            }
            $_GET[$variable] = is_array($_GET[$variable]) ? $this_array : implode(",", $this_array);
        }
        if (isset($_REQUEST[$variable])) {
            $this_array = is_array($_REQUEST[$variable]) ? $_REQUEST[$variable] : explode(",", $_REQUEST[$variable]);
            foreach ($this_array as &$value) {
                $value = intval($value);
            }
            $_REQUEST[$variable] = is_array($_REQUEST[$variable]) ? $this_array : implode(",", $this_array);
        }
        if (isset($GLOBALS[$variable])) {
            $this_array = is_array($GLOBALS[$variable]) ? $GLOBALS[$variable] : explode(",", $GLOBALS[$variable]);
            foreach ($this_array as &$value){
                $value = intval($value);
            }
            $GLOBALS[$variable] = is_array($GLOBALS[$variable]) ? $this_array : implode(",", $this_array);
        }
    }
}
ob_end_flush();