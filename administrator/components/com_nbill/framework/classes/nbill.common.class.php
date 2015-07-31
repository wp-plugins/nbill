<?php
/**
* Class file just containing static methods available globally. I could have just used a plain
* list of functions, but wrapping them in a class gives us a bit of extra protection from
* function name clashes in case the CMS (or another extension) contains similar functions.
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//For backward compatability, make sure bootstrap is loaded
include_once(realpath(dirname(__FILE__) . '/../bootstrap.php'));

/**
* Class just contains static functions for use anywhere within the code
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_common
{
    /**
    * Detect the name of the page that was requested and return either the relative or absolute URL.
    * @param boolean Whether to return the absolute URL (true), or a relative one (false).
    */
    public static function get_requested_page($full_url = false)
    {
        $retVal = "";
        if (isset($_SERVER['REQUEST_URI']) && nbf_common::nb_strlen($_SERVER['REQUEST_URI']) > 0 && nbf_common::nb_strlen($_SERVER['REQUEST_URI']) < 5000) //IIS has been known to spew out gazillions of characters of junk
        {
            $retVal = $_SERVER['REQUEST_URI'];
        }
        else
        {
            if (isset($_SERVER['argv']))
            {
                $retVal = $_SERVER['SCRIPT_NAME'] . "?" . $_SERVER['argv'][0];
            }
            else if (isset($_SERVER['QUERY_STRING']))
            {
              $retVal = $_SERVER['SCRIPT_NAME'] . "?" . $_SERVER['QUERY_STRING'];
            }
            else
            {
                $retVal = $_SERVER['SCRIPT_NAME'];
            }
        }

        if ($full_url)
        {
            if (isset($_SERVER['HTTPS']) && nbf_common::nb_strtolower($_SERVER['HTTPS']) == 'on')
            {
                $prefix = "https://";
            }
            else
            {
                $prefix = "http://";
            }
            $retVal = $prefix . @$_SERVER['HTTP_HOST'] . $retVal;
        }

        return $retVal;
    }

    /**
    * Safely retrieve a value from an array for the given key
    * @param array $array The array that might or might not contain the given key (passed by reference to save memory and allow efficient comparison with superglobals)
    * @param string $key The key to look for in the array
    * @param mixed $default_value The value to return if the key is not found
    * @param boolean $no_escape Whether or not to suppress database escaping
    * @param boolean $encode_html Whether or not encoded HTML tags are allowed within the value (if true, HTML tags will be encoded, if false, they will be stripped out)
    * @param boolean $allow_anything Whether or not to return the contents exactly as they are without encoding or stripping anything (escaping will still happen if prior parameter requires it)
    * @return mixed The value held for the given key - by default, escaped and stripped of HTML if applicable
    */
    public static function get_param(&$array, $key, $default_value = null, $no_escape = false, $encode_html = false, $allow_non_js_html = false, $allow_anything = false)
    {
        if (isset($array[$key]))
        {
            $value =& $array[$key];
            if (is_array($value) || is_object($value)) //For arrays or objects, iterate through their members
            {
                foreach ($value as $element_key=>&$element_value)
                {
                    $element_value = self::get_param($value, $element_key, null, $no_escape, $encode_html, $allow_non_js_html, $allow_anything);
                }
                return $value;
            }
            else
            {
                if (!$allow_anything)
                {
                    if (!$allow_non_js_html)
                    {
                        //Convert notations into plain text (regexes taken from examples in public domain)
                        //$value = preg_replace('/&#x([a-f0-9]+);/mei', "chr(0x\\1)", $value); //deprecated (/e)
                        $value = preg_replace_callback('/&#x([a-f0-9]+);/mi', function($match){return chr("0x$match");}, $value);
                        //$value = preg_replace('/&#(\d+);/me', "chr(\\1)", $value); //deprecated (/e)
                        $value = preg_replace_callback('/&#(\d+);/m', function($match){return chr($match);}, $value);
                        $value = @html_entity_decode($value, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding);

                        if ($encode_html)
                        {
                            //Convert back into notation where necessary
                            $value = @htmlentities($value, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding);
                        }
                        else
                        {
                            //Get rid of anything that smells like HTML
                            $temp_value = '';
                            if (defined('ENT_SUBSTITUTE')) //PHP 5.4 onwards
                            {
                                $temp_value = @htmlspecialchars($value,  ENT_COMPAT | 0 | ENT_SUBSTITUTE, nbf_cms::$interop->char_encoding);
                            }
                            if (strlen($temp_value == 0)) //Try the default (if invalid will return empty string)
                            {
                                $temp_value = @htmlspecialchars($value, ENT_COMPAT | 0 | nbf_cms::$interop->char_encoding);
                            }
                            if (strlen($temp_value == 0) && defined('ENT_IGNORE')) //PHP 5.3 onwards
                            {
                                $temp_value = @htmlspecialchars($value, ENT_COMPAT | 0 | ENT_IGNORE, nbf_cms::$interop->char_encoding);
                            }
                            if (strlen($temp_value) == 0)
                            {
                                $temp_value = @htmlentities($value, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding); //Convert everything (returns question marks if necessary on invalid characters)
                            }
                            $value = str_replace('&amp;', '&', strip_tags($temp_value));
                        }
                    }
                    else
                    {
                        //If common js attack vector detected, return fully sanitised value
                        if (self::xss_check($value))
                        {
                            return self::get_param($array, $key, $default_value, true);
                        }
                    }
                }
                return $no_escape ? $value : (nbf_cms::$interop && nbf_cms::$interop->database ? nbf_cms::$interop->database->getEscaped($value) : addslashes($value));
            }
        }
        else
        {
            return $default_value;
        }
    }

    /**
    * Returns true if a javascript xss attack vector is detected (html is still allowed though)
    * @param mixed $value
    */
    public static function xss_check($value, $strict = false)
    {
        if (defined('NBILL_ADMIN'))
        {
            return false; //Allow administrators more freedom
        }
        if (self::xss_check_decoded($value, $strict)){return true;}
        $check_value = @urldecode($value);
        if (self::xss_check_decoded($check_value, $strict)){return true;}
        $check_value = strlen($value) % 4 == 0 ? @base64_decode($value, true) : false;
        if (self::xss_check_decoded($check_value, $strict)){return true;}
        $check_value = @self::hex_to_string($value);
        if (self::xss_check_decoded($check_value, $strict)){return true;}
        $check_value = @self::oct_to_string($value);
        if (self::xss_check_decoded($check_value, $strict)){return true;}
        return false;
    }
    public static function hex_to_string($hex)
    {
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2)
        {
            $string .= @chr(@hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }
    public static function oct_to_string($oct)
    {
        $string='';
        for ($i=0; $i < strlen($oct)-1; $i+=2)
        {
            $string .= @chr(@octdec($oct[$i].$oct[$i+1]));
        }
        return $string;
    }
    public static function xss_check_decoded($decoded_value, $strict = false)
    {
        $decoded_value = str_replace(" ", "", strtolower($decoded_value));
        if (strpos($decoded_value, '<script') !== false) {return true;}
        if ($strict)
        {
            if (strpos($decoded_value, 'alert(') !== false) {return true;}
            if (strpos($decoded_value, 'function(') !== false) {return true;}
            if (strpos($decoded_value, '&#') !== false) {return true;}
            if (strpos($decoded_value, '"') !== false) {return true;}
        }
        if (strpos($decoded_value, '\0') !== false) {return true;}
        if (strpos($decoded_value, '^V^') !== false) {return true;}
        if (strpos($decoded_value, '%00') !== false) {return true;}
        return false;
    }

    /**
    * Returns the format string for dates - either in PHP format or the javascript calendar format
    * @param boolean Whether or not to return the javascript calendar format string instead of the PHP format string
    * @return string Date format string (eg. "d/m/Y")
    */
    public static function get_date_format($for_calendar = false)
    {
        //Get date format
        $nb_database = nbf_cms::$interop->database;
        $sql = "SELECT date_format FROM #__nbill_configuration WHERE id = 1";
        $nb_database->setQuery($sql);
        $config = null;
        $nb_database->loadObject($config);
        $date_format = "d/m/Y";
        if ($config)
        {
            $date_format = $config->date_format;
        }

        //If using with a calendar, convert to format required by calendar control
        if ($for_calendar)
        {
            $date_format = str_replace("d", "dd", $date_format);
            $date_format = str_replace("j", "d", $date_format);
            $date_format = str_replace("m", "mm", $date_format);
            $date_format = str_replace("n", "m", $date_format);
            $date_format = str_replace("y", "yy", $date_format);
            $date_format = str_replace("Y", "yyyy", $date_format);
            $date_format = str_replace("o", "yyyy", $date_format);
            $date_format = nbf_common::nb_strtolower($date_format);

            //Check it is a recognised date format, or default to yyyy/mm/dd
            $valid_date = false;
            $format_parts = explode("/", $date_format);
            if (count($format_parts) < 2)
            {
                $format_parts = explode("-", $date_format);
            }
            if (count($format_parts) < 2)
            {
                $format_parts = explode("\\", $date_format);
            }
            if (count($format_parts) < 2)
            {
                $format_parts = explode(".", $date_format);
            }
            if (count($format_parts) == 3)
            {
                foreach ($format_parts as $format_part)
                {
                    switch ($format_part)
                    {
                        case "d":
                        case "dd":
                        case "m":
                        case "mm":
                        case "yy":
                        case "yyyy":
                            $valid_date = true;
                            break;
                        default:
                            $valid_date = false;
                            break;
                    }
                    if (!$valid_date)
                    {
                        break;
                    }
                }
            }
            if (!$valid_date)
            {
                $date_format = "yyyy/mm/dd";
            }
        }

        return $date_format;
    }

    /**
    * Abandon all output and redirect to the given URL
    * @param string $url URL to redirect to
    */
    public static function redirect($url)
    {
        //Clear buffer and redirect
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
        if (headers_sent())
        {
            echo "<script type=\"text/javascript\">window.location='$url';</script>";
        }
        else
        {
            header("Location: $url");
        }
        exit;
    }

    /**
    * Call any custom code held in files stored in the supplied event folder
    * @param string $event_name Name of the event folder
    * @param array $params Parameters to be passed to the event
    */
    public static function fire_event($event_name, $params)
    {
        //Allow calls by value or by ref
        self::fire_event_by_ref($event_name, $params);
    }

    /**
    * Call any custom code held in files stored in the supplied event folder, passing parameters by reference
    * @param string $event_name Name of the event folder
    * @param array $params Parameters to be passed to the event
    */
    public static function fire_event_by_ref($event_name, &$params)
    {
        //Check for PHP files in the relevant directory, and include them in alphabetical order if they exist
        clearstatcache();
        if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/events/$event_name"))
        {
            //Directory exists - get files in it
            $files = array_diff(scandir(nbf_cms::$interop->nbill_admin_base_path . "/events/$event_name/"), array('.', '..'));
            sort($files);
            foreach ($files as $file)
            {
                //Make sure it is a php file
                if (nbf_common::nb_substr($file, nbf_common::nb_strlen($file) - 4) == ".php" ||
                        nbf_common::nb_substr($file, nbf_common::nb_strlen($file) - 6) == ".phtml" ||
                        nbf_common::nb_substr($file, nbf_common::nb_strlen($file) - 5) == ".php3" ||
                        nbf_common::nb_substr($file, nbf_common::nb_strlen($file) - 5) == ".php4" ||
                        nbf_common::nb_substr($file, nbf_common::nb_strlen($file) - 5) == ".php5")
                {
                    include(nbf_cms::$interop->nbill_admin_base_path . "/events/" . $event_name . "/" . $file);
                }
            }
        }
    }

    /**
    * Return the IP address of the current user, if known
    * @return string IP address of the current user if known
    */
    public static function get_user_ip()
    {
        $ip_address = new nBillIpAddress();
        return $ip_address->ip_address;
    }

    /**
    * Get an associative array of display option values
    * @return array Array of display options
    */
    public static function get_display_options()
    {
        $nb_database = nbf_cms::$interop->database;
        $sql = "SELECT * FROM #__nbill_display_options";
        $nb_database->setQuery($sql);
        $options = $nb_database->loadAssocList("name");
        return $options;
    }

    /**
    * Check whether or not to show a payment link on an invoice
    * @param object $invoice Database row representing the invoice
    * @return boolean Whether or not to show the payment link
    */
    public static function check_show_paylink($invoice)
    {
        switch (@$invoice->document_type)
        {
            case null:
            case "IN":
            case "":
                //Check the rules to see if we should show a payment link for this invoice
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.frontend.class.php");
                $nb_database = nbf_cms::$interop->database;

                $suppress_paylink = true;

                if ($invoice->partial_payment && $invoice->gateway_txn_id && $invoice->payment_plan_id)
                {
                    $sql = "SELECT plan_type FROM #__nbill_payment_plans WHERE id = " . intval($invoice->payment_plan_id);
                    $nb_database->setQuery($sql);
                    switch ($nb_database->loadResult())
                    {
                        case 'BB': //Installments
                        case 'DD': //Deposit + installments
                            return false;
                    }
                }

                $invoice_total = isset($invoice->total_gross->value) ? $invoice->total_gross->value : $invoice->total_gross; //Legacy module might pass in raw value instead of number object
                if ($invoice_total > 0 && (!$invoice->paid_in_full && !$invoice->refunded_in_full && !$invoice->partial_refund))
                {
                    //Check whether invoice or order overrides global value for showing payment link
                    $use_global = true;
                    //Check invoice first
                    if ($invoice->show_invoice_paylink != 0)
                    {
                        $use_global = false;
                        if ($invoice->show_invoice_paylink == 1)
                        {
                            $suppress_paylink = false;
                        }
                    }

                    if ($use_global)
                    {
                        //Now check order
                        $sql = "SELECT show_invoice_paylink, payment_frequency FROM #__nbill_orders
                                INNER JOIN #__nbill_orders_document
                                ON #__nbill_orders_document.order_id = #__nbill_orders.id
                                WHERE #__nbill_orders_document.document_id = " . intval($invoice->id);
                        $nb_database->setQuery($sql);
                        $order_details = null;
                        $nb_database->loadObject($order_details);
                        if ($order_details)
                        {
                            if ($order_details->show_invoice_paylink)
                            {
                                $use_global = false;
                                if ($order_details->show_invoice_paylink == 1)
                                {
                                    $suppress_paylink = false;
                                }
                            }
                        }
                        else
                        {
                            //This is an ad-hoc invoice, so set payment frequency to one-off for threshold checking
                            $order_details = new stdClass();
                            $order_details->payment_frequency = "AA";
                        }

                        if ($use_global)
                        {
                            //Check vendor record
                            $sql = "SELECT show_paylink FROM #__nbill_vendor WHERE id = " . intval($invoice->vendor_id);
                            $nb_database->setQuery($sql);
                            if ($nb_database->loadResult() == 0)
                            {
                                $use_global = false;
                            }
                        }

                        if ($use_global)
                        {
                            //Check whether global display option suppresses
                            if (!nbf_frontend::get_display_option("payment_link"))
                            {
                                $use_global = false;
                            }

                            if ($use_global)
                            {
                                //Find global value by checking payment frequencies against threshold
                                if (!(nbf_frontend::get_display_option("payment_link") && nbf_frontend::get_display_option("status") == false))
                                {
                                    if (nbf_frontend::get_display_option("pay_freq_paylink_threshold"))
                                    {
                                        $paylink_threshold = nbf_frontend::get_display_option("pay_freq_paylink_threshold");
                                        if ($paylink_threshold == "0")
                                        {
                                            $paylink_threshold = "AA";
                                        }
                                    }
                                    else
                                    {
                                        $paylink_threshold = "AA";
                                    }
                                    if ((($paylink_threshold == "AA" && $order_details->payment_frequency < $paylink_threshold) || ($paylink_threshold != "AA" && $order_details->payment_frequency >= $paylink_threshold)) || $order_details->payment_frequency == "AA" || $order_details->payment_frequency == "XX")
                                    {
                                        $suppress_paylink = false;
                                    }
                                }
                            }
                        }
                    }
                }
                return !$suppress_paylink;
            default:
                return false;
        }

    }

    /**
    * Checks whether the code passed in relies on any legacy functions, variables, or constants and loads the legacy file if so
    * @param string $code The code to be evaluated
    */
    public static function check_for_legacy(&$code)
    {
        if (!defined("NBILL_LEGACY_LOADED"))
        {
            if (nbf_common::nb_strpos($code, 'mosGetParam') !== false || nbf_common::nb_strpos($code, '$my') !== false || nbf_common::nb_strpos($code, '$database') !== false
                    || nbf_common::nb_strpos($code, '$nb_database') !== false || nbf_common::nb_strpos($code, 'mosMail') !== false
                    || nbf_common::nb_strpos($code, '$mosConfig_absolute_path') !== false || nbf_common::nb_strpos($code, '$mosConfig_live_site') !== false
                    || nbf_common::nb_strpos($code, 'nbMail') !== false || nbf_common::nb_strpos($code, 'PostHTTP') !== false || nbf_common::nb_strpos($code, 'GetHTTP') !== false
                    || nbf_common::nb_strpos($code, 'nb_redirect') !== false || nbf_common::nb_strpos($code, '$nb_cms_version') !== false
                    || nbf_common::nb_strpos($code, 'INV_ADMIN_BASE_PATH') !== false || nbf_common::nb_strpos($code, 'INV_FE_BASE_PATH') !== false
                    || nbf_common::nb_strpos($code, 'calculate_totals') !== false || nbf_common::nb_strpos($code, 'hand_over_to_gateway') !== false
                    || nbf_common::nb_strpos($code, 'INV_CORE_') !== false)
            {
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.backward.compatibility.php");
            }
        }
    }

    /**
    * Load a language file
    * @param string $action The file to load (action name only, not full file name)
    */
    public static function load_language($action)
    {
        if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/language/" . nbf_cms::$interop->language . "/$action." . nbf_cms::$interop->language . ".php"))
        {
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/language/" . nbf_cms::$interop->language . "/$action." . nbf_cms::$interop->language . ".php");
        }
        else
        {
            if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/language/en-GB/$action.en-GB.php"))
            {
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/language/en-GB/$action.en-GB.php");
            }
        }
    }

    /**
    * Attempts to retrieve the country selected by the client on an order form or quote request form
    * @param array $fields The form fields
    * @param array $posted_values The contents of the $_POST array
    */
    public static function get_billing_country($fields, $posted_values = null)
    {
        $nb_database = nbf_cms::$interop->database;
        $country = "WW";
        if (!$posted_values)
        {
            $posted_values = $_REQUEST;
        }

        //Check whether any previously submitted fields are mapped to contact country
        if (is_array($fields))
        {
            foreach ($fields as &$field)
            {
                if ($field->contact_mapping == 'country')
                {
                    //Entity mapping takes precedence over contact mapping
                    $country = nbf_common::get_param($posted_values, 'ctl_' . $field->name);
                    //No break, as we will keep looking for an entity mapping
                }
                if ($field->entity_mapping == 'country')
                {
                    $country = nbf_common::get_param($posted_values, 'ctl_' . $field->name);
                    break;
                }
            }
        }

        //If not, try loading from database (if logged in)
        if (!$country && nbf_cms::$interop->user->id)
        {
            //Try entity if possible
            if (nbf_common::get_param($posted_values, 'nbill_entity_id'))
            {
                $sql = "SELECT country FROM #__nbill_entity WHERE id = " . intval(nbf_common::get_param($posted_values, 'nbill_entity_id'));
                $nb_database->setQuery($sql);
                $country = $nb_database->loadResult();
            }
            if (!$country)
            {
                $sql = "SELECT country FROM #__nbill_contact WHERE user_id = " . intval(nbf_cms::$interop->user->id);
                $nb_database->setQuery($sql);
                $country = $nb_database->loadResult();
            }
        }
        return $country;
    }

    /**
    * Deprecated
    * Returns the value of the given constant in the given language, if available (even though that language is not in use, so the constant actually holds a different value). If no translation is found, the constant's value is returned.
    * @param string $language The language to translate into
    * @param string $feature The feature in whose language file to lookup the value (ie. first part of file name)
    * @param string $constant The constant name
    */
    public static function parse_translation($language, $feature, $constant)
    {
        $translation = new nBillTranslator(nbf_cms::$interop->nbill_admin_base_path, nbf_cms::$interop->language);
        return $translation->parseTranslation($language, $constant, $feature);
    }

    public static function parse_and_execute_code($content, $convert_html_entities = false)
    {
        if (!nbf_cms::$interop->demo_mode)
        {
            $php_start = null;
            $php_end = 0;
            while($php_start !== false && $php_end !== false)
            {
                $php_start = nbf_common::nb_strpos($content, "\$\$", $php_end);
                if (!nbf_cms::$interop->demo_mode && $php_start !== false)
                {
                    $php_end = nbf_common::nb_strpos($content, "\$\$", $php_start + 2);
                    if ($php_end !== false)
                    {
                        $pre_code = substr($content, 0, $php_start);
                        $php_code = substr($content, $php_start + 2, ($php_end - $php_start) - 2);
                        if ($convert_html_entities)
                        {
                            $php_code = html_entity_decode($php_code, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding);
                        }
                        $post_code = substr($content, $php_end + 2);
                        if (!$post_code) {$post_code = "";}
                        self::check_for_legacy($php_code);
                        ob_start();
                        $return_value = eval($php_code);
                        $evaluated_content = ob_get_clean();
                        $content = $pre_code . $evaluated_content . $return_value . $post_code;
                        $php_end = $php_start; //Carry on checking from where we started, as that token has gone now
                    }
                }
            }
        }
        return $content;
    }

    /**
    * Pass in a payment frequency code, and this function will return the column name.
    * Pass in a column name, and it will return the frequency code.
    * @param mixed $known_value
    */
    public static function convert_pay_freq($known_value)
    {
        $freqs = array();
        $freqs["SETUP"] = "net_price_setup_fee";
        $freqs["AA"] = "net_price_one_off";
        $freqs["BB"] = "net_price_weekly";
        $freqs["BX"] = "net_price_four_weekly";
        $freqs["CC"] = "net_price_monthly";
        $freqs["DD"] = "net_price_quarterly";
        $freqs["DX"] = "net_price_semi_annually";
        $freqs["EE"] = "net_price_annually";
        $freqs["FF"] = "net_price_biannually";
        $freqs["GG"] = "net_price_five_years";
        $freqs["HH"] = "net_price_ten_years";

        return array_key_exists($known_value, $freqs) ? $freqs[$known_value] : array_search($known_value, $freqs);
    }

    /**
    * Recalculate price, shipping, and tax for all orders passed in
    * @param mixed $product_id
    * @param mixed $order_ids
    * @param array $orders Optionally pass in the orders to recalculate (if passed in, results will be returned instead of updating database)
    */
    public static function update_existing_orders($product_id, $order_ids, $orders = array())
    {
        $nb_database = nbf_cms::$interop->database;

        $return_results = false;
        if (!$orders || count($orders) == 0)
        {
            //Load client and order details
            $sql = "SELECT #__nbill_orders.id, #__nbill_orders.net_price, #__nbill_orders.total_tax_amount,
                    #__nbill_orders.total_shipping_price, #__nbill_orders.total_shipping_tax, #__nbill_orders.quantity,
                    #__nbill_orders.shipping_id, #__nbill_orders.is_online,
                    #__nbill_orders.tax_exemption_code, #__nbill_orders.payment_frequency, #__nbill_orders.currency,
                    #__nbill_entity.country, #__nbill_xref_eu_country_codes.code AS in_eu, #__nbill_entity.tax_zone,
                    #__nbill_entity.tax_exemption_code, #__nbill_vendor.vendor_country
                    FROM #__nbill_orders
                    INNER JOIN #__nbill_entity ON #__nbill_orders.client_id = #__nbill_entity.id
                    LEFT JOIN #__nbill_xref_eu_country_codes ON #__nbill_entity.country = #__nbill_xref_eu_country_codes.code
                    INNER JOIN #__nbill_vendor ON #__nbill_orders.vendor_id = #__nbill_vendor.id
                    WHERE #__nbill_orders.id IN (" . implode(",", $order_ids) . ")
                    GROUP BY #__nbill_orders.id";
            $nb_database->setQuery($sql);
            $orders = $nb_database->loadObjectList();
        }
        else
        {
            $return_results = true;
        }

        //Load product details and price(s)
        $product = null;
        $sql = "SELECT is_freebie, is_taxable, requires_shipping, shipping_units, custom_tax_rate, electronic_delivery FROM #__nbill_product WHERE id = $product_id";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($product);
        $sql = "SELECT * FROM #__nbill_product_price WHERE product_id = $product_id";
        $nb_database->setQuery($sql);
        $new_prices = $nb_database->loadObjectList();

        //Load shipping details
        $shipping_ids = array();
        foreach ($orders as $order)
        {
            if ($order->shipping_id > 0)
            {
                $shipping_ids[] = $order->shipping_id;
            }
        }
        if (count($shipping_ids) > 0)
        {
            $shipping_ids = array_unique($shipping_ids);
            $sql = "SELECT id, is_taxable, tax_rate_if_different, is_fixed_per_invoice FROM #__nbill_shipping WHERE id IN (" . implode(",", $shipping_ids) . ")";
            $nb_database->setQuery($sql);
            $shippings = $nb_database->loadObjectList();
            $sql = "SELECT * FROM #__nbill_shipping_price WHERE shipping_id IN (" . implode(",", $shipping_ids) . ")";
            $nb_database->setQuery($sql);
            $shipping_prices = $nb_database->loadObjectList();
        }

        //Load tax details
        $sql = "SELECT vendor_id, country_code, tax_zone, tax_rate, online_exempt, exempt_with_ref_no, electronic_delivery FROM #__nbill_tax";
        $nb_database->setQuery($sql);
        $taxes = $nb_database->loadObjectList();

        //Go through each order and recalculate the values
        $config = nBillConfigurationService::getInstance()->getConfig();
        $updated_count = 0;
        foreach ($orders as &$order)
        {
            $net_price = $order->net_price;
            $total_tax_amount = $order->total_tax_amount;
            $total_shipping_price = $order->total_shipping_price;
            $total_shipping_tax = $order->total_shipping_tax;
            $clear_shipping_service = false;

            $freq_col = nbf_common::convert_pay_freq($order->payment_frequency);
            if (nbf_common::nb_strlen($freq_col) > 0)
            {
                //Work out net price
                if ($product->is_freebie)
                {
                    $net_price = 0;
                }
                else
                {
                    foreach ($new_prices as $new_price)
                    {
                        if ($new_price->currency_code == $order->currency)
                        {
                            $net_price = format_number($new_price->$freq_col * $order->quantity, $config->precision_currency);
                            break;
                        }
                    }
                }

                //Work out tax
                if (!$product->is_taxable)
                {
                    $total_tax_amount = 0;
                }
                else
                {
                    $found_tax = false;
                    $tax_record = null;

                    //Try to find matching tax zone
                    foreach ($taxes as $tax)
                    {
                        if ($tax->country_code == $order->country && nbf_common::nb_strlen($tax->tax_zone) > 0 && $tax->tax_zone == $order->tax_zone)
                        {
                            $tax_record = $tax;
                            $found_tax = true;
                            break;
                        }
                    }

                    //Try to find matching country
                    if (!$found_tax)
                    {
                        foreach ($taxes as $tax)
                        {
                            if ($tax->country_code == $order->country && $tax->electronic_delivery == $product->electronic_delivery && nbf_common::nb_strlen($tax->tax_zone) == 0 && nbf_common::nb_strlen($order->tax_zone) == 0)
                            {
                                $tax_record = $tax;
                                $found_tax = true;
                                break;
                            }
                        }
                    }

                    //Check for EU
                    if (!$found_tax && $order->in_eu && $order->country != $order->vendor_country)
                    {
                        foreach ($taxes as $tax)
                        {
                            if (nbf_common::nb_strtoupper($tax->country_code) == 'EU' && $tax->electronic_delivery == $product->electronic_delivery && nbf_common::nb_strlen($tax->tax_zone) == 0 && nbf_common::nb_strlen($order->tax_zone) == 0)
                            {
                                $tax_record = $tax;
                                $found_tax = true;
                                break;
                            }
                        }
                    }

                    //Go large
                    if (!$found_tax && $order->country != $order->vendor_country)
                    {
                        foreach ($taxes as $tax)
                        {
                            if (nbf_common::nb_strtoupper($tax->country_code) == 'WW' && $tax->electronic_delivery == $product->electronic_delivery && nbf_common::nb_strlen($tax->tax_zone) == 0 && nbf_common::nb_strlen($order->tax_zone) == 0)
                            {
                                $tax_record = $tax;
                                $found_tax = true;
                                break;
                            }
                        }
                    }

                    if ($found_tax)
                    {
                        if (($tax->online_exempt && $order->is_online) ||
                            ($tax->exempt_with_ref_no && nbf_common::nb_strlen($order->tax_exemption_code) > 0))
                        {
                            $total_tax_amount = 0;
                        }
                        else
                        {
                            if ($tax->tax_rate > 0 && $product->custom_tax_rate > 0)
                            {
                                $total_tax_amount = format_number(($net_price / 100) * $product->custom_tax_rate, $config->precision_currency);
                            }
                            else
                            {
                                $total_tax_amount = format_number(($net_price / 100) * $tax->tax_rate, $config->precision_currency);
                            }
                        }
                    }
                }

                //Work out shipping
                if (!$product->requires_shipping)
                {
                    $total_shipping_price = 0;
                    $total_shipping_tax = 0;
                    $clear_shipping_service = true;
                }
                else
                {
                    foreach ($shippings as $shipping)
                    {
                        if ($shipping->id == $order->shipping_id)
                        {
                            if ($shipping->is_fixed_per_invoice)
                            {
                                //Keep values the same - changes to product record would have had no effect
                            }
                            else
                            {
                                foreach ($shipping_prices as $shipping_price)
                                {
                                    if ($shipping_price->currency_code == $order->currency)
                                    {
                                        if ($product->shipping_units < 0)
                                        {
                                            //Don't multiply by quantity
                                            $total_shipping_price = format_number($shipping_price->net_price_per_unit * abs($product->shipping_units), $config->precision_currency);
                                        }
                                        else
                                        {
                                            $total_shipping_price = format_number($shipping_price->net_price_per_unit * $product->shipping_units * $order->quantity, $config->precision_currency);
                                        }
                                        //Work out shipping tax
                                        if ($shipping->is_taxable)
                                        {
                                            if ($shipping->tax_rate_if_different > 0)
                                            {
                                                $total_shipping_tax = format_number(($total_shipping_price / 100) * $shipping->tax_rate_if_different, $config->precision_currency);
                                            }
                                            else
                                            {
                                                $total_shipping_tax = format_number(($total_shipping_price / 100) * $tax->tax_rate, $config->precision_currency);
                                            }
                                        }
                                        else
                                        {
                                            $total_shipping_tax = 0;
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //Make sure something has changed
            if ($net_price != $order->net_price ||
                $total_tax_amount != $order->total_tax_amount ||
                $total_shipping_price != $order->total_shipping_price ||
                $total_shipping_tax != $order->total_shipping_tax)
            {
                if ($return_results)
                {
                    $order->net_price = $net_price;
                    $order->total_tax_amount = $total_tax_amount;
                    $order->total_shipping_price = $total_shipping_price;
                    $order->total_shipping_tax = $total_shipping_tax;
                }
                else
                {
                    $sql = "UPDATE #__nbill_orders SET net_price = '$net_price', total_tax_amount = '$total_tax_amount',
                            total_shipping_price = '$total_shipping_price', total_shipping_tax = '$total_shipping_tax'";
                    if ($clear_shipping_service)
                    {
                        $sql .= ", shipping_id = 0, shipping_service = ''";
                    }
                    $sql .= " WHERE id = $order->id";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                    $updated_count++;
                }
            }
        }

        if ($return_results)
        {
            return $orders;
        }
        else
        {
            nbf_common::load_language("products");
            nbf_globals::$message = sprintf(NBILL_EXISTING_ORDERS_UPDATED, $updated_count);
        }
    }

    /**
    * Load the form definition from a file
    * @param mixed $form_type typically QU or OR, but extensions can use others
    * @return array
    */
    public static function load_form_def($form_type)
    {
        if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/form_defs/" . $form_type . ".php"))
        {
            include(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/form_defs/" . $form_type . ".php");
        }
        if (!isset($form_def))
        {
            //Should not happen, but just in case, we'll load the default values
            $form_def['form_type'] = $form_type;
            $form_def['check_duplicate_products'] = $form_type == 'OR';
            $form_def['client_mapping_required'] = true;
            $form_def['action'] = $form_type == 'QU' ? "quote_request" : "orderforms";
            $form_def['fe_action'] = $form_type == 'QU' ? "quotes" : "orders";
            $form_def['class'] = $form_def['action'];
            $form_def['lang_suffix'] = $form_type == 'QU' ? "_QUOTE" : "";
            $form_def['edit_link_prefix'] = nbf_cms::$interop->admin_page_prefix . "&action=" . $form_def['action'] . "&task=edit&";
            $form_def['icon'] = sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, $form_def['action']);
            $form_def['default'] = array();
            $form_def['suppressed'] = array();
            $form_def['options_included'] = array();
            $form_def['options_included'][] = 'details_offline_payment_redirect';
            if ($form_type == 'QU')
            {
                $form_def['suppressed'][] = 'tab_order';
                $form_def['suppressed'][] = 'details_gateway';
                $form_def['suppressed'][] = 'email_pending_to_client';
                $form_def['suppressed'][] = 'email_admin_pending';
                $form_def['suppressed'][] = 'auto_handle_shipping';
                $form_def['suppressed'][] = 'advanced_pre_calculate_code';
                $form_def['suppressed'][] = 'advanced_order_creation_code';
                $form_def['suppressed'][] = 'editor_related_product';
                $form_def['suppressed'][] = 'editor_order_value';
            }
            else
            {
                $form_def['suppressed'][] = 'details_quote_accept_redirect';
            }
        }
        if (!array_key_exists('options_included', $form_def))
        {
            $form_def['options_included'] = array();
        }
        return $form_def;
    }

    public static function detach_file($attachment_id)
    {
        $nb_database = nbf_cms::$interop->database;

        $success = false;
        $attachment = null;
        $sql = "DELETE FROM #__nbill_supporting_docs WHERE id = " . intval($attachment_id);
        $nb_database->setQuery($sql);
        $nb_database->query();
    }

    public static function delete_file($attachment_id)
    {
        $nb_database = nbf_cms::$interop->database;

        $attachment = null;
        $sql = "SELECT file_path, file_name FROM #__nbill_supporting_docs WHERE id = " . intval($attachment_id);
        $nb_database->setQuery($sql);
        $nb_database->loadObject($attachment);
        if ($attachment)
        {
            $file_name = realpath($attachment->file_path . "/" . $attachment->file_name);
            if ($file_name)
            {
                @unlink($file_name);
            }
        }
        $success = !@file_exists($file_name);

        if ($success)
        {
            $sql = "DELETE FROM #__nbill_supporting_docs WHERE file_path = '" . addslashes(realpath($attachment->file_path)) . "' AND file_name = '" . $attachment->file_name . "'";
            $nb_database->setQuery($sql);
            $nb_database->query();
        }
        else
        {
            nbf_globals::$message = sprintf(NBILL_DELETE_FILE_FAILED, $attachment->file_name);
        }
    }

    public static function pdf_writer_available()
    {
        return file_exists(nbf_cms::$interop->nbill_fe_base_path . "/dompdf/dompdf.php")
            || file_exists(nbf_cms::$interop->nbill_admin_base_path . "/dompdf/dompdf.php")
            || file_exists(nbf_cms::$interop->nbill_fe_base_path . "/pdfwriter/nbill_to_pdf.php")
            || file_exists(nbf_cms::$interop->nbill_admin_base_path . "/pdfwriter/nbill_to_pdf.php")
            || file_exists(nbf_cms::$interop->nbill_fe_base_path . "/html2pdf/nbill_to_pdf.php")
            || file_exists(nbf_cms::$interop->nbill_admin_base_path . "/html2pdf/nbill_to_pdf.php");
    }

    public static function get_path_to_pdf_writer(&$generator)
    {
        $generator = 'dompdf';
        $path_to_pdfwriter = "";
        if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/dompdf/dompdf.php")) {
            $path_to_pdfwriter = nbf_cms::$interop->nbill_fe_base_path . "/dompdf";
        } else if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/dompdf/dompdf.php")) {
            $path_to_pdfwriter = nbf_cms::$interop->nbill_admin_base_path . "/dompdf";
        } else if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/pdfwriter/dompdf.php")) {
            $path_to_pdfwriter = nbf_cms::$interop->nbill_fe_base_path . "/pdfwriter";
        } else if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/pdfwriter/dompdf.php")) {
            $path_to_pdfwriter = nbf_cms::$interop->nbill_admin_base_path . "/pdfwriter";
        } else if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/html2pdf/nbill_to_pdf.php")) {
            $path_to_pdfwriter = nbf_cms::$interop->nbill_fe_base_path . "/html2pdf";
            $generator = 'html2ps';
        } else if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/html2pdf/nbill_to_pdf.php")) {
            $path_to_pdfwriter = nbf_cms::$interop->nbill_admin_base_path . "/html2pdf";
            $generator = 'html2ps';
        } else if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/pdfwriter/nbill_to_pdf.php")) {
            $path_to_pdfwriter = nbf_cms::$interop->nbill_fe_base_path . "/pdfwriter";
            $generator = 'html2ps';
        } else if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/pdfwriter/nbill_to_pdf.php")) {
            $path_to_pdfwriter = nbf_cms::$interop->nbill_admin_base_path . "/pdfwriter";
            $generator = 'html2ps';
        } else {
            $path_to_pdfwriter = null;
            $generator = null;
        }
        return $path_to_pdfwriter;
    }

    /**
    * Remove http :// www and common mis-typed fluff from a domain name
    * (this is mainly for use by extensions that require domain processing)
    */
    public static function clean_domain($domain, $strip_trailing_slash = true)
    {
        $domain = str_replace("http:////", "", $domain);
        $domain = str_replace("http:///", "", $domain);
        $domain = str_replace("http://", "", $domain);
        $domain = str_replace("http:/", "", $domain);
        $domain = str_replace("http:\\\\\\", "", $domain);
        $domain = str_replace("http:\\\\", "", $domain);
        $domain = str_replace("http:\\", "", $domain);
        $domain = str_replace("http//", "", $domain);
        $domain = str_replace("http:/", "", $domain);
        $domain = str_replace("http\\\\", "", $domain);
        $domain = str_replace("http\\", "", $domain);
        $domain = str_replace("http:", "", $domain);
        $domain = str_replace("http;//", "", $domain);
        $domain = str_replace("http;/", "", $domain);
        $domain = str_replace("http;\\\\", "", $domain);
        $domain = str_replace("http;\\", "", $domain);
        if (substr($domain, 0, 5) == "wwww.")
        {
            $domain = substr($domain, 5);
        }
        if (substr($domain, 0, 4) == "www.")
        {
            $domain = substr($domain, 4);
        }
        if (substr($domain, 0, 3) == "ww.")
        {
            $domain = substr($domain, 3);
        }
        if ($strip_trailing_slash)
        {
            if (substr($domain, strlen($domain) - 1) == "/"
                || substr($domain, strlen($domain) - 1) == "\\")
            {
                $domain = substr($domain, strlen($domain) - 1);
            }
        }
        return self::nb_strtolower($domain);
    }

    public static function debug_trace($data)
    {
        if (nbf_globals::$trace_debug)
        {
            static $request_id = 0;

            $new_request = false;
            if (!$request_id)
            {
                $request_id = uniqid();
                $new_request = true;
            }

            $log_file = nbf_cms::$interop->nbill_admin_base_path . "/nbill_debug_trace.log";
            $handle = @fopen(@$log_file, 'a');

            if ($new_request)
            {
                @fwrite($handle, "\n\n");
                @fwrite($handle, "*************************\n");
                @fwrite($handle, "Request ID: $request_id\n");
                @fwrite($handle, "*************************\n");
            }
            $nowtime = nbf_common::nb_time();
            $nowmicro = 0;
            $microseconds = gettimeofday();
            if (isset($microseconds['usec']))
            {
                $nowtime = $microseconds['sec'];
                $nowmicro = $microseconds['usec'];
            }
            @fwrite($handle, nbf_common::nb_date("Y-m-d h:i:s", $nowtime) . "." . $nowmicro . " " . $data);
            @fclose($handle);
        }
    }

    public static function hook_extension($action, $task, $local_vars, $output = "", $admin = true)
    {
        if (strpos($action, "..") !== false || strpos($task, "..") !== false) {
            return $output;
        }

        $ext_files = array();
        $expected_extension_folder = nbf_cms::$interop->nbill_admin_base_path . "/extensions/" . ($admin ? "administrator" : "frontend") . "/$action/$task";
        if (file_exists($expected_extension_folder)) {
            $ext_files = array_diff(scandir($expected_extension_folder), array(".", ".."));
        }
        if (count($ext_files) > 0) {
            foreach ($ext_files as $ext_file) {
                $custom_output = '';
                include($expected_extension_folder . "/" . $ext_file);
                $inject_after = '<!-- Custom Fields Placeholder -->';
                $inject_point = strpos($output, $inject_after);
                if ($inject_point !== false)
                {
                    $inject_point += strlen($inject_after);
                    $output = substr($output, 0, $inject_point) . $custom_output . substr($output, $inject_point + 1);
                }
            }
        }
        return $output;
    }

    /**
    * @return nBillNumberCurrency
    */
    public static function convertValueToCurrencyObject($value, $currency_code, $is_grand_total = true, $is_line_total = false)
    {
        static $currency; //Don't want to keep looking up the currency from the database on every call unless necessary

        $config = nBillConfigurationService::getInstance()->getConfig();
        $number_factory = new nBillNumberFactory($config);

        if (!$currency || $currency->code != $currency_code) {
            $currency_factory = new nBillCurrencyFactory();
            $currency_mapper = $currency_factory->createCurrencyMapper(nbf_cms::$interop->database);
            $currency_service = new nBillCurrencyService($currency_mapper);
            $currency = $currency_service->findCurrency($currency_code);
        }
        $currency_object = $number_factory->createNumberCurrency($value, $currency);
        if ($is_line_total) {
            $currency_object->setIsLineTotal(true);
        } else if ($is_grand_total) {
            $currency_object->setIsGrandTotal(true);
        }
        return $currency_object;
    }

    public static function convertValueToNumberObject($value, $type = 'decimal')
    {
        $config = nBillConfigurationService::getInstance()->getConfig();
        $number_factory = new nBillNumberFactory($config);
        return $number_factory->createNumber($value, $type);
    }

    /**************************************************************************************/
    /* Wrappers for PHP functions which might not offer the full functionality we need... */
    /**************************************************************************************/

    public static function nb_strlen($string)
    {
        if (is_array($string)) //Multiline select box
        {
            return count($string);
        }
        return function_exists('mb_strlen') ? mb_strlen($string, (isset(nbf_cms::$interop->char_encoding) ? nbf_cms::$interop->char_encoding : 'utf-8')) : strlen($string);
    }

    public static function nb_strpos($haystack, $needle, $offset = 0)
    {
        return function_exists('mb_strpos') ? @mb_strpos($haystack, $needle, $offset, (isset(nbf_cms::$interop->char_encoding) ? nbf_cms::$interop->char_encoding : 'utf-8')) : strpos($haystack, $needle, $offset); //Errors supressed on mb function as it throws an error if the haystack is empty
    }

    public static function nb_strrpos($haystack, $needle, $offset = 0)
    {
        //Before PHP 5.2, offset was not supported on mb_strrpos, and passing encoding in 3rd param is deprecated, so have to omit both
        if (function_exists('mb_strrpos') && $offset == 0)
        {
            return @mb_strrpos($haystack, $needle); //Errors supressed as it throws an error if the haystack is empty
        }
        else
        {
            return strrpos($haystack, $needle, $offset);
        }
    }

    public static function nb_strtolower($string)
    {
        return function_exists('mb_strtolower') ? mb_strtolower($string, (isset(nbf_cms::$interop->char_encoding) ? nbf_cms::$interop->char_encoding : 'utf-8')) : strtolower($string);
    }

    public static function nb_strtoupper($string)
    {
        return function_exists('mb_strtoupper') ? mb_strtoupper($string, (isset(nbf_cms::$interop->char_encoding) ? nbf_cms::$interop->char_encoding : 'utf-8')) : strtoupper($string);
    }

    public static function nb_ucwords($string)
    {
        return function_exists('mb_convert_case') ? mb_convert_case($string, MB_CASE_TITLE, (isset(nbf_cms::$interop->char_encoding) ? nbf_cms::$interop->char_encoding : 'utf-8')) : ucwords($string);
    }

    public static function nb_substr($string, $start, $length = null)
    {
        if ($length === null)
        {
            return function_exists('mb_substr') ? mb_substr($string, $start, mb_strlen($string), (isset(nbf_cms::$interop->char_encoding) ? nbf_cms::$interop->char_encoding : 'utf-8')) : substr($string, $start);
        }
        else
        {
            return function_exists('mb_substr') ? mb_substr($string, $start, $length, (isset(nbf_cms::$interop->char_encoding) ? nbf_cms::$interop->char_encoding : 'utf-8')) : substr($string, $start, $length);
        }

    }

    public static function nb_strtotime($time, $now = null)
    {
        return $now === null ? strtotime($time) : strtotime($time, $now);
    }

    public static function nb_mktime($hour = null, $minute = null, $second = null, $month = null, $day = null, $year = null, $is_dst = null)
    {
        return $is_dst === null ? mktime($hour, $minute, $second, $month, $day, $year) : mktime($hour, $minute, $second, $month, $day, $year, $is_dst);
    }

    public static function nb_date($format, $timestamp = null)
    {
        return $timestamp === null ? date($format) : date($format, $timestamp);
    }

    public static function nb_time()
    {
        return time();
    }

    public static function nb_getdate($timestamp = null)
    {
        return $timestamp === null ? getdate() : getdate($timestamp);
    }

    public static function nb_filename_safe($filename)
    {
        if (function_exists('mb_convert_encoding'))
        {
            $filename = mb_convert_encoding($filename, 'iso-8859-1', nbf_cms::$interop->char_encoding);
        }
        $filename = self::nb_strtolower(str_replace(" ", "-", $filename));
        $filename = preg_replace("/[^\_\-\.a-zA-Z0-9]/", "", $filename);
        return $filename;
    }
}