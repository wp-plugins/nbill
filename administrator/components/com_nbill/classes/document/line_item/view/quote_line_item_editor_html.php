<?php
class nBillQuoteLineItemEditorHtml extends nBillLineItemEditorHtml
{
    protected function loadSupportingScripts()
    {
        parent::loadSupportingScripts();
        $js = file_get_contents(realpath(dirname(__FILE__) . '/js/editor_quote_line_item.js'));
        nbf_cms::$interop->add_html_header('<script type="text/javascript">' . $js . '</script>');
    }

    protected function renderEditorBasicHeadingColumns()
    {
        $this->renderColumnHeading('item_number', '#', 'responsive-cell high-priority');
        $this->renderColumnHeading('product_code', NBILL_INVOICE_ITEM_CODE, 'responsive-cell optional');
        $this->renderColumnHeading('product_description', NBILL_INVOICE_ITEM_NAME);
        if ($this->canHideColumn('net_price_for_item') && $this->canHideColumn('tax_for_item')) {
            $this->renderColumnHeading('net_price_per_unit', NBILL_INVOICE_ITEM_NET_PRICE, 'numeric responsive-cell high-priority', '', true);
            $this->renderColumnHeading('no_of_units', NBILL_INVOICE_ITEM_QTY, 'numeric responsive-cell high-priority', '', true);
        } else {
            $this->renderColumnHeading('net_price_for_item', NBILL_INVOICE_ITEM_TOTAL_NET, 'numeric responsive-cell high-priority');
            $this->renderColumnHeading('tax_for_item', NBILL_INVOICE_ITEM_TAX, 'numeric responsive-cell high-priority');
        }
        $this->renderColumnHeading('shipping_for_item', NBILL_INVOICE_ITEM_SHIPPING, 'responsive-cell optional numeric');
        $this->renderColumnHeading('tax_for_shipping', NBILL_INVOICE_ITEM_SHIPPING_TAX, 'responsive-cell optional numeric');
        $this->renderColumnHeading('gross_price_for_item', NBILL_INVOICE_ITEM_GROSS, 'numeric');
        $this->renderColumnHeading('quote_item_accepted', NBILL_QUOTE_IS_ITEM_ACCEPTED, 'responsive-cell priority');
        $this->renderColumnHeading('action', NBILL_INVOICE_ITEM_ACTION);
    }

    protected function renderEditorBasicValueColumns($line_item, $item_number)
    {
        ob_start();
        $this->renderMoveButtons($line_item, '_basic');
        $up_down_buttons = ob_get_clean();
        $this->renderColumnValue('item_number', $item_number . $up_down_buttons, 'responsive-cell high-priority');
        $this->renderColumnValue('product_code', $line_item->product_code, 'responsive-cell optional');
        $this->renderColumnValue('product_description', $this->createEditableField('product_description', $line_item, $line_item->product_description), 'td-product-description');
        if ($this->canHideColumn('net_price_for_item') && $this->canHideColumn('tax_for_item')) {
            $this->renderColumnValue('net_price_per_unit', '<span class="responsive-cell optional">' . $this->createEditableField('net_price_per_unit', $line_item, $line_item->net_price_per_unit->getEditableDecimal()->format(), $line_item->net_price_per_unit) . '</span><span class="responsive-cell inverse-optional">' . $line_item->net_price_per_unit->format() . '</span>', 'numeric responsive-cell high-priority', '', true);
            $this->renderColumnValue('no_of_units', '<span class="responsive-cell optional">' . $this->createEditableField('no_of_units', $line_item, $line_item->no_of_units->getEditableDecimal()->format(), $line_item->no_of_units->format()) . '</span><span class="responsive-cell inverse-optional">' . $line_item->no_of_units->format() . '</span>', 'numeric responsive-cell high-priority', '', true);
        } else {
            $this->renderColumnValue('net_price_for_item', '<span class="responsive-cell optional">' . $this->createEditableField('net_price_for_item', $line_item, $line_item->net_price_for_item->getEditableDecimal()->format(), $line_item->net_price_for_item) . '</span><span class="responsive-cell inverse-optional">' . $line_item->getTotalNetForItem() . '</span>', 'numeric responsive-cell high-priority');
            $this->renderColumnValue('tax_for_item', '<span class="responsive-cell optional">' . $this->createEditableField('tax_for_item', $line_item, $line_item->tax_for_item->getEditableDecimal()->format(), $line_item->tax_for_item) . '</span><span class="responsive-cell inverse-optional">' . $line_item->getTotalTaxForItem() . '</span>', 'numeric responsive-cell high-priority');
        }
        $this->renderColumnValue('shipping_for_item', $this->createEditableField('shipping_for_item', $line_item, $line_item->shipping_for_item->getEditableDecimal()->format(), $line_item->shipping_for_item), 'responsive-cell optional numeric');
        $this->renderColumnValue('tax_for_shipping', $this->createEditableField('tax_for_shipping', $line_item, $line_item->tax_for_shipping->getEditableDecimal()->format(), $line_item->tax_for_shipping), 'responsive-cell optional numeric');
        $gross_value = $line_item->gross_price_for_item->format();
        if ($line_item->quote_pay_freq != nBillQuoteLineItemsCollection::QUOTE_PAY_FREQUENCY_ONE_OFF) {
            nbf_common::load_language("template.qu");
            $gross_value .= '<br /><span style="white-space:nowrap">' . constant('NBILL_PER_' . strtoupper($line_item->quote_pay_freq));
        }
        $this->renderColumnValue('gross_price_for_item', $gross_value, 'numeric');
        $alt = $line_item->quote_item_accepted ? NBILL_YES : NBILL_NO;

        $img = '<a href="javascript:void(0);" onclick="toggleAccepted(' . $line_item->getParentSection()->index . ', ' . $line_item->index . ', \'' . NBILL_YES . '\', \'' . NBILL_NO . '\');var e = arguments[0]||window.event;if(e.stopPropagation){e.stopPropagation();}else{e.cancelBubble = true;}"><img id="img_accepted_' . $line_item->getParentSection()->index . '_' . $line_item->index . '" src="' . nbf_cms::$interop->nbill_site_url_path . '/images/icons/' . ($line_item->quote_item_accepted ? 'tick' : 'cross') . '.png" alt="' . $alt . '" title="' . $alt . '" border="0" /></a>';
        $this->renderColumnValue('quote_item_accepted', $img, 'responsive-cell priority center-contents');

        $can_section_break = !($line_item->index==count($line_item->getParentSection()->line_items)-1);
        $id_suffix = '_basic';

        ob_start(); include(dirname(__FILE__) . '/template/editor_summary_items_action_buttons.php'); $action_buttons = ob_get_clean();
        $this->renderColumnValue('action', $action_buttons);
    }

