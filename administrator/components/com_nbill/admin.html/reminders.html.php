<?php
/**
* HTML output for reminder feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillReminders
{
	public static function showReminders($rows, $pagination, $vendors)
	{
        $vendor_col = false;
        nbf_common::load_language("xref");
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "reminders"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_REMINDERS_TITLE; ?>
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
        <input type="hidden" name="action" value="reminders" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<p align="left">
			<strong><?php echo NBILL_REMINDERS_WARNING; ?></strong>
		</p>
		<p align="left">
			<?php echo NBILL_REMINDERS_INTRO; ?>
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
				echo nbf_html::select_list($vendor_name, "vendor_filter", 'id="vendor_filter" class="inputbox" onchange="document.adminForm.submit();"', $selected_filter );
			}
			else
			{
				echo "<input type=\"hidden\" name=\"vendor_filter\" id=\"vendor_filter\" value=\"" . $vendors[0]->id . "\" />";
				$_POST['vendor_filter'] = $vendors[0]->id;
			}

			//Display filter for reminder name
			if (count($vendors) < 2)
			{
				echo "<p align=\"left\">";
			}
			$reminder_search = nbf_common::get_param($_POST,'reminder_search', '', true);
			echo NBILL_REMINDER_NAME . " <input type=\"text\" name=\"reminder_search\" value=\"" . $reminder_search . "\" />";
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
				    <?php echo NBILL_REMINDER_NAME; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_REMINDER_TYPE; ?>
			    </th>
			    <th class="title" width="5%" align="center" style="text-align:center">
				    <?php echo NBILL_REMINDER_ACTIVE; ?>
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
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];

				    $img 	= $row->active ? 'tick.png' : 'cross.png';
				    $task 	= $row->active ? 'unpublish' : 'publish';
				    $alt 	= $row->active ? NBILL_REMINDER_ACTIVE_YES : NBILL_REMINDER_ACTIVE_NO;

				    $link = nbf_cms::$interop->admin_page_prefix . "&action=reminders&task=edit&cid=$row->id&reminder_search=$reminder_search";

				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\">$checked</td>";
				    echo "<td class=\"list-value\"><a href=\"$link\" title=\"" . NBILL_EDIT_REMINDER . "\">" . $row->reminder_name . "</a></td>";
				    echo "<td>" . @constant($row->reminder_type_desc) . "</td>";
				    echo "<td width=\"5%\" class=\"selector\">";
				    echo "<a href=\"#\" onclick=\"for(var i=0; i<" . count($rows) . ";i++) {document.getElementById('cb' + i).checked=false};document.getElementById('cb$i').checked=true;document.adminForm.task.value='$task';document.adminForm.submit();return false;\">";
				    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/$img\" border=\"0\" alt=\"$alt\" /></a>";

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
		    <tr class="nbill_tr_no_highlight"><td colspan="<?php echo $vendor_col ? "6" : "5"; ?>" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		</form>
		<?php
	}

	/**
	* Edit a reminder (or create a new one)
	*/
	public static function editreminder($reminder_id, $row, $vendors, $reminder_types, $clients)
	{
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
		nbf_html::load_calendar();
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
			<?php
			$cal_date_format = nbf_common::get_date_format(true);
			?>
			//Validate
			if (form.reminder_name.value == "" && form.reminder_name.value == "")
			{
				alert('<?php echo NBILL_REMINDER_NAME_REQUIRED; ?>');
			}
			else if (form.starting_from.value.length > 0 && !IsValidDate(form.starting_from.value))
			{
				alert('<?php echo sprintf(NBILL_INVALID_DATE_FIELD, NBILL_REMINDER_STARTING_FROM, $cal_date_format); ?>');
			}
			else if (form.number_of_units.length > 0 && !IsNumeric(form.number_of_units.value))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_REMINDER_NUMBER_OF_UNITS); ?>');
			}
			else
			{
				document.adminForm.task.value=task_name;
                document.adminForm.submit();
			}
		}

		function type_changed()
		{
			switch (document.getElementById('reminder_type').value)
			{
				<?php
				foreach ($reminder_types as $reminder_type)
				{
					echo "case '$reminder_type->code':";
					?>
					switch (document.getElementById('reminder_name').value)
					{
						<?php
						foreach ($reminder_types as $reminder_type2)
						{
							echo "case '" . @constant($reminder_type2->description) . "':\n";
						}
						?>
						case '':
							document.getElementById('reminder_name').value = '<?php echo @constant($reminder_type->description); ?>';
							break;
					}
					document.getElementById('email_text').value = '<?php echo str_replace("\n", "\\n", str_replace("'", "\'", @constant($reminder_type->description . "_EMAIL"))); ?>';
					break;
				<?php
				}
				?>
			}
			if (document.getElementById('reminder_type').value == 'DD')
			{
				document.adminForm.send_after[1].checked = true;
			}
			else
			{
				document.adminForm.send_after[0].checked = true;
			}
		}
		</script>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "reminders"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php $reminder_name = $row->reminder_name;
				echo $row->id ? NBILL_EDIT_REMINDER . " '$reminder_name'" : NBILL_NEW_REMINDER; ?>
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
        <input type="hidden" name="action" value="reminders" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $reminder_id;?>" />
		<?php nbf_html::add_filters(); ?>

        <?php
        $tab_settings = new nbf_tab_group();
        $tab_settings->start_tab_group("admin_settings");
        $tab_settings->add_tab_title("basic", NBILL_ADMIN_TAB_BASIC);
        $tab_settings->add_tab_title("advanced", NBILL_ADMIN_TAB_ADVANCED);
        ob_start();
        ?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-reminders">
		    <tr>
			    <th colspan="2"><?php echo NBILL_REMINDER_DETAILS; ?>
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
							    echo nbf_html::select_list($vendor_name, "vendor_id", 'id="vendor_id" class="inputbox"', $selected_vendor);
						    ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_VENDOR_ID, "vendor_id_help"); ?>
					    </td>
				    </tr>
			    <?php }
		    ?>
		    <tr id="nbill-admin-tr-reminder-type">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_REMINDER_TYPE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    //Create a dropdown of types
				    $type_list = array();
				    foreach ($reminder_types as $reminder_type)
				    {
					    $type_list[] = nbf_html::list_option($reminder_type->code, @constant($reminder_type->description));
				    }
				    echo nbf_html::select_list($type_list, "reminder_type", 'onchange="type_changed();" class="inputbox" id="reminder_type"', $row->reminder_type);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_REMINDER_TYPE, "reminder_type_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-reminder-name">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_REMINDER_NAME; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="reminder_name" id="reminder_name" value="<?php echo str_replace("\"", "&quot;", $row->reminder_name); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_REMINDER_NAME, "reminder_name_help"); ?>
			    </td>
		    </tr>
            <!-- Custom Fields Placeholder -->
		    <tr id="nbill-admin-tr-reminder-active">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_REMINDER_ACTIVE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    echo nbf_html::yes_or_no_options("active", "", $row->active); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_REMINDER_ACTIVE, "active_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-reminder-admin">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_REMINDER_ADMIN; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    echo nbf_html::yes_or_no_options("admin", "", $row->admin); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_REMINDER_ADMIN, "admin_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-starting-from">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_REMINDER_STARTING_FROM; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					$date_format = nbf_common::get_date_format();
					$cal_date_format = nbf_common::get_date_format(true);
					$date_value = $row->id ? nbf_common::nb_date($date_format, $row->starting_from) : nbf_common::nb_date($date_format, nbf_common::nb_time());

					$date_parts = nbf_date::get_date_parts($date_value, $cal_date_format);
					if ($date_parts['y'] < 1971)
					{
						$date_value = "";
					}
					?>
					<span style="white-space:nowrap"><input type="text" name="starting_from" class="inputbox" maxlength="19" value="<?php echo $date_value; ?>" />
					<input type="button" name="starting_from_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.starting_from,'<?php echo $cal_date_format; ?>',this);" /></span>
                    <?php nbf_html::show_static_help(NBILL_INSTR_REMINDER_STARTING_FROM, "starting_from_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-no-of-units">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_REMINDER_NO_OF_UNITS; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="number_of_units" id="number_of_units" value="<?php echo $row->number_of_units; ?>" class="inputbox numeric" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_REMINDER_NO_OF_UNITS, "number_of_units_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-units">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_REMINDER_UNITS; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    //Create a dropdown of types
					    $unit_list = array();
					    $unit_list[] = nbf_html::list_option("days", NBILL_REMINDER_UNIT_DAYS);
					    $unit_list[] = nbf_html::list_option("weeks", NBILL_REMINDER_UNIT_WEEKS);
					    $unit_list[] = nbf_html::list_option("months", NBILL_REMINDER_UNIT_MONTHS);
					    echo nbf_html::select_list($unit_list, "units", 'class="inputbox" id="units"', $row->units);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_REMINDER_UNITS, "units_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-send-after">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_REMINDER_SEND_AFTER; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    echo nbf_html::yes_or_no_options("send_after", "", $row->send_after); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_REMINDER_SEND_AFTER, "send_after_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-email-text">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_REMINDER_EMAIL_TEXT; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <textarea name="email_text" id="email_text"><?php echo $row->email_text; ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_INSTR_REMINDER_EMAIL_TEXT, "email_text_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-client">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_REMINDER_CLIENT; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    //Create a dropdown of types
				    $client_list = array();
				    $client_list[] = nbf_html::list_option("0", NBILL_NOT_APPLICABLE);
				    foreach ($clients as $client)
				    {
					    $client_name = $client->company_name;
					    if (nbf_common::nb_strlen($client->company_name) > 0 && nbf_common::nb_strlen($client->contact_name) > 0)
					    {
						    $client_name .= " (";
					    }
					    $client_name .= $client->contact_name;
					    if (nbf_common::nb_strlen($client->company_name) > 0 && nbf_common::nb_strlen($client->contact_name) > 0)
					    {
						    $client_name .= ")";
					    }
					    $client_list[] = nbf_html::list_option($client->id, $client_name);
				    }
				    echo nbf_html::select_list($client_list, "client_id", 'class="inputbox" id="client_id"', $row->client_id);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_REMINDER_CLIENT, "client_id_help"); ?>
			    </td>
		    </tr>
		    </table>
        </div>

        <?php
        $tab_settings->add_tab_content("basic", ob_get_clean());
        ob_start();
        ?>

        <div class="rounded-table">
            <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-reminders-advanced">
            <tr>
                <th colspan="2"><?php echo NBILL_REMINDER_DETAILS; ?>
                </th>
            </tr>
            <tr id="nbill-admin-tr-filter">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_REMINDER_FILTER; ?>
                </td>
                <td class="nbill-setting-value">
                    <textarea name="filter" id="filter" <?php if (nbf_cms::$interop->demo_mode) {echo ' disabled="disabled"';} ?>><?php echo $row->filter; ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_INSTR_REMINDER_FILTER, "filter_help"); ?>
                </td>
            </tr>
            </table>
        </div>
        <?php
        $tab_settings->add_tab_content("advanced", ob_get_clean());
        $tab_settings->end_tab_group();
        ?>

		</form>

		<?php if (!$reminder_id)
		{ ?>
		<script type="text/javascript">
			type_changed();
		</script>
		<?php }
	}
}