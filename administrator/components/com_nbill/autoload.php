<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Autoload classes
function nbill_autoload($class_name)
{
    if (isset(nBillClassMap::$class_map[$class_name])) {
        include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . nBillClassMap::$class_map[$class_name];
        return true;
    } else {
        if (substr($class_name, 0, 5) == 'nBill') {
            //Protect against poison null byte
            $class_name = str_replace(chr(0), '', $class_name);
            //Belt and braces
            if (strpos($class_name, "..") !== false) {
                throw new Exception('Directory Traversal Attack Detected');
            }
            //Check admin
            $folder = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR;
            if (check_exists($folder, $class_name)) {
                return true;
            }
            $folder = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'admin.gateway' . DIRECTORY_SEPARATOR . 'admin.paypal' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR;
            if (check_exists($folder, $class_name)) {
                return true;
            }
            //Document templates
            if (class_exists('nbf_cms')) {
                $folder = nbf_cms::$interop->nbill_fe_base_path . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
                if (check_exists($folder, $class_name)) {
                    return true;
                }
            }
        }
        if (function_exists('__autoload')) { //Joomla 1.5
            return __autoload($class_name);
        }
        return false;
    }
}

function check_exists($folder, $class_name) {
    $files = array_diff(scandir($folder), array('.', '..'));
    foreach ($files as $file) {
        if (is_dir($folder . $file)) {
            if (check_exists($folder . $file . DIRECTORY_SEPARATOR, $class_name)) {
                return true;
            }
        }
        if (is_file($folder . $file)) {
            $compare_class = substr(strtolower($class_name), 0, 5) == 'nbill' ? substr($class_name, 5) : $class_name;
            if (strtolower(str_replace('_', '', $file)) == strtolower($compare_class) . ".php") {
                include_once($folder . DIRECTORY_SEPARATOR . $file);
                return true;
            }
        }
    }
}

spl_autoload_register('nbill_autoload');