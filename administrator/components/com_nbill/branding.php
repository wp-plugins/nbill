<?php
/**
* Branding constants
* @version 2
* @package nBill Lite
* @copyright (C) 2015 Netshine Software Limited

* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

if (!defined("NBILL_BRANDING_VERSION_HOST"))
{
    @define("NBILL_BRANDING_NAME", "nBill Lite"); //Name of the product
    define("NBILL_BRANDING_TRADEMARK_SYMBOL", "<sup>&#8482;</sup>"); //If product name is trademarked, TM &#8482; or R &#174; symbol can go here. It is a criminal offense in the UK to use a trademark symbol if you are not entitled to do so.
    define("NBILL_BRANDING_COMPANY", "Netshine Software Limited"); //Name of the company providing support
    define("NBILL_BRANDING_SUPPORT_URL", "www.nbill.co.uk/lite/support/support.html"); //Support desk URL
    define("NBILL_BRANDING_WEBSITE", "www.nbill.co.uk/lite"); //Product website
    define("NBILL_BRANDING_EULA", "http://www.gnu.org/licenses/gpl-2.0.txt"); //Link to License agreement
    define("NBILL_BRANDING_HTML2PS", "www.nbill.co.uk/pdf_generation.html"); //Link to explanation of PDF generator
    define("NBILL_BRANDING_DOCUMENTATION", "www.nbill.co.uk/lite/help/"); //Link to documentation
    define("NBILL_BRANDING_VERSION_HOST", "nbill.co.uk"); //Host name from which to retrieve version information and/or auto upgrades
    define("NBILL_BRANDING_VERSION_CHECK_PATH", "/api/v1/version_check.php"); //Path to version checking file (including leading slash)
}

if (!defined("NBILL_BRANDING_COMPONENT_NAME"))
{
    define("NBILL_BRANDING_COMPONENT_NAME", "com_nbill");
}