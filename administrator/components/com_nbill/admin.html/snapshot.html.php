<?php
/**
* HTML output for snapshot report
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillSnapshot
{
    public static function showSnapshotReport($vendors, $vendor_name, $currencies, $unpaid_invoices, $snapshot_date, $cfg_date_format, $csv = false)
    {
        $printer_friendly = nbf_common::get_param($_POST, 'printer_friendly');
        $tab_started = false;
        if ($printer_friendly)
        {
            $csv = false; //Can't be both!
        }

        if (!$csv)
        {
            if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
            {
                echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
            }
            nbf_html::load_calendar();
        }
        $exclude_filter = array();
        $exclude_filter[] = "snapshot_date";
        if (!$csv)
        {
            nbf_html::add_filters($exclude_filter);
        }
        if ($printer_friendly)
        {
            //Wrap whole lot in a table with cellpadding - only way to get margins to work cross-browser
            echo "<table border=\"0\" cellpadding=\"10\" cellspacing=\"0\"><tr><td>";
        }
        if (!$csv)
        {
            ?>
            <table class="adminheading" style="width:100%">
            <tr>
                <th <?php if ($printer_friendly) {echo "style=\"background-image:none !important; margin-left:0;padding-left:0;\"";} else {echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "snapshot");} ?>>
                    <?php echo NBILL_SNAPSHOT_TITLE . " " . NBILL_FOR . " $vendor_name";
                    ?>
                </th>
                <td align="right" style="text-align:right;">
                    <?php if (!$printer_friendly) { ?>
                        <form action="<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>" method="post" name="adminFormPF" target="_blank" style="display:inline;margin:0">
                            <input type="hidden" name="option" value="<?php echo nbf_common::get_param($_REQUEST, 'option'); ?>" />
                            <input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
                            <input type="hidden" name="task" value="<?php echo nbf_common::get_param($_REQUEST, 'task'); ?>" />
                            <input type="hidden" name="hidemainmenu" value="1" />
                            <input type="hidden" name="hide_billing_menu" value="1" />
                            <input type="hidden" name="vendor_filter" value="<?php echo nbf_common::get_param($_REQUEST, 'vendor_filter'); ?>" />
                            <input type="hidden" name="snapshot_date" value="<?php echo nbf_common::get_param($_REQUEST, 'snapshot_date'); ?>" />
                            <input type="hidden" name="defined_date_range" value="<?php echo nbf_common::get_param($_REQUEST, 'defined_date_range'); ?>" />
                            <input type="hidden" name="printer_friendly" value="1" />
                            <table cellpadding="5" cellspacing="0" border="0">
                                <tr>
                                    <td valign="middle">
                                        <a href="#" onclick="adminFormPF.submit();return false;" target="_blank"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/medium/print.gif" alt="Print" /></a>
                                    </td>
                                    <td valign="middle">
                                        <strong><a href="#" onclick="adminFormPF.submit();return false;" target="_blank" style="white-space:nowrap;"><?php echo NBILL_PRINTER_FRIENDLY; ?></a></strong>
                                    </td>
                                </tr>
                            </table>
                        </form>
                        <table cellpadding="5" cellspacing="0" border="0">
                            <tr>
                                <td valign="middle">
                                    <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=snapshot&task=csv&vendor_id=<?php echo nbf_common::get_param($_REQUEST, 'vendor_filter'); ?>&snapshot_date=<?php echo nbf_common::get_param($_REQUEST, 'snapshot_date'); ?>" title="<?php echo NBILL_CSV_DOWNLOAD_DESC; ?>"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path ?>/images/icons/medium/csv.gif" alt="<?php echo NBILL_CSV_DOWNLOAD_DESC; ?>" /></a>
                                </td>
                                <td valign="middle">
                                    <strong><a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=snapshot&task=csv&vendor_id=<?php echo nbf_common::get_param($_REQUEST, 'vendor_filter'); ?>&snapshot_date=<?php echo nbf_common::get_param($_REQUEST, 'snapshot_date'); ?>" title="<?php echo NBILL_CSV_DOWNLOAD_DESC; ?>"><?php echo NBILL_CSV_DOWNLOAD; ?></a></strong>
                                </td>
                            </tr>
                        </table>
                    <?php }
                    else
                    {
                        echo "<div style=\"white-space:nowrap\">" . NBILL_DATE_PRINTED . " " . nbf_common::nb_date($cfg_date_format, nbf_common::nb_time()) . "</div>";
                    }?>
                </td>
            </tr>
            </table>

            <?php if (!$printer_friendly) { ?>
                <p align="left"><?php echo NBILL_SNAPSHOT_INTRO; ?></p>
            <?php } ?>

            <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
            <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
            <input type="hidden" name="action" value="snapshot" />
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="box_checked" value="0" />
            <input type="hidden" name="hidemainmenu" value="0">
            <?php if ($printer_friendly)
            {
                //Print out date range
                echo "<div style=\"font-size:1.2em\"><strong>" . NBILL_SNAPSHOT_DATE . " " . nbf_common::get_param($_REQUEST, 'snapshot_date') . "</strong></div>";
            }
            else
            { ?>
                <div align="left">
                    <?php
                    //Display filter dropdown if multi-company
                    if (count($vendors) > 1)
                    {
                        echo NBILL_VENDOR_NAME . "&nbsp;";
                        $selected_filter = $vendors[0]->id;
                        if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'vendor_filter')) > 0)
                        {
                            $selected_filter = nbf_common::get_param($_POST, 'vendor_filter');
                        }
                        $vendor_name = array();
                        foreach ($vendors as $vendor)
                        {
                            $vendor_name[] = nbf_html::list_option($vendor->id, $vendor->vendor_name);
                        }
                        echo nbf_html::select_list($vendor_name, "vendor_filter", 'id="vendor_filter" class="inputbox" onchange="document.adminForm.submit();"', $selected_filter );
                        echo "&nbsp;&nbsp;";
                        $_POST['vendor_filter'] = $selected_filter;
                    }
                    else
                    {
                        echo "<input type=\"hidden\" name=\"vendor_filter\" id=\"vendor_filter\" value=\"" . $vendors[0]->id . "\" />";
                        $_POST['vendor_filter'] = $vendors[0]->id;
                    }
                    ?>
                    <span style="white-space:nowrap"><?php echo NBILL_SNAPSHOT_DATE; $cal_date_format = nbf_common::get_date_format(true); ?>
                    <input type="text" name="snapshot_date" size="10" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'snapshot_date'); ?>" />
                    <input type="button" name="snapshot_date_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.snapshot_date,'<?php echo $cal_date_format; ?>',this);" /></span>
                    <input type="submit" class="button btn" name="dosearch" value="<?php echo NBILL_GO; ?>" />
                </div>
                <?php
            }
        }

        $return_url = base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=snapshot&snapshot_date=" . nbf_common::nb_date(nbf_common::get_date_format(), $snapshot_date));

        if (!$printer_friendly && !$csv)
        {
            //Add a bit of buffer space between filters and results
            echo "<br />";
        }

        if (!$printer_friendly && !$csv)
        {
            foreach ($currencies as $currency)
            {
                if ((isset($unpaid_invoices[$currency->code]) && count($unpaid_invoices[$currency->code]) > 0))
                {
                    if (!$tab_started)
                    {
                    	$nbf_tab_snapshot = new nbf_tab_group();
                        $nbf_tab_snapshot->start_tab_group("snapshot");
                    }
                    $tab_started = true;
                    $nbf_tab_snapshot->add_tab_title($currency->code, $currency->code);
                }
            }
        }

        foreach ($currencies as $currency)
        {
            //Make sure there is something to display
            if ((isset($unpaid_invoices[$currency->code]) && count($unpaid_invoices[$currency->code]) > 0))
            {
                if ($printer_friendly)
                {
                    echo "<div class=\"adminheader\" style=\"margin-top:20px;\">" . $currency->code . "</div>";
                }
                else if (!$csv)
                {
                    ob_start();
                }
                if ($csv)
                {
                    echo NBILL_SNAPSHOT_INVOICE_DATE . ",";
                    echo NBILL_SNAPSHOT_INVOICE_NO . ",";
                    echo NBILL_SNAPSHOT_BILLING_NAME . ",";
                    echo NBILL_SNAPSHOT_NET_OS . ",";
                    echo NBILL_SNAPSHOT_TAX_OS . ",";
                    echo NBILL_SNAPSHOT_GROSS_OS . ",";
                    echo NBILL_SNAPSHOT_INVOICE_TOTAL . ",";
                    echo NBILL_SNAPSHOT_PARTIAL . ",";
                    echo NBILL_SNAPSHOT_LATER_PAID . ",";
                    echo NBILL_SNAPSHOT_LATER_PARTIAL . ",";
                    echo NBILL_SNAPSHOT_LATER_WO . "\n";
                }
                else
                { ?>
                    <div class="rounded-table">
                        <table class="adminlist table">
                            <tr>
                                <th style="text-align:left;<?php if ($printer_friendly) {echo "background-image:none;background-color:#dedede;";} ?>">
                                    <?php echo NBILL_SNAPSHOT_INVOICE_DATE; ?>
                                </th>
                                <th style="text-align:left;<?php if ($printer_friendly) {echo "background-image:none;background-color:#dedede;";} ?>">
                                    <?php echo NBILL_SNAPSHOT_INVOICE_NO; ?>
                                </th>
                                <th class="responsive-cell wide-only" style="text-align:left;<?php if ($printer_friendly) {echo "background-image:none;background-color:#dedede;";} ?>">
                                    <?php echo NBILL_SNAPSHOT_BILLING_NAME; ?>
                                </th>
                                <th align="right" style="text-align:right;<?php if ($printer_friendly) {echo "background-image:none;background-color:#dedede";} ?>">
                                    <?php echo NBILL_SNAPSHOT_NET_OS; if (!$printer_friendly) { ?>&nbsp;<?php nbf_html::show_overlib(NBILL_SNAPSHOT_NET_OS_HELP); ?>&nbsp;<?php } ?>
                                </th>
                                <th align="right" style="text-align:right;<?php if ($printer_friendly) {echo "background-image:none;background-color:#dedede";} ?>">
                                    <?php echo NBILL_SNAPSHOT_TAX_OS; if (!$printer_friendly) { ?>&nbsp;<?php nbf_html::show_overlib(NBILL_SNAPSHOT_TAX_OS_HELP); ?>&nbsp;<?php } ?>
                                </th>
                                <th align="right" style="text-align:right<?php if ($printer_friendly) {echo ";background-image:none;background-color:#dedede";} ?>">
                                    <?php echo NBILL_SNAPSHOT_GROSS_OS; if (!$printer_friendly) { ?>&nbsp;<?php nbf_html::show_overlib(NBILL_SNAPSHOT_GROSS_OS_HELP); ?>&nbsp;<?php } ?>
                                </th>
                                <th align="right" style="text-align:right<?php if ($printer_friendly) {echo ";background-image:none;background-color:#dedede";} ?>">
                                    <?php echo NBILL_SNAPSHOT_INVOICE_TOTAL; if (!$printer_friendly) { ?>&nbsp;<?php nbf_html::show_overlib(NBILL_SNAPSHOT_INVOICE_TOTAL_HELP); ?>&nbsp;<?php } ?>
                                </th>
                                <th class="selector responsive-cell priority" style="text-align:center<?php if ($printer_friendly) {echo ";background-image:none;background-color:#dedede";} ?>">
                                    <?php echo NBILL_SNAPSHOT_PARTIAL; if (!$printer_friendly) { ?>&nbsp;<?php nbf_html::show_overlib(NBILL_SNAPSHOT_PARTIAL_HELP); ?>&nbsp;<?php } ?>
                                </th>
                                <th class="responsive-cell optional selector" style="text-align:center<?php if ($printer_friendly) {echo ";background-image:none;background-color:#dedede";} ?>">
                                    <?php echo NBILL_SNAPSHOT_LATER_PARTIAL; if (!$printer_friendly) { ?>&nbsp;<?php nbf_html::show_overlib(NBILL_SNAPSHOT_LATER_PARTIAL_HELP); ?>&nbsp;<?php } ?>
                                </th>
                                <th class="responsive-cell optional selector" style="text-align:center<?php if ($printer_friendly) {echo ";background-image:none;background-color:#dedede";} ?>">
                                    <?php echo NBILL_SNAPSHOT_LATER_PAID; if (!$printer_friendly) { ?>&nbsp;<?php nbf_html::show_overlib(NBILL_SNAPSHOT_LATER_PAID_HELP); ?>&nbsp;<?php } ?>
                                </th>
                                <th class="responsive-cell optional selector" style="text-align:center<?php if ($printer_friendly) {echo ";background-image:none;background-color:#dedede";} ?>">
                                    <?php echo NBILL_SNAPSHOT_LATER_WO; if (!$printer_friendly) { ?>&nbsp;<?php nbf_html::show_overlib(NBILL_SNAPSHOT_LATER_WO_HELP); ?>&nbsp;<?php } ?>
                                </th>
                            </tr>
                            <?php
                    }
                    $net_paid = 0;
                    $tax_paid = 0;
                    $gross_paid = 0;
                    $grand_total_os_net = 0;
                    $grand_total_os_tax = 0;
                    $grand_total_os_gross = 0;
                    $grand_total_invoice = 0;
                    $invoice_count = 0;
                    for ($i = 0; $i < count($unpaid_invoices[$currency->code]); $i++)
                    {
                        $unpaid_invoice = $unpaid_invoices[$currency->code][$i];
                        if ($unpaid_invoice->income_date <= $snapshot_date)
                        {
                            //This amount was paid before the snapshot date
                            $net_paid = float_add($net_paid, $unpaid_invoice->net_amount, 'currency_grand');
                            $tax_paid = float_add($tax_paid, $unpaid_invoice->tax_amount, 'currency_grand');
                            $gross_paid = float_add($gross_paid, $unpaid_invoice->gross_amount, 'currency_grand');
                        }
                        if ($i == count($unpaid_invoices[$currency->code]) - 1 || $unpaid_invoices[$currency->code][$i]->document_id != $unpaid_invoices[$currency->code][$i + 1]->document_id)
                        {
                            $invoice_count++;
                            $grand_total_os_net = float_add($grand_total_os_net, float_subtract($unpaid_invoice->total_net, $net_paid, 'currency_grand'), 'currency_grand');
                            $grand_total_os_tax = float_add($grand_total_os_tax, float_subtract($unpaid_invoice->total_tax, $tax_paid, 'currency_grand'), 'currency_grand');
                            $grand_total_os_gross = float_add($grand_total_os_gross, float_subtract($unpaid_invoice->total_gross, $gross_paid, 'currency_grand'), 'currency_grand');
                            $grand_total_invoice = float_add($grand_total_invoice, $unpaid_invoice->total_gross, 'currency_grand');
                            if ($csv)
                            {
                                echo nbf_common::nb_date(nbf_common::get_date_format(), $unpaid_invoice->document_date) . ",";
                                echo $unpaid_invoice->document_no . ",";
                                echo str_replace(",", ";", $unpaid_invoice->billing_name) . ",";
                                echo format_number(float_subtract($unpaid_invoice->total_net, $net_paid), 'currency_grand', false, true) . ",";
                                echo format_number(float_subtract($unpaid_invoice->total_tax, $tax_paid), 'currency_grand', false, true) . ",";
                                echo format_number(float_subtract($unpaid_invoice->total_gross, $gross_paid), 'currency_grand', false, true) . ",";
                                echo format_number($unpaid_invoice->total_gross, 'currency_grand', false, true) . ",";
                                echo ($gross_paid > 0 ? NBILL_YES : NBILL_NO) . ",";
                                echo ($unpaid_invoice->partial_payment > 0 ? NBILL_YES : NBILL_NO) . ",";
                                echo ($unpaid_invoice->paid_in_full > 0 ? NBILL_YES : NBILL_NO) . ",";
                                echo ($unpaid_invoice->written_off > 0 ? NBILL_YES : NBILL_NO) . ",";
                                echo "\n";
                            }
                            else
                            {
                                ?>
                                <tr>
                                    <td style="text-align:left"><?php echo nbf_common::nb_date(nbf_common::get_date_format(), $unpaid_invoice->document_date); ?></td>
                                    <td style="text-align:left;white-space:nowrap;"><?php if (!$printer_friendly) { ?><a title="<?php echo NBILL_SNAPSHOT_EDIT_INVOICE; ?>" href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=invoices&task=edit&cid=<?php echo $unpaid_invoice->document_id; ?>&return=<?php echo $return_url; ?>"><?php } echo $unpaid_invoice->document_no;  if (!$printer_friendly) { ?></a>&nbsp;<a href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&action=invoices&task=printpreviewpopup&hidemainmenu=1&items=<?php echo $unpaid_invoice->document_id; ?>', '<?php echo nbf_common::nb_time(); ?>', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;" title="<?php echo NBILL_SNAPSHOT_VIEW_INVOICE; ?>"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/preview.gif" alt="<?php echo NBILL_SNAPSHOT_VIEW_INVOICE; ?>" border="0" /></a><?php } ?></td>
                                    <td class="responsive-cell wide-only" style="text-align:left"><?php if (!$printer_friendly && $unpaid_invoice->entity_id > 0) { ?><a title="<?php echo NBILL_SNAPSHOT_EDIT_CLIENT; ?>" href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=clients&task=edit&cid=<?php echo $unpaid_invoice->entity_id; ?>&return=<?php echo $return_url; ?>"><?php } echo $unpaid_invoice->billing_name; if ($unpaid_invoice->entity_id > 0) { ?></a><?php } ?></td>
                                    <td align="right" style="text-align:right"><?php echo format_number(float_subtract($unpaid_invoice->total_net, $net_paid), 'currency_grand', true, false, null, $currency->code); ?></td>
                                    <td align="right" style="text-align:right"><?php echo format_number(float_subtract($unpaid_invoice->total_tax, $tax_paid), 'currency_grand', true, false, null, $currency->code); ?></td>
                                    <td align="right" style="text-align:right"><?php echo format_number(float_subtract($unpaid_invoice->total_gross, $gross_paid), 'currency_grand', true, false, null, $currency->code); ?></td>
                                    <td align="right" style="text-align:right"><?php echo format_number($unpaid_invoice->total_gross, 'currency_grand', true, false, null, $currency->code); ?></td>
                                    <td class="responsive-cell priority" align="center" style="text-align:center;"><?php if (!$printer_friendly) { ?><a href="<?php echo $gross_paid > 0 ? nbf_cms::$interop->admin_page_prefix . "&action=income&for_invoice=" . $unpaid_invoice->document_id . "&search_date_to=" . $snapshot_date : "#"; ?>" title="<?php echo $gross_paid > 0 ? NBILL_SNAPSHOT_MARKED_PARTIAL : NBILL_SNAPSHOT_NOT_MARKED_PARTIAL; ?>" onclick="<?php echo $gross_paid > 0 ? "return true;" : "return false;"; ?>"><?php } ?><img style="display:block;margin-left:auto;margin-right:auto;" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/<?php echo $gross_paid > 0 ? "tick" : "cross"; ?>.png" border="0" alt="<?php echo $gross_paid > 0 ? NBILL_SNAPSHOT_MARKED_PARTIAL : NBILL_SNAPSHOT_NOT_MARKED_PARTIAL; ?>" /><?php if (!$printer_friendly) { ?></a><?php } ?></td>
                                    <td class="responsive-cell optional" align="center" style="text-align:center;"><?php if (!$printer_friendly) { ?><a href="<?php echo $unpaid_invoice->partial_payment > 0 ? nbf_cms::$interop->admin_page_prefix . "&action=income&for_invoice=" . $unpaid_invoice->document_id : "#"; ?>" title="<?php echo $unpaid_invoice->partial_payment > 0 ? ($gross_paid > 0 ? NBILL_SNAPSHOT_STILL_MARKED_LATER_PARTIAL : NBILL_SNAPSHOT_MARKED_LATER_PARTIAL) : NBILL_SNAPSHOT_NOT_MARKED_LATER_PARTIAL; ?>" onclick="<?php echo $unpaid_invoice->partial_payment > 0 ? "return true;" : "return false;"; ?>"><?php } ?><img style="display:block;margin-left:auto;margin-right:auto;" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/<?php echo $unpaid_invoice->partial_payment > 0 ? "tick" : "cross"; ?>.png" border="0" alt="<?php echo $unpaid_invoice->partial_payment ? ($gross_paid > 0 ? NBILL_SNAPSHOT_STILL_MARKED_LATER_PARTIAL : NBILL_SNAPSHOT_MARKED_LATER_PARTIAL) : NBILL_SNAPSHOT_NOT_MARKED_LATER_PARTIAL; ?>" /><?php if (!$printer_friendly) { ?></a><?php } ?></td>
                                    <td class="responsive-cell optional" align="center" style="text-align:center;"><?php if (!$printer_friendly) { ?><a href="<?php echo $unpaid_invoice->paid_in_full > 0 ? nbf_cms::$interop->admin_page_prefix . "&action=income&for_invoice=" . $unpaid_invoice->document_id : "#"; ?>" title="<?php echo $unpaid_invoice->paid_in_full > 0 ? NBILL_SNAPSHOT_MARKED_LATER_PAID : NBILL_SNAPSHOT_NOT_MARKED_LATER_PAID; ?>" onclick="<?php echo $unpaid_invoice->paid_in_full > 0 ? "return true;" : "return false;"; ?>"><?php } ?><img style="display:block;margin-left:auto;margin-right:auto;" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/<?php echo $unpaid_invoice->paid_in_full > 0 ? "tick" : "cross"; ?>.png" border="0" alt="<?php echo $unpaid_invoice->paid_in_full ? NBILL_SNAPSHOT_MARKED_LATER_PAID : NBILL_SNAPSHOT_NOT_MARKED_LATER_PAID; ?>" /><?php if (!$printer_friendly) { ?></a><?php } ?></td>
                                    <td class="responsive-cell optional" align="center" style="text-align:center;"><img style="display:block;margin-left:auto;margin-right:auto;" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/<?php echo $unpaid_invoice->written_off > 0 ? "tick" : "cross"; ?>.png" border="0" alt="<?php echo $unpaid_invoice->written_off ? NBILL_SNAPSHOT_MARKED_LATER_WO : NBILL_SNAPSHOT_NOT_MARKED_LATER_WO; ?>" /></td>
                                </tr>
                                <?php
                            }
                            $net_paid = 0;
                            $tax_paid = 0;
                            $gross_paid = 0;
                        }
                    }
                    if (!$csv)
                    {
                    ?>
                        <tr class="nbill_tr_no_highlight">
                            <td style="text-align:left" colspan="2"><strong><?php echo NBILL_SNAPSHOT_TOTALS . " " . sprintf(NBILL_SNAPSHOT_COUNT, $invoice_count); ?></strong></td>
                            <td class="responsive-cell wide-only"></td>
                            <td align="right" style="text-align:right"><strong><?php echo format_number($grand_total_os_net, 'currency_grand', true, false, null, $currency->code); ?></strong></td>
                            <td align="right" style="text-align:right"><strong><?php echo format_number($grand_total_os_tax, 'currency_grand', true, false, null, $currency->code); ?></strong></td>
                            <td align="right" style="text-align:right"><strong><?php echo format_number($grand_total_os_gross, 'currency_grand', true, false, null, $currency->code); ?></strong></td>
                            <td align="right" style="text-align:right"><strong><?php echo format_number($grand_total_invoice, 'currency_grand', true, false, null, $currency->code); ?></strong></td>
                            <td class="responsive-cell priority">&nbsp;</td>
                            <td class="responsive-cell optional">&nbsp;</td>
                            <td class="responsive-cell optional">&nbsp;</td>
                            <td class="responsive-cell optional">&nbsp;</td>
                        </tr>
                    </table>
                </div>
                <?php
                }
                if (!$printer_friendly && !$csv)
                {
                    $nbf_tab_snapshot->add_tab_content($currency->code, ob_get_clean());
                }
            }
        }
        if (!$printer_friendly && !$csv && $tab_started)
        {
            $nbf_tab_snapshot->end_tab_group();
        }
        if ($printer_friendly)
        {
            echo "</td></tr></table>";
        }
        if (!$csv)
        {
            ?>
            <br />
            </form>
            <?php
        }
    }
}