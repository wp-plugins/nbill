<?php
class nBillLineItemMapper
{
    /** @var nbf_database **/
    protected $db;
    /** @var nBillLineItemFactory **/
    protected $line_item_factory;
    /** @var nBillINumberFactory **/
    protected $number_factory;
    /** @var nBillCurrencyService **/
    protected $currency_service;

    /**
    * @param nBillLineItemFactory $line_item_factory
    * @param nBillINumberFactory $number_factory
    * @param nbf_database $database Required to load items from database, not required for mapping from a JSON string
    * @param nBillCurrencyService $currency_service Required to load items from database, not required for mapping from a JSON string
    * @return nBillLineItemMapper
    */
    public function __construct(nBillLineItemFactory $line_item_factory, nBillINumberFactory $number_factory, nbf_database $database = null, nBillCurrencyService $currency_service = null)
    {
        $this->db = $database;
        $this->line_item_factory = $line_item_factory;
        $this->number_factory = $number_factory;
        $this->currency_service = $currency_service;
    }

    /**
    * @return array<nBillLineItem>
    * @param int $document_id
    */
    public function loadItemsForDocument($document_type, $document_id, $vendor_id)
    {
        $currency = $this->loadDocumentCurrency($document_id, $vendor_id);
        if (intval($document_id)) {
            $sql = "SELECT #__nbill_document_items.*, #__nbill_nominal_ledger.description AS nominal_ledger_description, #__nbill_document.document_type,
                            #__nbill_document.currency AS currency_code, #__nbill_product.shipping_units AS product_shipping_units
                    FROM #__nbill_document_items
                    LEFT JOIN #__nbill_document ON #__nbill_document_items.document_id = #__nbill_document.id
                    LEFT JOIN #__nbill_currency ON #__nbill_document.currency = #__nbill_currency.code
                    LEFT JOIN #__nbill_product ON #__nbill_document_items.product_code = #__nbill_product.product_code AND #__nbill_product.product_code > ''
                    LEFT JOIN #__nbill_nominal_ledger ON #__nbill_document_items.nominal_ledger_code = #__nbill_nominal_ledger.code
                            AND #__nbill_nominal_ledger.vendor_id = #__nbill_document.vendor_id
                    WHERE document_id = " . intval($document_id) . "
                    GROUP BY #__nbill_document_items.id
                    ORDER BY ordering, id";
            $this->db->setQuery($sql);
            $db_items = $this->db->loadObjectList();
        } else {
            $db_items = array();
        }
        $collection = $this->mapLineItems($db_items, $currency, $document_type);
        return $collection;
    }

    protected function loadDocumentCurrency($document_id, $vendor_id)
    {
        $currency_code = '';
        if ($document_id) {
            //First, load currency from main document (in case there are no items, we still need to know what currency to use for the totals)
            $sql = "SELECT #__nbill_document.currency FROM #__nbill_document WHERE id = " . intval($document_id);
            $this->db->setQuery($sql);
            $currency_code = $this->db->loadResult();
        }
        if (!$currency_code) {
            $sql = "SELECT vendor_currency FROM #__nbill_vendor WHERE id = " . intval($vendor_id) . " UNION SELECT vendor_currency FROM #__nbill_vendor WHERE default_vendor = 1 LIMIT 1";
            $this->db->setQuery($sql);
            $currency_code = $this->db->loadResult();
        }
        $currency = $this->currency_service->findCurrency($currency_code);
        return $currency;
    }

    protected function mapLineItems($db_items, nBillCurrency $currency, $document_type = 'IN')
    {
        $collection = $this->line_item_factory->createLineItemsCollection($currency, $document_type);

        $sections = array();
        $section = $this->line_item_factory->createLineItemsSection($collection, $document_type);

        if ($db_items && count($db_items) > 0) {
            foreach ($db_items as $db_item)
            {
                $line_item = $this->mapLineItem($db_item, $currency, $section);
                $section->line_items[] = $line_item;
                if (strlen($db_item->section_name) > 0) {
                    $section->section_name = $db_item->section_name;
                    $section->discount_title = $db_item->section_discount_title;
                    $section->discount_percent = $this->number_factory->createNumber($db_item->section_discount_percent);
                    $section->calculateTotals();
                    $sections[] = $section;
                    $section = $this->line_item_factory->createLineItemsSection($collection, $document_type);
                }
            }
        }
        if (count($sections) == 0 || count($section->line_items) > 0) {
            $section->calculateTotals();
            $sections[] = $section;
        }
        $collection->sections = $sections;
        return $collection;
    }

