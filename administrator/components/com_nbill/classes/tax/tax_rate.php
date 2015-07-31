<?php
class nBillTaxRate
{
    /** @var int **/
    public $id;
    /** @var int **/
    public $vendor_id;
    /** @var string **/
    public $country_code;
    /** @var string **/
    public $tax_zone;
    /** @var string **/
    public $tax_name;
    /** @var string **/
    public $tax_abbreviation;
    /** @var string **/
    public $tax_reference_desc;
    /** @var nBillNumberTaxRate **/
    public $tax_rate;
    /** @var boolean **/
    public $online_exempt;
    /** @var string **/
    public $payment_instructions;
    /** @var string **/
    public $small_print;
    /** @var boolean **/
    public $exempt_with_ref_no;
    /** @var boolean **/
    public $electronic_delivery;
}