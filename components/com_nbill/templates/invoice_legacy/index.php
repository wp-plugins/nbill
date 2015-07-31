<?php
/**
* Legacy invoice template file - this template was used up until version 2.3 of nBill and is preserved here for those who want it
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');
?>

<?php if (!$pdf) { //Shading items causes problems with borders when converted to pdf ?>
<style type="text/css">
.row1
{
    background-color: #efefef;
}
</style>
<?php } ?>

<div align="center" class="main">
    <table cellpadding="10" cellspacing="0" border="0" width="90%">
        <tr>
            <td width="15%" align="left">
                <?php if (file_exists($logo_file))
                { ?>
                    <img src="<?php echo "$logo_src"; ?>" alt="Logo" /><?php
                }
                else
                {
                    echo "&nbsp;";
                }?>
            </td>
            <td align="center" valign="middle" class="vendor">
                <h2 style="margin-bottom:3px;"><?php echo $document->vendor_name; ?></h2>
                <?php echo str_replace("\n", ", ", $document->vendor_address); ?>
                <h1><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PRT_INVOICE_TITLE'); ?></h1>
            </td>
            <td width="15%">&nbsp;</td>
        </tr>
    </table>

    <table cellpadding="5" cellspacing="0" border="0" width="90%">
        <tr>
            <td width="50%"><?php echo $document->billing_name; ?><br /><?php echo str_replace("\n", "<br />", $document->billing_address); if (nbf_common::nb_strlen($document->billing_country_desc)> 0) {echo "<br />" . nbf_common::nb_ucwords(nbf_common::nb_strtolower($document->billing_country_desc));} ?></td>
            <td align="right" width="50%" style="text-align:right">
                <table cellpadding="3" cellspacing="0" border="0">
                    <tr>
                        <td class="field-title"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PRT_INVOICE_NO'); ?></td><td><?php echo $document->document_no; ?></td>
                    </tr>
                    <?php if (nbf_common::nb_strlen($document->reference) > 0) { ?>
                    <tr>
                        <td class="field-title"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_REFERENCE'); ?></td><td><?php echo $document->reference; ?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td class="field-title"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_DATE');?></td><td><?php echo nbf_common::nb_date($date_format, $document->document_date); ?></td>
                    </tr>
                    <?php if (nbf_common::nb_strlen($document->tax_desc) > 0) { ?>
                    <tr>
                        <td class="field-title"><?php echo $document->tax_desc; ?>:</td><td><?php echo $document->tax_no; ?></td>
                    </tr>
                    <?php } ?>
                    <?php if (nbf_common::nb_strlen($document->tax_exemption_code) > 0) { ?>
                    <tr>
                        <td class="field-title"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_CLIENT_TAX_REF'); ?></td><td><?php echo $document->tax_exemption_code; ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </td>
        </tr>
    </table>

    <br /><br />

    <table cellpadding="5" cellspacing="0" border="0" width="90%" class="nbill-invoice-table">
        <tr>
            <th class="field-title border left"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_DESC'); ?></th>
            <?php if (!$hide_unit_price[$document->id]) { ?><th><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_UNIT_PRICE'); ?></th><?php } ?>
            <?php if (!$hide_quantity[$document->id]) { ?><th><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_QUANTITY'); ?></th><?php } ?>
            <?php if (!$hide_discount[$document->id]) { ?><th><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_DISCOUNT'); ?></th><?php } ?>
            <?php if (!$hide_net_price[$document->id]) { ?><th><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_NET_PRICE'); ?></th><?php } ?>
            <?php if (!$hide_tax[$document->id]) { ?><th><?php if (nbf_common::nb_strlen($document->tax_abbreviation) == 0) {echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_VAT');} else {echo $document->tax_abbreviation;}; ?></th><?php } ?>
            <?php if (!$hide_shipping[$document->id]) { ?><th><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_SHIPPING'); ?></th><?php } ?>
            <?php if (!$hide_shipping_tax[$document->id]) { ?><th><?php echo sprintf(nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_SHIPPING_VAT'), nbf_common::nb_strlen($document->tax_abbreviation) > 0 ? $document->tax_abbreviation : nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_VAT')); ?></th><?php } ?>
            <th><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_TOTAL'); ?></th>
        </tr>

        <?php    $row = 0;
        foreach ($document_items as $document_item)
        {
            if ($document_item->document_id == $document->id)
            {
                $row = $row == 1 ? 0 : 1; ?>
                <tr class="<?php echo "row$row"; ?> border">
                    <td class="border left">
                    <?php
                    if (nbf_common::nb_strlen($document_item->product_description) > 0)
                    {
                        echo nbf_common::nb_strlen($document_item->detailed_description) > 0 ? "<strong>" . $document_item->product_description . "</strong><br />" . $document_item->detailed_description : $document_item->product_description;
                    }
                    else
                    {
                        echo nbf_common::nb_strlen($document_item->detailed_description) > 0 ? $document_item->detailed_description : "&nbsp;";
                    } ?>
                    </td>
                    <?php if (!$hide_unit_price[$document->id]) { ?><td align="right" class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document_item->net_price_per_unit, null, true, false); ?></td><?php } ?>
                    <?php if (!$hide_quantity[$document->id]) { ?><td align="right" class="numeric"><?php echo $document_item->no_of_units == intval($document_item->no_of_units) ? intval($document_item->no_of_units) : format_number($document_item->no_of_units); ?></td><?php } ?>
                    <?php if (!$hide_discount[$document->id]) { ?><td align="right" class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document_item->discount_amount, 2, true, false); if (nbf_common::nb_strlen($document_item->discount_description) > 0) {echo " (" . $document_item->discount_description . ")";} else {echo "&nbsp;";} ?></td><?php } ?>
                    <?php if (!$hide_net_price[$document->id]) { ?><td align="right" class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document_item->net_price_for_item, 2, true, false); ?></td><?php } ?>
                    <?php if (!$hide_tax[$document->id]) { ?><td align="right" class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document_item->tax_for_item, 2, true, false);?></td><?php } ?>
                    <?php if (!$hide_shipping[$document->id]) { ?><td align="right" class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document_item->shipping_for_item, 2, true, false); ?></td><?php } ?>
                    <?php if (!$hide_shipping_tax[$document->id]) { ?><td align="right" class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document_item->tax_for_shipping, 2, true, false); ?></td><?php } ?>
                    <td align="right" class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document_item->gross_price_for_item, 2, true, false); ?></td>
                </tr>
        <?php }
        } ?>

        <tr>
            <?php $colspan = 4;
            if ($hide_unit_price[$document->id])    {$colspan--;}
            if ($hide_quantity[$document->id])    {$colspan--;}
            if ($hide_discount[$document->id])    {$colspan--;}
            ?>
            <td class="total border bottom left" colspan="<?php echo $colspan; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_TOTAL'); ?></td>
            <?php if (!$hide_net_price[$document->id]) { ?><td align="right" class="total numeric border bottom"><?php echo $currency_symbol[$document->id] . format_number($document->total_net, 2, true, false);?></td><?php } ?>
            <?php if (!$hide_tax[$document->id]) { ?><td align="right" class="total numeric border bottom"><?php echo $currency_symbol[$document->id] . format_number($document->total_tax, 2, true, false); ?></td><?php } ?>
            <?php if (!$hide_shipping[$document->id]) { ?><td align="right" class="total numeric border bottom"><?php echo $currency_symbol[$document->id] . format_number($document->total_shipping, 2, true, false); ?></td><?php } ?>
            <?php if (!$hide_shipping_tax[$document->id]) { ?><td align="right" class="total numeric border bottom"><?php echo $currency_symbol[$document->id] . format_number($document->total_shipping_tax, 2, true, false); ?></td><?php } ?>
            <td align="right" class="total numeric border bottom"><?php echo $currency_symbol[$document->id] . format_number($document->total_gross, 2, true, false); ?></td>
        </tr>
    </table>
    <br />
    <table cellpadding="0" cellspacing="0" border="0" width="90%">
        <tr>
            <td align="right">
                <table cellpadding="5" cellspacing="0" border="0" align="right" class="nbill-invoice-table">
                    <?php if ($document->total_net != $document->total_gross) { ?>
                    <tr>
                        <td class="border left"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_NET_AMOUNT'); ?> </td><td class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document->total_net, 2, true, false); ?></td>
                    </tr>
                    <?php } ?>
                    <?php if ($document->total_shipping > 0) { ?>
                    <tr>
                        <td class="border left"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_SHIPPING');?> </td><td class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document->total_shipping, 2, true, false); ?></td>
                    </tr>
                    <?php } ?>
                    <?php for ($i=0; $i<count($tax_rates[$document->id]); $i++)
                    {?>
                    <tr><td class="border left"><?php echo $tax_name . " @ " . format_number($tax_rates[$document->id][$i], 2) . "%"; ?></td><td class="numeric"><?php echo $currency_symbol[$document->id] . format_number($tax_rate_amounts[$document->id][$i], 2, true, false); ?></td></tr>
                    <?php } ?>
                    <tr class="row1">
                        <td class="grand-total border bottom left"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PRT_AMOUNT_DUE'); ?> </td><td class="grand-total numeric border bottom"><?php echo $currency_symbol[$document->id] . format_number($document->total_gross, 2, true, false); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php
        $outstanding = 0;
        if ($document->paid_in_full || $document->partial_payment)
        {
            if (count($payment_details[$document->id]) > 0)
            {
                ?>
                <tr><td><p><strong><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PAYMENT_RECEIVED'); ?></p></strong>
                <table cellpadding="3" cellspacing="0" border="0" style="width:100%" class="nbill-invoice-table">
                    <tr>
                        <th class="border left"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PAYMENT_DATE'); ?></th>
                        <th><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PAYMENT_METHOD'); ?></th>
                        <th><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PAYMENT_REFERENCE'); ?></th>
                        <th class="numeric"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PAYMENT_AMOUNT'); ?></th>
                    </tr>
                    <?php
                    $outstanding = format_number($document->total_gross, 2) * 100;
                    $row = 0;
                    foreach ($payment_details[$document->id] as $payment_detail)
                    {
                        $outstanding -= format_number($payment_detail->gross_amount, 2) * 100;
                        $row = $row == 1 ? 0 : 1; ?>
                        <tr class="<?php echo "row$row"; ?>">
                            <td class="border left"><?php echo nbf_common::nb_date($date_format, $payment_detail->date); ?></td>
                            <td><?php echo @constant($payment_detail->pay_method); ?></td>
                            <td><?php echo nbf_common::nb_strlen($payment_detail->transaction_no) > 0 ? $payment_detail->transaction_no : nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_REFERENCE_UNKNOWN'); ?></td>
                            <td class="numeric"><?php echo $currency_symbol[$document->id] . format_number($payment_detail->gross_amount, 2, true, false); ?></td>
                        </tr>
                        <?php
                    }
                    $outstanding = format_number($outstanding / 100);
                    $outstanding = $outstanding < 0 ? 0 : $outstanding;
                    ?>
                    <tr>
                        <td colspan="3" class="total border bottom left"><strong><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_TOTAL_PAID'); ?></strong></td>
                        <td class="total numeric border bottom"><strong><?php echo $currency_symbol[$document->id] . format_number(((format_number($document->total_gross) * 100) - (format_number($outstanding) * 100)) / 100, 2, true, false); ?></strong></td>
                    </tr>
                </table>
                <br />
                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td align="right" class="numeric">
                        <table cellpadding="5" cellspacing="0" border="0" align="right" class="nbill-invoice-table">
                            <tr class="row1">
                                <td class="grand-total border bottom left"><strong><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_TOTAL_DUE'); ?></strong></td>
                                <td class="grand-total numeric border bottom"><strong><?php echo $currency_symbol[$document->id] . format_number($outstanding, 2, true, false); ?></strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                </table>
                <?php
            }
            else if ($document->paid_in_full)
            {
                ?><tr><td><p><strong><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_FULL_PAYMENT_RECEIVED'); ?></strong></p></td></tr><?php
            }
        }
        if (!$document->paid_in_full)
        {
            ?>
            <tr>
                <td>
                    <br /><br /><br /><?php echo $document->payment_instructions; ?>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td class="small-print">
                <br /><br /><?php echo $document->small_print; ?>
            </td>
        </tr>
    </table>

    <br />
    <?php
    if ($show_paylink && !$document->paid_in_full) { ?>
        <!-- Payment link -->
        <div class="payment-link">
            <?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_IF_NO_SCHEDULE'); ?> <a target="_blank" href="<?php echo nbf_cms::$interop->live_site; ?>/<?php echo nbf_cms::$interop->site_page_prefix; ?>&action=invoices&task=pay&invoice_id=<?php echo $document->id . nbf_cms::$interop->site_page_suffix; ?>"><strong><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PAY_THIS_INVOICE'); ?></strong></a>
        </div>
    <?php } ?>

    <br />
    <?php if ($show_remittance && !$document->paid_in_full) { ?>
    <!-- Remittance Advice -->
    <div style="width:100%; border-top:1px dashed; height:1px;"></div><!--Firefox is incapable of handling a self-closing div!!-->
    <br />
    <h3><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_REMITTANCE_ADVICE'); ?></h3>
    <p><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_REMITTANCE_INTRO'); ?></p>
    <p class="small-print" align="center" style="width:100%;text-align:center"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_RECEIVED_FROM') . ": " . $document->billing_name . ", " . str_replace("\n", ", ", $document->billing_address);  if (nbf_common::nb_strlen($document->billing_country_desc)> 0) {echo ", " . nbf_common::nb_ucwords(nbf_common::nb_strtolower($document->billing_country_desc));} ?></p>
    <table cellpadding="3" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;">
        <tr>
            <td class="field-title remittance"><?php echo nbf_common::parse_translation($document->default_language, "template.in", 'NBILL_PRT_INVOICE_NO'); ?></td><td class="remittance"><?php echo $document->document_no; ?></td>
            <td class="field-title remittance"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_DATE');?></td><td class="remittance"><?php echo nbf_common::nb_date($date_format, $document->document_date); ?></td>
        </tr>
        <tr>
        <?php if (nbf_common::nb_strlen($document->reference) > 0) { ?>
            <td class="field-title remittance"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_REFERENCE'); ?></td><td class="remittance"><?php echo $document->reference; ?></td>
        <?php
        }
        else
        {?>
            <td class="field-title remittance">&nbsp;</td><td class="remittance">&nbsp;</td>
        <?php } ?>
            <td class="field-title remittance"><?php echo $outstanding? nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_TOTAL_DUE') : nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_AMOUNT_DUE'); ?> </td><td class="remittance"><?php echo $currency_symbol[$document->id] . format_number(($outstanding ? $outstanding : $document->total_gross), 2, true, false); ?></td>
        </tr>
        <tr>
            <td colspan="4" class="field-title remittance" style="height: 50px;"><?php echo nbf_common::parse_translation($document->default_language, "nbill", 'NBILL_NOTES'); ?>:</td>
        </tr>
    </table>
    </div>
    <?php } ?>
</div>