<?php
class nBillItemEditorPopupHtml
{
    /** @var nBillLineItem **/
    protected $line_item;
    /** @var string **/
    protected $action;
    /** @var array **/
    protected $ledger_codes = array();
    /** @var nBillConfiguration **/
    protected $config;
    /** @var string **/
    protected $document_type = 'IN';
    /** @var array **/
    protected $shipping_methods = array();

    public function __construct(nBillLineItem $line_item, nBillConfiguration $config, $document_type = 'IN')
    {
        $this->line_item = $line_item;
        $this->config = $config;
        $this->document_type = $document_type;
    }

    public function showItemEditor($action='add_new_item', $ledger_codes = array(), $shipping_methods = array())
    {
        $this->action = $action;
        $this->ledger_codes = $ledger_codes;
        $this->shipping_methods = $shipping_methods;
        include(realpath(dirname(__FILE__)) . '/template/item_editor.php');
    }

    protected function renderFields()
    {
        $this->renderProductFields();
        $this->renderPriceFields();
        $this->renderShippingFields();
        $this->renderTotal();
    }

    protected function renderProductFields()
    {
        ?>
        <div class="nbill-widget-config-field">
            <div class="nbill-widget-config-floating-field">
                <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-item-code">
                    <label for="product_code"><?php echo NBILL_INVOICE_ITEM_CODE; ?></label>
                    <input type="text" name="product_code" id="product_code" value="<?php echo $this->line_item->product_code; ?>" autocomplete="off" onclick="_km(event,true,this.id,0);" onkeyup="_km(event,true,this.id,0);" />
                    <input type="hidden" name="product_shipping_units" id="product_shipping_units" value="<?php echo $this->line_item->product_shipping_units; ?>" />
                    <div id="product_code_ta" class="ta_div" style="display: none;"></div>
                    <div id="product_code_div" class="flist_div" style="display: none;" ></div>
                    <input type="button" id="btn_sku_lookup" value="<?php echo NBILL_SHOW_PRODUCT_LIST; ?>&#x25be;" class="button btn" onclick="var sku_div = document.getElementById('div_sku_list'); if(sku_div.children[0].tagName.toUpperCase()=='IMG'){submit_ajax_request('get_sku_list','vendor_id=' + document.getElementById('vendor_id').value + '&currency_code=' + document.getElementById('currency').value,function(content){document.getElementById('div_sku_list').innerHTML=content;extract_and_execute_js('div_sku_list', true);});} sku_div.style.display = sku_div.style.display=='none' ? 'block' : 'none';this.blur();" />
                </div>
                <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-item-name">
                    <label for="product_description"><?php echo NBILL_INVOICE_ITEM_NAME; ?></label>
                    <input type="text" name="product_description" id="product_description" value="<?php echo str_replace('"', '&quot;', $this->line_item->product_description); ?>" onclick="_km(event,true,this.id,0);" onkeyup="_km(event,true,this.id,0);" autocomplete="off" />
                    <div id="product_description_ta" class="ta_div" style="display: none;"></div>
                    <div id="product_description_div" class="flist_div" style="display: none;" ></div>
                </div>
                <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-item-description">
                    <label for="product_description">&nbsp;</label>
                    <input type="button" class="button btn" id="show_detail" value="&lt;&nbsp;&gt;" title="<?php echo NBILL_INVOICE_HTML_SHOW; ?>" onclick="var html_div = document.getElementById('div_detailed_desc'); html_div.style.display = html_div.style.display=='none' ? 'block' : 'none';this.blur();" />
                </div>
                <div class="nbill-widget-config-float-end"></div>
                <div id="div_sku_list" class="resizable" style="display:none;">
                    <img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/js/tinybox2/images/preload.gif" alt="Loading..." />
                </div>
                <div id="div_detailed_desc" style="display:none;">
                    <?php
                    echo nbf_cms::$interop->render_editor("detailed_description", "detailed_description", $this->line_item->detailed_description, 'style="width:500px;height:300px;"', true); ?>
                </div>
            </div>
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-item-ledger">
                <label for="nominal_ledger_code"><?php echo NBILL_INVOICE_ITEM_LEDGER; ?></label>
                <select name="nominal_ledger_code" id="nominal_ledger_code">
                <?php foreach ($this->ledger_codes as $ledger_code)
                {
                    ?>
                    <option value="<?php echo $ledger_code->code; ?>"<?php if ($ledger_code->code == $this->line_item->nominal_ledger_code) {echo ' selected="selected"';} ?>><?php echo $ledger_code->code . ' - ' . $ledger_code->description; ?></option>
                    <?php
                } ?>
                </select>
            </div>
            <div class="nbill-widget-config-float-end"></div>
        </div>
        <?php
    }

