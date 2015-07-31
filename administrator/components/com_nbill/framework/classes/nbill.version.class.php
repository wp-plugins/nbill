<?php
/**
* Store information about this version of nBill, and allow comparison between different versions
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
* This class stores the version information for this version of the software, and allows comparisons between version numbers.
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_version
{
    /** @var string Current software version (object defaults to this value if no other value supplied) */
    static public $nbill_version_no = "3.1.1";
    static public $nbill_allow_upgrade_from = "2.0.4";
    static public $upgraded_from = "";
    static public $suffix = "Lite";

    /** @var array Stores major, minor, and revision numbers in an array */
    private $version;

    /**
    * Create a new version object - if no version number is passed in, the default will be the current software version
    * @param string $version_string Version number to be represented by this object, eg. "2.0.1"
    */
    public function __construct($version_string = null)
    {
        if ($version_string)
        {
            $this->version = $this->string_to_array($version_string);
        }
        else
        {
            $this->version = $this->string_to_array(self::$nbill_version_no);
        }
    }

    /**
    * Converts the version number passed in to an array of at least 3 elements
    * @param string $version_string Version number to convert, eg. "2.0.1"
    * @return array An array of at least 3 elements containing an exploded view of the version number string
    */
    private function string_to_array($version_string)
    {
        $version_array = array();
        if ($version_string)
        {
            $version_array = explode(".", $version_string);
        }
        else
        {
            $version_array = explode(".", "0.0.0");
        }
        if (count($version_array) < 3)
        {
            for ($i = count($version_array); $i < count($version_array); $i++)
            {
                $version_array[] = "0";
            }
        }
        return $version_array;
    }

    /**
    * Returns the version number as a string
    * @return string
    */
    public function __toString()
    {
        return implode(".", $this->version);
    }

    public function get_short_version()
    {
        if (count($this->version) > 1)
        {
            $v = $this->version[0] . "." . $this->version[1];
            if (self::$suffix)
            {
                $v .= " " . self::$suffix;
            }
            return $v;
        }
    }

    /**
    * Checks whether the version number represented by the object is greater than, less than, or equal to
    * the version number string passed in.
    * @param string $operator The operator to use. Valid values are: "==", ">", or "<"
    * @param string $version_string The version number to compare against this object
    * @return boolean Whether or not the current object is greater than, less than, or equal to (depending on the operator) the version number passed in
    */
    public function compare($operator = "==", $version_string)
    {
        //Convert the version passed in into an array for comparison of elements
        $other_version = $this->string_to_array($version_string);

        //Get the maximum number of divisions
        $div_count = count($other_version) > count($this->version) ? count($other_version) : count($this->version);

        //Compare each division in turn (suppress errors in case one has more divisions than the other)
        for ($i = 0; $i < $div_count; $i++)
        {
            //We could use eval() to dynamically select the operator, but as some people disable that function we will do it the long way round (validation not required this way)
            switch ($operator)
            {
                case ">":
                    if (@$this->version[$i] > @$other_version[$i]) {return true;} else if (@$this->version[$i] < @$other_version[$i]) {return false;} break; //Breaking with my normal coding convention for the sake of brevity
                case "<":
                    if (@$this->version[$i] < @$other_version[$i]) {return true;} else if (@$this->version[$i] > @$other_version[$i]) {return false;} break;
                default:
                    if (@$this->version[$i] != @$other_version[$i]) {return false;} break;
            }
        }
        switch ($operator)
        {
            case ">":
            case "<":
                return false;
        }
        return true;
    }

    /**
    * Read the version number as stored in the database
    * @return string The database version number
    */
    static public function get_database_version()
    {
        $nb_database = nbf_cms::$interop->database;

        $sql = "SELECT * FROM #__nbill_version";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($version_info);
        $db_version = $version_info->software_version;
        if ($version_info->service_pack)
        {
            $db_version .= " (SP" . $version_info->service_pack . ")"; //No longer used, but may be present on nBill 1.x databases
        }
        return $db_version;
    }
}