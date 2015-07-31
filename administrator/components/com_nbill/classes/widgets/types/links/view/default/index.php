<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

foreach ($this->widget->links as $link_item)
{
    ?>
    <div class="nbill-widget-coloured-link">
        <div class="nbill-widget-div-link widget-link-item linkicon-<?php echo $this->widget->icon_type == LinksWidget::LARGE_ICONS ? 'large' : ($this->widget->icon_type == LinksWidget::SMALL_ICONS ? 'small' : 'none'); ?>">

            <a href="<?php echo $link_item->url; ?>" title="<?php echo $link_item->title; ?>"<?php if (strlen($link_item->link_attributes) > 0) {echo ' ' . $link_item->link_attributes;} ?>>
                <?php
                if (strlen($link_item->image) > 0) { ?>
                    <span class="widget-link-item-image"><img src="<?php echo $link_item->image; ?>" alt="<?php echo $link_item->title; ?>"<?php if (strlen($link_item->image_attributes) > 0) {echo ' ' . $link_item->image__attributes;} ?> /></span>
                    <?php
                }
                if (strlen($link_item->text) > 0) {
                    ?><span class="widget-link-item-text"<?php if (strlen($link_item->text_attributes) > 0) {echo ' ' . $link_item->text_attributes;} ?>><?php echo $link_item->text; ?></span><?php
                }
                ?>
            </a>
        </div>
    </div>
    <?php
}