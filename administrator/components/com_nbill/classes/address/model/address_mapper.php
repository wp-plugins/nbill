<?php
class nBillAddressMapper
{
    /** @var nbf_database **/
    protected $db;
    /** @var string **/
    protected $table_name;

    /**
    * @param nbf_database $db
    * @param string $table_name
    * @param int $record_id
    */
    public function __construct(nbf_database $db, $table_name)
    {
        $this->db = $db;
        $this->table_name = "`" . $table_name . "`";
    }

    public function loadAddress($address_id)
    {
        $address = new nBillAddress();
        $sql = "SELECT #__nbill_address.*, #__nbill_xref_country_codes.description AS country_desc
                FROM #__nbill_address
                LEFT JOIN #__nbill_xref_country_codes ON #__nbill_address.country COLLATE utf8_unicode_ci = #__nbill_xref_country_codes.code COLLATE utf8_unicode_ci
                WHERE #__nbill_address.id = " . intval($address_id);
        $this->db->setQuery($sql);
        $this->db->loadObject($address);
        if ($address && $address->id) {
            $address->country_desc = ucwords(strtolower($address->country_desc));
        }
        return $address;
    }

    /**
    * @return nBillAddress
    */
    public function loadShippingAddress($record_id)
    {
        $address = new nBillAddress();
        $sql = "SELECT #__nbill_address.*, #__nbill_xref_country_codes.description AS country_desc
                FROM #__nbill_address
                INNER JOIN " . $this->table_name . " ON " . $this->table_name . ".shipping_address_id = #__nbill_address.id
                LEFT JOIN #__nbill_xref_country_codes ON #__nbill_address.country COLLATE utf8_unicode_ci = #__nbill_xref_country_codes.code COLLATE utf8_unicode_ci
                WHERE " . $this->table_name . ".id = " . intval($record_id);
        $this->db->setQuery($sql);
        $this->db->loadObject($address);
        if ($address && $address->id) {
            $address->country_desc = ucwords(strtolower($address->country_desc));
        }
        return $address;
    }

    /**
    * @return nBillAddress
    */
    public function loadBillingAddress($record_id)
    {
        $address = new nBillAddress();
        $sql = "SELECT address_1 AS line_1, address_2 as line_2, address_3 as line_3, town, state, postcode, country, #__nbill_xref_country_codes.description AS country_desc
                FROM " . $this->table_name . "
                LEFT JOIN #__nbill_xref_country_codes ON " . $this->table_name . ".country = #__nbill_xref_country_codes.code
                WHERE " . $this->table_name . ".id = " . intval($record_id);
        $this->db->setQuery($sql);
        $this->db->loadObject($address);
        if ($address && $address->id) {
            $address->country_desc = ucwords(strtolower($address->country_desc));
        }
        return $address;
    }

    public function mapShippingAddressFromRequest($posted_values)
    {
        $address = new nBillAddress();
        $address->line_1 = nbf_common::get_param($posted_values, 'shipping_address_1');
        $address->line_2 = nbf_common::get_param($posted_values, 'shipping_address_2');
        $address->line_3 = nbf_common::get_param($posted_values, 'shipping_address_3');
        $address->town = nbf_common::get_param($posted_values, 'shipping_town');
        $address->state = nbf_common::get_param($posted_values, 'shipping_state');
        $address->postcode = nbf_common::get_param($posted_values, 'shipping_postcode');
        $address->country = nbf_common::get_param($posted_values, 'shipping_country');
        return $address;
    }

    public function saveShippingAddress(nBillAddress &$address, $record_id)
    {
        if ($record_id) {
            $sql = "SELECT shipping_address_id FROM " . $this->table_name . " WHERE id = " . $record_id;
            $this->db->setQuery($sql);
            $shipping_address_id = $this->db->loadResult();
            if ($shipping_address_id) {
                $sql = "UPDATE #__nbill_address SET
                        line_1 = '" . $address->line_1 . "',
                        line_2 = '" . $address->line_2 . "',
                        line_3 = '" . $address->line_3 . "',
                        town = '" . $address->town . "',
                        state = '" . $address->state . "',
                        postcode = '" . $address->postcode . "',
                        country = '" . $address->country . "'
                        WHERE id = " . intval($shipping_address_id);
            } else {
                $sql = "INSERT INTO #__nbill_address
                        (line_1, line_2, line_3, town, state, postcode, country)
                        VALUES (
                        '" . $address->line_1 . "',
                        '" . $address->line_2 . "',
                        '" . $address->line_3 . "',
                        '" . $address->town . "',
                        '" . $address->state . "',
                        '" . $address->postcode . "',
                        '" . $address->country . "'
                        )";
            }
            $this->db->setQuery($sql);
            $this->db->query();
            if (!$shipping_address_id) {
                $shipping_address_id = $this->db->insertid();
            }
            $address->id = $shipping_address_id;

            $sql = "UPDATE " . $this->table_name . " SET shipping_address_id = " . intval($shipping_address_id) . " WHERE id = " . $record_id;
            $this->db->setQuery($sql);
            $this->db->query();
        }
    }

    public function deleteShippingAddress($record_id)
    {
        $sql = "DELETE #__nbill_address.* FROM #__nbill_address INNER JOIN " . $this->table_name . " ON " . $this->table_name . ".shipping_address_id = #__nbill_address.id WHERE " . $this->table_name . ".id = " . $record_id;
        $this->db->setQuery($sql);
        $this->db->query();
        $sql = "UPDATE " . $this->table_name . " SET shipping_address_id = 0 WHERE id = " . $record_id;
        $this->db->setQuery($sql);
        $this->db->query();
    }
}