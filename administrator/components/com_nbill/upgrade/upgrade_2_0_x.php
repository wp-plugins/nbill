<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_0_4_to_2_0_5()
{
    //If quote request form email template is set to quote_email_default, change it to quote_request_email_default
    $nb_database = nbf_cms::$interop->database;

    $sql = "UPDATE #__nbill_vendor SET qrc_email_template_name = 'quote_request_email_default' WHERE qrc_email_template_name = 'quote_email_default'";
    $nb_database->setQuery($sql);
    $nb_database->query();
}

function upgrade_2_0_5_to_2_0_6()
{
##### LANGUAGE UPDATE START #####
    $text_to_replace = array();
    $replace_with = array();

    //Favourites
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_FAVOURITE_YES", "No");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_FAVOURITE_NO", "No");
LANG_TO;
    edit_language_item("favourites", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //nBill
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_MNU_POTENTIAL_CLIENTS_HELP
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_MNU_POTENTIAL_CLIENTS_DESC
LANG_TO;
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_MNU_PROFILE_FIELDS_HELP
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_MNU_PROFILE_FIELDS_DESC
LANG_TO;
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_MNU_QUOTE_REQUEST_HELP
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_MNU_QUOTE_REQUEST_DESC
LANG_TO;
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_MNU_QUOTES_HELP
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_MNU_QUOTES_DESC
LANG_TO;
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_MNU_PAYMENT_PLANS_HELP
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_MNU_PAYMENT_PLANS_DESC
LANG_TO;
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Payment plans
    $text_to_replace['en-GB'] = <<<LANG_FROM
the initial deposit payment is not classed as an installment
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
the initial deposit payment is classed as an installment
LANG_TO;
    edit_language_item("payment_plans", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();
##### LANGUAGE UPDATE END #####
}

function upgrade_2_0_6_to_2_0_7()
{
##### LANGUAGE UPDATE START #####
    $text_to_replace = array();
    $replace_with = array();

    //Frontend
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_FORM_NEXT", "Next >>");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_FORM_NEXT", "Next >>");
LANG_TO;
    edit_language_item("frontend", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_FORM_PREV", "<< Previous");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
//define("NBILL_FORM_PREV", "<< Previous");
LANG_TO;
    edit_language_item("frontend", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Invoices
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_PAYMENT_PLAN", "Payment Plan");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
@define("NBILL_PAYMENT_PLAN", "Payment Plan") ;
LANG_TO;
    edit_language_item("invoices", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_INSTR_PAYMENT_PLAN", "If a one-off amount is due, select which payment plan to implement (NOTE: not all payment gateways support payment plans)");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
@define("NBILL_INSTR_PAYMENT_PLAN", "If a one-off amount is due, select which payment plan to implement (NOTE: not all payment gateways support payment plans)") ;
LANG_TO;
    edit_language_item("invoices", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();
##### LANGUAGE UPDATE END #####
}

function upgrade_2_0_7_to_2_0_8()
{
    //If core country field does not have an xref, associate the country_codes xref with it
    $nb_database = nbf_cms::$interop->database;

    $sql = "UPDATE #__nbill_order_form_fields SET xref = 'country_codes' WHERE name = 'NBILL_CORE_country' AND xref = ''";
    $nb_database->setQuery($sql);
    $nb_database->query();
}

function upgrade_2_0_8_to_2_0_9()
{
    //In case product vendor has changed, remove any excess product price records
    $nb_database = nbf_cms::$interop->database;

    $sql = "DELETE #__nbill_product_price.*
            FROM #__nbill_product_price
            LEFT JOIN `#__nbill_product` ON #__nbill_product_price.product_id = #__nbill_product.id
            AND #__nbill_product_price.vendor_id = #__nbill_product.vendor_id
            WHERE #__nbill_product.vendor_id is NULL";
    $nb_database->setQuery($sql);
    $nb_database->query();

##### LANGUAGE UPDATE START #####
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();

    //Form Editor
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_FORM_FIELD_XREF_HELP", "If you want to populate the options of a dropdown list or option list based on the values held in a database table (rather than defining each option manually), you can specify the table here. To add your own cross reference table, just create a table in the database with a prefix of `%s`");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_FORM_FIELD_XREF_HELP", "If you want to populate the options of a dropdown list or option list based on the values held in a database table (rather than defining each option manually), you can specify the table here. To add your own cross reference table, just create a table in the database with a prefix of `%snbill_xref_`");
LANG_TO;
    edit_language_item("form.editor", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Profile Fields
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.0.9
define("NBILL_PROFILE_FIELD_PUBLISHED_YES", "Published");
define("NBILL_PROFILE_FIELD_PUBLISHED_NO", "Not Published");
LANG_ADD;
    edit_language_item("profile_fields", $text_to_add);
    $text_to_add = array();

    //Contacts
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_USERNAME_PASSWORD_REQUIRED", "In order to create a new user, a user name and password are required.");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_USERNAME_PASSWORD_REQUIRED", "In order to create a new user, a user name, password, and e-mail address are required.");
LANG_TO;
    edit_language_item("contacts", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Quotes
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.0.9
define("NBILL_QUOTE_PAY_FREQ_CHANGED", "WARNING! You have changed the payment frequency but the price will not be changed automatically. Please check that the price you are quoting is still correct.");
LANG_ADD;
    edit_language_item("quotes", $text_to_add);
    $text_to_add = array();

    //Reminders
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_REMINDER_PAYMENT_DUE_SUBJECT", "Reminder: Payment Due");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_REMINDER_PAYMENT_DUE_SUBJECT", "%s Reminder: Payment Due");
LANG_TO;
    edit_language_item("reminders", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_REMINDER_ORDER_EXPIRY_SUBJECT", "Reminder: Order Expiry");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_REMINDER_ORDER_EXPIRY_SUBJECT", "%s Reminder: Order Expiry");
LANG_TO;
    edit_language_item("reminders", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_REMINDER_RENEWAL_DUE_SUBJECT", "Reminder: Renewal Due");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_REMINDER_RENEWAL_DUE_SUBJECT", "%s Reminder: Renewal Due");
LANG_TO;
    edit_language_item("reminders", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_REMINDER_INVOICE_OVERDUE_SUBJECT", "Reminder: Invoice Overdue");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_REMINDER_INVOICE_OVERDUE_SUBJECT", "%s Reminder: Invoice Overdue");
LANG_TO;
    edit_language_item("reminders", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_REMINDER_USER_DEFINED_SUBJECT", "Reminder");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_REMINDER_USER_DEFINED_SUBJECT", "%s Reminder");
LANG_TO;
    edit_language_item("reminders", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Order forms
    $text_to_replace['en-GB'] = <<<LANG_FROM
define("NBILL_ORDER_FORM_DUPLICATE_PRODUCTS", "WARNING! You have assigned a product to a field TWICE on the order form(s) highlighted below. This will result in the product being ordered twice. You should only assign a product on EITHER the edit field popup OR the options popup, not both.");
LANG_FROM;
    $replace_with['en-GB'] = <<<LANG_TO
define("NBILL_ORDER_FORM_DUPLICATE_PRODUCTS", "WARNING! You have assigned a product to a field TWICE on the order form(s) highlighted below. This will result in the product being ordered twice. You should only assign a product on EITHER the field properties pane OR the options popup, not both.");
LANG_TO;
    edit_language_item("orderforms", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.0.9
define("NBILL_ORDER_FORM_UNMAPPED", "WARNING! You have specified that the form(s) highlighted below should automatically create order records, but none of the fields on the form are mapped to any client values. You need to either: 1) Make the form available to logged-in users only (so no mapping is required); 2) Change the form to NOT automatically create order records (not usually recommended unless you have some custom code to make the form do something else) or 3) Map one or more fields to a client value (on the processing tab of the field properties pane).");
LANG_ADD;
    edit_language_item("orderforms", $text_to_add);
    $text_to_add = array();

    //Clients
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.0.9
define("NBILL_CLIENT_USERNAME_ALPHANUM", "Please use letters and numbers only for the username");
LANG_ADD;
    edit_language_item("clients", $text_to_add);
    $text_to_add = array();

    //Suppliers
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.0.9
define("NBILL_SUPPLIER_USERNAME_ALPHANUM", "Please use letters and numbers only for the username");
LANG_ADD;
    edit_language_item("suppliers", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}