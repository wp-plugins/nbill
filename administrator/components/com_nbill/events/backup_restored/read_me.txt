To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
backup_restored

PARAMS:
["what"] => "all"|"billing" (all = All tables were restored; billing = Just the Billing tables were restored)
["queries"] => int (Number of queries executed)