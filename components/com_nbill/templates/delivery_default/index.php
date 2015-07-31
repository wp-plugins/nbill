<?php
/*
You can create your own invoice template based on this one, but if you want to
modify this one directly, it is recommended to backup this file first (you should
also keep a record of any changes you make and backup your customised file, as your
changes could be lost when you upgrade). It is best to add your own template to
another folder inside the components/com_nbill/templates folder, and update the
vendor record with your own template's folder name.

There are various variables and objects available to you, and these are described
below. You can use standard PHP code and HTML to define how the data is displayed
(bear in mind that this file is included once for each invoice).

CSS class definitions can be stored in a separate file called 'template.css'
in the same folder as this index.php file.  A link to this CSS file (if it exists)
will automatically be added by the component. However, for maximum compatability
between PDF and HTML versions, and readability via online document readers, it is
recommended to define inline styles at the start of the template.

Do not try to link to template.css or any other style sheet from this template file
as this would result in invalid HTML (the <head> section has already been defined
by the time we get to this file), and the PDF generator (if you are using it) will
probably not read the stylesheet.

Variables available:
Some of the variables are arrays indexed by document id - this is because the data
for all documents to be printed is already stored in these variables before this
template file is invoked to provide the layout for each document. You can just use
$document->id as the index for these arrays.

$pdf (boolean) Whether or not the output will be rendered as a PDF document (use this to suppress display of any elements that don't get rendered properly in PDF format - eg. shading and borders together can sometimes cause problems)
$template_path (string) Fully qualified path to this template
$logo_file (string) Fully qualified image file name for the logo associated with this vendor (use this to check whether a logo file exists)
$logo_src (string) HTTP src reference for the vendor logo (use this to actually include the image)
$currency_symbol (array) Array of currency symbols, indexed by document id
$date_format (string) Date format string (eg. "d/m/Y")
$document (object) Contains global information about the quote:
        ->id (int) Unique document identifier
        ->vendor_id (int) Unique identifier for vendor (you can ignore this)
        ->client_id (int) Unique identifier for client (you can ignore this)
        ->document_no (string) The quote number
        ->vendor_name (string) Name of the vendor
        ->vendor_address (string) Address of vendor (bear in mind that this might contain line breaks ("\n") which need converting to "<br />")
        ->billing_name (string) Name of person or company to whom quote is made out
        ->billing_address (string) Billing address (bear in mind that this might contain line breaks ("\n") which need converting to "<br />")
        ->billing_country (string) 2 character country code
        ->billing_country_desc (string) Full country name (NOTE: This may appear in upper case - use nbf_common::nb_ucwords(nbf_common::nb_strtolower($document->billing_country_desc)) to convert to title case)
        ->reference (string) Your own reference
        ->document_date (int) Quote date expressed as number of seconds since UNIX epoch (1st Jan 1970) - use nbf_common::nb_date() function in conjunction with $date_format variable to return a readable date
        ->tax_desc (string) Description of Vendor's tax code (eg. "VAT Number")
        ->tax_no (string) Vendor's VAT number or equivalent
        ->tax_exemption_code (string) Client's VAT number or reseller certification number
        ->currency (string) 3 character currency code (eg. USD or GBP)
        ->total_net (decimal) Net total of quote
        ->total_tax (decimal) Total tax for quote
        ->total_shipping (decimal) Total shipping for quote
        ->total_shipping_tax (decimal) Total tax for shipping
        ->total_gross (decimal) Gross total
        ->small_print (string) Legal information
        ->status (string) Quote status code (look up value on cross reference table)
        ->notes (string) Your own additional notes - NB. In most cases, you will not want this displayed on the invoice!
        ->document_type (string) 'IN' = Invoice; 'CR' = Credit Note; 'QU' = Quote; 'PO' = Purchase Order
        ->quote_status_desc (string) Quote status description (eg. Accepted, Rejected)
$document_items (array) Array of objects containing information about the individual items that make up the quote
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

The following "$hide_" variables allow you to determine whether it is possible
to suppress the display of certain fields to save space (eg. if there is only
one unit mentioned on the document, there is no need to display the unit price
and quantity). However, you do not have to hide these fields if you don't want to.
All of these "$hide_" variables are arrays indexed by document id.
$hide_unit_price
$hide_quantity
$hide_discount
$hide_net_price
$hide_tax
$hide_shipping
$hide_shipping_tax

The actual line items can be output using the built-in document rendering engine, and if you want to customise the output
of that, you can simply override the nBillLineItemHtml class (an example of this is provided with the default template in
the file default_template_line_item.php).
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
$style_shipping_details = "padding-top: 20px;padding-bottom: 20px;width:100%;";
$style_shipping_address = "width:100%;";
$style_document_details_td = "white-space:nowrap;text-align:right;";
$style_separator = "width:100%;border-top:1px dashed;height:1px;";
$style_payment_instructions = "padding-top:30px;";
$style_due_date = "text-align:right;white-space:nowrap;font-size:110%;color:#f00";
$style_small_print = "font-size:8pt;";

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

    <table class="billing-details" style="<?php echo $style_shipping_details; ?>">
        <tr>
            <td class="logo" style="<?php echo $style_logo; ?>">
                <?php if (file_exists($logo_file)) { ?>
                    <img src="<?php echo "$logo_src"; ?>" alt="Logo" /><?php
                } else {
                    echo "&nbsp;";
                }?>
            </td>
            <td class="vendor" style="<?php echo $style_vendor; ?>">
                <h1 style="<?php echo $style_title; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PRT_DELIVERY_NOTE_TITLE'); ?></h1>
                <div class="vendor-address">
                    <span class="vendor-name" style="<?php echo $style_vendor_name; ?>"><?php echo $document->vendor_name; ?></span><br />
                    <?php echo str_replace("\n", "<br />", $document->vendor_address); ?>
                </div>
            </td>
        </tr>
    </table>

    <table class="billing-details" style="<?php echo $style_shipping_details; ?>">
        <tr>
            <td class="billing-address" style="<?php echo $style_shipping_address; ?>">
                <?php echo $document->billing_name; ?><br />
                <?php
                if ($document->shipping_address != null) {
                    echo str_replace("\n", "<br />", $document->shipping_address->format());
                }
                ?>
            </td>
            <td>
                <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="<?php echo $style_document_details_td; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.qu", 'NBILL_PRT_RELATED_INVOICE_NO'); ?>&nbsp;</td>
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
                </table>
            </td>
        </tr>
    </table>

    <?php
    $doc_head = ob_get_clean();
    echo $doc_head;

    $document->language = $document->default_language; //For forward compatability
    $line_item_view = new nBillDeliveryTemplateLineItem($line_items[$document->id], $document);
    $translator = new nBillTranslator(nbf_cms::$interop->admin_base_path, nbf_cms::$interop->language);
    $page_break_html = '<!--NewPage--><div style="page-break-before:always;margin-top:30px;"></div>' . $doc_head;
    $line_item_view->renderDocumentSummary($translator, $line_item_style_array, $pdf, $page_break_html);

    if (trim(strip_tags($document->delivery_small_print)))
    { ?>
        <div style="<?php echo $style_small_print; ?>">
            <br /><br />
            <div style="<?php echo $style_separator; ?>"></div>
            <br />
            <?php echo $document->delivery_small_print; ?>
        </div>
    <?php } ?>

</div>