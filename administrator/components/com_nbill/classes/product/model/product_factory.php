<?php
class nBillProductFactory
{
    /** @var nBillNumberFactory **/
    protected $number_factory;
    /** @var nBillPaymentFactory **/
    protected $payment_factory;

    public function __construct(nBillNumberFactory $number_factory, nBillPaymentFactory $payment_factory)
    {
        $this->number_factory = $number_factory;
        $this->payment_factory = $payment_factory;
    }

    /** @return nBillCategory **/
    public function createCategory()
    {
        return new nBillCategory();
    }

    /** @return nBillProduct **/
    public function createProduct()
    {
        return new nBillProduct();
    }

    /** @return nBillPrice **/
    public function createPrice(nBillCurrency $currency)
    {
        $price = new nBillPrice();
        $price->currency = $currency;
        $price->setup_fee = $this->number_factory->createNumberCurrency(0, $currency);
        $price->payment_frequency = $this->payment_factory->createPaymentFrequency();
        $price->amount = $this->number_factory->createNumberCurrency(0, $currency);
        return $price;
    }

    public function createProductListView(nBillCategory $category_tree)
    {
        $view = new nBillProductListView($category_tree);
        return $view;
    }
}