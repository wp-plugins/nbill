To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
pending_order_activated

PARAMS:
["id"] => int (ID number of the pending order record)
["order_ids"] => int[,int[,int...etc.]] (Comma-separated list of order IDs for the order records created)

NOTE:
This event is fired whenever a pending order is activated - either when an administrator does so in the back-end, or when a payment gateway receives notification of payment for a pending order and creates the order record(s) automatically.