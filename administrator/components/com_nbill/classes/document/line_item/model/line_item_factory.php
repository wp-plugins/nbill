<?php
class nBillLineItemFactory
{
    /** @var nBillLineItemMapper **/
    protected $mapper = null;
    /** @var nBillLineItemService **/
    protected $service = null;
    /** @var nBillINumberFactory **/
    protected $number_factory;
    /** @var nBillConfiguration **/
    protected $config;

    public function __construct(nBillINumberFactory $number_factory, nBillConfiguration $config = null)
    {
        $this->number_factory = $number_factory;
        if (!$config) {
            $config = nBillConfigurationService::getInstance()->getConfig();
        }
        $this->config = $config;
    }

    /**
    * Static function not ideal, and probably shouldn't be in here!
    * @param mixed $document_type
    * @return nBillLineItemController
    */
    public static function createController($document_type = 'IN')
    {
        switch ($document_type)
        {
            case 'QU':
                $controller = new nBillQuoteLineItemController();
                break;
            case 'CR':
                $controller = new nBillCreditLineItemController();
                break;
            case 'IN':
            default:
                $controller = new nBillLineItemController();
                break;
        }
        return $controller;
    }

    /**
    * @param nBillINumberFactory $number_factory
    * @param nbf_database $db Required to load items from database, not required for mapping from a JSON string
    * @param nBillCurrencyService $currency_service Required to load items from database, not required for mapping from a JSON string
    * @return nBillLineItemMapper
    */
    public function createMapper(nbf_database $db = null, nBillCurrencyService $currency_service = null)
    {
        if ($this->mapper == null) {
            if ($db == null) {
                $db = nbf_cms::$interop->database;
            }
            $this->mapper = new nBillLineItemMapper($this, $this->number_factory, $db, $currency_service);
        }
        return $this->mapper;
    }

    public function createService(nBillLineItemMapper $mapper)
    {
        if ($this->service == null) {
            $this->service = new nBillLineItemService($mapper);
        }
        return $this->service;
    }

    /**
    * @return nBillLineItemsCollection
    */
    public function createLineItemsCollection(nBillCurrency $currency = null, $document_type = 'IN')
    {
        switch ($document_type)
        {
            case 'QU':
                $collection = new nBillQuoteLineItemsCollection($this, $this->number_factory, $currency);
                break;
            default:
                $collection = new nBillLineItemsCollection($this, $this->number_factory, $currency);
                break;
        }
        return $collection;
    }

    /**
    * @param nBillLineItemsCollection $parent_collection
    * @param string $document_type
    * @return nBillLineItemsSection
    */
    public function createLineItemsSection(nBillLineItemsCollection $parent_collection = null, $document_type = 'IN')
    {
        switch ($document_type)
        {
            case 'QU':
                $section = new nBillQuoteLineItemsSection($parent_collection, $this->number_factory);
                break;
            default:
                $section = new nBillLineItemsSection($parent_collection, $this->number_factory);
                break;
        }
        if ($parent_collection) {
            $section->attach($parent_collection);
        }
        return $section;
    }

    /**
    * @return nBillLineItem
    */
    public function createLineItem($type, nBillCurrency $currency, nBillLineItemsSection $parent_section = null)
    {
        $line_item = null;
        switch ($type)
        {
            case 'QU':
                $line_item = new nBillLineItemQuote($this->number_factory, $currency, $type, $parent_section);
                break;
            default:
                $line_item = new nBillLineItem($this->number_factory, $currency, $type, $parent_section);
                break;
        }

        $line_item->electronic_delivery = $this->config->default_electronic;
        return $line_item;
    }

    public function createLineItemView(nBillLineItemsCollection $line_items, $document_type = 'IN', $document = null, $editor = false)
    {
        switch ($document_type)
        {
            case 'QU':
                if ($editor) {
                    $view = new nBillQuoteLineItemEditorHtml($line_items, $document);
                } else {
                    $view = new nBillQuoteLineItemHtml($line_items, $document);
                }
                break;
            case 'IN':
            default:
                if ($editor) {
                    $view = new nBillLineItemEditorHtml($line_items, $document);
                } else {
                    $view = new nBillLineItemHtml($line_items, $document);
                }
                break;
        }
        return $view;
    }

    /**
    * @param nBillLineItemsSection $section
    * @param string $document_type
    * @return nBillLineItemSectionEditorHtml
    */
    public function createSectionEditorView(nBillLineItemsSection $section, $document_type = 'IN')
    {
        switch ($document_type)
        {
            case 'QU':
                $view = new nBillLineItemQuoteSectionEditorHtml($section);
                break;
            case 'IN':
            default:
                $view = new nBillLineItemSectionEditorHtml($section);
                break;
        }
        return $view;
    }

    public function createItemEditorView(nBillLineItem $item, nBillConfiguration $config, $document_type = 'IN')
    {
        switch ($document_type)
        {
            case 'QU':
                $view = new nBillItemEditorPopupQuoteHtml($item, $config, $document_type);
                break;
            case 'IN':
            default:
                $view = new nBillItemEditorPopupHtml($item, $config, $document_type);
                break;
        }
        return $view;
    }
}