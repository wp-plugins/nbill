<?php
/**
* Interop Class File for Wordpress
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* This class provides interop functions specific to Wordpress.
*
* @package nBill Framework Interop
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_interop_wordpress extends nbf_interop
{
    /** @var string Name of CMS (for display in error reports) */
    public $cms_name = "Wordpress";
    /** @var string Version number of CMS (for display in error reports) */
    public $cms_version = "4.x";
    /** @var boolean Whether or not to hide the FTP details on the global config screen (pick up details from CMS instead) */
    public $hide_ftp_details = true;
    /** @var boolean Whether or not a user can belong to more than one user group */
    public $multi_user_group = true;

    protected $hold_globals = array();

    /**
    * Set records per page
    */
    protected function initialise()
    {
        parent::initialise();
        $this->records_per_page = function_exists('get_option') ? get_option('posts_per_page') : 50;

        //Wordpress always adds slashes even if magic quotes is off (if on, we already strip them, but for wp we need to do it when off)
        if (!get_magic_quotes_gpc()) {
            //Hold originals in case other plugins rely on magic quoting (restore before handing control back)
            $this->hold_globals['POST'] = $_POST;
            $this->hold_globals['GET'] = $_GET;
            $this->hold_globals['COOKIE'] = $_COOKIE;
            $this->hold_globals['REQUEST'] = $_REQUEST;
            if (!function_exists('stripslashes_deep_newlines')) {
                function stripslashes_deep_newlines($value)
                {
                    $value = is_array($value) ?
                                array_map('stripslashes_deep', $value) :
                                stripslashes(str_replace('\\n', '[[newline]]', $value)); //Line breaks have to be passed in as newline characters to avoid xss protection issues
                    return str_replace('[[newline]]', '\\n', $value);
                }
            }
            $_POST = array_map('stripslashes_deep_newlines', $_POST);
            $_GET = array_map('stripslashes_deep_newlines', $_GET);
            $_COOKIE = array_map('stripslashes_deep_newlines', $_COOKIE);
            $_REQUEST = array_map('stripslashes_deep_newlines', $_REQUEST);
        }
    }

    public function terminate()
    {
        $_POST = $this->hold_globals['POST'];
        $_GET = $this->hold_globals['GET'];
        $_COOKIE = $this->hold_globals['COOKIE'];
        $_REQUEST = $this->hold_globals['REQUEST'];
    }

    /**
    * Records the location of the website root folder, and component files (admin and front end)
    **/
    protected function get_base_paths()
    {
        $this->nbill_admin_base_path = realpath(dirname(__FILE__) . "/../..");
        $this->nbill_fe_base_path = realpath(dirname(__FILE__) . "/../../../../../components/com_" . $this->component_name);
        $this->site_base_path = realpath(dirname(__FILE__) . "/../../../../../../../..");

        require_once($this->nbill_admin_base_path . "/framework/classes/nbill.file.class.php");

        //Try PHP temp directory
        $temp_path = @realpath(@sys_get_temp_dir());
        if (strlen($temp_path) > 0 && nbf_file::is_folder_writable($temp_path)) {
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
        //Remove any trailing slashes from all paths
        $this->remove_trailing_slash("nbill_admin_base_path");
        $this->remove_trailing_slash("nbill_fe_base_path");
        $this->remove_trailing_slash("site_base_path");
        $this->remove_trailing_slash("site_temp_path");
    }

    protected function get_live_site()
    {
        if (function_exists('get_site_url')) {
            return get_site_url();
        } else {
            return parent::get_live_site();
        }
    }

    /**
    * Set the string to use for the action paramter on front-end forms
    * @param boolean $submit_option Whether or not to include the option parameter
    */
    protected function set_fe_form_action($submit_option)
    {
        if ($submit_option) {
            $this->fe_form_action = "index.php?" . $this->component_name . "=my-account";
        } else {
            $this->fe_form_action = "index.php";
        }
    }

    public function set_cms_language($language)
    {
        //Depends on the multi-language plugin in use (if any)
        $_GET['lang'] = substr($language, 0, 2);

        //qTranslate
        if (function_exists('qtrans_init')) {
            qtrans_init(); //Might not have loaded yet even if installed
        }

        //WPML
        global $sitepress;
        if (isset($sitepress) && $sitepress !== null) {
            $sitepress->switch_lang(substr($language, 0, 2)); //wpml
        }

    }

    /**
    * Record the table and column names for the CMS database entities that are required by nBill
    */
    protected function set_cms_database_enum()
    {
        $this->cms_database_enum = new cms_database_enumerator();

        //Users
        $this->cms_database_enum->table_user = "#__users";
        $this->cms_database_enum->column_user_id = "ID";
        $this->cms_database_enum->column_user_username = "user_login";
        $this->cms_database_enum->column_user_email = "user_email";
        $this->cms_database_enum->column_user_gid = "";
        $this->cms_database_enum->column_user_name = "display_name";
        $this->cms_database_enum->column_user_password = "user_pass";
        $this->cms_database_enum->column_block = "user_status";
        $this->cms_database_enum->column_register_date = "user_registered";
        $this->cms_database_enum->column_last_visit_date = "";

        //Groups (roles)
        $this->cms_database_enum->column_user_group_id = "group_id";
        $this->cms_database_enum->column_user_group_name = "name";

        //Hard-coded default GID for admin
        $this->cms_database_enum->super_admin_gid = "administrator";
        $this->cms_database_enum->registered_gid = "subscriber";
    }

    public function use_gzip()
    {
        return false;
    }

    /**
    * Return the group name for the given group id
    * @param int $gid
    * @return The group name
    */
    public function get_user_group_name($gid)
    {
        global $wp_roles;
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        if (isset($wp_roles->roles) && array_key_exists($gid, $wp_roles->roles) && isset($wp_roles->roles[$gid]['name'])) {
            return $wp_roles->roles[$gid]['name'];
        } else {
            $role = get_role($gid);
            if ($role) {
                return ucwords($role->name);
            }
        }
        return defined('NBILL_UNKNOWN') ? NBILL_UNKNOWN : 'Unknown';
    }

    protected function get_admins($and_supers = true)
    {
        $admin_ids = array();
        $admins = get_users(array('role'=>'administrator'));
        foreach ($admins as $admin) {
            $admin_ids[] = intval($admin->id);
        }
        $admins = get_users(array('role'=>'super admin'));
        foreach ($admins as $admin) {
            $admin_ids[] = intval($admin->id);
        }
        return $admin_ids;
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

        $admin_ids = $this->get_admins();

        $sql = "SELECT count(`$user_table`.`$user_id_col`) FROM `$user_table` LEFT JOIN
                    #__nbill_contact ON `$user_table`.`$user_id_col` = #__nbill_contact.user_id
                    WHERE #__nbill_contact.user_id IS NULL";
        if (count($admin_ids) > 0) {
            $sql .= " AND `$user_table`.`$user_id_col` NOT IN (" . implode(",", $admin_ids) . ")";
        }
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

        $admin_ids = $this->get_admins();
        $sql = "SELECT `$user_table`.`$user_id_col` AS user_id, `$user_table`.`$user_username_col` AS username, `$user_table`.`$user_name_col` AS name, `$user_table`.`$user_email_col` AS email FROM `$user_table`
                    LEFT JOIN #__nbill_contact ON `$user_table`.`$user_id_col` = #__nbill_contact.user_id
                    WHERE #__nbill_contact.user_id IS NULL";
        if (count($admin_ids) > 0) {
            $sql .= " AND `$user_table`.`$user_id_col` NOT IN (" . implode(",", $admin_ids) . ")";
        }
        $sql .= " ORDER BY `$user_table`.`$user_name_col`";
        if ($pagination)
        {
            $sql .= " LIMIT $pagination->list_offset, $pagination->records_per_page";
        }
        $this->database->setQuery($sql);
        return $this->database->loadObjectList();
    }

    /**
    * Call the user subscription plugin
    */
    public function check_account_expiry()
    {
        //Just try including the mambot file, if it exists, then call the function
        if (file_exists($this->site_base_path . "/plugins/nbill/plugins/system/nbill_user_subscription.php")) {
            include_once($this->site_base_path . "/plugins/nbill/plugins/system/nbill_user_subscription.php");
        }
    }

    /**
    * Parse the configuration file to get the required value.
    * @param string $key Name of the setting to retrieve (do not prefix with $)
    * @return string The value from the config file, or NULL if no value found
    */
    protected function read_config_file_value($key)
    {
        if ($key == 'table_prefix') {
            global $wpdb;
            return $wpdb->prefix;
        } else if (defined($key)) {
            return constant($key);
        }
        return null;
    }

    /**
    * Work out which language is in use.
    * @return string Language code in format ll-CC where ll is the 2-character ISO 639-1 language code and CC is the 2 character ISO 3166 country code.
    */
    protected function get_language()
    {
        if (isset($_GET['lang'])) {
            $lang = nbf_common::get_param($_GET,'lang');
            switch (strtolower($lang)) {
                case 'bg':
                    return 'bg-BG';
                case 'ca':
                    return 'ca-ES';
                case 'el':
                case 'gr':
                    return 'el-GR';
                case 'nb':
                case 'no':
                    return 'nb-NO';
                default:
                    return strtolower($lang) . '-' . strtoupper($lang);
            }
        } else {
            return parent::get_language();
        }
    }

    /**
    * Return default language for website front end
    */
    public function get_frontend_language()
    {
        return $this->get_language();
    }

    /**
    * Set the URL prefixes that tell Joomla 1.5 to direct calls to nBill, and record the
    * URL path to the component files (for including the admin stylesheet and referencing images).
    */
    protected function set_url_prefixes()
    {
        $component = str_replace('com_', '', NBILL_BRANDING_COMPONENT_NAME);
        $this->cms_home_page = "index.php";
        $this->admin_page_prefix = "admin.php?page=$component/$component.php";
        $this->admin_popup_page_prefix = $this->admin_page_prefix . "&nbill_popup=1";
        $this->site_page_prefix = "?$component=my-account";
        $this->site_popup_page_prefix = $this->site_page_prefix . "&nbill_popup=1";
        $this->nbill_admin_url_path = plugins_url() . "/nbill/administrator/components/com_nbill";
        $this->nbill_site_url_path = plugins_url() . "/nbill/components/com_nbill";
        $this->admin_component_uninstaller = "plugins.php";
        $this->user_editor_url = "user-edit.php?user_id=%s";
    }

    /**
    * Get the site name from the config file, if available.
    * @return string The site name as recorded in the configuration file
    */
    function get_site_name()
    {
        $site_name = '';
        if (function_exists('get_option')) {
            $site_name = get_option('blogname');
        }
        if (!$site_name) {
            $sql = "SELECT option_value FROM #__options WHERE option_name = 'blogname'";
            $this->database->setQuery($sql);
            $site_name = $this->database->loadResult();
        }

        if ($site_name) {
            return $site_name;
        } else {
            return parent::get_site_name();
        }
    }

    /**
    * Get the database connection settings from the configuration file
    * @return nbf_db_connection
    */
    public function get_db_connection_settings()
    {
        $host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $port = null;
        if (strpos($host, ":") !== false) {
            $host_parts = explode(":", $host);
            if (count($host_parts) >= 2) {
                $host = $host_parts[0];
                $port = $host_parts[1];
            }
        }
        $database_name = defined('DB_NAME') ? DB_NAME : 'wordpress';
        $username = defined('DB_USER') ? DB_USER : 'root';
        $password = defined('DB_PASSWORD') ? DB_PASSWORD : '';
        $prefix = $this->read_config_file_value("table_prefix");
        $prefix = $prefix === null ? 'wp_' : $prefix;
        if (trim($database_name)) {
            return new nbf_db_connection($host, $database_name, $username, $password, $prefix, $port);
        }
    }

    protected function get_database()
    {
        require_once($this->nbill_admin_base_path . "/framework/database/nbill.database.class.php");
        $this->db_connection = $this->get_db_connection_settings();
        $nbf_db = new nbf_database($this->db_connection->host, $this->db_connection->user_name, $this->db_connection->password, $this->db_connection->db_name, $this->db_connection->prefix, $this->db_connection->port, $this->db_connection->socket);
        $nbf_db->set_char_encoding($this->db_charset);
        $return_value = $nbf_db;
        return $nbf_db;
    }

    public function cms_editor_supported()
    {
        return false;
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

        if ($user_id == null) {
            $user_id = get_current_user_id();
        } else {
            $return_user = true;
        }
        if ($user_id) {
            $this_user->id = $user_id;
            $user = new WP_User( $user_id );
            $this_user->username = $user->get('user_login');
            $this_user->email = $user->get('user_email');
            $this_user->name = $user->get('display_name');
            //Try to find first and last name on a contact record
            $contact = null;
            $sql = "SELECT first_name, last_name FROM #__nbill_contact WHERE user_id = " . $this_user->id;
            $nb_database->setQuery($sql);
            $nb_database->loadObject($contact);
            if ($contact) {
                $this_user->first_name = $contact->first_name;
                $this_user->last_name = $contact->last_name;
            }
            if (strlen($contact->first_name . $contact->last_name) > 1) {
                $this_user->name = trim($contact->first_name . ' ' . $contact->last_name);
            }
            $group_name = array();
            if (isset($user->roles) && is_array($user->roles)) {
                foreach ($user->roles as $role) {
                    $this_user->gid = $this_user->gid ? $this_user->gid : $role;
                    $this_user->groups[] = $role;
                    $group_name[] = $this->get_user_group_name($role);
                }
            }
            $this_user->group_name = implode(", ", $group_name);
        }

        if ($return_user) {
            return $this_user;
        }
        $this->user = $this_user;
    }

    public function get_user_registration_url()
    {
        return get_option('users_can_register') ? "w" . "p-login.php?action=register" : "";
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

        //Make sure username does not already exist
        if (username_exists($username)) {
            nbf_globals::$message = NBILL_USERNAME_EXISTS;
            return -1;
        }

        //Validate e-mail address
        if ((nbf_common::nb_strlen($email) == 0) || (preg_match("/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $email) == false)) {
            nbf_globals::$message = NBILL_EMAIL_INVALID;
            return -1;
        }

        //Make sure the email address is not already registered
        if (email_exists($email)) {
            nbf_globals::$message = NBILL_USER_EMAIL_EXISTS;
            return -1;
        }

        $user_id = wp_insert_user(array(
            'user_login' => esc_sql($username),
            'user_email' => esc_sql($email),
            'user_pass' => $password,
            'display_name' => esc_sql($name)
        ));

        //Set role
        $sql = "SELECT default_user_groups FROM #__nbill_configuration WHERE id = 1";
        $nb_database->setQuery($sql);
        $default_role = $nb_database->loadResult();
        $roles = get_option('wp_user_roles');
        if (!array_key_exists($default_role, $roles)) {
            $default_role = $this->cms_database_enum->registered_gid;
        }
        $user = new WP_User($user_id);
        $roles = explode(",", $default_role);
        foreach ($roles as $role) {
            $user->set_role($role);
        }

        return intval($user_id);
    }

    public function get_acl_group_list()
    {
        $groups = array();

        global $wp_roles;
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        if (!$wp_roles || !isset($wp_roles->roles)) {
            $group = new stdClass();
            $group->level = 1;
            $group->{$this->cms_database_enum->column_user_group_id} = "subscriber";
            $group->{$this->cms_database_enum->column_user_group_name} = "Subscriber";
            $groups[] = $group;
        } else {
            foreach ($wp_roles->roles as $id=>$role) {
                $group = new stdClass();
                $group->level = 1;
                $group->{$this->cms_database_enum->column_user_group_id} = $id;
                $group->{$this->cms_database_enum->column_user_group_name} = $role['name'];
                $groups[] = $group;
            }
        }

        return $groups;
    }

    /**
    * Change the access level user group for the given user to the new group id
    * @param integer $user_id ID of the user to upgrade or downgrade
    * @param integer $new_level Group ID (gid) of the new user group
    */
    function change_user_group($user_id, $new_group)
    {
        if ($this->demo_mode) {
            die("User group privileges cannot be changed in demo mode");
        }

        $user_id = intval($user_id);
        nbf_common::fire_event("user_group_changed", array("user_id"=>$user_id, "new_group"=>$new_group, "new_level"=>$new_group)); //new_level deprecated
        $user = new WP_User($user_id);
        $user->set_role($new_group);
    }

    /**
    * Add the user to the given group (this CMS does not support multiple groups per user, so it just changes the group)
    * @param integer $user_id ID of the user to add
    * @param integer $new_group Group ID (gid) of the group to add the user to
    */
    function add_user_to_group($user_id, $new_group)
    {
        if ($this->demo_mode) {
            die("User group privileges cannot be changed in demo mode");
        }

        $user_id = intval($user_id);
        nbf_common::fire_event("user_group_changed", array("user_id"=>$user_id, "new_group"=>$new_group, "new_level"=>$new_group)); //new_level deprecated
        $user = new WP_User($user_id);
        $user->add_role($new_group);
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
    * Whether or not the given user belongs to the given group
    * @param integer $user_id ID of the user
    * @param string $group_id ID of the group
    */
    function user_in_group($user_id, $group_id)
    {
        $user = new WP_User($user_id);
        foreach ($user->roles as $role) {
            if (strtolower($role) == strtolower($group_id)) {
                return true;
            }
        }
        return false;
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
        wp_logout();

        wp_set_auth_cookie($user->id);
        do_action('wp_login', $user->username, new WP_User($user->id));

        if ($url) {
            nbf_common::redirect($url);
            exit;
        }
    }

    public function log_out()
    {
        wp_logout();
    }

    /**
    * Get an associative array of parameters for the user subscription mambot/plugin
    * @return array The parameters as an associative array
    */
    public function get_account_expiry_params()
    {
        //Use the default values
        $param_assoc['js_refresh'] = 0;
        $param_assoc['reload'] = 1;
        return $param_assoc;
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
                wp_delete_user($user_id);
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
        return file_exists($this->wordpress_plugins_dir() . '/nbill_user_subscription');
    }

    protected function wordpress_plugins_dir()
    {
        return realpath(plugin_dir_path(__FILE__) . '../../../../../..');
    }

    /**
    * Returns a list of menu titles as an associative array keyed on the menu name. This list is used
    * to facilitate creating a new menu item pointing to an order form. If an empty array is returned
    * by an override of this function, the option to create a menu item will not be available to the user.
    */
    public function get_menu_list()
    {
        $nb_database = $this->database;
        $sql = "SELECT slug, name
                FROM `#__terms`
                INNER JOIN `#__term_taxonomy` ON `#__terms`.term_id = `#__term_taxonomy`.term_id
                WHERE `#__term_taxonomy`.taxonomy = 'nav_menu'";
        $nb_database->setQuery($sql);
        $menus = $nb_database->loadObjectList();
        $menu_types = array();
        foreach ($menus as $menu)
        {
            $menu_types[$menu->slug] = $menu->name;
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

        if (!$action) {
            $action = 'orders';
        }

        $sql = "SELECT term_id FROM #__terms WHERE slug = '" . esc_sql($menu) . "'";
        $nb_database->setQuery($sql);
        $menu_id = intval($nb_database->loadResult());
        if ($menu_id) {
            wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title' =>  $name,
                'menu-item-url' => $this->site_page_prefix . "&action=$action&task=order&cid=$form_id" . $this->site_page_suffix,
                'menu-item-status' => 'publish'
            ));

            nbf_globals::$message = NBILL_MENU_ITEM_CREATED;
            if (!nbf_common::get_param($_REQUEST, 'nbill_admin_via_fe'))
            {
                $link = "nav-menus.php?menu=$menu_id";
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
        $nb_database = nbf_cms::$interop->database;

        $sql = "SELECT switch_to_ssl FROM #__nbill_configuration WHERE id = 1";
        $nb_database->setQuery($sql);
        $switch_to_ssl = $nb_database->loadResult();
        if ($switch_to_ssl) {
            $return_url = str_replace("http://", "https://", $return_url);
        }

        $credentials = array();
        $credentials['user_login'] = $username;
        $credentials['user_password'] = $password;
        $credentials['remember'] = $remember;
        $user = wp_signon($credentials, false);
        if (is_wp_error($user)) {
            nbf_globals::$message = $user->get_error_message();
            return false;
        } else {
            wp_set_current_user($user->ID);
            $this->get_user();
            if ($return_url) {
                nbf_common::redirect($return_url);
            }
        }
    }

    /**
    * Return the URL for the lost password feature of the CMS
    */
    public function get_lost_password_link()
    {
        return "w" . "p-login.php?action=lostpassword";
    }

    public function get_logout_link($return = "")
    {
        if (!$return)
        {
            $return = '/';
        }
        return wp_logout_url($return);
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
        return $url_to_process;
    }

    /**
    * Update the password for the given user (or the currently logged in user if no user specified)
    * @param string $password The plain text password
    * @param integer $user_id The ID of the user to update (if omitted, the currently logged in user will be updated, if applicable)
    */
    public function update_password($password, $user_id = null)
    {
        if (!$this->demo_mode) {
            if (!$user_id) {
                $user_id = $this->user->id;
            }

            wp_update_user(array(
                'ID' => intval($user_id),
                'user_pass' => $password
            ));

            $this->get_user($user_id);
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
            //Use same mechanism as wordpress does for getting the default from email
            $sitename = strtolower(@$_SERVER['SERVER_NAME']);
            if (substr($sitename, 0, 4) == 'www.') {
                $sitename = substr($sitename, 4);
            }
            $from_email = 'wordpress@' . $sitename;
            nbf_config::$mailfrom = apply_filters('wp_mail_from', $from_email);
            nbf_config::$fromname = apply_filters('wp_mail_from_name', $this->get_site_name());
            nbf_config::$sendmail = false;
            nbf_config::$smtpauth = false;
            nbf_config::$smtpuser = '';
            nbf_config::$smtppass = '';
            nbf_config::$smtphost = '';
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

    public function get_gzip_config_url()
    {
        return "";
    }

    public function register_custom_component_name($product_name, $custom_component_name, $company_name, $product_website)
    {
        $component = str_replace('com_', '', $custom_component_name);
        if (!@file_exists($this->wordpress_plugins_dir() . "/$component/$component.php")) {
            //Attempt to create custom directory/file for transparent usage in front end
            try {
                nbf_file::nb_mkdir_recursive($this->wordpress_plugins_dir() . '/' . $component);
                file_put_contents($this->wordpress_plugins_dir() . "/$component/$component.php", "<?php \n/*\nPlugin Name: $product_name\nPlugin URI: $product_website\nDescription: Mobile Friendly Online Invoicing\nVersion: " . nbf_version::$nbill_version_no . "\nAuthor: Russell Walker\nAuthor URI: http://netshinesoftware.com/\nLicense: http://www.nbill.co.uk/" . "eula.html\nText Domain: $component\n*/\n\n//Ensure this file has been reached through a valid entry point\n(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');\n\n\$nbill_plugin_file = __FILE__;\n\$nbill_component_name = '$component';\nrequire(dirname((realpath(plugin_dir_path(__FILE__)))) . '/nbill/nbill.php');\n");
                file_put_contents($this->wordpress_plugins_dir() . "/$component/controller.php", "<?php \n//Ensure this file has been reached through a valid entry point\n(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');\n\nrequire(dirname((plugin_dir_path(__FILE__))) . '/../nbill/controller.php');");

                //Update default post type
                $options = get_option('nbill-options');
                $post_id = isset($options['default_post_id']) ? intval($options['default_post_id']) : 0;
                if ($post_id > 0) {
                    $sql = "UPDATE posts SET post_type = '$component' WHERE ID = " . intval($post_id);
                    $this->database->setQuery($sql);
                    $this->database->query();
                }

                //Add the active plugin name
                $sql = "SELECT option_value FROM #__options WHERE option_name = 'active_plugins'";
                $this->database->setQuery($sql);
                $plugins = $this->database->loadResult();
                $update = false;
                if ($plugins) {
                    $unser_plugins = @unserialize($plugins);
                    if ($unser_plugins) {
                        foreach ($unser_plugins as $index=>$plugin) {
                            if ($plugin == 'nbill/nbill.php') {
                                $unser_plugins[$index] = $component . '/' . $component . '.php';
                                break;
                            }
                        }
                        $unser_plugins = array_unique($unser_plugins);
                        $update = true;
                    }
                }
                if ($update) {
                    $ser_plugins = @serialize($unser_plugins);
                    if ($ser_plugins) {
                        $sql = "UPDATE #__options SET option_value = '" . $this->database->getEscaped($ser_plugins) . "' WHERE option_name = 'active_plugins'";
                        $this->database->setQuery($sql);
                        $this->database->query();
                    }
                }

                //Register new post type
                register_post_type($component, array(
                        'labels' => array(
                            'name' => __($product_name . ' Front-end'),
                            'singular_name' => __('Front-end Page')
                        ),
                        'can_export'          => false,
                        'exclude_from_search' => true,
                        'hierarchical'        => false,
                        'public'              => false,
                        'publicly_queryable'  => true,
                        'query_var'           => $component,
                        'show_in_menu'        => false,
                        'supports' => false,
                        'has_archive' => false,
                        'rewrite' => array('slug' => $component),
                        'show_ui' => false,
                        'show_in_nav_menus' => true
                    )
                );
                flush_rewrite_rules();

                //Clear any transient data that refers to the previous name
                $sql = "DELETE FROM #__options WHERE option_name LIKE '%transient%' AND option_value LIKE '%nbill%'";
                $this->database->setQuery($sql);
                $this->database->query();

                //Make a note of it in the database so we can uninstall cleanly
                $sql = "SELECT custom_component_names FROM #__nbill_lic" . "ense WHERE id = 1";
                $this->database->setQuery($sql);
                $db_component_names = explode(",", $this->database->loadResult());
                $db_component_names[] = nbill_custom_branding::$component_name;
                $db_component_names = array_unique($db_component_names);
                $sql = "UPDATE #__nbill_lic" . "ense SET custom_component_names = '" . implode(",", $db_component_names) . "' WHERE id = 1";
                $this->database->setQuery($sql);
                $this->database->query();
            } catch (Exception $e) {}
        }

        if (@file_exists($this->wordpress_plugins_dir() . "/$component/$component.php")) {
            //Use custom component name
            $this->component_name = $component;
            define("NBILL_BRANDING_COMPONENT_NAME", nbill_custom_branding::$component_name); //Joomla component name
        }
    }

    public function unregister_custom_component_name($db_component_names)
    {
        $db_component_names = explode(",", $db_component_names);
        foreach ($db_component_names as $db_component_name) {
            if (strlen(trim($db_component_name)) > 0 && $db_component_name != "com_nbill") {
                $component = str_replace('com_', '', $db_component_name);
                if (@file_exists($this->wordpress_plugins_dir() . "/$component")) {
                    //Make sure it really is a directory added by nBill branding!
                    $file_contents = @file_get_contents($this->wordpress_plugins_dir() . "/$component/$component.php");
                    if (strpos($file_contents, 'nbill') !== false) {
                        @nbf_file::remove_directory($this->wordpress_plugins_dir() . "/$component");

                        //Update the active plugin name
                        $sql = "SELECT option_value FROM #__options WHERE option_name = 'active_plugins'";
                        $this->database->setQuery($sql);
                        $plugins = $this->database->loadResult();
                        $update = false;
                        if ($plugins) {
                            $unser_plugins = @unserialize($plugins);
                            if ($unser_plugins) {
                                foreach ($unser_plugins as $index=>$plugin) {
                                    if ($plugin == $component . '/' . $component . '.php') {
                                        $unser_plugins[$index] = 'nbill/nbill.php';
                                        $unser_plugins = array_unique($unser_plugins);
                                        $update = true;
                                        break;
                                    }
                                }
                            }
                        }
                        if ($update) {
                            $ser_plugins = @serialize($unser_plugins);
                            if ($ser_plugins) {
                                $sql = "UPDATE #__options SET option_value = '" . $this->database->getEscaped($ser_plugins) . "' WHERE option_name = 'active_plugins'";
                                $this->database->setQuery($sql);
                                $this->database->query();
                            }
                        }

                        //Clear any transient data that refers to the previous name
                        $sql = "DELETE FROM #__options WHERE option_name LIKE '%transient%' AND option_value LIKE '%$component%'";
                        $this->database->setQuery($sql);
                        $this->database->query();
                    }
                }
            }
        }
        $this->component_name = "nbill";
        //Update default post type
        $options = get_option('nbill-options');
        $post_id = isset($options['default_post_id']) ? intval($options['default_post_id']) : 0;
        if ($post_id > 0) {
            $sql = "UPDATE posts SET post_type = 'nbill' WHERE ID = " . intval($post_id);
            $this->database->setQuery($sql);
            $this->database->query();
        }

        //We can forget about it now (even if it didn't remove properly, there is nothing more we can do about it)
        $this->database->setQuery("UPDATE #__nbill_lic" . "ense SET custom_component_names = '' WHERE id = 1");
        $this->database->query();
    }
}