<?php
/**
* nBill Checkbox Field Control Class file - for handling output and processing of checkboxes on forms.
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
* Checkbox
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_ee_default extends nbf_field_control
{
	/**
	* Checkboxes are treated differently because they do not use a value property to set/unset
	*/
	public function __construct($form_id, $id)
	{
        parent::__construct($form_id, $id);
		$this->html_control_type = 'EE';
	}

	/**
	* Renders the control in the admin form editor
	*/
	protected function _render_control($admin = false)
	{
        //Output hidden field first so that unchecked checkboxes are still returned in the posted data, but as an empty string
		?><input type="hidden" name="ctl_<?php echo $this->name . $this->suffix; ?>" value="" /><input class="nbill_form_input" type="checkbox" name="ctl_<?php echo $this->name . $this->suffix; ?>" id="ctl_<?php echo $this->id . $this->suffix; ?>"<?php echo $this->value ? " checked=\"checked\"" : ""; ?> class="nbill_control nbill_checkbox" value="On" <?php if ($admin) { ?>onclick="<?php echo $this->onclick_admin . $this->onchange_admin; ?>" <?php } echo $this->attributes; ?> />
		<label class="nbill_form_label" for="ctl_<?php echo $this->id . $this->suffix; ?>" id="chk_txt_<?php echo $this->id . $this->suffix; ?>"><?php
		echo $this->checkbox_text ? ((defined(str_replace("* ", "", $this->checkbox_text)) ? (nbf_common::nb_strpos($this->checkbox_text, "* ") !== false ? "* " : "") . constant(str_replace("* ", "", $this->checkbox_text)) : $this->checkbox_text)) : "";
	    ?></label><?php
	}

    /**
    * Render the read-only value
    * @param mixed $admin
    */
    protected function _render_summary($admin = false)
    {
        echo nbf_common::nb_strtolower($this->value) == "on" ? NBILL_YES : NBILL_NO;
    }
}