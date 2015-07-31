<?php
/**
* Interop Class File for Joomla 1.5
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* This class provides interop functions specific to Joomla 1.5.x.
*
* @package nBill Framework Interop
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_interop_joomla_1_5 extends nbf_interop
{
    /** @var string Name of CMS (for display in error reports) */
    public $cms_name = "Joomla!";
    /** @var string Version number of CMS (for display in error reports) */
    public $cms_version = "1.5.x";
    /** @var boolean Whether or not to hide the FTP details on the global config screen (pick up details from CMS instead) */
    public $hide_ftp_details = true;

    /**
    * Set records per page
    */
    protected function initialise()
    {
        parent::initialise();
        $this->records_per_page = $this->read_config_file_value("list_limit");
    }

    protected function construct_end()
    {
        //Correct the admin menu item, if necessary (only really needed if custom branding is in place)
        if (class_exists("nbill_custom_branding") && nbill_custom_branding::$product_name == NBILL_BRANDING_NAME)
        {
            $nb_database = $this->database;
            $nb_database->setQuery("SELECT `id` FROM `#__components` WHERE `option` = 'com_nbill'");
            $component_id = intval($nb_database->loadResult());
            if ($component_id)
            {
                $nb_database->setQuery( "UPDATE `#__components` SET `admin_menu_img` = '../administrator/components/com_nbill/logo-icon-16.gif', `name` = '" . NBILL_BRANDING_NAME . "', `admin_menu_alt` = '" . NBILL_BRANDING_NAME . "', `link` = 'option=" . NBILL_BRANDING_COMPONENT_NAME . "', `admin_menu_link` = 'option=" . NBILL_BRANDING_COMPONENT_NAME . "' WHERE `id` = $component_id");
                $nb_database->query();
            }
        }
    }

    /**
    * Tell Mambo (or Joomla! 1.0) about the new name
    * @param mixed $custom_component_name
    */
    public function register_custom_component_name($product_name, $custom_component_name, $company_name, $product_website)
    {
        //Amend the URL of any menu links for the component, and the title of the main admin menu item
        $nb_database = $this->database;
        $nb_database->setQuery("UPDATE #__components SET `link` = REPLACE(`link`, 'option=com_nbill', 'option=" . $custom_component_name . "'), `admin_menu_link` = REPLACE(`admin_menu_link`, 'option=com_nbill', 'option=" . $custom_component_name . "')");
        $nb_database->query();
        $nb_database->setQuery("UPDATE #__components SET `name` = '$product_name', `admin_menu_alt` = '$product_name' WHERE `admin_menu_img` = '../administrator/components/com_nbill/logo-icon-16.gif'");
        $nb_database->query();
        $nb_database->setQuery("UPDATE #__menu SET `link` = REPLACE(`link`, 'option=com_nbill', 'option=" . $custom_component_name . "')");
        $nb_database->query();
        parent::register_custom_component_name($product_name, $custom_component_name, $company_name, $product_website);
    }

    /**
    * Clean up any old branding info
    */
    public function unregister_custom_component_name($db_component_names)
    {
        $nb_database = $this->database;

        $nb_database->setQuery("SELECT custom_component_names FROM #__nbill_license WHERE id = 1");
        $db_component_names = explode(",", $nb_database->loadResult());
        if ($db_component_names)
        {
            foreach ($db_component_names as $db_component_name)
            {
                if (strlen(trim($db_component_name)) > 0)
                {
                    //Amend the URL of any menu links for the component, and the title of the main admin menu item
                    $nb_database->setQuery("UPDATE #__components SET `link` = REPLACE(`link`, 'option=" . $db_component_name . "', 'option=com_nbill'), `admin_menu_link` = REPLACE(`admin_menu_link`, 'option=" . $db_component_name . "', 'option=com_nbill')");
                    $nb_database->query();
                    $nb_database->setQuery("UPDATE #__components SET `name` = 'nBill', `admin_menu_alt` = 'nBill' WHERE `admin_menu_img` = '../administrator/components/com_nbill/logo-icon-16.gif'");
                    $nb_database->query();
                    $nb_database->setQuery("UPDATE #__menu SET `link` = REPLACE(`link`, 'option=" . $db_component_name . "', 'option=com_nbill')");
                    $nb_database->query();
                }
            }
        }
        parent::unregister_custom_component_name($db_component_names);
    }

    public function set_cms_language($language)
    {
        //Joomla will not allow us to get at the value without using their API
        $lang =& JFactory::getLanguage();
        $lang->setLanguage($language);
        $lang->load();
    }

    /**
    * Record the table and column names for the CMS database entities that are required by nBill
    */
    protected function set_cms_database_enum()
    {
        parent::set_cms_database_enum();
        $this->cms_database_enum->column_user_group_id = "id";
    }

    public function use_gzip()
    {
        return false; //Doesn't seem to work in J1.5!
    }

    /**
    * Return the group name for the given group id
    * @param int $gid
    * @return The group name
    */
    public function get_user_group_name($gid)
    {
        $nb_database = nbf_cms::$interop->database;
        $sql = "SELECT `name` FROM #__core_acl_aro_groups WHERE id = $gid";
        $nb_database->setQuery($sql);
        return $nb_database->loadResult();
    }

    /**
    * Call the user subscription plugin
    */
    public function check_account_expiry()
    {
        //Just try including the mambot file, if it exists, then call the function
        if (file_exists($this->site_base_path . "/plugins/system/nbill_user_subscription.php"))
        {
            include_once($this->site_base_path . "/plugins/system/nbill_user_subscription.php");
        }
    }

    /**
    * Parse the configuration file to get the required value. As we are avoiding the Joomla API
    * (at the unreasonable request of the Joomla developers), we have to parse the config file
    * instead of just loading the class and reading the static members.
    * @param string $key Name of the setting to retrieve (do not prefix with $)
    * @return string The value from the config file, or NULL if no value found
    */
    protected function read_config_file_value($key)
    {
        $ret_val = null;

        $config_file = $this->site_base_path . "/configuration.php";
        require_once($this->nbill_admin_base_path . "/framework/nbill.config.php");
        if (isset(nbf_config::$cms_config_file) && @nbf_config::$cms_config_file != '[DEFAULT]')
        {
            if (file_exists(nbf_config::$cms_config_file))
            {
                $config_file = nbf_config::$cms_config_file;
            }
        }

        if (file_exists($config_file))
        {
            //Read line by line as that makes it easier to parse the results
            $joomla_config = file($config_file);
            foreach ($joomla_config as $config_line)
            {
                $key_pos = nbf_common::nb_strpos(nbf_common::nb_strtolower($config_line), "\$" . $key);
                $key_found = false;
                if ($key_pos !== false)
                {
                    switch (nbf_common::nb_substr($config_line, $key_pos + nbf_common::nb_strlen($key) + 1, 1))
                    {
                        case " ":
                        case "\t":
                        case "=":
                        case "\n":
                        case "\r":
                            $key_found = true;
                            break;
                    }
                    if ($key_found)
                    {
                        $quote_pos = nbf_common::nb_strpos($config_line, "'", $key_pos + nbf_common::nb_strlen($key));
                        if ($quote_pos === false)
                        {
                            //Try double quotes instead
                            $quote_pos = nbf_common::nb_strpos($config_line, '"', $key_pos + nbf_common::nb_strlen($key));
                        }
                        if ($quote_pos !== false)
                        {
                            $end_quote_pos = nbf_common::nb_strpos($config_line, "'", $quote_pos + 1);
                            if ($end_quote_pos) {
                                $loopcount = 0;
                                while (substr($config_line, $end_quote_pos - 1, 1) == '\\' && $loopcount < 10) {
                                    $end_quote_pos = nbf_common::nb_strpos($config_line, "'", $end_quote_pos + 1);
                                    $loopcount++;
                                }
                            }
                            if ($end_quote_pos === false)
                            {
                                $end_quote_pos = nbf_common::nb_strpos($config_line, '"', $quote_pos + 1);
                                if ($end_quote_pos) {
                                    $loopcount = 0;
                                    while (substr($config_line, $end_quote_pos - 1, 1) == '\\' && $loopcount < 10) {
                                        $end_quote_pos = nbf_common::nb_strpos($config_line, '"', $end_quote_pos + 1);
                                        $loopcount++;
                                    }
                                }
                            }
                            if ($end_quote_pos !== false)
                            {
                                $ret_val = html_entity_decode(nbf_common::nb_substr($config_line, $quote_pos + 1, $end_quote_pos - ($quote_pos + 1)), ENT_COMPAT | 0, $this->char_encoding);
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $ret_val;
    }

    /**
    * Work out which language is in use. If $_SESSION contains a user object with a
    * valid language parameter, use that. Otherwise, load the value from the components
    * table (default language is stored in the params setting for the language component)
    * @return string Language code in format ll-CC where ll is the 2-character ISO 639-1 language code and CC is the 2 character ISO 3166 country code.
    */
    protected function get_language()
    {
        $lang = "";

        if ($this->cms_version == "1.5.x") //1.6 comes in here as well
        {
            //Are we running within the administrator folder?
            $admin = (nbf_common::nb_strpos(nbf_common::get_requested_page(true), $this->live_site . "/administrator") !== false);
            $user_param_name = $admin ? "admin_language=" : "language=";
            $default_param_name = $admin ? "administrator=" : "site=";

            //Check for user-specific language parameter in session for this login
            if (!$lang &&
                isset($_SESSION["__default"]) && isset($_SESSION["__default"]["registry"]) &&
                isset($_SESSION['__default']['registry']->_registry) &&
                isset($_SESSION["__default"]["registry"]->_registry['application']) &&
                isset($_SESSION["__default"]["registry"]->_registry['application']['data']) &&
                isset($_SESSION["__default"]["registry"]->_registry['application']['data']->lang))
            {
                $lang = $_SESSION["__default"]["registry"]->_registry['application']['data']->lang;
            }

            if (!$lang)
            {
                //Check for user-specific language parameter in session for this user
                if (isset($_SESSION["__default"]) && isset($_SESSION["__default"]["user"]))
                {
                    $user_params = explode("\n", $_SESSION["__default"]["user"]->params);
                    foreach ($user_params as $user_param)
                    {
                        if (nbf_common::nb_strpos($user_param, $user_param_name) !== false)
                        {
                            $lang = nbf_common::nb_substr($user_param, nbf_common::nb_strpos($user_param, $user_param_name) + nbf_common::nb_strlen($user_param_name));
                            break;
                        }
                    }
                }
            }
            if (!$lang)
            {
                //Load default language from database (components table)
                $sql = "SELECT `params` FROM #__components WHERE `option` = 'com_languages'";
                $this->database->setQuery($sql);
                $params = $this->database->loadResult();
                if ($params)
                {
                    $params = explode("\n", $params);
                    foreach ($params as $param)
                    {
                        if (nbf_common::nb_strpos($param, $default_param_name) !== false)
                        {
                            $lang = nbf_common::nb_substr($param, nbf_common::nb_strpos($param, $default_param_name) + nbf_common::nb_strlen($default_param_name));
                            break;
                        }
                    }
                }
            }
        }

        if (!$lang)
        {
            $lang = parent::get_language();
        }

        return $lang;
    }

    /**
    * Return default language for website front end
    */
    public function get_frontend_language()
    {
        $lang = "";
        $sql = "SELECT `params` FROM #__components WHERE `option` = 'com_languages'";
        $this->database->setQuery($sql);
        $params = $this->database->loadResult();
        if ($params)
        {
            $params = explode("\n", $params);
            foreach ($params as $param)
            {
                if (nbf_common::nb_strpos($param, "site=") !== false)
                {
                    $lang = substr($param, nbf_common::nb_strpos($param, "site=") + 5);
                    break;
                }
            }
        }
        return $lang;
    }

    /**
    * Set the URL prefixes that tell Joomla 1.5 to direct calls to nBill, and record the
    * URL path to the component files (for including the admin stylesheet and referencing images).
    */
    protected function set_url_prefixes()
    {
        $this->cms_home_page = "index.php";
        $this->admin_page_prefix = "index.php?option=" . NBILL_BRANDING_COMPONENT_NAME;
        $this->admin_popup_page_prefix = "index.php?option=" . NBILL_BRANDING_COMPONENT_NAME . "&nbill_popup=1";
        $this->site_page_prefix = "index.php?option=" . NBILL_BRANDING_COMPONENT_NAME;
        $this->site_popup_page_prefix = "index.php?option=" . NBILL_BRANDING_COMPONENT_NAME . "&nbill_popup=1";
        $this->nbill_admin_url_path = $this->live_site . "/administrator/components/com_nbill";
        $this->nbill_site_url_path = $this->live_site . "/components/com_nbill";
        $this->admin_component_uninstaller = "index.php?option=com_installer&task=manage&type=components";
        $this->user_editor_url = "index.php?option=com_users&view=user&task=edit&cid[]=%s";
    }

    /**
    * Get the site name from the config file, if available.
    * @return string The site name as recorded in the configuration file
    */
    function get_site_name()
    {
        $site_name = stripslashes($this->read_config_file_value("sitename"));
        if ($site_name)
        {
            return $site_name;
        }
        else
        {
            return parent::get_site_name();
        }
    }

    /**
    * Get the database connection settings from the configuration file
    * @return nbf_db_connection
    */
    public function get_db_connection_settings()
    {
        $host = $this->read_config_file_value("host");
        $port = null;
        if (nbf_common::nb_strpos($host, ":") !== false)
        {
            $host_parts = explode(":", $host);
            if (count($host_parts) >= 2)
            {
                $host = $host_parts[0];
                $port = $host_parts[1];
            }
        }
        $database_name = $this->read_config_file_value("db");
        $username = $this->read_config_file_value("user");
        $password = stripslashes($this->read_config_file_value("password"));
        $prefix = $this->read_config_file_value("dbprefix");
        if (trim($database_name))
        {
            return new nbf_db_connection($host, $database_name, $username, $password, $prefix, $port);
        }
    }

    /**
    * Add Joom!Fish support, if applicable
    */
    public function install_tasks()
    {
        parent::install_tasks(); //Add icon to administrator menu option
        if (file_exists($this->site_base_path . '/administrator/components/com_joomfish/config.joomfish.php') || file_exists($this->site_base_path . '/administrator/components/com_joomfish/joomfish.php'))
        {
            @copy($this->nbill_admin_base_path . "/translation/nbill_additional_links.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_additional_links.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_discounts.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_discounts.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_order_form.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_order_form.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_order_form_fields.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_order_form_fields.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_order_form_fields_options.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_order_form_fields_options.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_order_form_pages.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_order_form_pages.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_payment_plans.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_payment_plans.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_product.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_product.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_product_category.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_product_category.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_profile_fields.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_profile_fields.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_profile_fields_options.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_profile_fields_options.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_reminders.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_reminders.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_shipping.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_shipping.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_tax.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_tax.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_vendor.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_vendor.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_xref_country_codes.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_xref_country_codes.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_xref_eu_country_codes.xml", $this->site_base_path . "/administrator/components/com_joomfish/contentelements/nbill_xref_eu_country_codes.xml");
        }
    }

    /**
    * Remove Joom!Fish files, if applicable
    */
    public function uninstall_tasks()
    {
        parent::uninstall_tasks();
        if (@file_exists($this->site_base_path . "/administrator/components/com_joomfish/contentelements/"))
        {
            $old_joomfish_files = scandir(nbf_cms::$interop->site_base_path . "/administrator/components/com_joomfish/contentelements/");
            foreach ($old_joomfish_files as $old_joomfish_file)
            {
                if (substr(basename($old_joomfish_file), 0, 7) == "netinv_" || substr(basename($old_joomfish_file), 0, 6) == "nbill_" || nbf_common::nb_strtolower(substr(basename($old_joomfish_file), 0, 17)) == "translationnbill_")
                {
                    @unlink(nbf_cms::$interop->site_base_path . "/administrator/components/com_joomfish/contentelements/" . $old_joomfish_file);
                }
            }
        }
    }

    public function add_html_header($content)
    {
        //The only way to do  this is to use the Joomla API
        if (class_exists('JFactory')) { //For unit testing it doesn't matter
            $doc = @JFactory::getDocument();
            @$doc->addCustomTag($content);
        }

        //In case we are overriding the CMS (eg. on the form editor) and have output our own head tag, check the output buffers...

        //Read all the output buffers into memory
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

        //Go through each one and find the one that contains the head section (or already contains $content)
        $buffer_count = count($buffers);
        $header_added = false;
        for ($i = 0; $i < $buffer_count; $i++)
        {
            if (strpos($buffers[$i], $content) !== false)
            {
                //We have already successfully added this content to the head section
                $header_added = true;
            }
            else if (!$header_added && strrpos($buffers[$i], "</head>") !== false)
            {
                //Add custom head tag
                $buffers[$i] = substr_replace($buffers[$i], "$content</head>", strrpos($buffers[$i], "</head>"), 7);
                $header_added = true;
                break;
            }
        }
        //Echo them in back in the right order
        $fatal_handler = "fatal_error_handler" . (defined('NBILL_ADMIN') ? '_admin' : '');
        if (function_exists($fatal_handler)) {
            ob_start($fatal_handler);
        }
        for ($j = $buffer_count - 1; $j >= 0; $j--)
        {
            ob_start();
            echo $buffers[$j];
        }
    }

    public function cms_editor_supported()
    {
        switch (JFactory::getEditor()->get('_name'))
        {
            case "tinymce":
            case "jce": //This is almost identical to tinymce
            case "ckeditor":
            case "artofeditor": //This is almost identical to ckeditor
            case "codemirror":
                return true;
            default:
                return false;
        }
    }

    public function init_editor($force_nicedit = false)
    {
        require_once($this->nbill_admin_base_path . "/framework/nbill.config.php");
        if (nbf_config::$editor == "none" || nbf_config::$editor == "nicEdit" || $force_nicedit || !$this->cms_editor_supported())
        {
            return parent::init_editor($force_nicedit);
        }
        else
        {
            $editor = JFactory::getEditor();
            echo $editor->initialise();
        }
    }

    public function render_editor($element_name, $element_id, $value, $attributes = "", $force_nicedit = false)
    {
        require_once($this->nbill_admin_base_path . "/framework/nbill.config.php");
        if (nbf_config::$editor == "none" || nbf_config::$editor == "nicEdit" || $force_nicedit || !$this->cms_editor_supported())
        {
            return parent::render_editor($element_name, $element_id, $value, $attributes, $force_nicedit);
        }
        else
        {
            $editor = JFactory::getEditor();
            return $editor->display($element_name, $value, null, null, null, null, false);
        }
    }

    public function get_editor_contents($element_id, $element_name = '', $force_nicedit = false)
    {
        $element_name = $element_name == '' ? $element_id : $element_name;
        require_once($this->nbill_admin_base_path . "/framework/nbill.config.php");
        if (nbf_config::$editor == "none" || $force_nicedit || nbf_config::$editor == "nicEdit" || !$this->cms_editor_supported())
        {
            return parent::get_editor_contents($element_id, $element_name);
        }
        else
        {
            $editor = JFactory::getEditor();
            $result = $editor->save($element_name);
            if (trim($result) == '<span> </span>')
            {
                $result = ""; //Workaround for ckeditor
            }
            $result = str_replace('"', "'", $result); //to allow inline use
            return $result;
        }
    }

    /**
    * Set or return information about the currently logged in user
    * @return nb_user Details of the currently logged in user (or user whose ID is passed in)
    */
    public function get_user($user_id = null)
    {
        $nb_database = $this->database;
        $return_user = false;
        $this_user = new nb_user();
        if ($user_id == null)
        {
            if (isset($_SESSION['__default']) && isset($_SESSION['__default']['user']))
            {
                $user_id = intval(@$_SESSION['__default']['user']->id);
            }
        }
        else
        {
            $return_user = true;
        }
        if ($user_id)
        {
            $sql = "SELECT #__core_acl_aro_groups.id AS gid, #__core_acl_aro_groups.name AS group_name,
                    #__users.name, #__users.username, #__users.email, #__users.id
                    FROM #__core_acl_aro_groups
                    INNER JOIN #__users ON #__core_acl_aro_groups.id = #__users.gid
                    WHERE #__users.id = $user_id";
            $nb_database->setQuery($sql);
            $this_user = null;
            $nb_database->loadObject($this_user);
            $this_user->id = intval($this_user->id);
            $this_user->gid = intval($this_user->gid);
            $this_user->groups[$this_user->gid] = $this_user->group_name;
            //Try to find first and last name on a contact record
            $contact = null;
            $sql = "SELECT first_name, last_name FROM #__nbill_contact WHERE user_id = " . $this_user->id;
            $nb_database->setQuery($sql);
            $nb_database->loadObject($contact);
            if ($contact)
            {
                $this_user->first_name = $contact->first_name;
                $this_user->last_name = $contact->last_name;
            }
        }
        if ($return_user)
        {
            return $this_user;
        }
        $this->user = $this_user;
    }

    public function get_user_registration_url()
    {
        $sql = "SELECT id FROM #__components WHERE params LIKE '%allowUserRegistration=1%'";
        $this->database->setQuery($sql);
        return $this->database->loadResult() ? "index.php?option=com_user&view=register" : "";
    }

    /**
    * Create a new user record in the CMS
    * @param string $name Real name of person
    * @param string $username Username
    * @param string $password Plain text password
    * @param string $email Email address
    * @return integer Returns the user ID or -1 on failure
    */
    function create_user($name, $username, $password, $email)
    {
        $nb_database = $this->database;

        //Check that Joomla/Mambo would not complain about these user details
        $now = nbf_common::nb_date("Y-m-d h:i:s", nbf_common::nb_time());

        //Restrict username length if applicable
        $username_max_length = 25;
        $sql = "SHOW COLUMNS FROM #__users";
        $nb_database->setQuery($sql);
        $columns = $nb_database->loadObjectList();
        if ($columns)
        {
            foreach ($columns as $column)
            {
                if ($column->Field == "username")
                {
                    $username_max_length = intval(nbf_common::nb_substr($column->Type, 8, nbf_common::nb_strlen($column->Type) - 9));
                }
            }
        }
        if (nbf_common::nb_strlen($username) > $username_max_length)
        {
            $username = substr($username, 0, $username_max_length);
        }
        if (nbf_common::nb_strlen($username) < 3)
        {
            nbf_globals::$message = sprintf(NBILL_INVALID_CHARS_IN_FIELD, NBILL_INVALID_CHARS_USERNAME, 2);
            return -1;
        }
        if (preg_match("/[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+]/i", $username) || nbf_common::nb_strlen($username) < 3)
        {
            nbf_globals::$message = sprintf(NBILL_INVALID_CHARS_IN_FIELD, NBILL_INVALID_CHARS_USERNAME, 2);
            return -1;
        }

        //Restrict password to 50 characters
        if ( nbf_common::nb_strlen($password) > 50 )
        {
            $password = substr( $password, 0, 50 );
        }

        //Validate e-mail address
        if ((nbf_common::nb_strlen($email) == 0) || (preg_match("/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $email) == false))
        {
            nbf_globals::$message = NBILL_EMAIL_INVALID;
            return -1;
        }

        //Make sure username does not already exist
        $sql = "SELECT id FROM #__users WHERE username = '$username'";
        if ($this->user->id)
        {
            $sql .= " AND id != " . intval($this->user->id);
        }
        $nb_database->setQuery($sql);
        $existing_id = $nb_database->loadResult();
        if ($existing_id && $existing_id != $this->user->id)
        {
            nbf_globals::$message = NBILL_USERNAME_EXISTS;
            return -1;
        }

        //Make sure the email address is not already registered
        $sql = "SELECT id FROM #__users WHERE email = '$email'";
        if ($this->user->id)
        {
            $sql .= " AND id != " . intval($this->user->id);
        }
        $nb_database->setQuery($sql);
        $existing_id = $nb_database->loadResult();
        if ($existing_id && $existing_id != $this->user->id)
        {
            nbf_globals::$message = NBILL_USER_EMAIL_EXISTS;
            return -1;
        }

        $nb_database->insertid(); //Clear any previous value that may be lurking

        $pwd_hash = $this->get_password_hash($password);

        //Get default user group
        $sql = "SELECT default_user_groups FROM #__nbill_configuration WHERE id = 1";
        $nb_database->setQuery($sql);
        $default_gid = intval($nb_database->loadResult());
        if (!$default_gid || $this->demo_mode)
        {
            $default_gid = $this->cms_database_enum->registered_gid;
        }

        $sql = "INSERT INTO #__users (name, username, email, password, usertype, block, sendEmail, gid, registerDate, lastvisitDate, activation, params)
                VALUES ('" . $nb_database->getEscaped($name) . "', '$username', '$email', '" . $pwd_hash . "', 'Registered', 0, 0, $default_gid, '$now', '$now', '', 'editor=\nexpired=\nexpired_time=')";
        $nb_database->setQuery($sql);
        $nb_database->query();
        if (nbf_common::nb_strlen($nb_database->_errorMsg) == 0)
        {
            $user_id = $nb_database->insertid();
        }
        else
        {
            return -1;
        }

        //Check that we have a new user id
        if ($user_id < 1)
        {
            return -1;
        }
        else
        {
            //Insert ARO Records
            $sql = "INSERT INTO #__core_acl_aro (section_value, name, value) VALUES ('users', '" . $nb_database->getEscaped($name) . "', " . intval($user_id) . ")";
            $nb_database->setQuery($sql);
            $nb_database->query();
            $aro_id = $nb_database->insertid();
            $sql = "INSERT INTO #__core_acl_groups_aro_map (group_id, aro_id) VALUES ($default_gid, " . intval($aro_id) . ")";
            $nb_database->setQuery($sql);
            $nb_database->query();

            //Check whether Community Builder is present, if so, add there as well (TODO: Field mapping with CB)
            $sql = "SHOW TABLES LIKE '" . nbf_cms::$interop->db_connection->prefix . "comprofiler'";
            $nb_database->setQuery($sql);
            if ($nb_database->loadResult())
            {
                $sql = "INSERT INTO #__comprofiler (id, user_id) VALUES (" . intval($user_id) . ", " . intval($user_id) . ")";
                $nb_database->setQuery($sql);
                @$nb_database->query();
            }

            nbf_common::fire_event("user_created", array("id"=>$user_id, "username"=>$username, "email"=>$email));
            return intval($user_id);
        }
    }

    /**
    * Change the access level user group for the given user to the new group id
    * @param integer $user_id ID of the user to upgrade or downgrade
    * @param integer $new_level Group ID (gid) of the new user group
    */
    function change_user_group($user_id, $new_group)
    {
        if ($this->demo_mode)
        {
            if ($this->compare_user_groups($this->cms_database_enum->registered_gid, $new_group) == 2)
            {
                die("User group privileges cannot be escalated in demo mode");
            }
        }

        $nb_database = $this->database;
        $user_id = intval($user_id);
        $new_group = intval($new_group);

        nbf_common::fire_event("user_group_changed", array("user_id"=>$user_id, "new_group"=>$new_group, "new_level"=>$new_group)); //new_level deprecated

        $sql = "SELECT name FROM #__core_acl_aro_groups WHERE id = $new_group";
        $nb_database->setQuery($sql);
        $groupname = $nb_database->loadResult();

        $sql = "SELECT id AS aro_id FROM #__core_acl_aro WHERE value = " . $user_id;
        $nb_database->setQuery($sql);
        $aro_id = $nb_database->loadResult();

        $sql = "UPDATE #__core_acl_groups_aro_map SET group_id = $new_group WHERE aro_id = $aro_id";
        $nb_database->setQuery($sql);
        $nb_database->query();

        $sql = "UPDATE #__users SET usertype = '$groupname', gid = $new_group WHERE id = $user_id";
        $nb_database->setQuery($sql);
        $nb_database->query();

        if ($this->user->id == $user_id) //We are logged in...
        {
            //Update the session variables
            @$_SESSION['__default']['user']->gid = $new_level;
            switch ($new_level)
            {
                case 19:
                case 20:
                case 21:
                case 23:
                case 24:
                case 25:
                    //Belongs to the 'special' access level
                    @$_SESSION['__default']['user']->aid = 2;
                    break;
                default:
                    @$_SESSION['__default']['user']->aid = 1;
                    break;
            }
            @$_SESSION['__default']['user']->usertype = $groupname;
        }
    }

    /**
    * Add the user to the given group (this CMS does not support multiple groups per user, so it just changes the group)
    * @param integer $user_id ID of the user to add
    * @param integer $new_group Group ID (gid) of the group to add the user to
    */
    function add_user_to_group($user_id, $new_group)
    {
        return $this->change_user_group($user_id, $new_group);
    }

    /**
    * Remove the user from one group, and add them to another
    * @param integer $user_id ID of the user to move
    * @param integer $current_group Group ID (gid) of the old group
    * @param integer $new_group Group ID (gid) of the new group
    */
    function replace_user_group($user_id, $current_group, $new_group)
    {
        return $this->change_user_group($user_id, $new_group);
    }

    /**
    * Unable to find a way to do this in J1.5! So far...
    * For now, just update the @$_SESSION['__default']['user'] object (that's all we need so far)
    * @param nb_user $user Information about the user - typically just the ID is needed, but if required by the CMS,
    * the other data can be updated in the session variables instead of actually logging out and back in.
    * @param string $url URL to redirect to after logging back in (if applicable)
    */
    public function log_out_then_in_again($user, $url = "")
    {
        //Update the session variables
        @$_SESSION['__default']['user']->username = $user->username;
        if (nbf_common::nb_strlen($user->email) > 0)
        {
            @$_SESSION['__default']['user']->email = $user->email;
        }
        if (nbf_common::nb_strlen($user->name) > 0)
        {
            @$_SESSION['__default']['user']->name = $user->name;
        }
        if (nbf_common::nb_strlen($user->password) > 0)
        {
            @$_SESSION['__default']['user']->password = $user->password;
        }

        if ($url)
        {
            nbf_common::redirect($url);
            exit;
        }
    }

    public function log_out()
    {
        unset($_SESSION['__default']['user']);
        if(isset(JFactory::$session))
        {
            @JFactory::$session=null;
        }
    }

    /**
    * Get an associative array of parameters for the user subscription mambot/plugin
    * @return array The parameters as an associative array
    */
    public function get_account_expiry_params()
    {
        //First, set the default values
        $param_assoc['js_refresh'] = 0;
        $param_assoc['reload'] = 1;

        //Now load the values from the database and parse
        $nb_database = $this->database;
        $sql = "SELECT params FROM #__plugins WHERE folder = 'system' AND element = 'nbill_account_expiry'";
        $nb_database->setQuery($sql);
        $params = $nb_database->loadResult();
        $param_array = explode("\n", $params);
        if (count($param_array) > 0)
        {
            foreach ($param_array as $param)
            {
                $this_param = explode("=", $param);
                if (count($this_param) == 2)
                {
                    $param_assoc[$this_param[0]] = $this_param[1];
                }
            }
        }

        return $param_assoc;
    }

    /**
    * Returns 1 if level_a is higher, 2 if level_b is higher, or 0 if they are equal
    * @param int $level_a Group ID of first access level
    * @param int $level_b Group ID of second access level
    * @return int Comparison value: 1 if level_a is higher, 2 if level_b is higher, or 0 if they are equal
    */
    function compare_user_groups($level_a, $level_b)
    {
        $nb_database = $this->database;

        $frontend = 29;
        $backend = 30;

        //Get Public Frontend, Public Backend, level a, and level b from the group tree
        $sql = "SELECT id AS group_id, lft, rgt FROM #__core_acl_aro_groups WHERE
                    id IN ($frontend, $backend, $level_a, $level_b)";
        $nb_database->setQuery($sql);
        $groups = $nb_database->loadAssocList("group_id");

        if ($groups && isset($groups[$frontend]) && isset($groups[$backend]))
        {
            //Find out which branch each level is on (frontend or backend)
            $a_frontend = $groups[$level_a]['lft'] > $groups[$frontend]['lft'] && $groups[$level_a]['rgt'] < $groups[$frontend]['rgt'];
            $b_frontend = $groups[$level_b]['lft'] > $groups[$frontend]['lft'] && $groups[$level_b]['rgt'] < $groups[$frontend]['rgt'];
            $a_backend = $groups[$level_a]['lft'] > $groups[$backend]['lft'] && $groups[$level_a]['rgt'] < $groups[$backend]['rgt'];
            $b_backend = $groups[$level_b]['lft'] > $groups[$backend]['lft'] && $groups[$level_b]['rgt'] < $groups[$backend]['rgt'];

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
                $min = $groups[$frontend]['lft'];
                $max = $groups[$frontend]['rgt'];
            }
            else if ($a_backend && $b_backend)
            {
                $min = $groups[$backend]['lft'];
                $max = $groups[$backend]['rgt'];
            }
            $sql = "SELECT *, id AS group_id FROM #__core_acl_aro_groups WHERE lft > $min AND lft < $max ORDER BY lft ASC";
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
                   while (count($right) > 0 && $right[count($right)-1] < $row->rgt)
                   {
                       array_pop($right);
                   }
               }

               //Now we know how deep we are in the tree, check if we have hit one of our targets
               if ($row->group_id == $level_a)
               {
                   $a_pos = count($right);
               }
               if ($row->group_id == $level_b)
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

               $right[] = $row->rgt;
           }
        }
        return 0; //Assume Equality
    }

    /**
    * Delete all users in the given array of user IDs
    * @param mixed $user_id_array
    */
    public function delete_users($user_id_array, $cms_users_deleted = false)
    {
        if (!$cms_users_deleted) {
            $nb_database = $this->database;
            foreach ($user_id_array as &$user_id)
            {
                $user_id = intval($user_id);
                nbf_common::fire_event("user_deleted", array("user_id"=>$user_id));
            }
            reset($user_id_array);

            foreach ($user_id_array as &$user_id)
            {
                $sql = "SELECT id FROM #__core_acl_aro WHERE `value` = $user_id";
                $nb_database->setQuery($sql);
                $aro_id = intval($nb_database->loadResult());
                if ($aro_id)
                {
                    $sql = "DELETE FROM #__core_acl_groups_aro_map WHERE aro_id = $aro_id";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                    $sql = "DELETE FROM #__core_acl_aro WHERE id = $aro_id";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                    $sql = "DELETE FROM #__users WHERE id = $user_id";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                }
            }
            reset($user_id_array);
        }

        parent::delete_users($user_id_array, true);
    }

    /**
    * Check whether or not the user subscription plugin is installed and published
    */
    public function user_sub_plugin_present()
    {
        $sql = "SELECT published FROM #__plugins WHERE name LIKE '%nBill%' AND element = 'nbill_user_subscription'";
        $this->database->setQuery($sql);
        return $this->database->loadResult();
    }

    /**
    * Returns a list of menu titles as an associative array keyed on the menu name. This list is used
    * to facilitate creating a new menu item pointing to an order form. If an empty array is returned
    * by an override of this function, the option to create a menu item will not be available to the user.
    */
    public function get_menu_list()
    {
        $nb_database = $this->database;
        $sql = "SELECT menutype, title FROM `#__menu_types`";
        $nb_database->setQuery($sql);
        $menus = $nb_database->loadObjectList();
        $menu_types = array();
        foreach ($menus as $menu)
        {
            $menu_types[$menu->menutype] = $menu->title;
        }
        return $menu_types;
    }

    /**
    * Create a new menu item in the CMS, pointing to the order form
    * @param integer $form_id ID of form to link to
    * @param string $name Caption for the menu item
    * @param string $menu Name of the menu to add the link to
    * @param string $action The value to use for the action parameter in the URL
    * @return integer ID of the newly created menu item
    */
    public function create_menu_item($form_id, $name, $menu, $action = 'orders')
    {
        $nb_database = $this->database;

        if (!$action)
        {
            $action = 'orders';
        }

        $alias = nbf_common::nb_strtolower(str_replace(" ", "-", $name));
        $sql = "INSERT INTO #__menu (menutype, name, alias, link, type, published, ordering) VALUES
                    ('$menu', '$name', '$alias', '" . $this->site_page_prefix . "&action=$action&task=order&cid=$form_id" . $this->site_page_suffix . "', 'url', 1, 99);";
        $nb_database->setQuery($sql);
        $nb_database->query();
        $menu_id = $nb_database->insertid();
        if ($menu_id)
        {
            nbf_globals::$message = NBILL_MENU_ITEM_CREATED;
            if (!nbf_common::get_param($_REQUEST, 'nbill_admin_via_fe'))
            {
                $link = "index.php?option=com_menus&menutype=$menu&task=edit&cid[]=$menu_id";
                nbf_globals::$message .= "\n\n<a href=\"$link\">" . NBILL_CLICK_HERE . "</a> " . NBILL_MENU_ITEM_TO_EDIT;
            }
        }
        else
        {
            nbf_globals::$message = NBILL_MENU_ITEM_NOT_CREATED;
        }
        return $menu_id;
    }

    /**
    * Login to the CMS
    * @param string $username Username to login as
    * @param string $password Password for given username
    * @param boolean $remember Whether or not to stay logged in indefinitely
    * @param string $return_url URL to redirect to after logging in, if applicable
    */
    public function login($username, $password, $remember = false, $return_url = "")
    {
        global $mainframe;
        $nb_database = nbf_cms::$interop->database;

        $sql = "SELECT switch_to_ssl FROM #__nbill_configuration WHERE id = 1";
        $nb_database->setQuery($sql);
        $switch_to_ssl = $nb_database->loadResult();
        if ($switch_to_ssl)
        {
            $return_url = str_replace("http://", "https://", $return_url);
        }

        $credentials = array();
        $credentials['username'] = $username;
        $credentials['password'] = $password;
        $options = array();
        $options['remember'] = true; // $remember; //Some mysterious session problem can prevent the login from 'sticking' unless we set this to true
        if ($return_url)
        {
            $options['return'] = $return_url;
        }

        if ($mainframe->login($credentials, $options))
        {
            $this->get_user();
            if ($return_url)
            {
                nbf_common::redirect($return_url);
            }
        }
    }

    /**
    * Return the URL for the lost password feature of the CMS
    */
    public function get_lost_password_link()
    {
        return "index.php?option=com_user&view=reset";
    }

    public function get_logout_link($return = "")
    {
        if (!$return)
        {
            $return = '/';
        }
        return "index.php?option=com_user&view=login&task=logout&return=" . base64_encode($return);
    }

    /**
    * Make sure the username supplied is valid according to any rules imposed by the CMS
    * @param string $username May be truncated if required by CMS
    * @param string $email_as_username Whether or not the email address is being used as the username (prevents truncation)
    * @return boolean Whether the username is valid or not
    */
    public function validate_username(&$username, $email_as_username = false)
    {
        //Restrict username length if not using email address as username
        if (!$email_as_username && nbf_common::nb_strlen($username) > 150)
        {
            $username = substr($username, 0, 150);
        }
        if (nbf_common::nb_strlen($username) < 3)
        {
            return false;
        }
        return true;
    }

    public function process_url($url_to_process)
    {
        return JRoute::_($url_to_process); //We have no choice but to use a Joomla API function call
    }

    /**
    * Update the password for the given user (or the currently logged in user if no user specified)
    * @param string $password The plain text password
    * @param integer $user_id The ID of the user to update (if omitted, the currently logged in user will be updated, if applicable)
    */
    public function update_password($password, $user_id = null)
    {
        if (!$this->demo_mode) {
            $nb_database = $this->database;
            if (!$user_id)
            {
                $user_id = $this->user->id;
            }
            $salt = md5($password);
            $pwd_hash = md5($password . $salt) . ":" . $salt;
            $sql = "UPDATE #__users SET password = '$pwd_hash' WHERE id = " . intval($user_id);
            $nb_database->setQuery($sql);
            $nb_database->query();
            if (nbf_common::nb_strlen(str_replace("*", "", $password)) > 0)
            {
                $this->user->password = nbf_cms::$interop->get_password_hash($password);
            }
        }
    }

    /**
    * Set the email configuration settings from the Joomla configuration file
    */
    public function get_email_config()
    {
        require_once($this->nbill_admin_base_path . "/framework/nbill.config.php");
        if (nbf_config::$mailer == "[CMS]")
        {
            nbf_config::$mailfrom = $this->read_config_file_value("mailfrom");
            nbf_config::$fromname = $this->read_config_file_value("fromname");
            nbf_config::$sendmail = $this->read_config_file_value("sendmail");
            nbf_config::$smtpauth = $this->read_config_file_value("smtpauth");
            nbf_config::$smtpuser = $this->read_config_file_value("smtpuser");
            nbf_config::$smtppass = $this->read_config_file_value("smtppass");
            nbf_config::$smtphost = $this->read_config_file_value("smtphost");
        }
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
    public function get_gzip_config_url()
    {
        //Not really needed in J1.5, but it won't do any harm to support it...
        return "index.php?option=com_config";
    }

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
        $ftp_address = $this->read_config_file_value("ftp_host");
        $port = $this->read_config_file_value("ftp_port");
        $username = $this->read_config_file_value("ftp_user");
        $password = $this->read_config_file_value("ftp_pass");
        $root = $this->read_config_file_value("ftp_root");

        include_once($this->nbill_admin_base_path . "/framework/classes/nbill.file.class.php");
        $use_ftp = true;
        $error_message = "";
        return nbf_file::is_test_file_writable($use_ftp, $error_message);
    }

    public function prompt_for_ftp_details($error_message)
    {
        //Try to redirect to Joomla global config page
        @setcookie("configuration", "server");
        global $mainframe;
        if (method_exists($mainframe, 'enqueueMessage'))
        {
            if (!isset($_SESSION['__default']))
            {
                $_SESSION['__default'] = array();
            }
            if (!isset($_SESSION['__default']['application.queue']))
            {
                $_SESSION['__default']['application.queue'] = array();
            }
            if (!isset($_SESSION['__default']['application.queue']['0']))
            {
                $_SESSION['__default']['application.queue']['0'] = array();
            }
            $_SESSION['__default']['application.queue']['0']['type'] = 'message';
            $_SESSION['__default']['application.queue']['0']['message'] = $error_message;
            nbf_common::redirect("index.php?option=com_config");
        }
        else
        {
            parent::prompt_for_ftp_details($error_message);
        }
    }
}