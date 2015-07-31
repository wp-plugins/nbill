<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_6_2_to_2_9_0()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    //Remove deprecated table border setting, change legacy_renderer to renderer, allow rendering override on individual fields, allow guest-only forms
    $sql[] = "ALTER TABLE `#__nbill_order_form_pages` DROP `legacy_table_border`";
    $sql[] = "ALTER TABLE `#__nbill_order_form_pages` CHANGE `legacy_renderer` `renderer` TINYINT( 4 ) NOT NULL DEFAULT '0'";
    $sql[] = "ALTER TABLE `#__nbill_order_form_fields` ADD `override_absolute` TINYINT NOT NULL DEFAULT '0' AFTER `z_pos`";
    $sql[] = "ALTER TABLE `#__nbill_order_form` ADD `guests_only` TINYINT NOT NULL DEFAULT '0' AFTER `logged_in_users_only`";

    //Mobile friendly options for display
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('quote_date', '3') ON DUPLICATE KEY UPDATE `name`=`name`;";
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('quote_first_item', '2') ON DUPLICATE KEY UPDATE `name`=`name`;";
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('quote_net', '2') ON DUPLICATE KEY UPDATE `name`=`name`;";
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('invoice_link', '2') ON DUPLICATE KEY UPDATE `name`=`name`;";
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('order_date', '3') ON DUPLICATE KEY UPDATE `name`=`name`;";
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('expiry_date', '0') ON DUPLICATE KEY UPDATE `name`=`name`;";
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('product', '2') ON DUPLICATE KEY UPDATE `name`=`name`;";
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('order_value', '2') ON DUPLICATE KEY UPDATE `name`=`name`;";
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('frequency', '3') ON DUPLICATE KEY UPDATE `name`=`name`;";
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('document_date', '3') ON DUPLICATE KEY UPDATE `name`=`name`;";
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('due_date_on_list', '3') ON DUPLICATE KEY UPDATE `name`=`name`;";
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('first_item', '2') ON DUPLICATE KEY UPDATE `name`=`name`;";
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('net', '2') ON DUPLICATE KEY UPDATE `name`=`name`;";

    //New Dashboard
    $sql[] = "CREATE TABLE IF NOT EXISTS `#__nbill_widgets` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(100) NOT NULL DEFAULT '',
              `show_title` tinyint(4) NOT NULL DEFAULT '1',
              `published` tinyint(4) NOT NULL DEFAULT '1',
              `configurable` tinyint(4) NOT NULL DEFAULT '0',
              `params` text,
              `width` varchar(10) NOT NULL DEFAULT 'auto',
              `widget_type` varchar(50) NOT NULL DEFAULT 'html',
              `ordering` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=5;";
    $sql[] = "REPLACE INTO `#__nbill_widgets` (`id`, `title`, `show_title`, `published`, `configurable`, `params`, `width`, `widget_type`, `ordering`) VALUES
                (1, '', 1, 1, 1, '', '99%', 'html', 0),
                (2, '', 1, 1, 1, '', '49%', 'sales_graph', 1),
                (3, '', 1, 1, 1, '', '49%', 'orders_due', 2),
                (4, '', 1, 1, 1, '', 'auto', 'links', 3);";

    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `admin_custom_stylesheet` VARCHAR( 50 ) NOT NULL DEFAULT 'template_green.css'";

    //Due date on invoices
    $sql[] = "INSERT IGNORE INTO #__nbill_display_options (`name`, `value`) VALUES ('due_date', '0')";
    $sql[] = "INSERT IGNORE INTO #__nbill_display_options (`name`, `value`) VALUES ('generate_early', '0')";
    $sql[] = "ALTER TABLE `#__nbill_document` ADD `due_date` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `document_date` ";

    //Locale, decimal and currency handling
    $sql[] = "ALTER TABLE `#__nbill_configuration` CHANGE `locale` `locale` VARCHAR( 255 ) NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `precision_decimal` SMALLINT NOT NULL DEFAULT '2' AFTER `locale` ,
                ADD `precision_quantity` SMALLINT NOT NULL DEFAULT '0' AFTER `precision_decimal` ,
                ADD `precision_tax_rate` SMALLINT NOT NULL DEFAULT '2' AFTER `precision_quantity` ,
                ADD `precision_currency` SMALLINT NOT NULL DEFAULT '2' AFTER `precision_tax_rate` ,
                ADD `precision_currency_line_total` SMALLINT NOT NULL DEFAULT '2' AFTER `precision_currency` ,
                ADD `precision_currency_grand_total` SMALLINT NOT NULL DEFAULT '2' AFTER `precision_currency_line_total` ,
                ADD `thousands_separator` VARCHAR( 10 ) NOT NULL DEFAULT 'default' AFTER `precision_currency_grand_total` ,
                ADD `decimal_separator` VARCHAR( 10 ) NOT NULL DEFAULT 'default' AFTER `thousands_separator`,
                ADD `currency_format` VARCHAR( 25 ) NOT NULL DEFAULT '' AFTER `decimal_separator`;";
    $sql[] = "ALTER TABLE `#__nbill_currency` ADD `override_default_formatting` TINYINT NOT NULL DEFAULT '0' AFTER `symbol` ,
                ADD `precision_currency` SMALLINT NOT NULL DEFAULT '2' AFTER `override_default_formatting` ,
                ADD `precision_currency_line_total` SMALLINT NOT NULL DEFAULT '2' AFTER `precision_currency` ,
                ADD `precision_currency_grand_total` SMALLINT NOT NULL DEFAULT '2' AFTER `precision_currency_line_total` ,
                ADD `thousands_separator` VARCHAR( 10 ) NOT NULL DEFAULT 'default' AFTER `precision_currency_grand_total` ,
                ADD `decimal_separator` VARCHAR( 10 ) NOT NULL DEFAULT 'default' AFTER `thousands_separator`,
                ADD `currency_format` VARCHAR( 25 ) NOT NULL DEFAULT '' AFTER `decimal_separator`;";

    //New line item editor
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `use_legacy_document_editor` TINYINT NOT NULL DEFAULT '0'";
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `edit_products_in_documents` TINYINT NOT NULL DEFAULT '0'";
    $sql[] = "DELETE FROM #__nbill_display_options WHERE `name` = 'suppress_zero_tax'"; //Never worked well anyway!

    //VAT
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `auto_check_eu_vat_rates` TINYINT NOT NULL DEFAULT '1'";
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `eu_tax_rate_refresh_timestamp` INT UNSIGNED NOT NULL DEFAULT '0'";
    $sql[] = "INSERT IGNORE INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (28, 'HR', 'CROATIA');";
    $sql[] = "ALTER TABLE `#__nbill_tax` ADD `electronic_delivery` TINYINT NOT NULL DEFAULT '0'";
    $sql[] = "ALTER TABLE `#__nbill_tax` CHANGE `tax_name` `tax_name` VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_tax` CHANGE `tax_abbreviation` `tax_abbreviation` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_tax` CHANGE `tax_reference_no` `tax_reference_no` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_tax` CHANGE `tax_reference_desc` `tax_reference_desc` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_vendor` ADD `tax_reference_no` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `default_vendor`";
    $query = "SELECT id, small_print, payment_instructions FROM #__nbill_vendor ORDER BY id";
    $nb_database->setQuery($query);
    $vendors = $nb_database->loadObjectList();
    if ($vendors && count($vendors) > 0) {
        foreach ($vendors as $vendor)
        {
            $query = "SELECT id, tax_reference_no, country_code, small_print, payment_instructions FROM #__nbill_tax WHERE tax_reference_no != '' AND vendor_id = " . intval($vendor->id) . " ORDER BY country_code DESC";
            $nb_database->setQuery($query);
            $tax_codes = $nb_database->loadObjectList();
            $tax_ref = '';
            if ($tax_codes && count($tax_codes) > 0) {
                foreach ($tax_codes as $tax_code)
                {
                    $tax_ref = $tax_code->tax_reference_no;
                    if ($tax_code->country_code != 'WW' && $tax_code->country_code != 'EU') {
                        break;
                    }
                }
                $sql[] = "UPDATE #__nbill_vendor SET tax_reference_no = '" . $tax_ref . "' WHERE id = " . intval($vendor->id);

                foreach ($tax_codes as $tax_code)
                {
                    if (str_replace(' ', '', strip_tags($tax_code->payment_instructions)) == str_replace(' ', '', strip_tags($vendor->payment_instructions))) {
                        $sql[] = "UPDATE #__nbill_tax SET payment_instructions = NULL WHERE id = " . intval($tax_code->id);
                    }
                    if (str_replace(' ', '', strip_tags($tax_code->small_print)) == str_replace(' ', '', strip_tags($vendor->small_print))) {
                        $sql[] = "UPDATE #__nbill_tax SET small_print = NULL WHERE id = " . intval($tax_code->id);
                    }
                }
            }
        }
    }
    $sql[] = "ALTER TABLE `#__nbill_tax` DROP `tax_reference_no`";
    $sql[] = "ALTER TABLE `#__nbill_product` ADD `electronic_delivery` TINYINT NOT NULL DEFAULT '0'";
    $sql[] = "ALTER TABLE `#__nbill_document_items` ADD `electronic_delivery` TINYINT NOT NULL DEFAULT '0' AFTER `tax_rate_for_item`;";
    $sql[] = "ALTER TABLE `#__nbill_transaction` ADD `tax_rate_1_electronic_delivery` TINYINT NOT NULL DEFAULT '0' AFTER `tax_rate_1`;";
    $sql[] = "ALTER TABLE `#__nbill_transaction` ADD `tax_rate_2_electronic_delivery` TINYINT NOT NULL DEFAULT '0' AFTER `tax_rate_2`;";
    $sql[] = "ALTER TABLE `#__nbill_transaction` ADD `tax_rate_3_electronic_delivery` TINYINT NOT NULL DEFAULT '0' AFTER `tax_rate_3`;";
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `api_url_eu_vat_rates` VARCHAR(255) NOT NULL DEFAULT 'http://www.nbill.co.uk/api/v1/eu_vat_rates.json' AFTER `auto_check_eu_vat_rates`,
                ADD `geo_ip_lookup` TINYINT NOT NULL DEFAULT '1' AFTER `api_url_eu_vat_rates`,
                ADD `api_url_geo_ip` VARCHAR(255) NOT NULL DEFAULT 'http://freegeoip.net/json/##ip##' AFTER `geo_ip_lookup`;";
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `geo_ip_fail_on_mismatch` TINYINT NOT NULL DEFAULT '0' AFTER `api_url_geo_ip`;";
    $sql[] = "CREATE TABLE IF NOT EXISTS `#__nbill_entity_ip_address` (
              `entity_id` int(10) unsigned NOT NULL,
              `date` int(10) unsigned NOT NULL,
              `ip_address` varchar(50) NOT NULL DEFAULT '',
              `country_code` varchar(2) NOT NULL DEFAULT '',
              PRIMARY KEY (`entity_id`,`ip_address`)
            ) DEFAULT CHARSET=utf8;";
    $sql[] = "ALTER TABLE `#__nbill_pending_orders` ADD `ip_address` VARCHAR(50) NOT NULL DEFAULT '';";

    //Disable e-mail option
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `disable_email` TINYINT NOT NULL DEFAULT '0';";

    //Order details in front-end
    $sql[] = "ALTER TABLE `#__nbill_orders` ADD `form_id` INT UNSIGNED NOT NULL;";

    foreach ($sql as $query)
    {
        $nb_database->setQuery($query);
        $nb_database->query();
        if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
        {
            nbf_globals::$db_errors[] = $nb_database->_errorMsg . ' (SQL: ' . $nb_database->_sql . ')';
        }
    }

    $new_tables = array();
    $new_tables[] = "eu_tax_rate_info";
    $new_tables[] = "translation"; //Some who upgraded from Lite might be missing this
    require_once(nbf_cms::$interop->nbill_admin_base_path . "/install.new.php");
    new_db_install($new_tables, array('install.eu_vat_rates.sql'), false);

    $config = nBillConfigurationService::getInstance()->getConfig();
    $tax_service = new nBillTaxService(new nBillTaxMapper($nb_database, new nBillNumberFactory($config)), $config);
    if ($vendors && count($vendors) > 0) {
        foreach ($vendors as $vendor)
        {
            $tax_service->refreshEuTaxRecords($vendor->id);
        }
    }

