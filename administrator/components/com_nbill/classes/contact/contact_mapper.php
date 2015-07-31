<?php
class nBillContactMapper extends nBillDataMapper
{
    /** @var nBillContactFactory **/
    protected $factory;

    public function __construct(nbf_database $database, nBillContactFactory $factory)
    {
        parent::__construct($database, '#__nbill_contact');
        $this->factory = $factory;
    }

    public function loadContactsForEntity($entity_id)
    {
        $sql = "SELECT #__nbill_contact.* FROM #__nbill_contact
                INNER JOIN #__nbill_entity_contact ON #__nbill_contact.id = #__nbill_entity_contact.contact_id
                LEFT JOIN #__nbill_entity ON #__nbill_entity_contact.entity_id = #__nbill_entity.id
                WHERE #__nbill_entity_contact.entity_id = " . intval($entity_id) . "
                ORDER BY #__nbill_entity.primary_contact_id = #__nbill_contact.id DESC"; //Primary contact first
        $this->db->setQuery($sql);
        $db_contacts = $this->db->loadObjectList();

        $contacts = array();
        if ($db_contacts && count($db_contacts) > 0) {
            foreach ($db_contacts as $db_contact) {
                $contacts[] = $this->mapDbToContact($db_contact);
            }
        }
        return $contacts;
    }

    protected function mapDbToContact($db_contact)
    {
        $contact = $this->factory->createContact();
        if ($db_contact) {
            $contact_properties = get_object_vars($db_contact);
            if ($contact_properties && count($contact_properties) > 0) {
                foreach ($contact_properties as $key=>$value) {
                    if (property_exists($contact, $key)) {
                        $contact->$key = $value;
                    }
                }
            }
        }
        return $contact;
    }

    public function loadPrimaryContactForEntity($entity_id)
    {
        $db_contact = null;
        $sql = "SELECT #__nbill_contact.* FROM #__nbill_contact
                INNER JOIN #__nbill_entity ON #__nbill_entity.primary_contact_id = #__nbill_contact.id
                WHERE #__nbill_entity.id = " . intval($entity_id);
        $this->db->setQuery($sql);
        $this->db->loadObject($db_contact);
        return $this->mapDbToContact($db_contact);
    }

    public function findContactId($user_id)
    {
        $sql = "SELECT id FROM #__nbill_contact WHERE user_id = " . intval($user_id);
        $this->db->setQuery($sql);
        $contact_id = intval($this->db->loadResult());
        return $contact_id;
    }
}