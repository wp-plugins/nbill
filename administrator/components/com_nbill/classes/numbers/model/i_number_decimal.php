<?php
interface nBillINumberDecimal
{
    public function applyRoundingRules();
    /**
    * If a format string has been supplied, it will be used to format the value. Otherwise, the other parameters will be used to compute an appropriate format based on the locale.
    * @return string
    */
    public function format();
    /**
    * @param nBillINumberDecimal $number_to_add
    * @return nBillINumberDecimal
    */
    public function addNumber(nBillINumberDecimal $number_to_add);
    /**
    * @param nBillINumberDecimal $number_to_subract
    * @return nBillINumberDecimal
    */
    public function subtractNumber(nBillINumberDecimal $number_to_subract);
    /**
    * @return nBillINumberDecimal
    */
    public function makeNegative();
    /** @return nBillINumberDecimal */
    public function getEditableDecimal();
}
