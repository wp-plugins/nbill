<?php
class nBillLineItemEditorHtml extends nBillLineItemHtml
{
    /** @var array **/
    protected $shipping_methods = array();

    /**
    * @param boolean $return_html Whether to return the HTML (or output it to the browser)
    */
    public function renderEditorSummary($return_html = false, $shipping_methods = array())
    {
        if(!$return_html) {
            $this->loadSupportingScripts();
        }
        $this->shipping_methods = $shipping_methods;
        $this->initJs();
        ob_start();
        $this->renderEditorTabs();
        $this->renderEditorLineItems();
        $this->endEditorTabs();
        $html = ob_get_clean();
        if ($return_html) {
            return $html;
        } else {
            include(dirname(__FILE__) . "/template/editor_summary.php");
        }
    }

    protected function loadSupportingScripts()
    {
        //Local javascript
        $js = file_get_contents(realpath(dirname(__FILE__) . '/js/editor_line_item.js'));
        $js .= "\n\n" . file_get_contents(realpath(dirname(__FILE__) . '/js/json2.js'));
        nbf_cms::$interop->add_html_header('<script type="text/javascript">' . $js . '</script>');

        //Lightbox styling and processing (same as home page widgets)
        nbf_cms::$interop->add_html_header('<link rel="stylesheet" type="text/css" href="' . nbf_cms::$interop->nbill_site_url_path . '/js/tinybox2/style.css" />');
        nbf_cms::$interop->add_html_header('<script type="text/javascript" src="' . nbf_cms::$interop->nbill_site_url_path . '/js/tinybox2/tinybox.js"></script>');
        nbf_cms::$interop->add_html_header('<link rel="stylesheet" type="text/css" href="' . nbf_cms::$interop->nbill_site_url_path . '/style/admin/widgets.css" />');
        nbf_cms::$interop->add_html_header('<script type="text/javascript" src="' . nbf_cms::$interop->nbill_site_url_path . '/js/widgets/config.js"></script>');
        nbf_cms::$interop->add_html_header('<script type="text/javascript" src="' . nbf_cms::$interop->nbill_site_url_path . '/js/widgets/js_in_ajax.js"></script>');

        //Ajax support
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.client.php");
    }

    protected function initJs()
    {
        ?>
        <script type="text/javascript">
        var shipping_methods = <?php echo json_encode($this->shipping_methods); ?>;
        </script>
        <?php
        $this->renderPrecisionVariables();
    }

    protected function renderPrecisionVariables()
    {
        ?>
        <script type="text/javascript">
        quantity_precision = <?php echo intval($this->config->precision_quantity); ?>;
        tax_rate_precision = <?php echo intval($this->config->precision_tax_rate); ?>;
        decimal_precision = <?php echo intval($this->config->precision_decimal); ?>;
        currency_precision = <?php echo intval($this->config->precision_currency); ?>;
        currency_total_precision = <?php echo intval($this->config->precision_currency_line_total); ?>;
        </script>
        <?php
    }

    /**
    * Start tab group for editor summary
    */
    protected function renderEditorTabs()
    {
        $this->line_tab_settings = new nbf_tab_group();
        $this->line_tab_settings->start_tab_group("line_item_settings");
        $this->line_tab_settings->add_tab_title("line_item_basic", NBILL_ADMIN_TAB_BASIC);
        $this->line_tab_settings->add_tab_title("line_item_advanced", NBILL_ADMIN_TAB_ADVANCED);
        $this->line_tab_settings->add_tab_title("top", '<a href="#" style="display:inline-block;float:right;">' . NBILL_INVOICE_SCROLL_TO_TOP . '</a>', 'return true;', '', 'plain_link');
    }

    /**
    * Show line items for document editor
    */
    protected function renderEditorLineItems()
    {
        //Basic tab content
        ob_start();
        $this->renderEditorBasicContent();
        $this->line_tab_settings->add_tab_content("line_item_basic", ob_get_clean());
        //Advanced tab content
        ob_start();
        $this->renderEditorAdvancedContent();
        $this->line_tab_settings->add_tab_content("line_item_advanced", ob_get_clean());
    }

