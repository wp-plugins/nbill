<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function upgrade_2_3_2_to_2_3_3()
{
    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;
    $text_to_replace = array();
    $replace_with = array();
    $text_to_add = array();
    $sql = array();

    //Re-order line items
    $sql[] = "ALTER TABLE `#__nbill_document_items` ADD `ordering` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `document_id`";

    //Section breaks
    $sql[] = "ALTER TABLE `#__nbill_document_items` ADD `section_name` VARCHAR( 255 ) NOT NULL DEFAULT '',
                ADD `section_discount_title` VARCHAR( 255 ) NOT NULL DEFAULT '',
                ADD `section_discount_percent` DECIMAL( 20, 6 ) NOT NULL DEFAULT '0.00',
                ADD `section_quote_atomic` TINYINT( 4 ) NOT NULL DEFAULT '0'";

    //Discounts on documents
    $sql[] = "ALTER TABLE `#__nbill_discounts` ADD `available_for_documents` TINYINT NOT NULL DEFAULT '0' AFTER `available` ";
    $sql[] = "ALTER TABLE `#__nbill_gateway_tx` ADD `document_voucher_code` VARCHAR( 100 ) NOT NULL DEFAULT ''";
    $sql[] = "ALTER TABLE `#__nbill_gateway_tx` ADD `user_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `shipping_tax_amount`";

    //Ledger codes on discounts/fees
    $sql[] = "ALTER TABLE `#__nbill_discounts` ADD `nominal_ledger_code` VARCHAR( 20 ) NOT NULL DEFAULT '' AFTER `voucher`";

    //Populate missing email template for quote request confirmation emails
    $sql[] = "UPDATE #__nbill_vendor SET qrc_email_template_name = 'quote_request_email_default' WHERE qrc_email_template_name = ''";

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
    //Discounts
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.0
define("NBILL_DISCOUNT_AVAILABLE_DOCS", "Available for Quotes/Invoices?");
define("NBILL_INSTR_DISCOUNT_AVAILABLE_DOCS", "Whether or not to allow this discount to be applied to a quote or invoice at the time of payment.");
define("NBILL_DISCOUNT_AVAILABLE_DOCS_YES", "Available for invoices/quotes");
define("NBILL_DISCOUNT_AVAILABLE_DOCS_NO", "Not available for invoices/quotes");
define("NBILL_DISCOUNT_LEDGER_CODE", "Nominal Ledger Code");
define("NBILL_DISCOUNT_LEDGER_AUTO", "Auto-Select");
define("NBILL_INSTR_DISCOUNT_LEDGER_CODE", "You can optionally specify a ledger code to which amounts associated with this discount should be applied. If this is set to '" . NBILL_DISCOUNT_LEDGER_AUTO . "', " . NBILL_BRANDING_NAME . " will attempt to apply the amount to the ledger code associated with the item or items being paid for (in the case of invoice/quote discounts, the discount amount will be amalgamated into a single entry - so if there is more than one ledger code involved, one of the codes will be picked arbitrarily. As such, it might be best to set a specific ledger code for this discount if it is an invoice/quote discount and your invoices or quotes can contain multiple ledger codes, but not for an order discount). Please be aware though that using a separate ledger code for discounts can result in ledger entries for discounted items (which do not share the same ledger code) adding up to more than 100%. If in doubt, leave it on '" . NBILL_DISCOUNT_LEDGER_AUTO . "'!");
LANG_ADD;
    edit_language_item("discounts", $text_to_add);
    $text_to_add = array();

    //E-Mail
    $text_to_replace['en-GB'] = 'define("NBILL_EM_NEW_INVOICE_PAR_1_ATTACHED", "The attached invoice has been generated for you.");';
    $replace_with['en-GB'] = 'define("NBILL_EM_NEW_INVOICE_PAR_1_ATTACHED", "The attached invoice has been generated for you. If your reader can\'t open the attachment, please save the file and open it from your computer.");';
    edit_language_item("frontend", $replace_with, $text_to_replace);
    $text_to_replace['en-GB'] = 'define("NBILL_EM_NEW_CREDIT_PAR_1_ATTACHED", "The attached credit note has been generated for you.");';
    $replace_with['en-GB'] = 'define("NBILL_EM_NEW_CREDIT_PAR_1_ATTACHED", "The attached credit note has been generated for you. If your reader can\'t open the attachment, please save the file and open it from your computer.");';
    edit_language_item("frontend", $replace_with, $text_to_replace);
    $text_to_replace['en-GB'] = 'define("NBILL_EM_NEW_QUOTE_PAR_1_ATTACHED", "Thank you for requesting a quotation. Please find attached our quotation in accordance with your stated requirements.';
    $replace_with['en-GB'] = 'define("NBILL_EM_NEW_QUOTE_PAR_1_ATTACHED", "Thank you for requesting a quotation. Please find attached our quotation in accordance with your stated requirements. If your reader can\'t open the attachment, please save the file and open it from your computer.';
    edit_language_item("email", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Expenditure
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.0
define("NBILL_EXPENDITURE_WARNING_DEFAULT_LEDGER", "WARNING! You are allocating this expenditure to the default (-1 - Miscellaneous) nominal ledger. Are you sure?");
LANG_ADD;
    edit_language_item("expenditure", $text_to_add);
    $text_to_add = array();

    //Fees
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.0
define("NBILL_FEE_LEDGER_CODE", "Nominal Ledger Code");
define("NBILL_FEE_LEDGER_AUTO", "Auto-Select");
define("NBILL_INSTR_FEE_LEDGER_CODE", "You can optionally specify a ledger code to which amounts associated with this fee should be applied. If this is set to '" . NBILL_FEE_LEDGER_AUTO . "', " . NBILL_BRANDING_NAME . " will attempt to apply the amount to the ledger code associated with the item or items being paid for (in the case of payment gateway fees, the fee amount will be amalgamated into a single entry - so if there is more than one ledger code involved, one of the codes will be picked arbitrarily. As such, it might be best to set a specific ledger code for this fee if it is being used as a payment gateway fee).");
LANG_ADD;
    edit_language_item("fees", $text_to_add);
    $text_to_add = array();

    //Front end
    $text_to_replace['en-GB'] = 'define("NBILL_QUOTE_AWAITING_ACTION", "Quote %s: <a href=\"%s\">Pay Now</a> | <a href=\"%s\">Reject Quote</a> | <a href=\"%s\">View Quote Details</a>");';
    $replace_with['en-GB'] = 'define("NBILL_QUOTE_AWAITING_ACTION", "Quote %s: <a href=\"%s\">Pay Now</a> | <a href=\"%s\">Reject Remaining Items</a> | <a href=\"%s\">View Quote Details</a>");';
    edit_language_item("frontend", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.0
define("NBILL_QUOTE_REJECT_OS_WARNING", "Are you sure you want to REJECT the remaining items on this quote?");
define("NBILL_FE_DOC_DISCOUNT_VOUCHER", "Promotional Code (if applicable)");
define("NBILL_FE_DOC_DISCOUNT_APPLY", "Apply");
LANG_ADD;
    edit_language_item("frontend", $text_to_add);
    $text_to_add = array();

    //Gateway
    $text_to_replace['en-GB'] = 'If you want to use this feature then, make sure it is legal in your country to change an invoice amount even after the customer has received the invoice (if applicable).';
    $replace_with['en-GB'] = 'If you want to use this feature then, make sure it is legal in your country to change an invoice amount even after the customer has received the invoice (if applicable). Please also note that while this feature has been tested fairly thoroughly, it should still be regarded as experimental. Due to rounding differences in the way amounts are calculated, and the way partial payments are dealt with, it might not always yield the desired or expected results.';
    edit_language_item("gateway", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Income
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.0
define("NBILL_INCOME_WARNING_DEFAULT_LEDGER", "WARNING! You are allocating this income to the default (-1 - Miscellaneous) nominal ledger. Are you sure?");
LANG_ADD;
    edit_language_item("income", $text_to_add);
    $text_to_add = array();

    //Invoices
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.0
define("NBILL_DOC_SECTION_ADD", "Insert Section Break");
define("NBILL_DOC_SECTION_ADD_HELP", "Inserting a section break will group the above items together and show a sub-total for the section. You can also optionally apply a percentage discount to the section.");
define("NBILL_DOC_SECTION_DELETE", "Delete Section Break");
define("NBILL_DOC_SECTION_NAME", "Section Name: ");
define("NBILL_DOC_SECTION_DISCOUNT_TITLE", "Section Discount Title: ");
define("NBILL_DOC_SECTION_DISCOUNT_PC", "Section Discount %: ");
define("NBILL_DOC_SECTION_SUBTOTAL", "Sub-Total: ");
define("NBILL_DOC_SECTION_ACCEPTED_SUBTOTAL", "Accepted: ");
define("NBILL_DOC_DISCOUNT", "Discount");
define("NBILL_INSTR_DOC_DISCOUNT", "Discount to apply to the whole invoice (Note: the discount will only be applied if the discount rules are met - if the discount specified here has a voucher code, the customer will be prompted to enter it when paying the invoice online).");
define("NBILL_INVOICE_SKU_LOOKUP_ORDERING", "Order By %s");
define("NBILL_INVOICE_SKU_LOOKUP_ORDERING_SKU", "SKU");
define("NBILL_INVOICE_SKU_LOOKUP_ORDERING_NAME", "Name");
define("NBILL_DOC_SAVE_ADDED_PRODUCT", "You have added one or more new products on this document - would you like to save the added item(s) to your product list?");
define("NBILL_DOC_SAVE_UPDATED_PRODUCT", "You have updated one or more products on this document - would you like to save your changes to the product record? (WARNING! This will NOT affect any existing orders for the affected product(s).)");
LANG_ADD;
    edit_language_item("invoices", $text_to_add);
    $text_to_add = array();

    //nBill
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.0
define("NBILL_SECTION_DISCOUNT", "Section Discount");
define("NBILL_SECTION_OTHER", "Other");
LANG_ADD;
    edit_language_item("nbill", $text_to_add);
    $text_to_add = array();

    //Products
    $text_to_replace['en-GB'] = 'define("NBILL_DOWNLOAD_LOCATION_3", "Download Location 10';
    $replace_with['en-GB'] = 'define("NBILL_DOWNLOAD_LOCATION_3", "Download Location 3';
    edit_language_item("products", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    $text_to_replace['en-GB'] = '10rd';
    $replace_with['en-GB'] = '3rd';
    edit_language_item("products", $replace_with, $text_to_replace);
    $text_to_replace = array();
    $replace_with = array();

    //Quotes
    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.0
define("NBILL_DOC_SECTION_QUOTE_ATOMIC", "Atomic? ");
define("NBILL_DOC_SECTION_QUOTE_ATOMIC_HELP", "Whether or not ALL items in the section must be accepted or rejected as a whole");
define("NBILL_QUOTE_TOTAL_ACCEPTED", "Accepted Total");
define("NBILL_QUOTE_ACCEPTED_TOTAL_DISCOUNTED", " (approx)");
LANG_ADD;
    edit_language_item("quotes", $text_to_add);
    $text_to_add = array();

    $text_to_add['en-GB'] = <<<LANG_ADD

//Version 2.4.0
define("NBILL_QUOTE_ITEM_ACCEPTED_PARTIAL", "Partially Accepted");
define("NBILL_QUOTE_ACCEPTED_TOTAL", "Accepted Total");
LANG_ADD;
    edit_language_item("template.qu", $text_to_add);
    $text_to_add = array();
##### LANGUAGE UPDATE END #####
}