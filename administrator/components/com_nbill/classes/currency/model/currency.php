<?php
class nBillCurrency
{
    public $id;
    public $code = '';
    public $description = '';
    public $symbol = '';
    public $override_default_formatting = false;
    public $precision_currency = 2;
    public $precision_currency_line_total = 2;
    public $precision_currency_grand_total = 2;
    public $thousands_separator = 'default';
    public $decimal_separator = 'default';
    public $currency_format = '';

    public function __construct($currency_code)
    {
        $this->code = $currency_code;
        $this->setDefaultSymbol();
    }

    protected function setDefaultSymbol()
    {
        //Try to use hard-coded default
        switch ($this->code)
        {
            case 'GBP':
            case 'GIP': //Gibraltar
            case 'GGP': //Guernsey
            case 'FKP': //Falklands
            case 'JEP': //Jersey
            case 'EGP': //Egypt
                $this->symbol = '£';
                break;
            case 'EUR':
                $this->symbol = '€';
                break;
            case 'JPY':
                $this->symbol = '¥';
                break;
            case 'AFN':
                $this->symbol = '؋';
                break;
            case 'ANG':
            case 'AWG':
                $this->symbol = 'ƒ';
                break;
            case 'CRC':
                $this->symbol = '₡';
            case 'GHC':
                $this->symbol = '¢';
                break;
            case 'ILS':
                $this->symbol = '₪';
                break;
            case 'KRW':
            case 'KPW':
                $this->symbol = '₩';
                break;
            case 'LAK':
                $this->symbol = '₭';
                break;
            case 'MNT':
                $this->symbol = '₮';
                break;
            case 'NGN':
                $this->symbol = '₦';
            case 'PHP':
                $this->symbol = '₱';
                break;
            case 'PLN':
                $this->symbol = 'zł';
                break;
            case 'THB':
                $this->symbol = '฿';
                break;
            case 'UAH':
                $this->symbol = '₴';
                break;
            case 'VND':
                $this->symbol = '₫';
                break;
            default:
                $this->symbol = '$';
                break;
        }
    }
}