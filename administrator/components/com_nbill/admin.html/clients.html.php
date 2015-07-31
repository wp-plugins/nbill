<?php
/**
* HTML output for clients feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillClients
{
    protected static $custom_column_count = 0;

	public static function showClients($rows, $potential, $pagination, $vendors, $attachments = array())
	{
        $potential_suffix = $potential ? "_POT" : "";
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, nbf_common::get_param($_REQUEST, 'action')); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . constant("NBILL_CLIENTS_TITLE$potential_suffix"); ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		}

        if ($potential_suffix == "_POT")
        {
            echo "<p>" . NBILL_CLIENT_INTRO_POT . "</p>";
        }
        ?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0" />
        <input type="hidden" name="attachment_id" value="" />
		<?php
		//Display filter for client name
		if (count($vendors) < 2)
		{
			echo "<p align=\"left\">";
		}
		$client_search = nbf_common::get_param($_REQUEST,'client_search', '', true);
        $client_user_search = nbf_common::get_param($_REQUEST,'client_user_search', '', true);
        $client_email_search = nbf_common::get_param($_REQUEST,'client_email_search', '', true);
		echo NBILL_CLIENT . " <input type=\"text\" name=\"client_search\" value=\"" . $client_search . "\" />&nbsp; ";
        echo NBILL_CLIENT_USER . " <input type=\"text\" name=\"client_user_search\" value=\"" . $client_user_search . "\" />&nbsp; ";
        echo NBILL_EMAIL_ADDRESS . " <input type=\"text\" name=\"client_email_search\" value=\"" . $client_email_search . "\" />";
		if (strtolower(nbf_version::$suffix) != 'lite' && !$potential_suffix)
        {
            //Just those with active orders
		    echo "&nbsp;&nbsp;<span class=\"responsive-cell medium-or-narrow-only\"><br /></span><input type=\"checkbox\" class=\"nbill_form_input\" name=\"active_only\" id=\"active_only\"";
		    if (nbf_common::get_param($_POST,'active_only'))
		    {
			    echo " checked=\"checked\"";
		    }
		    echo " /><label for=\"active_only\" class=\"nbill_form_label\">" . NBILL_ACTIVE_CLIENTS_ONLY . "</label>";
        }
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
                <?php self::renderCustomColumn('id'); ?>
			    <th class="title">
				    <?php echo NBILL_CLIENT_NAME; ?>
			    </th>
                <?php self::renderCustomColumn('name'); ?>
                <th class="selector">
                    <?php echo NBILL_CLIENT_ACTION; ?>
                </th>
                <?php self::renderCustomColumn('action'); ?>
                <th class="title">
				    <?php echo NBILL_CLIENT_USER; ?>
			    </th>
                <?php self::renderCustomColumn('user'); ?>
			    <th class="title responsive-cell priority">
				    <?php echo NBILL_EMAIL_ADDRESS; ?>
			    </th>
                <?php self::renderCustomColumn('email_address'); ?>
			    <th class="title responsive-cell optional">
				    <?php echo NBILL_WEBSITE; ?>
			    </th>
                <?php self::renderCustomColumn('website'); ?>
			    <th class="title responsive-cell wide-only">
				    <?php echo NBILL_TELEPHONE; ?>
			    </th>
                <?php self::renderCustomColumn('telephone'); ?>
		    </tr>
		    <?php
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];
				    $link = nbf_cms::$interop->admin_page_prefix . "&action=" . nbf_common::get_param($_REQUEST, 'action') . "&task=edit&cid=$row->id&client_search=$client_search&client_user_search=$client_user_search&client_email_search=$client_email_search";
				    echo "<tr>";
				    echo "<td class=\"selector\" style=\"text-align:center\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\" style=\"text-align:center\">$checked</td>";
                    self::renderCustomColumn('id', $row);
				    echo "<td class=\"list-value\">";
                    $client_name = $row->company_name;
                    if (nbf_common::nb_strlen($row->name) > 0)
                    {
                        if (nbf_common::nb_strlen($row->company_name) > 0)
                        {
                            $client_name .= " (";
                        }
                        $client_name .= $row->name;
                        if (nbf_common::nb_strlen($row->company_name) > 0)
                        {
                            $client_name .= ")";
                        }
                    }
                    echo "<div style=\"float:left\"><a href=\"$link\" title=\"" . NBILL_EDIT_CLIENT . "\">";
                    echo $client_name;
				    echo "</a></div>";

                    
                    echo "</td>";
                    self::renderCustomColumn('name', $row);
                    echo "<td class=\"selector\" style=\"white-space:nowrap;max-width:150px\">";
                    if ($potential_suffix) {
                        echo "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=quotes&task=new&listed_client_id=$row->id&disable_client_list=1\" title=\"". NBILL_CLIENT_NEW_QUOTE . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/new_quote.gif\" alt=\"" . NBILL_CLIENT_NEW_QUOTE . "\" /></a>";
                        echo "&nbsp;<img alt=\"\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/separator.gif\" />";
                        echo "&nbsp;<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=quotes&client_id=$row->id&show_all=1&client_search=" . urlencode($client_name) . "\" title=\"" . NBILL_CLIENT_VIEW_QUOTES . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/quotes.gif\" alt=\"" . NBILL_CLIENT_VIEW_QUOTES . "\" /></a>";
                    } else {
                        
                        echo "&nbsp;<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=invoices&task=new&listed_client_id=$row->id&disable_client_list=1\" title=\"". NBILL_CLIENT_NEW_INVOICE . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/new_invoice.gif\" alt=\"" . NBILL_CLIENT_NEW_INVOICE . "\" /></a>";
                        echo "&nbsp;<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=credits&task=new&listed_client_id=$row->id&disable_client_list=1\" title=\"". NBILL_CLIENT_NEW_REFUND . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/new_refund.gif\" alt=\"" . NBILL_CLIENT_NEW_REFUND . "\" /></a>";
                        //echo "&nbsp;<img alt=\"\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/separator.gif\" />";
                        echo "<hr class=\"action-separator\" />";
                        
                        echo "&nbsp;<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=invoices&client_id=$row->id&show_all=1&client_search=" . urlencode($client_name) . "\" title=\"" . NBILL_CLIENT_VIEW_INVOICES . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/invoices.gif\" alt=\"" . NBILL_CLIENT_VIEW_INVOICES . "\" /></a>";
                    }
                    echo "</td>";
                    self::renderCustomColumn('action', $row);
                    echo "<td class=\"list-value\">$row->username";
				    echo "</td>";
                    self::renderCustomColumn('user', $row);
				    echo "<td class=\"list-value responsive-cell priority word-breakable\"><a href=\"mailto:" . $row->email_address . "\" title=\"" . $row->email_address . "\"><span class=\"responsive-cell wide-only\">" . $row->email_address . "</span><span class=\"responsive-cell very-narrow-only\"><img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/email-now.gif\" alt=\"" . $row->email_address . "\" /></span></a></td>";
				    if (nbf_common::nb_strlen($row->website_url) > 0 && substr($row->website_url, 0, 7) != "http://")
				    {
					    $url = "http://" . $row->website_url;
				    }
				    else
				    {
					    $url = $row->website_url;
				    }
				    echo "<td class=\"list-value responsive-cell optional word-breakable\"><a href=\"" . $url . "\" target=\"_blank\">" . $row->website_url . "</a></td>";
                    self::renderCustomColumn('website', $row);
				    echo "<td class=\"list-value responsive-cell wide-only word-breakable\">" . $row->telephone . "</td>";
                    self::renderCustomColumn('telephone', $row);
				    echo "</tr>";
			    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="<?php echo 9 + self::$custom_column_count; ?>" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		</form>
		<?php
	}

    protected static function renderCustomColumn($column_name, $row = 'undefined')
    {
        $method = ($row == 'undefined') ? 'render_header' : 'render_row';
        if (file_exists(dirname(__FILE__) . "/custom_columns/clients/after_$column_name.php")) {
            include_once(dirname(__FILE__) . "/custom_columns/clients/after_$column_name.php");
            if (is_callable(array("nbill_admin_clients_after_$column_name", $method))) {
                call_user_func(array("nbill_admin_clients_after_$column_name", $method), $row);
                if ($method == 'render_header') {
                    self::$custom_column_count++;
                }
            }
        }
    }

	/**
	* Edit a client (or create a new one)
	*/
	public static function editClient($client_id, $row, $languages, $potential, $custom_fields, $field_options, $country_codes, $currency_codes, $vendors, $default_email_invoice_option, $ledger, $email_options_xref, $contacts, $contact_custom_fields, $credits, $sync_primary, $last_contact_id = 0, $use_posted_values = false, $attachments = array(), $ip_info = null)
	{
        $potential_suffix = $potential ? "_POT" : "";
		include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.client.php");
		?>
		<script language="javascript" type="text/javascript">
		<?php nbf_html::add_js_validation_numeric(); ?>
		function nbill_submit_task(task_name)
        {
			if (task_name == 'cancel')
            {
				document.adminForm.task.value=task_name;
                document.adminForm.submit();
				return;
			}

			// do field validation
            if (false) { //To simplify for lite edition
            }
			<?php
            $first_vendor_done = false;
            
            ?>
            else {
				//If a contact will be deleted, check that they really want to continue...
				var delete_count = 0;
				<?php
                if ($contacts && count($contacts) > 0)
                {
                    foreach ($contacts as $contact)
				    {
					    if ($contact->id != $row->id)
					    {
						    ?>
						    if (document.getElementById('delete_<?php echo $contact->id; ?>').checked)
						    {
							    delete_count++;
						    }
						    <?php
					    }
				    }
                }
                ?>
				if (delete_count > 0)
				{
					if (confirm('<?php echo NBILL_CLIENT_CONTACT_DELETE_SURE; ?>'.replace('%s', delete_count)))
					{
						delete_count = 0;
					}
				}

				if (delete_count == 0)
				{
					//Submit all the contacts first...
					var ifr_doc;
					<?php
                    if ($contacts && count($contacts) > 0)
                    {
					    foreach ($contacts as $contact)
					    {
						    //Do validation here first, as any validation errors on the iframe will not be enforceable
						    ?>
						    ifr_doc = get_iframe_doc(document.getElementById('ifr_contact_<?php echo $contact->id; ?>'));
						    if (ifr_doc.adminForm.user_id.options != null && ifr_doc.adminForm.user_id.options[ifr_doc.adminForm.user_id.selectedIndex].value == '-2')
						    {
							    if (ifr_doc.adminForm.username.value == "" || ifr_doc.adminForm.password.value == "" || ifr_doc.adminForm.email_address.value == "")
                                {
                                    //Select the appropriate contact and cancel the save
							        select_tab_client('contact_<?php echo $contact->id; ?>');
							        alert ('<?php echo NBILL_USERNAME_PASSWORD_EMAIL_REQUIRED; ?>');
                                    return;
							    }
                                if (ifr_doc.adminForm.username.value.match(/[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+]/i) != null)
                                {
                                    //Select the appropriate contact and cancel the save
                                    select_tab_client('contact_<?php echo $contact->id; ?>');
                                    alert ('<?php echo NBILL_CLIENT_USERNAME_ALPHANUM; ?>');
                                    return;
                                }
						    }
						    ifr_doc.adminForm.task.value='apply';
						    ifr_doc.adminForm.submit();
						    <?php
					    }
                    }
					?>
					//Check whether anything has been added to the 'new' tab contact record, if so, save that too...
					ifr_doc = get_iframe_doc(document.getElementById('ifr_contact_new'));
                    if (document.getElementById('ifr_contact_new').contentWindow.form_dirty)
                    {
                        //Validate and save new contact
						if (ifr_doc.adminForm.user_id.options != null && ifr_doc.adminForm.user_id.options[ifr_doc.adminForm.user_id.selectedIndex].value == '-2')
						{
							//Select the appropriate contact and cancel the save
							if (ifr_doc.adminForm.username.value == "" || ifr_doc.adminForm.password.value == "" || ifr_doc.adminForm.email_address.value == "")
							{
                                select_tab_client('new_contact');
								alert ('<?php echo NBILL_USERNAME_PASSWORD_EMAIL_REQUIRED; ?>');
                                return;
							}
						}
                        document.getElementById('ifr_contact_new').contentWindow.nbill_submit_task('apply');
                        document.adminForm.new_contact.value = 1;
					}

					//Wait a mo before submitting so that changes to contacts have time to be saved first
					document.adminForm.task.value=task_name;
					setTimeout('document.adminForm.submit()', <?php echo 1500 + (count($contacts) * 500); ?>);
				}
            }
		}
		function get_iframe_doc(ifr)
		{
		      var doc;
		      if(ifr.contentDocument) {return ifr.contentDocument;}
		      else if(ifr.contentWindow) {return ifr.contentWindow.document;}
		      else if(ifr.document){return ifr.document;}
		      else return null;
		}
		function show_contacts(contact_list)
		{
			document.getElementById('contact_list').innerHTML = contact_list;
		}
		function contact_tab_click(contact_id)
		{
			iframe_elem = document.getElementById('ifr_contact_' + contact_id);
			if (iframe_elem != null)
			{
				if (iframe_elem.contentWindow)
				{
					if (iframe_elem.contentWindow.body_height)
					{
						iframe_elem.contentWindow.resize_nbill_iframe();
					}
				}
				else
				{
					if (iframe_elem.body_height)
					{
						iframe_elem.resize_nbill_iframe();
					}
				}
			}
		}
        function update_primary_contact(elem_id, elem_value)
        {
            ifr_doc = get_iframe_doc(document.getElementById('ifr_contact_<?php echo $row->primary_contact_id; ?>'));
            if (ifr_doc && ifr_doc.getElementById(elem_id))
            {
                ifr_doc.getElementById(elem_id).value=elem_value;
                switch (ifr_doc.getElementById(elem_id).type)
                {
                    case 'radio':
                    case 'checkbox':
                        if (ifr_doc.getElementById(elem_id).checked != document.getElementById(elem_id).checked) {
                            ifr_doc.getElementById(elem_id).checked = document.getElementById(elem_id).checked;
                            if (ifr_doc.getElementById(elem_id).click) {
                                ifr_doc.getElementById(elem_id).click();
                            } else if (ifr_doc.getElementById(elem_id).onClick) {
                                ifr_doc.getElementById(elem_id).onClick();
                            }
                        }
                        break;
                }
            }
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
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, nbf_common::get_param($_REQUEST, 'action')); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php
				$client_name = $row->company_name;
				echo $client_id ? constant("NBILL_EDIT_CLIENT$potential_suffix") . " '$client_name'" : constant("NBILL_NEW_CLIENT$potential_suffix");
                if ($row->id)
                {
                    if (strtolower(nbf_version::$suffix) != 'lite') {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=quotes&client_id=$row->id&show_all=1&client_search=" . urlencode($client_name) . "\" title=\"" . NBILL_CLIENT_VIEW_QUOTES . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/quotes.gif\" alt=\"" . NBILL_CLIENT_VIEW_QUOTES . "\" /></a>";
                        echo "&nbsp;<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=orders&client_id=$row->id&show_all=1&client_search=" . urlencode($client_name) . "\" title=\"" . NBILL_CLIENT_VIEW_ORDERS . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/orders.gif\" alt=\"" . NBILL_CLIENT_VIEW_ORDERS . "\" /></a>";
                    }
                    echo "&nbsp;<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=invoices&client_id=$row->id&show_all=1&client_search=" . urlencode($client_name) . "\" title=\"" . NBILL_CLIENT_VIEW_INVOICES . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/invoices.gif\" alt=\"" . NBILL_CLIENT_VIEW_INVOICES . "\" /></a>";
                }
                ?>
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
        <input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $client_id;?>" />
		<input type="hidden" name="is_client" value="<?php echo !$potential_suffix ? "1" : ""; ?>" />
		<input type="hidden" name="new_contact" value="" />
        <input type="hidden" name="last_contact_id" id="last_contact_id" value="<?php echo $last_contact_id; ?>" />
		<?php nbf_html::add_filters();

		$nbf_tab_client = new nbf_tab_group();
		$nbf_tab_client->start_tab_group("client", count($contacts) > 0);
        $nbf_tab_client->add_tab_title("client", NBILL_CLIENT_TAB_CLIENT);
        if ($contacts && count($contacts) > 0)
        {
            foreach ($contacts as $contact)
            {
        	    $nbf_tab_client->add_tab_title("contact_" . $contact->id, $contact->name, "", "contact_tab_click(" . $contact->id . ");");
            }
		}
		$nbf_tab_client->add_tab_title("assign", NBILL_CLIENT_ASSIGN_CONTACT);
		$nbf_tab_client->add_tab_title("new_contact", NBILL_CLIENT_NEW_CONTACT);

		ob_start();
        ?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-clients">
		    <tr>
			    <th colspan="2"><?php echo NBILL_CLIENT_DETAILS; ?></th>
		    </tr>

		    <tr id="nbill-admin-tr-company-name">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_COMPANY_NAME; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="company_name" id="company_name" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST, 'company_name', '', true) : str_replace("\"", "&quot;", $row->company_name); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_COMPANY_NAME, "company_name_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-primary">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CLIENT_PRIMARY; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    $contact_list = array();
                    if ($contacts && count($contacts) > 0)
                    {
				        foreach ($contacts as $contact)
				        {
					        $contact_list[] = nbf_html::list_option($contact->id, $contact->id . " - " . (nbf_common::nb_strlen($contact->name) > 0 ? $contact->name : NBILL_CONTACT_NAME_UNKNOWN));
				        }
                    }
				    echo nbf_html::select_list($contact_list, "primary_contact_id", "class=\"inputbox\" id=\"primary_contact_id\"", $use_posted_values ? nbf_common::get_param($_POST, 'primary_contact_id', '', true) : $row->primary_contact_id);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_CLIENT_PRIMARY, "primary_contact_id_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-add-name">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CLIENT_ADD_NAME_TO_INVOICE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php echo nbf_html::yes_or_no_options("add_name_to_invoice", "", $use_posted_values ? nbf_common::get_param($_POST, 'add_name_to_invoice', '', true) : $row->add_name_to_invoice); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_CLIENT_ADD_NAME_TO_INVOICE, "add_name_to_invoice_help"); ?>
			    </td>
		    </tr>

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
                    <input type="text" name="address_1" id="address_1" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'address_1', '', true) : @$row->address_1); ?>" class="inputbox" onchange="form_dirty=true;<?php if ($sync_primary) {?>update_primary_contact(this.id, this.value);<?php } ?>" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_ADDRESS, "address_1_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-address-2">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_ADDRESS_2; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="address_2" id="address_2" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'address_2', '', true) : @$row->address_2); ?>" class="inputbox" onchange="form_dirty=true;<?php if ($sync_primary) {?>update_primary_contact(this.id, this.value);<?php } ?>" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-address-3">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_ADDRESS_3; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="address_3" id="address_3" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'address_3', '', true) : @$row->address_3); ?>" class="inputbox" onchange="form_dirty=true;<?php if ($sync_primary) {?>update_primary_contact(this.id, this.value);<?php } ?>" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-town">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TOWN; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="town" id="town" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'town', '', true) : @$row->town); ?>" class="inputbox" onchange="form_dirty=true;<?php if ($sync_primary) {?>update_primary_contact(this.id, this.value);<?php } ?>" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-state">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_STATE; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="state" id="state" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'state', '', true) : @$row->state); ?>" class="inputbox" onchange="form_dirty=true;<?php if ($sync_primary) {?>update_primary_contact(this.id, this.value);<?php } ?>" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-postcode">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_POSTCODE; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="postcode" id="postcode" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'postcode', '', true) : @$row->postcode); ?>" class="inputbox" onchange="form_dirty=true;<?php if ($sync_primary) {?>update_primary_contact(this.id, this.value);<?php } ?>" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-country">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_CLIENT_COUNTRY; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                        $country = array();
                        $selected_country = "";
                        foreach ($country_codes as $country_code)
                        {
                            $country[] = nbf_html::list_option($country_code['code'], nbf_common::nb_ucwords(nbf_common::nb_strtolower($country_code['description'])));
                        }
                        if (!$client_id)
                        {
                            //For new clients, default to default vendor's country
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
                        echo nbf_html::select_list($country, "country", 'id="country" class="inputbox" onchange="form_dirty=true;' . ($sync_primary ? 'update_primary_contact(this.id, this.value);' : '') . '"', $selected_country);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_CLIENT_COUNTRY, "country_help"); ?>
                </td>
            </tr>
            </table>
            <?php
            $tab_address->add_tab_content("billing", ob_get_clean());
            ob_start();
            $disabled_attr = @$row->shipping_address->id ? '' : ' disabled="disabled"';
            ?>
            <label><input type="checkbox" name="same_as_billing" id="same_as_billing" onclick="shipping_same_as_billing(this.checked);<?php if ($sync_primary) {echo 'update_primary_contact(this.id, this.value);';} ?>"<?php if (!@$row->shipping_address->id) {echo ' checked="checked"';} ?> /><?php echo NBILL_ADDRESS_SAME_AS_BILLING; ?></label>
            <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-contacts-shipping-address">
            <tr id="nbill-admin-tr-shipping-address-1">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_ADDRESS_1; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text"<?php echo $disabled_attr; ?> name="shipping_address_1" id="shipping_address_1" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'shipping_address_1', '', true) : @$row->shipping_address->line_1); ?>" class="inputbox" onchange="form_dirty=true;<?php if ($sync_primary) {?>update_primary_contact(this.id, this.value);<?php } ?>" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_SHIPPING_ADDRESS_ID, "shipping_address_1_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-shipping-address-2">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_ADDRESS_2; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text"<?php echo $disabled_attr; ?> name="shipping_address_2" id="shipping_address_2" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'shipping_address_2', '', true) : @$row->shipping_address->line_2); ?>" class="inputbox" onchange="form_dirty=true;<?php if ($sync_primary) {?>update_primary_contact(this.id, this.value);<?php } ?>" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-shipping-address-3">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_ADDRESS_3; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text"<?php echo $disabled_attr; ?> name="shipping_address_3" id="shipping_address_3" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'shipping_address_3', '', true) : @$row->shipping_address->line_3); ?>" class="inputbox" onchange="form_dirty=true;<?php if ($sync_primary) {?>update_primary_contact(this.id, this.value);<?php } ?>" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-shipping-town">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_TOWN; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text"<?php echo $disabled_attr; ?> name="shipping_town" id="shipping_town" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'shipping_town', '', true) : @$row->shipping_address->town); ?>" class="inputbox" onchange="form_dirty=true;<?php if ($sync_primary) {?>update_primary_contact(this.id, this.value);<?php } ?>" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-shipping-state">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_STATE; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text"<?php echo $disabled_attr; ?> name="shipping_state" id="shipping_state" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'shipping_state', '', true) : @$row->shipping_address->state); ?>" class="inputbox" onchange="form_dirty=true;<?php if ($sync_primary) {?>update_primary_contact(this.id, this.value);<?php } ?>" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-shipping-postcode">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_POSTCODE; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text"<?php echo $disabled_attr; ?> name="shipping_postcode" id="shipping_postcode" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'shipping_postcode', '', true) : @$row->shipping_address->postcode); ?>" class="inputbox" onchange="form_dirty=true;<?php if ($sync_primary) {?>update_primary_contact(this.id, this.value);<?php } ?>" />
                </td>
            </tr>
            <tr id="nbill-admin-tr-shipping-country">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_CLIENT_COUNTRY; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    $selected_country = "";
                    if (!$client_id) {
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
                    echo nbf_html::select_list($country, "shipping_country", 'id="shipping_country"' . $disabled_attr . ' class="inputbox" onchange="form_dirty=true;' . ($sync_primary ? 'update_primary_contact(this.id, this.value);' : '') . '"', $selected_country);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_CLIENT_COUNTRY, "shipping_country_help"); ?>
                </td>
            </tr>
            </table>
            <?php
            $tab_address->add_tab_content("shipping", ob_get_clean());
            $tab_address->end_tab_group();
            ?>
            </td></tr>
            <?php
            //Offer choice of language if more than one available
            if (count($languages) > 1)
            { ?>
                <tr id="nbill-admin-tr-language">
                    <td class="nbill-setting-caption">
                        <?php echo NBILL_CLIENT_LANGUAGE; ?>
                    </td>
                    <td class="nbill-setting-value">
                        <?php
                        $lang_codes = array();
                        foreach ($languages as $key=>$value)
                        {
                            //If this is the default front-end language, the value should be blank (so if the default changes, the client's language is not stuck on the old value)
                            $lang_codes[] = nbf_html::list_option($key, $value);
                        }
                        if (!$client_id)
                        {
                            //For new clients, default to current language
                            $selected_language = nbf_cms::$interop->language;
                        }
                        else
                        {
                            $selected_language = $row->default_language;
                        }
                        if (!$selected_language)
                        {
                            $selected_language = nbf_cms::$interop->get_frontend_language();
                        }
                        echo nbf_html::select_list($lang_codes, "default_language", 'id="default_language" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST, 'default_language', '', true) : $selected_language);
                        ?>
                        <?php nbf_html::show_static_help(NBILL_INSTR_CLIENT_LANGUAGE, "default_language_help"); ?>
                    </td>
                </tr>
            <?php } ?>
		    <tr id="nbill-admin-tr-reference">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CLIENT_REFERENCE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="reference" id="reference" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'reference', '', true) : $row->reference); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_CLIENT_REFERENCE, "reference_help"); ?>
			    </td>
		    </tr>
            <!-- Custom Fields Placeholder -->
		    <tr id="nbill-admin-tr-website">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_WEBSITE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="website_url" id="website_url" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'website_url', '', true) : $row->website_url); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_WEBSITE, "website_url_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-tax_zone">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CLIENT_TAX_ZONE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="tax_zone" id="tax_zone" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'tax_zone', '', true) : $row->tax_zone); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_CLIENT_TAX_ZONE, "tax_zone_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-tax-exemption-code">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_TAX_EXEMPTION_CODE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="tax_exemption_code" id="tax_exemption_code" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'tax_exemption_code', '', true) : $row->tax_exemption_code); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_TAX_EXEMPTION_CODE, "tax_exemption_code_help"); ?>
			    </td>
		    </tr>
            <tr id="nbill-admin-tr-notes">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_NOTES; ?>
                </td>
                <td class="nbill-setting-value">
                    <textarea name="notes" id="notes"><?php echo $use_posted_values ? nbf_common::get_param($_POST, 'notes', '', true) : $row->notes; ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_INSTR_NOTES, "notes_help"); ?>
                </td>
            </tr>
            <?php
             ?>
		    </table>
        </div>

        <?php
        if ($custom_fields && count($custom_fields) > 0)
        {
            ?>
            <br />
            <div class="rounded-table">
                <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
                <tr>
                    <th colspan="2"><?php echo NBILL_CLIENT_CUSTOM_FIELDS; ?></th>
                </tr>
                <?php
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control.base.php");
                foreach ($custom_fields as $field)
                { ?>
                    <tr class="nbill-admin-tr-custom-field">
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
                            if (strpos($field->attributes, 'multiple="multiple"') !== false)
                            {
                                $control->attributes = 'multiple="multiple"'; //This is the only attribute we will allow as others could render the field disabled or invisible or mess up the layout
                            }
                            $control->required = $field->required;
                            if ($field->checkbox_text != null)
                            {
                                $control->checkbox_text = $field->checkbox_text;
                            }
                            $control->field_options = $field_options[$field->id];
                            $control->onclick_admin = "";
                            if ($sync_primary)
                            {
                                $control->onchange_admin = "update_primary_contact(this.id, this.value);";
                            }
                            else
                            {
                                $control->onchange_admin = "";
                            }
                            $control->onkeydown_admin = "";
                            $control->render_control();
                            nbf_html::show_static_help($field->help_text ? $field->help_text : "&nbsp;", $field->name . "_help");
                            ?>
                        </td>
                    </tr>
                <?php }
                ?></table>
            </div>
            <?php
        } ?>

        <?php
        if ($ip_info && count($ip_info) > 0)
        {
            ?>
            <br />
            <div class="rounded-table">
                <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
                <tr id="nbill-admin-tr-ip-info">
                    <th colspan="3"><?php echo NBILL_CLIENT_IP_INFO; ?></th>
                </tr>
                <tr id="nbill-admin-tr-ip-intro">
                    <td colspan="3"><?php echo NBILL_CLIENT_IP_INFO_INTRO; ?></td>
                </tr>
                <tr id="nbill-admin-tr-ip-data">
                    <td><strong><?php echo NBILL_CLIENT_IP_DATE; ?></strong></td>
                    <td><strong><?php echo NBILL_CLIENT_IP_ADDRESS; ?></strong></td>
                    <td><strong><?php echo NBILL_CLIENT_IP_COUNTRY; ?></strong></td>
                </tr>
                <?php
                foreach ($ip_info as $ip_entry)
                { ?>
                    <tr class="nbill-admin-tr-ip-entry">
                        <td class="nbill-setting-value">
                            <?php echo date(nbf_common::get_date_format(), $ip_entry->date); ?>
                        </td>
                        <td class="nbill-setting-value">
                            <?php echo $ip_entry->ip_address; ?>
                        </td>
                        <td class="nbill-setting-value">
                            <?php echo $ip_entry->country_code; ?>
                        </td>
                    </tr>
                    <?php
                } ?>
                </table>
            </div>
            <?php
        } ?>

        <?php
        
        ?>

        <?php
        //Add a tab for each contact
		$nbf_tab_client->add_tab_content("client", ob_get_clean());
		if ($contacts && count($contacts) > 0)
        {
            foreach ($contacts as $contact)
		    {
			    ob_start(); ?>
			    <div align="right" style="margin-top:3px;margin-bottom:6px;padding:3px;border:solid 1px #cccccc;background-color:#ffe6e6;">
				    <input type="checkbox" class="nbill_form_input" name="remove_<?php echo $contact->id; ?>" id="remove_<?php echo $contact->id; ?>" onclick="document.adminForm.delete_<?php echo $contact->id; ?>.checked=false;" /><label class="nbill_form_label" for="remove_<?php echo $contact->id; ?>"><?php echo NBILL_CLIENT_REMOVE_CONTACT; ?></label>
				    <input type="checkbox" class="nbill_form_input" name="delete_<?php echo $contact->id; ?>" id="delete_<?php echo $contact->id; ?>" onclick="document.adminForm.remove_<?php echo $contact->id; ?>.checked=false;" /><label class="nbill_form_label" for="delete_<?php echo $contact->id; ?>"><?php echo NBILL_CLIENT_DELETE_CONTACT; ?></label>
			    </div>
			    <iframe frameborder="0" style="width:100%;height:<?php echo $contact_custom_fields ? "10" : "8"; ?>50px;min-height:650px;" name="ifr_contact_<?php echo $contact->id; ?>" id="ifr_contact_<?php echo $contact->id; ?>" src="<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&action=contacts&task=edit&cid=<?php echo $contact->id; ?>&hide_billing_menu=1&hide_toolbar=1&nbill_entity_iframe=<?php echo $row->id; ?>#<?php echo uniqid(); ?>">
				    <?php echo sprintf(NBILL_CLIENT_NO_IFRAMES, nbf_cms::$interop->admin_page_prefix . "&action=contacts&task=edit&nbill_popup=1&cid=" . $contact->id); ?>
			    </iframe>
			    <hr />
			    <h3><?php echo NBILL_CLIENT_CONTACT_PERMISSIONS; ?></h3>
			    <table cellpadding="2" cellspacing="0" border="0" class="adminform">
				    <tr id="nbill-admin-tr-update-profile">
					    <td class="nbill-setting-caption">
						    <?php echo NBILL_CLIENT_UPDATE_PROFILE; ?>
					    </td>
					    <td class="nbill-setting-value">
						    <?php echo nbf_html::yes_or_no_options("cp_" . $contact->id . "_allow_update", "", $contact->allow_update); ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_CLIENT_UPDATE_PROFILE, "cp_" . $contact->id . "_allow_update_help"); ?>
					    </td>
				    </tr>
                    <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
				    <tr id="nbill-admin-tr-access-orders">
					    <td class="nbill-setting-caption">
						    <?php echo NBILL_CLIENT_ACCESS_ORDERS; ?>
					    </td>
					    <td class="nbill-setting-value">
						    <?php echo nbf_html::yes_or_no_options("cp_" . $contact->id . "_allow_orders", "", $contact->allow_orders); ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_CLIENT_ACCESS_ORDERS, "cp_" . $contact->id . "_allow_orders_help"); ?>
					    </td>
				    </tr>
                    <?php } ?>
				    <tr id="nbill-admin-tr-access-invoices">
					    <td class="nbill-setting-caption">
						    <?php echo NBILL_CLIENT_ACCESS_INVOICES; ?>
					    </td>
					    <td class="nbill-setting-value">
						    <?php echo nbf_html::yes_or_no_options("cp_" . $contact->id . "_allow_invoices", "", $contact->allow_invoices); ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_CLIENT_ACCESS_INVOICES, "cp_" . $contact->id . "_allow_invoices_help"); ?>
					    </td>
				    </tr>
                    <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
				    <tr id="nbill-admin-tr-access-quotes">
					    <td class="nbill-setting-caption">
						    <?php echo NBILL_CLIENT_ACCESS_QUOTES; ?>
					    </td>
					    <td class="nbill-setting-value">
						    <?php echo nbf_html::yes_or_no_options("cp_" . $contact->id . "_allow_quotes", "", $contact->allow_quotes); ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_CLIENT_ACCESS_QUOTES, "cp_" . $contact->id . "_allow_quotes_help"); ?>
					    </td>
				    </tr>
                    <?php } ?>
				    <tr id="nbill-admin-tr-email-invoices">
					    <td class="nbill-setting-caption">
						    <?php echo NBILL_EMAIL_INVOICE_OPTIONS; ?>
					    </td>
					    <td class="nbill-setting-value">
						    <?php
						    $email_options = array();
						    if ($row->id)
						    {
							    $selected_email_opt = $contact->email_invoice_option;
						    }
						    else
						    {
							    $selected_email_opt = $default_email_invoice_option;
						    }
						    foreach ($email_options_xref as $option_code)
						    {
							    $email_options[] = nbf_html::list_option($option_code->code, $option_code->description . "<br />");
						    }
						    echo nbf_html::radio_list($email_options, "cp_" . $contact->id . "_email_invoice_option", $selected_email_opt); ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_EMAIL_INVOICE_OPTIONS_CLIENT, "cp_" . $contact->id . "_email_invoice_option_help"); ?>
					    </td>
				    </tr>
                    <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
				    <tr id="nbill-admin-tr-reminders">
					    <td class="nbill-setting-caption">
						    <?php echo NBILL_CLIENT_REMINDER_EMAILS; ?>
					    </td>
					    <td class="nbill-setting-value">
						    <?php echo nbf_html::yes_or_no_options("cp_" . $contact->id . "_reminder_emails", "", $contact->reminder_emails); ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_CLIENT_REMINDER_EMAILS, "cp_" . $contact->id . "_reminder_emails_help"); ?>
					    </td>
				    </tr>
                    <tr id="nbill-admin-tr-allow-opt-in">
                        <td class="nbill-setting-caption">
                            <?php echo NBILL_CLIENT_ALLOW_OPT_IN; ?>
                        </td>
                        <td class="nbill-setting-value">
                            <?php echo nbf_html::yes_or_no_options("cp_" . $contact->id . "_allow_reminder_opt_in", "", $contact->allow_reminder_opt_in); ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_CLIENT_ALLOW_OPT_IN, "cp_" . $contact->id . "_allow_reminder_opt_in_help"); ?>
                        </td>
                    </tr>
                    <?php } ?>
			    </table>

			    <?php
			    $nbf_tab_client->add_tab_content("contact_" . $contact->id, ob_get_clean());
		    }
        }

		//Assign contact tab
		ob_start();
		?>
		<p align="left"><?php echo NBILL_CLIENT_CONTACT_FILTER; ?></p>
		<input type="text" name="contact_filter" id="contact_filter" value="" class="inputbox" />&nbsp;<input type="button" class="button btn" name="filter_contacts" id="filter_contacts" value="<?php echo NBILL_CLIENT_CONTACT_FILTER_GO; ?>" onclick="submit_ajax_request('get_contacts', 'contact_name=' + document.getElementById('contact_filter').value, show_contacts, true, null, '320');" />
		<div style="margin-top: 5px;" id="contact_list"></div>
		<?php
		$nbf_tab_client->add_tab_content("assign", ob_get_clean());

		//New contact tab
		ob_start();
		?>
		<iframe frameborder="0" style="width:100%;height:800px;" name="ifr_contact_new" id="ifr_contact_new" src="<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&action=contacts&task=new&hide_billing_menu=1&hide_toolbar=1&nbill_entity_iframe=<?php echo $row->id; ?>#<?php echo uniqid(); ?>">
			<?php echo sprintf(NBILL_CLIENT_NO_IFRAMES, nbf_cms::$interop->admin_page_prefix . "&action=contacts&task=new"); ?>
		</iframe>
		<?php
		$nbf_tab_client->add_tab_content("new_contact", ob_get_clean());

		$nbf_tab_client->end_tab_group();
		?>
		</form>
		<?php
	}
}