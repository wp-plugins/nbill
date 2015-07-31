<?php
/*
You can create your own invoice template based on this one, but if you want to
modify this one directly, it is recommended to backup this file first (you
should also keep a record of any changes you make and backup your customised
file, as your changes could be lost when you upgrade). It is best to add your
own template to another folder inside the components/com_nbill/templates folder,
and update the vendor record with your own template's folder name. Here are
step-by-step instructions on how to do that:

    1. Create a new folder in /components/com_nbill/templates named
        invoice_customised.
    2. Copy all the files from the
        /components/com_nbill/templates/invoice_default folder into your new
        invoice_customised folder.
    3. Rename the file default_template_line_item.php to
        customised_template_line_item.php.
    4. Open the file customised_template_line_item.php using a text editor such
        as notepad, and change the class name from 'nBillDefaultTemplateLineItem'
        to 'nBillCustomisedTemplateLineItem' (the name of the class must
        correspond with the name of the file so that it can be autoloaded).
    5. Open the file index.php (in your customised folder), and change
        'nBillDefaultTemplateLineItem' to 'nBillCustomisedTemplateLineItem' (it
        should only appear once in that file, about half way down).
    6. Log into nBill administrator, go to the vendor record, edit the vendor,
        click on the 'advanced' tab, and change the invoice template to
        'invoice_customised', then click save.

There are various variables and objects available to you, and these are
described below. You can use standard PHP code and HTML to define how the data
is displayed (bear in mind that this file is included once for each invoice).

CSS class definitions can be stored in a separate file called 'template.css' in
the same folder as this index.php file.  A link to this CSS file (if it exists)
will automatically be added by the component. However, for maximum compatability
between PDF and HTML versions, and readability via online document readers, it
is recommended to define inline styles at the start of the template.

Do not try to link to template.css or any other style sheet from this template
file as this would result in invalid HTML (the <head> section has already been
defined by the time we get to this file), and the PDF generator (if you are
using it) will probably not read the stylesheet.

Variables available:
Some of the variables are arrays indexed by document id - this is because the
data for all documents to be printed is already stored in these variables before
this template file is invoked to provide the layout for each document. You can
just use $document->id as the index for these arrays.

$pdf (boolean) Whether or not the output will be rendered as a PDF document (use this to suppress display of any elements that don't get rendered properly in PDF format - eg. shading and borders together can sometimes cause problems)
$template_path (string) Fully qualified path to this template
$logo_file (string) Fully qualified image file name for the logo associated with this vendor (use this to check whether a logo file exists)
$logo_src (string) HTTP src reference for the vendor logo (use this to actually include the image)
$currency_symbol (array) Array of currency symbols, indexed by document id
$date_format (string) Date format string (eg. "d/m/Y")
$document (object) Contains global information about the invoice, quote, or credit note:
        ->id (int) Unique document identifier
        ->vendor_id (int) Unique identifier for vendor (you can ignore this)
        ->client_id (int) Unique identifier for client (you can ignore this)
        ->document_no (string) The invoice, quote, or credit note number
        ->vendor_name (string) Name of the vendor
        ->vendor_address (string) Address of vendor (bear in mind that this might contain line breaks ("\n") which need converting to "<br />")
        ->billing_name (string) Name of person or company to whom document is made out
        ->billing_address (string) Billing address (bear in mind that this might contain line breaks ("\n") which need converting to "<br />")
        ->billing_country (string) 2 character country code
        ->billing_country_desc (string) Full country name (NOTE: This may appear in upper case - use nbf_common::nb_ucwords(nbf_common::nb_strtolower($document->billing_country_desc)) to convert to title case)
        ->reference (string) Your own reference
        ->document_date (int) Date expressed as number of seconds since UNIX epoch (1st Jan 1970) - use nbf_common::nb_date() function in conjunction with $date_format variable to return a readable date
        ->tax_desc (string) Description of Vendor's tax code (eg. "VAT Number")
        ->tax_no (string) Vendor's VAT number or equivalent
        ->tax_exemption_code (string) Client's VAT number or reseller certification number
        ->currency (string) 3 character currency code (eg. USD or GBP)
        ->total_net (decimal) Net total of document
        ->total_tax (decimal) Total tax for document
        ->total_shipping (decimal) Total shipping for document
        ->total_shipping_tax (decimal) Total tax for shipping
        ->total_gross (decimal) Gross total
        ->small_print (string) Legal information
        ->status (string) Quote status code (look up value on cross reference table) - not applicable to invoices or credit notes
        ->notes (string) Your own additional notes - NB. In most cases, you will not want this displayed on the invoice!
        ->document_type (string) 'IN' = Invoice; 'CR' = Credit Note; 'QU' = Quote;
        ->quote_status_desc (string) Quote status description (eg. Accepted, Rejected)
$document_items (array) Array of objects containing information about the individual items that make up the invoice, quote, or credit note
        ->id (int) Unique ITEM identifier
        ->document_id (int) Unique identifier (NB. ALWAYS check that this matches the value in $document->id otherwise you might end up writing items from other documents onto the current one!)
        ->vendor_id (int) Unique identifier for vendor (you can ignore this)
        ->client_id (int) Unique identifier for client (you can ignore this)
        ->nominal_ledger_code (string) This would not normally be included on the document, so ignore it
        ->product_description (string) Item description (max 255 characters)
        ->detailed_description (string) HTML description (unlimited size)
        ->net_price_per_unit (decimal) Cost per unit of the given item
        ->no_of_units (decimal) Quantity (note, this can be a decimal fraction)
        ->discount_amount (decimal) Amount of any discount applied to this item
        ->discount_description (string) Description of discount (ie. what it's for)
        ->net_price_for_item (decimal) Net price after multiplying unit price by quantity and subtracting any discount
        ->tax_for_item (decimal) Amount of tax
        ->shipping_id (int) Unique identifier for shipping service (you can ignore this)
        ->shipping_for_item (decimal) Cost of shipping for this item
        ->tax_for_shipping (decimal) Amount of tax applied to shipping amount
        ->gross_price_for_item (decimal) Total price for this item, including tax and shipping
        ->section_name (string) name of a section which groups this item with all previous items since the last section break
        ->section_discount_percent (decimal) Percentage discount to apply to items within this section
        ->section_discount_title (string) Description of discount

nBill v2.x only: The following "$hide_" variables allow you to determine whether it is possible to suppress the display of certain fields to save space (eg. if there is only one unit mentioned on the document, there is no need to display the unit price and quantity). However, you do not have to hide these fields if you don't want to. All of these "$hide_" variables are arrays indexed by document id.
$hide_unit_price
$hide_quantity
$hide_discount
$hide_net_price
$hide_tax
$hide_shipping
$hide_shipping_tax

Custom line item output

As of version 3 of nBill, the actual line items are output using the built-in
document rendering engine. You can override the behaviour of that to change the
way line items are output (for example to hide certain columns, change the order
of columns, or change the format of the data). As an example of how to do this,
here are instructions on how you could hide the net and gross total columns and
swap round the order of the quantity and unit price columns. Naturally you can
adapt this to make other changes.

First, create a new invoice template, as described in the numbered list at the
top of this page.

Now you can override any of the output from
/administrator/components/com_nbill/classes/document/line_item/view/line_item_html.php
by copying the function you want to change from that file and pasting it into
the class definition of your customised_template_line_item.php file - before you
start making changes, check to make sure that invoices are still showing up
correctly with no error messages.

For example, to hide the net total and gross total columns, you can override the
canHideColumn function, so that your customised_template_line_item.php file
looks like this:

<?php
class nBillCustomisedTemplateLineItem extends nBillLineItemHtml
{
    protected function canHideColumn($column)
    {
        switch ($column) {
            case 'net_price_for_item':
            case 'gross_price_for_item':
                return true;
        }
        return parent::canHideColumn($column);
    }
}

The column names you can specify here are net_price_per_unit, discount_amount,
net_price_for_item, tax_for_item, shipping_for_item, tax_for_shipping, and
gross_price_for_item.

To swap round the quantity and unit price columns (so the quantity is shown
first), we need to copy the renderHeadingColumns and renderColumnValues functions from
/administrator/components/com_nbill/classes/document/line_item/view/line_item_html.php
to our customised_template_line_item.php file, and simply swap the lines around
so that the quantity is output first. Our customised_template_line_item.php file
now looks like this:

<?php
class nBillCustomisedTemplateLineItem extends nBillLineItemHtml
{
    protected function canHideColumn($column)
    {
        switch ($column) {
            case 'net_price_for_item':
            case 'gross_price_for_item':
                return true;
        }
        return parent::canHideColumn($column);
    }

    protected function renderHeadingColumns($styles)
    {
        list($style_th_left, $style_th) = $styles;
        $this->renderColumnHeading('product_description', 'NBILL_PRT_DESC', 'main_headings', 'style="' . $style_th_left . '"');
        $this->renderColumnHeading('no_of_units', 'NBILL_PRT_QUANTITY', 'main_headings', 'style="' . $style_th . '"');
        $this->renderColumnHeading('net_price_per_unit', 'NBILL_PRT_UNIT_PRICE', 'main_headings', 'style="' . $style_th . '"');
        $this->renderColumnHeading('discount_amount', 'NBILL_PRT_DISCOUNT', 'main_headings', 'style="' . $style_th . '"');
        $this->renderColumnHeading('net_price_for_item', 'NBILL_PRT_NET_PRICE', 'main_headings', 'style="' . $style_th . '"');
        $this->renderColumnHeading('tax_for_item', strlen(@$this->document->tax_abbreviation) == 0 ? 'NBILL_PRT_VAT' : $this->document->tax_abbreviation, 'main_headings', 'style="' . $style_th . '"');
        $this->renderColumnHeading('shipping_for_item', 'NBILL_PRT_SHIPPING', 'main_headings', 'style="' . $style_th . '"');
        $this->renderColumnHeading('tax_for_shipping', sprintf($this->translator->parseTranslation($this->document->language, 'NBILL_PRT_SHIPPING_VAT', "template.common"), strlen(@$this->document->tax_abbreviation) > 0 ? $this->document->tax_abbreviation : $this->translator->parseTranslation($this->document->language, 'NBILL_PRT_VAT', "template.common")), 'main_headings', 'style="' . $style_th . '"');
        $this->renderColumnHeading('gross_price_for_item', 'NBILL_PRT_TOTAL', 'main_headings', 'style="' . $style_th . '"');
    }

    protected function renderColumnValues(nBillLineItem $line_item, $styles)
    {
        list($style_td_left, $style_td_numeric) = $styles;
        $description = $this->getFullDescription($line_item);
        $this->renderColumnValue('product_description', $description, '', 'style="' . $style_td_left . '"');
        $this->renderColumnValue('no_of_units', $line_item->no_of_units, '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('net_price_per_unit', $line_item->net_price_per_unit, '', 'style="' . $style_td_numeric . '"');
        $discount = $line_item->discount_amount->format();
        if (strlen($line_item->discount_description) > 0) {
            $discount .= ' (' . $line_item->discount_description . ')';
        }
        $this->renderColumnValue('discount_amount', $discount, '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('net_price_for_item', $line_item->net_price_for_item, '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('tax_for_item', $line_item->tax_for_item, '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('shipping_for_item', $line_item->shipping_for_item, '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('tax_for_shipping', $line_item->tax_for_shipping, '', 'style="' . $style_td_numeric . '"');
        $this->renderGrossPriceColumnValue($line_item, $styles);
    }
}

That's it. Of course in this particular case, we could delete a couple of lines
from each of those two functions, as we are not rendering the net or gross price
columns, but that won't make any practical difference, as the canHideColumn
function override already takes care of it.
*/

