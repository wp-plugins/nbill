<?php
class nBillLineItemQuote extends nBillLineItem
{
    /** @var string **/
    public $quote_pay_freq;
    /** @var boolean **/
    public $quote_auto_renew;
    /** @var string **/
    public $quote_relating_to;
    /** @var boolean **/
    public $quote_unique_invoice;
    /** @var boolean **/
    public $quote_mandatory;
    /** @var boolean **/
    public $quote_awaiting_payment;
    /** @var boolean **/
    public $quote_item_accepted;
    /** @var int **/
    public $quote_g_tx_id;
}