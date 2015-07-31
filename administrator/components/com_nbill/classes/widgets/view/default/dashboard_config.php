<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* This file is loaded via AJAX, so you cannot add anything to <head>
*/
?>
<script type="text/javascript">
var added_row = 0;
function move_row_up(row_number)
{
    if (row_number > 0) {
        var prev_elem = document.getElementById('td_id_' + (row_number - 1));
        var this_elem = document.getElementById('td_id_' + row_number);
        swap_elements(prev_elem, this_elem);

        //Note the change of ordering in the hidden field so we can save it on postback
        var this_widget_id = prev_elem.innerHTML;
        var swapped_widget_id = this_elem.innerHTML;
        document.getElementById('ordering_' + this_widget_id).value = row_number - 1;
        document.getElementById('ordering_' + swapped_widget_id).value = row_number;

        prev_elem = document.getElementById('td_title_' + (row_number - 1));
        this_elem = document.getElementById('td_title_' + row_number);
        swap_elements(prev_elem, this_elem);

        prev_elem = document.getElementById('td_type_' + (row_number - 1));
        this_elem = document.getElementById('td_type_' + row_number);
        swap_elements(prev_elem, this_elem);

        prev_elem = document.getElementById('td_published_' + (row_number - 1));
        this_elem = document.getElementById('td_published_' + row_number);
        swap_elements(prev_elem, this_elem);
    }
}
function move_row_down(row_number)
{
    move_row_up(row_number + 1);
}

function swap_elements(elem1, elem2)
{
    var prev_content = elem1.innerHTML;
    elem1.innerHTML = elem2.innerHTML;
    elem2.innerHTML = prev_content;
}

function add_new_row()
{
    if (document.getElementById('new_widget_title').value.length==0) {
        alert('<?php echo NBILL_WIDGETS_DASHBOARD_TITLE_MANDATORY ?>');
    } else {
        var widget_type_select = document.getElementById('new_widget_type');
        var widget_type = widget_type_select.options[widget_type_select.selectedIndex].value;
        var widget_title_input = document.getElementById('new_widget_title');
        var widget_title = widget_title_input.value;

        var table = document.getElementById('widget-list-table');
        var row = table.insertRow(table.rows.length - 1);
        added_row++;
        row.id = 'tr_added_row_' + added_row;

        var cell1 = row.insertCell(0);
        cell1.id = 'td_added_row_' + added_row;
        var textNode = document.createTextNode('-')
        cell1.appendChild(textNode);
        cell1.className='responsive-cell optional';
        var inputNode = document.createElement('input');
        inputNode.type='hidden';
        inputNode.name='added_row_' + added_row;
        inputNode.id='added_row_' + added_row;
        inputNode.value='{"type":"' + widget_type + '","title":"' + widget_title + '"}';
        cell1.appendChild(inputNode);

        var cell2 = row.insertCell(1);
        textNode = document.createTextNode(widget_title);
        cell2.appendChild(textNode);

        var cell3 = row.insertCell(2);
        cell3.className='responsive-cell optional';
        textNode = document.createTextNode(widget_type);
        cell3.appendChild(textNode);

        var cell4 = row.insertCell(3);
        cell4.colSpan=3;

        var cell5 = row.insertCell(4);
        cell5.className='center';
        cell5.innerHTML = '<a href="javascript:void(0);" onclick="document.getElementById(\'added_row_' + added_row + '\').value=\'\';document.getElementById(\'tr_added_row_' + added_row + '\').style.display=\'none\';return false;" class="widget-config-button small" id="delete_widget_new_' + added_row + '"><?php echo NBILL_DELETE; ?></a>';

        widget_type_select.selectedIndex = 0;
        widget_title_input.value = '';
    }
}

function reset_all()
{
    document.getElementById('task').value='reset_all';
    document.getElementById('tinybox_window').parentNode.style.display='none';
    submit_ajax_request('', getFormValues(), function(){location.reload();});
}
</script>

