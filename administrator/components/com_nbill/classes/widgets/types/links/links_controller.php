<?php
/**
* Controller for menu links widget
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class LinksController extends nBillWidgetController
{
    public function showWidget($ajax_refresh = false)
    {
        //If no icons defined, add default items
        if (count($this->widget->links) == 0) {
            $this->widget->links = $this->mapper->loadDefaultLinks();
        }

        //Process image and link URLs (replace tokens, apply disabled formatting if necessary)
        foreach ($this->widget->links as &$link_item)
        {
            $link_item->prepare_for_rendering(nbf_cms::$interop->nbill_admin_base_path, nbf_cms::$interop->admin_page_prefix, nbf_cms::$interop->nbill_site_url_path, $this->widget->icon_type);
        }
        parent::showWidget($ajax_refresh);
    }

    public function showConfig($then_exit = true)
    {
        nbf_common::load_language('favourites');
        $this->clearAllBuffers();
        $menu_links = $this->mapper->loadAllMenuLinks();
        foreach ($menu_links as $menu_link)
        {
            $selected = false;
            foreach ($this->widget->links as $link)
            {
                if ($link->url == $menu_link->url) {
                    $menu_link->published = true;
                    break;
                }
            }
        }
        $view = nBillWidgetFactory::makeWidgetView($this->widget);
        $view->menu_links = $menu_links;
        $view->renderConfig();
        exit;
    }
}