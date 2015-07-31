<?php
/**
* nBill Hidden Field Control Class file - for handling output and processing of hidden fields on forms.
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
* Outputs a hidden field
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_hh_default extends nbf_field_control
{
    /**
    * Override default values
    */
    public function __construct($form_id, $id)
    {
        parent::__construct($form_id, $id);
        $this->height_allowance = 0;
        $this->html_control_type = "HH";
        $this->support_options = true;
    }
    
	/**
	* Renders the control
	*/
	protected function _render_control($admin = false)
	{
		?><input <?php if ($admin) { ?>type="text"<?php } else { ?>type="hidden"<?php } ?> name="ctl_<?php echo $this->name . $this->suffix; ?>" id="ctl_<?php echo $this->id . $this->suffix; ?>" value="<?php echo defined($this->value) ? str_replace("\"", "&quot;", constant($this->value)) : str_replace("\"", "&quot;", $this->value); ?>" class="nbill_hidden_field" <?php if ($admin) { ?>onclick="<?php echo $this->onclick_admin; ?>" onchange="<?php echo $this->onchange_admin; ?>" <?php } echo $this->attributes; ?> /><?php
	}
}