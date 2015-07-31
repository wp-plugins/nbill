<?php
class nBillNominalLedgerMapper
{
    /** @var nbf_database **/
    protected $db;
    /** @var nBillNominalLedgerFactory **/
    protected $factory;

    public function __construct(nbf_database $db, nBillNominalLedgerFactory $factory)
    {
        $this->db = $db;
        $this->factory = $factory;
    }

    public function getAllLedgerCodes($include_miscellaneous = true)
    {
        $sql = "SELECT code, description FROM #__nbill_nominal_ledger ORDER BY code";
        $this->db->setQuery($sql);
        $db_ledgers = $this->db->loadObjectList();
        $ledger_codes = array();
        foreach ($db_ledgers as $db_ledger)
        {
            if (!$include_miscellaneous || $db_ledger->code != '-1') {
                $ledger_codes[] = $this->factory->createLedgerCode($db_ledger->code, $db_ledger->description);
            }
        }
        if ($include_miscellaneous) {
            $misc_present = false;
            foreach ($ledger_codes as $ledger_code)
            {
                if ($ledger_code->code == -1) {
                    $misc_present = true;
                    break;
                }
            }
            if (!$misc_present) {
                array_unshift($ledger_codes, $this->factory->createLedgerCode('-1', NBILL_MISCELLANEOUS));
            }
        }
        return $ledger_codes;
    }
}
