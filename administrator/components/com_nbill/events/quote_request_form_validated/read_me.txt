To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
quote_request_form_validated

PARAMS:
["id"] => int (ID of form that has been submitted for validation. You can look up the form details on #__nbill_order_form, plus there will be various values held in the $_POST array. This event is fired AFTER the form's own validation code has been evaluated - whether or not the form passed validation. If validation failed, the $error_message variable will be populated)