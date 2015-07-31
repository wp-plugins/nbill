<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_9_0_to_2_9_1()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `timezone` VARCHAR(100) NOT NULL DEFAULT '';";
    $sql[] = "UPDATE `#__nbill_menu` SET `text` = 'NBILL_MNU_DASHBOARD' WHERE `#__nbill_menu`.`id` = 1;";
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `default_electronic` TINYINT NOT NULL DEFAULT '0' ;";
    $sql[] = "UPDATE `#__nbill_configuration` SET `api_url_eu_vat_rates` = 'http://www.nbill.co.uk/api/v1/eu_vat_rates.json', `api_url_geo_ip` = 'http://freegeoip.net/json/##ip##' WHERE id = 1;";
    $sql[] = "UPDATE #__nbill_document_transaction
                INNER JOIN #__nbill_transaction ON #__nbill_document_transaction.transaction_id = #__nbill_transaction.id
                SET #__nbill_document_transaction.`date` = #__nbill_transaction.`date`
                WHERE `#__nbill_document_transaction`.date = 0";
    $sql[] = "UPDATE #__nbill_display_options SET `value` = 3 WHERE `name` = 'net'";
    $sql[] = "UPDATE #__nbill_display_options SET `value` = 4 WHERE `name` IN ('quote_net', 'order_date', 'order_value', 'document_date')";
    $sql[] = "UPDATE #__nbill_display_options SET `value` = 5 WHERE `name` IN ('quote_date', 'renew_link', 'due_date_on_list')";
    $sql[] = "DELETE FROM #__nbill_payment_gateway WHERE gateway_id = 'paypal' AND g_key = 'use_curl'";

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
define("NBILL_CFG_TIMEZONE", "Timezone");
define("NBILL_CFG_INSTR_TIMEZONE", "In most cases you should leave this blank, and the value from your php.ini file will be used. If your php.ini file does not have a valid timezone specified, and you cannot update it, or you wish to override the value in php.ini, you can enter a PHP timezone string here (eg. 'Europe/London' or 'America/Chigaco'). See www.php.net/timezones for a full list.");
define("NBILL_CFG_DEFAULT_ELECTRONIC", "Default to Electronic Delivery?");
define("NBILL_CFG_INSTR_DEFAULT_ELECTRONIC", "Whether or not to default new products, invoice line items, quote line items, income, and expenditure records to being marked as electronic deliveries. Digital goods and services sold within the EU have to be flagged as electronic deliveries and must apply VAT according to the VAT rate of the country where the customer is located (check with your local tax authority to determine whether any particular product or service you supply comes under this regulation, but it would generally included things like downloadable products, website hosting, telecommunications, and broadcasting services). If all or most of your products are digital, and you supply to the EU, you should set this to 'yes'. This will NOT have any effect on any existing records. Any existing products, invoice line items, quote line items, income, or expenditure records that relate to digitally supplied products will need to be updated manually and flagged as electronic delivery.");
LANG_ADD;
    edit_language_item("configuration", $text_to_add);
    $text_to_add = array();

    //Contacts
    $text_to_replace['en-GB'] = 'define("NBILL_INSTR_ADDRESS"';
    $replace_with['en-GB'] = 'define("NBILL_INSTR_CONTACT_ADDRESS"';
    edit_language_item("contacts", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = 'define("NBILL_INSTR_WEBSITE"';
    $replace_with['en-GB'] = 'define("NBILL_INSTR_CONTACT_WEBSITE"';
    edit_language_item("contacts", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = 'define("NBILL_INSTR_TELEPHONE"';
    $replace_with['en-GB'] = 'define("NBILL_INSTR_CONTACT_TELEPHONE"';
    edit_language_item("contacts", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = 'define("NBILL_INSTR_TELEPHONE_2"';
    $replace_with['en-GB'] = 'define("NBILL_INSTR_CONTACT_TELEPHONE_2"';
    edit_language_item("contacts", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = 'define("NBILL_INSTR_MOBILE"';
    $replace_with['en-GB'] = 'define("NBILL_INSTR_CONTACT_MOBILE"';
    edit_language_item("contacts", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = 'define("NBILL_INSTR_FAX"';
    $replace_with['en-GB'] = 'define("NBILL_INSTR_CONTACT_FAX"';
    edit_language_item("contacts", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = 'define("NBILL_CONTACT_NAME_UNKNOWN"';
    $replace_with['en-GB'] = 'define("NBILL_CONTACT_CONTACT_NAME_UNKNOWN"';
    edit_language_item("contacts", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    //Display Options
    $text_to_replace['en-GB'] = 'HTML2PS/PDF';
    $replace_with['en-GB'] = 'DomPDF';
    edit_language_item("display", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_add['en-GB'] = <<<LANG_ADD
define("NBILL_DISPLAY_HTML_PREVIEW_QUOTE", "Show HTML Preview?");
define("NBILL_DISPLAY_HTML_PREVIEW_QUOTE_DESC", "Whether or not to show an icon allowing an HTML version of the quote to be shown in a popup window.");
define("NBILL_DISPLAY_PDF_QUOTE", "Show PDF Link for Quotes");
define("NBILL_DISPLAY_PDF_QUOTE_DESC", "Whether or not to provide a PDF quote in the front end (NOTE: PDF generation can be slow and use a lot of server resources, so use with caution! The PDF link will only appear if you have DomPDF installed. See %s)");
define("NBILL_DISPLAY_LOW_PRIORITY", "Low Priority");
define("NBILL_DISPLAY_MEDIUM_PRIORITY", "Medium Priority");
define("NBILL_DISPLAY_HIGH_PRIORITY", "High Priority");
LANG_ADD;
    edit_language_item("display", $text_to_add);
    $text_to_add = array();

    //Expenditure
    $text_to_add['en-GB'] = <<<LANG_ADD
//Version 3.0.0
define("NBILL_INSTR_EXP_TAX_RATE_AND_AMOUNT_ELEC", " If the payment relates to digital goods within the EU, it should be marked as an electronic delivery - which indicates that the rate of tax is applied according to the country of the customer (these amounts are shown separately on the tax summary report).");
LANG_ADD;
    edit_language_item("expenditure", $text_to_add);
    $text_to_add = array();

    //Income
    $text_to_add['en-GB'] = <<<LANG_ADD
define("NBILL_INSTR_TAX_RATE_AND_AMOUNT_ELEC", " If the income relates to digital goods within the EU, it should be marked as an electronic delivery - which indicates that the rate of tax is applied according to the country of the customer (these amounts are shown separately on the tax summary report).");
LANG_ADD;
    edit_language_item("income", $text_to_add);
    $text_to_add = array();

    //nBill
    $text_to_add['en-GB'] = <<<LANG_ADD
define("NBILL_MNU_DASHBOARD", "Dashboard");
define("NBILL_REMOTE_POST_ERROR", "Sorry, an error occurred whilst posting the form. Please contact us for assistance.");
define("NBILL_PAYMENT_PLAN", "Payment Plan");
define("NBILL_INSTR_PAYMENT_PLAN", "If a one-off amount is due, select which payment plan to implement when this order is paid for (NOTE: not all payment gateways support payment plans)");
define("NBILL_OLD_VERSION_CHECKER", "WARNING! Your branding file is set to look up the latest version number using an old version checker for nBill v2. Please check for new releases manually, or update your branding file.");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();

    //Order Forms
    $text_to_add['en-GB'] = <<<LANG_ADD
define("NBILL_FORM_VIEW_IN_FE", "View form in front end (opens in a new window)");
LANG_ADD;
    edit_language_item("orderforms", $text_to_add);
    $text_to_add = array();

    //Products
    $text_to_replace['en-GB'] = 'define("NBILL_DELETE_DISCOUNT"';
    $replace_with['en-GB'] = 'define("NBILL_DELETE_PRODUCT_DISCOUNT"';
    edit_language_item("products", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = 'define("NBILL_PLEASE_SELECT_DISCOUNT"';
    $replace_with['en-GB'] = 'define("NBILL_PLEASE_SELECT_PRODUCT_DISCOUNT"';
    edit_language_item("products", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    //Invoice template
    $text_to_replace['en-GB'] = 'define("NBILL_SCAN_QR_CODE", " or scan the following QR code:");';
    $replace_with['en-GB'] = 'define("NBILL_CLICK_OR_SCAN_QR_CODE", " or scan the following QR code:");';
    edit_language_item("template.in", $replace_with, $text_to_replace, null, true);
    $text_to_replace = array();
    $replace_with = array();

    //Multiple
    $text_to_replace['en-GB'] = '@define("NBILL_PAYMENT_PLAN", "Payment Plan") ;';
    $replace_with['en-GB'] = '';
    edit_language_item("orders", $replace_with, $text_to_replace, null, true);
    edit_language_item("invoices", $replace_with, $text_to_replace, null, true);
    edit_language_item("orderforms", $replace_with, $text_to_replace, null, true);
    $text_to_replace['en-GB'] = '@define("NBILL_PAYMENT_PLAN", "Payment Plan");';
    edit_language_item("orders", $replace_with, $text_to_replace, null, true);
    edit_language_item("invoices", $replace_with, $text_to_replace, null, true);
    edit_language_item("orderforms", $replace_with, $text_to_replace, null, true);
    $text_to_replace['en-GB'] = 'define("NBILL_PAYMENT_PLAN", "Payment Plan");';
    edit_language_item("orders", $replace_with, $text_to_replace, null, true);
    edit_language_item("invoices", $replace_with, $text_to_replace, null, true);
    edit_language_item("orderforms", $replace_with, $text_to_replace, null, true);

    $text_to_replace['en-GB'] = '@define("NBILL_INSTR_PAYMENT_PLAN", "If a one-off amount is due, select which payment plan to implement when this order is paid for (NOTE: not all payment gateways support payment plans)") ;';
    $replace_with['en-GB'] = '';
    edit_language_item("orders", $replace_with, $text_to_replace, null, true);
    edit_language_item("invoices", $replace_with, $text_to_replace, null, true);
    edit_language_item("orderforms", $replace_with, $text_to_replace, null, true);
    $text_to_replace['en-GB'] = '@define("NBILL_INSTR_PAYMENT_PLAN", "If a one-off amount is due, select which payment plan to implement when this order is paid for (NOTE: not all payment gateways support payment plans)");';
    edit_language_item("orders", $replace_with, $text_to_replace, null, true);
    edit_language_item("invoices", $replace_with, $text_to_replace, null, true);
    edit_language_item("orderforms", $replace_with, $text_to_replace, null, true);
    $text_to_replace['en-GB'] = 'define("NBILL_INSTR_PAYMENT_PLAN", "If a one-off amount is due, select which payment plan to implement when this order is paid for (NOTE: not all payment gateways support payment plans)");';
    edit_language_item("orders", $replace_with, $text_to_replace, null, true);
    edit_language_item("invoices", $replace_with, $text_to_replace, null, true);
    edit_language_item("orderforms", $replace_with, $text_to_replace, null, true);
##### LANGUAGE UPDATE END #####
}