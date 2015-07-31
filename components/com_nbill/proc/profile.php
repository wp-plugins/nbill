<?php
/**
* Main processing file for user profile page
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Load core profile fields language file
nbf_common::load_language("core.profile_fields");

switch (nbf_common::get_param($_REQUEST, 'task'))
{
	case "update":
	default:
		edit_profile();
		break;
}

function edit_profile($use_posted_values = false, $entity_in_error = 0)
{
    $reload_page = false;
    $suppress_entity = false;
    if (nbf_cms::$interop->user->id)
    {
        require_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
        require_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.field.class.php");

        $nb_database = nbf_cms::$interop->database;

        //Get the profile fields
        $sql = "SELECT *, id AS option_field_id FROM #__nbill_profile_fields WHERE published = 1 AND field_type != 'OO' ORDER BY ordering";
        $nb_database->setQuery($sql);
        $profile_fields = $nb_database->loadObjectList();
        if (!$profile_fields)
        {
            $profile_fields = array();
        }

        $field_options = array();
        $sql_field_options = array();
        nbf_form_fields::load_field_options($profile_fields, "#__nbill_profile_fields_options", null, $field_options, $sql_field_options);

        //Load data from DB
        $username = "";
        $contact_data = null;
        $entity_data = array();

        $contact_data = nbf_frontend::load_contact_data();
        if ($contact_data->id)
        {
            $entity_data = nbf_frontend::load_entity_data($contact_data->id);
            //If this contact is associated with an entity, but does not have permission to edit, only show the contact mapped fields (otherwise it will create a new entity which is probably not desired)
            if (@$entity_data[0]->id === null)
            {
                $sql = "SELECT entity_id FROM #__nbill_entity_contact WHERE contact_id = " . intval($contact_data->id) . " AND allow_update = 0";
                $nb_database->setQuery($sql);
                if ($nb_database->loadResult())
                {
                    $suppress_entity = true;
                    $field_count = count($profile_fields);
                    for ($field_index = 0; $field_index < $field_count; $field_index++)
                    {
                        if (strlen($profile_fields[$field_index]->contact_mapping) == 0)
                        {
                            unset($profile_fields[$field_index]);
                        }
                    }
                    $profile_fields = array_values($profile_fields);
                }
            }
        }
        else
        {
            //Set the country and email option
            $new_entity = new stdClass();
            $new_entity->id = null;
            $sql = "SELECT vendor_country FROM #__nbill_vendor WHERE default_vendor = 1";
            $nb_database->setQuery($sql);
            $nb_database->loadObject($vendor);
            $new_entity->country = $nb_database->loadResult();
            $sql = "SELECT email_invoice_option FROM #__nbill_configuration WHERE id = 1";
            $nb_database->setQuery($sql);
            $new_entity->email_invoice_option = $nb_database->loadResult();
            $entity_data = array();
            $entity_data[] = $new_entity;
        }

        //Load list of languages
        $languages = nbf_cms::$interop->get_list_of_languages();

        //Check for form submission
	    if (nbf_common::get_param($_POST,'cancel'))
	    {
		    nbf_common::redirect(nbf_cms::$interop->process_url(nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . nbf_cms::$interop->site_page_suffix));
	    }
	    else if (nbf_common::get_param($_POST,'submit_entity_details'))
	    {
		    if (nbf_cms::$interop->user->id)
		    {
                $new_lang = "";
                nbill_fe_profile_custom::pre_submit();
                if (nbill_fe_profile_custom::submit())
                {
                    //Prepare arrays for binding form data to database
                    $contact_binding = array();
                    $entity_binding = array();
                    $contact_binding["user_id"] = $contact_data->user_id;
                    $contact_binding["id"] = $contact_data->id;

                    //Validate the fields and grab the values for binding
                    nbf_globals::$message = "";
                    $email = "";
                    $username = "";
                    $custom_contact_fields = array();
                    $custom_entity_fields = array();

                    foreach ($profile_fields as $user_field)
                    {
                        if ($user_field->contact_mapping == 'email_address' && isset($_POST['ctl_' . $user_field->name]))
                        {
                            $email = nbf_common::get_param($_POST, 'ctl_' . $user_field->name);
                            if (nbf_common::nb_strlen($username) > 0) {break;}
                        }
                        if ($user_field->contact_mapping == 'username' && isset($_POST['ctl_' . $user_field->name]))
                        {
                            $username = nbf_common::get_param($_POST, 'ctl_' . $user_field->name);
                            if (nbf_common::nb_strlen($email) > 0) {break;}
                            break;
                        }
                    }
                    foreach ($profile_fields as $field)
                    {
                        nbf_form_fields::register_value($field, array_filter($sql_field_options[$field->id]) + array_filter($field_options[$field->id]) + $sql_field_options[$field->id] + $field_options[$field->id]);
                        if (isset($_POST['ctl_' . $field->name]))
                        {
                            $field->value = nbf_common::get_param($_POST, 'ctl_' . $field->name);
                            //If mandatory, ensure we have a value
                            if ($field->required && nbf_common::nb_strlen($_POST['ctl_' . $field->name]) == 0)
                            {
                                nbf_globals::$fields_in_error[] = 'ctl_' . $field->name;
                                nbf_globals::$message = NBILL_ERR_MANDATORY_FIELD;
                                break;
                            }
                            //If confirmation required, make sure the 2 values are identical
                            if ($field->confirmation)
                            {
                                if (@$_POST['ctl_' . $field->name] !== @$_POST['ctl_' . $field->name . '_confirm'])
                                {
                                    //Abort!
                                    nbf_globals::$fields_in_error[] = 'ctl_' . $field->name;
                                    nbf_globals::$fields_in_error[] = 'ctl_' . $field->name . '_confirm';
                                    nbf_globals::$message = NBILL_VALUES_DONT_MATCH;
                                    break;
                                }
                            }
                            //Perform any field-type-specific validation
                            if (!nbf_form_fields::validate_field($field, array_filter($sql_field_options[$field->id]) + array_filter($field_options[$field->id]) + $sql_field_options[$field->id] + $field_options[$field->id]))
                            {
                                //Abort!
                                nbf_globals::$fields_in_error[] = 'ctl_' . $field->name;
                                break;
                            }
                            //Make sure the email address is not already registered
                            if ($field->contact_mapping == 'email_address' && nbf_common::get_param($_POST, 'ctl_' . $field->name) != $contact_data->email_address)
                            {
                                if (nbf_cms::$interop->email_in_use(nbf_common::get_param($_POST, 'ctl_' . $field->name)))
                                {
                                    nbf_globals::$fields_in_error[] = 'ctl_' . $field->name;
                                    nbf_globals::$message = NBILL_EMAIL_IN_USE;
                                    break;
                                }
                            }
                            if ($field->contact_mapping == 'username')
                            {
                                //If creating a user, ensure username valid and not in use
                                $username = nbf_common::get_param($_POST, 'ctl_' . $field->name);

                                //Make sure CMS is happy with this username
                                if (!nbf_cms::$interop->validate_username($username, $email == $username))
                                {
                                    nbf_globals::$fields_in_error[] = 'ctl_' . $field->name;
                                    nbf_globals::$message = NBILL_INVALID_USERNAME;
                                    break;
                                }
                                //Make sure there is not already a user with this name
                                if (nbf_cms::$interop->username_in_use($username))
                                {
                                    nbf_globals::$fields_in_error[] = 'ctl_' . $field->name;
                                    nbf_globals::$message = NBILL_USERNAME_IN_USE;
                                    break;
                                }
                            }
                            $this_entity_id = (nbf_common::nb_strlen($field->entity_mapping) > 0 && nbf_common::nb_strlen(nbf_common::get_param($_POST, 'ctl_' . $field->name, null, true)) > 0 && !array_key_exists('ctl_' . $field->name . '_entity_' . @$entity_data[0]->id, $_POST)) ? 'new' : 0;
                            if (count($entity_data) == 1)
                            {
                                $this_entity_id = $entity_data[0]->id ? $entity_data[0]->id : $this_entity_id;
                            }
                            nbf_form_fields::prepare_bindings($field, $custom_contact_fields, $contact_binding, $this_entity_id, $custom_entity_fields, $entity_binding, $_POST);
                        }
                        //Go through any entities that are listed separately
                        foreach ($entity_data as $entity)
                        {
                            if (isset($_POST['ctl_' . $field->name . '_entity_' . $entity->id]))
                            {
                                nbf_form_fields::register_value($field, array_filter($sql_field_options[$field->id]) + array_filter($field_options[$field->id]) + $sql_field_options[$field->id] + $field_options[$field->id], $field->name . '_entity_' . $entity->id, nbf_globals::$message);

                                $field->value = nbf_common::get_param($_POST, 'ctl_' . $field->name . '_entity_' . $entity->id);
                                //If mandatory, ensure we have a value
                                if ($field->required && nbf_common::nb_strlen($_POST['ctl_' . $field->name . '_entity_' . $entity->id]) == 0)
                                {
                                    nbf_globals::$fields_in_error[] = 'ctl_' . $field->name . '_entity_' . $entity->id;
                                    nbf_globals::$message = NBILL_ERR_MANDATORY_FIELD;
                                    $entity_in_error = $entity->id;
                                    break 2;
                                }
                                //If confirmation required, make sure the 2 values are identical
                                if ($field->confirmation)
                                {
                                    if (@$_POST['ctl_' . $field->name . '_entity_' . $entity->id] !== @$_POST['ctl_' . $field->name . '_entity_' . $entity->id . '_confirm'])
                                    {
                                        //Abort!
                                        nbf_globals::$fields_in_error[] = 'ctl_' . $field->name . '_entity_' . $entity->id;
                                        nbf_globals::$fields_in_error[] = 'ctl_' . $field->name . '_entity_' . $entity->id . '_confirm';
                                        nbf_globals::$message = NBILL_VALUES_DONT_MATCH;
                                        $entity_in_error = $entity->id;
                                        break 2;
                                    }
                                }
                                if (!nbf_form_fields::validate_field($field, array_filter($sql_field_options[$field->id]) + array_filter($field_options[$field->id]) + $sql_field_options[$field->id] + $field_options[$field->id], $field->name . '_entity_' . $entity->id, nbf_globals::$message))
                                {
                                    //Abort!
                                    nbf_globals::$fields_in_error[] = 'ctl_' . $field->name . '_entity_' . $entity->id;
                                    $entity_in_error = $entity->id;
                                    break 2;
                                }
                                //Store the value for binding
                                if ($field->entity_mapping)
                                {
                                    if (!isset($entity_binding[$entity->id]))
                                    {
                                        $entity_binding[$entity->id] = array();
                                        $entity_binding[$entity->id]['id'] = $entity->id;
                                        $entity_binding[$entity->id]['is_client'] = @$entity->is_client ? 1 : 0;
                                        $entity_binding[$entity->id]['is_supplier'] = @$entity->is_supplier ? 1 : 0;
                                    }
                                    if ($field->entity_mapping == "custom")
                                    {
                                        $custom_entity_fields[$field->name] = nbf_common::get_param($_POST, 'ctl_' . $field->name . '_entity_' . $entity->id);
                                        $entity_binding[$entity->id]['custom_fields'] = serialize($custom_entity_fields);
                                    }
                                    else if (isset($entity_binding[$entity->id][$field->entity_mapping]))
                                    {
                                        $entity_binding[$entity->id][$field->entity_mapping] .= ' ' . nbf_common::get_param($_POST, 'ctl_' . $field->name . '_entity_' . $entity->id);
                                    }
                                    else
                                    {
                                        $entity_binding[$entity->id][$field->entity_mapping] = nbf_common::get_param($_POST, 'ctl_' . $field->name . '_entity_' . $entity->id);
                                    }
                                }
                            }
                        }
                    }
                    //Bind language, if applicable
                    foreach ($entity_data as $entity)
                    {
                        if (isset($_POST['ctl_default_language_entity_' . $entity->id]))
                        {
                            $entity_binding[$entity->id]['default_language'] = nbf_common::get_param($_POST, 'ctl_default_language_entity_' . $entity->id);
                            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'ctl_default_language_entity_' . $entity->id)) > 0 && nbf_cms::$interop->language != nbf_common::get_param($_POST, 'ctl_default_language_entity_' . $entity->id))
                            {
                                $new_lang = $entity_binding[$entity->id]['default_language'];
                                $reload_page = true; //Have to refresh page to invoke new language
                            }
                        }
                    }

                    //If validation failed, abort
                    if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
                    {
                        //If entity was separate before, keep it separate now (even if it no longer needs to be) so that errors show up correctly
                        $separate_entity = false;
                        foreach ($profile_fields as $field)
                        {
                            foreach ($entity_data as $entity)
                            {
                                if (isset($_POST['ctl_' . $field->name . '_entity_' . $entity->id]))
                                {
                                    $separate_entity = true;
                                    break 2;
                                }
                            }
                        }

                        $_POST['submit_entity_details'] = false;
                        $_POST['force_separate_entity'] = $separate_entity;
                        edit_profile(true, $entity_in_error);
                        return;
                    }

                    //Need to check if any tax exemption codes have changed, so make a note of the old value before saving
                    $tax_exemption_code = array();
                    foreach ($entity_binding as $entity_item)
                    {
                        if ($entity_item['id'])
                        {
                            $sql = "SELECT tax_exemption_code FROM #__nbill_entity
                                    WHERE id = " . intval($entity_item['id']);
                            $nb_database->setQuery($sql);
                            $tax_exemption_code[$entity_item['id']] = $nb_database->loadResult();
                        }
                    }

                    //Remove password if it has not been specified
                    if (nbf_common::nb_strlen(nbf_common::get_param($contact_binding, 'password')) == 0 || nbf_common::get_param($contact_binding, 'password') == "**********")
                    {
                        unset($contact_binding['password']);
                    }

                    //Save the contact/entity data
                    $orig_username = nbf_cms::$interop->user->username;
                    if ($suppress_entity)
                    {
                        $entity_binding = array(); //This contact not allowed to update client record
                    }
                    nbf_form_fields::execute_bindings($contact_binding, $entity_binding, $username, $email, $tax_exemption_code, null);

                    if (nbf_common::nb_strlen($username) > 0 && $username != $orig_username)
                    {
                        nbf_cms::$interop->log_out_then_in_again(nbf_cms::$interop->user, nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . "&message=" . NBILL_DETAILS_SAVED . nbf_cms::$interop->site_page_suffix);
                    }
                    if (nbf_common::nb_strlen(nbf_globals::$message) == 0)
                    {
                        nbf_globals::$message = NBILL_DETAILS_SAVED;
                    }
                }
                nbill_fe_profile_custom::post_submit();
                if ($reload_page)
                {
                    nbf_common::redirect(nbf_cms::$interop->site_page_prefix . (strlen($new_lang) > 0 ? '&nbill_lang=' . $new_lang . '&lang=' . nbf_cms::$interop->get_cms_url_language_code($new_lang) : '') . nbf_cms::$interop->site_page_suffix);
                    exit;
                }
                else
                {
                    include_once(nbf_cms::$interop->nbill_fe_base_path . "/html/main.html.php");
                    include(nbf_cms::$interop->nbill_fe_base_path . "/proc/main.php");
                }
		    }
	    }
	    else
	    {
            //If this is a postback, call the process method on each field
            if (nbf_common::get_param($_REQUEST, 'postback'))
            {
                foreach ($profile_fields as $field)
                {
                    if (isset($_POST['ctl_' . $field->name]))
                    {
                        nbf_form_fields::process_field($field, array_filter($sql_field_options[$field->id]) + array_filter($field_options[$field->id]) + $sql_field_options[$field->id] + $field_options[$field->id]);
                    }
                    foreach ($entity_data as $entity)
                    {
                        if (isset($_POST['ctl_' . $field->name . '_entity_' . $entity->id]))
                        {
                            nbf_form_fields::process_field($field, array_filter($sql_field_options[$field->id]) + array_filter($field_options[$field->id]) + $sql_field_options[$field->id] + $field_options[$field->id], $field->name . '_entity_' . $entity->id);
                        }
                    }
                }
            }

            //Load xrefs
            $countries = nbf_xref::get_countries();
            $email_options_xref = nbf_xref::load_xref("email_invoice");

            if (!$use_posted_values)
            {
                //Map contact and entity data onto profile fields
                nbf_form_fields::map_contact_fields($contact_data, $profile_fields);
                nbf_form_fields::map_entity_fields($entity_data, $profile_fields, true);
            }
		    @nBillFrontEndProfile::edit_profile($profile_fields, $field_options, $sql_field_options, $contact_data, $entity_data, $countries, $email_options_xref, $entity_in_error, $languages);
	    }
    }
    else
    {
        nbf_globals::$message = NBILL_FORM_TIMEOUT;
    }
}