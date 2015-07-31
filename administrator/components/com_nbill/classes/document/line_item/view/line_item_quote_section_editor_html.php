<?php
class nBillLineItemQuoteSectionEditorHtml extends nBillLineItemSectionEditorHtml
{
    protected function renderFields()
    {
        parent::renderFields();
        ?>
        <label for="quote_atomic" class="radio-label"><?php echo NBILL_DOC_SECTION_QUOTE_ATOMIC; ?></label>
        <?php
        echo nbf_html::yes_or_no_options('quote_atomic', '', $this->section->quote_atomic);
    }
}