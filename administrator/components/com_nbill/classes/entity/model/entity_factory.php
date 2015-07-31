<?php
class nBillEntityFactory
{
    public function createEntity($type = 'client')
    {
        switch ($type) {
            case 'supplier':
                $entity = new nBillSupplier();
                break;
            case 'client':
            default:
                $entity = new nBillClient();
                break;
        }
        return $entity;
    }

    public function createEntityService(nBillContactService $contact_service)
    {
        $entity_mapper = new nBillEntityMapper(nbf_cms::$interop->database, $this);
        $entity_address_mapper = new nBillAddressMapper(nbf_cms::$interop->database, '#__nbill_entity');
        $entity_service = new nBillEntityService($entity_mapper, $entity_address_mapper, $contact_service);
        return $entity_service;
    }
}