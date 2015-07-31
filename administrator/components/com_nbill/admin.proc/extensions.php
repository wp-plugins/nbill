<?php
/**
* Main processing file for nBill extensions installer
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');if (nbf_cms::$interop->demo_mode)
{
    echo "Sorry, extensions cannot be installed or uninstalled in the demo version for security reasons.";
    return;
}

$nb_database = nbf_cms::$interop->database;
include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.file.class.php");

switch ($task)
{
	case "silent":
        break;
    case "uninstall":
	case "uninstall_upgrade":
		if (!is_array($cid))
		{
			$id = $cid;
			$cid = array();
			$cid[] = $id;
		}
		uninstall_extension($cid, $task == "uninstall_upgrade");
		//No break - fall through...
	default:
		if (isset($_FILES['zipfile']) && $_FILES['zipfile']['size'] > 0)
		{
            //Upload
            nbf_globals::$message = '';
            $file_name = nbf_cms::$interop->site_temp_path . "/" . $_FILES['zipfile']['name'];
            move_uploaded_file($_FILES['zipfile']['tmp_name'], $file_name);
			$fail_reason = "";
			include_once(nbf_cms::$interop->nbill_admin_base_path . "/dunzip2.inc.php");
			$zip = new dUnzip2($file_name);
			$list = $zip->getList();
			$install_file = "";
			foreach($list as $fileName=>$zippedFile)
			{
				if (substr($fileName, nbf_common::nb_strlen($fileName) - 4) == ".nbe")
				{
					$install_file = $fileName;
					break;
				}
			}

			$title = "";
			$extension_name = "";
			$orig_extension_name = "";
			$file_path_admin = "";
			$file_path_frontend = "";
			$nbill_min_version = "";
			$nbill_min_sp = "";
			$language_file = "";
			$setup_file = "";
			$english_language_file = ""; //In case language pack is not installed for live language
            $upgrade = false;
            $install_settings = array();

			if (nbf_common::nb_strlen($install_file) > 0)
			{
				$install_contents = explode("\n", str_replace("\r\n", "\n", $zip->unzip($install_file)));
				$valid_install_file = parse_setup_file($install_contents, $install_settings, $fail_reason);

				$title = $install_settings['title'][0];
                $orig_extension_name = $install_settings['name'][0];
				$extension_name = nbf_common::nb_strtolower($install_settings['name'][0]);

				if ($valid_install_file)
				{
					switch ($install_settings['type'][0])
					{
						case "gateway":
							//Make sure all settings are present and correct
							if (!isset($install_settings['file']) || !isset($install_settings['admin_file'])
									|| !isset($install_settings['gateway_parameter']))
							{
								$valid_install_file = false;
								$fail_reason = "Extension type is gateway, but one or more mandatory settings not found (file, admin_file, gateway_parameter)";
							}
							else
							{
								//Make sure all parameters contain all the necessary info
								foreach ($install_settings['gateway_parameter'] as $param)
								{
									if (!isset($param->key) || !isset($param->value) || !isset($param->label)
											|| !isset($param->help) || !isset($param->required)
											|| !isset($param->editable))
									{
										$valid_install_file = false;
										$fail_reason = "A gateway_parameter setting was found, but not all mandatory parts were present (value, label, help, required, editable) - this can happen if you have a hash or double-slash in the data but did not use an escape character or if you omit a semi-colon separator";
									}
								}
							}
							if ($valid_install_file)
							{
								//Add the gateway parameters to the database
								if ($valid_install_file && nbf_common::nb_strlen(nbf_globals::$message) == 0)
								{
									//Use this to check for existing values
									$sql_part1 = "SELECT id FROM #__nbill_payment_gateway
													WHERE gateway_id = '$extension_name'
													AND g_key = '";

									//Get next ordering number
									$sql = "SELECT ordering FROM #__nbill_payment_gateway
													WHERE gateway_id = '$extension_name' ORDER BY ordering DESC";
									$nb_database->setQuery($sql);
									$ordering = $nb_database->loadResult();
									if (!$ordering)
									{
										$ordering = 0;
									}
									$ordering++;

									//Insert description record in DB (if not already there)
									$sql = $sql_part1 . "gateway_description'";
									$nb_database->setQuery($sql);
									if (!$nb_database->loadResult())
									{
										$sql = "INSERT INTO #__nbill_payment_gateway (gateway_id, g_key, g_value, label, help_text, required, admin_can_edit, ordering)
														VALUES ('$extension_name', 'gateway_description', '" . $install_settings['description'][0] . "', '', '', 0, 0, $ordering)";
										$nb_database->setQuery($sql);
										$nb_database->query();
                                        if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                                        {
                                            nbf_globals::$message = sprintf(NBILL_EXTENSION_DB_ERROR, $nb_database->_errorMsg);
                                        }
										$ordering++;
									}

									//Insert any values defined in setup file (if not already there)
									foreach ($install_settings['gateway_parameter'] as $parameter)
									{
										$sql = $sql_part1 . $parameter->key . "'";
										$nb_database->setQuery($sql);
										if (!$nb_database->loadResult())
										{
                                            $parameter->value = str_replace("index.php?option=com_netinvoice", nbf_cms::$interop->site_page_prefix, $parameter->value);
											$sql = "INSERT INTO #__nbill_payment_gateway (gateway_id, g_key, g_value, label, help_text, required, admin_can_edit, ordering, data_type, options)
												    VALUES ('$extension_name', '" . @$parameter->key . "', '" . @$parameter->value . "', '" . @$parameter->label . "',
                                                    '" . @$parameter->help  ."', " . @$parameter->required . ", " . @$parameter->editable . ", $ordering,
                                                    '" . @$parameter->data_type . "', '" . @$parameter->options . "')";
											$nb_database->setQuery($sql);
											$nb_database->query();
                                            if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                                            {
                                                nbf_globals::$message = sprintf(NBILL_EXTENSION_DB_ERROR, $nb_database->_errorMsg);
                                            }
											$ordering++;
										}
									}
								}

								//Create directory structure
								$file_path_admin = nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/";
								$file_path_frontend = nbf_cms::$interop->nbill_fe_base_path . "/gateway/";
                                if (file_exists($file_path_admin . "admin.$extension_name"))
                                {
                                    $upgrade = true;
                                }
                                @mkdir($file_path_admin . "admin.$extension_name");
                                @mkdir($file_path_frontend . $extension_name);

								if (!file_exists($file_path_admin . "admin.$extension_name"))
								{
									nbf_globals::$message = sprintf(NBILL_EXTENSION_COULD_NOT_CREATE_DIR, $file_path_admin . "admin.$extension_name");
								}
								else
								{
									if (!file_exists($file_path_frontend . $extension_name))
									{
										nbf_globals::$message = sprintf(NBILL_EXTENSION_COULD_NOT_CREATE_DIR, $file_path_frontend . $extension_name);
									}
								}
							}
							break;
						case "language":
							//Make sure all settings are present and correct
							if (!isset($install_settings['admin_file']))
							{
								$valid_install_file = false;
								$fail_reason = "Extension type is language, but no admin_file setting was found";
							}
							if ($valid_install_file)
							{
								//Execute any database queries
								if (isset($install_settings['query']))
								{
									foreach ($install_settings['query'] as $sql)
									{
										$nb_database->setQuery($sql);
										$nb_database->query();
                                        if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                                        {
                                            nbf_globals::$message = sprintf(NBILL_EXTENSION_DB_ERROR, $nb_database->_errorMsg);
                                        }
									}
								}

								//Create directory structure
								$file_path_admin = nbf_cms::$interop->nbill_admin_base_path . "/language/";
                                if (file_exists($file_path_admin . $orig_extension_name))
                                {
                                    $upgrade = true;
                                }
                                @mkdir($file_path_admin . $orig_extension_name);
								if (!file_exists($file_path_admin . $orig_extension_name))
								{
									nbf_globals::$message = sprintf(NBILL_EXTENSION_COULD_NOT_CREATE_DIR, $file_path_admin . $orig_extension_name);
								}
							}
							break;
						case "feature":
                            
                                if (!isset($install_settings['lite_feature']) || count($install_settings['lite_feature']) == 0 || strtolower($install_settings['lite_feature'][0]) != 'yes') {
                                    nbf_globals::$message = "Sorry, this extension will not work in " . NBILL_BRANDING_NAME . ", only in the standard edition.";
                                
                            } else {
							    $file_path_admin = nbf_cms::$interop->nbill_admin_base_path . "/";
							    $file_path_frontend = nbf_cms::$interop->nbill_fe_base_path . "/";

                                $setup_file = "extensions/" . basename($install_file);
                                if (strlen(dirname($file_path_admin . $setup_file)) > 1 && file_exists($file_path_admin . $setup_file))
                                {
                                    $upgrade = true;
                                }

							    if (isset($install_settings['extension_menu']))
							    {
								    foreach ($install_settings['extension_menu'] as $ext_menu)
								    {
									    if (!isset($ext_menu->key) || !isset($ext_menu->parent_id)
											    || !isset($ext_menu->main_menu_parent_id)
											    || !isset($ext_menu->ordering)
											    || !isset($ext_menu->text)
											    || !isset($ext_menu->published))
									    {
										    $valid_install_file = false;
										    $fail_reason = "An extension_menu setting was found, but not all mandatory parts were present (parent_id, main_mneu_parent_id, ordering, text, published) - this can happen if you used a hash or double-slash in the data but did not include an escape character or if you omit a semi-colon separator";
										    break;
									    }
									    else
									    {
                                            //Do not allow more than one entry with the same 'ordering'
                                            $this_ordering = $ext_menu->ordering;
                                            $ordering_ok = true;
                                            $loop_counter = 0;
                                            do
                                            {
                                                $loop_counter++;
                                                $sql = "SELECT id FROM #__nbill_extensions_menu WHERE id != '$ext_menu->key' AND parent_id = '" . $ext_menu->parent_id . "' AND main_menu_parent_id = '" . $ext_menu->main_menu_parent_id . "' AND ordering = " . $this_ordering;
                                                $nb_database->setQuery($sql);
                                                if ($nb_database->loadResult())
                                                {
                                                    $ordering_ok = false;
                                                    $this_ordering++;
                                                }
                                                else
                                                {
                                                    $ordering_ok = true;
                                                }
                                            }
                                            while (!$ordering_ok && $loop_counter < 100);

										    $sql = "REPLACE INTO #__nbill_extensions_menu
													    (id, parent_id, main_menu_parent_id, ordering, text,
													    description, image, url, published, extension_name)
													    VALUES
													    ('$ext_menu->key', '$ext_menu->parent_id',
													    $ext_menu->main_menu_parent_id, $this_ordering,
													    '$ext_menu->text', '" . @$ext_menu->description . "',
													    '" . @$ext_menu->image . "', '" . @$ext_menu->url . "',
													    $ext_menu->published,
													    '" . nbf_common::nb_strtolower($install_settings['name'][0]) . "')";
										    $nb_database->setQuery($sql);
										    $nb_database->query();
                                            if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                                            {
                                                nbf_globals::$message = sprintf(NBILL_EXTENSION_DB_ERROR, $nb_database->_errorMsg);
                                            }
									    }
								    }
							    }
                            }
							break;
					}

					//Copy the files
					if ($valid_install_file && nbf_common::nb_strlen(nbf_globals::$message) == 0)
					{
						if (isset($install_settings['admin_file']) && count($install_settings['admin_file']) > 0)
						{
							foreach ($install_settings['admin_file'] as $admin_file)
							{
                                $dest_file = $file_path_admin . $admin_file;
								if (basename($admin_file) == $extension_name . "." . nbf_cms::$interop->language . ".php" || basename($admin_file) == $extension_name . ".en-GB.php")
								{
									$language_file = $dest_file;
								}
								if (basename($admin_file) == $extension_name . ".en-GB.php" || basename($admin_file) == $extension_name . "_english.php")
								{
									$english_language_file = $dest_file;
								}
                                if (strlen(dirname($dest_file)) > 1 && !file_exists(dirname($dest_file)))
                                {
                                    nbf_file::nb_mkdir_recursive(dirname($dest_file));
                                }

                                if (!file_exists(dirname($dest_file)))
                                {
                                    nbf_file::nb_mkdir_recursive(dirname($dest_file));
                                }

                                $hold_file_path_admin = $file_path_admin;
                                if (substr($admin_file, 0, 3) == "../")
                                {
                                    $file_path_admin = realpath($file_path_admin . "../");
                                    $admin_file = substr($admin_file, 3);
                                    $dest_file = $file_path_admin . "/" . $admin_file;
                                }
                                set_convert_utf8($dest_file);
								$zip->unzip($admin_file, $dest_file);

                                $file_path_admin = $hold_file_path_admin;

								if (!file_exists($dest_file))
								{
									if (nbf_common::nb_strpos($dest_file, "/language/") === false) //Doesn't matter if language file is not present
									{
										nbf_globals::$message = sprintf(NBILL_EXTENSION_COULD_NOT_CREATE_FILE, $dest_file);
										break;
									}
								}
                                else
                                {
                                    //If legacy gateway, replace any page prefix values
                                    if ($install_settings['type'][0] == "gateway")
                                    {
                                        $file_contents = @file_get_contents($dest_file);
                                        $file_amended = false;
                                        if (nbf_common::nb_strpos($file_contents, "com_netinvoice") !== false)
                                        {
                                            $file_contents = str_replace("com_netinvoice", "com_nbill", $file_contents);
                                            $file_amended = true;
                                        }
                                        if (nbf_common::nb_strpos($file_contents, "#__inv_") !== false)
                                        {
                                            $file_contents = str_replace("#__inv_", "#__nbill_", $file_contents);
                                            $file_amended = true;
                                        }
                                        if ($file_amended)
                                        {
                                            @file_put_contents($dest_file, $file_contents);
                                        }
                                        unset($file_contents);
                                    }
                                }
							}
						}
						if (nbf_common::nb_strlen(nbf_globals::$message) == 0)
						{
							if (isset($install_settings['file']) && count($install_settings['file']) > 0)
							{
								foreach ($install_settings['file'] as $file)
								{
									$dest_file = $file_path_frontend . $file;
									if (strlen(dirname($dest_file)) > 1 && !file_exists(dirname($dest_file)))
                                    {
                                        nbf_file::nb_mkdir_recursive(dirname($dest_file));
                                    }
                                    $hold_file_path_frontend = $file_path_frontend;
                                    if (substr($file, 0, 3) == "../")
                                    {
                                        $file_path_frontend = realpath($file_path_frontend . "../");
                                        $file = substr($file, 3);
                                        $dest_file = $file_path_frontend . '/' . $file;
                                    }
									set_convert_utf8($dest_file);
                                    $zip->unzip($file, $dest_file);
                                    $file_path_frontend = $hold_file_path_frontend;
									if (!file_exists($dest_file))
									{
										nbf_globals::$message = sprintf(NBILL_EXTENSION_COULD_NOT_CREATE_FILE, $dest_file);
										break;
									}
                                    else
                                    {
                                        //If legacy gateway, replace any page prefix values
                                        if ($install_settings['type'][0] == "gateway")
                                        {
                                            $file_contents = @file_get_contents($dest_file);
                                            $file_amended = false;
                                            if (nbf_common::nb_strpos($file_contents, "com_netinvoice") !== false)
                                            {
                                                $file_contents = str_replace("com_netinvoice", "com_nbill", $file_contents);
                                                $file_amended = true;
                                            }
                                            if (nbf_common::nb_strpos($file_contents, "#__inv_") !== false)
                                            {
                                                $file_contents = str_replace("#__inv_", "#__nbill_", $file_contents);
                                                $file_amended = true;
                                            }
                                            if ($file_amended)
                                            {
                                                @file_put_contents($dest_file, $file_contents);
                                            }
                                            unset($file_contents);
                                        }
                                    }
								}
							}
						}

						if ($valid_install_file && nbf_common::nb_strlen(nbf_globals::$message) == 0)
						{
							//Copy the setup file
							$setup_file = basename($install_file);
							if ($install_settings['type'][0] == "feature")
							{
								$setup_file = "extensions/$setup_file";
							}
                            if (strlen(dirname($file_path_admin . $setup_file)) > 1 && !file_exists(dirname($file_path_admin . $setup_file)))
                            {
                                nbf_file::nb_mkdir_recursive(dirname($file_path_admin . $setup_file));
                            }
							nbf_globals::$convert_utf8 = false; //It will be converted back again on uninstall, if required
							$zip->unzip($install_file, $file_path_admin . $setup_file);

                            //Execute any database queries
                            if (isset($install_settings['query']))
                            {
                                foreach ($install_settings['query'] as $sql)
                                {
                                    $nb_database->setQuery($sql);
                                    $nb_database->query();
                                    if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                                    {
                                        nbf_globals::$message = sprintf(NBILL_EXTENSION_DB_ERROR, $nb_database->_errorMsg);
                                    }
                                }
                            }

							//If there is an install file, execute it...
							switch ($install_settings['type'][0])
							{
								case "gateway":
									$install_file_name = $file_path_admin . "admin.$extension_name/$extension_name.install.php";
									break;
								case "language":
									$install_file_name = $file_path_admin . $orig_extension_name . "/$orig_extension_name.install.php";
									break;
								case "feature":
									$install_file_name = $file_path_admin . "extensions/$extension_name.install.php";
									break;
							}
							if (file_exists($install_file_name))
							{
								include_once($install_file_name);
							}

                            //If there is an upgrade file, and we are upgrading, execute it...
                            if ($upgrade)
                            {
                                $upgrade_file_name = "";
                                switch ($install_settings['type'][0])
                                {
                                    case "gateway":
                                        $upgrade_file_name = $file_path_admin . "admin.$extension_name/$extension_name.upgrade.php";
                                        break;
                                    case "language":
                                        $upgrade_file_name = $file_path_admin . $orig_extension_name . "/$orig_extension_name.upgrade.php";
                                        break;
                                    case "feature":
                                        $upgrade_file_name = $file_path_admin . "extensions/$extension_name.upgrade.php";
                                        break;
                                }
                                if (file_exists($upgrade_file_name))
                                {
                                    include_once($upgrade_file_name);
                                }
                            }
						}
					}
				}

				if (!$valid_install_file)
				{
					nbf_globals::$message = sprintf(NBILL_EXTENSION_INVALID_INSTALL_FILE, basename($install_file));
					if (nbf_common::nb_strlen($fail_reason) > 0)
					{
						nbf_globals::$message .= "<br /><br />DEBUG INFORMATION: $fail_reason";
					}
				}

			}
			else
			{
				nbf_globals::$message = NBILL_EXTENSION_NO_INSTALL_FILE;
			}
            @unlink($file_name);

			if (nbf_common::nb_strlen(nbf_globals::$message) == 0)
			{
				//Extension installed ok - save details in db so we can uninstall if required
                if ($upgrade)
                {
                    $sql = "SELECT id FROM #__nbill_extensions WHERE extension_name = '" . $nb_database->getEscaped($extension_name) . "'";
                    $nb_database->setQuery($sql);
                    $extension_id = intval($nb_database->loadResult());
                }
                $sql = $upgrade && $extension_id ? "REPLACE" : "INSERT";
				$sql .= " INTO #__nbill_extensions (" . ($upgrade && $extension_id ? "id, " : "") . "extension_type, extension_name, extension_title, extension_description,
								extension_date, date_installed, version, copyright, author_name, author_email,
								author_website, file_path_admin, file_path_frontend, setup_filename, gateway_id) VALUES
								(" . ($upgrade && $extension_id ? $extension_id . ", " : "") . "
                                '" . $nb_database->getEscaped(nbf_common::nb_strtolower($install_settings['type'][0])) . "', "
								. "'" . $nb_database->getEscaped($install_settings['name'][0]) . "', "
								. "'" . $nb_database->getEscaped($install_settings['title'][0]) . "', "
								. "'" . $nb_database->getEscaped($install_settings['description'][0]) . "', '" .
								$nb_database->getEscaped(@$install_settings['date'][0]) . "', "	. nbf_common::nb_time() . ", " .
								"'" . $nb_database->getEscaped(@$install_settings['version'][0]) . "', " .
								"'" . $nb_database->getEscaped(@$install_settings['copyright'][0]) . "', "
								. "'" . $nb_database->getEscaped(@$install_settings['author'][0]) . "', " .
								"'" . $nb_database->getEscaped(@$install_settings['author_email'][0]) . "', "
								. "'" . $nb_database->getEscaped(@$install_settings['author_url'][0]) . "', " .
								"'" . $nb_database->getEscaped($file_path_admin) . "', " .
								"'" . $nb_database->getEscaped($file_path_frontend) . "', " .
								"'" . $nb_database->getEscaped($setup_file) . "', " .
								"'" . $nb_database->getEscaped($extension_name) . "')";
				$nb_database->setQuery($sql);
				$nb_database->query();
				if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                {
                    nbf_globals::$message = sprintf(NBILL_EXTENSION_DB_ERROR, $nb_database->_errorMsg);
                }

				//For Payment Gateways, add the display name to the config table
				if ($install_settings['type'][0] == "gateway")
				{
                    $sql = "SELECT gateway_id FROM #__nbill_payment_gateway_config WHERE gateway_id = '" . $nb_database->getEscaped($extension_name) . "'";
                    $nb_database->setQuery($sql);
                    if (!$nb_database->loadResult())
                    {
                        //Find next ordering number
                        $sql = "SELECT ordering FROM #__nbill_payment_gateway_config ORDER BY ordering DESC LIMIT 1";
                        $nb_database->setQuery($sql);
                        $ordering = intval($nb_database->loadResult()) + 1;
					    $sql = "INSERT INTO #__nbill_payment_gateway_config (gateway_id, display_name, ordering, published)
									    VALUES ('" . $nb_database->getEscaped($extension_name) . "', '" . $nb_database->getEscaped($title) . "', $ordering, 1)";
					    $nb_database->setQuery($sql);
					    $nb_database->query();
                        if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                        {
                            nbf_globals::$message = sprintf(NBILL_EXTENSION_DB_ERROR, $nb_database->_errorMsg);
                        }
                    }

                    //Replace INV_ with NBILL_ for language file items on any parameters that were left behind by the previous version, if applicable
                    $sql = "UPDATE #__nbill_payment_gateway SET label = REPLACE(label, 'INV_', 'NBILL_') WHERE gateway_id = '" . $nb_database->getEscaped($extension_name) . "' AND label LIKE 'INV_%'";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                    $sql = "UPDATE #__nbill_payment_gateway SET help_text = REPLACE(help_text, 'INV_', 'NBILL_') WHERE gateway_id = '" . $nb_database->getEscaped($extension_name) . "' AND help_text LIKE 'INV_%'";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                    $sql = "UPDATE #__nbill_payment_gateway SET g_value = REPLACE(g_value, 'INV_', 'NBILL_') WHERE gateway_id = '" . $nb_database->getEscaped($extension_name) . "' AND g_value LIKE 'INV_%'";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
				}

				//Load language file, if applicable
				if (file_exists($language_file))
				{
                    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.backward.compatibility.php");
					include_once($language_file);
				}
				else if (file_exists($english_language_file))
				{
                    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.backward.compatibility.php");
					include_once($english_language_file);
				}

				$description = @constant($install_settings['description'][0]);
				if (nbf_common::nb_strlen($description) == 0)
				{
					$description = $install_settings['description'][0];
				}

				nbf_globals::$message = "<p align=\"left\">" . sprintf($upgrade ? NBILL_EXTENSION_UPGRADED : NBILL_EXTENSION_INSTALLED, $title) . "<br /><br />" . $description . "</p>";
				nBillExtensions::showSuccess(nbf_globals::$message);
				return;
			}
			else
			{
				//For new installs, rollback any parts of the installation that were successful, if poss
                if (!$upgrade)
                {
				    switch ($install_settings['type'][0])
				    {
					    case "gateway":
						    if (nbf_common::nb_strlen($extension_name) > 0)
						    {
							    if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/" . $extension_name . "/"))
							    {
								    nbf_file::remove_directory(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/" . $extension_name . "/");
							    }
							    if (nbf_common::nb_strlen($setup_file) > 0 && file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/" . basename($setup_file)))
							    {
								    unlink(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/" . basename($setup_file));
							    }
						    }
						    break;
					    case "language":
						    if (nbf_common::nb_strlen($orig_extension_name) > 0)
						    {
							    if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/language/" . $orig_extension_name . "/"))
							    {
								    nbf_file::remove_directory(nbf_cms::$interop->nbill_admin_base_path . "/language/" . $orig_extension_name . "/");
							    }
							    if (nbf_common::nb_strlen($setup_file) > 0 && file_exists(nbf_cms::$interop->nbill_admin_base_path . "/language/" . basename($setup_file)))
							    {
								    unlink(nbf_cms::$interop->nbill_admin_base_path . "/language/" . basename($setup_file));
							    }
						    }
						    break;
					    case "feature":
						    if (nbf_common::nb_strlen($extension_name) > 0)
						    {
							    if (nbf_common::nb_strlen($setup_file) > 0 && file_exists(nbf_cms::$interop->nbill_admin_base_path . "/extensions/" . basename($setup_file)))
							    {
								    unlink(nbf_cms::$interop->nbill_admin_base_path . "/extensions/" . basename($setup_file));
							    }
							    if (isset($install_settings['admin_file']) && count($install_settings['admin_file']) > 0)
							    {
								    foreach ($install_settings['admin_file'] as $admin_file)
								    {
									    if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/" . $admin_file))
									    {
										    unlink(nbf_cms::$interop->nbill_admin_base_path . "/" . $admin_file);
									    }
								    }
							    }
							    if (isset($install_settings['file']) && count($install_settings['file']) > 0)
							    {
								    foreach ($install_settings['file'] as $file)
								    {
									    if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/" . $file))
									    {
										    unlink(nbf_cms::$interop->nbill_fe_base_path . "/" . $file);
									    }
								    }
							    }
							    $sql = "DELETE FROM #__nbill_extensions_menu WHERE extension_name = '$extension_name'";
							    $nb_database->setQuery($sql);
							    $nb_database->query();
						    }
						    break;
				    }

				    //Execute any undo queries
				    if (isset($install_settings['undo_query']) && count($install_settings['undo_query']) > 0)
				    {
					    foreach ($install_settings['undo_query'] as $undo_query)
					    {
						    $nb_database->setQuery($undo_query);
						    $nb_database->query();
					    }
				    }
                }
			}
		}

		$query = "SELECT count(*) FROM #__nbill_extensions";
		$nb_database->setQuery( $query );
		$total = $nb_database->loadResult();

		//Add page navigation
		$pagination = new nbf_pagination("extension", $total);

		//Load the records
		$sql = "SELECT * FROM #__nbill_extensions ORDER BY extension_name";
		$nb_database->setQuery($sql);
		$rows = $nb_database->loadObjectList();
		if (!$rows)
		{
			$rows = array();
		}

		$date_format = nbf_common::get_date_format();
		nBillExtensions::showInstaller($rows, $pagination, $date_format);
		break;
}

function uninstall_extension($id_array, $skip_db = true)
{
	$nb_database = nbf_cms::$interop->database;
	$fail_reason = "";
    $remove_completely = !$skip_db; //So extension uninstall files know what to do

    require_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.file.class.php");

	//Check what type of extension
	$sql = "SELECT id, extension_type, extension_name, file_path_admin, file_path_frontend, setup_filename, gateway_id FROM #__nbill_extensions WHERE id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$ext_info = $nb_database->loadObjectList();
	if (!$ext_info)
	{
		$ext_info = array();
	}

	foreach ($ext_info as $extension)
	{
		//Load the setup file (at least to see if there are any undo queries to execute)
		$sql = "SELECT setup_filename FROM #__nbill_extensions WHERE id = $extension->id";
		$nb_database->setQuery($sql);
		$setup_filename = $nb_database->loadResult();

		if (!$skip_db && (nbf_common::nb_strlen($setup_filename) > 0 && file_exists($extension->file_path_admin . $setup_filename)))
		{
			$install_contents = explode("\n", str_replace("\r\n", "\n", file_get_contents($extension->file_path_admin . $setup_filename)));
			$install_settings = array();
			if (parse_setup_file($install_contents, $install_settings, $fail_reason))
			{
				if (isset($install_settings['undo_query']) && count($install_settings['undo_query']) > 0 && !$skip_db)
				{
					foreach ($install_settings['undo_query'] as $undo_query)
					{
						$nb_database->setQuery($undo_query);
						$nb_database->query();
					}
				}
			}
			else
			{
				echo $fail_reason;
			}
		}

		$uninstall_file_name = $extension->extension_name . ".uninstall.php";
		if ($extension->extension_type == "feature")
		{
			$uninstall_file_name = "extensions/$uninstall_file_name";
		}
		if (file_exists($extension->file_path_admin . $uninstall_file_name))
		{
			include($extension->file_path_admin . $uninstall_file_name);
		}
		else
		{
			$uninstall_file_name = "admin.$extension->extension_name/$extension->extension_name" . ".uninstall.php";
			if (file_exists($extension->file_path_admin . $uninstall_file_name))
			{
				include($extension->file_path_admin . $uninstall_file_name);
			}
		}

		switch (nbf_common::nb_strtolower($extension->extension_type))
		{
			case "language":
				if (nbf_file::remove_directory($extension->file_path_admin . $extension->extension_name . "/"))
				{
					nbf_globals::$message .= "<br />" . sprintf(NBILL_EXTENSION_REMOVED, $extension->extension_name);
				}
				else
				{
					nbf_globals::$message .= "<br />" . sprintf(NBILL_EXTENSION_NOT_REMOVED, $extension->extension_name);
				}
                if (strlen($setup_filename) > 0)
                {
				    @unlink($extension->file_path_admin . $setup_filename);
                }
				$sql = "DELETE FROM #__nbill_extensions WHERE id = " . $extension->id;
				$nb_database->setQuery($sql);
				$nb_database->query();
				break;
			case "gateway":
				nbf_common::load_language("gateway");
				$task = "include_only";
				include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/gateway.php");
				uninstallGateway($extension->gateway_id, $skip_db);
				if (nbf_common::nb_strlen(nbf_globals::$message) == 0)
				{
					nbf_globals::$message = sprintf(NBILL_EXTENSION_REMOVED, $extension->extension_name);
				}
				break;
			case "feature":
				//Read installation file to find out which files were added
                $install_contents = explode("\n", str_replace("\r\n", "\n", @file_get_contents($extension->file_path_admin . $setup_filename)));
                $install_settings = array();
                if (parse_setup_file($install_contents, $install_settings, $fail_reason))
                {
				    if (isset($install_settings['admin_file']) && count($install_settings['admin_file']) > 0)
				    {
					    foreach ($install_settings['admin_file'] as $admin_file)
					    {
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

				//Remove any menu items
				$sql = "DELETE FROM #__nbill_extensions_menu WHERE extension_name = '$extension->extension_name'";
				$nb_database->setQuery($sql);
				$nb_database->query();

                //Remove any form event hooks
                $sql = "DELETE FROM #__nbill_extension_form_events WHERE extension_name = '$extension->extension_name'";
                $nb_database->setQuery($sql);
                $nb_database->query();

                //If an empty extension folder exists, delete it
                if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/extensions/" . $extension->extension_name))
                {
                    @rmdir(nbf_cms::$interop->nbill_admin_base_path . "/extensions/" . $extension->extension_name);
                }

                //Delete setup file
				@unlink($extension->file_path_admin . $setup_filename);
				$sql = "DELETE FROM #__nbill_extensions WHERE id = " . $extension->id;
				$nb_database->setQuery($sql);
				$nb_database->query();

				nbf_globals::$message = sprintf(NBILL_EXTENSION_REMOVED, $extension->extension_name);
				break;
		}
	}
}

function parse_setup_file($install_contents, &$install_settings, &$fail_reason)
{
	$auto_convert = nbf_cms::$interop->char_encoding == "utf-8";

	$valid_install_file = true;
	for ($i = 0; $i < count($install_contents); $i++)
	{
		$line = $install_contents[$i];
		//Ignore comments
		if (substr($line, 0, 1) == "#" || substr($line, 0, 2) == "//" || nbf_common::nb_strlen(trim($line)) == 0)
		{
			continue;
		}

		//Get key/value pair
		$equals_pos = nbf_common::nb_strpos($line, "=");
		if ($equals_pos === false)
		{
			$valid_install_file = false;
			$fail_reason = "A non-blank line was encountered which does not contain an equals character - a key/value pair could not be parsed. (DATA=$line)";
			break;
		}
		$key = substr($line, 0, $equals_pos);
		$value = substr($line, $equals_pos + 1);

		$value = str_replace("\#", "@@@@@hash@@@@@", $value);
		$value = str_replace("\//", "@@@@@double_slash@@@@@", $value);
        $value = str_replace("\\[", "@@@@@open_square@@@@@", $value);
		$hash_pos = nbf_common::nb_strpos($value, "#");
		$slash_pos = nbf_common::nb_strpos($value, "//");
		$comment_pos = $hash_pos !== false && $slash_pos !== false ? min($hash_pos, $slash_pos) : max($hash_pos, $slash_pos);
		if ($comment_pos > 0)
		{
			$value = rtrim(substr($value, 0, $comment_pos));
		}

		//Check whether value continues onto next line
		if (nbf_common::nb_strpos($value, "[") !== false)
		{
			if (nbf_common::nb_strpos($value, "]", nbf_common::nb_strpos($value, "[")) !== false)
			{
				//All on the same line despite using braces
			}
			else
			{
				//Append each subsequent line until we meet a close bracket
				for($j = $i + 1; $j < count($install_contents); $j++)
				{
					if (nbf_common::nb_strlen($install_contents[$j]) > 0 && substr($install_contents[$j], 0, 1) != "#" && substr($install_contents[$j], 0, 2) != "//")
					{
						$install_contents[$j] = str_replace("\#", "@@@@@hash@@@@@", $install_contents[$j]);
						$install_contents[$j] = str_replace("\//", "@@@@@double_slash@@@@@", $install_contents[$j]);
                        $install_contents[$j] = str_replace("\\[", "@@@@@open_square@@@@@", $install_contents[$j]);
                        $install_contents[$j] = str_replace("\\]", "@@@@@close_square@@@@@", $install_contents[$j]);
						$hash_pos = nbf_common::nb_strpos($install_contents[$j], "#");
						$slash_pos = nbf_common::nb_strpos($install_contents[$j], "//");
						$comment_pos = $hash_pos !== false && $slash_pos !== false ? min($hash_pos, $slash_pos) : max($hash_pos, $slash_pos);
						if ($comment_pos > 0)
						{
							$value .= rtrim(substr($install_contents[$j], 0, $comment_pos));
						}
						else
						{
							$value .= $install_contents[$j];
						}
						if (nbf_common::nb_strpos($value, "]") !== false)
						{
							$i = $j;
							break;
						}
					}
				}
			}
			$value = str_replace("[", "", $value);
			$value = str_replace("]", "", $value);
		}
		$value = str_replace("@@@@@hash@@@@@", "#", $value);
		$value = str_replace("@@@@@double_slash@@@@@", "//", $value);
		$value = str_replace("@@@@@open_square@@@@@", "[", $value);
        $value = str_replace("@@@@@close_square@@@@@", "]", $value);
		if ($key == "Convert_ISO-8859-1_to_UTF-8")
		{
			switch ($value)
			{
				case "A":
					if ($auto_convert)
					{
						nbf_globals::$overall_convert_utf8 = true;
					}
					break;
				case "B":
					nbf_globals::$overall_convert_utf8 = true;
					break;
				default:
					nbf_globals::$overall_convert_utf8 = false;
					break;
			}
			if (nbf_globals::$overall_convert_utf8)
			{
				//Convert the setup file in memory
				for($x=0; $x<count($install_contents); $x++)
				{
					$install_contents[$x] = utf8_encode($install_contents[$x]);
				}
			}
		}

		//Check version compatibility
		if ($key == "nbill_version")
		{
			$nbill_min_version = new nbf_version($value);
            if ($nbill_min_version->compare(">", nbf_version::$nbill_version_no) || $nbill_min_version->compare("<", "2.0.0"))
            {
				$valid_install_file = false;
				$fail_reason = NBILL_EXTENSION_VERSION_INCOMPATIBLE;
			}
		}

		//If key is gateway parameter or menu item, get the breakdown from value
		if (($key == "gateway_parameter" || $key == "extension_menu") && nbf_common::nb_strpos($value, ";") !== false)
		{
			$obj_param = new stdClass();
			$param_settings = explode(";", $value);
			$obj_param->key = $param_settings[0];
			for ($k = 1; $k < count($param_settings); $k++)
			{
				$equals_pos = nbf_common::nb_strpos($param_settings[$k], "=");
				if ($equals_pos === false)
				{
					$valid_install_file = false;
					$fail_reason = "A gateway_parameter or extension_menu setting was encountered which did not contain an equals character. It was therefore not possible to separate the key from the value. (DATA=$value)";
					break;
				}
				$param_key = substr($param_settings[$k], 0, $equals_pos);
				$param_value = substr($param_settings[$k], $equals_pos + 1);
				$obj_param->$param_key = $param_value;
			}
			$value = $obj_param;
		}

		if (!isset($install_settings[$key]))
		{
			$install_settings[$key] = array(); //There might be several values for each key
		}
		$install_settings[$key][] = $value;
	}

    if (@$install_settings['type'][0] == 'gateway' && !$nbill_min_version->compare(">", nbf_version::$nbill_version_no))
    {
        //Legacy gateway extensions should run ok, so we will make an exception
        $valid_install_file = true;
        $fail_reason = "";
    }

	//Make sure the mandatory settings are present
	if (!key_exists("name", $install_settings) || !key_exists("type", $install_settings)
			|| !key_exists("title", $install_settings) || !key_exists("description", $install_settings))
	{
		$valid_install_file = false;
		$fail_reason = "One or more mandatory settings was not present (name, type, title, description)";
	}

    return $valid_install_file;
}

function set_convert_utf8($file_name)
{
	if (nbf_globals::$overall_convert_utf8)
	{
		switch (substr($file_name, nbf_common::nb_strrpos($file_name, ".")))
		{
			case ".txt":
			case ".php":
			case ".php3":
			case ".php4":
			case ".php5":
			case ".csv":
			case ".xml":
			case ".sql":
			case ".ini":
			case ".log":
			case ".htm":
			case ".html":
			case ".nbe":
				nbf_globals::$convert_utf8 = true;
				break;
			default:
				nbf_globals::$convert_utf8 = false;
				break;
		}
	}
	else
	{
		nbf_globals::$convert_utf8 = false;
	}
}