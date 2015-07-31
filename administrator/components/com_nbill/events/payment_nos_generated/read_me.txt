To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
payment_nos_generated

PARAMS:
["vendor_id"] => int (ID number of vendor record for whom payment numbers have been generated)
["date"] => int (Timestamp up to which payment numbers have been generated)
["no_of_nos"] => int (Number of payment numbers that were generated)
["first_no"] => string (First payment number in the range that was generated)
["last_no"] => string (Last payment number in the range that was generated)