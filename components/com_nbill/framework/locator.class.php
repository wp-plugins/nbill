<?php
/**
* Locates the framework files
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
* Class to detect which CMS is currently in use so we can find the location of the
* framework files (
*/
if (!class_exists("nbill_framework_locator"))
{
    class nbill_framework_locator
    {
        //Most likely location
        private static $framework_folder = "";

        /**
        * Work out where to find the framework files, based on the CMS in use
        */
        public static function find_framework()
        {
            if (file_exists(realpath(dirname(__FILE__) . "/../../administrator/components")))
            {
                self::$framework_folder = realpath(dirname(__FILE__) . "/../../administrator/components/com_nbill/framework");
            }
            else if (file_exists(realpath(dirname(__FILE__) . "/../../../administrator/components")))
            {
                self::$framework_folder = realpath(dirname(__FILE__) . "/../../../administrator/components/com_nbill/framework");
            }
            return self::$framework_folder;
        }
    }
}