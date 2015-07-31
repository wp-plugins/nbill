<?php
class nBillEntity
{
    /** @var int **/
    public $id;
    /** @var string *-*/
    public $company_name;
    /** @var nBillAddress **/
    public $billing_address;
    /** @var nBillAddress **/
    public $shipping_address;
    /** @var nBillContact **/
    public $primary_contact;
    /** @var array **/
    public $contacts;
    /** @var string **/
    public $reference;
    /** @var string **/
    public $default_language;
    /** @var string **/
    public $website_url;
    /** @var nBillCurrency **/
    public $default_currency;
    /** @var string **/
    public $tax_zone;
    /** @var string **/
    public $tax_exemption_code;
    /** @var string **/
    public $notes;
    /** @var \DateTime **/
    public $last_updated;
    /** @var array **/
    public $custom_fields;

    public function findBestShippingAddress($contact_id = 0)
    {
        $address = null;
        if (!$contact_id || ($this->primary_contact && $contact_id == $this->primary_contact->id)) {
            $address = $this->shipping_address;
        }
        if (!$address || !$address->id) {
            foreach ($this->contacts as $contact) {
                if ($contact->id == $contact_id) {
                    $address = $contact->shipping_address;
                    break;
                }
            }
        }
        if (!$address || !$address->id) {
            $address = $this->shipping_address;
        }

        return $address;
    }
}