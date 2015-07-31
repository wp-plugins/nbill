<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');
?>
<script type="text/javascript">
    function select_favourite(menu_item_id)
    {
        document.getElementById('favourite_' + menu_item_id).value = (document.getElementById('favourite_' + menu_item_id).value == 0 ? 1 : 0)
        if (document.getElementById('favourite_' + menu_item_id).value == '0') {
            document.getElementById('fav_select_' + menu_item_id).src = '<?php echo nbf_cms::$interop->nbill_site_url_path . "/images/icons/cross.png"; ?>'
        } else {
            document.getElementById('fav_select_' + menu_item_id).src = '<?php echo nbf_cms::$interop->nbill_site_url_path . "/images/icons/tick.png"; ?>';
        }
    }
    function select_ext_favourite(menu_item_id)
    {
        document.getElementById('ext_favourite_' + menu_item_id).value = (document.getElementById('ext_favourite_' + menu_item_id).value == 0 ? 1 : 0)
        if (document.getElementById('ext_favourite_' + menu_item_id).value == '0') {
            document.getElementById('ext_fav_select_' + menu_item_id).src = '<?php echo nbf_cms::$interop->nbill_site_url_path . "/images/icons/cross.png"; ?>'
        } else {
            document.getElementById('ext_fav_select_' + menu_item_id).src = '<?php echo nbf_cms::$interop->nbill_site_url_path . "/images/icons/tick.png"; ?>';
        }
    }
</script>

<div class="nbill-widget-config-field">
    <label><?php echo NBILL_WIDGETS_LINKS_ICON_TYPE; ?></label>
    <label class="radio-label"><input type="radio" name="icon_type" id="icon_type_small" value="0"<?php if ($this->widget->icon_type == LinksWidget::SMALL_ICONS) {echo ' checked="checked"';} ?> /><?php echo NBILL_WIDGETS_LINKS_ICONS_SMALL; ?></label>
    <label class="radio-label"><input type="radio" name="icon_type" id="icon_type_large" value="1"<?php if ($this->widget->icon_type == LinksWidget::LARGE_ICONS) {echo ' checked="checked"';} ?> /><?php echo NBILL_WIDGETS_LINKS_ICONS_LARGE; ?></label>
    <label class="radio-label"><input type="radio" name="icon_type" id="icon_type_none" value="2"<?php if ($this->widget->icon_type == LinksWidget::NO_ICONS) {echo ' checked="checked"';} ?> /><?php echo NBILL_WIDGETS_LINKS_ICONS_NONE; ?></label>
</div>
<div class="nbill-widget-config-field">
    <p><?php echo NBILL_FAVOURITES_INTRO; ?></p>
    <table class="widget-config-table" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <th><?php echo NBILL_FAVOURITES_ICON; ?></th>
            <th><?php echo NBILL_FAVOURITES_NAME; ?></th>
            <th class="responsive-cell priority"><?php echo NBILL_FAVOURITES_DESCRIPTION; ?></th>
            <th class="center"><?php echo NBILL_FAVOURITES_SELECT; ?></th>
        </tr>
        <?php
        foreach ($this->menu_links as $menu_link)
        {
            $img = $menu_link->published ? 'tick.png' : 'cross.png';
            $alt = $menu_link->published ? NBILL_FAVOURITE_YES : NBILL_FAVOURITE_NO;
            $prefix = 'favourite_';
            switch ($menu_link->type)
            {
                case LinkItem::TYPE_EXTENSION:
                    $prefix = 'ext_favourite_';
                    break;
                case LinkItem::TYPE_USER_DEFINED:
                    $prefix = 'user_favourite_';
                    break;
            }
            ?>
            <tr>
                <td><img src="<?php echo str_replace("[NBILL_FE]", nbf_cms::$interop->nbill_site_url_path, $menu_link->image); ?>" alt="<?php echo stripslashes(@constant($menu_link->text)); ?>" /></td>
                <td><?php echo stripslashes(@constant($menu_link->text)); ?></td>
                <td class="responsive-cell priority"><?php echo stripslashes(@constant($menu_link->title)); ?></td>
                <td class="center">
                    <input type="hidden" name="<?php echo $prefix . $menu_link->menu_id; ?>" id="<?php echo $prefix . $menu_link->menu_id; ?>" value="<?php echo $menu_link->published; ?>" />
                    <a href="#" onclick="select_<?php echo $prefix == 'ext_favourite_' ? 'ext_' : '' ?>favourite('<?php echo $menu_link->menu_id; ?>');return false;"><img id="<?php echo $prefix == 'ext_favourite_' ? 'ext_' : '' ?>fav_select_<?php echo $menu_link->menu_id; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path . "/images/icons/$img"; ?>" border="0" alt="<?php echo $alt; ?>" /></a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>