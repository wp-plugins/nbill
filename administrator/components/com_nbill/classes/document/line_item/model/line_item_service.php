<?php
class nBillLineItemService
{
    /** @var nBillLineItemMapper **/
    protected $mapper;

    public function __construct(nBillLineItemMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function getItemsForDocument($document_type, $document_id, $vendor_id = null)
    {
        return $this->mapper->loadItemsForDocument($document_type, $document_id, $vendor_id);
    }

    /**
    * Return array of document item IDs where items have been newly accepted
    * @param nBillLineItemsCollection $line_items
    */
    public function getNewQuoteAccepts($document_id, nBillLineItemsCollection $line_items)
    {
        $new_quote_accepts = array();
        $old_items = $this->getItemsForDocument('QU', $document_id);
        foreach ($old_items->sections as $old_section) {
            foreach ($old_section->line_items as $old_item) {
                if (!$old_item->quote_item_accepted) {
                    foreach ($line_items->sections as $new_section) {
                        foreach ($new_section->line_items as $new_item) {
                            if ($new_item->id == $old_item->id) {
                                if ($new_item->quote_item_accepted) {
                                    $new_quote_accepts[] = $new_item->id;
                                }
                                break 2;
                            }
                        }
                    }
                }
            }
        }
        return $new_quote_accepts;
    }

    public function saveItems($document_id, nBillLineItemsCollection $line_items, $vendor_id, $entity_id)
    {
        $this->mapper->saveItems($document_id, $line_items, $vendor_id, $entity_id);
    }
}