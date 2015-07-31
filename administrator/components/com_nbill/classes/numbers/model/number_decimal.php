<?php
class nBillNumberDecimal implements nBillINumberDecimal
{
    const ROUND_UP = 1;
    const ROUND_DOWN = 2;

    /** @var mixed String or float representation of a decimal number **/
    public $value;
    /** @var int Number of decimal places to round to **/
    public $precision;
    /** @var int Rounding mode to use **/
    public $rounding_mode = self::ROUND_UP;
    /** @var string Optional format string (overrides all other settings) **/
    public $format_string;
    /** @var bool Whether or not to apply HTML formatting to negative numbers (show in red) **/
    public $html_format_negative = true;
    /** @var bool Whether or not to wrap negative numbers in parentheses (if HTML formatted) **/
    public $negative_in_brackets = true;
    /** @var bool Whether or not to suppress commas from the result (either as thousands separator or decimal point), for the sake of using the value in a database query **/
    public $suppress_commas = false;
    /** @var string Current locale setting **/
    public $locale_setting = '';
    /** @var nBillConfiguration **/
    protected $config;
    /** @var bool **/
    protected $commas_replaced = false;
    /** @var nBillINumberFactory **/
    protected $factory;

    /**
    * Value object representing a decimal number
    * @param nBillINumberFactory $factory
    * @param nBillConfiguration $config
    * @param mixed $value Can be a float, int, or string
    * @return nBillNumberDecimal
    */
    public function __construct(nBillINumberFactory $factory, nBillConfiguration $config, $value = 0)
    {
        $this->factory = $factory;
        $this->config = $config;
        $this->precision = $config->precision_decimal;
        $this->locale_setting = $config->locale;
        $this->value = $value;
    }

    public function getIsZero()
    {
        return !isset($this->value) || $this->value == 0;
    }

    public function applyRoundingRules()
    {
        $this->applyLocale();
        $value = $this->convertToFloat($this->value);
        $value = $this->roundToPrecision($value);
        $this->value = $value;
        $this->revertLocale();
    }

    /**
    * If a format string has been supplied, it will be used to format the value. Otherwise, the other parameters will be used to compute an appropriate format based on the locale.
    * @return string
    */
    public function format()
    {
        return $this->formatValue();
    }

    /**
    * Computes an appropriate format based on the locale and other settings of the current object
    * @return string
    */
    protected function formatValue()
    {
        $this->applyLocale();
        $value = $this->convertToFloat($this->value);
        $value = $this->roundToPrecision($value);
        $value = $this->applyFormatting($value, $this->suppress_commas && !$this->html_format_negative);
        if (!$this->suppress_commas || $this->html_format_negative) {
            $value = $this->padOrTrimDecimals($value);
        }
        $value = $this->applyHtmlFormatting($value);
        $this->revertLocale();
        return $value;
    }

    /**
    * Temporarily switch to the defined locale if applicable
    */
    protected function applyLocale()
    {
        if ($this->locale_setting) {
            //Assignment to the locale_setting property here allows us to get the actual locale used, in case a choice of locales is provided in the config setting
            $this->locale_setting = @setlocale(LC_ALL, array_map('trim', explode(",", $this->locale_setting)));
        }
    }

    /**
    * Get the value as a float (even if passed in as a string with thousands separators)
    * @param string $str_value
    * @return float
    */
    protected function convertToFloat($str_value)
    {
        $this->commas_replaced = (strpos($str_value, ".") === false && strpos($str_value, ",") !== false);
        $str_float = str_replace(",", ".", $str_value . ""); //Convert float to string, and if locale indicates a comma decimal separator, replace with dot
        return (float)$str_float;
    }

    /**
    * Apply the appropriate rounding method
    * @param number $value
    */
    protected function roundToPrecision($value, $precision = null)
    {
        if ($precision === null) {
            $precision = $this->precision;
        }
        $rounded = round($value, $precision, $this->getPhpRoundingMode());
        return $rounded;
    }

