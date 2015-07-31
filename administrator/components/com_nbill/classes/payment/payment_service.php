<?php
class nBillPaymentService
{
    /** @var nBillPaymentMapper **/
    protected $mapper;

    public function __construct(nBillPaymentMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function getPaymentFrequencies()
    {
        return $this->mapper->getPaymentFrequencies();
    }
}
