<?php
/**
* @property array $sections
*/
class nBillLineItemsCollection implements JsonSerializable, SplObserver
{
    /** @var nBillCurrency **/
    public $currency;
    /** @var array **/
    protected $sections = array();
    /** @var nBillLineItemFactory **/
    protected $factory;
    /** @var nBillNumberFactory **/
    protected $number_factory;
    /** @var nBillINumberCurrency **/
    protected $net_total;
    /** @var nBillINumberCurrency **/
    protected $item_tax_total;
    /** @var nBillINumberCurrency **/
    protected $shipping_total;
    /** @var nBillINumberCurrency **/
    protected $shipping_tax_total;
    /** @var nBillINumberCurrency **/
    protected $tax_total;
    /** @var nBillINumberCurrency **/
    protected $gross_total;

    public function __construct(nBillLineItemFactory $factory, nBillNumberFactory $number_factory, nBillCurrency $currency = null)
    {
        $this->factory = $factory;
        $this->number_factory = $number_factory;
        $this->currency = $currency;
    }

    public function __set($property, $value)
    {
        switch ($property)
        {
            case 'sections':
                //Reset index of each section
                $this->sections = $value;
                for ($index = 0; $index < count($this->sections); $index++)
                {
                    $this->sections[$index]->index = $index;
                }
                break;
        }
    }

    public function &__get($property)
    {
        switch ($property)
        {
            case 'sections':
                return $this->sections;
            default:
                return null;
        }
    }

    public function jsonSerialize()
    {
        return array(
            'currency' => $this->currency,
            'sections' => $this->sections
        );
    }

    /**
    * @param SplSubject $subject
    * @param string $event_name
    */
    public function update(SplSubject $subject, $event_name = null)
    {
        if ($subject instanceof nBillLineItemsSection) {
            switch ($event_name)
            {
                case 'item_removed':
                default:
                    $this->refreshSections();
                    break;
            }
        }

    }

    public function removeSection($section_index)
    {
        if (count($this->sections) == 1) {
            //Can't completely remove the last section - just delete the name and any discount
            $this->sections[$section_index]->section_name = '';
            $this->sections[$section_index]->discount_title = '';
            $this->sections[$section_index]->discount_percent = $this->number_factory->createNumber(0);
        } else {
            //Move all items from the section into the next one available
            if (count($this->sections) > $section_index + 1) {
                //There is another one after this, we can add them to that
                $this->sections[$section_index + 1]->line_items = array_merge($this->sections[$section_index]->line_items, $this->sections[$section_index + 1]->line_items);
            } else {
                //This is the last one, so add them to the previous section
                $this->sections[$section_index - 1]->line_items = array_merge($this->sections[$section_index - 1]->line_items, $this->sections[$section_index]->line_items);
            }
            $this->sections[$section_index] = null;
        }
        $this->refreshSections();
    }

    public function insertSection($section, $section_index, $item_index)
    {
        //Move current item and others before it to the new section (anything after it can stay in the old section)
        for ($sibling_index = 0; $sibling_index <= $item_index; $sibling_index++)
        {
            $sibling = $this->sections[$section_index]->line_items[$sibling_index];
            $section->line_items[] = $sibling;
            unset($this->sections[$section_index]->line_items[$sibling_index]);
        }

        //Insert the new section in the appropriate place
        array_splice($this->sections, $section_index, 0, array($section));
        $this->refreshSections();
    }

    public function moveLineItemUp($section_index, $item_index)
    {
        if ($section_index == 0 && $item_index == 0) {
            //Already at the top - no action taken
        } else {
            $item = $this->sections[$section_index]->line_items[$item_index];
            if ($item_index == 0) {
                //Move to end of previous section
                array_push($this->sections[$section_index - 1]->line_items, $item);
                unset($this->sections[$section_index]->line_items[$item_index]);
            } else {
                //Swap with previous item
                $this->sections[$section_index]->line_items[$item_index] = $this->sections[$section_index]->line_items[$item_index - 1];
                $this->sections[$section_index]->line_items[$item_index - 1] = $item;
            }
        }
        $this->refreshSections();
    }

    public function moveLineItemDown($section_index, $item_index)
    {
        if ($section_index >= count($this->sections) - 1 && $item_index >= count($this->sections[$section_index]->line_items) - 1) {
            //Already at the bottom - no action taken
        } else {
            $item = $this->sections[$section_index]->line_items[$item_index];
            if ($item_index >= count($this->sections[$section_index]->line_items) - 1) {
                //Move to start of next section
                array_unshift($this->sections[$section_index + 1]->line_items, $item);
                unset($this->sections[$section_index]->line_items[$item_index]);
            } else {
                //Swap with next item
                $this->sections[$section_index]->line_items[$item_index] = $this->sections[$section_index]->line_items[$item_index + 1];
                $this->sections[$section_index]->line_items[$item_index + 1] = $item;
            }
        }
        $this->refreshSections();
    }

    public function refreshSections($recalculate_items = false)
    {
        //If any sections have been removed, reset the indices
        $this->sections = array_values(array_filter($this->sections));

        //If any sections are empty, remove them, and while we're at it, make sure everyone knows who their parent is
        for ($section_index = 0; $section_index < count($this->sections); $section_index++)
        {
            $this->sections[$section_index]->index = $section_index;
            $this->sections[$section_index]->line_items = array_values(array_filter($this->sections[$section_index]->line_items));
            if (count($this->sections[$section_index]->line_items) == 0 && count($this->sections) > 1) {
                $this->sections[$section_index] = null;
            } else {
                for ($item_index = 0; $item_index < count($this->sections[$section_index]->line_items); $item_index++)
                {
                    $this->sections[$section_index]->line_items[$item_index]->setParentSection($this->sections[$section_index]);
                    $this->sections[$section_index]->line_items[$item_index]->index = $item_index;
                }
            }
        }

        //Then reset indices again!
        $this->sections = array_values(array_filter($this->sections));

        //Reset ordering and make sure each section knows what its own index is
        $this->resetOrdering(true);

        //Recalculate
        $this->calculateTotals($recalculate_items);
    }

