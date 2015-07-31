<?php
/**
* View class for widgets (dashboard) configuration rendering
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillWidgetsView
{
    /** @var array List of widget types **/
    protected $widget_types = array();
    /** @var array List of ALL widget objects (published or not) for configuration **/
    protected $widgets = array();
    /** @var array List of CSS template files for different colour schemes **/
    public $templates = array();
    /** @var string Selected CSS template **/
    public $selected_template;
    /** @var string Path to colour scheme templates **/
    public $template_path;

    public function __construct($widget_types, $widgets)
    {
        $this->widget_types = $widget_types;
        $this->widgets = $widgets;
    }

    public function renderConfig()
    {
        $template = dirname(__FILE__) . "/custom/dashboard_config.php";
        if (!file_exists($template)) {
            $template = dirname(__FILE__) . "/default/dashboard_config.php";
        }
        include($template);
    }
}