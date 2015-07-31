<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_3_0_6_to_3_1_0()
{
    nbf_version::$nbill_version_no = '3.1.0';

    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    $new_tables[] = "paypal_preapp_invitations";
    $new_tables[] = "paypal_preapp_resources";
    $new_tables[] = "address";
    require_once(nbf_cms::$interop->nbill_admin_base_path . "/install.new.php");
    new_db_install($new_tables, array(), false);

    $sql[] = "UPDATE `#__nbill_payment_gateway` SET `data_type` = 'boolean' WHERE `#__nbill_payment_gateway`.`id` = 3;";
    $sql[] = "ALTER TABLE `#__nbill_payment_gateway` auto_increment = 11000;";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10005', 'paypal', 'api_info', '', '', 'NBILL_PAYPAL_API_INFO_HELP', '0', '0', '15', 'label');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10010', 'paypal', 'default_max_amount', '100.00', 'NBILL_PAYPAL_DEFAULT_MAX_AMOUNT', 'NBILL_PAYPAL_DEFAULT_MAX_AMOUNT_HELP', '0', '1', '16', 'decimal');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10015', 'paypal', 'default_payment_count', '15', 'NBILL_PAYPAL_DEFAULT_PAYMENT_COUNT', 'NBILL_PAYPAL_DEFAULT_PAYMENT_COUNT_HELP', '0', '1', '17', 'integer');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10020', 'paypal', 'api_sandbox', '1', 'NBILL_PAYPAL_API_USE_SANDBOX', 'NBILL_PAYPAL_API_USE_SANDBOX_HELP', '0', '1', '18', 'boolean');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10025', 'paypal', 'api_sandbox_user', '', 'NBILL_PAYPAL_API_SANDBOX_USER', 'NBILL_PAYPAL_API_SANDBOX_USER_HELP', '0', '1', '19', 'string');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10030', 'paypal', 'api_sandbox_password', '', 'NBILL_PAYPAL_API_SANDBOX_PASSWORD', 'NBILL_PAYPAL_API_SANDBOX_PASSWORD_HELP', '0', '1', '20', 'string');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10035', 'paypal', 'api_sandbox_signature', '', 'NBILL_PAYPAL_API_SANDBOX_SIGNATURE', 'NBILL_PAYPAL_API_SANDBOX_SIGNATURE_HELP', '0', '1', '21', 'string');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10040', 'paypal', 'api_sandbox_appid', 'APP-80W284485P519543T', 'NBILL_PAYPAL_API_SANDBOX_APPID', 'NBILL_PAYPAL_API_SANDBOX_APPID_HELP', '0', '1', '22', 'string');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10045', 'paypal', 'api_user', '', 'NBILL_PAYPAL_API_USER', 'NBILL_PAYPAL_API_USER_HELP', '0', '1', '23', 'string');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10050', 'paypal', 'api_password', '', 'NBILL_PAYPAL_API_PASSWORD', 'NBILL_PAYPAL_API_PASSWORD_HELP', '0', '1', '24', 'string');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10055', 'paypal', 'api_signature', '', 'NBILL_PAYPAL_API_SIGNATURE', 'NBILL_PAYPAL_API_SIGNATURE_HELP', '0', '1', '25', 'string');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10060', 'paypal', 'api_appid', '', 'NBILL_PAYPAL_API_APPID', 'NBILL_PAYPAL_API_APPID_HELP', '0', '1', '26', 'string');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`, `options`) VALUES (10065, 'paypal', 'confirm_signups', '1', 'NBILL_PAYPAL_CONFIRM_SIGNUPS', 'NBILL_PAYPAL_CONFIRM_SIGNUPS_HELP', 0, 1, 27, 'boolean', '');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`, `options`) VALUES (10070, 'paypal', 'preapp_thankyou', 'Thank you for authorising us to charge your Paypal account. Your instruction has been received successfully.', 'NBILL_PAYPAL_DEFAULT_PREAPP_THANKS', 'NBILL_PAYPAL_DEFAULT_PREAPP_THANKS_HELP', 0, 1, 28, 'text', '');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`, `options`) VALUES (10075, 'paypal', 'preapp_success_url', '', 'NBILL_PAYPAL_PREAPP_SUCCESS_URL', 'NBILL_PAYPAL_PREAPP_SUCCESS_URL_HELP', 0, 1, 29, 'string', '');";
    $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`, `options`) VALUES (10080, 'paypal', 'preapp_failure_url', '', 'NBILL_PAYPAL_PREAPP_FAILURE_URL', 'NBILL_PAYPAL_PREAPP_FAILURE_URL_HELP', 0, 1, 30, 'string', '');";
    $sql[] = "ALTER TABLE `#__nbill_payment_gateway` CHANGE `g_value` `g_value` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;";

    $sql[] = "ALTER TABLE `#__nbill_contact` ADD `shipping_address_id` INT NOT NULL DEFAULT '0' AFTER `postcode`;";
    $sql[] = "ALTER TABLE `#__nbill_entity` ADD `shipping_address_id` INT NOT NULL DEFAULT '0' AFTER `postcode`;";
    $sql[] = "ALTER TABLE `#__nbill_transaction` CHANGE `document_ids` `document_ids` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '0';"; //Indexed column too big for utf-8 and will only hold numbers and commas anyway

    //Shipping address profile fields and delivery notes
    $query = "SELECT ordering FROM #__nbill_profile_fields WHERE `name` = 'NBILL_CORE_country' OR id = 12 ORDER BY `name`='NBILL_CORE_country' DESC";
    $nb_database->setQuery($query);
    $ordering = $nb_database->loadResult();
    if (!$ordering) {$ordering = 12;}
    $inserts = 0;
    $sql_profile = array();
    $query = "SELECT id FROM #__nbill_profile_fields WHERE `name` = 'NBILL_CORE_shipping_same'";
    $nb_database->setQuery($query);
    if (!$nb_database->loadResult()) {
        $inserts++;
        $sql_profile[] = "INSERT INTO `#__nbill_profile_fields` (`ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `xref_sql`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `include_on_forms`, `show_on_summary`) VALUES (" . ($ordering + $inserts) . ", 'EE', 'NBILL_CORE_shipping_same', 'NBILL_ADDRESS_SHIPPING', 0, 'onclick=\"var nbc=document.getElementsByClassName(\\'nbill_control\\');for(var i=0;i<nbc.length;i++){if(nbc[i].name&&nbc[i].name.substr(0,24)==\\'ctl_NBILL_CORE_shipping_\\'){nbc[i].disabled=this.checked}}\"', 'NBILL_ADDRESS_SAME_AS_BILLING', '', '', 'On', 0, '', '', NULL, 0, 'same_as_billing', 'same_as_billing', 1, 1, 1);";
    }
    $query = "SELECT id FROM #__nbill_profile_fields WHERE `name` = 'NBILL_CORE_shipping_address_1'";
    $nb_database->setQuery($query);
    if (!$nb_database->loadResult()) {
        $inserts++;
        $sql_profile[] = "INSERT INTO `#__nbill_profile_fields` (`ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `xref_sql`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `include_on_forms`, `show_on_summary`) VALUES (" . ($ordering + $inserts) . ", 'AA', 'NBILL_CORE_shipping_address_1', 'NBILL_SHIPPING_ADDRESS_1', 0, 'disabled=\"disabled\"', '', '', '<script type=\"text/javascript\">chk=document.getElementsByName(\\'ctl_NBILL_CORE_shipping_same\\');for(i=0;i<chk.length;++i){if(chk[i].id.length>0){if(chk[i].checked===false){document.getElementsByName(\\'ctl_NBILL_CORE_shipping_address_1\\')[0].disabled=false;}break;}}</script>', '', 0, '', '', NULL, 0, 'shipping_address_1', 'shipping_address_1', 1, 1, 1);";
    }
    $query = "SELECT id FROM #__nbill_profile_fields WHERE `name` = 'NBILL_CORE_shipping_address_2'";
    $nb_database->setQuery($query);
    if (!$nb_database->loadResult()) {
        $inserts++;
        $sql_profile[] = "INSERT INTO `#__nbill_profile_fields` (`ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `xref_sql`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `include_on_forms`, `show_on_summary`) VALUES (" . ($ordering + $inserts) . ", 'AA', 'NBILL_CORE_shipping_address_2', 'NBILL_SHIPPING_ADDRESS_2', 0, 'disabled=\"disabled\"', '', '', '<script type=\"text/javascript\">chk=document.getElementsByName(\\'ctl_NBILL_CORE_shipping_same\\');for(i=0;i<chk.length;++i){if(chk[i].id.length>0){if(chk[i].checked===false){document.getElementsByName(\\'ctl_NBILL_CORE_shipping_address_2\\')[0].disabled=false;}break;}}</script>', '', 0, '', '', NULL, 0, 'shipping_address_2', 'shipping_address_2', 1, 1, 1);";
    }
    $query = "SELECT id FROM #__nbill_profile_fields WHERE `name` = 'NBILL_CORE_shipping_address_3'";
    $nb_database->setQuery($query);
    if (!$nb_database->loadResult()) {
        $inserts++;
        $sql_profile[] = "INSERT INTO `#__nbill_profile_fields` (`ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `xref_sql`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `include_on_forms`, `show_on_summary`) VALUES (" . ($ordering + $inserts) . ", 'AA', 'NBILL_CORE_shipping_address_3', 'NBILL_SHIPPING_ADDRESS_3', 0, 'disabled=\"disabled\"', '', '', '<script type=\"text/javascript\">chk=document.getElementsByName(\\'ctl_NBILL_CORE_shipping_same\\');for(i=0;i<chk.length;++i){if(chk[i].id.length>0){if(chk[i].checked===false){document.getElementsByName(\\'ctl_NBILL_CORE_shipping_address_3\\')[0].disabled=false;}break;}}</script>', '', 0, '', '', NULL, 0, 'shipping_address_3', 'shipping_address_3', 1, 1, 1);";
    }
    $query = "SELECT id FROM #__nbill_profile_fields WHERE `name` = 'NBILL_CORE_shipping_town'";
    $nb_database->setQuery($query);
    if (!$nb_database->loadResult()) {
        $inserts++;
        $sql_profile[] = "INSERT INTO `#__nbill_profile_fields` (`ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `xref_sql`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `include_on_forms`, `show_on_summary`) VALUES (" . ($ordering + $inserts) . ", 'AA', 'NBILL_CORE_shipping_town', '* NBILL_SHIPPING_TOWN', 0, 'disabled=\"disabled\"', '', '', '<script type=\"text/javascript\">chk=document.getElementsByName(\\'ctl_NBILL_CORE_shipping_same\\');for(i=0;i<chk.length;++i){if(chk[i].id.length>0){if(chk[i].checked===false){document.getElementsByName(\\'ctl_NBILL_CORE_shipping_town\\')[0].disabled=false;}break;}}</script>', '', 1, '', '', NULL, 0, 'shipping_town', 'shipping_town', 1, 1, 1);";
    }
    $query = "SELECT id FROM #__nbill_profile_fields WHERE `name` = 'NBILL_CORE_shipping_state'";
    $nb_database->setQuery($query);
    if (!$nb_database->loadResult()) {
        $inserts++;
        $sql_profile[] = "INSERT INTO `#__nbill_profile_fields` (`ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `xref_sql`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `include_on_forms`, `show_on_summary`) VALUES (" . ($ordering + $inserts) . ", 'AA', 'NBILL_CORE_shipping_state', 'NBILL_SHIPPING_STATE', 0, 'disabled=\"disabled\"', '', '', '<script type=\"text/javascript\">chk=document.getElementsByName(\\'ctl_NBILL_CORE_shipping_same\\');for(i=0;i<chk.length;++i){if(chk[i].id.length>0){if(chk[i].checked===false){document.getElementsByName(\\'ctl_NBILL_CORE_shipping_state\\')[0].disabled=false;}break;}}</script>', '', 0, '', '', NULL, 0, 'shipping_state', 'shipping_state', 1, 1, 1)";
    }
    $query = "SELECT id FROM #__nbill_profile_fields WHERE `name` = 'NBILL_CORE_shipping_postcode'";
    $nb_database->setQuery($query);
    if (!$nb_database->loadResult()) {
        $inserts++;
        $sql_profile[] = "INSERT INTO `#__nbill_profile_fields` (`ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `xref_sql`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `include_on_forms`, `show_on_summary`) VALUES (" . ($ordering + $inserts) . ", 'AA', 'NBILL_CORE_shipping_postcode', 'NBILL_SHIPPING_POSTCODE', 0, 'disabled=\"disabled\"', '', '', '<script type=\"text/javascript\">chk=document.getElementsByName(\\'ctl_NBILL_CORE_shipping_same\\');for(i=0;i<chk.length;++i){if(chk[i].id.length>0){if(chk[i].checked===false){document.getElementsByName(\\'ctl_NBILL_CORE_shipping_postcode\\')[0].disabled=false;}break;}}</script>', '', 0, '', '', NULL, 0, 'shipping_postcode', 'shipping_postcode', 1, 1, 1)";
    }
    $query = "SELECT id FROM #__nbill_profile_fields WHERE `name` = 'NBILL_CORE_shipping_country'";
    $nb_database->setQuery($query);
    if (!$nb_database->loadResult()) {
        $inserts++;
        $sql_profile[] = "INSERT INTO `#__nbill_profile_fields` (`ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `xref_sql`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `include_on_forms`, `show_on_summary`) VALUES (" . ($ordering + $inserts) . ", 'BB', 'NBILL_CORE_shipping_country', 'NBILL_SHIPPING_COUNTRY', 0, 'disabled=\"disabled\"', '', '', '<script type=\"text/javascript\">chk=document.getElementsByName(\\'ctl_NBILL_CORE_shipping_same\\');for(i=0;i<chk.length;++i){if(chk[i].id.length>0){if(chk[i].checked===false){document.getElementsByName(\\'ctl_NBILL_CORE_shipping_country\\')[0].disabled=false;}break;}}</script>', '\$\$\$sql=\"SELECT vendor_country FROM #__nbill_vendor WHERE default_vendor = 1\";nbf_cms::\$interop->database->setQuery(\$sql);return nbf_cms::\$interop->database->loadResult();\$\$', 0, '', 'country_codes', NULL, 0, 'shipping_country', 'shipping_country', 1, 1, 1);";
    }
    $sql[] = "UPDATE #__nbill_profile_fields SET ordering = ordering + $inserts WHERE ordering > $ordering";
    $sql = array_merge($sql, $sql_profile);

    $sql[] = "ALTER TABLE `#__nbill_orders` ADD `shipping_address_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `client_id`;";
    $sql[] = "ALTER TABLE `#__nbill_document` ADD `shipping_address_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `entity_id`;";
    $sql[] = "ALTER TABLE `#__nbill_vendor` ADD `delivery_small_print` TEXT NULL DEFAULT NULL AFTER `quote_small_print`;";
    $sql[] = "ALTER TABLE `#__nbill_vendor` ADD `delivery_template_name` VARCHAR(50) NOT NULL DEFAULT 'delivery_default' AFTER `quote_email_template_name`;";
    $sql[] = "ALTER TABLE `#__nbill_document` ADD `delivery_small_print` TEXT NULL DEFAULT NULL AFTER `small_print`;";

    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `negative_in_brackets` TINYINT NOT NULL DEFAULT '1' AFTER `currency_format`;";

    foreach ($sql as $query)
    {
        $nb_database->setQuery($query);
        $nb_database->query();
        if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
        {
            nbf_globals::$db_errors[] = $nb_database->_errorMsg . ' (SQL: ' . $nb_database->_sql . ')';
        }
    }

