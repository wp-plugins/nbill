<?php
/**
* nBill Database Class file - provides all the database access features.
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
* Provide direct database access (bypassing CMS API, but using existing connection if applicable)
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_database
{
    /**
    * @var boolean Whether or not to skip backward compatability efforts
    */
    var $skip_compat_processing = false;

    var $legacy_mode = false;
    /**
    * @var string Last error message
    */
    var $_errorMsg;
    /**
    * @var string SQL to execute
    */
    var $_sql;
    /**
    * @var string Prefix for all table names in the CMS database
    */
    var $_table_prefix;
    /**
    * @var int Where results are paginated, this bookmarks the current position in the result set
    */
    var $_pointer = null;
    /**
    * @var boolean Whether or not to use the old-fashioned MySQL functions instead of MySQLi
    */
    private $_use_mysql = false;
    /**
    * @var mixed MySQL resource (if using old-fashioned MySQL functions instead of MySQLi)
    */
    private $_mysql = null;
    /**
    * @var mixed MySQL database resource (if using old-fashioned MySQL functions instead of MySQLi)
    */
    private $_mysql_db = null;
    /**
    * @var mysqli $_mysqli PHP MySQLi object, used for database interaction
    */
    private $_mysqli = null;
    /**
    * @var int Autoincrement value of last record inserted
    */
    private $_insert_id = null;
    /**
    * @var int Number of rows affected by the last query executed
    */
    private $_rows_affected = null;
    /**
    * @var boolean Whether or not to attempt translation of data into current language (ie. whether translations exist for current language in the database)
    */
    private $_translate = false;

    /**
    * Create a new connection if we don't already have one, or if new details are passed in
    * @param string $host Optionally specify a host name
    * @param string $user Optionally specify a database username
    * @param string $pass Optionally specify the database user's password
    * @param string $db Optionally specify the database name (all other parameters will be ignored if this one is not present)
    * @param string $table_prefix Optionally specify a new table prefix
    * @param int $port Optionally specify the port number for database connections (if not standard 3306)
    * @param string $socket Optionally specify the socket or named pipe to use when connecting to the database
    * @param int $retry_on_failure Optionally specify how many times to retry (one second is allowed to elapse between each retry)
    */
    function __construct($host = "localhost", $user = null, $pass = null, $db = null, $table_prefix = null, $port = 3306, $socket = null, $retry_on_failure = 0)
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

        //Check whether to use MySQL or MySQLi
        include_once(realpath(dirname(__FILE__)) . "/../nbill.config.php");
        if (!class_exists("mysqli") || (isset(nbf_config::$mysql) && @nbf_config::$mysql))
        {
            $this->_use_mysql = true;
        }

        if ($host !== null || $user !== null || $db !== null || $table_prefix !== null)
        {
            if (!$this->_use_mysql)
            {
                //We need a new MySQLi connection
                $this->_mysqli = @new mysqli($host, $user, $pass, $db, $port, $socket);
                $this->_errorMsg = $this->_mysqli->connect_error;
                while (nbf_common::nb_strlen($this->_errorMsg) > 0 && $retry_on_failure > 0)
                {
                    //Try again
                    @$this->_mysqli->close();
                    unset($this->_mysqli);
                    $this->_errorMsg = "";
                    sleep(1);
                    $this->_mysqli = new mysqli($host, $user, $pass, $db, $port, $socket);
                    $this->_errorMsg = $this->_mysqli->connect_error;
                    $retry_on_failure--;
                }
                if (nbf_common::nb_strlen($this->_errorMsg) > 0)
                {
                    $this->_use_mysql = true;
                }
                else
                {
                    //Run a test query just to make sure it is working (sometimes connection works but queries don't for some reason)
                    $this->_mysqli->query("SHOW TABLES");
                    if (nbf_common::nb_strlen($this->_mysqli->error) > 0)
                    {
                        $this->_use_mysql = true;
                    }
                }
            }
            if ($this->_use_mysql)
            {
                //We need a new MySQL connection
                $this->_mysql = mysql_connect($host . ":" . $port, $user, $pass, true);
                $this->_mysql_db = mysql_select_db($db, $this->_mysql);
            }
            $this->_table_prefix = $table_prefix;
        }
        else
        {
            $db_connection = nbf_cms::$interop->db_connection;
            if (!$this->_use_mysql)
            {
                //If we don't already have a mysqli object, create a new one (will use existing connection if available)
                if (!$this->_mysqli)
                {
                    $this->_mysqli = new mysqli($db_connection->host, $db_connection->user_name, $db_connection->password, $db_connection->db_name, $db_connection->port, $db_connection->socket);
                    $this->_errorMsg = $this->_mysqli->connect_error;
                    while (nbf_common::nb_strlen($this->_errorMsg) > 0 && $retry_on_failure > 0)
                    {
                        //Try again
                        @$this->_mysqli->close();
                        unset($this->_mysqli);
                        $this->_errorMsg = "";
                        sleep(1);
                        $this->_mysqli = new mysqli($db_connection->host, $db_connection->user_name, $db_connection->password, $db_connection->db_name, $db_connection->port, $db_connection->socket);
                        $this->_errorMsg = $this->_mysqli->connect_error;
                        $retry_on_failure--;
                    }
                    if (nbf_common::nb_strlen($this->_errorMsg) > 0)
                    {
                        $this->_use_mysql = true;
                    }
                    else
                    {
                        //Run a test query just to make sure it is working (sometimes connection works but queries don't for some reason)
                        $this->_mysqli->query("SHOW TABLES");
                        if (nbf_common::nb_strlen($this->_mysqli->error) > 0)
                        {
                            $this->_use_mysql = true;
                        }
                    }
                }
            }
            if ($this->_use_mysql)
            {
                //Create MySQL connection
                $this->_mysql = mysql_connect($db_connection->host . ":" . $db_connection->port, $db_connection->user_name, $db_connection->password, true);
                $this->_mysql_db = mysql_select_db($db_connection->db_name, $this->_mysql);
            }
            $this->_table_prefix = $db_connection->prefix;
        }

        //Check whether any translations exist in the currently selected language
        if (!defined('NBILL_ADMIN') && class_exists('nbf_cms') && nbf_cms::$interop != null && strlen(@nbf_cms::$interop->language) > 0)
        {
            $this->_sql = "SELECT `id` FROM `" . $this->_table_prefix . "nbill_translation` WHERE `language` = '" . nbf_cms::$interop->language . "' AND `published` = 1";
            $this->_translate = $this->loadResult();
        }
    }

    public function close()
    {
        if ($this->_use_mysql) {
            if (isset($this->_mysql)) {
                @mysql_close($this->_mysql);
            }
        } else {
            if (isset($this->_mysqli)) {
                @mysqli_close($this->_mysqli);
            }
        }
    }

    function __destruct()
    {
        $this->close();
        if(!defined('PHP_VERSION_ID'))
        {
            $version = PHP_VERSION;
            define('PHP_VERSION_ID', ($version{0} * 10000 + $version{2} * 100 + $version{4}));
        }
        if (PHP_VERSION_ID < 50200 && !@$_SESSION['__default']['session.token'])
        {
            @session_write_close(); //Workaround for bug in PHP < 5.2
        }
    }

    function __wakeup()
    {
        $this->__construct(); //Re-establish database connection
    }

    function set_char_encoding($charset)
    {
        //Make sure we are using the correct character encoding
        if ($this->_use_mysql)
        {
            if (function_exists("mysql_set_charset"))
            {
                @mysql_set_charset($charset, $this->_mysql);
            }
        }
        else
        {
            if (method_exists($this->_mysqli, "get_charset") && @$this->_mysqli->get_charset()->charset != $charset)
            {
                @$this->_mysqli->set_charset($charset);
            }
        }
        if ($charset == 'utf8')
        {
            @$this->setQuery("SET NAMES 'utf8';");
            @$this->query();
        }
    }

    /**
    * Make a note of the SQL to execute (replacing the table prefix as appropriate)
    * @param string $sql The SQL to execute
    */
    function setQuery($sql)
    {
        if ($this->legacy_mode)
        {
            $sql = str_replace("#__inv_", "#__nbill_", $sql);
        }

        //Trailing semi-colon not allowed
        if (substr($sql, strlen($sql) - 1, 1) == ";")
        {
            $sql = substr($sql, 0, strlen($sql) - 1);
        }

        if (!$this->skip_compat_processing)
        {
            //If any custom code still uses the old contact.name column, replace it (only retrieval will work of course)
            $sql = str_replace("#__nbill_contact.name AS ", "CONCAT_WS(\' \', #__nbill_contact.first_name, #__nbill_contact.last_name) AS ", $sql);
            $sql = str_replace("#__nbill_contact.name", "CONCAT_WS(\' \', #__nbill_contact.first_name, #__nbill_contact.last_name) AS `name`", $sql);

            //If custom code still uses the old is_quote_request column, replace it
            $sql = str_replace("is_quote_request", "form_type='QU' AS is_quote_request", $sql);
        }

        //Old nCart checkout form and nTicket category form refer to legacy renderer and table border, which have now been removed (but nTicket turns off compat checking!)
        if (!$this->skip_compat_processing) {
            if (strpos($sql, "ALTER TABLE") === false && strpos($sql, "legacy_renderer") !== false) {
                if (strpos($sql, ", `legacy_renderer`, `legacy_table_border`) VALUES") !== false) {
                    //nCart checkout form
                    $sql = str_replace("`legacy_renderer`, `legacy_table_border`)", "`renderer`)", $sql);
                    $sql = str_replace(", 0, 1, '', 0, 0),", ", 0, 1, '', 2),", $sql);
                    $sql = str_replace(", 0, 1, '', 0, 0)", ", 0, 1, '', 2)", $sql);
                } else {
                    if (strpos($sql, 'SELECT ') !== false) {
                        //Something else - try just changing it to renderer (will use absolute positioning)
                        $sql = str_replace("legacy_renderer", "renderer AS legacy_renderer", $sql);
                    }
                }
            }
        }

        //Replace prefix tokens with actual table prefix
        $sql = str_replace("#__", $this->_table_prefix, $sql);

        //Store it, ready for execution
        $this->_sql = $sql;

        //Reset pointer
        $this->_pointer = null;

        //Clear any old error messages
        $this->_errorMsg = "";
    }

    /**
    * Execute the query without returning a result
    * @return boolean Whether or not the process was successful
    */
    function query()
    {
        if ($this->_use_mysql)
        {
            if (mysql_query($this->_sql, $this->_mysql))
            {
                $this->_rows_affected = @mysql_affected_rows($this->_mysql);
                $this->_insert_id = @mysql_insert_id($this->_mysql);
                return true;
            }
            else
            {
                $this->_rows_affected = null;
                $this->_insert_id = null;
                $this->_errorMsg = mysql_error($this->_mysql);
                return false;
            }
        }
        else
        {
            @mysqli_ping($this->_mysqli);
            if ($this->_mysqli->query($this->_sql))
            {
                $this->_rows_affected = $this->_mysqli->affected_rows;
                $this->_insert_id = $this->_mysqli->insert_id;
                return true;
            }
            else
            {
                $this->_rows_affected = null;
                $this->_insert_id = null;
                $this->_errorMsg = $this->_mysqli->error;
                return false;
            }
        }
    }

    /**
    * Execute the query and return the first record, bound to the given object
    * @param object Object to bind the results to
    * @return boolean Whether or not the process was successful
    */
    function loadObject(&$object)
    {
        if ($this->_use_mysql)
        {
            $result = mysql_query($this->_sql, $this->_mysql);
            if ($result)
            {
                $result_object = mysql_fetch_object($result);
                mysql_free_result($result);
            }
            else
            {
                $this->_errorMsg = mysql_error($this->_mysql);
                return false;
            }
        }
        else
        {
            @mysqli_ping($this->_mysqli);
            $result = $this->_mysqli->query($this->_sql);
            if ($result)
            {
                $result_object = $result->fetch_object();
                $result->free();
            }
            else
            {
                $this->_errorMsg = $this->_mysqli->error;
                return false;
            }
        }

        if ($object)
        {
            //Bind any matching properties to the existing object
            $properties = get_object_vars($object);
            foreach (array_keys($properties) as $property)
            {
                if (@$result_object->$property)
                {
                    $object->$property = $result_object->$property;
                }
            }
        }
        else
        {
            $object = $result_object;
        }

        $object = $this->_translate ? $this->translate_object($object) : $object;
        return true;
    }

    /**
    * Execute the query and return all records as a list of objects
    * @param string $key Optionally specify a field to use as the array key (make sure it is unique!)
    * @return array Array of objects with properties matching the column names of the data returned
    */
    function loadObjectList($key='')
    {
        //Get the records
        $object_list = null;

        if ($this->_use_mysql)
        {
            $result = mysql_query($this->_sql, $this->_mysql);
            if ($result)
            {
                $object_list = array();
                while ($result_object = mysql_fetch_object($result))
                {
                    if ($key)
                    {
                        $object_list[$result_object->$key] = $result_object;
                    }
                    else
                    {
                        $object_list[] = $result_object;
                    }
                }
                mysql_free_result($result);
            }
            else
            {
                $this->_errorMsg = mysql_error($this->_mysql);
            }
        }
        else
        {
            @mysqli_ping($this->_mysqli);
            $result = $this->_mysqli->query($this->_sql);
            if ($result)
            {
                $object_list = array();
                while ($result_object = $result->fetch_object())
                {
                    if ($key)
                    {
                        $object_list[$result_object->$key] = $result_object;
                    }
                    else
                    {
                        $object_list[] = $result_object;
                    }
                }
                $result->free();
            }
            else
            {
                $this->_errorMsg = $this->_mysqli->error;
            }
        }
        return $this->_translate ? $this->translate_object_array($object_list) : $object_list;
    }

    /**
    * Execute the query and return a chunk of records as a list of objects
    * @param array $object_list Array of objects with properties matching the column names of the data returned
    * @param string $key Optionally specify a field to use as the array key (make sure it is unique!)
    * @param integer $chunk_size Maximum number of records to retrieve (call this function repeatedly to get the next chunk)
    * @return boolean Whether or not any records were retrieved
    */
    function loadObjectListChunked(&$object_list, $key='', $chunk_size = null)
    {
        //Paginate results, if applicable
        $chunk_size = intval($chunk_size);
        if ($chunk_size)
        {
            if ($this->_pointer === null)
            {
                $this->_pointer = 0;
            }
            $hold_pointer = $this->_pointer;
            $this->setQuery($this->_sql . " LIMIT " . $this->_pointer . ", $chunk_size");
            $this->_pointer = $hold_pointer;
        }
        $this->_pointer += $chunk_size;

        //Get the records
        $object_list = $this->loadObjectList($key);

        //Are there any more?
        if (!$object_list || count($object_list) == 0)
        {
            return false; //We have reached the end
        }
        else
        {
            return true; //There may be more to come
        }
    }

    /**
    * Execute the query and return a single value from the first record
    * @return mixed The value of the first column in the first record returned by the query
    */
    function loadResult($internal_translation = false)
    {
        if ($this->_sql)
        {
            if ($this->_use_mysql)
            {
                $result = mysql_query($this->_sql, $this->_mysql);
                if ($result)
                {
                    $first_row = mysql_fetch_row($result);
                    mysql_free_result($result);
                }
                else
                {
                    $this->_errorMsg = mysql_error($this->_mysql);
                    return null;
                }
            }
            else
            {
                @mysqli_ping($this->_mysqli);
                $result = $this->_mysqli->query($this->_sql);
                if ($result)
                {
                    $first_row = $result->fetch_row();
                    $result->free();
                }
                else
                {
                    $this->_errorMsg = $this->_mysqli->error;
                    return null;
                }
            }
            if (count($first_row) > 0)
            {
                if (!$internal_translation)
                {
                    $first_row = $this->translate_object($first_row);
                }
                return $first_row[0];
            }
            else
            {
                return null;
            }
        }
        else
        {
            return null;
        }
    }

    /**
    * Execute the query and return an array of values from the first column of each record
    * @return array An array of single values derived from the first column in each record returned by the query
    */
    function loadResultArray()
    {
        $result_array = array();
        if ($this->_use_mysql)
        {
            $result = mysql_query($this->_sql, $this->_mysql);
            if ($result)
            {
                while ($row = mysql_fetch_row($result))
                {
                    if (count($row) > 0)
                    {
                        $result_array[] = $row[0];
                    }
                    else
                    {
                        $result_array[] = null;
                    }
                }
                mysql_free_result($result);
            }
            else
            {
                $this->_errorMsg = mysql_error($this->_mysql);
                return null;
            }
        }
        else
        {
            @mysqli_ping($this->_mysqli);
            $result = $this->_mysqli->query($this->_sql);
            if ($result)
            {
                while ($row = $result->fetch_row())
                {
                    if (count($row) > 0)
                    {
                        $result_array[] = $row[0];
                    }
                    else
                    {
                        $result_array[] = null;
                    }
                }
                $result->free();
            }
            else
            {
                $this->_errorMsg = $this->_mysqli->error;
                return null;
            }
        }
        return $result_array;
    }

    /**
    * Return the first row as an associative array
    * @return array Associative array representing the first row returned by the query
    */
    function loadAssoc()
    {
        if ($this->_use_mysql)
        {
            $result = mysql_query($this->_sql, $this->_mysql);
            if ($result)
            {
                return mysql_fetch_assoc($result);
            }
            else
            {
                $this->_errorMsg = mysql_error($this->_mysql);
                return null;
            }
        }
        else
        {
            @mysqli_ping($this->_mysqli);
            $result = $this->_mysqli->query($this->_sql);
            if ($result)
            {
                return $result->fetch_assoc();
            }
            else
            {
                $this->_errorMsg = $this->_mysqli->error;
                return null;
            }
        }
    }

    /**
    * Return all rows, each as an associative array
    * @param string $key Optionally specify a field to use as the array key (make sure it is unique!)
    * @return array Array of associative arrays
    */
    function loadAssocList($key = "")
    {
        $assoc_list = null;

        if ($this->_use_mysql)
        {
            $result = mysql_query($this->_sql, $this->_mysql);
            if ($result)
            {
                $assoc_list = array();
                while ($result_array = mysql_fetch_assoc($result))
                {
                    if ($key)
                    {
                        $assoc_list[$result_array[$key]] = $result_array;
                    }
                    else
                    {
                        $assoc_list[] = $result_array;
                    }
                }
                mysql_free_result($result);
            }
            else
            {
                $this->_errorMsg = mysql_error($this->_mysql);
            }
        }
        else
        {
            @mysqli_ping($this->_mysqli);
            $result = $this->_mysqli->query($this->_sql);
            if ($result)
            {
                $assoc_list = array();
                while ($result_array = $result->fetch_assoc())
                {
                    if ($key)
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
                    else
                    {
                        $assoc_list[] = $result_array;
                    }
                }
                $result->free();
            }
            else
            {
                $this->_errorMsg = $this->_mysqli->error;
            }
        }
        return $assoc_list;
    }

    /**
    * Insert a row in the given table using the object member names for column names and the object member values for data
    * @param string $table Name of the table to insert into (hard coded please - no user input!)
    * @param object $object Object to insert (passed by reference so that the key can be populated, if applicable)
    * @param string $keyName Name of the primary key column - this member of the object is populated with the insert ID if the insert is successful
    * @return boolean Whether or not the process was successful
    */
    function insertObject($table, &$object, $keyName = null)
    {
        //Get the members
        $object_vars = get_object_vars($object);

        //Filter out any undesirables (any that start with underscore or are objects or arrays)
        $object_columns = array();
        $object_values = array();
        foreach ($object_vars as $key=>$value)
        {
            if (!is_array($value) && !is_object($value) && substr($key, 0, 1) != "_")
            {
                $object_columns[] = "`" . $key . "`";
                $object_values[] = "'" . $value . "'";
            }
        }
        $columns = implode(",", $object_columns);
        $values = implode(",", $object_values);

        //Run the query
        $sql = "INSERT INTO `$table` ($columns) VALUES ($values)";
        $this->setQuery($sql);
        $return_value = $this->query();

        //Populate the key, if applicable
        if ($keyName != null)
        {
            @$object->$keyName = $this->_insert_id;
        }

        return $return_value;
    }

    /**
    * Update a row in the given table using the object member names for column names and the object member values for data
    * @param string $table Name of the table to update
    * @param object $object Object to use for updating
    * @param string $keyName Name of the primary key column - used in the WHERE clause to identify which row to update
    * @param boolean $updateNulls Whether or not to set values to a zero length string in the database if the corresponding object member is null
    * @return boolean Whether or not the process was successful
    */
    function updateObject($table, &$object, $keyName, $updateNulls=true)
    {
        //Get the members
        $object_vars = get_object_vars($object);

        //Filter out any undesirables (any that start with underscore or are objects or arrays, or the primary key, or nulls if we are asked not to update nulls)
        $updates = array();
        foreach ($object_vars as $key=>$value)
        {
            if (!is_array($value) && !is_object($value) && substr($key, 0, 1) != "_" && $key != $keyName && !(!$updateNulls && $value === null))
            {
                $updates[] = "`" . $key . "` = '" . $this->getEscaped($value) . "'";
            }
        }

        $sql = "UPDATE `$table` SET " . implode(",", $updates) . " WHERE `$keyName` = '" . $object->$keyName . "'";
        $this->setQuery($sql);
        return $this->query();
    }

    /**
    * Return the autoincremented ID of the last record to be inserted, and clear the value
    * @return int The autoincrement ID number of the last record inserted
    */
    function insertid()
    {
        $return_value = $this->_insert_id;
        $this->_insert_id = null;
        return $return_value;
    }

    /**
    * Escape any special characters to prevent SQL injection
    * @param string $text The text to escape
    * @return string The escaped text
    */
    function getEscaped($text)
    {
        if ($this->_use_mysql)
        {
            return @mysql_real_escape_string($text, $this->_mysql);
        }
        else
        {
            return $this->_mysqli->escape_string($text);
        }
    }

    /**
    * Return the number of rows affected by the last query to be executed, and clear the value
    * @return int The number of rows affected by the last query
    */
    function getAffectedRows()
    {
        $return_value = $this->_rows_affected;
        $this->_rows_affected = null;
        return $return_value;
    }

    /**
    * Load a particular record from a particular table and return it as an object (optionally sanitised for HTML display).
    * If no ID is passed in, a new object will be created with the default values taken from the database schema file.
    * @param string $table_name Name of table to load record from (hard code a valid value please! No user input!)
    * @param mixed $id Primary key value (typically an integer - if so, call intval to make sure, if not, surround in single quotes)
    * @param boolean $sanitize Whether or not to convert to html entities
    * @param string $key_name Name of primary key column
    */
    public function load_record($table_name, $id, $sanitize = true, $key_name = "id")
    {
        $row = null;
        $id = intval($id);
        if ($id)
        {
            $sql = "SELECT * FROM `$table_name` WHERE `$key_name` = $id";
            $this->setQuery($sql);
            $this->loadObject($row);
        }
        else
        {
            //New record - load default values from database schema
            $table_name = str_replace("#__nbill_", "", $table_name);
            $file_name = nbf_cms::$interop->nbill_admin_base_path . "/framework/database/schema/$table_name.xml";
            if (file_exists($file_name))
            {
                $schema = @simplexml_load_file($file_name);
                if ($schema)
                {
                    $row = new stdClass();
                    foreach ($schema->columns->column as $column)
                    {
                        switch ($column->type)
                        {
                            case "int":
                            case "tinyint":
                            case "smallint":
                            case "mediumint":
                            case "bigint":
                            case "integer":
                            case "long":
                                $default_value = (int)@$column->default;
                                break;
                            default:
                                $default_value = (string)@$column->default;
                                break;
                        }
                        $col_name = (string)$column['name'];
                        $row->$col_name = $default_value;
                    }
                }
            }
        }

        if ($row)
        {
            if ($sanitize)
            {
                //Encode any HTML, unless the schema says otherwise
                $table_name = str_replace("#__nbill_", "", $table_name);
                $file_name = nbf_cms::$interop->nbill_admin_base_path . "/framework/database/schema/$table_name.xml";
                if (file_exists($file_name))
                {
                    $schema = @simplexml_load_file($file_name);
                }
                $members = array_keys(get_object_vars($row));
                foreach ($members as $member)
                {
                    $skip = false;
                    if ($schema)
                    {
                        $col = $schema->xpath("columns/column[@name='$member']");
                        if ((string)@$col[0]->type == "text" || (string)@$col[0]->type == "varchar" && (string)@$col[0]->allow_html == "true" && (string)@$col[0]->encode_html == "false")
                        {
                            $skip = true;
                        }
                    }
                    if (!$skip)
                    {
                        $row->$member = htmlentities($row->$member, ENT_COMPAT, nbf_common::nb_strtoupper(nbf_cms::$interop->char_encoding));
                    }
                }
            }
        }

        return $row;
    }

    /**
    * Binds the values in the given array to the matching columns on the given table, either inserting
    * or updating a record (depending on whether a value is supplied for the primary key or not). This
    * function uses the database schema XML files to determine what to bind and what data to allow (eg.
    * html, int).
    *
    * @param string $table_name Name of table to bind to (hard code a valid value please! No user input!)
    * @param array $array Associative array whose keys match the table columns (any keys not represented by
    * table columns will be ignored). If no primary key value is present, a new record will be inserted,
    * otherwise the existing record will be updated.
    * @return boolean Whether or not a record was successfully inserted or updated.
    */
    public function bind_and_save($table_name, $array, $overwrite_primary_key = false)
    {
        //Remove prefixes
        $table_name = str_replace("#__nbill_", "", $table_name);

        $file_name = nbf_cms::$interop->nbill_admin_base_path . "/framework/database/schema/$table_name.xml";
        if (file_exists($file_name))
        {
            $schema = @simplexml_load_file($file_name);

            if ($schema)
            {
                //Check whether we are inserting or updating (see if any pk value exists)
                $insert = false;
                $auto_increment = false;
                $pk_cols = array();
                foreach ($schema->primary->columns->column as $pk_col)
                {
                    $pk_cols[] = (string)$pk_col->name;
                }
                $record_check = array(); //Holds criteria for checking whether a record already exists or not
                foreach ($pk_cols as $pk_col)
                {
                    $pk_node = $schema->xpath("columns/column[@name='$pk_col']");
                    switch (@$pk_node[0]->type)
                    {
                        case "int":
                        case "tinyint":
                        case "smallint":
                        case "mediumint":
                        case "bigint":
                        case "integer":
                        case "long":
                            $auto_increment = @$pk_node[0]->extra == "auto_increment";
                            if ($auto_increment && intval(@$array[$pk_col]) == 0)
                            {
                                $insert = true;
                                break 2;
                            }
                    }

                    if (array_key_exists($pk_col, $array))
                    {
                        $record_check[] = $pk_col . "='" . $array[$pk_col] . "'";
                    }
                }
                if (!$insert && count($record_check) > 0)
                {
                    //For multi-part or non-auto-incrementing keys, check whether a record already exists
                    $sql = "SELECT COUNT(*) FROM `#__nbill_$table_name` WHERE " . implode(" AND ", $record_check);
                    $this->setQuery($sql);
                    if (!$this->loadResult())
                    {
                        $insert = true;
                    }
                }

                $bound_items = array();

                foreach ($schema->columns->column as $column)
                {
                    if (isset($array[(string)$column['name']]))
                    {
                        switch ($column->type)
                        {
                            case "int":
                            case "tinyint":
                            case "smallint":
                            case "mediumint":
                            case "bigint":
                            case "integer":
                            case "long":
                                if (nbf_common::nb_strtolower($array[(string)$column['name']]) == "on")
                                {
                                    $array[(string)$column['name']] = 1; //Convert checkbox selection to integer value
                                }
                                $array[(string)$column['name']] = intval($array[(string)$column['name']]);
                                break;
                            case "char":
                            case "varchar":
                            case "text":
                            case "tinytext":
                            case "mediumtext":
                            case "longtext":
                            case "blob":
                            case "tinyblob":
                            case "mediumblob":
                            case "longblob":
                            case "binary":
                            case "varbinary":
                                $array[(string)$column['name']] = nbf_common::get_param($array, (string)$column['name'], null, false, nbf_common::nb_strtolower($column->encode_html) == "true", (nbf_common::nb_strtolower($column->allow_html) == "true" && nbf_common::nb_strtolower($column->encode_html) != "true"));
                                break;
                        }
                        if ($overwrite_primary_key || array_search((string)$column['name'], $pk_cols) === false || ($insert && !$auto_increment)) //Check whether to add primary key
                        {
                            if (nbf_common::nb_strtoupper($column->null) === 'NULL' && $array[(string)$column['name']] === 'NULL')
                            {
                                $bound_items[(string)$column['name']] = '**!!NULL!!**';
                            }
                            else
                            {
                                $bound_items[(string)$column['name']] = $array[(string)$column['name']];
                            }
                        }
                    }
                    else if ($insert)
                    {
                        //No value supplied. If null not allowed, we must provide a default value (strict mode on Windows will fail otherwise)
                        if ((string)$column->null !== 'NULL')
                        {
                            switch ($column->type)
                            {
                                case "int":
                                case "tinyint":
                                case "smallint":
                                case "mediumint":
                                case "bigint":
                                case "integer":
                                case "long":
                                    $bound_items[(string)$column['name']] = intval((string)$column->default);
                                    break;
                                default:
                                    $bound_items[(string)$column['name']] = (string)$column->default;
                                    break;
                            }
                        }
                    }
                }

                if (count($bound_items) > 0)
                {
                    $sql = "";
                    if ($insert)
                    {
                        $sql = "INSERT INTO `#__nbill_$table_name` (";
                        foreach (array_keys($bound_items) as $column)
                        {
                            $sql .= "`" . $column . "`, ";
                        }
                        //Remove the final comma and space
                        $sql = substr($sql, 0, strlen($sql) - 2);
                        $sql .= ") VALUES (";
                        foreach (array_values($bound_items) as $value)
                        {
                            if ($value === '**!!NULL!!**')
                            {
                                $sql .= "NULL, ";
                            }
                            else
                            {
                                $sql .= "'" . strval($value) . "', ";
                            }
                        }
                        //Remove the final comma and space
                        $sql = substr($sql, 0, strlen($sql) - 2);
                        $sql .= ")";
                    }
                    else
                    {
                        if (array_key_exists($pk_col, $array))
                        {
                            $sql = "UPDATE `#__nbill_$table_name` SET ";
                            foreach ($bound_items as $bound_key=>$bound_value)
                            {
                                $sql .= "`" . $bound_key . "` = ";
                                if ($bound_value === '**!!NULL!!**')
                                {
                                    $sql .= "NULL, ";
                                }
                                else
                                {
                                    $sql .= "'" . strval($bound_value) . "', ";
                                }
                            }
                            //Remove the final comma and space
                            $sql = substr($sql, 0, strlen($sql) - 2);
                            $sql .= " WHERE ";
                            $where = "";
                            foreach ($pk_cols as $pk_col)
                            {
                                if (nbf_common::nb_strlen($where) > 0)
                                {
                                    $where .= " AND ";
                                }
                                $where .= "`$pk_col` = '" . strval($array[$pk_col]) . "'";
                            }
                            $sql .= $where;
                        }
                    }
                    if (nbf_common::nb_strlen($sql) > 0)
                    {
                        $this->setQuery($sql);
                        return $this->query();
                    }
                    else
                    {
                        $this->_errorMsg = "Bind failed - no primary key in array";
                        return false;
                    }
                }
                else
                {
                    $this->_errorMsg = "Bind failed - no matching columns found in database schema XML file '$table_name.xml'";
                    return false;
                }
            }
            else
            {
                $this->_errorMsg = "Unable to load database schema XML file '$table_name.xml'";
                return false;
            }
        }
        else
        {
            $this->_errorMsg = "No database schema XML file found for table '$table_name'";
            return false;
        }
    }

    /**
    * Returns the MySQL server version number as an integer (eg. 4.1.0 returns 40100)
    */
    public function getVersion()
    {
        if ($this->_use_mysql)
        {
            return mysql_get_server_info($this->_mysql);
        }
        else
        {
            return $this->_mysqli->server_version;
        }
    }

    /**
    * Deletes all nBill tables from the database - USE WITH CAUTION!!!
    */
    function delete_tables()
    {
        //Delete verifier cookie and file, if they exist
        @setcookie ("nbverifier", "", nbf_common::nb_time() - 3600);
        if (file_exists(nbf_cms::$interop->site_temp_path . "/0336fg89.nb"))
        {
            @unlink(nbf_cms::$interop->site_temp_path . "/0336fg89.nb");
        }

        $sql = "SHOW TABLES";
        $this->setQuery($sql);
        $tables = $this->loadObjectList();
        if (!$tables)
        {
            $tables = array();
        }

        foreach ($tables as $table)
        {
            foreach ($table as $tablename)
            {
                //There will only be 1, but we don't know the name of the index so foreach is easiest...
                $name_parts = explode("_", $tablename);
                if (count($name_parts) > 1 && $name_parts[1] == "nbill")
                {
                    //This is a Billing table
                    $sql = "DROP TABLE $tablename";
                    $this->setQuery($sql);
                    $this->query();
                }
            }
        }
    }

    function get_xref_tables()
    {
    	//Get xref tables
    	$sql = "SHOW TABLES LIKE '" . $this->_table_prefix . "nbill_xref_%'";
		$this->setQuery($sql);
		$xref_table_list = $this->loadResultArray();
		$xref_tables = array();
		foreach ($xref_table_list as $table_name)
		{
			$table = substr($table_name, nbf_common::nb_strpos($table_name, "nbill_xref_") + 11);
			$xref_tables[$table] = nbf_common::nb_ucwords(str_replace("_", " ", $table));
		}
		$xref_tables['nbill_sql_list'] = NBILL_SQL_LIST;
		return $xref_tables;
	}

	/**
	* Defines entity (and entity_contact) mapping columns
	*/
	function get_entity_mapping($db_columns_only = false)
	{
		//Load supporting language file (if not already present)
		nbf_common::load_language("core.profile_fields");
		$entity_map = array();
		$entity_map["add_name_to_invoice"] = NBILL_CLIENT_ADD_NAME_TO_INVOICE;
		$entity_map["reference"] = NBILL_CLIENT_REF;
		$entity_map["company_name"] = NBILL_COMPANY_NAME;
		$entity_map["address_1"] = NBILL_ADDRESS_1;
		$entity_map["address_2"] = NBILL_ADDRESS_2;
		$entity_map["address_3"] = NBILL_ADDRESS_3;
		$entity_map["town"] = NBILL_TOWN;
		$entity_map["state"] = NBILL_STATE;
		$entity_map["postcode"] = NBILL_POSTCODE;
		$entity_map["country"] = NBILL_COUNTRY;
        if (!$db_columns_only) {
            $entity_map["same_as_billing"] = NBILL_ADDRESS_SAME_AS_BILLING;
            $entity_map["shipping_address_1"] = NBILL_SHIPPING_ADDRESS_1;
            $entity_map["shipping_address_2"] = NBILL_SHIPPING_ADDRESS_2;
            $entity_map["shipping_address_3"] = NBILL_SHIPPING_ADDRESS_3;
            $entity_map["shipping_town"] = NBILL_SHIPPING_TOWN;
            $entity_map["shipping_state"] = NBILL_SHIPPING_STATE;
            $entity_map["shipping_postcode"] = NBILL_SHIPPING_POSTCODE;
            $entity_map["shipping_country"] = NBILL_SHIPPING_COUNTRY;
        }
        $entity_map["website_url"] = NBILL_WEBSITE;
		$entity_map["tax_zone"] = NBILL_CLIENT_TAX_ZONE;
		$entity_map["tax_exemption_code"] = NBILL_TAX_EXEMPTION_CODE;
		$entity_map["email_invoice_option"] = NBILL_EMAIL_INVOICE_OPTIONS;
		$entity_map["reminder_emails"] = NBILL_EMAIL_REMINDERS;
		return $entity_map;
	}

	/**
	* Define contact mapping columns
	*/
	function get_contact_mapping($db_columns_only = false)
	{
		//Load supporting language file (if not already present)
		nbf_common::load_language("core.profile_fields");
		$contact_map["first_name"] = NBILL_FIRST_NAME;
        $contact_map["last_name"] = NBILL_LAST_NAME;
		$contact_map["address_1"] = NBILL_ADDRESS_1;
		$contact_map["address_2"] = NBILL_ADDRESS_2;
		$contact_map["address_3"] = NBILL_ADDRESS_3;
		$contact_map["town"] = NBILL_TOWN;
		$contact_map["state"] = NBILL_STATE;
		$contact_map["postcode"] = NBILL_POSTCODE;
		$contact_map["country"] = NBILL_COUNTRY;
        if (!$db_columns_only) {
            $contact_map["same_as_billing"] = NBILL_ADDRESS_SAME_AS_BILLING;
            $contact_map["shipping_address_1"] = NBILL_SHIPPING_ADDRESS_1;
            $contact_map["shipping_address_2"] = NBILL_SHIPPING_ADDRESS_2;
            $contact_map["shipping_address_3"] = NBILL_SHIPPING_ADDRESS_3;
            $contact_map["shipping_town"] = NBILL_SHIPPING_TOWN;
            $contact_map["shipping_state"] = NBILL_SHIPPING_STATE;
            $contact_map["shipping_postcode"] = NBILL_SHIPPING_POSTCODE;
            $contact_map["shipping_country"] = NBILL_SHIPPING_COUNTRY;
        }
        $contact_map["email_address"] = NBILL_EMAIL_ADDRESS;
		$contact_map["email_address_2"] = NBILL_EMAIL_ADDRESS_2;
		$contact_map["telephone"] = NBILL_TELEPHONE;
		$contact_map["telephone_2"] = NBILL_TELEPHONE_2;
		$contact_map["mobile"] = NBILL_MOBILE;
		$contact_map["fax"] = NBILL_FAX;
		$contact_map["username"] = NBILL_USERNAME;
		$contact_map["password"] = NBILL_PASSWORD;
		return $contact_map;
	}

    function translate_object_array($result_objects)
    {
        $ret_val = array();
        foreach ($result_objects as $key=>$result_object)
        {
            $ret_val[$key] = $this->translate_object($result_object);
        }
        return $ret_val;
    }

    /**
    * Detect translations for result object properties
    * @param mixed $result_object
    */
    function translate_object($result_object)
    {
        $convert_to_array = false;
        if ($result_object)
        {
            if (is_array($result_object))
            {
                $result_object = (object)$result_object;
                $convert_to_array = true;
            }
            $table_names = $this->get_table_names_in_sql();
            $columns = array_keys(get_object_vars($result_object));
            $decoded_columns = $this->decode_aliases($columns);

            //Find primary key (Id)
            $primary_key = null;
            for ($key_pointer = 0; $key_pointer < count($columns); $key_pointer++)
            {
                if (strtolower($columns[$key_pointer]) == 'id')
                {
                    $primary_key = $result_object->$columns[$key_pointer];
                    break;
                }
            }
            if ($primary_key === null)
            {
                //Try aliases
                for ($key_pointer = 0; $key_pointer < count($decoded_columns); $key_pointer++)
                {
                    if (strtolower($decoded_columns[$key_pointer]) == 'id')
                    {
                        $primary_key = $result_object->$decoded_columns[$key_pointer];
                        break;
                    }
                }
            }

            if ($primary_key !== null)
            {
                foreach ($table_names as $table_name)
                {
                    foreach ($decoded_columns as $key=>$column_name)
                    {
                        //If decoded column is qualified with table name, just check it on that one table name, otherwise, check all tables listed
                        if ((strpos($column_name, ".") === false || strpos($column_name, $table_name . ".") === 0) && intval($result_object->$columns[$key]) === 0)
                        {
                            $hold_sql = $this->_sql;
                            $translation_sql = "SELECT `value` FROM " . $this->_table_prefix . "nbill_translation
                                        WHERE language = '" . nbf_cms::$interop->language . "'
                                        AND source_table = '$table_name' AND source_column = '$column_name'
                                        AND source_pk = " . intval($primary_key) . " AND published = 1";
                            $this->setQuery($translation_sql);
                            $translation = $this->loadResult();
                            $this->_sql = $hold_sql;
                            if ($translation)
                            {
                                $result_object->$columns[$key] = $translation;
                            }
                        }
                    }
                }
            }

            if ($convert_to_array)
            {
                $result_object = (array)$result_object;
            }
        }
        return $result_object;
    }

    private function get_table_names_in_sql()
    {
        $table_names = array();
        if (defined('PREG_BAD_UTF8_OFFSET_ERROR')) {
            $sql_words = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', $this->_sql, -1, PREG_SPLIT_NO_EMPTY);
        } else {
            //PHP not compiled with support for UTF-8 regex matching - do it the slower way
            $sql_words = array();
            $tok = strtok($this->_sql, " \n\t\r\f\h\s");
            while ($tok !== false) {
                $sql_words[] = $tok;
                $tok = strtok(" \n\t\r\f\h\s");
            }
        }
        if ($sql_words && count($sql_words) > 0)
        {
            foreach ($sql_words as $sql_word)
            {
                $this_word = str_replace("`", "", $sql_word);
                if (stripos($this_word, $this->_table_prefix . 'nbill_') === 0 && stripos($this_word, ".") === false)
                {
                    $table_names[] = substr($this_word, strlen($this->_table_prefix) + 6);
                }
            }
        }
        return $table_names;
    }

    private function decode_aliases($columns)
    {
        //Check whether any of the column names in the supplied array are aliases (by looking for 'AS colname' in the SQL)
        $decoded_cols = array();
        $single_line_sql = str_replace("\t", " ", str_replace("\r", " ", str_replace("\n", " ", $this->_sql)));

        foreach ($columns as $key=>$column)
        {
            if (stripos($single_line_sql, ' AS ' . $column) !== false || stripos($single_line_sql, ' AS `' . $column))
            {
                //This is an alias - extract the real column name
                $sql_words = array_reverse(preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', $single_line_sql, -1, PREG_SPLIT_NO_EMPTY));
                for ($word_pointer = 0; $word_pointer < count($sql_words) - 2; $word_pointer++)
                {
                    if (strtolower(str_replace("`", "", $sql_words[$word_pointer])) == strtolower($column) && strtoupper($sql_words[$word_pointer + 1]) == 'AS' && strlen($sql_words[$word_pointer + 2]) > 0)
                    {
                        $real_column = str_replace("`", "", $sql_words[$word_pointer + 2]);
                        $decoded_cols[$key] = strtolower($real_column);
                    }
                }
            }
            else
            {
                $decoded_cols[$key] = $column;
            }
        }
        return $decoded_cols;
    }
}

/**
* Just an enumerator for the database connection details
*/
class nbf_db_connection
{
    /** @var string Name of database host (typically 'localhost') */
    public $host;
    /** @var string Name of the database */
    public $db_name;
    /** @var string Username of database user to connect with */
    public $user_name;
    /** @var string Password for database user */
    public $password;
    /** @var string Table prefix to avoid clashes with other installations using the same database (eg. 'jos_') */
    public $prefix;
    /** @var int Port number to use when connecting to the database (if not using default 3306) */
    public $port;
    /** @var string Socket or named pipe to use when connecting to the database (if applicable) */
    public $socket;

    public function __construct($host = "localhost", $db_name = "", $user_name = "", $password = "", $prefix = "", $port = 3306, $socket = null)
    {
        $this->host = $host;
        $this->db_name = $db_name;
        $this->user_name = $user_name;
        $this->password = $password;
        $this->prefix = $prefix;
        $this->port = $port;
        $this->socket = $socket;
    }
}