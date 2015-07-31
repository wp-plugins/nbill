<?php
interface nBillINumberCurrency extends nBillINumberDecimal
{
    public function resetTotalParams();
    /** @param boolean $value **/
    public function setIsLineTotal($value);
    /** @return boolean **/
    public function getIsLineTotal();
    /** @param boolean $value **/
    public function setIsGrandTotal($value);
    /** @return boolean **/
    public function getIsGrandTotal();
    /** @return boolean **/
    public function getIsZero();
}