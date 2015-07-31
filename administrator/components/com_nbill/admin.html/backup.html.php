<?php
/**
* HTML Output for backup/restore feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillBackupScreens
{
	public static function showBackup()
	{
		?>
		<script type="text/javascript">
		function restore_db()
		{
			if (confirm('<?php echo RESTORE_ARE_YOU_SURE; ?>'))
			{
				document.adminForm.task.value='dorestore';
                document.adminForm.submit();
                return true;
			}
		}
		</script>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "backup"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_BACKUP_RESTORE_TITLE; ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		}?>

		<p align="left"><?php echo NBILL_BACKUP_RESTORE_INTRO; ?></p>
        <p align="left"><?php echo NBILL_BACKUP_SMALL_ONLY ?></p>
		<h3 style="color:#ff0000"><?php echo NBILL_BACKUP_RESTORE_WARNING; ?></h3>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" enctype="multipart/form-data">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="backup" />
        <input type="hidden" name="task" value="dorestore" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

		<?php nbf_html::add_filters(); ?>

        <div class="rounded-table">
		<table class="adminform">
		<tr>
			<th class="nbill-setting-caption">
				<?php echo NBILL_BACKUP; ?>
			</th>
		</tr>
		<tr>
			<td class="list-value">
				<?php echo NBILL_BACKUP_WHAT; ?><br />
				<ul>
					<li><a style="text-decoration:underline;font-weight:bold" href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=backup&task=dobackup&what=nbill"><?php echo NBILL_BACKUP_BILLING; ?></a></li>
					<li><a style="text-decoration:underline;font-weight:bold" href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=backup&task=dobackup&what=all"><?php echo NBILL_BACKUP_ALL; ?></a></li>
				</ul>
			</td>
		</tr>
		</table>
        </div>

		<br />

        <div class="rounded-table">
		<table class="adminform">
		<tr>
			<th colspan="2">
				<?php echo NBILL_RESTORE; ?>
			</th>
		</tr>
		<tr>
			<td class="nbill-setting-caption">
				<?php echo NBILL_RESTORE_WHAT; ?>
			</td>
			<td class="list-value">
				<input type="radio" class="nbill_form_input" name="what" id="what_nbill" value="nbill" checked="checked" /><label class="nbill_form_label" for="what_nbill"><?php echo NBILL_RESTORE_BILLING; ?></label><br />
				<input type="radio" class="nbill_form_input" name="what" id="what_all" value="all" /><label for="what_all" class="nbill_form_label"><?php echo NBILL_RESTORE_ALL; ?></label>
                <?php nbf_html::show_static_help(NBILL_INSTR_RESTORE_WHAT, "what_help"); ?>
			</td>
		</tr>
		<tr>
			<td class="nbill-setting-caption">
				<?php echo NBILL_BACKUP_FILE; ?>
			</td>
			<td class="list-value">
				<input type="file" name="backup_file" id="backup_file" />
                <?php nbf_html::show_static_help(NBILL_INSTR_BACKUP_FILE, "backup_file_help"); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:center">
				<input class="button btn" type="submit" name="restore" id="restore" value=" <?php echo NBILL_RESTORE; ?> " onclick="return confirm('<?php echo RESTORE_ARE_YOU_SURE; ?>');" />
			</td>
		</tr>
		</table>
        </div>

		</form>

		<?php
	}
}