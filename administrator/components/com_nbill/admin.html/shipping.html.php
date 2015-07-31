<?php
/**
* HTML output for shipping feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillShipping
{
	public static function showShipping($rows, $pagination, $vendors)
	{
        $vendor_col = false;
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "shipping"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_SHIPPING_TITLE; ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		}
		?>
		<p align="left"><?php echo NBILL_SHIPPING_INTRO; ?></p>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="shipping" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
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
				    <?php echo NBILL_SHIPPING_SERVICE; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_SHIPPING_COUNTRY; ?>
			    </th>
			    <?php
				    //Only show vendor name if more than one listed
				    if (count($vendors) > 1 && $selected_filter == -999)
				    {?>
					    <th class="title">
						    <?php echo NBILL_VENDOR_NAME; ?>
					    </th>
				    <?php }
			    ?>
			    <th class="title">
				    <?php echo NBILL_SHIPPING_ID; ?>
			    </th>
		    </tr>
		    <?php
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];
				    $link = nbf_cms::$interop->admin_page_prefix . "&action=shipping&task=edit&cid=$row->id";
				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\">$checked</td>";
				    echo "<td class=\"list-value\"><a href=\"$link\" title=\"" . NBILL_EDIT_SHIPPING_RATE . "\">" . $row->service . "</a></td>";
				    echo "<td class=\"list-value\">" . $row->country . "</td>";
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
				    echo "<td class=\"list-value\">" . $row->id . "</td>";
				    echo "</tr>";
			    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="<?php echo $vendor_col ? "6" : "5"; ?>" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		</form>
		<?php
	}

	public static function editShipping($shipping_id, $row, $country_codes, $vendors, $currencies, $prices, $ledger)
	{
		?>
		<script language="javascript" type="text/javascript">
		<?php nbf_html::add_js_validation_numeric(); ?>
		function refresh_vendor()
		{
			//Show the appropriate dropdowns depending on selected vendor
			var vendor_id = document.getElementById('vendor_id').value;
			<?php
			foreach ($vendors as $vendor)
			{
				echo "document.getElementById('price_" . $vendor->id . "').style.display = 'none';";
				echo "document.getElementById('ledger_" . $vendor->id . "').style.display = 'none';";
			}
			?>
			document.getElementById('price_' + vendor_id).style.display = 'inline';
			document.getElementById('ledger_' + vendor_id).style.display = 'inline';
		}
		function nbill_submit_task(task_name)
        {
			var form = document.adminForm;
			if (task_name == 'cancel')
            {
				document.adminForm.task.value=task_name;
                document.adminForm.submit();
				return;
			}

			//Do field validation
			if (form.service.value == "")
			{
				alert('<?php echo NBILL_SERVICE_NAME_REQUIRED; ?>');
			}
			else if (form.country.value == "")
			{
				alert('<?php echo NBILL_COUNTRY_REQUIRED; ?>');
			}
			<?php
			foreach ($vendors as $vendor)
			{
				foreach ($currencies as $currency)
				{
					if (nbf_common::nb_strlen(trim($currency['code'])) > 0)
					{
						$suffix = $currency['code'] . "_" . $vendor->id;
						?>
						else if (!IsNumeric(form.net_price_per_unit_<?php echo $suffix; ?>.value, true))
						{
							alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_NET_PRICE_PER_UNIT); ?>');
						}
						<?php
					}
				}
			}
			?>
			else if (!IsNumeric(form.tax_rate_if_different.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_SHIPPING_TAX_RATE); ?>');
			}
			else
			{
				document.adminForm.task.value=task_name;
                document.adminForm.submit();
			}
		}
		</script>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "shipping"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php echo $row->id ? NBILL_EDIT_SHIPPING_RATE . " '$row->service'" : NBILL_NEW_SHIPPING_RATE; ?>
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
        <input type="hidden" name="action" value="shipping" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $shipping_id;?>" />
		<?php nbf_html::add_filters(); ?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-shipping">
		    <tr>
			    <th colspan="2"><?php echo NBILL_SHIPPING_DETAILS; ?></th>
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
							    echo nbf_html::select_list($vendor_name, "vendor_id", 'id="vendor_id" onchange="refresh_vendor();" class="inputbox"', $selected_vendor);
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
		    <tr id="nbill-admin-tr-shipping-service">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SHIPPING_SERVICE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="service" id="service" value="<?php echo str_replace("\"", "&quot;", $row->service); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_SHIPPING_SERVICE, "service_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-shipping-code">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SHIPPING_CODE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="code" maxlength="2" value="<?php echo $row->code; ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_SHIPPING_CODE, "code_help"); ?>
			    </td>
		    </tr>
            <!-- Custom Fields Placeholder -->
		    <tr id="nbill-admin-tr-shipping-country">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SHIPPING_COUNTRY; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $country = array();
					    foreach ($country_codes as $country_code)
					    {
						    $country[] = nbf_html::list_option($country_code['code'], nbf_common::nb_ucwords(nbf_common::nb_strtolower($country_code['description'])));
					    }
					    echo nbf_html::select_list($country, "country", 'class="inputbox" id="country"', $row->country);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_SHIPPING_COUNTRY, "country_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-shipping-prices">
			    <td colspan="2">
				    <?php
					    echo "<div style=\"margin-bottom:5px\">" . NBILL_SHIPPING_PRICE_INTRO . "</div>";

					    //New tab for each currency
					    foreach ($vendors as $vendor)
					    {
						    echo "<table id=\"price_" . $vendor->id . "\"><tr><td>";
						    $price_list = $prices[$vendor->id];
						    $nbf_tab_currency = new nbf_tab_group();
                            $nbf_tab_currency->start_tab_group("currency_" . $vendor->id);
                            foreach ($currencies as $currency)
                            {
                                if (nbf_common::nb_strlen(trim($currency['code'])) > 0)
                                {
                                    $suffix = $currency['code'] . "_" . $vendor->id;
                                    $nbf_tab_currency->add_tab_title($suffix, $currency['code']);
                                }
                            }

						    foreach ($currencies as $currency)
						    {
							    if (nbf_common::nb_strlen(trim($currency['code'])) > 0)
							    {
								    $shipping_price = "0.00";
								    foreach ($price_list as $price_list_record)
								    {
									    if ($price_list_record->currency_code == $currency['code'])
									    {
										    $shipping_price = format_number($price_list_record->net_price_per_unit, 'currency');
										    break;
									    }
								    }
								    $suffix = $currency['code'] . "_" . $vendor->id;
								    ob_start();
					                ?>
					 			    <table cellpadding="3" cellspacing="0" border="0" id="nbill-admin-table-shipping-prices-<?php echo $currency['code']; ?>">
									    <tr>
										    <td class="nbill-setting-caption">
											    <?php echo NBILL_NET_PRICE_PER_UNIT; ?>
										    </td>
										    <td class="nbill-setting-value">
											    <input type="text" name="net_price_per_unit_<?php echo $suffix ?>" value="<?php echo $shipping_price; ?>" class="inputbox" />
										    </td>
									    </tr>
								    </table>
					                <?php
								    $nbf_tab_currency->add_tab_content($suffix, ob_get_clean());
							    }
						    }
						    $nbf_tab_currency->end_tab_group();
						    echo "</td></tr></table>";
					    }?>
                        <?php nbf_html::show_static_help(NBILL_INSTR_NET_PRICE_PER_UNIT, "net_price_per_unit_help"); ?>
			    </td>
		    </tr>

		    <tr id="nbill-admin-tr-shipping-ledger">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_NOMINAL_LEDGER_CODE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    //Create a dropdown of ledger codes for each vendor - show/hide via javascript depending on vendor selected
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
							    $selected_ledger = $row->nominal_ledger_code;
						    }
						    else
						    {
							    $selected_ledger = '';
						    }
						    echo nbf_html::select_list($ledger_list, "ledger_" . $vendor->id, 'class="inputbox" id="ledger_' . $vendor->id . '"', $selected_ledger);
					    }
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_NOMINAL_LEDGER_CODE, "ledger_help"); ?>
			    </td>
		    </tr>

		    <tr id="nbill-admin-tr-fixed-price">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SHIPPING_FIXED; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php echo nbf_html::yes_or_no_options("is_fixed_per_invoice", "", $row->is_fixed_per_invoice); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_SHIPPING_FIXED, "is_fixed_per_invoice_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-is-taxable">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SHIPPING_IS_TAXABLE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php echo nbf_html::yes_or_no_options("is_taxable", "", $row->is_taxable); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_SHIPPING_IS_TAXABLE, "is_taxable_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-tax-rate">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SHIPPING_TAX_RATE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="tax_rate_if_different" value="<?php echo format_number($row->tax_rate_if_different, 'tax_rate'); ?>" class="inputbox" />&nbsp;%
                    <?php nbf_html::show_static_help(NBILL_INSTR_SHIPPING_TAX_RATE, "tax_rate_if_different_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-tracking-url">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SHIPPING_TRACKING_URL; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="parcel_tracking_url" value="<?php echo $row->parcel_tracking_url; ?>" class="inputbox" style="width:200px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_SHIPPING_TRACKING_URL, "parcel_tracking_url_help"); ?>
			    </td>
		    </tr>
		    </table>
        </div>

		</form>
		<script type="text/javascript">
			refresh_vendor();
		</script>
		<?php
	}
}