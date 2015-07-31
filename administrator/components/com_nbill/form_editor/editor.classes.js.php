<?php
/**
* Output for order form editor procedural javascript
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control.base.php");
?>
<script type="text/javascript">
function nbill_form(id)
{
	this.id = id;
	this.show_login = true;
	this.pages = new Object();
	this.fields = new Object();
	this.canvas_width = 750;
	this.canvas_height = 650;
	this.snap_to_grid = true;
	this.grid_size = 5;
	this.merge_by_default = false;
}
function nbill_form_page(page_no)
{
	this.page_no = page_no;
	this.label_width = 250;
	this.published = 1;
	this.close_gaps = true;
	this.min_width = 750;
    this.intro = '';
    this.footer = '';
    this.onload = '';
    this.external_js_files = '';
    this.page_submit_code = '';
    this.auto_tab = true;
    this.renderer = 2;
}
function nbill_form_field(id)
{
	this.id = id;
    this.form_id = '';
	this.page_no = 1;
	this.x_pos = 0;
	this.y_pos = 0;
	this.z_pos = 0;
	this.ordering = 0;
	this.field_type = 'AA'; //Textbox
	this.published = 1;
	this.name = '';
	this.label = '<?php echo NBILL_FORM_FIELD_DEFAULT_LABEL; ?>'.replace('%s', id);
	this.checkbox_text = null;
    this.show_label_by_default = true;
    this.override_absolute = false;
	this.horizontal_options = false;
	this.merge_columns = false;
	this.default_value = null;
	this.required = false;
	this.help_text = '';
	this.confirmation = false;
	this.show_on_summary = 2;
	this.attributes = null;
	this.pre_field = '';
	this.post_field = '';
	this.xref = '';
	this.xref_sql = '';
	this.related_product_cat = 0;
	this.related_product = 0;
	this.related_product_quantity = 1;
    this.override_freq = 0;
    this.order_value = null;
	this.value_required_for_order = '';
	this.entity_mapping = '';
	this.contact_mapping = '';
	this.support_options = false;
	this.html_control_type = 'AA';
	this.height_allowance = <?php echo intval(nbf_field_control::$default_height_allowance); ?>;
	this.option_height_allowance = 0;
	this.options = new Object();
    this.extended_params = null;
}
function nbill_form_field_option(id)
{
	this.id = id;
	this.code = '';
	this.description = '';
	this.ordering = 0;
	this.related_product_cat = 0;
	this.related_product = 0;
	this.related_product_quantity = 1;
}
</script>