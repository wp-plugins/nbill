<?php
defined('ABSPATH') or die ('Access Denied');

get_header();
?>
<div id="primary">
    <div id="content" role="main">
    <?php
    include(plugin_dir_path(__FILE__) . '/components/com_nbill/nbill.php');
    ?>
    </div>
</div>
<?php wp_reset_query(); ?>
<?php get_footer(); ?>