    public function merge($line_items = array())
    {
        $new_section = false;
        if (count($this->sections) > 0) {
            $section = end($this->sections); //Get by value, as we might want to leave it alone
            if (strlen($section->section_name) != 0) {
                //Add a new blank section
                $new_section = true;
            } else {
                $section =& end($this->sections); //Get by reference so we can update it
            }
        }
        if ($new_section) {
            $section = $this->factory->createLineItemsSection($this);
            $this->sections[] =& $section;
        }
        array_merge($section->line_items, $line_items);
    }

    public function getCurrency()
    {
        if (!isset($this->currency)) {
            if (count($this->sections) > 0) {
                foreach ($this->sections as $section)
                {
                    if (count($section->line_items) > 0) {
                        list($firstItem) = $this->line_items;
                        $this->currency = @$firstItem->gross_price_for_item->currency;
                        break;
                    }
                }
            }
        }
        return $this->currency;
    }

    public function getUnitQuantityPresent()
    {
        foreach ($this->sections as $section)
        {
            foreach ($section->line_items as $line_item)
            {
                if ($line_item->no_of_units->value != 1) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getTaxPresent()
    {
        foreach ($this->sections as $section)
        {
            foreach ($section->line_items as $line_item)
            {
                if ($line_item->tax_for_item->value != 0) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getShippingPresent()
    {
        foreach ($this->sections as $section)
        {
            foreach ($section->line_items as $line_item)
            {
                if ($line_item->shipping_for_item->value != 0 || $line_item->tax_for_shipping->value != 0) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getShippingTaxPresent()
    {
        foreach ($this->sections as $section)
        {
            foreach ($section->line_items as $line_item)
            {
                if ($line_item->tax_for_shipping->value != 0) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getDiscountsPresent()
    {
        foreach ($this->sections as $section)
        {
            foreach ($section->line_items as $line_item)
            {
                if ($line_item->discount_amount->value != 0 || strlen($line_item->discount_description) > 0) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getNetTotal()
    {
        if (!isset($this->net_total)) {
            $this->calculateTotals();
        }
        return $this->net_total;
    }

    public function getItemTaxTotal()
    {
        if (!isset($this->item_tax_total)) {
            $this->calculateTotals();
        }
        return $this->item_tax_total;
    }

    public function getShippingTotal()
    {
        if (!isset($this->shipping_total)) {
            $this->calculateTotals();
        }
        return $this->shipping_total;
    }

    public function getShippingTaxTotal()
    {
        if (!isset($this->shipping_tax_total)) {
            $this->calculateTotals();
        }
        return $this->shipping_tax_total;
    }

    public function getTaxTotal()
    {
        if (!isset($this->tax_total)) {
            $this->calculateTotals();
        }
        return $this->tax_total;
    }

    public function getGrossTotal()
    {
        if (!isset($this->gross_total)) {
            $this->calculateTotals();
        }
        return $this->gross_total;
    }

    public function calculateTotals($force_recalculate = false)
    {
        $this->net_total = $this->number_factory->createNumberCurrency(0, $this->getCurrency());
        $this->net_total->setIsGrandTotal(true);
        $this->item_tax_total = $this->number_factory->createNumberCurrency(0, $this->getCurrency());
        $this->item_tax_total->setIsGrandTotal(true);
        $this->shipping_total = $this->number_factory->createNumberCurrency(0, $this->getCurrency());
        $this->shipping_total->setIsGrandTotal(true);
        $this->shipping_tax_total = $this->number_factory->createNumberCurrency(0, $this->getCurrency());
        $this->shipping_tax_total->setIsGrandTotal(true);
        $this->tax_total = $this->number_factory->createNumberCurrency(0, $this->getCurrency());
        $this->tax_total->setIsGrandTotal(true);
        $this->gross_total = $this->number_factory->createNumberCurrency(0, $this->getCurrency());
        $this->gross_total->setIsGrandTotal(true);

        foreach ($this->sections as $section)
        {
            $section->calculateTotals($force_recalculate);
            $this->net_total = $this->net_total->addNumber($section->getNetTotal($force_recalculate));
            $this->item_tax_total = $this->item_tax_total->addNumber($section->getItemTaxTotal($force_recalculate));
            $this->shipping_total = $this->shipping_total->addNumber($section->getShippingTotal($force_recalculate));
            $this->shipping_tax_total = $this->shipping_tax_total->addNumber($section->getShippingTaxTotal($force_recalculate));
        }

        $this->tax_total = $this->item_tax_total->addNumber($this->shipping_tax_total);
        $this->gross_total = $this->net_total->addNumber($this->item_tax_total)->addNumber($this->shipping_total)->addNumber($this->shipping_tax_total);
    }

    public function resetOrdering($refresh_section_indices = false)
    {
        $ordering = 0;
        foreach ($this->sections as $section_index=>$section)
        {
            if ($refresh_section_indices) {
                $this->sections[$section_index]->index = $section_index;
            }
            foreach ($section->line_items as $line_item)
            {
                $line_item->ordering = $ordering;
                $ordering++;
            }
        }
    }
}