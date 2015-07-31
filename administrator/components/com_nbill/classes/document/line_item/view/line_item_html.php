<?php
class nBillLineItemHtml
{
    /** @var nBillLineItemsCollection **/
    public $line_item_collection;
    /** @var object $document For now, this is just a stdClass, but will eventually be a properly defined object **/
    public $document;
    /** @var nbf_tab_group **/
    protected $line_tab_settings;
    /** @var nBillTranslator **/
    protected $translator;
    /** @var boolean Whether or not we are rendering for a PDF conversion **/
    protected $pdf = false;
    /** @var array **/
    protected $shipping_methods = array();
    /** @var nBillConfiguration **/
    protected $config;

    public function __construct(nBillLineItemsCollection $line_item_collection, $document = null, nBillConfiguration $config = null)
    {
        $this->line_item_collection = $line_item_collection;
        $this->document = $document;
        if ($config == null) {
            $config = nBillConfigurationService::getInstance()->getConfig();
        }
        $this->config = $config;
    }

    protected function htmlAttributes($class, $attributes)
    {
        $value = strlen($class) > 0 ? ' class="' . $class . '"' : '';
        $value .= strlen($attributes) > 0 ? ' ' . $attributes : '';
        return $value;
    }

    public function getSectionName($section)
    {
        $section_name = strlen($section->section_name) > 0 ? $section->section_name : (count($this->line_item_collection->sections) > 1 ? (defined('NBILL_SECTION_OTHER') ? NBILL_SECTION_OTHER : 'Other') : '');
        return $section_name;
    }

    protected function canHideColumn($column)
    {
        switch ($column)
        {
            case 'net_price_per_unit':
            case 'no_of_units':
                if (!$this->config->never_hide_quantity && !$this->line_item_collection->getUnitQuantityPresent()) {
                    return true;
                }
                break;
            case 'discount_amount':
                if (!$this->line_item_collection->getDiscountsPresent()) {
                    return true;
                }
                break;
            case 'net_price_for_item':
            case 'tax_for_item':
                if (!$this->config->never_hide_tax && !$this->line_item_collection->getTaxPresent()) {
                    return true;
                }
                break;
            case 'shipping_for_item':
                if (!$this->line_item_collection->getShippingPresent()) {
                    return true;
                }
                break;
            case 'tax_for_shipping':
                if (!$this->line_item_collection->getShippingTaxPresent()) {
                    return true;
                }
                break;
        }
        return false;
    }

    /**
    * Render column heading
    * @param string $column Name of column
    * @param string $column_text Constant representing text to output for column heading (will be translated if applicable)
    * @param string $class Name of CSS class, if any
    * @param string $attributes Any other HTML attributes to apply to the th tag
    */
    protected function renderColumnHeading($column, $column_text, $class = '', $attributes = '', $force_show = false)
    {
        if ($force_show || !$this->canHideColumn($column)) {
            $text = $column_text;
            if (isset($this->translator)) {
                $text = $this->translator->parseTranslation($this->document->language, $column_text);
            }
            $class .= ($class ? ' ' : '') . 'nbill-doc-th-' . str_replace('_', '-', strtolower($column));
            ?>
            <th<?php echo $this->htmlAttributes($class, $attributes); ?>><?php echo $text; ?></th>
            <?php
        };
    }

    /**
    * Render column value
    * @param mixed $column Name of column
    * @param mixed $value HTML or raw value to output inside the td tag
    * @param mixed $class Name of CSS class, if any
    * @param mixed $attributes Any other HTML attributes to apply to the th tag
    */
    protected function renderColumnValue($column, $value, $class = '', $attributes = '', $force_show = false)
    {
        if (!$force_show && $this->canHideColumn($column)) {
            return;
        }
        $class .= ($class ? ' ' : '') . 'nbill-doc-td-' . str_replace('_', '-', strtolower($column));
        ?>
        <td<?php echo $this->htmlAttributes($class, $attributes); ?>><?php echo $value; ?></td>
        <?php
    }

    /**
    * Renders the line items for display on a document template
    * @param nBillTranslator $translator
    * @param array $inline_styles Array of inline styling rules, used to allow the PDF generator to work (standard stylesheet support is patchy at best)
    * @param boolean $pdf Whether or not we are rendering for a PDF conversion
    * @param string $page_break_html HTML to output when a page break is encountered (ie. to close any open tags and start a new page)
    * @param boolean $return_html Whether or not to return the resulting HTML (or output it to the browser)
    */
    public function renderDocumentSummary(nBillTranslator $translator, $inline_styles = array(), $pdf = false, $page_break_html = '', $return_html = false)
    {
        ob_start();
        $this->translator = $translator;
        $this->renderDocumentLineItems($pdf, $page_break_html, $inline_styles);
        $html = ob_get_clean();
        if ($return_html) {
            return $html;
        } else {
            echo $html;
        }
    }

