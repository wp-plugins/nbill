<?php
/**
* Main processing file for data import/export features
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

if (nbf_cms::$interop->demo_mode)
{
	echo NBILL_NOT_IN_DEMO_MODE;
	return;
}switch ($task)
{
	case "silent":
        break;
    case "clients":
		ioClients();
		break;
	case "import_all_clients_users":
		importAllUsers();
		break;
	case "import_select_clients_users":
		importSelectedUsers();
		break;
	case "import_selected_users":
		doUserImport($cid);
		break;
	case "import_clients_csv":
		importClientCSV();
		ioClients(true);
		break;
	case "export_clients_csv":
		exportAllClients();
		break;
	case "client_csv_help":
		showClientCSVHelp();
		break;
}

function ioClients()
{
	nBillIO::showIOClients();
}

function importAllUsers()
{
	$nb_database = nbf_cms::$interop->database;

	//Take every user record that is not already a contact, and create a client record for it
    $users = nbf_cms::$interop->get_non_super_admins();
	if (!$users)
	{
		$users = array();
	}

	//Get the default vendor country and use that as the default for new client records
	$sql = "SELECT vendor_country FROM #__nbill_vendor WHERE default_vendor = 1";
	$nb_database->setQuery($sql);
	$vendor_country = $nb_database->loadResult();

	$new_client_count = 0;
	foreach ($users as $user)
	{
		$sql = "INSERT INTO #__nbill_entity (is_client, country, last_updated, notes, custom_fields) VALUES (1, '$vendor_country', " . nbf_common::nb_time() . ", '', '')";
		$nb_database->setQuery($sql);
		$nb_database->query();
		$new_client_id = $nb_database->insertid();
		if ($new_client_id)
		{
            $sql = "INSERT INTO #__nbill_contact (user_id, first_name, last_name, email_address, country, last_updated, notes, custom_fields)
                        VALUES (" . $user->user_id .", '" . trim(nbf_common::nb_substr($user->name, 0, (nbf_common::nb_strpos($user->name, " ") > 0 ? nbf_common::nb_strpos($user->name, " ") : nbf_common::nb_strlen($user->name)))) . "', '" . trim(substr($user->name, nbf_common::nb_strpos($user->name, " "))) . "', '" . $user->email . "', '$vendor_country', " . nbf_common::nb_time() . ", '', '')";
            $nb_database->setQuery($sql);
            $nb_database->query();
            $new_contact_id = $nb_database->insertid();
            nbf_common::fire_event("contact_created", array("id"=>$new_contact_id));

            $sql = "UPDATE #__nbill_entity SET primary_contact_id = " . intval($new_contact_id) . " WHERE id = " . intval($new_client_id);
            $nb_database->setQuery($sql);
            $nb_database->query();

            $sql = "INSERT INTO #__nbill_entity_contact (contact_id, entity_id) VALUES (" . intval($new_contact_id) . ", " . intval($new_client_id) . ")";
            $nb_database->setQuery($sql);
            $nb_database->query();

			$new_client_count++;
			nbf_common::fire_event("client_created", array("id"=>$new_client_id));
		}
	}

	nbf_common::fire_event("users_imported", array("user_ids"=>"all", "client_ids"=>""));

	nbf_globals::$message = sprintf(NBILL_IMPORT_CLIENTS_COMPLETE, $new_client_count);

	ioClients(true);
}

function importSelectedUsers()
{
	$nb_database = nbf_cms::$interop->database;

    //Count the total number of records
	$total = nbf_cms::$interop->count_non_super_admins();

	//Add page navigation
	$pagination = new nbf_pagination("iouser", $total);

	//Get Users
	$rows = nbf_cms::$interop->get_non_super_admins($pagination);
	if (!$rows)
	{
		$rows = array();
	}

	nBillIO::selectUsers($rows, $pagination);
}

function doUserImport($id_array)
{
	$nb_database = nbf_cms::$interop->database;

    $user_table = nbf_cms::$interop->cms_database_enum->table_user;
    $user_name_col = nbf_cms::$interop->cms_database_enum->column_user_name;
    $user_email_col = nbf_cms::$interop->cms_database_enum->column_user_email;
    $user_id_col = nbf_cms::$interop->cms_database_enum->column_user_id;

	$sql = "SELECT `$user_id_col` AS user_id, `$user_name_col` AS name, `$user_email_col` AS email
            FROM `$user_table`
            WHERE `$user_id_col` IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$users = $nb_database->loadObjectList();
	if (!$users)
	{
		$users = array();
	}

	//Get the default vendor country and use that as the default for new client records
	$sql = "SELECT vendor_country FROM #__nbill_vendor WHERE default_vendor = 1";
	$nb_database->setQuery($sql);
	$vendor_country = $nb_database->loadResult();

	$new_client_count = 0;
	foreach ($users as $user)
	{
        $extra_fields = nbf_cms::$interop->load_cms_user_profile($user->user_id);
        $entity_fields = extract_table_fields($extra_fields, '#__nbill_entity');
        $contact_fields = extract_table_fields($extra_fields, '#__nbill_contact');
        $entity_custom_fields = extract_custom_fields($extra_fields, 'entity');
        $contact_custom_fields = extract_custom_fields($extra_fields, 'contact');

        if (!isset($entity_fields['country'])) {
            $entity_fields['country'] = $vendor_country;
        }
        if (!isset($contact_fields['country'])) {
            $contact_fields['country'] = $vendor_country;
        }
		$sql = "INSERT INTO #__nbill_entity (is_client, last_updated, notes, custom_fields";
        if ($entity_fields && count($entity_fields) > 0) {
            $sql .= ", `" . implode("`, `", array_keys($entity_fields)) . "`";
        }
        $sql .= ") VALUES (1, " . nbf_common::nb_time() . ", '', '" . (count($entity_custom_fields) > 0 ? serialize($entity_custom_fields) : '') . "'";
        if ($entity_fields && count($entity_fields) > 0) {
            $sql .= ", '" . implode("', '", array_values($entity_fields)) . "'";
        }
        $sql .= ")";
        $nb_database->setQuery($sql);
        $nb_database->query();
        $new_client_id = $nb_database->insertid();
        if ($new_client_id)
        {
            $sql = "INSERT INTO #__nbill_contact (user_id, first_name, last_name, email_address, last_updated, notes, custom_fields";
            if ($contact_fields && count($contact_fields) > 0) {
                $sql .= ", `" . implode("`, `", array_keys($contact_fields)) . "`";
            }
            $sql .= ") VALUES (" . $user->user_id .", '" . trim(substr($user->name, 0, (nbf_common::nb_strpos($user->name, " ") > 0 ? nbf_common::nb_strpos($user->name, " ") : nbf_common::nb_strlen($user->name)))) . "', '" . trim(substr($user->name, nbf_common::nb_strpos($user->name, " "))) . "', '" . $user->email . "', " . nbf_common::nb_time() . ", '', '" . (count($entity_custom_fields) > 0 ? serialize($entity_custom_fields) : '') . "'";
            if ($contact_fields && count($contact_fields) > 0) {
                $sql .= ", '" . implode("', '", array_values($contact_fields)) . "'";
            }
            $sql .= ")";
            $nb_database->setQuery($sql);
            $nb_database->query();
            $new_contact_id = $nb_database->insertid();
            nbf_common::fire_event("contact_created", array("id"=>$new_contact_id));

            $sql = "UPDATE #__nbill_entity SET primary_contact_id = " . intval($new_contact_id) . " WHERE id = " . intval($new_client_id);
            $nb_database->setQuery($sql);
            $nb_database->query();

            $sql = "INSERT INTO #__nbill_entity_contact (contact_id, entity_id) VALUES (" . intval($new_contact_id) . ", " . intval($new_client_id) . ")";
            $nb_database->setQuery($sql);
            $nb_database->query();

            $new_client_count++;
            nbf_common::fire_event("client_created", array("id"=>$new_client_id));
        }
	}

	nbf_common::fire_event("users_imported", array("user_ids"=>implode(",", $id_array), "client_ids"=>""));

	nbf_globals::$message = sprintf(NBILL_IMPORT_CLIENTS_COMPLETE, $new_client_count);
	importSelectedUsers();
}

function extract_table_fields($extra_fields, $table_name)
{
    $nb_database = nbf_cms::$interop->database;

    $table_fields = array();
    $sql = "SHOW COLUMNS FROM `$table_name` WHERE Field IN ('" . implode("', '", array_keys($extra_fields)) . "')";
    $nb_database->setQuery($sql);
    $table_columns = $nb_database->loadResultArray();
    foreach ($table_columns as $key) {
        $table_fields[$key] = $extra_fields[$key];
    }
    return $table_fields;
}

function extract_custom_fields($extra_fields, $mapping)
{
    $nb_database = nbf_cms::$interop->database;

    $custom_fields = array();
    $sql = "SELECT `name`, field_type FROM #__nbill_profile_fields
                    WHERE `name` IN ('" . implode("', '", array_keys($extra_fields)) . "')
                    AND " . ($mapping == 'entity' ? 'entity' : 'contact') . "_mapping = 'custom'";
    $nb_database->setQuery($sql);
    $table_columns = $nb_database->loadObjectList();
    foreach ($table_columns as $field) {
        if ($field->field_type == 'GZ') { //Date
            $date = strtotime($extra_fields[$field->name]);
            $custom_fields[$field->name] = date(nbf_common::get_date_format(), $date);
        } else {
            $custom_fields[$field->name] = $extra_fields[$field->name];
        }
    }
    return $custom_fields;
}

function importClientCSV()
{
	$nb_database = nbf_cms::$interop->database;

    $user_table = nbf_cms::$interop->cms_database_enum->table_user;
    $user_name_col = nbf_cms::$interop->cms_database_enum->column_user_name;
    $user_username_col = nbf_cms::$interop->cms_database_enum->column_user_username;
    $user_email_col = nbf_cms::$interop->cms_database_enum->column_user_email;
    $user_id_col = nbf_cms::$interop->cms_database_enum->column_user_id;
    $user_password_col = nbf_cms::$interop->cms_database_enum->column_user_password;

	if (count($_FILES) > 0 && isset($_FILES['import_clients_csv_file']))
	{
		//Read CSV file
        clearstatcache();
        ini_set("auto_detect_line_endings", true);

		$row = 1;
		$handle = fopen($_FILES['import_clients_csv_file']['tmp_name'], "r");

		$titles = array();
		$clients = array();
		$new_user_count = 0;
		$new_client_count = 0;
		$db_errors = array();
		$client_ids = array();

		//Get vendor country (to default to if not specified)
		$sql = "SELECT vendor_country FROM #__nbill_vendor WHERE default_vendor = 1";
		$nb_database->setQuery($sql);
		$vendor_country = $nb_database->loadResult();

		while (($data = fgetcsv($handle)) !== false)
		{
			$colcount = count($data);
			if ($colcount > 0)
			{
				if (count($titles) == 0)
				{
					$titles = $data;
				}
				else
				{
					$client = array();
					for ($i = 0; $i < $colcount; $i++)
					{
						$client[trim($titles[$i])] = trim($data[$i]);
					}

                    if (!array_key_exists("is_client", $client))
                    {
                        $client['is_client'] = '1';
                    }
                    if (!array_key_exists("last_updated", $client))
                    {
                        $client['last_updated'] = nbf_common::nb_time();
                    }

                    //If first name and last name are supplied, but name is not, populate name (for use in user creation if necessary)
                    if ((nbf_common::nb_strlen(@$client['first_name']) > 0 || nbf_common::nb_strlen(@$client['last_name']) > 0) && nbf_common::nb_strlen(@$client['name']) == 0)
                    {
                        $client['name'] = trim(@$client['first_name'] . ' ' . @$client['last_name']);
                    }

					//Check whether we already have a Client with the same email address
					if ((!isset($client['id']) || nbf_common::nb_strlen($client['id']) == 0) && isset($client['email_address']) && nbf_common::nb_strlen($client['email_address']) > 0)
					{
                        $existing_client = null;
						$sql = "SELECT #__nbill_entity.id AS entity_id, #__nbill_contact.id AS contact_id FROM #__nbill_entity
                                INNER JOIN #__nbill_entity_contact ON #__nbill_entity.id = #__nbill_entity_contact.entity_id
                                INNER JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id
                                WHERE #__nbill_contact.email_address = '" . $client['email_address'] . "'";
						$nb_database->setQuery($sql);
                        $nb_database->loadObject($existing_client);
						$client['id'] = @$existing_client->entity_id;
                        $client['contact_id'] = @$existing_client->contact_id;
                        $client['primary_contact_id'] = @$existing_client->contact_id;
					}

					//Check whether we already have a user with the same email address
					if ((!isset($client['user_id']) || nbf_common::nb_strlen($client['user_id']) == 0) && isset($client['email_address']) && nbf_common::nb_strlen($client['email_address']) > 0)
					{
						$sql = "SELECT `$user_id_col` AS user_id, `$user_name_col` AS name FROM `$user_table` WHERE `$user_email_col` = '" . $client['email_address'] . "'";
						$nb_database->setQuery($sql);
                        $user_data = null;
						$nb_database->loadObject($user_data);
						if ($user_data)
						{
							$client['user_id'] = $user_data->user_id;
							if ((!isset($client['company_name']) || nbf_common::nb_strlen($client['company_name']) == 0) && (!isset($client['name']) || nbf_common::nb_strlen($client['name']) == 0))
							{
								$client['name'] = $user_data->name;
							}
						}
					}

					//If we already have a user, update the email address if applicable
					if (isset($client['user_id']) && nbf_common::nb_strlen($client['user_id']) > 0 && intval($client['user_id']) > 0)
					{
                        if (isset($client['email_address']) && nbf_common::nb_strlen($client['email_address']) > 0)
                        {
						    $sql = "UPDATE `$user_table` SET `$user_email_col` = '" . $client['email_address'] . "' WHERE `$user_id_col` = " . $client['user_id'];
						    $nb_database->setQuery($sql);
						    $nb_database->query();
						    if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
						    {
							    $db_errors[] = $nb_database->_errorMsg;
						    }
                        }
                        else
                        {
                            //Get the client email address from the user record
                            $sql = "SELECT `$user_email_col` FROM `$user_table` WHERE `$user_id_col` = " . intval($client['user_id']);
                            $nb_database->setQuery($sql);
                            $client['email_address'] = $nb_database->loadResult();
                        }
					}

					if (isset($client['username']) && isset($client['password']) && isset($client['email_address']) &&
							(isset($client['company_name']) || isset($client['name'])) && (!isset($client['user_id']) || nbf_common::nb_strlen($client['user_id']) == 0))
					{
						//Check if new user record is required
						$new_user_id = -1;
						if (nbf_common::nb_strlen($client['username']) > 0 && nbf_common::nb_strlen($client['password']) > 0 && nbf_common::nb_strlen($client['email_address'] ) > 0)
						{
							//Get name and email address
							$name = trim($client['company_name']);
							if (nbf_common::nb_strlen($client['name']) > 0)
							{
								if (nbf_common::nb_strlen($name) > 0)
								{
									$name .= " (" . $client['name'] . ")";
								}
								else
								{
									$name = $client['name'];
								}
							}
							if (nbf_common::nb_strlen($name) > 0)
							{
								$new_user_id = nbf_cms::$interop->create_user($name, $client['username'], $client['password'], $client['email_address']);
								if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
								{
									$db_errors[] = $nb_database->_errorMsg;
								}
								if ($new_user_id > 0)
								{
									$new_user_count++;
									//If password was already MD5'd and/or salted, set it back again in the database
									if ((strlen($client['password']) == 32 && base64_decode($client['password']) !== false)
                                        || (strlen($client['password']) == 65 && substr($client['password'], 32, 1) == ":"))
									{
										$sql = "UPDATE `$user_table` SET `$user_password_col` = '" . $client['password'] . "' WHERE `$user_id_col` = " . intval($new_user_id);
										$nb_database->setQuery($sql);
										$nb_database->query();
										if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
										{
											$db_errors[] = $nb_database->_errorMsg;
										}
									}
									$client['user_id'] = $new_user_id;
								}
							}
						}
					}

					//If country not specified, default to vendor country
					if (!isset($client['country']) || nbf_common::nb_strlen($client['country']) == 0)
					{
						$client['country'] = $vendor_country;
					}

                    //If name is supplied but first_name and last_name are not, populate
                    if (nbf_common::nb_strlen(@$client['name']) > 0 && nbf_common::nb_strlen(@$client['first_name']) == 0 && nbf_common::nb_strlen(@$client['last_name']) == 0)
                    {
                        if (nbf_common::nb_strpos($client['name'], " ") !== false)
                        {
                            $client['first_name'] = substr($client['name'], 0, nbf_common::nb_strpos($client['name'], " "));
                            $client['last_name'] = substr($client['name'], nbf_common::nb_strpos($client['name'], " ") + 1);
                        }
                        else
                        {
                            $client['last_name'] = $client['name'];
                        }
                    }

					//Populate client table
                    $nb_database->bind_and_save("#__nbill_entity", $client, true);
					if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0 )
					{
						$db_errors[] = $nb_database->_errorMsg;
					}
					else
					{
						$new_client_id = $nb_database->insertid();
						if ($new_client_id)
						{
							$new_client_count++;
							$client_ids[] = $new_client_id;

                            //Create or update contact
                            $client['id'] = @$client['contact_id'];
                            $nb_database->bind_and_save("#__nbill_contact", $client, true);
                            if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0 )
                            {
                                $db_errors[] = $nb_database->_errorMsg;
                            }
                            else
                            {
                                $new_contact_id = $nb_database->insertid();
                                if ($new_contact_id)
                                {
                                    $client['contact_id'] = $new_contact_id;
                                    $sql = "UPDATE #__nbill_entity SET primary_contact_id = " . intval($new_contact_id) . ", last_updated = " . nbf_common::nb_time() . " WHERE id = " . $new_client_id;
                                    $nb_database->setQuery($sql);
                                    $nb_database->query();
                                    nbf_common::fire_event("contact_created", array("id"=>$new_client_id));
                                }
                                else
                                {
                                    nbf_common::fire_event("record_updated", array("type"=>"contact", "id"=>@$client['contact_id']));
                                }
                            }

                            //Update or insert into entity contact table
                            $client['entity_id'] = $new_client_id;
                            $nb_database->bind_and_save("#__nbill_entity_contact", $client, true);

							nbf_common::fire_event("client_created", array("id"=>$new_client_id));
						}
						else
						{
							$client_id = $client['id'];
                            if ($nb_database->getAffectedRows() > 0)
                            {
							    $client_ids[] = $client_id;
                            }
                            //Create or update contact
                            $client['id'] = @$client['contact_id'];
                            $nb_database->bind_and_save("#__nbill_contact", $client, true);
                            if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0 )
                            {
                                $db_errors[] = $nb_database->_errorMsg;
                            }
                            else
                            {
                                $new_contact_id = $nb_database->insertid();
                                if ($new_contact_id)
                                {
                                    $client['contact_id'] = $new_contact_id;
                                    $sql = "UPDATE #__nbill_entity SET primary_contact_id = " . intval($new_contact_id) . ", last_updated = " . nbf_common::nb_time() . " WHERE id = " . $new_client_id;
                                    $nb_database->setQuery($sql);
                                    $nb_database->query();
                                    $sql = "INSERT INTO #__nbill_entity_contact (contact_id, entity_id) VALUES (" . intval($new_contact_id) . ", " . intval($new_client_id) . ")";
                                    $nb_database->setQuery($sql);
                                    $nb_database->query();
                                    nbf_common::fire_event("contact_created", array("id"=>$new_client_id));
                                }
                                else
                                {
                                    nbf_common::fire_event("record_updated", array("type"=>"contact", "id"=>@$client['contact_id']));
                                }
                            }

                            //Update or insert into entity contact table
                            $client['entity_id'] = $client_id;
                            $nb_database->bind_and_save("#__nbill_entity_contact", $client, true);

							nbf_common::fire_event("record_updated", array("type"=>"client", "id"=>$client_id));
						}
					}
				}
			}
		}
		fclose($handle);

		nbf_globals::$message = NBILL_CLIENT_CSV_IMPORTED;
		if ($new_user_count > 0)
		{
			nbf_globals::$message .= "  " . sprintf(NBILL_CLIENT_CSV_IMPORT_NEW_USERS, $new_user_count);
		}
		nbf_globals::$message .= "  " . sprintf(NBILL_CLIENT_CSV_IMPORT_NEW_CLIENTS, $new_client_count + count($client_ids));
		if (count($db_errors) > 0)
		{
			nbf_globals::$message .= "  " . sprintf(NBILL_CLIENT_CSV_IMPORT_ERRORS, count($db_errors)) . "<br />";
			$count_errors = 0;
			foreach ($db_errors as $db_error)
			{
				$count_errors++;
				if ($count_errors > 10)
				{
					break;
				}
				nbf_globals::$message .= "<br />" . $db_error;
			}
		}

		if (count($client_ids) > 0)
		{
			nbf_common::fire_event("users_imported", array("user_ids"=>"", "client_ids"=>implode(",", $client_ids)));
		}
	}
}

function showClientCSVHelp()
{
    $nb_database = nbf_cms::$interop->database;
    $sql = "SHOW COLUMNS FROM #__nbill_entity";
    $nb_database->setQuery($sql);
    $colnames = $nb_database->loadResultArray();
    $sql = "SHOW COLUMNS FROM #__nbill_contact";
    $nb_database->setQuery($sql);
    $colnames = array_unique(array_merge($colnames, $nb_database->loadResultArray()));
    $sql = "SHOW COLUMNS FROM #__nbill_entity_contact";
    $nb_database->setQuery($sql);
    $colnames = array_unique(array_merge($colnames, $nb_database->loadResultArray()));
    $colnames = array_filter($colnames, "remove_unwanted_columns");

    //$colnames = get_object_vars($nb_database->load_record("client"));
	echo "<div style=\"padding:10px;\">";
	echo "<h3 style=\"color:#cc0000;\">" . NBILL_IMPORT_CLIENTS_CSV_HELP_TITLE . "</h3>";
	echo "<p>" . NBILL_IMPORT_CLIENTS_CSV_HELP_TEXT_1 . "</p>";
	echo "<ul>";
	foreach ($colnames as $colname)
	{
		if (substr($colname, 0, 1) != "_" && $colname != "is_client" && $colname != "is_supplier")
		{
			echo "<li>$colname</li>";
		}
	}
	echo "<li>username</li>";
	echo "<li>password</li>";
    echo "<li>contact_id</li>";
	echo "</ul>";
	echo "<p>" . NBILL_IMPORT_CLIENTS_CSV_HELP_TEXT_2 . "</p>";
	echo "<p>" . NBILL_IMPORT_CLIENTS_CSV_HELP_TEXT_3 . "</p>";
	echo "<p>" . NBILL_IMPORT_CLIENTS_CSV_HELP_TEXT_4 . "</p>";
	echo "<p>" . NBILL_IMPORT_CLIENTS_CSV_HELP_TEXT_5 . "</p>";
	echo "</div>";
	echo "<div align=\"center\" style=\"text-align:center;\"><a href=\"javascript:window.close();\">" . NBILL_CLOSE_WINDOW . "</a></div>";
}

function remove_unwanted_columns($value)
{
    switch ($value)
    {
        case "contact_id":
        case "entity_id":
            return false;
        default:
            return true;
    }
}

function exportAllClients()
{
	$nb_database = nbf_cms::$interop->database;

    ///RSW TODO: Split this into chunks so we don't use up too much memory

	$sql = "SELECT #__nbill_entity.*, #__nbill_contact.id AS contact_id, #__nbill_contact.user_id, #__nbill_contact.first_name, #__nbill_contact.last_name,
            #__nbill_contact.address_1, #__nbill_contact.address_2, #__nbill_contact.address_3, #__nbill_contact.town, #__nbill_contact.state,
            #__nbill_contact.country, #__nbill_contact.postcode, #__nbill_contact.email_address, #__nbill_contact.email_address_2,
            #__nbill_contact.telephone, #__nbill_contact.telephone_2, #__nbill_contact.mobile, #__nbill_contact.fax, #__nbill_contact.notes,
            #__nbill_contact.custom_fields, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS `name`,
            `allow_update`, `allow_orders`, `allow_invoices`, `allow_quotes`, `allow_purchase_orders`, `email_invoice_option`, `reminder_emails`, `allow_reminder_opt_in`
            FROM #__nbill_entity
            LEFT JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id
            LEFT JOIN #__nbill_entity_contact ON #__nbill_entity.id = #__nbill_entity_contact.entity_id AND #__nbill_contact.id = #__nbill_entity_contact.contact_id
            WHERE #__nbill_entity.is_client = 1";
	$nb_database->setQuery($sql);
	$clients = $nb_database->loadObjectList();
	if (!$clients)
	{
		nbf_globals::$message = NBILL_NO_CLIENTS_FOUND;
		ioClients(true);
		return;
	}

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

	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=\"clients_" . nbf_common::nb_date("Y-m-d_h-i-s", nbf_common::nb_time()) . ".csv\"");

	$csv = "";
	$titles = array();
	foreach ($clients as $client)
	{
		if (count($titles) == 0)
		{
			$titles = get_object_vars($client);
			foreach ($titles as $title=>$value)
			{
				if (nbf_common::nb_strlen($csv) > 0)
				{
					$csv .= ",";
				}
				$csv .= $title;
			}
			echo $csv . "\r\n";
			$csv = "";
		}

		foreach ($titles as $title=>$value)
		{
			if (nbf_common::nb_strlen($csv) > 0)
			{
				$csv .= ",";
			}
			$data = $client->$title;
			$data = str_replace("\"", "``", $data);
			if (nbf_common::nb_strpos($data, ",") !== false || nbf_common::nb_strpos($data, "\n") !== false)
			{
				$data = "\"" . $data . "\"";
			}
			else
			{
				$data = $client->$title;
			}
			$csv .= $data;
		}
		echo $csv . "\r\n";
		$csv = "";
	}

	exit();
}