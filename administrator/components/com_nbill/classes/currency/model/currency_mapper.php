<?php
class nBillCurrencyMapper extends nBillDataMapper
{
    /** @var nBillCurrencyFactory **/
    protected $currency_factory;

    public function __construct(nbf_database $database, nBillCurrencyFactory $currency_factory)
    {
        parent::__construct($database, '#__nbill_currency');
        $this->currency_factory = $currency_factory;
    }

    /**
    * @param string $currency_code
    */
    public function findCurrency($currency_code)
    {
        static $cache = array();
        if (isset($cache[$currency_code])) {
            return $cache[$currency_code];
        }
        $currency = $this->currency_factory->createCurrency($currency_code);
        $this->findObject($currency, "`code` = '" . $this->db->getEscaped($currency_code) . "'");
        $cache[$currency_code] = $currency;
        return $currency;
    }

    public function mapFromArray($array)
    {
        $currency = $this->findCurrency(@$array['code']);
        foreach ($array as $key=>$value)
        {
            if (property_exists($currency, $key)) {
                $currency->$key = $value;
            }
        }
        return $currency;
    }
}