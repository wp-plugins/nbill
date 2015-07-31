<?php
/**
* Mapper to load upcoming orders from the database
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class OrdersDueMapper extends nBillWidgetMapper
{
    public function loadOrdersDue(OrdersDueWidget $widget)
    {
        $due_date = strtotime("+" . $widget->number_of_units . " " . $widget->range_units);

        //If we are generating invoices in advance, set the $due_date forward
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.frontend.class.php");
        if (nbf_frontend::get_display_option('due_date') && nbf_frontend::get_display_option('generate_early')) {
            $due_date = strtotime("+" . nbf_frontend::get_display_option('due_date_no_of_units') . " " . nbf_frontend::get_display_option('due_date_units'), $due_date);
        }
        $date_parts = nbf_common::nb_getdate($due_date);
        $end_of_due_date = nbf_common::nb_mktime(23, 59, 59, $date_parts["mon"], $date_parts["mday"], $date_parts["year"]);

        $sql = "SELECT #__nbill_entity.company_name,
                #__nbill_orders.next_due_date,
                TRIM(CONCAT(TRIM(#__nbill_contact.first_name), ' ', TRIM(#__nbill_contact.last_name))) AS contact_name,
                #__nbill_orders.id, #__nbill_orders.order_no, #__nbill_orders.product_name, #__nbill_orders.product_id, #__nbill_orders.client_id, #__nbill_orders.relating_to,
                #__nbill_currency.symbol AS currency_symbol,
                #__nbill_orders.net_price + #__nbill_orders.total_tax_amount + #__nbill_orders.total_shipping_price + #__nbill_orders.total_shipping_tax AS total_gross
                FROM #__nbill_orders
                LEFT JOIN #__nbill_entity ON #__nbill_orders.client_id = #__nbill_entity.id
                LEFT JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id
                LEFT JOIN #__nbill_currency ON #__nbill_orders.currency = #__nbill_currency.code
                WHERE #__nbill_orders.cancellation_date = 0
                AND (#__nbill_orders.expiry_date = 0 OR #__nbill_orders.expiry_date >= #__nbill_orders.next_due_date
                    OR (#__nbill_orders.next_due_date = 0 AND #__nbill_orders.expiry_date >= $end_of_due_date))
                AND #__nbill_orders.next_due_date > 0
                AND #__nbill_orders.next_due_date <= $end_of_due_date
                AND (#__nbill_orders.auto_renew = 1 OR (#__nbill_orders.auto_renew = 0 AND #__nbill_orders.last_due_date = 0))
                AND #__nbill_orders.order_status != 'EE'
                AND #__nbill_orders.auto_create_invoice = 1
                GROUP BY #__nbill_orders.id
                ORDER BY #__nbill_orders.next_due_date
                LIMIT " . intval($widget->max_records);
        $this->db->setQuery($sql);
        $records = $this->db->loadObjectList();
        if (!$records) {
            $records = array();
        }
        foreach ($records as $record)
        {
            $client_name = '';
            if (strlen(trim($record->company_name)) > 0) {
                $client_name = $record->company_name;
                if (strlen($record->contact_name) > 0) {
                    $client_name .= " (";
                }
            }
            $client_name .= $record->contact_name;
            if (strlen(trim($record->company_name)) > 0 && strlen($record->contact_name) > 0) {
                $client_name .= ")";
            }
            $record->client_name = $client_name;
        }
        return $records;
    }
}