<?php
/**
* nBill Security Image Control Class file - for handling output and processing of captcha security images on forms.
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
* Captcha-style security image
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_ss_default extends nbf_field_control
{
	/**
	* Override default values
	*/
	public function __construct($form_id, $id)
	{
        parent::__construct($form_id, $id);
		$this->height_allowance = 57;
	}

	/**
	* Renders the control
	*/
	protected function _render_control($admin = false)
	{
		?><a name="secimage_<?php echo $this->name . $this->suffix; ?>"></a>
        <div style="float:left"><img style="border: solid 2px #bbbbbb;" src="<?php echo nbf_cms::$interop->live_site; ?>/<?php echo nbf_cms::$interop->site_page_prefix; ?>&action=orders&task=captcha_image" alt="Random letters" id="captcha_<?php echo $this->name . $this->suffix; ?>" width="150" height="50" /></div>
        <div style="float:left"><input style="float:none" type="text" name="ctl_<?php echo $this->name . $this->suffix; ?>" id="ctl_<?php echo $this->id . $this->suffix; ?>" value="<?php echo defined($this->value) ? str_replace("\"", "&quot;", constant($this->value)) : str_replace("\"", "&quot;", $this->value); ?>" class="<?php echo $this->css_class; ?>" style="vertical-align:top" <?php if ($admin) { ?>onclick="<?php echo $this->onclick_admin; ?>" onchange="<?php echo $this->onchange_admin; ?>" <?php } echo $this->attributes; ?> size="10" maxlength="5" /><br />
        <a href="#secimage_<?php echo $this->name . $this->suffix; ?>" onclick="var e=document.getElementById('captcha_<?php echo $this->name . $this->suffix; ?>');dv=new Date();e.src='<?php echo nbf_cms::$interop->live_site; ?>/<?php echo nbf_cms::$interop->site_page_prefix; ?>&action=orders&task=captcha_image&dummy='+dv.getTime();return false;"><?php echo NBILL_SECURITY_IMAGE_CHANGE; ?></a></div>
		<?php
	}

    /**
    * Make sure the value held is valid for this type of field
    * @param string $error_message If the value is not valid, this output parameter should be populated with an appropriate message
    * @return boolean Whether or not validation passed successfully
    */
    function validate(&$error_message)
    {
        include_once(nbf_cms::$interop->nbill_fe_base_path . "/captcha/verify.php");
        $success = check_captcha($this->value);
        unset($_SESSION['captcha_string']);
        if (!$success)
        {
            $error_message = NBILL_ERR_SECURITY_IMAGE_WRONG;
            return false;
        }
        return true;
    }
}