<?php
/**
* HTML Output for anomaly report
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillAnomaly
{
    public static function showAnomalyReport(&$vendors, $vendor_name, &$anomalies, $task = "", $printer_friendly = false)
    {
        nbf_cms::$interop->add_html_header("<link rel=\"stylesheet\" href=\"" . nbf_cms::$interop->nbill_site_url_path . "/style/admin/collapsible.css\" type=\"text/css\" />");
        nbf_html::load_calendar();
        $date_parts = nbf_common::nb_getdate(time());
        ?>
        <script type="text/javascript">
        function expand(identifier)
        {
            var expanded = document.getElementById('expanded_' + identifier).value;
            if (expanded == "1")
            {
                //Collapse the node
                document.getElementById('img_' + identifier).setAttribute('src', '<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png');
                document.getElementById(identifier).style.display = 'none';
                document.getElementById('expanded_' + identifier).value = 0;
            }
            else
            {
                //Expand the node
                document.getElementById('img_' + identifier).setAttribute('src', '<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png');
                document.getElementById(identifier).style.display = '';
                document.getElementById('expanded_' + identifier).value = 1;
            }
        }
        </script>

        <table class="adminheading" style="width:100%;">
        <tr>
            <th <?php if ($printer_friendly) {echo "style=\"background-image:none !important; margin-left:0;padding-left:0;\"";} else {echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "anomaly");} ?>>
                <?php echo NBILL_ANOMALY_TITLE . " " . NBILL_FOR . " $vendor_name";
                ?>
            </th>
            <td align="right" style="text-align:right;">
                <?php if (!$printer_friendly && $task=="show_anomalies") { ?>
                    <form action="<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>" method="post" name="adminFormPF" target="_blank" style="display:inline;margin:0">
                        <input type="hidden" name="option" value="<?php echo nbf_common::get_param($_REQUEST, 'option'); ?>" />
                        <input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
                        <input type="hidden" name="task" value="<?php echo nbf_common::get_param($_REQUEST, 'task'); ?>" />
                        <input type="hidden" name="hidemainmenu" value="1" />
                        <input type="hidden" name="hide_billing_menu" value="1" />
                        <input type="hidden" name="vendor_filter" value="<?php echo nbf_common::get_param($_REQUEST, 'vendor_filter'); ?>" />
                        <input type="hidden" name="search_date_from" value="<?php echo nbf_common::get_param($_REQUEST, 'search_date_from'); ?>" />
                        <input type="hidden" name="search_date_to" value="<?php echo nbf_common::get_param($_REQUEST, 'search_date_to'); ?>" />
                        <input type="hidden" name="printer_friendly" value="1" />
                        <?php for ($i=1; $i<10; $i++)
                        {
                            ?><input type="hidden" id="prt_check_<?php echo $i; ?>" name="check_<?php echo $i; ?>" value="" /><?php
                        } ?>
                        <table cellpadding="5" cellspacing="0" border="0" style="width:100%">
                            <tr>
                                <td align="right" valign="middle" style="text-align:right;width:100%;">
                                    <a href="#" onclick="for(var i=1;i<10;i++){document.getElementById('prt_check_' + i).value=document.getElementById('check_' + i).checked ? 'On' : '';}document.adminFormPF.submit();return false;" target="_blank"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/medium/print.gif" alt="Print" /></a>
                                </td>
                                <td align="right" valign="middle" style="text-align:right;white-space:nowrap;">
                                    <strong><a href="#" onclick="for(var i=1;i<10;i++){document.getElementById('prt_check_' + i).value=document.getElementById('check_' + i).checked ? 'On' : '';}document.adminFormPF.submit();return false;" target="_blank" style="white-space:nowrap;"><?php echo NBILL_ANOMALY_PF; ?></a></strong>
                                </td>
                            </tr>
                        </table>
                    </form>
                <?php }
                else if ($printer_friendly)
                {
                    echo "<div style=\"white-space:nowrap\">" . NBILL_DATE_PRINTED . " " . nbf_common::nb_date(nbf_common::get_date_format(), nbf_common::nb_time()) . "</div>";
                }?>
            </td>
        </tr>
        </table>

        <?php if (!$printer_friendly) { ?><p align="left"><?php echo NBILL_ANOMALY_INTRO; ?></p> <?php } ?>
        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
            <input type="hidden" name="option" value="<?php echo nbf_common::get_param($_REQUEST, 'option'); ?>" />
            <input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
            <input type="hidden" name="task" value="<?php echo nbf_common::get_param($_REQUEST, 'task'); ?>" />
            <input type="hidden" name="expanded_anomaly_criteria" id="expanded_anomaly_criteria" value="<?php echo $task=="show_anomalies" ? "0" : "1"; ?>" />

            <?php if ($printer_friendly)
            {
                //Print out date range
                if (nbf_common::get_param($_REQUEST, 'date_range') != "all")
                {
                    echo "<div style=\"font-size:1.2em\"><strong>" . NBILL_DATE_RANGE . " " . nbf_common::get_param($_REQUEST, 'search_date_from') . " " . NBILL_TO . " " . nbf_common::get_param($_REQUEST, 'search_date_to') . "</strong></div>";
                }
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
                        $_POST['vendor_filter'] = $selected_filter;
                    }
                    else
                    {
                        echo "<input type=\"hidden\" name=\"vendor_filter\" id=\"vendor_filter\" value=\"" . $vendors[0]->id . "\" />";
                        $_POST['vendor_filter'] = $vendors[0]->id;
                    } ?>
                </div>
                <?php
            } ?>

            <?php if (!$printer_friendly)
            { ?>
                <div><strong><?php if ($task=="show_anomalies") { ?><a href="#" onclick="expand('anomaly_criteria');return false;"><img id="img_anomaly_criteria" border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png" alt="" /></a>&nbsp;<?php } echo NBILL_ANOMALY_CRITERIA; ?>&nbsp;<?php nbf_html::show_overlib(NBILL_ANOMALY_CRITERIA_HELP); ?></strong></div>
                <div class="rounded-table">
                    <table class="adminlist table" cellspacing="0" cellpadding="2" id="anomaly_criteria" style="border-collapse:collapse;<?php if ($task=="show_anomalies") {echo "display:none;";} ?>">
                        <tr class="nbill_tr_no_highlight">
                            <th style="background-image:none"><?php echo NBILL_ANOMALY_DATE_RANGE; ?></th>
                            <td>
                                <input type="radio" name="date_range" id="date_all" class="nbill_form_input" value="all"<?php if (nbf_common::get_param($_REQUEST, 'date_range')!='range') {echo " checked=\"checked\"";} ?> onclick="if(this.checked){document.adminForm.search_date_from.disabled=true;document.adminForm.search_date_from_cal.disabled=true;document.adminForm.search_date_to.disabled=true;document.adminForm.search_date_to_cal.disabled=true;}" /><label for="date_all" class="nbill_form_label"><?php echo NBILL_ANOMALY_ALL; ?></label>&nbsp;
                                <input type="radio" name="date_range" id="date_range" class="nbill_form_input" value="range"<?php if (nbf_common::get_param($_REQUEST, 'date_range')=='range') {echo "checked=\"checked\"";} ?> onclick="if(this.checked){document.adminForm.search_date_from.disabled=false;document.adminForm.search_date_from_cal.disabled=false;document.adminForm.search_date_to.disabled=false;document.adminForm.search_date_to_cal.disabled=false;}" /><label for="date_range" class="nbill_form_label"><?php echo NBILL_ANOMALY_RANGE; ?></label>
                                <?php $date_format = nbf_common::get_date_format();
                                $cal_date_format = nbf_common::get_date_format(true); ?>
                                <span style="white-space:nowrap"><input type="text" name="search_date_from" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_from') == 0 ? nbf_common::nb_date($date_format, nbf_date::get_default_start_date()) : nbf_common::get_param($_REQUEST,'search_date_from'); ?>"<?php if (nbf_common::get_param($_REQUEST, 'date_range') == "all") {echo " disabled=\"disabled\"";} ?> />
                                <input type="button" name="search_date_from_cal" class="button btn" class="nbill_form_input" value="..." onclick="displayCalendar(document.adminForm.search_date_from,'<?php echo $cal_date_format; ?>',this);"<?php if (nbf_common::get_param($_REQUEST, 'date_range') == "all") {echo " disabled=\"disabled\"";} ?> /></span>
                                <?php echo NBILL_TO; ?>
                                <span style="white-space:nowrap"><input type="text" name="search_date_to" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_to') == nbf_common::nb_mktime(23, 59, 59, 12, 31, 2037) ? nbf_common::nb_date($date_format, nbf_common::nb_mktime(23, 59, 59, $date_parts["mon"], $date_parts["mday"], $date_parts["year"])) : nbf_common::get_param($_REQUEST,'search_date_to'); ?>"<?php if (nbf_common::get_param($_REQUEST, 'date_range') == "all") {echo " disabled=\"disabled\"";} ?> />
                                <input type="button" name="search_date_to_cal" class="button btn" class="nbill_form_input" value="..." onclick="displayCalendar(document.adminForm.search_date_to,'<?php echo $cal_date_format; ?>',this);"<?php if (nbf_common::get_param($_REQUEST, 'date_range') == "all") {echo " disabled=\"disabled\"";} ?> /></span>
                            </td>
                        </tr>
                        <tr class="nbill_tr_no_highlight">
                            <th style="background-image:none"><?php echo NBILL_ANOMALY_CHECKS; ?></th>
                            <td><input type="checkbox" class="nbill_form_input" name="check_1" id="check_1"<?php if ($task!="show_anomalies" || nbf_common::get_param($_REQUEST, 'check_1')){echo " checked=\"checked\"";} ?> /><label for="check_1" class="nbill_form_label"><?php echo NBILL_ANOMALY_CHECK_1; ?></label>&nbsp;<?php nbf_html::show_overlib(NBILL_ANOMALY_CHECK_1_HELP); ?></td>
                        </tr>
                        <?php for ($i=2; $i<10; $i++)
                        {
                            ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td colspan="2"><input type="checkbox" class="nbill_form_input" name="check_<?php echo $i; ?>" id="check_<?php echo $i; ?>"<?php if ($task!="show_anomalies" || nbf_common::get_param($_REQUEST, 'check_' . $i)){echo " checked=\"checked\"";} ?> /><label for="check_<?php echo $i; ?>" class="nbill_form_label"><?php echo constant("NBILL_ANOMALY_CHECK_$i"); ?></label>&nbsp;<?php nbf_html::show_overlib(constant("NBILL_ANOMALY_CHECK_$i" . "_HELP")); ?></td>
                            </tr>
                            <?php
                        }?>
                        <tr class="nbill_tr_no_highlight">
                            <td>&nbsp;</td>
                            <td><input type="submit" class="button btn" name="anomaly_search" id="anomaly_search" value="<?php echo NBILL_ANOMALY_SEARCH; ?>" onclick="document.adminForm.task.value='show_anomalies';document.adminForm.submit();return false;" /></td>
                        </tr>
                    </table>
                </div>
                <br />
            <?php } ?>
            <div id="anomaly_results"<?php if ($task!="show_anomalies") {echo " style=\"display:none;\"";} ?>>
                <?php if (!$printer_friendly) { ?><div><strong><?php echo NBILL_ANOMALY_RESULTS ?></strong></div><?php } ?>
                <?php
                for ($i=1; $i<10; $i++)
                {
                    if (nbf_common::get_param($_REQUEST, 'check_' . $i))
                    {
                        ?>
                        <div class="rounded-table">
                            <table border="0" cellspacing="0" cellpadding="2" class="adminlist table">
                                <tr class="nbill_tr_no_highlight"><th><?php echo constant("NBILL_ANOMALY_CHECK_" . $i); ?></th></tr>
                                <?php
                                if (isset($anomalies[$i]) && count($anomalies[$i]) > 0)
                                { ?>
                                    <tr class="nbill_tr_no_highlight"><td><img style="vertical-align:middle" src="<?php echo nbf_cms::$interop->nbill_site_url_path ?>/images/big-cross.png" alt="" />&nbsp;<?php echo constant("NBILL_ANOMALY_CHECK_" . $i . "_WHAT"); ?></td></tr>
                                    <tr class="nbill_tr_no_highlight"><td>
                                    <table border="0" cellspacing="0" cellpadding="2" class="nbill-anomaly">
                                        <?php self::output_anomalies($anomalies, $i); ?>
                                    </table>
                                    </td></tr>
                                <?php
                                }
                                else
                                {
                                    ?>
                                    <tr class="nbill_tr_no_highlight"><td><img style="vertical-align:middle" src="<?php echo nbf_cms::$interop->nbill_site_url_path ?>/images/big-tick.png" alt="" />&nbsp;<?php echo NBILL_ANOMALY_NONE_FOUND; ?></td></tr>
                                    <?php
                                }
                                ?>
                            </table>
                        </div>
                        <br />
                        <?php
                    }
                }
                ?>
            </div>
        </form>
        <?php
    }

    function output_anomalies(&$anomalies, $i)
    {
        $date_format = nbf_common::get_date_format();
        switch ($i)
        {
            case 1:
                //Missing Income/Expenditure Records
                $cr_header_added = false;
                if ($anomalies[$i][0]->document_type == "IN")
                {
                    ?>
                    <tr>
                        <th><?php echo NBILL_ANOMALY_INVOICE_NO; ?></th>
                        <th><?php echo NBILL_ANOMALY_DATE; ?></th>
                        <th class="responsive-cell optional"><?php echo NBILL_ANOMALY_DESC; ?></th>
                        <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_INVOICE_NET; ?></th>
                        <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_INVOICE_TAX; ?></th>
                        <th align="right" style="text-align:right"><?php echo NBILL_ANOMALY_INVOICE_GROSS; ?></th>
                        <th class="selector" style="text-align:center"><?php echo NBILL_ANOMALY_MARKED_PAID; ?></th>
                        <th class="selector" style="text-align:center"><?php echo NBILL_ANOMALY_MARKED_PARTIAL; ?></th>
                    </tr>
                    <?php
                }
                foreach ($anomalies[$i] as &$anomaly)
                {
                    if (!$cr_header_added && $anomaly->document_type == "CR")
                    {
                        ?>
                        <tr>
                            <th><?php echo NBILL_ANOMALY_CR_NO; ?></th>
                            <th><?php echo NBILL_ANOMALY_DATE; ?></th>
                            <th class="responsive-cell optional"><?php echo NBILL_ANOMALY_DESC; ?></th>
                            <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_CR_NET; ?></th>
                            <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_CR_TAX; ?></th>
                            <th align="right" style="text-align:right"><?php echo NBILL_ANOMALY_CR_GROSS; ?></th>
                            <th class="selector" style="text-align:center"><?php echo NBILL_ANOMALY_MARKED_PAID; ?></th>
                            <th class="selector" style="text-align:center"><?php echo NBILL_ANOMALY_MARKED_PARTIAL; ?></th>
                        </tr>
                        <?php
                        $cr_header_added = true;
                    }
                    $link = nbf_cms::$interop->admin_page_prefix . "&action=" . ($anomaly->document_type == "CR" ? "credits" : "invoices") . "&task=edit&cid=" . $anomaly->document_id;
                    ?>
                    <tr>
                        <td><a target="_blank" href="<?php echo $link; ?>"><?php echo $anomaly->document_no; ?></a></td>
                        <td><?php echo nbf_common::nb_date($date_format, $anomaly->document_date); ?></td>
                        <td class="responsive-cell optional"><?php echo $anomaly->billing_name; ?></td>
                        <td class="responsive-cell priority" align="right" style="text-align:right"><?php echo format_number($anomaly->total_net, 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                        <td class="responsive-cell priority" align="right" style="text-align:right"><?php echo format_number($anomaly->total_tax, 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                        <td align="right" style="text-align:right"><?php echo format_number($anomaly->total_gross, 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                        <td align="center" style="text-align:center"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/<?php echo $anomaly->paid_in_full ? "tick" : "cross"; ?>.png" alt="<?php echo ($anomaly->paid_in_full ? NBILL_ANOMALY_PAID_YES : NBILL_ANOMALY_PAID_NO); ?>" /></td>
                        <td align="center" style="text-align:center"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/<?php echo $anomaly->partial_payment ? "tick" : "cross"; ?>.png" alt="<?php echo ($anomaly->partial_payment ? NBILL_ANOMALY_PARTIAL_YES : NBILL_ANOMALY_PARTIAL_NO); ?>" /></td>
                    </tr>
                    <?php
                }
                break;
            case 2:
            case 3:
            case 7:
                $cr_header_added = false;
                if (isset($anomalies[$i][0]->document_type) && $anomalies[$i][0]->document_type == "IN")
                {
                    ?>
                    <tr>
                        <th><?php echo NBILL_ANOMALY_INVOICE_NO; ?></th>
                        <th><?php echo NBILL_ANOMALY_DATE; ?></th>
                        <th class="responsive-cell optional"><?php echo NBILL_ANOMALY_DESC; ?></th>
                        <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_INVOICE_NET; ?></th>
                        <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_INVOICE_TAX; ?></th>
                        <th align="right" style="text-align:right"><?php echo $i == 7 ? NBILL_ANOMALY_INVOICE_GROSS : NBILL_ANOMALY_AMOUNT_EXPECTED; ?></th>
                        <?php if ($i != 7) { ?><th align="right" style="text-align:right"><?php echo NBILL_ANOMALY_AMOUNT_RECEIVED; ?></th><?php } ?>
                        <th><?php echo NBILL_ANOMALY_ACTION; ?></th>
                    </tr>
                    <?php
                }
                foreach ($anomalies[$i] as &$anomaly)
                {
                    if (isset($anomaly->document_type))
                    {
                        if (!$cr_header_added && $anomaly->document_type == "CR")
                        {
                            ?>
                            <tr>
                                <th><?php echo NBILL_ANOMALY_CR_NO; ?></th>
                                <th><?php echo NBILL_ANOMALY_DATE; ?></th>
                                <th class="responsive-cell optional"><?php echo NBILL_ANOMALY_DESC; ?></th>
                                <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_CR_NET; ?></th>
                                <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_CR_TAX; ?></th>
                                <th align="right" style="text-align:right"><?php echo $i == 7 ? NBILL_ANOMALY_CR_GROSS : NBILL_ANOMALY_AMOUNT_EXPECTED; ?></th>
                                <?php if ($i != 7) { ?><th align="right" style="text-align:right"><?php echo NBILL_ANOMALY_AMOUNT_PAID; ?></th><?php } ?>
                                <th><?php echo NBILL_ANOMALY_ACTION; ?></th>
                            </tr>
                            <?php
                            $cr_header_added = true;
                        }
                        $link = nbf_cms::$interop->admin_page_prefix . "&action=" . ($anomaly->document_type == "CR" ? "credits" : "invoices") . "&task=edit&cid=" . $anomaly->document_id;
                        ?>
                        <tr>
                            <td><a target="_blank" href="<?php echo $link; ?>"><?php echo $anomaly->document_no; ?></a></td>
                            <td><?php echo nbf_common::nb_date($date_format, $anomaly->document_date); ?></td>
                            <td class="responsive-cell optional"><?php echo $anomaly->billing_name; ?></td>
                            <td class="responsive-cell priority" align="right" style="text-align:right"><?php echo format_number($anomaly->total_net, 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                            <td class="responsive-cell priority" align="right" style="text-align:right"><?php echo format_number($anomaly->total_tax, 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                            <td align="right" style="text-align:right"><?php echo format_number($anomaly->total_gross, 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                            <?php if ($i != 7) { ?><td align="right" style="text-align:right"><?php echo format_number($anomaly->total_paid, 'currency_grand', true, false, null, $anomaly->currency); ?></td><?php } ?>
                            <td><a target="_blank" href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=<?php echo $anomaly->document_type == "IN" ? "income" : "expenditure"; ?>&task=view&for_invoice=<?php echo $anomaly->document_id; ?>"><?php echo $anomaly->document_type == "IN" ? NBILL_ANOMALY_VIEW_INCOME : NBILL_ANOMALY_VIEW_EXPENDITURE; ?></a></td>
                        </tr>
                        <?php
                    }
                }
                if ($i != 7)
                {
                    break;
                }
            case 4:
            case 5:
            case 6:
            case 7:
                $ex_header_added = false;
                $in_header_added = false;
                foreach ($anomalies[$i] as &$anomaly)
                {
                    if (isset($anomaly->transaction_type))
                    {
                        if (!$in_header_added && $anomaly->transaction_type == "IN")
                        {
                            ?>
                            <tr>
                                <th><?php echo NBILL_ANOMALY_RECEIPT_NO; ?></th>
                                <th><?php echo NBILL_ANOMALY_DATE; ?></th>
                                <th class="responsive-cell optional"><?php echo NBILL_ANOMALY_DESC; ?></th>
                                <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_INCOME_NET; ?></th>
                                <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_INCOME_TAX; ?></th>
                                <th align="right" style="text-align:right"><?php echo NBILL_ANOMALY_INCOME_GROSS; ?></th>
                                <?php if ($i > 4) { ?>
                                    <th><?php echo NBILL_ANOMALY_INVOICE_NO; ?></th>
                                <?php } ?>
                            </tr>
                            <?php
                            $in_header_added = true;
                        }
                        if (!$ex_header_added && $anomaly->transaction_type == "EX")
                        {
                            ?>
                            <tr>
                                <th><?php echo NBILL_ANOMALY_PAYMENT_NO; ?></th>
                                <th class="responsive-cell optional"><?php echo NBILL_ANOMALY_DESC; ?></th>
                                <th><?php echo NBILL_ANOMALY_DATE; ?></th>
                                <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_EXP_NET; ?></th>
                                <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_EXP_TAX; ?></th>
                                <th align="right" style="text-align:right"><?php echo NBILL_ANOMALY_EXP_GROSS; ?></th>
                                <?php if ($i > 4) { ?>
                                    <th><?php echo NBILL_ANOMALY_CR_NO; ?></th>
                                <?php } ?>
                            </tr>
                            <?php
                            $ex_header_added = true;
                        }
                        $link = nbf_cms::$interop->admin_page_prefix . "&action=" . ($anomaly->transaction_type == "EX" ? "expenditure" : "income") . "&task=edit&cid=" . $anomaly->transaction_id;
                        ?>
                        <tr>
                            <td><a target="_blank" href="<?php echo $link; ?>"><?php echo $anomaly->transaction_no ? $anomaly->transaction_no : constant("NBILL_AWAITING_" . $anomaly->transaction_type); ?></a></td>
                            <td class="responsive-cell optional"><?php echo $anomaly->name; ?></td>
                            <td><?php echo nbf_common::nb_date($date_format, $anomaly->date); ?></td>
                            <td class="responsive-cell priority" align="right" style="text-align:right"><?php echo format_number(float_subtract($anomaly->amount, float_add($anomaly->tax_amount_1, float_add($anomaly->tax_amount_2, $anomaly->tax_amount_3, 'currency_grand'), 'currency_grand')), 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                            <td class="responsive-cell priority" align="right" style="text-align:right"><?php echo format_number(float_add($anomaly->tax_amount_1, float_add($anomaly->tax_amount_2, $anomaly->tax_amount_3, 'currency_grand'), 'currency_grand'), 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                            <td align="right" style="text-align:right"><?php echo format_number($anomaly->amount, 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                            <?php if ($i > 4) {
                                $link2 = nbf_cms::$interop->admin_page_prefix . "&action=" . ($anomaly->transaction_type == "EX" ? "credits" : "invoices") . "&task=edit&cid=" . $anomaly->document_id; ?>
                                <td><a target="_blank" href="<?php echo $link2; ?>"><?php echo $anomaly->document_no; ?></a></td>
                            <?php } ?>
                        </tr>
                        <?php
                    }
                }
                break;
            case 8:
                //Ledger Amount Mis-match
                $ex_header_added = false;
                $in_header_added = false;
                foreach ($anomalies[$i] as &$anomaly)
                {
                    if (isset($anomaly->transaction_type))
                    {
                        if (!$in_header_added && $anomaly->transaction_type == "IN")
                        {
                            ?>
                            <tr>
                                <th><?php echo NBILL_ANOMALY_RECEIPT_NO; ?></th>
                                <th><?php echo NBILL_ANOMALY_DATE; ?></th>
                                <th class="responsive-cell optional"><?php echo NBILL_ANOMALY_DESC; ?></th>
                                <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_INCOME_NET; ?></th>
                                <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_LEDGER_NET; ?></th>
                                <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_INCOME_TAX; ?></th>
                                <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_LEDGER_TAX; ?></th>
                                <th align="right" style="text-align:right"><?php echo NBILL_ANOMALY_INCOME_GROSS; ?></th>
                                <th align="right" style="text-align:right"><?php echo NBILL_ANOMALY_LEDGER_GROSS; ?></th>
                            </tr>
                            <?php
                            $in_header_added = true;
                        }
                        if (!$ex_header_added && $anomaly->transaction_type == "EX")
                        {
                            ?>
                            <tr>
                                <th><?php echo NBILL_ANOMALY_PAYMENT_NO; ?></th>
                                <th class="responsive-cell optional"><?php echo NBILL_ANOMALY_DESC; ?></th>
                                <th><?php echo NBILL_ANOMALY_DATE; ?></th>
                                <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_EXP_NET; ?></th>
                                <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_LEDGER_NET; ?></th>
                                <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_EXP_TAX; ?></th>
                                <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_LEDGER_TAX; ?></th>
                                <th align="right" style="text-align:right"><?php echo NBILL_ANOMALY_EXP_GROSS; ?></th>
                                <th align="right" style="text-align:right"><?php echo NBILL_ANOMALY_LEDGER_GROSS; ?></th>
                            </tr>
                            <?php
                            $ex_header_added = true;
                        }
                        $link = nbf_cms::$interop->admin_page_prefix . "&action=" . ($anomaly->transaction_type == "EX" ? "expenditure" : "income") . "&task=edit&cid=" . $anomaly->transaction_id;
                        ?>
                        <tr>
                            <td><a target="_blank" href="<?php echo $link; ?>"><?php echo $anomaly->transaction_no ? $anomaly->transaction_no : constant("NBILL_AWAITING_" . $anomaly->transaction_type); ?></a></td>
                            <td class="responsive-cell optional"><?php echo $anomaly->name; ?></td>
                            <td><?php echo nbf_common::nb_date($date_format, $anomaly->date); ?></td>
                            <td class="responsive-cell priority" align="right" style="text-align:right"><?php echo format_number(float_subtract($anomaly->amount, float_add($anomaly->tax_amount_1, float_add($anomaly->tax_amount_2, $anomaly->tax_amount_3, 'currency_grand'), 'currency_grand')), 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                            <td class="responsive-cell priority" align="right" style="text-align:right"><?php echo format_number($anomaly->ledger_total_net, 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                            <td class="responsive-cell priority" align="right" style="text-align:right"><?php echo format_number(float_add($anomaly->tax_amount_1, float_add($anomaly->tax_amount_2, $anomaly->tax_amount_3, 'currency_grand'), 'currency_grand'), 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                            <td class="responsive-cell priority" align="right" style="text-align:right"><?php echo format_number($anomaly->ledger_total_tax, 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                            <td align="right" style="text-align:right"><?php echo format_number($anomaly->amount, 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                            <td align="right" style="text-align:right"><?php echo format_number($anomaly->ledger_total_gross, 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                        </tr>
                        <?php
                    }
                }
                break;
            case 9:
                //Date Anomalies
                $cr_header_added = false;
                if ($anomalies[$i][0]->document_type == "IN")
                {
                    ?>
                    <tr>
                        <th><?php echo NBILL_ANOMALY_INVOICE_NO; ?></th>
                        <th class="responsive-cell optional"><?php echo NBILL_ANOMALY_DESC; ?></th>
                        <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_INVOICE_NET; ?></th>
                        <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_INVOICE_TAX; ?></th>
                        <th align="right" style="text-align:right"><?php echo NBILL_ANOMALY_INVOICE_GROSS; ?></th>
                        <th><?php echo NBILL_ANOMALY_INVOICE_DATE; ?></th>
                        <th><?php echo NBILL_ANOMALY_PAYMENT_DATE; ?></th>
                        <th><?php echo NBILL_ANOMALY_RECEIPT_NO; ?></th>
                    </tr>
                    <?php
                }
                foreach ($anomalies[$i] as &$anomaly)
                {
                    if (!$cr_header_added && $anomaly->document_type == "CR")
                    {
                        ?>
                        <tr>
                            <th><?php echo NBILL_ANOMALY_CR_NO; ?></th>
                            <th class="responsive-cell optional"><?php echo NBILL_ANOMALY_DESC; ?></th>
                            <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_CR_NET; ?></th>
                            <th class="responsive-cell priority" align="right" style="text-align:right"><?php echo NBILL_ANOMALY_CR_TAX; ?></th>
                            <th align="right" style="text-align:right"><?php echo NBILL_ANOMALY_CR_GROSS; ?></th>
                            <th><?php echo NBILL_ANOMALY_CR_DATE; ?></th>
                            <th><?php echo NBILL_ANOMALY_PAYMENT_DATE; ?></th>
                            <th><?php echo NBILL_ANOMALY_PAYMENT_NO; ?></th>
                        </tr>
                        <?php
                        $cr_header_added = true;
                    }
                    $link = nbf_cms::$interop->admin_page_prefix . "&action=" . ($anomaly->document_type == "CR" ? "credits" : "invoices") . "&task=edit&cid=" . $anomaly->document_id;
                    $link2 = nbf_cms::$interop->admin_page_prefix . "&action=" . ($anomaly->transaction_type == "EX" ? "expenditure" : "income") . "&task=edit&cid=" . $anomaly->transaction_id;
                    ?>
                    <tr>
                        <td><a target="_blank" href="<?php echo $link; ?>"><?php echo $anomaly->document_no; ?></a></td>
                        <td class="responsive-cell optional"><?php echo $anomaly->billing_name; ?></td>
                        <td class="responsive-cell priority" align="right" style="text-align:right"><?php echo format_number($anomaly->total_net, 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                        <td class="responsive-cell priority" align="right" style="text-align:right"><?php echo format_number($anomaly->total_tax, 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                        <td align="right" style="text-align:right"><?php echo format_number($anomaly->total_gross, 'currency_grand', true, false, null, $anomaly->currency); ?></td>
                        <td><?php echo nbf_common::nb_date($date_format, $anomaly->document_date); ?></td>
                        <td><?php echo nbf_common::nb_date($date_format, $anomaly->date); ?></td>
                        <td><a target="_blank" href="<?php echo $link2; ?>"><?php echo $anomaly->transaction_no ? $anomaly->transaction_no : ($anomaly->document_type == "CR" ? NBILL_AWAITING_EX : NBILL_AWAITING_IN); ?></a></td>
                    </tr>
                    <?php
                }
                break;
        }
    }
}