<?php
class nBillProduct
{
    /** @var int **/
    public $id;
    /** @var int **/
    public $vendor_id;
    /** @var nBillCategory **/
    public $category;
    /** @var string **/
    public $product_code;
    /** @var nBillNominalLedgerCode **/
    public $nominal_ledger;
    /** @var string **/
    public $name;
    /** @var string **/
    public $description;
    /** @var boolean **/
    public $is_freebie;
    /** @var boolean **/
    public $is_taxable;
    /** @var nBillNumberTaxRate */
    public $custom_tax_rate;
    /** @var boolean **/
    public $requires_shipping;
    /** @var array **/
    public $shipping_services;
    /** @var nBillINumberDecimal */
    public $shipping_units;
    /** @var array **/
    public $prices;
    /** @var boolean **/
    public $electronic_delivery;
}