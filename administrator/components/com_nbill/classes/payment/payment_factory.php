<?php
class nBillPaymentFactory
{
    /** @return nBillPaymentFrequency **/
    public function createPaymentFrequency()
    {
        return new nBillPaymentFrequency();
    }
}