To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
number_generated

PARAMS:
["vendor_id"] => int (ID number of vendor for which a number was generated)
["type"] => string (Type of number generated. Possible values are: order, invoice, receipt, payment, credit)
["number"] => string (The actual number that was generated)