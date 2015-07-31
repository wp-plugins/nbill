<?php
/**
* Provides currency formatting and optional HTML formatting of numeric values
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Replacement for PHP's crud number_format/round functions which round down for even numbers on Linux
//This one rounds up whether odd or even, as it should!!
function format_number($float, $decimal_places = null, $format_negative = false, $suppress_commas = true, $negative_in_brackets = null, $currency = '')
{
    $config = nBillConfigurationService::getInstance()->getConfig();
    if ($negative_in_brackets === null) {
        $negative_in_brackets = $format_negative ? $config->negative_in_brackets : false;
    }
    $number = null;
    if ($decimal_places === null) {
        $number = nbf_common::convertValueToNumberObject($float);
    } else if ($decimal_places == 'currency_line') {
        if ($currency) {
            $number = nbf_common::convertValueToCurrencyObject($float, $currency, false, true);
        } else {
            $decimal_places = $config->precision_currency_line_total;
        }
    } else if ($decimal_places == 'currency_grand') {
        if ($currency) {
            $number = nbf_common::convertValueToCurrencyObject($float, $currency, true);
        } else {
            $decimal_places = $config->precision_currency_grand_total;
        }
    } else if ($decimal_places == 'currency') {
        if ($currency) {
            $number = nbf_common::convertValueToCurrencyObject($float, $currency, false);
        } else {
            $decimal_places = $config->precision_currency;
        }
    } else if ($decimal_places == 'tax_rate') {
        $number = nbf_common::convertValueToNumberObject($float, 'tax_rate');
    } else if ($decimal_places == 'quantity') {
        $number = nbf_common::convertValueToNumberObject($float, 'quantity');
    } else {
        $decimal_places = intval($decimal_places);
    }

    if (!$number) {
        $number = nbf_common::convertValueToNumberObject($float);
        $number->precision = $decimal_places;
    }
    $number->html_format_negative = $format_negative;
    $number->suppress_commas = $suppress_commas;
    $number->negative_in_brackets = $negative_in_brackets;
    return $number->format();

    /*$locale_setting = nbf_cms::$interop->temp_set_locale();

    $commas_replaced = (nbf_common::nb_strpos($float, ".") === false && nbf_common::nb_strpos($float, ",") !== false);
    $str_float = str_replace(",", ".", $float . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot
    $float = (float)$str_float;

    $is_negative = $float < 0;
    if ($is_negative)
    {
        $float = abs($float);
    }

    if (nbf_common::nb_strpos($str_float, ".") !== false && substr($str_float, nbf_common::nb_strlen($str_float) - 1) == '5')
    {
        $float += 0.0001;
    }

    $ret_val = $float;

    if ($is_negative)
    {
        $ret_val = 0 - $ret_val;
    }

    if ($suppress_commas)
    {
        $thousands = "";
    }
    else
    {
        $thousands = ",";
    }

    $ret_val = number_format((double)$ret_val, $decimal_places, ".", $thousands);

    if (!$suppress_commas && $format_negative && function_exists("localeconv")) //Only applied to HTML output (invoices/reports)
    {
        $locale_info = localeconv();
        if (nbf_common::nb_strlen(@$locale_info['mon_thousands_sep']) > 0 && nbf_common::nb_strlen(@$locale_info['mon_thousands_sep']) < 4)
        {
            $ret_val = str_replace(",", "!!#!!", $ret_val); //In case decimal point becomes comma
        }
        if (nbf_common::nb_strlen(@$locale_info['mon_decimal_point']) > 0 && nbf_common::nb_strlen(@$locale_info['mon_decimal_point']) < 4)
        {
            $ret_val = str_replace(".", @$locale_info['mon_decimal_point'], $ret_val);
        }
        if (nbf_common::nb_strlen(@$locale_info['mon_thousands_sep']) > 0 && nbf_common::nb_strlen(@$locale_info['mon_thousands_sep']) < 4)
        {
            $ret_val = str_replace("!!#!!", @$locale_info['mon_thousands_sep'], $ret_val);
        }
    }
    else if ($format_negative && $commas_replaced)
    {
        $ret_val = str_replace(".", ",", $ret_val);
    }

    //Are we outputting a currency value?
    if (strlen($currency) > 0) {
        if (strlen($currency_format) > 0) {
            $ret_val = sprintf($currency_format, $ret_val);
        } else {
            $current_locale = '';
            if ($local_setting) {
                $current_locale = setlocale(LC_ALL, 0);
            }
            $formatter = new NumberFormatter($current_locale, NumberFormatter::CURRENCY);
            $new_ret_val = $formatter->formatCurrency($ret_val, $currency);
            if ($new_ret_val == null || $new_ret_val == 'NaN') {
                $formatter = new NumberFormatter('', NumberFormatter::CURRENCY);
                $new_ret_val = $formatter->formatCurrency($ret_val, $currency);
            }
            if ($new_ret_val && $new_ret_val != 'NaN') {
                $ret_val = $new_ret_val;
            }
        }
    }

    if ($format_negative)
    {
        if ($ret_val < 0)
        {
            if ($negative_in_brackets)
            {
                $ret_val = "<span style=\"color:#ff0000;\">(" . str_replace("-", "", $ret_val) . ")</span>";
            }
            else
            {
                $ret_val = "<span style=\"color:#ff0000;\">$ret_val</span>";
            }
        }
    }

    //Revert back to default US locale so that database inserts are not messed up by unexpected commas
    nbf_cms::$interop->set_default_locale();
    return $ret_val;*/
}

