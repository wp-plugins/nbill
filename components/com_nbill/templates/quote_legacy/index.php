<?php
/**
* Legacy quote template file - this template was used up until version 2.3 of nBill and is preserved here for those who want it
* @version 1
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
                <h1><?php echo nbf_common::parse_translation($document->default_language, "template.qu", 'NBILL_PRT_QUOTE_TITLE'); ?></h1>
            </td>
            <td width="15%">&nbsp;</td>
        </tr>
    </table>

    <table cellpadding="5" cellspacing="0" border="0" width="90%">
        <tr>
            <td width="50%"><?php echo $document->billing_name; ?><br /><?php echo str_replace("\n", "<br />", $document->billing_address);  if (nbf_common::nb_strlen($document->billing_country_desc)> 0) {echo "<br />" . nbf_common::nb_ucwords(nbf_common::nb_strtolower($document->billing_country_desc));} ?></td>
            <td align="right" width="50%" style="text-align:right">
                <table cellpadding="3" cellspacing="0" border="0">
                    <tr>
                        <td class="field-title"><?php echo nbf_common::parse_translation($document->default_language, "template.qu", 'NBILL_PRT_QUOTE_NO'); ?></td><td><?php echo $document->document_no; ?></td>
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

    <?php if (nbf_common::nb_strlen($document->quote_intro) > 0)
    {
        ?>
        <br />
        <table cellpadding="5" cellspacing="0" border="0" width="90%">
        <tr><td><?php echo $document->quote_intro; ?></td></tr>
        </table><?php
    }
    else
    { ?>
        <br /><?php
    } ?>

    <br />

    <table cellpadding="5" cellspacing="0" border="0" width="90%" class="nbill-invoice-table">
        <tr>
            <th class="border left"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_DESC'); ?></th>
            <?php if (!$hide_unit_price[$document->id]) { ?><th><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_UNIT_PRICE'); ?></th><?php } ?>
            <?php if (!$hide_quantity[$document->id]) { ?><th><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_QUANTITY'); ?></th><?php } ?>
            <?php if (!$hide_discount[$document->id]) { ?><th><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_DISCOUNT'); ?></th><?php } ?>
            <?php if (!$hide_net_price[$document->id]) { ?><th><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_NET_PRICE'); ?></th><?php } ?>
            <?php if (!$hide_tax[$document->id]) { ?><th><?php if (nbf_common::nb_strlen($document->tax_abbreviation) == 0) {echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_VAT');} else {echo $document->tax_abbreviation;}; ?></th><?php } ?>
            <?php if (!$hide_shipping[$document->id]) { ?><th><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_SHIPPING'); ?></th><?php } ?>
            <?php if (!$hide_shipping_tax[$document->id]) { ?><th><?php echo sprintf(nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_SHIPPING_VAT'), nbf_common::nb_strlen($document->tax_abbreviation) > 0 ? $document->tax_abbreviation : nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_VAT')); ?></th><?php } ?>
            <th><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_TOTAL'); ?></th>
            <?php if ($document->status == 'EE') { ?><th><?php echo nbf_common::parse_translation($document->default_language, "template.qu", 'NBILL_QUOTE_ITEM_ACCEPTED_TITLE'); ?></th><?php } ?>
        </tr>

        <?php
        $row = 0;
        $summary_net = array();
        $summary_tax = array();
        $summary_shipping = array();
        $summary_shipping_tax = array();
        $summary_gross = array();
        $recurring_present = false;
        $one_off_present = false;
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
                    <?php if (!$hide_discount[$document->id]) { ?><td align="right" class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document_item->discount_amount, null, true, false); if (nbf_common::nb_strlen($document_item->discount_description) > 0) {echo " (" . $document_item->discount_description . ")";} else {echo "&nbsp;";} ?></td><?php } ?>
                    <?php if (!$hide_net_price[$document->id]) { ?><td align="right" class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document_item->net_price_for_item, null, true, false); ?></td><?php } ?>
                    <?php if (!$hide_tax[$document->id]) { ?><td align="right" class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document_item->tax_for_item, null, true, false);?></td><?php } ?>
                    <?php if (!$hide_shipping[$document->id]) { ?><td align="right" class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document_item->shipping_for_item, null, true, false); ?></td><?php } ?>
                    <?php if (!$hide_shipping_tax[$document->id]) { ?><td align="right" class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document_item->tax_for_shipping, null, true, false); ?></td><?php } ?>
                    <td align="right" class="numeric"><?php echo $currency_symbol[$document->id] . format_number($document_item->gross_price_for_item, null, true, false);
                    $summary_freq_code = defined("NBILL_PER_" . nbf_common::nb_strtoupper($document_item->quote_pay_freq)) ? nbf_common::nb_strtoupper($document_item->quote_pay_freq) : 'AA';
                    if (!isset($summary_net[$summary_freq_code])) {$summary_net[$summary_freq_code] = 0;}
                    if (!isset($summary_tax[$summary_freq_code])) {$summary_tax[$summary_freq_code] = 0;}
                    if (!isset($summary_shipping[$summary_freq_code])) {$summary_shipping[$summary_freq_code] = 0;}
                    if (!isset($summary_shipping_tax[$summary_freq_code])) {$summary_shipping_tax[$summary_freq_code] = 0;}
                    if (!isset($summary_gross[$summary_freq_code])) {$summary_gross[$summary_freq_code] = 0;}
                    $summary_net[$summary_freq_code] += $document_item->net_price_for_item;
                    $summary_tax[$summary_freq_code] += $document_item->tax_for_item;
                    $summary_shipping[$summary_freq_code] += $document_item->shipping_for_item;
                    $summary_shipping_tax[$summary_freq_code] += $document_item->tax_for_shipping;
                    $summary_gross[$summary_freq_code] += $document_item->gross_price_for_item;
                    if ($summary_freq_code == 'AA')
                    {
                        $one_off_present = true;
                    }
                    else
                    {
                        $recurring_present = true;
                        ?><br /><span style="white-space:nowrap"><?php echo nbf_common::parse_translation($document->default_language, "template.qu", 'NBILL_PER_' . nbf_common::nb_strtoupper($document_item->quote_pay_freq)); ?></span><?php
                    }
                    ?></td>
                    <?php if ($document->status == 'EE') { ?><td align="center" style="text-align:center;" class="numeric"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/<?php echo $document_item->quote_item_accepted ? 'tick' : 'cross'; ?>.png" alt="<?php echo $document_item->quote_item_accepted ? nbf_common::parse_translation($document->default_language, "template.qu", 'NBILL_QUOTE_ITEM_ACCEPTED_YES') : nbf_common::parse_translation($document->default_language, "template.qu", 'NBILL_QUOTE_ITEM_ACCEPTED_NO'); ?>" /></td><?php } ?>
                </tr>
        <?php }
        }
        if ($recurring_present || $one_off_present)
        {
            $summary_index = 0;
            foreach ($summary_gross as $key=>$value)
            {
                $summary_index++;
                ?>
                <tr>
                    <?php $colspan = 4;
                    if ($hide_unit_price[$document->id]) {$colspan--;}
                    if ($hide_quantity[$document->id]) {$colspan--;}
                    if ($hide_discount[$document->id]) {$colspan--;}
                    ?>
                    <td class="total border bottom left" colspan="<?php echo $colspan; ?>"><?php echo nbf_common::parse_translation($document->default_language, "template.common", 'NBILL_PRT_TOTAL'); if ($key != 'AA') {echo nbf_common::parse_translation($document->default_language, "template.qu", 'NBILL_PER_' . nbf_common::nb_strtoupper($key));} else {if ($recurring_present) {echo nbf_common::parse_translation($document->default_language, "template.qu", 'NBILL_PRT_TOTAL_ONE_OFF');}} ?></td>
                    <?php if (!$hide_net_price[$document->id]) { ?><td align="right" class="total numeric border bottom"><?php echo $currency_symbol[$document->id] . format_number($summary_net[$key], null, true, false);?></td><?php } ?>
                    <?php if (!$hide_tax[$document->id]) { ?><td align="right" class="total numeric border bottom"><?php echo $currency_symbol[$document->id] . format_number($summary_tax[$key], null, true, false); ?></td><?php } ?>
                    <?php if (!$hide_shipping[$document->id]) { ?><td align="right" class="total numeric border bottom"><?php echo $currency_symbol[$document->id] . format_number($summary_shipping[$key], null, true, false); ?></td><?php } ?>
                    <?php if (!$hide_shipping_tax[$document->id]) { ?><td align="right" class="total numeric border bottom"><?php echo $currency_symbol[$document->id] . format_number($summary_shipping_tax[$key], null, true, false); ?></td><?php } ?>
                    <td align="right" class="total numeric border bottom"><?php echo $currency_symbol[$document->id] . format_number($value, null, true, false); ?></td>
                    <?php if ($document->status == 'EE') { ?><td class="total numeric border bottom">&nbsp;</td><?php } ?>
                </tr><?php
            }
        }
        else
        {
            ?>
            <tr>
                <td colspan="2" class="border bottom left><?php echo nbf_common::parse_translation($document->default_language, "template.qu", 'NBILL_QUOTE_NO_ITEMS'); ?></td>
            </tr>
            <?php
        }
        ?>
    </table>
    <br /><br />
    <table cellpadding="0" cellspacing="0" border="0" width="90%">
        <tr>
            <td class="small-print">
                <?php echo $document->small_print; ?>
            </td>
        </tr>
    </table>

    <?php if (nbf_common::nb_strlen(trim($document->correspondence)) > 0)
    { ?>
        <br /><br /><hr />
        <table cellpadding="0" cellspacing="0" border="0" width="90%">
            <tr>
                <td>
                    <p><h3><?php echo nbf_common::parse_translation($document->default_language, "template.qu", 'NBILL_QUOTE_CORRESPONDENCE_INTRO'); ?></h3></p>
                    <div id="correspondence">
                        <?php echo $document->correspondence; ?>
                    </div>
                </td>
            </tr>
        </table><hr />
        <?php
    } ?>
    <br /><br />
    <table cellpadding="0" cellspacing="0" border="0" width="90%">
        <tr>
            <td class="small-print">
                <strong><?php echo nbf_common::parse_translation($document->default_language, "template.qu", 'NBILL_PRT_QUOTE_STATUS'); ?>:</strong> '<?php
                 echo @constant($document->quote_status_desc); ?>'
            </td>
        </tr>
    </table>
</div>