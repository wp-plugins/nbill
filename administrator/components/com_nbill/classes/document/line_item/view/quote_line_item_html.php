<?php
class nBillQuoteLineItemHtml extends nBillLineItemHtml
{
    protected function renderHeadingColumns($styles)
    {
        parent::renderHeadingColumns($styles);
        list($style_th_left, $style_th) = $styles;
        if ($this->document->status == 'EE') { //Part accepted quote
            $this->renderColumnHeading('quote_item_status', $this->translator->parseTranslation($this->document->language, 'NBILL_QUOTE_ITEM_ACCEPTED_TITLE', "template.qu"), 'main_headings', 'style="' . $style_th . '"');
        }
    }

    protected function renderGrossPriceColumnValue(nBillLineItem $line_item, $styles)
    {
        list($style_td_left, $style_td_numeric, $style_td_center) = $styles;
        $gross_value = $line_item->gross_price_for_item->format();
        if ($line_item->quote_pay_freq != nBillQuoteLineItemsCollection::QUOTE_PAY_FREQUENCY_ONE_OFF) {
            $gross_value .= '<br /><span style="white-space:nowrap">' . $this->translator->parseTranslation($this->document->language, 'NBILL_PER_' . strtoupper($line_item->quote_pay_freq), "template.qu");
        }
        $this->renderColumnValue('gross_price_for_item', $gross_value, '', 'style="' . $style_td_numeric . '"');
        if ($this->document->status == 'EE') { //Part accepted quote
            $alt = $line_item->quote_item_accepted ? $this->translator->parseTranslation($this->document->language, 'NBILL_QUOTE_ITEM_ACCEPTED_YES', "template.qu") : $this->translator->parseTranslation($this->document->language, 'NBILL_QUOTE_ITEM_ACCEPTED_NO', "template.qu");
            if ($this->pdf) {
                $img_url = "file://" . nbf_cms::$interop->nbill_fe_base_path . '/images/icons/' . ($line_item->quote_item_accepted ? 'tick' : 'cross') . '.png';
            } else {
                $img_url = nbf_cms::$interop->nbill_site_url_path . '/images/icons/' . ($line_item->quote_item_accepted ? 'tick' : 'cross') . '.png';
            }
            $this->renderColumnValue('quote_item_accepted', '<img src="' . $img_url . '" alt="' . $alt . '" title="' . $alt . '" border="0" />', '', 'style="' . $style_td_center . '"');
        }
    }

    protected function renderSectionDiscountColumns(nBillQuoteLineItemsSection $section, $styles = array())
    {
        parent::renderSectionDiscountColumns($section, $styles);
        list($style_td_left, $style_td_numeric, $style_td_center) = $styles;
        if ($this->document->status == 'EE') { //Part accepted quote
            switch ($section->getQuoteItemsAcceptedStatus())
            {
                case nBillQuoteLineItemsSection::QUOTE_ITEMS_PARTIALLY_ACCEPTED:
                    $alt = $this->translator->parseTranslation($this->document->language, 'NBILL_QUOTE_ITEM_ACCEPTED_PARTIAL', "template.qu");
                    $image = 'partial';
                    break;
                case nBillQuoteLineItemsSection::QUOTE_ITEMS_ACCEPTED:
                    $alt = $this->translator->parseTranslation($this->document->language, 'NBILL_QUOTE_ITEM_ACCEPTED_YES', "template.qu");
                    $image = 'tick';
                    break;
                case nBillQuoteLineItemsSection::QUOTE_ITEMS_NOT_ACCEPTED:
                default:
                    $alt = $this->translator->parseTranslation($this->document->language, 'NBILL_QUOTE_ITEM_ACCEPTED_NO', "template.qu");
                    $image = 'cross';
                    break;
            }
            if ($this->pdf) {
                $img_url = "file://" . nbf_cms::$interop->nbill_fe_base_path . '/images/icons/' . $image . '.png';
            } else {
                $img_url = nbf_cms::$interop->nbill_site_url_path . '/images/icons/' . $image . '.png';
            }
            $this->renderColumnValue('quote_item_accepted', '<img src="' . $img_url . '" alt="' .  $alt . '" title="' . $alt . '" border="0" />', '', 'style="' . $style_td_center . '"');
        }
    }

    protected function renderSectionTotalColumns(nBillQuoteLineItemsSection $section, $styles = array())
    {
        parent::renderSectionTotalColumns($section, $styles);
        list($style_subtotal_td, $style_subtotal_td_numeric) = $styles;
        if ($this->document->status == 'EE') { //Part accepted quote
            $this->renderColumnValue('quote_item_accepted', '&nbsp;', '', 'style="' . $style_subtotal_td_numeric . '"');
        }
    }

