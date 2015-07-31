<?php
/**
* HTML output for currency page
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillCurrency
{
	public static function showCurrency($rows, $pagination)
	{
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "currencies"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_CURRENCY_TITLE; ?>
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
        <input type="hidden" name="action" value="currency" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

		<p align="left"><?php echo NBILL_CURRENCY_INTRO; ?></p>

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
				    <?php echo NBILL_CURRENCY_NAME; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_CURRENCY_CODE; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_CURRENCY_SYMBOL; ?>
			    </th>
		    </tr>
		    <?php
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];
				    $link = nbf_cms::$interop->admin_page_prefix . "&action=currency&task=edit&cid=$row->id";
				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\">$checked</td>";
				    echo "<td class=\"list-item\"><a href=\"$link\" title=\"" . NBILL_EDIT_CURRENCY_RATE . "\">" . $row->description . "</a></td>";
				    echo "<td class=\"list-item\">" . $row->code . "</td>";
				    echo "<td class=\"list-item\">" . $row->symbol . "</td>";
				    echo "</tr>";
			    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="5" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>
		</form>
		<?php
	}

	public static function editCurrency($currency_id, $row)
	{
		?>
		<script language="javascript" type="text/javascript">
		function nbill_submit_task(task_name)
        {
			if (task_name == 'cancel')
            {
				document.adminForm.task.value=task_name;
                document.adminForm.submit();
				return;
			}

			//Field validation
			if (document.adminForm.description.value == "")
			{
				alert('<?php echo NBILL_CURRENCY_NAME_REQUIRED; ?>');
			}
			else if (document.adminForm.code.value == "")
			{
				alert('<?php echo NBILL_CURRENCY_CODE_REQUIRED; ?>');
			}
            else if (document.adminForm.code.value.length != 3)
            {
                alert('<?php echo NBILL_ERR_ISO_CODE_LENGTH; ?>');
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
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "currencies"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php echo $row->id ? NBILL_EDIT_CURRENCY_RATE . " '$row->description'" : NBILL_NEW_CURRENCY_RATE; ?>
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
        <input type="hidden" name="action" value="currency" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $currency_id;?>" />
		<?php nbf_html::add_filters(); ?>

        <?php
        $tab_settings = new nbf_tab_group();
        $tab_settings->start_tab_group("admin_settings");
        $tab_settings->add_tab_title("basic", NBILL_ADMIN_TAB_BASIC);
        $tab_settings->add_tab_title("advanced", NBILL_ADMIN_TAB_ADVANCED);
        ob_start();
        ?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-currency">
		    <tr>
			    <th colspan="2"><?php echo NBILL_CURRENCY_DETAILS; ?></th>
		    </tr>

		    <tr id="nbill-admin-tr-currency-name">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CURRENCY_NAME; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="description" value="<?php echo $row->description; ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_CURRENCY_NAME, "description_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-currency-code">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CURRENCY_CODE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="code" value="<?php echo $row->code; ?>" class="inputbox" style="width:80px" maxlength="3" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_CURRENCY_CODE, "code_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-currency-symbol">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CURRENCY_SYMBOL; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="symbol" value="<?php echo $row->symbol; ?>" class="inputbox" style="width:50px;" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_CURRENCY_SYMBOL, "symbol_help"); ?>
			    </td>
		    </tr>
            <!-- Custom Fields Placeholder -->
            </table>
        </div>

        <?php
        $tab_settings->add_tab_content("basic", ob_get_clean());
        ob_start();
        ?>

        <div class="rounded-table">
        <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
        <tr>
            <th colspan="2"><?php echo NBILL_CURRENCY_DETAILS; ?></th>
        </tr>
        <?php
        nbf_html::show_admin_setting_yes_no($row, "override_default_formatting", "CURRENCY_");
        nbf_html::show_admin_setting_textbox($row, "precision_currency", "CURRENCY_");
        nbf_html::show_admin_setting_textbox($row, "precision_currency_line_total", "CURRENCY_");
        nbf_html::show_admin_setting_textbox($row, "precision_currency_grand_total", "CURRENCY_");
        nbf_html::show_admin_setting_textbox($row, "thousands_separator", "CURRENCY_");
        nbf_html::show_admin_setting_textbox($row, "decimal_separator", "CURRENCY_");
        nbf_html::show_admin_setting_textbox($row, "currency_format", "CURRENCY_");
        ?>
		</table>
        </div>
        <?php
        $tab_settings->add_tab_content("advanced", ob_get_clean());
        $tab_settings->end_tab_group();
        ?>

		</form>
		<?php
	}
}