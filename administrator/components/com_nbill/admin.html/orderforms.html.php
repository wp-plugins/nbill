<?php
/**
* HTML output for order form editor
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillOrderforms
{
	public static function showOrderForms($rows, $pagination, $vendors, $menus, $form_def, $duplicates = array(), $unmapped = array())
	{
        $lang_suffix = $form_def['lang_suffix'];
        $vendor_col = false;

        if (isset($form_def['list_toolbar']))
        {
            echo $form_def['list_toolbar'];
        }
		?>
		<script type="text/javascript">
		<?php echo get_prompt_js(); ?>
		var g_form_id;
		function create_menu(form_id)
		{
			g_form_id = form_id;
			IEprompt('<?php echo NBILL_CREATE_MENU_NAME; ?>', '');
		}
		function promptCallback(menu_text)
		{
			if (menu_text != null && menu_text.length > 0)
			{
                nbill_submit_task('create_menu_' + g_form_id + '_' + menu_text);
			}
		}
        function nbill_submit_task(task_name)
        {
            var form = document.adminForm;
            form.task.value=task_name;
            form.submit();
            return;
        }
		</script>

        <div class="nbill_<?php echo $form_def['class']; ?>">

		<table class="adminheading" style="width:auto;">
		<tr>
			<th <?php echo $form_def['icon']; ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . constant("NBILL_ORDER_FORMS_TITLE$lang_suffix"); ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		}

		if (count($duplicates) > 0)
		{
			echo "<div class=\"nbill-message\">" . NBILL_ORDER_FORM_DUPLICATE_PRODUCTS . "</div>";
		}

        if (count($unmapped) > 0)
        {
            echo "<div class=\"nbill-message\">" . NBILL_ORDER_FORM_UNMAPPED . "</div>";
        }
		?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="<?php echo $form_def['action']; ?>" />
        <input type="hidden" name="sub_action" value="<?php echo @$form_def['sub_action']; ?>" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0" />

		<p align="left"><?php echo $form_def['form_type'] == 'OR' ? sprintf(NBILL_ORDER_FORMS_INTRO, '<a href="http://' . NBILL_BRANDING_DOCUMENTATION . '" target="_blank">' . NBILL_ORDER_FORMS_DOC_LINK . '</a>') : constant("NBILL_ORDER_FORMS_INTRO$lang_suffix"); ?></p>

		<?php
        if (!@$form_def['vendor_agnostic'])
        {
		    //Display filter dropdown if multi-company
		    if (count($vendors) > 1)
		    {
			    echo "<p align=\"left\">" . NBILL_VENDOR_NAME . "&nbsp;";
			    $selected_filter = $vendors[0]->id;
			    if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'vendor_filter')) > 0)
			    {
				    $selected_filter = nbf_common::get_param($_POST, 'vendor_filter');
			    }
			    $vendor_name = array();
			    $vendor_name[] = nbf_html::list_option(-999, NBILL_ALL);
			    foreach ($vendors as $vendor)
			    {
				    $vendor_name[] = nbf_html::list_option($vendor->id, $vendor->vendor_name);
			    }
			    echo nbf_html::select_list($vendor_name, "vendor_filter", 'id="vendor_filter" class="inputbox" id="vendor_filter" onchange="nbill_submit_task();"', $selected_filter );
			    echo "</p>";
		    }
        }
		?>

        <div class="rounded-table">
		    <table class="adminlist table nbill_<?php echo $form_def['class']; ?>">
		    <tr>
			    <th class="selector">
				    <?php echo NBILL_ID; ?>
			    </th>
                <th class="selector">
                    <input type="checkbox" name="check_all" value="" onclick="for(var i=0; i<<?php echo count($rows); ?>;i++) {document.getElementById('cb' + i).checked=this.checked;} document.adminForm.box_checked.value=this.checked;" />
			    </th>
			    <th class="title">
				    <?php echo NBILL_FORM_TITLE; ?>
			    </th>
			    <th class="selector" colspan="2">
				    <?php echo NBILL_FORM_ORDERING; ?>
			    </th>
			    <th class="selector">
				    <?php echo NBILL_PUBLISHED; ?>
			    </th>
			    <?php
                if (!@$form_def['vendor_agnostic'])
                {
			        //Only show vendor name if more than one listed
			        if (count($vendors) > 1 && $selected_filter == -999)
			        {?>
				        <th class="title">
					        <?php echo NBILL_VENDOR_NAME; ?>
				        </th>
			            <?php
                        $vendor_col = true;
                    }
                }

                //Only show menu item creation column if at least one menu is known
                if (count($menus) > 0)
                {
                ?>
			        <th class="selector">
				        <?php echo NBILL_CREATE_MENU_ITEM; nbf_html::show_overlib(NBILL_CREATE_MENU_ITEM_HELP); ?>
			        </th>
                <?php
                } ?>
		    </tr>
		    <?php
            for ($i=0, $n=count( $rows ); $i < $n; $i++)
		    {
			    $row = &$rows[$i];

			    $highlight = "";
			    if (count($duplicates) > 0)
			    {
				    foreach ($duplicates as $duplicate)
				    {
					    if ($duplicate->form_id == $row->id)
					    {
						    $highlight = "background-color:#ffcccc !important;";
                            break;
					    }
				    }
			    }
                if (strlen($highlight) == 0 && count($unmapped) > 0)
                {
                    foreach ($unmapped as $unmapped_item)
                    {
                        if ($unmapped_item->form_id == $row->id)
                        {
                            $highlight = "background-color:#ffcccc !important;";
                            break;
                        }
                    }
                }

			    $link = $form_def['edit_link_prefix'] . "cid=$row->id";

			    $img = $row->published ? 'tick.png' : 'cross.png';
			    $task = $row->published ? 'unpublish' : 'publish';
			    $alt = $row->published ? 'Published' : 'Unpublished';

			    $checked = nbf_html::id_checkbox($i, $row->id);

			    echo "<tr>";
			    echo "<td class=\"selector\" style=\"$highlight\">";
			    echo $row->id;
			    $checked = nbf_html::id_checkbox($i, $row->id);
			    echo "</td><td class=\"selector\" style=\"$highlight\">$checked</td>";
                $fe_url = nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . "&amp;action=" . $form_def['fe_action'] . "&amp;task=order&amp;cid=$row->id" . htmlentities(nbf_cms::$interop->site_page_suffix);
			    echo "<td class=\"list-value\" style=\"$highlight\"><a href=\"$link\" title=\"" . @constant("NBILL_EDIT_ORDER_FORM$lang_suffix") . "\">" . $row->title . "</a>
                    &nbsp;<a target=\"_blank\" href=\"$fe_url\" title=\"" . NBILL_FORM_VIEW_IN_FE . "\"><img border=\"0\" src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/open.png\" alt=\"Open\" /></a>
                    </td>";
			    echo "<td class=\"selector\">";
			    echo $pagination->order_up_arrow($i);
			    echo "</td><td class=\"selector\">";
			    echo $pagination->order_down_arrow($i, $n);
			    echo "</td>";
			    echo "<td class=\"selector\" style=\"$highlight\">";
			    echo "<a href=\"#\" onclick=\"for(var i=0; i<" . count($rows) . ";i++) {document.getElementById('cb' + i).checked=false};document.getElementById('cb$i').checked=true;document.adminForm.task.value='$task';document.adminForm.submit();return false;\">";
			    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/$img\" border=\"0\" alt=\"$alt\" />";
			    echo "</a></td>";
                if (!@$form_def['vendor_agnostic'])
                {
			        //Only show vendor name if more than one listed
			        if (count($vendors) > 1 && $selected_filter == -999)
			        {
				        foreach ($vendors as $vendor)
				        {
					        if ($vendor->id == $row->vendor_id)
					        {
						        echo "<td class=\"list-value\" style=\"$highlight\">" . $vendor->vendor_name . "</td>";
						        $vendor_col = true;
						        break;
					        }
				        }
			        }
                }
			    echo "<td style=\"text-align:right;$highlight\">";
                if (count($menus) > 0)
                {
				    $menu_options = array();
				    foreach ($menus as $key=>$value)
				    {
					    $menu_options[] = nbf_html::list_option($key, $value);
				    }
				    echo nbf_html::select_list($menu_options, "menu_name_" . $row->id, 'id="menu_name_' . $row->id . '"', '');
				    echo "<input type=\"button\" class=\"button btn\" name=\"create_menu_" . $row->id . "\" value=\"" . NBILL_CREATE . "\" onclick=\"create_menu('" . $row->id . "');\" /></td>";
                }
			    echo "</tr>";
		    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="<?php echo (count($menus) > 0 ? ($vendor_col ? "9" : "8") : ($vendor_col ? "8" : "7")); ?>" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>
		</form>
		</div><?php
	}

	public static function editOrderForm($form_id, $row, $payment_plans, $vendors, $cats, $products, $selected_products, $disqual_products, $selected_disqual_products, $payment_gateways, $use_posted_values, $form_def, $no_of_pages = 1)
	{
		nbf_cms::$interop->init_editor();
		$field_tab_index = 0;
        $lang_suffix = $form_def['lang_suffix'];

        if (isset($form_def['edit_toolbar']))
        {
            echo $form_def['edit_toolbar'];
        }
        ?>
		<script language="javascript" type="text/javascript">
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

			if (document.getElementById('title').value == '')
			{
				alert('<?php echo NBILL_ORDER_FORM_TITLE_REQUIRED; ?>');
			}
			else if (form.max_upload_size && form.max_upload_size.value.length > 0 && !IsNumeric(form.max_upload_size.value, false))
			{
				alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_UPLOAD_MAX_SIZE); ?>');
			}
            else
			{
                window.frames.ifr_nbill_form_editor.show_wait_message(150, null);
                setTimeout(function(){do_sjax_submit(form, task_name)}, 50);
			}
		}
        function do_sjax_submit(form, task_name)
        {
            var err_msg = window.frames.ifr_nbill_form_editor.sjax_submit_form(task_name);
            if (err_msg.substr(0,3) == 'OK_')
            {
                if (task_name == 'save' || task_name == 'save_copy')
                {
                    document.adminForm.task.value='cancel';
                    document.adminForm.submit();
                }
                else
                {
                    //Re-load the page
                    form.task.value=task_name;
                    form.id.value=parseInt(err_msg.substr(3));
                    form.submit();
                    return;
                }
            }
            else
            {
                if (err_msg && err_msg.length > 0)
                {
                    alert(err_msg);
                }
                else
                {
                    alert('<?php echo NBILL_FORM_SAVE_FAILED; ?>');
                }
            }
        }
        function refresh_vendor()
		{
			//Show the appropriate dropdowns depending on selected vendor
			var vendor_id = document.getElementById('vendor_id').value;
			<?php
			foreach ($vendors as $vendor)
			{
				echo "document.getElementById('prereq_" . $vendor->id . "').style.display = 'none';";
				echo "document.getElementById('disqual_" . $vendor->id . "').style.display = 'none';";
			}
			?>
			document.getElementById('prereq_' + vendor_id).style.display = 'block';
			document.getElementById('disqual_' + vendor_id).style.display = 'block';
		}

        function populate_prereq_products(output)
        {
            //Extract the data
            if (output.length > 0)
            {
                var results = output.split('#!#');
                if (results.length == 2)
                {
                    document.getElementById('container_prereq_product_' + results[0]).innerHTML = results[1];
                }
            }
        }
		function select_product(vendor_id)
		{
			var selected_box = document.getElementById('cat_sel_product_' + vendor_id);
			var available_box = document.getElementById('cat_product_' + vendor_id);

			for (var i = 0; i < available_box.options.length; i++)
			{
				if (available_box.options[i].selected)
				{
					var exists = false;
					for (var j = 0; j < selected_box.options.length; j++)
					{
						if (selected_box.options[j].value == available_box.options[i].value)
						{
							exists = true;
							break;
						}
					}
					if (!exists)
					{
						var optn = document.createElement("option");
						optn.text = available_box.options[i].text;
						optn.value = available_box.options[i].value;
						selected_box.options.add(optn);
					}
				}
			}
			update_selected_products(vendor_id);
		}

		function remove_product(vendor_id)
		{
			var selected_box = document.getElementById('cat_sel_product_' + vendor_id);
			for (var i=0; i < selected_box.options.length; i++)
			{
				if (selected_box.options[i].selected)
				{
					selected_box.remove(i);
					i--;
				}
			}
			update_selected_products(vendor_id);
		}

		function update_selected_products(vendor_id)
		{
			var selected_box = document.getElementById('cat_sel_product_' + vendor_id);
			var selected_options = new Array();

			for (var i = 0; i < selected_box.options.length; i++)
			{
				selected_options[i] = selected_box.options[i].value;
			}
			document.getElementById('prerequisite_products_' + vendor_id).value = selected_options.join(',');
		}

		//Disqualifying products
        function populate_disqual_products(output)
        {
            //Extract the data
            if (output.length > 0)
            {
                var results = output.split('#!#');
                if (results.length == 2)
                {
                    document.getElementById('container_disqual_product_' + results[0]).innerHTML = results[1];
                }
            }
        }

		function select_disqual_product(vendor_id)
		{
			var selected_box = document.getElementById('cat_sel_disqual_product_' + vendor_id);
			var available_box = document.getElementById('cat_disqual_product_' + vendor_id);

			for (var i = 0; i < available_box.options.length; i++)
			{
				if (available_box.options[i].selected)
				{
					var exists = false;
					for (var j = 0; j < selected_box.options.length; j++)
					{
						if (selected_box.options[j].value == available_box.options[i].value)
						{
							exists = true;
							break;
						}
					}
					if (!exists)
					{
						var optn = document.createElement("option");
						optn.text = available_box.options[i].text;
						optn.value = available_box.options[i].value;
						selected_box.options.add(optn);
					}
				}
			}
			update_selected_disqual_products(vendor_id);
		}

		function remove_disqual_product(vendor_id)
		{
			var selected_box = document.getElementById('cat_sel_disqual_product_' + vendor_id);
			for (var i=0; i < selected_box.options.length; i++)
			{
				if (selected_box.options[i].selected)
				{
					selected_box.remove(i);
					i--;
				}
			}
			update_selected_disqual_products(vendor_id);
		}

		function update_selected_disqual_products(vendor_id)
		{
			var selected_box = document.getElementById('cat_sel_disqual_product_' + vendor_id);
			var selected_options = new Array();

			for (var i = 0; i < selected_box.options.length; i++)
			{
				selected_options[i] = selected_box.options[i].value;
			}
			document.getElementById('disqualifying_products_' + vendor_id).value = selected_options.join(',');
		}

        function save_thank_you_message()
        {
            <?php
            //Ensure HTML is saved to underlying textarea
            echo nbf_cms::$interop->get_editor_contents('editor1', 'thank_you_message');
            ?>
        }

        function save_unavailable_message()
        {
            <?php
            //Ensure HTML is saved to underlying textarea
            echo nbf_cms::$interop->get_editor_contents('editor0', 'form_unavailable_message');
            ?>
        }

        function save_niceditors()
        {
            for (index = 0; index < window[0].nicEditors.editors.length; index++) {
                for (instance_index = 0; instance_index < window[0].nicEditors.editors[index].nicInstances.length; instance_index++) {
                    window[0].nicEditors.editors[index].nicInstances[instance_index].saveContent();
                }
            }
        }
		</script>

        <div class="nbill_<?php echo $form_def['class']; ?>">
		<table class="adminheading" style="width:auto;">
		<tr>
			<th <?php echo $form_def['icon']; ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php
				echo $row->id ? constant("NBILL_EDIT_ORDER_FORM$lang_suffix") . " '$row->title'" : constant("NBILL_NEW_ORDER_FORM$lang_suffix");
				?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
        <?php if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
        {
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		} ?>
        <?php
        if (nbf_cms::$interop->show_gzip_warning()) { ?><div class="nbill-message"><?php $url = nbf_cms::$interop->get_gzip_config_url(); echo nbf_common::nb_strlen($url) > 0 ? sprintf(NBILL_GZIP_WARNING_URL, $url) : NBILL_GZIP_WARNING; ?></div><?php }
        ?>
        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="<?php echo $form_def['action']; ?>" />
        <input type="hidden" name="sub_action" value="<?php echo @$form_def['sub_action']; ?>" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $form_id;?>" />
        <input type="hidden" name="ordering" id="ordering" value="<?php echo $row->ordering; ?>" />
		<input type="hidden" name="form_type" value="<?php echo $form_def['form_type']; ?>" />
        <?php nbf_html::add_filters(); ?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
		    <tr>
			    <th colspan="2"><?php echo NBILL_FORM_DETAILS; ?></th>
		    </tr>
		    <?php
            $hidden_vendor = false;
		    if (!@$form_def['vendor_agnostic'])
            {
                if (count($vendors) > 1)
			    {?>
				    <tr>
					    <td class="nbill-setting-caption">
						    <?php echo NBILL_VENDOR_NAME; ?>
					    </td>
					    <td class="nbill-setting-value">
						    <?php
							    $vendor_name = array();
							    foreach ($vendors as $vendor)
							    {
								    $vendor_name[] = nbf_html::list_option($vendor->id, $vendor->vendor_name);
							    }
							    if ($use_posted_values)
							    {
								    $selected_vendor = nbf_common::get_param($_POST, 'vendor_id');
							    }
							    else
							    {
								    if($row->id)
								    {
									    $selected_vendor = $row->vendor_id;
								    }
								    else
								    {
									    $selected_vendor = nbf_common::get_param($_POST, 'vendor_filter');
								    }
								    if ($selected_vendor < 1)
								    {
									    $selected_vendor = @$vendors[0]->id;
								    }
							    }
							    echo nbf_html::select_list($vendor_name, "vendor_id", 'id="vendor_id" class="inputbox" onchange="refresh_vendor();"', $selected_vendor);
						    ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_VENDOR_ID, "vendor_id_help"); ?>
					    </td>
				    </tr>
			    <?php }
			    else
			    {
				    $hidden_vendor = true;
			    }
            }
            else
            {
                $hidden_vendor = true;
            }
		    ?>
		    <tr>
			    <td colspan="2">
                    <script type="text/javascript">
                    <!--
                    //Disable the tabs until the iframe loads to avoid errors, but not for more than 5 seconds
                    var nbill_disable_tabs=true;window.setTimeout('if(nbill_disable_tabs){nbill_disable_tabs=false;select_tab_form(document.getElementById("nbill_selected_tab_form").value);}', 5000);
                    -->
                    </script>

				    <?php
                    if ($hidden_vendor)
                    {
                        echo "<input type=\"hidden\" name=\"vendor_id\" id=\"vendor_id\" value=\"" . $vendors[0]->id . "\" />";
                        $_POST['vendor_id'] = $vendors[0]->id;
                        $selected_vendor = $vendors[0]->id;
                    }
				    $nbf_tab_form = new nbf_tab_group();
				    $nbf_tab_form->start_tab_group("form");
                    $nbf_tab_form->add_tab_title("fields", NBILL_FORM_TAB_FIELDS);
                    if (array_search("tab_details", $form_def['suppressed']) === false)
                    {
                        $nbf_tab_form->add_tab_title("details", NBILL_FORM_TAB_DETAILS);
                    }
                    if (array_search("tab_order", $form_def['suppressed']) === false)
                    {
                        $nbf_tab_form->add_tab_title("order", NBILL_FORM_TAB_ORDER_VALUES);
                    }
                    if (array_search("tab_advanced", $form_def['suppressed']) === false)
                    {
                        $nbf_tab_form->add_tab_title("advanced", NBILL_FORM_TAB_ADVANCED);
                    }
                    if (@$form_def['custom_settings_tab_title'])
                    {
                        $custom_settings = @$form_def['custom_settings'];
                        if ($custom_settings && count($custom_settings) > 0)
                        {
                            $nbf_tab_form->add_tab_title("custom_" . $form_def['action'] . "_" . @$form_def['sub_action'], $form_def['custom_settings_tab_title']);
                        }
                    }
                    ob_start();
                    ?>
                    <div class="small-screen touch-screen">
                        <?php echo NBILL_LARGE_SCREEN_WITH_POINTER_REQUIRED; ?>
                    </div>
                    <div class="large-screen has-pointer">
                        <p style="padding:5px;margin:0px;"><?php echo NBILL_FORM_EDITOR_INTRO; ?></p>
                        <input type="hidden" name="nbill_editor_ifr_selected_tab" id="nbill_editor_ifr_selected_tab" value="<?php echo nbf_common::get_param($_REQUEST, 'nbill_editor_ifr_selected_tab'); ?>" />
                        <iframe id="ifr_nbill_form_editor" name="ifr_nbill_form_editor" frameborder="0" style="width:100%;height:900px;" src="<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&action=form_editor&vendor_id=<?php echo $selected_vendor; ?>&form_id=<?php echo $row->id; ?>&hide_billing_menu=1<?php if (nbf_common::get_param($_REQUEST, 'task')=='apply'){echo '&nbill_selected_tab_nbill_form_pages=' . nbf_common::get_param($_REQUEST, 'nbill_editor_ifr_selected_tab');} ?>&form_type=<?php echo $form_def['form_type']; ?>&nocache=<?php echo uniqid(); ?>" onload="setTimeout('if(nbill_disable_tabs){nbill_disable_tabs=false;select_tab_form(document.getElementById(\'nbill_selected_tab_form\').value);}', 1000);">
                            <?php echo NBILL_IFRAMES_REQUIRED; ?>
                        </iframe>
                    </div>
                    <?php
                    $nbf_tab_form->add_tab_content("fields", ob_get_clean());

                    ob_start();
				    ?>
				    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
                        <?php if (array_search("details_published", $form_def['suppressed']) === false) { ?>
					    <tr>
						    <td class="nbill-setting-caption">
							    <?php echo NBILL_FORM_PUBLISHED; ?>
						    </td>
						    <td class="nbill-setting-value">
							    <?php echo nbf_html::yes_or_no_options("published", "", $use_posted_values ? nbf_common::get_param($_POST,'published', null, true) : $row->published); ?>
                                <?php nbf_html::show_static_help(NBILL_INSTR_FORM_PUBLISHED, "published_help"); ?>
						    </td>
					    </tr>
                        <?php }
                        if (array_search("details_title", $form_def['suppressed']) === false) { ?>
					    <tr>
						    <td class="nbill-setting-caption">
							    <?php echo NBILL_FORM_TITLE; ?>
						    </td>
						    <td class="nbill-setting-value">
							    <input type="text" name="title" id="title" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'title')) : $row->title; ?>" class="inputbox" style="width:200px" />
                                <?php nbf_html::show_static_help(NBILL_INSTR_FORM_TITLE, "title_help"); ?>
						    </td>
					    </tr>
                        <!-- Custom Fields Placeholder -->
                        <?php }
                        if (array_search("details_form_unavailable_message", $form_def['suppressed']) === false) { ?>
                        <tr>
                            <td class="nbill-setting-caption">
                                <?php echo NBILL_ORDER_FORM_UNAVAILABLE; ?>
                            </td>
                            <td class="nbill-setting-value">
                                <div style="float:left;">
                                <?php
                                echo nbf_cms::$interop->render_editor("form_unavailable_message", "editor0", $use_posted_values ? @$_POST['form_unavailable_message'] : $row->form_unavailable_message); ?>
                                </div>
                                <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_FORM_UNAVAILABLE, "form_unavailable_message_help"); ?>
                            </td>
                        </tr>
                        <?php }
                        if (array_search("details_always_show", $form_def['suppressed']) === false) { ?>
                        <tr>
                            <td class="nbill-setting-caption">
                                <?php echo NBILL_FORM_ALWAYS_SHOW; ?>
                            </td>
                            <td class="nbill-setting-value">
                                <?php echo nbf_html::yes_or_no_options("always_show", "", $use_posted_values ? nbf_common::get_param($_POST,'always_show', null, true) : $row->always_show); ?>
                                <?php nbf_html::show_static_help(NBILL_INSTR_FORM_ALWAYS_SHOW, "always_show_help"); ?>
                            </td>
                        </tr>
                        <?php }
                        if (array_search("details_thank_you_redirect", $form_def['suppressed']) === false) { ?>
					    <tr>
                            <td class="nbill-setting-caption">
                                <?php echo constant("NBILL_ORDER_FORM_THANK_YOU_REDIRECT$lang_suffix"); ?>
                            </td>
                            <td class="nbill-setting-value">
                                <input type="text" name="thank_you_redirect" id="thank_you_redirect" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'thank_you_redirect')) : $row->thank_you_redirect; ?>" class="inputbox" style="width:200px" />
                                <?php nbf_html::show_static_help(constant("NBILL_INSTR_ORDER_FORM_THANK_YOU_REDIRECT$lang_suffix"), "thank_you_redirect_help"); ?>
                            </td>
                        </tr>
                        <?php }
                        if (array_search("details_thank_you_message", $form_def['suppressed']) === false) { ?>
					    <tr>
						    <td class="nbill-setting-caption">
							    <?php echo NBILL_ORDER_FORM_THANK_YOU; ?>
						    </td>
						    <td class="nbill-setting-value">
							    <div style="float:left;">
							    <?php
							    echo nbf_cms::$interop->render_editor("thank_you_message", "editor1", $use_posted_values ? stripslashes((isset($_POST['thank_you_message']) && nbf_common::nb_strlen($_POST['thank_you_message']) > 0 ? $_POST['thank_you_message'] : constant("NBILL_FORM_DEFAULT_THANK_YOU$lang_suffix"))) : ($row->id ? $row->thank_you_message : constant("NBILL_FORM_DEFAULT_THANK_YOU$lang_suffix"))); ?>
                                </div>
                                <?php nbf_html::show_static_help(constant("NBILL_INSTR_ORDER_FORM_THANK_YOU$lang_suffix"), "thank_you_message_help"); ?>
						    </td>
					    </tr>
                        <?php }
                        if (array_search("details_gateway", $form_def['suppressed']) === false) { ?>
					    <tr>
						    <td class="nbill-setting-caption">
							    <?php echo NBILL_PAYMENT_GATEWAY; ?>
						    </td>
						    <td class="nbill-setting-value">
							    <?php
							    $gateway_list = array();
							    $gateway_list[] = nbf_html::list_option("-1", "-1 - " . NBILL_NOT_APPLICABLE);
							    foreach ($payment_gateways as $payment_gateway)
							    {
								    $gateway_list[] = nbf_html::list_option($payment_gateway->gateway_id, $payment_gateway->display_name);
							    }
							    $selected_gateway = $use_posted_values ? nbf_common::get_param($_POST, 'gateway', null, true) : $row->payment_gateway;
							    if (!$selected_gateway)
							    {
								    $selected_gateway = $vendor->default_gateway;
							    }
							    echo nbf_html::select_list($gateway_list, "gateway", 'class="inputbox" id="gateway" onclick="document.getElementById(\'payment_gateway\').value = document.getElementById(\'gateway\').value"', $selected_gateway);
							    ?>
                                <?php nbf_html::show_static_help(NBILL_INSTR_PAYMENT_GATEWAY, "gateway_help"); ?>
						    </td>
					    </tr>
                        <?php }
                        if (array_search("details_pending_until_paid", $form_def['suppressed']) === false) { ?>
                        <tr>
                            <td class="nbill-setting-caption">
                                <?php echo @constant("NBILL_FORM_PENDING_UNTIL_PAID$lang_suffix"); ?>
                            </td>
                            <td class="nbill-setting-value">
                                <?php
                                $pending_options = array();
                                $pending_options[] = nbf_html::list_option(0, NBILL_NO);
                                $pending_options[] = nbf_html::list_option(1, NBILL_YES);
                                $pending_options[] = nbf_html::list_option(2, NBILL_PENDING_IF_PAID_ONLINE);
                                echo nbf_html::radio_list($pending_options, "pending_until_paid", $use_posted_values ? nbf_common::get_param($_POST,'pending_until_paid', null, true) : $row->pending_until_paid); ?>
                                <br />
                                <?php nbf_html::show_static_help(@constant("NBILL_INSTR_FORM_PENDING_UNTIL_PAID$lang_suffix"), "pending_until_paid_help"); ?>
                            </td>
                        </tr>
                        <?php }
                        if (array_search("details_offline_payment_redirect", $form_def['options_included']) !== false) { ?>
                        <tr>
                            <td class="nbill-setting-caption">
                                <?php echo constant("NBILL_ORDER_FORM_OFFLINE_PAYMENT_REDIRECT$lang_suffix"); ?>
                            </td>
                            <td class="nbill-setting-value">
                                <input type="text" name="offline_payment_redirect" id="offline_payment_redirect" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'offline_payment_redirect')) : $row->offline_payment_redirect; ?>" class="inputbox" style="width:200px" />
                                <?php nbf_html::show_static_help(constant("NBILL_INSTR_ORDER_FORM_OFFLINE_PAYMENT_REDIRECT$lang_suffix"), "offline_payment_redirect_help"); ?>
                            </td>
                        </tr>
                        <?php }
                        if (array_search("details_quote_accept_redirect", $form_def['suppressed']) === false && defined('NBILL_FORM_DEFAULT_QUOTE_ACCEPT_REDIRECT')) { ?>
                        <tr>
                            <td class="nbill-setting-caption">
                                <?php echo NBILL_FORM_DEFAULT_QUOTE_ACCEPT_REDIRECT; ?>
                            </td>
                            <td class="nbill-setting-value">
                                <input type="text" name="quote_accept_redirect" id="quote_accept_redirect" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'quote_accept_redirect')) : $row->quote_accept_redirect; ?>" class="inputbox" style="width:200px" />
                                <?php nbf_html::show_static_help(NBILL_INSTR_FORM_DEFAULT_QUOTE_ACCEPT_REDIRECT, "quote_accept_redirect_help"); ?>
                            </td>
                        </tr>
                        <?php }

                        if (array_search("details_logged_in_users_only", $form_def['suppressed']) === false) { ?>
					    <tr>
						    <td class="nbill-setting-caption">
							    <?php echo NBILL_FORM_LOGGED_IN_ONLY; ?>
						    </td>
						    <td class="nbill-setting-value">
							    <?php echo nbf_html::yes_or_no_options("logged_in_users_only", "", $use_posted_values ? nbf_common::get_param($_POST,'logged_in_users_only', null, true) : $row->logged_in_users_only); ?>
                                <?php nbf_html::show_static_help(NBILL_INSTR_FORM_LOGGED_IN_ONLY, "logged_in_users_only_help"); ?>
						    </td>
					    </tr>
                        <?php }
                        if (array_search("details_guests_only", $form_def['suppressed']) === false) { ?>
                        <tr>
                            <td class="nbill-setting-caption">
                                <?php echo NBILL_FORM_GUESTS_ONLY; ?>
                            </td>
                            <td class="nbill-setting-value">
                                <?php echo nbf_html::yes_or_no_options("guests_only", "", $use_posted_values ? nbf_common::get_param($_POST,'guests_only', null, true) : $row->guests_only); ?>
                                <?php nbf_html::show_static_help(NBILL_INSTR_FORM_GUESTS_ONLY, "guests_only_help"); ?>
                            </td>
                        </tr>
                        <?php }
                        if (array_search("details_email_pending_to_client", $form_def['suppressed']) === false) { ?>
					    <tr>
						    <td class="nbill-setting-caption">
							    <?php echo NBILL_FORM_EMAIL_CLIENT_PENDING; ?>
						    </td>
						    <td class="nbill-setting-value">
							    <?php echo nbf_html::yes_or_no_options("email_pending_to_client", "", $use_posted_values ? nbf_common::get_param($_POST,'email_pending_to_client', null, true) : $row->email_pending_to_client); ?>
                                <?php nbf_html::show_static_help(NBILL_INSTR_FORM_EMAIL_CLIENT_PENDING, "email_pending_to_client_help"); ?>
						    </td>
					    </tr>
                        <?php }
                        if (array_search("details_email_confirmation_to_client", $form_def['suppressed']) === false) { ?>
					    <tr>
						    <td class="nbill-setting-caption">
							    <?php echo NBILL_FORM_EMAIL_CLIENT; ?>
						    </td>
						    <td class="nbill-setting-value">
							    <?php echo nbf_html::yes_or_no_options("email_confirmation_to_client", "", $use_posted_values ? nbf_common::get_param($_POST,'email_confirmation_to_client', null, true) : $row->email_confirmation_to_client); ?>
                                <?php nbf_html::show_static_help(constant("NBILL_INSTR_FORM_EMAIL_CLIENT$lang_suffix"), "email_confirmation_to_client_help"); ?>
						    </td>
					    </tr>
                        <?php }
                        if (array_search("details_email_admin_pending", $form_def['suppressed']) === false) { ?>
					    <tr>
						    <td class="nbill-setting-caption">
							    <?php echo NBILL_FORM_EMAIL_ADMIN_PENDING; ?>
						    </td>
						    <td class="nbill-setting-value">
							    <?php echo nbf_html::yes_or_no_options("email_admin_pending", "", $use_posted_values ? nbf_common::get_param($_POST,'email_admin_pending', null, true) : $row->email_admin_pending); ?>
                                <?php nbf_html::show_static_help(NBILL_INSTR_FORM_EMAIL_ADMIN_PENDING, "email_admin_pending_help"); ?>
						    </td>
					    </tr>
                        <?php }
                        if (array_search("details_email_admin", $form_def['suppressed']) === false) { ?>
					    <tr>
						    <td class="nbill-setting-caption">
							    <?php echo NBILL_FORM_EMAIL_ADMIN; ?>
						    </td>
						    <td class="nbill-setting-value">
							    <?php echo nbf_html::yes_or_no_options("email_admin", "", $use_posted_values ? nbf_common::get_param($_POST,'email_admin', null, true) : $row->email_admin); ?>
                                <?php nbf_html::show_static_help(constant("NBILL_INSTR_FORM_EMAIL_ADMIN$lang_suffix"), "email_admin_help"); ?>
						    </td>
					    </tr>
                        <?php }
                        if (array_search("details_admin_email_to", $form_def['suppressed']) === false) { ?>
					    <tr>
						    <td class="nbill-setting-caption">
							    <?php echo NBILL_FORM_ADMIN_EMAIL_ADDR; ?>
						    </td>
						    <td class="nbill-setting-value">
							    <input type="text" name="admin_email_to" id="admin_email_to" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'admin_email_to', null, true) : $row->admin_email_to; ?>" class="inputbox" style="width:200px" />
                                <?php nbf_html::show_static_help(NBILL_INSTR_FORM_ADMIN_EMAIL_ADDR, "admin_email_to_help"); ?>
						    </td>
					    </tr>
                        <?php }
                        if (array_search("details_auto_handle_shipping", $form_def['suppressed']) === false) { ?>
					    <tr>
						    <td class="nbill-setting-caption">
							    <?php echo NBILL_FORM_AUTO_HANDLE_SHIPPING; ?>
						    </td>
						    <td class="nbill-setting-value">
							    <?php echo nbf_html::yes_or_no_options("auto_handle_shipping", "", $use_posted_values ? nbf_common::get_param($_POST,'auto_handle_shipping', null, true) : $row->auto_handle_shipping); ?>
                                <?php nbf_html::show_static_help(NBILL_INSTR_FORM_AUTO_HANDLE_SHIPPING, "auto_handle_shipping_help"); ?>
						    </td>
					    </tr>
                        <?php }
                        if (array_search("details_auto_create_user", $form_def['suppressed']) === false) { ?>
                        <tr>
                            <td class="nbill-setting-caption">
                                <?php echo NBILL_FORM_AUTO_CREATE_USER; ?>
                            </td>
                            <td class="nbill-setting-value">
                                <?php echo nbf_html::yes_or_no_options("auto_create_user", "", $use_posted_values ? nbf_common::get_param($_POST,'auto_create_user', null, true) : $row->auto_create_user); ?>
                                <?php nbf_html::show_static_help(constant("NBILL_INSTR_FORM_AUTO_CREATE_USER$lang_suffix"), "auto_create_user_help"); ?>
                            </td>
                        </tr>
                        <?php }
                        if (array_search("details_use_email_address", $form_def['suppressed']) === false) { ?>
                        <tr>
                            <td class="nbill-setting-caption">
                                <?php echo NBILL_FORM_USE_EMAIL_ADDRESS; ?>
                            </td>
                            <td class="nbill-setting-value">
                                <?php echo nbf_html::yes_or_no_options("use_email_address", "", $use_posted_values ? nbf_common::get_param($_POST,'use_email_address', null, true) : $row->use_email_address); ?>
                                <?php nbf_html::show_static_help(NBILL_INSTR_FORM_USE_EMAIL_ADDRESS, "use_email_address_help"); ?>
                            </td>
                        </tr>
                        <?php }
                        if (array_search("details_auto_create_orders", $form_def['suppressed']) === false) { ?>
					    <tr>
						    <td class="nbill-setting-caption">
							    <?php echo @constant("NBILL_FORM_AUTO_CREATE_ORDERS$lang_suffix"); ?>
						    </td>
						    <td class="nbill-setting-value">
							    <?php
                                if ($form_def['form_type'] == 'QU')
                                {
                                    //Offer 3 options (this may be added for order forms as well)
                                    $order_options = array();
                                    $order_options[] = nbf_html::list_option(0, NBILL_NO);
                                    $order_options[] = nbf_html::list_option(1, NBILL_YES);
                                    $order_options[] = nbf_html::list_option(2, NBILL_FORM_QUOTE_ORDERS_IF_RECURRING);
                                    echo nbf_html::radio_list($order_options, "auto_create_orders", $use_posted_values ? nbf_common::get_param($_POST,'auto_create_orders', null, true) : ($row->id ? $row->auto_create_orders : 2));
                                }
                                else
                                {
                                    echo nbf_html::yes_or_no_options("auto_create_orders", "", $use_posted_values ? nbf_common::get_param($_POST,'auto_create_orders', null, true) : $row->auto_create_orders);
                                } ?>
                                <?php nbf_html::show_static_help(@constant("NBILL_INSTR_FORM_AUTO_CREATE_ORDERS$lang_suffix"), "auto_create_orders_help"); ?>
						    </td>
					    </tr>
                        <?php }
                        if (array_search("details_auto_create_invoice", $form_def['suppressed']) === false) { ?>
                        <tr>
						    <td class="nbill-setting-caption">
							    <?php echo @constant("NBILL_FORM_AUTO_CREATE_INVOICE$lang_suffix"); ?>
						    </td>
						    <td class="nbill-setting-value">
							    <?php echo nbf_html::yes_or_no_options("auto_create_invoice", "", $use_posted_values ? nbf_common::get_param($_POST,'auto_create_invoice', null, true) : $row->auto_create_invoice); ?>
                                <?php nbf_html::show_static_help(@constant("NBILL_INSTR_FORM_AUTO_CREATE_INVOICE$lang_suffix"), "auto_create_invoice_help"); ?>
						    </td>
					    </tr>
                        <?php }
                        if (array_search("details_auto_create_income", $form_def['suppressed']) === false) { ?>
					    <tr>
						    <td class="nbill-setting-caption">
							    <?php echo @constant("NBILL_FORM_AUTO_CREATE_INCOME$lang_suffix"); ?>
						    </td>
						    <td class="nbill-setting-value">
							    <?php echo nbf_html::yes_or_no_options("auto_create_income", "", $use_posted_values ? nbf_common::get_param($_POST,'auto_create_income', null, true) : $row->auto_create_income); ?>
                                <?php nbf_html::show_static_help(@constant("NBILL_INSTR_FORM_AUTO_CREATE_INCOME$lang_suffix"), "auto_create_income_help"); ?>
						    </td>
					    </tr>
                        <?php }
                        if (array_search("details_payment_plan_id", $form_def['suppressed']) === false) { ?>
                        <tr>
                            <td class="nbill-setting-caption">
                                <?php echo NBILL_PAYMENT_PLAN; ?>
                            </td>
                            <td class="nbill-setting-value">
                                <?php
                                $plan_list = array();
                                foreach ($payment_plans as $payment_plan)
                                {
                                    $plan_list[] = nbf_html::list_option($payment_plan->id, $payment_plan->plan_name);
                                }
                                $selected_plan = $use_posted_values ? nbf_common::get_param($_POST, 'payment_plan_id') : $row->payment_plan_id;
                                echo nbf_html::select_list($plan_list, "payment_plan_id", 'id="payment_plan_id" class="inputbox"', $selected_plan);
                                ?>
                                <?php nbf_html::show_static_help(@constant("NBILL_INSTR_PAYMENT_PLAN$lang_suffix"), "payment_plan_id_help"); ?>
                            </td>
                        </tr>
                        <?php } ?>
				    </table>
				    <?php if ($row->id)
				    {
                        $url = nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . "&amp;action=" . $form_def['fe_action'] . "&amp;task=order&amp;cid=$row->id" . htmlentities(nbf_cms::$interop->site_page_suffix);
					    echo "<p>" . sprintf(NBILL_ORDER_FORM_LINK, "<br /><br /><strong><a target=\"_blank\" href=\"$url\">" . $url . "</a></strong><br /><br />") . NBILL_ORDER_FORM_LINK_PREPOP . ($form_def['form_type'] == 'OR' ? NBILL_ORDER_FORM_LINK_PREPOP_ORDER_SUFFIX : "") . "</p>";
				    }
				    $nbf_tab_form->add_tab_content("details", ob_get_clean());

                    if (array_search("tab_order", $form_def['suppressed']) === false)
                    {
                        ob_start();
				        ?>
                        <p><?php echo NBILL_ORDER_VALUES_INTRO; ?></p>
				        <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
					        <?php
                            if (array_search("order_discount_voucher_code", $form_def['suppressed']) === false) { ?>
                            <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_FORM_VOUCHER_CODE; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <input type="text" name="discount_voucher_code" id="discount_voucher_code" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'discount_voucher_code')) : $row->discount_voucher_code; ?>" class="inputbox" style="width:200px" />
                                    <?php nbf_html::show_static_help(NBILL_INSTR_FORM_VOUCHER_CODE, "discount_voucher_code_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("order_payment_gateway", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_FORM_ORDER_GATEWAY; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <input type="text" name="payment_gateway" id="payment_gateway" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'payment_gateway')) : $row->payment_gateway; ?>" class="inputbox" style="width:200px" />
                                    <?php nbf_html::show_static_help(NBILL_INSTR_FORM_ORDER_GATEWAY, "payment_gateway_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("order_relating_to", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_FORM_RELATING_TO; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <input type="text" name="relating_to" id="relating_to" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'relating_to')) : $row->relating_to; ?>" class="inputbox" style="width:200px" />
                                    <?php nbf_html::show_static_help(NBILL_INSTR_FORM_RELATING_TO, "relating_to_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("order_shipping_id", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_FORM_SHIPPING_ID; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <input type="text" name="shipping_id" id="shipping_id" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'shipping_id')) : $row->shipping_id; ?>" class="inputbox" style="width:200px" />
                                    <?php nbf_html::show_static_help(sprintf(NBILL_INSTR_FORM_SHIPPING_ID, "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=shipping\">" .  NBILL_SHIPPING_LIST . "</a>"), "shipping_id_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("order_tax_exemption_code", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_FORM_TAX_EXEMPTION_CODE; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <input type="text" name="tax_exemption_code" id="tax_exemption_code" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'tax_exemption_code')) : $row->tax_exemption_code; ?>" class="inputbox" style="width:200px" />
                                    <?php nbf_html::show_static_help(NBILL_INSTR_FORM_TAX_EXEMPTION_CODE, "tax_exemption_code_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("order_payment_frequency", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_FORM_PAYMENT_FREQUENCY; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <input type="text" name="payment_frequency" id="payment_frequency" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'payment_frequency')) : $row->payment_frequency; ?>" class="inputbox" style="width:200px" />
                                    <?php nbf_html::show_static_help(NBILL_INSTR_FORM_PAYMENT_FREQUENCY, "payment_frequency_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("order_currency", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_FORM_CURRENCY; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <input type="text" name="currency" id="currency" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'currency')) : $row->currency; ?>" class="inputbox" style="width:200px" />
                                    <?php nbf_html::show_static_help(NBILL_INSTR_FORM_CURRENCY, "currency_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("order_unique_invoice", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_FORM_UNIQUE_INVOICE; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <input type="text" name="unique_invoice" id="unique_invoice" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'unique_invoice')) : $row->unique_invoice; ?>" class="inputbox" style="width:200px" />
                                    <?php nbf_html::show_static_help(NBILL_INSTR_FORM_UNIQUE_INVOICE, "unique_invoice_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("order_auto_renew", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_FORM_AUTO_RENEW; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <input type="text" name="auto_renew" id="auto_renew" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'auto_renew')) : $row->auto_renew; ?>" class="inputbox" style="width:200px" />
                                    <?php nbf_html::show_static_help(NBILL_INSTR_FORM_AUTO_RENEW, "auto_renew_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("order_expire_after", $form_def['suppressed']) === false) { ?>
                            <tr>
                                <td class="nbill-setting-caption">
                                    <?php echo NBILL_ORDER_FORM_EXPIRE_AFTER; ?>
                                </td>
                                <td class="nbill-setting-value">
                                    <input type="text" name="expire_after" id="expire_after" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'expire_after', null, true) : $row->expire_after; ?>" class="inputbox" style="width:200px" />
                                    <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_FORM_EXPIRE_AFTER, "expire_after_help"); ?>
                                </td>
                            </tr>
                            <?php }
                            if (array_search("order_expiry_date", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php
							        $cal_date_format = nbf_common::get_date_format(true);
							        echo sprintf(NBILL_FORM_EXPIRY_DATE, $cal_date_format); ?>
						        </td>
						        <td class="nbill-setting-value">
							        <?php
								        $expiry_date = "";
								        if (is_numeric($row->expiry_date))
								        {
									        $expiry_date = nbf_common::nb_date(nbf_common::get_date_format(), $row->expiry_date);
								        }
								        if (nbf_common::nb_strpos($row->expiry_date, "##") !== false)
								        {
									        $expiry_date = $row->expiry_date;
								        }
							        ?>
							        <input type="text" name="expiry_date" id="expiry_date" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'expiry_date')) : $expiry_date; ?>" class="inputbox" style="width:200px" />
                                    <?php nbf_html::show_static_help(NBILL_INSTR_FORM_EXPIRY_DATE, "expiry_date_help"); ?>
						        </td>
					        </tr>
                            <?php } ?>
				        </table>

				        <?php
                        $nbf_tab_form->add_tab_content("order", ob_get_clean());
                    }

                    if (array_search("tab_advanced", $form_def['suppressed']) === false)
                    {
				        ob_start();
				        ?>
				        <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
                            <?php
                            if (array_search("advanced_upload_path", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_UPLOAD_PATH; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <input type="text" name="upload_path" id="upload_path" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'upload_path')) : $row->upload_path; ?>" class="inputbox" />
                                    <?php nbf_html::show_static_help(NBILL_INSTR_UPLOAD_PATH . " " . NBILL_FORM_UPLOAD_PATH_WARNING, "upload_path_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("advanced_max_upload_size", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_UPLOAD_MAX_SIZE; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <input type="text" name="max_upload_size" id="max_upload_size" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'max_upload_size')) : $row->max_upload_size; ?>" class="inputbox numeric" /> KB
                                    <?php nbf_html::show_static_help(NBILL_INSTR_UPLOAD_MAX_SIZE, "max_upload_size_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("advanced_allowed_types", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_UPLOAD_ALLOWED_TYPES; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <input type="text" name="allowed_types" id="allowed_types" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST,'allowed_types')) : $row->allowed_types; ?>" class="inputbox" />
                                    <?php nbf_html::show_static_help(NBILL_INSTR_UPLOAD_ALLOWED_TYPES . " " . NBILL_FORM_UPLOAD_TYPE_WARNING, "allowed_types_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("advanced_attach_to_email", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_ATTACH_TO_EMAIL; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <?php echo nbf_html::yes_or_no_options("attach_to_email", "", $use_posted_values ? nbf_common::get_param($_POST,'attach_to_email', null, true) : $row->attach_to_email); ?>
                                    <?php nbf_html::show_static_help(NBILL_INSTR_ATTACH_TO_EMAIL, "attach_to_email_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("advanced_prereq_products", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_FORM_PREREQ_PRODUCTS; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <?php
							        //Wizard style select list (categories, products, selected products)
							        foreach ($vendors as $vendor)
							        {
								        if ($vendor->id == $selected_vendor)
								        {
									        $visibility = "block";
								        }
								        else
								        {
									        $visibility = "none";
								        }
							        ?>
							        <div id="prereq_<?php echo $vendor->id; ?>" class="list-multi-selector" style="display:<?php echo $visibility;?>">
								        <div class="list-container">
											<?php echo NBILL_FORM_PREREQ_CATS; ?><br />
                                            <div class="scrollable-multi-select">
											    <select multiple="multiple" size="7" name="category_<?php echo $vendor->id; ?>[]" id="category_<?php echo $vendor->id; ?>" onchange="submit_ajax_request('get_products', 'nbill_vendor_id=' + document.getElementById('vendor_id').value + '&nbill_product_cat=' + this.value + '&nbill_name=cat_product_' + document.getElementById('vendor_id').value + '[]&nbill_attributes=<?php echo urlencode('multiple="multiple" size="7" id="cat_product_' . $vendor->id . '"'); ?>&nbill_echo_vendor=true&nbill_suppress_na=1&nbill_vendor_agnostic=<?php echo @$form_def['vendor_agnostic'] ? '1' : '0'; ?>', populate_prereq_products, true, null, 400);">
											    <?php
                                                    $vendors2 = $vendors;
                                                    foreach ($vendors2 as $vendor2)
                                                    {
                                                        if (!@$form_def['vendor_agnostic'] && $vendor2->id != $vendor->id)
                                                        {
                                                            continue;
                                                        }
												        foreach ($cats[$vendor2->id] as $cat)
												        {
													        echo "<option value=\"" . $cat['id'] . "\"";
													        if ($use_posted_values)
													        {
														        if (is_array(nbf_common::get_param($_POST,'category_' . $vendor2->id)))
														        {
															        if (array_search($cat['id'], nbf_common::get_param($_POST,'category_' . $vendor2->id)) !== false)
															        {
																        echo " selected=\"selected\"";
															        }
														        }
													        }
													        echo ">" . $cat['name'] . "</option>";
												        }
                                                    }
											    ?>
											    </select>
                                            </div>
                                        </div>
                                        <div class="list-container">
											<?php echo NBILL_FORM_PREREQ_CAT_PROD; ?><br />
                                            <div id="container_prereq_product_<?php echo $vendor->id; ?>">
                                                <div class="scrollable-multi-select">
											        <select multiple="multiple" size="7" id="cat_product_<?php echo $vendor->id; ?>" name="cat_product_<?php echo $vendor->id; ?>[]">
											        <?php
                                                    foreach ($vendors2 as $vendor2)
                                                    {
                                                        if (!@$form_def['vendor_agnostic'] && $vendor2->id != $vendor->id)
                                                        {
                                                            continue;
                                                        }
												        foreach ($products[$vendor2->id] as $product)
												        {
                                                            echo "<option value=\"" . $product->id . "\">" . $product->name . "</option>";
												        }
                                                    }
											        ?>
											        </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="list-container">
                                            <div class="list-selector-buttons">
											    <input type="button" class="button btn" name="select_product_<?php echo $vendor->id; ?>" id="select_product_<?php echo $vendor->id; ?>" value=" > " onclick="select_product(<?php echo $vendor->id; ?>);" title="<?php echo NBILL_SELECT; ?>" /><br />
											    <input type="button" class="button btn" name="deselect_product_<?php echo $vendor->id; ?>" id="deselect_product_<?php echo $vendor->id; ?>" value=" < " onclick="remove_product(<?php echo $vendor->id; ?>);" title="<?php echo NBILL_DESELECT; ?>" />
										    </div>
                                            <div class="list-selected-items">
											    <span style="white-space:nowrap;"><?php echo NBILL_FORM_PREREQ_SELECTED_PROD; ?></span><br />
                                                <div class="scrollable-multi-select">
											        <select multiple="multiple" size="7" id="cat_sel_product_<?php echo $vendor->id; ?>" name="cat_sel_product_<?php echo $vendor->id; ?>[]">
											        <?php
                                                    foreach ($vendors2 as $vendor2)
                                                    {
                                                        if (!@$form_def['vendor_agnostic'] && $vendor2->id != $vendor->id)
                                                        {
                                                            continue;
                                                        }
												        $selected_product_ids = array();
												        if ($use_posted_values)
												        {
													        $selected_product_ids = explode(',', nbf_common::get_param($_POST,'prerequisite_products_' . $vendor2->id));
												        }
												        else
												        {
													        if ($vendor2->id == $row->vendor_id || @$form_def['vendor_agnostic'])
													        {
														        $selected_product_ids = explode(',', $row->prerequisite_products);
													        }
												        }
												        foreach ($selected_product_ids as $product_id)
												        {
													        foreach ($selected_products[$vendor2->id] as $selected_product)
													        {
														        if ($selected_product->id == $product_id)
														        {
															        echo "<option value=\"$product_id\">" . $selected_product->name . "</option>";
														        break;
														        }
													        }
												        }
                                                    }
											        ?>
											        </select>
                                                </div>
											    <input type="hidden" name="prerequisite_products_<?php echo $vendor->id; ?>" id="prerequisite_products_<?php echo $vendor->id; ?>" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'prerequisite_products_' . $vendor->id) : $row->prerequisite_products; ?>" />
                                                <?php nbf_html::show_static_help(constant("NBILL_INSTR_FORM_PREREQ_PRODUCTS$lang_suffix"), "prerequisite_products_" . $vendor->id . "_help"); ?>
										    </div>
                                        </div>
							        </div>
							        <?php } ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("advanced_disqual_products", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_FORM_DISQUAL_PRODUCTS; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <?php
							        //Wizard style select list (categories, products, selected products)
							        foreach ($vendors as $vendor)
							        {
								        if ($vendor->id == $selected_vendor)
								        {
									        $visibility = "block";
								        }
								        else
								        {
									        $visibility = "none";
								        }
							        ?>
                                   <div id="disqual_<?php echo $vendor->id; ?>" style="display:<?php echo $visibility; ?>">
							            <div class="list-container">
											<?php echo NBILL_FORM_DISQUAL_CATS; ?><br />
                                            <div class="scrollable-multi-select">
                                                <select multiple="multiple" size="7" name="disqual_category_<?php echo $vendor->id; ?>[]" id="disqual_category_<?php echo $vendor->id; ?>" onchange="submit_ajax_request('get_products', 'nbill_vendor_id=' + document.getElementById('vendor_id').value + '&nbill_product_cat=' + this.value + '&nbill_name=cat_disqual_product_' + document.getElementById('vendor_id').value + '[]&nbill_attributes=<?php echo urlencode('multiple="multiple" size="7" id="cat_disqual_product_' . $vendor->id . '"'); ?>&nbill_echo_vendor=true&nbill_suppress_na=1&nbill_vendor_agnostic=<?php echo @$form_def['vendor_agnostic'] ? '1' : '0'; ?>', populate_disqual_products, true, null, 400);">
											    <?php
                                                foreach ($vendors2 as $vendor2)
                                                {
                                                    if (!@$form_def['vendor_agnostic'] && $vendor2->id != $vendor->id)
                                                    {
                                                        continue;
                                                    }
												    foreach ($cats[$vendor2->id] as $cat)
												    {
													    echo "<option value=\"" . $cat['id'] . "\"";
													    if ($use_posted_values)
													    {
														    if (is_array(nbf_common::get_param($_POST,'disqual_category_' . $vendor2->id)))
														    {
															    if (array_search($cat['id'], nbf_common::get_param($_POST,'disqual_category_' . $vendor2->id)) !== false)
															    {
																    echo " selected=\"selected\"";
															    }
														    }
													    }
													    echo ">" . $cat['name'] . "</option>";
												    }
                                                }
											    ?>
											    </select>
                                            </div>
										    <div class="list-container">
											    <?php echo NBILL_FORM_DISQUAL_CAT_PROD; ?><br />
											    <div id="container_disqual_product_<?php echo $vendor->id; ?>">
                                                    <div class="scrollable-multi-select">
                                                        <select multiple="multiple" size="7" id="cat_disqual_product_<?php echo $vendor->id; ?>" name="cat_disqual_product_<?php echo $vendor->id; ?>[]">
											            <?php
                                                        foreach ($vendors2 as $vendor2)
                                                        {
                                                            if (!@$form_def['vendor_agnostic'] && $vendor2->id != $vendor->id)
                                                            {
                                                                continue;
                                                            }
												            foreach ($disqual_products[$vendor2->id] as $product)
												            {
													            echo "<option value=\"" . $product->id . "\">" . $product->name . "</option>";
												            }
                                                        }
											            ?>
											            </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="list-container">
                                                <div class="list-selector-buttons">
											        <input type="button" class="button btn" name="select_disqual_product_<?php echo $vendor->id; ?>" id="select_disqual_product_<?php echo $vendor->id; ?>" value=" > " onclick="select_disqual_product(<?php echo $vendor->id; ?>);" title="<?php echo NBILL_SELECT; ?>" /><br />
											        <input type="button" class="button btn" name="deselect_disqual_product_<?php echo $vendor->id; ?>" id="deselect_disqual_product_<?php echo $vendor->id; ?>" value=" < " onclick="remove_disqual_product(<?php echo $vendor->id; ?>);" title="<?php echo NBILL_DESELECT; ?>" />
										        </div>
                                                <div class="list-selected-items">
											        <span style="white-space:nowrap;"><?php echo NBILL_FORM_DISQUAL_SELECTED_PROD; ?></span><br />
											        <div class="scrollable-multi-select">
                                                        <select multiple="multiple" size="7" id="cat_sel_disqual_product_<?php echo $vendor->id; ?>" name="cat_sel_disqual_product_<?php echo $vendor->id; ?>[]">
											            <?php
                                                        foreach ($vendors2 as $vendor2)
                                                        {
                                                            if (!@$form_def['vendor_agnostic'] && $vendor2->id != $vendor->id)
                                                            {
                                                                continue;
                                                            }
												            $selected_product_ids = array();
												            if ($use_posted_values)
												            {
													            $selected_product_ids = explode(',', nbf_common::get_param($_POST,'disqualifying_products_' . $vendor2->id));
												            }
												            else
												            {
													            if ($vendor2->id == $row->vendor_id)
													            {
														            $selected_product_ids = explode(',', $row->disqualifying_products);
													            }
												            }
												            foreach ($selected_product_ids as $product_id)
												            {
													            foreach ($selected_disqual_products[$vendor2->id] as $selected_product)
													            {
														            if ($selected_product->id == $product_id)
														            {
															            echo "<option value=\"$product_id\">" . $selected_product->name . "</option>";
														            break;
														            }
													            }
												            }
                                                        }
											            ?>
											            </select>
                                                    </div>
											        <input type="hidden" name="disqualifying_products_<?php echo $vendor->id; ?>" id="disqualifying_products_<?php echo $vendor->id; ?>" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'disqualifying_products_' . $vendor->id) : $row->disqualifying_products; ?>" />
                                                    <?php nbf_html::show_static_help(constant("NBILL_INSTR_FORM_DISQUAL_PRODUCTS$lang_suffix"), "disqualifying_products_" . $vendor->id . "_help"); ?>
										        </div>
								            </div>
							            </div>
							        <?php } ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("advanced_process_code", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_FORM_PROCESS_CODE; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <textarea name="process_code" id="process_code" class="code"><?php echo $use_posted_values ? stripslashes(@$_POST['process_code']) : $row->process_code; ?></textarea>
                                    <?php nbf_html::show_static_help(NBILL_INSTR_FORM_PROCESS_CODE, "process_code_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("advanced_validation_code", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_FORM_VALIDATION_CODE; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <textarea name="validation_code" id="validation_code" class="code"><?php echo $use_posted_values ? stripslashes(@$_POST['validation_code']) : $row->validation_code; ?></textarea>
                                    <?php nbf_html::show_static_help(NBILL_INSTR_FORM_VALIDATION_CODE, "validation_code_help"); ?>
						        </td>
					        </tr>
					        <?php }
                            if (array_search("advanced_pre_calculate_code", $form_def['suppressed']) === false) { ?>
                            <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_ORDER_FORM_PRE_CALCULATE; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <textarea name="pre_calculate_code" id="pre_calculate_code" class="code"><?php echo $use_posted_values ? stripslashes(@$_POST['pre_calculate_code']) : $row->pre_calculate_code; ?></textarea>
                                    <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_FORM_PRE_CALCULATE, "pre_calculate_code_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("advanced_post_calculate_code", $form_def['suppressed']) === false) { ?>
                            <tr>
                                <td class="nbill-setting-caption">
                                    <?php echo NBILL_ORDER_FORM_POST_CALCULATE; ?>
                                </td>
                                <td class="nbill-setting-value">
                                    <textarea name="post_calculate_code" id="post_calculate_code" class="code"><?php echo $use_posted_values ? stripslashes(@$_POST['post_calculate_code']) : $row->post_calculate_code; ?></textarea>
                                    <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_FORM_POST_CALCULATE, "post_calculate_code_help"); ?>
                                </td>
                            </tr>
                            <?php }
                            if (array_search("advanced_submit_code", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_FORM_SUBMIT_CODE; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <textarea name="submit_code" id="submit_code" class="code"><?php echo $use_posted_values ? stripslashes(@$_POST['submit_code']) : $row->submit_code; ?></textarea>
                                    <?php nbf_html::show_static_help(NBILL_INSTR_FORM_SUBMIT_CODE, "submit_code_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("advanced_order_creation_code", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_ORDER_CREATION_CODE; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <textarea name="order_creation_code" id="order_creation_code" class="code"><?php echo $use_posted_values ? stripslashes(@$_POST['order_creation_code']) : $row->order_creation_code; ?></textarea>
                                    <?php nbf_html::show_static_help(NBILL_INSTR_ORDER_CREATION_CODE, "order_creation_code_help"); ?>
						        </td>
					        </tr>
                            <?php }
                            if (array_search("advanced_after_processing_code", $form_def['suppressed']) === false) { ?>
                            <tr>
                                <td class="nbill-setting-caption">
                                    <?php echo NBILL_FORM_POST_PROCESS_CODE; ?>
                                </td>
                                <td class="nbill-setting-value">
                                    <textarea name="after_processing_code" id="after_processing_code" class="code"><?php echo $use_posted_values ? stripslashes(@$_POST['after_processing_code']) : $row->after_processing_code; ?></textarea>
                                    <?php nbf_html::show_static_help(NBILL_INSTR_FORM_POST_PROCESS_CODE, "after_processing_code_help"); ?>
                                </td>
                            </tr>
                            <?php }
                            if (array_search("advanced_javascript_functions", $form_def['suppressed']) === false) { ?>
					        <tr>
						        <td class="nbill-setting-caption">
							        <?php echo NBILL_JAVASCRIPT_FUNCTIONS; ?>
						        </td>
						        <td class="nbill-setting-value">
							        <textarea name="javascript_functions" id="javascript_functions" class="code"><?php echo $use_posted_values ? stripslashes($_POST['javascript_functions']) : $row->javascript_functions; ?></textarea>
                                    <?php nbf_html::show_static_help(NBILL_INSTR_JAVASCRIPT_FUNCTIONS, "javascript_functions_help"); ?>
						        </td>
					        </tr>
                            <?php } ?>
				        </table>
				        <?php
				        $nbf_tab_form->add_tab_content("advanced", ob_get_clean());
                    }

                    if (@$form_def['custom_settings_tab_title'])
                    {
                        if ($custom_settings && count($custom_settings) > 0)
                        {
                            ob_start();
                            if (@$form_def['custom_settings_intro'])
                            {
                                echo "<p>" . $form_def['custom_settings_intro'] . "</p>";
                            }
                            ?>
                            <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
                            <?php
                            foreach ($custom_settings as $custom_setting)
                            {
                                ?>
                                <tr>
                                    <td class="nbill-setting-caption">
                                        <?php echo $custom_setting['caption']; ?>
                                    </td>
                                    <td class="nbill-setting-value">
                                        <?php
                                        switch ($custom_setting['field_type'])
                                        {
                                            case "BB": //Dropdown List
                                                $selected_item = $use_posted_values ? nbf_common::get_param($_POST, $custom_setting['name']) : ($row->id ? nbf_common::get_param($_REQUEST, $custom_setting['name']) : $custom_setting['default_value']);
                                                echo nbf_html::select_list($custom_setting['options'], $custom_setting['name'], 'id="' . $custom_setting['id'] . '" class="inputbox"', $selected_item);
                                                break;
                                            case "DD": //Option List
                                                $selected_item = $use_posted_values ? nbf_common::get_param($_POST, $custom_setting['name']) : ($row->id ? nbf_common::get_param($_REQUEST, $custom_setting['name']) : $custom_setting['default_value']);
                                                echo nbf_html::radio_list($custom_setting['options'], $custom_setting['name'], $selected_item, true);
                                                break;
                                            default:
                                                //Text box
                                                ?>
                                                <input type="text" name="<?php echo $custom_setting['name']; ?>" id="<?php echo $custom_setting['id']; ?>" value="<?php echo $use_posted_values ? stripslashes(nbf_common::get_param($_POST, $custom_setting['name'])) : ($row->id ? nbf_common::get_param($_REQUEST, $custom_setting['name']) : $custom_setting['default_value']); ?>" class="inputbox" style="width:200px" />
                                                <?php
                                                break;
                                        }
                                        ?>
                                        <?php nbf_html::show_static_help($custom_setting['description'], $custom_setting['name'] . "_help"); ?>
                                    </td>
                                </tr>
                                <?php
                            } ?>
                            </table>
                            <?php
                            $nbf_tab_form->add_tab_content("custom_" . $form_def['action'] . "_" . @$form_def['sub_action'], ob_get_clean());
                        }
                    }

                    $nbf_tab_form->end_tab_group();
				    ?>
			    </td>
		    </tr>
		    </table>
        </div>

		</form>
        </div>

		<script type="text/javascript">
		refresh_vendor();
		</script>
		<?php
	}
}