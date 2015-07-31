<?php
class nBillCurrencyService
{
    /** @var nBillCurrencyMapper **/
    protected $mapper;

    public function __construct(nBillCurrencyMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function findCurrency($currency_code)
    {
        $currency = $this->mapper->findCurrency($currency_code);
        return $currency;
    }

    public function saveCurrency(nBillCurrency &$currency)
    {
        $this->mapper->saveObject($currency);
    }
}