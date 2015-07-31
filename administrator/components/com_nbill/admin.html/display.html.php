<?php
/**
* HTML output for display options page
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillDisplay
{
	public static function showDisplayOptions($rows, $extension_links, $additional_links, $pay_freqs, $choose_lang)
	{
		?>
		<script language="javascript" type="text/javascript">
		function nbill_submit_task(task_name)
        {
			document.adminForm.task.value=task_name;
            document.adminForm.submit();
		}
		</script>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "display"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_DISPLAY_OPTIONS_TITLE; ?>
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
        <input type="hidden" name="action" value="display" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<?php nbf_html::add_filters(); ?>
		<p style="text-align:left"><?php echo NBILL_DISPLAY_INTRO; ?></p>

		<?php

        $responsive_options = array();
        $responsive_options[] = nbf_html::list_option(0, NBILL_DISPLAY_HIDE);
        $responsive_options[] = nbf_html::list_option(2, NBILL_DISPLAY_OPTIONAL);
        $responsive_options[] = nbf_html::list_option(3, NBILL_DISPLAY_LOW_PRIORITY);
        $responsive_options[] = nbf_html::list_option(4, NBILL_DISPLAY_MEDIUM_PRIORITY);
        $responsive_options[] = nbf_html::list_option(5, NBILL_DISPLAY_HIGH_PRIORITY);
        $responsive_options[] = nbf_html::list_option(1, NBILL_DISPLAY_ESSENTIAL);
        ob_start();
        ?>
        <tr>
            <td>
                &nbsp;
            </td>
            <td>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;| Responsive Options for Mobile Devices |
            </td>
        </tr>
        <?php
        $responsive_header = ob_get_clean();

		$nbf_tab_global = new nbf_tab_group();
        $nbf_tab_global->start_tab_group("global", true);
        $nbf_tab_global->add_tab_title("my_account", NBILL_MY_ACCOUNT);
        $nbf_tab_global->add_tab_title("my_profile", NBILL_MY_PROFILE, "", "", "responsive-cell priority");
        
        $nbf_tab_global->add_tab_title("my_invoices", NBILL_MY_INVOICES);
        ob_start();
		?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-display-options">
		    <tr>
			    <th colspan="2"><?php echo NBILL_DISPLAY_MY_ACCOUNT; ?></th>
		    </tr>
            <!-- Custom Fields Placeholder -->
            <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISPLAY_ADD_OPTION_TO_FORM_ACTION; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "submit_option")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
					    echo nbf_html::yes_or_no_options("submit_option", "", $display_value);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_ADD_OPTION_TO_FORM_ACTION_DESC, "submit_option_help"); ?>
			    </td>
		    </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_PATHWAY; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "pathway")
                            {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::yes_or_no_options("pathway", "", $display_value);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_PATHWAY_DESC, "pathway_help"); ?>
                </td>
            </tr>
		    <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_MY_ACCOUNT_HEADER; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "my_account")
                            {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::yes_or_no_options("my_account", "", $display_value);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_MY_ACCOUNT_HEADER_DESC, "my_account_help"); ?>
                </td>
            </tr>
            <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISPLAY_USER_GROUP; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "access")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
					    echo nbf_html::yes_or_no_options("access", "", $display_value);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_USER_GROUP_DESC, "access_help"); ?>
			    </td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISPLAY_PROFILE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "profile")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
					    echo nbf_html::yes_or_no_options("profile", "", $display_value);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_PROFILE_DESC, "profile_help"); ?>
			    </td>
		    </tr>
            <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISPLAY_ORDERS; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "orders")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
					    echo nbf_html::yes_or_no_options("orders", "", $display_value);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_ORDERS_DESC, "orders_help"); ?>
			    </td>
		    </tr>
            <?php } ?>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISPLAY_INVOICES; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "invoices")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
					    echo nbf_html::yes_or_no_options("invoices", "", $display_value);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_INVOICES_DESC, "invoices_help"); ?>
			    </td>
		    </tr>
            <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_QUOTES; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "quotes")
                            {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::yes_or_no_options("quotes", "", $display_value);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_QUOTES_DESC, "quotes_help"); ?>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_ADMIN; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "admin_via_fe")
                            {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::yes_or_no_options("admin_via_fe", "", $display_value);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_ADMIN_DESC, "admin_via_fe_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_ADMIN_FULL; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "admin_via_fe_full")
                            {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::yes_or_no_options("admin_via_fe_full", "", $display_value);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_ADMIN_FULL_DESC, "admin_via_fe_full_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_ADMIN_NEW; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "admin_via_fe_new")
                            {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::yes_or_no_options("admin_via_fe_new", "", $display_value);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_ADMIN_NEW_DESC, "admin_via_fe_new_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_LOGOUT; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "logout")
                            {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::yes_or_no_options("logout", "", $display_value);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_LOGOUT_DESC, "logout_help"); ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="rounded-table" style="margin-top:10px;">
        <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
            <tr>
                <th colspan="2"><?php echo NBILL_DISPLAY_EXTENSION_LINKS; ?></th>
            </tr>
            <?php
            if (count($extension_links) == 0)
            {
                ?>
                <tr>
                    <td colspan="2"><?php echo NBILL_DISPLAY_NO_EXTENSION_LINKS; ?></td>
                </tr>
                <?php
            }
            else
            {
                ?>
                <tr>
                    <td colspan="2"><?php echo NBILL_DISPLAY_EXTENSION_LINKS_INTRO; ?></td>
                </tr>
                <?php
                for ($i = 0; $i < count($extension_links); $i++)
                {
                    $thislink_extension = "";
                    $thislink_id = 0;
                    $thislink_text = "";
                    $thislink_desc = "";
                    $thislink_ordering = 0;
                    $thislink_published = true;
                    foreach ($extension_links as $link)
                    {
                        if ($link->ordering == $i)
                        {
                            $thislink_extension = str_replace("\"", "&quot;", $link->extension_name . ' ' . NBILL_LINK . ' ' . ($i + 1));
                            $thislink_id = intval($link->id);
                            $thislink_text = str_replace("\"", "&quot;", $link->link_text);
                            $thislink_desc = str_replace("\"", "&quot;", $link->link_description);
                            $thislink_ordering = $i;
                            $thislink_published = $link->published;
                        }
                    }
                    ?>
                    <tr>
                        <td colspan="2">
                            <?php echo $thislink_extension; ?>
                            &nbsp;&nbsp;<span style="white-space:nowrap;"><?php echo NBILL_LINK_TEXT; ?>&nbsp;<input type="text" name="extlink_<?php echo $thislink_id; ?>_text" id="extlink_<?php echo $i; ?>_text" value="<?php echo $thislink_text; ?>" /></span>
                            &nbsp;&nbsp;<span style="white-space:nowrap;"><?php echo NBILL_LINK_DESC; ?>&nbsp;<input type="text" name="extlink_<?php echo $thislink_id; ?>_desc" id="extlink_<?php echo $i; ?>_desc" value="<?php echo $thislink_desc; ?>" /></span>
                            &nbsp;&nbsp;<span style="white-space:nowrap;"><?php echo NBILL_LINK_ORDERING; ?>&nbsp;<input type="text" name="extlink_<?php echo $thislink_id; ?>_ordering" id="extlink_<?php echo $i; ?>_ordering" value="<?php echo $thislink_ordering; ?>" size="2" /></span>
                            &nbsp;&nbsp;<span style="white-space:nowrap;"><?php echo NBILL_LINK_PUBLISHED; ?>&nbsp;<input type="checkbox" name="extlink_<?php echo $thislink_id; ?>_published" id="extlink_<?php echo $i; ?>_published"<?php echo $thislink_published ? ' checked="checked"' : ''; ?> /></span>
                        </td>
                    </tr>
                <?php }
            } ?>
        </table>
        </div>

        <div class="rounded-table" style="margin-top:10px;">
        <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
		    <tr>
			    <th colspan="2"><?php echo NBILL_DISPLAY_ADDITIONAL_LINKS; ?></th>
		    </tr>
            <tr>
                <td colspan="2"><?php echo NBILL_DISPLAY_MORE_LINKS; ?></td>
            </tr>
		    <?php
		    for ($i = 1; $i <= 10; $i++)
		    {
			    $thislink_url = "";
			    $thislink_text = "";
			    $thislink_desc = "";
			    foreach ($additional_links as $link)
			    {
				    if ($link->ordering == $i)
				    {
					    $thislink_url = str_replace("\"", "&quot;", $link->url);
					    $thislink_text = str_replace("\"", "&quot;", $link->text);
					    $thislink_desc = str_replace("\"", "&quot;", $link->description);
				    }
			    }
			    ?>
			    <tr>
				    <td>
					    <?php echo NBILL_LINK . ' ' . $i; ?>
				    </td>
				    <td>
						<span style="white-space:nowrap"><?php echo NBILL_LINK_URL; ?>&nbsp;<input type="text" name="link<?php echo $i - 1; ?>_url" id="link<?php echo $i - 1; ?>_url" value="<?php echo $thislink_url; ?>" /></span>
						<span style="white-space:nowrap">&nbsp;<?php echo NBILL_LINK_TEXT; ?>&nbsp;<input type="text" name="link<?php echo $i - 1; ?>_text" id="link<?php echo $i - 1; ?>_text" value="<?php echo $thislink_text; ?>" /></span>
						<span style="white-space:nowrap">&nbsp;<?php echo NBILL_LINK_DESC; ?>&nbsp;<input type="text" name="link<?php echo $i - 1; ?>_desc" id="link<?php echo $i - 1; ?>_desc" value="<?php echo $thislink_desc; ?>" /></span>
				    </td>
			    </tr>
		    <?php } ?>
		    </table>
        </div>

		<?php
        $nbf_tab_global->add_tab_content("my_account", ob_get_clean());
        ob_start();
		?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
		    <tr>
			    <th colspan="2"><?php echo NBILL_DISPLAY_MY_PROFILE; ?></th>
		    </tr>
            <?php
            if ($choose_lang)
            {?>
                <tr>
                    <td class="nbill-setting-caption">
                        <?php echo NBILL_DISPLAY_LANGUAGE_SELECTION; ?>
                    </td>
                    <td class="nbill-setting-value">
                        <?php
                            $display_value = 1;
                            foreach ($rows as $row)
                            {
                                if ($row->name == "choose_lang")
                                {
                                    $display_value = $row->value;
                                    break;
                                }
                            }
                            echo nbf_html::yes_or_no_options("choose_lang", "", $display_value);
                        ?>
                        <?php nbf_html::show_static_help(NBILL_DISPLAY_LANGUAGE_SELECTION_DESC, "choose_lang_help"); ?>
                    </td>
                </tr>
            <?php } ?>
		    <tr>
			    <td colspan="2">
				    <?php echo sprintf(NBILL_DISPLAY_MY_PROFILE_HELP, '<a href="' . nbf_cms::$interop->admin_page_prefix . '&action=profile_fields">' . NBILL_DISPLAY_MY_PROFILE_HELP_FIELDS . '</a>'); ?>
			    </td>
		    </tr>
		    </table>
        </div>

		<?php
		$nbf_tab_global->add_tab_content("my_profile", ob_get_clean());
        
        ob_start();
		?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
		    <tr>
			    <th colspan="2"><?php echo NBILL_DISPLAY_MY_INVOICES; ?></th>
		    </tr>
            <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISPLAY_FILTER; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "filter")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
					    echo nbf_html::yes_or_no_options("filter", "", $display_value);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_FILTER_DESC, "filter_help"); ?>
			    </td>
		    </tr>
            <?php } ?>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISPLAY_DATE_RANGE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "invoice_date_range")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
					    echo nbf_html::yes_or_no_options("invoice_date_range", "", $display_value);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_DATE_RANGE_DESC, "invoice_date_range_help"); ?>
			    </td>
		    </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_HTML_PREVIEW; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "html_preview")
                            {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::yes_or_no_options("html_preview", "", $display_value);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_HTML_PREVIEW_DESC, "html_preview_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_PDF; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "pdf")
                            {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::yes_or_no_options("pdf", "", $display_value);
                    ?>
                    <?php nbf_html::show_static_help(sprintf(NBILL_DISPLAY_PDF_DESC, "<a href=\"http://" . NBILL_BRANDING_HTML2PS . "\" target=\"_blank\">" . NBILL_BRANDING_HTML2PS . "</a>"), "pdf_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_DUE_DATE; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        $no_of_units = 30;
                        $units = 'days';
                        foreach ($rows as $row)
                        {
                            if ($row->name == "due_date") {
                                $display_value = $row->value;
                            }
                            if ($row->name == "due_date_no_of_units") {
                                $no_of_units = $row->value;
                            }
                            if ($row->name == "due_date_units") {
                                $units = $row->value;
                            }
                        }
                        echo nbf_html::yes_or_no_options("due_date", "onchange=\"if(document.getElementById('due_date0').checked){document.getElementById('due_date_period').style.display='none';}else{document.getElementById('due_date_period').style.display='';}\"", $display_value);
                        ob_start();
                    ?>
                    <span id="due_date_period"<?php if (!$display_value) {echo ' style="display:none;"';} ?>>
                        <select name="due_date_no_of_units" id="due_date_no_of_units" style="width:auto;">
                            <?php for ($i=1; $i<=30; $i++) {
                                ?>
                                <option<?php if ($no_of_units == $i){echo ' selected="selected"';} ?>><?php echo $i; ?></option>
                                <?php
                            } ?>
                        </select>
                        <select name="due_date_units" id="due_date_units" style="width:auto;">
                            <option value="days"<?php if ($units=='days'){echo ' selected="selected"';}?>><?php echo NBILL_DAYS; ?> </option>
                            <option value="weeks"<?php if ($units=='weeks'){echo ' selected="selected"';}?>><?php echo NBILL_WEEKS; ?> </option>
                            <option value="months"<?php if ($units=='months'){echo ' selected="selected"';}?>><?php echo NBILL_MONTHS; ?> </option>
                        </select>
                        <?php
                        $selects = ob_get_clean();
                        echo sprintf(NBILL_DISPLAY_DUE_DATE_AFTER, $selects);
                        ?>
                    </span>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_DUE_DATE_DESC, "due_date_help"); ?>
                </td>
            </tr>
            <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_GENERATE_EARLY; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "generate_early") {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::yes_or_no_options("generate_early", "", $display_value);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_GENERATE_EARLY_DESC, "generate_early_help"); ?>
                </td>
            </tr>
            <?php }
            echo $responsive_header; ?>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISPLAY_INVOICE_DATE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "document_date")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
                        echo nbf_html::radio_list($responsive_options, "document_date", $display_value, true);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_INVOICE_DATE_DESC, "document_date_help"); ?>
			    </td>
		    </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_DUE_DATE_ON_LIST; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "due_date_on_list")
                            {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::radio_list($responsive_options, "due_date_on_list", $display_value, true);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_DUE_DATE_ON_LIST_DESC, "due_date_on_list_help"); ?>
                </td>
            </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISPLAY_FIRST_ITEM; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "first_item")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
					    echo nbf_html::radio_list($responsive_options, "first_item", $display_value, true);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_FIRST_ITEM_DESC, "first_item_help"); ?>
			    </td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISPLAY_NET; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "net")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
					    echo nbf_html::radio_list($responsive_options, "net", $display_value, true);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_NET_DESC, "net_help"); ?>
			    </td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISPLAY_GROSS; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "gross")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
					    echo nbf_html::radio_list($responsive_options, "gross", $display_value, true);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_GROSS_DESC, "gross_help"); ?>
			    </td>
		    </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_OUTSTANDING; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "outstanding")
                            {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::radio_list($responsive_options, "outstanding", $display_value, true);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_OUTSTANDING_DESC, "outstanding_help"); ?>
                </td>
            </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISPLAY_STATUS; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "status")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
					    echo nbf_html::radio_list($responsive_options, "status", $display_value, true);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_STATUS_DESC, "status_help"); ?>
			    </td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption"<?php if (strtolower(nbf_version::$suffix) != 'lite') { ?> rowspan="2"<?php } ?>>
				    <?php echo NBILL_DISPLAY_PAYMENT_LINK; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "payment_link")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
                        echo nbf_html::radio_list($responsive_options, "payment_link", $display_value, true);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_PAYMENT_LINK_DESC, "payment_link_help"); ?>
			    </td>
		    </tr>
		    <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
            <tr>
			    <td class="nbill-setting-value">
				    <?php echo NBILL_DISPLAY_PAYMENT_LINK_THRESHOLD . "&nbsp;";
					    $pay_freq_list = array();
					    foreach($pay_freqs as $pay_freq)
					    {
						    $pay_freq_list[] = nbf_html::list_option($pay_freq->code, $pay_freq->description);
					    }
					    $display_value = "AA";
					    foreach ($rows as $row)
					    {
						    if ($row->name == "pay_freq_paylink_threshold")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
					    echo nbf_html::select_list($pay_freq_list, "pay_freq_paylink_threshold", 'id="pay_freq_paylink_threshold" class="inputbox"', $display_value);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_PAYMENT_LINK_THRESHOLD_DESC, "pay_freq_paylink_threshold_help"); ?>
			    </td>
		    </tr>
            <?php } ?>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_PAYLINK_QR_CODE; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "paylink_qr_code")
                            {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::yes_or_no_options("paylink_qr_code", "", $display_value);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_PAYLINK_QR_CODE_DESC, "paylink_qr_code_help"); ?>
                </td>
            </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISPLAY_GATEWAY_CHOICE_INVOICE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "gateway_choice_invoice")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
					    echo nbf_html::yes_or_no_options("gateway_choice_invoice", "", $display_value);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_GATEWAY_CHOICE_INVOICE_DESC, "gateway_choice_invoice_help"); ?>
			    </td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_DISPLAY_PAY_REQUIRES_LOGIN_INVOICE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $display_value = 1;
					    foreach ($rows as $row)
					    {
						    if ($row->name == "login_to_pay_invoice")
						    {
							    $display_value = $row->value;
							    break;
						    }
					    }
					    echo nbf_html::yes_or_no_options("login_to_pay_invoice", "", $display_value);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_PAY_REQUIRES_LOGIN_INVOICE_DESC, "login_to_pay_invoice_help"); ?>
			    </td>
		    </tr>
            <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
		    <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_INV_SHOW_DATE_RANGE; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "inv_range")
                            {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::yes_or_no_options("inv_range", "", $display_value);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_INV_SHOW_DATE_RANGE_DESC, "inv_range_help"); ?>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_DISPLAY_SUPPRESS_ZERO_TAX; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $display_value = 1;
                        foreach ($rows as $row)
                        {
                            if ($row->name == "suppress_zero_tax")
                            {
                                $display_value = $row->value;
                                break;
                            }
                        }
                        echo nbf_html::yes_or_no_options("suppress_zero_tax", "", $display_value);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_DISPLAY_SUPPRESS_ZERO_TAX_DESC, "suppress_zero_tax_help"); ?>
                </td>
            </tr>
		    </table>
        </div>

		<?php
		$nbf_tab_global->add_tab_content("my_invoices", ob_get_clean());
        $nbf_tab_global->end_tab_group();
		?>

		</form>
		<?php
	}
}