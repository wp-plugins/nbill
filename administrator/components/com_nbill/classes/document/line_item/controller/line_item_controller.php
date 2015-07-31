<?php
class nBillLineItemController
{
    /** @var int **/
    protected $vendor_id;
    /** @var string **/
    protected $document_type;
    /** @var string **/
    protected $country_code;
    /** @var string **/
    protected $currency_code;
    /** @var int **/
    protected $section_index;
    /** @var int **/
    protected $item_index;
    /** @var string **/
    protected $line_items_json;
    /** @var nBillConfiguration **/
    protected $config;
    /** @var nBillNumberFactory **/
    protected $number_factory;
    /** @var nBillCurrencyFactory **/
    protected $currency_factory;
    /** @var nBillCurrencyMapper **/
    protected $currency_mapper;
    /** @var nBillCurrencyService **/
    protected $currency_service;
    /** @var nBillLineItemFactory **/
    protected $line_item_factory;
    /** @var nBillLineItemMapper **/
    protected $line_item_mapper;
    /** @var nBillLineItemCollection **/
    protected $item_collection;
    /** @var string **/
    protected $selected_tab_id;
    /** @var nBillNominalLedgerFactory **/
    protected $ledger_factory;
    /** @var nBillNominalLedgerMapper **/
    protected $ledger_mapper;
    /** @var nBillNominalLedgerService **/
    protected $ledger_service;
    /** @var nBillShippingFactory **/
    protected $shipping_factory;
    /** @var nBillShippingMapper **/
    protected $shipping_mapper;
    /** @var nBillShippingService **/
    protected $shipping_service;
    /** @var nBillProductService **/
    protected $product_service;
    /** @var nBillProductFactory **/
    protected $product_factory;
    /** @var nBillTaxService **/
    protected $tax_service;

    /** @var string **/
    protected $section_name;
    /** @var string **/
    protected $section_discount_title;
    /** @var decimal **/
    protected $section_discount_percent;
    /** @var int **/
    protected $client_id;
    /** @var int **/
    protected $product_id;
    /** @var string **/
    protected $tax_exemption_code;
    /** @var boolean **/
    protected $force_recalculate = false;

    public function __construct()
    {
        $this->extractRequestParameters();
        $this->loadLanguage();
        $this->initialise();
    }

    protected function extractRequestParameters()
    {
        $this->document_type = nbf_common::get_param($_REQUEST, 'document_type');
        $this->currency_code = nbf_common::get_param($_REQUEST, 'currency_code');
        $this->country_code = nbf_common::get_param($_REQUEST, 'country_code');
        $this->section_index = intval(nbf_common::get_param($_REQUEST, 'section_index'));
        $this->item_index = intval(nbf_common::get_param($_REQUEST, 'item_index'));
        $this->line_items_json = nbf_common::get_param($_REQUEST, 'line_items', '', true, false, true, true);
        $this->selected_tab_id = nbf_common::get_param($_REQUEST, 'selected_tab');
        $this->section_name = nbf_common::get_param($_REQUEST, 'section_name');
        $this->section_discount_title = nbf_common::get_param($_REQUEST, 'section_discount_title');
        $this->section_discount_percent = nbf_common::get_param($_REQUEST, 'section_discount_percent');
        $this->vendor_id = intval(nbf_common::get_param($_REQUEST, 'vendor_id'));
        $this->product_id = intval(nbf_common::get_param($_REQUEST, 'product_id'));
        $this->client_id = intval(nbf_common::get_param($_REQUEST, 'client_id'));
        $this->tax_exemption_code = nbf_common::get_param($_REQUEST, 'tax_exemption_code');
        $this->force_recalculate = nbf_common::get_param($_REQUEST, 'force_recalculate') ? true : false;
    }

    protected function loadLanguage()
    {
        nbf_common::load_language("invoices");
    }

