<?php
/**
* Entity class for Administrator home page widgets
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillWidget
{
    /** @var int Primary key **/
    public $id;
    /** @var string Type of widget (there should be a folder with this name in the widgets folder, which should contain an index.php file) **/
    public $type = 'html';
    /** @var boolean Whether or not to show it **/
    public $published = true;
    /** @var int Order of display **/
    public $ordering;
    /** @var boolean Whether or not to show the title **/
    public $show_title = true;
    /** @var string **/
    public $title;
    /** @var string Width */
    public $width = 'auto';
    /** @var boolean Whether or not this widget offers any configuration options (class controller must offer a configure method) **/
    public $configurable = true;
    /** @var boolean Whether or not changes to this widget have soiled the layout of the whole page (requiring a refresh) **/
    public $layout_dirty = false;

    public function __construct($type, $published, $ordering)
    {
        $this->type = $type;
        $this->published = $published;
        $this->ordering = $ordering;
    }

    /**
    * Returns a list of the custom properties of the widget that should be persisted.
    * By default this will return all properties that do not belong to the base object.
    * If any properties of a sub class are transient and should not be persisted, this function should be overridden to exclude them.
    */
    public function getParams()
    {
        $params = array();
        $base_properties = get_class_vars('nBillWidget');
        $derived_properties = array_keys(get_object_vars($this));
        foreach ($derived_properties as $property)
        {
            if (array_key_exists($property, $base_properties) === false) {
                $params[] = $property;
            }
        }
        return $params;
    }
}