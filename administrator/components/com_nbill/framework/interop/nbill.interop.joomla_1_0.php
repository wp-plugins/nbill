<?php
/**
* Interop Class File for Joomla 1.0
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Load the class definition for the parent class
require_once(dirname(__FILE__) . "/nbill.interop.mambo_4_5.php");

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* This class provides interop functions specific to Joomla 1.0.x. Where features
* are shared between Joomla 1.0 and Mambo, the parent class ([@see nbf_interop_mambo_4_5])
* provides the functionality.
*
* @package nBill Framework Interop
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_interop_joomla_1_0 extends nbf_interop_mambo_4_5
{
    /** @var string Stores the minor version number to allow for behavioural changes */
    private $_minor_version;
    /** @var string Name of CMS (for display in error reports) */
    public $cms_name = "Joomla!";
    /** @var string Version number of CMS (for display in error reports) */
    public $cms_version = "1.0.x";

    /**
    * Override the accessor for demo_mode to return the flag from the CMS
    * @return mixed
    */
    public function __get($property)
    {
        switch ($property)
        {
            case "demo_mode":
                if (isset($GLOBALS['_VERSION']) && isset($GLOBALS['_VERSION']->SITE) && !$GLOBALS['_VERSION']->SITE)
                {
                    return true;
                }
                else
                {
                    return parent::__get($property);
                }
            default:
                return parent::__get($property);
        }
    }

    /**
    * Calls parent constructor and loads the minor version number
    */
    function __construct()
    {
        parent::__construct();
        $this->_minor_version = @$GLOBALS['_VERSION']->DEV_LEVEL;
    }

    /**
    * Override character encoding
    */
    protected function initialise()
    {
        parent::initialise();
        $this->char_encoding = "iso-8859-1";
        $this->db_charset = "latin2";
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

        //There is an obscure bug in Joomla 1.0 which causes Firefox 4 to refresh the page when this function is used in the back end
        //As such, and given that Joomla 1.0 is rarely used these days, a non-compliant approach (just output the header within the body)
        //is safer than doing it 'properly' and suffering unwanted page refreshes.
        if (defined('NBILL_ADMIN'))
        {
            echo $content;
        }
        else
        {
            $content = trim($content);
            if ($mainframe && method_exists($mainframe, "addCustomHeadTag"))
            {
                if (method_exists($mainframe, "getHead"))
                {
                    $existing_head = $mainframe->getHead();
                    if (strpos($existing_head, $content) === false) //Make sure it has not already been added
                    {
                        $mainframe->addCustomHeadTag($content);
                    }
                }
                else
                {
                    $mainframe->addCustomHeadTag($content);
                }
            }
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
        for ($j = $buffer_count - 1; $j >= 0; $j--)
        {
            ob_start();
            echo $buffers[$j];
        }
    }

    /**
    * Convert a plain text password into a hash that can be stored in the database and compared
    * @param string $plain_text_password
    * @return string A hash
    */
    public function get_password_hash($plain_text_password)
    {
        if ($this->_minor_version < 13)
        {
            return md5($plain_text_password);
        }
        return parent::get_password_hash($plain_text_password);
    }

    /**
    * Log the current user out then back in again
    * @param nb_user $user Information about the user - typically just the ID is needed, but if required by the CMS,
    * the other data can be updated in the session variables instead of actually logging out and back in.
    * @param string $url URL to redirect to after logging back in (if applicable)
    */
    function log_out_then_in_again($user, $url = "")
    {
        $nb_database = $this->database;
        global $mainframe;
        global $my;

        $user->id = intval($user->id);

        //Get username and password so we can log in again in a mo...
        $user_details = null;
        $sql = "SELECT name, username, password, email FROM #__users WHERE id = $user->id";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($user_details);

        //Need to clear the session (log out, and log in again) so that the gid changes take effect
        @$mainframe->logout();
        if (method_exists($mainframe, "initSession"))
        {
            @$mainframe->initSession();
        }
        $my = null;

        if ($this->_minor_version > 12)
        {
            //Joomla 1.0.13 introduced a hardened password - and must use 'remember me' functionality to enable programmatic login
            $hash = mosHash(nbf_common::get_param($_SERVER,'HTTP_USER_AGENT'));
            $user_hash = md5($user_details->username . $hash);
            $password_hash = md5(substr($user_details->password, 0, nbf_common::nb_strpos($user_details->password, ":")) . $hash);
            $mainframe->login($user_hash, $password_hash, 1, $user->id);
        }
        else
        {
            $mainframe->login($user_details->username, $user_details->password);
        }

        if ($url)
        {
            nbf_common::redirect($url);
            exit;
        }
    }

    /**
    * Update the password for the given user (or the currently logged in user if no user specified)
    * @param string $password The plain text password
    * @param integer $user_id The ID of the user to update (if omitted, the currently logged in user will be updated, if applicable)
    */
    public function update_password($password, $user_id = null)
    {
        if (!$this->demo_mode) {
            if (nbf_common::nb_strlen(str_replace("*", "", $password)) > 0)
            {
                $nb_database = nbf_cms::$interop->database;
                if ($this->_minor_version > 12)
                {
                    if (!$user_id)
                    {
                        $user_id = $this->user->gid;
                    }
                    $pwd_hash = $this->get_password_hash($password);
                    $sql = "UPDATE #__users SET password = '$pwd_hash' WHERE id = " . intval($user_id);
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                    $this->user->password = $pwd_hash;
                }
                else
                {
                    parent::update_password($password, $user_id);
                }
            }
        }
    }

    public function get_login_spoof_checker()
    {
        if (function_exists('josSpoofValue'))
        {
            return '<input type="hidden" name="' . josSpoofValue(1) . '" value="1" />';
        }
    }
}