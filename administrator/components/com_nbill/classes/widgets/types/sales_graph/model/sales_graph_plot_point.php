<?php
/**
* Represents a set of data to be plotted on a sales graph
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class SalesGraphPlotPoint
{
    /** @var int value of X axis (this should be sequential for all plot points on the graph) */
    public $x_value;
    /** @var DateTime Start date of this plot point */
    public $x_start_date;
    /** @var DateTime End date of this plot point */
    public $x_end_date;
    /** @var float value of Y axis */
    public $y_value;
    /** @var string date format to use for auto-generating x-axis labels */
    public $date_format;
    /** @var string Label to show on X axis for this plot point (defaults to the date or date range, or if not specified, the x value) */
    public $x_label;

    /**
    * @param mixed $x_value
    * @param mixed $y_value
    * @return SalesGraphPlotPoint
    */
    public function __construct($x_value, $y_value = 0, $date_format = 'Y-m-d', DateTime $x_start_date = null, DateTime $x_end_date = null, $x_label = null)
    {
        $this->x_value = $x_value;
        $this->y_value = $y_value;
        $this->date_format = $date_format;
        $this->x_start_date = $x_start_date;
        $this->x_end_date = $x_end_date;
        $this->x_label = $x_label;
        $this->generate_x_label();
    }

    public function generate_x_label()
    {
        $label = '';
        if ($this->x_start_date) {
            $label = $this->x_start_date->format($this->date_format);
            if ($this->x_end_date && $this->x_end_date->format($this->date_format) != $this->x_start_date->format($this->date_format)) {
                //End date is different to start date
                //If the period is one month, drop the days and just show the month/year
                $u_start = $this->x_start_date->format('U');
                if (date('d', $u_start) == '01' &&
                        $this->x_end_date->format('U') == strtotime('+1 Month', $u_start) - 1) {
                    $label = $this->x_start_date->format($this->stripDatePart(array('d', 'D', 'j', 'l', 'N', 'w'), $this->date_format));
                } else {
                    //If the preiod is a year, drop the days and months and just show the year
                    if (date('d', $u_start) == '01' && date('m', $u_start) == '01' &&
                        $this->x_end_date->format('U') == strtotime('+1 Year', $u_start) - 1) {
                            $label = $this->x_start_date->format('Y');
                        } else {
                            //Otherwise, show the full date range
                            $label .= ' - ' . $this->x_end_date->format($this->date_format);
                        }
                }
            } else if ($this->x_end_date) {
                //If interval is 1 hour, use the time instead of the date
                $interval = $this->x_start_date->diff($this->x_end_date, true);
                if ($interval->d == 0 && $interval->h < 3) {
                    $label = $this->x_start_date->format('H:i') . 'h';
                }
            }
        }
        else {
            $label = $this->x_value;
        }
        $this->x_label = $label;
    }

    /**
    * Amends the given date format string to remove a particular element (eg. to remove the days and just leave month and year, send an array of 'd', 'D', 'j', 'l', 'N', and 'w')
    *
    * @param mixed $date_parts
    * @param mixed $date_format
    */
    protected function stripDatePart($date_parts, $date_format)
    {
        $new_format = $date_format;
        $separators = array('-', '/', '.', '\\', ' ');
        foreach ($date_parts as $date_part) {
            foreach ($separators as $separator) {
                $new_format = str_replace($date_part . $separator, '', $new_format);
                $new_format = str_replace($separator . $date_part, '', $new_format);
            }
        }
        return $new_format;
    }
}