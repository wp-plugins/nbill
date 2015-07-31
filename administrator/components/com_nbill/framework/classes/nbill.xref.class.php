<?php
/**
* Class file just containing static methods relating to cross references.
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

nbf_common::load_language("xref");

/**
* Static functions for dealing with cross reference tables
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_xref
{
	/**
	* Returns a list of allowed field types (checks for existence of whois component and writable upload directory)
	*/
	public static function get_field_types($form_id)
	{
		$nb_database = nbf_cms::$interop->database;

		$sql = "SELECT upload_path FROM #__nbill_order_form WHERE id = " . intval($form_id);
		$nb_database->setQuery($sql);
		$upload_path = $nb_database->loadResult();

		$field_types = self::load_xref("field_type");

		if (!$field_types)
		{
			$field_types = array();
		}
		$valid_field_types = array();
		foreach ($field_types as $field_type)
		{
			if ($field_type->code == "JJ")
			{
				//Make sure the J!Whois (or tp_whois) component is available (Mambo family only)
	            if (!file_exists(nbf_cms::$interop->site_base_path . "/components/com_jfwhois/classes/global.php") && !file_exists(nbf_cms::$interop->site_base_path . "/components/com_tpwhois/classes/global.php"))
	            {
	            	//Only allow if there is already a field of this type on the given form
	            	$sql = "SELECT id FROM #__nbill_order_form_fields WHERE form_id = " . intval($form_id) . " AND field_type = 'JJ'";
	            	$nb_database->setQuery($sql);
	            	if (!$nb_database->loadResult())
	            	{
						continue;
					}
	            }
			}
			if ($field_type->code == "KK")
			{
				//Make sure upload path exists and is writable
				$path_ok = false;
				if ($upload_path)
				{
					if (file_exists($upload_path))
					{
						if (is_writable($upload_path))
						{
							$path_ok = true;
						}
					}
				}
				if (!$path_ok)
				{
					//Only allow if there is already a field of this type on the given form
	            	$sql = "SELECT id FROM #__nbill_order_form_fields WHERE form_id = " . intval($form_id) . " AND field_type = 'KK'";
	            	$nb_database->setQuery($sql);
	            	if (!$nb_database->loadResult())
	            	{
						continue;
					}
				}
			}
			$valid_field_types[] = $field_type;
		}
		return $valid_field_types;
	}

	/**
    * Return a list of countries, optionally including the 2 special entries (EU, WW)
    * @param boolean $incl_eu_ww Whether or not to include the 2 special entries for Europe and Worldwide
    * @return array Associative array of countries: key = code, value = description
    */
    public static function get_countries($incl_eu_ww = false, $title_case = false)
    {
        $nb_database = nbf_cms::$interop->database;

        $sql = "SELECT id, code, description FROM #__nbill_xref_country_codes";
        if (!$incl_eu_ww)
        {
            $sql .= " WHERE code != 'EU' AND code != 'WW' ORDER BY description";
        }
        $nb_database->setQuery($sql);
        $country_codes = $nb_database->loadAssocList("id");
        if ($country_codes)
        {
            if ($title_case)
            {
                foreach ($country_codes as &$cc)
                {
                    $cc['description'] = nbf_common::nb_ucwords(nbf_common::nb_strtolower($cc['description']));
                }
            }
            return $country_codes;
        }
        else
        {
            return array();
        }
    }

    /**
    * Return a list of the currencies that have been defined
    * @return array Associative array of currencies: key = code, value = description
    */
    public static function get_currencies()
    {
        $nb_database = nbf_cms::$interop->database;

        $sql = "SELECT DISTINCT code, description FROM #__nbill_currency";
        $nb_database->setQuery($sql);
        $currency_codes = $nb_database->loadAssocList();
        if ($currency_codes)
        {
            return $currency_codes;
        }
        else
        {
            return array();
        }
    }

    /**
    * Loads all items in a cross reference table, and translates any constants into values
    * @param mixed $xref_name Name of the xref table to lookup
    */
    public static function load_xref($xref_name, $order_by_code = true, $sentence_case = false, $omit_codes = array(), $return_constants = false, $include_unpublished = false, $extra_params = null, $extension_table = false)
    {
    	$nb_database = nbf_cms::$interop->database;
        $ret_val = array();

        if (is_string($omit_codes))
        {
            $omit_codes = array($omit_codes);
        }

        if ($xref_name == "email_invoice")
        {
            //Don't show PDF option unless PDF generator is installed
            if (!nbf_common::pdf_writer_available())
            {
                $omit_codes[] = "AC";
                $omit_codes[] = "FF";
            }
        }

        if (substr($xref_name, 0, 13) == "[product_list")
        {
            $sql = "SELECT `id`, `id` AS `code`, `name` AS `description` FROM #__nbill_product";
            $where = "";
            if (nbf_common::nb_strpos($xref_name, ":") == 13)
            {
                $category_id = intval(substr($xref_name, 14));
                if ($category_id)
                {
                    $where = " WHERE category = " . $category_id;
                    if ($category_id == 1)
                    {
                        $where .= " OR category = -1"; //Include uncategorised items if category selected is 'root'
                    }
                }
            }
            if (count($omit_codes) > 0)
            {
                if (nbf_common::nb_strlen($where) == 0)
                {
                    $where = " WHERE ";
                }
                else
                {
                    $where .= " AND ";
                }
                $where .= "`gateway_id` NOT IN ('" . implode("','", $omit_codes) . "')";
            }
            $sql .= $where;
            $sql .= " ORDER BY ";
            $sql .= ($order_by_code ? "`id`" : "`name`");
            $nb_database->setQuery($sql);
            $entries = $nb_database->loadObjectList();
        }
        else if (substr($xref_name, 0, 13) == "[gateway_list")
        {
            $sql = "SELECT #__nbill_payment_gateway_config.`gateway_id` AS `code`, #__nbill_payment_gateway_config.`display_name` AS `description`,
                    #__nbill_payment_gateway_config.`ordering`, #__nbill_discounts.`is_fee` AS fee_or_discount
                    FROM #__nbill_payment_gateway_config
                    LEFT JOIN #__nbill_discounts ON #__nbill_payment_gateway_config.voucher_code = #__nbill_discounts.voucher AND #__nbill_payment_gateway_config.voucher_code > ''
                    WHERE ";
                    if (!$include_unpublished)
                    {
                        $sql .= "#__nbill_payment_gateway_config.published = ";
                    }
                    $sql .= "1 ";
            if (count($omit_codes) > 0)
            {
                $sql .= " AND `gateway_id` NOT IN ('" . implode("','", $omit_codes) . "')";
            }
            $sql .= "GROUP BY #__nbill_payment_gateway_config.`gateway_id` ORDER BY ";
            $sql .= ($order_by_code ? "`ordering`" : "`gateway_id`");
            $nb_database->setQuery($sql);
            $entries = $nb_database->loadObjectList();
        }
        else if (substr($xref_name, 0, 11) == "[order_list")
        {
            //Get list of orders for selected client, or in absence of client selection, all orders for the currently logged in user (restrict to prereq products if applicable)
            $entity_id = array();
            if (intval(nbf_common::get_param($_REQUEST, 'nbill_entity_id')))
            {
                $entity_id[] = intval(nbf_common::get_param($_REQUEST, 'nbill_entity_id'));
            }
            else
            {
                $sql = "SELECT entity_id FROM #__nbill_entity_contact INNER JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id WHERE #__nbill_contact.user_id = " . intval(nbf_cms::$interop->user->id);
                $nb_database->setQuery($sql);
                $entity_id = $nb_database->loadResultArray();
            }
            if (count($entity_id))
            {
                $sql = "SELECT `id` AS `code`, `order_no` AS `description`, relating_to FROM #__nbill_orders WHERE ";
                $add_closing_bracket = false;
                if ($xref_name == "[order_list_prereq]")
                {
                    $form_id = @$extra_params->form_id;
                    $query = "SELECT prerequisite_products FROM #__nbill_order_form WHERE id = " . intval($form_id);
                    $nb_database->setQuery($query);
                    $product_ids = $nb_database->loadResult();
                    if (!$product_ids)
                    {
                        $product_ids = 0;
                    }
                    if (@$extra_params->selected_value)
                    {
                        $sql .= "id = " . intval($extra_params->selected_value) . " OR (";
                        $add_closing_bracket = true;
                    }
                    $sql .= " product_id IN ($product_ids) AND ";
                }
                $sql .= "order_status != 'EE'
                        AND ((auto_renew = 1 AND (expiry_date = 0 OR expiry_date > " . nbf_common::nb_time() . ")) OR (auto_renew = 0 AND next_due_date > " . nbf_common::nb_time() . "))
                        AND client_id IN (" . implode(",", $entity_id) . ") ";
                if ($add_closing_bracket)
                {
                    $sql .= ")";
                }
                $sql .= "ORDER BY start_date, order_no";
                $nb_database->setQuery($sql);
                $entries = $nb_database->loadObjectList();
                if ($entries)
                {
                    for ($entry = 0; $entry < count($entries); $entry++)
                    {
                        if (strlen($entries[$entry]->relating_to) > 0)
                        {
                            $entries[$entry]->description .= " (" . $entries[$entry]->relating_to . ")";
                        }
                    }
                }
            }
            else
            {
                $entries = array();
            }
        }
        else
        {
            if ($extension_table)
            {
                $sql = "SELECT code, description FROM `$xref_name`";
            }
            else
            {
    	        $sql = "SELECT code, description FROM `#__nbill_xref_$xref_name`";
            }
    	    if (count($omit_codes) > 0)
    	    {
    		    $sql .= " WHERE code NOT IN ('" . implode("','", $omit_codes) . "')";
		    }
    	    $sql .= " ORDER BY ";
            $sql .= ($order_by_code ? (substr($xref_name, 0, 7) == "states_" ? "id" :"code") : "description");
            $nb_database->setQuery($sql);
    	    $entries = $nb_database->loadObjectList();
        }
    	if ($entries && !$return_constants)
		{
			//If descriptions are constants, replace with text from language file, and re-order
            $re_order = false;
			foreach ($entries as &$entry)
			{
                //See if we need to load a custom language file
                if (!defined($entry->description))
                {
                    nbf_common::load_language('xref.' . $xref_name);
                    if (!defined($entry->description))
                    {
                        nbf_common::load_language('xref.' . $xref_name . '.' . strtolower($entry->code));
                    }
                }
				if (defined($entry->description))
				{
					$entry->description = constant($entry->description);
					$re_order = true;
				}
                if ($sentence_case)
				{
					$entry->description = nbf_common::nb_ucwords(nbf_common::nb_strtolower($entry->description));
				}
			}
			if ($re_order)
			{
				usort($entries, 'compare_xref_entries_' . ($order_by_code ? (substr($xref_name, 0, 13) == "[gateway_list" ? 'ordering' : 'code') : 'description'));
			}
			$ret_val = $entries;
		}
		else
		{
            if ($entries)
            {
                $ret_val = $entries;
            }
            else
            {
			    $ret_val = array();
            }
		}

        return $ret_val;
	}

    /**
    * Look up an individual value from an xref table
    * @param string $xref_name Name of the xref
    * @param string $code Code to look up
    * @param boolean $extension_table Whether to use the full value of xref_name as the table name (because it belongs to an extension with its own prefix)
    * @return string The value of the given code
    */
    public static function lookup_xref_code($xref_name, $code, $extension_table = false)
    {
        $nb_database = nbf_cms::$interop->database;
        $sql = "SELECT description FROM ";
        if ($extension_table)
        {
            $sql .= "`$xref_name`";
        }
        else
        {
            $sql .= "`#__nbill_xref_$xref_name`";
        }
        $sql .= " WHERE code = '" . $nb_database->getEscaped($code) . "'";
        $nb_database->setQuery($sql);
        $result = $nb_database->loadResult();
        //See if we need to load a custom language file
        if (!defined($result))
        {
            nbf_common::load_language('xref.' . $xref_name);
            if (!defined($result))
            {
                nbf_common::load_language('xref.' . $xref_name . '.' . $code);
            }
        }
        if (defined($result))
        {
            $result = constant($result);
        }
        return $result;
    }
}

/**
* Comparer for sorting xref options into order based on 'code' property
* @param mixed $a First object to compare
* @param mixed $b Second object to compare
* @return mixed 0=Equal, 1=a>b, -1=b>a
*/
function compare_xref_entries_code($a, $b)
{
	if ($a->code == $b->code)
	{
	    return 0;
	}
	return ($a->code > $b->code) ? +1 : -1;
}

/**
* Comparer for sorting xref options into order based on 'description' property
* @param mixed $a First object to compare
* @param mixed $b Second object to compare
* @return mixed 0=Equal, 1=a>b, -1=b>a
*/
function compare_xref_entries_description($a, $b)
{
	if ($a->description == $b->description)
	{
	    return 0;
	}
	return ($a->description > $b->description) ? +1 : -1;
}

/**
* Comparer for sorting xref options into order based on 'ordering' property
* @param mixed $a First object to compare
* @param mixed $b Second object to compare
* @return mixed 0=Equal, 1=a>b, -1=b>a
*/
function compare_xref_entries_ordering($a, $b)
{
    if ($a->ordering == $b->ordering)
    {
        return 0;
    }
    return ($a->ordering > $b->ordering) ? +1 : -1;
}