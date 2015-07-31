<?php
/**
* HTML output for expenditure feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillExpenditure
{
	public static function showExpenditure($rows, $pagination, $vendors, $document_nos, $date_format, $attachments = array())
	{
        $vendor_col = false;
		nbf_html::load_calendar();
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "expenditure"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_EXPENDITURE_TITLE; ?>
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
        <input type="hidden" name="action" value="expenditure" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
        <input type="hidden" name="attachment_id" value="" />

		<p align="left"><?php echo NBILL_EXPENDITURE_INTRO; ?></p>

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
		&nbsp;&nbsp; <span style="white-space:nowrap"><?php echo NBILL_PAYMENT_NO; ?>&nbsp;<input type="text" id="payment_no_search" name="pyt_no_search" value="<?php echo nbf_common::get_param($_REQUEST,'pyt_no_search', '', true); ?>" /></span>
		&nbsp;&nbsp; <span style="white-space:nowrap"><?php echo NBILL_PAID_TO;?>&nbsp;<input type="text" name="paid_to_search" value="<?php echo nbf_common::get_param($_REQUEST,'paid_to_search', '', true); ?>" /></span>
		&nbsp;&nbsp; <span style="white-space:nowrap"><?php echo NBILL_PAYMENT_AMOUNT;?>&nbsp;<input type="text" name="pyt_amount_search" value="<?php echo nbf_common::get_param($_REQUEST,'pyt_amount_search', '', true); ?>" /></span>
		<br /><span style="white-space:nowrap"><?php echo NBILL_DATE_RANGE; $cal_date_format = nbf_common::get_date_format(true); ?>
		<input type="text" name="search_date_from" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_from'); ?>" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> />
		<input type="button" name="search_date_from_cal" class="btn button nbill-button" value="..." onclick="displayCalendar(document.adminForm.search_date_from,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> /></span>
		<span style="white-space:nowrap"><?php echo NBILL_TO; ?>
		<input type="text" name="search_date_to" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_to'); ?>" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> />
		<input type="button" name="search_date_to_cal" class="btn button nbill-button" value="..." onclick="displayCalendar(document.adminForm.search_date_to,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> /></span>
		<input type="submit" class="btn button nbill-button" name="dosearch" value="<?php echo NBILL_GO; ?>" />
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
			    <th class="title">
				    <?php echo NBILL_PAYMENT_NO; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_PAYMENT_DATE; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_PAID_TO; ?>
			    </th>
			    <th class="title" style="text-align:right;">
				    <?php echo NBILL_PAYMENT_AMOUNT; ?>
			    </th>
			    <th class="title responsive-cell optional" style="width:40%">
				    <?php echo NBILL_PAYMENT_FOR; ?>
			    </th>
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
		    </tr>
		    <?php
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];
				    $link = nbf_cms::$interop->admin_page_prefix . "&action=expenditure&task=edit&cid=$row->id&search_date_from=" . nbf_common::get_param($_REQUEST, 'search_date_from') . "&search_date_to=" . nbf_common::get_param($_REQUEST, 'search_date_to') . "&pyt_no_search=" . nbf_common::get_param($_REQUEST, 'pyt_no_search') . "&paid_to_search=" . nbf_common::get_param($_REQUEST, 'paid_to_search') . "&pyt_amount_search=" . nbf_common::get_param($_REQUEST, 'pyt_amount_search');
				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\">$checked</td>";
				    $transaction_no = $row->transaction_no;
				    if (nbf_common::nb_strlen($transaction_no) == 0)
				    {
					    $transaction_no = NBILL_EXP_UNNUMBERED;
				    }
                    echo "<td class=\"list-value\"><div style=\"float:left\">";
                    echo "<a href=\"$link\" title=\"" . NBILL_EDIT_EXPENDITURE . "\">" . $transaction_no . "</a>";
                    echo "&nbsp;<a href=\"#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=" . nbf_common::get_param($_REQUEST, 'action') . "&cid=" . $row->id . "&task=printer_friendly&hidemainmenu=1&hide_billing_menu=1', '" . uniqid() . "', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;\">";
                    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/preview.gif\" alt=\"" . NBILL_EXPENDITURE_PRINTER_FRIENDLY . "\" border=\"0\" />";
                    echo "</div>";
                    if (file_exists(nbf_cms::$interop->nbill_admin_base_path . '/admin.proc/supporting_docs.php')) {
                    ?>
                    <div style="float:right"><a href="#" onclick="<?php if ($row->attachment_count){ ?>var att=document.getElementById('attachments_<?php echo $row->id; ?>');if(att.style.display=='none'){att.style.display='';}else{att.style.display='none';}<?php }else{ ?>window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&use_stylesheet=1&show_toolbar=1&attach_to_type=EX&attach_to_id=<?php echo $row->id; ?>','','scrollbars=1,width=790,height=500');<?php } ?>return false;"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/supporting_docs.gif" alt="<?php echo NBILL_ATTACHMENTS; ?>" style="vertical-align:middle;" /><?php if ($row->attachment_count) {echo " (" . $row->attachment_count . ")";} ?></a></div>
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
                        <a href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&use_stylesheet=1&show_toolbar=1&attach_to_type=EX&attach_to_id=<?php echo $row->id; ?>','','scrollbars=1,width=790,height=500');return false;"><img style="vertical-align:middle" border="0" alt="" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/supporting_docs.gif" />&nbsp;<?php echo NBILL_NEW_ATTACHMENT; ?></a>
                        </td></tr>
                        </table>
                    </div>
                    <?php
                    }
                    echo "</td>";
				    echo "<td class=\"list-value\">" . nbf_common::nb_date($date_format, $row->date) . "</td>";
				    if ($row->entity_id > 0)
				    {
					    $return = base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=expenditure&task=view&search_date_from=" . nbf_common::get_param($_REQUEST, 'search_date_from') . "&search_date_to=" . nbf_common::get_param($_REQUEST, 'search_date_to') . "&pyt_no_search=" . nbf_common::get_param($_REQUEST, 'pyt_no_search') . "&paid_to_search=" . nbf_common::get_param($_REQUEST, 'paid_to_search') . "&pyt_amount_search=" . nbf_common::get_param($_REQUEST, 'pyt_amount_search'));
					    echo "<td class=\"list-value\"><a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=suppliers&task=edit&cid=" . $row->entity_id . "&return=$return\">" . $row->name . "</a></td>";
				    }
				    else
				    {
					    echo "<td class=\"list-value\">" . $row->name . "</td>";
				    }
				    echo "<td class=\"list-value\" style=\"text-align:right;\">" . format_number($row->amount, 'currency_grand', null, null, null, $row->currency) . "</td>";
				    $this_document_ids = explode(",", $row->document_ids);
				    if (count($this_document_ids) > 0)
				    {
					    $invoice_count = 0;
					    echo "<td class=\"list-value responsive-cell optional\" style=\"width:40%\">";
					    foreach ($this_document_ids as $this_document_id)
					    {
						    $this_document_nos = array();
						    foreach ($document_nos as $document_no)
						    {
							    if ($document_no->id == $this_document_id)
							    {
								    $invoice_count++;
								    echo "<a href=\"#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=credits&task=printpreviewpopup&hidemainmenu=1&items=" . $document_no->id . "', " . nbf_common::nb_time() . ", 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;\">" . $document_no->document_no . "</a> ";
							    }
						    }
					    }
					    if ($invoice_count == 0)
					    {
						    echo $row->for;
					    }
					    echo "</td>";
				    }
				    else
				    {
					    echo "<td class=\"list-value responsive-cell optional\" style=\"width:40%\">" . $row->for . "</td>";
				    }

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
				    echo "</tr>";
			    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="<?php echo $vendor_col ? "8" : "7"; ?>" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		</form>
		<?php
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
        include(nbf_cms::$interop->nbill_admin_base_path . "/admin.html/payment.template.html.php");
        exit;
    }

	public static function editExpenditure($expenditure_id, $row, $vendors, $invoices, $shipping, $suppliers, $pay_methods, $countries, $currencies, $ledger, $ledger_breakdown, $use_posted_values, $attachments = array())
	{
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
        if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
        {
            echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
        }
        nbf_html::load_calendar();
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.client.php");
        $nb_database = nbf_cms::$interop->database; //For escaping
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
        $ledger_table_heading .= '<th>' . NBILL_EXPENDITURE_LEDGER_NET_AMOUNT . '</th>';
        $ledger_table_heading .= '<th>' . NBILL_EXPENDITURE_LEDGER_TAX_AMOUNT . '</th>';
        $ledger_table_heading .= '<th>' . NBILL_EXPENDITURE_LEDGER_GROSS_AMOUNT . '</th>';
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
                $ledger_table_rows .= "</td><td><table border=\"0\" class=\"borderless\"><tr><td>" . NBILL_EXP_TAX_RATE . ":</td><td><input type=\"text\" name=\"ledger_tax_$ledger_entry->id" . "_rate\" id=\"ledger_tax_$ledger_entry->id" . "_rate\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_tax_$ledger_entry->id" . "_rate"), 'tax_rate') : format_number($ledger_entry->tax_rate, 'tax_rate')) . "\" onchange=\"ledger_price_update('" . $ledger_entry->id . "', false);\" /></td></tr>";
                $ledger_table_rows .= "<tr><td>" . NBILL_EXP_TAX_AMOUNT . ":</td><td><input type=\"text\" name=\"ledger_tax_$ledger_entry->id" . "_amount\" id=\"ledger_tax_$ledger_entry->id" . "_amount\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_tax_$ledger_entry->id" . "_amount"), 'currency_line') : format_number($ledger_entry->tax_amount, 'currency_line')) . "\" /></td></tr></table>";
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
                    $ledger_table_rows .= "</td><td><table border=\"0\" class=\"borderless\"><tr><td>" . NBILL_EXP_TAX_RATE . ":</td><td><input type=\"text\" name=\"ledger_tax_new_$added_item" . "_rate\" id=\"ledger_tax_new_$added_item" . "_rate\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_tax_new_$added_item" . "_rate"), 'tax_rate') : format_number(nbf_common::get_param($_POST,"ledger_tax_new_$added_item" . "_rate"), 'tax_rate')) . "\" onchange=\"ledger_price_update('new_" . $added_item . "', false);\" /></td></tr>";
                    $ledger_table_rows .= "<tr><td>" . NBILL_EXP_TAX_AMOUNT . ":</td><td><input type=\"text\" name=\"ledger_tax_new_$added_item" . "_amount\" id=\"ledger_tax_new_$added_item" . "_amount\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_tax_new_$added_item" . "_amount"), 'currency_line') : format_number(nbf_common::get_param($_POST,"ledger_tax_new_$added_item" . "_amount"), 'currency_line')) . "\" /></td></tr></table>";
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
        $ledger_table_new .= "</td><td><table border=\"0\" class=\"borderless\"><tr><td>" . NBILL_EXP_TAX_RATE . ":</td><td><input type=\"text\" name=\"ledger_tax_new_rate\" id=\"ledger_tax_new_rate\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_tax_new_rate"), 'tax_rate') : "") . "\" onchange=\"ledger_price_update('new', false);\" /></td></tr>";
        $ledger_table_new .= "<tr><td>" . NBILL_EXP_TAX_AMOUNT . ":</td><td><input type=\"text\" name=\"ledger_tax_new_amount\" id=\"ledger_tax_new_amount\" class=\"inputbox small-numeric\" value=\"" . ($use_posted_values ? format_number(nbf_common::get_param($_POST, "ledger_tax_new_amount"), 'currency_line') : "") . "\" /></td></tr></table>";
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
            else if (!IsValidDate(form.date.value, false))
			{
				alert('<?php echo sprintf(NBILL_INVALID_DATE_FIELD, NBILL_DATE_PAID, $cal_date_format); ?>');
			}
			else if (!IsNumeric(form.amount.value, false))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_AMOUNT_PAID); ?>');
			}
			else if (!IsNumeric(form.tax_rate_1.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_EXP_TAX_RATE); ?>');
			}
			else if (!IsNumeric(form.tax_amount_1.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_EXP_TAX_AMOUNT); ?>');
			}
			else if (!IsNumeric(form.tax_rate_2.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_EXP_TAX_RATE); ?>');
			}
			else if (!IsNumeric(form.tax_amount_2.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_EXP_TAX_AMOUNT); ?>');
			}
			else if (!IsNumeric(form.tax_rate_3.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_EXP_TAX_RATE); ?>');
			}
			else if (!IsNumeric(form.tax_amount_3.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_EXP_TAX_AMOUNT); ?>');
			}
            else
            {
                alerted = false;
                if (document.getElementById('ledger_new_' + document.getElementById('vendor_id').value).options.length > 1 && document.getElementById('ledger_new_' + document.getElementById('vendor_id').value).value == '-1' && document.getElementById('ledger_gross_new_amount').value != 0)
                {
                    alerted = !confirm('<?php echo NBILL_EXPENDITURE_WARNING_DEFAULT_LEDGER; ?>');
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
                                    alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_EXPENDITURE_LEDGER_NET_AMOUNT); ?>');
                                    alerted = true;
                                    break;
                                }
                                else if (inputs[input_index].id.indexOf('_rate') > -1)
                                {
                                    alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_EXPENDITURE_LEDGER_TAX_RATE); ?>');
                                    alerted = true;
                                    break;
                                }
                                else if (inputs[input_index].id.indexOf('_tax') > -1)
                                {
                                    alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_EXPENDITURE_LEDGER_TAX_AMOUNT); ?>');
                                    alerted = true;
                                    break;
                                }
                                else if (inputs[input_index].id.indexOf('_gross') > -1)
                                {
                                    alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_EXPENDITURE_LEDGER_GROSS_AMOUNT); ?>');
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
            }

            if (!alerted)
            {
                //Check that nominal ledger breakdown adds up to income total
                if (format_currency(ledger_gross_total * 1, <?php echo $config->precision_currency_grand_total; ?>) != format_currency(document.getElementById('amount').value * 1, <?php echo $config->precision_currency_grand_total; ?>)
                    || format_currency(ledger_tax_total * 1, <?php echo $config->precision_currency_grand_total; ?>) != format_currency(((format_currency(document.getElementById('tax_amount_1').value * 1, <?php echo $config->precision_currency_grand_total; ?>) * 1) + (format_currency(document.getElementById('tax_amount_2').value * 1, <?php echo $config->precision_currency_grand_total; ?>) * 1) + (format_currency(document.getElementById('tax_amount_3').value * 1, <?php echo $config->precision_currency_grand_total; ?>) * 1)) * 1, <?php echo $config->precision_currency_grand_total; ?>)
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
                {
                    document.adminForm.task.value=task_name;
                    document.adminForm.submit();
                }
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
            if (selected_invoices.length > 0)
            {
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
                document.getElementById('ledger_table_container').innerHTML = '<?php echo nbf_cms::$interop->database->getEscaped($ledger_table_heading . $ledger_table_new); ?>';
                document.getElementById('reference').value = '';
                refresh_vendor();
            }
		}

        function invoice_select_do_sjax(selected_invoices)
        {
            var invoice_data = submit_sjax_request('get_invoice_income_data', 'exp=1&transaction_id=<?php echo intval($expenditure_id); ?>&document_ids=' + selected_invoices);
            invoice_data = invoice_data.split('#!#');
            if (invoice_data.length == 19)
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
                document.getElementById('ledger_table_container').innerHTML = invoice_data[13];
                document.getElementById('reference').value = invoice_data[14];
                document.getElementById('added_items').value = invoice_data[15];
                document.getElementById('currency').value = invoice_data[16];
                var supplier = document.getElementById('entity_id');
                for (var supplier_index in supplier.options)
                {
                    if (supplier.options[supplier_index].value == invoice_data[17])
                    {
                        supplier.selectedIndex = supplier_index;
                    }
                }
                document.getElementById('payee_address').value = invoice_data[18];
                refresh_vendor();
            }
        }

		function format_currency(number, dec_places){
        //(c) Copyright 2008, Russell Walker, Netshine Software Limited. www.netshinesoftware.com
        if (typeof dec_places === 'undefined') {
            dec_places=2;
        }
        var new_number='';var i=0;var sign="";number=number.toString();number=number.replace(/^\s+|\s+$/g,'');if(number.charCodeAt(0)==45){sign='-';number=number.substr(1).replace(/^\s+|\s+$/g,'')}dec_places=dec_places*1;dec_point_pos=number.lastIndexOf(".");if(dec_point_pos==0){number="0"+number;dec_point_pos=1}if(dec_point_pos==-1||dec_point_pos==number.length-1){if(dec_places>0){new_number=number+".";for(i=0;i<dec_places;i++){new_number+="0"}if(new_number==0){sign=""}return sign+new_number}else{return sign+number}}var existing_places=(number.length-1)-dec_point_pos;if(existing_places==dec_places){return sign+number}if(existing_places<dec_places){new_number=number;for(i=existing_places;i<dec_places;i++){new_number+="0"}if(new_number==0){sign=""}return sign+new_number}var end_pos=(dec_point_pos*1)+dec_places;var round_up=false;if((number.charAt(end_pos+1)*1)>4){round_up=true}var digit_array=new Array();for(i=0;i<=end_pos;i++){digit_array[i]=number.charAt(i)}for(i=digit_array.length-1;i>=0;i--){if(digit_array[i]=="."){continue}if(round_up){digit_array[i]++;if(digit_array[i]<10){break}}else{break}}for(i=0;i<=end_pos;i++){if(digit_array[i]=="."||digit_array[i]<10||i==0){new_number+=digit_array[i]}else{new_number+="0"}}if(dec_places==0){new_number=new_number.replace(".","")}if(new_number==0){sign=""}return sign+new_number}

		function supplier_changed()
		{
			var supplier_id = document.getElementById('entity_id').value;
			var supplier_name = document.getElementById('name');
			var payee_address = document.getElementById('payee_address');
			var tax_reference = document.getElementById('tax_reference');
			var reference = document.getElementById('reference');
			var currency = document.getElementById('currency');
            var country = document.getElementById('country');

			switch (supplier_id)
			{
				<?php
				foreach ($suppliers as $supplier)
				{
					if (nbf_common::nb_strlen($supplier->company_name) == 0)
					{
						$supplier_name = $supplier->name;
					}
					else
					{
						$supplier_name = $supplier->company_name;
					}
					echo "case '" . $supplier->id . "':\n";
					echo "  supplier_name.value = '" . $nb_database->getEscaped($supplier_name) . "';\n";
					$payee_address_parts = array();
					if (nbf_common::nb_strlen($supplier->address_1) > 0)
					{
						$payee_address_parts[] = $nb_database->getEscaped($supplier->address_1);
					}
					if (nbf_common::nb_strlen($supplier->address_2) > 0)
					{
						$payee_address_parts[] = $nb_database->getEscaped($supplier->address_2);
					}
					if (nbf_common::nb_strlen($supplier->address_3) > 0)
					{
						$payee_address_parts[] = $nb_database->getEscaped($supplier->address_3);
					}
					if (nbf_common::nb_strlen($supplier->town) > 0)
					{
						$payee_address_parts[] = $nb_database->getEscaped($supplier->town);
					}
					if (nbf_common::nb_strlen($supplier->state) > 0)
					{
						$payee_address_parts[] = $nb_database->getEscaped($supplier->state);
					}
					if (nbf_common::nb_strlen($supplier->postcode) > 0)
					{
						$payee_address_parts[] = $nb_database->getEscaped($supplier->postcode);
					}
					echo "  payee_address.value = '" . implode("\\n", $payee_address_parts) . "';\n";
					echo "  tax_reference.value = '" . $nb_database->getEscaped($supplier->tax_exemption_code) . "';\n";
					echo "  reference.value = '" . $nb_database->getEscaped($supplier->reference) . "';\n";
                    if ($supplier->default_currency) {
                        echo "  currency.value = '" . $supplier->default_currency . "';\n";
                    }
                    echo "  country.value = '" . $supplier->country . "';\n";
					echo "  break;\n";
				}

				echo "default:\n";
				echo "  name.value = '';\n";
				echo "  payee_address.value = '';\n";
				echo "  tax_reference.value = '';\n";
				echo "  reference.value = '';\n";
                echo "  country.value = '" . $vendors[0]->vendor_country . "';\n";
				echo "  break;\n";
				?>
			}
		}

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
                var tax_amount = format_currency(document.getElementById('tax_amount_1').value, <?php echo $config->precision_currency_grand_total; ?>) * 1;
                var net_amount = (format_currency(document.getElementById('amount').value, <?php echo $config->precision_currency_grand_total; ?>) * 1) - tax_amount;
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
        <input type="hidden" name="action" value="expenditure" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $expenditure_id;?>" />
		<input type="hidden" name="removed_items", id="removed_items" value="<?php echo nbf_common::get_param($_POST,'removed_items'); ?>" />
		<input type="hidden" name="added_items", id="added_items" value="<?php echo nbf_common::get_param($_POST,'added_items'); ?>" />
        <input type="hidden" name="document_id" id="document_id" value="<?php echo nbf_common::get_param($_REQUEST, 'document_id'); ?>" />
        <input type="hidden" name="no_record_limit" id="no_record_limit" value="<?php echo nbf_common::get_param($_REQUEST, 'no_record_limit'); ?>" />
        <input type="hidden" name="guessed" id="guessed" value="<?php echo nbf_common::get_param($_REQUEST, 'guessed'); ?>" />
        <input type="hidden" name="transaction_type" value="EX" />
		<?php nbf_html::add_filters();
        if (nbf_common::get_param($_REQUEST, 'guessed'))
        { ?>
            <div style="border:solid 2px #ff0000;padding:3px;font-weight:bold;font-size:1.1em;color:#000000;">
                <?php echo NBILL_EXPENDITURE_LEDGER_GUESSED ?>
            </div>
        <?php }
        ?>

        <table class="adminheading" style="width:auto;">
        <tr>
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "expenditure"); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
                <?php echo $row->id ? NBILL_EDIT_EXPENDITURE . " '$row->transaction_no'" : NBILL_NEW_EXPENDITURE; ?>
            </th>
        </tr>
        </table>

        <div class="rounded-table">
		    <table border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-expenditure">
		    <tr>
			    <th colspan="2"><?php echo NBILL_EXPENDITURE_DETAILS; ?></th>
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
		    <tr id="nbill-admin-tr-payment-no">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_INCOME_PAYMENT_NO; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="transaction_no" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'transaction_no', null, true) : $row->transaction_no; ?>" class="inputbox" style="width:80px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_PAYMENT_NO, "transaction_no_help"); ?>
			    </td>
		    </tr>
            <!-- Custom Fields Placeholder -->
		    <tr id="nbill-admin-tr-related-credits">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_RELATED_CREDITS; ?>
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
								    $selected_invoices[] = nbf_common::get_param($_POST, 'document_id');
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
						    echo "<select name=\"invoices_" . $vendor->id . "[]\" size=\"5\" multiple=\"multiple\" class=\"inputbox\" id=\"invoices_" . $vendor->id . "\" onchange=\"invoice_selected();\" $visibility>";
						    foreach ($invoices[$vendor->id] as $invoice)
						    {
							    echo "<option value=\"" . $invoice->id . "\"";
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
                                <br /><span style="color:#ff0000;font-weight:bold"><?php echo sprintf(NBILL_EXPENDITURE_RECORD_LIMIT_WARNING, nbf_globals::$record_limit, nbf_globals::$record_limit); ?></span><br />
                                <input type="button" class="button btn" name="remove_record_limit" id="remove_record_limit" value="<?php echo NBILL_EXPENDITURE_SHOW_ALL; ?>" onclick="adminForm.no_record_limit.value='1';adminForm.submit();return false;" />
                            <?php }
                        }
                    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_RELATED_CREDITS, "document_id_help"); ?>
                </td>
		    </tr>
		    <tr id="nbill-admin-tr-supplier">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SUPPLIER; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $supplier_list = array();
					    $supplier_list[] = nbf_html::list_option("0", "0 - " . NBILL_NOT_APPLICABLE);
					    foreach ($suppliers as $supplier)
					    {
						    $supplier_name = $supplier->company_name;
						    if (nbf_common::nb_strlen($supplier->company_name) > 0 && nbf_common::nb_strlen(trim($supplier->name)) > 0)
						    {
							    $supplier_name .= " (";
						    }
						    $supplier_name .= $supplier->name;
						    if (nbf_common::nb_strlen($supplier->company_name) > 0 && nbf_common::nb_strlen(trim($supplier->name)) > 0)
						    {
							    $supplier_name .= ")";
						    }
						    $supplier_list[] = nbf_html::list_option($supplier->entity_id, $supplier_name);
					    }
					    echo nbf_html::select_list($supplier_list, "entity_id", 'id="entity_id" onchange="supplier_changed();" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST,'entity_id') : $row->entity_id);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_SUPPLIER, "entity_id_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-payee">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_EXP_PAYEE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="name" id="name" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'name', null, true)) : str_replace("\"", "&quot;", $row->name); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_SUPPLIER_NAME, "name_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-payee-address">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_EXP_PAYEE_ADDRESS; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <textarea name="address" id="payee_address" rows="4" cols="30"><?php echo $use_posted_values ? nbf_common::get_param($_POST,'address', null, true) : $row->address; ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_INSTR_PAYEE_ADDRESS, "payee_address_help"); ?>
			    </td>
		    </tr>
            <tr id="nbill-admin-tr-payee-country">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_PAYEE_COUNTRY; ?>
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
                    <?php nbf_html::show_static_help(NBILL_INSTR_PAYEE_COUNTRY, "country_help"); ?>
                </td>
            </tr>
		    <tr id="nbill-admin-tr-tax-reference">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_EXP_TAX_REFERENCE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="tax_reference" id="tax_reference" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'tax_reference', null, true)) : str_replace("\"", "&quot;", $row->tax_reference); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_EXP_TAX_REFERENCE, "tax_reference_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-for">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_EXPENDITURE_FOR; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="for" id="for" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'for', null, true)) : str_replace("\"", "&quot;", $row->for); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_EXPENDITURE_FOR, "for_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-date-paid">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DATE_PAID; ?>
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
				    <span style="white-space:nowrap"><input type="text" name="date" maxlength="19" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST, 'date') : $date_value; ?>" />
				    <input type="button" name="date_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.date,'<?php echo $cal_date_format; ?>',this);" /></span>
                    <?php nbf_html::show_static_help(NBILL_INSTR_DATE_PAID, "date_help"); ?>
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
				    <?php echo NBILL_AMOUNT_PAID; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="amount" onchange="update_tax_amount();auto_populate_ledger();" id="amount"<?php if (!$row->id) echo " onchange=\"if (document.getElementById('added_items').value.length == 0) {document.getElementById('ledger_new_amount').value = format_currency(document.getElementById('amount').value, " . $config->precision_currency_line_total . ");}\""; ?> value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'amount', null, true) : format_number($row->amount, 'currency_grand'); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_AMOUNT_PAID, "amount_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-tax-rate-and-amount">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_EXP_TAX_RATE_AND_AMOUNT; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <div><?php echo NBILL_EXP_TAX_RATE; ?> <input type="text" name="tax_rate_1" id="tax_rate_1" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'tax_rate_1', null, true) : format_number($row->tax_rate_1, 'tax_rate'); ?>" class="inputbox small-numeric" onchange="update_tax_amount();auto_populate_ledger();" />%&nbsp;&nbsp;
				    <?php echo NBILL_EXP_TAX_AMOUNT; ?> <input type="text" name="tax_amount_1" id="tax_amount_1" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'tax_amount_1', null, true) : format_number($row->tax_amount_1, 'currency_grand'); ?>" class="inputbox small-numeric" />
                    <span class="nbill-electronic-delivery"><?php echo NBILL_TX_ELECTRONIC_DELIVERY; ?> <?php echo nbf_html::yes_or_no_options("tax_rate_1_electronic_delivery", "", $row->tax_rate_1_electronic_delivery); ?></span></div>
                    <div><?php echo NBILL_EXP_TAX_RATE; ?> <input type="text" name="tax_rate_2" id="tax_rate_2" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'tax_rate_2', null, true) : format_number($row->tax_rate_2, 'tax_rate'); ?>" class="inputbox small-numeric" />%&nbsp;&nbsp;
				    <?php echo NBILL_EXP_TAX_AMOUNT; ?> <input type="text" name="tax_amount_2" id="tax_amount_2" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'tax_amount_2', null, true) : format_number($row->tax_amount_2, 'currency_grand'); ?>" class="inputbox small-numeric" />
                    <span class="nbill-electronic-delivery"><?php echo NBILL_TX_ELECTRONIC_DELIVERY; ?> <?php echo nbf_html::yes_or_no_options("tax_rate_2_electronic_delivery", "", $row->tax_rate_2_electronic_delivery); ?></span></div>
                    <div><?php echo NBILL_EXP_TAX_RATE; ?> <input type="text" name="tax_rate_3" id="tax_rate_3" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'tax_rate_3', null, true) : format_number($row->tax_rate_3, 'tax_rate'); ?>" class="inputbox small-numeric" />%&nbsp;&nbsp;
				    <?php echo NBILL_EXP_TAX_AMOUNT; ?> <input type="text" name="tax_amount_3" id="tax_amount_3" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'tax_amount_3', null, true) : format_number($row->tax_amount_3, 'currency_grand'); ?>" class="inputbox small-numeric" />
                    <span class="nbill-electronic-delivery"><?php echo NBILL_TX_ELECTRONIC_DELIVERY; ?> <?php echo nbf_html::yes_or_no_options("tax_rate_3_electronic_delivery", "", $row->tax_rate_3_electronic_delivery); ?></span></div>
                    <?php nbf_html::show_static_help(NBILL_INSTR_EXP_TAX_RATE_AND_AMOUNT . NBILL_INSTR_EXP_TAX_RATE_AND_AMOUNT_ELEC, "tax_amount_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-ledger-breakdown">
			    <td class="nbill-setting-value" colspan="2">
				    <?php echo NBILL_LEDGER_BREAKDOWN; ?>&nbsp;&nbsp; &gt;<a href="#" id="calc_toggle" onclick="if (typeof(calculator_toggle) === 'function'){calculator_toggle()};return false;"><?php echo NBILL_TX_CALC_OFF; ?></a><br />
                    <?php if (nbf_common::get_param($_REQUEST, 'guessed')) {echo "<strong>" . NBILL_EXPENDITURE_LEDGER_PLEASE_CHECK . "</strong>"; } ?>
				    <div id="ledger_table_container" style="padding: 3px;<?php echo nbf_common::get_param($_REQUEST, 'guessed') ? " border: solid 2px #ff0000" : "";?>;">
					    <?php echo $ledger_table_heading . $ledger_table_rows . $ledger_table_new; ?>
				    </div>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-reference">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_PAYMENT_REFERENCE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="reference" id="reference" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'reference', null, true)) : str_replace("\"", "&quot;", $row->reference); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_PAYMENT_REFERENCE, "reference_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-no-summary">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_EXP_NO_SUMMARY; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    echo nbf_html::yes_or_no_options("no_summary", "", $row->no_summary);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_EXP_NO_SUMMARY, "no_summary_help"); ?>
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

        <?php if (file_exists(nbf_cms::$interop->nbill_admin_base_path . '/admin.proc/supporting_docs.php') && $row->id)
        { ?>
            <div id="attachments_<?php echo $row->id; ?>" style="clear:both;">
                <input type="hidden" name="attachment_id" id="attachment_id" value="" />
                <input type="hidden" name="use_posted_values" value="" />
                <table cellpadding="3" cellspacing="0" border="0">
                <?php
                foreach ($attachments as $attachment)
                {
                    ?>
                    <tr class="nbill-admin-tr-attachment">
                    <td>
                        <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=supporting_docs&task=download&file=<?php echo base64_encode($attachment->id); ?>"><img style="vertical-align:middle" border="0" alt="" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file.png" />&nbsp;<?php echo $attachment->file_name; ?></a>
                    </td>
                    <td>
                        <input type="button" class="button btn" value="<?php echo NBILL_DETACH; ?>" onclick="if(confirm('<?php echo NBILL_DETACH_SURE; ?>')){document.adminForm.attachment_id.value='<?php echo $attachment->id; ?>';document.adminForm.task.value='detach_file_edit';document.adminForm.submit();}" />
                    </td>
                    <td>
                        <input type="button" class="button btn" value="<?php echo NBILL_DELETE; ?>" onclick="if(confirm('<?php echo sprintf(NBILL_DELETE_FILE_SURE, $attachment->file_name); ?>')){document.adminForm.attachment_id.value='<?php echo $attachment->id; ?>';document.adminForm.task.value='delete_file_edit';document.adminForm.submit();}" />
                    </td>
                    </tr>
                    <?php
                }
                ?>
                <tr><td colspan="3">
                <a href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&use_stylesheet=1&show_toolbar=1&attach_to_type=EX&attach_to_id=<?php echo $row->id; ?>','','scrollbars=1,width=790,height=500');return false;"><img style="vertical-align:middle" border="0" alt="" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/supporting_docs.gif" />&nbsp;<?php echo NBILL_NEW_ATTACHMENT; ?></a>
                </td></tr>
                </table>
            </div>
        <?php } ?>

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
}