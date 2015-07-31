<?php
/**
* HTML Output for email log
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillEmailLog
{
    public static function show_log($log_entries, $date_format, $pagination)
    {
        nbf_html::load_calendar();
        $exclude_filter = array();
        $exclude_filter[] = "search_date_from";
        $exclude_filter[] = "search_date_to";
        nbf_html::add_filters($exclude_filter);
        ?>
        <table class="adminheading" style="width:auto;">
        <tr>
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, nbf_common::get_param($_REQUEST, 'action')); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_EMAIL_LOG_TITLE; ?>
            </th>
        </tr>
        </table>

        <?php if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
        {
            echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
        } ?>

        <p style="clear:both;"><?php echo NBILL_EMAIL_LOG_INTRO; ?></p>

        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
            <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
            <input type="hidden" name="action" value="email_log" />
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="box_checked" value="0" />
            <input type="hidden" name="hidemainmenu" value="0">

            <div align="left">
                <?php nbf_html::show_defined_date_ranges(); ?>
                <div id="date_range_controls" style="display:<?php echo nbf_common::get_param($_REQUEST,'defined_date_range') == 'specified_range' ? 'block' : 'none'; ?>;">
                    <span style="white-space:nowrap">
                    <?php echo NBILL_DATE_RANGE; $cal_date_format = nbf_common::get_date_format(true); ?>
                    <input type="text" name="search_date_from" class="inputbox date-entry" maxlength="19" value="<?php echo intval(nbf_common::get_param($_REQUEST, 'for_document')) ? nbf_common::nb_date($date_format, 0) : nbf_common::get_param($_REQUEST,'search_date_from'); ?>" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> />
                    <input type="button" name="search_date_from_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.search_date_from,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> /></span>
                    <span style="white-space:nowrap"><?php echo NBILL_TO; ?>
                    <input type="text" name="search_date_to" class="inputbox date-entry" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_to'); ?>" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> />
                    <input type="button" name="search_date_to_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.search_date_to,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_POST,'all_outstanding')) {echo "disabled=\"disabled\"";} ?> /></span>
                </div>
                &nbsp;&nbsp;
                <span style="white-space:nowrap;"><?php echo NBILL_EMAIL_LOG_STATUS; ?>&nbsp;<select name="search_log_status" id="search_log_status">
                    <option value="all"<?php if (nbf_common::get_param($_REQUEST, 'search_log_status') == 'all'){echo " selected=\"selected\"";} ?>><?php echo NBILL_EMAIL_LOG_SHOW_ALL; ?></option>
                    <option value="success"<?php if (nbf_common::get_param($_REQUEST, 'search_log_status') == 'success'){echo " selected=\"selected\"";} ?>><?php echo NBILL_EMAIL_LOG_SHOW_SUCCESS; ?></option>
                    <option value="failure"<?php if (nbf_common::get_param($_REQUEST, 'search_log_status') == 'failure'){echo " selected=\"selected\"";} ?>><?php echo NBILL_EMAIL_LOG_SHOW_FAILURE; ?></option>
                </select></span>&nbsp;&nbsp;
                <span style="white-space:nowrap;"><?php echo NBILL_EMAIL_LOG_TYPE; ?>&nbsp;<select name="search_log_type" id="search_log_type">
                    <option value="all"<?php if (nbf_common::get_param($_REQUEST, 'search_log_type') == 'all'){echo " selected=\"selected\"";} ?>><?php echo NBILL_EMAIL_LOG_SHOW_ALL; ?></option>
                    <option value="PE"<?php if (nbf_common::get_param($_REQUEST, 'search_log_type') == 'PE'){echo " selected=\"selected\"";} ?>><?php echo NBILL_EMAIL_LOG_SHOW_PENDING; ?></option>
                    <option value="OR"<?php if (nbf_common::get_param($_REQUEST, 'search_log_type') == 'OR'){echo " selected=\"selected\"";} ?>><?php echo NBILL_EMAIL_LOG_SHOW_ORDERS; ?></option>
                    <option value="QU"<?php if (nbf_common::get_param($_REQUEST, 'search_log_type') == 'QU'){echo " selected=\"selected\"";} ?>><?php echo NBILL_EMAIL_LOG_SHOW_QUOTES; ?></option>
                    <option value="IN"<?php if (nbf_common::get_param($_REQUEST, 'search_log_type') == 'IN'){echo " selected=\"selected\"";} ?>><?php echo NBILL_EMAIL_LOG_SHOW_INVOICES; ?></option>
                </select></span>&nbsp;&nbsp;
                <span style="white-space:nowrap;"><?php echo NBILL_EMAIL_LOG_TO; ?>&nbsp;<input type="text" name="search_log_to" id="search_log_to" value="<?php echo nbf_common::get_param($_REQUEST, 'search_log_to'); ?>" /></span>
                <input type="submit" class="button btn" name="dosearch" value="<?php echo NBILL_GO; ?>" />
            </div>

            <div class="rounded-table">
                <table class="adminlist table">
                    <tr>
                        <th class="title">
                            <?php echo NBILL_EMAIL_LOG_DATE; ?>
                        </th>
                        <th class="title">
                            <?php echo NBILL_EMAIL_LOG_SUBJECT; ?>
                        </th>
                        <th class="title">
                            <?php echo NBILL_EMAIL_LOG_TO; ?>
                        </th>
                        <th class="title responsive-cell optional">
                            <?php echo NBILL_EMAIL_LOG_CC; ?>
                        </th>
                        <th class="title responsive-cell extra-wide-only">
                            <?php echo NBILL_EMAIL_LOG_BCC; ?>
                        </th>
                        <th class="title">
                            <?php echo NBILL_EMAIL_LOG_RECORD; ?>
                        </th>
                        <th class="selector">
                            <?php echo NBILL_EMAIL_LOG_ITEM_STATUS; ?>
                        </th>
                    </tr>
                    <?php
                    foreach ($log_entries as $log_entry)
                    {
                        ?>
                        <tr>
                            <td class="list-value">
                                <?php echo nbf_common::nb_date($date_format . " h:i:s", $log_entry->timestamp); ?>
                            </td>
                            <td class="list-value word-breakable">
                                <a href="#" onclick="window.open('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&action=email_log&task=details&hidemainmenu=1&hide_billing_menu=1&use_stylesheet=1&id=<?php echo $log_entry->log_id; ?>', '<?php echo nbf_common::nb_time(); ?>', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;"><?php echo nbf_common::nb_strlen($log_entry->subject) > 0 ? $log_entry->subject : NBILL_EMAIL_LOG_NO_SUBJECT; ?></a>
                            </td>
                            <td class="list-value word-breakable">
                                <?php echo $log_entry->to ? $log_entry->to : "&nbsp;"; ?>
                            </td>
                            <td class="list-value responsive-cell optional word-breakable">
                                <?php echo $log_entry->cc ? $log_entry->cc : "&nbsp;"; ?>
                            </td>
                            <td class="list-value responsive-cell extra-wide-only word-breakable">
                                <?php echo $log_entry->bcc ? $log_entry->bcc : "&nbsp;"; ?>
                            </td>
                            <td class="list-value"><?php switch ($log_entry->type)
                            {
                                case "PE":
                                    if ($log_entry->pending_order_exists)
                                    {
                                        echo "<a target=\"_blank\" href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=pending&task=show&cid=" . $log_entry->pending_order_id . "\">";
                                    }
                                    echo NBILL_EMAIL_LOG_PENDING . " " . $log_entry->pending_order_id;
                                    if ($log_entry->pending_order_exists)
                                    {
                                        echo "</a>";
                                    }
                                    break;
                                case "OR":
                                    if ($log_entry->order_no)
                                    {
                                        echo "<a target=\"_blank\" href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=orders&task=edit&cid=" . $log_entry->order_id . "\">";
                                    }
                                    echo NBILL_EMAIL_LOG_ORDER . " ";
                                    if ($log_entry->order_no)
                                    {
                                        echo $log_entry->order_no . "</a>";
                                    }
                                    else
                                    {
                                        echo NBILL_EMAIL_LOG_RECORD_DELETED;
                                    }
                                    break;
                                case "QU":
                                    if ($log_entry->document_no)
                                    {
                                        echo "<a target=\"_blank\" href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=quotes&task=edit&cid=" . $log_entry->document_id . "\">";
                                    }
                                    echo NBILL_EMAIL_LOG_QUOTE . " ";
                                    if ($log_entry->document_no)
                                    {
                                        echo $log_entry->document_no . "</a>";
                                        echo '<a title="HTML Preview" onclick="window.open(\'' . nbf_cms::$interop->admin_popup_page_prefix . '&action=quotes&task=printpreviewpopup&hidemainmenu=1&items=' . $log_entry->document_id .'\', \'' . uniqid() . '\', \'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no\');return false;" href="#"><img border="0" alt="HTML Preview" src="' . nbf_cms::$interop->nbill_site_url_path . '/images/preview.gif"/></a>';
                                    }
                                    else
                                    {
                                        echo NBILL_EMAIL_LOG_RECORD_DELETED;
                                    }
                                    break;
                                case "IN":
                                    if ($log_entry->document_no)
                                    {
                                        echo "&nbsp;<a target=\"_blank\" href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=invoices&task=edit&cid=" . $log_entry->document_id . "\">";
                                    }
                                    echo NBILL_EMAIL_LOG_INVOICE . " ";
                                    if ($log_entry->document_no)
                                    {
                                        echo $log_entry->document_no . "</a>";
                                        echo '&nbsp;<a title="HTML Preview" onclick="window.open(\'' . nbf_cms::$interop->admin_popup_page_prefix . '&action=invoices&task=printpreviewpopup&hidemainmenu=1&items=' . $log_entry->document_id .'\', \'' . uniqid() . '\', \'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no\');return false;" href="#"><img border="0" alt="HTML Preview" src="' . nbf_cms::$interop->nbill_site_url_path . '/images/preview.gif"/></a>';
                                    }
                                    else
                                    {
                                        echo NBILL_EMAIL_LOG_RECORD_DELETED;
                                    }
                                    break;
                                default:
                                    echo NBILL_EMAIL_LOG_UNKNOWN;
                                    break;
                            }
                            ?></td>
                            <td class="selector">
                                <?php
                                echo $log_entry->status; ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr class="nbill_tr_no_highlight"><td colspan="7" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
                </table>
            </div>
        </form>
        <?php
    }

    public static function show_email_details($email)
    {
        if ($email)
        {
            ?>
            <em><?php echo sprintf(NBILL_EMAIL_MESSAGE_TIMESTAMP, nbf_common::nb_date(nbf_common::get_date_format() . " h:i:s", $email->timestamp)); ?></em>
            <table cellpadding="3" cellspacing="0" border="0" class="nbill-email-table">
                <tr><td class="nbill-email-table" style="font-weight:bold;vertical-align:top;"><?php echo NBILL_EMAIL_MESSAGE_FROM; ?></td><td class="nbill-email-table"><?php echo $email->from; ?></td></tr>
                <tr><td class="nbill-email-table" style="font-weight:bold;vertical-align:top;"><?php echo NBILL_EMAIL_MESSAGE_TO; ?></td><td class="nbill-email-table"><?php echo $email->to; ?></td></tr>
                <tr><td class="nbill-email-table" style="font-weight:bold;vertical-align:top;"><?php echo NBILL_EMAIL_MESSAGE_CC; ?></td><td class="nbill-email-table"><?php echo $email->cc; ?></td></tr>
                <tr><td class="nbill-email-table" style="font-weight:bold;vertical-align:top;"><?php echo NBILL_EMAIL_MESSAGE_SUBJECT; ?></td><td class="nbill-email-table"><?php echo $email->subject; ?></td></tr>
                <tr><td class="nbill-email-table" colspan="2">
                    <?php
                    if ($email->html)
                    {
                        $start = nbf_common::nb_strpos($email->message, '<body>');
                        $start = $start ? $start + 6 : 0;
                        $email_message = nbf_common::nb_substr($email->message, $start);
                        $end = nbf_common::nb_strpos($email_message, '</body>');
                        $end = $end ? $end : nbf_common::nb_strlen($email_message);
                        $email_message = nbf_common::nb_substr($email_message, 0, $end);
                        echo $email_message;
                    }
                    else
                    {
                        echo str_replace("\n", "<br />", $email->message);
                    } ?>
                </td></tr>
            </table>
            <?php
        }
        else
        {
            echo NBILL_EMAIL_LOG_NO_DETAILS;
        }
    }
}