<?php
class nBillTaxMapper
{
    /** @var nbf_database **/
    protected $db;
    /** @var nBillNumberFactory **/
    protected $number_factory;

    public function __construct(nbf_database $db, nBillNumberFactory $number_factory)
    {
        $this->db = $db;
        $this->number_factory = $number_factory;
    }

    public function loadEUTaxRates()
    {
        $sql = "SELECT * FROM #__nbill_eu_tax_rate_info ORDER BY country_code";
        $this->db->setQuery($sql);
        $db_rates = $this->db->loadObjectList();
        if (nbf_cms::$interop->char_encoding == 'iso-8859-1') {
            foreach ($db_rates as &$db_rate)
            {
                $this->convertEncoding($db_rate->tax_name, NBILL_DEFAULT_VAT_NAME);
                $this->convertEncoding($db_rate->tax_abbreviation, NBILL_DEFAULT_VAT_ABBREVIATION);
                $this->convertEncoding($db_rate->tax_reference_desc, NBILL_DEFAULT_VAT_TAX_REF_DESC);
            }
        }
        return $db_rates;
    }

    protected function convertEncoding(&$value, $default_value) {
        $string = new nBillString($value);
        $value = $string->convertEncoding($default_value);
    }

    public function insertOrUpdateEuTax($vendor_id, $tax_rate)
    {
        if (intval($vendor_id)) {
            $sql = "SELECT vendor_country FROM #__nbill_vendor WHERE id = " . intval($vendor_id);
            $this->db->setQuery($sql);
            $vendor_cc = $this->db->loadResult();
            $exempt_with_ref = $this->db->getEscaped($tax_rate->country_code) != $vendor_cc ? '1' : '0';

            $sql = "SELECT id FROM #__nbill_tax WHERE vendor_id = " . intval($vendor_id) . " AND country_code = '" . $this->db->getEscaped($tax_rate->country_code) . "' AND electronic_delivery = 1";
            $this->db->setQuery($sql);
            $existing_id = $this->db->loadResult();

            if ($existing_id) {
                $sql = "UPDATE #__nbill_tax SET tax_name = '" . $this->db->getEscaped($tax_rate->tax_name) . "',
                                                tax_abbreviation = '" . $this->db->getEscaped($tax_rate->tax_abbreviation) . "',
                                                tax_reference_desc = '" . $this->db->getEscaped($tax_rate->tax_reference_desc) . "',
                                                tax_rate = '" . $this->db->getEscaped($tax_rate->tax_rate) . "',
                                                electronic_delivery = 1, exempt_with_ref_no = " . $exempt_with_ref . "
                                            WHERE id = " . intval($existing_id);
            } else {
                $sql = "INSERT INTO #__nbill_tax (vendor_id, country_code, tax_name, tax_abbreviation, tax_reference_desc, tax_rate, electronic_delivery, exempt_with_ref_no) VALUES (
                                            " . intval($vendor_id) . ",
                                            '" . $this->db->getEscaped($tax_rate->country_code) . "',
                                            '" . $this->db->getEscaped($tax_rate->tax_name) . "',
                                            '" . $this->db->getEscaped($tax_rate->tax_abbreviation) . "',
                                            '" . $this->db->getEscaped($tax_rate->tax_reference_desc) . "',
                                            '" . $this->db->getEscaped($tax_rate->tax_rate) . "',
                                            1, " . $exempt_with_ref . ")";
            }
            $this->db->setQuery($sql);
            $this->db->query();
        }
    }

    public function taxRateChanged($tax_rate, $vendor_id)
    {
        $sql = "SELECT id, tax_rate FROM #__nbill_tax WHERE vendor_id = " . intval($vendor_id) . " AND country_code = '" . $this->db->getEscaped($tax_rate->country_code) . "' AND electronic_delivery = 1";
        $this->db->setQuery($sql);
        $tax = null;
        $this->db->loadObject($tax);
        if ($tax) {
            if ($tax->tax_rate != $tax_rate->tax_rate) {
                return intval($tax->id);
            }
        }
        return false;
    }

    public function taxRateInUse($vat_id, $auto_renew_only = false, $manual_renew_only = false)
    {
        $old_vat = null;
        $sql = "SELECT vendor_id, country_code, tax_rate, electronic_delivery FROM #__nbill_tax WHERE id = " . intval($vat_id);
        $this->db->setQuery($sql);
        $this->db->loadObject($old_vat);

        $sql = "SELECT #__nbill_orders.id FROM #__nbill_orders
                        INNER JOIN #__nbill_entity ON #__nbill_orders.client_id = #__nbill_entity.id
                        INNER JOIN #__nbill_contact ON #__nbill_entity.primary_contact_id = #__nbill_contact.id
                        LEFT JOIN #__nbill_product ON #__nbill_orders.product_id = #__nbill_product.id
                        INNER JOIN #__nbill_xref_eu_country_codes ON #__nbill_entity.country = #__nbill_xref_eu_country_codes.code
                        WHERE
                        (#__nbill_orders.total_tax_amount > 0 || #__nbill_orders.total_shipping_tax > 0)
                        AND #__nbill_orders.payment_frequency NOT IN ('AA', 'XX')
                        AND #__nbill_product.electronic_delivery = " . ($old_vat->electronic_delivery ? '1' : '0') . "
                        AND (#__nbill_orders.net_price > 0 OR #__nbill_orders.total_shipping_price > 0)";
        if ($auto_renew_only) {
            $sql .= " AND #__nbill_orders.auto_renew = 1";
        } else if ($manual_renew_only) {
            $sql .= " AND #__nbill_orders.auto_renew = 0";
        }
        switch ($old_vat->country_code)
        {
            case 'WW':
            case 'EU':
                $query = "SELECT country_code FROM #__nbill_tax
                        WHERE vendor_id = " . intval($old_vat->vendor_id) . "
                        AND country_code NOT IN ('EU', 'WW')
                        AND electronic_delivery = " . ($old_vat->electronic_delivery ? '1' : '0');
                $this->db->setQuery($query);
                $ccs = $this->db->loadResultArray();
                $sql .= " AND #__nbill_entity.country NOT IN ('" . implode(', ', $ccs) . "')";
                break;
            default:
                $sql .= " AND #__nbill_entity.country = '" . $old_vat->country_code . "'";
                break;
        }

        $sql .= " AND (FORMAT((#__nbill_orders.net_price / 100) * " . $old_vat->tax_rate . ", 2) = FORMAT(#__nbill_orders.total_tax_amount, 2)
                        || FORMAT(((#__nbill_orders.net_price + #__nbill_orders.total_tax_amount) / (" . $old_vat->tax_rate . " + 100)) * 100, 2) = FORMAT(#__nbill_orders.net_price, 2)
                        || (#__nbill_orders.total_shipping_price > 0 AND FORMAT((#__nbill_orders.total_shipping_price / 100) * " . $old_vat->tax_rate . ", 2) = FORMAT(#__nbill_orders.total_shipping_tax, 2)))";
        $this->db->setQuery($sql);
        $affected_orders = $this->db->loadResultArray();
        return $affected_orders;
    }

    public function checkDbEncoding()
    {
        if (nbf_cms::$interop->char_encoding == 'utf-8') {
            $sql = "SELECT character_set_name FROM information_schema.`COLUMNS` C
                    WHERE table_schema = '" . nbf_cms::$interop->db_connection->db_name . "'
                    AND table_name = '#__nbill_document'
                    AND column_name = 'tax_abbreviation'";
            $this->db->setQuery($sql);
            $char_set = $this->db->loadResult();
            if (strtolower(substr($char_set, 0, 4)) != 'utf8') {
                $sql = "SHOW TABLES LIKE '#__nbill_%'";
                $this->db->setQuery($sql);
                $tables = $this->db->loadResultArray();
                foreach ($tables as $table) {
                    $sql = "ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci";
                    $this->db->setQuery($sql);
                    $this->db->query();
                }
            }
        }
    }

    /**
    * Refresh the template records for EU tax rates (updated from nBill server)
    * @param array $tax_rates JSON decoded array of tax rate stdClass objects
    */
    public function updateEuTaxRates($tax_rates)
    {
        foreach ($tax_rates as $tax_rate) {
            $sql = "REPLACE INTO #__nbill_eu_tax_rate_info (country_code, tax_name, tax_abbreviation, tax_reference_desc, tax_rate)
                        VALUES (";
            $sql .= "'" . $tax_rate->country_code . "', ";
            $sql .= "'" . $tax_rate->tax_name . "', ";
            $sql .= "'" . $tax_rate->tax_abbreviation . "', ";
            $sql .= "'" . $tax_rate->tax_reference_desc . "', ";
            $sql .= "'" . $tax_rate->tax_rate . "')";
            $this->db->setQuery($sql);
            $this->db->query();
        }
    }

    public function getTaxRate($tax_id)
    {
        $db_tax_rate = null;
        $sql = "SELECT * FROM #__nbill_tax WHERE id = " . intval($tax_id);
        $this->db->setQuery($sql);
        $this->db->loadObject($db_tax_rate);
        return $this->mapToTaxRate($db_tax_rate);
    }

    public function mapToTaxRate($db_tax_rate, $tax_rate = null)
    {
        if ($tax_rate === null) {
            $tax_rate = new nBillTaxRate();
        }
        $properties = get_object_vars($db_tax_rate);
        foreach ($properties as $key=>$value)
        {
            if ($key == 'tax_rate') {
                $tax_rate->tax_rate = $this->number_factory->createNumber($db_tax_rate->tax_rate, 'tax_rate');
            } else if (property_exists($tax_rate, $key)) {
                $tax_rate->$key = $db_tax_rate->$key;
            }
        }
        return $tax_rate;
    }

    public function applyDefaultEuTaxRate(nBillTaxRate $tax_rate)
    {
        $db_tax_rate = null;
        $sql = "SELECT * FROM #__nbill_eu_tax_rate_info WHERE country_code = '" . $this->db->getEscaped($tax_rate->country_code) . "'";
        $this->db->setQuery($sql);
        $this->db->loadObject($db_tax_rate);
        if ($db_tax_rate) {
            $tax_rate = $this->mapToTaxRate($db_tax_rate, $tax_rate);
        }
        return $tax_rate;
    }

    public function getRateForCountry($vendor_id, $country_code, $electronic_delivery = false, $client_id = null, $tax_exemption_code = '')
    {
        $tax_zone = $this->getTaxZoneForClient($client_id);
        $tax_exemption_code = $tax_exemption_code ? $tax_exemption_code : $this->getTaxExemptionCodeForClient($client_id);

        $sql = "SELECT *
                FROM #__nbill_tax
                WHERE vendor_id = " . intval($vendor_id) . "
                AND country_code = '" . $this->db->getEscaped($country_code) . "'
                AND tax_zone = '$tax_zone'
                AND electronic_delivery = " . ($electronic_delivery ? '1' : '0');
        $this->db->setQuery($sql);
        $db_tax_rate = null;
        $this->db->loadObject($db_tax_rate);

        if (!$db_tax_rate) {
            $sql = "SELECT #__nbill_tax.*, #__nbill_xref_eu_country_codes.code
                    FROM #__nbill_tax
                    INNER JOIN #__nbill_xref_eu_country_codes ON #__nbill_xref_eu_country_codes.code = '" . $this->db->getEscaped($country_code) . "'
                    WHERE vendor_id = " . intval($vendor_id) . "
                    AND #__nbill_tax.country_code = 'EU'
                    AND #__nbill_tax.tax_zone = '$tax_zone'
                    AND electronic_delivery = " . ($electronic_delivery ? '1' : '0');
            $this->db->setQuery($sql);
            $this->db->loadObject($db_tax_rate);
        }

        if (!$db_tax_rate) {
            $sql = "SELECT *
                    FROM #__nbill_tax
                    WHERE vendor_id = " . intval($vendor_id) . "
                    AND country_code = 'WW'
                    AND tax_zone = '$tax_zone'
                    AND electronic_delivery = " . ($electronic_delivery ? '1' : '0');
        }

        if (!$db_tax_rate && !$electronic_delivery) {
            //No tax rates set up - rather than default to an electronic delivery one, use a blank one
            $db_tax_rate = new stdClass();
            $db_tax_rate->id = null;
            $db_tax_rate->tax_rate = 0;
        }

        if ($db_tax_rate) {
            if (strlen($tax_exemption_code) > 0 && $db_tax_rate->exempt_with_ref_no) {
                $db_tax_rate->tax_rate = 0;
            }
        }

        return $db_tax_rate ? $this->mapToTaxRate($db_tax_rate) : false;
    }

    protected function getTaxZoneForClient($client_id)
    {
        $tax_zone = '';
        if ($client_id && $client_id > 0) {
            $sql = "SELECT tax_zone FROM #__nbill_entity WHERE id = " . intval($client_id);
            $this->db->setQuery($sql);
            $tax_zone = $this->db->loadResult();
        }
        return $tax_zone;
    }

    protected function getTaxExemptionCodeForClient($client_id)
    {
        $tax_exemption_code = '';
        if ($client_id && $client_id > 0) {
            $sql = "SELECT tax_exemption_code FROM #__nbill_entity WHERE id = " . intval($client_id);
            $this->db->setQuery($sql);
            $tax_exemption_code = $this->db->loadResult();
        }
        return $tax_exemption_code;
    }

    public function electronicDeliveryPresent()
    {
        $sql = "SELECT id FROM #__nbill_product WHERE electronic_delivery = 1 LIMIT 1";
        $this->db->setQuery($sql);
        if ($this->db->loadResult()) {
            return true;
        }

        $sql = "SELECT id FROM #__nbill_document_items WHERE electronic_delivery = 1 LIMIT 1";
        $this->db->setQuery($sql);
        if ($this->db->loadResult()) {
            return true;
        }

        $sql = "SELECT id FROM #__nbill_transaction WHERE tax_rate_1_electronic_delivery = 1 OR tax_rate_2_electronic_delivery = 1 OR tax_rate_3_electronic_delivery = 1 LIMIT 1";
        $this->db->setQuery($sql);
        if ($this->db->loadResult()) {
            return true;
        }

        return false;
    }
}