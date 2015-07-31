<?php
/**
* Language file for user admin feature (accessing nBill admin via front end)
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

define("NBILL_USER_ADMIN_TITLE", "User Administration");
define("NBILL_USER_ADMIN_INTRO_BETA", "You can use this feature to grant access to the administration features of " . NBILL_BRANDING_NAME . " via your website front end. This means that selected users will be able to access " . NBILL_BRANDING_NAME . " administration without needing to login to your CMS admin panel (this could be useful for example, if you wanted to give your accountants access to your billing records without giving them access to other areas of your website administration). Further granularity of access permissions may be added at a later date, but at present, <strong>all " . NBILL_BRANDING_NAME . " admin features will be made available</strong> to users who are given access here. You can use the Display Options feature to control whether or how a link to the " . NBILL_BRANDING_NAME . " administrator is shown in your website for those users who have access.");
define("NBILL_USER_ADMIN_USERNAME", "Username");
define("NBILL_USER_ADMIN_NAME", "Name");
define("NBILL_USER_ADMIN_EMAIL", "E-Mail Address");
define("NBILL_USER_ADMIN_ACCESS", "Grant Access?");
define("NBILL_USER_ADMIN_ACCESS_GRANTED", "Granted");
define("NBILL_USER_ADMIN_ACCESS_DENIED", "Denied");