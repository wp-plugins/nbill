<?php
/**
* HTML Output for favourites picker
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillFavourites
{
    public static function showFavourites($menu_items, $extension_menu_items)
    {
        ?>
        <script type="text/javascript">
        function select_favourite(menu_item_id)
        {
            document.getElementById('favourite_' + menu_item_id).value = (document.getElementById('favourite_' + menu_item_id).value == 0 ? 1 : 0)
            if (document.getElementById('favourite_' + menu_item_id).value == 0)
            {
                document.getElementById('fav_select_' + menu_item_id).src = '<?php echo nbf_cms::$interop->nbill_site_url_path . "/images/icons/cross.png"; ?>'
            }
            else
            {
                document.getElementById('fav_select_' + menu_item_id).src = '<?php echo nbf_cms::$interop->nbill_site_url_path . "/images/icons/tick.png"; ?>';
            }
        }
        function select_ext_favourite(menu_item_id)
        {
            document.getElementById('ext_favourite_' + menu_item_id).value = (document.getElementById('ext_favourite_' + menu_item_id).value == 0 ? 1 : 0)
            if (document.getElementById('ext_favourite_' + menu_item_id).value == 0)
            {
                document.getElementById('ext_fav_select_' + menu_item_id).src = '<?php echo nbf_cms::$interop->nbill_site_url_path . "/images/icons/cross.png"; ?>'
            }
            else
            {
                document.getElementById('ext_fav_select_' + menu_item_id).src = '<?php echo nbf_cms::$interop->nbill_site_url_path . "/images/icons/tick.png"; ?>';
            }
        }
        function nbill_submit_task(task_name)
        {
            document.adminForm.task.value=task_name;
            document.adminForm.submit();
            return;
        }
        </script>

        <table class="adminheading" style="width:auto;">
        <tr>
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, nbf_common::get_param($_REQUEST, 'action')); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_FAVOURITES_TITLE; ?>
            </th>
        </tr>
        </table>

        <p align="left"><?php echo NBILL_FAVOURITES_INTRO; ?></p>
        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
            <input type="hidden" name="option" value="<?php echo nbf_common::get_param($_REQUEST, 'option'); ?>" />
            <input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
            <input type="hidden" name="task" value="<?php echo nbf_common::get_param($_REQUEST, 'task'); ?>" />

            <table class="adminlist table table-striped" cellspacing="0" cellpadding="2">
                <tr>
                    <th style="width:30px"><?php echo NBILL_FAVOURITES_ICON; ?></th>
                    <th><?php echo NBILL_FAVOURITES_NAME; ?></th>
                    <th><?php echo NBILL_FAVOURITES_DESCRIPTION; ?></th>
                    <th style="width:40px;text-align:center"><?php echo NBILL_FAVOURITES_SELECT; ?></th>
                </tr>
                <?php
                foreach ($menu_items as $menu_item)
                {
                    $img = $menu_item->favourite ? 'tick.png' : 'cross.png';
                    $task = $menu_item->favourite ? 'unpublish' : 'publish';
                    $alt = $menu_item->favourite ? NBILL_FAVOURITE_YES : NBILL_FAVOURITE_NO;
                    ?>
                    <tr>
                        <td><img src="<?php echo str_replace("[NBILL_FE]", nbf_cms::$interop->nbill_site_url_path, $menu_item->image); ?>" alt="<?php echo stripslashes(@constant($menu_item->text)); ?>" /></td>
                        <td><?php echo stripslashes(@constant($menu_item->text)); ?></td>
                        <td><?php echo stripslashes(@constant($menu_item->description)); ?></td>
                        <td style="text-align:center">
                            <input type="hidden" name="favourite_<?php echo $menu_item->id; ?>" id="favourite_<?php echo $menu_item->id; ?>" value="<?php echo $menu_item->favourite; ?>" />
                            <a href="#" onclick="select_favourite(<?php echo intval($menu_item->id); ?>);return false;"><img id="fav_select_<?php echo $menu_item->id; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path . "/images/icons/$img"; ?>" border="0" alt="<?php echo $alt; ?>" /></a>
                        </td>
                    </tr>
                    <?php
                }
                foreach ($extension_menu_items as $menu_item)
                {
                    $img = $menu_item->favourite ? 'tick.png' : 'cross.png';
                    $task = $menu_item->favourite ? 'unpublish' : 'publish';
                    $alt = $menu_item->favourite ? NBILL_FAVOURITE_YES : NBILL_FAVOURITE_NO;
                    ?>
                    <tr>
                        <td><img src="<?php echo str_replace("[NBILL_FE]", nbf_cms::$interop->nbill_site_url_path, $menu_item->image); ?>" alt="<?php echo stripslashes(@constant($menu_item->text)); ?>" /></td>
                        <td><?php echo stripslashes(@constant($menu_item->text)); ?></td>
                        <td><?php echo stripslashes(@constant($menu_item->description)); ?></td>
                        <td style="text-align:center">
                            <input type="hidden" name="ext_favourite_<?php echo $menu_item->id; ?>" id="ext_favourite_<?php echo $menu_item->id; ?>" value="<?php echo $menu_item->favourite; ?>" />
                            <a href="#" onclick="select_ext_favourite('<?php echo $menu_item->id; ?>');return false;"><img id="ext_fav_select_<?php echo $menu_item->id; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path . "/images/icons/$img"; ?>" border="0" alt="<?php echo $alt; ?>" /></a>
                        </td>
                    </tr>
                    <?php
                } ?>
            </table>
        </form>
        <?php
    }
}
