<?php
/**
* HTML output for invoices
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillInvoice
{
    protected static $custom_column_count = 0;

	public static function showInvoices(&$rows, &$pagination, &$vendors, $first_product_description, $cfg_date_format, $orders_present, &$page_totals, &$sum_totals, &$quote_status, $awaiting = null, $attachments = array())
	{
        if (strtolower(nbf_version::$suffix) == 'lite') {
            $orders_present = false;
        }
		$feature = nbf_common::nb_strtoupper(nbf_common::get_param($_REQUEST, 'action'));
		$vendor_col = false;
        $doc_suffix = "";
        $doc_type = "IV";
        switch ($feature)
		{
            case "CREDITS":
			    $doc_suffix = "_CR";
                $doc_type = "CR";
                break;
            case "QUOTES":
                $doc_suffix = "_QU";
                $doc_type = "QU";
                break;
		}
		nbf_html::load_calendar();

        $search_date_from = nbf_common::get_param($_REQUEST,'search_date_from');
        $search_date_to = nbf_common::get_param($_REQUEST,'search_date_to');
        nbf_globals::$vendor_filter = nbf_common::get_param($_POST,'vendor_filter');
        $category_filter = nbf_common::get_param($_POST,'category_filter_' . nbf_globals::$vendor_filter);
        $client_search = nbf_common::get_param($_POST,'client_search');
        $product_search = nbf_common::get_param($_POST,'product_search');
        $nbill_no_search = nbf_common::get_param($_POST,'nbill_no_search');
        $return_url = nbf_cms::$interop->admin_page_prefix . "&action=" . nbf_common::get_param($_REQUEST, 'action') . "&task=view&search_date_from=$search_date_from&search_date_to=$search_date_to&vendor_filter=" . nbf_globals::$vendor_filter . "&category_filter_" . nbf_globals::$vendor_filter . "=$category_filter&client_search=$client_search&product_search=$product_search&nbill_no_search=$nbill_no_search";
        $multi_income_return_url = base64_encode(str_replace('&task=view', '&task=show', $return_url));
        $return_url = base64_encode($return_url);
        $total_net = 0;
        $total_tax = 0;
        $total_gross = 0;
		?>

		<script type="text/javascript">
        function nbill_submit_task(task_name)
        {
            var form = document.adminForm;
            form.task.value=task_name;
            form.submit();
        }
		function show_all_outstanding()
		{
			if (document.getElementById('all_outstanding').checked)
			{
				nbill_submit_task('all_outstanding');
			}
			else
			{
				//Re-enable date range
				document.getElementById('search_date_from').disabled = false;
				document.getElementById('search_date_to').disabled = false;
				document.getElementById('search_date_from_cal').disabled = false;
				document.getElementById('search_date_to_cal').disabled = false;
			}
		}
		</script>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, nbf_common::get_param($_REQUEST, 'action')); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . @constant("NBILL_$feature" . "_TITLE"); ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . str_replace("\n\n", "<br /><br />", nbf_globals::$message) . "</div>";
		} ?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
        <input type="hidden" name="task" value="<?php echo nbf_common::get_param($_REQUEST, 'task'); ?>" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
        <input type="hidden" name="order_id" value="<?php if (nbf_common::get_param($_REQUEST, 'task') == 'viewfororder') {echo nbf_common::get_param($_REQUEST, 'order_id');} ?>">
        <input type="hidden" name="attachment_id" value="" />

        <p align="left"><?php echo @constant("NBILL_$feature" . "_INTRO"); ?></p>

        <?php if ($doc_suffix == "")
        { ?>
            <div style="float:right">
                <strong><?php echo NBILL_MULTI_INVOICE_UPDATE; ?></strong><br />
                <?php echo NBILL_MARK_INVOICES_AS . '&nbsp;<br />';
                $status_array = array();
                $status_array[] = nbf_html::list_option('', NBILL_NOT_APPLICABLE);
                $status_array[] = nbf_html::list_option('PAID', NBILL_MULTI_PAID_SINGLE);
                $status_array[] = nbf_html::list_option('PAID_MULTIPLE', NBILL_MULTI_PAID_MULTIPLE);
                $status_array[] = nbf_html::list_option('WO', NBILL_INVOICE_LBL_WRITTEN_OFF);
                echo nbf_html::select_list($status_array, "multi_invoice_update", 'id="multi_invoice_update" class="inputbox"', ''); ?>
                <input type="hidden" name="multi_invoice_update_submit" id="multi_invoice_update_submit" value="" />
                <input type="hidden" name="multi_paid_return_url" value="<?php echo $multi_income_return_url; ?>" />
                <input type="button" class="btn button nbill-button" name="multi_invoice_update_submit_button" value="<?php echo NBILL_GO; ?>" onclick="if(document.getElementById('multi_invoice_update').value.length==0){alert('<?php echo NBILL_MULTI_INVOICE_SELECT; ?>');return false;}else{if(document.adminForm.box_checked.value == 0){alert('<?php echo NBILL_MULTI_INVOICE_SELECT_RECORDS; ?>');return false;}else{if(document.getElementById('multi_invoice_update').value=='PAID_MULTIPLE' || confirm('<?php echo NBILL_MULTI_INVOICE_SURE; ?>')){document.getElementById('multi_invoice_update_submit').value='1';document.adminForm.submit();}}}" />
            </div>
        <?php } ?>

		<p align="left">
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
				$vendor_name[] = nbf_html::list_option(-999, NBILL_ALL);
				foreach ($vendors as $vendor)
				{
					$vendor_name[] = nbf_html::list_option($vendor->id, $vendor->vendor_name);
				}
				echo nbf_html::select_list($vendor_name, "vendor_filter", 'id="vendor_filter" class="inputbox" onchange="nbill_submit_task();"', $selected_filter );
				$_POST['vendor_filter'] = $selected_filter;
			}
			else
			{
				echo "<input type=\"hidden\" name=\"vendor_filter\" id=\"vendor_filter\" value=\"" . $vendors[0]->id . "\" />";
				$_POST['vendor_filter'] = $vendors[0]->id;
			}

			?>
            <span style="white-space:nowrap;"><?php echo "&nbsp;&nbsp;" . @constant("NBILL_INVOICE_NUMBER$doc_suffix");?>&nbsp;<input type="text" id="invoice_no_search" name="nbill_no_search" value="<?php echo nbf_common::get_param($_POST,'nbill_no_search', '', true); ?>" /></span>
			<span style="white-space:nowrap;"><?php echo "&nbsp;&nbsp;" . NBILL_CLIENT;?>&nbsp;<input type="text" name="client_search" value="<?php echo nbf_common::get_param($_POST,'client_search', '', true); ?>" /></span>
            <span style="white-space:nowrap;"><?php echo "&nbsp;&nbsp;" . NBILL_DOC_DESCRIPTION;?>&nbsp;<input type="text" name="description_search" value="<?php echo nbf_common::get_param($_POST,'description_search', '', true); ?>" /></span>
            <?php if ($doc_suffix == "_QU")
            {
                //Allow filter by quote status
                $status_list = array();
                $status_list[] = nbf_html::list_option("", NBILL_ALL);
                foreach ($quote_status as $status)
                {
                    $status_list[] = nbf_html::list_option($status->code, $status->description);
                }
                ?><span style="white-space:nowrap;"><?php echo "&nbsp;&nbsp;" . NBILL_QUOTE_STATUS . " " . nbf_html::select_list($status_list, "quote_status", "", nbf_common::get_param($_REQUEST, 'quote_status', '', true)); ?></span><?php
            }
			?>
            <span style="white-space:nowrap;"><?php echo "&nbsp;&nbsp;" . NBILL_DATE_RANGE; $cal_date_format = nbf_common::get_date_format(true); ?>
			<input type="text" name="search_date_from" id="search_date_from" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_from'); ?>" <?php if (nbf_common::get_param($_REQUEST, 'show_all')) {echo "disabled=\"disabled\"";} ?> />
			<input type="button" name="search_date_from_cal" id="search_date_from_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.search_date_from,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_REQUEST, 'show_all')) {echo "disabled=\"disabled\"";} ?> /></span>
			<span style="white-space:nowrap;"><?php echo NBILL_TO; ?>
			<input type="text" name="search_date_to" id="search_date_to" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_to'); ?>" <?php if (nbf_common::get_param($_REQUEST, 'show_all')) {echo "disabled=\"disabled\"";} ?> />
			<input type="button" name="search_date_to_cal" id="search_date_to_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.search_date_to,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_REQUEST, 'show_all')) {echo "disabled=\"disabled\"";} ?> /></span>
			<?php if ($doc_suffix == "")
            {
                ?>
                <span style="white-space:nowrap;"><input type="checkbox" class="nbill_form_input" name="all_outstanding" id="all_outstanding"<?php if (nbf_common::get_param($_REQUEST, 'all_outstanding')){echo " checked=\"checked\"";} ?> /><label for="all_outstanding" class="nbill_form_label"><?php echo NBILL_INVOICE_SHOW_ALL_UNPAID; ?></label></span>
                <?php
            }
            ?>
            <input type="submit" class="btn button nbill-button" name="<?php echo nbf_common::get_param($_REQUEST, 'show_all') ? 'show_reset' : 'show_all'; ?>" value="<?php echo nbf_common::get_param($_REQUEST, 'show_all') ? NBILL_INVOICE_SHOW_RESET : NBILL_INVOICE_SHOW_ALL; ?>" onclick="document.adminForm.task.value='';return true;" />
            <?php if (nbf_common::get_param($_REQUEST, 'show_all')) {echo "<input type=\"hidden\" class=\"btn button nbill-button\" name=\"show_all\" value=\"1\" />";} ?>
            <input type="submit" class="btn button nbill-button" name="dosearch" id="dosearch" value="<?php echo NBILL_GO; ?>" onclick="document.adminForm.task.value='';return true;" />
            <input type="hidden" name="do_csv_download" id="do_csv_download" value="" />
            <?php
            if ($pagination->record_count > nbf_globals::$record_limit)
            {
                $csv_click = "if (confirm('" . sprintf(NBILL_CSV_EXPORT_LIMIT_WARNING, nbf_globals::$record_limit, nbf_globals::$record_limit, nbf_globals::$record_limit) . "')){document.getElementById('do_csv_download').value=1;document.adminForm.submit();document.getElementById('do_csv_download').value='';}return false;";
            }
            else
            {
                $csv_click = "document.getElementById('do_csv_download').value=1;document.adminForm.submit();document.getElementById('do_csv_download').value='';return false;";
            }
            ?>
            &nbsp;
            <span style="white-space:nowrap;"><a href="#" title="<?php echo NBILL_CSV_DOWNLOAD_LIST_DESC; ?>" onclick="<?php echo $csv_click; ?>"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path ?>/images/icons/medium/csv.gif" alt="<?php echo NBILL_CSV_DOWNLOAD_LIST_DESC; ?>" style="vertical-align:middle" /></a>
            <strong><a href="#" title="<?php echo NBILL_CSV_DOWNLOAD_LIST_DESC; ?>" onclick="<?php echo $csv_click; ?>"><?php echo NBILL_CSV_DOWNLOAD; ?></a></strong></span>
		</p>

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
			    <th class="title" colspan="2">
				    <?php echo @constant("NBILL_INVOICE_NUMBER$doc_suffix"); ?>
			    </th>
                <?php self::renderCustomColumn('document_no'); ?>
			    <th class="title">
				    <?php echo NBILL_CLIENT . ($doc_suffix != "_QU" ? " / " . @constant("NBILL_BILLING_NAME$doc_suffix") : ""); ?>
			    </th>
                <?php self::renderCustomColumn('client'); ?>
			    <th class="title responsive-cell optional">
				    <?php echo @constant("NBILL_INVOICE_DATE$doc_suffix"); ?>
			    </th>
                <?php self::renderCustomColumn('document_date'); ?>
			    <th class="title responsive-cell wide-only">
				    <?php echo @constant("NBILL_FIRST_ITEM$doc_suffix"); ?>
			    </th>
                <?php self::renderCustomColumn('first_item'); ?>
                <th class="title responsive-cell extra-wide-only" style="text-align:right;">
				    <?php echo NBILL_TOTAL_NET; ?>
			    </th>
                <?php self::renderCustomColumn('total_net'); ?>
                <th class="title responsive-cell extra-wide-only" style="text-align:right;">
                    <?php echo NBILL_TOTAL_TAX; ?>
                </th>
                <?php self::renderCustomColumn('total_tax'); ?>
			    <th class="title" style="text-align:right;">
				    <?php echo NBILL_TOTAL_GROSS; ?>
			    </th>
                <?php self::renderCustomColumn('total_gross'); ?>
                <?php
                switch ($doc_suffix)
                {
                    
                    default:
                        ?>
			            <th class="selector responsive-cell high-priority">
				            <?php echo NBILL_INVOICE_PAY_STATUS; ?>
			            </th>
                        <?php self::renderCustomColumn('payment_status'); ?>
                        <?php
                        break;
                } ?>
			    <th class="selector responsive-cell high-priority">
				    <?php echo NBILL_INVOICE_EMAILED; ?>
			    </th>
                <?php self::renderCustomColumn('invoice_emailed'); ?>
			    <?php
				    //Only show vendor name if more than one listed
				    if (count($vendors) > 1 && $selected_filter == -999)
				    {?>
					    <th class="title responsive-cell priority">
						    <?php echo NBILL_VENDOR_NAME; ?>
					    </th>
                        <?php self::renderCustomColumn('vendor'); ?>
				        <?php
                        $vendor_col = true;
                    }
			    ?>
		    </tr>
		    <?php
		    for ($i=0, $n=count( $rows ); $i < $n; $i++)
		    {
			    $row = &$rows[$i];

			    $img = $row->paid_in_full ? 'tick.png' : ($row->partial_payment ? 'partial.png' : 'cross.png');
			    $task = $row->paid_in_full ? 'notpaid' : 'paid';
			    $alt = $row->paid_in_full ? NBILL_DOCUMENT_PAID : ($row->partial_payment ? NBILL_DOCUMENT_PART_PAID : NBILL_DOCUMENT_NOT_PAID);
			    $link = nbf_cms::$interop->admin_page_prefix . "&action=" . nbf_common::get_param($_REQUEST, 'action') . "&task=edit&cid=$row->id&search_date_from=$search_date_from&search_date_to=$search_date_to&vendor_filter=" . nbf_globals::$vendor_filter . "&category_filter_" . nbf_globals::$vendor_filter . "=$category_filter&client_search=$client_search&product_search=$product_search&nbill_no_search=$nbill_no_search";

                $total_net += $row->total_net;
                $total_tax += $row->total_tax;
                $total_gross += $row->total_gross;

			    echo "<tr>";
			    echo "<td class=\"selector\">";
			    echo $pagination->list_offset + $i + 1;
			    $checked = nbf_html::id_checkbox($i, $row->id);
			    if ($row->written_off)
			    {
				    $tdstyle = " style=\"text-decoration: line-through;\"";
			    }
			    else
			    {
				    $tdstyle = "";
			    }
			    echo "</td><td class=\"selector\">$checked</td>";
                self::renderCustomColumn('id', $row);
			    echo "<td class=\"list-value\"$tdstyle><span style=\"white-space:nowrap;\"><a href=\"$link\" title=\"" . @constant("NBILL_EDIT_INVOICE$doc_suffix") . "\">" . $row->document_no . "</a></span></td>";
                self::renderCustomColumn('document_no', $row);
			    echo "<td class=\"list-value\"";
                if ($row->written_off)
                {
                    echo "text-decoration: line-through;";
                }
                echo "\"><div style=\"float:left;\">";
                echo "<a href=\"#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=" . nbf_common::get_param($_REQUEST, 'action') . "&task=printpreviewpopup&hidemainmenu=1&items=" . $row->id . "', '" . uniqid() . "', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;\" title=\"" . NBILL_PRINT . "\"><img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/preview.gif\" alt=\"" . NBILL_PRINT . "\" border=\"0\" style=\"vertical-align:middle;\" /></a>";
			    if (nbf_common::pdf_writer_available()) {
				    echo "&nbsp;&nbsp;<a href=\"#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=" . nbf_common::get_param($_REQUEST, 'action') . "&task=pdfpopup&hidemainmenu=1&items=" . $row->id . "', '" . uniqid() . "', 'width=800,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;\" title=\"" . NBILL_PDF . "\"><img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/pdf.png\" alt=\"" . NBILL_PDF . "\" border=\"0\" style=\"vertical-align:middle;\" /></a>";
			    }
                if ($doc_suffix == '') {
                    echo "&nbsp;&nbsp;<a href=\"#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=" . nbf_common::get_param($_REQUEST, 'action') . "&task=deliverynotepopup&hidemainmenu=1&items=" . $row->id . "', '" . uniqid() . "', 'width=800,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;\" title=\"" . NBILL_DELIVERY_NOTE . "\"><img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/delivery_note.png\" alt=\"" . NBILL_DELIVERY_NOTE . "\" border=\"0\" style=\"vertical-align:middle;\" /></a>";
                }

                
                if ($row->paid_in_full || $row->partial_payment) {
                    if (strtolower(nbf_version::$suffix) != 'lite' && nbf_common::get_param($_REQUEST, 'action') == "credits") {
                        //Link to expenditure items
                        echo " <a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=expenditure&for_credit_note=" . $row->id . "\" title=\"" . NBILL_SHOW_EXPENDITURE_RECORDS . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/expenditure.gif\" alt=\"" . NBILL_SHOW_EXPENDITURE_RECORDS . "\" style=\"vertical-align:middle;\" /></a>";
                    } else if (nbf_common::get_param($_REQUEST, 'action') != "credits") {
                        //Link to income items
                        if (!$row->order_count && $orders_present) {
                            echo "<span style=\"display:inline-block;width:19px;\">&nbsp;</span>";
                        }
                        echo " <a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=income&for_invoice=" . $row->id . "\" title=\"" . NBILL_SHOW_INCOME_RECORDS . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/income.gif\" alt=\"" . NBILL_SHOW_INCOME_RECORDS . "\" style=\"vertical-align:middle;\" /></a>";
                    }
                }
                if ($doc_type == "IV") {
                    //Issue refund
                    if (!$row->order_count && $orders_present) {
                        echo "<span style=\"display:inline-block;width:19px;\">&nbsp;</span>";
                    }
                    echo " <a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=credits&task=new&for_invoice=" . $row->id . "\" title=\"" . NBILL_REFUND_THIS_INVOICE . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/new_refund.gif\" alt=\"" . NBILL_REFUND_THIS_INVOICE . "\" style=\"vertical-align:middle;\" /></a>";
                }

                
                echo "</td>";
			    echo "<td class=\"list-value\"$tdstyle>";
                $billing_name = $row->billing_name;
                if ($row->entity_id > 0 && (nbf_common::nb_strlen($row->company_name) > 0 || nbf_common::nb_strlen($row->contact_name) > 0))
                {
                    $billing_name = "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=" . ($row->is_client ? "clients" : ($row->is_supplier ? "suppliers" : "potential_clients")) . "&task=edit&cid=" . $row->entity_id . "&return=" . $return_url . "\">";
                    if (nbf_common::nb_strlen($row->company_name) > 0)
                    {
                        $billing_name .= $row->company_name;
                        if (nbf_common::nb_strlen($row->contact_name) > 0)
                        {
                            $billing_name .= " (" . $row->contact_name . ")";
                        }
                    }
                    else
                    {
                        $billing_name .= $row->contact_name;
                    }
                    $billing_name .= "</a>";
                }
                echo $billing_name;
                echo "</td>";
                self::renderCustomColumn('client', $row);
			    echo "<td class=\"list-value responsive-cell optional\"$tdstyle>" . nbf_common::nb_date($cfg_date_format, $row->document_date) . "</td>";
                self::renderCustomColumn('document_date', $row);
			    $first_desc = "";
                $section_found = false;
			    foreach ($first_product_description as $descriptions)
			    {
                    if ($descriptions->document_id == $row->id)
				    {
                        if ($descriptions->section_name)
                        {
                            $first_desc = $descriptions->section_name;
                            $section_found = true;
                            break;
                        }
                    }
                }
                if (!$section_found)
                {
                    foreach ($first_product_description as $descriptions)
                    {
                        if ($descriptions->document_id == $row->id)
                        {
					        $first_desc = $descriptions->product_description;
                            break;
                        }
				    }
			    }
			    echo "<td class=\"list-value responsive-cell wide-only word-breakable\"$tdstyle>" . $first_desc . "</td>";
                self::renderCustomColumn('first_item', $row);
			    echo "<td$tdstyle class=\"list-value responsive-cell extra-wide-only\" style=\"text-align:right;white-space:nowrap;\">" . format_number($row->total_net, 'currency_grand', true, false, null, $row->currency) . "</td>";
                self::renderCustomColumn('total_net', $row);
                echo "<td$tdstyle class=\"list-value responsive-cell extra-wide-only\" style=\"text-align:right;white-space:nowrap;\">" . format_number($row->total_tax, 'currency_grand', true, false, null, $row->currency) . "</td>";
                self::renderCustomColumn('total_tax', $row);
			    echo "<td$tdstyle class=\"list-value\" style=\"text-align:right;white-space:nowrap;\">" . format_number($row->total_gross, 'currency_grand', true, false, null, $row->currency) . "</td>";
                self::renderCustomColumn('total_gross', $row);
                
                if ($doc_suffix != '_QU') {
                    echo "<td class=\"selector responsive-cell high-priority\">";
				    if ($row->written_off)
				    {
					    echo NBILL_INVOICE_LBL_WRITTEN_OFF;
				    }
				    else
				    {
					    if (!$row->paid_in_full)
					    {
						    $payment_action = nbf_common::get_param($_REQUEST, 'action') == "credits" ? "expenditure" : "income";
						    echo "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=$payment_action&task=edit&document_id=" . $row->id . "&return=$return_url&search_date_from=$search_date_from&search_date_to=$search_date_to&vendor_filter=" . nbf_globals::$vendor_filter . "&category_filter_" . nbf_globals::$vendor_filter . "=$category_filter&client_search=$client_search&product_search=$product_search&nbill_no_search=$nbill_no_search\">";
					    }
					    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/$img\" border=\"0\" alt=\"$alt\" title=\"$alt\" />";
					    if (!$row->paid_in_full)
					    {
						    echo "</a>";
					    }
                    }
				    echo "</td>";
                    self::renderCustomColumn('payment_status', $row);
                }
			    echo "<td class=\"selector responsive-cell high-priority\" style=\"white-space:nowrap;\">";
			    $nbill_email = $row->email_sent;
                echo "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=email_log&task=view&for_document=" . $row->id . "\">";
			    if ($nbill_email == -1)
			    {
				    //E-mail attempted, but e-mail address not found (invoice might not be associated with a client)
				    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/email-fail.gif\" border=\"0\" alt=\"" . NBILL_EMAIL_FAILED_NO_ADDRESS . "\" title=\"" . NBILL_EMAIL_FAILED_NO_ADDRESS . "\" />";
			    }
			    else if ($nbill_email < 0)
			    {
				    //E-mail attempted, but failed
				    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/email-fail.gif\" border=\"0\" alt=\"" . NBILL_EMAIL_FAILED . "\" title=\"" . NBILL_EMAIL_FAILED . "\" />";
			    }
			    else if ($nbill_email > 1000)
			    {
				    //E-mail sent successfully
				    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/email-sent.gif\" border=\"0\" alt=\"" . sprintf(NBILL_EMAIL_SENT, nbf_common::nb_date("d/m/Y H:i:s", $nbill_email)) . "\" title=\"" . sprintf(NBILL_EMAIL_SENT, nbf_common::nb_date("d/m/Y H:i:s", $nbill_email)) . "\" />";
			    }
			    else if ($row->order_count > 0)
			    {
				    //E-mail not due (client record indicates no)
				    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/email-not-sent.gif\" border=\"0\" alt=\"" . NBILL_EMAIL_NOT_DUE . "\" title=\"" . NBILL_EMAIL_NOT_DUE . "\" />";
			    }
                else
                {
                    //E-mail not due (ad-hoc invoice)
                    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/email-not-sent.gif\" border=\"0\" alt=\"" . NBILL_EMAIL_NOT_DUE_ADHOC . "\" title=\"" . NBILL_EMAIL_NOT_DUE_ADHOC . "\" />";
                }
                echo "</a>";
			    //Button to e-mail now...
			    echo "&nbsp;<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&hide_billing_menu=1&action=" . nbf_common::get_param($_REQUEST, 'action') . "&task=emailinvoice&document_id=" . $row->id . "&return=$return_url\" title=\"" . @constant("NBILL_EMAIL_NOW$doc_suffix") . "\"><img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/email-now.gif\" border=\"0\" alt=\"" . @constant("NBILL_EMAIL_NOW$doc_suffix") . "\" /></a>";
			    echo "</td>";
                self::renderCustomColumn('invoice_emailed', $row);
			    //Only show vendor name if more than one listed
			    $vendor_col = false;
			    if (count($vendors) > 1 && $selected_filter == -999)
			    {
				    foreach ($vendors as $vendor)
				    {
					    if ($vendor->id == $row->vendor_id)
					    {
						    echo "<td class=\"responsive-cell priority\">" . $vendor->vendor_name . "</td>";
                            self::renderCustomColumn('vendor', $row);
						    $vendor_col = true;
						    break;
					    }
				    }
			    }
			    echo "</tr>";
		    }

            //Colspans will not be perfect on narrow screens, but we'll do the best we reasonably can
            $extra_wide_colspan = ($vendor_col ? 8 : 7) + self::$custom_column_count;
            $narrow_colspan = ($vendor_col ? 6 : 5) + self::$custom_column_count;
            $extra_wide_end_colspan = ($vendor_col ? 3 : 2) + self::$custom_column_count;
            $narrow_end_colspan = ($vendor_col ? 5 : 4) + self::$custom_column_count;
            ob_start();
		    ?>
            <tr class="##TABLEROW_CLASS##">
                <td colspan="##COLSPAN##" style="font-weight:bold"><?php echo @constant("NBILL_INVOICE_TOTAL_THIS_PAGE$doc_suffix"); ?></td>
                <td class="responsive-cell extra-wide-only" style="font-weight:bold;text-align:right;white-space:nowrap;">
                    <?php for ($i=0; $i<count($page_totals); $i++)
                    {
                        if ($i>0){echo "<br />";}
                        echo format_number($page_totals[$i]->total_net_page, 'currency_grand', true, false, null, $page_totals[$i]->currency);
                    }?>
                </td>
                <td class="responsive-cell extra-wide-only" style="font-weight:bold;text-align:right;white-space:nowrap;">
                    <?php for ($i=0; $i<count($page_totals); $i++)
                    {
                        if ($i>0){echo "<br />";}
                        echo format_number($page_totals[$i]->total_tax_page, 'currency_grand', true, false, null, $page_totals[$i]->currency);
                    }?>
                </td>
                <td style="font-weight:bold;text-align:right;">
                    <?php for ($i=0; $i<count($page_totals); $i++)
                    {
                        if ($i>0){echo "<br />";}
                        echo format_number($page_totals[$i]->total_gross_page, 'currency_grand', true, false, null, $page_totals[$i]->currency);
                    }?>
                </td>
                <?php if ($doc_type == "QU")
                {
                    ?>
                    <td class="responsive-cell high-priority">&nbsp;</td>
                    <?php
                } ?>
                <td colspan="##END_COLSPAN##" class="responsive-cell high-priority">&nbsp;</td>
            </tr>
            <tr class="##TABLEROW_CLASS## responsive-cell high-priority">
                <td colspan="##COLSPAN##" style="background-color:#dddddd;font-weight:bold"><?php echo @constant("NBILL_INVOICE_TOTAL_ALL_PAGES$doc_suffix"); ?></td>
                <td class="responsive-cell extra-wide-only" style="background-color:#dddddd;font-weight:bold;text-align:right;white-space:nowrap;">
                    <?php for ($i=0; $i<count($sum_totals); $i++)
                    {
                        if ($i>0){echo "<br />";}
                        echo format_number($sum_totals[$i]->total_net_all, 'currency_grand', true, false, null, $sum_totals[$i]->currency);
                    }?>
                </td>
                <td class="responsive-cell extra-wide-only" style="background-color:#dddddd;font-weight:bold;text-align:right;white-space:nowrap;">
                    <?php for ($i=0; $i<count($sum_totals); $i++)
                    {
                        if ($i>0){echo "<br />";}
                        echo format_number($sum_totals[$i]->total_tax_all, 'currency_grand', true, false, null, $sum_totals[$i]->currency);
                    }?>
                </td>
                <td style="background-color:#dddddd;font-weight:bold;text-align:right;white-space:nowrap;">
                    <?php for ($i=0; $i<count($sum_totals); $i++)
                    {
                        if ($i>0){echo "<br />";}
                        echo format_number($sum_totals[$i]->total_gross_all, 'currency_grand', true, false, null, $sum_totals[$i]->currency);
                    }?>
                </td>
                <?php if ($doc_type == "QU")
                { ?>
                    <td>&nbsp;</td>
                <?php } ?>
                <td style="background-color:#dddddd;" colspan="##END_COLSPAN##">&nbsp;</td>
            </tr>

            <?php
            $table_ending = ob_get_clean();

            $extra_wide_output = str_replace("##COLSPAN##", $extra_wide_colspan, $table_ending);
            $extra_wide_output = str_replace("##END_COLSPAN##", $extra_wide_end_colspan, $extra_wide_output);
            $extra_wide_output = str_replace("##TABLEROW_CLASS##", "responsive-cell extra-wide-only", $extra_wide_output);
            echo $extra_wide_output;

            $narrow_output = str_replace("##COLSPAN##", $narrow_colspan, $table_ending);
            $narrow_output = str_replace("##END_COLSPAN##", $narrow_end_colspan, $narrow_output);
            $narrow_output = str_replace("##TABLEROW_CLASS##", "responsive-cell narrow-only", $narrow_output);
            echo $narrow_output;

            $footer_colspan = ($doc_type == "QU" ? 13 : 12) + self::$custom_column_count;
            if ($vendor_col) {$footer_colspan++;}
            ?>

            <tr class="nbill_tr_no_highlight"><td colspan="<?php echo $footer_colspan; ?>" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		</form>
		<?php
	}

    protected static function renderCustomColumn($column_name, $row = 'undefined')
    {
        $method = ($row == 'undefined') ? 'render_header' : 'render_row';
        if (file_exists(dirname(__FILE__) . "/custom_columns/invoices/after_$column_name.php")) {
            include_once(dirname(__FILE__) . "/custom_columns/invoices/after_$column_name.php");
            if (is_callable(array("nbill_admin_invoices_after_$column_name", $method))) {
                call_user_func(array("nbill_admin_invoices_after_$column_name", $method), $row);
                if ($method == 'render_header') {
                    self::$custom_column_count++;
                }
            }
        }
    }

    public static function downloadDocumentListCSV($vendors, $rows, $document_items, $date_format, $max_items_per_invoice)
    {
        nbf_common::load_language("contacts");
        $feature = nbf_common::nb_strtoupper(nbf_common::get_param($_REQUEST, 'action'));
        $doc_suffix = "";
        switch ($feature)
        {
            case "CREDITS":
                $doc_suffix = "_CR";
                break;
            case "QUOTES":
                $doc_suffix = "_QU";
                break;
        }

        $selected_filter = $vendors[0]->id;
        if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'vendor_filter')) > 0)
        {
            $selected_filter = nbf_common::get_param($_POST, 'vendor_filter');
        }

        if (count($vendors) > 1 && $selected_filter == -999)
        {
            echo NBILL_VENDOR_NAME . ",";
        }
        echo NBILL_ID . ",";
        echo @constant("NBILL_INVOICE_NUMBER$doc_suffix") . ",";
        echo NBILL_CLIENT . ($doc_suffix != "_QU" ? "/" . @constant("NBILL_BILLING_NAME$doc_suffix") : "") . ",";
        echo NBILL_CSV_COMPANY_NAME . ",";
        echo NBILL_CONTACT_FIRST_NAME . ",";
        echo NBILL_CONTACT_LAST_NAME . ",";
        echo @constant("NBILL_BILLING_ADDRESS$doc_suffix") . ",";
        echo NBILL_EMAIL_ADDRESS . ",";
        echo NBILL_TELEPHONE . ",";
        echo NBILL_MOBILE . ",";
        echo NBILL_FAX . ",";
        echo @constant("NBILL_INVOICE_DATE$doc_suffix") . ",";
        echo NBILL_TOTAL_NET . ",";
        echo NBILL_TOTAL_TAX . ",";
        echo NBILL_TOTAL_GROSS . ",";
        echo NBILL_REFERENCE . ",";
        switch ($doc_suffix)
        {
            case "_QU":
                echo NBILL_QUOTE_STATUS . ",";
                break;
            default:
                echo NBILL_INVOICE_PAY_STATUS . ",";
                break;
        }
        echo NBILL_INVOICE_EMAILED . ",";
        for ($i = 1; $i <= $max_items_per_invoice; $i++)
        {
            echo NBILL_INVOICE_ITEM_CODE . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            echo NBILL_INVOICE_ITEM_NAME . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            echo NBILL_INVOICE_ITEM_LEDGER . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            echo NBILL_INVOICE_ITEM_NET_PRICE . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            echo NBILL_INVOICE_ITEM_QTY . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            echo NBILL_INVOICE_ITEM_DISCOUNT_DESC . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            echo NBILL_INVOICE_ITEM_DISCOUNT_AMOUNT . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            echo NBILL_INVOICE_ITEM_TOTAL_NET . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            echo NBILL_INVOICE_ITEM_TAX_RATE . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            echo NBILL_INVOICE_ITEM_TAX . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            echo NBILL_INVOICE_ITEM_SHIPPING_SERVICE . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            echo NBILL_INVOICE_ITEM_SHIPPING . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            echo NBILL_INVOICE_ITEM_SHIPPING_TAX_RATE . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            echo NBILL_INVOICE_ITEM_SHIPPING_TAX . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            echo NBILL_INVOICE_ITEM_GROSS . sprintf(NBILL_CSV_ITEM_NO, $i) . ",";
            
        }

        echo "\r\n";

        foreach ($rows as $row)
        {
            if (count($vendors) > 1 && $selected_filter == -999)
            {
                foreach ($vendors as $vendor)
                {
                    if ($vendor->id == $row->vendor_id)
                    {
                        echo "\"" . str_replace("\"", "\"\"", $vendor->vendor_name) . "\",";
                        break;
                    }
                }
            }
            echo $row->id . ",";
            echo "\"" . str_replace("\"", "\"\"", $row->document_no) . "\",";
            $billing_name = $row->billing_name;
            if (!$billing_name && $row->entity_id > 0 && (nbf_common::nb_strlen($row->company_name) > 0 || nbf_common::nb_strlen($row->contact_name) > 0))
            {
                if (nbf_common::nb_strlen($row->company_name) > 0)
                {
                    $billing_name = $row->company_name;
                    if (nbf_common::nb_strlen($row->contact_name) > 0)
                    {
                        $billing_name .= " (" . $row->contact_name . ")";
                    }
                }
                else
                {
                    $billing_name = $row->contact_name;
                }
            }
            echo "\"" . str_replace("\"", "\"\"", $billing_name) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->company_name) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->first_name) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->last_name) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->billing_address) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->email_address) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->telephone) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->mobile) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->fax) . "\",";
            echo nbf_common::nb_date($date_format, $row->document_date) . ",";
            echo format_number($row->total_net, 'currency_grand') . ",";
            echo format_number($row->total_tax, 'currency_grand') . ",";
            echo format_number($row->total_gross, 'currency_grand') . ",";
            echo "\"" . str_replace("\"", "\"\"", $row->reference) . "\",";
            echo "\"";
            if ($feature == "QUOTES")
            {
                echo str_replace("\"", "\"\"", @constant($row->quote_status_desc));
            }
            else
            {
                if ($row->written_off)
                {
                    echo str_replace("\"", "\"\"", NBILL_INVOICE_LBL_WRITTEN_OFF);
                }
                else
                {
                    echo str_replace("\"", "\"\"", ($row->paid_in_full ? NBILL_DOCUMENT_PAID : ($row->partial_payment ? NBILL_DOCUMENT_PART_PAID : NBILL_DOCUMENT_NOT_PAID)));
                }
            }
            echo "\",\"";
            $nbill_email = $row->email_sent;
            if ($nbill_email == -1)
            {
                //E-mail attempted, but e-mail address not found (invoice might not be associated with a client)
                echo str_replace("\"", "\"\"", NBILL_EMAIL_FAILED_NO_ADDRESS);
            }
            else if ($nbill_email < 0)
            {
                //E-mail attempted, but failed
                echo str_replace("\"", "\"\"", NBILL_EMAIL_FAILED);
            }
            else if ($nbill_email > 1000)
            {
                //E-mail sent successfully
                echo str_replace("\"", "\"\"", sprintf(NBILL_EMAIL_SENT, nbf_common::nb_date("d/m/Y H:i:s", $nbill_email)));
            }
            else if ($row->order_count > 0)
            {
                //E-mail not due (client record indicates no)
                echo str_replace("\"", "\"\"", NBILL_EMAIL_NOT_DUE);
            }
            else
            {
                //E-mail not due (ad-hoc invoice)
                echo str_replace("\"", "\"\"", NBILL_EMAIL_NOT_DUE_ADHOC);
            }
            echo "\",";
            $i = 0;
            foreach ($document_items as $document_item)
            {
                if ($document_item->document_id == $row->id)
                {
                    $i++;
                    echo "\"" . str_replace("\"", "\"\"", $document_item->product_code) . "\",";
                    echo "\"" . str_replace("\"", "\"\"", $document_item->product_description) . "\",";
                    echo "\"" . str_replace("\"", "\"\"", $document_item->nominal_ledger_code) . "\",";
                    echo format_number($document_item->net_price_per_unit, 'currency') . ",";
                    echo format_number($document_item->no_of_units, 'quantity') . ",";
                    echo "\"" . str_replace("\"", "\"\"", $document_item->discount_description) . "\",";
                    echo format_number($document_item->discount_amount, 'currency') . ",";
                    echo format_number($document_item->net_price_for_item, 'currency_line') . ",";
                    echo format_number($document_item->tax_rate_for_item, 'tax_rate') . ",";
                    echo format_number($document_item->tax_for_item, 'currency_line') . ",";
                    echo "\"" . str_replace("\"", "\"\"", $document_item->shipping_service) . "\",";
                    echo format_number($document_item->shipping_for_item, 'currency_line') . ",";
                    echo format_number($document_item->tax_rate_for_shipping, 'tax_rate') . ",";
                    echo format_number($document_item->tax_for_shipping, 'currency_line') . ",";
                    echo format_number($document_item->gross_price_for_item, 'currency_line');
                    
                    if ($i != $max_items_per_invoice)
                    {
                        echo ",";
                    }
                }
            }
            //Pad out items to max if necessary
            while ($i < $max_items_per_invoice)
            {
                $i++;
                echo str_repeat(",", ($feature == "QUOTES" ? 15 : 21));
            }
            echo "\r\n";
        }
    }

    private static function make_js_safe($output)
    {
        //Break up any HTML tags so that interfering plugins don't disrupt it, and fix new line characters so they don't mess up the javascript
        return str_replace('</', '<\' + \'/', str_replace("\r", "", str_replace("\n", "\\n", addslashes($output))));
    }

	public static function editInvoice($document_id, $row, $line_item_factory, $line_items, $vendors, $clients, $selected_client_row, $default_tax_rate, $client, $tax_rates, $countries, $currencies, $ledger, $payment_plans, $shipping, $shipping_price, $use_posted_values, $client_changed = false, $attachments = array(), $related_docs = array(), $scroll_to_items = false)
	{
        $config = nBillConfigurationService::getInstance()->getConfig();
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.client.php");
        nbf_cms::$interop->add_html_header('<script type="text/javascript" src="' . nbf_cms::$interop->nbill_site_url_path . '/js/admin.js/sku_autosuggest.js"></script>');
        nbf_cms::$interop->add_html_header('<link rel="stylesheet" type="text/css" href="' . nbf_cms::$interop->nbill_site_url_path . '/style/admin/sku_autosuggest.css" />');
        nbf_cms::$interop->add_html_header('<link rel="stylesheet" type="text/css" href="' . nbf_cms::$interop->nbill_site_url_path . '/style/admin/document.css" />');

        $selected_tax_rate = 0;
		$doc_suffix = "";
        $doc_type = "IV";
        switch (strtoupper(nbf_common::get_param($_REQUEST, 'action')))
        {
            case "CREDITS":
                $doc_suffix = "_CR";
                $doc_type = "CR";
                break;
            case "QUOTES":
                $doc_suffix = "_QU";
                $doc_type = "QU";
                break;
        }

		nbf_html::load_calendar();
		nbf_cms::$interop->init_editor();
        if (strtoupper(@nbf_config::$editor) != 'NICEDIT' && strtoupper(@nbf_config::$editor) != 'NONE') {
            nbf_cms::$interop->init_editor(true); //Also need nicEdit for lightbox use
        }
		?>
        <script language="javascript" type="text/javascript">
        /*var awaiting_product_check = false;
        var product_check_timer = null;
        var product_check_retry_count = 0;*/
        var is_codemirror = null;
        var submit_on_client_change = false;
        var orig_status = 'AA';
        var orig_vendor_id = <?php echo intval(@$row->vendor_id); ?>;
		<?php nbf_html::add_js_validation_numeric();
		nbf_html::add_js_validation_date(); ?>

		function nbill_submit_task(task_name)
		{
			var form = document.adminForm;
            switch (task_name)
            {
                case 'save':
                case 'apply':
                    break;
                case 'cancel':
				    form.task.value=task_name;
                    form.submit();
				    return;
			}
			<?php
			$cal_date_format = nbf_common::get_date_format(true);

            //Do field validation
            if ($doc_suffix == "_QU")
            { ?>
                if (form.entity_id.value < 1 && (task_name == 'save' || task_name == 'save_copy' || task_name == 'apply'))
                {
                    alert('<?php echo NBILL_QUOTE_CLIENT_REQUIRED; ?>');
                }
                else
            <?php }
			?>
			if (form.vendor_name.value == "")
			{
				alert('<?php echo NBILL_INVOICE_VENDOR_NAME_REQUIRED; ?>');
			}
			else if (form.vendor_address.value == "")
			{
				alert('<?php echo NBILL_INVOICE_VENDOR_ADDRESS_REQUIRED; ?>');
			}
			else if (form.billing_name.value == "")
			{
				alert('<?php echo @constant("NBILL_BILLING_NAME_REQUIRED$doc_suffix"); ?>');
			}
			<?php if ($doc_suffix != '_QU')
            {
                ?>else if (form.billing_address.value == "")
			    {
				    alert('<?php echo @constant("NBILL_BILLING_ADDRESS_REQUIRED$doc_suffix"); ?>');
			    }<?php
            }
			if ($doc_suffix == "")
			{
				?>
				else if (form.written_off.checked && form.date_written_off.value == "")
				{
					alert('<?php echo NBILL_WRITTEN_OFF_DATE_REQUIRED; ?>');
				}
			<?php
            } ?>
			else if (!IsValidDate(form.document_date.value, false))
			{
				alert('<?php echo sprintf(NBILL_INVALID_DATE_FIELD, @constant("NBILL_INVOICE_DATE$doc_suffix"), $cal_date_format); ?>');
			}

            else {
			<?php
                
                 ?>

                if (task_name == 'save' || task_name == 'apply') {
                    if (document.adminForm.product_added.value && document.adminForm.product_added.value != 'false')
                    {
                        if (!confirm('<?php echo NBILL_DOC_SAVE_ADDED_PRODUCT; ?>')) {
                            document.adminForm.product_added.value = '';
                        }
                    }
                    if (document.adminForm.product_updated.value && document.adminForm.product_updated.value != 'false')
                    {
                        if (!confirm('<?php echo NBILL_DOC_SAVE_UPDATED_PRODUCT; ?>')) {
                            document.adminForm.product_updated.value = '';
                        }
                    }
                }

                document.adminForm.task.value=task_name;
                document.adminForm.submit();
                <?php
                if ($doc_suffix == "_QU" && ($row->status == 'DD' || $row->status == 'EE'))
                {
                    ?> } <?php
                }
                ?>
			}
		}

		function refresh_vendor(force_refresh)
		{
			//Show the appropriate dropdowns depending on selected vendor
			var vendor_id = document.getElementById('vendor_id').value;
			<?php
			foreach ($vendors as $vendor)
			{
				if ($doc_suffix == "")
                {
				    echo "document.getElementById('container_pay_inst_" . $vendor->id . "').style.display = 'none';";
                    echo "document.getElementById('container_sml_prt_delivery_" . $vendor->id . "').style.display = 'none';";
                }
				echo "document.getElementById('container_sml_prt_" . $vendor->id . "').style.display = 'none';";
                if ($doc_suffix == "_QU")
                {
                    echo "document.getElementById('container_quote_intro_" . $vendor->id . "').style.display = 'none';";
                }
			}
			if ($doc_suffix == "")
            { ?>
			    document.getElementById('container_pay_inst_' + vendor_id).style.display = 'inline';
                document.getElementById('container_sml_prt_delivery_' + vendor_id).style.display = 'inline';<?php
            } ?>
			document.getElementById('container_sml_prt_' + vendor_id).style.display = 'inline';
            <?php if ($doc_suffix == "_QU")
            { ?>
                document.getElementById('container_quote_intro_' + vendor_id).style.display = 'inline';
            <?php } ?>

            if (force_refresh || vendor_id != orig_vendor_id)
            {
			    switch (vendor_id)
			    {
				    <?php
				    foreach ($vendors as $vendor)
				    {
					    echo "case '" . $vendor->id . "':\n";
					    echo "  document.getElementById('vendor_name').value = '" . addslashes($vendor->vendor_name) . "';\n";
					    echo "  document.getElementById('vendor_address').value = '" . self::make_js_safe($vendor->vendor_address) . "';\n";
                        echo "  if(document.getElementById('billing_name').value.length==0&&document.getElementById('billing_address').value.length==0){document.getElementById('billing_country').value = '" . addslashes($vendor->vendor_country) . "';}\n";
                        echo "  document.getElementById('currency').value = '" . addslashes($vendor->vendor_currency) . "';\n";
					    $tax_rate_found = false;
                        $electronic_tax_rate_found = false;
					    if (!$selected_client_row)
                        {
                            foreach ($tax_rates[$vendor->id] as $tax_rate)
					        {
						        if ($tax_rate->country_code == $vendor->vendor_country && ($tax_rate->electronic_delivery == $config->default_electronic))
						        {
                                    if ($tax_rate->electronic_delivery) {
                                        $electronic_tax_rate_found = true;
                                    } else {
							            $tax_rate_found = true;
                                    }
                                    $selected_tax_rate = $tax_rate->id;
							        echo "document.getElementById('tax_no').value = '" . addslashes($vendor->tax_reference_no) . "';\n";
                                    echo "document.getElementById('default_tax_rate').value = '" . addslashes(format_number($tax_rate->tax_rate, 'tax_rate')) . "';\n";
                                    echo "document.getElementById('tax_abbreviation').value = '" . addslashes($tax_rate->tax_abbreviation) . "';\n";
							        echo "document.getElementById('tax_desc').value = '" . addslashes($tax_rate->tax_reference_desc) . "';\n";
                                    if (!$doc_suffix)
                                    {
                                        echo "document.getElementById('pay_inst_" . $vendor->id . "').value = '" . self::make_js_safe($tax_rate->payment_instructions) . "';\n";
                                        echo "document.getElementById('sml_prt_" . $vendor->id . "').value = '" . self::make_js_safe($tax_rate->small_print) . "';\n";
                                    }

                                    echo "document.getElementById('default_tax_rate').value = '" . addslashes(format_number($tax_rate->tax_rate, 'tax_rate')) . "';\n";
							        break;
						        }
					        }
					        if (!$tax_rate_found)
					        {
						        foreach ($tax_rates[$vendor->id] as $tax_rate)
						        {
							        if ($tax_rate->country_code == 'WW')
							        {
                                        if (!$tax_rate->electronic_delivery || !$electronic_tax_rate_found) {
								            $tax_rate_found = true;
                                            $selected_tax_rate = $tax_rate->id;
								            echo "document.getElementById('tax_no').value = '" . addslashes($vendor->tax_reference_no) . "';\n";
                                            echo "document.getElementById('default_tax_rate').value = '" . addslashes(format_number($tax_rate->tax_rate, 'tax_rate')) . "';\n";
                                            echo "document.getElementById('tax_abbreviation').value = '" . addslashes($tax_rate->tax_abbreviation) . "';\n";
								            echo "document.getElementById('tax_desc').value = '" . addslashes($tax_rate->tax_reference_desc) . "';\n";
                                            if (!$doc_suffix)
                                            {
                                                echo "document.getElementById('pay_inst_" . $vendor->id . "').value = '" . self::make_js_safe($tax_rate->payment_instructions) . "';\n";
                                                echo "document.getElementById('sml_prt_" . $vendor->id . "').value = '" . self::make_js_safe($tax_rate->small_print) . "';\n";
                                            }

                                            echo "document.getElementById('default_tax_rate').value = '" . addslashes(format_number($tax_rate->tax_rate, 'tax_rate')) . "';\n";
                                            break;
                                        }
							        }
						        }
					        }
                        }
					    if ($doc_suffix == "")
                        {
                            ?>
					        if (document.getElementById('pay_inst_<?php echo $vendor->id; ?>').value.length == 0)
					        {
						        <?php
						        echo "document.getElementById('pay_inst_" . $vendor->id . "').value = '" . self::make_js_safe($vendor->payment_instructions) . "';\n";
						        ?>
					        }<?php
                        } ?>
					    if (document.getElementById('sml_prt_<?php echo $vendor->id; ?>').value.length == 0)
					    {
						    <?php
                            switch ($doc_suffix)
                            {
                                case "_CR":
                                    echo "document.getElementById('sml_prt_" . $vendor->id . "').value = '" . self::make_js_safe($vendor->credit_small_print) . "';\n";
                                    break;
                                case "_QU":
                                    echo "document.getElementById('sml_prt_" . $vendor->id . "').value = '" . self::make_js_safe($vendor->quote_small_print) . "';\n";
                                    break;
                                default:
                                    echo "document.getElementById('sml_prt_" . $vendor->id . "').value = '" . self::make_js_safe($vendor->small_print) . "';\n";
                                    break;
						    }
						    ?>
					    }
                        if (document.getElementById('sml_prt_delivery_<?php echo $vendor->id; ?>') && document.getElementById('sml_prt_delivery_<?php echo $vendor->id; ?>').value.length == 0) {
                            <?php echo "document.getElementById('sml_prt_delivery_" . $vendor->id . "').value = '" . self::make_js_safe($vendor->delivery_small_print) . "';\n"; ?>
                        }
                        <?php
                        
					    echo "  break;\n";
				    }
				    ?>
			    }
                get_default_tax_info(null, true);
            }
		}

		function update_totals(on_accept_click)
		{
            if (typeof getCurrentLineItem != 'undefined') {
                line_item = getCurrentLineItem();
                if (line_item) {
                    line_item.reCalculateItemTotals();
                }
            }
		}

        function format_decimal(number, dec_places){
        //(c) Copyright 2008, Russell Walker, Netshine Software Limited. www.netshinesoftware.com
        if (typeof dec_places === 'undefined') {
            dec_places = 2;
        }
        number = number ? number : 0;
        var new_number='';var i=0;var sign="";number=number.toString();number=number.replace(/^\s+|\s+$/g,'');if(number.charCodeAt(0)==45){sign='-';number=number.substr(1).replace(/^\s+|\s+$/g,'')}dec_places=dec_places*1;dec_point_pos=number.lastIndexOf(".");if(dec_point_pos==0){number="0"+number;dec_point_pos=1}if(dec_point_pos==-1||dec_point_pos==number.length-1){if(dec_places>0){new_number=number+".";for(i=0;i<dec_places;i++){new_number+="0"}if(new_number==0){sign=""}return sign+new_number}else{return sign+number}}var existing_places=(number.length-1)-dec_point_pos;if(existing_places==dec_places){return sign+number}if(existing_places<dec_places){new_number=number;for(i=existing_places;i<dec_places;i++){new_number+="0"}if(new_number==0){sign=""}return sign+new_number}var end_pos=(dec_point_pos*1)+dec_places;var round_up=false;if((number.charAt(end_pos+1)*1)>4){round_up=true}var digit_array=new Array();for(i=0;i<=end_pos;i++){digit_array[i]=number.charAt(i)}for(i=digit_array.length-1;i>=0;i--){if(digit_array[i]=="."){continue}if(round_up){digit_array[i]++;if(digit_array[i]<10){break}}else{break}}for(i=0;i<=end_pos;i++){if(digit_array[i]=="."||digit_array[i]<10||i==0){new_number+=digit_array[i]}else{new_number+="0"}}if(dec_places==0){new_number=new_number.replace(".","")}if(new_number==0){sign=""}return sign+new_number}

        function format_currency(number, dec_places){
            //(c) Copyright 2008, Russell Walker, Netshine Software Limited. www.netshinesoftware.com
            if (typeof dec_places === 'undefined') {
                dec_places=2;
            }
            return format_decimal(number, dec_places);
        }

        function show_setup_fee_warning()
        {
            alert('<?php echo NBILL_DOC_SETUP_FEE_WARNING ?>');
        }

        function updateNicEdit(editorID, content) {
            if (nicEditors) {
                var nicE = nicEditors.findEditor('wysiwyg_' + editorID);
                if (!nicE) {
                    nicE = nicEditors.findEditor(editorID);
                }
                if (nicE) {
                    nicE.setContent(content);
                    nicE.saveContent();
                }
            }
        }

        function refresh_editor(editorID, content)
        {
            if (document.getElementById(editorID)) {
                sku_button_id = null;
                if (editorID.indexOf('detailed_desc') > -1) {
                    var raw_id = editorID.replace('nbill_', '');
                    raw_id = raw_id.replace('_detailed_description', '');
                    raw_id = raw_id.replace('detailed_description', '');
                    div_id = raw_id.length > 0 ? 'div_detailed_desc_' + raw_id : 'div_detailed_desc';
                    sku_button_id = 'nbill_' + raw_id + '_lookup_sku';
                } else {
                    div_id = 'container_' + editorID;
                }

                if (document.getElementById(editorID).value != content)
                {
                    editor_box = document.getElementById(editorID);
                    editor_box.value = content;
                    if (editor_box.tagName && editor_box.tagName.toLowerCase() == "textarea") {
                        editor_box.innerText = content;
                    }

                    <?php
                    if (nbf_config::$editor == 'nicEdit')
                    { ?>
                        //Update nicEdit editor
                        updateNicEdit(editorID, content);
                        <?php
                    }
                    else
                    { ?>
                        if (div_id == 'div_detailed_desc') {
                            //Update nicEdit editor
                            updateNicEdit(editorID, content);
                        } else {
                            //For ckeditor we will need to make the editor visible before it will allow successful updating of the content
                            if (document.getElementById(div_id) && document.getElementById(div_id).style) {
                                document.getElementById(div_id).style.display = '';
                            }
                            //The only way to clear the existing contents is to drill into the iframe
                            var editor_doc = null;
                            var iframe_result = find_editor_iframe_doc(editorID, div_id);
                            if (iframe_result.length = 2) {
                                editor_doc = iframe_result[0];
                                is_codemirror = iframe_result[1];
                            }
                            if (editor_doc)
                            {
                                var body = editor_doc.getElementsByTagName ? editor_doc.getElementsByTagName('body')[0] : null;
                                if (is_codemirror)
                                {
                                    if (body != null && typeof(body.textContent) != 'undefined') {
                                        body.textContent = content;
                                    } else if (body != null && typeof(body.innerText) != 'undefined') {
                                        body.innerText = content;
                                    } else {
                                        editor_doc.innerText = content;
                                    }
                                }
                                else
                                {
                                    body.innerHTML = content; //TinyMCE or JCE
                                }
                                if (document.getElementById(div_id) && document.getElementById(div_id).style) {
                                    document.getElementById(div_id).style.display = 'none';
                                }
                            }
                            else if (!is_codemirror)
                            {
                                //If jInsertEditorText exists, use it
                                if(typeof jInsertEditorText != 'undefined')
                                {
                                    setTimeout(function(){jInsertEditorText(content, editorID);if (document.getElementById(div_id) && document.getElementById(div_id).style) { document.getElementById(div_id).style.display = 'none';}}, 50); //ckeditor needs this to run async
                                }
                                else
                                {
                                    if (editor_doc)
                                    {
                                        //Try bypassing API
                                        editor_doc.getElementsByTagName('body')[0].innerHTML = content;
                                    }
                                    else
                                    {
                                        updateNicEdit(editorID, content);
                                    }
                                }
                            }
                        }
                    <?php } ?>
                }
            }
        }

        function find_editor_iframe_doc(editorID, div_id)
        {
            var ret_val = Array(2);
            ret_val[0] = null; //doc
            ret_val[1] = false; //is codemirror

            var editor_ifr = document.getElementById(editorID + '_ifr');
            var editor_doc = null;
            if (!editor_ifr && div_id)
            {
                //Codemirror makes it even harder! grrr.
                var editor_div = document.getElementById(div_id);
                if (editor_div && editor_div.childNodes && editor_div.childNodes.length > 2
                    && editor_div.childNodes[2].childNodes && editor_div.childNodes[1].className.indexOf('CodeMirror') > -1)
                {
                    editor_ifr = typeof(editor_div.childNodes[2].childNodes[1]) == 'iframe' ? editor_div.childNodes[2].childNodes[1] : editor_div.childNodes[2];
                    if (editor_ifr){ret_val[1] = true;}
                }
            }

            if (editor_ifr)
            {
                editor_doc = editor_ifr.contentDocument ? editor_ifr.contentDocument : (editor_ifr.contentWindow ? editor_ifr.contentWindow.document : editor_ifr);
                if (editor_doc && editor_doc.getElementsByTagName) {
                    ret_val[0] = editor_doc;
                }
            }
            return ret_val;
        }

        function get_default_tax_info(product_id, suppress_overwrite)
        {
            var country = document.getElementById('billing_country');
            var entity = document.getElementById('entity_id');
            var vendor = document.getElementById('vendor_id');
            var country_value = country.selectedIndex > -1 ? country.options[country.selectedIndex].value : '';
            var entity_value = entity.selectedIndex > -1 ? entity.options[entity.selectedIndex].value : '';

            submit_ajax_request('get_default_tax_info', 'document_type=<?php echo $doc_type; ?>&country=' + country_value + '&entity_id=' + entity_value + '&vendor_id=' + vendor.value + '&tax_exemption_code=' + document.getElementById('tax_exemption_code').value + '&product_id=' + product_id, function(content){populate_default_tax_info(content, suppress_overwrite)});
        }
        function populate_default_tax_info(result, suppress_overwrite)
        {
            if (result.length > 0) {
                var json_result = JSON.parse(result);
                if (json_result) {
                    document.getElementById('default_tax_rate').value = format_decimal(json_result.tax_rate, tax_rate_precision);
                    if (json_result.tax_abbreviation.length > 0 || json_result.tax_reference_no.length > 0 || json_result.tax_reference_desc.length > 0) {
                        document.getElementById('tax_abbreviation').value = json_result.tax_abbreviation;
                        document.getElementById('tax_no').value = json_result.tax_reference_no;
                        if (document.getElementById('tax_desc')) {
                            document.getElementById('tax_desc').value = json_result.tax_reference_desc;
                        }
                    }
                    if (!suppress_overwrite || document.getElementById('pay_inst_' + document.getElementById('vendor_id').value.trim().length == 0)) {
                        refresh_editor('pay_inst_' + document.getElementById('vendor_id').value, json_result.payment_instructions);
                    }
                    if (!suppress_overwrite || document.getElementById('sml_prt_' + document.getElementById('vendor_id').value.trim().length == 0)) {
                        refresh_editor('sml_prt_' + document.getElementById('vendor_id').value, json_result.small_print);
                    }
                }
            }
        }

        function update_client(result)
        {
            var json_result = JSON.parse(result);
            if (json_result) {
                document.getElementById('billing_name').value = json_result.billing_name;
                document.getElementById('billing_address').value = json_result.billing_address;
                document.getElementById('billing_country').value = json_result.billing_country;
                document.getElementById('reference').value = json_result.reference;
                document.getElementById('tax_exemption_code').value = json_result.tax_exemption_code;
                if (json_result.shipping_addresses) {
                    var shipping_address_id = document.getElementById('shipping_address_id');
                    var current_id = shipping_address_id.options[shipping_address_id.selectedIndex].value;
                    for(var i=shipping_address_id.options.length-1;i>=0;i--) {
                        shipping_address_id.remove(i);
                    }
                    for(address_id in json_result.shipping_addresses){
                        shipping_address_id.options[shipping_address_id.options.length] = new Option(json_result.shipping_addresses[address_id], address_id);
                    }
                    for(var i = 0; i < shipping_address_id.options.length;i++) {
                        if (shipping_address_id.options[i].value == current_id) {
                            shipping_address_id.selectedIndex = i;
                            break;
                        }
                    }
                }
            }
            get_default_tax_info();
        }
        </script>

        <table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, nbf_common::get_param($_REQUEST, 'action')); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php echo $row->id ? @constant("NBILL_EDIT_INVOICE$doc_suffix") . " '$row->document_no'" : @constant("NBILL_NEW_INVOICE$doc_suffix"); ?>
                <?php $doc_links = array();
                if (count($related_docs) > 0)
                {
                    echo ' - ' . NBILL_INVOICE_RELATED_DOCUMENTS . ' ';
                    foreach ($related_docs as $related_doc)
                    {
                        switch ($related_doc->document_type)
                        {
                            case "CR":
                                $doc_links[] = NBILL_DOC_TYPE_CREDIT . ' <a href="' . nbf_cms::$interop->admin_page_prefix . '&action=credits&task=edit&cid=' . $related_doc->id . '">' . $related_doc->document_no . '</a>';
                                break;
                            case "QU":
                                $doc_links[] = NBILL_DOC_TYPE_QUOTE . ' <a href="' . nbf_cms::$interop->admin_page_prefix . '&action=quotes&task=edit&cid=' . $related_doc->id . '">' . $related_doc->document_no . '</a>';
                                break;
                            default:
                                $doc_links[] = NBILL_DOC_TYPE_INVOICE . ' <a href="' . nbf_cms::$interop->admin_page_prefix . '&action=invoices&task=edit&cid=' . $related_doc->id . '">' . $related_doc->document_no . '</a>';
                                break;
                        }
                    }
                    echo implode(", ", $doc_links);
                }
                if (intval($row->id)) {
                    if (count($doc_links) > 0) {
                        echo "<br />";
                    }
                    echo "<a href=\"#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=" . nbf_common::get_param($_REQUEST, 'action') . "&task=printpreviewpopup&hidemainmenu=1&items=" . $row->id . "', '" . uniqid() . "', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;\" title=\"" . NBILL_PRINT . "\"><img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/preview.gif\" alt=\"" . NBILL_PRINT . "\" border=\"0\" style=\"vertical-align:middle;\" /></a>";
                    if (nbf_common::pdf_writer_available()) {
                        echo "&nbsp;&nbsp;<a href=\"#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=" . nbf_common::get_param($_REQUEST, 'action') . "&task=pdfpopup&hidemainmenu=1&items=" . $row->id . "', '" . uniqid() . "', 'width=800,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;\" title=\"" . NBILL_PDF . "\"><img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/pdf.png\" alt=\"" . NBILL_PDF . "\" border=\"0\" style=\"vertical-align:middle;\" /></a> ";
                    }
                }
                ?>
			</th>
		</tr>
		</table>

        <?php
        if ($doc_type == 'IV' && !@$row->id)
        {
            if (nbf_common::get_param($_COOKIE, 'nbill_adhoc_dont_bug') != 'on')
            {
                ?>
                <div class="nbill-message" id="nbill_adhoc_warning"><?php echo sprintf(NBILL_DOC_NEW_INVOICE_WARNING, '<a href="' . nbf_cms::$interop->admin_page_prefix . "&action=orders" . '">', '</a>'); ?><a style="float:right" href="#" onclick="if (confirm('<?php echo NBILL_ADHOC_DONT_BUG_SURE; ?>')){document.getElementById('nbill_adhoc_warning').style.display='none';var date = new Date();date.setTime(date.getTime()+(999*24*60*60*1000));var expires =''+date.toGMTString();document.cookie = 'nbill_adhoc_dont_bug=on;'+expires+'; path=/';} return false;"><?php echo NBILL_ADHOC_DONT_BUG; ?></a></div>
                <?php
            }
        }
        if ($tax_rate_found)
        {
            foreach ($vendors as $vendor)
            {
                foreach ($tax_rates[$vendor->id] as $tax_rate)
                {
                    if ($tax_rate->id == $selected_tax_rate)
                    {
                        break 2;
                    }
                }
            }
        }
        ?>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		} ?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" id="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
        <input type="hidden" name="task" id="task" value="edit" />
        <input type="hidden" name="ordering_item" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="id" value="<?php echo $document_id;?>" />
        <input type="hidden" name="cid" value="<?php echo $document_id;?>" />
        <input type="hidden" name="related_document_id" value="<?php echo nbf_common::get_param($_REQUEST, 'related_document_id') ? nbf_common::get_param($_REQUEST, 'related_document_id') : @$row->related_document_id; ?>" />
		<input type="hidden" name="removed_items" id="removed_items" value="<?php echo nbf_common::get_param($_POST,'removed_items'); ?>" />
		<input type="hidden" name="added_items" id="added_items" value="<?php echo nbf_common::get_param($_POST,'added_items'); ?>" />
        <input type="hidden" name="disable_client_list" id="disable_client_list" value="<?php echo nbf_common::get_param($_REQUEST, 'disable_client_list'); ?>" />
        <input type="hidden" name="listed_client_id" id="listed_client_id" value="<?php echo nbf_common::get_param($_REQUEST, 'listed_client_id'); ?>" />
        <input type="hidden" name="no_record_limit" id="no_record_limit" value="<?php echo nbf_common::get_param($_REQUEST, 'no_record_limit'); ?>" />
        <input type="hidden" name="document_type" id="document_type" value="<?php echo $doc_suffix ? substr($doc_suffix, 1) : "IN"; ?>" />
        <input type="hidden" name="default_tax_rate" id="default_tax_rate" value="<?php echo $use_posted_values ? nbf_common::get_param($_REQUEST, 'default_tax_rate') : format_number($default_tax_rate, 'tax_rate'); ?>" />
        <input type="hidden" name="product_added" id="product_added" value="<?php echo $use_posted_values ? nbf_common::get_param($_REQUEST, 'product_added') : ""; ?>" />
        <input type="hidden" name="product_updated" id="product_updated" value="<?php echo $use_posted_values ? nbf_common::get_param($_REQUEST, 'product_updated') : ""; ?>" />
        <input type="hidden" name="line_items" id="line_items" value="<?php echo htmlspecialchars(json_encode($line_items)); ?>" />
		<?php nbf_html::add_filters(); ?>

        <?php
        $tab_settings = new nbf_tab_group();
        $tab_settings->start_tab_group("admin_settings");
        $tab_settings->add_tab_title("basic", NBILL_ADMIN_TAB_BASIC);
        $tab_settings->add_tab_title("advanced", NBILL_ADMIN_TAB_ADVANCED);
        $tab_settings->add_tab_title("line_items", '<a href="#line_item_start" style="display:inline-block;float:right;" onclick="select_tab_admin_settings(\'nbill-tab-title-admin_settings-basic\');return true;">' . NBILL_INVOICE_SCROLL_TO_ITEMS . '</a>', 'return true;', '', 'plain_link');
        ob_start();
        ?>
        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-document">
		    <tr>
			    <th colspan="2"><?php echo @constant("NBILL_INVOICE_DETAILS$doc_suffix"); ?></th>
		    </tr>
            <?php
			    if (count($vendors) > 1)
			    {?>
				    <tr id="nbill-admin-tr-vendor">
					    <td class="nbill-setting-caption">
						    <?php echo NBILL_VENDOR; ?>
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
							    echo nbf_html::select_list($vendor_name, "vendor_id", 'onchange="orig_vendor_id=\'x\';refresh_vendor();" id="vendor_id" class="inputbox"', $selected_vendor);
						    ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_VENDOR_ID, "vendor_id_help"); ?>
					    </td>
				    </tr>
			    <?php }
			    else
			    {
				    echo "<tr><td colspan=\"3\"><input type=\"hidden\" name=\"vendor_id\" id=\"vendor_id\" value=\"" . $vendors[0]->id . "\" /></td></tr>";
				    $_POST['vendor_id'] = $vendors[0]->id;
                    $selected_vendor = $vendors[0]->id;
			    }
		    ?>
            <tr id="nbill-admin-tr-client">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SELECT_CLIENT; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    //Create a dropdown of clients
				    $client_list = array();
				    $client_list[] = nbf_html::list_option(-1, NBILL_NOT_APPLICABLE);
				    foreach ($clients as $client_item)
				    {
                        if ($client_item)
                        {
					        $client_name = $client_item->company_name;
					        if (nbf_common::nb_strlen($client_name) > 0 && nbf_common::nb_strlen($client_item->contact_name) > 0)
					        {
						        $client_name .= " (";
					        }
					        $client_name .= $client_item->contact_name;
					        if (nbf_common::nb_strlen($client_item->company_name) > 0 && nbf_common::nb_strlen($client_item->contact_name) > 0)
					        {
						        $client_name .= ")";
					        }
					        $client_list[] = nbf_html::list_option($client_item->id, $client_name);
                        }
				    }
				    if ($use_posted_values)
				    {
					    $selected_client = nbf_common::get_param($_POST, 'entity_id');
				    }
				    else
				    {
					    if($row->id)
					    {
						    $selected_client = $row->entity_id;
					    }
					    else
					    {
						    $selected_client = nbf_common::get_param($_REQUEST, 'listed_client_id', 0);
                            $client_changed = $selected_client; //Force population of name and address
					    }
				    }
				    echo nbf_html::select_list($client_list, "entity_id", 'onchange="submit_ajax_request(\'client_changed\', \'entity_id=\' + document.getElementById(\'entity_id\').value, function(result){update_client(result);if (submit_on_client_change){document.adminForm.task.value=\'client_changed\';document.adminForm.submit();}});" style="width:200px;" class="inputbox" id="entity_id"', $selected_client);
                    if (count($clients) == nbf_globals::$record_limit)
                    { ?>
                        <br /><span style="color:#ff0000;font-weight:bold"><?php echo sprintf(@constant("NBILL_INVOICE_RECORD_LIMIT_WARNING$doc_suffix"), nbf_globals::$record_limit, nbf_globals::$record_limit); ?></span><br />
                        <input type="button" class="button btn" name="remove_record_limit" id="remove_record_limit" value="<?php echo NBILL_INVOICE_SHOW_ALL; ?>" onclick="adminForm.no_record_limit.value='1';adminForm.submit();return false;" />
                    <?php }
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_SELECT_CLIENT, "entity_id_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-document-number">
			    <td class="nbill-setting-caption">
				    <?php echo @constant("NBILL_INVOICE_NUMBER$doc_suffix"); ?>
			    </td>
			    <td class="nbill-setting-value">
                    <input type="text" name="document_no" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'document_no', null, true) : $row->document_no; ?>" class="inputbox" style="width:80px" />
                    <?php nbf_html::show_static_help(@constant("NBILL_INSTR_INVOICE_NUMBER$doc_suffix"), "document_no_help"); ?>
			    </td>
		    </tr>
            <!-- Custom Fields Placeholder -->
            <?php
             ?>
		    <tr id="nbill-admin-tr-vendor-name">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_VENDOR_NAME; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="vendor_name" id="vendor_name" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'vendor_name', null, true)) : str_replace("\"", "&quot;", $row->vendor_name); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(@constant("NBILL_INSTR_VENDOR_NAME$doc_suffix"), "vendor_name_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-vendor-address">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_VENDOR_ADDRESS; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <textarea name="vendor_address" id="vendor_address" rows="4" cols="30"><?php echo $use_posted_values ? nbf_common::get_param($_POST,'vendor_address', null, true) : $row->vendor_address; ?></textarea>
                    <?php nbf_html::show_static_help(@constant("NBILL_INSTR_VENDOR_ADDRESS$doc_suffix"), "vendor_address_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-billing-name">
			    <td class="nbill-setting-caption">
				    <?php echo @constant("NBILL_BILLING_NAME$doc_suffix"); ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="billing_name" id="billing_name" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'billing_name', null, true)) : str_replace("\"", "&quot;", $row->billing_name); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(@constant("NBILL_INSTR_BILLING_NAME$doc_suffix"), "billing_name_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-billing-address">
			    <td class="nbill-setting-caption">
				    <?php echo @constant("NBILL_BILLING_ADDRESS$doc_suffix"); ?>
			    </td>
			    <td class="nbill-setting-value">
				    <textarea name="billing_address" id="billing_address" rows="4" cols="30"><?php echo $use_posted_values ? nbf_common::get_param($_POST,'billing_address', null, true) : $row->billing_address; ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_INSTR_BILLING_ADDRESS, "billing_address_help"); ?>
			    </td>
		    </tr>
            <tr id="nbill-admin-tr-billing-country">
                <td class="nbill-setting-caption">
                    <?php echo @constant("NBILL_BILLING_COUNTRY$doc_suffix"); ?>
                </td>
                <td class="nbill-setting-value">
                    <?php $invoice_country = array();
                    $selected_cc = "";
                    foreach ($countries as $country_code)
                    {
                        $invoice_country[] = nbf_html::list_option($country_code['code'], $country_code['description']);
                    }
                    if ($use_posted_values)
                    {
                        $selected_cc = nbf_common::get_param($_POST, 'billing_country');
                    }
                    else
                    {
                        if ($row->id || ($selected_client_row && $selected_client_row->id))
                        {
                            $selected_cc = $row->billing_country;
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
                    echo nbf_html::select_list($invoice_country, "billing_country", 'id="billing_country" class="inputbox" onchange="get_default_tax_info();"', $selected_cc);
                    nbf_html::show_static_help(NBILL_INSTR_BILLING_COUNTRY, "billing_country_help"); ?>
                </td>
            </tr>
            <?php
            $shipping_addresses = array(0=>NBILL_NOT_APPLICABLE);
            if (@$client->shipping_address) {
                $shipping_addresses[$client->shipping_address->id] = $client->shipping_address->format(true);
            }
            if (@$client->contacts) {
                foreach ($client->contacts as $contact) {
                    if (@$contact->shipping_address) {
                        $shipping_addresses[$contact->shipping_address->id] = $contact->shipping_address->format(true);
                    }
                }
            }
            nbf_html::show_admin_setting_dropdown($row, 'shipping_address_id', array_unique($shipping_addresses));
            ?>
		    <tr id="nbill-admin-tr-reference">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_REFERENCE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="reference" id="reference" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'reference', null, true)) : str_replace("\"", "&quot;", $row->reference); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_REFERENCE, "reference_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-date">
			    <td class="nbill-setting-caption">
				    <?php echo constant("NBILL_INVOICE_DATE$doc_suffix"); ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					$date_format = nbf_common::get_date_format();
					$cal_date_format = nbf_common::get_date_format(true);
					if ($use_posted_values)
					{
						$date_value = nbf_common::get_param($_POST, 'document_date');
					}
					else
					{
						$date_value = $row->id ? nbf_common::nb_date($date_format, $row->document_date) : nbf_common::nb_date($date_format, nbf_common::nb_time());
					}

					$date_parts = nbf_date::get_date_parts($date_value, $cal_date_format);
					if ($date_parts['y'] < 1971)
					{
						$date_value = "";
					}
					?>
					<span style="white-space:nowrap"><input type="text" name="document_date" class="inputbox date-entry" maxlength="19" value="<?php echo $date_value; ?>" />
					<input type="button" name="document_date_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.document_date,'<?php echo $cal_date_format; ?>',this);" /></span>
                    <?php nbf_html::show_static_help(NBILL_INSTR_INVOICE_DATE, "document_date_help"); ?>
			    </td>
		    </tr>
            <?php if ($doc_suffix == '') { ?>
            <tr id="nbill-admin-tr-due-date">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_INVOICE_DUE_DATE; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    $date_format = nbf_common::get_date_format();
                    $cal_date_format = nbf_common::get_date_format(true);
                    if ($use_posted_values)
                    {
                        $date_value = nbf_common::get_param($_POST, 'due_date');
                    }
                    else
                    {
                        $date_value = $row->id ? nbf_common::nb_date($date_format, $row->due_date) : "";
                    }

                    $date_parts = nbf_date::get_date_parts($date_value, $cal_date_format);
                    if (isset($date_parts['y']) && $date_parts['y'] < 1971)
                    {
                        $date_value = "";
                    }
                    ?>
                    <span style="white-space:nowrap"><input type="text" name="due_date" class="inputbox date-entry" maxlength="19" value="<?php echo $date_value; ?>" />
                    <input type="button" name="due_date_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.due_date,'<?php echo $cal_date_format; ?>',this);" /></span>
                    <?php nbf_html::show_static_help(NBILL_INSTR_INVOICE_DUE_DATE, "due_date_help"); ?>
                </td>
            </tr>
            <?php } ?>

		    <tr id="nbill-admin-tr-currency">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CURRENCY; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    $invoice_currency = array();
				    foreach ($currencies as $currency_code)
				    {
					    $invoice_currency[] = nbf_html::list_option($currency_code['code'], $currency_code['description']);
				    }
				    if ($use_posted_values)
				    {
					    $selected_curr = nbf_common::get_param($_POST, 'currency');
				    }
				    else
				    {
					    if ($row->id)
					    {
						    $selected_curr = $row->currency;
					    }
					    else
					    {
                            foreach ($vendors as $vendor)
                            {
                                if ($vendor->id == $selected_vendor)
                                {
                                    $selected_curr = $vendor->vendor_currency;
                                    break;
                                }
                            }
					    }
				    }
                    echo nbf_html::select_list($invoice_currency, "currency", 'id="currency" class="inputbox" onchange="submitLineItemAjaxTask(\'refresh\');"', $selected_curr);
                    nbf_html::show_static_help(NBILL_INSTR_CURRENCY, "currency_help"); ?>
			    </td>
		    </tr>
            <?php
             ?>
            <tr id="nbill-admin-tr-line-items">
			    <td colspan="2">
                    <a name="line_item_start" id="line_item_start" style="position:relative;top:-70px;"></a>
                    <h4><?php echo NBILL_INVOICE_LINE_ITEMS; ?></h4>
                    <?php
                    $line_item_view = $line_item_factory->createLineItemView($line_items, $row->document_type, $row, true);
                    $line_item_view->renderEditorSummary();
                    ?>
			    </td>
		    </tr>

		    <tr><td colspan="2" class="line_item_divider"><hr /></td></tr>
            <?php if ($doc_suffix == "")
            { ?>
			    <tr id="nbill-admin-tr-written-off">
				    <td class="nbill-setting-caption">
					    <?php echo NBILL_INVOICE_WRITTEN_OFF; ?>
				    </td>
				    <td class="nbill-setting-value">
					    <?php
					    if (isset($_POST['written_off']))
					    {
						    $written_off = $use_posted_values ? nbf_common::get_param($_POST,'written_off', null, true) : $row->written_off;
					    }
					    else
					    {
						    $written_off = $use_posted_values ? '' : $row->written_off;
					    }
					    echo nbf_html::yes_or_no_options("written_off", "", $written_off); ?>
                        <?php nbf_html::show_static_help(NBILL_INSTR_INVOICE_WRITTEN_OFF, "written_off_help"); ?>
				    </td>
			    </tr>
			    <tr id="nbill-admin-tr-write-off-date">
				    <td class="nbill-setting-caption">
					    <?php echo NBILL_INVOICE_WRITE_OFF_DATE; ?>
				    </td>
				    <td class="nbill-setting-value">
					    <?php
					    $date_format = nbf_common::get_date_format();
					    $cal_date_format = nbf_common::get_date_format(true);
					    if ($use_posted_values)
					    {
						    $date_value = nbf_common::get_param($_POST, 'date_written_off');
					    }
					    else
					    {
						    $date_value = $row->id ? nbf_common::nb_date($date_format, $row->date_written_off) : "";
					    }

					    $date_parts = nbf_date::get_date_parts($date_value, $cal_date_format);
					    if (@$date_parts['y'] < 1971)
					    {
						    $date_value = "";
					    }
					    ?>
					    <span style="white-space:nowrap"><input type="text" name="date_written_off" class="inputbox date-entry" maxlength="19" value="<?php echo $date_value; ?>" />
					    <input type="button" name="date_written_off_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.date_written_off,'<?php echo $cal_date_format; ?>',this);" /></span>
                        <?php nbf_html::show_static_help(NBILL_INSTR_WRITE_OFF_DATE, "date_written_off_help"); ?>
				    </td>
			    </tr>
			    <tr id="nbill-admin-tr-claim-tax-back">
				    <td class="nbill-setting-caption">
					    <?php echo NBILL_INVOICE_CLAIM_BACK; ?>
				    </td>
				    <td class="nbill-setting-value">
					    <?php
					    if (isset($_POST['claim_tax_back']))
					    {
						    $claim_tax_back = $use_posted_values ? nbf_common::get_param($_POST,'claim_tax_back', null, true) : $row->claim_tax_back;
					    }
					    else
					    {
						    $claim_tax_back = $use_posted_values ? '' : $row->claim_tax_back;
					    }
					    echo nbf_html::yes_or_no_options("claim_tax_back", "", $claim_tax_back); ?>
                        <?php nbf_html::show_static_help(NBILL_INSTR_INVOICE_CLAIM_BACK, "claim_tax_back_help"); ?>
				    </td>
			    </tr>
		        <?php
                $hidden_field = "";
            }
		    else
		    {
                $hidden_field = '<input type="hidden" name="date_written_off" id="date_written_off" value="" />';
		    }
            ?>
            <tr id="nbill-admin-tr-notes">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_NOTES; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    if (isset($_POST['notes']))
				    {
					    $notes = $use_posted_values ? nbf_common::get_param($_POST,'notes', null, true) : $row->notes;
				    }
				    else
				    {
					    $notes = $row->notes;
				    }
				    ?>
				    <textarea name="notes" id="notes" cols="35" rows="10"><?php echo $notes; ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_INSTR_NOTES, "notes_help"); ?>
			    </td>
		    </tr>
		    </table>
        </div>

        <?php
        $tab_settings->add_tab_content("basic", ob_get_clean());
        ob_start();
        ?>

        <div class="rounded-table">
            <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-document-advanced">
            <tr>
                <th colspan="2"><?php echo @constant("NBILL_INVOICE_DETAILS$doc_suffix"); ?></th>
            </tr>
            <?php
            
            ?>
            <tr id="nbill-admin-tr-tax-reference-desc">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_REFERENCE_DESC; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="tax_desc" id="tax_desc" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'tax_desc', null, true)) : str_replace("\"", "&quot;", $row->tax_desc); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_REFERENCE_DESC, "tax_desc_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-tax-abbreviation">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_ABBREVIATION; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="tax_abbreviation" id="tax_abbreviation" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'tax_abbreviation', null, true)) : str_replace("\"", "&quot;", $row->tax_abbreviation); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_ABBREVIATION, "tax_abbreviation_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-tax-reference-no">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_REFERENCE_NO; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="tax_no" id="tax_no" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'tax_no', null, true)) : str_replace("\"", "&quot;", $row->tax_no); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_REFERENCE_NO, "tax_no_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-tax-exemption-code">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_EXEMPTION_CODE; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="tax_exemption_code" id="tax_exemption_code" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'tax_exemption_code', null, true)) : str_replace("\"", "&quot;", $row->tax_exemption_code); ?>" class="inputbox" style="width:160px" onchange="get_default_tax_info();" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_EXEMPTION_CODE, "tax_exemption_code_help"); ?>
                </td>
            </tr>
            <?php
            

            //If a payment schedule has already been set up (for a payment plan involving installments), payment link will be disalbed - offer to re-enable
            if ($doc_suffix == "")
            {
                
                ?>
                <tr id="nbill-admin-tr-show-paylink">
                    <td class="nbill-setting-caption">
                        <?php echo NBILL_INVOICE_SHOW_PAYLINK; ?>
                    </td>
                    <td class="nbill-setting-value">
                        <?php
                        $paylink_options = array();
                        $paylink_options[] = nbf_html::list_option(0, NBILL_INVOICE_PAYLINK_USE_GLOBAL);
                        $paylink_options[] = nbf_html::list_option(1, NBILL_INVOICE_PAYLINK_SHOW);
                        $paylink_options[] = nbf_html::list_option(2, NBILL_INVOICE_PAYLINK_HIDE);
                        echo nbf_html::select_list($paylink_options, "show_invoice_paylink", 'id="show_invoice_paylink" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST,'show_invoice_paylink', null, true) : $row->show_invoice_paylink);
                        ?>
                        <?php nbf_html::show_static_help(NBILL_INSTR_INVOICE_SHOW_PAYLINK, "show_invoice_paylink_help"); ?>
                    </td>
                </tr>
                <?php
            }
            

            if ($doc_suffix != "_QU")
            { ?>
                <tr id="nbill-admin-tr-paid-in-full">
                    <td class="nbill-setting-caption">
                        <?php echo NBILL_INVOICE_PAID_IN_FULL; ?>
                    </td>
                    <td class="nbill-setting-value">
                        <?php
                        if (isset($_POST['paid_in_full']))
                        {
                            $paid_in_full = $use_posted_values ? nbf_common::get_param($_POST,'paid_in_full', null, true) : $row->paid_in_full;
                        }
                        else
                        {
                            $paid_in_full = $use_posted_values ? '' : $row->paid_in_full;
                        }
                        echo nbf_html::yes_or_no_options("paid_in_full", "", $paid_in_full); ?>
                        <?php nbf_html::show_static_help(@constant("NBILL_INSTR_INVOICE_PAID_IN_FULL$doc_suffix"), "paid_in_full_help"); ?>
                    </td>
                </tr>
                <tr id="nbill-admin-tr-partial-payment">
                    <td class="nbill-setting-caption">
                        <?php echo NBILL_INVOICE_PARTIAL_PAYMENT; ?>
                    </td>
                    <td class="nbill-setting-value">
                        <?php
                        if (isset($_POST['partial_payment']))
                        {
                            $partial_payment = $use_posted_values ? nbf_common::get_param($_POST,'partial_payment', null, true) : $row->partial_payment;
                        }
                        else
                        {
                            $partial_payment = $use_posted_values ? '' : $row->partial_payment;
                        }

                        echo nbf_html::yes_or_no_options("partial_payment", "", $partial_payment); ?>
                        <?php nbf_html::show_static_help(NBILL_INSTR_INVOICE_PARTIAL_PAYMENT, "partial_payment_help"); ?>
                    </td>
                </tr>
                <?php
            }
            if ($doc_suffix == "")
            { ?>
                <tr id="nbill-admin-tr-refunded-in-full">
                    <td class="nbill-setting-caption">
                        <?php echo NBILL_INVOICE_REFUND_IN_FULL; ?>
                    </td>
                    <td class="nbill-setting-value">
                        <?php
                        if (isset($_POST['refunded_in_full']))
                        {
                            $refunded_in_full = $use_posted_values ? nbf_common::get_param($_POST,'refunded_in_full', null, true) : $row->refunded_in_full;
                        }
                        else
                        {
                            $refunded_in_full = $use_posted_values ? '' : $row->refunded_in_full;
                        }
                        echo nbf_html::yes_or_no_options("refunded_in_full", "", $refunded_in_full); ?>
                        <?php nbf_html::show_static_help(NBILL_INSTR_INVOICE_REFUND_IN_FULL, "refunded_in_full_help"); ?>
                    </td>
                </tr>
                <tr id="nbill-admin-tr-partial-refund">
                    <td class="nbill-setting-caption">
                        <?php echo NBILL_INVOICE_PARTIAL_REFUND; ?>
                    </td>
                    <td class="nbill-setting-value">
                        <?php
                        if (isset($_POST['partial_refund']))
                        {
                            $partial_refund = $use_posted_values ? nbf_common::get_param($_POST,'partial_refund', null, true) : $row->partial_refund;
                        }
                        else
                        {
                            $partial_refund = $use_posted_values ? '' : $row->partial_refund;
                        }
                        echo nbf_html::yes_or_no_options("partial_refund", "", $partial_refund); ?>
                        <?php nbf_html::show_static_help(NBILL_INSTR_INVOICE_PARTIAL_REFUND, "partial_refund_help"); ?>
                    </td>
                </tr>
            <?php }
            
            if ($doc_suffix == "")
            {
                ?>
                <tr id="nbill-admin-tr-pay-instr">
                    <td class="nbill-setting-caption">
                        <?php echo NBILL_INVOICE_PAY_INSTR; ?>
                    </td>
                    <td class="nbill-setting-value">
                        <?php
                        foreach ($vendors as $vendor)
                        {
                            if ($use_posted_values)
                            {
                                $pay_inst = nbf_common::get_param($_POST, 'pay_inst_' . $vendor->id, null, true, false, true);
                            }
                            else
                            {
                                $pay_inst = $row->payment_instructions;
                                if (nbf_common::nb_strlen($pay_inst) == 0 && !$row->id)
                                {
                                    $pay_inst = $vendor->payment_instructions;
                                }
                            }
                            if ($vendor->id == $selected_vendor)
                            {
                                $visibility = " style=\"display:inline\" ";
                            }
                            else
                            {
                                $visibility = " style=\"display:none\" ";
                            }

                            echo "<div id=\"container_pay_inst_$vendor->id\" $visibility>";
                            echo nbf_cms::$interop->render_editor('pay_inst_' . $vendor->id, "pay_inst_" . $vendor->id, $pay_inst);
                            echo "</div>";
                        }
                        ?>
                        <?php nbf_html::show_static_help(NBILL_INSTR_INVOICE_PAY_INSTR, "pay_inst_help"); ?>
                    </td>
                </tr>
            <?php } ?>
            <tr id="nbill-admin-tr-small-print">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_INVOICE_SMALL_PRINT; ?>
                    <?php echo $hidden_field; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    foreach ($vendors as $vendor)
                    {
                        if ($use_posted_values)
                        {
                            $sml_prt = nbf_common::get_param($_POST, 'sml_prt_' . $vendor->id, null, true, false, true);
                        }
                        else
                        {
                            $sml_prt = $row->small_print;
                            if (nbf_common::nb_strlen($sml_prt) == 0 && !$row->id)
                            {
                                switch ($doc_suffix)
                                {
                                    case '_CR':
                                        $sml_prt = $vendor->credit_small_print;
                                        break;
                                    case '_QU':
                                        $sml_prt = $vendor->quote_small_print;
                                        break;
                                    default:
                                        $sml_prt = $vendor->small_print;
                                        break;
                                }
                            }
                        }
                        if ($vendor->id == $selected_vendor)
                        {
                            $visibility = " style=\"display:block;\" ";
                        }
                        else
                        {
                            $visibility = " style=\"display:none\" ";
                        }
                        echo "<div id=\"container_sml_prt_$vendor->id\" $visibility>";
                        echo nbf_cms::$interop->render_editor('sml_prt_' . $vendor->id, "sml_prt_" . $vendor->id, $sml_prt);
                        echo "</div>";
                    }
                ?>
                <?php if (!$doc_suffix) {nbf_html::show_static_help(NBILL_INSTR_INVOICE_SMALL_PRINT, "sml_prt_help");} ?>
                </td>
            </tr>
            <?php if ($doc_suffix == "") { ?>
            <tr id="nbill-admin-tr-delivery-small-print">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DELIVERY_SMALL_PRINT; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    foreach ($vendors as $vendor) {
                        if ($use_posted_values) {
                            $sml_prt_del = nbf_common::get_param($_POST, 'sml_prt_delivery_' . $vendor->id, null, true, false, true);
                        } else {
                            $sml_prt_del = $row->delivery_small_print;
                            if (strlen($sml_prt_del) == 0 && !$row->id) {
                                $sml_prt_del = $vendor->delivery_small_print;
                            }
                        }
                        if ($vendor->id == $selected_vendor) {
                            $visibility = " style=\"display:block;\" ";
                        } else {
                            $visibility = " style=\"display:none\" ";
                        }
                        echo "<div id=\"container_sml_prt_delivery_$vendor->id\" $visibility>";
                        echo nbf_cms::$interop->render_editor('sml_prt_delivery_' . $vendor->id, "sml_prt_delivery_" . $vendor->id, $sml_prt_del);
                        echo "</div>";
                    }
                ?>
                <?php if (!$doc_suffix) {nbf_html::show_static_help(NBILL_INSTR_DELIVERY_SMALL_PRINT, "sml_prt_delivery_help");} ?>
                </td>
            </tr>
            <?php } ?>
            </table>
        </div>

        <?php
        $tab_settings->add_tab_content("advanced", ob_get_clean());
        $tab_settings->end_tab_group();
        ?>

        <?php
        
         ?>

        <?php if ($row->id && $doc_suffix == "")
        { ?>
            <br />
            <div class="rounded-table">
		        <table cellpadding="3" cellspacing="0" border="0" width="100%" class="adminform">
                    <tr>
                        <th align="left"><?php echo NBILL_PAYLINK; ?></th>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php $url = nbf_cms::$interop->live_site . '/' . nbf_cms::$interop->site_page_prefix . '&action=invoices&task=pay&document_id=' . $row->id . nbf_cms::$interop->site_page_suffix; ?>
                                <a target="_blank" href="<?php echo $url; ?>"><?php echo $url; ?></a>
                            </strong>
                        </td>
                    </tr>
                </table>
            </div>
        <?php
        } ?>

		</form>

		<script type="text/javascript">
            <?php if (!$use_posted_values) { ?>refresh_vendor(<?php echo @$row->id ? 'false' : 'true'; ?>);<?php } ?>
            <?php if ($client_changed) {?>submit_ajax_request('client_changed', 'entity_id=' + document.getElementById('entity_id').value, update_client);get_default_tax_info(document.getElementById('product_id') ? document.getElementById('product_id').value : '');<?php } ?>
			<?php
            if ($scroll_to_items) {
                ?>
                window.location.hash='line_item_start';
                <?php
            } ?>
		</script>
		<?php
	}

	public static function printPreview($documents, $document_items, nBillINumberFactory $number_factory, $currencies, $line_items, $currency, $date_format, $tax_info, $shipping, $pdf, $internal = false, $pre_text = "", $post_text = "", $payment_details = array(), $use_local_image = false, $is_delivery_note = false)
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

        $nb_database = nbf_cms::$interop->database;
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.frontend.class.php");

        nbf_common::load_language("invoices");

		ob_start();
        if (!$pdf)
        {
            if (!headers_sent())
            {
                header("Content-Encoding: text/html"); //No gzip, thanks.
            }
        }

		$header = "";
		$footer = "";

		if (count($documents) > 0)
		{
            //Get template name etc.
            $doc_title = "";
            switch($documents[0]->document_type)
            {
                case "CR":
                    $template_name = $documents[0]->credit_template_name;
                    $fullpath = nbf_cms::$interop->nbill_fe_base_path . "/templates/$template_name/index.php";
                    if (nbf_common::nb_strlen($template_name) == 0 || !file_exists($fullpath))
                    {
                        $template_name = "credit_default";
                        $fullpath = nbf_cms::$interop->nbill_fe_base_path . "/templates/$template_name/index.php";
                        if (!file_exists($fullpath))
                        {
                            echo sprintf(NBILL_ERR_TEMPLATE_NOT_FOUND, $documents[0]->credit_template_name, $fullpath);
                            return;
                        }
                    }
                    $doc_title = "Credit Note";
                    break;
                
                default:
                    if ($is_delivery_note) {
                        $template_name = $documents[0]->delivery_template_name;
                        $fullpath = nbf_cms::$interop->nbill_fe_base_path . "/templates/$template_name/index.php";
                        if (nbf_common::nb_strlen($template_name) == 0 || !file_exists($fullpath))
                        {
                            $template_name = "delivery_default";
                            $fullpath = nbf_cms::$interop->nbill_fe_base_path . "/templates/$template_name/index.php";
                            if (!file_exists($fullpath))
                            {
                                echo sprintf(NBILL_ERR_TEMPLATE_NOT_FOUND, $documents[0]->delivery_template_name, $fullpath);
                                return;
                            }
                        }
                        $doc_title = "Delivery Note";
                    } else {
                        $template_name = $documents[0]->invoice_template_name;
                        $fullpath = nbf_cms::$interop->nbill_fe_base_path . "/templates/$template_name/index.php";
                        if (nbf_common::nb_strlen($template_name) == 0 || !file_exists($fullpath))
                        {
                            $template_name = "invoice_default";
                            $fullpath = nbf_cms::$interop->nbill_fe_base_path . "/templates/$template_name/index.php";
                            if (!file_exists($fullpath))
                            {
                                echo sprintf(NBILL_ERR_TEMPLATE_NOT_FOUND, $documents[0]->invoice_template_name, $fullpath);
                                return;
                            }
                        }
                        $doc_title = "Invoice";
                    }
                    break;
            }

            $include_path = nbf_cms::$interop->nbill_fe_base_path . "/templates/$template_name";
            $template_path = nbf_cms::$interop->nbill_fe_base_path . "/templates/$template_name";

            //Load colour scheme
            $sql = "SELECT title_colour, heading_bg_colour, heading_fg_colour FROM #__nbill_configuration WHERE id = 1";
            $nb_database->setQuery($sql);
            $nb_database->loadObject($colour_scheme);
            $title_colour = $colour_scheme->title_colour;
            $heading_bg_colour = $colour_scheme->heading_bg_colour;
            $heading_fg_colour = $colour_scheme->heading_fg_colour;

			if (!$pdf)
			{
                //Include the CSS file, if applicable (not for pdf, as this html will be appended for each page)
				$css = file_get_contents("$include_path/template.css");
				$print_css = file_exists("$include_path/print.css");

				$header = '<!DOCTYPE html>
							<html>
		  				<head>';
                if (!($internal && strlen($pre_text) > 0)) {
                    $header .= '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
                        <title>' . $doc_title . '</title><base href="' . nbf_cms::$interop->live_site . '" />';
                    $header .= '<meta http-equiv="Content-Type" content="text/html; charset=' . nbf_cms::$interop->char_encoding . '" />';
                    if (strlen($css) > 0)
				    {
                        $css = str_replace('##title_colour##', $title_colour, $css);
                        $css = str_replace('##heading_bg_colour##', $heading_bg_colour, $css);
                        $css = str_replace('##heading_fg_colour##', $heading_fg_colour, $css);
					    $header .= "<style type=\"text/css\">\n$css\n</style>\n";
				    }
				    if ($print_css)
				    {
					    //Have to link to this, as it doesn't work if sent with the HTML
					    $header .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . nbf_cms::$interop->nbill_site_url_path . "/templates/$template_name/print.css" . "\" media=\"print\" />\n";
				    }
                }
				$header .= "</head><body>";
				$footer = '</body></html>';
			}

			$tax_rates = array();
			$tax_rate_amounts = array();
			$currency_symbol = array();

            $first_invoice_done = false;

            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.tax.class.php");
			foreach ($documents as $document)
			{
                if ($first_invoice_done) {
                    //Multiple invoices printed at once - need page break between them
                    echo '<!--NewPage--><div style="page-break-before:always;"></div>';
                } else {
                    $first_invoice_done = true;
                }

                //Check whether to show remittance advice and/or payment link
                $show_remittance = $document->show_remittance;
                $show_paylink = nbf_common::check_show_paylink($document, nbf_common::get_display_options());

				//Get currency symbol
				$currency_symbol[$document->id] = "";
				foreach ($currency as $currency_item)
				{
					if ($currency_item->code == $document->currency)
					{
						$currency_symbol[$document->id] = $currency_item->symbol;
						break;
					}
				}

				//Get logo info
                if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . $document->vendor_id . ".gif")) {
                    $logo_file = nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . $document->vendor_id . ".gif";
                    if ($pdf) {
                        $logo_src = $use_local_image ? 'file://' . $logo_file : nbf_cms::$interop->nbill_site_url_path . "/images/vendors/" . $document->vendor_id . ".gif";
                    } else {
                        $logo_src = nbf_cms::$interop->live_site . "/" . htmlentities(nbf_cms::$interop->site_popup_page_prefix, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) . "&amp;action=show_image&amp;file_name=vendors/" . $document->vendor_id . ".gif" . htmlentities(nbf_cms::$interop->public_site_page_suffix(), ENT_COMPAT | 0, nbf_cms::$interop->char_encoding);
                    }
                } else {
                    $logo_file = nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . $document->vendor_id . ".png";
                    if ($pdf) {
                        $logo_src = $use_local_image ? 'file://' . $logo_file : nbf_cms::$interop->nbill_site_url_path . "/images/vendors/" . $document->vendor_id . ".png";
                    } else {
                        $logo_src = nbf_cms::$interop->live_site . "/" . htmlentities(nbf_cms::$interop->site_popup_page_prefix, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) . "&amp;action=show_image&amp;file_name=vendors/" . $document->vendor_id . ".png" . htmlentities(nbf_cms::$interop->public_site_page_suffix(), ENT_COMPAT | 0, nbf_cms::$interop->char_encoding);
                    }
                }

                include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.frontend.class.php");
				nbf_tax::get_tax_rates($document, $document_items, $shipping, $tax_info, $tax_name, $tax_rates, $tax_rate_amounts, false, true, !nbf_frontend::get_display_option("suppress_zero_tax"));

				//Work out which fields can be hidden if space is limited
				$hide_unit_price[$document->id] = true;
				$hide_quantity[$document->id] = true;
				$hide_discount[$document->id] = true;
				$hide_net_price[$document->id] = true;
				$hide_tax[$document->id] = true;
				$hide_shipping[$document->id] = true;
				$hide_shipping_tax[$document->id] = true;

				foreach ($document_items as $document_item)
				{
					if ($document_item->document_id == $document->id)
					{
						if ($document_item->no_of_units != 1)
						{
							$hide_unit_price[$document->id] = false;
							$hide_quantity[$document->id] = false;
						}
						if ($document_item->discount_amount > 0)
						{
							$hide_discount[$document->id] = false;
						}
						if ($document_item->tax_for_item > 0)
						{
							$hide_net_price[$document->id] = false;
							$hide_tax[$document->id] = false;
						}
						if ($document_item->shipping_for_item > 0)
						{
                            $hide_net_price[$document->id] = false;
							$hide_shipping[$document->id] = false;
						}
						if ($document_item->tax_for_shipping > 0)
						{
                            $hide_net_price[$document->id] = false;
							$hide_shipping_tax[$document->id] = false;
						}
					}
				}

                if (!nbf_frontend::get_display_option("suppress_zero_tax"))
                {
                    $hide_net_price[$document->id] = false;
                    $hide_tax[$document->id] = false;
                    $hide_shipping_tax[$document->id] = $hide_shipping[$document->id];
                }

				//Include language files
                nbf_common::load_language("template.common");
                nbf_common::load_language("template." . nbf_common::nb_strtolower($document->document_type));

				//Populate payment details variables
				$payment_date = null;
				$payment_method = null;
				$transaction_no = null;
				if (array_key_exists($document->id, $payment_details))
				{
                    //For backward compatibility, set the info for the last payment made...
					$payment_date = @$payment_details[$document->id][count($payment_details[$document->id]) - 1]->date;
                    foreach ($payment_details[$document->id] as $payment_detail)
                    {
                        if (!defined($payment_detail->pay_method))
                        {
                            if (defined($payment_detail->gateway_name))
                            {
                                $payment_detail->pay_method = $payment_detail->gateway_name;
                            }
                            else
                            {
                                $payment_detail->pay_method = 'NBILL_PAY_METHOD_TEMP_' . $payment_detail->gateway_name;
                                if (!defined('NBILL_PAY_METHOD_TEMP_' . $payment_detail->gateway_name))
                                {
                                    define('NBILL_PAY_METHOD_TEMP_' . $payment_detail->gateway_name, $payment_detail->gateway_name);
                                }
                            }
                        }
                        $payment_method = @constant($payment_detail->pay_method);
                    }

                    $transaction_no = @$payment_details[$document->id][count($payment_details[$document->id]) - 1]->transaction_no;
                    if (nbf_common::nb_strlen($transaction_no) == 0)
                    {
                        unset($transaction_no);
                    }
				}

                //For backward compatability with templates that don't support sections and section discounts, apply any
                //section discounts to the document items themselves so that the line items still add up to the total
                $template_contents = file_get_contents($fullpath);
                if (strpos($template_contents, '$line_items') === false && strpos($template_contents, 'section_discount_percent') === false)
                {
                    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.process.discount.class.php");
                    nbf_discount::apply_section_discounts($document_items);
                }

                //Either way, provide a separate copy for the total summary which takes section discounts into account
                $summary_doc_items = $document_items;
                nbf_discount::apply_section_discounts($summary_doc_items, $document->id);

                include($fullpath);
			}

			$html = ob_get_contents();
			@ob_end_clean();

			if ($pdf)
			{
				return $html;
			}
			else if ($internal)
			{
				ob_start(); //Re-start output buffer in case we are continuing with something else after this
				return $header . "<div style=\"font-size:10pt;\">$pre_text</div>" . $html . "<div style=\"font-size:10pt\">$post_text</div>" . $footer;
			}
			else
			{
				echo $header . $html . $footer;
				exit();
			}
		}
		@ob_end_clean();
		ob_start(); //Re-start output buffer in case we are continuing with something else after this
	}

	public static function showEMailInvoice($document_id, $client_credit = array())
	{
        //Output form
        $feature = nbf_common::nb_strtoupper(nbf_common::get_param($_REQUEST, 'action'));
        ?>
        <table class="adminheading" style="width:auto;">
        <tr>
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, nbf_common::get_param($_REQUEST, 'action')); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . @constant("NBILL_$feature" . "_TITLE"); ?>
            </th>
        </tr>
        </table>

        <div class="nbill-message-ie-padding-bug-fixer"></div>
        <?php
        nbf_cms::$interop->init_editor();
        if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
        {
            echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
        }
        $return_url = nbf_common::get_param($_REQUEST, 'return');
        if (nbf_common::nb_strlen($return_url) > 0)
        {
            $return_url = base64_decode($return_url);
            if ($return_url == false)
            {
                $return_url = "";
            }
        }
        ?>

        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <?php
        $hidden_fields_output = false;
        if (nbf_common::nb_strlen($return_url) > 0)
        {
            $return_params = explode("&", $return_url);
            foreach ($return_params as $return_param)
            {
                $param = explode("=", $return_param);
                if (count($param) == 2)
                {
                    if ($param[0] != nbf_cms::$interop->admin_page_prefix)
                    {
                        ?>
                        <input type="hidden" name="<?php echo $param[0]; ?>" value="<?php echo $param[1]; ?>" />
                        <?php
                    }
                }
            }
        }
        if (!$hidden_fields_output)
        { ?>
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
        <input type="hidden" name="task" value="view" />
        <input type="hidden" name="id" value="<?php echo $document_id; ?>" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
        <?php
        }
        nbf_email::show_email_form_for_document($document_id, "", $client_credit);
        ?>
        </form>
        <?php
	}

	public static function sendEmailDone($success)
	{
		$doc_suffix = "";
		if (nbf_common::get_param($_REQUEST, 'action') == "credits")
		{
			$doc_suffix = "_CR";
		}

		//Get parameters for sending e-mail (this should always be in a popup window)
		@ob_end_clean();

		//echo "<?xml version=\"1.0\" encoding=\"" . nbf_cms::$interop->char_encoding . "\"?>";
		?>
		<!DOCTYPE html>
        <html>
		<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <meta http-equiv="content-type" content="text/html; charset=<?php echo nbf_cms::$interop->char_encoding; ?>" />

		<title><?php echo NBILL_SEND_EMAIL; ?></title>
		<style>
			body
			{
				font-family: Arial, Helvetica, sans-serif;
				font-size: 80%;
			}
			.adminform th
			{
				background-color: #eeeeee;
				width: 100%;
			}
		</style>
		</head>
		<body style="padding:10px;">
			<div class="message">
			<?php echo $success ? NBILL_EMAIL_SENT_SUCCESS : @constant("NBILL_EMAIL_SENT_FAILURE$doc_suffix"); ?>
			</div>
			<br /><br />
			<div align="center" style="text-align:center;width:100%;">
				<div style="white-space:nowrap;margin-left:auto;margin-right:auto;"><a href="javascript:window.close();"><?php echo NBILL_CLOSE_WINDOW; ?></a></div>
			</div>
		</body>
        </html>
		<?php
		exit();
	}
}