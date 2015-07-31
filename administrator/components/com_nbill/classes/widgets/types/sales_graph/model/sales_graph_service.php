<?php
/**
* Service to retrieve the required data from the mapper class and populate a dataset domain object
* which can then be used elsewhere to render a sales graph
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class SalesGraphService
{
    const UNITS_HOURS = 'Hours';
    const UNITS_DAYS = 'Days';
    const UNITS_MONTHS = 'Months';
    const UNITS_YEARS = 'Years';

    /** @var integer Maximum number of plot points allowed on the graph **/
    public $max_increments = 100;
    /** @var integer unit of time measurement **/
    public $units = self::UNITS_DAYS;
    /** @var string default date format for labels **/
    public $date_format = 'Y-m-d';
    /** @var DateTime Start of data range (defaults to start of current month) **/
    public $from;
    /** @var DateTime End of data range (defaults to current day) **/
    public $to;
    /** @var boolean Whether or not to report only on income records - if false, data will be read from invoices, including unpaid invoices **/
    public $include_unpaid_invoices = false;
    /** @var boolean Whether or not to include expenditure as well as income to yield a net total **/
    public $include_expenditure = false;
    /** @var array Array of ledger codes (strings) to limit the data to transactions relating to those ledgers only **/
    public $ledger_codes = array();
    /** @var string **/
    public $currency;

    /** @var integer Actual number of plot points used **/
    protected $increments = 31;
    /** @var SalesGraphMapper Mapper object for retrieving graph data (supplied using constructor dependency injection) **/
    protected $mapper;

    public function __construct(SalesGraphMapper $mapper)
    {
        $this->mapper = $mapper;
        $this->currency = $mapper->currency; //Can be overridden later if required
        $this->from = new DateTime(date('Y-m-01 00:00:00'));
        $this->to = new DateTime(date('Y-m-d 23:59:59'));
    }

    /**
    * Populate a dataset with the sales figures for the given range and products
    * @return SalesGraphDataset
    */
    public function getSales()
    {
        $this->mapper->currency = $this->currency;
        $this->mapper->plot_points = $this->generatePlotPoints();
        $this->mapper->include_expenditure = $this->include_expenditure;
        $this->mapper->ledger_codes = $this->ledger_codes;

        if ($this->include_unpaid_invoices) {
            $this->plot_points = $this->mapper->loadInvoiceTotal($this->units);
        } else {
            $this->plot_points = $this->mapper->loadIncomeTotal($this->units);
        }
        return $this->plot_points;
    }

    /**
    * Calculates start and end date of each data point
    * @return array Array of SalesGraphPlotPoint objects
    */
    protected function generatePlotPoints()
    {
        $this->from = new DateTime($this->from->format('Y-m-d 0:00:00'));
        $this->to = new DateTime($this->to->format('Y-m-d 23:59:59'));
        $interval = $this->from->diff($this->to);
        if ($this->units == self::UNITS_HOURS) {
            $data_count = $interval->h + ($interval->days * 24);
            $data_count++; //We want to include start and end dates
            if ($data_count > $this->max_increments) {
                //Too much data - switch to days
                $this->units = self::UNITS_DAYS;
            }
        }
        if ($this->units == self::UNITS_DAYS) {
            $data_count = $interval->days;
            $data_count++; //We want to include start and end dates
            if ($data_count > $this->max_increments) {
                //Too much data - switch to months
                $this->units = self::UNITS_MONTHS;
            }
        }
        if ($this->units == self::UNITS_MONTHS) {
            $data_count = $interval->m + ($interval->y * 12);
            $data_count++; //Include whole of current month
            if ($data_count > $this->max_increments) {
                //Too much data - switch to years
                $this->units = self::UNITS_YEARS;
            }
        }
        if ($this->units == self::UNITS_YEARS) {
            $data_count = $interval->y;
            $data_count++; //Include whole of current year
            if ($data_count > $this->max_increments) {
                //Too much data - move the from date forward
                $this->from = clone($this->to);
                $this->from->modify('-' . $this->max_increments . ' Years');
            }
        }

        $this->increments = $data_count;

        $plot_points = array();
        $current = $this->from;
        $modification = '+1 ' . $this->units;
        $next = clone($current);
        $next->modify($modification);
        $next->modify('-1 Second'); //To avoid overlap with the next range

        for ($i = 0; $i < $this->increments; $i++) {
            $plot_points[] = new SalesGraphPlotPoint($i, 0, $this->date_format, clone($current), clone($next));
            $current = clone($next);
            $current->modify('+1 Second'); //Start of next range
            $next = clone($current);
            $next->modify($modification);
            $next->modify('-1 Second');
            if ($next > $this->to) {
                //Can happen due to a bug in PHP's DateTime->diff method
                break;
            }
        }

        return $plot_points;
    }

    /**
    * @return DateTime
    */
    public function getFirstTransactionDate()
    {
        $first_date = new DateTime(date('Y-m-d 0:00:00', $this->mapper->getFirstTransactionDate()));
        return $first_date;
    }
}