    protected function renderPriceFields()
    {
        ?>
        <div class="nbill-widget-config-field">
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-net-price">
                <label for="net_price_per_unit"><?php echo NBILL_INVOICE_ITEM_NET_PRICE; ?></label>
                <input type="text" name="net_price_per_unit" id="net_price_per_unit" class="decimal" value="<?php echo $this->line_item->net_price_per_unit->getEditableDecimal(); ?>" onchange="getCurrentLineItem().reCalculateItemTotals(true);" />
            </div>
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-quantity">
                <label for="no_of_units"><?php echo NBILL_INVOICE_ITEM_QTY; ?></label>
                <input type="text" name="no_of_units" id="no_of_units" class="numeric" value="<?php echo $this->line_item->no_of_units->getEditableDecimal(); ?>" onchange="getCurrentLineItem().reCalculateItemTotals(true);" />
            </div>

            <div class="nbill-widget-config-floating-field" id="discount_button">
                <label for="btn_discounts"><?php echo NBILL_SHOW_DISCOUNT_FIELDS; ?></label>
                <input type="button" id="btn_discounts" value="<?php echo $this->line_item->discount_amount->value != 0 ? '-':'+'; ?>" class="button btn" onclick="var discounts=document.getElementById('discount_container');discounts.style.display=discounts.style.display=='none'?'block':'none';this.value=this.value=='+'?'-':'+';this.blur();" />
            </div>
            <div class="nbill-widget-config-floating-field" id="discount_container" <?php echo $this->line_item->discount_amount->value != 0 ? '' : 'style="display:none;"'; ?>>
                <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-discount-desc">
                    <label for="discount_description"><?php echo NBILL_INVOICE_ITEM_DISCOUNT_DESC; ?></label>
                    <input type="text" name="discount_description" id="discount_description" value="<?php echo $this->line_item->discount_description; ?>" />
                </div>
                <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-discount-percent">
                    <label for="discount_percent"><?php echo NBILL_INVOICE_ITEM_DISCOUNT_PERCENT; ?></label>
                    <input type="text" name="discount_percentage" id="discount_percentage" class="decimal" value="<?php echo $this->line_item->discount_percentage->getEditableDecimal(); ?>" onchange="getCurrentLineItem().reCalculateItemTotals(true);" />
                </div>
                <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-discount-amount">
                    <label for="discount_amount"><?php echo NBILL_INVOICE_ITEM_DISCOUNT_AMOUNT; ?></label>
                    <input type="text" name="discount_amount" id="discount_amount" class="decimal" value="<?php echo $this->line_item->discount_amount->getEditableDecimal(); ?>" onchange="getCurrentLineItem().reCalculateItemTotals();" />
                </div>
            </div>

            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-total-net">
                <label for="net_price_for_item"><?php echo NBILL_INVOICE_ITEM_TOTAL_NET; ?></label>
                <input type="text" name="net_price_for_item" id="net_price_for_item" class="decimal" value="<?php echo $this->line_item->net_price_for_item->getEditableDecimal(); ?>" onchange="getCurrentLineItem().reCalculateItemTax();" />
            </div>
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-tax-rate">
                <label for="tax_rate_for_item"><?php echo NBILL_INVOICE_ITEM_TAX_RATE; ?></label>
                <input type="text" name="tax_rate_for_item" id="tax_rate_for_item" class="decimal" value="<?php echo $this->line_item->tax_rate_for_item->getEditableDecimal(); ?>" onchange="getCurrentLineItem().reCalculateItemTax();" />
            </div>
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-tax-amount">
                <label for="tax_for_item"><?php echo NBILL_INVOICE_ITEM_TAX; ?></label>
                <input type="text" name="tax_for_item" id="tax_for_item" class="decimal" value="<?php echo $this->line_item->tax_for_item->getEditableDecimal(); ?>" onchange="getCurrentLineItem().reCalculateGross();" />
            </div>
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-electronic-delivery">
                <div class="radio-caption"><?php echo NBILL_LINE_ITEM_ELECTRONIC_DELIVERY; ?>&nbsp;&nbsp;</div>
                <?php echo nbf_html::yes_or_no_options('electronic_delivery', '', $this->line_item->electronic_delivery); ?>
            </div>

            <div class="nbill-widget-config-float-end"></div>
        </div>
        <?php
    }