    /**
    * @return nBillLineItem
    */
    protected function mapLineItem($db_item, nBillCurrency $currency, nBillLineItemsSection $parent_section = null)
    {
        $line_item = $this->line_item_factory->createLineItem($db_item->document_type, $currency, $parent_section);
        $db_columns = get_object_vars($db_item);
        foreach ($db_columns as $db_column=>$db_value)
        {
            if (property_exists($line_item, $db_column)) {
                switch ($db_column)
                {
                    case 'net_price_for_item':
                    case 'tax_for_item':
                    case 'tax_for_shipping':
                    case 'gross_price_for_item':
                        $line_item->$db_column = $this->number_factory->createNumberCurrency($db_value, $this->currency_service->findCurrency($db_item->currency_code));
                        $line_item->$db_column->setIsLineTotal(true);
                        break;
                    case 'net_price_per_unit':
                    case 'discount_amount':
                    case 'shipping_for_item':
                        $line_item->$db_column = $this->number_factory->createNumberCurrency($db_value, $this->currency_service->findCurrency($db_item->currency_code));
                        break;
                    case 'no_of_units':
                    case 'product_shipping_units':
                        $line_item->$db_column = $this->number_factory->createNumber($db_value, 'quantity');
                        break;
                    case 'tax_rate_for_item':
                    case 'tax_rate_for_shipping':
                        $line_item->$db_column = $this->number_factory->createNumber($db_value, 'tax_rate');
                        break;
                    case 'discount_percentage':
                        $line_item->$db_column = $this->number_factory->createNumber($db_value);
                        break;
                    default:
                        $line_item->$db_column = $db_value;
                        break;
                }
            }
        }
        return $line_item;
    }

    public function mapLineItemsFromJson($json_string, nBillCurrencyMapper $currency_mapper, $document_type, nBillCurrency $currency)
    {
        $std_obj = json_decode($json_string, true);
        if ($std_obj) {
            $collection = $this->line_item_factory->createLineItemsCollection($currency, $document_type);

            foreach ($std_obj as $key=>$value)
            {
                switch ($key)
                {
                    case 'currency':
                        if ($currency) {
                            $collection->$key = $currency;
                        } else {
                            $collection->$key = $currency_mapper->mapFromArray($value);
                        }
                        break;
                    case 'sections':
                        foreach ($value as $array_sections)
                        {
                            $section = $this->line_item_factory->createLineItemsSection($collection, $document_type);
                            foreach ($array_sections as $section_key=>$section_value)
                            {
                                switch ($section_key)
                                {
                                    case 'line_items':
                                        foreach ($section_value as $line_item_key=>$line_item_value)
                                        {
                                            $item = $this->line_item_factory->createLineItem($document_type, $currency, $section);
                                            if (is_array($line_item_value))
                                            {
                                                foreach ($line_item_value as $item_key=>$item_value)
                                                {
                                                    switch ($item_key)
                                                    {
                                                        case 'net_price_per_unit':
                                                        case 'discount_amount':
                                                        case 'net_price_for_item':
                                                        case 'tax_for_item':
                                                        case 'shipping_for_item':
                                                        case 'tax_for_shipping':
                                                        case 'gross_price_for_item':
                                                            $number = $this->number_factory->createNumberCurrency(0, $currency);
                                                            $item->{$item_key} = $this->mapNumberFromJson($number, $item_value, $currency_mapper, $currency);
                                                            break;
                                                        case 'no_of_units':
                                                        case 'product_shipping_units':
                                                            $number = $this->number_factory->createNumber(0, 'quantity');
                                                            $item->{$item_key} = $this->mapNumberFromJson($number, $item_value, $currency_mapper);
                                                            break;
                                                        case 'tax_rate_for_item':
                                                        case 'tax_rate_for_shipping':
                                                            $number = $this->number_factory->createNumber(0, 'tax_rate');
                                                            $item->{$item_key} = $this->mapNumberFromJson($number, $item_value, $currency_mapper);
                                                            break;
                                                        case 'discount_percentage':
                                                            $number = $this->number_factory->createNumber(0);
                                                            $item->{$item_key} = $this->mapNumberFromJson($number, $item_value, $currency_mapper);
                                                            break;
                                                        case 'currency':
                                                            if ($currency) {
                                                                $item->{$item_key} = $currency;
                                                            } else {
                                                                $item->{$item_key} = $currency_mapper->mapFromArray($item_value);
                                                            }
                                                            break;
                                                        default:
                                                            $reflection_class = new ReflectionClass($item);
                                                            if (property_exists($item, $item_key) && $reflection_class->getProperty($item_key)->isPublic()) {
                                                                $item->{$item_key} = $item_value;
                                                            }
                                                            break;
                                                    }
                                                }
                                            }
                                            $section->line_items[] = $item;
                                        }
                                        break;
                                    case 'discount_percent':
                                        $number = $this->number_factory->createNumber(0);
                                        $section->$section_key = $this->mapNumberFromJson($number, $section_value, $currency_mapper);
                                        break;
                                    case 'discount_net':
                                    case 'discount_tax':
                                    case 'discount_gross':
                                        $number = $this->number_factory->createNumberCurrency(0, $currency);
                                        $section->$section_key = $this->mapNumberFromJson($number, $section_value, $currency_mapper);
                                        break;
                                    default:
                                        $reflection_class = new ReflectionClass($section);
                                        if (property_exists($section, $section_key) && $reflection_class->getProperty($section_key)->isPublic()) {
                                            $section->$section_key = $section_value;
                                        }
                                        break;
                                }
                            }
                            $collection->sections[] = $section;
                        }
                        break;
                    default:
                        $reflection_class = new ReflectionClass($collection);
                        if (property_exists($collection, $key) && $reflection_class->getProperty($key)->isPublic()) {
                            $collection->$key = $value;
                        }
                        break;
                }
            }
            return $collection;
        }
    }

