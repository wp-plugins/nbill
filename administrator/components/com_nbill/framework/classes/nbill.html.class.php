<?php
/**
* Class file just containing static methods for rendering HTML.
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* Class just contains static functions for use anywhere within the code
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_html
{
    /**
    * Output a pair of option buttons for a yes/no answer (returning value of 1 for yes, 0 (zero) for no)
    * @param string $option_name Name to give the HTML radio buttons
    * @param string $option_attributes HTML attributes to apply to each button
    * @param integer Which item is selected by default (0 or 1)
    * @return string The HTML to output
    */
    public static function yes_or_no_options($option_name, $option_attributes, $selected)
    {
        //No
        $options = '<input type="radio" class="nbill_form_input" name="' . $option_name . '" id="' . $option_name . '0" value="0"';
        if (nbf_common::nb_strlen($option_attributes) > 0)
        {
            $options .= ' ' . $option_attributes;
        }
        if ($selected == 0)
        {
            $options .= ' checked="checked"';
        }
        $options .= ' /><label for="' . $option_name . '0" class="nbill_form_label radio-label">' . NBILL_NO . '</label>&nbsp;&nbsp;';

        //Yes
        $options .= '<input type="radio" class="nbill_form_input" name="' . $option_name . '" id="' . $option_name . '1" value="1"';
        if (nbf_common::nb_strlen($option_attributes) > 0)
        {
            $options .= ' ' . $option_attributes;
        }
        if ($selected == 1)
        {
            $options .= ' checked="checked"';
        }
        $options .= ' /><label for="' . $option_name . '1" class="nbill_form_label radio-label">' . NBILL_YES . '</label>&nbsp;';

        return $options;
    }

    /**
    * Create an object representing an option tag for use within a select list or radio list with the given value, description, and attributes
    * @param mixed $value The value to be returned by the option
    * @param string $description The display name for the option
    * @param string $attributes Any additional HTML attributes to add
    */
    public static function list_option($value, $description, $attributes = "")
    {
        $option = new html_list_option();
        $option->value = $value;
        $option->description = $description;
        $option->attributes = $attributes;
        return $option;
    }

    /**
    * Create a select list
    * @param array $list_options An array of options (created with @see list_option)
    * @param string $select_name Name of the select tag
    * @param string $select_attributes Any additional HTML attributes to add to the select tag
    * @param string $selected The selected item
    * @return string The HTML for the select list
    */
    public static function select_list(&$list_options, $select_name, $select_attributes = "", $selected = null)
    {
        $select = '<select name="' . $select_name . '"';
        if (nbf_common::nb_strlen($select_attributes) > 0)
        {
            $select .= ' ' . $select_attributes;
        }
        $select .= '>';
        //De-select all in case options are being re-used
        foreach ($list_options as &$list_option)
        {
        	$list_option->selected = false;
		}

        foreach ($list_options as &$list_option)
        {
            if ((is_array($selected) && array_search($list_option->value, $selected) !== false) || $list_option->value == $selected)
            {
                $list_option->selected = true;
            }
            $select .= $list_option->__toString();
        }
        $select .= '</select>';
        return $select;
    }

    /**
    * Create a radio button list
    * @param array $list_options An array of options (created with @see list_option)
    * @param string $group_name Name of the group of radio buttons
    * @param string $selected The selected item
    * @return string The HTML for the select list
    */
    public static function radio_list(&$list_options, $group_name, $selected = null, $horizontal = false)
    {
        $output = "";
        $index = 0;
        foreach ($list_options as $list_option)
        {
            $list_option->_ordering = $index;
            $index++;
            $list_option->_group_name = $group_name;
            $list_option->selected = $list_option->value == $selected;
            $list_option->_radio = true;
            if (!$horizontal)
            {
                $output .= "<div>";
            }
            $output .= $list_option->__toString();
            if (!$horizontal)
            {
                $output .= "</div>";
            }
        }
        return $output;
    }

    /**
    * Output a checkbox with 'cb' + the given row number as the ID, and the record's primary key as the value
    * @param integer $row_number The sequential number of this record in the list
    * @param integer $id The primary key of the row
    */
    public static function id_checkbox($row_number, $id, $default_to_checked = false)
    {
        return '<input type="checkbox" name="cid[]" id="cb' . $row_number . '" value="' . $id . '" onclick="var max_possible=document.getElementsByTagName(\'input\').length;var max = (document.getElementById(\'records_per_page\') ? document.getElementById(\'records_per_page\').value : max_possible);max = max != \'\' && max != \'all\' ? max : max_possible;document.adminForm.box_checked.value = 0;for(var i=0;i<max;i++){if (document.getElementById(\'cb\' + i) && document.getElementById(\'cb\' + i).checked){document.adminForm.box_checked.value = 1;}}if(this.parentNode.parentNode && this.parentNode.parentNode.nodeName==\'TR\'){this.parentNode.parentNode.className=this.checked?\'selected\':\'\'}" ' . ($default_to_checked ? 'checked="checked"' : '') . '/>';
    }

    /**
    * Prepare to display a calendar control
    */
    static function load_calendar()
    {
        nbf_cms::$interop->add_html_header('<link rel="stylesheet" href="' . nbf_cms::$interop->nbill_site_url_path . '/calendar/dhtmlgoodies_calendar/dhtmlgoodies_calendar.css" media="screen"></link>');
        echo "<script type=\"text/javascript\">var pathToImages = '" . nbf_cms::$interop->nbill_site_url_path . "/calendar/images/';</script>";
        nbf_cms::$interop->add_html_header('<script type="text/javascript" src="' . nbf_cms::$interop->nbill_site_url_path . '/calendar/dhtmlgoodies_calendar/dhtmlgoodies_calendar.js"></script>');
    }

    /**
    * Output javascript function to validate a numeric value
    */
    static function add_js_validation_numeric()
    {
        ?>
        function IsNumeric(strString, emptyAllowed)
        {
            // Check for valid numeric strings
            var strValidChars = "0123456789.-";
            var strChar;
            var blnResult = true;

            if (strString.length == 0)
            {
                return emptyAllowed;
            }
            // Test strString consists of valid characters listed above
            for (i = 0; i < strString.length && blnResult == true; i++)
            {
              strChar = strString.charAt(i);
              if (strValidChars.indexOf(strChar) == -1)
              {
                    blnResult = false;
              }
            }
            return blnResult;
        }
        <?php
    }

    /**
    * Output javascript function to validate a date
    */
    static function add_js_validation_date()
    {
        ?>
        function get_date_parts(date_string)
        {
            //Return the date as an array: y,m,d
            var cal_date_format = '<?php echo nbf_common::nb_strtolower(nbf_common::get_date_format(true)); ?>';

            //Get the separator
            var separator = "";
            for (var i=0; i < cal_date_format.length; i++)
            {
                var char = cal_date_format.substr(i, 1);
                switch (char)
                {
                    case 'd':
                    case 'm':
                    case 'y':
                    case 'h':
                    case 'i':
                    case 's':
                        break;
                    default:
                        separator = char;
                        break;
                }
                if (separator.length > 0)
                {
                    break;
                }
            }

            var format_parts = cal_date_format.split(separator);
            var date_parts = date_string.split(separator);

            return_value = new Array();
            for (i=0; i < format_parts.length; i++)
            {
                var key = format_parts[i].substr(0, 1);
                var value = date_parts[i];
                //For year, make sure it is in full
                if (key == 'y')
                {
                    if (value.length < 4)
                    {
                        if (value > 69)
                        {
                            value = '19' + value;
                        }
                        else
                        {
                            value = '20' + value;
                        }
                    }
                }
                return_value[key] = value;
            }
            return return_value;
        }

        function IsValidDate(date_string, emptyAllowed)
        {
          var valid_date = false;

            if (date_string && date_string.length > 0)
            {
                date_parts = get_date_parts(date_string);
                if (date_parts['d'] != null && date_parts['m'] != null && date_parts['y'] != null)
                {
                    if (IsNumeric(date_parts['d']) && IsNumeric(date_parts['m']) && IsNumeric(date_parts['y']))
                    {
                        if (date_parts['y'] > 999 && date_parts['y'] < 2999)
                        {
                            if (date_parts['m'] > 0 && date_parts['m'] < 13)
                            {
                                var max_day = 31;
                                switch (date_parts['m'])
                                {
                                    case 2:
                                        max_day = 29;
                                        break;
                                    case 4:
                                    case 6:
                                    case 9:
                                    case 11:
                                        max_day = 30;
                                        break;
                                }
                                if (date_parts['d'] > 0 && date_parts['d'] <= max_day)
                                {
                                    valid_date = true;
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                if (emptyAllowed)
                {
                  valid_date = true;
                }
            }

            return valid_date;
        }
        <?php
    }

    /**
    * Output hidden fields to hold the values entered on filter controls so that when returning to a previous list, the filter remains intact
    * @param array $exclude_filter Array of filter names to exclude from the output
    */
    static function add_filters($exclude_filter = array())
    {
        if (array_search("return", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="return" id="return" value="<?php echo nbf_common::get_param($_REQUEST,'return'); ?>" />
        <?php
        }
        if (array_search("search_date_from", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="search_date_from" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_from'); ?>" />
        <?php
        }
        if (array_search("search_date_to", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="search_date_to" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_to'); ?>" />
        <?php
        }
        if (array_search("vendor_filter", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="vendor_filter" value="<?php echo nbf_common::get_param($_REQUEST,'vendor_filter'); ?>" />
        <?php
        }
        if (array_search("category_filter_" . nbf_common::get_param($_REQUEST,'vendor_filter'), $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="category_filter_<?php echo nbf_common::get_param($_REQUEST,'vendor_filter'); ?>" value="<?php echo nbf_common::get_param($_REQUEST,'category_filter_' . nbf_common::get_param($_REQUEST,'vendor_filter'));?>" />
        <?php
        }
        if (array_search("contact_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="contact_search" value="<?php echo nbf_common::get_param($_REQUEST,'contact_search'); ?>" />
        <?php
        }
        if (array_search("contact_user_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="contact_user_search" value="<?php echo nbf_common::get_param($_REQUEST,'contact_user_search'); ?>" />
        <?php
        }
        if (array_search("contact_email_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="contact_email_search" value="<?php echo nbf_common::get_param($_REQUEST,'contact_email_search'); ?>" />
        <?php
        }
        if (array_search("client_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="client_search" value="<?php echo nbf_common::get_param($_REQUEST,'client_search'); ?>" />
        <?php
        }
        if (array_search("client_user_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="client_user_search" value="<?php echo nbf_common::get_param($_REQUEST,'client_user_search'); ?>" />
        <?php
        }
        if (array_search("client_email_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="client_email_search" value="<?php echo nbf_common::get_param($_REQUEST,'client_email_search'); ?>" />
        <?php
        }
        if (array_search("product_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="product_search" value="<?php echo nbf_common::get_param($_REQUEST,'product_search'); ?>" />
        <?php
        }
        if (array_search("relating_to_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="relating_to_search" value="<?php echo nbf_common::get_param($_REQUEST,'relating_to_search'); ?>" />
        <?php
        }
        if (array_search("status_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="status_search" value="<?php echo nbf_common::get_param($_REQUEST,'status_search'); ?>" />
        <?php
        }
        if (array_search("order_no_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="order_no_search" value="<?php echo nbf_common::get_param($_REQUEST,'order_no_search'); ?>" />
        <?php
        }
        if (array_search("nbill_no_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="nbill_no_search" value="<?php echo nbf_common::get_param($_REQUEST,'nbill_no_search'); ?>" />
        <?php
        }
        if (array_search("rct_no_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="rct_no_search" value="<?php echo nbf_common::get_param($_REQUEST,'rct_no_search');?>" />
        <?php
        }
        if (array_search("name_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="name_search" value="<?php echo nbf_common::get_param($_REQUEST,'name_search');?>" />
        <?php
        }
        if (array_search("description_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="description_search" value="<?php echo nbf_common::get_param($_REQUEST,'description_search');?>" />
        <?php
        }
        if (array_search("rct_amount", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="rct_amount" value="<?php echo nbf_common::get_param($_REQUEST,'rct_amount');?>" />
        <?php
        }
        if (array_search("pyt_no_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="pyt_no_search" value="<?php echo nbf_common::get_param($_REQUEST,'pyt_no_search');?>" />
        <?php
        }
        if (array_search("paid_to_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="paid_to_search" value="<?php echo nbf_common::get_param($_REQUEST,'paid_to_search');?>" />
        <?php
        }
        if (array_search("pyt_amount", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="pyt_amount" value="<?php echo nbf_common::get_param($_REQUEST,'pyt_amount');?>" />
        <?php
        }
        if (array_search("supplier_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="supplier_search" value="<?php echo nbf_common::get_param($_REQUEST,'supplier_search'); ?>" />
        <?php
        }
        if (array_search("supplier_user_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="supplier_user_search" value="<?php echo nbf_common::get_param($_REQUEST,'supplier_user_search'); ?>" />
        <?php
        }
        if (array_search("supplier_email_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="supplier_email_search" value="<?php echo nbf_common::get_param($_REQUEST,'supplier_email_search'); ?>" />
        <?php
        }
        if (array_search("discount_search", $exclude_filter) === false)
        {
        ?>
            <input type="hidden" name="discount_search" value="<?php echo nbf_common::get_param($_REQUEST,'discount_search'); ?>" />
        <?php
        }
    }

    /**
    * Inject a style attribute into the existing attributes
    * @param string $attributes Existing attributes (possibly already including a style attribute)
    * @param mixed $style Style attribute to add
    * @return string Combined attribute string
    */
    public static function add_style($attributes, $style)
    {
        $attributes = nbf_common::nb_strtolower($attributes);
        if (nbf_common::nb_substr($style, nbf_common::nb_strlen($style) - 1, 1) !== ";")
        {
            $style .= ";";
        }

        $stylepos = nbf_common::nb_strpos($attributes, "style=");
        if ($stylepos === false)
        {
            $stylepos = nbf_common::nb_strpos($attributes, "style =");
        }
        if ($stylepos !== false)
        {
            $style_start = nbf_common::nb_strpos($attributes, "\"", $stylepos);
            if ($style_start === false)
            {
                //Try single quotes
                $style_start = nbf_common::nb_strpos($attributes, "'", $stylepos);
            }
            if ($style_start !== false)
            {
                $attributes = substr($attributes, 0, $style_start + 1) . $style . substr($attributes, $style_start + 1);
            }
            else
            {
                //Quote marks not found - give up
                return $attributes;
            }
        }
        else
        {
            $attributes .= " style=\"$style\"";
        }

        return $attributes;
    }

    /**
    * Inject an attribute into an existing attribute string if it is not already present
    * @param mixed $attributes Existing attributes
    * @param mixed $attr_name New attribute name
    * @param mixed $attr_value New attribute value
    */
    public static function add_attribute($attributes, $attr_name, $attr_value)
    {
        //Only add the attribute if it is not already present
        $attr_pos = nbf_common::nb_strpos($attributes, "$attr_name=");
        if ($attr_pos === false)
        {
            $attr_pos = nbf_common::nb_strpos($attributes, "$attr_name =");
            if ($attr_pos === false)
            {
                $attributes .= " $attr_name=\"$attr_value\"";
            }
        }
        return $attributes;
    }

    /**
    * Show the 'next' button on an order form or quote request form
    *
    * @param mixed $form_id
    * @param mixed $page
    * @param mixed $admin
    * @param mixed $legacy
    */
    public static function show_next_button($form_id, &$page, $admin = false, $legacy = false, $responsive = false)
    {
        $control_class = "nbf_field_control_nn";
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control.nn.php");
        $control = new $control_class($form_id, "next_" . $page->page_no);
        $control->name = "next_" . $page->page_no;
        $control->suffix = "";
        $control->default_value = $page->next_default_value;
        $control->attributes = $page->next_attributes;
        ?>
        <div id="fld_next_<?php echo $page->page_no; ?>" <?php if (!$legacy && !$responsive) { ?>class="nbill_field" style="top:<?php echo $page->next_y_pos; ?>px;left:<?php echo $page->next_x_pos; ?>px;height:auto;position:absolute;"<?php } if ($responsive) {?> style="float:right;"<?php } if ($legacy) {?> style="display:inline-block;"<?php } if ($admin) {?> onmousedown="field_mouse_down(event,this);return false;" onmousemove="field_mouse_move(event,this);return false;" onmouseup="field_mouse_up(event,this);return false;" onclick="stop_bubble(event);"<?php } ?>>
            <div id="val_next_<?php echo $page->page_no; ?>" class="nbill_value">

                <div id="pre_field_next_<?php echo $page->page_no; ?>" style="display:inline;"><?php echo $page->next_pre_field; ?></div>
                <div id="rendered_control_next_<?php echo $page->page_no; ?>" style="display:inline;">
                    <?php
                    $control->render_control($admin);
                    ?>
                </div>
                <div id="post_field_next_<?php echo $page->page_no; ?>" style="display:inline;"><?php echo $page->next_post_field; ?></div>
                <div id="help_next_<?php echo $page->page_no; ?>" style="display:inline;"><?php
                if (nbf_common::nb_strlen($page->next_help_text) > 0)
                {
                    echo '&nbsp;';
                    self::show_overlib($page->next_help_text);
                } ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
    * Show the 'previous' button on an order form or quote request form
    *
    * @param mixed $form_id
    * @param mixed $page
    * @param mixed $admin
    * @param mixed $legacy
    */
    public static function show_previous_button($form_id, &$page, $admin = false, $legacy = false, $responsive = false)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control.nn.php");
        $control = new nbf_field_control_nn($form_id, "prev_" . $page->page_no);
        $control->name = "prev_" . $page->page_no;
        $control->suffix = "";
        $control->default_value = $page->prev_default_value;
        $control->attributes = $page->prev_attributes;
        ?>
        <div id="fld_prev_<?php echo $page->page_no; ?>" <?php if (!$legacy && !$responsive){ ?>style="top:<?php echo $page->prev_y_pos; ?>px;left:<?php echo $page->prev_x_pos; ?>px;height:auto;position:absolute;"<?php } if ($admin) {?> onmousedown="field_mouse_down(event,this);return false;" onmousemove="field_mouse_move(event,this);return false;" onmouseup="field_mouse_up(event,this);return false;" onclick="stop_bubble(event);"<?php } ?>>
            <div id="val_prev_<?php echo $page->page_no; ?>" class="nbill_value">
            <div id="pre_field_prev_<?php echo $page->page_no; ?>" style="display:inline;"><?php echo $page->prev_pre_field; ?></div>
            <div id="rendered_control_prev_<?php echo $page->page_no; ?>" style="display:inline;">
                <?php
                $control->render_control($admin);
                ?>
            </div>
            <div id="post_field_prev_<?php echo $page->page_no; ?>" style="display:inline;"><?php echo $page->prev_post_field; ?></div>
            <div id="help_prev_<?php echo $page->page_no; ?>" style="display:inline;"><?php
            if (nbf_common::nb_strlen($page->prev_help_text) > 0)
            {
                echo '&nbsp;';
                self::show_overlib($page->prev_help_text);
            } ?></div>
            </div>
        </div>
        <?php
    }

    public static function include_overlib_js()
    {
        static $js_present = false;

        if (!$js_present) {
            nbf_cms::$interop->add_html_header('<script type="text/javascript" src="' . nbf_cms::$interop->nbill_site_url_path . '/js/nbill_overlib_mini.js"></script>');
            $js_present = true;
        }
    }

    public static function show_overlib($overlib_text, $elem_id = '')
    {
        self::include_overlib_js();
        $overlib_text = defined($overlib_text) ? constant($overlib_text) : $overlib_text;
        $overlib_text = strip_tags($overlib_text, '<p><b><strong><i><em><span>');
        $overlib_text = str_replace("'", "`", $overlib_text);
        $overlib_text = str_replace('"', "``", $overlib_text);

        if ($elem_id) {
            ?>
            <span id="<?php echo $elem_id; ?>">
            <?php
        }
        ?>
        <a href="javascript:void(0);" onblur="this.onmouseout();" onclick="overlibToggle=!overlibToggle;if(overlibToggle){this.onmouseover();}else{this.onmouseout();}return false;" onmouseover="return nbill_overlib('<?php echo $overlib_text; ?>');" onmouseout="return nbill_overlib_nd();"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/info.png" border="0" /></a>
        <?php
        if ($elem_id) {
            ?>
            </span>
            <?php
        }
    }

    public static function show_static_help($help_text, $elem_id)
    {
        //Avoid white-space on link to prevent unwanted wrapping on narrow screens
        ?><a title="<?php echo NBILL_HELP; ?>" href="javascript:void(0);" onclick="var help=document.getElementById('<?php echo $elem_id; ?>');help.style.display=help.style.display=='none'?'':'none';this.blur();return false;"><?php
            ?><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/help_icon.png" border="0" alt="<?php echo NBILL_HELP; ?>" /><?php
        ?></a>
        <div id="<?php echo $elem_id; ?>" class="nbill-static-help-text" style="display:none;"><?php echo strlen(trim(str_replace('<br>', '', str_replace('<br />', '', str_replace('&nbsp;', '', $help_text))))) > 0 ? $help_text : NBILL_STATIC_HELP_NONE; ?></div>
        <?php
    }

    protected static function getCaption($constant_prefix, $setting_name)
    {
        $caption = defined('NBILL_' . $constant_prefix . strtoupper($setting_name)) ? constant('NBILL_' . $constant_prefix . strtoupper($setting_name)) :
                    (defined($constant_prefix . strtoupper($setting_name)) ? constant($constant_prefix . strtoupper($setting_name)) : ucwords(str_replace('_', ' ', $setting_name))
                    );
        return $caption;
    }

    protected static function getHelpText($constant_prefix, $setting_name)
    {
        $help_text = defined('NBILL_INSTR_' . $constant_prefix . strtoupper($setting_name)) ? constant('NBILL_INSTR_' . $constant_prefix . strtoupper($setting_name)) :
                    (defined('NBILL_' . $constant_prefix . 'INSTR_' . strtoupper($setting_name)) ? constant('NBILL_' . $constant_prefix . 'INSTR_' . strtoupper($setting_name)) :
                    (defined('NBILL_' . $constant_prefix . strtoupper($setting_name) . '_HELP') ? constant('NBILL_' . $constant_prefix . strtoupper($setting_name) . '_HELP') :
                    (defined($constant_prefix . strtoupper($setting_name) . '_HELP') ? constant($constant_prefix . strtoupper($setting_name) . '_HELP') : ''
                    )));
        return $help_text;
    }

    public static function show_admin_setting_textbox($row, $setting_name, $constant_prefix = '', $css_class = '', $disable_in_demo_mode = false, $attributes = '')
    {
        ?>
        <tr id="nbill-admin-tr-<?php echo str_replace("_", "-", $setting_name); ?>">
            <td class="nbill-setting-caption">
                <?php echo self::getCaption($constant_prefix, $setting_name); ?>
            </td>
            <td class="nbill-setting-value">
                <input type="text" name="<?php echo $setting_name; ?>" value="<?php echo nbf_common::get_param($_REQUEST, $setting_name, $row->$setting_name, true); ?>" class="<?php echo array_search($setting_name, nbf_globals::$fields_in_error) !== false ? 'nbill-error ' : '' ?>inputbox<?php echo $css_class ? ' ' . $css_class : ''; ?>" <?php if ($disable_in_demo_mode && nbf_cms::$interop->demo_mode) {echo "disabled=\"disabled\" ";} if (strlen($attributes)) {echo ' ' . $attributes;} ?> />
                <?php self::show_static_help(self::getHelpText($constant_prefix, $setting_name), $setting_name . "_help"); ?>
            </td>
        </tr>
        <?php
    }

    public static function show_admin_setting_yes_no($row, $setting_name, $constant_prefix = '', $attributes = '', $tr_attributes = '')
    {
        ?>
        <tr<?php echo strlen($tr_attributes) > 0 ? ' ' . $tr_attributes : ''; ?> id="nbill-admin-tr-<?php echo str_replace("_", "-", $setting_name); ?>">
            <td class="nbill-setting-caption">
                <?php echo self::getCaption($constant_prefix, $setting_name); ?>
            </td>
            <td class="nbill-setting-value">
                <?php echo self::yes_or_no_options($setting_name, $attributes, $row->$setting_name); ?>
                <?php self::show_static_help(self::getHelpText($constant_prefix, $setting_name), $setting_name . "_help"); ?>
            </td>
        </tr>
        <?php
    }

    public static function show_admin_setting_dropdown($row, $setting_name, $kvp_values, $constant_prefix = '', $css_class = '', $disable_in_demo_mode = false, $attributes = '')
    {
        ?>
        <tr id="nbill-admin-tr-<?php echo str_replace("_", "-", $setting_name); ?>">
                <td class="nbill-setting-caption">
                    <?php echo self::getCaption($constant_prefix, $setting_name); ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    $item_list = array();
                    foreach ($kvp_values as $key=>$value) {
                        $item_list[] = nbf_html::list_option($key, $value);
                    }
                    $selected_item = nbf_common::get_param($_REQUEST, $setting_name, @$row->$setting_name, true);
                    echo nbf_html::select_list($item_list, $setting_name, (array_search($setting_name, nbf_globals::$fields_in_error) !== false ? 'nbill-error ' : '') . 'inputbox' . ($css_class ? ' ' . $css_class : '') . ' id="' . $setting_name . '"' . ($disable_in_demo_mode && nbf_cms::$interop->demo_mode ? ' disabled="disabled"' : '') . (strlen($attributes) > 0 ? ' ' . $attributes : ''), $selected_item);
                    self::show_static_help(self::getHelpText($constant_prefix, $setting_name), $setting_name . "_help"); ?>
                </td>
            </tr>
        <?php
    }

    public static function show_defined_date_ranges()
    {
        ?>
        <select name="defined_date_range" id="defined_date_range" onchange="document.getElementById('date_range_controls').style.display=this.options[this.selectedIndex].value=='specified_range'?'block':'none';if(this.options[this.selectedIndex].value!='specified_range'){document.adminForm.submit();}">
            <option value="current_month"<?php if (nbf_common::get_param($_REQUEST, 'defined_date_range')=='current_month'){echo ' selected="selected"';}?>><?php echo NBILL_RANGE_CURRENT_MONTH; ?></option>
            <option value="previous_month"<?php if (nbf_common::get_param($_REQUEST, 'defined_date_range')=='previous_month'){echo ' selected="selected"';}?>><?php echo NBILL_RANGE_PREVIOUS_MONTH; ?></option>
            <option value="current_and_previous_month"<?php if (nbf_common::get_param($_REQUEST, 'defined_date_range')=='current_and_previous_month'){echo ' selected="selected"';}?>><?php echo NBILL_RANGE_CURRENT_AND_PREVIOUS_MONTH; ?></option>
            <option value="current_quarter"<?php if (nbf_common::get_param($_REQUEST, 'defined_date_range')=='current_quarter'){echo ' selected="selected"';}?>><?php echo NBILL_RANGE_CURRENT_QUARTER; ?></option>
            <option value="previous_quarter"<?php if (nbf_common::get_param($_REQUEST, 'defined_date_range')=='previous_quarter'){echo ' selected="selected"';}?>><?php echo NBILL_RANGE_PREVIOUS_QUARTER; ?></option>
            <option value="current_and_previous_quarter"<?php if (nbf_common::get_param($_REQUEST, 'defined_date_range')=='current_and_previous_quarter'){echo ' selected="selected"';}?>><?php echo NBILL_RANGE_CURRENT_AND_PREVIOUS_QUARTER; ?></option>
            <option value="current_year"<?php if (nbf_common::get_param($_REQUEST, 'defined_date_range')=='current_year'){echo ' selected="selected"';}?>><?php echo NBILL_RANGE_CURRENT_YEAR; ?></option>
            <option value="previous_year"<?php if (nbf_common::get_param($_REQUEST, 'defined_date_range')=='previous_year'){echo ' selected="selected"';}?>><?php echo NBILL_RANGE_PREVIOUS_YEAR; ?></option>
            <option value="current_and_previous_year"<?php if (nbf_common::get_param($_REQUEST, 'defined_date_range')=='current_and_previous_year'){echo ' selected="selected"';}?>><?php echo NBILL_RANGE_CURRENT_AND_PREVIOUS_YEAR; ?></option>
            <option value="specified_range"<?php if (nbf_common::get_param($_REQUEST, 'defined_date_range')=='specified_range'){echo ' selected="selected"';}?>><?php echo NBILL_RANGE_SPECIFIED; ?></option>
        </select>
        <?php
    }
}

/**
* Represents an option in a select list
*/
class html_list_option
{
    /** @var mixed The value to return for the select list if this option is selected */
    public $value;
    /** @var string The display value */
    public $description;
    /** @var string Any extra HTML attributes for the option */
    public $attributes;
    /** @var boolean Whether or not this option is selected */
    public $selected;
    /** @var boolean Whether or not to render this as a radio option instead of a select list option */
    public $_radio = false;
    /** @var integer Index of the item in its parent array */
    public $_ordering;
    /** @var string Name of option group (for radio options only) */
    public $_group_name;

    /**
    * Render the option as an HTML tag (either a radio option or a select option)
    */
    public function __toString()
    {
        if ($this->_radio)
        {
            $option = '<input type="radio" class="nbill_form_input" value="' . $this->value . '"';
            $option .= ' name="' . $this->_group_name . '"';
            $option .= ' id="' . $this->_group_name . $this->_ordering . '"';
            if (nbf_common::nb_strlen($this->attributes) > 0)
            {
                $option .= ' ' . $this->attributes;
            }
            if ($this->selected)
            {
                $option .= ' checked="checked"';
            }
            $option .= ' />';
            $option .= '<label for="' . $this->_group_name . $this->_ordering . '" class="nbill_form_label">';
            $option .= $this->description;
            $option .= '</label>&nbsp;&nbsp;';
        }
        else
        {
            $option = '<option value="' . $this->value . '"';
            if (nbf_common::nb_strlen($this->attributes) > 0)
            {
                $option .= ' ' . $this->attributes;
            }
            if ($this->selected)
            {
                $option .= ' selected="selected"';
            }
            $option .= '>' . $this->description . '</option>';
        }
        return $option;
    }
}

class nbf_tab_group
{
    /** @var string Unique ID for this set of tabs (in case more than one group are on a page) */
    private $_group_id = "";
    /** @var string ID of the first tab rendered */
    private $_first_tab_id = "";
    /** @var string ID of the tab to be selected automatically */
    private $_selected_tab_id = "";

    public function set_default_selected_tab($selected_tab)
    {
        $this->_selected_tab_id = $selected_tab;
    }

    /**
    * Begin the output of a tabbed dialog
    * @param string $group_id Unique ID for this set of tabs (in case more than one group are on a page)
    * @param boolean Whether or not to collapse the tabs for narrow displays (less than 500px wide, but that could be changed by altering the nbill_tabs.css stylesheet)
    */
    public function start_tab_group($group_id, $responsive = false)
    {
    	$this->_group_id = $group_id;
        $this->_selected_tab_id = nbf_common::get_param($_REQUEST, 'nbill_selected_tab_' . $group_id);
        ob_start();
        ?>
        <script type="text/javascript">

        function select_tab_<?php echo $group_id; ?>(tab_id, force_select)
        {
            if (!document.getElementById(tab_id)) {
                //If selected using shortened form, pad it out to the full ID
                tab_id = 'nbill-tab-title-<?php echo $group_id; ?>-' + tab_id;
                if (!document.getElementById(tab_id)) {
                    return false;
                }
            }
            if (force_select || !window.nbill_disable_tabs) {
                if (tab_id && tab_id.length > 0)
                {
                    var page_id = tab_id.split('-').pop();

                    var divs = document.getElementsByTagName('div');
                    for(var i=0; i<divs.length; i++)
                    {
                        if (divs[i].id.indexOf('nbill-tab-title-<?php echo $group_id; ?>') > -1 && divs[i].id != tab_id)
                        {
                            this_tab_id = divs[i].id.split('-').pop();
                            divs[i].className = divs[i].className.replace(' selected', '');// 'nbill-tab-title';
                            if (document.getElementById('nbill-tab-content-<?php echo $group_id; ?>-' + this_tab_id)) {
                                document.getElementById('nbill-tab-content-<?php echo $group_id; ?>-' + this_tab_id).style.display = 'none';
                            }
                        }
                        if (document.getElementById('nbill-tab-content-<?php echo $group_id; ?>-' + page_id)) {
                            document.getElementById('nbill-tab-content-<?php echo $group_id; ?>-' + page_id).style.display = '';
                        }
                        if (document.getElementById(tab_id).className.indexOf(' selected') == -1) {
                            document.getElementById(tab_id).className += ' selected';
                        }
                    }
                    document.getElementById('nbill_selected_tab_<?php echo $group_id; ?>').value = tab_id;
                }

                if (window.onresize)
                {
                    setTimeout(function(){window.onresize();}, 200); //In case the change in content affects dynamically positioned elements (eg. footer)
                }
            }
        }

        </script>
        <?php $js_function = ob_get_clean();
        nbf_cms::$interop->add_html_header($js_function); ?>
        <div id="nbill-tab-group-<?php echo $group_id; ?>" class="nbill-tab-group<?php if ($responsive) {echo ' responsive-tabs';} ?>">
        <?php
    }

    /**
    * Add a new tab to the group
    * @param string $page_id Unique ID for this tab within the group
    * @param string $caption Caption to display on the tab
    * @param string $onclick_before Javascript to execute on click event before the tab is selected (NOTE: Always terminate this value with a semi-colon!)
    * @param string $onclick_after Javascript to exectue on click event after the tab is selected (NOTE: Always terminate this value with a semi-colon!)
    */
    public function add_tab_title($page_id, $caption, $onclick_before="", $onclick_after="", $css_class="", $attributes="")
    {
        if ($onclick_before) {
            $onclick_before = substr($onclick_before, strlen($onclick_before) - 1) == ";" ? $onclick_before : $onclick_before . ";";
        }
        ?>
        <div id="nbill-tab-title-<?php echo $this->_group_id; ?>-<?php echo $page_id; ?>" class="nbill-tab-title <?php echo $css_class; ?>" onclick="<?php echo $onclick_before; ?>select_tab_<?php echo $this->_group_id; ?>(this.id);<?php echo $onclick_after; ?>" <?php echo $attributes; ?>>
            <?php echo $caption; ?>
        </div>

        <?php
        if (nbf_common::nb_strlen($this->_selected_tab_id) == 0)
        {
            $this->_selected_tab_id = 'nbill-tab-title-' . $this->_group_id . '-' . $page_id;
        }
        if (nbf_common::nb_strlen($this->_first_tab_id) == 0)
        {
            $this->_first_tab_id = 'nbill-tab-title-' . $this->_group_id . '-' . $page_id;
        }
    }

    /**
    * Add the content for a given tab
    * @param string $page_id The ID of the tab, as specified when previously calling @see add_tab_title
    * @param string $content The content to display when this tab is selected
    */
    public function add_tab_content($page_id, $content)
    {
        ?>
        <div id="nbill-tab-content-<?php echo $this->_group_id; ?>-<?php echo $page_id; ?>" class="nbill-tab-content">
            <?php echo $content; ?>
        </div>
        <?php
    }

    /**
    * Finish off the tab page and select the first tab
    */
    public function end_tab_group()
    {
        ?>
        <input type="hidden" name="nbill_selected_tab_<?php echo $this->_group_id; ?>" id="nbill_selected_tab_<?php echo $this->_group_id; ?>" value="<?php echo $this->_selected_tab_id; ?>" />
        </div>
        <script type="text/javascript">
            if (window.nbill_disable_tabs)
            {
                select_tab_<?php echo $this->_group_id; ?>('<?php echo $this->_first_tab_id; ?>', true);
            }
            else
            {
                select_tab_<?php echo $this->_group_id; ?>('<?php echo $this->_selected_tab_id; ?>');
            }
        </script>
        <?php
    }
}