    /**
    * End tab group for editor summary
    */
    protected function endEditorTabs()
    {
        $this->line_tab_settings->end_tab_group();
    }

    protected function renderSectionActionButtons($section)
    {
        ?>
        <div class="line-item-section-actions">
            <a href="javascript:void(0);" onclick="showBlankBox(submitLineItemAjaxTask('edit_section_break_popup', 'section_index=<?php echo $section->index; ?>', function(response){refreshPopup(response);document.getElementById('section_name').focus();}));return false;" title="<?php echo NBILL_DOC_SECTION_EDIT; ?>"><img alt="<?php echo NBILL_DOC_SECTION_EDIT; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file_edit.png" /></a>
            <a href="javascript:void(0);" onclick="submitLineItemAjaxTask('remove_section_break', 'section_index=<?php echo $section->index; ?>');return false;" title="<?php echo NBILL_DOC_SECTION_DELETE; ?>"><img alt="<?php echo NBILL_DOC_SECTION_DELETE; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file_delete.png" /></a>
        </div>
        <?php
    }

    protected function renderEditorBasicContent()
    {
        ?>
        <div class="rounded-table compact">
            <table class="summary-list">
                <?php
                $this->renderEditorBasicHeadingRow();
                $section_number = 0;
                $item_number = 0;
                foreach ($this->line_item_collection->sections as $section)
                {
                    $section_number++;
                    $section_name = $this->getSectionName($section);
                    if (strlen($section_name) > 0) {
                        ?>
                        <tr class="section-heading" id="section_heading_basic_<?php echo $section_number; ?>">
                            <th colspan="<?php echo $this->getEditorBasicColumnCount(); ?>">
                                <?php echo $section_name;
                                $this->renderSectionActionButtons($section); ?>
                            </th>
                        </tr>
                        <?php
                    }
                    foreach ($section->line_items as $line_item)
                    {
                        $item_number++;
                        $this->renderEditorBasicValueRow($line_item, $item_number);
                    }
                    if (strlen($section_name) > 0) {
                        if ($section->discount_percent->value && $section->discount_percent->value != 0) {
                            $this->renderEditorBasicSectionDiscountRow($section_number, $section);
                        }
                        if (count($this->line_item_collection->sections) > 1) {
                            $this->renderEditorBasicSectionTotalRow($section_name, $section);

                        }
                    }
                }
                $this->renderEditorBasicTotalRow();
                ?>
            </table>
        </div>
        <?php
    }

    protected function renderEditorAdvancedContent()
    {
        ?>
        <div class="rounded-table compact">
            <table class="summary-list">
                <?php
                $this->renderEditorAdvancedHeadingRow();

                $section_number = 0;
                $item_number = 0;
                foreach ($this->line_item_collection->sections as $section)
                {
                    $section_number++;
                    $section_name = strlen($section->section_name) > 0 ? $section->section_name : (count($this->line_item_collection->sections) > 1 ? (defined('NBILL_SECTION_OTHER') ? NBILL_SECTION_OTHER : 'Other') : '');

                    if (strlen($section_name) > 0) {
                        ?>
                        <tr class="section-heading" id="section_heading_advanced_<?php echo $section_number; ?>">
                            <th colspan="<?php echo $this->getEditorAdvancedColumnCount(); ?>">
                                <?php echo $section_name;
                                $this->renderSectionActionButtons($section); ?>
                            </th>
                        </tr>
                        <?php
                    }

                    foreach ($section->line_items as $line_item)
                    {
                        $item_number++;
                        $this->renderEditorAdvancedValueRow($item_number, $line_item);
                    }
                } ?>
            </table>
        </div>
        <?php
    }

