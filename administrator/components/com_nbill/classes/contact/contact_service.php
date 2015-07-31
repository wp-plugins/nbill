<?php
class nBillContactService
{
    /** @var nBillEntityMapper **/
    protected $mapper;
    /** @var nBillAddressMapper **/
    protected $address_mapper;

    public function __construct(nBillContactMapper $mapper, nBillAddressMapper $address_mapper)
    {
        $this->mapper = $mapper;
        $this->address_mapper = $address_mapper;
    }

    /**
    * @param int $contact_id
    * @return nBillAddress
    */
    public function getShippingAddress($contact_id, $default_to_billing = true)
    {
        $address = $this->address_mapper->loadShippingAddress($contact_id);
        if ($default_to_billing && (!$address || !$address->id)) {
            $address = $this->getBillingAddress($contact_id);
        }
        return $address;
    }

    /**
    * @param int $contact_id
    * @return nBillAddress
    */
    public function getBillingAddress($contact_id)
    {
        $address = $this->address_mapper->loadBillingAddress($contact_id);
        return $address;
    }

    /**
    * @param array $posted_values
    */
    public function saveShippingAddress($posted_values, $contact_id)
    {
        $address = $this->address_mapper->mapShippingAddressFromRequest($posted_values);
        $this->address_mapper->saveShippingAddress($address, $contact_id);
    }

    public function deleteShippingAddress($contact_id)
    {
        $this->address_mapper->deleteShippingAddress($contact_id);
    }

    public function loadContactsForEntity($entity_id)
    {
        $contacts = $this->mapper->loadContactsForEntity($entity_id);
        foreach ($contacts as &$contact) {
            $contact->billing_address = $this->getBillingAddress($contact->id);
            $contact->shipping_address = $this->getShippingAddress($contact->id, false);
        }
        return $contacts;
    }

    public function loadPrimaryContactForEntity($entity_id)
    {
        $contact = $this->mapper->loadPrimaryContactForEntity($entity_id);
        $contact->billing_address = $this->getBillingAddress($contact->id);
        $contact->shipping_address = $this->getShippingAddress($contact->id, false);
        return $contact;
    }

    public function findContactId($user_id)
    {
        $contact_id = $this->mapper->findContactId($user_id);
        return $contact_id;
    }
}