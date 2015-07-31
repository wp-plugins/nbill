<?php
interface nBillINumberFactory
{
    /**
    * @param double $value
    * @param string $currency_code
    * @return nBillINumberCurrency
    */
    public function createNumberCurrency($value, nBillCurrency $currency);
    /**
    * @param double $value
    * @param string $type
    * @return nBillINumberDecimal
    */
    public function createNumber($value, $type = 'decimal');
}