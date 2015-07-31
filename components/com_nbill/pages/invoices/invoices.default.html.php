<?php
/**
* Default HTML output template for invoice processing pages
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nbill_fe_invoices
{
    public static function show_invoice_list($rows, $first_product_description, $date_format, $orders)
    {
        $pay_invoice_col = false;
        $outstanding_col = false;
        ?>
        <div class="nbill-invoice-list-header">
            <?php
            
            self::show_pathway(); ?>
        </div>
        <div class="nbill-invoice-list"><br />
            <?php
            self::_show_filters();
            ?>
            <table width="100%" class="contentpane category" id="nbill-invoice-list-table" cellpadding="3" cellspacing="1" border="0" style="margin-top:5px;">
                <tr class="jlist-table nbill_list_tr_headings nbill_invoices">
                    <?php
                    self::render_column_header($rows, "invoice_no", NBILL_FE_INVOICE_NUMBER, "white-space:nowrap;");
                    self::render_column_header($rows, "document_date", NBILL_FE_INVOICE_DATE);
                    $due_date_present = false;
                    foreach ($rows as $row) {
                        if (@$row->due_date) {
                            $due_date_present = true;
                            break;
                        }
                    }
                    if (nbf_frontend::get_display_option("due_date") || (nbf_frontend::get_display_option("due_date_on_list") && $due_date_present)) {
                        self::render_column_header($rows, "due_date", NBILL_FE_DUE_DATE, "", "due_date_on_list");
                    }
                    self::render_column_header($rows, "first_item", NBILL_FE_FIRST_ITEM);
                    self::render_column_header($rows, "net", NBILL_FE_TOTAL_NET, "text-align:right;white-space:nowrap;");
                    self::render_column_header($rows, "gross", NBILL_FE_TOTAL_GROSS, "text-align:right;white-space:nowrap;");

                    foreach ($rows as $row)
                    {
                        if ($row->total_outstanding->value > 0 && !float_cmp($row->total_gross->value, $row->total_outstanding->value))
                        {
                            self::render_column_header($rows, "outstanding", NBILL_FE_TOTAL_OUTSTANDING, "text-align:right;");
                            $outstanding_col = true;
                            break;
                        }
                    }
                    if (!$outstanding_col)
                    {
                        //Render any custom columns that come after this one anyway
                        if (file_exists(dirname(__FILE__) . "/custom_columns/after_outstanding.php"))
                        {
                            include_once(dirname(__FILE__) . "/custom_columns/after_outstanding.php");
                            if (is_callable(array("nbill_invoice_after_outstanding", 'render_header')))
                            {
                                call_user_func(array("nbill_invoice_after_outstanding", 'render_header'), $rows);
                            }
                        }
                    }
                    self::render_column_header($rows, "status", NBILL_INVOICE_STATUS);
                    foreach ($rows as $row)
                    {
                        if (nbf_common::check_show_paylink($row))
                        {
                            self::render_column_header($rows, "payment_link", NBILL_PAY_INVOICE_TITLE);
                            $pay_invoice_col = true;
                            break;
                        }
                    }
                    if (!$pay_invoice_col)
                    {
                        //Render any custom columns that come after this one anyway
                        if (file_exists(dirname(__FILE__) . "/custom_columns/after_paylink.php"))
                        {
                            include_once(dirname(__FILE__) . "/custom_columns/after_paylink.php");
                            if (is_callable(array("nbill_invoice_after_paylink", 'render_header')))
                            {
                                call_user_func(array("nbill_invoice_after_paylink", 'render_header'), $rows);
                            }
                        }
                    }
                    ?>
                </tr>
                <?php
                $rownumber = 2;
                for ($i=0, $n=count($rows); $i < $n; $i++)
                {
                    $row = &$rows[$i];
                    $rownumber = $rownumber == 1 ? 2 : 1;
                    $link = htmlentities(nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . "&action=invoices&task=print&cid=$row->id" . nbf_cms::$interop->site_page_suffix);
                    $pdf_link = htmlentities(nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . "&action=invoices&task=pdf&cid=$row->id" . nbf_cms::$interop->site_page_suffix);
                    ?>
                    <tr class="sectiontableentry<?php echo $rownumber; ?> cat-list-row<?php echo $rownumber; ?> nbill_list_tr_value" id="tr_invoice_<?php echo $row->id; ?>">
                        <td>
                            <?php echo $row->document_no; ?>
                            <?php if (nbf_frontend::get_display_option("html_preview")) { ?>
                                <a target="_blank" href="<?php echo $link; ?>" onclick="window.open('<?php echo $link; ?>', '<?php echo uniqid(); ?>', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;"><img src="<?php echo nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_popup_page_prefix . "&action=show_image&file_name=preview.gif" . nbf_cms::$interop->site_page_suffix; ?>" alt="<?php echo NBILL_HTML_INVOICE; ?>" border="0" /></a>
                            <?php } ?>
                        <?php
                        if (nbf_frontend::get_display_option("pdf")) {
                            if (nbf_common::pdf_writer_available())
                            {?>
                                <a target="_blank" title="<?php echo NBILL_PDF_INVOICE; ?>" href="<?php echo $pdf_link; ?>" onclick="window.open('<?php echo $pdf_link; ?>', '<?php echo uniqid(); ?>', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;"><img src="<?php echo nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_popup_page_prefix . "&action=show_image&file_name=pdf.png"; ?>" alt="<?php echo NBILL_PDF_INVOICE; ?>" title="<?php echo NBILL_PDF_INVOICE; ?>" border="0" /></a>
                            <?php }
                        }?>
                        </td>
                        <?php
                        if (is_callable(array("nbill_invoice_after_invoice_no", 'render_row')))
                        {
                            call_user_func(array("nbill_invoice_after_invoice_no", 'render_row'), $row);
                        }
                        $display_option = nbf_frontend::get_display_option("document_date");
                        if ($display_option) { ?>
                            <td class="responsive-cell<?php echo nbf_frontend::get_css_class_for_option($display_option); ?>"><?php echo nbf_common::nb_date($date_format, $row->document_date);?></td>
                            <?php
                        }
                        if (is_callable(array("nbill_invoice_after_document_date", 'render_row')))
                        {
                            call_user_func(array("nbill_invoice_after_document_date", 'render_row'), $row);
                        }
                        $display_option = nbf_frontend::get_display_option("due_date_on_list");
                        if ($display_option) {
                            $due_date = $row->due_date;
                            if (!$due_date) {
                                if (nbf_frontend::get_display_option("due_date")) {
                                    $due_date = strtotime("+ " . nbf_frontend::get_display_option("due_date_no_of_units") . " " . nbf_frontend::get_display_option("due_date_units"), $row->document_date);
                                }
                            }
                            if ($due_date) {
                                ?>
                                <td class="responsive-cell<?php echo nbf_frontend::get_css_class_for_option($display_option); ?>" style="color:#f00"><?php echo nbf_common::nb_date($date_format, $due_date);?></td>
                                <?php
                            } else if ($due_date_present) {
                                ?><td class="responsive-cell<?php echo nbf_frontend::get_css_class_for_option($display_option); ?>">&nbsp;</td><?php
                            }
                        }
                        if (is_callable(array("nbill_invoice_after_due_date", 'render_row')))
                        {
                            call_user_func(array("nbill_invoice_after_due_date", 'render_row'), $row);
                        }
                        $display_option = nbf_frontend::get_display_option("first_item");
                        if ($display_option)
                        {
                            $first_desc = "";
                            $section_name = "";
                            foreach ($first_product_description as $descriptions)
                            {
                                if ($descriptions->document_id == $row->document_id)
                                {
                                    $section_name = trim($descriptions->section_name);
                                    if (strlen($first_desc) == 0) {
                                        $first_desc = $descriptions->product_description;
                                    }
                                    if (strlen($section_name)) {
                                        $first_desc = $section_name;
                                        break;
                                    }
                                }
                            }
                            ?>
                            <td class="responsive-cell<?php echo nbf_frontend::get_css_class_for_option($display_option); ?>"><?php echo $first_desc; ?></td>
                            <?php
                        }
                        if (is_callable(array("nbill_invoice_after_first_item", 'render_row')))
                        {
                            call_user_func(array("nbill_invoice_after_first_item", 'render_row'), $row);
                        }
                        $display_option = nbf_frontend::get_display_option("net");
                        if ($display_option) { ?>
                            <td class="responsive-cell<?php echo nbf_frontend::get_css_class_for_option($display_option); ?>" style="white-space:nowrap;text-align:right;"><?php echo $row->total_net;?></td>
                        <?php }
                        if (is_callable(array("nbill_invoice_after_net", 'render_row')))
                        {
                            call_user_func(array("nbill_invoice_after_net", 'render_row'), $row);
                        }
                        $display_option = nbf_frontend::get_display_option("gross");
                        if ($display_option) { ?>
                            <td class="responsive-cell<?php echo nbf_frontend::get_css_class_for_option($display_option); ?>" style="white-space:nowrap;text-align:right;"><?php echo $row->total_gross;?></td>
                        <?php }
                        if (is_callable(array("nbill_invoice_after_gross", 'render_row')))
                        {
                            call_user_func(array("nbill_invoice_after_gross", 'render_row'), $row);
                        }
                        if ($outstanding_col)
                        {
                            $display_option = nbf_frontend::get_display_option("outstanding");
                            if ($display_option) { ?>
                                <td class="responsive-cell<?php echo nbf_frontend::get_css_class_for_option($display_option); ?>" style="white-space:nowrap;text-align:right;"><?php echo $row->total_outstanding;?></td>
                                <?php
                            }
                        }
                        if (is_callable(array("nbill_invoice_after_outstanding", 'render_row')))
                        {
                            call_user_func(array("nbill_invoice_after_outstanding", 'render_row'), $row);
                        }
                        $display_option = nbf_frontend::get_display_option("status");
                        if ($display_option) { ?>
                            <td class="responsive-cell<?php echo nbf_frontend::get_css_class_for_option($display_option); ?>"><?php
                            if ($row->refunded_in_full)
                            {
                                echo NBILL_INVOICE_REFUNDED;
                            }
                            else if ($row->partial_refund)
                            {
                                echo NBILL_INVOICE_PART_REFUNDED;
                            }
                            else if ($row->paid_in_full)
                            {
                                echo NBILL_FE_INVOICE_PAID;
                            }
                            else if ($row->partial_payment)
                            {
                                echo NBILL_INVOICE_PART_PAID;
                            }
                            else
                            {
                                echo "<span style=\"color:#ff0000;\">" . NBILL_INVOICE_UNPAID . "</span>";
                            }
                            ?></td>
                        <?php }
                        if (is_callable(array("nbill_invoice_after_status", 'render_row')))
                        {
                            call_user_func(array("nbill_invoice_after_status", 'render_row'), $row);
                        }
                        $display_option = nbf_frontend::get_display_option('payment_link');
                        if (nbf_common::check_show_paylink($row))
                        {
                            ?>
                            <td class="responsive-cell<?php echo nbf_frontend::get_css_class_for_option($display_option); ?>" style="white-space:nowrap">
                                <a href="<?php echo nbf_cms::$interop->site_page_prefix; ?>&action=invoices&task=pay&invoice_id=<?php echo $row->id; echo nbf_cms::$interop->site_page_suffix ?>"><?php echo NBILL_PAY_INVOICE; ?></a>
                            </td>
                            <?php
                        }
                        else if ($pay_invoice_col)
                        {
                            echo "<td class=\"responsive-cell" . nbf_frontend::get_css_class_for_option($display_option) . "\">&nbsp;</td>";  //One of the other invoices has a payment link, so we still need a table cell.
                        }
                        if (is_callable(array("nbill_invoice_after_paylink", 'render_row')))
                        {
                            call_user_func(array("nbill_invoice_after_paylink", 'render_row'), $row);
                        }
                        ?>
                    </tr>
                <?php } ?>
            </table>
        </div>
        <?php
    }

    public static function render_column_header($rows, $column_name, $header_text, $custom_style = "", $display_option_name = "")
    {
        if (!$display_option_name) {
            $display_option_name = $column_name;
        }
        $display_option = nbf_frontend::get_display_option($display_option_name);
        if ($display_option)
        {?>
            <th class="sectiontableheader responsive-cell<?php echo nbf_frontend::get_css_class_for_option($display_option); ?>"<?php echo $custom_style ? ' style="' . $custom_style . '"' : ''; ?>><?php echo $header_text; ?></th>
        <?php }
        if (file_exists(dirname(__FILE__) . "/custom_columns/after_$column_name.php"))
        {
            include_once(dirname(__FILE__) . "/custom_columns/after_$column_name.php");
            if (is_callable(array("nbill_invoice_after_$column_name", 'render_header')))
            {
                call_user_func(array("nbill_invoice_after_$column_name", 'render_header'), $rows);
            }
        }
    }

    public static function show_invoice_payment_summary($select_gateway, $gateways, $default_gateway, $invoice_details, $invoice_summary_total, $voucher_available = false)
    {
        ?>
        <table cellpadding="3" cellspacing="0" border="0" class="nbill_summary_table nbill_invoice_payment">
            <tr class="nbill_invoice_payment_intro">
                <th colspan="2" class="nbill_summary_sub_heading"><?php echo NBILL_SUMMARY_INVOICE_DETAILS; ?></th>
            </tr>
            <tr class="nbill_invoice_payment_row invoice_payment_number">
                <td class="field-title"><?php echo NBILL_FE_INVOICE_NUMBER; ?></td>
                <td class="field-value">
                <?php
                    $invoice_nos = "";
                    foreach ($invoice_details as $key=>$value)
                    {
                        if (nbf_common::nb_strlen($invoice_nos) > 0)
                        {
                            $invoice_nos .= ", ";
                        }
                        $invoice_nos .= $key;
                    }
                    echo $invoice_nos;
                ?>
                </td>
            </tr>
            <?php
            echo $invoice_summary_total; //This has been rendered by the summary table field control
            if ($select_gateway)
            {
                if (count($gateways) > 1)
                {
                    //Allow selection of payment gateway
                    ?>
                    <tr class="nbill_invoice_payment_row invoice_payment_gateway">
                        <td><?php echo NBILL_SELECT_GATEWAY; ?></td>
                        <td align="right" class="nbill-gateway-select">
                            <?php
                            $gateway_list = array();
                            $resubmit_on_select = false;
                            foreach($gateways as $gateway)
                            {
                                $gateway_list[] = nbf_html::list_option($gateway->code, $gateway->description);
                                if (@$gateway->fee_or_discount !== null)
                                {
                                    $resubmit_on_select = true;
                                }
                            }
                            echo nbf_html::select_list($gateway_list, "payment_gateway", 'id="payment_gateway" class="inputbox"' . ($resubmit_on_select ? ' onchange="document.select_gateway.submit();"' : ''), $default_gateway);
                            ?>
                        </td>
                    </tr>
                    <?php
                }
            }
            if ($voucher_available)
            {
                ?>
                <tr class="nbill_invoice_payment_row invoice_payment_discount">
                    <td><?php echo NBILL_FE_DOC_DISCOUNT_VOUCHER; ?></td>
                    <td>
                        <input type="text" name="nbill_document_voucher_code" id="nbill_document_voucher_code" value="" />
                        <input type="submit" class="button btn nbill-button" name="nbill_apply_voucher" value="<?php echo NBILL_FE_DOC_DISCOUNT_APPLY; ?>" />
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr class="nbill_invoice_payment_row invoice_payment_submit">
                <td colspan="2" align="right" class="nbill-invoice_payment-submit" style="text-align:right">
                    <?php
                    if (!$voucher_available)
                    {
                        ?>
                        <input type="hidden" name="nbill_document_voucher_code" value="<?php echo nbf_common::get_param($_REQUEST, 'nbill_document_voucher_code'); ?>" />
                        <?php
                    }
                    ?>
                    <input type="submit" class="button btn nbill-button" name="nbill_submit_invoice_payment_summary" id="nbill_submit_invoice_payment_summary" value="<?php echo NBILL_SUBMIT; ?>" />
                </td>
            </tr>
        </table>
        <?php
    }

    public static function invoice_already_paid($document_id, $document_no)
    {
        $url = nbf_cms::$interop->process_url(nbf_cms::$interop->site_page_prefix . "&action=invoices&task=print&cid=$document_id" . nbf_cms::$interop->site_page_suffix);
        $link = "<a target=\"_blank\" href=\"$url\" onclick=\"window.open('$url', '" . nbf_common::nb_time() . "', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;\">$document_no</a>";
        ?>
        <div class="nbill-message"><?php echo sprintf(NBILL_INVOICE_ALREADY_PAID, $link); ?></div>
        <p class="nbill_invoice_already_paid"><a href="<?php echo nbf_cms::$interop->process_url(nbf_cms::$interop->site_page_prefix . "&action=invoices&task=view" . nbf_cms::$interop->site_page_suffix); ?>"><?php echo NBILL_CLICK_HERE; ?></a><?php echo sprintf(NBILL_RETURN_TO_MY_INVOICES, NBILL_MY_INVOICES); ?></p>
        <?php
    }

    /**
    * If there is a message to display, display it (typically where the user attempts to pay an invoice which they cannot pay - eg. if there is already an installment-based payment plan in force)
    * @param mixed $message
    */
    public static function show_message($message)
    {
        if (nbf_common::nb_strlen($message) > 0)
        {
            echo $message;
        }
        else
        {
            if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
            {
                ?><div class="nbill-message"><?php echo nbf_globals::$message; ?></div><?php
            }
        }
    }

    public static function show_pathway($paying_invoice = false)
    {
        if (nbf_frontend::get_display_option("pathway"))
        { ?>
            <div class="pathway" style="margin-bottom:7px;"><a href="<?php
            $main = nbf_cms::$interop->process_url(nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . nbf_cms::$interop->site_page_suffix);
            echo $main; ?>"><?php echo NBILL_MAIN_MENU; ?></a> &gt; <?php
            if ($paying_invoice)
            {
                ?><a href="<?php echo nbf_cms::$interop->process_url(nbf_cms::$interop->site_page_prefix . "&action=invoices&task=view" . nbf_cms::$interop->site_page_suffix); ?>"><?php
            }
            echo NBILL_MY_INVOICES; ?>
            <?php
            if ($paying_invoice)
            {
                ?></a> &gt; <?php echo NBILL_INVOICE_PAY_NOW;
            }
            ?></div><?php
        }
    }

    

    protected static function _show_filters()
    {
        if (nbf_frontend::get_display_option("invoice_date_range"))
        {
            echo "&nbsp;&nbsp;" . NBILL_DATE_RANGE; $cal_date_format = nbf_common::get_date_format(true); ?>
            <span style="white-space:nowrap"><input type="text" id="search_date_from" class="calendar-textbox" name="search_date_from" size="10" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_from'); ?>" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> />
            <input type="button" id="search_date_from_cal" name="search_date_from_cal" class="calendar-button button btn" value="..." onclick="displayCalendar(document.invoices.search_date_from,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> /></span>
            <?php echo NBILL_TO; ?>
            <span style="white-space:nowrap"><input type="text" id="search_date_to" class="calendar-textbox" name="search_date_to" size="10" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_to'); ?>" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> />
            <input type="button" id="search_date_to_cal" name="search_date_to_cal" class="calendar-button button btn" value="..." onclick="displayCalendar(document.invoices.search_date_to,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> /></span>

            <input type="submit" id="dosearch" name="dosearch" class="button btn nbill-button" value="<?php echo NBILL_GO; ?>" />
            <?php
        }
    }
}