    protected function renderTotalRow($styles = array())
    {
        $quote_totals_net_for_item = $this->line_item_collection->getTotal('net_price_for_item');
        $quote_totals_tax_for_item = $this->line_item_collection->getTotal('tax_for_item');
        $quote_totals_shipping_for_item = $this->line_item_collection->getTotal('shipping_for_item');
        $quote_totals_tax_for_shipping = $this->line_item_collection->getTotal('tax_for_shipping');
        $quote_totals_gross = $this->line_item_collection->getTotal();

        $this->renderSeparator();

        $index = 0;
        foreach ($quote_totals_gross as $payment_frequency=>$total_gross)
        {
            ?>
            <tr class="summary-total-row grand-total">
                <?php
                $this->renderTotalColumns($payment_frequency, $quote_totals_net_for_item[$payment_frequency], $quote_totals_tax_for_item[$payment_frequency],
                        $quote_totals_shipping_for_item[$payment_frequency], $quote_totals_tax_for_shipping[$payment_frequency],
                        $quote_totals_gross[$payment_frequency], $styles, $index);
                ?>
            </tr>
            <?php
            $index++;
        }

        $this->renderAcceptedTotals($styles);
    }

    protected function renderTotalColumns($payment_frequency, $total_net_for_item, $total_tax_for_item, $total_shipping_for_item,
                $total_tax_for_shipping, $total_gross, $styles, $index)
    {
        list($style_th, $style_th_numeric, $style_th_left, $style_th_section_header,
                    $style_shaded_row, $style_td, $style_td_left, $style_td_top, $style_td_top_left, $style_td_numeric,
                    $style_td_numeric_left, $style_td_numeric_top, $style_td_numeric_top_left, $style_td_center, $style_detailed_desc, $style_subtotal_td,
                    $style_subtotal_td_numeric, $style_total_td, $style_total_td_top, $style_total_td_numeric, $style_total_td_numeric_left,
                    $style_total_td_numeric_top) = $styles;

        $description = $this->translator->parseTranslation($this->document->language, 'NBILL_PRT_QUOTE_TOTAL', "template.qu");
        if ($payment_frequency != nBillQuoteLineItemsCollection::QUOTE_PAY_FREQUENCY_ONE_OFF) {
            $description .= $this->translator->parseTranslation($this->document->language, 'NBILL_PER_' . strtoupper($payment_frequency), "template.qu");
        } else if ($this->line_item_collection->recurringItemsPresent()) {
            $description .= $this->translator->parseTranslation($this->document->language, 'NBILL_PRT_TOTAL_ONE_OFF', "template.qu");
        }
        $this->renderColumnValue('product_description', $description, '', 'style="' . ($index == 0 ? $style_total_td_top : $style_total_td) . '"');
        $this->renderColumnValue('net_price_per_unit', '&nbsp;', '', 'style="' . ($index == 0 ? $style_total_td_numeric_top : $style_total_td_numeric) . '"');
        $this->renderColumnValue('no_of_units', '&nbsp;', '', 'style="' . ($index == 0 ? $style_total_td_numeric_top : $style_total_td_numeric) . '"');
        $this->renderColumnValue('discount_amount', '&nbsp;', '', 'style="' . ($index == 0 ? $style_total_td_numeric_top : $style_total_td_numeric) . '"');
        $this->renderColumnValue('net_price_for_item', $total_net_for_item, '', 'style="' . ($index == 0 ? $style_total_td_numeric_top : $style_total_td_numeric) . '"');
        $this->renderColumnValue('tax_for_item', $total_tax_for_item, '', 'style="' . ($index == 0 ? $style_total_td_numeric_top : $style_total_td_numeric) . '"');
        $this->renderColumnValue('shipping_for_item', $total_shipping_for_item, '', 'style="' . ($index == 0 ? $style_total_td_numeric_top : $style_total_td_numeric) . '"');
        $this->renderColumnValue('tax_for_shipping', $total_tax_for_shipping, '', 'style="' . ($index == 0 ? $style_total_td_numeric_top : $style_total_td_numeric) . '"');
        $this->renderColumnValue('gross_price_for_item', $total_gross, '', 'style="' . ($index == 0 ? $style_total_td_numeric_top : $style_total_td_numeric) . '"');
        if ($this->document->status == 'EE') { //Part accepted quote
            $this->renderColumnValue('quote_item_accepted', '&nbsp;', '', 'style="' . ($index == 0 ? $style_total_td_numeric_top : $style_total_td_numeric) . '"');
        }
    }

    public function renderSeparator()
    {
        ?>
        <tr><td colspan="<?php echo $this->getColumnCount(); ?>" style="border:none;">&nbsp;</td></tr>
        <?php
    }

