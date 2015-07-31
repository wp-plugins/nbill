<?php
/**
* View class for widget rendering
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillWidgetView
{
    /** @var nBillWidget **/
    public $widget;

    public function __construct(nBillWidget $widget)
    {
        $this->widget = $widget;
        nbf_html::include_overlib_js();
    }

    /**
    * Renders entire widget, with title bar, controls etc. (renderContent function, below, is also called by the template)
    */
    public function render($ajax_refresh = false)
    {
        $template = dirname(__FILE__) . "/custom/index.php";
        if (!file_exists($template)) {
            $template = dirname(__FILE__) . "/default/index.php";
        }
        include($template);
    }

    /**
    * Renders content of widget
    */
    public function renderContent($ajax_refresh = false)
    {
        $template = realpath(dirname(__FILE__) . "/..") . "/types/" . $this->widget->type . "/view/custom/index.php";
        if (!file_exists($template)) {
            $template = realpath(dirname(__FILE__) . "/..") . "/types/" . $this->widget->type . "/view/default/index.php";
        }
        if (file_exists($template)) {
            if (file_exists(nbf_cms::$interop->nbill_fe_base_path . '/style/admin/widgets/types/' . $this->widget->type . '/default/template.css')) {
                nbf_cms::$interop->add_html_header('<link rel="stylesheet" type="text/css" href="' . nbf_cms::$interop->nbill_site_url_path . '/style/admin/widgets/types/' . $this->widget->type . '/default/template.css" />');
            }
            if (file_exists(nbf_cms::$interop->nbill_fe_base_path . 'style/admin/widgets/types/' . $this->widget->type . '/custom/template.css')) {
                nbf_cms::$interop->add_html_header('<link rel="stylesheet" type="text/css" href="' . nbf_cms::$interop->nbill_site_url_path . '/style/admin/widgets/types/' . $this->widget->type . '/custom/template.css" />');
            }
            include($template);
        } else {
            echo 'Widget template file not found: ' . $template;
        }
    }

    /**
    * Render entire config page (renderConfigContent function, below, is also called by the template)
    */
    public function renderConfig()
    {
        $template = dirname(__FILE__) . "/custom/config.php";
        if (!file_exists($template)) {
            $template = dirname(__FILE__) . "/default/config.php";
        }
        include($template);
    }

    public function renderConfigContent()
    {
        $config_file = realpath(dirname(__FILE__) . "/..") . "/types/" . $this->widget->type . "/view/custom/config.php";
        if (!file_exists($config_file)) {
            $config_file = realpath(dirname(__FILE__) . "/..") . "/types/" . $this->widget->type . "/view/default/config.php";
        }
        if (file_exists($config_file)) {
            include($config_file);
        } else {
            echo sprintf(NBILL_WIDGETS_CONFIG_NOT_FOUND, "/widgets/types/" . $this->widget->type . "/view/default/config.php");
        }
    }
}