<?php
class nBillContact
{
    /** @var int **/
    public $id;
    /** @var int **/
    public $user_id;
    /** @var string **/
    public $first_name;
    /** @var string **/
    public $last_name;
    /** @var nBillAddress **/
    public $billing_address;
    /** @var nBillAddress **/
    public $shipping_address;
    /** @var string **/
    public $email_address;
    /** @var string **/
    public $email_address_2;
    /** @var string **/
    public $telephone;
    /** @var string **/
    public $telephone_2;
    /** @var string **/
    public $mobile;
    /** @var string **/
    public $fax;
    /** @var string **/
    public $notes;
    /** @var \DateTime **/
    public $last_updated;
    /** @var array **/
    public $custom_fields;
}