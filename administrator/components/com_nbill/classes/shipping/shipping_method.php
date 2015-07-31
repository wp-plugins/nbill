<?php
class nBillShippingMethod
{
    /** @var int **/
    public $id;
    /** @var int **/
    public $vendor_id;
    /** @var string **/
    public $country;
    /** @var string **/
    public $code;
    /** @var string **/
    public $name;
    /** @var boolean **/
    public $is_taxable;
    /** @var nBillINumberTaxRate **/
    public $tax_rate_if_different;
    /** @var nBillINumberCurrency **/
    public $net_price;
    /** @var boolean **/
    public $is_fixed_per_invoice;
    /** @var nBillNominalLedger **/
    public $nominal_ledger;
    /** @var string **/
    public $parcel_tracking_url;
}