To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
ledger_deleted

PARAMS:
["ids"] => int[,int[,int...etc.]] (Comma-separated list of nominal ledger IDs for the items to be deleted. Event is fired immediately before record is deleted, so you can still look up any details you want on the #__nbill_nominal_ledger table)