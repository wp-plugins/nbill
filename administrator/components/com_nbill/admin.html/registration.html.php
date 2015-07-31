<?php
/**
* HTML output for registration (license key) feature
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillRegistration
{
    public static function showRegistration($license_key)
    {
        ?>
        <script type="text/javascript" language="javascript">
        function nbill_submit_task(task_name)
        {
            document.adminForm.task.value=task_name;
            document.adminForm.submit();
        }
        </script>
        <table class="adminheading" style="width:auto;">
        <tr>
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "registration"); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_REGISTRATION_TITLE; ?>
            </th>
        </tr>
        </table>
        <div class="nbill-message-ie-padding-bug-fixer"></div>
        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm" style="clear:both;">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="registration" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
        <?php nbf_html::add_filters(); ?>

        <div class="rounded-table">
            <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
            <tr>
                <th colspan="3"><?php echo NBILL_REGISTRATION_TITLE; ?></th>
            </tr>
            <tr>
                <td width="15%">
                    <?php echo NBILL_REG_LICENSE_KEY; ?>
                </td>
                <td>
                    <input type="text" name="license_key" value="<?php echo $license_key; ?>" class="inputbox long" />
                    <?php
                    $eula_link = "<a target=\"_blank\" href=\"http://" . NBILL_BRANDING_EULA . "\">" . NBILL_REG_EULA . "</a>";
                    nbf_html::show_static_help(sprintf(NBILL_REG_INSTR_LICENSE_KEY, $eula_link), "license_key"); ?>
                </td>
            </tr>
            </table>
        </div>
        <?php
    }
}