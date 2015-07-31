<?php
class nBillShippingService
{
    /** @var nBillShippingMapper **/
    protected $mapper;

    public function __construct(nBillShippingMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function getAllShippingMethods($currency_code)
    {
        return $this->mapper->getAllShippingMethods($currency_code);
    }
}
