<?php
/**
* nBill Label Control Class file - for handling output and processing of labels on forms.
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
* Label
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_ll_default extends nbf_field_control
{
	/**
	* Labels are treated differently because they do not use a value property to set/unset
	*/
	public function __construct($form_id, $id)
	{
        parent::__construct($form_id, $id);
		$this->html_control_type = 'LL';
	}

	/**
	* Renders the control
	*/
	protected function _render_control($admin = false)
	{
		?><div id="ctl_<?php echo $this->id . $this->suffix; ?>" style="float:left;"><?php echo defined($this->value) ? constant($this->value) : $this->value; ?></div><?php
	}
}