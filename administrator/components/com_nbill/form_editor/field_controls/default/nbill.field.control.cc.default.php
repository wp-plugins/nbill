<?php
/**
* nBill E-Mail Address Field Control Class file - for handling output and processing of email addresses on forms.
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
* Email address textbox
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_cc_default extends nbf_field_control
{
    /**
    * Make sure the value held is valid for this type of field
    * @param string $error_message If the value is not valid, this output parameter should be populated with an appropriate message
    * @return boolean Whether or not validation passed successfully
    */
    function validate(&$error_message)
    {
        $this->value = trim($this->value);
        //Based on unlicensed code in public domain from php.net user comments
        if (nbf_common::nb_strlen($this->value) > 0)
        {
            $regexp = "^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";
            if (preg_match("/$regexp/i", $this->value))
            {
                return true;
            }
            else
            {
                $error_message = NBILL_EMAIL_NOT_VALID;
                return false;
            }
        }
        return true;
    }
}