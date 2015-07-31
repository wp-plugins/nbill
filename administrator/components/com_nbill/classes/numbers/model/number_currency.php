<?php
class nBillNumberCurrency extends nBillNumberDecimal implements nBillINumberCurrency
{
    /** @var nBillCurrency **/
    public $currency;
    /** @var int **/
    public $precision_line_total;
    /** @var int **/
    public $precision_grand_total;

    /** @var bool **/
    protected $is_line_total = false;
    /** @var bool **/
    protected $is_grand_total = false;

    /**
    * Value object representing a currency number
    * @param nBillINumberFactory $factory
    * @param nBillConfiguration $config
    * @param mixed $value Can be a float, int, or string
    * @return nBillNumberCurrency
    */
    public function __construct(nBillINumberFactory $factory, nBillConfiguration $config, $value = 0, nBillCurrency $currency)
    {
        if ($currency->override_default_formatting) {
            $config->precision_currency = $currency->precision_currency;
            $config->precision_currency_line_total = $currency->precision_currency_line_total;
            $config->precision_currency_grand_total = $currency->precision_currency_grand_total;
            $config->thousands_separator = $currency->thousands_separator;
            $config->decimal_separator = $currency->decimal_separator;
            $config->currency_format = $currency->currency_format;
        }
        parent::__construct($factory, $config, $value);
        $this->currency = $currency;
        $this->format_string = $config->currency_format;
        $this->resetTotalParams();
    }

    public function resetTotalParams()
    {
        $this->is_line_total = false;
        $this->is_grand_total = false;
        $this->precision = $this->config->precision_currency;
    }

    /**
    * @param bool $value
    */
    public function setIsLineTotal($value)
    {
        $this->resetTotalParams();
        $this->is_line_total = $value;
        if ($value) {
            $this->precision = $this->config->precision_currency_line_total;
        }
    }

    /**
    * @return bool
    */
    public function getIsLineTotal()
    {
        return $this->is_line_total;
    }

    /**
    * @param bool $value
    */
    public function setIsGrandTotal($value)
    {
        $this->resetTotalParams();
        $this->is_grand_total = $value;
        if ($value) {
            $this->precision = $this->config->precision_currency_grand_total;
        }
    }

    /**
    * @return bool
    */
    public function getIsGrandTotal()
    {
        return $this->is_grand_total;
    }

    protected function tryNumberFormatter($value)
    {
        $new_value = null;
        if (class_exists('NumberFormatter')) {
            $formatter = new NumberFormatter($this->locale_setting, NumberFormatter::CURRENCY);
            $formatter->setAttribute(NumberFormatter::ROUNDING_MODE, $this->getPhpRoundingMode(true));
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $this->precision);
            $new_value = $formatter->formatCurrency($value, $this->currency->code);
            if ($new_value == null || $new_value == 'NaN') {
                $formatter = new NumberFormatter('', NumberFormatter::CURRENCY);
                $formatter->setAttribute(NumberFormatter::ROUNDING_MODE, $this->getPhpRoundingMode(true));
                $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $this->precision);
                $new_value = $formatter->formatCurrency($value, $this->currency->code);
            }
        }

        //Remove pesky extra space when prefixing currency symbol (if you want a space, you can use a format string)
        if (ord(substr($new_value, 0, 1)) == 194 && ord(substr($new_value, 1, 1)) > 150 && ord(substr($new_value, 2, 1)) == 32) {
            $new_value = substr($new_value, 0, 2) . substr($new_value, 3);
        } else if (ord(substr($new_value, 0, 1)) > 150 && ord(substr($new_value, 1, 1)) == 32) {
            $new_value = substr($new_value, 0, 1) . substr($new_value, 2);
        } else {
            for ($pos = 0; $pos < 4; $pos++)
            {
                if (substr($new_value, $pos, 2) == 'Â ' ||
                        trim(substr($new_value, $pos, 2)) == 'Â' ||
                        trim(utf8_decode(substr($new_value, $pos, 2))) == '' ||
                        ord(utf8_decode(substr($new_value, $pos, 2))) == '160') {
                    $new_value = substr($new_value, 0, $pos) . substr($new_value, $pos + 2);
                }
            }
        }

        return $new_value;
    }

    protected function manualFormatting($value)
    {
        $value = parent::manualFormatting($value);
        if ($this->currency->code == 'JPY' || (strpos(strtolower($this->config->locale), 'gb') === false && strpos(strtolower($this->config->locale), 'euro') !== false && $this->currency->code == 'EUR')) {
            $value .= ' ' . $this->getCurrencySymbol();
        } else {
            $value = $this->getCurrencySymbol() . $value;
        }
        return $value;
    }

    protected function getCurrencySymbol()
    {
        if (!$this->currency->symbol) {
            //Try to guess
            switch ($this->currency->code)
            {
                case 'GBP':
                    $this->currency->symbol = '£';
                    break;
                case 'EUR':
                    $this->currency->symbol = '€';
                    break;
                case 'JPY':
                    $this->currency->symbol = '¥';
                    break;
                default:
                    $this->currency->symbol = '$';
                    break;
            }
        }
        return $this->currency->symbol;
    }

    protected function getSimilarNumberObject($value)
    {
        $new_number = $this->factory->createNumberCurrency($value, $this->currency);
        if ($this->is_line_total) {
            $new_number->setIsLineTotal(true);
        } else if ($this->is_grand_total) {
            $new_number->setIsGrandTotal(true);
        }
        return $new_number;
    }
}