    protected function renderEditorBasicSectionDiscountColumns($section)
    {
        parent::renderEditorBasicSectionDiscountColumns($section);
        $this->renderColumnValue('quote_item_accepted', '&nbsp;', 'responsive-cell priority');
    }

    protected function renderEditorBasicSectionTotalColumns($section_name, $section)
    {
        parent::renderEditorBasicSectionTotalColumns($section_name, $section);
        $this->renderColumnValue('quote_item_accepted', '&nbsp;', 'responsive-cell priority');
    }

    protected function renderEditorBasicTotalColumns()
    {
        $this->renderColumnValue('item_number', '', 'responsive-cell high-priority');
        $this->renderColumnValue('product_code', '', 'responsive-cell optional');
        $this->renderColumnValue('product_description', NBILL_INVOICE_ITEM_TOTALS);
        if ($this->canHideColumn('net_price_for_item') && $this->canHideColumn('tax_for_item')) {
            $this->renderColumnValue('net_price_per_unit', '', 'numeric responsive-cell high-priority', '', true);
            $this->renderColumnValue('no_of_units', '', 'numeric responsive-cell high-priority', '', true);
        } else {
            $this->renderColumnValue('net_price_for_item', '<span class="responsive-cell optional">' . $this->line_item_collection->getNetTotal() . '</span><span class="responsive-cell inverse-optional">' . $this->line_item_collection->getNetTotal()->addNumber($this->line_item_collection->getShippingTotal()) . '</span>', 'numeric responsive-cell high-priority');
            $this->renderColumnValue('tax_for_item', '<span class="responsive-cell optional">' . $this->line_item_collection->getItemTaxTotal() . '</span><span class="responsive-cell inverse-optional">' . $this->line_item_collection->getTaxTotal() . '</span>', 'numeric responsive-cell high-priority');
        }
        $this->renderColumnValue('shipping_for_item', $this->line_item_collection->getShippingTotal(), 'responsive-cell optional numeric');
        $this->renderColumnValue('tax_for_shipping', $this->line_item_collection->getShippingTaxTotal(), 'responsive-cell optional numeric');
        $this->renderColumnValue('gross_price_for_item', $this->line_item_collection->getGrossTotal(), 'numeric');
        $this->renderColumnValue('quote_item_accepted', '&nbsp;', 'responsive-cell priority');
        ob_start();
        $this->renderAddNewButton();
        $add_button = ob_get_clean();
        $this->renderColumnValue('action', $add_button);
    }

    protected function getEditorBasicColumnCount()
    {
        $col_count = parent::getEditorBasicColumnCount();
        $col_count += 1; //We have 1 extra column for quotes
        return $col_count;
    }

