<?php
/**
* HTML output for nominal ledger feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillLedger
{
	public static function showLedger($rows, $pagination, $vendors)
	{
        $vendor_col = false;
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "ledger"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_LEDGER_TITLE; ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		}?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="ledger" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

		<p align="left"><?php echo NBILL_LEDGER_INTRO; ?></p>

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
				    <?php echo NBILL_LEDGER_CODE; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_LEDGER_DESCRIPTION; ?>
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
				    $link = nbf_cms::$interop->admin_page_prefix . "&action=ledger&task=edit&cid=$row->id";
				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\">$checked</td>";
				    echo "<td class=\"list-value\"><a href=\"$link\" title=\"" . NBILL_EDIT_LEDGER_CODE . "\">" . $row->code . "</a></td>";
				    echo "<td class=\"list-value\">" . $row->description . "</td>";
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
		    <tr class="nbill_tr_no_highlight"><td colspan="<?php echo $vendor_col ? "5" : "4"; ?>" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>
		</form>
		<?php
	}

	public static function editLedger($ledger_id, $row, $vendors)
	{
		?>
		<script language="javascript" type="text/javascript">
		function nbill_submit_task(task_name)
        {
			var form = document.adminForm;
			if (task_name == 'cancel')
            {
				document.adminForm.task.value=task_name;
                document.adminForm.submit();
				return;
			}

			// do field validation
			if (form.code.value == "")
			{
				alert('<?php echo NBILL_LEDGER_CODE_REQUIRED; ?>');
			}
			else if (form.description.value == "")
			{
				alert('<?php echo NBILL_LEDGER_DESC_REQUIRED; ?>');
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
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "ledger"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php echo $row->id ? NBILL_EDIT_LEDGER_CODE . " '$row->code'" : NBILL_NEW_LEDGER_CODE; ?>
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
        <input type="hidden" name="action" value="ledger" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $ledger_id;?>" />
		<?php nbf_html::add_filters(); ?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
		    <tr>
			    <th colspan="2"><?php echo NBILL_LEDGER_CODE_DETAILS; ?></th>
		    </tr>
		    <?php
			    if (count($vendors) > 1)
			    {?>
				    <tr>
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
							    echo nbf_html::select_list($vendor_name, "vendor_id", 'id="vendor_id" class="inputbox"', $selected_vendor);
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
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_LEDGER_CODE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="code" id="code" value="<?php echo $row->code; ?>" class="inputbox" style="width:80px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_LEDGER_CODE, "code_help"); ?>
			    </td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_LEDGER_DESCRIPTION; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="description" id="description" value="<?php echo str_replace("\"", "&quot;", $row->description); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_LEDGER_DESCRIPTION, "description_help"); ?>
			    </td>
		    </tr>
            <!-- Custom Fields Placeholder -->
		    </table>
        </div>

		</form>
		<?php
	}
}