    protected function mapNumberFromJson(nBillNumberDecimal $number, $item_value, nBillCurrencyMapper $currency_mapper, nBillCurrency $currency = null)
    {
        foreach ($item_value as $number_key=>$number_value)
        {
            switch ($number_key)
            {
                case 'currency':
                    if (!$currency) {
                        $currency = $currency_mapper->mapFromArray($number_value);
                    }
                    $number->$number_key = $currency;
                    break;
                case 'value':
                    $number->value = $number_value; //Other properties are not adjustable by the client
                    break;
                default:
                    break;
            }
        }
        return $number;
    }

    public function saveItems($document_id, nBillLineItemsCollection $line_items, $vendor_id, $entity_id)
    {
        $line_item_ids = array(); //Keep track of item IDs so we can remove any that have been deleted

        $ordering_offset = 0;
        foreach ($line_items->sections as $section)
        {
            foreach ($section->line_items as $line_item)
            {
                if ($line_item->id) {
                    $this->updateLineItem($section, $document_id, $line_item, $vendor_id, $entity_id, $ordering_offset);
                } else {
                    $line_item->id = $this->insertLineItem($section, $document_id, $line_item, $vendor_id, $entity_id, $ordering_offset);
                }
                $line_item_ids[] = intval($line_item->id);
            }
            $ordering_offset += count($section->line_items);
        }

        $sql = "DELETE FROM #__nbill_document_items WHERE document_id = " . intval($document_id) . " AND id NOT IN (" . implode(", ", $line_item_ids) . ")";
        $this->db->setQuery($sql);
        $this->db->query();
    }

