<?php
/**
* Main processing file for translations
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');switch ($task)
{
    case "silent":
        break;
    case "edit_table":
        editTranslationTable();
        break;
    case "edit_row":
        editTranslation();
        break;
    case "apply":
        saveTranslation();
        editTranslation();
        break;
    case "save":
        saveTranslation();
        editTranslationTable();
        break;
    case "cancel":
        editTranslationTable();
        break;
    default:
        showTranslations();
        break;
}

function showTranslations()
{
    $tables = array();
    $table_files = array();

    $table_files = array_diff(scandir(nbf_cms::$interop->nbill_admin_base_path . "/translation/"), array('.', '..'));
    foreach ($table_files as $key=>$table)
    {
        if (strtolower($table) == "index.html" || substr($table, 0, 6) != "nbill_" || substr($table, strlen($table) - 4) != ".xml")
        {
            continue;
        }
        $table = str_replace(".xml", "", $table);
        $table_object = new stdClass();
        $schema = @simplexml_load_file(nbf_cms::$interop->nbill_admin_base_path . "/translation/$table.xml");
        if ($schema)
        {
            $table_object->title = trim(str_replace("nBill:", "", (string)@$schema->name));
            $table_object->description = trim(str_replace("nBill:", "", (string)@$schema->description));
        }
        if (!$table_object->title)
        {
            $table_object->title = str_replace("nbill_", "", $table);
            $table_object->title = str_replace("xref_", "XRef ", $tables[$table]);
            $table_object->title = ucwords(str_replace("_", " ", $tables[$table]));
        }
        $tables[$table] = $table_object;

    }
    asort($tables);

    nBillTranslation::showTranslations($tables);
}

function editTranslationTable()
{
    //Show the records in the selected table, ready for translating
    $nb_database = nbf_cms::$interop->database;

    $table = str_replace("..", "_", urldecode(nbf_common::get_param($_REQUEST, 'table', '', false, true)));
    $table_name = $table;
    $rows = array();
    if (strlen($table) > 0 && file_exists(nbf_cms::$interop->nbill_admin_base_path . "/translation/$table.xml"))
    {
        //Load schema so we know which columns to retrieve
        $schema = @simplexml_load_file(nbf_cms::$interop->nbill_admin_base_path . "/translation/$table.xml");
        $columns = @$schema->reference->table;
        $pk_col = "id";
        $title_column = "";
        $title_name = "";
        $table_name = trim(str_replace("nBill:", "", (string)@$schema->name));
        foreach ($columns->field as $column)
        {
            switch (strtolower((string)$column['type']))
            {
                case "referenceid":
                    $pk_col = (string)$column['name'];
                    break;
                case "titletext":
                    $title_column = (string)$column['name'];
                    $title_name = (string)$column;
                    break;
            }
        }
        if ($pk_col && $title_column)
        {
            $sql = "SELECT `$pk_col` AS `pk`, `$title_column` AS `title` FROM `#__$table` ORDER BY `$pk_col`";
            $nb_database->setQuery($sql);
            $rows = $nb_database->loadObjectList();
        }
    }

    nBillTranslation::editTable($table, $table_name, $title_name, $rows);
}

function editTranslation()
{
    //Load the values in the default language and offer translation
    $nb_database = nbf_cms::$interop->database;

    $table = str_replace("..", "_", urldecode(nbf_common::get_param($_REQUEST, 'table', '', false, true)));
    $table_name = $table;
    $row_name = "";
    $row = null;
    $display_columns = array();

    if (strlen($table) > 0 && file_exists(nbf_cms::$interop->nbill_admin_base_path . "/translation/$table.xml"))
    {
        //Load schema so we know which columns to retrieve
        $schema = @simplexml_load_file(nbf_cms::$interop->nbill_admin_base_path . "/translation/$table.xml");
        $columns = @$schema->reference->table;
        $pk_col = "id";
        $sql_columns = array();
        $table_name = trim(str_replace("nBill:", "", (string)@$schema->name));
        foreach ($columns->field as $column)
        {
            $sql_columns[] = (string)$column['name'];
            switch (strtolower((string)$column['type']))
            {
                case "referenceid":
                    $pk_col = (string)$column['name'];
                    break;
                case "titletext":
                    $row_name = (string)$column['name'];
                    break;
            }
            if (@((int)$column['translate']))
            {
                $display_columns[(string)$column['name']] = (string)$column;
            }
        }
        if ($pk_col && count($sql_columns))
        {
            $sql = "SELECT `$pk_col` AS `pk`, " . implode(", ", $sql_columns) . " FROM `#__$table` WHERE `$pk_col` = '" . nbf_common::get_param($_REQUEST, 'row') . "'";
            $nb_database->setQuery($sql);
            $nb_database->loadObject($row);
            if ($row)
            {
                $row_name = $row->$row_name;
            }
        }
    }

    $row_name = trim(strip_tags($row_name));
    if (strlen($row_name) > 100)
    {
        $row_name = substr($row_name, 0, 100) . "...";
    }

    //Get list of languages and if we have a selection in a cookie, retrieve it
    $languages = nbf_cms::$interop->get_list_of_languages(true);
    reset($languages); //Make sure the pointer is at the start
    $default_language = nbf_common::get_param($_COOKIE, 'nbill_admin_translate_lang');
    $default_language = $default_language ? $default_language : key($languages);

    //Load any existing translations for this row
    $translation = array();
    foreach ($languages as $language=>$value)
    {
        $sql = "SELECT id, source_column, value, published FROM #__nbill_translation
                WHERE language = '$language'
                AND source_table = '" . substr($table, 6) . "'
                AND source_pk = '" . nbf_common::get_param($_REQUEST, 'row') . "'";
        $nb_database->setQuery($sql);
        $translation[$language] = $nb_database->loadAssocList('source_column');
        if (!$translation[$language])
        {
            $translation[$language] = array();
        }
    }

    nBillTranslation::editTranslation($table, $table_name, $row_name, $row, $display_columns, $languages, $default_language, $translation);
}

function saveTranslation()
{
    $nb_database = nbf_cms::$interop->database;

    //Set cookie for selected language
    setcookie('nbill_admin_translate_lang', nbf_common::get_param($_REQUEST, 'language'), nbf_common::nb_strtotime("+1 year"));
    $_COOKIE['nbill_admin_translate_lang'] = nbf_common::get_param($_REQUEST, 'language'); //Will not update until next page refresh but we need it before then

    $table = str_replace("..", "_", urldecode(nbf_common::get_param($_REQUEST, 'table', '', false, true)));

    if (strlen($table) > 0 && file_exists(nbf_cms::$interop->nbill_admin_base_path . "/translation/$table.xml"))
    {
        //Load languages
        $languages = nbf_cms::$interop->get_list_of_languages(true);

        //Load schema so we know which columns to retrieve
        $schema = @simplexml_load_file(nbf_cms::$interop->nbill_admin_base_path . "/translation/$table.xml");
        $columns = @$schema->reference->table;
        $sql_columns = array();
        foreach ($columns->field as $column)
        {
            if (@((int)$column['translate']))
            {
                $sql_columns[] = (string)$column['name'];
            }
        }
        if (count($sql_columns))
        {
            //Insert or replace for each language
            foreach ($languages as $language=>$language_value)
            {
                foreach ($sql_columns as $sql_column)
                {
                    $sql = "SELECT id FROM #__nbill_translation
                            WHERE language = '$language'
                            AND source_table = '" . str_replace("nbill_", "", $table) . "'
                            AND source_column = '$sql_column'
                            AND source_pk = '" . nbf_common::get_param($_REQUEST, 'row') . "'";
                    $nb_database->setQuery($sql);
                    $translation_id = intval($nb_database->loadResult());
                    $value = nbf_common::get_param($_REQUEST, 'translation_' . $language . '_' . $sql_column, '', false, false, true);

                    if ($translation_id)
                    {
                        if (strlen($value) > 0)
                        {
                            //Update
                            $sql = "UPDATE #__nbill_translation
                                    SET `value` = '$value',
                                    published = " . intval(nbf_common::get_param($_REQUEST, 'published_' . $language . '_' . $sql_column)) . "
                                    WHERE id = $translation_id";
                        }
                        else
                        {
                            //Delete
                            $sql = "DELETE FROM #__nbill_translation WHERE id = $translation_id";
                        }
                        $nb_database->setQuery($sql);
                        $nb_database->query();
                    }
                    else
                    {
                        if (strlen($value) > 0)
                        {
                            //Insert
                            $sql = "INSERT INTO #__nbill_translation (language, source_table, source_column, source_pk, value, published)
                                    VALUES ('" . $language . "', '" . str_replace("nbill_", "", $table) . "',
                                    '$sql_column', '" . nbf_common::get_param($_REQUEST, 'row') . "',
                                    '$value', " . intval(nbf_common::get_param($_REQUEST, 'published_' . $language . '_' . $sql_column)) . ")";
                            $nb_database->setQuery($sql);
                            $nb_database->query();
                        }
                    }
                }
            }
        }
    }
}