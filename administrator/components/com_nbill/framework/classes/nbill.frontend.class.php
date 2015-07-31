<?php
/**
* Class file just containing static methods available to the front end features of nBill.
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
class nbf_frontend
{
    /**
    * E-mail address validator, based on unlicensed code in public domain from php.net user comments
    * @param string $email E-mail address to validate
    */
    public static function validate_email($email)
    {
        // Create the syntactical validation regular expression
        $regexp = "^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";

        // Presume that the email is invalid
        $valid = 0;

        // Validate the syntax
        if (preg_match("/$regexp/i", $email))
        {
            $valid = 1;
        }
        else
        {
          $valid = 0;
        }
        return $valid;
    }

    /**
    * Gets information about shipping pricing and tax
    * @param integer $shipping_id
    * @param string $currency
    * @param mixed $normal_tax_rate
    * @param mixed $shipping_service (return value)
    * @param mixed $shipping_unit_price (return value)
    * @param mixed $shipping_tax_rate (return value)
    * @param mixed $shipping_ledger_code (return value)
    * @param mixed $shipping_fixed_price (return value)
    */
    public static function get_shipping_info($shipping_id, $currency, $normal_tax_rate, &$shipping_service, &$shipping_unit_price, &$shipping_tax_rate, &$shipping_ledger_code, &$shipping_fixed_price)
    {
        $nb_database = nbf_cms::$interop->database;

        //Get shipping info, pricing, and tax
        $sql = "SELECT #__nbill_shipping.*, #__nbill_shipping_price.net_price_per_unit FROM
                #__nbill_shipping INNER JOIN #__nbill_shipping_price ON #__nbill_shipping.id =
                #__nbill_shipping_price.shipping_id WHERE id = " . intval($shipping_id) . " AND
                #__nbill_shipping_price.currency_code = '$currency'";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($shipping);
        if ($shipping)
        {
            $shipping_service = $shipping->service;
            $shipping_unit_price = $shipping->net_price_per_unit;
            $shipping_ledger_code = $shipping->nominal_ledger_code;
            if ($shipping->is_taxable)
            {
                $shipping_tax_rate = $shipping->tax_rate_if_different;
                if ($shipping_tax_rate == 0)
                {
                    $shipping_tax_rate = $normal_tax_rate;
                }
            }
            else
            {
                $shipping_tax_rate = 0;
            }
            $shipping_fixed_price = $shipping->is_fixed_per_invoice;
        }
        else
        {
            $shipping_service = "";
            $shipping_unit_price = 0;
            $shipping_tax_rate = 0;
            $shipping_ledger_code = -1;
            $shipping_fixed_price = 0;
        }
    }

    /**
    * Load the display options (if not already done) and return the value for the given option
    * @param string $option_name
    * @return mixed The value of the option
    */
    public static function get_display_option($option_name)
    {
        static $display_options = null;
        if (!$display_options)
        {
            $display_options = nbf_common::get_display_options();
        }
        if ($display_options && is_array($display_options) && array_key_exists($option_name, $display_options))
        {
            if (!is_array($display_options[$option_name]))
            {
                return $display_options[$option_name];
            }
            else
            {
                if (array_key_exists("value", $display_options[$option_name]))
                {
                    return $display_options[$option_name]["value"];
                }
                else
                {
                    return false;
                }
            }
        }
        else
        {
            return true;
        }
    }

    public static function get_css_class_for_option($display_option)
    {
        if ($display_option === true) {
            return '';
        }
        switch ($display_option) {
            case 2:
                return ' optional';
            case 3:
                return ' low-priority';
            case 4:
                return ' medium-priority';
            case 5:
                return ' high-priority';
            default:
                return '';
        }
    }

    public static function get_token($source, $fields, &$token, $nbill_posted_values = null, $allow_multiple = false)
    {
        //Check for ##number## token in source, return $_POSTed value of result
        $return_token = array();
        $this_token = "";
        $retVal = "";
        $array_ret_val = array();

        $token_start = nbf_common::nb_strpos($source, "##");
        while ($token_start !== false)
        {
            $token_end = nbf_common::nb_strpos($source, "##", $token_start + 1);
            if ($token_end !== false)
            {
                $this_token = substr($source, $token_start, ($token_end - $token_start) + 2);
                $return_token[] = $this_token;
                $field_id = str_replace("##", "", $this_token);
                foreach($fields as $field)
                {
                    if ($field->id == $field_id)
                    {
                        if ($nbill_posted_values)
                        {
                            if (nbf_common::nb_strlen(nbf_common::get_param($nbill_posted_values, 'ctl_' . $field->name)) > 0)
                            {
                                $array_ret_val[] = nbf_common::get_param($nbill_posted_values, 'ctl_' . $field->name);
                            }
                        }
                        else
                        {
                            if (nbf_common::nb_strlen(nbf_common::get_param($_POST, 'ctl_' . $field->name)) > 0)
                            {
                                $array_ret_val[] = nbf_common::get_param($_POST, 'ctl_' . $field->name);
                            }
                        }
                        if (!$allow_multiple)
                        {
                            break 2;
                        }
                    }
                }
                $token_start = nbf_common::nb_strpos($source, "##", $token_end + 2);
            }
            else
            {
                break;
            }
        }

        $token = implode("", $return_token);
        if (count($array_ret_val) > 0)
        {
            return implode(";", $array_ret_val);
        }
        else
        {
            return "";
        }
    }

    

    public static function load_contact_data()
    {
        $nb_database = nbf_cms::$interop->database;
        $contact_data = null;

        //Get contact record
        if (intval(nbf_cms::$interop->user->id))
        {
            $sql = "SELECT #__nbill_contact.*, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS `name`, '" . nbf_cms::$interop->user->username . "' AS username, '**********' AS password
                        FROM #__nbill_contact WHERE #__nbill_contact.user_id = " . intval(nbf_cms::$interop->user->id);
            $nb_database->setQuery($sql);
            $nb_database->loadObject($contact_data);
        }        if ($contact_data)
        {
            $contact_factory = new nBillContactFactory();
            $contact_service = $contact_factory->createContactService();
            $shipping_address = $contact_service->getShippingAddress(intval(@$contact_data->id));
            if (@$shipping_address->id) {
                $contact_data->same_as_billing = false;
                foreach(get_object_vars($shipping_address) as $key=>$value) {
                    $contact_data->{'shipping_' . str_replace('line', 'address', $key)} = $value;
                }
            } else {
                $contact_data->same_as_billing = true;
            }

            //Extract custom fields
            $custom_data = null;
            if ($contact_data->custom_fields)
            {
                $custom_data = unserialize($contact_data->custom_fields);
            }
            if ($custom_data && is_array($custom_data))
            {
                foreach ($custom_data as $key=>$value)
                {
                    $contact_data->$key = $value;
                }
            }
        }
        else
        {
            //Get default info from the default vendor
            $sql = "SELECT vendor_country FROM #__nbill_vendor WHERE default_vendor = 1";
            $nb_database->setQuery($sql);
            $nb_database->loadObject($vendor);

            //Set the email address, contact name, country
            $contact_data = new stdClass();
            $contact_data->id = null;
            $contact_data->email_address = nbf_cms::$interop->user->email;
            $name_data = explode(" ", nbf_cms::$interop->user->name);
            if (count($name_data) > 0)
            {
                $contact_data->first_name = $name_data[0];
            }
            if (count($name_data) > 1)
            {
                array_shift($name_data);
                $contact_data->last_name = implode(" ", $name_data);
            }
            if(nbf_cms::$interop->user->id)
            {
                $contact_data->country = $vendor->vendor_country;
                $contact_data->user_id = nbf_cms::$interop->user->id;
                $contact_data->username = nbf_cms::$interop->user->username;
                $contact_data->password = "**********";
            }
            else
            {
                $contact_data->country = null;
                $contact_data->user_id = null;
                $contact_data->username = null;
                $contact_data->password = null;
            }
            $contact_data->same_as_billing = true;
        }

        return $contact_data;
    }

    public static function load_entity_data($contact_id, $entity_id = null)
    {
        $nb_database = nbf_cms::$interop->database;

        //Get entity record(s)
        $sql = "SELECT #__nbill_entity.*, #__nbill_entity_contact.email_invoice_option, #__nbill_entity_contact.reminder_emails,
                #__nbill_entity_contact.allow_reminder_opt_in, #__nbill_entity_contact.allow_invoices
                FROM #__nbill_entity
                INNER JOIN #__nbill_entity_contact ON #__nbill_entity.id = #__nbill_entity_contact.entity_id
                WHERE #__nbill_entity_contact.contact_id = " . intval($contact_id) . "
                AND #__nbill_entity_contact.allow_update = 1";
        if ($entity_id && intval($entity_id) > 0)
        {
            $sql .= " AND #__nbill_entity.id = " . intval($entity_id);
        }
        $nb_database->setQuery($sql);
        $entity_data = $nb_database->loadObjectList();

        if ($entity_data)
        {
            foreach ($entity_data as &$this_entity)
            {
                $contact_factory = new nBillContactFactory();
                $entity_factory = new nBillEntityFactory();
                $entity_service = $entity_factory->createEntityService($contact_factory->createContactService());
                $shipping_address = $entity_service->getShippingAddress(intval(@$this_entity->id));
                if (@$shipping_address->id) {
                    $this_entity->same_as_billing = false;
                    foreach(get_object_vars($shipping_address) as $key=>$value) {
                        $this_entity->{'shipping_' . str_replace('line', 'address', $key)} = $value;
                    }
                } else {
                    $this_entity->same_as_billing = true;
                }

                //Extract custom fields
                $custom_data = null;
                if ($this_entity->custom_fields)
                {
                    $custom_data = unserialize($this_entity->custom_fields);
                }
                if ($custom_data && is_array($custom_data))
                {
                    foreach ($custom_data as $key=>$value)
                    {
                        $this_entity->$key = $value;
                    }
                }
            }
        }
        else
        {
            //Set the country and email option
            $new_entity = new stdClass();
            $new_entity->id = null;
            $sql = "SELECT vendor_country FROM #__nbill_vendor WHERE default_vendor = 1";
            $nb_database->setQuery($sql);
            $nb_database->loadObject($vendor);
            $new_entity->country = $nb_database->loadResult();
            $sql = "SELECT email_invoice_option FROM #__nbill_configuration WHERE id = 1";
            $nb_database->setQuery($sql);
            $new_entity->email_invoice_option = $nb_database->loadResult();
            $new_entity->same_as_billing = true;
            $entity_data = array();
            $entity_data[] = $new_entity;
        }

        return $entity_data;
    }

    /**
    * Fetch an image file
    */
    public static function show_image($file_name)
    {
        $loopbreaker = 0;
        while (ob_get_length() !== false)
        {
            $loopbreaker++;
            @ob_end_clean();
            if ($loopbreaker > 15)
            {
                break;
            }
        }
        if (nbf_common::nb_strpos($file_name, "..") !== false && dirname($file_name) != '../calendar/images')
        {
            exit; //Directory traversal
        }
        $file = realpath(nbf_cms::$interop->nbill_fe_base_path . "/images/" . $file_name);
        $image_type = nbf_common::nb_strtolower(nbf_common::nb_substr(strrchr($file,'.'),1));
        switch ($image_type)
        {
            case "jpg":
            case "jpeg":
            case "gif":
            case "png":
                if (file_exists($file))
                {
                    if (!headers_sent())
                    {
                        header("Content-Type: image/".$image_type."");
                        header("Content-Length: " . filesize($file));
                    }
                    @ob_clean();
                    @flush();
                    readfile($file);
                }
                break;
        }
        exit;
    }

    /**
    * Fetch a javascript or CSS file
    */
    public static function fetch_file($file_name)
    {
        $loopbreaker = 0;
        while (ob_get_length() !== false)
        {
            $loopbreaker++;
            @ob_end_clean();
            if ($loopbreaker > 15)
            {
                break;
            }
        }
        if (nbf_common::nb_strpos($file_name, "..") !== false)
        {
            exit; //Directory traversal
        }
        $file = nbf_cms::$interop->nbill_fe_base_path . "/" . $file_name;
        $file_type = nbf_common::nb_strtolower(substr(strstr($file_name,'.'),1));
        switch ($file_type)
        {
            case "js":
                $file_type = "javascript";
                break;
            case "css":
                break;
            default:
                //Invalid file type - only js or css allowed
                exit;
        }
        if (file_exists($file))
        {
            header("Content-Type: text/".$file_type."");
            readfile($file);
        }
        exit;
    }

    
}