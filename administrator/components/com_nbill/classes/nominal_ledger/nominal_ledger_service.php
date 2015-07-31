<?php
class nBillNominalLedgerService
{
    /** @var nBillNominalLedgerMapper **/
    protected $mapper;

    public function __construct(nBillNominalLedgerMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function getAllLedgerCodes()
    {
        return $this->mapper->getAllLedgerCodes();
    }
}
