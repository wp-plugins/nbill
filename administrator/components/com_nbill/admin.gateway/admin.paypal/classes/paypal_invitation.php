<?php
/**
* Represents a Paypal preapproval invitation.
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillPaypalInvitation
{
    /** @var int **/
    public $id;
    /** @var int **/
    public $client_id;
    /** @var string **/
    public $first_name;
    /** @var string **/
    public $last_name;
    /** @var string **/
    public $email_address;
    /** @var string **/
    public $sent_to;
    /** @var string **/
    public $currency;
    /** @var string **/
    public $max_amount;
    /** @var int **/
    public $payment_count;
    /** @var string **/
    public $description;
    /** @var string **/
    public $token;
    /** @var \DateTime **/
    public $date_sent;
}