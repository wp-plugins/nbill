<?php
class nBillTaxService
{
    /** @var nBillTaxMapper **/
    protected $mapper;
    /** @var nBillConfiguration **/
    protected $config;

    public function __construct(nBillTaxMapper $mapper, nBillConfiguration $config)
    {
        $this->mapper = $mapper;
        $this->config = $config;
    }

    /**
    * @param int $vendor_id
    * @return array Array of ID numbers of VAT records that could not be updated automatically as they are being used by one or more recurring orders
    */
    public function refreshEuTaxRecords($vendor_id, &$affected_orders = null)
    {
        //Transfer from master table to tax table for the given vendor
        $tax_rates = $this->mapper->loadEUTaxRates();

        $skipped_ids = array();
        if ($tax_rates) {
            foreach ($tax_rates as $tax_rate)
            {
                //Make sure tax rate is not already in use on any recurring orders....
                $existing_id = $this->taxRateChanged($tax_rate, $vendor_id);
                if ($existing_id && $this->taxRateInUse($existing_id, $affected_orders)) {
                    $skipped_ids[] = $existing_id;
                } else {
                    $this->mapper->insertOrUpdateEuTax($vendor_id, $tax_rate);
                }
            }
        }

        return $skipped_ids;
    }

    /**
    * Check whether the rate has changed
    * @param stdClass $tax_rate Tax rate information from database
    * @param int $vendor_id
    * @return int ID number of tax rate record in use, or false if tax rate is not in use
    */
    public function taxRateChanged($tax_rate, $vendor_id)
    {
        $tax_id = $this->mapper->taxRateChanged($tax_rate, $vendor_id);
        return $tax_id ? $tax_id : false;
    }

    /**
    * Chheck whether any recurring orders are currently using this tax rate
    *
    * @param int $tax_id ID of tax record to check
    * @param array $affected_orders Output param populated with order IDs affected by changes to the tax rate of the given tax ID
    * @param boolean $auto_renew_only Whether to check for auto-renewing orders only or not
    * @param boolean $manual_renew_only Whether ot check for manual-renewing orders only or not
    */
    public function taxRateInUse($tax_id, &$affected_orders, $auto_renew_only = false, $manual_renew_only = false)
    {
        $affected_orders = $this->mapper->taxRateInUse($tax_id, $auto_renew_only, $manual_renew_only);
        return count($affected_orders) > 0;
    }

    /**
    * Attempt connection to nBill server to download latest VAT rates
    */
    public function checkTaxRates($force_check = false)
    {
        if ($force_check || ($this->config->auto_check_eu_vat_rates && ($this->config->eu_tax_rate_refresh_timestamp < time() - 86400))) {
            //Only need to bother if we have something marked for electronic delivery
            if ($this->mapper->electronicDeliveryPresent()) {
                //Make sure database can hold UTF-8 if required (when coping tables from an old database, it can bring latin1 encoding with it)
                $this->mapper->checkDbEncoding();
                //Connect to nBill server...
                $remote = new nBillRemote($this->config->api_url_eu_vat_rates);
                $tax_rates_json = $remote->get();
                if ($tax_rates_json) {
                    $tax_rates = json_decode($tax_rates_json);
                    if ($tax_rates) {
                        $this->mapper->updateEuTaxRates($tax_rates);
                    }
                }
            }
            $this->config->eu_tax_rate_refresh_timestamp = time();
        }
    }

    /**
    * @param int $old_vat_id
    * @return nBillTaxRate
    */
    public function prepareNewTaxRate($old_vat_id)
    {
        $tax_rate = $this->mapper->getTaxRate($old_vat_id);
        $new_rate = $this->mapper->applyDefaultEuTaxRate($tax_rate);
        return $new_rate;
    }

    public function getElectronicDeliveryRateForCountry($vendor_id, $country_code, $client_id = null, $tax_exemption_code = '')
    {
        $tax_rate = $this->mapper->getRateForCountry($vendor_id, $country_code, true, $client_id, $tax_exemption_code);
        return $tax_rate;
    }

    public function getNormalRateForCountry($vendor_id, $country_code, $client_id = null, $tax_exemption_code = '')
    {
        $tax_rate = $this->mapper->getRateForCountry($vendor_id, $country_code, false, $client_id, $tax_exemption_code);
        return $tax_rate;
    }
}