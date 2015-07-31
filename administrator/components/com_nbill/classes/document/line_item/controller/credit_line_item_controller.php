<?php
class nBillCreditLineItemController extends nBillLineItemController
{
    protected function loadLanguage()
    {
        parent::loadLanguage();
        nbf_common::load_language("credits");
    }
}