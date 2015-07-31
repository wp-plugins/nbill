<?php
/**
* Class file just containing static methods relating to date functions.
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
* Static functions relating to dates
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_date
{
    /**
    * Return the date as an array: y,m,d
    *
    * @param string $date_value The date as a string (eg. "21/10/2009")
    * @param string $cal_date_format The date format in use (eg. "dd/mm/yyyy")
    * @return array Associative array containing day (key='d'), month (key='m'), and year (key='y')
    * or an empty array if date could not be parsed
    */
    static function get_date_parts($date_value, $cal_date_format)
    {
        $cal_date_format = nbf_common::nb_strtolower($cal_date_format);

        if (nbf_common::nb_strlen($date_value) == 0 || $date_value == 0)
        {
            return array();
        }

        //Get the separator
        $separator = "";
        for ($i=0; $i < nbf_common::nb_strlen($cal_date_format); $i++)
        {
            $char = nbf_common::nb_substr($cal_date_format, $i, 1);
            switch ($char)
            {
                case "d":
                case "m":
                case "y":
                case "h":
                case "i":
                case "s":
                    break;
                default:
                    $separator = $char;
                    break;
            }
            if (nbf_common::nb_strlen($separator) > 0)
            {
                break;
            }
        }

        $format_parts = explode($separator, $cal_date_format);
        $date_parts = explode($separator, $date_value);

        $return_value = array();
        for ($i=0; $i < count($format_parts); $i++)
        {
            $key = nbf_common::nb_substr($format_parts[$i], 0, 1);
            $value = $date_parts[$i];
            //For year, make sure it is in full
            if ($key == "y")
            {
                if (nbf_common::nb_strlen($value) < 4)
                {
                    if ($value > 69)
                    {
                        $value = "19" . $value;
                    }
                    else
                    {
                        $value = "20" . $value;
                    }
                }
            }
            $return_value[$key] = $value;
        }

        //Make sure we have an integer in each element, otherwise return empty array
        if (is_array($return_value))
        {
            foreach ($return_value as $ret_val_val)
            {
                if (!is_numeric($ret_val_val))
                {
                    $return_value = array();
                    break;
                }
            }
        }

        return $return_value;
    }

    public static function get_default_start_date()
    {
        //Return the start date to use for lists based on the specified default type
        $nb_database = nbf_cms::$interop->database;

        $sql = "SELECT default_start_date FROM #__nbill_configuration WHERE id = 1";
        $nb_database->setQuery($sql);
        $default_type = $nb_database->loadResult();

        $reference_date = -1;
        switch ($default_type)
        {
            case "AA":
                $reference_date = nbf_common::nb_time();
                break;
            case "CC":
                $reference_date = nbf_common::nb_strtotime("-2 months");
                break;
            case "DD":
                $reference_date = nbf_common::nb_strtotime("-5 months");
                break;
            case "EE":
                $reference_date = nbf_common::nb_strtotime("-11 months");
                break;
            case "FF":
                $reference_date = nbf_common::nb_strtotime("-59 months");
                break;
            case "GG":
                $reference_date = 0;
                break;
            case "BB":
            default:
                $reference_date = nbf_common::nb_strtotime("-1 month");
                break;
        }

        if ($reference_date < 0)
        {
            $reference_date = nbf_common::nb_strtotime("-1 month");
        }

        $reference_date_parts = nbf_common::nb_getdate($reference_date);
        return nbf_common::nb_mktime(0, 0, 0, $reference_date_parts['mon'], 1, $reference_date_parts['year']);
    }

    public static function get_next_payment_date($first, $start, $payment_frequency, $set_to_midnight = true)
    {
        //For updating last and next payment date
        $leap_year = false;
        $first_date_parts = nbf_common::nb_getdate($first);
        $first_date_mday = $first_date_parts['mday']; //If this is > 28, may need to adjust result (eg. if first payment was on 30th Jan, next will be 28th Feb (or 1st March), after that we need to revert to 30th (March) - not 28th again (nor 1st April).
        $start_date_parts = nbf_common::nb_getdate($start);
        $start_date_year = $start_date_parts['year'];
        $last_day_feb_as_int = nbf_common::nb_mktime(0, 0, 0, 2, 28, $start_date_year);
        $leap_year_date = nbf_common::nb_getdate($last_day_feb_as_int + (24 * 60 * 60));
        $start_date_mday = $start_date_parts['mday'];
        if ($leap_year_date['mon'] == 2)
        {
            $leap_year = true;
        }
        if ($start_date_mday == 28 && $first_date_mday > 28 ||
                $start_date_mday == 29 && $first_date_mday > 29 ||
                $start_date_mday == 30 && $first_date_mday > 30)
        {
            //Start date day is artifically low (due to month of previous payment cycle having fewer days that the first month)
            switch ($first_date_mday)
            {
                case 29:
                    switch ($start_date_parts['mon'])
                    {
                        case 2:
                            if ($leap_year)
                            {
                                $start_date_mday = 29;
                            }
                            else
                            {
                                $start_date_mday = 28;
                            }
                            break;
                        default:
                            $start_date_mday = 29;
                            break;
                    }
                case 30:
                case 31:
                    switch ($start_date_parts['mon'])
                    {
                        case 1:
                        case 3:
                        case 5:
                        case 7:
                        case 8:
                        case 10:
                        case 12:
                            $start_date_mday = $first_date_mday;
                            break;
                        case 2:
                            if ($leap_year)
                            {
                                $start_date_mday = 29;
                            }
                            else
                            {
                                $start_date_mday = 28;
                            }
                            break;
                        default:
                            $start_date_mday = 30;
                            break;
                    }
            }
        }

        switch ($payment_frequency)
        {
            case "BB":  //Weekly
                $next_due_date = nbf_common::nb_strtotime("+1 Week", $start);
                break;
            case "BX":  //Four-weekly
                $next_due_date = nbf_common::nb_strtotime("+4 Weeks", $start);
                break;
            case "CC":  //Monthly
                //Have to work it out manually rather than use strtotime, as start day might have been amended, above
                if ($start_date_parts["mon"] == 12)
                {
                    $new_month = 1;
                    $new_year = $start_date_year + 1;
                }
                else
                {
                    $new_month = $start_date_parts["mon"] + 1;
                    $new_year = $start_date_year;
                }
                $next_due_date = nbf_common::nb_mktime($start_date_parts["hours"], $start_date_parts["minutes"], $start_date_parts["seconds"], $new_month, $start_date_mday, $new_year);
                break;
            case "DD":  //Quarterly
                //Have to work it out manually rather than use strtotime, as start day might have been amended, above
                $end_month = $start_date_parts['mon'] + 3;
                if ($end_month > 12)
                {
                    $end_month = $end_month - 12;
                    $end_year = $start_date_year + 1;
                    $next_due_date = nbf_common::nb_mktime(0, 0, 0, $end_month, $start_date_mday, $end_year);
                }
                else
                {
                    $next_due_date = nbf_common::nb_mktime(0, 0, 0, $start_date_parts['mon'] + 3, $start_date_mday, $start_date_year);
                }
                break;
            case "DX":  //Semi-annually
                //Have to work it out manually rather than use strtotime, as start day might have been amended, above
                $end_month = $start_date_parts['mon'] + 6;
                if ($end_month > 12)
                {
                    $end_month = $end_month - 12;
                    $end_year = $start_date_year + 1;
                    $next_due_date = nbf_common::nb_mktime(0, 0, 0, $end_month, $start_date_mday, $end_year);
                }
                else
                {
                    $next_due_date = nbf_common::nb_mktime(0, 0, 0, $start_date_parts['mon'] + 6, $start_date_mday, $start_date_year);
                }
                break;
            case "EE":  //Annually
                $next_due_date = nbf_common::nb_strtotime("+1 Year", $start);
                break;
            case "FF":  //Biannually
                $next_due_date = nbf_common::nb_strtotime("+2 Years", $start);
                break;
            case "GG":  //Five-yearly
                $next_due_date = nbf_common::nb_strtotime("+5 Years", $start);
                break;
            case "HH":  //Ten-yearly
                $next_due_date = nbf_common::nb_strtotime("+10 Years", $start);
                break;
            case "AA":  //One off
            case "XX":  //Not Applicable
            default:
                $next_due_date = 0;
                break;
        }

        //If first date was 29th or later, and next_due_date comes out as 1st March, set to 28th Feb (or 29th if leap year)
        $next_due_date_parts = nbf_common::nb_getdate($next_due_date);
        if ($first_date_mday > 28 && ($next_due_date_parts['mday'] <= $start_date_mday - 28) && $next_due_date_parts['mon'] == 3) //if ($first_date_mday > 28 && $next_due_date_parts['mday'] == 1 && $next_due_date_parts['mon'] == 3)
        {
            if ($leap_year)
            {
                $next_due_date = nbf_common::nb_mktime($next_due_date_parts['hours'], $next_due_date_parts['minutes'], $next_due_date_parts['seconds'], 2, 29, $next_due_date_parts['year']);
            }
            else
            {
                $next_due_date = nbf_common::nb_mktime($next_due_date_parts['hours'], $next_due_date_parts['minutes'], $next_due_date_parts['seconds'], 2, 28, $next_due_date_parts['year']);
            }
        }

        if ($set_to_midnight && $next_due_date > 0)
        {
            $next_due_date = nbf_common::nb_mktime(0, 0, 0, $next_due_date_parts['mon'], $next_due_date_parts['mday'], $next_due_date_parts['year']);
        }

        return $next_due_date;
    }

    public static function getDefinedRangeDates($range_name, &$start, &$end)
    {
        $end_date = new DateTime(date('Y-m-d 23:59:59', time()));
        switch ($range_name)
        {
            case 'previous_month':
                $end_date = new DateTime(date('Y-m-d 23:59:59', strtotime(date('Y-m-01 00:00:00')) - 4000));
                //fall through
            case 'current_and_previous_month':
                $start_date = new DateTime(date('Y-m-01 0:00:00', strtotime('first day of last month')));
                break;
            case 'current_quarter':
            case 'previous_quarter':
            case 'current_and_previous_quarter':
                $q_start = 10; //Assume last quarter by default
                switch (date('m'))
                {
                    case 1: case 2: case 3:
                        $q_start = 1;
                        break;
                    case 4: case 5: case 6:
                        $q_start = 4;
                        break;
                    case 7: case 8: case 9:
                        $q_start = 7;
                        break;
                    default:
                        break;
                }

                $start_date = new DateTime(date('Y-' . $q_start . '-01 0:0:0'));
                if ($range_name == 'previous_quarter') {
                    $end_date = new DateTime(date('Y-m-d 23:59:59', $start_date->getTimestamp() - 4000));
                }
                if ($range_name != 'current_quarter') {
                    if ($q_start == 1) {
                        $start_date = new DateTime(date(intval(date('Y')) - 1 . '-10-01 0:0:0'));
                    } else {
                        $start_date = new DateTime(date('Y-' . ($q_start - 3) . '-01 0:0:0'));
                    }
                }
                break;
            case 'current_year':
                $start_date = new DateTime(date('Y-01-01 00:00:00'));
                break;
            case 'previous_year':
                $end_date = new DateTime(date(intval(date('Y')) - 1 . '-12-31 23:59:59'));
                //fall through
            case 'current_and_previous_year':
                $start_date = new DateTime(date(intval(date('Y')) - 1 . '-01-01 00:00:00'));
                break;
            case 'current_month':
            default:
                $start_date = new DateTime(date('Y-m-01 00:00:00'));
                break;
        }

        $start = $start_date->getTimestamp();
        $end = $end_date->getTimestamp();
    }
}