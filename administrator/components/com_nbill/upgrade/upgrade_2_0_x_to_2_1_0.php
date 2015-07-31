<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_0_x_to_2_1_0()
{
    $nb_database = nbf_cms::$interop->database;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    //Allow order form pages to be translated using Joom!Fish
    $sql[] = "ALTER TABLE #__nbill_order_form_pages DROP PRIMARY KEY;";
    $sql[] = "ALTER TABLE #__nbill_order_form_pages ADD UNIQUE (`form_id`,`page_no`);";
    $sql[] = "ALTER TABLE #__nbill_order_form_pages ADD `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;";

    //New config option for specifying default user group(s) and switch to SSL for ALL nBill pages
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `default_user_groups` VARCHAR( 100 ) NOT NULL DEFAULT '' AFTER `select_users_from_list`";
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `all_pages_ssl` TINYINT NOT NULL DEFAULT '0' AFTER `switch_to_ssl`";

    //Move email invoice option from vendor to config
    $query = "SELECT email_invoice_option FROM #__nbill_vendor ORDER BY default_vendor DESC";
    $nb_database->setQuery($query);
    $eio = $nb_database->loadResult();
    if (!$eio)
    {
        $eio = "AA"; //Default
    }
    $sql[] = "ALTER TABLE `#__nbill_vendor` DROP `email_invoice_option`";
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `email_invoice_option`  CHAR( 2 ) NOT NULL DEFAULT 'AA'";
    $sql[] = "UPDATE #__nbill_configuration SET email_invoice_option = '$eio' WHERE id = 1";

    //Allow for multiple user groups per user
    $sql[] = "ALTER TABLE `#__nbill_product` ADD `multi_group` TINYINT NOT NULL DEFAULT '1' AFTER `user_group`";

    //Allow longer HTML strings in checkbox labels
    $sql[] = "ALTER TABLE `#__nbill_order_form_fields` CHANGE `checkbox_text` `checkbox_text` TEXT NOT NULL";
    $sql[] = "ALTER TABLE `#__nbill_profile_fields` CHANGE `checkbox_text` `checkbox_text` TEXT NOT NULL";

    //W3C Validator Compliance
    $sql[] = "UPDATE `#__nbill_xref_country_codes` SET `description` = 'ST VINCENT AND THE GRENADINES' WHERE `#__nbill_xref_country_codes`.`id` = 184;";

    //Support for compound discounts and fees and gateway fees
    $sql[] = "ALTER TABLE `#__nbill_discounts` ADD `is_fee` TINYINT NOT NULL DEFAULT '0' AFTER `id`";
    $sql[] = "ALTER TABLE `#__nbill_discounts` ADD `apply_to` VARCHAR(5) NOT NULL DEFAULT 'net' AFTER `amount`";
    $sql[] = "ALTER TABLE `#__nbill_discounts` ADD `is_compound` TINYINT NOT NULL DEFAULT '1' AFTER `apply_to`";
    $sql[] = "UPDATE #__nbill_menu SET ordering = ordering + 1 WHERE parent_id = 2 AND ordering > 7";
    $sql[] = "INSERT INTO `#__nbill_menu` (`id`, `parent_id`, `ordering`, `text`, `description`, `image`, `url`, `published`, `favourite`) VALUES (53, '2', '7', 'NBILL_MNU_FEES', 'NBILL_MNU_FEES_DESC', '[NBILL_FE]/images/icons/fees.gif', '[NBILL_ADMIN]&action=fees', '1', '0');";
    $sql[] = "UPDATE `#__nbill_discounts` SET `is_fee` = 1 WHERE (`percentage` < 0 AND `amount` = 0) OR (`percentage` = 0 AND `amount` < 0)";
    $sql[] = "ALTER TABLE `#__nbill_discounts` ADD `country` CHAR( 2 ) NOT NULL DEFAULT 'WW' AFTER `disqualifying_products` ";
    $sql[] = "ALTER TABLE `#__nbill_payment_gateway_config` ADD `voucher_code` VARCHAR(100) NOT NULL DEFAULT '' AFTER `display_name` ";
    $sql[] = "ALTER TABLE `#__nbill_payment_gateway_config` ADD `ordering` INT(11) NOT NULL";
    $sql[] = "ALTER TABLE `#__nbill_transaction` ADD `added_document_item_id` VARCHAR(255) NOT NULL DEFAULT ''";

    //Add offline payment as a payment gateway and allow for ordering
    $sql[] = "INSERT INTO `#__nbill_payment_gateway_config` (`gateway_id`, `display_name`, `ordering`, `published`) VALUES ('offline', 'NBILL_ARRANGE_OFFLINE', '0', '0');";
    $query = "SELECT gateway_id FROM #__nbill_payment_gateway_config ORDER BY gateway_id";
    $nb_database->setQuery($query);
    $gateways = $nb_database->loadObjectList();
    $ordering = 1;
    foreach ($gateways as $gateway)
    {
        $sql[] = "UPDATE #__nbill_payment_gateway_config SET ordering = $ordering WHERE gateway_id = '" . $gateway->gateway_id . "'";
        $ordering++;
    }

    //Allow gateway used to be recorded on income record
    $sql[] = "ALTER TABLE `#__nbill_transaction` CHANGE `method` `method` VARCHAR(100) NOT NULL ";

    //Allow for custom tax rate on an order
    $sql[] = "ALTER TABLE `#__nbill_orders` ADD `custom_tax_rate` DECIMAL(14, 6) NULL AFTER `net_price`";

    //Record more information about gateway transactions and allow for housekeeping
    $sql[] = "ALTER TABLE `#__nbill_gateway_tx` ADD `success_confirmed` TINYINT NOT NULL DEFAULT '0', ADD `last_updated` INT UNSIGNED NOT NULL DEFAULT '0'";
    $sql[] = "UPDATE #__nbill_gateway_tx SET last_updated = " . nbf_common::nb_time() . " WHERE 1";
    $sql[] = "ALTER TABLE `#__nbill_entity` ADD `last_updated` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
    $sql[] = "UPDATE #__nbill_entity SET last_updated = " . nbf_common::nb_time() . " WHERE 1";
    $sql[] = "ALTER TABLE `#__nbill_contact` ADD `last_updated` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
    $sql[] = "UPDATE #__nbill_contact SET last_updated = " . nbf_common::nb_time() . " WHERE 1";
    $sql[] = "INSERT INTO `#__nbill_menu` VALUES (54, 2, 13, 'NBILL_MNU_HOUSEKEEPING', 'NBILL_MNU_HOUSEKEEPING_DESC', '[NBILL_FE]/images/icons/housekeeping.gif', '[NBILL_ADMIN]&action=housekeeping', 1, 0)";

    //Extra quote features (pay offline, intro HTML, warning optional)
    $sql[] = "ALTER TABLE `#__nbill_vendor` ADD `quote_offline_pay_inst` TEXT NOT NULL AFTER `payment_instructions`";
    $sql[] = "ALTER TABLE `#__nbill_vendor` ADD `quote_default_intro` TEXT NOT NULL AFTER `po_no_locked`";
    $sql[] = "ALTER TABLE `#__nbill_document` ADD `quote_intro` TEXT NOT NULL AFTER `total_gross`";
    $sql[] = "ALTER TABLE `#__nbill_document` ADD `quote_show_warning` TINYINT NOT NULL DEFAULT '1' AFTER `pay_to_accept_quote`";

    //Language specification
    $sql[] = "ALTER TABLE `#__nbill_entity` ADD `default_language` VARCHAR(10) NOT NULL AFTER `postcode`";

    //New payment plan type
    $sql[] = "INSERT INTO `#__nbill_xref_plan_type` (`code` ,`description`) VALUES ('DX', 'NBILL_DEPOSIT_THEN_USER_CONTROLLED');";

    //Allow longer labels
    $sql[] = "ALTER TABLE `#__nbill_order_form_fields` CHANGE `label` `label` TEXT NOT NULL";
    $sql[] = "ALTER TABLE `#__nbill_profile_fields` CHANGE `label` `label` TEXT NOT NULL";

    //Admin via front end permissions
    $sql[] = "CREATE TABLE `#__nbill_user_admin` (`user_id` INT UNSIGNED NOT NULL, `admin_via_fe` TINYINT NOT NULL DEFAULT '0', PRIMARY KEY ( `user_id` ))";
    $sql[] = "INSERT INTO `#__nbill_menu` (`id`,`parent_id`,`ordering`,`text`,`description`,`image`,`url`,`published`,`favourite`) VALUES (NULL , '21', '7', 'NBILL_MNU_USER_ADMIN', 'NBILL_MNU_USER_ADMIN_DESC', '[NBILL_FE]/images/icons/user_admin.gif', '[NBILL_ADMIN]&action=user_admin', '1', '0')";
    $sql[] = "INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('admin_via_fe', 0);";

    //New logout link on display options
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('logout', 0)";

    //Fix pk problem
    $query = "SELECT code, count(*) AS n FROM #__nbill_xref_default_start_date GROUP BY code HAVING n>1";
    $nb_database->setQuery($query);
    if ($nb_database->loadResult())
    {
        //Delete and re-insert all data
        $sql[] = "DELETE FROM #__nbill_xref_default_start_date WHERE 1";
        $sql[] = "INSERT INTO `#__nbill_xref_default_start_date` (`code`, `description`) VALUES
                ('AA', 'NBILL_CFG_START_DATE_CURRENT_ONLY'),
                ('BB', 'NBILL_CFG_START_DATE_CURRENT_LAST'),
                ('CC', 'NBILL_CFG_START_DATE_QUARTER'),
                ('DD', 'NBILL_CFG_START_DATE_SEMI'),
                ('EE', 'NBILL_CFG_START_DATE_YEAR'),
                ('FF', 'NBILL_CFG_START_DATE_FIVE'),
                ('GG', 'NBILL_CFG_START_DATE_ALL');";
    }
    $sql[] = "ALTER TABLE `#__nbill_xref_default_start_date` ADD PRIMARY KEY(`code`)";

    //Store first and last name separately for greater accuracy
    $sql[] = "ALTER TABLE `#__nbill_contact` ADD `first_name` VARCHAR(100) NOT NULL DEFAULT '' AFTER `user_id`, ADD `last_name` VARCHAR(100) NOT NULL DEFAULT '' AFTER `first_name`";
    if (nbf_cms::$interop->char_encoding == 'utf-8')
    {
        $sql[] = "SET NAMES 'utf8'"; //Otherwise SUBSTRING is not multibyte safe
    }
    $sql[] = "UPDATE #__nbill_contact SET first_name = SUBSTRING_INDEX(`name`, ' ', 1), last_name = SUBSTRING(`name`, LENGTH(SUBSTRING_INDEX(`name`, ' ', 1)) + 2) WHERE 1";
    $sql[] = "UPDATE #__nbill_profile_fields SET contact_mapping = 'first_name' WHERE `name` = 'NBILL_CORE_first_name' AND contact_mapping = 'name'";
    $sql[] = "UPDATE #__nbill_profile_fields SET contact_mapping = 'last_name' WHERE `name` = 'NBILL_CORE_last_name' AND contact_mapping = 'name'";
    $sql[] = "UPDATE #__nbill_profile_fields SET contact_mapping = 'last_name' WHERE contact_mapping = 'name'";
    $sql[] = "UPDATE #__nbill_order_form_fields SET contact_mapping = 'first_name' WHERE `name` = 'NBILL_CORE_first_name' AND contact_mapping = 'name'";
    $sql[] = "UPDATE #__nbill_order_form_fields SET contact_mapping = 'last_name' WHERE `name` = 'NBILL_CORE_last_name' AND contact_mapping = 'name'";
    $sql[] = "UPDATE #__nbill_order_form_fields SET contact_mapping = 'last_name' WHERE contact_mapping = 'name'";
    $sql[] = "ALTER TABLE `#__nbill_contact` DROP `name`";

    foreach ($sql as $query)
    {
        $nb_database->setQuery($query);
        $nb_database->query();
        if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
        {
            nbf_globals::$db_errors[] = $nb_database->_errorMsg;
        }
    }

    //Increase size of all decimal fields from 14,6 to 20,6
    $sql = array(); //We do it separately as we want to ignore errors on this part (as some columns from the schema file might not exist yet)
    $file_names = array_diff(scandir(nbf_cms::$interop->nbill_admin_base_path . "/framework/database/schema/"), array('.', '..'));
    foreach($file_names as $file_name)
    {
        if (is_file(nbf_cms::$interop->nbill_admin_base_path . "/framework/database/schema/$file_name"))
        {
            $schema = @simplexml_load_file(nbf_cms::$interop->nbill_admin_base_path . "/framework/database/schema/$file_name");
            if ($schema)
            {
                foreach ($schema->columns->column as $column)
                {
                    if ($column->type == 'decimal')
                    {
                        $query = "ALTER IGNORE TABLE `#__nbill_" . $schema->name . "` CHANGE `" . (string)$column['name'] . "` `" . (string)$column['name'] . "` DECIMAL( 20, 6 )";
                        if (property_exists($column, "default"))
                        {
                            if (nbf_common::nb_strlen($column->default) > 0)
                            {
                                if ($column->default == "NULL" && $column->null == "NULL")
                                {
                                    $query .= " default NULL";
                                }
                                else
                                {
                                    $query .= " default '" . $column->default . "'";
                                }
                            }
                            else if ($column->null == "NOT NULL")
                            {
                                $query .= " default ''";
                            }
                        }
                        if (property_exists($column, 'null') && $column->null == "NOT NULL")
                        {
                            $query .= " NOT NULL";
                        }
                        $sql[] = $query;
                    }
                }
            }
        }
    }

    foreach ($sql as $query)
    {
        $nb_database->setQuery($query);
        $nb_database->query();
    }

    //Close any gaps in the gateway ordering
    $sql = "SELECT gateway_id, ordering FROM #__nbill_payment_gateway_config ORDER BY ordering";
    $nb_database->setQuery($sql);
    $gateways = $nb_database->loadObjectList();
    $ordering = 0;
    foreach ($gateways as $gateway)
    {
        $sql = "UPDATE #__nbill_payment_gateway_config SET ordering = $ordering WHERE gateway_id = '" . $gateway->gateway_id . "'";
        $nb_database->setQuery($sql);
        $nb_database->query();
        $ordering++;
    }

    //Close any gaps in the order form ordering
    $sql = "SELECT id, vendor_id, ordering FROM #__nbill_order_form ORDER BY vendor_id, ordering";
    $nb_database->setQuery($sql);
    $forms = $nb_database->loadObjectList();
    $ordering = 0;
    $last_vendor_id = -1;
    foreach ($forms as $form)
    {
        if ($form->vendor_id != $last_vendor_id)
        {
            $last_vendor_id = $form->vendor_id;
            $ordering = 0;
        }
        $sql = "UPDATE #__nbill_order_form SET ordering = $ordering WHERE id = " . intval($form->id);
        $nb_database->setQuery($sql);
        $nb_database->query();
        $ordering++;
    }

    //Create new xref tables for states
    $xref_tables = array();
    $xref_tables[] = "xref_states_au";
    $xref_tables[] = "xref_states_ca";
    $xref_tables[] = "xref_states_combined";
    $xref_tables[] = "xref_states_gb";
    $xref_tables[] = "xref_states_ie";
    $xref_tables[] = "xref_states_us";
    $xref_tables[] = "xref_states_us_and_ca";
    require_once(nbf_cms::$interop->nbill_admin_base_path . "/install.new.php");
    new_db_install($xref_tables, array("install.xref_states.sql"));

    //New config file options
    $text_to_replace = "}";
    $replace_with = <<<CONFIG_TO
    /** @var Whether or not to use the old fashioned MySQL library instead of MySQLi */
    public static \$mysql = false;
    /** @var Name of interop class to load. [AUTO] = detect automatically (recommended!) */
    public static \$interop_class = '[AUTO]';
    /** @var Location of CMS config file ([DEFAULT] = default location for the CMS, otherwise, specify full file path including file name) */
    public static \$cms_config_file = '[DEFAULT]';
}
CONFIG_TO;
    $content = @file_get_contents(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.config.php");
    if (nbf_common::nb_strpos($content, $replace_with) === false)
    {
        $content = str_replace($text_to_replace, $replace_with, $content);
        @file_put_contents(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.config.php", $content);
    }

##### LANGUAGE UPDATE START #####
    $text_to_replace = array();
    $replace_with = array();

    //Clients
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_WEBSITE", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_WEBSITE", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_TELEPHONE", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_TELEPHONE", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_TELEPHONE_2", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_TELEPHONE_2", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_COMPANY_NAME", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_COMPANY_NAME", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_CLIENT_ADD_NAME_TO_INVOICE", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_CLIENT_ADD_NAME_TO_INVOICE", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_ADDRESS_1", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_ADDRESS_1", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_ADDRESS_2", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_ADDRESS_2", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_ADDRESS_3", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_ADDRESS_3", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_TOWN", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_TOWN", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_STATE", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_STATE", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_POSTCODE", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_POSTCODE", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_CLIENT_COUNTRY", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_CLIENT_COUNTRY", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_MOBILE", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_MOBILE", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_FAX", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_FAX", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_CLIENT_CURRENCY", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_CLIENT_CURRENCY", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_CLIENT_TAX_ZONE", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_CLIENT_TAX_ZONE", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_MOBILE", "
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_MOBILE", "
LANG_TO;
    edit_language_item("clients", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Just in case this replacement has been run more than once, remove doubled-up slashes
    for ($i=0; $i<2; $i++)
    {
        $text_to_replace['en-GB'] = <<<LANG_FROM
////
LANG_FROM;
        $replace_with['en-GB'] = <<<LANG_TO
//
LANG_TO;
        edit_language_item("clients", $replace_with, $text_to_replace);
        $text_to_replace = array();
        $replace_with = array();
    }

    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_CLIENT_LANGUAGE", "Default Language");
define("NBILL_INSTR_CLIENT_LANGUAGE", "Where more than one language pack is installed, each client can be assigned a particular language which will be used by default in your website front-end and when sending emails to the client.");
LANG_ADD;
    edit_language_item("clients", $text_to_add);
    $text_to_add = array();

    //Configuration
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_CFG_DEFAULT_USER_GROUP", "Default User Group");
define("NBILL_CFG_INSTR_DEFAULT_USER_GROUP", "Default user group to which new users should be assigned when users are automatically created by " . NBILL_BRANDING_NAME . ".");
define("NBILL_INSTR_EMAIL_INVOICE_OPTIONS", "Specify the default invoice notification method when new invoices are generated. These values can be overridden for individual clients. If the default is set to send an e-mail to the client (either a notification or the actual invoice itself), the component front-end will allow the client to opt-out of this. If both the default setting against the vendor record AND the overriding value held against the client record both stipulate that no e-mail should be sent, the user will not be given the option to opt-in. <strong>Note:</strong> If sending automated e-mails, it is highly recommended to ensure that all client records have an associated user record so that they can log into the website front end to set their preferences and view their invoices. <strong>Also Note:</strong> Generating PDFs uses a lot of system resources - it is recommended to avoid this as a default, and only set it for those clients that really want it.");
define("NBILL_CFG_ALL_PAGES_SSL", "Use SSL for ALL pages?");
define("NBILL_CFG_INSTR_ALL_PAGES_SSL", "Force https for ALL " . NBILL_BRANDING_NAME . " pages in the website front end (note: affects pages output by " . NBILL_BRANDING_NAME . " only)");
LANG_ADD;
    edit_language_item("configuration", $text_to_add);
    $text_to_add = array();

    //Contacts
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_CONTACT_FIRST_NAME", "First Name");
define("NBILL_CONTACT_LAST_NAME", "Last Name");
LANG_ADD;
    edit_language_item("contacts", $text_to_add);
    $text_to_add = array();

    //Currencies
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_ERR_ISO_CODE_LENGTH", "ISO Currency Code must be exactly 3 characters long.");
LANG_ADD;
    edit_language_item("currency", $text_to_add);
    $text_to_add = array();

    //Discounts
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_DISCOUNT_APPLY_TO", "Apply to");
define("NBILL_INSTR_DISCOUNT_APPLY_TO", "If a percentage value is specified, indicate whether to calculate the discount value based on the net price, the tax amount, or the gross. In most cases this should be left as 'Net'");
define("NBILL_DISCOUNT_APPLY_NET", "Net");
define("NBILL_DISCOUNT_APPLY_TAX", "Tax");
define("NBILL_DISCOUNT_APPLY_GROSS", "Gross");
define("NBILL_DISCOUNT_COMPOUND", "Compound?");
define("NBILL_INSTR_DISCOUNT_COMPOUND", "Whether or not to calculate the value of the discount based on the running total (where more than one discount is applied in a single transaction). For example, if this is set to 'yes' and the net total of a transaction is \$100.00, and a 25% discount has already been applied, the value for this discount will be calculated based on a percentage of \$75.00, not \$100.00.");
define("NBILL_DISCOUNT_COUNTRY", "Country");
define("NBILL_INSTR_DISCOUNT_COUNTRY", "If you want to apply this discount only to clients within a particular country, specify the country here.");
LANG_ADD;
    edit_language_item("discounts", $text_to_add);
    $text_to_add = array();

    //Display Options
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_ALWAYS_SHOW", "Always Show");
define("NBILL_DISPLAY_LANGUAGE_SELECTION", "Show 'Choose Language' dropdown list?");
define("NBILL_DISPLAY_LANGUAGE_SELECTION_DESC", "Whether or not to show a list of languages for the client to choose from (only applicable if more than one " . NBILL_BRANDING_NAME . " language pack is installed). The language selected here will be used in the website front end when the client logs in and whenever automated e-mails are sent out for this client.");
define("NBILL_DISPLAY_ADMIN", "Display Link to Administrator");
define("NBILL_DISPLAY_ADMIN_DESC", "Whether or not to show a link to the " . NBILL_BRANDING_NAME . " administrator if the logged in user has been granted administration access (shows " . NBILL_BRANDING_NAME . " administration features within your website template) - if the logged in user has not been granted access, the link will not appear regardless of this setting.");
define("NBILL_DISPLAY_ADMIN_FULL", "Display Link to Administrator (Full Screen)");
define("NBILL_DISPLAY_ADMIN_FULL_DESC", "Whether or not to show a link to the " . NBILL_BRANDING_NAME . " administrator if the logged in user has been granted administration access (shows " . NBILL_BRANDING_NAME . " administration features within the browser window but without showing your website template thus allowing more room).");
define("NBILL_DISPLAY_ADMIN_NEW", "Open admin link in a new window?");
define("NBILL_DISPLAY_ADMIN_NEW_DESC", "Whether or not to open the " . NBILL_BRANDING_NAME . " administrator in a new window or tab.");
define("NBILL_DISPLAY_LOGOUT", "Show Logout Link?");
define("NBILL_DISPLAY_LOGOUT_DESC", "Whether or not to show a link that allows the user to logout.");
LANG_ADD;
    edit_language_item("display", $text_to_add);
    $text_to_add = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_DISPLAY_MY_PROFILE_HELP", "You can control what is displayed on the My Profile page by using the %s feature");
LANG_FROM;
        $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_DISPLAY_MY_PROFILE_HELP", "You can control the rest of what is displayed on the My Profile page by using the %s feature");
LANG_TO;
    edit_language_item("display", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //E-mail
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_EM_EMBEDDED_QUOTE_PAR_1", "To view your quote online and accept, partially accept, or reject it, please visit: %s.");
LANG_ADD;
    edit_language_item("email", $text_to_add);
    $text_to_add = array();

    //Form Editor
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_FORM_PRODUCT_ID", "Product ID");
define("NBILL_FORM_PRODUCT_SKU", "Product SKU");
define("NBILL_FORM_FIELD_PRODUCT_LIST", "Product List");
define("NBILL_FORM_FIELD_GATEWAY_LIST", "Gateway List");
define("NBILL_FORM_FILED_PRODUCT_LIST_ALL", "All");
define("NBILL_AUTO_SET_PRODUCT_ORDER_VALUE", "If a product is selected from this list, do you want it to be ordered when the form is submitted?");
define("NBILL_FORM_WARNING_DELETE_OPTIONS", "WARNING! Changing this field type will cause your manually defined list options to be deleted. Are you sure you want to do this?");
LANG_ADD;
    edit_language_item("form.editor", $text_to_add);
    $text_to_add = array();

    //Front-end
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_QUOTE_PAY_OFFLINE", "Please contact us to arrange payment by cash, cheque, or bank transfer. The quote will be held in a pending state until we have received your payment. Thank you.");
define("NBILL_QUOTE_NOT_FOUND", "Sorry, that quote could not be found.");
define("NBILL_CHOOSE_LANGUAGE", "Choose language");
define("NBILL_CHOOSE_LANGUAGE_HELP", "Whatever language you select here will be used for all of your billing information, including invoices and e-mails that are sent to you from our billing system. Your selection does not take effect until you click on `Submit` below.");
define("NBILL_QUOTE_ACCEPTED_AWAITING_PAYMENT_SUBJECT", "Quote Accepted - Awaiting Payment on %s");
define("NBILL_QUOTE_ACCEPTED_AWAITING_PAYMENT_BODY", "Quote %s has been accepted, or partially accepted by the client and is awaiting payment. Once payment is confirmed, the relevant order and invoice records will be created automatically. The following items have been accepted: \n\n%s");
define("NBILL_QUOTE_ACCEPTED_COMPLETED_SUBJECT", "Quote Accepted on %s");
define("NBILL_QUOTE_ACCEPTED_COMPLETED_BODY", "Quote %s has been accepted, or partially accepted by the client and the relevant order and invoice records have been created automatically. The following items have been accepted: \n\n%s");
define("NBILL_QUOTE_ITEM_ACCEPTED", "Accepted");
define("NBILL_QUOTE_ITEM_REJECTED", "Rejected");
define("NBILL_QUOTE_ITEM_AWAITING", "Awaiting Payment");
LANG_ADD;
    edit_language_item("frontend", $text_to_add);
    $text_to_add = array();

    //Gateway
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_GATEWAY_FEE_OR_DISCOUNT_CODE", "Fee or Discount Code");
define("NBILL_INSTR_GATEWAY_FEE_OR_DISCOUNT_CODE", "You can specify a voucher code here which can be used to apply either a fee or a discount (with a matching code) whenever someone pays using this payment method (by specifying a voucher code rather than the actual fee or discount, you can allow more than one fee or discount to be applied). All voucher codes that have been defined on your fee or discount records will be listed here. WARNING! Using this feature can cause invoice totals to be changed after the invoice has been created (to apply the gateway fee at the time of payment). If you want to use this feature then, make sure it is legal in your country to change an invoice amount even after the customer has received the invoice (if applicable).");
define("NBILL_GATEWAY_ORDERING", "Ordering");
define("NBILL_GATEWAY_OFFLINE_DESC", "Special built-in gateway for allowing selection of an offline payment method such as cash, cheque, or bank transfer.");
LANG_ADD;
    edit_language_item("gateway", $text_to_add);
    $text_to_add = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_GATEWAY_SUCCESS", "Thank you - your payment was received successfully.");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_GATEWAY_SUCCESS", "Thank you - your payment was received successfully."); //Note: You can use the following placeholders to represent transaction data which can be output in the thank you message (for use when integrating with affiliate tracking programs or to provide a custom payment confirmation screen): ##TX_ID## (transaction ID); ##AMOUNT## (amount of payment); ##CURRENCY## (currency used); ##ORDER_ID## (order record ID number, if known); ##ORDER_NO## (order number, if known). A transaction ID will always exist, but an order record might not.
LANG_TO;
    edit_language_item("gateway", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Income
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_RECEIVED_TAX_REF", "Client Tax Reference");
define("NBILL_INSTR_RECEIVED_TAX_REF", "The tax exemption code for the person who paid you, if applicable");
LANG_ADD;
    edit_language_item("income", $text_to_add);
    $text_to_add = array();

    //Invoices
    $text_to_replace['en-GB'] = <<<LANG_FROM
@@define
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
@define
LANG_TO;
    edit_language_item("invoices", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_CSV_ITEM_NO", " - Item %s");
define("NBILL_DOCUMENT_PAID", "Paid");
define("NBILL_DOCUMENT_NOT_PAID", "Not Paid");
define("NBILL_DOCUMENT_PART_PAID", "Part Paid");
LANG_ADD;
    edit_language_item("invoices", $text_to_add);
    $text_to_add = array();

    //Main
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTALL_ERROR", "Sorry, it looks like nBill failed to install correctly! Please try uninstalling and re-installing. If that does not help, please refer to the troubleshooting section of the documentation at <a href=\"http://" . NBILL_BRANDING_DOCUMENTATION . "\">" . NBILL_BRANDING_DOCUMENTATION . "</a>.<br /><br /><a href=\"index2.php\">Return to Home Page</a>");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_INSTALL_ERROR", "Sorry, it looks like nBill failed to install correctly! If you are using Joomla 1.5 or above and have moved the Joomla configuration.php file to a different location, you must enter the location of that file in the nBill configuration file (%s). Otherwise, please try uninstalling and re-installing. If that does not help, please refer to the troubleshooting section of the documentation at <a href=\"http://" . NBILL_BRANDING_DOCUMENTATION . "\">" . NBILL_BRANDING_DOCUMENTATION . "</a>.<br /><br /><a href=\"%s\">Return to Home Page</a>");
LANG_TO;
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_DOC_DESCRIPTION", "Description");
define("NBILL_MNU_FEES", "Fees");
define("NBILL_MNU_FEES_DESC", "Manage Fees");
define("NBILL_MNU_USER_ADMIN", "Administration");
define("NBILL_MNU_USER_ADMIN_DESC", "Set up permissions for users to access " . NBILL_BRANDING_NAME . " administration features via your website front end.");
define("NBILL_TB_QUOTE_PAID_OFFLINE", "Paid");
define("NBILL_TB_QUOTE_PAID_OFFLINE_DESC", "Click to confirm offline payment");
define("NBILL_MNU_HOUSEKEEPING", "Housekeeping");
define("NBILL_MNU_HOUSEKEEPING_DESC", "Delete old records");
define("NBILL_LANGUAGE_ENGLISH", "English");
define("NBILL_CSV_DOWNLOAD_LIST_DESC", "Download this list as a comma spearated values (CSV) file (which can be opened in a spreadsheet application such as MS Excel, or imported into another application)");
define("NBILL_CSV_COMPANY_NAME", "Organization Name");
define("NBILL_CSV_EXPORT_LIMIT_WARNING", "WARNING! CSV exports are limited to %s records. There are more than %s records on this list, so only the first %s will be exported.");
define("NBILL_LOGOUT", "Logout");
define("NBILL_FE_ADMIN_RETURN", "Return to website");
define("NBILL_FE_ADMIN_OPEN", "Open website in a new window");
define("NBILL_FE_ADMIN_WELCOME_LOGIN", "If the system administrator has enabled " . NBILL_BRANDING_NAME . " administration via the website front end, and has granted you access, please enter your username and password below.");
define("NBILL_FE_ADMIN_ACCESS_DENIED", "Access Denied");
define("NBILL_FE_ADMIN_ACCESS_DENIED_DESC", "Sorry, you do not have " . NBILL_BRANDING_NAME . " administration privileges via the website front end.");
define("NBILL_FE_ADMIN_ADMINISTRATOR", NBILL_BRANDING_NAME . " Administrator");
define("NBILL_FE_ADMIN_ADMINISTRATOR_DESC", "Access " . NBILL_BRANDING_NAME . " administrator functions (within the website template)");
define("NBILL_FE_ADMIN_ADMINISTRATOR_FULL_DESC", "Access " . NBILL_BRANDING_NAME . " administrator functions");
define("NBILL_FE_ADMIN_ADMINISTRATOR_NEW", " (Opens in a new window)");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();

    //Orderforms
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_ORDER_FORM_THANK_YOU_REDIRECT", "If you want to redirect the user to another page when an order is submitted instead of displaying the thank you message defined below, please enter a URL here. NOTE: This will have no effect if the payment gateway performs its own redirect.");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_INSTR_ORDER_FORM_THANK_YOU_REDIRECT", "If you want to redirect the user to another page when an order is submitted instead of displaying the thank you message defined below, please enter a URL here. NOTE: This will have no effect if the payment gateway performs its own redirect. You can use the following placeholders to represent transaction data which can be passed in the URL (for use when integrating with affiliate tracking programs or to provide a custom payment confirmation screen): ##TX_ID## (transaction ID); ##AMOUNT## (amount of payment); ##CURRENCY## (currency used); ##ORDER_ID## (order record ID number, if known); ##ORDER_NO## (order number, if known). A transaction ID will always exist, but an order record might not.");
LANG_TO;
    edit_language_item("orderforms", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_ORDER_FORM_THANK_YOU", "If the user is not redirected elsewhere by the payment gateway (or by your own redirect setting, above), this is the message that will be displayed on successful submission of the order form.");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_INSTR_ORDER_FORM_THANK_YOU", "If the user is not redirected elsewhere by the payment gateway (or by your own redirect setting, above), this is the message that will be displayed on successful submission of the order form. The transaction data placeholders mentioned above (in the description of the 'order complete redirect' setting) can also be used in the HTML code specified here (for example to include a tracking pixel for an affiliate system).");
LANG_TO;
    edit_language_item("orderforms", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Orders
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_ORDER_GOTO_CLIENT", "Go to Client record");
define("NBILL_ORDER_MULTI_STATUS_UPDATE", "Multiple Status Update");
define("NBILL_ORDER_SET_STATUS_TO", "Set all selected records to:");
define("NBILL_ORDER_MULTI_STATUS_SELECT", "Please select a status from the dropdown list");
define("NBILL_ORDER_MULTI_STATUS_SELECT_RECORDS", "Please check the box next to one or more records from the list of orders below");
define("NBILL_ORDER_MULTI_STATUS_SURE", "You are about to change the status of ALL of the selected orders. Are you sure you want to continue?");
LANG_ADD;
    edit_language_item("orders", $text_to_add);
    $text_to_add = array();

    //Payment Plans
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_PAYMENT_PLAN_TYPE", "Select the type of payment plan you require.<ul><li>'Payment Up Front' is the default action where the full amount owing is paid immediately.</li><li>'Installments' splits the amount owed into regular payments at intervals you define below.</li><li>'Deposit Plus Final Payment' takes a fixed amount or percentage immediately and allows the user to choose when to pay the balance (no further amount is taken automatically).</li><li>'Deposit Plus Installments' takes a fixed amount or percentage immediately, with the balance split into regular payments at intervals you define below (the initial deposit payment is classed as an installment).</li><li>'Deferred Payment' does not take any payment, and just waits for the user to pay when they are ready.</li><li>'User Controlled' allows the user to choose how much to pay up-front (if anything), and allows them to pay the rest (in multiple partial payments if required) when they are ready.</li></ul>");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_INSTR_PAYMENT_PLAN_TYPE", "Select the type of payment plan you require.<ul><li>'Payment Up Front' is the default action where the full amount owing is paid immediately.</li><li>'Installments' splits the amount owed into regular payments at intervals you define below.</li><li>'Deposit Plus Final Payment' takes a fixed amount or percentage immediately and allows the user to choose when to pay the balance (no further amount is taken automatically).</li><li>'Deposit Plus Installments' takes a fixed amount or percentage immediately, with the balance split into regular payments at intervals you define below (the initial deposit payment is classed as an installment).</li><li>'Deposit then User Controlled' takes a fixed amount or percentage immediately and then changes to 'user controlled' for the balance to be paid (in multiple partial payments if required) as and when the customer wishes (no further amount is taken automatically).</li><li>'Deferred Payment' does not take any payment, and just waits for the user to pay when they are ready.</li><li>'User Controlled' allows the user to choose how much to pay up-front (if anything), and allows them to pay the rest (in multiple partial payments if required) when they are ready.</li></ul>");
LANG_TO;
    edit_language_item("payment_plans", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Products
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_ENSURE_MAMBOT_PUBLISHED", "NOTE: As this product is a user subscription, please ensure you have installed and published the account expiry plugin (mambot) - available from " . NBILL_BRANDING_WEBSITE);
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_ENSURE_MAMBOT_PUBLISHED", "NOTE: As this product is a user subscription, please ensure you have installed and published the user subscription plugin (mambot) - available from " . NBILL_BRANDING_WEBSITE);
LANG_TO;
    edit_language_item("products", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_MULTI_GROUP", "Allow Multiple User Groups?");
define("NBILL_INSTR_MULTI_GROUP", "Whether or not to allow the user to belong to more than one group (if supported by the CMS). If this is set to 'yes', the user will be ADDED to the group represented by this product, but they will also continue to be members of any other groups they had access to before. If this is set to 'no', the user will be REMOVED from ALL other groups they belonged to, and will be assigned to the one represented by this product ONLY.");
define("NBILL_CSV_ITEM_CURRENCY", " - %s");
LANG_ADD;
    edit_language_item("products", $text_to_add);
    $text_to_add = array();

    //Quotes
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_QUOTE_ADMIN_AWAITING_PAYMENT", "(Awaiting Payment)");
define("NBILL_QUOTE_PAID_OFFLINE", "Click to register offline payment");
define("NBILL_QUOTE_PAY_OFFLINE_GENERATE", "This will generate order and/or invoice records for all accepted items on this quote and prompt you to enter the amount received. Are you sure?");
define("NBILL_QUOTE_OFFLINE_PAID_INVOICES_GENERATED", "%s invoice(s) generated");
define("NBILL_QUOTE_HTML_INTRO", "Quote Introduction");
define("NBILL_INSTR_QUOTE_HTML_INTRO", "You can enter any introductory text you like here, for example to explain the scope of the work.");
define("NBILL_QUOTE_SHOW_WARNING", "Show Warning?");
define("NBILL_INSTR_QUOTE_SHOW_WARNING", "Whether or not to show a javascript prompt warning that this is a legally binding contract and asking the user to confirm before accepting this quote.");
define("NBILL_QUOTE_ACCEPTED_BUT_AWAITING_PAYMENT", "Awaiting Payment");
LANG_ADD;
    edit_language_item("quotes", $text_to_add);
    $text_to_add = array();

    //Suppliers
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_SUPPLIER_LANGUAGE", "Default Language");
define("NBILL_INSTR_SUPPLIER_LANGUAGE", "Where more than one language pack is installed, each supplier can be assigned a particular language which will be used by default in your website front-end.");
LANG_ADD;
    edit_language_item("suppliers", $text_to_add);
    $text_to_add = array();

    //Vendors
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_PENDING_EMAIL_TEMPLATE", "Name of the HTML template to use for e-mailing pending order confirmation to the client (if applicable - configurable on each order form).");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_INSTR_PENDING_EMAIL_TEMPLATE", "Name of the HTML template to use for e-mailing pending order confirmation to the client (if applicable - whether or not to send an email is configurable on each order form).");
LANG_TO;
    edit_language_item("vendors", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.0
define("NBILL_QUOTE_DEFAULT_INTRO", "Quote Intro");
define("NBILL_INSTR_QUOTE_DEFAULT_INTRO", "You can optionally add an HTML introduction to your quotes. The value you specify here will be used as the default value for all new quote records (but you can override it for each individual quote if you wish).");
define("NBILL_QUOTE_PAY_INST", "Quote Offline Payment Instructions");
define("NBILL_INSTR_QUOTE_PAY_INST", "If the special 'offline' payment gateway is published, you can specify here how your client can make an offline payment when paying for a quote.");
LANG_ADD;
    edit_language_item("vendors", $text_to_add);
    $text_to_add = array();

    //XRef
    $text_to_add['en-GB'] = <<<LANG_ADD

//2.1.0 Payment Gateway
define("NBILL_ARRANGE_OFFLINE", "Offline Payment");

//2.1.0 Payment Plans
define("NBILL_DEPOSIT_THEN_USER_CONTROLLED", "Deposit then User Controlled");
LANG_ADD;
    edit_language_item("xref", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}