<?php
class nBillEntityMapper extends nBillDataMapper
{
    /** @var nbf_database **/
    protected $db;
    /** @var nBillEntityFactory **/
    protected $factory;

    public function __construct(nbf_database $database, nBillEntityFactory $factory)
    {
        parent::__construct($database, '#__nbill_entity');
        $this->factory = $factory;
    }

    public function loadEntity($entity_id)
    {
        $entity = null;
        $db_entity = null;
        $sql = "SELECT * FROM #__nbill_entity WHERE id = " . intval($entity_id);
        $this->db->setQuery($sql);
        $this->db->loadObject($db_entity);
        if ($db_entity) {
            $entity = $this->factory->createEntity($db_entity->is_client ? 'client' : ($db_entity->is_supplier ? 'supplier' : ''));
            foreach (get_object_vars($db_entity) as $key=>$value) {
                if (property_exists($entity, $key)) {
                    $entity->$key = $value;
                }
            }
        }
        return $entity;
    }
}