    protected function updateLineItem($section, $document_id, nBillLineItem $line_item, $vendor_id, $entity_id, $ordering_offset)
    {
        $sql = "UPDATE #__nbill_document_items SET ";
        $sql .= "vendor_id = " . intval($vendor_id) . ", ";
        $sql .= "ordering = " . (intval($ordering_offset) + intval($line_item->index)) . ", ";
        $sql .= "entity_id = " . intval($line_item->entity_id) . ", ";
        $sql .= "nominal_ledger_code = '" . $this->db->getEscaped($line_item->nominal_ledger_code) . "', ";
        $sql .= "product_description = '" . $this->db->getEscaped($line_item->product_description) . "', ";
        $sql .= "detailed_description = '" . $this->db->getEscaped($line_item->detailed_description) . "', ";
        $sql .= "net_price_per_unit = '" . $line_item->net_price_per_unit->getEditableDecimal()->format() . "', ";
        $sql .= "no_of_units = '" . $line_item->no_of_units->getEditableDecimal()->format() . "', ";
        $sql .= "discount_percentage = '" . $line_item->discount_percentage->getEditableDecimal()->format() . "', ";
        $sql .= "discount_amount = '" . $line_item->discount_amount->getEditableDecimal()->format() . "', ";
        $sql .= "discount_description = '" . $this->db->getEscaped($line_item->discount_description) . "', ";
        $sql .= "net_price_for_item = '" . $line_item->net_price_for_item->getEditableDecimal()->format() . "', ";
        $sql .= "tax_rate_for_item = '" . $line_item->tax_rate_for_item->getEditableDecimal()->format() . "', ";
        $sql .= "electronic_delivery = " . ($line_item->electronic_delivery ? '1' : '0') . ", ";
        $sql .= "tax_for_item = '" . $line_item->tax_for_item->getEditableDecimal()->format() . "', ";
        $sql .= "shipping_id = '" . $line_item->shipping_id . "', ";
        $sql .= "shipping_for_item = '" . $line_item->shipping_for_item->getEditableDecimal()->format() . "', ";
        $sql .= "tax_rate_for_shipping = '" . $line_item->tax_rate_for_shipping->getEditableDecimal()->format() . "', ";
        $sql .= "tax_for_shipping = '" . $line_item->tax_for_shipping->getEditableDecimal()->format() . "', ";
        $sql .= "gross_price_for_item = '" . $line_item->gross_price_for_item->getEditableDecimal()->format() . "', ";
        $sql .= "product_code = '" . $this->db->getEscaped($line_item->product_code) . "', ";
        $sql .= "page_break = '" . ($line_item->page_break ? '1' : '0') . "', ";
        $sql .= "section_name = '" . ($line_item->index == count($section->line_items) - 1 ? $this->db->getEscaped($section->section_name) : '') . "', ";
        $sql .= "section_discount_title = '" . ($line_item->index == count($section->line_items) - 1 ? $this->db->getEscaped($section->discount_title) : '') . "', ";
        $sql .= "section_discount_percent = '" . ($line_item->index == count($section->line_items) - 1 ? $section->discount_percent->format() : '') . "'";

        if ($line_item instanceof nBillLineItemQuote) {
            $sql .= ", section_quote_atomic = " . ($line_item->index == count($section->line_items) - 1 ? ($section->section_quote_atomic ? '1' : '0') : '0') . ", ";
            $sql .= "quote_pay_freq = '" . $line_item->quote_pay_freq . "', ";
            $sql .= "quote_auto_renew = " . ($line_item->quote_auto_renew ? '1' : '0') . ", ";
            $sql .= "quote_relating_to = '" . $this->db->getEscaped($line_item->quote_relating_to) . "', ";
            $sql .= "quote_unique_invoice = " . ($line_item->quote_unique_invoice ? '1' : '0') . ", ";
            $sql .= "quote_mandatory = " . ($line_item->quote_mandatory ? '1' : '0') . ", ";
            $sql .= "quote_awaiting_payment = " . ($line_item->quote_awaiting_payment ? '1' : '0') . ", ";
            $sql .= "quote_item_accepted = " . ($line_item->quote_item_accepted ? '1' : '0') . ", ";
            $sql .= "quote_g_tx_id = " . intval($line_item->quote_g_tx_id);
        }

        $sql .= " WHERE id = " . intval($line_item->id);

        $this->db->setQuery($sql);
        $this->db->query();
    }

