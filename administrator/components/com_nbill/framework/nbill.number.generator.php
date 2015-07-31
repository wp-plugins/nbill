<?php
/**
* Generates next consecutive number for orders, invoices, receipts, and payments.
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Order/Invoice/Receipt/Payment Number Generator
class nbf_number_generator
{
    static $orig_vendor_id = 1;
    static $using_master_db = false;
    static $db_class_name = 'nbf_database';

    static function get_next_number($vendor_id, $number_type, &$error)
    {
        $vendor = null;
        return self::get_next_number_and_vendor($vendor_id, $number_type, $vendor, $vendor, $vendor, $vendor, $error);
    }

    static function get_master_db($nb_database, &$vendor_id)
    {
        self::$orig_vendor_id = $vendor_id;
        self::$db_class_name = get_class($nb_database);
        $master_db_names = array();

        //First, check whether to defer to a master database instead of the local one
        $master_db = null;
        $sql = "SELECT use_master_db, master_host, master_username, master_password, master_dbname, master_table_prefix, master_vendor_id FROM #__nbill_vendor WHERE id = " . intval($vendor_id);
        $nb_database->setQuery($sql);
        $nb_database->loadObject($master_db);
        $master_db_names[] = nbf_cms::$interop->db_connection->host . "#" . nbf_cms::$interop->db_connection->db_name . "#" . nbf_cms::$interop->db_connection->prefix;

        //Go right back to the top-level parent if required
        while ($master_db && $master_db->use_master_db)
        {
            self::$using_master_db = true;
            //Check whether we've been here before (don't want to get stuck in a loop)
            if (array_search($master_db->master_host . "#" . $master_db->master_dbname . "#" . $master_db->master_table_prefix, $master_db_names) !== false)
            {
                //Stuck in a loop - abort!
                $error = NBILL_ERR_MASTER_DB_LOOP;
                $nb_database = new self::$db_class_name(nbf_cms::$interop->db_connection->host, nbf_cms::$interop->db_connection->user_name, nbf_cms::$interop->db_connection->password, nbf_cms::$interop->db_connection->db_name, nbf_cms::$interop->db_connection->prefix);
                return false;
            }
            else
            {
                $master_db_names[] = $master_db->master_host . "#" . $master_db->master_dbname . "#" . $master_db->master_table_prefix;
                $vendor_id = $master_db->master_vendor_id;
                $nb_database = new self::$db_class_name($master_db->master_host, $master_db->master_username, $master_db->master_password, $master_db->master_dbname, $master_db->master_table_prefix);
                if (!$nb_database)
                {
                    $error = NBILL_ERR_MASTER_DB_CONNECT;
                    $nb_database = new self::$db_class_name(nbf_cms::$interop->db_connection->host, nbf_cms::$interop->db_connection->user_name, nbf_cms::$interop->db_connection->password, nbf_cms::$interop->db_connection->db_name, nbf_cms::$interop->db_connection->prefix);
                    return false;
                }

                $master_db = null;
                $sql = "SELECT use_master_db, master_host, master_username, master_password, master_dbname, master_table_prefix, master_vendor_id FROM #__nbill_vendor WHERE id = " . intval($vendor_id);
                $nb_database->setQuery($sql);
                $nb_database->loadObject($master_db);
                if ($master_db == null)
                {
                    $error = NBILL_ERR_MASTER_DB_CONNECT;
                    $nb_database = new self::$db_class_name(nbf_cms::$interop->db_connection->host, nbf_cms::$interop->db_connection->user_name, nbf_cms::$interop->db_connection->password, nbf_cms::$interop->db_connection->db_name, nbf_cms::$interop->db_connection->prefix);
                    return false;
                }
            }
        }
        $nb_database->set_char_encoding(nbf_cms::$interop->db_charset);
        return $nb_database;
    }

    static function release_master_db(&$vendor_id)
    {
        $nb_database = null;
        if (self::$using_master_db) {
            $nb_database = new self::$db_class_name(nbf_cms::$interop->db_connection->host, nbf_cms::$interop->db_connection->user_name, nbf_cms::$interop->db_connection->password, nbf_cms::$interop->db_connection->db_name, nbf_cms::$interop->db_connection->prefix, false);
        } else {
            $nb_database = nbf_cms::$interop->database;
        }
        $vendor_id = self::$orig_vendor_id;
        $nb_database->set_char_encoding(nbf_cms::$interop->db_charset);
        return $nb_database;
    }

    static function get_next_number_and_vendor($vendor_id, $number_type, &$vendor_name, &$vendor_address, &$vendor_country, &$vendor_currency, &$error)
    {
        //Ensure we are starting from the child-most database
        $nb_database = nbf_cms::$interop->database;

        if ($number_type != "order" && $number_type != "quote" && $number_type != "po")
        {
            $nb_database = self::get_master_db($nb_database, $vendor_id);
            if ($nb_database === false) {
                return false;
            }
        }

        $next_no = '';

        //If number field is locked, poll the database for a maximum of 10 seconds
        $affectedRows = 0;
        $timeout = nbf_common::nb_time() + 10;
        while ($timeout > nbf_common::nb_time())
        {
            //If the next number field is not currently locked, lock it
            $sql = "UPDATE #__nbill_vendor SET $number_type" . "_no_locked = 1 WHERE id = " . $vendor_id . "
                        AND $number_type" . "_no_locked = 0";
            $nb_database->setQuery($sql);
            $nb_database->query();
            $affectedRows = @$nb_database->getAffectedRows();
            if ($affectedRows > 0)
            {
                break;
            }
            else
            {
                //Wait a bit before trying again
                sleep(1);
            }
        }
        if ($affectedRows < 1)
        {
            //We could not get a lock on the next number!
            if (strlen($nb_database->_errorMsg) > 0) {
                $error = NBILL_ERR_REPORT_INTRO . $nb_database->_errorMsg;
            } else {
                $error = sprintf(NBILL_ERR_COULD_NOT_GET_NEXT_NO, $number_type, $number_type);
            }
            $nb_database = self::release_master_db($vendor_id);
            return false;
        }

        //Get next number, increment by one, then release the lock
        $results = null;
        $sql = "SELECT vendor_name, vendor_address, vendor_country, vendor_currency, next_$number_type" .
                    "_no FROM #__nbill_vendor WHERE id = " . $vendor_id . "
                    AND $number_type" . "_no_locked = 1";
        $nb_database->setQuery($sql);
        $results = $nb_database->loadObjectList();
        if ($results && count($results) > 0)
        {
            $col_name = "next_" . $number_type . "_no";
            $number = $results[0]->$col_name;
            $vendor_name = $results[0]->vendor_name;
            $vendor_address = $results[0]->vendor_address;
            $vendor_country = $results[0]->vendor_country;
            $vendor_currency = $results[0]->vendor_currency;
            $next_no = self::increment_no($number, $number_type);
            if ($next_no == sprintf(NBILL_ERR_NO_NOT_NUMERIC, $number_type, $number_type, $number_type))
            {
                //Unlock database field and return the error
                $sql = "UPDATE #__nbill_vendor SET $number_type" . "_no_locked = 0 WHERE id = " . $vendor_id . "
                            AND $number_type" . "_no_locked = 1";
                $nb_database->setQuery($sql);
                $nb_database->query();

                $error = sprintf(NBILL_ERR_NO_NOT_NUMERIC, $number_type, $number_type, $number_type);
                $nb_database = self::release_master_db($vendor_id);
                return false;
            }
            //Update and unlock
            $sql = "UPDATE #__nbill_vendor SET next_$number_type" . "_no = '$next_no', $number_type" . "_no_locked = 0 WHERE
                        id = $vendor_id AND $number_type" . "_no_locked = 1";
            $nb_database->setQuery($sql);
            $nb_database->query();
        }
        else
        {
            //Next number not found - unlock database field and return the error
            $sql = "UPDATE #__nbill_vendor SET $number_type" . "_no_locked = 0 WHERE id = $vendor_id
                        AND $number_type" . "_no_locked = 1";
            $nb_database->setQuery($sql);
            $nb_database->query();
            $error = sprintf(NBILL_ERR_NO_NOT_FOUND, $number_type, $number_type);
            $nb_database = self::release_master_db($vendor_id);
            return false;
        }

        if (nbf_common::nb_strlen($number) == 0)
        {
            $error = sprintf(NBILL_ERR_NO_NOT_FOUND, $number_type, $number_type);
            $nb_database = self::release_master_db($vendor_id);
            return false;
        }

        $nb_database = self::release_master_db($vendor_id);

        nbf_common::fire_event("number_generated", array("vendor_id"=>$vendor_id, "type"=>$number_type, "number"=>$number));
        return $number;
    }

    static function increment_no($number, $number_type)
    {
        $prefix = "";
        for ($i = nbf_common::nb_strlen($number); $i > -1; $i--)
        {
            $char = substr($number, $i - 1, 1);
            if (!is_numeric($char))
            {
                //Reached beginning of numeric part
                $prefix = substr($number, 0, $i);
                $number = substr($number, $i);
                break;
            }
        }
        if (is_numeric($number))
        {
            return $prefix . str_pad($number + 1, nbf_common::nb_strlen($number), "0", STR_PAD_LEFT);
        }
        else
        {
            return sprintf(NBILL_ERR_NO_NOT_NUMERIC, $number_type, $number_type, $number_type);
        }
    }
}