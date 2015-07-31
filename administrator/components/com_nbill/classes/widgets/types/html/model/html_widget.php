<?php
/**
* Html widget
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class HtmlWidget extends nBillWidget
{
    public $message = '';

    public function __construct($type, $published, $ordering)
    {
        parent::__construct($type, $published, $ordering);
        $this->title = 'NBILL_WELCOME';
        $this->message = sprintf(NBILL_WIDGETS_HTML_DEFAULT_WELCOME, '<a target="_blank" href="http://' . ltrim(NBILL_BRANDING_SUPPORT_URL, 'http://') . '">' . NBILL_BRANDING_WEBSITE . '</a>');
    }
}