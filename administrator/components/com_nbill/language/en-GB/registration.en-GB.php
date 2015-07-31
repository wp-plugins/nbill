<?php
/**
* Language file for the registration (license key) page
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
* 
* @access private* 
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Configuration
define("NBILL_REGISTRATION_TITLE", "Registration");
define("NBILL_REG_LICENSE_KEY", "License Key");
define("NBILL_REG_INSTR_LICENSE_KEY", "If you wish to update your license key before it expires, you can enter a new one here. WARNING! If you enter an incorrect value here, it could prevent you from using " . NBILL_BRANDING_NAME . "! Only enter a valid license key, and copy it EXACTLY. By entering a license key, you are confirming that you understand and accept the %s");
define("NBILL_REG_EULA", "End User License Agreement");