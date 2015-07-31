<script type="text/javascript">
    var sectionClassName = <?php
    switch ($this->document->document_type)
    {
        case 'QU':
            echo 'QuoteLineItemsSection';
            break;
        default:
            echo 'LineItemsSection';
            break;
    }
    ?>;
    <?php
    $json_string = json_encode($this->line_item_collection);
    $escapers = array("\\", "/", "\"", "'", "\n", "\r", "\t", "\x08", "\x0c");
    $replacements = array("\\\\", "\\/", "\\\"", "\\'", "\\n", "\\r", "\\t", "\\f", "\\b");
    $json_string = str_replace($escapers, $replacements, $json_string);
    ?>
    json_items = JSON.parse('<?php echo $json_string; ?>');
    line_items = new LineItemsCollection(json_items, sectionClassName);
</script>

<div id="line_item_container">
    <div id="line_item_editor">
        <?php
        echo $html;
        ?>
    </div>
    <div id="line_item_editor_overlay" style="display:none;">
        <?php echo NBILL_LINE_ITEMS_UPDATING;?>
    </div>
</div>