<?php
/**
* Main processing file for clients
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');
nbf_common::load_language("core.profile_fields");switch ($task)
{
	case "new":
	    $cid[0] = null;
        //fall through
    case "edit":
		editClient($cid[0], intval(nbf_common::get_param($_POST, 'use_posted_values')));
		break;
	case "save":
		saveClient();
		if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'return')) > 0)
		{
			nbf_common::redirect(base64_decode(nbf_common::get_param($_REQUEST,'return')));
			break;
		}
		showClients();
		break;
	case "apply":
		saveClient();
		if (!$id)
		{
			$id = intval(nbf_common::get_param($_POST,'id'));
		}
		if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'moveto')) > 0)
		{
			editClient(intval(nbf_common::get_param($_POST,'moveto')));
		}
		else
		{
			editClient($id);
		}
		break;
	case "remove":
	case "delete":
		deleteClient($cid);
		showClients();
		break;
	case "cancel":
		if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'return')) > 0)
		{
			nbf_common::redirect(base64_decode(nbf_common::get_param($_REQUEST,'return')));
			break;
		}
		nbf_globals::$message = "";
		showClients();
		break;
	case "mambot_remove":
		remove_user_from_mambot_control(nbf_common::get_param($_REQUEST, 'id'));
		showClients();
		break;
    case "promote":
        promote_potential_clients($cid);
        showClients();
        break;
    case "detach_file":
        nbf_common::detach_file(intval(nbf_common::get_param($_REQUEST, 'attachment_id')));
        showClients();
        break;
    case "delete_file":
        nbf_common::delete_file(intval(nbf_common::get_param($_REQUEST, 'attachment_id')));
        showClients();
        break;
    case "detach_file_edit":
        nbf_common::detach_file(intval(nbf_common::get_param($_REQUEST, 'attachment_id')));
        editClient($cid[0], true);
        break;
    case "delete_file_edit":
        nbf_common::delete_file(intval(nbf_common::get_param($_REQUEST, 'attachment_id')));
        editClient($cid[0], true);
        break;
    case "silent":
        //Using functions only
        break;
	default:
		nbf_globals::$message = "";
		showClients();
		break;
}

function showClients()
{
	$nb_database = nbf_cms::$interop->database;

    $potential = nbf_common::get_param($_REQUEST, 'action') == 'potential_clients';

	//Get Vendors
	$sql = "SELECT id, vendor_name FROM #__nbill_vendor ORDER BY id";
	$nb_database->setQuery($sql);
	$vendors = $nb_database->loadObjectList();

    //Get user table and column names
    $user_table = nbf_cms::$interop->cms_database_enum->table_user;
    $user_username_col = nbf_cms::$interop->cms_database_enum->column_user_username;
    $user_id_col = nbf_cms::$interop->cms_database_enum->column_user_id;

	//Get client name filter
	$client_filter = "%" . trim(nbf_common::get_param($_REQUEST,'client_search')) . "%";
    $client_user_filter = "%" . trim(nbf_common::get_param($_REQUEST,'client_user_search')) . "%";
    $client_email_filter = "%" . trim(nbf_common::get_param($_REQUEST,'client_email_search')) . "%";

	//Count the total number of records
	$query = "SELECT count(*) as ordering FROM (SELECT #__nbill_entity.id FROM #__nbill_entity";

    //Alias contact_2 used so that it will work on BOTH SQL statements
	$query .= " LEFT JOIN (#__nbill_entity_contact INNER JOIN #__nbill_contact AS contact_2 ON #__nbill_entity_contact.contact_id = contact_2.id) ON #__nbill_entity_contact.entity_id = #__nbill_entity.id";
    if (nbf_common::nb_strlen($client_user_filter) > 2)
    {
        $query .= " LEFT JOIN $user_table ON contact_2.user_id = $user_table.$user_id_col";
    }
    

	$whereclause = " WHERE 1";
	if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST, 'for_contact')) > 0)
    {
        if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST, 'for_contact')) > 0)
        {
            $whereclause .= " AND contact_2.id = " . intval(nbf_common::get_param($_REQUEST, 'for_contact'));
        }
    }
    else
    {
        if (nbf_common::nb_strlen($client_filter) > 2)
	    {
		    $whereclause .= " AND (#__nbill_entity.company_name LIKE '$client_filter' OR CONCAT_WS(' ', #__nbill_entity.company_name, contact_2.first_name, contact_2.last_name) LIKE '$client_filter')";
	    }
        if (nbf_common::nb_strlen($client_user_filter) > 2)
        {
            $whereclause .= " AND $user_table.$user_username_col LIKE '$client_user_filter'";
        }
        if (nbf_common::nb_strlen($client_email_filter) > 2)
        {
            $whereclause .= " AND (contact_2.email_address LIKE '$client_email_filter' OR contact_2.email_address_2 LIKE '$client_email_filter')";
        }
	}
    
    if ($potential)
    {
        $whereclause .= " AND #__nbill_entity.is_client = 0 AND #__nbill_entity.is_supplier = 0";
    }
    else
    {
        $whereclause .= " AND #__nbill_entity.is_client = 1";
    }
    if (!nbf_common::get_param($_REQUEST, 'for_contact')) {
        $whereclause .= " AND (#__nbill_entity.primary_contact_id = #__nbill_entity_contact.contact_id OR #__nbill_entity.primary_contact_id IS NULL OR #__nbill_entity.primary_contact_id = 0)";
    }
	$query .= $whereclause . " GROUP BY #__nbill_entity.id) AS entity_list";
	$nb_database->setQuery($query);
	$total = $nb_database->loadResult();

	//Add page navigation
	$pagination = new nbf_pagination("client", $total);

    //Load the records
    $sql = "SELECT #__nbill_entity.id AS entity_id, #__nbill_entity.*, contact_2.id AS contact_id, contact_2.user_id,
                    CONCAT_WS(' ', contact_2.first_name, contact_2.last_name) AS `name`, contact_2.email_address, contact_2.telephone, `$user_table`.`$user_username_col` AS username,
                    #__nbill_account_expiry.user_id AS subscriber, COUNT(#__nbill_supporting_docs.id) AS attachment_count
                    FROM #__nbill_entity
                    LEFT JOIN (#__nbill_entity_contact INNER JOIN #__nbill_contact AS contact_2 ON #__nbill_entity_contact.contact_id = contact_2.id) ON #__nbill_entity.id = #__nbill_entity_contact.entity_id
                    LEFT JOIN `$user_table` ON contact_2.user_id = `$user_table`.`$user_id_col` ";
    
    $sql .= " LEFT JOIN #__nbill_account_expiry ON (contact_2.user_id = #__nbill_account_expiry.user_id AND `$user_table`.`$user_id_col` = contact_2.user_id)";
    $sql .= " LEFT JOIN #__nbill_supporting_docs ON #__nbill_entity.id = #__nbill_supporting_docs.associated_doc_id AND #__nbill_supporting_docs.associated_doc_type = 'CL'";
    $sql .= $whereclause;
    $sql .= " GROUP BY #__nbill_entity.id ORDER BY CONCAT(#__nbill_entity.company_name, CONCAT_WS(' ', contact_2.first_name, contact_2.last_name)) LIMIT $pagination->list_offset, $pagination->records_per_page";
    $nb_database->setQuery($sql);
    $rows = $nb_database->loadObjectList();
    if (!$rows)
    {
        $rows = array();
    }

    //Get any attachments
    $attachments = array();
    

	nBillClients::showClients($rows, $potential, $pagination, $vendors, $attachments);
}

function editClient($client_id, $use_posted_values = false)
{
    $potential = nbf_common::get_param($_REQUEST, 'action') == 'potential_clients';

	include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
    $nb_database = nbf_cms::$interop->database;
	$row = $nb_database->load_record("#__nbill_entity", $client_id);

    $contact_factory = new nBillContactFactory();
    $entity_factory = new nBillEntityFactory();
    $entity_service = $entity_factory->createEntityService($contact_factory->createContactService());
    $row->shipping_address = $entity_service->getShippingAddress($row->id);

    if ($row->id)
    {
        //If a supplier has been requested, redirect
        if ($row->is_supplier && !$row->is_client)
        {
            $params = "";
            foreach ($_GET as $key=>$value)
            {
                $value = $key == 'action' ? 'suppliers' : $value;
                $params .= "&" . $key . "=" . $value;
            }
            nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . $params);
        }
        else if (!$row->is_supplier && !$row->is_client)
        {
            $_POST['action'] = 'potential_clients';
            $_REQUEST['action'] = 'potential_clients';
            $potential = true;
        }
    }

    //Load vendors
	$sql = "SELECT id, vendor_name, vendor_country, vendor_currency, default_vendor FROM #__nbill_vendor ORDER BY default_vendor DESC, id";
	$nb_database->setQuery($sql);
	$vendors = $nb_database->loadObjectList();

	$ledger = array();
	foreach ($vendors as $vendor)
	{
		//Get nominal ledger codes
		$sql = "SELECT * FROM #__nbill_nominal_ledger WHERE vendor_id = " . $vendor->id . " ORDER BY code";
		$nb_database->setQuery($sql);
		$ledger[$vendor->id] = $nb_database->loadObjectList();
		if (!isset($ledger[$vendor->id]) || !$ledger[$vendor->id])
		{
			$ledger[$vendor->id] = array();
		}
	}

	$country_codes = nbf_xref::get_countries();
	$currency_codes = nbf_xref::get_currencies();
    $shared_mappings = array_keys(array_intersect($nb_database->get_entity_mapping(true), $nb_database->get_contact_mapping(true)));

    //Load contacts
	$sql = "SELECT #__nbill_contact.id, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS `name`, #__nbill_entity_contact.*, ";
    if (count($shared_mappings) > 0)
    {
        $sql .= "#__nbill_contact." . implode(", #__nbill_contact.", $shared_mappings) . " ";
    }
    $sql .= "FROM #__nbill_contact
			INNER JOIN #__nbill_entity_contact ON #__nbill_contact.id = #__nbill_entity_contact.contact_id
			INNER JOIN #__nbill_entity ON #__nbill_entity_contact.entity_id = #__nbill_entity.id
			WHERE #__nbill_entity.id = " . $client_id . "
			ORDER BY #__nbill_entity.primary_contact_id = #__nbill_contact.id DESC, `name`";
	$nb_database->setQuery($sql);
	$contacts = $nb_database->loadObjectList();

	//Load email invoice options
	$email_options_xref = nbf_xref::load_xref("email_invoice");
    $sql = "SELECT email_invoice_option FROM #__nbill_configuration WHERE id = 1";
    $nb_database->setQuery($sql);
    $default_email_invoice_option = $nb_database->loadResult();

	//Load Credits
	$credits = array();
	foreach ($vendors as $vendor)
	{
		$credits[$vendor->id] = null;
		$sql = "SELECT * FROM #__nbill_client_credit WHERE entity_id = " . intval($client_id) . " AND vendor_id = " . $vendor->id;
		$nb_database->setQuery($sql);
		$nb_database->loadObject($credits[$vendor->id]);
	}

    //Load custom field definitions
    $sql = "SELECT id, field_type, name, label, default_value, checkbox_text, required, attributes, xref, xref_sql, help_text
            FROM #__nbill_profile_fields
            WHERE entity_mapping = 'custom' AND field_type NOT IN ('JJ', 'LL', 'MM', 'NN', 'OO', 'SS')
            ORDER BY ordering";
    $nb_database->setQuery($sql);
    $custom_fields = $nb_database->loadObjectList();

    //Load the custom field options
    $field_options = array();
    if ($custom_fields && count($custom_fields) > 0)
    {
        nbf_common::load_language("form.editor");
    }
    foreach ($custom_fields as $field)
    {
        //If options are to be loaded from a table or SQL query, load them...
        $sql = "";
        $sql_field_options[$field->id] = array();
        $field_options[$field->id] = array();
        $xref_options = null;
        if ($field->xref == "nbill_sql_list")
        {
            $sql = $field->xref_sql;
            $nb_database->setQuery($sql);
            $xref_options = $nb_database->loadObjectList();
        }
        else if (nbf_common::nb_strlen($field->xref) > 0)
        {
            if (nbf_common::nb_strpos($field->xref, "country_codes") !== false)
            {
                $xref_options = nbf_xref::load_xref($field->xref, false, true, array('WW', 'EU'));
            }
            else
            {
                $xref_options = nbf_xref::load_xref($field->xref);
            }
        }
        if ($xref_options)
        {
            $i = 0;
            foreach ($xref_options as $xref_option)
            {
                $sql_field_option = new stdClass();
                $sql_field_option->id = "xref_" . $i;
                $sql_field_option->form_id = $form_id;
                $sql_field_option->field_id = $field->id;
                $sql_field_option->ordering = $i+1;
                $sql_field_option->code = $xref_option->code;
                $sql_field_option->description = $xref_option->description;
                $field_options[$field->id][] = $sql_field_option;
                $i++;
            }
        }

        //Add any manual options
        $sql = "SELECT *, option_value AS code, option_description AS description FROM #__nbill_profile_fields_options WHERE field_id = " . intval($field->id) . " ORDER BY ordering";
        $nb_database->setQuery($sql);
        $field_options[$field->id] = $field_options[$field->id] + $nb_database->loadObjectList();
    }

    if ($custom_fields && count($custom_fields))
    {
        //Apply the values
        $values = array();
        if ($row && $row->id && nbf_common::nb_strlen($row->custom_fields) > 0)
        {
            $values = unserialize(stripslashes($row->custom_fields));
        }
        foreach ($custom_fields as &$custom_field)
        {
            if ($row && $row->id)
            {
                if (array_key_exists($custom_field->name, $values))
                {
                    $custom_field->default_value = $values[$custom_field->name];
                }
            }
        }
    }

	//Check whether there are any custom fields for contacts (to determine how much space to allow for iframe)
    $sql = "SELECT count(*) FROM #__nbill_profile_fields WHERE contact_mapping = 'custom'";
    $nb_database->setQuery($sql);
    $contact_custom_fields = $nb_database->loadResult() ? true : false;

    //If all the shared client/contact fields are the same for the client AND primary contact, flag to update contact when client is updated
    $sync_primary = false;
    if ($row->primary_contact_id && count($contacts) > 0)
    {
        foreach ($contacts as $contact)
        {
            if ($contact->id == $row->primary_contact_id)
            {
                $mapping_match = true;
                foreach ($shared_mappings as $shared_mapping)
                {
                    if ($row->$shared_mapping != $contact->$shared_mapping)
                    {
                        $mapping_match = false;
                        break;
                    }
                }
                if ($mapping_match)
                {
                    $sync_primary = true;
                }
                break;
            }
        }
    }

    //Get the last contact ID, so we can tell for sure whether a new contact has been added successfully
    $sql = "SELECT id FROM #__nbill_contact ORDER BY id DESC LIMIT 1";
    $nb_database->setQuery($sql);
    $last_contact_id = $nb_database->loadResult();

    //Get a list of languages available
    $languages = nbf_cms::$interop->get_list_of_languages();

    //Get any attachments
    $attachments = array();
    

    $ip_info = null;
    if (intval(@$row->id))
    {
        //Get IP address/country information
        $sql = "SELECT * FROM #__nbill_entity_ip_address WHERE entity_id = " . intval($row->id);
        $nb_database->setQuery($sql);
        $ip_info = $nb_database->loadObjectList();
    }

    ob_start();
    nBillClients::editClient($client_id, $row, $languages, $potential, $custom_fields, $field_options, $country_codes, $currency_codes, $vendors, $default_email_invoice_option, $ledger, $email_options_xref, $contacts, $contact_custom_fields, $credits, $sync_primary, $last_contact_id, $use_posted_values, $attachments, $ip_info);
    $html = ob_get_clean();
    $output = nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'edit', get_defined_vars(), $html);
    echo $output;
}

function saveClient()
{
	$nb_database = nbf_cms::$interop->database;
	$new_client = false;

    $_POST['last_updated'] = nbf_common::nb_time();

	//Need to check if tax exemption code has changed
	$tax_exemption_code = '';
	if (nbf_common::get_param($_POST,'id'))
	{
		$sql = "SELECT tax_exemption_code FROM #__nbill_entity WHERE id = " . intval(nbf_common::get_param($_POST,'id'));
		$nb_database->setQuery($sql);
		$tax_exemption_code = $nb_database->loadResult();
	}

	$_POST['default_currency'] = nbf_common::get_param($_POST, 'default_currency_' . nbf_common::get_param($_POST,'vendor_id'));
	$_POST['credit_currency'] = nbf_common::get_param($_POST, 'credit_currency_' . nbf_common::get_param($_POST, 'vendor_id'));
	$_POST['credit_ledger_code'] = nbf_common::get_param($_POST, 'ledger_' . nbf_common::get_param($_POST, 'vendor_id'));

    //Extract and serialize custom field values
    $sql = "SELECT name
            FROM #__nbill_profile_fields
            WHERE entity_mapping = 'custom' AND field_type NOT IN ('JJ', 'LL', 'MM', 'NN', 'OO', 'SS')
            ORDER BY ordering";
    $nb_database->setQuery($sql);
    $custom_fields = $nb_database->loadObjectList();
    $custom_field_values = array();
    if ($custom_fields)
    {
        foreach ($custom_fields as $custom_field)
        {
            $custom_field_values[$custom_field->name] = nbf_common::get_param($_REQUEST, 'ctl_' . $custom_field->name, null, true, false, true);
        }
    }
    if ($custom_field_values && count($custom_field_values))
    {
        $_POST['custom_fields'] = serialize($custom_field_values);
    }

	$nb_database->bind_and_save("#__nbill_entity", $_POST);

    $insert = !nbf_common::get_param($_POST,'id');
    if ($insert) {
        $_POST['id'] = $nb_database->insertid();
    }
    $contact_factory = new nBillContactFactory();
    $entity_factory = new nBillEntityFactory();
    $entity_service = $entity_factory->createEntityService($contact_factory->createContactService());
    if (nbf_common::get_param($_REQUEST, 'same_as_billing')) {
        $entity_service->deleteShippingAddress(intval(nbf_common::get_param($_POST, 'id')));
    } else {
        $entity_service->saveShippingAddress($_REQUEST, intval(nbf_common::get_param($_POST, 'id')));
    }

    if ($insert)
    {
		$new_client = true;
		nbf_common::fire_event("client_created", array("id"=>nbf_common::get_param($_POST,'id')));
	}
	else
	{
		nbf_common::fire_event("record_updated", array("type"=>"client", "id"=>nbf_common::get_param($_POST, 'id')));
	}

	//If a tax exemption code has been added, update any orders to use it (and recalculate tax)
	$this_tax_exemption_code = nbf_common::get_param($_POST,'tax_exemption_code');
	if ($tax_exemption_code != $this_tax_exemption_code)
	{
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.tax.class.php");
		nbf_tax::update_tax_exemption_code(nbf_common::get_param($_POST,'id'));
	}

	//If any contacts need removing or deleting, do it...
	foreach ($_POST as $key=>$value)
	{
		switch (substr($key, 0, 7))
		{
			case "remove_":
			case "delete_":
				$contact_id = intval(substr($key, 7));
				if ($contact_id)
				{
                    //Check whether this is the primary (if so, we need to assign a remaining contact as the primary)
                    $sql = "SELECT primary_contact_id FROM #__nbill_entity WHERE id = " . intval(@$_POST['id']);
                    $nb_database->setQuery($sql);
                    $this_primary = $nb_database->loadResult();
                    if ($this_primary && $this_primary == $contact_id)
                    {
                        //Find another contact to use as primary, or set to zero if none
                        $sql = "SELECT contact_id FROM #__nbill_entity_contact WHERE entity_id = " . intval(@$_POST['id']) . " AND contact_id != $contact_id";
                        $nb_database->setQuery($sql);
                        $new_primary = intval($nb_database->loadResult());
                        $sql = "UPDATE #__nbill_entity SET primary_contact_id = $new_primary WHERE id = " . intval(@$_POST['id']);
                        $nb_database->setQuery($sql);
                        $nb_database->query();
                    }

					//Remove from client
					$sql = "DELETE FROM #__nbill_entity_contact WHERE entity_id = " . intval(@$_POST['id']) . " AND contact_id = $contact_id";
					$nb_database->setQuery($sql);
					$nb_database->query();
					if (substr($key, 0, 7) == "delete_")
					{
						//Delete contact completely
						$sql = "DELETE FROM #__nbill_contact WHERE id = $contact_id";
						$nb_database->setQuery($sql);
						$nb_database->query();
					}
				}
				break;
		}
	}

	//Load default email invoice option (from default vendor record)
    $sql = "SELECT email_invoice_option FROM #__nbill_configuration WHERE id = 1";
    $nb_database->setQuery($sql);
    $email_invoice_option = $nb_database->loadResult();
    if (!$email_invoice_option)
    {
        $email_invoice_option = "EE";
    }

    //If any contacts need assigning, do it...
    foreach ($_POST as $key=>$value)
	{
		switch (substr($key, 0, 15))
		{
			case "assign_contact_":
				$contact_id = intval(substr($key, 15));
				if ($contact_id)
				{
                    $sql = "REPLACE INTO #__nbill_entity_contact (entity_id, contact_id, email_invoice_option) VALUES (" . intval(nbf_common::get_param($_POST,'id')) . ", $contact_id, '$email_invoice_option')";
					$nb_database->setQuery($sql);
					$nb_database->query();

					//If client does not have a primary contact, make this the one...
					if (!nbf_common::get_param($_REQUEST, 'primary_contact_id'))
					{
						$_REQUEST['primary_contact_id'] = $contact_id;
						$sql = "UPDATE #__nbill_entity SET primary_contact_id = $contact_id WHERE id = " . intval(nbf_common::get_param($_POST,'id'));
						$nb_database->setQuery($sql);
						$nb_database->query();
					}
				}
				break;
		}
	}

	//If this is a new client with a new contact, or a new contact on a client which previously had no contacts, associate the two
	if ($new_client && nbf_common::get_param($_POST, 'new_contact'))
	{
		//Load the most recent addition to the contacts table (negligible risk of getting the wrong one)
		$sql = "SELECT id FROM #__nbill_contact ORDER BY id DESC LIMIT 1";
		$nb_database->setQuery($sql);
		$new_contact_id = intval($nb_database->loadResult());
        if ($new_contact_id > intval(nbf_common::get_param($_REQUEST, 'last_contact_id')))
        {
            //Replace rather than insert in case of page refresh
		    $sql = "REPLACE INTO #__nbill_entity_contact (entity_id, contact_id, email_invoice_option) VALUES (" . intval(nbf_common::get_param($_POST,'id')) . ", $new_contact_id, '$email_invoice_option')";
		    $nb_database->setQuery($sql);
		    $nb_database->query();

        }
	}
    if (nbf_common::get_param($_POST, 'new_contact'))
    {
        //If client does not have a primary contact, make this the one...
        if (!nbf_common::get_param($_REQUEST, 'primary_contact_id'))
        {
            //Load the most recent addition to the contacts table (negligible risk of getting the wrong one)
            $sql = "SELECT id FROM #__nbill_contact ORDER BY id DESC LIMIT 1";
            $nb_database->setQuery($sql);
            $new_contact_id = intval($nb_database->loadResult());
            $_REQUEST['primary_contact_id'] = $new_contact_id;
            $sql = "UPDATE #__nbill_entity SET primary_contact_id = $new_contact_id WHERE id = " . intval(nbf_common::get_param($_POST,'id'));
            $nb_database->setQuery($sql);
            $nb_database->query();
        }
    }

	//Update any contact permissions
	$sql = array();
	foreach ($_POST as $key=>$value)
	{
		switch (substr($key, 0, 3))
		{
			case "cp_":
				$contact_id = intval(substr($key, 3, nbf_common::nb_strpos($key, "_", 3) - 3));
				if (!isset($sql[$contact_id])){$sql[$contact_id]='UPDATE #__nbill_entity_contact SET ';}
				$sql[$contact_id] .= substr($key, strlen($contact_id) + 4) . " = '" . $value . "',";
				break;
		}
	}
	if (count($sql))
	{
		foreach ($sql as $contact_id=>$query)
		{
			$query = substr($query, 0, strlen($query) - 1) . " WHERE #__nbill_entity_contact.entity_id = " . intval(nbf_common::get_param($_POST,'id')) . " AND #__nbill_entity_contact.contact_id = " . $contact_id;
			$nb_database->setQuery($query);
			$nb_database->query();
		}
	}

    

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'save', get_defined_vars());
}

function deleteClient($id_array)
{
	$nb_database = nbf_cms::$interop->database;

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'delete', get_defined_vars());
	nbf_common::fire_event("client_deleted", array("ids"=>implode(",", $id_array)));

    //Delete shipping address, if applicable
    $sql = "SELECT shipping_address_id FROM #__nbill_entity WHERE id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $shipping_address_ids = $nb_database->loadResultArray();
    if ($shipping_address_ids && count($shipping_address_ids) > 0) {
        $sql = "DELETE FROM #__nbill_address WHERE id IN (" . implode(",", $shipping_address_ids) . ")";
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

	//Delete client record
	$sql = "DELETE FROM #__nbill_entity WHERE id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

    //Delete any contacts that are not being used elsewhere
    $sql = "SELECT contact_id FROM #__nbill_entity_contact WHERE entity_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $contact_ids = array_unique($nb_database->loadResultArray());
    if (count($contact_ids) > 0)
    {
        //Delete shipping address, if applicable
        $sql = "SELECT shipping_address_id FROM #__nbill_contact WHERE id IN (" . implode(",", $contact_ids) . ")";
        $nb_database->setQuery($sql);
        $shipping_address_ids = $nb_database->loadResultArray();
        if ($shipping_address_ids && count($shipping_address_ids) > 0) {
            $sql = "DELETE FROM #__nbill_address WHERE id IN (" . implode(",", $shipping_address_ids) . ")";
            $nb_database->setQuery($sql);
            $nb_database->query();
        }

        $sql = "SELECT contact_id FROM #__nbill_entity_contact WHERE contact_id IN (" . implode(",", $contact_ids) . ") AND entity_id NOT IN (" . implode(",", $id_array) . ")";
        $nb_database->setQuery($sql);
        $contacts_in_use = $nb_database->loadResultArray();
        $delete_contacts = array_diff($contact_ids, $contacts_in_use);
        if (count($delete_contacts) > 0)
        {
            nbf_common::fire_event("contact_deleted", array("ids"=>implode(",", $delete_contacts)));
            $sql = "DELETE FROM #__nbill_contact WHERE id IN (" . implode(",", $delete_contacts) . ")";
            $nb_database->setQuery($sql);
            $nb_database->query();
        }
    }

	//Delete any contact associations
	$sql = "DELETE FROM #__nbill_entity_contact WHERE entity_id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

	//Cancel any orders for this client
	$sql = "UPDATE #__nbill_orders SET order_cancelled = 1, cancellation_reason = '" . NBILL_CLIENT_DELETED
					 . "', client_id = 0 WHERE client_id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

	//Delete any pending orders for this client
	$sql = "DELETE FROM #__nbill_pending_orders WHERE client_id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

    //Remove association from any quotes and invoices
    $sql = "UPDATE #__nbill_document SET entity_id = 0 WHERE entity_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();

    //Remove any client credit
    $sql = "DELETE FROM #__nbill_client_credit WHERE entity_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();

    //Delete any reminders associated with this client (they are useless now)
    $sql = "DELETE FROM #__nbill_reminders WHERE client_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();

    //Remove association with any transactions
    $sql = "UPDATE #__nbill_transaction SET entity_id = 0 WHERE entity_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();

    //Detach any attachments
    $sql = "DELETE FROM #__nbill_supporting_docs WHERE associated_doc_type = 'CL' AND associated_doc_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();
}

function remove_user_from_mambot_control($user_id)
{
	$sql = "DELETE FROM #__nbill_account_expiry WHERE user_id = " . intval($user_id);
	$nb_database->setQuery($sql);
	$nb_database->query();

	nbf_globals::$message = NBILL_CLIENT_MAMBOT_CONTROL_CANCELLED;
}