<?php
/**
* Links widget
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class LinksWidget extends nBillWidget
{
    const SMALL_ICONS = 0;
    const LARGE_ICONS = 1;
    const NO_ICONS = 2;

    public $icon_type = LinksWidget::LARGE_ICONS;
    public $links = array();

    public function __construct($type, $published, $ordering)
    {
        parent::__construct($type, $published, $ordering);
        $this->title = 'NBILL_WIDGETS_LINKS_DEFAULT_TITLE';
    }
}