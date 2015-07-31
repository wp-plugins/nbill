<?php
/**
* HTML output for quotes
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');
include_once("invoices.html.php");

class nbillQuote
{
    public static function offer_quote_action($id, $old_task, $task, $quote, $data)
    {
        ?>
        <table class="adminheading" style="width:auto;">
        <tr>
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "quotes"); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_QUOTES_TITLE; ?>
            </th>
        </tr>
        </table>

        <div class="nbill-message-ie-padding-bug-fixer"></div>
        <?php
        nbf_cms::$interop->init_editor();
        if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
        {
            echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
        } ?>

        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="quotes" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />
        <input type="hidden" name="task" value="<?php echo $task; ?>" />
        <input type="hidden" name="old_task" value="<?php echo $old_task; ?>" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

        <?php
        switch ($task)
        {
            case "on_hold":
            case "quoted":
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.email.class.php");
                nbf_email::show_email_form_for_document($id, NBILL_QUOTE_SAVED);
                break;
            case "accepted":
                nbf_html::load_calendar();
                ?>
                <p><?php echo NBILL_QUOTE_GENERATE_INTRO; ?></p>
                <?php
                if (isset($data[2]) && $data[2] && count($data[2]) > 0)
                {
                    $links = array();
                    foreach ($data[2] as $existing_order)
                    {
                        $links[] = "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=orders&task=edit&cid=" . $existing_order->id . "\">" . $existing_order->order_no . "</a>";
                    }
                    echo "<div class=\"nbill-message\">" . NBILL_QUOTE_GENERATE_WARNING_ORDERS;
                    foreach ($links as $link)
                    {
                        echo "&nbsp;&nbsp;" . $link;
                    }
                    echo "</div><br />";
                }
                if (isset($data[3]) && $data[3] && count($data[3]) > 0)
                {
                    $links = array();
                    foreach ($data[3] as $existing_invoice)
                    {
                        $links[] = "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=invoices&task=edit&cid=" . $existing_invoice->id . "\">" . $existing_invoice->document_no . "</a>";
                    }
                    echo "<div class=\"nbill-message\">" . NBILL_QUOTE_GENERATE_WARNING_INVOICES;
                    foreach ($links as $link)
                    {
                        echo "&nbsp;" . $link;
                    }
                    echo "</div><br />";
                }
                ?>
                <div class="rounded-table">
                <table class="adminlist">
                    <tr>
                        <th class="title responsive-cell optional"><?php echo NBILL_INVOICE_ITEM_CODE; ?></th>
                        <th class="title responsive-cell high-priority"><?php echo NBILL_INVOICE_ITEM_NAME; ?></th>
                        <th class="title responsive-cell optional"><?php echo NBILL_INVOICE_ITEM_TOTAL_NET; ?></th>
                        <th class="title responsive-cell optional"><?php echo NBILL_INVOICE_ITEM_TAX; ?></th>
                        <th class="title"><?php echo NBILL_INVOICE_ITEM_GROSS; ?></th>
                        <th class="title responsive-cell priority"><?php echo NBILL_QUOTE_PAY_FREQ; ?></th>
                        <th class="title"><?php echo NBILL_QUOTE_CREATE_ORDER; ?></th>
                        <th class="selector"><?php echo NBILL_QUOTE_CREATE_INVOICE; ?></th>
                    </tr>
                    <?php
                    if (!@$data[0] || count($data[0]) == 0)
                    {
                        ?>
                        <tr><td colspan="8"><?php echo NBILL_NO_ACCEPTED_ITEMS; ?></td></tr>
                        <?php
                    }
                    else
                    {
                        foreach ($data[0] as $quote_item)
                        {
                            $invoice_requires_order = false;
                            if ($quote_item->quote_pay_freq != 'AA' && $quote_item->quote_pay_freq != 'XX')
                            {
                                $invoice_requires_order = true;
                            }
                            ?>
                            <tr>
                                <td class="list-value responsive-cell optional"><?php echo $quote_item->product_code; ?></td>
                                <td class="list-value responsive-cell high-priority"><?php echo $quote_item->product_description; ?></td>
                                <td class="list-value responsive-cell optional"><?php echo format_number($quote_item->net_price_for_item); ?></td>
                                <td class="list-value responsive-cell optional"><?php echo format_number($quote_item->tax_for_item); ?></td>
                                <td class="list-value"><?php echo format_number($quote_item->gross_price_for_item); ?></td>
                                <td class="list-value responsive-cell priority"><?php
                                echo @constant($quote_item->quote_pay_freq_desc); ?></td>
                                <td class="title"><input type="checkbox" name="create_order_<?php echo $quote_item->id; ?>" id="create_order_<?php echo $quote_item->id; ?>"<?php if (array_search($quote_item->id, $data[1]) !== false && $invoice_requires_order) {echo " checked=\"checked\"";} if ($invoice_requires_order) {echo " onclick=\"if(this.checked){document.getElementById('create_invoice_" . $quote_item->id . "').disabled=false;}else{document.getElementById('create_invoice_" . $quote_item->id . "').checked=false;document.getElementById('create_invoice_" . $quote_item->id . "').disabled=true;}\"";} ?> />
                                    <?php echo NBILL_QUOTE_ORDER_NEXT_DUE;
                                    $date_parts = nbf_common::nb_getdate();
                                    ?>
                                    <span style="white-space:nowrap"><input type="text" name="next_due_date_<?php echo $quote_item->id; ?>" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::nb_date(nbf_common::get_date_format(), nbf_common::nb_mktime(0,0,0,$date_parts['mon'],$date_parts['mday'],$date_parts['year'])); ?>" />
                                    <input type="button" name="next_due_date_<?php echo $quote_item->id; ?>_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.next_due_date_<?php echo $quote_item->id; ?>,'<?php echo nbf_common::get_date_format(true); ?>',this);" /></span>
                                </td>
                                <td class="selector"><input type="checkbox" name="create_invoice_<?php echo $quote_item->id; ?>" id="create_invoice_<?php echo $quote_item->id; ?>"<?php if (array_search($quote_item->id, $data[1]) !== false) {echo " checked=\"checked\"";} else {if ($invoice_requires_order){echo " disabled=\"disabled\"";}} ?> /></td>
                            </tr>
                        <?php }
                    }
                    ?>
                    <tr class="nbill_tr_no_highlight">
                        <td colspan="8" align="right" style="text-align:right;">
                        <?php if (@$data[0] && count($data[0]) > 0)
                        { ?>
                            <input type="submit" name="generate_records" id="generate_records" value="<?php echo NBILL_QUOTE_GENERATE_RECORDS; ?>" />
                        <?php } ?>
                        <input type="submit" name="abort" id="abort" value="<?php echo NBILL_QUOTE_GENERATE_ABORT; ?>" /></td>
                    </tr>
                </table>
                </div>
                <?php
        } ?>
        </form>
        <?php
    }
}