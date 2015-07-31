<?php
/**
* Controller for the sales graph widget
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class SalesGraphController extends nBillWidgetController
{
    /** @var SalesGraphService **/
    protected $service;

    public function __construct(SalesGraphWidget $widget, SalesGraphMapper $mapper)
    {
        $mapper->currency = $widget->currency;
        parent::__construct($widget, $mapper);
        $this->service = new SalesGraphService($mapper);
        $this->service->date_format = nbf_common::get_date_format();
    }

    public function route()
    {
        switch (@$_REQUEST['task'])
        {
            case 'new_date_range':
                $this->clearAllBuffers();
                $this->widget->date_range = intval(@$_POST['new_date_range']);
                if (defined('SalesGraphWidget::GRAPH_TYPE_' . strtoupper(@$_POST['graph_type']))) {
                    $this->widget->graph_type = constant('SalesGraphWidget::GRAPH_TYPE_' . strtoupper(@$_POST['graph_type']));
                }
                $this->showWidget(true);
                break;
            default:
                parent::route();
                break;
        }
    }

    public function showWidget($ajax_refresh = false)
    {
        $this->widget->prepareGraph($this->service);
        parent::showWidget($ajax_refresh);
    }
}