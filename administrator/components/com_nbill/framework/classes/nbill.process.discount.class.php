<?php
/**
* Class file just containing static methods relating to discount processing.
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
* Class just contains static functions for use anywhere within the code
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_discount
{
    

    /**
    * Calculate section discount amounts on invoice items
    * @param mixed $document_items
    */
    public static function apply_section_discounts(&$document_items, $document_id = 0, $accepted_only = false)
    {
        //Apply section discounts
        $last_doc_id = -1;
        foreach ($document_items as &$document_item)
        {
            $document_item = clone $document_item;
        }
        $section_items = array();
        foreach ($document_items as &$document_item)
        {
            if ((!$document_id || $document_item->document_id == $document_id))
            {
                if ($document_item->document_id != $last_doc_id)
                {
                    $section_items = array();
                    $last_doc_id = $document_item->document_id;
                }
                $section_items[] = &$document_item;
                if ($document_item->section_name)
                {
                    if ($document_item->section_discount_percent != 0)
                    {
                        foreach ($section_items as &$section_item)
                        {
                            if (!$accepted_only || $section_item->quote_item_accepted)
                            {
                                $this_net = $section_item->net_price_for_item;
                                $this_discount_net = ($this_net / 100) * $document_item->section_discount_percent;
                                $this_discount_tax = ($this_discount_net / 100) * $section_item->tax_rate_for_item;
                                $this_discount_gross = float_add($this_discount_net, $this_discount_tax);
                                $section_item->net_price_for_item = float_subtract($section_item->net_price_for_item, $this_discount_net);
                                $section_item->net_price_per_unit = format_number($section_item->net_price_for_item / ($section_item->no_of_units != 0 ? $section_item->no_of_units : 1));
                                $section_item->tax_for_item = float_subtract($section_item->tax_for_item, $this_discount_tax);
                                $section_item->gross_price_for_item = float_subtract($section_item->gross_price_for_item, $this_discount_gross);
                            }
                        }
                    }
                    $section_items = array();
                }
            }
        }
    }
}