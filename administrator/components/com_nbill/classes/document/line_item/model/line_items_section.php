<?php
/**
* @property array $line_items
*/
class nBillLineItemsSection implements JsonSerializable, SplSubject
{
    /** @var int **/
    public $index = null;
    /** @var string **/
    public $section_name = '';
    /** @var string **/
    public $discount_title = '';
    /** @var nBillINumberDecimal **/
    public $discount_percent;
    /** @var nBillINumberCurrency **/
    public $discount_net;
    /** @var nBillINumberCurrency **/
    public $discount_tax;
    /** @var nBillINumberCurrency **/
    public $discount_gross;

    /** @var array **/
    protected $line_items = array();
    /** @var nBillINumberCurrency **/
    protected $net_total;
    /** @var nBillINumberCurrency **/
    protected $shipping_total;
    /** @var nBillINumberCurrency **/
    protected $item_tax_total;
    /** @var nBillINumberCurrency **/
    protected $shipping_tax_total;
    /** @var nBillINumberCurrency **/
    protected $tax_total;
    /** @var nBillINumberCurrency **/
    protected $gross_total;

    /** @var nBillNumberFactory **/
    protected $number_factory;
    /** @var nBillLineItemsCollection **/
    protected $parent_collection;

    /** @var array **/
    protected $observers = array();

    public function __construct(nBillLineItemsCollection $collection = null, nBillNumberFactory $number_factory)
    {
        $this->parent_collection = $collection;
        $this->number_factory = $number_factory;
        $this->discount_percent = $number_factory->createNumber(0);
        if ($this->parent_collection) {
            $this->index = count($this->parent_collection->sections);
            $this->discount_net = $number_factory->createNumberCurrency(0, $this->parent_collection->getCurrency());
            $this->discount_tax = $number_factory->createNumberCurrency(0, $this->parent_collection->getCurrency());
            $this->discount_gross = $number_factory->createNumberCurrency(0, $this->parent_collection->getCurrency());
            $this->discount_net->setIsLineTotal(true);
            $this->discount_tax->setIsLineTotal(true);
            $this->discount_gross->setIsLineTotal(true);
        }
    }

    public function __set($property, $value)
    {
        switch ($property)
        {
            case 'line_items':
                //Reset index of each item
                $this->line_items = $value;
                for ($index = 0; $index < count($this->line_items); $index++)
                {
                    $this->line_items[$index]->index = $index;
                }
                break;
        }
    }

    public function &__get($property)
    {
        switch ($property)
        {
            case 'line_items':
                return $this->line_items;
            default:
                $value = null;
                return $value; //Returned by reference
        }
    }

    public function jsonSerialize()
    {
        return array(
            'index' => $this->index,
            'section_name' => $this->section_name,
            'discount_title' => $this->discount_title,
            'discount_percent' => $this->discount_percent,
            'discount_net' => $this->discount_net,
            'discount_tax' => $this->discount_tax,
            'discount_gross' => $this->discount_gross,
            'line_items' => $this->line_items
        );
    }

    public function attach(SplObserver $observer)
    {
        $id = spl_object_hash($observer);
        $this->observers[$id] = $observer;
    }

    public function detach(SplObserver $observer)
    {
        $id = spl_object_hash($observer);
        unset($this->observers[$id]);
    }

    public function notify($event_name = null)
    {
        foreach($this->observers as $observer)
        {
            $observer->update($this, $event_name);
        }
    }

    public function getParentCollection()
    {
        return $this->parent_collection;
    }

    public function getDiscountsPresent()
    {
        foreach ($this->line_items as $line_item)
        {
            if ($line_item->discount_amount->value != 0 || strlen($line_item->discount_description) > 0) {
                return true;
            }
        }
        return false;
    }

    public function getNetTotal($force_recalculate = false)
    {
        if (!isset($this->net_total)) {
            $this->calculateTotals($force_recalculate);
        }
        return $this->net_total;
    }

    public function getItemTaxTotal($force_recalculate = false)
    {
        if (!isset($this->item_tax_total)) {
            $this->calculateTotals($force_recalculate);
        }
        return $this->item_tax_total;
    }

    public function getShippingTotal($force_recalculate = false)
    {
        if (!isset($this->shipping_total)) {
            $this->calculateTotals($force_recalculate);
        }
        return $this->shipping_total;
    }

