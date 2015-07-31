To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
gateway_activated

PARAMS:
["gateway"] => string (Name of payment gateway)
["g_tx_id"] => int (Gateway transaction ID - use this to look up the rest of the details in the #__nbill_gateway_tx table)