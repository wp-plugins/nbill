<?php
class nBillNumberFactory implements nBillINumberFactory
{
    /** @var nBillConfiguration **/
    protected $config;

    public function __construct(nBillConfiguration $config)
    {
        $this->config = $config;
    }

    /**
    * @param double $value
    * @param nBillCurrency $currency
    * @return nBillINumberCurrency
    */
    public function createNumberCurrency($value, nBillCurrency $currency)
    {
        $number_currency = new nBillNumberCurrency($this, clone($this->config), $value, $currency); //Have to clone, as we change some values for this object only
        $number_currency->negative_in_brackets = $this->config->negative_in_brackets;
        return $number_currency;
    }

    /**
    * @param double $value
    * @param string $type
    * @return nBillINumberDecimal
    */
    public function createNumber($value, $type = 'decimal')
    {
        switch ($type)
        {
            case 'tax_rate':
                $number = new nBillNumberTaxRate($this, $this->config, $value);
                break;
            case 'quantity':
                $number = new nBillNumberQuantity($this, $this->config, $value);
                break;
            default:
                $number = new nBillNumberDecimal($this, $this->config, $value);
                break;
        }
        $number->negative_in_brackets = $this->config->negative_in_brackets;
        return $number;
    }
}