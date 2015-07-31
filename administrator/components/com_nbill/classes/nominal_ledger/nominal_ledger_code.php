<?php
class nBillNominalLedgerCode
{
    public $code;
    public $description;

    public function __construct($code, $description)
    {
        $this->code = $code;
        $this->description = $description;
    }
}