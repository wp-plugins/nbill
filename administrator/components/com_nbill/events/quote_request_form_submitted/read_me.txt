To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
quote_request_form_submitted

PARAMS:
["id"] => int (ID of the form that was submitted. You can look up the form details on #__nbill_order_form, plus there will be various values held in the $_POST array. This event is fired AFTER the form's own submit code has been evaluated. If the form's own submit code calls abort (ie. sets $abort to true), this event will not be fired)