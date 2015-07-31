<?php
/**
* Server-side processing for order form editor AJAX functions
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');



function show_field_options()
{
    if (!headers_sent())
    {
        header('Content-Encoding: text/html'); //No gzip
    }
	include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.category.class.php");
	nbf_common::load_language("form.editor");

    $no_products = nbf_common::get_param($_REQUEST, 'nbill_no_products');
	$vendor_id = intval(nbf_common::get_param($_REQUEST, 'nbill_vendor_id'));
	$options = urldecode(nbf_common::get_param($_REQUEST,'nbill_field_options', null, true, false, true));
	$options = @unserialize($options);
	if (!$options)
	{
		$options = array();
	}
	$field_id = nbf_common::get_param($_REQUEST, 'nbill_field_id');
    if (!$no_products)
    {
	    $cats = nbf_category_hierarchy::get_category_hierarchy($vendor_id);
	    $cat_list = array();
	    $cat_list[] = nbf_html::list_option("-1", NBILL_NOT_APPLICABLE);
	    foreach ($cats as $cat)
	    {
		    $cat_list[] = nbf_html::list_option($cat['id'], $cat['name']);
	    }
    }
	?>
	<h3><?php echo NBILL_FORM_FIELD_EDIT_OPTIONS; if (nbf_common::nb_strlen(@$_POST['name']) > 0) {echo ' (' . $_POST['name'] . ')';}?></h3>
	<p><?php echo NBILL_FORM_FIELD_OPTIONS_INTRO; ?></p>
	<table cellpadding="3" cellspacing="0" border="0" width="98%" class="nbill-admin-form" style="width:98%">
		<tr>
			<th>&nbsp;</th>
			<th valign="bottom"><?php echo NBILL_OPTION_VALUE; echo '&nbsp;'; nbf_html::show_overlib(NBILL_OPTION_VALUE_HELP); //<a href="#" onmouseover="return nbill_overlib(\'' . NBILL_OPTION_VALUE_HELP . '\');" onmouseout="return nbill_overlib_nd();" ><img src="' . nbf_cms::$interop->nbill_site_url_path . '/images/info.png" border="0" /></a>&nbsp;'; ?></th>
			<th valign="bottom" style="width:100%"><?php echo NBILL_OPTION_DESCRIPTION; echo '&nbsp;'; nbf_html::show_overlib(NBILL_OPTION_DESCRIPTION_HELP); //<a href="#" onmouseover="return nbill_overlib(\'' . NBILL_OPTION_DESCRIPTION_HELP . '\');" onmouseout="return nbill_overlib_nd();" ><img src="' . nbf_cms::$interop->live_site . '/components/com_nbill/images/info.png" border="0" /></a>&nbsp;';?></th>
			<th valign="bottom" align="center" style="text-align:center;white-space:nowrap;"><?php echo NBILL_OPTION_ORDERING; ?></th>
			<?php if (!$no_products) { ?>
            <th valign="bottom" colspan="2"><?php echo NBILL_OPTION_PRODUCT; echo '&nbsp;'; nbf_html::show_overlib(NBILL_OPTION_PRODUCT_HELP); //<a href="#" onmouseover="return nbill_overlib(\'' . NBILL_OPTION_PRODUCT_HELP . '\');" onmouseout="return nbill_overlib_nd();" ><img src="' . nbf_cms::$interop->nbill_site_url_path . '/images/info.png" border="0" /></a>&nbsp;';?></th>
			<th valign="bottom"><?php echo NBILL_OPTION_QUANTITY; echo '&nbsp;'; nbf_html::show_overlib(NBILL_OPTION_QUANTITY_HELP); //<a href="#" onmouseover="return nbill_overlib(\'' . NBILL_OPTION_QUANTITY_HELP . '\');" onmouseout="return nbill_overlib_nd();" ><img src="' . nbf_cms::$interop->nbill_site_url_path . '/images/info.png" border="0" /></a>&nbsp;';?></th>
            <?php } ?>
			<th>&nbsp;</th>
		</tr>
		<?php
		//Sort options into order
		usort($options, 'compare_options');
		$i = 0;
		foreach ($options as $option)
		{
			$i++;
			?>
			<tr id="tr_option_<?php echo $i; ?>">
				<td style="white-space:nowrap;"><?php echo NBILL_OPTION; ?> <?php echo $i; ?><input type="hidden" id="option_id_<?php echo $i; ?>" value="<?php echo $option['id']; ?>" /></td>
				<td><input type="text" id="option_<?php echo $i; ?>_value" value="<?php echo $option['code']; ?>" style="width:60px" /></td>
				<td><input type="text" id="option_<?php echo $i; ?>_description" value="<?php echo $option['description']; ?>" style="width:100%" /></td>
				<td><input type="text" id="option_<?php echo $i; ?>_ordering" value="<?php echo $i; ?>" style="width:30px;" /></td>
			    <?php if (!$no_products) { ?>
                <td><?php echo nbf_html::select_list($cat_list, "option_" . $i . "_related_product_cat", "id=\"option_" . $i . "_related_product_cat\" onclick=\"window.opener.submit_ajax_request('get_products', 'nbill_product_cat=' + this.value + '&nbill_vendor_id=" . $vendor_id . "&nbill_selected_product=' + document.getElementById('option_" . $i . "_related_product').value + '&nbill_name=option_" . $i . "_related_product&nbill_attributes=" . urlencode('id="option_' . $i . '_related_product"') . "', function(new_product_list){document.getElementById('product_list_" . $i . "').innerHTML=new_product_list;});\"", $option['related_product_cat'] ? $option['related_product_cat'] : '-1'); ?></td>
				<td><div id="product_list_<?php echo $i; ?>"><?php get_products($option['related_product_cat'] ? $option['related_product_cat'] : '-1', $option['related_product'], 'option_' . $i . '_related_product', urlencode('id="option_' . $i . '_related_product"')); ?></div></td>
				<td><input type="text" id="option_<?php echo $i; ?>_quantity" value="<?php echo $option['related_product_quantity']; ?>" style="width:50px;" /></td>
                <?php } ?>
				<td><input type="button" id="delete_<?php echo $i; ?>" value="<?php echo NBILL_OPTION_DELETE; ?>" onclick="document.getElementById('tr_option_<?php echo $i; ?>').style.display='none';" /></td>
			</tr>
			<?php
		}
		?>
		<tr class="nbill_new_option">
			<td style="white-space:nowrap;"><?php echo NBILL_NEW_OPTION; ?></td>
			<td><input type="text" id="option_new_value" value="" style="width:60px" /></td>
			<td><input type="text" id="option_new_description" value="" style="width:100%" /></td>
			<td>&nbsp;</td>
            <?php if (!$no_products) { ?>
			<td><?php echo nbf_html::select_list($cat_list, "option_new_related_product_cat", "id=\"option_new_related_product_cat\" onclick=\"window.opener.submit_ajax_request('get_products', 'nbill_product_cat=' + this.value + '&nbill_vendor_id=" . $vendor_id . "&nbill_selected_product=' + document.getElementById('option_new_related_product').value + '&nbill_name=option_new_related_product&nbill_attributes=" . urlencode('id="option_new_related_product"') . "', function(new_product_list){document.getElementById('product_list_new').innerHTML=new_product_list;});\"", "-1"); ?></td>
			<td><div id="product_list_new"><?php get_products(-1, null, 'option_new_related_product', urlencode('id="option_new_related_product"'));	?></div></td>
			<td><input type="text" id="option_new_quantity" value="" style="width:50px;" /></td>
            <?php } ?>
			<td><input type="button" id="add_new" value="<?php echo NBILL_OPTION_ADD_NEW; ?>" onclick="if (document.getElementById('option_new_description').value.length==0){alert('<?php echo NBILL_FIELD_OPTION_ENTER_DESCRIPTION ?>');}else{window.opener.add_new_option(document<?php if ($field_id !== '-1') { ?>, '<?php echo $field_id; ?>'<?php } ?>);}" /></td>
		</tr>
		<tr class="nbill_option_spacer">
			<td colspan="8">&nbsp;</td>
		</tr>
		<tr class="nbill_option_actions">
			<td colspan="8" align="right">
				<input type="button" id="btn_submit" value="<?php echo NBILL_SUBMIT; ?>" onclick="var opts=new Array();var dups=false;for(var i=1;i<=<?php echo $i; ?>;i++){var this_row=document.getElementById('tr_option_' + i);var this_obj=document.getElementById('option_' + i + '_value');var this_val=this_obj.value;if(this_row.style.display!='none'){for(var j=0;j<opts.length;j++){if(opts[j]==this_val){dups=true;break;}}opts[i]=this_val;}}if(!dups || confirm('<?php echo NBILL_OPTIONS_WARN_DUPLICATE_VALUES; ?>')){window.opener.save_options(document<?php if ($field_id !== '-1') { ?>, '<?php echo $field_id; ?>'<?php } ?>);window.close();}" />
				<input type="button" id="btn_cancel" value="<?php echo NBILL_CANCEL; ?>" onclick="window.close();" />
			</td>
		</tr>
	</table>
	<?php
}

/**
* Comparer for sorting options into order based on 'ordering' element
* @param mixed $a First element to compare
* @param mixed $b Second element to compare
* @return mixed 0=Equal, 1=a>b, -1=b>a
*/
function compare_options($a, $b)
{
    if ($a['ordering'] == $b['ordering'])
    {
        return 0;
    }
    return ($a['ordering'] > $b['ordering']) ? +1 : -1;
}