    protected function renderAcceptedTotals($styles = array())
    {
        if ($this->document->status == 'EE')
        {
            //Get accepted totals
            $accepted_totals_net_for_item = $this->line_item_collection->getTotal('net_price_for_item', true);
            $accepted_totals_tax_for_item = $this->line_item_collection->getTotal('tax_for_item', true);
            $accepted_totals_shipping_for_item = $this->line_item_collection->getTotal('shipping_for_item', true);
            $accepted_totals_tax_for_shipping = $this->line_item_collection->getTotal('tax_for_shipping', true);
            $accepted_totals_gross = $this->line_item_collection->getTotal('gross_price_for_item', true);

            $this->renderSeparator();
            list ($style_th, $style_th_numeric, $style_th_left, $style_th_section_header,
                    $style_shaded_row, $style_td, $style_td_left, $style_td_top, $style_td_top_left, $style_td_numeric,
                    $style_td_numeric_left, $style_td_numeric_top, $style_td_numeric_top_left, $style_td_center, $style_detailed_desc, $style_subtotal_td,
                    $style_subtotal_td_numeric, $style_total_td, $style_total_td_top, $style_total_td_numeric, $style_total_td_numeric_left,
                    $style_total_td_numeric_top, $style_total_td_numeric_top_left, $style_accepted_total_td, $style_accepted_total_td_top,
                    $style_accepted_total_td_numeric, $style_accepted_total_td_numeric_top) = $styles;

            $index = 0;
            foreach ($accepted_totals_gross as $payment_frequency=>$total_gross)
            {
                ?>
                <tr class="summary-total-row accepted-total">
                <?php
                $description = $this->translator->parseTranslation($this->document->language, 'NBILL_QUOTE_ACCEPTED_TOTAL', "template.common");
                if ($payment_frequency != nBillQuoteLineItemsCollection::QUOTE_PAY_FREQUENCY_ONE_OFF) {
                    $description .= $this->translator->parseTranslation($this->document->language, 'NBILL_PER_' . strtoupper($payment_frequency), "template.qu");
                } else if ($this->line_item_collection->recurringItemsPresent()) {
                    $description .= $this->translator->parseTranslation($this->document->language, 'NBILL_PRT_TOTAL_ONE_OFF', "template.qu");
                }
                $this->renderColumnValue('product_description', $description, '', 'style="' . ($index == 0 ? $style_accepted_total_td_top : $style_accepted_total_td) . '"');
                $this->renderColumnValue('net_price_per_unit', '&nbsp;', '', 'style="' . ($index == 0 ? $style_accepted_total_td_numeric_top : $style_accepted_total_td_numeric) . '"');
                $this->renderColumnValue('no_of_units', '&nbsp;', '', 'style="' . ($index == 0 ? $style_accepted_total_td_numeric_top : $style_accepted_total_td_numeric) . '"');
                $this->renderColumnValue('discount_amount', '&nbsp;', '', 'style="' . ($index == 0 ? $style_accepted_total_td_numeric_top : $style_accepted_total_td_numeric) . '"');
                $this->renderColumnValue('net_price_for_item', $accepted_totals_net_for_item[$payment_frequency], '', 'style="' . ($index == 0 ? $style_accepted_total_td_numeric_top : $style_accepted_total_td_numeric) . '"');
                $this->renderColumnValue('tax_for_item', $accepted_totals_tax_for_item[$payment_frequency], '', 'style="' . ($index == 0 ? $style_accepted_total_td_numeric_top : $style_accepted_total_td_numeric) . '"');
                $this->renderColumnValue('shipping_for_item', $accepted_totals_shipping_for_item[$payment_frequency], '', 'style="' . ($index == 0 ? $style_accepted_total_td_numeric_top : $style_accepted_total_td_numeric) . '"');
                $this->renderColumnValue('tax_for_shipping', $accepted_totals_tax_for_shipping[$payment_frequency], '', 'style="' . ($index == 0 ? $style_accepted_total_td_numeric_top : $style_accepted_total_td_numeric) . '"');
                $this->renderColumnValue('gross_price_for_item', $total_gross, '', 'style="' . ($index == 0 ? $style_accepted_total_td_numeric_top : $style_accepted_total_td_numeric) . '"');
                $this->renderColumnValue('quote_item_accepted', '&nbsp;', '', 'style="' . ($index == 0 ? $style_accepted_total_td_numeric_top : $style_accepted_total_td_numeric) . '"');
                $index++;
                ?>
                </tr>
                <?php
            }
        }
    }

    public function getColumnCount()
    {
        $col_count = parent::getColumnCount();
        if ($this->document->status == 'EE') { //Part accepted quote
            $col_count += $this->canHideColumn('quote_item_status') ? 0 : 1;
        }
        return $col_count;
    }
}