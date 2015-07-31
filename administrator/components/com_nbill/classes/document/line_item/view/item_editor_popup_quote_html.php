<?php
class nBillItemEditorPopupQuoteHtml extends nBillItemEditorPopupHtml
{
    /** @var array **/
    protected $payment_frequencies = array();

    protected function renderFields()
    {
        $this->renderProductFields();
        $this->renderPriceFields();
        $this->renderShippingFields();
        $this->renderQuoteFields();
        $this->renderTotal();
    }

    public function showItemEditor($action='add_new_item', $ledger_codes = array(), $shipping_methods = array(), $payment_frequencies = array())
    {
        $this->payment_frequencies = $payment_frequencies;
        parent::showItemEditor($action, $ledger_codes, $shipping_methods);
    }

    protected function renderQuoteFields()
    {
        ?>
        <div class="nbill-widget-config-field">
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-quote-pay-freq">
                <label for="quote_pay_freq"><?php echo NBILL_QUOTE_PAY_FREQ; ?></label>
                <select name="quote_pay_freq" id="quote_pay_freq">
                <?php
                foreach ($this->payment_frequencies as $payment_frequency)
                {
                    ?>
                    <option value="<?php echo $payment_frequency->code; ?>"<?php if ($payment_frequency->code == $this->line_item->quote_pay_freq) {echo ' selected="selected"';} ?>><?php echo $payment_frequency->description; ?></option>
                    <?php
                }
                ?>
                </select>
            </div>
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-quote-relating-to">
                <label for="quote_relating_to"><?php echo NBILL_QUOTE_RELATING_TO; ?></label>
                <input type="text" name="quote_relating_to" id="quote_relating_to" class="inputbox" value="<?php echo str_replace('"', '&quot;', $this->line_item->quote_relating_to); ?>" />
            </div>
            <div class="nbill-widget-config-float-end"></div>
        </div>
        <div class="nbill-widget-config-field">
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-quote-auto-renew">
                <div class="radio-caption"><?php echo NBILL_QUOTE_AUTO_RENEW; ?>&nbsp;&nbsp;</div>
                <?php echo nbf_html::yes_or_no_options('quote_auto_renew', '', $this->line_item->quote_auto_renew); ?>
            </div>
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-quote-unique-invoice">
                <div class="radio-caption"><?php echo NBILL_QUOTE_UNIQUE_INVOICE; ?>&nbsp;&nbsp;</div>
                <?php echo nbf_html::yes_or_no_options('quote_unique_invoice', '', $this->line_item->quote_unique_invoice); ?>
            </div>
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-quote-mandatory">
                <div class="radio-caption"><?php echo NBILL_QUOTE_ITEM_MANDATORY; ?>&nbsp;&nbsp;</div>
                <?php echo nbf_html::yes_or_no_options('quote_mandatory', '', $this->line_item->quote_mandatory); ?>
            </div>
            <div class="nbill-widget-config-floating-field" id="nbill-admin-doc-quote-accepted">
                <div class="radio-caption"><?php echo NBILL_QUOTE_IS_ITEM_ACCEPTED; ?></div>
                <?php echo nbf_html::yes_or_no_options('quote_item_accepted', '', $this->line_item->quote_item_accepted); ?>
            </div>
            <div class="nbill-widget-config-float-end"></div>
        </div>
        <?php
    }
}
