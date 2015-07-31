<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');
?>
<div class="nbill-widget-config-field">
    <label for="graph_type"><?php echo NBILL_WIDGETS_SALES_GRAPH_GRAPH_TYPE; ?></label>
    <select name="graph_type" id="graph_type">
        <option value="Line"<?php if ($this->widget->graph_type == SalesGraphWidget::GRAPH_TYPE_LINE) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_LINE; ?></option>
        <option value="Column"<?php if ($this->widget->graph_type == SalesGraphWidget::GRAPH_TYPE_COLUMN) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_COLUMN; ?></option>
        <option value="Bar"<?php if ($this->widget->graph_type == SalesGraphWidget::GRAPH_TYPE_BAR) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_BAR; ?></option>
        <option value="Pie"<?php if ($this->widget->graph_type == SalesGraphWidget::GRAPH_TYPE_PIE) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_PIE; ?></option>
    </select>
</div>
<div class="nbill-widget-config-field">
    <label for="graph_height"><?php echo NBILL_WIDGETS_SALES_GRAPH_HEIGHT; ?></label>
    <input type="text" name="graph_height" id="graph_height" class="numeric" value="<?php echo rtrim($this->widget->graph_height, '%pxemt'); ?>" />px
    <?php nbf_html::show_overlib(NBILL_WIDGETS_SALES_GRAPH_HEIGHT_HELP, "graph_height_help"); ?>
</div>
<div class="nbill-widget-config-field">
    <label for="currency"><?php echo NBILL_WIDGETS_SALES_GRAPH_CURRENCY; ?></label>
    <input type="text" name="currency" id="currency" value="<?php echo $this->widget->currency; ?>" maxlength="3" />
</div>
<div class="nbill-widget-config-field">
    <label for="include_expenditure"><?php echo NBILL_WIDGETS_SALES_GRAPH_GROSS_OR_NET; ?></label>
    <select name="include_expenditure" id="include_expenditure">
        <option value="0"<?php if (!$this->widget->include_expenditure) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_GROSS; ?></option>
        <option value="1"<?php if ($this->widget->include_expenditure) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_NET; ?></option>
    </select>
</div>
<div class="nbill-widget-config-field">
    <label for="include_unpaid_invoices"><?php echo NBILL_WIDGETS_SALES_GRAPH_INCLUDE_UNPAID_INVOICES; ?></label>
    <label class="radio-label"><input type="radio" name="include_unpaid_invoices" id="include_unpaid_invoices_no" value="0"<?php if (!$this->widget->include_unpaid_invoices) {echo ' checked="checked"';} ?> /><?php echo NBILL_NO; ?></label>
    <label class="radio-label"><input type="radio" name="include_unpaid_invoices" id="include_unpaid_invoices_yes" value="1"<?php if ($this->widget->include_unpaid_invoices) {echo ' checked="checked"';} ?> /><?php echo NBILL_YES; ?></label>
    <?php nbf_html::show_overlib(NBILL_WIDGETS_SALES_GRAPH_INCLUDE_UNPAID_INVOICES_HELP, "include_unpaid_invoices_help"); ?>
</div>
<div class="nbill-widget-config-field">
    <label for="date_range"><?php echo NBILL_WIDGETS_SALES_GRAPH_DATE_RANGE; ?></label>
    <select name="date_range" id="date_range">
        <option value="0"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_CURRENT_MONTH) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_CURRENT_MONTH; ?></option>
        <option value="10"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_PREV_MONTH) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_MONTH; ?></option>
        <option value="20"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_PREV_AND_CURRENT_MONTH) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_AND_CURRENT_MONTH; ?></option>
        <option value="30"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_PREV_VS_CURRENT_MONTH) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_VS_CURRENT_MONTH; ?></option>
        <option value="40"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_24_HOURS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_24_HOURS; ?></option>
        <option value="50"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_48_HOURS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_48_HOURS; ?></option>
        <option value="60"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_7_DAYS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_7_DAYS; ?></option>
        <option value="70"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_14_DAYS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_14_DAYS; ?></option>
        <option value="80"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_28_DAYS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_28_DAYS; ?></option>
        <option value="90"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_30_DAYS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_30_DAYS; ?></option>
        <option value="100"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_60_DAYS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_60_DAYS; ?></option>
        <option value="110"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_90_DAYS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_90_DAYS; ?></option>
        <option value="120"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_3_MONTHS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_3_MONTHS; ?></option>
        <option value="130"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_6_MONTHS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_6_MONTHS; ?></option>
        <option value="140"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_12_MONTHS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_12_MONTHS; ?></option>
        <option value="150"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_CURRENT_QUARTER) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_CURRENT_QUARTER; ?></option>
        <option value="160"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_PREV_QUARTER) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_QUARTER; ?></option>
        <option value="170"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_PREV_VS_CURRENT_QUARTER) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_VS_CURRENT_QUARTER; ?></option>
        <option value="180"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_CURRENT_YEAR) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_CURRENT_YEAR; ?></option>
        <option value="190"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_PREV_YEAR) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_YEAR; ?></option>
        <option value="200"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_PREV_VS_CURRENT_YEAR) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_VS_CURRENT_YEAR; ?></option>
        <option value="210"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_5_YEARS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_5_YEARS; ?></option>
        <option value="220"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_ALL_TIME) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_ALL_TIME; ?></option>
    </select>
</div>
