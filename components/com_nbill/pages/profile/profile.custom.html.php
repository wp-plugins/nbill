<?php
/**
* Custom override of default profile page - if you want to change the output of the My Profile page, 
* you can do it here (this file will not be overwritten during upgrades). Make sure you keep the 
* basic class structure intact, as it is called from the core.
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
* 
* @access private* 
 */

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

require_once(nbf_cms::$interop->nbill_fe_base_path . "/pages/profile/profile.default.html.php");

/**
* You can override the display and behaviour of the profile page by writing your own methods here
*/
class nbill_fe_profile_custom extends nbill_fe_profile
{
    
}