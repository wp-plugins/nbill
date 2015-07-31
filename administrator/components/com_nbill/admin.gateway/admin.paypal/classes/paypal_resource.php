<?php
/**
* Represents a Paypal resource.
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillPaypalResource
{
    /** @var int **/
    public $id;
    /** @var string **/
    public $type = 'preapp';
    /** @var string **/
    public $resource_id = '';
    /** @var string **/
    public $currency;
    /** @var string **/
    public $amount;
    /** @var string **/
    public $name;
    /** @var DateTime **/
    public $created_date;
    /** @var int **/
    public $interval_length;
    /** @var string **/
    public $interval_units;
    /** @var string **/
    public $payer_email;
    /** @var int **/
    public $g_tx_id;
    /** @var int **/
    public $entity_id;
    /** @var string **/
    public $order_ids;
    /** @var string **/
    public $document_ids;
    /** @var string **/
    public $status;
    /** @var int **/
    public $invitation_id;
}