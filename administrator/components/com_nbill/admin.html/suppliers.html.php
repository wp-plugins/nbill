<?php
/**
* HTML output for suppliers feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillSuppliers
{
	public static function showSuppliers($rows, $pagination, $vendors, $attachments = array())
	{
        nbf_common::load_language("core.profile_fields");
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "suppliers"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_SUPPLIERS_TITLE; ?>
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
        <input type="hidden" name="action" value="suppliers" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
        <input type="hidden" name="attachment_id" value="" />

		<?php
		//Display filter for supplier name
		if (count($vendors) < 2)
		{
			echo "<p align=\"left\">";
		}
		$supplier_search = nbf_common::get_param($_REQUEST,'supplier_search', '', true);
        $supplier_user_search = nbf_common::get_param($_REQUEST,'supplier_user_search', '', true);
        $supplier_email_search = nbf_common::get_param($_REQUEST,'supplier_email_search', '', true);
		echo NBILL_SUPPLIER . " <input type=\"text\" name=\"supplier_search\" value=\"" . $supplier_search . "\" />&nbsp; ";
        echo NBILL_SUPPLIER_USER . " <input type=\"text\" name=\"supplier_user_search\" value=\"" . $supplier_user_search . "\" />&nbsp; ";
        echo NBILL_EMAIL_ADDRESS . " <input type=\"text\" name=\"supplier_email_search\" value=\"" . $supplier_email_search . "\" />&nbsp; ";
		echo "&nbsp;&nbsp;<input type=\"submit\" name=\"dosearch\" value=\"" . NBILL_GO . "\" />";
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
				    <?php echo NBILL_SUPPLIER_NAME; ?>
			    </th>
                <th class="title responsive-cell priority">
				    <?php echo NBILL_SUPPLIER_USER; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_EMAIL_ADDRESS; ?>
			    </th>
			    <th class="title responsive-cell optional">
				    <?php echo NBILL_WEBSITE; ?>
			    </th>
			    <th class="title responsive-cell wide-only">
				    <?php echo NBILL_TELEPHONE; ?>
			    </th>
		    </tr>
		    <?php
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];
				    $link = nbf_cms::$interop->admin_page_prefix . "&action=suppliers&task=edit&cid=$row->id&supplier_search=$supplier_search";
				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\">$checked</td>";
				    echo "<td class=\"list-value\">";
                    $supplier_name = $row->company_name;
                    if (nbf_common::nb_strlen($row->name) > 0)
                    {
                        if (nbf_common::nb_strlen($row->company_name) > 0)
                        {
                            $supplier_name .= " (";
                        }
                        $supplier_name .= $row->name;
                        if (nbf_common::nb_strlen($row->company_name) > 0)
                        {
                            $supplier_name .= ")";
                        }
                    }
                    echo "<div style=\"float:left\"><a href=\"$link\" title=\"" . NBILL_EDIT_SUPPLIER . "\">";
                    echo $supplier_name;
				    echo "</a></div>";
                    if (file_exists(nbf_cms::$interop->nbill_admin_base_path . '/admin.proc/supporting_docs.php')) {
                    ?>
                    <div style="float:right"><a href="#" onclick="<?php if ($row->attachment_count){ ?>var att=document.getElementById('attachments_<?php echo $row->id; ?>');if(att.style.display=='none'){att.style.display='';}else{att.style.display='none';}<?php }else{ ?>window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&use_stylesheet=1&show_toolbar=1&attach_to_type=SU&attach_to_id=<?php echo $row->id; ?>','','scrollbars=1,width=790,height=500');<?php } ?>return false;"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/supporting_docs.gif" alt="<?php echo NBILL_ATTACHMENTS; ?>" style="vertical-align:middle;" /><?php if ($row->attachment_count) {echo " (" . $row->attachment_count . ")";} ?></a></div>
                    <div id="attachments_<?php echo $row->id; ?>" style="display:none;text-align:right;clear:both;">
                        <table cellpadding="3" cellspacing="0" border="0" style="margin-left:auto;margin-right:0px;">
                        <?php
                        foreach ($attachments as $attachment)
                        {
                            if ($attachment->associated_doc_id == $row->id)
                            {
                                ?>
                                <tr>
                                <td>
                                    <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=supporting_docs&task=download&file=<?php echo base64_encode($attachment->id); ?>"><img style="vertical-align:middle" border="0" alt="" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file.png" />&nbsp;<?php echo $attachment->file_name; ?></a>
                                </td>
                                <td>
                                    <input type="button" class="button btn" value="<?php echo NBILL_DETACH; ?>" onclick="if(confirm('<?php echo NBILL_DETACH_SURE; ?>')){document.adminForm.attachment_id.value='<?php echo $attachment->id; ?>';document.adminForm.task.value='detach_file';document.adminForm.submit();}" />
                                </td>
                                <td>
                                    <input type="button" class="button btn" value="<?php echo NBILL_DELETE; ?>" onclick="if(confirm('<?php echo sprintf(NBILL_DELETE_FILE_SURE, $attachment->file_name); ?>')){document.adminForm.attachment_id.value='<?php echo $attachment->id; ?>';document.adminForm.task.value='delete_file';document.adminForm.submit();}" />
                                </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <tr><td colspan="3">
                        <a href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&use_stylesheet=1&show_toolbar=1&attach_to_type=SU&attach_to_id=<?php echo $row->id; ?>','','scrollbars=1,width=790,height=500');return false;"><img style="vertical-align:middle" border="0" alt="" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/supporting_docs.gif" />&nbsp;<?php echo NBILL_NEW_ATTACHMENT; ?></a>
                        </td></tr>
                        </table>
                    </div>
                    <?php
                    }
                    echo "</td>";
                    /*echo "<td align=\"center\" style=\"text-align:center;white-space:nowrap;\">";
                    echo "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=purchase_orders&task=new&listed_supplier_id=$row->id&disable_supplier_list=1\" title=\"". NBILL_SUPPLIER_NEW_PURCHASE_ORDER . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/new_purchase_order.gif\" alt=\"" . NBILL_SUPPLIER_NEW_PURCHASE_ORDER . "\" /></a>";
                    echo "&nbsp;<img alt=\"\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/separator.gif\" />";
                    echo "&nbsp;<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=orders&supplier_id=$row->id&show_all=1&supplier_search=" . urlencode($supplier_name) . "\" title=\"" . NBILL_SUPPLIER_VIEW_PURCHASE_ORDERS . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/purchase_order.gif\" alt=\"" . NBILL_SUPPLIER_VIEW_PURCHASE_ORDERS . "\" /></a>";
                    echo "</td>";*/
                    echo "<td class=\"list-value responsive-cell priority\">$row->username";
				    echo "</td>";
				    echo "<td class=\"list-value word-breakable\"><a href=\"mailto:" . $row->email_address . "\">" . $row->email_address . "</a></td>";
				    if (nbf_common::nb_strlen($row->website_url) > 0 && substr($row->website_url, 0, 7) != "http://")
				    {
					    $url = "http://" . $row->website_url;
				    }
				    else
				    {
					    $url = $row->website_url;
				    }
				    echo "<td class=\"list-value responsive-cell optional word-breakable\"><a href=\"" . $url . "\" target=\"_blank\">" . $row->website_url . "</a></td>";
				    echo "<td class=\"list-value responsive-cell wide-only\">" . $row->telephone . "</td>";
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
	* Edit a supplier (or create a new one)
	*/
	public static function editSupplier($supplier_id, $row, $languages, $country_codes, $currency_codes, $vendors, $ledger, $email_options_xref, $contacts, $use_posted_values = false, $attachments = array())
	{
        nbf_common::load_language("core.profile_fields");
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

			//If a contact will be deleted, check that they really want to continue...
			var delete_count = 0;
			<?php
            if ($contacts)
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
            } ?>
			if (delete_count > 0)
			{
				if (confirm('<?php echo NBILL_SUPPLIER_CONTACT_DELETE_SURE; ?>'.replace('%s', delete_count)))
				{
					delete_count = 0;
				}
			}

			if (delete_count == 0)
			{
				//Submit all the contacts first...
				var ifr_doc;
				<?php
                if ($contacts)
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
						        select_tab_supplier('contact_<?php echo $contact->id; ?>');
							    alert ('<?php echo NBILL_USERNAME_PASSWORD_REQUIRED; ?>');
                                return;
                            }
                            if (ifr_doc.adminForm.username.value.match(/[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+]/i) != null)
                            {
                                //Select the appropriate contact and cancel the save
                                select_tab_supplier('contact_<?php echo $contact->id; ?>');
                                alert ('<?php echo NBILL_SUPPLIER_USERNAME_ALPHANUM; ?>');
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
                            select_tab_supplier('new_contact');
                            alert ('<?php echo NBILL_USERNAME_PASSWORD_REQUIRED; ?>');
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
		</script>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "suppliers"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php
				$supplier_name = $row->company_name;
				echo $supplier_id ? NBILL_EDIT_SUPPLIER . " '$supplier_name'" : NBILL_NEW_SUPPLIER; ?>
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
        <input type="hidden" name="action" value="suppliers" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $supplier_id;?>" />
		<input type="hidden" name="is_supplier" value="1" />
		<input type="hidden" name="new_contact" value="" />
		<?php nbf_html::add_filters(); ?>

		<?php
		$nbf_tab_supplier = new nbf_tab_group();
		$nbf_tab_supplier->start_tab_group("supplier", count($contacts) > 0);
        $nbf_tab_supplier->add_tab_title("supplier", NBILL_SUPPLIER_TAB_SUPPLIER);
        if ($contacts)
        {
            foreach ($contacts as $contact)
            {
        	    $nbf_tab_supplier->add_tab_title("contact_" . $contact->id, $contact->name ? $contact->name : NBILL_CONTACT_NAME_UNKNOWN, "", "contact_tab_click(" . $contact->id . ");");
		    }
        }
		$nbf_tab_supplier->add_tab_title("assign", NBILL_SUPPLIER_ASSIGN_CONTACT);
		$nbf_tab_supplier->add_tab_title("new_contact", NBILL_SUPPLIER_NEW_CONTACT);

		ob_start();
        ?>
        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-suppliers">
		    <tr>
			    <th colspan="2"><?php echo NBILL_SUPPLIER_DETAILS; ?></th>
		    </tr>

		    <tr id="nbill-admin-tr-company-name">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_COMPANY_NAME; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="company_name" id="company_name" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'company_name', '', true) : $row->company_name); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_SUPPLIER_COMPANY_NAME, "company_name_help"); ?>
			    </td>
		    </tr>
            <!-- Custom Fields Placeholder -->
		    <tr id="nbill-admin-tr-primary">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SUPPLIER_PRIMARY; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
				    $contact_list = array();
                    if ($contacts)
                    {
				        foreach ($contacts as $contact)
				        {
					        $contact_list[] = nbf_html::list_option($contact->id, $contact->id . " - " . (nbf_common::nb_strlen($contact->name) > 0 ? $contact->name : NBILL_CONTACT_NAME_UNKNOWN));
				        }
                    }
				    echo nbf_html::select_list($contact_list, "primary_contact_id", "", $use_posted_values ? nbf_common::get_param($_POST, 'primary_contact_id', '', true) : $row->primary_contact_id);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_SUPPLIER_PRIMARY, "primary_contact_id_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-address-1">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_ADDRESS_1; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="address_1" id="address_1" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'address_1', '', true) : $row->address_1); ?>" class="inputbox" style="width:160px;" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_SUPPLIER_ADDRESS, "address_1_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-address-2">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_ADDRESS_2; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="address_2" id="address_2" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'address_2', '', true) : $row->address_2); ?>" class="inputbox" style="width:160px;" />
                </td>
		    </tr>
		    <tr id="nbill-admin-tr-address-3">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_ADDRESS_3; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="address_3" id="address_3" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'address_3', '', true) : $row->address_3); ?>" class="inputbox" style="width:160px;" />
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-town">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_TOWN; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="town" id="town" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'town', '', true) : $row->town); ?>" class="inputbox" style="width:160px;" />
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-state">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_STATE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="state" id="state" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'state', '', true) : $row->state); ?>" class="inputbox" style="width:160px;" />
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-postcode">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_POSTCODE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="postcode" id="postcode" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'postcode', '', true) : $row->postcode); ?>" class="inputbox" style="width:160px;" />
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-country">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SUPPLIER_COUNTRY; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    $country = array();
					    foreach ($country_codes as $country_code)
					    {
						    $country[] = nbf_html::list_option($country_code['code'], nbf_common::nb_ucwords(nbf_common::nb_strtolower($country_code['description'])));
					    }
					    if (!$supplier_id)
					    {
						    //For new suppliers, default to selected vendor's country
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
							    $selected_country = $vendors[0]->vendor_country;
						    }
					    }
					    else
					    {
						    $selected_country = $row->country;
					    }
					    echo nbf_html::select_list($country, "country", 'id="country" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST, 'country', '', true) : $selected_country);
				    ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_SUPPLIER_COUNTRY, "country_help"); ?>
			    </td>
		    </tr>
            <?php
            //Offer choice of language if more than one available
            if (count($languages) > 1)
            { ?>
                <tr id="nbill-admin-tr-language">
                    <td class="nbill-setting-caption">
                        <?php echo NBILL_SUPPLIER_LANGUAGE; ?>
                    </td>
                    <td class="nbill-setting-value">
                        <?php
                        $lang_codes = array();
                        foreach ($languages as $key=>$value)
                        {
                            //If this is the default front-end language, the value should be blank (so if the default changes, the client's language is not stuck on the old value)
                            $lang_codes[] = nbf_html::list_option($key, $value);
                        }
                        if (!$supplier_id)
                        {
                            //For new clients, default to current language
                            $selected_language = nbf_cms::$interop->language;
                        }
                        else
                        {
                            $selected_language = $row->default_language;
                        }
                        echo nbf_html::select_list($lang_codes, "default_language", 'id="default_language" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST, 'default_language', '', true) : $selected_language);
                        ?>
                        <?php nbf_html::show_static_help(NBILL_INSTR_SUPPLIER_LANGUAGE, "default_language_help"); ?>
                    </td>
                </tr>
            <?php } ?>
		    <tr id="nbill-admin-tr-reference">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SUPPLIER_REFERENCE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="reference" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'reference', '', true) : $row->reference); ?>" class="inputbox" style="width:160px;" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_SUPPLIER_REFERENCE, "reference_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-website">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_WEBSITE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="website_url" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'website_url', '', true) : $row->website_url); ?>" class="inputbox" style="width:160px;" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_SUPPLIER_WEBSITE, "website_url_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-tax-reference">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_SUPPLIER_TAX_REFERENCE; ?>
			    </td class="nbill-setting-value">
			    <td>
				    <input type="text" name="tax_exemption_code" value="<?php echo str_replace("\"", "&quot;", $use_posted_values ? nbf_common::get_param($_POST, 'tax_exemption_code', '', true) : $row->tax_exemption_code); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_SUPPLIER_TAX_REFERENCE, "tax_exemption_code_help"); ?>
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-notes">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_NOTES; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <textarea name="notes" rows="10" cols="35"><?php echo $use_posted_values ? nbf_common::get_param($_POST, 'notes', '', true) : $row->notes; ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_INSTR_NOTES, "notes_help"); ?>
			    </td>
		    </tr>
		    </table>
        </div>

        <?php if (file_exists(nbf_cms::$interop->nbill_admin_base_path . '/admin.proc/supporting_docs.php')&& $row->id)
        { ?>
            <div id="attachments_<?php echo $row->id; ?>" style="clear:both;">
                <input type="hidden" name="attachment_id" id="attachment_id" value="" />
                <input type="hidden" name="use_posted_values" value="" />
                <table cellpadding="3" cellspacing="0" border="0">
                <?php
                foreach ($attachments as $attachment)
                {
                    ?>
                    <tr class="nbill-admin-tr-attachment">
                    <td>
                        <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=supporting_docs&task=download&file=<?php echo base64_encode($attachment->id); ?>"><img style="vertical-align:middle" border="0" alt="" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file.png" />&nbsp;<?php echo $attachment->file_name; ?></a>
                    </td>
                    <td>
                        <input type="button" class="button btn" value="<?php echo NBILL_DETACH; ?>" onclick="if(confirm('<?php echo NBILL_DETACH_SURE; ?>')){document.adminForm.attachment_id.value='<?php echo $attachment->id; ?>';document.adminForm.task.value='detach_file_edit';document.adminForm.submit();}" />
                    </td>
                    <td>
                        <input type="button" class="button btn" value="<?php echo NBILL_DELETE; ?>" onclick="if(confirm('<?php echo sprintf(NBILL_DELETE_FILE_SURE, $attachment->file_name); ?>')){document.adminForm.attachment_id.value='<?php echo $attachment->id; ?>';document.adminForm.task.value='delete_file_edit';document.adminForm.submit();}" />
                    </td>
                    </tr>
                    <?php
                }
                ?>
                <tr><td colspan="3">
                <a href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=supporting_docs&use_stylesheet=1&show_toolbar=1&attach_to_type=SU&attach_to_id=<?php echo $row->id; ?>','','scrollbars=1,width=790,height=500');return false;"><img style="vertical-align:middle" border="0" alt="" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/supporting_docs.gif" />&nbsp;<?php echo NBILL_NEW_ATTACHMENT; ?></a>
                </td></tr>
                </table>
            </div>
        <?php } ?>

		<?php
		//Add a tab for each contact
		$nbf_tab_supplier->add_tab_content("supplier", ob_get_clean());
        if ($contacts)
        {
		    foreach ($contacts as $contact)
		    {
			    ob_start(); ?>
			    <div align="right" style="margin-top:3px;margin-bottom:6px;padding:3px;border:solid 1px #cccccc;background-color:#ffe6e6;">
				    <input type="checkbox" class="nbill_form_input" name="remove_<?php echo $contact->id; ?>" id="remove_<?php echo $contact->id; ?>" onclick="document.adminForm.delete_<?php echo $contact->id; ?>.checked=false;" /><label class="nbill_form_label" for="remove_<?php echo $contact->id; ?>"><?php echo NBILL_SUPPLIER_REMOVE_CONTACT; ?></label>
				    <input type="checkbox" class="nbill_form_input" name="delete_<?php echo $contact->id; ?>" id="delete_<?php echo $contact->id; ?>" onclick="document.adminForm.remove_<?php echo $contact->id; ?>.checked=false;" /><label class="nbill_form_label" for="delete_<?php echo $contact->id; ?>"><?php echo NBILL_SUPPLIER_DELETE_CONTACT; ?></label>
			    </div>
			    <iframe frameborder="0" style="width:100%;height:850px;;min-height:650px;" name="ifr_contact_<?php echo $contact->id; ?>" id="ifr_contact_<?php echo $contact->id; ?>" src="<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&action=contacts&task=edit&cid=<?php echo $contact->id; ?>&hide_billing_menu=1&nbill_entity_iframe=<?php echo $row->id; ?>#<?php echo uniqid(); ?>">
				    <?php echo sprintf(NBILL_SUPPLIER_NO_IFRAMES, nbf_cms::$interop->admin_page_prefix . "&action=contacts&task=edit&cid=" . $contact->id); ?>
			    </iframe>
			    <hr />
			    <h3><?php echo NBILL_SUPPLIER_CONTACT_PERMISSIONS; ?></h3>
			    <table cellpadding="3" cellspacing="0" border="0" class="adminform">
				    <tr id="nbill-admin-tr-update-profile">
					    <td class="nbill-setting-caption">
						    <?php echo NBILL_SUPPLIER_UPDATE_PROFILE; ?>
					    </td>
					    <td class="nbill-setting-value">
						    <?php echo nbf_html::yes_or_no_options("cp_" . $contact->id . "_allow_update", "", $contact->allow_update); ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_SUPPLIER_UPDATE_PROFILE, "cp_" . $contact->id . "_allow_update_help"); ?>
					    </td>
				    </tr>
				    <!--<tr>
					    <td width="20%">
						    <?php echo NBILL_SUPPLIER_ACCESS_PURCHASE_ORDERS; ?>
					    </td>
					    <td>
						    <?php echo nbf_html::yes_or_no_options("cp_" . $contact->id . "_allow_purchase_orders", "", $contact->allow_purchase_orders); ?>
					    </td>
					    <td>
						    <?php echo NBILL_INSTR_SUPPLIER_ACCESS_PURCHASE_ORDERS; ?>
					    </td>
				    </tr>-->
			    </table>

			    <?php
			    $nbf_tab_supplier->add_tab_content("contact_" . $contact->id, ob_get_clean());
		    }
        }

		//Assign contact tab
		ob_start();
		?>
		<p align="left"><?php echo NBILL_SUPPLIER_CONTACT_FILTER; ?></p>
		<input type="text" name="contact_filter" id="contact_filter" value="" style="width:200px;" />&nbsp;<input type="button" name="filter_contacts" id="filter_contacts" value="<?php echo NBILL_SUPPLIER_CONTACT_FILTER_GO; ?>" onclick="submit_ajax_request('get_contacts', 'contact_name=' + document.getElementById('contact_filter').value, show_contacts, true, null, '320');" />
		<div style="margin-top: 5px;" id="contact_list"></div>
		<?php
		$nbf_tab_supplier->add_tab_content("assign", ob_get_clean());

		//New contact tab
		ob_start();
		?>
		<iframe frameborder="0" style="width:100%;height:800px;" name="ifr_contact_new" id="ifr_contact_new" src="<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&action=contacts&task=new&hide_billing_menu=1&nbill_entity_iframe=<?php echo $row->id; ?>#<?php echo uniqid(); ?>">
			<?php echo sprintf(NBILL_SUPPLIER_NO_IFRAMES, nbf_cms::$interop->admin_page_prefix . "&action=contacts&task=new"); ?>
		</iframe>
		<?php
		$nbf_tab_supplier->add_tab_content("new_contact", ob_get_clean());

		$nbf_tab_supplier->end_tab_group();
		?>
		</form>
		<?php
	}
}