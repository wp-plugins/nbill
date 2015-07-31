<?php
/**
* Class file just containing static methods relating to the product category hierarchy.
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
* Static functions relating to the the product category hierarchy
* 
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_category_hierarchy
{
    public static function get_category_hierarchy($vendor_id, $ignore_cat_id = -999)
    {
        $nb_database = nbf_cms::$interop->database;
        
        //Return array with category names hierarchically indented for display in dropdown
        $sql = "SELECT * FROM #__nbill_product_category";
        if ($vendor_id != null)
        {
            $sql .= " WHERE vendor_id = $vendor_id";
        }
        $sql .= " ORDER BY parent_id, ordering";
        $nb_database->setQuery($sql);
        $cats = $nb_database->loadObjectList();
        
        if (!$cats)
        {
            $cats = array();
        }
        
        $cat_info = array();
        
        //Find the root
        foreach ($cats as $cat)
        {
            if ($cat->parent_id == -1 || $cat->parent_id == 0)
            {
                $cat_info["id"] = $cat->id;
                break;
            }
        }
        
        if (isset($cat_info["id"]) && nbf_common::nb_strlen($cat_info["id"]) > 0)
        {
            //Order the categories according to hierarchy
            if (nbf_common::nb_strlen($cats[0]->name) > 0)
            {
                $cat_info["name"] = $cats[0]->name;
            }
            if (nbf_common::nb_strlen($cats[0]->name) > 0)
            {
                $cat_info["description"] = $cats[0]->description;
            }
            
            $cat_info["ordering"] = 0;
            $cat_info["vendor_id"] = $vendor_id;
            $cat_info["parent_id"] = -1;
            $cat_info["is_first"] = true;
            $cat_info["is_last"] = true;
            $newcats[] = $cat_info;
            self::add_child_cats($cat_info["id"], $cats, $newcats, 1, $ignore_cat_id);
        }
        else 
        {
            $cat_info["id"] = -1;
            $cat_info["name"] = NBILL_ROOT;
            $cat_info["description"] = "";
            $cat_info["ordering"] = 0;
            $cat_info["vendor_id"] = $vendor_id;
            $cat_info["parent_id"] = 0;
            $cat_info["is_first"] = true;
            $cat_info["is_last"] = true;
            $newcats[] = $cat_info;
        }
        return $newcats;
    }

    public static function add_child_cats($cat_id, $cat_source_array, &$cat_target_array, $level, $ignore_cat_id)
    {
        //Add any children of $cat_id found in the source array to the target array
        //$level tells us the number of parents between where we are and the root
        if (count($cat_source_array) > 0)
        {
            $is_first = true;
            for ($i = 0; $i < count($cat_source_array); $i++)
            {
                $source_cat = $cat_source_array[$i];
                if ($source_cat->parent_id == $cat_id && $source_cat->id != $ignore_cat_id)
                {
                    $prefix = str_repeat(".....", $level);
                    $cat_info = array();
                    $cat_info["id"] = $source_cat->id;
                    $cat_info["name"] = $prefix . $source_cat->name;
                    $cat_info["description"] = $source_cat->description;
                    $cat_info["ordering"] = $source_cat->ordering;
                    $cat_info["vendor_id"] = $source_cat->vendor_id;
                    $cat_info["parent_id"] = $source_cat->parent_id;
                    $cat_info["is_first"] = $is_first;
                    $is_first = false;
                    $cat_info["is_last"] = false;
                    if ($i + 1 == count($cat_source_array))
                    {
                        $cat_info["is_last"] = true;
                    }
                    else 
                    {
                        if ($cat_source_array[$i + 1]->parent_id != $source_cat->parent_id)
                        {
                            $cat_info["is_last"] = true;
                        }
                    }
                    $cat_target_array[] = $cat_info;
                    self::add_child_cats($source_cat->id, $cat_source_array, $cat_target_array, $level + 1, $ignore_cat_id);
                }
            }
        }
    }
}