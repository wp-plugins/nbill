<?php
/**
* Main processing file for individual nBill administrator home page widget
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillWidgetController
{
    /** @var nBillWidget **/
    protected $widget;
    /** @var nBillWidgetMapper **/
    protected $mapper;

    public function __construct(nBillWidget $widget, nBillWidgetMapper $mapper)
    {
        nbf_common::load_language("widgets");
        $this->widget = $widget;
        $this->mapper = $mapper;
    }

    public function route()
    {
        switch (@$_REQUEST['task'])
        {
            case "show_config":
                $this->showConfig();
                break;
            case "save_config":
                $this->saveConfig();
                break;
        }
    }

    public function showConfig($then_exit = true)
    {
        $this->clearAllBuffers();
        $view = nBillWidgetFactory::makeWidgetView($this->widget);
        $view->renderConfig();
        if ($then_exit) {
            exit;
        }
    }

    public function saveConfig($then_exit = true)
    {
        $this->clearAllBuffers();

        //Re-populate widget with posted data
        $this->widget = $this->mapper->loadWidget($this->widget->id, true);
        $this->mapper->saveWidget($this->widget);

        //Send a refreshed view of the widget as the AJAX response to update the display
        $this->showWidget(true);
        if ($then_exit) {
            exit;
        }
    }

    public function showWidget($ajax_refresh = false)
    {
        $view = nBillWidgetFactory::makeWidgetView($this->widget);
        if ($view) {
            $view->render($ajax_refresh);
        }
    }

    protected function clearAllBuffers()
    {
        $level = ob_get_level();
        for ($i=0;$i<=$level;$i++)
        {
            @ob_end_clean();
        }
        if (!@headers_sent()) {
          foreach (@headers_list() as $header)
            @header_remove($header);
        }
    }

    protected function enforceInteger($parameter, $default_value = 0)
    {
        $param = intval(@$_REQUEST[$parameter]);
        if (!$param && strval($param) != @$_REQUEST[$parameter]) {
            $param = $default_value; //Non numeric value entered, which is not allowed
        }
        $_REQUEST[$parameter] = $param;
    }
}