    protected function insertLineItem($section, $document_id, nBillLineItem $line_item, $vendor_id, $entity_id, $ordering_offset)
    {
        $sql = "INSERT INTO #__nbill_document_items (";
        $sql .= "vendor_id, document_id, ordering, ";
        $sql .= "entity_id, nominal_ledger_code, product_description, ";
        $sql .= "detailed_description, net_price_per_unit, no_of_units, ";
        $sql .= "discount_percentage, discount_amount, discount_description, net_price_for_item, ";
        $sql .= "tax_rate_for_item, electronic_delivery, tax_for_item, shipping_id, ";
        $sql .= "shipping_for_item, tax_rate_for_shipping, tax_for_shipping, ";
        $sql .= "gross_price_for_item, product_code, page_break, ";
        $sql .= "section_name, section_discount_title, section_discount_percent";
        if ($line_item instanceof nBillLineItemQuote) {
            $sql .=", quote_pay_freq, quote_auto_renew, quote_relating_to, ";
            $sql .= "quote_unique_invoice, quote_mandatory, quote_awaiting_payment, ";
            $sql .= "quote_item_accepted, quote_g_tx_id, section_quote_atomic";
        }
        $sql .= ") VALUES (";
        $sql .= intval($vendor_id) . ", ";
        $sql .= intval($document_id) . ", ";
        $sql .= (intval($ordering_offset) + intval($line_item->index)) . ", ";
        $sql .= intval($entity_id) . ", ";
        $sql .= "'" . $this->db->getEscaped($line_item->nominal_ledger_code) . "', ";
        $sql .= "'" . $this->db->getEscaped($line_item->product_description) . "', ";
        $sql .= "'" . $this->db->getEscaped($line_item->detailed_description) . "', ";
        $sql .= "'" . $line_item->net_price_per_unit->getEditableDecimal()->format() . "', ";
        $sql .= "'" . $line_item->no_of_units->getEditableDecimal()->format() . "', ";
        $sql .= "'" . $line_item->discount_percentage->getEditableDecimal()->format() . "', ";
        $sql .= "'" . $line_item->discount_amount->getEditableDecimal()->format() . "', ";
        $sql .= "'" . $this->db->getEscaped($line_item->discount_description) . "', ";
        $sql .= "'" . $line_item->net_price_for_item->getEditableDecimal()->format() . "', ";
        $sql .= "'" . $line_item->tax_rate_for_item->getEditableDecimal()->format() . "', ";
        $sql .= ($line_item->electronic_delivery ? '1' : '0') . ", ";
        $sql .= "'" . $line_item->tax_for_item->getEditableDecimal()->format() . "', ";
        $sql .= intval($line_item->shipping_id) . ", ";
        $sql .= "'" . $line_item->shipping_for_item->getEditableDecimal()->format() . "', ";
        $sql .= "'" . $line_item->tax_rate_for_shipping->getEditableDecimal()->format() . "', ";
        $sql .= "'" . $line_item->tax_for_shipping->getEditableDecimal()->format() . "', ";
        $sql .= "'" . $line_item->gross_price_for_item->getEditableDecimal()->format() . "', ";
        $sql .= "'" . $this->db->getEscaped($line_item->product_code) . "', ";
        $sql .= ($line_item->page_break ? '1' : '0') . ", ";
        $sql .= "'" . ($line_item->index == count($section->line_items) - 1 ? $this->db->getEscaped($section->section_name) : '') . "', ";
        $sql .= "'" . ($line_item->index == count($section->line_items) - 1 ? $this->db->getEscaped($section->discount_title) : '') . "', ";
        $sql .= "'" . ($line_item->index == count($section->line_items) - 1 ? $section->discount_percent->format() : '0.00') . "'";
        if ($line_item instanceof nBillLineItemQuote) {
            $sql .= ", ";
            $sql .= "'" . $this->db->getEscaped($line_item->quote_pay_freq) . "', ";
            $sql .= ($line_item->quote_auto_renew ? '1' : '0') . ", ";
            $sql .= "'" . $this->db->getEscaped($line_item->quote_relating_to) . "', ";
            $sql .= ($line_item->quote_unique_invoice ? '1' : '0') . ", ";
            $sql .= ($line_item->quote_mandatory ? '1' : '0') . ", ";
            $sql .= ($line_item->quote_awaiting_payment ? '1' : '0') . ", ";
            $sql .= ($line_item->quote_item_accepted ? '1' : '0') . ", ";
            $sql .= intval($line_item->quote_g_tx_id) . ", ";
            $sql .= ($section->quote_atomic ? '1' : '0');
        }
        $sql .= ")";
        $this->db->setQuery($sql);
        $this->db->query();
        return $this->db->insertid();
    }
}