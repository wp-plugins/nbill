<?php
/**
* nBill Date Field Control Class file - for handling output and processing of dates on forms.
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

include_once(realpath(dirname(__FILE__)) . "/../custom/nbill.field.control.base.php");

/**
* Outputs a calendar
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_gz_default extends nbf_field_control
{
    protected function _pre_render($admin = false)
    {
        static $dhtml_goodies_js_loaded = false;
        parent::_pre_render($admin);
        if (!$admin && !$dhtml_goodies_js_loaded)
        {
            ob_start();
            $css = file_get_contents(nbf_cms::$interop->nbill_fe_base_path . "/calendar/dhtmlgoodies_calendar/dhtmlgoodies_calendar.css");
            echo '<style type="text/css">' . $css . '</style>';
            ?>
            <script type="text/javascript">
                /*<![CDATA[*/
                var pathToImages = '<?php echo nbf_cms::$interop->nbill_site_url_path;?>/calendar/images/';
                /* ]]> */
            </script>
            <?php
            $js = file_get_contents(nbf_cms::$interop->nbill_fe_base_path . "/calendar/dhtmlgoodies_calendar/dhtmlgoodies_calendar.js");
            echo '<script type="text/javascript">' . "\n/*<![CDATA[*/\n" . $js . "\n/*]]>*/\n" . '</script>';
            $header_info = ob_get_clean();
            nbf_cms::$interop->add_html_header($header_info);
        }
    }
    
    /**
	* Renders the control
	*/
	protected function _render_control($admin = false)
	{
		$cal_date_format = nbf_common::get_date_format(true);
		?><input type="text" name="ctl_<?php echo $this->name . $this->suffix; ?>" id="ctl_<?php echo $this->id . $this->suffix; ?>" value="<?php echo defined($this->value) ? constant($this->value) : $this->value; ?>" <?php if ($admin) { ?>onclick="<?php echo $this->onclick_admin; ?>" onchange="<?php echo $this->onchange_admin; ?>" <?php } echo $this->attributes; ?> />
		<span id="nbill_cal_js_only_<?php echo $this->id . $this->suffix; ?>" style="display:none;">
            <script type="text/javascript">document.getElementById('ctl_<?php echo $this->id . $this->suffix; ?>').readOnly = true;</script><?php //Done with javascript so that non-js browsers will allow manual entry of date ?>
            <input type="button" name="ctl_<?php echo $this->name . $this->suffix; ?>_cal" class="button btn" value="<?php echo NBILL_FIELD_CALENDAR; ?>" onclick="<?php echo $admin ? $this->onclick_admin : ''; ?> displayCalendar(document.getElementById('ctl_<?php echo $this->id; ?>'),'<?php echo $cal_date_format; ?>',this);" />
		    <?php
		    if (!$this->required)
		    { ?>
			    <input type="button" name="ctl_<?php echo $this->name . $this->suffix; ?>_clear" class="button btn" value="<?php echo NBILL_FIELD_CALENDAR_CLEAR; ?>" onclick="<?php echo $admin ? $this->onclick_admin : ''; ?> document.getElementById('ctl_<?php echo $this->id . $this->suffix; ?>').value = '';if (document.getElementById('ctl_<?php echo $this->id . $this->suffix; ?>').onchange){document.getElementById('ctl_<?php echo $this->id . $this->suffix; ?>').onchange();}" />
		    <?php } ?>
        </span>
        <script type="text/javascript">document.getElementById('nbill_cal_js_only_<?php echo $this->id . $this->suffix; ?>').style.display='';</script>
        <?php
	}
    
    /**
    * Make sure the value held is valid for this type of field
    * @param string $error_message If the value is not valid, this output parameter should be populated with an appropriate message
    * @return boolean Whether or not validation passed successfully
    */
    function validate(&$error_message)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
        
        if (nbf_common::nb_strlen($this->value) > 0)
        {
            $valid_date = false;
            $date_parts = nbf_date::get_date_parts($this->value, nbf_common::get_date_format(true));
            if (count($date_parts) == 3)
            {
                if (is_numeric($date_parts['d']) && is_numeric($date_parts['m']) && is_numeric($date_parts['y']))
                {
                    if ($date_parts['y'] > 999 && $date_parts['y'] < 2999)
                    {
                        if ($date_parts['m'] > 0 && $date_parts['m'] < 13)
                        {
                            $max_day = 31;
                            switch ($date_parts['m'])
                            {
                                case 2:
                                    $max_day = 29;
                                    break;
                                case 4:
                                case 6:
                                case 9:
                                case 11:
                                    $max_day = 30;
                                    break;
                            }
                            if ($date_parts['d'] > 0 && $date_parts['d'] <= $max_day)
                            {
                                $valid_date = true;
                            }
                        }
                    }
                }
            }
            if (!$valid_date)
            {
                $error_message = sprintf(NBILL_DATE_NOT_VALID, $this->label, nbf_common::get_date_format(true));
                return false;
            }
        }
        return true;
    }
}