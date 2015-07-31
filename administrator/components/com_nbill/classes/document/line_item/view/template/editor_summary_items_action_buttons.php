<div onclick="var e = arguments[0]||window.event;if(e.stopPropagation){e.stopPropagation();}else{e.cancelBubble = true;}">
<a class="nbill-line-item-action-button" href="javascript:void(0);" id="nbill_line_item_edit_<?php echo $line_item->getParentSection()->index . '_' . $line_item->index; ?>" onclick="showBlankBox(submitLineItemAjaxTask('edit_item_popup', 'section_index=<?php echo $line_item->getParentSection()->index; ?>&item_index=<?php echo $line_item->index; ?>', function(response){refreshPopup(response);extract_and_execute_js('nbill_item_editor', true);}));return false;" title="<?php echo NBILL_INVOICE_ITEM_EDIT; ?>"><img alt="<?php echo NBILL_INVOICE_ITEM_EDIT; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file_edit.png" /></a>
<a class="nbill-line-item-action-button" href="javascript:void(0);" id="nbill_line_item_delete_<?php echo $line_item->getParentSection()->index . '_' . $line_item->index; ?>" onclick="submitLineItemAjaxTask('remove_line_item', 'section_index=<?php echo $line_item->getParentSection()->index; ?>&item_index=<?php echo $line_item->index; ?>');return false;" title="<?php echo NBILL_REMOVE_INVOICE_ITEM; ?>"><img alt="<?php echo NBILL_REMOVE_INVOICE_ITEM; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file_delete.png" /></a>

<?php if ($can_section_break) { ?><a class="nbill-line-item-action-button" href="javascript:void(0);" onclick="showBox('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=ajax&task=insert_section_break_popup&document_type=' + document.getElementById('document_type').value + '&section_index=<?php echo $line_item->getParentSection()->index; ?>&item_index=<?php echo $line_item->index; ?>');return false;" title="<?php echo NBILL_DOC_SECTION_ADD; ?>"><?php } ?>
<img alt="<?php echo NBILL_DOC_SECTION_ADD; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/section_break<?php if (!$can_section_break){echo '_disabled';} ?>.png" />
<?php if ($can_section_break) { ?></a><?php } ?>

<?php if (!$line_item->page_break) { ?>
    <a class="nbill-line-item-action-button" href="javascript:void(0);" onclick="submitLineItemAjaxTask('insert_page_break', 'section_index=<?php echo $line_item->getParentSection()->index; ?>&item_index=<?php echo $line_item->index; ?>');return false;" title="<?php echo NBILL_DOC_PAGE_BREAK; ?>">
<?php } ?>
<img alt="<?php echo NBILL_DOC_PAGE_BREAK; ?>" id="img_page_break_<?php echo $line_item->getParentSection()->index . '_' . $line_item->index . $id_suffix; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/page_break<?php if ($line_item->page_break) {echo '_disabled';} ?>.png" />
<?php if (!$line_item->page_break) { ?>
    </a>
<?php }
?>
</div>