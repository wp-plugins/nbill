<?php
/**
* Output the main nBill administrator toolbar
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nbill_TOOLBAR
{
	public static function defaultButtons($warn_delete_contacts = false)
	{
		$image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
		?>
		<table cellpadding="0" cellspacing="0" border="0" id="toolbar">
		<tr valign="middle" align="center">
			<!-- Add button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="document.adminForm.task.value='new';document.adminForm.submit();return false;">
					<img src="<?php echo $image_path; ?>add.png" alt="<?php echo NBILL_TB_NEW; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_NEW; ?>
				</a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<!-- Edit button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_EDIT; ?>');} else {document.adminForm.task.value='edit';document.adminForm.submit();return false;}">
					<img src="<?php echo $image_path ; ?>edit.png" alt="<?php echo NBILL_TB_EDIT; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_EDIT; ?>
				</a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_DELETE; ?>');} else if (confirm('<?php echo NBILL_TB_DELETE_ARE_YOU_SURE; if ($warn_delete_contacts) {echo NBILL_TB_DELETE_CONTACTS_TOO;} ?>')){document.adminForm.task.value='delete';document.adminForm.submit();return false;}">
					<img src="<?php echo $image_path ; ?>delete.png" alt="<?php echo NBILL_TB_DELETE; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_DELETE; ?>
				</a>
			</td>
		</tr>
		</table>
		<?php
	}

    public static function potential_clientButtons()
    {
        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
        ?>
        <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
        <tr valign="middle" align="center">
            <!-- Promote button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_PROMOTE; ?>');} else if (confirm('<?php echo NBILL_TB_PROMOTE_ARE_YOU_SURE;?>')){document.adminForm.task.value='promote';document.adminForm.submit();return false;}">
                    <img src="<?php echo $image_path; ?>promote.png" alt="<?php echo NBILL_TB_PROMOTE; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_PROMOTE; ?>
                </a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Add button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="document.adminForm.task.value='new';document.adminForm.submit();return false;">
                    <img src="<?php echo $image_path; ?>add.png" alt="<?php echo NBILL_TB_NEW; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_NEW; ?>
                </a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Edit button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_EDIT; ?>');} else {document.adminForm.task.value='edit';document.adminForm.submit();return false;}">
                    <img src="<?php echo $image_path ; ?>edit.png" alt="<?php echo NBILL_TB_EDIT; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_EDIT; ?>
                </a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <td>
                <a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_DELETE; ?>');} else if (confirm('<?php echo NBILL_TB_DELETE_ARE_YOU_SURE . NBILL_TB_DELETE_CONTACTS_TOO;?>')){document.adminForm.task.value='delete';document.adminForm.submit();return false;}">
                    <img src="<?php echo $image_path ; ?>delete.png" alt="<?php echo NBILL_TB_DELETE; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_DELETE; ?>
                </a>
            </td>
        </tr>
        </table>
        <?php
    }

    public static function profile_fieldButtons()
    {
        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
        ?>
        <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
        <tr valign="middle" align="center">
            <!-- Add button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="document.adminForm.task.value='new';document.adminForm.submit();return false;">
                    <img src="<?php echo $image_path; ?>add.png" alt="<?php echo NBILL_TB_NEW; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_NEW; ?>
                </a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Edit button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_EDIT; ?>');} else {document.adminForm.task.value='edit';document.adminForm.submit();return false;}">
                    <img src="<?php echo $image_path ; ?>edit.png" alt="<?php echo NBILL_TB_EDIT; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_EDIT; ?>
                </a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <td>
                <a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_DELETE; ?>');} else if (confirm('<?php echo NBILL_TB_DELETE_ARE_YOU_SURE;?>')){var in_use=false;var cbs=document.getElementsByTagName('input');for(var cb_index in cbs){var cb=cbs[cb_index];if(cb.id && cb.id.indexOf('cb')==0 && cb.type=='checkbox' && cb.checked){if(document.getElementById('in_use_' + cb.id.substr(2)) && document.getElementById('in_use_' + cb.id.substr(2)).value!=''){in_use=true;break;}}}if(in_use){if(confirm('<?php echo NBILL_TB_UNPUBLISH_ON_FORMS; ?>')){document.getElementById('apply_to_existing').value='1';}}document.adminForm.task.value='delete';document.adminForm.submit();return false;}">
                    <img src="<?php echo $image_path ; ?>delete.png" alt="<?php echo NBILL_TB_DELETE; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_DELETE; ?>
                </a>
            </td>
        </tr>
        </table>
        <?php
    }

	public static function orderButtons()
	{
		$image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
		$default_date = nbf_common::nb_date("Y/m/d", nbf_common::nb_time());
		?>
		<table cellpadding="0" cellspacing="0" border="0" id="toolbar">
		<tr valign="middle" align="center">
			<!-- Generate button -->
			<td>
				<script type="text/javascript">
				<?php echo get_prompt_js(); ?>
				function promptCallback(val)
				{
					if (val.length > 0)
					{
						document.adminForm.task.value='generate-' + val;
                        document.adminForm.submit();
					}
				}
				</script>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_GENERATE; ?>');}else{IEprompt('<?php echo NBILL_ENTER_OVERRIDE_DATE; ?>','<?php echo $default_date; ?>')}">
					<img src="<?php echo $image_path ; ?>generate.png" alt="<?php echo NBILL_TB_GENERATE; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_GENERATE; ?>
				</a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<!-- Add button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="document.adminForm.task.value='new';document.adminForm.submit();return false;">
					<img src="<?php echo $image_path; ?>add.png" alt="<?php echo NBILL_TB_NEW; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_NEW; ?>
				</a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<!-- Edit button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_EDIT; ?>');} else {document.adminForm.task.value='edit';document.adminForm.submit();return false;}">
					<img src="<?php echo $image_path ; ?>edit.png" alt="<?php echo NBILL_TB_EDIT; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_EDIT; ?>
				</a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_DELETE; ?>');} else if (confirm('<?php echo NBILL_TB_DELETE_ARE_YOU_SURE;?>')){document.adminForm.task.value='delete';document.adminForm.submit();return false;}">
					<img src="<?php echo $image_path ; ?>delete.png" alt="<?php echo NBILL_TB_DELETE; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_DELETE; ?>
				</a>
			</td>
		</tr>
		</table>
		<?php
	}

	public static function invoiceButtons()
	{
		$image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
		$default_date = nbf_common::nb_date("Y/m/d", nbf_common::nb_time());
		?>
		<table cellpadding="0" cellspacing="0" border="0" id="toolbar">
		<tr valign="middle" align="center">
			<?php if (nbf_common::get_param($_REQUEST, 'action') == "invoices") { ?>
			<!-- Generate button -->
			<td>
				<script type="text/javascript">
				<?php echo get_prompt_js(); ?>
				function promptCallback(val)
				{
					if (val.length > 0)
					{
						document.adminForm.task.value='generate-' + val;
                        document.adminForm.submit();
					}
				}
				</script>
				<a class="nbill-toolbar" href="#" onclick="IEprompt('<?php echo NBILL_ENTER_OVERRIDE_DATE; ?>','<?php echo $default_date; ?>')">
					<img src="<?php echo $image_path ; ?>generate.png" alt="<?php echo NBILL_TB_GENERATE_ALL; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_GENERATE_ALL; ?>
				</a>
			</td>
			<?php
			}
			if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'vendor_filter')) > 0 && nbf_common::get_param($_POST,'vendor_filter') > -1)
			{
				?>
				<!-- Spacer -->
				<td>&nbsp;</td>
				<!-- HTML Preview -->
				<td>
					<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_PREVIEW ?>');}else{var checked_items='';var checkboxes=document.getElementsByTagName('input');for(var i=0;i<=checkboxes.length;i++){if (checkboxes[i]!=null && checkboxes[i].id.substr(0,2)=='cb'){if(checkboxes[i].checked){checked_items+=checkboxes[i].value+',';}}}window.open('<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=invoices&task=printpreviewpopup&hidemainmenu=1&items='+checked_items, '<?php echo nbf_common::nb_time(); ?>', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no')}">
						<img src="<?php echo $image_path ; ?>preview-html.png" alt="<?php echo NBILL_TB_PREVIEW_HTML; ?>" align="middle" border="0" />
						<br /><?php echo NBILL_TB_PREVIEW_HTML; ?>
					</a>
				</td>
				<!-- Spacer -->
				<td>&nbsp;</td>
				<?php
				if (nbf_common::pdf_writer_available())
				{
					?>
					<td>
						<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_PREVIEW; ?>');}else{var checked_items='';var checkboxes=document.getElementsByTagName('input');for(var i=0;i<=checkboxes.length;i++){if (checkboxes[i]!=null && checkboxes[i].id.substr(0,2)=='cb'){if(checkboxes[i].checked){checked_items+=checkboxes[i].value+',';}}}window.open('<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=invoices&task=pdfpopup&hidemainmenu=1&items='+checked_items, '<?php echo nbf_common::nb_time(); ?>', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=yes,directories=no,status=yes,menubar=yes,copyhistory=yes')}">
							<img src="<?php echo $image_path ; ?>preview-pdf.png" alt="<?php echo NBILL_TB_PREVIEW_PDF; ?>" align="middle" border="0" />
							<br /><?php echo NBILL_TB_PREVIEW_PDF; ?>
						</a>
					</td>
					<?php
				}
				else
				{
					echo "<td width=\"100px\">" . NBILL_DOMPDF_NOT_INSTALLED . "</td>";
				}
			}
			else
			{
				echo "<td width=\"100px\">" . NBILL_SELECT_VENDOR_FOR_PRINT . "</td>";
			}
			?>
			<!-- Spacer -->
			<td width="30">&nbsp;</td>

			<!-- Add button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="document.adminForm.task.value='new';document.adminForm.submit();return false;">
					<img src="<?php echo $image_path; ?>add.png" alt="<?php echo NBILL_TB_NEW; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_NEW; ?>
				</a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<!-- Edit button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_EDIT; ?>');} else {document.adminForm.task.value='edit';document.adminForm.submit();return false;}">
					<img src="<?php echo $image_path ; ?>edit.png" alt="<?php echo NBILL_TB_EDIT; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_EDIT; ?>
				</a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_DELETE; ?>');} else if (confirm('<?php echo NBILL_TB_DELETE_ARE_YOU_SURE;?>')){document.adminForm.task.value='delete';document.adminForm.submit();return false;}">
					<img src="<?php echo $image_path ; ?>delete.png" alt="<?php echo NBILL_TB_DELETE; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_DELETE; ?>
				</a>
			</td>
		</tr>
		</table>
		<?php
	}

	public static function editButtons($show_copy = false, $show_new = false)
	{
		$_GET['hidemainmenu'] = 1;
		$image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
		?>
		<table cellpadding="0" cellspacing="0" border="0" id="toolbar">
		<tr valign="middle" align="center">
            <!-- Apply button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="nbill_submit_task('apply');return false;">
					<img src="<?php echo $image_path ; ?>apply.png" alt="<?php echo NBILL_TB_APPLY; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_APPLY;?>
				</a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
            <?php if ($show_copy)
            { ?>
            <!-- Save copy button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('save_copy');return false;">
                    <img src="<?php echo $image_path ; ?>save-copy.png" alt="<?php echo NBILL_TB_SAVE_COPY; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_SAVE_COPY;?>
                </a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <?php } ?>
			<!-- Save button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="nbill_submit_task('save');return false;">
					<img src="<?php echo $image_path ; ?>save.png" alt="<?php echo NBILL_TB_SAVE; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_SAVE;?></a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
            <!-- Cancel button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="nbill_submit_task('cancel');return false;">
					<img src="<?php echo $image_path ; ?>cancel.png" alt="<?php echo NBILL_TB_CANCEL; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_CANCEL;?></a>
			</td>
		</tr>
		</table>
		<?php
	}

    public static function registrationButtons()
    {
        $_GET['hidemainmenu'] = 1;
        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
        ?>
        <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
        <tr valign="middle" align="center">
            <!-- Save button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('save');return false;">
                    <img src="<?php echo $image_path ; ?>save.png" alt="<?php echo NBILL_TB_SAVE; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_SAVE;?></a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Cancel button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('cancel');return false;">
                    <img src="<?php echo $image_path ; ?>cancel.png" alt="<?php echo NBILL_TB_CANCEL; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_CANCEL;?></a>
            </td>
        </tr>
        </table>
        <?php
    }

    public static function supporting_docsButtons()
    {
        $_GET['hidemainmenu'] = 1;
        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
        ?>
        <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
        <tr valign="middle" align="center">
            <!-- New Folder button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&task=new_folder','','width=500,height=200');return false;">
                    <img src="<?php echo $image_path ; ?>new_folder.png" alt="<?php echo NBILL_TB_NEW_FOLDER; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_NEW_FOLDER;?></a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Upload button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&task=upload_file&current_path=' + encodeURIComponent(document.getElementById('supp_docs_current_path').value),'','width=500,height=400');return false;">
                    <img src="<?php echo $image_path ; ?>upload.png" alt="<?php echo NBILL_TB_UPLOAD; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_UPLOAD;?></a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Upload button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="document.adminForm.submit();return false;">
                    <img src="<?php echo $image_path ; ?>refresh.png" alt="<?php echo NBILL_TB_REFRESH; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_REFRESH;?></a>
            </td>
            <?php if (nbf_globals::$popup)
            { ?>
                <!-- Spacer -->
                <td>&nbsp;</td>
                <!-- Cancel button -->
                <td>
                    <a class="nbill-toolbar" href="#" onclick="window.close();return false;">
                        <img src="<?php echo $image_path ; ?>cancel.png" alt="<?php echo NBILL_TB_CANCEL; ?>" align="middle" border="0" />
                        <br /><?php echo NBILL_TB_CANCEL;?></a>
                </td>
            <?php } ?>
        </tr>
        </table>
        <?php
    }

    public static function transactionEditButtons()
    {
        $_GET['hidemainmenu'] = 1;
        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
        ?>
        <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
        <tr valign="middle" align="center">
            <?php $tx_id = intval(nbf_common::get_param($_REQUEST, 'cid'));
            if (!$tx_id)
            {
                $tx_id = intval(nbf_common::get_param($_REQUEST, 'id'));
            }
            if ($tx_id)
            { ?>
            <!-- Printer friendly button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&action=<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>&cid=<?php echo $tx_id; ?>&task=printer_friendly&hidemainmenu=1&hide_billing_menu=1', '<?php echo uniqid(); ?>', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;">
                    <img src="<?php echo $image_path ; ?>print.png" alt="<?php echo NBILL_TB_PRINTER_FRIENDLY; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_PRINTER_FRIENDLY;?>
                </a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <?php } ?>
            <!-- Apply button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('apply');return false;">
                    <img src="<?php echo $image_path ; ?>apply.png" alt="<?php echo NBILL_TB_APPLY; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_APPLY;?>
                </a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Save button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('save');return false;">
                    <img src="<?php echo $image_path ; ?>save.png" alt="<?php echo NBILL_TB_SAVE; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_SAVE;?></a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Cancel button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('cancel');return false;">
                    <img src="<?php echo $image_path ; ?>cancel.png" alt="<?php echo NBILL_TB_CANCEL; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_CANCEL;?></a>
            </td>
        </tr>
        </table>
        <?php
    }

    public static function incomeMultiButtons()
    {
        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
        ?>
        <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
        <tr valign="middle" align="center">
            <!-- Generate button -->
            <td id="nbill_toolbar_generate_button">
                <a class="nbill-toolbar" href="#" onclick="if(confirm('<?php echo NBILL_TB_MULTI_INCOME_GENERATE_WARNING ?>'.replace('%s', document.getElementById('invoice_count').value))){nbill_submit_task('do_multi_generate');}return false;">
                    <img src="<?php echo $image_path ; ?>generate.png" alt="<?php echo NBILL_TB_GENERATE; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_GENERATE; ?>
                </a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Cancel button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('cancel');return false;">
                    <img src="<?php echo $image_path ; ?>cancel.png" alt="<?php echo NBILL_TB_CANCEL; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_CANCEL;?></a>
            </td>
        </tr>
        </table>
        <?php
    }

    public static function favouriteButtons()
    {
        $_GET['hidemainmenu'] = 1;
        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
        ?>
        <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
        <tr valign="middle" align="center">
            <!-- Reset button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="if (confirm('<?php echo NBILL_TB_RESET_ARE_YOU_SURE; ?>')){nbill_submit_task('reset');}return false;">
                    <img src="<?php echo $image_path ; ?>reset.png" alt="<?php echo NBILL_TB_RESET; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_RESET;?>
                </a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Apply button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('apply');return false;">
                    <img src="<?php echo $image_path ; ?>apply.png" alt="<?php echo NBILL_TB_APPLY; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_APPLY;?>
                </a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Save button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('save');return false;">
                    <img src="<?php echo $image_path ; ?>save.png" alt="<?php echo NBILL_TB_SAVE; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_SAVE;?></a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Cancel button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('cancel');return false;">
                    <img src="<?php echo $image_path ; ?>cancel.png" alt="<?php echo NBILL_TB_CANCEL; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_CANCEL;?></a>
            </td>
        </tr>
        </table>
        <?php
    }

    public static function quoteButtons($show_copy = false, $show_new = false)
    {
        $_GET['hidemainmenu'] = 1;
        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
        ?>
        <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
        <tr valign="middle" align="center">
            <?php
            if (nbf_common::get_param($_REQUEST, 'cid'))
            {
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.process.quote.class.php");
                $awaiting = nbf_quote::check_awaiting_payment(intval(nbf_common::get_param($_REQUEST, 'cid')), true);
                if ($awaiting)
                {
                    ?>
                    <!-- Offline Payment button -->
                    <td>
                        <a class="nbill-toolbar" href="#" onclick="if (confirm('<?php echo NBILL_QUOTE_PAY_OFFLINE_GENERATE; ?>')){nbill_submit_task('paid_offline');}return false;" title="<?php echo NBILL_TB_QUOTE_PAID_OFFLINE_DESC; ?>">
                            <img src="<?php echo $image_path ; ?>pay-offline.png" alt="<?php echo NBILL_TB_QUOTE_PAID_OFFLINE; ?>" align="middle" border="0" />
                            <br /><?php echo NBILL_TB_QUOTE_PAID_OFFLINE;?>
                        </a>
                    </td>
                    <!-- Spacer -->
                    <td>&nbsp;</td>
                    <?php
                }
            }
            ?>
            <!-- Generate button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="if (confirm('<?php echo NBILL_TB_GENERATE_SAVE_FIRST; ?>')){nbill_submit_task('apply_and_generate');} else {nbill_submit_task('generate');}return false;">
                    <img src="<?php echo $image_path ; ?>generate.png" alt="<?php echo NBILL_TB_GENERATE; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_GENERATE;?>
                </a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Apply button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('apply');return false;">
                    <img src="<?php echo $image_path ; ?>apply.png" alt="<?php echo NBILL_TB_APPLY; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_APPLY;?>
                </a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <?php if ($show_copy)
            { ?>
            <!-- Save copy button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('save_copy');return false;">
                    <img src="<?php echo $image_path ; ?>save-copy.png" alt="<?php echo NBILL_TB_SAVE_COPY; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_SAVE_COPY;?>
                </a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <?php } ?>
            <!-- Save button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('save');return false;">
                    <img src="<?php echo $image_path ; ?>save.png" alt="<?php echo NBILL_TB_SAVE; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_SAVE;?></a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <!-- Cancel button -->
            <td>
                <a class="nbill-toolbar" href="#" onclick="nbill_submit_task('cancel');return false;">
                    <img src="<?php echo $image_path ; ?>cancel.png" alt="<?php echo NBILL_TB_CANCEL; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_CANCEL;?></a>
            </td>
        </tr>
        </table>
        <?php
    }

	public static function pendingShowButtons()
	{
		$image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
		$_GET['hidemainmenu'] = 1;
		?>
		<table cellpadding="0" cellspacing="0" border="0" id="toolbar">
		<tr valign="middle" align="center">
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (confirm('<?php echo NBILL_ACTIVATE_ARE_YOU_SURE;?>')){document.adminForm.task.value='activate';document.adminForm.submit();return false;}">
					<img src="<?php echo $image_path ; ?>activate.png" alt="<?php echo NBILL_TB_ACTIVATE; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_ACTIVATE_PENDING_ORDER; ?></a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<!-- Cancel button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="document.adminForm.task.value='cancel';document.adminForm.submit();return false;">
					<img src="<?php echo $image_path ; ?>cancel.png" alt="<?php echo NBILL_TB_CANCEL; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_CANCEL;?></a>
			</td>
		</tr>
		</table>
		<?php
	}

	public static function pendingButtons()
	{
		$image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
		?>
		<table cellpadding="0" cellspacing="0" border="0" id="toolbar">
		<tr valign="middle" align="center">
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_SELECT_PENDING_ORDER; ?>');} else if (confirm('<?php echo NBILL_ACTIVATE_ARE_YOU_SURE;?>')){document.adminForm.task.value='activate';document.adminForm.submit();return false;}">
					<img src="<?php echo $image_path ; ?>activate.png" alt="<?php echo NBILL_TB_ACTIVATE; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_ACTIVATE_PENDING_ORDER; ?></a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_DELETE; ?>'); } else if (confirm('<?php echo NBILL_TB_DELETE_ARE_YOU_SURE;?>')){document.adminForm.task.value='delete';document.adminForm.submit();return false;}">
					<img src="<?php echo $image_path ; ?>delete.png" alt="<?php echo NBILL_TB_DELETE; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_DELETE; ?>
				</a>
			</td>
		</tr>
		</table>
		<?php
	}

	public static function gatewayButtons()
	{
		$image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
		?>
		<table cellpadding="0" cellspacing="0" border="0" id="toolbar">
		<tr valign="middle" align="center">
			<!-- Add button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="document.adminForm.task.value='new';document.adminForm.submit();return false;">
					<img src="<?php echo $image_path; ?>add.png" alt="<?php echo NBILL_TB_NEW; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_NEW; ?>
				</a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<!-- Edit button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_EDIT; ?>'); } else {document.adminForm.task.value='edit';document.adminForm.submit();return false;}">
					<img src="<?php echo $image_path ; ?>edit.png" alt="<?php echo NBILL_TB_EDIT; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_EDIT; ?>
				</a>
			</td>
		</tr>
		</table>
		<?php
	}

	public static function incomeButtons($transaction_no_generation = false, $use_master = false)
	{
		$image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
		?>
		<table cellpadding="0" cellspacing="0" border="0" id="toolbar">
		<tr valign="middle" align="center">
			<?php
			if ($transaction_no_generation)
			{
				if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'vendor_filter')) > 0 && nbf_common::get_param($_POST,'vendor_filter') > -1)
				{
					?>
					<td>
					<script type="text/javascript">
					<?php echo get_prompt_js(); ?>
					function promptCallback(val)
					{
						if (val.length > 0)
						{
							document.adminForm.task.value='generatereceiptnos-' + val;
                            document.adminForm.submit();
						}
					}
					</script>
					<a class="nbill-toolbar" href="#" onclick="<?php if ($use_master) { echo "if(confirm('" . NBILL_GENERATE_MASTER . "')) {";} ?>IEprompt('<?php echo NBILL_GENERATE_RECEIPTS_UP_TO;?>', '<?php echo nbf_common::nb_date("Y/m/d", nbf_common::nb_time()); ?>');<?php if ($use_master) { echo "}";} ?>">
						<img src="<?php echo $image_path ; ?>generate.png" alt="<?php echo NBILL_GENERATE_RECEIPT_NOS; ?>" align="middle" border="0" />
						<br /><?php echo NBILL_GENERATE_RECEIPT_NOS; ?></a>
					</td>
				<?php }
				else
				{
					echo "<td width=\"100px\">" . NBILL_SELECT_VENDOR_FOR_RECEIPT_NO_GEN . "</td>";
				}
			}
			?>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<!-- Add button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="document.adminForm.task.value='new';document.adminForm.submit();return false;">
					<img src="<?php echo $image_path; ?>add.png" alt="<?php echo NBILL_TB_NEW; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_NEW; ?>
				</a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<!-- Edit button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_EDIT; ?>');} else {document.adminForm.task.value='edit';document.adminForm.submit();return false;}">
					<img src="<?php echo $image_path ; ?>edit.png" alt="<?php echo NBILL_TB_EDIT; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_EDIT; ?>
				</a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_DELETE; ?>'); } else if (confirm('<?php echo NBILL_TB_DELETE_ARE_YOU_SURE;?>')){document.adminForm.task.value='delete';document.adminForm.submit();return false;}">
					<img src="<?php echo $image_path ; ?>delete.png" alt="<?php echo NBILL_TB_DELETE; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_DELETE; ?>
				</a>
			</td>
		</tr>
		</table>
		<?php
	}

	public static function expenditureButtons($payment_no_generation = false, $use_master = false)
	{
		$image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
		?>
		<table cellpadding="0" cellspacing="0" border="0" id="toolbar">
		<tr valign="middle" align="center">
			<?php
			if ($payment_no_generation)
			{
				if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'vendor_filter')) > 0 && nbf_common::get_param($_POST,'vendor_filter') > -1)
				{
					?>
					<td>
					<script type="text/javascript">
						<?php echo get_prompt_js(); ?>
						function promptCallback(val)
						{
							if (val.length > 0)
							{
								document.adminForm.task.value='generatepaymentnos-' + val;
                                document.adminForm.submit();
							}
						}
					</script>
					<a class="nbill-toolbar" href="#" onclick="<?php if ($use_master) { echo "if(confirm('" . NBILL_GENERATE_MASTER . "')) {";} ?>IEprompt('<?php echo NBILL_GENERATE_PAYMENTS_UP_TO;?>', '<?php echo nbf_common::nb_date("Y/m/d", nbf_common::nb_time()); ?>');<?php if ($use_master) { echo "}";} ?>">
						<img src="<?php echo $image_path ; ?>generate.png" alt="<?php echo NBILL_GENERATE_PAYMENT_NOS; ?>" align="middle" border="0" />
						<br /><?php echo NBILL_GENERATE_PAYMENT_NOS; ?></a>
					</td>
				<?php }
				else
				{
					echo "<td width=\"100px\">" . NBILL_SELECT_VENDOR_FOR_PAYMENT_NO_GEN . "</td>";
				}
			}
			?>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<!-- Add button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="document.adminForm.task.value='new';document.adminForm.submit();return false;">
					<img src="<?php echo $image_path; ?>add.png" alt="<?php echo NBILL_TB_NEW; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_NEW; ?>
				</a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<!-- Edit button -->
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_EDIT; ?>');} else {document.adminForm.task.value='edit';document.adminForm.submit();return false;}">
					<img src="<?php echo $image_path ; ?>edit.png" alt="<?php echo NBILL_TB_EDIT; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_EDIT; ?>
				</a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_DELETE; ?>');} else if (confirm('<?php echo NBILL_TB_DELETE_ARE_YOU_SURE;?>')){document.adminForm.task.value='delete';document.adminForm.submit();return false;}">
					<img src="<?php echo $image_path ; ?>delete.png" alt="<?php echo NBILL_TB_DELETE; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_TB_DELETE; ?>
				</a>
			</td>
		</tr>
		</table>
		<?php
	}

	public static function importUsersButtons()
	{
		$image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
		?>
		<table cellpadding="0" cellspacing="0" border="0" id="toolbar">
		<tr valign="middle" align="center">
			<td>
				<a class="nbill-toolbar" href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=io&task=clients&vendor_id=<?php echo nbf_common::get_param($_POST, "import_vendor_id"); ?>">
					<img src="<?php echo $image_path ; ?>back.png" alt="<?php echo NBILL_TB_BACK; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_BACK;?></a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>

			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_IMPORT; ?>');}else{document.adminForm.task.value='import_selected_users';document.adminForm.submit();return false;}">
				<img src="<?php echo $image_path ; ?>add-users.png" alt="<?php echo NBILL_IMPORT; ?>" align="middle" border="0" />
				<br /><?php echo NBILL_IMPORT; ?></a>
			</td>
		</tr>
		</table>
		<?php
	}

	public static function gatewayInstallButtons()
	{
		$image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
		?>
		<table cellpadding="0" cellspacing="0" border="0" id="toolbar">
		<tr valign="middle" align="center">
			<td>
				<a class="nbill-toolbar" href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=gateway">
					<img src="<?php echo $image_path ; ?>back.png" alt="<?php echo NBILL_TB_BACK; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_BACK;?></a>
			</td>
			<!-- Spacer -->
			<td>&nbsp;</td>
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_DELETE;?>');} else if (confirm('<?php echo NBILL_TB_DELETE_ARE_YOU_SURE; ?>')){if (confirm('<?php echo NBILL_UNINSTALL_KEEP_SETTINGS ?>')) {document.adminForm.task.value='uninstall_upgrade';} else {document.adminForm.task.value='uninstall';}document.adminForm.submit();}return false;">
					<img src="<?php echo $image_path ; ?>delete.png" alt="<?php echo NBILL_EXTENSION_UNINSTALL; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_EXTENSION_UNINSTALL; ?></a>
			</td>
		</tr>
		</table>
		<?php
	}

	public static function extensionButtons()
	{
		$image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
		?>
		<table cellpadding="0" cellspacing="0" border="0" id="toolbar">
		<tr valign="middle" align="center">
			<td>
				<a class="nbill-toolbar" href="#" onclick="if (document.adminForm.box_checked.value == 0 || document.adminForm.box_checked.value == 'false'){alert('<?php echo NBILL_TB_SELECT_ITEM_TO_DELETE;?>');} else if (confirm('<?php echo NBILL_TB_DELETE_ARE_YOU_SURE; ?>')){if (confirm('<?php echo NBILL_UNINSTALL_KEEP_SETTINGS ?>')) {document.adminForm.task.value='uninstall_upgrade';} else {document.adminForm.task.value='uninstall';}document.adminForm.submit();}return false;">
					<img src="<?php echo $image_path ; ?>delete.png" alt="<?php echo NBILL_EXTENSION_UNINSTALL; ?>" align="middle" border="0" />
					<br /><?php echo NBILL_EXTENSION_UNINSTALL; ?></a>
			</td>
		</tr>
		</table>
		<?php
	}

    public static function emailLogButtons()
    {
        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
        ?>
        <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
        <tr valign="middle" align="center">
            <?php if (intval(nbf_common::get_param($_REQUEST, 'for_document'))) { ?>
            <td>
                <a class="nbill-toolbar" href="#" onclick="history.go(-1);">
                    <img src="<?php echo $image_path ; ?>back.png" alt="<?php echo NBILL_TB_BACK; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_BACK;?></a>
            </td>
            <!-- Spacer -->
            <td>&nbsp;</td>
            <?php } ?>
            <td>
                <script type="text/javascript">
                <?php echo get_prompt_js(); ?>
                function promptCallback(val)
                {
                    if (val.length > 0)
                    {
                        if (confirm('<?php echo NBILL_CLEAR_DOWN_OLD_SURE; ?>'))
                        {
                            document.adminForm.task.value='clear_old-' + val;
                            document.adminForm.submit();
                        }
                    }
                }
                </script>
                <a class="nbill-toolbar" href="#" onclick="IEprompt('<?php echo NBILL_CLEAR_DOWN_OLD_BEFORE; ?>', '<?php echo nbf_common::nb_date("Y/m/d", nbf_common::nb_strtotime("- 6 Months")); ?>');">
                    <img src="<?php echo $image_path ; ?>delete.png" alt="<?php echo NBILL_TB_DELETE_OLD; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_TB_DELETE_OLD; ?>
                </a>
            </td>
        </tr>
        </table>
        <?php
    }

    public static function editTranslationTableButtons()
    {
        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/";
        ?>
        <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
        <tr valign="middle" align="center">
            <td>
                <a class="nbill-toolbar" href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=translation">
                    <img src="<?php echo $image_path ; ?>back.png" alt="<?php echo NBILL_TB_BACK; ?>" align="middle" border="0" />
                    <br /><?php echo NBILL_BACK;?></a>
            </td>
        </tr>
        </table>
        <?php
    }
}