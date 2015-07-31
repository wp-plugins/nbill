<?php
/**
* Orders Due widget
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class OrdersDueWidget extends nBillWidget
{
    const RANGE_UNITS_DAYS = 'days';
    const RANGE_UNITS_WEEKS = 'weeks';
    const RANGE_UNITS_MONTHS = 'months';

    /** @var int **/
    public $number_of_units = 7;
    /** @var int **/
    public $range_units = self::RANGE_UNITS_DAYS;
    /** @var int **/
    public $max_records = 10;
    /** @var array **/
    public $records = array();
    /** @var string CSS rule for height of graph **/
    public $height = '225px';

    public function __construct($type, $published, $ordering)
    {
        parent::__construct($type, $published, $ordering);
        $this->title = 'NBILL_WIDGETS_ORDERS_DUE_DEFAULT_TITLE';
        $this->width = '49%'; //Default to half screen width
    }

    public function getParams()
    {
        //Don't persist the actual records
        $params = parent::getParams();
        if(($key = array_search('records', $params)) !== false) {
            unset($params[$key]);
        }
        return $params;
    }
}