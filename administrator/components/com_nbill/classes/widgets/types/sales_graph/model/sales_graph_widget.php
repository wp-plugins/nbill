<?php
/**
* Sales graph widget
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class SalesGraphWidget extends nBillWidget
{
    const GRAPH_TYPE_LINE = 'Line';
    const GRAPH_TYPE_COLUMN = 'Column';
    const GRAPH_TYPE_BAR = 'Bar';
    const GRAPH_TYPE_PIE = 'Pie';

    const RANGE_CURRENT_MONTH = 0;
    const RANGE_PREV_MONTH = 10;
    const RANGE_PREV_AND_CURRENT_MONTH = 20;
    const RANGE_PREV_VS_CURRENT_MONTH = 30;
    const RANGE_LAST_24_HOURS = 40;
    const RANGE_LAST_48_HOURS = 50;
    const RANGE_LAST_7_DAYS = 60;
    const RANGE_LAST_14_DAYS = 70;
    const RANGE_LAST_28_DAYS = 80;
    const RANGE_LAST_30_DAYS = 90;
    const RANGE_LAST_60_DAYS = 100;
    const RANGE_LAST_90_DAYS = 110;
    const RANGE_LAST_3_MONTHS = 120;
    const RANGE_LAST_6_MONTHS = 130;
    const RANGE_LAST_12_MONTHS = 140;
    const RANGE_CURRENT_QUARTER = 150;
    const RANGE_PREV_QUARTER = 160;
    const RANGE_PREV_VS_CURRENT_QUARTER = 170;
    const RANGE_CURRENT_YEAR = 180;
    const RANGE_PREV_YEAR = 190;
    const RANGE_PREV_VS_CURRENT_YEAR = 200;
    const RANGE_LAST_5_YEARS = 210;
    const RANGE_ALL_TIME = 220;

    /** @var SalesGraphDataSet **/
    public $dataset;
    /** @var string Type of graph to display (Line, Column, Bar, or Pie) **/
    public $graph_type = self::GRAPH_TYPE_LINE;
    /** @var int Default date range to show **/
    public $date_range = self::RANGE_PREV_VS_CURRENT_YEAR;
    /** @var string **/
    public $currency;
    /** @var boolean Whether or not to deduct expenditure to yield the net sales **/
    public $include_expenditure = false;
    /** @var boolean Whether or not to include unpaid invoices in the income total **/
    public $include_unpaid_invoices = false;
    /** @var string CSS rule for height of graph **/
    public $graph_height = '';
    /** @var boolean **/
    public $refresh_on_change = false;

    public function __construct($type, $published, $ordering)
    {
        parent::__construct($type, $published, $ordering);

        $this->title = 'NBILL_WIDGETS_SALES_GRAPH_DEFAULT_TITLE';
        $this->width = '49%'; //Default to half screen width
    }

    public function getParams()
    {
        //Don't persist the dataset
        $params = parent::getParams();
        if(($key = array_search('dataset', $params)) !== false) {
            unset($params[$key]);
        }
        return $params;
    }

    public function prepareGraph(SalesGraphService $service)
    {
        $this->calculateDateRange($service);

        if (strlen($this->currency) == 3) {
            $service->currency = $this->currency;
        }
        $service->include_unpaid_invoices = $this->include_unpaid_invoices ? true : false;
        $service->include_expenditure = $this->include_expenditure ? true : false;

        $plot_point_sets = array();
        $plot_point_sets[] = $service->getSales();
        $dataset = new SalesGraphDataset($plot_point_sets);

        //Apply legends, and if we are adding more plot point sets, grab them now
        $u_to = $service->to->format('U');
        $u_from = $service->from->format('U');
        switch ($this->date_range)
        {
            case SalesGraphWidget::RANGE_PREV_AND_CURRENT_MONTH:
                $dataset->plot_point_legends[0] = date('M Y', strtotime('previous month', $u_to)) . " - " . date('M Y', strtotime('this month', $u_to));//NBILL_WIDGETS_SALES_GRAPH_RANGE_CURRENT_MONTH;
                break;
            case SalesGraphWidget::RANGE_LAST_24_HOURS:
                $dataset->plot_point_legends[0] = 'NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_24_HOURS';
                break;
            case SalesGraphWidget::RANGE_LAST_48_HOURS:
                $dataset->plot_point_legends[0] = 'NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_48_HOURS';
                break;
            case SalesGraphWidget::RANGE_LAST_7_DAYS:
                $dataset->plot_point_legends[0] = 'NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_7_DAYS';
                break;
            case SalesGraphWidget::RANGE_LAST_14_DAYS:
                $dataset->plot_point_legends[0] = 'NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_14_DAYS';
                break;
            case SalesGraphWidget::RANGE_LAST_28_DAYS:
                $dataset->plot_point_legends[0] = 'NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_28_DAYS';
                break;
            case SalesGraphWidget::RANGE_LAST_30_DAYS:
                $dataset->plot_point_legends[0] = 'NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_30_DAYS';
                break;
            case SalesGraphWidget::RANGE_LAST_60_DAYS:
                $dataset->plot_point_legends[0] = 'NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_60_DAYS';
                break;
            case SalesGraphWidget::RANGE_LAST_90_DAYS:
                $dataset->plot_point_legends[0] = 'NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_90_DAYS';
                break;
            case SalesGraphWidget::RANGE_LAST_3_MONTHS:
                $dataset->plot_point_legends[0] = 'NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_3_MONTHS';
                break;
            case SalesGraphWidget::RANGE_LAST_6_MONTHS:
                $dataset->plot_point_legends[0] = 'NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_6_MONTHS';
                break;
            case SalesGraphWidget::RANGE_LAST_12_MONTHS:
                $dataset->plot_point_legends[0] = 'NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_12_MONTHS';
                break;
            case SalesGraphWidget::RANGE_PREV_VS_CURRENT_QUARTER:
                $this->calculateDateRange($service, 2);
                $plot_points = $service->getSales();
                $dataset->plot_point_sets[] = $plot_points;
                //Cannot compare dates in 2 adjacent quarters, so just use numbers
                $dataset->setLabelsNumeric();
                $dataset->plot_point_legends[1] = $this->startOfPreviousQuarter($service->to)->format('M') . " - " . $this->endOfPreviousQuarter($service->to)->format('M Y'); //NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_QUARTER;
                //Fall through
            case SalesGraphWidget::RANGE_CURRENT_QUARTER:
                $dataset->plot_point_legends[0] = $this->startOfQuarter($service->to)->format('M') . " - " . $this->endOfQuarter($service->to)->format('M Y'); //NBILL_WIDGETS_SALES_GRAPH_RANGE_CURRENT_QUARTER;
                break;
            case SalesGraphWidget::RANGE_PREV_QUARTER:
                $dataset->plot_point_legends[0] = $this->startOfPreviousQuarter($service->to)->format('M') . " - " . $this->endOfPreviousQuarter($service->to)->format('M Y'); //NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_QUARTER;
                break;
            case SalesGraphWidget::RANGE_PREV_VS_CURRENT_YEAR:
                $service->date_format = "M";
                $this->calculateDateRange($service, 2);
                $dataset->plot_point_sets[] = $service->getSales();
                $dataset->plot_point_legends[1] = date('Y', strtotime('previous year', $u_to)); //NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_YEAR;
                //Fall through
            case SalesGraphWidget::RANGE_CURRENT_YEAR:
            case SalesGraphWidget::RANGE_PREV_YEAR:
                $dataset->plot_point_legends[0] = date('Y', $u_to); //NBILL_WIDGETS_SALES_GRAPH_RANGE_CURRENT_YEAR;
                break;
            case SalesGraphWidget::RANGE_PREV_VS_CURRENT_MONTH:
                $service->date_format = "d";
                $this->calculateDateRange($service, 2);
                $dataset->plot_point_sets[] = $service->getSales();
                $dataset->plot_point_legends[1] = date('M Y', strtotime('previous month', $u_to - 259200)); //Take away 3 days to get round PHP buggy strtotime which thinks that one month before March 31st was still March //NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_MONTH;
                //Fall through
            case SalesGraphWidget::RANGE_PREV_MONTH:
            case SalesGraphWidget::RANGE_CURRENT_MONTH:
            default:
                $dataset->plot_point_legends[0] = date('M Y', strtotime('this month', $u_to)); //NBILL_WIDGETS_SALES_GRAPH_RANGE_CURRENT_MONTH;
                break;
            case SalesGraphWidget::RANGE_LAST_5_YEARS:
                $dataset->plot_point_legends[0] = date('Y', strtotime('-5 years', $u_to)) . " - " . date('Y', strtotime('this year', $u_to));
                break;
            case SalesGraphWidget::RANGE_ALL_TIME:
                $dataset->plot_point_legends[0] = date('Y', strtotime('this year', $u_from)) . " - " . date('Y', strtotime('this year', $u_to));
                break;
        }

        $this->dataset = $dataset;
        $this->equaliseDataset(); //When comparing Feb and March (for example), we need both datasets to have 31 entries
    }

    protected function calculateDateRange(SalesGraphService &$service, $dataset_number = 1)
    {
        $service->units = SalesGraphService::UNITS_DAYS;
        $service->to = new DateTime(date('Y-m-d 23:59:59'));

        switch ($this->date_range)
        {
            case SalesGraphWidget::RANGE_PREV_MONTH:
                $service->from = new DateTime('0:00:00 first day of previous month');
                $service->to = new DateTime('0:00:00 last day of previous month');
                break;
            case SalesGraphWidget::RANGE_PREV_AND_CURRENT_MONTH:
                $service->from = new DateTime('0:00:00 first day of previous month');
                break;
            case SalesGraphWidget::RANGE_LAST_24_HOURS:
                $service->from = clone($service->to);
                $service->from->modify('-24 hours +1 second');
                $service->units = SalesGraphService::UNITS_HOURS;
                break;
            case SalesGraphWidget::RANGE_LAST_48_HOURS:
                $service->from = clone($service->to);
                $service->from->modify('-48 hours +1 second');
                $service->units = SalesGraphService::UNITS_HOURS;
                break;
            case SalesGraphWidget::RANGE_LAST_7_DAYS:
                $service->from = clone($service->to);
                $service->from->modify('-7 days +1 second');
                break;
            case SalesGraphWidget::RANGE_LAST_14_DAYS:
                $service->from = clone($service->to);
                $service->from->modify('-14 days +1 second');
                break;
            case SalesGraphWidget::RANGE_LAST_28_DAYS:
                $service->from = clone($service->to);
                $service->from->modify('-28 days +1 second');
                break;
            case SalesGraphWidget::RANGE_LAST_30_DAYS:
                $service->from = clone($service->to);
                $service->from->modify('-30 days +1 second');
                break;
            case SalesGraphWidget::RANGE_LAST_60_DAYS:
                $service->from = clone($service->to);
                $service->from->modify('-60 days +1 second');
                break;
            case SalesGraphWidget::RANGE_LAST_90_DAYS:
                $service->from = clone($service->to);
                $service->from->modify('-90 days +1 second');
                break;
            case SalesGraphWidget::RANGE_LAST_3_MONTHS:
                $service->to->modify('23:59:59 last day of previous month');
                $service->from = clone($service->to);
                $service->from->modify('-3 months +5 days'); //In case the month doesn't have enough days in it, we will force all dates into the next month, then reset it back
                $service->from->modify('0:00:00 first day of this month');
                $service->units = SalesGraphService::UNITS_MONTHS;
                break;
            case SalesGraphWidget::RANGE_LAST_6_MONTHS:
                $service->to->modify('23:59:59 last day of previous month');
                $service->from = clone($service->to);
                $service->from->modify('-6 months +5 days');
                $service->from->modify('0:00:00 first day of this month');
                $service->units = SalesGraphService::UNITS_MONTHS;
                break;
            case SalesGraphWidget::RANGE_LAST_12_MONTHS:
                $service->to->modify('23:59:59 last day of previous month');
                $service->from = clone($service->to);
                $service->from->modify('-12 months +5 days');
                $service->from->modify('0:00:00 first day of this month');
                $service->units = SalesGraphService::UNITS_MONTHS;
                break;
            case SalesGraphWidget::RANGE_CURRENT_QUARTER:
            case SalesGraphWidget::RANGE_PREV_VS_CURRENT_QUARTER:
                switch ($dataset_number)
                {
                    case 1:
                        $service->to->modify('23:59:59 last day of previous month');
                        $service->from = $this->startOfQuarter($service->to);
                        break;
                    case 2:
                        $service->from = $this->startOfPreviousQuarter($service->to);
                        $service->to = clone($service->from);
                        $service->to->modify('+3 Months');
                        break;
                }
                $service->units = SalesGraphService::UNITS_MONTHS;
                break;
            case SalesGraphWidget::RANGE_PREV_QUARTER:
                $service->from = $this->startOfPreviousQuarter($service->to);
                $service->to = clone($service->from);
                $service->to->modify('+3 Months');
                $service->units = SalesGraphService::UNITS_MONTHS;
                break;
            case SalesGraphWidget::RANGE_CURRENT_YEAR:
            case SalesGraphWidget::RANGE_PREV_VS_CURRENT_YEAR:
                switch ($dataset_number)
                {
                    case 1:
                        $service->from = new DateTime($service->to->format('Y') . '-01-01 0:00:00');
                        $service->to->modify('23:59:59 last day of previous month');
                        if ($service->to->format('Y') < $service->from->format('Y')) {
                            //Happy new year!
                            $service->to = new DateTime();
                        }
                        break;
                    case 2:
                        $service->from = new DateTime(($service->to->format('Y') - 1) . '-01-01 0:00:00');
                        $service->to = new DateTime(($service->to->format('Y') - 1) . '-12-31 23:59:59');
                }
                $service->units = SalesGraphService::UNITS_MONTHS;
                break;
            case SalesGraphWidget::RANGE_PREV_YEAR:
                $service->from = new DateTime(($service->to->format('Y') - 1) . '-01-01 0:00:00');
                $service->to = new DateTime(($service->to->format('Y') - 1) . '-12-31 23:59:59');
                $service->units = SalesGraphService::UNITS_MONTHS;
                break;
            case SalesGraphWidget::RANGE_LAST_5_YEARS:
                $service->from = new DateTime(($service->to->format('Y') - 5) . '-01-01 0:00:00');
                $service->to = new DateTime(($service->to->format('Y') - 1) . '-12-31 23:59:59');
                $service->units = SalesGraphService::UNITS_YEARS;
                break;
            case SalesGraphWidget::RANGE_ALL_TIME:
                $service->from = new DateTime($service->getFirstTransactionDate()->format('Y-01-01 0:00:00'));
                $service->to = new DateTime(($service->to->format('Y') - 1) . '-12-31 23:59:59');
                $service->units = SalesGraphService::UNITS_YEARS;
                break;
            case SalesGraphWidget::RANGE_CURRENT_MONTH:
            case SalesGraphWidget::RANGE_PREV_VS_CURRENT_MONTH:
            default:
                switch ($dataset_number)
                {
                    case 1:
                        $service->from = new DateTime(date('Y-m-01 0:00:00'));
                        break;
                    case 2:
                        $service->from = new DateTime('0:00:00 first day of previous month');
                        $service->to = new DateTime('0:00:00 last day of previous month');
                        break;
                }
                break;
        }
    }

    protected function startOfQuarter(DateTime $date)
    {
        $new_date = null;
        $year = $date->format('Y');
        switch ($date->format('m'))
        {
            case 1: case 2: case 3:
                $new_date = new DateTime("$year-01-01 0:00");
                break;
            case 4: case 5: case 6:
                $new_date = new DateTime("$year-04-01 0:00");
                break;
            case 7: case 8: case 9:
                $new_date = new DateTime("$year-07-01 0:00");
                break;
            case 10: case 11: case 12:
                $new_date = new DateTime("$year-10-01 0:00");
                break;
        }
        return $new_date;
    }

    protected function startOfPreviousQuarter(DateTime $date)
    {
        $new_date = null;
        $year = $date->format('Y');
        switch ($date->format('m'))
        {
            case 1: case 2: case 3:
                $new_date = new DateTime(($year - 1) . "-10-01 0:00:00");
                break;
            case 4: case 5: case 6:
                $new_date = new DateTime("$year-01-01 0:00:00");
                break;
            case 7: case 8: case 9:
                $new_date = new DateTime("$year-04-01 0:00:00");
                break;
            case 10: case 11: case 12:
                $new_date = new DateTime("$year-07-01 0:00:00");
                break;
        }
        return $new_date;
    }

    protected function endOfQuarter(DateTime $date)
    {
        $new_date = null;
        $year = $date->format('Y');
        switch ($date->format('m'))
        {
            case 1: case 2: case 3:
                $new_date = new DateTime("$year-03-31 23:59:59");
                break;
            case 4: case 5: case 6:
                $new_date = new DateTime("$year-06-30 23:59:59");
                break;
            case 7: case 8: case 9:
                $new_date = new DateTime("$year-09-30 23:59:59");
                break;
            case 10: case 11: case 12:
                $new_date = new DateTime("$year-12-31 23:59:59");
                break;
        }
        return $new_date;
    }

    protected function endOfPreviousQuarter(DateTime $date)
    {
        $new_date = null;
        $year = $date->format('Y');
        switch ($date->format('m'))
        {
            case 1: case 2: case 3:
                $new_date = new DateTime(($year - 1) . "-12-31 23:59:59");
                break;
            case 4: case 5: case 6:
                $new_date = new DateTime("$year-03-31 23:59:59");
                break;
            case 7: case 8: case 9:
                $new_date = new DateTime("$year-06-30 23:59:59");
                break;
            case 10: case 11: case 12:
                $new_date = new DateTime("$year-09-30 23:59:59");
                break;
        }
        return $new_date;
    }

    /**
    * If first dataset is bigger, 2nd dataset needs to be padded out (other way round is ok) - this is due to the peculiarities of Google Charts
    */
    protected function equaliseDataset()
    {
        if (count($this->dataset->plot_point_sets) > 1) {
            $min_entries = count($this->dataset->plot_point_sets[0]);
            for ($set = 1; $set < count($this->dataset->plot_point_sets); $set++)
            {
                $this_entries = count($this->dataset->plot_point_sets[$set]);
                if ($this_entries < $min_entries) {
                    for ($i = $this_entries; $i < $min_entries; $i++) {
                        $this->dataset->plot_point_sets[$set][] = new SalesGraphPlotPoint($i + 1, 0);
                    }
                }
            }
        }
    }
}