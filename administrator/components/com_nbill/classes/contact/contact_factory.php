<?php
class nBillContactFactory
{
    public function createContact()
    {
        $contact = new nBillContact();
        return $contact;
    }

    public function createContactService()
    {
        $contact_mapper = new nBillContactMapper(nbf_cms::$interop->database, $this);
        $contact_address_mapper = new nBillAddressMapper(nbf_cms::$interop->database, '#__nbill_contact');
        $contact_service = new nBillContactService($contact_mapper, $contact_address_mapper);
        return $contact_service;
    }
}