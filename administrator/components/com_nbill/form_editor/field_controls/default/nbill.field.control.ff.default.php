<?php
/**
* nBill Textarea Field Control Class file - for handling output and processing of textareas on forms.
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
* Textarea
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_ff_default extends nbf_field_control
{
	/**
	* Override default values
	*/
	public function __construct($form_id, $id)
	{
        parent::__construct($form_id, $id);
		$this->height_allowance = 58;
	}

	/**
	* Renders the control
	*/
	protected function _render_control($admin = false)
	{
		?><textarea name="ctl_<?php echo $this->name . $this->suffix; ?>" id="ctl_<?php echo $this->id . $this->suffix; ?>" class="nbill_control" <?php if ($admin) { ?>onclick="<?php echo $this->onclick_admin; ?>" onchange="<?php echo $this->onchange_admin; ?>" <?php } echo $this->attributes; ?>><?php echo defined($this->value) ? constant($this->value) : $this->value; ?></textarea><?php
	}
    
    /**
    * Render the read-only value
    * @param mixed $admin
    */
    protected function _render_summary($admin = false)
    {
        $summary_value = str_replace("\r\n", "\n", $this->value);
        $summary_value = str_replace("\r", "\n", $summary_value);
        $summary_value = str_replace("\n", "<br />", $summary_value);
        echo $summary_value;
    }
}