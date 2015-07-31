To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
record_updated

PARAMS:
["type"] => string (Type of record that was updated. Possible values are: shipping, category, client, configuration, currency, discount, expenditure, gateway, income, invoice, credit, ledger, order, product, reminder, supplier, tax, vendor)
["id"] => int (ID number of the updated record)