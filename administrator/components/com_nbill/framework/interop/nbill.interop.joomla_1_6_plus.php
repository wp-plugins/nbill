<?php
/**
* Interop Class File for Joomla 1.6 and above
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

require_once(dirname(__FILE__) . "/nbill.interop.joomla_1_5.php");

/**
* This class provides interop functions specific to Joomla 1.5.x.
*
* @package nBill Framework Interop
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_interop_joomla_1_6_plus extends nbf_interop_joomla_1_5
{
    /** @var string Name of CMS (for display in error reports) */
    public $cms_name = "Joomla!";
    /** @var string Version number of CMS (for display in error reports) */
    public $cms_version = "1.6+";
    /** @var boolean Whether or not a user can belong to more than one user group */
    public $multi_user_group = true;
    /** @var int Value of access column in CMS menu table for public menu items */
    public $public_menu_access = 1;

    protected function initialise()
    {
        parent::initialise();
        $this_version = @$GLOBALS['jversion']->RELEASE;
        if (!$this_version)
        {
            $this_version = @$GLOBALS['version']->RELEASE ? @$GLOBALS['version']->RELEASE : "1.6+";
        }
        $this->cms_version = $this_version;
    }

    protected function construct_end()
    {
        //Correct the admin menu item, if necessary (1.7 does not create the menu item until after installation so we have to wait until first run to correct it)
        $nb_database = $this->database;
        $nb_database->setQuery("SELECT id, img FROM #__menu WHERE (menutype = '_adminmenu' OR menutype = 'main') AND `link` = 'index.php?option=" . NBILL_BRANDING_COMPONENT_NAME . "'");
        $menu_obj = null;
        $nb_database->loadObject($menu_obj);
        if ($menu_obj && $menu_obj->id)
        {
            $nb_database->setQuery( "UPDATE `#__menu` SET `img` = '../administrator/components/com_nbill/logo-icon-16.gif', `alias` = '" . NBILL_BRANDING_NAME . "' WHERE `id` = '$menu_obj->id'");
            $nb_database->query();
        }
    }

    /**
    * Tell Joomla! about the new name
    * @param mixed $custom_component_name
    */
    public function register_custom_component_name($product_name, $custom_component_name, $company_name, $product_website)
    {
        //Add an entry to the extensions table so Joomla will allow access via the custom name
        $nb_database = $this->database;
        $nb_database->setQuery("SELECT `extension_id` FROM #__extensions WHERE `element` = '$custom_component_name'");
        if (!$nb_database->loadResult())
        {
            $nb_database->setQuery("INSERT INTO #__extensions (`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`)
                                    VALUES ('$product_name', 'component', '$custom_component_name', '', 1, 1, 0, 1, '{\"legacy\":false,\"name\":\"$product_name\",\"type\":\"component\"}', '{}', 'nbill_branding_entry')");
            $nb_database->query();
        }

        //Update the manifest for the nBill extension entry so that it appears in the Joomla extension list under the custom name
        $nbill_entry = null;
        $nb_database->setQuery("SELECT extension_id, manifest_cache FROM #__extensions WHERE `type` = 'component' AND `element` = 'com_nbill'");
        $nb_database->loadObject($nbill_entry);
        if ($nbill_entry)
        {
            $manifest = json_decode($nbill_entry->manifest_cache, true);
            if ($manifest && count($manifest) > 0)
            {
                $manifest['name'] = $product_name;
                $manifest['author'] = $company_name;
                $manifest['authorEmail'] = '';
                $manifest['authorUrl'] = $product_website;
                $nb_database->setQuery("UPDATE #__extensions SET manifest_cache = '" . $nb_database->getEscaped(json_encode($manifest)) . "' WHERE `type` = 'component' AND `element` = 'com_nbill'");
                $nb_database->query();
            }
        }

        //Amend the URL of any menu links for the component, and the title of the main admin menu item
        $nb_database->setQuery("UPDATE #__menu SET `link` = REPLACE(`link`, 'index.php?option=com_nbill', 'index.php?option=" . $custom_component_name . "')");
        $nb_database->query();
        $nb_database->setQuery("UPDATE #__menu SET `title` = '$product_name' WHERE `title` = 'nBill' AND `menu_type` = 'main' AND `img` = '../administrator/components/com_nbill/logo-icon-16.gif'");
        $nb_database->query();
        parent::register_custom_component_name($product_name, $custom_component_name, $company_name, $product_website);
    }

    /**
    * Clean up any old branding info
    */
    public function unregister_custom_component_name($db_component_names)
    {
        $nb_database = $this->database;

        //If nBill tables have been deleted, we won't find the custom name, so just wipe them all
        $nb_database->setQuery("DELETE FROM #__extensions WHERE `custom_data` = 'nbill_branding_entry'");
        $nb_database->query();

        $nb_database->setQuery("SELECT custom_component_names FROM #__nbill_license WHERE id = 1");
        $db_component_names = explode(",", $nb_database->loadResult());
        if ($db_component_names)
        {
            foreach ($db_component_names as $db_component_name)
            {
                if (strlen(trim($db_component_name)) > 0)
                {
                    //Update the manifest for the nBill extension entry so that it appears in the Joomla extension list under the default name
                    $nbill_entry = null;
                    $nb_database->setQuery("SELECT extension_id, manifest_cache FROM #__extensions WHERE `type` = 'component' AND `element` = 'com_nbill'");
                    $nb_database->loadObject($nbill_entry);
                    if ($nbill_entry)
                    {
                        $manifest = json_decode($nbill_entry->manifest_cache, true);
                        if ($manifest && count($manifest) > 0)
                        {
                            $manifest['name'] = 'nBill';
                            $manifest['author'] = 'Netshine Software Limited';
                            $manifest['authorEmail'] = '';
                            $manifest['authorUrl'] = 'www.nbill.co.uk';
                            $nb_database->setQuery("UPDATE #__extensions SET manifest_cache = '" . $nb_database->getEscaped(json_encode($manifest)) . "' WHERE `type` = 'component' AND `element` = 'com_nbill'");
                            $nb_database->query();
                        }
                    }

                    //Amend the URL of any menu links for the component, and the title of the main admin menu item
                    $nb_database->setQuery("UPDATE #__menu SET `link` = REPLACE(`link`, 'index.php?option=" . $db_component_name . "', 'index.php?option=com_nbill')");
                    $nb_database->query();
                    $nb_database->setQuery("UPDATE #__menu SET `title` = 'nBill' WHERE `menu_type` = 'main' AND `img` = '../administrator/components/com_nbill/logo-icon-16.gif'");
                    $nb_database->query();
                }
            }
        }
        parent::unregister_custom_component_name($db_component_names);
    }

    protected function set_cms_database_enum()
    {
        $this->cms_database_enum = new cms_database_enumerator();

        //Access Levels
        $this->cms_database_enum->table_user_groups = "#__usergroups";
        $this->cms_database_enum->column_acl_id = "id";
        $this->cms_database_enum->column_acl_name = "title";

        //User Groups
        $this->cms_database_enum->table_user_group = "#__usergroups";
        $this->cms_database_enum->column_user_group_id = "id";
        $this->cms_database_enum->column_user_group_name = "title";
        $this->cms_database_enum->column_user_group_parent_id = "parent_id";
        $this->cms_database_enum->column_user_group_left = "lft";
        $this->cms_database_enum->column_user_group_right = "rgt";

        //Users
        $this->cms_database_enum->table_user = "#__users";
        $this->cms_database_enum->column_user_id = "id";
        $this->cms_database_enum->column_user_username = "username";
        $this->cms_database_enum->column_user_email = "email";
        $this->cms_database_enum->column_user_gid = null;
        $this->cms_database_enum->column_user_name = "name";
        $this->cms_database_enum->column_user_password = "password";
        $this->cms_database_enum->column_block = "block";
        $this->cms_database_enum->column_register_date = "registerDate";
        $this->cms_database_enum->column_last_visit_date = "lastvisitDate";

        //Hard-coded GIDs for super administrator and minimum administrator
        $this->cms_database_enum->super_admin_gid = 8;
        $this->cms_database_enum->manager_gid = 6;
        $this->cms_database_enum->registered_gid = 2;
    }

    /**
    * Return the group name for the given group id
    * @param int $gid
    * @return The group name
    */
    public function get_user_group_name($gid)
    {
        $nb_database = nbf_cms::$interop->database;
        $sql = "SELECT `title` FROM #__usergroups WHERE id = $gid";
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
    * Work out which language is in use. If $_SESSION contains a user object with a
    * valid language parameter, use that. Otherwise, load the value from the components
    * table (default language is stored in the params setting for the language component)
    * @return string Language code in format ll-CC where ll is the 2-character ISO 639-1 language code and CC is the 2 character ISO 3166 country code.
    */
    protected function get_language()
    {
        $lang = parent::get_language();

        /*//A value supplied in the URL overrides any other
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
        }*/

        //Joomla will not allow us to get at the value without using their API
        if (!$lang)
        {
            if (class_exists('JFactory')) { //It won't exist if we are unit testing
                $config = JFactory::getConfig();
                $lang = $config->get('language');
            }
        }

        if (!$lang) {
            //Are we running within the administrator folder?
            $admin = (nbf_common::nb_strpos(nbf_common::get_requested_page(true), $this->live_site . "/administrator") !== false);
            $user_param_name = $admin ? "admin_language=" : "language=";
            $default_param_name = $admin ? "\"administrator\"" : "\"site\"";

            //Check for user-specific language parameter in session for this login
            if (!$lang && isset($_SESSION["__default"]) && isset($_SESSION["__default"]["registry"]) &&
                method_exists($_SESSION['__default']['registry'], 'get') &&
                isset($_SESSION['__default']['registry']->get('application')->lang))
            {
                $lang = $_SESSION['__default']['registry']->get('application')->lang;
            }
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
            $sql = "SELECT `params` FROM #__extensions WHERE `element` = 'com_languages'";
            $this->database->setQuery($sql);
            $params = $this->database->loadResult();
            if (substr($params, 0, 1) == "{")
            {
                $params = substr($params, 1);
            }
            if (nbf_common::nb_substr($params, nbf_common::nb_strlen($params) - 1) == "}")
            {
                $params = nbf_common::nb_substr($params, 0, nbf_common::nb_strlen($params) - 1);
            }
            if ($params)
            {
                $params = explode(",", $params);
                foreach ($params as $param)
                {
                    $param = explode(":", $param);
                    if (count($param) == 2)
                    {
                        if ($param[0] == $default_param_name)
                        {
                            $lang = str_replace("\"", "", $param[1]);
                            break;
                        }
                    }
                }
            }
        }

        /*if (!$lang)
        {
            $lang = parent::get_language();
        }*/

        return $lang;
    }

    /**
    * Return default language for website front end
    */
    public function get_frontend_language()
    {
        $lang = "";
        $sql = "SELECT `params` FROM #__extensions WHERE `element` = 'com_languages'";
        $this->database->setQuery($sql);
        $params = $this->database->loadResult();
        if (substr($params, 0, 1) == "{")
        {
            $params = substr($params, 1);
        }
        if (nbf_common::nb_substr($params, nbf_common::nb_strlen($params) - 1) == "}")
        {
            $params = nbf_common::nb_substr($params, 0, nbf_common::nb_strlen($params) - 1);
        }
        if ($params)
        {
            $params = explode(",", $params);
            foreach ($params as $param)
            {
                $param = explode(":", $param);
                if (count($param) == 2)
                {
                    if ($param[0] == "\"site\"")
                    {
                        $lang = str_replace("\"", "", $param[1]);
                        break;
                    }
                }
            }
        }
        return $lang;
    }

    public function get_cms_url_language_code($nbill_language_code)
    {
        $sql = "SELECT `sef` FROM #__languages WHERE `lang_code` = '" . $this->database->getEscaped($nbill_language_code) . "'";
        $this->database->setQuery($sql);
        $url_lang_code = $this->database->loadResult();
        if ($url_lang_code && strlen($url_lang_code) > 1) {
            return $url_lang_code;
        }
        return parent::get_cms_url_language_code($nbill_language_code);
    }

    /**
    * Set the URL prefixes that tell Joomla 1.5 to direct calls to nBill, and record the
    * URL path to the component files (for including the admin stylesheet and referencing images).
    */
    protected function set_url_prefixes()
    {
        parent::set_url_prefixes();
        include_once($this->nframework_admin_base_path . "/framework/classes/nbill.version.class.php");
        $nb_version = new nbf_version($this->cms_version);
        if ($nb_version->compare('<', '2.5.0'))
        {
            $this->admin_component_uninstaller = "index.php?option=com_installer&view=manage&filters[type]=component&filters[search]=" . str_replace(' Lite', '', NBILL_BRANDING_NAME);
        }
        else
        {
            $this->admin_component_uninstaller = "index.php?option=com_installer&view=manage&filter_type=component&filter_search=" . str_replace(' Lite', '', NBILL_BRANDING_NAME);
        }
        $this->user_editor_url = "index.php?option=com_users&view=user&layout=edit&id=%s";
    }

    /**
    * Add Joom!Fish support, if applicable
    */
    public function install_tasks()
    {
        //Joomla 1.6 Beta converts admin menu option to lower case - so we need to correct it
        $nb_database = $this->database;
        $nb_database->setQuery("SELECT id FROM #__menu WHERE (menutype = '_adminmenu' OR menutype = 'main') AND `link` = 'index.php?option=com_nbill'");
        $id = intval($nb_database->loadResult());
        $nb_database->setQuery( "UPDATE `#__menu` SET `img` = '../administrator/components/com_nbill/logo-icon-16.gif', `alias` = '" . NBILL_BRANDING_NAME . "' WHERE `id` = '$id'");
        $nb_database->query();
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
    * Return the URL for the lost password feature of the CMS
    */
    public function get_lost_password_link()
    {
        return "index.php?option=com_users&view=reset";
    }

    public function get_logout_link($return = "")
    {
        if (!$return)
        {
            $return = $this->live_site . '/';
        }
        if (!@$_SESSION['__default']['session.token'])
        {
            $new_token = "";
            $zero_to_f = "0123456789abcdef";
            for ($i = 0; $i < 15; $i++)
            {
                $new_token .= $zero_to_f[(rand(0, 15))];
            }
            $new_token .= session_name();
            $_SESSION['__default']['session.token'] = md5($new_token);
        }
        return $this->live_site . "/index.php?option=com_users&view=login&task=user.logout&" . md5($this->read_config_file_value('secret') . $this->user->id . @$_SESSION['__default']['session.token']) . "=1&return=" . base64_encode($return);
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
            $sql = "SELECT #__users.name, #__users.username, #__users.email, #__users.id
                    FROM #__users WHERE #__users.id = $user_id";
            $nb_database->setQuery($sql);
            $nb_database->loadObject($this_user);
            //$this->user->id = intval($this_user->id);
            $sql = "SELECT #__usergroups.id, #__usergroups.title
                    FROM #__usergroups INNER JOIN #__user_usergroup_map ON #__usergroups.id = #__user_usergroup_map.group_id
                    WHERE #__user_usergroup_map.user_id = $user_id
                    GROUP BY #__usergroups.id";
            $nb_database->setQuery($sql);
            $groups = $nb_database->loadObjectList();
            $this_user->groups = array();
            foreach ($groups as $group)
            {
                $this_user->groups[$group->id] = $group->title;
            }
            $this_user->group_name = implode(", ", $this_user->groups);
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
        $sql = "SELECT extension_id FROM #__extensions WHERE params LIKE '%\"allowUserRegistration\":\"1\"%'";
        $this->database->setQuery($sql);
        return $this->database->loadResult() ? "index.php?option=com_users&view=registration" : "";
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
            $username = nbf_common::nb_substr($username, 0, $username_max_length);
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

        $sql = "INSERT INTO #__users (name, username, email, password, block, sendEmail, registerDate, lastvisitDate, activation, params)
                VALUES ('" . $nb_database->getEscaped($name) . "', '$username', '$email', '" . $pwd_hash . "', 0, 0, '$now', '$now', '', '{\"admin_language\":\"\",\"language\":\"\",\"editor\":\"\",\"helpsite\":\"\",\"timezone\":\"\"}')";
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
            //Assign to one or more user groups
            $sql = "SELECT #__nbill_configuration.default_user_groups FROM #__nbill_configuration WHERE #__nbill_configuration.id = 1";
            $nb_database->setQuery($sql);
            $groups = explode(",", $nb_database->loadResult());
            $group_count = 0;
            foreach ($groups as $group)
            {
                if (intval($group)) {$group_count++;}
            }
            if (!$group_count || $this->demo_mode)
            {
                $groups = array();
                $groups[] = $this->cms_database_enum->registered_gid;
            }
            foreach ($groups as $group)
            {
                //If group exists, map the user to it
                $sql = "SELECT id FROM #__usergroups WHERE id = " . intval($group);
                $nb_database->setQuery($sql);
                if ($nb_database->loadResult())
                {
                    $sql = "INSERT INTO #__user_usergroup_map (user_id, group_id) VALUES ($user_id, $group)";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                }
            }

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

        nbf_common::fire_event("user_group_changed", array("user_id"=>$user_id, "new_group"=>$new_group, "new_level"=>$new_level)); //new_level deprecated

        $sql = "DELETE FROM #__user_usergroup_map WHERE user_id = $user_id";
        $nb_database->setQuery($sql);
        $nb_database->query();

        $sql = "INSERT INTO #__user_usergroup_map (user_id, group_id) VALUES ($user_id, $new_group)";
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

    /**
    * Add the user to the given group (leaving existing group assignments alone)
    * @param integer $user_id ID of the user to add
    * @param integer $new_group Group ID (gid) of the group to add the user to
    */
    function add_user_to_group($user_id, $new_group)
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

        nbf_common::fire_event("user_group_changed", array("user_id"=>$user_id, "new_group"=>$new_group, "new_level"=>$new_level)); //new_level deprecated

        $sql = "INSERT INTO #__user_usergroup_map (user_id, group_id) VALUES ($user_id, $new_group)";
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

    /**
    * Remove the user from one group, and add them to another
    * @param integer $user_id ID of the user to move
    * @param integer $current_group Group ID (gid) of the old group
    * @param integer $new_group Group ID (gid) of the new group
    */
    function replace_user_group($user_id, $current_group, $new_group)
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
        $current_group = intval($current_group);
        $new_group = intval($new_group);

        nbf_common::fire_event("user_group_changed", array("user_id"=>$user_id, "new_group"=>$new_group, "new_level"=>$new_group)); //new_level deprecated

        $sql = "DELETE FROM #__user_usergroup_map WHERE user_id = $user_id AND group_id = $current_group";
        $nb_database->setQuery($sql);
        $nb_database->query();

        $sql = "REPLACE INTO #__user_usergroup_map (user_id, group_id) VALUES ($user_id, $new_group)";
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

    /**
    * Whether or not the given user belongs to the given group
    * @param integer $user_id ID of the user
    * @param integer $group_id ID of the group
    */
    function user_in_group($user_id, $group_id)
    {
        $sql = "SELECT user_id FROM #__user_usergroup_map WHERE user_id = " . intval($user_id) . " AND group_id = " . intval($group_id);
        $this->database->setQuery($sql);
        return $this->database->loadResult() == $user_id;
    }

    /**
    * Returns a list of user groups from the tree in order of hierarchy
    */
    public function get_acl_group_list()
    {
        $nb_database = $this->database;

        //Initialise
        $left = 0;
        $right = 0;
        $left_col = $this->cms_database_enum->column_user_group_left;
        $right_col = $this->cms_database_enum->column_user_group_right;
        $group_table = $this->cms_database_enum->table_user_group;
        $group_name_col = $this->cms_database_enum->column_user_group_name;
        $group_id_col = $this->cms_database_enum->column_user_group_id;
        $parent_id_col = $this->cms_database_enum->column_user_group_parent_id;

        //Find the starting point
        $sql = "SELECT `$left_col`, `$right_col` FROM `$group_table` WHERE parent_id = 0";
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

        return $groups;
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
        $sql = "SELECT params FROM #__extensions WHERE folder = 'system' AND element = 'nbill_account_expiry'";
        $nb_database->setQuery($sql);
        $params = $nb_database->loadResult();

        if (substr($params, 0, 1) == "{")
        {
            $params = substr($params, 1);
        }
        if (nbf_common::nb_substr($params, nbf_common::nb_strlen($params) - 1) == "}")
        {
            $params = nbf_common::nb_substr($params, 0, nbf_common::nb_strlen($params) - 1);
        }
        if ($params)
        {
            $params = explode(",", $params);
            foreach ($params as $param)
            {
                $param = explode(":", $param);
                if (count($param) == 2)
                {
                    $param_assoc[str_replace("\"", "", $param[0])] = str_replace("\"", "", $param[1]);
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

        $frontend = 2;
        $backend = 6;

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
                $sql = "DELETE FROM #__users WHERE id = $user_id";
                $nb_database->setQuery($sql);
                $nb_database->query();
                $sql = "DELETE FROM #__user_usergroup_map WHERE user_id = $user_id";
                $nb_database->setQuery($sql);
                $nb_database->query();
                $sql = "DELETE FROM #__user_profiles WHERE user_id = $user_id";
                $nb_database->setQuery($sql);
                $nb_database->query();
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
        $sql = "SELECT enabled FROM #__extensions WHERE element = 'nbill_user_subscription'";
        $this->database->setQuery($sql);
        return $this->database->loadResult();
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

        //Get the component ID
        $sql = "SELECT extension_id FROM #__extensions WHERE element = 'com_nbill'";
        $nb_database->setQuery($sql);
        $component_id = intval($nb_database->loadResult());

        //Get rgt from the parent menu
        $sql = "SELECT rgt FROM #__menu WHERE id = 1";
        $nb_database->setQuery($sql);
        $root_rgt = $nb_database->loadResult();

        //Create the menu item
        $alias = nbf_common::nb_strtolower(str_replace(" ", "-", $name)) . '_' . substr(uniqid(), 0, 5); //ID added in case a duplicate is present (even in trash)
        $sql = "INSERT INTO #__menu (menutype, title, alias, path, link, type, level, published, component_id, access, params, language, lft, rgt) VALUES
                    ('$menu', '$name', '$alias', '$alias', '" . $this->site_page_prefix . "&action=$action&task=order&cid=$form_id" . $this->site_page_suffix . "', 'url', 1, 1, $component_id, 1, '{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\"}', '*', $root_rgt, " . strval($root_rgt + 1) . ");";
        $nb_database->setQuery($sql);
        $nb_database->query();
        $menu_id = $nb_database->insertid();
        if ($menu_id)
        {
            //Update the rgt value of the root
            $sql = "UPDATE #__menu SET rgt = " . strval($root_rgt + 2) . " WHERE id = 1";
            $nb_database->setQuery($sql);
            $nb_database->query();
            //Report success
            nbf_globals::$message = NBILL_MENU_ITEM_CREATED;
            if (!nbf_common::get_param($_REQUEST, 'nbill_admin_via_fe'))
            {
                $link = $this->cms_version == "1.6" ? "index.php?option=com_menus&menutype=$menu&task=item.edit&cid[]=$menu_id" : "index.php?option=com_menus&task=item.edit&id=$menu_id";
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
        $mainframe = $GLOBALS['app'];
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
        $options['remember'] = true; // $remember; //Some mysterious session problem can prevent the login from 'sticking' unless we set this to true;
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
    * Count number of users who are not super admins
    */
    public function count_non_super_admins()
    {
        $user_table = nbf_cms::$interop->cms_database_enum->table_user;
        $user_id_col = nbf_cms::$interop->cms_database_enum->column_user_id;
        $super_admin_gid = intval(nbf_cms::$interop->cms_database_enum->super_admin_gid);

        $sql = "SELECT count(DISTINCT `$user_table`.`$user_id_col`) FROM `$user_table` LEFT JOIN #__nbill_contact ON `$user_table`.`$user_id_col` = #__nbill_contact.user_id
                        INNER JOIN #__user_usergroup_map ON $user_table.$user_id_col = #__user_usergroup_map.user_id
                        WHERE #__nbill_contact.user_id IS NULL
                        AND #__user_usergroup_map.group_id != $super_admin_gid";
        $this->database->setQuery($sql);
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
        $super_admin_gid = intval(nbf_cms::$interop->cms_database_enum->super_admin_gid);

        $sql = "SELECT `$user_table`.`$user_id_col` AS user_id, `$user_table`.`$user_username_col` AS username, `$user_table`.`$user_name_col` AS name, `$user_table`.`$user_email_col` AS email FROM `$user_table`
                        LEFT JOIN #__nbill_contact ON `$user_table`.`$user_id_col` = #__nbill_contact.user_id
                        INNER JOIN #__user_usergroup_map ON $user_table.$user_id_col = #__user_usergroup_map.user_id
                        WHERE #__nbill_contact.user_id IS NULL
                        AND #__user_usergroup_map.group_id != $super_admin_gid
                        GROUP BY `$user_table`.`$user_id_col`
                        ORDER BY `$user_table`.`$user_name_col`";
        if ($pagination)
        {
            $sql .= " LIMIT $pagination->list_offset, $pagination->records_per_page";
        }
        $this->database->setQuery($sql);
        return $this->database->loadObjectList();
    }

    public function prompt_for_ftp_details($error_message)
    {
        //Try to redirect to Joomla global config page
        @setcookie("configuration", "server");
        $mainframe = $_GLOBALS['app'];
        if (method_exists($mainframe, 'enqueueMessage'))
        {
            $mainframe->enqueueMessage($error_message);
            nbf_common::redirect("index.php?option=com_config");
        }
        else
        {
            parent::prompt_for_ftp_details($error_message);
        }
    }

    /**
    * If the CMS holds any extra data about the user, return it in an associative array
    * The key for each value will be matched to core profile field names (without the NBILL_CORE_ prefix)
    * @param int $user_id
    */
    public function load_cms_user_profile($user_id)
    {
        $profile = array();
        $sql = "SELECT profile_key, profile_value FROM #__user_profiles WHERE user_id = " . intval($user_id);
        $this->database->setQuery($sql);
        $profile_values = $this->database->loadObjectList();
        foreach ($profile_values as $profile_value) {
            $key = str_replace('profile.', '', $profile_value->profile_key);
            $value = trim(str_replace('"', '', $profile_value->profile_value));
            switch ($key) {
                case "address1":
                    $key = "address_1";
                    break;
                case "address2":
                    $key = "address_2";
                    break;
                case "city":
                    $key = "town";
                    break;
                case "phone":
                    $key = "telephone";
                    break;
                case "postal_code":
                    $key = "postcode";
                    break;
                case "region":
                    $key = "state";
                    break;
                case "country":
                    $sql = "SELECT `code` FROM #__nbill_xref_country_codes WHERE `code` = '$value' OR `description` = '$value'";
                    $this->database->setQuery($sql);
                    $value = $this->database->loadResult();
                    break;
                case "user_id": case "first_name": case "last_name": case "email_address": case "last_updated" : case "notes": case "custom_fields":
                    $value = '';
                    break;
            }
            if (strlen($value) > 0) {
                $profile[$key] = $value;
            }
        }

        return count($profile) > 0 ? $profile : null;
    }
}