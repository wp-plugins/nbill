<?php
/**
* Clean up when the component is uninstalled
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function com_uninstall()
{
	include_once(dirname(__FILE__) . "/admin.init.php");
    $nb_database = nbf_cms::$interop->database;

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

    //If there is no license key, no clients, no invoices, and no extensions, delete all tables (user probably couldn't get ioncube working)
    $sql = "SELECT license_key FROM #__nbill_license WHERE id = 1";
    $nb_database->setQuery($sql);
    $lic_key = $nb_database->loadResult();
    if ($lic_key !== null && nbf_common::nb_strlen($lic_key) == 0)
    {
        $sql = "SELECT #__nbill_entity.id FROM #__nbill_entity LIMIT 1 UNION
                SELECT #__nbill_document.id FROM #__nbill_entity LIMIT 1 UNION
                SELECT #__nbill_transaction.id FROM #__nbill_transaction LIMIT 1 UNION
                SELECT #__nbill_extensions.id FROM #__nbill_extensions LIMIT 1";
        $nb_database->setQuery($sql);
        $records = $nb_database->loadObjectList();
        if (!$records || count($records) == 0)
        {
            uninstall_delete_tables();
        }
    }

    if ($lic_key !== null && strlen($lic_key) > 0)
    {
      $sql = "DELETE FROM #__nbill_extensions";
      $nb_database->setQuery($sql);
      $nb_database->query();
    }

    nbf_cms::$interop->uninstall_tasks();

    //Delete any cookies
    @setcookie("nbverifier", false, nbf_common::nb_time() - 14400);
    foreach ($_COOKIE as $key=>$value)
    {
        if (substr($key, 0, 6) == "netinv" || substr($key, 0, 5) == "nbill")
        {
            @setcookie($key, false, nbf_common::nb_time() - 14400);
        }
    }

    return 'Uninstallation complete. ';
}

function uninstall_delete_tables()
{
  include_once(dirname(__FILE__) . "/admin.init.php");
  $nb_database = nbf_cms::$interop->database;

	//Delete cookie if it exists
	@setcookie ("nbverifier", "", nbf_common::nb_time() - 3600);

	$sql = "SHOW TABLES";
	$nb_database->setQuery($sql);
	$tables = $nb_database->loadObjectList();
	if (!$tables)
	{
		$tables = array();
	}

	foreach ($tables as $table)
	{
		foreach ($table as $tablename)
		{
			$name_parts = explode("_", $tablename);
			if (count($name_parts) > 1 && $name_parts[1] == "nbill")
			{
				//This is an nBill table
				$sql = "DROP TABLE $tablename";
				$nb_database->setQuery($sql);
				$nb_database->query();
			}
		}
	}
}