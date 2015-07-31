<?php
/**
* Interop Class File for Mambo 4.6
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
* This class provides interop functions specific to Mambo 4.6.x. Where features
* are shared between Joomla 1.0 and Mambo, the parent class ([@see nbf_interop_mambo_4_5])
* provides the functionality.
*
* @package nBill Framework Interop
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_interop_mambo_4_6 extends nbf_interop_mambo_4_5
{
    /** @var string Name of CMS (for display in error reports) */
    public $cms_name = "Mambo";
    /** @var string Version number of CMS (for display in error reports) */
    public $cms_version = "4.6.x";

    /**
    * Add support for Nokkaew, if applicable
    */
    public function install_tasks()
    {
        parent::install_tasks(); //Add icon to administrator menu option
        if (file_exists($this->site_base_path . '/administrator/components/com_nokkaew/config.nokkaew.php'))
        {
            @copy($this->nbill_admin_base_path . "/translation/nbill_additional_links.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_additional_links.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_discounts.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_discounts.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_order_form.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_order_form.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_order_form_fields.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_order_form_fields.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_order_form_fields_options.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_order_form_fields_options.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_order_form_pages.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_order_form_pages.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_payment_plans.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_payment_plans.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_product.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_product.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_product_category.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_product_category.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_profile_fields.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_profile_fields.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_profile_fields_options.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_profile_fields_options.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_reminders.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_reminders.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_shipping.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_shipping.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_tax.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_tax.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_vendor.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_vendor.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_xref_country_codes.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_xref_country_codes.xml");
            @copy($this->nbill_admin_base_path . "/translation/nbill_xref_eu_country_codes.xml", $this->site_base_path . "/administrator/components/com_nokkaew/contentelements/nbill_xref_eu_country_codes.xml");
        }
    }

    /**
    * Remove Nokkaew files, if applicable
    */
    public function uninstall_tasks()
    {
        parent::uninstall_tasks();
        if (@file_exists($this->site_base_path . "/administrator/components/com_nokkaew/contentelements/"))
        {
            $old_nokkaew_files = scandir(nbf_cms::$interop->site_base_path . "/administrator/components/com_nokkaew/contentelements/");
            foreach ($old_nokkaew_files as $old_nokkaew_file)
            {
                if (substr(basename($old_nokkaew_file), 0, 7) == "netinv_" || substr(basename($old_nokkaew_file), 0, 6) == "nbill_" || nbf_common::nb_strtolower(substr(basename($old_nokkaew_file), 0, 17)) == "translationnbill_")
                {
                    @unlink(nbf_cms::$interop->site_base_path . "/administrator/components/com_nokkaew/contentelements/" . $old_nokkaew_file);
                }
            }
        }
    }

    /**
    * Convert a plain text password into a hash that can be stored in the database and compared
    * @param string $plain_text_password
    * @return string A hash
    */
    public function get_password_hash($plain_text_password)
    {
        return md5($plain_text_password);
    }

    /**
    * Log the current user out then back in again
    * @param nb_user $user Information about the user - typically just the ID is needed, but if required by the CMS,
    * the other data can be updated in the session variables instead of actually logging out and back in.
    * @param string $url URL to redirect to after logging back in (if applicable)
    * */
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

        require_once($this->site_base_path . '/includes/authenticator.php');
        @$authenticator =& mamboAuthenticator::getInstance();
        nbf_globals::$message = "";
        $authenticator->authenticateUser(nbf_globals::$message, $user_details->username, $user_details->password);

        if ($url)
        {
            nbf_common::redirect($url);
            exit;
        }
    }
}