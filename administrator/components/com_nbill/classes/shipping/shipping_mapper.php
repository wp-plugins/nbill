<?php
class nBillShippingMapper
{
    /** @var nbf_database **/
    protected $db;
    /** @var nBillShippingFactory **/
    protected $factory;
    /** @var nBillNumberFactory **/
    protected $number_factory;
    /** @var nBillNominalLedgerFactory **/
    protected $ledger_factory;
    /** @var nBillCurrencyService **/
    protected $currency_service;

    public function __construct(nbf_database $db, nBillShippingFactory $factory, nBillNumberFactory $number_factory, nBillNominalLedgerFactory $ledger_factory, nBillCurrencyService $currency_service)
    {
        $this->db = $db;
        $this->factory = $factory;
        $this->number_factory = $number_factory;
        $this->ledger_factory = $ledger_factory;
        $this->currency_service = $currency_service;
    }

    public function getAllShippingMethods($currency_code, $include_not_applicable = true)
    {
        $sql = "SELECT #__nbill_shipping.*, #__nbill_nominal_ledger.description AS ledger_description,
                        #__nbill_shipping_price.net_price_per_unit AS net_price
                FROM #__nbill_shipping
                LEFT JOIN #__nbill_nominal_ledger ON #__nbill_shipping.nominal_ledger_code = #__nbill_nominal_ledger.code
                LEFT JOIN #__nbill_shipping_price ON #__nbill_shipping.id = #__nbill_shipping_price.shipping_id ";
        if (strlen($currency_code) > 0) {
            $sql .= " WHERE #__nbill_shipping_price.currency_code = '" . $this->db->getEscaped($currency_code) . "'";
        }
        $sql .= " GROUP BY #__nbill_shipping.id ORDER BY #__nbill_shipping.code";
        $this->db->setQuery($sql);
        $db_shipping_methods = $this->db->loadObjectList();
        $shipping_methods = array();
        if ($include_not_applicable) {
            $shipping_method = $this->factory->createShipping($currency_code);
            $shipping_method->id = 0;
            $shipping_method->code = '';
            $shipping_method->name = NBILL_NOT_APPLICABLE;
            $shipping_methods[] = $shipping_method;
        }
        foreach ($db_shipping_methods as $db_shipping_method)
        {
            $shipping_method = $this->factory->createShipping($currency_code);
            $shipping_method->id = $db_shipping_method->id;
            $shipping_method->vendor_id = $db_shipping_method->vendor_id;
            $shipping_method->code = $db_shipping_method->code;
            $shipping_method->name = $db_shipping_method->service;
            $shipping_method->country = $db_shipping_method->country;
            $shipping_method->is_fixed_per_invoice = $db_shipping_method->is_fixed_per_invoice;
            $shipping_method->is_taxable = $db_shipping_method->is_taxable;
            $shipping_method->tax_rate_if_different = $this->number_factory->createNumber($db_shipping_method->tax_rate_if_different, 'tax_rate');
            $shipping_method->net_price = $this->number_factory->createNumberCurrency($db_shipping_method->net_price, $this->currency_service->findCurrency($currency_code));
            $shipping_method->parcel_tracking_url = $db_shipping_method->parcel_tracking_url;
            $shipping_method->nominal_ledger = $this->ledger_factory->createLedgerCode($db_shipping_method->nominal_ledger_code, $db_shipping_method->ledger_description);
            $shipping_methods[] = $shipping_method;
        }
        return $shipping_methods;
    }
}
