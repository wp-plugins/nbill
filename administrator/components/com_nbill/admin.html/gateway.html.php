<?php
/**
* HTML output for payment gateway list
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillGateway
{
	public static function showGateways($rows, $pagination)
	{
		foreach ($rows as $row)
		{
			if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . nbf_common::nb_strtolower($row->gateway_id) . "/" . nbf_common::nb_strtolower($row->gateway_id) . "." . nbf_cms::$interop->language . ".php"))
			{
				include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . nbf_common::nb_strtolower($row->gateway_id) . "/" . nbf_common::nb_strtolower($row->gateway_id) . "." . nbf_cms::$interop->language . ".php");
			}
			else
			{
				if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . nbf_common::nb_strtolower($row->gateway_id) . "/" . nbf_common::nb_strtolower($row->gateway_id) . ".en-GB.php"))
				{
					include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . nbf_common::nb_strtolower($row->gateway_id) . "/" . nbf_common::nb_strtolower($row->gateway_id) . ".en-GB.php");
				}
                else
                {
                    if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . nbf_common::nb_strtolower($row->gateway_id) . "/" . nbf_common::nb_strtolower($row->gateway_id) . "_english.php"))
                    {
                        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.backward.compatibility.php");
                        //if (!defined("_VALID_MOS")) {define("_VALID_MOS", "1");} //For backward compatibility
                        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . nbf_common::nb_strtolower($row->gateway_id) . "/" . nbf_common::nb_strtolower($row->gateway_id) . "_english.php");
                    }
                }
			}
		} ?>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "payment"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_GATEWAY_TITLE; ?>
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
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="gateway" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<p align="left"><?php echo sprintf(NBILL_GATEWAY_INTRO, '<a href="http://' . NBILL_BRANDING_WEBSITE . '/" target="_blank">' . NBILL_BRANDING_WEBSITE . '</a>'); ?></p>

        <div class="rounded-table">
            <table class="adminlist">
            <tr class="nbill-admin-title-row">
                <th class="selector">
			    #
			    </th>
                <th class="selector">
                    <input type="checkbox" name="check_all" value="" onclick="for(var i=0; i<<?php echo count($rows); ?>;i++) {if(document.getElementById('cb' + i)){document.getElementById('cb' + i).checked=this.checked;}} document.adminForm.box_checked.value=this.checked;" />
			    </th>
			    <th class="title">
				    <?php echo NBILL_GATEWAY_NAME; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_GATEWAY_DISPLAY_NAME; ?>
			    </th>
			    <th class="title responsive-cell priority">
				    <?php echo NBILL_GATEWAY_DESCRIPTION; ?>
			    </th>
                <th class="title" colspan="2">
                    <?php echo NBILL_GATEWAY_ORDERING; ?>
                </th>
			    <th class="selector">
				    <?php echo NBILL_GATEWAY_PUBLISHED; ?>
			    </th>
		    </tr>
		    <?php
		    for ($i=0, $n=count( $rows ); $i < $n; $i++)
		    {
			    $row = &$rows[$i];

			    $img 	= $row->published ? 'tick.png' : 'cross.png';
			    $task 	= $row->published ? 'unpublish' : 'publish';
			    $alt 	= $row->published ? 'Published' : 'Unpublished';

			    $files_present = file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . $row->gateway_id . "/" . $row->gateway_id . ".php");
			    $link = nbf_cms::$interop->admin_page_prefix . "&action=gateway&task=edit&cid=$row->id";
			    echo "<tr>";
                echo "<td class=\"selector\">";
                echo $pagination->list_offset + $i + 1;
                $checked = nbf_html::id_checkbox($i, $row->id);
                echo "</td><td class=\"selector\">" . ($files_present ? "" : "<span style=\"display:none;\">") . $checked . ($files_present ? "" : "</span>") . "</td>";
			    if ($files_present)
			    {
                    echo "<td class=\"list-value\"><a href=\"$link\" title=\"" . NBILL_EDIT_GATEWAY . "\">" . $row->gateway_id . "</a></td>";
				    echo "<td class=\"list-value\">" . (defined($row->display_name) ? constant($row->display_name) : $row->display_name) . "</td>";
				    if ($row->description == "NBILL_PAYPAL_DESC")
				    {
					    //Insert affiliate link for paypal merchant account!
					    $gateway_desc = sprintf(@constant("NBILL_PAYPAL_DESC"), "<a target=\"_blank\" href=\"https://www.paypal.com/uk/mrb/pal=XZ9QZ6WU9G8FU\">", "</a>");
				    }
				    else
				    {
					    $gateway_desc = @constant($row->description);
				    }
				    if (nbf_common::nb_strlen($gateway_desc) == 0)
				    {
					    $gateway_desc = $row->description;
				    }
				    echo "<td class=\"list-value responsive-cell priority\">" . @$gateway_desc . "</td>";
			    }
			    else if ($row->gateway_id == 'offline')
                {
                    echo "<td class=\"list-value\">" . $row->gateway_id . "</td>";
                    echo "<td class=\"list-value\">" . (defined($row->display_name) ? constant($row->display_name) : $row->display_name) . "</td>";
                    echo "<td class=\"list-value responsive-cell priority\">" . NBILL_GATEWAY_OFFLINE_DESC . "</td>";
                }
                else
			    {
                    echo "<td class=\"list-value\" colspan = \"3\">" . sprintf(NBILL_GATEWAY_FILES_MISSING, $row->gateway_id) . "</td>";
			    }
                echo "<td class=\"selector\">";
                echo $pagination->order_up_arrow($i);
                echo "</td><td class=\"selector\">";
                echo $pagination->order_down_arrow($i, $n);
                echo "</td>";
			    echo "<td class=\"selector\">";
			    echo "<a href=\"#\" onclick=\"for(var i=0; i<" . count($rows) . ";i++) {document.getElementById('cb' + i).checked=false};document.getElementById('cb$i').checked=true;document.adminForm.task.value='$task';document.adminForm.submit();return false;\">";
			    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/$img\" border=\"0\" alt=\"$alt\" />";
			    echo "</a></td>";
			    echo "</tr>";
		    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="8" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		</form>
		<?php
	}

	public static function editGateway($id, $rows, $published, $display_name, $selected_code, $voucher_codes)
	{
        $gateway_row = $rows[0];
		if (is_object($gateway_row))
		{
			if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . nbf_common::nb_strtolower($gateway_row->gateway_id) . "/" . nbf_common::nb_strtolower($gateway_row->gateway_id) . "." . nbf_cms::$interop->language . ".php"))
			{
				include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . nbf_common::nb_strtolower($gateway_row->gateway_id) . "/" . nbf_common::nb_strtolower($gateway_row->gateway_id) . "." . nbf_cms::$interop->language . ".php");
			}
			else
			{
				if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . nbf_common::nb_strtolower($gateway_row->gateway_id) . "/" . nbf_common::nb_strtolower($gateway_row->gateway_id) . ".en-GB.php"))
				{
					include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . nbf_common::nb_strtolower($gateway_row->gateway_id) . "/" . nbf_common::nb_strtolower($gateway_row->gateway_id) . ".en-GB.php");
				}
                else
                {
                    if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . nbf_common::nb_strtolower($gateway_row->gateway_id) . "/" . nbf_common::nb_strtolower($gateway_row->gateway_id) . "_english.php"))
                    {
                        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.backward.compatibility.php");
                        //if (!defined("_VALID_MOS")) {define("_VALID_MOS", "1");} //For backward compatibility
                        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . nbf_common::nb_strtolower($gateway_row->gateway_id) . "/" . nbf_common::nb_strtolower($gateway_row->gateway_id) . "_english.php");
                    }
                }
			}
		}
		?>

        <script type="text/javascript">
        function nbill_submit_task(task_name)
        {
            var form = document.adminForm;
            form.task.value=task_name;
            form.submit();
            return;
        }
        </script>

		<table class="adminheading" style="width:auto;">
			<tr>
				<th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "payment"); ?>>
					<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>: <?php echo NBILL_EDIT_GATEWAY; ?>
				</th>
			</tr>
		</table>
		<div class="nbill-message-ie-padding-bug-fixer"></div>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="gateway" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $id;?>" />
		<?php nbf_html::add_filters(); ?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
			    <tr>
				    <th colspan="2"><?php echo NBILL_GATEWAY_SETTINGS; ?></th>
			    </tr>

                <?php
                if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin." . $gateway_row->gateway_id . "/" . $gateway_row->gateway_id . ".functions.php"))
                { ?>
                    <tr>
                        <td colspan="2" style="padding-top:5px;padding-bottom: 5px;">
                        <?php echo sprintf(NBILL_GATEWAY_EXTRA_FUNCTIONS, '<a href="' . nbf_cms::$interop->admin_page_prefix . '&action=gateway&task=functions&gateway=' . $gateway_row->gateway_id . '">' . NBILL_CLICK_HERE . '</a>'); ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>

			    <tr>
				    <td class="nbill-setting-caption">
					    <?php echo NBILL_GATEWAY_DISPLAY_NAME; ?>
				    </td>
				    <td class="nbill-setting-value">
					    <input type="text" name="display_name" value="<?php echo str_replace("\"", "&quot;", $display_name); ?>" class="inputbox" />
                        <?php nbf_html::show_static_help(NBILL_INSTR_GATEWAY_DISPLAY_NAME_2, "display_name_help"); ?>
				    </td>
			    </tr>
			    <tr>
				    <td class="nbill-setting-caption">
					    <?php echo NBILL_GATEWAY_PUBLISHED; ?>
				    </td>
				    <td class="nbill-setting-value">
					    <?php
					    echo nbf_html::yes_or_no_options("published", "", $published); ?>
                        <?php nbf_html::show_static_help(NBILL_INSTR_GATEWAY_PUBLISHED_2, "published_help"); ?>
				    </td>
			    </tr>
                <tr>
                    <td class="nbill-setting-caption">
                        <?php echo NBILL_GATEWAY_FEE_OR_DISCOUNT_CODE; ?>
                    </td>
                    <td class="nbill-setting-value">
                        <?php
                        $voucher_list = array();
                        $voucher_list[] = nbf_html::list_option("", NBILL_NOT_APPLICABLE);
                        foreach ($voucher_codes as $voucher_code)
                        {
                            $voucher_list[] = nbf_html::list_option($voucher_code->voucher, $voucher_code->voucher);
                        }
                        echo nbf_html::select_list($voucher_list, "voucher_code", 'class="inputbox"', $selected_code);
                        ?>
                        <?php nbf_html::show_static_help(NBILL_INSTR_GATEWAY_FEE_OR_DISCOUNT_CODE, "voucher_code_help"); ?>
                    </td>
                </tr>
                <!-- Custom Fields Placeholder -->
			    <?php
				    foreach ($rows as $setting)
				    {
					    if ($setting->admin_can_edit || $setting->data_type == 'label')
					    {
                            $label = @constant($setting->label);
                            if (nbf_common::nb_strlen($label) == 0)
                            {
                                $label = $setting->label;
                            }
                            $value = @constant($setting->g_value);
                            if (nbf_common::nb_strlen($value) == 0)
                            {
                                $value = $setting->g_value;
                            }
                            $help_text = @constant($setting->help_text);
                            if (nbf_common::nb_strlen($help_text) == 0)
                            {
                                $help_text = $setting->help_text;
                            }

                            //Replace any tokens (website address, page prefix, component url, line breaks)
                            $label = replace_tokens($label);
                            $value = replace_tokens($value, $setting->data_type == 'label');
                            $help_text = replace_tokens($help_text);
					        ?>

						    <tr id="row_<?php echo $setting->g_key; ?>">
                                <?php if ($setting->data_type == 'label')
                                {
                                    //This is just some information to output to the user - value could be held in any of the 3 settings
                                    ?>
                                    <td colspan="2">
                                        <?php
                                        if (strlen($label) > 0)
                                        {
                                            echo $label . ' ';
                                        }
                                        if (strlen($value) > 0)
                                        {
                                            echo $value . ' ';
                                        }
                                        if (strlen($help_text) > 0)
                                        {
                                            echo $help_text;
                                        }
                                        ?>
                                    </td>
                                    <?php
                                }
                                else
                                { ?>
							        <td class="nbill-setting-caption">
								        <?php
								        echo $label;
                                        if ($setting->gateway_id == "paypal" && $setting->g_key == "include_breakdown")
                                        {
                                            ?>
                                            <br /><span style="color:#ff0000;"><?php echo NBILL_DEPRECATED; ?></span>
                                            <?php
                                        } ?>
							        </td>
							        <td class="nbill-setting-value">
                                        <?php switch ($setting->data_type)
                                        {
                                            case 'bool':
                                            case 'boolean':
                                                echo nbf_html::yes_or_no_options('gateway_' . $setting->g_key, '', intval($value));
                                                break;
                                            case 'select':
                                                $options = explode(",", $setting->options);
                                                $option_list = array();
                                                foreach ($options as $option)
                                                {
                                                    $option_list[] = nbf_html::list_option($option, $option);
                                                }
                                                echo nbf_html::select_list($option_list, 'gateway_' . $setting->g_key, '', $value);
                                                break;
                                            case 'text':
                                                ?>
                                                <textarea name="gateway_<?php echo $setting->g_key; ?>"><?php echo $value; ?></textarea>
                                                <?php
                                                break;
                                            case 'int':
                                            case 'integer':
                                            case 'decimal':
                                            case 'float':
                                            case 'char':
                                            case 'varchar':
                                            default:
                                                ?>
                                                <input type="text" name="gateway_<?php echo $setting->g_key; ?>" value="<?php echo $value; ?>" class="inputbox" />
                                                <?php
                                                break;
                                        } ?>
                                        <?php nbf_html::show_static_help($help_text, $setting->g_key . "_help"); ?>
							        </td>
                                <?php } ?>
						    </tr>
					    <?php
					    }
				    }
			    ?>
		    </table>
        </div>

		</form>
		<?php
	}
}