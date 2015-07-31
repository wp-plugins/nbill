To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
order_status_updated

PARAMS:
["id"] => int (ID number of the order record. You can look up the rest of the details on the #__nbill_orders table)
["old_status"] => text (Status code before the change was made - if order record is new, this will be blank, otherwise, use values from the #__nbill_xref_order_status lookup table)
["new_status"] => text (Status code after the change was made - if order record has been deleted, this will be blank, otherwise, use values from the #__nbill_xref_order_status lookup table)

NOTES:
This event is fired whenever the status of an order changes. This includes when a new order is created (in which case the order_created event is fired first) AND when an existing order is deleted (in which case the order_deleted event is fired afterwards).