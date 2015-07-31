<?php
/**
* Default HTML output template for user profile page
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nbill_fe_profile
{
    /**
    * Render the form
    * @param mixed $profile_fields Core profile fields
    * @param mixed $field_options List options for radio list and dropdown list fields
    * @param mixed $sql_field_options List options loaded from database for radio list and dropdown list fields
    * @param mixed $contact_data Details of the loaded contact
    * @param mixed $entity_data Details of all entities associated with the loaded contact
    * @param mixed $countries List of countries
    * @param mixed $email_options_xref E-mail invoice preferences
    * @param mixed $entity_in_error ID of entity with an error so the appropriate tab can be pre-selected
    */
    public static function display(&$profile_fields, &$field_options, &$sql_field_options, &$contact_data, &$entity_data, &$countries, &$email_options_xref, $entity_in_error = 0, $languages = array())
    {
        if (nbf_frontend::get_display_option("pathway"))
        {?>
        <div class="pathway" style="margin-bottom:5px"><a href="<?php
        echo nbf_cms::$interop->process_url(nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . nbf_cms::$interop->site_page_suffix);
        ?>"><?php echo NBILL_MAIN_MENU; ?></a> &gt; <?php echo NBILL_MY_PROFILE; ?></div>
        <?php } ?>
        <table width="100%" class="contentpane nbill-profile" cellpadding="3" cellspacing="1" border="0">
            <?php
            $contact_output = "";
            $entity_tabs = array();
            $separate_entity = nbf_common::get_param($_POST, 'force_separate_entity', false);
            if (!$separate_entity)
            {
                //If ANY of the entity-mapped fields require separation, separate ALL entity-mapped fields
                foreach ($profile_fields as &$field)
                {
                    if ($field->entity_mapping && ((count($entity_data) > 1 || (count($entity_data) == 1 && $entity_data[0]->primary_contact_id != null && $entity_data[0]->primary_contact_id != $contact_data->id)) || (count($entity_data) == 1 && $field->contact_mapping && @$_REQUEST['ctl_' . $field->name] != @$_REQUEST['ctl_' . $field->name . '_entity_' . $entity_data[0]->id] && @$_REQUEST['ctl_' . $field->name . '_entity_' . $entity_data[0]->id] != null)))
                    {
                        $separate_entity = true;
                        break;
                    }
                }
            }

            if ($separate_entity)
            {
                foreach ($entity_data as $entity)
                {
                    $entity_tabs[$entity->id] = self::_render_language_selector($languages, $entity, '_entity_' . $entity->id);
                }
            }
            else
            {
                if (@$entity_data[0]->id)
                {
                    $contact_output = self::_render_language_selector($languages, $entity_data[0], '_entity_' . $entity_data[0]->id);
                }
            }

            foreach ($profile_fields as &$field)
            {
                //Render field for contact/entity as appropriate
                if ($field->contact_mapping || !$field->entity_mapping || count($entity_data) == 0 || !$separate_entity)
                {
                    if (!$field->contact_mapping && $field->entity_mapping)
                    {
                        if (isset($_REQUEST['ctl_' . $field->name . '_entity_' . $entity_data[0]->id]) && !isset($_REQUEST['ctl_' . $field->name]))
                        {
                            //Contact and entity are sharing the same controls, but this field is for entity only, so contact will not hold a value
                            $_REQUEST['ctl_' . $field->name] = $_REQUEST['ctl_' . $field->name . '_entity_' . $entity_data[0]->id];
                            $_POST['ctl_' . $field->name] = $_REQUEST['ctl_' . $field->name];
                        }
                    }
                    $contact_output .= self::_render_field($field, $field_options, $sql_field_options, '');
                }
                if ($field->entity_mapping && $separate_entity)
                {
                    foreach ($entity_data as $entity)
                    {
                        if (($entity->allow_invoices || $field->entity_mapping != 'email_invoice_option') && ($entity->allow_reminder_opt_in || $field->entity_mapping != 'reminder_emails'))
                        {
                            $entity_tabs[$entity->id] .= self::_render_field($field, $field_options, $sql_field_options, '_entity_' . $entity->id);
                        }
                    }
                }
            }

            if (nbf_common::nb_strlen($contact_output) > 0)
            {
                echo $contact_output;
            }
            if (count($entity_tabs) > 0)
            {
                echo '<tr><td colspan="2">';
                $entity_map_tab_group = new nbf_tab_group();
                $entity_map_tab_group->start_tab_group("entity_mapping", count($entity_tabs) > 2);
                foreach ($entity_data as $entity)
                {
                    //If any don't have a company name, use the contact name
                    $entity_name = $entity->company_name;
                    if (strlen($entity_name) == 0)
                    {
                        //Make sure there isn't already an entry for this name
                        $contact_name_exists = false;
                        $this_contact = trim($contact_data->first_name . ' ' . $contact_data->last_name);
                        foreach ($clients as $other_client)
                        {
                            if ($other_client->company_name == $this_contact)
                            {
                                $contact_name_exists = true;
                                break;
                            }
                        }
                        if (!$contact_name_exists)
                        {
                            $entity_name = $this_contact;
                        }
                    }
                    $entity_map_tab_group->add_tab_title($entity->id, $entity_name ? ($entity->is_supplier && !$entity->is_client ? NBILL_FE_SUPPLIER . ': ' : '') . $entity_name : sprintf($entity->is_supplier && !$entity->is_client ? NBILL_SUPPLIER_NONAME : NBILL_CLIENT_NONAME, $entity->id));
                }
                foreach ($entity_data as $entity)
                {
                    $entity_map_tab_group->add_tab_content($entity->id, '<table width="100%" class="contentpane nbill-profile" cellpadding="3" cellspacing="1" border="0">' . $entity_tabs[$entity->id] . '</table>');
                }
                echo '</td></tr>';
            }
            ?>
            <tr id="row_core_profile_submit"><td>&nbsp;</td><td style="<?php echo count($entity_tabs) > 0 ? "text-align:right;" : "text-align:left;padding-left:80px;"; ?>"><input type="submit" name="submit_entity_details" class="button btn" value="<?php echo NBILL_SUBMIT; ?>" />&nbsp;<input type="submit" name="cancel" class="button btn" value="<?php echo NBILL_CANCEL; ?>" /></td></tr>
        </table>
        <?php if (count($entity_tabs) > 0)
        { ?>
            <script type="text/javascript">
                select_tab_entity_mapping('<?php echo $entity_in_error ? $entity_in_error : $entity_data[0]->id; ?>');
            </script>
        <?php
        }
    }

    protected static function _render_field(&$field, &$field_options, &$sql_field_options, $suffix = "")
    {
        $control_class = "nbf_field_control";
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control.base.php");
        if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control." . nbf_common::nb_strtolower($field->field_type) . ".php"))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control." . nbf_common::nb_strtolower($field->field_type) . ".php");
            $control_class .= "_" . nbf_common::nb_strtolower($field->field_type);
        }
        $control = new $control_class(null, null);
        $control->name = $field->name . $suffix;
        $control->id = $field->id;
        $control->suffix = "";
        if (substr($field->type, 0, 1) != 'L' && isset($_POST['ctl_' . $field->name . $suffix]))
        {
            $posted_value = nbf_common::get_param($_POST, 'ctl_' . $field->name . $suffix, null, true, true, false);
            $field->value = $posted_value;
            $control->default_value = $posted_value;
        }
        else
        {
            if ($field->default_value != null)
            {
                $control->default_value = $field->default_value;
            }
        }
        if ($field->attributes != null)
        {
            $control->attributes = $field->attributes;
        }
        $control->required = $field->required;
        if ($field->checkbox_text != null)
        {
            $control->checkbox_text = $field->checkbox_text;
        }
        $control->horizontal_options = $field->horizontal_options;
        $control->field_options = array_filter($sql_field_options[$field->id]) + array_filter($field_options[$field->id]) + $sql_field_options[$field->id] + $field_options[$field->id];
        ob_start();
        ?>
        <tr id="row_core_profile_<?php echo $field->name . $suffix ?>">
            <?php if (!$field->merge_columns)
            { ?>
            <td class="field-title" style="min-width:25%;vertical-align:top;">
                <div id="lbl_<?php echo $field->id . $suffix; ?>" class="nbill_label"><?php echo $field->label ? ((defined(str_replace("* ", "", $field->label)) ? (nbf_common::nb_strpos($field->label, "* ") !== false ? "* " : "") . constant(str_replace("* ", "", $field->label)) : $field->label)) : "&nbsp;"; ?></div>
            </td>
            <?php } ?>
            <td class="field-value"<?php echo $field->merge_columns ? "colspan=\"2\"" : ""; ?> style="text-align:left;vertical-align:top;width:100%;">
                <?php if ($field->merge_columns)
                {
                    echo $field->label ? ((defined(str_replace("* ", "", $field->label)) ? (nbf_common::nb_strpos($field->label, "* ") !== false ? "* " : "") . constant(str_replace("* ", "", $field->label)) : $field->label)) : "";
                } ?>
                <div id="fld_<?php echo $field->id . $suffix ?>" class="nbill_profile_field" style="height:auto;">
                    <div id="val_<?php echo $field->id . $suffix ?>" class="nbill_value<?php if (@array_search('ctl_' . $field->name . $suffix, nbf_globals::$fields_in_error) !== false) {echo " field_in_error";} ?>">
                        <div id="pre_field_<?php echo $field->id . $suffix ?>" style="display:inline;"><?php
                        //Execute any code needed to retrieve the pre_field value
                        echo nbf_common::parse_and_execute_code($field->pre_field);
                        ?></div>
                        <div id="rendered_control_<?php echo $field->id . $suffix; ?>" style="display:inline;">
                            <?php
                            $control->render_control();
                            ?>
                        </div>
                        <div id="post_field_<?php echo $field->id . $suffix ?>" style="display:inline;"><?php
                        //Execute any code needed to retrieve the pre_field value
                        echo nbf_common::parse_and_execute_code($field->post_field);
                        ?></div>
                    </div>
                </div>
                <?php
                if (nbf_common::nb_strlen($field->help_text) > 0)
                { ?>
                    <div id="help_<?php echo $field->id . $suffix; ?>" style="display:inline;vertical-align:top;"><?php
                    nbf_html::show_overlib($field->help_text);
                    ?>
                    </div><?php
                } ?>
            </td>
        </tr>
        <?php
        if ($field->confirmation)
        {
            $suffix = "_confirm"; ?>
            <tr id="row_confirm_core_profile_<?php echo $field->name . $suffix; ?>">
                <td class="field-title" style="min-width:25%;vertical-align:top">
                    <div id="lbl_<?php echo $field->id . $suffix; ?>" class="nbill_label"><?php echo $field->label ? sprintf((nbf_common::nb_strpos($field->label, "* ") !== false ? "* " : "") . NBILL_CONFIRM_LABEL, ((defined(str_replace("* ", "", $field->label)) ? constant(str_replace("* ", "", $field->label)) : $field->label))) : sprintf(NBILL_CONFIRM_LABEL, "&nbsp;"); ?></div>
                </td>
                <td class="field-value" style="text-align:left;vertical-align:top;width:100%;">
                    <div id="conf_val_<?php echo $field->id . $suffix; ?>" class="nbill_value nbill_confirm_value<?php if (@array_search('ctl_' . $field->name . $suffix . '_confirm', nbf_globals::$fields_in_error) !== false) {echo " field_in_error";} ?>">
                        <?php
                        if (substr($field->type, 0, 1) != 'L' && isset($_POST['ctl_' . $field->name . $suffix . '_confirm']))
                        {
                            $control->default_value = nbf_common::get_param($_POST, 'ctl_' . $field->name . $suffix . '_confirm', null, true, true, false);
                        }
                        $control->suffix = "_confirm";
                        $control->render_control();
                        ?>
                    </div>
                </td>
            </tr>
            <?php
        }
        return ob_get_clean();
    }

    protected static function _render_language_selector($languages, $entity, $suffix = "")
    {
        $html = "";
        if (count($languages) > 1 && nbf_frontend::get_display_option("choose_lang"))
        {
            ob_start();
            ?>
            <tr id="row_core_profile_choose_language">
                <td class="field-title" style="min-width:25%;vertical-align:top">
                    <div id="lbl_choose_language" class="nbill_label"><?php echo NBILL_CHOOSE_LANGUAGE; ?></div>
                </td>
                <td class="field-value" style="text-align:left;vertical-align:top;width:100%;">
                    <div id="nbill_choose_language" class="nbill_value">
                        <?php
                        $lang_codes = array();
                        foreach ($languages as $key=>$value)
                        {
                            //If this is the default front-end language, the value should be blank (so if the default changes, the client's language is not stuck on the old value)
                            $lang_codes[] = nbf_html::list_option($key, $value);
                        }
                        $selected_language = @$entity->default_language;
                        if (!$selected_language)
                        {
                            $selected_language = nbf_cms::$interop->get_frontend_language();
                        }
                        echo nbf_html::select_list($lang_codes, "ctl_default_language" . $suffix, 'id="ctl_default_language' . $suffix . '" class="inputbox"', $selected_language);
                        ?>
                    </div>
                    <div id="help_choose_language" style="display:inline;vertical-align:top;"><?php
                    nbf_html::show_overlib(NBILL_CHOOSE_LANGUAGE_HELP);
                    ?>
                    </div>
                </td>
            </tr>
            <?php
            $html = ob_get_clean();
        }
        return $html;
    }

    /**
    * Any processing to be done before the core submit routine is called
    */
    public static function pre_submit()
    {
    }

    /**
    * If this function returns false, the core submit routine will be bypassed
    */
    public static function submit()
    {
        return true;
    }

    /**
    * Any processing to be done after the core submit routine is called
    */
    public static function post_submit()
    {
    }
}