    /**
    * @param number $value
    */
    protected function applyFormatting($value, $skip_custom_separators = false)
    {
        if (strlen($this->format_string) > 0) {
            $value = sprintf($this->format_string, $value);
        } else {
            $new_value = $this->tryNumberFormatter($value);
            if ($new_value && $new_value != 'NaN') {
                $value = $new_value;
            } else {
                $value = $this->manualFormatting($value);
            }
        }
        if (!$this->suppress_commas && !$skip_custom_separators) {
            $value = $this->applyCustomSeparators($value);
        }
        if ($this->suppress_commas) {
            $value = str_replace(",", "", $value);
        }
        return $value;
    }

    protected function tryNumberFormatter($value)
    {
        $new_value = null;
        if (class_exists('NumberFormatter')) {
            $formatter = new NumberFormatter($this->locale_setting, NumberFormatter::DECIMAL);
            $formatter->setAttribute(NumberFormatter::ROUNDING_MODE, $this->getPhpRoundingMode(true));
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $this->precision);
            $new_value = $formatter->format($value, NumberFormatter::TYPE_DEFAULT);
            if ($new_value == null || $new_value == 'NaN') {
                $formatter = new NumberFormatter('', NumberFormatter::DECIMAL);
                $formatter->setAttribute(NumberFormatter::ROUNDING_MODE, $this->getPhpRoundingMode(true));
                $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $this->precision);
                $new_value = $formatter->format($value, NumberFormatter::TYPE_DEFAULT);
            }
        }
        return $new_value;
    }

    protected function manualFormatting($value)
    {
        //Put thousands separator back, if required (we can change the actual character used in a minute)
        $thousands = $this->suppress_commas ? "" : ",";
        $value = number_format((double)$value, $this->precision, ".", $thousands);

        //If locale has its own separators, use those
        if (!$this->suppress_commas && function_exists("localeconv")) //Only applied to HTML output (invoices/reports)
        {
            $locale_info = localeconv();
            if (strlen(@$locale_info['mon_decimal_point']) == 0 && strlen(@$locale_info['mon_thousands_sep']) == 0) {
                //localeconv does not always work on Windoze, so we'll use a hard-coded workaround (mainly for unit testing, as not many people run this on windows anyway)
                if (strlen($this->config->locale) > 0) {
                    if (strpos(strtolower($this->config->locale), 'it_it') !== false ||
                        strpos(strtolower($this->config->locale), 'de_de') !== false ||
                        strpos(strtolower($this->config->locale), 'pt_pt') !== false ||
                        strpos(strtolower($this->config->locale), 'pt_br') !== false ||
                        strpos(strtolower($this->config->locale), 'nl_nl') !== false) {
                        //Assume dot for thousands, comma for decimals
                        $value = str_replace(",", "!!#!!", $value);
                        $value = str_replace(".", ",", $value);
                        $value = str_replace("!!#!!", ".", $value);
                    } else {
                        if (strpos(strtolower($this->config->locale), 'gb') ===false && strpos(strtolower($this->config->locale), 'euro') !== false) {
                            //Assume space for thousands, comma for decimals
                            $value = str_replace(",", " ", $value);
                            $value = str_replace(".", ",", $value);
                        }
                    }
                }
            } else {
                if (strlen(@$locale_info['mon_thousands_sep']) > 0 && strlen(@$locale_info['mon_thousands_sep']) < 4)
                {
                    $value = str_replace(",", "!!#!!", $value); //In case decimal point becomes comma
                }
                if (strlen(@$locale_info['mon_decimal_point']) > 0 && strlen(@$locale_info['mon_decimal_point']) < 4)
                {
                    $value = str_replace(".", @$locale_info['mon_decimal_point'], $value);
                }
                if (strlen(@$locale_info['mon_thousands_sep']) > 0 && strlen(@$locale_info['mon_thousands_sep']) < 4)
                {
                    $value = str_replace("!!#!!", @$locale_info['mon_thousands_sep'], $value);
                }
            }
        }
        else if ($this->html_format_negative && $this->commas_replaced)
        {
            $value = str_replace(".", ",", $value);
        }
        return $value;
    }

    protected function applyCustomSeparators($value)
    {
        if ($this->config->thousands_separator == 'default' && $this->config->decimal_separator == 'default') {
            return $value; //Nothing to do here
        }

        //If not specified, get from config
        $thousands_separator = $this->config->thousands_separator == 'default' ? $this->getThousandsSeparator() : $this->config->thousands_separator;
        $decimal_separator = $this->config->thousands_separator == 'default' ? $this->getDecimalSeparator() : $this->config->decimal_separator;

        //If not specified in config, use defaults
        $thousands_separator = $thousands_separator == 'default' ? $this->getThousandsSeparator() : $thousands_separator;
        $decimal_separator = $decimal_separator == 'default' ? $this->getDecimalSeparator() : $decimal_separator;

        $leading_chars = "";
        $trailing_chars = "";

        $temp_value = $this->stripLeadingCharacters($value, $leading_chars);
        $temp_value = $this->stripTrailingCharacters($temp_value, $trailing_chars);

        //Revert to locale-unaware string so PHP can recognise it as a float
        $temp_value = str_replace($this->getThousandsSeparator(), "", $temp_value);
        $temp_value = str_replace($this->getDecimalSeparator(), ".", $temp_value);
        $temp_value = str_replace(",", "", $temp_value);

        //Apply the custom separators
        $new_value = number_format($temp_value, $this->precision, $decimal_separator, $thousands_separator);

        //Restore any stripped characters
        $new_value = $leading_chars . $new_value . $trailing_chars;

        return $new_value;
    }

    protected function padOrTrimDecimals($value)
    {
        //To check decimal precision, we will need to ensure there are no non-numeric characters at the end (eg. currency symbol)
        if (is_double($value)) {
            $real_dec = $dec = $this->getDecimalSeparator();
            $value = strval($value); //In case it is still a float
            $dec = ".";
        } else {
            $dec = $this->getDecimalSeparator();
            $real_dec = $this->config->decimal_separator == 'default' ? $dec : $this->config->decimal_separator;
            if (strlen($value) - (strrpos($value, $real_dec) + 1) == $this->precision || (strpos($value, $dec) === false && strpos($value, $real_dec) !== false)) {
                $dec = $real_dec;
            }
        }
        $trailing_chars = "";
        $temp_value = $this->stripTrailingCharacters($value, $trailing_chars);

        //Check whether we have the right number of decimal places
        //$dec = $this->getDecimalSeparator();
        $dec_pos = strpos($temp_value, $dec);
        if ($dec_pos === false) {
            if ($this->precision == 0) {
                return $value; //Already hunky dory
            }
            $dec_pos = strlen($temp_value);
            $temp_value .= $dec;
        }
        if (strlen($temp_value) - ($dec_pos + 1) < $this->precision) {
            //Not enough decimals - get them from the original value if possible, otherwise just pad with zeros
            $orig_value = abs($this->roundToPrecision($this->value, $this->precision));
            $decimals = ($this->roundToPrecision($orig_value - floor($orig_value), $this->precision)) * pow(10, $this->precision);
            $temp_value = substr($temp_value, 0, $dec_pos + 1) . $decimals;
            if (strlen($temp_value) - ($dec_pos + 1) < $this->precision) {
                $temp_value .= str_repeat('0', $this->precision - (strlen($temp_value) - ($dec_pos + 1)));
            }
        } else if (strlen($temp_value) - ($dec_pos + 1) > $this->precision) {
            //Too many decimals
            if ($this->precision == 0) {
                $temp_value = substr($temp_value, 0, $dec_pos + $this->precision); //Omit decimal character
            } else {
                $temp_value = substr($temp_value, 0, $dec_pos + 1 + $this->precision);
            }
        }

        if ($dec != $real_dec)
        {
            $temp_value = str_replace($dec, $real_dec, $temp_value);
        }

        //Restore any trailing characters we may have stripped off
        return $temp_value . $trailing_chars;
    }

    protected function getThousandsSeparator()
    {
        //Must use new decimal object, as formatting on super classes (in particular, currency) can arbitrarily override separators at this point
        $temp_decimal = $this->factory->createNumber(0);
        $temp_decimal->locale_setting = $this->locale_setting;
        $temp_decimal->precision = 0;
        $str = $temp_decimal->applyFormatting(floatval(1000), true);
        return is_numeric(substr($str, 1, 1)) ? '' : substr($str, 1, 1);
    }

    protected function getDecimalSeparator()
    {
        //Must use new decimal object, as formatting on super classes (in particular, currency) can arbitrarily override precision at this point
        $temp_decimal = $this->factory->createNumber(0);
        $temp_decimal->locale_setting = $this->locale_setting;
        $temp_decimal->precision = 1;
        $str = $temp_decimal->applyFormatting(floatval(10.1), true);
        $str = $temp_decimal->stripLeadingCharacters($str);
        return substr($str, 2, 1);
    }

    /**
    * Remove any non-numeric characters from the start (and return them separately in the return param so they can be re-joined later if required)
    * @param string $value Formatted number (may include leading or trailling characters, eg. currency symbol)
    * @param string $leading_chars
    */
    protected function stripLeadingCharacters($value, &$leading_chars = null)
    {
        $value = html_entity_decode($value, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding);
        $first_number = $this->findFirstNumericPos($value);
        if ($first_number > 0) {
            $leading_chars = substr($value, 0, $first_number);
            return substr($value, strlen($leading_chars));
        }
        $leading_chars = "";
        return $value;
    }

    protected function findFirstNumericPos($value)
    {
        $first_number = 0;
        for ($i=0; $i<strlen($value); $i++)
        {
            $char = substr($value, $i, 1);
            if (is_numeric($char)) {
                return $i;
            }
        }
    }

    /**
    * Remove any non-numeric characters from the end (and return them separately in the return param so they can be re-joined later if required)
    * @param string $value Formatted number (may include leading or trailling characters, eg. currency symbol)
    * @param string $trailing_chars
    */
    protected function stripTrailingCharacters($value, &$trailing_chars = null)
    {
        $value = html_entity_decode($value, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding);
        $last_number = $this->findLastNumericPos($value);
        if ($last_number < strlen($value) - 1) {
            $trailing_chars = substr($value, $last_number + 1);
            return substr($value, 0, $last_number + 1);
        }
        $trailing_chars = "";
        return $value;
    }

    protected function findLastNumericPos($value)
    {
        $last_number = 0;
        for ($i=0; $i<strlen($value); $i++)
        {
            $char = substr($value, $i, 1);
            if (is_numeric($char)) {
                $last_number = $i;
            }
        }
        return $last_number;
    }

    /**
    * A bit naughty putting this here (esp. with inline styling!), but I don't think it is worth introducing another class just for this, and we might not always have access to the stylesheet
    * @param string $value
    */
    protected function applyHtmlFormatting($value)
    {
        if ($this->html_format_negative) {
            if ($this->value < 0) {
                $in_brackets = (substr($value, 0, 1) == '(' && substr($value, strlen($value) - 1) == ')');
                if (!$in_brackets && $this->negative_in_brackets) {
                    $value = "<span style=\"color:#ff0000;\">(" . str_replace("-", "", $value) . ")</span>";
                } else {
                    $value = "<span style=\"color:#ff0000;\">$value</span>";
                }
            }
        }
        return $value;
    }

    /**
    * Revert back to default US locale so that database inserts are not messed up by unexpected commas
    */
    protected function revertLocale()
    {
        @setlocale(LC_ALL, array("en_US.UTF-8", "en", "en_US", "en-US", "English_United States", "English_United States.1252"));
        if (strlen($this->locale_setting) > 0)
        {
            @setlocale(LC_CTYPE, array_map('trim', explode(",", $this->locale_setting)));
        }
    }

    /**
    * Return equivalent PHP constant for the object's current rounding mode (PHP round function uses different constants to NumberFormatter)
    * @param bool $formatter Whether to use the constant for NumberFormatter (true) or the round function (false)
    * @return int
    */
    protected function getPhpRoundingMode($formatter = false)
    {
        if ($this->rounding_mode == self::ROUND_DOWN) {
            return class_exists('NumberFormatter') && $formatter ? NumberFormatter::ROUND_DOWN : PHP_ROUND_HALF_DOWN;
        } else {
            return class_exists('NumberFormatter') && $formatter ? NumberFormatter::ROUND_UP : PHP_ROUND_HALF_UP;
        }
    }

    /**
    * @param nBillNumberDecimal $number_to_add
    * @return nBillNumberDecimal
    */
    public function addNumber(nBillINumberDecimal $number_to_add)
    {
        $f1 = $this->value;
        $f2 = $number_to_add->value;

        $f1 = $f1 ? $f1 : 0;
        $f2 = $f2 ? $f2 : 0;
        $e = pow(10, $this->precision);
        $first = $f1 * $e;
        $second = $f2 * $e;
        //Handle exponential notation
        if (strpos($first, "E") !== false)
        {
            $first = sprintf("%d", $first);
        }
        if (strpos($second, "E") !== false)
        {
            $second = sprintf("%d", $second);
        }
        $result = round($first) + round($second);
        $is_negative = $result < 0;
        $result = str_pad(abs($result), 3, "0", STR_PAD_LEFT);
        $result = $is_negative ? "-" . $result : $result;
        $result = substr($result, 0, strlen($result) - $this->precision) . "." . substr($result, strlen($result) - $this->precision);

        $new_number = $this->getSimilarNumberObject($result);
        return $new_number;
    }

    /**
    * @param nBillNumberDecimal $number_to_subract
    * @return nBillNumberDecimal
    */
    public function subtractNumber(nBillINumberDecimal $number_to_subtract)
    {
        $minuend = $this->value ? $this->value : 0;
        $subtrahend = $number_to_subtract->value ? $number_to_subtract->value : 0;
        $e = pow(10, $this->precision);
        $first = $minuend * $e;
        $second = $subtrahend * $e;
        //Handle exponential notation
        if (strpos($first, "E") !== false)
        {
            $first = sprintf("%d", $first);
        }
        if (strpos($second, "E") !== false)
        {
            $second = sprintf("%d", $second);
        }
        $result = round($first) - round($second);
        $is_negative = $result < 0;
        $result = str_pad(abs($result), 3, "0", STR_PAD_LEFT);
        $result = $is_negative ? "-" . $result : $result;
        $result = substr($result, 0, strlen($result) - $this->precision) . "." . substr($result, strlen($result) - $this->precision);

        $new_number = $this->getSimilarNumberObject($result);
        return $new_number;
    }

    protected function getSimilarNumberObject($value)
    {
        $new_number = $this->factory->createNumber($value);
        return $new_number;
    }

    public function makeNegative()
    {
        $new_number = $this->getSimilarNumberObject(0 - $this->value);
        return $new_number;
    }

    /**
    * Returns the rounded value without currency, separators, or other formatting (to allow for user editing)
    */
    public function getEditableDecimal()
    {
        $decimal = $this->factory->createNumber($this->value);
        $decimal->precision = $this->precision;
        $decimal->rounding_mode = $this->rounding_mode;
        $decimal->html_format_negative = false;
        $decimal->negative_in_brackets = false;
        $decimal->suppress_commas = true;
        $decimal->locale_setting = $this->locale_setting;
        return $decimal;
    }

    public function __toString()
    {
        return $this->format();
    }
}
