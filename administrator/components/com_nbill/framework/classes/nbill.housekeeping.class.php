<?php
/**
* Class file containing static methods to perform housekeeping (clear down old data).
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* Clear down old data
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_housekeeping
{
    /**
    * Find, and if specified, delete, old records
    *
    * @param int $record_count
    * @param int $type
    * @param int $vendor_id
    * @param int $older_than_date
    * @param boolean $delete
    */
    public static function find_old_records(&$record_count, $type, $vendor_id, $older_than_date = 0, $delete = false)
    {
        $nb_database = nbf_cms::$interop->database;
        $records_to_delete = array();
        if ($older_than_date > 0) //Don't delete everything!
        {
            switch ($type)
            {
                case 1:
                    //CMS Users
                    $records_to_delete = nbf_cms::$interop->find_old_user_records($older_than_date);
                    if (count($records_to_delete) > 0 && $delete)
                    {
                        nbf_cms::$interop->delete_users(array_keys($records_to_delete));
                    }
                    //Remove from account expiry, contacts, user admin
                    $sql = "DELETE FROM #__nbill_account_expiry WHERE user_id IN (" . implode(",", array_keys($records_to_delete)) . ")";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                    $sql = "UPDATE #__nbill_contact SET user_id = -1 WHERE user_id IN (" . implode(",", array_keys($records_to_delete)) . ")";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                    $sql = "DELETE FROM #__nbill_user_admin WHERE user_id IN (" . implode(",", array_keys($records_to_delete)) . ")";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                    $record_count = count($records_to_delete);
                    break;
                case 2:
                    //Potential Clients
                case 3:
                    //Clients
                    $sql = "SELECT #__nbill_entity.id AS entity_id, #__nbill_entity.company_name, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS `name`,
                        #__nbill_contact.user_id, MAX(#__nbill_pending_orders.timestamp) AS max_pending, MAX(#__nbill_orders.start_date) AS max_order_start,
                        MAX(#__nbill_orders.last_due_date) AS max_order_last_due, MAX(#__nbill_orders.next_due_date) AS max_order_next_due,
                        MAX(#__nbill_orders.expiry_date) AS max_order_expiry, MAX(#__nbill_document.document_date) AS max_doc_date,
                        MAX(#__nbill_document.date_written_off) AS max_wo_date, MAX(#__nbill_account_expiry.expiry_date) AS max_account_expiry
                        FROM #__nbill_entity
                        LEFT JOIN (#__nbill_entity_contact INNER JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id) ON #__nbill_entity.id = #__nbill_entity_contact.entity_id
                        LEFT JOIN #__nbill_pending_orders ON #__nbill_pending_orders.client_id = #__nbill_entity.id
                        LEFT JOIN #__nbill_orders ON #__nbill_orders.client_id = #__nbill_entity.id
                        LEFT JOIN #__nbill_document ON #__nbill_document.entity_id = #__nbill_entity.id
                        LEFT JOIN #__nbill_account_expiry ON #__nbill_account_expiry.user_id = #__nbill_contact.user_id
                        WHERE #__nbill_entity.last_updated < $older_than_date ";
                    switch ($type)
                    {
                        case 2: //Potential Clients
                            $sql .= "AND #__nbill_entity.is_client = 0 AND #__nbill_entity.is_supplier = 0 ";
                            break;
                        case 3: //Clients
                            $sql .= "AND #__nbill_entity.is_client = 1 AND #__nbill_entity.is_supplier = 0 ";
                            break;
                    }
                    $sql .= "GROUP BY #__nbill_entity.id
                        HAVING (max_pending IS NULL OR max_pending < $older_than_date)
                        AND (max_order_start IS NULL OR max_order_start < $older_than_date)
                        AND (max_order_last_due IS NULL OR max_order_last_due < $older_than_date)
                        AND (max_order_next_due IS NULL OR max_order_next_due < $older_than_date)
                        AND (max_order_expiry IS NULL OR max_order_expiry < $older_than_date)
                        AND (max_doc_date IS NULL OR max_doc_date < $older_than_date)
                        AND (max_wo_date IS NULL OR max_wo_date < $older_than_date)
                        AND (max_account_expiry IS NULL OR max_account_expiry < $older_than_date)
                        ORDER BY CONCAT(#__nbill_entity.company_name, CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name))";
                    $nb_database->setQuery($sql);
                    $clients = $nb_database->loadObjectList();
                    if (!$clients)
                    {
                        $clients = array();
                    }
                    foreach ($clients as $client)
                    {
                        $records_to_delete[$client->entity_id] = $client->company_name;
                        if (nbf_common::nb_strlen($client->company_name) > 0 && nbf_common::nb_strlen($client->name) > 0)
                        {
                            $records_to_delete[$client->entity_id] .= " (";
                        }
                        $records_to_delete[$client->entity_id] .= $client->name;
                        if (nbf_common::nb_strlen($client->company_name) > 0 && nbf_common::nb_strlen($client->name) > 0)
                        {
                            $records_to_delete[$client->entity_id] .= ")";
                        }
                        if (strlen($records_to_delete[$client->entity_id]) == 0)
                        {
                            $records_to_delete[$client->entity_id] = NBILL_UNKNOWN;
                        }
                    }
                    $record_count = count($records_to_delete);
                    if ($record_count > 0 && $delete)
                    {
                        nbf_common::load_language("clients");
                        $task = "silent";
                        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/clients.php");
                        deleteClient(array_keys($records_to_delete));
                    }
                    break;
                case 4:
                    //Suppliers
                    $sql = "SELECT #__nbill_entity.id AS entity_id, #__nbill_entity.company_name, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS `name`,
                            #__nbill_contact.user_id, MAX(#__nbill_document.document_date) AS max_doc_date, MAX(#__nbill_document.date_written_off) AS max_wo_date, MAX(#__nbill_transaction.`date`) AS max_tx_date
                        FROM #__nbill_entity
                        LEFT JOIN (#__nbill_entity_contact INNER JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id) ON #__nbill_entity.id = #__nbill_entity_contact.entity_id
                        LEFT JOIN #__nbill_document ON #__nbill_document.entity_id = #__nbill_entity.id
                        LEFT JOIN #__nbill_transaction ON #__nbill_entity.id = #__nbill_transaction.entity_id
                        WHERE #__nbill_entity.last_updated < $older_than_date
                        AND #__nbill_entity.is_client = 0 AND #__nbill_entity.is_supplier = 1
                        GROUP BY #__nbill_entity.id
                        HAVING (max_doc_date IS NULL OR max_doc_date < $older_than_date) AND (max_wo_date IS NULL OR max_wo_date < $older_than_date) AND (max_tx_date IS NULL OR max_tx_date < $older_than_date)
                        ORDER BY CONCAT(#__nbill_entity.company_name, CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name))";
                    $nb_database->setQuery($sql);
                    $suppliers = $nb_database->loadObjectList();
                    if (!$suppliers)
                    {
                        $suppliers = array();
                    }
                    foreach ($suppliers as $supplier)
                    {
                        $records_to_delete[$supplier->entity_id] = $supplier->company_name;
                        if (nbf_common::nb_strlen($supplier->company_name) > 0 && nbf_common::nb_strlen($supplier->name) > 0)
                        {
                            $records_to_delete[$supplier->entity_id] .= " (";
                        }
                        $records_to_delete[$supplier->entity_id] .= $supplier->name;
                        if (nbf_common::nb_strlen($supplier->company_name) > 0 && nbf_common::nb_strlen($supplier->name) > 0)
                        {
                            $records_to_delete[$supplier->entity_id] .= ")";
                        }
                        if (strlen($records_to_delete[$supplier->entity_id]) == 0)
                        {
                            $records_to_delete[$supplier->entity_id] = NBILL_UNKNOWN;
                        }
                    }
                    $record_count = count($records_to_delete);
                    if ($record_count > 0 && $delete)
                    {
                        $task = "silent";
                        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/suppliers.php");
                        deleteSupplier(array_keys($records_to_delete));
                    }
                    break;
                case 5:
                    //Orphan Contacts
                    $sql = "SELECT id, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS `name` FROM #__nbill_contact
                            LEFT JOIN #__nbill_entity_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id
                            WHERE #__nbill_entity_contact.entity_id IS NULL
                            AND #__nbill_contact.last_updated < $older_than_date";
                    $nb_database->setQuery($sql);
                    $records_to_delete = $nb_database->loadAssocList('id');
                    $record_count = count($records_to_delete);
                    if ($record_count > 0 && $delete)
                    {
                        nbf_common::fire_event("contact_deleted", array("ids"=>implode(",", array_keys($records_to_delete))));
                        $sql = "DELETE FROM #__nbill_contact WHERE id IN (" . implode(",", array_keys($records_to_delete)) . ")";
                        $nb_database->setQuery($sql);
                        $nb_database->query();
                    }
                    break;
                case 6:
                    //Pending Orders
                    $sql = $delete ? "DELETE" : "SELECT id, RTRIM(CONCAT(id, ' ', client_name)) AS link_text";
                    $sql .= " FROM #__nbill_pending_orders WHERE vendor_id = $vendor_id AND timestamp < $older_than_date";
                    if ($delete)
                    {
                        $nb_database->setQuery($sql);
                        $nb_database->query();
                        $record_count = $nb_database->getAffectedRows();
                    }
                    else
                    {
                        $sql .= " ORDER BY timestamp";
                        $nb_database->setQuery($sql);
                        $records_to_delete = $nb_database->loadAssocList('id');
                        $record_count = count($records_to_delete);
                    }
                    break;
                case 7:
                    //Orders
                    $sql = "SELECT id, order_no FROM #__nbill_orders
                            WHERE vendor_id = $vendor_id AND start_date < $older_than_date AND last_due_date < $older_than_date AND next_due_date < $older_than_date
                            ORDER BY order_no";
                    $nb_database->setQuery($sql);
                    $records_to_delete = $nb_database->loadAssocList('id');
                    $record_count = count($records_to_delete);
                    if ($record_count > 0 && $delete)
                    {
                        $sql = "SELECT id, order_status FROM #__nbill_orders WHERE id IN (" . implode(",", array_keys($records_to_delete)) . ")";
                        $nb_database->setQuery($sql);
                        $order_statuses = $nb_database->loadObjectList();
                        if ($order_statuses && count($order_statuses) > 0)
                        {
                            foreach ($order_statuses as $order_status)
                            {
                                nbf_common::fire_event("order_status_updated", array("id"=>$order_status->id, "old_status"=>$order_status->order_status, "new_status"=>""));
                            }
                        }
                        nbf_common::fire_event("order_deleted", array("ids"=>implode(",", array_keys($records_to_delete))));
                        $task = "silent";
                        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/orders.php");
                        deleteOrders(array_keys($records_to_delete));
                    }
                    break;
                case 8:
                    //Invoices
                    $sql = "SELECT #__nbill_document.id, #__nbill_document.document_no
                            FROM #__nbill_document
                            LEFT JOIN #__nbill_document_transaction ON #__nbill_document.id = #__nbill_document_transaction.document_id
                            LEFT JOIN #__nbill_transaction ON #__nbill_document_transaction.transaction_id = #__nbill_transaction.id
                            WHERE #__nbill_document.vendor_id = $vendor_id AND #__nbill_document.document_type = 'IN'
                            AND #__nbill_document.document_date < $older_than_date
                            AND #__nbill_document.date_written_off < $older_than_date
                            GROUP BY #__nbill_document.id
                            HAVING (MAX(#__nbill_transaction.date) IS NULL OR MAX(#__nbill_transaction.date) < $older_than_date)
                            ORDER BY #__nbill_document.document_no, #__nbill_document.id";
                    $nb_database->setQuery($sql);
                    $records_to_delete = $nb_database->loadAssocList('id');
                    $record_count = count($records_to_delete);
                    if ($record_count > 0 && $delete)
                    {
                        $task = "silent";
                        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/invoices.php");
                        $_REQUEST['action'] = "invoices";
                        deleteInvoices(array_keys($records_to_delete));
                        $_REQUEST['action'] = "housekeeping";
                    }
                    break;
                case 9:
                    //Quotes
                    $sql = "SELECT #__nbill_document.id, #__nbill_document.document_no FROM #__nbill_document
                            LEFT JOIN #__nbill_orders ON #__nbill_document.id = #__nbill_orders.related_quote_id
                            LEFT JOIN #__nbill_document AS invoices ON #__nbill_document.id = invoices.related_document_id
                            LEFT JOIN #__nbill_document_transaction ON invoices.id = #__nbill_document_transaction.document_id
                            LEFT JOIN #__nbill_transaction ON #__nbill_document_transaction.transaction_id = #__nbill_transaction.id
                            WHERE #__nbill_document.vendor_id = $vendor_id AND #__nbill_document.document_type = 'QU'
                            AND #__nbill_document.document_date < $older_than_date
                            GROUP BY #__nbill_document.id
                            HAVING (MAX(#__nbill_orders.start_date) IS NULL OR MAX(#__nbill_orders.start_date) < $older_than_date)
                            AND (MAX(#__nbill_orders.last_due_date) IS NULL OR MAX(#__nbill_orders.last_due_date) < $older_than_date)
                            AND (MAX(#__nbill_orders.next_due_date) IS NULL OR MAX(#__nbill_orders.next_due_date) < $older_than_date)
                            AND (MAX(invoices.document_date) IS NULL OR MAX(invoices.document_date) < $older_than_date)
                            AND (MAX(invoices.date_written_off) IS NULL OR MAX(invoices.date_written_off) < $older_than_date)
                            AND (MAX(#__nbill_transaction.date) IS NULL OR MAX(#__nbill_transaction.date) < $older_than_date)";
                    $nb_database->setQuery($sql);
                    $records_to_delete = $nb_database->loadAssocList('id');
                    $record_count = count($records_to_delete);
                    if ($record_count > 0 && $delete)
                    {
                        $task = "silent";
                        $_REQUEST['action'] = "quotes";
                        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/invoices.php");
                        deleteInvoices(array_keys($records_to_delete));
                        $_REQUEST['action'] = "housekeeping";
                    }
                    break;
                case 10:
                    //Gateway Tx Data
                    $sql = "SELECT #__nbill_gateway_tx.id, #__nbill_gateway_tx.id AS link_text
                            FROM #__nbill_gateway_tx
                            LEFT JOIN #__nbill_orders ON #__nbill_gateway_tx.id = #__nbill_orders.gateway_txn_id
                            LEFT JOIN #__nbill_document ON #__nbill_gateway_tx.id = #__nbill_document.gateway_txn_id
                            LEFT JOIN #__nbill_document AS invoices ON #__nbill_document.id IN (#__nbill_gateway_tx.document_ids)
                            LEFT JOIN #__nbill_document_transaction ON invoices.id = #__nbill_document_transaction.document_id
                            LEFT JOIN #__nbill_transaction ON #__nbill_document_transaction.transaction_id = #__nbill_transaction.id
                            WHERE #__nbill_gateway_tx.vendor_id = $vendor_id AND #__nbill_gateway_tx.last_updated < $older_than_date
                            GROUP BY #__nbill_gateway_tx.id
                            HAVING (MAX(#__nbill_orders.start_date) IS NULL OR MAX(#__nbill_orders.start_date) < $older_than_date)
                            AND (MAX(#__nbill_orders.last_due_date) IS NULL OR MAX(#__nbill_orders.last_due_date) < $older_than_date)
                            AND (MAX(#__nbill_orders.next_due_date) IS NULL OR MAX(#__nbill_orders.next_due_date) < $older_than_date)
                            AND (MAX(#__nbill_document.document_date) IS NULL OR MAX(#__nbill_document.document_date) < $older_than_date)
                            AND (MAX(#__nbill_document.date_written_off) IS NULL OR MAX(#__nbill_document.date_written_off) < $older_than_date)
                            AND (MAX(#__nbill_document.document_date) IS NULL OR MAX(#__nbill_document.document_date) < $older_than_date)
                            AND (MAX(#__nbill_document.date_written_off) IS NULL OR MAX(#__nbill_document.date_written_off < $older_than_date))
                            AND (MAX(#__nbill_transaction.date) IS NULL OR MAX(#__nbill_transaction.date < $older_than_date))";
                    $nb_database->setQuery($sql);
                    $records_to_delete = $nb_database->loadAssocList('id');
                    $record_count = count($records_to_delete);
                    if ($record_count > 0 && $delete)
                    {
                        $sql = "DELETE FROM #__nbill_gateway_tx WHERE id IN (" . implode(",", array_keys($records_to_delete)) . ")";
                        $nb_database->setQuery($sql);
                        $nb_database->query();
                    }
                    break;
                case 11:
                    //Income
                    nbf_common::load_language("income");
                    $sql = "SELECT id, transaction_no FROM #__nbill_transaction
                            WHERE vendor_id = $vendor_id AND transaction_type = 'IN'
                            AND date < $older_than_date
                            ORDER BY transaction_no, id";
                    $nb_database->setQuery($sql);
                    $records_to_delete = $nb_database->loadAssocList('id');
                    foreach ($records_to_delete as &$record_to_delete)
                    {
                        if (nbf_common::nb_strlen($record_to_delete) == 0)
                        {
                            $record_to_delete = NBILL_UNNUMBERED;
                        }
                    }
                    reset($records_to_delete);
                    $record_count = count($records_to_delete);
                    if ($record_count > 0 && $delete)
                    {
                        $task = "silent";
                        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/income.php");
                        deleteIncome(array_keys($records_to_delete));
                    }
                    break;
                case 12:
                    //Expenditure
                    nbf_common::load_language("expenditure");
                    $sql = "SELECT id, transaction_no FROM #__nbill_transaction
                            WHERE vendor_id = $vendor_id AND transaction_type = 'EX'
                            AND date < $older_than_date
                            ORDER BY transaction_no, id";
                    $nb_database->setQuery($sql);
                    $records_to_delete = $nb_database->loadAssocList('id');
                    foreach ($records_to_delete as &$record_to_delete)
                    {
                        if (nbf_common::nb_strlen($record_to_delete) == 0)
                        {
                            $record_to_delete = NBILL_EXP_UNNUMBERED;
                        }
                    }
                    reset($records_to_delete);
                    $record_count = count($records_to_delete);
                    if ($record_count > 0 && $delete)
                    {
                        $task = "silent";
                        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/expenditure.php");
                        deleteExpenditure(array_keys($records_to_delete));
                    }
                    break;
                case 13:
                    //Supporting documents
                    $sql = "SELECT supporting_docs_path FROM #__nbill_configuration WHERE id = 1";
                    $nb_database->setQuery($sql);
                    $path = $nb_database->loadResult();

                    clearstatcache();
                    $records_to_delete = self::get_supporting_docs($path, $older_than_date);
                    $record_count = count($records_to_delete);
                    if ($record_count > 0 && $delete)
                    {
                        foreach ($records_to_delete as $file)
                        {
                            @unlink($file);
                        }
                    }
            }
        }
        return $records_to_delete;
    }

    /**
    * Returns an array of all files older than the given date within the given folder (recursive)
    */
    public static function get_supporting_docs($path, $older_than_date)
    {
        static $recursive_count = 0;
        $recursive_count++;
        if ($recursive_count > 1000)
        {
            //Stuck in a loop
            return array();
        }
        $ret_val = array();
        $these_docs = @array_diff(scandir(realpath($path)), array('.', '..'));
        if ($these_docs && is_array($these_docs))
        {
            foreach ($these_docs as $this_doc)
            {
                $this_file = @realpath($path . "/" . $this_doc);
                if ($this_file)
                {
                    if (is_dir($this_file))
                    {
                        $ret_val = array_merge($ret_val, self::get_supporting_docs($this_file, $older_than_date));
                    }
                    else
                    {
                        if (filemtime($this_file) < $older_than_date)
                        {
                            $ret_val[$this_file] = $this_file;
                        }
                    }
                }
            }
        }
        return $ret_val;
    }

    public static function cron_do_housekeeping($send_email, $send_email_if_none, $email_address, $show_details, $hk_units, $hk_no_of_units, $record_types, $vendor)
    {
        $nb_database = nbf_cms::$interop->database;
        nbf_common::load_language("housekeeping");

        //Work out the older than date
        $older_than_date = nbf_common::nb_strtotime("- $hk_no_of_units $hk_units");
        //Find midnight so we can delete everything from the previous day and beyond
        $date_parts = nbf_common::nb_getdate($older_than_date);
        $older_than_date = nbf_common::nb_mktime(0, 0, 0, $date_parts['mon'], $date_parts['mday'], $date_parts['year']);

        $sql = "SELECT id, vendor_name FROM #__nbill_vendor";
        if ($vendor)
        {
            $sql .= " WHERE id = " . intval($vendor);
        }
        $sql .= " ORDER BY id";
        $nb_database->setQuery($sql);
        $vendor_ids = $nb_database->loadAssocList('id');

        $records_to_delete = array();
        $record_count = array();
        $total_record_count_by_vendor = array();
        $total_record_count = 0;
        foreach ($vendor_ids as $vendor_id=>$vendor_name)
        {
            $record_count[$vendor_id] = 0;
            if ($send_email && $show_details)
            {
                foreach ($record_types as $record_type)
                {
                    $records_to_delete[$vendor_id][$record_type] = nbf_housekeeping::find_old_records($record_count[$vendor_id], $record_type, $vendor_id, $older_than_date, false);
                }
            }
            $record_count[$vendor_id] = 0;
            $total_record_count_by_vendor[$vendor_id] = 0;
            foreach ($record_types as $record_type)
            {
                //Do the actual deletion
                nbf_housekeeping::find_old_records($record_count[$vendor_id], $record_type, $vendor_id, $older_than_date, true);
                $total_record_count_by_vendor[$vendor_id] += $record_count[$vendor_id];
                $total_record_count += $record_count[$vendor_id];
            }
        }

        if ($send_email && ($total_record_count > 0 || $send_email_if_none))
        {
            $message = "";
            foreach ($vendor_ids as $vendor_id=>$vendor_name)
            {
                $message .= "*" . sprintf(NBILL_HOUSEKEEPING_RECORDS_DELETED, $total_record_count_by_vendor[$vendor_id]) . " " . NBILL_FOR . " '" . $vendor_name . "'*\n\n";
                if ($show_details)
                {
                    foreach ($records_to_delete[$vendor_id] as $record_type=>$record_array)
                    {
                        if (count($record_array) > 0)
                        {
                            $message .= constant("NBILL_HOUSEKEEPING_TYPE_" . $record_type) . ":\n";
                            $message .= implode(", ", $record_array) . "\n\n";
                        }
                    }
                }
                $message .= "\n\n";
            }

            if (nbf_common::nb_strpos($email_address, ";") !== false)
            {
                $first_email_address = substr($email_address, 0, nbf_common::nb_strpos($email_address, ";"));
            }
            else
            {
                $first_email_address = $email_address;
            }
            nbf_cms::$interop->send_email($first_email_address, $first_email_address, explode(";", $email_address), NBILL_AUTO_HOUSEKEEPING_SUBJECT, $message);
        }

        echo str_replace("\n", "<br />", $message);
    }
}