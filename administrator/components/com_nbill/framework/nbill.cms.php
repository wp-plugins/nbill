<?php
/**
* CMS Class file - detects which CMS we are using and instantiates the relevant interop class accordingly.
* This is then stored in a static member of the CMS class.
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
* This class detects which CMS is running, and instantiates the appropriate interop class accordingly.
* The instantiated interop object is then held in a static member so we only have to do it once.
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_cms
{
    static private $_nbf_interop;

    /**
    * Work out which CMS we are using, then instantiate and return the appropriate interop object
    * @return nbf_interop
    */
    static function get_interop()
    {
        if (!isset(self::$_nbf_interop))
        {
            //Load the base class
            require_once(dirname(__FILE__) . "/interop/nbill.interop.abstract.php");

            //Work out name of interop class to instantiate based on product and version - first try Mambo family versioning
            $nbf_class_name = nbf_common::nb_strtolower(str_replace("!", "", @$GLOBALS['_VERSION']->PRODUCT));
            $nbf_class_name .= "_" . str_replace(".", "_", @$GLOBALS['_VERSION']->RELEASE);
            if (nbf_common::nb_strlen($nbf_class_name) < 2 || !file_exists(dirname(__FILE__) . "/interop/nbill.interop.$nbf_class_name.php"))
            {
                //Are we using Joomla 1.5 without legacy mode?
                $nbf_class_name = nbf_common::nb_strtolower(str_replace("!", "", @$GLOBALS['version']->PRODUCT));
                $nbf_class_name .= "_" . str_replace(".", "_", @$GLOBALS['version']->RELEASE);
                if (nbf_common::nb_strlen($nbf_class_name) < 2 || !file_exists(dirname(__FILE__) . "/interop/nbill.interop.$nbf_class_name.php"))
                {
                    //Try Wordpress
                    if (defined('WP_INC')) {
                        $nbf_class_name = "wordpress";
                    } else {
                        //CMS does not adhere to Mambo family versioning and is not Joomla 1.5 or Wordpress
                        //If required, further checks can be made here for other supported CMSs
                        //Otherwise, just default to the nBill Framework CMS (standalone)
                        $nbf_class_name = "nbf_1_0";
                    }
                }
            }

            //Load the class file and instantiate
            require_once(dirname(__FILE__) . "/interop/nbill.interop.$nbf_class_name.php");
            $nbf_class_name = "nbf_interop_" . $nbf_class_name;
            self::$_nbf_interop = new $nbf_class_name();
        }
        return self::$_nbf_interop;
    }
}
?>