<?php
/**
* HTML output for transaction report
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillTransactions
{
	public static function showTransactionReport($vendors, $vendor_name, $currencies, $transactions, $tx_ledger, $document_data, $cfg_date_format, $csv = false)
	{
        nbf_cms::$interop->add_html_header('<link rel="stylesheet" href="' . nbf_cms::$interop->nbill_site_url_path . '/style/admin/reports.css" type="text/css" />');
        $printer_friendly = nbf_common::get_param($_POST, 'printer_friendly');
        $tab_started = false;
		if ($printer_friendly)
		{
			$csv = false; //Can't be both!
		}

		if (!$csv)
		{
			if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
			{
				echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
			}
			nbf_html::load_calendar();
		}
		$exclude_filter = array();
		$exclude_filter[] = "search_date_from";
		$exclude_filter[] = "search_date_to";
		if (!$csv)
		{
			nbf_html::add_filters($exclude_filter);
		}
		if ($printer_friendly)
		{
			//Wrap whole lot in a table with cellpadding - only way to get margins to work cross-browser
			echo "<table border=\"0\" cellpadding=\"10\" cellspacing=\"0\"><tr><td>";
		}

		if (!$csv)
		{
			?>
			<table class="adminheading" style="width:100%">
			<tr>
				<th <?php if ($printer_friendly) {echo "style=\"background-image:none !important; margin-left:0;padding-left:0;\"";} else {echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "transactions");} ?>>
					<?php echo NBILL_TRANSACTIONS_TITLE . " " . NBILL_FOR . " $vendor_name"; ?>
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
                            <input type="hidden" name="defined_date_range" value="<?php echo nbf_common::get_param($_REQUEST, 'defined_date_range'); ?>" />
							<input type="hidden" name="printer_friendly" value="1" />
							<table cellpadding="5" cellspacing="0" border="0">
								<tr>
									<td valign="middle">
										<a href="#" onclick="adminFormPF.submit();return false;" target="_blank"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/medium/print.gif" alt="Print" /></a>
									</td>
									<td valign="middle">
										<strong><a href="#" onclick="adminFormPF.submit();return false;" target="_blank" style="white-space:nowrap;"><?php echo NBILL_PRINTER_FRIENDLY; ?></a></strong>
									</td>
								</tr>
							</table>
						</form>
						<table cellpadding="5" cellspacing="0" border="0">
							<tr>
                                <?php $csv_url = nbf_cms::$interop->admin_page_prefix . '&action=transactions&task=csv&vendor_id=' . nbf_common::get_param($_REQUEST, 'vendor_filter') . '&defined_date_range=' . nbf_common::get_param($_REQUEST, 'defined_date_range') . '&search_date_from=' . nbf_common::get_param($_REQUEST, 'search_date_from') . '&search_date_to=' . nbf_common::get_param($_REQUEST, 'search_date_to'); ?>
								<td valign="middle">
									<a href="<?php echo $csv_url; ?>" title="<?php echo NBILL_CSV_DOWNLOAD_DESC; ?>"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path ?>/images/icons/medium/csv.gif" alt="<?php echo NBILL_CSV_DOWNLOAD_DESC; ?>" /></a>
								</td>
								<td valign="middle">
									<strong><a href="<?php echo $csv_url; ?>" title="<?php echo NBILL_CSV_DOWNLOAD_DESC; ?>"><?php echo NBILL_CSV_DOWNLOAD; ?></a></strong>
								</td>
							</tr>
						</table>
					<?php }
					else
					{
						echo "<div style=\"white-space:nowrap\">" . NBILL_DATE_PRINTED . " " . nbf_common::nb_date($cfg_date_format, nbf_common::nb_time()) . "</div>";
					}?>
				</td>
			</tr>
			</table>

			<?php if (!$printer_friendly) { ?>
				<p align="left"><?php echo NBILL_TRANSACTIONS_INTRO; ?></p>
			<?php } ?>

			<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
            <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
            <input type="hidden" name="action" value="transactions" />
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
                        echo "&nbsp;&nbsp;";
						$_POST['vendor_filter'] = $selected_filter;
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
                <?php
			}
		}

        if (!$printer_friendly && !$csv)
        {
            //Add a bit of buffer space between filters and results
            echo "<br />";
        }

        if (!$printer_friendly && !$csv)
        {
            foreach ($currencies as $currency)
            {
                if ((isset($transactions[$currency->code]) && count($transactions[$currency->code]) > 0) || (isset($expenditures[$currency->code]) && count($expenditures[$currency->code]) > 0))
                {
                    if (!$tab_started)
                    {
                    	$nbf_tab_txs = new nbf_tab_group();
                        $nbf_tab_txs->start_tab_group("transactions");
                    }
                    $tab_started = true;
                    $nbf_tab_txs->add_tab_title($currency->code, $currency->code);
                }
            }
        }

        foreach ($currencies as $currency)
		{
			//Make sure there is something to display
			if ((isset($transactions[$currency->code]) && count($transactions[$currency->code]) > 0))
			{
				if ($printer_friendly)
				{
					echo "<div class=\"adminheader\" style=\"margin-top:20px;\">" . $currency->code . "</div>";
				}
				else if (!$csv)
				{
                    ob_start();
				}

				if ($csv)
				{
                    echo NBILL_TR_DATE . ",";
					echo NBILL_TR_ITEM_NO . ",";
					echo NBILL_TR_FROM_TO . ",";
					echo NBILL_TR_FOR . ",";
					echo NBILL_TR_LEDGER . ",";
                    echo NBILL_TR_NET_INCOME . ",";
                    echo NBILL_TR_TAX_INCOME . ",";
					echo NBILL_TR_INCOME . ",";
                    echo NBILL_TR_NET_EXPENDITURE . ",";
                    echo NBILL_TR_TAX_EXPENDITURE . ",";
					echo NBILL_TR_EXPENDITURE . ",";
					echo NBILL_TR_BALANCE . "\n";
				}
				else
				{
					?>
                    <div class="rounded-table">
					<table class="adminlist">
						<tr>
							<th class="title" <?php if ($printer_friendly) {echo "style=\"background-image:none;background-color:#dedede;\"";} ?>>
								<?php echo NBILL_TR_DATE; ?>
							</th>
							<th class="title" <?php if ($printer_friendly) {echo "style=\"background-image:none;background-color:#dedede;\"";} ?>>
								<?php echo NBILL_TR_ITEM_NO; ?>
							</th>
							<th class="title responsive-cell optional" <?php if ($printer_friendly) {echo "style=\"background-image:none;background-color:#dedede;\"";} ?>>
								<?php echo NBILL_TR_FROM_TO; ?>
							</th>
							<th class="title responsive-cell extra-wide-only" <?php if ($printer_friendly) {echo "style=\"background-image:none;background-color:#dedede;\"";} ?>>
								<?php echo NBILL_TR_FOR; ?>
							</th>
							<th class="title responsive-cell extra-extra-wide-only" <?php if ($printer_friendly) {echo "style=\"background-image:none;background-color:#dedede;\"";} ?>>
								<?php echo NBILL_TR_LEDGER; ?>
							</th>
                            <th class="title report-amount responsive-cell wide-only" <?php if ($printer_friendly) {echo "style=\"background-image:none;background-color:#dedede;\"";} ?>>
                                <?php echo NBILL_TR_NET_INCOME; ?>
                            </th>
                            <th class="title report-amount responsive-cell wide-only" <?php if ($printer_friendly) {echo "style=\"background-image:none;background-color:#dedede;\"";} ?>>
                                <?php echo NBILL_TR_TAX_INCOME; ?>
                            </th>
							<th class="title report-amount" <?php if ($printer_friendly) {echo "style=\"background-image:none;background-color:#dedede;\"";} ?>>
								<?php echo NBILL_TR_INCOME; ?>
							</th>
							<th class="title report-amount responsive-cell wide-only" <?php if ($printer_friendly) {echo "style=\"background-image:none;background-color:#dedede;\"";} ?>>
                                <?php echo NBILL_TR_NET_EXPENDITURE; ?>
                            </th>
                            <th class="title report-amount responsive-cell wide-only" <?php if ($printer_friendly) {echo "style=\"background-image:none;background-color:#dedede;\"";} ?>>
                                <?php echo NBILL_TR_TAX_EXPENDITURE; ?>
                            </th>
                            <th class="title report-amount" <?php if ($printer_friendly) {echo "style=\"background-image:none;background-color:#dedede;\"";} ?>>
                                <?php echo NBILL_TR_EXPENDITURE; ?>
                            </th>
                            <th class="title report-amount" <?php if ($printer_friendly) {echo "style=\"background-image:none;background-color:#dedede;\"";} ?>>
								<?php echo NBILL_TR_BALANCE; ?>
							</th>
						</tr>

						<?php
				}

				$return_url = base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=transactions&task=view&search_date_from=" . nbf_common::get_param($_REQUEST, 'search_date_from') . "&search_date_to=" . nbf_common::get_param($_REQUEST, 'search_date_to') . "&vendor_filter=" . nbf_common::get_param($_REQUEST, 'vendor_filter'));

				//Add transactions
				$inc_total_net = 0;
				$inc_total_tax = 0;
				$inc_total_gross = 0;

				$total_income = 0;
				$total_expenditure = 0;
                $total_net_income = 0;
                $total_net_expenditure = 0;
                $total_tax_income = 0;
                $total_tax_expenditure = 0;
				$is_income = false;
				$transaction_count = 0;

                foreach ($transactions[$currency->code] as $transaction)
				{
					$transaction_count++;
                    $is_income = $transaction->transaction_type == "IN";
					if ($csv)
					{
						echo nbf_common::nb_date($cfg_date_format, $transaction->date) . ",";
						if ($is_income)
						{
							echo nbf_common::nb_strlen($transaction->transaction_no) > 0 ? NBILL_TR_RECEIPT . " " . $transaction->transaction_no : NBILL_TR_AWAITING_RCT_NO;
						}
						else
						{
							echo nbf_common::nb_strlen($transaction->transaction_no) > 0 ? NBILL_TR_PAYMENT . " " . $transaction->transaction_no : NBILL_TR_AWAITING_PYT_NO;
						}
                        echo "," . str_replace(",", ";", $transaction->name) . ",";
						if (nbf_common::nb_strlen($transaction->for) > 0)
						{
							echo str_replace(",", ";", $transaction->for);
						}
						$document_ids = explode(",", $transaction->document_ids);
						if (count($document_ids) > 0)
						{
							$count_document_nos = 0;
							foreach ($document_data[$currency->code] as $invoice)
							{
								if (array_search($invoice->id, $document_ids) !== false)
								{
									if ($count_document_nos == 0)
									{
										if (nbf_common::nb_strlen($transaction->for) > 0)
										{
											echo " (";
										}
										if ($is_income)
										{
											echo NBILL_TR_INVOICE . " ";
										}
										else
										{
											echo NBILL_TR_CREDIT_NOTE . " ";
										}
									}
									else
									{
										echo "; ";
									}
									$count_document_nos++;
									echo $invoice->document_no;
								}
							}
							if (nbf_common::nb_strlen($transaction->for) > 0 && $count_document_nos > 0)
							{
								echo ")";
							}
						}
						echo ",";
						$count_ledgers = 0;
						foreach ($tx_ledger[$currency->code] as $ledger)
						{
							if ($ledger->transaction_id == $transaction->id)
							{
								if ($count_ledgers > 0)
								{
									echo "; ";
								}
								$count_ledgers++;
								echo $ledger->code . " - " . str_replace(",", "", $ledger->description);
								if (!float_cmp(format_number($ledger->net_amount, 'currency_line'), format_number((float_subtract($transaction->amount, (float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_line'), 'currency_line')), 'currency_line')), 'currency_line')))
								{
									echo " (" . format_number($ledger->net_amount, 'currency_line', null, $csv, null, $currency->code) . ")";
								}
							}
						}

						echo ",";
						if ($is_income)
                        {
                            echo format_number(float_subtract($transaction->amount, (float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand')), 'currency_grand'), 'currency_grand', null, $csv) . ",";
                            $total_net_income = float_add($total_net_income, float_subtract($transaction->amount, (float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand')), 'currency_grand'), 'currency_grand');
                            echo format_number(float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand'), 'currency_grand', !$csv, $csv) . ",";
                            $total_tax_income = float_add($total_tax_income, float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand'), 'currency_grand');
                            echo format_number($transaction->amount, 'currency_grand', !$csv, $csv) . ",";
                            $total_income = float_add($total_income, $transaction->amount, 'currency_grand');
                            echo ",,,";
                        }
                        else
                        {
						    echo ",,,";
                            echo format_number(float_subtract($transaction->amount, float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand'), 'currency_grand'), 'currency_grand', null, $csv) . ",";
                            $total_net_expenditure = float_add($total_net_expenditure, float_subtract($transaction->amount, float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand'), 'currency_grand'), 'currency_grand');
                            echo format_number(float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand'), 'currency_grand', !$csv, $csv) . ",";
                            $total_tax_expenditure = float_add($total_tax_expenditure, float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand'), 'currency_grand');
                            echo format_number($transaction->amount, 'currency_grand', !$csv, $csv) . ",";
                            $total_expenditure = float_add($total_expenditure, $transaction->amount, 'currency_grand') . ",";
                        }
						echo format_number(float_subtract($total_income, $total_expenditure, 'currency_grand'), 'currency_grand', !$csv, $csv);
						echo "\n";
					}
					else
					{
						?>
						<tr>
							<td class="list-value"><?php echo nbf_common::nb_date($cfg_date_format, $transaction->date); ?></td>
							<td class="list-value"><?php
								$link = "";
								$text = "";
								if ($is_income)
								{
									$link = nbf_cms::$interop->admin_page_prefix . "&action=income&task=edit&cid=$transaction->id&return=$return_url";
									$text = nbf_common::nb_strlen($transaction->transaction_no) > 0 ? NBILL_TR_RECEIPT . " " . $transaction->transaction_no : NBILL_TR_AWAITING_RCT_NO;
								}
								else
								{
									$link = nbf_cms::$interop->admin_page_prefix . "&action=expenditure&task=edit&cid=$transaction->id&return=$return_url";
									$text = nbf_common::nb_strlen($transaction->transaction_no) > 0 ? NBILL_TR_PAYMENT . " " . $transaction->transaction_no : NBILL_TR_AWAITING_PYT_NO;
								}
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
							<td class="list-value responsive-cell optional word-breakable"><?php
								$text = stripslashes($transaction->name);
								$document_ids = explode(",", $transaction->document_ids);

								if ($transaction->entity_id > 0 && !$printer_friendly)
								{
                                    if ($is_income)
                                    {
                                        echo "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=clients&task=edit&cid=$transaction->entity_id&return=$return_url\">$text</a>";
                                    }
                                    else
                                    {
									    echo "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=suppliers&task=edit&cid=$transaction->entity_id&return=$return_url\">$text</a>";
                                    }
								}
                                else
                                {
                                    echo $text;
                                } ?>
                            </td>
							<td class="list-value responsive-cell extra-wide-only"><?php
								if (nbf_common::nb_strlen($transaction->for) > 0)
								{
									echo stripslashes($transaction->for);
								}
								if (count($document_ids) > 0)
								{
									$count_document_nos = 0;
									foreach ($document_data[$currency->code] as $invoice)
									{
										if (array_search($invoice->id, $document_ids) !== false)
										{
											if ($count_document_nos == 0)
											{
												if (nbf_common::nb_strlen($transaction->for) > 0)
												{
													echo " (";
												}
												if ($is_income)
												{
													echo NBILL_TR_INVOICE . " ";
												}
												else
												{
													echo NBILL_TR_CREDIT_NOTE . " ";
												}
											}
											else
											{
												echo ", ";
											}
											$count_document_nos++;
											if ($printer_friendly)
											{
												echo $invoice->document_no;
											}
											else
											{
												echo "<a href=\"#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=invoices&task=printpreviewpopup&hidemainmenu=1&items=$invoice->id', '" . uniqid() . "', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;\" title=\"Preview\">" . $invoice->document_no . "</a>";
											}
										}
									}
									if (nbf_common::nb_strlen($transaction->for) > 0 && $count_document_nos > 0)
									{
										echo ")";
									}
								}
							?></td>
							<td class="list-value responsive-cell extra-extra-wide-only">
								<?php
								$count_ledgers = 0;
								foreach ($tx_ledger[$currency->code] as $ledger)
								{
									if ($ledger->transaction_id == $transaction->id)
									{
										if ($count_ledgers > 0)
										{
											echo "<br />";
										}
										$count_ledgers++;
										echo $ledger->code . " - " . stripslashes($ledger->description);
										if (!float_cmp(format_number($ledger->net_amount, 'currency_line'), format_number(float_subtract($transaction->amount, float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_line'), 'currency_line'), 'currency_line'), 'currency_line')))
										{
											echo " (" . format_number($ledger->net_amount, 'currency_line', null, $csv, null, $currency->code) . ")";
										}
									}
								}
								?>
							</td>
                            <td class="list-value report-amount responsive-cell wide-only"><?php if ($is_income) {echo format_number(float_subtract($transaction->amount, float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand'), 'currency_grand'), 'currency_grand', !$csv, $csv, null, $currency->code); $total_net_income = float_add($total_net_income, float_subtract($transaction->amount, (float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand')), 'currency_grand'), 'currency_grand'); } else { echo "&nbsp;"; } ?></td>
                            <td class="list-value report-amount responsive-cell wide-only"><?php if ($is_income) {echo format_number(float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand'), 'currency_grand', !$csv, $csv, null, $currency->code); $total_tax_income = float_add($total_tax_income, float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand'), 'currency_grand');} else { echo "&nbsp;"; } ?></td>
							<td class="list-value report-amount"><?php if ($is_income) {echo format_number($transaction->amount, 'currency_grand', !$csv, $csv, null, $currency->code); $total_income = float_add($total_income, $transaction->amount, 'currency_grand');} else { echo "&nbsp;"; } ?></td>
							<td class="list-value report-amount responsive-cell wide-only"><?php if ($is_income) {echo "&nbsp;"; } else {echo format_number(float_subtract($transaction->amount, (float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand')), 'currency_grand'), 'currency_grand', !$csv, $csv, null, $currency->code); $total_net_expenditure = float_add($total_net_expenditure, float_subtract($transaction->amount, float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand'), 'currency_grand'), 'currency_grand'); } ?></td>
                            <td class="list-value report-amount responsive-cell wide-only"><?php if ($is_income) {echo "&nbsp;"; } else {echo format_number(float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand'), 'currency_grand', !$csv, $csv, null, $currency->code); $total_tax_expenditure = float_add($total_tax_expenditure, float_add($transaction->tax_amount_1, float_add($transaction->tax_amount_2, $transaction->tax_amount_3, 'currency_grand'), 'currency_grand'), 'currency_grand'); } ?></td>
                            <td class="list-value report-amount"><?php if ($is_income) {echo "&nbsp;"; } else {echo format_number($transaction->amount, 'currency_grand', !$csv, $csv, null, $currency->code); $total_expenditure = float_add($total_expenditure, $transaction->amount, 'currency_grand');} ?></td>
							<td class="list-value report-amount"><?php echo format_number(float_subtract($total_income, $total_expenditure), 'currency_grand', !$csv, $csv, null, $currency->code); ?></td>
						</tr>
						<?php
					}
				}

				if ($csv)
				{
					echo "\n";
				}
				else
				{?>
					<tr style="background-color:#efefef;font-weight:bold;">
						<td colspan="2" style="border-top: solid 2px #999999;">
							<?php echo sprintf(NBILL_TR_TOTAL, $transaction_count); ?>
						</td>
                        <td class="report-total report-border-top responsive-cell optional"></td>
                        <td class="report-total report-border-top responsive-cell extra-wide-only"></td>
                        <td class="report-total report-border-top responsive-cell extra-extra-wide-only"></td>
                        <td class="report-total report-border-top responsive-cell wide-only">
                            <?php echo format_number($total_net_income, 'currency_grand', !$csv, $csv, null, $currency->code); ?>
                        </td>
                        <td class="report-total report-border-top responsive-cell wide-only">
                            <?php echo format_number($total_tax_income, 'currency_grand', !$csv, $csv, null, $currency->code); ?>
                        </td>
						<td class="report-total report-border-top">
							<?php echo format_number($total_income, 'currency_grand', !$csv, $csv, null, $currency->code); ?>
						</td>
                        <td class="report-total report-border-top responsive-cell wide-only">
                            <?php echo format_number($total_net_expenditure, 'currency_grand', !$csv, $csv, null, $currency->code); ?>
                        </td>
                        <td class="report-total report-border-top responsive-cell wide-only">
                            <?php echo format_number($total_tax_expenditure, 'currency_grand', !$csv, $csv, null, $currency->code); ?>
                        </td>
						<td class="report-total report-border-top">
							<?php echo format_number($total_expenditure, 'currency_grand', !$csv, $csv, null, $currency->code); ?>
						</td>
						<td class="report-total report-border-top">
							<?php echo format_number(float_subtract($total_income, $total_expenditure), 'currency_grand', !$csv, $csv, null, $currency->code); ?>
						</td>
					</tr>
				</table>
                </div>
				<?php
                } ?>
				<?php
				if (!$printer_friendly && !$csv)
				{
					$nbf_tab_txs->add_tab_content($currency->code, ob_get_clean());
				}
			}
		}
		if (!$printer_friendly && !$csv && $tab_started)
		{
			$nbf_tab_txs->end_tab_group();
		}
		if ($printer_friendly)
		{
			echo "</td></tr></table></div>";
		}
		if (!$csv)
		{
			?>
			<br />
			</form>
			<?php
		}
	}
}