    public function getShippingTaxTotal($force_recalculate = false)
    {
        if (!isset($this->shipping_tax_total)) {
            $this->calculateTotals($force_recalculate);
        }
        return $this->shipping_tax_total;
    }

    public function getTaxTotal($force_recalculate = false)
    {
        if (!isset($this->tax_total)) {
            $this->calculateTotals($force_recalculate);
        }
        return $this->tax_total;
    }

    public function getGrossTotal($force_recalculate = false)
    {
        if (!isset($this->gross_total)) {
            $this->calculateTotals($force_recalculate);
        }
        return $this->gross_total;
    }

    public function calculateTotals($force_recalculate = false)
    {
        $this->net_total = $this->number_factory->createNumberCurrency(0, $this->parent_collection->getCurrency());
        $this->net_total->setIsGrandTotal(true);
        $this->item_tax_total = $this->number_factory->createNumberCurrency(0, $this->parent_collection->getCurrency());
        $this->item_tax_total->setIsGrandTotal(true);
        $this->shipping_total = $this->number_factory->createNumberCurrency(0, $this->parent_collection->getCurrency());
        $this->shipping_total->setIsGrandTotal(true);
        $this->shipping_tax_total = $this->number_factory->createNumberCurrency(0, $this->parent_collection->getCurrency());
        $this->shipping_tax_total->setIsGrandTotal(true);
        $this->tax_total = $this->number_factory->createNumberCurrency(0, $this->parent_collection->getCurrency());
        $this->tax_total->setIsGrandTotal(true);
        $this->gross_total = $this->number_factory->createNumberCurrency(0, $this->parent_collection->getCurrency());
        $this->gross_total->setIsGrandTotal(true);

        foreach ($this->line_items as $line_item)
        {
            $this->net_total = $this->net_total->addNumber($line_item->getNetPriceForItem($force_recalculate));
            $this->item_tax_total = $this->item_tax_total->addNumber($line_item->getTaxForItem($force_recalculate));
            $this->shipping_total = $this->shipping_total->addNumber($line_item->shipping_for_item);
            $this->shipping_tax_total = $this->shipping_tax_total->addNumber($line_item->getTaxForShipping($force_recalculate));
            $this->tax_total = $this->tax_total->addNumber($line_item->getTotalTaxForItem($force_recalculate));
        }
        $this->gross_total = $this->net_total->addNumber($this->shipping_total)->addNumber($this->tax_total);
        $this->calculateSectionDiscount();
        $this->applySectionDiscount();
    }

    public function calculateSectionDiscount()
    {
        if ($this->discount_percent->value != 0)
        {
            $this->discount_net = $this->number_factory->createNumberCurrency(0, $this->parent_collection->getCurrency());
            $this->discount_tax = $this->number_factory->createNumberCurrency(0, $this->parent_collection->getCurrency());
            foreach ($this->line_items as $line_item)
            {
                $item_discount_net = $this->number_factory->createNumberCurrency(($line_item->getNetPriceForItem()->value / 100) * $this->discount_percent->value, $this->parent_collection->getCurrency());
                $item_discount_tax = $this->number_factory->createNumberCurrency(($line_item->getTaxForItem()->value / 100) * $this->discount_percent->value, $this->parent_collection->getCurrency());
                $this->discount_net = $this->discount_net->addNumber($item_discount_net);
                $this->discount_tax = $this->discount_tax->addNumber($item_discount_tax);
            }
            $this->discount_gross = $this->discount_net->addNumber($this->discount_tax);
        }
    }

    /**
    * @param nBillINumberCurrency $total
    * @param string $type
    * @return nBillINumberCurrency
    */
    protected function applySectionDiscount()
    {
        $this->net_total = $this->net_total->subtractNumber($this->discount_net);
        $this->item_tax_total = $this->item_tax_total->subtractNumber($this->discount_tax);
        $this->tax_total = $this->item_tax_total->addNumber($this->shipping_tax_total);
        $this->gross_total = $this->gross_total->subtractNumber($this->discount_gross);
    }

    public function removeItem($index)
    {
        unset($this->line_items[$index]);
        $this->line_items = array_values($this->line_items);
        //Close any gaps in the ordering
        for ($i=0; $i < count($this->line_items); $i++)
        {
            $this->line_items[$i]->index = $i;
        }
        $this->notify('item_removed');
    }
}