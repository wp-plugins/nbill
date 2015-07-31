<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* This file is loaded via AJAX, so you cannot add anything to <head> (all that has to be done in index.php)
*/

$full_width = '99%';
$half_width = '49%';
$default_fixed_width = '75';
?>

<script type="text/javascript">
function width_changed()
{
    var width_select = document.getElementById('widget_width');
    var new_width = width_select.options[width_select.selectedIndex].value;
    var width_value = document.getElementById('width_fixed_amount');
    var width_units = document.getElementById('width_units');

    switch (new_width)
    {
        case 'auto':
        case '<?php echo $full_width; ?>':
        case '<?php echo $half_width; ?>':
            width_value.style.display = 'none';
            width_units.innerHTML = '';
            break;
        case 'px':
        case '%':
            width_value.style.display = '';
            width_units.innerHTML = new_width;
            if (width_value.value.length == 0) {
                width_value.value = '<?php echo $default_fixed_width; ?>';
            }
            if (new_width == '%') {
                document.getElementById('width_percent_help').style.display='';
            } else {
                document.getElementById('width_percent_help').style.display='none';
            }
            break;
    }
}
</script>

<div class="nbill-widget nbill-widget-config" id="nbill-widget-config-<?php echo $this->widget->id; ?>">
    <div class="nbill-widget-title"><h2><?php echo sprintf(NBILL_WIDGETS_CONFIG_TITLE, defined($this->widget->title) ? constant($this->widget->title) : $this->widget->title); ?></h2></div>
    <div class="nbill-widget-container">
        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="nbill_widget_config_form" id="nbill_widget_config_form">
            <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
            <input type="hidden" name="action" value="widgets" />
            <input type="hidden" name="task" value="save_config" />
            <input type="hidden" name="widget_id" value="<?php echo $this->widget->id; ?>" />

            <div class="nbill-widget-config-field">
                <label for="title"><?php echo NBILL_WIDGETS_CONFIG_TITLE_PROMPT; ?></label>
                <input type="text" name="title" id="title" value="<?php echo defined($this->widget->title) ? constant($this->widget->title) : $this->widget->title; ?>" />
            </div>
            <div class="nbill-widget-config-field">
                <label for="widget_width"><?php echo NBILL_WIDGETS_CONFIG_WIDTH; ?></label>
                <select name="widget_width" id="widget_width" class="auto-size" onchange="width_changed();">
                    <?php
                    $selected = $this->widget->width;
                    $unit_value = $default_fixed_width;
                    switch ($selected)
                    {
                        case 'auto':
                        case $full_width:
                        case $half_width:
                            //Ok
                            break;
                        default:
                            if (substr($this->widget->width, strlen($this->widget->width)-2) == "px") {
                                $selected = 'px';
                                $unit_value = substr($this->widget->width, 0, strlen($this->widget->width)-2);
                            } else {
                                $selected = '%';
                                $unit_value = substr($this->widget->width, 0, strlen($this->widget->width)-1);
                            }
                    }
                    ?>
                    <option value="auto"<?php if ($selected=='auto') {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_CONFIG_WIDTH_AUTO; ?></option>
                    <option value="<?php echo $full_width; ?>"<?php if ($selected==$full_width) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_CONFIG_WIDTH_FULL; ?></option>
                    <option value="<?php echo $half_width; ?>"<?php if ($selected==$half_width) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_CONFIG_WIDTH_HALF; ?></option>
                    <option value="px"<?php if ($selected=='px') {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_CONFIG_WIDTH_FIXED_PX; ?></option>
                    <option value="%"<?php if ($selected=='%') {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_CONFIG_WIDTH_FIXED_PC; ?></option>
                </select>
                <input type="text" id="width_fixed_amount" name="width_fixed_amount" value="<?php echo $unit_value; ?>" class="number-of-units"<?php if ($selected != 'px' && $selected != '%') {echo ' style="display:none;"';} ?> /><span id="width_units"><?php if ($selected == 'px' || $selected == '%') {echo $selected;} ?></span>
                <span id="width_percent_help" style="display:none;"><?php nbf_html::show_overlib(NBILL_WIDGETS_CONFIG_WIDTH_HELP); ?></span>
            </div>
            <div class="nbill-widget-config-field">
                <label><?php echo NBILL_WIDGETS_CONFIG_SHOW_TITLE_PROMPT; ?></label>
                <label class="radio-label"><input type="radio" name="show_title" id="show_title_no" value="0"<?php if (!$this->widget->show_title) {echo ' checked="checked"';} ?> /><?php echo NBILL_NO; ?></label>
                <label class="radio-label"><input type="radio" name="show_title" id="show_title_yes" value="1"<?php if ($this->widget->show_title) {echo ' checked="checked"';} ?> /><?php echo NBILL_YES; ?></label>
            </div>

            <?php $this->renderConfigContent(); ?>

            <div class="nbill-widget-config-buttons">
                <a href="javascript:void(0);" class="widget-config-button" id="nbill_widget_config_cancel" name="cancel" onclick="if(typeof(pre_cancel_<?php echo $this->widget->id; ?>)!='undefined'){if (pre_cancel_<?php echo $this->widget->id; ?>()===false){return false;}}TINY.box.hide();return false;"><?php echo NBILL_CANCEL; ?></a>
                <a href="javascript:void(0);" class="widget-config-button" id="nbill_widget_config_save" name="save_config" onclick="if(typeof(pre_submit_<?php echo $this->widget->id; ?>)!='undefined'){if (pre_submit_<?php echo $this->widget->id; ?>()===false){return false;}}submit_ajax_request('', getFormValues(), function(content){TINY.box.hide();document.getElementById('nbill_widget_<?php echo $this->widget->id; ?>').innerHTML=content;extract_and_execute_js('nbill_widget_<?php echo $this->widget->id; ?>', true)});return false;"><?php echo NBILL_SUBMIT; ?></a>
            </div>
        </form>
    </div>
</div>