    /**
    * Show line items for document
    */
    protected function renderDocumentLineItems($pdf = false, $page_break_html = '', $inline_styles = array())
    {
        $this->pdf = $pdf;
        list ($style_th, $style_th_numeric, $style_th_left, $style_th_section_header,
        $style_shaded_row, $style_td, $style_td_left, $style_td_top, $style_td_top_left, $style_td_numeric,
        $style_td_numeric_left, $style_td_numeric_top, $style_td_numeric_top_left, $style_td_center, $style_detailed_desc, $style_subtotal_td,
        $style_subtotal_td_numeric, $style_total_td, $style_total_td_top, $style_total_td_numeric, $style_total_td_numeric_left,
        $style_total_td_numeric_top, $style_total_td_numeric_top_left) = $inline_styles;

        ob_start();
        ?>
        <div class="document-line-item-table-container">
            <table class="document-line-item-table" cellpadding="3" cellspacing="0" border="0" width="100%"><?php //padding and width specified like this for the sake of the PDF generator ?>
                <?php $this->renderHeadingRow(array($style_th_left, $style_th));

                $col_headers = ob_get_clean();
                echo $col_headers;

                $section_number = 0;
                $item_number = 0;
                $row = 0;
                foreach ($this->line_item_collection->sections as $section)
                {
                    $section_number++;
                    $section_name = $this->getSectionName($section);
                    if (strlen($section_name) > 0) {
                        ?>
                        <tr class="section-heading" id="section_heading_<?php echo $section_number; ?>">
                            <th colspan="<?php echo $this->getColumnCount(); ?>" style="<?php echo $style_th_section_header; ?>"><?php echo $section_name; ?></th>
                        </tr>
                        <?php
                    }
                    $page_break_markup = '';
                    foreach ($section->line_items as $line_item)
                    {
                        if (strlen($page_break_markup) > 0) {
                            echo $page_break_markup;
                            $page_break_markup = '';
                        }
                        $row = $row == 1 ? 0 : 1;
                        $item_number++;
                        $this->renderValueRow($line_item, $item_number, !$pdf && $row == 1 ? $style_shaded_row : '', array($style_td_left, $style_td_numeric, $style_td_center));

                        if ($line_item->page_break) {
                            $page_break_markup = '</table></div>';
                            $page_break_markup .= $page_break_html;
                            $page_break_markup .= $col_headers;
                        }
                    }
                    if (strlen($section_name) > 0) {
                        if ($section->discount_percent->value != 0) {
                            $this->renderSectionDiscountRow($section, $section_number, array($style_td_left, $style_td_numeric, $style_td_center));
                        }
                        if (count($this->line_item_collection->sections) > 1) {
                            $this->renderSectionTotalRow($section, array($style_subtotal_td, $style_subtotal_td_numeric));
                        }
                    }
                    if (strlen($page_break_markup) > 0) {
                        echo $page_break_markup;
                        $page_break_markup = '';
                    }
                }
                $this->renderTotalRow($inline_styles);
                ?>
            </table>
        </div>
        <?php
    }

    protected function renderHeadingRow($styles = array())
    {
        ?>
        <tr class="line_item_headings">
        <?php
        $this->renderHeadingColumns($styles);
        ?>
        </tr>
        <?php
    }

