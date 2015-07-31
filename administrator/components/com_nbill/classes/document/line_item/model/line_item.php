<?php
class nBillLineItem
{
    /** @var int **/
    public $index = null;
    /** @var int **/
    public $id;
    /** @var string Type of Line Item (invoice, quote, credit, potentially others in future) **/
    public $type;
    /** @var int **/
    public $vendor_id;
    /** @var int **/
    public $document_id;
    /** @var int **/
    public $ordering;
    /** @var int **/
    public $entity_id;
    /** @var string **/
    public $nominal_ledger_code;
    /** @var string **/
    public $nominal_ledger_description;
    /** @var string **/
    public $product_description;
    /** @var string **/
    public $detailed_description;
    /** @var nBillINumberCurrency **/
    public $net_price_per_unit;
    /** @var nBillINumber **/
    public $no_of_units;
    /** @var nBillINumber **/
    public $discount_percentage;
    /** @var nBillINumberCurrency **/
    public $discount_amount;
    /** @var string **/
    public $discount_description;
    /** @var nBillINumberCurrency **/
    public $net_price_for_item;
    /** @var nBillINumber **/
    public $tax_rate_for_item;
    /** @var nBillINumberCurrency **/
    public $tax_for_item;
    /** @var nBillINumber **/
    public $product_shipping_units;
    /** @var int **/
    public $shipping_id;
    /** @var nBillINumberCurrency **/
    public $shipping_for_item;
    /** @var nBillINumber **/
    public $tax_rate_for_shipping;
    /** @var nBillINumberCurrency **/
    public $tax_for_shipping;
    /** @var nBillINumberCurrency **/
    public $gross_price_for_item;
    /** @var string **/
    public $product_code;
    /** @var boolean **/
    public $page_break;
    /** @var nBillCurrency **/
    public $currency;
    /** @var boolean **/
    public $electronic_delivery;

    /** @var nBillLineItemsSection **/
    protected $parent_section;
    /** @var nBillNumberFactory **/
    protected $number_factory;

    public function __construct(nBillNumberFactory $number_factory, nBillCurrency $currency, $type = 'invoice', nBillLineItemsSection $parent_section = null)
    {
        $this->parent_section = $parent_section;
        if ($this->parent_section) {
            $this->index = count($this->parent_section->line_items);
            $this->ordering = count($this->parent_section->line_items);
        }
        $this->number_factory = $number_factory;
        $this->currency = $currency;
        $this->type = $type;
        $this->product_shipping_units = $this->number_factory->createNumber(0, 'quantity');
        $this->net_price_per_unit = $this->number_factory->createNumberCurrency(0, $this->currency);
        $this->no_of_units = $this->number_factory->createNumber(1, 'quantity');
        $this->discount_percentage = $this->number_factory->createNumber(0);
        $this->discount_amount = $this->number_factory->createNumberCurrency(0, $this->currency);
        $this->net_price_for_item = $this->number_factory->createNumberCurrency(0, $this->currency);
        $this->tax_rate_for_item = $this->number_factory->createNumber(0, 'tax_rate');
        $this->tax_for_item = $this->number_factory->createNumberCurrency(0, $this->currency);
        $this->shipping_for_item = $this->number_factory->createNumberCurrency(0, $this->currency);
        $this->tax_rate_for_shipping = $this->number_factory->createNumber(0, 'tax_rate');
        $this->tax_for_shipping = $this->number_factory->createNumberCurrency(0, $this->currency);
        $this->gross_price_for_item = $this->number_factory->createNumberCurrency(0, $this->currency);
    }

    /**
    * @return nBillLineItemsSection
    */
    public function getParentSection()
    {
        return $this->parent_section;
    }

    /**
    * @param nBillLineItemsSection $section
    */
    public function setParentSection(nBillLineItemsSection $section)
    {
        $this->parent_section = $section;
    }

    /**
    * Having set unit price, quantity, tax rate, and net shipping, calling this method will recalculate all other values
    */
    public function reCalculateAll()
    {
        $this->getNetPriceForItem(true);
        $this->getTaxForItem(true);
        $this->getTaxForShipping(true);
        $this->getGrossForItem(true);
    }

    /**
    * @param boolean $recalculate Whether or not to force the value to be re-calculated (based on unit price * quantity)
    * @return nBillINumberCurrency
    */
    public function getNetPriceForItem($recalculate = false)
    {
        if ($recalculate || !isset($this->net_price_for_item)) {
            if ($this->net_price_per_unit->value != 0) {
                $quantity = $this->no_of_units->value ? $this->no_of_units->value : 1;
                $this->net_price_for_item->value = $this->net_price_per_unit->value * $quantity;
            } else {
                $this->net_price_for_item = $this->number_factory->createNumberCurrency(0, $this->currency);
            }
            $this->net_price_for_item = $this->net_price_for_item->subtractNumber($this->discount_amount);
        }
        return $this->net_price_for_item;
    }

    /**
    * @param boolean $recalculate Whether or not to force the value to be re-calculated (based on net price and tax rate)
    * @return nBillINumberCurrency
    */
    public function getTaxForItem($recalculate = false)
    {
        if ($recalculate || !isset($this->tax_for_item)) {
            if ($this->tax_rate_for_item->value != 0) {
                $this->tax_for_item->value = ($this->getNetPriceForItem()->value / 100) * $this->tax_rate_for_item->value;
            } else {
                $this->tax_for_item = $this->number_factory->createNumberCurrency(0, $this->currency);
            }
        }
        return $this->tax_for_item;
    }

    /**
    * @param boolean $recalculate Whether or not to force the value to be re-calculated (based on shipping price and shipping tax rate)
    * @return nBillINumberCurrency
    */
    public function getTaxForShipping($recalculate = false)
    {
        if ($recalculate || !isset($this->tax_for_shipping)) {
            if ($this->tax_rate_for_shipping->value != 0) {
                $this->tax_for_shipping->value = ($this->shipping_for_item->value / 100) * $this->tax_rate_for_shipping->value;
            } else {
                $this->tax_for_shipping = $this->number_factory->createNumberCurrency(0, $this->currency);
            }
        }
        return $this->tax_for_shipping;
    }

    /**
    * @return nBillINumberCurrency
    */
    public function getTotalTaxForItem($recalculate = false)
    {
        return $this->getTaxForItem()->addNumber($this->getTaxForShipping($recalculate));
    }

    /**
    * @return nBillINumberCurrency
    */
    public function getTotalNetForItem()
    {
        return $this->getNetPriceForItem()->addNumber($this->shipping_for_item);
    }

    /**
    * @param boolean $recalculate Whether or not to force the value to be re-calculated (based on sum of net and tax values)
    * @return nBillINumberCurrency
    */
    public function getGrossForItem($recalculate = false)
    {
        if ($recalculate || !isset($this->gross_price_for_item)) {
            if ($this->getNetPriceForItem()->value != 0) {
                $this->gross_price_for_item = $this->getNetPriceForItem()->addNumber($this->shipping_for_item)->addNumber($this->getTotalTaxForItem());
            } else {
                $this->gross_price_for_item = $this->number_factory->createNumberCurrency(0, $this->currency);
            }
        }
        return $this->gross_price_for_item;
    }
}