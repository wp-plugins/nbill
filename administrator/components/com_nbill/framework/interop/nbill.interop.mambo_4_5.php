<?php
/**
* Abstract Interop Class File for Mambo family of CMSs (excluding Joomla 1.5)
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
* This abstract class provides interop functions that are shared between the Mambo family
* of CMSs - in particular (at time of writing), Mambo 4.6.x and Joomla 1.0.x. Could
* also possibly be used for Elxis, Aliro, Mia, etc.
*
* @package nBill Framework Interop
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
abstract class nbf_interop_mambo_4_5 extends nbf_interop
{
    /** @var string Name of CMS (for display in error reports) */
    public $cms_name = "Mambo";
    /** @var string Version number of CMS (for display in error reports) */
    public $cms_version = "4.5.x";

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
            $sql = "SELECT id FROM #__menu WHERE id = $this_item_id AND access = 0";
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
                    WHERE #__nbill_configuration.id = 1 AND #__menu.access = 0";
            $nb_database->setQuery($sql);
            $this_item_id = intval($nb_database->loadResult());
        }
        if (!$this_item_id)
        {
            //Use home page menu item ID or bust
            $sql = "SELECT id FROM #__menu WHERE menutype='mainmenu' AND published = 1 AND access = 0 ORDER BY ordering";
            $nb_database->setQuery($sql);
            $this_item_id = intval($nb_database->loadResult());
        }
        return $this_item_id ? "&Itemid=$this_item_id" : "";
    }

    /**
    * Set records per page
    */
    protected function initialise()
    {
        parent::initialise();
        global $mosConfig_list_limit;
        $this->records_per_page = $mosConfig_list_limit;
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

    /**
    * Indicates whether or not to compress page output
    */
    public function use_gzip()
    {
        global $mosConfig_gzip;
        return $mosConfig_gzip;
    }

    /**
    * Get the database connection settings from the configuration file
    * @return nbf_db_connection
    */
    public function get_db_connection_settings()
    {
        global $mosConfig_host, $mosConfig_db, $mosConfig_user, $mosConfig_password, $mosConfig_dbprefix;

        $host = $mosConfig_host;
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

        if (nbf_common::nb_strlen(trim($mosConfig_db)) > 0)
        {
            return new nbf_db_connection($host, $mosConfig_db, $mosConfig_user, $mosConfig_password, $mosConfig_dbprefix, $port);
        }
    }

    /**
    * Attempt to convert old-style language name to ISO standard language/country pair.
    * Language code is lower case and based on ISO 639-1. Country code is upper case and
    * based on ISO 3166. The 2 codes (2 characters each) are separated by a dash. The
    * most common languages with more than one variation are hard-coded. All others are
    * looked up in a CSV file of language codes (CSV file contains the most common
    * languages at the top, and is interrogated line-by-line, so processing time is
    * reduced for the more common languages). An attempt is then made to find a matching
    * nBill language folder (for any country with that language code). If none found,
    * nothing is returned - the caller defaults to British en-GB (en-GB).
    * @return string Language code in format ll-CC where ll is the 2-character ISO 639-1 language code and CC is the 2 character ISO 3166 country code.
    */
    protected function get_language()
    {
        global $mosConfig_lang;
        $lang = "";

        if ($mosConfig_lang)
        {
            //Try to match old-style language name to an installed language pack
            //eg. "en-GB" to "en-US" (if installed) or "en-GB" (if not)
            $languages = array_diff(scandir($this->nbill_admin_base_path . "/language/"), array('.', '..'));

            if ($languages)
            {
                //If there is an exact match, use it
                foreach ($languages as $language)
                {
                    if (nbf_common::nb_strtoupper($language) == nbf_common::nb_strtoupper($mosConfig_lang))
                    {
                        $lang = $language;
                        break;
                    }
                }

                if (!$lang)
                {
                    //For most common languages, do a straight (hard-coded) comparison
                    switch ($mosConfig_lang)
                    {
                        case "en-GB":
                            //Check for en-US or en-GB
                            foreach ($languages as $language)
                            {
                                switch ($language)
                                {
                                    case "en-US":
                                        $lang = $language;
                                        break;
                                }
                                if ($lang)
                                {
                                    break;
                                }
                            }
                            break;
                        case "french":
                            //fr-FR, fr-CA
                            foreach ($languages as $language)
                            {
                                switch ($language)
                                {
                                    case "fr-CA":
                                    case "fr-FR":
                                        $lang = $language;
                                        break;
                                }
                                if ($lang)
                                {
                                    break;
                                }
                            }
                            break;
                        case "german":
                            //de-DE, de-AT
                            foreach ($languages as $language)
                            {
                                switch ($language)
                                {
                                    case "de-AT":
                                    case "de-DE":
                                        $lang = $language;
                                        break;
                                }
                                if ($lang)
                                {
                                    break;
                                }
                            }
                            break;
                        case "portuguese":
                            //pt-PT, pt-BR
                            foreach ($languages as $language)
                            {
                                switch ($language)
                                {
                                    case "pt-PT":
                                    case "pt-BR":
                                        $lang = $language;
                                        break;
                                }
                                if ($lang)
                                {
                                    break;
                                }
                            }
                            break;
                        case "spanish":
                            //es-ES, es-AR, es-MX
                            foreach ($languages as $language)
                            {
                                switch ($language)
                                {
                                    case "es-MX":
                                    case "es-AR":
                                    case "es-ES":
                                        $lang = $language;
                                        break;
                                }
                                if ($lang)
                                {
                                    break;
                                }
                            }
                            break;
                        default:
                            //Lookup first part (lang) in iso csv file
                            $handle = fopen($this->nbill_admin_base_path . "/framework/iso/iso-languages.csv", "r");
                            if ($handle)
                            {
                                while (!feof($handle))
                                {
                                    $line = explode(",", str_replace("\r", "", str_replace("\n", "", fgets($handle))));
                                    if (count($line) == 2 && nbf_common::nb_strtolower($line[1]) == nbf_common::nb_strtolower($mosConfig_lang))
                                    {
                                        $lang_code = $line[0];
                                        //Find any language with this code
                                        foreach ($languages as $language)
                                        {
                                            if (nbf_common::nb_strtolower(substr($language, 0, 2)) == nbf_common::nb_strtolower($lang_code))
                                            {
                                                $lang = $language;
                                                break;
                                            }
                                        }
                                        if ($lang)
                                        {
                                            break;
                                        }
                                    }
                                }
                                fclose($handle);
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

    public function set_cms_language($language)
    {
        global $mosConfig_lang;
        switch(substr($language, 0, 2))
        {
            case "en":
                $mosConfig_lang = "english";
                break;
            case "fr":
                $mosConfig_lang = "french";
                break;
            case "de":
                $mosConfig_lang = "german";
                break;
            case "pt":
                $mosConfig_lang = "portuguese";
                break;
            case "es":
                $mosConfig_lang = "spanish";
                break;
            case "nl";
                $mosConfig_lang = "dutch";
                break;
            case "el";
                $mosConfig_lang = "greek";
                break;
            case "it":
                $mosConfig_lang = "italian";
                break;
            case "nb":
                $mosConfig_lang = "norwegian";
                break;
            case "pl":
                $mosConfig_lang = "polish";
                break;
            case "sl":
                $mosConfig_lang = "slovenian";
                break;
        }
    }

    /**
    * Use the live site setting from the config file. If this is not available (it should be!), default
    * to the parent function (which just returns the root location where the call was made from)
    * @return string The base part of the URL (to the root folder where the CMS is installed)
    */
    protected function get_live_site()
    {
        global $mosConfig_live_site;
        if ($mosConfig_live_site)
        {
            require_once(dirname(__FILE__) . "/../nbill.config.php");
            if ((@$_SERVER['HTTPS'] && nbf_common::nb_strtolower(@$_SERVER['HTTPS']) != 'off') || @$_SERVER['SERVER_PORT'] == nbf_config::$ssl_port)
            {
                $mosConfig_live_site = str_replace("http://", "https://", $mosConfig_live_site);
            }
            return $mosConfig_live_site;
        }
        else
        {
            return parent::get_live_site();
        }
    }

    /**
    * Set the URL prefixes that tell Mambo-family CMSs to direct calls to nBill, and record the
    * URL path to the component files (for including the admin stylesheet and referencing images).
    */
    protected function set_url_prefixes()
    {
        $this->cms_home_page = "index2.php";
        $this->admin_page_prefix = "index2.php?option=" . NBILL_BRANDING_COMPONENT_NAME;
        $this->admin_popup_page_prefix = "index3.php?option=" . NBILL_BRANDING_COMPONENT_NAME;
        $this->site_page_prefix = "index.php?option=" . NBILL_BRANDING_COMPONENT_NAME;
        $this->site_popup_page_prefix = "index2.php?option=" . NBILL_BRANDING_COMPONENT_NAME;
        $this->nbill_admin_url_path = $this->live_site . "/administrator/components/com_nbill";
        $this->nbill_site_url_path = $this->live_site . "/components/com_nbill";
        $this->admin_component_uninstaller = "index2.php?option=com_installer&amp;element=component";
        $this->user_editor_url = "index2.php?option=com_users&task=editA&id=%s&hidemainmenu=1";
    }

    /**
    * If the site name is recorded in the global config variable, return it, otherwise, call on the parent to default to the live site value
    * @return string The name of the website according to the config file
    */
    protected function get_site_name()
    {
        global $mosConfig_sitename;
        if ($mosConfig_sitename)
        {
            return stripslashes(html_entity_decode($mosConfig_sitename));
        }
        else
        {
            return parent::get_site_name();
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
            global $my;
            $user_id = intval($my->id);

            if (!$user_id)
            {
                global $mainframe;
                $user_id = @$mainframe->_session->userid;
            }
        }
        else
        {
            $return_user = true;
        }
        if ($user_id)
        {
            $sql = "SELECT #__core_acl_aro_groups.group_id AS gid, #__core_acl_aro_groups.name AS group_name,
                    #__users.name, #__users.username, #__users.email, #__users.id
                    FROM #__core_acl_aro_groups
                    INNER JOIN #__users ON #__core_acl_aro_groups.group_id = #__users.gid
                    WHERE #__users.id = $user_id";
            $nb_database->setQuery($sql);
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
        global $mosConfig_allowUserRegistration;
        return $mosConfig_allowUserRegistration ? "index.php?option=com_registration&task=register" : "";
    }

    public function cms_editor_supported()
    {
        return false;
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
        global $mosConfig_uniquemail, $my;
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
        if (nbf_common::nb_strlen($password) > 50)
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
        if ($my->id)
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

        if ($mosConfig_uniquemail)
        {
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
                $sql = "SELECT aro_id FROM #__core_acl_aro WHERE `value` = $user_id";
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

        global $my;
        $copy_my = $my;

        $nb_database = $this->database;

        $user_id = intval($user_id);
        $new_group = intval($new_group);

        nbf_common::fire_event("user_group_changed", array("user_id"=>$user_id, "new_group"=>$new_group, "new_level"=>$new_group)); //new_level deprecated

        $sql = "SELECT name FROM #__core_acl_aro_groups WHERE group_id = $new_group";
        $nb_database->setQuery($sql);
        $groupname = $nb_database->loadResult();

        $sql = "SELECT aro_id FROM #__core_acl_aro WHERE value = " . $user_id;
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
            $this->log_out_then_in_again(new nb_user($user_id));
        }

        $GLOBALS['my'] = $copy_my;
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
    * Return the group name for the given group id
    * @param int $gid
    * @return The group name
    */
    public function get_user_group_name($gid)
    {
        $nb_database = nbf_cms::$interop->database;
        $sql = "SELECT `name` FROM #__core_acl_aro_groups WHERE group_id = $gid";
        $nb_database->setQuery($sql);
        return $nb_database->loadResult();
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
        $sql = "SELECT params FROM #__mambots WHERE folder = 'system' AND element = 'nbill_account_expiry'";
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
        $sql = "SELECT group_id, lft, rgt FROM #__core_acl_aro_groups WHERE
                    group_id IN ($frontend, $backend, $level_a, $level_b)";
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
            $sql = "SELECT * FROM #__core_acl_aro_groups WHERE lft > $min AND lft < $max ORDER BY lft ASC";
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
    * Check whether or not the user subscription mambot is installed and published
    */
    public function user_sub_plugin_present()
    {
        $sql = "SELECT published FROM #__mambots WHERE name LIKE '%nBill%' AND element = 'nbill_user_subscription'";
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

        $sql = "INSERT INTO #__menu (menutype, name, link, type, published, ordering) VALUES
                    ('$menu', '$name', '" . $this->site_page_prefix . "&action=$action&task=order&cid=$form_id" . $this->site_page_suffix . "', 'url', 1, 99);";
        $nb_database->setQuery($sql);
        $nb_database->query();
        $menu_id = $nb_database->insertid();
        if ($menu_id)
        {
            nbf_globals::$message = NBILL_MENU_ITEM_CREATED;
            if (!nbf_common::get_param($_REQUEST, 'nbill_admin_via_fe'))
            {
                $link = "index2.php?option=com_menus&menutype=$menu&task=edit&id=$menu_id&hidemainmenu=1";
                nbf_globals::$message .= "\n\n<a href=\"$link\">" . NBILL_CLICK_HERE . "</a> " . NBILL_MENU_ITEM_TO_EDIT;
            }
        }
        else
        {
            nbf_globals::$message = NBILL_MENU_ITEM_NOT_CREATED;
        }
        return $menu_id;
    }

    public function check_account_expiry()
    {
        //Just try including the mambot file, if it exists, then call the function
        if (file_exists($this->site_base_path . "/mambots/system/_nbill_user_subscription/nbill_user_subscription.php"))
        {
            include_once($this->site_base_path . "/mambots/system/_nbill_user_subscription/nbill_user_subscription.php");
        }
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

        $_POST['username'] = $username;
        $_POST['passwd'] = $password;
        $_REQUEST['username'] = $username;
        $_REQUEST['passwd'] = $password;

        $mainframe->login();
        $this->get_user();
        if ($return_url)
        {
            nbf_common::redirect($return_url);
        }
    }

    /**
    * Return the URL for the lost password feature of the CMS
    */
    public function get_lost_password_link()
    {
        return "index.php?option=com_registration&task=lostPassword";
    }

    public function get_logout_link($return = "")
    {
        return "index.php?option=com_login&task=logout";
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
        if (!$email_as_username && nbf_common::nb_strlen($username) > 25)
        {
            $username = substr($username, 0, 25);
        }
        if (nbf_common::nb_strlen($username) < 3)
        {
            return false;
        }
        if (preg_match("/[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+]/i", $username) || nbf_common::nb_strlen($username) < 3)
        {
            return false;
        }
        return true;
    }

    public function process_url($url_to_process)
    {
        return defined('NBILL_ADMIN') ? $url_to_process : sefRelToAbs($url_to_process);
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
                $user_id = $this->user->gid;
            }
            $pwd_hash = md5($password);
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
    * Set the email configuration settings from the Mambo-style configuration file
    */
    public function get_email_config()
    {
        global $mosConfig_mailer, $mosConfig_mailfrom, $mosConfig_fromname, $mosConfig_sendmail, $mosConfig_smtpauth, $mosConfig_smtpuser, $mosConfig_smtppass, $mosConfig_smtphost;
        require_once($this->nbill_admin_base_path . "/framework/nbill.config.php");
        if (nbf_config::$mailer == "[CMS]")
        {
            nbf_config::$mailfrom = $mosConfig_mailfrom;
            nbf_config::$fromname = $mosConfig_fromname;
            nbf_config::$sendmail = $mosConfig_sendmail;
            nbf_config::$smtpauth = $mosConfig_smtpauth;
            nbf_config::$smtpuser = $mosConfig_smtpuser;
            nbf_config::$smtppass = $mosConfig_smtppass;
            nbf_config::$smtphost = $mosConfig_smtphost;
        }
    }

    /**
    * @return boolean Whether or not to show a warning about gzip causing problems on certain features (only return true if
    * gzip is turned on and does cause a problem on this CMS)
    */
    public function show_gzip_warning()
    {
        global $mosConfig_gzip;
        return $mosConfig_gzip;
    }

    /**
    * @return string The URL to the configuration page in the CMS where gzip can be turned off
    */
    public function get_gzip_config_url()
    {
        return "index2.php?option=com_config&hidemainmenu=1";
    }
}