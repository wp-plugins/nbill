<?php
class nBillDeliveryTemplateLineItem extends nBillLineItemHtml
{
    /**
    * You can override the default line item output here (the default output is in the file
    * /administrator/components/com_nbill/classes/document/line_item/view/line_item_html.php).
    * Just copy the function you want to override from that file into this, and make your changes in this file.
    * You can then upgrade nBill without losing your customisation. Please note however, that you still need to
    * copy the template folder (/components/com_nbill/templates/invoice_default), to a new folder (named whatever
    * you want, but use a prefix of delivery_) and select your new folder as the delivery note template on the vendor
    * record in nBill (as this delivery_default folder will be overwritten when you upgrade nBill)
    */

    //If you want to show amounts on the delivery note, you can delete this function:
    protected function canHideColumn($column)
    {
        switch ($column)
        {
            case 'net_price_per_unit':
            case 'discount_amount':
            case 'net_price_for_item':
            case 'tax_for_item':
            case 'shipping_for_item':
            case 'tax_for_shipping':
            case 'gross_price_for_item':
                return true;
        }
        return false;
    }

    //If you want to show the invoice total on the delivery note, you can delete this function:
    protected function renderTotalRow($styles = array())
    {
        return;
    }
}