##### LANGUAGE UPDATE START #####
    //Backup
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.0
define("NBILL_BACKUP_SMALL_ONLY", "This feature is provided for your convenience but it is only capable of backing up or restoring relatively small databases (as it loads the entire contents of each table into memory). For larger databases, you will need to use a separate tool (eg. phpMyAdmin, the mysqldump command line utility, or some other backup/restore script).");
LANG_ADD;
    edit_language_item("backup", $text_to_add);
    $text_to_add = array();

    //Clients
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.0
define("NBILL_CLIENT_IP_INFO", "IP Address Information");
define("NBILL_CLIENT_IP_INFO_INTRO", "The following IP address information has been detected for this client (country information is only collected if Geo-IP lookups are enabled in Global Configuration).");
define("NBILL_CLIENT_IP_DATE", "Date");
define("NBILL_CLIENT_IP_ADDRESS", "IP Address");
define("NBILL_CLIENT_IP_COUNTRY", "Country Code");
LANG_ADD;
    edit_language_item("clients", $text_to_add);
    $text_to_add = array();

    //Configuration
    $text_to_replace['en-GB'] = "'de_DE@euro, de_DE, de, ge, deu_deu'";
    $replace_with['en-GB'] = "'de_DE.UTF-8, de_DE.UTF-8@euro, de_DE@euro, de_DE, de, ge, deu_deu, deu, deutsch, german, German_Germany, German_Germany.1252'";
    edit_language_item("configuration", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.0
define("NBILL_CFG_PRECISION_DECIMAL", "Decimal Precision");
define("NBILL_CFG_INSTR_PRECISION_DECIMAL", "Numer of decimal places to use when handling general numeric values (maximum 6).");
define("NBILL_CFG_PRECISION_QUANTITY", "Quantity Precision");
define("NBILL_CFG_INSTR_PRECISION_QUANTITY", "Number of decimal places to use for quantities (maximum 6).");
define("NBILL_CFG_PRECISION_TAX_RATE", "Tax Rate Precision");
define("NBILL_CFG_INSTR_PRECISION_TAX_RATE", "Number of decimal places to use for tax rates (maximum 6).");
define("NBILL_CFG_PRECISION_CURRENCY", "Currency Precision");
define("NBILL_CFG_INSTR_PRECISION_CURRENCY", "Number of decimal places to use for general currency output (maximum 6).");
define("NBILL_CFG_PRECISION_CURRENCY_LINE_TOTAL", "Currency Line Total Precision");
define("NBILL_CFG_INSTR_PRECISION_CURRENCY_LINE_TOTAL", "Number of decimal places to use for line totals on quotes, invoices, reports, etc. (maximum 6).");
define("NBILL_CFG_PRECISION_CURRENCY_GRAND_TOTAL", "Currency Grand Total Precision");
define("NBILL_CFG_INSTR_PRECISION_CURRENCY_GRAND_TOTAL", "Number of decimal places to use for grand totals on quotes, invoices, reports, etc. (maximum 6).");
define("NBILL_CFG_THOUSANDS_SEPARATOR", "Thousands Separator");
define("NBILL_CFG_INSTR_THOUSANDS_SEPARATOR", "Custom thousands separator - if this is set to 'default', the separator from your server's current locale (or the locale setting above) will be used.");
define("NBILL_CFG_DECIMAL_SEPARATOR", "Decimal Separator");
define("NBILL_CFG_INSTR_DECIMAL_SEPARATOR", "Custom decimal separator - if this is set to 'default', the separator from your server's current locale (or the locale setting above) will be used.");
define("NBILL_CFG_CURRENCY_FORMAT", "Currency Format String");
define("NBILL_CFG_INSTR_CURRENCY_FORMAT", "By default, currency output will be based on the locale specified above, or the default locale of your server, however, you can override this here using the syntax of the PHP sprintf command. Please note however, that you must also specify a matching currency precision, above. For example, to output the amount 123.4567 as AU&#36;123.457 (ie. rounded to 3 decimal places, with an AU&#36; prefix), you would need to set the currency precision (above) to 3, and this currency format string to 'AU&#36;%01.3f' (without the quotes). To output the same value as '123 Reais' (ie. rounded to an integer, with a suffix of 'Reais'), you would use 0 (zero) as the currency precision, and the following currency format string: '%01f Reais' (without quotes). As sprintf does not support using a thousands separator, you will also need to specify a custom thousands separator, above, if using this setting. These values can be overridden on each currency record.");
define("NBILL_CFG_USE_LEGACY_DOCUMENT_EDITOR", "Use Legacy Document Editor?");
define("NBILL_CFG_INSTR_USE_LEGACY_DOCUMENT_EDITOR", "Whether or not to use the old (mobile-unfriendly) line item editor for invoices, quotes, and credit notes. You cannot manually modify the new 'electronic delivery' setting using the legacy editor, so it should not be used if you charge EU VAT. This setting is deprecated and will be removed in a future version.");
define("NBILL_CFG_EDIT_PRODUCTS_IN_DOCUMENTS", "Edit Products Within Documents?");
define("NBILL_CFG_INSTR_EDIT_PRODUCTS_IN_DOCUMENTS", "When you add a new line item in a document (quote, credit note, invoice), or edit an existing line item, and a SKU (product code) is present on the line item, if this option is set to 'yes', the line item editor will ask whether you want to save changes to the underlying product record. This effectively allows you to edit your products in-situ on an invoice or quote (but the prompts can be annoying if you often modify invoices or quotes without wanting to update the product record itself!).");
define("NBILL_CFG_AUTO_CHECK_EU_VAT_RATES", "Check for EU VAT rate changes?");
define("NBILL_CFG_INSTR_AUTO_CHECK_EU_VAT_RATES", "Whether or not to automatically connect to the " . NBILL_BRANDING_NAME . " server once a day and check whether any EU VAT rates have changed so you can be informed that you need to update your tax records. This will only happen if you have at least one product, invoice, or income record flagged for 'electronic delivery'. It is recommended that you have this set to 'yes' - you should only disable it if you do not charge VAT at all or are running a test installation of " . NBILL_BRANDING_NAME . ".");
define("NBILL_CFG_API_URL_EU_VAT_RATES", "EU VAT Rates API URL");
define("NBILL_CFG_INSTR_API_URL_EU_VAT_RATES", "The URL to connect to for looking up the current VAT rates of EU countries. You should generally leave this at the default value unless you have been specifically advised to change it.");
define("NBILL_CFG_GEO_IP_LOOKUP", "Geo-IP Lookup?");
define("NBILL_CFG_INSTR_GEO_IP_LOOKUP", "Whether or not to connect to a Geo-IP lookup service to find the country of a client when they place an order using an order form or request a quote using a quote request form. This is used to automatically populate the country for guest users. Also, if you have products which are delivered electronically within the EU, you might be expected to be able to show evidence that the client really consumed the service in the country they specified (so as to avoid people fraudulently entering a country with a lower VAT rate). Having this setting enabled simply records the country code (of the IP address that was used) in your database - if you want to enforce a match with the client record, there is a separate setting for that, below.");
define("NBILL_CFG_API_URL_GEO_IP", "Geo-IP API URL");
define("NBILL_CFG_INSTR_API_URL_GEO_IP", "The URL to connect to for looking up the the country code for a given IP address. Use ##ip## as a placeholder for the actual IP address. Valid values include 'http://freegeoip.net/json/##ip##' and 'http://ip-api.com/json/##ip##'. If either of those services go offline in future for any reason, a substitute URL can be entered here.");
define("NBILL_CFG_GEO_IP_FAIL_ON_MISMATCH", "Geo-IP Fail on Mis-match?");
define("NBILL_CFG_INSTR_GEO_IP_FAIL_ON_MISMATCH", "If this is set to 'yes', the client will not be able to proceed with an order or quote request unless the country of their IP address matches the country of their client record.");
define("NBILL_CFG_DISABLE_EMAIL", "Disable E-Mail?");
define("NBILL_CFG_INSTR_DISABLE_EMAIL", "Whether or not to completely stop " . NBILL_BRANDING_NAME . " (and only " . NBILL_BRANDING_NAME . ") from sending e-mails (useful for testing). " . NBILL_BRANDING_NAME . " will continue to behave as though e-mails have been sent succesfully (it will not report any errors), even though no e-mails have been sent.");
LANG_ADD;
    edit_language_item("configuration", $text_to_add);
    $text_to_add = array();

    //Contacts
    $text_to_replace['en-GB'] = "State/County/Province";
    $replace_with['en-GB'] = "State / County / Province";
    edit_language_item("contacts", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.0
define("NBILL_RESET_PASSWORD", "Reset Password");
LANG_ADD;
    edit_language_item("contacts", $text_to_add);
    $text_to_add = array();

    //Core Profile Fields
    $text_to_replace['en-GB'] = "State/County/Province";
    $replace_with['en-GB'] = "State / County / Province";
    edit_language_item("core.profile_fields", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    //Currency
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.0
define("NBILL_CURRENCY_OVERRIDE_DEFAULT_FORMATTING", "Override Formatting?");
define("NBILL_INSTR_CURRENCY_OVERRIDE_DEFAULT_FORMATTING", "Whether or not to override the precision (number of decimal places), separators (thousands and decimal), and currency symbol output for amounts expressed in this currency (if this setting is set to 'no', the values from the global configuration page will be used and the other settings relating to formatting on this tab will be ignored).");
define("NBILL_CURRENCY_PRECISION_CURRENCY", "Currency Precision");
define("NBILL_INSTR_CURRENCY_PRECISION_CURRENCY", "Number of decimal places to use for general currency output (maximum 6).");
define("NBILL_CURRENCY_PRECISION_CURRENCY_LINE_TOTAL", "Currency Line Total Precision");
define("NBILL_INSTR_CURRENCY_PRECISION_CURRENCY_LINE_TOTAL", "Number of decimal places to use for line totals on quotes, invoices, reports, etc. (maximum 6).");
define("NBILL_CURRENCY_PRECISION_CURRENCY_GRAND_TOTAL", "Currency Grand Total Precision");
define("NBILL_INSTR_CURRENCY_PRECISION_CURRENCY_GRAND_TOTAL", "Number of decimal places to use for grand totals on quotes, invoices, reports, etc. (maximum 6).");
define("NBILL_CURRENCY_THOUSANDS_SEPARATOR", "Thousands Separator");
define("NBILL_INSTR_CURRENCY_THOUSANDS_SEPARATOR", "Custom thousands separator - if this is set to 'default', the separator from your server's current locale (or the locale setting from the global configuration page) will be used.");
define("NBILL_CURRENCY_DECIMAL_SEPARATOR", "Decimal Separator");
define("NBILL_INSTR_CURRENCY_DECIMAL_SEPARATOR", "Custom decimal separator - if this is set to 'default', the separator from your server's current locale (or the locale setting from the global configuration page) will be used.");
define("NBILL_CURRENCY_CURRENCY_FORMAT", "Currency Format String");
define("NBILL_INSTR_CURRENCY_CURRENCY_FORMAT", "By default, currency output will be based on the locale specified in the global configuration, or the default locale of your server, however, you can override this here using the syntax of the PHP sprintf command. Please note however, that you must also specify a matching currency precision, above. For example, to output the amount 123.4567 as AU&#36;123.457 (ie. rounded to 3 decimal places, with an AU&#36; prefix), you would need to set the currency precision (above) to 3, and this currency format string to 'AU&#36;%01.3f' (without the quotes). To output the same value as '123 Reais' (ie. rounded to an integer, with a suffix of 'Reais'), you would use 0 (zero) as the currency precision, and the following currency format string: '%01f Reais' (without quotes). As sprintf does not support using a thousands separator, you will also need to specify a custom thousands separator, above, if using this setting.");
LANG_ADD;
    edit_language_item("currency", $text_to_add);
    $text_to_add = array();

    //Display Options
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.7.0
define("NBILL_DISPLAY_HIDE", "No");
define("NBILL_DISPLAY_OPTIONAL", "Optional");
define("NBILL_DISPLAY_IMPORTANT", "Prioritize");
define("NBILL_DISPLAY_ESSENTIAL", "Always show");
define("NBILL_DISPLAY_HTML_PREVIEW", "Show HTML Preview?");
define("NBILL_DISPLAY_HTML_PREVIEW_DESC", "Whether or not to show an icon allowing an HTML version of the invoice to be shown in a popup window.");

//Version 3.0.0
define("NBILL_DISPLAY_DUE_DATE", "Show Due Date on Invoice?");
define("NBILL_DISPLAY_DUE_DATE_AFTER", "%s after invoice date");
define("NBILL_DISPLAY_DUE_DATE_DESC", "Whether or not to show a due date as well as the invoice date. If this is set to 'yes', you can select how many days, weeks, or months after the invoice date the due date should be. It will then be calculated and displayed on the invoice, until such time as it has been paid in full.");
define("NBILL_DISPLAY_GENERATE_EARLY", "Generate invoices in advance?");
define("NBILL_DISPLAY_GENERATE_EARLY_DESC", "If a due date is being shown on the invoice, you can use this option to generate invoices for recurring orders in advance of the next due date on the order. For example, if you have an order with a next due date of 15th July, and you have the above '" . NBILL_DISPLAY_DUE_DATE . "' setting set to show the invoice due date 30 days after the invoice date, and this setting is set to 'yes', the invoice will be generated 30 days early - i.e. on 15th June (but the date range on the invoice will show the period starting from 15th July, and the due date will be shown as 15th July). This setting only applies to invoices that are generated based on your order records, and should be used in conjunction with a daily CRON job to generate your invoices automatically (see online documentation for more information).");
define("NBILL_DISPLAY_DUE_DATE_ON_LIST", "Show Due Date on List?");
define("NBILL_DISPLAY_DUE_DATE_ON_LIST_DESC", "Whether or not to show the invoice due date on the list of invoices (only applicable if '" . NBILL_DISPLAY_DUE_DATE . "' setting above is set to 'yes' OR the invoice has a manually entered due date.");
define("NBILL_DISPLAY_PAYLINK_QR_CODE", "Show QR Code?");
define("NBILL_DISPLAY_PAYLINK_QR_CODE_DESC", "Whether or not to show a QR code on invoices to enable the invoice to be paid by scanning with a mobile device. Only applicable if a payment link is shown on the invoice, and requires a remote call to the Google Chart API to obtain the QR code image data.");
LANG_ADD;
    edit_language_item("display", $text_to_add);
    $text_to_add = array();

    //Favourites
    $text_to_replace['en-GB'] = "Check the items that you want to appear as icons on the 'home' control panel";
    $replace_with['en-GB'] = "Check the items that you want to appear as links on the home control panel";
    edit_language_item("favourites", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    //Form Editor
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.7.0
define("NBILL_FORM_PAGE_RENDERER", "Renderer");
define("NBILL_FORM_PAGE_RENDERER_ABSOLUTE", "Absolute");
define("NBILL_FORM_PAGE_RENDERER_RESPONSIVE", "Responsive");
define("NBILL_FORM_PAGE_RENDERER_TABLE", "Table");
define("NBILL_INSTR_FORM_PAGE_RENDERER", "Form pages can be rendered in 3 different ways. Absolute rendering positions fields exactly where you put them in the editor (your template styling rules might cause them to take up more or less space than in the editor, so some adjustment may be required) - this is NOT responsive for mobile devices, but allows for multi-column layouts and free-format positioning of fields. Responsive rendering tries to adapt to the screen width for optimal display on both desktop and mobile devices, but may not be positioned exactly where you place them in the editor - although you can override it to use absolute positioning for certain fields (this is the recommended renderer for most pages). Table rendering outputs the fields in a table which can fix some rendering problems and is usually also responsive for mobile devices, but the fields are simply output sequentially.");
define("NBILL_FORM_FIELD_OVERRIDE_ABSOLUTE", "Absolute Position<br />Override");
define("NBILL_FORM_FIELD_OVERRIDE_ABSOLUTE_HELP", "When using the responsive renderer (on the page properties), you can set this property to `yes` to force this field to be positioned absolutely - ie. at the exact co-ordinates you specify (instead of rendering it sequentially). This setting has no effect unless the page uses responsive rendering (see page properties).");
LANG_ADD;
    edit_language_item("form.editor", $text_to_add);
    $text_to_add = array();

    //Front end
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.7.0
define("NBILL_ERR_DOWNLOAD_UNAVAVAILABLE_PLEASE_RENEW", "This download is no longer available as your order expired on %s.");
define("NBILL_HTML_INVOICE", "Display HTML Invoice");

//Version 3.0.0
define("NBILL_FE_DUE_DATE", "Due Date");
define("NBILL_GEO_IP_FAIL_FIELD", "Sorry your request cannot be processed because the country associated with your IP address (%1\\\$s) does not match your billing country (%2\\\$s). Please verify that your address details have been entered correctly.");
define("NBILL_GEO_IP_FAIL_ENTITY", "Sorry your request cannot be processed because the country associated with your IP address (%1\\\$s) does not match the billing country on your profile (%2\\\$s). Please verify that your address details have been entered correctly under 'My Profile'.");
define("NBILL_FE_ORDER_NO", "Order Number");
define("NBILL_FE_QUANTITY", "Quantity");
define("NBILL_ORDER_LAST_DUE_DATE", "Last Due Date");
define("NBILL_ORDER_NEXT_DUE_DATE", "Next Due Date");
define("NBILL_FE_FORM_FIELD_VALUES", "The following values were entered on the order form for this order.");
define("NBILL_FE_DOWNLOADS", "Downloads");
LANG_ADD;
    edit_language_item("frontend", $text_to_add);
    $text_to_add = array();

    $text_to_replace['en-GB'] = '"Accept/Reject"';
    $replace_with['en-GB'] = '"Accept / Reject"';
    edit_language_item("frontend", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = '"NBILL_FE_QUOTE_TOTAL_NET", "Net Total"';
    $replace_with['en-GB'] = '"NBILL_FE_QUOTE_TOTAL_NET", "Net"';
    edit_language_item("frontend", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = '"NBILL_FE_QUOTE_TOTAL_GROSS", "Gross Total"';
    $replace_with['en-GB'] = '"NBILL_FE_QUOTE_TOTAL_GROSS", "Gross"';
    edit_language_item("frontend", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = '"NBILL_FE_TOTAL_NET", "Net Total"';
    $replace_with['en-GB'] = '"NBILL_FE_TOTAL_NET", "Net"';
    edit_language_item("frontend", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = '"NBILL_FE_TOTAL_GROSS", "Gross Total"';
    $replace_with['en-GB'] = '"NBILL_FE_TOTAL_GROSS", "Gross"';
    edit_language_item("frontend", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    //Income
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.0
define("NBILL_TX_ELECTRONIC_DELIVERY", "Electronic Delivery?");
LANG_ADD;
    edit_language_item("income", $text_to_add);
    $text_to_add = array();

    //Invoices
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.0
define("NBILL_INVOICE_DUE_DATE", "Due Date");
define("NBILL_INSTR_INVOICE_DUE_DATE", "You can optionally enter a due date for the invoice - this overrides whatever you have set in Website-&gt;Display Options, on the My Invoices tab. If you manually enter a due date, it will be shown on the invoice regardless of the setting on the Display Options page. If you do not enter a due date, the settings in Display Options will be used.");
define("NBILL_INVOICE_ITEM_TOTALS", "Totals:");
define("NBILL_INVOICE_ITEM_ACTION", "Action");
define("NBILL_INVOICE_ITEM_EDIT", "Edit Item");
define("NBILL_INVOICE_LINE_ITEMS", "Line Items");
define("NBILL_LINE_ITEMS_UPDATING", "Updating...");
define("NBILL_DOC_SECTION_NAMED_SUBTOTAL", "%s Sub-Total: ");
define("NBILL_LINE_ITEM_REMOVE_PAGE_BREAK", "Delete Page Break");
define("NBILL_LINE_ITEM_SECTION_EDITOR", "Section Editor");
define("NBILL_DOC_SECTION_EDIT", "Edit Section Break");
define("NBILL_LINE_ITEM_EDITOR", "Line Item Editor");
define("NBILL_INVOICE_SCROLL_TO_ITEMS", "Jump to Line Items");
define("NBILL_INVOICE_SCROLL_TO_TOP", "Jump to Top");
define("NBILL_SHOW_PRODUCT_LIST", "Products");
define("NBILL_LINE_ITEM_ADD", "+ Add Item");
define("NBILL_LINE_ITEM_ELECTRONIC_DELIVERY", "Electronic Delivery?");
define("NBILL_DOMPDF_NOT_INSTALLED", "PDF Output not available - requires <a target=\\"_blank\\" href=\\"http://" . NBILL_BRANDING_HTML2PS . "\\">DomPDF</a>");
LANG_ADD;
    edit_language_item("invoices", $text_to_add);
    $text_to_add = array();

    //Invoice Template
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.0
define("NBILL_PRT_DUE_DATE", "Due Date:");
define("NBILL_SCAN_QR_CODE", " or scan the following QR code:");
define("NBILL_SCAN_HERE", "Scan here to pay this invoice.");
LANG_ADD;
    edit_language_item("template.in", $text_to_add);
    $text_to_add = array();

    //Main
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.7.0
define("NBILL_MAIN_DASHBOARD", "Dashboard");
LANG_ADD;
    edit_language_item("main", $text_to_add);
    $text_to_add = array();

    //nBill
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.7.0
define("NBILL_ADMIN_TAB_BASIC", "Basic");
define("NBILL_ADMIN_TAB_ADVANCED", "Advanced");
define("NBILL_RESPONSIVE_MENU", "%s Menu");
define("NBILL_STATIC_HELP_NONE", "Sorry, no help available for this setting - hopefully it is self-explanatory!");

//Version 3.0.0
define("NBILL_DAYS", "day(s)");
define("NBILL_WEEKS", "week(s)");
define("NBILL_MONTHS", "month(s)");
define("NBILL_YEARS", "year(s)");
define("NBILL_AJAX_GENERAL_ERROR", "Sorry! An error occurred. Please refresh the page and try again.");
define("NBILL_RANGE_CURRENT_MONTH", "Current Month");
define("NBILL_RANGE_PREVIOUS_MONTH", "Previous Month");
define("NBILL_RANGE_CURRENT_AND_PREVIOUS_MONTH", "Current and Previous Month");
define("NBILL_RANGE_CURRENT_QUARTER", "Current Quarter");
define("NBILL_RANGE_PREVIOUS_QUARTER", "Previous Quarter");
define("NBILL_RANGE_CURRENT_AND_PREVIOUS_QUARTER", "Current and Previous Quarter");
define("NBILL_RANGE_CURRENT_YEAR", "Current Year");
define("NBILL_RANGE_PREVIOUS_YEAR", "Previous Year");
define("NBILL_RANGE_CURRENT_AND_PREVIOUS_YEAR", "Current and Previous Year");
define("NBILL_RANGE_SPECIFIED", "Specified Date Range");
define("NBILL_WARNING_EMAIL_DISABLED", "WARNING! E-mails are currently disabled. " . NBILL_BRANDING_NAME . " will not send any e-mails under any circumstances. To turn e-mails back on, go to the <a href=\"%s\">Global Configuration</a> page.");
define("NBILL_DEFAULT_VAT_NAME", "Value Added Tax");
define("NBILL_DEFAULT_VAT_ABBREVIATION", "VAT");
define("NBILL_DEFAULT_VAT_TAX_REF_DESC", "VAT Number");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();

    $text_to_replace['en-GB'] = '"NBILL_CLIENT", "Client/Contact"';
    $replace_with['en-GB'] = '"NBILL_CLIENT", "Client / Contact"';
    edit_language_item("nbill", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    //Order Forms
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.0
define("NBILL_LARGE_SCREEN_WITH_POINTER_REQUIRED", "Sorry, the form editor has a large drag and drop interface and can only be used on screens with at least 900 pixels width, and a pointing device with hover capabilities (such as a mouse). Please use a desktop or laptop computer (or resize your browser) to define the fields of your form (you can still access the other tabs, above, on a mobile device though).");
define("NBILL_FORM_GUESTS_ONLY", "Guests Only?");
define("NBILL_INSTR_FORM_GUESTS_ONLY", "Whether to restrict this form to be available only to users who are NOT logged in.");
LANG_ADD;
    edit_language_item("orderforms", $text_to_add);
    $text_to_add = array();

    //Products
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.0
define("NBILL_PRODUCT_ELECTRONIC_DELIVERY", "Electronically Delivered?");
define("NBILL_INSTR_PRODUCT_ELECTRONIC_DELIVERY", "Whether or not this product is delivered electronically for the purposes of EU value added tax (and must therefore be charged using the tax rate prevailing in the country of the client rather than the vendor).");
LANG_ADD;
    edit_language_item("products", $text_to_add);
    $text_to_add = array();

    //Quotes
    $text_to_replace['en-GB'] = 'NBILL_QUOTE_CORRESPONDENCE", "Quote Correspondence';
    $replace_with['en-GB'] = 'NBILL_QUOTE_CORRESPONDENCE", "Quote Corre';
    edit_language_item("quotes", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = 'as any correspondence requesting';
    $replace_with['en-GB'] = 'as any <span class=\"word-breakable\">correspondence</span> requesting';
    edit_language_item("quotes", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    //Tax Summary
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.0
define("NBILL_VAT_RPT_ELECTRONIC_DELIVERY", "Electronic Delivery");
LANG_ADD;
    edit_language_item("taxsummary", $text_to_add);
    $text_to_add = array();

    //VAT
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 3.0.0
define("NBILL_TAX_ELECTRONIC_DELIVERY_ITEMS", "The following records are only used on products marked for 'electronic delivery'.");
define("NBILL_TAX_ADVANCED_INTRO", "You only need to specify values here if you want to override the values from the vendor record. In most cases you should leave these settings blank.");
define("NBILL_TAX_ELECTRONIC_DELIVERY", "Electronic Delivery Only?");
define("NBILL_INSTR_TAX_ELECTRONIC_DELIVERY", "Whether or not to use this tax rate only for products marked as electronically delivered. As of 1st January 2015, electronically delivered products must have value added tax charged at the prevailing rate in the country of the consumer, not the vendor. If this option is set to 'yes', this tax rate will be used in preference to any other rate for products marked as electronically delivered (you can still use a generic EU tax rate, based on your own country's rate, for other products). This setting has no effect on the special 'Worldwide' or 'European Union' country values, it is only for specific countries.");
define("NBILL_TAX_RATE_AUTO_CHANGE_WARNING", "WARNING! The EU VAT rate for electronic supplies has changed for country code '%1\\\$s' from %2\\\$s to %3\\\$s, but there are already orders with recurring payment frequencies that are using the old rate. Future invoices for these orders will continue to be produced at the old rate if the order records are not updated. The VAT rate change has NOT yet been saved. Please select what action to take below (please note, you might see this message several times if you have more than one vendor record, or if more than one VAT rate has changed - once all your VAT rates for all vendors are up-to-date, these messages will go away. To stop automatic VAT rate updates, please refer to the 'advanced' tab of the Global Configuration page).");
LANG_ADD;
    edit_language_item("vat", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}