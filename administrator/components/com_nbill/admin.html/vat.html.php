<?php
/**
* HTML output for sales tax (VAT)
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillVAT
{
	public static function showTax($rows, $pagination, $vendors)
	{
        $vendor_col = false;
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "tax"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_TAX_TITLE; ?>
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
        <input type="hidden" name="action" value="vat" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

		<p align="left"><?php echo NBILL_TAX_INTRO; ?></p>

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
				    <?php echo NBILL_TAX_NAME; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_TAX_COUNTRY; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_TAX_RATE; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_TAX_ZONE; ?>
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
		    </tr>
		    <?php
                $electronic_delivery_separator_done = false;
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];
                    if (!$electronic_delivery_separator_done && $row->electronic_delivery) {
                        ?>
                        <tr class="nbill_tr_no_highlight">
                            <td colspan="<?php echo (count($vendors) > 1 && $selected_filter == -999) ? '7' : '6' ; ?>">
                                <?php echo NBILL_TAX_ELECTRONIC_DELIVERY_ITEMS; ?>
                            </td>
                        </tr>
                        <?php
                        $electronic_delivery_separator_done = true;
                    }
				    $link = nbf_cms::$interop->admin_page_prefix . "&action=vat&task=edit&cid=$row->id";
				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\">$checked</td>";
				    echo "<td class=\"list-value\"><a href=\"$link\" title=\"" . NBILL_EDIT_TAX . "\">" . $row->tax_name;
				    if (nbf_common::nb_strlen(@$row->tax_abbreviation) > 0)
				    {
					    echo " (" . @$row->tax_abbreviation . ")";
				    }
				    echo "</a></td>";
				    echo "<td class=\"list-value\">" . $row->country_code . "</td>";
				    echo "<td class=\"list-value\">" . format_number($row->tax_rate) . " %</td>";
				    echo "<td class=\"list-value\">" . $row->tax_zone . "</td>";
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
		    <tr class="nbill_tr_no_highlight"><td colspan="<?php echo $vendor_col ? "7" : "6"; ?>" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>
		</form>
		<?php
	}

	/**
	* Edit a tax rate (or create a new one)
	*/
	public static function editTax($tax_id, $row, $country_codes, $vendors, $use_posted_values = false, $affected_orders = null, $auto_renew_count = 0)
	{
		nbf_cms::$interop->init_editor();
		?>
		<script language="javascript" type="text/javascript">
		<?php
		nbf_html::add_js_validation_numeric();
		?>
		function nbill_submit_task(task_name) {
			var form = document.adminForm;
			if (task_name == 'cancel') {
				document.adminForm.task.value=task_name;
                document.adminForm.submit();
				return;
			}

			// do field validation
            <?php
            
            if (!$affected_orders)
            { ?>
			    if (form.tax_name.value == "")
			    {
				    alert('<?php echo NBILL_TAX_NAME_REQUIRED; ?>');
			    }
			    else if (form.country_code.value == "")
			    {
				    alert('<?php echo NBILL_TAX_COUNTRY_REQUIRED; ?>');
			    }
			    else if (!IsNumeric(form.tax_rate.value, true))
			    {
				    alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_TAX_RATE); ?>');
			    }
			    else
			    {
				    document.adminForm.task.value=task_name;
                    document.adminForm.submit();
			    }
                <?php
            } ?>
		}

		function refresh_vendor()
		{
			//Show the appropriate dropdowns depending on selected vendor
			var vendor_id = document.getElementById('vendor_id').value;
			<?php
			foreach ($vendors as $vendor)
			{
				echo "document.getElementById('div_pay_inst_" . $vendor->id . "').style.display = 'none';";
				echo "document.getElementById('div_sml_prt_" . $vendor->id . "').style.display = 'none';";
			}
			?>
			document.getElementById('div_pay_inst_' + vendor_id).style.display = 'inline';
			document.getElementById('div_sml_prt_' + vendor_id).style.display = 'inline';
		}

		</script>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "tax"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php echo $row->id ? NBILL_EDIT_TAX . " '$row->tax_name'" : NBILL_NEW_TAX; ?>
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
        <input type="hidden" name="action" value="vat" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $tax_id;?>" />
		<?php nbf_html::add_filters(); ?>

		<?php
        
        ?>

        <?php
        $tab_settings = new nbf_tab_group();
        $tab_settings->start_tab_group("admin_settings");
        $tab_settings->add_tab_title("basic", NBILL_ADMIN_TAB_BASIC);
        $tab_settings->add_tab_title("advanced", NBILL_ADMIN_TAB_ADVANCED);
        ob_start();
        ?>

        <div class="rounded-table">
            <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-vat">
            <tr>
                <th colspan="2"><?php echo NBILL_TAX_DETAILS; ?></th>
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
                                    $selected_vendor = intval(nbf_common::get_param($_POST, 'vendor_id'));
                                }
                                else if($row->id)
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
            <tr id="nbill-admin-tr-tax-name">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_NAME; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="tax_name" id="tax_name" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST, 'tax_name', null, true)) : str_replace("\"", "&quot;", $row->tax_name); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_NAME, "tax_name_help"); ?>
                </td>
            </tr>
            <!-- Custom Fields Placeholder -->
            <tr id="nbill-admin-tr-tax-abbreviation">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_ABBREVIATION; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="tax_abbreviation" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST, 'tax_abbreviation', null, true)) : str_replace("\"", "&quot;", $row->tax_abbreviation); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_ABBREVIATION, "tax_abbreviation_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-tax-country">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_COUNTRY; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $vendor_country = array();
                        foreach ($country_codes as $country_code)
                        {
                            $vendor_country[] = nbf_html::list_option($country_code['code'], nbf_common::nb_ucwords(nbf_common::nb_strtolower($country_code['description'])));
                        }
                        echo nbf_html::select_list($vendor_country, "country_code", 'class="inputbox" id="country_code"', ($use_posted_values ? nbf_common::get_param($_POST, 'country_code', null, true) : ($row->country_code ? $row->country_code : 'WW')));
                    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_COUNTRY, "country_code_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-tax-zone">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_ZONE; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="tax_zone" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST, 'tax_zone', null, true) : $row->tax_zone; ?>" class="inputbox" maxlength="5" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_ZONE, "tax_zone_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-tax-reference-desc">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_REFERENCE_DESC; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="tax_reference_desc" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST, 'tax_reference_desc', null, true)) : str_replace("\"", "&quot;", $row->tax_reference_desc); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_REFERENCE_DESC, "tax_reference_desc_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-tax-rate">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_RATE; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="tax_rate" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST, 'tax_rate', null, true) : format_number($row->tax_rate); ?>" class="inputbox" /> %
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_RATE, "tax_rate_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-online-exempt">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_ONLINE_EXEMPT; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php echo nbf_html::yes_or_no_options("online_exempt", "", $use_posted_values ? nbf_common::get_param($_POST, 'online_exempt', null, true) : $row->online_exempt); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_ONLINE_EXEMPT, "online_exempt_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-exempt-with-ref">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_EXEMPT_WITH_REF; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php echo nbf_html::yes_or_no_options("exempt_with_ref_no", "", $use_posted_values ? nbf_common::get_param($_POST, 'exempt_with_ref_no', null, true) : $row->exempt_with_ref_no); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_EXEMPT_WITH_REF, "exempt_with_ref_no_help"); ?>
                </td>
            </tr>
            <?php
            nbf_html::show_admin_setting_yes_no($row, 'electronic_delivery', 'TAX_');
            ?>
            </table>
        </div>

        <?php
        $tab_settings->add_tab_content("basic", ob_get_clean());
        ob_start();
        ?>

        <div class="rounded-table">
            <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-advanced">
            <tr>
                <th colspan="2"><?php echo NBILL_TAX_DETAILS; ?></th>
            </tr>
            <tr><td colspan="2"><p><?php echo NBILL_TAX_ADVANCED_INTRO; ?></p></td></tr>
            <tr id="nbill-admin-tr-tax-payment-instr">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_PAYMENT_INSTR; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    foreach ($vendors as $vendor)
                    {
                        if ($use_posted_values && $vendor->id == nbf_common::get_param($_POST, 'vendor_id'))
                        {
                            $pay_inst = nbf_common::get_param($_POST, 'pay_inst_' . $vendor->id, null, true);
                        }
                        else
                        {
                            $pay_inst = $row->payment_instructions;
                        }

                        if (!defined( '_JOS_EDITORXTD_INCLUDED' ))
                        {
                            //Suppress display of mosimage and mospagebreak buttons
                            define( '_JOS_EDITORXTD_INCLUDED', 1 );
                        }
                        echo "<div id=\"div_pay_inst_$vendor->id\">";
                        echo nbf_cms::$interop->render_editor('pay_inst_' . $vendor->id, 'editor1' . $vendor->id, $pay_inst);
                        echo "</div>";
                    }
                    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_PAYMENT_INSTR, "pay_inst_help"); ?>

                </td>
            </tr>
            <tr id="nbill-admin-tr-tax-small-print">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TAX_SMALL_PRINT; ?>
                </td>
                <td  class="nbill-setting-value">
                    <?php
                    foreach ($vendors as $vendor)
                    {
                        if ($use_posted_values && $vendor->id == nbf_common::get_param($_POST, 'vendor_id'))
                        {
                            $sml_prt = nbf_common::get_param($_POST, 'sml_prt_' . $vendor->id, null, true);
                        }
                        else
                        {
                            $sml_prt = $row->small_print;
                        }
                        if (!defined( '_JOS_EDITORXTD_INCLUDED' ))
                        {
                            //Suppress display of mosimage and mospagebreak buttons
                            define( '_JOS_EDITORXTD_INCLUDED', 1 );
                        }
                        echo "<div id=\"div_sml_prt_$vendor->id\">";
                        echo nbf_cms::$interop->render_editor('sml_prt_' . $vendor->id, 'editor2' . $vendor->id, $sml_prt);
                        echo "</div>";
                    }
                ?>
                <?php nbf_html::show_static_help(NBILL_INSTR_TAX_SMALL_PRINT, "sml_prt_help"); ?>
                </td>
            </tr>
            </table>
        </div>

        <?php
        $tab_settings->add_tab_content("advanced", ob_get_clean());
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