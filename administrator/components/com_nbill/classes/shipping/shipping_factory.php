<?php
class nBillShippingFactory
{
    /** @var nBillNumberFactory **/
    protected $number_factory;
    /** @var nBillCurrencyService **/
    protected $currency_service;

    public function __construct(nBillNumberFactory $number_factory, nBillCurrencyService $currency_service)
    {
        $this->number_factory = $number_factory;
        $this->currency_service = $currency_service;
    }

    /** @return nBillShippingMethod **/
    public function createShipping($currency_code)
    {
        $shipping_method = new nBillShippingMethod();
        $shipping_method->net_price = $this->number_factory->createNumberCurrency(0, $this->currency_service->findCurrency($currency_code));
        $shipping_method->tax_rate_if_different = $this->number_factory->createNumber(0, 'tax_rate');
        return $shipping_method;
    }
}