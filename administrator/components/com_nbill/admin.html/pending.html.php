<?php
/**
* HTML output for pending orders
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillPending
{
	public static function showPending($rows, $pagination, $vendors, $date_format)
	{
        $vendor_col = false;
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "pending"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_PENDING_TITLE; ?>
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
        <input type="hidden" name="action" value="pending" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<p align="left"><?php echo NBILL_PENDING_INTRO; ?></p>

		<?php
			//Display filter dropdown if multi-company
			if (count($vendors) > 1)
			{
				echo "<p align=\"left\">" . NBILL_VENDOR_NAME . "&nbsp;";
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
				echo "</p>";
			}
		?>

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
				    <?php echo NBILL_PENDING_ORDER_ID; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_PENDING_ORDER_FORM; ?>
			    </th>
                <th class="title">
                    <?php echo NBILL_CLIENT_NAME; ?>
                </th>
			    <th class="title">
				    <?php echo NBILL_PENDING_ORDER_DATE; ?>
			    </th>
			    <th class="title responsive-cell priority">
				    <?php echo NBILL_PENDING_ORDER_VALUE; ?>
			    </th>
			    <?php
				    //Only show vendor name if more than one listed
				    if (count($vendors) > 1 && $selected_filter == -999)
				    {?>
					    <th class="title responsive-cell priority">
						    <?php echo NBILL_VENDOR_NAME; ?>
					    </th>
				    <?php }
			    ?>
		    </tr>
		    <?php
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];
				    $link = nbf_cms::$interop->admin_page_prefix . "&action=pending&task=show&cid=$row->id";
				    echo "<tr>";
				    echo "<td class=\"selector\" style=\"text-align:center\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\" style=\"text-align:center\">$checked</td>";
				    echo "<td class=\"list-value\"><a href=\"$link\" title=\"" . NBILL_SHOW_PENDING_ORDER . "\">" . $row->id . "</a></td>";
				    echo "<td class=\"list-value\">" . $row->order_form . "</td>";
                    if ($row->client_id > 0)
                    {
                        echo "<td class=\"list-value\"><a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=clients&task=edit&cid=" . $row->client_id . "&return=" . base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=pending") . "\">" . $row->client_name . "</a></td>";
                    }
                    else
                    {
                        echo "<td class=\"list-value\">" . $row->client_name . "</td>";
                    }
                    echo "<td class=\"list-value\">" . nbf_common::nb_date($date_format, $row->timestamp) . "</td>";

				    echo "<td class=\"list-value responsive-cell priority\">" . nbf_common::convertValueToCurrencyObject($row->total_gross, $row->currency)->format() . "</td>";
				    //Only show vendor name if more than one listed
				    $vendor_col = false;
				    if (count($vendors) > 1 && $selected_filter == -999)
				    {
					    foreach ($vendors as $vendor)
					    {
						    if ($vendor->id == $row->vendor_id)
						    {
							    echo "<td class=\"list-value responsive-cell priority\">" . $vendor->vendor_name . "</td>";
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

	public static function showPendingOrder($row, $date_format, $nbill_posted_values, $order_details)
	{
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "pending"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php echo NBILL_PENDING_ORDER . " $row->id"; ?>
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
        <input type="hidden" name="action" value="pending" />
        <input type="hidden" name="task" value="show" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="cid" value="<?php echo $row->id;?>" />
		<?php nbf_html::add_filters(); ?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
		    <tr>
			    <th colspan="2"><?php echo NBILL_PENDING_ORDER_DETAILS; ?></th>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption"><?php echo NBILL_VENDOR_NAME; ?></td>
                <td class="nbill-setting-value"><?php echo $row->vendor_name; ?></td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption"><?php echo NBILL_PENDING_ORDER_DATE; ?></td>
                <td class="nbill-setting-value"><?php echo nbf_common::nb_date($date_format, $row->timestamp); ?></td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption"><?php echo NBILL_PENDING_ORDER_FORM; ?></td>
                <td class="nbill-setting-value"><?php echo $row->form_name; ?></td>
		    </tr>
		    <?php if ($row->existing_client)
		    {?>
			    <tr>
				    <td class="nbill-setting-caption"><?php echo NBILL_CLIENT_NAME; ?></td>
				    <td class="nbill-setting-value">
					    <?php
					    $client_name = "";
					    if (nbf_common::nb_strlen($row->company_name) > 0)
					    {
						    $client_name = $row->company_name;
					    }
					    if (nbf_common::nb_strlen($row->contact_name) > 0)
					    {
						    if (nbf_common::nb_strlen ($client_name) > 0)
						    {
							    $client_name .= " (" . $row->contact_name . ")";
						    }
						    else
						    {
							    $client_name = $row->contact_name;
						    }
					    }
					    echo $client_name;
					    ?>
				    </td>
			    </tr>
			    <?php if ($row->user_id > 0)
			    {?>
				    <tr>
					    <td class="nbill-setting-caption"><?php echo NBILL_USERNAME; ?></td>
                        <td class="nbill-setting-value"><?php echo $row->username; ?></td>
				    </tr>
			    <?php }
		    }?>
		    <tr>
			    <td class="nbill-setting-caption"><?php echo NBILL_TAX_EXEMPTION_CODE; ?></td>
                <td class="nbill-setting-value"><?php echo $row->tax_exemption_code; ?></td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption"><?php echo NBILL_RELATING_TO; ?></td>
                <td class="nbill-setting-value"><?php echo $row->relating_to; ?></td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption"><?php echo NBILL_PAY_FREQUENCY; ?></td>
                <td class="nbill-setting-value"><?php echo $row->payment_frequency; ?></td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption"><?php echo NBILL_CURRENCY; ?></td>
                <td class="nbill-setting-value"><?php echo $row->currency; ?></td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption"><?php echo NBILL_SHIPPING_SERVICE; ?></td>
                <td class="nbill-setting-value"><?php echo $row->shipping_service; ?></td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption"><?php echo NBILL_PENDING_ORDER_VALUE; ?></td>
                <td class="nbill-setting-value"><?php echo nbf_common::convertValueToCurrencyObject($row->total_gross, $row->currency); ?></td>
		    </tr>
		    <tr>
                <td class="nbill-setting-caption"><?php echo NBILL_ORDER_DETAILS; ?></td>
                <td class="nbill-setting-value">
                    <?php
                        if ($order_details)
                        {
                            foreach ($order_details as $key=>$value)
                            {
                                if (is_array($value))
                                {
                                    if (array_key_exists("product_name", $value))
                                    {
                                        echo $value["product_name"] . "<br />";
                                    }
                                }
                                else
                                {
                                    echo "$key = $value<br />";
                                }
                            }
                        }
                    ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption"><?php echo NBILL_OTHER_DATA; ?></td>
                <td class="nbill-setting-value">
                    <?php
                        if ($nbill_posted_values)
                        {
                            foreach ($nbill_posted_values as $key=>$value)
                            {
                                if (substr($key, 0, 4) != 'ctl_') //Avoid duplication
                                {
                                    if (is_array($value))
                                    {
                                        echo "$key = ";
                                        foreach ($value as $value_entry)
                                        {
                                            echo $value_entry . "; ";
                                        }
                                        echo "<br />";
                                    }
                                    else
                                    {
                                        echo "$key = $value<br />";
                                    }
                                }
                            }
                        }
                    ?>
                </td>
            </tr>
		    </table>
        </div>
		<p align="left"><?php echo NBILL_PENDING_RESUME_LINK; ?></p>
		<p align="left"><strong><?php echo nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix; ?>&action=pending&task=pay&id=<?php echo $row->id . nbf_cms::$interop->site_page_suffix; ?></strong></p>
		</form>
	<?php
	}
}