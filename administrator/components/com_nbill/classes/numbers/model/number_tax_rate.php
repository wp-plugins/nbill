<?php
class nBillNumberTaxRate extends nBillNumberDecimal
{
    /**
    * Value object representing a tax rate number
    * @param nBillINumberFactory $factory
    * @param nBillConfiguration $config
    * @param mixed $value Can be a float, int, or string
    * @return nBillNumberTaxRate
    */
    public function __construct(nBillINumberFactory $factory, nBillConfiguration $config, $value = 0)
    {
        parent::__construct($factory, $config, $value);
        $this->precision = $config->precision_tax_rate;
    }

    protected function getSimilarNumberObject($value)
    {
        $new_number = $this->factory->createNumber($value, 'tax_rate');
        return $new_number;
    }
}