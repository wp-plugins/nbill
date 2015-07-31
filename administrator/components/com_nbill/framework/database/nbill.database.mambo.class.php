<?php
/**
* nBill Database Class file - provides all the database access features. If using the Mambo API,
* this will just act as a wrapper for the database object that is already instantiated within
* the CMS.
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
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_mambo_database extends nbf_database
{
    function __construct($host = 'localhost', $user = null, $pass = null, $db = null, $table_prefix = null, $port = null, $socket = null)
    {
        //If host includes port, split them out
        if (($port == 3306 || !$port) && strpos($host, ':') !== false)
        {
            $conn_array = explode(':', $host);
            if (count($conn_array) == 2)
            {
                $port = $conn_array[1];
                $host = $conn_array[0];
            }
        }

        global $database;
        if ($db != null)
        {
            if (@mysql_connect($host, $user, $pass, true))
            {
                $database = new database($host, $user, $pass, $db, $table_prefix);
                $this->_errorMsg = $database->_errorMsg;
            }
            else
            {
                $this->_errorMsg = "Could not connect";
            }
        }
        $this->_table_prefix = $database->_table_prefix;
        $database->_debug = 0; //Stop debug screens appearing in CMS
    }
    function setQuery($sql)
    {
        if ($this->legacy_mode)
        {
            $sql = str_replace("#__inv_", "#__nbill_", $sql);
        }
        global $database;
        $database->_pointer = null;
        $this->_pointer = null;

        if (!$this->skip_compat_processing)
        {
            //If any custom code still uses the old contact.name column, replace it (only retrieval will work of course)
            $sql = str_replace("#__nbill_contact.name AS ", "CONCAT_WS(\' \', #__nbill_contact.first_name, #__nbill_contact.last_name) AS ", $sql);
            $sql = str_replace("#__nbill_contact.name", "CONCAT_WS(\' \', #__nbill_contact.first_name, #__nbill_contact.last_name) AS `name`", $sql);

            //If custom code still uses the old is_quote_request column, replace it
            $sql = str_replace("is_quote_request", "form_type='QU' AS is_quote_request", $sql);
        }

        $database->setQuery($sql);
        $this->_sql = $database->_sql;
        $this->_errorMsg = $database->_errorMsg;
    }
    function query()
    {
        global $database;
        $database->query();
        $this->_errorMsg = $database->_errorMsg;
    }
    function loadObject(&$object)
    {
        global $database;
        $retVal = false;
        $retVal = $database->loadObject($object);
        if ($retVal && !$object)
        {
            $object = $retVal;
        }
        $this->_errorMsg = $database->_errorMsg;
        return true;
    }
    function loadObjectList($key='')
    {
        global $database;
        $retVal = $database->loadObjectList($key);
        if (!$retVal && nbf_common::nb_strlen($database->_errorMsg) == 0)
        {
            $retVal = array();
        }
        $this->_errorMsg = $database->_errorMsg;
        return $retVal;
    }
    function loadResult()
    {
        global $database;
        $retVal = $database->loadResult();
        $this->_errorMsg = $database->_errorMsg;
        return $retVal;
    }
    function loadResultArray()
    {
        global $database;
        $retVal = $database->loadResultArray();
        if (!$retVal)
        {
            $retVal = array();
        }
        $this->_errorMsg = $database->_errorMsg;
        return $retVal;
    }
    function loadAssoc() //1.5 Only
    {
        global $database;
        $retVal = $database->loadAssoc();
        $this->_errorMsg = $database->_errorMsg;
        return $retVal;
    }
    function loadAssocList($key = "")
    {
        global $database;
        $retVal = $database->loadAssocList($key);
        if (!$retVal)
        {
            $retVal = array();
        }
        if ($key)
        {
            $assoc_list = array();
            foreach ($retVal as $ret_key=>$result_array)
            {
                $key_value = $result_array[$key];
                unset($result_array[$key]);
                if (count($result_array) == 1)
                {
                    $assoc_list[$key_value] = current($result_array);
                }
                else
                {
                    $assoc_list[$key_value] = $result_array;
                }
            }
            $retVal = $assoc_list;
        }
        $this->_errorMsg = $database->_errorMsg;
        return $retVal;
    }
    function insertObject($table, &$object, $keyName = NULL, $verbose=false)
    {
        global $database;
        $retVal = $database->insertObject($table, $object, $keyName, $verbose);
        $this->_errorMsg = $database->_errorMsg;
        return $retVal;
    }
    function updateObject($table, &$object, $keyName, $updateNulls=true)
    {
        global $database;
        $retVal = $database->updateObject($table, $object, $keyName, $updateNulls);
        $this->_errorMsg = $database->_errorMsg;
        return $retVal;
    }
    function insertid()
    {
        global $database;
        $retVal = $database->insertid();
        $this->_errorMsg = $database->_errorMsg;
        return $retVal;
    }
    function getEscaped($text)
    {
        global $database;
        $retVal = $database->getEscaped($text);
        $this->_errorMsg = $database->_errorMsg;
        return $retVal;
    }
    function Quote($sql, $escaped = true)
    {
        global $database;
        $retVal = $database->Quote($sql, $escaped);
        $this->_errorMsg = $database->_errorMsg;
        return $retVal;
    }
    function getAffectedRows()
    {
        global $database;
        return @$database->getAffectedRows();
    }
    function getErrorMsg($escaped = false)
    {
        global $database;
        $retVal = $database->getErrorMsg($escaped);
        $this->_errorMsg = $database->_errorMsg;
        return $retVal;
    }
    function getVersion()
    {
        global $database;
        $retVal = $database->getVersion();
        $this->_errorMsg = $database->_errorMsg;
        return $retVal;
    }
}