    protected function initialise()
    {
        $this->config = nBillConfigurationService::getInstance()->getConfig();
        $this->number_factory = new nBillNumberFactory($this->config);
        $this->currency_factory = new nBillCurrencyFactory();
        $this->line_item_factory = new nBillLineItemFactory($this->number_factory);
        $this->line_item_mapper = $this->line_item_factory->createMapper();
        $this->currency_mapper = $this->currency_factory->createCurrencyMapper(nbf_cms::$interop->database);
        $this->currency_service = new nBillCurrencyService($this->currency_mapper);
        $this->item_collection = $this->line_item_mapper->mapLineItemsFromJson($this->line_items_json, $this->currency_mapper, $this->document_type, $this->currency_service->findCurrency($this->currency_code));
        if ($this->item_collection) {
            $this->item_collection->refreshSections($this->force_recalculate); //Recaluclate section discounts in case line item amounts have changed
        }
        $this->ledger_factory = new nBillNominalLedgerFactory();
        $this->ledger_mapper = new nBillNominalLedgerMapper(nbf_cms::$interop->database, $this->ledger_factory);
        $this->ledger_service = new nBillNominalLedgerService($this->ledger_mapper);
        $this->shipping_factory = new nBillShippingFactory($this->number_factory, $this->currency_service);
        $this->shipping_mapper = new nBillShippingMapper(nbf_cms::$interop->database, $this->shipping_factory, $this->number_factory, $this->ledger_factory, $this->currency_service);
        $this->shipping_service = new nBillShippingService($this->shipping_mapper);
        $this->product_factory = new nBillProductFactory($this->number_factory, new nBillPaymentFactory());
        $product_mapper = new nBillProductMapper(nbf_cms::$interop->database, $this->product_factory, $this->number_factory, $this->ledger_factory, $this->currency_service);
        $this->product_service = new nBillProductService($product_mapper);
        $tax_mapper = new nBillTaxMapper(nbf_cms::$interop->database, $this->number_factory);
        $this->tax_service = new nBillTaxService($tax_mapper, $this->config);
    }

    public function ajaxRemoveLineItem()
    {
        if ($this->item_collection) {
            if (count($this->item_collection->sections) > 0) {
                $this->item_collection->sections[$this->section_index]->removeItem($this->item_index);
            }
            $this->ajaxRenderEditorSummary();
        } else {
            //Error! Please refresh page
            echo NBILL_AJAX_GENERAL_ERROR;
        }
    }

    public function ajaxInsertPageBreak()
    {
        if ($this->item_collection) {
            if (count($this->item_collection->sections) > 0) {
                $line_item = $this->item_collection->sections[$this->section_index]->line_items[$this->item_index];
                $line_item->page_break = true;
                $this->ajaxRenderEditorSummary();
            }
        } else {
            //Error! Please refresh page
            echo NBILL_AJAX_GENERAL_ERROR;
        }
    }

    public function ajaxRemoveSectionBreak()
    {
        if ($this->item_collection) {
            $this->item_collection->removeSection($this->section_index);
            $this->ajaxRenderEditorSummary();
        } else {
            //Error! Please refresh page
            echo NBILL_AJAX_GENERAL_ERROR;
        }
    }

    protected function ajaxRenderEditorSummary($product_add = false, $product_update = false)
    {
        ob_start();
        $line_item_view = $this->line_item_factory->createLineItemView($this->item_collection, $this->document_type, null, true);
        $html = $line_item_view->renderEditorSummary(true, $this->shipping_service->getAllShippingMethods($this->currency_code));
        if (substr(nbf_cms::$interop->char_encoding, 0, 3) == 'iso') {
            header('Content-Type: text/html; charset=' . nbf_cms::$interop->char_encoding);
        }
        $new_json = json_encode($this->item_collection);
        echo $html . "#!#" . $new_json . '#!#' . $this->document_type . '#!#' . $this->selected_tab_id . '#!#' . ($product_add ? '1' : '0') . '#!#' . ($product_update ? '1' : '0');
        ob_flush();
    }

    public function ajaxInsertSectionBreakPopup()
    {
        $section = $this->line_item_factory->createLineItemsSection(null, $this->document_type);
        $section->section_name = $this->section_name;
        $section->discount_title = $this->section_discount_title;
        $section->discount_percent = $this->number_factory->createNumber($this->section_discount_percent);
        $view = $this->line_item_factory->createSectionEditorView($section, $this->document_type);
        $view->showSectionEditor();
    }

    public function ajaxInsertSectionBreak()
    {
        $new_section = $this->line_item_factory->createLineItemsSection($this->item_collection, $this->document_type);
        $new_section->section_name = $this->section_name;
        $new_section->discount_title = $this->section_discount_title;
        $new_section->discount_percent = $this->number_factory->createNumber($this->section_discount_percent);
        $this->item_collection->insertSection($new_section, $this->section_index, $this->item_index);
        $this->ajaxRenderEditorSummary();
    }

    public function ajaxEditSectionBreakPopup()
    {
        $section = $this->item_collection->sections[$this->section_index];
        $view = $this->line_item_factory->createSectionEditorView($section, $this->document_type);
        $view->showSectionEditor('save_section_break');
    }

