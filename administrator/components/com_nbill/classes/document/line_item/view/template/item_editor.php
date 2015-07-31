    <form name="nbill_item_editor" id="nbill_item_editor">
        <input type="hidden" name="section_index" id="section_index" value="<?php echo intval(@$_REQUEST['section_index']); ?>" />
        <input type="hidden" name="item_index" id="item_index" value="<?php echo intval(@$_REQUEST['item_index']); ?>" />
        <input type="hidden" name="vendor_id" value="<?php echo intval($this->line_item->vendor_id); ?>" />
        <div class="nbill-widget nbill-widget-config">

        <script type="text/javascript">
        <?php
        if (!intval($this->line_item->id)) {
            //Adding new item
            switch ($this->document_type) {
                case 'QU':
                    ?>
                    new_line_item = new QuoteLineItem(JSON.parse('<?php echo json_encode($this->line_item); ?>'));
                    <?php
                    break;
                default:
                    ?>
                    new_line_item = new LineItem(JSON.parse('<?php echo json_encode($this->line_item); ?>'));
                    <?php
                    break;
            }
        } ?>
        </script>

        <div class="nbill-widget-title"><h2><?php echo NBILL_LINE_ITEM_EDITOR; ?></h2></div>
        <div class="nbill-widget-container">
            <?php $this->renderFields(); ?>
        </div>
        <hr class="button-separator" />
        <div class="nbill-widget-config-buttons">
            <?php
            $this->renderCancelButton();
            $this->renderSubmitButton();
            ?>
        </div>
    </form>
</div>