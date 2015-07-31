<?php
class nBillQuoteLineItemController extends nBillLineItemController
{
    /** @var nBillPaymentFactory **/
    protected $payment_factory;
    /** @var nBillPaymentMapper **/
    protected $payment_mapper;
    /** @var nBillPaymentService **/
    protected $payment_service;
    /** @var boolean **/
    protected $section_atomic;

    protected function loadLanguage()
    {
        parent::loadLanguage();
        nbf_common::load_language("quotes");
    }

    protected function initialise()
    {
        parent::initialise();
        $this->payment_factory = new nBillPaymentFactory();
        $this->payment_mapper = new nBillPaymentMapper(nbf_cms::$interop->database, $this->payment_factory);
        $this->payment_service = new nBillPaymentService($this->payment_mapper);
    }

    public function extractRequestParameters()
    {
        parent::extractRequestParameters();
        $this->section_atomic = intval(nbf_common::get_param($_REQUEST, 'quote_atomic'));
    }

    public function ajaxInsertSectionBreakPopup()
    {
        $section = $this->line_item_factory->createLineItemsSection(null, $this->document_type);
        $section->section_name = $this->section_name;
        $section->discount_title = $this->section_discount_title;
        $section->discount_percent = $this->number_factory->createNumber($this->section_discount_percent);
        $section->quote_atomic = $this->section_atomic;
        $view = $this->line_item_factory->createSectionEditorView($section, $this->document_type);
        $view->showSectionEditor();
    }

    public function ajaxInsertSectionBreak()
    {
        $new_section = $this->line_item_factory->createLineItemsSection($this->item_collection, $this->document_type);
        $new_section->section_name = $this->section_name;
        $new_section->discount_title = $this->section_discount_title;
        $new_section->discount_percent = $this->number_factory->createNumber($this->section_discount_percent);
        $new_section->quote_atomic = $this->section_atomic;
        $this->item_collection->insertSection($new_section, $this->section_index, $this->item_index);
        $this->ajaxRenderEditorSummary();
    }

    public function ajaxEditItemPopup()
    {
        if ($this->item_index == count($this->item_collection->sections[$this->section_index]->line_items)) {
            //Creating a new item
            $item = $this->line_item_factory->createLineItem($this->document_type, $this->item_collection->currency, $this->item_collection->sections[$this->section_index]);
            $item->tax_rate_for_item = $this->number_factory->createNumber(nbf_common::get_param($_REQUEST, 'default_tax_rate'), 'tax_rate');

        } else {
            $item = $this->item_collection->sections[$this->section_index]->line_items[$this->item_index];
        }
        $ledger_codes = $this->ledger_service->getAllLedgerCodes();
        $shipping_methods = $this->shipping_service->getAllShippingMethods($this->currency_code);
        $view = $this->line_item_factory->createItemEditorView($item, $this->config, $this->document_type);
        $view->showItemEditor('save_item', $ledger_codes, $shipping_methods, $this->payment_service->getPaymentFrequencies());
    }

    public function ajaxSaveSectionBreak()
    {
        $section =& $this->item_collection->sections[$this->section_index];
        $section->section_name = $this->section_name;
        $section->discount_title = $this->section_discount_title;
        $section->discount_percent = $this->number_factory->createNumber($this->section_discount_percent);
        $section->quote_atomic = $this->section_atomic ? true : false;
        $this->ajaxRenderEditorSummary();
    }

    protected function wasProductUpdated()
    {
        foreach ($this->item_collection->sections as $section)
        {
            foreach ($section->line_items as $line_item)
            {
                if (strlen($line_item->product_code) > 0) {
                    if ($this->product_service->wasProductUpdated($this->vendor_id, $line_item->product_code, $line_item->nominal_ledger_code, $line_item->product_description, $line_item->detailed_description, $this->currency_code, $line_item->net_price_per_unit, $line_item->quote_pay_freq)) {
                        return true;
                    }
                }
            }
        }
    }
}