    protected function renderEditorPageBreak($section_index, $item_index, $id_suffix, $colspan = 100)
    {
        ?>
        <tr class="nbill_tr_no_highlight" id="page_break_<?php echo $section_index; ?>_<?php echo $item_index . $id_suffix; ?>">
            <td colspan="<?php echo $colspan; ?>">
                <table class="page-break-table">
                    <tr class="nbill_tr_no_highlight">
                        <td class="page-break-td">
                            <div class="line-item-page-break">
                                <span>
                                    <?php echo NBILL_DOC_PAGE_BREAK; ?>
                                </span>
                            </div>
                        </td>
                        <td class="page-break-button-td">
                            <a href="javascript:void(0);" onclick="deletePageBreak(<?php echo $section_index; ?>, <?php echo $item_index; ?>);return false;" title="<?php echo NBILL_LINE_ITEM_REMOVE_PAGE_BREAK; ?>"><img width="16" height="16" alt="<?php echo NBILL_LINE_ITEM_REMOVE_PAGE_BREAK; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file_delete.png" /></a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php
    }

    protected function renderMoveButtons(nBillLineItem $line_item, $id_suffix)
    {
        $first_item = false;
        $last_item = false;
        $section = $line_item->getParentSection();
        if ($section->index == 0 && $line_item->index == 0) {
            $first_item = true;
        }
        if ($line_item->index == count($section->line_items) - 1 &&
                    $section->index == count($section->getParentCollection()->sections) - 1) {
            $last_item = true;
        }
        ?>
        <span class="nbill-line-item-move-buttons" onclick="var e = arguments[0]||window.event;if(e.stopPropagation){e.stopPropagation();}else{e.cancelBubble = true;}"><?php
        if (!$first_item) {
            ?> <a class="nbill-line-item-action-button" href="javascript:void(0);" onclick="submitLineItemAjaxTask('move_line_item_up', 'section_index=<?php echo $section->index; ?>&item_index=<?php echo $line_item->index; ?>');return false;" title="<?php echo NBILL_MOVE_UP; ?>"><img alt="<?php echo NBILL_MOVE_UP; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/move_up.png" /></a><?php
        } else {
            ?> <img alt="<?php echo NBILL_MOVE_UP; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/move_up_disabled.png" /><?php
        }
        if (!$last_item) {
            ?><a class="nbill-line-item-action-button" href="javascript:void(0);" onclick="submitLineItemAjaxTask('move_line_item_down', 'section_index=<?php echo $section->index; ?>&item_index=<?php echo $line_item->index; ?>');return false;" title="<?php echo NBILL_MOVE_DOWN; ?>"><img alt="<?php echo NBILL_MOVE_DOWN; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/move_down.png" /></a> <?php
        } else {
            ?><img alt="<?php echo NBILL_MOVE_DOWN; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/move_down_disabled.png" /> <?php
        } ?>
        </span><?php
    }

    protected function getEditorBasicColumnCount()
    {
        $col_count = 0;
        $col_count += $this->canHideColumn('item_number') ? 0 : 1;
        $col_count += $this->canHideColumn('product_code') ? 0 : 1;
        $col_count += $this->canHideColumn('product_description') ? 0 : 1;
        if ($this->canHideColumn('net_price_for_item') && $this->canHideColumn('tax_for_item')) {
            /*$col_count += $this->canHideColumn('net_price_per_unit') ? 0 : 1;
            $col_count += $this->canHideColumn('no_of_units') ? 0 : 1;*/
            $col_count += 2;
        } else {
            $col_count += $this->canHideColumn('net_price_for_item') ? 0 : 1;
            $col_count += $this->canHideColumn('tax_for_item') ? 0 : 1;
        }
        $col_count += $this->canHideColumn('shipping_for_item') ? 0 : 1;
        $col_count += $this->canHideColumn('tax_for_shipping') ? 0 : 1;
        $col_count += $this->canHideColumn('gross_price_for_item') ? 0 : 1;
        $col_count += $this->canHideColumn('action') ? 0 : 1;
        return $col_count;
    }

    protected function renderEditorBasicHeadingRow()
    {
        ?>
        <tr>
            <?php
            $this->renderEditorBasicHeadingColumns();
            ?>
        </tr>
        <?php
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
        $this->renderColumnHeading('action', NBILL_INVOICE_ITEM_ACTION);
    }

