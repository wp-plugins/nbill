To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
payment_received

PARAMS:
["g_tx_id"] => int (Gateway transaction ID - use this to load further details from the database, eg. from the #__nbill_orders table)
["amount"] => float (Amount of payment received)
["currency"] => string (3-character ISO code of payment currency - eg. USD)
["order_ids"] => string (If the payment has resulted in the creation of one or more new order records, the order ID numbers will be provided here as a comma-delimited list)
["invoice_ids"] => string (If the payment has resulted in the creation of one or more new invoice records, the invoice ID numbers will be provided here as a comma-delimited list)
["transaction_id"] => int (If the payment has resulted in the creation of a new income record, the transaction ID number will be provided here)
["vendor_id"] => int (If the payment could be associated with a vendor record, the vendor ID will be provided here)
["reference"] => string (If a reference number was supplied by the payment gateway, it will be provided here)