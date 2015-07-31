<?php
/**
* nBill Field Control Class file - Base class for handling output and processing of field controls on forms.
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* By default, this will output a standard text box. Further functionality can be added by overriding this class
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_default
{
    static $default_height_allowance = 35; //Default amount of height to allow for a field (including spacing)
    static $default_spacing_offset = 10; //Default spacing between fields

    /** @var string Name of the field */
	var $name = "";
    /** @var mixed Unique identifier for the form this field appears on (typically the ID number as held in the database) */
    var $form_id;
	/** @var mixed Unique identifier for the field (typically the ID number as held in the database) */
	var $id = "";
	/** @var string Suffix to be appended to field name/id (typically used when adding a duplicate control for confirmation) */
	var $suffix = "";
	/** @var string Initial value to populate the field with */
	var $default_value = null;
    /** @var string Value assigned to the control at run-time (to be validated) */
    var $value = null;
    /** @var string Field label (for use in validation error messages) */
    var $label = "";
	/** @var string Name of CSS class to assign to the control */
	var $css_class = "nbill_control";
	/** @var string Javascript to execute on click event in admin form editor (not website front end) */
	var $onclick_admin = "if(!check_cancel_click(this, '[[id]]')){return false;}";
	/** @var string Javascript to execute on change event in admin form editor (not website front end) */
	var $onchange_admin = "update_default_value(this, '[[id]]');";
	/** @var string Javascript to execute on keydown event in admin form editor (not website front end) */
	var $onkeydown_admin = "stop_bubble(event);";
	/** @var string Any additional HTML attributes to add to the control - eg. 'style="width:200px;"' */
	var $attributes = "";
	/** @var boolean Whether or not this field is mandatory */
	var $required = false;
	/** @var string Text to show to the right of the checkbox (if applicable) */
	var $checkbox_text = "";
	/** @var boolean Whether or not to render the options horizontally (on one line) rather than vertically (each option on a new line), if applicable */
	var $horizontal_options = false;
	/** @var boolean Whether or not this field allows multiple choice options to be selected (typically only for dropdown lists or option lists) */
	var $support_options = false;
	/** @var array Options (if applicable - eg. select lists, radio lists) */
	var $field_options = array();
	/** @var string There are places where fields are treated differently depending on the type of html control used (mainly those that do not use the value property to set/unset their value, such as checkboxes, dropdowns, and radio lists). If you the control type is treated differently to a text box, the value here will be used to determine how to set/unset the values and possibly other behaviours. */
	var $html_control_type = 'AA';
	/** @var int How much height to allow for this control by default */
	var $height_allowance = 30;
	/** @var int How much height to allow for each option (if displayed vertically) */
	var $option_height_allowance = 0;
    /** @var boolean Whether or not to show a label next to the field by default (user can still add one if required, but for some controls it might not make sense so should be suppressed by default - eg. login box, summary table) */
    var $show_label_by_default = true;
    /** @var boolean Whether or not the field is being rendered in legacy mode (ie. in a table instead of absolutely positioned) */
    var $renderer = 2;
    /** @var array List of extended parameter definition objects */
    var $extended_params = null;
	/** @var string Holds onclick value including tokens during rendering */
	private $_onclick_admin = "if(!check_cancel_click(this, '[[id]]')){return false;}";
	/** @var string Holds onchange value including tokens during rendering */
	private $_onchange_admin = "update_default_value(this, '[[id]]');";
	/** @var string Holds the default value including any PHP code */
	private $_default_value = null;

    /**
    * @param mixed $id Field id
    * @return nbf_field_control
    */
    public function __construct($form_id, $id)
    {
        $this->form_id = $form_id;
        $this->id = $id;
        $this->height_allowance = self::$default_height_allowance;
    }

    public function set_extended_params($param_string)
    {
        $params = explode("\n", $param_string);
        $params = array_filter($params);
        foreach ($params as $param)
        {
            $param_parts = explode("=", $param);
            if (count($param_parts) == 2)
            {
                foreach ($this->extended_params as &$extended_param)
                {
                    if ($extended_param->param_name == $param_parts[0])
                    {
                        $extended_param->param_value = $param_parts[1];
                        if (property_exists($this, $param_parts[0]))
                        {
                            $this->$param_parts[0] = $param_parts[1];
                        }
                    }
                }
            }
        }
    }

	/**
	* This is the function that is called by nBill to output the control in the form editor. Generally you
	* would not need to override this, as the actual control is output in the render_admin function, which
	* is where you would typically override
    * @param boolean $admin Whether or not we are rendering in the back-end form editor
	*/
    function render_control($admin = false, $on_current_page = true)
	{
        if (!$on_current_page) {
            //Don't allow disabled attribute, otherwise values could be lost on postback - disabled not needed as field not visible anyway
            $this->attributes = str_replace('disabled="disabled"', '', $this->attributes);
            $this->attributes = str_replace('disabled = "disabled"', '', $this->attributes);
            $this->attributes = str_replace('disabled ="disabled"', '', $this->attributes);
            $this->attributes = str_replace('disabled= "disabled"', '', $this->attributes);
        }
		$this->_pre_render($admin);
		$this->_render_control($admin);
		$this->_post_render($admin);
	}

	/**
	* Performs any tasks that need processing immediately before the control is rendered
	*/
	protected function _pre_render($admin = false)
	{
        //Make a note of the original values of events, including tokens/PHP code
        if ($admin)
        {
		    $this->_onclick_admin = $this->onclick_admin;
		    $this->_onchange_admin = $this->onchange_admin;
        }
		$this->_default_value = $this->default_value;

		//Replace any tokens with their actual values, if applicable
        if ($admin)
        {
		    if (nbf_common::nb_strpos($this->onclick_admin, "[[") !== false && nbf_common::nb_strpos($this->onclick_admin, "]]") !== false)
		    {
			    $token = substr($this->onclick_admin, nbf_common::nb_strpos($this->onclick_admin, "[[") + 2, (nbf_common::nb_strpos($this->onclick_admin, "]]") - nbf_common::nb_strpos($this->onclick_admin, "[[")) - 2);
			    if (property_exists($this, $token))
			    {
				    $this->onclick_admin = str_replace("[[" . $token . "]]", $this->$token, $this->onclick_admin);
			    }
		    }
		    if (nbf_common::nb_strpos($this->onchange_admin, "[[") !== false && nbf_common::nb_strpos($this->onchange_admin, "]]") !== false)
		    {
			    $token = substr($this->onchange_admin, nbf_common::nb_strpos($this->onchange_admin, "[[") + 2, (nbf_common::nb_strpos($this->onchange_admin, "]]") - nbf_common::nb_strpos($this->onchange_admin, "[[")) - 2);
			    if (property_exists($this, $token))
			    {
				    $this->onchange_admin = str_replace("[[" . $token . "]]", $this->$token, $this->onchange_admin);
			    }
		    }
        }

		//Execute any code needed to retrieve the default value
        $this->default_value = nbf_common::parse_and_execute_code($this->default_value);
		if ($this->value === null)
        {
            $this->value = $this->default_value;
        }
	}

	/**
	* Renders the control
	*/
	protected function _render_control($admin = false)
	{
		?><input type="text" name="ctl_<?php echo $this->name . $this->suffix; ?>" id="ctl_<?php echo $this->id . $this->suffix; ?>" value="<?php echo defined($this->value) ? str_replace("\"", "&quot;", constant($this->value)) : str_replace("\"", "&quot;", $this->value); ?>" class="<?php echo $this->css_class; ?>" <?php if ($admin) { ?>onclick="<?php echo $this->onclick_admin; ?>" onchange="<?php echo $this->onchange_admin; ?>" <?php } echo $this->attributes; ?> /><?php
	}

    /**
	* Performs any tasks that need processing immediately after the control is rendered in the
	* admin form editor
	*/
	protected function _post_render($admin = false)
	{
		//Set event properties back to their original values
        if ($admin)
        {
		    $this->onclick_admin = $this->_onclick_admin;
		    $this->onchange_admin = $this->_onchange_admin;
        }
		$this->default_value = $this->_default_value;
	}

    /**
    * Output the value read-only (for the order summary page)
    */
    function render_summary($admin = false)
    {
        $this->_pre_render($admin);
        $this->_render_summary($admin);
        $this->_post_render($admin);
    }

    /**
    * Render the read-only value
    * @param mixed $admin
    */
    protected function _render_summary($admin = false)
    {
        echo $this->value;
    }

    /**
    * If some calculation needs to be performed to store the value based on the values of one or more HTML controls in the field,
    * this function can be used to perform that processing (ie. to set $this->value to the correct value for whatever was posted)
    */
    function register_value()
    {
        //Override if required
    }

    /**
    * Return the value to use for determining whether or not an associated product should be ordered
    */
    function get_product_value()
    {
        //Override if required
        return $this->value;
    }

    /**
    * Make sure the value held is valid for this type of field
    * @param string $error_message If the value is not valid, this output parameter should be populated with an appropriate message
    * @return boolean Whether or not validation passed successfully
    */
    function validate(&$error_message)
    {
        return true;
    }

    /**
    * Perform any custom processing that should occur if the form is posted without moving to a new page (eg. when a process button is clicked)
    * If the processing should only occur if a particular button is clicked, make sure to check that the required button was clicked, as this
    * method will be called for ALL fields whenever ANY ONE of them triggers a postback.
    * @param string $message This should be populated with any feedback to the user
    */
    public function process(&$message)
    {
        //Override if required
    }

    /**
    * Perform any custom processing that should occur when the page is submitted (we could be moving on to the next page, or submitting
    * the entire form - if the latter, the form_submit function will also be called)
    * @param string $message This should be populated with any feedback to the user
    * @return boolean Whether or not to allow the page submission (if false, the user will be returned to the page that this field appears on)
    */
    public function page_submit(&$message)
    {
        //Override if required
        return true;
    }

    /**
    * Perform any custom processing that should occur when the entire form is submitted
    * @param string $message This should be populated with any feedback to the user
    * @return boolean Whether or not to allow the form submission (if false, the user will be returned to the page that this field appears on)
    */
    public function form_submit(&$message)
    {
        //Override if required
        return true;
    }
}

class extended_param
{
    /** @var string Name of parameter */
    public static $param_name = '';
    /** @var string Label to show in field properties pane */
    public static $param_label = '';
    /** @var string Help text to show in field properties pane */
    public static $param_help = '';
    /** @var string Data type (boolean, integer, decimal[places], varchar, text, select) */
    public static $param_type = 'varchar';
    /** @var mixed Default value of parameter */
    public static $param_value = '';
    /** @var string Comma separated list of options (for dropdown lists) */
    public static $param_options = '';
}