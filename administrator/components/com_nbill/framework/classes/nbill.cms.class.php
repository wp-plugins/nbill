<?php
/**
* CMS Class file - detects which CMS we are using and instantiates the relevant interop class accordingly.
* This is then stored in a static member of the CMS class.
* @version 1
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
    /** @var nbf_interop $interop A representation of the features that depend on values from the CMS */
    static public $interop;

    /**
    * Work out which CMS we are using, then instantiate and return the appropriate interop object
    * @return nbf_interop
    */
    static function set_interop($force_refresh = false)
    {
        if ($force_refresh || !isset(self::$interop))
        {
            //Load the base class
            require_once(dirname(__FILE__) . "/../interop/nbill.interop.abstract.php");

            //If a class name has been specified in the config file, use that
            include_once(realpath(dirname(__FILE__)) . "/../nbill.config.php");
            if (isset(nbf_config::$interop_class) && @nbf_config::$interop_class && nbf_common::nb_strtoupper(@nbf_config::$interop_class) != '[AUTO]')
            {
                $nbf_class_name = nbf_config::$interop_class;
            }
            else
            {
                //Work out name of interop class to instantiate based on product and version - first try Mambo family versioning
                $nbf_class_name = nbf_common::nb_strtolower(str_replace("!", "", @$GLOBALS['_VERSION']->PRODUCT));
                $nbf_class_name .= "_" . str_replace(".", "_", @$GLOBALS['_VERSION']->RELEASE);
                if (nbf_common::nb_strlen($nbf_class_name) < 2 || !file_exists(dirname(__FILE__) . "/../interop/nbill.interop.$nbf_class_name.php"))
                {
                    //Are we using Joomla 1.5 without legacy mode or 1.6?
                    $nbf_class_name = nbf_common::nb_strtolower(str_replace("!", "", @$GLOBALS['version']->PRODUCT));
                    $nbf_class_name .= "_" . str_replace(".", "_", @$GLOBALS['version']->RELEASE);
                    if (nbf_common::nb_strlen($nbf_class_name) < 2 || !file_exists(dirname(__FILE__) . "/../interop/nbill.interop.$nbf_class_name.php"))
                    {
                        //From 1.7, the version info is no longer stored in global variables - let's see if there is a version file we can parse (we try to avoid interacting with the API)
                        $nbf_class_name = "";
                        $version_file = dirname(__FILE__) . "/../../../../../includes/version.php";
                        if (!file_exists($version_file))
                        {
                            //The version file was briefly stored elsewhere...
                            $version_file = dirname(__FILE__) . "/../../../../../libraries/joomla/version.php";
                        }
                        if (!file_exists($version_file))
                        {
                            //This file never stays still - moved again in 2.5
                            $version_file = dirname(__FILE__) . "/../../../../../libraries/cms/version/version.php";
                        }
                        if (file_exists($version_file))
                        {
                            $version_info = file_get_contents($version_file);
                            $start = nbf_common::nb_strpos($version_info, "public \$PRODUCT = '");
                            $end = nbf_common::nb_strpos($version_info, "'", $start + 19);
                            $product = nbf_common::nb_strtolower(str_replace("!", "", nbf_common::nb_substr($version_info, $start + 19, $end - ($start + 19))));
                            $start = nbf_common::nb_strpos($version_info, "public \$RELEASE = '");
                            $end = nbf_common::nb_strpos($version_info, "'", $start + 19);
                            $version = nbf_common::nb_strtolower(str_replace("!", "", nbf_common::nb_substr($version_info, $start + 19, $end - ($start + 19))));
                            $version_data = explode(".", $version);
                            $nbf_class_name = self::find_class_name($product, @$version_data[0], @$version_data[1]);
                        }
                        if (!$nbf_class_name)
                        {
                            //Try Wordpress
                            if (defined('WPINC')) {
                                $nbf_class_name = "wordpress";
                            } else {
                                //CMS does not adhere to Mambo family versioning and is not Joomla 1.5 or Wordpress
                                //If required, further checks can be made here for other supported CMSs
                                //Otherwise, just default to Joomla 1.6 plus
                                $nbf_class_name = "joomla_1_6_plus";
                            }
                        }
                    }
                }
            }

            //Load the class file and instantiate
            include_once(dirname(__FILE__) . "/../interop/nbill.interop.$nbf_class_name.php");
            $nbf_class_name = "nbf_interop_" . $nbf_class_name;
            self::$interop = new $nbf_class_name();
        }
    }

    private static function find_class_name($product, $major_version, $minor_version)
    {
        $nbf_class_name = "";
        $loop_breaker = 0;

        //For speed and reduced resource usage, we will hard code the known options
        switch ($product . "_" . $major_version . "." . $minor_version)
        {
            case "joomla_1.0":
                $nbf_class_name = "joomla_1_0";
                break;
            case "joomla_1.5":
                $nbf_class_name = "joomla_1_5";
                break;
            case "joomla_1.6":
            case "joomla_1.7":
            case "joomla_2.5":
            case "joomla_3.0":
            case "joomla_3.1":
            case "joomla_3.2":
            case "joomla_3.3":
            case "joomla_3.4":
            case "joomla_3.5":
                $nbf_class_name = "joomla_1_6_plus";
                break;
            case "mambo_4.6":
                $nbf_class_name = "mambo_4_6";
                break;
        }

        //If not specified, try to work it out the long way round...
        if (!file_exists(dirname(__FILE__) . "/../interop/nbill.interop.$nbf_class_name.php"))
        {
            while(strlen($nbf_class_name) == 0 && ($major_version > 1 || $minor_version > 5))
            {
                $loop_breaker++;
                if ($loop_breaker > 200)
                {
                    break;
                }
                $nbf_class_name = $product . "_" . $major_version . "_" . $minor_version . "_plus";
                if (!file_exists(dirname(__FILE__) . "/../interop/nbill.interop.$nbf_class_name.php"))
                {
                    $nbf_class_name = $product . "_" . $major_version . "_" . $minor_version;
                    if (!file_exists(dirname(__FILE__) . "/../interop/nbill.interop.$nbf_class_name.php"))
                    {
                        $nbf_class_name = "";
                        $minor_version--;
                        if ($minor_version < 0)
                        {
                            $minor_version = 50;
                            $major_version--;
                        }
                    }
                }
            }
        }

        return $nbf_class_name ? $nbf_class_name : "joomla_1_6_plus";
    }
}

//Initialise
nbf_cms::set_interop();