    protected function renderShippingFields()
    {
        ?>
        <div class="nbill-widget-config-field">
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-shipping-service">
                <label for="shipping_id"><?php echo NBILL_INVOICE_ITEM_SHIPPING_SERVICE; ?></label>
                <select name="shipping_id" id="shipping_id" onchange="getCurrentLineItem().reCalculateShipping();">
                <?php
                foreach ($this->shipping_methods as $shipping_method)
                {
                    ?>
                    <option value="<?php echo $shipping_method->id; ?>"<?php if ($shipping_method->id == $this->line_item->shipping_id) {echo ' selected="selected"';} ?>><?php echo $shipping_method->name; ?></option>
                    <?php
                } ?>
                </select>
            </div>
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-shipping-amount">
                <label for="shipping_for_item"><?php echo NBILL_INVOICE_ITEM_SHIPPING; ?></label>
                <input type="text" name="shipping_for_item" id="shipping_for_item" class="decimal" value="<?php echo $this->line_item->shipping_for_item->getEditableDecimal(); ?>" onchange="getCurrentLineItem().reCalculateShippingTax();" />
            </div>
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-shipping-tax-rate">
                <label for="tax_rate_for_shipping"><?php echo NBILL_INVOICE_ITEM_SHIPPING_TAX_RATE; ?></label>
                <input type="text" name="tax_rate_for_shipping" id="tax_rate_for_shipping" class="decimal" value="<?php echo $this->line_item->tax_rate_for_shipping->getEditableDecimal(); ?>" onchange="getCurrentLineItem().reCalculateShippingTax();" />
            </div>
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-shipping-tax">
                <label for="tax_for_shipping"><?php echo NBILL_INVOICE_ITEM_SHIPPING_TAX; ?></label>
                <input type="text" name="tax_for_shipping" id="tax_for_shipping" class="decimal" value="<?php echo $this->line_item->tax_for_shipping->getEditableDecimal(); ?>" onchange="getCurrentLineItem().reCalculateGross();" />
            </div>
            <div class="nbill-widget-config-float-end"></div>
        </div>
        <?php
    }

    protected function renderTotal()
    {
        ?>
        <div class="nbill-widget-config-field grand-total" id="nbill-admin-doc-gross-amount">
            <?php echo NBILL_INVOICE_ITEM_GROSS;?>
            <span id="total_gross"><?php echo $this->line_item->gross_price_for_item->getEditableDecimal(); ?></span>
        </div>
        <?php
    }

    protected function renderCancelButton()
    {
        ?>
        <a href="javascript:void(0);" class="widget-config-button" id="nbill_item_editor_cancel" name="cancel" onclick="TINY.box.hide();return false;"><?php echo NBILL_CANCEL; ?></a>
        <?php
    }

    protected function renderSubmitButton()
    {
        ?>
        <a href="javascript:void(0);" class="widget-config-button" id="nbill_item_editor_save" name="save_item" onclick="TINY.box.hide();<?php echo nbf_cms::$interop->get_editor_contents('detailed_description', 'detailed_description', true); ?>preSubmitLineItem();submitLineItemAjaxTask('<?php echo $this->action; ?>', getFormValues('nbill_item_editor'));return false;"><?php echo NBILL_SUBMIT; ?></a>
        <?php
    }
}