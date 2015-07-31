<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_1_0_to_2_1_1()
{
    $nb_database = nbf_cms::$interop->database;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    //Remove unwanted g_tx_ids from transaction table where data has been uploaded from a child database (messes up income processing when master catches up)
    $next_g_tx_id = 0;
    $query = "SHOW TABLE STATUS LIKE '#__nbill_gateway_tx'";
    $nb_database->setQuery($query);
    $nb_database->loadObject($g_tx_table);
    if ($g_tx_table && $g_tx_table->Auto_increment)
    {
        $next_g_tx_id = $g_tx_table->Auto_increment;
    }
    else
    {
        $query = "SELECT MAX(id) FROM #__nbill_gateway_tx";
        $nb_database->setQuery($query);
        $next_g_tx_id = intval($nb_database->loadResult()) + 1;
    }
    if ($next_g_tx_id > 1)
    {
        $sql[] = "UPDATE #__nbill_transaction SET g_tx_id = 0 WHERE g_tx_id >= " . intval($next_g_tx_id);
    }

    //Remove first/last name if it just consists of an empty space
    $sql[] = "UPDATE #__nbill_contact SET first_name = '' WHERE first_name = ' '";
    $sql[] = "UPDATE #__nbill_contact SET last_name = '' WHERE last_name = ' '";

    //New order form settings
    $sql[] = "ALTER TABLE `#__nbill_order_form` ADD `form_unavailable_message` TEXT NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_order_form` ADD `after_processing_code` TEXT NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_order_form_pages` ADD `legacy_renderer` TINYINT NOT NULL DEFAULT 0";
    $sql[] = "ALTER TABLE `#__nbill_order_form_pages` ADD `legacy_table_border` TINYINT NOT NULL DEFAULT 0";
    $sql[] = "UPDATE #__nbill_order_form_pages INNER JOIN #__nbill_order_form ON #__nbill_order_form_pages.form_id = #__nbill_order_form.id SET #__nbill_order_form_pages.legacy_renderer = #__nbill_order_form.legacy_renderer, #__nbill_order_form_pages.legacy_table_border = #__nbill_order_form.legacy_table_border";
    $sql[] = "ALTER TABLE `#__nbill_order_form` DROP `legacy_renderer`, DROP `legacy_table_border`";

    //Default 'relating to' display option to 'no' to avoid messing up the display on existing sites
    $sql[] = "INSERT INTO #__nbill_display_options (`name`, `value`) VALUES ('relating_to', '0')";

    //Custom tax rates and allow payment frequency change on renewal
    $sql[] = "ALTER TABLE `#__nbill_orders` CHANGE `custom_tax_rate` `custom_tax_rate` DECIMAL(20, 6) NULL DEFAULT NULL";
    $sql[] = "ALTER TABLE `#__nbill_product` ADD `custom_tax_rate` DECIMAL(20, 6) NOT NULL DEFAULT 0 AFTER `is_taxable`";
    $sql[] = "ALTER TABLE `#__nbill_product` ADD `allow_freq_change` TINYINT NOT NULL DEFAULT 0 AFTER `is_freebie`";
    $sql[] = "UPDATE `#__nbill_orders` SET `custom_tax_rate` = NULL WHERE 1"; //Start again - we don't want non-custom tax rates in here

    //Custom orders
    $sql[] = "ALTER TABLE `#__nbill_orders` ADD `custom_ledger_code` VARCHAR(20) NOT NULL DEFAULT '' AFTER `total_tax_amount`";

    //Remove any left-over prices in currencies that have been deleted
    $query = "SELECT code FROM #__nbill_currency WHERE 1";
    $nb_database->setQuery($query);
    $currencies = $nb_database->loadResultArray();
    $sql[] = "DELETE FROM #__nbill_product_price WHERE currency_code NOT IN ('" . implode("', '", $currencies) . "')";
    $sql[] = "DELETE FROM #__nbill_discount_currency_amount WHERE currency NOT IN ('" . implode("', '", $currencies) . "')";
    $sql[] = "DELETE FROM #__nbill_client_credit WHERE currency NOT IN ('" . implode("', '", $currencies) . "')";

    foreach ($sql as $query)
    {
        $nb_database->setQuery($query);
        $nb_database->query();
        if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
        {
            nbf_globals::$db_errors[] = $nb_database->_errorMsg;
        }
    }

##### LANGUAGE UPDATE START #####
    //Contacts
    $text_to_replace['en-GB'] = <<<LANG_FROM
mambot.
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
plugin.
LANG_TO;
    edit_language_item("contacts", $replace_with, $text_to_replace);
    edit_language_item("contacts", $replace_with, $text_to_replace); //We do it twice as the text appears twice
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
mambot?
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
plugin?
LANG_TO;
    edit_language_item("contacts", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Display
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.1
define("NBILL_DISPLAY_RELATING_TO", "Show 'relating to'");
define("NBILL_DISPLAY_RELATING_TO_DESC", "Whether or not to show a column to display the 'relating to' value (helps differentiate one order from another where the product name is the same).");
define("NBILL_DISPLAY_SUPPRESS_ZERO_TAX", "Suppress display of tax if no tax charged");
define("NBILL_DISPLAY_SUPPRESS_ZERO_TAX_DESC", "Whether to show or suppress tax rate and amount information on invoices where no tax was charged (if this is set to 'yes', and no tax was charged, tax amounts will be completely omitted from the invoice. If this is set to 'no', and no tax was charged, the tax rate and amount will be shown as 0.00 on the invoice). This is subject to using the default invoice template - custom invoice templates might not respect this setting.");
LANG_ADD;
    edit_language_item("display", $text_to_add);
    $text_to_add = array();

    //Invoices
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.1
define("NBILL_MULTI_INVOICE_UPDATE", "Multiple Invoice Update");
define("NBILL_MARK_INVOICES_AS", "Mark all selected invoices as:");
define("NBILL_MULTI_INVOICE_SELECT", "Please select what to do from the dropdown list");
define("NBILL_MULTI_INVOICE_SELECT_RECORDS", "Please check the box next to one or more records from the list of invoices below");
define("NBILL_MULTI_INVOICE_SURE", "You are about to change the status of ALL of the selected invoices. Are you sure you want to continue?");
define("NBILL_MULTI_INVOICE_COMPLETE", "%s invoices have been written off");
LANG_ADD;
    edit_language_item("invoices", $text_to_add);
    $text_to_add = array();

    //Form Editor
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.1
define("NBILL_OPTIONS_WARN_DUPLICATE_VALUES", "WARNING! You have more than one option defined with the same value. This might be OK if the duplicated values are just placeholders, but otherwise it should be avoided! Click OK to save anyway or Cancel to amend.");
define("NBILL_FORM_PAGE_LEGACY_RENDERER", "Render in a Table?");
define("NBILL_INSTR_FORM_PAGE_LEGACY_RENDERER", "If you set this to `yes`, the fields will be rendered in a table instead of being absolutely positioned. This means the fields might not be positioned exactly where you put them in the editor, but it can help resolve some layout problems (especially with the order summary control, which can vary in size depending on the values entered on the form by the end user).");
define("NBILL_FORM_PAGE_LEGACY_TABLE_BORDER", "Legacy Table Border?<br />(Deprecated)");
define("NBILL_INSTR_FORM_PAGE_LEGACY_TABLE_BORDER", "This setting is deprecated and should be set to `no`. In previous versions of " . NBILL_BRANDING_NAME . ", you could specify that the table containing your fields should have a border. This generally looks rubbish, is not semantic, and was a bad idea, but the option is included here for the benefit of those who are migrating from a previous version and wish to keep it. This option will only take effect if `Render in a Table` is set to `yes`.");
LANG_ADD;
    edit_language_item("form.editor", $text_to_add);
    $text_to_add = array();

    //Front End
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.1
define("NBILL_QUOTE_NOT_YET_AVAILABLE", "Your quote is currently being prepared. We will notify you when it is available to view. Thank you for your patience.");
define("NBILL_QUOTE_NOT_YET_AVAILABLE_ON_HOLD", "Your quote is not currently available to view because it has been put 'on hold' while we await further information. Click on '" . NBILL_FE_QUOTE_SHOW_CORRE . "' below to see any messages we have left for you, and/or use the form below to contact us or add further information.");
define("NBILL_FE_RELATING_TO", "Relating To");
define("NBILL_CHANGE", "Change...");
define("NBILL_UPDATE", "Update");
define("NBILL_PAY_FREQ_CHANGED", "Payment frequency has been changed from %s to %s. The order has NOT yet been renewed - check the details below and submit to proceed with renewal.");
LANG_ADD;
    edit_language_item("frontend", $text_to_add);
    $text_to_add = array();

    //Main
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.1
define("NBILL_PENDING_IF_PAID_ONLINE", "Only if paid online");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_UNKNOWN_EMAIL_ERROR", "The failure reason was not reported by the CMS
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_UNKNOWN_EMAIL_ERROR", "E-mail failed to send. The failure reason was not reported by the CMS
LANG_TO;
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Orders
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.1
define("NBILL_ORDER_CUSTOM_LEDGER_CODE", "Custom Ledger Code");
define("NBILL_ORDER_CUSTOM_TAX_RATE", "Custom Tax Rate");
define("NBILL_ORDER_CUSTOM_SETTINGS_SHOW", "+ Show Custom Settings");
define("NBILL_ORDER_CUSTOM_SETTINGS_HIDE", "+ Hide Custom Settings");
define("NBILL_ORDER_CUSTOM_SETTINGS_WARN", "(Only modify these values if you want to override the normal values or if there is no product selected, otherwise leave them blank)");
define("NBILL_ORDERS_CUSTOM_TAX_CHANGE_TITLE", "Custom Tax Rate Change");
define("NBILL_ORDERS_CUSTOM_TAX_CHANGE_INTRO", "You have updated a custom tax rate. Your changes have been saved, however, " . NBILL_BRANDING_NAME . " has detected %s order records that are using the same tax rate. Please select what action to take below.");
define("NBILL_ORDERS_CUSTOM_TAX_CHANGE_INTRO_AUTO", "%s order(s) which are set to auto-renew also currently hold a custom tax rate of %s;. Select one of the following options and click on the 'Save' toolbar button to update these records, or on 'Cancel' to continue without updating any orders.");
define("NBILL_ORDERS_CUSTOM_TAX_CHANGE_INTRO_MANUAL", "%s order(s) which are NOT set to auto-renew also currently hold a custom tax rate of %s. Select one of the following options and click on the 'Save' toolbar button to update these records, or on 'Cancel' to continue without updating any orders.");
define("NBILL_ORDERS_CUSTOM_RECALC_TAX_AND_NET", "Update custom tax rate from %s to %s, then re-calculate the tax and net amounts, keeping the gross amount the same");
define("NBILL_ORDERS_CUSTOM_RECALC_TAX_AND_GROSS", "Update custom tax rate from %s to %s, then re-calculate the tax and gross amounts, keeping the net amount the same");
define("NBILL_ORDERS_CUSTOM_NO_RECALC", "Update custom tax rate from %s to %s, but do not recalculate amounts (NOT RECOMMENDED)");
define("NBILL_ORDERS_CUSTOM_DO_NOTHING", "Do not update any of these orders");
define("NBILL_ORDERS_CUSTOM_RECOMMENDED", "(Recommended)");
define("NBILL_ORDER_CUSTOM_SHOW_ROWS", "+ Show Orders (allows selection of which order records to update)");
define("NBILL_ORDER_CUSTOM_HIDE_ROWS", "- Hide order records");
define("NBILL_ORDER_CUSTOM_UPDATED", "%s order(s) updated");
define("NBILL_USE_GLOBAL_RATE", "Global Tax Rate");
LANG_ADD;
    edit_language_item("orders", $text_to_add);
    $text_to_add = array();

    //Order Forms
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.1
define("NBILL_ORDER_FORM_UNAVAILABLE", "Form Unavailable Message");
define("NBILL_INSTR_ORDER_FORM_UNAVAILABLE", "Message to show if this form is not available (eg. if there are prerequisite products which the client does not have)");
define("NBILL_FORM_POST_PROCESS_CODE", "Post Process Code");
define("NBILL_INSTR_FORM_POST_PROCESS_CODE", "PHP code to evaluate after ALL other processing for this form is complete (ie. after the invoice has been generated and (where an online payment was made) marked as paid, if applicable - if `Pending until paid` is set to `yes`, this will only happen after payment has been received, or the pending order is activated by an administrator). This code will only be executed once per order form submission (unlike the order creation code, which is evaluated once for each product ordered and also on renewal). Available variables include the following (if applicable): \\\$client_id, \\\$order_ids[], \\\$document_ids[] (array of invoice IDs), \\\$transaction_id (income ID) - please load any other data you need from the database. If that doesn't make sense to you, just leave this blank.");
LANG_ADD;
    edit_language_item("orderforms", $text_to_add);
    $text_to_add = array();

    //Products
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.1
define("NBILL_PRODUCT_CUSTOM_TAX_RATE", "Custom Tax Rate");
define("NBILL_INSTR_PRODUCT_CUSTOM_TAX_RATE", "You can override the tax rate used when this product is ordered by specifying your own tax rate here (if the rate here is zero, the global tax rate will be applied). This rate will only be applied if a relevant global tax rate would normally take effect (so if tax would normally be omitted because the client has an exemption code, the custom tax rate would also be omitted). If you want the tax rate for this product to be 0%, you have to set 'Taxable?' to 'no' above, because entering zero here causes the global tax rate to be applied (you might also want to set the 'Suppress display of tax if no tax charged' display option to 'no' on the 'My Invoices' tab of the 'Display Options' page).");
define("NBILL_PRODUCT_ALLOW_FREQ_CHANGE", "Allow Frequency Change on Renewal?");
define("NBILL_INSTR_PRODUCT_ALLOW_FREQ_CHANGE", "Whether or not to allow the user to change the payment frequency on an order for this product when the order is renewed. If multiple orders are being renewed simultaneously, the option to change the frequency will only be offered if all orders allow it and all have the same frequency at the time of renewal. Only frequencies that have prices defined for the order currency will be offered (if only one frequency has a price defined, the option to change frequency will not be shown).");
define("NBILL_NOTE_USER_SUB", "Please Note: A 'User Subscription' in " . NBILL_BRANDING_NAME . " is a product which grants access to a particular user group. It has nothing to do with recurring payment frequencies. You do NOT need to mark your products as user subscriptions to take repeat payments, you ONLY need to do so if you want to restrict access to certain content on your website according to the user group that the user belongs to.");
LANG_ADD;
    edit_language_item("products", $text_to_add);
    $text_to_add = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
plugin (mambot)
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
plugin
LANG_TO;
    edit_language_item("products", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //VAT
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.1
define("NBILL_TAX_RATE_CHANGE_CHECK_PRODUCT_CUSTOM", "WARNING! You have one or more product records with a custom tax rate. If you want to change the custom tax rate on any products, please do so using the product editor.");
define("NBILL_TAX_RATE_CHANGE_CHECK_ORDERS_CUSTOM", "WARNING! You have one or more order records with a custom tax rate. If you want to change the custom tax rate on any orders, please do so using the order editor (if you have more than one order with the same custom tax rate, changing one of them will offer you the chance to update the others also).");
LANG_ADD;
    edit_language_item("vat", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}