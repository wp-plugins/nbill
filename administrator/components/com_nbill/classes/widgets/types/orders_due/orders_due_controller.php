<?php
/**
* Controller for the orders due widget
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class OrdersDueController extends nBillWidgetController
{
    /** @var OrdersDueService **/
    protected $service;

    public function showWidget($ajax_refresh = false)
    {
        $this->widget->records = $this->mapper->loadOrdersDue($this->widget);
        parent::showWidget($ajax_refresh);
    }

    public function saveConfig($then_exit = true)
    {
        $this->enforceInteger('number_of_units', 1);
        $this->enforceInteger('max_records', 1);
        parent::saveConfig($then_exit);
    }
}