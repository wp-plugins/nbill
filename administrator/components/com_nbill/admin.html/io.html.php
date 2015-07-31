<?php
/**
* HTML output for import/export features
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillIO
{
	public static function showIOClients()
	{
		?>
		<script type="text/javascript">
		function import_from_users()
		{
			if (confirm('<?php echo IMPORT_USERS_ARE_YOU_SURE; ?>'))
			{
                document.adminForm.task.value='douserimport';
                document.adminForm.submit();
				return true;
			}
		}
		function import_from_csv()
		{
			if (confirm('<?php echo IMPORT_CSV_ARE_YOU_SURE; ?>'))
			{
                document.adminForm.task.value='docsvimport';
                document.adminForm.submit();
				return true;
			}
		}
		</script>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "clients"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_CLIENT_IO_TITLE; ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		} ?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" enctype="multipart/form-data">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="io" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<?php nbf_html::add_filters(); ?>

		<p align="left"><?php echo NBILL_CLIENT_IO_INTRO; ?></p>

        <div class="rounded-table">
		    <table class="adminlist">
		    <tr class="nbill_tr_no_highlight">
			    <th colspan="3" align="left">
				    <?php echo NBILL_IMPORT; ?>
			    </th>
		    </tr>
		    <tr class="nbill_tr_no_highlight">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_IMPORT_CLIENTS_USERS; ?>:
			    </td>
			    <td class="nbill-setting-value">
				    <input type="button" class="button btn" name="import_all_users" value="<?php echo NBILL_IMPORT_ALL_USERS; ?>" onclick="if (confirm('<?php echo IMPORT_USERS_ARE_YOU_SURE; ?>')) {document.adminForm.task.value='import_all_clients_users';document.adminForm.submit();}" />
				    <input type="button" class="button btn" name="import_select_users" value="<?php echo NBILL_IMPORT_SELECT_USERS; ?>" onclick="document.adminForm.task.value='import_select_clients_users';document.adminForm.submit();" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_IMPORT_CLIENTS_USERS, "import_users_help"); ?>
			    </td>
		    </tr>
		    <tr class="nbill_tr_no_highlight">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_IMPORT_CLIENTS_CSV; ?>:
			    </td>
			    <td class="nbill-setting-value">
				    <input type="file" name="import_clients_csv_file" />&nbsp;&nbsp;<input type="button" class="button btn" name="import_csv" value="<?php echo NBILL_IMPORT_CSV; ?>" onclick="if (confirm('<?php echo IMPORT_CSV_ARE_YOU_SURE; ?>')) {document.adminForm.task.value='import_clients_csv';document.adminForm.submit();}" />
                    <?php nbf_html::show_static_help(sprintf(NBILL_INSTR_IMPORT_CLIENTS_CSV, "<span style=\"font-weight:bold;color:#bb0000;\">" . NBILL_IMPORTANT . "</span>", "<a href=\"" . nbf_cms::$interop->admin_popup_page_prefix . "&action=io&task=client_csv_help&hide_billing_menu=1\" target=\"_blank\" onclick=\" window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=io&task=client_csv_help&hide_billing_menu=1', '" . uniqid() . "', 'width=700,height=500,top=150,left=150,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no');return false;\">" . NBILL_IMPORT_CLIENTS_CSV_HELP . "</a>"), "import_clients_csv_file_help"); ?>
			    </td>
		    </tr>
		    </table>
        </div>

		<br />

        <div class="rounded-table">
		    <table class="adminlist">
		    <tr class="nbill_tr_no_highlight">
			    <th colspan="3" align="left">
				    <?php echo NBILL_EXPORT; ?>
			    </th>
		    </tr>
		    <tr class="nbill_tr_no_highlight">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_EXPORT_CLIENTS_CSV; ?>:
			    </td>
			    <td class="nbill-setting-value">
				    <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=io&task=export_clients_csv" id="export_link" onclick="document.getElementById('export_link').href='<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=io&task=export_clients_csv';return true;"><?php echo NBILL_EXPORT_CSV; ?></a>
                    <?php nbf_html::show_static_help(NBILL_INSTR_EXPORT_CLIENTS_CSV, "export_link_help"); ?>
			    </td>
		    </tr>
		    </table>
        </div>

		</form>

		<?php
	}

	public static function selectUsers($rows, $pagination)
	{
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "clients"); ?>>
				<?php echo NBILL_SELECT_USERS_TITLE; ?>
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
        <input type="hidden" name="action" value="io" />
        <input type="hidden" name="task" value="import_select_clients_users" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

		<p align="left"><?php echo NBILL_SELECT_USERS_INTRO; ?></p>

        <div class="rounded-table">
            <table class="adminlist">
            <tr class="nbill-admin-title-row">
                <th class="selector">
			    #
			    </th>
                <th class="selector">
                    <input type="checkbox" name="check_all" value="" onclick="for(var i=0; i<<?php echo count($rows); ?>;i++) {document.getElementById('cb' + i).checked=this.checked;} document.adminForm.box_checked.value=this.checked;" />
			    </th>
			    <th class="selector responsive-cell priority">
				    <?php echo NBILL_USER_ID; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_USER_NAME; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_USER_PERSON_NAME; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_EMAIL_ADDRESS; ?>
			    </th>
		    </tr>
		    <?php
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];
				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->user_id);
				    echo "</td><td class=\"selector\">$checked</td>";
				    echo "<td class=\"list-value responsive-cell priority\">" . $row->user_id . "</td>";
				    echo "<td class=\"list-value\">" . $row->username . "</td>";
				    echo "<td class=\"list-value\">" . $row->name . "</td>";
				    echo "<td class=\"list-value word-breakable\">" . $row->email . "</td>";
				    echo "</tr>";
			    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="6" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		</form>
		<?php
	}
}