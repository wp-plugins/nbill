<?php
/**
* HTML output for nominal ledger report
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillLedgerReport
{
	public static function showLedgerReport($vendors, $vendor_name, $currencies, $incomes, $invoice_data, $expenditures, $ledger_codes, $cfg_date_format)
	{
        nbf_cms::$interop->add_html_header('<link rel="stylesheet" href="' . nbf_cms::$interop->nbill_site_url_path . '/style/admin/reports.css" type="text/css" />');
		$tab_started = false;
		nbf_html::load_calendar();
		?>

		<script type="text/javascript">
		function expand(identifier)
		{
			var expanded = document.getElementById('expanded_' + identifier).value;
			if (expanded == "1")
			{
				//Collapse the node
				document.getElementById('img_' + identifier).setAttribute('src', '<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png');
				document.getElementById(identifier).style.display = 'none';
				document.getElementById('expanded_' + identifier).value = 0;
			}
			else
			{
				//Expand the node
				document.getElementById('img_' + identifier).setAttribute('src', '<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png');
				document.getElementById(identifier).style.display = '';
				document.getElementById('expanded_' + identifier).value = 1;
			}
		}
		function expandAll()
		{
			<?php foreach ($currencies as $currency)
			{
				foreach ($ledger_codes as $ledger_code)
				{ ?>
					//Income
					imgElem = document.getElementById('img_<?php echo $currency->code . '_' . $ledger_code->code; ?>');
					if (imgElem != null)
					{
						imgElem.setAttribute('src', '<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png');
						document.getElementById('<?php echo $currency->code . '_' . $ledger_code->code; ?>').style.display = '';
						document.getElementById('expanded_<?php echo $currency->code . '_' . $ledger_code->code; ?>').value = 1;
					}

					//Expenditure
					imgElem = document.getElementById('img_<?php echo $currency->code . '_' . $ledger_code->code; ?>_e');
					if (imgElem != null)
					{
						imgElem.setAttribute('src', '<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png');
						document.getElementById('<?php echo $currency->code . '_' . $ledger_code->code; ?>_e').style.display = '';
						document.getElementById('expanded_<?php echo $currency->code . '_' . $ledger_code->code; ?>_e').value = 1;
					}
				<?php
				}
			}
			?>
		}
		</script>

		<?php
		$printer_friendly = nbf_common::get_param($_POST, 'printer_friendly');

		if ($printer_friendly)
		{
			//Wrap whole lot in a table with cellpadding - only way to get margins to work cross-browser
			echo "<table border=\"0\" cellpadding=\"10\" cellspacing=\"0\"><tr><td>";
		}
		?>
		<table class="adminheading" style="width:100%;">
		<tr>
			<th <?php if ($printer_friendly) {echo "style=\"background-image:none !important; margin-left:0;padding-left:0;\"";} else {echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "ledger_report");} ?>>
				<?php echo NBILL_LEDGER_REPORT_TITLE . " " . NBILL_FOR . " $vendor_name";
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
						<input type="hidden" name="search_date_from" value="<?php echo nbf_common::get_param($_REQUEST, 'search_date_from'); ?>" />
						<input type="hidden" name="search_date_to" value="<?php echo nbf_common::get_param($_REQUEST, 'search_date_to'); ?>" />
						<input type="hidden" name="printer_friendly" value="1" />
						<input type="hidden" name="defined_date_range" value="<?php echo nbf_common::get_param($_REQUEST, 'defined_date_range'); ?>" />
                        <input type="hidden" name="collapsed" value="0" />
						<table cellpadding="5" cellspacing="0" border="0">
							<tr>
								<td valign="middle">
									<a href="#" onclick="adminFormPF.collapsed.value='0';adminFormPF.submit();return false;" target="_blank"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/medium/print.gif" alt="Print" /></a>
								</td>
								<td valign="middle">
									<strong><a href="#" onclick="adminFormPF.collapsed.value='0';adminFormPF.submit();return false;" target="_blank" style="white-space:nowrap;"><?php echo NBILL_LEDGER_REPORT_PF_EXPANDED; ?></a></strong>
								</td>
							</tr>
							<tr>
								<td valign="middle">
									<a href="#" onclick="adminFormPF.collapsed.value='1';adminFormPF.submit();return false;" target="_blank"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/medium/print.gif" alt="Print" /></a>
								</td>
								<td valign="middle">
									<strong><a href="#" onclick="adminFormPF.collapsed.value='1';adminFormPF.submit();return false;" target="_blank" style="white-space:nowrap;"><?php echo NBILL_LEDGER_REPORT_PF_COLLAPSED; ?></a></strong>
								</td>
							</tr>
						</table>
					</form>
				<?php }
				else
				{
					echo "<div style=\"white-space:nowrap\">" . NBILL_DATE_PRINTED . " " . nbf_common::nb_date($cfg_date_format, nbf_common::nb_time()) . "</div>";
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
			<p align="left"><?php echo NBILL_LEDGER_REPORT_INTRO; ?></p>
		<?php } ?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="ledger_report" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

		<?php
		$exclude_filter = array();
		$exclude_filter[] = "search_date_from";
		$exclude_filter[] = "search_date_to";
	 	nbf_html::add_filters($exclude_filter);

		if ($printer_friendly)
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
                    echo "&nbsp;&nbsp;";
				}
				else
				{
					echo "<input type=\"hidden\" name=\"vendor_filter\" id=\"vendor_filter\" value=\"" . $vendors[0]->id . "\" />";
					$_POST['vendor_filter'] = $vendors[0]->id;
				}
                nbf_html::show_defined_date_ranges();
                ?>
                <div id="date_range_controls" style="display:<?php echo nbf_common::get_param($_REQUEST,'defined_date_range') == 'specified_range' ? 'block' : 'none'; ?>;">
				    <span style="white-space:nowrap"><?php echo NBILL_DATE_RANGE; $cal_date_format = nbf_common::get_date_format(true); ?>
				    <input type="text" name="search_date_from" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_from'); ?>" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> />
				    <input type="button" name="search_date_from_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.search_date_from,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> /></span>
				    <span style="white-space:nowrap"><?php echo NBILL_TO; ?>
				    <input type="text" name="search_date_to" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_to'); ?>" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> />
				    <input type="button" name="search_date_to_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.search_date_to,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> /></span>
				    <input type="submit" class="button btn" name="dosearch" value="<?php echo NBILL_GO; ?>" />
                </div>
			</div>

			<?php if (!$printer_friendly)
			{ ?>
				<div align="left" style="clear:both;text-align:left;margin-bottom:5px;"><input type="button" class="button btn" value="<?php echo NBILL_LEDGER_REPORT_EXPAND_ALL; ?>" onclick="expandAll();" /></div>
				<?php
			}
			//Only start tab if there is something to display (otherwise JS error in J1.5)
			foreach ($currencies as $currency)
			{
				//Make sure there is something to display
				$data_present = false;
				foreach ($incomes[$currency->code] as $ledger_incomes)
				{
					if (count($ledger_incomes) > 0)
					{
                        $data_present = true;
                        if (!$tab_started)
                        {
                        	$nbf_tab_ledger = new nbf_tab_group();
                            $nbf_tab_ledger->start_tab_group("ledger");
                        }
						$tab_started = true;
                        break;
					}
				}
				if (!$data_present)
				{
					foreach ($expenditures[$currency->code] as $ledger_expenditures)
					{
						if (count($ledger_expenditures) > 0)
						{
	                        $data_present = true;
	                        if (!$tab_started)
	                        {
                        		$nbf_tab_ledger = new nbf_tab_group();
	                            $nbf_tab_ledger->start_tab_group("ledger");
	                        }
							$tab_started = true;
	                        break;
						}
					}
				}
				if ($data_present)
                {
                    $nbf_tab_ledger->add_tab_title($currency->code, $currency->code);
                }
			}
		}

		foreach ($currencies as $currency)
		{
			//Make sure there is something to display
			$data_present = false;
			foreach ($incomes[$currency->code] as $ledger_incomes)
			{
				if (count($ledger_incomes) > 0)
				{
					$data_present = true;
					break;
				}
			}
            if (!$data_present)
            {
                foreach ($expenditures[$currency->code] as $ledger_expenditures)
                {
                    if (count($ledger_expenditures) > 0)
                    {
                        $data_present = true;
                        break;
                    }
                }
            }
			if ($data_present)
			{
				if ($printer_friendly)
				{
					echo "<div class=\"adminheader\" style=\"margin-top:20px;\">" . $currency->code . "</div>";
				}
				else
				{
					ob_start();
				}
				?>
				<div align="center" class="componentheading" style="text-align:center"><?php echo NBILL_LEDGER_REPORT_INCOME; ?></div>
				<table class="nbill-ledger-report-table">
					<tr>
						<th colspan="2" class="fill-column<?php if ($printer_friendly) {echo " print";} ?>">
							<?php echo NBILL_LEDGER_REPORT_NOMINAL_CODE; ?>&nbsp;&nbsp;
						</th>
						<th class="report-total<?php if ($printer_friendly) {echo " print";} ?> responsive-cell optional">
							<?php echo NBILL_LEDGER_REPORT_NET_AMOUNT; ?>&nbsp;&nbsp;
						</th>
                        <th class="report-total<?php if ($printer_friendly) {echo " print";} ?> responsive-cell optional">
                            <?php echo NBILL_LEDGER_REPORT_TAX_AMOUNT; ?>&nbsp;&nbsp;
                        </th>
                        <th class="report-total<?php if ($printer_friendly) {echo " print";} ?>">
                            <?php echo NBILL_LEDGER_REPORT_GROSS_AMOUNT; ?>&nbsp;&nbsp;
                        </th>
						<th class="report-total<?php if ($printer_friendly) {echo " print";} ?> responsive-cell high-priority">
							<?php echo NBILL_LEDGER_REPORT_PERCENTAGE; ?>
						</th>
					</tr>

					<?php
					$return_url = base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=ledger_report&task=view&search_date_from=" . nbf_common::get_param($_REQUEST, 'search_date_from') . "&search_date_to=" . nbf_common::get_param($_REQUEST, 'search_date_to') . "&vendor_filter=" . nbf_common::get_param($_REQUEST, 'vendor_filter'));

					$income_net_total = 0;
                    $income_tax_total = 0;
                    $income_gross_total = 0;
                    $transaction_count = 0;
                    foreach ($ledger_codes as $ledger_code)
					{
						foreach ($incomes[$currency->code][$ledger_code->code] as $income_item)
						{
							$income_net_total = float_add($income_net_total, $income_item->net_amount, 'currency_grand');
                            $income_tax_total = float_add($income_tax_total, $income_item->tax_amount, 'currency_grand');
                            $income_gross_total = float_add($income_gross_total, $income_item->gross_amount, 'currency_grand');
						}
					}

					foreach ($ledger_codes as $ledger_code)
					{
						$identifier = $currency->code . "_" . $ledger_code->code;
						if (count($incomes[$currency->code][$ledger_code->code]) > 0)
						{
							?>
							<tr>
								<td class="expander-icon">
									<?php if (!$printer_friendly && count($incomes[$currency->code][$ledger_code->code]) > 0)
									{
										?><a href="#" onclick="expand('<?php echo $identifier; ?>');return false;"><img border="0" id="img_<?php echo $identifier; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png" alt="Expand/Collapse" /></a><?php
									}
									else
									{
										if (!($printer_friendly && nbf_common::get_param($_POST, 'collapsed')))
										{
											?><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png" alt="" /><?php
										}
									}
									?>
									<input type="hidden" name="expanded_<?php echo $identifier; ?>" id="expanded_<?php echo $identifier; ?>" value="0" />
								</td>
								<?php
									$ledger_net_amount = 0;
                                    $ledger_tax_amount = 0;
                                    $ledger_gross_amount = 0;
                                    $tx_count = 0;
									foreach ($incomes[$currency->code][$ledger_code->code] as $income_item)
									{
										$ledger_net_amount = float_add($ledger_net_amount, $income_item->net_amount, 'currency_line');
                                        $ledger_tax_amount = float_add($ledger_tax_amount, $income_item->tax_amount, 'currency_line');
                                        $ledger_gross_amount = float_add($ledger_gross_amount, $income_item->gross_amount, 'currency_line');
                                        $tx_count++;
									}
								?>
								<td class="fill-column"><?php
									echo $ledger_code->code . " - " . $ledger_code->description . " " . sprintf(NBILL_LEDGER_TX_COUNT, $tx_count); ?>
								</td>
								<td class="report-amount responsive-cell optional">
                                    <?php echo format_number($ledger_net_amount, 'currency_line', true, null, null, $currency->code); ?>
								</td>
                                <td class="report-amount responsive-cell optional">
                                    <?php echo format_number($ledger_tax_amount, 'currency_line', true, null, null, $currency->code); ?>
                                </td>
                                <td class="report-amount">
                                    <?php echo format_number($ledger_gross_amount, 'currency_line', true, null, null, $currency->code); ?>
                                </td>
								<td class="report-amount responsive-cell high-priority">
									<?php
										echo format_number(($income_net_total > 0 ? (($ledger_net_amount / $income_net_total) * 100) : "0.00")) . "%";
									?>
								</td>
							</tr>

							<tr id="<?php echo $identifier; ?>" <?php if (!$printer_friendly || nbf_common::get_param($_POST, 'collapsed')) { echo "style=\"display:none;\""; } ?>>
								<td colspan="4">
									<table cellpadding="0" cellspacing="0" border="0" class="collapsible">
										<tr>
											<th class="expanded-branch"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/branch-line.png" alt="" /></th>
											<th<?php if ($printer_friendly) {echo " class=\"print\"";} ?>>
												<?php echo NBILL_LEDGER_REPORT_DATE; ?>&nbsp;
											</th>
											<th<?php if ($printer_friendly) {echo " class=\"print\"";} ?>>
												<?php echo NBILL_LEDGER_REPORT_RECEIPT_NO; ?>&nbsp;
											</th>
											<th<?php if ($printer_friendly) {echo " class=\"print\"";} ?>>
												<?php echo NBILL_LEDGER_REPORT_FROM; ?>&nbsp;
											</th>
											<th<?php if ($printer_friendly) {echo " class=\"print\"";} ?>>
												<?php echo NBILL_LEDGER_REPORT_FOR; ?>&nbsp;
											</th>
											<th class="report-amount<?php if ($printer_friendly) {echo " print";} ?> responsive-cell optional">
												<?php echo NBILL_LEDGER_REPORT_NET_AMOUNT; ?>
											</th>
                                            <th class="report-amount<?php if ($printer_friendly) {echo " print";} ?> responsive-cell optional">
                                                <?php echo NBILL_LEDGER_REPORT_TAX_AMOUNT; ?>
                                            </th>
                                            <th class="report-amount<?php if ($printer_friendly) {echo " print";} ?>">
                                                <?php echo NBILL_LEDGER_REPORT_GROSS_AMOUNT; ?>
                                            </th>
										</tr>
										<?php
										$rowcount = 0;
										foreach ($incomes[$currency->code][$ledger_code->code] as $income_item)
										{
											$rowcount++;
											$branch_img = nbf_cms::$interop->nbill_site_url_path . "/images/branch.png";
											if ($rowcount == count($incomes[$currency->code][$ledger_code->code]))
											{
												$branch_img = nbf_cms::$interop->nbill_site_url_path . "/images/end-branch.png";
											}
											?>
											<tr style="padding:0px;border:0px;">
												<td class="expanded-branch">
													<img src="<?php echo $branch_img; ?>" alt="" />
												</td>
												<td><?php echo nbf_common::nb_date($cfg_date_format, $income_item->date); ?></td>
												<td><?php
													$link = nbf_cms::$interop->admin_page_prefix . "&action=income&task=edit&cid=$income_item->id&return=$return_url";
													$text = nbf_common::nb_strlen($income_item->transaction_no) > 0 ? $income_item->transaction_no : NBILL_LEDGER_REPORT_AWAITING_RCT_NO;
													if (!$printer_friendly)
													{
														echo "<a href=\"$link\">$text</a>";
													}
													else
													{
														echo $text;
													}
													?>
												</td>
												<td class="word-breakable"><?php
													$text = $income_item->name;
													$document_ids = explode(",", $income_item->document_ids);
													//If this relates to an invoice, link to the client
													$client_id = 0;
													if (!$printer_friendly)
													{
														//Find client id, if known
														foreach ($invoice_data[$currency->code] as $invoice)
														{
															if (array_search($invoice->id, $document_ids) !== false)
															{
																$client_id = $invoice->entity_id;
																break;
															}
														}
													}
													if ($client_id > 0)
													{
														echo "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=clients&task=edit&cid=$client_id&return=$return_url\">$text</a>";
													}
													else
													{
														echo $text;
													}
												?></td>
												<td class="word-breakable"<?php if ($printer_friendly){echo ' style="width:250px;"';} ?>><?php
													$for = "";
													if (nbf_common::nb_strlen(trim($income_item->for)) > 0)
													{
														$for .= $income_item->for;
													}
													if (count($document_ids) > 0)
													{
														$count_document_nos = 0;
														foreach ($invoice_data[$currency->code] as $invoice)
														{
															if (array_search($invoice->id, $document_ids) !== false)
															{
																if ($count_document_nos == 0)
																{
																	if (nbf_common::nb_strlen($for) > 0)
																	{
																		$for .= " - ";
																	}
																	$for .= NBILL_LEDGER_REPORT_INVOICE . " ";
																	if (nbf_common::nb_strlen($income_item->for) > 0)
																	{
																		$for .= "(";
																	}
																}
																else
																{
																	$for .= ", ";
																}
																$count_document_nos++;
																if ($printer_friendly)
																{
																	$for .= '<span style="white-space:nowrap;">' . $invoice->document_no . '</span>';;
																}
																else
																{
																	$for .= "<a href=\"#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=invoices&task=printpreviewpopup&hidemainmenu=1&items=$invoice->id', '" . uniqid() . "', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');\" title=\"Preview\">" . $invoice->document_no . "</a>";
																}
															}
														}
														if (nbf_common::nb_strlen($income_item->for) > 0 && $count_document_nos > 0)
														{
															$for .= ")";
														}
													}
													if (nbf_common::nb_strlen($for) > 0)
													{
														echo $for;
													}
													else
													{
														echo "&nbsp;";
													}
												?></td>
												<td class="report-amount responsive-cell optional"><?php echo format_number($income_item->net_amount, 'currency_line', true, null, null, $currency->code); ?></td>
                                                <td class="report-amount responsive-cell optional"><?php echo format_number($income_item->tax_amount, 'currency_line', true, null, null, $currency->code); ?></td>
                                                <td class="report-amount"><?php echo format_number($income_item->gross_amount, 'currency_line', true, null, null, $currency->code); ?></td>
												</td>
											</tr>
											<?php
										}
										$transaction_count += $rowcount;
										?>
										<tr>
											<td>&nbsp;</td>
											<td colspan="4" style="font-weight:bold"><?php echo sprintf(NBILL_LEDGER_REPORT_TOTAL, $ledger_code->code . " - " . $ledger_code->description, $rowcount); ?></td>
											<td class="report-total responsive-cell optional"><?php echo format_number($ledger_net_amount, 'currency_line', true, null, null, $currency->code); ?></td>
                                            <td class="report-total responsive-cell optional"><?php echo format_number($ledger_tax_amount, 'currency_line', true, null, null, $currency->code); ?></td>
                                            <td class="report-total"><?php echo format_number($ledger_gross_amount, 'currency_line', true, null, null, $currency->code); ?></td>
										</tr>
									</table>
								</td>
							</tr>
						<?php }
					} ?>
					<tr style="background-color:#efefef;">
						<td colspan="2"><?php echo sprintf(NBILL_LEDGER_REPORT_TOTAL_INCOME, $transaction_count); ?></td>
						<td class="report-total responsive-cell optional">
							<?php echo format_number($income_net_total, 'currency_grand', true, null, null, $currency->code); ?>
						</td>
                        <td class="report-total responsive-cell optional">
                            <?php echo format_number($income_tax_total, 'currency_grand', true, null, null, $currency->code); ?>
                        </td>
                        <td class="report-total">
                            <?php echo format_number($income_gross_total, 'currency_grand', true, null, null, $currency->code); ?>
                        </td>
						<td class="report-total responsive-cell high-priority">
							<?php echo format_number(100.00) . "%"; ?>
						</td>
					</tr>
				</table>

				<br />

				<div class="componentheading" style="text-align:center"><?php echo NBILL_LEDGER_REPORT_EXPENDITURE; ?></div>
				<table class="nbill-ledger-report-table">
					<tr>
						<th colspan="2" class="fill-column<?php if ($printer_friendly) {echo " print";} ?>">
							<?php echo NBILL_LEDGER_REPORT_NOMINAL_CODE; ?>&nbsp;&nbsp;
						</th>
						<th class="report-total<?php if ($printer_friendly) {echo " print";} ?> responsive-cell optional">
							<?php echo NBILL_LEDGER_REPORT_NET_AMOUNT; ?>&nbsp;&nbsp;
						</th>
                        <th class="report-total<?php if ($printer_friendly) {echo " print";} ?> responsive-cell optional">
                            <?php echo NBILL_LEDGER_REPORT_TAX_AMOUNT; ?>&nbsp;&nbsp;
                        </th>
                        <th class="report-total<?php if ($printer_friendly) {echo " print";} ?>">
                            <?php echo NBILL_LEDGER_REPORT_GROSS_AMOUNT; ?>&nbsp;&nbsp;
                        </th>
						<th class="report-total<?php if ($printer_friendly) {echo " print";} ?> responsive-cell high-priority">
							<?php echo NBILL_LEDGER_REPORT_PERCENTAGE; ?>
						</th>
					</tr>

					<?php
					//Need total expenditure so we can work out percentage
					$expenditure_net_total = 0;
                    $expenditure_tax_total = 0;
                    $expenditure_gross_total = 0;
                    $transaction_count = 0;
					foreach ($ledger_codes as $ledger_code)
					{
						foreach ($expenditures[$currency->code][$ledger_code->code] as $expenditure_item)
						{
							$expenditure_net_total = float_add($expenditure_net_total, $expenditure_item->net_amount, 'currency_grand');
                            $expenditure_tax_total = float_add($expenditure_tax_total, $expenditure_item->tax_amount, 'currency_grand');
                            $expenditure_gross_total = float_add($expenditure_gross_total, $expenditure_item->gross_amount, 'currency_grand');
						}
					}

					foreach ($ledger_codes as $ledger_code)
					{
						$identifier = $currency->code . "_" . $ledger_code->code . '_e';
						if (count($expenditures[$currency->code][$ledger_code->code]) > 0)
						{
							?>
							<tr>
								<td class="expander-icon">
									<?php if (!$printer_friendly && count($expenditures[$currency->code][$ledger_code->code]) > 0)
									{
										?><a href="#" onclick="expand('<?php echo $identifier; ?>');return false;"><img border="0" id="img_<?php echo $identifier; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/plus.png" alt="Expand/Collapse" /></a><?php
									}
									else
									{
										if (!($printer_friendly && nbf_common::get_param($_POST, 'collapsed')))
										{
											?><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/minus.png" alt="" /><?php
										}
									}
									?>
									<input type="hidden" name="expanded_<?php echo $identifier; ?>" id="expanded_<?php echo $identifier; ?>" value="0" />
								</td>
								<?php
									$ledger_net_amount = 0;
                                    $ledger_tax_amount = 0;
                                    $ledger_gross_amount = 0;
                                    $tx_count = 0;
									foreach ($expenditures[$currency->code][$ledger_code->code] as $expenditure_item)
									{
										$ledger_net_amount = float_add($ledger_net_amount, $expenditure_item->net_amount, 'currency_line');
                                        $ledger_tax_amount = float_add($ledger_tax_amount, $expenditure_item->tax_amount, 'currency_line');
                                        $ledger_gross_amount = float_add($ledger_gross_amount, $expenditure_item->gross_amount, 'currency_line');
                                        $tx_count++;
									}
								?>
                                <td class="fill-column"><?php
									echo $ledger_code->code . " - " . $ledger_code->description . " " . sprintf(NBILL_LEDGER_TX_COUNT, $tx_count); ; ?>
								</td>
								<td class="report-amount responsive-cell optional">
                                    <?php echo format_number($ledger_net_amount, 'currency_line', true, null, null, $currency->code); ?>
                                </td>
                                <td class="report-amount responsive-cell optional">
                                    <?php echo format_number($ledger_tax_amount, 'currency_line', true, null, null, $currency->code); ?>
                                </td>
                                <td class="report-amount">
                                    <?php echo format_number($ledger_gross_amount, 'currency_line', true, null, null, $currency->code); ?>
                                </td>
								<td class="report-amount responsive-cell high-priority">
									<?php
										echo format_number(@($ledger_net_amount / $expenditure_net_total) * 100) . "%";
									?>
								</td>
							</tr>

							<tr id="<?php echo $identifier; ?>" <?php if (!$printer_friendly || nbf_common::get_param($_POST, 'collapsed')) { echo "style=\"display:none;\""; } ?>>
								<td colspan="4">
									<table cellpadding="0" cellspacing="0" border="0" class="collapsible">
										<tr>
											<th class="expanded-branch"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/branch-line.png" alt="" /></th>
											<th <?php if ($printer_friendly) {echo "class=\"print\"";} ?>>
												<?php echo NBILL_LEDGER_REPORT_DATE; ?>&nbsp;
											</th>
											<th <?php if ($printer_friendly) {echo "class=\"print\"";} ?>>
												<?php echo NBILL_LEDGER_REPORT_PAYMENT_NO; ?>&nbsp;
											</th>
											<th <?php if ($printer_friendly) {echo "class=\"print\"";} ?>>
												<?php echo NBILL_LEDGER_REPORT_PAID_TO; ?>&nbsp;
											</th>
											<th <?php if ($printer_friendly) {echo "class=\"print\"";} ?>>
												<?php echo NBILL_LEDGER_REPORT_FOR; ?>&nbsp;
											</th>
											<th class="report-total<?php if ($printer_friendly) {echo " print";} ?> responsive-cell optional">
												<?php echo NBILL_LEDGER_REPORT_NET_AMOUNT; ?>
											</th>
                                            <th class="report-total<?php if ($printer_friendly) {echo " print";} ?> responsive-cell optional">
                                                <?php echo NBILL_LEDGER_REPORT_TAX_AMOUNT; ?>
                                            </th>
                                            <th class="report-total<?php if ($printer_friendly) {echo " print";} ?>">
                                                <?php echo NBILL_LEDGER_REPORT_GROSS_AMOUNT; ?>
                                            </th>
										</tr>
										<?php
										$rowcount = 0;
										foreach ($expenditures[$currency->code][$ledger_code->code] as $expenditure_item)
										{
											$rowcount++;
											$branch_img = nbf_cms::$interop->nbill_site_url_path . "/images/branch.png";
											if ($rowcount == count($expenditures[$currency->code][$ledger_code->code]))
											{
												$branch_img = nbf_cms::$interop->nbill_site_url_path . "/images/end-branch.png";
											}
											?>
											<tr style="padding:0px;border:0px;">
												<td class="expanded-branch">
													<img src="<?php echo $branch_img; ?>" alt="" />
												</td>
												<td><?php echo nbf_common::nb_date($cfg_date_format, $expenditure_item->date); ?></td>
												<td><?php
													$link = nbf_cms::$interop->admin_page_prefix . "&action=expenditure&task=edit&cid=$expenditure_item->id&return=$return_url";
													$text = nbf_common::nb_strlen($expenditure_item->transaction_no) > 0 ? $expenditure_item->transaction_no : NBILL_LEDGER_REPORT_AWAITING_PYT_NO;
													if (!$printer_friendly)
													{
														echo "<a href=\"$link\">$text</a>";
													}
													else
													{
														echo $text;
													}
													?>
												</td>
												<td class="word-breakable"><?php
													$text = $expenditure_item->name;
													$document_ids = explode(",", $expenditure_item->document_ids);
													if ($expenditure_item->entity_id > 0 && !$printer_friendly)
													{
														echo "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=suppliers&task=edit&cid=$expenditure_item->entity_id&return=$return_url\">$text</a>";
													}
													else
													{
														//If this relates to an invoice or credit note, link to the client
														$client_id = 0;
														if (!$printer_friendly)
														{
															//Find client id, if known
															foreach ($invoice_data[$currency->code] as $invoice)
															{
																if (array_search($invoice->id, $document_ids) !== false)
																{
																	$client_id = $invoice->entity_id;
																	break;
																}
															}
														}
														if ($client_id > 0)
														{
															echo "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=clients&task=edit&cid=$client_id&return=$return_url\">$text</a> (" . NBILL_LEDGER_REPORT_CLIENT_REFUND . ")";
														}
														else
														{
															echo $text;
														}
													}
												?></td>
												<td class="word-breakable"<?php if ($printer_friendly) {echo ' style="width:250px;"';} ?>><?php
													$for = "";
													if (nbf_common::nb_strlen(trim($expenditure_item->for)) > 0)
													{
														$for .= $expenditure_item->for;
													}
													if (count($document_ids) > 0)
													{
														$count_document_nos = 0;
														foreach ($invoice_data[$currency->code] as $invoice)
														{
															if (array_search($invoice->id, $document_ids) !== false)
															{
																if ($count_document_nos == 0)
																{
																	if (nbf_common::nb_strlen($for) > 0)
																	{
																		$for .= " - ";
																	}
																	$for .= NBILL_LEDGER_REPORT_CREDIT_NOTE . " ";
																	if (nbf_common::nb_strlen($expenditure_item->for) > 0)
																	{
																		$for .= " (";
																	}
																}
																else
																{
																	$for .= ", ";
																}
																$count_document_nos++;
																if ($printer_friendly)
																{
																	$for .= '<span style="white-space:nowrap;">' . $invoice->document_no . '</span>';
																}
																else
																{
																	$for .= "<a href=\"#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=invoices&task=printpreviewpopup&hidemainmenu=1&items=$invoice->id', '" . uniqid() . "', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');\" title=\"Preview\">" . $invoice->document_no . "</a>";
																}
															}
														}
														if (nbf_common::nb_strlen($expenditure_item->for) > 0 && $count_document_nos > 0)
														{
															$for .= ")";
														}
													}
													if (nbf_common::nb_strlen($for) > 0)
													{
														echo $for;
													}
													else
													{
														echo "&nbsp;";
													}
												?></td>
												<td class="report-amount responsive-cell optional"><?php echo format_number($expenditure_item->net_amount, 'currency_line', true, null, null, $currency->code); ?></td>
                                                <td class="report-amount responsive-cell optional"><?php echo format_number($expenditure_item->tax_amount, 'currency_line', true, null, null, $currency->code); ?></td>
                                                <td class="report-amount"><?php echo format_number($expenditure_item->gross_amount, 'currency_line', true, null, null, $currency->code); ?></td>
												</td>
											</tr>
											<?php
										}
										$transaction_count += $rowcount;
										?>
										<tr>
											<td>&nbsp;</td>
											<td colspan="4" style="font-weight:bold"><?php echo sprintf(NBILL_LEDGER_REPORT_TOTAL, $ledger_code->code . " - " . $ledger_code->description, $rowcount); ?></td>
											<td class="report-total responsive-cell optional"><?php echo format_number($ledger_net_amount, 'currency_line', true, null, null, $currency->code); ?></td>
                                            <td class="report-total responsive-cell optional"><?php echo format_number($ledger_tax_amount, 'currency_line', true, null, null, $currency->code); ?></td>
                                            <td class="report-total"><?php echo format_number($ledger_gross_amount, 'currency_line', true, null, null, $currency->code); ?></td>
										</tr>
									</table>
								</td>
							</tr>
						<?php }
					} ?>
					<tr style="background-color:#efefef;">
						<td colspan="2"><?php echo sprintf(NBILL_LEDGER_REPORT_TOTAL_EXPENDITURE, $transaction_count); ?></td>
						<td class="report-total responsive-cell optional">
							<?php echo format_number($expenditure_net_total, 'currency_grand', true, null, null, $currency->code); ?>
						</td>
                        <td class="report-total responsive-cell optional">
                            <?php echo format_number($expenditure_tax_total, 'currency_grand', true, null, null, $currency->code); ?>
                        </td>
                        <td class="report-total">
                            <?php echo format_number($expenditure_gross_total, 'currency_grand', true, null, null, $currency->code); ?>
                        </td>
						<td class="report-total responsive-cell high-priority">
							<?php echo format_number(100.00) . "%"; ?>
						</td>
					</tr>
				</table>

				<?php
				if (!$printer_friendly)
				{
					$nbf_tab_ledger->add_tab_content($currency->code, ob_get_clean());
				}
			}
		}
		if (!$printer_friendly && $tab_started)
		{
			$nbf_tab_ledger->end_tab_group();
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
}