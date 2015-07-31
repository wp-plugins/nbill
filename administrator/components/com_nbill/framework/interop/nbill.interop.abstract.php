<?php
/**
* Abstract Interop Class File for interoperation between nBill and different CMSs
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

if (!function_exists('sys_get_temp_dir'))
{
    function sys_get_temp_dir()
    {
        if($temp=getenv('TMP')) return $temp;
        if($temp=getenv('TEMP')) return $temp;
        if($temp=getenv('TMPDIR')) return $temp;
        $temp=tempnam(__FILE__,'');
        if (file_exists($temp))
        {
            unlink($temp);
            return dirname($temp);
        }
        return null;
    }
}

/**
* This class provides the basic framework for interop classes to inherit from.
* Some basic functionality that is shared by all interops is defined here (mainly
* the constructor which calls the functions required to populate the members), but
* most things must be overridden.
*
* @package nBill Framework Interop
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
abstract class nbf_interop
{
    /** @var boolean Whether or not the CMS is running a demo site (if true, some features of nBill will be disabled for security reasons) */
    private $demo_mode = false;
    /** @var string Name of component that is currently using the framework */
    public $component_name = "";
    /** @var int If an entity is detected for the logged in user, but no language has been set, this variable allows us to set the default to the current language */
    private $update_lang_for_entity_id = 0;
    /** @var string Name of CMS (for display in error reports) */
    public $cms_name = "";
    /** @var string Version number of CMS (for display in error reports) */
    public $cms_version = "";
    /** @var string Relative URL to home page of CMS (in case of abort from license key check */
    public $cms_home_page = "";
    /** @var string File path to administrator component files */
    public $nbill_admin_base_path = "";
    /** @var string File path to front end component files */
    public $nbill_fe_base_path = "";
    /** @var string File path to root of website */
    public $site_base_path = "";
    /** @var string File path to a folder where we can write temporary files (eg. during upgrade or when creating email attachments) */
    public $site_temp_path = "";
    /** @var string Full URL to administrator component files (for including admin stylesheet, eg. http://www.mydomain.com/administrator/components/com_nbill) */
    public $nbill_admin_url_path = "";
    /** @var string Full URL to front end component files (for referencing images and front-end pages, eg. http://www.mydomain.com/components/com_nbill) */
    public $nbill_site_url_path = "";
    /** @var string Base URL to which relative URLs can be appended */
    public $live_site = "";
    /** @var string URL to CMS's user editor (use %s token for user ID) */
    public $user_editor_url;
    /** @var string Name of website - typically taken from CMS configuration file */
    public $site_name = "";
    /** @var object Database object */
    public $database = null;
    /** @var string Language to use (eg. en-GB, en-US, fr-FR, etc.) */
    public $language = "";
    /** @var string First part of URL to use to access the component admin functions (eg. index2.php?option=com_nbill) */
    public $admin_page_prefix = "";
    /** @var string First part of URL to use to access the component admin functions in a popup window (to suppress CMS menu options etc) */
    public $admin_popup_page_prefix = "";
    /** @var string Page to redirect to when nBill tables have been deleted to uninstall the component */
    public $admin_component_uninstaller = "";
    /** @var string First part of URL to use to access the component front end functions (eg. index.php?option=com_nbill) */
    public $site_page_prefix = "";
    /** @var string First part of URL to use to access the component front end functions in a popup window (to suppress CMS menu options etc) */
    public $site_popup_page_prefix = "";
    /** @var nbf_db_connection Database connection details */
    public $db_connection = null;
    /** @var int Default number of items to display per list */
    public $records_per_page = 50;
    /** @var cms_database_enum Enumerator for the required database table names and column names in the CMS */
    public $cms_database_enum;
    /** @var string Character encoding used by CMS (eg. "iso-8859-1" or "utf-8") */
    public $char_encoding = "utf-8";
    /** @var string Database charset used by CMS (eg. "latin2" or "utf8") */
    public $db_charset;
    /** @var nb_user Information about the currently logged in user */
    public $user;
    /** @var string Text to use in the action parameter on front end forms (eg. index.php or index.php?option=com_nbill) */
    public $fe_form_action;
    /** @var boolean Whether or not a user can belong to more than one user group */
    public $multi_user_group = false;
    /** @var boolean Whether or not to hide the FTP details on the global config screen (pick up details from CMS instead) */
    public $hide_ftp_details = false;
    /** @var int Value of access column in CMS menu table for public menu items */
    public $public_menu_access = 0;

    /**
    * Accessor for properties that may require some processing when accessed
    * @return mixed
    */
    public function __get($property)
    {
        switch ($property)
        {
            case "demo_mode":
                return $this->demo_mode;
                break;
            case "site_page_suffix": //Prioritise request
            case "site_page_suffix_default": //Prioritise default
                $request_itemid = 0;
                if (isset($_REQUEST['Itemid']) && $_REQUEST['Itemid']) {
                    $request_itemid = intval($_REQUEST['Itemid']);
                } else if (isset($GLOBALS['Itemid']) && $GLOBALS['Itemid']) {
                    $request_itemid = intval($GLOBALS['Itemid']);
                }
                //See if there is a default value defined in the global config
                $nb_database = $this->database;
                $sql = "SELECT default_itemid FROM #__nbill_configuration WHERE id = 1";
                $nb_database->setQuery($sql);
                $default_itemid = intval($nb_database->loadResult());

                if ($property == 'site_page_suffix') {
                    return $request_itemid ? "&Itemid=" . $request_itemid : ($default_itemid ? "&Itemid=" . $default_itemid : "");
                } else {
                    return $default_itemid ? "&Itemid=" . $default_itemid : ($request_itemid ? "&Itemid=" . $request_itemid : "");
                }
            default:
                break;
        }

        if (substr($property, 0, 21) == 'nframework_component_')
        {
            $property_name = 'nbill_' . substr($property, 21);
            return $this->$property_name;
        }
        else if (substr($property, 0, 11) == 'nframework_')
        {
            $property_name = 'nbill_' . substr($property, 11);
            return $this->$property_name;
        }
    }

    /**
    * Any cleanup tasks to perform before handing control back to the CMS
    */
    public function terminate()
    {
    }

    /*
    * If the Itemid is not publicly accessible, switch to one that is (or omit altogether if none found)
    */
    public function public_site_page_suffix()
    {
        $nb_database = $this->database;
        $this_item_id = 0;
        if (isset($_REQUEST['Itemid']) && $_REQUEST['Itemid'])
        {
            $this_item_id =  intval($_REQUEST['Itemid']);
        }
        else if (isset($GLOBALS['Itemid']) && $GLOBALS['Itemid'])
        {
            $this_item_id = intval($GLOBALS['Itemid']);
        }
        if ($this_item_id)
        {
            $sql = "SELECT id FROM #__menu WHERE id = $this_item_id AND access = " . $this->public_menu_access;
            $nb_database->setQuery($sql);
            if (!$nb_database->loadResult())
            {
                $this_item_id = 0;
            }
        }
        if (!$this_item_id)
        {
            //See if there is a default value defined in the global config
            $sql = "SELECT default_itemid FROM #__nbill_configuration
                    INNER JOIN #__menu ON default_itemid = #__menu.id
                    WHERE #__nbill_configuration.id = 1 AND #__menu.access = " . $this->public_menu_access;
            $nb_database->setQuery($sql);
            $this_item_id = intval($nb_database->loadResult());
        }
        if (!$this_item_id)
        {
            //Use home page menu item ID or bust
            $sql = "SELECT id FROM #__menu WHERE home = 1 AND access = " . $this->public_menu_access . " AND published = 1";
            $nb_database->setQuery($sql);
            $this_item_id = intval($nb_database->loadResult());
        }
        return $this_item_id ? "&Itemid=$this_item_id" : "";
    }

    /**
    * Calls the necessary internal functions to populate the public members. If no language is found, default to en-GB, which will be supplied with the component by default.
    * */
    function __construct($component_name = "nbill")
    {
        $this->component_name = $component_name;
        $this->get_base_paths(); //Must do this first so we know where to find the config file
        $this->initialise();
        $this->database = $this->get_database();
        $this->set_default_locale();
        $this->live_site = $this->get_live_site();
        $this->remove_trailing_slash("live_site");
        if ($this->live_site == 'http://www.nbill.co.uk/demo' || $this->live_site == 'https://www.nbill.co.uk/demo') {
            $this->demo_mode = true;
        }
        $this->set_cms_database_enum();
        //We need to load the branding in case it affects the URL prefixes
        include_once($this->nbill_admin_base_path . "/branding.php");
        $this->set_url_prefixes();
        $this->get_user();
        $this->language = $this->get_language();
        if (!$this->language)
        {
            $this->language = "en-GB";
        }
        //If we have a client without a default language, set it to the language currently in use
        if ($this->language && $this->update_lang_for_entity_id)
        {
            $sql = "UPDATE #__nbill_entity SET default_language = '" . $this->language . "' WHERE id = " . intval($this->update_lang_for_entity_id);
            $this->database->setQuery($sql);
            $this->database->query();
        }
        $this->site_name = $this->get_site_name();
        $sql = "SELECT `value` FROM `#__nbill_display_options` WHERE `name` = 'submit_option'";
        $this->database->setQuery($sql);
        $submit_option = strval($this->database->loadResult());
        if ($submit_option !== "0")
        {
            $submit_option = 1;
        }
        $this->set_fe_form_action($submit_option);
        $this->construct_end();
    }

    /**
    * Anything that needs doing after the other functions in the constructor have been called
    */
    protected function construct_end(){}

    /**
    * If custom branding is in place and a custom component name is required, the CMS might need to be informed (eg. to add an entry in the extensions table)
    */
    public function register_custom_component_name($product_name, $custom_component_name, $company_name, $product_website)
    {
        if (!@file_exists($this->site_base_path . "/components/" . nbill_custom_branding::$component_name . "/" . substr(nbill_custom_branding::$component_name, 4) . ".php")) {
            //Attempt to create custom directory/file for transparent usage in front end
            try {
                nbf_file::nb_mkdir_recursive($this->site_base_path . "/components/" . nbill_custom_branding::$component_name);
                file_put_contents($this->site_base_path . "/components/" . nbill_custom_branding::$component_name . "/" . substr(nbill_custom_branding::$component_name, 4) . ".php", "<?php \n//Ensure this file has been reached through a valid entry point\n(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');\n\nrequire(dirname((realpath(__FILE__))) . '/../com_nbill/nbill.php');");
            } catch (Exception $e) {}
        }
        if (!@file_exists($this->site_base_path . "/administrator/components/" . nbill_custom_branding::$component_name . "/" . substr(nbill_custom_branding::$component_name, 4) . ".php")) {
            //Attempt to create custom directory/file for transparent usage in back end
            nbf_file::nb_mkdir_recursive($this->site_base_path . "/administrator/components/" . nbill_custom_branding::$component_name);
            file_put_contents($this->site_base_path . "/administrator/components/" . nbill_custom_branding::$component_name . "/" . substr(nbill_custom_branding::$component_name, 4) . ".php", "<?php \n//Ensure this file has been reached through a valid entry point\n(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');\n\nrequire(dirname((realpath(__FILE__))) . '/../com_nbill/nbill.php');");
            file_put_contents($this->site_base_path . "/administrator/components/" . nbill_custom_branding::$component_name . "/admin." . substr(nbill_custom_branding::$component_name, 4) . ".php", "<?php \n//Ensure this file has been reached through a valid entry point\n(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');\n\nrequire(dirname((realpath(__FILE__))) . '/../com_nbill/nbill.php');");
        }
        //If custom component name not available, either revert to com_nbill, or die
        if (@file_exists($this->site_base_path . "/components/" . nbill_custom_branding::$component_name . "/" . substr(nbill_custom_branding::$component_name, 4) . ".php")
            && @file_exists($this->site_base_path . "/administrator/components/" . nbill_custom_branding::$component_name . "/" . substr(nbill_custom_branding::$component_name, 4) . ".php")) {
            //Make a note of it in the database so we can uninstall cleanly
            $sql = "SELECT custom_component_names FROM #__nbill_license WHERE id = 1";
            $this->database->setQuery($sql);
            $db_component_names = explode(",", $this->database->loadResult());
            $db_component_names[] = nbill_custom_branding::$component_name;
            $db_component_names = array_unique($db_component_names);
            $sql = "UPDATE #__nbill_license SET custom_component_names = '" . implode(",", $db_component_names) . "' WHERE id = 1";
            $this->database->setQuery($sql);
            $this->database->query();
            //Use custom component name
            $this->component_name = str_replace('com_', '', $custom_component_name);
            define("NBILL_BRANDING_COMPONENT_NAME", nbill_custom_branding::$component_name); //Joomla component name
        }
    }

    /**
    * If there was a branding file, but isn't any longer, do any necessary cleanup
    */
    public function unregister_custom_component_name($db_component_names)
    {
        $db_component_names = explode(",", $db_component_names);
        foreach ($db_component_names as $db_component_name)
        {
            if (strlen(trim($db_component_name)) > 0 && $db_component_name != "com_nbill")
            {
                if (@file_exists($this->site_base_path . "/components/$db_component_name"))
                {
                    //Make sure it really is a directory added by nBill branding!
                    $file_contents = @file_get_contents($this->site_base_path . "/components/$db_component_name/" . substr($db_component_name, 4) . ".php");
                    if (strpos($file_contents, 'com_nbill') !== false)
                    {
                        @nbf_file::remove_directory($this->site_base_path . "/components/$db_component_name");
                    }
                }
                if (@file_exists($this->site_base_path . "/administrator/components/$db_component_name"))
                {
                    //Make sure it really is a directory added by nBill branding!
                    $file_contents = @file_get_contents($this->site_base_path . "/administrator/components/$db_component_name/" . substr($db_component_name, 4) . ".php");
                    if (strpos($file_contents, 'com_nbill') !== false)
                    {
                        @nbf_file::remove_directory($this->site_base_path . "/administrator/components/$db_component_name");
                    }
                }
            }
        }
        $this->component_name = "nbill";
        //We can forget about it now (even if it didn't remove properly, there is nothing more we can do about it)
        $this->database->setQuery("UPDATE #__nbill_license SET custom_component_names = '' WHERE id = 1");
        $this->database->query();
    }

    /**
    * Try to set the locale for LC_CTYPE ONLY (so string functions are handled correctly)
    */
    public function set_default_locale()
    {
        @setlocale(LC_ALL, array("en_US.UTF-8", "en", "en_US", "en-US", "English_United States", "English_United States.1252"));
        $nb_database = $this->database;
        $sql = "SELECT `locale` FROM #__nbill_configuration WHERE id = 1";
        $nb_database->setQuery($sql);
        $locale_setting = $nb_database->loadResult();
        if (nbf_common::nb_strlen($locale_setting) > 0)
        {
            @setlocale(LC_CTYPE, array_map('trim', explode(",", $locale_setting)));
        }
    }

    /**
    * Try to set the locale for LC_ALL (so numbers are formatted correctly). ONLY USE THIS TEMPORARILY while formatting
    * output, otherwise it can cause database errors when unexpected characters appear in the data (eg. decimal separator)
    */
    public function temp_set_locale()
    {
        $nb_database = $this->database;
        $sql = "SELECT `locale` FROM #__nbill_configuration WHERE id = 1";
        $nb_database->setQuery($sql);
        $locale_setting = $nb_database->loadResult();
        if (nbf_common::nb_strlen($locale_setting) > 0)
        {
            @setlocale(LC_ALL, array_map('trim', explode(",", $locale_setting)));
        }
        return $locale_setting;
    }

    /**
    * Run the user subscription check
    */
    abstract function check_account_expiry();

    /**
    * Set any configuration options or perform any other startup tasks
    */
    protected function initialise()
    {
        $this->records_per_page = 50; //Default - if CMS provides a configuration option, override with that instead
        $this->char_encoding = "utf-8"; //Override if CMS requires another encoding
        $this->db_charset = "utf8"; //Override if CMS uses another database charset
    }

    /**
    * Indicates whether or not gzip page compression is in use by the CMS
    */
    abstract public function use_gzip();

    /**
    * Tell us what language we need
    * @return string Language code in format ll-CC where ll is the 2-character ISO 639-1 language code and CC is the 2 character ISO 3166 country code.
    */
    protected function get_language()
    {
        $lang = "";
        $this->update_lang_for_entity_id = 0;

        //A value supplied in the URL overrides any other
        if (nbf_common::nb_strlen(@$_GET['nbill_lang']) > 0)
        {
            $lang = @$_GET['nbill_lang'];
            if (!@$_GET['nbill_lang_temp'] && @$_COOKIE['nbill_lang'] != $lang)
            {
                setcookie("nbill_lang", $lang, nbf_common::nb_strtotime("+15 minutes"));
            }
        }

        if (!$lang)
        {
            //Load from cookie, if available
            $lang = @$_COOKIE['nbill_lang'];
        }

        if (!$lang)
        {
            //If we have an entity ID with a saved language preference, use that
            if (intval(@$_REQUEST['nbill_entity_id']))
            {
                $sql = "SELECT default_language FROM #__nbill_entity WHERE id = " . intval(nbf_common::get_param($_REQUEST, 'nbill_entity_id'));
                $this->database->setQuery($sql);
                $lang = $this->database->loadResult();
            }
            else
            {
                //Try to guess entity ID if user is logged in
                $admin = (nbf_common::nb_strpos(nbf_common::get_requested_page(true), $this->live_site . "/administrator") !== false);
                if (!$admin && $this->user->id)
                {
                    $selected_entity = null;
                    $sql = "SELECT #__nbill_entity.id, #__nbill_entity.primary_contact_id, #__nbill_entity_contact.contact_id
                            FROM #__nbill_entity
                            INNER JOIN #__nbill_entity_contact ON #__nbill_entity.id = #__nbill_entity_contact.entity_id
                            INNER JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id
                            WHERE #__nbill_contact.user_id = " . intval($this->user->id) . "
                            ORDER BY #__nbill_entity.id";
                    $this->database->setQuery($sql);
                    $entities = $this->database->loadObjectList();
                    if (count($entities) > 0)
                    {
                        foreach ($entities as $entity)
                        {
                            if ($entity->primary_contact_id == $entity->contact_id)
                            {
                                $selected_entity = $entity;
                                break;
                            }
                        }
                        //If this user is not the pirmary contact of any, default to the first one
                        if (!$selected_entity)
                        {
                            $selected_entity = $entities[0];
                        }
                    }
                    if (@$selected_entity->id)
                    {
                        $sql = "SELECT default_language FROM #__nbill_entity WHERE id = " . intval($selected_entity->id);
                        $this->database->setQuery($sql);
                        $lang = $this->database->loadResult();
                        if (!$lang)
                        {
                            $this->update_lang_for_entity_id = $selected_entity->id;
                        }
                    }
                }
            }
        }

        if (!file_exists($this->nbill_admin_base_path . "/language/" . $lang))
        {
            $lang = "";
        }

        return $lang;
    }

    /**
    * If the website front-end can have a different default language to the back end, provide the front-end language here
    */
    public function get_frontend_language()
    {
        return $this->get_language();
    }

    public function get_list_of_languages($omit_current = false)
    {
        $languages = array_diff(scandir($this->nbill_admin_base_path . "/language/"), array('.', '..'));
        sort($languages);
        $array_size = count($languages);
        for ($lang_index = 0; $lang_index < $array_size; $lang_index++)
        {
            if (!is_dir($this->nbill_admin_base_path . "/language/" . $languages[$lang_index]) || !nbf_common::nb_strpos($languages[$lang_index], "-") == 2)
            {
                unset($languages[$lang_index]);
            }
        }
        $languages = array_filter($languages);
        $language_codes = array();
        foreach ($languages as $language)
        {
            if (!$omit_current || $language != $this->language)
            {
                $key = $language;
                if ($language == "en-GB")
                {
                    $language_codes[$key] = NBILL_LANGUAGE_ENGLISH;
                }
                else
                {
                    //Look it up - default to code if not found
                    $sql = "SELECT extension_title FROM #__nbill_extensions WHERE extension_name = '" . $language . "'";
                    $this->database->setQuery($sql);
                    $language_codes[$key] = $this->database->loadResult();
                    if (!$language_codes[$key])
                    {
                        $language_codes[$key] = $language;
                    }
                }
            }
        }
        return $language_codes;
    }

    public function get_cms_url_language_code($nbill_language_code)
    {
        //By default, just return the first part - if anything else is required, override
        $lang_parts = explode("-", $nbill_language_code);
        if (count($lang_parts) > 1) {
            return strtolower($lang_parts[0]);
        }
        $lang_parts = explode("_", $nbill_language_code);
        if (count($lang_parts) > 1) {
            return strtolower($lang_parts[0]);
        }
        return $nbill_language_code;
    }

    /**
    * Override this to tell us the name of the website (or default to the live site value) - make sure you htmlentity_decode the result
    * @return string The html entity decoded name of the website (defaults to live site value if not overridden)
    */
    protected function get_site_name()
    {
        if (nbf_common::nb_strlen($this->live_site) > 0) {
            return html_entity_decode($this->live_site, ENT_COMPAT | 0, $this->char_encoding);
        } else {
            return html_entity_decode($this->get_live_site(), ENT_COMPAT | 0, $this->char_encoding);
        }
    }

    /**
    * Records the location of the website root folder, and component files (admin and front end)
    * If the CMS inheriting this class does not follow the standard Mambo file structure, this
    * function will need to be overridden. */
    protected function get_base_paths()
    {
        $this->nbill_admin_base_path = realpath(dirname(__FILE__) . "/../..");
        $this->nbill_fe_base_path = realpath(dirname(__FILE__) . "/../../../../../components/com_" . $this->component_name);
        $this->site_base_path = realpath(dirname(__FILE__) . "/../../../../..");

        require_once($this->nbill_admin_base_path . "/framework/classes/nbill.file.class.php");

        //Try PHP temp directory
        $temp_path = @realpath(@sys_get_temp_dir());
        if (strlen($temp_path) > 0 && nbf_file::is_folder_writable($temp_path)) {
            $this->site_temp_path = $temp_path;
        } else {
            //Try media
            $temp_path = $this->site_base_path . "/media";
            if (nbf_file::is_folder_writable($temp_path)) {
                $this->site_temp_path = $temp_path;
            } else {
                //Failing that, try local component admin folder
                $temp_path = $this->nbill_admin_base_path;
                if (nbf_file::is_folder_writable($temp_path)) {
                    $this->site_temp_path = $temp_path;
                } else {
                    //Failing that, try local component front end folder
                    $temp_path = $this->nbill_fe_base_path;
                    if (nbf_file::is_folder_writable($temp_path)) {
                        $this->site_temp_path = $temp_path;
                    }
                }
            }
        }
        //Remove any trailing slashes from all paths
        $this->remove_trailing_slash("nbill_admin_base_path");
        $this->remove_trailing_slash("nbill_fe_base_path");
        $this->remove_trailing_slash("site_base_path");
        $this->remove_trailing_slash("site_temp_path");
    }

    /**
    * Set the string to use for the action paramter on front-end forms
    * @param boolean $submit_option Whether or not to include the option parameter
    */
    protected function set_fe_form_action($submit_option)
    {
        if ($submit_option)
        {
            $this->fe_form_action = $this->live_site . "/index.php?option=" . NBILL_BRANDING_COMPONENT_NAME;
        }
        else
        {
            $this->fe_form_action = $this->live_site . "/index.php";
        }
    }

    /**
    * Checks for a trailing slash on the path held in the given member, and removes it
    * @param string $member_name Name of the member containing the path.
    */
    protected function remove_trailing_slash($member_name)
    {
        $this->$member_name = rtrim($this->$member_name, '\\/');
        /*if (nbf_common::nb_substr($this->$member_name, nbf_common::nb_strlen($this->$member_name) - 1, 1) == "/" || nbf_common::nb_substr($this->$member_name, nbf_common::nb_strlen($this->$member_name) - 1, 1) == "\\")
        {
            $this->$member_name = nbf_common::nb_substr($this->$member_name, 0, nbf_common::nb_strlen($this->$member_name) - 1);
        }*/
    }

    /**
    * If a Mambo-family style database object is available, use the base class as a wrapper. Otherwise,
    * we need to handle database activity with the nBill framework database object (which inherits from
    * the base class).
    * @return nb_database Either the base class as a wrapper for the Mambo-style database object, or an inherited nBill framework database object
    */
    protected function get_database()
    {
        $return_value = null;
        require_once($this->nbill_admin_base_path . "/framework/database/nbill.database.class.php");
        $this->db_connection = $this->get_db_connection_settings();
        global $database;
        global $mainframe;
        if (class_exists("JFactory") && method_exists("JFactory", "getApplication"))
        {
            $mainframe = JFactory::getApplication();
        }
        $hold_j_client_id = $mainframe ? (isset($mainframe->_clientId) ? @$mainframe->_clientId : null) : null;
        if ($hold_j_client_id && $mainframe && property_exists($mainframe, '_clientId'))
        {
            @$mainframe->_clientId = 0; //Fool Joom!Fish into thinking it is running in the front end
        }
        $old_db_class = 'database';
        $translator_db_class_1 = 'mldatabase';
        $translator_db_class_2 = 'JFLegacyDatabase';
        if ($database && class_exists("database") && ($database instanceof $old_db_class || $database instanceof $translator_db_class_1 || $database instanceof $translator_db_class_2) && method_exists($database, "loadObjectList"))
        {
            //We can be pretty sure this is a Mambo-style database object, so use the extended class
            require_once($this->nbill_admin_base_path . "/framework/database/nbill.database.mambo.class.php");
            $return_value = new nbf_mambo_database();
        }
        else
        {
            //No Mambo-style database object available, check for translation component override
            if (class_exists($translator_db_class_1))
            {
                $db_config = array("driver"=>"mysqli", "host"=>$this->db_connection->host, "user"=>$this->db_connection->user_name, "password"=>$this->db_connection->password, "database"=>$this->db_connection->db_name, "prefix"=>$this->db_connection->prefix,"select"=>true);
                $database = new $translator_db_class_1($db_config);
                require_once($this->nbill_admin_base_path . "/framework/database/nbill.database.mambo.class.php");
                $return_value = new nbf_mambo_database();
            }
            else
            {
                if (class_exists($translator_db_class_2))
                {
                    $db_config = array("driver"=>"mysqli", "host"=>$this->db_connection->host, "user"=>$this->db_connection->user_name, "password"=>$this->db_connection->password, "database"=>$this->db_connection->db_name, "prefix"=>$this->db_connection->prefix,"select"=>true);
                    $database = new $translator_db_class_2($db_config);
                    require_once($this->nbill_admin_base_path . "/framework/database/nbill.database.mambo.class.php");
                    $return_value = new nbf_mambo_database();
                }
                else
                {
                    //Still nothing, so use the nBill framework database object (base class)
                    $nbf_db = new nbf_database($this->db_connection->host, $this->db_connection->user_name, $this->db_connection->password, $this->db_connection->db_name, $this->db_connection->prefix, $this->db_connection->port, $this->db_connection->socket);
                    $nbf_db->set_char_encoding($this->db_charset);
                    $return_value = $nbf_db;
                }
            }
        }

        if ($hold_j_client_id && $mainframe && property_exists($mainframe, '_clientId'))
        {
            @$mainframe->_clientId = $hold_j_client_id;
        }
        return $return_value;
    }

    /**
    * This function can be overridden if the value of 'live site' (the base URL for the website front-end)
    * is held in a configuration file in the CMS. Otherwise, the URL is calculated here based on the location
    * the script is being called from (which is how Joomla 1.5 does it anyway).
    * @return string The base part of the URL (ie. to where the CMS is installed)
    */
    protected function get_live_site()
    {
        $live_site = "";

        require_once(dirname(__FILE__) . "/../classes/nbill.common.class.php");
        $current_url = nbf_common::get_requested_page(true);
        $url_parts = @parse_url($current_url);

        //If live site has been set in config file, use that (but convert to https if necessary)
        if (method_exists($this, "read_config_file_value") && strlen($this->read_config_file_value('live_site')) > 3)
        {
            $live_site = $this->read_config_file_value('live_site');
            //Knock off trailing slash if present
            if (nbf_common::nb_substr($live_site, nbf_common::nb_strlen($live_site) - 1) == "/")
            {
                $live_site = nbf_common::nb_substr($live_site, 0, nbf_common::nb_strrpos($live_site, "/"));
            }
            //Convert to https if necessary
            if (isset($url_parts['scheme']) && $url_parts['scheme'] == "https"
                || (isset($_SERVER['HTTPS']) && nbf_common::nb_strtolower($_SERVER['HTTPS']) == 'on'))
            {
                $live_site = str_replace("http://", "https://", $live_site);
            }
            return $live_site;
        }

        //In some cases the only way to get the value is to ask Joomla for it
        if (class_exists('JUri') && method_exists('JUri', 'base')) {
            $live_site = JUri::base(false);
            //Knock off trailing slash if applicable
            $live_site = rtrim($live_site, "/");

            //If we are in the /administrator/ folder, knock off that part to get the root folder
            if (@nbf_common::nb_substr($live_site, nbf_common::nb_strlen($live_site) - 14) == "/administrator")
            {
                $live_site = nbf_common::nb_substr($live_site, 0, nbf_common::nb_strlen($live_site) - 14);
            }
            return $live_site;
        }

        //TODO: find the first part, before index.php or /administrator/index2.php
        if (isset($url_parts['scheme']))
        {
            $live_site = $url_parts['scheme'] . "://";
        }
        if (isset($url_parts['host']))
        {
            $live_site .= $url_parts['host'];
        }
        if (isset($url_parts['port']))
        {
            if (!isset($url_parts['scheme']) ||
                ($url_parts['scheme'] == "http" && $url_parts['port'] != 80) ||
                ($url_parts['scheme'] == "https" && $url_parts['port'] != 443) ||
                ($url_parts['scheme'] != "http" && $url_parts['scheme'] != "https"))
            {
                $live_site .= ":" . $url_parts['port'];
            }
        }
        if (isset($url_parts['path']))
        {
            $live_site .= str_replace("\\", "/", $url_parts['path']);
            //Knock off trailing slash and anything that comes after it
            $live_site = nbf_common::nb_substr($live_site, 0, nbf_common::nb_strrpos($live_site, "/"));

            //If we are in the /administrator/ folder, knock off that part to get the root folder
            if (@nbf_common::nb_substr($live_site, nbf_common::nb_strlen($live_site) - 14) == "/administrator")
            {
                $live_site = nbf_common::nb_substr($live_site, 0, nbf_common::nb_strlen($live_site) - 14);
            }
            if (@nbf_common::nb_substr($live_site, nbf_common::nb_strlen($live_site) - 9) == "/w" . "p-admin")
            {
                $live_site = nbf_common::nb_substr($live_site, 0, nbf_common::nb_strlen($live_site) - 9);
            }
        }
        //If SEF is turned on, and/or the Joom!Fish language is being treated as a folder, strip those out
        if (nbf_common::nb_strpos($live_site, "/component/nbill") !== false)
        {
            $live_site = nbf_common::nb_substr($live_site, 0, nbf_common::nb_strpos($live_site, "/component/nbill"));
        }
        if (nbf_common::nb_strpos($live_site, "/component") !== false)
        {
            $live_site = nbf_common::nb_substr($live_site, 0, nbf_common::nb_strpos($live_site, "/component"));
        }
        if (nbf_common::nb_substr($live_site, nbf_common::nb_strlen($live_site) - 3) == "/" . @$_REQUEST['lang'])
        {
            $live_site = nbf_common::nb_substr($live_site, 0, nbf_common::nb_strlen($live_site) - 3);
        }

        //If an index.php has found its way in there, strip it out (and everything after it)
        if (@nbf_common::nb_strpos($live_site, "index.php") !== false)
        {
            $live_site = nbf_common::nb_substr($live_site, 0, nbf_common::nb_strpos($live_site, "index.php"));
        }

        //If SEF URL handling has added the action parameter as a virtual folder, remove it
        $action = nbf_common::get_param($_REQUEST, 'action');
        if (method_exists($this, "read_config_file_value") && nbf_common::nb_strpos($live_site, "/$action") >= nbf_common::nb_strlen($live_site) - (nbf_common::nb_strlen($action) + 1))
        {
            if ($this->read_config_file_value('sef') && $this->read_config_file_value('sef_rewrite'))
            {
                $live_site = nbf_common::nb_substr($live_site, 0, nbf_common::nb_strpos($live_site, "/$action"));
            }
        }

        require_once(dirname(__FILE__) . "/../nbill.config.php");
        if ((@$_SERVER['HTTPS'] && nbf_common::nb_strtolower(@$_SERVER['HTTPS']) != 'off') || @$_SERVER['SERVER_PORT'] == nbf_config::$ssl_port)
        {
            $live_site = str_replace("http://", "https://", $live_site);
        }
        return $live_site;
    }

    /**
    * Override this function to supply the first part of the URL (after live site) to call
    * for each page request (ie. the bit that tells the CMS which component to hand over to).
    * Also set the full URLs to the admin and front-end component files (so we can include
    * the admin stylesheet and reference images in our HTML output)
    */
    abstract protected function set_url_prefixes();

    /**
    * Return information about the currently logged in user
    * @return nb_user Details of the currently logged in user
    */
    abstract public function get_user($user_id = null);

    /**
    * Return the database connection settings. The CMS should have these stored
    * in a config file somewhere, so this function should be overridden to get the details
    * from there.
    * @param string Host name (eg. "localhost")
    * @param string Database name
    * @param string Database username
    * @param string Password
    * @return nbf_db_connection An object containing the connection settings
    */
    abstract public function get_db_connection_settings();

    /**
    * Return the URL for the default CMS user registration page
    */
    abstract public function get_user_registration_url();

    /**
    * In cases where the CMS needs to output a validation hash on login forms, echo the hidden field here
    */
    public function get_login_spoof_checker()
    {
        return "";
    }

    public function add_js_file_to_head($js_url) {
        $content = '<script type="text/javascript" src="' . $js_url . '"></script>';
        return $this->add_html_header($content);
    }

    public function add_css_file_to_head($css_url) {
        $content = '<link rel="stylesheet" href="' . $css_url . '" type="text/css" />';
        return $this->add_html_header($content);
    }

    /**
    * Add some HTML to the <head> section of the page being output. If there is a global
    * $mainframe object with an addCustomHeadTag method, that is called first. If the
    * output has already been buffered, insert the code into the output buffer. If a
    * different approach is required by the CMS, that can be used instead by overriding
    * this function.
    */
    public function add_html_header($content)
    {
        global $mainframe;

        $content = trim($content);
        if ($mainframe && method_exists($mainframe, "addCustomHeadTag")) {
            if (method_exists($mainframe, "getHead")) {
                $existing_head = $mainframe->getHead();
                if (nbf_common::nb_strpos($existing_head, $content) === false) { //Make sure it has not already been added
                    $mainframe->addCustomHeadTag($content);
                }
            } else {
                $mainframe->addCustomHeadTag($content);
            }
        }

        //In case we are overriding the CMS (eg. on the form editor) and have output our own head tag, check the output buffers...

        //Read all the output buffers into memory
        $loop_breaker = 15; //Don't get stuck in a loop (some versions of PHP are buggy)
        $buffers = array();
        while (ob_get_length() !== false) {
            $loop_breaker--;
            $buffers[] = ob_get_contents();
            if (!@ob_end_clean()) {
                break;
            }
            if ($loop_breaker == 0) {
                break;
            }
        }

        //Go through each one and find the one that contains the head section (or already contains $content)
        $buffer_count = count($buffers);
        $header_added = false;
        for ($i = 0; $i < $buffer_count; $i++) {
            if (nbf_common::nb_strpos($buffers[$i], $content) !== false) {
                //We have already successfully added this content to the head section
                $header_added = true;
            } else if (!$header_added && strrpos($buffers[$i], "</head>") !== false) {
                //Add custom head tag
                $buffers[$i] = substr_replace($buffers[$i], "$content</head>", strrpos($buffers[$i], "</head>"), 7);
                $header_added = true;
                break;
            }
        }
        //Echo them in back in the right order
        for ($j = $buffer_count - 1; $j >= 0; $j--) {
            ob_start();
            echo $buffers[$j];
        }
    }

    /**
    * Converts a mambo-style path (to the component files) into the appropriate path
    * for the CMS concerned. Typically, you would not need to override this, as it
    * will work out the correct path translation based on the base paths already set.
    * @param string $path The relative path to convert.
    * @return string The converted relative path.
    */
    public function convert_path($path)
    {
        if (nbf_common::nb_strpos($path, "administrator/components/com_nbill") !== false)
        {
            return str_replace("administrator/components/com_nbill", nbf_common::nb_substr($this->nbill_admin_base_path, nbf_common::nb_strlen($this->site_base_path) + 1));
        }
        if (nbf_common::nb_strpos($path, "components/com_nbill") !== false)
        {
            return str_replace("components/com_nbill", nbf_common::nb_substr($this->nbill_fe_base_path, nbf_common::nb_strlen($this->site_base_path) + 1));
        }
        return $path;
    }

    /**
    * Perform any tasks necessary to complete the installation in the CMS (eg. adding menu icon, integrating with Joom!Fish/Nokkaew/whatever)
    */
    public function install_tasks()
    {
        //Add icon to admin menu (same for all Mambo family products and Joomla 1.5, but can be overridden for other CMSs if required)
        if (strtolower($this->cms_name) == 'mambo' || (strtolower($this->cms_name) == "joomla!" && (substr($this->cms_version, 0, 3) == "1.0" || substr($this->cms_version, 0, 3) == "1.5")))
        {
            $nb_database = $this->database;
            $nb_database->setQuery("SELECT id FROM #__components WHERE admin_menu_link = 'option=com_nbill'");
            $id = intval($nb_database->loadResult());
            $nb_database->setQuery( "UPDATE `#__components` SET `admin_menu_alt` = '" . NBILL_BRANDING_NAME . "', `admin_menu_img` = '../administrator/components/com_nbill/logo-icon-16.gif' WHERE `id` = '$id'");
            $nb_database->query();
        }
    }

    /**
    * Perform any tasks necessary to complete uninstallation
    */
    public function uninstall_tasks()
    {
        $this->unregister_custom_component_name();
    }

    /**
    * Record the table and column names for the CMS database entities that are required by nBill
    */
    protected function set_cms_database_enum()
    {
        $this->cms_database_enum = new cms_database_enumerator();

        //Access Levels
        $this->cms_database_enum->table_user_groups = "#__groups";
        $this->cms_database_enum->column_acl_id = "id";
        $this->cms_database_enum->column_acl_name = "name";

        //User Groups
        $this->cms_database_enum->table_user_group = "#__core_acl_aro_groups";
        $this->cms_database_enum->column_user_group_id = "group_id";
        $this->cms_database_enum->column_user_group_name = "name";
        $this->cms_database_enum->column_user_group_parent_id = "parent_id";
        $this->cms_database_enum->column_user_group_left = "lft";
        $this->cms_database_enum->column_user_group_right = "rgt";

        //Users
        $this->cms_database_enum->table_user = "#__users";
        $this->cms_database_enum->column_user_id = "id";
        $this->cms_database_enum->column_user_username = "username";
        $this->cms_database_enum->column_user_email = "email";
        $this->cms_database_enum->column_user_gid = "gid";
        $this->cms_database_enum->column_user_name = "name";
        $this->cms_database_enum->column_user_password = "password";
        $this->cms_database_enum->column_block = "block";
        $this->cms_database_enum->column_register_date = "registerDate";
        $this->cms_database_enum->column_last_visit_date = "lastvisitDate";

        //Hard-coded default GIDs for super administrator, minimum administrator, and registered groups
        $this->cms_database_enum->super_admin_gid = 25;
        $this->cms_database_enum->manager_gid = 23;
        $this->cms_database_enum->registered_gid = 18;
    }

    /**
    * Returns a list of user groups from the tree in order of hierarchy
    */
    public function get_acl_group_list()
    {
        $nb_database = $this->database;

        //Set the groups to be excluded (hidden)
        $hidden_groups = array();
        $hidden_groups[] = 17; //Root
        $hidden_groups[] = 28; //Users
        $hidden_groups[] = 29; //public_frontend
        $hidden_groups[] = 30; //public_backend

        //Initialise
        $left = 0;
        $right = 0;
        $left_col = $this->cms_database_enum->column_user_group_left;
        $right_col = $this->cms_database_enum->column_user_group_right;
        $group_table = $this->cms_database_enum->table_user_group;
        $group_name_col = $this->cms_database_enum->column_user_group_name;
        $group_id_col = $this->cms_database_enum->column_user_group_id;
        $parent_id_col = $this->cms_database_enum->column_user_group_parent_id;

        //Find the starting point (the USERS group)
        $sql = "SELECT `$left_col`, `$right_col` FROM `$group_table` WHERE `$group_name_col` = 'USERS'";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($context);
        if ($context)
        {
            $left = $context->$left_col;
            $right = $context->$right_col;
        }

        //Load the children of the USERS group
        $where = "";
        if ($left + $right != 0)
        {
            $where = "WHERE g1.$left_col > $left AND g1.$right_col < $right";
        }

        $sql = "SELECT g1.$group_id_col, g1.$group_name_col, g1.$parent_id_col,
                            count(g2.$group_name_col) AS level FROM $group_table AS g1
                            INNER JOIN $group_table AS g2
                            ON g1.$left_col BETWEEN g2.$left_col AND g2.$right_col
                            $where GROUP BY g1.$group_name_col
                            ORDER BY g1.$left_col";
        $nb_database->setQuery($sql);
        $groups = $nb_database->loadObjectList();
        if (!$groups)
        {
            $groups = array();
        }

        //Remove hidden groups
        $retVal = array();
        foreach ($groups as $group)
        {
            if (!in_array($group->$group_id_col, $hidden_groups))
            {
                $retVal[] = $group;
            }
        }

        return $retVal;
    }

    /**
    * Return the name of the given group
    * @param int $gid
    */
    public abstract function get_user_group_name($gid);

    /**
    * Get an associative array of parameters for the user subscription mambot/plugin
    * @return array The parameters as an associative array
    */
    public abstract function get_account_expiry_params();

    /**
    * Check whether current CMS HTML editor can be supported (we default to nicEdit if not)
    */
    public abstract function cms_editor_supported();

    /**
    * Initialises the HTML editor (include any supporting javascript), but does not actually output an editor (defaults to nicEdit)
    */
    public function init_editor($force_nicedit = false)
    {
        require_once($this->nbill_admin_base_path . "/framework/nbill.config.php");
        $this->add_html_header('<script type="text/javascript" src="' . $this->nbill_site_url_path . '/js/editors/nicEdit/nicEdit.js"></script>');
    }

    /**
    * Returns the necessary code to instantiate an HTML editor with the given name and ID (default editor is nicEdit)
    * @param string $element_name (hard coded please - no user input!)
    * @param string $element_id (hard coded please - no user input!)
    * @param string $value The default or initial value (HTML)
    * @param string $attributes Any other HTML attributes to add to the underlying textarea control (might not have any effect, depending on the editor!)
    * @param boolean $force_nicedit Whether or not to bypass the CMS editor and use nicEdit
    */
    public function render_editor($element_name, $element_id, $value, $attributes = "", $force_nicedit = false)
    {
        $output = "";

        require_once($this->nbill_admin_base_path . "/framework/nbill.config.php");

        $orig_config_value = nbf_config::$editor;
        nbf_config::$editor = "nicEdit";
        $output = "<div class=\"nbill-html-editor\">";
        $output .= "<textarea name=\"$element_name\" id=\"$element_id\" $attributes>$value</textarea>";
        $output .= "</div>";
        if (strtolower($orig_config_value) != 'none') {
            $output .= "<script type=\"text/javascript\">\n";
            $output .= "var wysiwyg_" . $element_id  . "= null;setTimeout(function(){try{wysiwyg_" . $element_id . "= new nicEditor({iconsPath : '" . $this->nbill_site_url_path . "/js/editors/nicEdit/nicEditorIcons.gif', fullPanel : true}).panelInstance('" . $element_id . "');}catch(err){alert(err);}}, 750);\n";
            $output .= "</script>\n";

            //Update apply/save buttons
            $loop_breaker = 15; //Don't get stuck in a loop (some versions of PHP are buggy)
            $buffers = array();
            while (ob_get_length() !== false)
            {
                $loop_breaker--;
                $buffers[] = ob_get_contents();
                if (!@ob_end_clean())
                {
                    break;
                }
                if ($loop_breaker == 0)
                {
                    break;
                }
            }

            //Go through each one and replace any javascript submits so that the nicEdit content is captured
            $buffer_count = count($buffers);
            $header_added = false;
            for ($i = 0; $i < $buffer_count; $i++)
            {
                //$buffers[$i] = str_replace("document.adminForm.submit()", 'if(' . $element_id . '.nicInstances){for(var i=0;i<' . $element_id . '.nicInstances.length;i++){' . $element_id . '.nicInstances[i].saveContent();}}document.adminForm.submit()', $buffers[$i]);
                $buffers[$i] = str_replace("document.adminForm.submit()", $this->get_editor_contents($element_id) . "\ndocument.adminForm.submit()", $buffers[$i]);
            }
            //Echo them in reverse (to get them back in the right order)
            for ($j = $buffer_count - 1; $j >= 0; $j--)
            {
                ob_start();
                echo $buffers[$j];
            }
        }

        nbf_config::$editor = $orig_config_value;
        return $output;
    }

    function get_editor_contents($element_id, $element_name = '', $force_nicedit = false)
    {
        $output = '';
        if (strtolower(@nbf_config::$editor) != 'none') {
            $element_name = $element_name == '' ? $element_id : $element_name;
            ob_start();
            ?>if (wysiwyg_<?php echo $element_id; ?> && wysiwyg_<?php echo $element_id; ?>.nicInstances && wysiwyg_<?php echo $element_id; ?>.nicInstances[0] && wysiwyg_<?php echo $element_id; ?>.nicInstances[0].saveContent)
            {
                wysiwyg_<?php
                echo $element_id; ?>.nicInstances[0].saveContent();
            }
            <?php
            $output = ob_get_clean();
        }
        return $output;
    }

    /**
    * Convert a plain text password into a hash that can be stored in the database and compared
    * @param string $plain_text_password
    * @return string A hash
    */
    public function get_password_hash($plain_text_password)
    {
        $salt = md5($plain_text_password);
        $pwd_hash = md5($plain_text_password . $salt) . ":" . $salt;
        return $pwd_hash;
    }

    /**
    * Just log the current user out
    */
    public function log_out()
    {
        global $mainframe, $my;
        @$mainframe->logout();
        if (method_exists($mainframe, "initSession"))
        {
            @$mainframe->initSession();
        }
        $my = null;
    }

    /**
    * Block the current user from logging in
    * @param integer $user_id ID of the user to block
    */
    public function block_user($user_id)
    {
        $nb_database = $this->database;
        $sql = "UPDATE " . $this->cms_database_enum->table_user . " SET " . $this->cms_database_enum->column_block . " = 1 WHERE id = " . intval($user_id);
        $nb_database->setQuery($sql);
        $nb_database->query();
        nbf_common::fire_event("user_blocked", array("user_id"=>$user_id));
    }

    /**
    * Check which of the 2 access level user groups is higher in the tree. NOTE: This function is
    * based on the pre-defined access level group tree in Mambo and Joomla. It might need to be
    * overridden for other CMSs.
    * @param integer $level_a
    * @param integer $level_b
    * @return integer Returns 1 if level_a is higher, 2 if level_b is higher, or 0 if they are equal
    */
    public function compare_user_groups($level_a, $level_b)
    {
        $nb_database = $this->database;

        $left_col = $this->cms_database_enum->column_user_group_left;
        $right_col = $this->cms_database_enum->column_user_group_right;
        $group_table = $this->cms_database_enum->table_user_group;
        $group_id_col = $this->cms_database_enum->column_user_group_id;
        $parent_id_col = $this->cms_database_enum->column_user_group_parent_id;

        $frontend = 29;
        $backend = 30;

        //Get Public Frontend, Public Backend, level a, and level b from the group tree
        $sql = "SELECT $group_id_col, $left_col, $right_col FROM $group_table WHERE
                $group_id_col IN ($frontend, $backend, $level_a, $level_b)";
        $nb_database->setQuery($sql);
        $groups = $nb_database->loadAssocList($group_id_col);

        if ($groups && isset($groups[$frontend]) && isset($groups[$backend]))
        {
            //Find out which branch each level is on (frontend or backend)
            $a_frontend = $groups[$level_a][$left_col] > $groups[$frontend][$left_col] && $groups[$level_a][$right_col] < $groups[$frontend][$right_col];
            $b_frontend = $groups[$level_b][$left_col] > $groups[$frontend][$left_col] && $groups[$level_b][$right_col] < $groups[$frontend][$right_col];
            $a_backend = $groups[$level_a][$left_col] > $groups[$backend][$left_col] && $groups[$level_a][$right_col] < $groups[$backend][$right_col];
            $b_backend = $groups[$level_b][$left_col] > $groups[$backend][$left_col] && $groups[$level_b][$right_col] < $groups[$backend][$right_col];

            //If one is within Frontend and one in Backend, return the backend one
            if ($a_frontend && $b_backend)
            {
                return 2;
            }
            else if ($a_backend && $b_frontend)
            {
                return 1;
            }

            //Either both are in Frontend, both are in Backend, or we are in neither frontend nor backend.
            //Return whichever one has a greater number of parents on the assumption that that makes it a higher access level
            //We have to traverse the tree to work this out
            $min = 0;
            $max = 999999;
            if ($a_frontend && $b_frontend)
            {
                $min = $groups[$frontend][$left_col];
                $max = $groups[$frontend][$right_col];
            }
            else if ($a_backend && $b_backend)
            {
                $min = $groups[$backend][$left_col];
                $max = $groups[$backend][$right_col];
            }
            $sql = "SELECT * FROM $group_table WHERE $left_col > $min AND $left_col < $max ORDER BY $left_col ASC";

            $nb_database->setQuery($sql);
            $full_tree = $nb_database->loadObjectList();

            //Keep a note of the right values
            $right = array();

            //Iterate through each row
            $a_pos = null;
            $b_pos = null;
            foreach ($full_tree as $row)
            {
               //If the right value of the current node is less than the last item in the array, remove the last item
               if (count($right)>0)
               {
                   while (count($right) > 0 && $right[count($right)-1] < $row->$right_col)
                   {
                       array_pop($right);
                   }
               }

               //Now we know how deep we are in the tree, check if we have hit one of our targets
               if ($row->$group_id_col == $level_a)
               {
                   $a_pos = count($right);
               }
               if ($row->$group_id_col == $level_b)
               {
                   $b_pos = count($right);
               }

               //If both targets have been found, we can compare them
               if ($a_pos !== null && $b_pos !== null)
               {
                   if ($a_pos > $b_pos)
                   {
                       return 1;
                   }
                   else if ($a_pos < $b_pos)
                   {
                       return 2;
                   }
                   else
                   {
                       return 0;
                   }
               }

               $right[] = $row->$right_col;
            }
        }
        return 0; //Assume Equality
    }

    /**
    * Create a new user record in the CMS
    * @param string $name Real name of person
    * @param string $username Username
    * @param string $password Plain text password
    * @param string $email Email address
    * @return integer Returns the user ID or -1 on failure
    */
    abstract function create_user($name, $username, $password, $email);

    /**
    * Generate a username based on the e-mail address
    * @param mixed $email_address
    * @param mixed $form_id Optional order form ID
    */
    public function generate_username($email_address, $form_id = null)
    {
        if ($form_id)
        {
            $sql = "SELECT use_email_address FROM #__" . nbf_globals::$form_table_prefix . " WHERE id = $form_id";
            $this->database->setQuery($sql);
            if ($this->database->loadResult())
            {
                return $email_address;
            }
        }

        //Use first part of email address and add a suffix if it is not unique
        $username = substr($email_address, 0, strpos($email_address, '@'));
        $suffix = 1;
        $unique = false;
        while (!$unique)
        {
            $sql = "SELECT `" . $this->cms_database_enum->column_user_id . "` FROM `" . $this->cms_database_enum->table_user . "` WHERE `" . $this->cms_database_enum->column_user_username . "` = '$username'";
            $this->database->setQuery($sql);
            if ($this->database->loadResult())
            {
                $suffix++;
                if ($suffix > 200)
                {
                    return ''; //Infinite loop protection
                }
                $username .= $suffix;
            }
            else
            {
                $unique = true;
            }
        }
        return $username;
    }

    /**
    * Delete the user from the database completely (use with caution!!)
    * @param integer $user_id ID of the user to delete
    */
    public function delete_user($user_id)
    {
        $user_id_array = array($user_id);
        self::delete_users($user_id_array);
    }

    /**
    * Delete all users in the given array of user IDs
    * @param mixed $user_id_array
    */
    function delete_users($user_id_array, $cms_users_deleted = false)
    {
        //Delete related information from nBill records
        $sql = "DELETE FROM #__nbill_account_expiry WHERE user_id IN (" . implode(",", $user_id_array) . ")";
        $nb_database->setQuery($sql);
        $nb_database->query();
        $sql = "UPDATE #__nbill_contact SET user_id = 0 WHERE user_id IN (" . implode(",", $user_id_array) . ")";
        $nb_database->setQuery($sql);
        $nb_database->query();
        $sql = "UPDATE #__nbill_pending_orders SET user_id = 0 WHERE user_id IN (" . implode(",", $user_id_array) . ")";
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

    /**
    * Change the access level user group for the given user to the new group id
    * @param integer $user_id ID of the user to upgrade or downgrade
    * @param integer $new_level Group ID (gid) of the new user group
    */
    abstract function change_user_group($user_id, $new_group);

    /**
    * Add the user to the given group (leaving existing group assignments alone if multiple groups are allowed by the CMS)
    * @param integer $user_id ID of the user to add
    * @param integer $new_group Group ID (gid) of the group to add the user to
    */
    abstract function add_user_to_group($user_id, $new_group);

    /**
    * Remove the user from one group, and add them to another
    * @param integer $user_id ID of the user to move
    * @param integer $current_group Group ID (gid) of the old group
    * @param integer $new_group Group ID (gid) of the new group
    */
    abstract function replace_user_group($user_id, $current_group, $new_group);

    /**
    * Whether or not the given user belongs to the given group
    * @param integer $user_id ID of the user
    * @param integer $group_id ID of the group
    */
    function user_in_group($user_id, $group_id)
    {
        $sql = "SELECT `" . $this->cms_database_enum->column_user_gid . "`
                FROM `" . $this->cms_database_enum->table_user . "`
                WHERE id = " . intval($user_id);
        $this->database->setQuery($sql);
        return $this->database->loadResult() == $group_id;
    }

    /**
    * Return a list of all CMS users older than a certain date
    * @param mixed $older_than_date
    */
    function find_old_user_records($older_than_date)
    {
        $sql = "SELECT `" . $this->cms_database_enum->column_user_id . "`, `" . $this->cms_database_enum->column_user_username . "`
                FROM `" . $this->cms_database_enum->table_user . "`
                WHERE `" . $this->cms_database_enum->column_register_date . "` < FROM_UNIXTIME($older_than_date)
                AND `" . $this->cms_database_enum->column_last_visit_date . "` < FROM_UNIXTIME($older_than_date)
                ORDER BY `" . $this->cms_database_enum->column_user_username . "`";
        $this->database->setQuery($sql);
        return $this->database->loadAssocList($this->cms_database_enum->column_user_id);
    }

    /**
    * If programmaitcally possible, log the current user out then back in again
    * @param nb_user $user Information about the user - typically just the ID is needed, but if required by the CMS,
    * the other data can be updated in the session variables instead of actually logging out and back in.
    * @param string $url URL to redirect to after logging back in (if applicable)
    */
    abstract function log_out_then_in_again($user, $url = "");

    /**
    * Check whether or not the user subscription mambot/plugin is installed and published
    */
    abstract function user_sub_plugin_present();

    /**
    * Create a new menu item in the CMS, pointing to the order form
    * @param integer $form_id ID of form to link to
    * @param string $name Caption for the menu item
    * @param string $menu Name of the menu to add the link to
    * @param string $action The value to use for the action parameter in the URL
    * @return integer ID of the newly created menu item
    */
    public abstract function create_menu_item($form_id, $name, $menu, $action = 'orders');

    /**
    * Log the given user in to the CMS
    * @param string $username Username of the user to login
    * @param string $password Password of the user to login
    * @param boolean $remember Whether or not to stay logged in indefinitely
    * @param string $return_url Where to redirect to after logging in
    */
    public abstract function login($username, $password, $remember = false, $return_url = "");

    /**
    * Return the URL for the lost password feature of the CMS
    */
    public abstract function get_lost_password_link();

    /**
    * Return the URL for the logout feature of the CMS
    * @param string $return Optional URL to redirect to after logging out, if supported by CMS
    */
    public abstract function get_logout_link($return = "");

    /**
    * Make sure the username supplied is valid according to any rules imposed by the CMS
    * @param string $username May be truncated if required by CMS
    * @param string $email_as_username Whether or not the email address is being used as the username (prevents truncation)
    * @return boolean Whether the username is valid or not
    */
    public abstract function validate_username(&$username, $email_as_username = false);

    /**
    * If CMS requires some processing to make URLs search engine-friendly, this is where it's done.
    */
    public function process_url($url_to_process)
    {
        return $url_to_process; //By default, we do nothing, but this can be overridden
    }

    /**
    * Update the username for the given user (or the currently logged in user if no user specified).
    * If the username and email address are the same on the existing record, the email address will also be updated.
    * @param string $username The new username
    * @param integer $user_id The ID of the user to update (if omitted, the currently logged in user will be updated, if applicable)
    */
    public function update_username($username, $user_id = null)
    {
        $nb_database = $this->database;
        if (!$user_id)
        {
            $user_id = $this->user->id;
        }
        $current_user = null;
        $user_name_col = $this->cms_database_enum->column_user_username;
        $email_col = $this->cms_database_enum->column_user_email;

        $sql = "SELECT $user_name_col, $email_col FROM " .
                $this->cms_database_enum->table_user . " WHERE " .
                $this->cms_database_enum->column_user_id . " = " . intval($user_id);
        $nb_database->setQuery($sql);
        $nb_database->loadObject($current_user);
        if ($current_user)
        {
            $sql = "UPDATE " . $this->cms_database_enum->table_user . " SET $user_name_col = '$username'";

            //If username is email address, update username too
            if ($current_user->$user_name_col == $current_user->$email_col)
            {
                $sql .= ", $email_col = '$username'";
            }
            $sql .= " WHERE " . $this->cms_database_enum->column_user_id . " = " . intval($user_id);
            $nb_database->setQuery($sql);
            $nb_database->query();
            $this->user->username = $username;
        }
    }

    /**
    * Update the password for the given user (or the currently logged in user if no user specified)
    * @param string $password The plain text password
    * @param integer $user_id The ID of the user to update (if omitted, the currently logged in user will be updated, if applicable)
    */
    public abstract function update_password($password, $user_id = null);

    /**
    * Update the email address for the given user (or the currently logged in user if no user specified).
    * If the username and email address are the same on the existing record, the username will also be updated.
    * @param string $email_address The new email address
    * @param integer $user_id The ID of the user to update (if omitted, the currently logged in user will be updated, if applicable)
    */
    public function update_email_address($email_address, $user_id = null)
    {
        $nb_database = $this->database;
        if (!$user_id)
        {
            $user_id = $this->user->id;
        }
        $current_user = null;
        $user_name_col = $this->cms_database_enum->column_user_username;
        $email_col = $this->cms_database_enum->column_user_email;

        $sql = "SELECT $user_name_col, $email_col FROM " .
                $this->cms_database_enum->table_user . " WHERE " .
                $this->cms_database_enum->column_user_id . " = " . intval($user_id);
        $nb_database->setQuery($sql);
        $nb_database->loadObject($current_user);
        if ($current_user)
        {
            $sql = "UPDATE " . $this->cms_database_enum->table_user . " SET $email_col = '$email_address'";

            //If username is email address, update username too
            if ($current_user->$user_name_col == $current_user->$email_col)
            {
                $sql .= ", $user_name_col = '$email_address'";
            }
            $sql .= " WHERE " . $this->cms_database_enum->column_user_id . " = " . intval($user_id);
            $nb_database->setQuery($sql);
            $nb_database->query();
            $this->user->email = $email_address;
        }
    }

    /**
    * Update the name for the given user (or the currently logged in user if no user specified).
    * @param string $name The new name
    * @param integer $user_id The ID of the user to update (if omitted, the currently logged in user will be updated, if applicable)
    */
    public function update_name($name, $user_id = null)
    {
        $nb_database = $this->database;
        if (!$user_id)
        {
            $user_id = $this->user->id;
        }
        $current_user = null;
        $name_col = $this->cms_database_enum->column_user_name;

        $sql = "SELECT $name_col FROM " .
                $this->cms_database_enum->table_user . " WHERE " .
                $this->cms_database_enum->column_user_id . " = " . intval($user_id);
        $nb_database->setQuery($sql);
        $nb_database->loadObject($current_user);
        if ($current_user)
        {
            $sql = "UPDATE " . $this->cms_database_enum->table_user . " SET $name_col = '$name'";
            $sql .= " WHERE " . $this->cms_database_enum->column_user_id . " = " . intval($user_id);
            $nb_database->setQuery($sql);
            $nb_database->query();
            $this->user->name = stripslashes($name);
        }
    }

    /**
    * Check whether a supplied email address is already in use
    * @param string $email_address Email address to check
    * @param boolean $exclude_current_user Whether or not to exclude the currently logged in user from the check
    * @return integer User ID of user who already has that email address, or false if not in use
    */
    public function email_in_use($email_address, $exclude_current_user = true)
    {
        $nb_database = $this->database;
        $sql = "SELECT " . $this->cms_database_enum->column_user_id . " FROM " .
                $this->cms_database_enum->table_user . " WHERE " .
                $this->cms_database_enum->column_user_email . " = '$email_address'";
        if ($exclude_current_user && nbf_cms::$interop->user->id)
        {
            $sql .= " AND id != " . nbf_cms::$interop->user->id;
        }
        $nb_database->setQuery($sql);
        $existing_id = $nb_database->loadResult();
        return $existing_id ? $existing_id : false;
    }

    /**
    * Check whether a supplied username is already in use
    * @param string $username Username address to check
    * @param boolean $exclude_current_user Whether or not to exclude the currently logged in user from the check
    * @return integer User ID of user who already has that username, or false if not in use
    */
    public function username_in_use($username, $exclude_current_user = true)
    {
        $nb_database = $this->database;
        $sql = "SELECT " . $this->cms_database_enum->column_user_id . " FROM " .
                $this->cms_database_enum->table_user . " WHERE " .
                $this->cms_database_enum->column_user_username . " = '$username'";
        if ($exclude_current_user && nbf_cms::$interop->user->id)
        {
            $sql .= " AND id != " . nbf_cms::$interop->user->id;
        }
        $nb_database->setQuery($sql);
        $existing_id = $nb_database->loadResult();
        return $existing_id ? $existing_id : false;
    }

    /**
    * Returns a list of menu titles as an associative array keyed on the menu name. This list is used
    * to facilitate creating a new menu item pointing to an order form. If an empty array is returned
    * by an override of this function, the option to create a menu item will not be available to the user.
    */
    public function get_menu_list()
    {
        $nb_database = $this->database;
        $sql = "SELECT title, params FROM `#__modules` WHERE params LIKE '%menutype=%' AND module = 'mod_mainmenu'";
        $nb_database->setQuery($sql);
        $menu_modules = $nb_database->loadObjectList();
        $menu_types = array();
        if ($menu_modules && count($menu_modules) > 0)
        {
            foreach ($menu_modules as $menu_module)
            {
                //Parse params to get the menu name
                $params = str_replace("\r\n", "\n", $menu_module->params);
                $params = explode("\n", $params);
                foreach ($params as $param)
                {
                    $key_value = explode("=", $param);
                    if (count($key_value) > 1 && $key_value[0] == "menutype")
                    {
                        $name = $key_value[1];
                        break;
                    }
                }
                $menu_types[$name] = $menu_module->title;
            }
        }
        return $menu_types;
    }

    /**
    * Sends an email using the configuration details defined in the CMS (config details can also be specified in the nBill config file)
    * @param string $from
    * @param string $fromname
    * @param mixed $recipient
    * @param string $subject
    * @param string $body
    * @param integer $mode
    * @param mixed $cc
    * @param mixed $bcc
    * @param string $attachment
    * @param string $replyto
    * @param string $replytoname
    * @return bool
    */
    public function send_email($from, $fromname, $recipient, $subject, $body, $mode = 0, $cc = null, $bcc = null, $attachment = null, $replyto = null, $replytoname = null, $attachment_info = null)
    {
        if ($this->demo_mode || nBillConfigurationService::getInstance()->getConfig()->disable_email)
        {
            return true;
        }

        $params = array();
        $params['from'] = $from;
        $params['from_name'] = $fromname;
        $params['recipient'] = $recipient;
        $params['subject'] = $subject;
        $params['body'] = $body;
        $params['mode'] = $mode;
        $params['cc'] = $cc;
        $params['bcc'] = $bcc;
        $params['attachment'] = $attachment;
        $params['reply_to'] = $replyto;
        $params['reply_to_name'] = $replytoname;
        $params['abort'] = false;
        nbf_common::fire_event_by_ref("email_pre_send", $params);
        //In case values were changed in the event, refresh the variables based on the parameter values
        if ($params['abort'] == true)
        {
            return false;
        }
        $from = $params['from'];
        $fromname = $params['from_name'];
        $recipient = $params['recipient'];
        $subject = $params['subject'];
        $body = $params['body'];
        $mode = $params['mode'];
        $cc = $params['cc'];
        if (!$cc || count($cc) == 0 || !$cc[0])
        {
            $cc = null;
        }
        $bcc = $params['bcc'];
        if (!$bcc || count($bcc) == 0 || !$bcc[0])
        {
            $bcc = null;
        }
        $attachment = $params['attachment'];
        $replyto = $params['reply_to'];
        $replytoname = $params['reply_to_name'];

        //Null reply to fields if blank, otherwise phpMailer complains
        if (!$replyto)
        {
            $replyto = null;
        }
        if (!$replytoname)
        {
            $replytoname = null;
        }

        if ($this->char_encoding == "utf-8")
        {
            //Base64 Encode subject so it shows up ok in Japanese etc.
            $subject = "=?utf-8?B?" . base64_encode($subject) . "?=";
        }
        else if ($this->char_encoding == "iso-8859-1")
        {
            $subject = "=?utf-8?B?" . base64_encode(utf8_encode($subject)) . "?=";
        }

        //Mambo style mail function does not seem to handle more than one attachment in Joomla 1.5 legacy
        $single_or_no_attachment = true;
        if (is_array($attachment))
        {
            if (count($attachment) > 1)
            {
                $single_or_no_attachment = false;
            }
            else
            {
                if (count($attachment) > 0)
                {
                    $attachment = $attachment[0];
                }
                else
                {
                    $attachment = "";
                }
            }
        }
        $this->_get_email_config();
        if (nbf_config::$mailer == "[CMS]" && function_exists("mosMail") && !defined("NBILL_LEGACY_MOSMAIL_LOADED") && $single_or_no_attachment) //Legacy file mosMail calls this function, so we get an endless loop if we call it from here!
        {
            //Use the mambo-style mail function
            $return_value = mosMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname) === true;
        }
        else if (nbf_config::$mailer == "[CMS]" && class_exists("JUtility") && method_exists("JUtility", "sendMail") && $single_or_no_attachment)
        {
            $return_value = JUtility::sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
        }
        else if (nbf_config::$mailer == "[CMS]" && class_exists("JFactory") && method_exists("JFactory", "getMailer"))
        {
            $mailer = JFactory::getMailer();
            $sender = array($from, $fromname);
            $mailer->setSender($sender);
            $mailer->addRecipient($recipient);
            $mailer->addCC($cc);
            $mailer->addBCC($bcc);
            if ($replyto)
            {
                $mailer->addReplyTo($replyto, $replytoname);
            }
            if ($single_or_no_attachment && strlen($attachment) > 0 && file_exists($attachment))
            {
                $mailer->addAttachment($attachment);
            }
            else
            {
                if (is_array($attachment))
                {
                    foreach ($attachment as $att_file)
                    {
                        if (file_exists($att_file))
                        {
                            $mailer->addAttachment($att_file);
                        }
                    }
                }
            }
            $mailer->setSubject($subject);
            $mailer->isHTML($mode);
            $mailer->Encoding = 'base64';
            $mailer->setBody($body);
            $return_value = $mailer->Send();
        }
        else
        {
            //Need our own mechanism for sending mail...
            if (nbf_common::nb_strlen($from) > 0)
            {
                nbf_config::$mailfrom = $from;
            }
            if (nbf_common::nb_strlen($fromname) > 0)
            {
                nbf_config::$fromname = $fromname;
            }

            include_once($this->nbill_admin_base_path . "/framework/phpm/class.phpmailer.php");
            $mailer = new nbPHPMailer();
            $mailer->CharSet = $this->char_encoding;
            switch (nbf_config::$mailer)
            {
                case "smtp":
                    $mailer->IsSMTP();
                    $mailer->Host = nbf_config::$smtphost;
                    $mailer->Port = nbf_config::$smtpport;
                    if (nbf_config::$smtpauth)
                    {
                        $mailer->SMTPAuth = true;
                        $mailer->Username = nbf_config::$smtpuser;
                        $mailer->Password = nbf_config::$smtppass;
                    }
                    break;
                case "ssmtp":
                    $mailer->IsSMTP();
                    $mailer->SMTPAuth = true;
                    $mailer->SMTPSecure = "ssl";
                    $mailer->Host = nbf_config::$smtphost;
                    $mailer->Port = nbf_config::$ssmtpport;
                    $mailer->Username = nbf_config::$smtpuser;
                    $mailer->Password = nbf_config::$smtppass;
                    break;
                case "sendmail":
                    $mailer->IsSendmail();
                    if (nbf_config::$sendmail)
                    {
                        $mailer->Sendmail = nbf_config::$sendmail;
                    }
                    break;
                default:
                    //Defaults to PHP Mail function
                    break;
            }

            $mailer->From = nbf_config::$mailfrom;
            $mailer->FromName = nbf_config::$fromname;

            if ($replyto)
            {
                if (!$replytoname)
                {
                    $replytoname = $replyto;
                }
                $mailer->AddReplyTo($replyto, $replytoname);
            }
            $mailer->Subject = $subject;
            if ($mode)
            {
                $mailer->IsHTML(true);
                $mailer->AltBody = strip_tags(str_replace("<br />", "\n", str_replace("</p>", "\n", $body)));
            }
            $mailer->Body = $body;
            if (is_array($recipient))
            {
                foreach ($recipient as $to_address)
                {
                    if (nbf_common::nb_strlen($to_address) > 0)
                    {
                        $mailer->AddAddress($to_address, $to_address);
                    }
                }
            }
            else
            {
                $mailer->AddAddress($recipient, $recipient);
            }
            if ($cc)
            {
                if (is_array($cc))
                {
                    foreach ($cc as $cc_address)
                    {
                        if (nbf_common::nb_strlen($cc_address) > 0)
                        {
                            $mailer->AddCC($cc_address, $cc_address);
                        }
                    }
                }
                else
                {
                    $mailer->AddAddress($cc, $cc);
                }
            }
            if ($bcc)
            {
                if (is_array($bcc))
                {
                    foreach ($bcc as $bcc_address)
                    {
                        if (nbf_common::nb_strlen($bcc_address) > 0)
                        {
                            $mailer->AddBCC($bcc_address, $bcc_address);
                        }
                    }
                }
                else
                {
                    $mailer->AddAddress($bcc, $bcc);
                }
            }

            if ((is_array($attachment) && count($attachment) > 0 && nbf_common::nb_strlen($attachment[0]) > 0) || (!is_array($attachment) && nbf_common::nb_strlen($attachment) > 0))
            {
                if (is_array($attachment))
                {
                    foreach ($attachment as $attach_file)
                    {
                        $attachment_type = 'application/octet-stream';
                        if (isset($attachment_info[$attach_file]) && strlen($attachment_info[$attach_file]) > 0)
                        {
                            $attachment_type = $attachment_info[$attach_file];
                        }
                        $mailer->AddAttachment($attach_file, '', 'base64', $attachment_type);
                    }
                }
                else
                {
                    $mailer->AddAttachment($attachment);
                }
            }

            $return_value = $mailer->Send();
        }
        if ($return_value && @get_class($return_value) != "JException")
        {
            nbf_common::fire_event("email_sent", array("from"=>$from, "from_name"=>$fromname, "recipient"=>$recipient, "subject"=>$subject, "body"=>$body));
        }
        if (isset($mailer))
        {
            nbf_globals::$message = $mailer->ErrorInfo;
        }
        if (!$return_value && nbf_common::nb_strlen(nbf_globals::$message) == 0)
        {
            nbf_globals::$message = NBILL_UNKNOWN_EMAIL_ERROR;
        }
        else if (@get_class($return_value) == "JException")
        {
            if (method_exists($return_value, "getMessage"))
            {
                nbf_globals::$message = $return_value->getMessage();
            }
            else
            {
                nbf_globals::$message = @$return_value->message;
            }
            $return_value = false;
        }
        return $return_value;
    }

    /**
    * Return the email configuration details as specified in the CMS
    */
    private function _get_email_config()
    {
        require_once($this->nbill_admin_base_path . "/framework/nbill.config.php");
        switch (nbf_common::nb_strtolower(nbf_config::$mailer))
        {
            case "mail":
            case "sendmail":
            case "smtp":
            case "ssmtp":
                //Just use values from config file
                break;
            default: //[CMS]
                //Try to load from CMS
                $this->get_email_config();
                break;
        }
    }

    /**
    * Should be overridden by CMS to set the mail config values held by the CMS.
    * Otherwise, default to PHP Mail function.
    */
    public function get_email_config()
    {
        require_once($this->nbill_admin_base_path . "/framework/nbill.config.php");
        nbf_config::$mailer = "mail";
        nbf_config::$mailfrom = "";
        nbf_config::$fromname = $this->site_name;
    }

    /**
    * Return the user ID for a record that matches the given values, if any
    * @param string $username
    * @param string $email
    * @param string $password Plain text password
    * @return mixed The existing user ID, or false if none found
    */
    public function get_user_id($username, $email, $password)
    {
        $nb_database = $this->database;
        $user_id = false;
        $col_id = $this->cms_database_enum->column_user_id;
        $col_pwd = $this->cms_database_enum->column_user_password;
        $sql = "SELECT $col_id, " . $this->cms_database_enum->column_user_username
                . ", " . $this->cms_database_enum->column_user_email . ", $col_pwd"
                . " FROM " . $this->cms_database_enum->table_user . " WHERE " . $this->cms_database_enum->column_user_username
                . " = '$username' AND " . $this->cms_database_enum->column_user_email . " = '$email'";
        $nb_database->setQuery($sql);
        $existing_user = null;
        $nb_database->loadObject($existing_user);
        $user_id = null;
        if ($existing_user)
        {
            $pwd_hash = $this->get_password_hash($password);
            if ($existing_user->$col_pwd == $pwd_hash)
            {
                $user_id = $existing_user->$col_id;
            }
        }
        return $user_id;
    }

    /**
    * Return the group ID (access level group) of the given user
    * @param int $user_id
    * @return array The group IDs that the given user belongs to
    */
    public function get_user_gid($user_id)
    {
        $nb_database = $this->database;
        $sql = "SELECT " . $this->cms_database_enum->column_user_gid . " FROM " . $this->cms_database_enum->table_user .
                " WHERE " . $this->cms_database_enum->column_user_id . " = " . intval($user_id);
        $nb_database->setQuery($sql);
        return $nb_database->loadResultArray();
    }

    /**
    * Count number of users who are not super admins
    */
    public function count_non_super_admins()
    {
        $user_table = nbf_cms::$interop->cms_database_enum->table_user;
        $user_id_col = nbf_cms::$interop->cms_database_enum->column_user_id;
        $user_gid_col = nbf_cms::$interop->cms_database_enum->column_user_gid;
        $super_admin_gid = intval(nbf_cms::$interop->cms_database_enum->super_admin_gid);

        $sql = "SELECT count(`$user_table`.`$user_id_col`) FROM `$user_table` LEFT JOIN
                    #__nbill_contact ON `$user_table`.`$user_id_col` = #__nbill_contact.user_id
                    WHERE #__nbill_contact.user_id IS NULL
                    AND `$user_table`.`$user_gid_col` != $super_admin_gid";
        $this->database->setQuery( $sql );
        return $this->database->loadResult();
    }

    /**
    * Return a list of users who are not super admins
    */
    public function get_non_super_admins($pagination = null)
    {
        $user_table = nbf_cms::$interop->cms_database_enum->table_user;
        $user_name_col = nbf_cms::$interop->cms_database_enum->column_user_name;
        $user_username_col = nbf_cms::$interop->cms_database_enum->column_user_username;
        $user_email_col = nbf_cms::$interop->cms_database_enum->column_user_email;
        $user_id_col = nbf_cms::$interop->cms_database_enum->column_user_id;
        $user_gid_col = nbf_cms::$interop->cms_database_enum->column_user_gid;
        $super_admin_gid = intval(nbf_cms::$interop->cms_database_enum->super_admin_gid);

        $sql = "SELECT `$user_table`.`$user_id_col` AS user_id, `$user_table`.`$user_username_col` AS username, `$user_table`.`$user_name_col` AS name, `$user_table`.`$user_email_col` AS email FROM `$user_table`
                    LEFT JOIN #__nbill_contact ON `$user_table`.`$user_id_col` = #__nbill_contact.user_id
                    WHERE #__nbill_contact.user_id IS NULL
                    AND `$user_table`.`$user_gid_col` != $super_admin_gid ORDER BY `$user_table`.`$user_name_col`";
        if ($pagination)
        {
            $sql .= " LIMIT $pagination->list_offset, $pagination->records_per_page";
        }
        $this->database->setQuery($sql);
        return $this->database->loadObjectList();
    }

    /**
    * Count number of users
    */
    public function count_all_users($username_filter = "", $name_filter = "", $email_filter = "", $exclusive_filters = true, $where_clause = "")
    {
        $user_table = nbf_cms::$interop->cms_database_enum->table_user;
        $user_name_col = nbf_cms::$interop->cms_database_enum->column_user_name;
        $user_username_col = nbf_cms::$interop->cms_database_enum->column_user_username;
        $user_email_col = nbf_cms::$interop->cms_database_enum->column_user_email;

        $sql = "SELECT count(*) FROM `$user_table`";
        if (nbf_common::nb_strlen($where_clause) > 0 || nbf_common::nb_strlen($username_filter) > 0 || nbf_common::nb_strlen($name_filter) > 0 || nbf_common::nb_strlen($email_filter) > 0)
        {
            $sql .= " WHERE ";
            $sql_where = array();
            if (nbf_common::nb_strlen($where_clause) > 0)
            {
                $sql .= $where_clause;
                if (nbf_common::nb_strlen($username_filter) > 0 || nbf_common::nb_strlen($name_filter) > 0 || nbf_common::nb_strlen($email_filter) > 0)
                {
                    $sql .= " AND (";
                }
            }
            if (nbf_common::nb_strlen($username_filter) > 0)
            {
                $sql_where[] = "`$user_table`.`$user_username_col` LIKE '%$username_filter%'";
            }
            if (nbf_common::nb_strlen($name_filter) > 0)
            {
                $sql_where[] = "`$user_table`.`$user_name_col` LIKE '%$name_filter%'";
            }
            if (nbf_common::nb_strlen($email_filter) > 0)
            {
                $sql_where[] = "`$user_table`.`$user_email_col` LIKE '%$email_filter%'";
            }
            $sql .= implode(($exclusive_filters ? " AND " : " OR "), $sql_where);
            if (nbf_common::nb_strlen($where_clause) > 0)
            {
                if (nbf_common::nb_strlen($username_filter) > 0 || nbf_common::nb_strlen($name_filter) > 0 || nbf_common::nb_strlen($email_filter) > 0)
                {
                    $sql .= ")";
                }
            }
        }
        $this->database->setQuery( $sql );
        return $this->database->loadResult();
    }

    /**
    * Return a list of users
    */
    public function get_all_users($admin_via_fe = false, $username_filter = "", $name_filter = "", $email_filter = "", $exclusive_filters, $where_clause = "", $order_by = null, $pagination = null)
    {
        $user_table = nbf_cms::$interop->cms_database_enum->table_user;
        $user_name_col = nbf_cms::$interop->cms_database_enum->column_user_name;
        $user_username_col = nbf_cms::$interop->cms_database_enum->column_user_username;
        $user_email_col = nbf_cms::$interop->cms_database_enum->column_user_email;
        $user_id_col = nbf_cms::$interop->cms_database_enum->column_user_id;
        $user_gid_col = nbf_cms::$interop->cms_database_enum->column_user_gid;
        $super_admin_gid = intval(nbf_cms::$interop->cms_database_enum->super_admin_gid);

        $sql = "SELECT `$user_table`.`$user_id_col` AS user_id, `$user_table`.`$user_username_col` AS username,
                `$user_table`.`$user_name_col` AS name, `$user_table`.`$user_email_col` AS email";
        if ($admin_via_fe)
        {
            $sql .= ", #__nbill_user_admin.admin_via_fe";
        }
        $sql .= " FROM `$user_table`";
        if ($admin_via_fe)
        {
            $sql .= " LEFT JOIN #__nbill_user_admin ON `$user_table`.`$user_id_col` = #__nbill_user_admin.user_id";
        }
        if (nbf_common::nb_strlen($where_clause) > 0 || nbf_common::nb_strlen($username_filter) > 0 || nbf_common::nb_strlen($name_filter) > 0 || nbf_common::nb_strlen($email_filter) > 0)
        {
            $sql .= " WHERE ";
            $sql_where = array();
            if (nbf_common::nb_strlen($where_clause) > 0)
            {
                $sql .= $where_clause;
                if (nbf_common::nb_strlen($username_filter) > 0 || nbf_common::nb_strlen($name_filter) > 0 || nbf_common::nb_strlen($email_filter) > 0)
                {
                    $sql .= " AND (";
                }
            }
            if (nbf_common::nb_strlen($username_filter) > 0)
            {
                $sql_where[] = "`$user_table`.`$user_username_col` LIKE '%$username_filter%'";
            }
            if (nbf_common::nb_strlen($name_filter) > 0)
            {
                $sql_where[] = "`$user_table`.`$user_name_col` LIKE '%$name_filter%'";
            }
            if (nbf_common::nb_strlen($email_filter) > 0)
            {
                $sql_where[] = "`$user_table`.`$user_email_col` LIKE '%$email_filter%'";
            }
            $sql .= implode(($exclusive_filters ? " AND " : " OR "), $sql_where);
            if (nbf_common::nb_strlen($where_clause) > 0)
            {
                if (nbf_common::nb_strlen($username_filter) > 0 || nbf_common::nb_strlen($name_filter) > 0 || nbf_common::nb_strlen($email_filter) > 0)
                {
                    $sql .= ")";
                }
            }
        }
        $sql .= " ORDER BY ";
        if (nbf_common::nb_strlen($order_by) > 0)
        {
            $sql .= $order_by . ", ";
        }
        $sql .= "CONCAT(`$user_table`.`$user_name_col`, `$user_table`.`$user_username_col`)";
        if ($pagination)
        {
            $sql .= " LIMIT $pagination->list_offset, $pagination->records_per_page";
        }
        $this->database->setQuery($sql);
        return $this->database->loadObjectList();
    }

    /**
    * Where to post the front end forms to (typically index.php, but further parameters might be required by CMS,
    * esp. if a SEF URL component is installed)
    */
    public function get_fe_form_action()
    {
    	if (nbf_frontend::get_display_option("submit_option"))
    	{
    		return "index.php?option=" . NBILL_BRANDING_COMPONENT_NAME;
		}
		else
		{
    		return "index.php";
		}
	}

	/**
	* Returns the URL of the main stylesheet used by the website front end
	*/
	public function get_website_stylesheet()
	{
		$nb_database = $this->database;
		$sql = "SELECT template FROM #__templates_menu WHERE menuid = 0 AND client_id = 0";
		$nb_database->setQuery($sql);
		$template = $nb_database->loadResult();
		if ($template)
		{
			$file_path = $this->site_base_path . "/templates/$template/";
			$css_path = $this->live_site . "/templates/$template/";
			if (file_exists($file_path . "template.css"))
			{
				return $css_path . "template_css";
			}
			else if (file_exists($file_path . "template_css.css"))
			{
				return $css_path . "template_css.css";
			}
			else if (file_exists($file_path . "css/template.css"))
			{
				return $css_path . "css/template.css";
			}
			else if (file_exists($file_path . "css/template_css.css"))
			{
				return $css_path . "css/template_css.css";
			}
		}
		return "";
	}

    /**
    * Return an array  of reserved words that cannot be used as field names or URL parameters
    * By default, this will return the reserved words typically used by Mambo, Joomla, and PAP
    * @param boolean $url_only Indicates whether to just return reserved words that are used by the CMS in URLs
    */
    public function get_reserved_words($url_only = false)
    {
        $reserved = array();
        $reserved[] = "option";
        $reserved[] = "action";
        $reserved[] = "task";
        $reserved[] = "id";
        $reserved[] = "cid";
        $reserved[] = "Itemid";
        $reserved[] = "aid";
        $reserved[] = "bid";
        $reserved[] = "hide_billing_menu";
        $reserved[] = "ajax";
        $reserved[] = "postback";
        $reserved[] = "page_no";
        $reserved[] = "nbill_entity_id";
        if ($url_only)
        {
            return $reserved;
        }
        $reserved[] = "logged_in";
        $reserved[] = "form_submit";
        $reserved[] = "submit";
        $reserved[] = "back";
        return $reserved;
    }

    /**
    * @return boolean Whether or not to show a warning about gzip causing problems on certain features (only return true if
    * gzip is turned on and does cause a problem on this CMS)
    */
    public function show_gzip_warning()
    {
        return false;
    }

    /**
    * @return string The URL to the configuration page in the CMS where gzip can be turned off
    */
    public abstract function get_gzip_config_url();

    /**
    * Return the FTP connection details for the local site, if known (to allow upgrader to overwrite its own files)
    * @param string $ftp_address
    * @param int $port
    * @param string $username
    * @param string $password
    * @param string $root Root folder that the FTP server allows access to with the above credentials
    * @return boolean Whether or not we can connect using these details
    */
    public function get_ftp_details(&$ftp_address, &$port, &$username, &$password, &$root)
    {
        require_once($this->nbill_admin_base_path . "/framework/nbill.config.php");
        $ftp_address = nbf_config::$ftp_address;
        $port = nbf_config::$ftp_port;
        $username = nbf_config::$ftp_username;
        $password = nbf_config::$ftp_password;
        $root = nbf_config::$ftp_root;

        //Test whether we can make a connection and write to a file
        include_once($this->nbill_admin_base_path . "/framework/classes/nbill.file.class.php");
        $use_ftp = true;
        $error_message = "";
        return nbf_file::is_test_file_writable($use_ftp, $error_message);
    }

    /**
    * Attempt to save the given details to the config file (try accessing directly first, then try ftp connection if it fails)
    * @param mixed $ftp_address
    * @param mixed $port
    * @param mixed $username
    * @param mixed $password
    * @param mixed $root
    */
    public function set_ftp_details($ftp_address, $port, $username, $password, $root)
    {
        $config_file_contents = $this->get_entire_config_file($ftp_address, $port, $username, $password, $root);
        if ($config_file_contents)
        {
            //Replace each entry
            $start_pos = nbf_common::nb_strpos($config_file_contents, 'public static $ftp_address');
            $end_pos = nbf_common::nb_strpos($config_file_contents, '/** @var', $start_pos);
            $config_file_contents = substr($config_file_contents, 0, $start_pos) . "public static \$ftp_address = '$ftp_address';\n    " . substr($config_file_contents, $end_pos);
            $start_pos = nbf_common::nb_strpos($config_file_contents, 'public static $ftp_port');
            $end_pos = nbf_common::nb_strpos($config_file_contents, '/** @var', $start_pos);
            $config_file_contents = substr($config_file_contents, 0, $start_pos) . "public static \$ftp_port = '$port';\n    " . substr($config_file_contents, $end_pos);
            $start_pos = nbf_common::nb_strpos($config_file_contents, 'public static $ftp_username');
            $end_pos = nbf_common::nb_strpos($config_file_contents, '/** @var', $start_pos);
            $config_file_contents = substr($config_file_contents, 0, $start_pos) . "public static \$ftp_username = '$username';\n    " . substr($config_file_contents, $end_pos);
            $start_pos = nbf_common::nb_strpos($config_file_contents, 'public static $ftp_password');
            $end_pos = nbf_common::nb_strpos($config_file_contents, '/** @var', $start_pos);
            $config_file_contents = substr($config_file_contents, 0, $start_pos) . "public static \$ftp_password = '$password';\n    " . substr($config_file_contents, $end_pos);
            $start_pos = nbf_common::nb_strpos($config_file_contents, 'public static $ftp_root');
            $end_pos = nbf_common::nb_strpos($config_file_contents, '/** @var', $start_pos);
            $config_file_contents = substr($config_file_contents, 0, $start_pos) . "public static \$ftp_root = '$root';\n    " . substr($config_file_contents, $end_pos);
            $this->set_entire_config_file($ftp_address, $port, $username, $password, $root, $config_file_contents);
            //Update in memory too, if applicable
            if (class_exists("nbf_config"))
            {
                nbf_config::$ftp_address = $ftp_address;
                nbf_config::$ftp_port = $port;
                nbf_config::$ftp_username = $username;
                nbf_config::$ftp_password = $password;
                nbf_config::$ftp_root = $root;
            }
        }
    }

    /**
    * Redirect to the page that allows FTP details to be entered, along with an error message (override if FTP details are stored in CMS)
    * @param string $error_message
    */
    public function prompt_for_ftp_details($error_message)
    {
        nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . "&action=configuration&ftp_message=" . urlencode($error_message) . "#ftp_details");
    }

    private function get_entire_config_file($ftp_address, $port, $username, $password, $root)
    {
        $config_file_contents = file_get_contents($this->nbill_admin_base_path . "/framework/nbill.config.php");
        if (nbf_common::nb_strlen($config_file_contents) == 0)
        {
            //If we don't have read access to the file (unusual!), try using FTP
            $handle = @ftp_connect($ftp_address, $port, 2);
            if ($handle !== false)
            {
                $memory_handle = @fopen("php://memory", "w");
                if (@ftp_login($handle, $username, $password))
                {
                    if (@ftp_chdir($handle, str_replace($root, "", $this->nbill_admin_base_path) . "/framework/"))
                    {
                        @ftp_fget($handle, $memory_handle, "nbill.config.php", FTP_ASCII);
                    }
                }
                @ftp_close($handle);
                $config_file_contents = @fread($memory_handle, 8192);
                @fclose($memory_handle);
            }
        }
        return $config_file_contents;
    }

    private function set_entire_config_file($ftp_address, $port, $username, $password, $root, $config_file_contents)
    {
        if (!file_put_contents($this->nbill_admin_base_path . "/framework/nbill.config.php", $config_file_contents))
        {
            //If we don't have write access to the file, try using FTP
            $handle = @ftp_connect($ftp_address, $port, 2);
            if ($handle !== false)
            {
                if (@ftp_login($handle, $username, $password))
                {
                    if (@ftp_chdir($handle, str_replace($root, "", $this->nbill_admin_base_path) . "/framework/"))
                    {
                        $memory_handle = @fopen("php://memory", "w");
                        @fwrite($memory_handle, $config_file_contents);
                        @ftp_fput($handle, "nbill.config.php", $memory_handle, FTP_ASCII);
                        @fclose($memory_handle);
                    }
                }
                @ftp_close($handle);
            }
        }
    }

    /**
    * If the CMS holds any extra data about the user, return it in an associative array
    * The key for each value will be matched to core profile field names (without the NBILL_CORE_ prefix)
    * @param int $user_id
    */
    public function load_cms_user_profile($user_id)
    {
        return array();
    }
}

