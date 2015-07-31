<?php
/**
* Main processing file for contacts
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
		editContact($cid[0]);
		break;
	case "save":
		saveContact();
		if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'return')) > 0)
		{
			nbf_common::redirect(base64_decode(nbf_common::get_param($_REQUEST,'return')));
			break;
		}
		showContacts();
		break;
	case "apply":
		saveContact();
		if (!$id)
		{
			$id = intval(nbf_common::get_param($_POST,'id'));
		}
		editContact($id);
		break;
	case "remove":
	case "delete":
		deleteContact($cid);
		showContacts();
		break;
	case "cancel":
		if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'return')) > 0)
		{
			nbf_common::redirect(base64_decode(nbf_common::get_param($_REQUEST,'return')));
			break;
		}
		nbf_globals::$message = "";
		showContacts();
		break;
    
	default:
		nbf_globals::$message = "";
		showContacts();
		break;
}

function showContacts()
{
	$nb_database = nbf_cms::$interop->database;

	//Get Vendors
	$sql = "SELECT id, vendor_name FROM #__nbill_vendor ORDER BY id";
	$nb_database->setQuery($sql);
	$vendors = $nb_database->loadObjectList();

    //Get user table and column names
    $user_table = nbf_cms::$interop->cms_database_enum->table_user;
    $user_username_col = nbf_cms::$interop->cms_database_enum->column_user_username;
    $user_id_col = nbf_cms::$interop->cms_database_enum->column_user_id;

	//Get contact name filter
	$contact_filter = "%" . trim(nbf_common::get_param($_POST,'contact_search')) . "%";
    $contact_user_filter = "%" . trim(nbf_common::get_param($_POST,'contact_user_search')) . "%";
    $contact_email_filter = "%" . trim(nbf_common::get_param($_POST,'contact_email_search')) . "%";

	//Count the total number of records
	$query = "SELECT count(*) FROM #__nbill_contact";
    if (nbf_common::nb_strlen($contact_user_filter) > 2)
    {
        $query .= " INNER JOIN $user_table ON #__nbill_contact.user_id = $user_table.$user_id_col";
    }
	$whereclause = "";
	if (nbf_common::nb_strlen($contact_filter) > 2)
	{
		$whereclause .= " WHERE CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name) LIKE '$contact_filter'";
	}
    if (nbf_common::nb_strlen($contact_user_filter) > 2)
    {
        if (nbf_common::nb_strlen($whereclause) > 0)
        {
            $whereclause .= " AND ";
        }
        else
        {
            $whereclause .= " WHERE ";
        }
        $whereclause .= "$user_table.$user_username_col LIKE '$contact_user_filter'";
    }
    if (nbf_common::nb_strlen($contact_email_filter) > 2)
    {
        if (nbf_common::nb_strlen($whereclause) > 0)
        {
            $whereclause .= " AND ";
        }
        else
        {
            $whereclause .= " WHERE ";
        }
        $whereclause .= " (#__nbill_contact.email_address LIKE '$contact_email_filter' OR #__nbill_contact.email_address_2 LIKE '$contact_email_filter')";
    }
	$query .= $whereclause;
	$nb_database->setQuery( $query );
	$total = $nb_database->loadResult();

	//Add page navigation
	$pagination = new nbf_pagination("contact", $total);

    //Load the records
	$sql = "SELECT #__nbill_contact.*, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS `name`,
                    `$user_table`.`$user_username_col` AS username, #__nbill_account_expiry.user_id AS subscriber,
                    #__nbill_entity.is_client, #__nbill_entity.is_supplier
                    FROM #__nbill_contact
					LEFT JOIN `$user_table` ON #__nbill_contact.user_id = `$user_table`.`$user_id_col`
                    LEFT JOIN (#__nbill_entity_contact LEFT JOIN #__nbill_entity ON #__nbill_entity_contact.entity_id = #__nbill_entity.id) ON #__nbill_entity_contact.contact_id = #__nbill_contact.id
                    LEFT JOIN #__nbill_account_expiry ON (#__nbill_contact.user_id = #__nbill_account_expiry.user_id AND `$user_table`.`$user_id_col` = #__nbill_contact.user_id)";
	$sql .= $whereclause;
	$sql .= " GROUP BY #__nbill_contact.id ORDER BY CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name) LIMIT $pagination->list_offset, $pagination->records_per_page";
	$nb_database->setQuery($sql);
	$rows = $nb_database->loadObjectList();
	if (!$rows)
	{
		$rows = array();
	}

	nBillContacts::showContacts($rows, $pagination, $vendors);
}

function editContact($contact_id)
{
	include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
    $nb_database = nbf_cms::$interop->database;
	$row = $nb_database->load_record("#__nbill_contact", $contact_id, false);
	if ($contact_id && $row == null) {
		//Contact has been deleted
		$contact_id = null;
	}

    $contact_factory = new nBillContactFactory();
    $contact_service = $contact_factory->createContactService();
    $row->shipping_address = $contact_service->getShippingAddress($row->id);

    //Load vendors
	$sql = "SELECT id, vendor_name, vendor_country, vendor_currency, default_vendor FROM #__nbill_vendor ORDER BY id";
	$nb_database->setQuery($sql);
	$vendors = $nb_database->loadObjectList();

	$user_list = false;
	$sql = "SELECT select_users_from_list FROM #__nbill_configuration";
	$nb_database->setQuery($sql);
	$nb_database->loadObject($config);
	if ($config)
	{
		$user_list = $config->select_users_from_list;
	}
	if ($user_list)
	{
        $user_table = nbf_cms::$interop->cms_database_enum->table_user;
        $user_username_col = nbf_cms::$interop->cms_database_enum->column_user_username;
        $user_id_col = nbf_cms::$interop->cms_database_enum->column_user_id;
		$sql = "SELECT `$user_id_col` AS id, `$user_username_col` AS username FROM `$user_table` ORDER BY username";
		$nb_database->setQuery($sql);
		$user_list = $nb_database->loadObjectList();
		if (!$user_list)
		{
			$user_list = array();
		}

        //In demo mode, don't allow admin access
        if (nbf_cms::$interop->demo_mode) {
            foreach ($user_list as $key=>$listed_user) {
                $this_user_id = intval(@$listed_user->id);
                if ($this_user_id) {
                    $this_user = nbf_cms::$interop->get_user($this_user_id);
                    foreach ($this_user->groups as $gid=>$group_name) {
                        if (($gid > 2 && $gid < 10) || strpos(strtolower($group_name), 'admin') !== false) {
                            unset($user_list[$key]);
                            break;
                        }
                    }
                }
            }
            $user_list = array_filter($user_list);
        }
	}

	$country_codes = nbf_xref::get_countries();
	$currency_codes = nbf_xref::get_currencies();

    //Load custom field definitions
    $sql = "SELECT id, field_type, name, label, default_value, checkbox_text, required, attributes, xref, xref_sql, help_text
            FROM #__nbill_profile_fields
            WHERE contact_mapping = 'custom' AND field_type NOT IN ('JJ', 'LL', 'MM', 'NN', 'OO', 'SS')
            ORDER BY ordering";
    $nb_database->setQuery($sql);
    $custom_fields = $nb_database->loadObjectList();

    if ($custom_fields && count($custom_fields) > 0)
    {
        if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/language/" . nbf_cms::$interop->language . "/form.editor." . nbf_cms::$interop->language . ".php"))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/language/" . nbf_cms::$interop->language . "/form.editor." . nbf_cms::$interop->language . ".php");
        }
        else
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/language/en-GB/form.editor.en-GB.php");
        }
    }

    //Load the custom field options
    $field_options = array();
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
            $values = unserialize($row->custom_fields);
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

    ob_start();
	nBillContacts::editContact($contact_id, $row, $custom_fields, $field_options, $country_codes, $currency_codes, $vendors, $user_list);
    $html = ob_get_clean();
    $output = nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'edit', get_defined_vars(), $html);
    echo $output;
}

function saveContact()
{
	$nb_database = nbf_cms::$interop->database;

    $_POST['email_address'] = trim(@$_POST['email_address']);
    $_POST['email_address_2'] = trim(@$_POST['email_address_2']);
    $_POST['username'] = trim(@$_POST['username']);
    $_POST['password'] = trim(@$_POST['password']);

	//If new user required, create one
	if (nbf_common::get_param($_POST,'user_id') == -2)
	{
        $name_array = array();
		$name_array[] = nbf_common::get_param($_POST, 'first_name', null, true);
        $name_array[] = nbf_common::get_param($_POST, 'last_name', null, true);
        $name = implode(" ", $name_array);
		$email = nbf_common::get_param($_POST, 'email_address');
		$username = nbf_common::get_param($_POST, 'username');
		$password = nbf_common::get_param($_POST, 'password', '', true);
		$user_id = nbf_cms::$interop->create_user($name, $username, $password, $email);
		if ($user_id == -1)
		{
			echo "<script> alert('".html_entity_decode(NBILL_ERR_CONTACT_COULD_NOT_CREATE_USER . '\n\n' . nbf_globals::$message)."'); window.history.go(-1); </script>\n";
			exit();
		}
		$_POST['user_id'] = $user_id;
	}

    //In demo mode, don't allow admin access
    if (nbf_cms::$interop->demo_mode) {
        $this_user_id = intval(@$_POST['user_id']);
        if ($this_user_id) {
            $this_user = nbf_cms::$interop->get_user($this_user_id);
            foreach ($this_user->groups as $gid=>$group_name) {
                if (($gid > 2 && $gid < 10) || strpos(strtolower($group_name), 'admin') !== false) {
                    $_POST['user_id'] = -1;
                    $user_id = -1;
                    break;
                }
            }
        }
    }

    //Extract and serialize custom field values
    $sql = "SELECT name
            FROM #__nbill_profile_fields
            WHERE contact_mapping = 'custom' AND field_type NOT IN ('JJ', 'LL', 'MM', 'NN', 'OO', 'SS')
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

    $_POST['last_updated'] = nbf_common::nb_time();
	$nb_database->bind_and_save("#__nbill_contact", $_POST);

    $insert = !nbf_common::get_param($_POST,'id');
    if ($insert) {
        $_POST['id'] = $nb_database->insertid();
    }

    $contact_factory = new nBillContactFactory();
    $contact_service = $contact_factory->createContactService();
    if (nbf_common::get_param($_REQUEST, 'same_as_billing')) {
        $contact_service->deleteShippingAddress(intval(nbf_common::get_param($_POST, 'id')));
    } else {
        $contact_service->saveShippingAddress($_REQUEST, intval(nbf_common::get_param($_POST, 'id')));
    }

	if ($insert)
	{
		nbf_common::fire_event("contact_created", array("id"=>nbf_common::get_param($_POST,'id')));
		//If saved from within a client or supplier record, save the entity association as well
		if (nbf_common::get_param($_REQUEST, 'nbill_entity_iframe'))
		{
            //Load default email invoice option (from default vendor record)
            $sql = "SELECT email_invoice_option FROM #__nbill_configuration WHERE id = 1";
            $nb_database->setQuery($sql);
            $email_invoice_option = $nb_database->loadResult();
            if (!$email_invoice_option)
            {
                $email_invoice_option = "EE";
            }
			$sql = "INSERT INTO #__nbill_entity_contact (entity_id, contact_id, email_invoice_option) VALUES (" . intval(nbf_common::get_param($_REQUEST, 'nbill_entity_iframe')) . ", " . intval($_POST['id']) . ", '$email_invoice_option')";
			$nb_database->setQuery($sql);
			$nb_database->query();
		}
	}
	else
	{
		nbf_common::fire_event("record_updated", array("type"=>"contact", "id"=>nbf_common::get_param($_POST, 'id')));
	}

	//If contact already associated with a user record, and email address or contact name changed, or password updated, update user record too.
	$user_id = intval(nbf_common::get_param($_POST, 'user_id'));
	if ($user_id > 0)
	{
        $email = nbf_common::get_param($_POST, 'email_address');
		if (strlen($email) > 0)
		{
			nbf_cms::$interop->update_email_address($email, $user_id);
		}

		$name = trim(nbf_common::get_param($_POST, 'first_name') . ' ' . nbf_common::get_param($_POST, 'last_name'));
		if (strlen($name) > 0)
		{
            nbf_cms::$interop->update_name($name, $user_id);
		}

        if (!nbf_cms::$interop->demo_mode) {
            $reset_password = trim(nbf_common::get_param($_POST, 'reset_password'));
            if (strlen($reset_password) > 0) {
                nbf_cms::$interop->update_password($reset_password, $user_id);
            }
        }
	}

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'save', get_defined_vars());
}

function deleteContact($id_array)
{
	$nb_database = nbf_cms::$interop->database;

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'delete', get_defined_vars());
	nbf_common::fire_event("contact_deleted", array("ids"=>implode(",", $id_array)));

    //Delete shipping address, if applicable
    $sql = "SELECT shipping_address_id FROM #__nbill_contact WHERE id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $shipping_address_ids = $nb_database->loadResultArray();
    if ($shipping_address_ids && count($shipping_address_ids) > 0) {
        $sql = "DELETE FROM #__nbill_address WHERE id IN (" . implode(",", $shipping_address_ids) . ")";
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

	//Delete contact record
	$sql = "DELETE FROM #__nbill_contact WHERE id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

	//Remove any entity associations
	$sql = "DELETE FROM #__nbill_entity_contact WHERE contact_id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();
}

function remove_user_from_mambot_control($user_id)
{
    $nb_database = nbf_cms::$interop->database;

	$sql = "DELETE FROM #__nbill_account_expiry WHERE user_id = " . intval($user_id);
	$nb_database->setQuery($sql);
	$nb_database->query();

	nbf_globals::$message = NBILL_CONTACT_MAMBOT_CONTROL_CANCELLED;
}