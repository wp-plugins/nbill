<?php
/**
* Data Mapper for Administrator home page widgets
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillWidgetFactory
{
    /**
    * Create a widget of the specified type
    * @param mixed $type
    * @param mixed $published
    * @param mixed $ordering
    * @param mixed $args,... Unlimited optional additional arguments to pass to the constructor
    * @return nBillWidget
    */
    public static function makeWidget($type = 'html', $published = true, $ordering = 0)
    {
        $widget = null;
        if (!$type) {
            $type = 'html';
        }
        if ($published === null) {
            $published = true;
        }
        $widget_file = dirname(__FILE__) . '/../types/' . $type . '/model/' . $type . '_widget.php';
        $class_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $type))) . 'Widget';
        if (file_exists($widget_file)) {
            include_once($widget_file);
            if (class_exists($class_name)) {
                $widget = call_user_func_array(array('self', 'createObject'), self::prepend(func_get_args(), $class_name));
            }
        }
        if (!$widget) {
            $widget = new nBillWidget($type, $published, $ordering);
        }
        return $widget;
    }

    /**
    * @param nBillWidget $widget
    * @param nbf_database $db
    * @param mixed $args,... Unlimited optional additional arguments to pass to the constructor
    * @return nBillWidgetMapper
    */
    public static function makeWidgetMapper(nBillWidget $widget, nbf_database $db)
    {
        $widget_mapper_file = dirname(__FILE__) . '/../types/' . $widget->type . '/model/' . $widget->type . '_mapper.php';
        if (file_exists($widget_mapper_file)) {
            $class_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $widget->type))) . 'Mapper';
            include_once($widget_mapper_file);
            if (class_exists($class_name)) {
                $args = func_get_args();
                array_shift($args); //Don't send widget to mapper constructor
                $mapper = call_user_func_array(array('self', 'createObject'), self::prepend($args, $class_name));
                return $mapper;
            }
        }
        return new nBillWidgetMapper($db);
    }

    /**
    * @param nBillWidget $widget
    * @param nBillWidgetMapper $mapper
    * @param mixed $args,... Unlimited optional additional arguments to pass to the constructor
    * @return nBillWidgetController
    */
    public static function makeWidgetController(nBillWidget $widget, nBillWidgetMapper $mapper)
    {
        $widget_controller_file = dirname(__FILE__) . '/../types/' . $widget->type . '/' . $widget->type . '_controller.php';
        if (file_exists($widget_controller_file)) {
            $class_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $widget->type))) . 'Controller';
            include_once($widget_controller_file);
            if (class_exists($class_name)) {
                $controller = call_user_func_array(array('self', 'createObject'), self::prepend(func_get_args(), $class_name));
                return $controller;
            }
        }
        return new nBillWidgetController($widget, $mapper);
    }

    /**
    * @param nBillWidget $widget
    * @param mixed $args,... Unlimited optional additional arguments to pass to the constructor
    * @return nBillWidgetView
    */
    public static function makeWidgetView(nBillWidget $widget)
    {
        $widget_view_file = dirname(__FILE__) . '/../types/' . $widget->type . '/view/' . $widget->type . '_view.php';
        if (!file_exists($widget_view_file)) {
            return new nBillWidgetView($widget);
        }
        $class_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $widget->type))) . 'View';
        include_once($widget_view_file);
        if (class_exists($class_name)) {
            $view = call_user_func_array(array('self', 'createObject'), self::prepend(func_get_args(), $class_name));
            return $view;
        } else {
            return new nBillWidgetView($widget);
        }
    }

    //By returning the array, we can use a single function call instead of using array_unshift which does not return the array
    protected static function prepend($array, $new_first_element)
    {
        array_unshift($array, $new_first_element);
        return $array;
    }

    protected static function createObject($class_name)
    {
        $reflection = new ReflectionClass($class_name);
        $args = func_get_args();
        array_shift($args); //Don't pass the class name to the constructor!
        $instance = $reflection->newInstanceArgs($args);
        return $instance;
    }
}