/**
* This class is used to indicate the table and column names to use for the relevant CMS tables
*/
class cms_database_enumerator
{
    //Access Levels
    public $table_user_groups;
    public $column_acl_id;
    public $column_acl_name;

    //User Groups
    public $table_user_group;
    public $column_user_group_id;
    public $column_user_group_name;
    public $column_user_group_parent_id;
    public $column_user_group_left;
    public $column_user_group_right;

    //Users
    public $table_user;
    public $column_user_id;
    public $column_user_username;
    public $column_user_email;
    public $column_user_gid;
    public $column_user_name;
    public $column_user_password;
    public $column_block;

    //Super Admin GID (for exclusion from client import)
    public $super_admin_gid;
    //Minimum access level for accessing admin features
    public $manager_gid;
    //Default registered user GID
    public $registered_gid;
}

/**
* Holds information about the currently logged in user
*/
class nb_user
{
    public $id;
    public $gid; //Needed for Joomla 1.5
    public $group_name; //Needed for Joomla 1.5
    public $groups = array();
    public $username;
    public $name;
    public $email;
    public $password; //Usually hashed
    public $first_name; //From nBill Contact record, if known
    public $last_name; //From nBill Contact record, if known

    function __construct($user_id = null)
    {
        if ($user_id)
        {
            $this->id = intval($user_id);
        }
    }
}