    protected function renderEditorBasicValueRow($line_item, $item_number)
    {
        ?>
        <tr id="line_item_basic_<?php echo $item_number; ?>" onclick="row_click('<?php echo $line_item->getParentSection()->index; ?>', <?php echo $line_item->index; ?>);">
        <?php
        $this->renderEditorBasicValueColumns($line_item, $item_number);
        if ($line_item->page_break) {
            $this->renderEditorPageBreak($line_item->getParentSection()->index, $line_item->index, '_basic', $this->getEditorBasicColumnCount());
        }
        ?>
        </tr>
        <?php
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
        $this->renderColumnValue('gross_price_for_item', $line_item->gross_price_for_item, 'numeric');
        $can_section_break = !($line_item->index==count($line_item->getParentSection()->line_items)-1);
        $id_suffix = '_basic';
        ob_start(); include(dirname(__FILE__) . '/template/editor_summary_items_action_buttons.php'); $action_buttons = ob_get_clean();
        $this->renderColumnValue('action', $action_buttons, 'line-item-action-buttons');
    }

    protected function createEditableField($field_name, nBillLineItem $line_item, $value = '', $display_value = null)
    {
        if ($display_value === null) {
            $display_value = $value;
        }
        $value = str_replace('"', '&quot;', $value);
        ob_start();
        $section_index = $line_item->getParentSection()->index;
        $item_index = $line_item->index;
        ?>
        <input class="inline-editable<?php if ($field_name == 'product_description') {echo ' product-description';} ?>" type="text" name="<?php echo "editable_" . $section_index . "_" . $item_index . "_" . $field_name; ?>" id="<?php echo "editable_" . $section_index . "_" . $item_index . "_" . $field_name; ?>" value="<?php echo $value; ?>" onchange="inlineUpdate('<?php echo $field_name;?>', <?php echo $section_index; ?>, <?php echo $item_index; ?>, this.value);" />
        <span class="inline-editable-read-only"><?php echo $display_value; ?></span>
        <?php
        return ob_get_clean();
    }

    protected function renderEditorBasicSectionDiscountRow($section_number, $section)
    {
        ?>
        <tr id="section_discount_<?php echo $section_number; ?>">
            <?php
            $this->renderEditorBasicSectionDiscountColumns($section);
            ?>
        </tr>
        <?php
    }

    protected function renderEditorBasicSectionDiscountColumns($section)
    {
        $this->renderColumnValue('item_number', '-', 'responsive-cell high-priority');
        $this->renderColumnValue('product_code', '', 'responsive-cell optional');
        $this->renderColumnValue('product_description', $section->discount_title);
        if ($this->canHideColumn('net_price_for_item') && $this->canHideColumn('tax_for_item')) {
            $this->renderColumnValue('net_price_per_unit', '', 'numeric responsive-cell high-priority', '', true);
            $this->renderColumnValue('no_of_units', '', 'numeric responsive-cell high-priority', '', true);
        } else {
            $this->renderColumnValue('net_price_for_item', $section->discount_net->makeNegative(), 'numeric responsive-cell high-priority');
            $this->renderColumnValue('tax_for_item', $section->discount_tax->makeNegative(), 'numeric responsive-cell high-priority');
        }
        $this->renderColumnValue('shipping_for_item', '', 'responsive-cell optional');
        $this->renderColumnValue('tax_for_shipping', '', 'responsive-cell optional');
        $this->renderColumnValue('gross_price_for_item', $section->discount_gross->makeNegative(), 'numeric');
        $this->renderColumnValue('action', '');
    }

    protected function renderEditorBasicSectionTotalRow($section_name, $section)
    {
        ?>
        <tr class="summary-total-row">
            <?php
            $this->renderEditorBasicSectionTotalColumns($section_name, $section);
            ?>
        </tr>
        <?php
    }

