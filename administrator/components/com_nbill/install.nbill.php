<?php
/**
* Main installation and upgrade file
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function com_install($disable_phone_home = false)
{    $pre_req_ok = false;
    require(dirname(__FILE__) . "/admin.init.php");
    if (!$pre_req_ok)
    {
        return;
    }

    $nb_database = nbf_cms::$interop->database;
    $db_errors = array();
    @ini_set("memory_limit", "128M"); //Shouldn't be needed, but just in case

    if (!defined("NBILL_BRANDING_NAME"))
    {
        define("NBILL_BRANDING_NAME", "nBill"); //Name of the product
        define("NBILL_BRANDING_TRADEMARK_SYMBOL", ""); //If product name is trademarked, TM &#8482; or R &#174; symbol can go here. It is a criminal offense in the UK to use a trademark symbol if you are not entitled to do so.
        define("NBILL_BRANDING_COMPANY", "Netshine Software Limited"); //Name of the company providing support
        define("NBILL_BRANDING_WEBSITE", "www.nbill.co.uk"); //Product website
        define("NBILL_BRANDING_EULA", "www.gnu.org/licenses/gpl-2.0.html"); //Link to License agreement
        define("NBILL_BRANDING_HTML2PS", "www.nbill.co.uk/pdf-generation.html"); //Link to explanation of html2ps script
        define("NBILL_BRANDING_DOCUMENTATION", "www.nbill.co.uk/help/"); //Link to documentation
        define("NBILL_BRANDING_VERSION_HOST", "nbill.co.uk"); //Host name from which to retrieve version information and/or auto upgrades
        define("NBILL_BRANDING_VERSION_CHECK_PATH", "/api/v1/version_check.php"); //Path to version checking file (including leading slash)
    }

    //Perform install tasks peculiar to CMS
    nbf_cms::$interop->install_tasks();

    //Check what version already exists (if any) for upgrade
    $sql = "SELECT * FROM #__nbill_version WHERE id = 1";
    $nb_database->setQuery($sql);
    $version_info = false;
    $nb_database->loadObject($version_info);

    if (!$version_info)
    {
        //New installation
        require_once(nbf_cms::$interop->nbill_admin_base_path . "/install.new.php");
        new_db_install();

        if ($disable_phone_home) {
            $sql = "UPDATE #__nbill_configuration SET version_auto_check = 0, auto_check_eu_vat_rates = 0 WHERE id = 1";
            $nb_database->setQuery($sql);
            $nb_database->query();
        }

        if (count(nbf_globals::$db_errors) == 0)
        {
            @$retVal = NBILL_INSTALL_COMPLETE;
            if ($retVal == "NBILL_INSTALL_COMPLETE")
            {
                $retVal = "Installation complete.";
            }
            return $retVal;
        }
        else
        {
            @$retVal = NBILL_DB_ERRORS;
            if ($retVal == "NBILL_DB_ERRORS")
            {
                $retVal = "The component has been installed, but one or more database errors occurred during the installation process. The errors are displayed below.";
            }
            $retVal .= '<br /><br />';
            $retVal .= implode("<br />", nbf_globals::$db_errors);
            return $retVal;
        }
    }
    else
    {
        //Upgrade
        $upgraded = false;
        switch ($version_info->software_version)
        {
            case "2.0.4":
            case "2.0.5":
            case "2.0.6":
            case "2.0.7":
            case "2.0.8":
                //Normally these would be split among the cases above, but due to a bug in the upgrader, all upgrade functions were skipped prior to 2.0.9
                include_once(dirname(__FILE__) . '/upgrade/upgrade_2_0_x.php');
                upgrade_2_0_4_to_2_0_5();
                upgrade_2_0_5_to_2_0_6();
                upgrade_2_0_6_to_2_0_7();
                upgrade_2_0_7_to_2_0_8();
                upgrade_2_0_8_to_2_0_9();
                //Fall through
            default:
                if (substr($version_info->software_version, 0, 4) == "2.0.") //Any 2.0 version including future versions of nBill Lite that use the 2.0 database structure
                {
                    run_upgrade_script('2.0.x', '2.1.0');
                    run_upgrade_script('2.1.0', '2.1.1');
                    run_upgrade_script('2.1.1', '2.2.0');
                    run_upgrade_script('2.2.0', '2.3.0');
                    run_upgrade_script('2.3.0', '2.3.1');
                    run_upgrade_script('2.3.1', '2.3.2');
                    run_upgrade_script('2.3.2', '2.3.3');
                    run_upgrade_script('2.3.3', '2.3.4');
                    run_upgrade_script('2.3.4', '2.4.0');
                    run_upgrade_script('2.4.0', '2.4.1');
                    run_upgrade_script('2.4.1', '2.5.0');
                    run_upgrade_script('2.5.1', '2.5.2');
                    run_upgrade_script('2.5.2', '2.6.0');
                    run_upgrade_script('2.6.0', '2.6.1');
                    run_upgrade_script('2.6.1', '2.6.2');
                    run_upgrade_script('2.6.2', '2.9.0');
                    run_upgrade_script('2.9.0', '2.9.1');
                    run_upgrade_script('2.9.1', '2.9.2');
                    run_upgrade_script('2.9.3', '3.0.0');
                    run_upgrade_script('3.0.1', '3.0.2');
                    run_upgrade_script('3.0.2', '3.0.3');
                    run_upgrade_script('3.0.4', '3.0.5');
                    run_upgrade_script('3.0.5', '3.0.6');
                    run_upgrade_script('3.0.6', '3.1.0');
                    $upgraded = true;
                }
                else
                {
                    switch ($version_info->software_version)
                    {
                        case "2.1.0":
                            run_upgrade_script('2.1.0', '2.1.1');
                            //fall through
                        case "2.1.1":
                            run_upgrade_script('2.1.1', '2.2.0');
                        case "2.2.0":
                            run_upgrade_script('2.2.0', '2.3.0');
                        case "2.3.0":
                            run_upgrade_script('2.3.0', '2.3.1');
                        case "2.3.1":
                            run_upgrade_script('2.3.1', '2.3.2');
                        case "2.3.2":
                            run_upgrade_script('2.3.2', '2.3.3');
                        case "2.3.3":
                            run_upgrade_script('2.3.3', '2.3.4');
                        case "2.3.4":
                            run_upgrade_script('2.3.4', '2.4.0');
                        case "2.4.0":
                            run_upgrade_script('2.4.0', '2.4.1');
                        case "2.4.1":
                            run_upgrade_script('2.4.1', '2.5.0');
                        case "2.5.0":
                        case "2.5.1":
                            run_upgrade_script('2.5.1', '2.5.2');
                        case "2.5.2":
                            run_upgrade_script('2.5.2', '2.6.0');
                        case "2.6.0":
                            run_upgrade_script('2.6.0', '2.6.1');
                        case "2.6.1":
                            run_upgrade_script('2.6.1', '2.6.2');
                        case "2.6.2":
                            run_upgrade_script('2.6.2', '2.9.0');
                        case "2.9.0":
                            run_upgrade_script('2.9.0', '2.9.1');
                        case "2.9.1":
                            run_upgrade_script('2.9.1', '2.9.2');
                        case "2.9.2":
                        case "2.9.3":
                            run_upgrade_script('2.9.3', '3.0.0');
                        case "3.0.0":
                        case "3.0.1":
                            run_upgrade_script('3.0.1', '3.0.2');
                        case "3.0.2":
                            run_upgrade_script('3.0.2', '3.0.3');
                        case "3.0.3":
                        case "3.0.4":
                            run_upgrade_script('3.0.4', '3.0.5');
                        case "3.0.5":
                            run_upgrade_script('3.0.5', '3.0.6');
                        case "3.0.6":
                            run_upgrade_script('3.0.6', '3.1.0');
                            $upgraded = true;
                        default:
                            //Latest version - nothing to do
                            break;
                    }
                }
                break;
        }

        if ($upgraded)
        {

        }
    }

    if (strlen(nbf_version::$upgraded_from) == 0)
    {
        $sql = "SELECT software_version FROM #__nbill_version WHERE id = 1";
        $nb_database->setQuery($sql);
        nbf_version::$upgraded_from = $nb_database->loadResult();
    }

    $sql = "DELETE FROM #__nbill_version WHERE id = 1";
    $nb_database->setQuery($sql);
    $nb_database->query();
    $sql = "INSERT INTO #__nbill_version (id, software_version, upgraded_from) VALUES (1, '" . nbf_version::$nbill_version_no . "', '" . nbf_version::$upgraded_from . "')";
    $nb_database->setQuery($sql);
    $nb_database->query();

    if (strlen($nb_database->_errorMsg) > 0)
    {
        nbf_globals::$db_errors[] = $nb_database->_errorMsg;
    }

    if (count(nbf_globals::$db_errors) == 0)
    {
        @$retVal = NBILL_UPGRADED_SUCCESSFULLY;
        if ($retVal == "NBILL_UPGRADED_SUCCESSFULLY")
        {
            $retVal = "Your installation of the component has been upgraded successfully.";
        }
        return $retVal;
    }
    else
    {
        $retVal = NBILL_DB_UPGRADE_ERRORS;
        if ($retVal == "NBILL_DB_UPGRADE_ERRORS")
        {
            $retVal = "The component has been upgraded, but one or more database errors occurred during the upgrade process. The errors are displayed below.";
        }
        $retVal .= '<br /><br />';
        $retVal .= implode("<br />", nbf_globals::$db_errors);
        return $retVal;
    }
}

function run_upgrade_script($from, $to)
{
    $function = 'upgrade_' . str_replace('.', '_', $from) . '_to_' . str_replace('.', '_', $to);
    $file_name = dirname(__FILE__) . '/upgrade/' . $function . '.php';
    if (file_exists($file_name)) {
        if (nbf_version::$nbill_version_no != $to) {
            nbf_version::$nbill_version_no = $to;
        }
        include_once($file_name);
        $function();
    }
}

function index_exists_on_column($table_name, $column_name)
{
    $nb_database = nbf_cms::$interop->database;

    $sql = "SHOW INDEX FROM `" . $table_name . "` WHERE Column_name = '" . $nb_database->getEscaped($column_name) . "'";
    $nb_database->setQuery($sql);
    $result = $nb_database->loadObjectList();
    return ($result && count($result) > 0) ? true : false;
}

function edit_language_item($feature, $arr_text_to_add, $arr_text_to_replace = null, $arr_end_text = null, $recursive_replace = false)
{
    //Add the specified text to the end of the relevant language file for every installed language
    $thisdir = dirname(__FILE__);
    $languages = array_diff(scandir("$thisdir/language/"), array(".", ".."));
    $end_text = null;
    $text_to_add = null;
    $text_to_replace = null;

    foreach ($languages as $language)
    {
        if (strpos($language, ".") !== false)
        {
              continue; //Folders only please
        }
        $filename = "$thisdir/language/$language/$feature.$language.php";

        //Get the correct values out of the arrays
        if (isset($arr_text_to_add[$language]))
        {
              $text_to_add = $arr_text_to_add[$language];
        }
        else
        {
              if (isset($arr_text_to_add['en-GB']))
              {
                  $text_to_add = $arr_text_to_add['en-GB'];
              }
        }
        if (isset($arr_text_to_replace[$language]))
        {
              $text_to_replace = $arr_text_to_replace[$language];
        }
        else
        {
              if (isset($arr_text_to_replace['en-GB']))
              {
                  $text_to_replace = $arr_text_to_replace['en-GB'];
              }
        }
        if (isset($arr_end_text[$language]))
        {
              $end_text = $arr_end_text[$language];
        }
        else
        {
              if (isset($arr_end_text['en-GB']))
              {
                  $end_text = $arr_end_text['en-GB'];
              }
        }

        //Find the file, and do the replacement
        if (file_exists($filename))
        {
            $content = file_get_contents($filename);
            if ($text_to_replace != null)
            {
                if ($end_text != null)
                {
                    //Use start and end markers instead of str_replace
                    $start_pos = strpos($content, $text_to_replace);
                    if ($start_pos !== false)
                    {
                        $end_pos = strpos($content, $end_text, $start_pos);
                        if ($end_pos !== false)
                        {
                            $content = substr($content, 0, $start_pos) . $text_to_add . substr($content, $end_pos + strlen($end_text));
                        }
                    }
                }
                else
                {
                    if ($recursive_replace)
                    {
                        //Keep repeating until the target text no longer appears at all
                        $loop_breaker = 0;
                        while (strpos($content, $text_to_replace) !== false)
                        {
                            if ($loop_breaker > 100)
                            {
                                break;
                            }
                            $content = str_replace($text_to_replace, $text_to_add, $content);
                            $loop_breaker++;
                        }
                    }
                    else
                    {
                        $content = str_replace($text_to_replace, $text_to_add, $content);
                    }
                }
            }
            else
            {
                if (strpos($content, $text_to_add) === false)
                {
                    //Append (insert before closing php tag - first check that closing tag exists)
                    $endtag_pos = strrpos($content, '?');
                    if (substr(substr($content, $endtag_pos), 0, 2) == "?>")
                    {
                        //Make sure the addition is not already present (if upgrading by uninstall/resinstall, it will be)
                        $newtextlines = explode("\n", $text_to_add);
                        foreach ($newtextlines as $newtextline)
                        {
                            if (strlen(trim($newtextline)) > 0)
                            {
                                if (strpos($content, str_replace("\r", "", $newtextline)) === false)
                                {
                                    $content = substr($content, 0, $endtag_pos) . $text_to_add . "\n" . substr($content, $endtag_pos);
                                    break;
                                }
                            }
                        }
                    }
                    else
                    {
                        //Closing PHP tag missing
                        $content = $content . "\n" . $text_to_add;
                    }
                }
            }
            clearstatcache();
            if (is_writable($filename))
            {
                $handle = fopen($filename, "w");
                fwrite($handle, $content);
                fclose($handle);
            }
            else
            {
                nbf_globals::$db_errors[] = "Language file $filename is not writable.";
            }
        }
    }
}