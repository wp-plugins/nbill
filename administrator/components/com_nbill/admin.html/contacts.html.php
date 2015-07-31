<?php
/**
* HTML output for contacts feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillContacts
{
	public static function showContacts($rows, $pagination, $vendors)
	{
        nbf_html::include_overlib_js();
        ?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "contacts"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_CONTACTS_TITLE; ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		}
		?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="contacts" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

		<p align="left"><?php echo NBILL_CONTACT_INTRO; ?></p>
		<?php
			//Display filter for contact name
			if (count($vendors) < 2)
			{
				echo "<p align=\"left\">";
			}
			$contact_search = nbf_common::get_param($_POST,'contact_search', '', true);
            $contact_user_search = nbf_common::get_param($_REQUEST,'contact_user_search', '', true);
            $contact_email_search = nbf_common::get_param($_REQUEST,'contact_email_search', '', true);
			echo "&nbsp;&nbsp;" . NBILL_CONTACT . " <input type=\"text\" name=\"contact_search\" value=\"" . $contact_search . "\" />";
            echo "&nbsp;&nbsp;" . NBILL_CONTACT_USER . " <input type=\"text\" name=\"contact_user_search\" value=\"" . $contact_user_search . "\" />";
            echo "&nbsp;&nbsp;" . NBILL_EMAIL_ADDRESS . " <input type=\"text\" name=\"contact_email_search\" value=\"" . $contact_email_search . "\" />";
			echo "&nbsp;&nbsp;<input type=\"submit\" class=\"button btn\" name=\"dosearch\" value=\"" . NBILL_GO . "\" />";
			echo "</p>";
		?>

        <div class="rounded-table">
            <table class="adminlist">
            <tr class="nbill-admin-title-row">
                <th class="selector">
			    #
			    </th>
                <th class="selector">
                    <input type="checkbox" name="check_all" value="" onclick="for(var i=0; i<<?php echo count($rows); ?>;i++) {document.getElementById('cb' + i).checked=this.checked;} document.adminForm.box_checked.value=this.checked;" />
			    </th>
			    <th class="title">
				    <?php echo NBILL_CONTACT_ID; ?>
			    </th>
                <th class="title">
				    <?php echo NBILL_CONTACT_NAME; ?>
			    </th>
                <th class="selector responsive-cell priority">
                    <?php echo NBILL_CONTACT_ENTITY; ?>
                </th>
                <th class="title">
				    <?php echo NBILL_CONTACT_USER; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_EMAIL_ADDRESS; ?>
			    </th>
			    <th class="title responsive-cell optional">
				    <?php echo NBILL_TELEPHONE; ?>
			    </th>
		    </tr>
		    <?php
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];
				    $link = nbf_cms::$interop->admin_page_prefix . "&action=contacts&task=edit&cid=$row->id&contact_search=$contact_search";
				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\">$checked</td>";
				    echo "<td class=\"list-value\">" . $row->id . "</td>";
				    echo "<td class=\"list-value\"><a href=\"$link\" title=\"" . NBILL_EDIT_CONTACT . "\">" . (nbf_common::nb_strlen($row->name) > 0 ? $row->name : NBILL_CONTACT_CONTACT_NAME_UNKNOWN) . "</a></td>";
                    echo "<td class=\"selector responsive-cell priority\">";
                    if ($row->is_client)
                    {
                        echo "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=clients&for_contact=" . $row->id . "&client_search=" . urlencode($row->name) . "\" title=\"" . NBILL_CONTACT_SHOW_CLIENTS . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/clients.gif" . "\" alt=\"" . NBILL_CONTACT_SHOW_CLIENTS . "\" /></a>";
                    }
                    if ($row->is_supplier)
                    {
                        echo "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=suppliers&for_contact=" . $row->id  . "&supplier_search=" . urlencode($row->name) . "\" title=\"" . NBILL_CONTACT_SHOW_SUPPLIERS . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/suppliers.gif" . "\" alt=\"" . NBILL_CONTACT_SHOW_SUPPLIERS . "\" /></a>";
                    }
                    echo "</td>";
                    echo "<td class=\"list-value\">$row->username";
				    if ($row->subscriber)
				    {
					    echo "&nbsp;<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=contacts&task=mambot_remove&id=$row->subscriber\" onclick=\"return confirm('" . NBILL_CONTACT_CANCEL_MAMBOT_CONTROL . "');\" onmouseover=\"return nbill_overlib('" . NBILL_CONTACT_UNDER_MAMBOT_CONTROL . "', '', '', '', '');\" onmouseout=\"return nbill_overlib_nd();\"><img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/subscriber.png\" alt=\"\" border=\"0\" /></a>";
				    }
				    echo "</td>";
				    echo "<td class=\"list-value word-breakable\"><a href=\"mailto:" . $row->email_address . "\">" . $row->email_address . "</a></td>";
				    echo "<td class=\"list-value responsive-cell optional\">" . $row->telephone . "</td>";
				    echo "</tr>";
			    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="9" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		</form>
		<?php

	}

	/**
	* Edit a contact (or create a new one)
	*/
	public static function editContact($contact_id, $row, $custom_fields, $field_options, $country_codes, $currency_codes, $vendors, $user_list)
	{
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.client.php");
		?>
		<script type="text/javascript">
		var body_height = 1;
        var form_dirty = false;
		function resize_nbill_iframe()
		{
			<?php if ($contact_id)
			{ ?>
				if(self==parent)
				{
					return false;
				}
				else
				{
					if (document.body.offsetHeight > 1 && body_height == 1)
					{
						body_height = document.body.offsetHeight;
						parent.document.getElementById('ifr_contact_<?php echo $contact_id; ?>').style.height=((body_height * 1) - 30) + 'px';
					}
				}
			<?php }
			else
			{
				?>
				return false;
				<?php
			} ?>
		}

		<?php nbf_html::add_js_validation_numeric(); ?>
		function nbill_submit_task(task_name)
        {
            var form = document.adminForm;
			if (task_name == 'cancel')
            {
				form.task.value=task_name;
                form.submit();
				return;
			}

			// do field validation
			if (form.user_id.options != null && form.user_id.options[form.user_id.selectedIndex].value == -2)
			{
				//Create new user
				if (form.username.value == "" || form.password.value == "" || form.email_address.value == "")
				{
					alert ('<?php echo NBILL_USERNAME_PASSWORD_REQUIRED; ?>');
				}
                else
                {
                    form.task.value=task_name;
                    form.submit();
                }
			}
			else
			{
				form.task.value=task_name;
                form.submit();
			}
		}
		function user_selected()
		{
			//If [Create New User] (value = -2), display user name/password boxes, otherwise, hide them
			if (document.getElementById('user_id').value == -2) {
				document.getElementById('userdetails').style.display = 'block';
                document.getElementById('reset_password_table').style.display = 'none';
			} else if (document.getElementById('user_id').value == -1) {
                document.getElementById('reset_password_table').style.display = 'none';
                document.getElementById('userdetails').style.display = 'none';
            } else {
				document.getElementById('userdetails').style.display = 'none';
                document.getElementById('reset_password_table').style.display = 'block';
			}
		}
        function copy_address_from_entity()
        {
            document.getElementById('address_1').value = parent.document.getElementById('address_1').value;
            document.getElementById('address_2').value = parent.document.getElementById('address_2').value;
            document.getElementById('address_3').value = parent.document.getElementById('address_3').value;
            document.getElementById('town').value = parent.document.getElementById('town').value;
            document.getElementById('state').value = parent.document.getElementById('state').value;
            document.getElementById('postcode').value = parent.document.getElementById('postcode').value;
            document.getElementById('country').value = parent.document.getElementById('country').value;

            document.getElementById('shipping_address_1').value = parent.document.getElementById('shipping_address_1').value;
            document.getElementById('shipping_address_2').value = parent.document.getElementById('shipping_address_2').value;
            document.getElementById('shipping_address_3').value = parent.document.getElementById('shipping_address_3').value;
            document.getElementById('shipping_town').value = parent.document.getElementById('shipping_town').value;
            document.getElementById('shipping_state').value = parent.document.getElementById('shipping_state').value;
            document.getElementById('shipping_postcode').value = parent.document.getElementById('shipping_postcode').value;
            document.getElementById('shipping_country').value = parent.document.getElementById('shipping_country').value;
            document.getElementById('same_as_billing').checked = parent.document.getElementById('same_as_billing').checked;
            shipping_same_as_billing(document.getElementById('same_as_billing').checked);
        }
        function check_email_exists(new_email_address)
        {
            show_wait_message(100, '<?php echo NBILL_CONTACT_CHECKING_EMAIL; ?>');
            var other_contact_name = submit_sjax_request('check_email', 'contact_id=<?php echo intval($contact_id); ?>&email=' + new_email_address);
            if (other_contact_name.length > 0)
            {
                alert('<?php echo NBILL_CONTACT_EMAIL_IN_USE; ?>'.replace('%s1', new_email_address).replace('%s2', other_contact_name));
                setTimeout(function(){document.getElementById('email_address').focus();}, 100);
                return false;
            }
            return true;
        }
        function shipping_same_as_billing(use_billing)
        {
            document.getElementById('shipping_address_1').disabled = use_billing;
            document.getElementById('shipping_address_2').disabled = use_billing;
            document.getElementById('shipping_address_3').disabled = use_billing;
            document.getElementById('shipping_town').disabled = use_billing;
            document.getElementById('shipping_state').disabled = use_billing;
            document.getElementById('shipping_postcode').disabled = use_billing;
            document.getElementById('shipping_country').disabled = use_billing;
        }
		</script>

        <table class="adminheading" style="width:auto;">
        <tr>
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, nbf_common::get_param($_REQUEST, 'action')); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
                <?php echo $contact_id ? NBILL_EDIT_CONTACT . " '" . implode(" ", array($row->first_name, $row->last_name)) . "'" : NBILL_NEW_CONTACT; ?>
            </th>
        </tr>
        </table>

        <div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		} ?>

		<form action="<?php echo nbf_common::get_param($_REQUEST, 'nbill_entity_iframe') ? nbf_cms::$interop->admin_popup_page_prefix : nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="contacts" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $contact_id;?>" />
        <?php if (nbf_common::get_param($_REQUEST, 'tmpl')) { ?><input type="hidden" name="tmpl" value="<?php echo nbf_common::get_param($_REQUEST, 'tmpl'); ?>" /><?php } ?>
		<input type="hidden" name="hide_billing_menu" value="<?php echo nbf_common::get_param($_REQUEST, 'hide_billing_menu'); ?>" />
		<input type="hidden" name="nbill_entity_iframe" value="<?php echo nbf_common::get_param($_REQUEST, 'nbill_entity_iframe'); ?>" />
		<?php nbf_html::add_filters(); ?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-contacts">
		    <tr>
			    <th colspan="2"><?php echo NBILL_CONTACT_DETAILS; ?></th>
		    </tr>
            <!-- Custom Fields Placeholder -->
		    <tr id="nbill-admin-tr-contact-user">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CONTACT_USER; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
                    if ($user_list)
				    {
					    $user = array();
					    $user[] = nbf_html::list_option(-1, NBILL_NONE);
					    $user[] = nbf_html::list_option(-2, NBILL_CREATE_USER);
					    foreach($user_list as $listed_user)
					    {
						    $user[] = nbf_html::list_option($listed_user->id, $listed_user->username);
					    }
					    echo nbf_html::select_list($user, "user_id", 'id="user_id" class="inputbox" onchange="user_selected();form_dirty=true;"', @$row->user_id);
				    }
				    else
				    {
					    ?><input type="text" name="user_id" id="user_id" value="<?php echo $row->user_id; ?>" class="inputbox" onchange="user_selected();form_dirty=true;" /><?php
				    } ?>
				    <table cellpadding="0" cellspacing="0" border="0" id="userdetails" style="display:none">
                        <tr><td><?php echo NBILL_USERNAME; ?> </td><td><input type="text" name="username" id="username" value="" class="inputbox" autocomplete="off" /></td></tr>
				        <tr><td><?php echo NBILL_PASSWORD; ?> </td><td><input type="password" name="password" id="password" value="" class="inputbox" /></td></tr>
                    </table>
                    <table cellpadding="0" cellspacing="0" border="0" id="reset_password_table" style="display:<?php echo intval(@$row->user_id) > 0 ? 'block' : 'none'; ?>">
                        <tr><td><?php echo NBILL_RESET_PASSWORD; ?> </td><td><input type="password" name="reset_password" id="reset_password" value="" class="inputbox" /></td></tr>
                    </table>

                    <?php nbf_html::show_static_help($user_list ? NBILL_INSTR_CONTACT_USER : NBILL_INSTR_CONTACT_USER_ID, "user_id_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-first-name">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CONTACT_FIRST_NAME; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="first_name" id="first_name" value="<?php echo str_replace("\"", "&quot;", @$row->first_name); ?>" class="inputbox" autocomplete="off" onchange="form_dirty=true;" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_CONTACT_NAME, "first_name_help"); ?>
			    </td>
		    </tr>
            <tr id="nbill-admin-tr-last-name">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_CONTACT_LAST_NAME; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="last_name" id="last_name" value="<?php echo str_replace("\"", "&quot;", @$row->last_name); ?>" class="inputbox" autocomplete="off" onchange="form_dirty=true;" />
                </td>
            </tr>
            <?php if (nbf_common::get_param($_REQUEST, 'nbill_entity_iframe') != null)
            { ?>
            <tr id="nbill-admin-tr-copy-address">
                <td class="nbill-setting-caption">&nbsp;</td>
                <td class="nbill-setting-value">
                    <input type="button" name="copy_address" id="copy_address" class="button btn" value="<?php echo NBILL_CONTACT_COPY_ADDRESS_FROM_CLIENT; ?>" onclick="copy_address_from_entity();form_dirty=true;" />
                    <?php nbf_html::show_static_help(NBILL_CONTACT_COPY_ADDRESS_HELP, "copy_address_help"); ?>
                </td>
            </tr>
            <?php } ?>

            <tr><td colspan="2">
            <?php
            $tab_address = new nbf_tab_group();
            $tab_address->start_tab_group("contact_address");
            $tab_address->add_tab_title("billing", NBILL_ADDRESS_BILLING);
            $tab_address->add_tab_title("shipping", NBILL_ADDRESS_SHIPPING);
            ob_start();
            ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-contacts-billing-address">
		    <tr id="nbill-admin-tr-address-1">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_ADDRESS_1; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="address_1" id="address_1" value="<?php echo str_replace("\"", "&quot;", @$row->address_1); ?>" class="inputbox" onchange="form_dirty=true;" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_CONTACT_ADDRESS, "address_1_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-address-2">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_ADDRESS_2; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="address_2" id="address_2" value="<?php echo str_replace("\"", "&quot;", @$row->address_2); ?>" class="inputbox" onchange="form_dirty=true;" />
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-address-3">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_ADDRESS_3; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="address_3" id="address_3" value="<?php echo str_replace("\"", "&quot;", @$row->address_3); ?>" class="inputbox" onchange="form_dirty=true;" />
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-town">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_TOWN; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="town" id="town" value="<?php echo str_replace("\"", "&quot;", @$row->town); ?>" class="inputbox" onchange="form_dirty=true;" />
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-state">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_STATE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="state" id="state" value="<?php echo str_replace("\"", "&quot;", @$row->state); ?>" class="inputbox" onchange="form_dirty=true;" />
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-postcode">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_POSTCODE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="postcode" id="postcode" value="<?php echo str_replace("\"", "&quot;", @$row->postcode); ?>" class="inputbox" onchange="form_dirty=true;" />
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-country">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CONTACT_COUNTRY; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $country = array();
					    $selected_country = "";
					    foreach ($country_codes as $country_code)
					    {
						    $country[] = nbf_html::list_option($country_code['code'], nbf_common::nb_ucwords(nbf_common::nb_strtolower($country_code['description'])));
					    }
					    if (!$contact_id)
					    {
						    //For new contacts, default to default vendor's country
						    foreach ($vendors as $vendor)
						    {
							    if ($vendor->default_vendor)
							    {
								    $selected_country = $vendor->vendor_country;
								    break;
							    }
						    }
						    if (!$selected_country)
						    {
							    $selected_country= $vendors[0]->vendor_country;
						    }
					    }
					    else
					    {
						    $selected_country = @$row->country;
					    }
					    echo nbf_html::select_list($country, "country", 'id="country" class="inputbox" onchange="form_dirty=true;"', $selected_country);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_CONTACT_COUNTRY, "country_help"); ?>
			    </td>
		    </tr>
            </table>
            <?php
            $tab_address->add_tab_content("billing", ob_get_clean());
            ob_start();
            $disabled_attr = @$row->shipping_address->id ? '' : ' disabled="disabled"';
            ?>
            <label><input type="checkbox" name="same_as_billing" id="same_as_billing" onclick="shipping_same_as_billing(this.checked);"<?php if (!@$row->shipping_address->id) {echo ' checked="checked"';} ?> /><?php echo NBILL_ADDRESS_SAME_AS_BILLING; ?></label>
            <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-contacts-shipping-address">
            <tr id="nbill-admin-tr-shipping-address-1">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_ADDRESS_1; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text"<?php echo $disabled_attr; ?> name="shipping_address_1" id="shipping_address_1" value="<?php echo str_replace("\"", "&quot;", @$row->shipping_address->line_1); ?>" class="inputbox" onchange="form_dirty=true;" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_SHIPPING_ADDRESS_ID, "shipping_address_1_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-shipping-address-2">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_ADDRESS_2; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text"<?php echo $disabled_attr; ?> name="shipping_address_2" id="shipping_address_2" value="<?php echo str_replace("\"", "&quot;", @$row->shipping_address->line_2); ?>" class="inputbox" onchange="form_dirty=true;" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-shipping-address-3">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_ADDRESS_3; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text"<?php echo $disabled_attr; ?> name="shipping_address_3" id="shipping_address_3" value="<?php echo str_replace("\"", "&quot;", @$row->shipping_address->line_3); ?>" class="inputbox" onchange="form_dirty=true;" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-shipping-town">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TOWN; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text"<?php echo $disabled_attr; ?> name="shipping_town" id="shipping_town" value="<?php echo str_replace("\"", "&quot;", @$row->shipping_address->town); ?>" class="inputbox" onchange="form_dirty=true;" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-shipping-state">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_STATE; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text"<?php echo $disabled_attr; ?> name="shipping_state" id="shipping_state" value="<?php echo str_replace("\"", "&quot;", @$row->shipping_address->state); ?>" class="inputbox" onchange="form_dirty=true;" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-shipping-postcode">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_POSTCODE; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text"<?php echo $disabled_attr; ?> name="shipping_postcode" id="shipping_postcode" value="<?php echo str_replace("\"", "&quot;", @$row->shipping_address->postcode); ?>" class="inputbox" onchange="form_dirty=true;" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-shipping-country">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_CONTACT_COUNTRY; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    $selected_country = "";
                    if (!$contact_id) {
                        //For new contacts, default to default vendor's country
                        foreach ($vendors as $vendor) {
                            if ($vendor->default_vendor) {
                                $selected_country = $vendor->vendor_country;
                                break;
                            }
                        }
                        if (!$selected_country) {
                            $selected_country= $vendors[0]->vendor_country;
                        }
                    } else {
                        $selected_country = @$row->shipping_address->country;
                    }
                    echo nbf_html::select_list($country, "shipping_country", 'id="shipping_country"' . $disabled_attr . ' class="inputbox" onchange="form_dirty=true;"', $selected_country);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_CONTACT_COUNTRY, "shipping_country_help"); ?>
                </td>
            </tr>
            </table>
            <?php
            $tab_address->add_tab_content("shipping", ob_get_clean());
            $tab_address->end_tab_group();
            ?>
            </td></tr>
		    <tr id="nbill-admin-tr-email-address">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_EMAIL_ADDRESS; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="email_address" id="email_address" autocomplete="off" value="<?php echo @$row->email_address; ?>" class="inputbox" autocomplete="off" onchange="form_dirty=true;return check_email_exists(this.value);" />
				    <input type="hidden" name="orig_email_address" id="orig_email_address" value="<?php echo @$row->email_address; ?>" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_EMAIL_ADDRESS, "email_address_help"); ?>
			    </td>
		    </tr>
            <tr id="nbill-admin-tr-email-address-2">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_EMAIL_ADDRESS_2; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="email_address_2" id="email_address_2" value="<?php echo @$row->email_address_2; ?>" class="inputbox" onchange="form_dirty=true;" />
                    <input type="hidden" name="orig_email_address_2" id="orig_email_address_2" value="<?php echo @$row->email_address_2; ?>" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_EMAIL_ADDRESS_2, "email_address_2_help"); ?>
                </td>
            </tr>
		    <tr id="nbill-admin-tr-telephone">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_TELEPHONE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="telephone" id="telephone" value="<?php echo str_replace("\"", "&quot;", @$row->telephone); ?>" class="inputbox" onchange="form_dirty=true;" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_CONTACT_TELEPHONE, "telephone_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-telephone-2">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_TELEPHONE_2; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="telephone_2" id="telephone_2" value="<?php echo str_replace("\"", "&quot;", @$row->telephone_2); ?>" class="inputbox" onchange="form_dirty=true;" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_CONTACT_TELEPHONE_2, "telephone_2_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-mobile">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_MOBILE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="mobile" id="mobile" value="<?php echo str_replace("\"", "&quot;", @$row->mobile); ?>" class="inputbox" onchange="form_dirty=true;" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_CONTACT_MOBILE, "mobile_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-fax">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_FAX; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="fax" id="fax" value="<?php echo str_replace("\"", "&quot;", @$row->fax); ?>" class="inputbox" onchange="form_dirty=true;" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_CONTACT_FAX, "fax_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-notes">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_NOTES; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <textarea name="notes" id="notes" onchange="form_dirty=true;"><?php echo @$row->notes; ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_INSTR_NOTES, "notes_help"); ?>
			    </td>
		    </tr>
		    </table>
        </div>

        <?php
        if ($custom_fields && count($custom_fields) > 0)
        {
            ?>
            <div class="rounded-table">
                <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
                <tr>
                    <th colspan="2"><?php echo NBILL_CONTACT_CUSTOM_FIELDS; ?></th>
                </tr>
                <?php
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control.base.php");
                foreach ($custom_fields as $field)
                { ?>
                    <tr id="nbill-admin-tr-custom-field-<?php echo intval($field->id); ?>">
                        <td class="nbill-setting-caption">
                            <?php echo (defined(str_replace("* ", "", $field->label)) ? (nbf_common::nb_strpos($field->label, "* ") !== false ? "* " : "") . constant(str_replace("* ", "", $field->label)) : $field->label) ?>
                        </td>
                        <td class="nbill-setting-value">
                            <?php
                            //Load control object
                            $control_class = "nbf_field_control";
                            if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control." . nbf_common::nb_strtolower($field->field_type) . ".php"))
                            {
                                include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control." . nbf_common::nb_strtolower($field->field_type) . ".php");
                                $control_class .= "_" . nbf_common::nb_strtolower($field->field_type);
                            }
                            $control = new $control_class(null, null);
                            $control->name = $field->name;
                            $control->id = $field->id;
                            $control->suffix = "";
                            if ($field->default_value != null)
                            {
                                $control->default_value = $field->default_value;
                            }
                            if ($field->attributes != null)
                            {
                                //$control->attributes = $field->attributes;
                            }
                            $control->required = $field->required;
                            if ($field->checkbox_text != null)
                            {
                                $control->checkbox_text = $field->checkbox_text;
                            }
                            $control->field_options = $field_options[$field->id];
                            $control->onclick_admin = "";
                            $control->onchange_admin = "";
                            $control->onkeydown_admin = "";
                            $control->render_control();
                            ?>
                            <?php nbf_html::show_static_help($field->help_text ? $field->help_text : "&nbsp;", $field->name . "_help"); ?>
                        </td>
                    </tr>
                <?php }
                ?></table>
            </div>
            <?php
        }
        ?>
		</form>
		<?php
		if ($contact_id)
		{ ?>
		<script type="text/javascript">
			//Wait a mo for output to finish, then resize the iframe (no access to body tag)
			setTimeout('resize_nbill_iframe()', 500);
		</script>
		<?php }
	}
}