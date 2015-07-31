<?php
/**
* HTML output for profile field editor
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillProfileFields
{
	public static function showProfileFields($rows, $pagination)
	{
        ?>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "profile_fields"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_PROFILE_FIELDS_TITLE; ?>
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
        <input type="hidden" name="action" value="profile_fields" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
        <input type="hidden" name="apply_to_existing" id="apply_to_existing" value="" />

		<p align="left"><?php echo NBILL_PROFILE_FIELDS_INTRO; ?></p>

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
				    <?php echo NBILL_FORM_FIELD_NAME; ?>
			    </th>
			    <th class="selector">
				    <?php echo NBILL_PROFILE_FIELD_PUBLISHED; ?>
			    </th>
			    <th class="title responsive-cell optional">
				    <?php echo NBILL_FORM_FIELD_TYPE; ?>
			    </th>
                <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
                <th class="title responsive-cell wide-only">
                    <?php echo NBILL_PROFILE_FIELD_IN_USE; ?>&nbsp;<?php nbf_html::show_overlib(NBILL_PROFILE_FIELD_IN_USE_HELP); ?>
                </th>
                <?php } ?>
			    <th class="title responsive-cell wide-only">
				    <?php echo NBILL_FORM_FIELD_LABEL; ?>
			    </th>
			    <th class="selector">
				    <?php echo NBILL_PROFILE_FIELD_REQUIRED; ?>
			    </th>
			    <th class="selector" colspan="2">
				    <?php echo NBILL_PROFILE_FIELD_ORDERING; ?>
			    </th>
		    </tr>
		    <?php
		    for ($i=0, $n = count($rows); $i < $n; $i++)
		    {
			    $row = &$rows[$i];
			    $link = nbf_cms::$interop->admin_page_prefix . "&action=profile_fields&task=edit&cid=$row->id";

			    $img = $row->published ? 'tick.png' : 'cross.png';
			    $task = $row->published ? 'unpublish' : 'publish';
			    $alt = $row->published ? NBILL_PROFILE_FIELD_PUBLISHED_YES : NBILL_PROFILE_FIELD_PUBLISHED_NO;

			    $img_reqd = $row->required ? 'tick.png' : 'cross.png';
			    $task_reqd = $row->required ? 'not_required' : 'required';
			    $alt_reqd = $row->required ? NBILL_PROFILE_FIELD_REQUIRED : NBILL_PROFILE_FIELD_NOT_REQUIRED;

                

			    $checked = nbf_html::id_checkbox($i, $row->id);

			    echo "<tr>";
			    echo "<td class=\"selector\">";
			    echo $row->id;
			    $checked = nbf_html::id_checkbox($i, $row->id);
			    echo "</td><td class=\"selector\">$checked</td>";
			    echo "<td class=\"list-value word-breakable\"><a href=\"$link\" title=\"" . NBILL_EDIT_PROFILE_FIELD . "\">" . str_replace('NBILL_CORE_', '<span class="responsive-cell optional">NBILL_CORE_</span>', $row->name) . "</a></td>";
			    echo "<td class=\"selector\">";
			    echo "<a href=\"#\" onclick=\"" . ($row->in_use ? "if (confirm('" . NBILL_PROFILE_FIELD_UPDATE_FORMS . "')){document.getElementById('apply_to_existing').value='1';}" : "") . "for(var i=0; i<" . count($rows) . ";i++) {document.getElementById('cb' + i).checked=false};document.getElementById('cb$i').checked=true;document.adminForm.task.value='$task';document.adminForm.submit();return false;\">";
			    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/$img\" border=\"0\" alt=\"$alt\" />";
			    echo "</a></td>";
			    echo "<td class=\"list-value responsive-cell optional\">" . (defined($row->field_type_description) ? constant($row->field_type_description) : $row->field_type_description) . "</td>";
                
                echo "<td class=\"list-value responsive-cell wide-only\">" . (defined(str_replace("* ", "", $row->label)) ? (nbf_common::nb_strpos($row->label, "* ") !== false ? "* " : "") . constant(str_replace("* ", "", $row->label)) : $row->label) . "</td>";
			    echo "<td class=\"selector\">";
			    echo "<a href=\"#\" onclick=\"" . ($row->in_use ? "if (confirm('" . NBILL_PROFILE_FIELD_UPDATE_FORMS . "')){document.getElementById('apply_to_existing').value='1';}" : "") . "for(var i=0; i<" . count($rows) . ";i++) {document.getElementById('cb' + i).checked=false};document.getElementById('cb$i').checked=true;document.adminForm.task.value='$task_reqd';document.adminForm.submit();return false;\">";
			    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/$img_reqd\" border=\"0\" alt=\"$alt_reqd\" />";
			    echo "</a></td>";
			    echo "<td class=\"selector\">";
			    echo $pagination->order_up_arrow($i);
			    echo "</td><td class=\"selector\">";
			    echo $pagination->order_down_arrow($i, $n);
			    echo "</td>";
			    echo "</tr>";
		    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="10" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		</form>
		<?php
	}

	public static function editProfileField($field_id, $row, $field_types, $xref_tables, $entity_map, $contact_map, $options, $in_use)
	{
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.client.php");
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/editor.classes.js.php");
		?>
		<script language="javascript" type="text/javascript">
		<?php nbf_html::add_js_validation_numeric(); ?>

        function validate_field_name(name)
        {
            var valid = true;
            if (name.length == 0)
            {
                alert('<?php echo NBILL_FIELD_NAME_MANDATORY; ?>');
                valid = false;
            }
            else if (!isNaN(parseInt(name.substr(0,1))))
            {
                alert('<?php echo NBILL_FIELD_NAME_ALPHA_FIRST; ?>');
                valid = false;
            }
            else if (name.indexOf(' ') > -1)
            {
                alert('<?php echo NBILL_FIELD_NAME_NO_SPACES; ?>');
                valid = false;
            }
            else if (!/^[\w-]+$/i.test(name))
            {
                alert('<?php echo NBILL_FIELD_NAME_ALPHANUM; ?>');
                valid = false;
            }
            else if (name.indexOf('next_') == 0 || name.indexOf('prev_') == 0)
            {
                alert('<?php echo NBILL_FIELD_NAME_PREV_NEXT; ?>');
                valid = false;
            }
            else
            {
                //Check for reserved words
                switch (name.toLowerCase())
                {
                    <?php foreach (nbf_cms::$interop->get_reserved_words() as $reserved)
                    { ?>
                    case '<?php echo $reserved; ?>':
                    <?php }?>
                        alert('<?php echo NBILL_ERR_FLD_NAME_IS_RESERVED_WORD; ?>'.replace('%s', name));
                        valid = false;
                        break;
                }
            }
            return valid;
        }

		function nbill_submit_task(task_name)
        {
			var form = document.adminForm;
			if (task_name == 'cancel')
            {
				form.task.value=task_name;
                form.submit();
				return;
			}

			if (validate_field_name(document.getElementById('name').value))
			{
                <?php if ($field_id && $in_use)
                { ?>
                if (confirm('<?php echo NBILL_PROFILE_FIELD_UPDATE_FORMS; ?>'))
                {
                    document.getElementById('apply_to_existing').value = '1';
                }
                <?php } ?>
                document.getElementById('serialized_options').value = serialize(field_options);
                form.task.value=task_name;
                form.submit();
			}
		}

        <?php
        include_once(nbf_cms::$interop->nbill_fe_base_path . "/js/serialize.js");
        ?>

        function get_assoc_array_length(assoc_array)
        {
            var count = 0;
            for (var element in assoc_array)
            {
                count++;
            }
            return count;
        }

        function clone_object(orig)
        {
            if(orig == null || typeof(orig) != 'object')
            {
                return orig;
            }
            var cloned = new orig.constructor();
            for(var key in orig)
            {
                cloned[key] = clone_object(orig[key]);
            }
            return cloned;
        }

        var output = '';
        var options_window = null;
        function show_field_options()
        {
            var popW = 800, popH = 400;
            var leftPos = (screen.availWidth - popW) / 2;
            var topPos = (screen.availHeight - popH) / 2;
            options_window = window.open('', 'field_options_<?php echo nbf_common::nb_time(); ?>', 'width=' + popW + ',height=' + popH + ',top=' + topPos + ',left=' + leftPos + ',toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no');
            options_window.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><h' + 'tml xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" dir="ltr">');
            options_window.document.write('<h' + 'ead><meta http-equiv="content-type" content="text/html; charset=<?php echo nbf_cms::$interop->char_encoding; ?>" /><link rel="stylesheet" href="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/style/admin/home.css" type="text/css" /><link rel="stylesheet" href="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/style/admin/form_editor.css" type="text/css" />');
            options_window.document.write('<script type="text/javascript" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/js/nbill_overlib_mini.js"></' + 'script>');
            options_window.document.write('<t' + 'itle><?php echo NBILL_FORM_FIELD_EDIT_OPTIONS; ?> (' + document.getElementById('name').value + ')</t' + 'itle></h' + 'ead>');
            options_window.document.write('<b' + 'ody onload="document.getElementById(\'nbill_field_options_container\').innerHTML = \'<?php echo NBILL_OPTIONS_LOADING; ?>\';">');
            options_window.document.write('<div id="nbill_field_options_container"></');
            options_window.document.write('div></b' + 'ody></html>');
            options_window.document.close();

            if (options_window.focus) {options_window.focus();}

            temp_options = clone_object(field_options);
            output = submit_sjax_request('show_field_options', 'nbill_field_id=-1&nbill_no_products=1&nbill_field_options=' + encodeURIComponent(serialize(temp_options)), false);
            setTimeout('show_output()', 500);
        }

        function show_output()
        {
            options_window.document.getElementById('nbill_field_options_container').innerHTML = output;
            output = '';
            options_window = null;
        }

        function save_options(window_document, return_value)
        {
            var nbill_options = new Object();
            var trs = window_document.getElementsByTagName('tr');

            //First, close any gaps in ordering - must go from 1 to number of items
            var j = 0;
            var orderings = new Array();
            for (var i = 0; i < trs.length; i++)
            {
                tr = trs[i];
                if (tr.getAttribute('id') != null && tr.getAttribute('id').indexOf('tr_option_') > -1)
                {
                    if (!tr.style.display || tr.style.display != 'none')
                    {
                        var option_no = tr.id.substr(10);
                        orderings[j] = parseInt(window_document.getElementById('option_' + option_no + '_ordering').value);
                        j++;
                    }
                }
            }
            orderings.sort(function(a,b){return a - b});

            //Now save the items
            for (var i = 0; i < trs.length; i++)
            {
                tr = trs[i];
                if (tr.getAttribute('id') != null && tr.getAttribute('id').indexOf('tr_option_') > -1)
                {
                    if (!tr.style.display || tr.style.display != 'none')
                    {
                        var option_no = tr.id.substr(10);
                        var nbill_option = new nbill_form_field_option(window_document.getElementById('option_id_' + option_no).value);
                        nbill_option.code = window_document.getElementById('option_' + option_no + '_value').value;
                        nbill_option.description = window_document.getElementById('option_' + option_no + '_description').value;
                        for (j=0; j<orderings.length; j++)
                        {
                            if (orderings[j] == window_document.getElementById('option_' + option_no + '_ordering').value)
                            {
                                nbill_option.ordering = j + 1;
                                break;
                            }
                        }
                        nbill_options[nbill_option.ordering - 1] = nbill_option;
                    }
                }
            }
            if (return_value)
            {
                return nbill_options;
            }
            else
            {
                field_options = nbill_options;
            }
        }

        function add_new_option(window_document)
        {
            //Find next new ID number
            var trs = window_document.getElementsByTagName('tr');

            var highest_no = 0;
            for (var i = 0; i < trs.length; i++)
            {
                tr = trs[i];
                if (tr.getAttribute('id') != null && tr.getAttribute('id').indexOf('tr_option_') > -1)
                {
                    var option_no = tr.id.substr(10);
                    var option_id = window_document.getElementById('option_id_' + option_no).value;
                    if (option_id.indexOf('added_') > -1)
                    {
                        if (parseInt(option_id.substr(6)) > highest_no)
                        {
                            highest_no = parseInt(option_id.substr(6));
                        }
                    }
                }
            }

            //Save options, add new, refresh page
            var temp_options = save_options(window_document, true);
            var nbill_options = temp_options;
            var new_id = 'added_' + (highest_no + 1);
            var nbill_option = new nbill_form_field_option(new_id);
            nbill_option.code = window_document.getElementById('option_new_value').value;
            nbill_option.description = window_document.getElementById('option_new_description').value;
            nbill_option.ordering = get_assoc_array_length(temp_options) + 1;
            temp_options[nbill_option.id] = nbill_option;
            submit_ajax_request('show_field_options', 'nbill_field_id=-1&nbill_no_products=1&nbill_field_options=' + encodeURIComponent(serialize(temp_options)), function(output){window_document.getElementById('nbill_field_options_container').innerHTML=output;}, false);
        }

        //Create javascript object to hold options (if applicable)
        var field_options = new Object();
        <?php
        if ($options && count($options) > 0)
        {
            $option_index = 0;
            foreach ($options as $option)
            {
                ?>
                fld_option = new nbill_form_field_option('<?php echo $option->id; ?>');
                fld_option.code = '<?php echo $option->option_value; ?>';
                fld_option.description = '<?php echo $option->option_description; ?>';
                fld_option.ordering = <?php echo $option->ordering; ?>;
                field_options[<?php echo $option_index; ?>] = fld_option;
                <?php
                $option_index++;
            }
        }
        ?>
		</script>

		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "profile_fields"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php echo $row->id ? NBILL_EDIT_PROFILE_FIELD . " '$row->name'" : NBILL_NEW_PROFILE_FIELD; ?>
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
        <input type="hidden" name="action" value="profile_fields" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
		<input type="hidden" name="id" value="<?php echo $field_id;?>" />
        <input type="hidden" name="apply_to_existing" id="apply_to_existing" value="" />
        <input type="hidden" name="serialized_options" id="serialized_options" value="" />
		<?php nbf_html::add_filters(); ?>

        <?php
        $tab_settings = new nbf_tab_group();
        $tab_settings->start_tab_group("admin_settings");
        $tab_settings->add_tab_title("basic", NBILL_ADMIN_TAB_BASIC);
        $tab_settings->add_tab_title("advanced", NBILL_ADMIN_TAB_ADVANCED);
        ob_start();
        ?>

        <div class="rounded-table">
		    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
		    <tr>
			    <th colspan="2"><?php echo NBILL_PROFILE_FIELD_DETAILS; ?></th>
		    </tr>

		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_FORM_FIELD_TYPE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php
					//Create a dropdown of types
					$type_list = array();
                    $options_allowed = array();
					foreach ($field_types as $field_type)
					{
						$type_list[] = nbf_html::list_option($field_type->code, $field_type->description);
                        //Check whether this field type supports options or not
                        $class_name = "nbf_field_control";
                        include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control.base.php");
                        if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control." . nbf_common::nb_strtolower($field_type->code) . ".php"))
                        {
                            include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control." . nbf_common::nb_strtolower($field_type->code) . ".php");
                            $class_name .= "_" . nbf_common::nb_strtolower($field_type->code);
                        }
                        $control_obj = new $class_name(0, 0);
                        if ($control_obj->support_options)
                        {
                            $options_allowed[] = nbf_common::nb_strtolower($field_type->code);
                        }
                        unset($control_obj);
					}
                    $on_change = "document.getElementById('nbill_profile_field_options').disabled=true;if(this.value){switch(this.value.toLowerCase()){";
                    foreach($options_allowed as $option_fld_type)
                    {
                        $on_change .= "case '" . $option_fld_type . "':";
                    }
                    $on_change .= "document.getElementById('nbill_profile_field_options').disabled=false;break;}}";
					echo nbf_html::select_list($type_list, "field_type", 'class="inputbox" id="field_type" onchange="' . $on_change . '"', $row->field_type);
				    ?>
                    <input type="button" class="button btn" name="nbill_profile_field_options" id="nbill_profile_field_options" value="<?php echo NBILL_FORM_FIELD_OPTIONS; ?>" <?php
                    if (array_search(nbf_common::nb_strtolower($row->field_type), $options_allowed) === false)
                    {
                        echo "disabled=\"disabled\"";
                    }
                    ?>onclick="show_field_options()" />
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_TYPE_HELP, "field_type_help"); ?>
			    </td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_FORM_FIELD_NAME; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="name" id="name" value="<?php echo $row->name; ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_NAME_HELP, "name_help"); ?>
			    </td>
		    </tr>
            <!-- Custom Fields Placeholder -->
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_PROFILE_FIELD_PUBLISHED; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php echo nbf_html::yes_or_no_options("published", "", $row->published); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_PROFILE_FIELD_PUBLISHED, "published_help"); ?>
			    </td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_FORM_FIELD_LABEL; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="label" id="label" value="<?php echo str_replace("\"", "&quot;", $row->label); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_LABEL_HELP, "label_help"); ?>
			    </td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_FORM_FIELD_SUFFIX; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="checkbox_text" id="checkbox_text" value="<?php echo str_replace("\"", "&quot;", $row->checkbox_text); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_SUFFIX_HELP, "checkbox_text_help"); ?>
			    </td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_FORM_FIELD_MERGE_COLS; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php echo nbf_html::yes_or_no_options("merge_columns", "", $row->merge_columns); ?>
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_MERGE_COLS_HELP, "merge_columns_help"); ?>
			    </td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_FORM_FIELD_DEFAULT_VALUE; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <input type="text" name="default_value" id="default_value" value="<?php echo str_replace("\"", "&quot;", $row->default_value); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_DEFAULT_VALUE_HELP, "default_value_help"); ?>
			    </td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_FORM_FIELD_REQUIRED; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php echo nbf_html::yes_or_no_options("required", "", $row->required); ?>
			    </td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_FORM_FIELD_HELP_TEXT; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <textarea name="help_text" id="help_text"><?php echo $row->help_text; ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_HELP_TEXT_HELP, "help_text_help"); ?>
			    </td>
		    </tr>
		    <tr>
			    <td class="nbill-setting-caption">
				    <?php echo NBILL_FORM_FIELD_CONFIRMATION; ?>
			    </td>
			    <td class="nbill-setting-value">
				    <?php echo nbf_html::yes_or_no_options("confirmation", "", $row->confirmation); ?>
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_CONFIRMATION_HELP, "confirmation_help"); ?>
			    </td>
		    </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_FORM_FIELD_INCLUDE_ON_FORMS; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php echo nbf_html::yes_or_no_options("include_on_forms", "", $row->include_on_forms); ?>
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_INCLUDE_ON_FORMS_HELP, "include_on_forms_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_FORM_FIELD_SUMMARY; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php echo nbf_html::yes_or_no_options("show_on_summary", "", $row->show_on_summary); ?>
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_SUMMARY_HELP, "show_on_summary_help"); ?>
                </td>
            </tr>
		    </table>
        </div>

        <?php
        $tab_settings->add_tab_content("basic", ob_get_clean());
        ob_start();
        ?>

        <div class="rounded-table">
            <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
            <tr>
                <th colspan="2"><?php echo NBILL_PROFILE_FIELD_DETAILS; ?></th>
            </tr>

            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_FORM_FIELD_ATTRIBUTES; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="attributes" id="attributes" value="<?php echo str_replace("\"", "&quot;", $row->attributes); ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_ATTRIBUTES_HELP, "attributes_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_FORM_FIELD_PRE_FIELD; ?>
                </td>
                <td class="nbill-setting-value">
                    <textarea name="pre_field" id="pre_field"><?php echo $row->pre_field; ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_PRE_FIELD_HELP, "pre_field_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_FORM_FIELD_POST_FIELD; ?>
                </td>
                <td class="nbill-setting-value">
                    <textarea name="post_field" id="post_field" rows="5"><?php echo $row->post_field; ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_POST_FIELD_HELP, "post_field_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_FORM_FIELD_XREF; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    $xref_list = array();
                    $xref_list[] = nbf_html::list_option("", "[" . NBILL_NOT_APPLICABLE . "]");
                    foreach ($xref_tables as $code=>$description)
                    {
                        $xref_list[] = nbf_html::list_option($code, defined($description) ? constant($description) : $description);
                    }
                    echo nbf_html::select_list($xref_list, "xref", "class=\"inputbox\"", $row->xref);
                    ?>
                    <?php nbf_html::show_static_help(sprintf(NBILL_FORM_FIELD_XREF_HELP, nbf_cms::$interop->db_connection->prefix), "xref_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_FORM_FIELD_XREF_SQL; ?>
                </td>
                <td class="nbill-setting-value">
                    <textarea name="xref_sql" id="xref_sql"><?php echo $row->xref_sql; ?></textarea>
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_XREF_SQL_HELP, "xref_sql_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_FORM_FIELD_ENTITY_MAP; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    $map_list = array();
                    $map_list[] = nbf_html::list_option("", "[" . NBILL_NOT_APPLICABLE . "]");
                    $map_list[] = nbf_html::list_option("custom", "[" . NBILL_CUSTOM_FIELD . "]");
                    foreach ($entity_map as $code=>$description)
                    {
                        $map_list[] = nbf_html::list_option($code, $description);
                    }
                    echo nbf_html::select_list($map_list, "entity_mapping", "class=\"inputbox\"", $row->id ? $row->entity_mapping : "custom");
                    ?>
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_ENTITY_MAP_HELP, "entity_mapping_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_FORM_FIELD_CONTACT_MAP; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    $map_list = array();
                    $map_list[] = nbf_html::list_option("", "[" . NBILL_NOT_APPLICABLE . "]");
                    $map_list[] = nbf_html::list_option("custom", "[" . NBILL_CUSTOM_FIELD . "]");
                    foreach ($contact_map as $code=>$description)
                    {
                        $map_list[] = nbf_html::list_option($code, $description);
                    }
                    echo nbf_html::select_list($map_list, "contact_mapping", "class=\"inputbox\"", $row->contact_mapping);
                    ?>
                    <?php nbf_html::show_static_help(NBILL_FORM_FIELD_CONTACT_MAP_HELP, "contact_mapping_help"); ?>
                </td>
            </tr>
            </table>
        </div>
		</form>

        <?php
        $tab_settings->add_tab_content("advanced", ob_get_clean());
        $tab_settings->end_tab_group();
        ?>

		<?php
	}
}