<?php
/**
* Feature Manifest File for Paypal gateway - indicates what features are supported by this gateway
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nbill_paypal_manifest
{
    /** @var Whether or not recurring payments are supported */
    public $recurring_payments = true;
    /** @var Which pre-defined recurring payment frequencies are supported (comma separated list) */
    public $defined_frequencies = 'BB,BX,CC,DD,DX,EE,FF,GG,HH';
    /** @var Whether or not the first payment can be zero (free trial) */
    public $first_payment_zero = true;
    /** @var Whether or not the first payment can be a different (non-zero) amount to the repeat payments */
    public $first_payment_different = true;
    /** @var Whether or not a fixed number of payments or expiry date is allowed (eg. for paying a fixed sum in installments) */
    public $fixed_no_of_payments = true;
    /** @var If a fixed number of payments is allowed, but there is a minimum number of installments, this property should hold the minimum (it is assumed that 1 single installment is always allowed) */
    public $minimum_no_of_payments = 3; //Paypal will allow 1, 3, or more payments, but not 2!
}