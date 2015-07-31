<?php
/**
* nBill Submit button Control Class file - for handling output and processing of submit buttons on forms.
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
* Submit button
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_nn_default extends nbf_field_control
{
	/**
	* Renders the control
	*/
	protected function _render_control($admin = false)
	{
        ?><input type="<?php echo $admin ? 'button' : 'submit'; ?>" name="ctl_<?php echo $this->name . $this->suffix; ?>" id="ctl_<?php echo $this->id . $this->suffix; ?>" value="<?php echo defined($this->value) ? constant($this->value) : $this->value; ?>" class="button btn" <?php if ($admin) {?>onclick="<?php echo $this->onclick_admin; ?>if (this.blur){this.blur();}return false;" <?php } echo $this->attributes; ?> /><?php
	}
}