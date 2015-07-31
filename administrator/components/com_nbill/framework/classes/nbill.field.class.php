<?php
/**
* Class file just containing static method relating to form field handling.
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

nbf_common::load_language("form.editor");

/**
* Static functions for form fields
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_form_fields
{
    /**
    * Load the options for the given fields
    * @param array $fields Fields to load the options for
    * @param string $field_option_table Name of database table to load options from (core profile, or order form)
    * @param mixed $form_id ID of order form, if applicable
    * @param array $field_options Return value holding the manually defined field options
    * @param array $sql_field_options Return value holding the field options loaded using an SQL query
    */
    public static function load_field_options($fields, $field_option_table, $form_id, &$field_options, &$sql_field_options)
    {
        //Load the field options
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
        $nb_database = nbf_cms::$interop->database;
        $field_options = array();
        foreach ($fields as $field)
        {
            //If options are to be loaded from a table or SQL query, load them...
            $sql = "";
            $sql_field_options[$field->id] = array();
            $field_options[$field->id] = array();
            $xref_options = null;
            if ($field->xref == "nbill_sql_list")
            {
                $sql = $field->xref_sql;
                //Execute any code needed to retrieve the SQL
                $sql = nbf_common::parse_and_execute_code($sql);
                $nb_database->setQuery($sql);
                $xref_options = $nb_database->loadObjectList();
                if (!$xref_options)
                {
                    $xref_options = array();
                }
            }
            else if (nbf_common::nb_strlen($field->xref) > 0)
            {
                if (nbf_common::nb_strpos($field->xref, "country_codes") !== false)
                {
                    $xref_options = nbf_xref::load_xref($field->xref, false, true, array('WW', 'EU'));
                }
                else
                {
                    $extra_params = new stdClass();
                    $extra_params->form_id = isset($field->form_id) ? $field->form_id : $form_id;
                    $extra_params->selected_value = nbf_common::get_param($_REQUEST, $field->name, $field->default_value);
                    $xref_options = nbf_xref::load_xref($field->xref, true, false, array(), false, false, $extra_params);
                }
            }
            if ($xref_options)
            {
                $i = 0;
                foreach ($xref_options as $xref_option)
                {
                    $sql_field_option = new stdClass();
                    $sql_field_option->id = "xref_" . $i;
                    $sql_field_option->form_id = isset($field->form_id) ? $field->form_id : $form_id;;
                    $sql_field_option->field_id = $field->id;
                    $sql_field_option->ordering = $i+1;
                    $sql_field_option->code = $xref_option->code;
                    if (defined($xref_option->description))
                    {
                        $sql_field_option->description = constant($xref_option->description);
                    }
                    else
                    {
                        $sql_field_option->description = $xref_option->description;
                    }
                    $sql_field_option->related_product_cat = 0;
                    $sql_field_option->related_product = 0;
                    $sql_field_option->related_product_quantity = 0;
                    $sql_field_options[$field->id][] = $sql_field_option;
                    $i++;
                }
            }

            //Add any manual options
            $sql = "SELECT * FROM `$field_option_table` WHERE field_id = " . intval($field->option_field_id) . " ORDER BY ordering";
            $nb_database->setQuery($sql);
            $these_field_options = $nb_database->loadObjectList();
            if ($these_field_options && count($these_field_options) > 0)
            {
                //Set code and description programatically in case Joom!Fish is being used (which fails to handle aliases)
                foreach ($these_field_options as &$this_field_option)
                {
                    $this_field_option->code = $this_field_option->option_value;
                    $this_field_option->description = $this_field_option->option_description;
                }
                $field_options[$field->id] = array_merge($field_options[$field->id], $these_field_options);
            }
            if ($field_option_table == "#__nbill_profile_fields_options")
            {
                $option_id_suffix = 1;
                foreach ($field_options[$field->id] as &$this_field_option)
                {
                    $this_field_option->id = 'added_' . $option_id_suffix;
                    $option_id_suffix++;
                }
            }
        }
    }

    public static function map_contact_fields($contact_data, $fields, $additional_array = null)
    {
        $contact_mappings = array();
        foreach ($fields as $field)
        {
            if ($field->contact_mapping)
            {
                if ($field->contact_mapping == "custom")
                {
                    $field->contact_mapping = $field->name;
                }
                if (!isset($contact_mappings[$field->contact_mapping]))
                {
                    $contact_mappings[$field->contact_mapping] = array();
                }
                $contact_mappings[$field->contact_mapping][] = $field->name;
                if (@$field->confirmation)
                {
                    $contact_mappings[$field->contact_mapping . '_confirm'][] = $field->name;
                }
            }
        }
        foreach ($contact_mappings as $key=>$contact_mapping)
        {
            if ($contact_data && property_exists($contact_data, $key))
            {
                //Load data from contact record
                if (count($contact_mapping) > 1)
                {
                    $this_map_parts = explode(" ", $contact_data->$key);
                    if (strlen(nbf_common::get_param($_REQUEST, 'ctl_' . $contact_mapping[count($contact_mapping) - 1])) == 0)
                    {
                        for ($i=0; $i<count($this_map_parts); $i++)
                        {
                            if ($i >= count($contact_mapping))
                            {
                                $_POST['ctl_' . $contact_mapping[count($contact_mapping) - 1]] .= ' ' . $this_map_parts[$i];
                                $_REQUEST['ctl_' . $contact_mapping[count($contact_mapping) - 1]] .= ' ' . $this_map_parts[$i];
                                if ($additional_array !== null && @array_key_exists('ctl_' . $contact_mapping[count($contact_mapping) - 1], $additional_array) === false)
                                {
                                    $additional_array['ctl_' . $contact_mapping[count($contact_mapping) - 1]] .= ' ' . $this_map_parts[$i];
                                }
                                if (array_key_exists($key . '_confirm', $contact_mappings))
                                {
                                    $_POST['ctl_' . $contact_mapping[count($contact_mapping) - 1] . '_confirm'] .= ' ' . $this_map_parts[$i];
                                    $_REQUEST['ctl_' . $contact_mapping[count($contact_mapping) - 1] . '_confirm'] .= ' ' . $this_map_parts[$i];
                                    if ($additional_array !== null && @array_key_exists('ctl_' . $contact_mapping[count($contact_mapping) - 1] . '_confirm', $additional_array) === false)
                                    {
                                        $additional_array['ctl_' . $contact_mapping[count($contact_mapping) - 1] . '_confirm'] .= ' ' . $this_map_parts[$i];
                                    }
                                }
                            }
                            else
                            {
                                $_POST['ctl_' . $contact_mapping[$i]] = $this_map_parts[$i];
                                $_REQUEST['ctl_' . $contact_mapping[$i]] = $this_map_parts[$i];
                                if ($additional_array !== null && @array_key_exists('ctl_' . $contact_mapping[$i], $additional_array) === false)
                                {
                                    $additional_array['ctl_' . $contact_mapping[$i]] = $this_map_parts[$i];
                                }
                                if (array_key_exists($key . '_confirm', $contact_mappings))
                                {
                                    $_POST['ctl_' . $contact_mapping[$i] . '_confirm'] = $this_map_parts[$i];
                                    $_REQUEST['ctl_' . $contact_mapping[$i] . '_confirm'] = $this_map_parts[$i];
                                    if ($additional_array !== null && @array_key_exists('ctl_' . $contact_mapping[$i] . '_confirm', $additional_array) === false)
                                    {
                                        $additional_array['ctl_' . $contact_mapping[$i] . '_confirm'] = $this_map_parts[$i];
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    if (strlen(nbf_common::get_param($_REQUEST, 'ctl_' . $contact_mapping[0])) == 0)
                    {
                        if (count($contact_mapping) == 1)
                        {
                            $_POST['ctl_' . $contact_mapping[0]] = $contact_data->$key;
                            $_REQUEST['ctl_' . $contact_mapping[0]] = $contact_data->$key;
                            if ($additional_array !== null && @array_key_exists('ctl_' . $contact_mapping[0], $additional_array) === false)
                            {
                                $additional_array['ctl_' . $contact_mapping[0]] = $contact_data->$key;;
                            }
                            if (array_key_exists($key . '_confirm', $contact_mappings))
                            {
                                $_POST['ctl_' . $contact_mapping[0] . '_confirm'] = $contact_data->$key;
                                $_REQUEST['ctl_' . $contact_mapping[0] . '_confirm'] = $contact_data->$key;
                                if ($additional_array !== null && @array_key_exists('ctl_' . $contact_mapping[0] . '_confirm', $additional_array) === false)
                                {
                                    $additional_array['ctl_' . $contact_mapping[0] . '_confirm'] = $contact_data->$key;;
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($additional_array !== null)
        {
            return $additional_array;
        }
    }

    public static function map_entity_fields($entity_data, $fields, $use_entity_suffix = false, $additional_array = null)
    {
        $entity_mappings = array();
        foreach ($fields as $field)
        {
            if ($field->entity_mapping)
            {
                if ($field->entity_mapping == "custom")
                {
                    $field->entity_mapping = $field->name;
                }
                if (!isset($entity_mappings[$field->entity_mapping]))
                {
                    $entity_mappings[$field->entity_mapping] = array();
                }
                $entity_mappings[$field->entity_mapping][] = $field->name;
                if (@$field->confirmation)
                {
                    $entity_mappings[$field->entity_mapping . '_confirm'][] = $field->name;
                }
            }
        }

        if ($entity_data)
        {
            if (!is_array($entity_data))
            {
                $entity_data = array($entity_data);
            }
            foreach ($entity_data as &$entity_data_record)
            {
                $entity_suffix = $use_entity_suffix ? '_entity_' . $entity_data_record->id : '';
                foreach ($entity_mappings as $key=>$entity_mapping)
                {
                    if (property_exists($entity_data_record, $key))
                    {
                        if (count($entity_mapping) > 1)
                        {
                            $mapped_value = $entity_data_record->$key ? $entity_data_record->$key : @$_REQUEST['ctl_' . $entity_mapping[count($entity_mapping) - 1] . $entity_suffix];
                            $this_map_parts = explode(" ", $mapped_value);
                            for ($i=0; $i<count($this_map_parts); $i++)
                            {
                                if ($i > count($entity_mapping))
                                {
                                    $_POST['ctl_' . $entity_mapping[count($entity_mapping) - 1] . $entity_suffix] .= ' ' . $this_map_parts[$i];
                                    $_REQUEST['ctl_' . $entity_mapping[count($entity_mapping) - 1] . $entity_suffix] .= ' ' . $this_map_parts[$i];
                                    if ($additional_array !== null && @array_key_exists('ctl_' . $entity_mapping[count($entity_mapping) - 1] . $entity_suffix, $additional_array) === false)
                                    {
                                        $additional_array['ctl_' . $entity_mapping[count($entity_mapping) - 1] . $entity_suffix] .= ' ' . $this_map_parts[$i];
                                    }
                                    if (array_key_exists($key . '_confirm', $entity_mappings))
                                    {
                                        $_POST['ctl_' . $entity_mapping[count($entity_mapping) - 1] . $entity_suffix . '_confirm'] .= ' ' . $this_map_parts[$i];
                                        $_REQUEST['ctl_' . $entity_mapping[count($entity_mapping) - 1] . $entity_suffix . '_confirm'] .= ' ' . $this_map_parts[$i];
                                        if ($additional_array !== null && @array_key_exists('ctl_' . $entity_mapping[count($entity_mapping) - 1] . $entity_suffix . '_confirm', $additional_array) === false)
                                        {
                                            $additional_array['ctl_' . $entity_mapping[count($entity_mapping) - 1] . $entity_suffix . '_confirm'] .= ' ' . $this_map_parts[$i];
                                        }
                                    }
                                }
                                else
                                {
                                    $_POST['ctl_' . $entity_mapping[$i] . $entity_suffix] = $this_map_parts[$i];
                                    $_REQUEST['ctl_' . $entity_mapping[$i] . $entity_suffix] = $this_map_parts[$i];
                                    if ($additional_array !== null && @array_key_exists('ctl_' . $entity_mapping[$i] . $entity_suffix, $additional_array) === false)
                                    {
                                        $additional_array['ctl_' . $entity_mapping[$i] . $entity_suffix] = $this_map_parts[$i];
                                    }
                                    if (array_key_exists($key . '_confirm', $entity_mappings))
                                    {
                                        $_POST['ctl_' . $entity_mapping[$i] . $entity_suffix . '_confirm'] = $this_map_parts[$i];
                                        $_REQUEST['ctl_' . $entity_mapping[$i] . $entity_suffix . '_confirm'] = $this_map_parts[$i];
                                        if ($additional_array !== null && @array_key_exists('ctl_' . $entity_mapping[$i] . $entity_suffix . '_confirm', $additional_array) === false)
                                        {
                                            $additional_array['ctl_' . $entity_mapping[$i] . $entity_suffix . '_confirm'] = $this_map_parts[$i];
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            if (count($entity_mapping) == 1)
                            {
                                $_POST['ctl_' . $entity_mapping[0] . $entity_suffix] = $entity_data_record->$key;
                                $_REQUEST['ctl_' . $entity_mapping[0] . $entity_suffix] = $entity_data_record->$key;
                                if ($additional_array !== null && @array_key_exists('ctl_' . $entity_mapping[0] . $entity_suffix, $additional_array) === false)
                                {
                                    $additional_array['ctl_' . $entity_mapping[0] . $entity_suffix] = $entity_data_record->$key;
                                }
                                if (array_key_exists($key . '_confirm', $entity_mappings))
                                {
                                    $_POST['ctl_' . $entity_mapping[0] . $entity_suffix . '_confirm'] = $entity_data_record->$key;
                                    $_REQUEST['ctl_' . $entity_mapping[0] . $entity_suffix . '_confirm'] = $entity_data_record->$key;
                                    if ($additional_array !== null && @array_key_exists('ctl_' . $entity_mapping[0] . $entity_suffix . '_confirm', $additional_array) === false)
                                    {
                                        $additional_array['ctl_' . $entity_mapping[0] . $entity_suffix . '_confirm'] = $entity_data_record->$key;
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        //Use default value
                        foreach ($fields as $field)
                        {
                            if ($field->entity_mapping == $key)
                            {
                                $default_value = isset($_REQUEST['ctl_' . $entity_mapping[count($entity_mapping) - 1] . $entity_suffix]) ? $_REQUEST['ctl_' . $entity_mapping[count($entity_mapping) - 1] . $entity_suffix] : '';
                                //if (strlen($default_value) == 0) {
                                    $default_value = nbf_common::parse_and_execute_code($field->default_value);
                                //}
                                $entity_data_record->$key = $default_value;
                                $_POST['ctl_' . $entity_mapping[0] . $entity_suffix] = $default_value;
                                $_REQUEST['ctl_' . $entity_mapping[0] . $entity_suffix] = $default_value;
                                if ($additional_array !== null && @array_key_exists('ctl_' . $entity_mapping[0] . $entity_suffix, $additional_array) === false)
                                {
                                    $additional_array['ctl_' . $entity_mapping[0] . $entity_suffix] = $default_value;
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

        if ($additional_array !== null)
        {
            return $additional_array;
        }
    }

    /**
    * Retrieves value from data supplied by user, or if there is none, from the value held in the database
    * @param mixed $bound_items Array of data supplied by user
    * @param mixed $saved_data Data loaded from database
    * @param mixed $mapped_field Name of the mapped field
    */
    static function map_billing_field($bound_items, $saved_data, $mapped_field)
    {
        $return_value = nbf_common::get_param($bound_items, $mapped_field, null, true) ;
        if (nbf_common::nb_strlen($return_value) == 0 && $saved_data && property_exists($saved_data, $mapped_field))
        {
            $return_value = $saved_data->$mapped_field;
        }
        return $return_value;
    }

    public static function register_value(&$field, $full_option_list, $posted_name = "")
    {
        $control = self::create_control($field, $full_option_list, $posted_name, false, 'register_value');
        if ($control != null)
        {
            return $control->register_value();
        }
        return true;
    }

    public static function validate_field(&$field, $full_option_list, $posted_name = "")
    {
        $control = self::create_control($field, $full_option_list, $posted_name, false, 'validate');
        if ($control != null)
        {
            return $control->validate(nbf_globals::$message);
        }
        return true;
    }

    public static function process_field(&$field, $full_option_list, $posted_name = "")
    {
        $control = self::create_control($field, $full_option_list, $posted_name, false, 'process');
        if ($control != null)
        {
            $control->process(nbf_globals::$message);
        }
    }

    public static function field_submit_page(&$field, $full_option_list, $posted_name = "")
    {
        $control = self::create_control($field, $full_option_list, $posted_name, false, 'page_submit');
        if ($control != null)
        {
            return $control->page_submit(nbf_globals::$message);
        }
        return true;
    }

    public static function field_submit_form(&$field, $full_option_list, $posted_name = "")
    {
        $control = self::create_control($field, $full_option_list, $posted_name, false, 'form_submit');
        if ($control != null)
        {
            return $control->form_submit(nbf_globals::$message);
        }
        return true;
    }

    public static function get_product_value(&$field, $full_option_list, $posted_name = "")
    {
        $control = self::create_control($field, $full_option_list, $posted_name, false, 'get_product_value');
        if ($control != null)
        {
            return $control->get_product_value();
        }
        return nbf_common::get_param($_POST, 'ctl_' . ($posted_name ? $posted_name : $field->name));
    }

    public static function &create_control(&$field, $full_option_list, $posted_name = "", $admin = false, $if_method_exists = null, $constructor_args = array())
    {
        //If $field is an array, convert to an object
        $this_field = (is_array($field) ? self::_convert_array_to_object($field) : $field);

        if (!$posted_name && isset($this_field->name))
        {
            $posted_name = $this_field->name;
        }
        $control_class = "nbf_field_control";
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control.base.php");
        if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/extensions/custom/nbill.field.control." . nbf_common::nb_strtolower($this_field->field_type) . ".php")) {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/extensions/custom/nbill.field.control." . nbf_common::nb_strtolower($this_field->field_type) . ".php");
            $control_class .= "_" . nbf_common::nb_strtolower($this_field->field_type) . "_ext";
        } else {
            if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control." . nbf_common::nb_strtolower($this_field->field_type) . ".php"))
            {
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control." . nbf_common::nb_strtolower($this_field->field_type) . ".php");
                $control_class .= "_" . nbf_common::nb_strtolower($this_field->field_type);
            }
        }
        if ($if_method_exists)
        {
            $ctl_reflector = new ReflectionClass($control_class);
            $declaring_class = $ctl_reflector->getMethod($if_method_exists)->getDeclaringClass()->name;
            if ($control_class != $declaring_class && $control_class . "_default" != $declaring_class)
            {
                //The method does not exist in the overridden class, so get out of here (not interested in base implementation)
                $control = null;
                return $control;
            }
        }
        if ($constructor_args && count($constructor_args) > 0)
        {
            $control = new $control_class(@$this_field->form_id, @$this_field->id, $admin, $constructor_args);
        }
        else
        {
            $control = new $control_class(@$this_field->form_id, @$this_field->id, $admin);
        }
        $control->name = $posted_name;
        $control->suffix = "";
        if (isset($this_field->label) && property_exists($control, 'label'))
        {
            $control->label = $this_field->label;
        }
        if (@$this_field->attributes != null)
        {
            $control->attributes = $this_field->attributes;
        }
        $control->required = @$this_field->required;
        if (@$this_field->checkbox_text != null)
        {
            $control->checkbox_text = $this_field->checkbox_text;
        }
        $control->set_extended_params(@$this_field->extended_params);
        $control->horizontal_options = @$this_field->horizontal_options;
        if ($full_option_list)
        {
            $this_option_list = array();
            if (is_array(@reset($full_option_list))) //Option lists for all fields on the form have been sent in all together
            {
                if (@$this_field->id)
                {
                    foreach ($full_option_list as $field_id=>$option_list)
                    {
                        if ($field_id == $this_field->id)
                        {
                            $this_option_list = $option_list;
                            break;
                        }
                    }
                }
            }
            if (!$this_option_list)
            {
                $this_option_list = $full_option_list;
            }
            $control->field_options = $this_option_list;
        }
        if (substr($this_field->field_type, 0, 1) != 'L' && isset($_REQUEST['ctl_' . $posted_name]))
        {
            $control->value = nbf_common::get_param($_REQUEST, 'ctl_' . $posted_name, null, true, true, false);
        }
        if (@$this_field->default_value != null)
        {
            $control->default_value = $this_field->default_value;
        }
        if (method_exists($control, 'register_value'))
        {
            $control->register_value();
        }
        return $control;
    }

    private static function _convert_array_to_object($array)
    {
        if (!is_array($array))
        {
            return $array;
        }
        $object = new stdClass();
        if (is_array($array) && count($array) > 0)
        {
            foreach ($array as $key=>$value)
            {
                $key = nbf_common::nb_strtolower(trim($key));
                if ($key)
                {
                    $object->$key = self::_convert_array_to_object($value);
                }
            }
        }
        return $object;
    }

    /**
    * Store the field value for binding
    * @param mixed $field
    * @param mixed $custom_contact_fields
    * @param mixed $contact_binding
    * @param mixed $entity_id
    * @param mixed $custom_entity_fields
    * @param mixed $entity_binding
    */
    public static function prepare_bindings(&$field, &$custom_contact_fields, &$contact_binding, $entity_id, &$custom_entity_fields, &$entity_binding, &$nbill_posted_values, $quote_request = false)
    {
        $nb_database = nbf_cms::$interop->database;

        if (!$nbill_posted_values)
        {
            $nbill_posted_values = $_POST;
        }

        if ($field->contact_mapping)
        {
            if ($field->contact_mapping == "custom")
            {
                $custom_contact_fields[$field->name] = nbf_common::get_param($nbill_posted_values, 'ctl_' . $field->name, null, true);
                $contact_binding['custom_fields'] = serialize($custom_contact_fields);
            }
            else if (isset($contact_binding[$field->contact_mapping]))
            {
                $contact_binding[$field->contact_mapping] .= ' ' . nbf_common::get_param($nbill_posted_values, 'ctl_' . $field->name, null, true);
            }
            else
            {
                $contact_binding[$field->contact_mapping] = nbf_common::get_param($nbill_posted_values, 'ctl_' . $field->name, null, true);
            }
        }

        if ($field->entity_mapping && $entity_id && !isset($nbill_posted_values['ctl_' . $field->name . '_entity_' . $entity_id]))
        {
            if (!isset($entity_binding[$entity_id]))
            {
                $sql = "SELECT is_client, is_supplier FROM #__nbill_entity WHERE id = " . intval($entity_id);
                $nb_database->setQuery($sql);
                $entity_data = null;
                $nb_database->loadObject($entity_data);
                $entity_binding[$entity_id] = array();
                $entity_binding[$entity_id]['id'] = $entity_id;
                $entity_binding[$entity_id]['is_client'] = ($entity_id && $entity_id != 'new' ? $entity_data->is_client : ($quote_request ? 0 : 1));
                $entity_binding[$entity_id]['is_supplier'] = $entity_id && $entity_id != 'new' ? $entity_data->is_supplier : 0;
            }
            if ($field->entity_mapping == "custom")
            {
                $custom_entity_fields[$field->name] = nbf_common::get_param($nbill_posted_values, 'ctl_' . $field->name, null, true);
                $entity_binding[$entity_id]['custom_fields'] = serialize($custom_entity_fields);
            }
            else if (isset($entity_binding[$entity_id][$field->entity_mapping]))
            {
                $entity_binding[$entity_id][$field->entity_mapping] .= ' ' . nbf_common::get_param($nbill_posted_values, 'ctl_' . $field->name, null, true);
            }
            else
            {
                $entity_binding[$entity_id][$field->entity_mapping] = nbf_common::get_param($nbill_posted_values, 'ctl_' . $field->name, null, true);
            }
        }
    }

    /**
    * Bind form data to contact/entity
    */
    public static function execute_bindings(&$contact_binding, &$entity_binding, $new_username = "", $new_email = "", $new_tax_exemptions = array(), $vendor_id = null, $ip_address = null)
    {
        $nb_database = nbf_cms::$interop->database;

        //If existing contact has same email address and this is allegedly a new contact, we should update the existing instead
        if ((!@$contact_binding['id'] || @$contact_binding['id'] == 'new') && strlen(@$contact_binding['email_address']) > 0)
        {
            $sql = "SELECT id FROM #__nbill_contact WHERE email_address = '" . $nb_database->getEscaped($contact_binding['email_address']) . "'";
            $nb_database->setQuery($sql);
            $contact_binding['id'] = $nb_database->loadResult();
            if (!intval($contact_binding['id']))
            {
                $contact_binding['id'] = null;
            }
            else
            {
                //There is probably also an existing client
                foreach ($entity_binding as $entity_key=>$entity_item)
                {
                    if (!@$entity_item['id'] || @$entity_item['id'] == 'new' || !$entity_key || $entity_key == 'new')
                    {
                        //If this is the primary contact for an existing client, and the contact only belongs to that one client, assume it is the same one
                        $sql = "SELECT id FROM #__nbill_entity WHERE primary_contact_id = " . intval($contact_binding['id']);
                        $nb_database->setQuery($sql);
                        $entity_id = intval($nb_database->loadResult());
                        if ($entity_id)
                        {
                            $sql = "SELECT count(*) FROM #__nbill_entity_contact WHERE contact_id = " . intval($contact_binding['id']);
                            $nb_database->setQuery($sql);
                            if ($nb_database->loadResult() < 2)
                            {
                                $entity_binding[$entity_key]['id'] = $entity_id;
                            }
                        }
                    }
                }
            }
        }

        if (!$contact_binding['id'] || (isset($contact_binding['last_name']) && strlen($contact_binding['last_name']) > 0
                    && isset($contact_binding['postcode']) && strlen($contact_binding['postcode']) > 0)) {
            $contact_binding['last_updated'] = nbf_common::nb_time();
            $nb_database->insertid(); //Clear any previous value that may be lurking
            $nb_database->bind_and_save("#__nbill_contact", $contact_binding);

            $insert = !$contact_binding['id'];
            if ($insert) {
                $contact_binding['id'] = $nb_database->insertid();
            }

            $contact_service = new nBillContactService(new nBillContactMapper(nbf_cms::$interop->database, new nBillContactFactory()), new nBillAddressMapper(nbf_cms::$interop->database, '#__nbill_contact'));
            if (nbf_common::get_param($contact_binding, 'same_as_billing')) {
                $contact_service->deleteShippingAddress(intval($contact_binding['id']));
            } else {
                $contact_service->saveShippingAddress($contact_binding, intval($contact_binding['id']));
            }

            if ($insert) {
                if ($contact_binding['id']) {
                    nbf_common::fire_event("contact_created", array("id"=>$contact_binding['id']));
                }
            } else {
                nbf_common::fire_event("record_updated", array("type"=>"contact", "id"=>$contact_binding['id']));
            }
        }
        foreach ($entity_binding as &$entity_item)
        {
            $insert = false;
            if ($entity_item['id'] == "new")
            {
                $entity_item['id'] = null;
                $insert = true;
            }
            $entity_item['last_updated'] = nbf_common::nb_time();
            if (!@$entity_item['default_language'])
            {
                if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'nbill_lang')) > 0)
                {
                    $entity_item['default_language'] = nbf_common::get_param($_POST, 'nbill_lang');
                }
                else
                {
                    $entity_item['default_language'] = nbf_cms::$interop->language;
                }
            }

            if (!$entity_item['id'] || (isset($entity_item['postcode']) && strlen($entity_item['postcode']) > 0)) {
                $nb_database->insertid(); //Clear any previous value that may be lurking
                $nb_database->bind_and_save("#__nbill_entity", $entity_item);
                if (!$entity_item['id']) {
                    $entity_item['id'] = $nb_database->insertid();
                }

                $contact_factory = new nBillContactFactory();
                $entity_factory = new nBillEntityFactory();
                $client_service = $entity_factory->createEntityService($contact_factory->createContactService());
                if (nbf_common::get_param($entity_item, 'same_as_billing')) {
                    $client_service->deleteShippingAddress(intval($entity_item['id']));
                } else {
                    $client_service->saveShippingAddress($entity_item, intval($entity_item['id']));
                }
                if ($insert) {
                    if ($entity_item['id'])
                    {
                        //Set primary contact ID
                        $sql = "UPDATE #__nbill_entity SET primary_contact_id = " . intval($contact_binding['id']) . " WHERE id = " . intval($entity_item['id']);
                        $nb_database->setQuery($sql);
                        $nb_database->query();

                        //Also need to insert in entity_contact table - first, get default email invoice option
                        $sql = "SELECT email_invoice_option FROM #__nbill_configuration WHERE id = 1";
                        $nb_database->setQuery($sql);
                        $email_invoice_option = $nb_database->loadResult();
                        if (!$email_invoice_option)
                        {
                            $email_invoice_option = 'AA';
                        }
                        $sql = "INSERT INTO #__nbill_entity_contact (entity_id, contact_id, email_invoice_option) VALUES (" . intval($entity_item['id']) . ", " . intval($contact_binding['id']) . ", '" . $email_invoice_option . "')";
                        $nb_database->setQuery($sql);
                        $nb_database->query();
                        nbf_common::fire_event(($entity_item['is_client'] || !$entity_item['is_supplier'] ? 'client_created' : 'supplier_created'), array("id"=>$entity_item['id']));
                    }
                }
                else
                {
                    nbf_common::fire_event("record_updated", array("type"=>($entity_item['is_client'] || !$entity_item['is_supplier'] ? 'client' : 'supplier'), "id"=>$entity_item['id']));
                }
            }

            if ($entity_item['id'])
            {
                $ip_object = new nBillIpAddress($ip_address);
                if (!defined('NBILL_ADMIN') && strlen($ip_object->ip_address) > 0) {
                    $sql = "INSERT IGNORE INTO #__nbill_entity_ip_address (entity_id, date, ip_address, country_code) VALUES (" . intval($entity_item['id']) . ", " . nbf_common::nb_time() . ", '" . $nb_database->getEscaped($ip_object->ip_address) . "', '" . $nb_database->getEscaped($ip_object->lookupCountryCode()) . "')";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                }
            }
        }

        //Update email invoice option/reminders, if allowed
        foreach ($entity_binding as &$entity_item)
        {
            $sql = "SELECT allow_invoices, allow_reminder_opt_in FROM #__nbill_entity_contact WHERE entity_id = " . intval($entity_item['id']);
            $nb_database->setQuery($sql);
            $entity = null;
            $nb_database->loadObject($entity);

            if (isset($entity_item['email_invoice_option']) && (!$entity || $entity->allow_invoices))
            {
                $sql = "UPDATE #__nbill_entity_contact SET email_invoice_option = '" . $entity_item['email_invoice_option'] . "' WHERE entity_id = " . intval($entity_item['id']);
                $nb_database->setQuery($sql);
                $nb_database->query();
            }
            if (isset($entity_item['reminder_emails']) && (!$entity || $entity->allow_reminder_opt_in))
            {
                $sql = "UPDATE #__nbill_entity_contact SET reminder_emails = '" . ($entity_item['reminder_emails'] ? '1' : '0') . "' WHERE entity_id = " . intval($entity_item['id']);
                $nb_database->setQuery($sql);
                $nb_database->query();
            }
        }

        //Update password if applicable
        if (nbf_common::nb_strlen(str_replace("*", "", nbf_common::get_param($contact_binding,'password'))) > 0 && nbf_common::get_param($contact_binding, 'user_id'))
        {
            nbf_cms::$interop->update_password(nbf_common::get_param($contact_binding,'password'), intval(nbf_common::get_param($contact_binding, 'user_id')));
        }

        //If email address changed, update user record
        if (nbf_common::nb_strlen($new_email) > 0 && nbf_common::get_param($contact_binding, 'user_id'))
        {
            nbf_cms::$interop->update_email_address($new_email, intval(nbf_common::get_param($contact_binding, 'user_id')));
        }

        //If contact name changed, update user record
        if (nbf_common::get_param($contact_binding, 'name') && nbf_common::get_param($contact_binding, 'user_id'))
        {
            nbf_cms::$interop->update_name(nbf_common::get_param($contact_binding, 'name'), intval(nbf_common::get_param($contact_binding, 'user_id')));
        }

        //If new tax exemption code added, modify orders accordingly, if applicable
        foreach ($entity_binding as $entity_item)
        {
            if ($entity_item['id'])
            {
                if (isset($new_tax_exemptions[$entity_item['id']]) && nbf_common::get_param($new_tax_exemptions,$entity_item['id']) != nbf_common::get_param($entity_item,'tax_exemption_code'))
                {
                    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.tax.class.php");
                    nbf_tax::update_tax_exemption_code($entity_item['id']);
                }
            }
        }

        //If username changed, update user record
        if (nbf_common::nb_strlen($new_username) > 0 && nbf_common::get_param($contact_binding, 'user_id'))
        {
            nbf_cms::$interop->update_username($new_username, intval(nbf_common::get_param($contact_binding, 'user_id')));
        }
    }
}