<?php
class com_nbillInstallerScript
{
    function postflight($action, $installer)
    {
        switch ($action)
        {
            case "install":
            case "update":
                //Stuff to do after installation is complete
                include_once($installer->getParent()->getPath("extension_administrator") . "/install.nbill.php");
                echo com_install();
                break;
        }
    }

    function uninstall($installer)
    {
        //Stuff to do when uninstalling
        include_once($installer->getParent()->getPath("extension_administrator") . "/uninstall.nbill.php");
        echo com_uninstall();
    }
}