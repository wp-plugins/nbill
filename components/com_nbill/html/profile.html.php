<?php
/**
* HTML output for user profile page
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

require_once(nbill_framework_locator::find_framework() . "/classes/nbill.html.class.php");
require_once(nbf_cms::$interop->nbill_fe_base_path . "/pages/profile/profile.custom.html.php");

class nBillFrontEndProfile
{
	public static function edit_profile(&$profile_fields, &$field_options, &$sql_field_options, &$contact_data, &$entity_data, &$countries, &$email_options_xref, $entity_in_error = 0, $languages = array())
	{
        $css = file_get_contents(nbf_cms::$interop->nbill_fe_base_path . "/style/nbill_profile.css");
        nbf_cms::$interop->add_html_header('<style type="text/css">' . $css . '</style>');
        $css = file_get_contents(nbf_cms::$interop->nbill_fe_base_path . "/style/nbill_tabs.css");
        nbf_cms::$interop->add_html_header('<style type="text/css">' . $css . '</style>');
        if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		}
		?>
		<form action="<?php echo nbf_cms::$interop->fe_form_action; ?>" method="post" name="profile" id="profile">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="<?php echo nbf_cms::$interop->component_name; ?>" value="my-account" />
        <input type="hidden" name="Itemid" value="<?php echo isset($_REQUEST['Itemid']) && $_REQUEST['Itemid'] ? intval($_REQUEST['Itemid']) : (isset($GLOBALS['Itemid']) && $GLOBALS['Itemid'] ? intval($GLOBALS['Itemid']) : ""); ?>" />
		<input type="hidden" name="id" value="<?php echo $entity_data->entity_id; ?>" />
        <input type="hidden" name="action" value="profile" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="postback" value="1" />
		<?php
        $fe_profile = new nbill_fe_profile_custom();
        $fe_profile->display($profile_fields, $field_options, $sql_field_options, $contact_data, $entity_data, $countries, $email_options_xref, $entity_in_error, $languages) ?>
		</form>
		<?php
	}
}