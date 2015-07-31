<?php
/**
* nBill Login Box Class file - for handling output and processing of login box on forms.
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
* Login box
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_oo_default extends nbf_field_control
{
    public $show_title = true;

    /**
    * Override default values
    */
    public function __construct($form_id, $id, $admin = false)
    {
        parent::__construct($form_id, $id);
        $this->html_control_type = 'FF'; //Textarea (used to handle default value, which is used as the intro text)
        $this->show_label_by_default = false;
        if (defined("NBILL_ADMIN") || $admin || !nbf_cms::$interop->user->id)
        {
            $this->height_allowance = 180;
        }
        else
        {
            $this->height_allowance = 30;
        }
        $this->attributes = 'style="border: solid 1px #cccccc;margin-left:auto;margin-right:auto;margin-bottom:10px;"';
    }

    /**
    * Renders the control
    */
    protected function _render_control($admin = false)
    {
        if ($admin || !nbf_cms::$interop->user->id)
        {
            ?>
            <div class="nbill-login-box-outer">
                <div class="nbill-login-box-inner">
                    <?php if ($this->show_title) { ?><div id="nbill_login_title_<?php echo $this->id; ?>"><?php echo NBILL_ALREADY_REGISTERED; ?></div><?php } ?>
                    <div style="display:none;"><textarea name="ctl_<?php echo $this->name; ?>" id="ctl_<?php echo $this->id; ?>" onchange="document.getElementById('nbill_login_value_<?php echo $this->id; ?>').innerHTML=this.value;"><?php echo defined($this->value) ? constant($this->value) : $this->value; ?></textarea></div>
                    <table cellpadding="3" cellspacing="0" border="0" class="nbill-login-box" <?php echo $this->attributes; ?>>
                        <tr>
                            <td style="padding:5px;text-align:left;"><?php echo NBILL_USER_NAME; ?></td><td style="padding:5px;"><input type="text" name="NBILL_LOGIN_username_<?php echo $this->id; ?>" id="NBILL_LOGIN_username_<?php echo $this->id; ?>" style="width:150px;"<?php if ($admin) {?> onfocus="this.blur();"<?php } ?> /></td>
                        </tr>
                        <tr>
                            <td style="padding:5px;text-align:left;"><?php echo NBILL_USER_PASSWORD; ?></td><td style="padding:5px;"><input type="password" name="NBILL_LOGIN_password_<?php echo $this->id; ?>" id="NBILL_LOGIN_password_<?php echo $this->id; ?>" style="width:150px;"<?php if ($admin) { ?> onfocus="this.blur();"<?php } ?> /></td>
                        </tr>
                        <tr>
                            <td style="padding:5px;text-align:left;" align="left"><a href="<?php echo nbf_cms::$interop->get_lost_password_link(); ?>"><?php echo NBILL_LOST_PASSWORD; ?></a></td>
                            <td style="padding:5px;text-align:right;" align="right">
                                <?php echo nbf_cms::$interop->get_login_spoof_checker(); ?>
                                <input type="submit" name="NBILL_login_submit_<?php echo $this->id; ?>" value="<?php echo NBILL_LOGIN; ?>" class="button btn nbill-button" id="NBILL_LOGIN_button_<?php echo $this->id; ?>"<?php if ($admin) { ?> onclick="return false;"<?php } ?> />
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php
        }
    }

    /**
    * Attempt to login with the given username and password
    * @param mixed $message Returns error message if login not successful
    */
    function process(&$message)
    {
        if (nbf_common::get_param($_REQUEST, 'NBILL_login_submit_' . $this->id))
        {
            if (!nbf_cms::$interop->user->id && strlen(nbf_common::get_param($_REQUEST, 'NBILL_LOGIN_username_' . $this->id)) > 0 && strlen(nbf_common::get_param($_REQUEST, 'NBILL_LOGIN_password_' . $this->id)) > 0)
            {
                nbf_cms::$interop->login(nbf_common::get_param($_REQUEST, 'NBILL_LOGIN_username_' . $this->id), nbf_common::get_param($_REQUEST, 'NBILL_LOGIN_password_' . $this->id));
                if (!nbf_cms::$interop->user->id)
                {
                    $message = NBILL_LOGIN_FAILED;
                }
            }
        }
    }
}