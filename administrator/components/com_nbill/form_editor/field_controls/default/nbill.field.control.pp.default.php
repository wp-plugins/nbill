<?php
/**
* nBill Summary Control Class file - for handling output and processing of the order summary on a form.
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

include_once(realpath(dirname(__FILE__)) . "/../custom/nbill.field.control.base.php");

/**
* Summary
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_pp_default extends nbf_field_control
{
    /** @var boolean Whether or not to build a plain text version of the summary table as well as HTML */
    public $render_plain_text = false;
    /** @var string Holds the plain text version of the summary */
    public $plain_text_summary = "";
    /** @var int The ID of the client record, if applicable */
    public static $current_entity_id = 0;
    /** @var boolean Whether or not to show all fields regardless of whether it is an existing client or not (typically for email confirmations) */
    public $include_all_fields = false;
    /** @var boolean (Deprecated) Whether or not this field is being shown on a quote request form (headings alter accordingly) */
    public $is_quote_request_form = false;
    /** @var string Type of form (replaces deprecated 'is_quote_request_form' property) */
    public $form_type = 'OR';

    protected static $line_height = 25;
    protected static $_form_def = array();
    protected static $_shipping_fields = array();
    protected static $_shipping_field_options = array();
    protected static $_shipping_height_allowance = 0;
    protected static $_order_total_summary = "";
    protected static $_order_total_summary_plain = "";
    protected static $_order_total_height_allowance = 0;
    protected static $_this_page_no = 0;

    //Static variables with getters and setters are for backward compatability with older versions of nCart
    public function &__get($property)
    {
        if (property_exists('nbf_field_control_pp_default', 'current_entity_id')) {
            return self::$$property;
        } else {
            throw new Exception('Property ' . $property . ' does not exist');
        }
    }

    public function __set($property, $value)
    {
        if (property_exists('nbf_field_control_pp_default', $property)) {
            nbf_field_control_pp_default::$$property = $value;
        } else {
            $stop = true;
        }
    }

    /**
    * Initialise the summary
    */
    public function __construct($form_id, $id, $admin = false)
    {
        nbf_common::load_language("xref");
        parent::__construct($form_id, $id);
        $this->html_control_type = 'LL';
        $this->show_label_by_default = false;
        $this->current_entity_id = nbf_common::get_param($_REQUEST, 'nbill_entity_id'); //Can be overwritten after instantiation, if required

        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");

        //Load all the other fields from previous pages on the same form
        if (intval($form_id))
        {
            $nb_database = nbf_cms::$interop->database;
            $sql = "SELECT form_type FROM #__nbill_order_form WHERE id = " . intval($form_id);
            $nb_database->setQuery($sql);
            $this->form_type = $nb_database->loadResult();
            if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/form_defs/" . strtoupper($this->form_type) . ".php"))
            {
                include(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/form_defs/" . strtoupper($this->form_type) . ".php");
                $this->_form_def = $form_def;
                if (!isset($this->_form_def['show_prices_on_summary']))
                {
                    $this->_form_def['show_prices_on_summary'] = true;
                }
            }
            else
            {
                $this->_form_def = array();
                $this->_form_def['show_prices_on_summary'] = true;
            }
            $this->is_quote_request_form = $this->form_type == 'QU' ? 1 : 0;
            $sql = "SELECT page_no FROM #__nbill_order_form_fields WHERE id = " . intval($id);
            $nb_database->setQuery($sql);
            $this->_this_page_no = intval($nb_database->loadResult());
            if (!$this->_this_page_no)
            {
                //Default to showing all (this will happen on order confirmation emails as there is no page number)
                $sql = "SELECT max(page_no) FROM #__nbill_order_form_pages WHERE form_id = " . intval($form_id);
                $nb_database->setQuery($sql);
                $this->_this_page_no = intval($nb_database->loadResult()) + 1;
            }
            $sql = "SELECT #__nbill_order_form_fields.*, #__nbill_order_form_fields.id AS option_field_id FROM #__nbill_order_form_fields WHERE form_id = " . intval($form_id) . " AND page_no <= " . $this->_this_page_no . " ORDER BY page_no, ordering";
            $nb_database->setQuery($sql);
            $this->value = $nb_database->loadObjectList();
            $this->height_allowance = 25;
            if (is_array($this->value))
            {
                foreach ($this->value as &$field)
                {
                    if ($field->published == 1 || ($field->published != 0 && $this->include_all_fields == true) || ($field->published == 2 && ($this->current_entity_id || nbf_common::get_param($_REQUEST, 'nbill_entity_id'))) || ($field->published == 3 && ($admin || !($this->current_entity_id || nbf_common::get_param($_REQUEST, 'nbill_entity_id')))))
                    {
                        if ($field->page_no < $this->_this_page_no && ($field->show_on_summary == 1 || ($field->show_on_summary == 2 && ((!$admin && $field->required) || ($admin && nbf_common::nb_strlen($field->default_value) > 0) || (!$admin && nbf_common::nb_strlen(nbf_common::get_param($_REQUEST, 'ctl_' . $field->name)) > 0)))))
                        {
                            $this->height_allowance += $this->line_height;
                        }
                    }
                }
            }

            
        }
        else
        {
            $this->default_value = "";
            $this->_form_def = array();
            $this->_form_def['show_prices_on_summary'] = true;
        }
    }

    /**
    * Renders the control
    */
    protected function _render_control($admin = false)
    {
        $nb_database = nbf_cms::$interop->database;
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.field.class.php");
        nbf_common::load_language("form.editor");
        nbf_common::load_language("core.profile_fields");
        $client_lang = nbf_cms::$interop->language;
        if (!$admin && $this->current_entity_id)
        {
            $sql = "SELECT default_language FROM #__nbill_entity WHERE id = " . intval($this->current_entity_id);
            $nb_database->setQuery($sql);
            $client_lang = $nb_database->loadResult();
        }
        ?>
        <table id="nbill_summary_table_<?php echo $this->id; ?>" class="nbill_summary_table" border="0" cellpadding="3" cellspacing="0" <?php echo $this->attributes; ?>>
            <tr id="nbill_summary_row_intro_title_<?php echo $this->id; ?>" class="nbill_summary_row summary_title">
                <th colspan="2" class="nbill_summary_sub_heading" valign="middle">
                    <?php
                    if (@$this->_form_def['summary_title'])
                    {
                        echo nbf_common::parse_translation($client_lang, @$this->_form_def['action'], $this->_form_def['summary_title']);
                    }
                    else
                    {
                        echo $this->form_type == "QU" ? nbf_common::parse_translation($client_lang, "nbill", 'NBILL_SUMMARY_QUOTE_REQUEST_DETAILS') : nbf_common::parse_translation($client_lang, "nbill", 'NBILL_SUMMARY_ORDER_DETAILS');
                    }
                    $this->height_allowance += $this->line_height;
                    ?>
                </th>
            </tr>
            <?php
            if ($this->_this_page_no && is_array($this->value))
            {
                $field_options = array();
                $sql_field_options = array();
                nbf_form_fields::load_field_options($this->value, "#__nbill_order_form_fields_options", $this->form_id, $field_options, $sql_field_options);
                foreach ($this->value as &$field)
                {
                    if ($field->published == 1 || ($field->published != 0 && $this->include_all_fields == true) || ($field->published == 2 && $this->current_entity_id) || ($field->published == 3 && ($admin || !$this->current_entity_id)))
                    {
                        $full_option_list = array_merge($field_options[$field->id], array_filter($sql_field_options[$field->id])) + $field_options[$field->id] + $sql_field_options[$field->id];
                        if (substr($field->field_type, 0, 1) != 'P' && ($field->show_on_summary == 1 || ($field->show_on_summary == 2 && (($admin && nbf_common::nb_strlen($field->default_value) > 0) || (!$admin && nbf_common::nb_strlen(nbf_common::get_param($_REQUEST, 'ctl_' . $field->name))) > 0))))
                        {
                            $control = nbf_form_fields::create_control($field, $full_option_list, "", $admin);
                            ?>
                            <tr id="nbill_summary_row_<?php echo $this->id; ?>_<?php echo $field->id; ?>" class="nbill_summary_row summary_value">
                                <td class="nbill_summary_label">
                                    <div id="nbill_summary_label_<?php echo $this->id; ?>_<?php echo $field->id; ?>">
                                        <?php $label = nbf_common::nb_strlen($field->label) == 0 && nbf_common::nb_strlen($field->checkbox_text) > 0 ? $field->checkbox_text : $field->label;
                                        $label = $label ? ((defined(str_replace("* ", "", $label)) ? constant(str_replace("* ", "", $label)) : str_replace("* ", "", $label))) : "&nbsp;";
                                        echo $label;
                                        if ($this->render_plain_text)
                                        {
                                            $this->plain_text_summary .= strip_tags($label) . ": ";
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="nbill_summary_value"<?php if ($field->field_type == 'GG' || $field->field_type == 'XG') {echo " style=\"text-align:right;\"";} ?>>
                                    <div id="nbill_summary_value_<?php echo $this->id; ?>_<?php echo $field->id; ?>">
                                        <?php
                                        ob_start();
                                        $control->render_summary();
                                        $control_summary = ob_get_clean();
                                        echo $control_summary;
                                        if ($this->render_plain_text)
                                        {
                                            $this->plain_text_summary .= $control_summary . "\n";
                                        }
                                        ?>
                                    </div>
                                </td>
                            </tr><?php
                        }
                    }
                }
            }
            else if ($admin)
            {
                ?>
                <tr class="nbill_summary_row summary_empty"><td class="nbill_summary_empty"><?php echo NBILL_SUMMARY_NOTHING_TO_SHOW_ADMIN; ?></td></tr>
                <?php
            }

            if ($this->_form_def['show_prices_on_summary'] && !$admin && $this->form_type != "QU")
            {
                $show_shipping = false;
                foreach ($this->_shipping_fields as $shipping_field)
                {
                    if (count($this->_shipping_field_options[$shipping_field->id]) > 1)
                    {
                        $show_shipping = true;
                        break;
                    }
                }

                if ($show_shipping)
                {
                    ?>
                    <tr id="nbill_summary_row_SHIPPING_title_<?php echo $this->id; ?>" class="nbill_summary_row summary_shipping_title">
                        <th colspan="2" class="nbill_summary_sub_heading" valign="middle">
                            <?php echo nbf_common::parse_translation($client_lang, "nbill", 'NBILL_SHIPPING_SERVICE'); ?>
                        </th>
                    </tr>
                    <?php
                    foreach ($this->_shipping_fields as &$shipping_field)
                    {
                        ?>
                        <tr id="nbill_summary_row_SHIPPING_<?php echo $this->id; ?>_<?php echo $shipping_field->id; ?>" class="nbill_summary_row summary_shipping">
                            <td class="nbill_summary_label">
                                <div id="nbill_summary_label_SHIPPING_<?php echo $this->id; ?>_<?php echo $shipping_field->id; ?>">
                                    <?php $shipping_label = $shipping_field->label ? ((defined(str_replace("* ", "", $shipping_field->label)) ? constant(str_replace("* ", "", $shipping_field->label)) : str_replace("* ", "", $shipping_field->label))) : "&nbsp;";
                                    echo $shipping_label;
                                    if ($this->render_plain_text)
                                    {
                                        $this->plain_text_summary .= $shipping_label . ": ";
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="nbill_summary_value">
                                <div id="nbill_summary_value_SHIPPING_<?php echo $this->id; ?>_<?php echo $shipping_field->id; ?>">
                                    <?php
                                    $control = nbf_form_fields::create_control($shipping_field, $this->_shipping_field_options[$shipping_field->id]);
                                    ob_start();
                                    if ($this->id == "email_summary")
                                    {
                                        $control->render_summary();
                                    }
                                    else
                                    {
                                        $control->render_control();
                                    }
                                    $shipping_summary = ob_get_clean();
                                    echo $shipping_summary;
                                    if ($this->render_plain_text)
                                    {
                                        $this->plain_text_summary .= $shipping_summary . "\n";
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                }
                if (nbf_common::nb_strlen($this->_order_total_summary) > 0)
                {
                    echo $this->_order_total_summary;
                    if ($this->render_plain_text)
                    {
                        $this->plain_text_summary .= "\n" . $this->_order_total_summary_plain;
                    }
                }
            }
            ?>
        </table>
        <?php
    }

    public function set_statics($statics)
    {
        //For backward compatability
        if ($statics && is_array($statics)) {
            foreach ($statics as $key=>$value) {
                if (property_exists($this, $key)) {
                    self::$$key = $value;
                }
            }
        }
    }

    public static function build_order_total_summary($this_field_id, $orders, $currency, $payment_frequency,
                    &$standard_totals, &$regular_totals, &$actual_totals, &$order_summary_total, &$order_summary_total_plain,
                    $payment_plan_type = 'AA', $payment_plan_name = NBILL_UP_FRONT, $installment_frequency = 'AA', $no_of_installments = 1,
                    $show_recalc_button = false) {
        //For backward compatability with older versions of nCart
        $nb_database = nbf_cms::$interop->database;
        $sql = "SELECT form_id FROM #__nbill_order_form_fields WHERE id = " . intval($this_field_id);
        $nb_database->setQuery($sql);
        $form_id = $nb_database->loadResult();

        $class = get_called_class();
        $instance = new $class($form_id, $this_field_id, defined('NBILL_ADMIN'));
        $reflection = new ReflectionClass($instance);
        $statics = $reflection->getStaticProperties();
        $instance->set_statics($statics);

        return $instance->order_total_summary_table($this_field_id, $orders, $currency, $payment_frequency,
                    $standard_totals, $regular_totals, $actual_totals, $order_summary_total, $order_summary_total_plain,
                    $payment_plan_type, $payment_plan_name, $installment_frequency, $no_of_installments,
                    $show_recalc_button);
    }

    public function order_total_summary_table($this_field_id, $orders, $currency, $payment_frequency,
                    &$standard_totals, &$regular_totals, &$actual_totals, &$order_summary_total, &$order_summary_total_plain,
                    $payment_plan_type = 'AA', $payment_plan_name = NBILL_UP_FRONT, $installment_frequency = 'AA', $no_of_installments = 1,
                    $show_recalc_button = false)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.xref.class.php");
        $order_total_height_allowance = $this->line_height;
        $separate_regular = $regular_totals->total_gross > 0 && $regular_totals->total_gross != $actual_totals->total_gross;
        $using_plan = $payment_frequency == 'AA' && ($actual_totals->total_gross != $standard_totals->total_gross || $payment_plan_type == 'FF');
        $pay_freq_desc = nbf_common::nb_strtolower(nbf_xref::lookup_xref_code("pay_frequency", $using_plan ? $installment_frequency : $payment_frequency));
        $email_summary = $this_field_id == 'email_summary';
        $nb_database = nbf_cms::$interop->database;

        $client_lang = nbf_cms::$interop->language;
        if ($this->current_entity_id)
        {
            $sql = "SELECT default_language FROM #__nbill_entity WHERE id = " . intval($this->current_entity_id);
            $nb_database->setQuery($sql);
            $client_lang = $nb_database->loadResult();
        }

        ob_start();
        ?>
        <tr class="nbill_summary_row summary_title summary_totals_title">
            <th colspan="2" class="nbill_summary_sub_heading" valign="middle">
                <?php if ($show_recalc_button && !$email_summary)
                {
                    ?><noscript><div style="float:right;margin-right:5px;"><input type="submit" name="nbill_summary_recalculate" id="nbill_summary_recalculate_shipping" value="<?php echo NBILL_RECALCULATE; ?>" /></div></noscript><?php
                }
                switch ($this_field_id)
                {
                    case "invoice":
                        echo nbf_common::parse_translation($client_lang, "frontend", 'NBILL_INVOICE_SUMMARY_TOTALS_TITLE');
                        $order_summary_total_plain .= nbf_common::nb_strtoupper(nbf_common::parse_translation($client_lang, "frontend", 'NBILL_INVOICE_SUMMARY_TOTALS_TITLE')) . "\n\n";
                        break;
                    case "quote":
                        echo nbf_common::parse_translation($client_lang, "frontend", 'NBILL_QUOTE_SUMMARY_TOTALS_TITLE');
                        $order_summary_total_plain .= nbf_common::nb_strtoupper(nbf_common::parse_translation($client_lang, "frontend", 'NBILL_QUOTE_SUMMARY_TOTALS_TITLE')) . "\n\n";
                        break;
                    default:
                        echo nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_TOTALS_TITLE');
                        $order_summary_total_plain .= nbf_common::nb_strtoupper(nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_TOTALS_TITLE')) . "\n\n";
                        break;
                }
                ?>
            </th>
        </tr>
        <?php
        $shipping_discounts_present = false;
        $summary_order_count = 0; //No. of orders that are being summarized - ie. anything that is not a shipping discount (so we know when we are on the last one)
        foreach ($orders as $summary_order)
        {
            if (!isset($summary_order["is_shipping_discount"]) || !$summary_order["is_shipping_discount"])
            {
                $summary_order_count++;
            }
        }

        if (count($orders) > 0)
        {
            $orders_summarized = 0;
            for ($i = 0; $i < count($orders); $i++)
            {
                $order = $orders[$i];
                if (isset($order["is_shipping_discount"]) && $order["is_shipping_discount"])
                {
                    //Add these after the shipping total
                    $shipping_discounts_present = true;
                    continue;
                }
                $orders_summarized++;
                $order_total_height_allowance += $this->line_height;

                $this->output_order_row($order, $order_summary_total_plain, $currency, $orders_summarized, $summary_order_count);

                if (isset($order['setup_fee']) && $order['setup_fee'] != 0)
                {
                    $order_total_height_allowance += $this->line_height;
                    ?>
                    <tr class="nbill_summary_row summary_setup_fee">
                        <td class="nbill_summary_label<?php if ($orders_summarized == $summary_order_count) { ?> nbill_summary_last_row<?php } ?>" style="text-align:left;">
                            <?php
                            $this_summary_label = $order['setup_fee'] > 0 ? nbf_common::parse_translation($client_lang, "nbill", 'NBILL_PRODUCT_SETUP_FEE') : nbf_common::parse_translation($client_lang, "nbill", 'NBILL_PRODUCT_NEGATIVE_SETUP_FEE');
                            $this_summary_label .= " (" . $order['product_name'] . ")";
                            echo $this_summary_label;
                            $order_summary_total_plain .= $this_summary_label . ": ";
                            ?>
                        </td>
                        <td class="nbill_summary_amount_value<?php if ($orders_summarized == $summary_order_count) { ?> nbill_summary_last_row<?php } ?>">
                            <?php
                            $this_summary_value = nbf_common::convertValueToCurrencyObject($order['setup_fee'], $currency)->format();
                            echo $this_summary_value;
                            $order_summary_total_plain .= strip_tags($this_summary_value) . "\n";
                            ?>
                        </td>
                    </tr>
                    <?php
                }
            }
        }

        if ($actual_totals->total_net != $actual_totals->total_gross)
        {
            $order_total_height_allowance += $this->line_height;
            ?>
            <tr class="nbill_summary_row summary_total summary_total_net">
                <td class="nbill_summary_label" style="text-align:left;">
                    <?php echo nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_NET_TOTAL');
                    $order_summary_total_plain .= nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_NET_TOTAL') . ": "; ?>
                </td>
                <td class="nbill_summary_amount_value">
                    <?php $this_summary_value = nbf_common::convertValueToCurrencyObject($actual_totals->total_net, $currency)->format();
                    echo $this_summary_value;
                    $order_summary_total_plain .= strip_tags($this_summary_value) . "\n";
                    ?>
                </td>
            </tr>
            <?php if ($actual_totals->total_tax != 0)
            {
                $order_total_height_allowance += $this->line_height;
                ?>
                <tr class="nbill_summary_row summary_total summary_total_tax">
                    <td class="nbill_summary_label" style="text-align:left;">
                        <?php
                        echo isset($order['tax_abbreviation']) && strlen($order['tax_abbreviation']) > 0 ? $order['tax_abbreviation'] : nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_TAX_TOTAL');
                        $order_summary_total_plain .= nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_TAX_TOTAL') . ": "; ?>
                    </td>
                    <td class="nbill_summary_amount_value">
                        <?php $this_summary_value = nbf_common::convertValueToCurrencyObject($actual_totals->total_tax, $currency)->format();
                        echo $this_summary_value;
                        $order_summary_total_plain .= strip_tags($this_summary_value) . "\n";
                        ?>
                    </td>
                </tr>
            <?php }

            if ($shipping_discounts_present)
            {
                $shipping_discount_total = 0;
                foreach ($orders as $order)
                {
                    if (isset($order["is_shipping_discount"]) && $order["is_shipping_discount"])
                    {
                        $shipping_discount_total = float_add($shipping_discount_total, $order['net_price']);
                    }
                }
                $order_total_height_allowance += $this->line_height;
                ?>
                <tr class="nbill_summary_row summary_total summary_total_shipping_item">
                    <td class="nbill_summary_label" style="text-align:left;">
                        <?php echo nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_SHIPPING_FEES');
                        $order_summary_total_plain .= nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_SHIPPING_FEES') . ": "; ?>
                    </td>
                    <td class="nbill_summary_amount_value">
                        <?php $this_summary_value = nbf_common::convertValueToCurrencyObject($actual_totals->total_shipping - $shipping_discount_total, $currency)->format();
                        echo $this_summary_value;
                        $order_summary_total_plain .= strip_tags($this_summary_value) . "\n";
                        ?>
                    </td>
                </tr>
                <?php
                foreach ($orders as $order)
                {
                    if (isset($order["is_shipping_discount"]) && $order["is_shipping_discount"])
                    {
                        $order_total_height_allowance += $this->line_height;
                        ?>
                        <tr class="nbill_summary_row summary_total summary_total_gateway_fee">
                            <td class="nbill_summary_label<?php if (@$order['gateway_voucher']) {echo " nbill_gateway_fee";} ?>" style="text-align:left;">
                                <?php echo $order['product_name'];
                                $order_summary_total_plain .= $order['product_name'] . ": "; ?>
                            </td>
                            <td class="nbill_summary_amount_value<?php if (@$order['gateway_voucher']) {echo " nbill_gateway_fee";} ?>">
                                <?php $this_summary_value = nbf_common::convertValueToCurrencyObject($order['net_price'], $currency)->format();
                                echo $this_summary_value;
                                $order_summary_total_plain .= strip_tags($this_summary_value) . "\n";
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
            }

            if ($actual_totals->total_shipping > 0 || $shipping_discounts_present)
            {
                $order_total_height_allowance += $this->line_height;
                ?>
                <tr class="nbill_summary_row summary_total summary_total_shipping">
                    <td class="nbill_summary_label" style="text-align:left;">
                        <?php echo nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_SHIPPING_TOTAL');
                        $order_summary_total_plain .= nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_SHIPPING_TOTAL') . ": ";
                        ?>
                    </td>
                    <td class="nbill_summary_amount_value">
                        <?php $this_summary_value = nbf_common::convertValueToCurrencyObject($actual_totals->total_shipping, $currency)->format();
                        echo $this_summary_value;
                        $order_summary_total_plain .= strip_tags($this_summary_value) . "\n";
                        ?>
                    </td>
                </tr>
            <?php }

            if ($actual_totals->total_shipping_tax != 0)
            {
                $order_total_height_allowance += $this->line_height;
                ?>
                <tr class="nbill_summary_row summary_total summary_total_shipping_tax">
                    <td class="nbill_summary_label" style="text-align:left;">
                        <?php echo nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_SHIPPING_TAX');
                        $order_summary_total_plain .= nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_SHIPPING_TAX') . ": ";
                        ?>
                    </td>
                    <td class="nbill_summary_amount_value">
                        <?php $this_summary_value = nbf_common::convertValueToCurrencyObject($actual_totals->total_shipping_tax, $currency)->format();
                        echo $this_summary_value;
                        $order_summary_total_plain .= strip_tags($this_summary_value) . "\n";
                        ?>
                    </td>
                </tr>
            <?php
            }
        }
        $order_total_height_allowance += $this->line_height;
        ?>
        <tr class="nbill_summary_row summary_total summary_total_gross">
            <th class="nbill_summary_sub_heading" style="text-align:left;" id="nbill_summary_total_to_pay_title">
                <?php
                if ($separate_regular && !$using_plan)
                {
                    $this_summary_label = nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_AMOUNT_TO_PAY_TODAY');
                }
                else if ($regular_totals->total_gross > 0 && !$using_plan && $payment_frequency && $payment_frequency != 'AA' && $payment_frequency != 'XX')
                {
                    if ($no_of_installments > 1)
                    {
                        $this_summary_label = sprintf(nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_AMOUNT_TO_PAY_REGULAR_INSTALLMENTS'), $no_of_installments, $pay_freq_desc);
                    }
                    else
                    {
                        $this_summary_label = sprintf(nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_AMOUNT_TO_PAY_REGULAR'), $pay_freq_desc);
                    }
                }
                else
                {
                    $this_summary_label = nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_AMOUNT_TO_PAY');
                }
                echo $this_summary_label;
                $order_summary_total_plain .= $this_summary_label . ": ";
                ?>
            </th>
            <th class="nbill_summary_sub_heading nbill_summary_amount_value" id="nbill_summary_total_to_pay_value">
                <?php $this_summary_value = nbf_common::convertValueToCurrencyObject($actual_totals->total_gross, $currency)->format();
                echo $this_summary_value;
                $order_summary_total_plain .= strip_tags($this_summary_value) . "\n";
                ?>
            </th>
        </tr>
        <?php

        if ($using_plan)
        {
            //We are using a payment plan which does not require full payment up-front
            $order_total_height_allowance += $this->line_height;
            if ($regular_totals->total_gross > 0)
            {
                $separate_regular = true;
            }
            ?>
            <tr class="nbill_summary_row summary_total summary_total_payment_plan_title">
                <td colspan="2" class="nbill_summary_label" style="text-align:center;;font-weight:bold;font-style:italic;">
                    <?php $this_summary_label = sprintf(nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_PAYMENT_PLAN'), $payment_plan_name);
                    echo $this_summary_label;
                    $order_summary_total_plain .= "\n" . $this_summary_label . "\n\n";
                    ?>
                </td>
            </tr>
            <?php
            $order_total_height_allowance += $this->line_height;
            ?>
            <tr class="nbill_summary_row summary_total summary_total_payment_plan">
                <th class="nbill_summary_sub_heading" style="text-align:left;vertical-align:middle" id="nbill_summary_payment_plan_title">
                    <?php echo nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_AMOUNT_TO_PAY_TODAY');
                    $order_summary_total_plain .= nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_AMOUNT_TO_PAY_TODAY') . ": ";
                    ?>
                </th>
                <th class="nbill_summary_sub_heading nbill_summary_amount_value" style="vertical-align:middle" id="nbill_summary_payment_plan_value">
                    <?php $this_summary_value = '';
                    if ($payment_plan_type == 'FF')
                    {
                        if ($email_summary)
                        {
                            $this_summary_value .= isset($_POST['nbill_payment_plan_ff_total_gross']) ? nbf_common::convertValueToCurrencyObject(nbf_common::get_param($_POST, 'nbill_payment_plan_ff_total_gross'), $currency) : nbf_common::convertValueToCurrencyObject($standard_totals->total_gross, $currency)->format();
                        }
                        else
                        {
                            if ($standard_totals->total_gross > 0) //No point offering if there is nothing to pay and if we are not yet on the right page it will mess up the display when we do get there
                            {
                                $order_total_height_allowance += 6; //Need to allow extra height for the input box
                                ob_start();
                                $amount_to_pay = nbf_common::get_param($_POST, 'nbill_payment_plan_ff_amount_changed') && isset($_POST['nbill_payment_plan_ff_total_gross']) ? nbf_common::convertValueToCurrencyObject(nbf_common::get_param($_POST, 'nbill_payment_plan_ff_total_gross'), $currency)->getEditableDecimal()->format() : nbf_common::convertValueToCurrencyObject($standard_totals->total_gross, $currency)->getEditableDecimal()->format();
                                $admin_amount = nbf_common::convertValueToCurrencyObject(nbf_common::get_param($_REQUEST, 'admin_amount'), $currency)->getEditableDecimal()->format();
                                if ($admin_amount && $admin_amount > 0 && $admin_amount <= $amount_to_pay)
                                {
                                    $amount_to_pay = $admin_amount;
                                    $_POST['nbill_payment_plan_ff_amount_changed'] = 1;
                                }
                                ?>
                                <input type="text" name="nbill_payment_plan_ff_total_gross" id="nbill_payment_plan_ff_total_gross" value="<?php echo $amount_to_pay ?>" style="width:100px;text-align:right;" onchange="document.getElementById('nbill_payment_plan_ff_amount_changed').value='1';" />
                                <input type="hidden" name="nbill_payment_plan_ff_amount_changed" id="nbill_payment_plan_ff_amount_changed" value="<?php echo nbf_common::get_param($_POST, 'nbill_payment_plan_ff_amount_changed') ? '1' : '0'; ?>" />
                                <?php
                                $this_summary_value .= ob_get_clean();
                            }
                            else
                            {
                                $this_summary_value .= "0.00";
                            }
                        }
                    }
                    else
                    {
                        $this_summary_value .= nbf_common::convertValueToCurrencyObject($standard_totals->total_gross, $currency)->format();
                    }
                    echo $this_summary_value;
                    $order_summary_total_plain .= strip_tags($this_summary_value) . "\n";
                    ?>
                </th>
            </tr>
            <?php
        }
        if ($separate_regular)
        {
            $order_total_height_allowance += $this->line_height;
            ?>
            <tr class="nbill_summary_row summary_total summary_total_thereafter">
                <th class="nbill_summary_sub_heading" style="text-align:left;" id="nbill_summary_total_thereafter_title">
                    <?php $this_summary_label = sprintf(nbf_common::parse_translation($client_lang, "frontend", 'NBILL_ORDER_SUMMARY_AMOUNT_TO_PAY_REGULAR_THEREAFTER'), ($using_plan || $no_of_installments > 1 ? ($no_of_installments - 1) . " " : ""), $pay_freq_desc);
                    echo $this_summary_label;
                    $order_summary_total_plain .= $this_summary_label . ": ";
                    ?>
                </th>
                <th class="nbill_summary_sub_heading nbill_summary_amount_value" id="nbill_summary_total_thereafter_value">
                    <?php $this_summary_value = nbf_common::convertValueToCurrencyObject($regular_totals->total_gross, $currency)->format();
                    echo $this_summary_value;
                    $order_summary_total_plain .= strip_tags($this_summary_value) . "\n";
                    ?>
                </th>
            </tr>
            <?php
        }

        $order_summary_total = ob_get_clean();
        return $order_total_height_allowance;
    }

    protected function output_order_row($order, &$order_summary_total_plain, $currency, $orders_summarized, $summary_order_count)
    {
        ?>
        <tr class="nbill_summary_row summary_value">
            <td class="nbill_summary_label<?php if ($orders_summarized == $summary_order_count && (!isset($order['setup_fee']) || !$order['setup_fee'])) {echo " nbill_summary_last_row";} if (@$order['gateway_voucher']) {echo " nbill_gateway_fee";} ?>" style="text-align:left;">
                <?php
                $this_summary_label = $order['product_name'];
                if (strlen(@$order['relating_to']) > 0) {
                    $this_summary_label .= " (" . $order['relating_to'] . ")";
                }
                if ($order['quantity'] > 1) {
                    $this_summary_label .= " x " . (float_cmp(intval($order['quantity']), $order['quantity']) ? intval($order['quantity']) : nbf_common::convertValueToNumberObject($order['quantity'], 'quantity')->format());
                }
                echo $this_summary_label;
                $order_summary_total_plain .= $this_summary_label . ": ";
             ?>
            </td>
            <td class="nbill_summary_amount_value<?php if ($orders_summarized == $summary_order_count && (!isset($order['setup_fee']) || !$order['setup_fee'])) {echo " nbill_summary_last_row";} if (@$order['gateway_voucher']) {echo " nbill_gateway_fee";} ?>">
                <?php
                if ($order['quantity'] > 1) {
                    $this_summary_value = nbf_common::convertValueToCurrencyObject($order['net_price'] * $order['quantity'], $currency, false, true)->format();
                } else {
                    $this_summary_value = nbf_common::convertValueToCurrencyObject($order['net_price'], $currency)->format();
                }
                echo $this_summary_value;
                $order_summary_total_plain .= strip_tags($this_summary_value) . "\n";
                ?>
            </td>
        </tr>
        <?php
    }
}