<?php
/**
* HTML output for discounts feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillDiscounts
{
	public static function showDiscounts($rows, $pagination, $vendors, $date_format)
	{
        $vendor_col = false;
        $action = nbf_common::nb_strtoupper(nbf_common::get_param($_REQUEST, 'action'));
        $record_type = $action == "FEES" ? "FEE" : "DISCOUNT";
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, nbf_common::get_param($_REQUEST, 'action')); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . constant("NBILL_" . $action . "_TITLE"); ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		}
		?>
		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="<?php echo nbf_common::nb_strtolower($action); ?>" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

		<p align="left">
			<?php echo constant("NBILL_" . $action . "_INTRO"); ?>
		</p>

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
				echo nbf_html::select_list($vendor_name, "vendor_filter", 'id="vendor_filter" class="inputbox" onchange="document.adminForm.submit();"', $selected_filter) . " ";
			}
			else
			{
				echo "<input type=\"hidden\" name=\"vendor_filter\" id=\"vendor_filter\" value=\"" . $vendors[0]->id . "\" />";
				$_POST['vendor_filter'] = $vendors[0]->id;
			}

			//Display filter for discount name
			if (count($vendors) < 2)
			{
				echo "<p align=\"left\">";
			}
			$discount_search = nbf_common::get_param($_POST,'discount_search', '', true);
			echo constant("NBILL_" . $record_type . "_NAME") . " <input type=\"text\" name=\"discount_search\" value=\"" . $discount_search . "\" />";
			echo "&nbsp;&nbsp;<input type=\"submit\" class=\"button btn\" name=\"dosearch\" value=\"" . NBILL_GO . "\" />";
			echo "</p>";
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
				    <?php echo constant("NBILL_" . $record_type . "_NAME"); ?>
			    </th>
			    <th class="title responsive-cell high-priority">
				    <?php echo NBILL_DISCOUNT_END_DATE; ?>
			    </th>
			    <th class="title<?php if ($record_type != 'FEE') {echo ' responsive-cell priority';} ?>">
				    <?php echo NBILL_DISCOUNT_PERCENTAGE; ?>
			    </th>
			    <th class="selector">
				    <?php echo $record_type == "FEE" ? NBILL_FEE_PUBLISHED : NBILL_DISCOUNT_AVAILABLE; ?>
			    </th>
                <?php if ($record_type != "FEE")
                {
                    ?>
                    <th class="selector">
                        <?php echo NBILL_DISCOUNT_AVAILABLE_DOCS; ?>
                    </th>
                    <?php
                }

			    //Only show vendor name if more than one listed
			    if (count($vendors) > 1 && $selected_filter == -999)
			    {?>
				    <th class="title">
					    <?php echo NBILL_VENDOR_NAME; ?>
				    </th>
			    <?php }
			    ?>
		    </tr>
		    <?php
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];

				    $img = $row->available ? 'tick.png' : 'cross.png';
				    $task = $row->available ? 'unpublish' : 'publish';
				    $alt = $row->available ? constant("NBILL_" . $record_type . "_AVAILABLE_YES") : constant("NBILL_" . $record_type . "_AVAILABLE_NO");
                    $img_doc = $row->available_for_documents ? 'tick.png' : 'cross.png';
                    $task_doc = $row->available_for_documents ? 'unpublish_doc' : 'publish_doc';
                    $alt_doc = $row->available_for_documents ? NBILL_DISCOUNT_AVAILABLE_DOCS_YES : NBILL_DISCOUNT_AVAILABLE_DOCS_NO;

				    $link = nbf_cms::$interop->admin_page_prefix . "&action=" . nbf_common::nb_strtolower($action) . "&task=edit&cid=$row->id&discount_search=$discount_search";

				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\">$checked</td>";
				    echo "<td class=\"list-value\"><a href=\"$link\" title=\"" . constant("NBILL_EDIT_" . $record_type) . "\">" . $row->discount_name . "</a></td>";
				    echo "<td class=\"list-value responsive-cell high-priority\">";
				    if ($row->time_limited)
				    {
					    echo nbf_common::nb_date($date_format, $row->end_date);
				    }
				    else
				    {
					    echo NBILL_NOT_APPLICABLE;
				    }
				    echo "</td>";
				    echo "<td class=\"list-value" . ($record_type != 'FEE' ? ' responsive-cell priority' : '') . "\">" . format_number(($record_type == "FEE" ? 0 - $row->percentage : $row->percentage)) . "</td>";
				    echo "<td class=\"selector\">";
				    echo "<a href=\"#\" title=\"" . $alt . "\" onclick=\"for(var i=0; i<" . count($rows) . ";i++) {document.getElementById('cb' + i).checked=false};document.getElementById('cb$i').checked=true;document.adminForm.task.value='$task';document.adminForm.submit();return false;\">";
				    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/$img\" border=\"0\" alt=\"$alt\" /></a></td>";
                    if ($record_type != "FEE")
                    {
                        echo "<td class=\"selector\">";
                        echo "<a href=\"#\" title=\"" . $alt_doc . "\" onclick=\"for(var i=0; i<" . count($rows) . ";i++) {document.getElementById('cb' + i).checked=false};document.getElementById('cb$i').checked=true;document.adminForm.task.value='$task_doc';document.adminForm.submit();return false;\">";
                        echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/$img_doc\" border=\"0\" alt=\"$alt_doc\" /></a></td>";
                    }

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
				    echo "</tr>";
			    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="<?php echo $vendor_col ? "8" : "7"; ?>" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		</form>
		<?php
	}

	/**
	* Edit a discount (or create a new one)
	*/
	public static function editDiscount($discount_id, $row, $vendors, $cats, $products, $selected_cats, $selected_products, $disqual_products, $selected_disqual_cats, $selected_disqual_products, $currencies, $discount_amount_list, $use_posted_values, $order_nos, $countries, $ledger_codes)
	{
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
		$date_format = nbf_common::get_date_format();
        $cal_date_format = nbf_common::get_date_format(true);
		nbf_html::load_calendar();
        $action = nbf_common::nb_strtoupper(nbf_common::get_param($_REQUEST, 'action'));
        $record_type = $action == "FEES" ? "FEE" : "DISCOUNT";
        ?>
		<script language="javascript" type="text/javascript">
		<?php nbf_html::add_js_validation_numeric();
		nbf_html::add_js_validation_date(); ?>

		function nbill_submit_task(task_name)
        {
			var form = document.adminForm;
			if (task_name == 'cancel')
            {
				form.task.value=task_name;
                form.submit();
				return;
		    }

			// do field validation
			if (form.discount_name.value == "" && form.discount_name.value == "")
			{
				alert('<?php echo constant("NBILL_" . $record_type . "_NAME_REQUIRED"); ?>');
			}
			else if (form.time_limited.checked && (form.start_date.value == "" || form.end_date.value == ""))
			{
				alert('<?php echo NBILL_DISCOUNT_DATE_REQUIRED; ?>');
			}
			else if (form.start_date.value.length > 0 && !IsValidDate(form.start_date.value))
			{
				alert('<?php echo sprintf(NBILL_INVALID_DATE_FIELD, NBILL_DISCOUNT_START_DATE, $cal_date_format); ?>');
			}
			else if (form.end_date.value.length > 0 && !IsValidDate(form.end_date.value))
			{
				alert('<?php echo sprintf(NBILL_INVALID_DATE_FIELD, NBILL_DISCOUNT_END_DATE, $cal_date_format); ?>');
			}
			<?php
            if ($record_type != "FEE")
            {
			    foreach ($vendors as $vendor)
			    {
				    foreach ($currencies as $currency)
				    {
					    if (nbf_common::nb_strlen(trim($currency['code'])) > 0)
					    {
						    $suffix = $currency['code'] . "_" . $vendor->id;?>
						    else if (!IsNumeric(form.min_order_value_<?php echo $suffix; ?>.value, true))
						    {
							    alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_DISCOUNT_MIN_ORDER_VALUE); ?>');
						    }
			                <?php
					    }
				    }
			    }
            } ?>
			else if (form.percentage.value == ""<?php
				foreach ($vendors as $vendor)
				{
					foreach ($currencies as $currency)
					{
						if (nbf_common::nb_strlen(trim($currency['code'])) > 0)
						{
							$suffix = $currency['code'] . "_" . $vendor->id;
							echo " && form.amount_" . $suffix . ".value == \"\"";
						}
					}
				}?>)
			{
				alert('<?php echo NBILL_DISCOUNT_AMOUNT_REQUIRED; ?>');
			}
			else if (form.percentage.value.length > 0 && !IsNumeric(form.percentage.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_DISCOUNT_PERCENTAGE); ?>');
			}
			<?php
			foreach ($vendors as $vendor)
			{
				foreach ($currencies as $currency)
				{
					if (nbf_common::nb_strlen(trim($currency['code'])) > 0)
					{
						$suffix = $currency['code'] . "_" . $vendor->id;?>
						else if (!IsNumeric(form.amount_<?php echo $suffix; ?>.value, true))
						{
							alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_DISCOUNT_AMOUNT); ?>');
						}
			            <?php
					}
				}
			} ?>
			else if (!IsNumeric(form.priority.value, true))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_DISCOUNT_PRIORITY); ?>');
			}
			else if (form.time_limited.checked && form.recurring.checked)
			{
				if (confirm('<?php echo constant("NBILL_" . $record_type . "S_WARNING_DATE_PLUS_RECURRING");?>'))
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
		function refresh_vendor()
		{
			var selected_vendor = document.getElementById('vendor_id').value;
			<?php foreach ($vendors as $vendor)
			{
				echo "document.getElementById('prereq_" . $vendor->id . "').style.display = 'none';\n";
                echo "document.getElementById('disqual_" . $vendor->id . "').style.display = 'none';\n";
                if ($record_type != "FEE")
                {
                    echo "document.getElementById('order_values_" . $vendor->id . "').style.display = 'none';\n";
                }
                echo "document.getElementById('discount_amount_" . $vendor->id . "').style.display = 'none';\n";
                echo "document.getElementById('nominal_ledger_" . $vendor->id . "').style.display = 'none';\n";
			} ?>
			document.getElementById('prereq_' + selected_vendor).style.display = 'block';
            document.getElementById('disqual_' + selected_vendor).style.display = 'block';
            <?php if ($record_type != "FEE")
            { ?>
                document.getElementById('order_values_' + selected_vendor).style.display = '';
            <?php } ?>
            document.getElementById('discount_amount_' + selected_vendor).style.display = '';
            document.getElementById('nominal_ledger_' + selected_vendor).style.display = '';
		}
		function select_product(vendor_id)
		{
			var selected_box = document.getElementById('cat_sel_product_' + vendor_id);
			var available_box = document.getElementById('cat_product_' + vendor_id);

			for (var i = 0; i < available_box.options.length; i++)
			{
				if (available_box.options[i].selected)
				{
					var exists = false;
					for (var j = 0; j < selected_box.options.length; j++)
					{
						if (selected_box.options[j].value == available_box.options[i].value)
						{
							exists = true;
							break;
						}
					}
					if (!exists)
					{
						var optn = document.createElement("option");
						optn.text = available_box.options[i].text;
						optn.value = available_box.options[i].value;
						selected_box.options.add(optn);
					}
				}
			}
			update_selected_products(vendor_id);
		}

		function remove_product(vendor_id)
		{
			var selected_box = document.getElementById('cat_sel_product_' + vendor_id);
			for (var i=0; i < selected_box.options.length; i++)
			{
				if (selected_box.options[i].selected)
				{
					selected_box.remove(i);
					i--;
				}
			}
			update_selected_products(vendor_id);
		}

		function update_selected_products(vendor_id)
		{
			var selected_box = document.getElementById('cat_sel_product_' + vendor_id);
			var selected_options = new Array();

			for (var i = 0; i < selected_box.options.length; i++)
			{
				selected_options[i] = selected_box.options[i].value;
			}
			document.getElementById('prerequisite_products_' + vendor_id).value = selected_options.join(',');
		}

        function select_disqual_product(vendor_id)
        {
            var selected_box = document.getElementById('cat_sel_disqual_product_' + vendor_id);
            var available_box = document.getElementById('cat_disqual_product_' + vendor_id);

            for (var i = 0; i < available_box.options.length; i++)
            {
                if (available_box.options[i].selected)
                {
                    var exists = false;
                    for (var j = 0; j < selected_box.options.length; j++)
                    {
                        if (selected_box.options[j].value == available_box.options[i].value)
                        {
                            exists = true;
                            break;
                        }
                    }
                    if (!exists)
                    {
                        var optn = document.createElement("option");
                        optn.text = available_box.options[i].text;
                        optn.value = available_box.options[i].value;
                        selected_box.options.add(optn);
                    }
                }
            }
            update_selected_disqual_products(vendor_id);
        }

        function remove_disqual_product(vendor_id)
        {
            var selected_box = document.getElementById('cat_sel_disqual_product_' + vendor_id);
            for (var i=0; i < selected_box.options.length; i++)
            {
                if (selected_box.options[i].selected)
                {
                    selected_box.remove(i);
                    i--;
                }
            }
            update_selected_disqual_products(vendor_id);
        }

        function update_selected_disqual_products(vendor_id)
        {
            var selected_box = document.getElementById('cat_sel_disqual_product_' + vendor_id);
            var selected_options = new Array();

            for (var i = 0; i < selected_box.options.length; i++)
            {
                selected_options[i] = selected_box.options[i].value;
            }
            document.getElementById('disqualifying_products_' + vendor_id).value = selected_options.join(',');
        }
		</script>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, nbf_common::get_param($_REQUEST, 'action')); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php $discount_name = $use_posted_values ? nbf_common::get_param($_POST, 'discount_name', null, true) : $row->discount_name;
				echo $row->id ? constant("NBILL_EDIT_" . $record_type) . " '$discount_name'" : constant("NBILL_NEW_" . $record_type); ?>
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
        <input type="hidden" name="action" value="<?php echo nbf_common::nb_strtolower($action); ?>" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $discount_id; ?>" />
        <input type="hidden" name="no_record_limit" id="no_record_limit" value="<?php echo nbf_common::get_param($_REQUEST, 'no_record_limit'); ?>" />
		<?php nbf_html::add_filters(); ?>

		<?php
		if (count($order_nos) > 0)
		{
			echo "<p align=\"left\" style=\"color:#ff0000;font-weight:bold;\">" . constant("NBILL_" . $record_type . "_WARNING_IN_USE") . "<br /><br />" . implode(", ", $order_nos) . "</p>";
            if (count($order_nos) == nbf_globals::$record_limit)
            { ?>
                <br /><span style="color:#ff0000;font-weight:bold"><?php echo sprintf(constant("NBILL_" . $record_type . "_RECORD_LIMIT_WARNING"), nbf_globals::$record_limit, nbf_globals::$record_limit); ?></span><br />
                <input type="button" class="button btn" name="remove_record_limit" id="remove_record_limit" value="<?php echo NBILL_DISCOUNT_SHOW_ALL; ?>" onclick="adminForm.no_record_limit.value='1';adminForm.submit();return false;" />
            <?php }
		}
		?>

        <?php
        $tab_settings = new nbf_tab_group();
        $tab_settings->start_tab_group("admin_settings");
        $tab_settings->add_tab_title("basic", NBILL_ADMIN_TAB_BASIC);
        $tab_settings->add_tab_title("advanced", NBILL_ADMIN_TAB_ADVANCED);
        ob_start();
        ?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-<?php echo strtolower($record_type); ?>">
		    <tr>
			    <th colspan="2"><?php echo constant("NBILL_" . $record_type . "_DETAILS"); ?>
			    <?php
				    if (count($vendors) < 2)
				    {
					    echo "<input type=\"hidden\" name=\"vendor_id\" id=\"vendor_id\" value=\"" . $vendors[0]->id . "\" />";
					    $selected_vendor = $vendors[0]->id;
					    $_POST['vendor_id'] = $selected_vendor;
				    }
			    ?>
			    </th>
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
								    $selected_vendor = $use_posted_values ? nbf_common::get_param($_POST, 'vendor_id') : nbf_common::get_param($_POST, 'vendor_filter');
							    }
							    echo nbf_html::select_list($vendor_name, "vendor_id", 'id="vendor_id" onchange="refresh_vendor();" class="inputbox"', $selected_vendor);
						    ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_VENDOR_ID, "vendor_id_help"); ?>
					    </td>
				    </tr>
			    <?php }
		    ?>
		    <tr id="nbill-admin-tr-<?php echo strtolower($record_type); ?>-name">
			    <td class="nbill-setting-caption">
				    <?php echo constant("NBILL_" . $record_type . "_NAME"); ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="discount_name" id="discount_name" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST, 'discount_name', null, true)) : str_replace("\"", "&quot;", $row->discount_name); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_NAME"), "discount_name_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-display-name">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISCOUNT_DISPLAY_NAME; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="display_name" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST, 'display_name', null, true)) : str_replace("\"", "&quot;", $row->display_name); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_DISPLAY_NAME"), "display_name_help"); ?>
			    </td>
		    </tr>
            <!-- Custom Fields Placeholder -->
		    <tr id="nbill-admin-tr-time-limited">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISCOUNT_TIME_LIMITED; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    echo nbf_html::yes_or_no_options("time_limited", "", $use_posted_values ? nbf_common::get_param($_POST, 'time_limited', null, true) : $row->time_limited); ?>
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_TIME_LIMITED"), "time_limited_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-start-date">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISCOUNT_START_DATE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    if ($use_posted_values)
					    {
						    $date_value = nbf_common::get_param($_POST, 'start_date');
					    }
					    else
					    {
						    $date_value = $row->id ? nbf_common::nb_date($date_format, $row->start_date) : "";
					    }

					    $date_parts = nbf_date::get_date_parts($date_value, $cal_date_format);
					    if (@$date_parts['y'] < 1971)
					    {
						    $date_value = "";
					    }
					    ?>
					    <span style="white-space:nowrap"><input type="text" name="start_date" maxlength="19" class="inputbox calendar" value="<?php echo $date_value; ?>" />
					    <input type="button" name="start_date_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.start_date,'<?php echo $cal_date_format; ?>',this);" /></span>
                        <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_START_DATE"), "start_date_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-end-date">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISCOUNT_END_DATE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    if ($use_posted_values)
					    {
						    $date_value = nbf_common::get_param($_POST, 'end_date');
					    }
					    else
					    {
						    $date_value = $row->id ? nbf_common::nb_date($date_format, $row->end_date) : "";
					    }

					    $date_parts = nbf_date::get_date_parts($date_value, $cal_date_format);
					    if (@$date_parts['y'] < 1971)
					    {
						    $date_value = "";
					    }
					    ?>
					    <span style="white-space:nowrap"><input type="text" name="end_date" maxlength="19" class="inputbox calendar" value="<?php echo $date_value; ?>" />
					    <input type="button" name="end_date_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.end_date,'<?php echo $cal_date_format; ?>',this);" /></span>
                        <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_END_DATE"), "end_date_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-global">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISCOUNT_GLOBAL; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php echo nbf_html::yes_or_no_options("global", "", $use_posted_values ? nbf_common::get_param($_POST, 'global', null, true) : $row->global); ?>
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_GLOBAL"), "global_help"); ?>
			    </td>
		    </tr>
            <?php if ($action != 'FEES') { ?>
		    <tr id="nbill-admin-tr-logged-in">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISCOUNT_LOGGED_IN; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php echo nbf_html::yes_or_no_options("logged_in_only", "", $use_posted_values ? nbf_common::get_param($_POST, 'logged_in_only', null, true) : $row->logged_in_only); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_DISCOUNT_LOGGED_IN, "logged_in_only_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-min-order-value">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISCOUNT_MIN_ORDER_VALUE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    //New tab for each currency
					    foreach ($vendors as $vendor)
					    {
						    echo "<table id=\"order_values_" . $vendor->id . "\" width=\"100%\"><tr><td align=\"left\">";
						    $min_order_values = $discount_amount_list[$vendor->id];
						    $nbf_tab_min_val = new nbf_tab_group();
                            $nbf_tab_min_val->start_tab_group("min_value_" . $vendor->id);
                            foreach ($currencies as $currency)
                            {
                                if (nbf_common::nb_strlen(trim($currency['code'])) > 0)
                                {
                                    $suffix = $currency['code'] . "_" . $vendor->id;
                                    $nbf_tab_min_val->add_tab_title($suffix, $currency['code']);
                                }
                            }

						    foreach ($currencies as $currency)
						    {
							    if (nbf_common::nb_strlen(trim($currency['code'])) > 0)
							    {
								    $min_order_value = format_number(0.00, 'currency');
								    foreach ($min_order_values as $min_order_record)
								    {
									    if ($min_order_record->currency == $currency['code'])
									    {
										    $min_order_value = format_number($min_order_record->min_order_value, 'currency');
										    break;
									    }
								    }
								    $suffix = $currency['code'] . "_" . $vendor->id;
								    ob_start();
								    ?>
									    <br /><!--Line break needed for IE6-->
									    <input type="text" name="min_order_value_<?php echo $suffix; ?>" id="min_order_value_<?php echo $suffix; ?>" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST, 'min_order_value_' . $suffix) : $min_order_value; ?>" class="inputbox" />
								    <?php
								    $nbf_tab_min_val->add_tab_content($suffix, ob_get_clean());
							    }
						    }
						    $nbf_tab_min_val->end_tab_group();
						    echo "</td></tr></table>";
					    }?>
                        <?php nbf_html::show_static_help(NBILL_INSTR_DISCOUNT_MIN_ORDER_VALUE, "min_order_value_help"); ?>
			    </td>
		    </tr>
            <?php } ?>
		    <tr id="nbill-admin-tr-shipping-only">
			    <td class="nbill-setting-caption">
				    <?php echo constant("NBILL_" . $record_type . "_SHIPPING_ONLY"); ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    echo nbf_html::yes_or_no_options("shipping_discount", "", $use_posted_values ? nbf_common::get_param($_POST, 'shipping_discount', null, true) : $row->shipping_discount); ?>
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_SHIPPING_ONLY"), "shipping_discount_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-percentage">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISCOUNT_PERCENTAGE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="percentage" value="<?php $percentage = ($use_posted_values ? nbf_common::get_param($_POST, 'percentage', null, true) : format_number($row->percentage)); echo $record_type == "FEE" ? 0 - $percentage : $percentage; ?>" class="inputbox numeric" /> %
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_PERCENTAGE"), "percentage_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-amount">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISCOUNT_AMOUNT; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    //New tab for each currency
					    foreach ($vendors as $vendor)
					    {
						    echo "<table id=\"discount_amount_" . $vendor->id . "\"><tr><td align=\"left\">";
						    $discount_amounts = $discount_amount_list[$vendor->id];
						    $nbf_tab_discount = new nbf_tab_group();
						    $nbf_tab_discount->start_tab_group("amount_" . $vendor->id);
                            foreach ($currencies as $currency)
                            {
                                if (nbf_common::nb_strlen(trim($currency['code'])) > 0)
                                {
                                    $suffix = $currency['code'] . "_" . $vendor->id;
                                    $nbf_tab_discount->add_tab_title($suffix, $currency['code']);
                                }
                            }

						    foreach ($currencies as $currency)
						    {
							    if (nbf_common::nb_strlen(trim($currency['code'])) > 0)
							    {
								    $discount_amount = format_number(0.00, 'currency');
								    foreach ($discount_amounts as $discount_amount_record)
								    {
									    if ($discount_amount_record->currency == $currency['code'])
									    {
										    $discount_amount = format_number($discount_amount_record->amount, 'currency');
										    break;
									    }
								    }
								    $suffix = $currency['code'] . "_" . $vendor->id;
								    ob_start();
								    ?>
									    <input type="text" name="amount_<?php echo $suffix; ?>" id="amount_<?php echo $suffix; ?>" value="<?php $this_amt = ($use_posted_values ? nbf_common::get_param($_POST, 'amount_' . $suffix) : $discount_amount); echo $record_type == "FEE" ? 0 - $this_amt : $this_amt; ?>" class="inputbox" />
								    <?php
								    $nbf_tab_discount->add_tab_content($suffix, ob_get_clean());
							    }
						    }
						    $nbf_tab_discount->end_tab_group();
						    echo "</td></tr></table>";
					    }?>
                        <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_AMOUNT"), "amount_help"); ?>
			    </td>
		    </tr>
            <tr id="nbill-admin-tr-voucher">
			    <td class="nbill-setting-caption">
				    <?php echo constant("NBILL_" . $record_type . "_VOUCHER"); ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="voucher" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST, 'voucher', null, true) : $row->voucher; ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_VOUCHER"), "voucher_help"); ?>
			    </td>
		    </tr>
            <tr id="nbill-admin-tr-recurring">
			    <td class="nbill-setting-caption">
				    <?php echo constant("NBILL_" . $record_type . "_RECURRING"); ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    echo nbf_html::yes_or_no_options("recurring", "", $use_posted_values ? nbf_common::get_param($_POST, 'recurring', null, true) : $row->recurring); ?>
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_RECURRING"), "recurring_help"); ?>
			    </td>
		    </tr>
            <tr id="nbill-admin-tr-renewals">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISCOUNT_RENEWALS; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    echo nbf_html::yes_or_no_options("add_to_renewals", "", $use_posted_values ? nbf_common::get_param($_POST, 'add_to_renewals', null, true) : $row->add_to_renewals); ?>
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_RENEWALS"), "add_to_renewals_help"); ?>
                </td>
            </tr>
		    <tr id="nbill-admin-tr-published">
			    <td class="nbill-setting-caption">
				    <?php echo $record_type == "FEE" ? NBILL_FEE_PUBLISHED : NBILL_DISCOUNT_AVAILABLE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    echo nbf_html::yes_or_no_options("available", "", $use_posted_values ? nbf_common::get_param($_POST, 'available', null, true) : $row->available); ?>
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_AVAILABLE"), "available_help"); ?>
			    </td>
		    </tr>
            <?php if ($record_type != "FEE") { ?>
            <tr id="nbill-admin-tr-documents">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISCOUNT_AVAILABLE_DOCS; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    echo nbf_html::yes_or_no_options("available_for_documents", "", $use_posted_values ? nbf_common::get_param($_POST, 'available_for_documents', null, true) : $row->available_for_documents); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_DISCOUNT_AVAILABLE_DOCS, "available_for_documents_help"); ?>
                </td>
            </tr>
		    <tr id="nbill-admin-tr-auto-disable">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISCOUNT_AUTO_DISABLE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    echo nbf_html::yes_or_no_options("unavailable_when_used", "", $use_posted_values ? nbf_common::get_param($_POST, 'unavailable_when_used', null, true) : $row->unavailable_when_used); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_DISCOUNT_AUTO_DISABLE, "unavailable_when_used_help"); ?>
			    </td>
		    </tr>
            <?php } ?>
		    <tr id="nbill-admin-tr-country">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISCOUNT_COUNTRY; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    $country_list = array();
                    foreach ($countries as $country)
                    {
                        $country_list[] = nbf_html::list_option($country['code'], $country['description']);
                    }
                    echo nbf_html::select_list($country_list, "country", "class=\"inputbox\"", $use_posted_values ? nbf_common::get_param($_POST, 'country') : ($row->id ? $row->country : "WW"));
                    ?>
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_COUNTRY"), "country_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-notes">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_NOTES; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <textarea name="notes"><?php echo $use_posted_values ? nbf_common::get_param($_POST, 'notes', null, true) : $row->notes; ?></textarea>
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
            <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
            <tr>
                <th colspan="2"><?php echo constant("NBILL_" . $record_type . "_DETAILS"); ?>
                </th>
            </tr>
            <tr id="nbill-admin-tr-apply-to">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISCOUNT_APPLY_TO; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    $apply_options = array();
                    $apply_options[] = nbf_html::list_option("net", NBILL_DISCOUNT_APPLY_NET);
                    $apply_options[] = nbf_html::list_option("tax", NBILL_DISCOUNT_APPLY_TAX);
                    $apply_options[] = nbf_html::list_option("gross", NBILL_DISCOUNT_APPLY_GROSS);
                    echo nbf_html::select_list($apply_options, "apply_to", null, $use_posted_values ? nbf_common::get_param($_POST, 'apply_to') : $row->apply_to);
                    ?>
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_APPLY_TO"), "apply_to_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-exclusive">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISCOUNT_EXCLUSIVE; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    echo nbf_html::yes_or_no_options("exclusive", "", $use_posted_values ? nbf_common::get_param($_POST, 'exclusive') : $row->exclusive); ?>
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_EXCLUSIVE"), "exclusive_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-compound">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISCOUNT_COMPOUND; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    echo nbf_html::yes_or_no_options("is_compound", "", $use_posted_values ? nbf_common::get_param($_POST, 'is_compound') : $row->is_compound); ?>
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_COMPOUND"), "is_compound_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-priority">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISCOUNT_PRIORITY; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="priority" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST, 'priority', null, true) : $row->priority; ?>" class="inputbox" style="width:160px;" />
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_PRIORITY"), "priority_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-ledger-code">
                <td class="nbill-setting-caption">
                    <?php echo constant("NBILL_" . $record_type . "_LEDGER_CODE"); ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    foreach ($vendors as $vendor)
                    {
                        if ($vendor->id == $selected_vendor)
                        {
                            $visibility = "inline";
                        }
                        else
                        {
                            $visibility = "none";
                        }
                        $ledger_list = array();
                        $ledger_list[] = nbf_html::list_option("", NBILL_DISCOUNT_LEDGER_AUTO);
                        foreach ($ledger_codes[$vendor->id] as $ledger_code)
                        {
                            $ledger_list[] = nbf_html::list_option($ledger_code->code, $ledger_code->code . " - " . $ledger_code->description);
                        }
                        echo nbf_html::select_list($ledger_list, "nominal_ledger_" . $vendor->id, "id=\"nominal_ledger_" . $vendor->id . "\" style=\"display:$visibility\"", $use_posted_values ? nbf_common::get_param($_POST, 'nominal_ledger_' . $vendor->id) : $row->nominal_ledger_code);
                    }
                    ?>
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_LEDGER_CODE"), "nominal_ledger_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-prerequiste-products">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISCOUNT_PREREQ_PRODUCTS; ?>
                </td>
                <td class="nbill-setting-value" colspan="2">
                    <?php
                    //Wizard style select list (categories, products, selected products)
                    foreach ($vendors as $vendor)
                    {
                        if ($vendor->id == $selected_vendor)
                        {
                            $visibility = "block";
                        }
                        else
                        {
                            $visibility = "none";
                        }
                    ?>
                    <div id="prereq_<?php echo $vendor->id; ?>" class="list-multi-selector" style="display:<?php echo $visibility;?>">
                        <div class="list-container">
                            <?php echo NBILL_DISCOUNT_PREREQ_CATS; ?><br />
                            <div class="scrollable-multi-select">
                                <select multiple="multiple" size="7" name="category_<?php echo $vendor->id; ?>[]" id="category_<?php echo $vendor->id; ?>" onchange="nbill_submit_task('cat_changed');">
                                <?php
                                    foreach ($cats[$vendor->id] as $cat)
                                    {
                                        echo "<option value=\"" . $cat['id'] . "\"";
                                        if ($use_posted_values)
                                        {
                                            if (is_array(nbf_common::get_param($_POST,'category_' . $vendor->id)))
                                            {
                                                if (array_search($cat['id'], nbf_common::get_param($_POST,'category_' . $vendor->id)) !== false)
                                                {
                                                    echo " selected=\"selected\"";
                                                }
                                            }
                                        }
                                        echo ">" . $cat['name'] . "</option>";
                                    }
                                ?>
                                </select>
                            </div>
                        </div>
                        <div class="list-container">
                            <?php echo NBILL_DISCOUNT_PREREQ_CAT_PROD; ?><br />
                            <div class="scrollable-multi-select">
                                <select multiple="multiple" size="7" id="cat_product_<?php echo $vendor->id; ?>" name="cat_product_<?php echo $vendor->id; ?>[]">
                                <?php
                                    foreach ($products[$vendor->id] as $product)
                                    {
                                        echo "<option value=\"" . $product->id . "\">" . $product->name . "</option>";
                                    }
                                ?>
                                </select>
                            </div>
                        </div>
                        <div class="list-container">
                            <div class="list-selector-buttons">
                                <input type="button" class="button btn" name="select_product_<?php echo $vendor->id; ?>" id="select_product_<?php echo $vendor->id; ?>" value=" > " onclick="select_product(<?php echo $vendor->id; ?>);" title="<?php echo NBILL_SELECT; ?>" /><br />
                                <input type="button" class="button btn" name="deselect_product_<?php echo $vendor->id; ?>" id="deselect_product_<?php echo $vendor->id; ?>" value=" < " onclick="remove_product(<?php echo $vendor->id; ?>);" title="<?php echo NBILL_DESELECT; ?>" />
                            </div>
                            <div class="list-selected-items">
                                <span style="white-space:nowrap;"><?php echo NBILL_DISCOUNT_PREREQ_SELECTED_PROD; ?></span><br />
                                <div class="scrollable-multi-select">
                                    <select multiple="multiple" size="7" id="cat_sel_product_<?php echo $vendor->id; ?>" name="cat_sel_product_<?php echo $vendor->id; ?>[]">
                                    <?php
                                        $selected_product_ids = array();
                                        if ($use_posted_values)
                                        {
                                            $selected_product_ids = explode(',', nbf_common::get_param($_POST,'prerequisite_products_' . $vendor->id));
                                        }
                                        else
                                        {
                                            if ($vendor->id == $row->vendor_id)
                                            {
                                                $selected_product_ids = explode(',', $row->prerequisite_products);
                                            }
                                        }
                                        foreach ($selected_product_ids as $product_id)
                                        {
                                            foreach ($selected_products[$vendor->id] as $selected_product)
                                            {
                                                if ($selected_product->id == $product_id)
                                                {
                                                    echo "<option value=\"$product_id\">" . $selected_product->name . "</option>";
                                                break;
                                                }
                                            }
                                        }
                                    ?>
                                    </select>
                                </div>
                                <input type="hidden" name="prerequisite_products_<?php echo $vendor->id; ?>" id="prerequisite_products_<?php echo $vendor->id; ?>" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'prerequisite_products_' . $vendor->id) : $row->prerequisite_products; ?>" />
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_PREREQ_PRODUCTS"), "prerequisite_products_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-disqualifying-products">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISCOUNT_DISQUAL_PRODUCTS; ?>
                </td>
                <td class="nbill-setting-value" colspan="2">
                    <?php
                    //Wizard style select list (categories, products, selected products)
                    foreach ($vendors as $vendor)
                    {
                        if ($vendor->id == $selected_vendor)
                        {
                            $visibility = "block";
                        }
                        else
                        {
                            $visibility = "none";
                        }
                    ?>
                    <div id="disqual_<?php echo $vendor->id; ?>" class="list-multi-selector" style="display:<?php echo $visibility;?>">
                        <div class="list-container">
                            <?php echo NBILL_DISCOUNT_DISQUAL_CATS; ?><br />
                            <div class="scrollable-multi-select">
                                <select multiple="multiple" size="7" name="disqual_category_<?php echo $vendor->id; ?>[]" id="disqual_category_<?php echo $vendor->id; ?>" onchange="nbill_submit_task('cat_changed');">
                                <?php
                                    foreach ($cats[$vendor->id] as $cat)
                                    {
                                        echo "<option value=\"" . $cat['id'] . "\"";
                                        if ($use_posted_values)
                                        {
                                            if (is_array(nbf_common::get_param($_POST,'disqual_category_' . $vendor->id)))
                                            {
                                                if (array_search($cat['id'], nbf_common::get_param($_POST,'disqual_category_' . $vendor->id)) !== false)
                                                {
                                                    echo " selected=\"selected\"";
                                                }
                                            }
                                        }
                                        echo ">" . $cat['name'] . "</option>";
                                    }
                                ?>
                                </select>
                            </div>
                        </div>
                        <div class="list-container">
                            <?php echo NBILL_DISCOUNT_DISQUAL_CAT_PROD; ?><br />
                            <div class="scrollable-multi-select">
                                <select multiple="multiple" size="7" id="cat_disqual_product_<?php echo $vendor->id; ?>" name="cat_disqual_product_<?php echo $vendor->id; ?>[]">
                                <?php
                                    foreach ($disqual_products[$vendor->id] as $disqual_product)
                                    {
                                        echo "<option value=\"" . $disqual_product->id . "\">" . $disqual_product->name . "</option>";
                                    }
                                ?>
                                </select>
                            </div>
                        </div>
                        <div class="list-container">
                            <div class="list-selector-buttons">
                                <input type="button" class="button btn" name="select_disqual_product_<?php echo $vendor->id; ?>" id="select_disqual_product_<?php echo $vendor->id; ?>" value=" > " onclick="select_disqual_product(<?php echo $vendor->id; ?>);" title="<?php echo NBILL_SELECT; ?>" /><br />
                                <input type="button" class="button btn" name="deselect_disqual_product_<?php echo $vendor->id; ?>" id="deselect_disqual_product_<?php echo $vendor->id; ?>" value=" < " onclick="remove_disqual_product(<?php echo $vendor->id; ?>);" title="<?php echo NBILL_DESELECT; ?>" />
                            </div>
                            <div class="list-selected-items">
                                <span style="white-space:nowrap;"><?php echo NBILL_DISCOUNT_DISQUAL_SELECTED_PROD; ?></span><br />
                                <div class="scrollable-multi-select">
                                    <select multiple="multiple" size="7" id="cat_sel_disqual_product_<?php echo $vendor->id; ?>" name="cat_sel_disqual_product_<?php echo $vendor->id; ?>[]">
                                    <?php
                                        $selected_product_ids = array();
                                        if ($use_posted_values)
                                        {
                                            $selected_product_ids = explode(',', nbf_common::get_param($_POST,'disqualifying_products_' . $vendor->id));
                                        }
                                        else
                                        {
                                            if ($vendor->id == $row->vendor_id)
                                            {
                                                $selected_product_ids = explode(',', $row->disqualifying_products);
                                            }
                                        }
                                        foreach ($selected_product_ids as $product_id)
                                        {
                                            foreach ($selected_disqual_products[$vendor->id] as $selected_product)
                                            {
                                                if ($selected_product->id == $product_id)
                                                {
                                                    echo "<option value=\"$product_id\">" . $selected_product->name . "</option>";
                                                    break;
                                                }
                                            }
                                        }
                                    ?>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="disqualifying_products_<?php echo $vendor->id; ?>" id="disqualifying_products_<?php echo $vendor->id; ?>" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'disqualifying_products_' . $vendor->id) : $row->disqualifying_products; ?>" />
                        </div>
                    </div>
                    <?php } ?>
                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_" . $record_type . "_DISQUAL_PRODUCTS"), "disqualifying_products_help"); ?>
                </td>
            </tr>
            </table>
        </div>
        <?php
        $tab_settings->add_tab_content("advanced", ob_get_clean());
        $tab_settings->end_tab_group();
        ?>

		</form>

		<script type="text/javascript">
			refresh_vendor();
		</script>
		<?php
	}
}