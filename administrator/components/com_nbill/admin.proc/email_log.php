<?php
/**
* Main processing file for email log report
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');//Delete any log entries more than 1 year old
$nb_database = nbf_cms::$interop->database;
$sql = "DELETE FROM #__nbill_email_log WHERE timestamp < " . nbf_common::nb_strtotime("- 1 Year");
$nb_database->setQuery($sql);
$nb_database->query();

switch ($task)
{
    case "silent":
        break;
    case "details":
        show_email_details();
        break;
    case "view":
        show_email_log();
        break;
    default:
        if (substr($task, 0, 10) == "clear_old-")
        {
            $old_date = substr($task, 10);
            if (nbf_common::nb_strlen($old_date) == 0 || $old_date == "null")
            {
                nbf_globals::$message = NBILL_NO_ACTION_TAKEN;
                show_email_log();
            }
            else
            {
                $date_int = 0;
                $date_parts = explode("/", $old_date);
                if (count($date_parts) == 3)
                {
                    $date_int = nbf_common::nb_mktime(0, 0, 0, $date_parts[1], $date_parts[2], $date_parts[0]);
                }
                if ($date_int > 0)
                {
                    //Do the do and set a value in $message
                    delete_old_emails($date_int);
                    nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . "&action=email_log&task=view&message=" . nbf_globals::$message);
                }
                else
                {
                    nbf_globals::$message = sprintf(NBILL_INVALID_DATE_ENTERED, nbf_common::get_date_format(true));
                    show_email_log();
                }
            }
        }
        else
        {
            show_email_log();
        }
        break;
}

function show_email_log()
{
    $nb_database = nbf_cms::$interop->database;
    $date_format = nbf_common::get_date_format();

    //Work out date range
    if (nbf_common::get_param($_REQUEST, 'defined_date_range') == 'specified_range') {
        $date_format = nbf_common::get_date_format();
        $cal_date_format = nbf_common::get_date_format(true);
        $date_parts = nbf_common::nb_getdate(time());
        $search_date_from = -1;
        if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST,'search_date_from')) > 5)
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
            $filter_date_parts = nbf_date::get_date_parts(nbf_common::get_param($_REQUEST,'search_date_from'), $cal_date_format);
            if (count($filter_date_parts) == 3)
            {
                $search_date_from = nbf_common::nb_mktime($filter_date_parts['y']==1970&&$filter_date_parts['m']==1&&$filter_date_parts['d']==1 ? 1 : 0, 0, 0, $filter_date_parts['m'], $filter_date_parts['d'], $filter_date_parts['y']);
            }
        }
        if ($search_date_from == -1)
        {
            //Don't use default start date as reports are not paginated (so takes ages to show a year or more by default) - show current month only
            $search_date_from = nbf_common::nb_mktime(0, 0, 0, $date_parts["mon"], 1, $date_parts["year"]);
        }
        $search_date_to = nbf_common::nb_mktime(23, 59, 59, $date_parts["mon"], $date_parts["mday"], $date_parts["year"]);
        if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST,'search_date_to')) > 5)
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.date.class.php");
            $filter_date_parts = nbf_date::get_date_parts(nbf_common::get_param($_REQUEST,'search_date_to'), $cal_date_format);
            if (count($filter_date_parts) == 3)
            {
                $search_date_to = nbf_common::nb_mktime(23, 59, 59, $filter_date_parts['m'], $filter_date_parts['d'], $filter_date_parts['y']);
            }
        }
    } else {
        $search_date_from = -1;
        $search_date_to = -1;
        $range = nbf_common::get_param($_REQUEST, 'defined_date_range');
        if (!$range) {
            $range = 'current_month';
        }
        include_once(nbf_cms::$interop->nbill_admin_base_path . '/framework/classes/nbill.date.class.php');
        nbf_date::getDefinedRangeDates($range, $search_date_from, $search_date_to);
    }
    $_REQUEST['search_date_from'] = nbf_common::nb_date($date_format, $search_date_from);
    $_REQUEST['search_date_to'] = nbf_common::nb_date($date_format, $search_date_to);

    //Check for status filter
    $status_filter = nbf_common::get_param($_REQUEST, 'search_log_status');
    switch ($status_filter)
    {
        case "success":
        case "failure":
            //leave alone
            break;
        default:
            $status_filter = "all";
            break;
    }
    $_REQUEST['search_log_status'] = $status_filter;

    //Check for type filter
    $type_filter = nbf_common::get_param($_REQUEST, 'search_log_type');
    switch ($type_filter)
    {
        case "PE":
        case "OR":
        case "QU":
        case "IN":
            //leave alone
            break;
        default:
            $type_filter = "all";
            break;
    }
    $_REQUEST['search_log_type'] = $type_filter;

    //Check for 'to' filter
    $to_filter = trim(nbf_common::get_param($_REQUEST, 'search_log_to'));

    //Check for document filter (passed in URL only)
    $document_filter = intval(nbf_common::get_param($_REQUEST, 'for_document'));

    //Count the total number of records
    $sql = "SELECT count(*) FROM #__nbill_email_log WHERE 1";
    if (!$document_filter)
    {
        $sql .= " AND #__nbill_email_log.timestamp >= $search_date_from AND #__nbill_email_log.timestamp <= $search_date_to";
    }
    switch ($status_filter)
    {
        case "success":
            $sql .= " AND #__nbill_email_log.status = 'OK'";
            break;
        case "failure":
            $sql .= " AND #__nbill_email_log.status != 'OK'";
            break;
    }
    switch ($type_filter)
    {
        case "PE":
        case "OR":
        case "QU":
        case "IN":
            $sql .= " AND #__nbill_email_log.type = '$type_filter'";
            break;
    }
    if ($to_filter)
    {
        $sql .= " AND `to` LIKE '%$to_filter%' OR `cc` LIKE '%$to_filter%' OR `bcc` LIKE '%$to_filter%'";
    }
    if ($document_filter)
    {
        $sql .= " AND document_id = $document_filter";
    }
    $nb_database->setQuery($sql);
    $total = $nb_database->loadResult();

    //Add page navigation
    $pagination = new nbf_pagination("email_log", $total);

    $sql = "SELECT #__nbill_email_log.id AS log_id, #__nbill_email_log.type, #__nbill_email_log.document_id, #__nbill_email_log.order_id,
            #__nbill_email_log.pending_order_id, #__nbill_email_log.to, #__nbill_email_log.cc, #__nbill_email_log.bcc,
            #__nbill_email_log.timestamp, #__nbill_email_log.status, #__nbill_email_log.subject,
            #__nbill_entity.company_name, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS `name`, #__nbill_document.document_no,
            #__nbill_pending_orders.id AS pending_order_exists, #__nbill_orders.order_no
            FROM #__nbill_email_log
            LEFT JOIN #__nbill_entity ON #__nbill_email_log.entity_id = #__nbill_entity.id
            LEFT JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id
            LEFT JOIN #__nbill_document ON #__nbill_email_log.document_id = #__nbill_document.id
            LEFT JOIN #__nbill_pending_orders ON #__nbill_email_log.document_id = #__nbill_pending_orders.id
            LEFT JOIN #__nbill_orders ON #__nbill_email_log.order_id = #__nbill_orders.id
            WHERE 1";
    if (!$document_filter)
    {
        $sql .= " AND #__nbill_email_log.timestamp >= $search_date_from AND #__nbill_email_log.timestamp <= $search_date_to";
    }
    switch ($status_filter)
    {
        case "success":
            $sql .= " AND #__nbill_email_log.status = 'OK'";
            break;
        case "failure":
            $sql .= " AND #__nbill_email_log.status != 'OK'";
            break;
    }
    switch ($type_filter)
    {
        case "PE":
        case "OR":
        case "QU":
        case "IN":
            $sql .= " AND #__nbill_email_log.type = '$type_filter'";
            break;
    }
    if ($to_filter)
    {
        $sql .= " AND `to` LIKE '%$to_filter%' OR `cc` LIKE '%$to_filter%' OR `bcc` LIKE '%$to_filter%'";
    }
    if ($document_filter)
    {
        $sql .= " AND document_id = $document_filter";
    }
    $sql .= " ORDER BY #__nbill_email_log.timestamp DESC LIMIT $pagination->list_offset, $pagination->records_per_page";
    $nb_database->setQuery($sql);
    $log_entries = $nb_database->loadObjectList();
    if (!$log_entries)
    {
        $log_entries = array();
    }

    nBillEmailLog::show_log($log_entries, $date_format, $pagination);
}

function show_email_details()
{
    nbf_common::load_language("email");
    $nb_database = nbf_cms::$interop->database;

    $email = null;
    $sql = "SELECT `timestamp`, `from`, `to`, `cc`, `bcc`, `subject`, `message`, `html` FROM #__nbill_email_log WHERE id = " . intval(nbf_common::get_param($_REQUEST, 'id'));
    $nb_database->setQuery($sql);
    $nb_database->loadObject($email);

    nBillEmailLog::show_email_details($email);
}

function delete_old_emails($before_date)
{
    $nb_database = nbf_cms::$interop->database;

    $sql = "SELECT count(*) FROM #__nbill_email_log WHERE timestamp < " . intval($before_date);
    $nb_database->setQuery($sql);
    $delete_count = intval($nb_database->loadResult());

    if ($delete_count)
    {
        $sql = "DELETE FROM #__nbill_email_log WHERE timestamp < " . intval($before_date);
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

    nbf_globals::$message = sprintf(NBILL_EMAIL_LOG_OLD_DELETED, $delete_count);
}