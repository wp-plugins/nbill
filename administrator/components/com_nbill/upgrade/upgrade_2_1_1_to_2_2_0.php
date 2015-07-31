<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_1_1_to_2_2_0()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    //Silently delete nbill_rec.xml schema file if found and drop rec table
    if (@file_exists(nbf_cms::$interop->nbill_admin_base_path . "/framework/database/schema/rec.xml"))
    {
        @unlink(nbf_cms::$interop->nbill_admin_base_path . "/framework/database/schema/rec.xml");
    }
    $query = "DROP TABLE IF EXISTS `#__nbill_rec`";
    $nb_database->setQuery($query);
    $nb_database->query();

    //User records might have blank names due to a bug in 2.1.0
    $sql[] = "UPDATE `#__users` INNER JOIN #__nbill_contact ON #__nbill_contact.user_id = #__users.id SET #__users.`name` =
                CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name) WHERE #__users.`name` = ''";

    //Convert order forms to use new type column instead of old boolean and allow for extensions to use the form editor
    $sql[] = "ALTER TABLE `#__nbill_order_form` ADD `form_type` CHAR(2) NOT NULL DEFAULT 'OR' AFTER `is_quote_request`";
    $sql[] = "UPDATE #__nbill_order_form SET form_type = 'QU' WHERE is_quote_request = 1";
    $sql[] = "ALTER TABLE `#__nbill_order_form` DROP `is_quote_request`;";
    $sql[] = "ALTER TABLE `#__nbill_pending_orders` ADD `ext_order_activation_code` TEXT NOT NULL";
    $sql[] = "ALTER TABLE `#__nbill_order_form` ADD `always_show` TINYINT NOT NULL DEFAULT '0' AFTER `disqualifying_products`";

    //Allow extensions to appear on favourites
    $sql[] = "ALTER TABLE `#__nbill_extensions_menu` ADD `favourite` TINYINT NOT NULL DEFAULT '0'";

    //New Locale config setting
    $sql[] = "ALTER TABLE `#__nbill_configuration` ADD `locale` VARCHAR( 100 ) NOT NULL DEFAULT '' AFTER `date_format`";

    //Fields that are not null should have a default value if possible
    $sql[] = "ALTER TABLE `#__nbill_entity` CHANGE `default_language` `default_language` VARCHAR(10) NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_order_form_fields_options` CHANGE `related_product_quantity` `related_product_quantity` VARCHAR( 30 ) NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_orders` CHANGE `custom_ledger_code` `custom_ledger_code` VARCHAR( 20 ) NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_transaction` CHANGE `method` `method` VARCHAR( 100 ) NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_gateway_tx` CHANGE `callback_file` `callback_file` VARCHAR( 255 ) NOT NULL DEFAULT '', CHANGE `callback_function` `callback_function` VARCHAR( 255 ) NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_order_form` CHANGE `disqualifying_products` `disqualifying_products` VARCHAR( 100 ) NOT NULL DEFAULT ''";

    $sql[] = "CREATE TABLE IF NOT EXISTS `#__nbill_extension_form_events` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `form_id` INT UNSIGNED NOT NULL DEFAULT '0', `form_event_name` VARCHAR( 50 ) NOT NULL DEFAULT '', `extension_name` VARCHAR( 50 ) NOT NULL DEFAULT '', `code_to_run` TEXT NULL)";

    //Tie up orders to their quote items (not just the quote as a whole)
    $sql[] = "ALTER TABLE `#__nbill_orders` ADD `related_quote_item_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `related_quote_id`";

    //New quote status
    $sql[] = "INSERT INTO #__nbill_xref_quote_status (code, description) VALUES ('GG', 'NBILL_STATUS_QUOTE_WITHDRAWN');";

    foreach ($sql as $query)
    {
        $nb_database->setQuery($query);
        $nb_database->query();
        if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
        {
            nbf_globals::$db_errors[] = $nb_database->_errorMsg;
        }
    }

    //Convert all TEXT columns to allow NULLs
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
                    if (strtolower($column->type) == 'text')
                    {
                        $query = "ALTER TABLE `#__nbill_" . $schema->name . "` CHANGE `" . (string)$column['name'] . "` `" . (string)$column['name'] . "` TEXT NULL";
                        $nb_database->setQuery($query);
                        $nb_database->query(); //Ignore errors for these, as columns in the schema might not exist yet
                    }
                }
            }
        }
    }

##### LANGUAGE UPDATE START #####
    //Backup
    $text_to_replace['en-GB'] = <<<LANG_FROM
 strtoupper
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
 nbf_common::nb_strtoupper
