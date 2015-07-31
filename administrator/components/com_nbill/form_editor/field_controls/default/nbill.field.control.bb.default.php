<?php
/**
* nBill Dropdown List Field Control Class file - for handling output and processing of dropdown lists on forms.
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
* Dropdown list
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_bb_default extends nbf_field_control
{
	/**
	* As this control supports multiple choice options, we need to indicate that in the constructor
	*/
	public function __construct($form_id, $id)
	{
        parent::__construct($form_id, $id);
		$this->support_options = true;
		$this->html_control_type = 'BB';
	}

	/**
	* Renders the control
	*/
	protected function _render_control($admin = false)
    {
        ?><select name="ctl_<?php echo $this->name . $this->suffix . (nbf_common::nb_strpos($this->attributes, 'multiple') !== false ? '[]' : ''); ?>" id="ctl_<?php echo $this->id . $this->suffix; ?>" class="<?php echo $this->css_class; ?>" <?php if ($admin){?>onclick="<?php echo $this->onclick_admin . $this->onchange_admin; ?>" <?php } echo $this->attributes; ?>>
        <?php
        //If value does not match any codes, but does match a description, use that
        $code_match_found = false;
        $desc_match = "";
        foreach ($this->field_options as $field_option)
        {
            if ((is_array($this->value) && array_search($field_option->code, $this->value) !== false) ||
                    (!is_array($this->value) && ($this->value == $field_option->code || @html_entity_decode($this->value, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) == $field_option->code || @html_entity_decode($this->value, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) == @utf8_decode($field_option->code))))
            {
                if (!is_array($this->value))
                {
                    $this->value = $field_option->code; //In case it has been sanitised and no longer matches exactly due to html entity encoding
                }
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
        foreach ($this->field_options as $field_option)
        { ?>
            <option value="<?php echo $field_option->code; ?>"<?php if ((is_array($this->value) && array_search($field_option->code, $this->value) !== false) || (!is_array($this->value) && $this->value == $field_option->code)) {echo " selected=\"selected\"";} ?>><?php echo $field_option->description; ?></option>
            <?php
        }
        ?>
        </select><?php
    }

    /**
    * Render the read-only value
    * @param mixed $admin
    */
    protected function _render_summary($admin = false)
    {
        if (is_array($this->value))
        {
            $output = array();
            foreach ($this->value as $selected_value)
            {
                foreach ($this->field_options as $option)
                {
                    if ($selected_value == $option->code)
                    {
                        $output[] = $option->description;
                        continue 2;
                    }
                }
                $output[] = $selected_value;
            }
            echo implode(", ", $output);
        }
        else
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
}