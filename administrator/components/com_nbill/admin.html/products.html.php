<?php
/**
* HTML output for products
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillProducts
{
    protected static $custom_column_count = 0;

	public static function showProducts($rows, $pagination, $vendors, $categories, $attachments = array())
	{
        $vendor_col = false;
        
        ?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "products"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_PRODUCTS_TITLE; ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		} ?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="products" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
        <input type="hidden" name="attachment_id" value="" />

		<p align="left"><?php echo NBILL_PRODUCTS_INTRO ?></p>

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
			}
			else
			{
				echo "<input type=\"hidden\" name=\"vendor_filter\" id=\"vendor_filter\" value=\"" . $vendors[0]->id . "\" />";
				$_POST['vendor_filter'] = $vendors[0]->id;
			}

			//Display filter dropdown if there are categories
			//Create a dropdown of categories for each vendor - show/hide via javascript depending on vendor selected
			$cat_title_displayed = false;
			foreach ($vendors as $vendor)
			{
				if (count($vendors) < 2)
				{
					echo "<p align=\"left\">";
				}
				$cat_list = array();
				$cat_list[] = nbf_html::list_option(-999, NBILL_ALL);
				foreach ($categories[$vendor->id] as $cat_item)
				{
					$cat_list[] = nbf_html::list_option($cat_item['id'], $cat_item['name']);
				}
				$category_filter = -999;
				if (isset($_POST['category_filter_' . $vendor->id]))
				{
					$category_filter = nbf_common::get_param($_POST, 'category_filter_' . $vendor->id);
				}
				if (strtolower(nbf_version::$suffix) != 'lite' && count($cat_list) > 0) {
					if (!$cat_title_displayed) {
						if (count($vendors) > 0) {
							echo "&nbsp;&nbsp;&nbsp;<span id=\"category_filter_label\">" . NBILL_PRODUCT_CATS . "</span>&nbsp;";
						} else {
							echo "<p align=\"left\"><span id=\"category_filter_label\">" . NBILL_PRODUCT_CATS . "</span>&nbsp;";
						}
						$cat_title_displayed = true;
					}
					echo nbf_html::select_list($cat_list, "category_filter_" . $vendor->id, 'class="inputbox" id="category_filter_' . $vendor->id . '" onchange="document.adminForm.submit();"', $category_filter);
				}
			}

            if ($pagination->record_count > nbf_globals::$record_limit)
            {
                $csv_click = "if (confirm('" . sprintf(NBILL_CSV_EXPORT_LIMIT_WARNING, nbf_globals::$record_limit, nbf_globals::$record_limit, nbf_globals::$record_limit) . "')){document.getElementById('do_csv_download').value=1;document.adminForm.submit();document.getElementById('do_csv_download').value='';}return false;";
            }
            else
            {
                $csv_click = "document.getElementById('do_csv_download').value=1;document.adminForm.submit();document.getElementById('do_csv_download').value='';return false;";
            }
            ?>
            <input type="hidden" name="do_csv_download" id="do_csv_download" value="" />
            &nbsp;
            <span style="white-space:nowrap;"><a href="#" title="<?php echo NBILL_CSV_DOWNLOAD_LIST_DESC; ?>" onclick="<?php echo $csv_click; ?>"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path ?>/images/icons/medium/csv.gif" alt="<?php echo NBILL_CSV_DOWNLOAD_LIST_DESC; ?>" style="vertical-align:middle" /></a>
            <strong><a href="#" title="<?php echo NBILL_CSV_DOWNLOAD_LIST_DESC; ?>" onclick="<?php echo $csv_click; ?>"><?php echo NBILL_CSV_DOWNLOAD; ?></a></strong></span>
            <?php

			if (count($vendors) > 0 || $cat_title_displayed)
			{
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
                <?php self::renderCustomColumn('id'); ?>
			    <th class="title">
				    <?php echo NBILL_PRODUCT_NAME; ?>
			    </th>
                <?php self::renderCustomColumn('name'); ?>
			    <th class="title">
				    <?php echo NBILL_PRODUCT_SKU; ?>
			    </th>
                <?php self::renderCustomColumn('sku'); ?>
                <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
			    <th class="title">
				    <?php echo NBILL_PRODUCT_CATEGORY; ?>
			    </th>
                <?php self::renderCustomColumn('category'); ?>
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
                <?php self::renderCustomColumn('vendor'); ?>
		    </tr>
		    <?php
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];
				    $link = nbf_cms::$interop->admin_page_prefix . "&action=products&task=edit&cid=$row->id";
				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\">$checked</td>";
                    self::renderCustomColumn('id', $row);
				    echo "<td class=\"list-value\"><a href=\"$link\" title=\"" . NBILL_EDIT_PRODUCT . "\">" . $row->name . "</a>";
                    
                    echo "</td>";
                    self::renderCustomColumn('name', $row);
				    echo "<td class=\"list-value\">" . $row->product_code . "</td>";
                    self::renderCustomColumn('sku', $row);

                    if (strtolower(nbf_version::$suffix) != 'lite') {
				        //Only show category name if more than one listed
				        $cat_col = false;
				        foreach ($categories[$row->vendor_id] as $category)
				        {
					        if ($category['id'] == $row->category)
					        {
						        echo "<td class=\"list-value\">" . $row->category_name . "</td>";
                                $cat_col = true;
						        break;
					        }
				        }
				        if (!$cat_col)
				        {
					        echo "<td>&nbsp;</td>";
				        }
                        self::renderCustomColumn('category', $row);
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
                    self::renderCustomColumn('vendor', $row);
				    echo "</tr>";
			    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="<?php echo ($vendor_col ? 6 : 5) + self::$custom_column_count; ?>" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		</form>
        <?php
        
	}

    protected static function renderCustomColumn($column_name, $row = 'undefined')
    {
        $method = ($row == 'undefined') ? 'render_header' : 'render_row';
        if (file_exists(dirname(__FILE__) . "/custom_columns/products/after_$column_name.php")) {
            include_once(dirname(__FILE__) . "/custom_columns/products/after_$column_name.php");
            if (is_callable(array("nbill_admin_products_after_$column_name", $method))) {
                call_user_func(array("nbill_admin_products_after_$column_name", $method), $row);
                if ($method == 'render_header') {
                    self::$custom_column_count++;
                }
            }
        }
    }

    public static function downloadProductsCSV($vendors, $rows, $product_prices, $max_currencies_per_product, $currencies)
    {
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
        echo NBILL_PRODUCT_CATEGORY . ",";
        echo NBILL_PRODUCT_SKU . ",";
        echo NBILL_NOMINAL_LEDGER_CODE . ",";
        echo NBILL_PRODUCT_NAME . ",";
        echo NBILL_PRODUCT_DESCRIPTION . ",";
        echo NBILL_IS_FREEBIE . ",";
        echo NBILL_IS_TAXABLE . ",";
        echo NBILL_REQUIRES_SHIPPING . ",";
        echo NBILL_SHIPPING_SERVICES . ",";
        echo NBILL_SHIPPING_UNITS . ",";
        echo NBILL_AUTO_FULFIL . ",";
        echo NBILL_IS_DOWNLOADABLE . ",";
        echo NBILL_DOWNLOAD_LOCATION . ",";
        echo NBILL_NO_OF_DAYS_AVAILABLE . ",";
        echo NBILL_DOWNLOAD_LINK_TEXT . ",";
        echo NBILL_DOWNLOAD_LOCATION_2 . ",";
        echo NBILL_DOWNLOAD_LINK_TEXT_2 . ",";
        echo NBILL_DOWNLOAD_LOCATION_3 . ",";
        echo NBILL_DOWNLOAD_LINK_TEXT_3 . ",";
        echo NBILL_EMAIL_DOWNLOADS . ",";
        echo NBILL_EMAIL_DOWNLOADS_MESSAGE . ",";
        echo NBILL_IS_USER_SUB . ",";
        echo NBILL_SUB_USER_GROUP . ",";
        if (nbf_cms::$interop->multi_user_group)
        {
            echo NBILL_MULTI_GROUP . ",";
        }
        echo NBILL_EXPIRY_LEVEL . ",";
        echo NBILL_EXPIRY_REDIRECT . ",";
        echo NBILL_ALLOW_GLOBAL_DISCOUNTS . ",";
        for ($i = 0; $i < $max_currencies_per_product; $i++)
        {
            echo NBILL_NET_PRICE_SETUP_FEE . sprintf(NBILL_CSV_ITEM_CURRENCY, $currencies[$i]) . ",";
            echo NBILL_NET_PRICE_ONE_OFF . sprintf(NBILL_CSV_ITEM_CURRENCY, $currencies[$i]) . ",";
            echo NBILL_NET_PRICE_WEEKLY . sprintf(NBILL_CSV_ITEM_CURRENCY, $currencies[$i]) . ",";
            echo NBILL_NET_PRICE_FOUR_WEEKLY . sprintf(NBILL_CSV_ITEM_CURRENCY, $currencies[$i]) . ",";
            echo NBILL_NET_PRICE_MONTHLY . sprintf(NBILL_CSV_ITEM_CURRENCY, $currencies[$i]) . ",";
            echo NBILL_NET_PRICE_QUARTERLY . sprintf(NBILL_CSV_ITEM_CURRENCY, $currencies[$i]) . ",";
            echo NBILL_NET_PRICE_SEMI_ANNUALLY . sprintf(NBILL_CSV_ITEM_CURRENCY, $currencies[$i]) . ",";
            echo NBILL_NET_PRICE_ANNUALLY . sprintf(NBILL_CSV_ITEM_CURRENCY, $currencies[$i]) . ",";
            echo NBILL_NET_PRICE_BIANNUALLY . sprintf(NBILL_CSV_ITEM_CURRENCY, $currencies[$i]) . ",";
            echo NBILL_NET_PRICE_FIVE_YEARLY . sprintf(NBILL_CSV_ITEM_CURRENCY, $currencies[$i]) . ",";
            echo NBILL_NET_PRICE_TEN_YEARLY . sprintf(NBILL_CSV_ITEM_CURRENCY, $currencies[$i]) . ",";
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
            echo "\"" . str_replace("\"", "\"\"", $row->category_name) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->product_code) . "\",";
            echo $row->nominal_ledger_code . ",";
            echo "\"" . str_replace("\"", "\"\"", $row->name) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->description) . "\",";
            echo ($row->is_freebie ? NBILL_YES : NBILL_NO) . ",";
            echo ($row->is_taxable ? NBILL_YES : NBILL_NO) . ",";
            echo ($row->requires_shipping ? NBILL_YES : NBILL_NO) . ",";
            echo "\"" . str_replace("\"", "\"\"", $row->shipping_services) . "\",";
            echo format_number($row->shipping_units, 'quantity') . ",";
            echo ($row->auto_fulfil_orders ? NBILL_YES : NBILL_NO) . ",";
            echo ($row->is_downloadable ? NBILL_YES : NBILL_NO) . ",";
            echo "\"" . str_replace("\"", "\"\"", $row->download_location) . "\",";
            echo $row->no_of_days_available . ",";
            echo "\"" . str_replace("\"", "\"\"", $row->download_link_text) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->download_location_2) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->download_link_text_2) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->download_location_3) . "\",";
            echo "\"" . str_replace("\"", "\"\"", $row->download_link_text_3) . "\",";
            echo ($row->email_downloads ? NBILL_YES : NBILL_NO) . ",";
            echo "\"" . str_replace("\"", "\"\"", $row->email_downloads_message) . "\",";
            echo ($row->is_sub ? NBILL_YES : NBILL_NO) . ",";
            echo $row->user_group . ",";
            if (nbf_cms::$interop->multi_user_group)
            {
                echo ($row->multi_group ? NBILL_YES : NBILL_NO) . ",";
            }
            echo $row->expiry_level . ",";
            echo "\"" . str_replace("\"", "\"\"", $row->expiry_redirect) . "\",";
            echo ($row->allow_global_discounts ? NBILL_YES : NBILL_NO);
            $product_price_count = 0;
            for ($currency_index = 0; $currency_index < count($currencies); $currency_index++)
            {
                foreach ($product_prices as $product_price)
                {
                    if ($product_price->product_id == $row->id)
                    {
                        $product_price_count++;
                        if ($product_price->currency_code == $currencies[$currency_index])
                        {
                            echo "," . format_number($product_price->net_price_setup_fee, 'currency');
                            echo "," . format_number($product_price->net_price_one_off, 'currency');
                            echo "," . format_number($product_price->net_price_weekly, 'currency');
                            echo "," . format_number($product_price->net_price_four_weekly, 'currency');
                            echo "," . format_number($product_price->net_price_monthly, 'currency');
                            echo "," . format_number($product_price->net_price_quarterly, 'currency');
                            echo "," . format_number($product_price->net_price_semi_annually, 'currency');
                            echo "," . format_number($product_price->net_price_annually, 'currency');
                            echo "," . format_number($product_price->net_price_biannually, 'currency');
                            echo "," . format_number($product_price->net_price_five_years, 'currency');
                            echo "," . format_number($product_price->net_price_ten_years, 'currency');
                        }
                    }
                }
            }
            for ($i = $product_price_count; $i < $max_currencies_per_product; $i++)
            {
                echo ",,,,,,,,,,,";
            }
            echo "\r\n";
        }
    }

	/**
	* Edit a product (or create a new one)
	*/
	public static function editProduct($product_id, $row, $vendors, $categories, $ledger, $shipping, $prices, $user_groups, $selected_cats = array(), $discounts = array(), $product_discounts = array(), $existing_orders = array(), $price_match_found = false, $user_sub_plugin_present = false, $use_posted_values, $attachments = array())
	{
        nbf_cms::$interop->init_editor();
		$currencies = nbf_xref::get_currencies();
		?>
		<script type="text/javascript" language="javascript">
        var update_existing = false;
		<?php nbf_html::add_js_validation_numeric(); ?>
		function nbill_submit_task(task_name)
		{
			var form = document.adminForm;
			if (task_name == 'cancel')
			{
				presubmit();
				form.task.value=task_name;
                form.submit();
				return;
			}

			//Validate fields
			if (form.name.value == "")
			{
				alert('<?php echo NBILL_PRODUCT_NAME_REQUIRED; ?>');
			}
			<?php
			foreach ($vendors as $vendor)
			{
				foreach ($currencies as $currency)
				{
					if (nbf_common::nb_strlen($currency['code']) > 0)
					{
						$suffix = $currency['code'] . "_" . $vendor->id; ?>
						else if (!IsNumeric(document.getElementById('net_price_one_off_<?php echo $suffix; ?>').value, true))
						{
							alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_NET_PRICE_ONE_OFF); ?>');
						}
                        
						<?php
					}
				}
			}
			?>
            
            else
			{
                <?php
                
                ?>
				presubmit();
				document.adminForm.task.value=task_name;
                document.adminForm.submit();
			}
		}

		function presubmit()
		{
            
		}

        function check_existing_orders(vendor_id, pay_freq, currency, orig_amount, new_amount)
        {
            
        }

		function refresh_vendor()
		{
			//Show the appropriate nominal ledger codes depending on selected vendor
			var vendor_id = document.getElementById('vendor_id').value;
			<?php
			foreach ($vendors as $vendor)
			{
                
                echo "document.getElementById('prices_" . $vendor->id . "').style.display = 'none';\n";

			}
			?>
            
			document.getElementById('prices_' + vendor_id).style.display = 'block';
            switch (vendor_id)
			{
				<?php
                
				?>
			}
		}

        

		//This function only: modified version of code released under LESSER GPL
		//CC-GNU LGPL (see http://cn.creativecommons.org/licenses/LGPL/2.1/index.html)
		function addEvent(obj, type, fn)
		{
			if (obj.attachEvent)
			{
				obj['e'+type+fn] = fn;
				obj[type+fn] = function(){obj['e'+type+fn](window.event);}
				obj.attachEvent('on'+type, obj[type+fn]);
			}
			else
			{
				obj.addEventListener( type, fn, false );
			}
		}

        function getRadioButtonValue(GroupName)
        {
            var retVal = null;

            if (GroupName == null)
            {
                    return null;
            }
            var radio_inputs = document.getElementsByTagName('input');
            for (var i=0; i<radio_inputs.length; i++)
            {
                var input = radio_inputs[i];
                if (input.type == 'radio' && input.name == GroupName && input.checked)
                {
                    retVal = input.value;
                    break;
                }
            }
            return retVal;
        }

        
		</script>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "products"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php echo $row->id ? NBILL_EDIT_PRODUCT . " '" . $row->name . "'" : NBILL_NEW_PRODUCT; ?>
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
        <input type="hidden" name="action" value="products" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $product_id;?>" />
        <input type="hidden" name="update_existing" id="update_existing" value="" />
		<?php nbf_html::add_filters(); ?>

        <?php
        $tab_settings = new nbf_tab_group();
        $tab_settings->start_tab_group("admin_settings");
        $tab_settings->add_tab_title("basic", NBILL_ADMIN_TAB_BASIC);
        if (strtolower(nbf_version::$suffix) != 'lite') {
            $tab_settings->add_tab_title("advanced", NBILL_ADMIN_TAB_ADVANCED);
        }
        ob_start();
        ?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-products">
		    <tr id="nbill-admin-tr-product-details">
			    <th colspan="2"><?php echo NBILL_PRODUCT_DETAILS; ?>
			    <?php
				    if (count($vendors) < 2)
				    {
					    echo "<input type=\"hidden\" name=\"vendor_id\" id=\"vendor_id\" value=\"" . $vendors[0]->id . "\" />";
					    $selected_vendor = $vendors[0]->id;
					    $_POST['vendor_id'] = $vendors[0]->id;
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
								    $selected_vendor = nbf_common::get_param($_POST, 'vendor_filter');
							    }
                                if ($selected_vendor < 1)
                                {
                                    $selected_vendor = @$vendors[0]->id;
                                }
							    echo nbf_html::select_list($vendor_name, "vendor_id", 'class="inputbox" id="vendor_id" onchange="refresh_vendor();"', $use_posted_values ? nbf_common::get_param($_POST, 'vendor_id', '', true) : $selected_vendor);
						    ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_VENDOR_ID, "vendor_id_help"); ?>
					    </td>
				    </tr>
			    <?php }
             ?>

		    <tr id="nbill-admin-tr-sku">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_PRODUCT_SKU; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="product_code" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'product_code', '', true) : $row->product_code); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_PRODUCT_SKU, "product_code_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-product-name">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_PRODUCT_NAME; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="name" id="name" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'name', '', true) : $row->name); ?>" class="inputbox" style="width:160px" />
				    <input type="hidden" name="old_product_name" id="old_product_name" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'name', '', true) : $row->name); ?>" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_PRODUCT_NAME, "name_help"); ?>
			    </td>
		    </tr>
            <tr id="nbill-admin-tr-product-desc">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_PRODUCT_DESCRIPTION; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php echo nbf_cms::$interop->render_editor("description", "editor1", $use_posted_values ? nbf_common::get_param($_POST, 'description', '', true, false, true) : $row->description); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_PRODUCT_HTML_DESCRIPTION, "description_help"); ?>
                </td>
            </tr>
            <!-- Custom Fields Placeholder -->
            <?php  ?>
		    <tr id="nbill-admin-tr-is-free">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_IS_FREEBIE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php echo nbf_html::yes_or_no_options("is_freebie", ($price_match_found ? 'onchange="update_existing=true;"' : ''), $use_posted_values ? nbf_common::get_param($_POST, 'is_freebie', '', true) : $row->is_freebie); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_IS_FREEBIE, "is_freebie_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-prices">
			    <td colspan="2">
				    <?php
					    echo "<div style=\"margin-bottom:5px\">" . NBILL_PRODUCT_PRICE_INTRO . "</div>";

					    //New tab for each currency
					    foreach ($vendors as $vendor)
					    {
						    //echo "<table id=\"prices_" . $vendor->id . "\" width=\"100%\"><tr><td>";
                            echo "<div id=\"prices_" . $vendor->id . "\">";
						    $nbf_tab_currency = new nbf_tab_group();
						    $nbf_tab_currency->start_tab_group("currency_" . $vendor->id);
                            $price_list = $prices[$vendor->id];
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
								    $price_list_record = null;
								    $setup_fee = format_number("0", 'currency');
								    $one_off = format_number("0", 'currency');
								    $weekly = format_number("0", 'currency');
								    $four_weekly = format_number("0", 'currency');
								    $monthly = format_number("0", 'currency');
								    $quarterly = format_number("0", 'currency');
								    $semiannually = format_number("0", 'currency');
								    $annually = format_number("0", 'currency');
								    $biannually = format_number("0", 'currency');
								    $fiveyearly = format_number("0", 'currency');
								    $tenyearly = format_number("0", 'currency');
								    $suffix = $currency['code'] . "_" . $vendor->id;
                                    foreach ($price_list as $price_list_record)
								    {
									    if ($price_list_record->currency_code == $currency['code'])
									    {
										    $setup_fee = format_number($use_posted_values ? nbf_common::get_param($_POST, 'net_price_setup_fee_' . $suffix, '', true) : $price_list_record->net_price_setup_fee, 'currency');
										    $one_off = format_number($use_posted_values ? nbf_common::get_param($_POST, 'net_price_one_off_' . $suffix, '', true) : $price_list_record->net_price_one_off, 'currency');
										    $weekly = format_number($use_posted_values ? nbf_common::get_param($_POST, 'net_price_weekly_' . $suffix, '', true) : $price_list_record->net_price_weekly, 'currency');
										    $four_weekly = format_number($use_posted_values ? nbf_common::get_param($_POST, 'net_price_four_weekly_' . $suffix, '', true) : $price_list_record->net_price_four_weekly, 'currency');
										    $monthly = format_number($use_posted_values ? nbf_common::get_param($_POST, 'net_price_monthly_' . $suffix, '', true) : $price_list_record->net_price_monthly, 'currency');
										    $quarterly = format_number($use_posted_values ? nbf_common::get_param($_POST, 'net_price_quarterly_' . $suffix, '', true) : $price_list_record->net_price_quarterly, 'currency');
										    $semiannually = format_number($use_posted_values ? nbf_common::get_param($_POST, 'net_price_semi_annually_' . $suffix, '', true) : $price_list_record->net_price_semi_annually, 'currency');
										    $annually = format_number($use_posted_values ? nbf_common::get_param($_POST, 'net_price_annually_' . $suffix, '', true) : $price_list_record->net_price_annually, 'currency');
										    $biannually = format_number($use_posted_values ? nbf_common::get_param($_POST, 'net_price_biannually_' . $suffix, '', true) : $price_list_record->net_price_biannually, 'currency');
										    $fiveyearly = format_number($use_posted_values ? nbf_common::get_param($_POST, 'net_price_five_years_' . $suffix, '', true) : $price_list_record->net_price_five_years, 'currency');
										    $tenyearly = format_number($use_posted_values ? nbf_common::get_param($_POST, 'net_price_ten_years_' . $suffix, '', true) : $price_list_record->net_price_ten_years, 'currency');
										    break;
									    }
								    }
								    ob_start();
					                ?>
					 			    <br /><!--Line break needed for IE6-->

								    <table cellpadding="3" cellspacing="0" border="0" id="nbill-admin-table-product-prices-<?php echo $currency['code']; ?>">
									    <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
                                        <tr id="nbill-admin-tr-price-setup-fee">
										    <td class="nbill-setting-caption">
											    <?php echo NBILL_NET_PRICE_SETUP_FEE; ?>
										    </td>
										    <td class="nbill-setting-value">
											    <input type="text" name="net_price_setup_fee_<?php echo $suffix; ?>" id="net_price_setup_fee_<?php echo $suffix; ?>" value="<?php echo $setup_fee; ?>" class="inputbox" />
                                                <?php nbf_html::show_static_help(NBILL_INSTR_NET_PRICE_SETUP_FEE, "net_price_setup_fee_" . $suffix . "_help"); ?>
										    </td>
									    </tr>
                                        <?php } ?>
									    <tr id="nbill-admin-tr-price-one-off">
										    <td class="nbill-setting-caption">
											    <?php echo NBILL_NET_PRICE_ONE_OFF; ?>
										    </td>
										    <td class="nbill-setting-value">
											    <input type="text" name="net_price_one_off_<?php echo $suffix; ?>" id="net_price_one_off_<?php echo $suffix; ?>" value="<?php echo $one_off; ?>" class="inputbox" />
                                                <?php nbf_html::show_static_help(NBILL_INSTR_NET_PRICE_ONE_OFF, "net_price_one_off_" . $suffix . "_help"); ?>
										    </td>
									    </tr>
                                        <?php  ?>
								    </table>
					                <?php
								    $nbf_tab_currency->add_tab_content($suffix, ob_get_clean());
							    }
						    }
						    $nbf_tab_currency->end_tab_group();
						    //echo "</td></tr></table>";
                            echo "</div>";
					    }?>
			    </td>
		    </tr>
            <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
            <tr id="nbill-admin-tr-allow-freq-change">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_PRODUCT_ALLOW_FREQ_CHANGE; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php echo nbf_html::yes_or_no_options("allow_freq_change", "", $use_posted_values ? nbf_common::get_param($_POST, 'allow_freq_change', '', true) : $row->allow_freq_change); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_PRODUCT_ALLOW_FREQ_CHANGE, "allow_freq_change_help"); ?>
                </td>
            </tr>
            <?php } ?>
		    <tr id="nbill-admin-tr-is-taxable">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_IS_TAXABLE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php echo nbf_html::yes_or_no_options("is_taxable", ($price_match_found ? 'onchange="update_existing=true;"' : ''), $use_posted_values ? nbf_common::get_param($_POST, 'is_taxable', '', true) : $row->is_taxable); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_IS_TAXABLE, "is_taxable_help"); ?>
			    </td>
		    </tr>
            <tr id="nbill-admin-tr-electronic-delivery">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_PRODUCT_ELECTRONIC_DELIVERY; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php echo nbf_html::yes_or_no_options("electronic_delivery", ($price_match_found ? 'onchange="update_existing=true;"' : ''), $use_posted_values ? nbf_common::get_param($_POST, 'electronic_delivery', '', true) : $row->electronic_delivery); ?>
                    <input type="hidden" name="electronic_delivery_orig" id="electronic_delivery_orig" value="<?php echo $row->electronic_delivery; ?>" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_PRODUCT_ELECTRONIC_DELIVERY, "electronic_delivery_help"); ?>
                </td>
            </tr>
            <?php  ?>
		    </table>
        </div>

        <?php
        $tab_settings->add_tab_content("basic", ob_get_clean());
        
        $tab_settings->end_tab_group();
        ?>

        <?php
         ?>

		</form>
		<script type="text/javascript">
		refresh_vendor();
		</script>
		<?php
	}
}