<?php
/**
* HTML output for income feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillIncome
{
    protected static $custom_column_count = 0;

	public static function showIncome($rows, $pagination, $vendors, $document_nos, $date_format, $attachments = array())
	{
        $vendor_col = false;
		nbf_html::load_calendar();
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "income"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_INCOME_TITLE; ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		} ?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="income" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
        <input type="hidden" name="attachment_id" value="" />

		<p align="left"><?php echo NBILL_INCOME_INTRO; ?></p>

		<?php
		//Display filter dropdown if multi-company
        echo "<p align=\"left\">";
		if (count($vendors) > 1)
		{
			echo NBILL_VENDOR_NAME . "&nbsp;";
			$selected_filter = $vendors[0]->id;
			if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'vendor_filter')) > 0)
			{
				$selected_filter = nbf_common::get_param($_POST, 'vendor_filter');
			}
			$vendor_name = array();
			$vendor_name[] = nbf_html::list_option(-999, NBILL_ALL);
			foreach ($vendors as $vendor)
			{
				$vendor_name[] = nbf_html::list_option($vendor->id, $vendor->vendor_name);
			}
			echo nbf_html::select_list($vendor_name, "vendor_filter", 'id="vendor_filter" class="inputbox" onchange="document.adminForm.submit();"', $selected_filter );
		}
		else
		{
			echo "<input type=\"hidden\" name=\"vendor_filter\" id=\"vendor_filter\" value=\"" . $vendors[0]->id . "\" />";
			$_POST['vendor_filter'] = $vendors[0]->id;
		}
		?>
        &nbsp;&nbsp; <span style="white-space:nowrap"><?php echo NBILL_INCOME_RECEIPT_NO; ?>&nbsp;<input type="text" id="receipt_no_search" name="rct_no_search" value="<?php echo nbf_common::get_param($_REQUEST,'rct_no_search', '', true); ?>" /></span>
		&nbsp;&nbsp; <span style="white-space:nowrap"><?php echo NBILL_RECEIVED_FROM;?>&nbsp;<input type="text" name="name_search" value="<?php echo nbf_common::get_param($_REQUEST,'name_search', '', true); ?>" /></span>
		&nbsp;&nbsp; <span style="white-space:nowrap"><?php echo NBILL_INCOME_AMOUNT;?>&nbsp;<input type="text" name="rct_amount_search" value="<?php echo nbf_common::get_param($_REQUEST,'rct_amount_search', '', true); ?>" /></span>
		<br /><span style="white-space:nowrap"><?php echo NBILL_DATE_RANGE; $cal_date_format = nbf_common::get_date_format(true); ?>
		<input type="text" name="search_date_from" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_from'); ?>" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> />
		<input type="button" name="search_date_from_cal" class="btn button nbill-button" value="..." onclick="displayCalendar(document.adminForm.search_date_from,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> /></span>
		<span style="white-space:nowrap"><?php echo NBILL_TO; ?>
		<input type="text" name="search_date_to" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_to'); ?>" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> />
		<input type="button" name="search_date_to_cal" class="btn button nbill-button" value="..." onclick="displayCalendar(document.adminForm.search_date_to,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> /></span>
		<input type="submit" name="dosearch" class="btn button nbill-button" value="<?php echo NBILL_GO; ?>" />
		<?php echo "</p>"; ?>

        <div class="rounded-table">
            <table class="adminlist">
            <tr class="nbill-admin-title-row">
                <th class="selector">
			    #
			    </th>
                <th class="selector">
                    <input type="checkbox" name="check_all" value="" onclick="for(var i=0; i<<?php echo count($rows); ?>;i++) {document.getElementById('cb' + i).checked=this.checked;} document.adminForm.box_checked.value=this.checked;" />
			    </th>
                <?php self::renderCustomColumn('id'); ?>
			    <th class="title">
				    <?php echo NBILL_INCOME_RECEIPT_NO; ?>
			    </th>
                <?php self::renderCustomColumn('receipt_no'); ?>
			    <th class="title">
				    <?php echo NBILL_INCOME_DATE; ?>
			    </th>
                <?php self::renderCustomColumn('date'); ?>
			    <th class="title" style="text-align:right;">
                    <?php echo NBILL_INCOME_AMOUNT; ?>
                </th>
                <?php self::renderCustomColumn('amount'); ?>
                <th class="title">
				    <?php echo NBILL_RECEIVED_FROM; ?>
			    </th>
                <?php self::renderCustomColumn('from'); ?>
			    <th class="title responsive-cell optional">
				    <?php echo NBILL_INCOME_INVOICE_NO; ?>
			    </th>
                <?php self::renderCustomColumn('invoice_no'); ?>
			    <?php
				    //Only show vendor name if more than one listed
				    if (count($vendors) > 1 && $selected_filter == -999)
				    {?>
					    <th class="title responsive-cell optional">
						    <?php echo NBILL_VENDOR_NAME; ?>
					    </th>
				        <?php
                        $vendor_col = true;
                    }
			    ?>
                <?php self::renderCustomColumn('vendor'); ?>
		    </tr>
		    <?php
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];
				    $link = nbf_cms::$interop->admin_page_prefix . "&action=income&task=edit&cid=$row->id&search_date_from=" . nbf_common::get_param($_REQUEST, 'search_date_from') . "&search_date_to=" . nbf_common::get_param($_REQUEST, 'search_date_to') . "&rct_no_search=" . nbf_common::get_param($_REQUEST, 'rct_no_search') . "&name_search=" . nbf_common::get_param($_REQUEST, 'name_search') . "&rct_amount_search=" . nbf_common::get_param($_REQUEST, 'rct_amount_search');
				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\">$checked</td>";
                    self::renderCustomColumn('id', $row);
				    $transaction_no = $row->transaction_no;
				    if (nbf_common::nb_strlen($transaction_no) == 0)
				    {
					    $transaction_no = NBILL_UNNUMBERED;
				    }
				    echo "<td class=\"list-value\"><div style=\"float:left\">";
                    echo "<a href=\"$link\" title=\"" . NBILL_EDIT_INCOME . "\">" . $transaction_no . "</a>";
                    echo "&nbsp;<a href=\"#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=" . nbf_common::get_param($_REQUEST, 'action') . "&cid=" . $row->id . "&task=printer_friendly&hidemainmenu=1&hide_billing_menu=1', '" . uniqid() . "', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;\">";
                    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/preview.gif\" alt=\"" . NBILL_INCOME_PRINTER_FRIENDLY . "\" border=\"0\" />";
                    echo "</div>";
                    if (file_exists(nbf_cms::$interop->nbill_admin_base_path . '/admin.proc/supporting_docs.php')) {
                    ?>
                    <div style="float:right"><a href="#" onclick="<?php if ($row->attachment_count){ ?>var att=document.getElementById('attachments_<?php echo $row->id; ?>');if(att.style.display=='none'){att.style.display='';}else{att.style.display='none';}<?php }else{ ?>window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&use_stylesheet=1&show_toolbar=1&attach_to_type=IN&attach_to_id=<?php echo $row->id; ?>','','scrollbars=1,width=790,height=500');<?php } ?>return false;"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/supporting_docs.gif" alt="<?php echo NBILL_ATTACHMENTS; ?>" style="vertical-align:middle;" /><?php if ($row->attachment_count) {echo " (" . $row->attachment_count . ")";} ?></a></div>
                    <div id="attachments_<?php echo $row->id; ?>" style="display:none;text-align:right;clear:both;">
                        <table cellpadding="3" cellspacing="0" border="0" style="margin-left:auto;margin-right:0px;">
                        <?php
                        foreach ($attachments as $attachment)
                        {
                            if ($attachment->associated_doc_id == $row->id)
                            {
                                ?>
                                <tr>
                                <td>
                                    <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=supporting_docs&task=download&file=<?php echo base64_encode($attachment->id); ?>"><img style="vertical-align:middle" border="0" alt="" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file.png" />&nbsp;<?php echo $attachment->file_name; ?></a>
                                </td>
                                <td>
                                    <input type="button" class="button btn" value="<?php echo NBILL_DETACH; ?>" onclick="if(confirm('<?php echo NBILL_DETACH_SURE; ?>')){document.adminForm.attachment_id.value='<?php echo $attachment->id; ?>';document.adminForm.task.value='detach_file';document.adminForm.submit();}" />
                                </td>
                                <td>
                                    <input type="button" class="button btn" value="<?php echo NBILL_DELETE; ?>" onclick="if(confirm('<?php echo sprintf(NBILL_DELETE_FILE_SURE, $attachment->file_name); ?>')){document.adminForm.attachment_id.value='<?php echo $attachment->id; ?>';document.adminForm.task.value='delete_file';document.adminForm.submit();}" />
                                </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <tr><td colspan="3">
                        <a href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&use_stylesheet=1&show_toolbar=1&attach_to_type=IN&attach_to_id=<?php echo $row->id; ?>','','scrollbars=1,width=790,height=500');return false;"><img style="vertical-align:middle" border="0" alt="" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/supporting_docs.gif" />&nbsp;<?php echo NBILL_NEW_ATTACHMENT; ?></a>
                        </td></tr>
                        </table>
                    </div>
                    <?php
                    }
                    echo "</td>";
                    self::renderCustomColumn('receipt_no', $row);
				    echo "<td class=\"list-value\">" . nbf_common::nb_date($date_format, $row->date) . "</td>";
                    self::renderCustomColumn('date', $row);
				    echo "<td class=\"list-value\" style=\"text-align:right;\">" . format_number($row->amount, 'currency_grand', null, null, null, $row->currency) . "</td>";
                    self::renderCustomColumn('amount', $row);
                    echo "<td class=\"list-value word-breakable\">" . $row->name . "</td>";
				    $this_document_ids = explode(",", $row->document_ids);
				    if (count($this_document_ids) > 0)
				    {
					    $invoice_count = 0;
					    echo "<td class=\"responsive-cell optional\">";
					    foreach ($this_document_ids as $this_document_id)
					    {
						    $this_document_nos = array();
						    foreach ($document_nos as $document_no)
						    {
							    if ($document_no->id == $this_document_id)
							    {
								    $invoice_count++;
								    echo "<a href=\"#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=invoices&task=printpreviewpopup&hidemainmenu=1&items=" . $document_no->id . "', " . nbf_common::nb_time() . ", 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;\">" . $document_no->document_no . "</a> ";
							    }
						    }
					    }
					    if ($invoice_count == 0)
					    {
						    echo NBILL_NO_INVOICE_NO;
					    }
					    echo "</td>";
				    }
				    else
				    {
					    echo "<td class=\"list-value responsive-cell optional\">" . NBILL_NO_INVOICE_NO . "</td>";
				    }
                    self::renderCustomColumn('invoice_no', $row);

				    //Only show vendor name if more than one listed
				    $vendor_col = false;
				    if (count($vendors) > 1 && $selected_filter == -999)
				    {
					    foreach ($vendors as $vendor)
					    {
						    if ($vendor->id == $row->vendor_id)
						    {
							    echo "<td class=\"list-value responsive-cell optional\">" . $vendor->vendor_name . "</td>";
							    $vendor_col = true;
							    break;
						    }
					    }
				    }
                    self::renderCustomColumn('vendor', $row);
				    echo "</tr>";
			    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="<?php echo ($vendor_col ? 8 : 7) + self::$custom_column_count; ?>" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		</form>
		<?php
	}

    protected static function renderCustomColumn($column_name, $row = 'undefined')
    {
        $method = ($row == 'undefined') ? 'render_header' : 'render_row';
        if (file_exists(dirname(__FILE__) . "/custom_columns/income/after_$column_name.php")) {
            include_once(dirname(__FILE__) . "/custom_columns/income/after_$column_name.php");
            if (is_callable(array("nbill_admin_income_after_$column_name", $method))) {
                call_user_func(array("nbill_admin_income_after_$column_name", $method), $row);
                if ($method == 'render_header') {
                    self::$custom_column_count++;
                }
            }
        }
    }

	public static function showPrinterFriendly($row, $documents, $vendor_name, $vendor_address, $title_colour, $heading_bg_colour, $heading_fg_colour)
    {
        //Forget the CMS admin template
        $loopbreaker = 0;
        while (ob_get_length() !== false)
        {
            $loopbreaker++;
            @ob_end_clean();
            if ($loopbreaker > 15)
            {
                break;
            }
        }
        include(nbf_cms::$interop->nbill_admin_base_path . "/admin.html/receipt.template.html.php");
        exit;
    }

    public static function editIncome($transaction_id, $row, $vendors, $invoices, $shipping, $pay_methods, $countries, $currencies, $ledger, $ledger_breakdown, $use_posted_values, $attachments = array())
	{
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		}
		nbf_html::load_calendar();
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.client.php");
        $config = nBillConfigurationService::getInstance()->getConfig();
		?>
		<script language="javascript" type="text/javascript">
		<?php nbf_html::add_js_validation_numeric();
		nbf_html::add_js_validation_date(); ?>

        function ledger_price_update(ledger_key, gross_to_net)
        {
            if (do_ledger_calc())
            {
                var net_amount = 0;
                var tax_rate = 0;
                var tax_amount = 0;
                var gross_amount = 0;
                if (document.getElementById('ledger_tax_' + ledger_key + '_rate'))
                {
                    tax_rate = document.getElementById('ledger_tax_' + ledger_key + '_rate').value;
                }

                if (tax_rate != 0)
                {
                    //Tax rate is populated - let's work it out tax amount either from the net or gross, whichever is available
                    if (document.getElementById('ledger_tax_' + ledger_key + '_amount'))
                    {
                        tax_amount = document.getElementById('ledger_tax_' + ledger_key + '_amount').value;
                    }
                    if (document.getElementById('ledger_net_' + ledger_key + '_amount'))
                    {
                        net_amount = document.getElementById('ledger_net_' + ledger_key + '_amount').value;
                    }
                    if (document.getElementById('ledger_gross_' + ledger_key + '_amount'))
                    {
                        gross_amount = document.getElementById('ledger_gross_' + ledger_key + '_amount').value;
                    }

                    if (net_amount != 0 && !gross_to_net)
                    {
                        //Net is populated, so calculate tax and gross from net
                        tax_amount = format_currency((net_amount / 100) * tax_rate, <?php echo $config->precision_currency_line_total; ?>);
                        gross_amount = format_currency((net_amount * 1) + (tax_amount * 1), <?php echo $config->precision_currency_line_total; ?>);
                        document.getElementById('ledger_tax_' + ledger_key + '_amount').value = tax_amount;
                        document.getElementById('ledger_gross_' + ledger_key + '_amount').value = gross_amount;
                    }
                    else if (gross_to_net || (gross_amount != 0 && net_amount == 0))
                    {
                        //Gross is populated, net is not, so calculate tax and net from gross
                        tax_amount = format_currency((gross_amount / (100 + (tax_rate * 1))) * tax_rate, <?php echo $config->precision_currency_line_total; ?>);
                        net_amount = format_currency((gross_amount * 1) - (tax_amount * 1), <?php echo $config->precision_currency_line_total; ?>);
                        document.getElementById('ledger_tax_' + ledger_key + '_amount').value = tax_amount;
                        document.getElementById('ledger_net_' + ledger_key + '_amount').value = net_amount;
                    }
                }
            }
        }

        function calculator_toggle()
        {
            var calc_link = document.getElementById('calc_toggle');
            if (calc_link)
            {
                if (calc_link.innerHTML == '<?php echo NBILL_TX_CALC_OFF; ?>')
                {
                    calc_link.innerHTML = '<?php echo NBILL_TX_CALC_ON; ?>';
                }
                else
                {
                    calc_link.innerHTML = '<?php echo NBILL_TX_CALC_OFF; ?>';
                }
            }
        }

        function do_ledger_calc()
        {
            var calc_link = document.getElementById('calc_toggle');
            return calc_link.innerHTML == '<?php echo NBILL_TX_CALC_OFF; ?>';
        }

		<?php
		$cal_date_format = nbf_common::get_date_format(true);
        $ledger_table_heading = '<div class="rounded-table" style="display:inline-block;"><table cellpadding="1" cellspacing="0" border="0"><tr>';
        $ledger_table_heading .= '<th>' . NBILL_NOMINAL_LEDGER_CODE . '</th>';
        $ledger_table_heading .= '<th>' . NBILL_INCOME_LEDGER_NET_AMOUNT . '</th>';
        $ledger_table_heading .= '<th>' . NBILL_INCOME_LEDGER_TAX_AMOUNT . '</th>';
        $ledger_table_heading .= '<th>' . NBILL_INCOME_LEDGER_GROSS_AMOUNT . '</th>';
        $ledger_table_heading .= '</tr>';
        $ledger_table_rows = "";
        foreach ($ledger_breakdown as $ledger_entry)
        {
            if (array_search($ledger_entry->id, explode(",", nbf_common::get_param($_POST,'removed_items'))) === false)
            {
                $ledger_table_rows .= '<tr><td>';
                foreach ($vendors as $vendor)
                {
                    $ledger_list = array();
                    $ledger_list[] = nbf_html::list_option("-1", "-1 - " . NBILL_MISCELLANEOUS);
                    foreach ($ledger[$vendor->id] as $ledger_item)
                    {
                        if ($ledger_item->vendor_id == $vendor->id)
                        {
                            if ($ledger_item->code != -1 && $ledger_item->description != NBILL_MISCELLANEOUS)
                            {
                                $ledger_list[] = nbf_html::list_option($ledger_item->code, $ledger_item->code . " - " . $ledger_item->description);
                            }
                        }
                    }
                    if($row->id)
                    {
                        $selected_ledger = $ledger_entry->nominal_ledger_code;
                    }
                    else
                    {
                        $selected_ledger = '';
                    }
                    $ledger_table_rows .= nbf_html::select_list($ledger_list, "ledger_" . $ledger_entry->id . "_" . $vendor->id, 'class="inputbox squashable" id="ledger_' . $ledger_entry->id . "_" . $vendor->id . '"', $selected_ledger) . " ";
                }
                $ledger_table_rows .= "</td><td><input type=\"text\" name=\"ledger_net_$ledger_entry->id" . "_amount\" id=\"ledger_net_$ledger_entry->id" . "_amount\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_net_$ledger_entry->id" . "_amount"), 'currency_line') : format_number($ledger_entry->net_amount, 'currency_line')) . "\" onchange=\"ledger_price_update('" . $ledger_entry->id . "', false);\" />";
                $ledger_table_rows .= "</td><td><table border=\"0\" class=\"borderless\"><tr><td>" . NBILL_INCOME_TAX_RATE . ":</td><td><input type=\"text\" name=\"ledger_tax_$ledger_entry->id" . "_rate\" id=\"ledger_tax_$ledger_entry->id" . "_rate\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_tax_$ledger_entry->id" . "_rate"), 'tax_rate') : format_number($ledger_entry->tax_rate, 'tax_rate')) . "\" onchange onchange=\"ledger_price_update('" . $ledger_entry->id . "', false);\" /></td></tr>";
                $ledger_table_rows .= "<tr><td>" . NBILL_INCOME_TAX_AMOUNT . ":</td><td><input type=\"text\" name=\"ledger_tax_$ledger_entry->id" . "_amount\" id=\"ledger_tax_$ledger_entry->id" . "_amount\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_tax_$ledger_entry->id" . "_amount"), 'currency_line') : format_number($ledger_entry->tax_amount, 'currency_line')) . "\" /></td></tr></table>";
                $ledger_table_rows .= "</td><td><input type=\"text\" name=\"ledger_gross_$ledger_entry->id" . "_amount\" id=\"ledger_gross_$ledger_entry->id" . "_amount\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_gross_$ledger_entry->id" . "_amount"), 'currency_line') : format_number($ledger_entry->gross_amount, 'currency_line')) . "\" onchange=\"ledger_price_update('" . $ledger_entry->id . "', true);\" /><br /><input type=\"hidden\" name=\"delete_ledger_entry_" . $ledger_entry->id . "\" id=\"delete_ledger_entry_" . $ledger_entry->id . "\" value=\"\" /><input type=\"submit\" class=\"button btn\" name=\"btn_delete_ledger_entry_" . $ledger_entry->id . "\" id=\"btn_delete_ledger_entry_" . $ledger_entry->id . "\" value=\"" . NBILL_REMOVE_INVOICE_ITEM . "\" onclick=\"document.getElementById('delete_ledger_entry_" . $ledger_entry->id . "').value='1';nbill_submit_task('remove_ledger_item');\" />";
                $ledger_table_rows .= "</td></tr>";
            }
        }

        //Added items
        $added_items = array();
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'added_items')) > 0)
        {
            $added_items = explode(",", nbf_common::get_param($_POST,'added_items'));
        }
        if (count($added_items) > 0)
        {
            foreach ($added_items as $added_item)
            {
                if (array_search("new_$added_item", explode(",", nbf_common::get_param($_POST,'removed_items'))) === false)
                {
                    $ledger_table_rows .= "<tr><td>";
                    foreach ($vendors as $vendor)
                    {
                        $ledger_list = array();
                        $ledger_list[] = nbf_html::list_option("-1", "-1 - " . NBILL_MISCELLANEOUS);
                        foreach ($ledger[$vendor->id] as $ledger_item)
                        {
                            if ($ledger_item->vendor_id == $vendor->id)
                            {
                                if ($ledger_item->code != -1 && $ledger_item->description != NBILL_MISCELLANEOUS)
                                {
                                    $ledger_list[] = nbf_html::list_option($ledger_item->code, $ledger_item->code . " - " . $ledger_item->description);
                                }
                            }
                        }
                        $selected_ledger = nbf_common::get_param($_POST,"ledger_new_" . $added_item . "_" . $vendor->id);
                        $ledger_table_rows .= nbf_html::select_list($ledger_list, "ledger_new_" . $added_item . "_" . $vendor->id, 'class="inputbox squashable" id="ledger_new_' . $added_item . "_" . $vendor->id . '"', $selected_ledger) . " ";
                    }
                    $ledger_table_rows .= "</td><td><input type=\"text\" name=\"ledger_net_new_$added_item" . "_amount\" id=\"ledger_net_new_$added_item" . "_amount\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_net_new_$added_item" . "_amount"), 'currency_line') : format_number(nbf_common::get_param($_POST,"ledger_net_new_$added_item" . "_amount"), 'currency_line')) . "\" onchange=\"ledger_price_update('new_" . $added_item . "', false);\" />";
                    $ledger_table_rows .= "</td><td><table border=\"0\" class=\"borderless\"><tr><td>" . NBILL_INCOME_TAX_RATE . ":</td><td><input type=\"text\" name=\"ledger_tax_new_$added_item" . "_rate\" id=\"ledger_tax_new_$added_item" . "_rate\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_tax_new_$added_item" . "_rate"), 'tax_rate') : format_number(nbf_common::get_param($_POST,"ledger_tax_new_$added_item" . "_rate"), 'tax_rate')) . "\" onchange=\"ledger_price_update('new_" . $added_item . "', false);\" /></td></tr>";
                    $ledger_table_rows .= "<tr><td>" . NBILL_INCOME_TAX_AMOUNT . ":</td><td><input type=\"text\" name=\"ledger_tax_new_$added_item" . "_amount\" id=\"ledger_tax_new_$added_item" . "_amount\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_tax_new_$added_item" . "_amount"), 'currency_line') : format_number(nbf_common::get_param($_POST,"ledger_tax_new_$added_item" . "_amount"), 'currency_line')) . "\" /></td></tr></table>";
                    $ledger_table_rows .= "</td><td><input type=\"text\" name=\"ledger_gross_new_$added_item" . "_amount\" id=\"ledger_gross_new_$added_item" . "_amount\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_gross_new_$added_item" . "_amount"), 'currency_line') : format_number(nbf_common::get_param($_POST,"ledger_gross_new_$added_item" . "_amount"), 'currency_line')) . "\" onchange=\"ledger_price_update('new_" . $added_item . "', true);\" /><br /><input type=\"hidden\" name=\"delete_ledger_entry_new_" . $added_item . "\" id=\"delete_ledger_entry_new_" . $added_item . "\" value=\"\" /><input type=\"submit\" class=\"button btn\" name=\"btn_delete_ledger_entry_new_" . $added_item . "\" id=\"btn_delete_ledger_entry_new_" . $added_item . "\" value=\"" . NBILL_REMOVE_INVOICE_ITEM . "\" onclick=\"document.getElementById('delete_ledger_entry_new_" . $added_item . "').value='1';nbill_submit_task('remove_ledger_item');\" />";
                    $ledger_table_rows .= "</td></tr>";
                }
            }
        }

        //New Entry
        $ledger_table_new = "<tr><td>";
        foreach ($vendors as $vendor)
        {
            $ledger_list = array();
            $ledger_list[] = nbf_html::list_option("-1", "-1 - " . NBILL_MISCELLANEOUS);
            foreach ($ledger[$vendor->id] as $ledger_item)
            {
                if ($ledger_item->vendor_id == $vendor->id)
                {
                    if ($ledger_item->code != -1 && $ledger_item->description != NBILL_MISCELLANEOUS)
                    {
                        $ledger_list[] = nbf_html::list_option($ledger_item->code, $ledger_item->code . " - " . $ledger_item->description);
                    }
                }
            }
            $selected_ledger = '';
            $ledger_table_new .= nbf_html::select_list($ledger_list, "ledger_new_" . $vendor->id, 'class="inputbox squashable" id="ledger_new_' . $vendor->id . '"', '');
        }
        $ledger_table_new .= "</td><td><input type=\"text\" name=\"ledger_net_new_amount\" id=\"ledger_net_new_amount\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_net_new_amount"), 'currency_line') : "") . "\" onchange=\"ledger_price_update('new', false);\" />";
        $ledger_table_new .= "</td><td><table border=\"0\" class=\"borderless\"><tr><td>" . NBILL_INCOME_TAX_RATE . ":</td><td><input type=\"text\" name=\"ledger_tax_new_rate\" id=\"ledger_tax_new_rate\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_tax_new_rate"), 'tax_rate') : "") . "\" onchange=\"ledger_price_update('new', false);\" /></td></tr>";
        $ledger_table_new .= "<tr><td>" . NBILL_INCOME_TAX_AMOUNT . ":</td><td><input type=\"text\" name=\"ledger_tax_new_amount\" id=\"ledger_tax_new_amount\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_tax_new_amount"), 'currency_line') : "") . "\" /></td></tr></table>";
        $ledger_table_new .= "</td><td><input type=\"text\" name=\"ledger_gross_new_amount\" id=\"ledger_gross_new_amount\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_gross_new_amount"), 'currency_line') : "") . "\" onchange=\"ledger_price_update('new', true);\" /><br /><input type=\"submit\" class=\"button btn\" name=\"add_ledger_entry\" id=\"add_ledger_entry\" value=\"" . NBILL_ADD_INVOICE_ITEM . "\" onclick=\"nbill_submit_task('add_ledger_item');\" />";
        $ledger_table_new .= "</td></tr>";
        $ledger_table_new .= "</table></div>";
		?>
		function nbill_submit_task(task_name)
        {
			var form = document.adminForm;
            var alerted = true;
			if (task_name != 'apply' && task_name != 'save')
            {
				document.adminForm.task.value=task_name;
                document.adminForm.submit();
				return;
			}
			else if (form.name.value == '')
			{
				alert('<?php echo NBILL_INCOME_FROM_REQUIRED; ?>');
			}
			else if (!IsValidDate(form.date.value, false))
			{
				alert('<?php echo sprintf(NBILL_INVALID_DATE_FIELD, NBILL_INCOME_DATE, $cal_date_format); ?>');
			}
			else if (!IsNumeric(form.amount.value, false))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_AMOUNT_RECEIVED); ?>');
			}
			else if (!IsNumeric(form.tax_rate_1.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_INCOME_TAX_RATE); ?>');
			}
			else if (!IsNumeric(form.tax_amount_1.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_INCOME_TAX_AMOUNT); ?>');
			}
			else if (!IsNumeric(form.tax_rate_2.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_INCOME_TAX_RATE); ?>');
			}
			else if (!IsNumeric(form.tax_amount_2.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_INCOME_TAX_AMOUNT); ?>');
			}
			else if (!IsNumeric(form.tax_rate_3.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_INCOME_TAX_RATE); ?>');
			}
			else if (!IsNumeric(form.tax_amount_3.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_INCOME_TAX_AMOUNT); ?>');
			}
            else
            {
                alerted = false;
                <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
                if (document.getElementById('ledger_new_' + document.getElementById('vendor_id').value).options.length > 1 && document.getElementById('ledger_new_' + document.getElementById('vendor_id').value).value == '-1' && document.getElementById('ledger_gross_new_amount').value != 0)
                {
                    alerted = !confirm('<?php echo NBILL_INCOME_WARNING_DEFAULT_LEDGER; ?>');
                }
                if (!alerted)
                {
                    var inputs = document.getElementsByTagName('input');
                    var ledger_net_total = 0.00;
                    var ledger_tax_total = 0.00;
                    var ledger_gross_total = 0.00;
                    for (var input_index=0; input_index<inputs.length; input_index++)
                    {
                        if (inputs[input_index] && inputs[input_index].id && inputs[input_index].id.substr(0, 7) == 'ledger_')
                        {
                            if (!IsNumeric(inputs[input_index].value, true))
                            {
                                if (inputs[input_index].id.indexOf('_net') > -1)
                                {
                                    alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_INCOME_LEDGER_NET_AMOUNT); ?>');
                                    alerted = true;
                                    break;
                                }
                                else if (inputs[input_index].id.indexOf('_rate') > -1)
                                {
                                    alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_INCOME_LEDGER_TAX_RATE); ?>');
                                    alerted = true;
                                    break;
                                }
                                else if (inputs[input_index].id.indexOf('_tax') > -1)
                                {
                                    alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_INCOME_LEDGER_TAX_AMOUNT); ?>');
                                    alerted = true;
                                    break;
                                }
                                else if (inputs[input_index].id.indexOf('_gross') > -1)
                                {
                                    alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_INCOME_LEDGER_GROSS_AMOUNT); ?>');
                                    alerted = true;
                                    break;
                                }
                            }
                            else
                            {
                                if (inputs[input_index].id.indexOf('_net') > -1)
                                {
                                    ledger_net_total += format_currency(inputs[input_index].value.replace(/,/, ''), <?php echo $config->precision_currency_line_total; ?>) * 1;
                                }
                                else if (inputs[input_index].id.indexOf('_rate') > -1)
                                {
                                    //Don't need this one
                                }
                                else if (inputs[input_index].id.indexOf('_tax')  > -1)
                                {
                                    ledger_tax_total += format_currency(inputs[input_index].value.replace(/,/, ''), <?php echo $config->precision_currency_line_total; ?>) * 1;
                                }
                                else if (inputs[input_index].id.indexOf('_gross') > -1)
                                {
                                    ledger_gross_total += format_currency(inputs[input_index].value.replace(/,/, ''), <?php echo $config->precision_currency_line_total; ?>) * 1;
                                }
                            }
                        }
                    }
                }
                <?php } ?>
            }

			if (!alerted)
			{
                <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
				//Check that nominal ledger breakdown adds up to income total
				if (format_currency(ledger_gross_total * 1, <?php echo $config->precision_currency_grand_total; ?>) != format_currency(document.getElementById('amount').value * 1, <?php echo $config->precision_currency_grand_total; ?>)
                    || format_currency(ledger_tax_total * 1, <?php echo $config->precision_currency_grand_total; ?>, <?php echo $config->precision_currency_grand_total; ?>) != format_currency(((format_currency(document.getElementById('tax_amount_1').value * 1, <?php echo $config->precision_currency_grand_total; ?>, <?php echo $config->precision_currency_grand_total; ?>) * 1) + (format_currency(document.getElementById('tax_amount_2').value * 1, <?php echo $config->precision_currency_grand_total; ?>, <?php echo $config->precision_currency_grand_total; ?>) * 1) + (format_currency(document.getElementById('tax_amount_3').value * 1, <?php echo $config->precision_currency_grand_total; ?>, <?php echo $config->precision_currency_grand_total; ?>) * 1)) * 1, <?php echo $config->precision_currency_grand_total; ?>, <?php echo $config->precision_currency_grand_total; ?>)
                    || format_currency(ledger_net_total * 1, <?php echo $config->precision_currency_grand_total; ?>) != format_currency((format_currency(document.getElementById('amount').value * 1, <?php echo $config->precision_currency_grand_total; ?>) * 1) - ((format_currency(document.getElementById('tax_amount_1').value * 1, <?php echo $config->precision_currency_grand_total; ?>) * 1) + (format_currency(document.getElementById('tax_amount_2').value * 1, <?php echo $config->precision_currency_grand_total; ?>) * 1) + (format_currency(document.getElementById('tax_amount_3').value * 1, <?php echo $config->precision_currency_grand_total; ?>) * 1)), <?php echo $config->precision_currency_grand_total; ?>))
				{
                    var input_box = confirm('<?php echo NBILL_LEDGER_BREAKDOWN_MISMATCH; ?>');
					if (input_box==true)
					{
						document.adminForm.task.value=task_name;
                        document.adminForm.submit();
					}
				}
				else
				{ <?php } ?>
					document.adminForm.task.value=task_name;
                    document.adminForm.submit();
				<?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
                } <?php } ?>
			}
		}
		function refresh_vendor()
		{
			//Show the appropriate nominal ledger codes depending on selected vendor
			var vendor_id = document.getElementById('vendor_id').value;
            var selects = document.getElementsByTagName('select');
            for (var index in selects)
            {
                if (selects[index] && selects[index].id && selects[index].id.substr(0, 7) == 'ledger_')
                {
                    var id_parts = selects[index].id.split('_');
                    if (id_parts[id_parts.length - 1] == vendor_id)
                    {
                        selects[index].style.display = '';
                    }
                    else
                    {
                        selects[index].style.display = 'none';
                    }
                }
            }
			<?php
			foreach ($vendors as $vendor)
			{
				echo "document.getElementById('invoices_" . $vendor->id . "').style.display = 'none';\n";
			}
			?>
			document.getElementById('invoices_' + vendor_id).style.display = 'inline';
		}

		function invoice_selected()
		{
            var vendor_id = document.getElementById('vendor_id').value;
            var invoicelist = document.getElementById('invoices_' + vendor_id);
            var selected_invoices = '';
            var j = 0;
            for (var i = 0; i < invoicelist.options.length; i++)
            {
                if (invoicelist.options[i].selected == true)
                {
                    if (j > 0)
                    {
                        selected_invoices += ',';
                    }
                    selected_invoices += invoicelist.options[i].value;
                    j++;
                }
            }
            //Delete any existing ledger items
            <?php
            $deleted_items = array();
            foreach ($ledger_breakdown as $ledger_entry)
            {
                $deleted_items[] = $ledger_entry->id;
            }
            if (count($deleted_items) > 0)
            {
                ?>
                document.getElementById('removed_items').value = '<?php echo implode(",", $deleted_items); ?>';
                <?php
            }
            ?>
            if (selected_invoices.length > 0)
            {
                show_wait_message(null, null);
                setTimeout(function(){invoice_select_do_sjax(selected_invoices)}, 50);
            }
            else
            {
                //Clear down
                document.getElementById('name').value = '';
                var vendor_cc = 'US';
                switch (vendor_id)
                {
                    <?php foreach ($vendors as $vendor)
                    { ?>
                        case '<?php echo $vendor->id; ?>':
                            vendor_cc = '<?php echo $vendor->vendor_country; ?>';
                            break;
                    <?php } ?>
                }
                var cc = document.getElementById('country');
                for (var index in cc.options)
                {
                    if (cc != undefined && cc[index] != undefined && cc[index].value != undefined && cc[index].value == vendor_cc)
                    {
                        cc.selectedIndex = index;
                    }
                }
                document.getElementById('amount').value = '0.00';
                document.getElementById('tax_rate_1').value = '0.00';
                document.getElementById('tax_amount_1').value = '0.00';
                <?php $suffix = $config->default_electronic ? '1' : '0'; ?>
                document.getElementById('tax_rate_1_electronic_delivery<?php echo $suffix; ?>').checked = true;
                document.getElementById('tax_rate_2').value = '0.00';
                document.getElementById('tax_amount_2').value = '0.00';
                document.getElementById('tax_rate_2_electronic_delivery<?php echo $suffix; ?>').checked = true;
                document.getElementById('tax_rate_3').value = '0.00';
                document.getElementById('tax_amount_3').value = '0.00';
                document.getElementById('tax_rate_3_electronic_delivery<?php echo $suffix; ?>').checked = true;
                <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
                document.getElementById('ledger_table_container').innerHTML = '<?php echo nbf_cms::$interop->database->getEscaped($ledger_table_heading . $ledger_table_new); ?>';
                <?php } ?>
                document.getElementById('reference').value = '';
                refresh_vendor();
            }
		}

        function invoice_select_do_sjax(selected_invoices)
        {
            var invoice_data = submit_sjax_request('get_invoice_income_data', 'transaction_id=<?php echo intval($transaction_id); ?>&document_ids=' + selected_invoices);
            invoice_data = invoice_data.split('#!#');
            if (invoice_data.length == 17)
            {
                document.getElementById('name').value = invoice_data[0];
                document.getElementById('country').value = invoice_data[1];
                document.getElementById('tax_reference').value = invoice_data[2];
                document.getElementById('amount').value = invoice_data[3];
                document.getElementById('tax_rate_1').value = invoice_data[4];
                document.getElementById('tax_amount_1').value = invoice_data[5];
                document.getElementById('tax_rate_1_electronic_delivery0').checked = (invoice_data[6]=='1'?false:true);
                document.getElementById('tax_rate_1_electronic_delivery1').checked = (invoice_data[6]=='1'?true:false);
                document.getElementById('tax_rate_2').value = invoice_data[7];
                document.getElementById('tax_amount_2').value = invoice_data[8];
                document.getElementById('tax_rate_2_electronic_delivery0').checked = (invoice_data[9]=='1'?false:true);
                document.getElementById('tax_rate_2_electronic_delivery1').checked = (invoice_data[9]=='1'?true:false);
                document.getElementById('tax_rate_3').value = invoice_data[10];
                document.getElementById('tax_amount_3').value = invoice_data[11];
                document.getElementById('tax_rate_3_electronic_delivery0').checked = (invoice_data[12]=='1'?false:true);
                document.getElementById('tax_rate_3_electronic_delivery1').checked = (invoice_data[12]=='1'?true:false);
                <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
                document.getElementById('ledger_table_container').innerHTML = invoice_data[13];
                <?php } ?>
                document.getElementById('reference').value = invoice_data[14];
                document.getElementById('added_items').value = invoice_data[15];
                document.getElementById('currency').value = invoice_data[16];
                refresh_vendor();
            }
        }

		function format_currency(number, dec_places){
        //(c) Copyright 2008, Russell Walker, Netshine Software Limited. www.netshinesoftware.com
        if (typeof dec_places === 'undefined') {
            dec_places=2;
        }
        var new_number='';var i=0;var sign="";number=number.toString();number=number.replace(/^\s+|\s+$/g,'');if(number.charCodeAt(0)==45){sign='-';number=number.substr(1).replace(/^\s+|\s+$/g,'')}dec_places=dec_places*1;dec_point_pos=number.lastIndexOf(".");if(dec_point_pos==0){number="0"+number;dec_point_pos=1}if(dec_point_pos==-1||dec_point_pos==number.length-1){if(dec_places>0){new_number=number+".";for(i=0;i<dec_places;i++){new_number+="0"}if(new_number==0){sign=""}return sign+new_number}else{return sign+number}}var existing_places=(number.length-1)-dec_point_pos;if(existing_places==dec_places){return sign+number}if(existing_places<dec_places){new_number=number;for(i=existing_places;i<dec_places;i++){new_number+="0"}if(new_number==0){sign=""}return sign+new_number}var end_pos=(dec_point_pos*1)+dec_places;var round_up=false;if((number.charAt(end_pos+1)*1)>4){round_up=true}var digit_array=new Array();for(i=0;i<=end_pos;i++){digit_array[i]=number.charAt(i)}for(i=digit_array.length-1;i>=0;i--){if(digit_array[i]=="."){continue}if(round_up){digit_array[i]++;if(digit_array[i]<10){break}}else{break}}for(i=0;i<=end_pos;i++){if(digit_array[i]=="."||digit_array[i]<10||i==0){new_number+=digit_array[i]}else{new_number+="0"}}if(dec_places==0){new_number=new_number.replace(".","")}if(new_number==0){sign=""}return sign+new_number}

        function update_tax_amount()
        {
            if (document.getElementById('tax_rate_1').value.length > 0)
            {
                var divisor = (document.getElementById('tax_rate_1').value * 1) + 100;
                document.getElementById('tax_amount_1').value = format_currency(((document.getElementById('amount').value * 1) / divisor) * document.getElementById('tax_rate_1').value, <?php echo $config->precision_currency_grand_total; ?>);
            }
        }

        function auto_populate_ledger()
        {
            <?php
            if ($row->id) {echo "return;";}
            else
            { ?>

            if (!document.getElementById('ledger_gross_new_1_amount') && document.getElementById('amount').value != 0)
            {
                //No ledger info has been specified yet, so auto-populate
                var tax_amount = format_currency(document.getElementById('tax_amount_1').value, <?php echo $config->precision_currency_line_total; ?>) * 1;
                var net_amount = (format_currency(document.getElementById('amount').value, <?php echo $config->precision_currency_line_total; ?>) * 1) - tax_amount;
                document.getElementById('ledger_net_new_amount').value = format_currency(net_amount, <?php echo $config->precision_currency_line_total; ?>);
                document.getElementById('ledger_tax_new_amount').value = format_currency(tax_amount, <?php echo $config->precision_currency_line_total; ?>);
                document.getElementById('ledger_tax_new_rate').value = format_currency(document.getElementById('tax_rate_1').value, <?php echo $config->precision_tax_rate; ?>);
                document.getElementById('ledger_gross_new_amount').value = format_currency(document.getElementById('amount').value, <?php echo $config->precision_currency_line_total; ?>);
            }
            <?php } ?>
        }
		</script>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="income" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $transaction_id;?>" />
		<input type="hidden" name="removed_items", id="removed_items" value="<?php echo nbf_common::get_param($_POST,'removed_items'); ?>" />
		<input type="hidden" name="added_items", id="added_items" value="<?php echo nbf_common::get_param($_POST,'added_items'); ?>" />
        <input type="hidden" name="document_id" id="document_id" value="<?php echo nbf_common::get_param($_REQUEST, 'document_id'); ?>" />
        <input type="hidden" name="no_record_limit" id="no_record_limit" value="<?php echo nbf_common::get_param($_REQUEST, 'no_record_limit'); ?>" />
        <input type="hidden" name="guessed" id="guessed" value="<?php echo nbf_common::get_param($_REQUEST, 'guessed'); ?>" />
        <input type="hidden" name="transaction_type" value="IN" />
		<?php nbf_html::add_filters();
        if (nbf_common::get_param($_REQUEST, 'guessed'))
        { ?>
            <div style="border:solid 2px #ff0000;padding:3px;font-weight:bold;font-size:1.1em;color:#000000;">
                <?php echo NBILL_INCOME_LEDGER_GUESSED ?>
            </div>
        <?php }
        ?>

		<table class="adminheading">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "income"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php echo $row->id ? NBILL_EDIT_INCOME . " '$row->transaction_no'" : NBILL_NEW_INCOME; ?>
			</th>
		</tr>
		</table>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-income">
		    <tr>
			    <th colspan="2"><?php echo NBILL_INCOME_DETAILS; ?></th>
		    </tr>
		    <?php
			    if (count($vendors) > 1)
			    {?>
				    <tr id="nbill-admin-tr-vendor-name">
					    <td class="nbill-setting-caption">
						    <?php echo NBILL_VENDOR_NAME; ?>
					    </td>
					    <td class="nbill-setting-value">
						    <?php
							    $vendor_name = array();
							    foreach ($vendors as $vendor)
							    {
								    $vendor_name[] = nbf_html::list_option($vendor->id, $vendor->vendor_name);
							    }
							    if ($use_posted_values)
							    {
								    $selected_vendor = nbf_common::get_param($_POST, 'vendor_id');
							    }
							    else
							    {
								    if($row->id)
								    {
									    $selected_vendor = $row->vendor_id;
								    }
								    else
								    {
									    $selected_vendor = nbf_common::get_param($_POST, 'vendor_filter');
								    }
								    if ($selected_vendor < 1)
								    {
									    $selected_vendor = @$vendors[0]->id;
								    }
							    }
							    echo nbf_html::select_list($vendor_name, "vendor_id", 'id="vendor_id" class="inputbox" onchange="refresh_vendor();"', $selected_vendor);
						    ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_VENDOR_ID, "vendor_id_help"); ?>
					    </td>
				    </tr>
			    <?php }
			    else
			    {
				    echo "<input type=\"hidden\" name=\"vendor_id\" id=\"vendor_id\" value=\"" . $vendors[0]->id . "\" />";
				    $_POST['vendor_id'] = $vendors[0]->id;
                    $selected_vendor = $vendors[0]->id;
			    }
		    ?>
		    <tr id="nbill-admin-tr-receipt-no">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_INCOME_RECEIPT_NO; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="transaction_no" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'transaction_no', null, true) : $row->transaction_no; ?>" class="inputbox" style="width:80px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_RECEIPT_NO, "transaction_no_help"); ?>
			    </td>
		    </tr>
            <!-- Custom Fields Placeholder -->
		    <tr id="nbill-admin-tr-related-invoice">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_RELATED_INVOICE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    foreach ($vendors as $vendor)
					    {
						    if($row->id)
						    {
							    $selected_invoices = explode(",", $row->document_ids);
						    }
						    else
						    {
							    $selected_invoices = array();
							    if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'document_id')) > 0)
							    {
								    $selected_invoices = explode(",", nbf_common::get_param($_POST, 'document_id'));
							    }
						    }

						    if ($vendor->id == $selected_vendor)
						    {
							    $visibility = "style=\"display:inline\"";
						    }
						    else
						    {
							    $visibility = "style=\"display:none\"";
						    }

						    //Manually output select list to allow for multiple selections
						    echo "<select name=\"invoices_" . $vendor->id . "[]\" size=\"10\" multiple=\"multiple\" class=\"inputbox\" id=\"invoices_" . $vendor->id . "\" onchange=\"invoice_selected();\" $visibility>";
						    foreach ($invoices[$vendor->id] as $invoice)
						    {
							    echo "<option value=\"" . intval($invoice->id) . "\"";
							    if ($use_posted_values)
							    {
								    if (count(nbf_common::get_param($_POST,'invoices_' . $vendor->id)) > 0)
								    {
									    if (array_search($invoice->id, nbf_common::get_param($_POST,'invoices_' . $vendor->id)) !== false)
									    {
										    echo " selected=\"selected\"";
									    }
								    }
							    }
							    else
							    {
								    if (array_search($invoice->id, $selected_invoices) !== false)
								    {
									    echo " selected=\"selected\"";
								    }
							    }
							    echo ">" . $invoice->document_no . ": " . $invoice->billing_name . " (" . format_number($invoice->total_gross, 'currency_grand', null, null, null, $invoice->currency) . ")</option>";
						    }
						    echo "</select>";
                            if (count($invoices[$vendor->id]) == nbf_globals::$record_limit)
                            { ?>
                                <br /><span style="color:#ff0000;font-weight:bold"><?php echo sprintf(NBILL_INCOME_RECORD_LIMIT_WARNING, nbf_globals::$record_limit, nbf_globals::$record_limit); ?></span><br />
                                <input type="button" class="button btn" name="remove_record_limit" id="remove_record_limit" value="<?php echo NBILL_INCOME_SHOW_ALL; ?>" onclick="adminForm.no_record_limit.value='1';adminForm.submit();return false;" />
                            <?php }
					    }
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_RELATED_INVOICE, "invoices_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-received-from">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_RECEIVED_FROM; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="name" id="name" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'name', null, true)) : str_replace("\"", "&quot;", $row->name); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_RECEIVED_FROM, "name_help"); ?>
			    </td>
		    </tr>
            <tr id="nbill-admin-tr-country">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_RECEIVED_COUNTRY; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php $country = array();
                    $selected_cc = "";
                    $country[] = nbf_html::list_option("", NBILL_UNKNOWN);
                    foreach ($countries as $country_code)
                    {
                        $country[] = nbf_html::list_option($country_code['code'], $country_code['description']);
                    }
                    if ($use_posted_values)
                    {
                        $selected_cc = nbf_common::get_param($_POST, 'country');
                    }
                    else
                    {
                        if ($row->id)
                        {
                            $selected_cc = $row->country;
                        }
                        else
                        {
                            foreach ($vendors as $vendor)
                            {
                                if ($vendor->id == $selected_vendor)
                                {
                                    $selected_cc = $vendor->vendor_country;
                                    break;
                                }
                            }
                        }
                    }
                    echo nbf_html::select_list($country, "country", 'id="country" class="inputbox"', $selected_cc); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_RECEIVED_COUNTRY, "country_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-tax-reference">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_RECEIVED_TAX_REF; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="tax_reference" id="tax_reference" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'tax_reference', null, true)) : str_replace("\"", "&quot;", $row->tax_reference); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_RECEIVED_TAX_REF, "tax_reference_help"); ?>
                </td>
            </tr>
		    <tr id="nbill-admin-tr-for">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_RECEIVED_FOR; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="for" id="for" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'for', null, true)) : str_replace("\"", "&quot;", $row->for); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_RECEIVED_FOR, "for_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-date">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DATE_RECEIVED; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    $date_format = nbf_common::get_date_format();
				    $cal_date_format = nbf_common::get_date_format(true);
				    $date_value = $row->id ? nbf_common::nb_date($date_format, $row->date) : nbf_common::nb_date($date_format, nbf_common::nb_time());
				    $date_parts = nbf_date::get_date_parts($date_value, $cal_date_format);
				    if ($date_parts['y'] < 1971)
				    {
					    $date_value = "";
				    }
				    ?>
				    <span style="white-space:nowrap"><input type="text" name="date" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST, 'date') : $date_value; ?>" />
				    <input type="button" name="date_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.date,'<?php echo $cal_date_format; ?>',this);" /></span>
                    <?php nbf_html::show_static_help(NBILL_INSTR_DATE_RECEIVED, "date_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-payment-method">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_PAYMENT_METHOD; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    $pay_methods_list = array();
				    foreach ($pay_methods as $pay_method)
				    {
					    $pay_methods_list[] = nbf_html::list_option($pay_method->code, $pay_method->description);
				    }
				    echo nbf_html::select_list($pay_methods_list, "method", 'id="method" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST,'method', null, true) : $row->method);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_PAYMENT_METHOD, "method_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-currency">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CURRENCY; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    $currency_list = array();
				    foreach ($currencies as $currency)
				    {
					    $currency_list[] = nbf_html::list_option($currency['code'], $currency['description']);
				    }
				    echo nbf_html::select_list($currency_list, "currency", 'id="currency" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST,'currency', null, true) : $row->currency);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_CURRENCY, "currency_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-amount">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_AMOUNT_RECEIVED; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="amount" id="amount" onchange="update_tax_amount();auto_populate_ledger();" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'amount', null, true) : format_number($row->amount, 'currency_grand'); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_AMOUNT_RECEIVED, "amount_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-tax-rate-and-amount">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_TAX_RATE_AND_AMOUNT; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <div><?php echo NBILL_INCOME_TAX_RATE; ?> <input type="text" name="tax_rate_1" id="tax_rate_1" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'tax_rate_1', null, true) : format_number($row->tax_rate_1, 'tax_rate'); ?>" class="inputbox small-numeric" onchange="update_tax_amount();auto_populate_ledger();" />%&nbsp;&nbsp;
				    <?php echo NBILL_INCOME_TAX_AMOUNT; ?> <input type="text" name="tax_amount_1" id="tax_amount_1" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'tax_amount_1', null, true) : format_number($row->tax_amount_1, 'currency_grand'); ?>" class="inputbox small-numeric" />
                    <span class="nbill-electronic-delivery"><?php echo NBILL_TX_ELECTRONIC_DELIVERY; ?> <?php echo nbf_html::yes_or_no_options("tax_rate_1_electronic_delivery", "", $row->tax_rate_1_electronic_delivery); ?></span></div>
				    <div><?php echo NBILL_INCOME_TAX_RATE; ?> <input type="text" name="tax_rate_2" id="tax_rate_2" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'tax_rate_2', null, true) : format_number($row->tax_rate_2, 'tax_rate'); ?>" class="inputbox small-numeric" />%&nbsp;&nbsp;
				    <?php echo NBILL_INCOME_TAX_AMOUNT; ?> <input type="text" name="tax_amount_2" id="tax_amount_2" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'tax_amount_2', null, true) : format_number($row->tax_amount_2, 'currency_grand'); ?>" class="inputbox small-numeric" />
                    <span class="nbill-electronic-delivery"><?php echo NBILL_TX_ELECTRONIC_DELIVERY; ?> <?php echo nbf_html::yes_or_no_options("tax_rate_2_electronic_delivery", "", $row->tax_rate_2_electronic_delivery); ?></span></div>
				    <div><?php echo NBILL_INCOME_TAX_RATE; ?> <input type="text" name="tax_rate_3" id="tax_rate_3" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'tax_rate_3', null, true) : format_number($row->tax_rate_3, 'tax_rate'); ?>" class="inputbox small-numeric" />%&nbsp;&nbsp;
				    <?php echo NBILL_INCOME_TAX_AMOUNT; ?> <input type="text" name="tax_amount_3" id="tax_amount_3" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'tax_amount_3', null, true) : format_number($row->tax_amount_3, 'currency_grand'); ?>" class="inputbox small-numeric" />
                    <span class="nbill-electronic-delivery"><?php echo NBILL_TX_ELECTRONIC_DELIVERY; ?> <?php echo nbf_html::yes_or_no_options("tax_rate_3_electronic_delivery", "", $row->tax_rate_3_electronic_delivery); ?></span></div>
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_RATE_AND_AMOUNT . NBILL_INSTR_TAX_RATE_AND_AMOUNT_ELEC, "tax_rate_help"); ?>
			    </td>
		    </tr>
            <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
		    <tr id="nbill-admin-tr-ledger-breakdown">
			    <td colspan="2" class="nbill-setting-value">
				    <?php echo NBILL_LEDGER_BREAKDOWN; ?>&nbsp;&nbsp; &gt;<a href="#" id="calc_toggle" onclick="if (typeof(calculator_toggle) === 'function'){calculator_toggle()};return false;"><?php echo NBILL_TX_CALC_OFF; ?></a><br />
			        <?php if (nbf_common::get_param($_REQUEST, 'guessed')) {echo "<strong>" . NBILL_INCOME_LEDGER_PLEASE_CHECK . "</strong>"; } ?>
                    <div id="ledger_table_container" style="padding: 3px;<?php echo nbf_common::get_param($_REQUEST, 'guessed') ? " border: solid 2px #ff0000" : "";?>;">
                        <?php echo $ledger_table_heading . $ledger_table_rows . $ledger_table_new; ?>
				    </div>
			    </td>
		    </tr>
            <?php } ?>
		    <tr id="nbill-admin-tr-reference">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_RECEIPT_REFERENCE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="reference" id="reference" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'reference', null, true)) : str_replace("\"", "&quot;", $row->reference); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_RECEIPT_REFERENCE, "reference_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-no-summary">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_INCOME_NO_SUMMARY; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    echo nbf_html::yes_or_no_options("no_summary", "", $row->no_summary);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_INCOME_NO_SUMMARY, "no_summary_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-notes">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_NOTES; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <textarea name="notes" id="notes"><?php echo $use_posted_values ? nbf_common::get_param($_POST,'notes', null, true) : $row->notes; ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_INSTR_NOTES, "notes_help"); ?>
			    </td>
		    </tr>
		    </table>
        </div>

        <?php
        
         ?>

		</form>
		<script type="text/javascript">
		refresh_vendor();
		<?php if (!$row->id && (count($ledger_breakdown) == 0 && nbf_common::nb_strlen(nbf_common::get_param($_POST,'added_items')) == 0))
		{?>
			setTimeout(function(){invoice_selected();}, 500);
		<?php }?>
		</script>
		<?php
	}

    public static function multi_income_generator($invoice_count, $document_ids, $pay_methods)
    {
        nbf_html::load_calendar();
        $date_format = nbf_common::get_date_format();
        $cal_date_format = nbf_common::get_date_format(true);
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
        ?>
        <script language="javascript" type="text/javascript">
        <?php nbf_html::add_js_validation_numeric();
        nbf_html::add_js_validation_date(); ?>
        function nbill_submit_task(task_name)
        {
            if (!IsValidDate(document.adminForm.date.value, false))
            {
                alert('<?php echo sprintf(NBILL_INVALID_DATE_FIELD, NBILL_INCOME_DATE, $cal_date_format); ?>');
            }
            else
            {
                document.adminForm.task.value=task_name;
                document.adminForm.submit();
                return;
            }
        }
        </script>

        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="income" />
        <input type="hidden" name="task" value="multi_invoice" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
        <input type="hidden" name="document_ids" value="<?php echo implode(",", $document_ids); ?>" />
        <input type="hidden" name="invoice_count" id="invoice_count" value="<?php echo $invoice_count; ?>" />
        <?php nbf_html::add_filters();
        ?>

        <table class="adminheading" style="width:auto;">
        <tr>
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "income"); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
                <?php echo NBILL_CREATE_MULTIPLE_INCOMES; ?>
            </th>
        </tr>
        </table>

        <p><?php echo NBILL_CREATE_MULTIPLE_INCOMES_INTRO; ?></p>

        <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
        <tr>
            <th colspan="3"><?php echo NBILL_INCOME_DETAILS; ?></th>
        </tr>
        <tr>
            <td width="20%">
                <?php echo NBILL_DATE_RECEIVED; ?>
            </td>
            <td>
                <?php
                $date_value = nbf_common::nb_date($date_format, nbf_common::nb_time());
                $date_parts = nbf_date::get_date_parts($date_value, $cal_date_format);
                if ($date_parts['y'] < 1971)
                {
                    $date_value = "";
                }
                ?>
                <span style="white-space:nowrap"><input type="text" name="date" size="25" maxlength="19" value="<?php echo $date_value; ?>" />
                <input type="button" name="date_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.date,'<?php echo $cal_date_format; ?>',this);" /></span>
            </td>
            <td>
                <?php echo NBILL_INSTR_DATE_RECEIVED; ?>
            </td>
        </tr>
        <tr>
            <td width="20%">
                <?php echo NBILL_PAYMENT_METHOD; ?>
            </td>
            <td>
                <?php
                $pay_methods_list = array();
                foreach ($pay_methods as $pay_method)
                {
                    $pay_methods_list[] = nbf_html::list_option($pay_method->code, $pay_method->description);
                }
                echo nbf_html::select_list($pay_methods_list, "method", 'id="method" class="inputbox"', nbf_common::get_param($_POST,'method', 'GG', true));
                ?>
            </td>
            <td>
                <?php echo NBILL_INSTR_PAYMENT_METHOD; ?>
            </td>
        </tr>
        <tr>
            <td width="20%">
                <?php echo NBILL_RECEIPT_REFERENCE; ?>
            </td>
            <td>
                <input type="text" name="reference" id="reference" value="" />
            </td>
            <td>
                <?php echo NBILL_INSTR_RECEIPT_REFERENCE; ?>
            </td>
        </tr>
        <tr>
            <td width="20%">
                <?php echo NBILL_NOTES; ?>
            </td>
            <td>
                <textarea name="notes" id="notes" cols="35" rows="10"></textarea>
            </td>
            <td>
                <?php echo NBILL_INSTR_NOTES; ?>
            </td>
        </tr>
        </table>

        </form>
        <?php
    }

    /**
    * No invoice records found (could happen if someone tries to mark invoices as paid that are already marked as paid or part-paid)
    */
    public function multi_income_abort()
    {
        //Remove generate button from toolbar
        $buffer = ob_get_clean();
        $buffer = str_replace('<td id="nbill_toolbar_generate_button">', '<td id="nbill_toolbar_generate_button" style="display:none;">', $buffer);
        echo $buffer;
        ?>
        <script language="javascript" type="text/javascript">
        function nbill_submit_task(task_name)
        {
            document.adminForm.task.value=task_name;
            document.adminForm.submit();
            return;
        }
        </script>
        <div class="nbill-message"><?php echo NBILL_MULTI_INCOME_NO_INVOICES_FOUND; ?></div>
        <?php
    }
}