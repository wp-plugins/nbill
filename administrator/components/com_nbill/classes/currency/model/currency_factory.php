<?php
class nBillCurrencyFactory
{
    public function createCurrency($currency_code)
    {
        $currency = new nBillCurrency($currency_code);
        return $currency;
    }

    public function createCurrencyMapper(nbf_database $db)
    {
        $mapper = new nBillCurrencyMapper($db, $this);
        return $mapper;
    }
}