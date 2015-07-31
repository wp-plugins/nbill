<?php
/**
* Class file just containing static method relating to address formatting.
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* Static function for address formatting
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_address
{
    public static function format_billing_address($address_1, $address_2, $address_3, $town, $state, $postcode, $country, $country_desc = "", $use_eu_format = false)
    {
        //This function is for backward compatability
        $nb_address = new nBillAddress($address_1, $address_2, $address_3, $town, $state, $postcode, $country);
        $nb_address->country_desc = $country_desc;
        $nb_address->use_eu_format = $use_eu_format;
        return $nb_address->format();
    }
}