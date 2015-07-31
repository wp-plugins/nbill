<div class="nbill-widget nbill-widget-config">
    <form name="nbill_section_editor" id="nbill_section_editor">
        <input type="hidden" name="section_index" id="section_index" value="<?php echo intval(@$_REQUEST['section_index']); ?>" />
        <input type="hidden" name="item_index" id="item_index" value="<?php echo intval(@$_REQUEST['item_index']); ?>" />

        <div class="nbill-widget-title"><h2><?php echo NBILL_LINE_ITEM_SECTION_EDITOR; ?></h2></div>
        <div class="nbill-widget-container">
            <?php $this->renderFields(); ?>
        </div>
        <hr />
        <div class="nbill-widget-config-buttons">
            <?php
            $this->renderCancelButton();
            $this->renderSubmitButton();
            ?>
        </div>
    </form>
</div>
