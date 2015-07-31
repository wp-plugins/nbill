<?php
class nBillNumberQuantity extends nBillNumberDecimal
{
    /**
    * Value object representing a quantity number
    * @param nBillINumberFactory $factory
    * @param nBillConfiguration $config
    * @param mixed $value Can be a float, int, or string
    * @return nBillNumberQuantity
    */
    public function __construct(nBillINumberFactory $factory, nBillConfiguration $config, $value = 0)
    {
        parent::__construct($factory, $config, $value);
        $this->precision = $config->precision_quantity;
    }

    protected function getSimilarNumberObject($value)
    {
        $new_number = $this->factory->createNumber($value, 'quantity');
        return $new_number;
    }
}