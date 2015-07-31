<?php
/**
* HTML Output for anomaly report
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillHousekeeping
{
    public static function doHousekeeping(&$vendors, $vendor_name, $records_to_delete = false, $total_records = 0)
    {
        ?>
        <script type="text/javascript">
        function expand(record_type)
        {
            document.getElementById('collapsed_type_' + record_type).style.display = 'none';
            document.getElementById('expanded_type_' + record_type).style.display = 'block';
        }
        function collapse(record_type)
        {
            document.getElementById('expanded_type_' + record_type).style.display = 'none';
            document.getElementById('collapsed_type_' + record_type).style.display = 'block';
        }
        </script>

        <table class="adminheading" style="width:100%;">
        <tr>
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "housekeeping"); ?>>
                <?php echo NBILL_HOUSEKEEPING_TITLE . " " . NBILL_FOR . " $vendor_name";
                ?>
            </th>
        </tr>
        </table>

        <?php
        if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
        {
            ?><div class="nbill-message"><?php echo nbf_globals::$message; ?></div><?php
        }
        ?>

        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
            <input type="hidden" name="option" value="<?php echo nbf_common::get_param($_REQUEST, 'option'); ?>" />
            <input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
            <input type="hidden" name="task" value="<?php echo nbf_common::get_param($_REQUEST, 'task'); ?>" />
            <input type="hidden" name="hk_postback" value="1" />
            <?php if ($records_to_delete !== false)
            {
                //Show what will be deleted and ask for confirmation
                if ($total_records > 0)
                {
                    ?>
                    <div class="nbill-message"><?php echo sprintf(NBILL_HOUSEKEEPING_EXECUTE_WARNING, '<span style="color:#ff0000">' . $total_records . '</span>', '<span style="color:#ff0000">' . nbf_common::get_param($_REQUEST, 'hk_no_of_units', 'x') . '</span>', '<span style="color:#ff0000">' . nbf_common::get_param($_REQUEST, 'hk_units', NBILL_HOUSEKEEPING_UNIT_DAYS) . '</span>'); ?></div><br /><br />
                    <?php
                    for ($i=1; $i<=13; $i++)
                    {
                        if (count($records_to_delete[$i]) > 0)
                        {
                            ?>
                            <div id="collapsed_type_<?php echo $i; ?>">
                                <a href="#" onclick="expand('<?php echo $i; ?>');return false;" title="<?php echo NBILL_HOUSEKEEPING_EXPAND; ?>"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path . "/images/plus.png" ?>" alt="<?php echo NBILL_HOUSEKEEPING_EXPAND; ?>" /><strong><?php echo constant("NBILL_HOUSEKEEPING_TYPE_$i"); ?> (<?php echo sprintf(NBILL_HOUSEKEEPING_X_RECORDS, count($records_to_delete[$i])); ?>)</strong></a>
                            </div>
                            <div id="expanded_type_<?php echo $i; ?>" style="display:none;">
                                <a href="#" onclick="collapse('<?php echo $i; ?>');return false;" title="<?php echo NBILL_HOUSEKEEPING_COLLAPSE; ?>"><img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path . "/images/minus.png" ?>" alt="<?php echo NBILL_HOUSEKEEPING_COLLAPSE; ?>" /><strong><?php echo constant("NBILL_HOUSEKEEPING_TYPE_$i"); ?> (<?php echo sprintf(NBILL_HOUSEKEEPING_X_RECORDS, count($records_to_delete[$i])); ?>)</strong></a>
                                <div>
                                    <?php
                                    $show_comma = false;
                                    foreach ($records_to_delete[$i] as $key=>$value)
                                    {
                                        if ($show_comma)
                                        {
                                            echo ", ";
                                        }
                                        ?><a target="_blank" href="<?php echo self::get_editor_url($i, $key); ?>"><?php echo $value; ?></a><?php
                                        $show_comma = true;
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        else
                        {
                            ?>
                            <img src="<?php echo nbf_cms::$interop->nbill_site_url_path . "/images/minus.png" ?>" alt="<?php echo NBILL_HOUSEKEEPING_NOTHING_TO_DELETE; ?>" /> <strong><?php echo constant("NBILL_HOUSEKEEPING_TYPE_$i"); ?></strong>
                            <div><?php echo NBILL_HOUSEKEEPING_NOTHING_TO_DELETE; ?></div>
                            <?php
                        }
                        echo "<br />";
                    }
                    ?>
                    <br />
                    <input type="submit" class="button btn" name="delete_records" id="delete_records" style="font-size:10pt;font-weight:bold;" value="<?php echo NBILL_HOUSEKEEPING_DELETE; ?>" />
                    <input type="submit" class="button btn" name="cancel" id="cancel" style="font-size:10pt;font-weight:bold;" value="<?php echo NBILL_HOUSEKEEPING_CANCEL; ?>" />
                    <?php
                }
                else
                {
                    $records_to_delete = false;
                    ?><div class="nbill-message"><?php echo NBILL_HOUSEKEEPING_NOTHING_TO_DELETE; ?></div><?php
                }
            }
            if ($records_to_delete !== false)
            { ?>
                <div style="display:none">
            <?php } ?>
            <p align="left"><?php echo NBILL_HOUSEKEEPING_INTRO; ?></p>
            <div align="left">
                <?php
                //Display filter dropdown if multi-company
                if (count($vendors) > 1)
                {
                    echo NBILL_VENDOR_NAME . "&nbsp;";
                    $selected_filter = $vendors[0]->id;
                    if (nbf_common::nb_strlen(nbf_common::get_param($_POST,'vendor_filter')) > 0)
                    {
                        $selected_filter = nbf_common::get_param($_POST, 'vendor_filter');
                    }
                    $vendor_name = array();
                    foreach ($vendors as $vendor)
                    {
                        $vendor_name[] = nbf_html::list_option($vendor->id, $vendor->vendor_name);
                    }
                    echo nbf_html::select_list($vendor_name, "vendor_filter", 'id="vendor_filter" class="inputbox""', $selected_filter );
                    $_POST['vendor_filter'] = $selected_filter;
                }
                else
                {
                    echo "<input type=\"hidden\" name=\"vendor_filter\" id=\"vendor_filter\" value=\"" . $vendors[0]->id . "\" />";
                    $_POST['vendor_filter'] = $vendors[0]->id;
                } ?>
            </div>

            <table class="adminlist" cellspacing="0" cellpadding="2" id="housekeeping_criteria">
                <tr class="nbill_tr_no_highlight">
                    <th class="housekeeping-side-heading"><?php echo NBILL_HOUSEKEEPING_SELECT_RECORDS; ?></th>
                    <td class="housekeeping-values"><input type="checkbox" class="nbill_form_input" name="type_1" id="type_1"<?php echo nbf_common::get_param($_REQUEST, 'hk_postback') && !nbf_common::get_param($_REQUEST, 'type_1') ? "" : " checked=\"checked\""; ?> /><label for="type_1" class="nbill_form_label"><?php echo NBILL_HOUSEKEEPING_TYPE_1; ?></label>&nbsp;<?php nbf_html::show_overlib(NBILL_HOUSEKEEPING_TYPE_1_HELP); ?></td>
                </tr>
                <?php
                for ($i=2; $i<=13; $i++)
                {
                    ?>
                    <tr class="nbill_tr_no_highlight">
                        <td>&nbsp;</td>
                        <td class="housekeeping-values" colspan="2"><input type="checkbox" class="nbill_form_input" name="type_<?php echo $i; ?>" id="type_<?php echo $i; ?>"<?php echo nbf_common::get_param($_REQUEST, 'hk_postback') && !nbf_common::get_param($_REQUEST, 'type_' . $i) ? "" : " checked=\"checked\""; ?> /><label for="type_<?php echo $i; ?>" class="nbill_form_label"><?php echo constant("NBILL_HOUSEKEEPING_TYPE_$i"); ?></label>&nbsp;<?php nbf_html::show_overlib(constant("NBILL_HOUSEKEEPING_TYPE_$i" . "_HELP")); ?></td>
                    </tr>
                    <?php
                }?>
                <tr class="nbill_tr_no_highlight">
                    <th class="housekeeping-side-heading"><?php echo NBILL_HOUSEKEEPING_DATE_FROM; ?></th>
                    <td class="housekeeping-values">
                        <select name="hk_no_of_units" id="no_of_units" style="width: 60px;">
                            <?php for ($i=1; $i<=50; $i++)
                            { ?>
                                <option value="<?php echo $i; ?>"<?php if ((!isset($_REQUEST['hk_no_of_units']) && $i == 6) || $i == nbf_common::get_param($_REQUEST, 'hk_no_of_units')) {echo " selected=\"selected\"";} ?>><?php echo $i; ?></option>
                            <?php } ?>
                        </select>
                        &nbsp;
                        <select name="hk_units" id="hk_units">
                            <option value="days"<?php if (nbf_common::get_param($_REQUEST, 'hk_units') == 'days'){echo " selected=\"selected\"";} ?>><?php echo NBILL_HOUSEKEEPING_UNIT_DAYS; ?></option>
                            <option value="weeks"<?php if (nbf_common::get_param($_REQUEST, 'hk_units') == 'weeks'){echo " selected=\"selected\"";} ?>><?php echo NBILL_HOUSEKEEPING_UNIT_WEEKS; ?></option>
                            <option value="months"<?php if (nbf_common::get_param($_REQUEST, 'hk_units') == 'months'){echo " selected=\"selected\"";} ?>><?php echo NBILL_HOUSEKEEPING_UNIT_MONTHS; ?></option>
                            <option value="years"<?php if (!isset($_REQUEST['hk_units']) || nbf_common::get_param($_REQUEST, 'hk_units') == 'years'){echo " selected=\"selected\"";} ?>><?php echo NBILL_HOUSEKEEPING_UNIT_YEARS; ?></option>
                        </select>
                        &nbsp;
                        <?php echo NBILL_HOUSEKEEPING_DATE_END; ?>
                    </td>
                </tr>
                <tr class="nbill_tr_no_highlight">
                    <td>&nbsp;</td>
                    <td><input type="submit" class="button btn" name="execute_housekeeping" id="execute_housekeeping" value="<?php echo NBILL_HOUSEKEEPING_PREVIEW; ?>" /></td>
                </tr>
            </table>
        </form>
        <?php
        if ($records_to_delete !== false)
        { ?>
            </div>
        <?php }
    }

    public static function get_editor_url($type, $key)
    {
        switch ($type)
        {
            case 1:
                //CMS User
                return sprintf(nbf_cms::$interop->user_editor_url, $key);
            case 2:
                //Potential Clients
                return nbf_cms::$interop->admin_page_prefix . "&action=potential_clients&task=edit&cid=" . $key;
            case 3:
                //Clients
                return nbf_cms::$interop->admin_page_prefix . "&action=clients&task=edit&cid=" . $key;
            case 4:
                //Suppliers
                return nbf_cms::$interop->admin_page_prefix . "&action=suppliers&task=edit&cid=" . $key;
            case 5:
                //Orphan Contacts
                return nbf_cms::$interop->admin_page_prefix . "&action=contacts&task=edit&cid=" . $key;
            case 6:
                //Pending Orders
                return nbf_cms::$interop->admin_page_prefix . "&action=pending&task=show&cid=" . $key;
            case 7:
                //Orders
                return nbf_cms::$interop->admin_page_prefix . "&action=orders&task=edit&cid=" . $key;
            case 8:
                //Invoices
                return nbf_cms::$interop->admin_page_prefix . "&action=invoices&task=edit&cid=" . $key;
            case 9:
                //Quotes
                return nbf_cms::$interop->admin_page_prefix . "&action=quotes&task=edit&cid=" . $key;
            case 10:
                //Gateway Tx Data
                return nbf_cms::$interop->admin_page_prefix . "&action=tx_search&g_tx_id=" . $key;
            case 11:
                //Income
                return nbf_cms::$interop->admin_page_prefix . "&action=income&task=edit&cid=" . $key;
            case 12:
                //Expenditure
                return nbf_cms::$interop->admin_page_prefix . "&action=expenditure&task=edit&cid=" . $key;
            case 13:
                //Supporting Docs
                return nbf_cms::$interop->admin_popup_page_prefix . "&action=supporting_docs&task=download&file=" . base64_encode($key);
        }
    }
}