<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');
?>
<script type="text/javascript">
function pre_submit_<?php echo $this->widget->id; ?>()
{
    <?php
    //Ensure HTML is saved to underlying textarea
    echo nbf_cms::$interop->get_editor_contents("message", "message");
    ?>
}
</script>
<div class="nbill-widget-config-field">
    <label for="message"><?php echo NBILL_WIDGETS_HTML_MESSAGE; ?></label>
    <?php echo nbf_cms::$interop->render_editor('message', 'message', $this->widget->message); ?>
    <!--<textarea name="message" id="message"><?php echo $this->widget->message; ?></textarea>-->
</div>