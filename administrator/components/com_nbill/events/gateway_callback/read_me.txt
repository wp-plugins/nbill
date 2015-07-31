To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
gateway_callback

PARAMS:
["gateway"] => string (Name of payment gateway that received the callback - get other parameters from $_REQUEST)