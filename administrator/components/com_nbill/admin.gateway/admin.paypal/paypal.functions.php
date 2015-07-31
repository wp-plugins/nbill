<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\CancelPreapprovalRequest;
use PayPal\Types\Common\RequestEnvelope;

/**
* This gateway was developed by and is copyright of Netshine Software Limited.
* Sections of code may be copyrighted to other parties (eg. where sample code was used
* from the Paypal documentation). All parts (of this gateway only) written by
* Netshine Software Limited are licensed for use in any way you wish, as long
* as this copyright message remains intact, and without any guarantee of any sort -
* use at your own risk.
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal." . nbf_cms::$interop->language . ".php")) {
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal." . nbf_cms::$interop->language . ".php");
} else if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal.en-GB.php")) {
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal.en-GB.php");
}

$selected_tab = nbf_common::get_param($_REQUEST, 'sub_task');
$selected_tab = $selected_tab ? $selected_tab : 'invoices';

if (!nbf_globals::$popup) {
    //Locate ID number of gateway
    $nb_database = nbf_cms::$interop->database;
    $sql = "SELECT id FROM #__nbill_payment_gateway WHERE gateway_id = 'paypal' AND g_key = 'gateway_description'";
    $nb_database->setQuery($sql);
    $gateway_id = intval($nb_database->loadResult());
    nbillPaypalFunctions::showIntro($selected_tab, $gateway_id);
}

switch ($selected_tab) {
    case "clients":
        switch (nbf_common::get_param($_REQUEST, 'process')) {
            case 'delete':
                delete_preauth();
                show_preauth_resources();
                break;
            case 'new':
                show_clients();
                break;
            case 'invite':
                invite_client();
                break;
            case 'resend_invite':
                invite_client(intval(nbf_common::get_param($_REQUEST, 'id')));
                break;
            case 'get_message':
                default_invitation_message();
                break;
            case 'abort_invite':
                show_clients();
                break;
            case 'send_invitation':
                send_invitation();
                break;
            case 'back':
            default:
                show_preauth_resources();
                break;
        }
        break;
    default:
        switch (nbf_common::get_param($_REQUEST, 'process')) {
            case 'collect_payment':
                show_payment_summary();
                break;
            default:
                show_payable_invoices();
                break;
        }
        break;
}

function show_preauth_resources()
{
    $nb_database = nbf_cms::$interop->database;

    nbf_common::load_language('contacts');
    nbf_common::load_language('clients');
    $nb_database = nbf_cms::$interop->database;

    //Get user table and column names
    $user_table = nbf_cms::$interop->cms_database_enum->table_user;
    $user_username_col = nbf_cms::$interop->cms_database_enum->column_user_username;
    $user_id_col = nbf_cms::$interop->cms_database_enum->column_user_id;

    //Get client name filter
    $client_filter = "%" . nbf_common::get_param($_REQUEST,'client_search') . "%";
    $client_user_filter = "%" . nbf_common::get_param($_REQUEST,'client_user_search') . "%";
    $client_email_filter = "%" . nbf_common::get_param($_REQUEST,'client_email_search') . "%";

    //Build criteria
    $whereclause = " WHERE #__nbill_paypal_preapp_resources.entity_id > 0 AND #__nbill_paypal_preapp_resources.type = 'preapp'";
    if (nbf_common::nb_strlen($client_filter) > 2) {
        $whereclause .= " AND (#__nbill_entity.company_name LIKE '$client_filter' OR CONCAT_WS(' ', #__nbill_entity.company_name, contact_2.first_name, contact_2.last_name) LIKE '$client_filter')";
    }
    if (nbf_common::nb_strlen($client_user_filter) > 2) {
        $whereclause .= " AND $user_table.$user_username_col LIKE '$client_user_filter'";
    }
    if (nbf_common::nb_strlen($client_email_filter) > 2) {
        $whereclause .= " AND (contact_2.email_address LIKE '$client_email_filter' OR contact_2.email_address_2 LIKE '$client_email_filter')";
    }
    $whereclause .= " AND #__nbill_entity.is_client = 1";

    //Count the total number of records
    $query = "SELECT count(*) as ordering
                    FROM (SELECT #__nbill_paypal_preapp_resources.id FROM #__nbill_paypal_preapp_resources
                    INNER JOIN #__nbill_entity ON #__nbill_paypal_preapp_resources.entity_id = #__nbill_entity.id
                    LEFT JOIN (#__nbill_entity_contact INNER JOIN #__nbill_contact AS contact_2 ON #__nbill_entity_contact.contact_id = contact_2.id) ON #__nbill_entity.id = #__nbill_entity_contact.entity_id
                    LEFT JOIN `$user_table` ON contact_2.user_id = `$user_table`.`$user_id_col` ";
    $query .= " LEFT JOIN #__nbill_account_expiry ON (contact_2.user_id = #__nbill_account_expiry.user_id AND `$user_table`.`$user_id_col` = contact_2.user_id)";
    $query .= $whereclause;
    $query .= " GROUP BY #__nbill_paypal_preapp_resources.id) sub";
    $nb_database->setQuery($query);
    $total = $nb_database->loadResult();

    //Add page navigation
    $pagination = new nbf_pagination("pp_client", $total);

    //Load the records
    $sql = "SELECT #__nbill_paypal_preapp_resources.*, #__nbill_entity.id AS entity_id, #__nbill_entity.company_name, contact_2.id AS contact_id, contact_2.user_id,
                    CONCAT_WS(' ', contact_2.first_name, contact_2.last_name) AS `name`, contact_2.email_address, contact_2.telephone, `$user_table`.`$user_username_col` AS username,
                    #__nbill_account_expiry.user_id AS subscriber
                    FROM #__nbill_paypal_preapp_resources
                    INNER JOIN #__nbill_entity ON #__nbill_paypal_preapp_resources.entity_id = #__nbill_entity.id
                    LEFT JOIN (#__nbill_entity_contact INNER JOIN #__nbill_contact AS contact_2 ON #__nbill_entity_contact.contact_id = contact_2.id) ON #__nbill_entity.id = #__nbill_entity_contact.entity_id
                    LEFT JOIN `$user_table` ON contact_2.user_id = `$user_table`.`$user_id_col` ";
    $sql .= " LEFT JOIN #__nbill_account_expiry ON (contact_2.user_id = #__nbill_account_expiry.user_id AND `$user_table`.`$user_id_col` = contact_2.user_id)";
    $sql .= $whereclause;
    $sql .= " GROUP BY #__nbill_paypal_preapp_resources.id ORDER BY CONCAT(#__nbill_entity.company_name, CONCAT_WS(' ', contact_2.first_name, contact_2.last_name)) LIMIT $pagination->list_offset, $pagination->records_per_page";
    $nb_database->setQuery($sql);
    $rows = $nb_database->loadObjectList();
    if (!$rows) {
        $rows = array();
    }

    nbillPaypalFunctions::showPreAuths($rows, $pagination);
}

function show_clients()
{
    nbf_common::load_language('contacts');
    nbf_common::load_language('clients');
    $nb_database = nbf_cms::$interop->database;

    //Get user table and column names
    $user_table = nbf_cms::$interop->cms_database_enum->table_user;
    $user_username_col = nbf_cms::$interop->cms_database_enum->column_user_username;
    $user_id_col = nbf_cms::$interop->cms_database_enum->column_user_id;

    //Get client name filter
    $client_filter = "%" . nbf_common::get_param($_REQUEST,'client_search') . "%";
    $client_user_filter = "%" . nbf_common::get_param($_REQUEST,'client_user_search') . "%";
    $client_email_filter = "%" . nbf_common::get_param($_REQUEST,'client_email_search') . "%";

    //Count the total number of records
    $query = "SELECT count(*) as ordering FROM (SELECT #__nbill_entity.id FROM #__nbill_entity";
    if (nbf_common::nb_strlen($client_filter) > 2 || nbf_common::nb_strlen($client_user_filter) > 2 || nbf_common::nb_strlen($client_email_filter) > 2 || nbf_common::nb_strlen(nbf_common::get_param($_REQUEST, 'for_contact')) > 0) {
        //Alias contact_2 used so that it will work on BOTH SQL statements
        $query .= " LEFT JOIN (#__nbill_entity_contact INNER JOIN #__nbill_contact AS contact_2 ON #__nbill_entity_contact.contact_id = contact_2.id) ON #__nbill_entity_contact.entity_id = #__nbill_entity.id";
        if (nbf_common::nb_strlen($client_user_filter) > 2) {
            $query .= " LEFT JOIN $user_table ON contact_2.user_id = $user_table.$user_id_col";
        }
    }
    $whereclause = " WHERE 1";
    switch (nbf_common::get_param($_REQUEST, 'pp_client_filter')) {
        case 'unauthorised':
            $query .= " LEFT JOIN #__nbill_paypal_preapp_resources ON #__nbill_entity.id = #__nbill_paypal_preapp_resources.entity_id AND #__nbill_paypal_preapp_resources.type = 'preapp' ";
            $whereclause .= " AND #__nbill_paypal_preapp_resources.id IS NULL ";
            break;
        case 'unaccepted':
            $query .= " LEFT JOIN #__nbill_paypal_preapp_resources ON #__nbill_entity.id = #__nbill_paypal_preapp_resources.entity_id AND #__nbill_paypal_preapp_resources.type = 'preapp'
                        INNER JOIN #__nbill_paypal_preapp_invitations ON #__nbill_entity.id = #__nbill_paypal_preapp_invitations.client_id ";
            $whereclause .= " AND #__nbill_paypal_preapp_resources.id IS NULL ";
            break;
        case 'authorised':
            $query .= " INNER JOIN #__nbill_paypal_preapp_resources ON #__nbill_entity.id = #__nbill_paypal_preapp_resources.entity_id AND #__nbill_paypal_preapp_resources.type = 'preapp' ";
            break;
        case 'all':
            break;
        case 'uninvited':
        default:
            $query .= " LEFT JOIN #__nbill_paypal_preapp_invitations ON #__nbill_entity.id = #__nbill_paypal_preapp_invitations.client_id
                        LEFT JOIN #__nbill_paypal_preapp_resources ON #__nbill_entity.id = #__nbill_paypal_preapp_resources.entity_id AND #__nbill_paypal_preapp_resources.type = 'preapp' ";
            $whereclause .= " AND #__nbill_paypal_preapp_invitations.id IS NULL AND #__nbill_paypal_preapp_resources.id IS NULL ";
            break;
    }
    if (nbf_common::nb_strlen($client_filter) > 2) {
        $whereclause .= " AND (#__nbill_entity.company_name LIKE '$client_filter' OR CONCAT_WS(' ', #__nbill_entity.company_name, contact_2.first_name, contact_2.last_name) LIKE '$client_filter')";
    }
    if (nbf_common::nb_strlen($client_user_filter) > 2) {
        $whereclause .= " AND $user_table.$user_username_col LIKE '$client_user_filter'";
    }
    if (nbf_common::nb_strlen($client_email_filter) > 2) {
        $whereclause .= " AND (contact_2.email_address LIKE '$client_email_filter' OR contact_2.email_address_2 LIKE '$client_email_filter')";
    }
    $whereclause .= " AND #__nbill_entity.is_client = 1";

    $query .= $whereclause . " GROUP BY #__nbill_entity.id) AS entity_list";
    $nb_database->setQuery($query);
    $total = $nb_database->loadResult();

    //Add page navigation
    $pagination = new nbf_pagination("pp_client", $total);

    //Load the records
    $sql = "SELECT #__nbill_entity.id AS entity_id, #__nbill_entity.*, contact_2.id AS contact_id, contact_2.user_id,
                    CONCAT_WS(' ', contact_2.first_name, contact_2.last_name) AS `name`, contact_2.email_address, contact_2.telephone, `$user_table`.`$user_username_col` AS username,
                    MAX(#__nbill_paypal_preapp_invitations.id) AS invitation_id, #__nbill_paypal_preapp_invitations.date_sent AS invitation_sent
                    FROM #__nbill_entity
                    LEFT JOIN (#__nbill_entity_contact INNER JOIN #__nbill_contact AS contact_2 ON #__nbill_entity_contact.contact_id = contact_2.id) ON #__nbill_entity.id = #__nbill_entity_contact.entity_id
                    LEFT JOIN `$user_table` ON contact_2.user_id = `$user_table`.`$user_id_col`
                    LEFT JOIN #__nbill_paypal_preapp_invitations ON #__nbill_entity.id = #__nbill_paypal_preapp_invitations.client_id ";
    switch (nbf_common::get_param($_REQUEST, 'pp_client_filter')) {
        case 'unauthorised':
            $sql .= " LEFT JOIN #__nbill_paypal_preapp_resources ON #__nbill_entity.id = #__nbill_paypal_preapp_resources.entity_id AND #__nbill_paypal_preapp_resources.type = 'preapp' ";
            $whereclause .= " AND #__nbill_paypal_preapp_resources.id IS NULL ";
            break;
        case 'unaccepted':
            $sql .= " LEFT JOIN #__nbill_paypal_preapp_resources ON #__nbill_entity.id = #__nbill_paypal_preapp_resources.entity_id AND #__nbill_paypal_preapp_resources.type = 'preapp' ";
            $whereclause .= " AND #__nbill_paypal_preapp_invitations.id IS NOT NULL AND #__nbill_paypal_preapp_resources.id IS NULL ";
            break;
        case 'authorised':
            $sql .= " INNER JOIN #__nbill_paypal_preapp_resources ON #__nbill_entity.id = #__nbill_paypal_preapp_resources.entity_id AND #__nbill_paypal_preapp_resources.type = 'preapp' ";
            break;
        case 'all':
            break;
        case 'uninvited':
        default:
            $sql .= " LEFT JOIN #__nbill_paypal_preapp_resources ON #__nbill_entity.id = #__nbill_paypal_preapp_resources.entity_id AND #__nbill_paypal_preapp_resources.type = 'preapp' ";
            $whereclause .= " AND #__nbill_paypal_preapp_resources.id IS NULL AND #__nbill_paypal_preapp_invitations.id IS NULL ";
            break;
    }
    $sql .= $whereclause;
    $sql .= " GROUP BY #__nbill_entity.id";
    $sql .= " ORDER BY CONCAT(#__nbill_entity.company_name, CONCAT_WS(' ', contact_2.first_name, contact_2.last_name)) LIMIT $pagination->list_offset, $pagination->records_per_page";
    $nb_database->setQuery($sql);
    $rows = $nb_database->loadObjectList();
    if (!$rows) {
        $rows = array();
    }

    nbillPaypalFunctions::showClients($rows, $pagination);
}

function invite_client($invitation_id = null)
{
    nbf_common::load_language("email");
    $nb_database = nbf_cms::$interop->database;
    $contact_email = "";
    $to = "";
    $first_name = "";
    $last_name = "";
    $contact_name = "";
    $preapp_desc = "";
    $multi_client = false;

    if ($invitation_id) {
        $invitation = null;
        $sql = "SELECT * FROM #__nbill_paypal_preapp_invitations WHERE id = " . intval($invitation_id);
        $nb_database->setQuery($sql);
        $nb_database->loadObject($invitation);
        $client_id = intval($invitation->client_id);
        $to = $invitation->sent_to;
        $contact_email = $invitation->email_address;
        $first_name = $invitation->first_name;
        $last_name = $invitation->last_name;
        $contact_name = trim($first_name . ' ' . $last_name);
        $max_amount = $invitation->max_amount;
        $payment_count = $invitation->payment_count;
        $preapp_desc = $invitation->description;
    } else {
        //Load gateway parameters
        $nb_database = nbf_cms::$interop->database;
        $sql = "SELECT * FROM #__nbill_payment_gateway WHERE gateway_id = 'paypal'";
        $nb_database->setQuery($sql);
        $paypal_fields = $nb_database->loadAssocList('g_key');
        if (!array_key_exists('app_id', $paypal_fields)) {
            //loadAssocList has not worked
            $paypal_fields = array();
            $alt_paypal_fields = $nb_database->loadObjectList();
            if (!$alt_paypal_fields) {
                $alt_paypal_fields = array();
            }
            foreach ($alt_paypal_fields as $alt_paypal_field) {
                $paypal_fields[$alt_paypal_field->g_key] = array();
                $paypal_fields[$alt_paypal_field->g_key]['g_key'] = $alt_paypal_field->g_key;
                $paypal_fields[$alt_paypal_field->g_key]['g_value'] = $alt_paypal_field->g_value;
            }
        }

        $client_id = nbf_common::get_param($_REQUEST, 'cid');
        if (is_array($client_id)) {
            if (count($client_id) == 1) {
                $client_id = intval($client_id[0]);
            } else {
                $multi_client = true;
            }
        } else {
            $client_id = intval($client_id);
        }

        $max_amount = $paypal_fields['default_max_amount']['g_value'];
        $payment_count = intval($paypal_fields['default_payment_count']['g_value']);
    }

    $from = "";
    $default_vendor_name = nbf_cms::$interop->live_site;
    $default_vendor_currency = 'USD';

    $default_vendor = null;
    $sql = "SELECT vendor_name, admin_email, vendor_currency FROM #__nbill_vendor WHERE default_vendor = 1";
    $nb_database->setQuery($sql);
    $nb_database->loadObject($default_vendor);
    if ($default_vendor) {
        $from = $default_vendor->admin_email;
        $default_vendor_name = $default_vendor->vendor_name;
        $default_vendor_currency = $default_vendor->vendor_currency;
    }

    if (!$multi_client && (!$contact_name || !$to)) {
        $contact = null;
        $sql = "SELECT email_address, first_name, last_name FROM #__nbill_contact INNER JOIN #__nbill_entity ON #__nbill_entity.primary_contact_id = #__nbill_contact.id WHERE #__nbill_entity.id = $client_id";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($contact);
        if ($contact) {
            if (!$to) {
                $to = $contact->email_address;
            }
            if (!$contact_name) {
                $contact_name = trim($contact->first_name . ' ' . $contact->last_name);
            }
            if (!$first_name && !$last_name) {
                $first_name = $contact->first_name;
                $last_name = $contact->last_name;
            }
        }
        if (!$contact_name || !$to) {
            $sql = "SELECT email_address, first_name, last_name FROM #__nbill_contact INNER JOIN #__nbill_entity_contact ON #__nbill_contact.id = #__nbill_entity_contact.contact_id WHERE #__nbill_entity_contact.entity_id = $client_id ORDER BY email_address DESC LIMIT 1";
            $nb_database->setQuery($sql);
            $nb_database->loadObject($contact);
            if ($contact) {
                if (!$to) {
                    $to = $contact->email_address;
                }
                if (!$contact_name) {
                    $contact_name = trim($contact->first_name . ' ' . $contact->last_name);
                }
                if (!$first_name && !$last_name) {
                    $first_name = $contact->first_name;
                    $last_name = $contact->last_name;
                }
            }
        }
    }
    if (!$contact_email && $to) {
        $contact_email = $to;
    }

    if ($multi_client) {
        $client_name = NBILL_PAYPAL_MULTIPLE_RECIPIENTS;
    } else {
        $sql = "SELECT company_name FROM #__nbill_entity WHERE id = " . intval($client_id);
        $nb_database->setQuery($sql);
        $company_name = $nb_database->loadResult();

        $client_name = trim($company_name);
        if (strlen($company_name) > 0 && strlen($contact_name) > 0) {
             $client_name .= ' (';
        }
        $client_name .= $contact_name;
        if (strlen($company_name) > 0 && strlen($contact_name) > 0) {
             $client_name .= ')';
        }
    }

    $sql = "SELECT code FROM #__nbill_currency ORDER BY code";
    $nb_database->setQuery($sql);
    $currencies = $nb_database->loadResultArray();
    if (!$currencies) {
        $currencies = array('USD');
    }

    nbillPaypalFunctions::inviteClient($invitation_id, $multi_client, $client_id, $client_name, $first_name, $last_name, $default_vendor_name, $from, $to, $contact_email, $max_amount, $payment_count, $preapp_desc, $currencies, $default_vendor_currency);
}

function default_invitation_message()
{
    $nb_database = nbf_cms::$interop->database;
    $client_id = nbf_common::get_param($_REQUEST, 'client_id');
    $multi_client = false;
    if ($client_id == 'multi') {
        $multi_client = true;
    } else {
        $client_id = intval($client_id);
    }

    //Load gateway parameters
    $nb_database = nbf_cms::$interop->database;
    $sql = "SELECT * FROM #__nbill_payment_gateway WHERE gateway_id = 'paypal'";
    $nb_database->setQuery($sql);
    $paypal_fields = $nb_database->loadAssocList('g_key');
    if (!array_key_exists('app_id', $paypal_fields)) {
        //loadAssocList has not worked
        $paypal_fields = array();
        $alt_paypal_fields = $nb_database->loadObjectList();
        if (!$alt_paypal_fields) {
            $alt_paypal_fields = array();
        }
        foreach ($alt_paypal_fields as $alt_paypal_field) {
            $paypal_fields[$alt_paypal_field->g_key] = array();
            $paypal_fields[$alt_paypal_field->g_key]['g_key'] = $alt_paypal_field->g_key;
            $paypal_fields[$alt_paypal_field->g_key]['g_value'] = $alt_paypal_field->g_value;
        }
    }

    if ($multi_client) {
        $client_name = NBILL_PAYPAL_CLIENT_PLACEHOLDER;
    } else {
        //Load default contact details
        $contact = null;
        $sql = "SELECT first_name, last_name FROM #__nbill_contact INNER JOIN #__nbill_entity ON #__nbill_entity.primary_contact_id = #__nbill_contact.id WHERE #__nbill_entity.id = $client_id";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($contact);
        if (!$contact || strlen($contact->first_name . $contact->last_name) == 0) {
            $sql = "SELECT first_name, last_name FROM #__nbill_contact INNER JOIN #__nbill_entity_contact ON #__nbill_contact.id = #__nbill_entity_contact.contact_id WHERE #__nbill_entity_contact.entity_id = $client_id ORDER BY email_address DESC LIMIT 1";
            $nb_database->setQuery($sql);
            $nb_database->loadObject($contact);
        }
        if ($contact) {
            $client_name = trim($contact->first_name . ' ' . $contact->last_name);
        }
        if (!$client_name) {
            $client_name = NBILL_PAYPAL_CLIENT;
        }
    }

    //Load vendor name (for signature)
    $sql = "SELECT vendor_name FROM #__nbill_vendor WHERE default_vendor = 1";
    $nb_database->setQuery($sql);
    $vendor_name = $nb_database->loadResult();
    if (!$vendor_name) {
        $vendor_name = nbf_cms::$interop->live_site;
    }

    $link = '<a href="http://www.paypal.com">' . NBILL_PAYPAL_PREAPP_LINK_PLACEHOLDER . '</a>';
    $default_message = "";
    if ($paypal_fields['api_sandbox']['g_value'] == '1') {
        $default_message = '<span style="color:#ff0000">' . NBILL_PAYPAL_INVITE_SANDBOX_WARNING . '</span><br /><br />';
    }
    $default_message .= sprintf(NBILL_PAYPAL_PREAPP_INVITE_BODY, $client_name, $link, $vendor_name);
    $default_message = str_replace("\n", "<br />", $default_message);
    nb_main_html::show_email_message_editor($default_message, true);
}

function send_invitation()
{
    $nb_database = nbf_cms::$interop->database;
    $invitation_id = nbf_common::get_param($_REQUEST, 'invitation_id');
    $invitation_update = $invitation_id ? true : false;

    $messages = array();

    $client_ids = array();
    $first_names = array();
    $last_names = array();
    $client_names = array();
    $contact_names = array();
    $send_to = array();
    if (nbf_common::get_param($_REQUEST, 'multi_client')) {
        $client_ids = explode(",", nbf_common::get_param($_REQUEST, 'cid'));
        foreach ($client_ids as $array_index=>$client_id) {
            $contact = null;
            $sql = "SELECT email_address, first_name, last_name
                    FROM #__nbill_contact
                    INNER JOIN #__nbill_entity ON #__nbill_entity.primary_contact_id = #__nbill_contact.id
                    WHERE #__nbill_entity.id = $client_id";
            $nb_database->setQuery($sql);
            $nb_database->loadObject($contact);
            if ($contact) {
                $send_to[$array_index] = $contact->email_address;
                $contact_email[$array_index] = $contact->email_address;
                $client_names[$array_index] = trim($contact->first_name . ' ' . $contact->last_name);
                $first_names[$array_index] = $contact->first_name;
                $last_names[$array_index] = $contact->last_name;
            }
            if (!$client_names[$array_index] || !$send_to[$array_index]) {
                $sql = "SELECT email_address, first_name, last_name FROM #__nbill_contact INNER JOIN #__nbill_entity_contact ON #__nbill_contact.id = #__nbill_entity_contact.contact_id WHERE #__nbill_entity_contact.entity_id = $client_id ORDER BY email_address DESC LIMIT 1";
                $nb_database->setQuery($sql);
                $nb_database->loadObject($contact);
                if ($contact) {
                    $send_to[$array_index] = $contact->email_address;
                    $contact_email[$array_index] = $contact->email_address;
                    $client_names[$array_index] = trim($contact->first_name . ' ' . $contact->last_name);
                    $first_names[$array_index] = $contact->first_name;
                    $last_names[$array_index] = $contact->last_name;
                }
            }
            $sql = "SELECT company_name FROM #__nbill_entity WHERE id = " . intval($client_id);
            $nb_database->setQuery($sql);
            $company_name = $nb_database->loadResult();

            $client_name = trim($company_name);
            if (strlen($company_name) > 0 && strlen($client_names[$array_index]) > 0) {
                 $client_name .= ' (';
            }
            $client_name .= $client_names[$array_index];
            if (strlen($company_name) > 0 && strlen($client_names[$array_index]) > 0) {
                 $client_name .= ')';
            }
            $client_names[$array_index] = $client_name;
            $contact_names[$array_index] = trim($first_names[$array_index] . ' ' . $last_names[$array_index]);
            if (strlen($company_name) > 0) {
                $contact_names[$array_index] .= ' (' . $company_name . ')';
            }
        }
    } else {
        $client_ids[] = intval(nbf_common::get_param($_REQUEST, 'cid'));
        $first_names[] = nbf_common::get_param($_REQUEST, 'first_name');
        $last_names[] = nbf_common::get_param($_REQUEST, 'last_name');
        $client_names[] = nbf_common::get_param($_REQUEST, 'client_name');
        $contact_email[] = nbf_common::get_param($_REQUEST, 'contact_email');
        $send_to[] = nbf_common::get_param($_REQUEST, 'message_to');
    }

    foreach ($client_ids as $array_index=>$client_id) {
        if (intval($invitation_id)) {
            $sql = "UPDATE #__nbill_paypal_preapp_invitations SET
                    email_address = '" . $contact_email[$array_index] . "',
                    sent_to = '" . $send_to[$array_index] . "',
                    max_amount = " . format_number(nbf_common::get_param($_REQUEST, 'max_amount'), 2) . ",
                    currency = '" . nbf_common::get_param($_REQUEST, 'currency', 'USD') . "',
                    payment_count = " . intval(nbf_common::get_param($_REQUEST, 'payment_count')) . ",
                    description = '" . nbf_common::get_param($_REQUEST, 'description') . "',
                    date_sent = " . nbf_common::nb_time() . "
                    WHERE id = " . intval($invitation_id);
            $nb_database->setQuery($sql);
            $nb_database->query();
            //Load the token
            $sql = "SELECT token FROM #__nbill_paypal_preapp_invitations WHERE id = " . intval($invitation_id);
            $nb_database->setQuery($sql);
            $token = $nb_database->loadResult();
        } else {
            $token = substr(uniqid(), 0, 15);
            $sql = "INSERT INTO #__nbill_paypal_preapp_invitations (client_id, first_name, last_name, email_address, sent_to, max_amount, currency, payment_count, description, token, date_sent)
                        VALUES (" . intval($client_id) . ",
                        '" . $first_names[$array_index] . "',
                        '" . $last_names[$array_index] . "',
                        '" . $contact_email[$array_index] . "',
                        '" . $send_to[$array_index] . "',
                        " . format_number(nbf_common::get_param($_REQUEST, 'max_amount'), 2) . ",
                        '" . nbf_common::get_param($_REQUEST, 'currency') . "',
                        " . intval(nbf_common::get_param($_REQUEST, 'payment_count')) . ",
                        '" . nbf_common::get_param($_REQUEST, 'description') . "',
                        '" . $token . "',
                        " . nbf_common::nb_time() . ")";
            $nb_database->setQuery($sql);
            $nb_database->query();
            $invitation_id = intval($nb_database->insertid());
        }

        if (!$invitation_id) {
            //Failed to save invitation!
            $messages[] = sprintf(NBILL_PAYPAL_PREAPP_INVITATION_SAVE_FAILED, $client_names[$array_index], $nb_database->_errorMsg);
        } else {
            //Check whether to use https
            $sql = "SELECT id FROM #__nbill_configuration WHERE switch_to_ssl = 1 OR all_pages_ssl = 1";
            $nb_database->setQuery($sql);
            $ssl = $nb_database->loadResult() ? true : false;

            $hash = md5($invitation_id . $token);
            $url = ($ssl ? str_replace('http:', 'https:', nbf_cms::$interop->live_site) : nbf_cms::$interop->live_site) . "/" . nbf_cms::$interop->site_popup_page_prefix . '&action=gatewayfunctions&gateway=paypal&task=functions&process=preauth&id=' . $invitation_id . '&token=' . $hash . nbf_cms::$interop->public_site_page_suffix();

            $message = urldecode(nbf_common::get_param($_REQUEST, 'html_message', '', true, false, true));
            $message = str_replace('http://www.paypal.com', $url, $message);
            $message = str_replace(NBILL_PAYPAL_CLIENT_PLACEHOLDER, $client_names[$array_index], $message);

            if (nbf_cms::$interop->send_email(nbf_common::get_param($_REQUEST, 'message_from'), nbf_common::get_param($_REQUEST, 'message_from_name'), explode(",", $send_to[$array_index]), nbf_common::get_param($_REQUEST, 'message_subject'), $message, 1, explode(",", nbf_common::get_param($_REQUEST, 'message_cc')), explode(",", nbf_common::get_param($_REQUEST, 'message_bcc')))) {
                //Success!
                $messages[] = sprintf(NBILL_PAYPAL_PREAPP_INVITATION_SENT, $client_names[$array_index]);
            } else {
                //Failed to send email!
                $messages[] = sprintf(NBILL_PAYPAL_PREAPP_INVITATION_FAILED, $client_names[$array_index], '<a href="' . $url . '">' . $url . '</a>');
                //If new, delete the invitation
                if (!$invitation_update) {
                    $sql = "DELETE FROM #__nbill_paypal_preapp_invitations WHERE id = " . intval($invitation_id);
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                }
            }
        }
        $invitation_id = null;
    }

    nbf_globals::$message = implode("<br /><br />", $messages);
    show_clients();
}

function delete_preauth()
{
    $nb_database = nbf_cms::$interop->database;
    $res_ids = nbf_common::get_param($_REQUEST, 'cid');
    foreach ($res_ids as &$res_id) {
        $res_id = intval($res_id);
    }

    if (!$res_ids) {
        nbf_globals::$message = NBILL_TB_SELECT_ITEM_TO_DELETE;
        return;
    } else {
        $sql = "SELECT resource_id FROM #__nbill_paypal_preapp_resources WHERE id IN (" . implode(",", $res_ids) . ")";
        $nb_database->setQuery($sql);
        $res_ids = $nb_database->loadResultArray();
    }

    $sql = "SELECT * FROM #__nbill_payment_gateway WHERE gateway_id = 'paypal'";
    $nb_database->setQuery($sql);
    $paypal_fields = $nb_database->loadAssocList('g_key');
    if (!array_key_exists('app_id', $paypal_fields)) {
        //loadAssocList has not worked
        $paypal_fields = array();
        $alt_paypal_fields = $nb_database->loadObjectList();
        if (!$alt_paypal_fields)
        {
            $alt_paypal_fields = array();
        }
        foreach ($alt_paypal_fields as $alt_paypal_field)
        {
            $paypal_fields[$alt_paypal_field->g_key] = array();
            $paypal_fields[$alt_paypal_field->g_key]['g_key'] = $alt_paypal_field->g_key;
            $paypal_fields[$alt_paypal_field->g_key]['g_value'] = $alt_paypal_field->g_value;
        }
    }

    require_once(nbf_cms::$interop->nbill_admin_base_path . '/admin.gateway/admin.paypal/sdk/vendor/autoload.php');
    $requestEnvelope = new RequestEnvelope("en_US");
    foreach ($res_ids as $res_id) {
        $cancelPreapprovalReq = new CancelPreapprovalRequest($requestEnvelope, $res_id);
        $config = array(
            // Signature Credential
            "mode" => $paypal_fields['api_sandbox']['g_value'] ? "sandbox" : "live",
            "acct1.UserName" => $paypal_fields['api_sandbox']['g_value'] ? $paypal_fields['api_sandbox_user']['g_value'] : $paypal_fields['api_user']['g_value'],
            "acct1.Password" => $paypal_fields['api_sandbox']['g_value'] ? $paypal_fields['api_sandbox_password']['g_value'] : $paypal_fields['api_password']['g_value'],
            "acct1.Signature" => $paypal_fields['api_sandbox']['g_value'] ? $paypal_fields['api_sandbox_signature']['g_value'] : $paypal_fields['api_signature']['g_value'],
            "acct1.AppId" => $paypal_fields['api_sandbox']['g_value'] ? $paypal_fields['api_sandbox_appid']['g_value'] : $paypal_fields['api_appid']['g_value']
        );
        $service = new AdaptivePaymentsService($config);
        try {
            $response = $service->CancelPreapproval($cancelPreapprovalReq);
            $sql = "DELETE FROM #__nbill_paypal_preapp_resources WHERE resource_id = " . intval($res_id);
            $nb_database->setQuery($sql);
            $nb_database->query();
        } catch(Exception $ex) {
            echo sprintf(NBILL_PAYPAL_API_ERR, str_replace(nbf_cms::$interop->site_base_path, "", $ex->getFile()) . ":line " . $ex->getLine() . ": " . $ex->getMessage());
            return;
        }
    }
}

function show_payable_invoices()
{
    $nb_database = nbf_cms::$interop->database;
    nbf_common::load_language("invoices");

    //Work out date range
    if (nbf_common::get_param($_REQUEST, 'show_reset')) {
        $_REQUEST['search_date_from'] = null;
        $_REQUEST['search_date_to'] = null;
        unset($_REQUEST['show_all']);
    }
    $date_format = nbf_common::get_date_format();
    $cal_date_format = nbf_common::get_date_format(true);
    $date_parts = nbf_common::nb_getdate(time());
    if (nbf_common::get_param($_REQUEST, 'show_all')) {
        $search_date_from = 0;
        $search_date_to = nbf_common::nb_mktime(23, 59, 59, 12, 31, 2037); //Largest value allowed for a date using a 32-bit integer is 18th Jan 2038
    } else {
        $search_date_from = -1;
        if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST,'search_date_from')) > 5) {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
            $filter_date_parts = nbf_date::get_date_parts(nbf_common::get_param($_REQUEST,'search_date_from'), $cal_date_format);
            if (count($filter_date_parts) == 3) {
                $search_date_from = nbf_common::nb_mktime($filter_date_parts['y']==1970&&$filter_date_parts['m']==1&&$filter_date_parts['d']==1 ? 1 : 0, 0, 0, $filter_date_parts['m'], $filter_date_parts['d'], $filter_date_parts['y']);
            }
        }
        if ($search_date_from == -1) {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
            $search_date_from = nbf_date::get_default_start_date();
        }
        $search_date_to = nbf_common::nb_mktime(23, 59, 59, $date_parts["mon"], $date_parts["mday"], $date_parts["year"]);
        if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST,'search_date_to')) > 5) {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
            $filter_date_parts = nbf_date::get_date_parts(nbf_common::get_param($_REQUEST,'search_date_to'), $cal_date_format);
            if (count($filter_date_parts) == 3) {
                $search_date_to = nbf_common::nb_mktime(23, 59, 59, $filter_date_parts['m'], $filter_date_parts['d'], $filter_date_parts['y']);
            }
        }
    }
    $_REQUEST['search_date_from'] = nbf_common::nb_date($date_format, $search_date_from);
    $_REQUEST['search_date_to'] = nbf_common::nb_date($date_format, $search_date_to);

    $nbill_no_filter = nbf_common::get_param($_REQUEST,'nbill_no_search');
    $nbill_no_filter = "%$nbill_no_filter%";
    $client_filter = nbf_common::get_param($_REQUEST,'client_search');
    $client_filter = "%$client_filter%";
    $product_filter = nbf_common::get_param($_REQUEST,'description_search');
    $product_filter = "%$product_filter%";
    if ($nbill_no_filter == "%%") {$nbill_no_filter = "";}
    if ($client_filter == "%%") {$client_filter = "";}
    if ($product_filter == "%%") {$product_filter = "";}
    $_POST['nbill_no_search'] = nbf_common::get_param($_REQUEST,'nbill_no_search');
    $_POST['client_search'] = nbf_common::get_param($_REQUEST,'client_search');
    $_POST['description_search'] = nbf_common::get_param($_REQUEST,'description_search');

    $query = "SELECT count(*) FROM #__nbill_document";
    $whereclause = " WHERE ";
    $count_joins = " INNER JOIN #__nbill_paypal_preapp_resources ON #__nbill_document.entity_id = #__nbill_paypal_preapp_resources.entity_id
                        LEFT JOIN #__nbill_payment_plans ON #__nbill_document.payment_plan_id = #__nbill_payment_plans.id";
    $whereclause .= "document_type = 'IN'
                        AND paid_in_full = 0
                        AND #__nbill_document.written_off = 0
                        AND #__nbill_paypal_preapp_resources.type = 'preapp'
                        AND #__nbill_paypal_preapp_resources.currency = #__nbill_document.currency
                        AND #__nbill_paypal_preapp_resources.amount >= #__nbill_document.total_gross
                        AND #__nbill_paypal_preapp_resources.status = 'active'
                        AND ((#__nbill_payment_plans.plan_type != 'BB' AND #__nbill_payment_plans.plan_type != 'DD') OR #__nbill_document.gateway_txn_id = 0) ";

    if (nbf_common::nb_strlen($nbill_no_filter) > 2) {
        if (nbf_common::nb_strlen($whereclause) > 7) {
            $whereclause .= " AND ";
        }
        $whereclause .= " #__nbill_document.document_no LIKE '$nbill_no_filter'";
    }
    if (nbf_common::nb_strlen($client_filter) > 2) {
        $count_joins .= " LEFT JOIN (#__nbill_entity LEFT JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id) ON #__nbill_document.entity_id = #__nbill_entity.id ";
        if (nbf_common::nb_strlen($whereclause) > 7) {
            $whereclause .= " AND ";
        }
        $whereclause .= " (#__nbill_entity.company_name LIKE '$client_filter' OR CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name) LIKE '$client_filter' OR #__nbill_document.billing_name LIKE '$client_filter' OR CONCAT(#__nbill_entity.company_name, ' (', CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name), ')') LIKE '$client_filter')";
    }
    if (intval(nbf_common::get_param($_REQUEST, 'client_id'))) {
        if (nbf_common::nb_strlen($whereclause) > 7) {
            $whereclause .= " AND ";
        }
        $whereclause .= " #__nbill_document.entity_id = " . intval(nbf_common::get_param($_REQUEST, 'client_id'));
    }
    if (nbf_common::nb_strlen($product_filter) > 2) {
        $count_joins .= " LEFT JOIN #__nbill_document_items ON #__nbill_document.id = #__nbill_document_items.document_id ";
        if (nbf_common::nb_strlen($whereclause) > 7) {
            $whereclause .= " AND ";
        }
        $whereclause .= "(#__nbill_document_items.product_description LIKE '$product_filter' OR #__nbill_document_items.detailed_description LIKE '$product_filter' OR #__nbill_document_items.product_code LIKE '$product_filter' OR #__nbill_document_items.discount_description LIKE '$product_filter')";
    }
    if (nbf_common::nb_strlen($whereclause) > 7) {
        $whereclause .= " AND ";
    }
    $whereclause .= "#__nbill_document.document_date >= $search_date_from AND #__nbill_document.document_date <= $search_date_to";

    $query .= $count_joins . $whereclause;
    $nb_database->setQuery($query);
    $total = $nb_database->loadResult();

    //Add page navigation
    $pagination = new nbf_pagination("pp_invoice", $total);

    //Load the records
    $joins = " INNER JOIN #__nbill_paypal_preapp_resources ON #__nbill_document.entity_id = #__nbill_paypal_preapp_resources.entity_id
                LEFT JOIN #__nbill_payment_plans ON #__nbill_document.payment_plan_id = #__nbill_payment_plans.id ";

    $sql = "SELECT #__nbill_document.id, #__nbill_document.vendor_id, #__nbill_document.id AS document_id, #__nbill_document.document_no, #__nbill_document.entity_id,
                #__nbill_document.billing_name, #__nbill_document.document_date, (#__nbill_document.total_net + #__nbill_document.total_shipping) AS total_net,
                (#__nbill_document.total_tax + #__nbill_document.total_shipping_tax) AS total_tax, #__nbill_document.total_gross,
                #__nbill_document.paid_in_full, #__nbill_document.partial_payment, #__nbill_document.email_sent, #__nbill_document.written_off,
                #__nbill_document.status, #__nbill_document.currency, #__nbill_document.document_type,
                TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS contact_name, #__nbill_entity.company_name,
                #__nbill_entity.is_client, #__nbill_entity.is_supplier";
    $sql .= " FROM #__nbill_document ";
    $joins .= "LEFT JOIN #__nbill_entity ON #__nbill_document.entity_id = #__nbill_entity.id
                LEFT JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id ";
    if (nbf_common::nb_strlen($product_filter) > 0) {
        $joins .= "LEFT JOIN #__nbill_document_items ON #__nbill_document.id = #__nbill_document_items.document_id ";
    }
    $sql .= $joins . $whereclause . " GROUP BY #__nbill_document.id";
    $sql .= " ORDER BY #__nbill_document.paid_in_full, #__nbill_document.partial_payment, DATE(FROM_UNIXTIME(#__nbill_document.document_date)) DESC, #__nbill_document.document_no + 0 DESC, #__nbill_document.document_no DESC ";
    $sql .= " LIMIT $pagination->list_offset, $pagination->records_per_page";

    $nb_database->setQuery($sql);
    $rows = $nb_database->loadObjectList();
    if (!$rows) {
        $rows = array();
    }

    //Get total net/tax/gross for the current page of invoices
    $sql = "SELECT currency, SUM(tmp_invoice.total_net) AS total_net_page, SUM(tmp_invoice.total_tax) AS total_tax_page,
                    SUM(tmp_invoice.total_gross) AS total_gross_page FROM ($sql) AS tmp_invoice GROUP BY currency ";
    $nb_database->setQuery($sql);
    $page_totals = $nb_database->loadObjectList();
    if (!$page_totals) {
        $page_totals = array();
    }

    //Get total net/tax/gross for ALL invoices in date range
    $sql = "SELECT currency, (SUM(#__nbill_document.total_net) + SUM(#__nbill_document.total_shipping)) AS total_net_all,
                    (SUM(#__nbill_document.total_tax) + SUM(#__nbill_document.total_shipping_tax)) AS total_tax_all,
                    SUM(#__nbill_document.total_gross) AS total_gross_all FROM #__nbill_document $count_joins $whereclause GROUP BY currency";
    $nb_database->setQuery($sql);
    $sum_totals = $nb_database->loadObjectList();
    if (!$sum_totals) {
        $sum_totals = array();
    }

    //Get list of ids
    $document_ids = array();
    foreach ($rows as $row) {
        $document_ids[] = $row->id;
    }

    $first_product_description = array();
    $document_items = array();
    $max_items_per_invoice = 0;

    if (count($document_ids) > 0) {
        //Get the first item or section's description
        $sql = "SELECT id, document_id, product_description, section_name FROM #__nbill_document_items WHERE
                        document_id IN (" . implode(",", $document_ids) . ") ORDER BY
                        document_id, ordering";
        $nb_database->setQuery($sql);
        $first_product_description = $nb_database->loadObjectList();
    }
    if (!$first_product_description) {
        $first_product_description = array();
    }

    //Get the date format
    $cfg_date_format = nbf_common::get_date_format(false);

    //Make a note of any that are already awaiting payment
    $g_tx_id_array = array();
    foreach ($rows as &$row) {
        $row->payment_pending_until = 0;
        $document_id = intval($row->id);
        $sql = "SELECT id, document_ids, payment_pending_until FROM #__nbill_gateway_tx WHERE document_ids LIKE '%" . $document_id . "%' ORDER BY id DESC LIMIT 10";
        $nb_database->setQuery($sql);
        $potential_txs = $nb_database->loadObjectList();
        foreach ($potential_txs as $potential_tx) {
            if ($potential_tx->document_ids) {
                $this_invoice_id_array = explode(",", $potential_tx->document_ids);
                if (array_search($document_id, $this_invoice_id_array) !== false) {
                    if ($potential_tx->payment_pending_until && $potential_tx->payment_pending_until > nbf_common::nb_time()) {
                        //Already awaiting payment
                        $row->payment_pending_until = $potential_tx->payment_pending_until;
                        break;
                    }
                }
            }
        }
    }

    //If any subscriptions are awaiting clearance, and any of the invoices will be paid as part of that, mark them as pending
    $related_orders = array();
    $sql = "SELECT document_id, order_id FROM #__nbill_orders_document WHERE document_id IN (" . implode(",", $document_ids) . ")";
    $nb_database->setQuery($sql);
    $related_orders = $nb_database->loadObjectList();
    if ($related_orders) {
        foreach ($related_orders as $related_order) {
            $match_found = false;
            $potential_renewals = array();
            $sql = "SELECT pending_order_id, payment_pending_until FROM #__nbill_gateway_tx WHERE payment_pending_until > 0 AND pending_order_id LIKE '%RENEW_" . $related_order->order_id . "%'";
            $nb_database->setQuery($sql);
            $potential_renewals = $nb_database->loadObjectList();
            if ($potential_renewals) {
                foreach ($potential_renewals as $renewal) {
                    $renewal_order_ids = explode(",", $renewal->pending_order_id);
                    foreach ($renewal_order_ids as $renewal_id) {
                        if ($renewal_id == 'RENEW_' . $related_order->order_id) {
                            //We have a match - mark the latest invoice for this order as pending
                            foreach ($rows as &$row) {
                                if (!$row->payment_pending_until && !$row->partial_payment && $row->id == $related_order->document_id) {
                                    $row->payment_pending_until = $renewal->payment_pending_until;
                                    break;
                                }
                            }
                            $match_found = true;
                            break;
                        }
                    }
                    if ($match_found) {
                        break;
                    }
                }
            }
        }
    }

    nbillPaypalFunctions::showInvoices($rows, $pagination, $first_product_description, $cfg_date_format, $page_totals, $sum_totals);
}

function show_payment_summary()
{
    $nb_database = nbf_cms::$interop->database;

    $document_ids = nbf_common::get_param($_REQUEST, 'cid');
    if (!$document_ids) {
        nbf_globals::$message = NBILL_PAYPAL_NO_INVOICES_SELECTED;
        show_payable_invoices();
        return;
    }

    //Skip any already awaiting payment
    $skip_ids = array();
    $g_tx_id_array = array();
    foreach ($document_ids as $document_id) {
        $sql = "SELECT id, document_ids, payment_pending_until FROM #__nbill_gateway_tx WHERE document_ids LIKE '%" . $document_id . "%' ORDER BY id DESC LIMIT 10";
        $nb_database->setQuery($sql);
        $potential_txs = $nb_database->loadObjectList();
        foreach ($potential_txs as $potential_tx) {
            if ($potential_tx->document_ids) {
                $this_invoice_id_array = explode(",", $potential_tx->document_ids);
                if (array_search($document_id, $this_invoice_id_array) !== false) {
                    if ($potential_tx->payment_pending_until && $potential_tx->payment_pending_until > nbf_common::nb_time()) {
                        //Already awaiting payment
                        $skip_ids[] = $document_id;
                        break;
                    }
                }
            }
        }
    }

    $document_ids = array_diff($document_ids, $skip_ids);

    if (!$document_ids) {
        nbf_globals::$message = NBILL_PAYPAL_NO_VALID_INVOICES_SELECTED;
        show_payable_invoices();
        return;
    }

    $payment_docs = array();
    $sql = "SELECT id, document_no, billing_name, billing_address, entity_id, billing_country, tax_exemption_code, reference, total_gross, currency
                FROM #__nbill_document WHERE id IN (" . implode(",", $document_ids) . ")";
    $nb_database->setQuery($sql);
    $documents = $nb_database->loadObjectList();
    $document = null;
    if ($documents) {
        foreach ($documents as &$document) {
            if ($document) {
                $total_gross = $document->total_gross;
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");
                $tax_rates = array();
                $tax_amounts = array();
                $ledger_codes = array();
                $ledger_nets = array();
                $ledger_tax_rates = array();
                $ledger_taxes = array();
                $ledger_grosses = array();
                nbf_payment::load_invoice_breakdowns(0, $document->id, $tax_rates, $tax_amounts, $ledger_codes, $ledger_nets, $ledger_tax_rates, $ledger_taxes, $ledger_grosses, $total_gross);
                $document->total_gross = format_number($total_gross);
                $payment_docs[] = $document;
            }
        }
    }

    nbillPaypalFunctions::takeInvoicePayment($payment_docs);
}