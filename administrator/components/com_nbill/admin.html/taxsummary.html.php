<?php
/**
* HTML output for tax summary report
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillTaxsummary
{
	public static function showTaxSummaryReport($vendors, $vendor_name, $include_unpaid, $currencies, $invoices, $invoice_items, $shipping, $incomes, $tax_info, $expenditures, $credit_notes, $credit_note_items, $write_offs, $write_off_items, $date_format)
	{
		nbf_cms::$interop->add_html_header('<link rel="stylesheet" href="' . nbf_cms::$interop->nbill_site_url_path . '/style/admin/reports.css" type="text/css" />');
        nbf_html::load_calendar();
        $tab_group_started = false;
        $tab_contents = array();
		?>

		<script type="text/javascript">
		function save_tax_scheme()
		{
			if (document.adminForm.include_unpaid[0].checked)
			{
				tax_scheme = 0;
			}
			else
			{
				tax_scheme = 1;
			}
			document.cookie = 'nbill_taxscheme_<?php echo md5(nbf_cms::$interop->live_site); ?>=' + tax_scheme + '; expires=Tue, 31 Dec 2199 23:59:59 UTC; path=/';
		}

        function expand(identifier, currency)
        {
            var expanded = document.getElementById('expanded_' + identifier + '_' + currency).value;
            if (expanded == "1")
            {
                //Collapse the node
                document.getElementById('img_' + identifier + '_' + currency).setAttribute('src', '<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png');
                document.getElementById(identifier + '_' + currency).style.display = 'none';
                document.getElementById('expanded_' + identifier + '_' + currency).value = 0;
            }
            else
            {
                //Expand the node
                document.getElementById('img_' + identifier + '_' + currency).setAttribute('src', '<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png');
                document.getElementById(identifier + '_' + currency).style.display = '';
                document.getElementById('expanded_' + identifier + '_' + currency).value = 1;
            }
        }
        </script>

		<?php
		$printer_friendly = nbf_common::get_param($_POST, 'printer_friendly');
		$exclude_filter = array();
		$exclude_filter[] = "search_date_from";
		$exclude_filter[] = "search_date_to";
		nbf_html::add_filters($exclude_filter);
		if ($printer_friendly)
		{
			//Wrap whole lot in a table with cellpadding - only way to get margins to work cross-browser
			echo "<table border=\"0\" cellpadding=\"10\" cellspacing=\"0\"><tr><td>";
		}
		?>
		<table class="adminheading" style="width:100%;">
		<tr>
			<th <?php if ($printer_friendly) {echo "style=\"background-image:none !important; margin-left:0;padding-left:0;\"";} else {echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "summary");} ?>>
				<?php echo NBILL_TAX_SUMMARY_TITLE . " " . NBILL_FOR . " $vendor_name";
				?>
			</th>
			<td align="right" style="text-align:right;">
				<?php if (!$printer_friendly) { ?>
					<form action="<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>" method="post" name="adminFormPF" target="_blank" style="display:inline;margin:0">
						<input type="hidden" name="option" value="<?php echo nbf_common::get_param($_REQUEST, 'option'); ?>" />
						<input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
						<input type="hidden" name="task" value="<?php echo nbf_common::get_param($_REQUEST, 'task'); ?>" />
						<input type="hidden" name="hidemainmenu" value="1" />
						<input type="hidden" name="hide_billing_menu" value="1" />
						<input type="hidden" name="vendor_filter" value="<?php echo nbf_common::get_param($_REQUEST, 'vendor_filter'); ?>" />
						<input type="hidden" name="include_unpaid" value="<?php echo nbf_common::get_param($_REQUEST, 'include_unpaid'); ?>" />
						<input type="hidden" name="search_date_from" value="<?php echo nbf_common::get_param($_REQUEST, 'search_date_from'); ?>" />
						<input type="hidden" name="search_date_to" value="<?php echo nbf_common::get_param($_REQUEST, 'search_date_to'); ?>" />
						<input type="hidden" name="defined_date_range" value="<?php echo nbf_common::get_param($_REQUEST, 'defined_date_range'); ?>" />
                        <input type="hidden" name="printer_friendly" value="1" />
                        <input type="hidden" name="collapsed" value="0" />
						<table cellpadding="5" cellspacing="0" border="0">
                            <tr>
                                <td valign="middle">
                                    <a href="#" onclick="adminFormPF.collapsed.value='0';adminFormPF.submit();return false;" target="_blank"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/medium/print.gif" alt="Print" /></a>
                                </td>
                                <td valign="middle">
                                    <strong><a href="#" onclick="adminFormPF.collapsed.value='0';adminFormPF.submit();return false;" target="_blank"><?php echo NBILL_TAX_SUMMARY_PF_EXPANDED; ?></a></strong>
                                </td>
                            </tr>
                            <tr>
                                <td valign="middle">
                                    <a href="#" onclick="adminFormPF.collapsed.value='1';adminFormPF.submit();return false;" target="_blank"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/medium/print.gif" alt="Print" /></a>
                                </td>
                                <td valign="middle">
                                    <strong><a href="#" onclick="adminFormPF.collapsed.value='1';adminFormPF.submit();return false;" target="_blank"><?php echo NBILL_TAX_SUMMARY_PF_COLLAPSED; ?></a></strong>
                                </td>
                            </tr>
                        </table>
					</form>
				<?php }
				else
				{
					echo "<div style=\"white-space:nowrap\">" . NBILL_DATE_PRINTED . " " . nbf_common::nb_date($date_format, nbf_common::nb_time()) . "</div>";
				}?>
			</td>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		} ?>

		<?php if (!$printer_friendly) { ?>
			<p align="left"><?php echo NBILL_TAX_SUMMARY_INTRO; ?></p>
		<?php } ?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="taxsummary" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

		<?php if ($printer_friendly)
		{
			//Print out date range
			echo "<div style=\"font-size:1.2em\"><strong>" . NBILL_DATE_RANGE . " " . nbf_common::get_param($_REQUEST, 'search_date_from') . " " . NBILL_TO . " " . nbf_common::get_param($_REQUEST, 'search_date_to') . "</strong></div>";
		}
		else
		{ ?>
			<div align="left">
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
					foreach ($vendors as $vendor)
					{
						$vendor_name[] = nbf_html::list_option($vendor->id, $vendor->vendor_name);
					}
					echo nbf_html::select_list($vendor_name, "vendor_filter", 'id="vendor_filter" class="inputbox" onchange="document.adminForm.submit();"', $selected_filter );
					$_POST['vendor_filter'] = $selected_filter;
				}
				else
				{
					echo "<input type=\"hidden\" name=\"vendor_filter\" id=\"vendor_filter\" value=\"" . $vendors[0]->id . "\" />";
					$_POST['vendor_filter'] = $vendors[0]->id;
				}
				echo "&nbsp;&nbsp;" . NBILL_INCLUDE_UNPAID;
				echo nbf_html::yes_or_no_options("include_unpaid", "onclick=\"save_tax_scheme();\"", $include_unpaid);
				echo "&nbsp;&nbsp;";
                nbf_html::show_defined_date_ranges();
                ?>
                <div id="date_range_controls" style="display:<?php echo nbf_common::get_param($_REQUEST,'defined_date_range') == 'specified_range' ? 'block' : 'none'; ?>;">
                    <span style="white-space:nowrap"><?php echo NBILL_DATE_RANGE; $cal_date_format = nbf_common::get_date_format(true); ?>
				    <input type="text" name="search_date_from" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_from'); ?>" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> />
				    <input type="button" name="search_date_from_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.search_date_from,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> /></span>
				    <span style="white-space:nowrap"><?php echo NBILL_TO; ?>
				    <input type="text" name="search_date_to" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_to'); ?>" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> />
				    <input type="button" name="search_date_to_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.search_date_to,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> /></span>
                </div>
                <input type="submit" class="button btn" name="dosearch" value="<?php echo NBILL_GO; ?>" />
			</div>
			<?php
		}

		foreach ($currencies as $currency)
		{
			//Construct summary data
			$taxable_supplies = 0;
			$non_taxable_supplies = 0;
			$summary_rates = array();
			$summary_amounts = array();
			$gross_total = 0;
			$tax_total = 0;
			$net_total = 0;
			$discrepancies = array();
            $cr_discrepancies = array();
			$summary_index = 0;
			$tax_name = NBILL_TAX;
            $rate_discrepancies = array();

            //Store breakdown to prove how we arrived at each amount (in case of VAT inspection!)
            $taxable_supplies_breakdown = array();
            $non_taxable_supplies_breakdown = array();
            $taxable_payments_breakdown = array();
            $non_taxable_payments_breakdown = array();
            $income_summary_breakdown = array();
            $expenditure_summary_breakdown = array();
            $wo_summary_breakdown = array();

			if ($include_unpaid)
			{
				nBillTaxsummary::invoice_summary($invoices[$currency->code], $invoice_items[$currency->code], $shipping,
                        $tax_info, $tax_name, $summary_index, $summary_rates, $summary_amounts, $taxable_supplies, $taxable_supplies_breakdown,
                        $non_taxable_supplies, $non_taxable_supplies_breakdown, $net_total, $tax_total, $gross_total, $income_summary_breakdown, $discrepancies);
			}

            //Add income items (if include_unpaid, the items here will only be those not related to invoices, otherwise, everything)
			foreach ($incomes[$currency->code] as $income)
			{
				if ($income->tax_rate_1 != 0 || $income->tax_amount_1 != 0)
				{
                    $this_rate = $income->tax_rate_1 . ($income->tax_rate_1_electronic_delivery ? ' e' : '');
					$rate_key = array_search($this_rate, $summary_rates);
					if ($rate_key === false)
					{
						//New rate
						$summary_rates[$summary_index] = $this_rate;
						$summary_amounts[$summary_index] = $income->tax_amount_1;
                        if (!isset($income_summary_breakdown[$summary_index]))
                        {
                            $income_summary_breakdown[$summary_index] = array();
                        }
                        self::add_to_breakdown($income_summary_breakdown[$summary_index], 1, $income, "1");
                    	$summary_index++;
					}
					else
					{
						//Existing rate
						$summary_amounts[$rate_key] += $income->tax_amount_1;
                        self::add_to_breakdown($income_summary_breakdown[$rate_key], 1, $income, "1");
                    }
				}
				if ($income->tax_rate_2 != 0 || $income->tax_amount_2 != 0)
				{
                    $this_rate = $income->tax_rate_2 . ($income->tax_rate_2_electronic_delivery ? ' e' : '');
					$rate_key = array_search($this_rate, $summary_rates);
					if ($rate_key === false)
					{
						//New rate
						$summary_rates[$summary_index] = $this_rate;
						$summary_amounts[$summary_index] = $income->tax_amount_2;
                        if (!isset($income_summary_breakdown[$summary_index]))
                        {
                            $income_summary_breakdown[$summary_index] = array();
                        }
                        self::add_to_breakdown($income_summary_breakdown[$summary_index], 1, $income, "2");
                        $summary_index++;
					}
					else
					{
						//Existing rate
						$summary_amounts[$rate_key] += $income->tax_amount_2;
                        self::add_to_breakdown($income_summary_breakdown[$rate_key], 1, $income, "2");
                    }
				}
				if ($income->tax_rate_3 != 0 || $income->tax_amount_3 != 0)
				{
                    $this_rate = $income->tax_rate_3 . ($income->tax_rate_3_electronic_delivery ? ' e' : '');
					$rate_key = array_search($this_rate, $summary_rates);
					if ($rate_key === false)
					{
						//New rate
						$summary_rates[$summary_index] = $this_rate;
						$summary_amounts[$summary_index] = $income->tax_amount_3;
                        if (!isset($income_summary_breakdown[$summary_index]))
                        {
                            $income_summary_breakdown[$summary_index] = array();
                        }
                        self::add_to_breakdown($income_summary_breakdown[$summary_index], 1, $income, "3");
                        $summary_index++;
					}
					else
					{
						//Existing rate
						$summary_amounts[$rate_key] += $income->tax_amount_3;
                        self::add_to_breakdown($income_summary_breakdown[$rate_key], 1, $income, "3");
					}
				}
				$total_tax_for_income_item = float_add($income->tax_amount_1, float_add($income->tax_amount_2, $income->tax_amount_3, 'currency_grand'), 'currency_grand');
				if ($total_tax_for_income_item != 0)
				{
                    $taxable_supplies = float_add($taxable_supplies, float_subtract($income->amount, $total_tax_for_income_item, 'currency_grand'), 'currency_grand');
                    self::add_to_breakdown($taxable_supplies_breakdown, 1, $income);
					$tax_total = float_add($tax_total, $total_tax_for_income_item, 'currency_grand');
				}
				else
				{
                    $non_taxable_supplies = float_add($non_taxable_supplies, $income->amount, 'currency_grand');
                    self::add_to_breakdown($non_taxable_supplies_breakdown, 1, $income);
				}
				$gross_total = float_add($gross_total, $income->amount, 'currency_grand');
				$net_total = float_add($net_total, float_subtract($income->amount, $total_tax_for_income_item, 'currency_grand'), 'currency_grand');
			}

			$report_tax_name = NBILL_VAT_RPT_TAX_NAME;

			//Summarise Expenditure
			$taxable_payments = 0;
			$non_taxable_payments = 0;
			$summary_rates_exp = array();
			$summary_amounts_exp = array();
			$summary_index = 0;
			$gross_total_exp = 0;
			$tax_total_exp = 0;
			$net_total_exp = 0;

			if ($include_unpaid)
			{
				nBillTaxsummary::invoice_summary($credit_notes[$currency->code], $credit_note_items[$currency->code], $shipping, $tax_info, $tax_name,
						$summary_index, $summary_rates_exp, $summary_amounts_exp, $taxable_payments, $taxable_payments_breakdown,
                        $non_taxable_payments, $non_taxable_payments_breakdown, $net_total_exp, $tax_total_exp, $gross_total_exp, $expenditure_summary_breakdown, $cr_discrepancies);
			}

			foreach ($expenditures[$currency->code] as $expenditure)
			{
				if ($expenditure->tax_rate_1 != 0)
				{
                    $this_rate = $expenditure->tax_rate_1 . ($expenditure->tax_rate_1_electronic_delivery ? ' e' : '');
					$rate_key = array_search($this_rate, $summary_rates_exp);
					if ($rate_key === false)
					{
						//New rate
						$summary_rates_exp[$summary_index] = $this_rate;
						$summary_amounts_exp[$summary_index] = $expenditure->tax_amount_1;
                        if (!isset($expenditure_summary_breakdown[$summary_index]))
                        {
                            $expenditure_summary_breakdown[$summary_index] = array();
                        }
                        self::add_to_breakdown($expenditure_summary_breakdown[$summary_index], 3, $expenditure, "1");
						$summary_index++;
					}
					else
					{
						//Existing rate
						$summary_amounts_exp[$rate_key] += $expenditure->tax_amount_1;
                        self::add_to_breakdown($expenditure_summary_breakdown[$rate_key], 3, $expenditure, "1");
					}
				}
				if ($expenditure->tax_rate_2 != 0)
				{
                    $this_rate = $expenditure->tax_rate_2 . ($expenditure->tax_rate_2_electronic_delivery ? ' e' : '');
					$rate_key = array_search($this_rate, $summary_rates_exp);
					if ($rate_key === false)
					{
						//New rate
						$summary_rates_exp[$summary_index] = $this_rate;
						$summary_amounts_exp[$summary_index] = $expenditure->tax_amount_2;
                        if (!isset($expenditure_summary_breakdown[$summary_index]))
                        {
                            $expenditure_summary_breakdown[$summary_index] = array();
                        }
                        self::add_to_breakdown($expenditure_summary_breakdown[$summary_index], 3, $expenditure, "2");
						$summary_index++;
					}
					else
					{
						//Existing rate
						$summary_amounts_exp[$rate_key] += $expenditure->tax_amount_2;
                        self::add_to_breakdown($expenditure_summary_breakdown[$rate_key], 3, $expenditure, "2");
					}
				}
				if ($expenditure->tax_rate_3 != 0)
				{
                    $this_rate = $expenditure->tax_rate_3 . ($expenditure->tax_rate_3_electronic_delivery ? ' e' : '');
					$rate_key = array_search($this_rate, $summary_rates_exp);
					if ($rate_key === false)
					{
						//New rate
						$summary_rates_exp[$summary_index] = $this_rate;
						$summary_amounts_exp[$summary_index] = $expenditure->tax_amount_3;
                        if (!isset($expenditure_summary_breakdown[$summary_index]))
                        {
                            $expenditure_summary_breakdown[$summary_index] = array();
                        }
                        self::add_to_breakdown($expenditure_summary_breakdown[$summary_index], 3, $expenditure, "3");
						$summary_index++;
					}
					else
					{
						//Existing rate
						$summary_amounts_exp[$rate_key] += $expenditure->tax_amount_3;
                        self::add_to_breakdown($expenditure_summary_breakdown[$rate_key], 3, $expenditure, "3");
					}
				}
				$total_tax_for_expenditure_item = float_add($expenditure->tax_amount_1, float_add($expenditure->tax_amount_2, $expenditure->tax_amount_3, 'currency_grand'), 'currency_grand');
				if ($total_tax_for_expenditure_item != 0)
				{
					$taxable_payments = float_add($taxable_payments, float_subtract($expenditure->amount, $total_tax_for_expenditure_item, 'currency_grand'), 'currency_grand');
                    self::add_to_breakdown($taxable_payments_breakdown, 3, $expenditure);
					$tax_total_exp = float_add($tax_total_exp, $total_tax_for_expenditure_item, 'currency_grand');
				}
				else
				{
					$non_taxable_payments = float_add($non_taxable_payments, $expenditure->amount, 'currency_grand');
                    self::add_to_breakdown($non_taxable_payments_breakdown, 3, $expenditure);
				}
				$gross_total_exp = float_add($gross_total_exp, $expenditure->amount, 'currency_grand');
				$net_total_exp = float_add($net_total_exp, float_subtract($expenditure->amount, $total_tax_for_expenditure_item, 'currency_grand'), 'currency_grand');
			}

			//Get any written-off invoices
			$write_off_taxable_supplies = 0;
			$write_off_taxable_supplies_breakdown = array();
            $write_off_non_taxable_supplies = 0;
            $write_off_non_taxable_supplies_breakdown = array();
			$write_off_summary_rates = array();
			$write_off_summary_amounts = array();
			$write_off_summary_index = 0;
			$write_off_gross_total = 0;
			$write_off_tax_total = 0;
			$write_off_net_total = 0;
			$write_off_discrepancies = array();

			if ($include_unpaid)
			{
				nBillTaxsummary::invoice_summary($write_offs[$currency->code], $write_off_items[$currency->code], $shipping, $tax_info, $tax_name,
						$write_off_summary_index, $write_off_summary_rates, $write_off_summary_amounts, $write_off_taxable_supplies, $write_off_taxable_supplies_breakdown,
                        $write_off_non_taxable_supplies, $write_off_non_taxable_supplies_breakdown, $write_off_net_total, $write_off_tax_total, $write_off_gross_total, $wo_summary_breakdown, $write_off_discrepancies);
			}

			//Make sure there is something to display
			if ($gross_total > 0 || $gross_total_exp > 0 || $write_off_gross_total > 0)
			{
				if ($printer_friendly)
				{
					echo "<div class=\"adminheader\" style=\"margin-top:20px;\">" . $currency->code . "</div>";
				}
				else
				{
                    if (!$tab_group_started)
                    {
                        echo "<br />";
                        $nbf_tab_tax_summary = new nbf_tab_group();
                        $nbf_tab_tax_summary->start_tab_group("tax_summary");
                        $tab_group_started = true;
                    }
                    $nbf_tab_tax_summary->add_tab_title($currency->code, $currency->code);
					ob_start();
				}
				?>
				<br />
                <div class="rounded-table">
				    <table class="adminlist">
				    <tr class="nbill_tr_no_highlight">
					    <th colspan="3" <?php if ($printer_friendly) {echo "style=\"background-image:none;\"";} ?>>
                            <?php echo NBILL_TAX_BREAKDOWN_INC; ?>
					    </th>
				    </tr>
				    <tr class="collapse-parent nbill_tr_no_highlight">
					    <td class="tax-summary-label">
                            <?php if (!$printer_friendly && count($taxable_supplies_breakdown) > 0)
                            {
                                ?><a href="#" onclick="expand('taxable_income', '<?php echo $currency->code; ?>');return false;"><img border="0" id="img_taxable_income_<?php echo $currency->code; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png" alt="<?php echo NBILL_BREAKDOWN_EXP_COLL; ?>" /></a><?php
                            }
                            else
                            {
                                if (!($printer_friendly && nbf_common::get_param($_POST, 'collapsed')))
                                {
                                    ?><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png" alt="" /><?php
                                }
                            }
                            ?>
                            <input type="hidden" name="expanded_taxable_income_<?php echo $currency->code; ?>" id="expanded_taxable_income_<?php echo $currency->code; ?>" value="0" />
                            <?php $total_label = ($include_unpaid ? NBILL_TOTAL_TAXABLE_DUE : NBILL_TOTAL_TAXABLE) . " (" . sprintf(NBILL_BREAKDOWN_TOTAL_COUNT, count($taxable_supplies_breakdown)) . ")";
                            echo $total_label; ?>
                        </td>
					    <td class="report-total tax-summary-amount"><?php echo format_number($taxable_supplies, 'currency_grand', true, null, null, $currency->code); ?></td>
					    <td class="tax-summary-help"><?php nbf_html::show_static_help($include_unpaid ? NBILL_INSTR_TOTAL_TAXABLE_DUE : NBILL_INSTR_TOTAL_TAXABLE, "taxable_income_help"); ?></td>
				    </tr>
                    <tr class="nbill_tr_no_highlight" id="taxable_income_<?php echo $currency->code; ?>" <?php if (!$printer_friendly || nbf_common::get_param($_POST, 'collapsed')) { echo "style=\"display:none;\""; } ?>>
                        <td colspan="3">
                            <?php self::output_breakdown($printer_friendly, $taxable_supplies_breakdown, "net_amount", $total_label, $taxable_supplies, $currency, true); ?>
                        </td>
                    </tr>
                    <tr class="collapse-parent nbill_tr_no_highlight">
					    <td class="tax-summary-label">
                            <?php if (!$printer_friendly && count($non_taxable_supplies_breakdown) > 0)
                            {
                                ?><a href="#" onclick="expand('non_taxable_income', '<?php echo $currency->code; ?>');return false;"><img border="0" id="img_non_taxable_income_<?php echo $currency->code; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png" alt="<?php echo NBILL_BREAKDOWN_EXP_COLL; ?>" /></a><?php
                            }
                            else
                            {
                                if (!($printer_friendly && nbf_common::get_param($_POST, 'collapsed')))
                                {
                                    ?><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png" alt="" /><?php
                                }
                            }
                            ?>
                            <input type="hidden" name="expanded_non_taxable_income_<?php echo $currency->code; ?>" id="expanded_non_taxable_income_<?php echo $currency->code; ?>" value="0" />
                            <?php $total_label = ($include_unpaid ? NBILL_TOTAL_NON_TAXABLE_DUE : NBILL_TOTAL_NON_TAXABLE) . " (" . sprintf(NBILL_BREAKDOWN_TOTAL_COUNT, count($non_taxable_supplies_breakdown)) . ")";
                            echo $total_label; ?>
                        </td>
					    <td class="report-total tax-summary-amount"><?php echo format_number($non_taxable_supplies, 'currency_grand', true, null, null, $currency->code); ?></td>
					    <td class="tax-summary-help"><?php nbf_html::show_static_help($include_unpaid ? NBILL_INSTR_TOTAL_NON_TAXABLE_DUE : NBILL_INSTR_TOTAL_NON_TAXABLE, "non_taxable_income_help"); ?></td>
				    </tr>
                    <tr class="nbill_tr_no_highlight" id="non_taxable_income_<?php echo $currency->code; ?>" <?php if (!$printer_friendly || nbf_common::get_param($_POST, 'collapsed')) { echo "style=\"display:none;\""; } ?>>
                        <td colspan="3">
                            <?php self::output_breakdown($printer_friendly, $non_taxable_supplies_breakdown, "net_amount", $total_label, $non_taxable_supplies, $currency, true); ?>
                        </td>
                    </tr>
                    <tr class="nbill_tr_no_highlight">
                        <td><?php echo NBILL_VAT_RPT_TOTAL_NET; ?></td>
                        <td class="report-total tax-summary-amount"><?php echo format_number($net_total, 'currency_grand', true, null, null, $currency->code); ?></td>
                        <td class="tax-summary-help"><?php nbf_html::show_static_help($include_unpaid ? NBILL_INSTR_VAT_RPT_TOTAL_NET_DUE : NBILL_INSTR_VAT_RPT_TOTAL_NET, "total_net_help"); ?></td>
                    </tr>
                    <?php
                    foreach ($summary_rates as $key=>$rate)
				    {?>
					    <tr class="collapse-parent nbill_tr_no_highlight">
						    <td class="tax-summary-label">
                                <?php if (!$printer_friendly && count($income_summary_breakdown[$key]) > 0)
                                {
                                    ?><a href="#" onclick="expand('income_tax_<?php echo $key; ?>', '<?php echo $currency->code; ?>');return false;"><img border="0" id="img_income_tax_<?php echo $key; ?>_<?php echo $currency->code; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png" alt="<?php echo NBILL_BREAKDOWN_EXP_COLL; ?>" /></a><?php
                                }
                                else
                                {
                                    if (!($printer_friendly && nbf_common::get_param($_POST, 'collapsed')))
                                    {
                                        ?><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png" alt="" /><?php
                                    }
                                }
                                ?>
                                <input type="hidden" name="expanded_income_tax_<?php echo $key; ?>_<?php echo $currency->code; ?>" id="expanded_income_tax_<?php echo $key; ?>_<?php echo $currency->code; ?>" value="0" />
                                <?php
                                $total_label = $report_tax_name . " @ " . format_number($rate, 'tax_rate') . "%" . " (" . sprintf(NBILL_BREAKDOWN_TOTAL_COUNT, count($income_summary_breakdown[$key])) . ")";
                                if (substr($rate, strlen($rate) - 2) == ' e') {
                                    $total_label .= ' - ' . NBILL_VAT_RPT_ELECTRONIC_DELIVERY;
                                }
                                echo $total_label; ?>
                            </td>
						    <td class="report-total tax-summary-amount"><?php echo format_number($summary_amounts[$key], 'currency_grand', true, null, null, $currency->code); ?></td>
						    <td>&nbsp;</td>
					    </tr>
                        <tr class="nbill_tr_no_highlight" id="income_tax_<?php echo $key; ?>_<?php echo $currency->code; ?>" <?php if (!$printer_friendly || nbf_common::get_param($_POST, 'collapsed')) { echo "style=\"display:none;\""; } ?>>
                            <td colspan="3">
                                <?php self::output_breakdown($printer_friendly, $income_summary_breakdown[$key], "tax_amount", $total_label, $summary_amounts[$key], $currency, true); ?>
                            </td>
                        </tr>
				    <?php } ?>
				    <tr class="nbill_tr_no_highlight">
                        <td class="tax-summary-label"><?php echo NBILL_VAT_RPT_TOTAL_TAX . " $report_tax_name"; ?></td>
                        <td class="report-total tax-summary-amount"><?php echo format_number($tax_total, 'currency_grand', true, null, null, $currency->code); ?></td>
                        <td class="tax-summary-help"><?php nbf_html::show_static_help($include_unpaid ? NBILL_INSTR_VAT_RPT_TOTAL_TAX_DUE : NBILL_INSTR_VAT_RPT_TOTAL_TAX, "tax_total_help"); ?></td>
                    </tr>
                    <tr class="nbill_tr_no_highlight">
					    <td class="tax-summary-label"><?php echo NBILL_GROSS_TOTAL; ?></td>
					    <td class="report-total tax-summary-amount"><?php echo format_number($gross_total, 'currency_grand', true, null, null, $currency->code); ?></td>
					    <td class="tax-summary-help"><?php nbf_html::show_static_help( $include_unpaid ? NBILL_INSTR_GROSS_TOTAL_DUE : NBILL_INSTR_GROSS_TOTAL, "gross_total_help"); ?></td>
				    </tr>

				    <?php if (count($discrepancies) > 0)
				    {?>
					    <tr class="nbill_tr_no_highlight">
						    <td colspan="3"><hr /></td>
					    </tr>
					    <tr class="nbill_tr_no_highlight">
						    <td class="tax-summary-label"><strong><?php echo NBILL_VAT_RPT_DISCREPANCIES; ?></strong></td>
						    <td class="tax-summary-amount">
						    <?php
							    foreach ($discrepancies as $key=>$value)
							    {?>
								    <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=invoices&task=edit&cid=<?php echo $key; ?>&return=<?php echo base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=taxsummary"); ?>"><?php echo $value; ?></a>&nbsp;&nbsp;
							    <?php }
						    ?>
						    </td>
						    <td class="tax-summary-help"><?php nbf_html::show_static_help(NBILL_INSTR_VAT_RPT_DISCREPANCIES, "discrepancies_help"); ?></td>
					    </tr>
				    <?php
				    }
				    ?>
				    </table>
                </div>
				<br />

                <div class="rounded-table">
                    <table class="adminlist">
                    <tr>
                        <th colspan="3" <?php if ($printer_friendly) {echo "style=\"background-image:none;\"";} ?>>
                            <?php echo NBILL_TAX_BREAKDOWN_EXP; ?>
                        </th>
                    </tr>
                    <tr class="collapse-parent nbill_tr_no_highlight">
                        <td class="tax-summary-label">
                            <?php if (!$printer_friendly && count($taxable_payments_breakdown) > 0)
                            {
                                ?><a href="#" onclick="expand('taxable_expenditure', '<?php echo $currency->code; ?>');return false;"><img border="0" id="img_taxable_expenditure_<?php echo $currency->code; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png" alt="<?php echo NBILL_BREAKDOWN_EXP_COLL; ?>" /></a><?php
                            }
                            else
                            {
                                if (!($printer_friendly && nbf_common::get_param($_POST, 'collapsed')))
                                {
                                    ?><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png" alt="" /><?php
                                }
                            }
                            ?>
                            <input type="hidden" name="expanded_taxable_expenditure_<?php echo $currency->code; ?>" id="expanded_taxable_expenditure_<?php echo $currency->code; ?>" value="0" />
                            <?php $total_label = NBILL_TOTAL_TAXABLE_PAID . " (" . sprintf(NBILL_BREAKDOWN_TOTAL_COUNT, count($taxable_payments_breakdown)) . ")";
                            echo $total_label; ?>
                        </td>
                        <td class="report-total tax-summary-amount"><?php echo format_number($taxable_payments, 'currency_grand', true, null, null, $currency->code); ?></td>
                        <td class="tax-summary-help"><?php nbf_html::show_static_help(NBILL_INSTR_TOTAL_TAXABLE_PAID, "total_taxable_paid_help"); ?></td>
                    </tr>
                    <tr class="nbill_tr_no_highlight" id="taxable_expenditure_<?php echo $currency->code; ?>" <?php if (!$printer_friendly || nbf_common::get_param($_POST, 'collapsed')) { echo "style=\"display:none;\""; } ?>>
                        <td colspan="3">
                            <?php self::output_breakdown($printer_friendly, $taxable_payments_breakdown, "net_amount", $total_label, $taxable_payments, $currency, true); ?>
                        </td>
                    </tr>
                    <tr class="collapse-parent nbill_tr_no_highlight">
                        <td class="tax-summary-label">
                            <?php if (!$printer_friendly && count($non_taxable_payments_breakdown) > 0)
                            {
                                ?><a href="#" onclick="expand('non_taxable_expenditure', '<?php echo $currency->code; ?>');return false;"><img border="0" id="img_non_taxable_expenditure_<?php echo $currency->code; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png" alt="<?php echo NBILL_BREAKDOWN_EXP_COLL; ?>" /></a><?php
                            }
                            else
                            {
                                if (!($printer_friendly && nbf_common::get_param($_POST, 'collapsed')))
                                {
                                    ?><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png" alt="" /><?php
                                }
                            }
                            ?>
                            <input type="hidden" name="expanded_non_taxable_expenditure_<?php echo $currency->code; ?>" id="expanded_non_taxable_expenditure_<?php echo $currency->code; ?>" value="0" />
                            <?php $total_label = NBILL_TOTAL_NON_TAXABLE_PAID . " (" . sprintf(NBILL_BREAKDOWN_TOTAL_COUNT, count($non_taxable_payments_breakdown)) . ")";
                            echo $total_label; ?>
                        </td>
                        <td class="report-total tax-summary-amount"><?php echo format_number($non_taxable_payments, 'currency_grand', true, null, null, $currency->code); ?></td>
                        <td class="tax-summary-help"><?php nbf_html::show_static_help(NBILL_INSTR_TOTAL_NON_TAXABLE_PAID, "total_non_taxable_paid_help"); ?></td>
                    </tr>
                    <tr id="non_taxable_expenditure_<?php echo $currency->code; ?>" class="nbill_tr_no_highlight" <?php if (!$printer_friendly || nbf_common::get_param($_POST, 'collapsed')) { echo "style=\"display:none;\""; } ?>>
                        <td colspan="3">
                            <?php self::output_breakdown($printer_friendly, $non_taxable_payments_breakdown, "net_amount", $total_label, $non_taxable_payments, $currency, true); ?>
                        </td>
                    </tr>
                    <tr class="nbill_tr_no_highlight">
                        <td class="tax-summary-label"><?php echo NBILL_VAT_RPT_TOTAL_NET; ?></td>
                        <td class="report-total tax-summary-amount"><?php echo format_number($net_total_exp, 'currency_grand', true, null, null, $currency->code); ?></td>
                        <td class="tax-summary-help"><?php nbf_html::show_static_help(NBILL_INSTR_VAT_RPT_TOTAL_NET_PAID, "total_net_paid_help"); ?></td>
                    </tr>
                    <?php
                    foreach ($summary_rates_exp as $key=>$rate)
                    {?>
                        <tr class="collapse-parent nbill_tr_no_highlight">
                            <td class="tax-summary-label">
                                <?php if (!$printer_friendly && count($expenditure_summary_breakdown[$key]) > 0)
                                {
                                    ?><a href="#" onclick="expand('expenditure_tax_<?php echo $key; ?>', '<?php echo $currency->code; ?>');return false;"><img border="0" id="img_expenditure_tax_<?php echo $key; ?>_<?php echo $currency->code; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png" alt="<?php echo NBILL_BREAKDOWN_EXP_COLL; ?>" /></a><?php
                                }
                                else
                                {
                                    if (!($printer_friendly && nbf_common::get_param($_POST, 'collapsed')))
                                    {
                                        ?><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png" alt="" /><?php
                                    }
                                }
                                ?>
                                <input type="hidden" name="expanded_expenditure_tax_<?php echo $key; ?>_<?php echo $currency->code; ?>" id="expanded_expenditure_tax_<?php echo $key; ?>_<?php echo $currency->code; ?>" value="0" />
                                <?php $total_label = $report_tax_name . " @ " . format_number($rate, 'tax_rate') . "%" . " (" . sprintf(NBILL_BREAKDOWN_TOTAL_COUNT, count($expenditure_summary_breakdown[$key])) . ")";
                                if (substr($rate, strlen($rate) - 2) == ' e') {
                                    $total_label .= ' - ' . NBILL_VAT_RPT_ELECTRONIC_DELIVERY;
                                }
                                echo $total_label; ?>
                            </td>
                            <td class="report-total tax-summary-amount"><?php echo format_number($summary_amounts_exp[$key], 'currency_grand', true, null, null, $currency->code); ?></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr class="nbill_tr_no_highlight" id="expenditure_tax_<?php echo $key; ?>_<?php echo $currency->code; ?>" <?php if (!$printer_friendly || nbf_common::get_param($_POST, 'collapsed')) { echo "style=\"display:none;\""; } ?>>
                            <td colspan="3">
                                <?php self::output_breakdown($printer_friendly, $expenditure_summary_breakdown[$key], "tax_amount", $total_label, $summary_amounts_exp[$key], $currency, true); ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr class="nbill_tr_no_highlight">
                        <td class="tax-summary-label"><?php echo NBILL_VAT_RPT_TOTAL_TAX . " $report_tax_name"; ?></td>
                        <td class="report-total tax-summary-amount"><?php echo format_number($tax_total_exp, 'currency_grand', true, null, null, $currency->code); ?></td>
                        <td class="tax-summary-help"><?php nbf_html::show_static_help(NBILL_INSTR_VAT_RPT_TOTAL_TAX_PAID, "total_tax_paid_help"); ?></td>
                    </tr>
                    <tr class="nbill_tr_no_highlight">
                        <td class="tax-summary-label"><?php echo NBILL_GROSS_TOTAL; ?></td>
                        <td class="report-total tax-summary-amount"><?php echo format_number($gross_total_exp, 'currency_grand', true, null, null, $currency->code); ?></td>
                        <td class="tax-summary-help"><?php nbf_html::show_static_help(NBILL_INSTR_GROSS_TOTAL_PAID, "gross_total_paid_help"); ?></td>
                    </tr>
                    <?php if (count($cr_discrepancies) > 0)
                    {?>
                        <tr class="nbill_tr_no_highlight">
                            <td colspan="3"><hr /></td>
                        </tr>
                        <tr class="nbill_tr_no_highlight">
                            <td class="tax-summary-label"><strong><?php echo NBILL_VAT_RPT_DISCREPANCIES; ?></strong></td>
                            <td class="tax-summary-amount">
                            <?php
                                foreach ($cr_discrepancies as $key=>$value)
                                {?>
                                    <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=credits&task=edit&cid=<?php echo $key; ?>&return=<?php echo base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=taxsummary"); ?>"><?php echo $value; ?></a>&nbsp;&nbsp;
                                <?php }
                            ?>
                            </td>
                            <td class="tax-summary-help"><?php nbf_html::show_static_help(NBILL_INSTR_VAT_RPT_DISCREPANCIES_CR, "discrepancies_cr_help"); ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                    </table>
                </div>

				<?php
				if (count($write_offs) > 0 && $include_unpaid)
				{
					?>
					<br />
                    <div class="rounded-table">
					    <table class="adminlist">
                            <tr class="nbill_tr_no_highlight">
                                <th colspan="3" <?php if ($printer_friendly) {echo "style=\"background-image:none;\"";} ?>>
                                    <?php echo NBILL_TAX_BREAKDOWN_WO; ?>
                                </th>
                            </tr>
                            <tr class="collapse-parent nbill_tr_no_highlight">
                                <td class="tax-summary-label">
                                    <?php if (!$printer_friendly && count($write_off_taxable_supplies_breakdown) > 0)
                                    {
                                        ?><a href="#" onclick="expand('taxable_wo', '<?php echo $currency->code; ?>');return false;"><img border="0" id="img_taxable_wo_<?php echo $currency->code; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png" alt="<?php echo NBILL_BREAKDOWN_EXP_COLL; ?>" /></a><?php
                                    }
                                    else
                                    {
                                        if (!($printer_friendly && nbf_common::get_param($_POST, 'collapsed')))
                                        {
                                            ?><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png" alt="" /><?php
                                        }
                                    }
                                    ?>
                                    <input type="hidden" name="expanded_taxable_wo_<?php echo $currency->code; ?>" id="expanded_taxable_wo_<?php echo $currency->code; ?>" value="0" />
                                    <?php $total_label = NBILL_TOTAL_TAXABLE_DUE_WO . " (" . sprintf(NBILL_BREAKDOWN_TOTAL_COUNT, count($write_off_taxable_supplies_breakdown)) . ")";
                                    echo $total_label; ?>
                                </td>
                                <td class="report-total tax-summary-amount"><?php echo format_number($write_off_taxable_supplies, 'currency_grand', true, null, null, $currency->code); ?></td>
                                <td class="tax-summary-help"><?php nbf_html::show_static_help(NBILL_INSTR_TOTAL_TAXABLE_DUE_WO, "taxable_due_wo_help"); ?></td>
                            </tr>
                            <tr class="nbill_tr_no_highlight" id="taxable_wo_<?php echo $currency->code; ?>" <?php if (!$printer_friendly || nbf_common::get_param($_POST, 'collapsed')) { echo "style=\"display:none;\""; } ?>>
                                <td colspan="3">
                                    <?php self::output_breakdown($printer_friendly, $write_off_taxable_supplies_breakdown, "net_amount", $total_label, $write_off_taxable_supplies, $currency, true); ?>
                                </td>
                            </tr>
                            <tr class="collapse-parent nbill_tr_no_highlight">
                                <td class="tax-summary-label">
                                    <?php if (!$printer_friendly && count($write_off_non_taxable_supplies_breakdown) > 0)
                                    {
                                        ?><a href="#" onclick="expand('non_taxable_wo', '<?php echo $currency->code; ?>');return false;"><img border="0" id="img_non_taxable_wo_<?php echo $currency->code; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png" alt="<?php echo NBILL_BREAKDOWN_EXP_COLL; ?>" /></a><?php
                                    }
                                    else
                                    {
                                        if (!($printer_friendly && nbf_common::get_param($_POST, 'collapsed')))
                                        {
                                            ?><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png" alt="" /><?php
                                        }
                                    }
                                    ?>
                                    <input type="hidden" name="expanded_non_taxable_wo_<?php echo $currency->code; ?>" id="expanded_non_taxable_wo_<?php echo $currency->code; ?>" value="0" />
                                    <?php $total_label = NBILL_TOTAL_NON_TAXABLE_DUE_WO . " (" . sprintf(NBILL_BREAKDOWN_TOTAL_COUNT, count($write_off_non_taxable_supplies_breakdown)) . ")";
                                    echo $total_label; ?>
                                </td>
                                <td class="report-total tax-summary-amount"><?php echo format_number($write_off_non_taxable_supplies, 'currency_grand', true, null, null, $currency->code); ?></td>
                                <td class="tax-summary-help"><?php nbf_html::show_static_help(NBILL_INSTR_TOTAL_NON_TAXABLE_DUE_WO, "non_taxable_due_wo_help"); ?></td>
                            </tr>
                            <tr class="nbill_tr_no_highlight" id="non_taxable_wo_<?php echo $currency->code; ?>" <?php if (!$printer_friendly || nbf_common::get_param($_POST, 'collapsed')) { echo "style=\"display:none;\""; } ?>>
                                <td colspan="3">
                                    <?php self::output_breakdown($printer_friendly, $write_off_non_taxable_supplies_breakdown, "net_amount", $total_label, $write_off_non_taxable_supplies, $currency, true); ?>
                                </td>
                            </tr>
                            <tr class="nbill_tr_no_highlight">
                                <td class="tax-summary-label"><?php echo NBILL_VAT_RPT_TOTAL_NET_WO; ?></td>
                                <td class="report-total tax-summary-amount"><?php echo format_number($write_off_net_total, 'currency_grand', true, null, null, $currency->code); ?></td>
                                <td class="tax-summary-help"><?php nbf_html::show_static_help(NBILL_INSTR_VAT_RPT_TOTAL_NET_DUE_WO, "total_net_due_wo_help"); ?></td>
                            </tr>
                            <?php
                            foreach ($write_off_summary_rates as $key=>$rate)
                            {?>
                                <tr class="collapse-parent nbill_tr_no_highlight">
                                    <td class="tax-summary-label">
                                        <?php if (!$printer_friendly && count($wo_summary_breakdown[$key]) > 0)
                                        {
                                            ?><a href="#" onclick="expand('wo_tax_<?php echo $key; ?>', '<?php echo $currency->code; ?>');return false;"><img border="0" id="img_wo_tax_<?php echo $key; ?>_<?php echo $currency->code; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png" alt="<?php echo NBILL_BREAKDOWN_EXP_COLL; ?>" /></a><?php
                                        }
                                        else
                                        {
                                            if (!($printer_friendly && nbf_common::get_param($_POST, 'collapsed')))
                                            {
                                                ?><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png" alt="" /><?php
                                            }
                                        }
                                        ?>
                                        <input type="hidden" name="expanded_wo_tax_<?php echo $key; ?>_<?php echo $currency->code; ?>" id="expanded_wo_tax_<?php echo $key; ?>_<?php echo $currency->code; ?>" value="0" />
                                        <?php $total_label = $report_tax_name . " @ " . format_number($rate, 'tax_rate') . "%" . " (" . sprintf(NBILL_BREAKDOWN_TOTAL_COUNT, count($wo_summary_breakdown[$key])) . ")";
                                        if (substr($rate, strlen($rate) - 2) == ' e') {
                                            $total_label .= ' - ' . NBILL_VAT_RPT_ELECTRONIC_DELIVERY;
                                        }
                                        echo $total_label; ?>
                                    </td>
                                    <td class="report-total tax-summary-amount"><?php echo format_number($write_off_summary_amounts[$key], 'currency_grand', true, null, null, $currency->code); ?></td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr class="nbill_tr_no_highlight" id="wo_tax_<?php echo $key; ?>_<?php echo $currency->code; ?>" <?php if (!$printer_friendly || nbf_common::get_param($_POST, 'collapsed')) { echo "style=\"display:none;\""; } ?>>
                                    <td colspan="3">
                                        <?php self::output_breakdown($printer_friendly, $wo_summary_breakdown[$key], "tax_amount", $total_label, $write_off_summary_amounts[$key], $currency, true); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr class="nbill_tr_no_highlight">
                                <td class="tax-summary-label"><?php echo NBILL_VAT_RPT_TOTAL_TAX_WO . " $report_tax_name"; ?></td>
                                <td class="report-total tax-summary-amount"><?php echo format_number($write_off_tax_total, 'currency_grand', true, null, null, $currency->code); ?></td>
                                <td class="tax-summary-help"><?php nbf_html::show_static_help(NBILL_INSTR_VAT_RPT_TOTAL_TAX_DUE_WO, "total_tax_due_wo_help"); ?></td>
                            </tr>
                            <tr class="nbill_tr_no_highlight">
                                <td class="tax-summary-label"><?php echo NBILL_GROSS_TOTAL_WO; ?></td>
                                <td class="report-total tax-summary-amount"><?php echo format_number($write_off_gross_total, 'currency_grand', true, null, null, $currency->code); ?></td>
                                <td class="tax-summary-help"><?php nbf_html::show_static_help(NBILL_INSTR_GROSS_TOTAL_DUE_WO, "gross_total_wo_help"); ?></td>
                            </tr>
                            <?php if (count($write_off_discrepancies) > 0)
                            {?>
                                <tr class="nbill_tr_no_highlight">
                                    <td colspan="3"><hr /></td>
                                </tr>
                                <tr class="nbill_tr_no_highlight">
                                    <td class="tax-summary-label"><strong><?php echo NBILL_VAT_RPT_DISCREPANCIES_WO; ?></strong></td>
                                    <td class="tax-summary-amount">
                                    <?php
                                        foreach ($write_off_discrepancies as $key=>$value)
                                        {?>
                                            <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=invoices&task=edit&cid=<?php echo $key; ?>&return=<?php echo base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=taxsummary"); ?>"><?php echo $value; ?></a>&nbsp;&nbsp;
                                        <?php }
                                    ?>
                                    </td>
                                    <td class="tax-summary-help"><?php nbf_html::show_static_help(NBILL_INSTR_VAT_RPT_DISCREPANCIES_WO, "discrepancies_wo_help"); ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                            </table>
                        </div>
					<?php
				}
				?>

				<br />
                <div class="center-contents">
				    <div class="rounded-table" style="display:inline-block;min-width:300px;">
					    <table class="adminlist">
						    <tr class="nbill_tr_no_highlight">
							    <th colspan="3" <?php if ($printer_friendly) {echo "style=\"background-image:none;\"";} ?>>
								    <?php echo NBILL_TAX_SUMMARY; ?>
							    </th>
						    </tr>
						    <tr class="nbill_tr_no_highlight">
							    <td><?php echo GROSS_PROFIT_LOSS; ?></td><td class="report-total"><?php echo format_number(($gross_total - $gross_total_exp) - $write_off_gross_total, 'currency_grand', true, null, null, $currency->code); ?></td>
						    </tr>
						    <tr class="nbill_tr_no_highlight">
							    <td><?php echo TAX_PAYABLE_REBATE_DUE; ?></td><td class="report-total"><?php echo format_number(($tax_total - $tax_total_exp) - $write_off_tax_total, 'currency_grand', true, null, null, $currency->code); ?></td>
						    </tr>
						    <tr class="nbill_tr_no_highlight">
							    <td><?php echo NET_PROFIT_LOSS; ?></td><td class="report-total"><?php echo format_number(($net_total - $net_total_exp) - $write_off_net_total, 'currency_grand', true, null, null, $currency->code); ?></td>
						    </tr>
					    </table>
				    </div>
                </div>
				<?php
				if (!$printer_friendly)
				{
                    $tab_contents[$currency->code] = ob_get_clean();
				}
			}
		}
		if (!$printer_friendly)
		{
			if ($tab_group_started)
			{
	            foreach ($tab_contents as $key=>$value)
	            {
	                $nbf_tab_tax_summary->add_tab_content($key, $value);
	            }
	            $nbf_tab_tax_summary->end_tab_group();
			}
			echo "<br /><div style=\"clear:both;\"><strong><a href=\"" . nbf_cms::$interop->admin_popup_page_prefix . "&action=taxsummary&task=list_excluded&search_date_from=" . nbf_common::get_param($_POST, 'search_date_from') . "&search_date_to=" . nbf_common::get_param($_POST, 'search_date_to') . "&hide_billing_menu=1\" target=\"_blank\" onclick=\"var popup_url='" . nbf_cms::$interop->admin_popup_page_prefix . "';var childWindow = window.open(popup_url + '&action=taxsummary&task=list_excluded&search_date_from=" . nbf_common::get_param($_POST, 'search_date_from') . "&search_date_to=" . nbf_common::get_param($_POST, 'search_date_to') . "&hide_billing_menu=1', 'excluded', 'width=600,height=500,top=150,left=150,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no'); if (childWindow.opener == null) {childWindow.opener = self;} if(childWindow.focus){ childWindow.focus(); }return false;\">" . NBILL_TAX_SUMMARY_LIST_EXCLUDED . "</a></strong></div>";
		}
		if ($printer_friendly)
		{
			echo "</td></tr></table>";
		}
		?>
		<br />
		</form>

		<?php
	}

	public static function invoice_summary(&$invoices, &$invoice_items, &$shipping, &$tax_info, &$tax_name,
					&$summary_index, &$summary_rates, &$summary_amounts, &$taxable_supplies, &$taxable_supplies_breakdown,
                    &$non_taxable_supplies, &$non_taxable_supplies_breakdown, &$net_total, &$tax_total, &$gross_total,
                    &$summary_breakdown, &$discrepancies)
	{
        $summary_index = 0;
		$tax_rates = array();
		$tax_rate_amounts = array();
        require_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.tax.class.php");
		foreach ($invoices as $invoice)
		{
			nbf_tax::get_tax_rates($invoice, $invoice_items, $shipping, $tax_info, $tax_name, $tax_rates, $tax_rate_amounts, true, null, null, true);
		}

		foreach ($tax_rates as $key=>$tax_rate)
		{
			$tax_rate_item_index = 0;
			foreach ($tax_rate as $tax_rate_key=>$tax_rate_item)
			{
				$rate_key = array_search($tax_rate_item, $summary_rates);
                if ($rate_key === false)
				{
					//New rate
                    $summary_rates[$summary_index] = $tax_rate_item;
					$single_tax_rate = "all";
                    foreach ($invoice_items as $invoice_item)
                    {
                        if ($invoice_item->id == substr($tax_rate_key, 0, nbf_common::nb_strpos($tax_rate_key, "_")))
                        {
                            if (!isset($summary_breakdown[$summary_index]))
                            {
                                $summary_breakdown[$summary_index] = array();
                            }
                            if (substr($tax_rate_key, nbf_common::nb_strpos($tax_rate_key, "_") + 1) == "shipping")
                            {
                                $summary_amounts[$summary_index] = float_add(@$summary_amounts[$summary_index], $invoice_item->tax_for_shipping, 'currency_grand');
                                $single_tax_rate = "shipping";
                            }
                            else
                            {
                                $summary_amounts[$summary_index] = float_add(@$summary_amounts[$summary_index], $invoice_item->tax_for_item, 'currency_grand');
                                $single_tax_rate = "item";
                            }
                            self::add_to_breakdown($summary_breakdown[$summary_index], ($invoice_item->document_type == "CR" ? 4 : 2), $invoice_item, $single_tax_rate);
                            break;
                        }
                    }
					$summary_index++;
				}
				else
				{
					//Existing rate
					$summary_rates[$rate_key] = $tax_rate_item;
                    $single_tax_rate = "all";
                    foreach ($invoice_items as $invoice_item)
                    {
                        if ($invoice_item->id == substr($tax_rate_key, 0, nbf_common::nb_strpos($tax_rate_key, "_")))
                        {

                            if (!isset($summary_breakdown[$rate_key]))
                            {
                                $summary_breakdown[$rate_key] = array();
                            }
                            if (substr($tax_rate_key, nbf_common::nb_strpos($tax_rate_key, "_") + 1) == "shipping")
                            {
                                $summary_amounts[$rate_key] = float_add(@$summary_amounts[$rate_key], $invoice_item->tax_for_shipping, 'currency_grand');
                                $single_tax_rate = "shipping";
                            }
                            else
                            {
                                $summary_amounts[$rate_key] = float_add(@$summary_amounts[$rate_key], $invoice_item->tax_for_item, 'currency_grand');
                                $single_tax_rate = "item";
                            }
                            self::add_to_breakdown($summary_breakdown[$rate_key], ($invoice_item->document_type == "CR" ? 4 : 2), $invoice_item, $single_tax_rate);
                            break;
                        }
                    }
				}
				$tax_rate_item_index++;
			}
		}

		$taxable_supplies = 0;
		$non_taxable_supplies = 0;
		$net_total = 0;
		$tax_total = 0;
		$gross_total = 0;
		foreach ($invoice_items as $invoice_item)
		{
			if ($invoice_item->tax_for_item != 0 || $invoice_item->tax_for_shipping != 0)
			{
				$taxable_supplies = float_add($taxable_supplies, float_add($invoice_item->net_price_for_item, $invoice_item->shipping_for_item, 'currency_grand'), 'currency_grand');
                self::add_to_breakdown($taxable_supplies_breakdown, ($invoice_item->document_type == "CR" ? 4 : 2), $invoice_item);
                $tax_total = float_add($tax_total, float_add($invoice_item->tax_for_item, $invoice_item->tax_for_shipping, 'currency_grand'), 'currency_grand');
			}
			else
			{
				$non_taxable_supplies = float_add($non_taxable_supplies, float_add($invoice_item->net_price_for_item, $invoice_item->shipping_for_item, 'currency_grand'), 'currency_grand');
                self::add_to_breakdown($non_taxable_supplies_breakdown, ($invoice_item->document_type == "CR" ? 4 : 2), $invoice_item);
			}
			$net_total = float_add($net_total, float_add($invoice_item->net_price_for_item, $invoice_item->shipping_for_item, 'currency_grand'), 'currency_grand');
			$gross_total = float_add($gross_total, $invoice_item->gross_price_for_item, 'currency_grand');
			if (format_number(float_add($invoice_item->net_price_for_item, float_add($invoice_item->tax_for_item, float_add($invoice_item->shipping_for_item, $invoice_item->tax_for_shipping, 'currency_grand'), 'currency_grand'), 'currency_grand'), 'currency_grand', false, true) != format_number($invoice_item->gross_price_for_item, 'currency_grand', false, true))
			{
				foreach ($invoices as $invoice)
				{
					if ($invoice->id == $invoice_item->document_id)
					{
						$discrepancies[$invoice_item->document_id] = $invoice->document_no;
						break;
					}
				}
				if (nbf_common::nb_strlen($discrepancies[$invoice_item->document_id]) == 0)
				{
					$discrepancies[$invoice_item->document_id] = NBILL_UNKNOWN;
				}
			}
		}

		//Check for discrepancies between sum of invoice_items and totals on invoice
		$invoice_net_total = 0;
		$invoice_tax_total = 0;
		$invoice_shipping_total = 0;
		$invoice_shipping_tax_total = 0;
		$invoice_gross_total = 0;
        include_once(nbf_cms::$interop->nbill_admin_base_path . '/framework/classes/nbill.process.discount.class.php');
        nbf_discount::apply_section_discounts($invoice_items);
		foreach ($invoices as $invoice)
		{
            $invoice_net_total = float_add($invoice_net_total, $invoice->total_net, 'currency_grand');
			$invoice_tax_total = float_add($invoice_tax_total, $invoice->total_tax, 'currency_grand');
			$invoice_shipping_total = float_add($invoice_shipping_total, $invoice->total_shipping, 'currency_grand');
			$invoice_shipping_tax_total = float_add($invoice_shipping_tax_total, $invoice->total_shipping_tax, 'currency_grand');
			$invoice_gross_total = float_add($invoice_gross_total, $invoice->total_gross, 'currency_grand');
		}
		if (format_number($net_total, 'currency_grand', false, true) != format_number(float_add($invoice_net_total, $invoice_shipping_total, 'currency_grand'), 'currency_grand', false, true)
				|| format_number($tax_total, 'currency_grand', false, true) != format_number(float_add($invoice_tax_total, $invoice_shipping_tax_total, 'currency_grand'), 'currency_grand', false, true)
				|| format_number($gross_total, 'currency_grand', false, true) != format_number($invoice_gross_total, 'currency_grand', false, true))
		{
			//There is a discrepancy - find out which invoice(s) is/are the culprit(s)
			foreach ($invoices as $invoice)
			{
				$items_total_net = 0;
				$items_total_tax = 0;
				$items_total_shipping = 0;
				$items_total_shipping_tax = 0;
				$items_total_gross = 0;
				$invoice_found = false;

				foreach ($invoice_items as $invoice_item)
				{
					if ($invoice_item->document_id == $invoice->id)
					{
						$invoice_found = true;
						$items_total_net = float_add($items_total_net, $invoice_item->net_price_for_item, 'currency_grand');
						$items_total_tax = float_add($items_total_tax, $invoice_item->tax_for_item, 'currency_grand');
						$items_total_shipping = float_add($items_total_shipping, $invoice_item->shipping_for_item, 'currency_grand');
						$items_total_shipping_tax = float_add($items_total_shipping_tax, $invoice_item->tax_for_shipping, 'currency_grand');
						$items_total_gross = float_add($items_total_gross, $invoice_item->gross_price_for_item, 'currency_grand');
					}
					else
					{
						if ($invoice_found)
						{
							break;
						}
					}
				}
				if ($invoice_found)
				{
					if (format_number($items_total_net, null, false, true) != format_number($invoice->total_net, null, false, true)
							|| format_number($items_total_tax, null, false, true) != format_number($invoice->total_tax, null, false, true)
							|| format_number($items_total_shipping, null, false, true) != format_number($invoice->total_shipping, null, false, true)
							|| format_number($items_total_shipping_tax, null, false, true) != format_number($invoice->total_shipping_tax, null, false, true))
					{
						$discrepancies[$invoice->id] = $invoice->document_no;
					}
				}
			}
			$i = 0;
		}
	}

    public static function output_breakdown($printer_friendly, $breakdowns, $value_property, $total_label, $total_value, $currency, $show_tax_ref = false)
    {
        $date_format = nbf_common::get_date_format();
        ?>
        <table cellpadding="0" cellspacing="0" border="0" class="collapsible">
            <tr>
                <th class="expanded-branch<?php $printer_friendly ? " print" : ""; ?>"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/branch-line.png" alt="" /></th>
                <th class="responsive-cell<?php $printer_friendly ? " print" : ""; ?>"><?php echo NBILL_BREAKDOWN_DATE; ?></th>
                <th class="responsive-cell<?php $printer_friendly ? " print" : ""; ?> optional"><?php echo NBILL_BREAKDOWN_TYPE; ?></th>
                <th class="responsive-cell<?php $printer_friendly ? " print" : ""; ?>"><?php echo NBILL_BREAKDOWN_REF; ?></th>
                <th class="responsive-cell<?php $printer_friendly ? " print" : ""; ?> optional"><?php echo NBILL_BREAKDOWN_DESC; ?></th>
                <th class="responsive-cell<?php $printer_friendly ? " print" : ""; ?>"><?php echo NBILL_BREAKDOWN_COUNTRY; ?></th>
                <?php if ($show_tax_ref)
                { ?>
                    <th class="responsive-cell<?php $printer_friendly ? " print" : ""; ?>"><?php echo NBILL_BREAKDOWN_TAX_REF; ?></th>
                <?php } ?>
                <th class="report-total<?php $printer_friendly ? " print" : ""; ?>"><?php echo NBILL_BREAKDOWN_NET; ?></th>
                <?php
                if ($value_property == "tax_amount") { ?>
                    <th class="report-total<?php $printer_friendly ? " print" : ""; ?>"><?php echo NBILL_BREAKDOWN_TAX; ?></th>
                <?php } ?>
            </tr>
            <?php
            $rowcount = 0;
            $net_total = 0;
            $tax_total = 0;
            $net_subtotal = 0;
            $tax_subtotal = 0;
            $prev_cc = count($breakdowns) > 0 ? $breakdowns[0]->country : '';
            foreach ($breakdowns as $breakdown)
            {
                $net_total = float_add($net_total, format_number($breakdown->net_amount));
                $tax_total = float_add($tax_total, format_number($breakdown->tax_amount));
                if ($breakdown->country != $prev_cc) {
                    ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td colspan="<?php echo $show_tax_ref ? '6' : '7'; ?>"><strong><?php echo sprintf(NBILL_VAT_RPT_SUBTOTAL, $prev_cc); ?></strong></td>
                        <td class="report-total<?php $printer_friendly ? " print" : ""; ?>"><?php echo format_number($net_subtotal, 'currency_grand', true, null, null, $currency->code); ?></td>
                        <?php if ($value_property == 'tax_amount') { ?>
                            <td class="report-total<?php $printer_friendly ? " print" : ""; ?>"><?php echo format_number($tax_subtotal, 'currency_grand', true, null, null, $currency->code); ?></td>
                        <?php } ?>
                    </tr>
                    <?php
                    $net_subtotal = 0;
                    $tax_subtotal = 0;
                    $prev_cc = $breakdown->country;
                }
                $net_subtotal = float_add($net_subtotal, format_number($breakdown->net_amount));
                $tax_subtotal = float_add($tax_subtotal, format_number($breakdown->tax_amount));
                $rowcount++;
                $branch_img = nbf_cms::$interop->nbill_site_url_path . "/images/branch.png";
                if ($rowcount == count($breakdowns))
                {
                    $branch_img = nbf_cms::$interop->nbill_site_url_path . "/images/end-branch.png";
                }
                ?>
                <tr style="padding:0px;border:0px;">
                    <td class="expanded-branch<?php $printer_friendly ? " print" : ""; ?>"><img src="<?php echo $branch_img; ?>" alt="" /></td>
                    <td class="responsive-cell<?php $printer_friendly ? " print" : ""; ?>"><?php echo nbf_common::nb_date($date_format, $breakdown->date); ?></td>
                    <td class="responsive-cell<?php $printer_friendly ? " print" : ""; ?> optional"><?php echo @constant("NBILL_BREAKDOWN_TYPE_" . $breakdown->type); ?></td>
                    <td class="responsive-cell<?php $printer_friendly ? " print" : ""; ?>"><?php if (!$printer_friendly) { ?><a href="<?php echo $breakdown->link; ?>" target="_blank"><?php } echo $breakdown->link_text; if (!$printer_friendly) { ?></a><?php } ?></td>
                    <td class="responsive-cell word-breakable<?php $printer_friendly ? " print" : ""; ?> optional"><?php if (!$printer_friendly && nbf_common::nb_strlen($breakdown->description_link) > 0) {echo "<a target=\"_blank\" href=\"" . $breakdown->description_link . "\">";} echo $breakdown->description; if (!$printer_friendly && nbf_common::nb_strlen($breakdown->description_link) > 0) {echo "</a>"; } ?></td>
                    <td class="responsive-cell<?php $printer_friendly ? " print" : ""; ?>"><?php echo $breakdown->country; ?></td>
                    <?php if ($show_tax_ref)
                    { ?>
                        <td class="responsive-cell word-breakable<?php $printer_friendly ? " print" : ""; ?>"><?php echo $breakdown->tax_reference; ?></td>
                    <?php } ?>
                    <td class="report-amount<?php $printer_friendly ? " print" : ""; ?>"><?php echo format_number($breakdown->net_amount, 'currency_grand', true, null, null, $currency->code); ?></td>
                    <?php if ($value_property == 'tax_amount') { ?>
                        <td class="report-amount<?php $printer_friendly ? " print" : ""; ?>"><?php echo format_number($breakdown->tax_amount, 'currency_grand', true, null, null, $currency->code); ?></td>
                    <?php } ?>
                </tr>
                <?php
            }
            if ($net_total != $net_subtotal || $tax_total != $tax_subtotal) {
                ?>
                <tr>
                    <td>&nbsp;</td>
                    <td colspan="<?php echo $show_tax_ref ? '6' : '7'; ?>"><strong><?php echo sprintf(NBILL_VAT_RPT_SUBTOTAL, $prev_cc); ?></strong></td>
                    <td class="report-total<?php $printer_friendly ? " print" : ""; ?>"><?php echo format_number($net_subtotal, 'currency_grand', true, null, null, $currency->code); ?></td>
                    <?php if ($value_property == 'tax_amount') { ?>
                        <td class="report-total<?php $printer_friendly ? " print" : ""; ?>"><?php echo format_number($tax_subtotal, 'currency_grand', true, null, null, $currency->code); ?></td>
                    <?php } ?>
                </tr>
                <?php
            } ?>
            <tr>
                <td>&nbsp;</td>
                <td colspan="<?php echo $show_tax_ref ? '6' : '7'; ?>"><strong><?php echo $total_label; ?></strong></td>
                <td class="report-total<?php $printer_friendly ? " print" : ""; ?>"><?php echo format_number($net_total, 'currency_grand', true, null, null, $currency->code); ?></td>
                <?php if ($value_property == 'tax_amount') { ?>
                    <td class="report-total<?php $printer_friendly ? " print" : ""; ?>"><?php echo format_number($tax_total, 'currency_grand', true, null, null, $currency->code); ?></td>
                <?php } ?>
            </tr>
        </table>
        <?php
    }

	public static function showTaxSummaryExcludedItems($date_format, $document_nos, $income_items, $expenditure, $credit_nos, $write_offs, $include_unpaid)
	{
		?>
		<div style="padding:10px;">
			<h2><?php echo NBILL_TAX_SUMMARY_EXCLUDED_TITLE; ?></h2>
			<p><?php echo NBILL_TAX_SUMMARY_EXCLUDED_INTRO; ?></p>
			<h5 style="margin-bottom:0px;"><?php echo NBILL_EXCLUDED_INCOME_TITLE; ?></h5>
            <div class="rounded-table">
			    <table class="adminlist" cellpadding="3" cellspacing="0">
			    <?php
				    $filters = "&vendor_filter=" . nbf_common::get_param($_POST, 'vendor_filter') . "&search_date_from=" . nbf_common::get_param($_POST, 'search_date_from') . "&search_date_to=" . nbf_common::get_param($_POST, 'search_date_to') . "&include_unpaid=$include_unpaid";
				    if (count($income_items) == 0)
				    {
					    echo "<tr><td align=\"left\">" . NBILL_TAX_SUMMARY_EXCLUDED_NO_INCOME . "</td></tr>";
				    }
				    else
				    {
					    ?>
					    <tr>
						    <th class="title">
							    <?php echo NBILL_TAX_EXCLUDED_RCT_NO; ?>
						    </th>
						    <th class="title">
							    <?php echo NBILL_TAX_EXCLUDED_DATE; ?>
						    </th>
						    <th class="title">
							    <?php echo NBILL_TAX_EXCLUDED_RCD_FROM; ?>
						    </th>
						    <th class="title">
							    <?php echo NBILL_TAX_EXCLUDED_AMOUNT; ?>
						    </th>
						    <th class="title">
							    <?php echo NBILL_TAX_EXCLUDED_NBILL_NO; ?>
						    </th>
					    </tr>
						    <?php
						    $income_total = 0;
                            $currency = '';
						    foreach($income_items as $income_item)
						    {
                                if (isset($income_item->currency)) {
                                    $currency = $income_item->currency;
                                }
							    $income_total += $income_item->amount;
							    $link = nbf_cms::$interop->admin_page_prefix . "&action=income&task=edit&cid=$income_item->id&return=" . base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=taxsummary$filters");
							    echo "<tr>";
							    $transaction_no = $income_item->transaction_no;
							    if (nbf_common::nb_strlen($transaction_no) == 0)
							    {
								    $transaction_no = NBILL_TAX_EXCLUDED_RCT_UNNUMBERED;
							    }
							    echo "<td align=\"left\"><a href=\"javascript: void(0);\" onclick=\"window.opener.location='$link';\" title=\"" . NBILL_TAX_EXCLUDED_EDIT_INC . "\">" . $transaction_no . "</a></td>";
							    echo "<td align=\"left\">" . nbf_common::nb_date($date_format, $income_item->date) . "</td>";
							    echo "<td align=\"left\">" . $income_item->name . "</td>";
							    echo "<td align=\"right\">" . format_number($income_item->amount, 'currency_grand', true, null, null, $currency) . "</td>";
							    $this_document_ids = explode(",", $income_item->document_ids);
							    if (count($this_document_ids) > 0)
							    {
								    $invoice_count = 0;
								    echo "<td align=\"left\">";
								    foreach ($this_document_ids as $this_document_id)
								    {
									    $this_document_nos = array();
									    foreach ($document_nos as $document_no)
									    {
										    if ($document_no->id == $this_document_id)
										    {
											    $invoice_count++;
											    echo "<a href=\"javascript: void(0);\" onclick=\"window.open('index2.php?option=" . NBILL_BRANDING_COMPONENT_NAME . "&action=invoices&task=printpreviewpopup&hidemainmenu=1&items=" . $document_no->id . "', " . nbf_common::nb_time() . ", 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');\">" . $document_no->document_no . "</a> ";
										    }
									    }
								    }
								    if ($invoice_count == 0)
								    {
									    echo NBILL_TAX_EXCLUDED_NO_INV;
								    }
								    echo "</td>";
							    }
							    else
							    {
								    echo "<td align=\"left\">" . NBILL_TAX_EXCLUDED_NO_INV . "</td>";
							    }
							    echo "</tr>";
						    }
						    ?>
						    <tr>
							    <td colspan="3" style="font-weight:bold;"><?php echo sprintf(NBILL_TAX_EXCLUDED_INCOME_TOTAL, count($income_items)); ?></td>
							    <td align="right" style="font-weight:bold;"><?php echo format_number($income_total, 'currency_grand', true, false, null, $currency); ?></td>
							    <td>&nbsp;</td>
						    </tr>
						    </table>
					    <?php
					    }
				    ?>
			    </table>
            </div>
			<br />

			<h5 style="margin-bottom:0px;"><?php echo NBILL_EXCLUDED_EXPENDITURE_TITLE; ?></h5>
            <div class="rounded-table">
			    <table class="adminlist" cellpadding="3" cellspacing="0">
			    <?php
				    if (count($expenditure) == 0)
				    {
					    echo "<tr><td align=\"left\">" . NBILL_TAX_SUMMARY_EXCLUDED_NO_EXP . "</td></tr>";
				    }
				    else
				    {
					    ?>
					    <tr>
						    <th class="title">
							    <?php echo NBILL_TAX_EXCLUDED_PYT_NO; ?>
						    </th>
						    <th class="title">
							    <?php echo NBILL_TAX_EXCLUDED_DATE; ?>
						    </th>
						    <th class="title">
							    <?php echo NBILL_TAX_EXCLUDED_PAID_TO; ?>
						    </th>
						    <th class="title">
							    <?php echo NBILL_TAX_EXCLUDED_AMOUNT; ?>
						    </th>
						    <th class="title">
							    <?php echo NBILL_TAX_EXCLUDED_PYT_FOR; ?>
						    </th>
					    </tr>
						    <?php
						    $expenditure_total = 0;
                            $currency = '';
						    foreach($expenditure as $expenditure_item)
						    {
                                if (isset($expenditure_item->currency)) {
                                    $currency = $expenditure_item->currency;
                                }
							    $expenditure_total += $expenditure_item->amount;
							    $link = nbf_cms::$interop->admin_page_prefix . "&action=expenditure&task=edit&cid=$expenditure_item->id&return=" . base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=taxsummary$filters");
							    echo "<tr>";
							    $transaction_no = $expenditure_item->transaction_no;
							    if (nbf_common::nb_strlen($transaction_no) == 0)
							    {
								    $transaction_no = NBILL_TAX_EXCLUDED_PYT_UNNUMBERED;
							    }
							    echo "<td align=\"left\"><a href=\"javascript: void(0);\" onclick=\"window.opener.location='$link';\" title=\"" . NBILL_TAX_EXCLUDED_EDIT_EXP . "\">" . $transaction_no . "</a></td>";
							    echo "<td align=\"left\">" . nbf_common::nb_date($date_format, $expenditure_item->date) . "</td>";
							    echo "<td align=\"left\">" . $expenditure_item->name . "</td>";
							    echo "<td align=\"right\">" . format_number($expenditure_item->amount, 'currency_grand', true, null, null, $currency) . "</td>";
							    $this_document_ids = explode(",", $expenditure_item->document_ids);
							    if (count($this_document_ids) > 0)
							    {
								    $invoice_count = 0;
								    echo "<td align=\"left\">";
								    foreach ($this_document_ids as $this_document_id)
								    {
									    $this_document_nos = array();
									    foreach ($credit_nos as $credit_no)
									    {
										    if ($credit_no->id == $this_document_id)
										    {
											    $invoice_count++;
											    echo "<a href=\"javascript: void(0);\" onclick=\"window.open('index2.php?option=" . NBILL_BRANDING_COMPONENT_NAME . "&action=credits&task=printpreviewpopup&hidemainmenu=1&items=" . $credit_no->id . "', " . nbf_common::nb_time() . ", 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');\">" . $credit_no->document_no . "</a> ";
										    }
									    }
								    }
								    if ($invoice_count == 0)
								    {
									    echo $expenditure_item->for;
								    }
								    echo "</td>";
							    }
							    else
							    {
								    echo "<td align=\"left\">" . $expenditure_item->for . "</td>";
							    }
							    echo "</tr>";
						    }
						    ?>
						    <tr>
							    <td colspan="3" style="font-weight:bold;"><?php echo sprintf(NBILL_TAX_EXCLUDED_EXPENDITURE_TOTAL, count($expenditure)); ?></td>
							    <td align="right" style="font-weight:bold;"><?php echo format_number($expenditure_total, 'currency_grand', true, false, null, $currency); ?></td>
							    <td>&nbsp;</td>
						    </tr>
						    </table>
					    <?php
					    }
				    ?>
			    </table>
            </div>
			<br />

			<?php if ($include_unpaid)
			{ ?>
				<h5 style="margin-bottom:0px;"><?php echo NBILL_WRITE_OFFS_TITLE; ?></h5>
                <div class="rounded-table">
				    <table class="adminlist" cellpadding="3" cellspacing="0">
				    <?php
					    if (count($write_offs) == 0)
					    {
						    echo "<tr><td align=\"left\">" . NBILL_TAX_SUMMARY_EXCLUDED_NO_WO . "</td></tr>";
					    }
					    else
					    {
						    ?>
						    <tr>
							    <th class="title">
								    <?php echo NBILL_TAX_EXCLUDED_NBILL_NO; ?>
							    </th>
							    <th class="title">
								    <?php echo NBILL_TAX_EXCLUDED_DATE; ?>
							    </th>
							    <th class="title">
								    <?php echo NBILL_TAX_EXCLUDED_WO_NAME; ?>
							    </th>
							    <th class="title">
								    <?php echo NBILL_TAX_EXCLUDED_WO_TOTAL; ?>
							    </th>
						    </tr>
							<?php
                            $currency = '';
							foreach($write_offs as $write_off)
							{
                                if (isset($write_off->currency)) {
                                    $currency = $write_off->currency;
                                }
								$link = nbf_cms::$interop->admin_page_prefix . "&action=invoices&task=edit&cid=$write_off->id&return=" . base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=taxsummary$filters");
								echo "<tr>";
								$document_no = $write_off->document_no;
								echo "<td align=\"left\"><a href=\"javascript: void(0);\" onclick=\"window.opener.location='$link';\" title=\"" . NBILL_TAX_EXCLUDED_WO_PREVIEW . "\">" . $document_no . "</a></td>";
								echo "<td align=\"left\">" . nbf_common::nb_date($date_format, $write_off->document_date) . "</td>";
								echo "<td align=\"left\">" . $write_off->billing_name . "</td>";
								echo "<td align=\"right\">" . format_number($write_off->total_gross, 'currency_grand', true, false, null, $currency) . "</td>";
								echo "</tr>";
							}
							?>
							</table>
						<?php
						}
					    ?>
				    </table>
                </div>
			<?php } ?>
		</div>
		<div align="center"><a href="javascript:window.close();"><?php echo NBILL_CLOSE_WINDOW; ?></a></div>
		<?php
	}

    /**
    * If there is already a record for the given type and parent ID (ie. for invoices and credit notes, or single tax rates), add to
    * the existing item, otherwise, add new item if not already present.
    * @param array $breakdown_array
    * @param int $type 1=Income, 2=Invoice, 3=Expenditure, 4=Credit Note
    * @param object $source_record
    * @param mixed $single_tax_rate For invoices/credit notes, this can be "all", "item", or "shipping" to indicate which tax rate we
    * want to record. For income/expenditure, the number of the tax rate we are interested in or "all"
    */
    public static function add_to_breakdown(&$breakdown_array, $type, &$source_record, $single_tax_rate = "all")
    {
        $breakdown_item = new tax_summary_breakdown($type, $source_record, $single_tax_rate);
        switch ($type)
        {
            case 1:
            case 3:
                //Income or expenditure - add to array if not already there
                $item_found = false;
                foreach ($breakdown_array as &$existing_item)
                {
                    if ($existing_item->id == $source_record->id)
                    {
                        $item_found = true;
                        break;
                    }
                }
                if (!$item_found)
                {
                    $breakdown_array[] = $breakdown_item;
                }
                else if ($single_tax_rate != "all")
                {
                    //If tax rate is the same as the existing one, amalgamate, otherwise, add (although should never need to add, as you would not have 2 different rates in the same breakdown)
                    if (float_cmp($existing_item->tax_rate, $breakdown_item->tax_rate))
                    {
                        $existing_item->tax_amount = float_add($existing_item->tax_amount, $breakdown_item->tax_amount, 'currency_grand');
                    }
                    else
                    {
                        $breakdown_array[] = $breakdown_item;
                    }
                }
                break;
            case 2:
            case 4:
                //Invoice or credit note - check primary key and parent
                $item_found = false;
                foreach ($breakdown_array as &$existing_item)
                {
                    if ($existing_item->id == $source_record->id)
                    {
                        $item_found = true;
                        break;
                    }
                }
                if (!$item_found)
                {
                    //Check for parent
                    foreach ($breakdown_array as &$existing_item)
                    {
                        if ($existing_item->parent_id == $source_record->document_id)
                        {
                            $item_found = true;
                            //Add to existing
                            $existing_item->net_amount = float_add($existing_item->net_amount, float_add($source_record->net_price_for_item, $source_record->shipping_for_item, 'currency_grand'), 'currency_grand');
                            switch ($single_tax_rate)
                            {
                                case "item":
                                    $existing_item->tax_amount = float_add($existing_item->tax_amount, $source_record->tax_for_item, 'currency_grand');
                                    break;
                                case "shipping":
                                    $existing_item->tax_amount = float_add($existing_item->tax_amount, $source_record->tax_for_shipping, 'currency_grand');
                                    break;
                                case "all":
                                default:
                                    $existing_item->tax_amount = float_add($existing_item->tax_amount, float_add($source_record->tax_for_item, $source_record->tax_for_shipping, 'currency_grand'), 'currency_grand');
                                    break;
                            }
                            $existing_item->gross_amount = float_add($existing_item->gross_amount, $source_record->gross_price_for_item, 'currency_grand');
                            break;
                        }
                    }
                    if (!$item_found)
                    {
                        $breakdown_array[] = $breakdown_item;
                    }
                }
                break;
        }
    }
}