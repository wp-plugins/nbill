<?php
class nBillDefaultTemplateQuoteLineItem extends nBillQuoteLineItemHtml
{
    /**
    * You can override the default line item output here (the default output is in the file
    * /administrator/components/com_nbill/classes/document/line_item/view/quote_line_item_html.php).
    * Just copy the function you want to override from that file into this, and make your changes in this file.
    * You can then upgrade nBill without losing your customisation. Please note however, that you still need to
    * copy the template folder (/components/com_nbill/templates/quote_default), to a new folder (named whatever
    * you want, but use a prefix of quote_) and select your new folder as the invoice template on the vendor
    * record in nBill (as this quote_default folder will be overwritten when you upgrade nBill)
    */
}