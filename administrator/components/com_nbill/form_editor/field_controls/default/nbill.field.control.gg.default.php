<?php
/**
* nBill Numeric Field Control Class file - for handling output and processing of numeric boxes on forms.
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
* Numeric textbox
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_gg_default extends nbf_field_control
{
	/**
	* Renders the control
	*/
	protected function _render_control($admin = false)
	{
		?><input type="text" name="ctl_<?php echo $this->name . $this->suffix; ?>" id="ctl_<?php echo $this->id . $this->suffix; ?>" value="<?php echo defined($this->value) ? constant($this->value) : $this->value; ?>" class="<?php echo $this->css_class; ?>" <?php if ($admin) { ?>onclick="<?php echo $this->onclick_admin; ?>" onchange="<?php echo $this->onchange_admin; ?>" <?php } echo $this->attributes; ?> size="12"<?php if (nbf_common::nb_strpos($this->attributes, 'style') === false) {echo ' style="text-align:right;width:100px;"';} ?> /><?php
	}
    
    /**
    * Make sure the value held is valid for this type of field
    * @param string $error_message If the value is not valid, this output parameter should be populated with an appropriate message
    * @return boolean Whether or not validation passed successfully
    */
    function validate(&$error_message)
    {
        $this->value = str_replace(",", "", $this->value);
        if (nbf_common::nb_strlen($this->value) == 0 || is_numeric($this->value))
        {
            return true;
        }
        else
        {
            $error_message = sprintf(NBILL_NUMERIC_ONLY, $this->label);
            return false;
        }
    }
}