<?php
if (file_exists(realpath(dirname(__FILE__)) . "/admin.control.nbill.php")) {

    //Initialise framework, language, branding, stylesheet, etc.
    require_once(realpath(dirname(__FILE__)) . "/admin.init.php");
    if (!$pre_req_ok)
    {
        return;
    }
    include(realpath(dirname(__FILE__)) . "/admin.control.nbill.php");
} else {
    die("Sorry, the component entry point file could not be found!");
}