<?php
abstract class nBillDataMapper
{
    protected $db;
    protected $table;

    /** @var SimpleXmlElement **/
    protected $schema;

    public function __construct(nbf_database $database, $table, $schema_path = '')
    {
        $this->db = $database;
        $this->table = $table;
        if (strlen($schema_path) == 0 || !file_exists($schema_path)) {
            $schema_path = nbf_cms::$interop->nbill_admin_base_path . "/framework/database/schema";
        }
        $this->loadSchema($schema_path);
    }

    protected function loadSchema($schema_path)
    {
        $table_name = str_replace("#__nbill_", "", $this->table);
        $file_name = $schema_path . "/$table_name.xml";
        if (file_exists($file_name))
        {
            $this->schema = @simplexml_load_file($file_name);
        }
    }

    /**
    * Returns the first matching row
    * @param mixed $object Object to populate, if found
    * @param mixed $where_clause WHERE clause to use for finding the appropriate record (if omitted, object properties will be used instead)
    * @param string $joins If WHERE clause requires joins to other tables, specify them here
    * @param string $order_by ORDER BY clause (in case of more than one matching row)
    * @param string $order_by GROUP BY clause (in case of more than one matching row)
    * @param string $having HAVING clause (in case of more than one matching row)
    * @return int Number of records found
    */
    public function findObject(&$object, $where_clause = '', $joins = '', $order_by = '', $group_by = '', $having = '')
    {
        $sql = "SELECT `" . $this->table . "`.* FROM `" . $this->table . "` ";
        if (strlen($joins) > 0) {
            $sql .= $joins;
        }
        if (strlen($where_clause) > 0) {
            $sql .= " WHERE " . $where_clause;
        } else {
            $wheres = array();
            foreach ($this->getDBMatchingKeyValuePairs($object) as $key=>$value)
            {
                $wheres[] = "`" . $key . "` = " . $this->getValueSqlString($key, $value);
            }
            if (count($wheres) > 0) {
                $sql .= implode(" AND ", $wheres);
            }
        }
        if (strlen($group_by) > 0) {
            $sql .= " GROUP BY " . $group_by;
        }
        if (strlen($having) > 0) {
            $sql .= " HAVING " . $having;
        }
        if (strlen($order_by) > 0) {
            $sql .= " ORDER BY " . $order_by;
        }
        $sql .= " LIMIT 1";
        $this->db->setQuery($sql);
        $this->db->loadObject($db_object);
        $this->mapDbToObject($object, $db_object);
        return $this->db->getAffectedRows();
    }

    /**
    * Return an appropriate string to use for the value in an SQL statement (escaped, or intval'd as appropriate for the data type, based on the XML schema file, if found, or just treated as a string [and escaped] otherwise)
    * @param string $key Column name
    * @param mixed $value Literal value
    */
    protected function getValueSqlString($key, $value)
    {
        $string = "";

        if ($this->schema)
        {
            $col = $schema->xpath("columns/column[@name='$key']");
            switch (@$col->type)
            {
                case "int":
                case "tinyint":
                case "smallint":
                case "mediumint":
                case "bigint":
                case "integer":
                case "long":
                    $string = strval(intval($value));
                    break;
                default:
                    $string = nbf_common::get_param(array($key=>$value), $key, '', false, (string)@$col[0]->encode_html != "false", (string)@$col[0]->allow_html == "true", (string)@$col[0]->allow_html == "true");
                    break;
            }
        }

        if (!$string) {
            $string = "'" . $this->db->getEscaped($value) . "'";
        }
        return $string;
    }

    /**
    * Return an array of key/value pairs where the database columns match the object properties (if a schema is available - otherwise, just returns all the object properties)
    * @param mixed $object
    */
    protected function getDBMatchingKeyValuePairs($object)
    {
        $object_vars = get_object_vars($object);

        $kvp = array();
        if ($this->schema) {
            foreach ($this->schema->columns->column as $column)
            {
                if (isset($column['name'])) {
                    $colname = (string)$column['name'];
                    if (property_exists($object, $colname)) {
                        $kvp[$colname] = $object->$colname;
                    }
                }
            }
            return $kvp;
        } else {
            return $object_vars;
        }
    }

    /**
    * Loads the object by the ID if supplied, or finds first matching record if ID not supplied
    * @param mixed $object
    * @return int Number of matching rows found
    */
    public function loadObject(&$object)
    {
        if ($object->id) {
            $sql = "SELECT `" . $this->table . "`.* FROM `" . $this->table . "` WHERE id = " . $object->id;
            $this->db->setQuery($sql);
            $this->db->loadObject($db_object);
            if ($db_object) {
                $this->mapDbToObject($object, $db_object);
                return 1;
            } else {
                return 0;
            }
        } else {
            return $this->findObject($object);
        }
    }

    protected function mapDbToObject(&$object, $db_object)
    {
        if ($db_object) {
            foreach (get_object_vars($db_object) as $key=>$value)
            {
                if (property_exists($object, $key)) {
                    $object->$key = $value;
                }
            }
        }
    }

    public function saveObject($object)
    {
        if ($object->id) {
            return $this->updateObject($object);
        } else {
            return $this->insertObject($object);
        }
    }

    /**
    * If there is not a one-to-one relationship between database columns and properties, override this method
    * @param mixed $object
    */
    protected function updateObject($object)
    {
        $sql = "UPDATE `" . $this->table . "` SET ";
        $columns = array();
        foreach ($this->getDBMatchingKeyValuePairs($object) as $key=>$value)
        {
            if ($key != 'id') {
                $columns[] = "`$key` = '" . $this->db->getEscaped($value) . "'";
            }
        }
        if (count($columns) > 0) {
            $sql .= implode(", ", $columns);
            $sql .= " WHERE `id` = " . intval($object->id);
            $this->db->setQuery($sql);
            $this->db->query();
            return (strlen($this->db->_errorMsg) == 0);
        }
        return false;
    }

    /**
    * If there is not a one-to-one relationship between database columns and public properties, override this method
    * @param mixed $object
    */
    protected function insertObject($object)
    {
        $sql = "INSERT INTO `" . $this->table . "` (";
        $columns = array();
        $values = array();
        foreach ($this->getDBMatchingKeyValuePairs($object) as $key=>$value)
        {
            if ($key != 'id') {
                $columns[] = "`" . $key . "`";
                $values[] = "'" . $this->db->getEscaped($value) . "'";
            }
        }
        if (count($columns) > 0) {
            $sql .= implode(", ", $columns);
            $sql .= ") VALUES (";
            $sql .= implode(", ", $values);
            $sql .= ")";
            $this->db->setQuery($sql);
            $this->db->query();
            return $this->db->insertid();
        }

    }
}