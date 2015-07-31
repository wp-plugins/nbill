<?php
/**
* nBill Radio List Field Control Class file - for handling output and processing of radio lists on forms.
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
* Radio list
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_dd_default extends nbf_field_control
{
	/**
    * As this control supports multiple choice options, we need to indicate that in the constructor
    */
    public function __construct($form_id, $id)
    {
        parent::__construct($form_id, $id);
        $this->support_options = true;
        $this->html_control_type = 'DD';
        $this->option_height_allowance = 18;
    }

    /**
    * Renders the control
    */
    protected function _render_control($admin = false)
    {
        ?><div id="radio_list_container_<?php echo $this->id . $this->suffix; ?>" style="float:left"><?php
        //If value does not match any codes, but does match a description, use that
        $code_match_found = false;
        $desc_match = "";
        foreach ($this->field_options as $field_option)
        {
            if ((is_array($this->value) && array_search($field_option->code, $this->value) !== false) ||
                    (!is_array($this->value) && ($this->value == $field_option->code || @html_entity_decode($this->value, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) == $field_option->code || @html_entity_decode($this->value, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) == @utf8_decode($field_option->code))))
            {
                $this->value = $field_option->code; //In case it has been sanitised and no longer matches exactly due to html entity encoding
                $code_match_found = true;
                break;
            }
            else
            {
                if ((is_array($this->value) && array_search($field_option->description, $this->value) !== false) || (!is_array($this->value) && ($this->value == $field_option->description || @html_entity_decode($this->value, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) == $field_option->description) || @html_entity_decode($this->value, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) == @utf8_decode($field_option->description)))
                {
                    if (is_array($this->value))
                    {
                        $desc_match[] = $field_option->code;
                    }
                    else
                    {
                        $desc_match = $field_option->code;
                    }
                }
            }
        }
        if (!$code_match_found && $desc_match)
        {
            $this->value = $desc_match;
        }
        $j = 0;
        foreach ($this->field_options as $field_option)
        {
            if (!$this->horizontal_options && !$this->renderer) { ?><div class="nbill_radio_vertical"><?php } ?>
            <label class="nbill_form_label"><input type="radio" class="nbill_form_input" name="ctl_<?php echo $this->name . $this->suffix; ?>" id="ctl_<?php echo $this->id . $this->suffix; ?>_<?php echo $field_option->code; ?>" value="<?php echo $field_option->code; ?>" class="nbill_radio"<?php if ((is_array($this->value) && array_search($field_option->code, $this->value) !== false) || (!is_array($this->value) && $this->value == $field_option->code)) {echo " checked=\"checked\"";} ?> <?php if ($admin) { ?>onclick="<?php echo $this->onclick_admin . $this->onchange_admin; ?>" <?php } echo $this->attributes; ?> /><?php echo $field_option->description; ?></label>
            <?php if ($this->horizontal_options) {?>&nbsp; <?php } else { if (!$this->renderer) { ?></div><?php } else {echo "<br />";} $j++; }
        }
        ?></div><?php
    }

    /**
    * Render the read-only value
    * @param mixed $admin
    */
    protected function _render_summary($admin = false)
    {
        foreach ($this->field_options as $option)
        {
            if ($this->value == $option->code)
            {
                echo $option->description;
                return;
            }
        }
        echo $this->value;
    }
}