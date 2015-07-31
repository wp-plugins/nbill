<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');
?>
<div class="nbill-widget-config-field">
    <label for="number_of_units"><?php echo NBILL_WIDGETS_ORDERS_DUE_RANGE; ?></label>
    <input type="number" min="1" max="100" name="number_of_units" id="number_of_units" class="numeric" value="<?php echo $this->widget->number_of_units; ?>" />
    <select name="range_units" id="range_units">
        <option value="<?php echo OrdersDueWidget::RANGE_UNITS_DAYS; ?>"<?php if ($this->widget->range_units == OrdersDueWidget::RANGE_UNITS_DAYS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_ORDERS_DUE_RANGE_DAYS; ?></option>
        <option value="<?php echo OrdersDueWidget::RANGE_UNITS_WEEKS; ?>"<?php if ($this->widget->range_units == OrdersDueWidget::RANGE_UNITS_WEEKS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_ORDERS_DUE_RANGE_WEEKS; ?></option>
        <option value="<?php echo OrdersDueWidget::RANGE_UNITS_MONTHS; ?>"<?php if ($this->widget->range_units == OrdersDueWidget::RANGE_UNITS_MONTHS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_ORDERS_DUE_RANGE_MONTHS; ?></option>
    </select>
    <?php nbf_html::show_overlib(NBILL_WIDGETS_ORDERS_DUE_RANGE_HELP, "date_range_help"); ?>
</div>
<div class="nbill-widget-config-field">
    <label for="max_records"><?php echo NBILL_WIDGETS_ORDERS_DUE_MAX; ?></label>
    <input type="number" min="1" max="100" maxlength="3" name="max_records" id="max_records" class="numeric" value="<?php echo $this->widget->max_records; ?>" />
</div>
<div class="nbill-widget-config-field">
    <label for="height"><?php echo NBILL_WIDGETS_ORDERS_DUE_HEIGHT; ?></label>
    <input type="text" name="height" id="height" class="numeric" value="<?php echo rtrim($this->widget->height, '%pxemt'); ?>" />px
    <?php nbf_html::show_overlib(NBILL_WIDGETS_ORDERS_DUE_HEIGHT_HELP, "height_help"); ?>
</div>