    protected function renderEditorBasicSectionTotalColumns($section_name, $section)
    {
        $this->renderColumnValue('item_number', '', 'responsive-cell high-priority');
        $this->renderColumnValue('product_code', '', 'responsive-cell optional');
        $this->renderColumnValue('product_description', sprintf(NBILL_DOC_SECTION_NAMED_SUBTOTAL, $section_name));
        if ($this->canHideColumn('net_price_for_item') && $this->canHideColumn('tax_for_item')) {
            $this->renderColumnValue('net_price_per_unit', '<span class="responsive-cell optional">&nbsp;</span>', 'numeric responsive-cell high-priority', '', true);
            $this->renderColumnValue('no_of_units', '<span class="responsive-cell optional">&nbsp;</span>', 'numeric responsive-cell high-priority', '', true);
        } else {
            $this->renderColumnValue('net_price_for_item', '<span class="responsive-cell optional">' . $section->getNetTotal() . '</span><span class="responsive-cell inverse-optional">' . $section->getNetTotal()->addNumber($section->getShippingTotal()) . '</span>', 'numeric responsive-cell high-priority');
            $this->renderColumnValue('tax_for_item', '<span class="responsive-cell optional">' . $section->getItemTaxTotal() . '</span><span class="responsive-cell inverse-optional">' . $section->getTaxTotal() . '</span>', 'numeric responsive-cell high-priority');
        }
        $this->renderColumnValue('shipping_for_item', $section->getShippingTotal(), 'responsive-cell optional numeric');
        $this->renderColumnValue('tax_for_shipping', $section->getShippingTaxTotal(), 'responsive-cell optional numeric');
        $this->renderColumnValue('gross_price_for_item', $section->getGrossTotal(), 'numeric');
        $this->renderColumnValue('action', '');
    }

    protected function renderEditorBasicTotalRow()
    {
        ?>
        <tr class="summary-total-row">
            <?php
            $this->renderEditorBasicTotalColumns();
            ?>
        </tr>
        <?php
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
        ob_start();
        $this->renderAddNewButton();
        $add_button = ob_get_clean();
        $this->renderColumnValue('action', $add_button);
    }

    protected function renderAddNewButton()
    {
        ?>
        <input type="button" class="btn button" name="add_new_line_item" id="add_new_line_item" value="<?php echo NBILL_LINE_ITEM_ADD; ?>" onclick="showBlankBox(submitLineItemAjaxTask('edit_item_popup', 'section_index=<?php $section_index = count($this->line_item_collection->sections) - 1;echo $section_index; ?>&item_index=<?php echo count($this->line_item_collection->sections[$section_index]->line_items); ?>&default_tax_rate=' + document.getElementById('default_tax_rate').value, function(response){refreshPopup(response);extract_and_execute_js('nbill_item_editor', true);}));return false;" />
        <?php
    }

    protected function getEditorAdvancedColumnCount()
    {
        return $this->line_item_collection->getDiscountsPresent() ? 10 : 8;
    }

    protected function renderEditorAdvancedHeadingRow()
    {
        ?>
        <tr>
        <?php
        $this->renderEditorAdvancedHeadingColumns();
        ?>
        </tr>
        <?php
    }

