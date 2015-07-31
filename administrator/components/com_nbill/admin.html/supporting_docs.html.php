<?php
/**
* HTML output for suppliers feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillSupportingDocs
{
	public static function showFiles($path, $files, $attachments = array())
	{
        $date_format = nbf_common::get_date_format();
        $current_path = nbf_common::get_param($_REQUEST, 'supp_docs_current_path', $path, true);
        if (file_exists($current_path))
        {
            $current_path = realpath($current_path);
        }
        ?>
        <table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "supporting_docs"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_SUPP_DOCS_TITLE; ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		} ?>

        <?php if (nbf_globals::$popup)
        {
            ?><p><?php echo NBILL_SUPP_DOCS_ATTACH_INTRO; ?></p><?php
        }
        else
        {
            ?><p><?php echo NBILL_SUPP_DOCS_INTRO; ?></p><?php
        } ?>

        <p><?php echo NBILL_SUPP_DOCS_WARNING; ?></p>

		<form action="<?php echo nbf_globals::$popup ? nbf_cms::$interop->admin_popup_page_prefix : nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="supporting_docs" />
        <input type="hidden" name="task" id="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
        <input type="hidden" name="new_folder_name" id="new_folder_name" value="" />
        <input type="hidden" name="current_file" id="current_file" />
        <input type="hidden" name="sort_by" id="sort_by" value="<?php echo nbf_common::get_param($_REQUEST, 'sort_by'); ?>" />
        <input type="hidden" name="attachment_id" id="attachment_id" value="" />
        <input type="hidden" name="nbill_popup" value="<?php echo nbf_globals::$popup; ?>" />
        <?php if (isset($_REQUEST['tmpl'])) {?><input type="hidden" name="tmpl" value="<?php echo nbf_common::get_param($_REQUEST, 'tmpl'); ?>" /><?php } ?>
        <input type="hidden" name="show_toolbar" value="<?php echo nbf_common::get_param($_REQUEST, 'show_toolbar'); ?>" />
        <input type="hidden" name="use_stylesheet" value="<?php echo nbf_common::get_param($_REQUEST, 'use_stylesheet'); ?>" />
        <input type="hidden" name="hide_billing_menu" value="<?php echo nbf_common::get_param($_REQUEST, 'hide_billing_menu'); ?>" />
        <input type="hidden" name="attach_to_type" value="<?php echo nbf_common::get_param($_REQUEST, 'attach_to_type'); ?>" />
        <input type="hidden" name="attach_to_id" value="<?php echo nbf_common::get_param($_REQUEST, 'attach_to_id'); ?>" />

		<?php echo NBILL_SUPP_DOCS_ROOT_PATH . ' '; nbf_html::show_overlib(NBILL_SUPP_DOCS_ROOT_PATH_DESC) . '&nbsp;'; ?>
        <input type="text" name="supp_docs_root_path" value="<?php echo $path; ?>" style="min-width:50%" />&nbsp;
		<input type="button" class="button btn" onclick="document.adminForm.task.value='save_root_path';document.adminForm.submit();return false;" name="save_path" value="<?php echo NBILL_SUPP_DOCS_SAVE_PATH; ?>" />
        <br />
        <div style="text-align:right;width:100%">
        <?php
        echo NBILL_SUPP_DOCS_SHOW_FILES; ?>
        <input type="radio" class="nbill_form_input" name="show_files" id="show_files_with" onclick="document.adminForm.submit();" value="with"<?php echo nbf_common::get_param($_REQUEST, 'show_files') == 'with' ? ' checked="checked"' : ''; ?> /><label for="show_files_with" class="nbill_form_label"><?php echo NBILL_SUPP_DOCS_SHOW_FILES_WITH; ?></label>
        <input type="radio" class="nbill_form_input" name="show_files" id="show_files_without" onclick="document.adminForm.submit();" value="without"<?php echo nbf_common::get_param($_REQUEST, 'show_files') == 'without' ? ' checked="checked"' : ''; ?> /><label for="show_files_without" class="nbill_form_label"><?php echo NBILL_SUPP_DOCS_SHOW_FILES_WITHOUT; ?></label>
        <input type="radio" class="nbill_form_input" name="show_files" id="show_files_all" onclick="document.adminForm.submit();" value="all"<?php echo nbf_common::get_param($_REQUEST, 'show_files') != 'with' && nbf_common::get_param($_REQUEST, 'show_files') != 'without' ? ' checked="checked"' : ''; ?> /><label for="show_files_all" class="nbill_form_label"><?php echo NBILL_SUPP_DOCS_SHOW_FILES_ALL; ?></label>
        </div>

        <input type="text" disabled="disabled" name="supp_docs_current_path_display" style="font-weight:bold;width:98%;max-width:98%;" value="<?php echo NBILL_SUPP_DOCS_CURRENT_PATH . "&nbsp;" . $current_path; ?>" />
        <input type="hidden" name="supp_docs_current_path" id="supp_docs_current_path" value="<?php echo $current_path; ?>" />

        <div class="rounded-table">
        <table class="adminlist">
		<tr>
			<th class="title">
                <div style="float:right"><a href="#" onclick="document.adminForm.sort_by.value='name_asc';document.adminForm.submit();return false;" title="<?php echo NBILL_SUPP_DOCS_SORT_ASC; ?>"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/ascending<?php if(nbf_common::get_param($_REQUEST, 'sort_by')=='name_asc'){echo '_selected';} ?>.png" alt="<?php echo NBILL_SUPP_DOCS_SORT_ASC; ?>" /></a>&nbsp;&nbsp;&nbsp;<a href="#" onclick="document.adminForm.sort_by.value='name_desc';document.adminForm.submit();return false;" title="<?php echo NBILL_SUPP_DOCS_SORT_DESC; ?>"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/descending<?php if(nbf_common::get_param($_REQUEST, 'sort_by')=='name_desc'){echo '_selected';} ?>.png" alt="<?php echo NBILL_SUPP_DOCS_SORT_DESC; ?>" /></a></div>
				<?php echo NBILL_SUPP_DOCS_FILE_OR_FOLDER_NAME; ?>
			</th>
            <th class="title">
                <div style="float:right"><a href="#" onclick="document.adminForm.sort_by.value='size_asc';document.adminForm.submit();return false;" title="<?php echo NBILL_SUPP_DOCS_SORT_ASC; ?>"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/ascending<?php if(nbf_common::get_param($_REQUEST, 'sort_by')=='size_asc'){echo '_selected';} ?>.png" alt="<?php echo NBILL_SUPP_DOCS_SORT_ASC; ?>" /></a>&nbsp;&nbsp;&nbsp;<a href="#" onclick="document.adminForm.sort_by.value='size_desc';document.adminForm.submit();return false;" title="<?php echo NBILL_SUPP_DOCS_SORT_DESC; ?>"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/descending<?php if(nbf_common::get_param($_REQUEST, 'sort_by')=='size_desc'){echo '_selected';} ?>.png" alt="<?php echo NBILL_SUPP_DOCS_SORT_DESC; ?>" /></a></div>
                <?php echo NBILL_SUPP_DOCS_SIZE; ?>
            </th>
            <th class="title responsive-cell priority">
                <div style="float:right"><a href="#" onclick="document.adminForm.sort_by.value='modified_asc';document.adminForm.submit();return false;" title="<?php echo NBILL_SUPP_DOCS_SORT_ASC; ?>"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/ascending<?php if(nbf_common::get_param($_REQUEST, 'sort_by')=='modified_asc'){echo '_selected';} ?>.png" alt="<?php echo NBILL_SUPP_DOCS_SORT_ASC; ?>" /></a>&nbsp;&nbsp;&nbsp;<a href="#" onclick="document.adminForm.sort_by.value='modified_desc';document.adminForm.submit();return false;" title="<?php echo NBILL_SUPP_DOCS_SORT_DESC; ?>"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/descending<?php if(nbf_common::get_param($_REQUEST, 'sort_by')=='modified_desc'){echo '_selected';} ?>.png" alt="<?php echo NBILL_SUPP_DOCS_SORT_DESC; ?>" /></a></div>
                <?php echo NBILL_SUPP_DOCS_LAST_MODIFIED; ?>
            </th>
            <th class="title">
                <?php echo NBILL_SUPP_DOCS_ATTACHED_TO; ?>
            </th>
            <th class="title">
                <?php echo NBILL_SUPP_DOCS_ACTION; ?>
            </th>
		</tr>
        <?php
        if ($current_path != $path)
        { ?>
            <tr>
                <td><a href="#" onclick="document.getElementById('supp_docs_current_path').value='<?php echo addslashes($current_path . '/..'); ?>';document.adminForm.task.value='open_folder';document.adminForm.submit();return false;"><img border="0" alt="<?php echo NBILL_SUPP_DOCS_UP_ONE_LEVEL; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/folder_closed.png" />..</a></td>
                <td class="list-value">&nbsp;</td><td class="list-value responsive-cell priority">&nbsp;</td><td class="list-value">&nbsp;</td><td class="list-value">&nbsp;</td>
            </tr>
            <?php
        }
        foreach ($files as $file)
        {
            if (is_dir($current_path . "/" . $file['name']))
            {
                ?>
                <tr>
                    <td class="list-value word-breakable">
                        <a href="#" onclick="document.getElementById('supp_docs_current_path').value='<?php echo addslashes($current_path . '/' . $file['name']); ?>';document.adminForm.task.value='open_folder';document.adminForm.submit();return false;"><img style="vertical-align:middle" border="0" alt="" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/folder_closed.png" />&nbsp;<?php echo $file['name']; ?></a>
                    </td>
                    <td class="list-value">
                        &nbsp;
                    </td>
                    <td class="list-value responsive-cell priority">
                        <?php echo date($date_format . " H:i:s", $file['mtime']); ?>
                    </td>
                    <td class="list-value">&nbsp;</td>
                    <td class="list-value">
                        <a href="#" onclick="if(confirm('<?php echo sprintf(NBILL_SUPP_DOCS_DELETE_FOLDER_SURE, $file['name']); ?>')){document.adminForm.current_file.value='<?php echo addslashes($file['name']); ?>';document.adminForm.task.value='delete';document.adminForm.submit();}return false;" title="<?php echo NBILL_DELETE; ?>"><img style="vertical-align:middle" border="0" alt="<?php echo NBILL_DELETE; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file_delete.png" /></a>
                    </td>
                </tr>
                <?php
            }
        }
        foreach ($files as $file)
        {
            if (!is_dir($current_path . "/" . $file['name']))
            {
                ?>
                <tr>
                    <td class="list-value word-breakable">
                        <a href="<?php if (nbf_common::get_param($_REQUEST, 'attach_to_type') && nbf_common::get_param($_REQUEST, 'attach_to_id')){echo '#';} else {echo nbf_cms::$interop->admin_popup_page_prefix; ?>&action=supporting_docs&task=download&file=<?php echo base64_encode($current_path . "/" . $file['name']);} ?>" onclick="<?php if (nbf_common::get_param($_REQUEST, 'attach_to_type') && nbf_common::get_param($_REQUEST, 'attach_to_id')){ ?>document.adminForm.task.value='attach';document.adminForm.current_file.value='<?php echo $file['name']; ?>';setTimeout('if(window.opener.document.adminForm.task.value.length>0){window.opener.document.adminForm.task=\'edit\';if(window.opener.document.adminForm.use_posted_values){window.opener.document.adminForm.use_posted_values.value=\'1\';}}window.opener.document.adminForm.submit();window.close();', 250);document.adminForm.submit();return false;<?php } else { ?>return true;<?php } ?>"><img style="vertical-align:middle" border="0" alt="" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file.png" />&nbsp;<?php echo $file['name']; ?></a>
                        <?php if (nbf_common::get_param($_REQUEST, 'attach_to_type') && nbf_common::get_param($_REQUEST, 'attach_to_id'))
                        {
                            ?>
                            <a href="<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&action=supporting_docs&task=download&file=<?php echo base64_encode($current_path . "/" . $file['name']); ?>"><img style="vertical-align:middle" border="0" alt="" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/preview.gif" /></a>
                            <?php
                        } ?>
                    </td>
                    <td class="list-value">
                        <?php echo intval($file['size'] / 1024) == 0 ? ($file['size'] > 0 ? '< 1' : 0) : intval($file['size'] / 1024); ?> KB
                    </td>
                    <td class="list-value responsive-cell priority">
                        <?php echo date($date_format . " H:i:s", $file['mtime']); ?>
                    </td>
                    <td class="list-value word-breakable">
                        <?php
                        $attachment_shown = false;
                        foreach ($attachments as $attachment)
                        {
                            if ($attachment->file_name == $file['name'])
                            {
                                $detach_button = '<input type="button" class="button btn" value="' . NBILL_DETACH . '" onclick="if(confirm(\'' . NBILL_DETACH_SURE . '\')){document.adminForm.attachment_id.value=\'' . $attachment->id . '\';document.adminForm.task.value=\'detach\';document.adminForm.submit();}return false;" />';
                                switch ($attachment->associated_doc_type)
                                {
                                    case 'EX':
                                        if ($attachment_shown)
                                        {
                                            echo ", ";
                                        }
                                        ?><a href="<?php echo nbf_cms::$interop->admin_page_prefix . "&action=expenditure&task=edit&cid=" . $attachment->associated_doc_id; ?>" target="_blank" title="<?php echo NBILL_SUPP_DOCS_EDIT_EXPENDITURE; ?>"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/expenditure.gif" alt="<?php echo NBILL_SUPP_DOCS_EDIT_EXPENDITURE; ?>" style="vertical-align: middle;" /><?php echo $attachment->transaction_no; ?></a><?php echo $detach_button;
                                        $attachment_shown = true;
                                        break;
                                    case 'CR':
                                        if ($attachment_shown)
                                        {
                                            echo ", ";
                                        }
                                        ?><a href="<?php echo nbf_cms::$interop->admin_page_prefix . "&action=credits&task=edit&cid=" . $attachment->associated_doc_id; ?>" target="_blank" title="<?php echo NBILL_SUPP_DOCS_EDIT_CREDIT; ?>"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/credits.gif" alt="<?php echo NBILL_SUPP_DOCS_EDIT_CREDIT; ?>" style="vertical-align: middle;" /><?php echo $attachment->document_no; ?></a><?php echo $detach_button;
                                        $attachment_shown = true;
                                        break;
                                    case 'IV':
                                        if ($attachment_shown)
                                        {
                                            echo ", ";
                                        }
                                        ?><a href="<?php echo nbf_cms::$interop->admin_page_prefix . "&action=invoices&task=edit&cid=" . $attachment->associated_doc_id; ?>" target="_blank" title="<?php echo NBILL_SUPP_DOCS_EDIT_INVOICE; ?>"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/invoices.gif" alt="<?php echo NBILL_SUPP_DOCS_EDIT_INVOICE; ?>" style="vertical-align: middle;" /><?php echo $attachment->document_no; ?></a><?php echo $detach_button;
                                        $attachment_shown = true;
                                        break;
                                    case 'QU':
                                        if ($attachment_shown)
                                        {
                                            echo ", ";
                                        }
                                        ?><a href="<?php echo nbf_cms::$interop->admin_page_prefix . "&action=quotes&task=edit&cid=" . $attachment->associated_doc_id; ?>" target="_blank" title="<?php echo NBILL_SUPP_DOCS_EDIT_QUOTE; ?>"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/quotes.gif" alt="<?php echo NBILL_SUPP_DOCS_EDIT_QUOTE; ?>" style="vertical-align: middle;" /><?php echo $attachment->document_no; ?></a><?php echo $detach_button;
                                        $attachment_shown = true;
                                        break;
                                    case 'IN':
                                        if ($attachment_shown)
                                        {
                                            echo ", ";
                                        }
                                        ?><a href="<?php echo nbf_cms::$interop->admin_page_prefix . "&action=income&task=edit&cid=" . $attachment->associated_doc_id; ?>" target="_blank" title="<?php echo NBILL_SUPP_DOCS_EDIT_INCOME; ?>"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/income.gif" alt="<?php echo NBILL_SUPP_DOCS_EDIT_INCOME; ?>" style="vertical-align: middle;" /><?php echo $attachment->transaction_no; ?></a><?php echo $detach_button;
                                        $attachment_shown = true;
                                        break;
                                    case 'CL':
                                        if ($attachment_shown)
                                        {
                                            echo ", ";
                                        }
                                        $client_name = trim($attachment->company_name);
                                        if (strlen(trim($attachment->company_name)) > 0 && strlen(trim($attachment->contact_name)) > 0)
                                        {
                                            $client_name .= " (";
                                        }
                                        $client_name .= trim($attachment->contact_name);
                                        if (strlen(trim($attachment->company_name)) > 0 && strlen(trim($attachment->contact_name)) > 0)
                                        {
                                            $client_name .= ")";
                                        }
                                        ?><a href="<?php echo nbf_cms::$interop->admin_page_prefix . "&action=clients&task=edit&cid=" . $attachment->associated_doc_id; ?>" target="_blank" title="<?php echo NBILL_SUPP_DOCS_EDIT_CLIENT; ?>"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/clients.gif" alt="<?php echo NBILL_SUPP_DOCS_EDIT_CLIENT; ?>" style="vertical-align: middle;" /><?php echo $client_name; ?></a><?php echo $detach_button;
                                        $attachment_shown = true;
                                        break;
                                    case 'SU':
                                        if ($attachment_shown)
                                        {
                                            echo ", ";
                                        }
                                        $client_name = trim($attachment->company_name);
                                        if (strlen(trim($attachment->company_name)) > 0 && strlen(trim($attachment->contact_name)) > 0)
                                        {
                                            $client_name .= " (";
                                        }
                                        $client_name .= trim($attachment->contact_name);
                                        if (strlen(trim($attachment->company_name)) > 0 && strlen(trim($attachment->contact_name)) > 0)
                                        {
                                            $client_name .= ")";
                                        }
                                        ?><a href="<?php echo nbf_cms::$interop->admin_page_prefix . "&action=suppliers&task=edit&cid=" . $attachment->associated_doc_id; ?>" target="_blank" title="<?php echo NBILL_SUPP_DOCS_EDIT_SUPPLIER; ?>"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/suppliers.gif" alt="<?php echo NBILL_SUPP_DOCS_EDIT_SUPPLIER; ?>" style="vertical-align: middle;" /><?php echo $client_name; ?></a><?php echo $detach_button;
                                        $attachment_shown = true;
                                        break;
                                    case 'OR':
                                        if ($attachment_shown)
                                        {
                                            echo ", ";
                                        }
                                        ?><a href="<?php echo nbf_cms::$interop->admin_page_prefix . "&action=orders&task=edit&cid=" . $attachment->associated_doc_id; ?>" target="_blank" title="<?php echo NBILL_SUPP_DOCS_EDIT_ORDER; ?>"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/orders.gif" alt="<?php echo NBILL_SUPP_DOCS_EDIT_ORDER; ?>" style="vertical-align: middle;" /><?php echo $attachment->order_no; ?></a><?php echo $detach_button;
                                        $attachment_shown = true;
                                        break;
                                        break;
                                    case 'PR':
                                        if ($attachment_shown)
                                        {
                                            echo ", ";
                                        }
                                        ?><a href="<?php echo nbf_cms::$interop->admin_page_prefix . "&action=products&task=edit&cid=" . $attachment->associated_doc_id; ?>" target="_blank" title="<?php echo NBILL_SUPP_DOCS_EDIT_PRODUCT; ?>"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/products.gif" alt="<?php echo NBILL_SUPP_DOCS_EDIT_PRODUCT; ?>" style="vertical-align: middle;" /><?php echo $attachment->product_name; ?></a><?php echo $detach_button;
                                        $attachment_shown = true;
                                        break;
                                        break;
                                }
                            }
                        }
                        ?>
                    </td>
                    <td class="list-value">
                        <a href="#" onclick="if(confirm('<?php echo sprintf(NBILL_DELETE_FILE_SURE, $file['name']); ?>')){document.adminForm.current_file.value='<?php echo addslashes($file['name']); ?>';document.adminForm.task.value='delete';document.adminForm.submit();}return false;" title="<?php echo NBILL_DELETE; ?>"><img style="vertical-align:middle" border="0" alt="<?php echo NBILL_DELETE; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file_delete.png" /></a>
                        <a href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&task=copy_file&root_path=<?php echo urlencode($path); ?>&current_path=<?php echo urlencode($current_path); ?>&source_path=<?php echo urlencode(realpath($current_path));?>&source_file=<?php echo urlencode($file['name']); ?>','','width=790,height=200,scrollbars=1');return false;" title="<?php echo NBILL_SUPP_DOCS_ACTION_COPY; ?>"><img style="vertical-align:middle" border="0" alt="<?php echo NBILL_SUPP_DOCS_ACTION_COPY; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file_copy.png" /></a>
                        <a href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&task=edit_file&source_path=<?php echo urlencode(realpath($current_path));?>&source_file=<?php echo urlencode($file['name']); ?>','','width=790,height=550,scrollbars=1');return false;" title="<?php echo NBILL_SUPP_DOCS_ACTION_EDIT; ?>"><img style="vertical-align:middle" border="0" alt="<?php echo NBILL_SUPP_DOCS_ACTION_EDIT; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file_edit.png" /></a>
                        <a href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&task=rename_file&source_path=<?php echo urlencode(realpath($current_path));?>&source_file=<?php echo urlencode($file['name']); ?>','','width=790,height=200,scrollbars=1');return false;" title="<?php echo NBILL_SUPP_DOCS_ACTION_RENAME; ?>"><img style="vertical-align:middle" border="0" alt="<?php echo NBILL_SUPP_DOCS_ACTION_RENAME; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file_rename.png" /></a>
                        <a href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&task=move_file&root_path=<?php echo urlencode($path); ?>&current_path=<?php echo urlencode($current_path); ?>&source_path=<?php echo urlencode(realpath($current_path));?>&source_file=<?php echo urlencode($file['name']); ?>','','width=790,height=200,scrollbars=1');return false;" title="<?php echo NBILL_SUPP_DOCS_ACTION_MOVE; ?>"><img style="vertical-align:middle" border="0" alt="<?php echo NBILL_SUPP_DOCS_ACTION_MOVE; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file_move.png" /></a>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
		</table>
        </div>

		</form>
		<?php
	}

    public static function getFolderName()
    {
        ?><p><?php echo NBILL_SUPP_DOCS_NEW_FOLDER_NAME_INTRO; ?></p>
        <p><?php echo NBILL_SUPP_DOCS_NEW_FOLDER_NAME; ?>&nbsp;<input id="new_folder_name" type="text" style="width:200px;" />&nbsp;<input type="button" class="button btn" value="<?php echo NBILL_SUBMIT; ?>" onclick="window.opener.document.getElementById('new_folder_name').value=document.getElementById('new_folder_name').value;window.opener.document.getElementById('task').value='create_folder';window.opener.document.adminForm.submit();window.close();return false;" />
        <br /><br /><p style="text-align:center"><a href="#" onclick="window.close();return false;"><?php echo NBILL_CLOSE_WINDOW; ?></a></p>
        <?php
    }

    public static function showFileUploadPopup($message = "", $upload_done = false)
    {
        if ($upload_done)
        {
            ?>
            <script type="text/javascript">window.opener.document.adminForm.submit();window.close();</script>
            <?php
        }
        else
        {
            if ($message)
            {
                ?>
                <div class="nbill-message message"><?php echo $message; ?></div>
                <?php
            }
            ?><p><?php echo NBILL_SUPP_DOCS_FILE_UPLOAD_INTRO; ?></p>
            <form action="<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>" enctype="multipart/form-data" action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
            <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
            <input type="hidden" name="action" value="supporting_docs" />
            <input type="hidden" name="current_path" value="<?php echo nbf_common::get_param($_REQUEST, 'current_path'); ?>" />
            <input type="hidden" name="task" id="task" value="do_file_upload" />
            <input type="hidden" name="hidemainmenu" value="1" />
            <input type="hidden" name="hide_billing_menu" value="1" />
            <?php
            for ($i = 1; $i <= 10; $i++)
            {
                ?>
                <input type="file" name="upload_<?php echo $i; ?>" size="50" /><br />
                <?php
            }
            ?>
            <br /><input type="submit" class="button btn" name="submit_upload" value="<?php echo NBILL_SUBMIT; ?>" />
            </form>
            <br /><br /><p style="text-align:center"><a href="#" onclick="window.close();return false;"><?php echo NBILL_CLOSE_WINDOW; ?></a></p>
            <?php
        }
    }

    public static function showFileMovePopup($message = "", $move_done = false, $copy = false)
    {
        $root_path = realpath(nbf_common::get_param($_REQUEST, 'root_path', '', true));
        $current_path = realpath(nbf_common::get_param($_REQUEST, 'current_path', $root_path, true));
        if (!$root_path)
        { ?>
            <div class="nbill-message message"><?php echo NBILL_SUPP_DOCS_ROOT_PATH_NOT_FOUND; ?></div>
            <?php
            return;
        }
        if (!$current_path)
        { ?>
            <div class="nbill-message message"><?php echo NBILL_SUPP_DOCS_CURRENT_PATH_NOT_FOUND; ?></div>
            <?php
            return;
        }

        $ds = "/";
        if ((strpos($current_path, "/") === false && strpos($current_path, "\\") !== false) || (strpos($root_path, "/") === false && strpos($root_path, "\\") !== false))
        {
            $ds = "\\";
        }

        if ($move_done)
        {
            ?>
            <script type="text/javascript">window.opener.document.adminForm.supp_docs_current_path.value='<?php echo addslashes($current_path); ?>';window.opener.document.adminForm.submit();window.close();</script>
            <?php
        }
        else
        {
            if ($message)
            {
                ?>
                <div class="nbill-message message"><?php echo $message; ?></div>
                <?php
            }
            ?><p><?php echo $copy ? NBILL_SUPP_DOCS_COPY_INTRO : NBILL_SUPP_DOCS_MOVE_INTRO; ?></p>
            <form action="<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>" enctype="multipart/form-data" action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
            <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
            <input type="hidden" name="action" value="supporting_docs" />
            <input type="hidden" name="root_path" value="<?php echo $root_path; ?>" />
            <input type="hidden" name="current_path" value="<?php echo $current_path; ?>" />
            <input type="hidden" name="task" id="task" value="do_file_move" />
            <input type="hidden" name="hidemainmenu" value="1" />
            <input type="hidden" name="hide_billing_menu" value="1" />
            <input type="hidden" name="copy" value="<?php echo $copy ? "1" : ""; ?>" />
            <input type="hidden" name="source_path" value="<?php echo nbf_common::get_param($_REQUEST, 'source_path', '', true); ?>" />
            <input type="hidden" name="source_file" value="<?php echo nbf_common::get_param($_REQUEST, 'source_file', '', true); ?>" />

            <?php echo $current_path . $ds; ?>
            <select name="sub_folder" id="sub_folder" onchange="if (this.options[selectedIndex].value.length > 0){document.adminForm.task.value='move_file';document.adminForm.current_path.value='<?php echo addslashes($current_path . $ds); ?>' + this.options[selectedIndex].value;document.adminForm.submit();}">
                <option value=""><?php echo NBILL_SELECT; ?></option>
                <?php if ($current_path != $root_path)
                { ?>
                    <option value="..">..</option>
                    <?php
                }
                $files = array_diff(@scandir($current_path), array(".", ".."));
                foreach ($files as $file)
                {
                    if (is_dir($current_path . $ds . $file))
                    {
                        ?>
                        <option value="<?php echo $file; ?>"><?php echo $file; ?></option>
                        <?php
                    }
                }
                ?>
            </select>

            <input type="submit" class="button btn" name="submit_move" value="<?php echo NBILL_SUBMIT; ?>" />
            </form>
            <br /><br /><p style="text-align:center"><a href="#" onclick="window.close();return false;"><?php echo NBILL_CLOSE_WINDOW; ?></a></p>
            <?php
        }
    }

    public static function showFileRenamePopup($message = "", $rename_done = false)
    {
        $source_path = realpath(nbf_common::get_param($_REQUEST, 'source_path', '', true));
        if (!$source_path)
        { ?>
            <div class="nbill-message message"><?php echo NBILL_SUPP_DOCS_CURRENT_PATH_NOT_FOUND; ?></div>
            <?php
            return;
        }
        $source_file = nbf_common::get_param($_REQUEST, 'source_file', '', true);

        $ds = "/";
        if ((strpos($source_path, "/") === false && strpos($source_path, "\\") !== false))
        {
            $ds = "\\";
        }

        if ($rename_done)
        {
            ?>
            <script type="text/javascript">window.opener.document.adminForm.submit();window.close();</script>
            <?php
        }
        else
        {
            if ($message)
            {
                ?>
                <div class="nbill-message message"><?php echo $message; ?></div>
                <?php
            }
            ?>
            <strong><?php echo $source_path . $ds . $source_file; ?></strong><br /><br />
            <p><?php echo NBILL_SUPP_DOCS_RENAME_INTRO; ?></p>
            <form action="<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>" enctype="multipart/form-data" action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
            <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
            <input type="hidden" name="action" value="supporting_docs" />
            <input type="hidden" name="source_path" value="<?php echo $source_path; ?>" />
            <input type="hidden" name="task" id="task" value="do_file_rename" />
            <input type="hidden" name="hidemainmenu" value="1" />
            <input type="hidden" name="hide_billing_menu" value="1" />
            <input type="hidden" name="source_file" value="<?php echo $source_file; ?>" />

            <?php echo NBILL_SUPP_DOCS_NEW_FILE_NAME; ?>&nbsp;<input type="text" name="new_file_name" style="width: 200px;" value="<?php echo $source_file; ?>" />

            <input type="submit" class="button btn" name="submit_rename" value="<?php echo NBILL_SUBMIT; ?>" />
            </form>
            <br /><br /><p style="text-align:center"><a href="#" onclick="window.close();return false;"><?php echo NBILL_CLOSE_WINDOW; ?></a></p>
            <?php
        }
    }

    public static function showFileEditPopup($message = "", $edit_done = false)
    {
        $source_path = realpath(nbf_common::get_param($_REQUEST, 'source_path', '', true));
        if (!$source_path)
        { ?>
            <div class="nbill-message message"><?php echo NBILL_SUPP_DOCS_CURRENT_PATH_NOT_FOUND; ?></div>
            <?php
            return;
        }
        $source_file = nbf_common::get_param($_REQUEST, 'source_file', '', true);

        $ds = "/";
        if ((strpos($source_path, "/") === false && strpos($source_path, "\\") !== false))
        {
            $ds = "\\";
        }

        if ($edit_done)
        {
            ?>
            <script type="text/javascript">window.close();</script>
            <?php
        }
        else
        {
            if ($message)
            {
                ?>
                <div class="nbill-message message"><?php echo $message; ?></div>
                <?php
            }
            ?>
            <strong><?php echo $source_path . $ds . $source_file; ?></strong><br /><br />
            <p><?php echo NBILL_SUPP_DOCS_EDIT_INTRO; ?></p>
            <form action="<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>" enctype="multipart/form-data" action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
            <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
            <input type="hidden" name="action" value="supporting_docs" />
            <input type="hidden" name="source_path" value="<?php echo $source_path; ?>" />
            <input type="hidden" name="task" id="task" value="do_file_edit" />
            <input type="hidden" name="hidemainmenu" value="1" />
            <input type="hidden" name="hide_billing_menu" value="1" />
            <input type="hidden" name="source_file" value="<?php echo $source_file; ?>" />

            <textarea class="large-text" name="file_contents"><?php echo file_get_contents($source_path . $ds . $source_file); ?></textarea>
            <br />
            <input type="submit" class="button btn" name="submit_edit" value="<?php echo NBILL_SUBMIT; ?>" />
            </form>
            <br /><p style="text-align:center"><a href="#" onclick="window.close();return false;"><?php echo NBILL_CLOSE_WINDOW; ?></a></p>
            <?php
        }
    }
}