    public function ajaxSaveSectionBreak()
    {
        $section =& $this->item_collection->sections[$this->section_index];
        $section->section_name = $this->section_name;
        $section->discount_title = $this->section_discount_title;
        $section->discount_percent = $this->number_factory->createNumber($this->section_discount_percent);
        $this->ajaxRenderEditorSummary();
    }

    public function ajaxMoveLineItemUp()
    {
        $this->item_collection->moveLineItemUp($this->section_index, $this->item_index);
        $this->ajaxRenderEditorSummary();
    }

    public function ajaxMoveLineItemDown()
    {
        $this->item_collection->moveLineItemDown($this->section_index, $this->item_index);
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
        $view->showItemEditor('save_item', $ledger_codes, $shipping_methods);
    }

    public function ajaxGetSkuList()
    {
        $category_tree = $this->product_service->getCategoryTree($this->vendor_id);
        $category_tree = $this->product_service->populateCategoryTreeWithProducts($category_tree, $this->vendor_id, $this->currency_code);
        $view = $this->product_factory->createProductListView($category_tree);
        $view->showProductList();
    }

    public function ajaxGetProduct()
    {
        $product = $this->product_service->loadProduct($this->product_id, $this->currency_code);
        $result = array();
        $result['product_code'] = $product->product_code;
        $result['name'] = $product->name;
        $result['description'] = $product->description;
        $result['nominal_ledger_code'] = $product->nominal_ledger->code;
        $result['is_taxable'] = $product->is_taxable;
        $result['use_custom_tax_rate'] = false;
        if ($product->custom_tax_rate > 0) {
            $result['use_custom_tax_rate'] = true;
        }
        $result['custom_tax_rate'] = $product->custom_tax_rate;
        $result['electronic_delivery'] = $product->electronic_delivery ? 1 : 0;
        $product_tax_rate = false;
        if ($product->electronic_delivery && $product->is_taxable && $product->custom_tax_rate == 0) {
            $product_tax_rate = $this->tax_service->getElectronicDeliveryRateForCountry($product->vendor_id, $this->country_code, $this->client_id, $this->tax_exemption_code);
        } else if (!$product->electronic_delivery && $product->is_taxable && $product->custom_tax_rate == 0) {
            $product_tax_rate = $this->tax_service->getNormalRateForCountry($product->vendor_id, $this->country_code, $this->client_id, $this->tax_exemption_code);
        }
        if ($product_tax_rate) {
            $result['custom_tax_rate'] = $product_tax_rate->tax_rate->getEditableDecimal()->format();
            $result['use_custom_tax_rate'] = true;
        }
        $result['shipping_units'] = $product->shipping_units;
        $result['setup_fee'] = '0.00';
        $result['net_price'] = '0.00';
        $result['payment_frequency'] = 'AA';
        foreach ($product->prices as $price)
        {
            if ($price->amount->value != 0) {
                $result['setup_fee'] = $price->setup_fee->getEditableDecimal()->format();
                $result['net_price'] = $price->amount->getEditableDecimal()->format();
                $result['payment_frequency'] = $price->payment_frequency->code;
                break;
            }
        }
        echo json_encode($result);
    }

    public function ajaxRefresh()
    {
        $this->ajaxRenderEditorSummary();
    }

    public function ajaxSaveItem()
    {
        //Check whether we need to prompt on product add/update
        $product_add = false;
        $product_update = false;
        if ($this->config->edit_products_in_documents) {
            $product_add = $this->wasProductAdded() ? $this->section_index . ':' . $this->item_index : '';
            if (!$product_add) {
                $product_update = $this->wasProductUpdated() ? $this->section_index . ':' . $this->item_index : '';
            }
        }

        //Refresh the items
        $this->ajaxRenderEditorSummary($product_add, $product_update);
    }

    protected function wasProductAdded()
    {
        $line_item = $this->item_collection->sections[$this->section_index]->line_items[$this->item_index];
        if ($line_item) {
            if (strlen($line_item->product_code) > 0) {
                if (!$this->product_service->productCodeExists($line_item->product_code)) {
                    return true;
                }
            }
        }
    }

    protected function wasProductUpdated()
    {
        $line_item = $this->item_collection->sections[$this->section_index]->line_items[$this->item_index];
        if ($line_item) {
            if (strlen($line_item->product_code) > 0) {
                if ($this->product_service->wasProductUpdated($this->vendor_id, $line_item->product_code, $line_item->nominal_ledger_code, $line_item->product_description, $line_item->detailed_description, $this->currency_code, $line_item->net_price_per_unit)) {
                    return true;
                }
            }
        }
    }
}