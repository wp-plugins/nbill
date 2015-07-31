<?php
/**
* Class file just containing static methods relating to transaction searches.
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* Class just contains static functions for use anywhere within the code
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_tx
{
    public static function load_transaction_details($g_tx_id, $income_reference = false, $latest_only = false, $psp_reference_retry = false)
    {
        //Return information about the order(s) for the given transaction ID (optionally checking the income reference)
        $nb_database = nbf_cms::$interop->database;

        $tx_details = new stdClass;
        $tx_details->pending_order = null;
        $tx_details->orders = array();
        $tx_details->invoices = array();
        $tx_details->income = array();
        $tx_details->expenditure = array();
        $tx_details->g_tx_id = $g_tx_id;

        if (intval($g_tx_id) != 0)
        {
            //Get transaction record
            $sql = "SELECT pending_order_id, document_ids, net_amount, tax_amount, shipping_amount, shipping_tax_amount, vendor_id, form_id
                            FROM #__nbill_gateway_tx WHERE id = " . intval($g_tx_id);
            $nb_database->setQuery($sql);
            $txn = null;
            $nb_database->loadObject($txn);

            $tx_details->pending_order = null;
            $tx_details->orders = array();
            

            //Get invoice details, if known
            $sql = "SELECT #__nbill_document.id, #__nbill_document.vendor_id, #__nbill_document.entity_id, #__nbill_document.document_no,
                                    #__nbill_document.billing_name, #__nbill_document.billing_address, #__nbill_document.reference,
                                    #__nbill_document.document_date, #__nbill_document.currency, #__nbill_document.total_net,
                                    #__nbill_document.total_tax, #__nbill_document.total_shipping, #__nbill_document.total_shipping_tax,
                                    #__nbill_document.total_gross, " . nbf_cms::$interop->cms_database_enum->table_user .
                                    "." . nbf_cms::$interop->cms_database_enum->column_user_username . " AS username
                                    FROM #__nbill_document
                                    LEFT JOIN (#__nbill_entity INNER JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id) ON #__nbill_document.entity_id = #__nbill_entity.id
                                    LEFT JOIN " . nbf_cms::$interop->cms_database_enum->table_user . " ON
                                    #__nbill_contact.user_id = " . nbf_cms::$interop->cms_database_enum->table_user .
                                    "." . nbf_cms::$interop->cms_database_enum->column_user_id . "
                                    WHERE #__nbill_document.gateway_txn_id = " . intval($g_tx_id );
            if ($txn)
            {
                if ($txn->document_ids)
                {
                    $sql .= " UNION SELECT #__nbill_document.id, #__nbill_document.vendor_id, #__nbill_document.entity_id, #__nbill_document.document_no,
                                    #__nbill_document.billing_name, #__nbill_document.billing_address, #__nbill_document.reference,
                                    #__nbill_document.document_date, #__nbill_document.currency, #__nbill_document.total_net,
                                    #__nbill_document.total_tax, #__nbill_document.total_shipping, #__nbill_document.total_shipping_tax,
                                    #__nbill_document.total_gross, " . nbf_cms::$interop->cms_database_enum->table_user .
                                    "." . nbf_cms::$interop->cms_database_enum->column_user_username . " AS username
                                    FROM #__nbill_document
                                    LEFT JOIN (#__nbill_entity INNER JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id) ON #__nbill_document.entity_id = #__nbill_entity.id
                                    LEFT JOIN " . nbf_cms::$interop->cms_database_enum->table_user . " ON
                                    #__nbill_contact.user_id = " . nbf_cms::$interop->cms_database_enum->table_user .
                                    "." . nbf_cms::$interop->cms_database_enum->column_user_id . "
                                    WHERE #__nbill_document.id IN (" . $txn->document_ids . ")
                                    ORDER BY document_no DESC, document_date DESC";
                }
            }
            if ($latest_only)
            {
                $sql .= " LIMIT 1";
            }
            $nb_database->setQuery($sql);
            $tx_details->invoices = $nb_database->loadObjectList();
            if (!$tx_details->invoices)
            {
                $tx_details->invoices = array();
            }
        }

        //Get income record, if known
        $sql = "SELECT #__nbill_transaction.id, #__nbill_transaction.transaction_no, #__nbill_transaction.transaction_type, #__nbill_transaction.vendor_id, #__nbill_transaction.name,
                        #__nbill_transaction.for, #__nbill_transaction.currency, #__nbill_transaction.amount, #__nbill_transaction.date, #__nbill_transaction.document_ids
                        FROM #__nbill_transaction WHERE #__nbill_transaction.transaction_type = 'IN' ";
        if (intval($g_tx_id) != 0)
        {
            $sql .= "AND #__nbill_transaction.g_tx_id = " . intval($g_tx_id) . " ";
            if ($income_reference)
            {
                $sql .= "OR #__nbill_transaction.reference = '" . $g_tx_id . "' ";
            }
        }
        else
        {
            $sql .= "AND #__nbill_transaction.reference = '" . $g_tx_id . "' ";
        }
        $sql .= "ORDER BY #__nbill_transaction.transaction_no, #__nbill_transaction.date DESC";
        if ($latest_only)
        {
            $sql .= " LIMIT 1";
        }
        $nb_database->setQuery($sql);
        $tx_details->income = $nb_database->loadObjectList();
        if ($tx_details->income)
        {
            //Load invoice numbers, if applicable
            $document_no_array = array();
            foreach ($tx_details->income as $key=>$income)
            {
                if (nbf_common::nb_strlen($income->document_ids) > 0)
                {
                    $sql = "SELECT #__nbill_document.id, #__nbill_document.document_no FROM #__nbill_document WHERE #__nbill_document.id IN (" . $income->document_ids . ")";
                    $nb_database->setQuery($sql);
                    $document_nos = $nb_database->loadObjectList();
                    $document_no_array = array();
                    if ($document_nos)
                    {
                        foreach ($document_nos as $document_no)
                        {
                            $document_no_array[$document_no->id] = $document_no->document_no;
                        }
                    }
                    $tx_details->income[$key]->document_nos = $document_no_array;
                    $i = 0;
                }
            }
        }

        $tx_details->expenditure = array();
        

        if (!$psp_reference_retry && strlen(strval($g_tx_id)) > 0)
        {
            $sql = "SELECT id FROM #__nbill_gateway_tx WHERE psp_reference = '" . $g_tx_id . "' ORDER BY id DESC LIMIT 1";
            $nb_database->setQuery($sql);
            $alt_g_tx_id = intval($nb_database->loadResult());
            if (!$alt_g_tx_id)
            {
                $sql = "SELECT id FROM #__nbill_gateway_tx WHERE psp_reference LIKE '%" . $g_tx_id . "%' ORDER BY id DESC LIMIT 1";
                $nb_database->setQuery($sql);
                $alt_g_tx_id = intval($nb_database->loadResult());
            }
            if ($alt_g_tx_id)
            {
                $alt_tx_details = self::load_transaction_details($alt_g_tx_id, $income_reference, $latest_only, true);
                if ($alt_tx_details)
                {
                    if ($tx_details->pending_order == null)
                    {
                        $tx_details->pending_order = $alt_tx_details->pending_order;
                    }
                    $tx_details->orders = array_filter(array_merge($tx_details->orders, $alt_tx_details->orders));
                    $tx_details->invoices = array_filter(array_merge($tx_details->invoices, $alt_tx_details->invoices));
                    $tx_details->income = array_filter(array_merge($tx_details->income, $alt_tx_details->income));
                    $tx_details->expenditure = array_filter(array_merge($tx_details->expenditure, $alt_tx_details->expenditure));
                }
            }
        }

        if ($tx_details->pending_order != null || count($tx_details->orders) > 0 || count($tx_details->invoices) > 0 || count($tx_details->income) > 0 || count($tx_details->expenditure) > 0)
        {
            return $tx_details;
        }
        else
        {
            return false;
        }
    }

    public static function generate_txn_text_output($tx_details, $show_message_if_empty = false)
    {
        nbf_common::load_language("tx_search");

        //Takes the transaction details (returned by the load_transaction_details() function) and returns a human-readable summary as plain text (for e-mails)
        $output = NBILL_TXN_DETAILS . " (" . intval($tx_details->g_tx_id) . ")";
        $date_format = nbf_common::get_date_format();

        if (!$tx_details)
        {
            $output = "";
            if ($show_message_if_empty)
            {
                $output = NBILL_TXN_NOT_FOUND;
            }
            return $output;
        }

        //Income
        if (count($tx_details->income) > 0)
        {
            foreach ($tx_details->income as $income)
            {
                $output .= "\n\n";
                $output .= nbf_common::nb_strtoupper(NBILL_TXN_TYPE_INCOME) . ":\n";
                $output .= NBILL_TXN_RECEIPT_NO . ": " . ($income->transaction_no ? $income->transaction_no : NBILL_TXN_RECEIPT_NO_UNKNOWN) . "\n";
                $output .= NBILL_TXN_DATE . ": " . nbf_common::nb_date($date_format, $income->date) . "\n";
                $output .= NBILL_TXN_AMOUNT . ": " . format_number($income->amount, 'currency_grand', null, null, null, $income->currency) . "\n";
                $output .= NBILL_TXN_CLIENT . ": $income->name\n";
                if (nbf_common::nb_strlen($income->for) > 0)
                {
                    $output .= NBILL_TXN_RELATING_TO . ": $income->for\n";
                }
                else
                {
                    if (nbf_common::nb_strlen($income->document_ids) > 0 && count($income->document_nos) > 0)
                    {
                        $output .= NBILL_TXN_RELATING_TO . " " . NBILL_TXN_INVOICE_NO . ": " . implode(", ", $income->document_nos);
                    }
                }
            }
        }

        

        //Invoices
        if (count($tx_details->invoices) > 0)
        {
            foreach ($tx_details->invoices as $invoice)
            {
                $output .= "\n\n";
                $output .= nbf_common::nb_strtoupper(NBILL_TXN_TYPE_INVOICE) . ":\n";
                $output .= NBILL_TXN_INVOICE_NO . ": $invoice->document_no\n";
                $output .= NBILL_TXN_DATE . ": " . nbf_common::nb_date($date_format, $invoice->document_date) . "\n";
                $output .= NBILL_TXN_AMOUNT . ": " . format_number($invoice->total_gross, 'currency_grand', null, null, null, $invoice->currency) . "\n";
                $output .= NBILL_TXN_CLIENT . ": $invoice->billing_name\n";
                $output .= NBILL_TXN_USERNAME . ": $invoice->username\n";
            }
        }

        

        return $output;
    }

    public static function generate_txn_html_output($tx_details, $include_links = true, $return_url = "", $show_message_if_empty = false)
    {
        nbf_common::load_language("tx_search");

        if (!$tx_details)
        {
            $output = "";
            if ($show_message_if_empty)
            {
                $output = "<p>" . NBILL_TXN_NOT_FOUND . "</p>";
            }
            return $output;
        }

        //Takes the transaction details (returned by the load_transaction_details() function) and returns a human-readable summary as HTML (for browser display). Links to relevant records in the back end are also generated unless suppressed by the optional parameter. A return_url allows control to return to the calling page after viewing a record.
        $output = "<p>" . NBILL_TXN_DETAILS . "</p>";
        $date_format = nbf_common::get_date_format();
        if (nbf_common::nb_strlen($return_url) > 0)
        {
            $return_url = "&return=" . base64_encode($return_url);
        }

        //Income
        if (count($tx_details->income) > 0)
        {
            $output .= "<span class=\"nbill-transaction-heading\">" . nbf_common::nb_strtoupper(NBILL_TXN_TYPE_INCOME) . "</span>";
            $output .= "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" class=\"nbill-transaction-table\">";
            $output .= "<tr>";
            $output .= "<th>" . NBILL_TXN_RECEIPT_NO . "</th>";
            $output .= "<th>" . NBILL_TXN_DATE . "</th>";
            $output .= "<th>" . NBILL_TXN_AMOUNT . "</th>";
            $output .= "<th>" . NBILL_TXN_CLIENT . "</th>";
            $output .= "<th>" . NBILL_TXN_RELATING_TO . "</th>";
            $output .= "</tr>";

            foreach ($tx_details->income as $income)
            {
                $url = nbf_cms::$interop->admin_page_prefix . "&action=income&task=edit&cid=$income->id" . $return_url;
                $output .= "<tr>";
                $output .= "<td>";
                if ($include_links)
                {
                    $output .= "<a target=\"_blank\" href=\"$url\">" . ($income->transaction_no ? $income->transaction_no : NBILL_TXN_RECEIPT_NO_UNKNOWN) . "</a>";
                }
                else
                {
                    $output .= ($income->transaction_no ? $income->transaction_no : NBILL_TXN_RECEIPT_NO_UNKNOWN);
                }
                $output .= "</td>";
                $output .= "<td>" . nbf_common::nb_date($date_format, $income->date) . "</td>";
                $output .= "<td>" . format_number($income->amount, 'currency_grand', null, null, null, $income->currency) . "</td>";
                $output .= "<td>$income->name</td>";
                $output .= "<td>";
                $for_string = "";
                if (nbf_common::nb_strlen($income->for) > 0)
                {
                    $for_string .= "$income->for";
                }
                else
                {
                    if (nbf_common::nb_strlen($income->document_ids) > 0 && count($income->document_nos) > 0)
                    {
                        foreach ($income->document_nos as $document_id=>$document_no)
                        {
                            $url = nbf_cms::$interop->admin_page_prefix . "&action=invoices&task=edit&cid=$document_id" . $return_url;
                            if (nbf_common::nb_strlen($for_string) > 0)
                            {
                                $for_string .= ", ";
                            }
                            else
                            {
                                $for_string = NBILL_TXN_INVOICE_NO . " ";
                            }
                            $for_string .= "<a target=\"_blank\" href=\"$url\">" . $document_no . "</a>";
                        }
                    }
                }
                if (nbf_common::nb_strlen($for_string) > 0)
                {
                    $output .= $for_string;
                }
                else
                {
                    $output .= "&nbsp;";
                }
                $output .= "</td>";
                $output .= "</tr>";
            }

            $output .= "</table><br /><br />";
        }

        

        //Invoices
        if (count($tx_details->invoices) > 0)
        {
            $output .= "<span class=\"nbill-transaction-heading\">" . nbf_common::nb_strtoupper(NBILL_TXN_TYPE_INVOICE) . "</span>";
            $output .= "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" class=\"nbill-transaction-table\">";
            $output .= "<tr>";
            $output .= "<th>" . NBILL_TXN_INVOICE_NO . "</th>";
            $output .= "<th>" . NBILL_TXN_DATE . "</th>";
            $output .= "<th>" . NBILL_TXN_AMOUNT . "</th>";
            $output .= "<th>" . NBILL_TXN_CLIENT . "</th>";
            $output .= "<th>" . NBILL_TXN_USERNAME . "</th>";
            $output .= "</tr>";

            foreach ($tx_details->invoices as $invoice)
            {
                $url = nbf_cms::$interop->admin_page_prefix . "&action=invoices&task=edit&cid=$invoice->id" . $return_url;
                $output .= "<tr>";
                $output .= "<td>";
                if ($include_links)
                {
                    $output .= "<a target=\"_blank\" href=\"$url\">$invoice->document_no</a>";
                }
                else
                {
                    $output .= $invoice->document_no;
                }
                $output .= "</td>";
                $output .= "<td>" . nbf_common::nb_date($date_format, $invoice->document_date) . "</td>";
                $output .= "<td>" . format_number($invoice->total_gross, 'currency_grand', null, null, null, $invoice->currency) . "</td>";
                $url = nbf_cms::$interop->admin_page_prefix . "&action=clients&task=edit&cid=$invoice->entity_id" . $return_url;
                $output .= "<td>";
                if ($include_links)
                {
                    $output .= "<a target=\"_blank\" href=\"$url\">" . $invoice->billing_name . "</a>";
                }
                else
                {
                    $output .= $invoice->billing_name;
                }
                $output .= "</td>";
                $output .= "<td>" . $invoice->username . " </td>";
                $output .= "</tr>";
            }

            $output .= "</table><br /><br />";
        }

        

        return $output;
    }
}