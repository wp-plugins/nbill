<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

if (!$ajax_refresh) {
    ?>
    <div class="nbill-widget type-<?php echo $this->widget->type; ?>" id="nbill_widget_<?php echo $this->widget->id; ?>" style="width:<?php echo $this->widget->width; ?>">
    <?php
}
else {
    if ($this->widget->layout_dirty) {
        //This widget has peed all over the layout, we're gonna need to reload the whole page, not just do an ajax refresh
        ?>
        <script type="text/javascript">
            showBox('about:blank'); //So the user knows something is happening
            location.reload(false)
        </script>
        <?php
        return;
    }
}
?>
        <?php if ($this->widget->show_title) { ?>
            <div class="nbill-widget-title"><h2><?= defined($this->widget->title) ? constant($this->widget->title) : $this->widget->title; ?></h2></div>
        <?php } ?>
        <div class="nbill-widget-container">
            <div class="nbill-widget-controls">
                <?php if ($this->widget->configurable) { ?>
                    <a href="javascript:void(0);" onclick="showBox('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=widgets&task=show_config&widget_id=<?php echo $this->widget->id; ?>',extract_and_execute_js,function(){setTimeout(function(){extract_and_execute_js('nbill_widget_<?php echo $this->widget->id; ?>', true)}, 250);});return false;" title="<?php echo NBILL_WIDGET_CONFIGURATION; ?>">
                        <img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path ?>/images/widget_config.png" alt="<?php echo NBILL_WIDGET_CONFIGURATION; ?>" />
                    </a>
                <?php } ?>
                <a href="javascript:void(0);" onclick="document.getElementById('nbill_widget_<?php echo $this->widget->id; ?>').style.display='none';return false;" title="<?php echo NBILL_WIDGET_CLOSE; ?>">
                    <img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path ?>/images/widget_close.png" alt="<?php echo NBILL_WIDGET_CLOSE; ?>" />
                </a>
            </div>
            <div id="nbill_widget_content_<?php echo $this->widget->id; ?>" class="nbill-widget-content">
                <?php
                $this->renderContent($ajax_refresh);
                ?>
            </div>
        </div>
    <?php if (!$ajax_refresh) { ?>
    </div>
    <?php
}