/**
* Default Invoice Template
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//We have to use inline styling for the sake of PDF generation (also helps with some webmail clients)
if ($pdf) {
    //Only sure way to render UTF-8 characters with dompdf is to use the supplied fonts (firefly and DejaVu) - those fonts will be stripped out later if not needed though
    $style_container = "font-family: firefly, DejaVu Sans, Tahoma, Verdana, Arial, Helvetica, sans-serif;font-size: 10pt;padding-left:4%;padding-right:4%;margin-bottom:30px;";
} else {
    $style_container = "font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif;font-size: 10pt;padding-left:4%;padding-right:4%;margin-bottom:30px;";
}
$style_title = "color: #$title_colour;";
$style_logo_and_vendor = "width:100%;";
$style_logo = "";
$style_vendor = "text-align:right;";
$style_vendor_name = "font-weight:bold;";
$style_billing_details = "padding-top: 20px;padding-bottom: 20px;width:100%;";
$style_billing_address = "width:100%;";
$style_document_details_td = "white-space:nowrap;text-align:right;";
$style_separator = "width:100%;border-top:1px dashed;height:1px;";
$style_payment_instructions = "padding-top:30px;";
$style_due_date = "text-align:right;white-space:nowrap;font-size:110%;color:#f00";
$style_small_print = "font-size:8pt;";
$style_zero_rated_supply = "white-space:nowrap;";

$style_th = "border:solid 1px #aaaaaa;border-left:0px;padding:5px;background-color:#$heading_bg_colour;color:#$heading_fg_colour;";
$style_th_numeric = "border:solid 1px #aaaaaa;border-left:0px;padding:5px;background-color:#$heading_bg_colour;color:#$heading_fg_colour;text-align:right;";
$style_th_left = "border:solid 1px #aaaaaa;background-color:#$heading_bg_colour;padding:5px;color:#$heading_fg_colour;";
$style_th_section_header = "border:solid 1px #aaaaaa;border-top:0px;background-color:#dedede;color:#$title_colour;text-align:left;";
$style_shaded_row = "background-color:#eeeeee;";
$style_td = "border:solid 1px #aaaaaa;border-top:0px;border-left:0px;text-align:left;";
$style_td_left = "border:solid 1px #aaaaaa;border-top:0px;text-align:left;";
$style_td_top = "border:solid 1px #aaaaaa;border-left:0px;text-align:left;";
$style_td_top_left = "border:solid 1px #aaaaaa;text-align:left;";
$style_td_numeric = "border:solid 1px #aaaaaa;border-left:0px;border-top:0px;text-align:right;white-space:nowrap;";
$style_td_numeric_left = "border:solid 1px #aaaaaa;border-top:0px;text-align:right;white-space:nowrap;";
$style_td_numeric_top = "border:solid 1px #aaaaaa;border-left:0px;text-align:right;white-space:nowrap;";
$style_td_numeric_top_left = "border:solid 1px #aaaaaa;text-align:right;white-space:nowrap;";
$style_td_center = "border:solid 1px #aaaaaa;border-left:0px;border-top:0px;text-align:center;";
$style_detailed_desc = "padding-left:10px;";
$style_subtotal_td = "border:solid 1px #aaaaaa;border-top:0px;text-align:left;font-weight: bold;color: #333333;";
$style_subtotal_td_numeric = "border:solid 1px #aaaaaa;border-left:0px;border-top:0px;text-align:right;white-space:nowrap;font-weight: bold;color: #333333;";
$style_total_td = "border:solid 1px #aaaaaa;border-top:0px;text-align:left;font-weight: bold;color: #$title_colour;";
$style_total_td_top = "border:solid 1px #aaaaaa;text-align:left;font-weight: bold;color: #$title_colour;";
$style_total_td_numeric = "border:solid 1px #aaaaaa;border-left:0px;border-top:0px;text-align:right;white-space:nowrap;font-weight: bold;color: #$title_colour;";
$style_total_td_numeric_left = "border:solid 1px #aaaaaa;border-top:0px;text-align:right;white-space:nowrap;font-weight: bold;color: #$title_colour;";
$style_total_td_numeric_top = "border:solid 1px #aaaaaa;border-left:0px;text-align:right;white-space:nowrap;font-weight: bold;color: #$title_colour;";
$style_total_td_numeric_top_left = "border:solid 1px #aaaaaa;text-align:right;white-space:nowrap;font-weight: bold;color: #$title_colour;";
$style_payment_link_container = "margin-top:30px;display:block;font-size:9pt;padding:5px;border: solid 1px #999999;background-color:#eeeeee;text-align:center;";
$style_important_text = "font-weight:bold;color:#$title_colour;";
$line_item_style_array = array($style_th, $style_th_numeric, $style_th_left, $style_th_section_header,
        $style_shaded_row, $style_td, $style_td_left, $style_td_top, $style_td_top_left, $style_td_numeric,
        $style_td_numeric_left, $style_td_numeric_top, $style_td_numeric_top_left, $style_td_center, $style_detailed_desc, $style_subtotal_td,
        $style_subtotal_td_numeric, $style_total_td, $style_total_td_top, $style_total_td_numeric, $style_total_td_numeric_left,
        $style_total_td_numeric_top, $style_total_td_numeric_top_left);
?>

<div class="document-container" style="<?php echo $style_container; ?>">
    <?php if (!$pdf && defined('NBILL_PRT_PRINT'))
    { ?>
        <script type="text/javascript">
        <!--
        document.write('<div class="print-page"><a href="javascript:void(0);" onclick="window.print();return false;"><img id="img-print" src="<?php echo $pdf ? 'file://' . nbf_cms::$interop->site_base_path : nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/medium/print.gif" alt="<?php echo NBILL_PRT_PRINT; ?>" border="0" /><?php echo NBILL_PRT_PRINT; ?></a></div>');
        -->
        </script>
    <?php }

    ob_start();
    ?>

    <table class="billing-details" style="<?php echo $style_billing_details; ?>">
        <tr>
            <td class="logo" style="<?php echo $style_logo; ?>">
                <?php if (file_exists($logo_file)) { ?>
                    <img src="<?php echo "$logo_src"; ?>" alt="Logo" /><?php
                } else {
                    echo "&nbsp;";
                }?>
            </td>
            <td class="vendor" style="<?php echo $style_vendor; ?>">
                <h1 style="<?php echo $style_title; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PRT_INVOICE_TITLE'); ?></h1>
                <div class="vendor-address">
                    <span class="vendor-name" style="<?php echo $style_vendor_name; ?>"><?php echo $document->vendor_name; ?></span><br />
                    <?php echo str_replace("\n", "<br />", $document->vendor_address); ?>
                </div>
            </td>
        </tr>
    </table>

    <table class="billing-details" style="<?php echo $style_billing_details; ?>">
        <tr>
            <td class="billing-address" style="<?php echo $style_billing_address; ?>">
                <?php echo $document->billing_name; ?><br /><?php echo str_replace("\n", "<br />", $document->billing_address);  if (nbf_common::nb_strlen($document->billing_country_desc)> 0) {echo "<br />" . nbf_common::nb_ucwords(nbf_common::nb_strtolower($document->billing_country_desc));} ?>
            </td>
            <td>
                <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="<?php echo $style_document_details_td; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.qu", 'NBILL_PRT_INVOICE_NO'); ?>&nbsp;</td>
                        <td style="<?php echo $style_document_details_td; ?>"><?php echo $document->document_no; ?></td>
                    </tr>
                    <?php if (nbf_common::nb_strlen($document->reference) > 0) { ?>
                    <tr>
                        <td style="<?php echo $style_document_details_td; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_REFERENCE'); ?>&nbsp;</td>
                        <td style="<?php echo $style_document_details_td; ?>"><?php echo $document->reference; ?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td style="<?php echo $style_document_details_td; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_DATE');?>&nbsp;</td>
                        <td style="<?php echo $style_document_details_td; ?>"><?php echo nbf_common::nb_date($date_format, $document->document_date); ?></td>
                    </tr>
                    <?php
                        $date_1971 = 34128000;
                        if ($document->due_date >= $date_1971|| (!$document->paid_in_full && nbf_frontend::get_display_option('due_date'))) {
                        $due_date = $document->due_date;
                        if ($due_date < $date_1971) {
                            $due_date = $document->document_date;
                            $due_date = strtotime("+" . nbf_frontend::get_display_option('due_date_no_of_units') . " " . nbf_frontend::get_display_option('due_date_units'), $due_date);
                        }
                        ?>
                        <tr>
                            <td style="<?php echo $style_due_date; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_DUE_DATE');?>&nbsp;</td>
                            <td style="<?php echo $style_due_date; ?>"><?php echo nbf_common::nb_date($date_format, $due_date); ?></td>
                        </tr>
                        <?php
                    } ?>
                    <?php
                    if (nbf_common::nb_strlen($document->tax_no) > 0) { ?>
                        <tr>
                            <td style="<?php echo $style_document_details_td; ?>"><?php echo $document->tax_desc; ?>:&nbsp;</td>
                            <td style="<?php echo $style_document_details_td; ?>"><?php echo $document->tax_no; ?></td>
                        </tr>
                        <?php
                    } ?>
                    <?php if (nbf_common::nb_strlen($document->tax_exemption_code) > 0) { ?>
                        <tr>
                            <td style="<?php echo $style_document_details_td; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_CLIENT_TAX_REF'); ?>&nbsp;</td>
                            <td style="<?php echo $style_document_details_td; ?>"><?php echo $document->tax_exemption_code; ?></td>
                        </tr>
                        <?php
                        if ($document->total_tax == 0 && $document->in_eu) {
                            ?>
                            <tr>
                                <td colspan="2" style="<?php echo $style_zero_rated_supply; ?>"><br /><strong><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PRT_ZERO_RATED');?></strong></td>
                            </tr>
                            <?php
                        }
                    } ?>
                </table>
            </td>
        </tr>
    </table>

    <?php
    $doc_head = ob_get_clean();
    echo $doc_head;

    $document->language = $document->default_language; //For forward compatability
    $line_item_view = new nBillDefaultTemplateLineItem($line_items[$document->id], $document);
    $translator = new nBillTranslator(nbf_cms::$interop->admin_base_path, nbf_cms::$interop->language);
    $page_break_html = '<!--NewPage--><div style="page-break-before:always;margin-top:30px;"></div>' . $doc_head;
    $line_item_view->renderDocumentSummary($translator, $line_item_style_array, $pdf, $page_break_html);
    ?>

    <table cellpadding="3" cellspacing="0" border="0" style="margin-top:10px;width:100%;">
        <tr>
            <td style="width:100%;">&nbsp;</td><?php // For dompdf ?>
            <td style="text-align:right;padding-right:0px;">
                <table cellpadding="3" cellspacing="0" border="0" style="text-align:right;">
                    <?php if ($document->total_net != $document->total_gross || nBillConfigurationService::getInstance()->getConfig()->never_hide_tax) { ?>
                    <tr>
                        <td style="<?php echo $style_td_numeric_top_left; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_NET_AMOUNT'); ?> </td>
                        <td style="<?php echo $style_td_numeric_top; ?>"><?php $number = $number_factory->createNumberCurrency($document->total_net, $currencies[$document->id]); $number->setIsGrandTotal(true); echo $number; ?></td>
                    </tr>
                    <?php } ?>
                    <?php if ($document->total_shipping > 0) { ?>
                    <tr>
                        <td style="<?php echo $style_td_numeric_left; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_SHIPPING');?> </td>
                        <td style="<?php echo $style_td_numeric; ?>"><?php $number = $number_factory->createNumberCurrency($document->total_shipping, $currencies[$document->id]); $number->setIsGrandTotal(true); echo $number; ?></td>
                    </tr>
                    <?php } ?>
                    <?php for ($i=0; $i<count($tax_rates[$document->id]); $i++)
                    {?>
                    <tr>
                        <td style="<?php echo $style_td_numeric_left; ?>"><?php echo $tax_name . " @ " . $number_factory->createNumber($tax_rates[$document->id][$i], 'tax_rate') . "%"; ?></td>
                        <td style="<?php echo $style_td_numeric; ?>"><?php $number = $number_factory->createNumberCurrency($tax_rate_amounts[$document->id][$i], $currencies[$document->id]); $number->setIsGrandTotal(true); echo $number; ?></td>
                    </tr>
                    <?php }
                    $no_summary = $document->total_net == $document->total_gross && $document->total_shipping <= 0 && count($tax_rates[$document->id]) == 0;
                     ?>
                    <tr style="<?php echo $style_shaded_row; ?>">
                        <td style="<?php echo $no_summary ? $style_total_td_numeric_top_left : $style_total_td_numeric_left; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PRT_AMOUNT_DUE'); ?> </td>
                        <td style="<?php echo $no_summary ? $style_total_td_numeric_top : $style_total_td_numeric; ?>"><?php $number = $number_factory->createNumberCurrency($document->total_gross, $currencies[$document->id]); $number->setIsGrandTotal(true); echo $number; ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <?php
    $outstanding = $number_factory->createNumberCurrency(0, $currencies[$document->id]);
    $outstanding->setIsGrandTotal(true);
    if ($document->paid_in_full || $document->partial_payment)
    {
        if (count($payment_details[$document->id]) > 0)
        {
            ?>
            <table cellpadding="3" cellspacing="0" border="0" style="margin-top:10px;width:100%;">
                <tr>
                    <td colspan="4"><strong><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PAYMENT_RECEIVED'); ?></strong></td>
                </tr>
                <tr>
                    <th class="main_headings" style="<?php echo $style_th_left; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PAYMENT_DATE'); ?></th>
                    <th class="main_headings" style="<?php echo $style_th; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PAYMENT_METHOD'); ?></th>
                    <th class="main_headings" style="<?php echo $style_th; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PAYMENT_REFERENCE'); ?></th>
                    <th class="main_headings" style="<?php echo $style_th_numeric; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PAYMENT_AMOUNT'); ?></th>
                </tr>
                <?php
                $outstanding->value = $document->total_gross;
                $row = 0;
                foreach ($payment_details[$document->id] as $payment_detail)
                {
                    $number = $number_factory->createNumberCurrency($payment_detail->gross_amount, $currencies[$document->id]);
                    $outstanding = $outstanding->subtractNumber($number);
                    $row = $row == 1 ? 0 : 1; ?>
                    <tr style="<?php echo !$pdf && $row == 1 ? $style_shaded_row : ''; ?>">
                        <td style="<?php echo $style_td_left; ?>"><?php echo nbf_common::nb_date($date_format, $payment_detail->date); ?></td>
                        <td style="<?php echo $style_td; ?>"><?php echo @constant($payment_detail->pay_method); ?></td>
                        <td style="<?php echo $style_td; ?>"><?php echo nbf_common::nb_strlen($payment_detail->transaction_no) > 0 ? $payment_detail->transaction_no : nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_REFERENCE_UNKNOWN'); ?></td>
                        <td style="<?php echo $style_td_numeric; ?>"><?php $number = $number_factory->createNumberCurrency($payment_detail->gross_amount, $currencies[$document->id]); $number->setIsGrandTotal(true); echo $number; ?></td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td colspan="3" style="<?php echo $style_total_td; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_TOTAL_PAID'); ?></td>
                    <td style="<?php echo $style_total_td_numeric; ?>"><?php $number = $number_factory->createNumberCurrency($document->total_gross, $currencies[$document->id]); $number->setIsGrandTotal(true); echo $number->subtractNumber($outstanding); ?></td>
                </tr>
            </table>
            <br />
            <table cellpadding="3" cellspacing="0" border="0" style="margin-top:10px;width:100%">
                <tr>
                    <td style="border:none;width:100%">&nbsp;</td><?php // For dompdf ?>
                    <td style="text-align:right;padding-right:0px;">
                        <table cellpadding="3" cellspacing="0" border="0" style="text-align:right;">
                            <tr style="<?php echo $style_shaded_row; ?>">
                                <td style="<?php echo $style_total_td_numeric_top_left; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_TOTAL_DUE'); ?></td>
                                <td style="<?php echo $style_total_td_numeric_top; ?>"><?php echo $outstanding->value < 0 ? $number_factory->createNumberCurrency(0, $currencies[$document->id])->format() . ' <span style="color:#ff0000">' . NBILL_PRT_OVERPAID . '</span>' : $outstanding->format(); ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <?php
        }
        else if ($document->paid_in_full)
        {
            ?><p><strong><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_FULL_PAYMENT_RECEIVED'); ?></strong></p><?php
        }
    }

    if (!$document->paid_in_full)
    {
        ?>
        <div class="payment_instructions" style="<?php echo $style_payment_instructions; ?>">
            <?php
            echo $document->payment_instructions;
            ?>
        </div>
        <?php
    }

    if (trim(strip_tags($document->small_print)))
    { ?>
        <div style="<?php echo $style_small_print; ?>">
            <br /><br />
            <div style="<?php echo $style_separator; ?>"></div>
            <br />
            <?php echo $document->small_print; ?>
        </div>
    <?php } ?>    <?php
    if ($show_paylink && !$document->paid_in_full)
    { ?>
        <!-- Payment link -->
        <div style="<?php echo $style_payment_link_container; ?>">
            <?php
            $paylink_url = nbf_cms::$interop->live_site . '/' . nbf_cms::$interop->site_page_prefix . '&action=invoices&task=pay&invoice_id=' . $document->id . nbf_cms::$interop->site_page_suffix;
            $paylink_output = false;
            if (nbf_frontend::get_display_option('paylink_qr_code')) {
                $qrcode_url = 'https://chart.googleapis.com/chart?cht=qr&chs=120x120&choe=UTF-8&chld=M|1&chl=' . urlencode($paylink_url);
                $remote = new nBillRemote($qrcode_url);
                $qr_code_data = $remote->get(10);
                if (strlen($qr_code_data) > 20) {
                    $qr_code_data = base64_encode($qr_code_data);
                    ?>
                    <table border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width:100%;text-align:left;">
                                <div class="payment-link">
                                <?php
                                echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_IF_NO_SCHEDULE'); if($pdf){ ?><span style="padding-left: 1px;"><?php } ?> <a style="<?php echo $style_important_text ?>" target="_blank" href="<?php echo htmlentities($paylink_url, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding); ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PAY_THIS_INVOICE'); ?></a><?php
                                echo nbf_common::parse_translation($document->default_language, "template.in", "NBILL_CLICK_OR_SCAN_QR_CODE"); if($pdf){ ?></span><?php } ?>
                                </div>
                                <div class="print-only" style="display:none;">
                                <?php
                                echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_SCAN_HERE');
                                ?>
                                </div>
                            </td>
                            <td style="white-space:nowrap;">
                                <img src="<?php echo $use_local_image ? 'data:image/png;base64,' . $qr_code_data : $qrcode_url; ?>" alt="<?php echo nbf_common::parse_translation($document->default_language, "template.in", "NBILL_SCAN_HERE"); ?>" />
                            </td>
                        </tr>
                    </table>
                    <?php
                    $paylink_output = true;
                }
            }
            if (!$paylink_output) {
                echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_IF_NO_SCHEDULE'); if($pdf){?><span style="padding-left: 1px;"><?php } ?> <a style="<?php echo $style_important_text ?>" target="_blank" href="<?php echo htmlentities($paylink_url, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding); ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PAY_THIS_INVOICE'); ?></a><?php if ($pdf) { ?></span><?php }
            }
            ?>
            <div style="clear:both;"></div>
        </div>
        <?php
    }

    if ($show_remittance && !$document->paid_in_full) { ?>
    <!--NewPage-->
    <div style="page-break-before:always;margin-top:30px;margin-bottom:30px;<?php echo $style_separator; ?>"></div>
    <h3 style="<?php echo $style_important_text; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_REMITTANCE_ADVICE'); ?></h3>
    <p><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_REMITTANCE_INTRO'); ?></p>
    <p style="width:100%;text-align:center;<?php echo $style_small_print; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_RECEIVED_FROM') . ": " . $document->billing_name . ", " . str_replace("\n", ", ", $document->billing_address);  if (nbf_common::nb_strlen($document->billing_country_desc)> 0) {echo ", " . nbf_common::nb_ucwords(nbf_common::nb_strtolower($document->billing_country_desc));} ?></p>
    <table cellpadding="3" cellspacing="0" border="0" width="100%">
        <tr>
            <td style="<?php echo $style_td_top_left; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PRT_INVOICE_NO'); ?></td>
            <td style="<?php echo $style_td_top; ?>"><?php echo $document->document_no; ?></td>
            <td style="<?php echo $style_td_top; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_DATE');?></td>
            <td style="<?php echo $style_td_top; ?>"><?php echo nbf_common::nb_date($date_format, $document->document_date); ?></td>
        </tr>
        <tr>
        <?php
        if (nbf_common::nb_strlen($document->reference) > 0)
        { ?>
            <td style="<?php echo $style_td_left; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_REFERENCE'); ?></td>
            <td style="<?php echo $style_td; ?>"><?php echo $document->reference; ?></td>
        <?php
        }
        else
        {?>
            <td style="<?php echo $style_td_left; ?>">&nbsp;</td>
            <td style="<?php echo $style_td; ?>">&nbsp;</td>
            <?php
        } ?>
            <td style="<?php echo $style_td; ?>"><?php echo isset($outstanding) && $outstanding->value ? nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_TOTAL_DUE') : nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_AMOUNT_DUE'); ?> </td>
            <td style="<?php echo $style_td; ?>"><?php $number = $number_factory->createNumberCurrency($document->total_gross, $currencies[$document->id]); $number->setIsGrandTotal(true); echo isset($outstanding) && $outstanding->value ? $outstanding : $number; ?></td>
        </tr>
        <tr>
            <td colspan="4" style="height:50px;<?php echo $style_td_left; ?>"><?php echo nbf_common::parse_translation($document->default_language, "nbill", 'NBILL_NOTES'); ?>:</td>
        </tr>
    </table>
    <?php } ?>
</div>