<?php
/**
* Main entry point into nBill administrator
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Check whether to trace this request and spit out a log
if (@file_exists(dirname(__FILE__) . "/framework/nbill.config.php"))
{
    @include_once(dirname(__FILE__) . "/framework/nbill.config.php");
    if (@class_exists("nbf_config"))
    {
        if (property_exists("nbf_config", "trace_debug_admin"))
        {
            nbf_globals::$trace_debug = @nbf_config::$trace_debug_admin;
        }
    }
}
nbf_common::debug_trace("POST: " . print_r($_POST, true) . "\nGET: " . print_r($_GET, true) . "\nCOOKIE: " . print_r($_COOKIE, true));//Load supporting libraries that are needed in administrator but not for installation (hence separate from admin.init.php)
require_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.pagination.class.php");
require_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.html.class.php");

//Find default vendor, if applicable
nbf_globals::$vendor_filter = nbf_common::get_param($_REQUEST, 'vendor_filter');
if (nbf_common::nb_strlen(nbf_globals::$vendor_filter) == 0)
{
    if (isset($_COOKIE['nbill_vendor_' . md5(nbf_cms::$interop->live_site)]))
    {
        nbf_globals::$vendor_filter = $_COOKIE['nbill_vendor_' . md5(nbf_cms::$interop->live_site)];
    }
    if (nbf_common::nb_strlen(nbf_globals::$vendor_filter) > 0 && nbf_globals::$vendor_filter != "-999")
    {
        //Make sure vendor exists
        $sql = "SELECT id FROM #__nbill_vendor WHERE id = " . intval(nbf_globals::$vendor_filter);
        nbf_cms::$interop->database->setQuery($sql);
        $id_exists = nbf_cms::$interop->database->loadResult();
        if (!$id_exists)
        {
            nbf_globals::$vendor_filter = "";
        }
    }
    if (nbf_common::nb_strlen(nbf_globals::$vendor_filter) == 0)
    {
        //Get first vendor from database
        $sql = "SELECT id FROM #__nbill_vendor ORDER BY id LIMIT 1";
        nbf_cms::$interop->database->setQuery($sql);
        nbf_globals::$vendor_filter = intval(nbf_cms::$interop->database->loadResult());
        if (!nbf_globals::$vendor_filter)
        {
            nbf_common::debug_trace("Vendor not found! Database object: " . print_r(nbf_cms::$interop->database, true));
            //Check whether component has installed correctly
            $db_ok = false;
            $sql = "SELECT description FROM #__nbill_xref_reminder_type WHERE code = 'XX'"; //One of the last SQL statements to be executed during install
            nbf_cms::$interop->database->setQuery($sql);
            $db_ok = nbf_cms::$interop->database->loadResult();
            if (!file_exists(dirname(__FILE__) . "/logo-icon-16.gif") || !$db_ok)
            {
                nbf_common::debug_trace("xref reminder type found? " . ($db_ok ? "yes" : "no") . "; logo-icon-16.gif exists? " . (file_exists(dirname(__FILE__) . "/logo-icon-16.gif")  ? "yes" : "no"));
                //Forget the CMS admin template
                $loopbreaker = 0;
                while (ob_get_length() !== false)
                {
                    $loopbreaker++;
                    @ob_end_clean();
                    if ($loopbreaker > 15)
                    {
                        break;
                    }
                }
                if (defined("NBILL_3_INSTALL_ERROR"))
                {
                    die(sprintf(NBILL_3_INSTALL_ERROR, nbf_cms::$interop->cms_home_page));
                }
                else
                {
                    //We have no language file, so use hard-coded error message
                    die("Sorry, it looks like nBill Lite failed to install correctly! Please try uninstalling and re-installing. If that does not help, please refer to the troubleshooting section of the documentation at <a href=\"http://www.nbill.co.uk/help/\">www.nbill.co.uk/help/</a>.<br /><br /><a href=\"" . $nb_interop->cms_home_page . "\">Return to Home Page</a>");
                }
            }
        }
    }
}
$_POST['vendor_filter'] = nbf_globals::$vendor_filter;
$_REQUEST['vendor_filter'] = nbf_globals::$vendor_filter;
$cookie_success = @setcookie('nbill_vendor_' . md5(nbf_cms::$interop->live_site), nbf_globals::$vendor_filter, nbf_common::nb_mktime(23,59,59,12,31,2037));

if (nbf_common::nb_strpos(urldecode(nbf_common::get_param($_REQUEST, 'action')), "..") !== false || nbf_common::nb_strpos(urldecode(nbf_common::get_param($_REQUEST, 'action')), "/") !== false || nbf_common::nb_strpos(urldecode(nbf_common::get_param($_REQUEST, 'action')), "\\") !== false)
{
    die("Hacking Attempt");
}

// Get parameters
$task = nbf_common::get_param($_REQUEST, 'task', '');
nbf_globals::$message = nbf_common::get_param($_REQUEST, 'message', '', true);
nbf_globals::$message = str_replace('\n', "\n", nbf_globals::$message);
nbf_globals::$message = str_replace("\n", '<br />', nbf_globals::$message); //Replace hard and soft line breaks with html equivalent
nbf_globals::$message = str_replace('[anomaly_link]', '<a href="' . nbf_cms::$interop->admin_page_prefix . '&action=anomaly">' . NBILL_MNU_ANOMALY . '</a>', nbf_globals::$message);

$cid = nbf_common::get_param( $_POST, 'cid', '' );
if (!is_array( $cid ))
{
    $cid = array(0);
    $cid[0] = intval(nbf_common::get_param($_REQUEST, 'cid', ''));
}
foreach ($cid as $cid_key=>$cid_value)
{
    $cid[$cid_key] = intval($cid_value);
}
$id = intval(nbf_common::get_param($_POST, 'id', ''));
if (!$cid || count($cid) == 0 || !$cid[0])
{
    $cid[0] = $id;
}



//Check database version matches software version
include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.upgrader.class.php");
$db_version = nbf_version::get_database_version();

if ($db_version != nbf_version::$nbill_version_no && nbf_common::get_param($_REQUEST, 'action') != "upgrade_db")
{
	if ($db_version > nbf_version::$nbill_version_no)
	{
		nbf_globals::$message .= (strlen(nbf_globals::$message) > 0 ? "<br />" : "") . "<span style=\"background-color:#ff0000;color:#ffffff;\">" . NBILL_SOFTWARE_OOD . "</span>";
	}
	else if ($db_version < nbf_version::$nbill_version_no)
	{
		nbf_globals::$message .= (strlen(nbf_globals::$message) > 0 ? "<br />" : "") . "<span style=\"background-color:#ff0000;color:#ffffff;\">" . sprintf(NBILL_DATABASE_OOD, "<a style=\"color:#ffff00;text-decoration:underline;\" href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=upgrade_db\">" . NBILL_CLICK_HERE . "</a>") . "</span>";
	}
}
else
{
	if (nbf_common::get_param($_REQUEST, 'action') == "upgrade_db")
	{
		include_once(nbf_cms::$interop->nbill_admin_base_path . "/install.nbill.php");
		nbf_globals::$message = com_install();
	}
}
$nb_database = nbf_cms::$interop->database;

//Include support libraries
require_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.number.format.php");
require_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.nbill.html.php");

//Load language file for selected action
if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/language/" . nbf_cms::$interop->language . "/" . nbf_common::get_param($_REQUEST, 'action') . "." . nbf_cms::$interop->language . ".php"))
{
	@include_once(nbf_cms::$interop->nbill_admin_base_path . "/language/" . nbf_cms::$interop->language . "/" . nbf_common::get_param($_REQUEST, 'action') . "." . nbf_cms::$interop->language . ".php");
}
else
{
	if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/language/en-GB/" . nbf_common::get_param($_REQUEST, 'action') . ".en-GB.php"))
	{
		@include_once(nbf_cms::$interop->nbill_admin_base_path . "/language/en-GB/" . nbf_common::get_param($_REQUEST, 'action') . ".en-GB.php");
	}
}

nb_main_html::start_container();

//Show the menu (unless request parameter tells us not to)
if (!nbf_common::get_param($_REQUEST, 'hide_billing_menu'))
{
	$menus = get_menu_hierarchy();
	nb_main_html::show_main_menu(nbf_globals::$message, $menus);
    if (nBillConfigurationService::getInstance()->getConfig()->disable_email) {
        nbf_globals::$message .= (strlen(nbf_globals::$message) > 0 ? "<br />" : "") . sprintf(NBILL_WARNING_EMAIL_DISABLED, nbf_cms::$interop->admin_page_prefix . '&action=configuration');
    }
}

//If we are loading an extension with more than one page, and have not been told to suppress it, show a row of tabs
$ext_menu_tabs = array();
if (!nbf_common::get_param($_REQUEST, 'hide_extension_page_tabs'))
{
    $expected_url = $nb_database->getEscaped(str_replace(nbf_cms::$interop->admin_page_prefix, '[NBILL_ADMIN]', str_replace('/administrator/', '', nbf_common::get_requested_page())));
    $possible_url = $nb_database->getEscaped(str_replace(nbf_cms::$interop->admin_page_prefix, '[NBILL_ADMIN]', nbf_cms::$interop->admin_page_prefix . '&action=' . nbf_common::get_param($_REQUEST, 'action') . "&sub_action=" . nbf_common::get_param($_REQUEST, 'sub_action') . "%"));

    $sql = "SELECT m2.text, m2.description, m2.image, m2.url
            FROM #__nbill_extensions_menu AS m1
            INNER JOIN #__nbill_extensions_menu AS m2 ON m1.parent_id = m2.parent_id
            WHERE (m1.url = '$expected_url' OR m1.url LIKE '$possible_url')
                    AND m1.parent_id != -1 AND m1.published = 1 AND m2.published = 1
            GROUP BY m2.id ORDER BY m2.ordering";
    $nb_database->setQuery($sql);
    $ext_menu_tabs = $nb_database->loadObjectList();
}

//Show the toolbar (unless we are on a popup)
if (!nbf_common::get_param($_REQUEST, 'hide_toolbar') && (!nbf_globals::$popup || nbf_common::get_param($_REQUEST, 'show_toolbar')))
{
    nb_main_html::show_toolbar($ext_menu_tabs);
}

switch (nbf_common::get_param($_REQUEST, 'action'))
{
    case "about":
        nb_main_html::show_about_box();
        break;
    case "ajax":
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.server.php");
        break;
    case "registration":
        echo "<div class=\"nbill-message\">This is the Open Source, 'Lite' version of nBill, released under the GPL. You do not need a license key to run this software. The standard edition of nBill is published under a proprietary license, and does require a license key.</div>";
        break;
    case "email":
        switch (nbf_common::get_param($_REQUEST, 'task'))
        {
            case 'get_message':
            case 'get_correspondence':
                $document_id = nbf_common::get_param($_REQUEST, 'document_id');
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.email.class.php");
                $document = null;
                $sql = "SELECT #__nbill_document.id, #__nbill_document.document_type, #__nbill_document.document_no,
                                #__nbill_document.billing_name, #__nbill_document.status, #__nbill_document.vendor_id, #__nbill_document.correspondence,
                                #__nbill_vendor.invoice_email_template_name, #__nbill_vendor.credit_email_template_name,
                                #__nbill_vendor.quote_email_template_name, #__nbill_vendor.po_email_template_name, #__nbill_entity.default_language,
                                #__nbill_contact.email_address, #__nbill_entity.company_name, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS `name`, " .
                                nbf_cms::$interop->cms_database_enum->table_user . "." . nbf_cms::$interop->cms_database_enum->column_user_username . " AS username
                                FROM #__nbill_document
                                INNER JOIN #__nbill_vendor ON #__nbill_document.vendor_id = #__nbill_vendor.id
                                LEFT JOIN #__nbill_entity ON #__nbill_document.entity_id = #__nbill_entity.id
                                LEFT JOIN #__nbill_entity_contact ON #__nbill_entity.id = #__nbill_entity_contact.entity_id AND #__nbill_entity_contact.contact_id = #__nbill_entity.primary_contact_id
                                LEFT JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id
                                LEFT JOIN " . nbf_cms::$interop->cms_database_enum->table_user . " ON #__nbill_contact.user_id = " . nbf_cms::$interop->cms_database_enum->table_user . "." . nbf_cms::$interop->cms_database_enum->column_user_id . "
                                WHERE #__nbill_document.id = " . intval($document_id);
                $nb_database->setQuery($sql);
                $nb_database->loadObject($document);
                if (!$document)
                {
                    $document = new stdClass();
                    $document->correspondence = "";
                }
                if (nbf_common::get_param($_REQUEST, 'task') == 'get_correspondence')
                {
                    nb_main_html::show_email_message($document->correspondence);
                }
                else
                {
                    switch (nbf_common::get_param($_REQUEST, 'message_type'))
                    {
                        case "attach":
                            nb_main_html::show_email_message_editor(nbf_email::get_attach_message($document), false);
                            break;
                        case "notify":
                            nb_main_html::show_email_message_editor(nbf_email::get_notify_message($document), false);
                            break;
                        case "embed":
                            nb_main_html::show_email_message_editor(nbf_email::get_embedded_message($document), true);
                            break;
                        case "template_attach":
                            nb_main_html::show_email_message_editor(nbf_email::get_template_attach_message($document, true), true);
                            break;
                        case "template_notify":
                            nb_main_html::show_email_message_editor(nbf_email::get_template_notify_message($document, true), true);
                            break;
                    }
                }
        }
        break;
	default:
        //Load html file
		if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.html/" . nbf_common::get_param($_REQUEST, 'action') . ".html.php")) {
			include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.html/" . nbf_common::get_param($_REQUEST, 'action') . ".html.php");
		} else {
		    //Load language file for main (home) page
		    if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/language/" . nbf_cms::$interop->language . "/main." . nbf_cms::$interop->language . ".php")) {
		        @include_once(nbf_cms::$interop->nbill_admin_base_path . "/language/" . nbf_cms::$interop->language . "/main." . nbf_cms::$interop->language . ".php");
		    } else if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/language/en-GB/main.en-GB.php")) {
		        include_once(nbf_cms::$interop->nbill_admin_base_path . "/language/en-GB/main.en-GB.php");
		    }
			include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.html/main.html.php");
		}

		//Load processing file
		if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/" . nbf_common::get_param($_REQUEST, 'action') . ".php")) {
            nbf_common::debug_trace("Loading action file: " . nbf_common::get_param($_REQUEST, 'action'));
			include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/" . nbf_common::get_param($_REQUEST, 'action') . ".php");
		} else {
            switch (nbf_common::get_param($_REQUEST, 'action')) {
                case '':
                case 'upgrade_db':
                    break;
                default:
                    $_REQUEST['disabled'] = 1;
                    break;
            }
            nbf_common::debug_trace("Loading default action file: main");
            if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/language/" . nbf_cms::$interop->language . "/main." . nbf_cms::$interop->language .".php")) {
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/language/" . nbf_cms::$interop->language . "/main." . nbf_cms::$interop->language .".php");
            } else {
                nbf_cms::$interop->nbill_admin_base_path . "/language/en-GB/main.en-GB.php";
            }
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.html/main.html.php"); //In case html file exists but processing file doesn't
			include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/main.php");
		}
}

function check_for_translation(&$object_list, $table_name, $key, $columns)
{
    //Check for a Joom!Fish translation on payment instructions and small print (by default, joomfish only runs in front end)
    $nb_database = nbf_cms::$interop->database;

    $jf_trans = null;

    $sql = "SELECT `value`, `reference_id`, `reference_field` FROM #__jf_content
            INNER JOIN #__languages ON #__languages.id = #__jf_content.language_id
            WHERE #__languages.code = '" . nbf_cms::$interop->language . "'
            AND #__jf_content.reference_table = '$table_name'
            AND #__jf_content.reference_field IN ($columns)";
    $nb_database->setQuery($sql);
    $jf_trans = $nb_database->loadObjectList();
    if (nbf_common::nb_strlen($nb_database->_errorMsg) == 0 && $jf_trans)
    {
        foreach ($jf_trans as $translation)
        {
            if (nbf_common::nb_strlen($translation->value) > 0)
            {
                $field = $translation->reference_field;
                for ($i=0; $i<count($object_list); $i++)
                {
                    if ($object_list[$i]->$key == $translation->reference_id)
                    {
                        $object_list[$i]->$field = $translation->value;
                        break;
                    }
                }
            }
        }
    }
}

nb_main_html::end_container(nBillConfigurationService::getInstance()->getConfig()->admin_custom_stylesheet);
nbf_cms::$interop->terminate();
@ob_end_flush();