    protected function renderHeadingColumns($styles)
    {
        list($style_th_left, $style_th) = $styles;
        $this->renderColumnHeading('product_description', 'NBILL_PRT_DESC', 'main_headings', 'style="' . $style_th_left . '"');
        $this->renderColumnHeading('net_price_per_unit', 'NBILL_PRT_UNIT_PRICE', 'main_headings', 'style="' . $style_th . '"');
        $this->renderColumnHeading('no_of_units', 'NBILL_PRT_QUANTITY', 'main_headings', 'style="' . $style_th . '"');
        $this->renderColumnHeading('discount_amount', 'NBILL_PRT_DISCOUNT', 'main_headings', 'style="' . $style_th . '"');
        $this->renderColumnHeading('net_price_for_item', 'NBILL_PRT_NET_PRICE', 'main_headings', 'style="' . $style_th . '"');
        $this->renderColumnHeading('tax_for_item', strlen(@$this->document->tax_abbreviation) == 0 ? 'NBILL_PRT_VAT' : $this->document->tax_abbreviation, 'main_headings', 'style="' . $style_th . '"');
        $this->renderColumnHeading('shipping_for_item', 'NBILL_PRT_SHIPPING', 'main_headings', 'style="' . $style_th . '"');
        $this->renderColumnHeading('tax_for_shipping', sprintf($this->translator->parseTranslation($this->document->language, 'NBILL_PRT_SHIPPING_VAT', "template.common"), strlen(@$this->document->tax_abbreviation) > 0 ? $this->document->tax_abbreviation : $this->translator->parseTranslation($this->document->language, 'NBILL_PRT_VAT', "template.common")), 'main_headings', 'style="' . $style_th . '"');
        $this->renderColumnHeading('gross_price_for_item', 'NBILL_PRT_TOTAL', 'main_headings', 'style="' . $style_th . '"');
    }

    protected function renderValueRow(nBillLineItem $line_item, $item_number, $row_style, $styles = array())
    {
        ?>
        <tr class="line_item_values" id="line_item_<?php echo $item_number; ?>" style="<?php echo $row_style; ?>">
        <?php
        $this->renderColumnValues($line_item, $styles);
        ?>
        </tr>
        <?php
    }