    protected function renderEditorAdvancedHeadingColumns()
    {
        ?>
        <th class="responsive-cell high-priority nbill-admin-th-doc-item-no">#</th>
        <th class="responsive-cell high-priority nbill-admin-th-doc-item-code"><?php echo NBILL_INVOICE_ITEM_CODE; ?></th>
        <th class="responsive-cell priority nbill-admin-th-doc-item-name"><?php echo NBILL_INVOICE_ITEM_NAME; ?></th>
        <th class="responsive-cell optional nbill-admin-th-doc-item-ledger"><?php echo NBILL_INVOICE_ITEM_LEDGER; ?></th>
        <?php
        if (!$this->canHideColumn('net_price_for_item') || !$this->canHideColumn('tax_for_item')) { ?>
            <th class="numeric nbill-admin-th-doc-net-price"><?php echo NBILL_INVOICE_ITEM_NET_PRICE; ?></th>
            <th class="nbill-admin-th-doc-quantity"><?php echo NBILL_INVOICE_ITEM_QTY; ?></th>
            <?php
        }
        if ($this->line_item_collection->getDiscountsPresent()) { ?>
            <th class="responsive-cell optional nbill-admin-th-doc-discount-desc"><?php echo NBILL_INVOICE_ITEM_DISCOUNT_DESC; ?></th>
            <th class="numeric nbill-admin-th-doc-discount-amount"><?php echo NBILL_INVOICE_ITEM_DISCOUNT_AMOUNT; ?></th>
        <?php } ?>
        <th class="responsive-cell optional nbill-admin-th-doc-relating-to"><?php echo NBILL_QUOTE_RELATING_TO; ?></th>
        <th class="responsive-cell optional nbill-admin-th-doc-electronic-delivery"><?php echo NBILL_LINE_ITEM_ELECTRONIC_DELIVERY; ?></th>
        <th class="nbill-admin-th-doc-action"><?php echo NBILL_INVOICE_ITEM_ACTION; ?></th>
        <?php
    }

    protected function renderEditorAdvancedValueColumns($item_number, nBillLineItem $line_item)
    {
        ob_start();
        $this->renderMoveButtons($line_item, '_advanced');
        $up_down_buttons = ob_get_clean();
        ?>
        <td class="responsive-cell high-priority nbill-admin-td-doc-item-no"><?php echo $up_down_buttons . $item_number; ?></td>
        <td class="responsive-cell high-priority nbill-admin-td-doc-item-code"><?php echo $line_item->product_code; ?></td>
        <td class="responsive-cell priority nbill-admin-td-doc-item-desc"><?php echo $line_item->product_description; ?></td>
        <td class="responsive-cell optional nbill-admin-td-doc-item-ledger"><?php echo $line_item->nominal_ledger_code; if (strlen($line_item->nominal_ledger_description) > 0) { ?> (<?php echo $line_item->nominal_ledger_description; ?>)<?php } ?></td>
        <?php
        if (!$this->canHideColumn('net_price_for_item') || !$this->canHideColumn('tax_for_item')) {
            ?>
            <td class="numeric nbill-admin-td-doc-net-price"><?php echo $this->createEditableField('net_price_per_unit', $line_item, $line_item->net_price_per_unit->getEditableDecimal()->format(), $line_item->net_price_per_unit->format()); ?></td>
            <td class="nbill-admin-td-doc-quantity"><?php echo $this->createEditableField('no_of_units', $line_item, $line_item->no_of_units->getEditableDecimal()->format(), $line_item->no_of_units->format()); ?></td>
            <?php
        }
        if ($this->line_item_collection->getDiscountsPresent()) { ?>
            <td class="responsive-cell optional nbill-admin-td-doc-discount-desc"><?php echo $line_item->discount_description; ?></td>
            <td class="numeric nbill-admin-td-doc-discount-amount"><?php echo $line_item->discount_amount->format(); ?></td>
        <?php } ?>
        <td class="responsive-cell optional word-breakable nbill-admin-td-doc-quote-relating-to"><?php echo $line_item->quote_relating_to; ?></td>
        <td class="responsive-cell optional nbill-admin-td-doc-electronic-delivery"><?php echo $line_item->electronic_delivery ? NBILL_YES : NBILL_NO; ?></td>

        <td class="nbill-admin-td-doc-action">
            <?php
            $can_section_break = !($line_item->index==count($line_item->getParentSection()->line_items)-1);
            $id_suffix = '_advanced';
            include(dirname(__FILE__) . '/template/editor_summary_items_action_buttons.php'); ?>
        </td>
        <?php
    }

    protected function getEditorAdvancedColumnCount()
    {
        $col_count = parent::getEditorAdvancedColumnCount();
        $col_count += 1; //We have 1 extra column for quotes
        return $col_count;
    }
}