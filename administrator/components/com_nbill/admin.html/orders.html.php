<?php
/**
* HTML output for orders feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillOrders
{
    protected static $custom_column_count = 0;

	public static function showOrders($rows, $pagination, $vendors, $categories, $date_format, $xref_status, $attachments = array())
	{
		nbf_html::load_calendar();
        $vendor_col = false;
		?>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "orders"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_ORDERS_TITLE; ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . str_replace('\n', '<br />', nbf_globals::$message) . "</div>";
		} ?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="orders" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
        <input type="hidden" name="attachment_id" value="" />

		<p align="left"><?php echo NBILL_ORDERS_INTRO ?></p>

        <div style="float:right">
            <strong><?php echo NBILL_ORDER_MULTI_STATUS_UPDATE; ?></strong><br />
            <?php echo NBILL_ORDER_SET_STATUS_TO . '&nbsp;<br />';
            $status_array = array();
            $status_array[] = nbf_html::list_option('', NBILL_NOT_APPLICABLE);
            foreach ($xref_status as $status)
            {
                $status_array[] = nbf_html::list_option($status->code, $status->description);
            }
            echo nbf_html::select_list($status_array, "multi_status_update", 'id="multi_status_update" class="inputbox"', ''); ?>
            <input type="hidden" name="multi_status_update_submit" id="multi_status_update_submit" value="" />
            <input type="button" name="multi_status_update_submit_button" class="btn button nbill-button" value="<?php echo NBILL_GO; ?>" onclick="if(document.getElementById('multi_status_update').value.length==0){alert('<?php echo NBILL_ORDER_MULTI_STATUS_SELECT; ?>');return false;}else{if(document.adminForm.box_checked.value == 0){alert('<?php echo NBILL_ORDER_MULTI_STATUS_SELECT_RECORDS; ?>');return false;}else{if(confirm('<?php echo NBILL_ORDER_MULTI_STATUS_SURE; ?>')){document.getElementById('multi_status_update_submit').value='1';document.adminForm.submit();}}}" />
        </div>
		<?php
			echo "<p align=\"left\"><span style=\"white-space:nowrap;\">";
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
				echo nbf_html::select_list($vendor_name, "vendor_filter", 'id="vendor_filter" class="inputbox" onchange="document.adminForm.submit();"', $selected_filter);
			}
			else
			{
				echo "<input type=\"hidden\" name=\"vendor_filter\" id=\"vendor_filter\" value=\"" . $vendors[0]->id . "\" />";
				$_POST['vendor_filter'] = $vendors[0]->id;
			}
			echo "</span> &nbsp;&nbsp;<span style=\"white-space:nowrap;\">" . NBILL_ORDER_NO;?>&nbsp;<input type="text" name="order_no_search" value="<?php echo nbf_common::get_param($_REQUEST,'order_no_search', '', true); ?>" id="order_no_search" /></span>
            <?php echo " &nbsp;&nbsp;<span style=\"white-space:nowrap;\">" . NBILL_CLIENT;?>&nbsp;<input type="text" name="client_search" value="<?php echo nbf_common::get_param($_REQUEST,'client_search', '', true); ?>" /></span>
			<?php echo " &nbsp;&nbsp;<span style=\"white-space:nowrap;\">" . NBILL_PRODUCT;?>&nbsp;<input type="text" name="product_search" value="<?php echo nbf_common::get_param($_REQUEST,'product_search', '', true); ?>" /></span>
			<?php echo " &nbsp;&nbsp;<span style=\"white-space:nowrap;\">" . NBILL_RELATING_TO;?>&nbsp;<input type="text" name="relating_to_search" value="<?php echo nbf_common::get_param($_REQUEST,'relating_to_search', '', true); ?>" /></span>
            <?php echo " &nbsp;&nbsp;<span style=\"white-space:nowrap;\">" . NBILL_ORDER_STATUS;?>&nbsp;<?php
            echo nbf_html::select_list($status_array, "status_search", 'id="status_search" class="inputbox"', nbf_common::get_param($_REQUEST,'status_search'));
			echo "</span> &nbsp;&nbsp;<span style=\"white-space:nowrap;\">" . NBILL_DATE_RANGE; $cal_date_format = nbf_common::get_date_format(true); ?>
			<input type="text" name="search_date_from" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_from'); ?>" <?php if (nbf_common::get_param($_REQUEST,'show_all')) {echo "disabled=\"disabled\"";} ?> />
			<input type="button" name="search_date_from_cal" class="btn button nbill-button" value="..." onclick="displayCalendar(document.adminForm.search_date_from,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_REQUEST,'show_all')) {echo "disabled=\"disabled\"";} ?> /></span>
			<span style="white-space:nowrap;"><?php echo NBILL_TO; ?>
			<input type="text" name="search_date_to" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_to'); ?>" <?php if (nbf_common::get_param($_REQUEST,'show_all')) {echo "disabled=\"disabled\"";} ?> />
			<input type="button" name="search_date_to_cal" class="btn button nbill-button" value="..." onclick="displayCalendar(document.adminForm.search_date_to,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_REQUEST,'show_all')) {echo "disabled=\"disabled\"";} ?> /></span>
			<span style="white-space:nowrap;"><input type="submit" class="btn button nbill-button" name="<?php echo nbf_common::get_param($_REQUEST, 'show_all') ? 'show_reset' : 'show_all'; ?>" value="<?php echo nbf_common::get_param($_REQUEST, 'show_all') ? NBILL_ORDER_SHOW_RESET : NBILL_ORDER_SHOW_ALL; ?>" />
            <?php if (nbf_common::get_param($_REQUEST, 'show_all')) {echo "<input type=\"hidden\" name=\"show_all\" value=\"1\" />";} ?>
            <input type="submit" class="btn button nbill-button" name="dosearch" value="<?php echo NBILL_GO; ?>" /></span>
            <input type="hidden" name="do_csv_download" id="do_csv_download" value="" />
            <?php if ($pagination->record_count > nbf_globals::$record_limit)
            {
                $csv_click = "if (confirm('" . sprintf(NBILL_CSV_EXPORT_LIMIT_WARNING, nbf_globals::$record_limit, nbf_globals::$record_limit, nbf_globals::$record_limit) . "')){document.getElementById('do_csv_download').value=1;document.adminForm.submit();document.getElementById('do_csv_download').value='';}return false;";
            }
            else
            {
                $csv_click = "document.getElementById('do_csv_download').value=1;document.adminForm.submit();document.getElementById('do_csv_download').value='';return false;";
            } ?>
            &nbsp;
            <span style="white-space:nowrap;"><a href="#" title="<?php echo NBILL_CSV_DOWNLOAD_LIST_DESC; ?>" onclick="<?php echo $csv_click; ?>"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path ?>/images/icons/medium/csv.gif" alt="<?php echo NBILL_CSV_DOWNLOAD_LIST_DESC; ?>" style="vertical-align:middle" /></a>
            <strong><a href="#" title="<?php echo NBILL_CSV_DOWNLOAD_LIST_DESC; ?>" onclick="<?php echo $csv_click; ?>"><?php echo NBILL_CSV_DOWNLOAD; ?></a></strong></span>
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
				    <?php echo NBILL_ORDER_NO; ?>
			    </th>
                <?php self::renderCustomColumn('order_no'); ?>
			    <th class="title">
				    <?php echo NBILL_ORDER_PRODUCT_NAME; ?>
			    </th>
                <?php self::renderCustomColumn('product'); ?>
			    <th class="title responsive-cell high-priority">
				    <?php echo NBILL_ORDER_START_DATE; ?>
			    </th>
                <?php self::renderCustomColumn('start_date'); ?>
                <th class="title responsive-cell optional">
                    <?php echo NBILL_NEXT_DUE_DATE; ?>
                </th>
                <?php self::renderCustomColumn('next_due_date'); ?>
			    <th class="title">
				    <?php echo NBILL_CLIENT_NAME; ?>
			    </th>
                <?php self::renderCustomColumn('client'); ?>
			    <th class="title responsive-cell priority">
				    <?php echo NBILL_ORDER_RELATING_TO; ?>
			    </th>
                <?php self::renderCustomColumn('relating_to'); ?>
			    <th class="title responsive-cell extra-wide-only">
				    <?php echo NBILL_ORDER_STATUS; ?>
			    </th>
                <?php self::renderCustomColumn('status'); ?>
			    <th class="title responsive-cell wide-only" style="text-align:right;white-space:nowrap;">
				    <?php echo NBILL_ORDER_ORDER_VALUE; nbf_html::show_overlib(NBILL_ORDER_ORDER_VALUE_HELP); ?>
			    </th>
                <?php self::renderCustomColumn('order_value'); ?>
			    <?php
				    //Only show vendor name if more than one listed
				    if (count($vendors) > 1 && $selected_filter == -999)
				    {?>
					    <th class="title">
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
				    $link = nbf_cms::$interop->admin_page_prefix . "&action=orders&task=edit&cid=$row->id&search_date_from=" . nbf_common::get_param($_REQUEST,'search_date_from') . "&search_date_to=" . nbf_common::get_param($_REQUEST,'search_date_to') . "&vendor_filter=" . nbf_common::get_param($_REQUEST,'vendor_filter') . "&relating_to_search=" . nbf_common::get_param($_REQUEST,'relating_to_search') . "&client_search=" . nbf_common::get_param($_REQUEST,'client_search') . "&product_search=" . nbf_common::get_param($_REQUEST,'product_search') . "&nbill_no_search=" . nbf_common::get_param($_REQUEST,'nbill_no_search');
				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\">$checked</td>";
                    self::renderCustomColumn('id', $row);
				    echo "<td class=\"list-value\"><a href=\"$link\" title=\"" . NBILL_EDIT_ORDER . "\">";
                    ?>
                    <div style="float:right;">
                        <?php
                        echo $row->order_no . "</a>";
                        ?>
                        <div style="white-space:nowrap;float:right;">
                        <?php
                        if ($row->id)
				        {
					        $invoice_link = nbf_cms::$interop->admin_page_prefix . "&action=invoices&task=viewfororder&order_id=$row->id&show_all=1";
					        echo "&nbsp;&nbsp;&nbsp;<a href=\"$invoice_link\" title=\"" . NBILL_SHOW_INVOICES_FOR_ORDER . "\"><img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/invoices.gif\" alt=\"" . NBILL_SHOW_INVOICES_FOR_ORDER . "\" border=\"0\" style=\"vertical-align:middle\" /></a>";
				        }
                        if (file_exists(nbf_cms::$interop->nbill_admin_base_path . '/admin.proc/supporting_docs.php')) {
                        ?>
                        <a href="#" onclick="<?php if ($row->attachment_count){ ?>var att=document.getElementById('attachments_<?php echo $row->id; ?>');if(att.style.display=='none'){att.style.display='';}else{att.style.display='none';}<?php }else{ ?>window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&use_stylesheet=1&show_toolbar=1&attach_to_type=OR&attach_to_id=<?php echo $row->id; ?>','','scrollbars=1,width=790,height=500');<?php } ?>return false;"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/supporting_docs.gif" alt="<?php echo NBILL_ATTACHMENTS; ?>" style="vertical-align:middle;" /><?php if ($row->attachment_count) {echo " (" . $row->attachment_count . ")";} ?></a>
                        <div id="attachments_<?php echo $row->id; ?>" style="display:none;text-align:right;float:right;">
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
                                        <input type="button" class="btn button nbill-button" value="<?php echo NBILL_DETACH; ?>" onclick="if(confirm('<?php echo NBILL_DETACH_SURE; ?>')){document.adminForm.attachment_id.value='<?php echo $attachment->id; ?>';document.adminForm.task.value='detach_file';document.adminForm.submit();}" />
                                    </td>
                                    <td>
                                        <input type="button" class="btn button nbill-button" value="<?php echo NBILL_DELETE; ?>" onclick="if(confirm('<?php echo sprintf(NBILL_DELETE_FILE_SURE, $attachment->file_name); ?>')){document.adminForm.attachment_id.value='<?php echo $attachment->id; ?>';document.adminForm.task.value='delete_file';document.adminForm.submit();}" />
                                    </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                            <tr><td colspan="3">
                            <a href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&use_stylesheet=1&show_toolbar=1&attach_to_type=OR&attach_to_id=<?php echo $row->id; ?>','','scrollbars=1,width=790,height=500');return false;"><img style="vertical-align:middle" border="0" alt="" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/supporting_docs.gif" />&nbsp;<?php echo NBILL_NEW_ATTACHMENT; ?></a>
                            </td></tr>
                            </table>
                        </div>
                        <?php } ?>
                        </div>
                    </div>
                    <?php
				    echo "</td>";
                    self::renderCustomColumn('order_no', $row);
                    echo "<td class=\"list-value\">";
                    if ($row->product_id > 0)
                    {
				        $product_link = nbf_cms::$interop->admin_page_prefix . "&action=products&task=edit&cid=" . $row->product_id . "&return=" . base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=orders&search_date_from=" . nbf_common::get_param($_REQUEST,'search_date_from') . "&search_date_to=" . nbf_common::get_param($_REQUEST,'search_date_to') . "&vendor_filter=" . nbf_common::get_param($_REQUEST,'vendor_filter') . "&relating_to_search=" . nbf_common::get_param($_REQUEST,'relating_to_search') . "&client_search=" . nbf_common::get_param($_REQUEST,'client_search') . "&product_search=" . nbf_common::get_param($_REQUEST,'product_search') . "&nbill_no_search=" . nbf_common::get_param($_REQUEST,'nbill_no_search'));
				        echo "<a href=\"$product_link\">";
                    }
				    if (nbf_common::nb_strlen($row->product_code) > 0 && substr($row->product_name, 0, nbf_common::nb_strlen($row->product_code . " - ")) != $row->product_code . " - ")
				    {
					    echo $row->product_code . ' - ';
				    }
				    echo $row->product_name;
                    if ($row->product_id > 0)
                    {
                        echo "</a>";
                    }
                    echo "</td>";
                    self::renderCustomColumn('product', $row);
				    echo "<td class=\"list-value responsive-cell high-priority\">" . nbf_common::nb_date($date_format, $row->start_date) . "</td>";
                    self::renderCustomColumn('start_date', $row);
                    echo "<td class=\"list-value responsive-cell optional\">" . ($row->next_due_date ? nbf_common::nb_date($date_format, $row->next_due_date) : '') . "</td>";
                    self::renderCustomColumn('next_due_date', $row);
				    $client_link = nbf_cms::$interop->admin_page_prefix . "&action=clients&task=edit&cid=" . $row->client_id . "&return=" . base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=orders&search_date_from=" . nbf_common::get_param($_REQUEST,'search_date_from') . "&search_date_to=" . nbf_common::get_param($_REQUEST,'search_date_to') . "&vendor_filter=" . nbf_common::get_param($_REQUEST,'vendor_filter') . "&relating_to_search=" . nbf_common::get_param($_REQUEST,'relating_to_search') . "&client_search=" . nbf_common::get_param($_REQUEST,'client_search') . "&product_search=" . nbf_common::get_param($_REQUEST,'product_search') . "&nbill_no_search=" . nbf_common::get_param($_REQUEST,'nbill_no_search'));
				    echo "<td class=\"list-value\"><a href=\"$client_link\">" . $row->company_name;
				    if (nbf_common::nb_strlen($row->contact_name) > 0)
				    {
					    if (nbf_common::nb_strlen($row->company_name) > 0)
					    {
						    echo " (";
					    }
					    echo $row->contact_name;
					    if (nbf_common::nb_strlen($row->company_name) > 0)
					    {
						    echo ")";
					    }
				    }
				    echo "</a>";
                    if (strlen($row->email_address) > 0)
                    {
                        echo "&nbsp;<a href=\"mailto:" . $row->email_address . "\" title=\"" . $row->email_address . "\"><img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/email-now.gif\" border=\"0\" alt=\"" . $row->email_address . "\" style=\"vertical-align:middle\" /></a>";
                    }
                    echo "</td>";
                    self::renderCustomColumn('client', $row);
				    echo "<td class=\"list-value responsive-cell priority word-breakable\">" . $row->relating_to . "</td>";
                    self::renderCustomColumn('relating_to', $row);
				    echo "<td class=\"list-value responsive-cell extra-wide-only\">" . @constant($row->order_status_desc) . "</td>";
                    self::renderCustomColumn('status', $row);
				    echo "<td class=\"list-value responsive-cell wide-only\" style=\"text-align:right\">" . format_number($row->net_price, 'currency', true, false, null, $row->currency) . "</td>";
                    self::renderCustomColumn('order_value', $row);

				    //Only show vendor name if more than one listed
				    $vendor_col = false;
				    if (count($vendors) > 1 && $selected_filter == -999)
				    {
					    foreach ($vendors as $vendor)
					    {
						    if ($vendor->id == $row->vendor_id)
						    {
							    echo "<td class=\"list-value\">" . $vendor->vendor_name . "</td>";
							    $vendor_col = true;
							    break;
						    }
					    }
				    }
                    self::renderCustomColumn('vendor', $row);
				    echo "</tr>";
			    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="<?php echo ($vendor_col ? 11 : 10) + self::$custom_column_count; ?>" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		</form>
		<?php
	}

    protected static function renderCustomColumn($column_name, $row = 'undefined')
    {
        $method = ($row == 'undefined') ? 'render_header' : 'render_row';
        if (file_exists(dirname(__FILE__) . "/custom_columns/orders/after_$column_name.php")) {
            include_once(dirname(__FILE__) . "/custom_columns/orders/after_$column_name.php");
            if (is_callable(array("nbill_admin_orders_after_$column_name", $method))) {
                call_user_func(array("nbill_admin_orders_after_$column_name", $method), $row);
                if ($method == 'render_header') {
                    self::$custom_column_count++;
                }
            }
        }
    }

    public static function downloadOrderListCSV($vendors, $rows, $date_format)
    {
        nbf_common::load_language("contacts");
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
        echo NBILL_ORDER_NO . ",";
        echo NBILL_ORDER_PRODUCT_NAME . ",";
        echo NBILL_ORDER_RELATING_TO . ",";
        echo NBILL_ORDER_STATUS . ",";
        echo NBILL_ORDER_ORDER_VALUE . ",";
        echo NBILL_ORDER_PAYMENT_FREQUENCY . ",";
        echo NBILL_ORDER_START_DATE . ",";
        echo NBILL_CSV_COMPANY_NAME . ",";
        echo NBILL_CONTACT_FIRST_NAME . ",";
        echo NBILL_CONTACT_LAST_NAME . ",";
        echo NBILL_EMAIL_ADDRESS . ",";
        echo NBILL_TELEPHONE . ",";
        echo NBILL_MOBILE . ",";
        echo NBILL_FAX . ",";
        echo NBILL_NEXT_DUE_DATE;

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
            echo "\"" . str_replace("\"", "\"\"", $row->order_no) . "\",";
            echo "\"";
            if (nbf_common::nb_strlen($row->product_code) > 0 && substr($row->product_name, 0, nbf_common::nb_strlen($row->product_code . " - ")) != $row->product_code . " - ")
            {
                echo str_replace("\"", "\"\"", $row->product_code) . ' - ';
            }
            echo str_replace("\"", "\"\"", $row->product_name) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->relating_to) . "\",";
            echo "\"" . str_replace("\"", "\"\"", @constant($row->order_status_desc)) . "\",";
            echo format_number($row->net_price, 'currency') . ",";
            echo "\"" . str_replace("\"", "\"\"", @constant($row->payment_frequency_desc)) . "\",";
            echo nbf_common::nb_date($date_format, $row->start_date) . ",";
            echo "\"" . str_replace("\"", "\"\"", $row->company_name) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->first_name) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->last_name) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->email_address) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->telephone) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->mobile) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->fax) . "\",";
            echo nbf_common::nb_date($date_format, $row->next_due_date);
            echo "\r\n";
        }
    }

	/**
	* Edit an order (or create a new one)
	*/
	public static function editOrder($order_id, $row, $currencies, $tax_rates, $vendors, $clients,
					$selected_client_row, nBillClient $client, $categories, $shipping, $selected_cats, $products,
					$selected_products, $pay_frequencies, $product_currencies, $use_posted_values,
					$xref_status, $recalculate = false, $discounts, $order_discounts, $attachments = array())
	{
        $config = nBillConfigurationService::getInstance()->getConfig();
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
		nbf_html::load_calendar();
        $default_tax_rate = 0;
		?>
		<script language="javascript" type="text/javascript">
		<?php nbf_html::add_js_validation_numeric();
		nbf_html::add_js_validation_date(); ?>
		<?php
			$subs_present = false;
			foreach ($products as $product_list)
			{
				foreach ($product_list as $product)
				{
					if ($product->is_sub)
					{
						$subs_present = true;
						break;
					}
				}
				if ($subs_present)
				{
					break;
				}
			}

			if ($subs_present)
			{
				//Get list of client/users (so if product selected is a subscription we know if there is a user id)
				$client_count = count($clients);
				?>
				var client_users = new Array(<?php echo $client_count; ?>);
				<?php
				foreach ($clients as $this_client)
				{
					?>
					client_users[<?php echo $this_client->id; ?>] = <?php echo intval($this_client->user_id); ?>;
					<?php
				}
			}
		?>

		function nbill_submit_task(task_name)
		{
			var form = document.adminForm;
			var vendor_id = document.getElementById('vendor_id').value;
			if (task_name == 'cancel') {
				document.adminForm.task.value=task_name;
                document.adminForm.submit();
				return;
			}
			<?php
			$cal_date_format = nbf_common::get_date_format(true);
			?>

			// do field validation
			if (form.product_name.value == "")
			{
				alert('<?php echo NBILL_ORDER_PRODUCT_NAME_REQUIRED; ?>');
			}
			else if (document.getElementById('client_id').value == "")
			{
				alert('<?php echo NBILL_CLIENT_REQUIRED; ?>');
			}
			else if (form.net_price.value == "")
			{
				alert('<?php echo NBILL_ORDER_NET_PRICE_REQUIRED; ?>');
			}
			else if (form.start_date.value == "")
			{
				alert('<?php echo NBILL_ORDER_START_DATE_REQUIRED; ?>');
			}
			else if (form.net_price.value > 0 && form.payment_frequency.value == "")
			{
				alert('<?php echo NBILL_PAY_FREQUENCY_REQUIRED; ?>');
			}
			else if (!IsNumeric(form.quantity.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_ORDER_QUANTITY); ?>');
			}
			else if (!IsValidDate(form.start_date.value, false))
			{
				alert('<?php echo sprintf(NBILL_INVALID_DATE_FIELD, NBILL_START_DATE, $cal_date_format); ?>');
			}
			else if (!IsValidDate(form.expiry_date.value, true))
			{
				alert('<?php echo sprintf(NBILL_INVALID_DATE_FIELD, NBILL_EXPIRY_DATE, $cal_date_format); ?>');
			}
			else if (!IsValidDate(form.last_due_date.value, true))
			{
				alert('<?php echo sprintf(NBILL_INVALID_DATE_FIELD, NBILL_LAST_DUE_DATE, $cal_date_format); ?>');
			}
			else if (!IsValidDate(form.next_due_date.value, true))
			{
				alert('<?php echo sprintf(NBILL_INVALID_DATE_FIELD, NBILL_NEXT_DUE_DATE, $cal_date_format); ?>');
			}
			else if (!IsValidDate(form.cancellation_date.value, true))
			{
				alert('<?php echo sprintf(NBILL_INVALID_DATE_FIELD, NBILL_CANCELLATION_DATE, $cal_date_format); ?>');
			}
			else if (!IsNumeric(form.net_price.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_ORDER_NET_PRICE); ?>');
			}
			else if (!IsNumeric(form.total_tax_amount.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_ORDER_TAX_AMOUNT); ?>');
			}
			else if (!IsNumeric(form.total_shipping_price.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_ORDER_TOTAL_SHIPPING); ?>');
			}
			else if (!IsNumeric(form.total_shipping_tax.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_ORDER_SHIPPING_TAX); ?>');
			}
			else
			{
				var quantity_ok = true;
				if (form.quantity.value == '' || form.quantity.value == '0')
				{
					quantity_ok = confirm(NBILL_ORDER_WARNING_QTY_ZERO);
				}
				if (quantity_ok)
				{
					<?php
                    if ($subs_present)
					{ ?>
						switch (document.getElementById('product_' + document.getElementById('vendor_id').value).value)
						{
							<?php
							$sub_count = 0;
							foreach ($products as $product_item)
							{
								foreach ($product_item as $product)
								{
									if ($product->is_sub)
									{
										echo "  case '" . $product->id . "':\n";
										$sub_count++;
									}
								}
							}
							if ($sub_count > 0)
							{
								?>
								if (client_users[document.getElementById('client_id').value] > 0)
								{
									presubmit();
									document.adminForm.task.value=task_name;
                                    document.adminForm.submit();
								}
								else
								{
									alert('<?php echo NBILL_CANNOT_ORDER_SUB_WITHOUT_USER; ?>');
								}
								break;
								<?php
							}	?>
							default:
								presubmit();
								document.adminForm.task.value=task_name;
                                document.adminForm.submit();
								break;
						}
					<?php
					}
					else
					{
						?>
						presubmit();
						document.adminForm.task.value=task_name;
                        document.adminForm.submit();
						<?php
					} ?>
				}
			}
		}

		function presubmit()
		{
			//Add any newly added discounts to the hidden field so we can pick them up on postback
			<?php foreach ($vendors as $vendor)
			{?>
				var discountCount_<?php echo $vendor->id; ?> = document.getElementById('discount_<?php echo $vendor->id; ?>_count').value;
				var discounts_<?php echo $vendor->id;?>;
				discounts_<?php echo $vendor->id; ?> = '';
				for (var i=0; i< discountCount_<?php echo $vendor->id; ?>; i++)
				{
					if (i > 0)
					{
						discounts_<?php echo $vendor->id; ?> += '|';  //new discount delimiter
					}
					discounts_<?php echo $vendor->id; ?> += document.getElementById('discount_<?php echo $vendor->id; ?>_' + i + '_id').value;
					discounts_<?php echo $vendor->id; ?> += '#' + document.getElementById('discount_<?php echo $vendor->id; ?>_' + i + '_priority').value;
				}
				document.getElementById('discount_added_items_<?php echo $vendor->id; ?>').value = discounts_<?php echo $vendor->id; ?>;
			<?php
			}
			?>
		}

		function refresh_vendor()
		{
			//Show the appropriate dropdowns depending on selected vendor
			var vendor_id = document.getElementById('vendor_id').value;
			<?php
			foreach ($vendors as $vendor)
			{
				echo "document.getElementById('category_" . $vendor->id . "').style.display = 'none';";
				echo "document.getElementById('product_" . $vendor->id . "').style.display = 'none';";
				echo "document.getElementById('shipping_" . $vendor->id . "').style.display = 'none';";
				echo "document.getElementById('order_discounts_" . $vendor->id . "').style.display = 'none';\n";
			}
			?>
			document.getElementById('category_' + vendor_id).style.display = 'inline';
			document.getElementById('product_' + vendor_id).style.display = 'inline';
			document.getElementById('shipping_' + vendor_id).style.display = 'inline';
			switch (vendor_id)
			{
				<?php
				foreach ($vendors as $vendor)
				{
					echo "case '" . $vendor->id . "':\n";
					echo "document.getElementById('order_discounts_" . $vendor->id . "').style.display = '';\n";
					echo "break;\n";
				}
				?>
			}
		}
		function update_invoice_details(client_updated)
		{
			var vendor_id = document.getElementById('vendor_id').value;
			var product_list = document.getElementById('product_' + vendor_id);
			var shipping_selected = document.getElementById('shipping_' + vendor_id);
			if (shipping_selected.selectedIndex >= 0)
			{
				shipping_selected = shipping_selected.options[shipping_selected.selectedIndex].value;
			}
			else
			{
				shipping_selected = '';
			}
			if (product_list.selectedIndex >= 0)
			{
				document.getElementById('product_name').value = product_list.options[product_list.selectedIndex].text;
			}

			//Update net_price using $products in conjunction with selected payment frequency
			var pay_freq = document.getElementById('payment_frequency');
			var net_price = document.getElementById('net_price');
			var is_taxable = false;
            var custom_tax_rate = '<?php echo $row->custom_tax_rate == "" ? 'NULL' : format_number($row->custom_tax_rate, 'tax_rate'); ?>';
			var shipping_taxable = false;
			var shipping_amount = '0.00';
			var shipping_tax_rate = '0.00';
			var quantity = document.getElementById('quantity').value;
			if (quantity.length == 0)
			{
				quantity = 1;
			}

			if (pay_freq.options.length > 0)
			{
				switch (pay_freq.options[pay_freq.selectedIndex].value)
				{
					case 'AA':  //One-off
						switch (vendor_id)
						{
							<?php
							foreach ($vendors as $vendor)
							{
								$selected_product = self::get_selected_product($products, $selected_products, $vendor->id);
								if ($selected_product == null)
								{
									continue;
								}
								echo "case '" . $vendor->id . "':\n";
								echo "  net_price.value = '" . $selected_product->net_price_one_off . "';\n";
								echo $selected_product->is_taxable ? "  is_taxable = true;\n" : "  is_taxable = false;\n";
                                if ($selected_product->custom_tax_rate > 0)
                                {
                                    echo "  custom_tax_rate = '" . format_number($selected_product->custom_tax_rate, 'tax_rate') . "';\n";
                                }
								self::get_shipping_values($selected_product, $shipping, $vendor->id);
								echo "  break;\n";
							}
							?>
							default:
										net_price.value = '0.00';
										break;
						}
						break;
					case 'BB':  //Weekly
						switch (vendor_id)
						{
							<?php foreach ($vendors as $vendor)
							{
								echo "case '" . $vendor->id . "':\n";
									$selected_product = self::get_selected_product($products, $selected_products, $vendor->id);
									if ($selected_product == null)
									{
										continue;
									}
									echo "  net_price.value = '" . $selected_product->net_price_weekly . "';\n";
									echo $selected_product->is_taxable ? "  is_taxable = true;\n" : "  is_taxable = false;\n";
                                    if ($selected_product->custom_tax_rate > 0)
                                    {
                                        echo "  custom_tax_rate = '" . format_number($selected_product->custom_tax_rate, 'tax_rate') . "';\n";
                                    }
									self::get_shipping_values($selected_product, $shipping, $vendor->id);
									echo "  break;\n";
								} ?>
								default:
										net_price.value = '0.00';
										break;
						}
						break;
					case 'BX':  //Four-weekly
						switch (vendor_id)
						{
							<?php foreach ($vendors as $vendor)
							{
								echo "case '" . $vendor->id . "':\n";
									$selected_product = self::get_selected_product($products, $selected_products, $vendor->id);
									if ($selected_product == null)
									{
										continue;
									}
									echo "  net_price.value = '" . $selected_product->net_price_four_weekly . "';\n";
									echo $selected_product->is_taxable ? "  is_taxable = true;\n" : "  is_taxable = false;\n";
                                    if ($selected_product->custom_tax_rate > 0)
                                    {
                                        echo "  custom_tax_rate = '" . format_number($selected_product->custom_tax_rate, 'tax_rate') . "';\n";
                                    }
									self::get_shipping_values($selected_product, $shipping, $vendor->id);
									echo "  break;\n";
								} ?>
								default:
										net_price.value = '0.00';
										break;
						}
						break;
					case 'CC':  //Monthly
						switch (vendor_id)
						{
							<?php foreach ($vendors as $vendor)
							{
								echo "case '" . $vendor->id . "':\n";
									$selected_product = self::get_selected_product($products, $selected_products, $vendor->id);
									if ($selected_product == null)
									{
										continue;
									}
									echo "  net_price.value = '" . $selected_product->net_price_monthly . "';\n";
									echo $selected_product->is_taxable ? "  is_taxable = true;\n" : "  is_taxable = false;\n";
                                    if ($selected_product->custom_tax_rate > 0)
                                    {
                                        echo "  custom_tax_rate = '" . format_number($selected_product->custom_tax_rate, 'tax_rate') . "';\n";
                                    }
									self::get_shipping_values($selected_product, $shipping, $vendor->id);
									echo "  break;\n";
								} ?>
								default:
										net_price.value = '0.00';
										break;
						}
						break;
					case 'DD':  //Quarterly
						switch (vendor_id)
						{
							<?php foreach ($vendors as $vendor)
							{
								echo "case '" . $vendor->id . "':\n";
									$selected_product = self::get_selected_product($products, $selected_products, $vendor->id);
									if ($selected_product == null)
									{
										continue;
									}
									echo "  net_price.value = '" . $selected_product->net_price_quarterly . "';\n";
									echo $selected_product->is_taxable ? "  is_taxable = true;\n" : "  is_taxable = false;\n";
                                    if ($selected_product->custom_tax_rate > 0)
                                    {
                                        echo "  custom_tax_rate = '" . format_number($selected_product->custom_tax_rate, 'tax_rate') . "';\n";
                                    }
									self::get_shipping_values($selected_product, $shipping, $vendor->id);
									echo "  break;\n";
								} ?>
								default:
										net_price.value = '0.00';
										break;
						}
						break;
					case 'DX':  //Semi-annually
						switch (vendor_id)
						{
							<?php foreach ($vendors as $vendor)
							{
								echo "case '" . $vendor->id . "':\n";
									$selected_product = self::get_selected_product($products, $selected_products, $vendor->id);
									if ($selected_product == null)
									{
										continue;
									}
									echo "  net_price.value = '" . $selected_product->net_price_semi_annually . "';\n";
									echo $selected_product->is_taxable ? "  is_taxable = true;\n" : "  is_taxable = false;\n";
                                    if ($selected_product->custom_tax_rate > 0)
                                    {
                                        echo "  custom_tax_rate = '" . format_number($selected_product->custom_tax_rate, 'tax_rate') . "';\n";
                                    }
									self::get_shipping_values($selected_product, $shipping, $vendor->id);
									echo "  break;\n";
								} ?>
								default:
										net_price.value = '0.00';
										break;
						}
						break;
					case 'EE':  //Annually
						switch (vendor_id)
						{
							<?php foreach ($vendors as $vendor)
							{
								echo "case '" . $vendor->id . "':\n";
									$selected_product = self::get_selected_product($products, $selected_products, $vendor->id);
									if ($selected_product == null)
									{
										continue;
									}
									echo "  net_price.value = '" . $selected_product->net_price_annually . "';\n";
									echo $selected_product->is_taxable ? "  is_taxable = true;\n" : "  is_taxable = false;\n";
                                    if ($selected_product->custom_tax_rate > 0)
                                    {
                                        echo "  custom_tax_rate = '" . format_number($selected_product->custom_tax_rate, 'tax_rate') . "';\n";
                                    }
									self::get_shipping_values($selected_product, $shipping, $vendor->id);
									echo "  break;\n";
								} ?>
								default:
										net_price.value = '0.00';
										break;
						}
						break;
					case 'FF':  //Bi-Annually
						switch (vendor_id)
						{
							<?php foreach ($vendors as $vendor)
							{
								echo "case '" . $vendor->id . "':\n";
									$selected_product = self::get_selected_product($products, $selected_products, $vendor->id);
									if ($selected_product == null)
									{
										continue;
									}
									echo "  net_price.value = '" . $selected_product->net_price_biannually . "';\n";
									echo $selected_product->is_taxable ? "  is_taxable = true;\n" : "  is_taxable = false;\n";
                                    if ($selected_product->custom_tax_rate > 0)
                                    {
                                        echo "  custom_tax_rate = '" . format_number($selected_product->custom_tax_rate, 'tax_rate') . "';\n";
                                    }
									self::get_shipping_values($selected_product, $shipping, $vendor->id);
									echo "  break;\n";
								} ?>
								default:
										net_price.value = '0.00';
										break;
						}
						break;
					case 'GG':  //Five-Yearly
						switch (vendor_id)
						{
							<?php foreach ($vendors as $vendor)
							{
								echo "case '" . $vendor->id . "':\n";
									$selected_product = self::get_selected_product($products, $selected_products, $vendor->id);
									if ($selected_product == null)
									{
										continue;
									}
									echo "  net_price.value = '" . $selected_product->net_price_five_years . "';\n";
									echo $selected_product->is_taxable ? "  is_taxable = true;\n" : "  is_taxable = false;\n";
                                    if ($selected_product->custom_tax_rate > 0)
                                    {
                                        echo "  custom_tax_rate = '" . format_number($selected_product->custom_tax_rate, 'tax_rate') . "';\n";
                                    }
									self::get_shipping_values($selected_product, $shipping, $vendor->id);
									echo "  break;\n";
								} ?>
								default:
										net_price.value = '0.00';
										break;
						}
						break;
					case 'HH':  //Ten-Yearly
						switch (vendor_id)
						{
							<?php foreach ($vendors as $vendor)
							{
								echo "case '" . $vendor->id . "':\n";
									$selected_product = self::get_selected_product($products, $selected_products, $vendor->id);
									if ($selected_product == null)
									{
										continue;
									}
									echo "  net_price.value = '" . $selected_product->net_price_ten_years . "';\n";
									echo $selected_product->is_taxable ? "  is_taxable = true;\n" : "  is_taxable = false;\n";
                                    if ($selected_product->custom_tax_rate > 0)
                                    {
                                        echo "  custom_tax_rate = '" . format_number($selected_product->custom_tax_rate, 'tax_rate') . "';\n";
                                    }
									self::get_shipping_values($selected_product, $shipping, $vendor->id);
									echo "  break;\n";
								} ?>
								default:
										net_price.value = '0.00';
										break;
						}
						break;
					case 'XX':  //Not Applicable (Freebie)
						net_price.value = '0.00';
						is_taxable = false;
						break;
				}
			}

			net_price.value = format_currency(net_price.value * quantity, <?php echo $config->precision_currency; ?>);

			var tax_amount = '0.00';
			var tax_rate = 0;
			var client = document.getElementById('client_id');
			var currency_changed = false;

			document.getElementById('total_shipping_price').value = format_currency(shipping_amount, <?php echo $config->precision_currency; ?>);

			//Find the correct currency, exemption code, and tax rate depending on client's default currency/code/location/tax zone/status
			if (client.selectedIndex >= 0)
			{
				<?php
                if ($selected_client_row != null)
				{
					if (nbf_common::nb_strlen($selected_client_row->client_tax_rate) > 0)
					{
						if ($selected_client_row->online_exempt)
						{
							echo "  if (is_online() == 0) {\n";
						}
						if ($selected_client_row->exempt_with_ref_no)
						{
							echo "  if (document.getElementById('tax_exemption_code').value.length == 0) {\n";
						}
                        $default_tax_rate = $selected_client_row->client_tax_rate;
						echo "  tax_rate = " . $default_tax_rate . ";\n";
                        echo "  tax_amount = (net_price.value / 100) * " . $selected_client_row->client_tax_rate . ";\n";
						if ($selected_client_row->exempt_with_ref_no)
						{
							echo "}\n";
						}
						if ($selected_client_row->online_exempt)
						{
							echo "}\n";
						}
					}
					else
					{
                        $selected_product = self::get_selected_product($products, $selected_products, nbf_common::get_param($_POST, 'vendor_id'));
                        if (!$selected_product) {
                            $selected_product = new stdClass();
                            $selected_product->electronic_delivery = 0;
                        }
                        $tax_rate_found = false;
						foreach ($tax_rates[nbf_common::get_param($_POST, 'vendor_id')] as $tax_rate)
						{
							$tax_rate_found = false;
							if ($tax_rate->country_code == $selected_client_row->country && $selected_product->electronic_delivery == $tax_rate->electronic_delivery)
							{
								$tax_rate_found = true;
								if ($tax_rate->online_exempt)
								{
									echo "  if (is_online() == 0) {\n";
								}
								if ($tax_rate->exempt_with_ref_no)
								{
									echo "  if (document.getElementById('tax_exemption_code').value.length == 0) {\n";
								}
                                $default_tax_rate = $tax_rate->tax_rate;
								echo "  tax_rate = " . $default_tax_rate . ";\n";
								echo "  tax_amount = (net_price.value / 100) * " . $tax_rate->tax_rate . ";\n";
								if ($tax_rate->exempt_with_ref_no)
								{
									echo "}\n";
								}
								if ($tax_rate->online_exempt)
								{
									echo "}\n";
								}
								break;
							}
						}
						if (!$tax_rate_found && $selected_client_row->in_eu != null)
						{
							//Check for EU rate
							foreach ($tax_rates[nbf_common::get_param($_POST, 'vendor_id')] as $tax_rate)
							{
								if ($tax_rate->country_code == "EU" && $selected_product->electronic_delivery == $tax_rate->electronic_delivery)
								{
									$tax_rate_found = true;
									if ($tax_rate->online_exempt)
									{
										echo "  if (is_online() == 0) {\n";
									}
									if ($tax_rate->exempt_with_ref_no)
									{
										echo "  if (document.getElementById('tax_exemption_code').value.length == 0) {\n";
									}
                                    $default_tax_rate = $tax_rate->tax_rate;
									echo "  tax_rate = " . $default_tax_rate . ";\n";
									echo "  tax_amount = (net_price.value / 100) * " . $tax_rate->tax_rate . ";\n";
									if ($tax_rate->exempt_with_ref_no)
									{
										echo "}\n";
									}
									if ($tax_rate->online_exempt)
									{
										echo "}\n";
									}
									break;
								}
							}
						}
						//Check for WW rate
						if (!$tax_rate_found)
						{
							foreach ($tax_rates[nbf_common::get_param($_POST, 'vendor_id')] as $tax_rate)
							{
								if ($tax_rate->country_code == "WW" && $selected_product->electronic_delivery == $tax_rate->electronic_delivery)
								{
									if ($tax_rate->online_exempt)
									{
										echo "  if (is_online() == 0) {\n";
									}
									if ($tax_rate->exempt_with_ref_no)
									{
										echo "  if (document.getElementById('tax_exemption_code').value.length == 0) {\n";
									}
                                    $default_tax_rate = $tax_rate->tax_rate;
									echo "  tax_rate = " . $default_tax_rate . ";\n";
									echo "  tax_amount = (net_price.value / 100) * " . $tax_rate->tax_rate . ";\n";
									if ($tax_rate->exempt_with_ref_no)
									{
										echo "}\n";
									}
									if ($tax_rate->online_exempt)
									{
										echo "}\n";
									}
									break;
								}
							}
						}
					}
				}
				?>
			}

            document.getElementById('custom_tax_rate').value = '';
			if (is_taxable)
			{
                if (custom_tax_rate != 'NULL' && custom_tax_rate > 0 && tax_amount != 0)
                {
                    tax_amount = (net_price.value / 100) * custom_tax_rate;
                    document.getElementById('custom_tax_rate').value = custom_tax_rate;
                }
				tax_amount = format_currency(tax_amount, <?php echo $config->precision_currency; ?>);
				document.getElementById('total_tax_amount').value = tax_amount;
			}
			else
			{
				document.getElementById('total_tax_amount').value = '0.00';
			}
			var shipping_tax_amount = '0.00';
			if (shipping_taxable)
			{
				if (shipping_tax_rate == null || shipping_tax_rate == '0.00' || shipping_tax_rate == 0)
				{
					shipping_tax_rate = tax_rate;
				}
				shipping_tax_amount = (shipping_amount / 100) * shipping_tax_rate;
			}
			document.getElementById('total_shipping_tax').value = format_currency(shipping_tax_amount, <?php echo $config->precision_currency; ?>);

            //document.getElementById('custom_tax_rate').value = custom_tax_rate;

			recalculate_total();

			if (currency_changed)
			{
				nbill_submit_task('edit_currency_change');
			}
		}

		function is_online()
		{
			for (var i=0; i < document.adminForm.is_online.length; i++)
		  {
		  	if (document.adminForm.is_online[i].checked)
		    {
		    	return document.adminForm.is_online[i].value;
		    }
		  }
		}

        function format_currency(number, dec_places){
        //(c) Copyright 2008, Russell Walker, Netshine Software Limited. www.netshinesoftware.com
        if (typeof dec_places === 'undefined') {
            dec_places=2;
        }
        var new_number='';var i=0;var sign="";number=number.toString();number=number.replace(/^\s+|\s+$/g,'');if(number.charCodeAt(0)==45){sign='-';number=number.substr(1).replace(/^\s+|\s+$/g,'')}dec_places=dec_places*1;dec_point_pos=number.lastIndexOf(".");if(dec_point_pos==0){number="0"+number;dec_point_pos=1}if(dec_point_pos==-1||dec_point_pos==number.length-1){if(dec_places>0){new_number=number+".";for(i=0;i<dec_places;i++){new_number+="0"}if(new_number==0){sign=""}return sign+new_number}else{return sign+number}}var existing_places=(number.length-1)-dec_point_pos;if(existing_places==dec_places){return sign+number}if(existing_places<dec_places){new_number=number;for(i=existing_places;i<dec_places;i++){new_number+="0"}if(new_number==0){sign=""}return sign+new_number}var end_pos=(dec_point_pos*1)+dec_places;var round_up=false;if((number.charAt(end_pos+1)*1)>4){round_up=true}var digit_array=new Array();for(i=0;i<=end_pos;i++){digit_array[i]=number.charAt(i)}for(i=digit_array.length-1;i>=0;i--){if(digit_array[i]=="."){continue}if(round_up){digit_array[i]++;if(digit_array[i]<10){break}}else{break}}for(i=0;i<=end_pos;i++){if(digit_array[i]=="."||digit_array[i]<10||i==0){new_number+=digit_array[i]}else{new_number+="0"}}if(dec_places==0){new_number=new_number.replace(".","")}if(new_number==0){sign=""}return sign+new_number}

		function shipping_service_changed()
		{
			var vendor_id = document.getElementById('vendor_id').value;
			var shipping_list = document.getElementById('shipping_' + vendor_id);
			if (shipping_list.selectedIndex >= 0)
			{
				document.getElementById('shipping_service').value = shipping_list.options[shipping_list.selectedIndex].text;
			}
			update_invoice_details(false);
		}
		function recalculate_total()
		{
			var vendor_id = document.getElementById('vendor_id').value;
			var net_price = document.getElementById('net_price');
			var tax_amount = document.getElementById('total_tax_amount');
			var shipping_tax_amount = document.getElementById('total_shipping_tax');
			var shipping_amount = document.getElementById('total_shipping_price');

			var invoice_total = format_currency((net_price.value * 1) + (tax_amount.value * 1) + (shipping_amount.value * 1) + (shipping_tax_amount.value * 1), <?php echo $config->precision_currency; ?>); // *1 = force to number
			var currency = document.getElementById('currency');
			if (currency.selectedIndex >= 0)
			{
				currency = currency.options[currency.selectedIndex].value;

				var el_order_total = document.getElementById('order_total');
				if (el_order_total.childNodes.length > 0)
				{
					el_order_total.removeChild(el_order_total.firstChild);
				}
				el_order_total.appendChild(document.createTextNode(currency + ' ' + invoice_total));
				//document.getElementById('order_total').innerHTML = currency + ' ' + invoice_total;
			}
			else
			{
				var el_order_total = document.getElementById('order_total');
				if (el_order_total.childNodes.length > 0)
				{
					el_order_total.removeChild(el_order_total.firstChild);
				}
				el_order_total.appendChild(document.createTextNode(invoice_total));
				//document.getElementById('order_total').innerHTML = invoice_total;
			}
		}
		function change_frequency()
		{
			update_invoice_details(false);
		}
		function add_order_discount()
		{
			//Get current vendor
			var vendor_id = document.getElementById('vendor_id').value;

			//Check that the values are valid
			if (document.getElementById('discount_' + vendor_id + '_new_id').value == 0)
			{
				alert('<?php echo NBILL_PLEASE_SELECT_DISCOUNT; ?>');
				return;
			}

			//Check if this discount is already present (don't allow it twice)
			var discountTable = document.getElementById('discount_' + vendor_id + '_new_row').parentNode;
			for ( i=0; i < discountTable.childNodes.length; i++)
			{
				var elemId = discountTable.childNodes[i].id;
				if (elemId != null && elemId.length > 0 && elemId != 'discount_' + vendor_id + '_new_row')
				{
					if (elemId.indexOf('_') > 0)
					{
						var idParts = elemId.split('_');
						if (idParts.length > 2)
						{
							if (document.getElementById('discount_' + vendor_id + '_' + idParts[2] + '_row').style.display != 'none')
							{
								existingElem = document.getElementById('discount_' + vendor_id + '_' + idParts[2] + '_id');
								if (existingElem != null)
								{
									if (existingElem.value == document.getElementById('discount_' + vendor_id + '_new_id').options[document.getElementById('discount_' + vendor_id + '_new_id').selectedIndex].value)
									{
										alert('<?php echo NBILL_ORDER_DISCOUNT_DUPLICATION; ?>');
										return;
									}
								}
							}
						}
					}
				}
			}

			//Set default values if priority not set
			if (document.getElementById('discount_' + vendor_id + '_new_priority').value.length == 0)
			{
				document.getElementById('discount_' + vendor_id + '_new_priority').value = 1;
			}

			//Store the values from the 'new' line
			var new_id = document.getElementById('discount_' + vendor_id + '_new_id').selectedIndex;
			var new_discount = document.getElementById('discount_' + vendor_id + '_new_id').options[new_id].text;
			var new_discount_value = document.getElementById('discount_' + vendor_id + '_new_id').options[new_id].value;
			var new_priority = document.getElementById('discount_' + vendor_id + '_new_priority').value;

			//Reset 'new' line ready for next input
			document.getElementById('discount_' + vendor_id + '_new_id').selectedIndex = 0;
            document.getElementById('discount_' + vendor_id + '_new_priority').value = '';

			//Work out what number we are on
			var discountNo = (document.getElementById('discount_' + vendor_id + '_count').value * 1);
			document.getElementById('discount_' + vendor_id + '_count').value = (discountNo * 1) + 1;

			//Create a new row
			var newElement = document.createElement('tr');
			newElement.id = 'discount_' + vendor_id + '_' + discountNo + '_row';

			//First cell - discount id
			var firstCell = document.createElement('td');
			var discountId = document.createElement('input');
			discountId.setAttribute('type', 'hidden');
			discountId.setAttribute('value', new_discount_value);
			discountId.id = 'discount_' + vendor_id + '_' + discountNo + '_id';
			firstCell.appendChild(discountId);
            var discountText = document.createTextNode(new_discount);
            firstCell.appendChild(discountText);

            //Second cell - priority
            var secondCell = document.createElement('td');
            var priority = document.createElement('input'); //document.getElementById('discount_' + vendor_id + '_new_priority').cloneNode(true);
            priority.style['width'] = '80px';
            priority.setAttribute('type', 'text');
            priority.id = 'discount_' + vendor_id + '_' + discountNo + '_priority';
            priority.value = new_priority;
            secondCell.appendChild(priority);

            //Third cell - delete button
            var thirdCell = document.createElement('td');
            thirdCell.setAttribute('align', 'left');
            var deleteButton = document.createElement('input');
            deleteButton.setAttribute('type', 'button');
            deleteButton.setAttribute('name', 'discount_' + vendor_id + '_' + discountNo + '_delete');
            deleteButton.id = 'discount_' + vendor_id + '_' + discountNo + '_delete';
            deleteButton.setAttribute('value', '<?php echo NBILL_DELETE_DISCOUNT; ?>');

            addEvent( deleteButton, 'click', function(){ delete_discount(discountNo); } );

            //AttachEvent(deleteButton, 'click', 'delete_discount', false);
            //deleteButton.onClick = "delete_discount('" + discountNo + "');";
            thirdCell.appendChild(deleteButton);

            newElement.appendChild(firstCell);
            newElement.appendChild(secondCell);
            newElement.appendChild(thirdCell);

            //Work out where to put it
            var newRow = document.getElementById('discount_' + vendor_id + '_new_row');
            newRow.parentNode.insertBefore(newElement, newRow);
		}

		//This function only: modified version of code released under
		//CC-GNU LGPL (see http://cn.creativecommons.org/licenses/LGPL/2.1/index.html)
		function addEvent( obj, type, fn )
		{
			if ( obj.attachEvent )
			{
				obj['e'+type+fn] = fn;
				obj[type+fn] = function(){obj['e'+type+fn]( window.event );}
				obj.attachEvent( 'on'+type, obj[type+fn] );
			}
			else
			{
				obj.addEventListener( type, fn, false );
			}
		}

		function delete_discount(discountNo)
		{
			//Get current vendor
			var vendor_id = document.getElementById('vendor_id').value;

			delList = document.getElementById('discount_deleted_items_' + vendor_id);
			if (delList.value.length > 0)
			{
				delList.value += ",";
			}
			delList.value += discountNo;
			document.getElementById('discount_deleted_items_' + vendor_id).value = delList.value;
			document.getElementById('discount_' + vendor_id + '_' + discountNo + '_row').style.display = 'none';
		}
		</script>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "orders"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php echo $row->id ? NBILL_EDIT_ORDER . " '" . $row->order_no . "'" : NBILL_NEW_ORDER; ?>
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
        <input type="hidden" name="action" value="orders" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $order_id;?>" />
        <input type="hidden" name="disable_client_list" id="disable_client_list" value="<?php echo nbf_common::get_param($_REQUEST, 'disable_client_list'); ?>" />
        <input type="hidden" name="listed_client_id" id="listed_client_id" value="<?php echo nbf_common::get_param($_REQUEST, 'listed_client_id'); ?>" />
        <input type="hidden" name="no_record_limit" id="no_record_limit" value="<?php echo nbf_common::get_param($_REQUEST, 'no_record_limit'); ?>" />
		<?php
        nbf_html::add_filters(); ?>

        <?php
        $tab_settings = new nbf_tab_group();
        $tab_settings->start_tab_group("admin_settings");
        $tab_settings->add_tab_title("basic", NBILL_ADMIN_TAB_BASIC);
        $tab_settings->add_tab_title("advanced", NBILL_ADMIN_TAB_ADVANCED);
        ob_start();
        ?>

        <div class="rounded-table">
		    <table class="adminform" id="nbill-admin-table-orders">
		    <tr>
			    <th colspan="2"><?php echo NBILL_ORDER_DETAILS; ?></th>
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
							    echo nbf_html::select_list($vendor_name, "vendor_id", 'onchange="refresh_vendor();" id="vendor_id" class="inputbox"', $selected_vendor);
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
		    <tr id="nbill-admin-tr-order-no">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_ORDER_NO; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="order_no" id="order_no" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'order_no', null, true) : $row->order_no; ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_NO, "order_no_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-select-client">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SELECT_CLIENT; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    //Create a dropdown of clients
				    $client_list = array();
				    $selected_client = '';
				    foreach ($clients as $client_item)
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
				    if ($use_posted_values)
				    {
					    $selected_client = nbf_common::get_param($_POST, 'client_id');
				    }
				    else
				    {
					    if($row->id)
					    {
						    $selected_client = $row->client_id;
					    }
					    else
					    {
						    $selected_cat = 0;
					    }
				    }
				    echo nbf_html::select_list($client_list, "client_id", 'onchange="nbill_submit_task(\'change_client\')" class="inputbox" id="client_id"', $selected_client);
                    if (count($clients) == nbf_globals::$record_limit)
                    { ?>
                        <br /><span style="color:#ff0000;font-weight:bold"><?php echo sprintf(NBILL_ORDERS_RECORD_LIMIT_WARNING, nbf_globals::$record_limit, nbf_globals::$record_limit); ?></span><br />
                        <input type="button" class="btn button nbill-button" name="remove_record_limit" id="remove_record_limit" value="<?php echo NBILL_ORDER_SHOW_ALL; ?>" onclick="adminForm.no_record_limit.value='1';adminForm.submit();return false;" />
                    <?php }
				    ?>
                    <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=clients&task=edit&cid=<?php echo $selected_client; ?>" onclick="if(document.getElementById('client_id').value){window.location='<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=clients&task=edit&cid='+document.getElementById('client_id').value;}else{alert('<?php echo NBILL_CLIENT_REQUIRED; ?>');}return false;" title="<?php echo NBILL_ORDER_GOTO_CLIENT; ?>"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/clients.gif" alt="<?php echo NBILL_ORDER_GOTO_CLIENT;?>" /></a> <?php echo NBILL_INSTR_SELECT_CLIENT; ?>
			    </td>
		    </tr>
            <tr id="nbill-admin-tr-select-product">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SELECT_PRODUCT; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    //Create a dropdown of categories for each vendor - show/hide via javascript depending on vendor selected
					    foreach ($vendors as $vendor)
					    {
						    $cat_list = array();
						    foreach ($categories[$vendor->id] as $cat_item)
						    {
							    $cat_list[] = nbf_html::list_option($cat_item['id'], $cat_item['name']);
						    }
						    if ($use_posted_values)
						    {
							    $selected_cat = nbf_common::get_param($_POST, 'category_' . $vendor->id);
						    }
						    else
						    {
							    if($row->id)
							    {
								    $selected_cat = $selected_cats[$vendor->id];
							    }
							    else
							    {
								    $selected_cat = 0;
							    }
						    }
						    if ($vendor->id == $selected_vendor)
						    {
							    $visibility = "style=\"display:inline\" ";
						    }
						    else
						    {
							    $visibility = "style=\"display:none\" ";
						    }
						    echo nbf_html::select_list($cat_list, "category_" . $vendor->id, $visibility . 'onchange="nbill_submit_task(\'edit_cat_change\');" class="inputbox" id="category_' . $vendor->id . '"', @$selected_cats[$vendor->id]);
					    }
					    echo "<br />";
					    //Create a dropdown of products for each vendor and the selected category - show/hide via javascript depending on vendor selected
					    foreach ($vendors as $vendor)
					    {
						    $product_list = array();
                            $product_list[] = nbf_html::list_option(0, NBILL_NOT_APPLICABLE);
						    foreach ($products[$vendor->id] as $product_item)
						    {
                                $item_name = "";
							    if (nbf_common::nb_strlen($product_item->product_code) > 0)
							    {
								    $item_name = $product_item->product_code . " - ";
							    }
							    $item_name .= $product_item->name;
							    $product_list[] = nbf_html::list_option($product_item->id, $item_name);
						    }
						    if ($use_posted_values)
						    {
							    $selected_product = nbf_common::get_param($_POST,'product_' . $vendor->id);
						    }
						    else
						    {
							    if($row->id)
							    {
								    $selected_product = $row->product_id;
							    }
							    else
							    {
								    $selected_product = 0;
							    }
						    }
						    if ($vendor->id == $selected_vendor)
						    {
							    $visibility = "style=\"display:inline\" ";
						    }
						    else
						    {
							    $visibility = "style=\"display:none\" ";
						    }
					    echo nbf_html::select_list($product_list, "product_" . $vendor->id, $visibility . 'onchange="nbill_submit_task(\'product_updated\');" class="inputbox" id="product_' . $vendor->id . '"', $selected_product);
					    }
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_SELECT_PRODUCT, "product_help"); ?>
			    </td>
		    </tr>
            <!-- Custom Fields Placeholder -->
		    <tr id="nbill-admin-tr-shipping">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_ORDER_SHIPPING; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    //Create a dropdown of shipping services for each vendor - show/hide via javascript depending on vendor selected
					    foreach ($vendors as $vendor)
					    {
						    if ($vendor->id == $selected_vendor)
						    {
							    $visibility = "style=\"display:inline\" ";
						    }
						    else
						    {
							    $visibility = "style=\"display:none\" ";
						    }
						    $selected_product = self::get_selected_product($products, $selected_products, $vendor->id);
						    if ($selected_product == null)
						    {
							    //No product selected, so offer all shipping options
							    $shipping_list = array();
							    $shipping_list[] = nbf_html::list_option("-1", NBILL_NOT_APPLICABLE);
							    foreach ($shipping[$vendor->id] as $shipping_item)
							    {
								    $shipping_list[] = nbf_html::list_option($shipping_item->id, $shipping_item->description);
							    }
							    if ($use_posted_values)
							    {
								    $selected_shipping = nbf_common::get_param($_POST, 'shipping_' . $vendor->id);
							    }
							    else
							    {
								    if($row->id)
								    {
									    $selected_shipping = $row->shipping_id;
								    }
								    else
								    {
									    $selected_shipping = -1;
								    }
							    }
							    echo nbf_html::select_list($shipping_list, "shipping_" . $vendor->id, $visibility . 'onchange="shipping_service_changed();" class="inputbox" id="shipping_' . $vendor->id . '"', $selected_shipping);
						    }
						    else
						    {
							    $shipping_list = array();
							    $shipping_list[] = nbf_html::list_option("-1", NBILL_NOT_APPLICABLE);
							    if ($selected_product->requires_shipping)
							    {
								    foreach ($shipping[$vendor->id] as $shipping_item)
								    {
									    if (array_search($shipping_item->id, explode(",", $selected_product->shipping_services)) !== false)
									    {
										    $shipping_list[] = nbf_html::list_option($shipping_item->id, $shipping_item->description);
									    }
								    }
							    }
							    if ($use_posted_values)
							    {
								    $selected_shipping = nbf_common::get_param($_POST, 'shipping_' . $vendor->id);
							    }
							    else
							    {
								    if($row->id)
								    {
									    $selected_shipping = $row->shipping_id;
								    }
								    else
								    {
									    $selected_shipping = -1;
								    }
							    }
							    echo nbf_html::select_list($shipping_list, "shipping_" . $vendor->id, $visibility . 'onchange="shipping_service_changed();" class="inputbox" id="shipping_' . $vendor->id . '"', $selected_shipping);
						    }
					    }
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_SHIPPING, "shipping_help"); ?>
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
            <tr id="nbill-admin-tr-payment-frequency">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_ORDER_PAYMENT_FREQUENCY; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    $pay_freq_list = array();
				    foreach ($pay_frequencies as $pay_frequency)
				    {
					    $pay_freq_list[] = nbf_html::list_option($pay_frequency->code, $pay_frequency->description);
				    }
				    echo nbf_html::select_list($pay_freq_list, "payment_frequency", 'id="payment_frequency" onchange="change_frequency();" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST,'payment_frequency', null, true) : $row->payment_frequency);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_PAYMENT_FREQUENCY, "payment_frequency_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-auto-renew">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_AUTO_RENEW ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    if (isset($_POST['auto_renew']))
				    {
					    $auto_renew = $use_posted_values ? nbf_common::get_param($_POST,'auto_renew', null, true) : $row->auto_renew;
				    }
				    else
				    {
					    $auto_renew = $use_posted_values ? '' : $row->auto_renew;
				    }
				    echo nbf_html::yes_or_no_options("auto_renew", "", $auto_renew); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_AUTO_RENEW, "auto_renew_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-currency">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_ORDER_CURRENCY ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    $order_currency = array();
				    foreach ($currencies as $currency_code)
				    {
                        if (@$product_currencies[$selected_vendor] && count($product_currencies[$selected_vendor]) > 0)
                        {
					        foreach ($product_currencies[$selected_vendor] as $product_currency)
					        {
						        if ($product_currency->currency_code == $currency_code['code'])
						        {
							        $order_currency[] = nbf_html::list_option($currency_code['code'], $currency_code['description']);
							        break;
						        }
					        }
                        }
				    }
				    $selected_curr = "";
				    if ($use_posted_values)
				    {
					    foreach ($product_currencies[$selected_vendor] as $product_currency)
					    {
                            if (@$product_currencies[$selected_vendor] && count($product_currencies[$selected_vendor]) > 0)
                            {
						        if (isset($_POST['currency']) && $product_currency->currency_code == nbf_common::get_param($_POST, 'currency'))
						        {
							        $selected_curr = nbf_common::get_param($_POST, 'currency');
							        break;
						        }
                            }
					    }
				    }
				    if (nbf_common::nb_strlen($selected_curr) == 0)
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
				    if (count($order_currency) == 0)
                    {
                        foreach ($currencies as $currency_code)
                        {
                            $order_currency[] = nbf_html::list_option($currency_code['code'], $currency_code['description']);
                        }
                    }
				    echo nbf_html::select_list($order_currency, "currency", 'onchange="nbill_submit_task(\'edit_currency_change\');" id="currency" class="inputbox"', $use_posted_values ? nbf_common::get_param($_REQUEST, 'currency') : $selected_curr);
            	    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_CURRENCY, "currency_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-quantity">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_ORDER_QUANTITY ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    $quantity = "";
				    if ($use_posted_values)
				    {
					    $quantity = nbf_common::get_param($_POST, 'quantity');
				    }
				    else
				    {
					    if ($row->id)
					    {
						    $quantity = format_number($row->quantity, 'quantity');
					    }
				    }
				    if (nbf_common::nb_strlen($quantity) == 0)
				    {
					    $quantity = 1;
				    }
				    ?>
				    <input type="text" name="quantity" id="quantity" onchange="update_invoice_details(false);" value="<?php echo $quantity; ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_QUANTITY, "quantity_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-relating-to">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_RELATING_TO ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="relating_to" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'relating_to', null, true)) : str_replace("\"", "&quot;", $row->relating_to); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_RELATING_TO, "relating_to_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-start-date">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_START_DATE ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					$date_format = nbf_common::get_date_format();
					$cal_date_format = nbf_common::get_date_format(true);
					if ($use_posted_values)
					{
						$date_value = nbf_common::get_param($_POST, 'start_date');
					}
					else
					{
						$date_value = $row->id ? nbf_common::nb_date($date_format, $row->start_date) : nbf_common::nb_date($date_format, nbf_common::nb_time());
					}

					$date_parts = nbf_date::get_date_parts($date_value, $cal_date_format);
					if ($date_parts['y'] < 1971)
					{
						$date_value = "";
					}
					?>
					<span style="white-space:nowrap"><input type="text" name="start_date" maxlength="19" class="inputbox" value="<?php echo $date_value; ?>" />
					<input type="button" name="start_date_cal" class="btn button nbill-button" value="..." onclick="displayCalendar(document.adminForm.start_date,'<?php echo $cal_date_format; ?>',this);" /></span>
                    <?php nbf_html::show_static_help(NBILL_INSTR_START_DATE, "start_date_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-expiry-date">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_EXPIRY_DATE ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					$date_format = nbf_common::get_date_format();
					$cal_date_format = nbf_common::get_date_format(true);
					if ($use_posted_values)
					{
						$date_value = nbf_common::get_param($_POST, 'expiry_date');
					}
					else
					{
						$date_value = $row->id ? nbf_common::nb_date($date_format, $row->expiry_date) : "";
					}

					$date_parts = nbf_date::get_date_parts($date_value, $cal_date_format);
					if (@$date_parts['y'] < 1971)
					{
						$date_value = "";
					}
					?>
					<span style="white-space:nowrap"><input type="text" name="expiry_date" class="inputbox" maxlength="19" value="<?php echo $date_value; ?>" />
					<input type="button" name="expiry_date_cal" class="btn button nbill-button" value="..." onclick="displayCalendar(document.adminForm.expiry_date,'<?php echo $cal_date_format; ?>',this);" /></span>
                    <?php nbf_html::show_static_help(NBILL_INSTR_EXPIRY_DATE, "expiry_date_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-last-due-date">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_LAST_DUE_DATE ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					$date_format = nbf_common::get_date_format();
					$cal_date_format = nbf_common::get_date_format(true);
					if ($use_posted_values)
					{
						$date_value = nbf_common::get_param($_POST, 'last_due_date');
					}
					else
					{
						$date_value = $row->id ? nbf_common::nb_date($date_format, $row->last_due_date) : "";
					}

					$date_parts = nbf_date::get_date_parts($date_value, $cal_date_format);
					if (@$date_parts['y'] < 1971)
					{
						$date_value = "";
					}
					?>
					<span style="white-space:nowrap"><input type="text" name="last_due_date" class="inputbox" maxlength="19" value="<?php echo $date_value; ?>" />
					<input type="button" name="last_due_date_cal" class="btn button nbill-button" value="..." onclick="displayCalendar(document.adminForm.last_due_date,'<?php echo $cal_date_format; ?>',this);" /></span>
                    <?php nbf_html::show_static_help(NBILL_INSTR_LAST_DUE_DATE, "last_due_date_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-next-due-date">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_NEXT_DUE_DATE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					$date_format = nbf_common::get_date_format();
					$cal_date_format = nbf_common::get_date_format(true);
					if ($use_posted_values)
					{
						$date_value = nbf_common::get_param($_POST, 'next_due_date');
					}
					else
					{
						$date_value = $row->id ? nbf_common::nb_date($date_format, $row->next_due_date) : nbf_common::nb_date($date_format, nbf_common::nb_time());
					}

					$date_parts = nbf_date::get_date_parts($date_value, $cal_date_format);
					if (@$date_parts['y'] < 1971)
					{
						$date_value = "";
					}
					?>
					<span style="white-space:nowrap"><input type="text" name="next_due_date" class="inputbox" maxlength="19" value="<?php echo $date_value; ?>" />
					<input type="button" name="next_due_date_cal" class="btn button nbill-button" value="..." onclick="displayCalendar(document.adminForm.next_due_date,'<?php echo $cal_date_format; ?>',this);" /></span>
                    <?php nbf_html::show_static_help(NBILL_INSTR_NEXT_DUE_DATE, "next_due_date_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-order-status">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_ORDER_STATUS; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    foreach ($xref_status as $status)
					    {
						    $status_list[] = nbf_html::list_option($status->code, $status->description);
					    }
					    echo nbf_html::select_list($status_list, "order_status", 'class="inputbox" id="order_status"', $use_posted_values ? nbf_common::get_param($_POST, 'order_status', null, true) : $row->order_status);
				    ?>
				    <input type="hidden" name="old_status" value="<?php echo $row->order_status; ?>" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_STATUS, "order_status_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-cancellation-date">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CANCELLATION_DATE ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					$date_format = nbf_common::get_date_format();
					$cal_date_format = nbf_common::get_date_format(true);
					if ($use_posted_values)
					{
						$date_value = nbf_common::get_param($_POST, 'cancellation_date');
					}
					else
					{
						$date_value = $row->id ? nbf_common::nb_date($date_format, $row->cancellation_date) : "";
					}

					$date_parts = nbf_date::get_date_parts($date_value, $cal_date_format);
					if (@$date_parts['y'] < 1971)
					{
						$date_value = "";
					}
					?>
					<span style="white-space:nowrap"><input type="text" name="cancellation_date" class="inputbox" maxlength="19" value="<?php echo $date_value; ?>" />
					<input type="button" name="cancellation_date_cal" class="btn button nbill-button" value="..." onclick="displayCalendar(document.adminForm.cancellation_date,'<?php echo $cal_date_format; ?>',this);" /></span>
                    <?php nbf_html::show_static_help(NBILL_INSTR_CANCELLATION_DATE, "cancellation_date_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-cancellation-reason">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CANCELLATION_REASON ?>
			    </td>
			    <td class="nbill-setting-value">
				    <textarea name="cancellation_reason" rows="4" cols="20"><?php echo $use_posted_values ? nbf_common::get_param($_POST,'cancellation_reason', null, true) : $row->cancellation_reason; ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_INSTR_CANCELLATION_REASON, "cancellation_reason_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-order-tracking-id">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_ORDER_TRACKING_ID; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="parcel_tracking_id" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'parcel_tracking_id', null, true) : $row->parcel_tracking_id; ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_TRACKING_ID, "parcel_tracking_id_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-notes">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_NOTES; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <textarea name="notes" id="notes" cols="35" rows="10"><?php echo $use_posted_values ? nbf_common::get_param($_POST,'notes', null, true) : $row->notes; ?></textarea>
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
            <table class="adminform" id="nbill-admin-table-order-details">
            <tr>
                <th colspan="2"><?php echo NBILL_ORDER_DETAILS; ?></th>
            </tr>
            <tr id="nbill-admin-tr-is-online">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_IS_ONLINE ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    if (isset($_POST['is_online']))
                    {
                        $is_online = $use_posted_values ? nbf_common::get_param($_POST,'is_online', null, true) : $row->is_online;
                    }
                    else
                    {
                        $is_online = $use_posted_values ? '' : $row->is_online;
                    }
                    echo nbf_html::yes_or_no_options("is_online", "onchange=\"update_invoice_details(false);\" id=\"is_online\"", $is_online); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_IS_ONLINE, "is_online_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-tax-exemption-code">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_EXEMPTION_CODE ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    if ($use_posted_values)
                    {
                        $tax_exemption_code = str_replace("\"", "&quot;", nbf_common::get_param($_POST, 'tax_exemption_code'));
                    }
                    else
                    {
                        $tax_exemption_code = $row->id ? str_replace("\"", "&quot;", $row->tax_exemption_code) : str_replace("\"", "&quot;", @$selected_client_row->tax_exemption_code);
                    }
                    ?>
                    <input type="text" name="tax_exemption_code" id="tax_exemption_code" onchange="update_invoice_details(false);" value="<?php echo $tax_exemption_code; ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_EXEMPTION_CODE, "tax_exemption_code_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-unique-invoice">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_UNIQUE_INVOICE; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    if (isset($_POST['unique_invoice']))
                    {
                        $unique_invoice = $use_posted_values ? nbf_common::get_param($_POST,'unique_invoice', null, true) : $row->unique_invoice;
                    }
                    else
                    {
                        $unique_invoice = $use_posted_values ? '' : $row->unique_invoice;
                    }
                    echo nbf_html::yes_or_no_options("unique_invoice", "", $unique_invoice); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_UNIQUE_INVOICE, "unique_invoice_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-show-paylink">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_ORDER_SHOW_PAYLINK; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    $paylink_options = array();
                    $paylink_options[] = nbf_html::list_option(0, NBILL_ORDER_PAYLINK_USE_GLOBAL);
                    $paylink_options[] = nbf_html::list_option(1, NBILL_ORDER_PAYLINK_SHOW);
                    $paylink_options[] = nbf_html::list_option(2, NBILL_ORDER_PAYLINK_HIDE);
                    echo nbf_html::select_list($paylink_options, "show_invoice_paylink", 'id="show_invoice_paylink" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST,'show_invoice_paylink', null, true) : $row->show_invoice_paylink);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_SHOW_PAYLINK, "show_invoice_paylink_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-voucher-code">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_ORDER_VOUCHER_CODE; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="discount_voucher" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'discount_voucher', null, true)) : str_replace("\"", "&quot;", $row->discount_voucher); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_VOUCHER_CODE, "discount_voucher_help"); ?>
                </td>
            </tr>

            <?php foreach ($vendors as $vendor)
            {
            ?>
                <tr id="order_discounts_<?php echo $vendor->id; if ($vendor->id != $selected_vendor) {echo "\" style=\"display:none;";} ?>">
                    <td colspan="3">
                        <input type="hidden" name="discount_<?php echo $vendor->id; ?>_count" id="discount_<?php echo $vendor->id; ?>_count" value="<?php echo count(@$order_discounts[$vendor->id]); ?>" />
                        <input type="hidden" name="discount_deleted_items_<?php echo $vendor->id; ?>" id="discount_deleted_items_<?php echo $vendor->id; ?>" value="" />
                        <input type="hidden" name="discount_added_items_<?php echo $vendor->id; ?>" id="discount_added_items_<?php echo $vendor->id; ?>" value = "" />
                        <table cellpadding="4" cellspacing="0" border="0" class="adminform" id="order_discount_<?php echo $vendor->id; ?>_table">
                            <tr><th colspan="4"><?php echo NBILL_ORDER_DISCOUNT_TITLE; ?></th></tr>
                            <tr><td colspan="4"><?php echo NBILL_ORDER_DISCOUNT_INTRO; ?></td></tr>
                            <tr>
                                <td><strong><?php echo NBILL_ORDER_DISCOUNT;?></strong></td>
                                <td><strong><?php echo NBILL_ORDER_DISCOUNT_ORDERING; ?></strong></td>
                                <td width="100%">&nbsp;</td>
                            </tr>
                            <?php
                                //Display controls for each existing product discount
                                $discount_no = 0;
                                foreach ($order_discounts[$vendor->id] as $order_discount)
                                { ?>
                                <tr id="discount_<?php echo $vendor->id; ?>_<?php echo $discount_no; ?>_row">
                                    <td>
                                        <input type="hidden" name="discount_<?php echo $vendor->id; ?>_<?php echo $discount_no; ?>_id" id="discount_<?php echo $vendor->id; ?>_<?php echo $discount_no; ?>_id" value="<?php echo $order_discount->discount_id; ?>" />
                                        <?php
                                        foreach ($discounts[$vendor->id] as $discount)
                                        {
                                            if ($discount->discount_id == $order_discount->discount_id)
                                            {
                                                echo $discount->discount_name;
                                                break;
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td><input type="text" name="discount_<?php echo $vendor->id; ?>_<?php echo $discount_no; ?>_priority" id="discount_<?php echo $vendor->id; ?>_<?php echo $discount_no; ?>_priority" value="<?php echo $order_discount->ordering; ?>" style="width: 80px;" /></td>
                                    <td><input type="button" class="btn button nbill-button" name="discount_<?php echo $vendor->id; ?>_<?php echo $discount_no; ?>_delete" id="discount_<?php echo $vendor->id; ?>_<?php echo $discount_no; ?>_delete" value="<?php echo NBILL_DELETE_DISCOUNT; ?>" onclick="delete_discount('<?php echo $discount_no; ?>');" />
                                </tr>
                                <?php
                                    $discount_no++;
                                }
                                //Display extra row for new discount
                            ?>
                            <tr style="background-color: #ccffcc;" id="discount_<?php echo $vendor->id; ?>_new_row">
                                <td>
                                <?php
                                    $discount_list = array();
                                    $discount_list[] = nbf_html::list_option(0, NBILL_NOT_APPLICABLE);
                                    foreach ($discounts[$vendor->id] as $discount)
                                    {
                                        $discount_list[] = nbf_html::list_option($discount->discount_id, $discount->discount_name);
                                    }
                                    echo nbf_html::select_list($discount_list, "discount_" . $vendor->id . "_new_id", 'class="inputbox squashable" id="discount_' . $vendor->id . '_new_id"', 'value', 'text');
                                ?>
                                </td>
                                <td><input type="text" name="discount_<?php echo $vendor->id; ?>_new_priority" id="discount_<?php echo $vendor->id; ?>_new_priority" value="" class="inputbox small-numeric" /></td>
                                <td><input type="button" class="btn button nbill-button" name="add_<?php echo $vendor->id; ?>_new_discount" id="add_<?php echo $vendor->id; ?>_new_discount" value="<?php echo NBILL_ADD_ORDER_DISCOUNT; ?>" onclick="add_order_discount();" /></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            <?php
            }

            nbf_html::show_admin_setting_yes_no($row, 'auto_create_invoice', 'ORDERS_');
            nbf_html::show_admin_setting_yes_no($row, 'auto_create_income', 'ORDERS_');
            nbf_html::show_admin_setting_textbox($row, 'gateway_txn_id', 'ORDERS_');
            ?>

            <tr id="nbill-admin-tr-form-field-values">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_ORDER_FORM_FIELD_VALUES; ?>
                </td>
                <td class="nbill-setting-value">
                    <textarea name="form_field_values" id="form_field_values" cols="30" rows="6"><?php
                    echo trim($row->form_field_values);
                    ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_FORM_FIELD_VALUES, "form_field_values_help"); ?>
                </td>
            </tr>
            <?php if (nbf_common::nb_strlen($row->uploaded_files) > 0)
            {
                $file_uploads = explode("\n", str_replace("\r", "", $row->uploaded_files));
                $file_index = 0;
                foreach ($file_uploads as $file_upload)
                {
                    if (file_exists($file_upload))
                    {
                        ?>
                        <tr class="nbill-admin-tr-file-upload">
                            <td class="nbill-setting-caption">
                                <?php echo NBILL_ORDER_FORM_FILE_UPLOADS; ?>
                            </td>
                            <td class="nbill-setting-value">
                                <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=orders&task=view_uploaded_file&cid=<?php echo $row->id; ?>&index=<?php echo $file_index; ?>"><?php echo basename($file_upload); ?></a>
                                <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_FORM_FILE_UPLOADS, "file_upload_" . $file_index . "_help"); ?>
                            </td>
                        </tr>
                        <?php
                    }
                    $file_index++;
                }
            }
            ?>
            </table>
        </div>

        <?php
        $tab_settings->add_tab_content("advanced", ob_get_clean());
        $tab_settings->end_tab_group();
        ?>

		<br />

        <div class="rounded-table">
            <table cellpadding="3" cellspacing="0" border="0" class="adminform" id="nbill-admin-tr-orders-invoice-details">
                <tr>
                    <th><?php echo NBILL_INVOICE_DETAILS; ?></th>
                </tr>
                <tr>
                    <td><?php echo NBILL_ORDER_INVOICE_INTRO . ' ' . NBILL_ORDER_VOUCHER_DISCOUNT_NOT_SHOWN; ?></td>
                <tr>
                    <td>
                        <table class="floating-tablette">
                            <tr>
                                <th><?php echo NBILL_ORDER_PRODUCT_NAME; ?></th>
                                <td><input type="text" name="product_name" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'product_name', null, true)) : str_replace("\"", "&quot;", $row->product_name); ?>" id="product_name" class="inputbox" /></td>
                            </tr>
                            <tr>
                                <th><?php echo NBILL_SHIPPING_SERVICE; ?></th>
                                <td><input type="text" name="shipping_service" id="shipping_service" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'shipping_service', null, true) : $row->shipping_service; ?>" class="inputbox" /></td>
                            </tr>
                        </table>
                        <table class="floating-tablette">
                            <tr>
                                <th><?php echo NBILL_ORDER_NET_PRICE; ?></th>
                                <td><input type="text" name="net_price" id="net_price" onchange="recalculate_total();" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'net_price', null, true) : format_number($row->net_price, 'currency'); ?>" class="inputbox" /></td>
                            </tr>
                            <tr>
                                <th><?php echo NBILL_ORDER_TOTAL_SHIPPING; ?></th>
                                <td><input type="text" name="total_shipping_price" id="total_shipping_price" onchange="recalculate_total();" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'total_shipping_price', null, true) : format_number($row->total_shipping_price, 'currency'); ?>" class="inputbox" /></td>
                            </tr>
                        </table>
                        <table class="floating-tablette">
                            <tr>
                                <th><?php echo NBILL_ORDER_TAX_AMOUNT; ?></th>
                                <td><input type="text" name="total_tax_amount" id="total_tax_amount" onchange="recalculate_total();" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'total_tax_amount', null, true) : format_number($row->total_tax_amount, 'currency'); ?>" class="inputbox" /></td>
                            </tr>
                            <tr>
                                <th><?php echo NBILL_ORDER_SHIPPING_TAX; ?></th>
                                <td><input type="text" name="total_shipping_tax" id="total_shipping_tax" onchange="recalculate_total();" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'total_shipping_tax', null, true) : format_number($row->total_shipping_tax, 'currency'); ?>" class="inputbox" /></td>
                            </tr>
                        </table>

                        <table class="floating-tablette">
                            <tr>
                                <td>
                                    <?php
                                    $custom_ledger = $use_posted_values ? nbf_common::get_param($_POST, 'custom_ledger_code') : $row->custom_ledger_code;
                                    $custom_tax_rate = $use_posted_values ? nbf_common::get_param($_POST, 'custom_tax_rate') : ($row->custom_tax_rate == 'NULL' ? '' : $row->custom_tax_rate);
                                    $show_custom = nbf_common::nb_strlen($custom_ledger) > 0 || nbf_common::nb_strlen($custom_tax_rate) > 0;
                                    ?>
                                    <input type="hidden" name="orig_custom_tax_rate" value="<?php echo @$row->custom_tax_rate; ?>" />
                                    <div id="custom_settings_hidden"<?php echo $show_custom ? ' style="display:none;"' : ''; ?>>
                                    <a href="#" onclick="document.getElementById('custom_settings_hidden').style.display='none';document.getElementById('custom_settings_visible').style.display='';return false;"><?php echo NBILL_ORDER_CUSTOM_SETTINGS_SHOW; ?></a> <?php echo NBILL_ORDER_CUSTOM_SETTINGS_WARN; ?>
                                    </div>

                                    <div id="custom_settings_visible"<?php echo !$show_custom ? ' style="display:none;"' : ''; ?>>
                                    <div><a href="#" onclick="document.getElementById('custom_settings_visible').style.display='none';document.getElementById('custom_settings_hidden').style.display='';return false;"><?php echo NBILL_ORDER_CUSTOM_SETTINGS_HIDE; ?></a> <?php echo NBILL_ORDER_CUSTOM_SETTINGS_WARN; ?></div>
                                    <table class="floating-tablette">
                                        <tr>
                                            <th><?php echo NBILL_ORDER_CUSTOM_LEDGER_CODE;?></th>
                                            <td><input type="text" name="custom_ledger_code" id="custom_ledger_code" value="<?php echo $custom_ledger; ?>" class="inputbox numeric" /></td>
                                        </tr>
                                    </table>
                                    <table class="floating-tablette">
                                        <tr>
                                            <th><?php echo NBILL_ORDER_CUSTOM_TAX_RATE;?></th>
                                            <td><input type="text" name="custom_tax_rate" id="custom_tax_rate" value="<?php echo nbf_common::nb_strlen($custom_tax_rate) > 0 ? format_number($custom_tax_rate, 'tax_rate') : ''; ?>" class="inputbox numeric" />%</td>
                                        </tr>
                                    </table>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    //Work out total for initial display (will be updated by javscript as selections are made)
                                    $order_total = $use_posted_values ? nbf_common::get_param($_POST,'net_price', null, true) : format_number($row->net_price, 'currency');
                                    $order_total = float_add($order_total, $use_posted_values ? nbf_common::get_param($_POST,'total_tax_amount', null, true) : format_number($row->total_tax_amount, 'currency'));
                                    $order_total = float_add($order_total, $use_posted_values ? nbf_common::get_param($_POST,'total_shipping_price', null, true) : format_number($row->total_shipping_price, 'currency'));
                                    $order_total = float_add($order_total, $use_posted_values ? nbf_common::get_param($_POST,'total_shipping_tax', null, true) : format_number($row->total_shipping_tax, 'currency'));
                                    $order_total = format_number($order_total, 'currency', false, null, null, $row->currency);
                                    ?>
                                    <strong><?php echo NBILL_ORDER_TOTAL; ?></strong> <strong><span id="order_total"><?php echo $use_posted_values ? nbf_common::get_param($_POST,'currency', null, true) : $row->currency; echo " " . $order_total; ?></span></strong>
                                </td>
                            </tr>
                        </table>
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
                        <input type="button" class="btn button nbill-button" value="<?php echo NBILL_DETACH; ?>" onclick="if(confirm('<?php echo NBILL_DETACH_SURE; ?>')){document.adminForm.attachment_id.value='<?php echo $attachment->id; ?>';document.adminForm.task.value='detach_file_edit';document.adminForm.submit();}" />
                    </td>
                    <td>
                        <input type="button" class="btn button nbill-button" value="<?php echo NBILL_DELETE; ?>" onclick="if(confirm('<?php echo sprintf(NBILL_DELETE_FILE_SURE, $attachment->file_name); ?>')){document.adminForm.attachment_id.value='<?php echo $attachment->id; ?>';document.adminForm.task.value='delete_file_edit';document.adminForm.submit();}" />
                    </td>
                    </tr>
                    <?php
                }
                ?>
                <tr><td colspan="3">
                <a href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&use_stylesheet=1&show_toolbar=1&attach_to_type=OR&attach_to_id=<?php echo $row->id; ?>','','scrollbars=1,width=790,height=500');return false;"><img style="vertical-align:middle" border="0" alt="" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/supporting_docs.gif" />&nbsp;<?php echo NBILL_NEW_ATTACHMENT; ?></a>
                </td></tr>
                </table>
            </div>
        <?php } ?>

		<br />

		<?php
			if ($order_id && $row->payment_frequency != "AA" && $row->payment_frequency != "XX")
			{
				//Show payment schedule link
				?>
                <div class="rounded-table">
				    <table cellpadding="3" cellspacing="0" border="0" width="100%" class="adminform" id="nbill-admin-table-order-paylink">
					    <tr>
						    <th align="left"><?php echo NBILL_ORDER_PAYLINK; ?></th>
					    </tr>
					    <tr>
						    <td>
						    <?php
							    echo NBILL_ORDER_PAYLINK_PROMPT . "<br /><br />
							    <strong>" . nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . "&action=orders&task=renew&order_id=$order_id" . nbf_cms::$interop->site_page_suffix . "</strong>";
						    ?>
						    </td>
					    </tr>
				    </table>
                </div>
			<?php
			}
		?>

		</form>
		<?php
		if ($recalculate || !$row->id)
		{
			?>
			<script type="text/javascript">
				refresh_vendor();
				update_invoice_details(false);
			</script>
			<?php
		}
		else if (!$row->id)
		{
			?>refresh_vendor();<?php
		}
	}

    public static function offer_update_electronic($total_count, $autos, $manuals, $old_electronic)
    {
        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
        ?>
        <script type="text/javascript">
        function nbill_submit_task(task_name)
        {
            var form = document.adminForm;
            document.adminForm.task.value=task_name;
            document.adminForm.submit();
        }
        </script>

        <div style="float:right">
        <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
        <tr valign="middle" align="center">
            <!-- Save button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('update_electronic');return false;">
                    <img src="<?php echo $image_path ; ?>save.png" alt="<?php echo NBILL_TB_SAVE; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_SAVE;?></a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Cancel button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('cancel');return false;">
                    <img src="<?php echo $image_path ; ?>cancel.png" alt="<?php echo NBILL_TB_CANCEL; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_CANCEL;?></a>
            </td>
        </tr>
        </table>
        </div>

        <table class="adminheading" style="width:auto;">
        <tr>
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "orders"); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_ORDERS_ELECTRONIC_CHANGE_TITLE; ?>
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
        <input type="hidden" name="action" value="orders" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="1" />
        <input type="hidden" name="hidemainmenu" value="0">

        <div class="nbill-message"><?php echo sprintf(NBILL_ORDERS_ELECTRONIC_CHANGE_INTRO, intval($total_count)); ?></div>

        <?php
        if ($autos && count($autos) > 0)
        { ?>
            <p align="left"><?php echo sprintf(NBILL_ORDERS_ELECTRONIC_CHANGE_INTRO_AUTO, count($autos)); ?></p>
            <table cellpadding="3" cellspacing="0" border="0">
                <tr>
                    <td><input type="radio" name="rate_change_auto_renew_action" id="auto_update_net" value="update_net" checked="checked" /></td>
                    <td><label for="auto_update_net" style="font-weight:bold;"><?php echo NBILL_ORDERS_ELECTRONIC_RECALC_TAX_AND_NET . " " . NBILL_ORDERS_CUSTOM_RECOMMENDED; ?></label></td>
                </tr>
                <tr>
                    <td><input type="radio" name="rate_change_auto_renew_action" id="auto_update_gross" value="update_gross" /></td>
                    <td><label for="auto_update_gross" style="font-weight:bold;"><?php echo NBILL_ORDERS_ELECTRONIC_RECALC_TAX_AND_GROSS; ?></label></td>
                </tr>
                <tr>
                    <td><input type="radio" name="rate_change_auto_renew_action" id="auto_no_action_and_cancel" value="no_action_and_cancel" /></td>
                    <td><label for="auto_no_action_and_cancel" style="font-weight:bold;"><?php echo NBILL_ORDERS_ELECTRONIC_DO_NOTHING; ?></label></td>
                </tr>
            </table>
            <div id="auto_rows_hidden">
                <a href="#" onclick="document.getElementById('auto_rows_hidden').style.display='none';document.getElementById('auto_rows_visible').style.display='';return false;"><?php echo NBILL_ORDER_CUSTOM_SHOW_ROWS; ?></a>
                </div>
                <div id="auto_rows_visible" style="display:none;">
                <a href="#" onclick="document.getElementById('auto_rows_visible').style.display='none';document.getElementById('auto_rows_hidden').style.display='';return false;"><?php echo NBILL_ORDER_CUSTOM_HIDE_ROWS; ?></a>
                <?php
                self::show_custom_tax_rate_order_list($autos);
                ?>
            </div>
            <?php
            if ($manuals && count($manuals) > 0)
            {
                ?>
                <br />
                <hr />
                <br />
                <?php
            }
        }
        if ($manuals && count($manuals) > 0)
        { ?>
            <p align="left"><?php echo sprintf(NBILL_ORDERS_ELECTRONIC_CHANGE_INTRO_MANUAL, count($manuals)); ?></p>
            <table cellpadding="3" cellspacing="0" border="0">
                <tr>
                    <td><input type="radio" name="rate_change_manual_renew_action" id="manual_update_net" value="update_net" /></td>
                    <td><label for="manual_update_net" style="font-weight:bold;"><?php echo NBILL_ORDERS_ELECTRONIC_RECALC_TAX_AND_NET; ?></label></td>
                </tr>
                <tr>
                    <td><input type="radio" name="rate_change_manual_renew_action" id="manual_update_gross" value="update_gross" checked="checked" /></td>
                    <td><label for="manual_update_gross" style="font-weight:bold;"><?php echo NBILL_ORDERS_ELECTRONIC_RECALC_TAX_AND_GROSS . " " . NBILL_ORDERS_CUSTOM_RECOMMENDED; ?></label></td>
                </tr>
                <tr>
                    <td><input type="radio" name="rate_change_manual_renew_action" id="manual_no_action_and_cancel" value="no_action_and_cancel" /></td>
                    <td><label for="manual_no_action_and_cancel" style="font-weight:bold;"><?php echo NBILL_ORDERS_ELECTRONIC_DO_NOTHING; ?></label></td>
                </tr>
            </table>
            <div id="manual_rows_hidden">
                <a href="#" onclick="document.getElementById('manual_rows_hidden').style.display='none';document.getElementById('manual_rows_visible').style.display='';return false;"><?php echo NBILL_ORDER_CUSTOM_SHOW_ROWS; ?></a>
            </div>
            <div id="manual_rows_visible" style="display:none;">
                <a href="#" onclick="document.getElementById('manual_rows_visible').style.display='none';document.getElementById('manual_rows_hidden').style.display='';return false;"><?php echo NBILL_ORDER_CUSTOM_HIDE_ROWS; ?></a>
                <?php
                self::show_custom_tax_rate_order_list($manuals);
                ?>
            </div><?php
        }
    }

    public static function offer_update_custom_tax_rates($total_count, $autos, $manuals, $old_rate, $new_rate, $date_format)
    {
        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
        ?>
        <script type="text/javascript">
        function nbill_submit_task(task_name)
        {
            var form = document.adminForm;
            document.adminForm.task.value=task_name;
            document.adminForm.submit();
        }
        </script>

        <div style="float:right">
        <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
        <tr valign="middle" align="center">
            <!-- Save button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('update_custom_tax');return false;">
                    <img src="<?php echo $image_path ; ?>save.png" alt="<?php echo NBILL_TB_SAVE; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_SAVE;?></a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Cancel button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('cancel');return false;">
                    <img src="<?php echo $image_path ; ?>cancel.png" alt="<?php echo NBILL_TB_CANCEL; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_CANCEL;?></a>
            </td>
        </tr>
        </table>
        </div>

        <table class="adminheading" style="width:auto;">
        <tr>
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "orders"); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_ORDERS_CUSTOM_TAX_CHANGE_TITLE; ?>
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
        <input type="hidden" name="action" value="orders" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="1" />
        <input type="hidden" name="hidemainmenu" value="0">
        <input type="hidden" name="old_rate" value="<?php echo $old_rate; ?>" />
        <input type="hidden" name="new_rate" value="<?php echo $new_rate; ?>" />

        <div class="nbill-message"><?php echo sprintf(NBILL_ORDERS_CUSTOM_TAX_CHANGE_INTRO, intval($total_count)); ?></div>

        <?php
        if ($autos && count($autos) > 0)
        { ?>
            <p align="left"><?php echo sprintf(NBILL_ORDERS_CUSTOM_TAX_CHANGE_INTRO_AUTO, count($autos), $old_rate . "%"); ?></p>
            <table cellpadding="3" cellspacing="0" border="0">
                <tr>
                    <td><input type="radio" name="rate_change_auto_renew_action" id="auto_update_net" value="update_net" checked="checked" /></td>
                    <td><label for="auto_update_net" style="font-weight:bold;"><?php echo sprintf(NBILL_ORDERS_CUSTOM_RECALC_TAX_AND_NET, ($old_rate === null ? NBILL_USE_GLOBAL_RATE : $old_rate . "%"), ($new_rate === null ? NBILL_USE_GLOBAL_RATE : $new_rate . "%")) . " " . NBILL_ORDERS_CUSTOM_RECOMMENDED; ?></label></td>
                </tr>
                <tr>
                    <td><input type="radio" name="rate_change_auto_renew_action" id="auto_update_gross" value="update_gross" /></td>
                    <td><label for="auto_update_gross" style="font-weight:bold;"><?php echo sprintf(NBILL_ORDERS_CUSTOM_RECALC_TAX_AND_GROSS, ($old_rate === null ? NBILL_USE_GLOBAL_RATE : $old_rate . "%"), ($new_rate === null ? NBILL_USE_GLOBAL_RATE : $new_rate . "%")); ?></label></td>
                </tr>
                <tr>
                    <td><input type="radio" name="rate_change_auto_renew_action" id="auto_no_action_and_cancel" value="no_action_and_cancel" /></td>
                    <td><label for="auto_no_action_and_cancel" style="font-weight:bold;"><?php echo NBILL_ORDERS_CUSTOM_DO_NOTHING; ?></label></td>
                </tr>
                <tr>
                    <td><input type="radio" name="rate_change_auto_renew_action" id="auto_update_no_recalc" value="update_no_recalc" /></td>
                    <td><label for="auto_update_no_recalc" style="font-weight:bold;"><?php echo sprintf(NBILL_ORDERS_CUSTOM_NO_RECALC, ($old_rate === null ? NBILL_USE_GLOBAL_RATE : $old_rate . "%"), ($new_rate === null ? NBILL_USE_GLOBAL_RATE : $new_rate . "%")); ?></label></td>
                </tr>
            </table>
            <div id="auto_rows_hidden">
                <a href="#" onclick="document.getElementById('auto_rows_hidden').style.display='none';document.getElementById('auto_rows_visible').style.display='';return false;"><?php echo NBILL_ORDER_CUSTOM_SHOW_ROWS; ?></a>
                </div>
                <div id="auto_rows_visible" style="display:none;">
                <a href="#" onclick="document.getElementById('auto_rows_visible').style.display='none';document.getElementById('auto_rows_hidden').style.display='';return false;"><?php echo NBILL_ORDER_CUSTOM_HIDE_ROWS; ?></a>
                <?php
                self::show_custom_tax_rate_order_list($autos);
                ?>
            </div>
            <?php
            if ($manuals && count($manuals) > 0)
            {
                ?>
                <br />
                <hr />
                <br />
                <?php
            }
        }
        if ($manuals && count($manuals) > 0)
        { ?>
            <p align="left"><?php echo sprintf(NBILL_ORDERS_CUSTOM_TAX_CHANGE_INTRO_MANUAL, count($manuals), $old_rate . "%"); ?></p>
            <table cellpadding="3" cellspacing="0" border="0">
                <tr>
                    <td><input type="radio" name="rate_change_manual_renew_action" id="manual_update_net" value="update_net" /></td>
                    <td><label for="manual_update_net" style="font-weight:bold;"><?php echo sprintf(NBILL_ORDERS_CUSTOM_RECALC_TAX_AND_NET, ($old_rate === null ? NBILL_USE_GLOBAL_RATE : $old_rate . "%"), ($new_rate === null ? NBILL_USE_GLOBAL_RATE : $new_rate . "%")); ?></label></td>
                </tr>
                <tr>
                    <td><input type="radio" name="rate_change_manual_renew_action" id="manual_update_gross" value="update_gross" checked="checked" /></td>
                    <td><label for="manual_update_gross" style="font-weight:bold;"><?php echo sprintf(NBILL_ORDERS_CUSTOM_RECALC_TAX_AND_GROSS, ($old_rate === null ? NBILL_USE_GLOBAL_RATE : $old_rate . "%"), ($new_rate === null ? NBILL_USE_GLOBAL_RATE : $new_rate . "%")) . " " . NBILL_ORDERS_CUSTOM_RECOMMENDED; ?></label></td>
                </tr>
                <tr>
                    <td><input type="radio" name="rate_change_manual_renew_action" id="manual_no_action_and_cancel" value="no_action_and_cancel" /></td>
                    <td><label for="manual_no_action_and_cancel" style="font-weight:bold;"><?php echo NBILL_ORDERS_CUSTOM_DO_NOTHING; ?></label></td>
                </tr>
                <tr>
                    <td><input type="radio" name="rate_change_manual_renew_action" id="manual_update_no_recalc" value="update_no_recalc" /></td>
                    <td><label for="manual_update_no_recalc" style="font-weight:bold;"><?php echo sprintf(NBILL_ORDERS_CUSTOM_NO_RECALC, ($old_rate === null ? NBILL_USE_GLOBAL_RATE : $old_rate . "%"), ($new_rate === null ? NBILL_USE_GLOBAL_RATE : $new_rate . "%")); ?></label></td>
                </tr>
            </table>
            <div id="manual_rows_hidden">
                <a href="#" onclick="document.getElementById('manual_rows_hidden').style.display='none';document.getElementById('manual_rows_visible').style.display='';return false;"><?php echo NBILL_ORDER_CUSTOM_SHOW_ROWS; ?></a>
            </div>
            <div id="manual_rows_visible" style="display:none;">
                <a href="#" onclick="document.getElementById('manual_rows_visible').style.display='none';document.getElementById('manual_rows_hidden').style.display='';return false;"><?php echo NBILL_ORDER_CUSTOM_HIDE_ROWS; ?></a>
                <?php
                self::show_custom_tax_rate_order_list($manuals);
                ?>
            </div><?php
        }
    }

    private static function show_custom_tax_rate_order_list($rows)
    {
        ?>
        <div class="rounded-table">
        <table class="adminlist">
        <tr>
            <th class="selector">
            #
            </th>
            <th class="selector">
                <input type="checkbox" name="check_all" value="" onclick="for(var i=0; i<<?php echo count($rows); ?>;i++) {document.getElementById('cb' + i).checked=this.checked;} document.adminForm.box_checked.value=this.checked;" checked="checked" />
            </th>
            <th class="title">
                <?php echo NBILL_ORDER_NO; ?>
            </th>
            <th class="title">
                <?php echo NBILL_ORDER_PRODUCT_NAME; ?>
            </th>
            <th class="title">
                <?php echo NBILL_ORDER_START_DATE; ?>
            </th>
            <th class="title">
                <?php echo NBILL_CLIENT_NAME; ?>
            </th>
            <th class="title responsive-cell priority">
                <?php echo NBILL_ORDER_RELATING_TO; ?>
            </th>
            <th class="title responsive-cell extra-wide-only">
                <?php echo NBILL_ORDER_STATUS; ?>
            </th>
            <th class="title responsive-cell wide-only" style="text-align:right;white-space:nowrap;">
                <?php echo NBILL_ORDER_ORDER_VALUE; nbf_html::show_overlib(NBILL_ORDER_ORDER_VALUE_HELP); ?>
            </th>
        </tr>
        <?php
        for ($i=0, $n=count( $rows ); $i < $n; $i++)
        {
            $row = &$rows[$i];
            $link = nbf_cms::$interop->admin_page_prefix . "&action=orders&task=edit&cid=$row->id&search_date_from=" . nbf_common::get_param($_REQUEST,'search_date_from') . "&search_date_to=" . nbf_common::get_param($_REQUEST,'search_date_to') . "&vendor_filter=" . nbf_common::get_param($_REQUEST,'vendor_filter') . "&relating_to_search=" . nbf_common::get_param($_REQUEST,'relating_to_search') . "&client_search=" . nbf_common::get_param($_REQUEST,'client_search') . "&product_search=" . nbf_common::get_param($_REQUEST,'product_search') . "&nbill_no_search=" . nbf_common::get_param($_REQUEST,'nbill_no_search');
            echo "<tr>";
            echo "<td class=\"selector\">";
            echo $i + 1;
            $checked = nbf_html::id_checkbox($i, $row->id, true);
            echo "</td><td class=\"selector\">$checked</td>";
            echo "<td class=\"list-value\"><a target=\"_blank\" href=\"$link\" title=\"" . NBILL_EDIT_ORDER . "\">" . $row->order_no . "</td>";
            echo "<td class=\"list-value\">";
            if ($row->product_id > 0)
            {
                $product_link = nbf_cms::$interop->admin_page_prefix . "&action=products&task=edit&cid=" . $row->product_id . "&return=" . base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=orders&search_date_from=" . nbf_common::get_param($_REQUEST,'search_date_from') . "&search_date_to=" . nbf_common::get_param($_REQUEST,'search_date_to') . "&vendor_filter=" . nbf_common::get_param($_REQUEST,'vendor_filter') . "&relating_to_search=" . nbf_common::get_param($_REQUEST,'relating_to_search') . "&client_search=" . nbf_common::get_param($_REQUEST,'client_search') . "&product_search=" . nbf_common::get_param($_REQUEST,'product_search') . "&nbill_no_search=" . nbf_common::get_param($_REQUEST,'nbill_no_search'));
                echo "<a href=\"$product_link\" target=\"_blank\">";
            }
            if (nbf_common::nb_strlen($row->product_code) > 0 && substr($row->product_name, 0, nbf_common::nb_strlen($row->product_code . " - ")) != $row->product_code . " - ")
            {
                echo $row->product_code . ' - ';
            }
            echo $row->product_name;
            if ($row->product_id > 0)
            {
                echo "</a>";
            }
            echo "</td>";
            echo "<td class=\"list-value\" style=\"white-space:nowrap;\">" . nbf_common::nb_date(nbf_common::get_date_format(), $row->start_date) . "</td>";
            $client_link = nbf_cms::$interop->admin_page_prefix . "&action=clients&task=edit&cid=" . $row->client_id . "&return=" . base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=orders&search_date_from=" . nbf_common::get_param($_REQUEST,'search_date_from') . "&search_date_to=" . nbf_common::get_param($_REQUEST,'search_date_to') . "&vendor_filter=" . nbf_common::get_param($_REQUEST,'vendor_filter') . "&relating_to_search=" . nbf_common::get_param($_REQUEST,'relating_to_search') . "&client_search=" . nbf_common::get_param($_REQUEST,'client_search') . "&product_search=" . nbf_common::get_param($_REQUEST,'product_search') . "&nbill_no_search=" . nbf_common::get_param($_REQUEST,'nbill_no_search'));

            echo "<td class=\"list-value\"><a href=\"$client_link\" target=\"_blank\">" . $row->company_name;
            if (nbf_common::nb_strlen($row->contact_name) > 0)
            {
                if (nbf_common::nb_strlen($row->company_name) > 0)
                {
                    echo " (";
                }
                echo $row->contact_name;
                if (nbf_common::nb_strlen($row->company_name) > 0)
                {
                    echo ")";
                }
            }
            echo "</a></td>";
            echo "<td class=\"list-value responsive-cell priority\">" . $row->relating_to . "</td>";
            echo "<td class=\"list-value responsive-cell extra-wide-only\">" . @constant($row->order_status_desc) . "</td>";
            echo "<td class=\"list-value responsive-cell wide-only\" style=\"text-align:right\">" . format_number($row->net_price, 'currency', true, false, null, $row->currency) . "</td>";
            echo "</tr>";
        }
        echo "</table></div>";
    }

	public static function get_selected_product($products, $selected_products, $vendor_id)
	{
		$selected_product = null;
		foreach ($products[$vendor_id] as $product)
		{
			if ($product->id == $selected_products[$vendor_id])
			{
				return $product;
			}
		}
		return null;
	}

	public static function get_shipping_values($selected_product, $shipping, $vendor_id)
	{
		if ($selected_product->requires_shipping)
		{
			//Find shipping rate, and multiply by no. of units for this product
			?>
			shipping_taxable = false;
			switch (shipping_selected)
			{
            <?php
				foreach ($shipping[$vendor_id] as $shipping_item)
				{
					echo "case '" . $shipping_item->id . "':\n";
					if ($shipping_item->is_fixed_per_invoice)
					{
						echo "  shipping_amount = " . format_number($shipping_item->net_price_per_unit, 'currency') . ";\n";
					}
					else
					{
						if ($selected_product->shipping_units < 0)
						{
							echo "  shipping_amount = " . format_number($shipping_item->net_price_per_unit, 'currency') . " * " . abs($selected_product->shipping_units) . ";\n";
						}
						else
						{
							echo "  shipping_amount = " . format_number($shipping_item->net_price_per_unit, 'currency') . " * " . $selected_product->shipping_units . " * quantity;\n";
						}
					}
					//If tax rate different, make a note of it
					if ($shipping_item->is_taxable)
					{
						echo "  shipping_taxable = true;\n";
						echo "  shipping_tax_rate = " . $shipping_item->tax_rate_if_different . ";\n";
					}
					echo "  break;\n";
				}?>
			}
			<?php
		}
	}
}