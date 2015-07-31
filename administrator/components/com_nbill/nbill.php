<?php
/**
* Entry point into both front-end and back-end (done this way for cross compatability with older versions of Joomla/Mambo)
* @version 3
* @package nBill
* @copyright (C) 2014 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

if (file_exists(realpath(dirname(__FILE__)) . "/site.nbill.php")) {
    include(realpath(dirname(__FILE__)) . "/site.nbill.php");
} else if (file_exists(realpath(dirname(__FILE__)) . "/admin.nbill.php")) {
    include(realpath(dirname(__FILE__)) . "/admin.nbill.php");
} else {
    die("Sorry, the component entry point file could not be found!");
}