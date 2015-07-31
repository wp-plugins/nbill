<?php
class nBillNominalLedgerFactory
{
    public function createLedgerCode($code, $description)
    {
        return new nBillNominalLedgerCode($code, $description);
    }
}