##### LANGUAGE UPDATE START #####
    //Configuration
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.1.0
define("NBILL_CFG_NEGATIVE_IN_BRACKETS", "Negative in Brackets?");
define("NBILL_CFG_INSTR_NEGATIVE_IN_BRACKETS", "Whether or not to show negative numbers in brackets. If this is set to '" . NBILL_NO . "', the minus symbol (-) will be used instead.");
define("NBILL_CFG_INSTR_DATABASE_FUNCTIONS_LITE", "Clear down or delete " . NBILL_BRANDING_NAME . " database tables, or migrate data from version 1.2.x. Clearing down the tables will delete all of your data, and allow you to start with a clean slate. Delete tables before uninstalling the component to completely remove the component from your system. DO NOT delete the tables if you want to upgrade to the latest version of " . NBILL_BRANDING_NAME . " and keep your data! NOTE: DELETING THE TABLES WILL MAKE " . nbf_common::nb_strtoupper(NBILL_BRANDING_NAME) . " INOPERABLE. After deleting, you must uninstall the component as it will no longer work. Migrating data from version 1.2.x will delete all existing data and copy the data from version 1.2.x instead (this might take some time).");
define("NBILL_CFG_CONFIRM_CLEAR_LITE", "Are you sure you want to permanently delete all of your data?");
define("NBILL_CFG_TABLES_CLEARED_LITE", "Tables Cleared. All of your data has been deleted.");
LANG_ADD;
    edit_language_item("configuration", $text_to_add);
    $text_to_add = array();

    //Invoices
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.1.0
define("NBILL_DELIVERY_SMALL_PRINT", "Delivery Note Small Print");
define("NBILL_INSTR_DELIVERY_SMALL_PRINT", "Any legal information you want to display on the delivery note (eg. returns policy).");
define("NBILL_DELIVERY_NOTE", "Delivery Note");
LANG_ADD;
    edit_language_item("invoices", $text_to_add);
    $text_to_add = array();

    //nBill
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.1.0
define("NBILL_ADDRESS_BILLING", "Billing Address");
define("NBILL_ADDRESS_SHIPPING", "Shipping Address");
define("NBILL_ADDRESS_SAME_AS_BILLING", "Same as Billing Address");
define("NBILL_SHIPPING_ADDRESS_1", "Shipping Address 1");
define("NBILL_SHIPPING_ADDRESS_2", "Shipping Address 2");
define("NBILL_SHIPPING_ADDRESS_3", "Shipping Address 3");
define("NBILL_SHIPPING_TOWN", "Shipping Town");
define("NBILL_SHIPPING_STATE", "Shipping State");
define("NBILL_SHIPPING_POSTCODE", "Shipping Postcode");
define("NBILL_SHIPPING_COUNTRY", "Shipping Country");
define("NBILL_INSTR_SHIPPING_ADDRESS_ID", "Enter the shipping address that will appear on delivery notes (if applicable).");
define("NBILL_SHIPPING_ADDRESS_ID", "Shipping Address");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();

    //Template
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.1.0
define("NBILL_PRT_DELIVERY_NOTE_TITLE", "DELIVERY NOTE");
define("NBILL_PRT_RELATED_INVOICE_NO", "Related Invoice No:");
LANG_ADD;
    edit_language_item("template.in", $text_to_add);
    $text_to_add = array();

    //Tax Summary
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.1.0
define("NBILL_VAT_RPT_SUBTOTAL", "Sub-Total (%s)");
LANG_ADD;
    edit_language_item("taxsummary", $text_to_add);
    $text_to_add = array();

    //Vendors
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.1.0
define("NBILL_DELIVERY_TEMPLATE", "Delivery Note Template");
define("NBILL_INSTR_DELIVERY_TEMPLATE", "Name of the HTML template to use for producing delivery notes (if applicable).");
define("NBILL_DELIVERY_SMALL_PRINT", "Delivery Note Small Print");
define("NBILL_INSTR_INVOICE_SMALL_PRINT_DE", "Enter any legal disclaimers etc. that you want to appear on delivery notes.");
LANG_ADD;
    edit_language_item("vendors", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}