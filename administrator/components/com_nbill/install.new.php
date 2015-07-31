<?php
/**
* Creates the nBill database tables
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

ini_set("memory_limit", "128M"); //Shouldn't be needed, but just in case

if ( !function_exists( 'property_exists' ) )
{
    function property_exists( $class, $property )
    {
        if (is_object( $class ))
        {
            $vars = get_object_vars($class);
        } else
        {
            $vars = get_class_vars($class);
        }
        return array_key_exists($property, $vars);
    }
}

/**
* Creates and populates the tables based on the schema files and SQL file(s).
* @param array $tables Optional array containing a list of table names to create (in case you want to limit it to re-building certain tables)
* @param array $sql_files Optional array containing a list of SQL files to execute (to populate the new tables)
* @param boolean $drop_existing If table already exists, we will normally drop it and re-create it, but you can override that behaviour here
*/
function new_db_install($tables = array(), $sql_files = array("install.new.sql", "install.xref_states.sql", "install.eu_vat_rates.sql"), $drop_existing = true)
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;

    //Create table structure based on XML schema files
    $file_names = array_diff(scandir(nbf_cms::$interop->nbill_admin_base_path . "/framework/database/schema/"), array('.', '..'));
    foreach($file_names as $file_name)
    {
        if (is_file(nbf_cms::$interop->nbill_admin_base_path . "/framework/database/schema/$file_name"))
        {
            $schema = @simplexml_load_file(nbf_cms::$interop->nbill_admin_base_path . "/framework/database/schema/$file_name");
            if ($schema && (!$tables || array_search($schema->name, $tables) !== false))
            {
                if ($drop_existing) {
                    //Drop existing, if applicable
                    $sql = "DROP TABLE IF EXISTS `#__nbill_" . $schema->name . "`;";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                }

                //Create new table
                $sql = "CREATE TABLE " . (!$drop_existing ? 'IF NOT EXISTS ' : '') . "`#__nbill_" . $schema->name . "` (\n";
                foreach ($schema->columns->column as $column)
                {
                    $sql .= "`" . (string)$column['name'] . "` " . $column->type;
                    if (intval($column->length))
                    {
                        $sql .= "(" . $column->length . ")";
                    }
                    if (property_exists($column, "signed"))
                    {
                        $sql .= " " . $column->signed;
                    }
                    if (property_exists($column, "extra_column_definition")) {
                        $sql .= " " . $column->extra_column_definition;
                    }
                    $sql .= " " . $column->null;
                    if (property_exists($column, "extra"))
                    {
                        $sql .= " " . $column->extra;
                    }
                    switch (strtolower($column->type))
                    {
                        case "text":
                        case "mediumtext":
                        case "blob":
                        case "mediumblob":
                            //Cannot have default values
                            $sql .= ", \n";
                            break;
                        default:
                            if (property_exists($column, "default"))
                            {
                                if (nbf_common::nb_strlen($column->default) > 0)
                                {
                                    if ($column->default == "NULL" && $column->null == "NULL")
                                    {
                                        $sql .= " default NULL, \n";
                                    }
                                    else
                                    {
                                        $sql .= " default '" . $column->default . "', \n";
                                    }
                                }
                                else if ($column->null == "NOT NULL")
                                {
                                    switch (strtolower($column->type))
                                    {
                                        case "int":
                                        case "tinyint":
                                        case "decimal":
                                            $sql .= " default 0, \n";
                                            break;
                                        default:
                                            $sql .= " default '', \n";
                                            break;
                                    }
                                }
                                else
                                {
                                    $sql .= ", \n";
                                }
                            }
                            else
                            {
                                $sql .= ", \n";
                            }
                    }
                }
                if (property_exists($schema, "primary"))
                {
                    $pk = " PRIMARY KEY (";
                    foreach ($schema->primary->columns->column as $pk_col)
                    {
                        if (strlen($pk) > 14)
                        {
                            $pk .= ",";
                        }
                        $pk .= "`" . (string)$pk_col->name . "`";
                    }
                    if (strlen($pk) > 14)
                    {
                        $sql .= $pk . "), \n";
                    }
                }
                if (property_exists($schema, "index"))
                {
                    $schema_index_array = array();
                    $schema_index_array = is_array($schema->index) ? $schema->index : array($schema->index);
                    foreach ($schema_index_array as $schema_index);
                    {
                        $index = "";
                        foreach ($schema_index->columns->column as $index_col)
                        {
                            $index .= " KEY ";
                            $index .= "`" . (string)$index_col->name . "` (`" . (string)$index_col->name . "`), \n";
                        }
                        if (nbf_common::nb_strlen($index))
                        {
                            $sql .= $index;
                        }
                    }
                }
                //Remove trailing comma
                $sql = substr($sql, 0, strlen($sql) - 3);
                $sql .= ")";
                if (nbf_cms::$interop->char_encoding == 'utf-8' || nbf_cms::$interop->char_encoding == 'utf8')
                {
                    $sql .= " DEFAULT CHARSET=utf8";
                }
                if (@$schema->engine)
                {
                    $sql .= " ENGINE=" . $schema->engine;
                }
                if (@$schema->table_option) {
                    $sql .= " " . $schema->table_option;
                }
                $nb_database->setQuery($sql);
                $nb_database->query();
                if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                {
                    nbf_globals::$db_errors[] = $nb_database->_errorMsg . " (SQL=" . $sql . ")";
                }
            }
        }
    }

    //Enter any intial data
    foreach ($sql_files as $sql_file)
    {
        $sql_file = dirname(__FILE__) . "/" . $sql_file;
        $handle = fopen($sql_file, "r");
        while (!feof($handle))
        {
            $line = fgets($handle);
            $query = "";
            while (nbf_common::nb_strpos($line, "##########") === false && !feof($handle))
            {
                $query .= $line;
                $line = str_replace("\n", "", fgets($handle));
            }
            if ((nbf_common::nb_strpos($line, "##########") !== false || feof($handle)) && nbf_common::nb_strlen($query) > 0)
            {
                //Execute query
                $nb_database->setQuery($query);
                $nb_database->query();
                if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                {
                    nbf_globals::$db_errors[] = $nb_database->_errorMsg . " (SQL=" . $query . ")";
                }
            }
        }
        fclose($handle);
    }

    //Check whether we need to populate the site id hash
    $sql = "SELECT site_id_hash FROM #__nbill_license WHERE id = 1";
    $nb_database->setQuery($sql);
    $site_hash = $nb_database->loadResult();
    if (!$site_hash)
    {
        $sql = "UPDATE #__nbill_license SET site_id_hash = '" . md5(uniqid("C", true)) . "' WHERE id = 1";
        $nb_database->setQuery($sql);
        $nb_database->query();
    }
}