LANG_TO;
    edit_language_item("backup", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Configuration
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.2.0
define("NBILL_CFG_LOCALE", "Locale");
define("NBILL_CFG_INSTR_LOCALE", "You can optionally enter a locale setting to control the formatting of numbers and processing of text. The value you use depends on your operating system. If you are not sure of the exact value to use, you can list several possible values separated by commas, and the first one that works will be used. For example, a German locale setting might look like this: 'de_DE@euro, de_DE, de, ge, deu_deu' (without the quote marks). NOTE: This setting will only take effect if the specified locale is installed on your server and the user that PHP runs under has permission to change the locale, so it will not work in every case.");
define("NBILL_CFG_TABLES_CLEARED_ERR", NBILL_BRANDING_NAME . " attempted to re-build its database tables, but one or more errors occurred. The error(s) reported are listed below.");
LANG_ADD;
    edit_language_item("configuration", $text_to_add);
    $text_to_add = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
 strtoupper
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
 nbf_common::nb_strtoupper
LANG_TO;
    edit_language_item("configuration", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Contacts
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.2.0
define("NBILL_CONTACT_CHECKING_EMAIL", "Checking E-mail Address");
LANG_ADD;
    edit_language_item("contacts", $text_to_add);
    $text_to_add = array();

    //Display
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.2.0
define("NBILL_DISPLAY_QUOTE_NO_DEFAULT_ACCEPT", "Default to NOT accepted");
define("NBILL_DISPLAY_QUOTE_NO_DEFAULT_ACCEPT_DESC", "Whether or not to assume that a quote has not been accepted until the client specifically marks it as accepted. You might want to set this to 'no' if you think your clients are likely struggle with the concept of checking the items they want to accept. If this is set to 'no', all items on a new quote will default to accepted and the client will have to manually uncheck items that they don't want to accept.");
LANG_ADD;
    edit_language_item("display", $text_to_add);
    $text_to_add = array();

    //E-mail
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.1.1
define("NBILL_EMAIL_MESSAGE_FROM_NAME", "From Name");
LANG_ADD;
    edit_language_item("email", $text_to_add);
    $text_to_add = array();

    //Fees
    $text_to_replace['en-GB'] = <<<LANG_FROM
the value for this discount
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
the value for this fee
LANG_TO;
    edit_language_item("fees", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Form Editor
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.2.0
define("NBILL_FORM_FIELD_ORDER_LIST", "Existing Order List");
define("NBILL_FORM_FIELD_PREREQ_ORDER_LIST", "Prerequisite Order List");
LANG_ADD;
    edit_language_item("form.editor", $text_to_add);
    $text_to_add = array();

    //Front-end
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.2.0
define("NBILL_QUOTE_SORRY_WITHDRAWN", "Sorry, this quote has been withdrawn.");
LANG_ADD;
    edit_language_item("frontend", $text_to_add);
    $text_to_add = array();

    //Invoices
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.2.0
define("NBILL_MULTI_PAID_SINGLE", "Paid (all in one go)");
define("NBILL_MULTI_PAID_MULTIPLE", "Paid (individually)");
LANG_ADD;
    edit_language_item("invoices", $text_to_add);
    $text_to_add = array();

    //Income
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.2.0
define("NBILL_CREATE_MULTIPLE_INCOMES", "Generate Multiple Income Records");
define("NBILL_CREATE_MULTIPLE_INCOMES_INTRO", "You have requested to mark selected invoices as paid in full. This feature will create a separate income record for each of the selected invoices, loading the relevant data (such as tax and ledger breakdowns) from the invoice record. Please also provide the following information which will be used for each income record generated, and click on the '" . NBILL_TB_GENERATE . "' toolbar button.");
define("NBILL_MULTI_INCOME_NO_INVOICES_FOUND", "No qualifying unpaid invoices were found. Process aborted.");
define("NBILL_CREATE_MULTIPLE_INCOMES_COMPLETE", "%s income records created.");
define("NBILL_CREATE_MULTIPLE_INCOMES_ERROR", "WARNING! One or more errors occurred whilst attempting to generate income records:");
define("NBILL_TB_MULTI_INCOME_GENERATE_WARNING", "WARNING! This will generate %s new income records.");
LANG_ADD;
    edit_language_item("income", $text_to_add);
    $text_to_add = array();

    //Ledger report
    $text_to_replace['en-GB'] = <<<LANG_FROM
make up the total. The 'Balance' column just refers to the net profit/loss for the given date range, and bears no relation to the balance in your bank account!
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
make up the total.
LANG_TO;
    edit_language_item("ledger_report", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //nBill
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.2.0
define("NBILL_POST_REMOTE_ERROR", "An error occurred whilst trying to connect to %s");
define("NBILL_DB_UPGRADE_ERRORS", NBILL_BRANDING_NAME . " has been upgraded, but one or more database errors occurred during the upgrade process. The errors are displayed below.");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();

    //Order Forms
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_EDIT_ORDER_FORM", "Edit Order Form");
define("NBILL_NEW_ORDER_FORM", "New Order Form");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_EDIT_ORDER_FORM", "Edit Form");
define("NBILL_NEW_ORDER_FORM", "New Form");
LANG_TO;
    edit_language_item("orderforms", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.2.0
define("NBILL_FORM_ALWAYS_SHOW", "Always show on form list?");
define("NBILL_INSTR_FORM_ALWAYS_SHOW", "Whether or not to show this form even if it is not available to the user (eg. because they are not logged in, or do not have a necessary prerequisite product). If this is set to 'yes', the form will always show up on the list of forms (as long as it is published), but if not available to the user, the link will be greyed out, and the 'form unavailable message' as defined above will be shown if the user hovers their mouse over the link.");
define("NBILL_FORM_UPLOAD_PATH_WARNING", "WARNING! It is recommended to ensure ALL file types are uploaded to an area of your account that is NOT publicly accessible (but still writable by the user PHP is running under).");
define("NBILL_FORM_UPLOAD_TYPE_WARNING", "WARNING! If you allow executable files to be uploaded (eg. .php, .pl), this could be a serious security risk. You should limit the file types to just those that you absolutely need to allow.");
LANG_ADD;
    edit_language_item("orderforms", $text_to_add);
    $text_to_add = array();

    //Products
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.2.0
define("NBILL_INSTR_PRODUCT_HTML_DESCRIPTION", "You can enter a more detailed description here. This will appear in the detailed description setting for any invoices or quotes relating to this product.");
LANG_ADD;
    edit_language_item("products", $text_to_add);
    $text_to_add = array();

    //Profile Fields
$text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_PROFILE_FIELD_PUBLISHED_No",
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_PROFILE_FIELD_PUBLISHED_NO",
LANG_TO;
    edit_language_item("profile_fields", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Quote Request Forms
$text_to_replace['en-GB'] = <<<LANG_FROM
they are allowed accept any part
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
they are allowed to accept any part
LANG_TO;
    edit_language_item("quote_request", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Quotes
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.2.0
define("NBILL_QUOTE_NO_INVOICE_GENERATED", "An invoice could not be generated for this quote. As a result, the quote status has been updated (pending items are now marked as accepted), but the receipt has not been recorded. You may need to create invoice and/or income records manually for this transaction.");
LANG_ADD;
    edit_language_item("quotes", $text_to_add);
    $text_to_add = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_QUOTE_STATUS", "NEW means that the client or potential client has requested a quote but you have not yet finalised the price (any items you may add to the quote are not visible to the user). <br />ON HOLD means you are awaiting further information from the client (any items you have added are not visible to the user). <br />QUOTED means you have supplied the price (any items you added to the quote, including the total, are visible to the user). <br />ACCEPTED means the user has accepted the entire quote - typically this would result in one or more order or invoice records being generated. <br />PART ACCEPTED means they have accepted one or more items, but not all items on the quote - typically this would result in one or more order or invoice records being generated.<br />REJECTED means the user has rejected the entire quote.");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_INSTR_QUOTE_STATUS", "NEW means that the client or potential client has requested a quote but you have not yet finalised the price (any items you may add to the quote are not visible to the user). <br />ON HOLD means you are awaiting further information from the client (any items you have added are not visible to the user). <br />QUOTED means you have supplied the price (any items you added to the quote, including the total, are visible to the user). <br />ACCEPTED means the user has accepted the entire quote - typically this would result in one or more order or invoice records being generated. <br />PART ACCEPTED means they have accepted one or more items, but not all items on the quote - typically this would result in one or more order or invoice records being generated.<br />REJECTED means the user has rejected the entire quote.<br />WITHDRAWN means that an administrator has decided that the quote should no longer be available to the client (it is effectively 'unpublished').");
LANG_TO;
    edit_language_item("quotes", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Reminders
$text_to_replace['en-GB'] = <<<LANG_FROM
#__inv_
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
#__nbill_
LANG_TO;
    edit_language_item("reminders", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Suppliers
$text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_USERNAME_PASSWORD_REQUIRED", "In order to create a new user, a user name and password are required.");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_USERNAME_PASSWORD_REQUIRED", "In order to create a new user, a user name, password, and e-mail address are required.");
LANG_TO;
    edit_language_item("suppliers", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Transaction report
    $text_to_replace['en-GB'] = <<<LANG_FROM
No income or expenditure items are excluded, but this report does not include any unpaid invoices.
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
No income or expenditure items are excluded, but this report does not include any unpaid invoices. The 'Balance' column just refers to the net profit/loss for the given date range, and bears no relation to the balance in your bank account!
LANG_TO;
    edit_language_item("transactions", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Transaction search
$text_to_replace['en-GB'] = <<<LANG_FROM
INV_TX_
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
NBILL_TX_
LANG_TO;
    edit_language_item("tx_search", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Xref
    $text_to_add['en-GB'] = <<<LANG_ADD

//2.2.0 Extra quote status
define("NBILL_STATUS_QUOTE_WITHDRAWN", "Withdrawn");
LANG_ADD;
    edit_language_item("xref", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####

    //Remove erroneous renew_order.php file, if present
    $file_name = nbf_cms::$interop->nbill_admin_base_path . "/events/order_renewed/renew_order.php";
    if (file_exists($file_name))
    {
        if (md5_file($file_name) == "e2291f9b144968a7cd849d1659327eb0")
        {
            @unlink($file_name);
        }
    }
}