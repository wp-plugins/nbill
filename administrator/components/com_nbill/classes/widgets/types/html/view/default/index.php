<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');
nbf_cms::$interop->init_editor();
?>
<div class="nbill-html-message">
    <?php echo $this->widget->message; ?>
</div>