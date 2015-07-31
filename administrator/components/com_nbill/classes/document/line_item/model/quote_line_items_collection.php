<?php
class nBillQuoteLineItemsCollection extends nBillLineItemsCollection
{
    const QUOTE_PAY_FREQUENCY_ONE_OFF = 'AA';

    /**
    * @return array Array of totals or totals of accepted items only (nBillINumberCurrency objects), keyed on payment frequency code
    */
    public function getTotal($column = 'gross_price_for_item', $accepted_only = false)
    {
        $accepted_totals = array();
        foreach ($this->sections as $section)
        {
            $section_accepted_totals = $section->getTotal($column, $accepted_only);
            foreach ($section_accepted_totals as $payment_frequency=>$section_total)
            {
                if (array_key_exists($payment_frequency, $accepted_totals)) {
                    $accepted_totals[$payment_frequency] = $accepted_totals[$payment_frequency]->addNumber($section_total);
                } else {
                    $accepted_totals[$payment_frequency] = $section_total;
                }
            }
        }
        return $accepted_totals;
    }

    public function recurringItemsPresent()
    {
        foreach ($this->sections as $section)
        {
            foreach ($section->line_items as $line_item)
            {
                if (strlen($line_item->quote_pay_freq) > 0 && $line_item->quote_pay_freq != self::QUOTE_PAY_FREQUENCY_ONE_OFF) {
                    return true;
                }
            }
        }
        return false;
    }
}