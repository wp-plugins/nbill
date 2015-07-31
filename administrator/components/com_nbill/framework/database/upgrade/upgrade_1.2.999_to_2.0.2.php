<?php
/**
* Upgrade script from version 1.2.6 to 2.0.0
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
 This software is NOT open source.
*
* This component was developed by Netshine Software Limited (www.netshinesoftware.com). Use of this

*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* Performs any data manipulation required when upgrading (structural changes are handled automatically by comparing the schema files with the database)
*/
function upgrade_1_2_999_to_2_0_2()
{
    require_once(dirname(__FILE__) . "/upgrade_1.2.999_to_2.0.0.php");
    return upgrade_1_2_999_to_2_0_0();
}