<div class="nbill-widget nbill-widget-config" id="nbill-widget-config-main">
    <div class="nbill-widget-title"><h2><?php echo NBILL_WIDGETS_DASHBOARD_CONFIG; ?></h2></div>
    <div class="nbill-widget-container">
        <p><?php echo NBILL_WIDGETS_DASHBOARD_CONFIG_INTRO; ?></p>
        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="nbill_widget_config_form" id="nbill_widget_config_form">
            <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
            <input type="hidden" name="action" value="widgets" />
            <input type="hidden" name="task" id="task" value="save_main_config" />

            <table id="widget-list-table" class="widget-config-table" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <th class="responsive-cell optional"><?php echo NBILL_ID; ?></th>
                    <th><?php echo NBILL_WIDGETS_DASHBOARD_CONFIG_TITLE; ?></th>
                    <th class="responsive-cell optional"><?php echo NBILL_WIDGETS_DASHBOARD_CONFIG_TYPE; ?></th>
                    <th class="center"><?php echo NBILL_WIDGETS_DASHBOARD_CONFIG_PUBLISHED; ?></th>
                    <th class="center" colspan="2"><?php echo NBILL_WIDGETS_DASHBOARD_CONFIG_ORDERING; ?></th>
                    <th class="center"><?php echo NBILL_WIDGETS_DASHBOARD_CONFIG_ACTION; ?></th>
                </tr>
                <?php
                $ordering = 0;
                foreach ($this->widgets as $widget)
                {
                    ?>
                    <tr id="row_<?php echo $ordering; ?>">
                        <td id="td_id_<?php echo $ordering; ?>" class="responsive-cell optional"><?php echo $widget->id; ?></td>
                        <td id="td_title_<?php echo $ordering; ?>"><?php echo defined($widget->title) ? constant($widget->title) : $widget->title; ?><input type="hidden" name="ordering_<?php echo $widget->id; ?>" id="ordering_<?php echo $widget->id; ?>" value="<?php echo $ordering; ?>" /></td>
                        <td id="td_type_<?php echo $ordering; ?>" class="responsive-cell optional"><?php echo $widget->type; ?></td>
                        <td id="td_published_<?php echo $ordering; ?>" class="center">
                            <input type="hidden" name="published_<?php echo $widget->id; ?>" id="published_<?php echo $widget->id; ?>" value="<?php echo $widget->published; ?>" />
                            <a href="javascript:void(0);" onclick="var p=document.getElementById('published_<?php echo $widget->id; ?>');var i=document.getElementById('img_published_<?php echo $widget->id; ?>');if(p.value=='1'){p.value='0';i.src='<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/cross.png';}else{p.value='1';i.src='<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/tick.png';}return false;" title="<?php echo $widget->published ? NBILL_WIDGETS_DASHBOARD_UNPUBLISH : NBILL_WIDGETS_DASHBOARD_PUBLISH ?>">
                                <img id="img_published_<?php echo $widget->id; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/<?php echo $widget->published ? "tick.png" : "cross.png"; ?>" border="0" alt="<?php echo $widget->published ? NBILL_WIDGETS_DASHBOARD_UNPUBLISH : NBILL_WIDGETS_DASHBOARD_PUBLISH ?>" />
                            </a>
                        </td>
                        <td class="center">
                            <?php if ($ordering > 0) { ?>
                                <a href="javascript:void(0);" onclick="move_row_up(<?php echo $ordering; ?>);this.blur();return false;" title="<?php echo NBILL_MOVE_UP; ?>">
                                    <img id="img_up_<?php echo $ordering; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/move_up.png" border="0" alt="<?php echo NBILL_MOVE_UP ?>" />
                                </a>
                                <?php
                            } ?>
                        </td>
                        <td class="center">
                            <?php
                            if ($ordering < count($this->widgets) - 1) { ?>
                                <a href="javascript:void(0);" onclick="move_row_down(<?php echo $ordering; ?>);this.blur();return false;" title="<?php echo NBILL_MOVE_DOWN; ?>">
                                    <img id="img_down_<?php echo $ordering; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/move_down.png" border="0" alt="<?php echo NBILL_MOVE_DOWN ?>" />
                                </a><?php
                            } ?>
                        </td>
                        <td id="td_action_<?php echo $ordering; ?>" class="center">
                            <input type="hidden" name="deleted_<?php echo $widget->id; ?>" id="deleted_<?php echo $widget->id; ?>" value="0" />
                            <a href="javascript:void(0);" onclick="document.getElementById('deleted_' + document.getElementById('td_id_<?php echo $ordering; ?>').innerHTML).value='1';document.getElementById('row_<?php echo $ordering; ?>').style.display='none';return false;" class="widget-config-button small" id="delete_widget_<?php echo $ordering; ?>"><?php echo NBILL_DELETE; ?></a>
                        </td>
                    </tr>
                    <?php
                    $ordering++;
                }
                ?>
                <tr class="widget-new-row">
                    <td colspan="7" id="td_title_new">
                        <?php echo NBILL_NEW; ?> <input type="text" name="new_widget_title" id="new_widget_title" placeholder="<?php echo NBILL_WIDGETS_DASHBOARD_ADD_ENTER_TITLE; ?>" />
                        <select name="new_widget_type" id="new_widget_type">
                            <?php
                            foreach ($this->widget_types as $type=>$description)
                            { ?>
                                <option value="<?php echo $type; ?>"><?php echo $description; ?></option>
                                <?php
                            } ?>
                        </select>
                        <a href="javascript:void(0);" onclick="add_new_row();return false;" class="widget-config-button small" id="add_widget_new"><?php echo NBILL_WIDGETS_DASHBOARD_ADD_NEW; ?></a>
                    </td>
                </tr>
            </table>

            <a href="javascript:void(0);" onclick="if(confirm('<?php echo NBILL_WIDGETS_DASHBOARD_RESET_ALL_HELP ?>')){reset_all();}return false;" class="widget-config-button" id="reset_all"><?php echo NBILL_WIDGETS_DASHBOARD_RESET_ALL; ?></a>

            <hr />
            <div class="nbill-widget-config-colours">
                <label for="colour_scheme_css"><?php echo NBILL_WIDGETS_DASHBOARD_COLOUR_SCHEME_CSS; ?>
                <select name="colour_scheme_css" id="colour_scheme_css">
                    <?php foreach ($this->templates as $template) { ?>
                    <option<?php if ($this->selected_template == $template) {echo ' selected="selected"';} ?>><?php echo $template; ?></option>
                    <?php } ?>
                </select></label>
                <?php echo sprintf(NBILL_WIDGETS_DASHBOARD_COLOUR_SCHEME_INFO, $this->template_path); ?>
            </div>
            <hr />

            <?php
            $reload = (strtolower(str_replace('!','',nbf_cms::$interop->cms_name)) == 'joomla' && substr(nbf_cms::$interop->cms_version, 0, 2) == '1.');
            ?>

            <div class="nbill-widget-config-buttons">
                <a href="javascript:void(0);" class="widget-config-button" id="nbill_widget_config_cancel" name="cancel" onclick="TINY.box.hide();return false;"><?php echo NBILL_CANCEL; ?></a>
                <a href="javascript:void(0);" class="widget-config-button" id="nbill_widget_config_save" name="save_config" onclick="this.style.cursor='wait';submit_ajax_request('', getFormValues(), function(content){document.getElementById('nbill_widget_config_save').style.cursor='pointer';<?php if ($reload){ ?>document.location.reload();return; <?php } ?>TINY.box.hide();document.getElementById('nbill_dashboard').innerHTML=content;<?php foreach ($this->widgets as $widget) { ?>extract_and_execute_js('nbill_widget_<?php echo $widget->id; ?>', true);<?php } ?>});return false;"><?php echo NBILL_SUBMIT; ?></a>
            </div>
        </form>
    </div>
</div>