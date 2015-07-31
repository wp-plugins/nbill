<?php
class nBillConfigurationMapper extends nBillDataMapper
{
    public function __construct(nbf_database $database)
    {
        parent::__construct($database, '#__nbill_configuration');
    }

    public function saveObject($object)
    {
        if (nbf_cms::$interop->demo_mode) {
            $object->default_user_groups = '2';
            $object->api_url_geo_ip = 'http://www.telize.com/geoip/##ip##';
            $object->api_url_eu_vat_rates = 'http://nbill.co.uk/api/v1/eu_vat_rates.json';
        }
        parent::saveObject($object);
    }

    public function loadObject(&$object)
    {
        $ret_val = parent::loadObject($object);
        $sql = "SELECT `value` FROM #__nbill_display_options WHERE `name` = 'suppress_zero_tax'";
        $this->db->setQuery($sql);
        $object->never_hide_tax = $this->db->loadResult() == '0';
        return $ret_val;
    }
}