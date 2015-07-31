<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_3_3_to_2_3_4()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    //Populate missing email template for quote request confirmation emails
    $sql[] = "UPDATE #__nbill_vendor SET qrc_email_template_name = 'quote_request_email_default' WHERE qrc_email_template_name = ''";

    //Page breaks on PDFs
    $sql[] = "ALTER TABLE `#__nbill_document_items` ADD `page_break` TINYINT NOT NULL DEFAULT '0'";

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
    //Invoices
    $text_to_add['en-GB'] = <<<LANG_ADD
define("NBILL_DOC_PAGE_BREAK", "Page Break");
define("NBILL_DOC_PAGE_BREAK_HELP", "Check this box to attempt to insert a page break between this item and the next one, if supported by your template (NOTE: it is not possible to force a browser to insert a page break, but most modern browsers will respect this setting, as will the PDF generator).");
LANG_ADD;
    edit_language_item("invoices", $text_to_add);
    $text_to_add = array();

    //Quotes
    $text_to_add['en-GB'] = <<<LANG_ADD
define("NBILL_QUOTE_SECTION_ACCEPTED", "Accepted %s");
LANG_ADD;
    edit_language_item("template.qu", $text_to_add);
    $text_to_add = array();

    //Invoice/Credit/Quote templates
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.0
define("NBILL_PRT_PRINT", "Print");
define("NBILL_PRT_PRINT_PAGE", "Print this page");
define("NBILL_PRT_SUBTOTAL", "%s Sub-Total");
LANG_ADD;
    edit_language_item("template.common", $text_to_add);
    $text_to_add = array();

    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.0
define("NBILL_PAYMENT_SENT", "The following payment(s) have been sent:");
define("NBILL_FULL_PAYMENT_SENT", "Paid in Full.");
define("NBILL_CR_PAYMENT_DATE", "Payment Date");
define("NBILL_CR_PAYMENT_METHOD", "Method");
define("NBILL_CR_PAYMENT_AMOUNT" , "Payment Amount");
define("NBILL_CR_PAYMENT_REFERENCE", "Our Reference");
define("NBILL_CR_TOTAL_PAID", "Total Amount Paid");
define("NBILL_CR_TOTAL_DUE", "Amount Outstanding:");
define("NBILL_CR_REFERENCE_UNKNOWN", "Not Yet Assigned");
LANG_ADD;
    edit_language_item("template.cr", $text_to_add);
    $text_to_add = array();

    //nBill (try to prevent triggering mod_security)
    $text_to_replace['en-GB'] = '&lt;&lt;';
    $replace_with['en-GB'] = '&laquo;';
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();
    $text_to_replace['en-GB'] = '&gt;&gt;';
    $replace_with['en-GB'] = '&raquo;';
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = '<<';
    $replace_with['en-GB'] = '&laquo;';
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();
    $text_to_replace['en-GB'] = '>>';
    $replace_with['en-GB'] = '&raquo;';
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = '< ';
    $replace_with['en-GB'] = '&lsaquo; ';
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();
    $text_to_replace['en-GB'] = ' >';
    $replace_with['en-GB'] = '&rsaquo;';
    edit_language_item("nbill", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();
##### LANGUAGE UPDATE END #####
}