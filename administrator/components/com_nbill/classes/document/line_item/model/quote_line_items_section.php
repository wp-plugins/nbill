<?php
class nBillQuoteLineItemsSection extends nBillLineItemsSection
{
    const QUOTE_ITEMS_PARTIALLY_ACCEPTED = 0;
    const QUOTE_ITEMS_ACCEPTED = 1;
    const QUOTE_ITEMS_NOT_ACCEPTED = 2;

    /** @var boolean **/
    public $quote_atomic;

    public function jsonSerialize()
    {
        return array(
            'index' => $this->index,
            'section_name' => $this->section_name,
            'quote_atomic' => $this->quote_atomic,
            'discount_title' => $this->discount_title,
            'discount_percent' => $this->discount_percent,
            'discount_net' => $this->discount_net,
            'discount_tax' => $this->discount_tax,
            'discount_gross' => $this->discount_gross,
            'line_items' => $this->line_items
        );
    }

    public function getQuoteItemsAcceptedStatus()
    {
        $accepted_count = 0;
        foreach ($this->line_items as $line_item)
        {
            if ($line_item->type == 'QU' && $line_item->quote_item_accepted) {
                $accepted_count++;
            }
        }

        if ($accepted_count == count($this->line_items)) {
            return self::QUOTE_ITEMS_ACCEPTED;
        } else if ($accepted_count > 0) {
            return self::QUOTE_ITEMS_PARTIALLY_ACCEPTED;
        } else {
            return self::QUOTE_ITEMS_NOT_ACCEPTED;
        }
    }

    /**
    * @return array Array of totals or totals of accepted items only (nBillINumberCurrency objects), keyed on payment frequency code
    */
    public function getTotal($column = 'gross_price_for_item', $accepted_only = false)
    {
        $accepted_total = array();
        foreach ($this->line_items as $line_item)
        {
            if (!$accepted_only || $line_item->quote_item_accepted) {
                if (!array_key_exists($line_item->quote_pay_freq, $accepted_total)) {
                    $accepted_total[$line_item->quote_pay_freq] = $this->number_factory->createNumberCurrency(0, $this->parent_collection->getCurrency());
                    $accepted_total[$line_item->quote_pay_freq]->setIsGrandTotal(true);
                }
                $accepted_total[$line_item->quote_pay_freq] = $accepted_total[$line_item->quote_pay_freq]->addNumber($line_item->$column);
            }
        }
        if ($this->discount_percent->value != 0) {
            $total_discount_value = $this->number_factory->createNumberCurrency(0, $this->parent_collection->getCurrency());
            switch ($column) {
                case 'net_price_for_item':
                    $total_discount_value = clone($this->discount_net);
                    break;
                case 'tax_for_item':
                    $total_discount_value = clone($this->discount_tax);
                    break;
                case 'gross_price_for_item':
                    $total_discount_value = clone($this->discount_gross);
                    break;
            }
            if ($total_discount_value->value != 0) {
                foreach ($accepted_total as $pay_freq=>$amount) {
                    $this_discount = $this->number_factory->createNumberCurrency(($amount->value / 100) * $this->discount_percent->value, $this->parent_collection->getCurrency());
                    $this_discount->value = $this_discount->value > $total_discount_value->value ? $total_discount_value->value : $this_discount->value;
                    $accepted_total[$pay_freq] = $accepted_total[$pay_freq]->subtractNumber($this_discount);
                    $total_discount_value->subtractNumber($this_discount);
                }
            }
        }
        return $accepted_total;
    }
}