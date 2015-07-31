<?php
/**
* Interop Class File for the nBill Framework CMS
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
* 
* @access private* 
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* This class provides interop functions specific to the nBill Framework CMS (standalone)
* 
* @package nBill Framework Interop
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
* @todo Write the nBill Framework CMS!!!
*/
class nbf_interop_nbf_1_0 extends nbf_interop
{
    /** @var string Name of CMS (for display in error reports) */
    public $cms_name = "nBill Framework CMS";
    /** @var string Version number of CMS (for display in error reports) */
    public $cms_version = "1.0.x";
}