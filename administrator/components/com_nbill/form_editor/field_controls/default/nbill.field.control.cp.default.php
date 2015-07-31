<?php
/**
* nBill Passowrd Field Control Class file - for handling output and processing of passwords on forms.
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
* Password box
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_cp_default extends nbf_field_control
{
	/**
	* Renders the control
	*/
	protected function _render_control($admin = false)
	{
		?><input type="password" name="ctl_<?php echo $this->name . $this->suffix; ?>" id="ctl_<?php echo $this->id . $this->suffix; ?>" value="<?php echo defined($this->value) ? str_replace("\"", "&quot;", constant($this->value)) : str_replace("\"", "&quot;", $this->value); ?>" class="<?php echo $this->css_class; ?>" <?php if ($admin) { ?>onclick="<?php echo $this->onclick_admin; ?>" onchange="<?php echo $this->onchange_admin; ?>" <?php } echo $this->attributes; ?> /><?php
	}
    
    /**
    * Render the read-only value
    * @param mixed $admin
    */
    protected function _render_summary($admin = false)
    {
        echo str_repeat("*", nbf_common::nb_strlen($this->value));
    }
}