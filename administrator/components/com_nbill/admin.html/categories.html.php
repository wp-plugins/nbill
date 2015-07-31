<?php
/**
* HTML output for product categories
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillCategories
{
	public static function showCategories($rows, $pagination, $vendors)
	{
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		}
		?>
		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="categories" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

        <table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "categories"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_CATEGORIES_TITLE; ?>
			</th>
		</tr>
		</table>

		<?php
			//Display filter dropdown if multi-company
			if (count($vendors) > 1)
			{
				echo "<p style=\"clear:both;\">" . NBILL_VENDOR_NAME . "&nbsp;";
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
				echo nbf_html::select_list($vendor_name, "vendor_filter", 'id="vendor_filter" class="inputbox" onchange="document.adminForm.submit();"', $selected_filter );
				echo "</p>";
			}
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
				    <?php echo NBILL_CATEGORY_NAME; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_CATEGORY_DESCRIPTION; ?>
			    </th>
			    <th class="title" colspan="2">
				    <?php echo NBILL_CATEGORY_ORDERING; ?>
			    </th>
			    <?php
				    //Only show vendor name if more than one listed
				    if (count($vendors) > 1 && $selected_filter == -999)
				    {?>
					    <th class="title">
						    <?php echo NBILL_VENDOR_NAME; ?>
					    </th>
				    <?php }
			    ?>
		    </tr>
		    <?php
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];
				    $link = nbf_cms::$interop->admin_page_prefix . "&action=categories&task=edit&cid=" . $row['id'];
				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
                    $checked = nbf_html::id_checkbox($i, $row['id']);
				    echo "</td><td class=\"selector\">$checked</td>";
				    echo "<td class=\"list-value\"><a href=\"$link\" title=\"" . NBILL_EDIT_CATEGORY . "\">" . $row['name'] . "</a></td>";
				    echo "<td class=\"list-value\">" . ($row['description'] ? $row['description'] : '&nbsp;') . "</td>";
				    echo "<td class=\"selector\">";
                    echo $pagination->order_up_arrow($i, !$row['is_first'], NBILL_MOVE_UP);
				    echo "</td><td class=\"selector\">";
				    echo $pagination->order_down_arrow($i, $n, !$row['is_last'], NBILL_MOVE_DOWN);
				    echo "</td>";

				    //Only show vendor name if more than one listed
				    $vendor_col = false;
				    if (count($vendors) > 1 && $selected_filter == -999)
				    {
					    foreach ($vendors as $vendor)
					    {
						    if ($vendor->id == $row['vendor_id'])
						    {
							    echo "<td class=\"list-value\">" . $vendor->vendor_name . "</td>";
							    $vendor_col = true;
							    break;
						    }
					    }
				    }
				    echo "</tr>";
			    }
		    ?>
		    </table>
        </div>

		</form>
		<?php
	}

	/**
	* Edit a category (or create a new one)
	*/
	public static function editCategory($cat_id, $row, $vendors, $parent_cats)
	{
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		}
		?>
		<script language="javascript" type="text/javascript">
		function nbill_submit_task(task_name)
        {
			var form = document.adminForm;
			if (task_name == 'cancel')
            {
				document.adminForm.task.value=task_name;
                document.adminForm.submit();
				return;
			}

			//Validate fields
			if (form.name.value == "")
			{
				alert('<?php echo NBILL_CATEGORY_NAME_REQUIRED; ?>');
			}
			else
			{
				document.adminForm.task.value=task_name;
                document.adminForm.submit();
			}
		}
		function refresh_vendor()
		{
			//Show the appropriate nominal ledger codes depending on selected vendor
			var vendor_id = document.getElementById('vendor_id').value;
			<?php
			foreach ($vendors as $vendor)
			{
				echo "document.getElementById('category_" . $vendor->id . "').style.display = 'none';";
			}
			?>
			document.getElementById('category_' + vendor_id).style.display = 'inline';
		}
		</script>
		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="ordering" value="<?php echo $row->ordering; ?>" />
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="categories" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $cat_id;?>" />
		<input type="hidden" name="old_parent_id" value="<?php echo @$row->parent_id; //So we know to update ordering if it changes ?>" />
		<?php nbf_html::add_filters(); ?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "categories"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php echo $row->id ? NBILL_EDIT_CATEGORY . " '" . $row->name . "'" : NBILL_NEW_CATEGORY; ?>
			</th>
		</tr>
		</table>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-categories">
		    <tr>
			    <th colspan="2"><?php echo NBILL_CATEGORY_DETAILS; ?></th>
		    </tr>
		    <?php
			    if (count($vendors) > 1)
			    {?>
				    <tr id="nbill-admin-tr-vendor-name">
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
							    echo nbf_html::select_list($vendor_name, "vendor_id", 'id="vendor_id" class="inputbox" onchange="refresh_vendor();"', $selected_vendor);
						    ?>
                            <?php nbf_html::show_static_help(NBILL_INSTR_VENDOR_ID, "vendor_id_help"); ?>
					    </td>
				    </tr>
			    <?php }
			    else
			    {
				    echo "<input type=\"hidden\" name=\"vendor_id\" id=\"vendor_id\" value=\"" . $vendors[0]->id . "\" />";
				    $_POST['vendor_id'] = $vendors[0]->id;
                    $selected_vendor = $vendors[0]->id;
			    }
		    ?>
		    <tr id="nbill-admin-tr-category-name">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CATEGORY_NAME; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="name" id="name" value="<?php echo str_replace("\"", "&quot;", $row->name); ?>" class="inputbox" style="width:160px" />
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-category-desc">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CATEGORY_DESCRIPTION; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="description" value="<?php echo str_replace("\"", "&quot;", $row->description); ?>" />
			    </td>
		    </tr>
		    <tr id="nbill-admin-tr-category-parent">
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_CATEGORY_PARENT; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					    //Create a dropdown of parent categories for each vendor - show/hide via javascript depending on vendor selected
					    foreach ($vendors as $vendor)
					    {
						    $cat_list = array();
						    foreach ($parent_cats[$vendor->id] as $cat_item)
						    {
							    $cat_list[] = nbf_html::list_option($cat_item['id'], $cat_item['name']);
						    }
						    if($row->id)
						    {
							    $selected_cat = $row->parent_id;
						    }
						    else
						    {
							    $selected_cat = 0;
						    }
						    $attributes = 'class="inputbox" id="category_' . $vendor->id . '"';
						    if ($row->id && ($row->parent_id == null || $row->parent_id == -1 || $row->parent_id == 0))
						    {
							    $attributes .= " disabled=\"disabled\"";
						    }
						    echo nbf_html::select_list($cat_list, "category_" . $vendor->id, $attributes, $selected_cat);
					    }
				    ?>
			    </td>
		    </tr>
            <!-- Custom Fields Placeholder -->
		    </table>
        </div>

		</form>
		<script type="text/javascript">
			refresh_vendor();
		</script>
		<?php
	}
}