    protected function renderColumnValues(nBillLineItem $line_item, $styles)
    {
        list($style_td_left, $style_td_numeric) = $styles;
        $description = $this->getFullDescription($line_item);
        $this->renderColumnValue('product_description', $description, '', 'style="' . $style_td_left . '"');
        $this->renderColumnValue('net_price_per_unit', $line_item->net_price_per_unit, '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('no_of_units', $line_item->no_of_units, '', 'style="' . $style_td_numeric . '"');
        $discount = $line_item->discount_amount->format();
        if (strlen($line_item->discount_description) > 0) {
            $discount .= ' (' . $line_item->discount_description . ')';
        }
        $this->renderColumnValue('discount_amount', $discount, '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('net_price_for_item', $line_item->net_price_for_item, '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('tax_for_item', $line_item->tax_for_item, '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('shipping_for_item', $line_item->shipping_for_item, '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('tax_for_shipping', $line_item->tax_for_shipping, '', 'style="' . $style_td_numeric . '"');
        $this->renderGrossPriceColumnValue($line_item, $styles);
    }

    protected function getFullDescription($line_item)
    {
        $description = $line_item->product_description;
        $stripped_detailed = trim(strip_tags($line_item->detailed_description));
        if (strlen($description) > 0 && strlen($stripped_detailed) > 0) {
            $description .= '<br />';
        }
        if (strlen($stripped_detailed) > 0) {
            $description .= '<div class="detailed_description"' . ($this->pdf ? 'style="padding-left:10px;"' : '') . '><p>' . $line_item->detailed_description . '</p></div>';
        }
        return $description;
    }

    protected function renderGrossPriceColumnValue(nBillLineItem $line_item, $styles = array())
    {
        list($style_td_left, $style_td_numeric) = $styles;
        $this->renderColumnValue('gross_price_for_item', $line_item->gross_price_for_item->format(), '', 'style="' . $style_td_numeric . '"');
    }

    protected function renderSectionDiscountRow(nBillLineItemsSection $section, $section_number, $styles = array())
    {
        ?>
        <tr class="line_item_section_discount" id="section_discount_<?php echo $section_number; ?>">
        <?php
        $this->renderSectionDiscountColumns($section, $styles);
        ?>
        </tr>
        <?php
    }

    protected function renderSectionDiscountColumns(nBillLineItemsSection $section, $styles = array())
    {
        list($style_td_left, $style_td_numeric) = $styles;
        $this->renderColumnValue('product_description', $section->discount_title, '', 'style="' . $style_td_left . '"');
        $this->renderColumnValue('net_price_per_unit', '', '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('no_of_units', '', '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('discount_amount', '', '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('net_price_for_item', $section->discount_net->makeNegative(), '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('tax_for_item', $section->discount_tax->makeNegative(), '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('shipping_for_item', '', '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('tax_for_shipping', '', '', 'style="' . $style_td_numeric . '"');
        $this->renderColumnValue('gross_price_for_item', $section->discount_gross->makeNegative(), '', 'style="' . $style_td_numeric . '"');
    }

    protected function renderSectionTotalRow(nBillLineItemsSection $section, $styles = array())
    {
        ?>
        <tr class="summary-total-row section-total">
        <?php
        $this->renderSectionTotalColumns($section, $styles);
        ?>
        </tr>
        <?php
    }

    protected function renderSectionTotalColumns(nBillLineItemsSection $section, $styles)
    {
        list($style_subtotal_td, $style_subtotal_td_numeric) = $styles;
        $this->renderColumnValue('product_description', sprintf(NBILL_DOC_SECTION_NAMED_SUBTOTAL, $this->getSectionName($section)), '', 'style="' . $style_subtotal_td . '"');
        $this->renderColumnValue('net_price_per_unit', '', '', 'style="' . $style_subtotal_td_numeric . '"');
        $this->renderColumnValue('no_of_units', '', '', 'style="' . $style_subtotal_td_numeric . '"');
        $this->renderColumnValue('discount_amount', '', '', 'style="' . $style_subtotal_td_numeric . '"');
        $this->renderColumnValue('net_price_for_item', $section->getNetTotal(), '', 'style="' . $style_subtotal_td_numeric . '"');
        $this->renderColumnValue('tax_for_item', $section->getItemTaxTotal(), '', 'style="' . $style_subtotal_td_numeric . '"');
        $this->renderColumnValue('shipping_for_item', $section->getShippingTotal(), '', 'style="' . $style_subtotal_td_numeric . '"');
        $this->renderColumnValue('tax_for_shipping', $section->getShippingTaxTotal(), '', 'style="' . $style_subtotal_td_numeric . '"');
        $this->renderColumnValue('gross_price_for_item', $section->getGrossTotal(), '', 'style="' . $style_subtotal_td_numeric . '"');
    }

    protected function renderTotalRow($styles = array())
    {
        ?>
        <tr class="summary-total-row grand-total">
        <?php
        $this->renderTotalColumns($styles);
        ?>
        </tr>
        <?php
    }

    protected function renderTotalColumns($styles)
    {
        list ($style_th, $style_th_numeric, $style_th_left, $style_th_section_header,
        $style_shaded_row, $style_td, $style_td_left, $style_td_top, $style_td_top_left, $style_td_numeric,
        $style_td_numeric_left, $style_td_numeric_top, $style_td_numeric_top_left, $style_td_center, $style_detailed_desc, $style_subtotal_td,
        $style_subtotal_td_numeric, $style_total_td, $style_total_td_top, $style_total_td_numeric) = $styles;

        $this->renderColumnValue('product_description', NBILL_INVOICE_ITEM_TOTALS, '', 'style="' . $style_total_td . '"');
        $this->renderColumnValue('net_price_per_unit', '', '', 'style="' . $style_total_td_numeric . '"');
        $this->renderColumnValue('no_of_units', '', '', 'style="' . $style_total_td_numeric . '"');
        $this->renderColumnValue('discount_amount', '', '', 'style="' . $style_total_td_numeric . '"');
        $this->renderColumnValue('net_price_for_item', $this->line_item_collection->getNetTotal(), '', 'style="' . $style_total_td_numeric . '"');
        $this->renderColumnValue('tax_for_item', $this->line_item_collection->getItemTaxTotal(), '', 'style="' . $style_total_td_numeric . '"');
        $this->renderColumnValue('shipping_for_item', $this->line_item_collection->getShippingTotal(), '', 'style="' . $style_total_td_numeric . '"');
        $this->renderColumnValue('tax_for_shipping', $this->line_item_collection->getShippingTaxTotal(), '', 'style="' . $style_total_td_numeric . '"');
        $this->renderColumnValue('gross_price_for_item', $this->line_item_collection->getGrossTotal(), '', 'style="' . $style_total_td_numeric . '"');
    }

    public function getColumnCount()
    {
        $col_count = 0;
        $col_count += $this->canHideColumn('product_description') ? 0 : 1;
        $col_count += $this->canHideColumn('net_price_per_unit') ? 0 : 1;
        $col_count += $this->canHideColumn('no_of_units') ? 0 : 1;
        $col_count += $this->canHideColumn('discount_amount') ? 0 : 1;
        $col_count += $this->canHideColumn('net_price_for_item') ? 0 : 1;
        $col_count += $this->canHideColumn('tax_for_item') ? 0 : 1;
        $col_count += $this->canHideColumn('shipping_for_item') ? 0 : 1;
        $col_count += $this->canHideColumn('tax_for_shipping') ? 0 : 1;
        $col_count += $this->canHideColumn('gross_price_for_item') ? 0 : 1;
        return $col_count;
    }
}