<?php
/**
* Represents a set of options that can be JSON encoded and used by the Google Chart API
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class SalesGraphChartOptions
{
    public $title = '';
    public $chartArea;
    public $legend = 'bottom';
    public $is3D = true;
    public $vAxis;

    public function __construct()
    {
        $this->chartArea = new stdClass();
        $this->chartArea->width = '80%';
        $this->chartArea->height = '80%';
        $this->vAxis = new stdClass();
        $this->vAxis->minValue = 0;
    }
}