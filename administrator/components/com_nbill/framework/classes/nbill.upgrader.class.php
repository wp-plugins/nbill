<?php
/**
* Check the nBill server to see if an update is available, download and apply if so.
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nbf_upgrader
{
    /** @var boolean Whether or not a new version is available for download */
    static public $new_version_available = false;
    /** @var string Version number of the latest version available */
    static public $latest_version = "";
    /** @var string Any notes about the new version as specified on the nBill server */
    static public $latest_description = "";
    /** @var boolean Whether or not it is possible to upgrade automatically with a single click (based on upgrade requirements, not local conditions) */
    static public $latest_auto = false;
    /** @var boolean Whether or not the component was blocked from checking for a new version (eg. if CURL and sockets are both disabled in PHP) */
    static public $unable_to_check_version = false;
    /** @var boolean Whether or not we are connecting to an older version checker (and can therefore only check up to a certain version number, which requires a warning) **/
    static public $old_version_checker = false;

    /**
    * Migrates data from nBill 1.2.6 onwards ONLY
    */
    static function migrate()
    {
        $script_name = nbf_cms::$interop->nbill_admin_base_path . "/framework/database/upgrade/upgrade_1.2.999_to_2.0.0.php";
        if (file_exists($script_name))
        {
            include_once($script_name);
            $function_name = "upgrade_1_2_999_to_2_0_0";
            if (function_exists($function_name))
            {
                return $function_name();
            }
        }
    }

    /**
    * Connect to the nBill server to check whether a newer version is available (results are returned in the static members)
    * @param boolean $manual_check Whether or not the user has explicitly requested a version check (if not, the check will only be performed if the config settings allow it)
    * @param boolean $auto_update Whether or not to go ahead and try to install the upgrade, if available
    */
    static function check_version($manual_check = false, $auto_update = false)
    {
        require_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.remote.class.php");
        $nbf_interop = nbf_cms::$interop;
	    self::$unable_to_check_version = false;
	    $check_required = null;
	    $update_required = null;
	    if ($manual_check)
	    {
		    $check_required = true;
		    $update_required = $auto_update;
	    }
	    else
	    {
		    //Check whether to check!
		    $sql = "SELECT version_auto_check, auto_update FROM #__nbill_configuration WHERE id = 1";
		    $nbf_interop->database->setQuery($sql);
		    $nbf_interop->database->loadObject($config);
		    $check_required = $config->version_auto_check;
		    $update_required = $config->auto_update;
	    }

	    if ($check_required && !$nbf_interop->demo_mode)
	    {
            //See what the server says...
            self::$old_version_checker = false; //We will detect whether we are using an older version check and warn if necessary
            $path = NBILL_BRANDING_VERSION_CHECK_PATH;
            if ($manual_check) {
                $path .= (strpos($path, '?') !== false ? '&' : '?') . 'manual=1';
            }
            $results = nbf_remote::get_remote(NBILL_BRANDING_VERSION_HOST, $path, array(), 5);
            if (strpos($results, '###') !== false) {
                self::$old_version_checker = true;
                $results = substr($results, nbf_common::nb_strpos($results, "NBILL_VERSION="));
		        $version_data = explode("###\n", $results);
            } else {
                $version_data = json_decode($results, true);
            }
		    if (count($version_data) > 2)
		    {
                foreach ($version_data as $key=>$value)
                {
                    if (is_numeric($key)) {
                        $line_data = explode("=", $value);
                        if (count($line_data) >= 2)
                        {
                            $key = strtolower(substr($line_data[0], 6));
                            $value = '';
                            array_shift($line_data);
                            $value = implode('=', $line_data);
                        }
                    }
                    switch ($key) {
                        case "version":
                            self::$latest_version = $value;
                            break;
                        case "auto_update":
                        case "auto_update_from_" . nbf_version::$nbill_version_no:
                            self::$latest_auto = $value == "true";
                            break;
                        case "description":
                        case "description_" . nbf_version::$nbill_version_no:
                            self::$latest_description = html_entity_decode($value);
                            break;
                    }
                }

                $nbf_version = new nbf_version();
			    if ($nbf_version->compare("<", self::$latest_version))
			    {
				    self::$new_version_available = true;
			    }
		    }
		    else
		    {
			    self::$unable_to_check_version = true;
                self::$latest_description = $results;
		    }

		    /*if (self::$new_version_available && $update_required && !$nbf_interop->demo_mode)
		    {
			    if (self::$latest_auto)
			    {
                    //Check whether we have write access to the files - if not, prompt for FTP details
                    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.file.class.php");
                    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.config.php");
                    $use_ftp = false;
                    $error_message = "";

                    nbf_cms::$interop->get_ftp_details(nbf_config::$ftp_address, nbf_config::$ftp_port, nbf_config::$ftp_username, nbf_config::$ftp_password, nbf_config::$ftp_root);
                    if (!nbf_file::is_test_file_writable($use_ftp, $error_message))
                    {
                        //Prompt for new FTP details
                        $error_message .= ". " . NBILL_CFG_FTP_TRY_AGAIN;
                        nbf_cms::$interop->prompt_for_ftp_details($error_message);
                    }
                    else
                    {
				        //Try installing it!
				        $copy_errors = array();
				        $db_errors = "";
				        $upgrade_success = self::auto_upgrade(self::$latest_version, $copy_errors, $db_errors);
				        if ($upgrade_success)
				        {
					        nbf_globals::$message = NBILL_PATCH_INSTALLED;
					        self::$new_version_available = false;
				        }
				        else
				        {
					        //Disable auto-updates
					        $sql = "UPDATE #__nbill_configuration SET auto_update = 0 WHERE id = 1";
					        $nbf_interop->database->setQuery($sql);
					        $nbf_interop->database->query();

					        if (count($copy_errors) > 0)
					        {
						        nbf_globals::$message = NBILL_PATCH_COPY_FAILURES . "&nbsp;<br />";
						        nbf_globals::$message .= sprintf(NBILL_AUTO_UPDATE_DISABLED, "<a href=\"" . $nbf_interop->admin_page_prefix . "&action=configuration\">", "</a>") . "<br /><br />";
						        foreach ($copy_errors as $copy_error)
						        {
							        nbf_globals::$message .= $copy_error . "<br />";
						        }
					        }
					        else if (nbf_common::nb_strlen($db_errors) > 0)
					        {
						        nbf_globals::$message = NBILL_PATCH_DB_ERRORS . "&nbsp;<br />";
						        nbf_globals::$message .= sprintf(NBILL_AUTO_UPDATE_DISABLED, "<a href=\"" . $nbf_interop->admin_page_prefix . "&action=configuration\">", "</a>"). "<br /><br />";
						        nbf_globals::$message .= $db_errors;
					        }
					        else
					        {
                                if (nbf_common::nb_strlen(nbf_globals::$message) == 0)
                                {
						            nbf_globals::$message = NBILL_PATCH_FAILED_TO_INSTALL . "&nbsp;<br />" . sprintf(NBILL_AUTO_UPDATE_DISABLED, "<a href=\"" . $nbf_interop->admin_page_prefix . "&action=configuration\">", "</a>");
                                }
					        }
				        }
                    }
			    }
		    }*/
	    }
    }

    
}