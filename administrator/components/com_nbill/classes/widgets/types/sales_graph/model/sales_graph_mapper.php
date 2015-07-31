<?php
/**
* Mapper to load graph data from the database
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class SalesGraphMapper extends nBillWidgetMapper
{
    public $currency = 'USD';

    public function __construct(nbf_database $db)
    {
        parent::__construct($db);
        $this->findCurrency();
    }

    protected function findCurrency()
    {
        $sql = "SELECT vendor_currency FROM #__nbill_vendor WHERE default_vendor = 1";
        $this->db->setQuery($sql);
        $this->currency = $this->db->loadResult();
            if (!$this->currency || strlen($this->currency) != 3) {
                $sql = "SELECT vendor_currency FROM #__nbill_vendor WHERE vendor_currency > '' ORDER BY id";
                $this->db->setQuery($sql);
                $this->currency = $this->db->loadResult();
            }

        if (!$this->currency || strlen($this->currency) != 3) {
            $this->currency = 'USD';
        }
        return $this->currency;
    }

    protected function loadParams(nBillWidget $widget, $db_widget, $populate_from_request = false)
    {
        $widget = parent::loadParams($widget, $db_widget, $populate_from_request);
        if (strlen($widget->currency) != 3) {
            $widget->currency = $this->findCurrency();
        }
        return $widget;
    }

    /**
    * @param array $plot_points Array of SalesGraphPlotPoint objects
    * @param array $ledger_codes
    */
    public function loadIncomeTotal($units = SalesGraphService::UNITS_DAYS)
    {
        $by_ledger = $this->ledger_codes && count($this->ledger_codes) > 0;
        $amount_col = $by_ledger ? '#__nbill_transaction_ledger.gross_amount' : '#__nbill_transaction.amount';
        if ($this->plot_points && count($this->plot_points) > 0) {
            list($date_range_in_seconds, $oldest, $youngest) = array(0, 0, 0);
            $this->calculateRangeLimits($date_range_in_seconds, $oldest, $youngest);
            //Although verbose, this is more efficient than making MySQL convert all the dates, since they are stored as UNIX timestamps, and more accurate than trying to calculate the range in seconds (because of DST)
            $sql = "SELECT (CASE ";
            foreach ($this->plot_points as $plot_point)
            {
                $u_from = $plot_point->x_start_date->format('U');
                $u_to = $plot_point->x_end_date->format('U');
                $sql .= "WHEN `date` >= $u_from AND `date` <= $u_to THEN '$u_from-$u_to' ";
            }
            $sql .= "END) AS `range`, ";
            if ($this->include_expenditure) {
                $sql .= "SUM(CASE transaction_type
                        WHEN 'IN' THEN $amount_col
                        WHEN 'EX' THEN -$amount_col
                        END) AS `total` ";
            } else {
                $sql .= "SUM($amount_col) AS `total` ";
            }
            $sql .= "FROM #__nbill_transaction";
            if ($by_ledger) {
                $sql .= " INNER JOIN #__nbill_transaction_ledger ON #__nbill_transaction_ledger.transaction_id = #__nbill_transaction.id";
                $sql .= " WHERE ";
                $sql .= " #__nbill_transaction_ledger.nominal_ledger_code IN (" . implode(",", $this->ledger_codes) . ") AND ";
            } else {
                $sql .= " WHERE ";
            }
            $sql .= "`date` >= $oldest AND `date` <= $youngest
                    AND (transaction_type = 'IN'";
            if ($this->include_expenditure) {
                $sql .= " OR transaction_type = 'EX'";
            }
            $sql .= ")";
            $sql .= " AND #__nbill_transaction.currency = '" . $this->currency . "'";
            $sql .= " GROUP BY 1 ORDER BY 1";
            $this->db->setQuery($sql);
            $results = $this->db->loadObjectList();
            $this->populatePlotPoints($results);
        }
        return $this->plot_points;
    }

    /**
    * @param array $plot_points Array of SalesGraphPlotPoint objects
    * @param boolean $with_expenditure Whether or not to deduct expenditure
    * @param array $ledger_codes
    */
    public function loadInvoiceTotal($units = SalesGraphService::UNITS_DAYS)
    {
        $by_ledger = $this->ledger_codes && count($this->ledger_codes) > 0;
        if ($this->plot_points && count($this->plot_points) > 0) {

            list($date_range_in_seconds, $oldest, $youngest) = array(0, 0, 0);
            $this->calculateRangeLimits($date_range_in_seconds, $oldest, $youngest);

            $invoice_total_col = $by_ledger ? 'gross_price_for_item' : 'total_gross';

            //Get totals from all invoices that are not written off - subtract credit note amounts if we are including expenditure
            $sql = "SELECT `range`,
                    SUM(`total`) AS `total`
                    FROM (";

                    //Although verbose, this is more efficient than making MySQL convert all the dates, since they are stored as UNIX timestamps, and more accurate than trying to calculate the range in seconds (because of DST)
                    $sql .= "(SELECT (CASE ";
                    foreach ($this->plot_points as $plot_point)
                    {
                        $u_from = $plot_point->x_start_date->format('U');
                        $u_to = $plot_point->x_end_date->format('U');
                        $sql .= "WHEN `document_date` >= $u_from AND `document_date` <= $u_to THEN '$u_from-$u_to' ";
                    }
                    $sql .= "END) AS `range`, ";
                    if ($this->include_expenditure) {
                        $sql .= "SUM(CASE document_type
                                WHEN 'IN' THEN `$invoice_total_col`
                                WHEN 'CR' THEN -`$invoice_total_col`
                                END) AS `total` ";
                    } else {
                        $sql .= "SUM(`$invoice_total_col`) AS `total` ";
                    }
                    $sql .= "FROM #__nbill_document";
                    if ($by_ledger) {
                        $sql .= " INNER JOIN #__nbill_document_items ON #__nbill_document.id = #__nbill_document_items.document_id";
                    }
                    $sql .= " WHERE #__nbill_document.document_date >= $oldest AND #__nbill_document.document_date <= $youngest";
                    $sql .= " AND (document_type = 'IN'";
                    if ($this->include_expenditure) {
                        $sql .= " OR document_type = 'CR'";
                    }
                    $sql .= ")";
                    $sql .= " AND written_off = 0";
                    if ($by_ledger) {
                        $sql .= " AND nominal_ledger_code IN (" . implode(",", $this->ledger_codes) . ")";
                    }
                    $sql .= " AND #__nbill_document.currency = '" . $this->currency . "'";
                    $sql .= " GROUP BY 1 ORDER BY 1)";

                    $sql .= " UNION ALL ";

                    //Add on all income that is not related to invoices - subtract expenditure not related to credit notes if including expenditure
                    $sql .= "(SELECT (CASE ";
                    foreach ($this->plot_points as $plot_point)
                    {
                        $u_from = $plot_point->x_start_date->format('U');
                        $u_to = $plot_point->x_end_date->format('U');
                        $sql .= "WHEN #__nbill_transaction.`date` >= $u_from AND #__nbill_transaction.`date` <= $u_to THEN '$u_from-$u_to' ";
                    }
                    $sql .= "END) AS `range`, ";
                    if ($this->include_expenditure) {
                        $sql .= "SUM(CASE transaction_type
                                WHEN 'IN' THEN `amount`
                                WHEN 'EX' THEN -`amount`
                                END) AS `total` ";
                    } else {
                        $sql .= "SUM(`amount`) AS `total` ";
                    }
                    $sql .= "FROM #__nbill_transaction
                            LEFT JOIN #__nbill_document_transaction ON #__nbill_transaction.id = #__nbill_document_transaction.transaction_id";
                    if ($by_ledger) {
                        $sql .= " INNER JOIN #__nbill_transaction_ledger ON #__nbill_transaction_ledger.transaction_id = #__nbill_transaction.id";
                        $sql .= " WHERE ";
                        $sql .= " #__nbill_transaction_ledger.nominal_ledger_code IN (" . implode(",", $this->ledger_codes) . ") AND ";
                    } else {
                        $sql .= " WHERE ";
                    }
                    $sql .= "#__nbill_document_transaction.document_id IS NULL
                            AND #__nbill_transaction.date >= $oldest AND #__nbill_transaction.date <= $youngest
                            AND (transaction_type = 'IN'";
                    if ($this->include_expenditure) {
                        $sql .= " OR transaction_type = 'EX'";
                    }
                    $sql .= ")";
                    $sql .= " AND #__nbill_transaction.currency = '" . $this->currency . "'";
                    $sql .= " GROUP BY 1 ORDER BY 1)";

            $sql .= ") t1 GROUP BY 1 ORDER BY 1";

            $this->db->setQuery($sql);
            $results = $this->db->loadObjectList();
            $this->populatePlotPoints($results);
        }
        return $this->plot_points;
    }

    protected function populatePlotPoints($results)
    {
        for ($i = 0; $i < count($this->plot_points); $i++)
        {
            foreach ($results as $result)
            {
                if ($this->plot_points[$i]->x_start_date->format('U') . '-' . $this->plot_points[$i]->x_end_date->format('U') == $result->range) {
                    $this->plot_points[$i]->y_value = $result->total;
                    break;
                }
            }
        }
    }

    protected function calculateRangeLimits(&$date_range_in_seconds, &$oldest, &$youngest)
    {
        $date_range_in_seconds = $this->plot_points[0]->x_end_date->format('U') - $this->plot_points[0]->x_start_date->format('U') + 1;
        $oldest = $this->plot_points[0]->x_start_date->format('U');
        $youngest = $this->plot_points[count($this->plot_points) - 1]->x_end_date->format('U');
    }

    public function getFirstTransactionDate()
    {
        $sql = "SELECT MIN(`date`) FROM #__nbill_transaction WHERE transaction_type = 'IN' AND currency = '" . $this->currency . "'";
        $this->db->setQuery($sql);
        return $this->db->loadResult();
    }
}