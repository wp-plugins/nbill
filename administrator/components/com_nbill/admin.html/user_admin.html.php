<?php
/**
* HTML output for user admin feature (accessing nBill admin via front end)
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillUserAdmin
{
	public static function showUserList($rows, $pagination)
	{
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "user_admin"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_USER_ADMIN_TITLE; ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		} ?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
		<p align="left"><?php echo NBILL_USER_ADMIN_INTRO_BETA; ?></p>

        <?php
        $name_search = nbf_common::get_param($_REQUEST,'user_admin_name_search', '', true);
        $user_search = nbf_common::get_param($_REQUEST,'user_admin_user_search', '', true);
        $email_search = nbf_common::get_param($_REQUEST,'user_admin_email_search', '', true);
        echo NBILL_USER_ADMIN_NAME . " <input type=\"text\" name=\"user_admin_name_search\" value=\"" . $name_search . "\" />&nbsp; ";
        echo NBILL_USER_ADMIN_USERNAME . " <input type=\"text\" name=\"user_admin_user_search\" value=\"" . $user_search . "\" />&nbsp; ";
        echo NBILL_USER_ADMIN_EMAIL . " <input type=\"text\" name=\"user_admin_email_search\" value=\"" . $email_search . "\" />&nbsp; ";
        echo "&nbsp;&nbsp;<input type=\"submit\" class=\"button btn\" name=\"dosearch\" value=\"" . NBILL_GO . "\" />";
        echo "</p>";
        ?>
        <input type="hidden" name="action_id" id="action_id" value="" />

        <div class="rounded-table">
            <table class="adminlist">
            <tr class="nbill-admin-title-row">
                <th class="title">
                    <?php echo NBILL_USER_ADMIN_NAME; ?>
                </th>
                <th class="title">
				    <?php echo NBILL_USER_ADMIN_USERNAME; ?>
			    </th>
			    <th class="title">
                    <?php echo NBILL_USER_ADMIN_EMAIL; ?>
                </th>
                <th class="selector">
                    <?php echo NBILL_USER_ADMIN_ACCESS; ?>
                </th>
		    </tr>
		    <?php
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];
                    $img = $row->admin_via_fe ? 'tick.png' : 'cross.png';
                    $task = $row->admin_via_fe ? 'deny' : 'grant';
                    $alt = $row->admin_via_fe ? NBILL_USER_ADMIN_ACCESS_GRANTED : NBILL_USER_ADMIN_ACCESS_DENIED;
				    echo "<tr>";
				    echo "<td class=\"list-value\">" . $row->name . "</td>";
                    echo "<td class=\"list-value\">" . $row->username . "</td>";
				    echo "<td class=\"list-value word-breakable\">" . $row->email . "</td>";
                    echo "<td class=\"selector\">";
                    if (!(nbf_cms::$interop->user->id == $row->user_id && nbf_common::get_param($_REQUEST, 'nbill_admin_via_fe')))
                    {
                        echo "<a href=\"#\" title=\"$alt\" onclick=\"document.adminForm.action_id.value='" . $row->user_id . "';document.adminForm.task.value='$task';document.adminForm.submit();return false;\">";
                    }
                    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/$img\" border=\"0\" alt=\"$alt\" />";
                    if (!(nbf_cms::$interop->user->id == $row->user_id && nbf_common::get_param($_REQUEST, 'nbill_admin_via_fe')))
                    {
                        echo "</a>";
                    }
                    echo "</td>";
				    echo "</tr>";
			    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="5" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		<input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
		<input type="hidden" name="action" value="user_admin" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="box_checked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0">
		</form>
		<?php
	}
}