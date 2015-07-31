<?php
/**
* HTML output for transaction search feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class invTxSearchScreens
{
	public static function showTxSearch()
	{
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "tx_search"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_TX_SEARCH_TITLE; ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		} ?>

        <form action="<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>" method="post" name="adminForm" enctype="multipart/form-data">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="tx_search" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="1" />
        <input type="hidden" name="hide_billing_menu" value="1" />
		<?php nbf_html::add_filters(); ?>

		<p align="left"><?php echo NBILL_TX_SEARCH_INTRO; ?></p>

		<table class="adminlist table table-striped">
		<tr>
			<td>
				<?php echo NBILL_TX_SEARCH_ID; ?>&nbsp;<input type="text" name="g_tx_id" id="g_tx_id" value="<?php echo nbf_common::get_param($_REQUEST, 'g_tx_id'); ?>" style="margin-bottom:0" />
				<input type="submit" class="button btn" name="do_search" id="do_search" value="<?php echo NBILL_TX_SEARCH_SEARCH; ?>" />
			</td>
		</tr>
		</table>

		<br />
		</form>
    	<?php
	}
}