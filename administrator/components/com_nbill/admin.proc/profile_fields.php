<?php
/**
* Main processing file for profile fields editor
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');//Load language files
nbf_common::load_language("xref");
nbf_common::load_language("core.profile_fields");
nbf_common::load_language("form.editor");

switch ($task)
{
	case "silent":
        break;
    case "new":
	    $cid[0] = null;
        //fall through
    case "edit":
		editProfileField($cid[0]);
		break;
	case "reorder":
		editProfileField($id, true);
		break;
	case "apply":
		saveProfileField($id);
		if (!$id)
		{
			$id = intval(nbf_common::get_param($_POST,'id'));
		}
		editProfileField($id);
		break;
	case "save":
		saveProfileField($id);
		showProfileFields();
		break;
	case "remove":
	case "delete":
		deleteProfileFields($cid);
		showProfileFields();
		break;
	case "cancel":
		showProfileFields();
		break;
	case "orderup":
		moveFieldUp($cid[0]);
		showProfileFields();
		break;
	case "orderdown":
		moveFieldDown($cid[0]);
		showProfileFields();
		break;
	case "publish":
		publishFields($cid);
		showProfileFields();
		break;
	case "unpublish":
		unpublishFields($cid);
		showProfileFields();
		break;
    case "required":
        requireFields($cid);
        showProfileFields();
        break;
    case "not_required":
        unrequireFields($cid);
        showProfileFields();
        break;
	case "fieldoptions":
		include_once(nbf_cms::$interop->nbill_admin_base_path . "/field.options.php");
		break;
	default:
		showProfileFields();
		break;
}

function showProfileFields()
{
    $nb_database = nbf_cms::$interop->database;

	$sql = "SELECT count(*) FROM #__nbill_profile_fields";
	$nb_database->setQuery($sql);
	$total = $nb_database->loadResult();

	//Add page navigation
	$pagination = new nbf_pagination("profile_fields", $total);

	//Load the records
	$sql = "SELECT #__nbill_profile_fields.*, #__nbill_xref_field_type.description AS field_type_description,
            #__nbill_order_form_fields.id AS in_use
			FROM #__nbill_profile_fields
			LEFT JOIN #__nbill_xref_field_type ON #__nbill_profile_fields.field_type = #__nbill_xref_field_type.code
            LEFT JOIN #__nbill_order_form_fields ON #__nbill_profile_fields.name = #__nbill_order_form_fields.name
                AND #__nbill_profile_fields.entity_mapping = #__nbill_order_form_fields.entity_mapping
                AND #__nbill_profile_fields.contact_mapping = #__nbill_order_form_fields.contact_mapping
            GROUP BY #__nbill_profile_fields.id
			ORDER BY ordering LIMIT $pagination->list_offset, $pagination->records_per_page";
	$nb_database->setQuery($sql);
	$rows = $nb_database->loadObjectList();
	if (!$rows)
	{
		$rows = array();
	}

    if (strtolower(nbf_version::$suffix) == 'lite') {
        foreach ($rows as $row) {
            $row->in_use = false;
        }
    }

    //Check whether we need to load any language files for custom fields
    foreach ($rows as $row)
    {
        if (!defined($row->field_type_description))
        {
            nbf_common::load_language('xref.field_type');
            if (!defined($row->field_type_description))
            {
                nbf_common::load_language('xref.field_type.' . strtolower($row->field_type));
            }
        }
    }

	nBillProfileFields::showProfileFields($rows, $pagination);
}

function editProfileField($field_id)
{
	include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
    $nb_database = nbf_cms::$interop->database;

    $row = $nb_database->load_record("#__nbill_profile_fields", $field_id);

	//Get field options
    $form_fields_options = null;
    if ($field_id)
    {
        $form_fields_options = null;
	    $sql = "SELECT * FROM #__nbill_profile_fields_options WHERE field_id = $field_id ORDER BY ordering";
	    $nb_database->setQuery($sql);
	    $form_fields_options = $nb_database->loadObjectList();
    }
	if (!$form_fields_options)
	{
		$form_fields_options = array();
	}

	//Get field types
	$field_types = nbf_xref::get_field_types(-1);

	$xref_tables = $nb_database->get_xref_tables();
	$entity_map = $nb_database->get_entity_mapping();
	$contact_map = $nb_database->get_contact_mapping();

    //Check whether this field is in use on any order forms
    $in_use = false;
    

    ob_start();
	nBillProfileFields::editProfileField($field_id, $row, $field_types, $xref_tables, $entity_map, $contact_map, $form_fields_options, $in_use);
    $html = ob_get_clean();
    $output = nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'edit', get_defined_vars(), $html);
    echo $output;
}

function saveProfileField($field_id)
{
	$nb_database = nbf_cms::$interop->database;

	if (!$field_id)
    {
        //Find next ordering
        $sql = "SELECT ordering FROM #__nbill_profile_fields ORDER BY ordering DESC LIMIT 1";
        $nb_database->setQuery($sql);
        $highest = $nb_database->loadResult();
        if ($highest || $highest === "0")
        {
            $_POST['ordering'] = $highest + 1;
        }
        else
        {
            $_POST['ordering'] = 0;
        }
	}

    

	$nb_database->bind_and_save("#__nbill_profile_fields", $_POST);
    if (!$field_id)
    {
        $_POST['id'] = $nb_database->insertid();
        $field_id = nbf_common::get_param($_POST, 'id');
    }

    //Save Options
    $option_ids = array();
    $field_option_ids = array();
    $options = html_entity_decode(urldecode(nbf_common::get_param($_REQUEST, 'serialized_options', null, true)));
    $options = unserialize($options);
    if ($options)
    {
        foreach ($options as &$option)
        {
            if (substr($option['id'], 0, 6) == "added_")
            {
                $option['id'] = null;
            }
            $option['option_value'] = $option['code'];
            $option['option_description'] = $option['description'];
            $option['field_id'] = $field_id;
            $nb_database->bind_and_save("#__nbill_profile_fields_options", $option);
            if (!$option['id'])
            {
                $option['id'] = $nb_database->insertid();
                
            }
            else
            {
                
            }
            $option_ids[] = $option['id'];
        }
    }
    //Delete any no longer required
    $sql = "DELETE FROM #__nbill_profile_fields_options WHERE field_id = " . $field_id;
    if (count($option_ids) > 0)
    {
        $sql .= " AND id NOT IN (" . implode(",", $option_ids) . ")";
    }
    $nb_database->setQuery($sql);
    $nb_database->query();
    
    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'save', get_defined_vars());
}

function deleteProfileFields($id_array)
{
	$nb_database = nbf_cms::$interop->database;

    nbf_common::hook_extension(nbf_common::get_param($_REQUEST, 'action'), 'delete', get_defined_vars());
    nbf_common::fire_event("profile_field_deleted", array("ids"=>implode(",", $id_array)));

	if (nbf_common::get_param($_REQUEST, 'apply_to_existing'))
    {
        $sql = "UPDATE #__nbill_order_form_fields
                INNER JOIN #__nbill_profile_fields
                ON #__nbill_order_form_fields.name = #__nbill_profile_fields.name
                AND #__nbill_order_form_fields.entity_mapping = #__nbill_profile_fields.entity_mapping
                AND #__nbill_order_form_fields.contact_mapping = #__nbill_profile_fields.contact_mapping
                SET #__nbill_order_form_fields.published = 0
                WHERE #__nbill_profile_fields.id IN (" . implode(",", $id_array) . ")";
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

	$sql = "DELETE FROM #__nbill_profile_fields WHERE id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

    $sql = "DELETE FROM #__nbill_profile_fields_options WHERE field_id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();

    //Close any gaps in the ordering
    $sql = "SELECT id, ordering FROM #__nbill_profile_fields ORDER BY ordering";
    $nb_database->setQuery($sql);
    $fields = $nb_database->loadObjectList();
    $ordering = 0;
    foreach ($fields as $field)
    {
        $sql = "UPDATE #__nbill_profile_fields SET ordering = $ordering WHERE id = " . intval($field->id);
        $nb_database->setQuery($sql);
        $nb_database->query();
        $ordering++;
    }
}

function moveFieldUp($field_id)
{
	$nb_database = nbf_cms::$interop->database;

	$sql = "SELECT ordering FROM #__nbill_profile_fields WHERE id = $field_id";
	$nb_database->setQuery($sql);
	$nb_database->loadObject($row);
	if ($row->ordering > 0)
	{
		$orderings = array();
		$sql = "SELECT id, ordering FROM #__nbill_profile_fields ORDER BY ordering";
		$nb_database->setQuery($sql);
		$fields = $nb_database->loadObjectList();
		if (!$fields)
		{
			$fields = array();
		}
		$prev_field_id = -1;
		$ordering = 0;
		foreach ($fields as $field)
		{
			if ($field->id == $field_id)
			{
				$orderings[$field->id] = $orderings[$prev_field_id];
				$orderings[$prev_field_id] = $ordering;
			}
			else
			{
				$orderings[$field->id] = $ordering;
			}
			$ordering++;
			$prev_field_id = $field->id;
		}
		foreach ($orderings as $id=>$ordering)
		{
			$sql = "UPDATE #__nbill_profile_fields SET ordering = $ordering WHERE id = $id";
			$nb_database->setQuery($sql);
			$nb_database->query();
		}
	}
}

function moveFieldDown($field_id)
{
	$nb_database = nbf_cms::$interop->database;

	$orderings = array();
	$sql = "SELECT id, ordering FROM #__nbill_profile_fields ORDER BY ordering";
	$nb_database->setQuery($sql);
	$fields = $nb_database->loadObjectList();
	if (!$fields)
	{
		$fields = array();
	}
	$next_field = false;
	$ordering = 0;
	foreach ($fields as $field)
	{
		if ($next_field)
		{
			$orderings[$field->id] = $orderings[$field_id];
			$orderings[$field_id] = $ordering;
			$next_field = false;
		}
		else
		{
			$orderings[$field->id] = $ordering;
		}
		if ($field->id == $field_id)
		{
			$next_field = true;
		}
		$ordering++;
	}
	foreach ($orderings as $id=>$ordering)
	{
		$sql = "UPDATE #__nbill_profile_fields SET ordering = $ordering WHERE id = $id";
		$nb_database->setQuery($sql);
		$nb_database->query();
	}
}

function publishFields($id_array)
{
	$nb_database = nbf_cms::$interop->database;

	$sql = "UPDATE #__nbill_profile_fields SET published = 1 WHERE id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

    
}

function unpublishFields($id_array)
{
	$nb_database = nbf_cms::$interop->database;
	$sql = "UPDATE #__nbill_profile_fields SET published = 0 WHERE id IN (" . implode(",", $id_array) . ")";
	$nb_database->setQuery($sql);
	$nb_database->query();

    
}

function requireFields($id_array)
{
    $nb_database = nbf_cms::$interop->database;

    $sql = "UPDATE #__nbill_profile_fields SET required = 1, label = CONCAT('* ', REPLACE(label, '* ', '')) WHERE id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();

    
}

function unrequireFields($id_array)
{
    $nb_database = nbf_cms::$interop->database;
    $sql = "UPDATE #__nbill_profile_fields SET required = 0, label = REPLACE(label, '* ', '') WHERE id IN (" . implode(",", $id_array) . ")";
    $nb_database->setQuery($sql);
    $nb_database->query();

    
}