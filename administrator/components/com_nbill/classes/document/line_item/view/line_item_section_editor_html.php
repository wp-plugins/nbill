<?php
class nBillLineItemSectionEditorHtml
{
    /** @var nBillLineItemsSection **/
    protected $section;
    /** @var string **/
    protected $action;

    public function __construct(nBillLineItemsSection $section)
    {
        $this->section = $section;
    }

    public function showSectionEditor($action='insert_section_break')
    {
        $this->action = $action;
        include(realpath(dirname(__FILE__)) . '/template/section_editor.php');
    }

    protected function renderFields()
    {
        ?>
        <div class="nbill-widget-config-field" id="nbill-admin-doc-section-name">
            <label for="section_name"><?php echo NBILL_DOC_SECTION_NAME; ?></label>
            <input type="text" name="section_name" id="section_name" value="<?php echo $this->section->section_name; ?>" autofocus />
        </div>
        <div class="nbill-widget-config-field" id="nbill-admin-doc-section-title">
            <label for="section_discount_title"><?php echo NBILL_DOC_SECTION_DISCOUNT_TITLE; ?></label>
            <input type="text" name="section_discount_title" id="section_discount_title" value="<?php echo $this->section->discount_title; ?>" />
        </div>
        <div class="nbill-widget-config-field" id="nbill-admin-doc-section-discount">
            <label for="section_discount_percent"><?php echo NBILL_DOC_SECTION_DISCOUNT_PC; ?></label>
            <input type="text" name="section_discount_percent" id="section_discount_percent" value="<?php echo $this->section->discount_percent; ?>" />
        </div>
        <?php
    }

    protected function renderCancelButton()
    {
        ?>
        <a href="javascript:void(0);" class="widget-config-button" id="nbill_section_editor_cancel" name="cancel" onclick="TINY.box.hide();return false;"><?php echo NBILL_CANCEL; ?></a>
        <?php
    }

    protected function renderSubmitButton()
    {
        ?>
        <a href="javascript:void(0);" class="widget-config-button" id="nbill_section_editor_save" name="save_section" onclick="TINY.box.hide();submitLineItemAjaxTask('<?php echo $this->action; ?>', getFormValues('nbill_section_editor'));return false;"><?php echo NBILL_SUBMIT; ?></a>
        <?php
    }
}