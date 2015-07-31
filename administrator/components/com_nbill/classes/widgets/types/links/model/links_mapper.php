<?php
/**
* Mapper to load menu links from the database
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class LinksMapper extends nBillWidgetMapper
{
    public function loadDefaultLinks()
    {
        //This is used when resetting to factory defaults
        $links = array();
        $links[] = $this->loadMenuLink(3);
        $links[] = $this->loadMenuLink(5);
        $links[] = $this->loadMenuLink(10);
        $links[] = $this->loadMenuLink(12);
        $links[] = $this->loadMenuLink(13);
        $links[] = $this->loadMenuLink(14);
        $links[] = $this->loadMenuLink(16);
        $links[] = $this->loadMenuLink(17);
        $links[] = $this->loadMenuLink(22);
        $links[] = $this->loadMenuLink(24);
        $links[] = $this->loadMenuLink(25);
        $links[] = $this->loadMenuLink(26);
        $links[] = $this->loadMenuLink(27);
        $links[] = $this->loadMenuLink(36);
        $links[] = $this->loadMenuLink(37);
        $links[] = $this->loadMenuLink(45);
        return $links;
    }

    public function loadAllMenuLinks()
    {
        $link_items = $this->loadAllCoreMenuLinks();
        $link_items = array_merge($link_items, $this->loadAllExtensionMenuLinks());
        return $link_items;
    }

    protected function loadAllCoreMenuLinks()
    {
        $sql = "SELECT * FROM #__nbill_menu
                WHERE parent_id > 0
                AND image LIKE '[NBILL_FE]/images/icons/%'
                AND url LIKE '[NBILL_ADMIN]%'
                AND published = 1
                ORDER BY parent_id, ordering";
        $this->db->setQuery($sql);
        $menu_items = $this->db->loadObjectList();

        $link_items = array();
        foreach ($menu_items as $menu_item)
        {
            $link_items[] = $this->mapMenuLink($menu_item);
        }
        return $link_items;
    }

    protected function loadAllExtensionMenuLinks()
    {
        $sql = "SELECT * FROM #__nbill_extensions_menu
                WHERE image LIKE '[NBILL_FE]/images/icons/%'
                AND url LIKE '[NBILL_ADMIN]%'
                AND published = 1
                ORDER BY extension_name, parent_id, ordering";
        $this->db->setQuery($sql);
        $extension_menu_items = $this->db->loadObjectList();

        $link_items = array();
        foreach ($extension_menu_items as $ext_menu_item)
        {
            nbf_common::load_language($ext_menu_item->extension_name);
            $link_items[] = $this->mapMenuLink($ext_menu_item, LinkItem::TYPE_EXTENSION);
        }
        return $link_items;
    }

    protected function loadMenuLink($menu_id)
    {
        $menu_item = null;
        $sql = "SELECT * FROM #__nbill_menu
                WHERE parent_id > 0
                AND image LIKE '[NBILL_FE]/images/icons/%'
                AND url LIKE '[NBILL_ADMIN]%'
                AND published = 1
                AND id = " . intval($menu_id);
        $this->db->setQuery($sql);
        $this->db->loadObject($menu_item);

        return $this->mapMenuLink($menu_item);
    }

    protected function loadExtensionMenuLink($ext_menu_id)
    {
        $ext_menu_item = null;
        $sql = "SELECT * FROM #__nbill_extensions_menu
                WHERE image LIKE '[NBILL_FE]/images/icons/%'
                AND url LIKE '[NBILL_ADMIN]%'
                AND published = 1
                AND id = '" . $this->db->getEscaped($ext_menu_id) . "'";
        $this->db->setQuery($sql);
        $this->db->loadObject($ext_menu_item);

        return $this->mapMenuLink($ext_menu_item, LinkItem::TYPE_EXTENSION);
    }

    protected function mapMenuLink($db_menu_item, $type = LinkItem::TYPE_MENU)
    {
        $link_item = new LinkItem($db_menu_item->url);
        $link_item->type = $type;
        $link_item->menu_id = $db_menu_item->id;
        $link_item->image = $db_menu_item->image;
        $link_item->text = $db_menu_item->text;
        $link_item->title = $db_menu_item->description;
        return $link_item;
    }

    public function mapWidget($db_widget, $populate_from_request = false)
    {
        //Populate $_REQUEST with correct objects, then pass up to parent
        $link_items = array();
        foreach ($_REQUEST as $key=>$value) {
            if (substr($key, 0, 10) == 'favourite_' && strpos($key, '_', 10) === false && $value) {
                $link_items[] = $this->loadMenuLink(intval(substr($key, 10)));
            } else if (substr($key, 0, 14) == 'ext_favourite_' && $value) {
                $link_items[] = $this->loadExtensionMenuLink(substr($key, 14));
            } else if (substr($key, 0, 15) == 'user_favourite_' && $value) {
                //TODO: Add support for custom links

            }
        }
        $_REQUEST['links'] = $link_items;
        return parent::mapWidget($db_widget, $populate_from_request);
    }

    protected function loadParams(nBillWidget $widget, $db_widget, $populate_from_request = false)
    {
        $params = json_decode($db_widget->params, true);
        if (!$params) { //Fake it with the default items
            $params = array();
            $params['links'] = $this->loadDefaultLinks();
        }
        $base_properties = get_class_vars('nBillWidget');
        $derived_properties = get_object_vars($widget);
        foreach ($derived_properties as $key=>$value)
        {
            if (array_key_exists($key, $base_properties) === false) {
                if ($populate_from_request) {
                    if (isset($_REQUEST[$key])) {
                        $widget->$key = is_string($_REQUEST[$key]) ? html_entity_decode($_REQUEST[$key]) : $_REQUEST[$key];
                    }
                } else if (isset($params[$key])) {
                    if ($key == 'links') {
                        $links_array = $params[$key];
                        $widget->$key = $this->convertLinksFromArray($links_array);
                    } else {
                        $widget->$key = $params[$key];
                    }
                }
            }
        }
        return $widget;
    }

    protected function convertLinksFromArray($links_array)
    {
        $new_link_array = array();
        foreach ($links_array as $link)
        {
            if (is_array($link)) {
                $new_link = new LinkItem($link['url']);
                foreach ($link as $key=>$value)
                {
                    if (property_exists($new_link, $key)) {
                        $new_link->$key = $value;
                    }
                }
                $new_link_array[] = $new_link;
            } else if (is_a($link, 'LinkItem')) { //Already a LinkItem object
                $new_link_array[] = $link;
            }
        }
        return $new_link_array;
    }
}