//The following functions are based on code in the public domain (PHP user comments)
function float_cmp($f1, $f2, $precision = null) // are 2 floats equal
{
    $precision = get_precision_value($precision);
    $f1 = $f1 ? $f1 : 0;
    $f2 = $f2 ? $f2 : 0;
    $e = pow(10, $precision);
    $first = $f1 * $e;
    $second = $f2 * $e;
    //Handle exponential notation
    if (nbf_common::nb_strpos($first, "E") !== false)
    {
        $first = sprintf("%d", $first);
    }
    if (nbf_common::nb_strpos($second, "E") !== false)
    {
        $second = sprintf("%d", $second);
    }
    $i1 = round($first);
    $i2 = round($second);
    return ($i1 == $i2);
}

function float_gtr($big, $small, $precision = null) // is one float bigger than another
{
    $precision = get_precision_value($precision);
    $big = $big ? $big : 0;
    $small = $small ? $small : 0;
    $e = pow(10, $precision);
    $first = $big * $e;
    $second = $small * $e;
    //Handle exponential notation
    if (nbf_common::nb_strpos($first, "E") !== false)
    {
        $first = sprintf("%d", $first);
    }
    if (nbf_common::nb_strpos($second, "E") !== false)
    {
        $second = sprintf("%d", $second);
    }
    $ibig = round($first);
    $ismall = round($second);
    return ($ibig > $ismall);
}

function float_gtr_e($big, $small, $precision = null) // is on float bigger or equal to another
{
    $precision = get_precision_value($precision);
    $big = $big ? $big : 0;
    $small = $small ? $small : 0;
    $e = pow(10, $precision);
    $first = $big * $e;
    $second = $small * $e;
    //Handle exponential notation
    if (nbf_common::nb_strpos($first, "E") !== false)
    {
        $first = sprintf("%d", $first);
    }
    if (nbf_common::nb_strpos($second, "E") !== false)
    {
        $second = sprintf("%d", $second);
    }
    $ibig = round($first);
    $ismall = round($second);
    return ($ibig >= $ismall);
}

function float_add($f1, $f2, $precision = null) //Add 2 floats together (return as string)
{
    $precision = get_precision_value($precision);
    $f1 = $f1 ? $f1 : 0;
    $f2 = $f2 ? $f2 : 0;
    $e = pow(10, $precision);
    $first = $f1 * $e;
    $second = $f2 * $e;
    //Handle exponential notation
    if (nbf_common::nb_strpos($first, "E") !== false)
    {
        $first = sprintf("%d", $first);
    }
    if (nbf_common::nb_strpos($second, "E") !== false)
    {
        $second = sprintf("%d", $second);
    }
    $result = round($first) + round($second);
    $is_negative = $result < 0;
    $result = str_pad(abs($result), 3, "0", STR_PAD_LEFT);
    $result = $is_negative ? "-" . $result : $result;
    $result = substr($result, 0, nbf_common::nb_strlen($result) - $precision) . "." . substr($result, nbf_common::nb_strlen($result) - $precision);
    return $result;
}

function float_subtract($minuend, $subtrahend, $precision = null) //Subtract subtrahend from minuend (return string)
{
    $precision = get_precision_value($precision);
    $minuend = $minuend ? $minuend : 0;
    $subtrahend = $subtrahend ? $subtrahend : 0;
    $e = pow(10, $precision);
    $first = $minuend * $e;
    $second = $subtrahend * $e;
    //Handle exponential notation
    if (nbf_common::nb_strpos($first, "E") !== false)
    {
        $first = sprintf("%d", $first);
    }
    if (nbf_common::nb_strpos($second, "E") !== false)
    {
        $second = sprintf("%d", $second);
    }
    $result = round($first) - round($second);
    $is_negative = $result < 0;
    $result = str_pad(abs($result), 3, "0", STR_PAD_LEFT);
    $result = $is_negative ? "-" . $result : $result;
    $result = substr($result, 0, nbf_common::nb_strlen($result) - $precision) . "." . substr($result, nbf_common::nb_strlen($result) - $precision);
    return $result;
}

function get_precision_value($precision)
{
    $config = nBillConfigurationService::getInstance()->getConfig();
    if ($precision === null) {
        $precision = $config->precision_decimal;
    } else if ($precision == 'currency_line') {
        $precision = $config->precision_currency_line_total;
    } else if ($precision == 'currency_grand') {
        $precision = $config->precision_currency_grand_total;
    } else if ($precision == 'currency') {
        $precision = $config->precision_currency;
    } else if ($precision == 'tax_rate') {
        $precision = $config->precision_tax_rate;
    } else if ($precision == 'quantity') {
        $precision = $config->precision_quantity;
    } else {
        $precision = intval($precision);
    }
    return $precision;
}