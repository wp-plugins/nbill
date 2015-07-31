<?php
class nBillEntityService
{
    /** @var nBillEntityMapper **/
    protected $mapper;
    /** @var nBillAddressMapper **/
    protected $address_mapper;
    /** @var nBillContactService **/
    protected $contact_service;

    public function __construct(nBillEntityMapper $mapper, nBillAddressMapper $address_mapper, nBillContactService $contact_service)
    {
        $this->mapper = $mapper;
        $this->address_mapper = $address_mapper;
        $this->contact_service = $contact_service;
    }

    /**
    * @param int $entity_id
    * @return nBillAddress
    */
    public function getShippingAddress($entity_id, $default_to_billing = true)
    {
        $address = $this->address_mapper->loadShippingAddress($entity_id);
        if ($default_to_billing && (!$address || !$address->id)) {
            $address = $this->getBillingAddress($entity_id);
        }
        return $address;
    }

    /**
    * @param int $entity_id
    * @return nBillAddress
    */
    public function getBillingAddress($entity_id)
    {
        $address = $this->address_mapper->loadBillingAddress($entity_id);
        return $address;
    }

    /**
    * @param int $entity_id
    * @param array $posted_values
    */
    public function saveShippingAddress($posted_values, $entity_id)
    {
        $address = $this->address_mapper->mapShippingAddressFromRequest($posted_values);
        $this->address_mapper->saveShippingAddress($address, $entity_id);
    }

    public function deleteShippingAddress($entity_id)
    {
        $this->address_mapper->deleteShippingAddress($entity_id);
    }

    /**
    * @param int $entity_id
    * @param boolean $load_contacts Whether or not to eager load contacts
    * @return {nBillClient|nBillSupplier}
    */
    public function loadEntity($entity_id, $load_contacts = false)
    {
        $entity = $this->mapper->loadEntity($entity_id);
        if ($entity) {
            $this->loadPrimaryContact($entity);
            $entity->billing_address = $this->getBillingAddress($entity_id);
            $entity->shipping_address = $this->getShippingAddress($entity_id, false);
            if ($load_contacts) {
                $this->loadContacts($entity);
            }
        }
        return $entity;
    }

    public function loadContacts(&$entity)
    {
        $entity->contacts = $this->contact_service->loadContactsForEntity($entity->id);
    }

    public function loadPrimaryContact(&$entity)
    {
        $entity->primary_contact = $this->contact_service->loadPrimaryContactForEntity($entity->id);
    }
}