    protected function renderEditorAdvancedHeadingColumns()
    {
        ?>
        <th class="responsive-cell high-priority nbill-admin-th-doc-item-no">#</th>
        <th class="responsive-cell high-priority nbill-admin-doc-th-item-code"><?php echo NBILL_INVOICE_ITEM_CODE; ?></th>
        <th class="responsive-cell priority nbill-admin-doc-th-item-name"><?php echo NBILL_INVOICE_ITEM_NAME; ?></th>
        <th class="responsive-cell optional nbill-admin-doc-th-item-ledger"><?php echo NBILL_INVOICE_ITEM_LEDGER; ?></th>
        <?php
        if (!$this->canHideColumn('net_price_for_item') || !$this->canHideColumn('tax_for_item')) { ?>
            <th class="numeric nbill-admin-doc-th-net-price"><?php echo NBILL_INVOICE_ITEM_NET_PRICE; ?></th>
            <th class="nbill-admin-doc-th-quantity"><?php echo NBILL_INVOICE_ITEM_QTY; ?></th>
            <?php
        }
        if ($this->line_item_collection->getDiscountsPresent()) { ?>
            <th class="responsive-cell optional nbill-admin-doc-th-discount-desc"><?php echo NBILL_INVOICE_ITEM_DISCOUNT_DESC; ?></th>
            <th class="numeric nbill-admin-doc-th-discount-amount"><?php echo NBILL_INVOICE_ITEM_DISCOUNT_AMOUNT; ?></th>
        <?php } ?>
        <th class="responsive-cell optional nbill-admin-doc-th-electronic-delivery"><?php echo NBILL_LINE_ITEM_ELECTRONIC_DELIVERY; ?></th>
        <th class="nbill-admin-doc-th-action"><?php echo NBILL_INVOICE_ITEM_ACTION; ?></th>
        <?php
    }

    protected function renderEditorAdvancedValueRow($item_number, nBillLineItem $line_item)
    {
        ?>
        <tr id="line_item_advanced_<?php echo $item_number; ?>" onclick="row_click('<?php echo $line_item->getParentSection()->index; ?>', <?php echo $line_item->index; ?>);">
        <?php
            $this->renderEditorAdvancedValueColumns($item_number, $line_item);
            if ($line_item->page_break) {
                $this->renderEditorPageBreak($line_item->getParentSection()->index, $line_item->index, '_advanced', $this->getEditorAdvancedColumnCount());
            }
        ?>
        </tr>
        <?php
    }

    protected function renderEditorAdvancedValueColumns($item_number, nBillLineItem $line_item)
    {
        ob_start();
        $this->renderMoveButtons($line_item, '_advanced');
        $up_down_buttons = ob_get_clean();
        ?>
        <td class="responsive-cell high-priority nbill-admin-doc-td-item-number"><?php echo $up_down_buttons . $item_number; ?></td>
        <td class="responsive-cell high-priority nbill-admin-doc-td-item-code"><?php echo $line_item->product_code; ?></td>
        <td class="responsive-cell priority nbill-admin-doc-td-item-description"><?php echo $line_item->product_description; ?></td>
        <td class="responsive-cell optional nbill-admin-doc-td-item-ledger"><?php echo $line_item->nominal_ledger_code; if (strlen($line_item->nominal_ledger_description) > 0) { ?> (<?php echo $line_item->nominal_ledger_description; ?>)<?php } ?></td>
        <?php
        if (!$this->canHideColumn('net_price_for_item') || !$this->canHideColumn('tax_for_item')) {
            ?>
            <td class="numeric nbill-admin-doc-td-net-price"><?php echo $this->createEditableField('net_price_per_unit', $line_item, $line_item->net_price_per_unit->getEditableDecimal()->format(), $line_item->net_price_per_unit->format()); ?></td>
            <td class="nbill-admin-doc-td-no-of-units"><?php echo $this->createEditableField('no_of_units', $line_item, $line_item->no_of_units->getEditableDecimal()->format(), $line_item->no_of_units->format()); ?></td>
            <?php
        }
        if ($this->line_item_collection->getDiscountsPresent()) { ?>
            <td class="responsive-cell optional nbill-admin-doc-td-discount-desc"><?php echo $line_item->discount_description; ?></td>
            <td class="numeric nbill-admin-doc-td-discount-amount"><?php echo $line_item->discount_amount->format(); ?></td>
        <?php } ?>
        <td class="responsive-cell optional nbill-admin-doc-td-electronic-delivery"><?php echo $line_item->electronic_delivery ? NBILL_YES : NBILL_NO; ?></td>

        <td class="nbill-admin-doc-td-action">
            <?php
            $can_section_break = !($line_item->index==count($line_item->getParentSection()->line_items)-1);
            $id_suffix = '_advanced';
            include(dirname(__FILE__) . '/template/editor_summary_items_action_buttons.php');
            ?>
        </td>
        <?php
    }
}