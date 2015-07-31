<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_3_0_to_2_3_1()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    //New default Itemid parameter
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `default_itemid` INT UNSIGNED NOT NULL DEFAULT '0'";
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `redirect_to_itemid` TINYINT NOT NULL DEFAULT '0'";

    //Correct erroneous auto-increment flag on client credit table
    $sql[] = "ALTER TABLE `#__nbill_client_credit` CHANGE `entity_id` `entity_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0'";

    //Quote accept redirect column may be missing if upgrading from 2.3.0 Lite (ignore any error when trying to add it back in)
    $query = "ALTER TABLE `#__nbill_order_form` ADD `quote_accept_redirect` VARCHAR(255) NOT NULL DEFAULT ''";
    $nb_database->setQuery($query);
    $nb_database->query();

    foreach ($sql as $query)
    {
        $nb_database->setQuery($query);
        $nb_database->query();
        if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
        {
            nbf_globals::$db_errors[] = $nb_database->_errorMsg;
        }
    }

    //New config file option for debug tracing
    $text_to_replace = "}";
    $replace_with = <<<CONFIG_TO
    /** @var Whether to trace all frontend activity in a log file (WARNING! only for use when debugging - can produce a lot of output!) */
    public static \$trace_debug_frontend = false;
    /** @var Whether to trace all admin activity in a log file (WARNING! only for use when debugging - can produce a lot of output!) */
    public static \$trace_debug_admin = false;
}
CONFIG_TO;
    $content = @file_get_contents(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.config.php");
    if (nbf_common::nb_strpos($content, $replace_with) === false)
    {
        $content = str_replace($text_to_replace, $replace_with, $content);
        @file_put_contents(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.config.php", $content);
    }
    $text_to_replace = array();
    $replace_with = array();

##### LANGUAGE UPDATE START #####
    //Configuration
    $text_to_add['en-GB'] = <<<LANG_ADD
//Version 2.3.1
define("NBILL_CFG_DEFAULT_MENU_ITEM", "Default Menu Itemid");
define("NBILL_CFG_INSTR_DEFAULT_MENU_ITEM", "Itemid of the menu item to use as the default for links output by nBill when no particular menu item was used to access the page. This can be used to control which modules get displayed by Joomla! - the Itemid value for each menu item can be found on the far right of the list of menu items.");
define("NBILL_CFG_REDIRECT_TO_ITEMID", "Redirect to Itemid?");
define("NBILL_CFG_INSTR_REDIRECT_TO_ITEMID", "If a front-end page is accessed without an Itemid parameter, this setting allows you to specify that the visitor should be redirected to the equivalent page with the default Itemid. Only takes effect if a default Itemid is specified, above. This setting is experimental and should be used with caution.");
LANG_ADD;
    edit_language_item("configuration", $text_to_add);
    $text_to_add = array();

    //Main
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.3.1
define("NBILL_ORDER_DATA_CORRUPTION_WARNING", "Warning! Possible data integrity corruption"); //On rare occasions, some have reported data loss on form submissions. These language elements allow for detection and reporting of such problems.
define("NBILL_ORDER_SAVE_DATA_CORRUPTION", "There may have been some data corruption whilst attempting to save a pending order. The order details are as follows:");
define("NBILL_ORDER_LOAD_DATA_CORRUPTION", "There may have been some data corruption whilst attempting to load a pending order. The order details are as follows:");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTALL_ERROR", "Sorry, it looks like nBill failed to install correctly! If you are using Joomla 1.5 or above and have moved the Joomla configuration.php file to a different location, you must enter the location of that file in the nBill configuration
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_INSTALL_ERROR", "Sorry, it looks like nBill failed to install correctly! Try setting the database connection type to MySQL instead of MySQLi (you can do this by editing the file %s and changing line 71 from 'public static \\\$mysql = false'; to 'public static \\\$mysql = true;'). If you are using Joomla 1.5 or above and have moved the Joomla configuration.php file to a different location, you must enter the location of that file in the " . NBILL_BRANDING_NAME . " configuration
LANG_TO;
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Suppliers
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_COMPANY_NAME",
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_INSTR_SUPPLIER_COMPANY_NAME",
LANG_TO;
    edit_language_item("suppliers", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_ADDRESS",
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_INSTR_SUPPLIER_ADDRESS",
LANG_TO;
    edit_language_item("suppliers", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

        $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_COUNTRY",
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_INSTR_SUPPLIER_COUNTRY",
LANG_TO;
    edit_language_item("suppliers", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

        $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_REFERENCE",
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_INSTR_SUPPLIER_REFERENCE",
LANG_TO;
    edit_language_item("suppliers", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

        $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_EMAIL_ADDRESS",
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_INSTR_EMAIL_ADDRESS",
LANG_TO;
    edit_language_item("suppliers", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

        $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_WEBSITE",
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_INSTR_SUPPLIER_WEBSITE",
LANG_TO;
    edit_language_item("suppliers", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

        $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_TELEPHONE",
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_INSTR_TELEPHONE",
LANG_TO;
    edit_language_item("suppliers", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

        $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_TELEPHONE_2",
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_INSTR_TELEPHONE_2",
LANG_TO;
    edit_language_item("suppliers", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_MOBILE",
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_INSTR_MOBILE",
LANG_TO;
    edit_language_item("suppliers", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

        $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_FAX",
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_INSTR_FAX",
LANG_TO;
    edit_language_item("suppliers", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();
##### LANGUAGE UPDATE END #####
}